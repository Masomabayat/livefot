<?php
/**
 *
 * Pagination Functions
 *
 * @package True News
 */

if( !function_exists('true_news_archive_pagination_x') ):

	// Archive Page Navigation
	function true_news_archive_pagination_x(){

		// Global Query
	    if( is_front_page() ){

	    	$posts_per_page = absint( get_option('posts_per_page') );
	        $twp_paged = ( get_query_var( 'page' ) ) ? absint( get_query_var( 'page' ) ) : 1;
	        $posts_args = array(
	            'posts_per_page'        => $posts_per_page,
	            'paged'                 => $twp_paged,
	        );
	        $posts_qry = new WP_Query( $posts_args );
	        $max = $posts_qry->max_num_pages;

	    }else{
	        global $wp_query;
	        $max = $wp_query->max_num_pages;
	        $twp_paged = ( get_query_var( 'paged' ) > 1 ) ? get_query_var( 'paged' ) : 1;
	    }

		$true_news_default = true_news_get_default_theme_options();
		$true_news_pagination_layout = esc_html( get_theme_mod( 'true_news_pagination_layout',$true_news_default['true_news_pagination_layout'] ) );
		$true_news_pagination_load_more = esc_html__('Load More Posts','true-news');
		$true_news_pagination_no_more_posts = esc_html__('No More Posts','true-news');

		if( $true_news_pagination_layout == 'next-prev' ){

			if( is_front_page() && is_page() ){

	            global $twp_paged;
	            $twp_paged = ( get_query_var( 'page' ) ) ? absint( get_query_var( 'page' ) ) : 1;
	            $latest_post_query = new WP_Query( array( 'post_type'=>'post', 'paged'=>$twp_paged ) );?>
	            <nav class="navigation posts-navigation" role="navigation" aria-label="Posts">
	                <div class="nav-links">
	                    <div class="nav-previous"><?php echo true_news_single_nav_escape( get_next_posts_link( __( 'Older posts', 'true-news' ), $latest_post_query->max_num_pages ) ); ?></div>
	                    <div class="nav-next"><?php echo true_news_single_nav_escape( get_previous_posts_link( __( 'Newer posts', 'true-news' ) ) ); ?></div>
	                </div>
	            </nav>
	        <?php

	        }else{
	            the_posts_navigation();
	        }

		}elseif( $true_news_pagination_layout == 'load-more' ){ ?>

			<div class="twp-ajax-post-load">

				<div  style="display: none;" class="twp-loaded-content"></div>
				<div style="display: none;" class="twp-loging-status"></div>

				<?php if( $max > 1 ){ ?>

					<a class="twp-loading-button twp-loading-style" href="javascript:void(0)"><?php echo esc_html( $true_news_pagination_load_more ); ?></a>

				<?php
				}else{ ?>

					<a class="twp-loading-button twp-loading-style twp-no-posts" href="javascript:void(0)"><?php echo esc_html( $true_news_pagination_no_more_posts ); ?></a>

				<?php
				} ?>

			</div>

		<?php }elseif( $true_news_pagination_layout == 'auto-load' ){
			 if( $max > 1 ){
				echo '<div style="display: none;" class="twp-loaded-content"></div>';
				echo '<div class="true-news-auto-pagination"></div>';
			}else{
				echo '<div style="display: none;" class="twp-loaded-content"></div>';
				echo '<div class="true-news-auto-pagination twp-no-posts">'.esc_html( $true_news_pagination_no_more_posts ).'</div>';
			}
		}else{

			if( is_page_template('template-parts/true-news-home-page.php') ){

				global $twp_paged;
	            $twp_paged = ( get_query_var( 'page' ) ) ? absint( get_query_var( 'page' ) ) : 1;
	            $latest_post_query = new WP_Query( array( 'post_type'=>'post', 'paged'=> absint( $twp_paged ) ) );
	            echo '<nav class="navigation pagination" role="navigation">';
	            echo '<div class="nav-links">';
	            $big = 999999999; // need an unlikely integer
	            echo paginate_links( array(
	                'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
	                'format' => '?paged=%#%',
	                'current' => max( 1, get_query_var('page') ),
	                'total' => absint( $latest_post_query->max_num_pages )
	            ) );
	            echo '</div>';
	            echo '</nav>';
	        }else{

	        	the_posts_pagination();
	        	
	        }

		}
			
	}

endif;
add_action('true_news_archive_pagination','true_news_archive_pagination_x',20);