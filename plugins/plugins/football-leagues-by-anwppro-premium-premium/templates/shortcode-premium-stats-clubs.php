<?php
/**
 * The Template for displaying Stats for Clubs.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-stats-clubs.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.12.4
 *
 * @version       0.15.1
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
		'competition_id' => '',
		'multistage'     => 0,
		'limit'          => 10,
		'class'          => '',
		'club_column'    => 'logo_abbr',
		'sort_column'    => '',
		'header'         => '',
		'sort_order'     => 'DESC',
		'date_before'    => '',
		'date_after'     => '',
		'layout_mod'     => '',
	]
);

/*
|--------------------------------------------------------------------
| Prepare Columns
|--------------------------------------------------------------------
*/
$club_column = mb_strtolower( $args['club_column'] );
$columns     = wp_parse_slug_list( $args['columns'] );

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
$cache_key = 'FL-PRO-SHORTCODE_stats-clubs__' . md5( maybe_serialize( $args ) );

if ( class_exists( 'AnWPFL_Cache' ) && anwp_fl()->cache->get( $cache_key, 'anwp_match' ) ) {
	$stats_data = anwp_fl()->cache->get( $cache_key, 'anwp_match' );
} else {
	// Load data in default way
	$stats_data = anwp_fl_pro()->club->get_clubs_stats_totals_custom( $args, $columns );

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
| Generate unique ID
|--------------------------------------------------------------------
*/
$tabulator_data_id = hash( 'crc32', wp_json_encode( $args ) );

/*
|--------------------------------------------------------------------
| Table Header/Footer
|--------------------------------------------------------------------
*/
$series_map  = anwp_fl()->data->get_series();
$col_headers = array_merge(
	anwp_fl_pro()->stats->get_labels_match_club_stats(),
	[
		'club'           => [
			'abbr' => AnWPFL_Text::get_value( 'stats_clubs__shortcode__club__abbr', __( 'Club', 'anwp-football-leagues' ) ),
			'text' => AnWPFL_Text::get_value( 'stats_clubs__shortcode__club', __( 'Club', 'anwp-football-leagues' ) ),
		],
		'wins'           => [
			'abbr' => '<span class="d-inline-block anwp-text-white club-form__item-pro anwp-text-uppercase anwp-bg-success">' . $series_map['w'] . '</span>',
			'text' => AnWPFL_Text::get_value( 'stats_clubs__shortcode__wins', __( 'Wins', 'anwp-football-leagues' ) ),
		],
		'draws'          => [
			'abbr' => '<span class="d-inline-block anwp-text-white club-form__item-pro anwp-text-uppercase anwp-bg-warning">' . $series_map['d'] . '</span>',
			'text' => AnWPFL_Text::get_value( 'stats_clubs__shortcode__draws', __( 'Draws', 'anwp-football-leagues-premium' ) ),
		],
		'losses'         => [
			'abbr' => '<span class="d-inline-block anwp-text-white club-form__item-pro anwp-text-uppercase anwp-bg-danger">' . $series_map['l'] . '</span>',
			'text' => AnWPFL_Text::get_value( 'stats_clubs__shortcode__losses', __( 'Losses', 'anwp-football-leagues-premium' ) ),
		],
		'played'         => [
			'abbr' => '<svg class="anwp-icon--s20 anwp-icon--trans p-0"><use xlink:href="#icon-field"></use></svg>',
			'text' => AnWPFL_Text::get_value( 'stats_clubs__shortcode__played', __( 'Played', 'anwp-football-leagues' ) ),
		],
		'goals'          => [
			'abbr' => '<svg class="icon__ball anwp-icon--stats-goal p-0"><use xlink:href="#icon-ball"></use></svg>',
			'text' => AnWPFL_Text::get_value( 'stats_clubs__shortcode__goals', __( 'Goals', 'anwp-football-leagues' ) ),
		],
		'goals_conceded' => [
			'abbr' => '<svg class="icon__ball icon__ball--conceded p-0"><use xlink:href="#icon-ball"></use></svg>',
			'text' => AnWPFL_Text::get_value( 'stats_clubs__shortcode__goals_conceded', __( 'Goals Conceded', 'anwp-football-leagues' ) ),
		],
		'clean_sheets'   => [
			'abbr' => '<svg class="icon__ball p-0"><use xlink:href="#icon-ball_canceled"></use></svg>',
			'text' => AnWPFL_Text::get_value( 'stats_clubs__shortcode__clean_sheets', __( 'Clean Sheets', 'anwp-football-leagues' ) ),
		],
	]
);

if ( ! empty( $col_headers['cards_y']['abbr'] ) ) {
	$col_headers['cards_y']['abbr'] = '<svg class="icon__card p-0"><use xlink:href="#icon-card_y"></use></svg>';
}

if ( ! empty( $col_headers['cards_r']['abbr'] ) ) {
	$col_headers['cards_r']['abbr'] = '<svg class="icon__card p-0"><use xlink:href="#icon-card_r"></use></svg>';
}

/*
|--------------------------------------------------------------------
| Prepare Table Columns
|--------------------------------------------------------------------
*/
$tabulator_data['columns'] = [
	[
		'title'         => $col_headers['club']['abbr'],
		'headerTooltip' => $col_headers['club']['abbr'] !== $col_headers['club']['text'] ? $col_headers['club']['text'] : $col_headers['club']['abbr'],
		'field'         => 'club',
		'formatter'     => 'html',
		'sorter'        => 'sortName',
		'frozen'        => true,
		'cssClass'      => 'anwp-text-nowrap',
	],
];

foreach ( $columns as $column ) :
	$tabulator_data['columns'][] = [
		'title'         => empty( $col_headers[ $column ]['text'] ) ? '' : $col_headers[ $column ]['abbr'],
		'field'         => $column,
		'sorter'        => 'number',
		'headerTooltip' => empty( $col_headers[ $column ]['text'] ) ? '' : ( $col_headers[ $column ]['abbr'] !== $col_headers[ $column ]['text'] ? $col_headers[ $column ]['text'] : '' ),
		'cssClass'      => 'anwp-text-right',
	];
endforeach;

$tabulator_data['columns'][] = [
	'field'   => 'sort_name',
	'visible' => false,
];

/*
|--------------------------------------------------------------------
| Prepare Table Data
|--------------------------------------------------------------------
*/
foreach ( $stats_data as $club_id => $stat_data ) {

	$club_data = [];
	ob_start();

	if ( in_array( $club_column, [ 'logo', 'logo_title', 'logo_abbr' ], true ) ) :
		$club_logo = anwp_fl()->club->get_club_logo_by_id( $club_id );
		if ( $club_logo ) :
			echo '<img loading="lazy" width="20" height="20" class="align-middle d-inline-block anwp-object-contain mb-0 mr-2 anwp-w-20 anwp-h-20" src="' . esc_url( $club_logo ) . '">';
		endif;
	endif;

	if ( 'logo_abbr' === $club_column || 'abbr' === $club_column ) :
		echo esc_html( anwp_fl()->club->get_club_abbr_by_id( $club_id ) );
	endif;

	if ( 'logo_title' === $club_column || 'title' === $club_column ) :
		echo esc_html( anwp_fl()->club->get_club_title_by_id( $club_id ) );
	endif;

	$club_data['club'] = ob_get_clean();

	$club_data['sort_name'] = esc_html( anwp_fl()->club->get_club_title_by_id( $club_id ) );

	foreach ( $columns as $column ) {
		$club_data[ $column ] = $stat_data[ $column ] ?? '';
	}

	$tabulator_data['data'][] = $club_data;
}
?>
<script>window.AnWPFLTabulator = window.AnWPFLTabulator || {};</script>
<script>window.AnWPFLTabulator[ '<?php echo esc_attr( $tabulator_data_id ); ?>' ] = <?php echo wp_json_encode( $tabulator_data ); ?>;</script>
<?php wp_enqueue_script( 'anwp-fl-tabulator' ); ?>
<div class="anwp-b-wrap anwp-fl-tabulator-stats">

	<?php
	if ( ! empty( $args['header'] ) ) :
		anwp_fl()->load_partial(
			[
				'text'  => $args['header'],
				'class' => 'anwpfl-suspension-risk__header',
			],
			'general/header'
		);
	endif;
	?>

	<div class="shortcode-stats_clubs_custom anwp-user-select-none stats-clubs--shortcode-premium <?php echo esc_attr( $args['class'] ); ?>"
		data-layout-mod="<?php echo esc_attr( $args['layout_mod'] ); ?>" data-id="<?php echo esc_attr( $tabulator_data_id ); ?>"></div>
</div>
