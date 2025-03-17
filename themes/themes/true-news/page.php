<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package True News
 */
get_header(); 
	
	$content_class = true_news_content_class(); ?>

	<div class="block-elements block-elements-blog">
		<div class="wrapper">
			<div class="twp-row">

				<div id="primary" class="content-area <?php echo esc_attr( $content_class ); ?>">
					<main id="main" class="site-main" role="main">

						<?php
						while ( have_posts() ) : the_post();

							get_template_part( 'components/post/content', 'page' );

							// If comments are open or we have at least one comment, load up the comment template.
							if ( comments_open() || get_comments_number() ) :
								comments_template();
							endif;

						endwhile; // End of the loop.
						?>

					</main>
				</div>

				<?php true_news_sidebar(); ?>

			</div>
		</div>
	</div>
	
<?php
get_footer();
