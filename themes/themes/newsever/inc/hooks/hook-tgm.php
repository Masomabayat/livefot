<?php
/**
 * Recommended plugins
 *
 * @package Newsever
 */

if ( ! function_exists( 'newsever_recommended_plugins' ) ) :

    /**
     * Recommend plugins.
     *
     * @since 1.0.0
     */
    function newsever_recommended_plugins() {

        $plugins = array(
            array(
                'name'     => esc_html__( 'Blockspare - Beautiful Page Building Blocks for WordPress', 'newsever' ),
                'slug'     => 'blockspare',
                'required' => false,
            ),
            array(
                'name'     => esc_html__( 'WP Post Author', 'newsever' ),
                'slug'     => 'wp-post-author',
                'required' => false,
            ),
            array(
                'name'     => esc_html__( 'One Click Demo Import', 'newsever' ),
                'slug'     => 'one-click-demo-import',
                'required' => false,
            )
        );

        tgmpa( $plugins );

    }

endif;

add_action( 'tgmpa_register', 'newsever_recommended_plugins' );
