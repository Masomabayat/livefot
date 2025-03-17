<?php
/**
 * Returns all categories.
 *
 * @since Newsever 1.0.0
 */
if (!function_exists('newsever_get_terms')):
function newsever_get_terms( $category_id = 0, $taxonomy='category', $default='' ){
    $taxonomy = !empty($taxonomy) ? $taxonomy : 'category';

    if ( $category_id > 0 ) {
            $term = get_term_by('id', absint($category_id), $taxonomy );
            if($term)
                return esc_html($term->name);


    } else {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
        ));


        if (isset($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                if( $default != 'first' ){
                    $array['0'] = __('Select Category', 'newsever');
                }
                $array[$term->term_id] = esc_html($term->name);
            }

            return $array;
        }
    }
}
endif;

/**
 * Returns all categories.
 *
 * @since Newsever 1.0.0
 */
if (!function_exists('newsever_get_terms_link')):
function newsever_get_terms_link( $category_id = 0 ){

    if (absint($category_id) > 0) {
        return get_term_link(absint($category_id), 'category');
    } else {
        return get_post_type_archive_link('post');
    }
}
endif;

/**
 * Returns word count of the sentences.
 *
 * @since Newsever 1.0.0
 */
if (!function_exists('newsever_get_excerpt')):
    function newsever_get_excerpt($length = 25, $newsever_content = null, $post_id = 1) {
        $widget_excerpt   = newsever_get_option('global_widget_excerpt_setting');
        if($widget_excerpt == 'default-excerpt'){
            return the_excerpt();
        }

        $length          = absint($length);
        $source_content  = preg_replace('`\[[^\]]*\]`', '', $newsever_content);
        $trimmed_content = wp_trim_words($source_content, $length, '...');
        return $trimmed_content;
    }
endif;

/**
 * Returns no image url.
 *
 * @since Newsever 1.0.0
 */
if(!function_exists('newsever_no_image_url')):
    function newsever_no_image_url(){
        $url = get_template_directory_uri().'/assets/images/no-image.png';
        return $url;
    }

endif;





/**
 * Outputs the tab posts
 *
 * @since 1.0.0
 *
 * @param array $args  Post Arguments.
 */
if (!function_exists('newsever_render_posts')):
  function newsever_render_posts( $type, $show_excerpt, $excerpt_length, $number_of_posts, $category = '0' ){

    $args = array();
   
    switch ($type) {
        case 'popular':
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => absint($number_of_posts),
                'orderby' => 'comment_count',
                'ignore_sticky_posts' => true
            );
            break;

        case 'recent':
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => absint($number_of_posts),
                'orderby' => 'date',
                'ignore_sticky_posts' => true
            );
            break;

        case 'categorised':
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => absint($number_of_posts),
                'ignore_sticky_posts' => true
            );
            $category = isset($category) ? $category : '0';
            if (absint($category) > 0) {
                $args['cat'] = absint($category);
            }
            break;


        default:
            break;
    }

    if( !empty($args) && is_array($args) ){
        $all_posts = new WP_Query($args);
        if($all_posts->have_posts()):
            echo '<ul class="article-item article-list-item article-tabbed-list article-item-left">';
            while($all_posts->have_posts()): $all_posts->the_post();

                ?>
                <li class="af-double-column list-style">
                    <div class="read-single color-pad">
                        <?php
                        if(has_post_thumbnail()){
                            $thumb = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()));
                            $url = $thumb['0'];
                            $col_class = 'col-sm-8';
                        }else {
                            $url = '';
                            $col_class = 'col-sm-12';
                        }
                        global $post;
                        ?>
                        <?php if (!empty($url)): ?>
                            <div class="data-bg read-img pos-rel col-4 float-l read-bg-img"
                                     data-background="<?php echo esc_url($url); ?>">
                                <?php if (!empty($url)): ?>
                                    <img src="<?php echo esc_url($url); ?>">
                                <?php endif; ?>
                                <a href="<?php the_permalink(); ?>"></a>
                                <div class="min-read-post-format">
                                    <?php echo newsever_post_format($post->ID); ?>
                                    <span class="min-read-item">
                                                <?php newsever_count_content_words($post->ID); ?>
                                            </span>
                                </div>

                            </div>
                        <?php endif; ?>
                        <div class="read-details col-75 float-l pad color-tp-pad">
                            <div class="full-item-metadata primary-font">
                                <div class="read-categories">

                                    <?php newsever_post_categories(); ?>
                                </div>
                            </div>
                            <div class="full-item-content">
                                <div class="read-title">
                                    <h4>
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h4>
                                </div>
                                <div class="entry-meta">
                                    <?php newsever_get_comments_count($post->ID); ?>
                                    <?php newsever_post_item_meta(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            <?php
            endwhile;wp_reset_postdata();
            echo '</ul>';
        endif;
    }
}
endif;

if(!function_exists('newsever_update_widget_before')):
    
    function newsever_update_widget_before($args='',$background='',$str=''){
        $newclass = 'aft-widget-background-'.esc_attr($background);
        $args['before_widget'] = str_replace( $str, $newclass, $args['before_widget'] );
        return $args['before_widget'];
    }
    endif;
