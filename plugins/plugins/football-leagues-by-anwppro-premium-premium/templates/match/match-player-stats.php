<?php
/**
 * The Template for displaying Match >> Player Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-player-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.9.7
 *
 * @version       0.16.0
 */
// phpcs:disable WordPress.NamingConventions.ValidVariableName

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$data,
	[
		'home_club'       => '',
		'away_club'       => '',
		'club_home_logo'  => '',
		'club_away_logo'  => '',
		'club_home_title' => '',
		'club_away_title' => '',
		'match_id'        => '',
		'season_id'       => '',
		'header'          => true,
		'layout_mod'      => 'even',
	]
);

/*
|--------------------------------------------------------------------
| Prepare Stats Data
|--------------------------------------------------------------------
*/
$stats_players_columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ), true );

if ( empty( $stats_players_columns ) ) {
	return;
}

$stats_data['home'] = anwp_fl_pro()->stats->get_game_players_statistics( $data['match_id'], $data['home_club'], false );
$stats_data['away'] = anwp_fl_pro()->stats->get_game_players_statistics( $data['match_id'], $data['away_club'], false );

if ( empty( $stats_data['home'] ) && empty( $stats_data['away'] ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Generate unique ID
|--------------------------------------------------------------------
*/
$tabulator_data_id = hash( 'crc32', wp_json_encode( $data ) );

/*
|--------------------------------------------------------------------
| Prepare Columns
|--------------------------------------------------------------------
*/
$player_column = [
	'title'      => esc_html( esc_html( AnWPFL_Text::get_value( 'match__player_stats__player', __( 'Player', 'anwp-football-leagues' ) ) ) ),
	'field'      => 'player',
	'headerSort' => false,
	'frozen'     => true,
	'cssClass'   => 'anwp-text-nowrap',
];

$tabulator_data = [
	'columns_goalkeeper' => [ $player_column ],
	'columns_field'      => [ $player_column ],
];

foreach ( $stats_players_columns as $column ) {
	if ( 'hidden' === $column['visibility'] ) {
		continue;
	}

	$column_config = [
		'title'         => esc_html( $column['abbr'] ),
		'field'         => 'id_' . $column['id'],
		'headerSort'    => 'composed' !== $column['type'],
		'sorter'        => 'number',
		'headerTooltip' => esc_attr( $column['name'] ),
		'cssClass'      => 'anwp-text-right',
	];

	if ( 'f' !== $column['group'] ) {
		$tabulator_data['columns_goalkeeper'][] = $column_config;
	}

	if ( 'g' !== $column['group'] ) {
		$tabulator_data['columns_field'][] = $column_config;
	}
}

foreach ( [ 'home', 'away' ] as $side ) {
	foreach ( $stats_data[ $side ] as $player_id => $player_stats ) {
		if ( empty( $data['players'][ $player_id ] ) ) {
			continue;
		}

		$player_has_data = false;

		$player_data = [
			'player' => $data['players'][ $player_id ]['short_name'],
		];

		foreach ( $stats_players_columns as $column ) {
			$cell_value = anwp_fl_pro()->stats->render_player_match_stats( (array) $player_stats, $column );

			$player_data[ 'id_' . $column['id'] ]           = esc_html( $cell_value );
			$player_data[ 'id_' . $column['id'] . '_sort' ] = anwp_fl_pro()->stats->get_game_sorting_value( $column, $cell_value );

			if ( ! empty( absint( $cell_value ) ) ) {
				$player_has_data = true;
			}
		}

		if ( $player_has_data ) {
			if ( 'g' === $data['players'][ $player_id ]['position'] ) {
				$tabulator_data[ 'data_goalkeeper_' . $side ][] = $player_data;
			} else {
				$tabulator_data[ 'data_field_' . $side ][] = $player_data;
			}
		}
	}
}
?>
<script>window.AnWPFLTabulator = window.AnWPFLTabulator || {};</script>
<script>window.AnWPFLTabulator[ '<?php echo esc_attr( $tabulator_data_id ); ?>' ] = <?php echo wp_json_encode( $tabulator_data ); ?>;</script>
<?php wp_enqueue_script( 'anwp-fl-tabulator' ); ?>
<div class="anwp-section anwp-fl-stats-match-players anwp-fl-tabulator-stats">

	<?php
	if ( ! empty( $data['header'] ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__player_stats__players_statistics', __( 'Players Statistics', 'anwp-football-leagues-premium' ) ),
			],
			'general/header'
		);
	endif;

	anwp_football_leagues()->load_partial(
		[
			'club_id' => $data['home_club'],
			'class'   => 'my-2',
		],
		'club/club-title'
	);
	?>

	<div class="anwp-text-sm anwp-font-semibold mt-2 mb-1"><?php echo esc_html( AnWPFL_Text::get_value( 'match__player_stats__goalkeepers', __( 'Goalkeepers', 'anwp-football-leagues' ) ) ); ?></div>
	<div
		class="anwp-fl-match-player-stats"
		data-id="<?php echo esc_attr( $tabulator_data_id ); ?>" data-layout-mod="<?php echo esc_attr( $data['layout_mod'] ); ?>"
		data-columns="columns_goalkeeper" data-data="data_goalkeeper_home"
	></div>

	<div class="anwp-text-sm anwp-font-semibold mt-3 mb-1"><?php echo esc_html( AnWPFL_Text::get_value( 'match__player_stats__field_players', __( 'Field Players', 'anwp-football-leagues-premium' ) ) ); ?></div>
	<div
		class="anwp-fl-match-player-stats"
		data-id="<?php echo esc_attr( $tabulator_data_id ); ?>" data-layout-mod="<?php echo esc_attr( $data['layout_mod'] ); ?>"
		data-columns="columns_field" data-data="data_field_home"
	></div>

	<?php
	anwp_football_leagues()->load_partial(
		[
			'club_id' => $data['away_club'],
			'class'   => 'mb-2 mt-4',
			'is_home' => false,
		],
		'club/club-title'
	);
	?>
	<div class="anwp-text-sm anwp-font-semibold mt-2 mb-1"><?php echo esc_html( AnWPFL_Text::get_value( 'match__player_stats__goalkeepers', __( 'Goalkeepers', 'anwp-football-leagues' ) ) ); ?></div>
	<div
		class="anwp-fl-match-player-stats"
		data-id="<?php echo esc_attr( $tabulator_data_id ); ?>" data-layout-mod="<?php echo esc_attr( $data['layout_mod'] ); ?>"
		data-columns="columns_goalkeeper" data-data="data_goalkeeper_away"
	></div>

	<div class="anwp-text-sm anwp-font-semibold mt-3 mb-1"><?php echo esc_html( AnWPFL_Text::get_value( 'match__player_stats__field_players', __( 'Field Players', 'anwp-football-leagues-premium' ) ) ); ?></div>
	<div
		class="anwp-fl-match-player-stats"
		data-id="<?php echo esc_attr( $tabulator_data_id ); ?>" data-layout-mod="<?php echo esc_attr( $data['layout_mod'] ); ?>"
		data-columns="columns_field" data-data="data_field_away"
	></div>
</div>
