<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package True News
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function true_news_body_classes( $classes ) {

	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}
	
	// Add a class of no-sidebar when there is no sidebar present
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	if( !is_page_template('template-parts/true-news-home-page.php') ){
		
		$true_news_default = true_news_get_default_theme_options();
	    $sidebar = 'right-sidebar';
	    
	    if( is_singular('post') || is_singular('page') ){
	    	
	        $sidebar = esc_html( get_post_meta( get_the_ID(), 'true_news_post_sidebar_option', true ) );
	        if( $sidebar == '' || $sidebar == 'global-sidebar' ){
		        $sidebar = get_theme_mod( 'true_news_single_sidebar_layout',$true_news_default['true_news_single_sidebar_layout'] );
		    }
	    }

	    if( ( $sidebar == 'global-sidebar' && is_front_page() ) || 
	    	( is_home() && is_front_page() ) || 
	    	( !is_singular() && !is_front_page() ) || 
	    	( is_home() && !is_front_page() ) ){

            $sidebar = get_theme_mod( 'true_news_global_sidebar_layout',$true_news_default['true_news_global_sidebar_layout'] );
        
        }

        if( is_404() || is_page_template('template-parts/true-news-read-later-posts.php') || is_page_template('template-parts/true-news-trending-posts.php') ){
	    	$sidebar = 'no-sidebar';
	    }
	    
	    if( $sidebar == 'left-right-content' || $sidebar == 'content-left-right' || $sidebar == 'both-sidebar' ){
	    	$classes[] = 'column-layout-3';
	    }

	    if( $sidebar == '' || $sidebar == 'left-sidebar' || $sidebar == 'right-sidebar' ){
	    	$classes[] = 'column-layout-2';
	    }

	    $classes[] = $sidebar;

	}

	if( is_page_template('template-parts/true-news-home-page.php') ){

		$true_news_default = true_news_get_default_theme_options();
		$true_news_home_sections = get_theme_mod( 'true_news_home_sections', json_encode( $true_news_default['true_news_home_sections'] ) );

		$true_news_home_sections = json_decode( $true_news_home_sections );
		$repeat_times = 1;

		foreach ( $true_news_home_sections as $true_news_home_section ) {

		    $home_section_type = isset( $true_news_home_section->home_section_type ) ? $true_news_home_section->home_section_type : '';
		    switch ($home_section_type) {

		    	case 'latest-posts':

		            $ed_latest_news = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
		            if ( $ed_latest_news == 'yes' ) {

		            	$sidebar = isset( $true_news_home_section->sidebar_layout ) ? $true_news_home_section->sidebar_layout : 'global-sidebar';
		            	if( $sidebar == '' || $sidebar == 'global-sidebar' ){
					        $sidebar = get_theme_mod( 'true_news_global_sidebar_layout',$true_news_default['true_news_global_sidebar_layout'] );
					    }

					    if( $sidebar == 'left-right-content' || $sidebar == 'content-left-right' || $sidebar == 'both-sidebar' ){
					    	$classes[] = 'column-layout-3';
					    }

					    if( $sidebar == '' || $sidebar == 'left-sidebar' || $sidebar == 'right-sidebar' ){
					    	$classes[] = 'column-layout-2';
					    }

					    $classes[] = $sidebar;

		            }
		        break;

		        default:
		        break;

		    }
		    $repeat_times++;
		}

	}

	if( get_header_image() ){

		 $classes[] = 'twp-has-header-image';

	}

	$background_image = get_theme_mod('background_image');
	if( $background_image ){

		$classes[] = 'custom-background-image';

	}

	return $classes;

}
add_filter( 'body_class', 'true_news_body_classes' );
