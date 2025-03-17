<?php
/**
 * Pagination Settings
 *
 * @package True News
 */

$true_news_default = true_news_get_default_theme_options();

// Pagination Section.
$wp_customize->add_section( 'true_news_pagination_section',
	array(
	'title'      => esc_html__( 'Pagination Settings', 'true-news' ),
	'priority'   => 20,
	'capability' => 'edit_theme_options',
	'panel'		 => 'theme_option_panel',
	)
);

// Pagination Layout Settings
$wp_customize->add_setting( 'true_news_pagination_layout',
	array(
	'default'           => $true_news_default['true_news_pagination_layout'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'true_news_pagination_layout',
	array(
	'label'       => esc_html__( 'Pagination Method', 'true-news' ),
	'section'     => 'true_news_pagination_section',
	'type'        => 'select',
	'choices'     => array(
		'next-prev' => esc_html__('Next/Previous Method','true-news'),
		'numeric' => esc_html__('Numeric Method','true-news'),
		'load-more' => esc_html__('Ajax Load More Button','true-news'),
		'auto-load' => esc_html__('Ajax Auto Load','true-news'),
	),
	)
);