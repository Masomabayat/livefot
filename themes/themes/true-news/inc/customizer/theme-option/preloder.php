<?php
/**
* Preloader Setting.
*
* @package True News
*/

$true_news_default = true_news_get_default_theme_options();

$wp_customize->add_section( 'preloader_section',
	array(
	'title'      => esc_html__( 'Preloader Settings', 'true-news' ),
	'priority'   => 10,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Enable Disable Preloader.
$wp_customize->add_setting('ed_preloader',
    array(
        'default' => $true_news_default['ed_preloader'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_preloader',
    array(
        'label' => esc_html__('Enable Preloader', 'true-news'),
        'section' => 'preloader_section',
        'type' => 'checkbox',
    )
);

// Breadcrumb Layout.
$wp_customize->add_setting( 'twp_perloader_layout',
    array(
    'default'           => $true_news_default['twp_perloader_layout'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'true_news_sanitize_select',
    )
);
$wp_customize->add_control( 'twp_perloader_layout',
    array(
    'label'       => esc_html__( 'Preloader Type', 'true-news' ),
    'section'     => 'preloader_section',
    'type'        => 'select',
    'choices'               => array(
        'simple' => esc_html__( 'Simple Preloader', 'true-news' ),
        'advanced' => esc_html__( 'Advanced Preloader', 'true-news' ),
        ),
    )
);

// Breadcrumb Layout.
$wp_customize->add_setting( 'twp_perloader_title',
    array(
    'default'           => $true_news_default['twp_perloader_title'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'true_news_sanitize_select',
    )
);
$wp_customize->add_control( 'twp_perloader_title',
    array(
    'label'       => esc_html__( 'Preloader Content Title', 'true-news' ),
    'section'     => 'preloader_section',
    'type'        => 'text',
    'active_callback' => 'true_news_preloader_callback',
    )
);

$wp_customize->add_setting('ed_preloader_home_only',
    array(
        'default' => $true_news_default['ed_preloader_home_only'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_preloader_home_only',
    array(
        'label' => esc_html__('Enable Only On Home Page', 'true-news'),
        'section' => 'preloader_section',
        'type' => 'checkbox',
        'active_callback' => 'true_news_preloader_callback',
    )
);

$wp_customize->add_setting('ed_preloader_24_hour',
    array(
        'default' => $true_news_default['ed_preloader_24_hour'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_preloader_24_hour',
    array(
        'label' => esc_html__('Dismiss Preloader For 24 Hours', 'true-news'),
        'section' => 'preloader_section',
        'type' => 'checkbox',
        'active_callback' => 'true_news_preloader_callback',
    )
);