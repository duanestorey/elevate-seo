<div class="upload-setting upload_<?php echo $this->setting_name; ?>">
	<label><?php echo $this->display_text; ?></label>

	<div class="image-wrapper">
	<?php if ( $this->current_value ) { ?>
		<img src="<?php echo esc_url( trailingslashit( home_url() ) . $this->current_value ); ?>" class="<?php echo $this->setting_name; ?>" />
	<?php } ?>
	</div>
	<input type="text" name="elevate_<?php echo $this->setting_name; ?>" value="<?php if ( $this->current_value ) echo $this->current_value; ?>" readonly /> 
	<button class="upload" data-name="<?php echo $this->setting_name; ?>"><?php _e( 'Upload', 'elevate-seo' ); ?></button>
	<button class="upload-reset" id="<?php echo $this->setting_name; ?>_reset" data-name="<?php echo $this->setting_name; ?>"><?php _e( 'Remove', 'elevate-seo' ); ?></button>
</div>
