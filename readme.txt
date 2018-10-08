=== Elevate SEO ===
Contributors: lindellmedia, duanestorey
Tags: seo, pagespeed, google, sitemap, woocommerce
Requires at least: 4.8
Tested up to: 4.9.8
Requires PHP: 5.6
Stable tag: 1.0.8
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
 
Take your website to new heights with the cutting edge search engine optimization and performance monitoring suite for WordPress.
 
== Description ==

Elevate helps you with the management of your WordPress website from a search engine optimization perspective. It can automatically configure your site on Google for you via the installation wizard, or make use of your previously configured Google search console information. 

You will also be able to manage your search titles and descriptions while editing your content within WordPress.

All of the necessary social media and search console tasks are handled automatically for you, including the generation and submission of an XML sitemap, and the verification of your site on Google Search Console. 

Elevate will also insert the appropriate information for Facebook, Twitter and Linked to showcase your site or featured images when your content is shared, as well as your custom title and meta information, which you can adjust on a per-post basis to help improve your Google and Bing search rankings.

For more information, please visit the official [Elevate for WordPress](https://elevatewp.io/?utm_campaign=wporg&utm_medium=web&utm_source=wporg "Elevate for WordPress"). home page.

== Screenshots ==
 
1. Elevate dashboard
2. Configuration wizard
3. Post/Page search configuration pane
4. Social media preview window
5. Real-time feedback for search engine readiness
6. Ability to adjust search engine meta on a per-post basis
7. Example page in the administration panel

== Frequently Asked Questions ==
 
= Does Elevate talk to remote servers =  

Yes, in order to make authentication of Google OAuth more user-friendly, Elevate proxies the requests through elevatewp.io and relays the information back to the WordPress plugin. Without this, users are forced to authenticate using a more difficult method. All information, including the OAuth tokens, are deleted from our servers after a successful token authorization or renewal. Users can revoke this authorization at any time via the plugin administration panel, or on Google as well. For more information please read the privacy policy at https://elevatewp.io/privacy.html. 

= Aren't there other plugins that help with this? = 

Sure, there are a few other plugins, and some of them are quite popular.  But Elevate was designed from the ground up with the idea that search engine optimization shouldn't be difficult.  That's why Elevate purposefully and intelligently configures your site, and helps automatically populate search meta content when it can.

= Sounds great - what other features can I expect in the short term? =

We're doing heavier integration with Google in the next few releases, and will help bring some of that data back into the dashboard for you. In addition, Elevate will automatically fix many of the site issues that are routinely encountered.

== Installation ==

To install the plugin, simply add the plugin from the WordPress admin panel in the plugins section. Upon installing, you can configure the plugin using the Getting Started wizard, or configure the settings manually for the plugin.
 
== Changelog ==

= Version 1.1.0 (Oct 8, 2018) =

* Changed: Massively enhanced dashboard with SEO and performance data
* Changed: Reduced package size by reorganizing content
* Fixed: Issue with SEO information on WooCommerce store page
* Fixed: Issue with web preview and long titles

= Version 1.0.8 (Sep 29, 2018) =

* Fixed: Possible issues with Google refresh token
* Added: WooCommerce support

= Version 1.0.7 (Sep 28, 2018) =

* Added: Ability to cache bust CDN images
* Added: Structured data for posts
* Added: Breadcrumbs for pages
* Added: Integration with WooCommerce category pages
* Added: Ability to set search titles and meta data for taxonomy pages
* Fixed: Issue with image width and height for meta thumbnail images
* Fixed: When offline, dashboard shows 0s instead of endless spinners

= Version 1.0.6 (Sep 23, 2018) =

* Added: Cron job to update internal data
* Added: New Wizard pane to automatically configure Apache
* Added: Ability to utilize CDN for post/page content
* Fixed: Slow page load time when included image is a remote URL
* Fixed: Speed improvements due to localization detection 
* Fixed: Issue with %20 in AJAX data
* Changed: Modified AJAX loading in Dashboard to improve responsiveness
* Changed: Modified fonts to better match WP admin
* Changed: Updated Dashboard look and feel

= Version 1.0.5 =

* Added: width/height information for Facebook + Twitter meta data
* Added: Article information for posts
* Fixed: Modified permissions of uploaded files to 0644
* Fixed: Problem with locale in Open Graph information
* Changed: Modified dashboard appearance

= Version 1.0.4 =

* Fixed: Removed shortcodes from intelligent meta descriptions 

= Version 1.0.3 =

* Changed: Adjustments with dashboard image
* Fixed: filemtime warning on dashboard

= Version 1.0.1 =

* Fixed: Readme file

= Version 1.0 =

* Added: First release!

= Version 1.0b2 =

* Fixed: Home page description in wizard didn't properly save
* Added: Ability to add social media image via wizard
* Added: Enhanced Robots.txt generation
* Fixed: Incorrect add URL for Bing in the wizard
* Fixed: Issue with site wide social media images
* Added: Ability to preview social media sharing
* Added: Now gives suggestions for content writing
* Fixed: Issue with automatically filled meta descriptions and extra period.

* First beta release

== Upgrade Notice ==

First version.
