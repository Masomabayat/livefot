<?php 
/**
 * Header Options.
 *
 * @package True News
 */
$true_news_default = true_news_get_default_theme_options();
$true_news_page_lists = true_news_page_lists();

// Add Theme Options Panel.
$wp_customize->add_panel( 'theme_option_panel',
	array(
		'title'      => esc_html__( 'Theme Options', 'true-news' ),
		'priority'   => 200,
		'capability' => 'edit_theme_options',
	)
);

// Header Section.
$wp_customize->add_section( 'header_section',
	array(
	'title'      => esc_html__( 'Header Settings', 'true-news' ),
	'priority'   => 5,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);


$wp_customize->add_setting( 'select_header_style',
	array(
		'default'           => $true_news_default['select_header_style'],
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'true_news_sanitize_select',
	)
);
$wp_customize->add_control( 'select_header_style',
	array(
		'label'    => esc_html__( 'Header Layout', 'true-news' ),
		'section'  => 'header_section',
        'type'        => 'select',
        'choices'     => array(
            'header-style-default' => __( 'Header Default Layout', 'true-news' ),
            'header-style-sec' => __( 'Header Alternate Layout', 'true-news' )
        ),
	)
);

// Affix Bar 
$wp_customize->add_setting('twp_affix_bar_logo',
    array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw'
    )
);
$wp_customize->add_control( new WP_Customize_Image_Control(
    $wp_customize,
    'twp_affix_bar_logo',
    	array(
        	'label'      => esc_html__( 'Affix Bar Logo', 'true-news' ),
           	'section'    => 'header_section',
       	)
   	)
);

$wp_customize->add_setting('ed_header_randomize',
    array(
        'default' => $true_news_default['ed_header_randomize'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_header_randomize',
    array(
        'label' => esc_html__('Enable Randomize Icon', 'true-news'),
        'section' => 'header_section',
        'type' => 'checkbox',
    )
);


$wp_customize->add_setting('ed_header_trending',
    array(
        'default' => $true_news_default['ed_header_trending'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_header_trending',
    array(
        'label' => esc_html__('Enable Trending Icon', 'true-news'),
        'section' => 'header_section',
        'type' => 'checkbox',
    )
);

$wp_customize->add_setting( 'trending_posts_page',
    array(
        'default'           =>'',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_select',
    )
);
$wp_customize->add_control( 'trending_posts_page',
    array(
        'label'    => esc_html__( 'Trending Posts Page', 'true-news' ),
        'description'    => esc_html__( 'Selected page should be assigned into "Trending Posts Page" template.', 'true-news' ),
        'section'  => 'header_section',
        'type'        => 'select',
        'choices'     => $true_news_page_lists,
        'active_callback' => 'true_news_trending_posts_ac',
    )
);

$wp_customize->add_setting('ed_header_search',
    array(
        'default' => $true_news_default['ed_header_search'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'true_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_header_search',
    array(
        'label' => esc_html__('Enable Search Icon', 'true-news'),
        'section' => 'header_section',
        'type' => 'checkbox',
    )
);