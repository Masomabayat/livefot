<?php
/**
* Custom Functions
*
* @package True News
*/


if ( ! function_exists( 'true_news_entry_footer' ) ) :

    /**
     * Prints HTML with meta information for the categories, tags and comments.
     */
    function true_news_entry_footer( $hide_post_author_avatar, $hide_post_author, $hide_post_date,$comment = false ) {

        echo '<footer class="entry-footer">';
            if ( 'post' === get_post_type() ) :
                true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); 
            endif;

            if( $comment ){
                if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
                    echo '<span class="comments-link">';
                    comments_popup_link( esc_html__( 'Leave a comment', 'true-news' ), esc_html__( '1 Comment', 'true-news' ), esc_html__( '% Comments', 'true-news' ) );
                    echo '</span>';
                }
            }

        echo '</footer>';

    }

endif;

if ( ! function_exists( 'true_news_post_tag_cats' ) ) :

    /**
     * Prints HTML with meta information for the categories, tags and comments.
     */
    function true_news_post_tag_cats( $cat = true, $tag = false ) {
        echo '<div class="entry-meta entry-meta-categories">';
            
            // Hide category and tag text for pages.
            if ( 'post' === get_post_type() ) {
                if( $cat ){
                    /* translators: used between list items, there is a space after the comma */
                    $categories_list = get_the_category_list();
                    if ( $categories_list ) {
                        echo '<span class="cat-links">' . wp_kses_post( $categories_list ) . '</span>'; // WPCS: XSS OK.
                    }
                }
                if( $tag ){
                    /* translators: used between list items, there is a space after the comma */
                    $tags_list = get_the_tag_list( '', esc_html__( ', ', 'true-news' ) );
                    if ( $tags_list ) {
                        echo '<span class="tags-links">' . wp_kses_post( $tags_list ) . '</span>'; // WPCS: XSS OK.
                    }
                }
            }
        echo '</div>';

    }
    
endif;

if( !function_exists('true_news_header_content') ):

    function true_news_header_content(){
        
        $true_news_default = true_news_get_default_theme_options();
        $ed_read_later_posts = esc_html( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) );
        $ed_header_randomize = esc_html( get_theme_mod( 'ed_header_randomize',$true_news_default['ed_header_randomize'] ) );
        $ed_header_trending = esc_html( get_theme_mod( 'ed_header_trending',$true_news_default['ed_header_trending'] ) );
        $ed_header_search = esc_html( get_theme_mod( 'ed_header_search',$true_news_default['ed_header_search'] ) );
        $trending_posts_page = esc_html( get_theme_mod('trending_posts_page') );
        $pinned_posts_page = esc_html( get_theme_mod('pinned_posts_page') );
        if( $ed_header_randomize ){

            $randargs = array( 
                'orderby' => 'rand',
                'posts_per_page' => 1, 
                'post_type' => 'post',
                'post__not_in' => get_option("sticky_posts")
            );
            $rand_post_query = new WP_Query( $randargs );
            if( $rand_post_query->have_posts() ):
                $i = 1;
                while ( $rand_post_query->have_posts() ) : $rand_post_query->the_post();
                    if( $i == 1 ){ ?>

                        <div class="navbar-item">
                            <a title="<?php echo esc_attr('Random Posts','true-news'); ?>" href="<?php the_permalink(); ?>" class="twp-btn twp-navbar-btn twp-btn-transparent">
                                <i class="ion ion-ios-shuffle meta-icon meta-icon-large"></i>
                            </a>
                        </div>

                    <?php
                    }
                    $i++;
                endwhile;
                wp_reset_postdata();
            endif; ?>
            
        <?php } ?>
        
        <?php if( $ed_header_trending && $trending_posts_page ){
            $page_link = get_page_link( $trending_posts_page ) ?>
            <div class="navbar-item">
                <a href="<?php echo esc_url( $page_link ); ?>" class="twp-btn twp-navbar-btn twp-btn-transparent">
                    <i class="ion ion-ios-flame meta-icon meta-icon-large"></i>
                </a>
            </div>
        <?php } ?>

        <?php if( $ed_read_later_posts && $pinned_posts_page ){
            $page_link = get_page_link( $pinned_posts_page ) ?>
            <div class="navbar-item">
                <a href="<?php echo esc_url( $page_link ); ?>" class="twp-btn twp-navbar-btn twp-btn-transparent">
                    <i class="ion ion-md-bookmarks meta-icon meta-icon-large"></i>
                </a>
            </div>
        <?php } ?>

        <?php if( $ed_header_search ){ ?>
            <div class="navbar-item">
                <button type="button" id="icon-search" class="twp-btn twp-navbar-btn twp-btn-transparent">
                    <i class="ion ion-ios-search meta-icon meta-icon-large"></i>
                </button>
            </div>
        <?php }
    }

endif;

if( !function_exists('true_news_post_thumbnail') ):

    function true_news_post_thumbnail( $size = 'large', $class = '', $false = true, $atag = false ){

        $true_news_default = true_news_get_default_theme_options();
        $ed_lazyload = esc_html( get_theme_mod( 'ed_lazyload',$true_news_default['ed_lazyload'] ) );
        $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(), $size );

        if( has_post_thumbnail() ):

            if( $ed_lazyload && $false ){ ?>

                <?php if( !is_singular() || is_page_template('template-parts/true-news-home-page.php') || $atag ){ ?>
                    <a href="<?php the_permalink(); ?>" class="<?php echo esc_attr( $class ); ?> entry-image-holder">
                    <?php  }
                    
                    the_post_thumbnail( $size, array(
                        'alt' => the_title_attribute( array(
                            'echo' => false,
                        ) ),
                        'title' => the_title_attribute( array(
                            'echo' => false,
                        ) ),
                    ) );
                    
                    if( !is_singular() || is_page_template('template-parts/true-news-home-page.php') || $atag ){ ?>
                    </a>
                    <?php  } ?>

            <?php }else{ ?>

                <?php if( !is_singular() || is_page_template('template-parts/true-news-home-page.php') ){ ?><a href="<?php the_permalink(); ?>" class="entry-image-holder <?php echo esc_attr( $class ); ?>"><?php  } ?>

                    <img src="<?php echo esc_url( $featured_image[0] ); ?>"  title="<?php the_title_attribute(); ?>" alt="<?php the_title_attribute(); ?>">

                <?php if( !is_singular() || is_page_template('template-parts/true-news-home-page.php') ){ ?></a><?php  } ?>

            <?php }

        endif;

    }

endif;

add_filter( 'get_the_archive_title', function ($title) {    
    if ( is_category() ) {    
            $title = single_cat_title( '', false );    
        } elseif ( is_tag() ) {    
            $title = single_tag_title( '', false );    
        } elseif ( is_author() ) {    
            $title = '<span class="vcard">' . get_the_author() . '</span>' ;    
        } elseif ( is_tax() ) { //for custom post types
            $title = single_term_title( '', false );
        }    
    return $title;    
});

if( !function_exists('true_news_preloader') ):

    function true_news_preloader(){

        $true_news_default = true_news_get_default_theme_options();
        $ed_lazyload = absint( get_theme_mod( 'ed_lazyload',$true_news_default['ed_lazyload'] ) );
        $ed_preloader = absint( get_theme_mod( 'ed_preloader', $true_news_default['ed_preloader'] ) );
        if ( $ed_preloader && !is_customize_preview() ) {

            $twp_perloader_layout = esc_html( get_theme_mod( 'twp_perloader_layout',$true_news_default['twp_perloader_layout'] ) );

            if( $twp_perloader_layout == 'advanced' && !isset( $_COOKIE['twppreloader'] ) ){

                $twp_perloader_title = esc_html( get_theme_mod( 'twp_perloader_title',$true_news_default['twp_perloader_title'] ) );
                $ed_preloader_home_only = absint( get_theme_mod( 'ed_preloader_home_only',$true_news_default['ed_preloader_home_only'] ) );

                if( $ed_preloader_home_only ){
                    if( is_home() || is_front_page() ){
                        $status = true;
                    }else{
                        $status = false;
                    }
                }else{
                    $status = true;
                }

                if( $status ){ ?>

                    <div class="preloader">

                        <div class="twp-preloader-skip"><a href="javascript:void(0)"><?php esc_html_e( 'Skip','true-news' ); ?></a></div>
                        <?php if( $twp_perloader_title ){ ?>
                            <div class="wrapper">
                                <div class="twp-row">
                                    <div class="column column-10">
                                        <div class="content-header">
                                            <h2 class="block-title block-title-large"> <?php echo esc_html( $twp_perloader_title ); ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php 
                        $true_news_preloader_args = array(
                            'post_type' => 'post',
                            'ignore_sticky_posts' => true,
                            'posts_per_page' => 3,
                        );
                        
                        $true_news_preloader_query = new WP_Query($true_news_preloader_args);
                        if ($true_news_preloader_query->have_posts()) : ?>
                            <div class="wrapper">
                                <div class="twp-row">

                                    <?php while( $true_news_preloader_query->have_posts() ):
                                        $true_news_preloader_query->the_post(); ?>

                                        <div class="column column-three">
                                            <figure class="twp-news-flash">
                                                
                                                <?php

                                                if( has_post_thumbnail() ):

                                                    $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
                                                    if( $ed_lazyload ){ ?>

                                                        <span href="<?php the_permalink(); ?>" >

                                                            <img src="<?php echo esc_url( $featured_image[0] ); ?>"  title="<?php the_title_attribute(); ?>" alt="<?php the_title_attribute(); ?>">

                                                        </span>

                                                    <?php }else{ ?>

                                                            <img src="<?php echo esc_url( $featured_image[0] ); ?>"  title="<?php the_title_attribute(); ?>" alt="<?php the_title_attribute(); ?>">

                                                    <?php }

                                                endif; ?>

                                                <div class="entry-meta-date">
                                                    <span class="day"><?php echo esc_html( get_the_date('d') ); ?></span><span class="month"><?php echo esc_html( get_the_date('M') ); ?></span>
                                                </div>

                                                <figcaption>

                                                    <h3><?php the_title(); ?></h3>

                                                    <?php
                                                    echo '<div class="preloader-excerpt-content">';
                                                    if( has_excerpt() ){
                                                        the_excerpt();
                                                    }else{
                                                        echo esc_html( wp_trim_words( get_the_content(),15,'...' ) );
                                                    }
                                                    echo '</div>'; ?>

                                                </figcaption>

                                                <div class="hover"><i class="ion ion-md-open"></i></div>

                                                <a href="<?php the_permalink(); ?>"></a>

                                            </figure>
                                        </div>

                                    <?php endwhile; ?>

                                </div>
                            </div>
                            <?php
                            wp_reset_postdata();
                        endif; ?>

                    </div>

                <?php
                }

            }else{ ?>

                <div class="preloader">
                    <div class="loader-wrapper">

                        <div class="blobs">
                            <div class="blob"></div>
                            <div class="blob"></div>
                            <div class="blob"></div>
                            <div class="blob"></div>
                            <div class="blob"></div>
                        </div>

                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1">
                            <defs>
                                <filter id="gaussian-loader">
                                    <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur"/>
                                    <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo"/>
                                    <feBlend in="SourceGraphic" in2="goo"/>
                                </filter>
                            </defs>
                        </svg>

                    </div>
                </div>

            <?php
            }
            
        }
    }

endif;

if( !function_exists('true_news_header_image') ):

    // Top Header Banner    
    function true_news_header_image(){

        if ( get_header_image() ) {

            $true_news_default = true_news_get_default_theme_options();
            $header_banner_text_align = esc_html( get_theme_mod('header_banner_text_align','header_banner_text_align') );
            echo '<div class="twp-header-image twp-align-'.esc_attr($header_banner_text_align).'">';

            $header_medai_text_link = esc_html( get_theme_mod('header_medai_text_link') );
            $header_medai_text = esc_html( get_theme_mod('header_medai_text') );
            if ($header_medai_text_link) {
                echo '<a href="' . esc_url($header_medai_text_link) . '">';
            }
            echo '<span class="twp-banner-image"><img src="' . esc_url(get_header_image()) . '" title="' . esc_html__('Header Image', 'true-news') . '" alt="' . esc_html__('Header Image', 'true-news') . '" /></span>';
            if( $header_medai_text ){ echo '<span class="twp-header-text">'.esc_html($header_medai_text).'</span>'; }
            if ($header_medai_text_link) {
                echo '</a>';
            }
            echo '</div>';
        }

    }

endif;

if( !function_exists('true_news_sidebar') ):

    // Home Sections
    function true_news_sidebar( $sidebar = '' ){

        $true_news_default = true_news_get_default_theme_options();
        if( empty( $sidebar ) ){

            $sidebar = 'right-sidebar';
            
            if( is_singular() ){

                $sidebar = esc_html( get_post_meta( get_the_ID(), 'true_news_post_sidebar_option', true ) );

                if( $sidebar == '' || $sidebar == 'global-sidebar' ){
                    $sidebar = esc_html( get_theme_mod( 'true_news_single_sidebar_layout',$true_news_default['true_news_single_sidebar_layout'] ) );
                }

            }

        }

        if( ( $sidebar == 'global-sidebar' && is_front_page() ) || ( is_home() && is_front_page() ) || ( !is_singular() && !is_front_page() ) ){
            $sidebar = esc_html( get_theme_mod( 'true_news_global_sidebar_layout',$true_news_default['true_news_global_sidebar_layout'] ) );
        }
        
        $sidebar_1_class = '';
        $sidebar_2_class = '';
        if( $sidebar == 'content-left-right' ){
            $sidebar_1_class = 'column-order-3';
            $sidebar_2_class = 'column-order-2 widget-area-highlight';
        }elseif( $sidebar == 'left-right-content' ){
            $sidebar_1_class = 'column-order-2 widget-area-highlight';
            $sidebar_2_class = 'column-order-1';
        }elseif( $sidebar == 'left-sidebar' ){
            $sidebar_1_class = '';
            $sidebar_2_class = 'column-order-1';
        }elseif( $sidebar == 'both-sidebar' ){
            $sidebar_1_class = 'column-order-3';
            $sidebar_2_class = 'column-order-1';
        }

        if( $sidebar != 'left-sidebar' && $sidebar != 'no-sidebar' ){
            echo '<aside id="twp-aside-primary" class="widget-area widget-area-1 '.esc_attr( $sidebar_1_class ).'" role="complementary">';
            get_sidebar();
            echo '</aside>';
        }

        if( $sidebar != 'right-sidebar' && $sidebar != 'no-sidebar' ){
            echo '<aside id="twp-aside-secondary" class="widget-area widget-area-2 '.esc_attr( $sidebar_2_class ).'" role="complementary">';
            get_sidebar( '2' );
            echo '</aside>';
        }

    }

endif;

if( !function_exists('true_news_content_class') ):

    // Home Sections
    function true_news_content_class( $sidebar = '' ){

        $true_news_default = true_news_get_default_theme_options();
        if( empty( $sidebar ) ){

            $sidebar = 'right-sidebar';
            if( is_singular('post') || is_singular('page') ){

                $sidebar = esc_html( get_post_meta( get_the_ID(), 'true_news_post_sidebar_option', true ) );
                if( $sidebar == '' || $sidebar == 'global-sidebar' ){
                    $sidebar = esc_html( get_theme_mod( 'true_news_single_sidebar_layout',$true_news_default['true_news_single_sidebar_layout'] ) );
                }
            }

        }

        if( ( $sidebar == 'global-sidebar' && is_front_page() ) || ( is_home() && is_front_page() ) || ( !is_singular() && !is_front_page() ) ){
            $sidebar = esc_html( get_theme_mod( 'true_news_global_sidebar_layout',$true_news_default['true_news_global_sidebar_layout'] ) );
        }

        $content_class = '';
        if( $sidebar == 'content-left-right' ){
            $content_class = 'column-order-1';
        }elseif( $sidebar == 'left-right-content' ){
            $content_class = 'column-order-3';
        }elseif( $sidebar == 'left-sidebar' ){
            $content_class = 'column-order-2';
        }elseif( $sidebar == 'both-sidebar' ){
            $content_class = 'column-order-2 widget-area-highlight';
        }

        return $content_class;
    }

endif;

if( !function_exists('true_news_home_sections') ):

    // Home Sections
    function true_news_home_sections( ){
    	
    	$home_sections = array(
	        'block-1' => esc_html__('Section Block 1','true-news'),
            'block-2' => esc_html__('Section Block 2','true-news'),
            'block-3' => esc_html__('Section Block 3','true-news'),
            'block-4' => esc_html__('Section Block 4','true-news'),
            'block-5' => esc_html__('Section Block 5','true-news'),
            'block-6' => esc_html__('Section Slider Block','true-news'),
            'block-7' => esc_html__('Section Carousel Block','true-news'),
            'block-8' => esc_html__('Section Video Block','true-news'),
            'recommended' => esc_html__('Section Recommended Block','true-news'),
            'latest-posts' => esc_html__('Section Latests Posts Block','true-news'),
            'advertise-area' => esc_html__('Advertisement Block','true-news'),

	    );

	    return $home_sections;

    }

endif;

if( !function_exists('true_news_sidebar_layout') ):

    // Home Sections
    function true_news_sidebar_layout( ){
        
        $sidebar_layout = array(
            'right-sidebar' => esc_html__('Content & Right Sidebar','true-news'),
            'left-sidebar' => esc_html__('Left Sidebar & Content','true-news'),
            'both-sidebar' => esc_html__('Left Sidebar, Content & Right Sidebar','true-news'),
            'content-left-right' => esc_html__('Content, Left Sidebar & Right Sidebar','true-news'),
            'left-right-content' => esc_html__('Left Sidebar, Right Sidebar & Content','true-news'),
            'no-sidebar' => esc_html__('No Sidebar','true-news'),
        );

        return $sidebar_layout;

    }

endif;

if( !function_exists( 'true_news_sanitize_sidebar_option' ) ) :

    // Sidebar Option Sanitize.
    function true_news_sanitize_sidebar_option( $input ){

        $metabox_options = array( 'global-sidebar','left-sidebar','right-sidebar','both-sidebar','no-sidebar','content-left-right','left-right-content' );
        if( in_array( $input,$metabox_options ) ){
            return $input;
        }else{
            return '';
        }

    }

endif;

if( !function_exists('true_news_pagination_type') ):

    // Pagination Type
    function true_news_pagination_type( ){
        
        $pagination_type = array(
            'default' => esc_html__('Default (Older / Newer Post)','true-news'),
            'numeric' => esc_html__('Numeric','true-news'),
            'button_click_load' => esc_html__('Button Click Ajax Load','true-news'),
            'infinite_scroll_load' => esc_html__('Infinite Scroll Ajax Load','true-news'),
        );

        return $pagination_type;

    }

endif;

/**
 * function for google fonts
 */
if (!function_exists('true_news_fonts_url')) :

    /**
     * Return fonts URL.
     *
     * @since 1.0.0
     * @return string Fonts URL.
     */
    function true_news_fonts_url(){
        
        $fonts_url = '';
        $fonts = array();
        $true_news_default = true_news_get_default_theme_options();
        $true_news_primary_font   = $true_news_default['primary_font'];
        $true_news_secondary_font = $true_news_default['secondary_font'];

        $true_news_fonts   = array();
        $true_news_fonts[] = $true_news_primary_font;
        $true_news_fonts[] = $true_news_secondary_font.'&display=swap';

        for ($i = 0; $i < count($true_news_fonts); $i++) {

            if ( 'off' !== sprintf(_x( 'on', '%s font: on or off', 'true-news' ), $true_news_fonts[$i] ) ) {
                $fonts[] = $true_news_fonts[$i];
            }

        }

        if ($fonts) {
            $fonts_url = add_query_arg(array(
                'family' => urldecode(implode('|', $fonts)),
            ), 'https://fonts.googleapis.com/css');
        }

        return $fonts_url;
    }
endif;

if( !function_exists( 'true_news_post_category_list' ) ) :

    // Post Category List.
    function true_news_post_category_list( $select_cat = true ){

        $post_cat_lists = get_categories(
            array(
                'hide_empty' => '0',
                'exclude' => '1',
            )
        );

        $post_cat_cat_array = array();
        if( $select_cat ){
            $post_cat_cat_array[''] = esc_html__( '-- Select Category --','true-news' );
        }

        foreach ( $post_cat_lists as $post_cat_list ) {

            $post_cat_cat_array[$post_cat_list->slug] = $post_cat_list->name;

        }

        return $post_cat_cat_array;
    }

endif;

if( !function_exists( 'true_news_page_lists' ) ) :

    // Page List.
    function true_news_page_lists(){

        $page_lists = array();
        $page_lists[''] = esc_html__( '-- Select Page --','true-news' );
        $pages = get_pages(
            array (
                'parent'  => 0, // replaces 'depth' => 1,
            )
        );
        foreach( $pages as $page ){

            $page_lists[$page->ID] = $page->post_title;

        }
        return $page_lists;
    }

endif;

if ( ! function_exists( 'true_news_posted_on' ) ) :

    /**
     * Prints HTML with meta information for the current post-date/time.
     */
    function true_news_posted_on( $text = true, $link = true, $formate = false ) {
        
        $true_news_default = true_news_get_default_theme_options();
        $post_date_formate = get_theme_mod( 'post_date_formate',$true_news_default['post_date_formate'] );
        $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
        if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
            $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
        }

        $time_string = sprintf( $time_string,
            esc_attr( get_the_date( DATE_W3C ) ),
            esc_html( get_the_date() ),
            esc_attr( get_the_modified_date( DATE_W3C ) ),
            esc_html( get_the_modified_date() )
        );

        if( $post_date_formate == 'time-ago' || $formate ){

            if( $link ){

                if( $text ){

                    $posted_on = sprintf(
                        /* translators: %s: post date. */
                        esc_html_x( 'Posted on %s', 'post date', 'true-news' ),
                        '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . human_time_diff( get_the_date('U'), current_time('timestamp') ) .__(' Ago','true-news'). '</a>'
                    );

                }else{

                    $posted_on = '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . human_time_diff( get_the_date('U'), current_time('timestamp') ) .__(' Ago','true-news'). '</a>';

                }
                

            }else{


                if( $text ){

                     $posted_on = sprintf(
                        /* translators: %s: post date. */
                        esc_html_x( 'Posted on %s', 'post date', 'true-news' ),  human_time_diff( get_the_date('U'), current_time('timestamp') ) .__(' Ago','true-news')
                    );

                }else{

                    $posted_on = human_time_diff( get_the_date('U'), current_time('timestamp') ) .esc_html__(' Ago','true-news');

                }
               

            }
            

        }else{


            if( $link ){

                if( $text ){

                    $posted_on = sprintf(
                        /* translators: %s: post date. */
                        esc_html_x( 'Posted on %s', 'post date', 'true-news' ),
                        '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
                    );

                }else{

                    $posted_on = '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>';

                }
                

            }else{

                if( $text ){

                    $posted_on = sprintf(
                        /* translators: %s: post date. */
                        esc_html_x( 'Posted on %s', 'post date', 'true-news' ), $time_string
                    );

                }else{

                    $posted_on = $time_string;

                }
                

            }

        }

        echo '<span class="posted-on">' . $posted_on . '</span>'; // WPCS: XSS OK.

    }

endif;

if ( ! function_exists( 'true_news_posted_by' ) ) :
    /**
     * Prints HTML with meta information for the current author.
     */
    function true_news_posted_by( $avatar = true, $author = true ) {

        if( $avatar ){
            $author_img = get_avatar( get_the_author_meta( 'ID' ) , 100, '', '', array( 'class' => 'avatar-img' ) );
            echo '<span class="author-img"> ' . true_news_avatar_escape( $author_img ) . '</span>';
        }

        if( $author ){

            $byline = sprintf(
                /* translators: %s: post author. */
                esc_html_x( '%s', 'post author', 'true-news' ),
                '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
            );

            echo '<span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.
        }

    }
endif;

if ( ! function_exists( 'true_news_avatar_escape' ) ) :

    /** Avatar Sanitize **/
    function true_news_avatar_escape($input){

        $all_tags = array(
            'img'=>array(
                'class'=>array(),
                'loading'=>array(),
                'alt'=>array(),
                'src'=>array(),
                'height'=>array(),
                'id'=>array(),
                'srcset'=>array(),
                'width'=>array(),
                'title'=>array(),
            )
         );
        return wp_kses($input,$all_tags);
        
    }

endif;

if ( ! function_exists( 'true_news_entry_meta' ) ) :

    /**
     * Get Entry Meta
     */
    function true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date,$text = true,$link = true, $formate = false ) {

        echo '<div class="entry-meta">';

            if ( $hide_post_author_avatar != 'yes' || 
             $hide_post_author != 'yes' || 
             $hide_post_date != 'yes' ) {

            if ( $hide_post_author_avatar != 'yes' || 
                 $hide_post_author != 'yes' ) {

                if( $hide_post_author_avatar == 'yes' ){ 
                    $post_author_avatar = false;
                }else{
                    $post_author_avatar = true;
                }

                if( $hide_post_author == 'yes' ){
                    $post_author = false;
                }else{
                    $post_author = true;
                }

                true_news_posted_by( $post_author_avatar, $post_author );
                
            }

            if( ( $hide_post_author_avatar != 'yes' || 
             $hide_post_author != 'yes' ) && ( $hide_post_date != 'yes' ) ){
                echo "<span class='sep-date-author'><i class='ion ion-ios-remove'></i></span>";
            }

            if( $hide_post_date != 'yes' ){
                true_news_posted_on($text,$link,$formate);
            }

        echo '</div>';

    }

    }
endif;

if( !function_exists('true_news_get_post_formate') ):

    // Home Sections
    function true_news_get_post_formate( ){
        
        $true_news_default = true_news_get_default_theme_options();
        $ed_read_later_posts = get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] );
        $format = get_post_format( get_the_ID() );
        $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
        if( $format == 'gallery' ){

            if ( ( function_exists('has_block') && has_block('gallery', get_the_content() ) ) || get_post_gallery() ) {
        
                if ( function_exists('has_block') && has_block('gallery', get_the_content()) ) {

                    $post_blocks = parse_blocks( get_the_content() );
                    if( $post_blocks ){
                        foreach( $post_blocks as $post_block ){

                            if( isset( $post_block['blockName'] ) && 
                                isset( $post_block['innerHTML'] ) && 
                                $post_block['blockName'] == 'core/gallery' ){

                                echo '<div class="entry-gallery">';
                                echo wp_kses_post( $post_block['innerHTML'] );
                                echo '</div>';
                                break;

                            }
                        }
                    }

                }else{

                    if( get_post_gallery() ){
                        echo '<div class="entry-gallery">';
                        echo wp_kses_post( get_post_gallery() );
                        echo '</div>';
                    }
                }
                
            }else{
                if(  get_the_post_thumbnail() ){ ?>
                    <div class="post-thumbnail">
                        <?php true_news_post_thumbnail('large','',$false = false); ?>
                    </div>
                <?php }
            }

        }elseif( $format == 'video' ){

            $content = apply_filters( 'the_content', get_the_content() );
            $video = false;

            // Only get video from the content if a playlist isn't present.
            if ( false === strpos( $content, 'wp-playlist-script' ) ) {
                $video = get_media_embedded_in_content( $content, array( 'video', 'object', 'embed', 'iframe' ) );
            }
            if ( ! empty( $video ) ) {
                foreach ( $video as $video_html ) {
                    echo '<div class="entry-video">';
                    echo true_news_iframe_escape( $video_html );
                    echo '</div>';
                    break;
                }
            }else{
                if(  get_the_post_thumbnail() ){ ?>
                    <div class="post-thumbnail">
                        <?php true_news_post_thumbnail('large','',$false = false); ?>
                    </div>
                <?php }
            }

        }elseif( $format == 'quote' ){

            echo '<div class="twp-quote-formatet">';
            if ( has_post_thumbnail() ) { ?>

                <div class="data-bg data-bg data-bg-medium" style="background-image: url( <?php echo esc_url( $featured_image[0] ); ?> );"> </div>
            <?php
            }else{
                echo '<div class="twp-bg-quote"></div>';
            }
            echo "<blockquote>";
            if( has_excerpt() ){
                the_excerpt();
            }else{
                echo esc_html( wp_trim_words( get_the_content(),30,'...' ) );
            }
            echo "</blockquote>"; ?>
            <h2 class="entry-title quote-entry-title">
                <a href="<?php echo esc_url( get_the_permalink() ); ?>">
                    <?php
                    echo esc_html( get_the_title() );
                    if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); }
                    ?>
                </a>
            </h2>
            <?php
            echo "</div>";

        }elseif( $format == 'audio' ){

            $content = get_the_content();
            $content = apply_filters( 'the_content', $content );

            $audio = false;
            if ( false === strpos( $content, 'wp-playlist-script' ) ) {
                $audio = get_media_embedded_in_content( $content, array( 'audio' ) );
            }

            if( $audio ){

                foreach ( $audio as $audio_html ) {
                    echo '<div class="twp-audio">';
                    echo wp_kses_post( $audio_html );
                    echo '</div>';
                    break;
                }

            }else{

                if(  get_the_post_thumbnail() ){ ?>
                    <div class="post-thumbnail">
                        <?php true_news_post_thumbnail('large','',$false = false); ?>
                    </div>
                <?php
                }

            }

        }else{

            if(  get_the_post_thumbnail() ){ ?>
                <div class="post-thumbnail">
                    <?php true_news_post_thumbnail('large','',$false = false); ?>
                </div>
            <?php
            }
        }

    }

endif;



if ( ! function_exists( 'true_news_disable_default_booster' ) ) :
    
    // Disable Default Content Of social Share
    function true_news_disable_default_booster() {
        return false;
    }

endif;


if( class_exists( 'Booster_Extension_Class') ){

    add_filter('booster_extension_filter_ss_ed','true_news_disable_default_booster');
    add_filter('booster_extension_filter_views_ed','true_news_disable_default_booster');
    add_filter('booster_extension_filter_readtime_ed','true_news_disable_default_booster');
    add_filter('booster_extension_filter_like_ed','true_news_disable_default_booster');
    add_filter('booster_extension_filter_reaction_ed','true_news_disable_default_booster');

}

if( !function_exists('true_news_check_woocommerce_page') ):
    
    // Check if woocommerce pages.
    function true_news_check_woocommerce_page(){

        if( !class_exists( 'WooCommerce' ) ):
            return false;
        endif;

        if( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ){
            return true;
        }else{
            return false;
        }

    }
    
endif;

if( !function_exists( 'true_news_breadcrumb' ) ) :

    // Trail Breadcrumb.
    function true_news_breadcrumb(){

        if( !is_home() && !is_front_page() && !is_404() ){ ?>

            <div class="block-elements block-elements-breadcrumb">
                <div class="wrapper">

                    <?php 
                    $true_news_default = true_news_get_default_theme_options();
                    $breadcrumb_layout = get_theme_mod('breadcrumb_layout',$true_news_default['breadcrumb_layout']);
                    if( $breadcrumb_layout != 'disable' && !is_front_page() ):
                            breadcrumb_trail();
                    endif; ?>

                    
                        <?php
                        if( is_search() ){ ?>
                            <div class="twp-banner-details">
                                <header class="page-header">
                                    <h1 class="page-title">
                                        <?php
                                        /* translators: %s: search query. */
                                        printf( esc_html__( 'Search Results for: %s', 'true-news' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
                                        ?>
                                    </h1>
                                </header><!-- .page-header -->
                            </div>
                        <?php } ?>

                        <?php
                        if( is_archive() && !is_author() ){ ?>

                            <div class="twp-banner-details">
                                <header class="page-header">
                                    <?php
                                    the_archive_title( '<h1 class="page-title">', '</h1>' );
                                    the_archive_description( '<div class="archive-description">', '</div>' );
                                    ?>
                                </header><!-- .page-header -->
                            </div>
                        <?php }

                        if( is_author() ){ ?>
                            <div class="twp-banner-details">
                                <header class="page-header">
                                    <?php $author_img = get_avatar( get_the_author_meta('ID'),200, '', '', array('class' => 'avatar-img') ); ?>

                                    <div class="author-image">
                                        <?php echo wp_kses_post( $author_img ); ?>
                                    </div>

                                    <div class="author-title-desc">
                                        <?php
                                        the_archive_title( '<h1 class="page-title">', '</h1>' );
                                        the_archive_description( '<div class="archive-description">', '</div>' );
                                        ?>
                                    </div>
                                </header><!-- .page-header -->
                            </div>
                        <?php } ?>

                </div>
            </div>
        <?php
        }
    }

endif;
add_action( 'true_news_header_banner_x','true_news_breadcrumb',20 );

if( !function_exists('true_news_sanitize_meta_pagination')):

    /** Sanitize Enable Disable Checkbox **/
    function true_news_sanitize_meta_pagination( $input ) {

        $valid_keys = array('global-layout','no-navigation','norma-navigation','ajax-next-post-load');
        if ( in_array( $input , $valid_keys ) ) {
            return $input;
        }
        return '';

    }

endif;


if( !function_exists('true_news_off_canvas_content') ):

    function true_news_off_canvas_content(){

        if ( is_active_sidebar('off-canvas-1')  || 
        is_active_sidebar('off-canvas-2') || 
        is_active_sidebar('off-canvas-3')) : ?>

        <div id="overhead-elements" class="overhead-elements">
            <div class="wrapper">
                <div class="twp-row">
                    <div class="column">
                        <button type="button" id="overhead-closer" class="close-popup twp-btn-transparent"></button>
                    </div>
                </div>
            </div>

            <div class="wrapper">
                <div class="twp-row twp-offcanvas-row">
                    <?php if (is_active_sidebar('off-canvas-1')) : ?>
                        <div class="column">
                            <?php dynamic_sidebar('off-canvas-1'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('off-canvas-2')) : ?>
                        <div class="column">
                            <?php dynamic_sidebar('off-canvas-2'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('off-canvas-3')) : ?>
                        <div class="column">
                            <?php dynamic_sidebar('off-canvas-3'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <?php endif; ?>


        <div class="popup-search">
            <div class="table-align">
                <div class="table-align-cell v-align-middle">
                    <button type="button" id="search-closer" class="close-popup twp-btn-transparent"></button>
                    <?php get_search_form(); ?>
                </div>
            </div>
        </div>

    <?php
    }

endif;

add_action( 'true_news_off_canvas_content_action','true_news_off_canvas_content' );

if ( ! function_exists( 'true_news_sub_menu_toggle_button' ) ) :

    function true_news_sub_menu_toggle_button( $args, $item, $depth ) {

        // Add sub menu toggles to the main menu with toggles
        if ( $args->theme_location == 'primary-menu' || $args->theme_location == 'top-menu' ) {
            // Wrap the menu item link contents in a div, used for positioning
            $args->before = '<div class="submenu-wrapper">';
            $args->after  = '';
            // Add a toggle to items with children
            if ( in_array( 'menu-item-has-children', $item->classes ) ) {
                $toggle_target_string = '.menu-item.menu-item-' . $item->ID . ' > .sub-menu';
                // Add the sub menu toggle
                $args->after .= '<button type="button" class="submenu-toggle twp-btn-transparent" data-toggle-target="' . $toggle_target_string . '" data-toggle-type="slidetoggle" data-toggle-duration="250"><span class="screen-reader-text">' . __( 'Show sub menu', 'true-news' ) . '</span><i class="ion ion-ios-arrow-down"></i></button>';
            }
            // Close the wrapper
            $args->after .= '</div><!-- .submenu-wrapper -->';
            // Add sub menu icons to the main menu without toggles (the fallback menu)
        }
        return $args;

    }

    add_filter( 'nav_menu_item_args', 'true_news_sub_menu_toggle_button', 10, 3 );

endif;


/**
 * True News SVG Icon helper functions
 *
 * @package WordPress
 * @subpackage True News
 * @since 1.0.0
 */
if ( ! function_exists( 'true_news_the_theme_svg' ) ) :

    /**
     * Output and Get Theme SVG.
     * Output and get the SVG markup for an icon in the True_News_SVG_Icons class.
     *
     * @param string $svg_name The name of the icon.
     * @param string $group The group the icon belongs to.
     * @param string $color Color code.
     */
    function true_news_the_theme_svg( $svg_name, $color = '' ) {

        echo true_news_get_theme_svg( $svg_name, $color ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in true_news_get_theme_svg();.
        
    }

endif;

if ( ! function_exists( 'true_news_get_theme_svg' ) ) :

    /**
     * Get information about the SVG icon.
     *
     * @param string $svg_name The name of the icon.
     * @param string $group The group the icon belongs to.
     * @param string $color Color code.
     */

    function true_news_get_theme_svg( $svg_name, $color = '' ) {

        // Make sure that only our allowed tags and attributes are included.
        $svg = wp_kses(
            True_News_SVG_Icons::true_news_get_svg( $svg_name, $color ),
            array(
                'svg'     => array(
                    'class'       => true,
                    'xmlns'       => true,
                    'width'       => true,
                    'height'      => true,
                    'viewbox'     => true,
                    'aria-hidden' => true,
                    'role'        => true,
                    'focusable'   => true,
                ),
                'path'    => array(
                    'fill'      => true,
                    'fill-rule' => true,
                    'd'         => true,
                    'transform' => true,
                ),
                'polygon' => array(
                    'fill'      => true,
                    'fill-rule' => true,
                    'points'    => true,
                    'transform' => true,
                    'focusable' => true,
                ),
            )
        );

        if ( ! $svg ) {
            return false;
        }
        return $svg;
    
    }

endif;

if ( ! function_exists( 'true_news_iframe_escape' ) ) :

    /** Escape Iframe **/
    function true_news_iframe_escape( $input ){

        $all_tags = array(
            'iframe'=>array(
                'width'=>array(),
                'height'=>array(),
                'src'=>array(),
                'frameborder'=>array(),
                'allow'=>array(),
                'allowfullscreen'=>array(),
            )
        );

        return wp_kses($input,$all_tags);
        
    }

endif;

if ( ! function_exists( 'true_news_single_nav_escape' ) ) :

    /** Escape Iframe **/
    function true_news_single_nav_escape( $input ){

        $all_tags = array(
            'a'=>array(
                'rel'=>array(),
                'src'=>array(),
                'title'=>array(),
            )
        );

        return wp_kses($input,$all_tags);
        
    }

endif;