<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class ActionsRegister {
	private static $initialized = false;

	private static $actors = array();

	public static function init() {
		if ( self::$initialized ) {
			return;
		}

		\add_action( 'wp_headers', __CLASS__ . '::filterHeaders', \PHP_INT_MAX );
		\add_action( 'send_headers', __CLASS__ . '::sendHeaders', \PHP_INT_MAX );
		\add_action( 'admin_init', __CLASS__ . '::sendAdminHeaders', \PHP_INT_MAX );

		self::$initialized = true;
	}

	/**
	 * Register an actor.
	 *
	 * @param string $key
	 * @param Actor  $actor
	 */
	public static function registerActor( $key, $actor ) {
		self::init();
		self::$actors[$key] = $actor;
	}

	/**
	 * Retrieve a registered actor.
	 *
	 * @param string $key
	 *
	 * @return object
	 */
	public static function getActor( $key ) {
		self::init();

		return self::$actors[$key];
	}

	/**
	 * Calls the filterHeaders() method of all registered actors.
	 *
	 * @param array $headers
	 *
	 * @return array
	 */
	public static function filterHeaders( $headers ) {
		if ( $headers ) {
			foreach ( self::$actors as $actor ) {
				if (
					\method_exists( $actor, 'filterHeaders' )
					&& \is_callable( array( $actor, 'filterHeaders' ), true )
				) {
					$new_headers = $actor->filterHeaders( $headers );
					if ( $new_headers && \count( $new_headers ) ) {
						$headers = $new_headers;
					}
				}
			}
		}

		return $headers;
	}

	/**
	 * Calls the sendHeaders() method of all registered actors.
	 */
	public static function sendHeaders() {
		foreach ( self::$actors as $actor ) {
			if (
				\method_exists( $actor, 'sendHeaders' )
				&& \is_callable( array( $actor, 'sendHeaders' ), true )
			) {
				$actor->sendHeaders();
			}
		}
	}

	/**
	 * Calls the adminHeaders() method of all registered actors.
	 */
	public static function sendAdminHeaders() {
		foreach ( self::$actors as $actor ) {
			if (
				\method_exists( $actor, 'sendAdminHeaders' )
				&& \is_callable( array( $actor, 'sendAdminHeaders' ), true )
			) {
				$actor->sendAdminHeaders();
			}
		}
	}
}
