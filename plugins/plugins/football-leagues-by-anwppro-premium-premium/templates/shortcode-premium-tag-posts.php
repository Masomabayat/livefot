<?php
/**
 * The Template for displaying Tag Posts shortcode
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-tag-posts.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author         Andrei Strekozov <anwp.pro>
 * @package        AnWP-Football-Leagues/Templates
 * @since          0.10.5
 *
 * @version        0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'id'        => '',
		'limit'     => 10,
		'show_tags' => 1,
		'cols'      => 2,
		'layout'    => 'grid',
	]
);

if ( empty( $data->id ) ) {
	return;
}

$ids = wp_parse_id_list( $data->id );

if ( empty( $ids ) ) {
	return;
}

// Get Term IDs
$post_tags = wp_get_object_terms( $ids, 'post_tag', [ 'fields' => 'all' ] );
$term_ids  = wp_list_pluck( $post_tags, 'term_id' );

$tag_posts = anwp_football_leagues_premium()->data->get_tag_posts_by_id( $term_ids, $data->limit );

// Prepare layout
$posts_in_col = absint( ceil( count( $tag_posts ) / $data->cols ) );

if ( $data->cols < 1 || $data->cols > 3 ) {
	$data->cols = 2;
}

switch ( $data->cols ) {
	case 3:
		$col_class = 'anwp-col-md-4 anwp-col-sm-6';
		break;
	case 2:
		$col_class = 'anwp-col-md-6';
		break;
	default:
		$col_class = 'anwp-col-12';
}
?>
<div class="anwp-b-wrap anwp-fl-tag-posts">

	<?php if ( AnWP_Football_Leagues::string_to_bool( $data->show_tags ) && ! empty( $post_tags ) ) : ?>
		<div class="d-flex flex-wrap mb-3">
			<?php foreach ( $post_tags as $post_tag ) : ?>
				<div class="anwp-fl-tag text-nowrap mr-3 d-flex align-items-center">
					<svg class="anwp-icon anwp-icon--octi mr-1">
						<use xlink:href="#icon-tag"></use>
					</svg>
					<a class="anwp-link-without-effects" href="<?php echo esc_url( get_term_link( $post_tag->term_id ) ); ?>"><?php echo esc_html( $post_tag->name ); ?></a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php
	if ( empty( $tag_posts ) ) :
		anwp_football_leagues()->load_partial(
			[
				'no_data_text' => esc_html( AnWPFL_Text::get_value( 'tag_posts__shortcode__no_posts_found', __( 'No Posts Found', 'anwp-football-leagues-premium' ) ) ),
			],
			'general/no-data'
		);
	else :
		if ( 'simple' === $data->layout ) :
			?>
			<div class="anwp-row">
				<div class="<?php echo esc_attr( $col_class ); ?>">
					<?php foreach ( $tag_posts as $tag_post_index => $tag_post ) : ?>
						<?php
						if ( $tag_post_index && 0 === $tag_post_index % $posts_in_col ) {
							echo '</div><div class="' . esc_attr( $col_class ) . '">';
						}
						?>
						- <a href="<?php echo esc_url( get_permalink( $tag_post ) ); ?>"><?php echo esc_html( $tag_post->post_title ); ?></a><br>
					<?php endforeach; ?>
				</div>
			</div>
		<?php else : ?>
			<div class="anwp-row mt-n3">
				<?php foreach ( $tag_posts as $tag_post ) : ?>
					<div class="anwp-fl-tag-posts__item d-flex position-relative <?php echo esc_attr( $col_class ); ?> mt-3">
						<div class="anwp-fl-tag-posts__thumbnail position-relative">

							<div class="anwp-fl-tag-posts__thumbnail-img"
								style="background-image: url(<?php echo esc_url( anwp_football_leagues_premium()->data->get_post_image_uri( 'thumbnail', true, $tag_post->ID ) ); ?>)">
							</div>

							<div class="anwp-fl-tag-posts__thumbnail-bg anwp-position-cover"></div>
						</div>

						<div class="anwp-fl-tag-posts__content pl-3 flex-grow-1">

							<div class="anwp-fl-tag-posts__title anwp-font-heading">
								<?php echo esc_html( $tag_post->post_title ); ?>
							</div>

							<div class="anwp-fl-tag-posts__bottom-meta mt-1">
								<span class="posted-on"><?php echo anwp_football_leagues_premium()->data->get_post_date( $tag_post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</div>
						</div>

						<a class="anwp-fl-tag-posts__link anwp-position-cover anwp-link-without-effects" href="<?php the_permalink( $tag_post ); ?>" aria-hidden="true"></a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
