<?php
/**
 * The Template for displaying List of Standing Tables
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-standings.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP-Football-Leagues/Templates
 * @since            0.11.2
 *
 * @version          0.14.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Merge with default params
$data = (object) wp_parse_args(
	$data,
	[
		'context'        => 'shortcode',
		'competition_id' => '',
		'match_id'       => '',
		'season_id'      => '',
		'club_id'        => '',
		'layout'         => '',
		'partial'        => '',
		'bottom_link'    => '',
		'show_notes'     => 1,
		'show_titles'    => 1,
	]
);

$standings = [];

// Check for required data
if ( ! empty( $data->competition_id ) ) {
	$standings = anwp_football_leagues_premium()->standing->get_standings_by_competition_id( $data->competition_id );
} elseif ( ! empty( $data->match_id ) ) {
	$standings = anwp_football_leagues_premium()->standing->get_standing_by_match_id( $data->match_id );
} elseif ( ! empty( $data->season_id ) ) {
	$standings = anwp_football_leagues_premium()->standing->get_standings_by_season_id( $data->season_id, $data->club_id );
}

if ( empty( $standings ) || ! is_array( $standings ) ) {
	return;
}

foreach ( $standings as $standing ) {

	if ( ! absint( $standing ) ) {
		continue;
	}

	$data->title         = '';
	$data->wrapper_class = 'standing-multi--shortcode';
	$data->id            = absint( $standing );

	if ( AnWP_Football_Leagues::string_to_bool( $data->show_titles ) ) {
		$data->title = get_the_title( $standing );
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo anwp_football_leagues()->template->shortcode_loader( 'standing', $data );
}
