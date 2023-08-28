<?php
/**
 * Sends an upgrade insecure requests CSP header.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class UpgradeInsecure extends AbstractSettingsHandler {
	protected $tab = 'general';

	protected $section = 'upgrade-insecure';

	public function __construct() {
		$this->setDefault( 'enabled', false );

		$this->addSettingsSection();
	}

	private function addSettingsSection() {
		SettingsRegister::addSettingsSection(
			array(
				'tab_id'              => $this->tab,
				'section_id'          => $this->section,
				'section_title'       => 'Upgrade Insecure Requests',
				'section_description' => '
					<p>
						Request that browsers automatically upgrade requests to HTTPS.
						<strong>Note:</strong> This is not guaranteed, and does not replace <a href="#hsts">the HSTS
						setting</a>.
					</p>
				',
				'section_order' => 1,
				'fields'        => array(
					array(
						'id'      => 'enabled',
						'title'   => 'Enable',
						'type'    => 'toggle',
						'default' => $this->getDefault( 'enabled' ),
						'link'    => array(
							'url'      => \esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/upgrade-insecure-requests' ),
							'type'     => 'tooltip',
							'text'     => 'MDN Documentation ofr Upgrade-Insecure-Requests',
							'external' => true,
						),
					),
				),
			),
		);
	}
}
