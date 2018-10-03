<?php

$page = new ElevateSettingsPage( __( 'Performance', 'elevate-seo' ) );

$section = new ElevateSettingsSection( __( 'Basic Settings', 'elevate-seo' ), __( 'Settings related to the speed of your website', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingCheckbox( 'use_cdn', __( 'Use Content Delivery Network', 'elevate-seo' ), __( 'Controls whether or not to substitute the CDN URL for images in your content.', 'elevate-seo' ) ) );
$section->add_setting( new ElevateSettingText( 'cdn_url', __( 'Content Delivery Network URL', 'elevate-seo' ), __( 'If you use a CDN, you can insert the URL here and have your post images served from the CDN', 'elevate-seo' ), 'https://mydomain.cloudfront.com' ) );

$page->add_section( $section );
$page->render();


