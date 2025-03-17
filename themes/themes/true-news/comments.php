<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package True News
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

$true_news_default = true_news_get_default_theme_options();
$twp_comment_toggle_button_title = get_theme_mod( 'twp_comment_toggle_button_title',$true_news_default['twp_comment_toggle_button_title'] );
$ed_toggle_comment = get_theme_mod( 'ed_toggle_comment',$true_news_default['ed_toggle_comment'] );
$twp_enable_normal_comment_box = get_post_meta( $post->ID, 'twp_enable_normal_comment_box', true);

if( !$twp_enable_normal_comment_box ){

    $true_news_default = true_news_get_default_theme_options();
    $twp_enable_normal_comment_box = get_theme_mod( 'ed_toggle_comment',$true_news_default['ed_toggle_comment'] );

}

if( is_singular('post') && $twp_enable_normal_comment_box ){

	$comment_class = 'twp-comment-area';
	echo '<button type="button" class="twp-comment-toggle"><span class="comment-toggle-label secondary-font">';
	true_news_the_theme_svg('comment','rgb(255,255,255)');
	echo esc_html( $twp_comment_toggle_button_title ).'</span><span class="comment-toggle-icon"></span></button>';

}else{

	$comment_class = '';

} ?>

<div id="comments" class="comments-area <?php echo $comment_class; ?>">

	<?php
	// You can start editing here -- including this comment!
	if ( have_comments() ) : ?>
		
		<h2 class="comments-title">
			<?php
			printf( // WPCS: XSS OK.
				esc_html( _nx( 'One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'true-news' ) ),
				number_format_i18n( get_comments_number() ),
				'<span>' . get_the_title() . '</span>'
			); ?>
		</h2>

		<?php
		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<nav id="comment-nav-above" class="navigation comment-navigation" role="navigation">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'true-news' ); ?></h2>
				<div class="nav-links">

					<div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'true-news' ) ); ?></div>
					<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'true-news' ) ); ?></div>

				</div>
			</nav>
		<?php
		endif; // Check for comment navigation. ?>

		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
			) ); ?>
		</ol>

		<?php 
		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'true-news' ); ?></h2>
				<div class="nav-links">

					<div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'true-news' ) ); ?></div>
					<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'true-news' ) ); ?></div>

				</div>
			</nav>
		<?php
		endif; // Check for comment navigation.

	endif; // Check for have_comments().


	// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>

		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'true-news' ); ?></p>

	<?php
	endif;

	comment_form(); ?>

</div>
