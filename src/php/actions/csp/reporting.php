<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\CSP;

use CUMULUS\Wordpress\SecurityHeaders\Actions\AbstractActor;
use const CUMULUS\Wordpress\SecurityHeaders\BASEDIR;
use function CUMULUS\Wordpress\SecurityHeaders\debug;
use CUMULUS\Wordpress\SecurityHeaders\Installer;
use function CUMULUS\Wordpress\SecurityHeaders\ns;
use const CUMULUS\Wordpress\SecurityHeaders\PLUGIN;
use const CUMULUS\Wordpress\SecurityHeaders\PREFIX;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;
use Exception;
use WP_Error;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class Reporting extends AbstractActor {
	/**
		 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\Reporting
		 */
	protected $settings;

	/**
	 * Name for the WP AJAX action we'll create and listen to.
	 */
	private $ajax_action = 'wpshr';

	/**
	 * Name of the WP Cron event for report flushing.
	 */
	private static $cron_name = 'wpshr-cron-flush-reports';

	public function __construct() {
		$this->settings = SettingsRegister::getHandler( 'csp/reporting' );
		$this->setupReportHandler();
		$this->setupCron();
	}

	public function sendHeaders() {
		if ( $this->isActive() && $this->settings->getReportingUrl() ) {
			debug( 'CSP Reporting is ACTIVE.' );

			$report_to = array(
				'group'     => 'wpsh',
				'max_age'   => 1800,
				'endpoints' => array(
					array(
						'url' => $this->settings->getReportingUrl(),
					),
				),
			);

			return;
		}
		debug( 'CSP Reporting is NOT active.' );
	}

	public function sendAdminHeaders() {
		/**
		 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\Mode
		 */
		$csp_mode = SettingsRegister::getHandler( 'csp/mode' );

		if ( $csp_mode->getSetting( 'in-admin' ) ) {
			$this->sendHeaders();
		}
	}

	public function addReportSamplePolicy( $policies, $directive ) {
		$valid_directives = array(
			'default-src',
			'script-src',
			'script-src-attr',
			'script-src-elem',
			'style-src',
			'style-src-attr',
			'style-src-elem',
		);
		if ( \in_array( $directive, $valid_directives ) ) {
			$policies[] = "'report-sample'";
		}

		return $policies;
	}

	public function addReportToPolicy( $policies ) {
		if ( $this->isActive() && (bool) $this->settings->getReportingUrl() ) {
			$policies['report-uri'] = "report-uri {$this->settings->getReportingUrl()}";
		}

		return $policies;
	}

	public function setupReportHandler() {
		if ( $this->isActive() && $this->settings->getReportingUrl() ) {
			if ( (bool) $this->getSetting( 'built_in' ) ) {
				\add_action( "wp_ajax_{$this->ajax_action}", array( $this, 'ingestReport' ) );
				\add_action( "wp_ajax_nopriv_{$this->ajax_action}", array( $this, 'ingestReport' ) );
				debug( 'Built-In Reporting is ENABLED' );
			}

			$prefix = PREFIX;
			if ( (bool) $this->getSetting( 'report_sample' ) ) {
				\add_filter( "{$prefix}_csp_all", array( $this, 'addReportSamplePolicy' ), 10, 2 );
			}
			\add_filter( "{$prefix}_csp_final", array( $this, 'addReportToPolicy' ) );
		}
	}

	public function setupCron() {
		$is_scheduled = \wp_next_scheduled( self::$cron_name );
		if ( $this->isActive() && ! $is_scheduled ) {
			\wp_schedule_event( \time(), 'hourly', self::$cron_name );
		} elseif ( ! $this->isActive() && $is_scheduled ) {
			self::removeCron();
		}

		\add_action( self::$cron_name, array( $this, 'handleCron' ) );
		\register_deactivation_hook( PLUGIN, ns( 'Reporting::removeCron' ) );
		\register_uninstall_hook( PLUGIN, ns( 'Reporting::removeCron' ) );
	}

	public static function removeCron() {
		$is_scheduled = \wp_next_scheduled( self::$cron_name );
		if ( $is_scheduled ) {
			\wp_unschedule_event( $is_scheduled, 'hourly', self::$cron_name );
		}
	}

	/**
	 * Handle cron event to flush reports.
	 */
	public function handleCron() {
		if ( ! $this->isActive() ) {
			return;
		}

		try {
			$retain = \intval( $this->settings->getSetting( 'retain_days' ) );
			if ( ! $retain || ! \in_array( $retain, array( 15, 30, 60, 90, 120 ) ) ) {
				return;
			}

			global $wpdb;
			$table = $wpdb->prefix . PREFIX . Installer::$table_name;
			$time  = \date( 'Y-m-d H:i:s', \strtotime( "-{$retain} days" ) );
			$sql   = $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s", $time );
			$wpdb->query( $sql );
		} catch ( Exception $e ) {
			// Do nothing
		}
	}

	public function ingestReport() {
		\header( 'X-Content-Type-Options: nosniff' );

		if ( ! $this->isActive() ) {
			return \wp_send_json_error( array( 'error' => 'CSP Reporting is not active.' ), 400 );
		}
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return \wp_send_json_error( array( 'error' => 'CSP Reports must be POST requests.' ), 403 );
		}

		$data = \json_decode( \file_get_contents( 'php://input' ), true );
		if ( ! $data || ! isset( $data['csp-report'] ) ) {
			return \wp_send_json_error( array( 'error' => 'Received invalid report.' ), 403 );
		}
		$data = $data['csp-report'];

		$clean_data = $this->validateReport( $data );
		if ( \is_wp_error( $clean_data ) ) {
			return \wp_send_json_error( $clean_data->get_error_message(), $clean_data->get_error_code() );
		}

		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		global $wpdb;
		$insert = $wpdb->insert(
			$wpdb->prefix . PREFIX . Installer::$table_name,
			array(
				'created_at'         => \current_time( 'mysql' ),
				'full_report'        => \json_encode( $clean_data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES ),
				'document_uri'       => $clean_data['document-uri']       ?? '',
				'referrer'           => $clean_data['referrer']           ?? null,
				'violated_directive' => $clean_data['violated-directive'] ?? '',
				'original_policy'    => $clean_data['original-policy']    ?? '',
				'blocked_uri'        => $clean_data['blocked-uri']        ?? '',
				'source_file'        => $clean_data['source-file']        ?? null,
				'line_number'        => $clean_data['line-number']        ?? null,
				'column_number'      => $clean_data['column-number']      ?? null,
				'script_sample'      => $clean_data['script-sample']      ?? null,
				'status_code'        => $clean_data['status-code']        ?? null,
				'user_agent'         => $user_agent                       ?? null,
			)
		);

		if ( $insert ) {
			return \wp_send_json_success();
		}

		\trigger_error( 'Error logging CSP report!', \E_USER_WARNING );
		\trigger_error(
			\htmlspecialchars(
				$wpdb->last_error,
				\ENT_QUOTES,
				'UTF-8'
			),
			\E_USER_WARNING
		);

		return \wp_send_json_error( array( 'error' => 'Failed to log report.' ), 500 );
	}

	public function validateReport( $report ) {
		if ( ! \is_array( $report ) ) {
			return new WP_Error( '403', 'Received a malformed report.' );
		}

		// Check for required keys
		$required_keys = array(
			'document-uri',
			'blocked-uri',
			'violated-directive',
			'original-policy',
		);
		foreach ( $required_keys as $key ) {
			if ( ! \is_array( $report ) || ! \array_key_exists( $key, $report ) ) {
				return new WP_Error( '403', 'Report is missing required keys.' );
			}
		}

		// Check for browser-specific URL schemes
		foreach ( array( 'document-uri', 'source-file', 'blocked-uri' ) as $key ) {
			if (
				\array_key_exists( $key, $report )
				&& ! \in_array( $report[$key], array( 'inline', 'about', 'data' ) )
			) {
				$scheme = \parse_url( $report[$key], \PHP_URL_SCHEME );
				if ( $scheme ) {
					$scheme = \mb_strtolower( $scheme );
				}
				if ( ! $scheme || ! \in_array( $scheme, array( 'http', 'https' ) ) ) {
					// Ignore non-browser URL schemes
					\trigger_error(
						\htmlspecialchars(
							'Invalid URL scheme ' . $scheme . 'in ' . $key . ' of CSP report',
							\ENT_QUOTES,
							'UTF-8'
						),
						\E_USER_WARNING
					);
					\trigger_error(
						\htmlspecialchars(
							\print_r( $report, true ),
							\ENT_QUOTES,
							'UTF-8'
						),
						\E_USER_WARNING
					);

					return new WP_Error( '200', 'Ignored due to non-browser URL scheme.' );
				}
			}
		}

		// Check that document host matches our allowed hosts
		$document_host = \parse_url( $report['document-uri'], \PHP_URL_HOST );
		if ( $document_host ) {
			$document_host = \mb_strtolower( $document_host );
		}
		$site_host = \mb_strtolower( \parse_url( \get_site_url(), \PHP_URL_HOST ) );
		if ( $document_host && $document_host !== $site_host ) {
			return new WP_Error( '403', 'Incorrect document-uri hostname in report.' );
		}

		// CSP-WTF filters
		$filters = include BASEDIR . '/src/php/csp-wtf-filters.php';

		$report_issue = true;
		foreach ( $filters as $filter_check => $options ) {
			$filter_on = $report[$options['filter_on']] ?? false;
			if ( ! $filter_on || ( \is_array( $report ) && ! \array_key_exists( $filter_on, $report ) ) ) {
				continue;
			}
			if ( false !== \mb_strpos( $report[$filter_on], $filter_check ) ) {
				$report_issue = false;

				break;
			}
		}
		if ( true !== $report_issue ) {
			\trigger_error( 'Invalid CSP report', \E_USER_WARNING );
			\trigger_error(
				\htmlspecialchars(
					\print_r( $report, true ),
					\ENT_QUOTES,
					'UTF-8'
				),
				\E_USER_WARNING
			);

			return new WP_Error( '403', 'Invalid report.' );
		}

		return $report;
	}
}
