<?php
/**
 * The Template for displaying Match >> Widget Shots.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-widget-shots.php.
 *
 * // phpcs:disable WordPress.NamingConventions.ValidVariableName
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.11.10
 *
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$data,
	[
		'color_home'         => '',
		'color_away'         => '',
		'home_shots'         => '',
		'away_shots'         => '',
		'home_shots_on_goal' => '',
		'away_shots_on_goal' => '',
	]
);

$value_home_all = absint( $data['home_shots'] );
$value_away_all = absint( $data['away_shots'] );
$value_home_on  = absint( $data['home_shots_on_goal'] );
$value_away_on  = absint( $data['away_shots_on_goal'] );

if ( ! $value_home_all && ! $value_away_all && ! $value_home_on && ! $value_away_on ) {
	return;
}
?>
<div class="my-3">
	<div class="mx-auto d-flex anwp-text-white anwp-match-widget-shots">
		<div class="w-50 d-flex position-relative anwp-match-widget-shots__block-outer">
			<div class="w-50 d-flex flex-column pb-5 pl-1 anwp-match-widget-shots__block-off">
				<span class="anwp-text-xs"><?php echo esc_html( AnWPFL_Text::get_value( 'match__stats__off_target', __( 'Off Target', 'anwp-football-leagues-premium' ) ) ); ?></span>
				<span class="anwp-match-widget-shots__value anwp-text-base"><?php echo absint( $value_home_all - $value_home_on ); ?></span>
			</div>
			<div class="w-50 d-flex flex-column justify-content-end mt-5 pl-1 anwp-match-widget-shots__block-on anwp-match-widget-shots__block-on-home" style="background-color: <?php echo esc_attr( $data['color_home'] ); ?>">
				<span class="anwp-text-xs"><?php echo esc_html( AnWPFL_Text::get_value( 'match__stats__on_target', __( 'On Target', 'anwp-football-leagues-premium' ) ) ); ?></span>
				<span class="anwp-match-widget-shots__value anwp-text-base mb-3"><?php echo absint( $value_home_on ); ?></span>
			</div>
			<span class="anwp-position-cover anwp-match-widget-shots__bg" style="background-color: <?php echo esc_attr( $data['color_home'] ); ?>"></span>
		</div>
		<div class="w-50 d-flex position-relative anwp-match-widget-shots__block-outer">
			<div class="w-50 d-flex flex-column justify-content-end align-items-end mt-5 pr-1 anwp-match-widget-shots__block-on anwp-match-widget-shots__block-on-away" style="background-color: <?php echo esc_attr( $data['color_away'] ); ?>">
				<span class="anwp-text-xs"><?php echo esc_html( AnWPFL_Text::get_value( 'match__stats__on_target', __( 'On Target', 'anwp-football-leagues-premium' ) ) ); ?></span>
				<span class="anwp-match-widget-shots__value anwp-text-base mb-3"><?php echo absint( $value_away_on ); ?></span>
			</div>
			<div class="w-50 d-flex flex-column pb-5 align-items-end pr-1 anwp-match-widget-shots__block-off">
				<span class="anwp-text-xs"><?php echo esc_html( AnWPFL_Text::get_value( 'match__stats__off_target', __( 'Off Target', 'anwp-football-leagues-premium' ) ) ); ?></span>
				<span class="anwp-match-widget-shots__value anwp-text-base"><?php echo absint( $value_away_all - $value_away_on ); ?></span>
			</div>
			<span class="anwp-position-cover anwp-match-widget-shots__bg" style="background-color: <?php echo esc_attr( $data['color_away'] ); ?>"></span>
		</div>
	</div>
</div>
