<?php
/**
 * Settings for individual CSP directives.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class Directives extends AbstractSettingsHandler {
	protected $tab = 'csp';

	protected $section = 'policies';

	protected static $defaults = array(
		'default-src' => array(
			'enabled'  => true,
			'policies' => array( "'self'" ),
		),
		'base-uri' => array(
			'enabled'  => true,
			'policies' => array( "'self'" ),
		),
		'script-src' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'style-src' => array(
			'enabled'  => true,
			'policies' => array( "'self'", "'unsafe-inline'" ),
		),
		'img-src' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'font-src' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'connect-src' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'media-src' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'object-src' => array(
			'enabled'  => true,
			'policies' => array( "'none'" ),
		),
		'child-src' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'frame-src' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'worker-src' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'manifest-src' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'base-uri' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'form-action' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
		'frame-ancestors' => array(
			'enabled'  => false,
			'policies' => array( "'self'" ),
		),
	);

	public function __construct() {
		$this->addSettingsSection();
	}

	/**
	 * Sets up settings section for AutoNonce.
	 *
	 * @return void
	 */
	private function addSettingsSection() {
		$defaultDirectives = $this->getDefaultDirectives();

		foreach ( $defaultDirectives as $directive => $default ) {
			$this->setDefault( $directive, $default['enabled'] );
			$fields[] = array(
				'id'      => $directive,
				'title'   => $directive,
				'type'    => 'toggle',
				'default' => $default['enabled'],
				'link'    => array(
					'url'      => "https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/{$directive}",
					'type'     => 'tooltip',
					'text'     => "MDN documentation for {$directive}",
					'external' => true,
				),
			);

			$policies = array();

			for ( $i = 0; $i < \count( $default['policies'] ); $i++ ) {
				$policies[] = array(
					'row_id' => $i,
					'policy' => $default['policies'][$i],
				);
			}

			$this->setDefault( "{$directive}-policy", $policies );

			$fields[] = array(
				'id'        => "{$directive}-policy",
				'title'     => '',
				'type'      => 'group',
				'class'     => 'policies',
				'default'   => $policies,
				'subfields' => array(
					array(
						'id'         => 'policy',
						'title'      => 'Value',
						'type'       => 'text',
						'class'      => 'medium',
						'attributes' => array(
							'title'   => 'One policy value per row, no spaces or invalid characters.',
							'pattern' => '[^\s\@;,]+',
						),
					),
					array(
						'id'    => 'note',
						'title' => 'Note',
						'type'  => 'text',
						'class' => 'medium',
					),
				),
				'show_if' => array(
					array(
						'field' => $this->getSectionId() . '_' . $directive,
						'value' => array( '1' ),
					),
				),
			);
		}

		SettingsRegister::addSettingsSection(
			array(
				'tab_id'              => $this->tab,
				'section_order'       => 3,
				'section_id'          => $this->section,
				'section_title'       => '<span id="' . $this->getSectionId() . '"></span>Directives and Policies',
				'section_description' => '
					<p>
						One policy per row. Keyword and hash values should be entered <em>with single quotes</em>, host values without.
						<a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/Sources" target="_blank>
							For more information about policy values, see MDN.
						</a>
					</p>
					<p>
						Disabled policies may still be covered by <nobr>"default-src"</nobr>, however note that
						directives <em><strong>do not</strong></em> cascade. An enabled
						directive will completely override <nobr>"default-src"</nobr> policies for its covered scope.
					</p>
				',
				'fields' => $fields,
			),
		);
	}

	/**
	 * Retrieve default directives and their policies.
	 *
	 * @return array
	 */
	public static function getDefaultDirectives() {
		return self::$defaults;
	}

	/**
	 * Check if a directive is enabled.
	 *
	 * @param string $directive
	 *
	 * @return bool
	 */
	public function isDirectiveEnabled( $directive ) {
		$prefix = \CUMULUS\Wordpress\SecurityHeaders\PREFIX;

		if (
			! \apply_filters( "{$prefix}_csp_disable_{$directive}", false )
			&& $this->getSetting( $directive )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve all current directive policies.
	 *
	 * @param bool $only_enabled When true, only enabled directives will be returned
	 *
	 * @return array
	 */
	public function getDirectives( $only_enabled = false ) {
		if ( $only_enabled ) {
			return $this->getEnabledDirectives();
		}

		$directives = array();

		foreach ( \array_keys( $this->getDefaultDirectives() ) as $directive ) {
			$directives[$directive] = $this->getPolicies( $directive );
		}

		return $directives;
	}

	/**
	 * Retrieve enabled directives.
	 *
	 * @return array
	 */
	public function getEnabledDirectives() {
		$enabled_directives = array();

		foreach ( \array_keys( $this->getDefaultDirectives() ) as $directive ) {
			if ( $this->isDirectiveEnabled( $directive ) ) {
				$policies = $this->getPolicies( $directive );

				if ( $policies && \count( $policies ) ) {
					$enabled_directives[$directive] = $policies;
				}
			}
		}

		return $enabled_directives;
	}

	/**
	 * Retrieve the current policies for a directive.
	 *
	 * @param string $directive
	 *
	 * @return array
	 */
	public function getPolicies( $directive ) {
		$policies = $this->getSetting( "{$directive}-policy" );

		if ( \count( $policies ) ) {
			return \array_column( $policies, 'policy' );
		}

		return array();
	}

	public function getFilteredPolicies( $directive ) {
		$prefix = \CUMULUS\Wordpress\SecurityHeaders\PREFIX;

		$policies = $this->getPolicies( $directive );

		if ( \has_filter( "{$prefix}_csp_{$directive}" ) ) {
			$policies = \apply_filters(
				"{$prefix}_csp_{$directive}",
				$policies,
				$directive
			);
		}

		if ( \has_filter( "{$prefix}_csp_all" ) ) {
			$policies = \apply_filters(
				"{$prefix}_csp_all",
				$policies,
				$directive
			);
		}

		return $policies;
	}

	/**
	 * Checks if a specific policy is set in a directive. It will only match
	 * WHOLE strings.
	 *
	 * @param string $directive
	 * @param string $policy
	 *
	 * @return bool
	 */
	public function policyContains( $directive, $policy ) {
		return \in_array( $policy, $this->getPolicies( $directive ) );
	}

	public function validate( $input ) {
		$return_input = array();

		foreach ( \array_keys( $input ) as $key ) {
			if ( \is_array( $input[$key] ) && \array_key_exists( 'policy', $input[$key][0] ) ) {
				// Policy row
				$output = array();

				// Reset row ids, trim and discard empties
				$i = 0;
				foreach ( $input[$key] as $row ) {
					$row['policy'] = \trim( $row['policy'] );

					if ( '' !== $row['policy'] ) {
						$row['row_id'] = $i;
						$output[]      = $row;
						$i++;
					}
				}
				$return_input[$key] = $output;
			} else {
				if ( false === \intval( $input[$key] ) ) {
					\wp_die( 'Invalid value for key ' . $key );

					continue;
				}
				$return_input[$key] = $input[$key];
			}
		}

		return $return_input;
	}
}
