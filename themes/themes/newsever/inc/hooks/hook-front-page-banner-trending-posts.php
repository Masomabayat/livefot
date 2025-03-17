<?php
if (!function_exists('newsever_banner_trending_posts')):
    /**
     * Ticker Slider
     *
     * @since Newsever 1.0.0
     *
     */
    function newsever_banner_trending_posts()
    {
        $color_class = 'category-color-1';
        ?>
        <?php

        
        $newsever_trending_category = newsever_get_option('select_trending_carousel_category');
        $newsever_number_of_trending_slides = newsever_get_option('number_of_trending_slides');

        $carousel_class = 'af-main-banner-trending-posts-vertical-carousel';


        $dir = 'ltr';
        if (is_rtl()) {
            $dir = 'rtl';
        }
        ?>

        <?php
        $color_class = 'category-color-1';
        if(absint($newsever_trending_category) > 0){
            $color_id = "category_color_" . $newsever_trending_category;
            // retrieve the existing value(s) for this meta field. This returns an array
            $term_meta = get_option($color_id);
            $color_class = ($term_meta) ? $term_meta['color_class_term_meta'] : 'category-color-1';
        }
        $section_title = newsever_get_option('trending_slider_title');
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
        
        <div class="af-main-banner-trending-posts trending-posts" dir="<?php echo esc_attr($dir); ?>">
            <div class="section-wrapper">
                <div class="af-double-column list-style clearfix <?php echo esc_attr($carousel_class); ?>">
                    <?php

                    $count = 1;
                    $trending_posts = newsever_get_posts($newsever_number_of_trending_slides, $newsever_trending_category);
                    if ($trending_posts->have_posts()) :
                    while ($trending_posts->have_posts()) :
                    $trending_posts->the_post();
                    global $post;
                    $url = newsever_get_freatured_image_url($post->ID, 'thumbnail');
                    ?>

                    <div class="col-1" data-mh="af-feat-list">
                        <div class="read-single color-pad">
                            <div class="data-bg read-img pos-rel col-4 float-l read-bg-img"
                                 data-background="<?php echo esc_url($url); ?>">
                                <img src="<?php echo esc_url($url); ?>"/>
                                <a href="<?php the_permalink(); ?>"></a>
                                <div class="trending-post-items pos-rel col-4 float-l show-inside-image">
                                            <span class="trending-no">
                                                <?php echo sprintf('%s', esc_html($count)); ?>
                                            </span>
                                </div>
                            </div>
                            <div class="trending-post-items pos-rel col-4 float-l" >

                        </div>
                        <div class="read-details col-75 float-l pad color-tp-pad">
                            <div class="read-categories">
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

                <?php
                $count++;
                endwhile;
                endif;
                wp_reset_postdata();
                ?>
            </div>
        </div>
        </div>

        <!-- Trending line END -->
        <?php

    }
endif;

add_action('newsever_action_banner_trending_posts', 'newsever_banner_trending_posts', 10);