<?php

class ElevateMeta {
	static function output_property( $property, $content ) {
		echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n"; 
	}

	static function output_property_url( $property, $content ) {
		echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_url( $content ) . '" />' . "\n"; 
	}	

	static function output_name( $property, $content ) {
		echo '<meta name="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n"; 
	}

	static function output_name_url( $property, $content ) {
		echo '<meta name="' . esc_attr( $property ) . '" content="' . esc_url( $content ) . '" />' . "\n"; 
	}		
}