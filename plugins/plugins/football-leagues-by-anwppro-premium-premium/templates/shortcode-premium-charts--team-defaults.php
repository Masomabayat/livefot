<?php
/**
 * The Template for displaying Chart.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-charts--team-defaults.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.11.10
 * @version       0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'type'           => '',
		'stat'           => '',
		'limit'          => '',
		'club_id'        => '',
		'competition_id' => '',
		'multistage'     => '',
		'season_id'      => '',
		'league_id'      => '',
		'club_side'      => '',
		'x_axis_label'   => 1,
		'data_label'     => 1,
		'color'          => '',
		'title'          => '',
		'no_data_text'   => '',
	]
);

if ( ! absint( $args->club_id ) ) {
	return;
}

if ( 'club' === $args->color ) {
	$club_color  = get_post_meta( $args->club_id, '_anwpfl_main_color', true );
	$args->color = $club_color ?: '';
}

$stats_data = anwp_football_leagues_premium()->charts->get_stats_team_defaults( $args );

if ( empty( $stats_data ) ) {

	if ( trim( $args->no_data_text ) ) {
		anwp_football_leagues()->load_partial(
			[
				'no_data_text' => $args->no_data_text,
			],
			'general/no-data'
		);
	}

	return;
}

// Load Charts Library
wp_enqueue_script( 'anwp-fl-echarts' );
?>
<div class="anwp-b-wrap">
	<?php
	/*
	|--------------------------------------------------------------------
	| Club Header
	|--------------------------------------------------------------------
	*/
	$club_side = mb_strtolower( $args->club_side );

	if ( in_array( $club_side, [ 'home', 'away' ], true ) ) :

		anwp_football_leagues()->load_partial(
			[
				'club_id' => $args->club_id,
				'class'   => 'mb-3',
				'is_home' => 'away' !== $club_side,
			],
			'club/club-title'
		);

	endif;
	?>

	<div class="anwp-fl-chart anwp-fl-chart__team-defaults"
		data-fl-series="<?php echo esc_attr( wp_json_encode( $stats_data['series'] ) ); ?>"
		data-fl-x-axis-label="<?php echo esc_attr( $args->x_axis_label ); ?>"
		data-fl-data-label="<?php echo esc_attr( $args->data_label ); ?>"
		data-fl-indexes="<?php echo esc_attr( wp_json_encode( $stats_data['indexes'] ) ); ?>"
		data-fl-tooltips="<?php echo esc_attr( wp_json_encode( $stats_data['tooltips'] ) ); ?>"
		data-fl-stat="<?php echo esc_attr( $args->stat ); ?>"
		data-fl-color="<?php echo esc_attr( $args->color ); ?>"
		data-fl-title="<?php echo esc_attr( $args->title ); ?>"
	></div>
</div>
