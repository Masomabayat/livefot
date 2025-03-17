<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Newsever
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php
if (function_exists('wp_body_open')) {
    wp_body_open();
} else {
    do_action('wp_body_open');
} ?>
<?php
$enable_preloader = newsever_get_option('enable_site_preloader');
if (1 == $enable_preloader):
    ?>
    <div id="af-preloader">
        <div class="spinner">
            <div class="spinnerhalf spinnerhalf--left"></div>
            <div class="spinnerhalf spinnerhalf--right"></div>
        </div>
    </div>
<?php endif; ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'newsever'); ?></a>


    <?php

    do_action('newsever_action_header_section');

    do_action('newsever_action_front_page_main_section');
    ?>

        <div class="container-wrapper">
            <?php do_action('newsever_action_get_breadcrumb'); ?>
        </div>


    <?php
    if (is_singular('post')) {
        $single_post_featured_image_view = newsever_get_option('single_post_featured_image_view');
        /*if($single_post_featured_image_view == 'full'){*/
        do_action('newsever_action_single_header');
        /*}*/
    }
    ?>


    <div id="content" class="container-wrapper ">
