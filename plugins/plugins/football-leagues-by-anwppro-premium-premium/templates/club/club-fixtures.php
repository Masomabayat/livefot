<?php
/**
 * The Template for displaying Club >> Fixtures Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/club/club-fixtures.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.8.4
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

/**
 * Hook: anwpfl/tmpl-club/before_fixtures
 *
 * @since 0.7.5
 *
 * @param WP_Post $club
 * @param integer $data->season_idv
 */
do_action( 'anwpfl/tmpl-club/before_fixtures', $club, $data->season_id );

/**
 * Filter: anwpfl/tmpl-club/render_fixtures
 *
 * @since 0.7.5
 *
 * @param bool
 * @param WP_Post $club
 * @param integer $season_id
 */
if ( ! apply_filters( 'anwpfl/tmpl-club/render_fixtures', true, $club, $data->season_id ) ) {
	return;
}
?>
<div class="club__fixtures club-section anwp-section" id="anwp-section-fixtures">
	<?php
	if ( ! empty( $data->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'club__fixtures__fixtures', __( 'Fixtures', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	endif;

	/**
	 * Filter: anwpfl/tmpl-club/fixtures_limit
	 *
	 * @since 0.7.5
	 *
	 * @param integer $qty
	 * @param WP_Post $club
	 * @param integer $season_id
	 */
	$fixtures_limit = apply_filters( 'anwpfl/tmpl-club/fixtures_limit', 10, $club, $data->season_id );

	echo anwp_football_leagues()->template->shortcode_loader(
		'matches',
		[
			'class'           => 'mt-2',
			'filter_by_clubs' => anwp_football_leagues()->club->get_subteam_ids( $club->ID ),
			'season_id'       => $data->season_id,
			'type'            => 'fixture',
			'limit'           => $fixtures_limit,
			'sort_by_date'    => 'asc',
			'show_load_more'  => true,
		]
	); // WPCS: XSS ok.
	?>
</div>
