<?php
if (!class_exists('Newsever_Posts_Express_List')) :
    /**
     * Adds Newsever_Posts_Express_List widget.
     */
    class Newsever_Posts_Express_List extends AFthemes_Widget_Base
    {
        /**
         * Sets up a new widget instance.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            $this->text_fields = array('newsever-categorised-posts-title', 'newsever-excerpt-length', 'newsever-posts-number');
            $this->select_fields = array('newsever-select-category', 'newsever-show-excerpt','newsever-select-background', 'newsever-select-background-type');

            $widget_ops = array(
                'classname' => 'newsever_posts_express_list list-layout aft-widget',
                'description' => __('Displays posts from selected category in an express list.', 'newsever'),
                'customize_selective_refresh' => false,
            );

            parent::__construct('newsever_posts_express_list', __('AFTN Posts Express List', 'newsever'), $widget_ops);
        }

        /**
         * Front-end display of widget.
         *
         * @see WP_Widget::widget()
         *
         * @param array $args Widget arguments.
         * @param array $instance Saved values from database.
         */

        public function widget($args, $instance)
        {

            $instance = parent::newsever_sanitize_data($instance, $instance);


            /** This filter is documented in wp-includes/default-widgets.php */
            $title = apply_filters('widget_title', $instance['newsever-categorised-posts-title'], $instance, $this->id_base);
            $background = isset($instance['newsever-select-background']) ? $instance['newsever-select-background'] : 'default';
            $background_type = isset($instance['newsever-select-background-type']) ? $instance['newsever-select-background-type'] : 'solid-background';

            $background = $background . ' ' . $background_type;


            $category = isset($instance['newsever-select-category']) ? $instance['newsever-select-category'] : '0';

    
            if ( $instance['newsever-select-background']|| $instance['newsever-select-background-type']) {
                $args['before_widget']= newsever_update_widget_before($args,$background,'aft-widget');
            }
            
            // open the widget container
            echo $args['before_widget'];
            ?>
            <?php if (!empty($title)): ?>
            <div class="em-title-subtitle-wrap">
                <?php if (!empty($title)): ?>
                    <h4 class="widget-title header-after1">
                        <span class="header-after">
                            <?php echo esc_html($title); ?>
                            </span>
                    </h4>
                <?php endif; ?>

            </div>
        <?php endif; ?>
            <?php
            $all_posts = newsever_get_posts(5, $category);
            ?>
            <div class="widget-block widget-wrapper">
                <div class="af-container-row clearfix">
                    <?php
                    $count = 1;
                    if ($all_posts->have_posts()) :
                        while ($all_posts->have_posts()) : $all_posts->the_post();
                            global $post;
                            if ($count == 1) {
                                $thumbnail_size = 'newsever-medium';

                            } else {
                                $thumbnail_size = 'thumbnail';
                            }
                            $url = newsever_get_freatured_image_url($post->ID, $thumbnail_size);

                            ?>

                            <?php if ($count == 1): ?>
                                <div class="col-2 pad float-l grid-part af-sec-post">
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
                            <?php else: ?>
                                <div class="col-2 pad float-l list-part af-sec-post">
                                    <div class="af-double-column list-style clearfix">
                                        <div class="read-single color-pad">
                                            <div class="data-bg read-img pos-rel col-4 float-l read-bg-img"
                                                 data-background="<?php echo esc_url($url); ?>">
                                                <img src="<?php echo esc_url($url); ?>">

                                                <a href="<?php the_permalink(); ?>"></a>
                                            </div>
                                            <div class="read-details col-75 float-l pad color-tp-pad">
                                                <div class="read-categories">

                                                    <?php newsever_post_categories(); ?>
                                                </div>
                                                <div class="read-title">
                                                    <h4>
                                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                    </h4>
                                                </div>
                                                <div class="entry-meta">
                                                    <?php newsever_get_comments_count($post->ID); ?>
                                                    <?php newsever_post_item_meta(); ?>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php
                            $count++;
                        endwhile;
                    endif;
                    wp_reset_postdata();
                    ?>


                </div>
            </div>

            <?php
            // close the widget container
            echo $args['after_widget'];
        }

        /**
         * Back-end widget form.
         *
         * @see WP_Widget::form()
         *
         * @param array $instance Previously saved values from database.
         */
        public function form($instance)
        {
            $this->form_instance = $instance;

    
            $background = array(
                'default' => __('Default', 'newsever'),
                'dim' => __('Dim', 'newsever'),
                'dark' => __('Alternative', 'newsever'),
                'secondary-background' => __('Secondary Color', 'newsever'),
    
            );


            $categories = newsever_get_terms();

            if (isset($categories) && !empty($categories)) {
                // generate the text input for the title of the widget. Note that the first parameter matches text_fields array entry
                echo parent::newsever_generate_text_input('newsever-categorised-posts-title', __('Title', 'newsever'), __('Posts Express List', 'newsever'));
                echo parent::newsever_generate_select_options('newsever-select-category', __('Select category', 'newsever'), $categories);
                echo parent::newsever_generate_select_options('newsever-select-background', __('Select Background', 'newsever'), $background);
            }

            //print_pre($terms);


        }

    }
endif;