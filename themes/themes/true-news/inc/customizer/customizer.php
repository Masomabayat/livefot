<?php
/**
 * True News Theme Customizer
 *
 * @package True News
 */

//customizer
require get_template_directory().'/inc/customizer/core/default.php';
/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function true_news_customize_register( $wp_customize ) {
	
	$true_news_default = true_news_get_default_theme_options();
	// Load custom controls.
	require get_template_directory().'/inc/customizer/core/control.php';

	// Load customize sanitize.
	require get_template_directory().'/inc/customizer/core/sanitize.php';
	
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	//Ticker News
	require get_template_directory() . '/inc/customizer/theme-option/pagination.php';
	require get_template_directory() . '/inc/customizer/theme-option/ticker-news.php';
	require get_template_directory() . '/inc/customizer/theme-option/header.php';
	require get_template_directory() . '/inc/customizer/home-content/home-content.php';
	require get_template_directory() . '/inc/customizer/theme-option/footer.php';
	require get_template_directory() . '/inc/customizer/theme-option/preloder.php';
	require get_template_directory() . '/inc/customizer/theme-option/general.php';
	require get_template_directory() . '/inc/customizer/theme-option/sidebar-layout.php';
	require get_template_directory() . '/inc/customizer/theme-option/single.php';
	require get_template_directory() . '/inc/customizer/theme-option/breadcrumb.php';
	require get_template_directory() . '/inc/customizer/theme-option/read-later.php';

	$wp_customize->add_setting(
        'twp_general_text_color',
        array(
            'default'           => $true_news_default['twp_general_text_color'],
            'sanitize_callback' => 'sanitize_hex_color',
            'priority' => 1
        )
    );

    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'twp_general_text_color',
            array(
                'settings'      => 'twp_general_text_color',
                'section'       => 'colors',
                'label'         => esc_html__('General Text Color', 'true-news' ),
            )
        )
    );

    $wp_customize->add_setting(
        'twp_general_heading_text_color',
        array(
            'default'           => $true_news_default['twp_general_heading_text_color'],
            'sanitize_callback' => 'sanitize_hex_color',
            'priority' => 1
        )
    );

    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'twp_general_heading_text_color',
            array(
                'settings'      => 'twp_general_heading_text_color',
                'section'       => 'colors',
                'label'         => esc_html__('Heading Text Color', 'true-news' ),
            )
        )
    );

    $wp_customize->add_setting(
        'twp_general_heading_link_color',
        array(
            'default'           => $true_news_default['twp_general_heading_link_color'],
            'sanitize_callback' => 'sanitize_hex_color',
            'priority' => 1
        )
    );

    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'twp_general_heading_link_color',
            array(
                'settings'      => 'twp_general_heading_link_color',
                'section'       => 'colors',
                'label'         => esc_html__('Link Color', 'true-news' ),
            )
        )
    );

	// Banner Text.
	$wp_customize->add_setting( 'header_medai_text',
	    array(
	    'capability'        => 'edit_theme_options',
	    'sanitize_callback' => 'sanitize_text_field',
	    )
	);
	$wp_customize->add_control( 'header_medai_text',
	    array(
	    'label'    => esc_html__( 'Banner Text', 'true-news' ),
	    'section'  => 'header_image',
	    'type'     => 'text',
	    )
	);

	// Banner Text Link.
	$wp_customize->add_setting( 'header_medai_text_link',
		array(
		'default'           => '',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control( 'header_medai_text_link',
		array(
		'label'    => esc_html__( 'Banner Link', 'true-news' ),
		'section'  => 'header_image',
		'type'     => 'text',
		)
	);

	$wp_customize->add_setting(
	    'header_banner_text_align',
	    array(
	        'default' 			=> $true_news_default['header_banner_text_align'],
	        'capability'        => 'edit_theme_options',
	        'sanitize_callback' => 'true_news_sanitize_select'
	    )
	);
	$wp_customize->add_control(
	    new True_News_Custom_Radio_Image_Control( 
	        $wp_customize,
	        'header_banner_text_align',
	        array(
	            'settings'      => 'header_banner_text_align',
	            'section'       => 'header_image',
	            'label'         => esc_html__( 'Banner Text Align', 'true-news' ),
	            'choices'       => array(
	                'left'  => 'dashicons-editor-alignleft',
	                'center'  => 'dashicons-editor-aligncenter',
	                'right'  => 'dashicons-editor-alignright',
	            )
	        )
	    )
	);

	$wp_customize->add_setting('tab_section_title',
	    array(
	        'default' => $true_news_default['tab_section_title'],
	        'capability' => 'edit_theme_options',
	        'sanitize_callback' => 'sanitize_text_field',
	    )
	);
	$wp_customize->add_control('tab_section_title',
	    array(
	        'label' => esc_html__('Latest Posts Section title', 'true-news'),
	        'section' => 'static_front_page',
	        'type' => 'text',
	    )
	);
	$wp_customize->add_setting('ed_popular_posts',
	    array(
	        'default' => $true_news_default['ed_popular_posts'],
	        'capability' => 'edit_theme_options',
	        'sanitize_callback' => 'true_news_sanitize_checkbox',
	    )
	);
	$wp_customize->add_control('ed_popular_posts',
	    array(
	        'label' => esc_html__('Enable Popular Tab', 'true-news'),
	        'description'       => esc_html__( 'Popular posts will be display on the basis of post comment.', 'true-news' ),
	        'section' => 'static_front_page',
	        'type' => 'checkbox',
	    )
	);
	$wp_customize->add_setting('ed_trending_posts',
	    array(
	        'default' => $true_news_default['ed_trending_posts'],
	        'capability' => 'edit_theme_options',
	        'sanitize_callback' => 'true_news_sanitize_checkbox',
	    )
	);
	$wp_customize->add_control('ed_trending_posts',
	    array(
	        'label' => esc_html__('Enable Trending Tab', 'true-news'),
	        'description'       => esc_html__( 'Trending posts will be display on the basis of post views. Please install Booster Extension Plugin for post visit count.', 'true-news' ),
	        'section' => 'static_front_page',
	        'type' => 'checkbox',
	    )
	);

	// Register custom section types.
	$wp_customize->register_section_type( 'True_News_Customize_Section_Upsell' );

	// Register sections.
	$wp_customize->add_section(
		new True_News_Customize_Section_Upsell(
			$wp_customize,
			'theme_upsell',
			array(
				'title'    => esc_html__( 'True News Pro', 'true-news' ),
				'pro_text' => esc_html__( 'Upgrade To Pro', 'true-news' ),
				'pro_url'  => esc_url('https://www.themeinwp.com/theme/true-news-pro/'),
				'priority'  => 1,
			)
		)
	);

}
add_action( 'customize_register', 'true_news_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function true_news_customize_preview_js() {

	wp_enqueue_script( 'true-news-customizer', get_template_directory_uri() . '/assets/js/customizer.js', array( 'customize-preview' ), '20151215', true );

}
add_action( 'customize_preview_init', 'true_news_customize_preview_js' );

function true_news_customizer_script() {

	wp_enqueue_style('true-news-repeater', get_template_directory_uri() . '/assets/css/repeater.css');
	wp_enqueue_style('true-news-customizer', get_template_directory_uri() . '/assets/css/customizer.css');
	wp_enqueue_script( 'true-news-repeater', get_template_directory_uri() . '/assets/js/repeater.js', array( 'jquery','customize-controls' ), '20151215', 
		true );
	wp_enqueue_script('jquery-ui-button');
	wp_enqueue_script( 'true-news-customizer-custom', get_template_directory_uri() . '/assets/js/customizer-custom.js', array( 'jquery' ), '20151215', true );

	wp_localize_script(
        'true-news-repeater', 
        'true_news_repeater',
        array(
            'optionns'   =>  "<option selected='selected' value='block-1'>". esc_html__('Block 1','true-news')."</option>
            <option value='block-2'>". esc_html__('Section Block 2','true-news')."</option></option>
            <option value='block-3'>". esc_html__('Section Block 3','true-news')."</option></option>
            <option value='block-4'>". esc_html__('Section Block 4','true-news')."</option>
            <option value='block-5'>". esc_html__('Section Block 5','true-news')."</option>
            <option value='block-6'>". esc_html__('Section Slider Block','true-news')."</option>
            <option value='block-7'>". esc_html__('Section Carousel Block','true-news')."</option>
            <option value='block-8'>". esc_html__('Section Video Block','true-news')."</option>
            <option value='recommended'>". esc_html__('Section Recommended Block','true-news')."</option>
            <option value='latest-posts'>". esc_html__('Section Latest Posts Block','true-news')."</option>
            <option value='advertise-area'>". esc_html__('Advertise Block','true-news')."</option>",
             'new_section'   =>  esc_html__('New Section','true-news'),
             'upload_image'   =>  esc_html__('Choose Image','true-news'),
             'use_imahe'   =>  esc_html__('Select','true-news'),
         )
    );

}
add_action( 'customize_controls_enqueue_scripts', 'true_news_customizer_script' );

function true_news_preloader_callback( $control ){
    
    $twp_perloader_layout = $control->manager->get_setting( 'twp_perloader_layout' )->value();
    if( $twp_perloader_layout == 'advanced' ){
    	return true;
    }
    
    return false;
}

function true_news_trending_posts_ac( $control ){
    
    $twp_perloader_layout = $control->manager->get_setting( 'ed_header_trending' )->value();
    if( $twp_perloader_layout ){
    	return true;
    }
    
    return false;
}

function true_news_pinpost_posts_ac( $control ){
    
    $twp_perloader_layout = $control->manager->get_setting( 'ed_read_later_posts' )->value();
    if( $twp_perloader_layout ){
    	return true;
    }
    
    return false;
}

function true_news_comment_toggle_button_title_ac( $control ){
    
    $twp_perloader_layout = $control->manager->get_setting( 'ed_toggle_comment' )->value();
    if( $twp_perloader_layout ){
    	return true;
    }
    
    return false;
}

function true_news_ticker_post_ac( $control ){
    
    $twp_perloader_layout = $control->manager->get_setting( 'ed_ticker_post' )->value();
    if( $twp_perloader_layout ){
    	return true;
    }
    
    return false;
}