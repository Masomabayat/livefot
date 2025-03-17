<?php
/**
 * FreeNews Highlighted Category
 *
 * @package freenews
 */

/**
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */

// Highlighted Category Left

$wp_customize->add_setting( 'highlighted_categories_left', array(
    'default'           => '',
    'sanitize_callback' => 'freenews_sanitize_select'
) );

$wp_customize->add_control( 'highlighted_categories_left', array(
    'section' => 'freenews_highlighted_category_section',
    'label'   => esc_html__( 'Select  highlighted Category Left', 'freenews' ),
    'description' => esc_html__('Selected below category will display Left side under Highlighted Section','freenews'),
    'choices' => freenews_cat_list(),
    'type' => 'select',
    'priority'    => 20,
    ));


// Highlighted Category Right

$wp_customize->add_setting( 'highlighted_cat_right_title_text', array(
    'default' => esc_html__('World News','freenews'),
    'sanitize_callback' => 'sanitize_text_field',
));
$wp_customize->add_control( 'highlighted_cat_right_title_text', array(
    'priority'=>50,
    'label' => esc_html__('Title', 'freenews'),
    'section' => 'freenews_highlighted_category_section',
    'type' => 'text',
));

$wp_customize->add_setting( 'highlighted_categories_right', array(
    'default'           => '',
    'sanitize_callback' => 'freenews_sanitize_multi_checkbox'
) );

$wp_customize->add_control( 
    new FreeNews_Customize_Multiple_Checkboxes_Control( 
        $wp_customize,
            'highlighted_categories_right', array(
                'section' => 'freenews_highlighted_category_section',
                'label'   => esc_html__( 'Select  highlighted Category Right', 'freenews' ),
                'description' => esc_html__('Selected below category will display Right side under Highlighted Section','freenews'),
                'choices' => freenews_cat_list_checkbox(),
                'priority'    => 60,
            ) 
    )
);