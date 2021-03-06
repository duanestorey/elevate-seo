<?php $has_tokens = ElevatePlugin::get()->has_google_tokens(); ?>
<section class="elevate">
	<div class="header">
		<h2><?php _e( 'Elevate SEO - Dashboard', 'elevate-seo' ); ?></h2>
		<div class="about"><?php echo sprintf( __( 'lovingly maintained by %sDuane Storey%s &amp; %sElevateWP.io%s', 'elevate-seo' ), '<a href="https://twitter.com/duanestorey" target="_blank">', '</a>', '<a href="https://elevatewp.io" target="_blank">', '</a>' ); ?></div>
		<div class="clear"></div>
	</div>

	<div id="dashboard">  
		<div class="has-data" style="display: block;"> 
			<div class="big-data">
				<div class="top-grid">	
					<div class="item item-1">
						<img class="spin" src="<?php echo ELEVATE_PLUGIN_URL; ?>/dist/images/dash-spinner.svg" alt="" style="display: none;" />
						<h2><?php _e( 'Speed', 'elevate-seo' ); ?> <a href="https://developers.google.com/speed/pagespeed/insights/?url=<?php echo urlencode( home_url() ); ?>" target="_blank"><i class="fa fa-external-link-square"> </i></a></h2>

						<div class="col-1">
							<span class="mobile-speed">-</span>
							<i class="fa fa-chevron-up"> </i><i class="fa fa-chevron-down"> </i>
							<h4><?php _e( 'Mobile', 'elevate-seo' ); ?></h4>
						</div>

						<div class="col-2">	
							<span class="desktop-speed">-</span>
							<i class="fa fa-chevron-up"> </i><i class="fa fa-chevron-down"> </i>
							<h4><?php _e( 'Desktop', 'elevate-seo' ); ?></h4>
						</div>						
					</div>
					
					<div class="item item-2">
						<img class="spin" src="<?php echo ELEVATE_PLUGIN_URL; ?>/dist/images/dash-spinner.svg" alt="" style="display: none;" />
						<h2><?php _e( 'Search', 'elevate-seo' ); ?> <?php if ( $has_tokens ) { ?><a href="https://search.google.com/search-console/performance/search-analytics?resource_id=<?php echo urlencode( home_url() ); ?>" target="_blank"><i class="fa fa-external-link-square"> </i></a><?php } else { ?><i title="<?php _e( 'Requires Authentication', 'elevate-seo' ); ?>" class="fa fa-warning"> </i><?php } ?></h2>

						<div class="col-1">
							<span class="analytics-views">-</span>
							<i class="fa fa-chevron-up"> </i><i class="fa fa-chevron-down"> </i>
							<h4><?php _e( 'Views', 'elevate-seo' ); ?></h4>
						</div>	

						<div class="col-2">
							<span class="analytics-visits">-</span>
							<i class="fa fa-chevron-up"> </i><i class="fa fa-chevron-down"> </i>
							<h4><?php _e( 'Visitors', 'elevate-seo' ); ?></h4>
						</div>							

						<div class="col-3">
							<span class="impressions">-</span>
							<i class="fa fa-chevron-up"> </i><i class="fa fa-chevron-down"> </i>
							<h4><?php _e( 'Impressions', 'elevate-seo' ); ?></h4>
						</div>

						<div class="col-4">
							<span class="clicks">-</span>
							<i class="fa fa-chevron-up"> </i><i class="fa fa-chevron-down"> </i>
							<h4><?php _e( 'Clicks', 'elevate-seo' ); ?></h4>
						</div>							
	
						<div class="col-5">
							<span class="click-rate">-</span>
							<i class="fa fa-chevron-up"> </i><i class="fa fa-chevron-down"> </i>
							<h4><?php _e( 'CTR', 'elevate-seo' ); ?></h4>
						</div>						
					</div>
					<div class="item visits">
						<h2><?php _e( 'Visitors &amp Page Views', 'elevate-seo' ); ?></h2>
						<h4><?php _e( 'From Google Analytics', 'elevate-seo' ); ?></h4>
						<section class="graph">
							<p class="waiting"><?php _e( 'Loading data...', 'elevate-seo' ); ?></p>
							<canvas id="visits-chart"></canvas>
						</section>														
					</div>	
					<div class="item search-stats">
						<h2><?php _e( 'Organic Traffic', 'elevate-seo' ); ?></h2>
						<h4><?php _e( '7-Day Moving Average', 'elevate-seo' ); ?></h4>
						<section class="graph">
							<p class="waiting"><?php _e( 'Loading data...', 'elevate-seo' ); ?></p>
							<canvas id="search-chart"></canvas>
						</section>															
					</div>										
					<div class="item speed">
						<h2><?php _e( 'Speed Results', 'elevate-seo' ); ?></h2>
						<h4><?php _e( 'From Page Speed Insights', 'elevate-seo' ); ?></h4>
						<section class="graph">
							<p class="waiting"><?php _e( 'Loading data...', 'elevate-seo' ); ?></p>
							<canvas id="speed-chart"></canvas>
						</section>													
					</div>	
					<div class="info google-info grow">
						<div class="wrap"><i class="fa fa-google"> </i></div>
						<h4><?php _e( 'Google Status', 'elevate-seo' ); ?></h4>

						<ul>
							<li><em><?php _e( 'OAuth Status:', 'elevate-seo' ); ?></em>
								<span>
								<?php if ( $has_tokens ) { ?>
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
					<div class="info plugin-info grow">
						<div class="wrap"><i class="fa fa-wrench"> </i></div>
						<h4><?php _e( 'Plugin Info', 'elevate-seo' ); ?></h4>

						<ul>
							<li><em><?php _e( 'Version:', 'elevate-seo' ); ?></em><span><?php echo ELEVATE_PLUGIN_VERSION; ?></span></li>
							<li><em><?php _e( 'Last Modified:', 'elevate-seo' ); ?></em><span><?php echo date( 'F jS, Y', filemtime( ELEVATE_PLUGIN_DIR . '/dist/php/cache-bust.php' ) ); ?></span></li>
							<li><em><?php _e( 'Release Notes:', 'elevate-seo' ); ?></em><span><a target="_blank" href="<?php echo ELEVATE_PLUGIN_URL; ?>/readme.txt"><?php _e( 'View', 'elevate-seo' ); ?></a></span></li>
						</ul>
					</div>
					<div class="info sitemap-info grow">
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
	</div>
</section>