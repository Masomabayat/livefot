<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package freenews
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php 
	//wp_body_open hook from WordPress 5.2
	if ( function_exists( 'wp_body_open' ) ) {
	    wp_body_open();
	} ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'freenews' ); ?></a>

	<?php
	$disable_search_form = get_theme_mod('disable_search_form',0);
	$show_banner = get_theme_mod('show_banner','home-page');
	$disable_flash_news = get_theme_mod('disable_flash_news',0);
	$disable_category_highlight_right = get_theme_mod('disable_category_highlight_right',0);
	$disable_category_highlight_left = get_theme_mod('disable_category_highlight_left',0);
	$disable_main_banner = get_theme_mod('disable_main_banner',0); ?>

	<header id="masthead" class="site-header">
		<div id="main-header" class="main-header">
			<div class="navigation-top">
        		<div class="wrap">
            	<div id="site-header-menu" class="site-header-menu">
               	<nav class="main-navigation" aria-label="<?php esc_attr_e('Primary Menu','freenews'); ?>" role="navigation">
							<?php
								/**
								 * Top Navigation
								 */
								do_action('freenews_frontend_navigation_top'); 
							?>
						 </nav><!-- #site-navigation -->
           		</div>
        		</div><!-- .wrap -->
			</div><!-- .navigation-top -->
			<?php
				/**
				* Secondary Navigation
				*/
				do_action('freenews_frontend_secondary_navigation');
			
			if ($disable_flash_news ==0 || has_nav_menu('menu-2')) {
			?>

			<div class="top-header">
				<div class="top-header-inner">

					<?php
						if($disable_flash_news==0){
							/**
							* Flash News
							*/
							do_action('freenews_frontend_flash_news');
						}
					?>

					<div class="header-social-menu">

						<?php
							if(has_nav_menu('menu-2')){
								/**
								 * Social navigation
								 */
								do_action ('freenews_frontend_social_navigation');
							}
						?>

					</div><!-- .header-social-menu -->
				</div><!-- .top-header-inner -->
			</div><!-- .top-header -->

			<?php }
			if ( ($disable_search_form == 0 ) || ( has_header_image() ) ){ ?>
				<div class="header-media-search">

					<?php
						if ($disable_search_form == 0){
							/**
							* Search Form
							*/
							do_action('freenews_frontend_search_form');
						}

						if ( has_header_image() ) {
							/**
							* Header Image
							*/
							do_action ('freenews_frontend_header_image');
						}
					?>

				</div><!-- .header-media-search -->
			<?php } ?>
			<div class="main-header-brand">
				<div class="header-brand">
					<div class="wrap">
						<div class="header-brand-content">
							<?php 

								/**
								 * Site Branding
								 */
								do_action ('freenews_frontend_site_branding');
							?>

							<div class="header-right">
								<div class="header-banner">

									<?php if ( is_active_sidebar( 'header-banner' ) ) {

										dynamic_sidebar( 'header-banner' );

									} ?>
								</div><!-- .header-banner -->
							</div><!-- .header-right -->
						</div><!-- .header-brand-content -->
					</div><!-- .wrap -->
				</div><!-- .header-brand -->

				<div id="nav-sticker">
					<div class="navigation-top">
						<div class="wrap">
							<div id="site-header-menu" class="site-header-menu">
								<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e('Primary Menu','freenews'); ?>">
								<?php

									/**
									 * Top Navigation
									 */
									do_action('freenews_frontend_navigation_top');

								?>
								</nav><!-- #site-navigation -->
            			</div>
        				</div><!-- .wrap -->
     				</div><!-- .navigation-top -->
				<div class="clock"> 
					<div id="time"></div>
					<div id="date"><?php echo date_i18n(__('l, F d, Y','freenews')); ?></div>
				</div>
				</div><!-- #nav-sticker -->
				<?php
					/**
					 * Secondary Navigation
					 */
					do_action('freenews_frontend_secondary_navigation');
				?>
			</div><!-- .main-header-brand -->
			<?php if( $disable_category_highlight_right == 0 || $disable_category_highlight_left ==0 || $disable_main_banner ==0 ) { ?>
			
					<?php
					/**
					* Main Banner
					*/

					if($show_banner=='home-page' ){
						if ( is_front_page() && is_home() ) {
							do_action ('freenews_frontend_mainbanner_before'); 

							// Default homepage
							do_action ('freenews_frontend_hightlight_category_left');
							do_action('freenews_frontend_main_banner');
							do_action('freenews_frontend_hightlight_category_right');

							do_action ('freenews_frontend_mainbanner_after');


							} elseif ( is_front_page()){
								do_action ('freenews_frontend_mainbanner_before');

								//Static homepage
								do_action ('freenews_frontend_hightlight_category_left');
								do_action('freenews_frontend_main_banner');
								do_action('freenews_frontend_hightlight_category_right');

								do_action ('freenews_frontend_mainbanner_after');
							}
					} elseif ($show_banner=='static-homepage') {
						do_action ('freenews_frontend_mainbanner_before');

							if ( is_front_page()){
								//Static homepage
									do_action ('freenews_frontend_hightlight_category_left');
									do_action('freenews_frontend_main_banner');
									do_action('freenews_frontend_hightlight_category_right');

								}
						do_action ('freenews_frontend_mainbanner_after');

							} elseif ($show_banner=='blog') {
							do_action ('freenews_frontend_mainbanner_before');

								if ( is_home()){
								//Blog page
									do_action ('freenews_frontend_hightlight_category_left');
									do_action('freenews_frontend_main_banner');
									do_action('freenews_frontend_hightlight_category_right');

									}

							do_action ('freenews_frontend_mainbanner_after'); ?>
							<?php } else {
								//everything else
								do_action ('freenews_frontend_mainbanner_before');

								do_action ('freenews_frontend_hightlight_category_left');
								do_action('freenews_frontend_main_banner');
								do_action('freenews_frontend_hightlight_category_right');

								do_action ('freenews_frontend_mainbanner_after');
							}
				
		} ?>
		</div><!-- .main-header -->
	</header><!-- #masthead -->

	<div id="content" class="site-content">
		<div class="site-content-cell">
			<?php if ( ( is_front_page() && is_home() ) || is_front_page() ) {
			// Default homepage
				if ( is_active_sidebar( 'advertise-area' ) ) { ?>
					<div class="advertise-area">
						<div class="wrap">

							<?php dynamic_sidebar( 'advertise-area' ); ?>

						</div><!-- .wrap -->
					</div><!-- .advertise-area -->

				<?php }

			} ?>