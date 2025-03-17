<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package True News
 */
$true_news_default = true_news_get_default_theme_options();
$ed_read_later_posts = get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] );
$hide_post_author_avatar = get_theme_mod( 'twp_hide_post_author_avatar',$true_news_default['twp_hide_post_author_avatar'] );
$hide_post_author = get_theme_mod( 'twp_hide_post_author',$true_news_default['twp_hide_post_author'] );
$hide_post_date = get_theme_mod( 'twp_hide_post_date',$true_news_default['twp_hide_post_date'] );
$hide_post_categories = get_theme_mod( 'hide_post_categories',$true_news_default['hide_post_categories'] );

if( $hide_post_author_avatar ){ $hide_post_author_avatar = 'yes'; }else{ $hide_post_author_avatar = 'no'; }
if( $hide_post_author ){ $hide_post_author = 'yes'; }else{ $hide_post_author = 'no'; }
if( $hide_post_date ){ $hide_post_date = 'yes'; }else{ $hide_post_date = 'no'; }

if( is_singular() ): ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

<?php endif;

	if ( is_single() ) { ?>

        <div class="meta-categories-3">

            <?php if( !$hide_post_categories ){ true_news_post_tag_cats(); } ?>

        </div>

		<header class="entry-header entry-header-1">

			<h1 class="entry-title">

	            <?php the_title(); ?>

	            <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>

	        </h1>

		</header>

	<?php 
	} ?>

	<?php
	if( class_exists( 'Booster_Extension_Class') && is_single() && 'post' === get_post_type() ){

		echo '<div class="twp-single-booster">';
		$args = array('layout'=>'layout-1','status' => 'enable');
		do_action('booster_extension_social_icons',$args);
		do_action('booster_extension_read_time');
		do_action('booster_extension_post_view_action');
		echo '</div>';

	}

	if ( is_single() && ( get_the_post_thumbnail() ) ) : ?>

		<div class="post-thumbnail">

			<?php if( get_the_post_thumbnail() ){ ?>

				<?php true_news_post_thumbnail('full'); ?>

			<?php } ?>

		</div>

	<?php endif; ?>
	
	<div class="twp-content-wraper">

		<?php
		if ( !is_singular() ) { ?>

            <div class="meta-categories-3">

			    <?php if( !$hide_post_categories ){ true_news_post_tag_cats(); } ?>

            </div>

			<header class="entry-header">

				<h2 class="entry-title">

                    <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>

                    <?php if( 'post' === get_post_type() && $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>

                </h2>

			</header>

		<?php }

		if( is_singular() && has_excerpt() ){

	        /*Get first word of content*/
	        $first_word = substr( get_the_excerpt(), 0, 1 );
	        /*only allow alphabets*/
	        if ( preg_match("/[A-Za-z]+/", $first_word) != TRUE ) {
	            $first_word = '';
	        }

			echo '<div class="excerpt-content" data-initials="' . esc_attr($first_word) . '">';

					the_excerpt();

			echo '</div>';

		} ?>

		<div class="entry-content">

			<?php
			if( is_singular() ):

				the_content( sprintf(
					/* translators: %s: Name of current post. */
					wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'true-news' ), array( 'span' => array( 'class' => array() ) ) ),
					the_title( '<span class="screen-reader-text">"', '"</span>', false )
				) );

			else:

				if( has_excerpt() ){
						
					the_excerpt();

				}else{

					echo esc_html( wp_trim_words( get_the_content(),25,'...' ) );

				}

			endif;

			if( class_exists( 'Booster_Extension_Class') && is_single() && 'post' === get_post_type() ){
				echo '<div class="twp-single-booster">';
				$args = array('allenable' => 'allenable');
				do_action('booster_extension_like_dislike',$args);
				do_action('booster_extension_reaction');
				echo '</div>';
			}

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'true-news' ),
				'after'  => '</div>',
			) ); ?>

		</div>

		<?php if( !is_singular() ) { true_news_entry_footer( $hide_post_author_avatar, $hide_post_author, $hide_post_date ); } ?>

	</div>

<?php if( is_singular() ): ?>

</article>

<?php endif; ?>