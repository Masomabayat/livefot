<?php

/**
 * Option Panel
 *
 * @package Newsever
 */

$default = newsever_get_default_theme_options();

/**
 * Frontpage options section
 *
 * @package Newsever
 */


// Add Frontpage Options Panel.
$wp_customize->add_panel('frontpage_option_panel',
    array(
        'title' => esc_html__('Frontpage Options', 'newsever'),
        'priority' => 199,
        'capability' => 'edit_theme_options',
    )
);


// Advertisement Section.
$wp_customize->add_section('frontpage_advertisement_settings',
    array(
        'title' => esc_html__('Banner Advertisement', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'frontpage_option_panel',
    )
);


// Setting banner_advertisement_section.
$wp_customize->add_setting('banner_advertisement_section',
    array(
        'default' => $default['banner_advertisement_section'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'absint',
    )
);


$wp_customize->add_control(
    new WP_Customize_Cropped_Image_Control($wp_customize, 'banner_advertisement_section',
        array(
            'label' => esc_html__('Banner Section Advertisement', 'newsever'),
            'description' => sprintf(esc_html__('Recommended Size %1$s px X %2$s px', 'newsever'), 930, 100),
            'section' => 'frontpage_advertisement_settings',
            'width' => 930,
            'height' => 100,
            'flex_width' => true,
            'flex_height' => true,
            'priority' => 120,
        )
    )
);

/*banner_advertisement_section_url*/
$wp_customize->add_setting('banner_advertisement_section_url',
    array(
        'default' => $default['banner_advertisement_section_url'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    )
);
$wp_customize->add_control('banner_advertisement_section_url',
    array(
        'label' => esc_html__('URL Link', 'newsever'),
        'section' => 'frontpage_advertisement_settings',
        'type' => 'text',
        'priority' => 130,
    )
);


//=================================
// Trending Posts Section.
//=================================
$wp_customize->add_section('newsever_flash_posts_section_settings',
    array(
        'title' => esc_html__('Exclusive Posts', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'frontpage_option_panel',
    )
);

$wp_customize->add_setting('show_flash_news_section',
    array(
        'default' => $default['show_flash_news_section'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_checkbox',
    )
);

$wp_customize->add_control('show_flash_news_section',
    array(
        'label' => esc_html__('Enable Exclusive Posts Section', 'newsever'),
        'section' => 'newsever_flash_posts_section_settings',
        'type' => 'checkbox',
        'priority' => 22,

    )
);

// Setting - number_of_slides.
$wp_customize->add_setting('flash_news_title',
    array(
        'default' => $default['flash_news_title'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control('flash_news_title',
    array(
        'label' => esc_html__('Exclusive News Title', 'newsever'),
        'section' => 'newsever_flash_posts_section_settings',
        'type' => 'text',
        'priority' => 23,
        'active_callback' => 'newsever_flash_posts_section_status'

    )
);

// Setting - number_of_slides.
$wp_customize->add_setting('flash_news_subtitle',
    array(
        'default' => $default['flash_news_subtitle'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control('flash_news_subtitle',
    array(
        'label' => esc_html__('Exclusive News Subtitle', 'newsever'),
        'section' => 'newsever_flash_posts_section_settings',
        'type' => 'text',
        'priority' => 23,
        'active_callback' => 'newsever_flash_posts_section_status'

    )
);

// Setting - drop down category for slider.
$wp_customize->add_setting('select_flash_news_category',
    array(
        'default' => $default['select_flash_news_category'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'absint',
    )
);


$wp_customize->add_control(new Newsever_Dropdown_Taxonomies_Control($wp_customize, 'select_flash_news_category',
    array(
        'label' => esc_html__('Exclusive Posts Category', 'newsever'),
        'description' => esc_html__('Posts to be shown on trending posts ', 'newsever'),
        'section' => 'newsever_flash_posts_section_settings',
        'type' => 'dropdown-taxonomies',
        'taxonomy' => 'category',
        'priority' => 23,
        'active_callback' => 'newsever_flash_posts_section_status'
    )));


/**
 * Main Banner Slider Section
 * */

// Main banner Sider Section.
$wp_customize->add_section('frontpage_main_banner_section_settings',
    array(
        'title' => esc_html__('Main Banner Section', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'frontpage_option_panel',
    )
);

// Setting - show_main_news_section.
$wp_customize->add_setting('show_main_news_section',
    array(
        'default' => $default['show_main_news_section'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_checkbox',
    )
);

$wp_customize->add_control('show_main_news_section',
    array(
        'label' => esc_html__('Enable Main Banner Section', 'newsever'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'checkbox',
        'priority' => 100,

    )
);


//section title
$wp_customize->add_setting('main_banner_section_title',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new Newsever_Section_Title(
        $wp_customize,
        'main_banner_section_title',
        array(
            'label' => esc_html__('Main Slider Section ', 'newsever'),
            'section' => 'frontpage_main_banner_section_settings',
            'priority' => 100,
            'active_callback' => 'newsever_main_banner_section_status'

        )
    )
);




// Setting - sticky_header_option.
$wp_customize->add_setting('main_banner_section_label',
    array(
        'default' => $default['main_banner_section_label'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('main_banner_section_label',
    array(
        'label' => esc_html__('Section Title', 'newsever'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'text',
        'priority' => 100,
        'active_callback' => 'newsever_main_banner_section_status'
    )
);
// Setting - drop down category for slider.
$wp_customize->add_setting('select_slider_news_category',
    array(
        'default' => $default['select_slider_news_category'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'absint',
    )
);


$wp_customize->add_control(new Newsever_Dropdown_Taxonomies_Control($wp_customize, 'select_slider_news_category',
    array(
        'label' => esc_html__('Category', 'newsever'),
        'description' => esc_html__('Posts to be shown on Main News Slider', 'newsever'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'dropdown-taxonomies',
        'taxonomy' => 'category',
        'priority' => 100,
        'active_callback' => 'newsever_main_banner_section_status'
    )));




//Editorials Starts
$wp_customize->add_setting('editorials_section_title',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new Newsever_Section_Title(
        $wp_customize,
        'editorials_section_title',
        array(
            'label' => esc_html__('Editorials Section ', 'newsever'),
            'section' => 'frontpage_main_banner_section_settings',
            'priority' => 100,
            'active_callback' => 'newsever_main_banner_section_status'

        )
    )
);

// Setting - sticky_header_option.
$wp_customize->add_setting('editors_pick_section_title',
    array(
        'default' => $default['editors_pick_section_title'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('editors_pick_section_title',
    array(
        'label' => esc_html__('Section Title', 'newsever'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'text',
        'priority' => 100,
        'active_callback' => 'newsever_main_banner_section_status'
    )
);

// Setting - drop down category for slider.
$wp_customize->add_setting('select_editors_pick_category',
    array(
        'default' => $default['select_editors_pick_category'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'absint',
    )
);

$wp_customize->add_control(new Newsever_Dropdown_Taxonomies_Control($wp_customize, 'select_editors_pick_category',
    array(
        'label' => esc_html__('Category', 'newsever'),
        'description' => esc_html__('Posts to be shown on Editorials slider section', 'newsever'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'dropdown-taxonomies',
        'taxonomy' => 'category',
        'priority' => 100,
        'active_callback' => function ($control) {
            return (
            newsever_main_banner_section_status($control)


            );
        }
    )));


//Trending Slider Starts
$wp_customize->add_setting('trending_carousel_section_title',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new Newsever_Section_Title(
        $wp_customize,
        'trending_carousel_section_title',
        array(
            'label' => esc_html__('Trending Section ', 'newsever'),
            'section' => 'frontpage_main_banner_section_settings',
            'priority' => 100,
            'active_callback' => 'newsever_main_banner_section_status'

        )
    )
);

// Setting - sticky_header_option.
$wp_customize->add_setting('trending_slider_title',
    array(
        'default' => $default['trending_slider_title'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('trending_slider_title',
    array(
        'label' => esc_html__('Section Title', 'newsever'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'text',
        'priority' => 100,
        'active_callback' => 'newsever_main_banner_section_status'
    )
);


// Setting - select_main_banner_section_mode.
$wp_customize->add_setting('select_trending_carousel_section_mode',
    array(
        'default' => $default['select_trending_carousel_section_mode'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_select',
    )
);

$wp_customize->add_control('select_trending_carousel_section_mode',
    array(
        'label' => esc_html__('Select Trending Carousel Position', 'newsever'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'select',
        'choices' => array(
            'left' => esc_html__("Left", 'newsever'),
            'right' => esc_html__("Right", 'newsever'),

        ),
        'priority' => 100,
        'active_callback' => function ($control) {
            return (
                newsever_main_banner_section_status($control)



            );
        }
    ));

// Setting - drop down category for slider.
$wp_customize->add_setting('select_trending_carousel_category',
    array(
        'default' => $default['select_trending_carousel_category'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'absint',
    )
);

$wp_customize->add_control(new Newsever_Dropdown_Taxonomies_Control($wp_customize, 'select_trending_carousel_category',
    array(
        'label' => esc_html__('Category', 'newsever'),
        'description' => esc_html__('Posts to be shown on Trending slider section', 'newsever'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'dropdown-taxonomies',
        'taxonomy' => 'category',
        'priority' => 100,
        'active_callback' => function ($control) {
            return (
                newsever_main_banner_section_status($control)


            );
        }
    )));




// Disable main banner in blog
$wp_customize->add_setting('disable_main_banner_on_blog_archive',
    array(
        'default' => $default['disable_main_banner_on_blog_archive'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_checkbox',
    )
);

$wp_customize->add_control('disable_main_banner_on_blog_archive',
    array(
        'label' => esc_html__('Disable Main Banner section on Static Posts page', 'newsever'),
        'section' => 'frontpage_main_banner_section_settings',
        'type' => 'checkbox',
        'priority' => 100,
        'active_callback' => 'newsever_main_banner_section_status'
    )
);


// Frontpage Layout Section.
$wp_customize->add_section('frontpage_layout_settings',
    array(
        'title' => esc_html__('Frontpage Layout Settings', 'newsever'),
        'priority' => 10,
        'capability' => 'edit_theme_options',
        'panel' => 'frontpage_option_panel',
    )
);


// Setting - show_main_news_section.
$wp_customize->add_setting('frontpage_content_alignment',
    array(
        'default' => $default['frontpage_content_alignment'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_select',
    )
);


$wp_customize->add_control('frontpage_content_alignment',
    array(
        'label' => esc_html__('Frontpage Content alignment', 'newsever'),
        'description' => esc_html__('Select frontpage content alignment', 'newsever'),
        'section' => 'frontpage_layout_settings',
        'type' => 'select',
        'choices' => array(
            'frontpage-layout-1' => esc_html__('Default - Frontpage Layout 1 ', 'newsever'),
            'frontpage-layout-2' => esc_html__('Frontpage Layout 2', 'newsever'),
            'frontpage-layout-3' => esc_html__('Frontpage Layout 3', 'newsever')
        ),
        'priority' => 10,
    ));

// Setting - frontpage_sticky_sidebar.
$wp_customize->add_setting('frontpage_sticky_sidebar',
    array(
        'default' => $default['frontpage_sticky_sidebar'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_checkbox',
    )
);

$wp_customize->add_control('frontpage_sticky_sidebar',
    array(
        'label' => esc_html__('Make Frontpage Sidebar Sticky', 'newsever'),
        'section' => 'frontpage_layout_settings',
        'type' => 'checkbox',
        'priority' => 10,
        //'active_callback' => 'newsever_frontpage_content_alignment_status'
    )
);