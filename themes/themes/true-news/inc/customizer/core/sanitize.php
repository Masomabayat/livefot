<?php
/**
 * Sanitization functions.
 *
 * @package True News
 */

if (!function_exists('true_news_sanitize_select')) :

    /**
     * Sanitize select.
     */
    function true_news_sanitize_select($input, $setting)
    {

        // Ensure input is a slug.
        $input = sanitize_text_field($input);

        // Get list of choices from the control associated with the setting.
        $choices = $setting->manager->get_control($setting->id)->choices;

        // If the input is a valid key, return it; otherwise, return the default.
        return (array_key_exists($input, $choices) ? $input : $setting->default);

    }

endif;


if (!function_exists('true_news_sanitize_checkbox')) :

    /**
     * Sanitize checkbox.
     */
    function true_news_sanitize_checkbox($checked)
    {

        return ((isset($checked) && true === $checked) ? true : false);

    }

endif;


if (!function_exists('true_news_sanitize_positive_integer')) :

    /**
     * Sanitize positive integer.
     */
    function true_news_sanitize_positive_integer($input, $setting)
    {

        $input = absint($input);

        // If the input is an absolute integer, return it.
        // otherwise, return the default.
        return ($input ? $input : $setting->default);

    }

endif;

if (!function_exists('true_news_sanitize_dropdown_pages')) :

    /**
     * Sanitize dropdown pages.
     */
    function true_news_sanitize_dropdown_pages($page_id, $setting)
    {

        // Ensure $input is an absolute integer.
        $page_id = absint($page_id);

        // If $page_id is an ID of a published page, return it; otherwise, return the default.
        return ('publish' === get_post_status($page_id) ? $page_id : $setting->default);

    }

endif;

if ( ! function_exists( 'true_news_sanitize_repeater' ) ) :
    
    /**
    * Sanitise Repeater Field
    */
    function true_news_sanitize_repeater($input){
        
        $input_decoded = json_decode( $input, true );
        
        if(!empty($input_decoded)) {

            foreach ($input_decoded as $boxes => $box ){

                foreach ($box as $key => $value){

                    if( $key == 'section_ed' 
                        || $key == 'slider_arrows' 
                        || $key == 'slider_dots' 
                        || $key == 'slider_autoplay' 
                        || $key == 'hide_post_date' 
                        || $key == 'hide_post_author' 
                        || $key == 'hide_post_category' 
                        || $key == 'hide_trending_posts' 
                        || $key == 'hide_popular_posts' 
                        || $key == 'enable_rtl' 
                        || $key == 'section_ed_full_width' ){

                        $input_decoded[$boxes][$key] = true_news_sanitize_repeater_ed( $value );

                    }elseif( $key == 'home_section_type' ){

                        $input_decoded[$boxes][$key] = true_news_sanitize_home_sections( $value );

                    }elseif( $key == 'section_bg_color' ){

                        $input_decoded[$boxes][$key] = sanitize_hex_color( $value );

                    }elseif( $key == 'pagination' ){

                        $input_decoded[$boxes][$key] =  true_news_sanitize_pagination_type( $value );

                    }elseif( $key == 'sidebar_layout' ){

                        $input_decoded[$boxes][$key] =  true_news_sanitize_sidebar_type( $value );

                    }elseif( $key == 'advertise_script' ){

                        $input_decoded[$boxes][$key] =   $value;

                    }elseif( $key == 'post_category' || 
                             $key == 'post_category_1' || 
                             $key == 'post_category_2' || 
                             $key == 'post_category_3' ){

                        $input_decoded[$boxes][$key] =  true_news_sanitize_category( $value );

                    }else{

                        $input_decoded[$boxes][$key] = sanitize_text_field( $value );

                    }
                    
                }

            }
           
            return json_encode($input_decoded);

        }    

        return $input;
    }
endif;


/** Sanitize Enable Disable Checkbox **/
function true_news_sanitize_repeater_ed( $input ) {

    $valid_keys = array('yes','no');
    if ( in_array( $input , $valid_keys ) ) {
        return $input;
    }
    return '';

}

/** Sanitize Home Sections **/
function true_news_sanitize_home_sections( $input ) {

    $home_sections = true_news_home_sections();
    if ( array_key_exists( $input , $home_sections ) ) {
        return $input;
    }
    return '';

}

/** Sanitize Pagination Type **/
function true_news_sanitize_pagination_type( $input ) {

    $home_sections = true_news_pagination_type();
    if ( array_key_exists( $input , $home_sections ) ) {
        return $input;
    }
    return '';

}

/** Sanitize Sidebar Type **/
function true_news_sanitize_sidebar_type( $input ) {

    $sidebars = array(
                    'global-sidebar' => esc_html__('Global Sidebar','true-news'),
                    'right-sidebar' => esc_html__('Content & Right Sidebar','true-news'),
                    'left-sidebar' => esc_html__('Left Sidebar & Content','true-news'),
                    'both-sidebar' => esc_html__('Left Sidebar, Content & Right Sidebar','true-news'),
                    'content-left-right' => esc_html__('Content, Left Sidebar & Right Sidebar','true-news'),
                    'left-right-content' => esc_html__('Left Sidebar, Right Sidebar & Content','true-news'),
                    'no-sidebar' => esc_html__('No Sidebar','true-news'),
                );
    if ( array_key_exists( $input , $sidebars ) ) {
        return $input;
    }
    return '';

}

/** Sanitize Category **/
function true_news_sanitize_category( $input ) {

   $true_news_post_category_list = true_news_post_category_list();
    if ( array_key_exists( $input , $true_news_post_category_list ) ) {
        return $input;
    }
    return '';

}