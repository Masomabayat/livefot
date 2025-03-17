<?php
/**
 * The Template for displaying Stats for Players.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-stats-players.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @ToDo add nationalities
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.5.7
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'competition_id' => 0,
		'multistage'     => 0,
		'season_id'      => 0,
		'club_id'        => 0,
		'rows'           => '',
		'paging'         => 1,
		'links'          => 1,
		'type'           => '', // p, g
		'sections'       => '',
		'date_from'      => '',
		'layout_mod'     => 'even',
	]
);

$data->type  = mb_strtolower( $data->type );
$data->rows  = in_array( $data->rows, [ '10', '25', '50', '-1' ], true ) ? $data->rows : '10';
$paging      = AnWP_Football_Leagues::string_to_bool( $data->paging );
$player_link = AnWP_Football_Leagues::string_to_bool( $data->links );

/*
|--------------------------------------------------------------------
| Generate unique ID
|--------------------------------------------------------------------
*/
$tabulator_data_id = hash( 'crc32', wp_json_encode( $data ) );

/*
|--------------------------------------------------------------------
| Sections
|--------------------------------------------------------------------
*/
$sections = [
	'club'           => true,
	'position'       => true,
	'appearance'     => true,
	'minutes'        => true,
	'cards'          => true,
	'goals'          => true,
	'goals_penalty'  => true,
	'goals_own'      => true,
	'assists'        => true,
	'goals_conceded' => true,
	'clean_sheets'   => true,
];

if ( ! empty( $data->sections ) ) {

	$visible_sections = wp_parse_slug_list( $data->sections );

	foreach ( array_keys( $sections ) as $section ) {
		if ( ! in_array( $section, $visible_sections, true ) ) {
			$sections[ $section ] = false;
		}
	}
}

/*
|--------------------------------------------------------------------
| Columns
|--------------------------------------------------------------------
*/
$tabulator_data = [
	'columns' => [
		[
			'title'        => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__player', __( 'Player', 'anwp-football-leagues' ) ) ),
			'field'        => 'player',
			'sorter'       => 'sortName',
			'headerFilter' => 'input',
			'frozen'       => true,
		],
	],
	'data'    => [],
];

if ( $sections['club'] && ! intval( $data->club_id ) ) {
	$tabulator_data['columns'][] = [
		'title'              => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__club', __( 'Club', 'anwp-football-leagues' ) ) ),
		'field'              => 'club',
		'headerFilter'       => 'input',
		'headerFilterParams' => [
			'clearable' => true,
		],
	];
}

if ( $sections['position'] ) {
	$tabulator_data['columns'][] = [
		'title'              => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__position', __( 'Position', 'anwp-football-leagues' ) ) ),
		'field'              => 'position',
		'headerFilter'       => 'list',
		'headerFilterParams' => [
			'valuesLookup' => true,
			'clearable'    => true,
		],
	];
}

$common_part = [
	'headerFilter'            => 'number',
	'headerFilterPlaceholder' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__min', __( 'min', 'anwp-football-leagues-premium' ) ) ),
	'headerFilterFunc'        => '>=',
	'sorter'                  => 'number',
	'headerHozAlign'          => 'right',
	'cssClass'                => 'anwp-text-right',
];

if ( $sections['appearance'] ) {
	$tabulator_data['columns'][] = array_merge(
		[
			'title'         => '<svg class="anwp-icon--s20 anwp-icon--trans p-0"><use xlink:href="#icon-field"></use></svg>',
			'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__played_matches', __( 'Played Matches', 'anwp-football-leagues' ) ) ),
			'field'         => 'played',
		],
		$common_part
	);

	$tabulator_data['columns'][] = array_merge(
		[
			'title'         => '<svg class="anwp-icon--s20 anwp-icon--trans p-0"><use xlink:href="#icon-field-shirt"></use></svg>',
			'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__started', __( 'Started', 'anwp-football-leagues' ) ) ),
			'field'         => 'started',
		],
		$common_part
	);
}

if ( $sections['minutes'] ) {
	$tabulator_data['columns'][] = array_merge(
		[
			'title'         => '<svg class="anwp-icon--s20 anwp-icon--gray-900 p-0"><use xlink:href="#icon-watch"></use></svg>',
			'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__minutes', __( 'Minutes', 'anwp-football-leagues' ) ) ),
			'field'         => 'minutes',
		],
		$common_part
	);
}

if ( $sections['cards'] ) {
	$tabulator_data['columns'][] = array_merge(
		[
			'title'         => '<svg class="icon__card p-0"><use xlink:href="#icon-card_y"></use></svg>',
			'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__yellow_cards', __( 'Yellow Cards', 'anwp-football-leagues' ) ) ),
			'field'         => 'card_y',
		],
		$common_part
	);

	$tabulator_data['columns'][] = array_merge(
		[
			'title'         => '<svg class="icon__card p-0"><use xlink:href="#icon-card_yr"></use></svg>',
			'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__2_d_yellow_red_cards', __( '2d Yellow > Red Cards', 'anwp-football-leagues' ) ) ),
			'field'         => 'card_yr',
		],
		$common_part
	);

	$tabulator_data['columns'][] = array_merge(
		[
			'title'         => '<svg class="icon__card p-0"><use xlink:href="#icon-card_r"></use></svg>',
			'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__red_cards', __( 'Red Cards', 'anwp-football-leagues' ) ) ),
			'field'         => 'card_r',
		],
		$common_part
	);
}

if ( 'g' !== $data->type ) {
	if ( $sections['goals'] ) {
		$tabulator_data['columns'][] = array_merge(
			[
				'title'         => '<svg class="icon__ball anwp-icon--stats-goal p-0"><use xlink:href="#icon-ball"></use></svg>',
				'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__goals', __( 'Goals', 'anwp-football-leagues' ) ) ),
				'field'         => 'goals',
			],
			$common_part
		);
	}

	if ( $sections['goals_penalty'] ) {
		$tabulator_data['columns'][] = array_merge(
			[
				'title'         => '<svg class="icon__ball anwp-icon--stats-goal p-0"><use xlink:href="#icon-ball_penalty"></use></svg>',
				'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__goals_from_penalty', __( 'Goals (from penalty)', 'anwp-football-leagues' ) ) ),
				'field'         => 'goals_penalty',
			],
			$common_part
		);
	}

	if ( $sections['goals_own'] ) {
		$tabulator_data['columns'][] = array_merge(
			[
				'title'         => '<svg class="icon__ball icon__ball--own p-0"><use xlink:href="#icon-ball"></use></svg>',
				'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__own_goals', __( 'Own Goals', 'anwp-football-leagues' ) ) ),
				'field'         => 'goals_own',
			],
			$common_part
		);
	}

	if ( $sections['assists'] ) {
		$tabulator_data['columns'][] = array_merge(
			[
				'title'         => '<svg class="icon__ball anwp-opacity-50 p-0"><use xlink:href="#icon-ball"></use></svg>',
				'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__assists', __( 'Assists', 'anwp-football-leagues' ) ) ),
				'field'         => 'assists',
			],
			$common_part
		);
	}
}

if ( 'p' !== $data->type ) {
	if ( $sections['goals_conceded'] ) {
		$tabulator_data['columns'][] = array_merge(
			[
				'title'         => '<svg class="icon__ball icon__ball--conceded p-0"><use xlink:href="#icon-ball"></use></svg>',
				'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__goals_conceded', __( 'Goals Conceded', 'anwp-football-leagues' ) ) ),
				'field'         => 'goals_conceded',
			],
			$common_part
		);
	}

	if ( $sections['clean_sheets'] ) {
		$tabulator_data['columns'][] = array_merge(
			[
				'title'         => '<svg class="icon__ball p-0"><use xlink:href="#icon-ball_canceled"></use></svg>',
				'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__clean_sheets', __( 'Clean Sheets', 'anwp-football-leagues' ) ) ),
				'field'         => 'clean_sheets',
			],
			$common_part
		);
	}
}

$tabulator_data['columns'][] = [
	'field'   => 'sort_name',
	'visible' => false,
];

/*
|--------------------------------------------------------------------
| Get data
|--------------------------------------------------------------------
*/
$stats = anwp_football_leagues_premium()->player->get_players_stats_totals( $data, $sections );

if ( empty( $stats ) ) {
	return;
}

$tabulator_data['data'] = anwp_football_leagues_premium()->player->get_players_stats_totals_json( $stats, $data );
?>
<script>window.AnWPFLTabulator = window.AnWPFLTabulator || {};</script>
<script>window.AnWPFLTabulator[ '<?php echo esc_attr( $tabulator_data_id ); ?>' ] = <?php echo wp_json_encode( $tabulator_data ); ?>;</script>
<?php wp_enqueue_script( 'anwp-fl-tabulator' ); ?>
<div class="anwp-b-wrap anwp-fl-tabulator-stats">
	<div class="shortcode-stats_players stats-players stats-players--tabulator anwp-fl-data-tables stats-players--type-<?php echo esc_attr( $data->type ); ?>"
		data-layout-mod="<?php echo esc_attr( $data->layout_mod ); ?>"
		data-rows="<?php echo esc_attr( $data->rows ); ?>" data-paging="<?php echo esc_attr( $paging ? 'yes' : '' ); ?>"
		data-id="<?php echo esc_attr( $tabulator_data_id ); ?>"></div>
</div>
