<?php
/**
 * WordPress.com-specific functions and definitions.
 *
 * This file is centrally included from `wp-content/mu-plugins/wpcom-theme-compat.php`.
 *
 * @package Twenty_Twenty
 */

/**
 * Adds support for wp.com-specific theme functions.
 *
 * @global array $themecolors
 */
function twentytwenty_wpcom_setup() {
	global $themecolors;

	// Set theme colors for third party services.
	if ( ! isset( $themecolors ) ) {
		$themecolors = array(
			'bg'     => 'f5efe0',
			'border' => 'dedfdf',
			'text'   => '000000',
			'link'   => 'd61347',
			'url'    => 'd61347',
		);
	}
}
add_action( 'after_setup_theme', 'twentytwenty_wpcom_setup' );

/**
 * Add setting for hiding page title on the homepage.
 */
function twentytwenty_wpcom_customize_update( $wp_customize ) {
	$wp_customize->add_setting( 'hide_front_page_title', array(
		'default'              => false,
		'type'                 => 'theme_mod',
		'transport'            => 'postMessage',
		'sanitize_callback'    => 'twentytwenty_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'hide_front_page_title', array(
		'label'		  => esc_html__( 'Hide Homepage Title', 'twentytwenty' ),
		'description' => esc_html__( 'Check to hide the page title, if your homepage is set to display a static page.', 'twentytwenty' ),
		'section'	  => 'static_front_page',
		'priority'	  => 10,
		'type'		  => 'checkbox',
		'settings'	  => 'hide_front_page_title',
	) );
}
add_action( 'customize_register', 'twentytwenty_wpcom_customize_update' );

/**
* Sanitize the checkbox.
*
* @param boolean $input.
*
* @return boolean true if is 1 or '1', false if anything else
*/
function twentytwenty_sanitize_checkbox( $input ) {
	if ( 1 == $input ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Bind JS handlers to instantly live-preview changes.
 */
function twentytwenty_wpcom_customize_preview_js() {
	wp_enqueue_script( 'twentytwenty_wpcom_customize_preview', get_theme_file_uri( '/inc/customize-preview-wpcom.js' ), array( 'customize-preview' ), '1.0', true );
}
add_action( 'customize_preview_init', 'twentytwenty_wpcom_customize_preview_js' );

/**
 * Enqueue our WP.com styles for front-end.
 * Loads after style.css so we can add overrides.
 */
function twentytwenty_wpcom_scripts() {
	wp_enqueue_style( 'twentytwenty-wpcom-style', get_template_directory_uri() . '/inc/style-wpcom.css', array( 'twentytwenty-style' ), '20201022' );
}
add_action( 'wp_enqueue_scripts', 'twentytwenty_wpcom_scripts' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function twentytwenty_wpcom_body_classes( $classes ) {

	$hide = get_theme_mod( 'hide_front_page_title', false );

	if ( true === $hide ) {
		$classes[] = 'hide-homepage-title';
	}

	$credit_option = get_option( 'footercredit' );

	if ( 'hidden' == $credit_option ) {
		$classes[] = 'hide-footer-credit';
	}

	return $classes;
}
add_filter( 'body_class', 'twentytwenty_wpcom_body_classes' );
