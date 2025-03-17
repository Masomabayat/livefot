<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AnWPFL_Premium_API_API_Football_Schedule {

	/**
	 * API
	 *
	 * @var AnWPFL_Premium_API_API_Football
	 */
	protected $api;

	/**
	 * Constructor.
	 *
	 * @param AnWPFL_Premium_API_API_Football $api
	 */
	public function __construct( AnWPFL_Premium_API_API_Football $api ) {
		$this->api = $api;
	}

	/**
	 * @param string $action_slug
	 *
	 * @return array
	 */
	public function get_action_competitions( string $action_slug ): array {
		$all_competitions = get_option( 'anwp_fl_api_league_actions' ) ?: [];

		return wp_list_filter( $all_competitions, [ $action_slug => true ] ) ?: [];
	}

	/**
	 * Scheduled Odds Update job works here.
	 *
	 * @since 0.14.3
	 */
	public function run_scheduled_odds() {

		$competitions = $this->get_action_competitions( 'odds' );

		if ( empty( $competitions ) ) {
			return;
		}

		AnWP_Football_Leagues_Premium::set_time_limit( 300 );

		foreach ( $competitions as $competition ) {

			// Check Competition ID
			$competition_id = $competition['local_id'] ?? 0;

			if ( ! $competition_id ) {
				continue;
			}

			$this->api->updater->api_update_odds(
				[
					'competition_id'     => $competition_id,
					'competition_api_id' => $competition['api_id'],
					'season_api'         => $competition['api_season'],
				]
			);
		}
	}

	/**
	 * Scheduled Odds Update job works here.
	 *
	 * @since 0.14.3
	 */
	public function run_scheduled_injuries() {

		$competitions = $this->get_action_competitions( 'injuries' );

		if ( empty( $competitions ) ) {
			return;
		}

		AnWP_Football_Leagues_Premium::set_time_limit( 300 );

		foreach ( $competitions as $competition ) {

			// Check Competition ID
			$competition_id = $competition['local_id'] ?? 0;

			if ( ! $competition_id ) {
				continue;
			}

			$this->api->updater->api_update_injuries(
				[
					'competition_id'     => $competition_id,
					'competition_api_id' => $competition['api_id'],
					'season_api'         => $competition['api_season'],
				]
			);
		}
	}

	/**
	 * Prepare Scheduled Lineups import.
	 * This task is scheduled daily.
	 *
	 * @throws Exception
	 * @return void
	 * @since 0.16.0
	 */
	public function run_scheduled_lineups() {

		global $wpdb;
		AnWP_Football_Leagues_Premium::set_time_limit( 300 );

		$competitions = $this->get_action_competitions( 'lineups' );

		if ( empty( $competitions ) ) {
			return;
		}

		$scheduled_import = wp_next_scheduled( 'anwp_fl_api_scheduled_import_lineups' );

		/*
		|--------------------------------------------------------------------
		| Get All games to schedule - not finished games in -2 and +25 hours
		|--------------------------------------------------------------------
		*/
		$current_date = AnWPFL_Premium_Helper::get_current_datetime();

		// Get Games
		$query = "SELECT * FROM $wpdb->anwpfl_matches WHERE finished = 0 ";

		// Get competition to filter
		$local_league_ids = wp_list_pluck( $competitions, 'local_id' );

		$format = implode( ', ', array_fill( 0, count( array_values( $local_league_ids ) ), '%d' ) );
		$query  .= $wpdb->prepare( " AND ( competition_id IN ({$format}) OR main_stage_id IN ({$format}) ) ", array_merge( array_values( $local_league_ids ), array_values( $local_league_ids ) ) ); // phpcs:ignore

		// Filter by date
		$query .= $wpdb->prepare( ' AND kickoff > %s ', $current_date->sub( new DateInterval( 'PT2H' ) )->format( 'Y-m-d H:i:s' ) );
		$query .= $wpdb->prepare( ' AND kickoff < %s ', $current_date->add( new DateInterval( 'PT25H' ) )->format( 'Y-m-d H:i:s' ) );
		$query .= ' ORDER BY kickoff ASC';

		$games_to_schedule = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $games_to_schedule ) ) {
			if ( $scheduled_import ) {
				wp_unschedule_hook( 'anwp_fl_api_scheduled_import_lineups' );
			}

			return;
		}

		/*
		|--------------------------------------------------------------------
		| Get ALL already scheduled games
		|--------------------------------------------------------------------
		*/
		$scheduled_ids = wp_parse_id_list(
			$wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT local_value
					FROM $wpdb->anwpfl_import_mapping
					WHERE       provider = %s
					            AND type = 'match-lineups'
				",
					$this->api->provider
				)
			) ?: []
		);

		// Schedule if not scheduled
		foreach ( $games_to_schedule as $game ) {

			if ( in_array( absint( $game->match_id ), $scheduled_ids, true ) ) {
				continue;
			}

			$match_api_id = $wpdb->get_var(
				$wpdb->prepare(
					"
							SELECT external_value
							FROM {$wpdb->prefix}anwpfl_import_mapping
							WHERE       provider = %s
							            AND type = 'match'
							            AND local_value = %d
						",
					$this->api->provider,
					$game->match_id
				)
			);

			if ( ! $match_api_id ) {
				continue;
			}

			$game_data_to_insert = [
				'provider'       => $this->api->provider,
				'type'           => 'match-lineups',
				'local_value'    => $game->match_id,
				'external_value' => $match_api_id,
				'status'         => $game->kickoff,
				'extra_data'     => $this->api->helper->get_mapped_club_id( $game->home_club, false ),
			];

			$wpdb->replace( $wpdb->anwpfl_import_mapping, $game_data_to_insert );
		}

		/*
		|--------------------------------------------------------------------
		| Remove missing previously scheduled games
		|--------------------------------------------------------------------
		*/
		$games_to_schedule_ids = wp_parse_id_list( wp_list_pluck( $games_to_schedule, 'match_id' ) );

		foreach ( array_diff( $scheduled_ids, $games_to_schedule_ids ) as $missed_game ) {
			$wpdb->delete(
				$wpdb->prefix . 'anwpfl_import_mapping',
				[
					'provider'    => $this->api->provider,
					'type'        => 'match-lineups',
					'local_value' => $missed_game,
				]
			);
		}

		/*
		|--------------------------------------------------------------------
		| Schedule main task
		|--------------------------------------------------------------------
		*/
		if ( isset( $games_to_schedule[0]->kickoff ) ) {
			wp_unschedule_hook( 'anwp_fl_api_scheduled_import_lineups' );

			// Get closest game time and schedule import
			$closest_time = new DateTimeImmutable( $games_to_schedule[0]->kickoff, wp_timezone() );

			$diff_minutes = ( $closest_time->getTimestamp() - $current_date->getTimestamp() ) / 60;

			$diff_minutes = $diff_minutes <= 40 ? 0 : (int) $diff_minutes - 40;

			wp_schedule_event( time() + $diff_minutes * 60, 'anwp-fl-every-10-minutes', 'anwp_fl_api_scheduled_import_lineups' );
		}

		/*
		|--------------------------------------------------------------------
		| Remove Finished tasks
		|--------------------------------------------------------------------
		*/
		$games_finished_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT a.local_value
					FROM $wpdb->anwpfl_import_mapping a
					LEFT JOIN $wpdb->anwpfl_matches AS b ON b.match_id = a.local_value
					WHERE       a.provider = %s
					            AND a.`type` = 'match-lineups'
					            AND b.`finished` = 1
					",
				$this->api->provider
			),
			OBJECT_K
		);

		foreach ( $games_finished_ids as $games_finished_id ) {
			$wpdb->delete(
				$wpdb->anwpfl_import_mapping,
				[
					'provider'    => $this->api->provider,
					'type'        => 'match-lineups',
					'local_value' => $games_finished_id,
				]
			);
		}
	}

	/**
	 * Scheduled job works here.
	 *
	 * @return void
	 * @throws Exception
	 * @since 0.13.4
	 */
	public function run_scheduled_import_lineups() {

		// Remove scheduled task if root task is not active
		if ( ! wp_next_scheduled( 'anwp_fl_api_scheduled_lineups' ) ) {
			wp_unschedule_hook( 'anwp_fl_api_scheduled_import_lineups' );

			return;
		}

		global $wpdb;

		// Get closest scheduled games (-10/+40 min)
		$current_date = AnWPFL_Premium_Helper::get_current_datetime();

		$games_scheduled = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT `external_value`, `local_value`, `status`, `extra_data`
					FROM $wpdb->anwpfl_import_mapping
					WHERE       provider = %s
					            AND `type` = 'match-lineups'
					            AND `status` != 'yes'
					            AND `status` != ''
					            AND `status` >= %s
					            AND `status` <= %s
					",
				$this->api->provider,
				$current_date->sub( new DateInterval( 'PT20M' ) )->format( 'Y-m-d H:i:s' ),
				$current_date->add( new DateInterval( 'PT40M' ) )->format( 'Y-m-d H:i:s' )
			)
		);

		/*
		|--------------------------------------------------------------------
		| Import lineups
		|--------------------------------------------------------------------
		*/
		$this->api->init_players_map();

		foreach ( $games_scheduled as $game_scheduled ) {

			$game_id     = absint( $game_scheduled->local_value );
			$game_api_id = absint( $game_scheduled->external_value );
			$team_api_id = absint( $game_scheduled->extra_data );

			$response = $this->api->send_request_to_api( 'fixtures/lineups', 0, [ 'fixture' => $game_api_id ] );

			if ( ! empty( $response['errors'] ) || empty( $response['response'] ) || ! is_array( $response['response'] ) ) {
				continue;
			}

			$lineups_data = $this->api->parser->parse_match_lineups_and_subs(
				[
					'lineups' => $response['response'],
					'teams'   => [
						'home' => [
							'id' => $team_api_id,
						],
					],
				]
			);

			if ( ! empty( $lineups_data['home_line_up'] ) || ! empty( $lineups_data['away_line_up'] ) ) {
				$wpdb->replace( $wpdb->anwpfl_lineups, array_merge( $lineups_data, [ 'match_id' => $game_id ] ) );

				if ( 'yes' === $this->api->config['lineups_formation'] ) {
					$this->api->parser->parse_lineups_formation( $game_api_id, $game_id, $response['response'], 0 );
				}

				anwp_fl_pro()->cache->maybe_flush_cache( 'game', 'run_scheduled_lineups', $game_id );

				if ( ! empty( $lineups_data['home_line_up'] ) && ! empty( $lineups_data['away_line_up'] ) ) {
					$wpdb->replace(
						$wpdb->prefix . 'anwpfl_import_mapping',
						[
							'provider'       => $this->api->provider,
							'type'           => 'match-lineups',
							'local_value'    => $game_id,
							'external_value' => $game_api_id,
							'status'         => 'yes',
							'extra_data'     => '',
						]
					);
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Get new scheduled time
		|--------------------------------------------------------------------
		*/
		$games_scheduled_all = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT `external_value`, `local_value`, `status`, `extra_data`
					FROM {$wpdb->prefix}anwpfl_import_mapping
					WHERE       provider = %s
					            AND `type` = 'match-lineups'
					            AND `status` != 'yes'
					            AND `status` != ''
					            AND `status` >= %s
					ORDER BY `status` ASC
					",
				$this->api->provider,
				$current_date->sub( new DateInterval( 'PT20M' ) )->format( 'Y-m-d H:i:s' )
			)
		);

		if ( empty( $games_scheduled_all ) ) {
			wp_unschedule_event( wp_next_scheduled( 'anwp_fl_api_scheduled_import_lineups' ), 'anwp_fl_api_scheduled_import_lineups' );

			return;
		}

		if ( ! empty( $games_scheduled_all[0]->status ) ) {

			// Get closest game time and schedule import
			$closest_time = new DateTimeImmutable( $games_scheduled_all[0]->status, wp_timezone() );
			$diff_minutes = ( $closest_time->getTimestamp() - $current_date->getTimestamp() ) / 60;

			if ( $diff_minutes > 50 ) {
				wp_unschedule_event( wp_next_scheduled( 'anwp_fl_api_scheduled_import_lineups' ), 'anwp_fl_api_scheduled_import_lineups' );
				wp_schedule_event( time() + ( absint( $diff_minutes ) - 40 ) * 60, 'anwp-fl-every-10-minutes', 'anwp_fl_api_scheduled_import_lineups' );
			}
		}
	}

	/**
	 * Scheduled Finished job works here.
	 *
	 * @since 0.11.0
	 */
	public function run_scheduled_finished() {

		global $wpdb;
		AnWP_Football_Leagues_Premium::set_time_limit( 300 );

		$competitions = $this->get_action_competitions( 'finished' );

		if ( empty( $competitions ) ) {
			return;
		}

		$this->api->init_players_map();

		foreach ( $competitions as $competition ) {

			$competition_id = $competition['local_id'] ?? 0;

			if ( ! $competition_id ) {
				continue;
			}

			// Check secondary (support old V2 tasks)
			if ( 'secondary' === anwp_fl()->competition->get_competition( $competition_id )->multistage ) {
				$competition_id = anwp_fl()->competition->get_competition( $competition_id )->multistage_main;

				if ( ! $competition_id ) {
					continue;
				}
			}

			/*
			|--------------------------------------------------------------------------
			| Step 1 :: get all finished matches from API
			|--------------------------------------------------------------------------
			*/
			if ( absint( $competition['api_id'] ?? '' ) && absint( $competition['api_season'] ?? '' ) ) {
				$competition_api = [
					'competition_api_id' => absint( $competition['api_id'] ),
					'season_api'         => absint( $competition['api_season'] ),
				];
			} else {
				$competition_api = $this->api->helper->get_api_competition_by_local( $competition_id );
			}

			// Return error on empty competition
			if ( ! $competition_api['competition_api_id'] || ! $competition_api['season_api'] ) {
				continue;
			}

			// Get Fixtures from API
			$response = $this->api->send_request_to_api(
				'fixtures',
				180,
				[
					'league' => $competition_api['competition_api_id'],
					'season' => $competition_api['season_api'],
				]
			);

			if ( ! empty( $response['errors'] ) || empty( $response['response'] ) ) {
				continue;
			}

			$finished_api_ids = [];

			foreach ( $response['response'] as $match ) {
				$api_finished_status = [ 'FT', 'PEN', 'AET' ];

				if ( in_array( $match['fixture']['status']['short'] ?? '', $api_finished_status, true ) && absint( $match['fixture']['id'] ?? '' ) ) {
					$finished_api_ids[] = absint( $match['fixture']['id'] );
				}
			}

			/*
			|--------------------------------------------------------------------------
			| Step 2 :: get local fixtures
			|--------------------------------------------------------------------------
			*/
			$games_to_update_maybe = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT a.external_value, a.local_value
					FROM $wpdb->anwpfl_import_mapping a
					LEFT JOIN $wpdb->anwpfl_matches AS b ON b.match_id = a.local_value
					WHERE       a.provider = %s
					            AND a.type = 'match'
					            AND b.finished = 0
					            AND a.external_value IS NOT NULL
					            AND ( b.competition_id = %d OR b.main_stage_id = %d )
					",
					$this->api->provider,
					$competition_id,
					$competition_id
				)
			);

			// Prepare update array
			$games_to_update = [];

			foreach ( $games_to_update_maybe as $game ) {
				if ( in_array( absint( $game->external_value ), $finished_api_ids, true ) ) {
					$games_to_update[ absint( $game->external_value ) ] = absint( $game->local_value );
				}
			}

			/*
			|--------------------------------------------------------------------------
			| Step 3 :: Create missed matches
			|--------------------------------------------------------------------------
			*/
			if ( ! empty( $games_to_update ) ) {
				foreach ( $games_to_update as $game_api_id => $game_id ) {
					if ( $this->api->helper->update_match( $game_api_id, $game_id ) ) {
						$this->api->insert_mapped_link( 'match', $game_id, $game_api_id, 'result' );

						anwp_fl_pro()->cache->maybe_flush_cache( 'game', 'run_scheduled_finished', $game_id );
					}
				}

				anwp_fl_pro()->standing->calculate_competition_standings_by_games( $competition_id, $games_to_update );
				anwp_fl_pro()->competition->update_current_matchweek( $competition_id );

				anwp_fl_pro()->cache->maybe_flush_cache( 'competition', 'run_scheduled_finished', anwp_fl()->competition->get_main_competition_id( $competition_id ) );
			}
		}
	}

	/**
	 * Scheduled Kickoff Time Update job works here.
	 *
	 * @since 0.13.4
	 */
	public function run_scheduled_kickoff() {

		AnWP_Football_Leagues_Premium::set_time_limit( 300 );

		$competitions = $this->get_action_competitions( 'kickoff' );

		if ( empty( $competitions ) ) {
			return;
		}

		foreach ( $competitions as $competition ) {

			// Check Competition ID
			$competition_id = $competition['local_id'] ?? 0;

			if ( ! $competition_id ) {
				continue;
			}

			$league_obj = [
				'year'        => $competition['api_season'] ?? '',
				'id'          => $competition['api_id'] ?? '',
				'local_value' => absint( $competition_id ),
			];

			if ( ! absint( $league_obj['id'] ) || ! absint( $league_obj['year'] ) ) {
				continue;
			}

			$this->api->updater->api_update_kickoff( [ 'league' => $league_obj ] );
		}
	}

	/**
	 * Scheduled Predictions Update job works here.
	 *
	 * @since 0.13.4
	 */
	public function run_scheduled_predictions() {

		AnWP_Football_Leagues_Premium::set_time_limit( 300 );

		$competitions = $this->get_action_competitions( 'prediction' );

		if ( empty( $competitions ) ) {
			return;
		}

		foreach ( $competitions as $competition ) {

			$competition_id = $competition['local_id'] ?? 0;

			if ( ! $competition_id ) {
				continue;
			}

			$this->api->updater->api_update_predictions( [ 'competition_id' => $competition_id ] );
		}
	}

	/**
	 * Prepare Scheduled LIVE Games.
	 * This task is scheduled every hour. Or on setting LIVE mode for competition.
	 *
	 * @throws Exception
	 * @since 0.13.0
	 */
	public function run_scheduled_live() {

		global $wpdb;
		AnWP_Football_Leagues_Premium::set_time_limit( 300 );

		$scheduled_task    = wp_next_scheduled( 'anwp_fl_api_scheduled_live' );
		$scheduled_subtask = wp_next_scheduled( 'anwp_fl_api_scheduled_import_live' );

		/*
		|--------------------------------------------------------------------
		| Check if LIVE mode in Options is active
		|--------------------------------------------------------------------
		*/
		if ( 'yes' !== $this->api->config['live'] ) {
			if ( $scheduled_task ) {
				wp_unschedule_hook( 'anwp_fl_api_scheduled_live' );
			}

			if ( $scheduled_subtask ) {
				wp_unschedule_hook( 'anwp_fl_api_scheduled_import_live' );
			}

			return;
		}

		/*
		|--------------------------------------------------------------------
		| Get Competitions
		|--------------------------------------------------------------------
		*/
		$competitions = $this->get_action_competitions( 'live' );

		if ( empty( $competitions ) ) {
			if ( $scheduled_subtask ) {
				wp_unschedule_hook( 'anwp_fl_api_scheduled_import_live' );
			}

			return;
		}

		/*
		|--------------------------------------------------------------------
		| Check out closest scheduled games
		| Get minutes to the closest scheduled game (-3 -- +2) and get all upcoming games
		|--------------------------------------------------------------------
		*/
		$current_date = AnWPFL_Premium_Helper::get_current_datetime();

		// Get competition to filter
		$local_competition_ids = array_values( wp_list_pluck( $competitions, 'local_id' ) );

		// Get Games
		$query = "SELECT m.*, i.external_value as match_api_id FROM $wpdb->anwpfl_matches m LEFT JOIN $wpdb->anwpfl_import_mapping AS i ON m.match_id = i.local_value AND i.type = 'match' WHERE m.finished = 0 ";

		// Get competition to filter
		$format = implode( ', ', array_fill( 0, count( $local_competition_ids ), '%d' ) );
		$query  .= $wpdb->prepare( " AND ( m.competition_id IN ({$format}) OR m.main_stage_id IN ({$format}) ) ", array_merge( $local_competition_ids, $local_competition_ids ) ); // phpcs:ignore

		// Filter by date
		$query .= $wpdb->prepare( ' AND m.kickoff > %s ', $current_date->sub( new DateInterval( 'PT3H' ) )->format( 'Y-m-d H:i:s' ) );
		$query .= $wpdb->prepare( ' AND m.kickoff < %s ', $current_date->add( new DateInterval( 'PT2H' ) )->format( 'Y-m-d H:i:s' ) );
		$query .= ' ORDER BY m.kickoff ASC';

		$games_to_schedule = $wpdb->get_results( $query ); // phpcs:ignore

		if ( empty( $games_to_schedule ) ) {
			if ( $scheduled_subtask ) {
				wp_unschedule_hook( 'anwp_fl_api_scheduled_import_live' );
			}

			return;
		}

		$scheduled_ids_saved = wp_parse_id_list(
			$wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT local_value
					FROM $wpdb->anwpfl_import_mapping
					WHERE provider = %s AND type = 'match-live'
					",
					$this->api->provider
				)
			) ?: []
		);

		// Schedule if not scheduled
		foreach ( $games_to_schedule as $game ) {
			if ( in_array( absint( $game->match_id ), $scheduled_ids_saved, true ) ) {
				continue;
			}

			if ( ! $game->match_api_id ) {
				continue;
			}

			$competition_api_id = $this->api->helper->get_api_competition_by_local( absint( $game->main_stage_id ) ?: $game->competition_id )['competition_api_id'];

			if ( empty( $competition_api_id ) ) {
				continue;
			}

			$game_data_to_insert = [
				'provider'       => $this->api->provider,
				'type'           => 'match-live',
				'local_value'    => $game->match_id,
				'external_value' => $game->match_api_id,
				'status'         => $competition_api_id,
			];

			$wpdb->replace( $wpdb->anwpfl_import_mapping, $game_data_to_insert );
		}

		/*
		|--------------------------------------------------------------------
		| Remove scheduled status in Missing Games
		|--------------------------------------------------------------------
		*/
		$games_to_schedule_ids = wp_parse_id_list( wp_list_pluck( $games_to_schedule, 'match_id' ) );

		foreach ( array_diff( $scheduled_ids_saved, $games_to_schedule_ids ) as $missed_game ) {
			$wpdb->delete(
				$wpdb->anwpfl_import_mapping,
				[
					'provider'    => $this->api->provider,
					'type'        => 'match-live',
					'local_value' => $missed_game,
				]
			);
		}

		/*
		|--------------------------------------------------------------------
		| Schedule LIVE Import Hook
		|--------------------------------------------------------------------
		*/
		if ( ! $scheduled_subtask && isset( $games_to_schedule[0]->kickoff ) ) {
			$closest_time = new DateTimeImmutable( $games_to_schedule[0]->kickoff, wp_timezone() );
			$diff_minutes = ( $closest_time->getTimestamp() - $current_date->getTimestamp() ) / 60;
			$diff_minutes = $diff_minutes <= 20 ? 0 : (int) $diff_minutes - 20;

			wp_schedule_event( time() + $diff_minutes * 60, 'anwp-fl-every-minute', 'anwp_fl_api_scheduled_import_live' );
		}
	}

	/**
	 * Scheduled job works here.
	 *
	 * @since 0.13.0
	 */
	public function run_scheduled_import_live() {

		// Remove scheduled task if scheduled core task not active
		if ( ! wp_next_scheduled( 'anwp_fl_api_scheduled_live' ) || 'yes' !== $this->api->config['live'] ) {
			wp_unschedule_hook( 'anwp_fl_api_scheduled_import_live' );

			return;
		}

		global $wpdb;

		$scheduled = [
			'leagues' => [],
			'api_ids' => [],
			'active'  => [],
		];

		// Get scheduled matches
		$scheduled['games'] = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT external_value, local_value, status, extra_data
				FROM $wpdb->anwpfl_import_mapping
				WHERE provider = %s AND type = 'match-live'
				",
				$this->api->provider
			),
			OBJECT_K
		) ? : [];

		if ( empty( $scheduled['games'] ) ) {
			wp_unschedule_hook( 'anwp_fl_api_scheduled_import_live' );

			return;
		}

		foreach ( $scheduled['games'] as $schedule_game ) {
			$scheduled['leagues'][] = $schedule_game->status;

			if ( 'live-active' === $schedule_game->extra_data ) {
				$scheduled['active'][] = absint( $schedule_game->external_value );
			}
		}

		$scheduled['leagues'] = wp_parse_id_list( $scheduled['leagues'] );

		if ( 1 === count( $scheduled['leagues'] ) ) {
			$scheduled['leagues'][] = 0;
		}

		/*
		|--------------------------------------------------------------------
		| Get live matches
		|--------------------------------------------------------------------
		*/
		$response = $this->api->send_request_to_api( 'fixtures', 0, [ 'live' => implode( '-', $scheduled['leagues'] ) ] );

		if ( ! empty( $response['errors'] ) ) {
			return;
		}

		if ( empty( $response['response'] ) || ! is_array( $response['response'] ) ) {
			$response['response'] = [];
		}

		/*
		|--------------------------------------------------------------------
		| Handle Data
		|--------------------------------------------------------------------
		*/
		$live_data = [];

		foreach ( $response['response'] as $api_live_game ) {

			if ( in_array( absint( $api_live_game['fixture']['id'] ), array_keys( $scheduled['games'] ), true ) ) {
				if ( in_array( $api_live_game['fixture']['status']['short'], [ '1H', 'HT', '2H', 'ET', 'P', 'BT' ], true ) ) {
					$scheduled['api_ids'][] = absint( $api_live_game['fixture']['id'] );

					$live_data[ $scheduled['games'][ $api_live_game['fixture']['id'] ]->local_value ] = $api_live_game;
				}
			}
		}

		update_option( 'anwpfl_api_import_live_data', $live_data, true );

		/*
		|--------------------------------------------------------------------
		| Update Live Games Status
		|--------------------------------------------------------------------
		*/
		$save_active = array_diff( $scheduled['api_ids'], $scheduled['active'] );

		if ( ! empty( $save_active ) ) {
			foreach ( $save_active as $save_active_game ) {
				$wpdb->update(
					$wpdb->anwpfl_import_mapping,
					[
						'extra_data' => 'live-active',
					],
					[
						'external_value' => $save_active_game,
						'type'           => 'match-live',
					]
				);

				if ( ! empty( $scheduled['games'][ $save_active_game ]->local_value ) ) {
					anwp_fl_pro()->cache->maybe_flush_cache( 'game', 'run_scheduled_import_live', $scheduled['games'][ $save_active_game ]->local_value );
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Handle Finished Games
		|--------------------------------------------------------------------
		*/
		$finished_games = array_diff( $scheduled['active'], $scheduled['api_ids'] );

		if ( ! empty( $finished_games ) ) {
			foreach ( $finished_games as $finished_game ) {
				$api_game_data      = $this->api->get_fixture_from_api( $finished_game );
				$local_game_post_id = $scheduled['games'][ $finished_game ]->local_value;

				if ( ! is_wp_error( $api_game_data ) && isset( $api_game_data['fixture']['status']['short'] ) && in_array( $api_game_data['fixture']['status']['short'], [ 'FT', 'PEN', 'AET' ], true ) ) {
					$this->api->helper->update_match( $finished_game, $local_game_post_id, $api_game_data );
					anwp_fl_pro()->cache->maybe_flush_cache( 'game', 'live_api_import_finished', $local_game_post_id );

					$wpdb->delete(
						$wpdb->anwpfl_import_mapping,
						[
							'type'           => 'match-live',
							'local_value'    => $local_game_post_id,
							'external_value' => $finished_game,
						]
					);

					$this->api->insert_mapped_link( 'match', $local_game_post_id, $finished_game, 'result' );
					$game_data = anwp_fl()->match->get_game_data( $local_game_post_id );

					anwp_fl()->standing->calculate_standing_prepare( $local_game_post_id, $game_data['competition_id'], $game_data['group_id'] );
					anwp_fl_pro()->competition->update_current_matchweek( $game_data['competition_id'], $local_game_post_id );
					anwp_fl_pro()->cache->maybe_flush_cache( 'competition', 'live_api_import_finished', $game_data['main_stage_id'] ?: $game_data['competition_id'] );
				}
			}
		}
	}
}
