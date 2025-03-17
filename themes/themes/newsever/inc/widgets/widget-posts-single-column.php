<?php
if (!class_exists('Newsever_Single_Col_Categorised_Posts')) :
    /**
     * Adds Newsever_Single_Col_Categorised_Posts widget.
     */
    class Newsever_Single_Col_Categorised_Posts extends AFthemes_Widget_Base
    {
        /**
         * Sets up a new widget instance.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            $this->text_fields = array('newsever-categorised-posts-title', 'newsever-posts-number', 'newsever-excerpt-length');
            $this->select_fields = array('newsever-select-category', 'newsever-show-excerpt','newsever-select-background', 'newsever-select-background-type');

            $widget_ops = array(
                'classname' => 'newsever_single_col_categorised_posts aft-widget',
                'description' => __('Displays posts from selected category in single column.', 'newsever'),
                'customize_selective_refresh' => false,
            );

            parent::__construct('newsever_single_col_categorised_posts', __('AFTN Single Column ', 'newsever'), $widget_ops);
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
            $category = isset($instance['newsever-select-category']) ? $instance['newsever-select-category'] : '0';
            $background = isset($instance['newsever-select-background']) ? $instance['newsever-select-background'] : 'default';

            $background_type = isset($instance['newsever-select-background-type']) ? $instance['newsever-select-background-type'] : 'solid-background';

            $background = $background . ' ' . $background_type;


            if ( $instance['newsever-select-background']) {
                $args['before_widget']= newsever_update_widget_before($args,$background,'aft-widget');
            }
            // open the widget container
            echo $args['before_widget'];
            ?>
            <?php if (!empty($title) || !empty($subtitle)): ?>
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
            <div class="widget-block list-style clearfix">
                <?php
                if ($all_posts->have_posts()) :
                    while ($all_posts->have_posts()) : $all_posts->the_post();
                        global $post;

                        $url = newsever_get_freatured_image_url($post->ID, 'newsever-medium');

                        ?>

                        <div class="read-single color-pad">
                            <div class="data-bg read-img pos-rel col-2 float-l read-bg-img af-sec-list-img"
                                 data-background="<?php echo esc_url($url); ?>">
                                <?php if (!empty($url)): ?>
                                    <img src="<?php echo esc_url($url); ?>">
                                <?php endif; ?>
                                <div class="min-read-post-format">
                                    <?php echo newsever_post_format($post->ID); ?>
                                    <span class="min-read-item">
                                                <?php newsever_count_content_words($post->ID); ?>
                                            </span>
                                </div>

                                <a href="<?php the_permalink(); ?>"></a>
                            </div>
                            <div class="read-details col-2 float-l pad af-sec-list-txt color-tp-pad">
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
                    <?php
                    endwhile;
                    ?>
                <?php endif;
                wp_reset_postdata(); ?>
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

            );




            $categories = newsever_get_terms();

            if (isset($categories) && !empty($categories)) {
                // generate the text input for the title of the widget. Note that the first parameter matches text_fields array entry
                echo parent::newsever_generate_text_input('newsever-categorised-posts-title', 'Title', 'Single Column Posts');
                echo parent::newsever_generate_select_options('newsever-select-category', __('Select category', 'newsever'), $categories);
                echo parent::newsever_generate_select_options('newsever-select-background', __('Select Background', 'newsever'), $background);


            }

            //print_pre($terms);


        }

    }
endif;