<?php
/**
 *
 * Footer Content Functions
 *
 * @package True News
 */
if( !function_exists('true_news_footer_ticker_posts') ):

    function true_news_footer_ticker_posts(){
            
        $true_news_default = true_news_get_default_theme_options();
        $ed_read_later_posts = get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] );
        $ed_ticker_post = absint( get_theme_mod( 'ed_ticker_post',$true_news_default['ed_ticker_post'] ) );
        $ed_ticker_post_arrow = absint( get_theme_mod( 'ed_ticker_post_arrow',$true_news_default['ed_ticker_post_arrow'] ) );
        $ed_ticker_post_dots = absint( get_theme_mod( 'ed_ticker_post_dots',$true_news_default['ed_ticker_post_dots'] ) );
        $ed_ticker_post_autoplay = absint( get_theme_mod( 'ed_ticker_post_autoplay',$true_news_default['ed_ticker_post_autoplay'] ) );

        $footer_ticker_post_category = esc_html( get_theme_mod( 'footer_ticker_post_category' ) );
        if( $ed_ticker_post ){

            $footer_ticker_query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 12, 'category_name' => esc_html( $footer_ticker_post_category ) ) ); ?>

            <div class="drawer-handle">
                <div class="drawer-handle-open">
                    <i class="ion ion-ios-add"></i>
                </div>
            </div>

            <?php if( $footer_ticker_query->have_posts() ){

                if ( $ed_ticker_post_autoplay ) {
                    $autoplay = 'true';
                }else{
                    $autoplay = 'false';
                }
                if( $ed_ticker_post_dots ) {
                    $dots = 'true';
                }else {
                    $dots = 'false';
                }
                if( $ed_ticker_post_arrow ) {
                    $arrows = 'true';
                }else {
                    $arrows = 'false';
                }
                if( is_rtl() ) {
                    $rtl = 'true';
                }else{
                    $rtl = 'false';
                }
            ?>

                <div class="recommendation-panel-content">
                    <div class="drawer-handle-close">
                        <i class="ion ion-ios-close"></i>
                    </div>
                    <div class="recommendation-panel-slider">
                        <div class="wrapper">
                            <div class="drawer-carousel" data-slick='{"autoplay": <?php echo esc_attr( $autoplay ); ?>, "dots": <?php echo esc_attr( $dots ); ?>, "arrows": <?php echo esc_attr( $arrows ); ?>, "rtl": <?php echo esc_attr( $rtl ); ?>}'>
                                <?php
                                while( $footer_ticker_query->have_posts() ){
                                    $footer_ticker_query->the_post();
                                    $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium' ); ?>

                                    <div class="slide-item">
                                        <article class="news-article">
                                            <div class="news-article-list direct-ltr">
                                                <div class="entry-image entry-image-1">
                                                    <a href="<?php the_permalink(); ?>" class="data-bg data-bg-xs" data-background="<?php echo esc_url( $featured_image[0] ); ?>"></a>
                                                </div>
                                                <div class="entry-details">
                                                    <h4 class="entry-title entry-title-small">
                                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                        <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                    </h4>
                                                </div>
                                            </div>
                                        </article>
                                    </div>

                                <?php } ?>

                            </div>
                        </div>
                    </div>
                </div>

            <?php
            wp_reset_postdata(); }
        }
    }

endif;

add_action('true_news_footer_contents','true_news_footer_ticker_posts',10);


if( !function_exists('true_news_footer_offcanvas') ):

    function true_news_footer_offcanvas(){

        ?>
        <div id="offcanvas-menu">
            <div class="close-offcanvas-menu offcanvas-item">
                <a href="javascript:void(0)" class="offcanvas-close">
                    <span>
                       <?php echo esc_html__('Close', 'true-news'); ?>
                    </span>
                    <span class="ion ion-ios-close meta-icon meta-icon-large"></span>
                </a>
            </div>

            <div id="primary-nav-offcanvas" class="offcanvas-navigation offcanvas-item">
                <div class="offcanvas-title">
                    <?php esc_html_e('Top Menu', 'true-news'); ?>
                </div>
                <?php wp_nav_menu(array(
                    'theme_location' => 'top-menu',
                    'menu_id' => 'top-menu',
                    'container' => 'div',
                    'container_class' => 'menu'
                )); ?>
            </div>

            <div id="primary-nav-offcanvas" class="offcanvas-navigation offcanvas-item">
                <div class="offcanvas-title">
                    <?php esc_html_e('Main Menu', 'true-news'); ?>
                </div>
                <?php wp_nav_menu(array(
                    'theme_location' => 'primary-menu',
                    'menu_id' => 'primary-menu',
                    'container' => 'div',
                    'container_class' => 'menu'
                )); ?>
            </div>
                
            <?php if (has_nav_menu('social-menu')) { ?>
                <div class="offcanvas-social offcanvas-item">
                    <div class="offcanvas-title">
                        <?php esc_html_e('Social profiles', 'true-news'); ?>
                    </div>
                    <div class="social-icons">
                        <?php
                        wp_nav_menu(
                            array('theme_location' => 'social-menu',
                                'link_before' => '<span class="screen-reader-text">',
                                'link_after' => '</span>',
                                'menu_id' => 'social-menu',
                                'fallback_cb' => false,
                                'menu_class' => false,
                                'depth' => 1
                            )); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php
    }

endif;

add_action('true_news_footer_contents','true_news_footer_offcanvas',15);