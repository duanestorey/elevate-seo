<?php

/**
 *  Copyright 2018 by Duane Storey <duanestorey@gmail.com>
 *
 *  Licensed under GNU General Public License 3.0 or later. 
 *  Some rights reserved. See COPYING, AUTHORS.
 *
 *  @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */


class ElevateSettingsPage {
	var $page_name;
	var $sections;

	public function __construct( $page_name ) {
		$this->page_name = $page_name;

		$this->sections = array();
	}

	public function render() {
		echo '<section class="elevate">';
		echo '	<div class="header">';
		echo '		<h2>Elevate - ' . $this->page_name . '</h2>';
		echo '		<div class="about">' . sprintf( __( 'lovingly maintained by %sDuane Storey%s &amp; %sElevateWP.io%s', 'elevate-seo' ), '<a href="https://twitter.com/duanestorey">', '</a>', '<a href="https://elevatewp.io">', '</a>' ) . '</div>';
		echo '		<div class="clearfix"></div>';
		echo '	</div>';

		echo '	<div class="settings-area">';
		echo '		<form method="POST" action="' . add_query_arg( array( 'elevate-action' => 'save-settings' ), $_SERVER[ 'REQUEST_URI' ] ) . '">';

		foreach( $this->sections as $section ) {
			echo '			<div class="settings-section">';
			echo '				<h4>' . $section->section_name . '</h4>';
			echo '				<h5>' . $section->section_desc . '</h5>';

			$section->render();

			echo '			</div>';
		}

		echo '			<input class="button button-primary" type="submit" value="' . __( 'Save Settings', 'elevate-seo' ) . '" name="elevate-submit"></input>';
		echo '		</form>';
		echo '	</div>';
		echo '</section>';
	}

	public function add_section( $section ) {
		$this->sections[] = $section;
	}
}