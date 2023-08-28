<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\General;

use CUMULUS\Wordpress\SecurityHeaders\Actions\AbstractActor;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class Content extends AbstractActor {
	/**
	 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General\Content
	 */
	protected $settings;

	public function __construct() {
		$this->settings = SettingsRegister::getHandler( 'general/content' );
	}

	public function sendHeaders() {
		$xfo = $this->getSetting( 'x-frame-options' );

		if ( $xfo && '' !== $xfo ) {
			\header( "X-Frame-Options: {$xfo}" );
		}

		if ( $this->getSetting( 'x-content-type-options' ) ) {
			\header( 'X-Content-Type-Options: nosniff' );
		}
	}
}
