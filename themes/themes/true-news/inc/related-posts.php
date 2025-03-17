<?php
/**
 * Related Posts Functions.
 *
 * @package True News
 */

if ( !function_exists('true_news_related_posts') ):

    // Single Posts Related Posts.
    function true_news_related_posts()
    {
        global $post;
        $true_news_default = true_news_get_default_theme_options();
        $hide_post_author_avatar = esc_html( get_theme_mod('twp_hide_post_author_avatar', $true_news_default['twp_hide_post_author_avatar']) );
        $hide_post_author = esc_html( get_theme_mod('twp_hide_post_author', $true_news_default['twp_hide_post_author']) );
        $hide_post_date = esc_html( get_theme_mod('twp_hide_post_date', $true_news_default['twp_hide_post_date']) );
        $hide_post_categories = esc_html( get_theme_mod( 'hide_post_categories',$true_news_default['hide_post_categories'] ) );
        if ($hide_post_author_avatar) {
            $hide_post_author_avatar = 'yes';
        } else {
            $hide_post_author_avatar = 'no';
        }
        if ($hide_post_author) {
            $hide_post_author = 'yes';
        } else {
            $hide_post_author = 'no';
        }
        if ($hide_post_date) {
            $hide_post_date = 'yes';
        } else {
            $hide_post_date = 'no';
        }
        $cats = get_the_category($post->ID);
        $category = array();
        if ($cats) {
            foreach ($cats as $cat) {
                $category[] = $cat->term_id;
            }
        }
        $related_posts_query = new WP_Query(array('post_type' => 'post', 'posts_per_page' => 5, 'post__not_in' => array($post->ID), 'category__in' => $category));
        $ed_related_post = absint(get_theme_mod('ed_related_post', $true_news_default['ed_related_post']));
        if ($ed_related_post && $related_posts_query->have_posts()):
            $i = 1;
            $posts_1 = array();
            $posts_2 = array();
            while ($related_posts_query->have_posts()) {
                $related_posts_query->the_post();
                if ($i == 1) {
                    $posts_1[] = absint( get_the_ID() );
                } else {
                    $posts_2[] = absint( get_the_ID() );
                }
                $i++;
            }
            wp_reset_postdata();
            $related_posts_query_1 = new WP_Query(array('post_type' => 'post', 'posts_per_page' => 1, 'post__in' => $posts_1));
            $related_posts_query_2 = new WP_Query(array('post_type' => 'post', 'posts_per_page' => 4, 'post__in' => $posts_2)); ?>
            <div class="twp-related-article">
                <div class="twp-row twp-row-small">

                    <?php while ($related_posts_query_1->have_posts()):
                        $related_posts_query_1->the_post(); ?>

                        <div class="column column-4 column-md-10 column-sm-10">
                            <div class="content-main">
                                <div class="content-panel">
                                    <article class="news-article">
                                        <div class="news-article-panel">

                                            <?php if (get_the_post_thumbnail()): ?>
                                                <div class="entry-image">
                                                    <?php true_news_post_thumbnail('true-news-medium'); ?>

                                                    <div class="meta-categories-2">
                                                        <?php if( !$hide_post_categories ){ true_news_post_tag_cats(); } ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="entry-details">
                                                <h3 class="entry-title entry-title-medium">
                                                    <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                                </h3>
                                                <?php true_news_entry_meta($hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false); ?>
                                            </div>

                                        </div>
                                    </article>
                                </div>
                            </div>
                        </div>

                    <?php endwhile;

                    wp_reset_postdata(); ?>
                    
                    <?php if ($related_posts_query_2->have_posts()): ?>
                        <div class="column column-6 column-md-10 column-sm-10">
                            <div class="twp-row twp-row-small">
                                <?php while ($related_posts_query_2->have_posts()):
                                    $related_posts_query_2->the_post(); ?>
                                    <div class="column column-5 column-md-5 column-sm-5">
                                        <div class="content-main">
                                            <div class="content-panel">
                                                <article class="news-article">
                                                    <div class="news-article-panel">
                                                        <?php if ( get_the_post_thumbnail()): ?>
                                                            <div class="entry-image">

                                                                <?php true_news_post_thumbnail('true-news-medium'); ?>

                                                                <div class="meta-categories-2">
                                                                    <?php if( !$hide_post_categories ){ true_news_post_tag_cats(); } ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="entry-details">
                                                            <h3 class="entry-title entry-title-small">
                                                                <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
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
                    endif; ?>
                </div>
            </div>
            <?php
            wp_reset_postdata();
        endif;
    }
endif;