<?php
add_action('wp_enqueue_scripts','true_news_dynamic_style',100);
function true_news_dynamic_style(){


    $true_news_dynamic_style = '';
    $true_news_default = true_news_get_default_theme_options();
    $twp_general_text_color = get_theme_mod( 'twp_general_text_color',$true_news_default['twp_general_text_color'] );
    $twp_general_heading_text_color = get_theme_mod( 'twp_general_heading_text_color',$true_news_default['twp_general_heading_text_color'] );
    $twp_general_heading_link_color = get_theme_mod( 'twp_general_heading_link_color',$true_news_default['twp_general_heading_link_color'] );

    if( $twp_general_text_color ){
        $true_news_dynamic_style .= "body, input, select, textarea{color:".esc_html( $twp_general_text_color )."}";
                
    }

    if( $twp_general_heading_link_color ){
        $true_news_dynamic_style .= "a, a:visited{color:".esc_html( $twp_general_heading_link_color )."}";

    }
    
    if( $twp_general_heading_text_color ){
        $true_news_dynamic_style .= "h1, h2, h3, h4, h5, h6, .entry-title a, .twp-latest-tab ul li .twp-latest-filter{color:".esc_html( $twp_general_heading_text_color )."}";
                
    }


    
    wp_add_inline_style( 'true-news-style', $true_news_dynamic_style );
}