<?php
    if (!function_exists('newsever_front_page_widgets_section')) :
        /**
         *
         * @param null
         * @return null
         *
         * @since Newsever 1.0.0
         *
         */
        function newsever_front_page_widgets_section()
        {
            ?>
            <!-- Main Content section -->
            <?php
            $frontpage_layout = newsever_get_option('frontpage_content_alignment');
            
            
            if (is_active_sidebar('home-content-widgets') && is_active_sidebar('home-sidebar-1-widgets') && is_active_sidebar('home-sidebar-2-widgets')) {
                ?>
                <section class="section-block-upper 1">
                    <div class="af-three-col-layout af-container-block-wrapper">
                        <?php if ($frontpage_layout != 'frontpage-layout-3') { ?>

                            <div id="primary" class="content-area ">

                                <main id="main" class="site-main">
                                    <?php dynamic_sidebar('home-content-widgets'); ?>
                                </main>
                            </div>
                            
                            
                            <?php
                            $sticky_sidebar_class = '';
                            $sticky_sidebar = newsever_get_option('frontpage_sticky_sidebar');
                            if ($sticky_sidebar) {
                                $sticky_sidebar_class = 'aft-sticky-sidebar';
                            }
                            ?>
                            <div id="secondary" class="sidebar-area <?php echo esc_attr($sticky_sidebar_class); ?>">
                                <div class="theiaStickySidebar">
                                    <aside class="widget-area color-pad">
                                        <?php dynamic_sidebar('home-sidebar-1-widgets'); ?>
                                    </aside>
                                </div>
                            </div>
                            
                            
                            <?php
                            $sticky_sidebar_class = '';
                            $sticky_sidebar = newsever_get_option('frontpage_sticky_sidebar');
                            if ($sticky_sidebar) {
                                $sticky_sidebar_class = 'aft-sticky-sidebar';
                            }
                            ?>
                            <div id="tertiary" class="sidebar-area <?php echo esc_attr($sticky_sidebar_class); ?>">
                                <div class="theiaStickySidebar">
                                    <aside class="widget-area color-pad">
                                        <?php dynamic_sidebar('home-sidebar-2-widgets'); ?>
                                    </aside>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div id="primary" class="content-area ">

                                <main id="main" class="site-main">
                                    <?php dynamic_sidebar('home-content-widgets'); ?>
                                </main>
                            </div>
                        
                        <?php } ?>
                    </div>
                </section>
            <?php } elseif (is_active_sidebar('home-content-widgets') && is_active_sidebar('home-sidebar-1-widgets')) {
                ?>
                <section class="section-block-upper 2">
                    <div class="af-three-col-layout af-container-block-wrapper">
                        
                        <?php if (is_active_sidebar('home-sidebar-1-widgets') && $frontpage_layout != 'frontpage-layout-3') { ?>
                            <div id="primary" class="content-area ">

                                <main id="main" class="site-main">
                                    <?php dynamic_sidebar('home-content-widgets'); ?>
                                </main>
                            </div>
                            <?php
                            $sticky_sidebar_class = '';
                            $sticky_sidebar = newsever_get_option('frontpage_sticky_sidebar');
                            if ($sticky_sidebar) {
                                $sticky_sidebar_class = 'aft-sticky-sidebar';
                            }
                            ?>
                            <div id="secondary" class="sidebar-area <?php echo esc_attr($sticky_sidebar_class); ?>">
                                <div class="theiaStickySidebar">
                                    <aside class="widget-area color-pad">
                                        
                                        <?php dynamic_sidebar('home-sidebar-1-widgets'); ?>
                                    </aside>
                                </div>
                            </div>
                        
                        
                        <?php } else {
                            ?>

                            <div id="primary" class="content-area ">

                                <main id="main" class="site-main">
                                    <?php dynamic_sidebar('home-content-widgets'); ?>
                                </main>
                            </div>
                            <?php
                        } ?>


                    </div>
                </section>
                <?php
                
            } elseif (is_active_sidebar('home-content-widgets') && is_active_sidebar('home-sidebar-2-widgets')) {
                ?>
                <section class="section-block-upper 3">
                    <div class="af-three-col-layout af-container-block-wrapper">
                        
                        <?php if (is_active_sidebar('home-sidebar-2-widgets') && $frontpage_layout != 'frontpage-layout-3') { ?>
                            <div id="primary" class="content-area ">

                                <main id="main" class="site-main">
                                    <?php dynamic_sidebar('home-content-widgets'); ?>
                                </main>
                            </div>
                            <?php
                            $sticky_sidebar_class = '';
                            $sticky_sidebar = newsever_get_option('frontpage_sticky_sidebar');
                            if ($sticky_sidebar) {
                                $sticky_sidebar_class = 'aft-sticky-sidebar';
                            }
                            ?>
                            <div id="tertiary" class="sidebar-area <?php echo esc_attr($sticky_sidebar_class); ?>">
                                <div class="theiaStickySidebar">
                                    <aside class="widget-area color-pad">
                                        
                                        <?php dynamic_sidebar('home-sidebar-2-widgets'); ?>
                                    </aside>
                                </div>
                            </div>
                        
                        
                        <?php } else {
                            ?>

                            <div id="primary" class="content-area ">

                                <main id="main" class="site-main">
                                    <?php dynamic_sidebar('home-content-widgets'); ?>
                                </main>
                            </div>
                            <?php
                        } ?>


                    </div>
                </section>
                <?php
                
            } else {
                if (is_active_sidebar('home-content-widgets')) {
                ?>
                <section class="section-block-upper 4">
                    <div class="af-three-col-layout af-container-block-wrapper">
                        <div id="primary" class="content-area ">
                            <main id="main" class="site-main">
                                <?php dynamic_sidebar('home-content-widgets'); ?>
                            </main>
                        </div>
                    </div>
                </section>
            <?php }
            }
        }
    endif;
    add_action('newsever_front_page_section', 'newsever_front_page_widgets_section', 50);