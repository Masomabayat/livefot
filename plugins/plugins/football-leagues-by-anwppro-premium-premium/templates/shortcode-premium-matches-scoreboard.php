<?php
/**
 * The Template for displaying Matches Scoreboard.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-matches-scoreboard.php
 *
 * @var object $data - Object with shortcode data.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP-Football-Leagues-Premium/Templates
 *
 * @since            0.6.0
 *
 * @version          0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'competition_id'      => '',
		'season_id'           => '',
		'stadium_id'          => '',
		'show_secondary'      => 0,
		'type'                => '',
		'limit'               => 0,
		'filter_by'           => '',
		'filter_values'       => '',
		'days_offset'         => '',
		'days_offset_to'      => '',
		'today_focus'         => 1,
		'match_card'          => 'card-a',
		'show_match_datetime' => 1,
		'club_links'          => 1,
		'club_titles'         => 1,
		'priority'            => 0,
		'loop'                => 0,
		'autoplay'            => 0,
	]
);

$args->sort_by_date = 'asc';

// Get competition matches
$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $args );

if ( empty( $matches ) ) {
	return;
}

/*
|--------------------------------------------------------------------------
| Slider Config
|--------------------------------------------------------------------------
*/
$initial_slide = 0;

if ( AnWP_Football_Leagues::string_to_bool( $args->today_focus ) ) {

	foreach ( $matches as $index => $match ) {
		if ( date( 'Y-m-d', strtotime( $match->kickoff ) ) >= date( 'Y-m-d' ) ) {
			$initial_slide = $index;
			break;
		}
	}
}

// Load Swiper
wp_enqueue_script( 'anwp-fl-swiper' );
?>
<div class="anwp-b-wrap anwp-fl-matches-scoreboard"
	data-fl-initial="<?php echo absint( $initial_slide ); ?>"
	data-fl-loop="<?php echo esc_attr( AnWP_Football_Leagues::string_to_bool( $args->loop ) ? 'yes' : '' ); ?>"
	data-fl-autoplay="<?php echo esc_attr( AnWP_Football_Leagues::string_to_bool( $args->autoplay ) ? 'yes' : '' ); ?>">

	<div class="anwp-fl-matches-scoreboard__swiper-container">
		<div class="swiper-wrapper">
			<?php
			foreach ( $matches as $m_index => $match ) :

				// Get match data to render
				$data = anwp_football_leagues()->match->prepare_match_data_to_render( $match, $args );

				// Add extra options
				$data['show_match_datetime'] = $args->show_match_datetime;
				$data['club_links']          = $args->club_links;
				$data['club_titles']         = $args->club_titles;

				anwp_football_leagues()->load_partial( $data, 'match/match', $args->match_card );

			endforeach;
			?>
		</div>
	</div>

	<div class="anwp-fl-matches-scoreboard__swiper-button-prev d-flex align-items-center justify-content-center anwp-user-select-none"> <</div>
	<div class="anwp-fl-matches-scoreboard__swiper-button-next d-flex align-items-center justify-content-center anwp-user-select-none"> ></div>
</div>
