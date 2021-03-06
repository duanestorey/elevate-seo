<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */


define( 'ELEVATE_DEBUG_INFO', 1 );
define( 'ELEVATE_DEBUG_VERBOSE', 4 );

function ELEVATE_DEBUG( $level, $message ) {
	ElevateDebug::instance()->write( $level, $message );
}

class ElevateDebug {
	var $file_desc;
    var $enabled;
    var $level;

    private function __construct() {
        $this->enabled = false;
        $this->file_desc = false;
        $this->level = ELEVATE_DEBUG_INFO;
    }     

    public function set_level( $level ) {
        $this->level = $level;
    }

    public static function instance() {
        static $inst = null;
        if ( $inst === null ) {
        	$inst = new ElevateDebug();
        }

        return $inst;
    }

    public function enable( $enable = true ) {
        $this->enabled = $enable;

        if ( $enable ) {
            if ( !file_exists( ELEVATE_PLUGIN_DIR . '/debug' ) ) {
                mkdir( ELEVATE_PLUGIN_DIR . '/debug' );
            }

            $this->file_desc = fopen( ELEVATE_PLUGIN_DIR . '/debug/debug.txt', 'a+t' ); 
        } else {
            if ( $this->file_desc ) {
                fclose( $this->file_desc );
                $this->file_desc = false;
            }
        }   
    }

    public function set_debug_level( $level ) {
    	$this->debug_level = $level;
    }

    public function write( $level, $message ) {
        if ( !$this->enabled )
            return;

    	if ( flock( $this->file_desc, LOCK_EX ) ) {
    		fprintf( $this->file_desc, "%20s | %s\n", date( 'Ymd g:i:s' ), trim( $message, "\r\n" ) ) ;
    		fflush( $this->file_desc );
    		flock( $this->file_desc, LOCK_UN );
    	}
    }  
}