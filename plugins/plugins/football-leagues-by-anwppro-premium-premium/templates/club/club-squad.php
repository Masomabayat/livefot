<?php
/**
 * The Template for displaying Club >> Squad Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/club/club-squad.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.8.4
 * @version       0.9.2
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
 * Hook: anwpfl/tmpl-club/before_squad
 *
 * @since 0.7.5
 *
 * @param WP_Post $club
 * @param integer $season_id
 */
do_action( 'anwpfl/tmpl-club/before_squad', $club, $data->season_id );

/**
 * Filter: anwpfl/tmpl-club/render_squad
 *
 * @since 0.7.5
 *
 * @param bool
 * @param WP_Post $club
 * @param integer $season_id
 */
if ( ! apply_filters( 'anwpfl/tmpl-club/render_squad', true, $club, $data->season_id ) ) {
	return;
}
?>
	<div class="club__squad club-section anwp-section" id="anwp-section-squad">

		<?php
		/**
		 * Filter: anwpfl/tmpl-club/squad_layout
		 *
		 * @since 0.7.5
		 *
		 * @param bool
		 * @param WP_Post $club
		 * @param integer $season_id
		 */
		$squad_layout = apply_filters( 'anwpfl/tmpl-club/squad_layout', anwp_football_leagues_premium()->customizer->get_value( 'squad', 'club_squad_layout' ), $club, $data->season_id );

		$data_squad = [
			'club_id'         => $data->club_id,
			'season_id'       => $data->season_id,
			'season_dropdown' => 'hide',
			'class'           => '',
			'layout'          => $squad_layout,
			'header'          => false,
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo anwp_football_leagues()->template->shortcode_loader( 'summary' === get_post_meta( $data->club_id, '_anwpfl_root_type', true ) ? 'squad-summary' : 'squad', $data_squad );
		?>
	</div>
<?php
/**
 * Hook: anwpfl/tmpl-club/after_squad
 *
 * @since 0.7.5
 *
 * @param WP_Post $club
 * @param integer $season_id
 */
do_action( 'anwpfl/tmpl-club/after_squad', $club, $data->season_id );
