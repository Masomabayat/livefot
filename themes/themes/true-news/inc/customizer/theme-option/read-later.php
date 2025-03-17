<?php
/**
* Read Later Section
*
* @package True News
*/

$true_news_default = true_news_get_default_theme_options();
$true_news_page_lists = true_news_page_lists();
// Single Post Section.
$wp_customize->add_section( 'pin_post_setting',
    array(
    'title'      => esc_html__( 'Read Later Settings', 'true-news' ),
    'capability' => 'edit_theme_options',
    'priority'   => 10,
    'panel'      => 'theme_option_panel',
    )
);

// Read Later Enable Disable
$wp_customize->add_setting('ed_read_later_posts',
    array(
        'default' => $true_news_default['ed_read_later_posts'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_read_later_posts',
    array(
        'label' => esc_html__('Enable Read Later', 'true-news'),
        'section' => 'pin_post_setting',
        'type' => 'checkbox',
    )
);

$wp_customize->add_setting( 'pinned_posts_page',
    array(
        'default'           =>'',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_select',
    )
);
$wp_customize->add_control( 'pinned_posts_page',
    array(
        'label'    => esc_html__( 'Read Later Posts Page', 'true-news' ),
        'description'    => esc_html__( 'Selected page should be assigned into "Read Later Posts" template.', 'true-news' ),
        'section'  => 'pin_post_setting',
        'type'        => 'select',
        'choices'     => $true_news_page_lists,
        'active_callback' => 'true_news_pinpost_posts_ac',
    )
);

// Read Later Enable Disable
$wp_customize->add_setting('ed_read_later_posts_notification',
    array(
        'default' => $true_news_default['ed_read_later_posts_notification'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_read_later_posts_notification',
    array(
        'label' => esc_html__('Enable Read Later', 'true-news'),
        'section' => 'pin_post_setting',
        'type' => 'checkbox',
        'active_callback' => 'true_news_pinpost_posts_ac',
    )
);