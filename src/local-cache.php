<?php

define( 'ELEVATE_CACHE_DEFAULT_DURATION', 24*HOUR_IN_SECONDS );
define( 'ELEVATE_CACHE_PREFIX', 'elevate_' );

class ElevateLocalCache {
	var $key;
	var $cache_data;

	static function create( $key ) {
		return new ElevateLocalCache( $key );
	}

	public function __construct( $key ) {
		$this->key = ELEVATE_CACHE_PREFIX . $key;
	}

	public function is_cached() {
		$temp_data = get_transient( $this->key );

		if ( $temp_data !== false ) {
			// In the cache
			$this->cache_data = $temp_data;

			return true;
		} else {
			return false;
		}
	}

	public function get_data() {
		return $this->cache_data;
	}

	public function add_to_cache( $data, $duration = ELEVATE_CACHE_DEFAULT_DURATION ) {
		set_transient( $this->key, $data, $duration );
	}

	static function delete_entry( $key ) {
		delete_transient( ELEVATE_CACHE_PREFIX . $key );
	}

	public function delete() {
		ElevateLocalCache::delete_entry( $this->key );
	}
};