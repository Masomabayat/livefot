<?php
if (!function_exists('covernews_front_page_main_section_2')) :
    /**
     * Banner Slider
     *
     * @since CoverNews 1.0.0
     *
     */
    function covernews_front_page_main_section_2()
    {


        $global_content_layout = covernews_get_option('global_content_layout');
        $covernews_enable_main_slider = covernews_get_option('show_main_news_section');
        $covernews_slider_title = covernews_get_option('main_news_slider_title');
        $covernews_slider_category = covernews_get_option('select_slider_news_category');
        $covernews_number_of_slides = covernews_get_option('number_of_slides');

        $covernews_editors_picks_title = covernews_get_option('editors_picks_title');
        $covernews_editors_picks_category = covernews_get_option('select_editors_picks_category');
        $covernews_editors_picks_number = covernews_get_option('select_editors_picks_number');
        if($covernews_editors_picks_number == 'slider-with-3-editors-picks'){
            $covernews_number_of_featured_news = 3;
            $col_class_6 = 'col-sm-7';
            $col_class_4 = 'col-sm-5';
        }elseif($covernews_editors_picks_number == 'slider-with-2-editors-picks'){
            $covernews_number_of_featured_news = 2;
            $col_class_6 = 'col-sm-8';
            $col_class_4 = 'col-sm-4';
        }else{
            $covernews_number_of_featured_news = 4;
            $col_class_6 = 'col-sm-6';
            $col_class_4 = 'col-sm-6';
    }

        $covernews_main_banner_section_order_2 = covernews_get_option('select_main_banner_section_order_2');

        $color_class = covernews_get_category_color_class($covernews_slider_category);
        $covernews_nav_control_class = empty($covernews_slider_title) ? 'no-section-title' : '';
        ?>

        <section class="af-blocks">
            
                <div class="container af-main-banner default-section-slider aft-banner-2 <?php echo esc_attr($covernews_main_banner_section_order_2); ?>">
                    <div class="row">
                        <?php do_action('covernews_action_banner_exclusive_posts'); ?>
<?php if ($covernews_enable_main_slider): ?>
                        <div class="for-main-row <?php echo esc_attr($covernews_editors_picks_number); ?>">
                            <div class="main-story-wrapper <?php echo esc_attr($col_class_6); ?>">
                                <?php if ($covernews_slider_title): ?>
                                <h4 class="header-after1">
                                    <span class="header-after <?php echo esc_attr($color_class); ?>">

                                        <?php echo apply_filters( 'the_title', $covernews_slider_title); ?>
                                    </span>


                                </h4>
                            <?php endif; ?>

                                <div class="main-slider-wrapper">
                                <div class="main-slider full-slider-mode">

                                    <?php
                                    $slider_posts = covernews_get_posts($covernews_number_of_slides, $covernews_slider_category);


                                    if ($slider_posts->have_posts()) :
                                        while ($slider_posts->have_posts()) : $slider_posts->the_post();
                                            global $post;
                                            $thumbnail_size = 'covernews-slider-center';
                                            ?>
                                            <figure class="slick-item">
                                                <div class="data-bg-hover data-bg-slide read-bg-img">
                                                    <a class="aft-slide-items" href="<?php the_permalink(); ?>">
                                                        <?php if ( has_post_thumbnail() ):
                                                            the_post_thumbnail($thumbnail_size);
                                                        endif;
                                                        ?>
                                                    </a>
                                                    <?php echo covernews_post_format($post->ID); ?>
                                                    <figcaption class="slider-figcaption slider-figcaption-1">
                                                        <div class="figure-categories figure-categories-bg">


                                                            <?php covernews_post_categories(); ?>
                                                        </div>
                                                        <div class="title-heading">
                                                            <h3 class="article-title slide-title">
                                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                            </h3>
                                                        </div>
                                                        <div class="grid-item-metadata grid-item-metadata-1">
                                                            <?php covernews_post_item_meta(); ?>
                                                        </div>
                                                    </figcaption>
                                                </div>
                                            </figure>


                                        <?php
                                        endwhile;
                                    endif;
                                    wp_reset_postdata();
                                    ?>
                                </div>

                                <div class="af-main-navcontrols <?php echo esc_attr($covernews_nav_control_class); ?>"></div>
                            </div>
                            </div>
                            <?php

                            ?>

                            <div class="af-main-banner-editors-picks layout-2 categorized-story <?php echo esc_attr($col_class_4); ?>">
                                <?php if ($covernews_editors_picks_title): ?>
                                <h4 class="header-after1">
                                        <span class="header-after">

                                            <?php echo apply_filters( 'the_title', $covernews_editors_picks_title); ?>
                                        </span>
                                </h4>
                            <?php endif; ?>
                                <div class="featured-posts-grid">
                                    <div class="row">
                                        <?php

                                        $featured_posts = covernews_get_posts($covernews_number_of_featured_news, $covernews_editors_picks_category);
                                        if ($featured_posts->have_posts()) :
                                            while ($featured_posts->have_posts()) :
                                                $featured_posts->the_post();
                                                if (has_post_thumbnail()) {
                                                    $thumb = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'medium');
                                                    $url = $thumb['0'];
                                                } else {
                                                    $url = '';
                                                }
                                                global $post;
                                                $thumbnail_size ='medium';

                                                ?>

                                                <div class="col-sm-6 odd-grid">
                                                    <div class="spotlight-post" data-mh="banner-height">
                                                        <figure class="featured-article">
                                                            <div class="featured-article-wrapper">
                                                                <div class="data-bg-hover data-bg-featured read-bg-img">
                                                                    <a href="<?php the_permalink(); ?>">
                                                                        <?php if ( has_post_thumbnail() ):
                                                                            the_post_thumbnail($thumbnail_size);
                                                                        endif;
                                                                        ?>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <?php echo covernews_post_format($post->ID); ?>
                                                        </figure>

                                                        <figcaption class="cate-fig">
                                                            <div class="figure-categories figure-categories-bg">

                                                                <?php covernews_post_categories(); ?>
                                                            </div>
                                                            <div class="title-heading">
                                                                <h3 class="article-title article-title-2">
                                                                    <a href="<?php the_permalink(); ?>">
                                                                        <?php the_title(); ?>
                                                                    </a>
                                                                </h3>
                                                            </div>
                                                            <div class="grid-item-metadata">
                                                                <?php covernews_post_item_meta(); ?>
                                                            </div>
                                                        </figcaption>
                                                    </div>
                                                </div>


                                            <?php endwhile;
                                        endif;
                                        wp_reset_postdata();
                                        ?>

                                    </div>
                                </div>
                            </div>

                        </div>
  <?php endif; ?>
                    </div>
                </div>

          


            <div class="container container-full-width">
                <div class="row">
                    <?php

                    $covernews_enable_featured_news = covernews_get_option('show_featured_news_section');
                    if ($covernews_enable_featured_news):
                        $covernews_featured_news_title = covernews_get_option('featured_news_section_title');


                        ?>

                        <div class="af-main-banner-featured-posts grid-layout">

                            <?php if (!empty($covernews_featured_news_title)): ?>

                                <h4 class="header-after1 ">
                                <span class="header-after">

                                    <?php echo apply_filters( 'the_title', $covernews_featured_news_title); ?>
                                </span>

                                </h4>
                            <?php endif; ?>

                            <?php do_action('covernews_action_banner_featured_posts'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- end slider-section -->
        <?php
    }
endif;
add_action('covernews_action_front_page_main_section_2', 'covernews_front_page_main_section_2', 40);