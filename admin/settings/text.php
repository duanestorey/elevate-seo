<label for="elevate_<?php echo $this->setting_name; ?>"><?php echo $this->display_text; ?></label>
<input type="text" name="elevate_<?php echo $this->setting_name; ?>" id="elevate_<?php echo $this->setting_name; ?>" <?php if ( $this->current_value ) echo ' value="' . $this->current_value . '"'; ?> placeholder="<?php echo $this->placeholder; ?>"/> 


<?php include( 'tooltip.php' ); ?>