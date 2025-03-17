<?php
if (!function_exists('newsever_banner_editors_pick')):
    /**
     * Ticker Slider
     *
     * @since Newsever 1.0.0
     *
     */
    function newsever_banner_editors_pick()
    {

        $color_class = 'category-color-1';
        ?>
        <?php


        $newsever_editors_pick_category = newsever_get_option('select_editors_pick_category');

        $newsever_slider_mode = newsever_get_option('select_main_banner_section_mode');
        $dir = 'ltr';
        if (is_rtl()) {
            $dir = 'rtl';
        }
        ?>

        <?php
        $color_class = 'category-color-1';
        if(absint($newsever_editors_pick_category) > 0){
            $color_id = "category_color_" . $newsever_editors_pick_category;
            // retrieve the existing value(s) for this meta field. This returns an array
            $term_meta = get_option($color_id);
            $color_class = ($term_meta) ? $term_meta['color_class_term_meta'] : 'category-color-1';
        }
        $section_title = newsever_get_option('editors_pick_section_title');
        ?>

        <?php if (!empty($section_title)): ?>
        <div class="em-title-subtitle-wrap">
            <?php if (!empty($section_title)): ?>
                <h4 class="widget-title header-after1">
                        <span class="header-after <?php echo esc_attr($color_class); ?>">
                            <?php echo esc_html($section_title);  ?>
                        </span>
                </h4>
            <?php endif; ?>
        </div>
    <?php endif; ?>

        <div class="af-main-banner-featured-posts featured-posts" dir="<?php echo esc_attr($dir); ?>">

            <div class="section-wrapper">
                <div class="small-gird-style af-container-row clearfix">
                    <?php

                    //$newsever_number_of_editors_pick_news = newsever_get_option('number_of_editors_pick_news');

                    if (($newsever_slider_mode == 'layout-4') || ($newsever_slider_mode == 'layout-5')) {
                        $newsever_number_of_editors_pick_news = 4;
                        $col_class = 'col-2 pad';
                    } else {
                        $col_class = 'col-1 pad';
                        $newsever_number_of_editors_pick_news = 2;
                    }

                    $featured_posts = newsever_get_posts($newsever_number_of_editors_pick_news, $newsever_editors_pick_category);
                    if ($featured_posts->have_posts()) :
                        while ($featured_posts->have_posts()) :
                            $featured_posts->the_post();
                            global $post;
                            $url = newsever_get_freatured_image_url($post->ID, 'newsever-medium');
                            ?>
                            <div class="float-l big-grid af-category-inside-img af-sec-post <?php echo esc_attr($col_class); ?>">
                                <div class="read-single pos-rel">
                                    <div class="data-bg read-img pos-rel read-bg-img"
                                         data-background="<?php echo esc_url($url); ?>">
                                        <img src="<?php echo esc_url($url); ?>">
                                        <a class="aft-slide-items" href="<?php the_permalink(); ?>"></a>

                                        <div class="min-read-post-format">
                                            <?php echo newsever_post_format($post->ID); ?>
                                            <span class="min-read-item">
                                                <?php newsever_count_content_words($post->ID); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="read-details">

                                        <div class="read-categories af-category-inside-img">

                                            <?php newsever_post_categories(); ?>
                                        </div>

                                        <div class="read-title">
                                            <h4>
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </h4>
                                        </div>
                                        <div class="entry-meta">
                                            <?php newsever_post_item_meta(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endwhile;
                    endif;
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
        </div>
        <!-- Editors Pick line END -->
        <?php

    }
endif;

add_action('newsever_action_banner_editors_pick', 'newsever_banner_editors_pick', 10);