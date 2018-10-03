<?php

require_once( dirname( __FILE__ ) . '/meta.php' );

class ElevateTwitter {
	var $url;
	var $title;
	var $desc;
	var $image;
	var $site;

	public function __construct( $url, $title, $desc, $image = false, $site = false, $large_image = false ) {
		$this->url = $url;
		$this->title = $title;
		$this->desc = $desc;
		$this->image = $image;
		$this->site = $site;
		$this->large_image = $large_image;
	
		// Make sure we have an '@' sign in the Twitter name
		if ( strlen( $this->site ) ) {
			$this->site = '@' . ltrim( $this->site, '@' );
		}
	}

	public function output_meta() {
		if ( !$this->large_image ) {
			ElevateMeta::output_name( 'twitter:card', 'summary' );	
		} else {
			ElevateMeta::output_name( 'twitter:card', 'summary_large_image' );
		}
		
		ElevateMeta::output_name( 'twitter:title', $this->title );	
		ElevateMeta::output_name( 'twitter:description', $this->desc );	

		if ( $this->image ) {
			ElevateMeta::output_name( 'twitter:image', $this->image );	
		}

		if ( $this->site ) {
			ElevateMeta::output_name( 'twitter:site', $this->site );		
		}
	}
}