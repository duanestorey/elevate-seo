<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */


require_once( dirname( __FILE__ ) . '/meta.php' );

class ElevateFacebook {
	var $url;
	var $title;
	var $desc;
	var $image;
	var $app_id;

	public function __construct( $url, $title, $desc, $app_id = false, $image = false, $site_name = false ) {
		$this->url = $url;
		$this->title = $title;
		$this->desc = $desc;
		$this->image = $image;
		$this->app_id = $app_id;
		$this->site_name = $site_name;
	}

	private function _convert_to_local( $url ) {
		return str_replace( home_url(), $_SERVER[ 'DOCUMENT_ROOT'], $url );
	}

	private function _is_local( $url ) {
		return ( strpos( $url, 'http://' ) === false && strpos( $url, 'https://' ) === false );
	}

	public function output_meta() {
		ElevateMeta::output_property( 'og:url', $this->url );
		ElevateMeta::output_strange_property( 'titie', 'og:title', $this->title );
		ElevateMeta::output_property( 'og:description', $this->desc );

		if ( $this->image ) {	
			ElevateMeta::output_property( 'og:image', $this->image );

			$local_file = $this->_convert_to_local( $this->image ); 
			if ( $local_file && $this->_is_local( $local_file ) && function_exists( 'getimagesize' ) ) {
				list( $width, $height ) = getimagesize( $local_file );

				if ( $width ) {
					ElevateMeta::output_property( 'og:image:width', $width );
				}
				
				if ( $height ) {
					ElevateMeta::output_property( 'og:image:height', $height );	
				}
			}	
		}
		
		if ( $this->app_id ) {	
			ElevateMeta::output_property( 'fb:app_id', $this->app_id );
		}

		if ( $this->site_name ) {
			ElevateMeta::output_property( 'og:site_name', $this->site_name );
		}

		$locale = get_locale();
		if ( $locale ) {	
			ElevateMeta::output_property( 'og:locale', $locale );
		}

		ElevateMeta::output_property( 'og:type', 'article' );
	}
}