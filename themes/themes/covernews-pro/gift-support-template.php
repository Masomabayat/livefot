<?php
    // Template name: gift support template
    get_header();
?>
<style>
   <?php echo file_get_contents(get_template_directory_uri() . '/assets/css/support-pages.css'); ?>
</style>
<main dir="ltr" class="main-supports-pages">
    <div class="wrapper-supports-pages">
        <section class="tabs-block">
            <ul class="tabs-block__tabs-list">
                <li data-content="support" class="tabs-block__tab tabs-block__tab_active"><?php the_field('tabs-block__support-tab-name'); ?></li>
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
                        <h2 class="tabs-block__support-title"><?php the_field('tabs-block__support-title'); ?></h2>
                            <label class="tabs-block__form-variant-wrapper">
                                <input id="gift-support-form-type" name="gift-support-form-type" readonly type="text" value="Report a bug" class="tabs-block__form-variant-selected-show">
                                <ul class="tabs-block__form-variants">
                                    <li data-form="bug" class="tabs-block__form-variant tabs-block__form-variant_selected">Report a bug</li>
                                    <li data-form="gift" class="tabs-block__form-variant">Request to be sponsor and add your gift</li>
                                    <li data-form="tournament" class="tabs-block__form-variant">Request to sponsor a tournament</li> 
                                </ul>
                            </label>
                            <div data-form="bug" class="tabs-block__form-wrapper tabs-block__form-wrapper_active">
                                <?php get_template_part( 'template-parts/forms/gift-support', 'bug' ); ?> 
                            </div>
                            <div data-form="gift" class="tabs-block__form-wrapper">
                                <?php get_template_part( 'template-parts/forms/gift-support', 'gift' ); ?> 
                            </div>
                            <div data-form="tournament" class="tabs-block__form-wrapper">
                                <?php get_template_part( 'template-parts/forms/gift-support', 'tournament' ); ?> 
                            </div>
                    </div>
                    <div data-content="changeable" class="tabs-block__tab-content tabs-block__tab-content_active">
                        <?php get_template_part( 'template-parts/gift-tabs-template' ); ?>  
                    </div>
                </div>
                <div class="tabs-block__gift-box">
                    <div class="tabs-block__gift-image-wrapper">
                        <img src="<?php the_field('tabs-block__gift-image'); ?>" alt="gift-img">
                    </div>
                    <div class="tabs-block__app-buttons-wrapper">
                        <a class="tabs-block__app-button app-store" href="<?php the_field('button-app-store-link', 'option'); ?>">
                        <div class="tabs-block__app-button-content">
                            <div class="tabs-block__app-button-icon" style="background: center / cover no-repeat url('<?php echo get_template_directory_uri() . '/assets/images/icons/icon__app-store.png'; ?>')"></div>
                            <div class="tabs-block__app-button-text">
                                <p>GET IT ON</p>
                                <h3>App Store</h3>
                            </div>
                        </div>
                        </a>
                        <a class="tabs-block__app-button play-market" href="<?php the_field('button-play-market-link', 'option'); ?>">
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