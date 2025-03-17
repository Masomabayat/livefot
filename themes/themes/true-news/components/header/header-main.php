<?php
/**
 * Header Style 1
 *
 *
 * @package True News
 */
?>
<header id="masthead" class="site-header header-affix site-header-1" role="banner">

   <?php do_action('true_news_affix_bar_action'); ?>

    <div class="header-topbar header-border">
        <div class="wrapper">
            <div class="twp-row-flex">
                <div class="topbar-left">
                    <?php do_action('true_news_header_site_branding_action'); ?>
                </div>

                <div class="topbar-right">
                    <div class="topbar-items">
                        <?php do_action('true_news_header_top_nav_action'); ?>
                        <?php do_action('true_news_header_top_bar_item_action'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="header-navigation header-bg header-affix-follow header-affixnav header-border">
        <div class="wrapper">
            <div class="twp-row-flex">

                <?php do_action('true_news_header_nav_action'); ?>

                <div class="nav-right">
                    <div class="navbar-items">
                        <?php true_news_header_content(); ?>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <?php do_action('true_news_action_ticker_section'); ?>
    <?php if( is_front_page() ){ ?><div class="elements-hr"></div> <?php } ?>
</header>
