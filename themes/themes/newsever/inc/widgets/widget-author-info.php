<?php
if (!class_exists('Newsever_author_info')) :
    /**
     * Adds Newsever_author_info widget.
     */
    class Newsever_author_info extends AFthemes_Widget_Base
    {
        /**
         * Sets up a new widget instance.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            $this->text_fields = array('newsever-author-info-title', 'newsever-author-info-subtitle', 'newsever-author-info-image', 'newsever-author-info-name', 'newsever-author-info-desc', 'newsever-author-info-phone', 'newsever-author-info-email');
            $this->url_fields = array('newsever-author-info-facebook', 'newsever-author-info-twitter', 'newsever-author-info-linkedin', 'newsever-author-info-instagram', 'newsever-author-info-vk', 'newsever-author-info-youtube');

            $this->select_fields = array( 'newsever-select-background', 'newsever-select-background-type');

            $widget_ops = array(
                'classname' => 'newsever_author_info_widget aft-widget',
                'description' => __('Displays author info.', 'newsever'),
                'customize_selective_refresh' => false,
            );

            parent::__construct('newsever_author_info', __('AFTN Author Info', 'newsever'), $widget_ops);
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
            
            $title = apply_filters('widget_title', $instance['newsever-author-info-title'], $instance, $this->id_base);
            $background = isset($instance['newsever-select-background']) ? $instance['newsever-select-background'] : 'default';

            $background_type = isset($instance['newsever-select-background-type']) ? $instance['newsever-select-background-type'] : 'solid-background';

            $background = $background . ' ' . $background_type;

            $profile_image = isset($instance['newsever-author-info-image']) ? ($instance['newsever-author-info-image']) : '';

            if ($profile_image) {
                $image_attributes = wp_get_attachment_image_src($profile_image, 'large');
                $image_src = $image_attributes[0];
                $image_class = 'data-bg data-bg-hover';

            } else {
                $image_src = '';
                $image_class = 'no-bg';
            }

            $name = isset($instance['newsever-author-info-name']) ? ($instance['newsever-author-info-name']) : '';

            $desc = isset($instance['newsever-author-info-desc']) ? ($instance['newsever-author-info-desc']) : '';
            $facebook = isset($instance['newsever-author-info-facebook']) ? ($instance['newsever-author-info-facebook']) : '';
            $twitter = isset($instance['newsever-author-info-twitter']) ? ($instance['newsever-author-info-twitter']) : '';
            $linkedin = isset($instance['newsever-author-info-linkedin']) ? ($instance['newsever-author-info-linkedin']) : '';
            $youtube = isset($instance['newsever-author-info-youtube']) ? ($instance['newsever-author-info-youtube']) : '';
            $instagram = isset($instance['newsever-author-info-instagram']) ? ($instance['newsever-author-info-instagram']) : '';
            $vk = isset($instance['newsever-author-info-vk']) ? ($instance['newsever-author-info-vk']) : '';
    
            if ( $instance['newsever-select-background'] || $instance['newsever-select-background-type']) {
                $args['before_widget']= newsever_update_widget_before($args,$background,'aft-widget');
            }
            echo $args['before_widget'];
            ?>
            <section class="products">
                <div class="container-wrapper">
                    <?php if (!empty($title)): ?>
                        <div class="section-head">
                            <?php if (!empty($title)): ?>
                                <h4 class="widget-title header-after1">
                                    <span class="header-after">
                                        <?php echo esc_html($title); ?>
                                    </span>
                                </h4>
                            <?php endif; ?>


                        </div>

                    <?php endif; ?>
                    <div class="widget-block widget-wrapper">
                    <div class="posts-author-wrapper">

                        <?php if (!empty($image_src)) : ?>


                            <figure class="data-bg read-img pos-rel read-bg-img af-author-img <?php echo esc_attr($image_class); ?>"
                                    data-background="<?php echo esc_url($image_src); ?>">
                                <img src="<?php echo esc_attr($image_src); ?>" alt=""/>
                            </figure>

                        <?php endif; ?>
                        <div class="af-author-details">
                            <?php if (!empty($name)) : ?>
                                <h4 class="af-author-display-name"><?php echo esc_html($name); ?></h4>
                            <?php endif; ?>
                            <?php if (!empty($desc)) : ?>
                                <p class="af-author-display-name"><?php echo esc_html($desc); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($facebook) || !empty($twitter) || !empty($linkedin) || !empty($youtube) || !empty($instagram) || !empty($vk)) : ?>
                                <div class="social-navigation aft-small-social-menu">
                                    <ul>
                                        <?php if (!empty($facebook)) : ?>
                                            <li>
                                                <a href="<?php echo esc_url($facebook); ?>" target="_blank"></a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (!empty($instagram)) : ?>
                                            <li>
                                                <a href="<?php echo esc_url($instagram); ?>" target="_blank"></a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (!empty($youtube)) : ?>
                                            <li>
                                                <a href="<?php echo esc_url($youtube); ?>" target="_blank"></a>
                                            </li>
                                        <?php endif; ?>



                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    </div>
                </div>
            </section>
            <?php
            //print_pre($all_posts);
            // close the widget container
            echo $args['after_widget'];

            //$instance = parent::newsever_sanitize_data( $instance, $instance );


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
            $categories = newsever_get_terms();
            $background = array(
                'default' => __('Default', 'newsever'),
                'dim' => __('Dim', 'newsever'),
                'dark' => __('Alternative', 'newsever'),
    
            );



            if (isset($categories) && !empty($categories)) {
                // generate the text input for the title of the widget. Note that the first parameter matches text_fields array entry
                echo parent::newsever_generate_text_input('newsever-author-info-title', __('About Author', 'newsever'), __('Title', 'newsever'));

                echo parent::newsever_generate_image_upload('newsever-author-info-image', __('Profile image', 'newsever'), __('Profile image', 'newsever'));
                echo parent::newsever_generate_text_input('newsever-author-info-name', __('Name', 'newsever'), __('Name', 'newsever'));
                echo parent::newsever_generate_text_input('newsever-author-info-desc', __('Descriptions', 'newsever'), '');
                echo parent::newsever_generate_text_input('newsever-author-info-facebook', __('Facebook', 'newsever'), '');
                echo parent::newsever_generate_text_input('newsever-author-info-instagram', __('Instagram', 'newsever'), '');
                echo parent::newsever_generate_text_input('newsever-author-info-youtube', __('Youtube', 'newsever'), '');
                echo parent::newsever_generate_select_options('newsever-select-background', __('Select Background', 'newsever'), $background);



            }
        }
    }
endif;