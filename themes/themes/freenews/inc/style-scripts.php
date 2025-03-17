<?php
 /**
 * Enqueue scripts and styles.
 *
 * @package freenews
 */

function freenews_scripts() {
	$select_main_banner_category = get_theme_mod('select_main_banner_category','');
	$slider_options = get_theme_mod('slider-options','main-banner');
	$disable_main_banner = get_theme_mod('disable_main_banner',0);
	$enable_sticky_menu = get_theme_mod('enable_sticky_menu',1);
	wp_enqueue_style( 'freenews-style', get_stylesheet_uri() );

	wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/assets/library/fontawesome/css/font-awesome.min.css' );

	wp_enqueue_style( 'freenews-google-fonts', freenews_fonts_url(), array(), null );

	wp_enqueue_script('freenews-global', get_template_directory_uri().'/assets/js/global.js', array('jquery'), true, false);

	wp_enqueue_script( 'freenews-navigation', get_template_directory_uri() . '/assets/js/navigation.min.js', array(), false, true );

	wp_enqueue_script( 'freenews-skip-link-focus-fix', get_template_directory_uri() . '/assets/js/skip-link-focus-fix.js', array(), false, true );

	wp_enqueue_script( 'ResizeSensor', get_template_directory_uri() . '/assets/library/sticky-sidebar/ResizeSensor.min.js', array(), false, true );

	wp_enqueue_script( 'theia-sticky-sidebar', get_template_directory_uri() . '/assets/library/sticky-sidebar/theia-sticky-sidebar.min.js', array(), false, true );

	wp_enqueue_script( 'slick', get_template_directory_uri() . '/assets/library/slick/slick.min.js', array(), false, true );

	wp_enqueue_script( 'freenews-slick-settings', get_template_directory_uri() . '/assets/library/slick/slick-settings.js', array(), false, true );

	if($enable_sticky_menu ==1 ){
		wp_enqueue_script( 'jquery-sticky', get_template_directory_uri() . '/assets/library/sticky/jquery.sticky.js', array(), false, true );
		wp_enqueue_script( 'freenews-sticky-settings', get_template_directory_uri() . '/assets/library/sticky/sticky-setting.js', array(), false, true );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_enqueue_script( 'marquee', get_template_directory_uri() . '/assets/library/marquee/jquery.marquee.min.js', array(), false, true );

	wp_enqueue_script( 'freenews-marquee-settings', get_template_directory_uri() . '/assets/library/marquee/marquee-settings.js', array(), false, true );
}

add_action( 'wp_enqueue_scripts', 'freenews_scripts' );

function freenews_admin_notice (){

  wp_enqueue_style( 'freenews-admin-css', get_template_directory_uri() . '/css/admin/admin.css' );

}

add_action( 'admin_enqueue_scripts', 'freenews_admin_notice' );

if ( ! function_exists( 'freenews_fonts_url' ) ) :
/**
 * Register Google fonts for FreeNews.
 *
 * Create your own freenews_fonts_url() function to override in a child theme.
 *
 *
 * @return string Google fonts URL for the theme.
 */
function freenews_fonts_url() {
	$fonts_url = '';
	$fonts     = array();
	$subsets   = 'latin,latin-ext';

	/* translators: If there are characters in your language that are not supported by Heebo, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Heebo font: on or off', 'freenews' ) ) {
		$fonts[] = 'Heebo:300,400,700';
	}

	/* translators: If there are characters in your language that are not supported by Arimo, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Arimo font: on or off', 'freenews' ) ) {
		$fonts[] = 'Arimo';
	}

	if ( $fonts ) {
		$fonts_url = add_query_arg( array(
			'family' => esc_attr( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
		), '//fonts.googleapis.com/css' );
	}

	return $fonts_url;
}
endif;

// Dynamic Category Color
if ( ! function_exists( 'freenews_freenews_category_colors' ) ) :
	function freenews_category_colors(){
		$categories_list =get_terms( 'category' );

		$output_css='';

		foreach ( $categories_list as $category_data ) {

			 $cat_data = get_theme_mod('cat_col_'.esc_html( strtolower( $category_data->name ) ) );

			 $cat_id = $category_data->term_id;
			 ?>
			 <?php if( $cat_data != '' ){

				 	$output_css .= '.cat-links .category-color-'.absint($cat_id).'{

						background-color:'.esc_attr($cat_data).';'.'

					}
					.secondary-menu .category-color-'.absint($cat_id). ' > a:hover,
					.secondary-menu .category-color-'.absint($cat_id). ' > a:focus,
					.secondary-menu > li.current-menu-item.category-color-'.absint($cat_id). ' > a, 
					.secondary-menu > li.current_page_item.category-color-'.absint($cat_id). ' > a, 
					.secondary-menu > li.current-menu-ancestor.category-color-1 > a {
						border-bottom-color:'.esc_attr($cat_data).';'.'

					}';
				}
		}
	wp_add_inline_style( 'freenews-style', $output_css );
	}
	add_action( 'wp_enqueue_scripts', 'freenews_category_colors', 10 );
endif;