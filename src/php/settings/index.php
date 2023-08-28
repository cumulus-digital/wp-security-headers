<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Settings;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

require_once __DIR__ . '/Framework.php';
require_once __DIR__ . '/AbstractSettingsHandler.php';
require_once __DIR__ . '/SettingsRegister.php';

require_once __DIR__ . '/general/index.php';
require_once __DIR__ . '/csp/index.php';
require_once __DIR__ . '/reports/index.php';

SettingsRegister::init();

\add_action( 'admin_menu', function () {
	SettingsRegister::wpsf()->add_settings_page( array(
		'parent_slug' => 'options-general.php',
		'page_title'  => 'Security Headers',
		'menu_title'  => 'Security Headers',
		'capability'  => 'switch_themes',
	) );
} );
