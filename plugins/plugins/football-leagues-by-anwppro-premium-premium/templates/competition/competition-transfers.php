<?php
/**
 * The Template for displaying Competition >> Transfers Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/competition/competition-transfers.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.10.2
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
		'competition_id' => '',
		'season_id'      => '',
		'header'         => true,
	]
);

$competition_terms = wp_get_object_terms( $data->competition_id, 'anwp_season' );

if ( isset( $competition_terms[0] ) && isset( $competition_terms[0]->term_id ) && absint( $competition_terms[0]->term_id ) ) {
	$data->season_id = absint( $competition_terms[0]->term_id );
}

if ( ! absint( $data->competition_id ) ) {
	return;
}
?>
<div class="club__transfers club-section anwp-section" id="anwp-section-transfers">
	<?php
	if ( ! empty( $args->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'competition__transfers__transfers', __( 'Transfers', 'anwp-football-leagues-premium' ) ),
			],
			'general/header'
		);
	endif;

	// Without Window
	$shortcode_attr = [
		'competition_id' => $data->competition_id,
		'season_id'      => $data->season_id,
		'order'          => 'DESC',
		'window'         => '',
		'layout'         => 'competition',
	];

	$shortcode_output = anwp_football_leagues()->template->shortcode_loader( 'premium-transfers', $shortcode_attr );

	if ( $shortcode_output ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $shortcode_output;
	}

	if ( ! $shortcode_output ) {
		anwp_football_leagues()->load_partial(
			[
				'no_data_text' => AnWPFL_Text::get_value( 'competition__transfers__no_data', __( 'No data', 'anwp-football-leagues-premium' ) ),
			],
			'general/no-data'
		);
	}
	?>
</div>
