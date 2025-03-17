<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package True News
 */

get_header(); ?>
    <div class="block-elements block-elements-errorpage">
        <div class="wrapper">
            <div class="twp-row">
                <div class="column">
                    <main id="main" class="site-main" role="main">
                        <section class="error-404 not-found">
                            <header class="page-header">
                                <h1 class="page-title"><i class="ion ion-ios-warning"></i> <?php esc_html_e('Oops! That page can&rsquo;t be found.', 'true-news'); ?></h1>
                            </header>
                            <div class="page-content">
                                <p><?php esc_html_e('It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'true-news'); ?></p>
                            </div>
                            
                            <?php get_search_form(); ?>
                            
                        </section>
                    </main>
                </div>
            </div>
        </div>
    </div>
<?php
get_footer();
