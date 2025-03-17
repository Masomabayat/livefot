<?php
 /**
 * Register Sidebar area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 * @package freenews
 */
function freenews_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'freenews' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here to appear in your sidebar on blog posts and archive pages.', 'freenews' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Header Banner', 'freenews' ),
		'id'            => 'header-banner',
		'description'   => esc_html__( 'This section will appear at right side of Site Title', 'freenews' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Advertise Area', 'freenews' ),
		'id'            => 'advertise-area',
		'description'   => esc_html__( 'This section will appear just below Highlighted Category and Main Banner', 'freenews' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'FreeNews Template Left Section', 'freenews' ),
		'id'            => 'freenews-template-left',
		'description'   => esc_html__( 'This section will appear when FreeNews Template is selected at Left Section', 'freenews' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'FreeNews Template Main Section', 'freenews' ),
		'id'            => 'freenews-template-main',
		'description'   => esc_html__( 'This section will appear when FreeNews Template is selected at Main Section', 'freenews' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'FreeNews Template Right Section', 'freenews' ),
		'id'            => 'freenews-template-right',
		'description'   => esc_html__( 'This section will appear when FreeNews Template is selected at Right Section', 'freenews' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_widget( 'FreeNews_posts' );
	register_widget( 'FreeNews_list_category_posts' );
	register_widget( 'FreeNews_category_slide' );
	register_widget( 'FreeNews_blog_category_posts' );
}
add_action( 'widgets_init', 'freenews_widgets_init' );