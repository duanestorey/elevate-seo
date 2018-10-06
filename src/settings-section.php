<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */

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