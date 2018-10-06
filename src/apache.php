<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */

define( 'ELEVATE_HTACCESS_SCHEMA_VERSION', '1.0' );

global $apache_instance;
$apache_instance = 0;

class ElevateApache {
	var $has_apache;
	var $htaccess_contents;

	public function __construct() {
		$this->has_apache = $this->is_apache_installed();
		$this->htaccess_contents = false;
	}

	static function instance() {
		if ( !$apache_instance ) {
			$apache_instance = new ElevateApache;
		}

		return $apache_instance;
	}

	static function is_apache_installed() {
		if ( function_exists( 'apache_get_version' ) ) {
			return apache_get_version();
		} else if ( strpos( $_SERVER[ 'SERVER_SOFTWARE' ], 'Apache' ) !== false ) {
			return true;
		} else {
			return false;
		}
	}

	public function is_using_ssl() {
		return ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] != 'off' );
	}

	public function supports_ssl() {
		if ( $this->is_using_ssl() ) {
			return true;
		} else {
			// See if it's possible to open a connection
			$response = wp_remote_head( 
				str_replace( 'http://', 'https://', home_url() ),
				array(
					'timeout' => 2,
					'redirection' => 2,
				) 
			);

			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Response is ' . print_r( $response, true ) );
		}
	}

	public function htaccess_supports( $module ) {
		$htaccess = $this->get_htaccess();

		return ( strpos( $htaccess, $module ) !== false );
	}

	public function is_using_fpm() {
		return ( $this->is_apache_installed() && !function_exists( 'apache_get_modules' ) );
	}

	static function apache_supports( $module ) {
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules = apache_get_modules();

			return in_array( $module, $modules );	
		} else {
			return false;
		}	
	}

	private function _htaccess_full_path() {
		return $_SERVER[ 'DOCUMENT_ROOT' ] . '/.htaccess';
	}

	private function _htaccess_backup_path() {
		return $_SERVER[ 'DOCUMENT_ROOT' ] . '/.htaccess-elevate.bak';
	}

	public function get_htaccess() {
		if ( !$this->htaccess_contents ) {
			$this->htaccess_contents = file_get_contents( $this->_htaccess_full_path() );
		}

		return $this->htaccess_contents;
	}

	public function has_deflate() {
		$contents = $this->get_htaccess();

		return ( strpos( $contents, 'mod_deflate.c' ) !== false );
	}

	private function _remove_elevate_info( $content ) {
		if ( preg_match_all( "/(\# BEGIN Elevate SEO(.*)\# END Elevate SEO)/is", $content, $matches ) ) {
			$content = str_replace( $matches[0][0], '', $content );
		}

		return $content;
	}

	public function fix() {
		$content = $this->get_htaccess();

		$expires_template = file_get_contents( ELEVATE_PLUGIN_DIR . '/include/htaccess/expires.txt' );
		$compress_template = file_get_contents( ELEVATE_PLUGIN_DIR . '/include/htaccess/compress.txt' );

		$elevate_info = "# BEGIN Elevate SEO, Version " . ELEVATE_HTACCESS_SCHEMA_VERSION . "\n\n" . $expires_template . "\n\n" . $compress_template . "\n\n# END Elevate SEO\n\n";

		$new_content = $this->_remove_elevate_info( $content );

		$new_content = $elevate_info . trim( $new_content );

		$this->write_htaccess( $new_content, $content );

		return $new_content;
	}

	public function write_htaccess( $content, $original_content = false ) {
		// Make sure we can write to it
		if ( $this->is_htaccess_writable() ) {
			if ( $original_content ) {
				// Make a backup of the original
				file_put_contents( $this->_htaccess_backup_path(), $original_content );
			}

			// Write the primary file
			file_put_contents( $this->_htaccess_full_path(), $content );

			// Update our cached copy
			$this->htaccess_contents = $content;
		}
	}

	public function is_htaccess_writable() {
		return is_writable( $this->_htaccess_full_path() );
	}
}