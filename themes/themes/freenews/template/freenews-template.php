<?php
/**
 * Template Name: Home Template
 *
 * @package freenews
 * 
 */

get_header();

	/*
	 * Standard Section
	 */

	do_action ('freenews_frontend_standard_section');

?>
	<div class="main-section">
		<div class="wrap">

			<?php

			if ( is_active_sidebar( 'freenews-template-main' ) ) { ?>
					<div id="main-content-area" class="main-content-area">
						<div class="main-content-holder">

							<?php dynamic_sidebar( 'freenews-template-main' ); ?>

						</div><!-- .main-content-holder -->
					</div><!-- .main-content-area -->

			<?php }

			if ( is_active_sidebar( 'freenews-template-left' ) ) { ?>
					<aside id="left-widget-area" class="left-widget-area" role="complementary" aria-label="<?php esc_attr_e('Template Left','freenews'); ?>">
						<div class="theiaStickySidebar">

							<?php dynamic_sidebar( 'freenews-template-left' ); ?>

						</div>
					</aside><!-- .left-widget-area -->

			<?php }

			if ( is_active_sidebar( 'freenews-template-right' ) ) { ?>
					<aside id="right-widget-area" class="right-widget-area" role="complementary" aria-label="<?php esc_attr_e('Template Right','freenews'); ?>">
						<div class="theiaStickySidebar">

							<?php dynamic_sidebar( 'freenews-template-right' ); ?>

						</div>
					</aside><!-- .right-widget-area -->

			<?php }

			?>

		</div><!-- .wrap -->
	</div><!-- .main-section -->





<?php get_footer();