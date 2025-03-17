<?php
/**
 * The Template for displaying Match content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/content-match.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.7.3
 *
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// phpcs:disable WordPress.NamingConventions

$game_post = get_post();

// Prepare Match data
$game_data = anwp_football_leagues()->match->get_game_data( $game_post->ID );

if ( empty( $game_data['match_id'] ) || absint( $game_data['match_id'] ) !== $game_post->ID ) {
	return '';
}

$match_classes   = [];
$match_classes[] = 'match-' . $game_post->ID;
$match_classes[] = 'match-status__' . $game_data['finished'];

/*
|--------------------------------------------------------------------
| Check Match Live in Commentary
|--------------------------------------------------------------------
*/
if ( anwp_fl_pro()->match->maybe_match_live( $game_post ) && ! absint( $game_data['finished'] ) ) {
	$match_classes[] = 'match__inner--live';
}

if ( ! absint( $game_data['finished'] ) ) {
	if ( ( ! empty( $game_post->_anwpfl_live_status ) || ! empty( $game_post->_anwpfl_match_live_commentary ) ) || anwp_football_leagues_premium()->live->is_api_game_active( $game_post->ID ) ) {
		$match_classes[] = 'fl-match-live-layout';
	}
}
?>
<div class="anwp-b-wrap position-relative match match__inner <?php echo esc_attr( implode( ' ', $match_classes ) ); ?>" data-id="<?php echo absint( $game_post->ID ); ?>">
	<?php

	// Get match data to render
	$game_data = anwp_fl()->match->prepare_match_data_to_render( $game_data, [], 'match', 'full' );

	/*
	|--------------------------------------------------------------------
	| Match FrontEnd edit form
	|--------------------------------------------------------------------
	*/
	if ( anwp_fl_pro()->match_public->is_front_edit_enabled() && anwp_fl_pro()->match_public->has_user_cap_front_edit( $game_post->ID ) ) {
		anwp_fl()->load_partial( $game_data, 'match/match-edit' );
	}

	// Get meta fields
	$game_data['summary']         = get_post_meta( $game_post->ID, '_anwpfl_summary', true );
	$game_data['video_source']    = get_post_meta( $game_post->ID, '_anwpfl_video_source', true );
	$game_data['video_media_url'] = get_post_meta( $game_post->ID, '_anwpfl_video_media_url', true );
	$game_data['video_id']        = get_post_meta( $game_post->ID, '_anwpfl_video_id', true );

	// Get extra Referees
	$game_data['assistant_1']       = get_post_meta( $game_post->ID, '_anwpfl_assistant_1', true );
	$game_data['assistant_2']       = get_post_meta( $game_post->ID, '_anwpfl_assistant_2', true );
	$game_data['referee_fourth_id'] = get_post_meta( $game_post->ID, '_anwpfl_referee_fourth', true );

	// Prepare Game players
	$game_data['players'] = anwp_football_leagues()->player->get_game_players( $game_data );

	/**
	 * Filter: anwpfl/tmpl-match/render_header
	 *
	 * @since 0.7.5
	 *
	 * @param WP_Post $game_post
	 */
	if ( apply_filters( 'anwpfl/tmpl-match/render_header', true, $game_post ) ) {
		anwp_football_leagues()->load_partial( $game_data, 'match/match' );
	}

	/**
	 * Hook: anwpfl/tmpl-match/after_header
	 *
	 * @since 0.7.5
	 * @since 0.7.6 - Added $data
	 *
	 * @param WP_Post $match_post
	 */
	do_action( 'anwpfl/tmpl-match/after_header', $game_post, $game_data );

	/*
	|--------------------------------------------------------------------
	| Rendering Match Sections
	|--------------------------------------------------------------------
	*/
	$match_sections = [
		'timeline',
		'goals',
		'penalty_shootout',
		'missed_penalties',
		'formation',
		'lineups',
		'substitutes',
		'missing',
		'referees',
		'video',
		'cards',
		'stats',
		'player-stats',
		'summary',
		'gallery',
		'latest',
		'h2h',
		'commentary',
		'custom-code',
	];

	foreach ( $match_sections as $section ) {
		if ( 'stats' === $section ) {
			$stats_layout = sanitize_key( AnWPFL_Premium_Options::get_value( 'match_stats_layout', '' ) );
			anwp_football_leagues()->load_partial( $game_data, 'match/match-stats', $stats_layout );
		} else {
			anwp_football_leagues()->load_partial( $game_data, 'match/match-' . sanitize_key( $section ) );
		}
	}
	?>
</div>
