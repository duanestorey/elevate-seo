<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */


define( 'ELEVATE_API_VERSION', '1.2' );
define( 'ELEVATE_API_PATH' , 'https://api.elevatewp.io/v/' . ELEVATE_API_VERSION . '/' );

require_once( 'debug.php' );

class ElevateAPI {
	var $license_key;

	public function __construct() {
		$this->license_key = '';
	}

	static function get_oauth_auth_url( $read_only = false, $redirect_url = false ) {
		if ( !$redirect_url ) {
			$redirect_url = admin_url( 'admin.php?page=elevate_plugin' );
		}

		$url = ELEVATE_API_PATH . 'oauth/?elevate_redirect=' . urlencode( $redirect_url ) . '&elevate_state=' . wp_create_nonce( 'api_generate' );

		return $url;
	}

	static function is_api_nonce_valid( $nonce ) {
		return wp_verify_nonce( $nonce, 'api_generate' );
	}

	private function _make_api_call( $api_method, $params = array(), $authenticate = false ) {
		$params[ 'method' ] = $api_method;
		$params[ 't' ] = time();

		if ( $authenticate ) {
			ksort( $params );

			$auth_string = '';
			foreach ( $param as $key => $value ) {
				$auth_string = $auth_string . $key . $value;
			}

			$params[ 'auth' ] = md5( $auth_string . $this->license_key );
		}

		$http_params = array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,		
			'headers' => array( 'User-agent' => 'Elevate SEO v' . ELEVATE_PLUGIN_VERSION ),
			'body' => $params
		);

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Making HTTP request ' . ELEVATE_API_PATH . ' ' . print_r( $http_params, true ) );

		$result = wp_remote_post(
			ELEVATE_API_PATH,
			$http_params
		);

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, print_r( $result, true ) );

		if ( is_wp_error( $result ) ) {
			return false;
		} else {
			return json_decode( $result[ 'body' ] );
		}			
	}

	private function _is_api_error( $result ) {
		return ( is_object( $result ) && isset( $result->code ) && $result->code != 0 );
	}

	public function get_oauth_token( $initial_code ) {
		$result = $this->_make_api_call( 'get_oauth_token', array( 'code' => $initial_code ) );

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, print_r( $result, true ) );

		if ( !$this->_is_api_error( $result ) ) {
			return $result->body;
		}		
	}

	public function refresh_oauth_token( $refresh_token ) {
		$result = $this->_make_api_call( 'refresh_oauth_token', array( 'token' => $refresh_token ) );

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, print_r( $result, true ) );

		if ( !$this->_is_api_error( $result ) ) {
			return $result->body;
		}
	}

	public function subscribe_to_mailing_list( $email ) {
		$result = $this->_make_api_call( 'mailing_list_subscribe', array( 'email' => $email, 'ip' => $_SERVER[ 'SERVER_ADDR' ] ) );

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Adding [' . $email . '] to mailing list' );

		if ( !$this->_is_api_error( $result ) ) {
			return $result->body;
		}		
	}
}
