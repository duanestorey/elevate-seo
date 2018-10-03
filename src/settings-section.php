<?php

class ElevateSettingsSection {
	var $settings;
	var $section_name;

	public function __construct( $section_name, $section_desc = '' ) {
		$this->section_name = $section_name;
		$this->section_desc = $section_desc;

		$this->settings = array();
	}

	public function render() {
		foreach( $this->settings as $setting ) {

			echo '<div class="elevate-setting ' . $setting->setting_type . '">';
			$setting->render();
			echo '</div>' . "\n";
		}
	}

	public function add_setting( $setting ) {
		$this->settings[] = $setting;
	}
}