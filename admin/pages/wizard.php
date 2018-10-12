<section class="elevate">
	<div class="bg-wrap">
		<div class="header">
			<h2>Elevate - <em><?php _e( 'Get Started', 'elevate-seo' ); ?></em></h2>
			<div class="clear"></div>
		</div>
		<div id="elevate-wizard" style="display: none;">
		    <h3>Intro</h3>
		    <div class="hero"></div>
		    <section class="intro">
		    	<h3><?php _e( 'Welcome to Elevate SEO', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( 'We just need a little bit of information to get you up and running' , 'elevate-seo' ); ?></h4>

		    	<i class="fa fa-chevron-up"> </i>

		        <p><?php _e( 'This wizard will guide you through the basic configuration steps needed to enhance your site and starting using the full features of the Elevate  plugin for WordPress.', 'elevate-seo' ); ?></p>
		    </section>			    		    	    
		    <h3><?php _e( 'Basics', 'elevate-seo' ); ?></h3>
		    <section class="basics">	
		    	<h3><?php _e( 'Configure Your Site', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( 'The following settings will help us set your site up properly', 'elevate-seo' ); ?></h4>
		        <form>
		        	<img src="<?php echo ELEVATE_PLUGIN_URL; ?>/dist/images/map.png" />
		        	<label><?php _e( 'Language', 'elevate-seo' ); ?></label>
		       		<select name="elevate_wizard_language" id="elevate_wizard_language">
		       			<?php $options = ElevatePlugin::get_supported_languages(); ?>
		       			<?php foreach( $options as $key => $value ) { ?>
		       				<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
		       			<?php } ?>
		       		</select>
		        </form>
		    </section>	 
		    <h3><?php _e( 'Name', 'elevate-seo' ); ?></h3>
		    <section class="site-name">	
		    	<h3><?php _e( 'Configure Your Site\'s Name', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( "It's time to start branding your search results", "elevate-seo" ); ?></h4>
		    	<p><?php _e( 'Your Site Name reflects the primary branding of your website or business.  For example, if you had an autobody repair facility called "Joe\'s Autobody", you would set your Site Name as that.', 'elevate-seo' ); ?></p>
		        <form>
		       		<label><?php _e( 'Site Name', 'elevate-seo' ); ?></label>
		       		<input type="text" name="elevate_site_name" id="elevate_site_name" value="<?php echo esc_attr( ElevatePlugin::get_one_setting( 'site_name' ) ); ?>" />
		        </form>
		    </section>			
		    <h3><?php _e( 'Title', 'elevate-seo' ); ?></h3>
		    <section class="home-title">	
		    	<h3><?php _e( 'Configure Your Search Title', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( "Set the title that search engines see for your main home page", "elevate-seo" ); ?></h4>
		    	<p><?php _e( 'Your Search Title is typically set to be your Site Name followed by a keyword-rich description of the information or services you or your website provide.', 'elevate-seo' ); ?></p>
		        <form>
		       		<label><?php _e( 'Home Search Title', 'elevate-seo' ); ?></label>
		       		<input type="text" name="elevate_home_title" id="elevate_home_title" value="<?php echo esc_attr( ElevatePlugin::get_one_setting( 'home_title' ) ); ?>" />
		        </form>
		    </section>			        
		    <h3><?php _e( 'Description', 'elevate-seo' ); ?></h3>
		    <section class="description">	
		    	<h3><?php _e( 'Configure Your Search Description', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( "Set the description that search engines see for your main home page", "elevate-seo" ); ?></h4>
		    	<p><?php _e( 'Your Search Description should include a few sentences about the primary purpose of your website.', 'elevate-seo' ); ?></p>
		        <form>
		       		<label><?php _e( 'Home Search Description', 'elevate-seo' ); ?></label>
		       		<textarea name="elevate_site_description" id="elevate_site_description"><?php echo esc_textarea( ElevatePlugin::get_one_setting( 'home_desc' ) ); ?></textarea>
		        </form>
		    </section>	
		    <h3><?php _e( 'Branding', 'elevate-seo' ); ?></h3>
		    <section class="branding">	
		    	<h3><?php _e( 'Sharing Image', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( "Choose how others see your website", "elevate-seo" ); ?></h4>
		    	<p><?php echo sprintf( __( 'Select the default image that appears when a person shares your content on social media. If you do not have one, take a look at %sUnsplash.com%s for images you can use for free.', 'elevate-seo' ), '<a href="http://unsplash.com" target="_blank">', '</a>' ); ?></p>
		    	<div id="wpwrap"></div>

		    	<button class="upload" data-name="facebook_default_image"><?php _e( "Upload Image", "elevate-seo" ); ?></button>
		    	<div class="upload_facebook_default_image">
		    		<input name="elevate_facebook_default_image" type="text" id="elevate_facebook_default_image" value="" />
		    		<a data-name="facebook_default_image" class="link" href="#" target="_blank"><div class="image-wrapper"></div></a>
		    	</div>

		    	<button id="facebook_default_image_reset" class="remove" data-name="facebook_default_image" style="display: none;"><?php _e( 'Remove', 'elevate-seo' ); ?></button>
		    </section>			    
		    <h3><?php _e( 'Preview', 'elevate-seo' ); ?></h3>
		    <section class="preview">	
		    	<h3><?php _e( 'Take a Look', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( "See how your site will look in search results", "elevate-seo" ); ?></h4>
		    	<p><?php _e( 'The following is an example of how your site will be displayed in various search engines. If you are not satisfied with it, you can modify this information on the previous screens.', 'elevate-seo' ); ?></p>
		    	<div class="google-preview">
		    		<div class="image" id="google-image" style="display: none"></div>
					<div class="title" id="google-title" style="color: #1a0dab; font-size: 18px; font-weight: normal; font-family: arial;"><?php _e( 'Title', 'elevate-seo' ); ?></div>
					<a href="#" style="color: #006621; text-decoration: none; font-size: 14px; font-weight: normal;"><?php echo esc_url( home_url() ); ?></a>
					<div class="desc" id="google-desc" style="font-family: arial; color: rgb( 84, 84, 84 ); line-height: 1.4; font-size: 13px;"><?php _e( 'Description', 'elevate-seo' ); ?></div>
				</div>	
		    </section>		        	
		    <h3><?php _e( 'Twitter', 'elevate-seo' ); ?></h3>
		    <section class="twitter">	
		    	<h3><?php _e( 'Configure Twitter', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( "Help other people find you online", "elevate-seo" ); ?></h4>
		    	<p><i class="fa fa-twitter"> </i><?php _e( 'Any content that visitors share on Twitter can automatically be tagged with your Twitter username once configured below. This will help drive traffic to your Twitter account and your website.', 'elevate-seo' ); ?></p>
		        <form>
		       		<label><?php _e( 'Twitter Username', 'elevate-seo' ); ?></label>
		       		<input type="text" name="elevate_social_twitter_name" id="elevate_social_twitter_name" placeholder="MyTwitterName" value="<?php echo esc_attr( ElevatePlugin::get_one_setting( 'social_twitter_name' ) ); ?>" />
		        </form>		    	
		    </section>			    	    	
		    <?php if ( !ElevatePlugin::get()->has_google_tokens( false ) ) { ?>
		    <h3><?php _e( 'Google', 'elevate-seo' ); ?></h3>
		    <section class="authenticate">
		    	<h3><?php _e( 'Google Search Console', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( 'Authenticate with Google for enhanced functionality', 'elevate-seo' ); ?></h4>
		    	<p><?php _e( "Elevate can access your Search Console data, automatically configure Sitemaps, and also perform Google site verification if needed - these items are all recommended for the best Search Engine Optimization results for this site.", "elevate-seo" ); ?>
		    		
		    	<p><?php echo sprintf( __( 'Click the button below to authenticate with Google, or click Next to skip. You can revoke authorization at any time in the future by %svisiting Google%s.', 'elevate-seo' ), '<a target="_blank" href="https://myaccount.google.com/permissions?pli=1">', '</a>' ); ?></p>

		    	<a id="wizard-auth" class="button" href="<?php echo ElevateAPI::get_oauth_auth_url(); ?>"><?php _e( 'Authenticate', 'elevate-seo' ); ?></a>

		    	<div class="search_api">
		       		<input type="text" name="elevate_wizard_search_api" placeholder="Paste API Key Here" />
		       	</div>
		    </section> 
		    <?php } ?>
		    <?php if ( ElevatePlugin::get()->has_google_tokens( false ) ) { ?>
		    <h3><?php _e( 'Inspect', 'elevate-seo' ); ?></h3>
		    <section class="google-services">
		   		<h3><?php _e( 'Google Services', 'elevate-seo' ); ?></h3>
		   		<h4><?php _e( 'Determining which services to automatically configure', 'elevate-seo' ); ?></h4>

			   	<div class="not-centered">
			   	 	<div id="services-checking">
			   	 		<img src="<?php echo ELEVATE_PLUGIN_URL; ?>/dist/images/gears.svg" alt="AJAX loading gears" />
			   	 		<p><?php _e( 'Please wait while we examine the current configuration of your site and Google services. This will only take a few moments.', 'elevate-seo' ); ?></p>
			   	 	</div>
			   	 	<div id="services-missing" style="display: none;">
			   	 		<i class="fa fa-frown-o"> </i>
			   	 		<p><?php echo sprintf( __( "It seems you haven't signed up for a Google Analytics account yet - we recommend %ssigning up for an account%s so that Elevate can configure it for you. To proceed without setting up Analytics, click Next, otherwise refresh the page after creating your Google Analytics account.", "elevate-seo" ), '<a href="https://analytics.google.com/analytics/web/provision/?authuser=0#provision/SignUp/">', '</a>' ); ?></p>
			   	 	</div>
			   	 	<div id="services-found" style="display: none;">
			   	 		<i class="fa fa-smile-o"> </i>
			   	 		<p><?php _e( "Great news - Elevate has access to your Google accounts and can now automatically configure them.", "elevate-seo" ); ?><br /><?php _e( "Please choose which services to automatically configure on the next page.", "elevate-seo" ); ?></p>
			   	 	</div>		   	 	
			   	</div>
		    </section>
			<h3><?php _e( 'Configure', 'elevate-seo' ); ?></h3>
		    <section class="google-services-config">
		   		<h3><?php _e( 'Google Services', 'elevate-seo' ); ?></h3>
		   		<h4><?php _e( 'Choose which services to automatically configure', 'elevate-seo' ); ?></h4>

			   	<div class="not-centered">
			   	 	<div id="services-checked">
						   	<ul>
						   	 	<li>
						   			<div id="config-search-console">
						   	 		<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>
						   	 		<h2><?php _e( 'Configure Search Console', 'elevate-seo' ); ?></h2>
						   	 		
						   	 		<p class="text"><?php _e( 'Search console helps Google properly index your website traffic and can provide useful information about your page rankings.', 'elevate-seo' ); ?></p>
						   	 		</div>
						   	 	</li>
						   	 	<li>
						   	 		<div id="config-add-analytics">
						   	 		<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i><i class="fa fa-frown-o"> </i>
						   	 		<h2><?php _e( 'Create Analytics Property', 'elevate-seo' ); ?></h2>
						   	 		
						   	 		<p class="text"><?php _e( 'Google Analytics can provide valuable information about what visitors read and interact with on your website.', 'elevate-seo' ); ?></p>

						   	 		<p class="disabled-info"><i class="fa fa-exclamation-circle"> </i> <?php _e( 'Requires a Google Analytics account', 'elevate-seo' ); ?></p>
						   	 		</div>
						   	 	</li>
						   	 	<li>
						   	 		<div id="config-install-analytics">
						   	 		<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i><i class="fa fa-frown-o"> </i>
						   	 		<h2><?php _e( 'Install Analytics Code', 'elevate-seo' ); ?></h2>
						   	 		
						   	 		<p class="text"><?php _e( 'Automatically insert the Google Analytics tracking code on to each page of your website.', 'elevate-seo' ); ?></p>

						   	 		<p class="disabled-info"><i class="fa fa-exclamation-circle"> </i> <?php _e( 'Requires a Google Analytics account', 'elevate-seo' ); ?></p>
						   	 		</div>
						   	 	</li>
						   	</ul>
					</div>
			   	</div>
		    </section>	  	 
		    <h3><?php _e( 'Google', 'elevate-seo' ); ?></h3>
		    <section class="site-setup">
		      	<h3><?php _e( 'Configuring Your Site', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( 'Give us a few moments while we configure your site with Google', 'elevate-seo' ); ?></h4>
		    	<div id="pb"></div>
		    	<ul>
		    		<li class="generate-sitemap"><?php echo sprintf( __( 'Generating a new sitemap on this site %s', 'elevate-seo' ), '<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>' ); ?></li>		    	
		    		<li class="check-google"><?php echo sprintf( __( 'Checking that we can talk to Google %s', 'elevate-seo' ), '<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>' ); ?></li>
		    		<li class="check-sc-site"><?php echo sprintf( __( 'Looking for your site on Google Search Console %s', 'elevate-seo' ), '<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>' ); ?></li>
		    		<li class="add-sc-site"><?php echo sprintf( __( 'Adding your site to Google Search Console %s', 'elevate-seo' ), '<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>' ); ?></li>
		    		<li class="check-verification"><?php echo sprintf( __( 'Checking if your site is verified on Google %s', 'elevate-seo' ), '<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>' ); ?></li>
		    		<li class="verify-site"><?php echo sprintf( __( 'Verifying your site on Google %s', 'elevate-seo' ), '<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>' ); ?></li>
		    		<li class="check-sitemap"><?php echo sprintf( __( 'Checking to see if you have a sitemap configured on Google %s', 'elevate-seo' ), '<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>' ); ?></li>
		    		<li class="add-sitemap"><?php echo sprintf( __( 'Adding a new sitemap for your site on Google %s', 'elevate-seo' ), '<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>' ); ?></li>
		    		<li class="create-analytics-property"><?php echo sprintf( __( 'Create a new Google Analytics property for your site %s', 'elevate-seo' ), '<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>' ); ?></li>
		    		<li class="add-analytics-code"><?php echo sprintf( __( 'Adding Google Analytics code for this site %s', 'elevate-seo' ), '<i class="fa fa-check-circle"> </i><i class="fa fa-times-circle"> </i>' ); ?></li>
		    		<li class="complete"><?php _e( 'Automatic configuration of your site is complete', 'elevate-seo' ); ?></li>
		    	</ul>

		    	<div id="config-error" style="display: none">
		    		<h2><?php _e( 'Configuration Problem', 'elevate-seo' ); ?></h2>
		    		<p><?php _e( 'We were unable to automatically verify your site on Google Search Console; you will likely have to complete this step manually.', 'elevate-seo' ); ?></p>

		    		<button class="config-error_close"><?php _e( 'Close', 'elevate-seo' ); ?></button>
		    	</div>
		    </section>
		    <?php } ?>	    
		    <h3>Bing</h3>
		    <section class="bing">	
		    	<h3><?php _e( 'Configure Bing', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( "There's more than just Google out there", "elevate-seo" ); ?></h4>
		    	<p><?php _e( 'Submitting your site to Microsoft\'s Bing search engine helps more visitors find your website.<br />The steps to add your site on Bing are listed below.', 'elevate-seo' ); ?></p>
		        <form>
		        	<ol>
		        		<li><?php echo sprintf( __( '%sClick here%s to go to Bing\'s configuration page.  If you don\'t have an account on Bing, you should create one now', 'elevate-seo' ), '<a href="https://www.bing.com/webmaster/home/addsite?from=mysites&addurl=' . esc_url( home_url() ) . '" target="_blank">', '</a>' ); ?>
		        		</li>
		        		<li><?php echo sprintf( __( 'Your sitemap is located at %s%s%s. Copy the URL from here and paste it into the \'Add a sitemap\' field at Bing.', 'elevate-seo' ), '<strong>', esc_attr( ElevatePlugin::get()->get_sitemap_url() ), '</strong>' ); ?></li>
		        		<li><?php _e( 'Click "Add" on Bing to add your site and sitemap', 'elevate-seo' ); ?></li>
		        		<li><?php _e( 'Copy the authentication code from Bing and paste it in the box below.', 'elevate-seo' ); ?></li>
		        		<li><?php _e( 'If you are using a cache plugin, please empty your cache after pasting.', 'elevate-seo' ); ?></li>
		        	</ol>
		        	<br />
		        	<label>Authentication Code</label>
		       		<input type="text" name="elevate_bing_auth_code" id="elevate_bing_auth_code" placeholder="C6555C4716879EC5A2B121EE52018DCE" value="<?php echo esc_attr( ElevatePlugin::get_one_setting( 'bing_auth_code' ) ); ?>" />
		       		<p>Click 'Verify' on Bing to complete the process</p>
		        </form>		    	
		    </section>	
 			<?php if ( ElevateApache::is_apache_installed() ) { ?>
		    <h3><?php _e( 'Hosting', 'elevate-seo' ); ?></h3>	
		    <section class="speed">
		  		<h3><?php _e( 'Hosting', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( "Let's check the configuration of your website", 'elevate-seo' ); ?></h4>
		    	<span><?php _e( 'Server Capabilities', 'elevate-seo' ); ?></span>
		    	<ul>
		    		<?php if ( !ElevateApache::instance()->is_using_fpm() ) { ?>
			    		<?php if ( ElevateApache::apache_supports( 'mod_expires' ) ) { ?>
			    			<li class="good"><i class="fa fa-check"> </i><?php _e( 'Your website supports browser caching of resources', 'elevate-seo' ); ?></li>
			    		<?php } else { ?>
							<li class="problem"><i class="fa fa-times"> </i><?php _e( 'Your website does not support browser caching of resources', 'elevate-seo' ); ?></li>
			    		<?php } ?>

			    		<?php if ( ElevateApache::apache_supports( 'mod_deflate' ) ) { ?>
			    		<li class="good"><i class="fa fa-check"> </i><?php _e( 'Your website supports compressing resources in real-time', 'elevate-seo' ); ?></li>
			    		<?php } else { ?>
			    		<li class="problem"><i class="fa fa-times"> </i><?php _e( 'Your website does not support compressing resources in real-time', 'elevate-seo' ); ?></li>
			    		<?php } ?>
			    	<?php } else { ?>
			    		<li class="can-fix"><i class="fa fa-warning"> </i><?php _e( 'Unable to determine server abilities since PHP is running using FastCGI', 'elevate-seo' ); ?></li>
			    	<?php } ?>
		    	</ul>
		    	<span><?php _e( 'Website Configuration', 'elevate-seo' ); ?></span>
		    	<ul class="needs-fixing">
		    		<?php if ( ElevateApache::apache_supports( 'mod_expires' ) || ElevateApache::instance()->is_using_fpm() ) { ?>
		    			<?php if ( ElevateApache::instance()->htaccess_supports( 'mod_expires' ) ) { ?>
		    			<li class="good"><i class="fa fa-check"> </i><?php _e( 'Your website is configured for browser caching of resources', 'elevate-seo' ); ?></li>
		    			<?php } else { ?>
		    			<li class="good" style="display: none"><i class="fa fa-check"> </i><?php _e( 'Your website is configured for browser caching of resources', 'elevate-seo' ); ?></li>
		    			<li class="can-fix"><i class="fa fa-warning"> </i><?php _e( 'Your website is not configured for browser caching of resources', 'elevate-seo' ); ?></li>
		    			<?php } ?>
		    		<?php } else { ?>
		    		<li class="problem"><i class="fa fa-times"> </i><?php _e( 'Your website cannot be configured for browser caching of resources', 'elevate-seo' ); ?></li>
		    		<?php } ?>
		    		<?php if ( ElevateApache::apache_supports( 'mod_deflate' ) || ElevateApache::instance()->is_using_fpm() ) { ?>
		    			<?php if ( ElevateApache::instance()->htaccess_supports( 'mod_deflate' ) ) { ?>
		    			<li class="good"><i class="fa fa-check"> </i><?php _e( 'Your website is configured to compress resources in real-time', 'elevate-seo' ); ?></li>
		    			<?php } else { ?>
		    			<li class="good" style="display: none;"><i class="fa fa-check"> </i><?php _e( 'Your website is configured to compress resources in real-time', 'elevate-seo' ); ?></li>
		    			<li class="can-fix"><i class="fa fa-warning"> </i><?php _e( 'Your website is not configured to compress resources in real-time', 'elevate-seo' ); ?></li>
		    			<?php } ?>
		    		<?php } else { ?>
					<li class="problem"><i class="fa fa-times"> </i><?php _e( 'Your website cannot be configured to compress resources in real-time', 'elevate-seo' ); ?></li>
		    		<?php } ?>
		    	</ul>		    	
		    	<?php if ( ( ( ElevateApache::apache_supports( 'mod_expires' ) || ElevateApache::instance()->is_using_fpm() ) && !ElevateApache::instance()->htaccess_supports( 'mod_expires' ) ) || ( ( ElevateApache::apache_supports( 'mod_deflate' ) || ElevateApache::instance()->is_using_fpm() ) && !ElevateApache::instance()->htaccess_supports( 'mod_deflate' ) ) ) { ?>
		    		<a id="wizard-fix" class="button" href="#"><?php _e( 'Fix Issues', 'elevate-seo' ); ?></a>
		    		<p class="fix-notice" style="display: none"><?php _e( 'Changes have been made to your website\'s .htaccess file. If this causes issues, manually copy .htaccess-elevate.bak to .htaccess in your website\'s main directory to restore the old configuration', 'elevate-seo' ); ?></p>
		    	<?php } else { ?>
		    		<p><?php _e( 'Your website is optimally configured based on its abilities', 'elevate-seo' ); ?></p>
		    	<?php } ?>
		    </section>		    
		    <?php } ?>			    			    		  
		    <h3><?php _e( 'News', 'elevate-seo' ); ?></h3>
		    <section class="wizard-news">
		        <h3><?php _e( 'Get Timely Updates', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( "We'll email you when there's a new update", "elevate-seo" ); ?></h4>
		    	<p><?php _e( "We aim to release new versions of Elevate every week or two.  Enter your email address below to be notified by email when a new release is available.", "elevate-seo" ); ?></p>
		    	<form>
		    		<label for="elevate_wizard_email"><?php _e( 'Your Email Address', 'elevate-seo' ); ?></label>
			       	<input type="text" name="elevate_wizard_email" id="elevate_wizard_email" placeholder="myemail@somedomain.com" />
		    	</form>
		    	<p><?php _e( 'You can unsubscribe at any time via a link at the bottom of each email.', 'elevate-seo' ); ?><br />
		    	<!-- <?php _e( "If you would like to bypass this, simply click Next.", "elevate-seo" ); ?></p> -->
		    </section>	 
		    <h3><?php _e( "Done", "elevate-seo" ); ?></h3>
		    <section class="done">
		    	<h3><?php _e( 'Setup Complete', 'elevate-seo' ); ?></h3>
		    	<h4><?php _e( 'Elevate SEO has been configured for use on your website.', 'elevate-seo' ); ?></h4>
		    	<i class="fa fa-child"> </i>
		        <p><?php echo sprintf( __( 'Your website is properly configured. Please visit the individual settings pages to further configure Elevate SEO. For more information on using Elevate, please %svisit our knowledgebase%s.', 'elevate-seo' ), '<a href="https://elevatewp.io/knowledge-base/?utm_campaign=elevate-kb&utm_source=elevate&utm_medium=web" target="_blank">', '</a>' ); ?></p>
		    </section>	 	       
		</div>
	</div>
</section>