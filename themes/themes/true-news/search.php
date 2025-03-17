<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package True News
 */
get_header(); 
$content_class = true_news_content_class();
$true_news_default = true_news_get_default_theme_options();
$ed_read_later_posts = get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ); ?>

<div class="block-elements block-elements-search">
	<div class="wrapper">
		<div class="twp-row">
			
			<section id="primary" class="content-area <?php echo esc_attr( $content_class ); ?>">
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
							}else{ 
								$image_size = 'true-news-medium';
								$class .= ' twp-post-grid';
							}
							if ( !get_the_post_thumbnail() ){ $class .= ' twp-has-no-thumb'; } ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class( $class ); ?>>

								<?php
								if ( get_the_post_thumbnail() || $ed_read_later_posts ) : ?>

									<div class="post-thumbnail">

										<?php if( get_the_post_thumbnail() ){ ?>

											<?php true_news_post_thumbnail( $image_size ); ?>

										<?php } ?>

									</div>

								<?php endif;
								
								/**
								 * Run the loop for the search to output the results.
								 * If you want to overload this in a child theme then include a file
								 * called content-search.php and that will be used instead.
								 */
								get_template_part( 'components/post/content', 'search' ); ?>

							</article><!-- #post-## -->

							<?php
							$i++;
							if( $i == 4 ){ $i = 1; }

						endwhile;

					else :

						get_template_part( 'components/post/content', 'none' );

					endif;

					if ( have_posts() ) : do_action('true_news_archive_pagination'); endif; ?>
				
				</main>
			</section>
			
			<?php true_news_sidebar(); ?>

		</div>
	</div>
</div>
<?php
get_footer();
