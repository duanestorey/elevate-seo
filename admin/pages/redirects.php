<?php

$page = new ElevateSettingsPage( __( 'Redirects', 'elevate-seo' ) );

$section = new ElevateSettingsSection( __( 'General', 'elevate-seo' ), __( 'Choose how redirects are handled', 'elevate-seo' ) );
$section->add_setting( new ElevateSettingCheckbox( 'ssl_redirect', __( 'Redirect to SSL', 'elevate-seo' ), __( 'Can be used to force all non-SSL traffic to be 301 (permanently) redirected to SSL. This is best handled via the server, but this can be used for smaller sites.', 'elevate-seo' ) ) );
$section->add_setting( new ElevateSettingCheckbox( 'redirect_media', __( 'Redirect Media to Parent', 'elevate-seo' ), __( 'Will force direct links to image attachments to redirect back to the post or page where they were included.', 'elevate-seo' ) ) );
$page->add_section( $section );

$page->render();
