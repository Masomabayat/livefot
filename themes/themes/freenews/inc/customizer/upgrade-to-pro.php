<?php
/**
 * FreeNews Theme Customizer
 *
 * @package freenews
 */

use WPTRT\Customize\Section\Button;

add_action( 'customize_register', function( $manager ) {

	$manager->register_section_type( Button::class );

	$manager->add_section(
		new Button( $manager, 'freenews_pro', [
			'title'       => __( 'FreeNews Pro', 'freenews' ),
			'priority'    => 1,
			'button_text' => __( 'Upgrade To Pro', 'freenews' ),
			'button_url'  => 'https://themespiral.com/themes/freenews/'
		] )
	);

} );

// Load the JS and CSS.
add_action( 'customize_controls_enqueue_scripts', function() {

	$version = wp_get_theme()->get( 'Version' );

	wp_enqueue_script(
		'freenews-customize-section-button',
		get_theme_file_uri( 'vendor/wptrt/customize-section-button/public/js/customize-controls.js' ),
		[ 'customize-controls' ],
		$version,
		true
	);

	wp_enqueue_style(
		'freenews-customize-section-button',
		get_theme_file_uri( 'vendor/wptrt/customize-section-button/public/css/customize-controls.css' ),
		[ 'customize-controls' ],
 		$version
	);

} );