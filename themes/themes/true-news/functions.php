<?php
/**
 * True News functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package True News
 */

if ( ! function_exists( 'true_news_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function true_news_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on components, use a find and replace
	 * to change 'true-news' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'true-news', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'lazy-load-iframes' );
	add_image_size('true-news-medium', 480, 298, true );
	add_image_size('true-news-small', 200, 150, true );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'top-menu' => esc_html__( 'Top Menu', 'true-news' ),
		'primary-menu' => esc_html__( 'Primary Menu', 'true-news' ),
		'footer-menu' => esc_html__( 'Footer Menu', 'true-news' ),
		'social-menu' => esc_html__( 'Social Menu', 'true-news' ),
	) );

	/**
	 * Add support for core custom logo.
	 */
	add_theme_support( 'custom-logo', array(
		'height'      => 200,
		'width'       => 200,
		'flex-width'  => true,
		'flex-height' => true,
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 * See https://developer.wordpress.org/themes/functionality/post-formats/
	 */

	add_theme_support( 'post-formats', array(
        'video',
        'audio',
        'gallery',
        'quote',
        'image'
    ) );

	// Set up the WordPress core custom background feature.
	$true_news_defaults = array(
	    'default-color'          => 'ffffff',
	    'default-image' => '',
	);
	add_theme_support( 'custom-background', $true_news_defaults );


}
endif;
add_action( 'after_setup_theme', 'true_news_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function true_news_content_width() {

	$GLOBALS['content_width'] = apply_filters( 'true_news_content_width', 1000 );

}
add_action( 'after_setup_theme', 'true_news_content_width', 0 );

/**
 * Return early if Custom Logos are not available.
 *
 * @todo Remove after WP 4.7
 */
function true_news_the_custom_logo() {

	if ( ! function_exists( 'the_custom_logo' ) ) {
		return;
	} else {
		the_custom_logo();
	}

}


/**
 * Enqueue scripts and styles.
 */
function true_news_scripts() {

	$fonts_url = true_news_fonts_url();
    if (!empty($fonts_url)) {
        wp_enqueue_style( 'true-news-google-fonts', $fonts_url, array(), null);
    }
    wp_enqueue_style( 'ionicons', get_template_directory_uri() . '/assets/libraries/ionicons/css/ionicons.min.css' );
    wp_enqueue_style( 'magnific-popup', get_template_directory_uri() . '/assets/libraries/magnific-popup/magnific-popup.css' );
    wp_enqueue_style( 'slick', get_template_directory_uri() . '/assets/libraries/slick/css/slick.min.css' );
    wp_enqueue_style( 'true-news-style', get_stylesheet_uri() );

    wp_enqueue_script( 'true-news-skip-link-focus-fix', get_template_directory_uri() . '/assets/js/skip-link-focus-fix.js', array(), '20151215', true );
    wp_enqueue_script( 'slick', get_template_directory_uri() . '/assets/libraries/slick/js/slick.min.js', array('jquery'), '', true );
    wp_enqueue_script( 'magnific-popup', get_template_directory_uri() . '/assets/libraries/magnific-popup/jquery.magnific-popup.min.js', array('jquery'), '', true );
    wp_enqueue_script( 'theiaStickySidebar', get_template_directory_uri() . '/assets/libraries/theiaStickySidebar/theia-sticky-sidebar.js', array('jquery'), '', true );
    wp_enqueue_script( 'imagesloaded' );
    wp_enqueue_script( 'masonry' );
    wp_enqueue_script( 'true-news-pagination', get_template_directory_uri() . '/assets/js/pagination.js', array('jquery'), '', 1 );
	wp_enqueue_script( 'true-news-ajax', get_template_directory_uri() . '/assets/js/ajax.js', array('jquery'), '', true );
    wp_enqueue_script( 'true-news-script', get_template_directory_uri() . '/assets/js/script.js', array( 'jquery', 'wp-mediaelement'), '', true );

    $ajax_nonce = wp_create_nonce('true_news_ajax_nonce');

    wp_localize_script( 
        'true-news-ajax', 
        'true_news_ajax',
        array(
            'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
            'loadmore'   => esc_html__( 'Load More Posts', 'true-news' ),
            'nomore'     => esc_html__( 'No More Posts', 'true-news' ),
            'loading'    => esc_html__( 'Loading...', 'true-news' ),
            'ajax_nonce' => $ajax_nonce,
         )
    );

	// Global Query
    if( is_front_page() ){

    	$posts_per_page = absint( get_option('posts_per_page') );
        $paged = ( get_query_var( 'page' ) ) ? absint( get_query_var( 'page' ) ) : 1;
        $posts_args = array(
            'posts_per_page'        => $posts_per_page,
            'paged'                 => $paged,
        );
        $posts_qry = new WP_Query( $posts_args );
        $max = $posts_qry->max_num_pages;

    }else{
        global $wp_query;
        $max = $wp_query->max_num_pages;
        $paged = ( get_query_var( 'paged' ) > 1 ) ? get_query_var( 'paged' ) : 1;
    }

    $true_news_default = true_news_get_default_theme_options();
    $true_news_pagination_layout = get_theme_mod( 'true_news_pagination_layout',$true_news_default['true_news_pagination_layout'] );

    // Pagination Data
    wp_localize_script( 
	    'true-news-pagination', 
	    'true_news_pagination',
	    array(
	        'paged'  => absint( $paged ),
	        'maxpage'   => absint( $max ),
	        'nextLink'   => next_posts( $max, false ),
	        'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
	        'loadmore'   => esc_html__( 'Load More Posts', 'true-news' ),
	        'nomore'     => esc_html__( 'No More Posts', 'true-news' ),
	        'loading'    => esc_html__( 'Loading...', 'true-news' ),
	        'pagination_layout'   => esc_html( $true_news_pagination_layout ),
	        'ajax_nonce' => $ajax_nonce,
	     )
	);


    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'true_news_scripts' );


/**
 * Admin enqueue scripts and styles.
 */
if ( !function_exists('true_news_admin_scripts') ):

    function true_news_admin_scripts(){

        wp_enqueue_style('true-news-admin', get_template_directory_uri() . '/assets/css/admin.css');

        // Enqueue Script Only On Widget Page.
        wp_enqueue_media();
        wp_enqueue_script('true-news-custom-widgets', get_template_directory_uri() . '/assets/js/widget.js', array('jquery'), '1.0.0', true);

    }

endif;

add_action('admin_enqueue_scripts', 'true_news_admin_scripts');

//* Add description to menu items
add_filter( 'walker_nav_menu_start_el', 'true_news_add_description', 10, 2 );
function true_news_add_description( $item_output, $item ) {
    $description = $item->post_content;
    if (('' !== $description) && (' ' !== $description) ) {
        return preg_replace( '/(<a.*)</', '$1' . '<span class="menu-description">' . esc_html( $description ) . '</span><', $item_output) ;
    }
    else {
        return $item_output;
    };
}

add_filter('wp_nav_menu_items', 'true_news_add_admin_link', 1, 2);
function true_news_add_admin_link($items, $args){
    if( $args->theme_location == 'primary-menu' ){
        $item = '<li class="brand-home"><a title="'.esc_html__('Home','true-news').'" href="'. esc_url( home_url() ) .'">' . "<span class='icon ion-ios-home'></span>" . '</a></li>';
        $items = $item . $items;
    }
    return $items;
}
/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function true_news_pingback_header()
{
    if ( is_singular() && pings_open() ) {
        printf('<link rel="pingback" href="%s">', esc_url( get_bloginfo('pingback_url') ) );
    }
}

add_action('wp_head', 'true_news_pingback_header');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom Function.
 */
require get_template_directory() . '/inc/custom-functions.php';

/**
 * Recommended Plugins
 */
require get_template_directory() . '/inc/recommended-plugins.php';


/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/body-classes.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Sidebar Metabox
 */
require get_template_directory() . '/inc/metabox.php';

/**
 * Pagination
 */
require get_template_directory() . '/inc/pagination.php';

/**
 * Widget Register
 */
require get_template_directory() . '/inc/widgets/widgets.php';

/**
 * Ajax Post Load
 */
require get_template_directory() . '/inc/ajax.php';

/**
 * Read Later
 */
require get_template_directory() . '/inc/read-later.php';

/**
 * Single Post related posts
 */
require get_template_directory() . '/inc/related-posts.php';

/**
 * Header Functions
 */
require get_template_directory() . '/inc/header.php';

/**
 * Footer Functions
 */
require get_template_directory() . '/inc/footer.php';

/**
 * SVG Icons
 */
require get_template_directory() . '/inc/svg-icons.php';

/**
 * Breadcrumbs
 */
require get_template_directory() . '/assets/libraries/breadcrumbs/breadcrumbs.php';


/**
 * Home Latest Posts
 */
require get_template_directory() . '/components/sections/true-news-latest-posts.php';

/**
 * Block 1
 */
require get_template_directory() . '/components/sections/true-news-block-1.php';

/**
 * Block 2
 */
require get_template_directory() . '/components/sections/true-news-block-2.php';

/**
 * Block 3
 */
require get_template_directory() . '/components/sections/true-news-block-3.php';

/**
 * Block 4
 */
require get_template_directory() . '/components/sections/true-news-block-4.php';

/**
 * Block 5
 */
require get_template_directory() . '/components/sections/true-news-block-5.php';

/**
 * Block 6
 */
require get_template_directory() . '/components/sections/true-news-slider.php';

/**
 * Block 7
 */
require get_template_directory() . '/components/sections/true-news-carousel.php';

/**
 * Block 8
 */
require get_template_directory() . '/components/sections/true-news-video.php';

/**
 * Recommended
 */
require get_template_directory() . '/components/sections/true-news-recommended.php';
require get_template_directory() . '/components/sections/true-news-advertise.php';
/**
 * Dynamic Color
 */
require get_template_directory() . '/assets/css/style.php';