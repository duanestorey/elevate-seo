<?php

$page = new ElevateSettingsPage( __( 'Performance', 'elevate-seo' ) );

if ( false ) {
	// Not quite baked yet
	$section = new ElevateSettingsSection( __( 'Page Cache', 'elevate-seo' ), __( 'Speed up your website by caching frequently accessed pages', 'elevate-seo' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'enable_page_cache', __( 'Enable page caching', 'elevate-seo' ), __( 'When enabled Elevate will cache pages, reducing server load and speeding up response time.', 'elevate-seo' ) ) );
	$page->add_section( $section );
}

$section = new ElevateSettingsSection( __( 'Content Delivery Network', 'elevate-seo' ), __( 'Distribute resources to a remote delivery network', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingCheckbox( 'use_cdn', __( 'Use Content Delivery Network', 'elevate-seo' ), __( 'Controls whether or not to substitute the CDN URL for images in your content.', 'elevate-seo' ) ) );
$section->add_setting( new ElevateSettingText( 'cdn_url', __( 'Content Delivery Network URL', 'elevate-seo' ), __( 'If you use a CDN, you can insert the URL here and have your post images served from the CDN', 'elevate-seo' ), 'https://mydomain.cloudfront.com' ) );
$page->add_section( $section );

$page->render();


