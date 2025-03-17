<?php
/**
 * The Template for displaying player content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/content-player.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.3.0
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$player_id         = get_the_ID();
$current_season_id = anwp_football_leagues_premium()->player->get_post_season( $player_id );

/*
|--------------------------------------------------------------------------
| Prepare player data for sections
|--------------------------------------------------------------------------
*/

// Card icons
$card_icons = [
	'y'  => '<svg class="icon__card m-0"><use xlink:href="#icon-card_y"></use></svg>',
	'r'  => '<svg class="icon__card m-0"><use xlink:href="#icon-card_r"></use></svg>',
	'yr' => '<svg class="icon__card m-0"><use xlink:href="#icon-card_yr"></use></svg>',
];

$series_map = anwp_football_leagues()->data->get_series();

// Get season matches
$season_matches      = anwp_football_leagues()->player->tmpl_get_latest_matches( $player_id, $current_season_id );
$competition_matches = anwp_football_leagues()->player->tmpl_prepare_competition_matches( $season_matches );

$player_data = [
	'current_season_id'   => $current_season_id,
	'competition_matches' => $competition_matches,
	'card_icons'          => $card_icons,
	'series_map'          => $series_map,
	'club_id'             => (int) get_post_meta( $player_id, '_anwpfl_current_club', true ),
];

$player_data += ( anwp_fl()->player->get_player_data( $player_id ) ?? [] );

$player_data['club_id']       = $player_data['team_id'] ?? ''; // compatibility with pre v0.16.0
$player_data['position_code'] = $player_data['position'] ?? ''; // compatibility with pre v0.16.0

$player_data['club_title'] = anwp_fl()->club->get_club_title_by_id( $player_data['club_id'] );
$player_data['club_link']  = anwp_fl()->club->get_club_link_by_id( $player_data['club_id'] );
?>
<div class="anwp-b-wrap player player__inner player-id-<?php echo (int) $player_id; ?>">
	<?php

	$player_sections = [
		'header',
		'stats_panel',
		'description',
		'stats',
		'matches',
		'missed',
		'gallery',
	];

	foreach ( $player_sections as $section ) {
		anwp_football_leagues()->load_partial( $player_data, 'player/player-' . sanitize_key( $section ) );
	}
	?>
</div>
