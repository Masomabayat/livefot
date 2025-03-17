<?php
/**
 * The Template for displaying MatchWeeks Slides.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-matchweeks-slides.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.6.0
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// Prevent errors with new params
$args = (object) wp_parse_args(
	$data,
	[
		'competition_id'      => '',
		'group_id'            => '',
		'show_club_logos'     => 1,
		'show_match_datetime' => 1,
		'club_links'          => 1,
		'show_club_name'      => 1,
		'matchweek'           => '',
		'match_card'          => 'slim',
		'slides_to_show'      => 4,
	]
);

if ( empty( $data->competition_id ) ) {
	return;
}

// Set layout
$layout = in_array( $args->match_card, [ 'simple', 'modern', 'slim' ], true ) ? $args->match_card : 'slim';

/*
|--------------------------------------------------------------------------
| Get games to render
|--------------------------------------------------------------------------
*/
$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended(
	[
		'competition_id' => $args->competition_id,
		'sort_by_date'   => 'asc',
		'group_by'       => 'matchweek',
		'group_id'       => $args->group_id,
	]
);

if ( empty( $matches ) ) {
	return;
}

/*
|--------------------------------------------------------------------------
| Prepare Data Structure
|--------------------------------------------------------------------------
*/
$matchweeks = [];
foreach ( $matches as $match ) {
	if ( '0' !== $match->match_week && $match->match_week ) {
		$matchweeks[ $match->match_week ][] = $match;
	}
}

ksort( $matchweeks );

/*
|--------------------------------------------------------------------------
| Slider Config
|--------------------------------------------------------------------------
*/
$initial_slide = 0;
$navs_to_show  = intval( $args->slides_to_show < 1 ? 4 : $args->slides_to_show );

if ( $args->matchweek ) {

	if ( -1 === intval( $args->matchweek ) ) {
		$current_matchweek = get_post_meta( $data->competition_id, '_anwpfl_matchweek_current', true );

		if ( intval( $current_matchweek ) ) {
			$args->matchweek = intval( $current_matchweek );
		}
	}

	$initial_slide = (int) array_search( (int) $args->matchweek, array_keys( $matchweeks ), true );
}

if ( count( array_keys( $matchweeks ) ) < $navs_to_show ) {
	$navs_to_show = count( array_keys( $matchweeks ) );
}

// Load Swiper
wp_enqueue_script( 'anwp-fl-swiper' );
?>
<div class="anwp-b-wrap anwp-fl-matchweek-slides" data-initial="<?php echo absint( $initial_slide ); ?>" data-navs="<?php echo absint( $navs_to_show ); ?>">
	<div class="anwp-fl-matchweek-nav-slides__swiper-outer-container">
		<div class="anwp-fl-matchweek-nav-slides__swiper-container">
			<div class="swiper-wrapper">
				<?php foreach ( array_keys( $matchweeks ) as $index => $matchweek_number ) : ?>
					<div class="anwp-fl-matchweek-nav-slides__swiper-slide" data-index="<?php echo absint( $index ); ?>"><?php echo esc_html( $matchweek_number ); ?></div>
				<?php endforeach; ?>
			</div>
		</div>
		<!-- If we need navigation buttons -->
		<div class="anwp-fl-matchweek-nav-slides__prev swiper-button-prev"></div>
		<div class="anwp-fl-matchweek-nav-slides__next swiper-button-next"></div>
	</div>

	<div class="anwp-fl-matchweek-slides__swiper-container">
		<div class="swiper-wrapper">
			<?php foreach ( $matchweeks as $matchweek_number => $matchweek ) : ?>
				<div class="anwp-fl-matchweek-slides__swiper-slide">
					<div class="anwp-text-base competition__stage-title">
						<?php echo esc_html( anwp_football_leagues()->options->get_text_matchweek( $matchweek_number ) ); ?>
					</div>
					<?php
					foreach ( $matchweek as $m_index => $match ) :

						// Get match data to render
						$data = anwp_football_leagues()->match->prepare_match_data_to_render( $match, $args );

						// Add extra options
						$data['show_club_name']      = $args->show_club_name;
						$data['show_match_datetime'] = $args->show_match_datetime;
						$data['club_links']          = 0;
						$data['competition_logo']    = false;

						anwp_football_leagues()->load_partial( $data, 'match/match', $layout );

					endforeach;
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
