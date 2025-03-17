<?php
/**
 * Block 8
 *
 * @package True News
 */
if( !function_exists( 'true_news_block_8_section' ) ):
    function true_news_block_8_section( $true_news_home_section, $repeat_times ){

        $true_news_default = true_news_get_default_theme_options();
        $section_title = esc_html( isset( $true_news_home_section->section_title ) ? $true_news_home_section->section_title : '' );
        $hide_post_author_avatar = esc_html( isset( $true_news_home_section->hide_post_author_avatar ) ? $true_news_home_section->hide_post_author_avatar : '' );
        $hide_post_author = esc_html( isset( $true_news_home_section->hide_post_author ) ? $true_news_home_section->hide_post_author : '' );
        $hide_post_date = esc_html( isset( $true_news_home_section->hide_post_date ) ? $true_news_home_section->hide_post_date : '' );
        $ed_read_later_posts = absint( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) );
        $video_posts_post_query = new WP_Query(
            array(
                'post_type' => 'post',
                'posts_per_page' => 6,
                'post__not_in' => get_option("sticky_posts"),
                'tax_query' => array(
                   array(
                     'taxonomy' => 'post_format',
                     'field' => 'slug',
                     'terms' => 'post-format-video'
                   )
                ),
            )
        ); ?>
        <div class="block-elements block-elements-8">
            <?php if( $section_title ){ ?>
                <div class="wrapper">
                    <div class="twp-row">
                        <div class="column column-10">
                            <div class="content-header">
                                <h2 class="block-title">
                                    <?php echo esc_html( $section_title ); ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="wrapper">
                <div class="twp-row twp-row-small">
                    <?php if( $video_posts_post_query->have_posts() ):
                        $i = 0;
                        while( $video_posts_post_query->have_posts() ):
                            $video_posts_post_query->the_post();
                            $video_url = 'javascript:void(0)';
                            $content = apply_filters( 'the_content', get_the_content() );
                            $video = false;
                            // Only get video from the content if a playlist isn't present.
                            if ( false === strpos( $content, 'wp-playlist-script' ) ) {
                                $video = get_media_embedded_in_content( $content, array( 'video', 'object', 'embed', 'iframe' ) );
                            }
                            if ( ! empty( $video ) ) {
                                $j = 1;
                                foreach ( $video as $video_html ) {
                                    if( $j == 1 ){
                                        $video_html =  esc_html( $video_html );
                                        if( strpos( $video_html, 'youtube') != false ){
                                            $video_html = explode( '/embed/',$video_html );
                                            $video_html = explode( '?feature=oembed',$video_html[1] );
                                            $video_url = esc_url('https://www.youtube.com/watch?v='.$video_html[0]);
                                        }elseif( strpos( $video_html, 'vimeo') != false ){
                                            $video_html = explode( '/video/',$video_html );
                                            $video_html = explode( '?dnt=',$video_html[1] );
                                            $video_url = esc_url('https://vimeo.com/'.$video_html[0]);
                                        }
                                    }
                                    $j++;
                                }
                            }; ?>
                            <div class="column column-6 column-md-10 column-sm-10">
                                <div class="content-wrapper content-video content-video-panel">
                                    <div class="content-main">
                                        <div class="content-panel">
                                            <article class="news-article">
                                                <div class="news-article-panel">
                                                    <?php if( has_post_thumbnail() ): ?>
                                                        <div class="entry-image entry-image-1">
                                                            
                                                            <?php true_news_post_thumbnail(); ?>

                                                            <a href="<?php if( $video_url ){ echo esc_url( $video_url ); } ?>" class="popup-video" tabindex="0">
                                                                <span class="format-icon playback-animation">
                                                                    <i class="ion ion-ios-play"></i>
                                                                </span>
                                                            </a>
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
                            </div>
                            <?php
                            $i++;
                            if( $i == 1 ){ break; }
                        endwhile;
                        wp_reset_postdata();
                    endif; ?>
                    <?php if( $video_posts_post_query->have_posts() ): ?>
                        <div class="column column-4 column-md-10 column-sm-10">
                            <div class="content-wrapper content-video content-video-list">
                                <div class="content-main">
                                    <div class="content-list">
                                        <?php
                                        $i = 0;
                                        while( $video_posts_post_query->have_posts() ):
                                            $video_posts_post_query->the_post();
                                            if( $i != 1 ){
                                                $video_url = 'javascript:void(0)';
                                                $content = apply_filters( 'the_content', get_the_content() );
                                                $video = false;
                                                // Only get video from the content if a playlist isn't present.
                                                if ( false === strpos( $content, 'wp-playlist-script' ) ) {
                                                    $video = get_media_embedded_in_content( $content, array( 'video', 'object', 'embed', 'iframe' ) );
                                                }
                                                if ( ! empty( $video ) ) {
                                                    $j = 1;
                                                    foreach ( $video as $video_html ) {
                                                        if( $j == 1 ){
                                                            $video_html =  esc_html( $video_html );
                                                            if( strpos( $video_html, 'youtube') != false ){
                                                                $video_html = explode( '/embed/',$video_html );
                                                                $video_html = explode( '?feature=oembed',$video_html[1] );
                                                                $video_url = esc_url('https://www.youtube.com/watch?v='.$video_html[0]);
                                                            }elseif( strpos( $video_html, 'vimeo') != false ){
                                                                $video_html = explode( '/video/',$video_html );
                                                                $video_html = explode( '?dnt=',$video_html[1] );
                                                                $video_url = esc_url('https://vimeo.com/'.$video_html[0]);
                                                            }
                                                        }
                                                        $j++;
                                                    }
                                                }; ?>
                                                <article class="news-article <?php if ( !get_the_post_thumbnail() ){ echo 'twp-has-no-thumb'; } ?>">
                                                    <div class="news-article-list direct-ltr">
                                                        <?php if( has_post_thumbnail() ): ?>
                                                            <div class="entry-image entry-image-1">
                                                                
                                                                <?php true_news_post_thumbnail('medium'); ?>

                                                                <a href="<?php if( $video_url ){ echo esc_url( $video_url ); } ?>" class="popup-video" tabindex="0">
                                                                    <span class="format-icon playback-animation">
                                                                        <i class="ion ion-ios-play"></i>
                                                                    </span>
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="entry-details">
                                                            <h3 class="entry-title entry-title-small">
                                                                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                                                                <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                            </h3>
                                                            
                                                            <?php true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); ?>
                                                        </div>
                                                    </div>
                                                </article>
                                            <?php
                                            }
                                            $i++;
                                        endwhile; ?>
                                    </div>
                                </div>
                            </div>
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