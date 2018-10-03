<?php

class ElevateSetting {
	var $setting_name;
	var $setting_type;
	var $setting_tooltip;
	var $current_value;

	public function __construct( $setting_type, $setting_name, $setting_tooltip, $is_real_setting = true ) {
		$this->setting_type = $setting_type;
		$this->setting_name = $setting_name;
		$this->setting_tooltip = $setting_tooltip;

		if ( $is_real_setting ) {
			$this->current_value = $this->_get_setting( $this->setting_name );	
		}
	}

	public function render() {
		$settings_file = ELEVATE_PLUGIN_DIR . '/admin/settings/' . $this->setting_type . '.php';
		if ( file_exists( $settings_file ) ) {
			include( $settings_file );
		}
	}

	private function _get_setting( $name ) {
		global $elevate_plugin;

		if ( isset( $elevate_plugin->settings->{$name} ) ) {
			return $elevate_plugin->settings->{$name};	
		} else {
			var_dump( debug_backtrace() );
			die( 'Unable to find setting ' . $name );
		}	
	}
}

class ElevateSettingCheckbox extends ElevateSetting {
	var $display_text;

	public function __construct( $setting_name, $display_text, $tooltip ) {
		parent::__construct( 'checkbox', $setting_name, $tooltip );

		$this->display_text = $display_text;
	}
}

class ElevateSettingUpload extends ElevateSetting {
	var $display_text;

	public function __construct( $setting_name, $display_text, $tooltip ) {
		parent::__construct( 'upload', $setting_name, $tooltip );

		$this->display_text = $display_text;
	}
}

class ElevateSettingText extends ElevateSetting {
	var $display_text;
	var $placeholder;

	public function __construct( $setting_name, $display_text, $tooltip, $placeholder = false ) {
		parent::__construct( 'text', $setting_name, $tooltip );

		$this->display_text = $display_text;
		$this->placeholder = $placeholder;
	}
}

class ElevateSettingTextArea extends ElevateSetting {
	var $display_text;
	var $placeholder;

	public function __construct( $setting_name, $display_text, $tooltip ) {
		parent::__construct( 'textarea', $setting_name, $tooltip );

		$this->display_text = $display_text;
	}
}

class ElevateSettingList extends ElevateSetting {
	var $values;	
	var $display_text;

	public function __construct( $setting_name, $display_text, $tooltip, $values ) {
		parent::__construct( 'select', $setting_name, $tooltip );

		$this->display_text = $display_text;
		$this->values = $values;
	}
}

class ElevateSettingSpacer extends ElevateSetting {
	public function __construct() {
		parent::__construct( 'spacer', '', '', false );
	}
}

class ElevateSettingRadio extends ElevateSetting {
	var $values;	
	var $display_text;

	public function __construct( $setting_name, $display_text, $tooltip, $values ) {
		parent::__construct( 'radio', $setting_name, $tooltip );

		$this->display_text = $display_text;
		$this->values = $values;
	}
}

class ElevateSettingButton extends ElevateSetting {
	var $display_text;
	var $button_text;

	public function __construct( $setting_name, $display_text, $tooltip, $button_text ) {
		parent::__construct( 'clickable-button', $setting_name, $tooltip, false );

		$this->display_text = $display_text;
		$this->button_text = $button_text;
	}
}