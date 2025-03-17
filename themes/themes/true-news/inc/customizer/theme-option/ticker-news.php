<?php
/**
 * Ticker News section
 *
 * @package True News
 */

$true_news_default = true_news_get_default_theme_options();
$true_news_post_category_list = true_news_post_category_list();

// Setting - show_ticker_section.
$wp_customize->add_setting( 'show_ticker_section',
	array(
		'default'           => $true_news_default['show_ticker_section'],
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'true_news_sanitize_checkbox',
	)
);
$wp_customize->add_control( 'show_ticker_section',
	array(
		'label'    => esc_html__( 'Enable Ticker News', 'true-news' ),
		'section'  => 'static_front_page',
		'type'     => 'checkbox',
	)
);

// Setting - ticker_title.
$wp_customize->add_setting( 'ticker_title',
	array(
		'default'           => $true_news_default['ticker_title'],
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'ticker_title',
	array(
		'label'    => esc_html__( 'Ticker News Title', 'true-news' ),
		'section'  => 'static_front_page',
		'type'     => 'text',
	)
);

// Setting - show_ticker_meta_data.
$wp_customize->add_setting( 'show_ticker_meta_data',
	array(
		'default'           => $true_news_default['show_ticker_meta_data'],
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'true_news_sanitize_checkbox',
	)
);
$wp_customize->add_control( 'show_ticker_meta_data',
	array(
		'label'    => esc_html__( 'Enable Meta Data on Ticker News', 'true-news' ),
		'section'  => 'static_front_page',
		'type'     => 'checkbox',
	)
);

$wp_customize->add_setting('select_category_for_ticker',
    array(
        'default' => $true_news_default['select_category_for_ticker'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('select_category_for_ticker',
    array(
        'label' => esc_html__('Category for Ticker News', 'true-news'),
        'section' => 'static_front_page',
        'type' => 'select',
        'choices' => $true_news_post_category_list,
    )
);