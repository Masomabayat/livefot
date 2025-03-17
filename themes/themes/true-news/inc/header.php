<?php
/**
 *
 * Header Content Functions
 *
 * @package True News
 */

if (!function_exists('true_news_ticker_news')) :
    /**
     * Breaking News Scroll
     *
     * @since ticker-news 1.0.0
     *
     */
    function true_news_ticker_news()
    {   
        $true_news_default = true_news_get_default_theme_options();
        $show_ticker_section = esc_html( get_theme_mod( 'show_ticker_section', $true_news_default['show_ticker_section'] ) );
        if ( $show_ticker_section && ( is_home() || is_front_page() ) ) {

            $true_news_ticker_news_category = esc_html( get_theme_mod( 'select_category_for_ticker', $true_news_default['select_category_for_ticker'] ) );
            $true_news_ticker_news_title = esc_html( get_theme_mod( 'ticker_title', $true_news_default['ticker_title'] ) );
            $show_ticker_meta_data = esc_html( get_theme_mod( 'show_ticker_meta_data', $true_news_default['show_ticker_meta_data'] ) );
            ?>
            <div class="header-marquee">
                <div class="wrapper">
                    <div class="twp-row">
                        <div class="column column-10">
                            <div class="marquee-bar">
                                <div class="title-label">
                                    <?php echo wp_kses_post($true_news_ticker_news_title); ?>
                                </div>
                                <?php
                                $true_news_ticker_news_args = array(
                                    'post_type' => 'post',
                                    'post__not_in' => get_option("sticky_posts"),
                                    'category_name' => esc_html( $true_news_ticker_news_category ),
                                    'ignore_sticky_posts' => true,
                                    'posts_per_page' => 7,
                                );

                                if( is_rtl() ) {
                                    $rtl = 'true';
                                }else{
                                    $rtl = 'false';
                                }

                                $true_news_ticker_news_post_query = new WP_Query($true_news_ticker_news_args);
                                if ($true_news_ticker_news_post_query->have_posts()) : ?>
                                    <div class="twp-marquee" data-slick='{"rtl": <?php echo esc_attr( $rtl ); ?>}'>
                                        <?php
                                        while ($true_news_ticker_news_post_query->have_posts()) :
                                            $true_news_ticker_news_post_query->the_post(); ?>
                                            <div class="twp-marquee-list">
                                                <a href="<?php the_permalink(); ?>" class="marquee-title">
                                                    <?php if ( has_post_thumbnail() ) {
                                                        $feature_image = wp_get_attachment_image_src(get_post_thumbnail_id( get_the_ID() ), 'thumbnail'); ?>
                                                        <span class="marquee-image">
                                                        <img src="<?php echo esc_url( $feature_image[0] ); ?>"
                                                             title="<?php the_title_attribute(); ?>"
                                                             alt="<?php the_title_attribute(); ?>">
                                                    </span>
                                                    <?php }
                                                    if ( $show_ticker_meta_data ){ ?>
                                                        <span class="marquee-date">
                                                        <?php true_news_entry_meta( $hide_post_author_avatar = 'yes', $hide_post_author = 'yes', $hide_post_date = 'no', $text = false, $link = false ); ?>
                                                            
                                                    </span>
                                                    <?php } ?>
                                                    <?php the_title(); ?>
                                                </a>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php
                                endif;
                                wp_reset_postdata(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
endif;
add_action('true_news_action_ticker_section', 'true_news_ticker_news', 10);


if( !function_exists('true_news_affix_bar') ):

    function true_news_affix_bar(){

        ?>
        <div class="header-affixbar header-affix-follow">
            <div class="wrapper">
                <div class="twp-row-flex">
                    <div class="topbar-left">
                        <div class="site-branding affix-site-branding">
                            <?php
                            $twp_affix_bar_logo = get_theme_mod('twp_affix_bar_logo'); 
                            if( $twp_affix_bar_logo ){
                                echo '<a href="'. esc_url( home_url('/') ).'" rel="home" ><img src="'.esc_html( $twp_affix_bar_logo ).'" alt="'.esc_html( get_bloginfo('name') ).'" title="'.esc_html( get_bloginfo('name') ).'" />';
                            }else{
                                if ( has_custom_logo() ) {
                                    true_news_the_custom_logo();
                                }else { ?>
                                    <div class="site-title twp-affix-title">
                                        <a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>
                                    </div>
                                <?php
                                }
                            } ?>
                        </div>
                    </div>
                    <div class="topbar-center">
                        <div class="topbar-items">
                            <div class="topbar-item">
                                <?php do_action('true_news_mid_affix_bar_action'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="topbar-right">
                        <div class="topbar-items">
                            <div class="topbar-item">
                                <div class="social-icons">
                                    <?php
                                    wp_nav_menu(
                                        array('theme_location' => 'social-menu',
                                            'link_before' => '<span class="screen-reader-text">',
                                            'link_after' => '</span>',
                                            'menu_id' => 'social-menu',
                                            'fallback_cb' => false,
                                            'menu_class' => false
                                        )); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

endif;
add_action('true_news_affix_bar_action', 'true_news_affix_bar', 10);

if( !function_exists('true_news_mid_affix_bar') ):

    function true_news_mid_affix_bar(){
        ?>
        <div class="twp-mid-affixbar">
            <div id="twp-current-read">

                <div id="twp-current-read-bg" class="twp-read-slideup current-read-animated"></div>
                <?php
                if ( !is_page_template('template-parts/true-news-home-page.php') && is_singular() ) { ?>

                    <span class="current-news-title twp-delay-animated1">
                      <?php esc_html_e( 'Reading Now','true-news' ); ?>
                    </span>
                    <span class="breaking-news-headline twp-delay-animated2 twp-read-fadein current-read-marquee">
                        <?php the_title(); ?>
                    </span>

                <?php
                }elseif ( is_search() ) { ?>

                    <span class="current-news-title twp-delay-animated1">
                      <?php esc_html_e( 'Search Results for:','true-news' ); ?>
                    </span>

                    <?php
                    /* translators: %s: search query. */
                    echo '<span class="breaking-news-headline twp-delay-animated2 twp-read-fadein current-read-marquee">' . esc_html( get_search_query() ) . '</span>';

                }elseif ( is_archive() && !is_author() ) { ?>

                    <span class="current-news-title twp-delay-animated1">
                      <?php esc_html_e( 'Category: ','true-news' ); ?>
                    </span>
                    
                    <?php the_archive_title('<span class="breaking-news-headline twp-delay-animated2 twp-read-fadein current-read-marquee">', '</span>');

                }elseif ( is_author() ) { ?>

                        <span class="current-news-title twp-delay-animated1">
                          <?php esc_html_e( 'Author: ','true-news' ); ?>
                        </span>
                        <?php the_archive_title('<span class="breaking-news-headline twp-delay-animated2 twp-read-fadein current-read-marquee">', '</span>');

                }else {

                    $true_news_ticker_news_args = array(
                        'post_type' => 'post',
                        'ignore_sticky_posts' => true,
                        'posts_per_page' => 1,
                    );
                    $true_news_ticker_news_post_query = new WP_Query($true_news_ticker_news_args);
                    if ($true_news_ticker_news_post_query->have_posts()) : ?>

                        <span class="current-news-title twp-delay-animated1">
                          <?php esc_html_e( 'News Flash: ','true-news' ); ?>
                        </span>

                        <?php
                        while ($true_news_ticker_news_post_query->have_posts()) :
                            $true_news_ticker_news_post_query->the_post(); ?>
                            <a href="<?php the_permalink(); ?>" class="breaking-news-headline twp-delay-animated2 twp-read-fadein current-read-marquee">
                                
                                <?php the_title(); ?>
                                <?php true_news_entry_meta($hide_post_author_avatar = 'yes', $hide_post_author = 'yes', $hide_post_date = 'no', $text = false, $link = false, $formate = true); ?>
                            </a>
                        <?php endwhile; ?>

                    <?php
                    endif;
                    wp_reset_postdata();
                } ?>

            </div>
        </div>
    <?php
    }

endif;
add_action('true_news_mid_affix_bar_action', 'true_news_mid_affix_bar', 10);

if( !function_exists('true_news_header_top_bar_item') ):

    function true_news_header_top_bar_item(){ ?>

        <?php if ( is_active_sidebar('off-canvas-1')  || is_active_sidebar('off-canvas-2') || is_active_sidebar('off-canvas-3')) : ?>

            <div class="topbar-item">

                <button type="button" class="offcanvas-trigger-btn">
                    <div class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </button>

            </div>

        <?php
        endif;
    }

endif;

add_action('true_news_header_top_bar_item_action', 'true_news_header_top_bar_item', 10);

if( !function_exists('true_news_header_site_branding') ):

    function true_news_header_site_branding(){ ?>

        <div class="site-branding">

            <?php true_news_the_custom_logo();

            if (is_front_page() && is_home()) : ?>

                <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>
                </h1>

            <?php else : ?>

                <div class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>
                </div>

            <?php
            endif;

            $description = get_bloginfo('description', 'display');

            if ($description || is_customize_preview()) : ?>

                <p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>

            <?php
            endif; ?>

        </div><!-- .site-branding -->
        <?php
    }

endif;

add_action('true_news_header_site_branding_action', 'true_news_header_site_branding', 10);

if( !function_exists('true_news_header_nav') ):

    function true_news_header_nav(){
        ?>
        <nav id="site-navigation" class="main-navigation" role="navigation">
            <button type="button" id="toggle-target" class="twp-btn toggle-menu twp-btn-transparent" aria-controls="primary-menu" aria-expanded="false">
                 <span class="screen-reader-text">
                    <?php esc_html_e('Primary Menu', 'true-news'); ?>
                </span>
                <i class="ham"></i>
            </button>
            <div class="twp-navigation-area">
                <?php wp_nav_menu(array(
                    'theme_location' => 'primary-menu',
                    'menu_id' => 'primary-menu',
                    'container' => 'div',
                    'container_class' => 'menu'
                )); ?>
            </div>
        </nav>
        <?php
    }

endif;

add_action('true_news_header_nav_action', 'true_news_header_nav', 10);

if( !function_exists('true_news_header_top_nav') ):

    function true_news_header_top_nav(){ ?>
            <div class="topbar-item">
                <div class="twp-topnav-area">
                    <?php wp_nav_menu(array(
                        'theme_location' => 'top-menu',
                        'menu_id' => 'top-menu',
                        'container' => 'div',
                        'container_class' => 'menu'
                    )); ?>
                </div>
            </div>

        <?php
    }

endif;

add_action('true_news_header_top_nav_action', 'true_news_header_top_nav', 10);
