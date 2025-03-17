<?php
/**
 * The Template for displaying Club Stats.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-stats-club.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         AnWP-Football-Leagues/Templates
 * @since           0.12.4
 *
 * @version         0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'competition_id' => '',
		'season_id'      => '',
		'league_id'      => '',
		'multistage'     => 0,
		'club_id'        => '',
		'date_before'    => '',
		'per_game'       => 1,
		'stats'          => '',
		'class'          => '',
		'header'         => '',
		'caching_time'   => '',
	]
);

if ( ! absint( $args->club_id ) ) {
	return;
}

$args->stats = wp_parse_slug_list( $args->stats );
$per_game    = AnWP_Football_Leagues::string_to_bool( $args->per_game );
$stats_data  = anwp_football_leagues_premium()->club->get_club_stats( $args );

if ( empty( $stats_data ) ) {
	return;
}

// Text Strings
$text_strings = [
	'home'           => AnWPFL_Text::get_value( 'stats__club__home', __( 'Home', 'anwp-football-leagues' ) ),
	'away'           => AnWPFL_Text::get_value( 'stats__club__away', __( 'Away', 'anwp-football-leagues' ) ),
	'all'            => AnWPFL_Text::get_value( 'stats__club__all', __( 'All', 'anwp-football-leagues' ) ),
	'total'          => AnWPFL_Text::get_value( 'stats__h2h__total', __( 'Total', 'anwp-football-leagues' ) ),
	'per_match'      => AnWPFL_Text::get_value( 'stats__h2h__per_match', __( 'Per Match', 'anwp-football-leagues' ) ),
	'wins'           => AnWPFL_Text::get_value( 'stats__h2h__wins', __( 'Wins', 'anwp-football-leagues' ) ),
	'draws'          => AnWPFL_Text::get_value( 'stats__h2h__draws', __( 'Draws', 'anwp-football-leagues-premium' ) ),
	'losses'         => AnWPFL_Text::get_value( 'stats__h2h__losses', __( 'Losses', 'anwp-football-leagues-premium' ) ),
	'played'         => AnWPFL_Text::get_value( 'stats__h2h__played', __( 'Played', 'anwp-football-leagues' ) ),
	'corners'        => AnWPFL_Text::get_value( 'stats__h2h__corners', __( 'Corners', 'anwp-football-leagues' ) ),
	'fouls'          => AnWPFL_Text::get_value( 'stats__h2h__fouls', __( 'Fouls', 'anwp-football-leagues' ) ),
	'offsides'       => AnWPFL_Text::get_value( 'stats__h2h__offsides', __( 'Offsides', 'anwp-football-leagues' ) ),
	'shots'          => AnWPFL_Text::get_value( 'stats__h2h__shots', __( 'Shots', 'anwp-football-leagues' ) ),
	'shots_on_goal'  => AnWPFL_Text::get_value( 'stats__h2h__shots_on_goal', __( 'Shots on Goal', 'anwp-football-leagues' ) ),
	'cards_y'        => AnWPFL_Text::get_value( 'stats__h2h__cards_y', __( 'Yellow Cards', 'anwp-football-leagues' ) ),
	'cards_r'        => AnWPFL_Text::get_value( 'stats__h2h__cards_r', __( 'Red Cards', 'anwp-football-leagues' ) ),
	'goals'          => AnWPFL_Text::get_value( 'stats__h2h__goals', __( 'Goals', 'anwp-football-leagues' ) ),
	'goals_conceded' => AnWPFL_Text::get_value( 'stats__h2h__goals_conceded', __( 'Goals Conceded', 'anwp-football-leagues' ) ),
	'clean_sheets'   => AnWPFL_Text::get_value( 'stats__h2h__clean_sheets', __( 'Clean Sheets', 'anwp-football-leagues' ) ),
];
?>
<div class="anwp-b-wrap anwp-fl-stats-club-shortcode">

	<?php
	if ( ! empty( $args->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => $args->header,
			],
			'general/header'
		);
	endif;
	?>

	<div class="table-responsive">
		<table class="table table-sm m-0 w-100 table-bordered anwp-text-center anwp-border-0 anwp-text-sm">

			<thead class="anwp-text-xs">
				<tr>
					<td class="w-50"></td>
					<td colspan="<?php echo $per_game ? 2 : 1; ?>"><?php echo esc_html( $text_strings['home'] ); ?></td>
					<td colspan="<?php echo $per_game ? 2 : 1; ?>"><?php echo esc_html( $text_strings['away'] ); ?></td>
					<td colspan="<?php echo $per_game ? 2 : 1; ?>"><?php echo esc_html( $text_strings['all'] ); ?></td>
				</tr>
			</thead>

			<tbody>
				<?php foreach ( [ 'played', 'wins', 'draws', 'losses' ] as $row_index => $default_stat ) : ?>
					<tr class="<?php echo esc_attr( ( 0 !== $row_index % 2 ) ? 'anwp-bg-light' : '' ); ?>">
						<td class="anwp-text-left"><?php echo esc_html( $text_strings[ $default_stat ] ); ?></td>
						<td colspan="<?php echo $per_game ? 2 : 1; ?>"><?php echo esc_html( $stats_data[ $default_stat ]['h'] ); ?></td>
						<td colspan="<?php echo $per_game ? 2 : 1; ?>"><?php echo esc_html( $stats_data[ $default_stat ]['a'] ); ?></td>
						<td colspan="<?php echo $per_game ? 2 : 1; ?>"><?php echo esc_html( $stats_data[ $default_stat ]['h'] + $stats_data[ $default_stat ]['a'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>

			<?php if ( $args->stats ) : ?>
				<thead class="anwp-text-xs">
				<tr class="anwp-border-0">
					<td colspan="<?php echo $per_game ? 7 : 4; ?>" class="anwp-border-left-0 anwp-border-right-0 py-1"></td>
				</tr>
				<?php if ( $per_game ) : ?>
					<tr>
						<td rowspan="2" class="w-50"></td>
						<td colspan="2"><?php echo esc_html( $text_strings['home'] ); ?></td>
						<td colspan="2"><?php echo esc_html( $text_strings['away'] ); ?></td>
						<td colspan="2"><?php echo esc_html( $text_strings['all'] ); ?></td>
					</tr>
					<tr>
						<td><?php echo esc_html( $text_strings['per_match'] ); ?></td>
						<td><?php echo esc_html( $text_strings['total'] ); ?></td>
						<td><?php echo esc_html( $text_strings['per_match'] ); ?></td>
						<td><?php echo esc_html( $text_strings['total'] ); ?></td>
						<td><?php echo esc_html( $text_strings['per_match'] ); ?></td>
						<td><?php echo esc_html( $text_strings['total'] ); ?></td>
					</tr>
				<?php else : ?>
					<tr>
						<td class="w-50"></td>
						<td><?php echo esc_html( $text_strings['home'] ); ?></td>
						<td><?php echo esc_html( $text_strings['away'] ); ?></td>
						<td><?php echo esc_html( $text_strings['all'] ); ?></td>
					</tr>
				<?php endif; ?>
				</thead>

				<tbody>
				<?php
				/*
				|--------------------------------------------------------------------
				| Render default stats
				| 'corners', 'fouls', 'offsides', 'shots', 'shots_on_goal','cards_y','cards_r','goals','goals_conceded','clean_sheets'
				|--------------------------------------------------------------------
				*/
				foreach ( $args->stats as $row_index => $stat_value ) :

					if ( ! isset( $stats_data[ $stat_value ]['h'] ) ) {
						continue;
					}

					?>
					<tr class="<?php echo esc_attr( ( 0 !== $row_index % 2 ) ? 'anwp-bg-light' : '' ); ?>">
						<td class="anwp-text-left"><?php echo esc_html( isset( $text_strings[ $stat_value ] ) ? $text_strings[ $stat_value ] : '' ); ?></td>

						<?php if ( $per_game ) : ?>
							<td><?php echo esc_html( $stats_data[ $stat_value ]['h'] && $stats_data['played']['h'] ? ( round( $stats_data[ $stat_value ]['h'] / $stats_data['played']['h'], 1 ) ) : '' ); ?></td>
						<?php endif; ?>
						<td><?php echo esc_html( $stats_data[ $stat_value ]['h'] ); ?></td>

						<?php if ( $per_game ) : ?>
							<td><?php echo esc_html( $stats_data[ $stat_value ]['a'] && $stats_data['played']['a'] ? ( round( $stats_data[ $stat_value ]['a'] / $stats_data['played']['a'], 1 ) ) : '' ); ?></td>
						<?php endif; ?>
						<td><?php echo esc_html( $stats_data[ $stat_value ]['a'] ); ?></td>

						<?php if ( $per_game ) : ?>
							<td><?php echo esc_html( ( ( $stats_data['played']['h'] + $stats_data['played']['a'] ) && ( $stats_data[ $stat_value ]['a'] + $stats_data[ $stat_value ]['h'] ) ) ? ( round( ( $stats_data[ $stat_value ]['a'] + $stats_data[ $stat_value ]['h'] ) / ( $stats_data['played']['h'] + $stats_data['played']['a'] ), 1 ) ) : '' ); ?></td>
						<?php endif; ?>
						<td><?php echo esc_html( $stats_data[ $stat_value ]['h'] + $stats_data[ $stat_value ]['a'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
	</div>
</div>
