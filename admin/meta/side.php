<div class="side-box">
	<span class="desc">
		<span class="waiting" style="display: none;"><i class="fa fa-pencil"> </i><?php _e( 'Not enough content to analyze - continue writing.', 'elevate-seo' ); ?></span>
		<span class="good" style="display: none;"><i class="fa fa-check"> </i><?php _e( 'Your content looks good from a search and social media perspective.', 'elevate-seo' ); ?></span>
		<span class="warning" style="display: none;"><i class="fa fa-warning"> </i><?php _e( 'You may want to make a few search adjustments before publishing or saving.', 'elevate-seo' ); ?></span>
		<span class="bad" style="display: none;"><i class="fa fa-exclamation"> </i><?php _e( 'You have a few serious search issues that need addressing.', 'elevate-seo' ); ?></span>
	</span>

	<div style="text-align: right; clear: both">
		<a class="social_preview_open learn-more button"><?php _e( 'Web Preview', 'elevate-seo' ); ?></a>
		<a class="learn_more_open learn-more button"><?php _e( 'Learn More', 'elevate-seo' ); ?></a>
	</div>

	<div id="learn_more">
		<h2><?php _e( 'Learn More', 'elevate-seo' ); ?></h2>

		<p><?php _e( 'Content Analysis', 'elevate-seo' ); ?></p>

		<div class="clear"></div>

		<div class="lm-wrap">
			<div class="wrapper">
				<ul>
					<li class="good" id="elevate-all-good"><i class="fa fa-check"> </i><?php _e( 'Congratulations, everything looks great.', 'elevate-seo' ); ?></li>

					<li class="warning" id="elevate-title-long"><i class="fa fa-warning"> </i><?php echo sprintf( __( 'Your Search Title is a bit long - consider shortening it to %d characters or less so it displays properly in search engines.', 'elevate-seo' ), 70 ); ?></li>

					<li class="warning" id="elevate-title-short"><i class="fa fa-warning"> </i><?php echo sprintf( __( 'Your Search Title is a bit short - consider lengthening it to %d characters or more so it displays properly in search engines.', 'elevate-seo' ), 20 ); ?></li>

					<li class="warning" id="elevate-desc-long"><i class="fa fa-warning"> </i><?php echo sprintf( __( 'Your Search Description is a bit long - consider shortening it to %d characters or less so it displays properly in search engines.', 'elevate-seo' ), 300 ); ?></li>

					<li class="warning" id="elevate-desc-short"><i class="fa fa-warning"> </i><?php echo sprintf( __( 'Your Search Description is too short - consider lengthening it to between %d and %d characters so it displays properly in search engines.', 'elevate-seo' ), 50, 300 ); ?></li>

					<li class="critical" id="elevate-no-title"><i class="fa fa-exclamation"> </i><?php _e( 'You have not set a title for your content; consider adding a keyword-rich, descriptive title to help rank higher in search engines.', 'elevate-seo' ); ?></li>					
				</ul>
			</div>
		</div>

		<button class="learn_more_close"><?php _e( 'Close', 'elevate-seo' ); ?></button>
	</div>
</div>