<?php
/**
 * FreeNews Main Banner
 *
 * @package freenews
 */

/**
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */

$wp_customize->add_setting( 'select_main_banner_category', array(
    'default' => '',
    'sanitize_callback' => 'freenews_sanitize_select',
));
$wp_customize->add_control( 'select_main_banner_category', array(
    'priority'=>10,
    'label' => esc_html__('Select Main Banner', 'freenews'),
    'section' => 'freenews_main_banner_section',
    'type' => 'select',
    'choices'   =>  freenews_cat_list()
));