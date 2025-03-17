<?php

$newsever_slider_mode = newsever_get_option('select_main_banner_section_mode');
if ($newsever_slider_mode != 'none'):
    $wrapper_class = 'aft-main-banner';
    $newsever_show_trending_carousel_section = newsever_get_option('show_trending_carousel_section');

    $newsever_select_trending_carousel_section_mode = newsever_get_option('select_trending_carousel_section_mode');


    if ($newsever_show_trending_carousel_section) {
        if ($newsever_slider_mode == 'default') {
            if (($newsever_select_trending_carousel_section_mode == 'left')) {
                $wrapper_class .= '-trending-' . $newsever_select_trending_carousel_section_mode;
            } elseif (($newsever_select_trending_carousel_section_mode == 'right')) {
                $wrapper_class .= '-trending-' . $newsever_select_trending_carousel_section_mode;
            }
        }
    }


    ?>

    <!-- <div class="banner-carousel-1 af-widget-carousel owl-carousel owl-theme"> -->
    <div class="aft-main-banner-wrapper clearfix aft-add-gaps-between">
        <div class="aft-banner-box-wrapper af-container-row clearfix <?php echo esc_attr($wrapper_class); ?>">
            <?php

            $col_class_75 = 'col-40 pad';
            $col_class_25 = 'col-30 pad';

            ?>

            <?php

            $newsever_top_left = false;
            $newsever_bottom_right = false;

            if (($newsever_select_trending_carousel_section_mode == 'left')) {
                $newsever_top_left = true;
                $col_class_25 .= ' ' . $newsever_select_trending_carousel_section_mode;
            } elseif (($newsever_select_trending_carousel_section_mode == 'right')) {
                $newsever_bottom_right = true;
                $col_class_25 .= ' ' . $newsever_select_trending_carousel_section_mode;
            }
            ?>

            <?php

            if ($newsever_top_left): ?>
                <?php if ($newsever_show_trending_carousel_section): ?>
                    <div class="af-trending-news-part float-l <?php echo esc_attr($col_class_25); ?> ">
                        <?php do_action('newsever_action_banner_trending_posts'); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>


            <div class="aft-carousel-part float-l <?php echo esc_attr($col_class_75); ?>">
                <?php newsever_get_block('carousel', 'banner'); ?>
            </div>


            <div class="float-l af-editors-pick <?php echo esc_attr($col_class_25); ?> ">
                <?php do_action('newsever_action_banner_editors_pick'); ?>
            </div>

            <?php
            if ($newsever_bottom_right): ?>
                <?php if ($newsever_show_trending_carousel_section): ?>
                    <div class="af-trending-news-part float-l <?php echo esc_attr($col_class_25); ?> ">
                        <?php do_action('newsever_action_banner_trending_posts'); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>


        </div>
    </div>
<?php endif; ?>