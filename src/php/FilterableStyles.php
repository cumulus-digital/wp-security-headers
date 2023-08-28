<?php
/**
 * NOTE: Don't use this. load_style_tag filter works.
 */

namespace CUMULUS\Wordpress\SecurityHeaders;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;
use WP_Styles;

/**
 * This class extends the default WP_Styles class in order to
 * override print_inline_style and allow our code to modify
 * the inline style tags it generates.
 */
class FilterableStyles extends WP_Styles {
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
			&& ! \current_theme_supports( 'html5', 'style' )
		) {
			$this->type_attr = " type='text/css'";
		}

		/*
		 * Copy the contents of existing $wp_scripts into the new one.
		 * This is needed for numerous plug-ins that do not play nice.
		 *
		 * https://wordpress.stackexchange.com/a/284495/198117
		 */
		if ( \array_key_exists( 'wp_styles', $GLOBALS ) && $GLOBALS['wp_styles'] instanceof WP_Styles ) {
			$missing_styles = \array_diff_key( $GLOBALS['wp_styles']->registered, $this->registered );

			foreach ( $missing_styles as $mstyle ) {
				$this->registered[ $mstyle->handle ] = $mstyle;
			}
		}
	}

	public function do_item( $handle, $group = false ) {
		return parent::do_item( $handle, $group );
		/**
		 * @var \Cumulus\Wordpress\SecurityHeaders\Settings\Section\CSP\AutoNonce
		 */
		$autoNonceHandler = SettingsRegister::getHandler( 'csp/auto_nonce' );

		if (
			! $autoNonceHandler->isActive()
			|| ! $autoNonceHandler->isDirectiveEnabled( 'style-src' )
		) {
			return parent::do_item( $handle, $group );
		}

		\ob_start();
		$returned = parent::do_item( $handle, $group );
		$output   = \ob_get_clean();

		if ( false === $returned ) {
			return false;
		}
		$prefix = PREFIX;
		$output = \apply_filters(
			"{$prefix}_filter_styles",
			$output,
			$handle
		);

		echo "<!-- Filtered! -->\n";
		echo $output;
	}

	// public function print_inline_style( $handle, $display = true )
	// {
	// 	return parent::print_inline_style( $handle, $display );

	// 	if ( ! $display ) {
	// 		return parent::print_inline_style( $handle, $display );
	// 	}

	// 	$prefix = \CUMULUS\Wordpress\SecurityHeaders\PREFIX;

	// 	/**
	// 	 * @var \Cumulus\Wordpress\SecurityHeaders\Settings\CSPAutoNonce
	// 	 */
	// 	$autoNonceHandler = Settings::getHandler( 'csp-auto_nonce' );

	// 	if (
	// 		! $autoNonceHandler->isActive()
	// 		|| ! $autoNonceHandler->isDirectiveEnabled( 'style-src' )
	// 	) {
	// 		return parent::print_inline_style( $handle, $display );
	// 	}

	// 	\ob_start();
	// 	$returned = parent::print_inline_style( $handle, $display );
	// 	$output   = \ob_get_clean();

	// 	\do_action( 'qm/debug', ['WTF', $handle, $display, $returned, $output] );

	// 	$output = \apply_filters(
	// 		"{$prefix}_filter_styles",
	// 		$output,
	// 		$handle
	// 	);

	// 	echo "<!-- Filtered! -->\n";
	// 	echo $output;

	// 	return true;
	// }
}
