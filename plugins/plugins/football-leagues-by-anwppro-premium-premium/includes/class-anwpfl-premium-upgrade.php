<?php
/**
 * AnWP Football Leagues Premium Upgrade.
 *
 * @since   0.15.4
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Upgrade class.
 */
class AnWPFL_Premium_Upgrade {

	/**
	 * Current DB structure version.
	 *
	 * @var    int
	 * @since  0.3.0
	 */
	const DB_VERSION = 9;

	/**
	 * Parent plugin class.
	 *
	 * @var    AnWP_Football_Leagues_Premium
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( AnWP_Football_Leagues_Premium $plugin ) {

		// Check Plugin DB Version
		add_action( 'init', [ $this, 'update_db_check' ], 1 );

		// Check for Setting update
		add_action( 'cmb2_after_init', [ $this, 'update_setting' ] );

		add_filter( 'anwpfl/toolbox-updater/get_updater_tasks', [ $this, 'get_toolbox_updater_tasks' ] );
		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Register REST routes.
	 */
	public function add_rest_routes() {

		register_rest_route(
			'anwpfl/api-toolbox-updater',
			'/sync_table__anwpfl_player/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_sync_table_anwpfl_player' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'anwpfl/api-toolbox-updater',
			'/migrate_table__anwpfl_player_stats/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_migrate_table_anwpfl_player_stats' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'anwpfl/api-toolbox-updater',
			'/drop_table__anwpfl_player_stats/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_drop_table_anwpfl_player_stats' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'anwpfl/api-toolbox-updater',
			'/migrate_api_league_actions/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_migrate_api_league_actions' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'anwpfl/api-toolbox-updater',
			'/migrate_prediction_from_meta/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_migrate_prediction_from_meta' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'anwpfl/api-toolbox-updater',
			'/migrate_formations_from_meta/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_migrate_formations_from_meta' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'anwpfl/api-toolbox-updater',
			'/migrate_transfers_from_meta/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_migrate_transfers_from_meta' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'anwpfl/api-toolbox-updater',
			'/migrate_previously_scheduled_api_tasks/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_migrate_previously_scheduled_api_tasks' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Check Plugin's DB version.
	 *
	 * @since 0.1.0
	 */
	public function update_db_check() {
		if ( (int) get_option( 'anwpfl_premium_db_version' ) < self::DB_VERSION && flbap_fs()->can_use_premium_code() && flbap_fs()->is_premium() ) {
			$this->update_db();
		}
	}

	/**
	 * Update plugin DB
	 *
	 * @since 0.1.0 (2018-04-20)
	 */
	public function update_db(): bool {

		global $wpdb;

		$charset_collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$charset_collate = $wpdb->get_charset_collate();
		}

		/*
		anwpfl_transfers
			transfer_status: 0 - rumour, 1 - official
			transfer_window: 0 - not set, 1 - pre, 2 - mid
			club_in/club_out: 0 - not set, 1 - unknown, 2 - custom text, 3... - club ID
		*/

		$sql = "
CREATE TABLE {$wpdb->prefix}anwpfl_import_mapping (
  mapping_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  provider varchar(20) DEFAULT '' NOT NULL,
  type varchar(20) DEFAULT '' NOT NULL,
  local_value bigint(20) UNSIGNED NOT NULL,
  external_value varchar(20) DEFAULT '' NOT NULL,
  status varchar(20) DEFAULT '' NOT NULL,
  extra_data text NOT NULL,
  PRIMARY KEY  (mapping_id),
  UNIQUE KEY uniq (provider,type,local_value),
  KEY provider (provider),
  KEY type (type),
  KEY local_value (local_value),
  KEY status (status)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}anwpfl_predictions (
  match_id bigint(20) UNSIGNED NOT NULL,
  prediction_advice varchar(200) DEFAULT '' NOT NULL,
  prediction_advice_alt varchar(200) DEFAULT '' NOT NULL,
  prediction_percent varchar(500) DEFAULT '' NOT NULL,
  prediction_comparison text NOT NULL,
  PRIMARY KEY  (match_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}anwpfl_formations (
  match_id bigint(20) UNSIGNED NOT NULL,
  home_club_shirt varchar(200) DEFAULT '' NOT NULL,
  away_club_shirt varchar(200) DEFAULT '' NOT NULL,
  formation text NOT NULL,
  formation_extra text NOT NULL,
  PRIMARY KEY  (match_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}anwpfl_transfers (
  transfer_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  player_id bigint(20) UNSIGNED NOT NULL,
  season_id bigint(20) UNSIGNED NOT NULL,
  club_in bigint(20) UNSIGNED NOT NULL,
  club_out bigint(20) UNSIGNED NOT NULL,
  club_in_text varchar(200) DEFAULT '' NOT NULL,
  club_out_text varchar(200) DEFAULT '' NOT NULL,
  fee varchar(200) DEFAULT '' NOT NULL,
  transfer_date date NOT NULL default '0000-00-00',
  transfer_end_date date NOT NULL default '0000-00-00',
  transfer_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  transfer_window tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  api_transfer_hash varchar(200) DEFAULT '' NOT NULL,
  api_transfer_hash_full varchar(200) DEFAULT '' NOT NULL,
  PRIMARY KEY  (transfer_id),
  KEY player_id (player_id),
  KEY season_id (season_id),
  KEY club_in (club_in),
  KEY club_out (club_out),
  KEY transfer_date (transfer_date),
  KEY transfer_status (transfer_status),
  KEY transfer_window (transfer_window)
) $charset_collate;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		$success = empty( $wpdb->last_error );

		if ( (int) get_option( 'anwpfl_premium_db_version' ) < self::DB_VERSION && $success ) {
			update_option( 'anwpfl_premium_db_version', self::DB_VERSION, true );
		}

		return $success;
	}

	/**
	 * Check Plugin's setting for update.
	 *
	 * @since 0.9.2
	 * @return void
	 */
	public function update_setting() {
		global $wpdb;

		// Get current saved settings version
		$settings_version = absint( get_option( 'anwpfl_premium_settings_version' ) );
		$update_version   = false;

		if ( ! flbap_fs()->can_use_premium_code() || ! flbap_fs()->is_premium() ) {
			return;
		}

		// Move old premium settings to the FL+ Configurator
		if ( $settings_version < 1 ) {
			if ( function_exists( 'cmb2_get_option' ) ) {

				// matchweeks_as_slides
				$matchweeks_as_slides = anwp_football_leagues()->get_option_value( 'matchweeks_as_slides' );
				cmb2_update_option( 'anwp_football_leagues_premium_options', 'matchweeks_as_slides', $matchweeks_as_slides );

				// match_scoreboard
				$match_scoreboard = anwp_football_leagues()->get_option_value( 'match_scoreboard' );
				cmb2_update_option( 'anwp_football_leagues_premium_options', 'match_scoreboard', $match_scoreboard );

				// match_scoreboard_image
				$match_scoreboard_image = anwp_football_leagues()->get_option_value( 'match_scoreboard_image' );
				cmb2_update_option( 'anwp_football_leagues_premium_options', 'match_scoreboard_image', $match_scoreboard_image );

				// match_scoreboard_image_id
				$match_scoreboard_image_id = anwp_football_leagues()->get_option_value( 'match_scoreboard_image_id' );
				cmb2_update_option( 'anwp_football_leagues_premium_options', 'match_scoreboard_image_id', $match_scoreboard_image_id );
			}

			$update_version   = true;
			$settings_version = 1;
		}

		/*
		|--------------------------------------------------------------------
		| Stats - Match Club
		| @since 0.9.7
		|--------------------------------------------------------------------
		*/
		if ( $settings_version < 2 ) {

			$core_stats_sections = [];

			// Check maybe previous settings are set
			if ( function_exists( 'cmb2_get_option' ) ) {
				$core_stats_sections = AnWPFL_Premium_Options::get_value( 'match_stats_order' );
			}

			$max_core_options = [
				'yellowCards'   => 10,
				'yellow2RCards' => 10,
				'redCards'      => 10,
				'corners'       => 25,
				'fouls'         => 50,
				'offsides'      => 25,
				'possession'    => 100,
				'shots'         => 50,
				'shotsOnGoals'  => 50,
				'goals'         => 10,
			];

			if ( empty( $core_stats_sections ) || ! is_array( $core_stats_sections ) ) {
				$core_stats_sections = [
					'yellowCards',
					'yellow2RCards',
					'redCards',
					'corners',
					'fouls',
					'offsides',
					'possession',
					'shots',
					'shotsOnGoals',
					'goals',
				];
			}

			$stats_match_club_core_options = anwp_football_leagues_premium()->stats->get_match_stats_club_core_options();
			$stats_columns_match_club      = [];
			$stats_match_club_last_id      = 0;

			foreach ( $core_stats_sections as $stats_section ) {
				$stats_section_trimmed = ltrim( $stats_section, '_' );

				if ( ! empty( $stats_match_club_core_options[ $stats_section_trimmed ] ) ) {
					$stats_columns_match_club[] = (object) [
						'type'       => 'default',
						'name'       => $stats_match_club_core_options[ $stats_section_trimmed ],
						'field_slug' => $stats_section_trimmed,
						'visibility' => $stats_section_trimmed === $stats_section ? '' : 'hidden',
						'postfix'    => '',
						'prefix'     => '',
						'digits'     => '',
						'max'        => $max_core_options[ $stats_section_trimmed ] ?? '',
						'id'         => ++ $stats_match_club_last_id,
					];
				}
			}

			$stats_columns_match_club = wp_json_encode( $stats_columns_match_club );

			if ( $stats_columns_match_club ) {
				update_option( 'anwpfl_stats_columns_match_club', $stats_columns_match_club, false );
			}

			if ( absint( $stats_match_club_last_id ) ) {
				update_option( 'anwpfl_stats_columns_match_club_last_id', absint( $stats_match_club_last_id ), false );
			}

			$update_version   = true;
			$settings_version = 2;
		}

		if ( $settings_version < 4 ) {
			/* removed update script - no more actual */

			$update_version   = true;
			$settings_version = 4;
		}

		if ( $settings_version < 5 ) {
			if ( ! empty( AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_key' ) ) ) {
				$api_config = [
					'key'                         => AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_key' ),
					'initial_provider'            => '',
					'provider'                    => 'api-football',
					'request_url'                 => 'rapid' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_url' ) ? 'rapid' : 'direct',
					'cache_requests'              => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_url' ) ? 'yes' : 'no',
					'stadiums'                    => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_stadiums' ) ? 'yes' : 'no',
					'referees'                    => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_referees' ) ? 'yes' : 'no',
					'coaches'                     => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_coaches' ) ? 'yes' : 'no',
					'club_advanced_stats'         => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_club_advanced_stats' ) ? 'yes' : 'no',
					'player_stats'                => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_player_stats' ) ? 'yes' : 'no',
					'lineups_formation'           => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_lineups_formation' ) ? 'yes' : 'no',
					'reset_squad'                 => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_reset_squad' ) ? 'yes' : 'no',
					'live'                        => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_live' ) ? 'yes' : 'no',
					'predictions'                 => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_predictions' ) ? 'yes' : 'no',
					'predictions_data'            => sanitize_key( AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_predictions_data' ) ),
					'prediction_show_bottom_line' => 'yes' === AnWPFL_Premium_Options::get_value( 'prediction_show_bottom_line' ) ? 'yes' : 'no',
					'photos_player'               => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_photos_player' ) ? 'yes' : 'no',
					'photos_player_force'         => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_photos_player_force' ) ? 'yes' : 'no',
					'photos_coach'                => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_photos_coach' ) ? 'yes' : 'no',
					'club_logos'                  => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_club_logos' ) ? 'yes' : 'no',
					'stadium_photos'              => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_stadium_photos' ) ? 'yes' : 'no',
					'update_kickoff_0'            => 'no' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_update_kickoff_0' ) ? 'no' : 'yes',
					'sslverify'                   => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_sslverify' ) ? 'yes' : 'no',
					'odds'                        => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_ods' ) ? 'yes' : 'no',
					'odds_clickable'              => 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_ods_clickable' ) ? 'yes' : 'no',
				];

				// Bookmakers - books
				$disabled_books = AnWPFL_Premium_Options::get_value( 'import_api_api_ods_disabled_books' ) ?: [];

				if ( ! empty( $disabled_books ) ) {
					$disabled_books = array_unique( array_map( 'absint', $disabled_books ) );
				}

				$api_config['books'] = array_values( array_diff( array_keys( anwp_football_leagues_premium()->match->get_bookmaker_options() ), $disabled_books ) );

				// aff link
				$api_config['aff_links'] = [];

				foreach ( array_keys( anwp_football_leagues_premium()->match->get_bookmaker_options() ) as $book_id ) {
					if ( ! empty( AnWPFL_Premium_Options::get_value( 'import_api_bookmaker_' . absint( $book_id ) ) ) ) {
						$api_config['aff_links'][ $book_id ] = AnWPFL_Premium_Options::get_value( 'import_api_bookmaker_' . absint( $book_id ) );
					}
				}

				update_option( 'anwpfl_api_import_config', $api_config );

				if ( 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_mode' ) && 'yes' === AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_live' ) && 'yes' !== AnWPFL_Premium_Options::get_value( 'import_api_api_football_com_live_hybrid' ) ) {
					if ( function_exists( 'cmb2_update_option' ) ) {
						cmb2_update_option( 'anwp_football_leagues_premium_options', 'match_live_mode', '' );
					}
				}
			}

			$update_version   = true;
			$settings_version = 5;
		}

		/*
		|--------------------------------------------------------------------
		| API Scheduled Tasks - v0.16.0
		|--------------------------------------------------------------------
		*/
		if ( $settings_version < 6 ) {

			// Transfers
			$wpdb->query( "UPDATE $wpdb->posts SET comment_count = 1 WHERE post_status = 'publish' AND post_type = 'anwp_transfer'" );

			// API Scheduled Tasks
			$tasks_map = [
				'anwp_fl_api_import_finished',
				'anwp_fl_api_import_kickoff',
				'anwp_fl_api_import_predictions',
				'anwp_fl_api_import_odds',
				'anwp_fl_api_import_injuries',
				'anwp_fl_live_prepare_api_import_hook',
				'anwp_fl_api_import_prepare_lineups',
			];

			$previously_scheduled = [];

			$all_crons = _get_cron_array();

			if ( ! empty( $all_crons ) ) {
				foreach ( $tasks_map as $scheduled_task ) {
					foreach ( $all_crons as $cron ) {
						if ( isset( $cron[ $scheduled_task ] ) ) {
							$previously_scheduled[] = $scheduled_task;
							wp_unschedule_hook( $scheduled_task );
						}
					}
				}
			}

			if ( ! empty( $previously_scheduled ) ) {
				update_option( 'anwpfl_premium_previously_scheduled_016', $previously_scheduled, true );
			}

			$update_version   = true;
			$settings_version = 6;
		}

		if ( $update_version ) {
			update_option( 'anwpfl_premium_settings_version', $settings_version, true );
		}
	}

	/**
	 */
	public function get_toolbox_updater_tasks( $tasks ) {
		global $wpdb;

		/*
		|--------------------------------------------------------------------
		| Check database structure mirrors statistic's config
		|--------------------------------------------------------------------
		*/
		$stat_columns_to_create = anwp_fl_pro()->stats->check_player_stats_db_sync_needed();

		if ( $stat_columns_to_create ) {
			$tasks[] = [
				'status'      => 'pending',
				'total'       => count( $stat_columns_to_create ),
				'order'       => 5,
				'title'       => 'Sync table "players" with player statistics configuration',
				'slug'        => 'sync_table__anwpfl_player',
				'description' => 'Create new columns in the "players" table according to the statistics configuration.',
				'subtasks'    => $stat_columns_to_create,
			];
		}

		/*
		|--------------------------------------------------------------------
		| Migrate Player Statistics to players table
		|--------------------------------------------------------------------
		*/
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}anwpfl_player_stats';" ) ) {
			if ( intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}anwpfl_player_stats;" ) ) ) {
				$games_to_migrate = $wpdb->get_col( "SELECT DISTINCT match_id FROM {$wpdb->prefix}anwpfl_player_stats;" );

				$tasks[] = [
					'status'      => 'pending',
					'total'       => count( $games_to_migrate ),
					'order'       => 10,
					'title'       => 'Migrate table "player_stats" to "players"',
					'slug'        => 'migrate_table__anwpfl_player_stats',
					'description' => 'Move data from "player_stats" table to "players" table.',
					'subtasks'    => $games_to_migrate,
				];
			}

			$tasks[] = [
				'status'      => 'pending',
				'total'       => 1,
				'order'       => 20,
				'title'       => 'Drop table "player_stats"',
				'slug'        => 'drop_table__anwpfl_player_stats',
				'description' => 'The table is no longer required and can be safely deleted.',
				'subtasks'    => [ 1 ],
			];
		}

		/*
		|--------------------------------------------------------------------
		| Migrate Predictions to dedicated table
		|--------------------------------------------------------------------
		*/
		$predictions_to_migrate_qty = intval( $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE `meta_key` = '_anwpfl_prediction_advice';" ) );

		if ( $predictions_to_migrate_qty ) {
			$predictions_to_migrate = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE `meta_key` = '_anwpfl_prediction_advice';" );

			$tasks[] = [
				'status'      => 'pending',
				'total'       => count( $predictions_to_migrate ),
				'order'       => 60,
				'title'       => 'Migrate predictions to "predictions" table',
				'slug'        => 'migrate_prediction_from_meta',
				'description' => 'Move all predictions from "postmeta" to new "predictions" table.',
				'subtasks'    => array_values( $predictions_to_migrate ),
			];
		}

		/*
		|--------------------------------------------------------------------
		| Migrate Formations to dedicated table
		|--------------------------------------------------------------------
		*/
		$formations_to_migrate_qty = intval( $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE `meta_key` = '_anwpfl_match_formation';" ) );

		if ( $formations_to_migrate_qty ) {
			$formations_to_migrate = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE `meta_key` = '_anwpfl_match_formation';" );

			$tasks[] = [
				'status'      => 'pending',
				'total'       => count( $formations_to_migrate ),
				'order'       => 60,
				'title'       => 'Migrate formations to "formations" table',
				'slug'        => 'migrate_formations_from_meta',
				'description' => 'Move all formation data from "postmeta" to new "formations" table.',
				'subtasks'    => array_values( $formations_to_migrate ),
			];
		}

		/*
		|--------------------------------------------------------------------
		| Migrate Transfers to dedicated table
		|--------------------------------------------------------------------
		*/
		$transfers_to_migrate_qty = intval( $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE `post_status` = 'publish' AND `post_type` = 'anwp_transfer' AND `comment_count` = 1 ;" ) );

		if ( $transfers_to_migrate_qty ) {
			$transfers_to_migrate = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE `post_status` = 'publish' AND `post_type` = 'anwp_transfer' AND `comment_count` = 1;" );

			$tasks[] = [
				'status'      => 'pending',
				'total'       => count( $transfers_to_migrate ),
				'order'       => 80,
				'title'       => 'Migrate transfers to "transfers" table',
				'slug'        => 'migrate_transfers_from_meta',
				'description' => 'Move all transfers data from "postmeta" to new "transfers" table.',
				'subtasks'    => array_values( $transfers_to_migrate ),
			];
		}

		/*
		|--------------------------------------------------------------------
		| Migrate API Import Actions to a new format
		|--------------------------------------------------------------------
		*/
		if ( get_option( 'anwpfl_api_import_api-football_dashboard' ) ) {
			$tasks[] = [
				'status'      => 'pending',
				'total'       => 1,
				'order'       => 90,
				'title'       => 'Migrate API Import Actions to a new format',
				'slug'        => 'migrate_api_league_actions',
				'description' => 'Migrate API Import Actions to a new structure.',
				'subtasks'    => [ 1 ],
			];
		}

		/*
		|--------------------------------------------------------------------
		| Migrate Scheduled Tasks
		|--------------------------------------------------------------------
		*/
		if ( get_option( 'anwpfl_premium_previously_scheduled_016' ) ) {
			$tasks[] = [
				'status'      => 'pending',
				'total'       => 1,
				'order'       => 95,
				'title'       => 'Migrate Scheduled API Tasks',
				'slug'        => 'migrate_previously_scheduled_api_tasks',
				'description' => 'Migrate Previously Scheduled API Tasks to a new format',
				'subtasks'    => [ 1 ],
			];
		}

		return $tasks;
	}

	/*
	|--------------------------------------------------------------------
	| Tasks from Toolbox >> Updater
	|--------------------------------------------------------------------
	*/

	/**
	 * Run task to migrate API league actions
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function run_migrate_api_league_actions() {

		global $wpdb;

		$options = [];

		$rows = $wpdb->get_results(
			"
				SELECT local_value, external_value, extra_data
				FROM {$wpdb->prefix}anwpfl_import_mapping
				WHERE       provider = 'api-football'
				            AND type = 'config-saved-v3'
				            AND status = 'true'
			"
		) ?: [];

		// Load saved options
		$saved      = get_option( 'anwpfl_api_import_api-football_dashboard' ) ?: [];
		$saved_live = array_map( 'absint', $wpdb->get_col( "SELECT local_value FROM $wpdb->anwpfl_import_mapping WHERE `type` = 'config-live-v3' AND status = 'true' " ) ?: [] );

		// Prepare options
		foreach ( $rows as $row ) {
			$extra_data = json_decode( $row->extra_data, true ) ?: [];

			$options[ $row->local_value ] = [
				'local_id'   => $row->local_value,
				'api_id'     => $row->external_value,
				'api_season' => $extra_data['season'] ?? '',
				'finished'   => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['a_finished'] ),
				'lineups'    => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['a_lineups'] ),
				'kickoff'    => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['a_kickoff'] ),
				'prediction' => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['a_prediction'] ),
				'odds'       => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['a_odds'] ),
				'injuries'   => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['a_injuries'] ),
				'transfers'  => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['a_transfers'] ),
				'live'       => in_array( absint( $row->local_value ), $saved_live, true ),
				'players'    => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['a_players'] ),
			];
		}

		update_option( 'anwp_fl_api_league_actions', $options, true );

		update_option( 'anwpfl_api_import_api-football_dashboard__OLD', $saved, false );
		delete_option( 'anwpfl_api_import_api-football_dashboard' );

		return rest_ensure_response( [] );
	}

	/**
	 * Run task to drop table "anwpfl_player_stats"
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function run_drop_table_anwpfl_player_stats() {
		global $wpdb;

		if ( $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}anwpfl_player_stats`" ) > 0 ) {
			return new WP_Error( 'anwp_rest_error', 'Table is not empty!', [ 'status' => 400 ] );
		}

		try {
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}anwpfl_player_stats';" ) ) {
				$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'anwpfl_player_stats' );
			}
		} catch ( Exception $exception ) {
			error_log( $exception ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		return rest_ensure_response( [] );
	}

	/**
	 * Run task to create statistical column in players table.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function run_sync_table_anwpfl_player( WP_REST_Request $request ) {
		$subtasks = array_map( 'absint', $request->get_param( 'subtasks' ) );

		foreach ( $subtasks as $subtask ) {
			anwp_fl_pro()->stats->create_stat_column_in_players_table( $subtask );
		}

		return rest_ensure_response( [] );
	}

	/**
	 * Run task to migrate "player_stats" data to "players" table.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function run_migrate_table_anwpfl_player_stats( WP_REST_Request $request ) {
		global $wpdb;

		$game_ids = array_map( 'absint', $request->get_param( 'subtasks' ) );

		if ( empty( $game_ids ) ) {
			return rest_ensure_response( [] );
		}

		foreach ( $game_ids as $game_id ) {
			$items = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT *
				FROM {$wpdb->prefix}anwpfl_player_stats
				WHERE match_id = %d
				",
					$game_id
				)
			);

			$club_player__grouped = [];

			foreach ( $items as $item ) {
				$slug = $item->club_id . '-' . $item->player_id;

				if ( empty( $club_player__grouped[ $slug ] ) ) {
					$club_player__grouped[ $slug ] = [];
				}

				$club_player__grouped[ $slug ][ $item->stats_id ] = $item->value;
			}

			foreach ( $club_player__grouped as $group_slug => $group_data ) {
				$slugs = explode( '-', $group_slug );

				if ( ! anwp_fl_pro()->stats->update_player_advanced_game_stats( $game_id, $slugs[1], $slugs[0], $group_data ) ) {
					return new WP_Error( 'anwp_rest_error', 'Error updating data', [ 'status' => 400 ] );
				}
			}

			$wpdb->delete(
				$wpdb->prefix . 'anwpfl_player_stats',
				[ 'match_id' => $game_id ]
			);
		}

		return rest_ensure_response( [] );
	}

	/**
	 * Run task to migrate predictions meta to "predictions" table.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function run_migrate_prediction_from_meta( WP_REST_Request $request ) {
		global $wpdb;

		$game_ids = array_map( 'absint', $request->get_param( 'subtasks' ) );

		if ( empty( $game_ids ) ) {
			return rest_ensure_response( [] );
		}

		foreach ( $game_ids as $game_id ) {
			$game_obj = get_post( $game_id );

			if ( empty( $game_obj->ID ) || $game_obj->ID !== $game_id ) {
				return rest_ensure_response( [] );
			}

			/*
			|--------------------------------------------------------------------
			| Insert Data
			|--------------------------------------------------------------------
			*/
			$insert_data = [
				'match_id'              => $game_id,
				'prediction_advice'     => get_post_meta( $game_id, '_anwpfl_prediction_advice', true ),
				'prediction_percent'    => wp_json_encode( get_post_meta( $game_id, '_anwpfl_prediction_percent', true ) ),
				'prediction_comparison' => wp_json_encode( get_post_meta( $game_id, '_anwpfl_prediction_comparison', true ) ),
				'prediction_advice_alt' => get_post_meta( $game_id, '_anwpfl_prediction_advice_alt', true ),
			];

			if ( ! $wpdb->insert( $wpdb->prefix . 'anwpfl_predictions', $insert_data ) ) {
				return new WP_Error( 'anwp_rest_error', 'Insert Data Error', [ 'status' => 400 ] );
			}

			delete_post_meta( $game_id, '_anwpfl_prediction_advice' );
			delete_post_meta( $game_id, '_anwpfl_prediction_percent' );
			delete_post_meta( $game_id, '_anwpfl_prediction_comparison' );
			delete_post_meta( $game_id, '_anwpfl_prediction_advice_alt' );
		}

		return rest_ensure_response( [] );
	}

	/**
	 * Run task to migrate formations meta to "formations" table.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function run_migrate_formations_from_meta( WP_REST_Request $request ) {
		global $wpdb;

		$game_ids = array_map( 'absint', $request->get_param( 'subtasks' ) );

		if ( empty( $game_ids ) ) {
			return rest_ensure_response( [] );
		}

		foreach ( $game_ids as $game_id ) {
			$game_obj = get_post( $game_id );

			if ( empty( $game_obj->ID ) || $game_obj->ID !== $game_id ) {
				return rest_ensure_response( [] );
			}

			/*
			|--------------------------------------------------------------------
			| Insert Data
			|--------------------------------------------------------------------
			*/
			$insert_data = [
				'match_id'        => $game_id,
				'home_club_shirt' => get_post_meta( $game_id, '_anwpfl_home_club_shirt', true ),
				'away_club_shirt' => get_post_meta( $game_id, '_anwpfl_away_club_shirt', true ),
				'formation'       => get_post_meta( $game_id, '_anwpfl_match_formation', true ),
				'formation_extra' => get_post_meta( $game_id, '_anwpfl_match_formation_extra', true ),
			];

			if ( ! $wpdb->insert( $wpdb->prefix . 'anwpfl_formations', $insert_data ) ) {
				return new WP_Error( 'anwp_rest_error', 'Insert Data Error', [ 'status' => 400 ] );
			}

			delete_post_meta( $game_id, '_anwpfl_match_formation' );
			delete_post_meta( $game_id, '_anwpfl_home_club_shirt' );
			delete_post_meta( $game_id, '_anwpfl_away_club_shirt' );
			delete_post_meta( $game_id, '_anwpfl_match_formation_extra' );
		}

		return rest_ensure_response( [] );
	}

	/**
	 * Migrate Previously Scheduled API Tasks to a new format
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function run_migrate_previously_scheduled_api_tasks( WP_REST_Request $request ) {
		$old_scheduled_tasks = get_option( 'anwpfl_premium_previously_scheduled_016' );

		if ( empty( $old_scheduled_tasks ) ) {
			return rest_ensure_response( [] );
		}

		/*
		|--------------------------------------------------------------------
		| Update Data
		|--------------------------------------------------------------------
		*/
		$tasks_map = [
			'anwp_fl_api_import_finished'          => [ 'finished', 'hourly' ],
			'anwp_fl_api_import_kickoff'           => [ 'kickoff', 'daily' ],
			'anwp_fl_api_import_predictions'       => [ 'predictions', 'daily' ],
			'anwp_fl_api_import_odds'              => [ 'odds', 'anwp-fl-every-4-hours' ],
			'anwp_fl_api_import_injuries'          => [ 'injuries', 'daily' ],
			'anwp_fl_live_prepare_api_import_hook' => [ 'live', 'hourly' ],
			'anwp_fl_api_import_prepare_lineups'   => [ 'lineups', 'daily' ],
		];

		foreach ( $old_scheduled_tasks as $old_scheduled_task ) {
			if ( $tasks_map[ $old_scheduled_task ] ?? '' ) {
				$schedules_task_slug = 'anwp_fl_api_scheduled_' . $tasks_map[ $old_scheduled_task ][0];

				wp_schedule_event( time(), $tasks_map[ $old_scheduled_task ][1], $schedules_task_slug );
			}
		}

		delete_option( 'anwpfl_premium_previously_scheduled_016' );

		return rest_ensure_response( [] );
	}

	/**
	 * Run task to migrate transfers meta to "transfers" table.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function run_migrate_transfers_from_meta( WP_REST_Request $request ) {
		global $wpdb;

		$transfer_ids = array_map( 'absint', $request->get_param( 'subtasks' ) );

		if ( empty( $transfer_ids ) ) {
			return rest_ensure_response( [] );
		}

		foreach ( $transfer_ids as $transfer_id ) {
			$transfer_obj = get_post( $transfer_id );

			if ( empty( $transfer_obj->ID ) || $transfer_obj->ID !== $transfer_id ) {
				return rest_ensure_response( [] );
			}

			$transfer_status = 'rumour' === get_post_meta( $transfer_id, '_anwpfl_status', true ) ? 0 : 1;
			$transfer_window = 0;

			if ( 'mid' === get_post_meta( $transfer_id, '_anwpfl_window', true ) ) {
				$transfer_window = 2;
			} elseif ( 'pre' === get_post_meta( $transfer_id, '_anwpfl_window', true ) ) {
				$transfer_window = 1;
			}

			$club_in     = 0;
			$old_club_in = get_post_meta( $transfer_id, '_anwpfl_club_in', true );

			if ( 'unknown' === $old_club_in ) {
				$club_in = 1;
			} elseif ( 'custom_club' === $old_club_in ) {
				$club_in = 2;
			} elseif ( absint( $old_club_in ) ) {
				$club_in = absint( $old_club_in );
			}

			$club_out     = 0;
			$old_club_out = get_post_meta( $transfer_id, '_anwpfl_club_out', true );

			if ( 'unknown' === $old_club_out ) {
				$club_out = 1;
			} elseif ( 'custom_club' === $old_club_out ) {
				$club_out = 2;
			} elseif ( absint( $old_club_out ) ) {
				$club_out = absint( $old_club_out );
			}

			/*
			|--------------------------------------------------------------------
			| Insert Data
			|--------------------------------------------------------------------
			*/
			$insert_data = [
				'transfer_id'            => $transfer_id,
				'player_id'              => get_post_meta( $transfer_id, '_anwpfl_player_id', true ),
				'season_id'              => get_post_meta( $transfer_id, '_anwpfl_season', true ),
				'club_in'                => $club_in,
				'club_out'               => $club_out,
				'club_in_text'           => get_post_meta( $transfer_id, '_anwpfl_custom_club_in_text', true ),
				'club_out_text'          => get_post_meta( $transfer_id, '_anwpfl_custom_club_out_text', true ),
				'fee'                    => get_post_meta( $transfer_id, '_anwpfl_fee', true ),
				'transfer_date'          => empty( get_post_meta( $transfer_id, '_anwpfl_transfer_date', true ) ) ? '0000-00-00' : get_post_meta( $transfer_id, '_anwpfl_transfer_date', true ),
				'transfer_end_date'      => empty( get_post_meta( $transfer_id, '_anwpfl_transfer_end_date', true ) ) ? '0000-00-00' : get_post_meta( $transfer_id, '_anwpfl_transfer_end_date', true ),
				'transfer_status'        => $transfer_status,
				'transfer_window'        => $transfer_window,
				'api_transfer_hash'      => get_post_meta( $transfer_id, '_anwpfl_api_transfer_hash', true ),
				'api_transfer_hash_full' => get_post_meta( $transfer_id, '_anwpfl_api_transfer_hash_full', true ),
			];

			if ( ! $wpdb->insert( $wpdb->prefix . 'anwpfl_transfers', $insert_data ) ) {
				return new WP_Error( 'anwp_rest_error', 'Insert Data Error', [ 'status' => 400 ] );
			}

			$wpdb->update( $wpdb->posts, [ 'comment_count' => 0 ], [ 'ID' => $transfer_id ] );

			delete_post_meta( $transfer_id, '_anwpfl_player_id' );
			delete_post_meta( $transfer_id, '_anwpfl_season' );
			delete_post_meta( $transfer_id, '_anwpfl_transfer_date' );
			delete_post_meta( $transfer_id, '_anwpfl_transfer_end_date' );
			delete_post_meta( $transfer_id, '_anwpfl_api_transfer_hash' );
			delete_post_meta( $transfer_id, '_anwpfl_api_transfer_hash_full' );
			delete_post_meta( $transfer_id, '_anwpfl_custom_club_in_text' );
			delete_post_meta( $transfer_id, '_anwpfl_custom_club_out_text' );
			delete_post_meta( $transfer_id, '_anwpfl_fee' );
			delete_post_meta( $transfer_id, '_anwpfl_status' );
			delete_post_meta( $transfer_id, '_anwpfl_window' );
			delete_post_meta( $transfer_id, '_anwpfl_club_in' );
			delete_post_meta( $transfer_id, '_anwpfl_club_out' );
		}

		return rest_ensure_response( [] );
	}
}
