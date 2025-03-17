<?php
/**
* Ajax Functions Function.
*
* @package True News
*/

add_action('wp_ajax_true_news_recommended_posts', 'true_news_recommended_posts_callback');
add_action('wp_ajax_nopriv_true_news_recommended_posts', 'true_news_recommended_posts_callback');

// Recommendec Post Ajax Call Function.
function true_news_recommended_posts_callback() {

    if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'true_news_ajax_nonce' ) ) {

        $true_news_default = true_news_get_default_theme_options();
        $ed_read_later_posts = get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] );
        $twp_paged = absint( wp_unslash( $_POST['page'] ) );
        $true_news_default = true_news_get_default_theme_options();
        $true_news_home_sections = get_theme_mod( 'true_news_home_sections', json_encode( $true_news_default['true_news_home_sections'] ) );
        $true_news_home_sections = json_decode( $true_news_home_sections );

        foreach( $true_news_home_sections as $true_news_home_section ){
            
            $home_section_type = esc_html( isset( $true_news_home_section->home_section_type ) ? $true_news_home_section->home_section_type : '' ) ;
            switch( $home_section_type ){

                case 'block-5':

                    
                    $hide_post_author_avatar = esc_html( isset( $true_news_home_section->hide_post_author_avatar ) ? $true_news_home_section->hide_post_author_avatar : '' );
                    $hide_post_author = esc_html( isset( $true_news_home_section->hide_post_author ) ? $true_news_home_section->hide_post_author : '' );
                    $hide_post_date = esc_html( isset( $true_news_home_section->hide_post_date ) ? $true_news_home_section->hide_post_date : '' );
                    $recommended_posts_category = esc_html( isset( $true_news_home_section->post_category ) ? $true_news_home_section->post_category : '' );

                    if( $recommended_posts_category ){
                        $idObj = get_category_by_slug( $recommended_posts_category ); 
                        $id = $idObj->term_id;
                        $cat_name = $idObj->name;
                        $cat_link = get_category_link( $id );
                    }

                    $post_cat_query = new WP_Query( array( 'post_type' => 'post','post_status' => 'publish','posts_per_page' => 4, 'category_name' => esc_html( $recommended_posts_category ), 'paged'=> absint( $twp_paged ) ) );

                    if ( $post_cat_query->have_posts() ) :
                        while ( $post_cat_query->have_posts() ) :
                            $post_cat_query->the_post();
                            $format = get_post_format( get_the_ID() );

                            ob_start(); ?>

                            <div class="twp-masonary-item">
                                <div class="content-grid">
                                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'news-article twp-current-recommended' ); ?>>
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

                        <?php
                        $output['content'][] = ob_get_clean();
                        endwhile;
                        wp_send_json_success($output);
                        wp_reset_postdata();

                    endif;

                break;

            }

        }
    }
    wp_die();
}

add_action('wp_ajax_true_news_recommended_posts_tab', 'true_news_recommended_posts_tab_callback');
add_action('wp_ajax_nopriv_true_news_recommended_posts_tab', 'true_news_recommended_posts_tab_callback');

// Recommendec Post Ajax Call Function.
function true_news_recommended_posts_tab_callback() {

    if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'true_news_ajax_nonce' ) ) {

        $first = true;
        if( isset( $_POST['first'] ) ){
            $first = false;
        }
        $twp_paged = absint( wp_unslash( $_POST['page'] ) );
        $catid = absint( wp_unslash( $_POST['catid'] ) );
        $hide_post_author_avatar = esc_html( wp_unslash( $_POST['Hspaa'] ) );
        $hide_post_date = esc_html( wp_unslash( $_POST['Hspd'] ) );
        $hide_post_author = esc_html( wp_unslash( $_POST['Hspa'] ) );
        
        $post_cat_query = new WP_Query( array( 'post_type' => 'post','posts_per_page' => 4,'post_status' => 'publish', 'cat' => absint( $catid ), 'paged'=> absint( $twp_paged ) ) );

        if ( $post_cat_query->have_posts() ) :
            while ( $post_cat_query->have_posts() ) :
                $post_cat_query->the_post();
                $format = get_post_format( get_the_ID() );

                if( $first ){ ob_start(); }

                $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'medium_large' ); ?>

                <div class="twp-masonary-item <?php echo absint( $twp_paged ); ?>">

                    <div class="content-grid">
                        <article id="post-<?php the_ID(); ?>" <?php post_class( 'news-article news-article-tab' ); ?>>
                            <div class="news-article-grid">

                                <?php true_news_get_post_formate(); ?>

                                <div class="entry-details">

                                    <h3 class="entry-title entry-title-small">
                                        <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                                    </h3>
                                    
                                    <?php true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); ?>

                                </div>

                            </div>
                        </article>
                    </div>
                </div>

            <?php
            if( $first ){
                $output['content'][] = ob_get_clean();
            }
            endwhile;
            if( $first ){
                wp_send_json_success($output);
            }
            wp_reset_postdata();
        endif;

    }
    wp_die();
}

add_action('wp_ajax_true_news_single_infinity', 'true_news_single_infinity_callback');
add_action('wp_ajax_nopriv_true_news_single_infinity', 'true_news_single_infinity_callback');

// Recommendec Post Ajax Call Function.
function true_news_single_infinity_callback() {

    if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'true_news_ajax_nonce' ) ) {

        $postid = absint( wp_unslash( $_POST['postid'] ) );
        $true_news_default = true_news_get_default_theme_options();
        $hide_post_author_avatar = get_theme_mod( 'twp_hide_post_author_avatar',$true_news_default['twp_hide_post_author_avatar'] );
        $hide_post_author = get_theme_mod( 'twp_hide_post_author',$true_news_default['twp_hide_post_author'] );
        $hide_post_date = get_theme_mod( 'twp_hide_post_date',$true_news_default['twp_hide_post_date'] );
        $hide_post_categories = get_theme_mod( 'hide_post_categories',$true_news_default['hide_post_categories'] );
        if( $hide_post_author_avatar ){ $hide_post_author_avatar = 'yes'; }else{ $hide_post_author_avatar = 'no'; }
        if( $hide_post_author ){ $hide_post_author = 'yes'; }else{ $hide_post_author = 'no'; }
        if( $hide_post_date ){ $hide_post_date = 'yes'; }else{ $hide_post_date = 'no'; }
        $post_single_next_posts = new WP_Query( array( 'post_type' => 'post','post_status' => 'publish','posts_per_page' => 1, 'post__in' => array( absint( $postid ) ) ) );


        if ( $post_single_next_posts->have_posts() ) :
            while ( $post_single_next_posts->have_posts() ) :
                $post_single_next_posts->the_post();
                ob_start(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class('after-load-ajax'); ?>>

                    <div class="meta-categories-3">
                       <?php if( !$hide_post_categories ){ true_news_post_tag_cats(); } ?>
                    </div>

                        <header class="entry-header entry-header-1">
                        <?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
                        </header>
                    <?php

                    if( class_exists( 'Booster_Extension_Class') && 'post' === get_post_type() ){

                        echo '<div class="twp-single-booster">';
                        $args = array('layout'=>'layout-1','status' => 'enable');
                        do_action('booster_extension_social_icons',$args);
                        do_action('booster_extension_read_time');
                        do_action('booster_extension_post_view');
                        echo '</div>';

                    }

                    if ( get_the_post_thumbnail() ) :
                        $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' ); ?>
                        <div class="post-thumbnail">
                            <a href="<?php the_permalink(); ?>">

                                <img src="<?php echo esc_url( $featured_image[0] ); ?>"  title="<?php the_title_attribute(); ?>" alt="<?php the_title_attribute(); ?>">

                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="twp-content-wraper">

                        <?php
                        echo '<div class="excerpt-content">';
                            if( has_excerpt() ){
                                the_excerpt();
                            }else{
                                echo esc_html( wp_trim_words( get_the_content(),30,'...' ) );
                            }
                        echo '</div>';
                        ?>

                        <div class="entry-content">
                            <?php
                            the_content( sprintf(
                                /* translators: %s: Name of current post. */
                                wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'true-news' ), array( 'span' => array( 'class' => array() ) ) ),
                                the_title( '<span class="screen-reader-text">"', '"</span>', false )
                            ) );

                            if( class_exists( 'Booster_Extension_Class') && 'post' === get_post_type() ){
                                echo '<div class="twp-single-booster">';
                                $args = array('allenable' => 'allenable');
                                do_action('booster_extension_like_dislike',$args);
                                do_action('booster_extension_reaction');
                                echo '</div>';

                            }

                            ?>
                        </div>

                    </div>

                </article>

                <?php
                $next_post_id = '';
                $next_post = get_next_post();
                if( isset( $next_post->ID ) ){ 
                    $next_post_id = $next_post->ID;
                }
                $output['postid'][] = $next_post_id;
                $output['content'][] = ob_get_clean();

            endwhile;

            wp_send_json_success($output);
            wp_reset_postdata();
        endif;
    }
    wp_die();
}

add_action('wp_ajax_true_news_latest_posts', 'true_news_latest_posts_callback');
add_action('wp_ajax_nopriv_true_news_latest_posts', 'true_news_latest_posts_callback');

// Recommendec Post Ajax Call Function.
function true_news_latest_posts_callback() {

    if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'true_news_ajax_nonce' ) ) {

        $true_news_default = true_news_get_default_theme_options();
        $twp_paged = absint( wp_unslash( $_POST['page'] ) );
        $date = esc_attr( wp_unslash( $_POST['date'] ) );
        $author = esc_attr( wp_unslash( $_POST['author'] ) );
        $ed_read_later_posts = absint( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) );
        $author_avtar = esc_html( wp_unslash( $_POST['author_avtar'] ) );
        $recommended_post_category = esc_html( wp_unslash( $_POST['category'] ) );
        $recommended_post_query = new WP_Query( array( 'post_type' => 'post','posts_per_page' => 4,'post_status' => 'publish', 'category_name' => esc_html( $recommended_post_category ), 'paged'=> absint( $twp_paged ),'post__not_in' => get_option("sticky_posts") ) );

        if ( $recommended_post_query->have_posts() ) :

            while ( $recommended_post_query->have_posts() ) :
                $recommended_post_query->the_post(); ?>

                <div class="column column-5">
                    <article class="related-items twp-current-recommended">
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
                            <?php endif; ?>

                            <div class="column column-7">
                                <div class="post-content">
                                    
                                    <h3 class="entry-title entry-title-small">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                    </h3>

                                    <div class="entry-meta entry-meta-1">
                                        <span class="posted-on">
                                            <?php true_news_entry_footer( $author_avtar, $author, $date,$comment = false ); ?>
                                        </span>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </article>
                </div>
            
            <?php
            endwhile;
            wp_reset_postdata();
            
        endif;
    }
    wp_die();
}

add_action('wp_ajax_true_news_get_posts_ajax', 'true_news_get_posts_ajax_callback');
add_action('wp_ajax_nopriv_true_news_get_posts_ajax', 'true_news_get_posts_ajax_callback');
if ( !function_exists('true_news_get_posts_ajax_callback') ):

    // Recommendec Post Ajax Call Function.
    function true_news_get_posts_ajax_callback(){

        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'true_news_ajax_nonce') ) {

            $trending_items = array();
            $tabid = esc_html( wp_unslash( $_POST['tabid'] ) );
            $trending = esc_html( wp_unslash( $_POST['trending'] ) );

            if( $tabid == 'twp-popular-tab' ){

                $most_comment_posts_args  = array(
                    'post_type' => 'post',
                    'orderby' => 'comment_count',
                    'posts_per_page' => 10
                );

                $most_comment_posts = new WP_Query( $most_comment_posts_args );
                if( $most_comment_posts->have_posts() ){
                    while( $most_comment_posts->have_posts()){
                        $most_comment_posts->the_post();

                        $trending_items[ absint( get_the_ID() ) ] = absint( get_the_ID() );

                    }
                    wp_reset_postdata();
                }

            }else{

                if( class_exists('Booster_Extension_Class') && $tabid == 'twp-trending-tab' ){

                    if( $trending == 'day' ){
                        $days = 1;
                    }elseif ($trending == 'month'){
                        $days = 32;
                    }else{
                        $days = 7;
                    }
                    $trending_items = booster_extension_posts_visits($days);
                    arsort( $trending_items );

                }

            }

            $true_news_default = true_news_get_default_theme_options();

            if( $trending_items ){

                $ed_read_later_posts = get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] );
                $i = 1;
                foreach ($trending_items as $post_id => $visit) {

                    $trending_query = new WP_Query( array( 'post_type' => 'post', 'post__in' => array( $post_id) ) );

                    if ( $trending_query->have_posts() ) : ?>

                            <?php
                            /* Start the Loop */
                            while ($trending_query->have_posts()) :

                                $trending_query->the_post();
                                $class = 'twp-post-hentry';
                                if( $i == 1 ){ 
                                    $image_size = 'large';
                                    $class .= ' twp-post-full twp-current-recommended'; 
                                }else{ 
                                    $image_size = 'true-news-medium';
                                    $class .= ' twp-post-grid twp-current-recommended';
                                }
                                if ( !get_the_post_thumbnail() ){ $class .= ' twp-has-no-thumb'; } ?>

                                <article id="post-<?php the_ID(); ?>" <?php post_class( $class ); ?>>
                                        
                                        
                                    <?php
                                    if ( get_the_post_thumbnail() ) : ?>
                                        <div class="post-thumbnail">

                                            <?php if( get_the_post_thumbnail() ){ ?>

                                                <?php true_news_post_thumbnail($image_size); ?>

                                            <?php } ?>

                                        </div>
                                    <?php endif; ?>
                                    

                                    <div class="twp-content-wraper">

                                        <div class="meta-categories-3">
                                            <?php if( $hide_post_category != 'yes' ) { true_news_post_tag_cats(); } ?>
                                        </div>

                                        <header class="entry-header">

                                            <h2 class="entry-title">
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                            </h2>
                                            
                                        </header>

                                        <div class="entry-content">
                                            
                                            <?php
                                            if( has_excerpt() ){
                                                    
                                                the_excerpt();

                                            }else{

                                                echo esc_html( wp_trim_words( get_the_content(),30,'...' ) );

                                            }

                                            wp_link_pages( array(
                                                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'true-news' ),
                                                'after'  => '</div>',
                                            ) ); ?>

                                        </div>

                                        <?php true_news_entry_footer( $hide_post_author_avatar, $hide_post_author, $hide_post_date ); ?>
                                        
                                    </div>

                                </article><!-- #post-## -->

                                <?php
                                
                            endwhile; ?>

                        <?php
                        wp_reset_postdata();
                    endif;

                    $i++;
                    if( $i == 4 ){ $i = 1; }

                }

            }else{ ?>

                <p><?php esc_html_e( 'Nothing Found.', 'true-news' ); ?></p>
            
            <?php
            }

        }
        die();
        
    }
endif;