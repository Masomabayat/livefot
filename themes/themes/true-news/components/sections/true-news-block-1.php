<?php
/**
 * Block 1
 *
 * @package True News
 */
if( !function_exists( 'true_news_block_1_section' ) ):
	function true_news_block_1_section( $true_news_home_section, $repeat_times ){

        $true_news_default = true_news_get_default_theme_options();
        $hide_post_author_avatar = isset( $true_news_home_section->hide_post_author_avatar ) ? $true_news_home_section->hide_post_author_avatar : '' ;
        $hide_post_author = esc_html( isset( $true_news_home_section->hide_post_author ) ? $true_news_home_section->hide_post_author : '' );
        $hide_post_date = esc_html( isset( $true_news_home_section->hide_post_date ) ? $true_news_home_section->hide_post_date : '' );
        $post_category_1 = esc_html( isset( $true_news_home_section->section_block_1_cat_1 ) ? $true_news_home_section->section_block_1_cat_1 : '' );
        $post_category_2 = esc_html( isset( $true_news_home_section->section_block_1_cat_2 ) ? $true_news_home_section->section_block_1_cat_2 : '' );
        $post_category_3 = esc_html( isset( $true_news_home_section->section_block_1_cat_3 ) ? $true_news_home_section->section_block_1_cat_3 : '' );
        $section_title_1 = esc_html( isset( $true_news_home_section->section_block_1_title_1 ) ? $true_news_home_section->section_block_1_title_1 : '' );
        $section_title_2 = esc_html( isset( $true_news_home_section->section_block_1_title_2 ) ? $true_news_home_section->section_block_1_title_2 : '' );
        $section_title_3 = esc_html( isset( $true_news_home_section->section_block_1_title_3 ) ? $true_news_home_section->section_block_1_title_3 : '' );
        $hide_post_category = esc_html( isset( $true_news_home_section->hide_post_category ) ? $true_news_home_section->hide_post_category : '' );
        $slider_autoplay = esc_html( isset($true_news_home_section->slider_autoplay) ? $true_news_home_section->slider_autoplay : '');
        $slider_dots = esc_html( isset($true_news_home_section->slider_dots) ? $true_news_home_section->slider_dots : '');
        $slider_arrows = esc_html( isset($true_news_home_section->slider_arrows) ? $true_news_home_section->slider_arrows : '');
        $ed_read_later_posts = absint( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) );

        if( $slider_autoplay == 'yes' ){
            $autoplay = 'true';
        }else{
            $autoplay = 'false';
        }
        if( $slider_dots == 'yes' ){
            $dots = 'true';
        }else {
            $dots = 'false';
        }
        if( $slider_arrows == 'yes' ){
            $arrows = 'true';
        }else {
            $arrows = 'false';
        }
        if( is_rtl() ){
            $rtl = 'true';
        }else{
            $rtl = 'false';
        }

        
        $left_block_2_query_1 = new WP_Query( array('post_type' => 'post', 'posts_per_page' => 5,'post__not_in' => get_option("sticky_posts"), 'category_name' => esc_html( $post_category_1 ) ) );
        $left_block_2_query_2 = new WP_Query( array('post_type' => 'post', 'posts_per_page' => 5,'post__not_in' => get_option("sticky_posts"), 'category_name' => esc_html( $post_category_2 ) ) );
        $left_block_2_query_3 = new WP_Query( array('post_type' => 'post', 'posts_per_page' => 10,'post__not_in' => get_option("sticky_posts"), 'category_name' => esc_html( $post_category_3 ) ) ); ?>
		<div class="block-elements block-elements-1">
            <div class="wrapper">
                <div class="twp-row">

                    <div class="column column-5 column-md-10 column-sm-10">
                        <div class="content-wrapper content-wrapper-1 content-wrapper-1a">
                            
                            <?php if( $section_title_1 ): ?>
                                <div class="content-header">
                                    <h2 class="block-title">
                                        <?php echo esc_html( $section_title_1 ); ?>
                                    </h2>
                                </div>
                            <?php endif;

                            if( $left_block_2_query_1->have_posts() ): ?>
                                <div class="twp-slick-slider" data-slick='{"autoplay": <?php echo esc_attr( $autoplay ); ?>, "dots": <?php echo esc_attr( $dots ); ?>, "arrows": <?php echo esc_attr( $arrows ); ?>, "rtl": <?php echo esc_attr( $rtl ); ?>}'>
                                    <?php while( $left_block_2_query_1->have_posts() ):
                                        $left_block_2_query_1->the_post();
                                        $featured_image_1 = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large'); ?>
                                        <div class="content-slides <?php if ( !get_the_post_thumbnail() ){ echo 'twp-has-no-thumb'; } ?>">
                                            <article class="news-article">

                                                <div class="post-thumbnail">
                                                    <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
                                                        <span class="entry-image data-bg data-bg-big" data-background="<?php echo esc_url( $featured_image_1[0] ); ?>" ></span>
                                                    </a>
                                                </div>

                                                <div class="meta-categories-3 meta-categories-3a">
                                                    <?php if( $hide_post_category != 'yes' ) { true_news_post_tag_cats(); } ?>
                                                </div>
                                                <h3 class="entry-title entry-title-big">
                                                    <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                                    <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                </h3>
                                                
                                                <?php true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); ?>
                                                <div class="entry-details entry-details-medium">
                                                    <?php 
                                                    if( has_excerpt() ){
                                                        the_excerpt();
                                                    }else{
                                                        echo esc_html( wp_trim_words( get_the_content(),50,'...' ) );
                                                    } ?>

                                                </div>
                                            </article>
                                        </div>
                                    <?php endwhile; ?>
                                </div>

                                <?php
                                wp_reset_postdata();
                            endif; ?>
                            
                        </div>
                    </div>

                    <div class="column column-quarter column-md-5 column-sm-5 column-lr-border">
                        <div class="content-wrapper content-wrapper-1 content-wrapper-2a">
                            
                            <?php if( $section_title_2 ): ?>
                                <div class="content-header">
                                    <h2 class="block-title">
                                        <?php echo esc_html( $section_title_2 ); ?>
                                    </h2>
                                </div>
                            <?php endif;
                            if( $left_block_2_query_2->have_posts() ): ?>
                                <div class="content-main">

                                    <?php
                                    $i = 1;
                                    while( $left_block_2_query_2->have_posts() ):
                                        $left_block_2_query_2->the_post();

                                        if( $i == 1 ){  ?>

                                            <div class="content-panel <?php if ( !get_the_post_thumbnail() ){ echo 'twp-has-no-thumb'; } ?>">
                                                <article class="news-article">
                                                    <div class="news-article-panel">
                                                        <?php if( has_post_thumbnail() ||  $hide_post_category != 'yes' ): ?>
                                                            <div class="entry-image">
                                                                
                                                                <?php if( has_post_thumbnail() ){ true_news_post_thumbnail('true-news-medium'); } ?>

                                                                 <?php if( $hide_post_category != 'yes' ) { ?>
                                                                    <div class="meta-categories-1">
                                                                        <?php true_news_post_tag_cats(); ?>
                                                                    </div>
                                                                <?php } ?>

                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="entry-details">
                                                            <h3 class="entry-title entry-title-medium">
                                                                <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                                                <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                            </h3>
                                                            
                                                            <?php true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); ?>
                                                        </div>
                                                    </div>
                                                </article>
                                            </div>

                                        <?php
                                        }else{ ?>

                                            <div class="content-list <?php if ( !get_the_post_thumbnail() ){ echo 'twp-has-no-thumb'; } ?>">
                                                <article class="news-article">
                                                    <div class="news-article-list direct-ltr">

                                                        <?php if( has_post_thumbnail() ): ?>
                                                            <div class="entry-image entry-image-1">
                                                                <?php if( has_post_thumbnail() ){
                                                                    true_news_post_thumbnail('true-news-small');
                                                                } ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="entry-details">
                                                            <h3 class="entry-title entry-title-small">
                                                                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                                                                <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                            </h3>
                                                            <?php true_news_entry_meta( $hide_post_author_avatar = 'yes', $hide_post_author = 'yes', $hide_post_date, $text = false ); ?>
                                                        </div>
                                                    </div>
                                                </article>
                                            </div>
                                        <?php
                                        }

                                        $i++;
                                    endwhile; ?>

                                </div>
                                <?php
                                wp_reset_postdata();
                            endif; ?>
                        </div>
                    </div>

                    <div class="column column-quarter column-md-5 column-sm-5">
                        <div class="content-wrapper content-wrapper-1 content-wrapper-3a">
                            <div class="content-header">
                                
                                <?php if( $section_title_3 ): ?>
                                    <h2 class="block-title block-title-medium">
                                        <?php echo esc_html( $section_title_3 ); ?>
                                    </h2>
                                <?php endif; ?>

                                <?php if( $slider_arrows != 'no' ) { ?>
                                    <div class="title-controls title-controls-nav">
                                        <button type="button" class="twp-slide-prev slide-icon-1 slide-prev-2 slick-arrow">
                                            <i class="ion ion-ios-arrow-back slick-arrow meta-icon meta-icon-large"></i>
                                        </button>
                                        <button type="button" class="twp-slide-next slide-icon-1 slide-next-2 slick-arrow">
                                            <i class="ion ion-ios-arrow-forward slick-arrow meta-icon meta-icon-large"></i>
                                        </button>
                                    </div>
                                <?php } ?>

                            </div>
                            <?php if( $left_block_2_query_3->have_posts() ): ?>
                                <div class="verticle-slider" data-slick='{"autoplay": <?php echo esc_attr( $autoplay ); ?>}'>
                                    <?php
                                    while( $left_block_2_query_3->have_posts() ):
                                        $left_block_2_query_3->the_post();
                                        $featured_image_2 = wp_get_attachment_image_src(get_post_thumbnail_id(), 'true-news-small'); ?>
                                        
                                        <div class="verticle-slides <?php if ( !get_the_post_thumbnail() ){ echo 'twp-has-no-thumb'; } ?>">
                                            <article class="news-article">
                                                <div class="news-article-list direct-ltr">
                                                    <div class="entry-image">
                                                        <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
                                                            <span class="entry-image data-bg data-bg-small" data-background="<?php echo esc_url( $featured_image_2[0] ); ?>" ></span>
                                                        </a>
                                                    </div>
                                                    <div class="entry-details">
                                                        <h3 class="entry-title entry-title-small">
                                                            <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                                            <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                        </h3>
                                                        <?php true_news_entry_meta( $hide_post_author_avatar = 'yes', $hide_post_author = 'yes', $hide_post_date, $text = false ); ?>
                                                    </div>
                                                </div>
                                            </article>
                                        </div>

                                    <?php endwhile; ?>
                                </div>
                                <?php 
                                wp_reset_postdata();
                            endif; ?>

                        </div>
                    </div>

                </div>
            </div>
        </div>
	<?php }
endif;
