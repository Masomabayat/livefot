<?php

/**
 * Font and Color Option Panel
 *
 * @package CoverNews
 */

$default = covernews_get_default_theme_options();


// Setting - global content alignment of news.
$wp_customize->add_setting('global_site_mode',
    array(
        'default'           => $default['global_site_mode'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'covernews_sanitize_select',
    )
);

$wp_customize->add_control( 'global_site_mode',
    array(
        'label'       => esc_html__('Site Mode', 'covernews'),
        'description' => esc_html__('Select global site mode', 'covernews'),
        'section'     => 'colors',
        'type'        => 'select',
        'choices'               => array(
            'default' => esc_html__( 'Default', 'covernews' ),
            'dark' => esc_html__( 'Dark', 'covernews' ),

        ),
        'priority'    => 5,
    ));

//section title
$wp_customize->add_setting('top_header_section_title',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new CoverNews_Section_Title(
        $wp_customize,
        'top_header_section_title',
        array(
            'label' => esc_html__('Top header Colors', 'covernews'),
            'section' => 'colors',
            'priority' => 5,

        )
    )
);

// Setting - show_site_title_section.
$wp_customize->add_setting('top_header_background_color',
    array(
        'default'           => $default['top_header_background_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);
$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'top_header_background_color',
        array(
            'label'      => esc_html__( 'Background color', 'covernews' ),
            'section'    => 'colors',
            'settings'   => 'top_header_background_color',
            'priority' => 5,
            //'active_callback' => 'covernews_top_header_status'
        )
    )
);

// Setting - show_site_title_section.
$wp_customize->add_setting('top_header_text_color',
    array(
        'default'           => $default['top_header_text_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);

$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'top_header_text_color',
        array(
            'label'      => esc_html__( 'Texts color', 'covernews' ),
            'section'    => 'colors',
            'settings'   => 'top_header_text_color',
            'priority' => 5,
            //'active_callback' => 'covernews_top_header_status'
        )
    )
);


//section title
$wp_customize->add_setting('menu_section_title',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new CoverNews_Section_Title(
        $wp_customize,
        'menu_section_title',
        array(
            'label' => esc_html__('Menu Colors', 'covernews'),
            'section' => 'colors',
            'priority' => 5,

        )
    )
);

//Setting - archive content view of news.
$wp_customize->add_setting('main_navigation_background_color_mode',
    array(
        'default'           => $default['main_navigation_background_color_mode'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'covernews_sanitize_select',
    )
);

$wp_customize->add_control( 'main_navigation_background_color_mode',
    array(
        'label'       => esc_html__('Background Color Mode', 'covernews'),
        'section'     => 'colors',
        'type'        => 'select',
        'choices'               => array(
            'secondary-color' => esc_html__( 'Secondary Color - Default', 'covernews' ),
            'custom-color' => esc_html__( 'Custom Color', 'covernews' ),
        ),
        'priority'    => 5,
    ));


// Setting - slider_caption_bg_color.
$wp_customize->add_setting('main_navigation_custom_background_color',
    array(
        'default'           => $default['main_navigation_custom_background_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);
$wp_customize->add_control(

    new WP_Customize_Color_Control(
        $wp_customize,
        'main_navigation_custom_background_color',
        array(
            'label'    => esc_html__('Background Color', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 5,
            'active_callback' => 'covernews_main_navigation_background_color_mode_status'
        )
    )
);

// Setting - slider_caption_bg_color.
$wp_customize->add_setting('main_navigation_link_color',
    array(
        'default'           => $default['main_navigation_link_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);
$wp_customize->add_control(

    new WP_Customize_Color_Control(
        $wp_customize,
        'main_navigation_link_color',
        array(
            'label'    => esc_html__('Texts Color', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 5,
        )
    )
);

// Setting - slider_caption_bg_color.
$wp_customize->add_setting('main_navigation_badge_background',
    array(
        'default'           => $default['main_navigation_badge_background'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);
$wp_customize->add_control(

    new WP_Customize_Color_Control(
        $wp_customize,
        'main_navigation_badge_background',
        array(
            'label'    => esc_html__('Badge Background', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 5,
        )
    )
);

// Setting - slider_caption_bg_color.
$wp_customize->add_setting('main_navigation_badge_color',
    array(
        'default'           => $default['main_navigation_badge_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);
$wp_customize->add_control(

    new WP_Customize_Color_Control(
        $wp_customize,
        'main_navigation_badge_color',
        array(
            'label'    => esc_html__('Badge Color', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 5,
        )
    )
);





//section title
$wp_customize->add_setting('general_section_title',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new CoverNews_Section_Title(
        $wp_customize,
        'general_section_title',
        array(
            'label' => esc_html__('General Colors', 'covernews'),
            'section' => 'colors',
            'priority' => 5,

        )
    )
);




// Setting - primary_color.
$wp_customize->add_setting('primary_color',
    array(
    'default'           => $default['primary_color'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'sanitize_hex_color',
    )
);
$wp_customize->add_control(

    new WP_Customize_Color_Control(
        $wp_customize,
        'primary_color',
        array(
            'label'    => esc_html__('Primary Color', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 5,
            'active_callback' => 'covernews_global_site_mode_status'
        )
    )
);

// Setting - secondary_color.
$wp_customize->add_setting('secondary_color',
    array(
        'default'           => $default['secondary_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);

$wp_customize->add_control(

    new WP_Customize_Color_Control(
        $wp_customize,
        'secondary_color',
        array(
            'label'    => esc_html__('Secondary Color', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 5,
        )
    )
);


//section title
$wp_customize->add_setting('global_color_section_notice',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new CoverNews_Simple_Notice_Custom_Control(
        $wp_customize,
        'global_color_section_notice',
        array(
            'description' => esc_html__('Background Color will not be applicable for this mode.', 'covernews'),
            'section' => 'colors',
            'priority' => 10,
            'active_callback' => 'covernews_global_site_mode_dark_light_status'
        )
    )
);

// Setting - primary_color.
$wp_customize->add_setting('site_wide_title_color',
    array(
        'default'           => $default['site_wide_title_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);
$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'site_wide_title_color',
        array(
            'label'    => esc_html__('Global Title Color', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 100,
            'active_callback' => 'covernews_global_site_mode_status'
        )
    )
);

// Setting - secondary_color.
$wp_customize->add_setting('link_color',
    array(
        'default'           => $default['link_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);

$wp_customize->add_control(

    new WP_Customize_Color_Control(
        $wp_customize,
        'link_color',
        array(
            'label'    => esc_html__('Global Link Color', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 100,
            'active_callback' => 'covernews_global_site_mode_status'
        )
    )
);




// Setting - slider_caption_bg_color.
$wp_customize->add_setting('title_link_color',
    array(
        'default'           => $default['title_link_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);
$wp_customize->add_control(

    new WP_Customize_Color_Control(
        $wp_customize,
        'title_link_color',
        array(
            'label'    => esc_html__('Post Title Link Color', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 100,
            'active_callback' => 'covernews_global_site_mode_status'
        )
    )
);

// Setting - slider_caption_bg_color.
$wp_customize->add_setting('title_over_image_color',
    array(
        'default'           => $default['title_over_image_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);
$wp_customize->add_control(

    new WP_Customize_Color_Control(
        $wp_customize,
        'title_over_image_color',
        array(
            'label'    => esc_html__('Post Title Link Over Image Color', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 100,
            'active_callback' => 'covernews_global_site_mode_status'
        )
    )
);



// Setting - slider_caption_bg_color.
$wp_customize->add_setting('post_format_color',
    array(
        'default'           => $default['post_format_color'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
    )
);
$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'post_format_color',
        array(
            'label'    => esc_html__('Post Format Icon Color', 'covernews'),
            'section'  => 'colors',
            'type'     => 'color',
            'priority' => 100,
        )
    )
);

//========== category colors  options ===============


//section title
$wp_customize->add_setting('category_section_title',
    array(
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    new CoverNews_Section_Title(
        $wp_customize,
        'category_section_title',
        array(
            'label' => esc_html__('Category Colors', 'covernews'),
            'section' => 'colors',
            'priority' => 100,

        )
    )
);

for ($i = 1; $i <= 7; $i++) {
// Setting - slider_caption_bg_color.
    $wp_customize->add_setting('category_color_'.$i,
        array(
            'default' => $default['category_color_'.$i],
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'category_color_'.$i,
            array(
                'label' => sprintf(__('Category %d Color', 'covernews'), $i),
                'section' => 'colors',
                'type' => 'color',
                'priority' => 100,
            )
        )
    );

    
}

$wp_customize->add_setting('category_color_8',
        array(
            'default' => $default['category_color_8'],
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'category_color_8',
            array(
                'label' => sprintf(__('Category %d Background Color', 'covernews'), '8'),
                'description' => esc_html__('Category 8 will take #404040 as a text-color.', 'covernews'),
                'section' => 'colors',
                'type' => 'color',
                'priority' => 100,
            )
        )
    );


//============= Font Options ===================
// font Section.
$wp_customize->add_section('font_typo_section',
    array(
        'title'      => esc_html__('Fonts & Typography', 'covernews'),
        'priority'   => 10,
        'capability' => 'edit_theme_options',
        'panel'      => 'theme_option_panel',
    )
);

global $covernews_google_fonts;





// Setting - primary_font.
$wp_customize->add_setting('primary_font',
    array(
        'default'           => $default['primary_font'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'covernews_sanitize_select',
    )
);
$wp_customize->add_control('primary_font',
    array(
        'label'    => esc_html__('Primary Font', 'covernews'),
        'section'  => 'font_typo_section',
        'type'     => 'select',
        'choices'  => $covernews_google_fonts,
        'priority' => 100,
    )
);

// Setting - secondary_font.
$wp_customize->add_setting('secondary_font',
    array(
        'default'           => $default['secondary_font'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'covernews_sanitize_select',
    )
);
$wp_customize->add_control('secondary_font',
    array(
        'label'    => esc_html__('Secondary Font', 'covernews'),
        'section'  => 'font_typo_section',
        'type'     => 'select',
        'choices'  => $covernews_google_fonts,
        'priority' => 110,
    )
);

// Setting - secondary_font.
$wp_customize->add_setting('letter_spacing',
    array(
        'default'           => $default['letter_spacing'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('letter_spacing',
    array(
        'label'    => esc_html__('Global Letter Spacing', 'covernews'),
        'section'  => 'font_typo_section',
        'type'     => 'number',
        'priority' => 110,
    )
);

// Setting - secondary_font.
$wp_customize->add_setting('line_height',
    array(
        'default'           => $default['line_height'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('line_height',
    array(
        'label'    => esc_html__('Global Line height', 'covernews'),
        'section'  => 'font_typo_section',
        'type'     => 'number',
        'priority' => 110,
    )
);


// Setting - secondary_font.
$wp_customize->add_setting('main_banner_silder_caption_font_size',
    array(
        'default'           => $default['main_banner_silder_caption_font_size'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control('main_banner_silder_caption_font_size',
    array(
        'label'    => esc_html__('Main Banner Slider Caption Size', 'covernews'),
        'section'  => 'font_typo_section',
        'type'     => 'number',
        'priority' => 110,
    )
);


// Setting - secondary_font.
$wp_customize->add_setting('covernews_page_posts_title_font_size',
    array(
        'default'           => $default['covernews_page_posts_title_font_size'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control('covernews_page_posts_title_font_size',
    array(
        'label'    => esc_html__('Page/Posts Title Size', 'covernews'),
        'section'  => 'font_typo_section',
        'type'     => 'number',
        'priority' => 110,
    )
);

// Setting - secondary_font.
$wp_customize->add_setting('covernews_section_title_font_size',
    array(
        'default'           => $default['covernews_section_title_font_size'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control('covernews_section_title_font_size',
    array(
        'label'    => esc_html__('Global Section Title Size', 'covernews'),
        'section'  => 'font_typo_section',
        'type'     => 'number',
        'priority' => 110,
    )
);


// Setting - secondary_font.
$wp_customize->add_setting('spotlight_posts_title_font_size',
    array(
        'default'           => $default['spotlight_posts_title_font_size'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control('spotlight_posts_title_font_size',
    array(
        'label'    => esc_html__('Spotlight Post Title Size', 'covernews'),
        'section'  => 'font_typo_section',
        'type'     => 'number',
        'priority' => 110,
    )
);

// Setting - secondary_font.
$wp_customize->add_setting('general_title_font_size',
    array(
        'default'           => $default['general_title_font_size'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control('general_title_font_size',
    array(
        'label'    => esc_html__('General Post Title Size', 'covernews'),
        'section'  => 'font_typo_section',
        'type'     => 'number',
        'priority' => 110,
    )
);

// Setting - secondary_font.
$wp_customize->add_setting('general_font_size',
    array(
        'default'           => $default['general_font_size'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control('general_font_size',
    array(
        'label'    => esc_html__('General Font Size', 'covernews'),
        'section'  => 'font_typo_section',
        'type'     => 'number',
        'priority' => 110,
    )
);
