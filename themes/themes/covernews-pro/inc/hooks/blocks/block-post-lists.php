<?php
/**
 * List block part for displaying page content in page.php
 *
 * @package CoverNews
 */

$image_class = covernews_get_option('archive_layout');
if ($image_class == 'archive-layout-list') {
    $image_align_class = covernews_get_option('archive_image_alignment');
    $image_class .= ' ';
    $image_class .= $image_align_class;
}

$excerpt_length = 20;

global $post;
$thumbnail_size ='medium';
$col_class = 'col-ten';
?>
<div class="base-border <?php echo esc_attr($image_class); ?>">
    <div class="align-items-center">
        <?php

        if (has_post_thumbnail()):
            $col_class = 'col-five';
            ?>
            <div class="col <?php echo $col_class; ?> col-image">
                <figure class="categorised-article">
                    <div class="categorised-article-wrapper">
                        <div class="data-bg-hover data-bg-categorised read-bg-img"">
                        <a  href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ):
                                the_post_thumbnail($thumbnail_size);
                            endif;
                            ?>
                        </a>

                        </div>
                    </div>
                </figure>
                <?php echo covernews_post_format($post->ID); ?>
            </div>
        <?php endif; ?>
        <div class="col <?php echo $col_class; ?> col-details">
            <div class="row prime-row">
                <?php if ('post' === get_post_type()) : ?>
                    <div class="figure-categories figure-categories-bg">
                        
                        <?php covernews_post_categories('/'); ?>
                    </div>
                <?php endif; ?>
            <?php the_title('<h3 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a>
            </h3>'); ?>
            <div class="grid-item-metadata">

                <?php covernews_post_item_meta(); ?>
            </div>
            <?php
            $archive_content_view = covernews_get_option('archive_content_view');
            if ($archive_content_view != 'archive-content-none'):
                ?>
                <div class="full-item-discription">
                    <div class="post-description">

                        <?php

                        if ($archive_content_view == 'archive-content-excerpt') {
                            $excerpt = covernews_get_excerpt($excerpt_length, get_the_content());
                            echo wp_kses_post(wpautop($excerpt));
                        } else {
                            the_content();
                        }
                        ?>

                    </div>
                </div>
            <?php endif; ?>
        </div>
        </div>
        <?php
        wp_link_pages(array(
            'before' => '<div class="page-links">' . esc_html__('Pages:', 'covernews'),
            'after' => '</div>',
        ));
        ?>
    </div>
</div>






