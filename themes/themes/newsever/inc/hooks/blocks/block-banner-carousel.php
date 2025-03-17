<?php
/**
 * Full block part for displaying page content in page.php
 *
 * @package Newsever
 */
?>

<?php

$newsever_slider_category = newsever_get_option('select_slider_news_category');
$newsever_number_of_slides = newsever_get_option('number_of_slides');
$newsever_slider_mode = newsever_get_option('select_main_banner_section_mode');
$newsever_cat_class = '';

$newsever_select_default_carousel_layout = newsever_get_option('select_default_carousel_layout');
if ($newsever_select_default_carousel_layout == 'title-over-image') {
    $newsever_cat_class = 'af-category-inside-img';
}


$newsever_column = newsever_get_option('select_default_carousel_column');

if ($newsever_slider_mode == 'layout-6') {
    $newsever_slidesToShow = 2;
    $newsever_slide_to_scroll = 2;
    $newsever_centerMode = false;
    $newsever_break_point_1_slidesToShow = 2;
    $newsever_break_point_1_slidesToScroll = 2;
    $newsever_break_point_2_slidesToShow = 1;
    $newsever_break_point_2_slidesToScroll = 1;
    $newsever_break_point_3_slidesToShow = 1;
    $newsever_break_point_3_slidesToScroll = 1;
} elseif ($newsever_slider_mode == 'layout-7') {
    $newsever_slidesToShow = 3;
    $newsever_slide_to_scroll = 3;
    $newsever_centerMode = false;
    $newsever_break_point_1_slidesToShow = 3;
    $newsever_break_point_1_slidesToScroll = 3;
    $newsever_break_point_2_slidesToShow = 1;
    $newsever_break_point_2_slidesToScroll = 1;
    $newsever_break_point_3_slidesToShow = 1;
    $newsever_break_point_3_slidesToScroll = 1;
} else {
    $newsever_slidesToShow = 1;
    $newsever_slide_to_scroll = 1;
    $newsever_centerMode = false;
    $newsever_break_point_1_slidesToShow = 1;
    $newsever_break_point_1_slidesToScroll = 1;
    $newsever_break_point_2_slidesToShow = 1;
    $newsever_break_point_2_slidesToScroll = 1;
    $newsever_break_point_3_slidesToShow = 1;
    $newsever_break_point_3_slidesToScroll = 1;
}


$newsever_carousel_args = array(
    'slidesToShow' => $newsever_slidesToShow,
    'autoplaySpeed' => 8000,
    'slidesToScroll' => $newsever_slide_to_scroll,
    'centerMode' => $newsever_centerMode,
    'responsive' => array(
        array(
            'breakpoint' => 1024,
            'settings' => array(
                'slidesToShow' => $newsever_break_point_2_slidesToShow,
                'slidesToScroll' => $newsever_break_point_3_slidesToScroll,
                'infinite' => true
            ),
        ),
        array(
            'breakpoint' => 769,
            'settings' => array(
                'slidesToShow' => $newsever_break_point_2_slidesToShow,
                'slidesToScroll' => $newsever_break_point_2_slidesToScroll,
                'infinite' => true,
            ),
        ),
        array(
            'breakpoint' => 480,
            'settings' => array(
                'slidesToShow' => $newsever_break_point_3_slidesToShow,
                'slidesToScroll' => $newsever_break_point_3_slidesToScroll,
                'infinite' => true
            ),
        ),
    ),
);

$newsever_carousel_args_encoded = wp_json_encode($newsever_carousel_args);


?>

<?php
$color_class = 'category-color-1';
if (absint($newsever_slider_category) > 0) {
    $color_id = "category_color_" . $newsever_slider_category;
    // retrieve the existing value(s) for this meta field. This returns an array
    $term_meta = get_option($color_id);
    $color_class = ($term_meta) ? $term_meta['color_class_term_meta'] : 'category-color-1';
}
$section_title = newsever_get_option('main_banner_section_label');
?>

<?php if (!empty($section_title)): ?>
    <div class="em-title-subtitle-wrap">
        <?php if (!empty($section_title)): ?>
            <h4 class="widget-title header-after1">
                        <span class="header-after <?php echo esc_attr($color_class); ?>">
                            <?php echo esc_html($section_title); ?>
                        </span>
            </h4>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="af-banner-carousel-1 af-widget-carousel slick-wrapper banner-carousel-slider <?php echo esc_attr($newsever_select_default_carousel_layout); ?>"
     data-slick='<?php echo wp_kses_post($newsever_carousel_args_encoded); ?>'>
    <?php
    $slider_posts = newsever_get_posts($newsever_number_of_slides, $newsever_slider_category);
    if ($slider_posts->have_posts()) :
        while ($slider_posts->have_posts()) : $slider_posts->the_post();

            global $post;
            $url = newsever_get_freatured_image_url($post->ID, 'newsever-medium');


            ?>
            <div class="slick-item">
                <div class="read-single color-pad pos-rel">
                    <div class="read-img pos-rel read-img read-bg-img data-bg"
                         data-background="<?php echo esc_url($url); ?>">
                        <a class="aft-slide-items" href="<?php the_permalink(); ?>"></a>
                        <?php if (!empty($url)): ?>
                            <img src="<?php echo esc_url($url); ?>">
                        <?php endif; ?>

                        <div class="min-read-post-format">
                            <?php echo newsever_post_format($post->ID); ?>
                            <span class="min-read-item">
                                <?php newsever_count_content_words($post->ID); ?>
                            </span>
                        </div>
                    </div>
                    <div class="read-details color-tp-pad">
                        <div class="read-categories <?php echo esc_attr($newsever_cat_class); ?>">
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
        endwhile;
    endif;
    wp_reset_postdata();
    ?>
</div>