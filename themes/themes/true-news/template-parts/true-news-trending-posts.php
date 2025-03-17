<?php
/**
 * Template Name: Trending Template
 *
 * Home Page Template File
 *
 * @package True News
 */
get_header();
	

	if( class_exists('Booster_Extension_Class') ){

        $trending_items = array();
        $days = 32;
        if( function_exists( 'booster_extension_posts_visits' ) ){
	        $trending_items = booster_extension_posts_visits( $days );
	    }
        arsort( $trending_items );

        if( $trending_items ){

        	$true_news_default = true_news_get_default_theme_options();
        	$ed_read_later_posts = get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] );
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
								<div class="twp-masonary-layout all-item-content tn-content-active content-loded">

									<?php
						            foreach( $trending_items as $trending_post_id => $visit ){

						                $trending_query = new WP_Query( array( 'post_type' => 'post','ignore_sticky_posts' => 1,'posts_per_page'=> 1, 'post__in' => array( $trending_post_id ) ) );

						                if( $trending_query->have_posts() ):

					                        /* Start the Loop */
					                        while ($trending_query->have_posts()) :
												$trending_query->the_post();
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

					                        <?php
					                        endwhile;

						                    wp_reset_postdata();

						                endif;

						            } ?>

						        </div>
					        </main>
					    </div>
					</div>
				</div>
			</div>

		<?php
		}

    }else{
    	?>
    	<div class="block-elements block-elements-archive">
			<div class="wrapper">
				<div class="twp-row">
					<p><?php esc_html_e('Please Activate Booster Extension Plugin For trending posts.','true-news'); ?></p>
				</div>
			</div>
		</div>

		<?php
    }

get_footer();