<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\General;

use CUMULUS\Wordpress\SecurityHeaders\Actions\AbstractActor;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class UpgradeInsecure extends AbstractActor {
	/**
	 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General\UpgradeInsecure
	 */
	protected $settings;

	public function __construct() {
		$this->settings = SettingsRegister::getHandler( 'general/upgrade_insecure' );
	}

	public function sendHeaders() {
		if ( $this->getSetting( 'enabled' ) ) {
			\header( 'Content-Security-Policy: upgrade-insecure-requests', false );
		}
	}
}
