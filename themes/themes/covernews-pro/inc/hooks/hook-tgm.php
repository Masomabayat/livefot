<?php
/**
 * Recommended plugins
 *
 * @package CoverNews
 */

if ( ! function_exists( 'covernews_recommended_plugins' ) ) :

    /**
     * Recommend plugins.
     *
     * @since 1.0.0
     */
    function covernews_recommended_plugins() {

        $plugins = array(

            array(
                'name'     => esc_html__( 'AF Companion', 'covernews' ),
                'slug'     => 'af-companion',
                'required' => false,
            ),
            array(
                'name'     => esc_html__( 'Blockspare - Beautiful Page Building Gutenberg Blocks for WordPress', 'covernews' ),
                'slug'     => 'blockspare',
                'required' => false,
            ),
            array(
                'name'     => esc_html__( 'Latest Posts Block Lite', 'covernews' ),
                'slug'     => 'latest-posts-block-lite',
                'required' => false,
            ),
            array(
                'name'     => esc_html__( 'Magic Content Box Lite', 'covernews' ),
                'slug'     => 'magic-content-box-lite',
                'required' => false,
            ),

            array(
                'name'     => esc_html__( 'WP Post Author', 'covernews' ),
                'slug'     => 'wp-post-author',
                'required' => false,
            ),
            array(
                'name'     => __( 'MailChimp for WordPress', 'covernews' ),
                'slug'     => 'mailchimp-for-wp',
                'required' => false,
            ),
            array(
                'name'     => esc_html__( 'Smush Image Compression and Optimization', 'covernews' ),
                'slug'     => 'wp-smushit',
                'required' => false,
            ),
            array(
                'name'     => esc_html__( 'Regenerate Thumbnails', 'covernews' ),
                'slug'     => 'regenerate-thumbnails',
                'required' => false,
            ),
        );

        tgmpa( $plugins );

    }

endif;

add_action( 'tgmpa_register', 'covernews_recommended_plugins' );
