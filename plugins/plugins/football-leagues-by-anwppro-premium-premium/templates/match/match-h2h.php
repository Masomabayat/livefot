<?php
/**
 * The Template for displaying Match >> H2H Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-h2h.php.
 *
 * // phpcs:disable WordPress.NamingConventions.ValidVariableName
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.5.8
 *
 * @version       0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'home_club' => '',
		'away_club' => '',
		'kickoff'   => '',
		'header'    => true,
	]
);

/**
 * Hook: anwpfl/tmpl-match/h2h_before
 *
 * @param object $data Match data
 *
 * @since 0.5.8
 */
do_action( 'anwpfl/tmpl-match/h2h_before', $data );

$shortcode_html = anwp_football_leagues()->template->shortcode_loader(
	'premium-matches-h2h',
	[
		'club_a'       => $data->home_club,
		'club_b'       => $data->away_club,
		'type'         => 'result',
		'limit'        => 10,
		'sort_by_date' => 'desc',
		'class'        => '',
		'date_before'  => $data->kickoff,
		'header'       => true,
	]
);

// Hide section if empty
if ( empty( $shortcode_html ) ) {
	return;
}
?>
<div class="anwp-section">

	<?php
	if ( ! empty( $data->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__h2h__head_to_head_matches', __( 'Head to Head Matches', 'anwp-football-leagues-premium' ) ),
			],
			'general/header'
		);
	endif;
	?>

	<?php echo $shortcode_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
