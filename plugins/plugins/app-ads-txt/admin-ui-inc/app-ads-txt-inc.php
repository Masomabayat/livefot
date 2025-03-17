<div class="adtm-row">

    <div class="adtm-note" style="margin: 10px 0;">
    
        <p><?php echo  sprintf( wp_kses( __( 'Once your settings are applied, please check your app-ads.txt files here. <a href="%s" target="_blank" class="note-btn">Your app-ads.txt</a>', 'app-ads-txt' ), array(
        'a' => array(
        'href'   => array(),
        'target' => array(),
        'class' => array(),
        ),
        ) ), esc_url( $host_url . '/app-ads.txt' ) ); ?></p>

    </div>

    <h2><?php echo  esc_html__( 'Add rules for App-Ads.txt, one line at a time. Then save', 'app-ads-txt' ) ;?></h2>

        <div class="adtm-alert adtm-info">
            <span class="closebtn">&times;</span> 
            <?php echo "App-Ads.txt is a premium feature. " . $get_pro . " " . __( 'App-Ads.txt', 'app-ads-txt' ); ?>
        </div>

        <div class="adtm-repeater repeat-section" id="app_ads_repeater" data-field-id="sample_txt_field_id" data-items-index-array="[1]">
        
        <div class="repeat-items">
            
            <div class="repeat-item" data-field-index="1">

                <table class="adtm-table"><tr>
                    <td>
                        <label>Ad Source Domain</label>
                        <div class="adtm-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="adtm-tooltiptext">
                                <?php echo  __( 'The canonical domain name of the SSP, Exchange, system that bidders connect to (they publishes a document detailing what domain name to use).', 'app-ads-txt' ); ?>
                            </span>
                        </div>
                        <input class="repeat-el adtm-disabled" type="text" value="" id="sample_txt_field_id[1][domain]" name="sample_txt_field_id[1][domain]" placeholder="Ad Source Domain" disabled>
                    </td>
                    <td>
                        <label>Publisher ID</label>
                        <div class="adtm-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="adtm-tooltiptext">
                                <?php echo  __( ' This must contain the same value used in transactions (i.e. OpenRTB bid requests) in the field specified by the SSP/exchange (For OpenRTB = publisher ID | For OpenDirect = publisher’s organization ID).', 'app-ads-txt' ); ?>
                            </span>
                        </div>
                        <input class="repeat-el adtm-disabled" type="text" value="" id="sample_txt_field_id[1][pub]" name="sample_txt_field_id[1][pub]" placeholder="Publisher ID" disabled>
                    </td>
                    <td>
                        <label>Type of Relationship</label>
                        <div class="adtm-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="adtm-tooltiptext">
                                <?php echo  __( 'Your direct demand partners should be listed as “direct.” If your partners are using third-party resellers to sell your inventory, such providers should be listed as “reseller.” In any case, you should not add any provider to your ads-txt.txt file unless you or your partner have a direct relationship with them.', 'app-ads-txt' ); ?>
                            </span>
                        </div>
                        <select class="repeat-el adtm-disabled" name="sample_txt_field_id[1][relation]">
                            <option value="" selected>Select Relationship</option>
                            <option value="DIRECT" disabled>Direct</option>
                            <option value="RESELLER" disabled>Reseller</option>
                        </select>
                    </td>
                    <td>
                        <label>Ad Source ID</label>
                        <div class="adtm-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="adtm-tooltiptext">
                                <?php echo  __( 'An ID that uniquely identifies the advertising system within a certification authority.', 'app-ads-txt' ); ?>
                            </span>
                        </div>
                        <input class="repeat-el adtm-disabled" type="text" value="" id="sample_txt_field_id[1][cert]" name="sample_txt_field_id[1][cert]" placeholder="Ad Source ID" disabled>
                    </td>
                </tr></table>

                <input type="hidden" class="repeat-el ad-field-number" name="sample_txt_field_id[1][number]" value="1">
                
            </div>

        </div>

    </div>

</div>

<br>

<div class="adtm-row">
    <div class="adtm-block">

        <label>(for expert) Copy-paste all rules if you already have them ready to use. Then save.</label>

        <div class="adtm-tooltip">
            <span class="dashicons dashicons-editor-help"></span>
            <span class="adtm-tooltiptext">
                <?php echo  __( 'Add more custom rules if you need them, otherwise, leave it blank.', 'app-ads-txt' ); ?>
            </span>
        </div>
        
        <textarea name="sample-txt-custom" rows="4" class="adtm-area adtm-disabled" id="adtm-rules" disabled></textarea>

    </div>
</div>