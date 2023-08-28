<?php
/**
 * Initialize and load sections for Content Security Policy settings.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

SettingsRegister::addSettingsTab( array(
	'id'    => 'csp',
	'title' => 'Content Security Policy',
) );

require __DIR__ . '/mode.php';
SettingsRegister::registerHandler( 'csp/mode', new Mode() );

require __DIR__ . '/reporting.php';
SettingsRegister::registerHandler( 'csp/reporting', new Reporting() );

require __DIR__ . '/directives.php';
SettingsRegister::registerHandler( 'csp/directives', new Directives() );

require __DIR__ . '/auto-nonce.php';
SettingsRegister::registerHandler( 'csp/auto_nonce', new AutoNonce() );
