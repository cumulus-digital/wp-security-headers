<?php

namespace CUMULUS\Wordpress\SecurityHeaders;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class Uninstaller {
	public static function run() {
		$optionsHandler = SettingsRegister::getHandler( 'general/uninstall_options' );
		if ( $optionsHandler->getSetting( 'drop_table' ) ) {
			self::removeDatabase();
		}
		if ( $optionsHandler->getSetting( 'wipe_settings' ) ) {
			self::removeOptions();
		}
	}

	public static function removeDatabase() {
		global $wpdb;
		$table_name = $wpdb->prefix . PREFIX . Installer::$table_name;
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
		\delete_option( PREFIX . Installer::$version_key );
	}

	public static function removeOptions() {
		\delete_option( PREFIX . '_settings' );
	}
}
\register_uninstall_hook( PLUGIN, ns( 'Uninstaller::run' ) );
