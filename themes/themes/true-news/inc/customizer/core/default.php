<?php
/**
 * Default theme options.
 *
 * @package True News
 */

if (!function_exists('true_news_get_default_theme_options')):

    /**
     * Get default theme options
     *
     * @return array Default theme options.
     * @since 1.0.0
     *
     */
    function true_news_get_default_theme_options()
    {

        $true_news_defaults = array();

        // ticker news
        $true_news_defaults['show_ticker_section'] = 1;
        $true_news_defaults['show_ticker_meta_data'] = 1;
        $true_news_defaults['ticker_title'] = esc_html__('Breaking News', 'true-news');
        $true_news_defaults['select_category_for_ticker'] = 0;

        //Theme Options
        $true_news_defaults['ed_preloader']                   = 1;
        $true_news_defaults['twp_perloader_layout']           = 'simple';
        $true_news_defaults['twp_perloader_title'] = esc_html__('Just In', 'true-news');
        $true_news_defaults['ed_preloader_home_only']             = 0;
        $true_news_defaults['ed_preloader_24_hour']             = 0;
        $true_news_defaults['post_date_formate']             = 'default';
        $true_news_defaults['twp_hide_post_author_avatar']             = 0;
        $true_news_defaults['twp_hide_post_author']             = 0;
        $true_news_defaults['twp_hide_post_date']             = 0;
        $true_news_defaults['true_news_pagination_layout']             = 'numeric';
        $true_news_defaults['true_news_global_sidebar_layout']         = 'right-sidebar';
        $true_news_defaults['true_news_single_sidebar_layout']         = 'right-sidebar';
        $true_news_defaults['ed_related_post']                = 1;
        $true_news_defaults['related_post_title']             = esc_html__('Related Post','true-news');
        $true_news_defaults['ed_author_section']              = 1;
        $true_news_defaults['ed_toggle_comment']              = 0;
        $true_news_defaults['twp_navigation_type']              = 'norma-navigation';
        $true_news_defaults['twp_comment_toggle_button_title']             = esc_html__('Comment','true-news');
        $true_news_defaults['ed_lazyload']                   = 0;
        $true_news_defaults['hide_post_categories']                   = 0;
        $true_news_defaults['ed_read_later_posts']                   = 0;
        $true_news_defaults['ed_read_later_posts_notification']                   = 1;
        $true_news_defaults['ed_header_randomize']                   = 0;
        $true_news_defaults['ed_header_trending']                   = 0;
        $true_news_defaults['ed_header_search']                   = 1;
        $true_news_defaults['ed_trending_posts']                   = 0;
        $true_news_defaults['ed_popular_posts']                   = 0;
        $true_news_defaults['tab_section_title']             = esc_html__('Recent News','true-news');

        //header section
        $true_news_defaults['select_header_style'] = 'header-style-default';
        $true_news_defaults['header_banner_text_align']     = 'center';

        $true_news_defaults['primary_font'] = 'Roboto:300,300i,400,400i,500,500i,700,700i';
        $true_news_defaults['secondary_font'] = 'Oswald:400,500,600,700';

        // Home options.
        $true_news_defaults['true_news_home_sections'] = array(
            
            array(
                'home_section_type' => 'block-1',
                'section_ed' => 'yes',
                'slider_autoplay' => 'yes',
                'slider_dots' => 'no',
                'slider_arrows' => 'yes',
                'section_block_1_cat_1' => '',
                'section_block_1_title_1' => '',
                'section_block_1_cat_2' => '',
                'section_block_1_title_2' => '',
                'section_block_1_cat_3' => '',
                'section_block_1_title_3' => esc_html__("Recommended Posts", 'true-news'),
            ),
            array(
                'home_section_type' => 'block-2',
                'section_ed' => 'yes',
                'slider_autoplay' => 'yes',
                'slider_dots' => 'no',
                'slider_arrows' => 'yes',
                'section_block_1_title_left' => '',
                'section_block_1_cat_left' => '',
                'section_block_1_cat_1' => '',
                'section_block_1_title_1' => '',
            ),
            array(
                'home_section_type' => 'block-3',
                'section_ed' => 'yes',
                'section_block_1_title_left' => esc_html__("Editor's Pick", 'true-news'),
                'section_title_3' => esc_html__("Featured Editor's Pick", 'true-news'),
                'hide_post_author_avatar' => 'no',
                'hide_post_category' => 'no',
                'hide_post_author' => 'no',
                'hide_post_date' => 'no',
                'section_block_1_cat_left' => '',
                'post_category_3' => '',
            ),
            array(
                'home_section_type' => 'block-4',
                'section_ed' => 'yes',
                'section_title' => esc_html__('Recent News', 'true-news'),
                'hide_post_author_avatar' => 'no',
                'hide_post_category' => 'no',
                'hide_post_author' => 'no',
                'hide_post_date' => 'no',
            ),
            array(
                'home_section_type' => 'block-5',
                'section_ed' => 'no',
                'section_title' => esc_html__('Recent News', 'true-news'),
                'hide_post_author_avatar' => 'no',
                'hide_post_category' => 'no',
                'hide_post_author' => 'no',
                'hide_post_date' => 'no',
                'pagination' => 'button_click_load',
            ),
            array(
                'home_section_type' => 'block-6',
                'section_ed' => 'yes',
                'hide_post_author_avatar' => 'no',
                'hide_post_category' => 'no',
                'hide_post_author' => 'no',
                'hide_post_date' => 'no',
                'slider_autoplay' => 'yes',
                'slider_dots' => 'no',
                'slider_arrows' => 'yes',
                'hide_slider_overlay' => 'no',
            ),
            array(
                'home_section_type' => 'block-7',
                'section_ed' => 'yes',
                'section_title' => esc_html__('Editors Pick', 'true-news'),
                'hide_post_author_avatar' => 'no',
                'hide_post_category' => 'no',
                'hide_post_author' => 'no',
                'hide_post_date' => 'no',
                'slider_dots' => 'no',
                'slider_arrows' => 'yes',
                'hide_slider_overlay' => 'no',
            ),
            array(
                'home_section_type' => 'block-8',
                'section_ed' => 'no',
                'section_title' => esc_html__('Videos', 'true-news'),
                'hide_post_author_avatar' => 'no',
                'hide_post_category' => 'no',
                'hide_post_author' => 'no',
                'hide_post_date' => 'no',
            ),
             array(
                'home_section_type' => 'recommended',
                'section_ed' => 'yes',
                'section_title' => esc_html__('You May Have Missed', 'true-news'),
                'hide_post_author_avatar' => 'no',
                'hide_post_category' => 'no',
                'hide_post_author' => 'no',
                'hide_post_date' => 'no',
            ),
            array(
                'home_section_type' => 'latest-posts',
                'section_ed' => 'yes',
                'hide_post_author_avatar' => 'no',
                'hide_post_category' => 'no',
                'hide_post_author' => 'no',
                'hide_post_date' => 'no',
                'hide_popular_posts' => 'no',
                'hide_trending_posts' => 'no',
                'sidebar_layout' => 'right-sidebar',
                'section_title' => esc_html__("Recent Posts", 'true-news'),
            ),
            array(
                'home_section_type' => 'advertise-area',
                'advertise_link' => '',
                'advertise_image' => '',
                'section_ed' => 'no',
            ),
        );

        // Footer Options.
        $true_news_defaults['footer_column_layout'] = 4;
        $true_news_defaults['footer_copyright_text'] = esc_html__('Copyright All rights reserved', 'true-news');
        $true_news_defaults['ed_ticker_post']                 = 1;
        $true_news_defaults['ed_ticker_post_arrow']           = 1;
        $true_news_defaults['ed_ticker_post_dots']            = '';
        $true_news_defaults['ed_ticker_post_autoplay']        = 1;
        $true_news_defaults['breadcrumb_layout']              = 'simple';
        $true_news_defaults['twp_general_text_color']         = '#555555';
        $true_news_defaults['twp_general_heading_text_color']         = '#000';
        $true_news_defaults['twp_general_heading_link_color']         = '#000';

        // Pass through filter.
        $true_news_defaults = apply_filters('true_news_filter_default_theme_options', $true_news_defaults);

        return $true_news_defaults;

    }

endif;
