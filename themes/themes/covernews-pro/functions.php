<?php
/**
 * CoverNews functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package CoverNews
 */

if (!function_exists('covernews_setup')):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
    /**
     *
     */
    /**
     *
     */
    function covernews_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on CoverNews, use a find and replace
	 * to change 'covernews' to the name of your theme in all the template files.
	 */
	load_theme_textdomain('covernews', get_template_directory().'/languages');

	// Add default posts and comments RSS feed links to head.
	add_theme_support('automatic-feed-links');

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support('title-tag');

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support('post-thumbnails');


        add_image_size('covernews-slider-full', 1115, 715, true); // width, height, crop
        add_image_size('covernews-slider-center', 800, 500, true); // width, height, crop
        add_image_size('covernews-featured', 1024, 0, false ); // width, height, crop
        add_image_size('covernews-medium', 540, 340, true); // width, height, crop
        add_image_size('covernews-medium-square', 400, 250, true); // width, height, crop

    /*
     * Enable support for Post Formats on posts and pages.
     *
     * @link https://developer.wordpress.org/themes/functionality/post-formats/
     */
    add_theme_support( 'post-formats', array( 'image', 'video', 'gallery' ) );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(array(
			'aft-primary-nav' => esc_html__('Primary Menu', 'covernews'),
			'aft-top-nav' => esc_html__('Top Menu', 'covernews'),
			'aft-social-nav' => esc_html__('Social Menu', 'covernews'),
			'aft-footer-nav' => esc_html__('Footer Menu', 'covernews'),
		));

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support('html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		));

	// Set up the WordPress core custom background feature.
	add_theme_support('custom-background', apply_filters('covernews_custom_background_args', array(
				'default-color' => 'f5f5f5',
				'default-image' => '',
			)));

	// Add theme support for selective refresh for widgets.
	add_theme_support('customize-selective-refresh-widgets');

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support('custom-logo', array(
			'flex-width'  => true,
			'flex-height' => true,
		));

        /*theme updater*/
        if (is_admin()) {
            require (get_template_directory().'/inc/updater/theme-updater.php');
        }

        /*
        * Add theme support for gutenberg block
        */
        add_theme_support( 'align-wide' );
        add_theme_support( 'responsive-embeds' );


}
endif;
add_action('after_setup_theme', 'covernews_setup');



/**
 * function for google fonts
 */
if (!function_exists('covernews_fonts_url')):

    /**
     * Return fonts URL.
     *
     * @since 1.0.0
     * @return string Fonts URL.
     */
    function covernews_fonts_url() {

        $fonts_url = '';
        $fonts     = array();
        $subsets   = 'latin,latin-ext';

        $covernews_primary_font   = covernews_get_option('primary_font');
        $covernews_secondary_font = covernews_get_option('secondary_font');

        $covernews_fonts   = array();
        $covernews_fonts[] = $covernews_primary_font;
        $covernews_fonts[] = $covernews_secondary_font;

        $i = 0;
        for ($i = 0; $i < count($covernews_fonts); $i++) {

            if ('off' !== sprintf(_x('on', '%s font: on or off', 'covernews'), $covernews_fonts[$i])) {
                $fonts[] = $covernews_fonts[$i];
            }

        }

        if ($fonts) {
            $fonts_url = add_query_arg(array(
                'family' => urldecode(implode('|', $fonts)),
                'subset' => urldecode($subsets),
            ), 'https://fonts.googleapis.com/css');
        }

        return $fonts_url;
    }
endif;


/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function covernews_content_width() {
	$GLOBALS['content_width'] = apply_filters('covernews_content_width', 640);
}
add_action('after_setup_theme', 'covernews_content_width', 0);


/**
 * Load Init for Hook files.
 */
require get_template_directory() . '/inc/custom-style.php';

/**
 * Enqueue scripts and styles.
 */
function covernews_scripts() {

	$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG?'':'.min';
	wp_enqueue_style('font-awesome-v5', get_template_directory_uri().'/assets/font-awesome-v5/css/fontawesome-all'.$min.'.css');
	wp_enqueue_style('bootstrap', get_template_directory_uri().'/assets/bootstrap/css/bootstrap'.$min.'.css');
	wp_enqueue_style('slick', get_template_directory_uri().'/assets/slick/css/slick.css');
	wp_enqueue_style('sidr', get_template_directory_uri().'/assets/sidr/css/jquery.sidr.dark.css');
	wp_enqueue_style('magnific-popup', get_template_directory_uri().'/assets/magnific-popup/magnific-popup.css');
    wp_enqueue_style('sliderpro', get_template_directory_uri().'/assets/slider-pro/css/slider-pro'.$min.'.css');
    wp_enqueue_style('flatpickr-style', "https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css", [], '1.1');
	wp_enqueue_style('support-pages-style', get_template_directory_uri() . '/assets/css/support-pages.css', [], '1.1');

    $fonts_url = covernews_fonts_url();

    if (!empty($fonts_url)) {
        wp_enqueue_style('covernews-google-fonts', $fonts_url, array(), null);
    }

    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_style('covernews-woocommerce-style', get_template_directory_uri() . '/assets/woocommerce.css');
    }
	wp_enqueue_style('covernews-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ));
    wp_add_inline_style( 'covernews-style', covernews_custom_style() );


	wp_enqueue_script('covernews-navigation', get_template_directory_uri().'/js/navigation.js', array(), '20151215', true);
	wp_enqueue_script('covernews-skip-link-focus-fix', get_template_directory_uri().'/js/skip-link-focus-fix.js', array(), '20151215', true);

	wp_enqueue_script('slick', get_template_directory_uri().'/assets/slick/js/slick'.$min.'.js', array('jquery'), '', true);
	wp_enqueue_script('bootstrap', get_template_directory_uri().'/assets/bootstrap/js/bootstrap'.$min.'.js', array('jquery'), '', true);
	wp_enqueue_script('sidr', get_template_directory_uri().'/assets/sidr/js/jquery.sidr'.$min.'.js', array('jquery'), '', true);
	wp_enqueue_script('magnific-popup', get_template_directory_uri().'/assets/magnific-popup/jquery.magnific-popup'.$min.'.js', array('jquery'), '', true);
    wp_enqueue_script('sliderpro', get_template_directory_uri().'/assets/slider-pro/js/jquery.sliderPro'.$min.'.js', array('jquery'), '', true);
	wp_enqueue_script('matchheight', get_template_directory_uri().'/assets/jquery-match-height/jquery.matchHeight'.$min.'.js', array('jquery'), '', true);

    wp_enqueue_script('marquee', get_template_directory_uri() . '/assets/marquee/jquery.marquee.js', array('jquery'), '', true);

//    wp_enqueue_script('sticky-sidebar', get_template_directory_uri() . '/assets/theiaStickySidebar/theia-sticky-sidebar.min.js', array('jquery'), '', true);

    wp_enqueue_script('covernews-script', get_template_directory_uri().'/assets/script.js', array('jquery'), '', 1);
    wp_enqueue_script('flatpickr-script',"https://cdn.jsdelivr.net/npm/flatpickr", ['jquery'], '1.0');
	wp_enqueue_script('support-pages-script', get_template_directory_uri() . '/assets/js/support-pages.js', [], '1.3');
    wp_localize_script( 'support-pages-script', 'fdgajax', array('ajaxurl' => admin_url( 'admin-ajax.php' ),));
	
$disable_sticky_header_option = covernews_get_option('disable_sticky_header_option');
    if($disable_sticky_header_option == false ){  
    wp_enqueue_script('covernews-fixed-header-script', get_template_directory_uri().'/assets/fixed-header-script.js', array('jquery'), '', 1);
}

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'covernews_scripts');

add_action('wp_ajax_submit_support_form', 'support_form_send');
add_action('wp_ajax_nopriv_submit_support_form', 'support_form_send');

function support_form_send() {
	$admin_email = get_option('admin_email');
	$message;
	$to = 'info@livefootballcenter.com';
	$subject = $_POST['subject'];
	$headers[] = 'Content-type: text/plain; charset=utf-8';
	$headers[] = 'From:' . $admin_email;
	foreach($_POST as $key => $value) {
		$message .= "$key:\n $value \n\n";
	};
	$attachments;
	if(isset($_FILES['gift'])) {
		$wordpress_upload_dir = wp_upload_dir();
		$uploaded_file = $_FILES['gift']['tmp_name'];
        $file_name = $_FILES['gift']['name'];
		$file_name = wp_unique_filename($wordpress_upload_dir['path'], $file_name);
		$new_file_path = $wordpress_upload_dir['path'] . '/' . $file_name;
		move_uploaded_file($uploaded_file, $new_file_path);

		
		$attachments = [$new_file_path];

		if(wp_mail( $to, $subject, $message, $headers, $attachments )) {
			echo 'success';
		} else {
			echo 'error';
		};

		if (file_exists($new_file_path)) {
            unlink($new_file_path);
		}
        
	} else {
		if(wp_mail( $to, $subject, $message, $headers )) {
			echo 'success';
		} else {
			echo 'error';
		};
	};
	wp_die();
 }

/**
 * Enqueue admin scripts and styles.
 *
 * @since CoverNews 1.0.0
 */
function covernews__admin_scripts($hook){
    if ('widgets.php' === $hook) {
        wp_enqueue_media();
        wp_enqueue_script('covernews-widgets', get_template_directory_uri() . '/assets/widgets.js', array('jquery'), '1.0.0', true);
    }
}
add_action('admin_enqueue_scripts', 'covernews__admin_scripts');

/**
 * Functions which adds instagram posts in footer.
 */
//require get_template_directory().'/inc/template-instagram.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory().'/inc/template-tags.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory().'/inc/template-images.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory().'/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory().'/inc/customizer/customizer.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory().'/inc/custom-header.php';

/**
 * Customizer additions.
 */
require get_template_directory().'/inc/init.php';

/**
 * Customizer additions.
 */
require get_template_directory().'/inc/ocdi.php';



/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory().'/inc/jetpack.php';
}

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
    require get_template_directory() . '/inc/woocommerce.php';
}

/**
 * Descriptions on Header Menu
 * @author AF themes
 * @param string $item_output, HTML outputp for the menu item
 * @param object $item, menu item object
 * @param int $depth, depth in menu structure
 * @param object $args, arguments passed to wp_nav_menu()
 * @return string $item_output
 */
function covernews_header_menu_desc( $item_output, $item, $depth, $args ) {

    if( 'aft-primary-nav' == $args->theme_location  && $item->description )
        $item_output = str_replace( '</a>', '<span class="menu-description">' . $item->description . '</span></a>', $item_output );

    return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'covernews_header_menu_desc', 10, 4 );

if( function_exists('acf_add_options_page') ) {
    
    acf_add_options_page();
    
}