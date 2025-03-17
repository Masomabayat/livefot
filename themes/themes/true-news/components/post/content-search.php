<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package True_News
 */

$true_news_default = true_news_get_default_theme_options();
$hide_post_categories = get_theme_mod( 'hide_post_categories',$true_news_default['hide_post_categories'] );
$ed_read_later_posts = get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] );
?>

	<div class="twp-content-wraper">

        <div class="meta-categories-3">
            <?php if( !$hide_post_categories ){ true_news_post_tag_cats(); } ?>
        </div>

		<header class="entry-header">
			<h2 class="entry-title">
                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                <?php if( 'post' === get_post_type() && $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
            </h2>
		</header>

		<div class="entry-content">
			<?php

				if( has_excerpt() ){
						
					the_excerpt();

				}else{

					echo esc_html( wp_trim_words( get_the_content(),30,'...' ) );

				}
			?>
		</div>

		<?php true_news_entry_footer( $hide_post_author_avatar = 'no', $hide_post_author = 'no', $hide_post_date = 'no' ); ?>

	</div>