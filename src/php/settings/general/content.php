<?php
/**
 * Strict Transport Security.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class Content extends AbstractSettingsHandler {
	protected $tab = 'general';

	protected $section = 'content';

	public function __construct() {
		$this->setDefaults( array(
			'x-frame-options'        => '',
			'x-content-type-options' => false,
		) );

		$this->addSettingsSection();
	}

	private function addSettingsSection() {
		SettingsRegister::addSettingsSection(
			array(
				'tab_id'        => $this->tab,
				'section_id'    => $this->section,
				'section_title' => 'Content',
				'section_order' => 2,
				'fields'        => array(
					array(
						'id'       => 'x-frame-options',
						'title'    => 'X-Frame-Options',
						'subtitle' => 'Control where this site can be included in an iframe',
						'type'     => 'select',
						'choices'  => array(
							''           => 'Allow all',
							'SAMEORIGIN' => 'Only allow same origin',
							'DENY'       => 'Prevent all',
						),
						'default' => $this->getDefault( 'x-frame-options' ),
						'link'    => array(
							'url'      => \esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options' ),
							'type'     => 'tooltip',
							'text'     => 'MDN Documentation for X-Frame-Options header',
							'external' => true,
						),
					),
					array(
						'id'       => 'x-content-type-options',
						'title'    => 'X-Content-Type-Options',
						'subtitle' => 'Do not allow MIME type sniffing',
						'type'     => 'toggle',
						'default'  => $this->getDefault( 'x-content-type-options' ),
						'link'     => array(
							'url'      => \esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options' ),
							'type'     => 'tooltip',
							'text'     => 'MDN Documentation for X-Content-Type-Options header',
							'external' => true,
						),
					),
				),
			),
		);
	}

	public function validate( $input ) {
		$validations = array(
			array(
				'id'       => 'x-frame-options',
				'callback' => function ( $val ) {
					if (
						! \in_array( $val, array(
							'',
							'SAMEORIGIN',
							'DENY',
						) )
					) {
						\wp_die( 'Invalid value for x-frame-options' );
					}

					return $val;
				},
			),
		);

		$input = $this->runValidations( $validations, $input );

		return $input;
	}
}
