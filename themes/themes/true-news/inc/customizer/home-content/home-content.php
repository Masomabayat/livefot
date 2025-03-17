<?php
/**
* Sections Repeater Options.
*
* @package True News
*/

$true_news_default = true_news_get_default_theme_options();
$home_sections = true_news_home_sections();
$true_news_post_category_list = true_news_post_category_list();
$pagination_type = true_news_pagination_type();

// Slider Section.
$wp_customize->add_section( 'home_sections_repeater',
    array(
    'title'      => esc_html__( 'Homepage Content', 'true-news' ),
    'capability' => 'edit_theme_options',
    'priority'   => 150,
    )
);

// Recommended Posts Enable Disable.
$wp_customize->add_setting( 'true_news_home_sections', array(
    'sanitize_callback' => 'true_news_sanitize_repeater',
    'default' => json_encode( $true_news_default['true_news_home_sections'] ),
));

$wp_customize->add_control(  new True_News_Repeater_Controler( $wp_customize, 'true_news_home_sections', 
    array(
        'section' => 'home_sections_repeater',
        'settings' => 'true_news_home_sections',
        'true_news_box_label' => esc_html__('New Section','true-news'),
        'true_news_box_add_control' => esc_html__('Add New Section','true-news'),
    ),
    array(
        'section_ed' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Enable Section', 'true-news' ),
            'class'       => 'home-section-ed'
        ),
        'home_section_type' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Section Type', 'true-news' ),
            'options'     => $home_sections,
            'class'       => 'home-section-type'
        ),
        'section_title' => array(
            'type'        => 'text',
            'label'       => esc_html__( 'Section Title', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-4-fields block-5-fields block-7-fields block-8-fields recommended-fields latest-posts-fields'
        ),

        'post_category' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Post Category', 'true-news' ),
            'options'     => $true_news_post_category_list,
            'class'       => 'home-repeater-fields-hs block-4-fields block-7-fields recommended-fields'
        ),
        'section_block_1_title_left' => array(
            'type'        => 'text',
            'label'       => esc_html__( 'Left Section Title', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-2-fields block-3-fields'
        ),
        'section_block_1_cat_left' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Left Section Category', 'true-news' ),
            'options'     => $true_news_post_category_list,
            'class'       => 'home-repeater-fields-hs block-2-fields block-3-fields'
        ),
        'section_block_1_title_1' => array(
            'type'        => 'text',
            'label'       => esc_html__( 'Slider Title', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-1-fields block-2-fields'
        ),
        'section_block_1_cat_1' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Slider Category', 'true-news' ),
            'options'     => $true_news_post_category_list,
            'class'       => 'home-repeater-fields-hs block-1-fields block-6-fields block-2-fields'
        ),
        'section_title_1' => array(
            'type'        => 'text',
            'label'       => esc_html__( 'Section Title One', 'true-news' ),
            'class'       => 'home-repeater-fields-hs'
        ),
        'post_category_1' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Post Category One', 'true-news' ),
            'options'     => $true_news_post_category_list,
            'class'       => 'home-repeater-fields-hs block-5-fields'
        ),
        'section_block_1_title_2' => array(
            'type'        => 'text',
            'label'       => esc_html__( 'Mid Section Title', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-1-fields'
        ),
        'section_block_1_cat_2' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Mid Section Category', 'true-news' ),
            'options'     => $true_news_post_category_list,
            'class'       => 'home-repeater-fields-hs block-1-fields'
        ),
        'section_title_2' => array(
            'type'        => 'text',
            'label'       => esc_html__( 'Section Title Two', 'true-news' ),
            'class'       => 'home-repeater-fields-hs'
        ),
        
        'post_category_2' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Post Category Two', 'true-news' ),
            'options'     => $true_news_post_category_list,
            'class'       => 'home-repeater-fields-hs block-5-fields'
        ),
        'post_category_3' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Post Category Three', 'true-news' ),
            'options'     => $true_news_post_category_list,
            'class'       => 'home-repeater-fields-hs block-5-fields'
        ),
        'post_category_4' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Post Category Four', 'true-news' ),
            'options'     => $true_news_post_category_list,
            'class'       => 'home-repeater-fields-hs block-5-fields'
        ),
        'section_block_1_title_3' => array(
            'type'        => 'text',
            'label'       => esc_html__( 'Vertical Slider Title', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-1-fields'
        ),
        'section_block_1_cat_3' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Vertical Slider Category', 'true-news' ),
            'options'     => $true_news_post_category_list,
            'class'       => 'home-repeater-fields-hs block-1-fields'
        ),
        'section_title_3' => array(
            'type'        => 'text',
            'label'       => esc_html__( 'Right Section Title', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-2-fields  block-3-fields'
        ),
        'post_category_right_side' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Right Section Post Category', 'true-news' ),
            'options'     => $true_news_post_category_list,
            'class'       => 'home-repeater-fields-hs block-2-fields block-3-fields'
        ),
        'sidebar_layout' => array(
            'type'        => 'select',
            'label'       => esc_html__( 'Sidebar Layout', 'true-news' ),
            'options'     => array(
                                'global-sidebar' => esc_html__('Global Sidebar','true-news'),
                                'right-sidebar' => esc_html__('Content & Right Sidebar','true-news'),
                                'left-sidebar' => esc_html__('Left Sidebar & Content','true-news'),
                                'both-sidebar' => esc_html__('Left Sidebar, Content & Right Sidebar','true-news'),
                                'content-left-right' => esc_html__('Content, Left Sidebar & Right Sidebar','true-news'),
                                'left-right-content' => esc_html__('Left Sidebar, Right Sidebar & Content','true-news'),
                                'no-sidebar' => esc_html__('No Sidebar','true-news'),
                            ),
            'class'       => 'home-repeater-fields-hs latest-posts-fields'
        ),
        'hide_popular_posts' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Enable Popular Tab', 'true-news' ),
            'description'       => esc_html__( 'Popular posts will be display on the basis of post comment.', 'true-news' ),
            'class'       => 'home-repeater-fields-hs latest-posts-fields'
        ),
        'hide_trending_posts' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Enable Trending Tab', 'true-news' ),
            'description'       => esc_html__( 'Trending posts will be display on the basis of post views. Please install Booster Extension Plugin for post visit count.', 'true-news' ),
            'class'       => 'home-repeater-fields-hs latest-posts-fields'
        ),
        'hide_slider_overlay' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Hide Slider Overlay', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-6-fields'
        ),
        'hide_post_category' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Hide Post Categories', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-4-fields block-5-fields block-3-fields block-2-fields block-1-fields latest-posts-fields block-6-fields block-7-fields block-8-fields recommended-fields'
        ),
        'hide_post_author_avatar' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Hide Author Image', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-4-fields block-5-fields block-3-fields block-2-fields block-1-fields latest-posts-fields block-6-fields block-7-fields block-8-fields recommended-fields'
        ),
        'hide_post_author' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Hide Author Name', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-4-fields block-5-fields block-3-fields block-2-fields block-1-fields latest-posts-fields block-6-fields block-7-fields block-8-fields recommended-fields'
        ),
        'hide_post_date' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Hide Post Date', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-4-fields block-5-fields block-3-fields block-2-fields block-1-fields latest-posts-fields block-6-fields block-7-fields block-8-fields recommended-fields'
        ),
        'slider_autoplay' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Enable Autoplay', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-6-fields block-2-fields block-1-fields block-7-fields'
        ),
        'slider_dots' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Enable Dots', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-6-fields block-2-fields block-1-fields block-7-fields'
        ),
        'slider_arrows' => array(
            'type'        => 'checkbox',
            'label'       => esc_html__( 'Enable Arrows', 'true-news' ),
            'class'       => 'home-repeater-fields-hs block-6-fields block-2-fields block-1-fields block-7-fields'
        ),
        'advertise_image' => array(
            'type'        => 'upload',
            'label'       => esc_html__( 'Advertise Image', 'true-news' ),
            'description' => esc_html__( 'Recommended Image Size is 970x250 PX.', 'true-news' ),
            'class'       => 'home-repeater-fields-hs advertise-area-fields'
        ),
        'advertise_link' => array(
            'type'        => 'link',
            'label'       => esc_html__( 'Advertise Image Link', 'true-news' ),
            'class'       => 'home-repeater-fields-hs advertise-area-fields'
        ),
        'advertise_script' => array(
            'type'        => 'textarea',
            'label'       => esc_html__( 'Advertise Script', 'true-news' ),
            'class'       => 'home-repeater-fields-hs advertise-area-fields'
        ),

    )
));
