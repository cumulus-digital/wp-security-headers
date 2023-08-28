<?php
/**
 * Activation of CSP.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\Reports;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use const CUMULUS\Wordpress\SecurityHeaders\BASEDIR;
use const CUMULUS\Wordpress\SecurityHeaders\BASEURL;
use CUMULUS\Wordpress\SecurityHeaders\Installer;
use const CUMULUS\Wordpress\SecurityHeaders\PREFIX;
use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;
use WP_Error;

class Dashboard extends AbstractSettingsHandler {
	protected $tab = 'reports';

	protected $section = 'dashboard';

	protected $ajax_action = 'wpshr-reports';

	protected $cspReportSetting;

	public function __construct() {
		$this->cspReportSetting = SettingsRegister::getHandler( 'csp/reporting' );

		$this->addSettingsSection();
		$this->addAjaxHandlers();

		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function enqueueScripts( $hook ) {
		$prefix  = PREFIX;
		$baseurl = BASEURL;

		if ( $hook !== 'settings_page_' . \str_replace( '_', '-', $prefix ) . '-settings' ) {
			return;
		}

		$assets = require BASEDIR . '/build/reports-table.asset.php';

		\wp_enqueue_script(
			"{$prefix}-reports_table-script",
			"{$baseurl}/build/reports-table.js",
			$assets['dependencies'],
			$assets['version'],
			true
		);

		/*
		\wp_enqueue_style(
			"{$prefix}-reports-table-style",
			"{$baseurl}/build/style-reports-table.css"
		);

		\wp_enqueue_style(
			"{$prefix}-reports-table-style2",
			"{$baseurl}/build/reports-table.css"
		);
		 */

		$admin_ajax = \admin_url( 'admin-ajax.php' );
		\wp_add_inline_script(
			"{$prefix}-reports_table-script",
			"
				window.{$prefix}_ajax = {
					url: '{$admin_ajax}',
					actions: {
						get: '{$this->ajax_action}-get',
						flush: '{$this->ajax_action}-flush',
					},
					shadow_styles: [
						'{$baseurl}/build/style-reports-table.css',
						'{$baseurl}/build/reports-table.css'
					]
				};
			",
			'before'
		);
	}

	private function addSettingsSection() {
		SettingsRegister::addSettingsSection(
			array(
				'tab_id'              => $this->tab,
				'section_order'       => 1,
				'section_id'          => $this->section,
				'section_title'       => '<span id="' . $this->getSectionId() . '"></span>Reports',
				'section_description' => $this->isBuiltInEnabled()
					? '<reports-table">Loading reports...</reports-table>'
					: '<p>To see reports in this tab, you must enable the Built-In Report URI.</p>',
				/*
				'fields'        => array(
					array(
						'id'    => 'vue-container',
						'title' => '',
						'type'  => 'html',
						'raw'   => true,
						'html'  => $this->isBuiltInEnabled()
							? '<reports-table">Loading reports...</reports-table>'
							: '<p>To see reports in this tab, you must enable the Built-In Report URI.</p>',
					),
				),
				 */
			),
		);
	}

	public function isReportingEnabled() {
		return (bool) $this->cspReportSetting->getSetting( 'enabled' );
	}

	public function isBuiltInEnabled() {
		return (bool) $this->cspReportSetting->getSetting( 'built_in' );
	}

	public function addAjaxHandlers() {
		\add_action( "wp_ajax_{$this->ajax_action}-get", array( $this, 'fetchReports' ) );
		\add_action( "wp_ajax_{$this->ajax_action}-flush", array( $this, 'flushReports' ) );
	}

	public function flushReports() {
		if ( ! \is_admin() || ! \current_user_can( 'switch_themes' ) ) {
			return \wp_send_json_error( new WP_Error( '403', 'Access denied.' ) );
		}
		if ( ! \in_array( $_SERVER['REQUEST_METHOD'], array( 'POST', 'DELETE' ) ) ) {
			return \wp_send_json_error( new WP_Error( '403', 'DELETE/POST access only.' ) );
		}

		global $wpdb;
		$table  = $wpdb->prefix . PREFIX . Installer::$table_name;
		$delete = $wpdb->query( "TRUNCATE TABLE {$table}" );

		return \wp_send_json_success();
	}

	public function fetchReports() {
		if ( ! \is_admin() || ! \current_user_can( 'switch_themes' ) ) {
			return \wp_send_json_error( new WP_Error( '403', 'Access denied.' ) );
		}

		if ( ! $this->cspReportSetting->isActive() ) {
			return \wp_send_json_error( new WP_Error( '400', 'CSP Reporting is not active.' ) );
		}
		if ( 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
			return \wp_send_json_error( new WP_Error( '403', 'GET access only.' ) );
		}

		$per_page = \array_key_exists( 'pp', $_GET ) ? \intval( $_GET['pp'] ) : 10          ?? 10;
		$page     = \array_key_exists( 'p', $_GET ) ? \intval( $_GET['p'] ) : 0             ?? 0;
		$sort     = \array_key_exists( 's', $_GET ) ? \mb_strtolower( $_GET['s'] ) : 'date' ?? 'date';
		$order    = \array_key_exists( 'o', $_GET ) ? \mb_strtolower( $_GET['o'] ) : 'desc' ?? 'desc';

		if (
			! \in_array( $sort, array( 'date', 'created_at', 'violated_directive', 'document_uri', 'status_code' ), true )
		) {
			$sort = 'created_at';
		}
		if ( 'date' === $sort ) {
			$sort = 'created_at';
		}
		if ( ! \in_array( $order, array( 'asc', 'desc' ), true ) ) {
			$order = 'desc';
		}

		if ( $page && $page > 0 ) {
			--$page;
		}
		$directives   = $_GET['d'] ?? false;
		$where_clause = null;
		if ( $directives && 'all' !== \mb_strtolower( $directives ) ) {
			$directives     = \explode( ',', \mb_strtolower( $directives ) );
			$elem_attr_dirs = array( 'script-src', 'style-src' );
			foreach ( $elem_attr_dirs as $elem_attr_dir ) {
				if ( \in_array( $elem_attr_dir, $directives ) ) {
					if ( ! \in_array( "{$elem_attr_dir}-elem", $directives ) ) {
						$directives[] = "{$elem_attr_dir}-elem";
					}
					if ( ! \in_array( "{$elem_attr_dir}-attr", $directives ) ) {
						$directives[] = "{$elem_attr_dir}-attr";
					}
				}
			}
			$sql_directives = \array_map( function ( $val ) {
				return "'" . \esc_sql( $val ) . "'";
			}, $directives );
			$where_clause = 'WHERE violated_directive IN (' . \implode( ',', $sql_directives ) . ')';
		}

		global $wpdb;
		$table = $wpdb->prefix . PREFIX . Installer::$table_name;
		$sql   = $wpdb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS * FROM {$table} {$where_clause} ORDER BY {$sort} {$order} LIMIT %d OFFSET %d",
			$per_page,
			$page
		);
		$results = $wpdb->get_results( $sql );

		$total = $wpdb->get_var( "SELECT COUNT(id) FROM {$table} {$where_clause}" );

		if ( $results && \count( $results ) ) {
			return \wp_send_json_success( array(
				'items'    => $results,
				'page'     => $page + 1,
				'per_page' => $per_page,
				'total'    => \intval( $total ),
			) );
		}

		return \wp_send_json_error( array( 'error' => 'No reports found.' ) );
	}
}
