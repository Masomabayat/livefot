<?php
/**
* Single Post Options.
*
* @package True News
*/

$true_news_default = true_news_get_default_theme_options();

// Single Post Section.
$wp_customize->add_section( 'single_post_setting',
	array(
	'title'      => esc_html__( 'Single Post Settings', 'true-news' ),
	'priority'   => 35,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Related Posts Enable Disable.
$wp_customize->add_setting('ed_related_post',
    array(
        'default' => $true_news_default['ed_related_post'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_related_post',
    array(
        'label' => esc_html__('Enable Related Posts', 'true-news'),
        'section' => 'single_post_setting',
        'type' => 'checkbox',
    )
);

// Related Posts Section Title.
$wp_customize->add_setting( 'related_post_title',
    array(
    'default'           => $true_news_default['related_post_title'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control( 'related_post_title',
    array(
    'label'    => esc_html__( 'Related Posts Section Title', 'true-news' ),
    'section'  => 'single_post_setting',
    'type'     => 'text',
    )
);


$wp_customize->add_setting('ed_toggle_comment',
    array(
        'default' => $true_news_default['ed_toggle_comment'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_toggle_comment',
    array(
        'label' => esc_html__('Enable Collapse on Comment Box', 'true-news'),
        'section' => 'single_post_setting',
        'type' => 'checkbox',
    )
);

// Related Posts Section Title.
$wp_customize->add_setting( 'twp_comment_toggle_button_title',
    array(
    'default'           => $true_news_default['twp_comment_toggle_button_title'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control( 'twp_comment_toggle_button_title',
    array(
    'label'    => esc_html__( 'Comment Toggle Button Label', 'true-news' ),
    'section'  => 'single_post_setting',
    'type'     => 'text',
    'active_callback' => 'true_news_comment_toggle_button_title_ac',
    )
);

$wp_customize->add_setting('twp_navigation_type',
    array(
        'default' => $true_news_default['twp_navigation_type'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('twp_navigation_type',
    array(
        'label' => esc_html__('Single Post Navigation Type', 'true-news'),
        'section' => 'single_post_setting',
        'type' => 'select',
        'choices' => array('no-navigation' => esc_html__('Disable Navigation','true-news' ),'norma-navigation' => esc_html__('Next Previous Navigation','true-news' ),'ajax-next-post-load' => esc_html__('Ajax Load Next 3 Posts Contents','true-news' ) ),
    )
);