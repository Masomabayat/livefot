<?php
/**
 * Author Widgets.
 *
 * @package True News
 */
if ( !function_exists('true_news_author_widgets') ) :
    /**
     * Load widgets.
     *
     * @since 1.0.0
     */
    function true_news_author_widgets(){
        // Auther widget.
        register_widget('True_News_Author_widget');
    }
endif;
add_action('widgets_init', 'true_news_author_widgets');
/*Video widget*/
if ( !class_exists('True_News_Author_widget') ) :
    /**
     * Author widget Class.
     *
     * @since 1.0.0
     */
    class True_News_Author_widget extends True_News_Widget_Base
    {
        /**
         * Sets up a new widget instance.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            $opts = array(
                'classname' => 'true_news_author_widget',
                'description' => esc_html__('Displays authors details in post.', 'true-news'),
                'customize_selective_refresh' => true,
            );
            $fields = array(
                'title' => array(
                    'label' => esc_html__('Title:', 'true-news'),
                    'type' => 'text',
                    'class' => 'widefat',
                ),
                'image_bg_url' => array(
                    'label' => esc_html__('Widget Background Image:', 'true-news'),
                    'type'  => 'image',
                ),
                'author-name' => array(
                    'label' => esc_html__('Name:', 'true-news'),
                    'type' => 'text',
                    'class' => 'widefat',
                ),
                'description' => array(
                    'label' => esc_html__('Description:', 'true-news'),
                    'type'  => 'textarea',
                    'class' => 'widget-content widefat'
                ),
                'image_url' => array(
                    'label' => esc_html__('Author Image:', 'true-news'),
                    'type'  => 'image',
                ),
                'url-fb' => array(
                   'label' => esc_html__('Facebook URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-tw' => array(
                   'label' => esc_html__('Twitter URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-lt' => array(
                   'label' => esc_html__('Linkedin URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-ig' => array(
                   'label' => esc_html__('Instagram URL:', 'true-news'),
                   'type' => 'esc_html__',
                   'class' => 'widefat',
                    ),
            );
            parent::__construct( 'true-news-author-layout', esc_html__('BP: Author Widget', 'true-news'), $opts, array(), $fields );
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
            $params = $this->get_params($instance);
            echo $args['before_widget'];
            if ( ! empty( $params['title'] ) ) {
                echo $args['before_title'] . esc_html( $params['title'] ) . $args['after_title'];
            } ?>
            <div class="widget-content author-info <?php if( $params['image_bg_url'] ){ echo "author-bg-enable"; } ?>">
                <?php if ( ! empty( $params['image_bg_url'] ) ) { ?>
                    <div class="author-background data-bg data-bg-small" data-background="<?php echo esc_url( $params['image_bg_url'] ); ?>">
                    </div>
                <?php } ?>
                <div class="author-avatar">
                    <?php if ( ! empty( $params['image_url'] ) ) { ?>
                        <div class="profile-image data-bg" data-background="<?php echo esc_url( $params['image_url'] ); ?>">
                    </div>
                    <?php } ?>
                </div>
                <div class="author-details">
                    <?php if ( ! empty( $params['author-name'] ) ) { ?>
                        <h3 class="author-name"><?php echo esc_html( $params['author-name'] );?></h3>
                    <?php } ?>
                    <?php if ( ! empty( $params['description'] ) ) { ?>
                        <div class="author-bio"><?php echo wp_kses_post( $params['description']); ?></div>
                    <?php } ?>
                </div>
                <div class="author-social">
                    <?php if ( ! empty( $params['url-fb'] ) ) { ?>
                        <a href="<?php echo esc_url( $params['url-fb'] ); ?>" target="_blank"><i class="ion ion-logo-facebook"></i></a>
                    <?php } ?>
                    <?php if ( ! empty( $params['url-tw'] ) ) { ?>
                        <a href="<?php echo esc_url( $params['url-tw'] ); ?>" target="_blank"><i class="ion ion-logo-twitter"></i></a>
                    <?php } ?>
                    <?php if ( ! empty( $params['url-lt'] ) ) { ?>
                        <a href="<?php echo esc_url( $params['url-lt'] ); ?>" target="_blank"><i class="ion ion-logo-linkedin"></i></a>
                    <?php } ?>
                    <?php if ( ! empty( $params['url-ig'] ) ) { ?>
                        <a href="<?php echo esc_url( $params['url-ig'] ); ?>" target="_blank"><i class="ion ion-logo-instagram"></i></a>
                    <?php } ?>
                </div>
            </div>
            <?php echo $args['after_widget'];
        }
    }
endif;