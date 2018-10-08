<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */

require_once( 'debug.php' );

class ElevatePlugin {
	var $settings;
	var $request_vars;
	var $sitemap_generator;
	var $elevate_db;
	var $debug_log;
	var $override_locale;

	function __construct() {
		$this->settings = false;
		$this->request_vars = array();
		$this->override_locale = false;
	}

	static function get_supported_languages() {
		return array( 
			'auto' => __( 'Automatic', 'elevate-seo' ),
			'en' => 'English',
			'es' => 'Español',
			'de' => 'Deutsch',
			'ru' => 'Росси́я',
			'fr' => 'Français',		
			'zh' => '漢語'
		); 		
	}

	static function get() {
		global $elevate_plugin;
		return $elevate_plugin;
	}

	static function get_settings() {
		global $elevate_plugin;
		return $elevate_plugin->settings;
	}

	static function get_one_setting( $setting_name ) {
		global $elevate_plugin;
		return $elevate_plugin->settings->{$setting_name};
	}	

	public function save_meta_box_info( $post_id, $post, $update  ) {
		$post_data = new stdClass;

		if ( !isset( $this->request_vars[ 'elevate_post_title' ] ) ) {
			return;
		}

		ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Save post called with ' . print_r( $post, true ) . ' RV => ' . print_r( $this->request_vars, true ) );

		$temp_title = $this->request_vars[ 'elevate_post_title' ];
		if ( $temp_title ) {
			$post_data->title = $temp_title;	
		}
		
		$temp_desc = $this->request_vars[ 'elevate_post_description' ];
		if ( $temp_desc ) {
			$post_data->desc = $temp_desc;	
		}
		
		$temp_canonical = $this->request_vars[ 'elevate_post_canonical' ];
		if ( $temp_canonical ) {
			$post_data->canonical = $temp_canonical;	
		}
		
		$temp_language = $this->request_vars[ 'elevate_post_language' ];
		if ( $temp_language ) {
			// only set it if we've manually overridden it, this will ensure we can make a site wide change later
			$post_data->language = $temp_language;
		}

		$temp_robots = $this->request_vars[ 'elevate_post_robots' ];
		if ( $temp_robots ) {
			$post_data->robots = $temp_robots;
		}		

		ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Saving POST META for POST ' . $post_id . ', data is ' . print_r( $post_data, true ) );

		update_post_meta( $post_id, 'elevate_seo_info', $post_data );
	}

	public function get_index_follow_info( $include_inherit = true ) {
		$params = array(
			'index,follow' => __( 'Index and follow links', 'elevate-seo' ),
			'index,nofollow' => __( 'Index but disallow link following', 'elevate-seo' ),
			'noindex,follow' => __( 'Don\'t index but allow link following', 'elevate-seo' ),
			'noindex,nofollow' => __( 'Don\'t index and also disallow link following', 'elevate-seo' )
		);

		if ( $include_inherit ) {
			$params = array_merge( array( 'inherit' => __( 'Inherit default status', 'elevate-seo' ) ), $params );
		}

		return $params;
	}

	public function get_saved_meta_box_info( $post_id, $is_term = false ) {
		$post_data = new stdClass;

		$post_data->title = '';
		$post_data->desc = '';
		$post_data->canonical = '';
		$post_data->language = 'auto';
		$post_data->robots = 'inherit';

		if ( $is_term ) {
			$saved_post_data = get_term_meta( $post_id, 'elevate_seo_info', true );
		} else {
			$saved_post_data = get_post_meta( $post_id, 'elevate_seo_info', true );
		}

		ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Saved POST META is ' . print_r( $saved_post_data, true ) );

		if ( isset( $saved_post_data ) ) {
			if ( isset( $saved_post_data->title ) ) {
				$post_data->title = $saved_post_data->title;
			}

			if ( isset( $saved_post_data->desc ) ) {
				$post_data->desc = $saved_post_data->desc;
			}	
			
			if ( isset( $saved_post_data->canonical ) ) {
				$post_data->canonical = $saved_post_data->canonical;
			}

			if ( isset( $saved_post_data->language ) ) {
				$post_data->language = $saved_post_data->language;
			}		

			if ( isset( $saved_post_data->robots ) ) {
				$post_data->robots = $saved_post_data->robots;
			}																					
		} 

		ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Merged POST META is ' . print_r( $post_data, true ) );

		// Import from Yoast if the user has chosen it
		if ( $this->settings->import_behaviour == 'yoast' ) {
			if ( !$post_data->title ) {
				$yoast_title = get_post_meta( $post_id, '_yoast_wpseo_title', true );

				if ( $yoast_title ) {
					$post_data->title = $yoast_title;
				}
			}

			if ( !$post_data->desc ) {
				$yoast_desc = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
				if ( $yoast_desc ) {
					$post_data->desc = $yoast_desc;
				}
			}
		}

		return $post_data;
	}

	public function handle_robots( $content, $public ) {
		if ( $public && $this->settings->robots_txt == 'enhanced' ) {
			return $this->get_robots_content();
		} else {
			return $content;
		}
	}

	private function _strip_home_url( $url ) {
		return untrailingslashit( str_replace( home_url(), '', $url ) );
	}

	public function get_robots_content() {
		$robots_source = file_get_contents( ELEVATE_PLUGIN_DIR . '/admin/templates/robots.txt' );
		if ( $robots_source ) {
			$upload_dir_info = wp_upload_dir();

			$robots_source = str_replace( 
				array(
					'[admin_url]',
					'[plugin_url]',
					'[upload_url]',
					'[sitemap_url]'
				),
				array(
					$this->_strip_home_url( admin_url() ),
					$this->_strip_home_url( plugins_url() ),
					$this->_strip_home_url( $upload_dir_info[ 'baseurl' ] ),
					$this->get_sitemap_url()
				),
				$robots_source
			);
		}

		return $robots_source;
	}

	public function setup_post_meta_box() {
		// Set up the Classic Editor

		$page_types = array( 'post', 'page' );

		// Add custom post types to our list
		$custom_types = get_post_types( array( 'public' => 'true', '_builtin' => false ), 'names' );

		$page_types = array_merge( $page_types, $custom_types );

		foreach( $page_types as $page_type ) {
		  	add_meta_box( 
		        'elevate_seo',
		         __( 'Elevate SEO / Search Titles &amp; Descriptions', 'elevate_seo' ),
		        array( &$this, 'post_meta_content' ),
		        $page_type,
		        'normal',
		        'high'
		    );	

		  	add_meta_box( 
		        'elevate_seo_side',
		        'Elevate SEO',
		        array( &$this, 'post_meta_side_content' ),
		        $page_type,
		        'side',
		        'high'
		    );		    
		}			    
	}

	function post_meta_content( $post ) {
		$post_info = clone $post;

		include( ELEVATE_PLUGIN_DIR . '/admin/meta/titles.php' );
	}

	function post_meta_side_content( $post ) {
		$post_info = clone $post;

		include( ELEVATE_PLUGIN_DIR . '/admin/meta/side.php' );		
	}

	public function handle_cron() {
		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'CRON BEGIN' );

		// clear the dashboard transients
		$this->_clear_dashboard_transients();

		// update our data
		$url_to_inspect = $this->_get_clean_site_url();
		
		$this->_update_search_and_analytics_data( $url_to_inspect );
		$this->get_page_speed( $url_to_inspect );
		$this->_get_analytics_page_data();

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'CRON END' );
	}

	public function initialize() {
		add_theme_support( 'post-thumbnails' ); 

		add_action( 'admin_menu', array( &$this, 'create_admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_bar_menu', array( &$this, 'admin_tool_bar' ), 100 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'handle_admin_enqueue_scripts' ) );
		add_action( 'admin_head', array( &$this, 'handle_admin_head' ) );

		add_action( 'wp_ajax_elevate_ajax', array( &$this, 'handle_admin_ajax' ) );

		add_action( 'add_meta_boxes', array( &$this, 'setup_post_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_info' ), 10, 3 );	

		add_action( 'init', array( &$this, 'handle_init' ) );
		add_action( 'wp', array( &$this, 'handle_wp' ) );
		add_filter( 'wp_title', array( &$this, 'handle_title' ), -1 );
		add_filter( 'pre_get_document_title', array( &$this, 'handle_title' ), -1 );
		add_action( 'wp_head', array( &$this, 'handle_wp_head' ) );
		add_action( 'wp_footer', array( &$this, 'handle_footer' ) );
	
		add_action( 'publish_post', array( &$this, 'refresh_sitemap' ) );
		add_action( 'publish_page', array( &$this, 'refresh_sitemap' ) );

		add_filter( 'get_canonical_url', array( &$this, 'filter_wp_canonical' ), 10, 2 ); 
		add_filter( 'robots_txt', array( &$this, 'handle_robots' ), 10, 2 );
		add_action( 'elevate_sitemap_update', array( &$this, 'elevate_sitemap_update' ), 10, 2 );
		add_action( 'after_setup_theme', array( &$this, 'setup_languages' ) );
		add_filter( 'locale', array( &$this, 'handle_locale' ) );

		add_filter( 'admin_body_class', array( &$this, 'handle_admin_body_class' ) );

		elevate_check_cron_job();

		$this->debug_log = ElevateDebug::instance();

		$this->_cleanup_get_and_post();

		if ( !$this->settings ) {
			$this->settings = new stdClass;
			$this->_load_saved_settings();	
		}	

		$this->debug_log->enable( $this->settings->enable_debug_log );

		// Load the sitemap generator
		// TODO: Defer this to later
		require_once( dirname( __FILE__ ) . '/sitemap.php' );
		$this->sitemap_generator = new ElevateSitemap( $this, $this->_check_sitemap_status(), $this->_get_sitemap_dir(), $this->_get_sitemap_params() );

		// Load our class for manipulating the DB tables
		require_once( dirname( __FILE__ ) . '/elevate-db.php' );
		$this->elevate_db = new ElevateDB;

		$this->check_for_version_update();
	}

	public function check_for_version_update() {
		$result = get_option( 'elevate_last_version_check', 0 );
		if ( !$result ) {
			$this->_clear_dashboard_transients();

			update_option( 'elevate_last_version_check', ELEVATE_PLUGIN_VERSION );
		}
	}

	public function handle_admin_body_class( $body_class ) {
		if ( !$this->has_google_tokens() ) {
			$body_class .= ' elevate_no_tokens';
		}

		return $body_class;
	}

	public function handle_tax_save_edit_form( $tag ) {
		$term = get_term( $tag );

		$post_data = new stdClass;

		if ( !isset( $this->request_vars[ 'elevate_post_title' ] ) ) {
			return;
		}

		ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Save term called with ' . print_r( $tag, true ) . ' RV => ' . print_r( $this->request_vars, true ) );

		$temp_title = $this->request_vars[ 'elevate_post_title' ];
		if ( $temp_title ) {
			$post_data->title = $temp_title;	
		}
		
		$temp_desc = $this->request_vars[ 'elevate_post_description' ];
		if ( $temp_desc ) {
			$post_data->desc = $temp_desc;	
		}
		
		$temp_canonical = $this->request_vars[ 'elevate_post_canonical' ];
		if ( $temp_canonical ) {
			$post_data->canonical = $temp_canonical;	
		}
		
		$temp_language = $this->request_vars[ 'elevate_post_language' ];
		if ( $temp_language ) {
			// only set it if we've manually overridden it, this will ensure we can make a site wide change later
			$post_data->language = $temp_language;
		}

		$temp_robots = $this->request_vars[ 'elevate_post_robots' ];
		if ( $temp_robots ) {
			$post_data->robots = $temp_robots;
		}		

		ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Saving POST META for POST ' . $post_id . ', data is ' . print_r( $post_data, true ) );

		update_term_meta( $term->term_id, 'elevate_seo_info', $post_data );
	}

	public function handle_tax_edit_form( $tag ) {
		global $post_info;

		$post_info = new stdClass;
		$post_info->ID = $this->request_vars[ 'tag_ID' ];
		$post_info->is_term = 1;
		$post_info->post_type = 'term';

		$term = get_term( $post_info->ID );

		$post_info->post_title = $term->name;
		$post_info->post_content = $term->description;

		$post_info->permalink = get_term_link( $term );

		require_once( dirname( __FILE__ ) . '/../admin/meta/titles.php' );
	}

	private function _should_override_locale() {
		if ( get_the_ID() && !is_admin() && !defined( 'ELEVATE_IN_CRON' ) ) {
			ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'In locale override'  );

			$meta_box_info = $this->get_saved_meta_box_info( get_the_ID() );
			$post = get_post( get_the_ID() );

			if ( $meta_box_info->language != 'inherit' ) {
				return $meta_box_info->language;
			}
		}

		return false;		
	}

	public function handle_locale( $locale ) {
		if ( $this->override_locale ) {
			return $this->override_locale;
		}

		$new_locale = $this->_should_override_locale();

		if ( $new_locale && $new_locale != 'auto' ) {
			$this->override_locale = $new_locale;

			return $new_locale;
		} else {
			return $locale;	
		}
	}	

	public function setup_languages() {
		// set up languages
		if (  $this->settings->selected_locale != 'auto' && $this->settings->selected_locale != 'en' ) {
			// load the associated translation file
			load_textdomain( 'elevate-seo', ELEVATE_PLUGIN_DIR . '/lang/' . $this->settings->selected_locale . '.mo' ); 
		}	
	}

	public function elevate_sitemap_update( $status, $entries ) {
		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'SITEMAP UPDATE' . $status . ' ' . $entries );

		if ( $status ) {
			touch( $this->_get_sitemap_dir() . '/generating.txt' );
		} else {
			unlink( $this->_get_sitemap_dir() . '/generating.txt' );
		}

		if ( $entries ) {
			$f = fopen( $this->_get_sitemap_dir() . '/info.txt', 'w+t' );
			if ( $f ) {
				fwrite( $f, $entries );
				fclose( $f );
			}
		}
	}

	public function filter_wp_canonical( $url, $post ) {
		if ( $this->settings->insert_canonical ) {
			// we're handling canonical, and not WordPress
			return false;
		} else {
			return $url;
		}
	}

	public function refresh_sitemap() {
		$this->_generate_sitemap( true );
	}

	public function handle_footer() {
		if ( $this->settings->insert_analytics ) {
			echo $this->settings->analytics_code;
		}
	}

	private function _create_toolbar_args( $id, $title, $link, $parent ) {
		return array(
			'id'        => $id,
            'title'     => $title,
            'href'      => $link,
            'parent'    => $parent	    	
	    );
	}

	public function admin_tool_bar( $admin_bar ) {
		$toolbar_nonce = wp_create_nonce( 'elevate_toolbar' );

		if ( $this->settings->wizard_complete ) {
			$admin_bar->add_node( $this->_create_toolbar_args( 'elevate_top', 'Elevate SEO', admin_url( 'admin.php?page=elevate_dashboard' ), false ) );
	    	$admin_bar->add_node( $this->_create_toolbar_args( 'elevate_dashboard', __( 'Dashboard', 'elevate-seo' ), admin_url( 'admin.php?page=elevate_dashboard' ), 'elevate_top' ) );
		} else {
			$admin_bar->add_node( $this->_create_toolbar_args( 'elevate_top', 'Elevate SEO', admin_url( 'admin.php?page=elevate_plugin' ), false ) );
	   	 	$admin_bar->add_node( $this->_create_toolbar_args( 'elevate_dashboard', __( 'Get Started', 'elevate-seo' ), admin_url( 'admin.php?page=elevate_plugin' ), 'elevate_top' ) );
		}

	    if ( strpos( $_SERVER['REQUEST_URI'], 'page=elevate_dashboard' ) !== false ) {
	    	$admin_bar->add_node( $this->_create_toolbar_args( 'elevate_refresh', __( 'Refresh Statistics', 'elevate-seo' ), admin_url( 'admin.php?page=elevate_dashboard&elevate_action=refresh_stats&toolbar_nonce=' . $toolbar_nonce ), 'elevate_top' ) );
	    }		

		if ( $this->settings->cdn_url && $this->settings->use_cdn ) {
			$admin_bar->add_node( $this->_create_toolbar_args( 'elevate_update_cache', __( 'Clear Image Cache', 'elevate-seo' ), add_query_arg( array( 'elevate_action' => 'clear_cache', 'toolbar_nonce' => $toolbar_nonce ), $_SERVER['REQUEST_URI'] ), 'elevate_top' ) );	
		}

	    $admin_bar->add_node( $this->_create_toolbar_args( 'elevate_update_sitemap', __( 'Update Sitemap', 'elevate-seo' ), admin_url( 'admin.php?page=elevate_dashboard&elevate_action=update_sitemap&toolbar_nonce=' . $toolbar_nonce ), 'elevate_top' ) );	    	 
	}

	private function _cleanup_get_and_post() {
		// Use to fix the quotation issues on some systems
		// after this is called, internally we only look at $this->request_vars instead of $_POST and $_GET
		$to_process = array_merge( $_POST, $_GET );

		if ( count( $to_process ) ) {
			foreach( $to_process as $key => $value ) {
				if ( get_magic_quotes_gpc() ) {
					$this->request_vars[ $key ] = stripslashes( $to_process[ $key ] );
				} else {
				    $this->request_vars[ $key ] = $to_process[ $key ];
				}
			}
		} 
	}

	private function _ajax_failure( $error, $reason ) {
		$result = new stdClass;
		$result->code = ELEVATE_AJAX_FAILURE;
		$result->error = $error;
		$result->reason = $reason;

		echo json_encode( $result );
		wp_die();
	}

	private function _ajax_success( $body = '', $reason = 'ok' ) {
		$result = new stdClass;
		$result->code = ELEVATE_AJAX_SUCCESS;
		$result->reason = $reason;
		$result->body = $body;

		echo json_encode( $result );
		wp_die();		
	}

	private function _get_google_tokens( $refresh_if_invalid = true ) {
		$google_tokens = get_option( ELEVATE_GOOGLE_TOKENS_KEY, false );

		if ( is_object( $google_tokens ) ) {
			$refresh_time = $google_tokens->expire_time - 30; // do it at least 30 seconds before
			if ( time() > $refresh_time && $refresh_if_invalid ) {

				ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Attempting to refresh Google tokens, ' . print_r( $google_tokens, true ) );

				if ( !isset( $google_tokens->refresh_token ) ) {
					ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Token is missing refresh information'  );
				}

				ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Refreshing Google tokens' );

				require_once( 'search-console-api.php' );

				$search_console = new ElevateSearchConsole();
				$result = $search_console->refresh_token( $google_tokens->refresh_token );

				ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, "\tResult from token refresh API calls is [" . print_r( $result, true ) . "]" );

				if ( $result ) {
					$decoded_result = json_decode( $result );

					if ( isset( $decoded_result->access_token )) {
						ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Token refresh successful, updating with new access token' );

						$google_tokens->access_token = $decoded_result->access_token;
						$google_tokens->token_time = time();
						$google_tokens->expire_time = $google_tokens->token_time + $decoded_result->expires_in;

						update_option( ELEVATE_GOOGLE_TOKENS_KEY, $google_tokens, false );
					} else {
						ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Token refresh failed' );
					}	
				}
			} else {
				ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, "Using Google API tokens [" . print_r( $google_tokens, true ) . "]" );
			}
		}

		return $google_tokens;
	}

	public function has_google_tokens( $refresh_if_invalid = false ) {
		$tokens = $this->_get_google_tokens( $refresh_if_invalid );

		return ( isset( $tokens->access_token ) && isset( $tokens->token_time ) );
	}

	private function _convert_oauth_code_to_token( $code ) {
		require_once( 'search-console-api.php' );

		ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Converting code into access and refresh token, code is [' . $code . ']' );

		$search_console = new ElevateSearchConsole();
		$result = $search_console->get_token( $code );

		if ( $result ) {
			$decoded_result = json_decode( $result );

			if ( isset( $decoded_result->access_token )) {
				$google_tokens = new stdClass;

				$google_tokens->access_token = $decoded_result->access_token;
				$google_tokens->token_time = time();
				$google_tokens->expire_time = $google_tokens->token_time + $decoded_result->expires_in;

				if ( isset( $decoded_result->refresh_token ) ) {
					$google_tokens->refresh_token = $decoded_result->refresh_token;	

					ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Setting REFRESH token to ' . $decoded_result->refresh_token );
				} else {
					ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Response missing REFRESH token - do we already have it?' );
				}

				update_option( ELEVATE_GOOGLE_TOKENS_KEY, $google_tokens, false );

				$this->_clear_dashboard_transients();

				return true;
			}
		} 

		return false;		
	}

	private function _update_search_and_analytics_data( $url ) {
		if ( defined( 'ELEVATE_NO_CACHE' ) || false === ( $search = get_transient( 'elevate_site_search_' . md5( $url ) ) ) ) {	
			$search = new stdClass;

			$search->crawl_errors = $this->get_crawl_errors( $url );
			$search->analytics = $this->get_search_analytics( $url );

			$this->elevate_db->add_search( 
				$search->analytics->impressions_raw, 
				$search->analytics->clicks,
				$search->analytics->ctr, 
				$search->analytics->position,
				$search->crawl_errors->not_found,
				$search->crawl_errors->permissions,
				$search->crawl_errors->server_error 
			);	

			ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, 'Looking at analytics data ' . print_r( $search, true ) );

			set_transient( 'elevate_site_search_' . md5( $url ), $search, ELEVATE_TRANSIENT_SITE_INFO );
		}		

		return $search;
	}

	private function _is_on_google_search() {
		$site_found = false;

		$google_tokens = $this->_get_google_tokens();

		if ( !$google_tokens ) {
			return $site_found;
		}

		$search_console = new ElevateSearchConsole();
		$result = $search_console->get_sites( $google_tokens->access_token );

		if ( $result ) {
			$decoded_result = json_decode( $result );

			$site_found = false;
			$home_url = trailingslashit( home_url() );
			if ( isset( $decoded_result->siteEntry ) ) {
				foreach( $decoded_result->siteEntry as $result ) {
					// Check to see if this site matches
					ELEVATE_DEBUG( ELEVATE_DEBUG_VERBOSE, "Searching SC sites, comparing " . $result->siteUrl . ' to ' . $home_url );
					if ( trailingslashit( $result->siteUrl ) == $home_url ) {
						$site_found = true;
						break;
					}
				}							
			}
		}

		return $site_found;
	}

	private function _is_site_verified( $url = false ) {
		$is_verified = 0;

		if ( $url === false ) {
			$url = $this->_get_clean_site_url();
		}

		$google_tokens = $this->_get_google_tokens();

		if ( $google_tokens ) {
			$search_console = new ElevateSearchConsole();
			$result = $search_console->check_site_verification( $google_tokens->access_token, $url );

			$decoded_result = json_decode( $result );
			if ( isset( $decoded_result->error->code ) && $decoded_result->error_code = 403 ) {
				// We aren't the site owner
				$is_verified = 0;
			} else if ( isset( $decoded_result->owners ) ) {
				$is_verified = 1;
			}
		}

		return $is_verified;	
	}

	private function _has_configured_sitemap() {
		$has_sitemap = 0;

		$google_tokens = $this->_get_google_tokens();

		if ( $google_tokens ) {

			$search_console = new ElevateSearchConsole();
			$result = $search_console->get_sitemaps( $google_tokens->access_token, trailingslashit( home_url() ) );

			if ( $result ) {
				$decoded_result = json_decode( $result );
				if ( isset( $decoded_result->sitemap ) ) {
					$desired_path = trailingslashit( home_url() ) . 'sitemap.xml';

					foreach( $decoded_result->sitemap as $sitemap ) {
						if ( $desired_path == $sitemap->path ) {
							// This entry already exists
							$has_sitemap = 1;
						}
					}
				}
			}
		}

		return $has_sitemap;	
	}

	private function _has_analytics_property( $all_accounts ) {
		// TODO: Check for scenario where they have multiple accounts, one of which may not be writable
		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Checking for existing analytics property' );
		if ( $all_accounts->kind === 'analytics#accountSummaries' ) {
			foreach( $all_accounts->items as $item ) {
				if ( $item->kind == 'analytics#accountSummary' ) {
					$account_id = $item->id;

					foreach( $item->webProperties as $property ) {
						if ( trailingslashit( $property->websiteUrl ) == trailingslashit( home_url() ) ) {
							ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Found existing web property' );

							$property->account_id = $account_id;

							return $property;
						}
					}
				}	
			}		
		}

		return false;
	}

	private function _get_analytics_page_data() {
		$analytics_cache = ElevateLocalCache::create( 'analytics_cache' );
		if ( !$analytics_cache->is_cached() ) {
			$accounts = $this->_get_analytics_accounts();

			if ( $accounts ) {
				//echo $account_id; die;
				require_once( 'search-console-api.php' );
				$search_console = new ElevateSearchConsole();

				$google_tokens = $this->_get_google_tokens();

				foreach( $accounts as $account_id => $account ) {
					$views = $search_console->get_analytics_views( $google_tokens->access_token, $account_id, '~all' );
					if ( $views ) {
						$decoded_views = json_decode( $views );

						if ( $decoded_views ) {
							foreach( $decoded_views->items as $key => $view ) {
								if ( $this->_get_clean_url( $view->websiteUrl ) == $this->_get_clean_site_url() ) {

									$data = $search_console->get_analytics_report( $google_tokens->access_token, $view->id );
									if ( $data ) {
										$decoded_data = json_decode( $data );
										if ( isset( $decoded_data->reports ) && isset( $decoded_data->reports[0] ) ) {
											$page_data = new stdClass;
											$page_data->totals = new stdClass;
											$page_data->totals->views = 0;
											$page_data->totals->visitors = 0;
											$page_data->totals->sessions = 0;

											$page_data->data = array();

											$report = $decoded_data->reports[0];
											
											$first_row = false;
											$last_row = false;

											foreach( $report->data->rows as $key => $row_data ) {
												$one_entry = new stdClass;

												$one_entry->raw_date = $row_data->dimensions[0];
												$one_entry->unix_date = gmmktime( 0, 0, 0, substr( $one_entry->raw_date, 4, 2 ), substr( $one_entry->raw_date, 6, 2 ), substr( $one_entry->raw_date, 0, 4 ) );
												$one_entry->views = $row_data->metrics[0]->values[0];
												$page_data->totals->views += $one_entry->views;

												$one_entry->visitors = $row_data->metrics[0]->values[1];
												$page_data->totals->visitors += $one_entry->visitors;

												$one_entry->sessions = $row_data->metrics[0]->values[2];
												$page_data->totals->sessions += $one_entry->sessions;

												$one_entry->duration = $row_data->metrics[0]->values[3];

												$page_data->data[ $one_entry->unix_date ] = $one_entry;

												if ( !$first_row ) {
													$first_row = $one_entry;
												}

												$last_row = $one_entry;
											}
										}

										ksort( $page_data->data );

										$prepped_data = new stdClass;
										$prepped_data->labels = array();
										$prepped_data->views = array();
										$prepped_data->visitors = array();

										foreach( $page_data->data as $datetime => $this_data ) {
											$prepped_data->labels[] = date( 'M d', $datetime );
											$prepped_data->views[] = $this_data->views;
											$prepped_data->visitors[] = $this_data->visitors;

											$this->elevate_db->add_or_update_visits( $datetime, $this_data->visitors, $this_data->views );
										}

										$page_data->prepped_data = $prepped_data;

										$page_data->prepped_data->views_dir = $this->elevate_db->_sign( $last_row->views - $first_row->views );
										$page_data->prepped_data->visitors_dir = $this->elevate_db->_sign( $last_row->visitors - $first_row->visitors );

										// Clean up data

										$page_data->totals->views = $this->_fix_big_number( $page_data->totals->views );
										$page_data->totals->visitors = $this->_fix_big_number( $page_data->totals->visitors );

										$analytics_cache->add_to_cache( $page_data );
									
										return $page_data;
									}
								}
							}
						}
					}
				}
			}

			return false;
		} else {
			return $analytics_cache->get_data();
		}

	}

	private function _get_analytics_accounts() {
		$accounts = array();

		require_once( 'search-console-api.php' );
		$search_console = new ElevateSearchConsole();

		$google_tokens = $this->_get_google_tokens();

		$sc_result = $search_console->get_analytics_accounts( $google_tokens->access_token );
		if ( $sc_result ) {
			$decoded_result = json_decode( $sc_result );

			if ( isset( $decoded_result->error ) ) {
				ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Encountered an error with analytics accounts' );

				return false;
			} else {
				foreach( $decoded_result->items as $item ) {
					if ( $item->kind == 'analytics#accountSummary' ) {
						$account_id = $item->id;

						$properties = array();
						foreach( $item->webProperties as $property ) {
							$info = new stdClass;
							$info->name = $property->name;
							$info->id = $property->id;
							$info->url = $property->websiteUrl;
							$info->account_id = $account_id;

							$properties[ $property->websiteUrl ] = $info;
						}

						$accounts[ $account_id ] = $properties;
					}
				}
			}
		}		

		return $accounts;
	}

	private function _strip_and_clean_url( $url ) {
		// remove http/https
		$url = str_replace( array( 'http://', 'https://' ), array( '', '' ), $url );

		return trailingslashit( $url );
	}

	private function _matches_this_site( $url ) {
		$home = $this->_strip_and_clean_url( home_url() );

		return ( $home == $this->_strip_and_clean_url( $url ) );
	}

	public function force_reload_settings() {
		$this->_load_saved_settings();
	}

	private function _load_saved_settings() {
		require_once( 'helpers.php' );

		$saved_settings = get_option( ELEVATE_SETTINGS_KEY );
		if ( is_object( $saved_settings ) ) {
			$this->settings = $saved_settings;

			// Backfill default settings in case there are new ones
			$default_settings = new stdClass;
			$this->init_default_settings( $default_settings );

			$setting_keys = ElevateHelpers::object_to_array( $default_settings );
			foreach( $setting_keys as $key => $value ) {
				if ( !isset( $this->settings->{$key} ) ) {
					// Set the default
					$this->settings->{$key} = $default_settings->{$key};
				} 
			}
		} else {
			// Simply use defaults
			$this->init_default_settings( $this->settings );

			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, '  Using defaults, wizard is ' . $this->settings->wizard_complete );
		}
	}	

	private function _get_clean_url( $url ) {
		return trailingslashit( strtolower( $url ) );
	}

	private function _get_clean_site_url() {
		return $this->_get_clean_url( home_url() );
	}

	private function _create_property_view( $account_id, $property_id ) {
		require_once( 'search-console-api.php' );

		$search_console = new ElevateSearchConsole();
		$google_tokens = $this->_get_google_tokens();	

		$result = $search_console->add_analytics_web_view( $google_tokens->access_token, $account_id, $property_id );	

		return $result;
	}

	private function _revoke_all_tokens() {		
		require_once( 'search-console-api.php' );

		$search_console = new ElevateSearchConsole();
		$google_tokens = $this->_get_google_tokens( false );


		if ( isset( $google_tokens->access_token ) ) {
		//	$search_console->revoke_token( $google_tokens->access_token );
		}

		if ( isset( $google_tokens->refresh_token ) ) {
		//	$search_console->revoke_token( $google_tokens->refresh_token );
		}	

		// Removed the revoke as this often seems to affect all sites authenticated under one account
		// but we'll forget the tokens ourselves, even if Google keeps a copy internally	

		delete_option( ELEVATE_GOOGLE_TOKENS_KEY );		
	}

	private function _clear_dashboard_transients() {
		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Clearing dashboard transients' );

		$url_to_inspect = $this->_get_clean_site_url();
		
		delete_transient( 'elevate_site_has_analytics_' . md5( $url_to_inspect ) );
		delete_transient( 'elevate_site_is_verified_' . md5( $url_to_inspect ) );	
		delete_transient( 'elevate_page_speed_' . md5( $url_to_inspect ) );	
		delete_transient( 'elevate_site_search_' . md5( $url_to_inspect ) );

		ElevateLocalCache::delete_entry( 'analytics_cache' );
	}

	public function handle_admin_ajax() {
		require_once( 'search-console-api.php' );

		$nonce = $this->request_vars[ 'elevate_nonce' ];
		if ( wp_verify_nonce( $nonce, ELEVATE_AJAX_NONCE_NAME ) && current_user_can( 'manage_options' ) ) {

			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Handling AJAX method for [' . $this->request_vars[ 'elevate_action' ] . ']' );

			switch( $this->request_vars[ 'elevate_action' ] ) {
				case 'file_upload':
					$key = $this->request_vars[ 'file_key' ];
					if ( isset( $_FILES[ $key ] ) ) {
						if ( isset( $this->request_vars[ 'acceptable_types' ] ) ) {
							$allowed_types = explode( ",", $this->request_vars[ 'acceptable_types' ] );

							if ( !in_array( $_FILES[ $key ][ 'type' ], $allowed_types ) ) {
								$this->_ajax_failure( 'invalid_mime_type', __( 'Invalid MIME type' ) );
							}
						}
						$upload_dir = $this->_get_elevate_upload_dir();
						$actual_file = $upload_dir . $_FILES[ $key ][ 'name' ];

						rename( $_FILES[ $key ][ 'tmp_name' ], $actual_file );

						chmod( $actual_file, 0644 );

						$result = new stdClass;
						$result->file_name = ltrim( str_replace( $_SERVER[ 'DOCUMENT_ROOT' ], '/', $actual_file ), '/\\' );
						$result->full_file_url = esc_url( trailingslashit( home_url() ) . $result->file_name );
						$result->file_type = 'type';

						$this->_ajax_success( $result );
					}

					$this->_ajax_failure( 'no_file', __( 'No file was uploaded' ) );
					break;
				case 'revoke_all_tokens':
					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'AJAX Delete Token' );

					$this->_revoke_all_tokens();
					$this->_ajax_success();
					break;
				case 'reset_settings':
					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Settings reset' );

					$this->_revoke_all_tokens();
					delete_option( ELEVATE_SETTINGS_KEY );
					$this->_ajax_success();
					break;
				case 'setup_google_token':
					if ( $this->_convert_oauth_code_to_token( $this->request_vars[ 'code' ] ) ) {
						$this->_ajax_success();
					} else {
						$this->_ajax_failure( 'token_grant_failure', __( 'Unable to acquire Google token', 'elevate-seo' ) );	
					}

					$this->_ajax_failure( 'token_grant_failure', __( 'Unable to acquire Google token', 'elevate-seo' ) );
					
					break;
				case 'check_all_services':
					$result = new stdClass;

					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, "\tChecking for Google Search\t" );

					$result->on_search_console = $this->_is_on_google_search() ? 1 : 0;

					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, "\t\tResult is " . $result->on_search_console );
					$result->has_been_verified = $result->on_search_console ? ( $this->_is_site_verified() ? 1 : 0 ) : 0;
					$result->has_sitemap = $this->_is_on_google_search() ? ( $this->_has_configured_sitemap() ? 1 : 0 ) : 0;

					$result->is_using_analytics = 0;
					$result->items = array();

					$accounts = $this->_get_analytics_accounts();

					$result->has_google_analytics = 0;

					$has_property = false;
					if ( $accounts ) {
						$result->has_google_analytics = 1;

						ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, "HAS ANALYTICS ACCOUNT!" );

						foreach( $accounts as $id => $properties ) {
							foreach( $properties as $property ) {
								if ( $property->url ) {
									if ( $this->_matches_this_site( $property->url ) ) {
										$has_property = true;
										break;
									}
								}
							}
						}					
					}

					$result->has_analytics_prop = ( $has_property ? 1 : 0 );

					$this->_ajax_success( $result );
					break;
				case 'google_check_sites':
					$google_tokens = $this->_get_google_tokens();

					if ( !$google_tokens ) {
						$this->_ajax_failure( 'google_not_authenticated', __( 'No authentication for Google Search Console', 'elevate-seo' ) );
					}

					$search_console = new ElevateSearchConsole();
					$result = $search_console->get_sites( $google_tokens->access_token );

					if ( $result ) {
						$decoded_result = json_decode( $result );
						$site_found = false;
						$home_url = $this->_get_clean_site_url();
						if ( isset( $decoded_result->siteEntry ) ) {
							foreach( $decoded_result->siteEntry as $result ) {
								// Check to see if this site matches
								if ( trailingslashit( $result->siteUrl ) == $home_url ) {
									$site_found = true;
									break;
								}
							}							
						}

						if ( $site_found ) {
							$this->_ajax_success();	
						} else {
							$this->_ajax_failure( 'no_site_configured', __( "Site isn't configured on Search Console", "elevate-seo" ) );
						}
					} else {
						$this->_ajax_failure( 'cant_locate_sites', __( 'Unable to locate sites', 'elevate-seo' ) );	
					}

					break;
				case 'google_add_site':	
					$google_tokens = $this->_get_google_tokens();

					if ( !$google_tokens ) {
						$this->_ajax_failure( 'google_not_authenticated', __( 'No authentication for Google Search Console', 'elevate-seo' ) );
					}

					$search_console = new ElevateSearchConsole();
					$result = $search_console->add_site( $google_tokens->access_token, $this->_get_clean_site_url() );

					$this->_ajax_success( $result );	
					break;
				case 'generate_sitemap':	
					$this->_generate_sitemap();

					$this->_ajax_success();
					break;	
				case 'check_site_verification':
					$google_tokens = $this->_get_google_tokens();

					if ( !$google_tokens ) {
						$this->_ajax_failure( 'google_not_authenticated', __( 'No authentication for Google Search Console', 'elevate-seo' ) );
					}

					$search_console = new ElevateSearchConsole();
					$result = $search_console->check_site_verification( $google_tokens->access_token, $this->_get_clean_site_url() );

					$decoded_result = json_decode( $result );
					if ( isset( $decoded_result->error->code ) && $decoded_result->error_code = 403 ) {
						// We aren't the site owner
						$this->_ajax_failure( 'not_site_owner', __( "This site hasn't been verified yet", 'elevate-seo' ) );
					} else if ( isset( $decoded_result->owners ) ) {
						$this->_ajax_success( $result );
					}

					$this->_ajax_failure( 'error_verifying_site', __( "Unexpected response verifying site", 'elevate-seo' ) );
					
					break;
				case 'verify_site':
					$google_tokens = $this->_get_google_tokens();

					if ( !$google_tokens ) {
						$this->_ajax_failure( 'google_not_authenticated', __( 'No authentication for Google Search Console', 'elevate-seo' ) );
					}

					$search_console = new ElevateSearchConsole();
					$result = $search_console->get_site_verification_token( $google_tokens->access_token, $this->_get_clean_site_url() );

					if ( $result ) {
						$decoded_result = json_decode( $result );
						if ( isset( $decoded_result->token ) ) {
							// We know have the authentication token from Google, so we have to create a file in the root directory for it
							$root_dir = $_SERVER[ 'DOCUMENT_ROOT' ];
							$token_file = trailingslashit( $root_dir ) . $decoded_result->token;
							$temp_file = fopen( $token_file, 'w+t' );
							if ( $temp_file ) {
								fwrite( $temp_file, 'google-site-verification: ' . $decoded_result->token );
								fclose( $temp_file );

								// Verification file now exists, let's tell Google about it and hope they verify the site
								$result = $search_console->verify_site( $google_tokens->access_token, $this->_get_clean_site_url() );

								$decoded_result = json_decode( $result );
								if ( isset( $decoded_result->error ) ) {
									$this->_ajax_failure( 'verification_failure', __( 'Site verification failed', 'elevate-seo' ) );
								}

								$this->_ajax_success( $result );
							} 
						}					
					}
				
					$this->_ajax_failure( 'verification_failure', __( 'Site verification failed', 'elevate-seo' ) );
					break;
				case 'check_sitemap':
					$google_tokens = $this->_get_google_tokens();

					if ( !$google_tokens ) {
						$this->_ajax_failure( 'google_not_authenticated', __( 'No authentication for Google Search Console', 'elevate-seo' ) );
					}

					$search_console = new ElevateSearchConsole();
					$result = $search_console->get_sitemaps( $google_tokens->access_token, trailingslashit( home_url() ) );

					if ( $result ) {
						$decoded_result = json_decode( $result );
						if ( isset( $decoded_result->sitemap ) ) {
							$desired_path = trailingslashit( home_url() ) . 'sitemap.xml';

							foreach( $decoded_result->sitemap as $sitemap ) {
								if ( $desired_path == $sitemap->path ) {
									// This entry already exists
									$this->_ajax_success();
								}
							}
						}
					}

					$this->_ajax_failure( 'no_sitemap_configured', __( 'Sitemap has not been configured', 'elevate-seo' ) );
					break;
				case 'add_sitemap':
					$google_tokens = $this->_get_google_tokens();

					if ( !$google_tokens ) {
						$this->_ajax_failure( 'google_not_authenticated', __( 'No authentication for Google Search Console', 'elevate-seo' ) );
					}

					$search_console = new ElevateSearchConsole();
					$result = $search_console->add_sitemap( $google_tokens->access_token, trailingslashit( home_url() ), trailingslashit( home_url() ) . 'sitemap.xml' );

					if ( !$result ) {
						$this->_ajax_success( $result );
					}

					$this->_ajax_failure( 'cant_add_sitemap', __( 'Unable to add the sitemap', 'elevate-seo' ) );
					break;	
				case 'create_analytics_property':
					$google_tokens = $this->_get_google_tokens();

					if ( !$google_tokens ) {
						$this->_ajax_failure( 'google_not_authenticated', __( 'No authentication for Google Search Console', 'elevate-seo' ) );
					}

					$search_console = new ElevateSearchConsole();
					$accounts = $search_console->get_analytics_accounts( $google_tokens->access_token );
					$decoded_accounts = json_decode( $accounts );

					$property = $this->_has_analytics_property( $decoded_accounts );
					if ( !$property ) {
						ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Creating new analytics web property - accounts are [' . print_r( $accounts, true ) . ']' );
	
						if ( $decoded_accounts ) {
							ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Decoded account' );
							// Make sure we have at least one account
							if ( isset( $decoded_accounts->totalResults ) && $decoded_accounts->totalResults >= 1 ) {
								// Get the first account
								ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Has at least one account' );
								$account_number = $decoded_accounts->items[1]->id;

								ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Account ID is ' . $account_number  );

								$result = $search_console->add_analytics_web_property( $google_tokens->access_token, $account_number, home_url(), $this->settings->home_title );

								ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Created property ' . print_r( $result, true )  );
							}
						}
					} else {
						// Check to see if a view exists
						ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Property already exists, seeing if we need to create a View'  );
						if ( !isset( $property->profiles ) ) {
							ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Property does not have a view - creating one'  );

							$this->_create_property_view( $property->account_id, $property->id );
						}
					}

					$this->_ajax_success( $result );					
					break;
				case 'add_analytics_code':
					$google_tokens = $this->_get_google_tokens();

					if ( !$google_tokens ) {
						$this->_ajax_failure( 'google_not_authenticated', __( 'No authentication for Google Search Console', 'elevate-seo' ) );
					}

					$result = new stdClass;
					$result->found = 0;
					$result->id = 0;

					$accounts = $this->_get_analytics_accounts();

					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Trying to add analytics code, here are the accounts: ' . print_r( $accounts, true ) );
					foreach( $accounts as $id => $sites ) {
						foreach( $sites as $url => $info ) {
							if ( $this->_get_clean_url( $url ) == $this->_get_clean_site_url() ) {
								ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Found the site [' . $url . ']' );

								$result->found = 1;
								$result->id = $info->id;

								$this->settings->insert_analytics = 1;

								$analytics_code = file_get_contents( ELEVATE_PLUGIN_DIR . '/admin/services/analytics.txt' );
								$this->settings->analytics_code = str_replace( '#analytics_id#', $info->id, $analytics_code );

								$this->actual_save_settings();

								break;
							}
						}
					}

					$this->_ajax_success( $result );				
					break;
				case 'subscribe_mailing_list':
					require_once( 'api.php' );

					$email = $this->request_vars[ 'email' ];

					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Getting ready to add email ' . $email );

					$api = new ElevateAPI;
					$result = $api->subscribe_to_mailing_list( $email );

					$this->_ajax_success( $result );
					break;
				case 'get_updated_title':
					query_posts( 'p=' . $this->request_vars[ 'content_id' ] );
					the_post();

					$content = new stdClass;
					$content->new_title = $this->get_placeholder_title( $this->request_vars[ 'content_title' ], is_single() );
					$content->new_desc = $this->_get_intelligent_meta_desc_from_content( $this->request_vars[ 'content_desc' ] );

					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Placeholder title is ' . $this->request_vars[ 'content_title' ] );

					$this->_ajax_success( $content );
					break;	
				case 'wizard_save_state':
					if ( isset( $this->request_vars[ 'settings' ] ) ) {
						$settings_changed = false;
						foreach( $this->request_vars[ 'settings' ] as $key => $value ) {
							$decoded_value = urldecode( $value );

							if ( isset( $this->settings->$key ) ) {
								$this->settings->$key = $value;
								$settings_changed = true;
							}
							ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Updating wizard setting [' . $key . '] => [' . $decoded_value . ']' );
						}

						if ( $settings_changed ) {
							$this->actual_save_settings();
						}
					}
					$this->_ajax_success();
					break;		
				case 'wizard_done':
					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Setting wizard to DONE' );

					$this->settings->wizard_complete = 1;
					$this->actual_save_settings();

					$this->_clear_dashboard_transients();

					$this->_ajax_success();
					break;	
				case 'show_wizard':
					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Setting wizard to SHOW' );

					$this->settings->wizard_complete = 0;
					$this->actual_save_settings();

					$this->_ajax_success();
					break;
				case 'get_dashboard_data':
					$result = new stdClass;

					$url_to_inspect = $this->_get_clean_site_url();

					$search = $this->_update_search_and_analytics_data( $url_to_inspect );

					$result->crawl_errors = $search->crawl_errors;
					$result->search_analytics = $search->analytics;

					/* These methods are not cached internally, so cache them here */
					if ( false === ( $analytics_result = get_transient( 'elevate_site_has_analytics_' . md5( $url_to_inspect ) ) ) ) {	
						$analytics_result = $this->_check_installed_analytics( $url_to_inspect );

						ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Has analytics installed? ' . $analytics_result );

						set_transient( 'elevate_site_has_analytics_' . md5( $url_to_inspect ), $analytics_result, ELEVATE_TRANSIENT_SITE_INFO );
					}

					$result->has_analytics_installed = ( $analytics_result ? 1 : 0 );

					if ( false === ( $verification_result = get_transient( 'elevate_site_is_verified_' . md5( $url_to_inspect ) ) ) ) {	
						$verification_result = $this->_is_site_verified( $url_to_inspect );

						set_transient( 'elevate_site_is_verified_' . md5( $url_to_inspect ), $verification_result, ELEVATE_TRANSIENT_SITE_INFO );
					}

					$result->is_site_verified = ( $verification_result ? 1 : 0 );
					/* End Caching */

					$result->sitemap_info = $this->_check_sitemap_status();

					$this->_ajax_success( $result );
					break;
				case 'get_dashboard_data_speed':
					$result = new stdClass;

					$url_to_inspect = $this->_get_clean_site_url();

					/* These methods are all cached internally */
					$speed_result = $this->get_page_speed( $url_to_inspect );

					$result->desktop = $speed_result->desktop;
					$result->mobile = $speed_result->mobile;

					$this->_ajax_success( $result );
					break;	
				case 'get_dashboard_pagespeed_data':
					$result = $this->elevate_db->get_pagespeed_data( 0, 8 );

					$this->_ajax_success( $result );
					break;
				case 'get_dashboard_search_data':
					$result = $this->elevate_db->get_search_data( 0, 8 );

					$this->_ajax_success( $result );
					break;
				case 'get_dashboard_404_data':
					$result = $this->elevate_db->get_search_404_data( 0, 8 );

					$this->_ajax_success( $result );
					break;		
				case 'get_dashboard_data_analytics':
					$result = $this->_get_analytics_page_data();

					$this->_ajax_success( $result );
					break;
				case 'fix_htaccess':
					require_once( dirname( __FILE__ ) . '/apache.php' );
					$content = ElevateApache::instance()->fix();

					$this->_ajax_success( $content );
					break;				
				default:
					$this->_ajax_failure( 'unknown_ajax_failure', __( 'Unknown AJAX method', 'elevate-seo' ) );
					break;
			}
		} else {
			$this->_ajax_failure( __( 'invalid_nonce', 'The supplied NONCE is invalid', 'elevate-seo' ) );
		}
	}

	private function _fix_big_number( $number ) {
		if ( $number > 1000000 ) {
			return sprintf( __( '%0.2fM', 'elevate-seo' ), $number/1000000.0 );
		} else if ( $number > 1000 ) {
			return sprintf( __( '%0.2fk', 'elevate-seo' ), $number/1000.0 );
		} else return $number;
	}

	public function _check_sitemap_status() {
		$sitemap_info = new stdClass;
		$sitemap_info->has_sitemap = 0;
		$sitemap_info->modified_time = 0;
		$sitemap_info->entries = 0;
		$sitemap_info->is_generating = file_exists( $this->_get_sitemap_dir() . '/generating.txt' );

		if ( $this->settings->sitemap_name ) {
			$sitemap_file = $this->_get_sitemap_dir() . '/' . $this->settings->sitemap_name;

			if ( file_exists( $sitemap_file ) ) {
				$sitemap_info->has_sitemap = 1;
				$sitemap_info->modified_time = date( 'm/d/Y g:ia', filemtime( $sitemap_file ) );

				if ( file_exists( $this->_get_sitemap_dir() . '/info.txt' ) ) {
					$sitemap_info->entries = file_get_contents( $this->_get_sitemap_dir() . '/info.txt' );
				}
			}
		}

		return $sitemap_info;
	}

	public function get_search_analytics( $url ) {
		$google_tokens = $this->_get_google_tokens();

		$analytics_result = new stdClass;

		$analytics_result->clicks = 0;
		$analytics_result->impressions = 0;
		$analytics_result->ctr = 0;
		$analytics_result->position = 0;	
		$analytics_result->valid = false;	

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Looking for analytics data' );

		if ( $google_tokens ) {
			$search_console = new ElevateSearchConsole();

			$result = $search_console->get_search_analytics( $google_tokens->access_token, $url );

			if ( $result ) {
				$decoded_result = json_decode( $result );

				if ( !isset( $decoded_result->error ) ) {
					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Search analytics results [' . print_r( $decoded_result, true ) . ']' );	

					$analytics_result = $decoded_result->rows[0];

					$analytics_result->clicks_raw = $analytics_result->clicks;
					$analytics_result->impressions_raw = $analytics_result->impressions;
					$analytics_result->clicks = $this->_fix_big_number( $analytics_result->clicks );
					$analytics_result->impressions = $this->_fix_big_number( $analytics_result->impressions );
					$analytics_result->ctr = sprintf( '%0.1f%%', $analytics_result->ctr*100 ); 
					$analytics_result->position = sprintf( '%0.1f', $analytics_result->position ); 	
					$analytics_result->valid = true;
				}
			}

			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Search results [' . print_r( $analytics_result, true ) . ']' );
		}

		return $analytics_result;
	}

	private function _check_installed_analytics( $url ) {
		$result = wp_remote_get( $url );
		if ( is_array( $result ) ) {
			$content = $result[ 'body' ];

			return ( strpos( $content, '/analytics.js' ) !== false ) || ( strpos( $content, 'gtag/js?' ) !== false );
		}	

		// not installed
		return false;	
	}

	public function get_crawl_errors( $url ) {
		$google_tokens = $this->_get_google_tokens();

		$total_errors = new stdClass;

		$total_errors->count = 0;
		$total_errors->not_found = 0;
		$total_errors->not_followed = 0;
		$total_errors->permissions = 0;
		$total_errors->server_error = 0;	
		$total_errors->roboted = 0;
		$total_errors->valid = false;	

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Looking for crawl errors' );	

		if ( $google_tokens ) {
			require_once( dirname( __FILE__ ) . '/search-console-api.php' );
			$search_console = new ElevateSearchConsole();
		
			$search_errors = $search_console->get_crawl_errors( $google_tokens->access_token, $url );		

			if ( $search_errors ) {
				$decoded_errors = json_decode( $search_errors );

				ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Crawl errors result [' . print_r( $decoded_errors, true ) . ']' );	

				foreach( $decoded_errors->countPerTypes as $key => $value ) {
					if ( isset( $value->entries ) ) {
						$total_errors->count += $value->entries[0]->count;

						switch ( $value->category ) {
							case 'notFound':
								$total_errors->not_found += $value->entries[0]->count;
								break;
							case 'authPermissions':
								$total_errors->permissions += $value->entries[0]->count;
								break;
							case 'serverError':
								$total_errors->server_error += $value->entries[0]->count;
								break;
							case 'notFollowed':
								$total_errors->not_followed += $value->entries[0]->count;
								break;
							case 'roboted':
								$total_errors->roboted += $value->entries[0]->count;;
								break;
						}
					}
				}
			}	

			$total_errors->valid = true;
			$total_errors->not_found = $total_errors->not_found ;
			$total_errors->permissions = $total_errors->permissions ;
			$total_errors->server_error = $total_errors->server_error;
	}	

		return $total_errors;
	}

	public function get_page_speed( $url ) {
		require_once( 'page-speed.php' );

		$key = 'elevate_page_speed_' . md5( $url );		
		$cached_result = get_transient( $key );

		if ( defined( 'ELEVATE_NO_CACHE' ) || false === $cached_result ) {	
			$page_speed = new ElevatePageSpeed;

			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Performing page speed' );

			$cached_result = $page_speed->check_page( $url );

			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Result ' . print_r( $cached_result, true ) );

			set_transient( $key, $cached_result, ELEVATE_TRANSIENT_SITE_INFO );

			// Update our page speed metric
			require_once( dirname( __FILE__ ) . '/elevate-db.php' );

			$db = new ElevateDB;
			$db->add_pagespeed( $url, $cached_result->desktop, $cached_result->mobile );
		}	

		// Fix large numbers
		$cached_result->desktop->response_bytes = $this->_fix_big_number( $cached_result->desktop->response_bytes );
		$cached_result->mobile->response_bytes = $this->_fix_big_number( $cached_result->mobile->response_bytes );
		$cached_result->desktop->js_bytes = $this->_fix_big_number( $cached_result->desktop->js_bytes );
		$cached_result->mobile->js_bytes = $this->_fix_big_number( $cached_result->mobile->js_bytes );
		$cached_result->desktop->css_bytes = $this->_fix_big_number( $cached_result->desktop->css_bytes );
		$cached_result->mobile->css_bytes = $this->_fix_big_number( $cached_result->mobile->css_bytes );

		return $cached_result;
	}

	public function get_placeholder_title( $title, $is_post ) {
		require_once( 'title-modifier.php' );

		$new_title = false;

		if ( $is_post ) {
			$new_title = ElevateTitleModifier::apply_title_template( $title, $this->settings->post_template );
		} else {
			$new_title = ElevateTitleModifier::apply_title_template( $title, $this->settings->page_template );
		} 	

		return $new_title;
	}

	public function is_elevate_page() {
		return ( strpos( $_SERVER[ 'REQUEST_URI' ], '?page=elevate_' ) !== false );
	}

	public function is_elevate_dashboard() {
		return ( strpos( $_SERVER[ 'REQUEST_URI' ], '?page=elevate_dashboard' ) !== false );
		
	}

	private function _is_gutenberg_installed() {
		return function_exists( 'the_gutenberg_project' );
	}

	public function get_admin_color( $index ) {
		global $_wp_admin_css_colors;
		$colors = $_wp_admin_css_colors[ get_user_option( 'admin_color' ) ]->colors;
		return $colors[ $index ];
	}

	public function get_taxonomy_description() {
		if ( $this->is_editing_taxonomy() ) {
			$term = get_term( $this->request_vars[ 'tag_ID' ] );

			return $term->description;
		}
	}

	public function is_editing_taxonomy() {
		return $this->_post_contains( 'term.php?taxonomy=' );
	}

	private function _get_elevate_data() {
		$elevate_data = array(
			'home_url' => trailingslashit( home_url() ),
			'elevate_nonce' => wp_create_nonce( ELEVATE_AJAX_NONCE_NAME ),
			'admin_ajax' => admin_url( 'admin-ajax.php' ),
			'ajax_success' => ELEVATE_AJAX_SUCCESS,
			'ajax_failure' => ELEVATE_AJAX_FAILURE,
			'msg_invalid_mime' => __( 'Please only submit JPEG or PNG image files.', 'elevate-seo' ),
			'msg_setup_cant_add_sitemap' => __( 'We were unable to automatically add a sitemap on Google Search Console; you will likely have to complete this step manually.', 'elevate-seo' ),
			'settings_general_page' => admin_url( 'admin.php?page=elevate_dashboard' ),
			'settings_wizard_page' => admin_url( 'admin.php?page=elevate_plugin' ),
			'gutenberg_installed' => $this->_is_gutenberg_installed() ? '1' : '0',
			'has_google_tokens' => $this->has_google_tokens() ? '1' : '0',
			'oauth_auth_url' => ElevateAPI::get_oauth_auth_url( false, admin_url( 'admin.php?page=elevate_search' ) ),
			'default_image_url' => json_encode( trailingslashit( home_url() ) . $this->settings->facebook_default_image ),
			'default_image' => json_encode( $this->settings->facebook_default_image ),
			'thumbnail_behaviour' => json_encode( $this->settings->thumbnail_behaviour ),
			'first_post_image' => '0',
			'is_new_page' => '1',
			'is_editing_term' => $this->is_editing_taxonomy() ? '1' : '0',
			'intelligent_desc' => $this->settings->fill_empty_description ? '1' : '0',
			'wp_locale' => get_locale(),
			'guten_not_enough' => __( 'Not enough content to analyze - continue writing.', 'elevate-seo' ),
			'guten_all_good' => __( 'Your content looks good from a search and social media perspective.', 'elevate-seo' ),
			'guten_a_few_adjustments' => __( 'You may want to make a few search adjustments before publishing or saving.', 'elevate-seo' ),
			'guten_serious' => __( 'You have a few serious search issues that need addressing.', 'elevate-seo' ),
			'guten_web_preview' => __( 'Web Preview', 'elevate-seo' ),
			'guten_learn_more' => __( 'Learn More', 'elevate-seo' ),
			'wizard_fix' => __( 'Fix Issues', 'elevate-seo' ),
			'wizard_try_fix' => __( 'Attemping to Fix', 'elevate-seo' ),
			'text_mobile_speed' => __( 'Mobile Speed', 'elevate-seo' ),
			'text_desktop_speed' => __( 'Desktop Speed', 'elevate-seo' ),
			'text_impressions' => __( 'Impressions', 'elevate-seo' ),
			'text_clicks' => __( 'Clicks', 'elevate-seo' ),
			'text_fof' => __( '404 Errors', 'elevate-seo' ),
			'text_page_views' => __( 'Page Views', 'elevate-seo' ),
			'text_visitors' => __( 'Visitors' , 'elevate-seo' )
		);

		if ( isset( $_GET[ 'elevate_continue_wizard' ] ) && $this->has_google_tokens( false ) ) {
			$elevate_data[ 'post_oauth_skip' ] = 1;
		}

		// Show whether or not we are editing a post
		if ( isset( $_GET[ 'action'] ) && ( $_GET[ 'action' ] == 'edit' ) ) {
			$elevate_data[ 'is_new_page' ] = 0;
		}

		$post_id = get_the_ID();
		if ( $post_id ) {
			$elevate_data[ 'post_id' ] = $post_id;

			$image = $this->get_first_image_in_post( $post_id );

			if ( $image ) {
				$elevate_data[ 'first_post_image' ] = json_encode( $image );	
			}
		}

		return $elevate_data;
	}

	private function _post_contains( $substr ) {
		return ( strpos( $_SERVER[ 'REQUEST_URI' ], $substr ) !== false );
	}
 
	public function get_draft_permalink( $post ) {
		if ( $this->is_editing_taxonomy() ) {
			$term = get_term( $this->request_vars[ 'tag_ID' ] );

			return get_term_link( $term );
		};

		if ( in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) ) ) {
	        $this_post = clone $post;
	        $this_post->post_status = 'published';
	        $this_post->post_name = sanitize_title( $this_post->post_name ? $this_post->post_name : $this_post->post_title, $this_post->ID );
	        $this_post->filter = 'sample';

	        $permalink = get_the_permalink( $this_post );
	    } else {

	        $permalink = get_the_permalink();
	    }

	    return $permalink;
	}

	public function get_locale_listing() {
		require_once( dirname( __FILE__ ) . '/locales.php' );

		return elevate_get_locale_list();
	}

	public function handle_admin_enqueue_scripts( $hook ) {
		if ( $this->is_elevate_page() ) {
			// Styles
			wp_enqueue_style( 'elevate-admin-css', ELEVATE_PLUGIN_URL . '/dist/css/admin.css', array(), ELEVATE_CACHE_VERSION );
			wp_enqueue_script( 'elevate-custom', ELEVATE_PLUGIN_URL . '/dist/js/bundle.min.js', array( 'jquery' ), ELEVATE_CACHE_VERSION, true );	

			$language_params = $this->_get_elevate_data();

			// Check OAUTH nonce and set up JS for OAUTH completion
			if ( isset( $this->request_vars[ 'elevate_state'] ) ) {
				$oauth_nonce = $this->request_vars[ 'elevate_state' ];
				if ( ElevateAPI::is_api_nonce_valid( $oauth_nonce ) && isset( $this->request_vars[ 'elevate_oauth'] ) ) {
					$language_params[ 'oauth_complete' ] = 1;
					$language_params[ 'oauth_code' ] = $this->request_vars[ 'code' ];
				}			
			}

			if ( $this->has_google_tokens() ) {
				$language_params[ 'has_tokens' ] = 1;
			}

			wp_localize_script( 'elevate-custom', 'ElevateData', $language_params );			
		} else if ( $this->_post_contains( '?post=') || $this->_post_contains( 'post-new.php' ) || $this->is_editing_taxonomy() ) {
			$depends = array( 'jquery' );

			if ( $this->_is_gutenberg_installed() ) {
				$depends = array_merge( $depends, array( 'wp-blocks', 'wp-element', 'jquery', 'wp-edit-post' ) );
			}

			wp_enqueue_style( 'elevate-meta-css', ELEVATE_PLUGIN_URL . '/dist/css/meta.css', false, ELEVATE_CACHE_VERSION );
			wp_enqueue_script( 'elevate-custom', ELEVATE_PLUGIN_URL . '/dist/js/bundle.min.js', $depends, ELEVATE_CACHE_VERSION, true );

			wp_localize_script( 'elevate-custom', 'ElevateData', $this->_get_elevate_data() );
		}	
	}

	private function _is_woo_commerce_shop_page() {
		return function_exists( 'is_shop' ) && is_shop();
	}

	private function _get_internal_title() {
		require_once( 'title-modifier.php' );

		if ( $this->_is_woo_commerce_shop_page() ) {
			$shop_page_id = get_option( 'woocommerce_shop_page_id' );;

			$meta_info = $this->get_saved_meta_box_info( $shop_page_id );

			if ( $meta_info->title ) {
				return $meta_info->title;
			} else {
				return ElevateTitleModifier::apply_title_template( false, $this->settings->store_template );
			}
		} else if ( is_front_page() ) {
			return $this->settings->home_title;
		} else if ( is_home() ) {
			if ( $this->_has_separate_blog_page() ) { 
				return $this->settings->blog_title;
			} else {
				return $this->settings->home_title;
			}
		} else if ( is_singular() ) {	
			if ( have_posts() ) {
				the_post();

				if ( is_singular() ) {
					$title = get_the_title();

					$meta_info = $this->get_saved_meta_box_info( get_the_ID() );

					if ( $meta_info->title ) {
						rewind_posts();
						return $meta_info->title;
					}
				}

				rewind_posts();
			}

			if ( is_single() ) {
				return ElevateTitleModifier::apply_title_template( $title, $this->settings->post_template );
			} else if ( is_page() ) {
				return ElevateTitleModifier::apply_title_template( $title, $this->settings->page_template );
			}
		} else if ( is_category() || is_tag() || is_tax() ) {
			$category = get_queried_object();

			$meta_info = $this->get_saved_meta_box_info( $category->term_id, true );

			if ( $meta_info->title ) {
				return $meta_info->title;
			}

			return ElevateTitleModifier::apply_title_template( $title, $this->settings->taxonomy_template );
		} else if ( is_attachment() ) {
			return ElevateTitleModifier::apply_title_template( $title, $this->settings->media_template );
		} else if ( is_author() ) {
			return ElevateTitleModifier::apply_title_template( $title, $this->settings->author_template );
		} else if ( is_404() ) {
			return ElevateTitleModifier::apply_title_template( false, $this->settings->fof_template );
		} else if ( is_search() ) {
			return ElevateTitleModifier::apply_title_template( false, $this->settings->search_template );
		} else {
			return ElevateTitleModifier::apply_title_template( $title, $this->settings->post_template );
		}		
	}

	public function handle_title( $title ) {
		if ( is_admin() ) {
			return $title;
		} else {
			return $this->_get_internal_title();	
		}	
	}

	function init_default_settings( &$settings ) {
		// General Settings
		$settings->selected_locale = 'auto';

		$settings->enable_advanced_settings = 0;

		$settings->site_type = 'personal';

		$user_info = get_userdata( 1 );
		if ( $user_info ) {
			if ( $user_info->display_name ) {
				$settings->site_owner = $user_info->display_name;		
			} else if ( $user_info->first_name && $user_info->last_name ) {
				$settings->site_owner = $user_info->first_name . ' ' . $user_info->last_name;	
			}
			
		} else {
			$settings->site_owner = '';
		}

		// Sitemap
		$settings->enable_sitemap_generation = 1;
		$settings->sitemap_name = 'sitemap.xml';
		$settings->sitemap_entries = 0;
		$settings->sitemap_is_generating = 0;
		$settings->show_google_preview = 1;
		$settings->robots_txt = 'enhanced';
		$settings->cache_version = time();

		// Sitemap, what to include
		$settings->sitemap_posts = 1;
		$settings->sitemap_custom_posts = 1;
		$settings->sitemap_pages = 1;
		$settings->sitemap_media = 1;
		$settings->sitemap_authors = 1;
		$settings->sitemap_categories = 1;
		$settings->sitemap_tags = 1;
		$settings->sitemap_taxonomy = 1;
		$settings->sitemap_images = 1;

		// Indexing
		$settings->index_default = 'index,follow';
		$settings->index_front_page = 'inherit';
		$settings->index_posts = 'inherit';
		$settings->index_pages = 'inherit';
		$settings->index_authors = 'inherit';
		$settings->index_media = 'inherit';
		$settings->index_categories = 'inherit';
		$settings->index_tags = 'inherit';
		$settings->index_taxonomies = 'inherit';
		$settings->index_archives = 'inherit';
		$settings->index_fourohfour = 'noindex,follow';
		$settings->index_search = 'noindex,follow';

		// Titles
		$settings->title_separator = '—';
		$settings->site_name = get_bloginfo( 'title' );
		$settings->home_title = sprintf( '%s %s %s', get_bloginfo( 'title' ), $settings->title_separator, get_bloginfo( 'description' ) );
		$settings->home_desc = get_bloginfo( 'description' );

		$settings->blog_title = sprintf( __( 'Latest articles %s %s', 'elevate-seo' ), $settings->title_separator, $settings->site_name );
		$settings->blog_desc = sprintf( __( 'Read some of the latest articles from our site.', 'elevate-seo' ), $settings->home_title );
		
		$settings->home_template = '#site_name# #separator# #home_desc#';
		$settings->blog_template = '#blog_desc# #separator# #site_name#';
		$settings->post_template = '#post_name# #separator# #site_name#';
		$settings->page_template = '#page_name# #separator# #site_name#';
		$settings->archive_template = '#archive_name# #separator# #site_name#';
		$settings->media_template = '#post_name#  #separator# #site_name#';
		$settings->author_template = '#author_name# #separator# #site_name#';
		$settings->taxonomy_template = '#taxonomy_name# #separator# #site_name#';
		$settings->fof_template = __( 'Page not found #separator# #site_name#', 'elevate-seo' );
		$settings->store_template = __( 'Store #separator# #site_name#', 'elevate-seo' );
		$settings->search_template = __( 'Search results for \'#search_query#\' #separator# #site_name#', 'elevate-seo' );

		// Social Media
		$settings->social_twitter_name = '';
		$settings->social_facebook_name = '';
		$settings->social_instagram_name = '';
		$settings->social_pinterest_name = '';
		$settings->social_linkedin_name = '';
		$settings->social_googleplus_name = '';

		$settings->enable_facebook_og = 1;
		$settings->facebook_app_id = '';
		$settings->facebook_default_image = '';

		$settings->enable_twitter_cards = 1;
		$settings->twitter_card_image_size = 'summary';

		$settings->include_article_info = 1;

		// Redirects
		$settings->ssl_redirect = 0;
		$settings->redirect_media = 1;

		// Google
		$settings->insert_analytics = 0;
		$settings->analytics_code = '';
		$settings->fill_empty_description = 1;
		
		$settings->thumbnail_behaviour = 'include_content';
		$settings->bing_auth_code = '';

		// Performance
		$settings->use_cdn = 0;
		$settings->cdn_url = '';

		// Meta information
		$settings->insert_canonical = 1;
		$settings->insert_language = 0;
		$settings->insert_robots = 0;

		// Wizard complete
		$settings->wizard_complete = 0;

		// Import behaviour
		$settings->import_behaviour = 'none';
		
		// Debugging
		$settings->enable_debug_log = 0;

		// Breadcrumbs 
		$settings->enable_breadcrumbs = 1;
		$settings->breadcrumb_home = __( 'Home', 'elevate-seo' );
	}

	private function _create_directory_if_needed( $dir ) {
		if ( !file_exists( $dir ) ) {
			mkdir( $dir, 0755 );
		}		
	}

	function _get_elevate_upload_dir() {
		$upload_dir = wp_upload_dir();

		$dir = trailingslashit( $upload_dir[ 'basedir' ] ) . 'elevate/';
		$this->_create_directory_if_needed( $dir );

		return $dir;
	}

	public function get_sitemap_url() {
		return home_url() . '/' . $this->settings->sitemap_name;
	}

	public function _get_sitemap_dir() {
		$upload_dir = wp_upload_dir();

		$sitemap_dir = trailingslashit( $upload_dir[ 'basedir' ] ) . 'elevate-sitemap/';
		$this->_create_directory_if_needed( $sitemap_dir );

		return $sitemap_dir;
	}

	function _get_sitemap_params() {
		$params = array();
		if ( $this->settings->sitemap_posts ) {
			$params[] = 'posts';
		}

		if ( $this->settings->sitemap_custom_posts ) {
			$params[] = 'custom-posts';
		}

		if ( $this->settings->sitemap_pages ) {
			$params[] = 'pages';
		}	

		if ( $this->settings->sitemap_media ) {
			$params[] = 'media';
		}		

		if ( $this->settings->sitemap_authors ) {
			$params[] = 'authors';
		}	

		if ( $this->settings->sitemap_categories ) {
			$params[] = 'categories';
		}	

		if ( $this->settings->sitemap_tags ) {
			$params[] = 'tags';
		}

		if ( $this->settings->sitemap_taxonomy ) {
			$params[] = 'taxonomies';
		}	

		if ( $this->settings->sitemap_images ) {
			$params[] = 'images';
		}				

		return $params;										
	}

	function _generate_sitemap( $force = true ) {
		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Calling generate' );

		if ( file_exists( $this->_get_sitemap_dir() . '/generating.txt' ) ) {
			unlink( $this->_get_sitemap_dir() . '/generating.txt' );	
		}
		
		if ( file_exists( $this->_get_sitemap_dir() . '/info.txt' ) ) {
			unlink( $this->_get_sitemap_dir() . '/info.txt' );	
		}
		
		do_action( 'generate_sitemap', $force ? 1 : 0 );
		
		return true;
	}

	function _get_current_page_without_actions() {
		$current_page = $_SERVER[ 'REQUEST_URI' ];
		return add_query_arg( array( 'elevate-action' => false ), $current_page );
	}

	function redirect( $url ) {
		header( 'Location: ' . $url );
		die;
	}

	public function actual_save_settings() {
		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Actual saving, wizard state is ' . $this->settings->wizard_complete );

		update_option( ELEVATE_SETTINGS_KEY, $this->settings, 'no' );
	}

	function save_settings() {
		require_once( 'helpers.php' );

		// Called when the user clicks the Save Settings button
		$was_modified = false;

		$default_settings = new stdClass;
		$this->init_default_settings( $default_settings );

		$setting_keys = ElevateHelpers::object_to_array( $default_settings );
		foreach( $setting_keys as $key => $value ) {
			// See if this key exists in the post data; if it does we'll update our setting from it
			$modified_key = 'elevate_' . $key; // add elevate_ prefix
			if ( isset( $this->request_vars[ $modified_key ] ) ) {
				$new_value = $this->request_vars[ $modified_key ];

				// Checkboxes are slightly different, so we'll handle them separately
				if ( $new_value == '%is_check%' ) {
					$this->settings->{$key} = ( isset( $this->request_vars[ $modified_key . '_cb' ] ) && $this->request_vars[ $modified_key . '_cb' ] == 'on' );
				} else {
					$this->settings->{$key} = $new_value;	
				}
				
				// Set modification flag
				if ( !$was_modified ) {
					$was_modified = true;
				};
			}
		}

		// Update the database if something changed
		if ( $was_modified ) {
			$this->actual_save_settings();
		}

		//$this->_generate_sitemap( false );
		$this->redirect( $this->_get_current_page_without_actions() );
	}

	private function _dump_file_to_browser( $filename ) {
		$to_dump = fopen( $filename, 'rb' );

		if ( $to_dump ) {
			while ( !feof( $to_dump ) ) {
	    		$contents = fread( $to_dump, 8192 );
	    		echo $contents;
			}

			fclose( $to_dump );			
		}
	}

	private function _check_for_sitemap() {
		if ( ( strpos( $_SERVER[ 'REQUEST_URI' ], $this->settings->sitemap_name ) !== false ) || ( strpos( $_SERVER[ 'REQUEST_URI' ], "-sitemap" ) !== false ) ) {
			// Looks like a sitemap request
			$base = basename( $_SERVER[ 'REQUEST_URI' ] );
			$potential_file = trailingslashit( $this->_get_sitemap_dir() ) . ltrim( $_SERVER[ 'REQUEST_URI' ], '/' );

			if ( file_exists( $potential_file ) ) {
				header( 'Content-type: text/xml' );

				$this->_dump_file_to_browser( $potential_file );
				die;
			}
		} 	
	}

	private function _check_for_redirects() {
		if ( $this->settings->ssl_redirect ) {
			// TODO: do SSL redirect
		}

		if ( $this->settings->redirect_media && is_attachment() ) {
			global $post;

			if (  $post->post_parent ) {
				$redirect_url = get_permalink( $post->post_parent );

				// Do a 301 Redirecgt
				header( "HTTP/1.1 301 Moved Permanently" ); 
				header( "Location: " . $redirect_url ); 
				die;
			}
		}
	}

	function handle_wp() {
		if ( !is_admin() ) {
			$this->_check_for_redirects();
		}		
	}

	private function _setup_taxonomies() {
		$wp_taxonomies = get_taxonomies( 
			array(  
				'public' => true
  			)
  		);

		$exclude_list = array( 'post_format', 'product_shipping_class' );

		foreach( $wp_taxonomies as $tax ) {
			if ( !in_array( $tax, $exclude_list ) ) {
				add_action( $tax . '_edit_form', array( &$this, 'handle_tax_edit_form' ), 10  );
				add_action( 'edited_' . $tax, array( &$this, 'handle_tax_save_edit_form' ), 10, 1  );		
			}
		}	
	}

	function handle_init() {
		if ( $_SERVER[ 'REQUEST_URI'] == '/BingSiteAuth.xml' ) {
			if ( $this->settings->bing_auth_code ) {
				header( 'Content-type: text/xml' );

				echo '<?xml version="1.0"?><users><user>' . $this->settings->bing_auth_code . '</user></users>';
				die;
			}
		}

		if ( !is_admin() && $this->settings->cdn_url && $this->settings->use_cdn ) {
			add_filter( 'the_content', array( &$this, 'handle_content_cdn' ) );
		}

		$this->_check_for_sitemap();	

		$this->_setup_taxonomies();

		if ( !is_admin() ) {
			$this->handle_elevate_actions();
		}
	}

	function handle_content_cdn( $content ) {
		if ( preg_match_all( '/(<img.+src=[\'"]([^\'"]+)[\'"].*>)/i', $content, $matches ) ) {
			if ( isset( $matches ) && isset( $matches[0] ) && count ( $matches[ 0 ] ) ) {

				$count = 0;
				$to_find = array();
				$to_replace = array();

				foreach( $matches[0] as $content_image ) {

					$image_url = $matches[ 2 ][ $count ];
					$url_with_version = add_query_arg( array( 'v' => $this->settings->cache_version ), $image_url );

					$original_image_tag = $matches[ 1 ][ $count ];
					$modified_image_tag = str_replace( $image_url, $url_with_version, $original_image_tag );

					$new_tag = str_replace( trailingslashit( home_url() ), trailingslashit( $this->settings->cdn_url ), $modified_image_tag );
					
					$to_find[] = $content_image;
					$to_replace[] = $new_tag;

					$count++;
				}
				
				if ( count( $to_find ) ) {
					$content = str_replace( $to_find, $to_replace, $content );
				}
			}
		} 
		
		return $content;		
	}

 	function _has_separate_blog_page() {
		$posts_page = get_option( 'page_for_posts' );
		$home_page = get_option( 'page_on_front' );

		return( $posts_page && $home_page );
	}

	function is_on_front_or_posts() {
		return ( ( get_the_ID() == get_option( 'page_for_posts' ) ) || ( get_the_ID() == get_option( 'page_on_front' ) ) );
	}

	private function _output_one_meta_field( $name, $content, $include_empty = true ) {
		require_once( dirname( __FILE__ ) . '/meta.php' );

		if ( $content || ( !$content && $include_empty ) ) {
			ElevateMeta::output_name( $name, $content );
		}
	}

	private function _output_one_property_field( $name, $content, $include_empty = true ) {
		require_once( dirname( __FILE__ ) . '/meta.php' );

		if ( $content || ( !$content && $include_empty ) ) {
			ElevateMeta::output_property( $name, $content );
		}
	}

	private function _maybe_output_social_tags( $url, $title, $desc, $app_id, $image ) {
		if ( $this->settings->enable_facebook_og ) {
			require_once( 'facebook.php' );

			$facebook = new ElevateFacebook( $url, $title, $desc, $app_id, $image, $this->settings->site_name );
			$facebook->output_meta();
		}

		if ( $this->settings->enable_twitter_cards ) {
			require_once( 'twitter.php' );

			$twitter = new ElevateTwitter( $url, $title, $desc, $image, $this->settings->social_twitter_name, $this->settings->twitter_card_image_size == 'summary_large_image' );
			$twitter->output_meta();		
		}
	}

	public function get_first_image_in_post( $post_id ) {
		$post_info = get_post( $post_id );
		if ( $post_info ) {
			if ( preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_info->post_content, $matches ) ) {
				if ( isset( $matches[1] ) && is_array( $matches[1] ) && isset( $matches[1][0] ) ) {

					$total_images = count( $matches[ 1 ] );
					$cur_image = 0;

					while ( true ) {
						if ( $cur_image == $total_images ) {
							break;
						}

						$image = $matches[ 1 ][ $cur_image ];	

						// Blacklist some tracking images
						if ( strpos( $image, 'linksynergy.com') !== false ) {
							$image = false;
							$cur_image++;

							continue;
						}

						$cur_image++;

						if ( $image ) {
							return $image;
						}
					}
				}
			}
		}

		return false;
	}

	private function _get_post_image( $post_id = false ) {
		$image = false;

		// Check for site-wide images
		if ( $this->settings->thumbnail_behaviour == 'force_global' && $this->settings->facebook_default_image ) {
			return trailingslashit( home_url() ) . $this->settings->facebook_default_image;
		}

		// Check for post content first
		if ( $post_id ) {
			$image = get_the_post_thumbnail_url( $post_id, 'full' );	

			if ( !$image ) {
				if ( $this->settings->thumbnail_behaviour == 'include_content' ) {
					// Look for an image in the content

					$image = $this->get_first_image_in_post( $post_id );
				}
			} 			
		}

		// Check for global image
		if ( !$image ) {
			if ( $this->settings->facebook_default_image ) {
				$image = trailingslashit( home_url() ) . $this->settings->facebook_default_image;
			}
		}

		return $image;
	}

	public function _get_intelligent_meta_desc_from_content( $content, $max_length = 300 ) {
		if ( strlen( $content ) < $max_length ) {
			return $content;
		}

		$all_sentences = explode( '.', strip_tags( $content ) );

		$description = false;

		if ( !empty( $all_sentences ) ) {
			$description = $all_sentences[0] . '.';

			$max_count = count( $all_sentences );
			$cur_count = 1;

			while ( true ) {
				if ( $cur_count == $max_count ) {
					break;
				}

				if ( ( strlen( $description ) + strlen( $all_sentences[ $cur_count ] ) < $max_length ) ) {
					$description = $description . ' ' . trim( $all_sentences[ $cur_count ] );
				} else {
					break;
				}

				$cur_count++;

				if ( $cur_count < $max_count ) {
					$description = $description . '.';
				}
			}
		}

		return $description;
	}

	public function get_intelligent_meta_desc( $id = false, $max_length = 300 ) {
		$description = false;

		if ( $id ) {
			$post = get_post( $id );

			$content = strip_tags( strip_shortcodes( $post->post_content ) );
		} else {
			$content = strip_shortcodes( strip_shortcodes( strip_tags( get_the_content() ) ) );	
		}

		if ( strlen( $content ) < $max_length ) {
			return $content;
		}

		// look for periods, this likely won't work in some languages - will find a work around later
		$description = $this->_get_intelligent_meta_desc_from_content( $content, $max_length );	

		return $description;
	}

	private function _maybe_insert_canonical( $url ) {
		if ( $this->settings->insert_canonical ) {
			echo '<link rel="canonical" href="' . esc_url( $url  ) . '" />' . "\n";
		}			
	}

	private function _maybe_output_breadcrumb() {
		require_once( dirname( __FILE__ ) . '/breadcrumbs.php' );

		if ( $this->settings->enable_breadcrumbs ) {
			$breadcrumbs = new ElevateBreadcrumbs( $this->settings->breadcrumb_home );
			$breadcrumbs->output_breadrumbs();		
		}
	}

	private function _output_meta_fields() {
		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'In output meta fields' );

		$modified_title = $this->_get_internal_title();

		if ( $this->_is_woo_commerce_shop_page() ) {
			$shop_page_id = get_option( 'woocommerce_shop_page_id' );

			$meta_info = $this->get_saved_meta_box_info( $shop_page_id, true );
		} else if ( is_tax() || is_category() || is_tag() ) {
			$category = get_queried_object();
			$meta_info = $this->get_saved_meta_box_info( $category->term_id, true );
		} else {
			$meta_info = $this->get_saved_meta_box_info( get_the_ID(), is_tax() );
			$modified_title = $this->_get_internal_title();
		}	
		
		if ( $this->_is_woo_commerce_shop_page() ) {
			$shop_page_id = get_option( 'woocommerce_shop_page_id' );
			$image = $this->_get_post_image( $shop_page_id );

			$description = $meta_info->desc ? $meta_info->desc : false;

			if ( !$description && $this->settings->fill_empty_description ) {
				// Attempt to fill the meta from the content
				$description = $this->get_intelligent_meta_desc( $shop_page_id );
			}

			$this->_output_one_meta_field( 'description', $description );
			$this->_maybe_output_social_tags( get_permalink( $shop_page_id ), $modified_title, $description, $this->settings->facebook_app_id, $image );

			$this->_maybe_insert_canonical( get_permalink( $shop_page_id ) );	
		} else if ( is_front_page() ) {
			if ( $this->_has_separate_blog_page() ) {
				// Show the home description
				$image = $this->_get_post_image( get_option( 'page_on_front' ) );

				$this->_output_one_meta_field( 'description', $this->settings->home_desc );
				$this->_maybe_output_social_tags( home_url(), $modified_title, $this->settings->home_desc, $this->settings->facebook_app_id, $image );
			} else {
				// There's no image in this case, so we use the default images 
				$image = $this->_get_post_image( false );

				$this->_output_one_meta_field( 'description', $this->settings->home_desc );
				$this->_maybe_output_social_tags( home_url(), $modified_title, $this->settings->home_desc, $this->settings->facebook_app_id, $image );	
			}

			$this->_maybe_insert_canonical( home_url() );	
		} else if ( is_home() ) {
			if ( $this->_has_separate_blog_page() ) {
				$page_id = get_option( 'page_for_posts' );
				$image = $this->_get_post_image( get_option( $page_id ) );
				$link = get_permalink( $page_id );

				// Show the home description
				$this->_output_one_meta_field( 'description', $this->settings->blog_desc );
				$this->_maybe_output_social_tags( $link, $modified_title, $this->settings->blog_desc, $this->settings->facebook_app_id, $image );

				$this->_maybe_insert_canonical( $link );	
			} else {
				$image = $this->_get_post_image( get_option( 'page_on_front' ) );

				// Show the home description
				$this->_output_one_meta_field( 'description', $this->settings->home_desc );
				$this->_maybe_output_social_tags( home_url(), $modified_title, $this->settings->home_desc, $this->settings->facebook_app_id, $image );	

				$this->_maybe_insert_canonical( home_url() );			
			}
		} else if ( is_singular() || is_page() ) {
			$image = $this->_get_post_image( get_the_ID() );

			$description = $meta_info->desc ? $meta_info->desc : false;

			if ( !$description && $this->settings->fill_empty_description ) {
				// Attempt to fill the meta from the content
				$description = $this->get_intelligent_meta_desc();
			}

			$this->_output_one_meta_field( 'description', $description );
			$this->_maybe_output_social_tags( get_permalink(), $modified_title, $description, $this->settings->facebook_app_id, $image );

			$this->_maybe_insert_canonical( get_permalink() );	

			if ( is_page() ) {
				$this->_maybe_output_breadcrumb();
			}
		} else if ( is_search() || is_404() ) {
			$image = false;

			if ( $this->settings->facebook_default_image ) {
				$image = trailingslashit( home_url() ) . $this->settings->facebook_default_image;
			}

			global $wp;
			$link = home_url( add_query_arg( $_GET, $wp->request ) );

			$this->_maybe_output_social_tags( $link, $modified_title, false, $this->settings->facebook_app_id, $image );
			$this->_maybe_insert_canonical( $link );

			if ( is_404() ) {
				// Let's log all 404s
				$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				$this->elevate_db->add_404( $actual_link );
			}
		} else if ( is_tax() || is_category() || is_tag() ) {
			$image = false;

			$term = get_term( $category->term_id );

			if ( $term->taxonomy === 'product_cat' ) {
				// WooCommerce

				$thumbnail_id = get_woocommerce_term_meta( $term->term_id, 'thumbnail_id', true );

				if ( $thumbnail_id ) {
					$image = wp_get_attachment_url( $thumbnail_id );
				}
			}

			if ( !$image && $this->settings->facebook_default_image ) {
				$image = trailingslashit( home_url() ) . $this->settings->facebook_default_image;
			}

			$term = get_term( $category->term_id );
			$description = $meta_info->desc ? $meta_info->desc : $term->description;
			$this->_output_one_meta_field( 'description', $description );

			$category = get_queried_object();

			$this->_maybe_output_social_tags( get_category_link( $category->term_id ), $modified_title, $description, $this->settings->facebook_app_id, $image );
			$this->_maybe_insert_canonical( get_category_link( $category->term_id ) );				
		}

		if ( $this->settings->site_owner ) {
			$this->_output_one_meta_field( 'publisher', $this->settings->site_owner  );

			$author = get_the_author(); 
			if ( empty( $author ) ) {
				$author = $this->settings->site_owner;
			}

			$this->_output_one_meta_field( 'author', $author );
		}	

		// Meta ROBOTS
		$default = $this->settings->index_default;

		$index_status = $default;

		if ( is_home() ) {
			if ( $this->settings->index_front_page != 'inherit' ) {
				$index_status = $this->settings->index_front_page;
			}
		} else if ( is_page() ) {
			if ( $this->settings->index_pages != 'inherit' ) {
				$index_status = $this->settings->index_pages;
			}		
		} else if ( is_single() ) {
			if ( $this->settings->index_posts != 'inherit' ) {
				$index_status = $this->settings->index_posts;
			}			
		} else if ( is_author() ) {
			if ( $this->settings->index_authors != 'inherit' ) {
				$index_status = $this->settings->index_authors;
			}	
		} else if ( is_attachment() ) {
			if ( $this->settings->index_media != 'inherit' ) {
				$index_status = $this->settings->index_media;
			}	
		} else if ( is_category () ) {
			if ( $this->settings->index_categories != 'inherit' ) {
				$index_status = $this->settings->index_categories;
			}	
		} else if ( is_tag() ) {
			if ( $this->settings->index_tags != 'inherit' ) {
				$index_status = $this->settings->index_tags;
			}	
		} else if ( is_tax() ) {
			if ( $this->settings->index_taxonomies != 'inherit' ) {
				$index_status = $this->settings->index_taxonomies;
			}	
		} else if ( is_archive() ) {
			if ( $this->settings->index_archives != 'inherit' ) {
				$index_status = $this->settings->index_archives;
			}	
		} else if ( is_search() ) {
			if ( $this->settings->index_search != 'inherit' ) {
				$index_status = $this->settings->index_search;
			}	
		} else if ( is_404() ) {
			if ( $this->settings->index_fourohfour != 'inherit' ) {
				$index_status = $this->settings->index_fourohfour;
			}			
		}

		// Check per page robots status
		if ( isset( $meta_info ) && isset( $meta_info->robots ) && $meta_info->robots != 'inherit' ) {
			$index_status = $meta_info->robots;
		}

		// Default bot behaviour is always index,follow, so only output if it's different
		if ( $index_status != 'index,follow' ) {
			$this->_output_one_meta_field( 'robots', $index_status );
		}	

		if ( is_front_page() && $this->settings->bing_auth_code ) {
			$this->_output_one_meta_field( 'msvalidate.01', $this->settings->bing_auth_code );
		}	

		if ( $this->settings->include_article_info && is_singular() && !is_page() ) {
			global $post;

			$this->_output_one_property_field( 'og:type', 'article' );

			$post_date = new DateTime( $post->post_date );
			$this->_output_one_property_field( 'article:published_time', $post_date->format( 'c' ) );

			$post_date = new DateTime( $post->post_modified );
			$this->_output_one_property_field( 'article:modified_time', $post_date->format( 'c' ) );

			$tags = get_the_tags();
			if ( $tags ) {
				foreach( $tags as $this_tag ) {
					$this->_output_one_property_field( 'article:tag', $this_tag->name );	
				}
			}

			$category = get_the_category();
			if ( $category ) {
				$this->_output_one_property_field( 'article:section', $category[0]->name );
			}	  		

			/*
			$author = get_the_author();
			if ( $author ) {
				$this->_output_one_meta_field( 'article:author', $author );
			}
			*/
		}
	}

	function _is_doing_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	function handle_elevate_actions() {
		if ( !$this->_is_doing_ajax() ) {
			if ( isset( $this->request_vars[ 'elevate_action' ] ) ) {
				$toolbar_nonce = $this->request_vars[ 'toolbar_nonce' ];
				if ( wp_verify_nonce( $toolbar_nonce, 'elevate_toolbar' ) && current_user_can( 'manage_options' ) ) {
					switch( $this->request_vars[ 'elevate_action' ] ) {
						case 'refresh_stats':
							$this->_clear_dashboard_transients();
							break;
						case 'update_sitemap':
							$this->refresh_sitemap();
							break;
						case 'clear_cache':
							$this->settings->cache_version = time();
							$this->actual_save_settings();
							
							break;	
					}					
				}

				$this->redirect( add_query_arg( array( 'elevate_action' => false, 'toolbar_nonce' => false ), $_SERVER[ 'REQUEST_URI' ] ) );
			}
		}
	}

	function _handle_admin() {
		// Check OAUTH nonce and set up JS for OAUTH completion
		if ( isset( $this->request_vars[ 'elevate_state'] ) ) {
			$oauth_nonce = $this->request_vars[ 'elevate_state' ];
			if ( ElevateAPI::is_api_nonce_valid( $oauth_nonce ) && isset( $this->request_vars[ 'elevate_oauth'] ) ) {
				$this->_convert_oauth_code_to_token( $this->request_vars[ 'code' ] );

				$params = array(
					'code' => false,
					'elevate_readonly' => false,
					'elevate_state' => false,
					'elevate_oauth' => false,
					'elevate_continue_wizard' => 1
				);

				$this->redirect( add_query_arg( $params, $_SERVER[ 'REQUEST_URI' ] ) );
			}			
		}	

		$this->handle_elevate_actions();	
	}

	function handle_wp_head() {
		// Where we output meta fields
		$this->_output_meta_fields();
	}

	function handle_admin_head() {
		echo '<style type="text/css">';
		echo '	section.elevate .about a, #wpbody .elevate .wizard .steps .done a, #wpbody .elevate .wizard .content h3, .elevate .settings-section h4 { color: ' . $this->get_admin_color( 2 ) . '; }';
		echo '	#wpbody .elevate .wizard .content h4, .elevate .settings-section h5 { color: ' . $this->get_admin_color( 1 ) . '; }';
		echo '	#wpbody .elevate .wizard .actions a, .upload button, .inside-button .ui-button, .elevate .settings-area form input.button-primary { background: ' . $this->get_admin_color( 2 ) . '; }';
		echo '  .inside-button .ui-button:hover, .elevate .settings-area form input.button-primary:hover, #wpbody .elevate .wizard .actions a:hover { background: ' . $this->get_admin_color( 3 ) . '; }';
		echo '  section.elevate .about a:hover { color: ' . $this->get_admin_color( 3 ) . '; }';
		echo ' .elevate-setting .tooltip { color: ' . $this->get_admin_color( 2 ) . '; }';
		echo ' #wpbody .elevate .wizard .content #wizard-auth, #wpbody .elevate .wizard .content #wizard-fix { background: ' . $this->get_admin_color( 2 ) . ';}';
		echo '</style>';		
	}

	function admin_init() {
		// Check for Elevate specific actions
		if ( isset( $_GET[ 'elevate-action' ] ) ) {
			switch( $_GET[ 'elevate-action' ] ) {
				case 'save-settings':
					// User has click Save Settings, so let's save them
					$this->save_settings();
					break;
			}
		}
		
		$this->_handle_admin();	
	}

	function create_admin_menu() {
		$settings = $this->get_settings();

		$top_level = false;

		if ( $settings->wizard_complete ) {
			$top_level = 'elevate_dashboard';

			add_menu_page( 'Elevate', __( 'Elevate SEO', 'elevate' ), 'manage_options', $top_level, '', 'dashicons-search', 20  );
			add_submenu_page( $top_level, __( 'Dashboard', 'elevate' ), __( 'Dashboard', 'elevate' ), 'manage_options', $top_level, array( &$this, 'show_plugin_dashboard' ) );;
		} else {
			$top_level = 'elevate_plugin';

			add_menu_page( 'Elevate', __( 'Elevate SEO', 'elevate' ), 'manage_options', $top_level, '', 'dashicons-search', 20  );
			add_submenu_page( $top_level, __( 'Get Started', 'elevate' ), __( 'Get Started', 'elevate' ), 'manage_options', $top_level, array( &$this, 'show_plugin_wizard' ) );
		}

		add_submenu_page( $top_level, __( 'General', 'elevate' ), __( 'General', 'elevate' ), 'manage_options', 'elevate_general', array( &$this, 'show_general_options' ) );		

		add_submenu_page( $top_level, __( 'Search', 'elevate-seo' ), __( 'Search', 'elevate-seo' ), 'manage_options', 'elevate_search', array( &$this, 'show_search_options' ) );	

		add_submenu_page( $top_level, __( 'Sitemap', 'elevate' ), __( 'Sitemap', 'elevate' ), 'manage_options', 'elevate_sitemap', array( &$this, 'show_sitemap_options' ) );


		add_submenu_page( $top_level, __( 'Redirects', 'elevate' ), __( 'Redirects', 'elevate' ), 'manage_options', 'elevate_redirects', array( &$this, 'show_redirects' ) );	
		add_submenu_page( $top_level, __( 'Performance', 'elevate' ), __( 'Performance', 'elevate' ), 'manage_options', 'elevate_performance', array( &$this, 'show_performance' ) );

		add_submenu_page( $top_level, __( 'Social Media', 'elevate' ), __( 'Social Media', 'elevate' ), 'manage_options', 'elevate_social', array( &$this, 'show_social_media_options' ) );

		if ( ElevatePlugin::get_one_setting( 'enable_advanced_settings') ) {
			add_submenu_page( $top_level, __( 'Advanced', 'elevate-seo' ), __( 'Advanced', 'elevate-seo' ), 'manage_options', 'elevate_advanced', array( &$this, 'show_advanced_settings' ) );	
		}
	}

	function show_advanced_settings() {
		include( ELEVATE_PLUGIN_DIR . '/admin/pages/advanced.php' );
	}

	function show_plugin_wizard() {
		require_once( dirname( __FILE__ ) . '/apache.php' );

		include( ELEVATE_PLUGIN_DIR . '/admin/pages/wizard.php' );
	}

	function show_general_options() {
		include( ELEVATE_PLUGIN_DIR . '/admin/pages/general.php' );
	}

	function show_sitemap_options() {
		include( ELEVATE_PLUGIN_DIR . '/admin/pages/sitemap.php' );
	}

	function show_titles() {
		include( ELEVATE_PLUGIN_DIR . '/admin/pages/titles.php' );
	}

	function show_search_options() {
		include( ELEVATE_PLUGIN_DIR . '/admin/pages/google.php' );
	}

	function show_redirects() {
		include( ELEVATE_PLUGIN_DIR . '/admin/pages/redirects.php' );
	}	

	function show_performance() {
		include( ELEVATE_PLUGIN_DIR . '/admin/pages/performance.php' );
	}

	function show_indexing() {
		include( ELEVATE_PLUGIN_DIR . '/admin/pages/indexing.php' );
	}

	function show_social_media_options() {
		include( ELEVATE_PLUGIN_DIR . '/admin/pages/social.php' );
	}

	function show_plugin_dashboard() {
		include( ELEVATE_PLUGIN_DIR . '/admin/pages/dashboard.php' );
	}
}