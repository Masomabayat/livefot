<?php
/**
 * The Template for displaying Stats for Players - Custom.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-stats-players-custom--tabulator.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.15.1
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
		'layout_mod'     => '',
		'rows'           => '',
		'paging'         => 1,
	]
);

$args['type']   = mb_strtolower( $args['type'] );
$args['links']  = AnWP_Football_Leagues::string_to_bool( $args['links'] );
$args['photos'] = AnWP_Football_Leagues::string_to_bool( $args['photos'] );
$args['paging'] = AnWP_Football_Leagues::string_to_bool( $args['paging'] );

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

if ( class_exists( 'AnWPFL_Cache' ) && anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) ) {
	$stats_data = anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' );
} else {
	// Load data in default way
	$stats_data = anwp_football_leagues_premium()->player->get_players_stats_totals_custom( $args, $columns );

	// Save transient
	if ( ! empty( $stats_data ) && class_exists( 'AnWPFL_Cache' ) ) {
		anwp_football_leagues()->cache->set( $cache_key, $stats_data, 'anwp_match' );
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
		'title' => AnWPFL_Text::get_value( 'stats_players__shortcode__club', __( 'Club', 'anwp-football-leagues' ) ),
	],
	'player_name'    => [
		'title' => AnWPFL_Text::get_value( 'stats_players__shortcode__player', __( 'Player', 'anwp-football-leagues' ) ),
	],
	'position'       => [
		'title' => AnWPFL_Text::get_value( 'stats_players__shortcode__position', __( 'Position', 'anwp-football-leagues' ) ),
	],
	'appearance'     => [
		'title'         => '<svg class="anwp-icon--s20 anwp-icon--trans p-0"><use xlink:href="#icon-field"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__played_matches', __( 'Played Matches', 'anwp-football-leagues' ) ) ),
	],
	'started'        => [
		'title'         => '<svg class="anwp-icon--s20 anwp-icon--trans p-0"><use xlink:href="#icon-field-shirt"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__started', __( 'Started', 'anwp-football-leagues' ) ) ),
	],
	'minutes'        => [
		'title'         => '<svg class="anwp-icon--s20 anwp-icon--gray-900 p-0"><use xlink:href="#icon-watch"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__minutes', __( 'Minutes', 'anwp-football-leagues' ) ) ),
	],
	'cards_all'      => [
		'title'         => '<svg class="icon__card p-0"><use xlink:href="#icon-card_yr"></use></svg>',
		'headerTooltip' => esc_html__( 'Cards (All)', 'anwp-football-leagues-premium' ),
	],
	'cards_y'        => [
		'title'         => '<svg class="icon__card p-0"><use xlink:href="#icon-card_y"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__yellow_cards', __( 'Yellow Cards', 'anwp-football-leagues' ) ) ),
	],
	'cards_r'        => [
		'title'         => '<svg class="icon__card p-0"><use xlink:href="#icon-card_r"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__red_cards', __( 'Red Cards', 'anwp-football-leagues' ) ) ),
	],
	'goals'          => [
		'title'         => '<svg class="icon__ball anwp-icon--stats-goal p-0"><use xlink:href="#icon-ball"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__goals', __( 'Goals', 'anwp-football-leagues' ) ) ),
	],
	'goals_own'      => [
		'title'         => '<svg class="icon__ball icon__ball--own p-0"><use xlink:href="#icon-ball"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__own_goals', __( 'Own Goals', 'anwp-football-leagues' ) ) ),
	],
	'goals_penalty'  => [
		'title'         => '<svg class="icon__ball anwp-icon--stats-goal p-0"><use xlink:href="#icon-ball_penalty"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__goals_from_penalty', __( 'Goals (from penalty)', 'anwp-football-leagues' ) ) ),
	],
	'assists'        => [
		'title'         => '<svg class="icon__ball anwp-semi-opacity p-0"><use xlink:href="#icon-ball"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__assists', __( 'Assists', 'anwp-football-leagues' ) ) ),
	],
	'goals_conceded' => [
		'title'         => '<svg class="icon__ball icon__ball--conceded p-0"><use xlink:href="#icon-ball"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__goals_conceded', __( 'Goals Conceded', 'anwp-football-leagues' ) ) ),
	],
	'clean_sheets'   => [
		'title'         => '<svg class="icon__ball p-0"><use xlink:href="#icon-ball_canceled"></use></svg>',
		'headerTooltip' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__clean_sheets', __( 'Clean Sheets', 'anwp-football-leagues' ) ) ),
	],
];

/*
|--------------------------------------------------------------------
| Generate unique ID
|--------------------------------------------------------------------
*/
$tabulator_data_id = hash( 'crc32', wp_json_encode( $data ) );

$tabulator_data = [
	'columns' => [],
	'data'    => [],
];

foreach ( $columns as $column ) {
	$column = mb_strtolower( $column );
	$sorter = 'number';

	$col_stat_id = absint( mb_substr( $column, 3 ) );

	if ( $col_stat_id ) {
		$column_data = [
			'tooltip' => '',
			'text'    => '',
		];

		$stats_columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ) );

		if ( ! empty( $stats_columns ) && is_array( $stats_columns ) ) {
			$stat_items = array_values( wp_list_filter( $stats_columns, [ 'id' => absint( $col_stat_id ) ] ) );

			if ( ! empty( $stat_items[0] ) && 'simple' === $stat_items[0]->type ) {
				$column_data['tooltip'] = $stat_items[0]->name;
				$column_data['text']    = $stat_items[0]->abbr;
			}
		}

		$tabulator_data['columns'][] = [
			'title'                   => $column_data['text'],
			'field'                   => $column,
			'sorter'                  => $sorter,
			'headerTooltip'           => $column_data['tooltip'],
			'cssClass'                => 'number' === $sorter ? 'anwp-text-right' : '',
			'headerFilter'            => 'number',
			'headerFilterPlaceholder' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__min', __( 'min', 'anwp-football-leagues-premium' ) ) ),
			'headerFilterFunc'        => '>=',
		];
	} elseif ( 'ranking' === $column ) {
		$tabulator_data['columns'][] = [
			'title'    => '#',
			'field'    => $column,
			'sorter'   => 'number',
			'cssClass' => 'anwp-text-center',
		];
	} elseif ( 'player_name' === $column ) {
		$tabulator_data['columns'][] = [
			'title'        => empty( $col_headers[ $column ]['title'] ) ? '' : $col_headers[ $column ]['title'],
			'field'        => $column,
			'sorter'       => 'sortName',
			'headerFilter' => 'input',
		];
	} elseif ( 'club' === $column ) {
		$tabulator_data['columns'][] = [
			'title'              => empty( $col_headers[ $column ]['title'] ) ? '' : $col_headers[ $column ]['title'],
			'field'              => $column,
			'sorter'             => 'string',
			'headerFilter'       => 'input',
			'headerFilterParams' => [
				'clearable' => true,
			],
		];
	} elseif ( 'position' === $column ) {
		$tabulator_data['columns'][] = [
			'title'              => empty( $col_headers[ $column ]['title'] ) ? '' : $col_headers[ $column ]['title'],
			'field'              => $column,
			'sorter'             => 'string',
			'headerFilter'       => 'list',
			'headerFilterParams' => [
				'valuesLookup' => true,
				'clearable'    => true,
			],
		];
	} else {
		$tabulator_data['columns'][] = [
			'title'                   => empty( $col_headers[ $column ]['title'] ) ? '' : $col_headers[ $column ]['title'],
			'field'                   => $column,
			'sorter'                  => $sorter,
			'headerHozAlign'          => 'right',
			'cssClass'                => 'number' === $sorter ? 'anwp-text-right' : '',
			'headerFilter'            => 'number',
			'headerFilterPlaceholder' => esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__min', __( 'min', 'anwp-football-leagues-premium' ) ) ),
			'headerFilterFunc'        => '>=',
		];
	}
}

$tabulator_data['columns'][] = [
	'field'   => 'sort_name',
	'visible' => false,
];

$data_output = [];
$photo_dir   = wp_upload_dir()['baseurl'];

foreach ( $stats_data as $index => $player ) {
	/** @var array $player = [
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

	$player_data = [];

	foreach ( $columns as $column ) {
		if ( 'ranking' === $column ) {
			$player_data[ $column ] = $index + 1;
		} elseif ( 'player_name' === $column ) {
			if ( $args['links'] && ! empty( $player['link'] ) ) {
				if ( $args['photos'] && $player['photo'] ) {
					$player_data['player_name'] = '<div class="d-flex"><img loading="lazy" width="30" height="30" class="anwp-object-contain anwp-rounded mr-2 mb-0 border rounded anwp-w-30 anwp-h-30" src="' . esc_attr( $photo_dir . $player['photo'] ) . '"><a href="' . esc_attr( $player['link'] ) . '">' . esc_html( $player['player_name'] ) . '</a></div>';
				} else {
					$player_data['player_name'] = '<a href="' . esc_attr( $player['link'] ) . '">' . esc_html( $player['player_name'] ) . '</a>';
				}
			} else {
				if ( $args['photos'] && $player['photo'] ) {
					$player_data['player_name'] = '<div class="d-flex"><img loading="lazy" width="30" height="30" class="anwp-object-contain anwp-rounded mr-2 mb-0 border rounded anwp-w-30 anwp-h-30" src="' . esc_attr( $photo_dir . $player['photo'] ) . '">' . esc_html( $player['player_name'] ) . '</div>';
				} else {
					$player_data['player_name'] = esc_html( $player['player_name'] );
				}
			}
		} elseif ( 'club' === $column ) {
			foreach ( explode( ',', $player['clubs'] ) as $ii => $club_id ) {
				$args['club_column'] = mb_strtolower( $args['club_column'] );

				if ( in_array( $args['club_column'], [ 'logo', 'logo_title', 'logo_abbr' ], true ) ) {
					$club_logo = anwp_football_leagues()->club->get_club_logo_by_id( $club_id ) ?: '';

					if ( $club_logo ) {
						$club_logo = '<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-2 mb-0 anwp-w-20 anwp-h-20" src="' . esc_url( $club_logo ) . '" data-toggle="anwp-tooltip" data-tippy-content="' . esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $club_id ) ) . '">';
					}

					if ( 'logo_abbr' === $args['club_column'] ) {
						$player_data['club'] = '<div class="d-flex">' . $club_logo . anwp_football_leagues()->club->get_club_abbr_by_id( $club_id ) . '</div>';
					} elseif ( 'logo_title' === $args['club_column'] ) {
						$player_data['club'] = '<div class="d-flex">' . $club_logo . anwp_football_leagues()->club->get_club_title_by_id( $club_id ) . '</div>';
					} else {
						$player_data['club'] = $club_logo;
					}
				} elseif ( 'abbr' === $args['club_column'] ) {
					$player_data['club'] = anwp_football_leagues()->club->get_club_abbr_by_id( $club_id );
				} elseif ( 'title' === $args['club_column'] ) {
					$player_data['club'] = anwp_football_leagues()->club->get_club_title_by_id( $club_id );
				}
			}
		} else {
			$player_data[ $column ] = isset( $player[ $column ] ) ? ( is_numeric( $player[ $column ] ) ? ( $player[ $column ] + 0 ) : $player[ $column ] ) : 0;
		}
	}

	$player_data['sort_name'] = esc_attr( '. ' === mb_substr( $player['player_name'], 1, 2 ) ? mb_substr( $player['player_name'], 3 ) : $player['player_name'] );
	$player_data['position']  = esc_html( anwp_fl()->player->get_position_l10n( $player_data['position'] ?? '' ) );
	$tabulator_data['data'][] = $player_data;
}
?>
<script>window.AnWPFLTabulator = window.AnWPFLTabulator || {};</script>
<script>window.AnWPFLTabulator[ '<?php echo esc_attr( $tabulator_data_id ); ?>' ] = <?php echo wp_json_encode( $tabulator_data ); ?>;</script>
<?php wp_enqueue_script( 'anwp-fl-tabulator' ); ?>
<div class="anwp-b-wrap anwp-fl-tabulator-stats">

	<?php
	if ( ! empty( $args['header'] ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => $args['header'],
			],
			'general/header'
		);
	endif;
	?>

	<div class="stats-players-custom stats-players-custom--tabulator anwp-fl-data-tables"
			data-layout-mod="<?php echo esc_attr( $args['layout_mod'] ); ?>"
			data-rows="<?php echo esc_attr( $args['rows'] ); ?>" data-paging="<?php echo esc_attr( $args['paging'] ? 'yes' : '' ); ?>"
			data-id="<?php echo esc_attr( $tabulator_data_id ); ?>"></div>
</div>
