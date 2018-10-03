<label for="elevate_<?php echo $this->setting_name; ?>"><?php echo $this->display_text; ?></label>
<input type="checkbox" name="elevate_<?php echo $this->setting_name; ?>_cb" id="elevate_<?php echo $this->setting_name; ?>" <?php if ( $this->current_value ) echo ' checked'; ?> /> 
<input type="hidden" name="elevate_<?php echo $this->setting_name; ?>" id="elevate_<?php echo $this->setting_name; ?>_cb" value="%is_check%" /> 

<?php include( 'tooltip.php' ); ?>