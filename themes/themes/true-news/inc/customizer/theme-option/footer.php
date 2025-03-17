<?php
/**
* Footer Settings.
*
* @package True News
*/

$true_news_default = true_news_get_default_theme_options();
$true_news_post_category_list = true_news_post_category_list();
// Footer Section.
$wp_customize->add_section( 'footer_setting',
	array(
	'title'      => esc_html__( 'Footer Settings', 'true-news' ),
	'priority'   => 200,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Footer Layout.
$wp_customize->add_setting( 'footer_column_layout',
    array(
    'default'           => $true_news_default['footer_column_layout'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'true_news_sanitize_select',
    )
);
$wp_customize->add_control( 'footer_column_layout',
    array(
    'label'       => esc_html__( 'Top Footer Column Layout', 'true-news' ),
    'section'     => 'footer_setting',
    'type'        => 'select',
    'choices'               => array(
        '1' => esc_html__( 'One Column', 'true-news' ),
        '2' => esc_html__( 'Two Column', 'true-news' ),
        '3' => esc_html__( 'Three Column', 'true-news' ),
        '4' => esc_html__( 'Four Column', 'true-news' ),
        ),
    )
);

// Header Image Ad Link.
$wp_customize->add_setting( 'footer_copyright_text',
    array(
    'default'           => $true_news_default['footer_copyright_text'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control( 'footer_copyright_text',
    array(
    'label'    => esc_html__( 'Footer Copyright Text', 'true-news' ),
    'section'  => 'footer_setting',
    'type'     => 'text',
    )
);

// Ticker Post Enable Disable.
$wp_customize->add_setting('ed_ticker_post',
    array(
        'default' => $true_news_default['ed_ticker_post'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_ticker_post',
    array(
        'label' => esc_html__('Enable Sticky Carousal Posts', 'true-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
    )
);

$wp_customize->add_setting( 'footer_ticker_post_category',
    array(
    'default'           => '',
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'true_news_sanitize_select',
    )
);
$wp_customize->add_control( 'footer_ticker_post_category',
    array(
    'label'       => esc_html__( 'Sticky Carousal Post Category', 'true-news' ),
    'section'     => 'footer_setting',
    'type'        => 'select',
    'choices'     => $true_news_post_category_list,
    'active_callback' => 'true_news_ticker_post_ac',
    )
);

// Ticker Post Slider Arrow Enable Disable.
$wp_customize->add_setting('ed_ticker_post_arrow',
    array(
        'default' => $true_news_default['ed_ticker_post_arrow'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_ticker_post_arrow',
    array(
        'label' => esc_html__('Enable Sticky Carousal Posts Slider Arrows', 'true-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
        'active_callback' => 'true_news_ticker_post_ac',
    )
);

// Ticker Post Slider Dots Enable Disable.
$wp_customize->add_setting('ed_ticker_post_dots',
    array(
        'default' => $true_news_default['ed_ticker_post_dots'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_ticker_post_dots',
    array(
        'label' => esc_html__('Enable Sticky Carousal Posts Slider Dots', 'true-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
        'active_callback' => 'true_news_ticker_post_ac',
    )
);

// Ticker Post Slider Autoplay Enable Disable.
$wp_customize->add_setting('ed_ticker_post_autoplay',
    array(
        'default' => $true_news_default['ed_ticker_post_autoplay'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_ticker_post_autoplay',
    array(
        'label' => esc_html__('Enable Sticky Carousal Posts Slider Autoplay', 'true-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
        'active_callback' => 'true_news_ticker_post_ac',
    )
);

