<?php
/**
 * Settings for reporting CSP issues.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class Reporting extends AbstractSettingsHandler {
	protected $tab = 'csp';

	protected $section = 'reporting';

	private $ajax_action = 'wpshr';

	private static $cron_name = 'wpshr-cron-reporting-flush';

	private $report_url = '';

	public function __construct() {
		$base_url         = \admin_url( 'admin-ajax.php' );
		$this->report_url = "{$base_url}?action={$this->ajax_action}";

		$this->setDefault( 'enabled', true );
		$this->setDefault( 'report_sample', true );
		$this->setDefault( 'built_in', true );
		$this->setDefault( 'retain_days', 30 );
		$this->setDefault( 'remote_url', '' );

		$this->addSettingsSection();
	}

	private function addSettingsSection() {
		SettingsRegister::addSettingsSection(
			array(
				'tab_id'              => $this->tab,
				'section_order'       => 2,
				'section_id'          => $this->section,
				'section_title'       => '<span id="' . $this->getSectionId() . '"></span>Reporting',
				'section_description' => ' ',
				'show_if'             => array(
					array(
						'field' => 'csp_mode_enabled',
						'value' => array( 'enabled', 'testing' ),
					),
				),
				'fields' => array(
					array(
						'id'      => 'enabled',
						'title'   => 'Enable reporting',
						'type'    => 'toggle',
						'default' => $this->getDefault( 'enabled' ),
					),
					array(
						'id'       => 'report_sample',
						'title'    => 'Attempt to report samples',
						'subtitle' => '<strong>Note:</strong> Sample reporting may be unreliable.',
						'type'     => 'toggle',
						'default'  => $this->getDefault( 'built_in' ),
					),
					array(
						'id'       => 'built_in',
						'title'    => 'Use bult-in report URI',
						'subtitle' => '<strong>Note:</strong> this may significantly increase database activity.',
						'type'     => 'toggle',
						'default'  => $this->getDefault( 'built_in' ),
					),
					// @TODO implement this!
					array(
						'id'       => 'retain_days',
						'title'    => 'Keep reports',
						'subtitle' => 'Stale reports will be flushed periodically.',
						'type'     => 'select',
						'choices'  => array(
							30  => '30 Days',
							60  => '60 Days',
							90  => '90 Days',
							120 => '120 Days',
						),
						'default' => $this->getDefault( 'retain_days' ),
						'show_if' => array(
							array(
								'field' => "{$this->getSectionId()}_built_in",
								'value' => array( '1' ),
							),
						),
					),
					array(
						'id'      => 'remote_url',
						'title'   => 'Remote reporting URL',
						'type'    => 'url',
						'class'   => 'wide',
						'default' => $this->getDefault( 'remote_url' ),
						'show_if' => array(
							array(
								'field' => 'csp_reporting_built_in',
								'value' => array( '0' ),
							),
						),
						'hide_if' => array(
							array(
								'field' => 'csp_reporting_built_in',
								'value' => array( '1' ),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Determines if CSP Reporting is activated.
	 *
	 * @return bool
	 */
	public function isActive() {
		/**
		 * @var CSPMode
		 */
		$csp_mode = SettingsRegister::getHandler( 'csp/mode' );

		if ( $csp_mode->isActive() ) {
			$enabled = $this->getSetting( 'enabled' );
			if ( $enabled && '0' !== $enabled ) {
				return true;
			}
		}

		return false;
	}

	public function getReportingUrl() {
		if ( $this->isActive() ) {
			$built_in = $this->getSetting( 'built_in' );
			if ( $built_in && '0' !== $built_in ) {
				return $this->report_url;
			}
		}

		return;
	}

	public function generateReportString( $policies ) {
		$policies['report-uri'] = "report-uri {$this->getReportingUrl()}";
		// $policies['report-to']  = 'report-to wpsh';

		return $policies;
	}

	public function addReportSample( $policies ) {
		$policies[] = "'report-sample'";

		return $policies;
	}

	public function validate( $input ) {
		$validations = array(
			array(
				'id'       => 'enabled',
				'callback' => function ( $val ) {
					if ( ! \in_array( (string) $val, array( '0', '1' ) ) ) {
						\wp_die( 'Invalid value for CSP Reporting enabled' );
					}

					return $val;
				},
			),
			array(
				'id'       => 'built_in',
				'callback' => function ( $val ) {
					if ( ! \in_array( (string) $val, array( '0', '1' ) ) ) {
						\wp_die( 'Invalid value for CSP Reporting "Use Built In"' );
					}

					return $val;
				},
			),
			array(
				'id'       => 'remote_url',
				'callback' => function ( $val ) {
					if ( empty( $val ) ) {
						return $val;
					}
					if ( ! \filter_var( $val, \FILTER_VALIDATE_URL ) ) {
						\wp_die( 'CSP Reporting "Remote URL" must be a valid URL' );
					}

					return $val;
				},
			),
		);

		$input = $this->runValidations( $validations, $input );

		return $input;
	}
}
