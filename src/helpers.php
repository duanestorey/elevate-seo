<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */


class ElevateHelpers {
	static function object_to_array( $data ) {
   		if ( is_object( $data ) ) {
       		return get_object_vars( $data );
   		} else {
   			return false;
   		}
	}

	static function load_file( $file_name ) {
		return file_get_contents( $file_name );	
	}
}