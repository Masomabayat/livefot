<?php
/**
* Widget Functions.
*
* @package True News
*/
/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function true_news_widgets_widget()
{   
    $true_news_default = true_news_get_default_theme_options();
    
    register_sidebar( array(
        'name'          => esc_html__( 'Right Sidebar', 'true-news' ),
        'id'            => 'sidebar-1',
        'description'   => '',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Left Sidebar', 'true-news' ),
        'id'            => 'sidebar-2',
        'description'   => '',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
        'name'          => esc_html__( 'Off Canvas sidebar 1', 'true-news' ),
        'id'            => 'off-canvas-1',
        'description'   => '',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );
    register_sidebar( array(
        'name'          => esc_html__( 'Off Canvas sidebar 2', 'true-news' ),
        'id'            => 'off-canvas-2',
        'description'   => '',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );
    
    register_sidebar( array(
        'name'          => esc_html__( 'Off Canvas sidebar 3', 'true-news' ),
        'id'            => 'off-canvas-3',
        'description'   => '',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );

    $footer_column_layout = absint( get_theme_mod( 'footer_column_layout',$true_news_default['footer_column_layout'] ) );
    for( $i = 0; $i < $footer_column_layout; $i++ ){
    	if( $i == 0 ){ $count = esc_html__('One','true-news'); }
    	if( $i == 1 ){ $count = esc_html__('Two','true-news'); }
    	if( $i == 2 ){ $count = esc_html__('Three','true-news'); }
        if( $i == 3 ){ $count = esc_html__('Four','true-news'); }
	    register_sidebar( array(
	        'name' => esc_html__('Footer Widget ', 'true-news').$count,
	        'id' => 'true-news-footer-widget-'.$i,
	        'description' => esc_html__('Add widgets here.', 'true-news'),
	        'before_widget' => '<div id="%1$s" class="widget %2$s">',
	        'after_widget' => '</div>',
	        'before_title' => '<h2 class="widget-title">',
	        'after_title' => '</h2>',
	    ));
	}
}
add_action('widgets_init', 'true_news_widgets_widget');
/**
 * Widget Base Class.
 */
require get_template_directory() . '/inc/widgets/widget-base-class.php';
/**
 * Recent Post Widget.
 */
require get_template_directory() . '/inc/widgets/recent-post.php';
/**
 * Social Link Widget.
 */
require get_template_directory() . '/inc/widgets/social-link.php';
/**
 * Author Widget.
 */
require get_template_directory() . '/inc/widgets/author.php';
/**
 * Author Widget.
 */
require get_template_directory() . '/inc/widgets/tab-posts.php';
/**
 * Category Widget.
 */
require get_template_directory() . '/inc/widgets/category.php';