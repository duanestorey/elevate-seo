<?php

global $wp_prefix;

define( 'ELEVATE_DB_OPTION', 'elevate_db_version' );
define( 'ELEVATE_DB_VERSION', '1.0.8' );

class ElevateDB {
	var $db_version;

	public function __construct() {
		$db_version = 0;
	}

	private function _modify_or_add_db( $name ) {
		$pre_schema = file_get_contents( ELEVATE_PLUGIN_DIR . '/include/schema/' . $name . '.sql' );

		global $wpdb;

		$schema = str_replace( array( '{prefix}', '{charset_collate}' ), array( $wpdb->prefix, $wpdb->get_charset_collate() ), $pre_schema );

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $schema );
	}

	private function _clean_url( $url ) {
		$fragments = parse_url( $url );

		return $fragments[ 'scheme' ] . '://' . $fragments[ 'host' ] . $fragments[ 'path' ];
	}

	public function check() {
		$this->db_version = get_option( ELEVATE_DB_OPTION, 0 );

		if ( !$this->db_version || $this->db_version != ELEVATE_DB_VERSION ) {
			// fresh install

			$this->_modify_or_add_db( 'speed' );
			$this->_modify_or_add_db( 'redirects' );
			$this->_modify_or_add_db( '404' );
			$this->_modify_or_add_db( 'search' );

			update_option( ELEVATE_DB_OPTION, ELEVATE_DB_VERSION );
		}
	}

	public function add_pagespeed( $url, $desktop_info, $mobile_info ) {
		global $wpdb;

		$this->check();

		$query = $wpdb->prepare( 'INSERT INTO ' . $wpdb->prefix . 'elevate_speed (url, desktop_speed, desktop_size, desktop_resources, mobile_speed, mobile_size, mobile_resources) VALUES (%s,%0.2f,%d,%d,%0.2f,%d,%d)', $this->_clean_url( $url ), $desktop_info->speed, $desktop_info->response_bytes, $desktop_info->resources, $mobile_info->speed, $mobile_info->response_bytes, $mobile_info->resources );
		if ( $query ) {
			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Executing query ' . $query );
			$wpdb->query( $query ) ;
		}
	}

	public function get_pagespeed_data( $offset, $days ) {
		global $wpdb;

		$start_time = time() - ($offset + $days)*24*HOUR_IN_SECONDS;
		$end_time = time() - ( $offset - 1 )*24*HOUR_IN_SECONDS;

		$query = $wpdb->prepare( "SELECT date(test_time) as test_time,desktop_speed,mobile_speed FROM " . $wpdb->prefix . "elevate_speed WHERE test_time >= '%s' AND test_time <= '%s' GROUP BY date(test_time) ORDER BY test_time ASC LIMIT 7", date( 'Y-m-d', $start_time ), date ( 'Y-m-d', $end_time ) );

		$results = $wpdb->get_results( $query );

		$prepped_results = new stdClass;
		if ( $results ) {
			$prepped_results->labels = array();
			$prepped_results->mobile_data = array();
			$prepped_results->desktop_data = array();

			foreach( $results as $result ) {
				$label = date( 'M d', strtotime( $result->test_time ) );
				$prepped_results->	labels[] = $label;
				$prepped_results->mobile_data[] = $result->mobile_speed;
				$prepped_results->desktop_data[] = $result->desktop_speed;
			}
		}

		return $prepped_results;
	}

	public function get_search_data( $offset, $days ) {
		global $wpdb;

		$start_time = time() - ($offset + $days)*24*HOUR_IN_SECONDS;
		$end_time = time() - ( $offset - 1 )*24*HOUR_IN_SECONDS;

		$query = $wpdb->prepare( "SELECT date(test_time) as test_time,impressions,ctr FROM " . $wpdb->prefix . "elevate_search WHERE test_time >= '%s' AND test_time <= '%s' GROUP BY date(test_time) ORDER BY test_time ASC LIMIT 7", date( 'Y-m-d', $start_time ), date ( 'Y-m-d', $end_time ) );

		$results = $wpdb->get_results( $query );

		$prepped_results = new stdClass;
		if ( $results ) {
			$scale_factor = 1.0;

			$prepped_results->labels = array();
			$prepped_results->clicks = array();
			$prepped_results->impressions = array();

			foreach( $results as $result ) {
				$label = date( 'M d', strtotime( $result->test_time ) );
				$prepped_results->	labels[] = $label;
				$prepped_results->impressions[] = round( $result->impressions );
				$prepped_results->clicks[] = round( ( $result->impressions * $result->ctr ) / 100.0 );
			}
		}

		return $prepped_results;
	}	

	public function get_search_404_data( $offset, $days ) {
		global $wpdb;

		$start_time = time() - ($offset + $days)*24*HOUR_IN_SECONDS;
		$end_time = time() - ( $offset - 1 )*24*HOUR_IN_SECONDS;

		$query = $wpdb->prepare( "SELECT date(test_time) as test_time,errors_not_found FROM " . $wpdb->prefix . "elevate_search WHERE test_time >= '%s' AND test_time <= '%s' GROUP BY date(test_time) ORDER BY test_time ASC LIMIT 7", date( 'Y-m-d', $start_time ), date ( 'Y-m-d', $end_time ) );

		$results = $wpdb->get_results( $query );

		$prepped_results = new stdClass;
		if ( $results ) {

			$prepped_results->labels = array();
			$prepped_results->errors_not_found = array();

			foreach( $results as $result ) {
				$label = date( 'M d', strtotime( $result->test_time ) );
				$prepped_results->labels[] = $label;
				$prepped_results->errors_not_found[] = $result->errors_not_found;
			}
		}

		return $prepped_results;
	}		

	public function add_404( $url ) {
		global $wpdb;

		$this->check();

		$result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'elevate_404 WHERE url = %s', $this->_clean_url( $url ) ) );
		if ( $result ) {
			
			$query = $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . 'elevate_404 SET last_date=FROM_UNIXTIME(%d), count=%d WHERE id=%d', time(), $result->count + 1, $result->id ) ;
			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Executing query ' . print_r( $result, true ) . $query );
			$wpdb->query( $query );
		} else {
			$wpdb->query( $wpdb->prepare( 'INSERT INTO ' . $wpdb->prefix . 'elevate_404 ( url,count ) VALUES( %s, %d )', $this->_clean_url( $url ), 1 ) );
		}
	}

	public function add_search( $impressions, $clicks, $ctr, $avg_pos, $error_not_found, $error_not_auth, $error_server ) {
		global $wpdb;

		$this->check();

		$wpdb->query( $wpdb->prepare( 'INSERT INTO ' . $wpdb->prefix . 'elevate_search ( impressions,clicks,ctr,avg_pos,errors_not_found,errors_not_auth,errors_server ) VALUES( %d, %d, %f, %f, %d, %d, %d)', $impressions, $clicks, $ctr, $avg_pos, $error_not_found, $error_not_auth, $error_server ) );
	}	
}