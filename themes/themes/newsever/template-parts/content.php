<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Newsever
 */

?>


<?php if (is_singular()) : ?>
        <div class="entry-content read-details">
            <?php
            the_content(sprintf(
                wp_kses(
                /* translators: %s: Name of current post. Only visible to screen readers */
                    __('Continue reading<span class="screen-reader-text"> "%s"</span>', 'newsever'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                get_the_title()
            )); ?>
            <?PHP if (is_single()): ?>
                <div class="post-item-metadata entry-meta">
                    <?php newsever_post_item_tag(); ?>
                </div>
            <?php endif; ?>
            <?php
            the_post_navigation(array(
                'prev_text' => __('<span class="em-post-navigation">Previous</span> %title', 'newsever'),
                'next_text' => __('<span class="em-post-navigation">Next</span> %title', 'newsever'),
                'in_same_term' => true,
                'taxonomy' => __('category', 'newsever'),
                'screen_reader_text' => __('Continue Reading', 'newsever'),
            ));
            ?>
            <?php wp_link_pages(array(
                'before' => '<div class="page-links">' . esc_html__('Pages:', 'newsever'),
                'after' => '</div>',
            ));
            ?>
        </div><!-- .entry-content -->
<?php else:

 do_action('newsever_action_archive_layout');

endif;
