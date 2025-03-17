<?php
/**
 * Template Name: Read Later Template
 *
 * Pinned Posts Template File
 *
 * @package True News
 */
get_header();
	
	$pin_posts = true_news_add_read_later_posts_lists();
	$pin_posts_query = new WP_Query( array('post_type' => 'post', 'post__in' => $pin_posts ) );
	if( $pin_posts ){

		$true_news_default = true_news_get_default_theme_options();
		$ed_read_later_posts = esc_html( get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ) );
		$hide_post_categories = esc_html( get_theme_mod( 'hide_post_categories',$true_news_default['hide_post_categories'] ) );
		$hide_post_author_avatar = esc_html( get_theme_mod( 'twp_hide_post_author_avatar',$true_news_default['twp_hide_post_author_avatar'] ) );
		$hide_post_author = esc_html( get_theme_mod( 'twp_hide_post_author',$true_news_default['twp_hide_post_author'] ) );
		$hide_post_date = esc_html( get_theme_mod( 'twp_hide_post_date',$true_news_default['twp_hide_post_date'] ) );
		if( $hide_post_author_avatar ){ $hide_post_author_avatar = 'yes'; }else{ $hide_post_author_avatar = 'no'; }
		if( $hide_post_author ){ $hide_post_author = 'yes'; }else{ $hide_post_author = 'no'; } ?>

		<div class="block-elements block-elements-archive">
			<div class="wrapper">
				<div class="twp-row">
					<div id="primary" class="content-area">
						<main id="main" class="site-main" role="main">
						
						<?php
							if ( $pin_posts_query->have_posts() ) : ?>

								<div class="twp-masonary-layout all-item-content tn-content-active content-loded">
	                                <?php
	                                while( $pin_posts_query->have_posts() ):
	                                    $pin_posts_query->the_post();
	                                    $format = get_post_format( get_the_ID() ); ?>

	                                    <div class="twp-masonary-item">
	                                        <div class="content-grid">
	                                            <article class="news-article">
	                                                <div class="news-article-grid">

	                                                    <?php true_news_get_post_formate(); ?>

	                                                    <div class="entry-details">

	                                                        <?php if( $format != 'quote' ){ ?>

	                                                            <h3 class="entry-title entry-title-small">
	                                                                
	                                                                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>

	                                                                <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>

	                                                            </h3>

	                                                        <?php } ?>
	                                                        
	                                                        <?php true_news_entry_meta( $hide_post_author_avatar, $hide_post_author, $hide_post_date, $text = false ); ?>

	                                                    </div>

	                                                </div>
	                                            </article>
	                                        </div>
	                                    </div>

	                                <?php endwhile; ?>
	                            </div>
								
								<?php 
								wp_reset_postdata();
							endif; ?>

						</main>
					</div>
					
				</div>
			</div>
		</div>
		
	<?php
	}else{ ?>

		<div class="block-elements block-elements-archive">
			<div class="wrapper">
				<p><?php esc_html_e( 'It seems there is not any post added into Read Later list.', 'true-news' ); ?></p>
			</div>
		</div>

	<?php
	}

get_footer();