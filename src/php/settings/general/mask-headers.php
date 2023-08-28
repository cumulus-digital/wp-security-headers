<?php
/**
 * Allows attempting to mask or remove headers.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class MaskHeaders extends AbstractSettingsHandler {
	protected $tab = 'general';

	protected $section = 'mask-headers';

	public function __construct() {
		$this->setDefaults( array(
			'x-powered-by' => true,
			'server'       => true,
		) );

		$this->addSettingsSection();
	}

	private function addSettingsSection() {
		SettingsRegister::addSettingsSection(
			array(
				'tab_id'              => $this->tab,
				'section_id'          => $this->section,
				'section_title'       => 'Mask Headers',
				'section_description' => '
					<p>
						<em>Attempt</em> to mask sensitive headers. It is usually better to do
						this in your server configuration.
					</p>
				',
				'section_order' => 2,
				'fields'        => array(
					array(
						'id'       => 'x-powered-by',
						'title'    => 'X-Powered-By',
						'subtitle' => 'X-Powered-By may expose PHP version',
						'type'     => 'toggle',
						'default'  => $this->getDefault( 'x-powered-by' ),
					),
					array(
						'id'       => 'server',
						'title'    => 'Server',
						'subtitle' => 'May expose webserver version. <strong>Not normally removable by PHP!</strong>',
						'type'     => 'toggle',
						'default'  => $this->getDefault( 'server' ),
					),
				),
			),
		);
	}
}
