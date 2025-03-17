<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package True News
 */
get_header();
	
	$true_news_default = true_news_get_default_theme_options();
	$ed_popular_posts = get_theme_mod('ed_popular_posts',$true_news_default['ed_popular_posts']);
	$ed_trending_posts = get_theme_mod('ed_trending_posts',$true_news_default['ed_trending_posts']);
	$section_title = get_theme_mod('tab_section_title',$true_news_default['tab_section_title']);
	$content_class = true_news_content_class(); ?>
	<div class="block-elements block-elements-blog">
		<div class="wrapper">
			<div class="twp-row">
				<div id="primary" class="content-area <?php echo esc_attr( $content_class ); ?>">
					
					<?php if( $section_title || $ed_popular_posts || $ed_trending_posts ){ ?>

						<div class="content-header">
		                    
		                    <?php if( $section_title ){ ?>

			                    <h2 class="block-title">
			                        <?php echo esc_html( $section_title ); ?>
			                    </h2>

			                <?php } ?>

			                <?php if( $ed_popular_posts || $ed_trending_posts ){ ?>
		                        <div class="title-controls">
		                            <div class="twp-latest-tab">
		                                <ul>

		                                    <li>
		                                        <button type="button" id="twp-latest-tab" class="twp-latest-filter twp-btn-transparent twp-tab-active twp-content-loded">
                                                    <?php true_news_the_theme_svg('latest','rgb(192, 192, 192)'); ?> <?php esc_html_e('Latest', 'true-news'); ?>
		                                        </button>
		                                    </li>

		                                    <?php if( $ed_popular_posts ){ ?>
		                                        <li>
		                                            <button type="button" id="twp-popular-tab" class="twp-latest-filter twp-btn-transparent">
                                                        <?php true_news_the_theme_svg('star','rgb(192, 192, 192)'); ?> <?php esc_html_e('Popular', 'true-news'); ?>
		                                            </button>
		                                        </li>
		                                    <?php } ?>

		                                    <?php if( class_exists('Booster_Extension_Class') && $ed_trending_posts ){ ?>
		                                        <li>
		                                            <button type="button" id="twp-trending-tab" class="twp-latest-filter twp-btn-transparent">
		                                                <i class="ion ion-ios-flame"></i> <?php esc_html_e('Trending', 'true-news'); ?>
		                                            </button>
		                                        </li>
		                                    <?php } ?>

		                                </ul>
		                            </div>
		                        </div>
	                        <?php } ?>

		                </div>

		            <?php } ?>

					<main id="main" class="site-main" role="main">
						<div id="twp-latest-tab-content" class="twp-tab-contents twp-content-active">

							<?php
							if ( have_posts() ) :
								
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
									if ( !get_the_post_thumbnail() ){ $class .= ' twp-has-no-thumb'; }
									?>

									<article id="post-<?php the_ID(); ?>" <?php post_class( $class ); ?>>

										<div class="post-thumbnail">

											<?php if ( get_the_post_thumbnail() ) : ?>

												<?php true_news_post_thumbnail($image_size); ?>

											<?php endif; ?>

										</div>

										<?php
										/*
										 * Include the Post-Format-specific template for the content.
										 * If you want to override this in a child theme, then include a file
										 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
										 */
										get_template_part( 'components/post/content', get_post_format() ); ?>

									</article><!-- #post-## -->

									<?php
									$i++;
									if( $i == 4 ){ $i = 1; }

								endwhile;

							else :

								get_template_part( 'components/post/content', 'none' );

							endif; ?>

							<?php do_action('true_news_archive_pagination'); ?>

						</div>

						<?php if( $ed_popular_posts ){ ?>

							<div id="twp-popular-tab-content" class="twp-tab-contents"></div>

						<?php } ?>

						<?php if( class_exists('Booster_Extension_Class') && $ed_trending_posts ){ ?>

							<div id="twp-trending-tab-content" class="twp-tab-contents"></div>

						<?php } ?>

					</main>

				</div>
				
				<?php true_news_sidebar(); ?>
				
			</div>
		</div>
	</div>
<?php
get_footer();
