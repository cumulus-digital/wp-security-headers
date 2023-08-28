<?php
/**
 * Sends an upgrade insecure requests CSP header.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class ExportOptions extends AbstractSettingsHandler {
	protected $tab = 'general';

	protected $section = 'export-options';

	public function __construct() {
		$this->addSettingsSection();
	}

	private function addSettingsSection() {
		SettingsRegister::addSettingsSection(
			array(
				'tab_id'        => $this->tab,
				'section_id'    => $this->section,
				'section_title' => 'Import/Export',
				'section_order' => \PHP_INT_MAX,
				'fields'        => array(
					array(
						'id'    => 'export',
						'title' => 'Export Settings',
						'type'  => 'export',
					),
					array(
						'id'    => 'import',
						'title' => 'Import Settings',
						'type'  => 'import',
					),
				),
			),
		);
	}
}
