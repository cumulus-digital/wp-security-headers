<?php
/**
 * Activation of CSP.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class Mode extends AbstractSettingsHandler {
	protected $tab = 'csp';

	protected $section = 'mode';

	public function __construct() {
		$this->setDefault( 'enabled', '' );
		$this->setDefault( 'in-admin', false );
		$this->setDefault( 'upgrade-insecure-requests', false );

		$this->addSettingsSection();
	}

	private function addSettingsSection() {
		SettingsRegister::addSettingsSection(
			array(
				'tab_id'        => $this->tab,
				'section_order' => 1,
				'section_id'    => $this->section,
				'section_title' => '<span id="' . $this->getSectionId() . '"></span>Operation Mode',
				'fields'        => array(
					array(
						'id'    => 'enabled',
						'title' => 'Enable CSP',
						'type'  => 'select',
						'desc'  => '
							<strong class="error-message">Be sure to test before enabling!</strong>
						',
						'choices' => array(
							''        => 'Disabled',
							'testing' => 'Reporting Only (Test Mode)',
							'enabled' => 'Enabled',
						),
						'default' => $this->getDefault( 'enabled' ),
					),
					array(
						'id'      => 'in-admin',
						'title'   => 'Enforce CSP in WordPress admin area',
						'type'    => 'toggle',
						'default' => $this->getDefault( 'in-admin' ),
						'show_if' => array(
							array(
								'field' => $this->getSectionId() . '_enabled',
								'value' => array( 'testing', 'enabled' ),
							),
						),
					),
					array(
						'id'    => 'warning',
						'title' => '',
						'type'  => 'html',
						'class' => 'error-message',
						'html'  => '
							WARNING! Enforcing Content Security Policy in the WordPress admin area
							may result in being locked out of your site!
						',
						'show_if' => array(
							array(
								array(
									'field' => $this->getSectionId() . '_enabled',
									'value' => array( 'testing', 'enabled' ),
								),
								array(
									'field' => $this->getSectionId() . '_in-admin',
									'value' => array( '1' ),
								),
							),
						),
					),
					array(
						'id'    => 'should-auto-nonce',
						'title' => '',
						'type'  => 'html',
						'html'  => '
							The WordPress admin may include scripts and styles in a way
							that basic auto-noncing cannot handle. You may wish to enable
							<a href="#csp_auto-nonce_enabled">Auto-Nonce</a> including the
							admin area, as well as experiment with
							<a href="#csp_auto-nonce_use_buffer">Parse Full Responses</a>.
						',
						'show_if' => array(
							array(
								array(
									'field' => $this->getSectionId() . '_enabled',
									'value' => array( 'testing', 'enabled' ),
								),
								array(
									'field' => $this->getSectionId() . '_in-admin',
									'value' => array( '1' ),
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Determines if the CSP is activated.
	 *
	 * @return bool
	 */
	public function isActive() {
		$enabled = $this->getSetting( 'enabled' );
		if ( $enabled && '' !== $enabled ) {
			if ( ! \is_admin() || \wp_doing_ajax() ) {
				return true;
			}

			if ( $this->getSetting( 'in-admin' ) ) {
				return true;
			}
		}

		return false;
	}

	public function validate( $input ) {
		$validations = array(
			array(
				'id'       => 'enabled',
				'callback' => function ( $val ) {
					if (
						! \in_array( $val, array(
							'',
							'testing',
							'enabled',
						) )
					) {
						\wp_die( 'Invalid value for CSP enabled' );
					}

					return $val;
				},
			),
		);

		$input = $this->runValidations( $validations, $input );

		return $input;
	}
}
