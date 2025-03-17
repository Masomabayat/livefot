<?php
/**
 * The Template for displaying Match (plain version).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match--plain.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author         Andrei Strekozov <anwp.pro>
 * @package        AnWP-Football-Leagues/Templates
 * @since          0.14.8
 *
 * @version        0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$data,
	[
		'club_home_title' => '',
		'club_away_title' => '',
		'permalink'       => '',
	]
);
?>
<div class="match-plain">
	<a class="anwp-fl-link" href="<?php echo esc_url( $data['permalink'] ); ?>"><?php echo esc_html( $data['club_home_title'] ); ?> - <?php echo esc_html( $data['club_away_title'] ); ?></a>
</div>
