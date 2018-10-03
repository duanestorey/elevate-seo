<label><?php echo $this->display_text; ?></label>
	<div class="radio-inside">
	<?php foreach( $this->values as $key => $value ) { ?>
	 <label class="secondary"><input type="radio" name="elevate_<?php echo $this->setting_name; ?>" id="elevate_<?php echo $this->setting_name; ?>" value="<?php echo $key; ?>" <?php if ( $this->current_value == $key ) echo ' checked'; ?>><?php echo $value; ?></label>
	<?php } ?>
	</div>
