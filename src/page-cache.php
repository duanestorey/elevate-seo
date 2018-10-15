<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */

define( 'ELEVATE_PAGE_CACHE_VERSION', '1.0' );

class ElevatePageCache {
	var $cache_dir;
	var $cache_version;
	var $cache_key;

	public function __construct() {
		$this->cache_version = 0;
		$this->cache_key = 0;
		$this->cache_dir = '';
		$this->cache_rel_path = '';

		$dir_info = $this->_wp_upload_dir();
		if ( isset( $dir_info[ 'basedir' ] ) ) {
			$this->cache_dir = $this->_trailingslashit( $dir_info[ 'basedir' ] ) . 'elevate-cache/';
			if ( !file_exists( $this->cache_dir ) ) {
				mkdir( $this->cache_dir, 0755 );

				$htaccess_file_name = $this->cache_dir . '.htaccess';
				$htaccess_file = fopen( $htaccess_file_name, 'w+t' );
				if ( $htaccess_file ) {
					fwrite( $htaccess_file, "Options All –Indexes\n" );
					fclose( $htaccess_file );
				}
			}
		}

		if ( isset( $dir_info[ 'baseurl' ] ) ) {
			$url_info = parse_url( $dir_info[ 'baseurl' ] );
			if ( $url_info ) {
				$this->cache_rel_path = $this->_trailingslashit( $url_info[ 'path' ] ) . 'elevate-cache/';
			}
		}
	}

	private function _wp_upload_dir() {
		if ( function_exists( 'wp_upload_dir' ) ) {
			return wp_upload_dir();
		} else {
			$dir_info = array();
			$dir_info[ 'basedir' ] = ELEVATE_CACHE_WP_BASE_DIR;
			$dir_info[ 'baseurl' ] = ELEVATE_CACHE_WP_REL_URL;

			return $dir_info;
		}
	}

	public function set_cache_version( $version ) {
		$this->cache_version = $version;
	}

	public function has_cached_page() {
		if ( !$this->_can_cache_page() ) {
			return false;
		}

		if ( file_exists( $this->_get_cache_file_path() ) ) {
			$last_modified = filemtime( $this->_get_cache_file_path() );

			$seconds_since = ( time() - $last_modified );
			if ( $seconds_since > $this->_get_cache_duration() ) {
				return false;
			} else {
				return $this->_get_cache_key();
			}	
		}	

		return false;
	}

	public function cache_and_serve_page() {
		if ( $this->_can_cache_page() ) {
			ob_start( array( &$this, '_handle_cache_done' ) );	
		}
	}

	public function serve_cached_page() {
		$cache_file = file_get_contents( $this->_get_cache_file_path() );
		if ( $cache_file ) {
			$decoded_cache_info = json_decode( $cache_file );

			if ( $decoded_cache_info ) {
				foreach( $decoded_cache_info->headers as $header ) {
					header( $header );
				}

				header( 'X-Elevate-Cache: ' . ELEVATE_PAGE_CACHE_VERSION . '/Hit' );
				header( 'Cache-Control: private, must-revalidate' );

				http_response_code( 200 ); 

				echo $decoded_cache_info->body;

				die;
			}
		}
	}

	public function _handle_cache_done( $content ) {
		$cache_data = new stdClass;

		$cache_data->headers = array();
		$cache_data->body = $content;

		if ( function_exists( 'headers_list' ) ) {
			header( 'ETag: ' . $this->_get_cache_key() );

			$cache_data->headers = headers_list();

			header( 'X-Elevate-Cache: ' . ELEVATE_PLUGIN_VERSION . '/Miss' );
		}

		$cache_file = fopen( $this->_get_cache_file_path(), 'w+t' );
		if ( $cache_file ) {
			fwrite( $cache_file, json_encode( $cache_data ) );
			fclose( $cache_file );
		}

		return $content;
	}

	public function cache_robots_path() {
		return $this->cache_rel_path;
	}

	private function _trailingslashit( $path ) {
		return rtrim( $path, '/\\' ) . '/';
	}

	private function _can_cache_page() {
		if ( $this->_is_user_logged_in() ) {
			return false;
		}

		$rejected_paths = array( 'wp-login.php', 'robots.txt', '/feed/', '/wp-admin/' );
		foreach( $rejected_paths as $path ) {
			if ( strpos( strtolower( $_SERVER[ 'REQUEST_URI' ] ), $path ) !== false ) {
				return false;
			}
		}

		// Check request methods
		if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			switch( strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
				case 'GET':
					if ( strpos( $_SERVER[ 'REQUEST_URI' ], '?' ) !== false ) {
						return false;
					}
					break;
				case 'POST':
					// Don't cache POST requests
					return false;
				case 'HEAD':
					// Don't cache HEAD requests
					return false;
				default:
					break;
			}
		}

		// Don't cache posts with passwords
		foreach( $_COOKIE as $key => $value ) {
			if ( preg_match( '|^wp-postpass_(.*)|i', $key ) ) {
				return false;
			}
		}	

		// Don't cache AJAX requests
		if ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' ) {
			return false;
		}

		return true;
	}

	private function _get_cache_duration() {
		return HOUR_IN_SECONDS*24;
	}

	public function _get_cache_directory() {
		return $this->cache_dir;
	}

	private function _get_cache_file_path() {
		return $this->_get_cache_directory() . $this->_get_cache_key() . '.json';
	}

	private function _is_user_logged_in() {
		foreach( $_COOKIE as $key => $value ) {
			if ( preg_match( '|^wordpress_logged_in_(.*)|i', $key ) ) {
				return $_COOKIE[ $key ];
			}
		}		

		return false;
	}

	private function _get_cache_key() {
		if ( $this->cache_key ) {
			return $this->cache_key;
		}

		$cache_key = $_SERVER[ 'REQUEST_URI' ];

		$cookies = $_COOKIE;
		ksort( $_COOKIE );

		// Cache separate pages for people who have commented previously
		foreach( $cookies as $key => $value ) {
			if ( preg_match( '|^comment_author_(.*)|i', $key ) ) {
				$cache_key = $cache_key . $key . '|' . $value;	
			}
		}

		$logged_in_cookie = $this->_is_user_logged_in();
		if ( $logged_in_cookie ) {
			$cache_key .= 'user_' . $logged_in_cookie;
		} else {
			$cache_key .= 'not_logged_in';
		}

		$cache_key = $cache_key . $this->cache_version;

		$this->cache_key = md5( $cache_key );

		return $this->cache_key;
	}
}