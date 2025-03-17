<?php
/**
 * The Template for displaying Chart.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-charts--goals-15.php.
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
		'competition_id' => '',
		'multistage'     => '',
		'season_id'      => '',
		'league_id'      => '',
		'club_id'        => '',
		'club_side'      => '',
		'type'           => '',
		'text_scored'    => '',
		'text_conceded'  => '',
		'legend'         => 1,
		'no_data_text'   => '',
		'limit'          => '',
	]
);

if ( ! absint( $args->club_id ) ) {
	return;
}

$stats_data = anwp_football_leagues_premium()->charts->get_stats_goals_15( $args );

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

	<div class="anwp-fl-chart anwp-fl-chart__goals-15"
		data-fl-text-scored="<?php echo $args->text_scored ? esc_attr( $args->text_scored ) : esc_attr__( 'Scored', 'anwp-football-leagues-premium' ); ?>"
		data-fl-text-conceded="<?php echo $args->text_conceded ? esc_attr( $args->text_conceded ) : esc_attr__( 'Conceded', 'anwp-football-leagues-premium' ); ?>"
		data-fl-series-1="<?php echo wp_json_encode( $stats_data['series_1'] ); ?>"
		data-fl-series-2="<?php echo wp_json_encode( $stats_data['series_2'] ); ?>"
		data-fl-indexes="<?php echo esc_attr( wp_json_encode( $stats_data['indexes'] ) ); ?>"
		data-fl-legend="<?php echo AnWP_Football_Leagues::string_to_bool( $args->legend ) ? 'show' : 'hide'; ?>"
	></div>
</div>
