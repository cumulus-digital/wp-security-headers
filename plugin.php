<?php

namespace CUMULUS\Wordpress\SecurityHeaders;

/*
 * Plugin Name: Wordpress Security Headers
 * Plugin URI: https://github.com/cumulus-digital/wp-security-headers
 * Github Plugin URI: https://github.com/cumulus-digital/wp-security-headers
 * Primary Branch: main
 * Description: Control several security-related HTTP features including a rudamentary CSP manager with auto-nonce capability.
 * Author: vena
 * License: UNLICENSED
 * Version: 1.0.8
 */

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

require_once __DIR__ . '/src/php/helpers.php';

\define( 'CUMULUS\Wordpress\SecurityHeaders\DEBUG', true );

\define( 'CUMULUS\Wordpress\SecurityHeaders\PLUGIN', __FILE__ );
\define( 'CUMULUS\Wordpress\SecurityHeaders\BASEDIR', \plugin_dir_path( __FILE__ ) );
\define( 'CUMULUS\Wordpress\SecurityHeaders\BASEURL', \plugin_dir_url( __FILE__ ) );
\define( 'CUMULUS\Wordpress\SecurityHeaders\PREFIX', 'cmls_wpsh' );

require_once BASEDIR . '/vendor-prefixed/autoload.php';
require_once BASEDIR . '/vendor-prefixed/jamesckemp/wordpress-settings-framework/wp-settings-framework.php';
require_once BASEDIR . '/src/php/index.php';

\add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$prefix  = PREFIX;
	$baseurl = BASEURL;

	if ( $hook !== 'settings_page_' . \str_replace( '_', '-', $prefix ) . '-settings' ) {
		return;
	}

	$assets = require BASEDIR . '/build/backend.asset.php';

	\wp_enqueue_style( "{$prefix}-styles", "{$baseurl}/build/backend.css" );
	\wp_enqueue_script( "{$prefix}-styles", "{$baseurl}/build/backend.js", $assets['dependencies'], $assets['version'], true );
} );
