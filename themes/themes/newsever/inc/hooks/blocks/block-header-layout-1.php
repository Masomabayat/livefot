<?php
/**
 * List block part for displaying header content in page.php
 *
 * @package Newsever
 */

?>
<?php
$class = '';
$background = '';
if (has_header_image()) {
    $class = 'data-bg';
    $background = get_header_image();
}
$show_date = newsever_get_option('show_date_section');
$show_social_menu = newsever_get_option('show_social_menu_section');

$header_layout = newsever_get_option('header_layout');

if ($header_layout == 'header-layout-centered') {
    $header_class = 'logo-centered';
} else {
    $header_class = '';
}
?>
<?php if (is_active_sidebar('off-canvas-panel') || (has_nav_menu('aft-social-nav') && $show_social_menu == true) || ($show_date == true)) : ?>
    <div class="top-header">
        <div class="container-wrapper">
            <div class="top-bar-flex">
                <div class="top-bar-left col-66">

                    <?php if (is_active_sidebar('off-canvas-panel')) : ?>
                        <div class="off-cancas-panel">
  							<span class="offcanvas">
  								<a href="#" class="offcanvas-nav">
  									<div class="offcanvas-menu">
  										<span class="mbtn-top"></span>
  										<span class="mbtn-mid"></span>
  										<span class="mbtn-bot"></span>
  									</div>
  								</a>
  							</span>
                        </div>
                    <?php endif; ?>

                    <div class="date-bar-left">
                        <?php

                        if ($show_date == true): ?>
                            <span class="topbar-date">
                                        <?php
                                        echo wp_kses_post(date_i18n(get_option('date_format')));
                                        ?>
                                    </span>

                        <?php endif; ?>
                        <?php
                        $aft_language_switcher = newsever_get_option('aft_language_switcher');
                        if (!empty($aft_language_switcher)):
                            ?>
                            <span class="language-icon">
                                <?php echo do_shortcode($aft_language_switcher); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php

                    $secondry_menu_opt = newsever_get_option('show_secondary_menu_section');
                    if ($secondry_menu_opt == true) {
                        ?>
                        <div class="af-secondary-menu">
                            <div class="container-wrapper">
                                <?php if (has_nav_menu('aft-secondary-nav')): ?>
                                    <div class="aft-secondary-nav-wrapper">
                                        <div class="aft-small-secondary-nav">
                                            <?php
                                            wp_nav_menu(array(
                                                'theme_location' => 'aft-secondary-nav',
                                                'menu_id' => 'aft-secondary-menu',
                                                'container' => 'div',
                                                'container_class' => 'aft-secondary-navigation'
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                <?php endif;
                                ?>

                            </div>
                        </div>
                        <?php
                    }

                    ?>
                </div>

                <div class="top-bar-right col-3">
  						<span class="aft-small-social-menu">
  							<?php

                            if (has_nav_menu('aft-social-nav') && $show_social_menu == true): ?>

                                <?php
                                wp_nav_menu(array(
                                    'theme_location' => 'aft-social-nav',
                                    'link_before' => '<span class="screen-reader-text">',
                                    'link_after' => '</span>',
                                    'menu_id' => 'social-menu',
                                    'container' => 'div',
                                    'container_class' => 'social-navigation'
                                ));
                                ?>

                            <?php endif; ?>
  						</span>
                </div>
            </div>
        </div>

    </div>
<?php endif; ?>
<div class="main-header <?php echo esc_attr($header_class); ?> <?php echo esc_attr($class); ?>"
     data-background="<?php echo esc_attr($background); ?>">
    <div class="container-wrapper">
        <div class="af-container-row af-flex-container">
            <?php

            $newsever_banner_advertisement = newsever_get_option('banner_advertisement_section');
            $col_class_3 = 'col-1';
            if ((is_active_sidebar('home-advertisement-widgets')) || (absint($newsever_banner_advertisement) > 0)){
                $col_class_3 = 'col-3';
            }

            ?>

            <div class="<?php echo esc_attr($col_class_3); ?> float-l pad">
                <div class="logo-brand">
                    <div class="site-branding">
                        <?php
                        the_custom_logo();
                        if (is_front_page() || is_home()) : ?>
                            <h1 class="site-title font-family-1">
                                <a href="<?php echo esc_url(home_url('/')); ?>"
                                   rel="home"><?php bloginfo('name'); ?></a>
                            </h1>
                        <?php else : ?>
                            <p class="site-title font-family-1">
                                <a href="<?php echo esc_url(home_url('/')); ?>"
                                   rel="home"><?php bloginfo('name'); ?></a>
                            </p>
                        <?php endif; ?>

                        <?php
                        $description = get_bloginfo('description', 'display');
                        if ($description || is_customize_preview()) : ?>
                            <p class="site-description"><?php echo esc_html($description); ?></p>
                        <?php
                        endif; ?>
                    </div>
                </div>
            </div>

            <?php if ((is_active_sidebar('home-advertisement-widgets')) || (absint($newsever_banner_advertisement) > 0)): ?>

            <div class="col-66 float-l pad">
                <?php
                $banner_advertisement_scope = newsever_get_option('banner_advertisement_scope');
                if($banner_advertisement_scope == 'front-page-only'){
                    if( is_home() || is_front_page() ){
                        do_action('newsever_action_banner_advertisement');
                    }
                } else {
                    do_action('newsever_action_banner_advertisement');

                }

                ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>
<div class="exclusive-news">
        <?php do_action('newsever_action_banner_exclusive_posts'); ?>
</div>