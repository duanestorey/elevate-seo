<?php

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