<?php
/**
 * The Template for displaying Match Countdown - Modern Layout.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-countdown--modern.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.15.0
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( 'hide' === anwp_football_leagues()->customizer->get_value( 'match', 'fixture_flip_countdown' ) ) {
	return;
}

if ( 'classic' === anwp_football_leagues()->customizer->get_value( 'match', 'countdown_layout' ) ) {
	anwp_football_leagues()->load_partial( $data, 'match/match-countdown' );
	return;
}

$data = (object) wp_parse_args(
	$data,
	[
		'kickoff'        => '',
		'kickoff_c'      => '',
		'special_status' => '',
		'context'        => '',
	]
);

if ( '0000-00-00 00:00:00' === $data->kickoff || ! $data->kickoff || in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) {
	return;
}

$kickoff_diff = ( date_i18n( 'U', get_date_from_gmt( $data->kickoff, 'U' ) ) - date_i18n( 'U' ) ) > 0 ? date_i18n( 'U', get_date_from_gmt( $data->kickoff, 'U' ) ) - date_i18n( 'U' ) : 0;

if ( $kickoff_diff > 0 ) :

	$label_text = [
		'days'    => AnWPFL_Text::get_value( 'data__flip_countdown__days', esc_html_x( 'days', 'flip countdown', 'anwp-football-leagues' ) ),
		'hours'   => AnWPFL_Text::get_value( 'data__flip_countdown__hours', esc_html_x( 'hours', 'flip countdown', 'anwp-football-leagues' ) ),
		'minutes' => AnWPFL_Text::get_value( 'data__flip_countdown__minutes', esc_html_x( 'minutes', 'flip countdown', 'anwp-football-leagues' ) ),
		'seconds' => AnWPFL_Text::get_value( 'data__flip_countdown__seconds', esc_html_x( 'seconds', 'flip countdown', 'anwp-football-leagues' ) ),
	];

	?>
	<div class="anwp-text-center <?php echo esc_attr( 'widget' === $data->context ? 'py-2' : 'py-3' ); ?> anwp-fl-pro-game-countdown anwp-fl-pro-game-countdown--<?php echo esc_attr( $data->context ); ?> mx-auto d-none"
		data-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>">
		<div class="d-flex flex-wrap justify-content-center anwp-fl-pro-game-countdown__inner">
			<?php foreach ( [ 'days', 'hours', 'minutes', 'seconds' ] as $time_slug ) : ?>
				<div class="anwp-fl-pro-game-countdown__item anwp-fl-pro-game-countdown__<?php echo esc_attr( $time_slug ); ?>">
					<svg class="anwp-fl-pro-game-countdown__svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
						<g class="anwp-fl-pro-game-countdown__circle">
							<circle class="anwp-fl-pro-game-countdown__path-elapsed" cx="50" cy="50" r="45" />
							<path
								stroke-dasharray="283"
								class="anwp-fl-pro-game-countdown__path-remaining"
								d="
									M 50, 50
									m -45, 0
									a 45,45 0 1,0 90,0
									a 45,45 0 1,0 -90,0
								"
							></path>
						</g>
					</svg>
					<div class="anwp-fl-pro-game-countdown__label-wrapper d-flex flex-column">
						<div class="anwp-fl-pro-game-countdown__label anwp-leading-1">
							<?php echo esc_html( $label_text[ $time_slug ] ); ?>
						</div>
						<div class="anwp-fl-pro-game-countdown__value anwp-fl-pro-game-countdown__value-<?php echo esc_attr( $time_slug ); ?> anwp-leading-1"></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
