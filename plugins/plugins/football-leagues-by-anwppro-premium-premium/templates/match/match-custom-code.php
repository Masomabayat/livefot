<?php
/**
 * The Template for displaying Match >> Custom Code Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-custom-code.php.
 *
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.9.2
 *
 * @version       0.9.2 (Premium)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'match_id' => '',
	]
);

// Hide section if empty
if ( empty( $data->match_id ) ) {
	return;
}

$custom_code = get_post_meta( $data->match_id, '_anwpfl_match_custom_code', true );

if ( ! empty( $custom_code ) ) {
	echo do_shortcode( $custom_code ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
