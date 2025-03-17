<?php
/**
 * Latest Posts
 *
 * @package True News
 */
if( !function_exists( 'true_news_latest_posts_section' ) ):
	function true_news_latest_posts_section( $true_news_home_section, $repeat_times ){

		$true_news_default = true_news_get_default_theme_options();
		$ed_read_later_posts =absint(  get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) );
		$hide_post_author_avatar = esc_html( isset( $true_news_home_section->hide_post_author_avatar ) ? $true_news_home_section->hide_post_author_avatar : '' );
	    $hide_post_author = esc_html( isset( $true_news_home_section->hide_post_author ) ? $true_news_home_section->hide_post_author : '' );
	    $hide_post_date = esc_html( isset( $true_news_home_section->hide_post_date ) ? $true_news_home_section->hide_post_date : '' );
	    $hide_popular_posts = esc_html( isset( $true_news_home_section->hide_popular_posts ) ? $true_news_home_section->hide_popular_posts : '' );
	    $section_title = esc_html( isset( $true_news_home_section->section_title ) ? $true_news_home_section->section_title : '' );
	    $hide_trending_posts = esc_html( isset( $true_news_home_section->hide_trending_posts ) ? $true_news_home_section->hide_trending_posts : '' );
	    $sidebar = esc_html( isset( $true_news_home_section->sidebar_layout ) ? $true_news_home_section->sidebar_layout : 'global-sidebar');
	    $content_class = true_news_content_class( $sidebar );
	    $hide_post_category = esc_html( isset( $true_news_home_section->hide_post_category ) ? $true_news_home_section->hide_post_category : '' );

	    global $paged;
		$true_news_paged = ( get_query_var( 'page' ) ) ? absint( get_query_var( 'page' ) ) : 1;
	    $latest_post_query = new WP_Query( array( 'post_type' => 'post', 'paged' => $true_news_paged ) ); ?>
	    
		<div class="block-elements block-elements-blog">
			<div class="wrapper">
				<div class="twp-row">
					<div id="primary" class="content-area <?php echo esc_attr( $content_class ); ?>">

						<?php if( $hide_popular_posts != 'no' || $hide_trending_posts != 'no' ){ ?>
							<div class="content-header">
			                    <h2 class="block-title">
			                        <?php echo esc_html( $section_title ); ?>
			                    </h2>
		                        <div class="title-controls">
		                            <div class="twp-latest-tab">
		                                <ul>
		                                    <li>
		                                        <button type="button" id="twp-latest-tab" class="twp-latest-filter twp-btn-transparent twp-tab-active twp-content-loded">
		                                            <?php true_news_the_theme_svg('latest','rgb(192, 192, 192)'); ?><?php esc_html_e('Latest', 'true-news'); ?>
		                                        </button>
		                                    </li>

		                                    <?php if( $hide_popular_posts != 'no' ){ ?>
		                                        <li>
		                                            <button type="button" id="twp-popular-tab" class="twp-latest-filter twp-btn-transparent">
                                                        <?php true_news_the_theme_svg('star','rgb(192, 192, 192)'); ?> <?php esc_html_e('Popular', 'true-news'); ?>
                                                    </button>
		                                        </li>
		                                    <?php } ?>

		                                    <?php if( class_exists('Booster_Extension_Class') && $hide_trending_posts != 'no' ){ ?>
		                                        <li>
		                                            <button type="button" id="twp-trending-tab" class="twp-latest-filter twp-btn-transparent">
                                                        <?php true_news_the_theme_svg('blaze','rgb(192, 192, 192)'); ?> <?php esc_html_e('Trending', 'true-news'); ?>
                                                    </button>
		                                        </li>
		                                    <?php } ?>

		                                </ul>
		                            </div>
		                        </div>
			                </div>
			            <?php } ?>

						<main id="main" class="site-main" role="main">

							<div id="twp-latest-tab-content" class="twp-tab-contents twp-content-active">
								<?php
								if ( $latest_post_query->have_posts() ) :

									$i = 1;
									/* Start the Loop */
									while ( $latest_post_query->have_posts() ) : $latest_post_query->the_post();

										$class = 'twp-post-hentry';
										if( $i == 1 ){ 
											$image_size = 'large';
											$class .= ' twp-post-full'; 
										}else{ 
											$image_size = 'true-news-medium';
											$class .= ' twp-post-grid';
										}
										if ( !get_the_post_thumbnail() ){ $class .= ' twp-has-no-thumb'; }
										/*
										 * Include the Post-Format-specific template for the content.
										 * If you want to override this in a child theme, then include a file
										 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
										 */
										?>
										<article id="post-<?php the_ID(); ?>" <?php post_class( $class ); ?>>
											
											
											<?php
											if ( get_the_post_thumbnail() ) : ?>
												<div class="post-thumbnail">
													<?php if( get_the_post_thumbnail() ){ ?>
														<?php true_news_post_thumbnail($image_size); ?>
													<?php } ?>
												</div>
											<?php endif; ?>
											

											<div class="twp-content-wraper">
	                                            <div class="meta-categories-3">
	                                                <?php if( $hide_post_category != 'yes' ) { true_news_post_tag_cats($cat = true); } ?>
	                                            </div>
												<header class="entry-header">

													<h2 class="entry-title">
                                                        <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                                        <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                                    </h2>
													
													
												</header>

												<div class="entry-content">
													
													<?php
														if( has_excerpt() ){
																
															the_excerpt();
														}else{
															echo esc_html( wp_trim_words( get_the_content(),30,'...' ) );
														}

													wp_link_pages( array(
														'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'true-news' ),
														'after'  => '</div>',
													) );
													?>

												</div>

												<?php true_news_entry_footer( $hide_post_author_avatar, $hide_post_author, $hide_post_date ); ?>
												
											</div>

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
							
							<?php if( $hide_popular_posts != 'no' ){ ?>
								<div id="twp-popular-tab-content" class="twp-tab-contents"></div>
							<?php } ?>

							<?php if(class_exists('Booster_Extension_Class') && $hide_trending_posts != 'no' ){ ?>
								<div id="twp-trending-tab-content" class="twp-tab-contents"></div>
							<?php } ?>
							
						</main>
					</div>
					
					<?php true_news_sidebar( $sidebar ); ?>

				</div>
			</div>
		</div>
		<?php
	}
endif;
