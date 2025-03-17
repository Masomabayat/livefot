<?php
/**
 * The template for displaying home page.
 * Template Name: Front-page Template
 * @package Newsever
 */

get_header();
if ( 'posts' == get_option( 'show_on_front' ) ) {
    include( get_home_template() );
} else {

    /**
     * newsever_action_sidebar_section hook
     * @since Newsever 1.0.0
     *
     * @hooked newsever_front_page_section -  20
     * @sub_hooked newsever_front_page_section -  20
     */
    do_action('newsever_front_page_section');


}
get_footer();