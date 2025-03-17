<?php
/**
 * FreeNews All theme Options
 *
 * @package freenews
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */

//Front Page Category (List of categories to hide from home page)

    $frontpage_cat_lists = freenews_frontpage_cat_list();

    $wp_customize->add_setting( 'front_page_categories', array(
        'default'           => '',
        'sanitize_callback' => 'freenews_sanitize_multi_checkbox'
    ) );

    $wp_customize->add_control(
        new FreeNews_Customize_Multiple_Checkboxes_Control(
            $wp_customize,
            'front_page_categories',
            array(
                'section' => 'freenews_all_theme_options',
                'label'   => esc_html__( 'Front/ Home page posts categories', 'freenews' ),
                'description' => esc_html__('Selected category display on front/ home page. If not selected, all post will be displayed','freenews'),
                'choices' => $frontpage_cat_lists,
                'priority'    => 10,
            )
        )
    );

    //  Blog Options
    $wp_customize->add_setting('main-title', array(
            'type'              => 'main-title-control',
            'sanitize_callback' => 'sanitize_text_field',            
        )
    );
    $wp_customize->add_control( new FreeNews_title_display( $wp_customize, 'freenews-100', array(
            'priority' => 100,
            'label' => esc_html__('Blog/ Single Options', 'freenews'),
            'section' => 'freenews_all_theme_options',
            'settings' => 'main-title',
        ) )
    );

    $wp_customize->add_setting( 'disable-author', array(
        'default' => 0,
        'sanitize_callback' => 'freenews_sanitize_checkbox',
    ));
    $wp_customize->add_control( 'disable-author', array(
        'priority'=>110,
        'label' => esc_html__('Hide Author', 'freenews'),
        'section' => 'freenews_all_theme_options',
        'type' => 'checkbox',
    ));

    $wp_customize->add_setting( 'disable-date', array(
        'default' => 0,
        'sanitize_callback' => 'freenews_sanitize_checkbox',
    ));
    $wp_customize->add_control( 'disable-date', array(
        'priority'=>120,
        'label' => esc_html__('Hide Date', 'freenews'),
        'section' => 'freenews_all_theme_options',
        'type' => 'checkbox',
    ));

    $wp_customize->add_setting( 'disable-cateogry', array(
        'default' => 0,
        'sanitize_callback' => 'freenews_sanitize_checkbox',
    ));
    $wp_customize->add_control( 'disable-cateogry', array(
        'priority'=>130,
        'label' => esc_html__('Hide Category', 'freenews'),
        'section' => 'freenews_all_theme_options',
        'type' => 'checkbox',
    ));

    $wp_customize->add_setting( 'disable-comments', array(
        'default' => 0,
        'sanitize_callback' => 'freenews_sanitize_checkbox',
    ));
    $wp_customize->add_control( 'disable-comments', array(
        'priority'=>140,
        'label' => esc_html__('Hide Comments', 'freenews'),
        'section' => 'freenews_all_theme_options',
        'type' => 'checkbox',
    ));

    //Select Theme Layout
    $wp_customize->add_setting( 'select-layout', array(
        'default' => 'right',
        'sanitize_callback' => 'freenews_sanitize_select',
    ));
    $wp_customize->add_control( 'select-layout', array(
        'priority'=>200,
        'label' => esc_html__('Select Sidebar Layout', 'freenews'),
        'section' => 'freenews_all_theme_options',
        'type' => 'radio',
        'choices' => array(
            'right' => esc_html__( 'Default Right Sidebar','freenews' ),
            'left' => esc_html__( 'Left Sidebar','freenews' ),
        ),
    ));

    $wp_customize->add_setting( 'enable_sticky_menu', array(
        'default' => 1,
        'sanitize_callback' => 'freenews_sanitize_checkbox',
    ));
    $wp_customize->add_control( 'enable_sticky_menu', array(
        'priority'=>270,
        'label' => esc_html__('Enable Sticky Menu', 'freenews'),
        'section' => 'freenews_all_theme_options',
        'type' => 'checkbox',
    ));

    $wp_customize->add_setting( 'post-pagination', array(
        'default' => 'numeric',
        'sanitize_callback' => 'freenews_sanitize_select',
    ));
    $wp_customize->add_control( 'post-pagination', array(
        'priority'=>290,
        'label' => esc_html__('Post Pagination', 'freenews'),
        'section' => 'freenews_all_theme_options',
        'type' => 'radio',
        'choices' => array(
            'default' => esc_html__( 'Default','freenews' ),
            'numeric' => esc_html__( 'Numeric','freenews' ),
        ),
    ));
