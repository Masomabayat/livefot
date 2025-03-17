<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Newsever
 */

get_header();

?>
    <div class="af-container-block-wrapper clearfix">
        <div id="primary" class="content-area ">
            <main id="main" class="site-main ">
                <?php
                while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                        <?php if(!is_singular('post')): ?>
                            <header class="entry-header">
                                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                            </header><!-- .entry-header -->
                        <?php endif; ?>

                        <?php
                            if (has_excerpt(get_the_ID())):
                                $single_posts_excerpt = get_the_excerpt(get_the_ID());
                                if (!empty($single_posts_excerpt)):
                                    ?>
                                    <div class="post-excerpt">
                                        <?php echo esc_html(get_the_excerpt(get_the_ID())); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                                <div class="entry-content-wrap read-single">
                                    <?php
                                    if(is_singular('post')){
                                        $single_post_featured_image_view = newsever_get_option('single_post_featured_image_view');
                                        if ($single_post_featured_image_view == 'within-content') {
                                            do_action('newsever_action_single_featured_image');
                                        }
                                    }else{
                                        newsever_post_thumbnail();
                                    }
                                    ?>

                                    <?php get_template_part('template-parts/content', get_post_type()); ?>
                                </div>

                        <div class="aft-comment-related-wrap">
                            <?php
                            // If comments are open or we have at least one comment, load up the comment template.
                            if (comments_open() || get_comments_number()) :
                                comments_template();
                            endif;
                            ?>

                            <?php
                            $show_related_posts = esc_attr(newsever_get_option('single_show_related_posts'));
                            if ($show_related_posts):
                                if ('post' === get_post_type()) :
                                    newsever_get_block('related');
                                endif;
                            endif;
                            ?>
                        </div>


                    </article>
                <?php

                endwhile; // End of the loop.
                ?>

            </main><!-- #main -->
        </div><!-- #primary -->
        <?php
        get_sidebar(); ?>
    </div>
<?php
get_footer();
