<?php
/**
 * Receommended
 *
 * @package True News
 */
if (!function_exists('true_news_block_recommended_section')):
    function true_news_block_recommended_section($true_news_home_section, $repeat_times){

        $true_news_default = true_news_get_default_theme_options();
        $section_title = esc_html( isset($true_news_home_section->section_title) ? $true_news_home_section->section_title : '');
        $post_cat = esc_html( isset($true_news_home_section->post_category) ? $true_news_home_section->post_category : '');
        $hide_post_author_avatar = esc_html( isset($true_news_home_section->hide_post_author_avatar) ? $true_news_home_section->hide_post_author_avatar : 'no');
        $hide_post_author = esc_html( isset($true_news_home_section->hide_post_author) ? $true_news_home_section->hide_post_author : 'no');
        $hide_post_date = esc_html( isset($true_news_home_section->hide_post_date) ? $true_news_home_section->hide_post_date : 'no');
        $post_cat_query = new WP_Query( array( 'post_type' => 'post','post_status' => 'publish', 'posts_per_page' => 4,'post__not_in' => get_option("sticky_posts"), 'category_name' => esc_html( $post_cat ),'post__not_in' => get_option("sticky_posts") ) );
        $ed_read_later_posts = absint( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) ); ?>
        <div class="block-elements block-elements-recommended">
            <div class="wrapper">
                <div class="twp-row twp-row-small">
                    <?php
                    if ($section_title) { ?>
                        <div class="column column-3 column-md-10 column-sm-10">
                            <header class="content-header">
                                <h2 class="block-title">
                                    <?php echo esc_html($section_title); ?> <i class="ion ion-md-arrow-dropright"></i>
                                </h2>
                            </header>
                        </div>
                    <?php } ?>

                    <?php if( $post_cat_query->have_posts() ): ?>

                        <div class="column column-7 column-md-10 column-sm-10 twp-latest-post-<?php echo esc_attr( $repeat_times ); ?>">
                            
                            <div class="twp-row twp-row-small latest-blog-wrapper">
                                <?php while ($post_cat_query->have_posts()):
                                    $post_cat_query->the_post(); ?>
                                    <div class="column column-5">
                                        <article class="related-items">
                                            <div class="twp-row twp-row-small">

                                                <?php
                                                if ( get_the_post_thumbnail() ) : ?>
                                                    <div class="column column-3">
                                                        <div class="post-thumb">
                                                            <?php if( get_the_post_thumbnail() ){ ?>
                                                                <?php true_news_post_thumbnail('true-news-small'); ?>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                <?php
                                                endif; ?>

                                                <div class="column <?php if ( !get_the_post_thumbnail() ){ echo 'column-10'; }else{ echo 'column-7'; } ?>">
                                                    <div class="post-content">
                                                        <h3 class="entry-title entry-title-small">
                                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                            <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                        </h3>
                                                        <div class="entry-meta entry-meta-1">
                                                            <span class="posted-on">
                                                                <?php true_news_entry_footer($hide_post_author_avatar, $hide_post_author, $hide_post_date,$comment = false); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </article>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                            <a href="javascript:void(0)" class="infinity-btn" >
                                <span date="<?php echo esc_attr( $hide_post_date ); ?>" author="<?php echo esc_attr( $hide_post_author ); ?>" author-avtar="<?php echo esc_attr( $hide_post_author_avatar ); ?>" paged="2" repeat-time="<?php echo esc_attr( $repeat_times ); ?>" data-cat="<?php echo esc_attr( $post_cat ); ?>" class="twp-recommended-loadmore">
                                    <?php esc_html_e('Load More Posts','true-news'); ?>
                                </span>
                            </a>

                        </div>

                    <?php 
                    wp_reset_postdata();
                    endif; ?>

                </div>
            </div>
        </div>
        <?php
    }
endif;