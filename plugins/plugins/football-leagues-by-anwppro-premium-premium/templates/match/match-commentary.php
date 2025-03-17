<?php
/**
 * The Template for displaying Match >> Commentary Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-commentary.php.
 *
 * // phpcs:disable WordPress.NamingConventions.ValidVariableName
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.5.8
 *
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$data,
	[
		'match_id'  => '',
		'home_club' => '',
		'away_club' => '',
		'builder'   => '',
		'players'   => [],
		'header'    => true,
	]
);

/*
|--------------------------------------------------------------------
| Prepare Commentary events
|--------------------------------------------------------------------
*/
$game_data      = anwp_fl()->match->get_game_data( $data['match_id'] );
$game_events    = $game_data['match_events'] ? json_decode( $game_data['match_events'] ) : [];
$data['events'] = anwp_fl_pro()->match->parse_match_comments_events( $game_events, $data['match_id'], 'match-live' === $data['builder'] );

if ( empty( $data['events'] ) && empty( get_post_meta( $data['match_id'], '_anwpfl_live_status', true ) ) && ! anwp_football_leagues_premium()->live->is_api_game_active( $data['match_id'] ) ) {
	return;
}

$color_home = get_post_meta( $data['home_club'], '_anwpfl_main_color', true );
$color_away = get_post_meta( $data['away_club'], '_anwpfl_main_color', true );

if ( empty( $color_home ) ) {
	$color_home = '#0085ba';
}

if ( empty( $color_away ) ) {
	$color_away = '#dc3545';
}

// Check wrapper height
$wrapper_height = absint( AnWPFL_Premium_Options::get_value( 'match_commentary_max_height' ) );

// Wrapper classes
$wrapper_classes = '';

if ( $wrapper_height ) {
	$wrapper_classes .= ' match-commentary--height-limit';
}
?>
<div class="anwp-section">

	<?php
	if ( ! empty( $args->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__commentary__match_commentary', __( 'Match Commentary', 'anwp-football-leagues-premium' ) ),
			],
			'general/header'
		);
	endif;
	?>

	<div class="match-commentary">
		<div class="anwp-row anwp-no-gutters">
			<div class="anwp-col-sm">
				<?php
				anwp_football_leagues()->load_partial(
					[
						'club_id' => $data['home_club'],
						'class'   => 'mr-sm-2 my-1',
					],
					'club/club-title'
				);
				?>
			</div>
			<div class="anwp-col-sm">
				<?php
				anwp_football_leagues()->load_partial(
					[
						'club_id' => $data['away_club'],
						'class'   => 'ml-sm-2 my-1',
						'is_home' => false,
					],
					'club/club-title'
				);
				?>
			</div>
		</div>

		<div class="mt-2 match-commentary__wrapper-outer position-relative <?php echo esc_attr( $wrapper_classes ); ?>"
			style="<?php echo $wrapper_height ? 'max-height: ' . absint( $wrapper_height ) . 'px' : ''; ?>">
			<div class="match-commentary__wrapper position-relative p-4 anwp-bg-light">
				<?php
				if ( ! empty( $data['events'] ) && 'match-live' !== $data['builder'] ) {
					$event_match_data = (object) [
						'club_home_title' => $data['club_home_title'],
						'club_away_title' => $data['club_away_title'],
						'club_home_logo'  => $data['club_home_logo'],
						'club_away_logo'  => $data['club_away_logo'],
						'match_id'        => $data['match_id'],
						'club_home_abbr'  => $data['club_home_abbr'],
						'club_away_abbr'  => $data['club_away_abbr'],
						'color_home'      => $color_home,
						'color_away'      => $color_away,
						'home_club'       => intval( $data['home_club'] ),
						'away_club'       => intval( $data['away_club'] ),
						'players'         => $data['players'] ? : anwp_fl()->player->get_game_players( anwp_fl()->match->prepare_match_data_to_render( $game_data, [], 'match', 'full' ) ),
					];

					// Set Scores
					if ( 'no' !== AnWPFL_Premium_Options::get_value( 'match_commentary_show_scores' ) ) {
						$data['events'] = anwp_football_leagues_premium()->match->set_scores_in_commentary( $data['events'], $data );
					}

					foreach ( $data['events'] as $event ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo anwp_football_leagues_premium()->match->get_commentary_event_tmpl( $event, $event_match_data );
					}
				}
				?>
			</div>
			<div class="match-commentary__wrapper-shadow"></div>
		</div>
	</div>
</div>
<?php
