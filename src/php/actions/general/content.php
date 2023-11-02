<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\General;

use CUMULUS\Wordpress\SecurityHeaders\Actions\AbstractActor;
use CUMULUS\Wordpress\SecurityHeaders\Settings\SettingsRegister;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

class Content extends AbstractActor {
	/**
		 * @var \CUMULUS\Wordpress\SecurityHeaders\Settings\Section\General\Content
		 */
	protected $settings;

	public function __construct() {
		$this->settings = SettingsRegister::getHandler( 'general/content' );
	}

	public function sendHeaders() {
		$xfo                          = $this->getSetting( 'x-frame-options' );
		$referrer_policy              = $this->getSetting( 'referrer-policy' );
		$cross_origin_embedder_policy = $this->getSetting( 'cross-origin-embedder-policy' );
		$cross_origin_opener_policy   = $this->getSetting( 'cross-origin-opener-policy' );
		$cross_origin_resource_policy = $this->getSetting( 'cross-origin-resource-policy' );

		if ( $xfo && '' !== $xfo ) {
			\header( "X-Frame-Options: {$xfo}", true );
		}

		if ( $this->getSetting( 'x-content-type-options' ) ) {
			\header( 'X-Content-Type-Options: nosniff', true );
		}

		if ( $referrer_policy && '' !== $referrer_policy ) {
			\header( "Referrer-Policy: {$referrer_policy}", true );
		}

		if ( $cross_origin_embedder_policy && '' !== $cross_origin_embedder_policy ) {
			\header( "Cross-Origin-Embedder-Policy: {$cross_origin_embedder_policy}", true );
		}

		if ( $cross_origin_opener_policy && '' !== $cross_origin_opener_policy ) {
			\header( "Cross-Origin-Opener-Policy: {$cross_origin_opener_policy}", true );
		}

		if ( $cross_origin_resource_policy && '' !== $cross_origin_resource_policy ) {
			\header( "Cross-Origin-Resource-Policy: {$cross_origin_resource_policy}", true );
		}
	}
}
