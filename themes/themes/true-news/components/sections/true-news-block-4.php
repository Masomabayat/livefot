<?php
/**
 * Block 4
 *
 * @package True News
 */
if (!function_exists('true_news_block_4_section')):
    function true_news_block_4_section($true_news_home_section, $repeat_times){   

        $true_news_default = true_news_get_default_theme_options();
        $section_title = esc_html( isset($true_news_home_section->section_title) ? $true_news_home_section->section_title : '');
        $post_cat = esc_html( isset($true_news_home_section->post_category) ? $true_news_home_section->post_category : '');
        $hide_post_author_avatar = esc_html( isset($true_news_home_section->hide_post_author_avatar) ? $true_news_home_section->hide_post_author_avatar : '');
        $hide_post_author = esc_html( isset($true_news_home_section->hide_post_author) ? $true_news_home_section->hide_post_author : '');
        $hide_post_date = esc_html( isset($true_news_home_section->hide_post_date) ? $true_news_home_section->hide_post_date : '');
        $post_cat_query = new WP_Query(array('post_type' => 'post', 'posts_per_page' => 4,'post__not_in' => get_option("sticky_posts"), 'category_name' => esc_html($post_cat)));
        $hide_post_category = esc_html( isset( $true_news_home_section->hide_post_category ) ? $true_news_home_section->hide_post_category : '' );
        $ed_read_later_posts = absint( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) ); ?>
        
        <div class="block-elements block-elements-4">
            <?php if ($section_title) { ?>
                <div class="wrapper">
                    <div class="twp-row twp-row-small">
                        <div class="column column-10">
                            <div class="content-header content-header-center">
                                <h2 class="block-title">
                                    <?php echo esc_html($section_title); ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }
            if ($post_cat_query->have_posts()): ?>
                
                <div class="wrapper">
                    <div class="twp-row twp-row-small">
                        <?php
                        while ($post_cat_query->have_posts()):
                            $post_cat_query->the_post();
                            $featured_small_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'true-news-medium'); ?>
                            <div class="column column-quarter column-sm-5">
                                <div class="content-grid">
                                    <div class="content-wrapper content-wrapper-1 content-wrapper-4a">
                                        <article class="news-article">
                                            <div class="news-article-grid">

                                                <?php if( has_post_thumbnail() ||  $hide_post_category != 'yes' ): ?>
                                                    <div class="entry-image entry-image-1">
                                                        
                                                        <?php if( has_post_thumbnail() ){ true_news_post_thumbnail('true-news-medium'); } 

                                                        if( $hide_post_category != 'yes' ) { ?>
                                                            <div class="meta-categories-1">
                                                                <?php true_news_post_tag_cats(); ?>
                                                            </div>
                                                        <?php } ?>

                                                    </div>
                                                <?php endif; ?>

                                                <div class="entry-details">
                                                    <h3 class="entry-title entry-title-small">
                                                        <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                                        <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                    </h3>
                                                    <?php true_news_entry_meta($hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false); ?>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php
                wp_reset_postdata();
            endif;
            ?>
        </div>
        <?php
    }
endif;