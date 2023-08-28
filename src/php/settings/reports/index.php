<?php
/**
 * Initialize and load Reportings tab.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\Reports;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

SettingsRegister::addSettingsTab( array(
	'id'    => 'reports',
	'title' => 'Reports',
) );

require __DIR__ . '/dashboard.php';
SettingsRegister::registerHandler( 'reports/dashboard', new Dashboard() );
