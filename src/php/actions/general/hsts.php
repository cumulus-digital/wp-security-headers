<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\General;

use CUMULUS\Wordpress\SecurityHeaders\Actions\AbstractActor;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class HSTS extends AbstractActor {
	/**
	 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General\HSTS
	 */
	protected $settings;

	public function __construct() {
		$this->settings = SettingsRegister::getHandler( 'general/hsts' );
	}

	public function sendHeaders() {
		if ( $this->getSetting( 'enabled' ) ) {
			$maxage             = $this->getSetting( 'max-age' );
			$include_subdomains = $this->getSetting( 'include-subdomains' );
			$preload            = $this->getSetting( 'preload' );
			$value              = "max-age={$maxage}";

			if ( $include_subdomains ) {
				$value .= '; includeSubdomains';
			}

			if ( $preload ) {
				$value .= '; preload';
			}

			\header( "Strict-Transport-Security: {$value}", true );
		}
	}
}
