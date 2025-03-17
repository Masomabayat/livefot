<?php
if (!function_exists('covernews_page_layout_blocks')) :
    /**
     *
     * @since CoverNews 1.0.0
     *
     * @param null
     * @return null
     *
     */
    function covernews_page_layout_blocks($archive_layout = 'full')
    {

        $archive_layout = covernews_get_option('archive_layout');

        switch ($archive_layout) {
            case "archive-layout-grid":
                covernews_get_block('grid');
                break;
            case "archive-layout-grid-2":
                covernews_get_block('grid-2');
                break;
            case "archive-layout-list":
                covernews_get_block('lists');
                break;
            case "archive-layout-full":
                covernews_get_block('full');;
                break;
            case "archive-layout-full-2":
                covernews_get_block('full-2');;
                break;
            default:
                covernews_get_block('full');;
        }
    }
endif;
