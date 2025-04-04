<?php
/**
 * The Template for displaying Widget :: Birthday.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/widget-birthday.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         AnWP-Football-Leagues/Templates
 * @since           0.10.19
 *
 * @version         0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = wp_parse_args(
	$data,
	[
		'club_id'       => '',
		'type'          => 'players',
		'days_before'   => 5,
		'days_after'    => 3,
		'group_by_date' => 0,
		'layout'        => '',
		'cache'         => 'v3',
	]
);

$players = anwp_fl()->player->get_birthdays( $data );
?>
<div class="anwp-b-wrap">
	<?php
	if ( empty( $players ) ) {
		anwp_fl()->load_partial(
			[
				'no_data_text' => AnWPFL_Text::get_value( 'birthdays__widget__no_upcoming_birthdays', __( 'No Upcoming Birthdays', 'anwp-football-leagues' ) ),
			],
			'general/no-data'
		);
	} else {

		$date_title = '';

		foreach ( $players as $player ) {

			if ( AnWP_Football_Leagues::string_to_bool( $data['group_by_date'] ) && $date_title !== $player->meta_date_short ) {
				ob_start();
				?>
				<div class="player-birthday-card__date-subtitle d-flex align-items-center anwp-bg-light py-1 px-2">
					<svg class="anwp-icon anwp-icon--octi mr-1">
						<use xlink:href="#icon-calendar"></use>
					</svg>
					<div class="player-birthday-card__date-subtitle-text anwp-text-base">
						<?php echo esc_html( date_i18n( 'M d', get_date_from_gmt( gmdate( 'Y' ) . '-' . $player->meta_date_short, 'U' ) ) ); ?>
					</div>
				</div>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo ob_get_clean();

				$date_title = $player->meta_date_short;
			}

			anwp_fl()->load_partial( $player, 'player/player-birthday-card', $data['layout'] );
		}
	}
	?>
</div>
