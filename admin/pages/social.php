<?php

$page = new ElevateSettingsPage( __( 'Social Media', 'elevate-seo' ) );

/*
$section = new ElevateSettingsSection( __( 'Accounts', 'elevate-seo' ), __( 'Configure your social media accounts.', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingText( 'social_twitter_name', 'Twitter', '', 'username' ) );
$section->add_setting( new ElevateSettingText( 'social_facebook_name', 'Facebook', '', 'username'  ) );
$section->add_setting( new ElevateSettingText( 'social_instagram_name', 'Instagram', '', 'username'  ) );
$section->add_setting( new ElevateSettingText( 'social_pinterest_name', 'Pinterest', '', '@username'  ) );
$section->add_setting( new ElevateSettingText( 'social_linkedin_name', 'LinkedIn', '', 'https://linkedin.com/in/myname'  ) );
$section->add_setting( new ElevateSettingText( 'social_googleplus_name', 'Google+', '', 'https://plus.google.com/+myname'  ) );
$page->add_section( $section );
*/

$section = new ElevateSettingsSection( __( 'Social Media Services', 'elevate' ), __( 'Configure which services generate additional page information', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingCheckbox( 'enable_facebook_og', __( 'Enable Facebook OpenGraph', 'elevate-seo' ), '' ) );
$section->add_setting( new ElevateSettingText( 'facebook_app_id', 'Facebook App ID', __( 'If you create an application, you can use Facebook Insights to track analytics to and from Facebook', 'elevate-seo' ), __( 'Optional', 'elevate-seo' )  ) );
$section->add_setting( new ElevateSettingSpacer() );
$section->add_setting( new ElevateSettingCheckbox( 'enable_twitter_cards', __( 'Enable Twitter cards', 'elevate-seo' ), '' ) );
$section->add_setting( new ElevateSettingList( 'twitter_card_image_size', __( 'Twitter card image size', 'elevate-seo' ), '', array(
	'summary' => __( 'Default', 'elevate-seo' ),
	'summary_large_image' => __( 'Large', 'elevate-seo' )
) ) );
$section->add_setting( new ElevateSettingText( 'social_twitter_name', __( 'Your Twitter username', 'elevate-seo' ), '', 'MyUserName' ) );
$section->add_setting( new ElevateSettingSpacer() );
$section->add_setting( new ElevateSettingList( 'thumbnail_behaviour', __( 'Social media image priority', 'elevate-seo' ), __( 'You can select which image is used as the focus image when your content is shared. The site-wide image can be configured on this page as well.', 'elevate-seo' ), array(
		'featured' => __( 'Featured, Site-wide', 'elevate-seo' ),
		'include_content' => __( 'Featured, First in Content, Site-wide', 'elevate-seo' ),
		'force_global' => __( 'Site-wide always', 'elevate-seo' )
	) ) );

$section->add_setting( new ElevateSettingUpload( 'facebook_default_image', __( 'Site-wide social media image', 'elevate-seo' ), '' ) );

$page->add_section( $section );

if ( ElevatePlugin::get_one_setting( 'enable_advanced_settings') ) {
	$section = new ElevateSettingsSection( __( 'Structured Data', 'elevate' ), __( 'Configure which cards are automatically inserted', 'elevate-seo' ) );
	$section->add_setting( new ElevateSettingCheckbox( 'include_article_info', __( 'Insert article information for posts', 'elevate-seo' ), '', '' ) );
	$section->add_setting( new ElevateSettingSpacer() );
	$section->add_setting( new ElevateSettingCheckbox( 'enable_breadcrumbs', __( 'Insert breadcrumb information', 'elevate-seo' ), '' ) );
	$section->add_setting( new ElevateSettingText( 'breadcrumb_home', __( 'Breadcrumb name for home page', 'elevate-seo' ), '', '' ) );
	$page->add_section( $section );
}


$page->render();