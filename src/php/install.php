<?php

namespace CUMULUS\Wordpress\SecurityHeaders;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class Installer {
	public static $version = '1.0';

	public static $version_key = '_dbversion';

	public static $table_name = '_reports';

	public static function run() {
		self::createDatabase();
	}

	/**
	 * Install database table for reports.
	 */
	public static function createDatabase() {
		global $wpdb;

		$table_name  = $wpdb->prefix . PREFIX . self::$table_name;
		$version_key = PREFIX . self::$version_key;

		$charset_collate = $wpdb->get_charset_collate();

		$default_time = 'CURRENT_TIMESTAMP';
		if ( \version_compare( $wpdb->db_version(), '5.6.5', '<' ) ) {
			$default_time = '\'0000-00-00 00:00:00\'';
		}

		$raw_type = 'text';

		$sql = "CREATE TABLE {$table_name} (
			id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
			created_at datetime DEFAULT {$default_time} NOT NULL,
			full_report {$raw_type} NOT NULL,
			document_uri text NOT NULL,
			referrer text,
			violated_directive varchar(255) NOT NULL,
			original_policy text NOT NULL,
			blocked_uri text DEFAULT '' NOT NULL,
			source_file text,
			line_number mediumint(9),
			column_number mediumint(9),
			status_code int(4) UNSIGNED,
			script_sample varchar(50),
			user_agent text,
			KEY created_at (created_at),
			KEY violated_directive (violated_directive),
			PRIMARY KEY  (id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( \dbDelta( $sql ) ) {
			\add_option( $version_key, self::$version );
		}
	}

	/**
	 * Check if we need to upgrade the database schema.
	 */
	public static function checkDatabase() {
		$version_key = PREFIX . self::$version_key;
		if ( \get_site_option( $version_key ) !== self::$version ) {
			self::createDatabase();
		}
	}
}

\register_activation_hook( PLUGIN, ns( 'Installer::run' ) );
\add_action( 'plugins_loaded', ns( 'Installer::checkDatabase' ) );
