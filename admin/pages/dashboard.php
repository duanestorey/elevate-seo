<section class="elevate">
	<div class="header">
		<h2><?php _e( 'Elevate SEO - Dashboard', 'elevate-seo' ); ?></h2>
		<div class="about"><?php echo sprintf( __( 'lovingly maintained by %sDuane Storey%s &amp; %sElevateWP.io%s', 'elevate-seo' ), '<a href="https://twitter.com/duanestorey">', '</a>', '<a href="https://elevatewp.io">', '</a>' ); ?></div>
		<div class="clear"></div>
	</div>

	<div id="dashboard">  
		<div class="has-data" style="display: block;"> 
		<!--
			<div class="big-data">
				<ul>
					<li class="speed">
						<h2>Speed</h2>
						<h3>Your website speed</h3>
						<div>
							<span>80%</span>
							<h4>Mobile Score</h4>
							
						</div>
						<div>
							<span>80%</span>
							<h4>Desktop Score</h4>
							
						</div>	
						<div>
							<span>100</span>
							<h4>Not Authorized</h4>
							
						</div>	
						<div>
							<span>100</span>
							<h4>Not Authorized</h4>
						</div>														
					</li>
					<li class="search-stats">
						<h2>Search</h2>
						<h3>Your website traffic</h3>
						<div>
							<span>100</span>
							<h4>Visitors</h4>
						</div>								
						<div>
							<span>80%</span>
							<h4>Impressions</h4>
							
						</div>
						<div>
							<span>80%</span>
							<h4>Clicks</h4>
							
						</div>	
						<div>
							<span>100</span>
							<h4>Average Position</h4>
							
						</div>													
					</li>
					<li class="crawl">
						<h2>Crawl</h2>
						<h3>Your website issues</h3>
						<div>
							<span>80%</span>
							<h4>Mobile Score</h4>
							
						</div>
						<div>
							<span>80%</span>
							<h4>Desktop Score</h4>
							
						</div>	
						<div>
							<span>100</span>
							<h4>Not Authorized</h4>
							
						</div>	
						<div>
							<span>100</span>
							<h4>Not Authorized</h4>
						</div>														
					</li>											
				</ul>
			</div>
			-->
			<div class="hero">
				<ul>
					<?php if ( ElevatePlugin::get()->has_google_tokens() ) { ?>
					<li class="grow">
						<a class="link" href="https://www.google.com/webmasters/tools/crawl-errors?hl=en&siteUrl=<?php echo urlencode( home_url() ); ?>" target="_blank">
						<span class="num crawl-errors"><img class="spin" src="<?php echo ELEVATE_PLUGIN_URL; ?>/dist/images/dash-spinner.svg" /></span>
						<span class="what"><?php _e( 'Crawl Errors', 'elevate-seo' ); ?></span>
						</a>
					</li>
					<?php } else { ?>
					<li class="grow disabled">
						<a href="<?php echo admin_url( 'admin.php?page=elevate_search' ); ?>" title="<?php _e( "Current disabled: requires Google OAuth authentication", "elevate-seo" ); ?>">
							<span class="num crawl-errors"><i class="fa fa-exclamation-triangle"> </i></span>
							<span class="what"><?php _e( 'Crawl Errors', 'elevate-seo' ); ?></span>
						</a>
					</li>
					<?php } ?>	
									
					<li class="grow">
						<a class="link" href="https://developers.google.com/speed/pagespeed/insights/?url=<?php echo urlencode( home_url() ); ?>" target="_blank">
							<span class="num desktop-speed"><img class="spin" src="<?php echo ELEVATE_PLUGIN_URL; ?>/dist/images/dash-spinner.svg" /></span>
							<span class="what"><?php _e( 'Desktop Speed', 'elevate-seo' ); ?></span>
						</a>
					</li>
					<li class="grow">
						<a class="link" href="https://developers.google.com/speed/pagespeed/insights/?url=<?php echo urlencode( home_url() ); ?>" target="_blank">
							<span class="num mobile-speed"><img class="spin" src="<?php echo ELEVATE_PLUGIN_URL; ?>/dist/images/dash-spinner.svg" /></span>
							<span class="what"><?php _e( 'Mobile Speed', 'elevate-seo' ); ?></span>
						</a>
					</li>				
					<?php if ( ElevatePlugin::get()->has_google_tokens() ) { ?>
					<li class="grow">
						<a class="link" href="https://www.google.com/webmasters/tools/search-analytics?hl=en&siteUrl=<?php echo urlencode( home_url() ); ?>" target="_blank">
							<span class="num clicks"><img class="spin" src="<?php echo ELEVATE_PLUGIN_URL; ?>/dist/images/dash-spinner.svg" /></span>
							<span class="what"><?php _e( 'Search Clicks', 'elevate-seo' ); ?></span>
						</a>
					</li>
					<?php } else { ?>
					<li class="grow disabled">
						<a href="<?php echo admin_url( 'admin.php?page=elevate_search' ); ?>" title="<?php _e( "Current disabled: requires Google OAuth authentication", "elevate-seo" ); ?>">
							<span class="num clicks"><i class="fa fa-exclamation-triangle"> </i></span>
							<span class="what"><?php _e( 'Search Clicks', 'elevate-seo' ); ?></span>
						</a>
					</li>
					<?php } ?>													
				</ul>
			</div>    
			<div class="flex">
				<?php if ( ElevatePlugin::get()->has_google_tokens() ) { ?>
				<a class="link" href="https://www.google.com/webmasters/tools/crawl-errors?hl=en&siteUrl=<?php echo urlencode( home_url() ); ?>" target="_blank">
				<div class="block-col2 crawl-info grow">
					<div class="wrap"><i class="fa fa-font"> </i></div>
					<h4><?php _e( 'Crawl Errors', 'elevate-seo' ); ?></h4>

					<ul>
						<li><em><?php _e( 'Not Found:', 'elevate-seo' ); ?></em><span class="not-found"></span></li>
						<li><em><?php _e( 'Not Authorized:', 'elevate-seo' ); ?></em><span class="not-auth"></span></li>
						<li><em><?php _e( 'Server Error:', 'elevate-seo' ); ?></em><span class="server-error"></span></li>
					</ul>
				</div>	
				</a>
				<?php } else { ?>
				<a href="<?php echo admin_url( 'admin.php?page=elevate_search' ); ?>" title="<?php _e( "Current disabled: requires Google OAuth authentication", "elevate-seo" ); ?>">
				<div class="block-col2 crawl-info grow disabled">
					<div class="wrap"><i class="fa fa-font"> </i></div>
					<h4><?php _e( 'Crawl Errors', 'elevate-seo' ); ?></h4>

					<ul>
						<li><i class="fa fa-exclamation-triangle"> </i></li>
						<li class="req"><?php _e( 'Requires Google OAuth Authentication', 'elevate-seo' ); ?></li>
						<li></li>
					</ul>
				</div>	
				</a>
				<?php } ?>
				<a class="link" href="https://developers.google.com/speed/pagespeed/insights/?url=<?php echo urlencode( home_url() ); ?>" target="_blank">
				<div class="block-col2 speed-info grow">
					<div class="wrap"><i class="fa fa-bolt"> </i></div>
					<h4><?php _e( 'Site Speed', 'elevate-seo' ); ?></h4>

					<ul>
						<li><em><?php _e( 'Average Speed:', 'elevate-seo' ); ?></em><span class="average-speed"></span></li>
						<li><em><?php _e( 'Desktop Size:', 'elevate-seo' ); ?></em><span class="desktop-size"></span></li>
						<li><em><?php _e( 'Mobile Size:', 'elevate-seo' ); ?></em><span class="mobile-size"></span></li>
					</ul>
				</div>		
				</a>	
				<?php if ( ElevatePlugin::get()->has_google_tokens() ) { ?>
				<a class="link" href="https://www.google.com/webmasters/tools/search-analytics?hl=en&siteUrl=<?php echo urlencode( home_url() ); ?>" target="_blank">
				<div class="block-col2 search-info grow">
					<div class="wrap"><i class="fa fa-search"> </i></div>
					<h4><?php _e( 'Search Statistics', 'elevate-seo' ); ?></h4>

					<ul>
						<li><em><?php _e( 'Impressions:', 'elevate-seo' ); ?></em><span class="impressions"></span></li>
						<li><em><?php _e( 'Click Rate:', 'elevate-seo' ); ?></em><span class="click-rate"></span></li>
						<li><em><?php _e( 'Average Position:', 'elevate-seo' ); ?></em><span class="position"></span></li>		
					</ul>
				</div>	
				</a>	
				<?php } else { ?>
				<a href="<?php echo admin_url( 'admin.php?page=elevate_search' ); ?>" title="<?php _e( "Current disabled: requires Google OAuth authentication", "elevate-seo" ); ?>">
				<div class="block-col2 search-info grow disabled">
					<div class="wrap"><i class="fa fa-search"> </i></div>
					<h4><?php _e( 'Search Statistics', 'elevate-seo' ); ?></h4>

					<ul>
						<li><i class="fa fa-exclamation-triangle"> </i></li>
						<li class="req"><?php _e( 'Requires Google OAuth Authentication', 'elevate-seo' ); ?></li>
						<li></li>
					</ul>
				</div>	
				</a>
				<?php } ?>
				<div class="block-col2 google-info grow">
					<div class="wrap"><i class="fa fa-google"> </i></div>
					<h4><?php _e( 'Google Status', 'elevate-seo' ); ?></h4>

					<ul>
						<li><em><?php _e( 'OAuth Status:', 'elevate-seo' ); ?></em>
							<span>
							<?php if ( get_elevate_plugin()->has_google_tokens() ) { ?>
							<?php _e( 'Authenticated', 'elevate-seo' ); ?>
							<?php } else { ?>
							<?php _e( 'Not Authenticated', 'elevate-seo' ); ?>
							<?php } ?>
							</span>
						</li>					
						<li><em><?php _e( 'Analytics:', 'elevate-seo' ); ?></em>
							<span class="analytics-installed" data-tracking="<?php _e( 'Tracking', 'elevate-seo' ); ?>" data-not-tracking="<?php _e( 'Not Tracking', 'elevate-seo' ); ?>">
							</span>
						</li>
						<li><em><?php _e( 'Site Verification:', 'elevate-seo' ); ?></em><span class="site-verify" data-verified="<?php _e( 'Verified', 'elevate-seo' ); ?>" data-not-verified="<?php _e( 'Not Verified', 'elevate-seo' ); ?>"></span></li>
					</ul>
				</div>												
				<div class="block-col2 plugin-info grow">
					<div class="wrap"><i class="fa fa-wrench"> </i></div>
					<h4><?php _e( 'Plugin Info', 'elevate-seo' ); ?></h4>

					<ul>
						<li><em><?php _e( 'Version:', 'elevate-seo' ); ?></em><span><?php echo ELEVATE_PLUGIN_VERSION; ?></span></li>
						<li><em><?php _e( 'Last Modified:', 'elevate-seo' ); ?></em><span><?php echo date( 'F jS, Y', filemtime( ELEVATE_PLUGIN_DIR . '/elevate-seo.php' ) ); ?></span></li>
						<li><em><?php _e( 'Release Notes:', 'elevate-seo' ); ?></em><span><a target="_blank" href="<?php echo ELEVATE_PLUGIN_URL; ?>/readme.txt"><?php _e( 'View', 'elevate-seo' ); ?></a></span></li>
					</ul>
				</div>
				<div class="block-col2 sitemap-info grow">
					<div class="wrap"><i class="fa fa-sitemap"> </i></div>
					<h4><?php _e( 'Sitemap Info', 'elevate-seo' ); ?></h4>

					<ul>
						<li><em><?php _e( 'Status:', 'elevate-seo' ); ?></em><span class="sitemap-generated" data-pending="<?php _e( 'Pending', 'elevate-seo' ); ?>" data-generating="<?php _e( 'Generating', 'elevate-seo' ); ?>" data-generated="<?php _e( 'Generated', 'elevate-seo' ); ?>" data-not-generated="<?php _e( 'Not Generated' ); ?>"></span></li>
						<li><em><?php _e( 'Last Modified:', 'elevate-seo' ); ?></em><span class="last-modified" data-none="<?php _e( 'N/A', 'elevate-seo' ); ?>"></span></li>
						<li><em><?php _e( 'Total Entries:', 'elevate-seo' ); ?></em><span class="sitemap-entries"></span></li>
					</ul>
				</div>			
			</div>							
		</div>
	</div>
</section>