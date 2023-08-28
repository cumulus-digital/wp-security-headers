<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\CSP;

use CUMULUS\Wordpress\SecurityHeaders\Actions\AbstractActor;
use function CUMULUS\Wordpress\SecurityHeaders\debug;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class Mode extends AbstractActor {
	/**
		 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\Mode
		 */
	protected $settings;

	public function __construct() {
		$this->settings = SettingsRegister::getHandler( 'csp/mode' );
	}

	public function sendHeaders() {
		if ( $this->isActive() ) {
			debug( 'CSP is ACTIVE.' );

			return;
		}
		debug( 'CSP is NOT active.' );
	}

	public function sendAdminHeaders() {
		if ( $this->settings->getSetting( 'in-admin' ) ) {
			$this->sendHeaders();
		}
	}
}
