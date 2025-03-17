<?php
$enable_instagram = covernews_get_option('footer_show_instagram_post_carousel');

if ($enable_instagram) {
    $username = esc_attr(covernews_get_option('instagram_username'));
    $number = absint(covernews_get_option('number_of_instagram_posts'));
    $thumbnail_size = esc_attr(covernews_get_option('footer_instagram_post_carousel_thumb_size'));
    $instagram_access_token_type = esc_attr(covernews_get_option('instagram_access_token_type'));




    if (!empty($username) && !empty($number)) {

        if($instagram_access_token_type == 'graphapi'){
            $access_token = esc_attr(covernews_get_option('instagram_access_token_graphapi'));
            $media_array = covernews_scrape_instagram_graphapi($username, $access_token, $number);

            if (is_wp_error($media_array)) {
                echo wp_kses_post($media_array->get_error_message());
            } else { ?>

                <div class="section-block section-insta-block clearfix">
                    <div class="insta-slider-wrapper">
                        <div class="insta-feed-head">
                            <a href="//instagram.com/<?php echo esc_attr(trim($username)); ?>" rel="me"
                               class="secondary-color secondary-font" target="_blank">
                                <h3 class="instagram-title"><?php echo esc_html__('Find us on Instagram', 'covernews'); ?></h3>
                                <p class="instagram-username"><?php echo '@'.$username; ?></p>
                            </a>
                        </div>
                        <div class="insta-slider-block">
                            <?php
                            foreach ($media_array as $item) {
                                $media_permalink = $item['original'];
                                if ( substr_count( $media_permalink, '/' ) >= 5 ) {
                                    $media_permalink_array = explode( '/', $media_permalink );
                                    $perm_id = $media_permalink_array[ count( $media_permalink_array ) - 2 ];
                                    $news_media_permalink = 'https://www.instagram.com/p/' . $perm_id . '/';
                                    $media_url = $news_media_permalink . 'media?size=m';
                                }
                                ?>

                                <div class="col-sm-2 insta-item">
                                    <a href="<?php echo esc_url($media_permalink) ?>"  target="_blank"
                                       class="insta-hover">
                                        <figure class="af-insta-height data-bg"
                                                data-background="<?php echo esc_url($media_url) ; ?>">

                                        </figure>


                                        <div class="insta-details">
                                            <div class="insta-tb">
                                                <div class="insta-tc">
                                        <p class="insta-logo"><i class="fab fa-instagram"></i></p>


                                                </div>
                                            </div>

                                        </div>

                                    </a>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
                <?php

            }
        } else {
            $access_token = esc_attr(covernews_get_option('instagram_access_token'));
            $media_array = covernews_scrape_instagram($username, $access_token, $number);
            if (is_wp_error($media_array)) {
                echo wp_kses_post($media_array->get_error_message());
            } else {
                ?>
                <div class="section-block section-insta-block clearfix">
                    <div class="insta-slider-wrapper">
                        <div class="insta-feed-head">
                            <a href="//instagram.com/<?php echo esc_attr(trim($username)); ?>" rel="me"
                               class="secondary-color secondary-font" target="_blank">
                                <h3 class="instagram-title"><?php echo esc_html__('Find us on Instagram', 'covernews'); ?></h3>
                                <p class="instagram-username"><?php echo '@'.$username; ?></p>
                            </a>
                        </div>
                        <div class="insta-slider-block">
                            <?php
                            foreach ($media_array as $item) { ?>

                                <div class="col-sm-2 insta-item zoom-gallery">
                                    <a href="<?php echo esc_url($item['original']) ?>" title="<?php if (isset($item['description']['text']) && !empty($item['description']['text'])){ echo esc_html($item['description']['text']); } ?>" target="_self"
                                       class="insta-hover">
                                        <figure class="af-insta-height data-bg"
                                                data-background="<?php echo esc_url($item['small']) ; ?>">
                                        </figure>
                                        <div class="insta-details">
                                            <div class="insta-tb">
                                                <div class="insta-tc">

                                                    <p class="insta-logo"><i class="fab fa-instagram"></i></p>

                                                </div>
                                            </div>

                                        </div>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
                <?php
            }
        }



    }
}
