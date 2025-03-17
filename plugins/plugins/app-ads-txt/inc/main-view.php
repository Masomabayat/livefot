<div class="wrap adtm-containter">

        <h2><span class="dashicons dashicons-media-text" style="margin-top: 6px; font-size: 24px;"></span> Ads.txt & App-Ads.txt Manager <?php 
echo esc_html__( 'Settings', 'app-ads-txt' );
?></h2>

    <h2 class="nav-tab-wrapper">

        <a href="<?php 
echo esc_url( '?page=adtm&tab=adtm-settings' );
?>" 
        class="nav-tab <?php 
echo ( $active_tab == 'adtm-settings' ? 'nav-tab-active' : '' );
?>">Settings</a>
        
        <a href="<?php 
echo esc_url( '?page=adtm&tab=adtm-faq' );
?>" 
        class="nav-tab <?php 
echo ( $active_tab == 'adtm-faq' ? 'nav-tab-active' : '' );
?>">FAQ</a>
        
        <a href="<?php 
echo esc_url( '?page=adtm&tab=adtm-recs' );
?>" 
        class="nav-tab <?php 
echo ( $active_tab == 'adtm-recs' ? 'nav-tab-active' : '' );
?>">Recommendations</a>

    </h2>

    <?php 
if ( $active_tab == 'adtm-settings' ) {
    ?>

    <!-- Start Settings -->
    <div class="adtm-row">
        
        <!-- Start Main Settings Column -->
        <div class="adtm-column col-9">

            <div class="adtm-main">

                <form method="post">
                    
                    <?php 
    if ( function_exists( 'wp_nonce_field' ) ) {
        wp_nonce_field( 'adtm__settings' );
    }
    ?>

        <div class="adtm-tabs">

            <ul class="tabs">
                <li class="tab-link current" data-tab="tab-1">Ads.txt Settings</li>
                <li class="tab-link" data-tab="tab-2">App-Ads.txt Settings</li>
            </ul>

            <!-- START App-Ads.txt TAB -->
            <div id="tab-1" class="tab-content current">     
                
                <?php 
    include_once plugin_basename( __DIR__ ) . '/admin-ui-inc/ads-txt-inc.php';
    ?>

            </div>
            <!-- END App-Ads.txt Tab -->

            <!-- START Ads.txt TAB -->
            <div id="tab-2" class="tab-content">

                <?php 
    include_once dirname( __FILE__ ) . '/admin-ui-inc/app-ads-txt-inc.php';
    ?>

            </div>
            <!-- END Ads.txt TAB -->

        </div><!-- container --> 
                
        <div class="adtm-alert adtm-warning">
            <span class="closebtn">&times;</span>
            <?php 
    echo __( 'Warning: make sure to clear your cache after saving changes.', 'app-ads-txt' );
    ?>
        </div>

        <hr>

        <div class="adtm-row">

            <div class="adtm-column col-3">
                
                <span class="adtm-label">
                    <?php 
    echo esc_html__( 'Delete Settings', 'app-ads-txt' );
    ?></span>
                </div>

            <div class="adtm-column col-9">
                
                <label class="adtm-switch">

                    <input type="checkbox" id="remove_settings" name="remove_settings" value="remove_settings" <?php 
    if ( isset( $adtm__options['remove_settings'] ) && !empty( $adtm__options['remove_settings'] ) ) {
        echo 'checked="checked"';
    }
    ?> />

                    <span class="adtm-slider adtm-round"></span>

                </label>
                &nbsp;
                <span><?php 
    echo esc_html__( 'Checking this box will remove ALL settings (app-ads.txt & ads.txt) when you deactivate plugin.', 'app-ads-txt' );
    ?></span>
                
            </div>

        </div>
       
        <p class="submit"><input type="submit" name="update" class="button-primary" value="<?php 
    echo esc_html__( 'Save Changes', 'app-ads-txt' );
    ?>" /></p>

        </form>

                                    
        <div class="adtm-note" style="margin-top: 5px;">

            <p><?php 
    echo esc_html__( 'Note: app-ads.txt & ads.txt Manager creates virtual app-ads.txt & ads.txt files. Please make sure that your permalinks are enabled and there is no physical app-ads.txt & ads.txt file on your server. If you\'re using any kind of cache then make sure to clear it after Saving Changes. Please read FAQ for more details.', 'app-ads-txt' );
    ?></p>
            <p><?php 
    echo esc_html__( 'If app-ads.txt is still not working. Please try to save Settings >', 'app-ads-txt' );
    ?> <a href="options-permalink.php"><?php 
    echo __( 'Permalinks', 'app-ads-txt' );
    ?></a>
            &nbsp; <?php 
    echo sprintf( wp_kses( __( '(Check <a href="%s" target="_blank">Screenshot</a>)', 'add-tiktok-advertising-pixel' ), array(
        'a' => array(
            'href'   => array(),
            'target' => array(),
        ),
    ) ), esc_url( plugin_dir_url( __FILE__ ) . '/assets/imgs/permalinks.png' ) );
    ?></p>

        </div>

        <div class="adtm-alert adtm-success">
            <span class="closebtn">&times;</span>
            <?php 
    echo __( 'Make sure search engines can crawl your .txt files. Optimize your robots.txt with', 'app-ads-txt' );
    ?> <a href="https://wordpress.org/plugins/better-robots-txt/">Better Robots.txt</a>
        </div>
        
        <br />
        
        <div class="adtm-row">
            <label>Common app-ads.txt snippet:</label>
            <div class="adtm-tooltip">
                <span class="dashicons dashicons-editor-help"></span>
                <span class="adtm-tooltiptext">
                    <?php 
    echo __( 'Just a sample for copy-paste purpose. Make sure to change itaccordingaly inside custom rules box.', 'app-ads-txt' );
    ?>
                </span>
            </div>
            <textarea rows="4" class="adtm-area" id="adtm-snippet" disabled>
#Google Adsense
google.com, pub-PUBID, DIRECT, uFt9u99PqH2rBF7R2vGdjCVdBgu

#Bing Ads
bing.com, pubID, DIRECT, TkP7rKhxx8GRJq3hDkvNWbakuP6

#Outbrain
outbrain.com, pubID, DIRECT
            </textarea>
        </div>
                
        <?php 
    include dirname( __FILE__ ) . '/inc/seo-recommendations.php';
    ?>
    
    </div> <!-- End adtm-main -->
    
</div> <!-- End main settings adtm-column col-8 -->

    <?php 
    // Sidebar
    include dirname( __FILE__ ) . '/sidebar.php';
}
if ( $active_tab == 'adtm-ads-txt' ) {
    include dirname( __FILE__ ) . '../ads-txt-admin.php';
}
if ( $active_tab == 'adtm-faq' ) {
    include dirname( __FILE__ ) . '/faq.php';
}
if ( $active_tab == 'adtm-recs' ) {
    include dirname( __FILE__ ) . '/recommendations.php';
}
?>

</div>