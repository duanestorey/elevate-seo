<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */


define( 'ELEVATE_CRLF', "\n" );
define( 'ELEVATE_TAB', "\t" );
define( 'ELEVATE_SITEMAP_PER_PAGE', 20 );
define( 'ELEVATE_PRIORITY_MAIN', 1.0 );
define( 'ELEVATE_PRIORITY_POSTS', 0.8 );
define( 'ELEVATE_PRIORITY_ARCHIVES', 0.5 );

require_once( dirname( __FILE__ ) . '/wp-async-task.php' );

class ElevateSitemap extends WP_Async_Task {
	var $page_for_posts;
	var $page_for_home;
	var $elevate_plugin;

	var $sitemap_status;
	var $sitemap_dir;
	var $sitemap_params;

	protected $action = 'generate_sitemap';

 	public function __construct( &$plugin, $sitemap_status, $sitemap_dir, $sitemap_params ) {
 		$page_for_posts = get_option( 'page_for_posts' );
 		if ( $page_for_posts ) {
 			$this->page_for_posts = $page_for_posts;
 		}

 		$page_for_home = get_option( 'page_on_front' );
 		if ( $page_for_home ) {
 			$this->page_for_home = $page_for_home;
 		}

 		$this->elevate_plugin = &$plugin;
 		$this->sitemap_status = $sitemap_status;
 		$this->sitemap_dir = $sitemap_dir;
 		$this->sitemap_params = $sitemap_params;

 		parent::__construct();
	}	

	protected function prepare_data( $data ) {
		return array( 'force_it' => $data[0] );
	}

	protected function run_action() {
		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Asynchronously generating sitemap' );
		
		if ( $_POST[ 'force_it' ] || !$this->sitemap_status->has_sitemap ) {
			do_action( 'elevate_sitemap_update', 1, 0 );

			$total_entries = $this->elevate_plugin->settings->sitemap_entries;

			$total_entries = $this->generate( $_SERVER[ 'DOCUMENT_ROOT' ], $this->sitemap_dir, $this->elevate_plugin->settings->sitemap_name, $this->sitemap_params, $this->sitemap_params );	

			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Sitemap generated, total entries are ' . $total_entries );

			do_action( 'elevate_sitemap_update', 0, $total_entries );
		} else {
			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Skipping sitemap generation because it already exists and we aren\'t forcing it' );
		}
	}

	private function _write_sitemap_header( $sitemap_file ) {
		fwrite( $sitemap_file, '<?xml version="1.0" encoding="UTF-8"?>' . ELEVATE_CRLF );
		fwrite( $sitemap_file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . ELEVATE_CRLF );		
	}

	private function _write_sitemap_footer( $sitemap_file ) {
		fwrite( $sitemap_file, '</urlset>' . ELEVATE_CRLF );
	}

	private function _write_sitemap_info( $sitemap_file, $loc, $lastmod, $priority, $changefreq ) {
		fwrite( $sitemap_file, ELEVATE_TAB . '<url>' . ELEVATE_CRLF );
		fwrite( $sitemap_file, ELEVATE_TAB . ELEVATE_TAB . '<loc>' . $this->_encode_url( $loc ) . '</loc>' . ELEVATE_CRLF );
		fwrite( $sitemap_file, ELEVATE_TAB . ELEVATE_TAB . '<priority>' . $priority . '</priority>' . ELEVATE_CRLF );

		if ( $lastmod ) {
			fwrite( $sitemap_file, ELEVATE_TAB . ELEVATE_TAB . '<lastmod>' . $lastmod. '</lastmod>' . ELEVATE_CRLF );	
		}
			
		fwrite( $sitemap_file, ELEVATE_TAB . ELEVATE_TAB . '<changefreq>' . $changefreq . '</changefreq>' . ELEVATE_CRLF );
		fwrite( $sitemap_file, ELEVATE_TAB . '</url>' . ELEVATE_CRLF );
	}

	private function _encode_url( $url ) {
		$split_url = parse_url( $url );

		$new_url = $split_url[ 'scheme' ] . '://' . $split_url[ 'host' ];

		$path = $split_url[ 'path' ];
		$exploded_path = explode( '/', $path );
		$new_path = '/';

		$new_parts = array();
		foreach( $exploded_path as $one_path ) {
			$new_parts[] = rawurlencode( $one_path );
		}

		$new_path = implode( '/', $new_parts );

		if ( isset( $split_url[ 'query' ] ) ) {
			$new_path = $new_path . '?' . rawurlencode( $split_url[ 'query' ] );
		}

		return $new_url . $new_path;
	}

	private function _get_sitemap_priority( $post ) {
		$priority = ELEVATE_PRIORITY_ARCHIVES;

		$is_front = trailingslashit( home_url() ) == trailingslashit( get_permalink() );

		if ( $is_front ) {
			// Default main page
			$priority = ELEVATE_PRIORITY_MAIN;
		} else if ( $post->ID == $this->page_for_home ) {
			// Typically the blog page
			$priority = ELEVATE_PRIORITY_MAIN;
		} else if ( $post->ID == $this->page_for_posts ) {
			// Typically the blog page
			$priority = ELEVATE_PRIORITY_POSTS;
		} else if ( $post->post_type == 'page' || $post->post_type == 'post' ) { 
			$priority = ELEVATE_PRIORITY_POSTS;
		} 

		return $priority;
	}

	public function _generate_one_file( $query_params, $filename ) {
		$num_entries = 0;

		$sitemap_part = fopen( $filename, 'w+t' );
		$page_num = 0;
		if ( $sitemap_part ) {
			$this->_write_sitemap_header( $sitemap_part );

			$query_params[ 'posts_per_page' ] = ELEVATE_SITEMAP_PER_PAGE;
			$query_params[ 'post_status' ] = array( 'publish' );

			$query = new WP_Query( $query_params );

			$total_entries = $query->found_posts;
			$total_pages = $query->max_num_pages;
			$current_page = 1;

			while ( $total_pages ) {
				$query_params[ 'paged' ] = $current_page;
				$query_params[ 'update_post_term_cache' ] = false;
				$query_params[ 'cache_results' ] = false;
				$query_params[ 'update_post_meta_cache' ] = false;

				$query = new WP_Query( $query_params );

				while ( $query->have_posts() ) {
					$query->the_post();

					global $post;

					$this->_write_sitemap_info( $sitemap_part, get_permalink(), get_the_modified_time( 'Y-m-d' ), $this->_get_sitemap_priority( $post ), trailingslashit( home_url() ) == trailingslashit( get_permalink() ) ? 'daily' : 'weekly' );

					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, '   Writing entries ' . $num_entries . ' ' . memory_get_usage() );

					$num_entries++;

					unset( $post );
				}	

				ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Sitemap page written ' . $filename . ' ' . ' ' . $query->max_num_pages . ' ' . $current_page . ' ' . $total_pages );				
				unset( $query );

				$current_page++;
				$total_pages--;
			}

			$this->_write_sitemap_footer( $sitemap_part );
			fclose( $sitemap_part );	
		}

		gc_collect_cycles();

		return $num_entries;
	}

	private function _write_one_taxonomy_sitemap( $path_for_sub_files, $tax ) {
		$total_entries = 0;

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Starting writing term ' . $tax );

		$taxonomy_sitemap = fopen( $path_for_sub_files .'taxonomy-' . $tax . '-sitemap.xml', 'w+t' );
		if ( $taxonomy_sitemap ) {
			$this->_write_sitemap_header( $taxonomy_sitemap );

			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, '   Doing term query ' . $tax );
			$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true ) );
			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'What?' );
			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, '   Done term query ' . $tax );
			if ( $terms ) {
				foreach( $terms as $key => $term ) {
					ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, '   Writing term ' . $key );

					$link = get_term_link( $term->term_id );

					$modified_time = false;
					$tax_query = array( 'taxonomy' => $tax, 'field' => 'term_id', 'terms' => $term->term_id ); 
					$query_params = array( 'post_type' => 'post', 'orderby' => 'modified', 'order' => 'desc', 'posts_per_page' => 1, 'tax_query' => array( $tax_query ) );

					$query_params[ 'update_post_term_cache' ] = false;
					$query_params[ 'cache_results' ] = false;
					$query_params[ 'update_post_meta_cache' ] = false;

					$query = new WP_Query( $query_params );
					if ( $query->have_posts() ) {
						$query->the_post();
						$modified_time = get_the_modified_time( 'Y-m-d' );

						global $post;

						unset( $post );
					}
					
					$this->_write_sitemap_info( $taxonomy_sitemap, $link, $modified_time, ELEVATE_PRIORITY_ARCHIVES, 'weekly' );

					$total_entries++;

					unset( $query );
				}						
			}
		}

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, '   Writing footer' . $tax );

		$this->_write_sitemap_footer( $taxonomy_sitemap );	
		fclose( $taxonomy_sitemap );	

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Finished writing term ' . $tax );

		return $total_entries;
	}

	private function _custom_tax_to_skip() {
		return array( 'nav_menu', 'tag', 'category', 'link_category', 'post_format' );
	}

	public function generate( $document_root, $desired_directory, $filename, $include = array( 'posts', 'custom-posts', 'pages', 'media', 'authors', 'categories', 'tags', 'taxonomies' ), $generate = array( 'posts', 'custom-posts', 'pages', 'media', 'authors', 'categories', 'tags', 'taxonomies' ) ) {
		if ( !file_exists( $desired_directory ) ) {
			mkdir( $desired_directory, 0755 );
		}

		$total_entries = 0;

		$path_for_sub_files = $document_root;

		if ( file_exists( $desired_directory ) ) {
			$path_for_sub_files = $desired_directory;
		}

		$sitemap_file = fopen( $path_for_sub_files . '/' . $filename, 'w+t' );
		$sitemap_pieces = array();

		foreach( $generate as $sitemap_type ) {
			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Writing sitemap piece ' . $sitemap_type );

			switch( $sitemap_type ) {
				case 'posts':
					$query_params = array( 'post_type' => 'post' );
					$total_entries += $this->_generate_one_file( $query_params, $path_for_sub_files . 'posts-sitemap.xml' );						
					
					break;
				case 'custom-posts':
					$post_types = get_post_types( array( 'public' => true, '_builtin' => false ) );
					if ( $post_types ) {				
						foreach( $post_types as $post_type ) {
							$query_params = array( 'post_type' => $post_type );
							$total_entries += $this->_generate_one_file( $query_params, $path_for_sub_files . $post_type . '-sitemap.xml', ELEVATE_PRIORITY_POSTS );	

							$sitemap_pieces[ $post_type . '-sitemap.xml' ] = filemtime( $path_for_sub_files . $post_type . '-sitemap.xml');
						}			
					}

					break;
				case 'pages':
					$query_params = array( 'post_type' => 'page' );
					$total_entries += $this->_generate_one_file( $query_params, $path_for_sub_files . 'pages-sitemap.xml' );
					
					break;
				case 'media':
					$query_params = array( 'post_type' => 'attachment', 'post_status' => 'inherit' );
					$total_entries += $this->_generate_one_file( $query_params, $path_for_sub_files . 'attachments-sitemap.xml' );
				
					break;
				case 'authors':
					$author_sitemap = fopen( $path_for_sub_files . 'authors-sitemap.xml', 'w+t' );
					if ( $author_sitemap ) {
						$this->_write_sitemap_header( $author_sitemap );

						$users = get_users( array( 'role__in' => array( 'administrator', 'editor', 'author', 'contributor' ) ) );
						foreach( $users as $user ) {
							// See if they've published anything
							$query = new WP_Query( array( 'post_type' => 'post', 'author' => $user->ID, 'posts_per_page' => 1, 'orderby' => 'modified', 'order' => 'desc' ) );
							if ( $query->have_posts() ) {
								// this user has posts
								$link = get_author_posts_url( $user->ID );

								$total_entries++;

								$this->_write_sitemap_info( $author_sitemap, $link, get_the_time( 'Y-m-d' ), ELEVATE_PRIORITY_ARCHIVES, 'weekly' );
							}
						}

						$this->_write_sitemap_footer( $author_sitemap );
						fclose( $author_sitemap );
					}
					break;
				case 'categories':
					$total_entries += $this->_write_one_taxonomy_sitemap( $path_for_sub_files, 'category' );
					break;
				case 'tags':
					$total_entries += $this->_write_one_taxonomy_sitemap( $path_for_sub_files, 'post_tag' );
					break;
				case 'taxonomies':
					$taxonomies = get_taxonomies();
					foreach( $taxonomies as $tax ) {
						// Skip default built-in taxonomies
						if ( in_array( $tax, $this->_custom_tax_to_skip() ) ) {
							continue;
						}

						$total_entries += $this->_write_one_taxonomy_sitemap( $path_for_sub_files, $tax );
					}		
					break;
			}	

			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Finished writing sitemap piece ' . $sitemap_type );		
		}

		foreach( $include as $sitemap_type ) {
			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Including sitemap piece ' . $sitemap_type );

			switch( $sitemap_type ) {
				case 'posts':
					if ( file_exists( $path_for_sub_files . 'posts-sitemap.xml' ) ) {
						$sitemap_pieces[ 'posts-sitemap.xml' ] = filemtime( $path_for_sub_files . 'posts-sitemap.xml' );
					}
					break;
				case 'custom-posts':
					$post_types = get_post_types( array( 'public' => true, '_builtin' => false ) );
						
					foreach( $post_types as $post_type ) {
						if ( file_exists( $path_for_sub_files . $post_type . '-sitemap.xml' ) ) {
							$sitemap_pieces[ $post_type . '-sitemap.xml' ] = filemtime( $path_for_sub_files . $post_type . '-sitemap.xml' );	
						}
					}			
					break;
				case 'pages':
					if ( file_exists( $path_for_sub_files . 'pages-sitemap.xml' ) ) { 
						$sitemap_pieces[ 'pages-sitemap.xml' ] = filemtime( $path_for_sub_files . 'pages-sitemap.xml' );
					}
					break;
				case 'media':
					if ( file_exists( $path_for_sub_files . 'attachments-sitemap.xml' ) ) {
						$sitemap_pieces[ 'attachments-sitemap.xml' ] = filemtime( $path_for_sub_files . 'attachments-sitemap.xml' );
					}
					break;
				case 'authors':
					if ( file_exists( $path_for_sub_files . 'authors-sitemap.xml' ) ) { 
						$sitemap_pieces[ 'authors-sitemap.xml' ] = filemtime( $path_for_sub_files . 'authors-sitemap.xml' );
					}
					break;
				case 'categories':
					if ( file_exists( $path_for_sub_files . 'taxonomy-category-sitemap.xml' ) ) { 
						$sitemap_pieces[ 'taxonomy-category-sitemap.xml' ] = filemtime( $path_for_sub_files . 'taxonomy-category-sitemap.xml' );
					}
					break;
				case 'tags':
					if ( file_exists( $path_for_sub_files . 'taxonomy-tag-sitemap.xml' ) ) { 
						$sitemap_pieces[ 'taxonomy-tag-sitemap.xml' ] = filemtime( $path_for_sub_files . 'taxonomy-tag-sitemap.xml' );
					}
					break;
				case 'taxonomies':
					$taxonomies = get_taxonomies();
					
					foreach( $taxonomies as $tax ) {
						// Skip default built-in taxonomies
						if ( in_array( $tax, $this->_custom_tax_to_skip() ) ) {
							continue;
						}

						if ( file_exists( $path_for_sub_files . 'taxonomy-' . $tax . '-sitemap.xml' ) ) {
							$sitemap_pieces[ 'taxonomy-' . $tax . '-sitemap.xml' ] = filemtime( $path_for_sub_files . 'taxonomy-' . $tax . '-sitemap.xml' ); 
						}
					}						

					break;
			}

			ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Done including sitemap piece ' . $sitemap_type );
		}

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Writing master file now' );

		fwrite( $sitemap_file, '<?xml version="1.0" encoding="UTF-8"?>' . ELEVATE_CRLF );
		fwrite( $sitemap_file, '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . ELEVATE_CRLF );

		ksort( $sitemap_pieces );
		foreach( $sitemap_pieces as $name => $last_modified ) {
			fwrite( $sitemap_file, ELEVATE_TAB . '<sitemap>' . ELEVATE_CRLF );
			fwrite( $sitemap_file, ELEVATE_TAB . ELEVATE_TAB . '<loc>' . $this->_encode_url( home_url() . '/' . $name ) . '</loc>' . ELEVATE_CRLF );
			fwrite( $sitemap_file, ELEVATE_TAB . ELEVATE_TAB . '<lastmod>' . date( 'Y-m-d', $last_modified ) . '</lastmod>' . ELEVATE_CRLF );
			fwrite( $sitemap_file, ELEVATE_TAB . '</sitemap>' . ELEVATE_CRLF );
		}
		fwrite( $sitemap_file,  '</sitemapindex>' . ELEVATE_CRLF );
		fclose( $sitemap_file );

		ELEVATE_DEBUG( ELEVATE_DEBUG_INFO, 'Sitemap internally written' );

		return $total_entries;
	}
}