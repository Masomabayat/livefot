<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package CoverNews
 */
$archive_class = covernews_get_option('archive_layout');

    if ($archive_class == 'archive-layout-grid'):
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('col-lg-4 col-sm-4 col-md-4 latest-posts-grid'); ?>
                 data-mh="archive-layout-grid">
            <?php covernews_page_layout_blocks('archive-layout-grid'); ?>
        </article>
    <?php elseif($archive_class == 'archive-layout-grid-2'): ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('col-lg-6 col-sm-6 col-md-6 latest-posts-grid'); ?> data-mh="archive-layout-grid-2" >
            <?php covernews_page_layout_blocks('archive-layout-grid-2'); ?>
        </article>
    <?php elseif($archive_class == 'archive-layout-list'): ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('latest-posts-list col-sm-12'); ?> >
            <?php covernews_page_layout_blocks('archive-layout-list'); ?>
        </article>
        <?php else: ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('latest-posts-full col-sm-12'); ?>>
            <?php covernews_page_layout_blocks(); ?>
        </article>
    <?php endif; ?>


