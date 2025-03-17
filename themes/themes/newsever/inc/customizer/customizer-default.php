<?php
/**
 * Default theme options.
 *
 * @package Newsever
 */

if (!function_exists('newsever_get_default_theme_options')):

/**
 * Get default theme options
 *
 * @since 1.0.0
 *
 * @return array Default theme options.
 */
function newsever_get_default_theme_options() {

    $defaults = array();
    // Preloader options section
    $defaults['enable_site_preloader'] = 1;

    // Header options section
    $defaults['header_layout'] = 'header-layout-side';

    $defaults['show_top_header_section'] = 0;
    $defaults['top_header_background_color'] = "#1c1c1c";
    $defaults['top_header_text_color'] = "#ffffff";

    $defaults['show_top_menu'] = 1;
    $defaults['show_social_menu_section'] = 1;
    $defaults['show_secondary_menu_section'] = 1;
    $defaults['enable_sticky_header_option'] = 0;
    
    $defaults['show_date_section'] = 1;

    $defaults['disable_header_image_tint_overlay'] = 1;


    $defaults['banner_advertisement_section'] = '';
    $defaults['banner_advertisement_section_url'] = '';
    $defaults['banner_advertisement_open_on_new_tab'] = 0;
    $defaults['banner_advertisement_scope'] = 'site-wide';


    // breadcrumb options section
    $defaults['enable_breadcrumb'] = 1;
    $defaults['select_breadcrumb_mode'] = 'simple';


    // Frontpage Section.

    $defaults['show_popular_tags_section'] = 1;
    $defaults['show_popular_tags_title'] = __('Popular Tags', 'newsever');
    $defaults['number_of_popular_tags'] = 7;
    $defaults['select_popular_tags_mode'] = 'post_tag';

    $defaults['show_flash_news_section'] = 1;
    $defaults['flash_news_title'] = __('Exclusive', 'newsever');
    $defaults['flash_news_subtitle'] = __('Breaking News', 'newsever');
    $defaults['select_flash_news_category'] = 0;
    $defaults['number_of_flash_news'] = 5;
    $defaults['disable_animation']= 0;
    $defaults['select_flash_new_mode'] = 'flash-slide-left';
    $defaults['banner_flash_news_scope'] = 'front-page-only';

    $defaults['show_main_news_section'] = 1;
    $defaults['main_banner_section_label'] = __('Main News', 'newsever');

    $defaults['select_main_banner_section_mode'] = 'default';
    $defaults['select_slider_news_category'] = 0;
    $defaults['main_banner_section_background_color'] = '#1c1c1c';
    $defaults['main_banner_section_secondary_background_color'] = '#212121';
    $defaults['main_banner_section_texts_color'] = '#ffffff';

    $defaults['show_trending_carousel_section'] = 1;
    $defaults['select_trending_carousel_section_mode'] = 'left';
    $defaults['select_trending_carousel_section_mode_grid'] = 'top';
    $defaults['select_trending_carousel_category'] = 0;

    //Defaults carousel layout
    $defaults['select_default_carousel_column'] = 'carousel-2';
    $defaults['select_default_carousel_layout'] = 'title-under-image';

    //Defaults grid layout
    $defaults['select_default_grid_column'] = 'grid-layout-1';

    //Defaults slider layout
    $defaults['select_default_slider_mode'] = 'default';
    $defaults['select_default_slider_thumb_mode'] = 'show';

    //Banner Layout Mode
    $defaults['select_banner_layout_mode'] = 'boxed';
    $defaults['enable_gaps_between_thumbs'] = true;

    $defaults['number_of_slides'] = 5;

    $defaults['trending_slider_title'] = __("Trending", 'newsever');
    $defaults['select_trending_news_category'] = 0;
    $defaults['number_of_trending_slides'] = 5;

    $defaults['show_featured_news_section'] = 1;
    $defaults['featured_news_section_title'] = __('Featured Story', 'newsever');
    $defaults['select_featured_news_category'] = 0;

    $defaults['editors_pick_section_title'] = __("Editorials", 'newsever');
    $defaults['select_editors_pick_category'] = 0;


    $defaults['frontpage_content_alignment'] = 'frontpage-layout-1';

    $defaults['frontpage_sticky_sidebar'] = 1;

    //layout options
    $defaults['global_content_layout'] = 'default-content-layout';
    $defaults['global_content_alignment'] = 'align-content-left';
    $defaults['global_image_alignment'] = 'full-width-image';
    $defaults['global_post_date_author_setting'] = 'show-date-author';
    $defaults['global_hide_post_date_author_in_list'] = 1;
    $defaults['global_excerpt_length'] = 20;
    $defaults['global_read_more_texts'] = __('Read more', 'newsever');
    $defaults['global_widget_excerpt_setting'] = 'trimmed-content';
    $defaults['global_date_display_setting'] = 'default-date';

    $defaults['archive_layout'] = 'archive-layout-list';
    $defaults['archive_pagination_view'] = 'archive-default';
    $defaults['archive_image_alignment_grid'] = 'archive-image-default';
    $defaults['archive_image_alignment_list'] = 'archive-image-left';
    $defaults['archive_image_alignment'] = 'archive-image-default';
    $defaults['archive_content_view'] = 'archive-content-excerpt';
    $defaults['disable_main_banner_on_blog_archive'] = 0;

    //Related posts
    $defaults['single_show_featured_image'] = 1;
    $defaults['single_post_featured_image_view']     = 'default';


    //Related posts
    $defaults['single_show_related_posts'] = 1;
    $defaults['single_related_posts_title']     = __( 'More Stories', 'newsever' );
    $defaults['single_number_of_related_posts']  = 3;

    //Pagination.
    $defaults['site_pagination_type'] = 'default';


    // Footer.
    // Latest posts
    $defaults['frontpage_show_latest_posts'] = 1;
    $defaults['frontpage_latest_posts_section_title'] = __('You may have missed', 'newsever');
    $defaults['frontpage_latest_posts_category'] = 0;
    $defaults['number_of_frontpage_latest_posts'] = 4;


    $defaults['footer_copyright_text'] = esc_html__('Copyright &copy; All rights reserved.', 'newsever');
    $defaults['hide_footer_menu_section']  = 0;
    $defaults['hide_footer_site_title_section']  = 0;
    $defaults['hide_footer_copyright_credits']  = 0;
    $defaults['number_of_footer_widget']  = 3;


    //font option

    $defaults['primary_font']      = 'Roboto:100,300,400,500,700';
    $defaults['secondary_font']    = 'Barlow:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800';
    $defaults['tertiary_font']    = 'Barlow+Semi+Condensed:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700';

    $defaults['post_format_color']    = '#202020';

    $defaults['global_show_home_menu']           = 'yes';
    $defaults['global_home_menu_icon']           = 'fa fa-home';
    $defaults['global_show_comment_count']           = 'yes';
    $defaults['global_hide_comment_count_in_list']   = 1;
    $defaults['global_show_min_read']           = 'yes';
    $defaults['global_hide_min_read_in_list']   = 1;
    $defaults['global_show_min_read_number']   = 250;
    $defaults['aft_language_switcher']           = '';
    $defaults['show_watch_online_section']           = 1;
    $defaults['watch_online_icon']           = 'fa fa-youtube-play';
    $defaults['aft_custom_title']           = __('Watch Online', 'newsever');
    $defaults['aft_custom_link']           = '';
    $defaults['global_show_categories']           = 'yes';
    $defaults['global_show_home_menu_border']    = 'show-menu-border';
    $defaults['global_site_mode_setting']    = 'aft-default-mode';

    //font size
    $defaults['site_title_font_size']    = 60;


    // Pass through filter.
    $defaults = apply_filters('newsever_filter_default_theme_options', $defaults);

	return $defaults;

}

endif;
