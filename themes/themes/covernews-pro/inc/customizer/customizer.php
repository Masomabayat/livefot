<?php
/**
 * CoverNews Theme Customizer
 *
 * @package CoverNews
 */

if (!function_exists('covernews_get_option')):
/**
 * Get theme option.
 *
 * @since 1.0.0
 *
 * @param string $key Option key.
 * @return mixed Option value.
 */
function covernews_get_option($key) {

	if (empty($key)) {
		return;
	}

	$value = '';

	$default       = covernews_get_default_theme_options();
	$default_value = null;

	if (is_array($default) && isset($default[$key])) {
		$default_value = $default[$key];
	}

	if (null !== $default_value) {
		$value = get_theme_mod($key, $default_value);
	} else {
		$value = get_theme_mod($key);
	}

	return $value;
}
endif;

// Load customize default values.
require get_template_directory().'/inc/customizer/customizer-callback.php';

// Load customize default values.
require get_template_directory().'/inc/customizer/customizer-default.php';

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function covernews_customize_register($wp_customize) {

	// Load customize controls.
	require get_template_directory().'/inc/customizer/customizer-control.php';

	// Load customize sanitize.
	require get_template_directory().'/inc/customizer/customizer-sanitize.php';

	$wp_customize->get_setting('blogname')->transport         = 'postMessage';
	$wp_customize->get_setting('blogdescription')->transport  = 'postMessage';
	$wp_customize->get_setting('header_textcolor')->transport = 'postMessage';

	if (isset($wp_customize->selective_refresh)) {
		$wp_customize->selective_refresh->add_partial('blogname', array(
				'selector'        => '.site-title a',
				'render_callback' => 'covernews_customize_partial_blogname',
			));
		$wp_customize->selective_refresh->add_partial('blogdescription', array(
				'selector'        => '.site-description',
				'render_callback' => 'covernews_customize_partial_blogdescription',
			));
	}

    $default = covernews_get_default_theme_options();

    // Setting - secondary_font.
    $wp_customize->add_setting('site_title_font_size',
        array(
            'default'           => $default['site_title_font_size'],
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control('site_title_font_size',
        array(
            'label'    => esc_html__('Site Title Size', 'covernews'),
            'section'  => 'title_tagline',
            'type'     => 'number',
            'priority' => 50,
        )
    );
    // use get control
    $wp_customize->get_control( 'header_textcolor')->label = __( 'Site Title/Tagline Color', 'covernews' );
    $wp_customize->get_control( 'header_textcolor')->section = 'title_tagline';


    // Setting - select_main_banner_section_mode.
    $wp_customize->add_setting('select_header_image_mode',
        array(
            'default'           => $default['select_header_image_mode'],
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'covernews_sanitize_select',
        )
    );

    $wp_customize->add_control( 'select_header_image_mode',
        array(
            'label'       => esc_html__('Header Image Mode', 'covernews'),
            'description'       => esc_html__('Image visibility may vary as per the mode', 'covernews'),
            'section'     => 'header_image',
            'type'        => 'select',
            'choices'               => array(
                'default' => esc_html__( "Set as Background", 'covernews' ),
                'full' => esc_html__( "Show Full Image", 'covernews' ),
            ),
            'priority'    => 50
        ));

    // Setting - header overlay.
    $wp_customize->add_setting('disable_header_image_tint_overlay',
        array(
            'default'           => $default['disable_header_image_tint_overlay'],
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'covernews_sanitize_checkbox',
        )
    );

    $wp_customize->add_control('disable_header_image_tint_overlay',
        array(
            'label'    => esc_html__('Disable Image Tint/Overlay', 'covernews'),
            'section'  => 'header_image',
            'type'     => 'checkbox',
            'priority' => 50,
        )
    );




	/*theme option panel info*/
	require get_template_directory().'/inc/customizer/theme-options.php';



}
add_action('customize_register', 'covernews_customize_register');

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function covernews_customize_partial_blogname() {
	bloginfo('name');
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function covernews_customize_partial_blogdescription() {
	bloginfo('description');
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function covernews_customize_preview_js() {
	wp_enqueue_script('covernews-customizer', get_template_directory_uri().'/js/customizer.js', array('customize-preview'), '20151215', true);

}
add_action('customize_preview_init', 'covernews_customize_preview_js');

function covernews_customizer_css() {
    wp_enqueue_style('covernews-custom-controls-css', trailingslashit(get_template_directory_uri()) . 'inc/customizer/css/customizer.css', array(), '1.0', 'all');
}
add_action( 'customize_controls_enqueue_scripts', 'covernews_customizer_css',0 );

