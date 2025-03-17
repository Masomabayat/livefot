<?php
/**
 * The Template for displaying Player Stats.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-player-stats-panel.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         AnWP-Football-Leagues/Templates
 * @since           0.14.9
 *
 * @version         0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = wp_parse_args(
	$data,
	[
		'player_id'      => '',
		'competition_id' => '',
		'season_id'      => '',
		'league_id'      => '',
		'club_id'        => '',
		'show_secondary' => 1,
		'per_game'       => 1,
		'block_width'    => 160,
		'stats'          => '',
		'class'          => '',
		'header'         => '',
		'layout'         => '',
		'notes'          => '',
		'date_from'      => '',
		'date_to'        => '',
		'season_text'    => 0,
	]
);

if ( ! absint( $args['player_id'] ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Populate Stats
|--------------------------------------------------------------------
*/
$stats = wp_parse_slug_list( $args['stats'] );

if ( empty( $stats ) ) {
	return;
}

$player_statistics = anwp_football_leagues_premium()->player->get_player_statistics( $args );

if ( empty( $player_statistics ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Text Strings
|--------------------------------------------------------------------
*/
$available_stats = [
	'played'         => [
		'text'       => AnWPFL_Text::get_value( 'player__stats_panel__games_played', __( 'Games Played', 'anwp-football-leagues-premium' ) ),
		'icon'       => 'field',
		'icon_class' => 'anwp-icon--trans',
	],
	'started'        => [
		'text'       => AnWPFL_Text::get_value( 'player__stats_panel__games_started', __( 'Games Started', 'anwp-football-leagues-premium' ) ),
		'icon'       => 'field-shirt',
		'icon_class' => 'anwp-icon--trans',
	],
	'sub_in'         => [
		'text'       => AnWPFL_Text::get_value( 'player__stats_panel__sub_in', __( 'Substituted In', 'anwp-football-leagues' ) ),
		'icon'       => 'field-shirt-in',
		'icon_class' => 'anwp-icon--trans',
	],
	'minutes'        => [
		'text'       => AnWPFL_Text::get_value( 'player__stats_panel__minutes', __( 'Minutes', 'anwp-football-leagues' ) ),
		'icon'       => 'watch',
		'icon_class' => 'anwp-icon--gray-900',
	],
	'goals'          => [
		'text' => AnWPFL_Text::get_value( 'player__stats_panel__goals', __( 'Goals', 'anwp-football-leagues' ) ),
		'icon' => 'ball',
	],
	'assists'        => [
		'text'       => AnWPFL_Text::get_value( 'player__stats_panel__assists', __( 'Assists', 'anwp-football-leagues' ) ),
		'icon'       => 'ball',
		'icon_class' => 'anwp-opacity-50',
	],
	'goals_conceded' => [
		'text'       => AnWPFL_Text::get_value( 'player__stats_panel__goals_conceded', __( 'Goals Conceded', 'anwp-football-leagues' ) ),
		'icon'       => 'ball',
		'icon_class' => 'icon__ball--conceded',
	],
	'clean_sheets'   => [
		'text' => AnWPFL_Text::get_value( 'player__stats_panel__clean_sheets', __( 'Clean Sheets', 'anwp-football-leagues' ) ),
		'icon' => 'ball_canceled',
	],
	'cards_all'      => [
		'text' => AnWPFL_Text::get_value( 'player__stats_panel__cards_all', __( 'Cards (All)', 'anwp-football-leagues-premium' ) ),
		'icon' => 'card_yr',
	],
	'card_y'         => [
		'text' => AnWPFL_Text::get_value( 'player__stats_panel__card_y', __( 'Cards - Yellow', 'anwp-football-leagues-premium' ) ),
		'icon' => 'card_y',
	],
	'card_r'         => [
		'text' => AnWPFL_Text::get_value( 'player__stats_panel__card_r', __( 'Cards - Red', 'anwp-football-leagues-premium' ) ),
		'icon' => 'card_r',
	],
	'card_yr'        => [
		'text' => AnWPFL_Text::get_value( 'player__stats_panel__card_yr', __( 'Cards - 2nd Yellow/Red', 'anwp-football-leagues-premium' ) ),
		'icon' => 'card_yr',
	],
	'goals_penalty'  => [
		'text' => AnWPFL_Text::get_value( 'player__stats_panel__goals_penalty', __( 'Goal From Penalty', 'anwp-football-leagues' ) ),
		'icon' => 'ball_penalty',
	],
	'own_goals'      => [
		'text'       => AnWPFL_Text::get_value( 'player__stats_panel__own_goals', __( 'Own Goals', 'anwp-football-leagues' ) ),
		'icon'       => 'ball',
		'icon_class' => 'icon__ball--own',
	],
];

$block_width   = absint( $args['block_width'] ) > 10 ? $args['block_width'] : 160;
$stats_columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ) );
$rating_field  = AnWPFL_Premium_Options::get_value( 'player_rating' );

if ( in_array( 'cards_all', $stats, true ) ) {
	$player_statistics['cards_all'] = absint( $player_statistics['card_y'] ) + absint( $player_statistics['card_yr'] ) + absint( $player_statistics['card_r'] );
}
?>
<div class="anwp-b-wrap anwp-fl-player-stats-shortcode player-stats-pro <?php echo esc_attr( $args['class'] ); ?>">

	<?php
	if ( ! empty( $args['header'] ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => $args['header'],
			],
			'general/header'
		);
	endif;

	if ( AnWP_Football_Leagues::string_to_bool( $args['season_text'] ) && $args['season_id'] ) :
		?>
		<div class="player-stats-pro__season-wrapper anwp-text-base mb-1">
			<span class="player-stats-pro__season anwp-font-semibold">
				<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats_panel__season', __( 'Season', 'anwp-football-leagues' ) ) ); ?>:
			</span>
			<?php echo esc_html( get_term_by( 'id', intval( $args['season_id'] ), 'anwp_season' )->name ); ?>
		</div>
	<?php endif; ?>

	<div class="player-stats-pro__wrapper anwp-grid-table" style="--player-stats-block-width: <?php echo absint( $block_width ); ?>px;">
		<?php
		foreach ( $stats as $stat ) :
			if ( absint( $stat ) && isset( $player_statistics[ 'c_id__' . $stat ] ) ) {
				$stat = 'c_id__' . $stat;
			} elseif ( ! isset( $player_statistics[ $stat ] ) ) {
				continue;
			}
			?>
			<div class="player-stats-pro__item d-flex flex-column anwp-border-light anwp-text-center py-2 px-1">
				<div class="player-stats-pro__stat d-flex justify-content-center anwp-h-20">
					<?php if ( isset( $available_stats[ $stat ]['icon'] ) ) : ?>
						<svg class="icon__ball mr-2 anwp-flex-none <?php echo esc_attr( $available_stats[ $stat ]['icon_class'] ?? '' ); ?>">
							<use xlink:href="#icon-<?php echo esc_attr( $available_stats[ $stat ]['icon'] ); ?>"></use>
						</svg>
					<?php endif; ?>
					<span class="anwp-text-xs anwp-leading-1 align-self-center">
						<?php
						if ( isset( $available_stats[ $stat ]['text'] ) ) {
							echo esc_html( $available_stats[ $stat ]['text'] );
						} elseif ( absint( str_replace( 'c_id__', '', $stat ) ) ) {
							if ( isset( anwp_fl_pro()->stats->get_stats_player_match_column_by_id( str_replace( 'c_id__', '', $stat ) )['name'] ) ) {
								echo esc_html( anwp_fl_pro()->stats->get_stats_player_match_column_by_id( str_replace( 'c_id__', '', $stat ) )['name'] );
							}
						}
						?>
					</span>
				</div>
				<div class="player-stats-pro__value anwp-text-4xl mt-2">
					<?php
					if ( absint( str_replace( 'c_id__', '', $stat ) ) && absint( str_replace( 'c_id__', '', $stat ) ) === absint( $rating_field ) ) {
						echo esc_html( $player_statistics[ $stat . '__qty' ] ? number_format( $player_statistics[ $stat ] / $player_statistics[ $stat . '__qty' ], 1 ) : '' );
					} else {
						echo esc_html( $player_statistics[ $stat ] );
					}
					?>
				</div>
				<?php
				if ( AnWP_Football_Leagues::string_to_bool( $args['per_game'] ) ) :
					if ( ( absint( str_replace( 'c_id__', '', $stat ) ) && absint( str_replace( 'c_id__', '', $stat ) ) === absint( $rating_field ) ) || 'played' === $stat ) {
						$per_game_stat = '-';
					} elseif ( ! absint( $player_statistics[ $stat ] ) || ! absint( $player_statistics['played'] ) ) {
						$per_game_stat = 0;
					} else {
						$per_game_stat = number_format( $player_statistics[ $stat ] / $player_statistics['played'], 1 );

						if ( '0.0' === $per_game_stat ) {
							$per_game_stat = number_format( $player_statistics[ $stat ] / $player_statistics['played'], 2 );
						}

						if ( '0.00' === $per_game_stat ) {
							$per_game_stat = '-';
						}
					}
					?>
					<div class="player-stats-pro__pg anwp-text-xl mt-1"><?php echo esc_html( $per_game_stat ); ?></div>
					<div class="player-stats-pro__pg-text anwp-text-xxs anwp-opacity-70 anwp-leading-1">
						<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats_panel__per_game', __( 'Per Game', 'anwp-football-leagues' ) ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $args['notes'] ) : ?>
		<div class="player-stats-pro__notes anwp-text-xxs mt-1"><?php echo $args['notes']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php endif; ?>
</div>
