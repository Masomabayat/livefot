<?php
/**
 * The Template for displaying Club >> Game Stats.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/club/club-game-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.12.4
 *
 * @version       0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'club_id'   => '',
		'season_id' => '',
		'header'    => true,
	]
);

$club = get_post( $data->club_id );

$shortcode_args = [
	'season_id' => $data->season_id,
	'club_id'   => $data->club_id,
	'per_game'  => 1,
	'stats'     => 'goals,goals_conceded,cards_y,cards_r,clean_sheets,corners,fouls,offsides,shots,shots_on_goal',
	'class'     => '',
	'header'    => '',
];

$shortcode_output = anwp_football_leagues()->template->shortcode_loader( 'premium-stats-club', $shortcode_args );

if ( empty( $shortcode_output ) ) {
	return;
}
?>
<div class="club__game-stats club-section anwp-section">

	<?php
	if ( ! empty( $data->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'club__stats__club_stats', __( 'Club Stats', 'anwp-football-leagues-premium' ) ),
			],
			'general/header'
		);
	endif;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $shortcode_output;
	?>
</div>
