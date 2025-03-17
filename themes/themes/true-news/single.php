<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package True News
 */
get_header();
	
	$true_news_default = true_news_get_default_theme_options();
	$twp_navigation_type = get_post_meta( get_the_ID(), 'twp_disable_ajax_load_next_post', true );
	if( $twp_navigation_type == '' || $twp_navigation_type == 'global-layout' ){
		$twp_navigation_type = get_theme_mod( 'twp_navigation_type', $true_news_default['twp_navigation_type'] );
	}
	
	$content_class = true_news_content_class(); ?>
	<div class="block-elements block-elements-single">
		<div class="wrapper">
			<div class="twp-row">

				<div id="primary" class="content-area <?php echo esc_attr( $content_class ); ?>">
					<main id="main" class="site-main" role="main">

						<?php
						while ( have_posts() ) : the_post();

							get_template_part( 'components/post/content', get_post_format() );

							// If comments are open or we have at least one comment, load up the comment template.
							if ( comments_open() || get_comments_number() ) :
								comments_template();
							endif;

						endwhile; // End of the loop.

						if( is_singular('post') ):

						    true_news_related_posts();

						endif;
						
						if( $twp_navigation_type != 'no-navigation' && is_singular('post') ){

							if( $twp_navigation_type == 'norma-navigation' ){

								the_post_navigation();

							}else{

								$next_post = get_next_post();
								if( isset( $next_post->ID ) ){

									$next_post_id = $next_post->ID;
									echo '<div loop-count="1" next-post="'.absint( $next_post_id ).'" class="twp-single-infinity"></div>';

								}

							}
						} ?>

					</main>
				</div>

				<?php true_news_sidebar(); ?>

			</div>
		</div>
	</div>

<?php
get_footer();
