<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


/**
 * Customizer
 *
 * @class   covernews
 */

if (!function_exists('covernews_custom_style')) {

    function covernews_custom_style()
    {
        $top_header_background = covernews_get_option('top_header_background_color');
        $top_text_color = covernews_get_option('top_header_text_color');
        global $covernews_google_fonts;
        $covernews_primary_color = covernews_get_option('primary_color');
        $covernews_secondary_color = covernews_get_option('secondary_color');
        $link_color = covernews_get_option('link_color');
        $covernews_site_wide_title_color = covernews_get_option('site_wide_title_color');
        $covernews_postformat_color = covernews_get_option('post_format_color');


        $covernews_footer_background_color = covernews_get_option('footer_background_color');
        $covernews_footer_texts_color = covernews_get_option('footer_texts_color');
        $covernews_footer_credits_background_color = covernews_get_option('footer_credits_background_color');
        $covernews_footer_credits_texts_color = covernews_get_option('footer_credits_texts_color');

        $covernews_mainbanner_silder_caption_font_size = covernews_get_option('main_banner_silder_caption_font_size');
        $covernews_mainbanner_featured_news_title_font_size = covernews_get_option('main_banner_featured_news_title_font_size');
        $covernews_covernews_page_posts_title_font_size = covernews_get_option('covernews_page_posts_title_font_size');
        $covernews_section_title_font_size = covernews_get_option('covernews_section_title_font_size');
        $covernews_spotlight_posts_title_font_size = covernews_get_option('spotlight_posts_title_font_size');
        $covernews_general_title_font_size = covernews_get_option('general_title_font_size');
        $covernews_general_font_size = covernews_get_option('general_font_size');


        $covernews_mailchimp_background_color = covernews_get_option('footer_mailchimp_background_color');
        $covernews_mailchimp_filed_border_color = covernews_get_option('footer_mailchimp_field_border_color');

        $main_navigation_background_color_mode = covernews_get_option('main_navigation_background_color_mode');
        $main_navigation_custom_background_color = covernews_get_option('main_navigation_custom_background_color');
        $main_navigation_link_color = covernews_get_option('main_navigation_link_color');
        $main_navigation_badge_background = covernews_get_option('main_navigation_badge_background');
        $main_navigation_badge_color = covernews_get_option('main_navigation_badge_color');
        $covernews_title_color = covernews_get_option('title_link_color');
        $covernews_title_over_image_color = covernews_get_option('title_over_image_color');


        $covernews_category_color_1 = covernews_get_option('category_color_1');
        $covernews_category_color_2 = covernews_get_option('category_color_2');
        $covernews_category_color_3 = covernews_get_option('category_color_3');
        $covernews_category_color_4 = covernews_get_option('category_color_4');
        $covernews_category_color_5 = covernews_get_option('category_color_5');
        $covernews_category_color_6 = covernews_get_option('category_color_6');
        $covernews_category_color_7 = covernews_get_option('category_color_7');
        $covernews_category_color_8 = covernews_get_option('category_color_8');


        $covernews_primary_font = $covernews_google_fonts[covernews_get_option('primary_font')];
        $covernews_secondary_font = $covernews_google_fonts[covernews_get_option('secondary_font')];
        $covernews_letter_spacing = covernews_get_option('letter_spacing');
        $covernews_line_height = covernews_get_option('line_height');

        ob_start();
        ?>


        <?php if (!empty($top_header_background)): ?>
        body .top-masthead {
        background: <?php echo $top_header_background; ?>;
        }


    <?php endif; ?>

    <?php if (!empty($top_text_color)): ?>
        body .top-masthead,
        body .top-masthead .top-navigation a:hover,
        body .top-masthead .top-navigation a {
        color: <?php echo $top_text_color; ?>;

        }

    <?php endif; ?>

    <?php if (!empty($covernews_primary_color)): ?>
        body .offcanvas-menu span,
        body .primary-color {
        background-color: <?php echo $covernews_primary_color; ?>;
        }

        body{
        color: <?php echo $covernews_primary_color; ?>;
        }
    <?php endif; ?>

    <?php if (!empty($covernews_secondary_color)): ?>
        body .secondary-color,
        body button,
        body input[type="button"],
        body input[type="reset"],
        body input[type="submit"],
        body .site-content .search-form .search-submit,
        body .site-footer .search-form .search-submit,
        body .main-navigation,
        body .em-post-format i,
        body span.header-after:after,
        body #secondary .widget-title span:after,
        body .af-tabs.nav-tabs > li > a.active:after,
        body .af-tabs.nav-tabs > li > a:hover:after,
        body .exclusive-posts .exclusive-now,
        body span.trending-no,
        body .tagcloud a:hover{
        background: <?php echo $covernews_secondary_color; ?>;
        }


        body.dark .latest-posts-full .header-details-wrapper .entry-title a:hover,
        body.dark .entry-title a:visited:hover,
        body.dark .entry-title a:hover,
        body.dark h3.article-title.article-title-1 a:visited:hover,
        body.dark h3.article-title.article-title-1 a:hover,
        body.dark .trending-posts-carousel h3.article-title a:visited:hover,
        body.dark .trending-posts-carousel h3.article-title a:hover,
        body.dark .exclusive-slides a:visited:hover,
        body.dark .exclusive-slides a:hover,
        body.dark .article-title.article-title-1 a:visited:hover,
        body.dark .article-title.article-title-1 a:hover,
        body.dark .article-title a:visited:hover,
        body.dark .article-title a:hover,
        #wp-calendar caption,
        #wp-calendar td#today,
        .entry-title a:visited:hover,
        .entry-title a:hover,
        h3.article-title.article-title-1 a:visited:hover,
        h3.article-title.article-title-1 a:hover,
        .trending-posts-carousel h3.article-title a:visited:hover,
        .trending-posts-carousel h3.article-title a:hover,
        .exclusive-slides a:visited:hover,
        .exclusive-slides a:hover,
        .article-title.article-title-1 a:visited:hover,
        .article-title.article-title-1 a:hover,
        .article-title a:visited:hover,
        .article-title a:hover,
        body a:hover,
        body a:focus,
        body a:active,
        body .figure-categories-2 .cat-links a
        {
        color: <?php echo $covernews_secondary_color; ?>;
        }


        body #loader:after {

        border-left-color: <?php echo $covernews_secondary_color; ?>;

        }


    <?php endif; ?>


    <?php if (!empty($main_navigation_background_color_mode)):
        if ($main_navigation_background_color_mode == 'custom-color'):
            ?>
            body .main-navigation{
            background: <?php echo $main_navigation_custom_background_color; ?>;
            }
        <?php
        endif;
    endif;

        ?>
    <?php if (!empty($link_color)): ?>


        a{
        color: <?php echo $link_color; ?>;

        }

        .af-tabs.nav-tabs > li.active > a,
        .af-tabs.nav-tabs > li:hover > a,
        .af-tabs.nav-tabs > li:focus > a{
        color: <?php echo $link_color; ?>;
        }

        .social-widget-menu ul li a,
        .em-author-details ul li a,
        .tagcloud a {
        border-color: <?php echo $link_color; ?>;
        }

        a:visited{
        color: <?php echo $link_color; ?>;
        }
    <?php endif; ?>

    <?php if (!empty($covernews_site_wide_title_color)): ?>
        body h1,
        body h2,
        body h2 span,
        body h3,
        body h4,
        body h5,
        body h6,
        body #primary .widget-title,
        body .af-tabs.nav-tabs > li.active > a, .af-tabs.nav-tabs > li:hover > a, .af-tabs.nav-tabs > li:focus > a{
        color: <?php echo $covernews_site_wide_title_color; ?>;

        }
    <?php endif; ?>

    <?php if (!empty($covernews_line_height)): ?>
        body h1,
        body h2,
        body h2 span,
        body h3,
        body h4,
        body h5,
        body h6 {
        line-height: <?php echo $covernews_line_height; ?>;
        }
    <?php endif; ?>

    <?php if (!empty($main_navigation_link_color)): ?>

        body .header-layout-2 .site-header .main-navigation.aft-sticky-navigation .site-branding .site-title a,
        body .main-navigation.aft-sticky-navigation span.af-mobile-site-title-wrap .site-title a,
        body .main-navigation .menu ul.menu-desktop > li > a:visited,
        body .main-navigation .menu ul.menu-desktop > li > a .fa-angle-down,
        body .main-navigation .menu ul.menu-desktop > li > a,
        body .search-icon,
        body .search-icon:visited,
        body .search-icon:hover,
        body .search-icon:focus,
        body .search-icon:active
        {
            color: <?php echo $main_navigation_link_color; ?>;
        }
        
        body .search-overlay.reveal-search .search-icon i.fa.fa-search:before,
        body .search-overlay.reveal-search .search-icon i.fa.fa-search:after,
        body .ham,
        body .ham:before, 
        body .ham:after,
        body .main-navigation ul>li>a:after
        {
        background-color: <?php echo $main_navigation_link_color; ?>;
        }
        @media only screen and (max-width: 991px) {
            .navigation-container ul li a{
                color: <?php echo $main_navigation_link_color; ?>;
            }
            .main-navigation .menu .menu-mobile li a button:before, 
            .main-navigation .menu .menu-mobile li a button:after{
                background-color: <?php echo $main_navigation_link_color; ?>;
            }
        }
    <?php endif; ?>

    <?php if (!empty($main_navigation_badge_background)): ?>

        body span.menu-description
        {
        background: <?php echo $main_navigation_badge_background; ?>;
        }

    <?php endif; ?>

    <?php if (!empty($main_navigation_badge_color)): ?>

        body span.menu-description
        {
        color: <?php echo $main_navigation_badge_color; ?>;
        }

    <?php endif; ?>


    <?php if (!empty($covernews_title_color)): ?>

        body h3.article-title.article-title-1 a,
        body .trending-posts-carousel h3.article-title a,
        body .exclusive-slides a
        {
        color: <?php echo $covernews_title_color; ?>;
        }

        body h3.article-title.article-title-1 a:visited,
        body .trending-posts-carousel h3.article-title a:visited,
        body .exclusive-slides a:visited
        {
        color: <?php echo $covernews_title_color; ?>;
        }

    <?php endif; ?>

    <?php if (!empty($covernews_title_over_image_color)): ?>
        body.dark .categorized-story .title-heading .article-title-2 a:visited:hover,
        body.dark .categorized-story .title-heading .article-title-2 a,
        body .categorized-story .title-heading .article-title-2 a:visited:hover,
        body .categorized-story .title-heading .article-title-2 a,
        body.dark .full-plus-list .spotlight-post:first-of-type figcaption h3 a:hover,
        body .full-plus-list .spotlight-post:first-of-type figcaption h3 a:hover,
        body.dark .slider-figcaption-1 .article-title a:visited:hover,
        body.dark .slider-figcaption-1 .article-title a:hover,
        .slider-figcaption-1 .article-title a:visited:hover,
        .slider-figcaption-1 .article-title a:hover,
        body .slider-figcaption-1 .slide-title a,
        body .categorized-story .title-heading .article-title-2 a,
        body .full-plus-list .spotlight-post:first-of-type figcaption h3 a{
        color: <?php echo $covernews_title_over_image_color; ?>;
        }

        body .slider-figcaption-1 .slide-title a:visited,
        body .categorized-story .title-heading .article-title-2 a:visited,
        body .full-plus-list .spotlight-post:first-of-type figcaption h3 a:visited{
        color: <?php echo $covernews_title_over_image_color; ?>;
        }


    <?php endif; ?>

    <?php if (!empty($covernews_postformat_color)): ?>
        body .figure-categories-bg .em-post-format:before{
        background: <?php echo $covernews_postformat_color; ?>;
        }
        body span.trending-no,
        body .em-post-format{
        color: <?php echo $covernews_postformat_color; ?>;
        }

    <?php endif; ?>


    <?php if (!empty($covernews_primary_font)): ?>
        body,
        body button,
        body input,
        body select,
        body optgroup,
        body textarea {
        font-family: <?php echo $covernews_primary_font; ?>;
        }

    <?php endif; ?>

    <?php if (!empty($covernews_secondary_font)): ?>
        body h1,
        body h2,
        body h3,
        body h4,
        body h5,
        body h6,
        body .main-navigation a,
        body .font-family-1,
        body .site-description,
        body .trending-posts-line,
        body .exclusive-posts,
        body .widget-title,
        body .em-widget-subtitle,
        body .grid-item-metadata .item-metadata,
        body .af-navcontrols .slide-count,
        body .figure-categories .cat-links,
        body .nav-links a {
        font-family: <?php echo $covernews_secondary_font; ?>;
        }

    <?php endif; ?>


    <?php if (!empty($covernews_line_height)): ?>
        .article-title, .site-branding .site-title, .main-navigation .menu ul li a, .slider-figcaption .slide-title {
        letter-spacing: <?php echo $covernews_letter_spacing; ?>px;
        line-height: <?php echo $covernews_line_height; ?>;
        }

    <?php endif; ?>
        <!--        category color starts-->

    <?php if (!empty($covernews_category_color_1)): ?>

        body .figure-categories .cat-links a.category-color-1 {
        background-color: <?php echo $covernews_category_color_1; ?>;
        }

        body .trending-story .figure-categories .cat-links a.covernews-categories.category-color-1,
        body .list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-1,
        body .full-plus-list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-1,
        body .covernews_tabbed_posts_widget .figure-categories .cat-links a.covernews-categories.category-color-1,
        body .trending-posts-vertical-carousel .figure-categories .cat-links a.covernews-categories.category-color-1

        {
        color: <?php echo $covernews_category_color_1; ?>;
        }

        body .full-plus-list .spotlight-post:first-of-type .figure-categories .cat-links a.covernews-categories.category-color-1,
        body .full-plus-list .spotlight-post:first-of-type .figure-categories .cat-links a.covernews-categories,
        body .figure-categories .cat-links a.covernews-categories
        {
        color: #ffffff;
        background-color: <?php echo $covernews_category_color_1; ?>;
        }

        body span.header-after.category-color-1:after{
        background: <?php echo $covernews_category_color_1; ?>;
        }

    <?php endif; ?>


    <?php if (!empty($covernews_category_color_2)): ?>

        body .figure-categories .cat-links a.category-color-2 {
        background-color: <?php echo $covernews_category_color_2; ?>;
        }

        body .trending-story .figure-categories .cat-links a.covernews-categories.category-color-2,
        body .list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-2,
        body .full-plus-list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-2,
        body .covernews_tabbed_posts_widget .figure-categories .cat-links a.covernews-categories.category-color-2,
        body .trending-posts-vertical-carousel .figure-categories .cat-links a.covernews-categories.category-color-2
        {
        color: <?php echo $covernews_category_color_2; ?>;
        }

        body .full-plus-list .spotlight-post:first-of-type .figure-categories .cat-links a.covernews-categories.category-color-2{
        color: #ffffff;
        background-color: <?php echo $covernews_category_color_2; ?>;
        }

        body span.header-after.category-color-2:after{
        background: <?php echo $covernews_category_color_2; ?>;
        }
    <?php endif; ?>

    <?php if (!empty($covernews_category_color_3)): ?>

        body .figure-categories .cat-links a.category-color-3 {
        background-color: <?php echo $covernews_category_color_3; ?>;
        }

        body .trending-story .figure-categories .cat-links a.covernews-categories.category-color-3,
        body .list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-3,
        body .full-plus-list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-3,
        body .covernews_tabbed_posts_widget .figure-categories .cat-links a.covernews-categories.category-color-3,
        body .trending-posts-vertical-carousel .figure-categories .cat-links a.covernews-categories.category-color-3
        {
        color: <?php echo $covernews_category_color_3; ?>;
        }

        body .full-plus-list .spotlight-post:first-of-type .figure-categories .cat-links a.covernews-categories.category-color-3{
        color: #ffffff;
        background-color: <?php echo $covernews_category_color_3; ?>;
        }

        body span.header-after.category-color-3:after{
        background: <?php echo $covernews_category_color_3; ?>;
        }
    <?php endif; ?>

    <?php if (!empty($covernews_category_color_4)): ?>

        body .figure-categories .cat-links a.category-color-4 {
        background-color: <?php echo $covernews_category_color_4; ?>;
        }

        body .trending-story .figure-categories .cat-links a.covernews-categories.category-color-4,
        body .list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-4,
        body .full-plus-list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-4,
        body .covernews_tabbed_posts_widget .figure-categories .cat-links a.covernews-categories.category-color-4,
        body .trending-posts-vertical-carousel .figure-categories .cat-links a.covernews-categories.category-color-4
        {
        color: <?php echo $covernews_category_color_4; ?>;
        }

        body .full-plus-list .spotlight-post:first-of-type .figure-categories .cat-links a.covernews-categories.category-color-4{
        color: #ffffff;
        background-color: <?php echo $covernews_category_color_4; ?>;
        }

        body span.header-after.category-color-4:after{
        background: <?php echo $covernews_category_color_4; ?>;
        }
    <?php endif; ?>

    <?php if (!empty($covernews_category_color_5)): ?>

        body .figure-categories .cat-links a.category-color-5 {
        background-color: <?php echo $covernews_category_color_5; ?>;
        }

        body .trending-story .figure-categories .cat-links a.covernews-categories.category-color-5,
        body .list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-5,
        body .full-plus-list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-5,
        body .covernews_tabbed_posts_widget .figure-categories .cat-links a.covernews-categories.category-color-5,
        body .trending-posts-vertical-carousel .figure-categories .cat-links a.covernews-categories.category-color-5
        {
        color: <?php echo $covernews_category_color_5; ?>;
        }

        body .full-plus-list .spotlight-post:first-of-type .figure-categories .cat-links a.covernews-categories.category-color-5{
        color: #ffffff;
        background-color: <?php echo $covernews_category_color_5; ?>;
        }

        body span.header-after.category-color-5:after{
        background: <?php echo $covernews_category_color_5; ?>;
        }
    <?php endif; ?>

    <?php if (!empty($covernews_category_color_6)): ?>

        body .figure-categories .cat-links a.category-color-6 {
        background-color: <?php echo $covernews_category_color_6; ?>;
        }

        body .trending-story .figure-categories .cat-links a.covernews-categories.category-color-6,
        body .list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-6,
        body .full-plus-list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-6,
        body .covernews_tabbed_posts_widget .figure-categories .cat-links a.covernews-categories.category-color-6,
        body .trending-posts-vertical-carousel .figure-categories .cat-links a.covernews-categories.category-color-6
        {
        color: <?php echo $covernews_category_color_6; ?>;
        }

        body .full-plus-list .spotlight-post:first-of-type .figure-categories .cat-links a.covernews-categories.category-color-6{
        color: #ffffff;
        background-color: <?php echo $covernews_category_color_6; ?>;
        }

        body span.header-after.category-color-6:after{
        background: <?php echo $covernews_category_color_6; ?>;
        }
    <?php endif; ?>

    <?php if (!empty($covernews_category_color_7)): ?>

        body .figure-categories .cat-links a.category-color-7 {
        background-color: <?php echo $covernews_category_color_7; ?>;
        }

        body .trending-story .figure-categories .cat-links a.covernews-categories.category-color-7,
        body .list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-7,
        body .full-plus-list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-7,
        body .covernews_tabbed_posts_widget .figure-categories .cat-links a.covernews-categories.category-color-7,
        body .trending-posts-vertical-carousel .figure-categories .cat-links a.covernews-categories.category-color-7
        {
        color: <?php echo $covernews_category_color_7; ?>;
        }

        body .full-plus-list .spotlight-post:first-of-type .figure-categories .cat-links a.covernews-categories.category-color-7{
        color: #ffffff;
        background-color: <?php echo $covernews_category_color_7; ?>;
        }

        body span.header-after.category-color-7:after{
        background: <?php echo $covernews_category_color_7; ?>;
        }
    <?php endif; ?>

    <?php if (!empty($covernews_category_color_8)): ?>

        body .figure-categories .cat-links a.category-color-8 {
        background-color: <?php echo $covernews_category_color_8; ?>;
        color: #404040;
        }

        body .trending-story .figure-categories .cat-links a.covernews-categories.category-color-8,
        body .list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-8,
        body .full-plus-list .spotlight-post .figure-categories .cat-links a.covernews-categories.category-color-8,
        body .covernews_tabbed_posts_widget .figure-categories .cat-links a.covernews-categories.category-color-8,
        body .trending-posts-vertical-carousel .figure-categories .cat-links a.covernews-categories.category-color-8
        {
        color: #404040;
        }

        body .full-plus-list .spotlight-post:first-of-type .figure-categories .cat-links a.covernews-categories.category-color-8{
        color: #404040;
        background-color: <?php echo $covernews_category_color_8; ?>;
        }

        body span.header-after.category-color-8:after{
        background: <?php echo $covernews_category_color_8; ?>;
        color: #404040;
        }
    <?php endif; ?>

    <?php if (!empty($covernews_footer_background_color)): ?>
        body .site-footer .primary-footer{
        background: <?php echo $covernews_footer_background_color; ?>;

        }

    <?php endif; ?>

    <?php if (!empty($covernews_footer_texts_color)): ?>
        body .site-footer,
        body .site-footer .widget-title span,
        body .site-footer .site-title a,
        body .site-footer .site-description,
        body .site-footer a {
        color: <?php echo $covernews_footer_texts_color; ?>;

        }

        .site-footer .social-widget-menu ul li a,
        .site-footer .em-author-details ul li a,
        .site-footer .tagcloud a
        {
        border-color: <?php echo $covernews_footer_texts_color; ?>;
        }

        .site-footer a:visited {
        color: <?php echo $covernews_footer_texts_color; ?>;
        }


    <?php endif; ?>

        <?php if (!empty($covernews_footer_credits_background_color)): ?>
        body .site-info {
        background: <?php echo $covernews_footer_credits_background_color; ?>;

        }

    <?php endif; ?>

    <?php if (!empty($covernews_footer_credits_texts_color)): ?>
        body .site-info,
        body .site-info a {
        color: <?php echo $covernews_footer_credits_texts_color; ?>;

        }

    <?php endif; ?>

        <?php if (!empty($covernews_mailchimp_background_color)): ?>
        body .mailchimp-block {
        background: <?php echo $covernews_mailchimp_background_color; ?>;

        }
    <?php endif; ?>


    <?php if (!empty($covernews_mailchimp_filed_border_color)): ?>
        body .mc4wp-form-fields input[type="text"], body .mc4wp-form-fields input[type="email"] {
        border-color: <?php echo $covernews_mailchimp_filed_border_color; ?>;

    }
    <?php endif; ?>
    
    
    <?php if (!empty($covernews_mainbanner_silder_caption_font_size)): ?>
        @media only screen and (min-width: 1025px) and (max-width: 1599px) {
            body .covernews_posts_slider_widget .slider-figcaption .slide-title,
            body .af-main-banner .slider-figcaption .slide-title {
            font-size: <?php echo $covernews_mainbanner_silder_caption_font_size; ?>px;
            }
        }


    <?php endif; ?>


    <?php if (!empty($covernews_general_font_size)): ?>

        body,
        button,
        input,
        select,
        optgroup,
        textarea,
        body .entry-content-wrap,
        

        {
        font-size: <?php echo $covernews_general_font_size; ?>px;
        }

        blockquote{
            font-size: calc(<?php echo $covernews_general_font_size; ?>px + 20%);
        }

    <?php endif; ?>

    <?php if (!empty($covernews_section_title_font_size)): ?>

        body blockquote cite,
        body .nav-previous a,
        body .nav-next a,
        body .af-tabs.nav-tabs > li > a,
        body #secondary .af-tabs.nav-tabs > li > a,
        body #primary .af-tabs.nav-tabs > li > a,
        body footer .widget-title,
        body #secondary .widget-title span,
        body span.header-after
        {
        font-size: <?php echo $covernews_section_title_font_size; ?>px;
        }

    <?php endif; ?>

    <?php if (!empty($covernews_spotlight_posts_title_font_size)): ?>

        body .covernews_single_col_categorised_posts .article-title,
        body .full .spotlight-post figcaption h3 a,
        body .full-plus-list .spotlight-post:first-of-type figcaption h3 a,
        body .categorized-story .title-heading .article-title-2
        {
        font-size: <?php echo $covernews_spotlight_posts_title_font_size; ?>px;
        }

        @media only screen and (max-width: 767px){
            body .covernews_single_col_categorised_posts .article-title,
            body .full .spotlight-post figcaption h3 a,
            body .covernews_posts_slider_widget .slider-figcaption .slide-title,
            body .full-plus-list .spotlight-post:first-of-type figcaption h3 a,
            body .categorized-story .title-heading .article-title-2
            {
                font-size: 20px;
            }
        }

    <?php endif; ?>

    <?php if (!empty($covernews_general_title_font_size)): ?>
        body .widget_recent_comments li a,
        body .widget_pages li a,
        body .widget_archive li a,
        body .widget_meta li a,
        body .widget_categories li,
        body .widget_nav_menu li a,
        body .widget_recent_entries li a,
        body .article-title
        {
        font-size: <?php echo $covernews_general_title_font_size; ?>px;
        }

    <?php endif; ?>


    <?php if (!empty($covernews_covernews_page_posts_title_font_size)): ?>

        body.archive .content-area .page-title,
        body.search-results .content-area .header-title-wrapper .page-title,
        body header.entry-header h1.entry-title{
        font-size: <?php echo $covernews_covernews_page_posts_title_font_size; ?>px;
        }

    <?php endif; ?>

    <?php if (!empty($covernews_section_title_font_size)): ?>
        body #primary .em-widget-subtitle {
        font-size: <?php echo $covernews_section_title_font_size; ?>px;
        }

    <?php endif; ?>

    .elementor-template-full-width .elementor-section.elementor-section-full_width > .elementor-container,
    .elementor-template-full-width .elementor-section.elementor-section-boxed > .elementor-container{
        max-width: 1200px;
    }
    @media (min-width: 1600px){
        .elementor-template-full-width .elementor-section.elementor-section-full_width > .elementor-container,
        .elementor-template-full-width .elementor-section.elementor-section-boxed > .elementor-container{
            max-width: 1600px;
        }
    }
            
        }
        <!--        end if media query-->

        <?php
        return ob_get_clean();
    }
}


