<?php
/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function newsever_widgets_init()
{
    register_sidebar(array(
        'name' => esc_html__('Main Sidebar', 'newsever'),
        'id' => 'sidebar-1',
        'description' => esc_html__('Add widgets for main sidebar.', 'newsever'),
        'before_widget' => '<div id="%1$s" class="widget newsever-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title widget-title-1"><span class="header-after">',
        'after_title' => '</span></h2>',
    ));



    register_sidebar(array(
        'name'          => esc_html__('Off Canvas', 'newsever'),
        'id'            => 'off-canvas-panel',
        'description'   => esc_html__('Add widgets for off-canvas section.', 'newsever'),
        'before_widget' => '<div id="%1$s" class="widget newsever-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title widget-title-1"><span class="header-after">',
        'after_title' => '</span></h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Front-page Content Section', 'newsever'),
        'id' => 'home-content-widgets',
        'description' => esc_html__('Add widgets to front-page contents section.', 'newsever'),
        'before_widget' => '<div id="%1$s" class="widget newsever-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title"><span class="header-after">',
        'after_title' => '</span></h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Front-page Primary Sidebar', 'newsever'),
        'id' => 'home-sidebar-1-widgets',
        'description' => esc_html__('Add widgets to front-page first sidebar section.', 'newsever'),
        'before_widget' => '<div id="%1$s" class="widget newsever-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title widget-title-1"><span class="header-after">',
        'after_title' => '</span></h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Front-page Secondary Sidebar', 'newsever'),
        'id' => 'home-sidebar-2-widgets',
        'description' => esc_html__('Add widgets to front-page second sidebar section.', 'newsever'),
        'before_widget' => '<div id="%1$s" class="widget newsever-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title widget-title-1"><span class="header-after">',
        'after_title' => '</span></h2>',
    ));


    register_sidebar(array(
        'name' => esc_html__('Footer First Section', 'newsever'),
        'id' => 'footer-first-widgets-section',
        'description' => esc_html__('Displays items on footer first column.', 'newsever'),
        'before_widget' => '<div id="%1$s" class="widget newsever-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title widget-title-1"><span class="header-after">',
        'after_title' => '</span></h2>',
    ));


    register_sidebar(array(
        'name' => esc_html__('Footer Second Section', 'newsever'),
        'id' => 'footer-second-widgets-section',
        'description' => esc_html__('Displays items on footer second column.', 'newsever'),
        'before_widget' => '<div id="%1$s" class="widget newsever-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title widget-title-1"><span class="header-after">',
        'after_title' => '</span></h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Footer Third Section', 'newsever'),
        'id' => 'footer-third-widgets-section',
        'description' => esc_html__('Displays items on footer third column.', 'newsever'),
        'before_widget' => '<div id="%1$s" class="widget newsever-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title widget-title-1"><span class="header-after">',
        'after_title' => '</span></h2>',
    ));





}

add_action('widgets_init', 'newsever_widgets_init');