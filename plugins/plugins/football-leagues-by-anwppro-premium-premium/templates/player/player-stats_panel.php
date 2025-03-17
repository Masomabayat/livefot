<?php
/**
 * The Template for displaying Player >> Stats Panel.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/player/player-stats_panel.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.1.0
 *
 * @version       0.14.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'player_id'         => '',
		'current_season_id' => '',
		'position_code'     => '',
	]
);

if ( ! intval( $data->player_id ) || ( ! absint( $data->current_season_id ) && 'all' !== $data->current_season_id ) ) {
	return;
}

if ( empty( $data->position_code ) ) {
	$data->position_code = get_post_meta( $data->player_id, '_anwpfl_position', true );
}

if ( 'g' === $data->position_code ) {
	$stats = [ 'played', 'started', 'minutes', 'goals_conceded', 'clean_sheets' ];
} else {
	$stats = [ 'played', 'started', 'minutes', 'goals', 'assists' ];
}

$shortcode_args = [
	'player_id'      => $data->player_id,
	'season_id'      => 'all' === $data->current_season_id ? '' : $data->current_season_id,
	'show_secondary' => 1,
	'per_game'       => 1,
	'block_width'    => 140,
	'stats'          => $stats,
	'season_text'    => 1,
];

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo anwp_football_leagues()->template->shortcode_loader( 'premium-player-stats-panel', $shortcode_args );
