<label for="elevate_<?php echo $this->setting_name; ?>"><?php echo $this->display_text; ?></label>
<select name="elevate_<?php echo $this->setting_name; ?>" id="elevate_<?php echo $this->setting_name; ?>">
	<?php foreach( $this->values as $key => $value ) { ?>
	<option value="<?php echo $key; ?>"<?php if ( $key == $this->current_value ) echo ' selected="selected"'; ?>><?php echo $value; ?></option>
	<?php } ?>
</select>

<?php include( 'tooltip.php' ); ?>