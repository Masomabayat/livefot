<?php
if (!function_exists('newsever_front_page_main_section_1')) :
    /**
     * Banner Slider
     *
     * @since Newsever 1.0.0
     *
     */
    function newsever_front_page_main_section_1()
    {
        $newsever_enable_main_slider = newsever_get_option('show_main_news_section');

        ?>


        <?php if ($newsever_enable_main_slider): ?>

        <?php


        $dir = 'ltr';
        if (is_rtl()) {
            $dir = 'rtl';
        }
        $newsever_slider_mode = newsever_get_option('select_main_banner_section_mode');
        $newsever_class = $newsever_slider_mode;
        $newsever_banner_layout_mode = newsever_get_option('select_banner_layout_mode');
        if ($newsever_banner_layout_mode == 'boxed') {

            $newsever_class .= ' af-main-banner-boxed';

        }
        ?>
        <section
                class="aft-blocks aft-main-banner-section banner-carousel-1-wrap bg-fixed <?php echo $newsever_class; ?>"
                dir="<?php echo esc_attr($dir); ?>">
            <?php
            if (is_active_sidebar('home-above-main-banner-widgets')): ?>
                <div class="main-banner-widget-wrapper">
                    <div class="main-banner-widget-section container-wrapper">
                        <?php dynamic_sidebar('home-above-main-banner-widgets'); ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php do_action('newsever_action_banner_featured_section'); ?>

            <?php

            if ($newsever_slider_mode == 'default') {
                newsever_get_block('layout-1', 'main-banner');
            } else {
                newsever_get_block($newsever_slider_mode, 'main-banner');
            }


            ?>

            <?php
            if (is_active_sidebar('home-below-main-banner-widgets')): ?>
                <div class="main-banner-widget-wrapper">
                    <div class="main-banner-widget-section container-wrapper">
                        <?php dynamic_sidebar('home-below-main-banner-widgets'); ?>
                    </div>
                </div>
            <?php endif; ?>

        </section>
    <?php endif; ?>

        <!-- end slider-section -->
        <?php
    }
endif;
add_action('newsever_action_front_page_main_section_1', 'newsever_front_page_main_section_1', 40);