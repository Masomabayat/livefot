<?php
/**
* Posts Settings
*
* @package True News
*/

$true_news_default = true_news_get_default_theme_options();

$wp_customize->add_section( 'posts_settings',
	array(
	'title'      => esc_html__( 'General Settings', 'true-news' ),
	'priority'   => 1,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Enable Disable Post.
$wp_customize->add_setting('hide_post_categories',
    array(
        'default' => $true_news_default['hide_post_categories'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('hide_post_categories',
    array(
        'label' => esc_html__('Hide Post Categories', 'true-news'),
        'section' => 'posts_settings',
        'type' => 'checkbox',
    )
);

// Enable Disable Post.
$wp_customize->add_setting('post_date_formate',
    array(
        'default' => $true_news_default['post_date_formate'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_select',
    )
);
$wp_customize->add_control('post_date_formate',
    array(
        'label' => esc_html__('Posted Date Fromate', 'true-news'),
        'section' => 'posts_settings',
        'type' => 'select',
        'choices'               => array(
            'default' => esc_html__( 'Apply Default Formate', 'true-news' ),
            'time-ago' => esc_html__( 'Apply Time Age Formate', 'true-news' ),
            ),
        )
);

$wp_customize->add_setting('twp_hide_post_author_avatar',
    array(
        'default' => $true_news_default['twp_hide_post_author_avatar'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('twp_hide_post_author_avatar',
    array(
        'label' => esc_html__('Hide Post Author Image', 'true-news'),
        'section' => 'posts_settings',
        'type' => 'checkbox',
    )
);

$wp_customize->add_setting('twp_hide_post_author',
    array(
        'default' => $true_news_default['twp_hide_post_author'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('twp_hide_post_author',
    array(
        'label' => esc_html__('Hide Post Author Name', 'true-news'),
        'section' => 'posts_settings',
        'type' => 'checkbox',
    )
);

$wp_customize->add_setting('twp_hide_post_date',
    array(
        'default' => $true_news_default['twp_hide_post_date'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('twp_hide_post_date',
    array(
        'label' => esc_html__('Hide Post Date', 'true-news'),
        'section' => 'posts_settings',
        'type' => 'checkbox',
    )
);
$wp_customize->add_section( 'lazy_load_section',
    array(
    'title'      => esc_html__( 'Lazy Load Settings', 'true-news' ),
    'priority'   => 10,
    'capability' => 'edit_theme_options',
    'panel'      => 'theme_option_panel',
    )
);

// Enable Disable Lazy Load.
$wp_customize->add_setting('ed_lazyload',
    array(
        'default' => $true_news_default['ed_lazyload'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_lazyload',
    array(
        'label' => esc_html__('Enable Lazy Load', 'true-news'),
        'section' => 'posts_settings',
        'type' => 'checkbox',
    )
);