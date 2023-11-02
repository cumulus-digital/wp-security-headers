<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\General;

use CUMULUS\Wordpress\SecurityHeaders\Actions\AbstractActor;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class MaskHeaders extends AbstractActor {
	/**
	 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General\MaskHeaders
	 */
	protected $settings;

	public function __construct() {
		$this->settings = SettingsRegister::getHandler( 'general/mask_headers' );
	}

	public function sendHeaders() {
		if ( $this->getSetting( 'x-powered-by' ) ) {
			@\ini_set( 'expose_php', 'off' );
			\header( 'X-Powered-By: Unicorns', true );
		}

		if ( $this->getSetting( 'server' ) ) {
			\header( 'Server: Rainbows', true );
		}
	}
}
