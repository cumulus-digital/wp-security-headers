<?php
/**
 * Automatically add nonces to content and CSP header.
 */

namespace CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Actions\ActionsRegister;
use const CUMULUS\Wordpress\SecurityHeaders\PREFIX;
use CUMULUS\Wordpress\SecurityHeaders\Settings\AbstractSettingsHandler;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

class AutoNonce extends AbstractSettingsHandler {
	protected $tab = 'csp';

	protected $section = 'auto-nonce';

	protected $nonce;

	/**
	 * Directives elligible for auto-noncing.
	 */
	protected static $directives = array(
		'script-src',
		'style-src',
	);

	public function __construct() {
		$this->setDefault( 'enabled', '' );
		$this->setDefault( 'use_buffer', false );

		foreach ( self::$directives as $directive ) {
			$this->setDefault( $this->getDirectiveEnabledKey( $directive ), true );
		}

		$this->addSettingsSection();
	}

	/**
	 * Generate the settings key for a directive.
	 *
	 * @param string $directive
	 *
	 * @return string
	 */
	public function getDirectiveEnabledKey( $directive ) {
		return "{$directive}-enabled";
	}

	public function addSettingsSection() {
		$settings_section = array(
			'tab_id'              => $this->tab,
			'section_order'       => 4,
			'section_id'          => $this->section,
			'section_title'       => 'Auto-Nonce Scripts and Styles',
			'section_description' => '
				<p>
					Attempt to automatically add a nonce to scripts and styles
					affected by CSP directives.
				</p>

				<blockquote class="callout">
					<p>
						Using this feature implies that the output of registered scripts and styles
						from WordPress and plugins can be trusted.
					</p>
				</blockquote>

				<blockquote class="callout">
					<p>
						<strong>Using this feature along with front-end caching may be broken or insecure.</strong>
						If you are using caching, it is better to
						<a href="https://content-security-policy.com/hash/" target="_blank" rel="noopener">
							generate hashes for included resources
						</a>
						which require them and cannot be allowed by domain rules,
						and apply them to <a href="#csp_policies">directive policies</a> manually.
					</p>
				</blockquote>
				<p>
					A nonce will <strong>not</strong> be set for directives if its effective policy contains <nobr>\'unsafe-inline\'</nobr> or \'none\'
					with the exception of <nobr>script-src</nobr> if the effective policy contains <nobr>\'strict-dynamic\'</nobr>
				</p>
				<blockquote class="callout">
					<p>
						An example of a potential issue is the <nobr>\'style-src\'</nobr> directive, which applies to all inline
						style attributes, style tags, <em>and</em> link tags for external stylesheets. While a nonce could be applied
						to link tags, its presence in the style-src directive would override <nobr>\'unsafe-inline\'</nobr> and
						disallow style tags and attributes. On the other hand, inline styles cannot be nonced.
					</p>
				</blockquote>
				<p>
					If a directive is enabled here, but not in <a href="#csp_policies">CSP policies</a>, we
					will attempt to apply the nonce to the <nobr>\'default-src\'</nobr> directive. The above rules will apply to
					\'default-src\'.
				</p>
				<p>
					Only script, style, and link tags may be nonced. Tags which use style or event (e.g. "onclick")
					attributes cannot be nonced.
				</p>
				',
			'fields' => array(
				array(
					'id'      => 'enabled',
					'title'   => 'Enable',
					'type'    => 'select',
					'choices' => array(
						''        => 'Disabled',
						'enabled' => 'Enabled',
						'admin'   => 'Enabled including Admin',
					),
					'desc' => '
						<strong class="error-message">Be sure to read all warnings above before enabling!</strong>
					',
					'default' => $this->getDefault( 'enabled' ),
				),
				array(
					'id'    => 'test',
					'title' => '',
					'type'  => 'html',
					'html'  => '
						<p class="callout">
							<strong class="error-message">Warning:</strong> Auto-nonce will not operate in admin if
							<a href="#csp_mode">Enforce CSP in Admin</a> is not enabled!
						</p>
					',
					'hide_if' => array(
						array(
							array(
								'field' => "{$this->getSectionId()}_enabled",
								'value' => array( 'enabled', 'undefined', '' ),
							),
						),
						array(
							array(
								'field' => 'csp_mode_in-admin',
								'value' => array( '1', 'true' ),
							),
						),
					),
				),
				array(
					'id'    => 'test2',
					'title' => '',
					'type'  => 'html',
					'html'  => '
						<p class="callout">
							<strong class="error-message">Warning:</strong> CSP is enabled for admin,
							but Auto-Nonce is not.
						</p>
					',
					'show_if' => array(
						array(
							array(
								'field' => "{$this->getSectionId()}_enabled",
								'value' => array( 'enabled' ),
							),
							array(
								'field' => 'csp_mode_in-admin',
								'value' => array( 'true', '1' ),
							),
						),
					),
					'hide_if' => array(
						array(
							'field' => "{$this->getSectionId()}_enabled",
							'value' => array( '', 'admin', 'undefined' ),
						),
						array(
							'field' => 'csp_mode_in-admin',
							'value' => array( 'false', 'undefined' ),
						),
					),
				),
				array(
					'id'      => 'use_buffer',
					'title'   => 'Parse Full Responses',
					'type'    => 'toggle',
					'default' => $this->getDefault( 'use_buffer' ),
					'desc'    => '
						<div>
							<p>
								<span class="error-message">WARNING:</span>
								Parsing full responses uses output buffering to store and manipulate
								the <em>entire output</em> of a page, including where user input has
								been accepted and displayed. <strong>This is often insecure</strong>,
								and may result in increased memory usage, as well as broken pages.
								<strong>Use with caution</strong> and only as a last-resort or for
								testing.
							</p>
						</div>
					',
					'show_if' => array(
						array(
							'field' => "{$this->getSectionId()}_enabled",
							'value' => array( 'enabled', 'admin' ),
						),
					),
				),
			),
		);

		$prefix = PREFIX;

		// Output toggles for each noncable directive
		foreach ( $this->getDirectives() as $directive ) {
			$desc = null;

			$settings_section['fields'][] = array(
				'id'      => $this->getDirectiveEnabledKey( $directive ),
				'type'    => 'toggle',
				'title'   => $directive,
				'desc'    => $desc,
				'default' => $this->getDefault( $this->getDirectiveEnabledKey( $directive ) ),
				'show_if' => array(
					array(
						'field' => "{$this->getSectionId()}_enabled",
						'value' => array( 'enabled', 'admin' ),
					),
				),
			);

			\add_action(
				"wpsf_after_field_{$prefix}_{$this->getDirectiveEnabledKey( $directive )}",
				function () use ( $directive ) {
					/**
					 * @var \CUMULUS\Wordpress\SecurityHeaders\Actions\CSP\AutoNonce
					 */
					$autoNonceAction = ActionsRegister::getActor( 'csp/auto_nonce' );
					$bad_policies    = $autoNonceAction->getBadPolicies( $directive );

					if ( $bad_policies && \count( $bad_policies ) ) {
						?>
							<span class="error-message">Warning!</span>
							<a
								href="#csp_policies_<?php echo $directive; ?>"
							>
								Policy
							</a>
							contains <?php echo \implode( ' and ', $bad_policies ); ?>
						<?php
					}
				}
			);
		}

		SettingsRegister::addSettingsSection( $settings_section );
	}

	/**
	 * Determine if autononce is enabled.
	 *
	 * @return bool
	 */
	public function isActive() {
		// Never handle json requests
		if ( \wp_is_json_request() ) {
			return false;
		}

		/**
		 * @var CSPMode
		 */
		$csp_mode = SettingsRegister::getHandler( 'csp/mode' );

		// first check if CSP is active
		if ( $csp_mode->isActive() ) {
			$enabled = $this->getSetting( 'enabled' );

			// Check if auto-nonce is active
			if ( $enabled && '' !== $enabled ) {
				// Check if there are any enabled directives
				if ( $this->getEnabledDirectives() ) {
					// We are enabled, but we only handle the admin if the CSP
					// is enabled in the admin and we're told to auto-nonce in
					// the admin.
					if ( \is_admin() && $csp_mode->getSetting( 'in-admin' ) && 'admin' === $enabled ) {
						return true;
					} elseif ( ! \is_admin() || \wp_doing_ajax() ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Retrieve directives for noncing.
	 *
	 * @return array
	 */
	public function getDirectives() {
		return self::$directives;
	}

	/**
	 * Retrieve ENABLED directives for noncing.
	 *
	 * @return array|false
	 */
	public function getEnabledDirectives() {
		$enabled = array();

		foreach ( $this->getDirectives() as $directive ) {
			if ( $this->isDirectiveEnabled( $directive ) ) {
				$enabled[] = $directive;
			}
		}

		return \count( $enabled ) ? $enabled : false;
	}

	/**
	 * Determine if a directive is enabled for auto-noncing.
	 *
	 * @param string $directive
	 *
	 * @return bool
	 */
	public function isDirectiveEnabled( $directive ) {
		return (bool) $this->getSetting( $this->getDirectiveEnabledKey( $directive ) );
	}
}
