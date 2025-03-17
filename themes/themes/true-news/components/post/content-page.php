<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package True_News
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="twp-content-wraper">

		<?php
		if ( get_the_post_thumbnail() ) : ?>
			<div class="post-thumbnail">
				<?php true_news_post_thumbnail('full'); ?>
			</div>
		<?php endif; ?>

		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</header>
		<div class="entry-content">
			<?php
				the_content();

				wp_link_pages( array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'true-news' ),
					'after'  => '</div>',
				) );
			?>
		</div>
		<footer class="entry-footer">
			<?php
				edit_post_link(
					sprintf(
						/* translators: %s: Name of current post */
						esc_html__( 'Edit %s', 'true-news' ),
						the_title( '<span class="screen-reader-text">"', '"</span>', false )
					),
					'<span class="edit-link">',
					'</span>'
				);
			?>
		</footer>
	</div>
</article><!-- #post-## -->