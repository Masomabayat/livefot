<?php

/**
 * Front page section additions.
 */


if (!function_exists('newsever_full_width_upper_footer_section')) :
    /**
     *
     * @since Newsever 1.0.0
     *
     * @param null
     * @return null
     *
     */
    function newsever_full_width_upper_footer_section()
    {

        if (1 == newsever_get_option('frontpage_show_latest_posts')) {
            newsever_get_block('latest');
        }

    }
endif;
add_action('newsever_action_full_width_upper_footer_section', 'newsever_full_width_upper_footer_section');
