<label for="elevate_<?php echo $this->setting_name; ?>"><?php echo $this->display_text; ?></label>
<textarea name="elevate_<?php echo $this->setting_name; ?>" id="elevate_<?php echo $this->setting_name; ?>"><?php if ( $this->current_value ) echo $this->current_value; ?></textarea>

<?php include( 'tooltip.php' ); ?>