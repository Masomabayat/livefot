<?php
/**
 * Theme Functions which enhance the theme by hooking into WordPress
 *
 * @package freenews
 */


// Navigation Top
function freenews_navigation_top(){ ?>
    <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
        <span class="toggle-text"><?php esc_html_e('Menu','freenews'); ?></span>
        <span class="toggle-bar"></span>
    </button>

    <?php
    wp_nav_menu( array(
        'container' =>'',
        'theme_location' => 'menu-1',
        'menu_id'        => 'primary-menu',
        'items_wrap'      => '<ul id="primary-menu" class="menu nav-menu">%3$s</ul>',
    ) );
}

add_action('freenews_frontend_navigation_top','freenews_navigation_top');

// Navigation Secondary
function freenews_secondary_navigation(){
    if ( has_nav_menu( 'menu-3' ) ) {  ?>
       <nav class="secondary-navigation" role="navigation" aria-label="<?php esc_attr_e('Secondary Navigation','freenews'); ?>">
            <div class="wrap">
                <button class="secondary-menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                    <span class="secondary-toggle-text"><?php esc_html_e('Menu','freenews'); ?></span>
                    <span class="secondary-toggle-bar"></span>
                </button>
                <?php
                wp_nav_menu( array(
                    'container' =>'',
                    'theme_location' => 'menu-3',
                    'menu_id'        => 'primary-menu',
                    'items_wrap'      => '<ul id="primary-menu" class="secondary-menu">%3$s</ul>',
                ) ); ?>
            </div><!-- .wrap -->
        </nav><!-- .secondary-navigation -->       
<?php }
}

add_action('freenews_frontend_secondary_navigation','freenews_secondary_navigation');

// Search Form 
function freenews_search_form(){
    $search_text = get_theme_mod('search_text',esc_html__('Search','freenews')); ?>
<div class="search-container">
    <form role="search" method="get" class="search" action="<?php echo esc_url( home_url( '/' ) ); ?>"  role="search"> 
        <label for='s' class='screen-reader-text'><?php esc_html_e( 'Search', 'freenews' ); ?></label> 
            <input class="search-field" placeholder="<?php echo esc_attr($search_text).'&hellip;'; ?>" name="s" type="search"> 
            <input class="search-submit" value="<?php echo esc_attr($search_text); ?>" type="submit">
    </form>
</div><!-- .search-container -->
    
<?php }
add_action('freenews_frontend_search_form','freenews_search_form');

// Social Navigation
function freenews_social_navigation(){ ?>
    <nav class="social-navigation" role="navigation" aria-label="<?php esc_attr_e('Social Navigation','freenews'); ?>">
        <?php
       
            wp_nav_menu( array(
                'container' =>'',
                'theme_location' => 'menu-2',
                'menu_id'        => 'primary-menu',
                'items_wrap'      => '<ul class="social-links-menu">%3$s</ul>',
                'link_before'    => '<span class="screen-reader-text">',
                'link_after'     => '</span>',
            ) );
        ?>
    </nav><!-- .social-navigation -->

<?php }

add_action('freenews_frontend_social_navigation','freenews_social_navigation');

// Site Branding
function freenews_site_branding(){ ?>
    <div class="site-branding">
        <?php the_custom_logo(); ?>
        <div class="site-branding-text">

            <?php if ( is_front_page() && is_home() ) : ?>
                <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                <?php
            else :
                ?>
                <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
                <?php
            endif;
            $freenews_description = get_bloginfo( 'description', 'display' );
            if ( $freenews_description || is_customize_preview() ) :
                ?>
                <p class="site-description"><?php echo $freenews_description; /* WPCS: xss ok. */ ?></p>
            <?php endif; ?>

        </div><!-- .site-branding-text -->
    </div><!-- .site-branding -->

<?php }

add_action('freenews_frontend_site_branding','freenews_site_branding');

// Main Banner
function freenews_main_banner(){
$disable_main_banner = get_theme_mod('disable_main_banner',0);
$select_main_banner_category = get_theme_mod('select_main_banner_category','');
$no_of_main_banner = get_theme_mod('no_of_main_banner','3');
$slider_options = get_theme_mod('slider-options','main-banner');
$excerpt_text = get_theme_mod('excerpt_text',esc_html__('Read More','freenews'));
$excerpt_display = get_theme_mod('excerpt-display','excerpt-content');
$query = new WP_Query(array(
    'posts_per_page' =>  absint($no_of_main_banner),
    'post_type' => array( 'post' ) ,
    'category_name' => esc_attr($select_main_banner_category),
));
if(!is_paged()){
    if($disable_main_banner==0){
        if($select_main_banner_category!='' || $slider_options !='main-banner'){ ?>

            <div class="banner-list">
                <?php 
                if($slider_options == 'metaslider' || $slider_options == 'smartslider' || $slider_options == 'masterslider'){
                    do_action('freenews_frontend_plugins_slider');
                } else {
                    while ($query->have_posts()):$query->the_post();  ?>
                        <div class="slide">
                            <div class="slide-content">
                                 <?php if(has_post_thumbnail()){ ?>
                                <div class="slide-thumb">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"> <?php the_post_thumbnail('freenews-main-banner'); ?></a>
                                    <?php } ?>
                                </div><!-- .slide-thumb -->

                                <div class="slide-text-wrap">
                                    <div class="slide-text-content">
                                        <div class="slide-meta">
                                            <?php 
                                                freenews_cat_lists ();
                                                freenews_posted_on(); 
                                            ?>
                                        </div><!-- .slide-meta -->
                                        <h2 class="slide-title"><a href="<?php the_permalink(); ?>" alt="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                                   
                                        <div class="slide-text">
                                            <?php 
                                                if($excerpt_display == 'full-content'){
                                                    the_content( sprintf(
                                                    wp_kses(
                                                        /* translators: %s: Name of current post. Only visible to screen readers */
                                                        $excerpt_text. '<span class="screen-reader-text"> "%s"</span>',
                                                        array(
                                                            'span' => array(
                                                                'class' => array(),
                                                            ),
                                                        )
                                                    ),
                                                    get_the_title()
                                                ) );
                                                } else {
                                                    the_excerpt();
                                                } ?>
                                        </div><!-- .slider-text -->
                                   
                                    </div><!-- .slide-text-content -->
                                </div><!-- .slide-text-wrap -->
                            </div><!-- .slide-content -->
                        </div><!-- .slide -->
                    <?php endwhile;
                    wp_reset_postdata();
                } ?>
            </div><!-- .banner-list -->
        <?php }
    }
} ?>

<?php }

add_action('freenews_frontend_main_banner','freenews_main_banner');

//  Highlighted Category Left
function freenews_hightlight_category_left(){
    $highlighted_categories_left = get_theme_mod('highlighted_categories_left','');
    $disable_category_highlight_left = get_theme_mod('disable_category_highlight_left',0);

    $query = new WP_Query(array(
        'posts_per_page' =>  2,
        'post_type' => array( 'post' ) ,
        'category_name' => esc_attr($highlighted_categories_left),
    ));
    if($disable_category_highlight_left ==0 ) {
        if ($highlighted_categories_left!='' ) {
            if(!is_paged()){ ?>
                <div class="highlighted-category-left">
                    <?php
                    if( $query->have_posts() ) {
                        while ($query->have_posts()):$query->the_post();
                            if(has_post_thumbnail()) { ?>
                            <div class="highlighted-category-content">
                                   <div class="highlighted-category-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('freenews-highlighted'); ?>
                                        </a>
                                    </div><!-- .highlighted-category-thumbnail -->
                                <div class="highlighted-category-header">
                                    <div class="highlighted-category-meta">
                                        <?php
                                            freenews_cat_lists();

                                            freenews_posted_on(); 
                                        ?>
                                    </div><!-- ."highlighted-category-meta -->
                                        <h2 class="highlighted-category-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                </div><!-- .highlighted-category-header -->
                            </div><!-- .highlighted-category-content -->
                            <?php }
                        endwhile;
                    }
                        wp_reset_postdata(); ?>
               </div><!-- .highlighted-category-left -->
            <?php
            }
        }
    }
}

add_action('freenews_frontend_hightlight_category_left','freenews_hightlight_category_left');

// HIghlighted Category Right
function freenews_hightlight_category_right(){
    $highlighted_categories_right = get_theme_mod('highlighted_categories_right','');
    $disable_category_highlight_right = get_theme_mod('disable_category_highlight_right',0);
    $highlighted_cat_right_title_text = get_theme_mod ('highlighted_cat_right_title_text',esc_html__('World News','freenews'));

    if(!empty($highlighted_categories_right[0])){ 
        $checked_cat_list = array(); 
        foreach( $highlighted_categories_right as $cat_key => $cat_value ){
            $checked_cat_list[] = $cat_value;
        }
        $get_cat_check_slugs = implode( ",", $checked_cat_list );
        $query = new WP_Query(array(
            'post_type' => array( 'post' ) ,
            'category_name' => wp_kses_post($get_cat_check_slugs),
        ));
        if($disable_category_highlight_right ==0 ) {
            if(!is_paged()){ ?>

               <div class="highlighted-category-right">
                   <div class="hl-category-inner">
                        <?php if ($highlighted_cat_right_title_text != ''){ ?>
                           <div class="hl-category-header">
                                <h3 class="hl-category-posts-title"><?php echo esc_html ($highlighted_cat_right_title_text); ?></h3>
                                <div class="hl-category-navigation"></div>
                            </div>
                        <?php } ?>
                        <div class="highlighted-category-posts">
                            <?php

                            if( $query->have_posts() ) {
                                while ($query->have_posts()):$query->the_post();
                                    if(has_post_thumbnail()) { ?>
                                        <div class="highlighted-slide">
                                            <div class="highlighted-category-content">
                                                    <div class="highlighted-category-thumbnail">
                                                        <a href="<?php the_permalink(); ?>">
                                                            <?php the_post_thumbnail('freenews-highlighted'); ?>
                                                        </a>
                                                    </div><!-- .highlighted-category-thumbnail -->
                                                    <div class="highlighted-category-header">
                                                        <div class="highlighted-category-meta">
                                                            <?php
                                                                freenews_cat_lists();

                                                                freenews_posted_on(); 
                                                            ?>
                                                        </div><!-- ."highlighted-category-meta -->

                                                        <h2 class="highlighted-category-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                                    </div><!-- .highlighted-category-header -->
                                            </div><!-- .highlighted-category-content -->
                                        </div><!-- .highlighted-slide -->
                                    <?php }
                                endwhile;
                            }
                                wp_reset_postdata(); ?>
                        </div><!-- .highlighted-category-posts -->
                    </div><!-- .highlighted-category-inner -->
                </div><!-- .highlighted-category-right -->

            <?php }
            }
        }
}

add_action('freenews_frontend_hightlight_category_right','freenews_hightlight_category_right');

function freenews_header_image(){ ?>
    <div class="custom-header">
        <div class="custom-header-media">
            <?php the_custom_header_markup(); ?>
        </div><!-- .custom-header-media -->
    </div><!-- .custom-header -->
<?php
}

add_action('freenews_frontend_header_image','freenews_header_image');


// Flash News
function freenews_flash_news(){
$disable_flash_news = get_theme_mod('disable_flash_news',0);
$flash_news_category = get_theme_mod('flash_news_category','');
$flash_news_title_text = get_theme_mod ('flash_news_title_text',esc_html__('Flash News','freenews'));

if ($flash_news_category ==''){
    $query = new WP_Query(array(
        'post_type' => array( 'post' )
    ));
} else {
    $query = new WP_Query(array(
        'post_type' => array( 'post' ) ,
        'category_name' => esc_attr($flash_news_category),
    ));
}

?>
    <div class="flash-news">
        <?php if ($flash_news_title_text !=''){ ?>
        <div class="flash-news-header">
            <h4 class="flash-news-title"><?php echo esc_html($flash_news_title_text); ?></h4>
        </div>
        <?php } ?>
        <div class="marquee">
            <?php while ($query->have_posts()):$query->the_post(); ?>
                <artical class="news-post-title"><h3><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3></artical>

            <?php endwhile;
                wp_reset_postdata();
            ?>
        </div><!-- .marquee -->
    </div><!-- .flash-news -->
    <?php
}

add_action('freenews_frontend_flash_news','freenews_flash_news');

// Before Main Banner
function freenews_main_banner_before(){
$disable_main_banner = get_theme_mod('disable_main_banner',0);
$select_main_banner_category = get_theme_mod('select_main_banner_category','');
$slider_options = get_theme_mod('slider-options','main-banner');

    if($disable_main_banner==0){
        if($select_main_banner_category!='' || $slider_options !='main-banner'){  ?>
            <div class="main-banner">
                <div class="banner-wrap">

        <?php }
    }
}

add_action('freenews_frontend_mainbanner_before','freenews_main_banner_before');

// After Main Banner
function freenews_main_banner_after(){
$disable_main_banner = get_theme_mod('disable_main_banner',0);
$select_main_banner_category = get_theme_mod('select_main_banner_category','');
$slider_options = get_theme_mod('slider-options','main-banner');
    if($disable_main_banner==0){
        if($select_main_banner_category!='' || $slider_options !='main-banner'){ ?>
            </div><!-- .banner-wrap -->
        </div><!-- .main-banner -->

        <?php }
    }
}

add_action('freenews_frontend_mainbanner_after','freenews_main_banner_after');