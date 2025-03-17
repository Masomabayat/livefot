<?php
/**
 * Social Link Widgets.
 *
 * @package True News
 */
if ( !function_exists('true_news_social_link_widget') ) :
    /**
     * Load widgets.
     *
     * @since 1.0.0
     */
    function true_news_social_link_widget(){
        // Social Link Widget.
        register_widget('True_News_Social_Link_widget');
    }
endif;
add_action('widgets_init', 'true_news_social_link_widget');
/*Social widget*/
if ( !class_exists( 'True_News_Social_Link_widget' ))  :
    /**
     * Social widget Class.
     *
     * @since 1.0.0
     */
    class True_News_Social_Link_widget extends True_News_Widget_Base
    {
        /**
         * Sets up a new widget instance.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            $opts = array(
                'classname' => 'true_news_social_widget',
                'description' => esc_html__('Displays Social share.', 'true-news'),
                'customize_selective_refresh' => true,
            );
            $fields = array(
                'title' => array(
                    'label' => esc_html__('Title:', 'true-news'),
                    'type' => 'text',
                    'class' => 'widefat',
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
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-pt' => array(
                   'label' => esc_html__('Pinterest URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-rt' => array(
                   'label' => esc_html__('Reddit URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-sk' => array(
                   'label' => esc_html__('Skype URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-sc' => array(
                   'label' => esc_html__('Snapchat URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-tr' => array(
                   'label' => esc_html__('Tumblr URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-th' => array(
                   'label' => esc_html__('Twitch URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-yt' => array(
                   'label' => esc_html__('Youtube URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-vo' => array(
                   'label' => esc_html__('Vimeo URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-wa' => array(
                   'label' => esc_html__('Whatsapp URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-wp' => array(
                   'label' => esc_html__('WordPress URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-gh' => array(
                   'label' => esc_html__('Github URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-fs' => array(
                   'label' => esc_html__('FourSquare URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-db' => array(
                   'label' => esc_html__('Dribbble URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
                'url-vk' => array(
                   'label' => esc_html__('VK URL:', 'true-news'),
                   'type' => 'url',
                   'class' => 'widefat',
                    ),
            );
            parent::__construct( 'true-news-social-layout', esc_html__('BP: Social Widget', 'true-news'), $opts, array(), $fields );
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
            if ( ! empty( $params['title'] ) ) {
                echo $args['before_title'] . esc_html( $params['title'] ) . $args['after_title'];
            } ?>
            <div class="twp-social-widget">
                <ul class="social-widget-wrapper">
                    <?php if ( !empty( $params['url-fb'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-fb']); ?>" target="_blank"><i class="ion ion-logo-facebook"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-tw'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-tw']); ?>" target="_blank"><i class="ion ion-logo-twitter"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-lt'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-lt']); ?>" target="_blank"><i class="ion ion-logo-linkedin"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-ig'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-ig']); ?>" target="_blank"><i class="ion ion-logo-instagram"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-pt'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-pt']); ?>" target="_blank"><i class="ion ion-logo-pinterest"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-rt'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-rt']); ?>" target="_blank"><i class="ion ion-logo-reddit"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-sk'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-sk']); ?>" target="_blank"><i class="ion ion-logo-skype"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-sc'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-sc']); ?>" target="_blank"><i class="ion ion-logo-snapchat"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-tr'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-tr']); ?>" target="_blank"><i class="ion ion-logo-tumblr"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-th'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-th']); ?>" target="_blank"><i class="ion ion-logo-twitch"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-yt'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-yt']); ?>" target="_blank"><i class="ion ion-logo-youtube"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-vo'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-vo']); ?>" target="_blank"><i class="ion ion-logo-vimeo"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-wa'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-wa']); ?>" target="_blank"><i class="ion ion-logo-whatsapp"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-wp'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-wp']); ?>" target="_blank"><i class="ion ion-logo-wordpress"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-gh'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-gh']); ?>" target="_blank"><i class="ion ion-logo-github"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-fs'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-fs']); ?>" target="_blank"><i class="ion ion-logo-foursquare"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-db'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-db']); ?>" target="_blank"><i class="ion ion-logo-dribbble"></i></a>
                        </li>
                    <?php } ?>
                    <?php if ( !empty( $params['url-vk'] ) ) { ?>
                        <li>
                            <a href="<?php echo esc_url($params['url-vk']); ?>" target="_blank"><i class="ion ion-logo-vk"></i></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <?php echo $args['after_widget'];
        }
    }
endif;
