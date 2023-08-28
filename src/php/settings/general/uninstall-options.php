<?php
/**
 * Sends an upgrade insecure requests CSP header.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class UninstallOptions extends AbstractSettingsHandler {
	protected $tab = 'general';

	protected $section = 'uninstall-options';

	public function __construct() {
		$this->setDefault( 'wipe', false );

		$this->addSettingsSection();
	}

	private function addSettingsSection() {
		SettingsRegister::addSettingsSection(
			array(
				'tab_id'              => $this->tab,
				'section_id'          => $this->section,
				'section_title'       => 'Uninstall Options',
				'section_description' => '
					By default, settings and reports will not be deleted when
					uninstalling this plugin. Toggle below to delete when uninstalling.
				',
				'section_order' => \PHP_INT_MAX,
				'fields'        => array(
					array(
						'id'      => 'wipe_settings',
						'title'   => 'Wipe Settings',
						'type'    => 'toggle',
						'default' => $this->getDefault( 'wipe_settings' ),
						'desc'    => 'Delete all plugin settings.',
					),
					array(
						'id'      => 'drop_table',
						'title'   => 'Remove Reports Table',
						'type'    => 'toggle',
						'default' => $this->getDefault( 'drop_table' ),
						'desc'    => 'Delete reports from database.',
					),
				),
			),
		);
	}
}
