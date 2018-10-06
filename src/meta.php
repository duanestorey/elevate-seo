<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */

class ElevateMeta {
	static function output_property( $property, $content ) {
		echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n"; 
	}

	static function output_property_url( $property, $content ) {
		echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_url( $content ) . '" />' . "\n"; 
	}	

	static function output_name( $property, $content ) {
		echo '<meta name="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n"; 
	}

	static function output_name_url( $property, $content ) {
		echo '<meta name="' . esc_attr( $property ) . '" content="' . esc_url( $content ) . '" />' . "\n"; 
	}		
}