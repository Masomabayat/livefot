<?php
/**
 * Global Sidebar Layout Settings
 *
 * @package True News
 */

$true_news_default = true_news_get_default_theme_options();
$sidebar_layout = true_news_sidebar_layout();
// Pagination Section.
$wp_customize->add_section( 'true_news_global_sidebar_setting',
	array(
	'title'      => esc_html__( 'Sidebar Layout', 'true-news' ),
	'priority'   => 20,
	'capability' => 'edit_theme_options',
	'panel'		 => 'theme_option_panel',
	)
);

// Sidebar Layout Settings
$wp_customize->add_setting( 'true_news_global_sidebar_layout',
	array(
	'default'           => $true_news_default['true_news_global_sidebar_layout'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'true_news_global_sidebar_layout',
	array(
	'label'       => esc_html__( 'Archive Sidebar Layout', 'true-news' ),
	'section'     => 'true_news_global_sidebar_setting',
	'type'        => 'select',
	'choices'     => $sidebar_layout
	)
);

// Single Layout Settings
$wp_customize->add_setting( 'true_news_single_sidebar_layout',
	array(
	'default'           => $true_news_default['true_news_single_sidebar_layout'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'true_news_single_sidebar_layout',
	array(
	'label'       => esc_html__( 'Single Post Sidebar Layout', 'true-news' ),
	'section'     => 'true_news_global_sidebar_setting',
	'type'        => 'select',
	'choices'     => $sidebar_layout
	)
);