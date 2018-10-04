<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */


global $elevate_cron_handler;

require_once( dirname( __FILE__ ) . '/wp-async-task.php' );

class ElevateCronHandler extends WP_Async_Task {
	protected $action = 'elevate_cron_job';

 	public function __construct() {
 		parent::__construct();
	}		

	protected function run_action() {
		define( 'ELEVATE_IN_CRON', 1 );

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Asynchronously running CRON' );

		do_action( 'elevate_do_handle_nightly_cron' );

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Asynchronously finishing CRON' );
	}

	protected function prepare_data( $data ) {
		return array();
	}	
}

function elevate_check_cron_job() {
	global $elevate_cron_handler;
	$elevate_cron_handler = new ElevateCronHandler;

	$has_been_set = get_option( 'elevate_cron_set_by', 0 );
	if ( $has_been_set != ELEVATE_PLUGIN_VERSION ) {
		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Setting up CRON job' );

		wp_clear_scheduled_hook( 'elevate_cron_job' );
		wp_schedule_event( time() + 3, 'daily', 'elevate_cron_job' );

		update_option( 'elevate_cron_set_by', ELEVATE_PLUGIN_VERSION );
	}
}

function elevate_handle_nightly_cron() {
	elevate_load_plugin();

	global $elevate_plugin;
	$elevate_plugin->handle_cron();
}

function elevate_activate() {
}

function elevate_deactivate() {
	ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'BEGIN Deactivation hook' );	

	wp_clear_scheduled_hook( 'elevate_cron_job' );
	delete_option( 'elevate_cron_set_by' );

	ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'DONE Deactivation hook' );	
}

add_action( 'elevate_do_handle_nightly_cron', 'elevate_handle_nightly_cron' );