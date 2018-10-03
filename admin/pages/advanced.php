<?php

$page = new ElevateSettingsPage( __( 'Advanced', 'elevate-seo' ) );

$section = new ElevateSettingsSection( __( 'Templates', 'elevate-seo' ), __( 'Configure how title tags are generated in each section of your site.', 'elevate-seo' ) );
// $section->add_setting( new ElevateSettingText( 'home_template', __( 'Home Title', 'elevate-seo' ), '', '' ) );
// $section->add_setting( new ElevateSettingText( 'blog_template', __( 'Blog Title', 'elevate-seo' ), '', '' ) );
$section->add_setting( new ElevateSettingText( 'post_template', __( 'Post', 'elevate-seo' ), '', '' ) );
$section->add_setting( new ElevateSettingText( 'page_template', __( 'Page', 'elevate-seo' ), '', '' ) );
$section->add_setting( new ElevateSettingText( 'archive_template', __( 'Archive', 'elevate-seo' ), '', '' ) );
$section->add_setting( new ElevateSettingText( 'media_template', __( 'Media', 'elevate-seo' ), '', '' ) );
$section->add_setting( new ElevateSettingText( 'author_template', __( 'Authors', 'elevate-seo' ), '', '' ) );
$section->add_setting( new ElevateSettingText( 'taxonomy_template', __( 'Taxonomies', 'elevate-seo' ), '', '' ) );
$section->add_setting( new ElevateSettingText( 'fof_template', __( '404 (Page not found)', 'elevate-seo' ), '', '' ) );
$section->add_setting( new ElevateSettingText( 'search_template', __( 'Search results', 'elevate-seo' ), '', '' ) );
$section->add_setting( new ElevateSettingText( 'title_separator', __( 'Title separator', 'elevate-seo' ), '' ) );

$page->add_section( $section );

$section = new ElevateSettingsSection( __( 'Index Settings', 'elevate-seo' ), __( 'Configure how search engines will behave when visiting sections of this site.', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingList( 'index_default', __( 'Default Status', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info( false ) ) );

$section->add_setting( new ElevateSettingSpacer );
$section->add_setting( new ElevateSettingList( 'index_front_page', __( 'Home', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );
$section->add_setting( new ElevateSettingList( 'index_posts', __( 'Posts', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );
$section->add_setting( new ElevateSettingList( 'index_pages', __( 'Pages', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );
$section->add_setting( new ElevateSettingList( 'index_authors', __( 'Author Pages', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );
$section->add_setting( new ElevateSettingList( 'index_media', __( 'Media Attachments', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );
$section->add_setting( new ElevateSettingList( 'index_categories', __( 'Category Pages', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );
$section->add_setting( new ElevateSettingList( 'index_tags', __( 'Tag Pages', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );
$section->add_setting( new ElevateSettingList( 'index_taxonomies', __( 'Taxonomy pages', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );
$section->add_setting( new ElevateSettingList( 'index_archives', __( 'Archive pages', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );
$section->add_setting( new ElevateSettingList( 'index_search', __( 'Search results', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );
$section->add_setting( new ElevateSettingList( 'index_fourohfour', __( 'Not found (404) pages', 'elevate-seo' ), '', get_elevate_plugin()->get_index_follow_info() ) );

$page->add_section( $section );

$section = new ElevateSettingsSection( __( 'Extras', 'elevate-seo' ), __( 'You can restart the initial configuration wizard or reset all settings here', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingButton( 'show_wizard_again', __( 'Configuration helper', 'elevate-seo' ), false, __( 'Launch', 'elevate-seo' ) ) );
$section->add_setting( new ElevateSettingButton( 'reset_all_settings', __( 'Reset settings', 'elevate-seo' ), __( 'This will reset Elevate to its initial default settings and remove the sitemap', 'elevate-seo' ), __( 'Reset', 'elevate-seo' ) ) );
$page->add_section( $section );

$section = new ElevateSettingsSection( __( 'Debugging', 'elevate-seo' ), __( 'For when your website is feeling blue', 'elevate-seo' ) );

$section->add_setting( new ElevateSettingCheckbox( 'enable_debug_log', __( 'Enabled debug log', 'elevate-seo' ), __( 'Turning this option on will cause a debug log to be written to the server to help diagnose a problem.', 'elevate-seo' ) ) );

$page->add_section( $section );

$page->render();

