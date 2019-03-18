<?php
/*
Plugin Name: Elevate SEO
Plugin URI: http://elevatewp.io
Description: Search Engine Optimization and Performance Monitoring Suite
Author: Duane Storey
Tags: SEO, Sitemap, Google, Page Speed
Version: 1.2.2
Author URI: https://elevatewp.io/
Text Domain: elevate-seo
*/

define( 'ELEVATE_PLUGIN_VERSION', '1.2.2' );
define( 'ELEVATE_PLUGIN_URL', plugins_url( '', __FILE__  ) );
define( 'ELEVATE_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'ELEVATE_PLUGIN_SRC_DIR', dirname( __FILE__ ) . '/src/' );

require_once( ELEVATE_PLUGIN_DIR . '/src/config.php' );
require_once( ELEVATE_PLUGIN_DIR . '/src/elevate-class.php' );
require_once( ELEVATE_PLUGIN_DIR . '/src/cron.php' );
require_once( ELEVATE_PLUGIN_DIR . '/src/api.php' );
require_once( ELEVATE_PLUGIN_DIR . '/src/debug.php' );
require_once( ELEVATE_PLUGIN_DIR . '/src/local-cache.php' );
require_once( ELEVATE_PLUGIN_DIR . '/dist/php/cache-bust.php' );

function elevate_load_plugin() {
	global $elevate_plugin;

	if ( !$elevate_plugin ) {
		$elevate_plugin = new ElevatePlugin;	
		$elevate_plugin->initialize();
	}
}

function get_elevate_plugin() {
	global $elevate_plugin;
	return $elevate_plugin;
}

add_action( 'plugins_loaded', 'elevate_load_plugin' );

register_activation_hook( __FILE__, 'elevate_activate' );
register_deactivation_hook( __FILE__, 'elevate_deactivate' );


