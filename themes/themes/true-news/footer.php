<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package True News
 */
$true_news_default = true_news_get_default_theme_options();
$ed_read_later_posts_notification = absint( get_theme_mod( 'ed_read_later_posts_notification', $true_news_default['ed_read_later_posts_notification'] ) );
?>
<div class="tooltipOuter">
    <div class="tooltipInner"></div>
</div>
<?php if( $ed_read_later_posts_notification ){ ?>
    <div class="twp-read-later-notification"></div>
<?php }

do_action('true_news_off_canvas_content_action'); ?>

</div>

<footer id="colophon" class="site-footer" role="contentinfo">

    <?php

    if( is_active_sidebar('true-news-footer-widget-0') || is_active_sidebar('true-news-footer-widget-1') || is_active_sidebar('true-news-footer-widget-2') || is_active_sidebar('true-news-footer-widget-3') ):

        $footer_column_layout = absint( get_theme_mod( 'footer_column_layout', $true_news_default['footer_column_layout'] ) ); ?>

        <div class="footer-area footer-upper-area <?php echo 'footer-column-' . absint($footer_column_layout); ?>">
            <div class="wrapper">
                <div class="twp-row twp-footer-row">

                    <?php if (is_active_sidebar('true-news-footer-widget-0')): ?>
                        <div class="column">
                            <?php dynamic_sidebar('true-news-footer-widget-0'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('true-news-footer-widget-1')): ?>
                        <div class="column">
                            <?php dynamic_sidebar('true-news-footer-widget-1'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('true-news-footer-widget-2')): ?>
                        <div class="column">
                            <?php dynamic_sidebar('true-news-footer-widget-2'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('true-news-footer-widget-3')): ?>
                        <div class="column">
                            <?php dynamic_sidebar('true-news-footer-widget-3'); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    <?php endif; ?>

    <div class="footer-area footer-lower-area">

        <div class="wrapper">
            <div class="twp-row">

                <div class="column column-quarter">
                    <?php do_action('true_news_header_site_branding_action'); ?>
                </div>

                <div class="column column-5"></div>

                <div class="column column-quarter">
                    <?php if( has_nav_menu('social-menu') ){ ?>

                        <div class="site-footer-social">
                            <div class="social-icons">
                                <?php
                                wp_nav_menu(
                                    array('theme_location' => 'social-menu',
                                        'link_before' => '<span class="screen-reader-text">',
                                        'link_after' => '</span>',
                                        'menu_id' => 'social-menu',
                                        'fallback_cb' => false,
                                        'menu_class' => false,
                                        'depth' => 1,
                                    )
                                ); ?>
                            </div>
                        </div>

                    <?php } ?>
                </div>

            </div>
        </div>

        <div class="wrapper">
            <hr class="footer-hr">
        </div>

        <div class="wrapper wrapper-last">
            <div class="twp-row">

                <div class="column column-5">
                    <div class="site-info">

                        <?php
                        $true_news_default = true_news_get_default_theme_options();
                        $footer_copyright_text = get_theme_mod( 'footer_copyright_text',$true_news_default['footer_copyright_text'] );

                        echo esc_html__( 'Copyright ','true-news'). '&copy '. absint( date( 'Y' ) ).' <a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" ><span>' . esc_html( get_bloginfo( 'name', 'display' ) ) . '</span></a> '.esc_html( $footer_copyright_text ) ;

                        echo '<br>';

                        echo esc_html__( 'Theme: ','true-news').'True News '.esc_html__( 'By ','true-news').'<a href="'.esc_url('https://www.themeinwp.com/theme/true-news').'"  title="' . esc_attr__( 'Themeinwp', 'true-news' ) . '" target="_blank" rel="author"><span>' . __( 'Themeinwp. ', 'true-news' ) . '</span></a>';
                        echo  esc_html__( 'Powered by ', 'true-news' ).'<a href="'.esc_url('https://wordpress.org').'" title="' . esc_attr__( 'WordPress', 'true-news' ) . '" target="_blank"><span>' . esc_html__( 'WordPress.', 'true-news' ) . '</span></a>';
                        ?>

                    </div>
                </div>

                <div class="column column-5">
                    <div class="site-footer-menu">
                        <?php wp_nav_menu(array(
                            'theme_location' => 'footer-menu',
                            'menu_id' => 'footer-menu',
                            'container' => 'div',
                            'container_class' => 'menu',
                            'depth' => 1
                        )); ?>
                    </div>
                </div>

            </div>
        </div>

    </div>
</footer>

<?php do_action('true_news_footer_contents'); ?>

<button type="button" class="scroll-up">
    <i class="ion ion-ios-arrow-round-up meta-icon meta-icon-large"></i>
</button>

</div>
</div>
</div>
<?php wp_footer(); ?>
</body>
</html>