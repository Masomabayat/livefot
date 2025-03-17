<?php
    // Template name: application support template
    get_header();
?>
<style>
    <?php echo file_get_contents(get_template_directory_uri() . '/assets/css/support-pages.css'); ?>
</style>
<main dir="ltr" class="main-supports-pages">
    <div class="wrapper-supports-pages">
        <section class="tabs-block">
            <ul class="tabs-block__tabs-list">
                <li data-content="support" class="tabs-block__tab tabs-block__tab_active"><?php echo get_field('tabs-block__support-tab-name'); ?></li>
                <?php 
                    $tabsList = get_field('tabs-block__tabs-list');
                
                    foreach($tabsList as $key => $items) {
                        $name = $items['tabs-block__tab-name'];
                ?>
                    <li data-content="changeable" data-tab="<?php echo $key; ?>" class="tabs-block__tab tabs-block__tab_changeable"><?php echo $name; ?></li>
                <?php
                    }
                ?>
            </ul>
            <div class="tabs-block__tabs-content">
                <div class="tabs-block__tab-content-wrapper">
                    <div data-content="support" class="tabs-block__tab-content tabs-block__tab-content_active">
                        <h2 class="tabs-block__support-title"><?php echo get_field('tabs-block__support-title'); ?></h2>
                        <div>
                            <label class="tabs-block__form-variant-wrapper">
                                <input class="tabs-block__form-variant-selected-show app-support-form-type" name="app-support-form-type" readonly type="text" value="Feedback and bug reporting">
                                <ul class="tabs-block__form-variants">
                                    <li data-form="bug" class="tabs-block__form-variant tabs-block__form-variant_selected">Feedback and bug reporting</li>
                                    <li data-form="social" class="tabs-block__form-variant">Social media links</li>
                                    <li data-form="league" class="tabs-block__form-variant">Request to add new league</li> 
                                    <li data-form="features" class="tabs-block__form-variant">Request to add new features</li> 
                                </ul>
                            </label>
                            <div data-form="bug" class="tabs-block__form-wrapper tabs-block__form-wrapper_active">
                                <?php get_template_part( 'template-parts/forms/application-support', 'bug' ); ?> 
                            </div>
                            <div data-form="social" class="tabs-block__form-wrapper">
                                <?php get_template_part( 'template-parts/forms/application-support', 'social' ); ?> 
                            </div>
                            <div data-form="league" class="tabs-block__form-wrapper">
                                <?php get_template_part( 'template-parts/forms/application-support', 'league' ); ?> 
                            </div>
                            <div data-form="features" class="tabs-block__form-wrapper">
                                <?php get_template_part( 'template-parts/forms/application-support', 'features' ); ?> 
                            </div>
                        </div>
                    </div>
                    <div data-content="changeable" class="tabs-block__tab-content tabs-block__tab-content_active">
                        <?php get_template_part( 'template-parts/application-tabs-template' ); ?>  
                    </div>
                </div>
                <div class="tabs-block__gift-box">
                    <div class="tabs-block__gift-image-wrapper">
                        <img src="<?php echo get_field('tabs-block__right-image'); ?>" alt="gift-img">
                    </div>
                    <div class="tabs-block__app-buttons-wrapper">
                        <a class="tabs-block__app-button app-store" href="<?php echo get_field('button-app-store-link', 'option'); ?>">
                        <div class="tabs-block__app-button-content">
                            <div class="tabs-block__app-button-icon" style="background: center / cover no-repeat url('<?php echo get_template_directory_uri() . '/assets/images/icons/icon__app-store.png'; ?>')"></div>
                            <div class="tabs-block__app-button-text">
                                <p>GET IT ON</p>
                                <h3>App Store</h3>
                            </div>
                        </div>
                        </a>
                        <a class="tabs-block__app-button play-market" href="<?php echo get_field('button-play-market-link', 'option'); ?>">
                        <div class="tabs-block__app-button-content">
                            <div class="tabs-block__app-button-icon" style="background: center / cover no-repeat url('<?php echo get_template_directory_uri() . '/assets/images/icons/icon__play-market.png'; ?>')"></div>
                            <div class="tabs-block__app-button-text">
                                <p>GET IT ON</p>
                                <h3>Google Play</h3>
                            </div>
                        </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
    get_footer();
?>