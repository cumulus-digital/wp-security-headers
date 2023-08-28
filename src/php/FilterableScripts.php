<?php

namespace CUMULUS\Wordpress\SecurityHeaders;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;
use WP_Scripts;

/**
 * This class extends the default WP_Scripts class in order to
 * override print_extra_scripts and allow our code to modify
 * the inline script tags it generates.
 */
class FilterableScripts extends WP_Scripts {
	private $type_attr = '';

	/**
	 * Executes the parent class constructor and initialization, then copies in the
	 * pre-existing $wp_scripts contents.
	 */
	public function __construct() {
		parent::__construct();

		if (
			\function_exists( 'is_admin' )
			&& ! \is_admin()
			&& \function_exists( 'current_theme_supports' )
			&& ! \current_theme_supports( 'html5', 'script' )
		) {
			$this->type_attr = " type='text/javascript'";
		}

		/*
		 * Copy the contents of existing $wp_scripts into the new one.
		 * This is needed for numerous plug-ins that do not play nice.
		 *
		 * https://wordpress.stackexchange.com/a/284495/198117
		 */
		if ( $GLOBALS['wp_scripts'] instanceof WP_Scripts ) {
			$missing_scripts = \array_diff_key( $GLOBALS['wp_scripts']->registered, $this->registered );

			foreach ( $missing_scripts as $mscript ) {
				$this->registered[ $mscript->handle ] = $mscript;
			}
		}
	}

	public function print_extra_script( $handle, $display = true ) {
		if ( ! $display ) {
			return parent::print_extra_script( $handle, $display );
		}

		$prefix = PREFIX;

		/**
		 * @var \Cumulus\Wordpress\SecurityHeaders\Settings\Section\CSP\AutoNonce
		 */
		$autoNonceHandler = SettingsRegister::getHandler( 'csp/auto_nonce' );

		if (
			! $autoNonceHandler->isActive()
			|| ! $autoNonceHandler->isDirectiveEnabled( 'script-src' )
		) {
			return parent::print_extra_script( $handle, $display );
		}

		\ob_start();
		parent::print_extra_script( $handle, true );
		$output = \ob_get_clean();

		$handled_output = \apply_filters(
			"{$prefix}_filter_scripts",
			$output,
			$handle
		);

		// debug( array( 'caught', $handle, $handled_output ) );
		echo $handled_output;

		return true;
	}
}
