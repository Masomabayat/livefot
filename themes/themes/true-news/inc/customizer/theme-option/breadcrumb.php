<?php
/**
* Breadcrumb
*
* @package True News
*/

$true_news_default = true_news_get_default_theme_options();

// Breadcrumb Section.
$wp_customize->add_section( 'breadcrumb_section',
	array(
	'title'      => esc_html__( 'Breadcrumb Settings', 'true-news' ),
	'priority'   => 15,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Breadcrumb Layout.
$wp_customize->add_setting( 'breadcrumb_layout',
	array(
	'default'           => $true_news_default['breadcrumb_layout'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'true_news_sanitize_select',
	)
);
$wp_customize->add_control( 'breadcrumb_layout',
	array(
	'label'       => esc_html__( 'Breadcrumb Layout', 'true-news' ),
	'description' => sprintf( esc_html__( 'Advanced: Requires %1$sBreadcrumb NavXT%2$s plugin.', 'true-news' ), '<a href="https://wordpress.org/plugins/breadcrumb-navxt/" target="_blank">','</a>' ),
	'section'     => 'breadcrumb_section',
	'type'        => 'select',
	'choices'               => array(
		'disable' => esc_html__( 'Disabled', 'true-news' ),
		'simple' => esc_html__( 'Simple', 'true-news' ),
		'advanced' => esc_html__( 'Advanced', 'true-news' ),
	    ),
	'priority'    => 10,
	)
);