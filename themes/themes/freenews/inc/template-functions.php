<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package freenews
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function freenews_body_classes( $classes ) {
    $select_layout = get_theme_mod('select-layout','right');

    // Adds a class of hfeed to non-singular pages.
    if ( ! is_singular() ) {
        $classes[] = 'hfeed';
    }

    //has header image
    if(has_header_image()){
        $classes[] = 'has-header-image';
    }

    //left sidebar
    if($select_layout=='left'){
        $classes[] = 'left-sidebar';

    }

    if ( is_active_sidebar( 'freenews-template-left' ) ) {
        $classes[] = 'lw-area';
    }

    if ( is_active_sidebar( 'freenews-template-right' ) ) {
        $classes[] = 'rw-area';
    }

        // Add class if sidebar is used.
    if ( is_active_sidebar( 'sidebar-1' ) ) {
        $classes[] = 'has-sidebar';
    }

	return $classes;
}
add_filter( 'body_class', 'freenews_body_classes' );

/**
 * Adds custom class to the array of posts classes.
 */
function freenews_post_classes( $classes, $class, $post_id ) {
    $classes[] = 'entry';

    return $classes;
}
add_filter( 'post_class', 'freenews_post_classes', 10, 3 );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function freenews_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
add_action( 'wp_head', 'freenews_pingback_header' );

// Default Category Lists for Dropdown

if( !function_exists( 'freenews_cat_list' ) ):
    function freenews_cat_list() {
        $freenews_args = array(
            'type'       => 'post',
            'taxonomy'   => 'category',
        );
        $freenews_cat_lists = get_categories( $freenews_args );
        $freenews_cat_list = array('' => esc_html__('--Select--','freenews'));
        foreach( $freenews_cat_lists as $category ) {
            $freenews_cat_list[esc_attr( $category->slug )] = esc_html( $category->name );
        }
        return $freenews_cat_list;
    }
endif;


// Default Category Lists for mulitple checkbox

if( !function_exists( 'freenews_cat_list_checkbox' ) ):
    function freenews_cat_list_checkbox() {
        $freenews_args = array(
            'type'       => 'post',
            'taxonomy'   => 'category',
        );
        $freenews_cat_lists = get_categories( $freenews_args );
        foreach( $freenews_cat_lists as $category ) {
            $freenews_cat_list_checkbox[esc_attr( $category->slug )] = esc_html( $category->name );
        }
        return $freenews_cat_list_checkbox;
    }
endif;

//front page category list

if( !function_exists( 'freenews_frontpage_cat_list' ) ):
    function freenews_frontpage_cat_list() {
        $freenews_args = array(
            'type'       => 'post',
            'taxonomy'   => 'category',
        );
        $freenews_frontpage_cat_lists = get_categories( $freenews_args );
        foreach( $freenews_frontpage_cat_lists as $category ) {
            $freenews_frontpage_cat_list[esc_attr( $category->term_id )] = esc_html( $category->name );
        }
        return $freenews_frontpage_cat_list;
    }
endif;

//Exclude posts from home page

function freenews_exclude_homepage($query) {
    $front_page_categories = get_theme_mod('front_page_categories','');
    if ( is_array( $front_page_categories ) && !in_array( 0, $front_page_categories ) ) {
        if ( $query->is_home() && $query->is_main_query() ) {
            $query->query_vars['category__in'] = $front_page_categories;
        }
    }
}
add_action('pre_get_posts', 'freenews_exclude_homepage');

