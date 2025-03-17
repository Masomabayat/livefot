<?php

function adtm_notice_rate()
{
    if ( !PAnD::is_admin_notice_active( 'adtm-rating-120' ) ) {
        return;
    }
    ?>
    
            <div data-dismissible="adtm-rating-30" class="notice adtm-notice notice-success is-dismissible">
                <p class="adtm-p"><?php 
    $rating_url = "https://wordpress.org/support/plugin/app-ads-txt/reviews/?rate=5#new-post";
    $show_support = sprintf( wp_kses( __( 'Show support for App-ads.txt & Ads.txt Manager with a 5-star rating » <a href="%s" target="_blank">Click here</a>', 'app-ads-txt' ), array(
        'a' => array(
        'href'   => array(),
        'target' => array(),
    ),
    ) ), esc_url( $rating_url ) );
    echo  $show_support ;
    ?></p>
            </div>
    <?php 
}

add_action( 'admin_init', array( 'PAnD', 'init' ) );
//add_action( 'admin_notices', 'adtm_notice_subscribe' );
add_action( 'admin_notices', 'adtm_notice_rate' );
// end free only