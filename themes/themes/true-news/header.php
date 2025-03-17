<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package True News
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php
if( function_exists('wp_body_open') ){
    wp_body_open();
}

true_news_preloader(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'true-news'); ?></a>
    <div class="twp-site-wrapper">

        <?php true_news_header_image(); ?>

        <div class="twp-site-content">

        <?php
        $true_news_default = true_news_get_default_theme_options();
        $true_news_header_style = esc_html( get_theme_mod( 'select_header_style',$true_news_default['select_header_style'] ) );
        
        if( $true_news_header_style == 'header-style-default' ){

            get_template_part('components/header/header', 'main');

        } else {

            get_template_part('components/header/header', 'secondary');

        } ?>
        
        <div id="content" class="site-content">

        <?php if( empty( true_news_check_woocommerce_page() ) && !is_home() ){ do_action('true_news_header_banner_x'); } ?>