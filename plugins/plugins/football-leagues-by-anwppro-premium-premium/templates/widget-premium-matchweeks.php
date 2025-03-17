<?php
/**
 * The Template for displaying Matchweeks Slider.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/widget-premium-matchweeks.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.1.0
 *
 * @version       0.8.12
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check for required data
if ( empty( $data->competition_id ) ) {
	return;
}

$data->context = 'widget';

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo anwp_football_leagues()->template->shortcode_loader( 'premium-matchweeks-slides', (array) $data );
