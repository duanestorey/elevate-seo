<?php

class ElevateHTTP {
	static function make_request( $url, $request_type = 'GET', $body = false ) {
		$params = array(
			'method' => $request_type,
			'user-agent' => 'Elevate SEO ' . ELEVATE_PLUGIN_VERSION,
			'timeout' => 30
		);

		if ( $params[ 'method' ] === 'PUT' ) {
			$params[ 'headers' ][ 'Content-length' ] = 0;
		} else if ( $params[ 'method' ] == 'POST' ) {
			$params[ 'body' ] = $body;
		}

		$result = wp_remote_request(
			$url,
			$params
		);

		if ( is_wp_error( $result ) ) {
			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, ' HTTP ERROR, result of call is [' . print_r( $result, true ) . ']' );
			return false;
		} else {
			//ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, ' HTTP SUCCESS, result of call is [' . $result[ 'body' ] . ']' );
			return $result[ 'body' ];
		}					
	}
}