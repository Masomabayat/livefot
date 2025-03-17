<?php
/**
 * Stats Configurator For AnWP Football Leagues Premium
 *
 * @link       https://anwp.pro
 * @since      0.9.7
 *
 * @package    AnWP_Football_Leagues
 * @subpackage AnWP_Football_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Must check that the user has the required capability
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues' ) );
}

/*
|--------------------------------------------------------------------
| Column Options
|--------------------------------------------------------------------
*/
$options_match_club = [
	[
		'type'       => 'default',
		'name'       => 'Default',
		'abbr'       => '',
		'field_slug' => '',
		'visibility' => '',
		'postfix'    => '',
		'prefix'     => '',
		'digits'     => '',
		'max'        => '',
	],
	[
		'type'       => 'simple',
		'name'       => 'Simple',
		'abbr'       => '',
		'visibility' => '',
		'postfix'    => '',
		'prefix'     => '',
		'digits'     => '',
		'max'        => '',
	],
	[
		'type'       => 'time',
		'name'       => 'Time',
		'abbr'       => '',
		'visibility' => '',
		'max'        => '',
	],
	[
		'type'       => 'calculated',
		'name'       => 'Calculated',
		'abbr'       => '',
		'visibility' => '',
		'field_1'    => '',
		'field_2'    => '',
		'calc'       => '',
		'digits'     => '',
		'postfix'    => '',
		'prefix'     => '',
		'max'        => '',
	],
];

$options_match_player = [
	[
		'type'       => 'default',
		'name'       => 'Default',
		'abbr'       => '',
		'group'      => '',
		'field_slug' => '',
		'visibility' => '',
	],
	[
		'type'       => 'simple',
		'name'       => 'Simple',
		'abbr'       => '',
		'group'      => '',
		'visibility' => '',
		'postfix'    => '',
		'prefix'     => '',
		'digits'     => '',
	],
	[
		'type'       => 'time',
		'name'       => 'Time',
		'abbr'       => '',
		'group'      => '',
		'visibility' => '',
	],
	[
		'type'       => 'calculated',
		'name'       => 'Calculated',
		'abbr'       => '',
		'group'      => '',
		'visibility' => '',
		'field_1'    => '',
		'field_2'    => '',
		'calc'       => '',
		'digits'     => '',
		'postfix'    => '',
		'prefix'     => '',
	],
	[
		'type'       => 'composed',
		'name'       => 'Composed',
		'abbr'       => '',
		'visibility' => '',
		'group'      => '',
		'separator'  => '',
		'field_1'    => '',
		'field_2'    => '',
		'field_3'    => '',
	],
];

/*
|--------------------------------------------------------------------
| Localization
|--------------------------------------------------------------------
*/
$config_l10n = [
	'all'                       => esc_html__( 'all', 'anwp-football-leagues-premium' ),
	'are_you_sure'              => esc_html__( 'Are you sure?', 'anwp-football-leagues' ),
	'available_column_types'    => esc_html__( 'Available Column Types', 'anwp-football-leagues-premium' ),
	'calculation'               => esc_html__( 'Calculation', 'anwp-football-leagues-premium' ),
	'cancel'                    => esc_html__( 'Cancel', 'anwp-football-leagues' ),
	'club_statistics'           => esc_html__( 'Club Statistics', 'anwp-football-leagues-premium' ),
	'confirm_delete'            => esc_html__( 'Confirm Delete', 'anwp-football-leagues' ),
	'default_field'             => esc_html__( 'Default Field', 'anwp-football-leagues-premium' ),
	'delete_stats'              => esc_html__( 'Delete Stats', 'anwp-football-leagues-premium' ),
	'difference'                => esc_html__( 'difference', 'anwp-football-leagues-premium' ),
	'digits'                    => esc_html__( 'Digits', 'anwp-football-leagues-premium' ),
	'digits_after_decimal'      => esc_html__( 'Digits after decimal point', 'anwp-football-leagues-premium' ),
	'do_not_remove_statistical' => esc_html__( 'Do not remove Statistical parameter which has been used in finished games.', 'anwp-football-leagues-premium' ),
	'field_1'                   => esc_html__( 'Field 1', 'anwp-football-leagues-premium' ),
	'field_2'                   => esc_html__( 'Field 2', 'anwp-football-leagues-premium' ),
	'field_3'                   => esc_html__( 'Field 3', 'anwp-football-leagues-premium' ),
	'field_players'             => esc_html__( 'Field Players', 'anwp-football-leagues-premium' ),
	'full_name'                 => esc_html_x( 'Full Name', 'statistical parameter', 'anwp-football-leagues-premium' ),
	'full_width'                => esc_html__( 'Full Width', 'anwp-football-leagues-premium' ),
	'goalkeepers'               => esc_html__( 'Goalkeepers', 'anwp-football-leagues' ),
	'group'                     => esc_html_x( 'Group', 'statistic config', 'anwp-football-leagues-premium' ),
	'match'                     => esc_html__( 'Match', 'anwp-football-leagues' ),
	'maximum_value_desc'        => esc_html__( 'Maximum value. Used to calculate the full bar width in the default layout.', 'anwp-football-leagues-premium' ),
	'player_statistics'         => esc_html__( 'Player Statistics', 'anwp-football-leagues-premium' ),
	'postfix'                   => esc_html__( 'Postfix', 'anwp-football-leagues-premium' ),
	'prefix'                    => esc_html__( 'Prefix', 'anwp-football-leagues-premium' ),
	'ratio'                     => esc_html__( 'ratio', 'anwp-football-leagues-premium' ),
	'related_field'             => esc_html_x( 'Related Field', 'statistic config', 'anwp-football-leagues-premium' ),
	'remove'                    => esc_html__( 'Remove', 'anwp-football-leagues-premium' ),
	'save'                      => esc_html__( 'Save', 'anwp-football-leagues' ),
	'select'                    => esc_html__( 'select', 'anwp-football-leagues' ),
	'separator'                 => esc_html_x( 'Separator', 'statistic config', 'anwp-football-leagues-premium' ),
	'short_name'                => esc_html_x( 'Short Name', 'statistical parameter', 'anwp-football-leagues-premium' ),
	'stats_parameter_added'     => esc_html__( 'Statistical parameter added to the Layout', 'anwp-football-leagues-premium' ),
	'sum'                       => esc_html__( 'sum', 'anwp-football-leagues-premium' ),
	'type'                      => esc_html__( 'Type', 'anwp-football-leagues' ),
	'visibility'                => esc_html__( 'Visibility', 'anwp-football-leagues-premium' ),
];

/*
|--------------------------------------------------------------------
| Options - API Advanced Club Stats
|--------------------------------------------------------------------
*/
$advanced_api_club_options = [
	'shots_off_goal'   => esc_html__( 'Shots off Goal', 'anwp-football-leagues-premium' ),
	'blocked_shots'    => esc_html__( 'Blocked Shots', 'anwp-football-leagues-premium' ),
	'shots_insidebox'  => esc_html__( 'Shots insidebox', 'anwp-football-leagues-premium' ),
	'shots_outsidebox' => esc_html__( 'Shots outsidebox', 'anwp-football-leagues-premium' ),
	'goalkeeper_saves' => esc_html__( 'Goalkeeper Saves', 'anwp-football-leagues-premium' ),
	'total_passes'     => esc_html__( 'Total passes', 'anwp-football-leagues-premium' ),
	'passes_accurate'  => esc_html__( 'Passes accurate', 'anwp-football-leagues-premium' ),
];

/*
|--------------------------------------------------------------------
| Vue Data
|--------------------------------------------------------------------
*/
$data = [
	'isActiveTeamAPIAdvancedStats'   => AnWPFL_Premium_API::get_config_value( 'club_advanced_stats', 'no' ), // old - 'apiImportClubAdvancedStats'
	'optionsTeamAPIAdvancedStats'    => $advanced_api_club_options, // old - 'optionsMatchClubAPIAdvancedStats'
	'optionsTeamColumnTypes'         => $options_match_club, // old - 'optionsMatchClubColumn'
	'optionsTeamCoreStats'           => anwp_fl_pro()->stats->get_match_stats_club_core_options(), // old - 'optionsMatchClubCore'
	'optionsPlayerColumnTypes'       => $options_match_player, // old - optionsMatchPlayerColumn
	'optionsPlayerCoreStats'         => anwp_fl_pro()->stats->get_match_stats_player_core_options(), // old - optionsMatchPlayerCore
	'teamStatColumns'                => get_option( 'anwpfl_stats_columns_match_club' ), // old - matchClubColumns
	'teamStatColumnLastId'           => absint( get_option( 'anwpfl_stats_columns_match_club_last_id' ) ), // old - 'matchClubColumnLastId'
	'playerStatColumns'              => get_option( 'anwpfl_stats_columns_match_player' ), // old - matchPlayerColumns
	'playerStatColumnLastId'         => absint( get_option( 'anwpfl_stats_columns_match_player_last_id' ) ), // old - matchPlayerColumnLastId
	'isActivePlayerAPIAdvancedStats' => AnWPFL_Premium_API::get_config_value( 'player_stats', 'no' ), // old - apiImportPlayerStats
	'l10n'                           => $config_l10n,
	'dbStatIds'                      => anwp_fl_pro()->stats->get_player_db_stat_ids() ?: [],
	'spinnerUrl'                     => admin_url( 'images/spinner.gif' ),
	'rest_root'                      => esc_url_raw( rest_url() ),
	'rest_nonce'                     => wp_create_nonce( 'wp_rest' ),
];
?>
<script type="text/javascript">
	window._anwpFLStatsConfig = <?php echo wp_json_encode( $data ); ?>;
</script>

<div class="wrap anwp-b-wrap">
	<div class="mb-2 pb-1">
		<h1 class="mb-0"><?php echo esc_html__( 'Statistics Configurator', 'anwp-football-leagues-premium' ); ?></h1>
	</div>
	<div class="anwp-admin-docs-link d-flex align-items-center table-info border p-2 border-info my-2">
		<svg class="anwp-icon anwp-icon--octi anwp-icon--s16">
			<use xlink:href="#icon-book"></use>
		</svg>
		<b class="mx-2">Documentation:</b>
		<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/2/articles/510-club-statistics-match">Match > Club Statistics</a>
		<span class="mx-2 anwp-small">|</span>
		<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/2/articles/509-custom-player-statistics-match">Match > Player Statistics</a>
	</div>

	<div id="fl-app-stats-config"></div>
</div>
