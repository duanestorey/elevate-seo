<?php

require_once( 'http-request.php' );

define( 'ELEVATE_GOOGLE_PS_URL', 'https://www.googleapis.com/pagespeedonline/v4/runPagespeed' );
define( 'ELEVATE_PS_CACHE_VERSION', '1.0.0' );

class ElevatePageSpeed {
	function __construct() {}

	public function parse_google_data( $google_data ) {
		$internal_data = new stdClass;

		$decoded_data = json_decode( $google_data );

		$internal_data->speed = $decoded_data->ruleGroups->SPEED->score;
		$internal_data->response_bytes = $decoded_data->pageStats->overTheWireResponseBytes;
		$internal_data->resources = $decoded_data->pageStats->numberResources;

		return $internal_data;
	}

	private function _get_cache_key( $url ) {
		$cache_key = $url . ELEVATE_PS_CACHE_VERSION;

		return 'elevate_speed_' . md5( $cache_key );				
	}

	public function check_page( $url ) {
		require_once( 'http-request.php' );

		$desktop_request_url = ELEVATE_GOOGLE_PS_URL . '?url=' . urlencode( $url );
		$mobile_request_url = ELEVATE_GOOGLE_PS_URL . '?url=' . urlencode( $url ) . '&strategy=mobile';

		$desktop_result = ElevateHTTP::make_request( $desktop_request_url );
		$mobile_result = ElevateHTTP::make_request( $mobile_request_url );

		if ( $desktop_result && $mobile_result ) {
			$desktop_info = $this->parse_google_data( $desktop_result );
			$mobile_info = $this->parse_google_data( $mobile_result );

			$speed_result = new stdClass;
			$speed_result->desktop = $desktop_info;
			$speed_result->mobile = $mobile_info;

			return $speed_result;
		} else {
			return false;	
		}
	}
}