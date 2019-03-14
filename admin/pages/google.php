<?php

$page = new ElevateSettingsPage( __( 'Search', 'elevate-seo' ) );

$section = new ElevateSettingsSection( __( 'Options', 'elevate-seo' ), __( 'Various options for search-related services', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingCheckbox( 'insert_analytics', __( 'Insert visitor tracking code', 'elevate-seo' ), __( 'Insert code for Google Analytics or other website visitor tracking', 'elevate-seo' ) ) );
$section->add_setting( new ElevateSettingTextArea( 'analytics_code', __( 'Visitor tracking code to insert', 'elevate-seo' ), __( 'This can be found within your Google analytics or other website visitor tracking configuration', 'elevate-seo' ) ) );
$section->add_setting( new ElevateSettingText( 'bing_auth_code', __( 'Bing site verification code', 'elevate-seo' ), __( 'When you add a new site to your Bing webmaster tools, Bing will give you an authorization code to insert into the webpage', 'elevate-seo') ) );

$section->add_setting( new ElevateSettingSpacer() );
if ( ElevatePlugin::get()->has_google_tokens() ) {
	$section->add_setting( 
		new ElevateSettingList( 
			'analytics_account_to_use', 
			__( 'Analytics account', 'elevate-seo' ), 
			__( 'You can choose which analytics account to display information for on the dashboard.', 'elevate-seo' ), 
			ElevatePlugin::get()->get_analytics_list_for_setting()
		)
	);
	$section->add_setting( new ElevateSettingSpacer() );
	
	$section->add_setting( new ElevateSettingButton( 'search_console_deactivate', __( 'Google OAuth Authentication', 'elevate-seo' ), '', __( 'Remove Authentication', 'elevate-seo' ) ) );	
} else {
	$section->add_setting( new ElevateSettingButton( 'search_console_activate', __( 'Google OAuth Authentication', 'elevate-seo' ), '', __( 'Authenticate', 'elevate-seo' ) ) );	
}

$page->add_section( $section );

$section = new ElevateSettingsSection( __( 'Crawling', 'elevate-seo' ), __( 'Options for controlling granular search', 'elevate-seo' ) );
$section->add_setting( 
	new ElevateSettingList( 
		'robots_txt', 
		__( 'Robots.txt Generation', 'elevate-seo' ), '', 
		array(
			'default' => __( 'Default WordPress', 'elevate-seo' ),
			'enhanced' => __( 'Enhanced', 'elevate-seo' ) 
		)
	) 
);
$section->add_setting( new ElevateSettingTextArea( 'robots_extra', __( 'Other information to append to robots.txt', 'elevate-seo' ), __( 'Only applies to Enhanced robots.txt generation', 'elevate-seo' ) ) );

$page->add_section( $section );

$section = new ElevateSettingsSection( __( 'Search Information', 'elevate-seo' ), __( 'Configure meta fields', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingCheckbox( 'insert_meta', __( 'Insert title and meta tags', 'elevate-seo' ), __( 'If you are using another SEO plugin, you may want to disable inserting meta tags from Elevate.', 'elevate-seo' ) ) );
$section->add_setting( new ElevateSettingSpacer() );
$section->add_setting( new ElevateSettingCheckbox( 'insert_canonical', __( 'Allow editing the canonical field', 'elevate-seo' ), __( 'This option will insert canonical meta information on each post or page for you to edit.', 'elevate-seo' ) ) );
$section->add_setting( new ElevateSettingCheckbox( 'insert_language', __( 'Allow editing the language field', 'elevate-seo' ), __( 'This option will insert the language field for each post or page to select.', 'elevate-seo' ) ) );
$section->add_setting( new ElevateSettingCheckbox( 'insert_robots', __( 'Allow editing the index status', 'elevate-seo' ), __( 'This option will allow you to edit the robots information for each post or page.', 'elevate-seo' ) ) );

$section->add_setting( new ElevateSettingSpacer() );
$section->add_setting( new ElevateSettingCheckbox( 'fill_empty_description', __( 'Intelligently fill empty meta description', 'elevate-seo' ), __( 'If no search description is given for a post, Elevate will automatically attempt to fill it with post content.', 'elevate-seo' ) ) );
if ( ElevatePlugin::get_one_setting( 'enable_advanced_settings') ) {
	$section->add_setting( new ElevateSettingList( 'import_behaviour', __( 'Default search title and description', 'elevate-seo' ), __( 'If you have used other SEO plugins previously, you can dynamically import their data here.', 'elevate-seo' ), array(
			'none' => __( 'Do not import data', 'elevate-seo' ),
			'yoast' => __( 'Import from Yoast', 'elevate-seo' ),
			'wp_meta_seo' => __( 'Import from WP Meta SEO', 'elevate-seo' )
		) 
	) );
}
$page->add_section( $section );

$page->render();
