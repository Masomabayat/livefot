<?php
/**
 * Tab Posts Widgets.
 *
 * @package True News
 */

if ( !function_exists('true_news_tab_posts_widgets') ) :
    /**
     * Load widgets.
     *
     * @since 1.0.0
     */
    function true_news_tab_posts_widgets(){
        // Tab Post widget.
        register_widget('True_News_Tab_Posts_Widget');

    }
endif;
add_action('widgets_init', 'true_news_tab_posts_widgets');

/* Tabed widget */
if ( !class_exists('True_News_Tab_Posts_Widget') ):

    /**
     * Tabbed widget Class.
     *
     * @since 1.0.0
     */
    class True_News_Tab_Posts_Widget extends True_News_Widget_Base {

        /**
         * Sets up a new widget instance.
         *
         * @since 1.0.0
         */
        function __construct() {

            $opts = array(
                'classname'   => 'true_news_widget_tabbed',
                'description' => esc_html__('Tabbed widget.', 'true-news'),
            );
            $fields = array(
                'popular_heading' => array(
                    'label'          => esc_html__('Popular', 'true-news'),
                    'type'           => 'heading',
                ),
                'popular_number' => array(
                    'label'         => esc_html__('No. of Posts:', 'true-news'),
                    'type'          => 'number',
                    'css'           => 'max-width:60px;',
                    'default'       => 5,
                    'min'           => 1,
                    'max'           => 10,
                ),
                'enable_discription' => array(
                    'label'             => esc_html__('Enable Description:', 'true-news'),
                    'type'              => 'checkbox',
                    'default'           => true,
                ),
                'select_image_size' => array(
                    'label' => esc_html__('Select Image Size Featured Post:', 'true-news'),
                    'type' => 'select',
                    'default' => 'medium',
                    'options' => array(
                        'thumbnail' => esc_html__('Thumbnail', 'true-news'),
                        'medium' => esc_html__( 'Medium', 'true-news' ),
                        'large' => esc_html__( 'Large', 'true-news' ),
                        'full' => esc_html__( 'Full', 'true-news' ),
                        ),
                    
                ),
                'excerpt_length' => array(
                    'label'         => esc_html__('Excerpt Length:', 'true-news'),
                    'description'   => esc_html__('Number of words', 'true-news'),
                    'default'       => 10,
                    'css'           => 'max-width:60px;',
                    'min'           => 0,
                    'max'           => 200,
                ),
                'recent_heading' => array(
                    'label'         => esc_html__('Recent', 'true-news'),
                    'type'          => 'heading',
                ),
                'recent_number' => array(
                    'label'        => esc_html__('No. of Posts:', 'true-news'),
                    'type'         => 'number',
                    'css'          => 'max-width:60px;',
                    'default'      => 5,
                    'min'          => 1,
                    'max'          => 10,
                ),
                'comments_heading' => array(
                    'label'           => esc_html__('Comments', 'true-news'),
                    'type'            => 'heading',
                ),
                'comments_number' => array(
                    'label'          => esc_html__('No. of Comments:', 'true-news'),
                    'type'           => 'number',
                    'css'            => 'max-width:60px;',
                    'default'        => 5,
                    'min'            => 1,
                    'max'            => 10,
                ),
            );

            parent::__construct( 'true-news-tabbed', esc_html__( 'IN: Tab Posts Widget', 'true-news' ), $opts, array(), $fields );

        }

        /**
         * Outputs the content for the current widget instance.
         *
         * @since 1.0.0
         *
         * @param array $args Display arguments.
         * @param array $instance Settings for the current widget instance.
         */
        function widget( $args, $instance ) {

            $params = $this->get_params( $instance );
            $tab_id = 'tabbed-'.$this->number;

            echo $args['before_widget'];
            ?>
            <div class="tabbed-container">
                <div class="tab-head">
                     <ul class="twp-nav-tabs clear">
                        <li tab-data="tab-popular" class="tab tab-popular active">
                            <a href="javascript:void(0)">
                                <span class="fire-icon tab-icon">
                                    <svg version="1.1" id="fire-icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" fill="currentcolor" viewBox="0 0 611.999 611.999" style="enable-background:new 0 0 611.999 611.999;" xml:space="preserve">
                                        <g>
                                            <path d="M216.02,611.195c5.978,3.178,12.284-3.704,8.624-9.4c-19.866-30.919-38.678-82.947-8.706-149.952
                                                c49.982-111.737,80.396-169.609,80.396-169.609s16.177,67.536,60.029,127.585c42.205,57.793,65.306,130.478,28.064,191.029
                                                c-3.495,5.683,2.668,12.388,8.607,9.349c46.1-23.582,97.806-70.885,103.64-165.017c2.151-28.764-1.075-69.034-17.206-119.851
                                                c-20.741-64.406-46.239-94.459-60.992-107.365c-4.413-3.861-11.276-0.439-10.914,5.413c4.299,69.494-21.845,87.129-36.726,47.386
                                                c-5.943-15.874-9.409-43.33-9.409-76.766c0-55.665-16.15-112.967-51.755-159.531c-9.259-12.109-20.093-23.424-32.523-33.073
                                                c-4.5-3.494-11.023,0.018-10.611,5.7c2.734,37.736,0.257,145.885-94.624,275.089c-86.029,119.851-52.693,211.896-40.864,236.826
                                                C153.666,566.767,185.212,594.814,216.02,611.195z"/>
                                        </g>
                                    </svg>
                                </span>
                                <?php esc_html_e( 'Popular', 'true-news' );?>
                            </a>
                        </li>
                        <li tab-data="tab-recent" class="tab tab-recent">
                            <a href="javascript:void(0)">
                                <span class="flash-icon tab-icon">
                                    <svg version="1.1" id="flash-icon" xmlns="http://www.w3.org/2000/svg" fill="currentcolor" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 612 612" style="enable-background:new 0 0 612 612;" xml:space="preserve">
                                        <g>
                                            <path d="M286.363,607.148l195.851-325.636c7.954-13.225-1.571-30.069-17.003-30.069H334.436L479.335,30.732
                                                C487.998,17.537,478.532,0,462.748,0H237.426c-8.806,0-16.558,5.804-19.039,14.253l-90.655,308.81
                                                c-3.73,12.706,5.796,25.431,19.039,25.431h176.915l-55.512,251.4C265.75,610.872,280.57,616.781,286.363,607.148z"/>
                                        </g>
                                    </svg>
                                </span>
                                <?php esc_html_e('Recent', 'true-news');?>
                            </a>
                        </li>
                        <li tab-data="tab-comments" class="tab tab-comments">
                            <a href="javascript:void(0)">
                                <span class="comment-icon tab-icon">
                                    <svg version="1.1" id="comment-icon" fill="currentcolor" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="18px" height="18px" viewBox="0 0 511.626 511.626" style="enable-background:new 0 0 511.626 511.626;" xml:space="preserve">
                                        <g>
                                            <path d="M477.371,127.44c-22.843-28.074-53.871-50.249-93.076-66.523c-39.204-16.272-82.035-24.41-128.478-24.41
                                                c-34.643,0-67.762,4.805-99.357,14.417c-31.595,9.611-58.812,22.602-81.653,38.97c-22.845,16.37-41.018,35.832-54.534,58.385
                                                C6.757,170.833,0,194.484,0,219.228c0,28.549,8.61,55.3,25.837,80.234c17.227,24.931,40.778,45.871,70.664,62.811
                                                c-2.096,7.611-4.57,14.846-7.426,21.693c-2.855,6.852-5.424,12.474-7.708,16.851c-2.286,4.377-5.376,9.233-9.281,14.562
                                                c-3.899,5.328-6.849,9.089-8.848,11.275c-1.997,2.19-5.28,5.812-9.851,10.849c-4.565,5.048-7.517,8.329-8.848,9.855
                                                c-0.193,0.089-0.953,0.952-2.285,2.567c-1.331,1.615-1.999,2.423-1.999,2.423l-1.713,2.566c-0.953,1.431-1.381,2.334-1.287,2.707
                                                c0.096,0.373-0.094,1.331-0.57,2.851c-0.477,1.526-0.428,2.669,0.142,3.433v0.284c0.765,3.429,2.43,6.187,4.998,8.277
                                                c2.568,2.092,5.474,2.95,8.708,2.563c12.375-1.522,23.223-3.606,32.548-6.276c49.87-12.758,93.649-35.782,131.334-69.097
                                                c14.272,1.522,28.072,2.286,41.396,2.286c46.442,0,89.271-8.138,128.479-24.417c39.208-16.272,70.233-38.448,93.072-66.517
                                                c22.843-28.062,34.263-58.663,34.263-91.781C511.626,186.108,500.207,155.509,477.371,127.44z"/>
                                        </g>
                                    </svg>
                                </span>
                                <?php esc_html_e('Comments', 'true-news');?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content">
                    <div class="tab-pane content-tab-popular active">
                        <?php $this->render_news( 'popular', $params );?>
                    </div>
                    <div class="tab-pane content-tab-recent">
                        <?php $this->render_news('recent', $params );?>
                    </div>
                    <div class="tab-pane content-tab-comments">
                        <?php $this->render_comments( $params );?>
                    </div>
                </div>
            </div>
            <?php

            echo $args['after_widget'];

        }

        /**
         * Render news.
         *
         * @since 1.0.0
         *
         * @param array $type Type.
         * @param array $params Parameters.
         * @return void
         */
        function render_news($type, $params) {

            if ( !in_array( $type, array('popular', 'recent') ) ) {
                return;
            }

            switch ($type) {
                case 'popular':

                    $cat_slug = '';
                    if( isset( $params['tab_cat'] ) ){
                        $cat_slug = $params['tab_cat'];
                    }

                    $qargs = array(
                        'posts_per_page' => $params['popular_number'],
                        'no_found_rows'  => true,
                        'orderby'        => 'comment_count',
                        'category_name'  => $cat_slug,
                    );

                    break;

                case 'recent':

                    $cat_slug = '';
                    if( isset( $params['tab_cat'] ) ){
                        $cat_slug = $params['tab_cat'];
                    }

                    $qargs = array(
                        'posts_per_page' => $params['recent_number'],
                        'no_found_rows'  => true,
                        'category_name'  => $cat_slug,
                    );

                    break;

                default:
                    break;
            }

            $all_posts = get_posts( $qargs );

            if ( !empty( $all_posts ) ):

                global $post; 
                $true_news_default = true_news_get_default_theme_options();
                $ed_read_later_posts = get_theme_mod( 'ed_read_later_posts',$true_news_default['ed_read_later_posts'] ); ?>
                
                <ul class="twp-widget-list article-tabbed-list">
                    <?php foreach ( $all_posts as $key => $post ): ?>
                        <?php setup_postdata( $post );?>
                        <li>
                            <article class="article-list">
                                <div class="twp-row twp-row-small">

                                     <?php if( has_post_thumbnail() ): ?>
                                        <div class="column column-4">
                                            <div class="article-image">
                                                <?php if( has_post_thumbnail() ){
                                                    true_news_post_thumbnail('true-news-small','',true,$atag = true);
                                                } ?>

                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="column <?php if( !has_post_thumbnail() ){ echo 'column-10'; }else{ echo 'column-6'; } ?>">
                                        <div class="article-body">
                                            <div class="entry-meta entry-meta-1">
                                                <span class="posted-on">
                                                   <?php true_news_entry_meta( $hide_post_author_avatar = 'yes', $hide_post_author= 'yes', $hide_post_date= 'no', $text = false ); ?>
                                                </span>
                                            </div>
                                            <h3 class="entry-title entry-title-small">
                                                <a href="<?php the_permalink();?>">
                                                    <?php the_title();?>
                                                </a>
                                                <?php if( $ed_read_later_posts ){ true_news_add_read_later_post_html( get_the_ID() ); } ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="twp-row twp-row-small">
                                    <div class="column">
                                        <?php if ( true === $params['enable_discription'] ) {?>
                                            <?php if ( absint( $params['excerpt_length'] ) > 0):?>
                                                <div class="post-description">
                                                    <?php echo esc_html( wp_trim_words( get_the_content(),25,'...' ) ); ?>
                                                </div>
                                            <?php endif;?>
                                        <?php }?>
                                    </div>
                                </div>
                            </article>
                        </li>
                    <?php endforeach;?>
                </ul><!-- .news-list -->

                <?php wp_reset_postdata();?>

            <?php endif; 

        }

        /**
         * Render comments.
         *
         * @since 1.0.0
         *
         * @param array $params Parameters.
         * @return void
         */
        function render_comments($params) {

            $cat_slug = '';
            $post_array = array();
            if( !empty( $params['tab_cat'] ) ){

                $cat_slug = $params['tab_cat'];

                $qargs = array(
                    'posts_per_page' => -1,
                    'no_found_rows'  => true,
                    'category_name'  => $cat_slug,
                );

                $all_posts = get_posts( $qargs );

                if ( !empty( $all_posts ) ){

                    global $post;

                    foreach ( $all_posts as $key => $post ){
                        setup_postdata( $post );
                        $post_array[] = get_the_ID();
                    }
                }
            }

            $comment_args = array(
                'number'      => $params['comments_number'],
                'status'      => 'approve',
                'post_status' => 'publish',
                'post__in'  => $post_array,
            );

            $comments = get_comments( $comment_args );
            ?>
            <?php if ( !empty( $comments ) ):?>
                <ul class="twp-widget-list comments-tabbed-list">
                    <?php foreach ( $comments as $key => $comment ):?>
                        <li>
                            <div class="twp-row twp-row-sm">
                                <div class="column column-4">
                                    <figure class="article-thumbmnail">
                                        <?php $comment_author_url = esc_url( get_comment_author_url( $comment ) ); ?>
                                        <?php if ( !empty( $comment_author_url ) ): ?>
                                            <a href="<?php echo esc_url( $comment_author_url ); ?>"><?php echo wp_kses_post( get_avatar( $comment, 130 ) );
                                                ?></a>
                                        <?php  else : ?>
                                            <?php echo wp_kses_post( get_avatar( $comment, 130 ) );?>
                                        <?php endif;?>
                                    </figure>
                                </div>
                                <div class="column column-6">
                                    <div class="comments-content">
                                        <?php echo wp_kses_post( get_comment_author_link( $comment ) ); ?>
                                    </div>
                                    <h3 class="entry-title entry-title-small">
                                        <a href="<?php echo esc_url( get_comment_link( $comment ) ); ?>">
                                            <?php echo esc_html( get_the_title( $comment->comment_post_ID ) );?></a>
                                    </h3>
                                </div>
                            </div>
                        </li>
                    <?php endforeach;?>
                </ul><!-- .comments-list -->
            <?php endif;?>
            <?php
        }

    }
endif;
