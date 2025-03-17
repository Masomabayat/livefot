<?php
/**
 * The Template for displaying Stat Players widget.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/widget-premium-stat-players.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.12.0
 *
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check for required data
if ( empty( $data->stat ) ) {
	return;
}

$data->context = 'widget';

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo anwp_football_leagues()->template->shortcode_loader( 'premium-stat-players', (array) $data );
