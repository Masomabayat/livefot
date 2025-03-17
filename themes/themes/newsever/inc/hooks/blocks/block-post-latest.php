<?php
    /**
     * List block part for displaying latest posts in footer.php
     *
     * @package Newsever
     */
    
    $newsever_latest_posts_title = newsever_get_option('frontpage_latest_posts_section_title');
    $newsever_latest_posts_subtitle = newsever_get_option('frontpage_latest_posts_section_subtitle');
    $number_of_posts = newsever_get_option('number_of_frontpage_latest_posts');
    
    $all_posts = newsever_get_posts($number_of_posts);


?>
<div class="af-main-banner-latest-posts grid-layout">
    <div class="container-wrapper">
    <div class="af-container-block-wrapper pad-20">
            <div class="widget-title-section">
                <?php if (!empty($newsever_latest_posts_title)): ?>
                    <h4 class="widget-title header-after1">
                            <span class="header-after">
                                <?php echo esc_html($newsever_latest_posts_title); ?>
                            </span>
                    </h4>
                <?php endif; ?>

            </div>
            <div class="af-container-row clearfix">
                <?php
                    if ($all_posts->have_posts()) :
                        while ($all_posts->have_posts()) : $all_posts->the_post();
                            global $post;
                            $url = newsever_get_freatured_image_url($post->ID, 'newsever-medium');
                            
                            ?>
                            <div class="col-4 pad float-l" data-mh="you-may-have-missed">
                                <div class="read-single color-pad">
                                    <div class="data-bg read-img pos-rel read-bg-img"
                                         data-background="<?php echo esc_url($url); ?>">
                                        <img src="<?php echo esc_url($url); ?>">
                                        <div class="min-read-post-format">
                                            <?php echo newsever_post_format($post->ID); ?>
                                            <span class="min-read-item">
                                <?php newsever_count_content_words($post->ID); ?>
                            </span>
                                        </div>
                                        <a href="<?php the_permalink(); ?>"></a>
                                    </div>
                                    <div class="read-details color-tp-pad">

                                        <div class="read-categories">
                                            <?php newsever_post_categories(); ?>
                                        </div>
                                        <div class="read-title">
                                            <h4>
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </h4>
                                        </div>
                                        <div class="entry-meta">
                                            <?php newsever_post_item_meta(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                        endwhile; ?>
                    <?php
                    endif;
                    wp_reset_postdata();
                ?>
            </div>
    </div>
    </div>
</div>
