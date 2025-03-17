<?php
/**
 * Block 5
 *
 * @package True News
 */
if( !function_exists( 'true_news_block_5_section' ) ):
	function true_news_block_5_section( $true_news_home_section, $repeat_times ){
        
        $true_news_default = true_news_get_default_theme_options();
        $ed_read_later_posts = absint( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) );
        $section_title = esc_html( isset( $true_news_home_section->section_title ) ? $true_news_home_section->section_title : '' );
        $post_category_1 = esc_html( isset( $true_news_home_section->post_category_1 ) ? $true_news_home_section->post_category_1 : '' );
        $post_category_2 = esc_html( isset( $true_news_home_section->post_category_2 ) ? $true_news_home_section->post_category_2 : '' );
        $post_category_3 = esc_html( isset( $true_news_home_section->post_category_3 ) ? $true_news_home_section->post_category_3 : '' );
        $post_category_4 = esc_html( isset( $true_news_home_section->post_category_4 ) ? $true_news_home_section->post_category_4 : '' );
        $hide_post_author_avatar = esc_html( isset( $true_news_home_section->hide_post_author_avatar ) ? $true_news_home_section->hide_post_author_avatar : '' );
        $hide_post_author = esc_html( isset( $true_news_home_section->hide_post_author ) ? $true_news_home_section->hide_post_author : '' );
        $hide_post_date = esc_html( isset( $true_news_home_section->hide_post_date ) ? $true_news_home_section->hide_post_date : '' );
        $ctas_arrays = array();
        
        if( $post_category_1 ){
            array_push( $ctas_arrays,$post_category_1 );
        }
        if( $post_category_2 ){
            array_push( $ctas_arrays,$post_category_2 );
        }
        if( $post_category_3 ){
            array_push( $ctas_arrays,$post_category_3 );
        }
        if( $post_category_4 ){
            array_push( $ctas_arrays,$post_category_4 );
        }
        $ctas_arrays = array_unique( $ctas_arrays );
        ?>
		<div class="block-elements block-elements-5">
            <?php if( $section_title ): ?>
                <div class="wrapper">
                    <div class="twp-row twp-row-small">
                        <div class="column column-10">
                            <div class="content-header content-header-center">
                                <h2 class="block-title">
                                    <?php echo esc_html( $section_title ); ?>
                                </h2>
                                 <div hs-paa="<?php echo esc_attr( $hide_post_author_avatar ); ?>" hs-pd="<?php echo esc_attr( $hide_post_date ); ?>" hs-pa="<?php echo esc_attr( $hide_post_author ); ?>" class="title-controls tn-tab-title">
                                    <?php
                                    foreach( $ctas_arrays as $ctas ){ 
                                        $cat_info = get_category_by_slug( $ctas );
                                        $cat_id = $cat_info->term_id; ?>
                                        <a href="javascript:void(0)" cat-id="<?php echo esc_attr( $cat_id ); ?>"><?php echo esc_html( $cat_info->name ); ?></a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            <?php endif; ?>
            <div class="wrapper">
                <?php
                $i = 1;
                foreach( $ctas_arrays as $ctas ){ 
                    $cat_info = get_category_by_slug( $ctas );
                    $cat_id = $cat_info->term_id;
                    if( $i == 1 ){
                        $post_cat_query = new WP_Query( array('post_type' => 'post', 'posts_per_page' => 4,'post__not_in' => get_option("sticky_posts"), 'category_name' => esc_html( $ctas ) ) );
                        if( $post_cat_query->have_posts() ): ?>
                            <div class="twp-masonary-layout all-item-content tn-content-active <?php echo esc_attr( $cat_id ).'-content'; ?> content-loded">
                                <?php
                                while( $post_cat_query->have_posts() ):
                                    $post_cat_query->the_post();
                                    $format = get_post_format( get_the_ID() ); ?>
                                    <div class="twp-masonary-item">
                                        <div class="content-grid">
                                            <article class="news-article">
                                                <div class="news-article-grid">
                                                    <?php true_news_get_post_formate(); ?>
                                                    <div class="entry-details">
                                                        <?php if( $format != 'quote' ){ ?>
                                                            <h3 class="entry-title entry-title-small">
                                                                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                                                                <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                            </h3>
                                                        <?php } ?>
                                                        
                                                        <?php true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); ?>
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php
                        endif;
                    }else{ ?>
                        <div class="twp-masonary-layout <?php echo esc_attr( $cat_id ).'-content'; ?>"></div>
                    <?php }
                    $i++;
                } ?>
                <div class="tn-infinity-scroll">
                    <?php

                    $j = 1;
                    foreach( $ctas_arrays as $ctas ){
                        $cat_info = get_category_by_slug( $ctas );
                        $cat_id = $cat_info->term_id; ?>
                        <a href="javascript:void(0)" class="infinity-btn-tab <?php echo esc_attr( $cat_id ).'-current-tab'; ?>">
                            <span paged="2" current-id="<?php echo esc_attr( $cat_id ); ?>" class="<?php if( $j == 1 ){ echo 'loadmore'; }else{ echo 'loadmore-tab'; } ?>"><?php echo esc_html('Load More Posts','true-news'); ?></span>
                        </a>
                        <?php
                        $j++;
                    } ?>
                </div>
            </div>
        </div>
	<?php
    }
endif;