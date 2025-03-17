<?php
/**
 * The Template for displaying Referee Stats.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-referee-stats.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         AnWP-Football-Leagues/Templates
 * @since           0.14.7
 *
 * @version         0.14.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'referee_id'     => '',
		'competition_id' => '',
		'season_id'      => '',
		'league_id'      => '',
		'show_secondary' => 0,
		'per_game'       => 1,
		'block_width'    => 1,
		'stats'          => '',
		'class'          => '',
		'header'         => '',
		'layout'         => '',
		'notes'          => '',
		'show_games'     => 1,
		'type'           => 'result',
	]
);

if ( ! absint( $args->referee_id ) ) {
	return;
}

$referee_games = anwp_football_leagues()->referee->get_referee_games( $args, 'stats' );

if ( empty( $referee_games ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Prepare Game Stats
|--------------------------------------------------------------------
*/
$stat_values = [
	'card_y'   => 0,
	'card_y_h' => 0,
	'card_y_a' => 0,
	'card_r'   => 0,
	'card_r_h' => 0,
	'card_r_a' => 0,
	'foul'     => 0,
	'foul_h'   => 0,
	'foul_a'   => 0,
];

$yr_count = AnWPFL_Options::get_value( 'yr_card_count', 'r' );

foreach ( $referee_games as $referee_game ) {
	$stat_values['card_y_h'] += absint( $referee_game->home_cards_y ) + ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? absint( $referee_game->home_cards_yr ) : 0 );
	$stat_values['card_y_a'] += absint( $referee_game->away_cards_y ) + ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? absint( $referee_game->away_cards_yr ) : 0 );
	$stat_values['card_r_h'] += absint( $referee_game->home_cards_r ) + ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? absint( $referee_game->home_cards_yr ) : 0 );
	$stat_values['card_r_a'] += absint( $referee_game->away_cards_r ) + ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? absint( $referee_game->away_cards_yr ) : 0 );
	$stat_values['foul_h']   += absint( $referee_game->home_fouls );
	$stat_values['foul_a']   += absint( $referee_game->away_fouls );
}

$stat_values['card_y'] = $stat_values['card_y_h'] + $stat_values['card_y_a'];
$stat_values['card_r'] = $stat_values['card_r_h'] + $stat_values['card_r_a'];
$stat_values['foul']   = $stat_values['foul_h'] + $stat_values['foul_a'];

/*
|--------------------------------------------------------------------
| Populate Stats
|--------------------------------------------------------------------
*/
if ( empty( $args->stats ) ) {
	$stats = [ 'card_y', 'card_y_h', 'card_y_a', 'card_r', 'card_r_h', 'card_r_a' ];
} else {
	$stats = wp_parse_slug_list( $args->stats );
}

// Text Strings
$text_strings = [
	'card_y'    => AnWPFL_Text::get_value( 'stats__referee__card_y', __( 'Yellow Cards', 'anwp-football-leagues-premium' ) ),
	'card_y_h'  => AnWPFL_Text::get_value( 'stats__referee__card_y_h', __( 'Yellow Cards (Home)', 'anwp-football-leagues-premium' ) ),
	'card_y_a'  => AnWPFL_Text::get_value( 'stats__referee__card_y_a', __( 'Yellow Cards (Away)', 'anwp-football-leagues-premium' ) ),
	'card_r'    => AnWPFL_Text::get_value( 'stats__referee__card_r', __( 'Red Cards', 'anwp-football-leagues-premium' ) ),
	'card_r_h'  => AnWPFL_Text::get_value( 'stats__referee__card_r_h', __( 'Red Cards (Home)', 'anwp-football-leagues-premium' ) ),
	'card_r_a'  => AnWPFL_Text::get_value( 'stats__referee__card_r_a', __( 'Red Cards (Away)', 'anwp-football-leagues-premium' ) ),
	'foul'      => AnWPFL_Text::get_value( 'stats__referee__foul', __( 'Fouls', 'anwp-football-leagues-premium' ) ),
	'foul_h'    => AnWPFL_Text::get_value( 'stats__referee__foul_h', __( 'Fouls (Home)', 'anwp-football-leagues-premium' ) ),
	'foul_a'    => AnWPFL_Text::get_value( 'stats__referee__foul_a', __( 'Fouls (Away)', 'anwp-football-leagues-premium' ) ),
	'per_match' => AnWPFL_Text::get_value( 'stats__referee__per_match', __( 'Per Match', 'anwp-football-leagues-premium' ) ),
	'games'     => AnWPFL_Text::get_value( 'stats__referee__games', __( 'Games', 'anwp-football-leagues-premium' ) ),
];

$available_stats = [
	'card_y'   => [
		'text' => $text_strings['card_y'],
		'icon' => 'card_y',
	],
	'card_y_h' => [
		'text' => $text_strings['card_y_h'],
		'icon' => 'card_y',
	],
	'card_y_a' => [
		'text' => $text_strings['card_y_a'],
		'icon' => 'card_y',
	],
	'card_r'   => [
		'text' => $text_strings['card_r'],
		'icon' => 'card_r',
	],
	'card_r_h' => [
		'text' => $text_strings['card_r_h'],
		'icon' => 'card_r',
	],
	'card_r_a' => [
		'text' => $text_strings['card_r_a'],
		'icon' => 'card_r',
	],
	'foul'     => [
		'text' => $text_strings['foul'],
		'icon' => 'whistle',
	],
	'foul_h'   => [
		'text' => $text_strings['foul_h'],
		'icon' => 'whistle',
	],
	'foul_a'   => [
		'text' => $text_strings['foul_a'],
		'icon' => 'whistle',
	],
];

$block_width = absint( $args->block_width ) > 10 ? $args->block_width : 160;
?>
<div class="anwp-b-wrap anwp-fl-referee-stats-shortcode referee-stats <?php echo esc_attr( $args->class ); ?>">

	<?php
	if ( ! empty( $args->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => $args->header,
			],
			'general/header'
		);
	endif;

	if ( AnWP_Football_Leagues::string_to_bool( $args->show_games ) ) :
		?>
		<div class="referee-stats__games-num-wrapper anwp-text-base mb-1">
			<?php echo esc_html( $text_strings['games'] ); ?> -
			<span class="referee-stats__games-num anwp-font-semibold"><?php echo absint( count( $referee_games ) ); ?></span>
		</div>
	<?php endif; ?>

	<div class="referee-stats__wrapper anwp-grid-table" style="--referee-stats-block-width: <?php echo absint( $block_width ); ?>px;">
		<?php
		foreach ( $stats as $stat ) :
			if ( empty( $available_stats[ $stat ] ) || ! isset( $stat_values[ $stat ] ) ) {
				continue;
			}
			?>
			<div class="referee-stats__item d-flex flex-column anwp-border-light anwp-text-center p-1">
				<div class="referee-stats__stat d-flex justify-content-center">
					<svg class="icon__ball mr-2 anwp-flex-none">
						<use xlink:href="#icon-<?php echo esc_attr( $available_stats[ $stat ]['icon'] ); ?>"></use>
					</svg>
					<span class="anwp-text-xs anwp-leading-1 align-self-center"><?php echo esc_html( $available_stats[ $stat ]['text'] ); ?></span>
				</div>
				<div class="referee-stats__value anwp-text-4xl my-2"><?php echo esc_html( $stat_values[ $stat ] ); ?></div>
				<?php if ( AnWP_Football_Leagues::string_to_bool( $args->per_game ) ) : ?>
					<div class="referee-stats__pg anwp-text-xl"><?php echo esc_html( round( $stat_values[ $stat ] / count( $referee_games ), 1 ) ); ?></div>
					<div class="referee-stats__pg-text anwp-text-xxs anwp-opacity-70 anwp-leading-1"><?php echo esc_html( $text_strings['per_match'] ); ?></div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $args->notes ) : ?>
		<div class="referee-stats__notes anwp-text-xxs mt-1"><?php echo $args->notes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php endif; ?>
</div>
