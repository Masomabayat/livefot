<?php
/**
 * The Template for displaying Club >> Transfers Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/club/club-transfers.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.8.11
 *
 * @version       0.13.1
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

if ( ! absint( $data->club_id ) ) {
	return;
}

if ( 'yes' === get_post_meta( $data->club_id, '_anwpfl_is_national_team', true ) ) {
	return;
}
?>
<div class="club__transfers club-section anwp-section" id="anwp-section-transfers">

	<?php
	if ( ! empty( $data->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'club__transfers__transfers', __( 'Transfers', 'anwp-football-leagues-premium' ) ),
			],
			'general/header'
		);
	endif;

	// Without Window
	$shortcode_attr = [
		'club_id'   => $data->club_id,
		'season_id' => $data->season_id,
		'order'     => 'DESC',
		'window'    => 'none',
	];

	$shortcode_output = anwp_football_leagues()->template->shortcode_loader( 'premium-transfers', $shortcode_attr );

	if ( $shortcode_output ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $shortcode_output;
	}

	// Mid Season
	$shortcode_attr['window'] = 'mid';
	$shortcode_output_mid     = anwp_football_leagues()->template->shortcode_loader( 'premium-transfers', $shortcode_attr );

	if ( $shortcode_output_mid ) {
		echo '<div class="anwp-text-base mb-1">' . esc_html( AnWPFL_Text::get_value( 'club__transfers__mid_season_window', __( 'Mid-season window', 'anwp-football-leagues-premium' ) ) ) . '</div>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $shortcode_output_mid;
	}

	// Pre Season
	$shortcode_attr['window'] = 'pre';
	$shortcode_output_pre     = anwp_football_leagues()->template->shortcode_loader( 'premium-transfers', $shortcode_attr );

	if ( $shortcode_output_pre ) {
		echo '<div class="anwp-text-base mb-1">' . esc_html( AnWPFL_Text::get_value( 'club__transfers__pre_season_window', __( 'Pre-season window', 'anwp-football-leagues-premium' ) ) ) . '</div>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $shortcode_output_pre;
	}

	if ( ! $shortcode_output && ! $shortcode_output_mid && ! $shortcode_output_pre ) {
		anwp_football_leagues()->load_partial(
			[
				'no_data_text' => AnWPFL_Text::get_value( 'club__transfers__no_data', __( 'No data', 'anwp-football-leagues-premium' ) ),
			],
			'general/no-data'
		);
	}
	?>
</div>
