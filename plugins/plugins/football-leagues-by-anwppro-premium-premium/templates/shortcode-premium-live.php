<?php
/**
 * The Template for displaying Matches.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-matches.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.4.3
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'competition_id'        => '',
		'filter_by_clubs'       => '',
		'class'                 => '',
		'group_by'              => '',
		'group_by_header_style' => '',
		'show_club_logos'       => 1,
		'competition_logo'      => 1,
		'no_data_text'          => '',
		'layout'                => 'slim',
	]
);

$url_data = [];

foreach ( $data as $d_key => $d_value ) {
	if ( in_array( $d_key, [ 'competition_id', 'filter_by_clubs', 'group_by', 'group_by_header_style', 'show_club_logos', 'competition_logo', 'layout' ], true ) && '' !== $d_value ) {
		$url_data[ $d_key ] = $d_value;
	}
}

// Get competition matches
//$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $args );
?>
<div class="anwp-b-wrap match-list__outer-wrapper anwp-fl-live-games-shortcode">
	<div class="d-none anwp-fl-live-games-shortcode__empty">
		<?php
		if ( trim( $args->no_data_text ) ) {
			anwp_football_leagues()->load_partial(
				[
					'no_data_text' => $args->no_data_text,
				],
				'general/no-data'
			);
		}
		?>
	</div>
	<div class="match-list match-list--shortcode match-list--live-shortcode <?php echo esc_attr( $args->class ); ?>"
		data-args="<?php echo esc_attr( wp_json_encode( $url_data ) ); ?>"></div>
</div>
