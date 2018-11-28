<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */

require_once( 'http-request.php' );

define( 'ELEVATE_GOOGLE_PS_URL', 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed' );
define( 'ELEVATE_PS_CACHE_VERSION', '1.0.1' );

class ElevatePageSpeed {
	function __construct() {}

	public function parse_google_data( $google_data ) {
		$internal_data = new stdClass;

		$decoded_data = json_decode( $google_data );

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Parsing page speed data ' . print_r( $decoded_data, true ) );

		$internal_data->speed = $decoded_data->ruleGroups->SPEED->score;

		$internal_data->resources = $decoded_data->pageStats->numberResources;

		$internal_data->js_resources = $decoded_data->pageStats->numberJsResources;
		$internal_data->js_bytes = $decoded_data->pageStats->javascriptResponseBytes;

		$internal_data->css_resources = $decoded_data->pageStats->numberCssResources;
		$internal_data->css_bytes = $decoded_data->pageStats->cssResponseBytes;

		$internal_data->response_bytes = $decoded_data->pageStats->overTheWireResponseBytes;
		$internal_data->response_resources = $decoded_data->pageStats->numberStaticResources;

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