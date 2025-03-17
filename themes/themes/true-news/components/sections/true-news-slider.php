<?php
/**
 * Block 4
 *
 * @package True News
 */
if (!function_exists('true_news_block_6_section')):
    function true_news_block_6_section($true_news_home_section, $repeat_times)
    {   
        $true_news_default = true_news_get_default_theme_options();
        $post_cat = esc_html( isset($true_news_home_section->section_block_1_cat_1) ? $true_news_home_section->section_block_1_cat_1 : '');
        $hide_post_author_avatar = esc_html( isset($true_news_home_section->hide_post_author_avatar) ? $true_news_home_section->hide_post_author_avatar : '');
        $hide_post_author = esc_html( isset($true_news_home_section->hide_post_author) ? $true_news_home_section->hide_post_author : '');
        $hide_post_date = esc_html( isset($true_news_home_section->hide_post_date) ? $true_news_home_section->hide_post_date : '');
        $slider_autoplay = esc_html( isset($true_news_home_section->slider_autoplay) ? $true_news_home_section->slider_autoplay : '');
        $slider_dots = esc_html( isset($true_news_home_section->slider_dots) ? $true_news_home_section->slider_dots : '');
        $slider_arrows = esc_html( isset($true_news_home_section->slider_arrows) ? $true_news_home_section->slider_arrows : '');
        $hide_slider_overlay = esc_html( isset($true_news_home_section->hide_slider_overlay) ? $true_news_home_section->hide_slider_overlay : '');
        $post_cat_query = new WP_Query(array('post_type' => 'post', 'posts_per_page' => 4,'post__not_in' => get_option("sticky_posts"), 'category_name' => esc_html($post_cat)));
        $hide_post_category = esc_html( isset( $true_news_home_section->hide_post_category ) ? $true_news_home_section->hide_post_category : '' );
        $ed_read_later_posts = absint( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) );

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
        if( $slider_arrows == 'yes' ) {
            $arrows = 'true';
        }else {
            $arrows = 'false';
        }
        if( is_rtl() ) {
            $rtl = 'true';
        }else{
            $rtl = 'false';
        }
        if ($post_cat_query->have_posts()): ?>
            <div class="block-elements block-elements-6">
                <div class="wrapper">
                    <div class="twp-row">
                        <div class="column column-10">
                            <div class="content-wrapper content-wrapper-1">
                                <div class="twp-main-slider <?php if( $hide_slider_overlay != 'yes' ){ echo 'twp-slider-overlay'; } ?>" data-slick='{"autoplay": <?php echo esc_attr( $autoplay ); ?>, "dots": <?php echo esc_attr( $dots ); ?>, "arrows": <?php echo esc_attr( $arrows ); ?>, "rtl": <?php echo esc_attr( $rtl ); ?>}'>
                                    <?php
                                    while ($post_cat_query->have_posts()):
                                        $post_cat_query->the_post();
                                        $featured_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?>
                                        <div class="content-slides data-bg data-bg-large" data-background="<?php echo esc_url($featured_image[0]); ?>">
                                            <article class="news-article">
                                                <div class="news-article-wrapper">
                                                    <div class="meta-categories-3 slide-animated">
                                                        <?php if( $hide_post_category != 'yes' ) { true_news_post_tag_cats(); } ?>
                                                    </div>
                                                    <h3 class="entry-title entry-title-big slide-animated">
                                                        <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                                        <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                    </h3>
                                                    <div class="slide-animated">
                                                        <?php true_news_entry_meta($hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false); ?>
                                                    </div>
                                                    <div class="entry-details slide-animated">
                                                        <?php the_excerpt(); ?>
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
            </div>
            <?php
            wp_reset_postdata();
        endif;
    }
endif;