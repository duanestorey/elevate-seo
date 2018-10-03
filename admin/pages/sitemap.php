<?php
/*
	Elevate SEO Plugin for WordPress
	Copyright (C) 2017-2018 by Duane Storey and Lindell Media Inc.

	Released under the GNU General Public License, version 3.0
*/ 


$page = new ElevateSettingsPage( __( 'Sitemap', 'elevate-seo' ) );

$section = new ElevateSettingsSection( __( 'General', 'elevate' ), __( 'When submitted to search engines, sitemaps can improve your site ranking.', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingCheckbox( 'enable_sitemap_generation', __( 'Enable sitemap generation', 'elevate-seo' ), '' ) );
$section->add_setting( new ElevateSettingText( 'sitemap_name', __( 'Sitemap name', 'elevate-seo' ), '' ) );
$page->add_section( $section );

if ( ElevatePlugin::get_one_setting( 'enable_advanced_settings') ) {
	$section = new ElevateSettingsSection( __( 'Sitemap Contents', 'elevate' ), __( 'Specify the content you would like the sitemap to contain.', 'elevate-seo' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'sitemap_posts', __( 'Include posts', 'elevate-seo' ), '' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'sitemap_custom_posts', __( 'Include custom posts', 'elevate-seo' ), '' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'sitemap_pages', __( 'Include pages', 'elevate-seo' ), '' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'sitemap_media', __( 'Include media &amp; attachments', 'elevate-seo' ), '' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'sitemap_authors', __( 'Include author archives', 'elevate-seo' ), '' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'sitemap_categories', __( 'Include categories', 'elevate-seo' ), '' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'sitemap_tags', __( 'Include tags', 'elevate-seo' ), '' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'sitemap_taxonomy', __( 'Include custom taxonomies', 'elevate-seo' ), '' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'sitemap_images', __( 'Include images', 'elevate-seo' ), '' ) );
	$page->add_section( $section );
}

$page->render();