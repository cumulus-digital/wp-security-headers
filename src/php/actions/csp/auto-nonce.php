<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\CSP;

use CUMULUS\Wordpress\SecurityHeaders\Actions\AbstractActor;
use function CUMULUS\Wordpress\SecurityHeaders\debug;
use CUMULUS\Wordpress\SecurityHeaders\FilterableScripts;
use CUMULUS\Wordpress\SecurityHeaders\FilterableStyles;
use const CUMULUS\Wordpress\SecurityHeaders\PREFIX;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;
use DomDocument;
use DOMElement;
use DOMProcessingInstruction;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class AutoNonce extends AbstractActor {
	/**
		 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\AutoNonce
		 */
	protected $settings;

	/**
	 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\Directives
	 */
	protected $directivesHandler;

	private $nonce;

	public function __construct() {
		$this->settings          = SettingsRegister::getHandler( 'csp/auto_nonce' );
		$this->directivesHandler = SettingsRegister::getHandler( 'csp/directives' );
		$this->setupFilters();
	}

	public function sendHeaders() {
		if ( $this->isActive() ) {
			debug( 'Auto-Nonce is ACTIVE' );

			// When auto-nonce is active, we need to ensure HTML is not cached.
			\header( 'Cache-Control: max-age=0, no-cache, no-store, must-revalidate' );
			\header( 'Pragme: no-cache' );
			\header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
		}
	}

	public function sendAdminHeaders() {
		/**
		 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\Mode
		 */
		$csp_mode = SettingsRegister::getHandler( 'csp/mode' );

		if ( $csp_mode->getSetting( 'in-admin' ) ) {
			$this->sendHeaders();
		}
	}

	public function setupFilters() {
		if ( $this->isActive() ) {
			$prefix = PREFIX;
			foreach ( $this->settings->getEnabledDirectives() as $directive ) {
				\add_filter(
					"{$prefix}_csp_{$directive}",
					array( $this, 'addNonceHeader' ),
					10,
					2
				);
			}

			// Create shortcode for nonce
			\add_shortcode( 'cmls_wpsh_nonce', array( $this, 'generateNonce' ) );

			// Should we buffer and parse an entire request?
			if ( (bool) $this->settings->getSetting( 'use_buffer' ) ) {
				\add_action( 'plugins_loaded', array( $this, 'startBuffer' ), \PHP_INT_MIN );

				/*
				 * Wordpress flushes the buffer in shutdown priority 1,
				 * so we need to re-establish our buffer after
				 */
				\add_action( 'shutdown', array( $this, 'startBuffer' ), 2 );

				\add_action( 'shutdown', array( $this, 'flushAllBuffers' ), \PHP_INT_MAX );
			} else {
				// Filter scripts and styles as they're registered...

				// Add nonce to tags
				\add_filter( 'script_loader_tag', array( $this, 'addNonceToHTMLFragment' ), 10, 3 );
				\add_filter( 'style_loader_tag', array( $this, 'addNonceToHTMLFragment' ), 10, 3 );

				// Add nonce attributes if needed
				\add_filter( 'wp_inline_script_attributes', array( $this, 'addNonceAttribute' ) );
				\add_filter( 'wp_script_attributes', array( $this, 'addNonceAttribute' ) );

				// Set up filterable scripts/styles
				\add_action(
					'init',
					function () {
						if ( ! \array_key_exists( 'wp_cripts', $GLOBALS ) || ! $GLOBALS['wp_scripts'] instanceof FilterableScripts ) {
							debug( 'Setting up inline script filter' );
							$GLOBALS['wp_scripts'] = new FilterableScripts();
						}

						/*
						// DISABLED - style_loader_tag works
						if ( ! \array_key_exists( 'wp_styles', $GLOBALS ) || ! $GLOBALS['wp_styles'] instanceof FilterableStyles ) {
							$fstyles              = new FilterableStyles();
							$GLOBALS['wp_styles'] = $fstyles;
						}*/
					},
					1
				);
				// Filters provided by FilterableScripts and FilterableStyles
				\add_filter( "{$prefix}_filter_scripts", array( $this, 'addNonceToHTMLFragment' ), 10, 3 );
				// \add_filter( "{$prefix}_filter_styles", array( $this, 'addNonceToHTMLFragment' ), 10, 3 );

				// Filter Custom HTML blocks
				\add_filter( 'render_block', function ( $content, $block ) {
					if (
						false    !== \mb_stripos( $content, '<script' )
						|| false !== \mb_stripos( $content, '<style' )
						|| false !== \mb_stripos( $content, '<link' )
					) {
						$content = $this->addNonceToHTMLFragment( $content );
					}

					return $content;
				}, \PHP_INT_MAX, 2 );

				// Deal with the customizer support script
				\add_action( 'admin_bar_menu', function () {
					\remove_action( 'wp_before_admin_bar_render', 'wp_customize_support_script' );
				}, \PHP_INT_MAX );
				\add_action( 'wp_body_open', function () {
					if ( \is_user_logged_in() ) {
						\ob_start();
						\wp_customize_support_script();
						// $output = \str_replace( array( "<script>\n", '</script>' ), '', \ob_get_clean() );
						echo $this->addNonceToHTMLDocument( \ob_get_clean() );
					}
				} );
			}
		}
	}

	public function generateNonce() {
		if ( ! $this->nonce ?? false ) {
			$this->nonce = \base64_encode( \random_bytes( 18 ) );
		}

		return $this->nonce;
	}

	/**
	 * Returns a list of defined policies for a directive which
	 * will negate a nonce.
	 *
	 * @param string $directive
	 *
	 * @return array|false
	 */
	public function getBadPolicies( $directive ) {
		$bad_policies = array( "'none'", "'unsafe-inline'" );
		$errors       = array();

		// "bad" policies are fallbacks when 'strict-dynamic' is set on script-src or default-src
		if (
			(
				'script-src'     === $directive
				|| 'default-src' === $directive
			)
			&& $this->directivesHandler->policyContains( $directive, "'strict-dynamic'" )
		) {
			return false;
		}

		foreach ( $bad_policies as $bad_policy ) {
			if ( $this->directivesHandler->policyContains( $directive, $bad_policy ) ) {
				$errors[] = $bad_policy;
			}
		}

		return \count( $errors ) ? $errors : false;
	}

	/**
	 * Determine if the given directive's policies require adding
	 * our nonce to the CSP header, if so add it.
	 *
	 * @param array  $policies
	 * @param string $directive
	 *
	 * @return array
	 */
	public function addNonceHeader( $policies, $directive ) {
		if ( ! $directive ) {
			\trigger_error( 'We received an addNonceHeader call without a directive!', \E_USER_WARNING );

			return $policies;
		}

		// First check if auto-nonce is active
		if ( ! $this->isActive() ) {
			return $policies;
		}

		// Check if directive is enabled for auto-noncing
		if ( ! $this->settings->isDirectiveEnabled( $directive ) ) {
			return $policies;
		}

		$headerDirective = $directive;

		// If directive is not enabled, but we have been instructed to nonce it,
		// we will apply the nonce to default-src
		if ( ! $this->directivesHandler->isDirectiveEnabled( $directive ) ) {
			$headerDirective = 'default-src';
		}

		// Now we need to ensure that the policies of the discovered
		// directive don't contain anything that would negate a nonce
		$bad_policies = $this->getBadPolicies( $headerDirective );
		if ( $bad_policies && \count( $bad_policies ) ) {
			debug( "Skipping nonce for {$headerDirective} due to negating policies." );

			return $policies;
		}

		$nonce = "'nonce-{$this->generateNonce()}'";

		// Check if this directive already has our nonce
		if ( $this->directivesHandler->policyContains( $headerDirective, $nonce ) ) {
			return $policies;
		}

		/*
		 * if the original directive is not default-src and we've discovered we need
		 * to deal with default-src, we will have to filter it later.
		 */
		if ( 'default-src' === $headerDirective ) {
			$prefix = \CUMULUS\Wordpress\SecurityHeaders\PREFIX;
			\add_filter(
				"{$prefix}_csp_default-src",
				function ( $policies, $directive ) use ( $nonce ) {
					$policies[] = $nonce;

					return $policies;
				},
				10,
				2
			);

			return $policies;
		}

		$policies[] = $nonce;

		return $policies;
	}

	/**
	 * Starts an output buffer with a handler to auto-nonce output.
	 */
	public function startBuffer() {
		if ( $this->isActive() ) {
			debug( 'Starting output buffer...' );
			\ob_start( function ( $output ) {
				if ( ! isset( $output ) ) {
					return $output;
				}

				if ( ! $this->isActive() ) {
					return $output;
				}

				return $this->addNonceToHTMLDocument( $output );
			} );
		}
	}

	/**
	 * Forces all output buffers to end and flush.
	 */
	public function flushAllBuffers() {
		$level = \ob_get_level();

		for ( $i = 0; $i < $level; $i++ ) {
			\ob_end_flush();
		}
	}

	/**
	 * Add a nonce attribute to an array of tag attributes.
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function addNonceAttribute( $attributes = array() ) {
		// debug( array( 'addNonceAttribute', $attributes ) );

		if ( ! \array_key_exists( 'nonce', $attributes ) ) {
			$attributes['nonce'] = $this->generateNonce();
		}

		return $attributes;
	}

	/**
	 * Add a nonce to tags within a given HTML document.
	 *
	 * @param string $html
	 * @param string $handle
	 * @param string $src
	 *
	 * @return string;
	 */
	public function addNonceToHTMLDocument( $html, $handle = '', $src = '' ) {
		if ( ! $this->isActive() ) {
			return $html;
		}

		if ( ! $html ) {
			return $html;
		}

		$directives = $this->settings->getEnabledDirectives();

		if ( ! $directives ) {
			return $html;
		}

		$raw_html = $html;
		// We need to add an encoding declaration so we don't mangle characters
		if ( false === \mb_stripos( $html, '<?xml' ) ) {
			$raw_html = '<?xml encoding="utf-8" ?>' . $html;
		}

		$doc               = new DomDocument( '1.0', 'UTF-8' );
		$old_error_setting = \libxml_use_internal_errors( true );
		$doc->loadHTML(
			$raw_html,
			\LIBXML_SCHEMA_CREATE | \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD
		);
		\libxml_clear_errors();
		\libxml_use_internal_errors( $old_error_setting );

		// Remove xml encoding declaration
		foreach ( $doc->childNodes as $item ) {
			if ( $item instanceof DOMProcessingInstruction ) {
				$doc->removeChild( $item );

				break;
			}
		}
		$doc->encoding = 'UTF-8';

		$affected = array();

		// Handle script tags
		if (
			$this->settings->isDirectiveEnabled( 'script-src' )
			&& (
				$this->directivesHandler->isDirectiveEnabled( 'script-src' )
				|| $this->directivesHandler->isDirectiveEnabled( 'default-src' )
			)
		) {
			if ( \mb_stristr( $html, '<script' ) ) {
				$scripts = $doc->getElementsByTagName( 'script' );

				if ( \count( $scripts ) ) {
					foreach ( $scripts as $script ) {
						if ( $this->nodeCanBeNonced( $script ) ) {
							$affected[] = $script;
						}
					}
				}
			}
		}

		// Handle style and link tags
		if (
			$this->settings->isDirectiveEnabled( 'style-src' )
			&& (
				$this->directivesHandler->isDirectiveEnabled( 'style-src' )
				|| $this->directivesHandler->isDirectiveEnabled( 'default-src' )
			)
		) {
			// Style tags don't have to be nonced if 'unsafe-inline' is set
			if (
				(
					$this->directivesHandler->isDirectiveEnabled( 'style-src' )
					&& ! $this->directivesHandler->policyContains( 'style-src', "'unsafe-inline'" )
				) || (
					! $this->directivesHandler->isDirectiveEnabled( 'style-src' )
					&& (
						$this->directivesHandler->isDirectiveEnabled( 'default-src' )
						&& ! $this->directivesHandler->policyContains( 'default-src', "'unsafe-inline'" )
					)
				)
			) {
				if ( \mb_stristr( $html, '<style' ) ) {
					$styles = $doc->getElementsByTagName( 'style' );

					if ( \count( $styles ) ) {
						foreach ( $styles as $style ) {
							if ( $this->nodeCanBeNonced( $style ) ) {
								$affected[] = $style;
							}
						}
					}
				}
			}

			// Handle linked stylesheets
			if ( \mb_stristr( $html, '<link' ) ) {
				$links = $doc->getElementsByTagName( 'link' );

				if ( \count( $links ) ) {
					foreach ( $links as $link ) {
						if (
							$link->hasAttribute( 'rel' )
							&& 'stylesheet' === \mb_strtolower( $link->getAttribute( 'rel' ) )
							&& $this->nodeCanBeNonced( $link )
						) {
							$affected[] = $link;
						}
					}
				}
			}
		}

		foreach ( $affected as $tag ) {
			$tag->setAttribute( 'nonce', $this->generateNonce() );
		}

		return $doc->saveHTML();
	}

	/**
	 * Add a nonce to tags within a given HTML fragment.
	 *
	 * Since a fragment may contain multiple root nodes, we must
	 * wrap the fragment in a custom container element, and
	 * then remove that wrapper on output.
	 *
	 * @param string $html
	 * @param string $handle
	 * @param string $src
	 *
	 * @return string;
	 */
	public function addNonceToHTMLFragment( $html, $handle = '', $src = '' ) {
		// debug( array( 'addNonceToHTMLFragment', $handle, $html ) );
		$prefix = PREFIX;
		$html   = "<{$prefix}-container>{$html}</{$prefix}-container>";

		return \str_replace(
			array( "<{$prefix}-container>", "</{$prefix}-container>" ),
			'',
			(string) $this->addNonceToHTMLDocument( $html, $handle, $src )
		);
	}

	/**
	 * Determine if an DOMNode should be nonced.
	 *
	 * @param function $customCheck Called after all our checks are done
	 *
	 * @return bool
	 */
	public function nodeCanBeNonced( DOMElement $el, $customCheck = null ) {
		// No if el already has a nonce
		if ( $el->hasAttribute( 'nonce' ) ) {
			return false;
		}

		$nodeName = \mb_strtolower( $el->nodeName );

		// Get the source attribute for the tag
		$src = null;

		if ( 'script' === $nodeName ) {
			$src = 'src';
		}

		if ( 'link' === $nodeName ) {
			$src = 'href';
		}

		// Get the applicable directive for this tag
		$directive = null;

		$node_name_to_directive = array(
			'script' => 'script-src',
			'link'   => 'style-src',
			'style'  => 'style-src',
		);

		if ( \array_key_exists( $nodeName, $node_name_to_directive ) ) {
			$directive = $node_name_to_directive[$nodeName];
		}

		// Check the policies for the applicable directive
		if ( $directive ) {
			// Ensure we're enabled for noncing this directive
			if ( ! $this->settings->isDirectiveEnabled( $directive ) ) {
				return false;
			}

			/**
			 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\CSP\Directives
			 */
			$directivesHandler = SettingsRegister::getHandler( 'csp/directives' );

			$effective_directive = $directive;

			// If the directive is not CSP-enabled, we fall back to default-src
			if ( ! $directivesHandler->isDirectiveEnabled( $directive ) ) {
				$effective_directive = 'default-src';
			}

			// Let's check if this directive has ANY policies
			if ( \count( $directivesHandler->getPolicies( $effective_directive ) ) ) {
				// If directive has 'none', we will not nonce and let them fail
				if ( $directivesHandler->policyContains( $effective_directive, "'none'" ) ) {
					return false;
				}

				// Handle src attributes
				if ( $src && $el->hasAttribute( $src ) ) {
					$src_url = \parse_url( $el->getAttribute( $src ) );
					$wp_url  = \parse_url( \get_site_url() );

					// Handle directives with a 'self' policy
					if (
						$directivesHandler->policyContains( $effective_directive, "'self'" )
						&& (
							(
								'default-src'   === $effective_directive
								|| 'script-src' === $effective_directive
							)
							&& ! $directivesHandler->policyContains( $effective_directive, "'strict-dynamic'" )
						)
					) {
						// if we don't have a host, assume it's local
						if ( ! \array_key_exists( 'host', $src_url ) || ! $src_url['host'] ) {
							return false;
						}

						$src_host = \mb_strtolower( $src_url['host'] );

						if ( isset( $src_url['port'] ) ) {
							$src_host = "{$src_host}:{$src_url['port']}";
						}

						$wp_host = \mb_strtolower( $wp_url['host'] );

						if ( isset( $wp_url['port'] ) ) {
							$wp_host = "{$wp_host}:{$wp_url['port']}";
						}

						if ( $src_host === $wp_host ) {
							return false;
						}
					}
				}

				// Inline scripts usually need a nonce unless unsafe-inline is set
				if (
					'script' === $nodeName
					&& ! $el->hasAttribute( $src )
					&& ! $directivesHandler->policyContains( $effective_directive, "'unsafe-inline'" )
				) {
					if ( $el->hasAttribute( 'type' ) ) {
						// If their type is not executable, they don't need a nonce
						$type = \mb_strtolower( $el->getAttribute( 'type' ) );

						if ( ! \preg_match( '/javascript|ecmascript|jstrict|livescript/', $type ) ) {
							return false;
						}
					}
				}
			}
		}

		if ( $customCheck && \is_callable( $customCheck ) ) {
			if ( ! $customCheck( $el ) ) {
				return false;
			}
		}

		// Default to noncable
		return true;
	}
}
