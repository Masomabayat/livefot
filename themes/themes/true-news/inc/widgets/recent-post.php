<?php
/**
 * Recent Post Widgets.
 *
 * @package True News
 */


if ( !function_exists('true_news_recent_post_widgets') ) :

    /**
     * Load widgets.
     *
     * @since 1.0.0
     */
    function true_news_recent_post_widgets(){
        // Recent Post widget.
        register_widget('True_News_Sidebar_Recent_Post_Widget');

    }

endif;
add_action('widgets_init', 'true_news_recent_post_widgets');

// Recent Post widget
if ( !class_exists('True_News_Sidebar_Recent_Post_Widget') ) :

    /**
     * Recent Post.
     *
     * @since 1.0.0
     */
    class True_News_Sidebar_Recent_Post_Widget extends True_News_Widget_Base
    {

        /**
         * Sets up a new widget instance.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            $opts = array(
                'classname' => 'true_news_popular_post_widget',
                'description' => esc_html__('Displays post form selected category specific for popular post in sidebars.', 'true-news'),
                'customize_selective_refresh' => true,
            );
            $fields = array(
                'title' => array(
                    'label' => esc_html__('Title:', 'true-news'),
                    'type' => 'text',
                    'class' => 'widefat',
                ),
                'post_category' => array(
                    'label' => esc_html__('Select Category:', 'true-news'),
                    'type' => 'dropdown-taxonomies',
                    'show_option_all' => esc_html__('All Categories', 'true-news'),
                ),
                'enable_counter' => array(
                    'label' => esc_html__('Enable Counter:', 'true-news'),
                    'type' => 'checkbox',
                    'default' => true,
                ),
                'post_number' => array(
                    'label' => esc_html__('Number of Posts:', 'true-news'),
                    'type' => 'number',
                    'default' => 4,
                    'css' => 'max-width:60px;',
                    'min' => 1,
                    'max' => 9,
                ),
            );

            parent::__construct( 'true-news-popular-sidebar-layout', esc_html__('IN: Recent Post Widget', 'true-news'), $opts, array(), $fields );
        }

        /**
         * Outputs the content for the current widget instance.
         *
         * @since 1.0.0
         *
         * @param array $args Display arguments.
         * @param array $instance Settings for the current widget instance.
         */
        function widget( $args, $instance )
        {

            $params = $this->get_params( $instance );

            echo $args['before_widget'];

            if ( !empty( $params['title'] ) ) {
                echo $args['before_title'] . $params['title'] . $args['after_title'];
            }

            $qargs = array(
                'posts_per_page' => esc_attr( $params['post_number'] ),
                'no_found_rows' => true,
            );
            if ( absint( $params['post_category'] ) > 0 ) {
                $qargs['category'] = absint($params['post_category']);
            }
            $all_posts = get_posts( $qargs );
            ?>
            <?php global $post; 
            $count = 1;
            ?>
            <?php if ( !empty( $all_posts ) ) : ?>
            <div class="twp-recent-widget">                
                <ul class="twp-widget-list recent-widget-list">
                <?php foreach ($all_posts as $key => $post) : ?>
                    <?php setup_postdata($post); ?>
                    <li>
                        <article class="article-list">
                            <div class="twp-row twp-row-xs">
                                <div class="column column-four">
                                    <div class="article-image">
                                        <?php if ( has_post_thumbnail() ) {
                                            $thumb = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'medium' );
                                        } ?>

                                        <a href="<?php the_permalink(); ?>" class="data-bg data-bg-small" data-background="<?php echo esc_url( $thumb[0] ); ?>"></a>

                                        <?php if ( true === $params['enable_counter'] ) { ?>
                                            <div class="trend-item">
                                                <span class="number"> <?php echo $count; ?></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="column column-six">
                                    <div class="article-body">
                                        <div class="entry-meta">
                                            <span class="posted-on">
                                                <?php the_time('j M Y'); ?>
                                            </span>
                                        </div>
                                        <h3 class="entry-title entry-title-medium">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php the_title(); ?>
                                            </a>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </li>
                <?php 
                $count++;
                endforeach; ?>
                </ul>
            </div>

            <?php wp_reset_postdata(); ?>

        <?php endif; ?>
            <?php echo $args['after_widget'];
        }
    }
endif;