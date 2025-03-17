<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package freenews
 */

//Excerpt More
function freenews_excerpt_more( $link ) {
   $excerpt_text = get_theme_mod('excerpt_text',esc_html__('Read More','freenews'));
    if ( is_admin() ) {
        return $link;
    }

    $link = sprintf(
        '<a href="%1$s" class="more-link">%2$s</a>',
        esc_url( get_permalink( get_the_ID() ) ),
        /* translators: %s: Name of current post */
        sprintf( $excerpt_text, get_the_title( get_the_ID() ) )
    );
    return ' &hellip; ' . $link;
}
add_filter( 'excerpt_more', 'freenews_excerpt_more' );

//Excerpt length
function freenews_excerpt_length($length) {
    $excerpt_length = get_theme_mod('excerpt_length','30');
    if( is_admin() ){
        return absint($length);
    }

    $length = $excerpt_length;
    return absint($length);
}
add_filter('excerpt_length', 'freenews_excerpt_length');

// Site Info
function freenews_site_info(){ ?>
    <a href="<?php echo esc_url( __( 'https://wordpress.org/', 'freenews' ) ); ?>">
<?php
/* translators: %s: CMS name, i.e. WordPress. */
printf( esc_html__( 'Proudly powered by %s', 'freenews' ), 'WordPress' );
?>
</a>
<span class="sep"> | </span>
<?php
/* translators: 1: Theme name, 2: Theme author. */
printf( esc_html__( 'Theme: %1$s By %2$s.', 'freenews' ), 'FreeNews <span class="sep"> | </span> ', '<a href="'.esc_url('https://themespiral.com/').'">ThemeSpiral.com</a>' );
}

add_action ('freenews_footer_copyright_frontend','freenews_site_info');
