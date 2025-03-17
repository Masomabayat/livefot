<?php

/**
 * Option Panel
 *
 * @package Newsever
 */

$default = newsever_get_default_theme_options();
/*theme option panel info*/
require get_template_directory() . '/inc/customizer/frontpage-options.php';

// Add Theme Options Panel.
$wp_customize->add_panel('theme_option_panel',
    array(
        'title' => esc_html__('Theme Options', 'newsever'),
        'priority' => 200,
        'capability' => 'edit_theme_options',
    )
);


// Preloader Section.
$wp_customize->add_section('site_preloader_settings',
    array(
        'title' => esc_html__('Preloader Options', 'newsever'),
        'priority' => 4,
        'capability' => 'edit_theme_options',
        'panel' => 'theme_option_panel',
    )
);

// Setting - preloader.
$wp_customize->add_setting('enable_site_preloader',
    array(
        'default' => $default['enable_site_preloader'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_checkbox',
    )
);

$wp_customize->add_control('enable_site_preloader',
    array(
        'label' => esc_html__('Enable preloader', 'newsever'),
        'section' => 'site_preloader_settings',
        'type' => 'checkbox',
        'priority' => 10,
    )
);
    
    
    /**
     * Layout options section
     *
     * @package Newsever
     */

// Layout Section.
    $wp_customize->add_section('site_layout_settings',
        array(
            'title' => esc_html__('Global Settings', 'newsever'),
            'priority' => 9,
            'capability' => 'edit_theme_options',
            'panel' => 'theme_option_panel',
        )
    );


// Setting - breadcrumb.
$wp_customize->add_setting('enable_breadcrumb',
    array(
        'default' => $default['enable_breadcrumb'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_checkbox',
    )
);

$wp_customize->add_control('enable_breadcrumb',
    array(
        'label' => esc_html__('Show breadcrumbs', 'newsever'),
        'section' => 'site_layout_settings',
        'type' => 'checkbox',
        'priority' => 10,
    )
);

// Setting - global content alignment of news.
    $wp_customize->add_setting('global_content_alignment',
        array(
            'default' => $default['global_content_alignment'],
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'newsever_sanitize_select',
        )
    );
    
    $wp_customize->add_control('global_content_alignment',
        array(
            'label' => esc_html__('Global Content Alignment', 'newsever'),
            'section' => 'site_layout_settings',
            'type' => 'select',
            'choices' => array(
                'align-content-left' => esc_html__('Content - Primary sidebar', 'newsever'),
                'align-content-right' => esc_html__('Primary sidebar - Content', 'newsever'),
                'full-width-content' => esc_html__('Full width content', 'newsever')
            ),
            'priority' => 130,
        ));

// Setting - global content alignment of news.
    $wp_customize->add_setting('global_show_categories',
        array(
            'default' => $default['global_show_categories'],
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'newsever_sanitize_select',
        )
    );
    
    $wp_customize->add_control('global_show_categories',
        array(
            'label' => esc_html__('Post Categories', 'newsever'),
            'section' => 'site_layout_settings',
            'type' => 'select',
            'choices' => array(
                'yes' => esc_html__('Show', 'newsever'),
                'no' => esc_html__('Hide', 'newsever'),
            
            ),
            'priority' => 130,
        ));


// Setting - global content alignment of news.
    $wp_customize->add_setting('global_widget_excerpt_setting',
        array(
            'default' => $default['global_widget_excerpt_setting'],
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'newsever_sanitize_select',
        )
    );
    
    $wp_customize->add_control('global_widget_excerpt_setting',
        array(
            'label' => esc_html__('Widget Excerpt Mode', 'newsever'),
            'section' => 'site_layout_settings',
            'type' => 'select',
            'choices' => array(
                'trimmed-content' => esc_html__('Trimmed Content', 'newsever'),
                'default-excerpt' => esc_html__('Default Excerpt', 'newsever'),
            
            ),
            'priority' => 130,
        ));


    /**
     * Header section
     *
     * @package Newsever
     */

// Frontpage Section.
    $wp_customize->add_section('header_options_settings',
        array(
            'title' => esc_html__('Header Options', 'newsever'),
            'priority' => 49,
            'capability' => 'edit_theme_options',
            'panel' => 'theme_option_panel',
        )
    );




// Setting - sticky_header_option.
    $wp_customize->add_setting('enable_sticky_header_option',
        array(
            'default' => $default['enable_sticky_header_option'],
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'newsever_sanitize_checkbox',
        )
    );
    $wp_customize->add_control('enable_sticky_header_option',
        array(
            'label' => esc_html__('Enable Sticky Header', 'newsever'),
            'section' => 'header_options_settings',
            'type' => 'checkbox',
            'priority' => 11
        )
    );

// Setting - global content alignment of news.
$wp_customize->add_setting('global_show_home_menu',
    array(
        'default' => $default['global_show_home_menu'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_select',
    )
);

$wp_customize->add_control('global_show_home_menu',
    array(
        'label' => esc_html__('Home Menu Icon', 'newsever'),
        'section' => 'header_options_settings',
        'type' => 'select',
        'choices' => array(
            'yes' => esc_html__('Show', 'newsever'),
            'no' => esc_html__('Hide', 'newsever'),

        ),
        'priority' => 11,
    ));




//=================================
//Watch Online Section.
//=================================


//section title
$wp_customize->add_setting('custom_link_section_title',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new Newsever_Section_Title(
        $wp_customize,
        'custom_link_section_title',
        array(
            'label' => esc_html__('Custom Link Section ', 'newsever'),
            'section' => 'header_options_settings',
            'priority' => 100,

        )
    )
);


$wp_customize->add_setting('show_watch_online_section',
    array(
        'default' => $default['show_watch_online_section'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_checkbox',
    )
);

$wp_customize->add_control('show_watch_online_section',
    array(
        'label' => esc_html__('Enable Watch Online Section', 'newsever'),
        'section' => 'header_options_settings',
        'type' => 'checkbox',
        'priority' => 100,

    )
);

// Setting - sticky_header_option.
$wp_customize->add_setting('aft_custom_title',
    array(
        'default' => $default['aft_custom_title'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('aft_custom_title',
    array(
        'label' => esc_html__('Title', 'newsever'),
        'section' => 'header_options_settings',
        'type' => 'text',
        'priority' => 130,
        'active_callback' => 'newsever_show_watch_online_section_status'
    )
);

// Setting - sticky_header_option.
$wp_customize->add_setting('aft_custom_link',
    array(
        'default' => $default['aft_custom_link'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('aft_custom_link',
    array(
        'label' => esc_html__('Button Link', 'newsever'),
        'section' => 'header_options_settings',
        'type' => 'text',
        'priority' => 130,
        'active_callback' => 'newsever_show_watch_online_section_status'
    )
);







//========== comment count options ===============

// Global Section.
$wp_customize->add_section('site_comment_count_settings',
    array(
        'title' => esc_html__('Comment Count', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'theme_option_panel',
    )
);

// Setting - global content alignment of news.
$wp_customize->add_setting('global_show_comment_count',
    array(
        'default' => $default['global_show_comment_count'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_select',
    )
);

$wp_customize->add_control('global_show_comment_count',
    array(
        'label' => esc_html__('Comment Count', 'newsever'),
        'section' => 'site_comment_count_settings',
        'type' => 'select',
        'choices' => array(
            'yes' => esc_html__('Show', 'newsever'),
            'no' => esc_html__('Hide', 'newsever'),

        ),
        'priority' => 130,
    ));



//========== minutes read count options ===============

// Global Section.
$wp_customize->add_section('site_min_read_settings',
    array(
        'title' => esc_html__('Minutes Read Count', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'theme_option_panel',
    )
);


// Setting - global content alignment of news.
$wp_customize->add_setting('global_show_min_read',
    array(
        'default' => $default['global_show_min_read'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_select',
    )
);

$wp_customize->add_control('global_show_min_read',
    array(
        'label' => esc_html__('Minutes Read Count', 'newsever'),
        'section' => 'site_min_read_settings',
        'type' => 'select',
        'choices' => array(
            'yes' => esc_html__('Show', 'newsever'),
            'no' => esc_html__('Hide', 'newsever'),

        ),
        'priority' => 130,
    ));

//========== date and author options ===============

// Global Section.
$wp_customize->add_section('site_post_date_author_settings',
    array(
        'title' => esc_html__('Date and Author', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'theme_option_panel',
    )
);

// Setting - global content alignment of news.
$wp_customize->add_setting('global_post_date_author_setting',
    array(
        'default' => $default['global_post_date_author_setting'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_select',
    )
);


$wp_customize->add_control('global_post_date_author_setting',
    array(
        'label' => esc_html__('Date and Author', 'newsever'),
        'section' => 'site_post_date_author_settings',
        'type' => 'select',
        'choices' => array(
            'show-date-author' => esc_html__('Show Date and Author', 'newsever'),
            'hide-date-author' => esc_html__('Hide All', 'newsever'),
        ),
        'priority' => 130,
    ));



//========== single posts options ===============

// Single Section.
$wp_customize->add_section('site_single_posts_settings',
    array(
        'title' => esc_html__('Single Post', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'theme_option_panel',
    )
);

// Setting - related posts.
$wp_customize->add_setting('single_show_featured_image',
    array(
        'default' => $default['single_show_featured_image'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_checkbox',
    )
);

$wp_customize->add_control('single_show_featured_image',
    array(
        'label' => __('Show Featured Image', 'newsever'),
        'section' => 'site_single_posts_settings',
        'type' => 'checkbox',
        'priority' => 100,
    )
);


//========== related posts  options ===============

// Single Section.
$wp_customize->add_section('site_single_related_posts_settings',
    array(
        'title' => esc_html__('Related Posts', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'theme_option_panel',
    )
);



// Setting - related posts.
$wp_customize->add_setting('single_related_posts_title',
    array(
        'default' => $default['single_related_posts_title'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control('single_related_posts_title',
    array(
        'label' => __('Title', 'newsever'),
        'section' => 'site_single_related_posts_settings',
        'type' => 'text',
        'priority' => 100,
        //'active_callback' => 'newsever_related_posts_status'
    )
);

/**
 * Archive options section
 *
 * @package Newsever
 */

// Archive Section.
$wp_customize->add_section('site_archive_settings',
    array(
        'title' => esc_html__('Archive Settings', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'theme_option_panel',
    )
);



// Setting - archive content view of news.
$wp_customize->add_setting('archive_image_alignment_list',
    array(
        'default' => $default['archive_image_alignment_list'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'newsever_sanitize_select',
    )
);

$wp_customize->add_control('archive_image_alignment_list',
    array(
        'label' => esc_html__('Image alignment', 'newsever'),
        'description' => esc_html__('Select image alignment for archive', 'newsever'),
        'section' => 'site_archive_settings',
        'type' => 'select',
        'choices' => array(
            'archive-image-left' => esc_html__('Left', 'newsever'),
            'archive-image-right' => esc_html__('Right', 'newsever'),
            'archive-image-alternate' => esc_html__('Alternate', 'newsever'),
        ),
        'priority' => 130,
        //'active_callback' => 'newsever_archive_image_status'
    ));


//========== footer latest blog carousel options ===============

// Footer Section.
$wp_customize->add_section('frontpage_latest_posts_settings',
    array(
        'title' => esc_html__('You May Have Missed', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'theme_option_panel',
    )
);



// Setting - featured_news_section_title.
$wp_customize->add_setting('frontpage_latest_posts_section_title',
    array(
        'default' => $default['frontpage_latest_posts_section_title'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('frontpage_latest_posts_section_title',
    array(
        'label' => esc_html__('Posts Section Title', 'newsever'),
        'section' => 'frontpage_latest_posts_settings',
        'type' => 'text',
        'priority' => 100,
        //'active_callback' => 'newsever_latest_news_section_status'

    )
);




//========== footer section options ===============
// Footer Section.
$wp_customize->add_section('site_footer_settings',
    array(
        'title' => esc_html__('Footer', 'newsever'),
        'priority' => 50,
        'capability' => 'edit_theme_options',
        'panel' => 'theme_option_panel',
    )
);

// Setting - global content alignment of news.
$wp_customize->add_setting('footer_copyright_text',
    array(
        'default' => $default['footer_copyright_text'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control('footer_copyright_text',
    array(
        'label' => __('Copyright Text', 'newsever'),
        'section' => 'site_footer_settings',
        'type' => 'text',
        'priority' => 100,
    )
);


