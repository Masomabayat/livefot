<?php
if (!function_exists('newsever_banner_exclusive_posts')):
    /**
     * Ticker Slider
     *
     * @since Newsever 1.0.0
     *
     */
    function newsever_banner_exclusive_posts()
    {

        if (false != newsever_get_option('show_flash_news_section')) :
            //$dir = '';
            //$em_ticker_news_mode = newsever_get_option('select_flash_new_mode');
            $em_ticker_news_mode = 'aft-flash-slide left';
            $dir = 'left';
            if (is_rtl()) {
                $em_ticker_news_mode = 'aft-flash-slide right';
                $dir = 'right';
            }
            ?>
            <div class="banner-exclusive-posts-wrapper clearfix">

                <?php
                $category = newsever_get_option('select_flash_news_category');
                $number_of_posts = newsever_get_option('number_of_flash_news');
                $em_ticker_news_title = newsever_get_option('flash_news_title');
                $em_ticker_news_subtitle = newsever_get_option('flash_news_subtitle');

                $all_posts = newsever_get_posts($number_of_posts, $category);
                $show_trending = true;

                ?>

                <div class="container-wrapper">
                    <div class="exclusive-posts">
                        <div class="exclusive-now primary-color">
                            <?php if (!empty($em_ticker_news_title)): ?>
                                <span class="exclusive-news-title"> 
                                    <?php echo esc_html($em_ticker_news_title); ?>
                                </span>
                            <?php endif; ?>
                            <div class="exclusive-now-txt-animation-wrap">
                                <span class="fancy-spinner">
                                    <div class="ring"></div>
                                    <div class="ring"></div>
                                    <div class="dot"></div>
                                </span>
                                <span class="exclusive-texts-wrapper">
                                <?php if (!empty($em_ticker_news_subtitle)):
                                    $animation_opt = newsever_get_option('disable_animation');
                                    $animation_class = '';
                                    if ($animation_opt != true) {
                                        $animation_class = 'af-exclusive-animation';
                                    }
                                    ?>
                                    <span class="exclusive-news-subtitle <?php echo $animation_class; ?>">
                                        <span><?php echo esc_html($em_ticker_news_subtitle); ?></span>
                                    </span>
                                <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="exclusive-slides" dir="ltr">
                            <?php
                            if ($all_posts->have_posts()) : ?>
                            <div class='marquee <?php echo esc_attr($em_ticker_news_mode); ?>' data-speed='80000'
                                 data-gap='0' data-duplicated='true' data-direction="<?php echo esc_attr($dir); ?>">
                                <?php
                                while ($all_posts->have_posts()) : $all_posts->the_post();
                                    global $post;
                                    $url = newsever_get_freatured_image_url($post->ID, 'thumbnail');
                                    ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if ($show_trending == true): ?>

                                        <?php endif; ?>

                                        <span class="circle-marq">
                                        <?php if ($url) { ?>
                                            <img src="<?php echo esc_url($url); ?>"
                                                 alt="<?php the_title_attribute(); ?>">
                                        <?php } ?>
                                    </span>

                                        <?php the_title(); ?>
                                    </a>
                                <?php

                                endwhile;
                                endif;
                                wp_reset_postdata();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Excluive line END -->
        <?php
        endif;



    }
endif;

add_action('newsever_action_banner_exclusive_posts', 'newsever_banner_exclusive_posts', 10);