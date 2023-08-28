<?php
/**
 * Initialize and load sections for general settings.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

SettingsRegister::addSettingsTab( array(
	'id'    => 'general',
	'title' => 'General',
) );

require __DIR__ . '/hsts.php';
SettingsRegister::registerHandler( 'general/hsts', new HSTS() );

require __DIR__ . '/upgrade-insecure.php';
SettingsRegister::registerHandler( 'general/upgrade_insecure', new UpgradeInsecure() );

require __DIR__ . '/content.php';
SettingsRegister::registerHandler( 'general/content', new Content() );

require __DIR__ . '/mask-headers.php';
SettingsRegister::registerHandler( 'general/mask_headers', new MaskHeaders() );

require __DIR__ . '/uninstall-options.php';
SettingsRegister::registerHandler( 'general/uninstall_options', new UninstallOptions() );

require __DIR__ . '/export-options.php';
SettingsRegister::registerHandler( 'general/export_options', new ExportOptions() );
