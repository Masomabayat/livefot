<?php
function true_news_advertise( $true_news_home_section ){
$advertise_image = isset( $true_news_home_section->advertise_image ) ? $true_news_home_section->advertise_image : '' ;
$advertise_link = isset( $true_news_home_section->advertise_link ) ? $true_news_home_section->advertise_link : '' ;
$advertise_script = isset( $true_news_home_section->advertise_script ) ? $true_news_home_section->advertise_script : '' ; ?>

<div class="home-lead-block twp-blocks">
    <div class="wrapper">
        <div class="twp-row">
            <div class="column">
                <?php if( $advertise_script ){
                    echo $advertise_script;
                }else{ 
                    if( $advertise_image ){ ?>
                        <a href="<?php echo esc_url( $advertise_link ); ?>" target="_blank" class="home-lead-link">
                            <img src="<?php echo esc_url( $advertise_image ); ?>" alt="<?php esc_html_e('Advertise Image','true-news'); ?>">
                        </a>
                    <?php
                    }
                } ?>
                
            </div>
        </div>
    </div>
</div>

<?php } ?>