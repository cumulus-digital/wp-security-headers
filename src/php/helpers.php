<?php

namespace CUMULUS\Wordpress\SecurityHeaders;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

/**
 * Namespace a given function or class name, given as a string.
 *
 * @param string $str
 * @param string $ns
 *
 * @return string
 */
function ns( $str, $ns = __NAMESPACE__ ) {
	return $ns . '\\' . $str;
}

/**
 * Retrieve a key=>value array of current headers.
 *
 * @return array
 */
function getCurrentHeaders() {
	$current_headers = array();

	foreach ( \headers_list() as $h ) {
		\preg_match( '#^.+?(?=:)#', $h, $key );

		if ( empty( $key ) ) {
			continue;
		}
		$key                                     = \reset( $key );
		$value                                   = \ltrim( $h, $key . ':' );
		$current_headers[\mb_strtolower( $key )] = $value;
	}

	return $current_headers;
}

function debug() {
	if ( WP_DEBUG ) {
		\do_action( 'qm/debug', \func_get_arg( 0 ) );
	}
}
