<?php
/**
 * The Template for displaying Timezone switcher
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-timezones.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP-Football-Leagues/Templates
 * @since            0.11.2
 *
 * @version          0.14.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( 'yes' !== AnWPFL_Premium_Options::get_value( 'user_auto_timezone' ) ) {
	return;
}

static $render_modaal_once = false;

// Merge with default params
$data = (object) wp_parse_args(
	$data,
	[
		'clock_icon'                 => 1,
		'default_classes'            => 1,
		'text'                       => '',
		'text_utc'                   => '',
		'text_auto'                  => '',
		'text_save'                  => '',
		'text_change_your_time_zone' => '',
		'custom_classes'             => '',
	]
);

$timezones = anwp_football_leagues_premium()->data->get_timezone_offsets();

if ( AnWP_Football_Leagues::string_to_bool( $data->default_classes ) ) {
	$btn_classes = [
		'anwp-rounded',
		'anwp-border',
		'anwp-border-light',
		'anwp-fl-timezone__btn',
	];
} else {
	$btn_classes = [];
}

if ( $data->custom_classes ) {
	$btn_classes[] = $data->custom_classes;
}
?>

<div class="anwp-fl-timezone d-none">
	<div class="anwp-cursor-pointer px-3 anwp-fl-timezone__btn d-flex align-items-center <?php echo esc_attr( implode( ' ', $btn_classes ) ); ?>">
		<?php if ( AnWP_Football_Leagues::string_to_bool( $data->clock_icon ) ) : ?>
			<svg class="match-small__date-icon anwp-icon anwp-icon--feather anwp-icon--s14 ml-auto mr-2">
				<use xlink:href="#icon-clock-alt"></use>
			</svg>
		<?php endif; ?>

		<?php if ( $data->text ) : ?>
			<span class="anwp-fl-timezone__btn-text"><?php echo esc_html( $data->text ); ?></span>
		<?php endif; ?>
		<span class="anwp-fl-timezone__tz_utc mr-1"><?php echo esc_html( $data->text_utc ); ?></span>
		<span class="anwp-fl-timezone__tz"></span>
	</div>
</div>
<?php
if ( ! $render_modaal_once ) :
	$render_modaal_once = true;
	?>
	<div id="anwp-modaal-timezone" class="anwp-fl-modal" aria-hidden="true">
		<div class="anwp-fl-modal__overlay" tabindex="-1" data-micromodal-close>
			<div class="anwp-fl-modal__container anwp-b-wrap anwp-overflow-y-auto anwp-w-400" role="dialog" aria-modal="true">
				<button class="anwp-fl-modal__close" aria-label="Close modal" type="button" data-micromodal-close></button>

				<div class="anwp-fl-timezone-selector anwp-text-center">
					<div class="mb-3 anwp-text-lg"><?php echo esc_html( $data->text_change_your_time_zone ); ?>:</div>

					<div class="d-flex align-self-center justify-content-center">
						<select id="anwp-fl-timezone-selector__select" class="mr-3">
							<option value="">- <?php echo esc_html( $data->text_auto ); ?> -</option>
							<?php foreach ( $timezones as $offset => $timezone ) : ?>
								<option value="<?php echo esc_attr( $offset ); ?>"><?php echo esc_html( $timezone ); ?></option>
							<?php endforeach; ?>
						</select>

						<button class="button anwp-fl-timezone-selector__save" type="button"><?php echo esc_html( $data->text_save ); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
endif;
