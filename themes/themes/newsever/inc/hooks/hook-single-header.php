<?php
if (!function_exists('newsever_single_header')) :
    /**
     * Banner Slider
     *
     * @since Newsever 1.0.0
     *
     */
    function newsever_single_header()
    {
        $single_post_featured_image_view = newsever_get_option('single_post_featured_image_view');
        $show_featured_image = newsever_get_option('single_show_featured_image');

        global $post;
        $post_id = $post->ID;

        $wrapper_class = '';
        if (($show_featured_image == false) || (has_post_thumbnail($post_id) == false)) {
            $wrapper_class = 'aft-no-featured-image';

        }


        ?>

        <header class="entry-header pos-rel <?php echo esc_attr($wrapper_class); ?>">
            <div class="container-wrapper ">
                <div class="read-details af-container-block-wrapper">

                    <?php
                    $newsever_has_featured_image = false;
                    $col_class = 'col-1';

                    if ($single_post_featured_image_view == 'within-content' || $single_post_featured_image_view == 'full') {
                        $col_class = 'col-1';
                    } else {
                        if (has_post_thumbnail($post_id)) {
                            $newsever_has_featured_image = true;
                            $col_class = 'col-2';
                        }
                    }

                    ?>



                    <?php

                    if ($newsever_has_featured_image):
                        $single_post_featured_image_view = newsever_get_option('single_post_featured_image_view');
                        if ($single_post_featured_image_view == 'default'):
                            ?>
                            <div class="newsever-entry-featured-image-wrap float-l <?php echo esc_attr($col_class); ?>">
                                <?php do_action('newsever_action_single_featured_image'); ?>
                            </div>
                        <?php endif;
                    endif; ?>

                    <div class="newsever-entry-header-details-wrap float-l <?php echo esc_attr($col_class); ?>">
                        <?php do_action('newsever_action_single_entry_details'); ?>
                    </div>


                </div>

            </div>


            <?php

            if ($single_post_featured_image_view == 'full') {
                do_action('newsever_action_single_featured_image');


            }
            ?>

        </header><!-- .entry-header -->

        <!-- end slider-section -->
        <?php
    }
endif;
add_action('newsever_action_single_header', 'newsever_single_header', 40);

add_action('newsever_action_single_entry_details', 'newsever_single_entry_details', 40);

function newsever_single_entry_details()
{
    global $post;

    $single_post_featured_image_view = newsever_get_option('single_post_featured_image_view');
    $col_class = '';
    if ($single_post_featured_image_view == 'full') {
        $col_class = 'af-category-inside-img';
    }

    ?>
    <div class="entry-header-details <?php echo esc_attr($col_class); ?>">
        <?php if ('post' === get_post_type()) : ?>
            <div class="read-categories">
                <?php newsever_post_categories(); ?>

            </div>
        <?php endif; ?>

        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
        <div class="post-meta-share-wrapper">
            <div class="post-meta-detail">
                                    <span class="min-read-post-format">
                                        <?php echo newsever_post_format($post->ID); ?>
                                    </span>
                <span class="entry-meta">
                                        <?php newsever_post_item_publish_author(); ?>
                                    </span>
                <?php newsever_post_item_publish_date(); ?>
                <?php newsever_count_content_words($post->ID); ?>
            </div>
            <?php
            newsever_single_post_social_share_icons($post->ID);
            ?>
        </div>

    </div>
    <?php

}


add_action('newsever_action_single_featured_image', 'newsever_single_featured_image', 40);

function newsever_single_featured_image()
{
    global $post;
    $post_id = $post->ID;
    $show_featured_image = newsever_get_option('single_show_featured_image');


    if ($show_featured_image):
        ?>
        <div class="read-img pos-rel">
            <?php newsever_post_thumbnail(); ?>
            <span class="aft-image-caption-wrap">

                        <?php
                        if (has_post_thumbnail($post_id)):
                            if ($aft_image_caption = get_post(get_post_thumbnail_id())->post_excerpt): ?>
                                <span class="aft-image-caption">
                                    <p>
                                        <?php echo $aft_image_caption; ?>
                                    </p>
                                </span>
                            <?php
                            endif;
                        endif;
                        ?>
                    </span>

        </div>
    <?php endif;

}