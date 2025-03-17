<?php
if (!class_exists('Newsever_Tabbed_Posts')) :
    /**
     * Adds Newsever_Tabbed_Posts widget.
     */
    class Newsever_Tabbed_Posts extends AFthemes_Widget_Base
    {
        /**
         * Sets up a new widget instance.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            $this->text_fields = array('newsever-tabbed-popular-posts-title', 'newsever-tabbed-latest-posts-title', 'newsever-tabbed-categorised-posts-title', 'newsever-excerpt-length', 'newsever-posts-number');

            $this->select_fields = array('newsever-show-excerpt', 'newsever-enable-categorised-tab', 'newsever-select-category','newsever-select-background', 'newsever-select-background-type');

            $widget_ops = array(
                'classname' => 'newsever_tabbed_posts_widget aft-widget',
                'description' => __('Displays tabbed posts lists from selected settings.', 'newsever'),
                'customize_selective_refresh' => false,
            );

            parent::__construct('newsever_tabbed_posts', __('AFTN Tabbed Posts', 'newsever'), $widget_ops);
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
            $tab_id = 'tabbed-' . $this->number;


            /** This filter is documented in wp-includes/default-widgets.php */

            $show_excerpt = isset($instance['newsever-show-excerpt']) ? $instance['newsever-show-excerpt'] : 'false';
            $excerpt_length = isset($instance['newsever-excerpt-length']) ? $instance['newsever-excerpt-length'] : '20';
            $number_of_posts = isset($instance['newsever-posts-number']) ? $instance['newsever-posts-number'] : '5';


            $popular_title = isset($instance['newsever-tabbed-popular-posts-title']) ? $instance['newsever-tabbed-popular-posts-title'] : __('AFTN Popular', 'newsever');
            $latest_title = isset($instance['newsever-tabbed-latest-posts-title']) ? $instance['newsever-tabbed-latest-posts-title'] : __('AFTN Latest', 'newsever');


            $enable_categorised_tab = isset($instance['newsever-enable-categorised-tab']) ? $instance['newsever-enable-categorised-tab'] : 'true';
            $categorised_title = isset($instance['newsever-tabbed-categorised-posts-title']) ? $instance['newsever-tabbed-categorised-posts-title'] : __('Trending', 'newsever');
            $category = isset($instance['newsever-select-category']) ? $instance['newsever-select-category'] : '0';
    
            $background = isset($instance['newsever-select-background']) ? $instance['newsever-select-background'] : 'default';

            $background_type = isset($instance['newsever-select-background-type']) ? $instance['newsever-select-background-type'] : 'solid-background';

            $background = $background . ' ' . $background_type;


            if ( $instance['newsever-select-background'] || $instance['newsever-select-background-type']) {
                $args['before_widget']= newsever_update_widget_before($args,$background,'aft-widget');
            }

            // open the widget container
            echo $args['before_widget'];
            ?>
            <div class="tabbed-container">
                <div class="tabbed-head">
                    <ul class="nav nav-tabs af-tabs tab-warpper" role="tablist">
                        <li class="tab tab-recent active">
                            <a href="#<?php echo esc_attr($tab_id); ?>-recent"
                               aria-controls="<?php esc_attr_e('Recent', 'newsever'); ?>" role="tab"
                               data-toggle="tab" class="font-family-1">
                                <i class="fa fa-bolt" aria-hidden="true"></i>  <?php echo esc_html($latest_title); ?>
                            </a>
                        </li>
                        <li role="presentation" class="tab tab-popular">
                            <a href="#<?php echo esc_attr($tab_id); ?>-popular"
                               aria-controls="<?php esc_attr_e('Popular', 'newsever'); ?>" role="tab"
                               data-toggle="tab" class="font-family-1">
                                <i class="fa fa-clock-o" aria-hidden="true"></i>  <?php echo esc_html($popular_title); ?>
                            </a>
                        </li>

                        <?php if ($enable_categorised_tab == 'true'): ?>
                            <li class="tab tab-categorised">
                                <a href="#<?php echo esc_attr($tab_id); ?>-categorised"
                                   aria-controls="<?php esc_attr_e('Categorised', 'newsever'); ?>" role="tab"
                                   data-toggle="tab" class="font-family-1">
                                   <i class="fa fa-fire" aria-hidden="true"></i>  <?php echo esc_html($categorised_title); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="widget-block widget-wrapper">
                <div class="tab-content">
                    <div id="<?php echo esc_attr($tab_id); ?>-recent" role="tabpanel" class="tab-pane active">
                        <?php
                        newsever_render_posts('recent', $show_excerpt, $excerpt_length, 5);
                        ?>
                    </div>
                    <div id="<?php echo esc_attr($tab_id); ?>-popular" role="tabpanel" class="tab-pane">
                        <?php
                        newsever_render_posts('popular', $show_excerpt, $excerpt_length, 5);
                        ?>
                    </div>
                    <?php if ($enable_categorised_tab == 'true'): ?>
                        <div id="<?php echo esc_attr($tab_id); ?>-categorised" role="tabpanel" class="tab-pane">
                            <?php
                            newsever_render_posts('categorised', $show_excerpt, $excerpt_length, 5, $category);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
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
            $enable_categorised_tab = array(
                'true' => __('Yes', 'newsever'),
                'false' => __('No', 'newsever')

            );

            $options = array(
                'false' => __('No', 'newsever'),
                'true' => __('Yes', 'newsever')

            );
    
            $background = array(
                'default' => __('Default', 'newsever'),
                'dim' => __('Dim', 'newsever'),
                'dark' => __('Alternative', 'newsever'),
    
            );




            // generate the text input for the title of the widget. Note that the first parameter matches text_fields array entry
            ?><h4><?php _e('Latest Posts', 'newsever'); ?></h4><?php
            echo parent::newsever_generate_text_input('newsever-tabbed-latest-posts-title', __('Title', 'newsever'), __('Latest', 'newsever'));

            ?><h4><?php _e('Popular Posts', 'newsever'); ?></h4><?php
            echo parent::newsever_generate_text_input('newsever-tabbed-popular-posts-title', __('Title', 'newsever'), __('Popular', 'newsever'));

            $categories = newsever_get_terms();
            if (isset($categories) && !empty($categories)) {
                ?><h4><?php _e('Categorised Posts', 'newsever'); ?></h4>
                <?php
                echo parent::newsever_generate_select_options('newsever-enable-categorised-tab', __('Enable Categorised Tab', 'newsever'), $enable_categorised_tab);
                echo parent::newsever_generate_text_input('newsever-tabbed-categorised-posts-title', __('Title', 'newsever'), __('Trending', 'newsever'));
                echo parent::newsever_generate_select_options('newsever-select-category', __('Select category', 'newsever'), $categories);

            }
            ?><h4><?php _e('Settings for all tabs', 'newsever'); ?></h4><?php

            echo parent::newsever_generate_select_options('newsever-select-background', __('Select Background', 'newsever'), $background);

        }
    }
endif;