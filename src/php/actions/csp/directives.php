<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\CSP;

use CUMULUS\Wordpress\SecurityHeaders\Actions\AbstractActor;
use function CUMULUS\Wordpress\SecurityHeaders\debug;
use const CUMULUS\Wordpress\SecurityHeaders\PREFIX;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class Directives extends AbstractActor {
	/**
		 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\Directives
		 */
	protected $settings;

	public function __construct() {
		$this->settings = SettingsRegister::getHandler( 'csp/directives' );
	}

	public function sendHeaders() {
		/**
		 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\Mode
		 */
		$csp_mode = SettingsRegister::getHandler( 'csp/mode' );

		if ( ! $csp_mode->isActive() ) {
			return;
		}

		$header = 'Content-Security-Policy';

		if ( 'testing' === $csp_mode->getSetting( 'enabled' ) ) {
			$header = 'Content-Security-Policy-Report-Only';
		}

		debug( 'Sending CSP header' );
		\header( $header . ': ' . \implode( '; ', $this->generatePoliciesArray() ), false );
	}

	public function sendAdminHeaders() {
		/**
		 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\Mode
		 */
		$csp_mode = SettingsRegister::getHandler( 'csp/mode' );

		if ( $csp_mode->getSetting( 'in-admin' ) ) {
			$this->sendHeaders();
		}
	}

	public function generatePoliciesArray() {
		/**
		 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\Mode
		 */
		$csp_mode = SettingsRegister::getHandler( 'csp/mode' );

		$all_directives     = $this->settings->getDirectives();
		$enabled_directives = $this->settings->getEnabledDirectives();

		$default_src = array();

		// Need to be sure we filter default-src last
		if ( \array_key_exists( 'default-src', $enabled_directives ) ) {
			$default_src = $enabled_directives['default-src'];
			unset( $enabled_directives['default-src'] );
		}

		$final_policies = array();

		// A directive which is not enabled may still set a filter on default-src,
		// so we have to process filters for ALL directives.
		foreach ( $all_directives as $directive => $policy ) {
			$filtered_policy = $this->sanitizePolicies( $directive, $policy );

			if ( \array_key_exists( $directive, $enabled_directives ) ) {
				$final_policies[$directive] = $filtered_policy;
			}
		}

		$default_src_policy = $this->sanitizePolicies( 'default-src', $default_src );

		if ( $default_src_policy ) {
			\array_unshift( $final_policies, $default_src_policy );
		}

		$prefix         = PREFIX;
		$final_policies = \apply_filters( "{$prefix}_csp_final", $final_policies );

		return $final_policies;
	}

	public function sanitizePolicies( $directive, $policies ) {
		// Apply filters
		$policies = $this->getFilteredPolicies( $directive );

		if ( \count( $policies ) ) {
			// Remove or replace special characters
			$policies = \str_replace(
				array( "\r", "\n", "\t", ';', ',' ),
				array( '', '', ' ', '%3B', '%2C' ),
				$policies
			);

			return $directive . ' ' . \implode( ' ', $policies );
		}

		return $directive;
	}

	public function getFilteredPolicies( $directive ) {
		$prefix = \CUMULUS\Wordpress\SecurityHeaders\PREFIX;

		$policies = $this->settings->getPolicies( $directive );

		if ( \has_filter( "{$prefix}_csp_{$directive}" ) ) {
			$policies = \apply_filters(
				"{$prefix}_csp_{$directive}",
				$policies,
				$directive
			);
		}

		if ( \has_filter( "{$prefix}_csp_all" ) ) {
			$policies = \apply_filters(
				"{$prefix}_csp_all",
				$policies,
				$directive
			);
		}

		return $policies;
	}
}
