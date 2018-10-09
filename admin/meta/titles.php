<div class="elevate-title-meta">
	<?php if ( ElevatePlugin::is_editing_taxonomy() ) { ?>
	<h2><?php _e( 'Elevate Search Information', 'elevate-seo' ); ?></h2>
	<?php } ?>
	<?php if ( ElevatePlugin::get()->_has_separate_blog_page() && ElevatePlugin::get()->is_on_front_or_posts() ) { ?>
		<p><?php echo sprintf( __( 'You can configure the title and description for this page via the %sElevate SEO settings page%s.', 'elevate-seo' ), '<a href="' . admin_url( 'admin.php?page=elevate_general' ) . '">', '</a>' ); ?></p>
	<?php } else { ?>
		<label for="elevate_post_title"><?php _e( 'Search Title', 'elevate-seo' ); ?></label>
		<a href="#" class='reset' id="elevate_post_title_reset">Reset</a>

		<?php $meta_box_info = get_elevate_plugin()->get_saved_meta_box_info( $post_info->ID, isset( $post_info->is_term ) ? '1' : '0' ); ?>
		<?php $post_title = get_elevate_plugin()->get_placeholder_title( $post_info->post_title, $post_info->post_type != 'page' ); ?>
		<input type="text" name="elevate_post_title" id="elevate_post_title" value="<?php echo $meta_box_info->title; ?>" placeholder="<?php echo esc_attr( $post_title ); ?>" />

		<label for="elevate_post_description"><?php _e( 'Search Description', 'elevate-seo' ); ?></label>
		<a href="#" class='reset' id="elevate_post_description_reset">Reset</a>
		<?php
			$placeholder = __( 'Enter a one or two sentence summary of this content for search engines.', 'elevate-seo' );

			if ( ElevatePlugin::is_editing_taxonomy() ) {
				$placeholder = ElevatePlugin::get()->get_taxonomy_description();
		 	} else {
		 		if ( ElevatePlugin::get_one_setting( 'fill_empty_description' ) ) {
					$placeholder = ElevatePlugin::get()->get_intelligent_meta_desc( $post_info->ID );
				}
			} 
		?>
		<textarea name="elevate_post_description" id="elevate_post_description" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo $meta_box_info->desc; ?></textarea>

		<?php if ( ElevatePlugin::get_one_setting( 'insert_canonical' ) ) { ?>
			<label for="elevate_post_canonical"><?php _e( 'Canonical URL', 'elevate-seo' ); ?></label> <span class="tooltip" data-ot="<?php _e( 'If this post came from another site, put the original URL here.', 'elevate-seo' ); ?>" data-ot-tip-joint="bottom left"><i>?</i></span>
			<input type="text" name="elevate_post_canonical" id="elevate_post_canonical" value="<?php echo $meta_box_info->canonical; ?>" placeholder="<?php echo esc_url( ElevatePlugin::get()->get_draft_permalink( $post_info ) ); ?>" />
		<?php } ?>

		<?php if ( ElevatePlugin::get_one_setting( 'insert_language' ) ) { ?>
		<div class="language">
			<label for="elevate_post_canonical"><?php _e( 'Language', 'elevate-seo' ); ?></label>
			<select name="elevate_post_language">
				<?php $locales = get_elevate_plugin()->get_locale_listing(); ?>
				<?php foreach ( $locales as $key => $locale ) { ?>
					<option value="<?php echo esc_attr( $key ); ?>"<?php if ( $key == $meta_box_info->language ) echo ' selected'; ?>><?php echo esc_html( $locale ); ?></option>
				<?php } ?>
			</select>
			<span class="tooltip" data-ot="<?php _e( 'Select the language for this content.', 'elevate-seo' ); ?>" data-ot-tip-joint="bottom left"><i>?</i></span>
		</div>
		<?php } ?>	

		<?php if ( ElevatePlugin::get_one_setting( 'insert_robots' ) ) { ?>
		<div class="robots">
			<label for="elevate_post_canonical"><?php _e( 'Index status', 'elevate-seo' ); ?></label>
			<select name="elevate_post_robots">
				<?php $indicies = get_elevate_plugin()->get_index_follow_info(); ?>
				<?php foreach ( $indicies as $key => $index ) { ?>
					<option value="<?php echo esc_attr( $key ); ?>"<?php if ( $key == $meta_box_info->robots ) echo ' selected'; ?>><?php echo esc_html( $index ); ?></option>
				<?php } ?>
			</select>
			<span class="tooltip" data-ot="<?php _e( 'Select the indexing behaviour for this content.', 'elevate-seo' ); ?>" data-ot-tip-joint="bottom left"><i>?</i></span>
		</div>
		<?php } ?>				
	<?php } ?>
</div>
<div id="social_preview">
	<h2><?php _e( 'Web Preview', 'elevate-seo' ); ?></h2>
	<ul>
		<li><i class="fa fa-google active" data-tab="google-preview"> </i></li>
		<li><i class="fa fa-facebook-f" data-tab="facebook-preview"> </i></li>
		<li><i class="fa fa-twitter" data-tab="twitter-preview"> </i></li>
		<li><i class="fa fa-linkedin" data-tab="linkedin-preview"> </i></li>
	</ul>
	<div class="clear"></div>

	<div class="previews">
		<div class="google-preview">
			<p><?php echo sprintf( __( 'This is how your content will look on %s.', 'elevate-seo' ), 'Google' ); ?></p>
			<div class="wrapper">
				
				<div class="title google-title search-title" id="google-title" style="color: #1a0dab; font-size: 18px; font-weight: normal; font-family: arial; margin-bottom: 2px"><?php echo $post_title; ?></div>
				<a href="#" style="color: #006621; text-decoration: none; font-size: 14px; font-weight: normal; margin-bottom: 2px"><?php echo ElevatePlugin::get()->get_draft_permalink( $post_info ); ?></a>
				<div class="desc google-desc" id="google-desc" style="font-family: arial; color: rgb( 84, 84, 84 ); line-height: 1.4; font-size: 13px;"><?php if ( $meta_box_info->desc ) echo esc_html( $meta_box_info->desc ); else echo $placeholder; ?></div>
			</div>
		</div>

		<div class="twitter-preview<?php if ( ElevatePlugin::get_one_setting( 'twitter_card_image_size' ) != 'summary' ) echo ' large'; ?>" style="display: none;">
			<p><?php echo sprintf( __( 'This is how your content will look on %s.', 'elevate-seo' ), 'Twitter' ); ?></p>
			<div class="wrapper">
				
				<img src="" style="display: none" />

				<div class="twitter-wrap">
					<div class="title twitter-title search-title" id="twitter-title"><?php echo $post_title; ?></div>
					<div class="desc twitter-desc" id="twitter-desc"><?php if ( $meta_box_info->desc ) echo esc_html( $meta_box_info->desc ); else echo $placeholder; ?></div>			
					<?php 
						$url = parse_url( ElevatePlugin::get()->get_draft_permalink( $post_info ) );
					?>

					<div class="clear"></div>
					
					<div class="site_name"><?php echo esc_html( str_replace( 'www.', '', strtolower( $url[ 'host' ] ) ) ); ?></div>
				</div>
			</div>
		</div>
		<div class="facebook-preview" style="display: none;">
			<p><?php echo sprintf( __( 'This is how your content will look on %s.', 'elevate-seo' ), 'Facebook' ); ?></p>
			<div class="wrapper">
				
				<img src="" style="display: none" />

				<?php 
					$url = parse_url( ElevatePlugin::get()->get_draft_permalink( $post_info ) );
				?>
				<div class="fb-wrap">			
					<div class="site_name"><?php echo esc_html( $url[ 'host' ] ); ?></div>
					<div class="title facebook-title search-title" id="facebook-title"><?php echo $post_title; ?></div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<div class="linkedin-preview" style="display: none;">
			<p><?php echo sprintf( __( 'This is how your content will look on %s.', 'elevate-seo' ), 'LinkedIn' ); ?></p>
			<div class="wrapper">
			
				<div class="inner">
					<img src="" style="display: none" />
					
					<div class="box">
						<div class="title linkedin-title search-title" id="facebook-title"><?php echo $post_title; ?></div>
					
						<?php 
							$url = parse_url( ElevatePlugin::get()->get_draft_permalink( $post_info ) );
						?>
						<div class="site_name"><?php echo esc_html( $url[ 'host' ] ); ?></div>
					</div>
				</div>
			</div>
		</div>		
	</div>

	<button class="social_preview_close"><?php _e( 'Close', 'elevate-seo' ); ?></button>
</div>

