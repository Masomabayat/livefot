<?php
/**
 * The Template for displaying Club >> Trophies Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/club/club-trophies.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.8.5
 *
 * @version       0.14.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'club_id' => '',
	]
);

$trophies = get_post_meta( $data->club_id, '_fl_pro_trophies', true );

if ( empty( $trophies ) ) {
	return;
}
?>
<div class="club__trophies mt-4 anwp-bg-light p-3">
	<?php
	foreach ( $trophies as $trophy ) :
		if ( empty( $trophy['image'] ) ) {
			continue;
		}

		$number_tooltip = isset( $trophy['number_tooltip'] ) ? $trophy['number_tooltip'] : '';
		?>
		<div class="club-trophy d-flex flex-column anwp-text-center">
			<img loading="lazy" width="100" height="100" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $trophy['title'] ); ?>" class="club-trophy__img anwp-object-contain mx-auto anwp-w-100 anwp-h-100" src="<?php echo esc_url( $trophy['image'] ); ?>" alt="">
			<div class="club-trophy__number anwp-text-center mt-1">
				<div class="club-trophy__text d-inline-block anwp-text-sm anwp-fl-border anwp-border-light px-2 py-1" <?php echo $number_tooltip ? 'data-toggle="anwp-tooltip"' : ''; ?> data-tippy-content="<?php echo esc_attr( $number_tooltip ); ?>">
					<?php echo esc_html( isset( $trophy['number'] ) ? $trophy['number'] : '' ); ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
