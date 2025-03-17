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
 * @version         0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'competition_id'       => '',
		'season_id'            => '',
		'league_id'            => '',
		'show_secondary'       => 1,
		'class'                => '',
		'header'               => '',
		'layout'               => '',
		'show_games'           => 1,
		'show_rc'              => 1,
		'show_fouls'           => 1,
		'home_away'            => 1,
		'per_game'             => 1,
		'profile_link'         => 1,
		'type'                 => 'result',
		'filter_by_clubs'      => '',
		'filter_by_matchweeks' => '',
		'limit'                => '',
		'date_from'            => '',
		'date_to'              => '',
		'exclude_ids'          => '',
		'include_ids'          => '',
		'home_club'            => '',
		'away_club'            => '',
		'order'                => '',
		'layout_mod'           => 'even',
	]
);

$referee_stats = anwp_fl_pro()->referee->get_referees_stats( $args );

if ( empty( $referee_stats ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Generate unique ID
|--------------------------------------------------------------------
*/
$tabulator_data_id = hash( 'crc32', wp_json_encode( $args ) );

/*
|--------------------------------------------------------------------
| Text Strings
|--------------------------------------------------------------------
*/
$text_strings = [
	'referee'      => AnWPFL_Text::get_value( 'stats__referee__referee', __( 'Referee', 'anwp-football-leagues-premium' ) ),
	'games'        => AnWPFL_Text::get_value( 'stats__referee__games', __( 'Games', 'anwp-football-leagues-premium' ) ),
	'fouls'        => AnWPFL_Text::get_value( 'stats__referee__foul', __( 'Fouls', 'anwp-football-leagues-premium' ) ),
	'card_y'       => AnWPFL_Text::get_value( 'stats__referee__card_y', __( 'Yellow Cards', 'anwp-football-leagues-premium' ) ),
	'card_r'       => AnWPFL_Text::get_value( 'stats__referee__card_r', __( 'Red Cards', 'anwp-football-leagues-premium' ) ),
	'all_abbr'     => AnWPFL_Text::get_value( 'stats__referee__all_abbr', _x( 'All', 'All (abbr.) in Referees Stats', 'anwp-football-leagues-premium' ) ),
	'all_tooltip'  => AnWPFL_Text::get_value( 'stats__referee__all_tooltip', _x( 'All', 'All - tooltip in Referees Stats (default empty)', 'anwp-football-leagues-premium' ) ),
	'pg_abb'       => AnWPFL_Text::get_value( 'stats__referee__pg_abbr', _x( 'PG', 'Per Game (abbr.) in Referees Stats', 'anwp-football-leagues-premium' ) ),
	'pg_tooltip'   => AnWPFL_Text::get_value( 'stats__referee__pg_tooltip', _x( 'Per Game', 'Per Game - tooltip in Referees Stats', 'anwp-football-leagues-premium' ) ),
	'h_abb'        => AnWPFL_Text::get_value( 'stats__referee__h_abbr', _x( 'H', 'Home (abbr.) in Referees Stats', 'anwp-football-leagues-premium' ) ),
	'h_tooltip'    => AnWPFL_Text::get_value( 'stats__referee__h_tooltip', _x( 'Home', 'Home - tooltip in Referees Stats', 'anwp-football-leagues-premium' ) ),
	'a_abb'        => AnWPFL_Text::get_value( 'stats__referee__a_abbr', _x( 'A', 'Away (abbr.) in Referees Stats', 'anwp-football-leagues-premium' ) ),
	'a_tooltip'    => AnWPFL_Text::get_value( 'stats__referee__a_tooltip', _x( 'Away', 'Away - tooltip in Referees Stats', 'anwp-football-leagues-premium' ) ),
	'h_pg_abb'     => AnWPFL_Text::get_value( 'stats__referee__h_pg_abbr', _x( 'H-PG', 'Home Per Game (abbr.) in Referees Stats', 'anwp-football-leagues-premium' ) ),
	'h_pg_tooltip' => AnWPFL_Text::get_value( 'stats__referee__h_pg_tooltip', _x( 'Home - Per Game', 'Home Per Game - tooltip in Referees Stats', 'anwp-football-leagues-premium' ) ),
	'a_pg_abb'     => AnWPFL_Text::get_value( 'stats__referee__a_pg_abbr', _x( 'A-PG', 'Away Per Game (abbr.) in Referees Stats', 'anwp-football-leagues-premium' ) ),
	'a_pg_tooltip' => AnWPFL_Text::get_value( 'stats__referee__a_pg_tooltip', _x( 'Away - Per Game', 'Away Per Game - tooltip in Referees Stats', 'anwp-football-leagues-premium' ) ),
];

/*
|--------------------------------------------------------------------
| Stat Params
|--------------------------------------------------------------------
*/
$table_stats = [ 'card_y' ];

if ( AnWP_Football_Leagues::string_to_bool( $args->show_rc ) ) {
	$table_stats[] = 'card_r';
}

if ( AnWP_Football_Leagues::string_to_bool( $args->show_fouls ) ) {
	$table_stats[] = 'fouls';
}

/*
|--------------------------------------------------------------------
| Prepare Columns
|--------------------------------------------------------------------
*/
$title_map = [
	'card_y' => '<svg class="icon__ball mr-2 anwp-flex-none"><use xlink:href="#icon-card_y"></use></svg>' . esc_html( $text_strings['card_y'] ),
	'card_r' => '<svg class="icon__ball mr-2 anwp-flex-none"><use xlink:href="#icon-card_r"></use></svg>' . esc_html( $text_strings['card_r'] ),
	'fouls'  => '<svg class="icon__ball mr-2 anwp-flex-none"><use xlink:href="#icon-whistle"></use></svg>' . esc_html( $text_strings['fouls'] ),
];

$columns = [
	[
		'title'     => esc_html( $text_strings['referee'] ),
		'field'     => 'referee',
		'sorter'    => 'sortName',
		'formatter' => 'html',
		'frozen'    => true,
	],
];

if ( AnWP_Football_Leagues::string_to_bool( $args->show_games ) ) {
	$columns[] = [
		'title'    => esc_html( $text_strings['games'] ),
		'field'    => 'games',
		'sorter'   => 'number',
		'cssClass' => 'anwp-text-right',
	];
}

foreach ( $table_stats as $table_stat ) {

	$stat_column = [
		'title'          => $title_map[ $table_stat ],
		'headerTooltip'  => $text_strings[ $table_stat ],
		'headerHozAlign' => 'center',
	];

	$nested_columns = [];

	if ( AnWP_Football_Leagues::string_to_bool( $args->home_away ) || AnWP_Football_Leagues::string_to_bool( $args->per_game ) ) {
		$nested_columns[] = [
			'title'         => esc_html( $text_strings['all_abbr'] ),
			'field'         => $table_stat,
			'sorter'        => 'number',
			'headerTooltip' => esc_attr( $text_strings['all_tooltip'] ),
			'cssClass'      => 'anwp-text-right',
		];

		if ( AnWP_Football_Leagues::string_to_bool( $args->per_game ) ) {
			$nested_columns[] = [
				'title'         => esc_html( $text_strings['pg_abb'] ),
				'field'         => $table_stat . '_pg',
				'sorter'        => 'number',
				'headerTooltip' => esc_attr( $text_strings['pg_tooltip'] ),
				'cssClass'      => 'anwp-text-right',
			];
		}

		if ( AnWP_Football_Leagues::string_to_bool( $args->home_away ) ) {
			$nested_columns[] = [
				'title'         => esc_html( $text_strings['h_abb'] ),
				'field'         => $table_stat . '_h',
				'sorter'        => 'number',
				'headerTooltip' => esc_attr( $text_strings['h_tooltip'] ),
				'cssClass'      => 'anwp-text-right',
			];

			if ( AnWP_Football_Leagues::string_to_bool( $args->per_game ) ) {
				$nested_columns[] = [
					'title'         => esc_html( $text_strings['h_pg_abb'] ),
					'field'         => $table_stat . '_h_pg',
					'sorter'        => 'number',
					'headerTooltip' => esc_attr( $text_strings['h_pg_tooltip'] ),
					'cssClass'      => 'anwp-text-right',
				];
			}

			$nested_columns[] = [
				'title'         => esc_html( $text_strings['a_abb'] ),
				'field'         => $table_stat . '_a',
				'sorter'        => 'number',
				'headerTooltip' => esc_attr( $text_strings['a_tooltip'] ),
				'cssClass'      => 'anwp-text-right',
			];

			if ( AnWP_Football_Leagues::string_to_bool( $args->per_game ) ) {
				$nested_columns[] = [
					'title'         => esc_html( $text_strings['a_pg_abb'] ),
					'field'         => $table_stat . '_a_pg',
					'sorter'        => 'number',
					'headerTooltip' => esc_attr( $text_strings['a_pg_tooltip'] ),
					'cssClass'      => 'anwp-text-right',
				];
			}
		}
	}

	if ( ! empty( $nested_columns ) ) {
		$stat_column['columns'] = $nested_columns;
	}

	$columns[] = $stat_column;
}

$columns[] = [
	'field'   => 'sort_name',
	'visible' => false,
];

/*
|--------------------------------------------------------------------
| Prepare Table Data
|--------------------------------------------------------------------
*/
$table_data = [];

foreach ( $referee_stats as $referee_id => $referee_stat ) {
	$referee_obj = anwp_football_leagues()->referee->get_referee( $referee_id );

	if ( empty( $referee_obj ) ) {
		continue;
	}

	$referee_data = [];

	ob_start();
	if ( ! empty( $referee_obj->country ) ) :
		anwp_football_leagues()->load_partial(
			[
				'class'         => 'options__flag mb-n1 mr-1',
				'wrapper_class' => 'mr-1',
				'size'          => 16,
				'country_code'  => $referee_obj->country,
			],
			'general/flag'
		);
	endif;

	if ( AnWP_Football_Leagues::string_to_bool( $args->profile_link ) ) {
		echo '<a href="' . esc_attr( $referee_obj->link ) . '" title="' . esc_attr( $referee_obj->name ) . '" class="anwp-link-without-effects">' . esc_html( $referee_obj->name ) . '</a>';
	} else {
		echo esc_html( $referee_obj->name );
	}

	$referee_data['referee'] = ob_get_clean();

	$referee_data['sort_name'] = esc_attr( '. ' === mb_substr( $referee_obj->name, 1, 2 ) ? mb_substr( $referee_obj->name, 3 ) : $referee_obj->name );
	$referee_data['games']     = absint( $referee_stat['games'] );

	foreach ( $table_stats as $stat ) {
		$referee_data[ $stat ] = absint( $referee_stat[ $stat ] );

		if ( AnWP_Football_Leagues::string_to_bool( $args->per_game ) ) {
			$referee_data[ $stat . '_pg' ] = esc_html( round( $referee_stat[ $stat ] / $referee_stat['games'], 1 ) );
		}

		if ( AnWP_Football_Leagues::string_to_bool( $args->home_away ) ) {
			$referee_data[ $stat . '_h' ] = absint( $referee_stat[ $stat . '_h' ] );
			$referee_data[ $stat . '_a' ] = absint( $referee_stat[ $stat . '_a' ] );
		}

		if ( AnWP_Football_Leagues::string_to_bool( $args->home_away ) && AnWP_Football_Leagues::string_to_bool( $args->per_game ) ) {
			$referee_data[ $stat . '_h_pg' ] = esc_html( round( $referee_stat[ $stat . '_h' ] / $referee_stat['games'], 1 ) );
			$referee_data[ $stat . '_a_pg' ] = esc_html( round( $referee_stat[ $stat . '_a' ] / $referee_stat['games'], 1 ) );
		}
	}

	$table_data[] = $referee_data;
}

$tabulator_data = [
	'columns' => $columns,
	'data'    => $table_data,
];

$initial_sort = [];

if ( $args->order ) {
	foreach ( explode( ';', $args->order ) as $order_row ) {
		$order_col     = explode( ',', $order_row );
		$table_order[] = [ absint( $order_col[0] ) - 1, $order_col[1] ?? 'asc' ];

		if ( isset( $columns[ absint( $order_col[0] ) - 1 ] ) ) {
			$initial_sort[] = [
				'column' => $columns[ absint( $order_col[0] ) - 1 ]['field'],
				'dir'    => $order_col[1] ?? 'asc',
			];
		}
	}

	if ( ! empty( $initial_sort ) ) {
		$tabulator_data['initialSort'] = array_reverse( $initial_sort );
	}
}
?>
<script>
	window.AnWPFLTabulator = window.AnWPFLTabulator || {};
	window.AnWPFLTabulator[ '<?php echo esc_attr( $tabulator_data_id ); ?>' ] = <?php echo wp_json_encode( $tabulator_data ); ?>;
</script>
<?php wp_enqueue_script( 'anwp-fl-tabulator' ); ?>
<div class="anwp-b-wrap anwp-fl-referees-stats-shortcode referees-stats anwp-fl-tabulator-stats <?php echo esc_attr( $args->class ); ?>">

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

	<div class="anwp-fl-referees-stats-shortcode--tabulator"
		data-layout-mod="<?php echo esc_attr( $args->layout_mod ); ?>"
		data-id="<?php echo esc_attr( $tabulator_data_id ); ?>"></div>
</div>
