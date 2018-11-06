<?php

$page = new ElevateSettingsPage( __( 'General', 'elevate-seo' ) );

$section = new ElevateSettingsSection( __( 'Basic Configuration', 'elevate-seo' ), __( 'Configure the language and default settings.', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingList( 'selected_locale', __( 'Language', 'elevate-seo' ), __( 'Choose the language for the Elevate admin panel.', 'elevate-seo' ), 
	ElevatePlugin::get_supported_languages()
) );
$section->add_setting( new ElevateSettingCheckbox( 'enable_advanced_settings', __( 'Enabled advanced settings', 'elevate-seo' ), __( 'Enabling this option will show all the advanced settings.', 'elevate-seo' ) ) );
$section->add_setting( new ElevateSettingCheckbox( 'include_powered_by', __( 'Promote Elevate SEO in footer', 'elevate-seo' ), __( 'Add \'Powered by Elevate SEO\' to your theme\'s footer', 'elevate-seo' ), false ) );
$page->add_section( $section );


$section = new ElevateSettingsSection( __( 'Site Information', 'elevate-seo' ), __( 'Information about this site and its owner', 'elevate-seo' ) );

/*
$section->add_setting( new ElevateSettingRadio( 'site_type', __( 'Type of readership', 'elevate-seo' ), '', array( 
	'personal' => __( 'General Populace', 'elevate-seo' ),
	'business' => __( 'Professionals', 'elevate-seo '),
	'non-profit' => __( 'Academics', 'elevate-seo' ) 
	) 
) );

$section->add_setting( new ElevateSettingSpacer() );
*/

$section->add_setting( new ElevateSettingText( 'site_name', __( 'Site name', 'elevate-seo' ), __( 'The primary name of your website or company', 'elevate-seo' ) , 'My Company Inc.' ) );
$section->add_setting( new ElevateSettingSpacer() );
$section->add_setting( new ElevateSettingText( 'home_title', __( 'Home search title', 'elevate-seo' ), '', 'My Company Inc. | The best company in the Pacific Northwest' ) );
$section->add_setting( new ElevateSettingTextArea( 'home_desc', __( 'Home search description', 'elevate-seo' ), __( 'The description of your primary website page for search engines. This is normally one or two sentences long and describes the products or services you primarily offer.', 'elevate-seo' ) ) );

if ( get_elevate_plugin()->_has_separate_blog_page() ) {
	$section->add_setting( new ElevateSettingText( 'blog_title', __( 'Blog search title', 'elevate-seo' ), '', 'Latest posts by my Company Inc.' ) );
	$section->add_setting( new ElevateSettingTextArea( 'blog_desc', __( 'Blog search description', 'elevate-seo' ), '' ) );
}

$section->add_setting( new ElevateSettingText( 'site_owner', __( 'Site owner', 'elevate-seo' ), __( 'The name of the owner of this site, for example, Duane Storey', 'elevate-seo' ), 'Some Person' ) );


/*
$section->add_setting( new ElevateSettingSpacer() );

$section->add_setting( new ElevateSettingList( 'site_language', __( 'Site Content Language', 'elevate-seo' ), __( 'The language for the majority of the content on your website', 'elevate-seo' ), 
	ElevatePlugin::get_locale_listing()
) );



*/

$page->add_section( $section );


$page->render();

