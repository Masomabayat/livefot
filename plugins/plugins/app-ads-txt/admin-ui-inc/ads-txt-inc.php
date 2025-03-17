<div class="adtm-row">

<div class="adtm-note" style="margin: 10px 0;">

        <p><?php echo  sprintf( wp_kses( __( 'Once your settings are applied, please check your ads.txt files here. <a href="%s" target="_blank" class="note-btn">Your ads.txt</a>', 'app-ads-txt' ), array(
        'a' => array(
        'href'   => array(),
        'target' => array(),
        'class' => array(),
        ),
        ) ), esc_url( $host_url . '/ads.txt' ) ); ?></p>

    </div>

    <h2><?php echo  esc_html__( 'Add rules for Ads.txt, one line at a time. Then save', 'app-ads-txt' ) ;?></h2>

    <?php if ( isset( $adtm__options['ads-txt'] ) && !empty($adtm__options['ads-txt']) ) { ?>

    <div class="adtm-repeater repeat-section" id="ads_txt_repeater" data-field-id="ads_txt_field_id"
        data-items-index-array="[<?php 
            $count = count($adtm__options['ads-txt']); 
            $i = 0; 
            foreach($adtm__options['ads-txt'] as $key => $value) { 
                if(++$i !== $count) {
                    echo esc_attr($value['number']) . ",";
                } else {
                    echo esc_attr($value['number']);
                }
            }
        ?>]">
        
    <div class="repeat-items">
        <?php foreach($adtm__options['ads-txt'] as $key => $value) { ?>
        <div class="repeat-item" data-field-index="<?php echo esc_attr($value['number']); ?>">
            <table class="adtm-table">
                <tr>
                    <td>
                        <label>Ad Source Domain</label>
                        <div class="adtm-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="adtm-tooltiptext">
                                <?php echo esc_html__('The canonical domain name of the SSP, Exchange, system that bidders connect to (they publishes a document detailing what domain name to use).', 'app-ads-txt'); ?>
                            </span>
                        </div>
                        <input class="repeat-el" type="text" 
                            value="<?php echo esc_attr($value['domain']); ?>" 
                            id="ads_txt_field_id[<?php echo esc_attr($value['number']); ?>][domain]" 
                            name="ads_txt_field_id[<?php echo esc_attr($value['number']); ?>][domain]" 
                            placeholder="Domain of Ads System">
                    </td>
                    <td>
                        <label>Publisher ID</label>
                        <div class="adtm-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="adtm-tooltiptext">
                                <?php echo esc_html__('This must contain the same value used in transactions (i.e. OpenRTB bid requests) in the field specified by the SSP/exchange (For OpenRTB = publisher ID | For OpenDirect = publisher\'s organization ID).', 'app-ads-txt'); ?>
                            </span>
                        </div>
                        <input class="repeat-el" type="text" 
                            value="<?php echo esc_attr($value['pub']); ?>" 
                            id="ads_txt_field_id[<?php echo esc_attr($value['number']); ?>][pub]" 
                            name="ads_txt_field_id[<?php echo esc_attr($value['number']); ?>][pub]" 
                            placeholder="Publisher ID">
                    </td>
                    <td>
                        <label>Type of Relationship</label>
                        <div class="adtm-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="adtm-tooltiptext">
                                <?php echo esc_html__('Your direct demand partners should be listed as "direct." If your partners are using third-party resellers to sell your inventory, such providers should be listed as "reseller." In any case, you should not add any provider to your ads-txt.txt file unless you or your partner have a direct relationship with them.', 'app-ads-txt'); ?>
                            </span>
                        </div>
                        <select class="repeat-el" name="ads_txt_field_id[<?php echo esc_attr($value['number']); ?>][relation]">
                            <option value="" <?php selected(empty($value['relation'])); ?>>Select Relationship</option>
                            <option value="DIRECT" <?php selected($value['relation'], 'DIRECT'); ?>>Direct</option>
                            <option value="RESELLER" <?php selected($value['relation'], 'RESELLER'); ?>>Reseller</option>
                        </select>
                    </td>
                    <td>
                        <label>Ad Source ID</label>
                        <div class="adtm-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="adtm-tooltiptext">
                                <?php echo esc_html__('An ID that uniquely identifies the advertising system within a certification authority.', 'app-ads-txt'); ?>
                            </span>
                        </div>
                        <input class="repeat-el" type="text" 
                            value="<?php echo esc_attr($value['cert']); ?>" 
                            id="ads_txt_field_id[<?php echo esc_attr($value['number']); ?>][cert]" 
                            name="ads_txt_field_id[<?php echo esc_attr($value['number']); ?>][cert]" 
                            placeholder="Ad Source ID">
                    </td>
                </tr>
            </table>
            
            <input type="hidden" class="repeat-el ad-field-number" 
                name="ads_txt_field_id[<?php echo esc_attr($value['number']); ?>][number]" 
                value="<?php echo esc_attr($value['number']); ?>">
        </div>
        <?php } ?>
    </div>
</div>
        
        <?php } else { ?>

        <div class="adtm-repeater repeat-section" id="ads_txt_repeater" data-field-id="ads_txt_field_id" data-items-index-array="[1]">
        
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
                        <input class="repeat-el" type="text" value="" id="ads_txt_field_id[1][domain]" name="ads_txt_field_id[1][domain]" placeholder="Ad Source Domain">
                    </td>
                    <td>
                        <label>Publisher ID</label>
                        <div class="adtm-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="adtm-tooltiptext">
                                <?php echo  __( ' This must contain the same value used in transactions (i.e. OpenRTB bid requests) in the field specified by the SSP/exchange (For OpenRTB = publisher ID | For OpenDirect = publisher’s organization ID).', 'app-ads-txt' ); ?>
                            </span>
                        </div>
                        <input class="repeat-el" type="text" value="" id="ads_txt_field_id[1][pub]" name="ads_txt_field_id[1][pub]" placeholder="Publisher ID">
                    </td>
                    <td>
                        <label>Type of Relationship</label>
                        <div class="adtm-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="adtm-tooltiptext">
                                <?php echo  __( 'Your direct demand partners should be listed as “direct.” If your partners are using third-party resellers to sell your inventory, such providers should be listed as “reseller.” In any case, you should not add any provider to your ads-txt.txt file unless you or your partner have a direct relationship with them.', 'app-ads-txt' ); ?>
                            </span>
                        </div>
                        <select class="repeat-el" name="ads_txt_field_id[1][relation]">
                            <option value="" selected>Select Relationship</option>
                            <option value="DIRECT">Direct</option>
                            <option value="RESELLER">Reseller</option>
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
                        <input class="repeat-el" type="text" value="" id="ads_txt_field_id[1][cert]" name="ads_txt_field_id[1][cert]" placeholder="Ad Source ID">
                    </td>
                </tr></table>

                <input type="hidden" class="repeat-el ad-field-number" name="ads_txt_field_id[1][number]" value="1">
                
            </div>

        </div>

    </div>

    <?php } ?>

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

        <textarea name="ads-txt-custom" rows="4" class="adtm-area" id="adtm-rules"><?php if(isset($adtm__options['ads-txt-custom']) && !empty($adtm__options['ads-txt-custom'])) echo stripslashes($adtm__options['ads-txt-custom']); ?></textarea>

    </div>
</div>