<?php
/**
 * The Template for displaying H2H Matches.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-matches-h2h.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         AnWP-Football-Leagues/Templates
 * @since           0.5.8
 *
 * @version         0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'competition_id'      => '',
		'season_id'           => '',
		'league_id'           => '',
		'show_secondary'      => 0,
		'type'                => '',
		'limit'               => 0,
		'sort_by_date'        => '',
		'show_club_logos'     => 1,
		'show_match_datetime' => true,
		'club_links'          => true,
		'class'               => '',
		'club_a'              => '',
		'club_b'              => '',
		'date_before'         => '',
		'no_data_text'        => '',
	]
);

// Get competition matches
$matches = anwp_football_leagues_premium()->match->get_matches_h2h( $args );

if ( empty( $matches ) ) {

	if ( trim( $args->no_data_text ) ) {
		anwp_football_leagues()->load_partial(
			[
				'no_data_text' => $args->no_data_text,
			],
			'general/no-data'
		);
	}

	return;
}
?>
<div class="anwp-b-wrap match-list match-list--shortcode <?php echo esc_attr( $args->class ); ?>">
	<?php
	foreach ( $matches as $ii => $match ) :

		// Get match data to render
		$data = anwp_football_leagues()->match->prepare_match_data_to_render( $match, $args );

		anwp_football_leagues()->load_partial( $data, 'match/match', 'slim' );
	endforeach;
	?>
</div>
