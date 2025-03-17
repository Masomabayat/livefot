<?php
if (!function_exists('newsever_banner_advertisement')):
    /**
     * Ticker Slider
     *
     * @since Newsever 1.0.0
     *
     */
    function newsever_banner_advertisement()
    {

        if (('' != newsever_get_option('banner_advertisement_section')) ) { ?>
            <div class="banner-promotions-wrapper">
                <?php if (('' != newsever_get_option('banner_advertisement_section'))):

                    $newsever_banner_advertisement = newsever_get_option('banner_advertisement_section');
                    $newsever_banner_advertisement = absint($newsever_banner_advertisement);
                    $newsever_banner_advertisement = wp_get_attachment_image($newsever_banner_advertisement, 'full');
                    $newsever_banner_advertisement_url = newsever_get_option('banner_advertisement_section_url');
                    $newsever_banner_advertisement_url = isset($newsever_banner_advertisement_url) ? esc_url($newsever_banner_advertisement_url) : '#';
                    $newsever_open_on_new_tab = newsever_get_option('banner_advertisement_open_on_new_tab');
                    $newsever_open_on_new_tab = ('' != $newsever_open_on_new_tab) ? '_blank' : '';

                    ?>
                    <div class="promotion-section">
                        <a href="<?php echo esc_url($newsever_banner_advertisement_url); ?>" target="<?php echo esc_attr($newsever_open_on_new_tab); ?>">
                            <?php echo $newsever_banner_advertisement; ?>
                        </a>
                    </div>
                <?php endif; ?>                

            </div>
            <!-- Trending line END -->
            <?php
        }


    }
endif;

add_action('newsever_action_banner_advertisement', 'newsever_banner_advertisement', 10);