<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */


class ElevateBreadcrumbs {
	var $home_name;

	public function __construct( $home_name ) {
		$this->home_name = $home_name;
	}

	private function _create_post_data( $id, $title, $link ) {
		$post_data = new stdClass;
		$post_data->ID = $id;
		$post_data->title = $title;
		$post_data->link = $link;

		return $post_data;
	}

	private function _output_real_breadcrumbs( $breadcrumbs ) {
		echo '<script type="application/ld+json">' . "\n";
		echo '{ "@context": "http://schema.org", "@type": "BreadcrumbList", "itemListElement": [' . "\n";
	  	$count = 1; 
	  	$to_output = array();
	  	foreach( $breadcrumbs as $info ) { 
	  		$string_to_add = '{"@type": "ListItem", "position": ' . $count . ',"name": "' . esc_attr( $info->title ) . '","item": "' . esc_attr( $info->link ) . '"}' . "\n";;
	  		$to_output[] = $string_to_add;

	  		$count++;
	  	}
	  	echo implode( ',', $to_output );

		echo "]}</script>\n";		
	}

	public function output_breadrumbs() {
		global $post;
		$breadcrumbs = array();

		$this_post = $post;

		$count = 1;
		while ( true ) {
			if ( !$this_post ) {
				break;
			}

			$breadcrumbs[ $count++ ] = $this->_create_post_data( $this_post->ID, $this_post->post_title, get_permalink( $this_post->ID ) );

			if ( $this_post->post_parent == 0 ) {
				$breadcrumbs[ $count++ ] = $this->_create_post_data( 0, $this->home_name, home_url() );

				break;
			} else {
				$this_post = get_post( $this_post->post_parent );
			}
		}

		if ( $breadcrumbs && count( $breadcrumbs ) ) {
			krsort( $breadcrumbs );
			$this->_output_real_breadcrumbs( $breadcrumbs );	
		}
	}
}