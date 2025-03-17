<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package True News
 */
get_header(); 
	
	$content_class = true_news_content_class();
	$true_news_default = true_news_get_default_theme_options();
	$sidebar = get_theme_mod( 'true_news_global_sidebar_layout',$true_news_default['true_news_global_sidebar_layout'] ); ?>
	
	<div class="block-elements block-elements-archive">
		<div class="wrapper">
			<div class="twp-row">

				<div id="primary" class="content-area <?php echo esc_attr( $content_class ); ?>">
					<main id="main" class="site-main" role="main">

					<?php
					if ( have_posts() ) : ?>
						<?php
						$i = 1;
						/* Start the Loop */
						while ( have_posts() ) : the_post();

							$class = 'twp-post-hentry';
							if( $i == 1 ){

								$image_size = 'large';
								$class .= ' twp-post-full';
								if( $sidebar == 'no-sidebar' ){
									$image_size = 'full';
								}

							}else{

								$image_size = 'true-news-medium';
								$class .= ' twp-post-grid';

							}

							if ( !get_the_post_thumbnail() ){ $class .= ' twp-has-no-thumb'; } ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class( $class ); ?>>
								
								<?php
								if ( get_the_post_thumbnail() ) : ?>

									<div class="post-thumbnail">
										<?php if( get_the_post_thumbnail() ){ ?>

											<?php true_news_post_thumbnail($image_size); ?>

										<?php } ?>
									</div>

								<?php endif;

								/*
								 * Include the Post-Format-specific template for the content.
								 * If you want to override this in a child theme, then include a file
								 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
								 */
								get_template_part( 'components/post/content', get_post_format() );
								?>

							</article><!-- #post-## -->

							<?php
							$i++;
							if( $i == 4 ){ $i = 1; }

						endwhile;
						
					else :

						get_template_part( 'components/post/content', 'none' );

					endif;

					do_action('true_news_archive_pagination'); ?>

					</main>
				</div>
				
				<?php true_news_sidebar(); ?>
				
			</div>
		</div>
	</div>
<?php
get_footer();
