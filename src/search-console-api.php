<?php

class ElevateSearchConsole {
	public function __construct() {}

	public function refresh_token( $token ) { 
		require_once( 'api.php' ); 

		$elevate_api = new ElevateAPI; 
		$result = $elevate_api->refresh_oauth_token( $token );    

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Refreshing OAUTH token via ELEVATE API, result is [' . $result . ']' ); 

		return $result;
	} 	

	public function get_token( $api_key ) { 
		require_once( 'api.php' ); 

		$elevate_api = new ElevateAPI; 

		return $elevate_api->get_oauth_token( $api_key ); 
	} 	

	private function _make_authenticated_call( $url, $access_token, $request_type = 'GET', $post_params = false ) {
		$params = array(
			'method' => $request_type,
			'headers' => array( 'Authorization' => 'Bearer ' . $access_token ),
			'user-agent' => 'Elevate SEO ' . ELEVATE_PLUGIN_VERSION,
			'timeout' => 15
		);

		if ( $params[ 'method' ] === 'PUT' ) {
			$params[ 'headers' ][ 'Content-length' ] = 0;
		} else if ( $params[ 'method' ] == 'POST' ) {
			$params[ 'body' ] = $post_params;
		}

		ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Making authenticated call, with params [' . print_r( $params, true ) . '], to URL [' . $url . ']' );

		$result = wp_remote_request(
			$url,
			$params
		);

		if ( is_wp_error( $result ) ) {
			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, '	ERROR, result of call is [' . print_r( $result, true ) . ']' );

			return false;
		} else {
			return $result[ 'body' ];
		}		
	}

	private function _make_authenticated_post_json_call( $url, $access_token, $json_blob ) {
		$headers = array( 
			'Authorization' => ' Bearer ' . $access_token,
			'Content-type' => 'application/json',
			'Content-length' => strlen( $json_blob )
		);	

		$params = array(
			'method' => 'POST',
			'headers' => $headers,
			'user-agent' => 'Elevate SEO ' . ELEVATE_PLUGIN_VERSION,
			'body' => $json_blob,
			'timeout' => 15
		);

		ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Making authenticated JSON call, with params [' . print_r( $params, true ) . '], to URL [' . $url . ']' );

		$result = wp_remote_request(
			$url,
			$params
		);

		if ( is_wp_error( $result ) ) {
			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, '	ERROR, result of call is [' . print_r( $result, true ) . ']' );

			return false;
		} else {
			//ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, '	SUCCESS, result of call is [' . $result[ 'body' ] . ']' );

			return $result[ 'body' ];
		}	
	}

	public function get_sites( $access_token ) {
		$url = 'https://www.googleapis.com/webmasters/v3/sites';

		return $this->_make_authenticated_call( $url, $access_token );
	}

	public function get_sitemaps( $access_token, $site_url ) {
		$url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode( $site_url ) . '/sitemaps';

		return $this->_make_authenticated_call( $url, $access_token );
	}	

	public function add_sitemap( $access_token, $site_url, $sitemap_url ) {
		$url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode( $site_url ) . '/sitemaps/' . urlencode( $sitemap_url );

		return $this->_make_authenticated_call( $url, $access_token , 'PUT' );
	}		

	public function add_site( $access_token, $site_url ) {
		$url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode( $site_url );

		return $this->_make_authenticated_call( $url, $access_token, 'PUT' );
	}

	public function check_site_verification( $access_token, $site_url ) {
		$url = 'https://www.googleapis.com/siteVerification/v1/webResource/' . urlencode( $site_url );

		$body = $this->_make_authenticated_call( $url, $access_token, 'GET' );
		return $body;
	}

	public function get_site_verification_token( $access_token, $site_url ) {
		$url = 'https://www.googleapis.com/siteVerification/v1/token';

		$verification_data = '{ "verificationMethod": "FILE", "site": { "identifier": "' . $site_url . '", "type": "SITE" } }';

		return $this->_make_authenticated_post_json_call( $url, $access_token, $verification_data );	
	}

	public function verify_site( $access_token, $site_url ) {
		$url = 'https://www.googleapis.com/siteVerification/v1/webResource?verificationMethod=FILE';

		$verification_data = '{ "site": { "identifier": "' . $site_url . '", "type": "SITE" } }';

		return $this->_make_authenticated_post_json_call( $url, $access_token, $verification_data );	
	}	

	public function get_analytics_accounts( $access_token ) {
		$url = 'https://www.googleapis.com/analytics/v3/management/accountSummaries';

		$body = $this->_make_authenticated_call( $url, $access_token, 'GET' );
		return $body;		
	}

	public function add_analytics_web_property( $access_token, $account_id, $analytics_url, $name ) {
		$url = 'https://www.googleapis.com/analytics/v3/management/accounts/' . $account_id . '/webproperties';

		$json_data = new stdClass;
		$json_data->websiteUrl = $analytics_url;
		$json_data->name = $name;

		$body = $this->_make_authenticated_post_json_call( $url, $access_token, json_encode( $json_data ) );
		return $body;
	}

	public function add_analytics_web_view( $access_token, $account_id, $web_property_id ) {
		$url = 'https://www.googleapis.com/analytics/v3/management/accounts/' . $account_id . '/webproperties/' . $web_property_id . '/profiles';

		$json_data = new stdClass;
		$json_data->name = 'Elevate SEO Data';

		$body = $this->_make_authenticated_post_json_call( $url, $access_token, json_encode( $json_data ) );
		return $body;
	}


	public function revoke_token( $token ) {
		$url = 'https://accounts.google.com/o/oauth2/revoke?token=' . urlencode( $token );

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Attempting to revoke token [' . $token . ']' );

		$params = array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,					
			'user-agent' => 'Elevate SEO ' . ELEVATE_PLUGIN_VERSION,
			'body' => ''
		);

		$result = wp_remote_request(
			$url,
			$params
		);

		if ( is_wp_error( $result ) ) {
			return false;
		} else {
			return $result[ 'body' ];
		}	
	}

	public function get_crawl_errors( $access_token, $site_url ) {
		$url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode( $site_url ) . '/urlCrawlErrorsCounts/query';
		
		$body = $this->_make_authenticated_call( $url, $access_token, 'GET' );
		
		return $body;	
	}


	public function get_search_analytics( $access_token, $site_url ) {
		$url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode( $site_url ) . '/searchAnalytics/query';

		$end_date = time();
		$start_date = ( $end_date + 24*HOUR_IN_SECONDS) - 7*HOUR_IN_SECONDS*24;

		$search_params = '{ "startDate": "' . date( "Y-m-d", $start_date ) . '", "endDate": "' . date( "Y-m-d", $end_date ) . '"}';

		return $this->_make_authenticated_post_json_call( $url, $access_token, $search_params );	
	}		

	public function get_analytics_report( $access_token, $view_id ) {
		$url = 'https://analyticsreporting.googleapis.com/v4/reports:batchGet';

		$report_params = '{' .
		 '"reportRequests":'.
		  '['.
		    '{'.
		      '"viewId": "XXXX",'.
		      '"metrics": ['.
		        '{"expression": "ga:pageviews"},'.
		        '{"expression": "ga:sessions"}'.
		      '],'.
		     '"metricFilterClauses": [{'.
		          '"filters": [{'.
		              '"metricName": "ga:pageviews",'.
		              '"operator": "GREATER_THAN",'.
		              '"comparisonValue": "2"'.
		          '}]'.
		      '}]' .
		  '}]'.
		'}';

		return $this->_make_authenticated_post_json_call( $url, $access_token, $report_params );	
	}
}