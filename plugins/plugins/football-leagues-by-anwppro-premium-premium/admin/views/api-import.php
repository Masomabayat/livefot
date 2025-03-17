<?php
/**
 * API Import
 *
 * @link       https://anwp.pro
 * @since      0.15.4
 *
 * @package    AnWP_Football_Leagues
 * @subpackage AnWP_Football_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues' ) );
}

// phpcs:ignore WordPress.Security.NonceVerification
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';
$time_limit = @set_time_limit( 300 ) ? 300 : ini_get( 'max_execution_time' ); // phpcs:ignore

/*
|--------------------------------------------------------------------
| Import Options
|--------------------------------------------------------------------
*/
$saved_api_configs = anwp_fl_pro()->api->get_data( [ 'get_data_method' => 'get_data_league_actions' ] );
$api_import_config = get_option( 'anwpfl_api_import_config' );

$api_import_options = [
	'rest_root'               => esc_url_raw( rest_url() ),
	'rest_nonce'              => wp_create_nonce( 'wp_rest' ),
	'spinner_url'             => admin_url( 'images/spinner.gif' ),
	'new_competition_url'     => admin_url( 'post-new.php?post_type=anwp_competition' ),
	'leagues_url'             => admin_url( 'admin.php?page=anwp-football-leagues-api&tab=leagues' ),
	'settings_url'            => admin_url( 'admin.php?page=anwp-football-leagues-api&tab=settings' ),
	'dashboard_url'           => admin_url( 'admin.php?page=anwp-football-leagues-api' ),
	'live_url'                => admin_url( 'admin.php?page=anwp_football_leagues_premium_options#anwp-fl-pro-options--live' ),
	'duplicate_games_url'     => admin_url( 'edit.php?post_type=anwp_match&post_status=all&_anwpfl_match_duplicates=yes' ),
	'duplicate_teams_url'     => admin_url( 'edit.php?post_type=anwp_club&post_status=all&_anwpfl_club_duplicates=yes' ),
	'api_provider'            => empty( $api_import_config['provider'] ) ? null : $api_import_config['provider'], // axios required
	'bookmakers'              => anwp_football_leagues_premium()->match->get_bookmaker_list(),
	'l10n_datepicker'         => anwp_football_leagues_premium()->data->get_vue_datepicker_locale(),
	'saved_configs'           => anwp_football_leagues_premium()->api->get_data(
		[
			'get_data_method' => 'get_data_saved_configs',
			'output_key'      => 'external_value',
		]
	),
	'scheduled_finished'      => wp_next_scheduled( 'anwp_fl_api_scheduled_finished' ),
	'scheduled_kickoff'       => wp_next_scheduled( 'anwp_fl_api_scheduled_kickoff' ),
	'scheduled_predictions'   => wp_next_scheduled( 'anwp_fl_api_scheduled_predictions' ),
	'scheduled_odds'          => wp_next_scheduled( 'anwp_fl_api_scheduled_odds' ),
	'scheduled_injuries'      => wp_next_scheduled( 'anwp_fl_api_scheduled_injuries' ),
	'scheduled_lineups'       => wp_next_scheduled( 'anwp_fl_api_scheduled_lineups' ),
	'scheduled_live'          => wp_next_scheduled( 'anwp_fl_api_scheduled_live' ),
	'saved_dashboard_configs' => $saved_api_configs,
];

$is_initial_setup = empty( $api_import_config );
?>
<script type="text/javascript">
	window._anwpAPIImport = <?php echo wp_json_encode( $api_import_options ); ?>;
	window._anwpAPIConfig = <?php echo wp_json_encode( $is_initial_setup ? [] : $api_import_config ); ?>;
</script>
<div class="wrap anwp-b-wrap">

	<?php if ( $is_initial_setup || 'settings' === $active_tab ) : ?>
		<?php AnWP_Football_Leagues_Premium::include_file( 'admin/views/api-import--settings' ); ?>
	<?php elseif ( 'leagues' === $active_tab ) : ?>
		<?php AnWP_Football_Leagues_Premium::include_file( 'admin/views/api-import--leagues' ); ?>
	<?php else : ?>
		<?php AnWP_Football_Leagues_Premium::include_file( 'admin/views/api-import--dashboard' ); ?>
	<?php endif; ?>

</div>
