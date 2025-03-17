<?php
// Template name: mobile support page
    get_header();
?>
<style>
    <?php echo file_get_contents(get_template_directory_uri() . '/assets/css/support-pages.css'); ?>

    main {
    	min-height: 100vh !important;
    }
    .tabs-block__tab-content.tabs-block__tab-content_active {
    	min-height: 300px;
    }

    .header-layout-1, .site-footer {
    	display: none;
    }
    .main-supports-pages .tabs-block__tab-content-wrapper {
    	box-shadow: none;
    	padding-top: 40px;
    }
    .main-supports-pages .tabs-block__tabs-content {
    	padding: 0;
    	border: none;
    	border-radius: 0;
    	box-shadow: none;
    	background: transparent;
    }
    .main-supports-pages .tabs-block__tab-content-wrapper {
    	width: 100%
    }
    @media only screen and (max-width: 767px) {
        .main-supports-pages .tabs-block__form-select {
            grid-column: span 2 !important;
        }
        #scroll-up {
            display: none !important;
        }
    }
</style>
<main dir="ltr" class="main-supports-pages">
    <div class="wrapper-supports-pages">
        <section class="tabs-block">
            <div class="tabs-block__tabs-content">
                <div class="tabs-block__tab-content-wrapper">
                    <div data-content="support" class="tabs-block__tab-content tabs-block__tab-content_active">
                        <div>
                            <label class="tabs-block__form-variant-wrapper">
                                <input class="tabs-block__form-variant-selected-show app-support-form-type" name="app-support-form-type" readonly type="text" value="Advertise on LiveFot">
                                <ul class="tabs-block__form-variants">
                                    <!-- <li data-form="social" class="tabs-block__form-variant">Social media links</li> -->
                                    <li data-form="features" class="tabs-block__form-variant tabs-block__form-variant_selected">Advertise on LiveFot</li> 
                                    <li data-form="league" class="tabs-block__form-variant">Request to add new league</li> 
                                    <li data-form="bug" class="tabs-block__form-variant">Feedback and bug reporting</li>
                                </ul>
                            </label>
                            <div data-form="bug" class="tabs-block__form-wrapper">
                                <?php /*echo do_shortcode('[contact-form-7 id="7bfc0bc" title="bug form"]');*/ ?>
                                <?php get_template_part( 'template-parts/forms/application-support', 'bug' ); ?> 
                            </div>
                            <div data-form="social" class="tabs-block__form-wrapper">
                            	<?php /*echo do_shortcode('[contact-form-7 id="a50e876" title="social form"]');*/ ?>
                                <?php get_template_part( 'template-parts/forms/application-support', 'social' ); ?> 
                            </div>
                            <div data-form="league" class="tabs-block__form-wrapper">
                                <?php get_template_part( 'template-parts/forms/application-support', 'league' ); ?> 
                            </div>
                            <div data-form="features" class="tabs-block__form-wrapper tabs-block__form-wrapper_active">
                                <?php get_template_part( 'template-parts/forms/application-support', 'features' ); ?> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
    get_footer();
?>