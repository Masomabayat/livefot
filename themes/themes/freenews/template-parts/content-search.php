<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package freenews
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php 

		freenews_post_thumbnail();

	?>

	<div class="entry-content-holder">
		<header class="entry-header">

			<div class="entry-meta">

				<?php freenews_cat_lists (); ?>

			</div><!-- .entry-meta -->

			<?php

			if ( is_singular() ) :

					the_title( '<h1 class="entry-title">', '</h1>' );

				else :

					the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );

			// Hide author, post date, category and tag text for pages.
				if ( 'post' === get_post_type() ) { ?>

					<div class="entry-meta">
						<?php
							

								freenews_posted_by();

								freenews_posted_on();
						?>
					</div><!-- .entry-meta -->

				<?php }
			endif; ?>

	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_excerpt(); ?>
	</div><!-- .entry-content -->

		<?php
		// Hide author, post date, category and tag text for pages.
			if ( 'post' === get_post_type() ) { ?>

			<footer class="entry-footer">
				<div class="entry-meta">

				<?php

					freenews_tag_lists();

					freenews_comment_links();

				?>
				</div><!-- .entry-meta -->
			</footer><!-- .entry-footer -->
		<?php } ?>
	</div><!-- .entry-content-holder -->
</article><!-- #post-<?php the_ID(); ?> -->