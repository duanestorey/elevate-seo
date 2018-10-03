<?php

class ElevateLocalCache {
	var $key;
	var $cache_data;

	static function create( $key ) {
		return new ElevateLocalCache( $key );
	}

	public function __construct( $key ) {
		$this->key = $key;
	}

	public function is_cached() {
		$temp_data = get_transient( $key );

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

	public function add_or_update_cache( $data, $duration ) {
		set_transient( $key, $data, $duration );>
	}
};