<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Settings;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use const CUMULUS\Wordpress\SecurityHeaders\PREFIX;

class SettingsRegister {
	/**
		 * Holds reference to WPSF instance.
		 */
	private static $wpsf;

	/**
	 * Holds registered settings handlers.
	 */
	private static $handlers = array();

	/**
	 * Holds default values.
	 */
	public static $defaults = array();

	/**
	 * Any initialization needed for settings.
	 */
	public static function init() {
		$wpsf = self::wpsf();
	}

	/**
	 * Retrieve and/or create the WordpressSettingsFramework object.
	 *
	 * @return Framework
	 */
	public static function wpsf() {
		if ( ! self::$wpsf ) {
			self::$wpsf = new Framework( null, PREFIX );

			// Will handle validation of settings in each Handler
			\add_filter( PREFIX . '_settings_validate', __CLASS__ . '::validateSettings' );
		}

		return self::$wpsf;
	}

	/**
	 * Add a tab to the settings page.
	 *
	 * @param array $tab
	 */
	public static function addSettingsTab( $tab ) {
		\add_filter( 'wpsf_register_settings_' . PREFIX, function ( $WPSF ) use ( $tab ) {
			$WPSF['tabs'][] = $tab;

			return $WPSF;
		} );
	}

	/**
	 * Add a section to the settings page.
	 *
	 * @param array $settings
	 * @param mixed $section
	 */
	public static function addSettingsSection( $section ) {
		\add_filter( 'wpsf_register_settings_' . PREFIX, function ( $WPSF ) use ( $section ) {
			$WPSF['sections'][] = $section;

			return $WPSF;
		} );
	}

	/**
	 * Set the default value for a settings field.
	 *
	 * @param string $section
	 * @param string $field
	 * @param mixed  $default
	 *
	 * @return void
	 */
	public static function setDefault( $section, $field, $default ) {
		if ( ! isset( self::$defaults[$section] ) ) {
			self::$defaults[$section] = array(
				$field => $default,
			);
		} else {
			self::$defaults[$section][$field] = $default;
		}
	}

	/**
	 * Retrieve the default value for a settings field.
	 *
	 * @param string $section
	 * @param string $field
	 *
	 * @return mixed
	 */
	public static function getDefault( $section, $field ) {
		if ( isset( self::$defaults[$section], self::$defaults[$section][$field] ) ) {
			return self::$defaults[$section][$field];
		}

		return;
	}

	/**
	 * Retrieve the value of a setting, or its default if not set.
	 * If there is no value or it is not set, return false.
	 *
	 * @param string $section
	 * @param string $field
	 *
	 * @return mixed|false
	 */
	public static function getSetting( $section, $field ) {
		$options = \get_option( PREFIX . '_settings' );
		$default = self::getDefault( $section, $field );
		$key     = "{$section}_{$field}";

		if ( \is_array( $options ) && \array_key_exists( $key, $options ) ) {
			if ( \is_bool( $default ) ) {
				return \boolval( $options[$key] );
			}

			return $options[$key];
		}

		if ( null !== $default ) {
			return $default;
		}

		return false;
	}

	/**
	 * Register a handler for a settings section.
	 *
	 * @param string $key
	 * @param <SettingsHandler> $handler
	 *
	 * @return void
	 */
	public static function registerHandler( $key, $handler ) {
		self::$handlers[$key] = $handler;
	}

	/**
	 * Retrieve a registered handler.
	 *
	 * @param string $key
	 *
	 * @return <SettingsHandler>
	 */
	public static function getHandler( $key ) {
		return self::$handlers[$key];
	}

	/**
	 * Calls the validate() method of all registered Handlers, passing
	 * only that Handler's settings to the method.
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public static function validateSettings( $input ) {
		foreach ( self::$handlers as $handler ) {
			if ( \method_exists( $handler, 'validate' ) && \is_callable( array( $handler, 'validate' ) ) ) {
				$section       = $handler->getSectionId();
				$handlers_data = \array_filter(
					$input,
					function ( $key ) use ( $section ) {
						return \str_starts_with( $key, $section . '_' );
					},
					\ARRAY_FILTER_USE_KEY
				);
				$new_input = $handler->validate( $handlers_data );

				if ( $new_input ) {
					$input = \array_merge( $input, $new_input );
				}
			}
		}

		return $input;
	}
}
