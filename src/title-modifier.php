<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */

class ElevateTitleModifier {
	static function apply_title_template( $current_title, $template ) {
		$to_find = array( 
			'#separator#', 
			'#home_desc#',
			'#home_title#',
			'#blog_desc#',
			'#page_name#',
			'#taxonomy_name#',
			'#author_name#',
			'#post_name#',
			'#site_name#',
			'#search_query#'
		);

		global $post;

		$to_replace = array( 
			ElevatePlugin::get_one_setting( 'title_separator' ),
			ElevatePlugin::get_one_setting( 'home_desc' ),
			ElevatePlugin::get_one_setting( 'home_title' ),
			ElevatePlugin::get_one_setting( 'blog_desc' ),
			$post->post_title,
			is_category() || is_tax() || is_tag() ? single_term_title( '', false ) : '',
			get_the_author_meta( 'display_name' ),
			$current_title ? $current_title : $post->post_title,
			ElevatePlugin::get_one_setting( 'site_name' ),
			isset( $_GET[ 's' ] ) ? $_GET[ 's' ] : ''
		);

		return str_replace( $to_find, $to_replace, $template );
	}
}