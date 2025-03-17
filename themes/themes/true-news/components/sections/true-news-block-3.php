<?php
/**
 * Latest News
 *
 * @package True News
 */

if( !function_exists( 'true_news_block_3_section' ) ):
    function true_news_block_3_section( $true_news_home_section, $repeat_times ){

        $true_news_default = true_news_get_default_theme_options();
        $section_title = esc_html( isset( $true_news_home_section->section_block_1_title_left ) ? $true_news_home_section->section_block_1_title_left : '' );
        $post_category_1 = esc_html( isset( $true_news_home_section->section_block_1_cat_left ) ? $true_news_home_section->section_block_1_cat_left : '' );
        $hide_post_author_avatar = esc_html( isset( $true_news_home_section->hide_post_author_avatar ) ? $true_news_home_section->hide_post_author_avatar : '' );
        $hide_post_author = esc_html( isset( $true_news_home_section->hide_post_author ) ? $true_news_home_section->hide_post_author : '' );
        $hide_post_date = esc_html( isset( $true_news_home_section->hide_post_date ) ? $true_news_home_section->hide_post_date : '' );
        $section_title_1 = esc_html( isset( $true_news_home_section->section_title_3 ) ? $true_news_home_section->section_title_3 : '' );
        $post_category_2 = esc_html( isset( $true_news_home_section->post_category_right_side ) ? $true_news_home_section->post_category_right_side : '' );
        $hide_post_category = esc_html( isset( $true_news_home_section->hide_post_category ) ? $true_news_home_section->hide_post_category : '' );
        $ed_read_later_posts = absint( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) );
        $left_post_cat_query = new WP_Query( array('post_type' => 'post', 'posts_per_page' => 3,'post__not_in' => get_option("sticky_posts"), 'category_name' => esc_html( $post_category_1 ) ) );
        $posts_array_3 = array();
        if( $left_post_cat_query->have_posts() ):
            
            while( $left_post_cat_query->have_posts() ):
                $left_post_cat_query->the_post();
                $posts_array_3[] = get_the_ID();
            endwhile;
            wp_reset_postdata();
        endif;
        
        $right_post_cat_query = new WP_Query( array('post_type' => 'post','post__not_in' => $posts_array_3,'post__not_in' => get_option("sticky_posts"), 'posts_per_page' => 5, 'category_name' => esc_html( $post_category_2 ) ) );

        $posts_array_1 = array();
        $posts_array_2 = array();
        if( $right_post_cat_query->have_posts() ):
            $i = 1;
            while( $right_post_cat_query->have_posts() ):
                $right_post_cat_query->the_post();
                if( $i == 1 || $i == 2 ){
                    $posts_array_1[] = get_the_ID();
                }else{
                    $posts_array_2[] = get_the_ID();
                }
                
                if( $i == 5 ){ break; }
                $i++;
            endwhile;
            wp_reset_postdata();
        endif;

        $right_right_post_query_1 = new WP_Query( array('post_type' => 'post', 'posts_per_page' => 2,'post__not_in' => get_option("sticky_posts"), 'post__in' => $posts_array_1 ) );
        $right_right_post_query_2 = new WP_Query( array('post_type' => 'post', 'posts_per_page' => 3,'post__not_in' => get_option("sticky_posts"), 'post__in' => $posts_array_2 ) );
        ?>
        <div class="block-elements block-elements-3">
            <div class="wrapper">
                <div class="twp-row">
                    <div class="column column-4 column-md-10 column-sm-10">
                        <div class="content-wrapper content-wrapper-1 content-wrapper-3a">
                            <?php if( $section_title ): ?>
                                <div class="content-header">
                                    <h2 class="block-title">
                                        <?php echo esc_html( $section_title ); ?>
                                    </h2>
                                </div>
                            <?php endif;
                            if( $left_post_cat_query->have_posts() ): ?>
                                <div class="content-main">
                                    <?php
                                    while( $left_post_cat_query->have_posts() ):
                                        $left_post_cat_query->the_post(); ?>
                                        <div class="content-list">
                                            <article class="news-article">
                                                <div class="news-article-list news-article-list-medium direct-ltr">

                                                    <?php if(  has_post_thumbnail() ): ?>
                                                        <div class="entry-image entry-image-1">
                                                            <?php if(  has_post_thumbnail() ){ true_news_post_thumbnail('true-news-medium'); } ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="entry-details">

                                                        <div class="meta-categories-2">
                                                            <?php if( $hide_post_category != 'yes' ) { true_news_post_tag_cats(); } ?>
                                                        </div>

                                                        <h3 class="entry-title entry-title-medium">
                                                            <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                                                            <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                        </h3>

                                                        <?php true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); ?>

                                                        <div class="entry-details entry-details-small">
                                                            <?php 
                                                            if( has_excerpt() ){
                                                                the_excerpt();
                                                            }else{
                                                                echo esc_html( wp_trim_words( get_the_content(),13,'...' ) );
                                                            }
                                                            ?>
                                                        </div>

                                                        <a class="post-more" href="<?php the_permalink(); ?>"><?php esc_html_e('Read More','true-news'); ?></a>

                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    <?php
                                    endwhile; ?>
                                </div>
                                <?php
                                wp_reset_postdata();
                            endif; ?>
                        </div>
                    </div>
                    <div class="column column-6 column-md-10 column-sm-10">
                        <div class="content-wrapper content-wrapper-1 content-wrapper-3b">
                            <?php if( $section_title_1 ): ?>
                                <div class="content-header">
                                    <h2 class="block-title">
                                        <?php echo esc_html( $section_title_1 ); ?>
                                    </h2>
                                </div>
                            <?php endif; ?>
                            <?php
                            if( $right_right_post_query_1->have_posts() ):
                                while( $right_right_post_query_1->have_posts() ):
                                    $right_right_post_query_1->the_post(); ?>
                                    <div class="content-grid">
                                        <article class="news-article">
                                            <div class="twp-row twp-row-small">
                                                <?php if( has_post_thumbnail() ): ?>
                                                    <div class="column column-4 column-sm-5">
                                                        <div class="entry-image entry-image-1">
                                                            <?php true_news_post_thumbnail('true-news-medium'); ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="column <?php if ( !get_the_post_thumbnail() ){ echo 'column-10'; }else{ echo 'column-6'; } ?> column-sm-5">
                                                    <div class="entry-details">
                                                        <div class="meta-categories-3">
                                                            <?php if( $hide_post_category != 'yes' ) { true_news_post_tag_cats(); } ?>
                                                        </div>
                                                        <h3 class="entry-title entry-title-big">
                                                            <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                                                            <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                        </h3>
                                                        <?php true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                <?php
                                endwhile;
                                wp_reset_postdata();
                            endif; ?>
                        </div>
                        <?php
                        if( $right_right_post_query_2->have_posts() ): ?>
                            <div class="content-wrapper content-wrapper-1 content-wrapper-3c">
                                <div class="twp-row twp-row-small">
                                    <?php
                                    while( $right_right_post_query_2->have_posts() ):
                                        $right_right_post_query_2->the_post(); ?>
                                        <div class="column column-three column-md-three column-sm-three">
                                            <div class="content-main">
                                                <div class="content-panel">
                                                    <article class="news-article">
                                                        <div class="news-article-panel">

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
                                                                <h3 class="entry-title entry-title-medium">
                                                                    <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                                                                    <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                                </h3>
                                                                <?php true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); ?>
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
            </div>
        </div>
    <?php
    }
endif;
