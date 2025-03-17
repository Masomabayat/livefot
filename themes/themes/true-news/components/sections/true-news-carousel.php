<?php
/**
 * Block 4
 *
 * @package True News
 */
if( !function_exists( 'true_news_block_7_section' ) ):
    function true_news_block_7_section( $true_news_home_section, $repeat_times ){

        $true_news_default = true_news_get_default_theme_options();
        $section_title = esc_html( isset( $true_news_home_section->section_title ) ? $true_news_home_section->section_title : '' );
        $post_cat = esc_html( isset( $true_news_home_section->post_category ) ? $true_news_home_section->post_category : '' );
        $hide_post_author_avatar = esc_html( isset( $true_news_home_section->hide_post_author_avatar ) ? $true_news_home_section->hide_post_author_avatar : '' );
        $hide_post_author = esc_html( isset( $true_news_home_section->hide_post_author ) ? $true_news_home_section->hide_post_author : '' );
        $hide_post_date = esc_html( isset( $true_news_home_section->hide_post_date ) ? $true_news_home_section->hide_post_date : '' );
        $post_cat_query = new WP_Query( array('post_type' => 'post', 'posts_per_page' => 10,'post__not_in' => get_option("sticky_posts"), 'category_name' => esc_html( $post_cat ) ) );
        $hide_post_category = esc_html( isset( $true_news_home_section->hide_post_category ) ? $true_news_home_section->hide_post_category : '' );
        $ed_read_later_posts = absint( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) );
        $slider_autoplay = esc_html( isset($true_news_home_section->slider_autoplay) ? $true_news_home_section->slider_autoplay : '');
        $slider_dots = esc_html( isset($true_news_home_section->slider_dots) ? $true_news_home_section->slider_dots : '');
        $slider_arrows = esc_html( isset($true_news_home_section->slider_arrows) ? $true_news_home_section->slider_arrows : '');

        if ( $slider_autoplay == 'yes' ) {
            $autoplay = 'true';
        }else{
            $autoplay = 'false';
        }
        if( $slider_dots == 'yes' ) {
            $dots = 'true';
        }else {
            $dots = 'false';
        }
        if( is_rtl() ) {
            $rtl = 'true';
        }else{
            $rtl = 'false';
        } ?>
        <div class="block-elements block-elements-7">
            
            <div class="wrapper">
                <div class="twp-row">
                    <div class="column column-10">
                        <div class="content-header">
                            <?php if( $section_title ){ ?>
                                <h2 class="block-title block-title-large text-white">
                                    <?php echo esc_html( $section_title ); ?>
                                </h2>
                            <?php } ?>

                            <?php if( $slider_arrows != 'no' ){ ?>

                                <div class="title-controls title-controls-nav">
                                    <button type="button" class="twp-slide-prev slide-icon-1 slide-prev-3 slick-arrow">
                                        <i class="ion ion-ios-arrow-back slick-arrow meta-icon meta-icon-large"></i>
                                    </button>
                                    <button type="button" class="twp-slide-next slide-icon-1 slide-next-3 slick-arrow">
                                        <i class="ion ion-ios-arrow-forward slick-arrow meta-icon meta-icon-large"></i>
                                    </button>
                                </div>

                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>
            <?php if( $post_cat_query->have_posts() ): ?>
                <div class="wrapper">
                    <div class="twp-row">
                        <div class="column column-10">
                            <div class="content-wrapper content-wrapper-carousel">
                                <div class="carousel-view slick-slider-space" data-slick='{"autoplay": <?php echo esc_attr( $autoplay ); ?>, "dots": <?php echo esc_attr( $dots ); ?>, "rtl": <?php echo esc_attr( $rtl ); ?>}'>
                                    <?php
                                    while( $post_cat_query->have_posts() ):
                                        $post_cat_query->the_post();
                                        $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'true-news-medium' ); ?>
                                        <div class="content-slides">
                                            <article class="news-article">
                                                <div class="news-article-grid">

                                                        <div class="entry-image entry-image-1">
                                                            
                                                            <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
                                                                <span class="entry-image data-bg data-bg-medium" data-background="<?php echo esc_url( $featured_image[0] ); ?>" ></span>
                                                            </a>

                                                            <?php
                                                            if( $hide_post_category != 'yes' ) { ?>
                                                                <div class="meta-categories-1">
                                                                    <?php true_news_post_tag_cats(); ?>
                                                                </div>
                                                            <?php } ?>

                                                        </div>

                                                    <div class="entry-details">
                                                        <h3 class="entry-title entry-title-small">
                                                            <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                                            <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                        </h3>
                                                        
                                                        <?php true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); ?>
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                wp_reset_postdata();
            endif; ?>
        </div>
    <?php
    }
endif;