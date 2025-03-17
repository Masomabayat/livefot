<?php
/**
 * The Template for displaying Calendar Slider.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-calendar-slider.php
 *
 * @var object $data - Object with shortcode data.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP-Football-Leagues-Premium/Templates
 *
 * @since            0.11.15
 * @version          0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'competition_id'       => '',
		'filter_by_clubs'      => 0,
		'no_data_text'         => '',
		'group_by_competition' => '',
		'competition_country'  => '', // country/country-flag/flag
		'show_day_of_week'     => 1,
		'day_leading_zero'     => 1,
		'month_text'           => 'short',
		'calendar_size'        => '',
		'competition_title'    => '',
		'competition_link'     => 1,
		'centered'             => 0,
		'day_width'            => '',
	]
);

$show_day_of_week = AnWP_Football_Leagues::string_to_bool( $args->show_day_of_week );
$day_leading_zero = AnWP_Football_Leagues::string_to_bool( $args->day_leading_zero );

/*
|--------------------------------------------------------------------------
| Get dates slides
|--------------------------------------------------------------------------
*/
$date_slides  = [];
$current_date = apply_filters( 'anwpfl/config/localize_date_arg', true ) ? date_i18n( 'Y-m-d' ) : date( 'Y-m-d' ); // phpcs:ignore
$date_obj     = DateTime::createFromFormat( 'Y-m-d H:i:s', $current_date . '00:00:00' );

if ( empty( $date_obj ) ) {
	return;
}

global $wp_locale;

$date_obj->sub( new DateInterval( 'P30D' ) );

for ( $ii = 0; $ii <= 120; $ii ++ ) {

	$date_obj->add( new DateInterval( 'P1D' ) );

	$date_slides[] =
		[
			'date'     => $date_obj->format( 'Y-m-d' ),
			'day'      => $day_leading_zero ? $date_obj->format( 'd' ) : $date_obj->format( 'j' ),
			'day_text' => $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( $date_obj->format( 'w' ) ) ),
			'month'    => 'short' === $args->month_text ? $wp_locale->get_month_abbrev( $wp_locale->get_month( $date_obj->format( 'm' ) ) ) : $wp_locale->get_month( $date_obj->format( 'm' ) ),
		];
}

// Load Swiper
wp_enqueue_script( 'anwp-fl-swiper' );

$short_names_map = [
	'competition_country'  => 'c_cy',
	'competition_link'     => 'c_lk',
	'competition_title'    => 'c_tl',
	'group_by_competition' => 'gr_by_c',
];

$url_data = [];

foreach ( $args as $d_key => $d_value ) {
	if ( isset( $short_names_map[ $d_key ] ) && '' !== $d_value ) {
		$url_data[ $short_names_map[ $d_key ] ] = $d_value;
	}
}
?>
<div class="anwp-b-wrap anwp-fl-calendar-slider anwp-fl-calendar-slider__size-<?php echo esc_attr( $args->calendar_size ); ?>"
	data-args="<?php echo esc_attr( wp_json_encode( $url_data ) ); ?>"
	data-fl-no-text="<?php echo esc_attr( $args->no_data_text ); ?>"
	data-fl-dates="<?php echo esc_attr( anwp_football_leagues_premium()->match->get_calendar_slider_games_dates( $args, $date_slides[0]['date'], $date_slides[ count( $date_slides ) - 1 ]['date'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
	data-fl-centered="<?php echo AnWP_Football_Leagues::string_to_bool( $args->centered ) ? 'yes' : ''; ?>" data-fl-day-width="<?php echo absint( $args->day_width ) ?: ''; ?>">
	<div class="anwp-fl-calendar-slider__swiper-container-outer anwpfl-not-ready">
		<div class="anwp-fl-calendar-slider__swiper-container">
			<div class="swiper-wrapper">

				<?php foreach ( $date_slides as $slide_index => $date_slide ) : ?>
					<div class="anwp-fl-calendar-slider__swiper-slide py-2" style="<?php echo absint( $args->day_width ) ? 'width: ' . absint( $args->day_width ) . 'px' : ''; ?>"
						data-date="<?php echo esc_html( $date_slide['date'] ); ?>"
						data-fl-index="<?php echo absint( $slide_index ); ?>">

						<div class="anwp-fl-calendar-slider__day anwp-text-3xl mt-2">
							<?php echo esc_html( $date_slide['day'] ); ?>
						</div>

						<div class="anwp-fl-calendar-slider__month-text anwp-text-base">
							<?php echo esc_html( $date_slide['month'] ); ?>
						</div>

						<?php if ( $show_day_of_week ) : ?>
							<div class="anwp-fl-calendar-slider__day-text anwp-text-xs"><?php echo esc_html( $date_slide['day_text'] ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>

			</div>
		</div>

		<div class="anwp-fl-calendar-slider__swiper-button-prev d-flex align-items-center justify-content-center user-select-none"> <</div>
		<div class="anwp-fl-calendar-slider__swiper-button-next d-flex align-items-center justify-content-center user-select-none"> ></div>
	</div>

	<div class="anwp-fl-calendar-slider__games mt-3 position-relative"></div>

</div>
