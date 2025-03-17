<?php
/**
 * The Template for displaying Calendar Matches.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/widget-premium-calendar.php
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.5.10 (Premium)
 * @version       0.14.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// Prevent errors with new params
$data = (object) wp_parse_args(
	$data,
	[
		'competition_id'       => '',
		'show_secondary'       => '',
		'club_id'              => '',
		'show_club_logos'      => 1,
		'show_club_name'       => 1,
		'group_by_time'        => 0,
		'group_by_competition' => 0,
		'club_links'           => true,
		'layout'               => 'simple',
	]
);

unset( $data->before_widget );
unset( $data->after_widget );
unset( $data->before_title );
unset( $data->after_title );
unset( $data->title );

wp_enqueue_style( 'flatpickr' );
wp_enqueue_style( 'flatpickr-airbnb' );
wp_enqueue_script( 'flatpickr' );

$current_date = apply_filters( 'anwpfl/config/localize_date_arg', true ) ? date_i18n( 'Y-m-d' ) : date( 'Y-m-d' );

$short_names_map = [
	'club_id'              => 'cl_id',
	'club_links'           => 'c_l',
	'competition_id'       => 'c_id',
	'group_by_competition' => 'g_c',
	'group_by_time'        => 'g_t',
	'layout'               => 'l',
	'show_club_logos'      => 's_c_l',
	'show_club_name'       => 's_c_n',
	'show_secondary'       => 's_s',
];

$url_data = [];

foreach ( $data as $d_key => $d_value ) {
	if ( isset( $short_names_map[ $d_key ] ) && '' !== $d_value ) {
		$url_data[ $short_names_map[ $d_key ] ] = $d_value;
	}
}
?>
<div class="anwp-b-wrap anwp-fl-calendar-widget-wrap-outer">
	<input type="text" class="form-control anwp-fl-calendar-widget-wrap d-none">
	<div class="match-list match-list--widget mt-3 layout--<?php echo esc_attr( $data->layout ); ?>">
		<div class="anwp-fl-calendar-matches-wrapper"
			data-args="<?php echo esc_attr( wp_json_encode( $url_data ) ); ?>"
			data-fl-today="<?php echo esc_attr( $current_date ); ?>"></div>
		<div class="anwp-calendar-loading anwp-text-center my-3 d-none d-print-none">
			<img src="<?php echo esc_url( admin_url( '/images/spinner.gif' ) ); ?>">
		</div>
	</div>
</div>
