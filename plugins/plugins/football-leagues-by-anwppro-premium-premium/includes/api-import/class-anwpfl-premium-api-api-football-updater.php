<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AnWPFL_Premium_API_API_Football_Updater {

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
	 * Get list of all available API Setup methods
	 *
	 * @return string[]
	 */
	public function get_available_api_update_methods(): array {
		return [
			'api_update_matches',
			'api_update_dashboard_config',
			'api_get_wizard_totals',
			'api_update_wizard_loop',
			'api_update_dashboard_status',
			'api_update_league_teams',
			'api_update_competition_logo',
			'api_create_new_matches',
			'api_update_kickoff',
			'api_get_competition_groups',
			'api_reupdate_matches',
			'api_update_competition_structure',
			'api_update_injuries',
			'api_save_scheduled_task',
			'api_update_odds',
			'api_update_squad',
			'api_update_predictions',
			'api_update_transfers',
		];
	}

	/**
	 * Update missed matches from API.
	 *
	 * @param $params
	 *
	 * @return array|WP_Error
	 * @since 0.13.0
	 */
	public function api_update_matches( $params ) {

		global $wpdb;

		$import = [
			'result'  => false,
			'message' => '',
			'qty'     => 0,
		];

		$competition_id = absint( $params['competition_id'] ?? 0 );

		if ( ! $competition_id ) {
			return new WP_Error( 'anwp_rest_error', 'Invalid Competition ID', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------
		| Check Progress Status
		|--------------------------------------------------------------------
		*/
		$progress_status = sanitize_key( $params['progress_status'] ?? '' );

		if ( ! $progress_status ) {
			return new WP_Error( 'anwp_rest_error', 'Progress status not set', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------------
		| Step 1 :: get all finished matches from API
		|--------------------------------------------------------------------------
		*/
		$competition_api = $this->api->helper->get_api_competition_by_local( $competition_id );

		// Return error on empty competition
		if ( ! $competition_api['competition_api_id'] || ! $competition_api['season_api'] ) {
			return new WP_Error( 'anwp_rest_error', 'Invalid API Competition ID or Season', [ 'status' => 400 ] );
		}

		// Get Fixtures from API
		$response = $this->api->send_request_to_api(
			'fixtures',
			300,
			[
				'league' => $competition_api['competition_api_id'],
				'season' => $competition_api['season_api'],
			]
		);

		if ( ! empty( $response['errors'] ) ) {
			return new WP_Error( 'anwp_rest_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
		}

		if ( empty( $response['response'] ) ) {
			return new WP_Error( 'anwp_rest_error', 'Empty API Data - Fixtures', [ 'status' => 400 ] );
		}

		$api_data = $response['response'];

		$api_finished_matches_ids = [];

		foreach ( $api_data as $match ) {
			$api_finished_status = [ 'FT', 'PEN', 'AET' ];

			if ( isset( $match['fixture']['status']['short'] ) && in_array( $match['fixture']['status']['short'], $api_finished_status, true ) && absint( $match['fixture']['id'] ) ) {
				$api_finished_matches_ids[] = absint( $match['fixture']['id'] );
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Step 2 :: get local fixtures
		|--------------------------------------------------------------------------
		*/
		// Get Local Matches to Update
		$matches_not_result = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT a.external_value, a.local_value
					FROM {$wpdb->prefix}anwpfl_import_mapping a
					LEFT JOIN {$wpdb->prefix}anwpfl_matches AS b ON b.match_id = a.local_value
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
		$matches_to_update = [];

		foreach ( $matches_not_result as $match ) {
			if ( in_array( absint( $match->external_value ), $api_finished_matches_ids, true ) ) {
				$matches_to_update[ absint( $match->external_value ) ] = absint( $match->local_value );
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Step 3 :: Create missed matches
		|--------------------------------------------------------------------------
		*/
		$step_limit = 3;

		if ( 'loop' === $progress_status && ! empty( $matches_to_update ) ) {
			foreach ( $matches_to_update as $match_external => $match_local ) {
				if ( -- $step_limit < 0 ) {
					break;
				}

				if ( $this->api->helper->update_match( $match_external, $match_local ) ) {
					$this->api->insert_mapped_link( 'match', $match_local, $match_external, 'result' );
					$import['qty'] ++;

					// ToDO option to update cache
					anwp_fl_pro()->cache->maybe_flush_cache( 'game', 'api_update_matches', $match_local );
				}
			}
		}

		if ( empty( $matches_to_update ) && 'total' === $progress_status ) {
			return new WP_Error( 'anwp_rest_warning', 'No Games to Update', [ 'status' => 400 ] );
		}

		$import['result'] = true;

		/*
		|--------------------------------------------------------------------
		| Prepare progress data
		|--------------------------------------------------------------------
		*/
		if ( 'total' === $progress_status ) {
			$import['progress_total']   = count( $matches_to_update );
			$import['progress_message'] = 'Updating games - 0/' . count( $matches_to_update ) . ' ...';
		} elseif ( 'loop' === $progress_status ) {
			$progress_total = $params['progress_total'] ?? 0;
			$progress_sum   = $params['progress_sum'] ?? 0;

			$import['progress_total']   = $progress_total;
			$import['progress_sum']     = $progress_sum + $import['qty'];
			$import['progress_message'] = 'Updating games - ' . $import['progress_sum'] . '/' . $progress_total . ' ...';
		}

		/*
		|--------------------------------------------------------------------------
		| Step 4 :: recalculate associated standing
		|--------------------------------------------------------------------------
		*/
		if ( 'loop' === $progress_status && ( $import['progress_sum'] + $step_limit ) >= $import['progress_total'] ) {
			anwp_fl_pro()->standing->calculate_competition_standings_by_games( $competition_id, $matches_to_update );
			anwp_fl_pro()->competition->update_current_matchweek( $competition_id );

			// ToDO option to update cache
			anwp_fl_pro()->cache->maybe_flush_cache( 'competition', 'api_update_matches', anwp_fl()->competition->get_main_competition_id( $competition_id ) );
		}

		return $import;
	}

	/**
	 * Save dashboard config.
	 *
	 * @param $params
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since 0.13.0
	 */
	public function api_update_dashboard_config( $params ) {

		if ( empty( $params['competitions'] ) || ! is_array( $params['competitions'] ) ) {
			return new WP_Error( 'rest_anwp_fl_error', esc_html__( 'Data Error.', 'anwp-football-leagues-premium' ), [ 'status' => 400 ] );
		}

		$data = [];

		foreach ( $params['competitions'] as $competition ) {

			if ( ! absint( $competition['local_id'] ) ) {
				continue;
			}

			$data[ absint( $competition['local_id'] ) ] = [
				'local_id'   => absint( $competition['local_id'] ),
				'api_id'     => absint( $competition['api_id'] ),
				'api_season' => absint( $competition['api_season'] ),
				'finished'   => AnWP_Football_Leagues::string_to_bool( $competition['finished'] ?? '' ),
				'kickoff'    => AnWP_Football_Leagues::string_to_bool( $competition['kickoff'] ?? '' ),
				'prediction' => AnWP_Football_Leagues::string_to_bool( $competition['prediction'] ?? '' ),
				'odds'       => AnWP_Football_Leagues::string_to_bool( $competition['odds'] ?? '' ),
				'injuries'   => AnWP_Football_Leagues::string_to_bool( $competition['injuries'] ?? '' ),
				'transfers'  => AnWP_Football_Leagues::string_to_bool( $competition['transfers'] ?? '' ),
				'lineups'    => AnWP_Football_Leagues::string_to_bool( $competition['lineups'] ?? '' ),
				'players'    => AnWP_Football_Leagues::string_to_bool( $competition['players'] ?? '' ),
				'live'       => AnWP_Football_Leagues::string_to_bool( $competition['live'] ?? '' ),
			];
		}

		update_option( 'anwp_fl_api_league_actions', $data, true );

		return rest_ensure_response(
			[
				'result' => true,
			]
		);
	}

	/**
	 * Get Wizard Totals
	 *
	 * @param $params
	 *
	 * @return mixed
	 * @since 0.14.6
	 */
	public function api_get_wizard_totals( $params ) {
		return $this->api->wizard->get_wizard_totals( $params );
	}

	/**
	 * Update wizard loop
	 *
	 * @param $params
	 *
	 * @return mixed
	 * @since 0.14.6
	 */
	public function api_update_wizard_loop( $params ) {
		return $this->api->wizard->update_wizard_loop( $params );
	}

	/**
	 * Update dashboard status
	 *
	 * @param $params
	 *
	 * @return array|WP_Error
	 * @since 0.13.0
	 */
	public function api_update_dashboard_status( $params ) {

		global $wpdb;

		$league_obj = wp_parse_args(
			$params['league_obj'] ?? [],
			[
				'local_status' => '',
				'local_value'  => '',
				'id'           => '',
			]
		);

		if ( ! $league_obj['id'] || ! $league_obj['local_value'] ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Invalid Initial Data', [ 'status' => 400 ] );
		}

		$mapping_table = $wpdb->prefix . 'anwpfl_import_mapping';

		return [
			'result' => $wpdb->update(
				$mapping_table,
				[
					'status' => AnWP_Football_Leagues::string_to_bool( $league_obj['local_status'] ) ? 'false' : 'true',
				],
				[
					'provider'       => 'api-football',
					'type'           => 'config-saved-v3',
					'local_value'    => $league_obj['local_value'],
					'external_value' => $league_obj['id'],
				]
			),
		];
	}

	/**
	 * Update API league teams
	 *
	 * @param $params
	 *
	 * @return mixed
	 * @since 0.13.0
	 */
	public function api_update_league_teams( $params ) {

		$api_league_id = empty( $params['league_id'] ) ? 0 : absint( $params['league_id'] );
		$api_season    = empty( $params['season'] ) ? 0 : absint( $params['season'] );

		if ( ! $api_league_id || ! $api_season ) {
			return new WP_Error( 'rest_invalid', 'Incorrect data', [ 'status' => 400 ] );
		}

		$team_ids = [];

		if ( ! empty( $params['teams'] ) && is_array( $params['teams'] ) ) {
			foreach ( $params['teams'] as $team ) {
				if ( isset( $team['checked'] ) && AnWP_Football_Leagues::string_to_bool( $team['checked'] ) && absint( $team['id'] ) ) {
					$team_ids[] = absint( $team['id'] );
				}
			}
		}

		if ( empty( $team_ids ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'No teams to update', [ 'status' => 400 ] );
		}

		return [ 'updated' => $this->api->update_clubs_data( $team_ids, $api_league_id, $api_season ) ];
	}

	/**
	 * Get API competition logo
	 *
	 * @param array $params
	 *
	 * @return array|WP_Error
	 * @since 0.13.0
	 */
	public function api_update_competition_logo( array $params ) {

		$competition_id = absint( $params['competition_id'] );
		$logo_url       = esc_url_raw( $params['logo'] );

		if ( ! $competition_id || ! $logo_url ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Invalid Competition ID or Logo', [ 'status' => 400 ] );
		}

		$local_logo_url = $this->api->upload_logo( $logo_url, $competition_id, 'competition' );

		if ( $local_logo_url ) {
			anwp_fl()->cache->delete( 'FL-COMPETITIONS-LIST' );

			return [
				'result' => true,
				'logo'   => $local_logo_url,
			];
		}

		return new WP_Error( 'rest_anwp_fl_error', 'Error on Save Logo', [ 'status' => 400 ] );
	}


	/**
	 * Create new API Games
	 *
	 * @param $params
	 *
	 * @return mixed
	 * @since 0.13.0
	 */
	public function api_create_new_matches( $params ) {

		$league_obj = wp_parse_args(
			$params['league'] ?? [],
			[
				'year'        => '',
				'start'       => '',
				'end'         => '',
				'id'          => '',
				'local_value' => '',
			]
		);

		if ( empty( $league_obj['id'] ) || empty( $league_obj['year'] || empty( $league_obj['local_value'] ) ) ) {
			return new WP_Error( 'anwp_rest_error', 'Initial data error', [ 'status' => 400 ] );
		}

		global $wpdb;

		$import = [
			'message' => '',
			'qty'     => 0,
		];

		$progress_status = isset( $params['progress_status'] ) ? sanitize_key( $params['progress_status'] ) : '';

		if ( ! $progress_status ) {
			return new WP_Error( 'anwp_rest_error', 'Progress status not set', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------------
		| Pre :: multistage competition
		|--------------------------------------------------------------------------
		*/
		$api_rounds    = [];
		$mapped_rounds = $this->api->helper->get_api_mapped_rounds( $league_obj['id'], $league_obj['local_value'] );

		foreach ( $mapped_rounds as $mapped_stage ) {
			$api_rounds = array_merge( $api_rounds, $mapped_stage );
		}

		if ( empty( $api_rounds ) ) {
			return new WP_Error( 'anwp_rest_error', 'API rounds not set', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------------
		| Step 1 :: get all matches from API
		|--------------------------------------------------------------------------
		*/

		/*
		|--------------------------------------------------------------------
		| Get Fixtures from API
		|--------------------------------------------------------------------
		*/
		$request_args = [
			'league' => $league_obj['id'],
			'season' => $league_obj['year'],
		];

		$response = $this->api->send_request_to_api( 'fixtures', 600, $request_args );

		if ( ! empty( $response['errors'] ) ) {
			return new WP_Error( 'anwp_rest_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
		}

		if ( empty( $response['response'] ) ) {
			return new WP_Error( 'anwp_rest_error', 'Empty API Data', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------------
		| Filter by Rounds
		|--------------------------------------------------------------------------
		*/
		$api_data = [];

		foreach ( $response['response'] as $api_match ) {
			if ( in_array( $api_match['league']['round'], $api_rounds, true ) ) {
				$api_data[] = $api_match;
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Step 2 :: get mapped/missed matches
		|--------------------------------------------------------------------------
		*/
		$matches_api_ids = array_map(
			function ( $m ) {
				return $m['fixture']['id'];
			},
			$api_data
		);

		$this->api->helper->remove_missed_matches( $matches_api_ids );

		$placeholders = array_fill( 0, count( $matches_api_ids ), '%s' );
		$format       = implode( ', ', $placeholders );

		$matches_mapped = $wpdb->get_col(
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"
					SELECT external_value
					FROM {$wpdb->prefix}anwpfl_import_mapping
					WHERE       provider = %s
					            AND type = 'match'
					            AND external_value IN ({$format})
					",
				array_merge( [ $this->api->provider ], $matches_api_ids )
			)
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		/*
		|--------------------------------------------------------------------------
		| Step 3 :: create new (missed) matches
		|--------------------------------------------------------------------------
		*/
		$matches_to_create = 0;
		$step_limit        = 5;

		foreach ( $api_data as $game ) {

			if ( isset( $game['fixture']['id'] ) && ! in_array( (string) $game['fixture']['id'], $matches_mapped, true ) ) {
				$matches_to_create ++;

				if ( 'loop' === $progress_status ) {

					if ( -- $step_limit < 0 ) {
						break;
					}

					$match_id = $this->api->helper->create_match( $league_obj['local_value'], $game, $mapped_rounds );

					if ( $match_id ) {
						$import['qty'] ++;
					} else {
						$step_limit ++;
					}
				}
			}
		}

		if ( empty( $matches_to_create ) && 'total' === $progress_status ) {
			return new WP_Error( 'anwp_rest_warning', 'No Games to Create', [ 'status' => 400 ] );
		}

		$import['result'] = true;

		/*
		|--------------------------------------------------------------------
		| Prepare progress data
		|--------------------------------------------------------------------
		*/
		if ( 'total' === $progress_status ) {
			$import['progress_total']   = $matches_to_create;
			$import['progress_message'] = 'Creating games - 0/' . $matches_to_create . ' ...';
		} elseif ( 'loop' === $progress_status ) {
			$progress_total = $params['progress_total'] ?? 0;
			$progress_sum   = $params['progress_sum'] ?? 0;

			$import['progress_total']   = $progress_total;
			$import['progress_sum']     = $progress_sum + $import['qty'];
			$import['progress_message'] = 'Creating games - ' . $import['progress_sum'] . '/' . $progress_total . ' ...';
		}

		return $import;
	}

	/**
	 * Update kickoff time for upcoming matches.
	 *
	 * @param array $params
	 *
	 * @return array|WP_Error
	 * @since 0.13.0
	 */
	public function api_update_kickoff( array $params ) {
		global $wpdb;

		$import = [
			'message' => '',
			'qty'     => 0,
		];

		if ( ! empty( $params['context'] ) && 'dashboard' === $params['context'] && ! empty( $params['competition_id'] ) ) {
			$mapped_competition = $this->api->helper->get_api_competition_by_local( $params['competition_id'] );

			$league_obj = [
				'year'        => $mapped_competition['season_api'],
				'id'          => $mapped_competition['competition_api_id'],
				'local_value' => absint( $params['competition_id'] ),
			];
		} else {
			$league_obj = $params['league'] ?? [];
		}

		if ( empty( $league_obj['id'] ?? '' ) || empty( $league_obj['year'] ?? '' ) || empty( $league_obj['local_value'] ?? '' ) ) {
			return new WP_Error( 'anwp_rest_error', 'Initial data error', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------------
		| Step 1 :: get all matches from API
		|--------------------------------------------------------------------------
		*/
		$response = $this->api->send_request_to_api(
			'fixtures',
			600,
			[
				'league' => $league_obj['id'],
				'season' => $league_obj['year'],
			]
		);

		if ( ! empty( $response['errors'] ) ) {
			return new WP_Error( 'anwp_rest_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
		}

		if ( empty( $response['response'] ) ) {
			return new WP_Error( 'anwp_rest_error', 'Empty API Data', [ 'status' => 400 ] );
		}

		$api_data = $response['response'];

		/*
		|--------------------------------------------------------------------------
		| Step 2 :: get mapped/missed matches
		|--------------------------------------------------------------------------
		*/
		$matches_api_ids = array_map(
			function ( $m ) {
				return $m['fixture']['id'];
			},
			$api_data
		);

		$placeholders = array_fill( 0, count( $matches_api_ids ), '%s' );
		$format       = implode( ', ', $placeholders );

		// Update only finished games or all games
		$games_to_update = '';

		if ( 'no' !== $this->api->config['update_kickoff_0'] ) {
			$games_to_update = 'AND b.finished = 0';
		}

		// Get upcoming games
		$matches_upcoming = $wpdb->get_results(
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"
					SELECT a.external_value, a.local_value, b.kickoff, b.special_status, b.stadium_id, b.referee
					FROM {$wpdb->prefix}anwpfl_import_mapping a
					LEFT JOIN {$wpdb->prefix}anwpfl_matches AS b ON b.match_id = a.local_value
					WHERE       a.provider = %s
					            AND a.type = 'match'
					            $games_to_update
					            AND a.external_value IN ({$format})
					",
				array_merge( [ $this->api->provider ], $matches_api_ids )
			),
			OBJECT_K
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		if ( empty( $matches_upcoming ) ) {
			return new WP_Error( 'anwp_rest_warning', 'No Upcoming games', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------------
		| Step 3 :: update kickoff
		|--------------------------------------------------------------------------
		*/
		$matches_updated = 0;

		foreach ( $api_data as $match ) {
			if ( key_exists( $match['fixture']['id'], $matches_upcoming ) ) {

				$match_analyzed = $matches_upcoming[ $match['fixture']['id'] ];

				if ( ! empty( $match['fixture']['date'] ) ) {

					$maybe_gmt_date = DateTime::createFromFormat( 'Y-m-d\TH:i:sP', $match['fixture']['date'] )->format( 'Y-m-d H:i:s' );
					$kickoff        = get_date_from_gmt( $maybe_gmt_date );

					// Update only if game has the valid kickoff time
					if ( $kickoff && anwp_fl()->helper->validate_date( $kickoff ) ) {

						$special_status = '';

						if ( ! empty( $match['fixture']['status']['short'] ) && in_array( $match['fixture']['status']['short'], [ 'PST', 'TBD', 'CANC' ], true ) ) {
							$special_status = $match['fixture']['status']['short'];
						}

						if ( $kickoff !== $match_analyzed->kickoff || $special_status !== $match_analyzed->special_status ) {
							anwp_fl()->match->update(
								$match_analyzed->local_value,
								[
									'kickoff'        => $kickoff,
									'kickoff_gmt'    => $maybe_gmt_date,
									'special_status' => $special_status,
								]
							);

							if ( $kickoff !== $match_analyzed->kickoff ) {
								anwp_fl_pro()->match->update_match_title_slug( [], $match_analyzed->local_value );
							}

							// Update counter
							$matches_updated ++;
						}
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Step 4 :: update stadiums
		|--------------------------------------------------------------------------
		*/
		if ( 'no' !== $this->api->config['stadiums'] ) {
			foreach ( $api_data as $match ) {
				if ( key_exists( $match['fixture']['id'], $matches_upcoming ) && ! empty( $match['fixture']['venue']['id'] ) ) {

					$match_analyzed = $matches_upcoming[ $match['fixture']['id'] ];
					$stadium_id     = absint( $this->api->get_mapped_stadium_id( $match['fixture']['venue'] ) );

					if ( $stadium_id && absint( $match_analyzed->stadium_id ) !== $stadium_id ) {
						anwp_fl()->match->update(
							$match_analyzed->local_value,
							[
								'stadium_id' => $stadium_id,
							]
						);

						$matches_updated ++;
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Step 5 :: update referees
		|--------------------------------------------------------------------------
		*/
		if ( 'yes' === $this->api->config['referees'] ) {
			foreach ( $api_data as $match ) {

				if ( key_exists( $match['fixture']['id'], $matches_upcoming ) && ! empty( $match['fixture']['referee'] ) ) {

					$match_analyzed = $matches_upcoming[ $match['fixture']['id'] ];
					$referee_id     = $this->api->get_referee_id_by_api_name( $match['fixture']['referee'] );

					if ( ! empty( $referee_id ) && absint( $referee_id ) !== absint( $match_analyzed->referee ) ) {
						anwp_fl()->match->update(
							$match_analyzed->local_value,
							[
								'referee' => $referee_id,
							]
						);

						$matches_updated ++;
					}
				}
			}
		}

		$import['result'] = true;
		$import['qty']    = $matches_updated;

		if ( ! absint( $matches_updated ) ) {
			return new WP_Error( 'anwp_rest_warning', 'All Games have valid time, stadiums and referees', [ 'status' => 400 ] );
		}

		return $import;
	}

	/**
	 * Get API competition groups
	 *
	 * @param $params
	 *
	 * @return mixed
	 * @since 0.13.0
	 */
	public function api_get_competition_groups( $params ) {

		$league_obj    = isset( $params['league_obj'] ) ? (object) $params['league_obj'] : false;
		$mapped_rounds = isset( $params['mapped_rounds'] ) ? (object) $params['mapped_rounds'] : false;
		$single_table  = AnWP_Football_Leagues::string_to_bool( $params['single_table'] ?? false );

		if ( empty( $league_obj ) || empty( $league_obj->id ) || empty( $mapped_rounds ) || empty( $league_obj->local_value ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Invalid Initial Data', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------
		| Get local competition structure
		|--------------------------------------------------------------------
		*/
		$local_competition = anwp_football_leagues()->competition->get_competition( $league_obj->local_value );
		$local_stages      = [ $local_competition ];

		if ( empty( $local_competition ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Local Competition not exists', [ 'status' => 400 ] );
		}

		if ( 'main' === $local_competition->multistage ) {
			$all_competitions = anwp_football_leagues()->competition->get_competitions();

			if ( ! empty( $all_competitions ) && is_array( $all_competitions ) ) {
				foreach ( $all_competitions as $competition ) {
					if ( absint( $competition->multistage_main ) === absint( $league_obj->local_value ) ) {
						$local_stages[] = $competition;
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Get standing tables with its names
		|--------------------------------------------------------------------
		*/
		$api_standings = [];

		$response = $this->api->send_request_to_api(
			'standings',
			3600,
			[
				'league' => $league_obj->id,
				'season' => $league_obj->year,
			]
		);

		if ( ! empty( $response['response'] ) ) {
			foreach ( $response['response'] as $league_data ) {
				if ( ! empty( $league_data['league']['standings'] ) ) {
					foreach ( $league_data['league']['standings'] as $standing_data ) {
						if ( ! empty( $standing_data ) && is_array( $standing_data ) ) {

							$maybe_group_title = sanitize_text_field( $standing_data[0]['group'] ?? '' );

							if ( false !== mb_stripos( $maybe_group_title, 'Group' ) ) {
								$maybe_group_title = mb_substr( $maybe_group_title, mb_stripos( $maybe_group_title, 'Group' ) );
							} else {
								continue;
							}

							if ( empty( $maybe_group_title ) ) {
								continue;
							}

							$group_teams = [];

							foreach ( $standing_data as $standing_place ) {
								if ( isset( $standing_place['team']['id'] ) ) {
									$group_teams[] = absint( $standing_place['team']['id'] );
								}
							}

							natsort( $group_teams );
							$api_standings[ implode( '-', $group_teams ) ] = $maybe_group_title;
						}
					}
				}
			}
		}

		$request_args = [
			'league' => $league_obj->id,
			'season' => $league_obj->year,
		];

		/*
		|--------------------------------------------------------------------
		| Get Fixtures from API
		|--------------------------------------------------------------------
		*/
		$response = $this->api->send_request_to_api( 'fixtures', 600, $request_args );

		if ( ! empty( $response['errors'] ) ) {
			return new WP_Error( 'anwp_rest_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
		}

		if ( empty( $response['response'] ) ) {
			return new WP_Error( 'anwp_rest_error', 'Empty API Data', [ 'status' => 400 ] );
		}

		$api_games        = $response['response'];
		$mapped_structure = [];

		foreach ( $local_stages as $local_competition ) {

			if ( empty( $local_competition->rounds ) || ! is_array( $local_competition->rounds ) ) {
				continue;
			}

			foreach ( $local_competition->rounds as $local_round ) {

				$mapped_slug = $local_competition->id . '-' . $local_round->id;
				$output      = [];

				if ( empty( $mapped_rounds->{$mapped_slug} ) ) {
					continue;
				}

				$maybe_sync = true;

				if ( ! empty( $local_competition->groups ) || is_array( $local_competition->groups ) ) {
					foreach ( $local_competition->groups as $competition_group ) {
						if ( absint( $competition_group->round ) === absint( $local_round->id ) && ! empty( $competition_group->clubs ) && count( $competition_group->clubs ) ) {
							$maybe_sync = false;
							break;
						}
					}
				}

				if ( 'round-robin' === $local_competition->type ) {
					$temp_groups = [];

					if ( $single_table ) {

						$temp_group = [];

						foreach ( $api_games as $fixture ) {
							if ( ! in_array( $fixture['league']['round'], $mapped_rounds->{$mapped_slug}, true ) ) {
								continue;
							}

							if ( isset( $fixture['teams']['home']['id'] ) ) {
								$temp_group[ $fixture['teams']['home']['id'] ] = $fixture['teams']['home']['name'];
							}

							if ( isset( $fixture['teams']['away']['id'] ) ) {
								$temp_group[ $fixture['teams']['away']['id'] ] = $fixture['teams']['away']['name'];
							}
						}

						$temp_groups[] = $temp_group;
					} else {
						foreach ( $api_games as $fixture ) {

							if ( ! in_array( $fixture['league']['round'], $mapped_rounds->{$mapped_slug}, true ) ) {
								continue;
							}

							if ( isset( $fixture['teams']['home']['id'] ) ) {
								$temp_groups[ $fixture['teams']['home']['id'] ][ $fixture['teams']['home']['id'] ] = $fixture['teams']['home']['name'];

								if ( isset( $fixture['teams']['away']['id'] ) ) {
									$temp_groups[ $fixture['teams']['home']['id'] ][ $fixture['teams']['away']['id'] ] = $fixture['teams']['away']['name'];
								}
							}

							if ( isset( $fixture['teams']['away']['id'] ) ) {
								$temp_groups[ $fixture['teams']['away']['id'] ][ $fixture['teams']['away']['id'] ] = $fixture['teams']['away']['name'];

								if ( isset( $fixture['teams']['home']['id'] ) ) {
									$temp_groups[ $fixture['teams']['away']['id'] ][ $fixture['teams']['home']['id'] ] = $fixture['teams']['home']['name'];
								}
							}
						}

						foreach ( $temp_groups as $item_index => $item ) {
							ksort( $item, SORT_NUMERIC );
							$temp_groups[ $item_index ] = $item;
						}

						$temp_groups = array_map( 'unserialize', array_unique( array_map( 'serialize', $temp_groups ) ) );
					}

					$group_counter = 1;

					foreach ( $temp_groups as $group ) {
						$group_title = 'Group #' . $group_counter ++;
						$group_teams = array_keys( $group );

						natsort( $group_teams );

						if ( ! empty( $api_standings[ implode( '-', $group_teams ) ] ) ) {
							$group_title = $api_standings[ implode( '-', $group_teams ) ];
						}

						$output[] = [
							'title'    => $group_title,
							'textData' => implode( ', ', array_values( $group ) ),
							'clubs'    => implode( ',', array_keys( $group ) ),
							'sync'     => $maybe_sync,
						];
					}

					$output = wp_list_sort( $output, 'title' );
				} elseif ( 'knockout' === $local_competition->type ) {

					$structure = [];

					foreach ( $api_games as $fixture ) {
						if ( ! in_array( $fixture['league']['round'], $mapped_rounds->{$mapped_slug}, true ) ) {
							continue;
						}

						$home_id = isset( $fixture['teams']['home']['id'] ) ? absint( $fixture['teams']['home']['id'] ) : 0;
						$away_id = isset( $fixture['teams']['away']['id'] ) ? absint( $fixture['teams']['away']['id'] ) : 0;

						if ( empty( $home_id ) || empty( $away_id ) ) {
							continue;
						}

						if ( ! empty( $structure[ $home_id . '-' . $away_id ] ) || ! empty( $structure[ $away_id . '-' . $home_id ] ) ) {
							continue;
						}

						$structure[ $home_id . '-' . $away_id ] = $fixture['teams']['home']['name'] . ' - ' . $fixture['teams']['away']['name'];
					}

					$club_ids   = [];
					$club_names = [];

					foreach ( $structure as $round_ids => $round_names ) {
						$club_ids[]   = $round_ids;
						$club_names[] = $round_names;
					}

					$output[] = [
						'title'    => '',
						'clubs'    => implode( ',', array_keys( $structure ) ),
						'textData' => implode( '; ', $club_names ),
						'sync'     => $maybe_sync,
					];
				}

				$mapped_structure[ $mapped_slug ] = $output;
			}
		}

		return [ 'mapped_structure' => $mapped_structure ];
	}

	/**
	 * Update missed matches from API.
	 *
	 * @param $params
	 *
	 * @return mixed
	 * @since 0.13.0
	 */
	public function api_reupdate_matches( $params ) {
		$competition_id = absint( $params['competition_id'] ?? '' );

		if ( ! $competition_id ) {
			return new WP_Error( 'anwp_rest_error', 'Invalid Competition ID', [ 'status' => 400 ] );
		}

		global $wpdb;

		$import = [
			'result'  => false,
			'message' => '',
			'qty'     => 0,
		];

		$match_id    = absint( $params['match_id'] ?? 0 );
		$match_week  = absint( $params['matchweek'] ?? 0 );
		$update_type = sanitize_key( $params['update_type'] ?? '' );

		if ( ! in_array( $update_type, [ 'all', 'match', 'matchweek' ], true ) ) {
			return new WP_Error( 'anwp_rest_error', 'Incorrect Update Type', [ 'status' => 400 ] );
		}

		if ( 'match' === $update_type && ! $match_id ) {
			return new WP_Error( 'anwp_rest_error', 'Empty Match ID', [ 'status' => 400 ] );
		}

		if ( 'matchweek' === $update_type && ! $match_week ) {
			return new WP_Error( 'anwp_rest_error', 'Empty MatchWeek', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------
		| Update Single Match
		|--------------------------------------------------------------------
		*/
		if ( 'match' === $update_type ) {
			// Get Local Matches to Update
			$match = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT *
					FROM {$wpdb->prefix}anwpfl_import_mapping
					WHERE       provider = %s
					            AND type = 'match'
					            AND local_value = %d
					",
					$this->api->provider,
					$match_id
				)
			);

			if ( empty( $match->external_value ) || ! absint( $match->external_value ) ) {
				return new WP_Error( 'anwp_rest_error', 'Invalid Match ID', [ 'status' => 400 ] );
			}

			$api_match_id = absint( $match->external_value );

			if ( $this->api->helper->update_match( $api_match_id, $match_id ) ) {
				$import['qty'] ++;
				$import['result'] = true;
			}

			return $import;
		}

		/*
		|--------------------------------------------------------------------
		| Update MatchWeek Games
		|--------------------------------------------------------------------
		*/
		if ( 'matchweek' === $update_type ) {
			// Get all finished matches for selected matchweek
			$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended(
				[
					'filter_by_matchweeks' => $match_week,
					'competition_id'       => $competition_id,
					'type'                 => 'result',
					'show_secondary'       => 1,
				]
			);

			if ( empty( $matches ) || ! is_array( $matches ) ) {
				return new WP_Error( 'anwp_rest_error', 'Empty Data', [ 'status' => 400 ] );
			}

			$matches = wp_list_pluck( $matches, 'match_id' );

			foreach ( $matches as $single_match ) {

				sleep( 1 );

				// Get Local Matches to Update
				$match = $wpdb->get_row(
					$wpdb->prepare(
						"
							SELECT *
							FROM {$wpdb->prefix}anwpfl_import_mapping
							WHERE       provider = %s
							            AND type = 'match'
							            AND local_value = %d
							",
						$this->api->provider,
						$single_match
					)
				);

				if ( empty( $match->external_value ) || ! absint( $match->external_value ) ) {
					continue;
				}

				$api_match_id = absint( $match->external_value );

				if ( $this->api->helper->update_match( $api_match_id, $single_match ) ) {
					$import['qty'] ++;
					$import['result'] = true;
				}
			}

			return $import;
		}

		/*
		|--------------------------------------------------------------------
		| Update All Games
		|--------------------------------------------------------------------
		*/
		$progress_status = isset( $params['progress_status'] ) ? sanitize_key( $params['progress_status'] ) : '';

		if ( ! $progress_status ) {
			return new WP_Error( 'anwp_rest_error', 'Progress status not set', [ 'status' => 400 ] );
		}

		if ( 'total' === $progress_status ) {

			$matches = anwp_fl()->competition->tmpl_get_competition_matches_extended(
				[
					'competition_id' => $competition_id,
					'type'           => 'result',
					'show_secondary' => 1,
				],
				'ids'
			);

			if ( empty( $matches ) || ! is_array( $matches ) ) {
				return new WP_Error( 'anwp_rest_warning', 'No Games to Update', [ 'status' => 400 ] );
			}

			$import['result'] = true;

			$import['progress_total']   = count( $matches );
			$import['progress_message'] = 'Updating games - 0/' . count( $matches ) . ' ...';

		} elseif ( 'loop' === $progress_status ) {

			$progress_total = $params['progress_total'] ?? 0;
			$progress_sum   = $params['progress_sum'] ?? 0;

			$matches = anwp_fl()->competition->tmpl_get_competition_matches_extended(
				[
					'competition_id' => $competition_id,
					'type'           => 'result',
					'sort_by_date'   => 'asc',
					'show_secondary' => 1,
				],
				'ids'
			);

			$matches = array_slice( $matches, $progress_sum, 3 );

			foreach ( $matches as $single_match ) {

				// Get Local Matches to Update
				$match = $wpdb->get_row(
					$wpdb->prepare(
						"
							SELECT *
							FROM {$wpdb->prefix}anwpfl_import_mapping
							WHERE       provider = %s
							            AND type = 'match'
							            AND local_value = %d
							",
						$this->api->provider,
						$single_match
					)
				);

				if ( empty( $match->external_value ) || ! absint( $match->external_value ) ) {
					continue;
				}

				$api_match_id = absint( $match->external_value );

				if ( $this->api->helper->update_match( $api_match_id, $single_match ) ) {
					$import['qty'] ++;
					$import['result'] = true;
				}
			}

			$import['progress_total']   = $progress_total;
			$import['progress_sum']     = $progress_sum + $import['qty'];
			$import['progress_message'] = 'Updating games - ' . $import['progress_sum'] . '/' . $progress_total . ' ... (IDs: ' . implode( ', ', $matches ) . ')';
		}

		return $import;
	}

	/**
	 * Update competition structure
	 *
	 * @param $params
	 *
	 * @return array|true[]|WP_Error
	 * @since 0.13.0
	 */
	public function api_update_competition_structure( $params ) {

		$league_obj    = (object) ( $params['league_obj'] ?? [] );
		$mapped_rounds = (object) ( $params['mapped_rounds'] ?? [] );
		$api_rounds    = $params['api_rounds'] ?? [];
		$mapped_groups = (object) ( $params['mapped_groups'] ?? [] );

		if ( empty( $league_obj ) || empty( $league_obj->id ) || empty( $mapped_rounds ) || empty( $league_obj->local_value ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Invalid Initial Data', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------
		| Get local competition structure
		|--------------------------------------------------------------------
		*/
		$local_competition = anwp_football_leagues()->competition->get_competition( $league_obj->local_value );
		$local_stages      = [ $local_competition ];

		if ( empty( $local_competition ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Local Competition not exists', [ 'status' => 400 ] );
		}

		if ( 'main' === $local_competition->multistage ) {
			$all_competitions = anwp_football_leagues()->competition->get_competitions();

			if ( ! empty( $all_competitions ) && is_array( $all_competitions ) ) {
				foreach ( $all_competitions as $competition ) {
					if ( absint( $competition->multistage_main ) === absint( $league_obj->local_value ) ) {
						$local_stages[] = $competition;
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Save mapped data
		|--------------------------------------------------------------------
		*/
		$this->api->helper->update_api_rounds( $api_rounds, $league_obj->id, $league_obj->local_value );
		$this->api->helper->update_api_mapped_rounds( $mapped_rounds, $league_obj->id, $league_obj->local_value );

		/*
		|--------------------------------------------------------------------
		| Update competition structure
		|--------------------------------------------------------------------
		*/
		foreach ( $local_stages as $local_competition ) {

			if ( empty( $local_competition->rounds ) || ! is_array( $local_competition->rounds ) ) {
				continue;
			}

			foreach ( $local_competition->rounds as $local_round ) {

				$mapped_slug = $local_competition->id . '-' . $local_round->id;

				if ( empty( $mapped_rounds->{$mapped_slug} ) || empty( $mapped_groups->{$mapped_slug} ) ) {
					continue;
				}

				if ( 'round-robin' === $local_competition->type ) {
					/*
					|--------------------------------------------------------------------
					| Round Robin
					|--------------------------------------------------------------------
					*/
					$group_clubs = [];

					foreach ( $mapped_groups->{$mapped_slug} as $sync_group ) {
						if ( empty( $sync_group['sync'] ) || empty( $sync_group['clubs'] ) ) {
							continue;
						}

						$api_club_ids  = wp_parse_id_list( $sync_group['clubs'] );
						$api_new_clubs = [];

						foreach ( $api_club_ids as $api_club_id ) {
							if ( ! $this->api->helper->get_mapped_club_id( $api_club_id ) ) {
								$api_new_clubs[] = $api_club_id;
							}
						}

						if ( ! empty( $api_new_clubs ) ) {
							$this->api->update_clubs_data( $api_new_clubs, $league_obj->id, $league_obj->year );

							// trigger force update
							$this->api->helper->get_mapped_club_id( 0, true, true );
						}

						$maybe_group_title = sanitize_text_field( $sync_group['title'] );

						if ( false !== mb_stripos( $maybe_group_title, 'Group' ) ) {
							$maybe_group_title = mb_substr( $maybe_group_title, mb_stripos( $maybe_group_title, 'Group' ) );
						} else {
							$maybe_group_title = '';
						}

						$group_clubs[] = [
							'ids'   => $api_club_ids,
							'title' => $maybe_group_title,
						];
					}

					// Get competition groups
					$competition_groups = json_decode( get_post_meta( $local_competition->id, '_anwpfl_groups', true ) );

					if ( ! empty( $competition_groups ) && is_array( $competition_groups ) ) {

						foreach ( $competition_groups as $group ) {
							if ( empty( $group_clubs ) ) {
								break;
							}

							$local_ids = [];
							$api_ids   = array_shift( $group_clubs );

							foreach ( $api_ids['ids'] as $api_club_id ) {
								$local_club_id = absint( $this->api->helper->get_mapped_club_id( $api_club_id ) );

								if ( $local_club_id ) {
									$local_ids[] = $local_club_id;
								}
							}

							$group->clubs = $local_ids;

							if ( ! empty( $api_ids['title'] ) ) {
								$group->title = $api_ids['title'];
							}
						}
					} else {
						$competition_groups = [];
					}

					if ( ! empty( $group_clubs ) ) {

						$next_group_id = get_post_meta( $local_competition->id, '_anwpfl_group_next_id', true );
						$round_id      = get_post_meta( $local_competition->id, '_anwpfl_round_next_id', true ) - 1;

						foreach ( $group_clubs as $api_ids ) {

							$local_ids = [];

							foreach ( $api_ids['ids'] as $api_club_id ) {
								$local_club_id = absint( $this->api->helper->get_mapped_club_id( $api_club_id ) );

								if ( $local_club_id ) {
									$local_ids[] = $local_club_id;
								}
							}

							$competition_groups[] = (object) [
								'id'    => absint( $next_group_id ),
								'title' => empty( $api_ids['title'] ) ? 'Group #' . $next_group_id : $api_ids['title'],
								'round' => absint( $round_id ),
								'clubs' => $local_ids,
							];

							$next_group_id ++;
						}

						update_post_meta( $local_competition->id, '_anwpfl_group_next_id', $next_group_id );
					}

					update_post_meta( $local_competition->id, '_anwpfl_groups', wp_json_encode( $competition_groups ) );
				} elseif ( 'knockout' === $local_competition->type ) {

					/*
					|--------------------------------------------------------------------
					| Knockout
					|--------------------------------------------------------------------
					*/
					foreach ( $mapped_groups->{$mapped_slug} as $sync_round ) {

						if ( empty( $sync_round['sync'] ) || empty( $sync_round['clubs'] ) ) {
							continue;
						}

						$api_ties      = wp_parse_list( $sync_round['clubs'] );
						$api_new_clubs = [];
						$api_club_ids  = [];

						foreach ( $api_ties as $api_tie_index => $api_tie ) {

							$api_ties[ $api_tie_index ] = explode( '-', $api_tie );

							$api_club_ids[] = $api_ties[ $api_tie_index ][0];
							$api_club_ids[] = $api_ties[ $api_tie_index ][1];
						}

						foreach ( $api_club_ids as $api_club_id ) {
							if ( ! $this->api->helper->get_mapped_club_id( $api_club_id ) ) {
								$api_new_clubs[] = $api_club_id;
							}
						}

						if ( ! empty( $api_new_clubs ) ) {
							$this->api->update_clubs_data( $api_new_clubs, $league_obj->id, $league_obj->year );

							// trigger force update
							$this->api->helper->get_mapped_club_id( 0, true, true );
						}

						// Get competition groups
						$competition_groups = json_decode( get_post_meta( $local_competition->id, '_anwpfl_groups', true ) );

						if ( ! empty( $competition_groups ) && is_array( $competition_groups ) ) {
							foreach ( $competition_groups as $group ) {

								if ( empty( $api_ties ) ) {
									break;
								}

								if ( absint( $group->round ) === absint( $local_round->id ) ) {

									$tie_ids   = array_shift( $api_ties );
									$local_ids = [];

									foreach ( $tie_ids as $api_club_id ) {
										$local_club_id = absint( $this->api->helper->get_mapped_club_id( $api_club_id ) );

										if ( $local_club_id ) {
											$local_ids[] = $local_club_id;
										}
									}

									$group->clubs = $local_ids;
									continue;
								}
							}

							if ( ! empty( $api_ties ) ) {

								$next_group_id = get_post_meta( $local_competition->id, '_anwpfl_group_next_id', true );

								foreach ( $api_ties as $api_tie ) {

									$local_ids = [];

									foreach ( $api_tie as $api_club_id ) {
										$local_club_id = absint( $this->api->helper->get_mapped_club_id( $api_club_id ) );

										if ( $local_club_id ) {
											$local_ids[] = $local_club_id;
										}
									}

									$competition_groups[] = (object) [
										'id'    => absint( $next_group_id ),
										'title' => 'Tie #' . $next_group_id,
										'round' => absint( $local_round->id ),
										'clubs' => $local_ids,
									];

									$next_group_id ++;
								}

								update_post_meta( $local_competition->id, '_anwpfl_group_next_id', $next_group_id );
							}

							update_post_meta( $local_competition->id, '_anwpfl_groups', wp_json_encode( $competition_groups ) );
						}
					}
				}
			}
		}

		// Reset Competition Cache
		if ( class_exists( 'AnWPFL_Cache' ) ) {
			anwp_fl()->cache->delete( 'FL-COMPETITIONS-LIST' );
		}

		if ( AnWP_Football_Leagues::string_to_bool( $params['new_competition'] ) && ! $this->api->helper->get_api_competition_by_local( $league_obj->local_value )['competition_api_id'] ) {

			global $wpdb;

			// Setup table name
			$mapping_table = $wpdb->prefix . 'anwpfl_import_mapping';

			$extra_data = wp_json_encode(
				(object) [
					'season' => $league_obj->year,
					'start'  => $league_obj->start,
					'end'    => $league_obj->end,
				]
			);

			// Update saved config mapping
			$data = [
				'provider'       => 'api-football',
				'type'           => 'config-saved-v3',
				'local_value'    => $league_obj->local_value,
				'external_value' => $league_obj->id,
				'status'         => 'false',
				'extra_data'     => $extra_data,
			];

			$wpdb->replace( $mapping_table, $data );

			// Update Competition mapping
			$data['status'] = '';
			$data['type']   = 'competition-v3';

			$wpdb->replace( $mapping_table, $data );

			return [
				'result'        => true,
				'saved_configs' => $this->api->get_data_saved_configs(),
			];
		}

		return [ 'result' => true ];
	}

	/**
	 * Update injuries.
	 *
	 * @param $params
	 *
	 * @return array|WP_Error
	 * @since 0.13.7
	 */
	public function api_update_injuries( $params ) {
		global $wpdb;

		$competition_id = isset( $params['competition_id'] ) ? absint( $params['competition_id'] ) : '';

		if ( ! $competition_id ) {
			return new WP_Error( 'anwp_rest_error', 'Invalid Competition ID', [ 'status' => 400 ] );
		}

		$import = [
			'result'  => false,
			'message' => '',
			'qty'     => 0,
		];

		$players_updated = 0;

		// Get upcoming games
		$matches_upcoming = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT a.external_value, a.local_value
					FROM {$wpdb->prefix}anwpfl_import_mapping a
					LEFT JOIN {$wpdb->prefix}anwpfl_matches AS b ON b.match_id = a.local_value
					WHERE       a.provider = %s
					            AND a.type = 'match'
					            AND b.finished = 0
					            AND b.kickoff >= CURDATE()
					            AND ( b.competition_id = %d OR b.main_stage_id = %d )
					ORDER BY b.kickoff ASC
					LIMIT 30
					",
				$this->api->provider,
				$competition_id,
				$competition_id
			),
			OBJECT_K
		);

		if ( empty( $matches_upcoming ) ) {
			return new WP_Error( 'anwp_rest_warning', 'No Upcoming matches', [ 'status' => 400 ] );
		}

		if ( absint( $params['competition_api_id'] ?? '' ) && absint( $params['season_api'] ?? '' ) ) {
			$api_competition = [
				'competition_api_id' => absint( $params['competition_api_id'] ),
				'season_api'         => absint( $params['season_api'] ),
			];
		} else {
			$api_competition = $this->api->helper->get_api_competition_by_local( $competition_id );
		}

		/*
		|--------------------------------------------------------------------------
		| Get League's Injuries
		|--------------------------------------------------------------------------
		*/
		$response = $this->api->send_request_to_api(
			'injuries',
			600,
			[
				'league' => absint( $api_competition['competition_api_id'] ),
				'season' => absint( $api_competition['season_api'] ),
			]
		);

		if ( ! empty( $response['errors'] ) ) {
			return new WP_Error( 'anwp_rest_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
		}

		if ( empty( $response['response'] ) ) {
			return new WP_Error( 'anwp_rest_error', 'Empty API Data', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------------
		| Get saved injuries
		|--------------------------------------------------------------------------
		*/
		$matches_local_ids = array_values( wp_list_pluck( $matches_upcoming, 'local_value' ) );
		$placeholders      = array_fill( 0, count( $matches_local_ids ), '%s' );
		$format            = implode( ', ', $placeholders );
		$saved_injuries    = [];

		$saved_rows = $wpdb->get_results(
			// phpcs:disable WordPress.DB
			$wpdb->prepare(
				"
					SELECT match_id, player_id
					FROM {$wpdb->prefix}anwpfl_missing_players
					WHERE match_id IN ({$format})
					",
				$matches_local_ids
			)
			// phpcs:enable WordPress.DB
		);

		if ( ! empty( $saved_rows ) ) {
			foreach ( $saved_rows as $saved_row ) {
				if ( ! isset( $saved_injuries[ $saved_row->match_id ] ) ) {
					$saved_injuries[ $saved_row->match_id ] = [];
				}

				$saved_injuries[ $saved_row->match_id ][] = absint( $saved_row->player_id );
			}
		}

		$this->api->init_players_map();
		$l10n_text = [
			'red_card'     => AnWPFL_Text::get_value( 'api_import__injuries__red_card', __( 'Red Card', 'anwp-football-leagues-premium' ) ),
			'yellow_cards' => AnWPFL_Text::get_value( 'api_import__injuries__yellow_cards', __( 'Yellow Cards', 'anwp-football-leagues-premium' ) ),
			'questionable' => AnWPFL_Text::get_value( 'api_import__injuries__questionable', __( 'questionable', 'anwp-football-leagues-premium' ) ),
		];

		foreach ( $response['response'] as $injury_data ) {

			// Update only selected upcoming games
			if ( empty( $injury_data['fixture']['id'] ) || empty( $matches_upcoming[ $injury_data['fixture']['id'] ] ) ) {
				continue;
			}

			$match_id_local  = $matches_upcoming[ $injury_data['fixture']['id'] ]->local_value;
			$player_id_local = absint( $this->api->players_mapped[ $injury_data['player']['id'] ]->local_value );

			// Miss existing data
			if ( ! empty( $saved_injuries[ $match_id_local ] ) && in_array( $player_id_local, $saved_injuries[ $match_id_local ], true ) ) {
				continue;
			}

			$missing_reason  = 'injured';
			$missing_comment = $injury_data['player']['reason'];

			if ( 'Missing Fixture' === $injury_data['player']['type'] && in_array( $injury_data['player']['reason'], [ 'Red Card', 'Yellow Cards' ], true ) ) {
				$missing_reason  = 'suspended';
				$missing_comment = 'Red Card' === $injury_data['player']['reason'] ? $l10n_text['red_card'] : $l10n_text['yellow_cards'];
			}

			if ( 'Questionable' === $injury_data['player']['type'] ) {
				$missing_comment = '(' . $l10n_text['questionable'] . ') ' . $missing_comment;
			}

			// Prepare data to insert
			$data = [
				'reason'    => $missing_reason,
				'match_id'  => $match_id_local,
				'club_id'   => $this->api->helper->get_mapped_club_id( $injury_data['team']['id'] ),
				'player_id' => $player_id_local,
				'comment'   => $missing_comment,
			];

			// Insert data to DB
			if ( $wpdb->insert( $wpdb->prefix . 'anwpfl_missing_players', $data ) ) {
				$players_updated ++;
			}
		}

		$import['qty']    = $players_updated;
		$import['result'] = true;

		return $import;
	}

	/**
	 * Save Finished Scheduled task
	 *
	 * @param $params
	 *
	 * @return array|WP_Error
	 */
	public function api_save_scheduled_task( $params ) {

		$current_task = sanitize_key( $params['savedTask'] ?? '' );
		$is_scheduled = AnWP_Football_Leagues::string_to_bool( $params['scheduled'] ?? false );

		$recurrence_task_map = apply_filters(
			'anwpfl/api-import/hook-recurrence',
			[
				'finished'    => 'hourly',
				'kickoff'     => 'daily',
				'predictions' => 'daily',
				'live'        => 'hourly',
				'lineups'     => 'daily',
				'injuries'    => 'daily',
				'odds'        => 'anwp-fl-every-4-hours',
			]
		);

		if ( ! in_array( $current_task, array_keys( $recurrence_task_map ), true ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Unsupported Task', [ 'status' => 400 ] );
		}

		$schedules_task_slug = 'anwp_fl_api_scheduled_' . $current_task;

		if ( $is_scheduled && wp_next_scheduled( $schedules_task_slug ) ) {
			wp_unschedule_hook( $schedules_task_slug );
		} elseif ( ! $is_scheduled && ! wp_next_scheduled( $schedules_task_slug ) ) {
			wp_schedule_event( time(), $recurrence_task_map[ $current_task ], $schedules_task_slug );
		}

		return [
			'result'    => true,
			'scheduled' => (bool) wp_next_scheduled( $schedules_task_slug ),
		];
	}

	/**
	 * Update odds.
	 *
	 * @param $params
	 *
	 * @return array|WP_Error
	 * @since 0.14.3
	 */
	public function api_update_odds( $params ) {

		global $wpdb;

		$competition_id = absint( $params['competition_id'] ?? '' );

		if ( ! $competition_id ) {
			return new WP_Error( 'anwp_rest_error', 'Invalid Competition ID', [ 'status' => 400 ] );
		}

		$import = [
			'result'  => false,
			'message' => '',
			'qty'     => 0,
		];

		if ( absint( $params['competition_api_id'] ?? '' ) && absint( $params['season_api'] ?? '' ) ) {
			$competition_api = [
				'competition_api_id' => absint( $params['competition_api_id'] ),
				'season_api'         => absint( $params['season_api'] ),
			];
		} else {
			$competition_api = $this->api->helper->get_api_competition_by_local( $competition_id );
		}

		// Return error on empty competition
		if ( empty( $competition_api['competition_api_id'] ) || empty( $competition_api['season_api'] ) ) {
			return new WP_Error( 'anwp_rest_error', 'Invalid API Competition ID or Season', [ 'status' => 400 ] );
		}

		$game_odds = [];

		// Get Fixtures from API
		$response = $this->api->send_request_to_api(
			'odds',
			3600,
			[
				'league' => $competition_api['competition_api_id'],
				'season' => $competition_api['season_api'],
			]
		);

		if ( ! empty( $response['response'] ) && is_array( $response['response'] ) ) {
			$game_odds = $response['response'];

			// Check pagination
			if ( ! empty( $response['paging']['total'] ) && $response['paging']['total'] > 1 ) {
				for ( $ii = 2; $ii <= $response['paging']['total']; $ii ++ ) {

					$response = $this->api->send_request_to_api(
						'odds',
						HOUR_IN_SECONDS,
						[
							'league' => $competition_api['competition_api_id'],
							'season' => $competition_api['season_api'],
							'page'   => $ii,
						]
					);

					if ( ! empty( $response['response'] ) && is_array( $response['response'] ) ) {
						$game_odds = array_merge( $game_odds, $response['response'] );
					}
				}
			}

			/*
			|--------------------------------------------------------------------
			| Prepare and save odds
			|--------------------------------------------------------------------
			*/
			// Get upcoming games
			$competition_games = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT a.external_value, a.local_value
					FROM {$wpdb->prefix}anwpfl_import_mapping a
					LEFT JOIN {$wpdb->prefix}anwpfl_matches AS b ON b.match_id = a.local_value
					WHERE       a.provider = %s
					            AND a.type = 'match'
					            AND b.finished = 0
					            AND ( b.competition_id = %d OR b.main_stage_id = %d )
					",
					$this->api->provider,
					$competition_id,
					$competition_id
				),
				OBJECT_K
			);

			$odds_to_save = [];

			foreach ( $game_odds as $game_odd ) {
				if ( empty( $game_odd['fixture']['id'] ) || empty( $competition_games[ $game_odd['fixture']['id'] ] ) ) {
					continue;
				}

				$last_updated = DateTime::createFromFormat( 'Y-m-d\TH:i:sP', $game_odd['update'] )->format( 'Y-m-d H:i:s' );
				$odds_updated = anwp_fl()->helper->validate_date( $last_updated ) ? $last_updated : '';

				$odds_to_save[ $competition_games[ $game_odd['fixture']['id'] ]->local_value ] = [
					'last_update' => $odds_updated,
					'odds'        => $this->api->parser->parse_odds( $game_odd['bookmakers'] ),
				];
			}

			if ( $odds_to_save ) {
				update_post_meta( $competition_id, '_anwpfl_league_odds', $odds_to_save );
			}
		}

		// Remove error message if updated games exist
		if ( $game_odds ) {
			$import['error']  = '';
			$import['result'] = true;
		}

		return $import;
	}

	/**
	 * Update club squad.
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	public function api_update_squad( $params ) {

		$import = [
			'result'         => false,
			'message'        => '',
			'progress_items' => [],
			'qty'            => 0,
		];

		$competition_id = absint( $params['competition_id'] ?? '' );

		if ( ! $competition_id ) {
			return new WP_Error( 'anwp_rest_error', 'Invalid Competition ID', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------
		| Check Progress Status
		|--------------------------------------------------------------------
		*/
		$progress_status = sanitize_key( $params['progress_status'] ?? '' );

		if ( ! $progress_status ) {
			return new WP_Error( 'anwp_rest_error', 'Progress status not set', [ 'status' => 400 ] );
		}

		if ( 'total' === $progress_status ) {
			$import['progress_items']   = anwp_fl()->competition->get_competition_multistage_clubs( $competition_id );
			$import['progress_total']   = count( $import['progress_items'] );
			$import['progress_message'] = 'Updating club players  - 0/' . count( $import['progress_items'] ) . ' ...';
			$import['result']           = true;

			return $import;
		} elseif ( 'loop' !== $progress_status ) {
			return new WP_Error( 'anwp_rest_error', 'Invalid progress status', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------
		| Progress Items - Clubs
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $params['progress_list_id'] ) ) {
			$club_id = absint( $params['progress_list_id'] );
		} else {
			$clubs = wp_parse_id_list( $params['progress_items'] );

			if ( empty( $clubs ) || ! is_array( $clubs ) ) {
				return new WP_Error( 'anwp_rest_error', 'Progress items are empty', [ 'status' => 400 ] );
			}

			$club_id = array_shift( $clubs );
		}

		$club_api_id = $this->api->helper->get_mapped_club_id( $club_id, false );

		if ( ! absint( $club_api_id ) ) {
			return new WP_Error( 'anwp_rest_error', 'Empty Mapping Club ID', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------
		| Prepare season data
		|--------------------------------------------------------------------
		*/
		$competition_obj = anwp_fl()->competition->get_competition( $competition_id );

		if ( empty( $competition_obj ) ) {
			return new WP_Error( 'anwp_rest_error', 'Incorrect Season', [ 'status' => 400 ] );
		}

		$season_api = empty( $params['season_api'] ) ? $this->api->helper->get_api_competition_by_local( $competition_id )['season_api'] : absint( $params['season_api'] );

		if ( empty( $season_api ) ) {
			return new WP_Error( 'anwp_rest_error', 'Incorrect Season API', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------
		| Get Saved Squad
		|--------------------------------------------------------------------
		*/
		$season_slug = 's:' . explode( ',', $competition_obj->season_ids )[0];
		$club_squad  = json_decode( get_post_meta( $club_id, '_anwpfl_squad', true ) );

		if ( ! $club_squad ) {
			$club_squad = (object) [];
		}

		$squad_players  = [];
		$club_squad_ids = [];

		if ( isset( $club_squad->{$season_slug} ) && 'yes' !== $this->api->config['reset_squad'] ) {
			$club_squad_ids = wp_list_pluck( $club_squad->{$season_slug}, 'id' );

			foreach ( $club_squad->{$season_slug} as $squad_player ) {
				$squad_players[ $squad_player->id ] = $squad_player;
			}
		}

		/*
		|--------------------------------------------------------------------
		| Get data from API
		|--------------------------------------------------------------------
		*/
		$updated_api_player_ids = [];

		$this->api->init_players_map();

		$query_args = [
			'page'   => 1,
			'season' => $season_api,
			'team'   => $club_api_id,
		];

		$response = $this->api->send_request_to_api( 'players', DAY_IN_SECONDS, $query_args );

		if ( ! empty( $response['errors'] ) ) {
			return new WP_Error( 'anwp_rest_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
		}

		if ( ! empty( $response['response'] ) && is_array( $response['response'] ) ) {
			$api_players = $response['response'];

			// Check pagination
			if ( ! empty( $response['paging']['total'] ) && $response['paging']['total'] > 1 ) {
				for ( $ii = 2; $ii <= $response['paging']['total']; $ii ++ ) {

					$query_args['page'] = $ii;
					$subpage_response   = $this->api->send_request_to_api( 'players', DAY_IN_SECONDS, $query_args );

					if ( ! empty( $subpage_response['response'] ) && is_array( $subpage_response['response'] ) ) {
						$api_players = array_merge( $api_players, $subpage_response['response'] );
					}
				}
			}

			foreach ( $api_players as $api_player ) {

				$load_player = false;

				foreach ( $api_player['statistics'] as $league_stats ) {
					if ( ! empty( $league_stats['games']['appearences'] ) ) {
						$load_player = true;
						break;
					}
				}

				if ( ! empty( $api_player['player'] ) && ! empty( $api_player['player']['id'] ) && $load_player ) {

					if ( $this->api->helper->update_player( $api_player, true, absint( $club_id ) ) ) {
						$updated_api_player_ids[] = absint( $api_player['player']['id'] );
					}

					if ( ! empty( $this->api->players_mapped[ $api_player['player']['id'] ] ) && ! empty( $this->api->players_mapped[ $api_player['player']['id'] ]->local_value ) ) {
						$local_player_id = absint( $this->api->players_mapped[ $api_player['player']['id'] ]->local_value );

						// Position
						if ( ! empty( $api_player['statistics'] ) && ! empty( $api_player['statistics'][0]['games'] ) && ! empty( $api_player['statistics'][0]['games']['position'] ) ) {

							$player_position = mb_strtolower( mb_substr( $api_player['statistics'][0]['games']['position'], 0, 1 ) );

							if ( 'a' === $player_position ) {
								$player_position = 'f';
							}
						}

						if ( in_array( $local_player_id, $club_squad_ids, true ) ) {
							if ( ! empty( $player_position ) && isset( $squad_players[ $local_player_id ] ) && empty( $squad_players[ $local_player_id ]->position ) ) {
								$squad_players[ $local_player_id ]->position = $player_position;
							}

							continue;
						}

						$squad_players[ $local_player_id ] = (object) [
							'id'       => $local_player_id,
							'position' => $player_position ?? '',
							'number'   => '',
							'status'   => '',
						];
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Get Squad API Data
		|--------------------------------------------------------------------
		*/
		if ( isset( $params['current_season'] ) && 'yes' === $params['current_season'] ) {

			$query_args = [
				'team' => $club_api_id,
			];

			$response = $this->api->send_request_to_api( 'players/squads', DAY_IN_SECONDS, $query_args );

			if ( ! empty( $response['errors'] ) ) {
				return new WP_Error( 'anwp_rest_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
			}

			if ( ! empty( $response['response'][0]['players'] ) && is_array( $response['response'][0]['players'] ) ) {

				foreach ( $response['response'][0]['players'] as $api_squad_player ) {
					if ( ! in_array( absint( $api_squad_player['id'] ), $updated_api_player_ids, true ) ) {
						$this->api->helper->update_player( [ 'player' => $api_squad_player ], true, absint( $club_id ) );
						$updated_api_player_ids[] = absint( $api_squad_player['id'] );
					}

					if ( ! empty( $this->api->players_mapped[ $api_squad_player['id'] ] ) && ! empty( $this->api->players_mapped[ $api_squad_player['id'] ]->local_value ) ) {
						$local_player_id = absint( $this->api->players_mapped[ $api_squad_player['id'] ]->local_value );

						// Position
						$player_position = mb_strtolower( mb_substr( $api_squad_player['position'], 0, 1 ) ) ? : '';

						if ( 'a' === $player_position ) {
							$player_position = 'f';
						}

						if ( empty( $player_position ) && isset( $squad_players[ $local_player_id ] ) && ! empty( $squad_players[ $local_player_id ]->position ) ) {
							$player_position = $squad_players[ $local_player_id ]->position;
						}

						// Number
						if ( isset( $squad_players[ $local_player_id ] ) && ! empty( $squad_players[ $local_player_id ]->number ) ) {
							$player_number = $squad_players[ $local_player_id ]->number;
						} else {
							$player_number = empty( $api_squad_player['number'] ) ? '' : $api_squad_player['number'];
						}

						$squad_players[ $local_player_id ] = (object) [
							'id'       => $local_player_id,
							'position' => $player_position ? : '',
							'number'   => $player_number,
							'status'   => '',
						];
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Save Club Squad
		|--------------------------------------------------------------------
		*/

		// Update club slug with new data
		$club_squad->{$season_slug} = array_values( $squad_players );

		// Save squad
		update_post_meta( $club_id, '_anwpfl_squad', wp_slash( wp_json_encode( $club_squad ) ) );

		/*
		|--------------------------------------------------------------------
		| Import Coach
		|--------------------------------------------------------------------
		*/
		if ( 'no' !== $this->api->config['coaches'] ) {
			$club_staff_squad = json_decode( get_post_meta( $club_id, '_anwpfl_staff', true ) );

			if ( ! $club_staff_squad ) {
				$club_staff_squad = (object) [];
			}

			if ( empty( $club_staff_squad->{$season_slug} ) ) {

				$updated_staff_squad = $this->api->helper->get_staff_squad_data( $club_api_id, explode( ',', $competition_obj->season_text )[0] );

				if ( $updated_staff_squad ) {
					$club_staff_squad->{$season_slug} = array_values( $updated_staff_squad );
					update_post_meta( $club_id, '_anwpfl_staff', wp_slash( wp_json_encode( $club_staff_squad ) ) );
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Import summary
		|-------------------------------------------------------------------
		*/

		$import['qty']    = 1;
		$import['result'] = true;

		$progress_sum           = $params['progress_sum'] ?? 0;
		$import['progress_sum'] = $progress_sum + $import['qty'];

		if ( ! empty( $params['progress_list_id'] ) ) {

			$import['progress_message'] = '';
		} else {
			$progress_total = $params['progress_total'] ?? 0;

			$import['progress_total'] = $progress_total;
			$import['progress_items'] = empty( $clubs ) ? [] : $clubs;

			$import['progress_message'] = 'Updating club players - ' . $import['progress_sum'] . '/' . $progress_total . ' ...';
		}

		return $import;
	}

	/**
	 * Update game predictions matches.
	 *
	 * @param $params
	 *
	 * @return array|WP_Error
	 * @since 0.13.0
	 */
	public function api_update_predictions( $params ) {
		global $wpdb;

		$competition_id = absint( $params['competition_id'] ?? '' );

		if ( ! $competition_id ) {
			return new WP_Error( 'anwp_rest_error', 'Invalid Competition ID', [ 'status' => 400 ] );
		}

		$import = [
			'result'  => false,
			'message' => '',
			'qty'     => 0,
		];

		$matches_updated = 0;

		// Get upcoming games
		$matches_upcoming = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT a.external_value, a.local_value
					FROM {$wpdb->prefix}anwpfl_import_mapping a
					LEFT JOIN {$wpdb->prefix}anwpfl_matches AS b ON b.match_id = a.local_value
					WHERE       a.provider = %s
					            AND a.type = 'match'
					            AND b.finished = 0
					            AND ( b.competition_id = %d OR b.main_stage_id = %d )
								AND b.kickoff < DATE_ADD( CURDATE(), INTERVAL 15 DAY)
					",
				$this->api->provider,
				$competition_id,
				$competition_id
			),
			OBJECT_K
		);

		if ( empty( $matches_upcoming ) ) {
			return new WP_Error( 'anwp_rest_warning', 'No Upcoming matches in the next 15 days', [ 'status' => 400 ] );
		}

		$placeholders = array_fill( 0, count( $matches_upcoming ), '%s' );
		$format       = implode( ', ', $placeholders );

		$saved_ids = $wpdb->get_col(
		// phpcs:disable WordPress.DB
			$wpdb->prepare(
				"
					SELECT match_id
					FROM {$wpdb->prefix}anwpfl_predictions
					WHERE match_id IN ({$format})
				",
				wp_list_pluck( $matches_upcoming, 'local_value' )
			)
		// phpcs:enable WordPress.DB
		);

		$saved_ids = array_map( 'absint', $saved_ids );

		/*
		|--------------------------------------------------------------------------
		| Get and save predictions
		|--------------------------------------------------------------------------
		*/
		foreach ( $matches_upcoming as $match_upcoming ) {
			$response = $this->api->send_request_to_api(
				'predictions',
				600,
				[
					'fixture' => absint( $match_upcoming->external_value ),
				]
			);

			if ( ! empty( $response['errors'] ) ) {
				return new WP_Error( 'anwp_rest_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
			}

			if ( empty( $response['response'] ) ) {
				return new WP_Error( 'anwp_rest_error', 'Empty API Data', [ 'status' => 400 ] );
			}

			$prediction_save_data = [];

			if ( ! empty( $response['response'][0]['predictions'] ) ) {
				$prediction_data = $response['response'][0]['predictions'];

				if ( ! empty( $prediction_data['advice'] ) && trim( $prediction_data['advice'] ) ) {
					$prediction_save_data['prediction_advice'] = $prediction_data['advice'];
				}
			}

			if ( 'advice_comparison' === $this->api->config['predictions_data'] ) {
				if ( ! empty( $response['response'][0]['predictions']['percent'] ) ) {
					$prediction_save_data['prediction_percent'] = wp_json_encode( $response['response'][0]['predictions']['percent'] ) ?: '';
				}

				if ( ! empty( $response['response'][0]['comparison'] ) ) {
					$prediction_save_data['prediction_comparison'] = wp_json_encode( $response['response'][0]['comparison'] ) ?: '';
				}

				do_action( 'anwpfl/api-import/save_prediction_advanced', $match_upcoming->local_value, $response );
			}

			if ( ! empty( $prediction_save_data ) ) {
				if ( in_array( absint( $match_upcoming->local_value ), $saved_ids, true ) ) {
					$wpdb->update(
						$wpdb->prefix . 'anwpfl_predictions',
						$prediction_save_data,
						[ 'match_id' => $match_upcoming->local_value ]
					);
				} else {
					$wpdb->insert(
						$wpdb->prefix . 'anwpfl_predictions',
						array_merge(
							[
								'match_id' => $match_upcoming->local_value,
							],
							$prediction_save_data
						)
					);
				}

				// Update counter
				$matches_updated ++;
			}
		}

		$import['qty'] = $matches_updated;

		// Remove error message if updated games exist
		if ( $matches_updated ) {
			$import['error']  = '';
			$import['result'] = true;
		}

		return $import;
	}

	/**
	 * Update competition transfers.
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	public function api_update_transfers( $params ) {

		$competition_id = isset( $params['competition_id'] ) ? absint( $params['competition_id'] ) : '';

		if ( ! $competition_id ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Invalid Initial Data', [ 'status' => 400 ] );
		}

		global $wpdb;

		$transfer_fee_strings = [
			'free' => AnWPFL_Text::get_value( 'api_import__transfers__free', '' ),
			'loan' => AnWPFL_Text::get_value( 'api_import__transfers__loan', '' ),
		];

		$import = [
			'result'  => false,
			'message' => '',
			'qty'     => 0,
		];

		$transfers_updated = 0;

		/*
		|--------------------------------------------------------------------------
		| Step 1 :: get competition data
		|--------------------------------------------------------------------------
		*/
		$competition_api = $this->api->helper->get_api_competition_by_local( $competition_id );

		// Return error on empty competition
		if ( ! $competition_api['competition_api_id'] ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Invalid Mapping Competition ID', [ 'status' => 400 ] );
		}

		// Get Leagues from API
		$response = $this->api->send_request_to_api(
			'leagues',
			DAY_IN_SECONDS,
			[
				'id'     => $competition_api['competition_api_id'],
				'season' => $competition_api['season_api'],
			]
		);

		if ( ! empty( $response['errors'] ) ) {
			return new WP_Error( 'rest_anwp_fl_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
		}

		if ( empty( $response['response'] ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Empty API Data', [ 'status' => 400 ] );
		}

		$league_data = $response['response'][0];

		if ( ! empty( $league_data['country']['name'] ) && 'World' === $league_data['country']['name'] ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Only National competitions are supported', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------------
		| Step 2 :: calculate windows dates
		|--------------------------------------------------------------------------
		*/
		if ( empty( $league_data['seasons'][0]['start'] ) || empty( $league_data['seasons'][0]['end'] ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'League Start or End is not set', [ 'status' => 400 ] );
		}

		$league_data['anwp_season_start_year'] = absint( mb_substr( $league_data['seasons'][0]['start'], 0, 4 ) );
		$league_data['anwp_season_end_year']   = absint( mb_substr( $league_data['seasons'][0]['end'], 0, 4 ) );

		if ( $league_data['anwp_season_end_year'] - $league_data['anwp_season_start_year'] > 1 ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Competition lasting for more than one year is not supported', [ 'status' => 400 ] );
		}

		if ( $league_data['anwp_season_end_year'] - $league_data['anwp_season_start_year'] < 0 ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Competition dates are incorrect', [ 'status' => 400 ] );
		}

		if ( $league_data['anwp_season_end_year'] > $league_data['anwp_season_start_year'] ) {
			$window_dates = [
				'pre_start'  => $league_data['anwp_season_start_year'] . '-04-01',
				'pre_finish' => $league_data['anwp_season_start_year'] . '-10-31',
				'mid_start'  => $league_data['anwp_season_start_year'] . '-11-01',
				'mid_finish' => $league_data['anwp_season_end_year'] . '-03-31',
			];
		} else {
			$window_dates = [
				'pre_start'  => $league_data['anwp_season_start_year'] - 1 . '-10-01',
				'pre_finish' => $league_data['anwp_season_start_year'] . '-05-31',
				'mid_start'  => $league_data['anwp_season_start_year'] . '-06-01',
				'mid_finish' => $league_data['anwp_season_start_year'] . '-09-30',
			];
		}

		/*
		|--------------------------------------------------------------------------
		| Step 3 :: get clubs transfers
		|--------------------------------------------------------------------------
		*/

		// Prepare data
		$this->api->init_players_map();
		$season_id = anwp_fl()->competition->tmpl_get_competition_terms( $competition_id )['season_id'][0];

		// Get clubs
		$clubs = anwp_fl()->competition->get_competition_multistage_clubs( $competition_id );

		foreach ( $clubs as $club_local_id ) {

			// Get API club ID
			$club_api_id = absint( $this->api->helper->get_mapped_club_id( $club_local_id, false ) );

			if ( ! absint( $club_api_id ) ) {
				continue;
			}

			$response = $this->api->send_request_to_api(
				'transfers',
				DAY_IN_SECONDS,
				[
					'team' => $club_api_id,
				]
			);

			if ( ! empty( $response['errors'] ) ) {
				return new WP_Error( 'rest_anwp_fl_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
			}

			if ( empty( $response['response'] ) ) {
				return new WP_Error( 'rest_anwp_fl_error', 'Empty API Data', [ 'status' => 400 ] );
			}

			$transfer_hash = 'club:' . $club_local_id . ';season:' . $season_id;

			// Remove old transfers
			$old_transfers = $wpdb->get_col(
				$wpdb->prepare(
					"
						SELECT transfer_id
						FROM $wpdb->anwpfl_transfers
						WHERE api_transfer_hash = %s
					",
					$transfer_hash
				)
			);

			if ( ! empty( $old_transfers ) && is_array( $old_transfers ) ) {
				foreach ( $old_transfers as $old_transfer_id ) {
					wp_delete_post( $old_transfer_id, true );
					$wpdb->delete( $wpdb->anwpfl_transfers, [ 'transfer_id' => $old_transfer_id ] );
				}
			}

			// Get all saved full hashes
			$saved_transfers_full_hashes = $wpdb->get_col(
				"
					SELECT api_transfer_hash_full
					FROM $wpdb->anwpfl_transfers
					WHERE api_transfer_hash_full != ''
				"
			);

			// Save Transfers
			foreach ( $response['response'] as $transfer_data ) {
				foreach ( $transfer_data['transfers'] as $transfer ) {

					/*
					|--------------------------------------------------------------------
					| Validate Transfer Date
					|--------------------------------------------------------------------
					*/
					if ( empty( $transfer['date'] ) ) {
						continue;
					}

					if ( 6 === absint( mb_strlen( $transfer['date'] ) ) ) {
						$transfer_temp_date = DateTime::createFromFormat( 'dmy', $transfer['date'] );

						$transfer['date'] = $transfer_temp_date->format( 'Y-m-d' );
						$transfer['date'] = anwp_fl()->helper->validate_date( $transfer['date'] ) ? $transfer['date'] : '';
					}

					if ( empty( $transfer['date'] ) || $transfer['date'] > $window_dates['mid_finish'] || $transfer['date'] < $window_dates['pre_start'] ) {
						continue;
					}

					/*
					|--------------------------------------------------------------------
					| Get Player ID
					|--------------------------------------------------------------------
					*/
					$player_id = $this->api->get_or_create_player( $transfer_data['player']['id'] ?? 0, $transfer_data['player']['name'] ?? '', absint( $transfer['teams']['in']['id'] ?? 0 ) );

					if ( ! absint( $player_id ) ) {
						continue;
					}

					/*
					|--------------------------------------------------------------------
					| Prepare Post data
					|--------------------------------------------------------------------
					*/
					$post_data = [
						'post_status' => 'publish',
						'post_type'   => 'anwp_transfer',
						'post_title'  => sanitize_text_field( $transfer_data['player']['name'] ),
					];

					$insert_data = [
						'player_id' => absint( $player_id ),
					];

					/*
					|--------------------------------------------------------------------
					| Transfer Hash
					|--------------------------------------------------------------------
					*/
					$full_transfer_hash = implode(
						'-',
						[
							$transfer_data['player']['id'],
							$transfer['date'],
							$transfer['teams']['in']['id'] ?? 0,
							$transfer['teams']['out']['id'] ?? 0,
						]
					);

					if ( in_array( $full_transfer_hash, $saved_transfers_full_hashes, true ) ) {
						continue;
					}

					$saved_transfers_full_hashes[]         = $full_transfer_hash;
					$insert_data['api_transfer_hash']      = $transfer_hash;
					$insert_data['api_transfer_hash_full'] = $full_transfer_hash;

					/*
					|--------------------------------------------------------------------
					| Clubs IN <> OUT
					|--------------------------------------------------------------------
					*/
					$insert_data['club_in'] = 0;

					if ( ! empty( $transfer['teams']['in']['id'] ) ) {
						if ( absint( $transfer['teams']['in']['id'] ) === $club_api_id ) {
							$insert_data['club_in'] = absint( $club_local_id );
						} else {
							$club_in = $this->api->helper->get_mapped_club_id( absint( $transfer['teams']['in']['id'] ) );

							if ( absint( $club_in ) ) {
								$insert_data['club_in'] = absint( $club_in );
							} elseif ( ! empty( $transfer['teams']['in']['name'] ) ) {
								$insert_data['club_in']      = 2;
								$insert_data['club_in_text'] = sanitize_text_field( $transfer['teams']['in']['name'] );
							}
						}
					}

					// Club OUT
					$insert_data['club_out'] = 0;

					if ( ! empty( $transfer['teams']['out']['id'] ) ) {
						if ( absint( $transfer['teams']['out']['id'] ) === $club_api_id ) {
							$insert_data['club_out'] = absint( $club_local_id );
						} else {
							$club_out = $this->api->helper->get_mapped_club_id( absint( $transfer['teams']['out']['id'] ) );

							if ( absint( $club_out ) ) {
								$insert_data['club_out'] = absint( $club_out );
							} elseif ( ! empty( $transfer['teams']['out']['name'] ) ) {
								$insert_data['club_out']      = 2;
								$insert_data['club_out_text'] = sanitize_text_field( $transfer['teams']['out']['name'] );
							}
						}
					}

					// Season ID
					$insert_data['season_id'] = absint( $season_id );

					// Fee
					if ( ! empty( $transfer['type'] ) && 'N/A' !== trim( $transfer['type'] ) ) {
						/*
						|--------------------------------------------------------------------
						| Transfers Translations: free & loan
						|--------------------------------------------------------------------
						*/
						$transfer_fee = sanitize_text_field( $transfer['type'] );

						if ( $transfer_fee && isset( $transfer_fee_strings[ mb_strtolower( $transfer_fee ) ] ) && $transfer_fee_strings[ mb_strtolower( $transfer_fee ) ] ) {
							$transfer_fee = $transfer_fee_strings[ mb_strtolower( $transfer_fee ) ];
						}

						$insert_data['fee'] = $transfer_fee;
					}

					// Transfer Date
					$insert_data['transfer_date'] = sanitize_text_field( $transfer['date'] );

					// Transfer Status
					$insert_data['transfer_status'] = 1;
					$insert_data['transfer_window'] = 0;

					// Transfer Window
					if ( $transfer['date'] < $window_dates['mid_start'] ) {
						$insert_data['transfer_window'] = 1;
					} elseif ( $transfer['date'] < $window_dates['mid_finish'] ) {
						$insert_data['transfer_window'] = 2;
					}

					$insert_id = wp_insert_post( $post_data );

					if ( absint( $insert_id ) ) {
						$wpdb->insert( $wpdb->anwpfl_transfers, array_merge( $insert_data, [ 'transfer_id' => $insert_id ] ) );
						$transfers_updated ++;
					}
				}
			}
		}

		$import['qty'] = $transfers_updated;

		// Remove error message if updated games exist
		if ( $transfers_updated ) {
			$import['error']  = '';
			$import['result'] = true;
		}

		return $import;
	}
}
