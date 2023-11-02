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
			'x-frame-options'              => '',
			'x-content-type-options'       => false,
			'referrer-policy'              => 'strict-origin-when-cross-origin',
			'cross-origin-embedder-policy' => 'unsafe-none',
			'cross-origin-opener-policy'   => 'unsafe-none',
			'cross-origin-resource-policy' => '',
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
						'id'      => 'x-frame-options',
						'title'   => 'X-Frame-Options',
						'desc'    => '<p>Control where this site can be included in an iframe</p>',
						'type'    => 'select',
						'choices' => array(
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
						'desc'     => '<p>Enable to set "no-sniff" header</p>',
						'type'     => 'toggle',
						'default'  => $this->getDefault( 'x-content-type-options' ),
						'link'     => array(
							'url'      => \esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options' ),
							'type'     => 'tooltip',
							'text'     => 'MDN Documentation for X-Content-Type-Options header',
							'external' => true,
						),
					),
					array(
						'id'    => 'referrer-policy',
						'title' => 'Referrer-Policy',
						'desc'  => '
							<div class="desc">
								<p>Control how much referrer information should be included with requests.</p>
								<dl>

									<dt>no-referrer</dt>
									<dd><p>Referer header is omitted from all requests.</p></dd>

									<dt>no-referrer-when-downgrade</dt>
									<dd><p>Only send Referer header when security level stays the same (HTTPS->HTTPS).</p></dd>

									<dt>origin</dt>
									<dd><p>Only send origin in Referer header.</p></dd>

									<dt>origin-when-cross-origin</dt>
									<dd><p>When request is the same security level and origin, send the full Referer header. Otherwise only send the origin.</p></dd>

									<dt>same-origin</dt>
									<dd><p>Only send Referer header to same-origin requests.</p></dd>

									<dt>strict-origin</dt>
									<dd><p>Only send Referer header if origin and security level remain the same.</p></dd>

									<dt>strict-origin-when-cross-origin (Default)</dt>
									<dd><p>Send the full Referer header on same-origin requests. Send only the origin on cross-origin requets when the security level remains the same. Otherwise do not send a Referer.</p></dd>

									<dt>unsafe-url</dt>
									<dd><p>Send the full Referer header for any request regardless of security</p></dd>

								</dl>
							</div>
						',
						'type'    => 'select',
						'choices' => array(
							'no-referrer'                     => 'no-referrer',
							'no-referrer-when-downgrade'      => 'no-referrer-when-downgrade',
							'origin'                          => 'origin',
							'origin-when-cross-origin'        => 'origin-when-cross-origin',
							'same-origin'                     => 'same-origin',
							'strict-origin'                   => 'strict-origin',
							'strict-origin-when-cross-origin' => 'strict-origin-when-cross-origin (Default)',
							'unsafe-url'                      => 'unsafe-url',
						),
						'default' => $this->getDefault( 'referrer-policy' ),
						'link'    => array(
							'url'      => \esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy' ),
							'type'     => 'tooltip',
							'text'     => 'MDN Documentation for Referrer-Policy header',
							'external' => true,
						),
					),
					array(
						'id'    => 'cross-origin-embedder-policy',
						'title' => 'Cross-Origin-Embedder-Policy',
						'desc'  => '
							<div class="desc">
								<p>Control what cross-origin resources can be embedded on this site.</p>

								<dl>

									<dt>unsafe-none (Default)</dt>
									<dd><p>Allows the document to fetch cross-origin resources without giving explicit permission.</p></dd>

									<dt>require-corp</dt>
									<dd><p>A document can only load resources from the same origin, or resources explicitly marked as loadable from another origin.</p></dd>

									<dt>credentialless</dt>
									<dd><p>no-cors cross-origin requests are sent without credentials. In particular, it means Cookies are omitted from the request, and ignored from the response. The responses are allowed without an explicit permission via the Cross-Origin-Resource-Policy header. Navigate responses behave similarly as the require-corp mode: They require Cross-Origin-Resource-Policy response header.</p></dd>

								</dl>
							</div>
						',
						'type'    => 'select',
						'choices' => array(
							'unsafe-none'    => 'unsafe-none (Default)',
							'require-corp'   => 'require-corp',
							'credentialless' => 'credentialless',
						),
						'default' => $this->getDefault( 'cross-origin-embedder-policy' ),
						'link'    => array(
							'url'      => \esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Embedder-Policy' ),
							'type'     => 'tooltip',
							'text'     => 'MDN Documentation for Cross-Origin-Embedder-Policy header',
							'external' => true,
						),
					),
					array(
						'id'    => 'cross-origin-opener-policy',
						'title' => 'Cross-Origin-Opener-Policy',
						'desc'  => '
							<div class="desc">
								<p>Control whether <em>other</em> sites which open this site can share a browsing context.</p>

								<dl>

									<dt>unsafe-none (Default)</dt>
									<dd><p>Allows the document to be added to its opener\'s browsing context group unless the opener itself has a COOP of same-origin or same-origin-allow-popups.</p></dd>

									<dt>same-origin-allow-popups</dt>
									<dd><p>Retains references to newly opened windows or tabs that either don\'t set COOP or that opt out of isolation by setting a COOP of unsafe-none.</p></dd>

									<dt>same-origin</dt>
									<dd><p>Isolates the browsing context exclusively to same-origin documents. Cross-origin documents are not loaded in the same browsing context.</p></dd>

								</dl>
							</div>
						',
						'type'    => 'select',
						'choices' => array(
							'unsafe-none'              => 'unsafe-none (Default)',
							'same-origin-allow-popups' => 'same-origin-allow-popups',
							'same-origin'              => 'same-origin',
						),
						'default' => $this->getDefault( 'cross-origin-opener-policy' ),
						'link'    => array(
							'url'      => \esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Opener-Policy' ),
							'type'     => 'tooltip',
							'text'     => 'MDN Documentation for Cross-Origin-Opener-Policy header',
							'external' => true,
						),
					),
					array(
						'id'    => 'cross-origin-resource-policy',
						'title' => 'Cross-Origin-Resource-Policy',
						'desc'  => '
							<div class="desc">
								<p>Disabled by default. Protects against certain requests to this site from other origins.</p>
								<p><strong class="error-message">Warning:</strong> Due to a bug in Chrome, enabling this
								with any setting may break PDFs.</p>

								<dl>

									<dt>same-site</dt>
									<dd>
										<p>
											Only requests from the same Site can read the resource.<br>
											<strong class="error-message">Warning:</strong> This is less secure than *-origin! See MDN documentation.
										</p>
									</dd>

									<dt>same-origin</dt>
									<dd><p>Only requests from the same origin (i.e. scheme + host + port) can read the resource.</p></dd>

									<dt>cross-origin</dt>
									<dd><p>Requests from any origin (both same-site and cross-site) can read the resource. This is useful when COEP is used (see below).</p></dd>

								</dl>
							</div>
						',
						'type'    => 'select',
						'choices' => array(
							''             => 'Disabled',
							'same-site'    => 'same-site',
							'same-origin'  => 'same-origin',
							'cross-origin' => 'cross-origin',
						),
						'default' => $this->getDefault( 'cross-origin-resource-policy' ),
						'link'    => array(
							'url'      => \esc_url( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Resource-Policy' ),
							'type'     => 'tooltip',
							'text'     => 'MDN Documentation for Cross-Origin-Resource-Policy header',
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
