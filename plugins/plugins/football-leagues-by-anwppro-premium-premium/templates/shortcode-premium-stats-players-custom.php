<?php
/**
 * The Template for displaying Stats for Players - Custom.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-stats-players-custom.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.12.2
 *
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = wp_parse_args(
	$data,
	[
		'columns'        => '',
		'season_id'      => '',
		'league_id'      => '',
		'club_id'        => '',
		'competition_id' => '',
		'multistage'     => 0,
		'type'           => '',
		'links'          => 0,
		'caching_time'   => '',
		'photos'         => 0,
		'limit'          => 10,
		'sort_column'    => '',
		'sort_order'     => 'DESC',
		'sort_column_2'  => '',
		'sort_order_2'   => 'DESC',
		'class'          => '',
		'header'         => '',
		'club_column'    => 'logo_abbr',
		'date_from'      => '',
		'date_to'        => '',
	]
);

$args['type']   = mb_strtolower( $args['type'] );
$args['links']  = AnWP_Football_Leagues::string_to_bool( $args['links'] );
$args['photos'] = AnWP_Football_Leagues::string_to_bool( $args['photos'] );

/*
|--------------------------------------------------------------------
| Prepare Columns
|--------------------------------------------------------------------
*/
$columns = wp_parse_slug_list( $args['columns'] );

if ( empty( $columns ) || ! is_array( $columns ) ) {
	return;
}

foreach ( $columns as $col_index => $column ) {
	if ( absint( $column ) ) {
		$columns[ $col_index ] = 'c__' . absint( $column );
	}
}

if ( absint( $args['sort_column'] ) ) {
	$args['sort_column'] = 'c__' . $args['sort_column'];
}

/*
|--------------------------------------------------------------------
| Get data
|--------------------------------------------------------------------
*/

// Try to get from cache
$cache_key = 'FL-PRO-SHORTCODE_stats-players-custom__' . md5( maybe_serialize( $data ) );

if ( class_exists( 'AnWPFL_Cache' ) && anwp_fl()->cache->get( $cache_key, 'anwp_match' ) ) {
	$stats_data = anwp_fl()->cache->get( $cache_key, 'anwp_match' );
} else {
	// Load data in default way
	$stats_data = anwp_fl_pro()->player->get_players_stats_totals_custom( $args, $columns );

	// Save transient
	if ( ! empty( $stats_data ) && class_exists( 'AnWPFL_Cache' ) ) {
		anwp_fl()->cache->set( $cache_key, $stats_data, 'anwp_match' );
	}
}

if ( empty( $stats_data ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Table Header/Footer
|--------------------------------------------------------------------
*/
$col_headers = [
	'club'           => [
		'tooltip' => AnWPFL_Text::get_value( 'stats_players__shortcode__club', __( 'Club', 'anwp-football-leagues' ) ),
		'text'    => AnWPFL_Text::get_value( 'stats_players__shortcode__club', __( 'Club', 'anwp-football-leagues' ) ),
		'class'   => 'text-left',
	],
	'player_name'    => [
		'tooltip' => AnWPFL_Text::get_value( 'stats_players__shortcode__player', __( 'Player', 'anwp-football-leagues' ) ),
		'text'    => AnWPFL_Text::get_value( 'stats_players__shortcode__player', __( 'Player', 'anwp-football-leagues' ) ),
		'class'   => 'text-left',
	],
	'position'       => [
		'tooltip' => AnWPFL_Text::get_value( 'stats_players__shortcode__position', __( 'Position', 'anwp-football-leagues' ) ),
		'text'    => AnWPFL_Text::get_value( 'stats_players__shortcode__position', __( 'Position', 'anwp-football-leagues' ) ),
		'class'   => '',
	],
	'appearance'     => [
		'tooltip' => '',
		'text'    => '<svg class="anwp-icon--s20 anwp-icon--trans p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__played_matches', __( 'Played Matches', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-field"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'started'        => [
		'tooltip' => '',
		'text'    => '<svg class="anwp-icon--s20 anwp-icon--trans p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__started', __( 'Started', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-field-shirt"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'minutes'        => [
		'tooltip' => '',
		'text'    => '<svg class="anwp-icon--s20 anwp-icon--gray-900 p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__minutes', __( 'Minutes', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-watch"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'cards_all'      => [
		'tooltip' => '',
		'text'    => '<svg class="icon__card p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html__( 'Cards (All)', 'anwp-football-leagues-premium' ) . '"><use xlink:href="#icon-card_yr"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'cards_y'        => [
		'tooltip' => '',
		'text'    => '<svg class="icon__card p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__yellow_cards', __( 'Yellow Cards', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-card_y"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'cards_r'        => [
		'tooltip' => '',
		'text'    => '<svg class="icon__card p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__red_cards', __( 'Red Cards', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-card_r"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'goals'          => [
		'tooltip' => '',
		'text'    => '<svg class="icon__ball anwp-icon--stats-goal p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__goals', __( 'Goals', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-ball"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'goals_own'      => [
		'tooltip' => '',
		'text'    => '<svg class="icon__ball icon__ball--own p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__own_goals', __( 'Own Goals', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-ball"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'goals_penalty'  => [
		'tooltip' => '',
		'text'    => '<svg class="icon__ball anwp-icon--stats-goal p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__goals_from_penalty', __( 'Goals (from penalty)', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-ball_penalty"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'assists'        => [
		'tooltip' => '',
		'text'    => '<svg class="icon__ball anwp-semi-opacity p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__assists', __( 'Assists', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-ball"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'goals_conceded' => [
		'tooltip' => '',
		'text'    => '<svg class="icon__ball icon__ball--conceded p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__goals_conceded', __( 'Goals Conceded', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-ball"></use></svg>',
		'class'   => 'anwp-text-center',
	],
	'clean_sheets'   => [
		'tooltip' => '',
		'text'    => '<svg class="icon__ball p-0" data-toggle="anwp-tooltip" data-tippy-content="' . esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__clean_sheets', __( 'Clean Sheets', 'anwp-football-leagues' ) ) ) . '"><use xlink:href="#icon-ball_canceled"></use></svg>',
		'class'   => 'anwp-text-center',
	],
];

$photo_dir = wp_upload_dir()['baseurl'];
?>
<div class="anwp-b-wrap">

	<?php
	if ( ! empty( $args['header'] ) ) :
		anwp_fl()->load_partial(
			[
				'text' => $args['header'],
			],
			'general/header'
		);
	endif;
	?>

	<div class="anwpfl-not-ready shortcode-stats_players_custom table-responsive anwp-fl-data-tables">
		<table class="table anwp-data-shortcode-stats_players_custom table-bordered table-striped table-sm mb-1 anwp-text-xs <?php echo esc_attr( $args['class'] ); ?>">
		<thead>
			<tr>
				<?php
				foreach ( $columns as $column ) :

					$column = mb_strtolower( $column );

					// Ranking
					if ( 'ranking' === $column ) :
						?>
						<th></th>
						<?php
						continue;
					endif;

					// Default Stat
					if ( ! empty( $col_headers[ $column ]['text'] ) ) :
						$col_tooltip = '';

						if ( $col_headers[ $column ]['tooltip'] ) :
							$col_tooltip = 'data-toggle="anwp-tooltip" data-tippy-content="' . $col_headers[ $column ]['tooltip'] . '"';
						endif;
						?>
						<th class="anwp-text-center <?php echo esc_attr( $col_headers[ $column ]['class'] ); ?>" <?php echo $col_tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php echo $col_headers[ $column ]['text']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</th>
						<?php
						continue;
					endif;

					// Custom Stat
					if ( false !== mb_strpos( $column, 'c__' ) ) :
						$col_stat_id = absint( mb_substr( $column, 3 ) );

						$column_data = [
							'tooltip' => '',
							'text'    => '',
						];

						if ( $col_stat_id ) {
							$stats_columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ) );

							if ( ! empty( $stats_columns ) && is_array( $stats_columns ) ) {
								$stat_items = array_values( wp_list_filter( $stats_columns, [ 'id' => absint( $col_stat_id ) ] ) );

								if ( ! empty( $stat_items[0] ) && 'simple' === $stat_items[0]->type ) {
									$column_data['tooltip'] = $stat_items[0]->name;
									$column_data['text']    = $stat_items[0]->abbr;
								}
							}
						}

						if ( $column_data['tooltip'] ) :
							$column_data['tooltip'] = 'data-toggle="anwp-tooltip" data-tippy-content="' . $column_data['tooltip'] . '"';
						endif;

						?>
						<th class="anwp-text-center" <?php echo $column_data['tooltip']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php echo $column_data['text']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</th>
						<?php
					endif;

				endforeach;
				?>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $stats_data as $ranking => $stat_data ) :
				/** @var array $stat_data = [
				 *      'player_id' => 3771,
				 *      'position' => 'm',
				 *      'player_name' => 'A. Adli',
				 *      'photo' => '/2023/09/129682.jpg',
				 *      'appearance' => 6,
				 *      'c__6' => 5,
				 *      'c__14' => 41.5,
				 *      'clubs' => 11196,
				 *      'link' => 'http://fl-1.test/player/a-adli/',
				 *  ] */
				?>
					<tr>
						<?php
						foreach ( $columns as $column ) :

							$column = mb_strtolower( $column );

							// Ranking
							if ( 'ranking' === $column ) :
								?>
								<td class="anwp-text-center"><?php echo absint( $ranking + 1 ); ?></td>
								<?php
								continue;
							endif;

							// Player Name
							if ( 'player_name' === $column && $args['links'] && ! empty( $stat_data['link'] ) ) :
								if ( $args['photos'] && $stat_data['photo'] ) :
									?>
									<td class="px-2 anwp-text-left anwp-cell-with-player-photo">
										<img loading="lazy" width="30" height="30" class="anwp-object-contain mr-2 mb-0 anwp-w-30 anwp-h-30" src="<?php echo esc_attr( $photo_dir . $stat_data['photo'] ); ?>">
										<a class="" href="<?php echo esc_attr( $stat_data['link'] ); ?>"><?php echo esc_html( $stat_data['player_name'] ); ?></a>
									</td>
								<?php else : ?>
									<td class="px-2 anwp-text-left">
										<a href="<?php echo esc_attr( $stat_data['link'] ); ?>"><?php echo esc_html( $stat_data['player_name'] ); ?></a>
									</td>
									<?php
								endif;

								continue;

							elseif ( 'player_name' === $column ) :
								if ( $args['photos'] && $stat_data['photo'] ) :
									echo '<td class="px-2 text-left anwp-cell-with-player-photo"><img loading="lazy" width="30" height="30" class="anwp-object-contain mr-2 mb-0 border rounded anwp-w-30 anwp-h-30" src="' . esc_attr( $photo_dir . $stat_data['photo'] ) . '">' . esc_html( $stat_data['player_name'] ) . '</td>';
								else :
									echo '<td class="px-2 text-left">' . esc_html( $stat_data['player_name'] ) . '</td>';
								endif;

								continue;

							endif;

							// Club
							if ( 'club' === $column ) :
								echo '<td class="px-2 text-left anwp-club-title-column">';

								foreach ( explode( ',', $stat_data['clubs'] ) as $ii => $club_id ) :

									$args['club_column'] = mb_strtolower( $args['club_column'] );

									if ( in_array( $args['club_column'], [ 'logo', 'logo_title', 'logo_abbr' ], true ) ) :
										$club_logo = anwp_fl()->club->get_club_logo_by_id( $club_id );
										if ( $club_logo ) :
											echo '<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-2 mb-0 anwp-w-20 anwp-h-20" src="' . esc_url( $club_logo ) . '" data-toggle="anwp-tooltip" data-tippy-content="' . esc_attr( anwp_fl()->club->get_club_title_by_id( $club_id ) ) . '">';
										endif;
									endif;

									if ( 'logo_abbr' === $args['club_column'] || 'abbr' === $args['club_column'] ) :
										echo '<span>' . esc_html( anwp_fl()->club->get_club_abbr_by_id( $club_id ) ) . '</span>';
									endif;

									if ( 'logo_title' === $args['club_column'] || 'title' === $args['club_column'] ) :
										echo '<span>' . esc_html( anwp_fl()->club->get_club_title_by_id( $club_id ) ) . '</span>';
									endif;
								endforeach;

								echo '</td>';
								continue;
							endif;

							// Render Position
							if ( 'position' === $column ) :
								echo '<td class="px-2">' . esc_html( anwp_fl()->player->get_position_l10n( $stat_data['position'] ?? '' ) ) . '</td>';
								continue;
							endif;

							// Handle custom Stats
							if ( false !== mb_strpos( $column, 'c__' ) ) :
								echo '<td class="anwp-text-center anwp-stats-number">' . esc_html( $stat_data[ $column ] ?? 0 ) . '</td>';
								continue;
							endif;

							// Render Default Stats
							if ( isset( $stat_data[ $column ] ) ) :
								echo '<td class="anwp-text-center anwp-stats-number">' . esc_html( $stat_data[ $column ] ) . '</td>';
							endif;

						endforeach;
						?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
