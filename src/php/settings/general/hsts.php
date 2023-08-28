<?php
/**
 * Strict Transport Security.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class HSTS extends AbstractSettingsHandler {
	protected $tab = 'general';

	protected $section = 'hsts';

	public function __construct() {
		$this->setDefaults( array(
			'enabled'            => false,
			'max-age'            => 31536000,
			'include-subdomains' => false,
			'preload'            => false,
		) );

		$this->addSettingsSection();
	}

	private function addSettingsSection() {
		SettingsRegister::addSettingsSection(
			array(
				'tab_id'              => $this->tab,
				'section_id'          => $this->section,
				'section_title'       => '<span id="hsts"></span>Strict Transport Security (HSTS)',
				'section_order'       => 1,
				'section_description' => '
					<p>
						Inform browsers that this site should only be accessed using HTTPS
					</p>
				',
				'fields' => array(
					array(
						'id'      => 'enabled',
						'title'   => 'Enable HSTS',
						'type'    => 'toggle',
						'default' => $this->getDefault( 'enabled' ),
						'link'    => array(
							'url'      => \esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security' ),
							'type'     => 'tooltip',
							'text'     => 'MDN Documentation for Strict-Transport-Security',
							'external' => true,
						),
					),
					array(
						'id'      => 'max-age',
						'title'   => 'Max-Age',
						'desc'    => 'Time, in seconds, that browsers should remember to only use HTTPS',
						'default' => $this->getDefault( 'max-age' ),
						'show_if' => array(
							array(
								'field' => $this->getSectionId() . '_enabled',
								'value' => array( '1' ),
							),
						),
					),
					array(
						'id'      => 'include-subdomains',
						'title'   => 'Include Subdomains',
						'type'    => 'toggle',
						'default' => $this->getDefault( 'include-subdomains' ),
						'show_if' => array(
							array(
								'field' => $this->getSectionId() . '_enabled',
								'value' => array( '1' ),
							),
						),
					),
					array(
						'id'      => 'preload',
						'title'   => 'Preload',
						'type'    => 'toggle',
						'default' => $this->getDefault( 'preload' ),
						'show_if' => array(
							array(
								'field' => $this->getSectionId() . '_enabled',
								'value' => array( '1' ),
							),
						),
						'link' => array(
							'url'      => \esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security#preloading_strict_transport_security' ),
							'type'     => 'tooltip',
							'text'     => 'MDN Documentation for HSTS "preload"',
							'external' => true,
						),
					),
				),
			),
		);
	}
}
