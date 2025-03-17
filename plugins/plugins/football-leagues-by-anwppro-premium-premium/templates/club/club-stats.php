<?php
/**
 * The Template for displaying Club >> Stats.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/club/club-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.5.8 (premium)
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

// Stats for goalkeepers
$stats_g = anwp_football_leagues()->template->shortcode_loader(
	'premium-stats-players',
	[
		'season_id'  => $data->season_id,
		'club_id'    => $data->club_id,
		'rows'       => '-1',
		'type'       => 'g',
		'paging'     => false,
		'layout_mod' => 'even',
	]
);

// Stats for field players
$stats_p = anwp_football_leagues()->template->shortcode_loader(
	'premium-stats-players',
	[
		'season_id'  => $data->season_id,
		'club_id'    => $data->club_id,
		'rows'       => '-1',
		'type'       => 'p',
		'paging'     => false,
		'layout_mod' => 'even',
	]
);

if ( empty( $stats_g ) && empty( $stats_p ) ) {
	return;
}

/**
 * Hook: anwpfl/tmpl-club/before_stats
 *
 * @param WP_Post $club
 * @param integer $season_id
 */
do_action( 'anwpfl/tmpl-club/before_stats', $club, $data->season_id );
?>
<div class="club__stats club-section anwp-section" id="anwp-section-stats">

	<?php
	if ( ! empty( $data->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'club__stats__player_stats', __( 'Player Stats', 'anwp-football-leagues-premium' ) ),
			],
			'general/header'
		);
	endif;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $stats_g;

	echo '<div class="my-2">&nbsp;</div>';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $stats_p;
	?>
</div>
