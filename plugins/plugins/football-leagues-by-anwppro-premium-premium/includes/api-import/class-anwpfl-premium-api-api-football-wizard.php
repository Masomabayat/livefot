<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AnWPFL_Premium_API_API_Football_Wizard {

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
	public function __construct( $api ) {
		$this->api = $api;
	}

	/**
	 * Get Wizard Available Leagues
	 *
	 * @return array|WP_Error
	 * @throws Exception
	 */
	public function get_wizard_leagues() {

		$wizard_data_local = apply_filters( 'anwpfl/wizard-data/data_local', false );

		if ( $wizard_data_local ) {
			$data_file_path = apply_filters( 'anwpfl/wizard-data/data_local_path', '' );

			if ( $data_file_path ) {
				$wizard_leagues_raw = parse_ini_file( $data_file_path . '\leagues.ini' );
			} else {
				return [
					'leagues' => [],
				];
			}

			$wizard_leagues = [];

			foreach ( $wizard_leagues_raw as $league_id => $league_data ) {
				$wizard_leagues[ mb_substr( $league_id, 2 ) ] = array_unique( array_map( 'absint', $league_data ) );
			}
		} elseif ( apply_filters( 'anwpfl/wizard-data/ignore_cache', false ) ) {
			$wizard_response = wp_remote_get( 'https://gitlab.com/api/v4/projects/37123257/repository/files/leagues.ini/raw?ref=main' );

			if ( 200 === wp_remote_retrieve_response_code( $wizard_response ) ) {
				$response = wp_remote_retrieve_body( $wizard_response );
			} else {
				return [
					'leagues' => [],
				];
			}

			$wizard_leagues_raw = parse_ini_string( $response );
			$wizard_leagues     = [];

			foreach ( $wizard_leagues_raw as $league_id => $league_data ) {
				$wizard_leagues[ mb_substr( $league_id, 2 ) ] = array_unique( array_map( 'absint', $league_data ) );
			}
		} else {

			$current_date     = new DateTime( current_time( 'mysql' ) );
			$unix_time        = $current_date->format( 'U' );
			$wizard_updated_u = absint( get_option( 'anwpfl_premium_api_wizard_updated_u' ) );

			if ( ( $wizard_updated_u + HOUR_IN_SECONDS * 3 ) > $unix_time ) {
				$wizard_leagues = get_option( 'anwpfl_premium_api_wizard_leagues' );

				if ( ! empty( $wizard_leagues ) ) {
					return [
						'leagues' => $wizard_leagues,
					];
				}
			}

			$wizard_response = wp_remote_get( 'https://gitlab.com/api/v4/projects/37123257/repository/files/leagues.ini/raw?ref=main' );

			if ( 200 === wp_remote_retrieve_response_code( $wizard_response ) ) {
				$response = wp_remote_retrieve_body( $wizard_response );
			} else {
				return [
					'leagues' => [],
				];
			}

			$wizard_leagues_raw = parse_ini_string( $response );

			$wizard_leagues   = [];
			$wizard_updated_u = $unix_time;

			foreach ( $wizard_leagues_raw as $league_id => $league_data ) {
				$wizard_leagues[ mb_substr( $league_id, 2 ) ] = array_unique( array_map( 'absint', $league_data ) );
			}

			// Cache results
			update_option( 'anwpfl_premium_api_wizard_leagues', $wizard_leagues, false );
			update_option( 'anwpfl_premium_api_wizard_updated_u', $wizard_updated_u, false );
		}

		return [
			'leagues' => $wizard_leagues,
		];
	}

	/**
	 * Get Wizard Totals
	 *
	 * @param $params
	 *
	 * @return mixed
	 * @since 0.14.6
	 */
	public function get_wizard_totals( $params ) {

		$params = wp_parse_args(
			$params,
			[
				'action' => '',
				'season' => '',
				'league' => '',
			]
		);

		if ( empty( $params['action'] ) || empty( $params['season'] || empty( $params['league'] ) ) ) {
			return new WP_Error( 'anwp_rest_error', 'Initial data error', [ 'status' => 400 ] );
		}

		$import = [
			'message' => '',
			'result'  => false,
		];

		/*
		|--------------------------------------------------------------------
		| Try to load Import file
		|--------------------------------------------------------------------
		*/
		$import_config = $this->get_import_config( $params['league'], $params['season'] );

		if ( ! $import_config ) {
			return new WP_Error( 'rest_anwp_fl_wizard_file_error', 'Import Configuration file is not available', [ 'status' => 400 ] );
		}

		if ( 'createCompetition' === $params['action'] ) {
			$import['progress_list'] = [
				[
					'id'    => 'create_teams',
					'title' => 'Create/Update Teams',
				],
				[
					'id'    => 'create_competitions',
					'title' => 'Create Competitions',
				],
			];
		} elseif ( 'updatePlayers' === $params['action'] ) {
			$api_club_ids = wp_parse_id_list( $import_config->teams );

			foreach ( $api_club_ids as $api_club_id ) {
				if ( $this->api->helper->get_mapped_club_id( $api_club_id ) ) {
					$import['progress_list'][] = [
						'id'    => $api_club_id,
						'title' => anwp_football_leagues()->club->get_club_title_by_id( $this->api->helper->get_mapped_club_id( $api_club_id ) ),
					];
				}
			}
		} elseif ( 'createGames' === $params['action'] ) {
			$new_games = $this->get_create_games_totals( $params );

			if ( ! empty( $new_games ) ) {
				$import['progress_list'] = $new_games;
			}
		} elseif ( 'updateGames' === $params['action'] ) {
			$update_games = $this->get_update_games_totals( $params );

			if ( ! empty( $update_games ) ) {
				$import['progress_list'] = $update_games;
			}
		} elseif ( 'updateStanding' === $params['action'] ) {
			$import['progress_list'] = [
				[
					'id'    => 'create_standings',
					'title' => 'Create Standings',
				],
			];
		} else {
			$import['progress_list'] = [];
		}

		$import['result'] = true;

		return $import;
	}

	/**
	 * Update wizard loop
	 *
	 * @param $params
	 *
	 * @return mixed
	 * @since 0.14.6
	 */
	public function update_wizard_loop( $params ) {

		$params = wp_parse_args(
			$params,
			[
				'action'       => '',
				'season'       => '',
				'league'       => '',
				'league_obj'   => '',
				'progress_id'  => '',
				'progress_sum' => '',
			]
		);

		if ( empty( $params['action'] ) || empty( $params['season'] || empty( $params['league'] ) || empty( $params['progress_id'] ) ) ) {
			return new WP_Error( 'anwp_rest_error', 'Initial data error', [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------
		| Try to load Import file
		|--------------------------------------------------------------------
		*/
		$import_config = $this->get_import_config( $params['league'], $params['season'] );

		if ( ! $import_config ) {
			return new WP_Error( 'rest_anwp_fl_wizard_file_error', 'Import Configuration file is not available', [ 'status' => 400 ] );
		}

		$import = [
			'message' => '',
			'qty'     => 0,
		];

		/*
		|--------------------------------------------------------------------
		| Create Competition
		|--------------------------------------------------------------------
		*/
		if ( 'createCompetition' === $params['action'] ) {
			if ( 'create_competitions' === $params['progress_id'] ) {
				// Create Competitions
				$this->create_competitions( $params, $import_config );

				// Fetch saved configs
				$import['saved_configs']        = $this->api->get_data_saved_configs();
				$import['local_competition_id'] = $this->get_competition_by_old_id( $import_config->competition_id );

				$competition_obj = anwp_football_leagues()->competition->get_competition( $import['local_competition_id'] );

				if ( $competition_obj ) {
					$import['local_logo']   = $competition_obj->logo;
					$import['local_title']  = $competition_obj->title;
					$import['local_season'] = $competition_obj->season_text;
					$import['local_edit']   = admin_url( '/post.php?post=' . absint( $import_config->competition_id ) . '&action=edit' );
					$import['local_status'] = AnWP_Football_Leagues::string_to_bool( $params['league_obj']['current'] ) ? 'true' : 'false';
				}
			} elseif ( 'create_teams' === $params['progress_id'] && ! empty( $import_config->teams ) ) {
				$api_club_ids  = wp_parse_id_list( $import_config->teams );
				$api_new_clubs = [];

				foreach ( $api_club_ids as $api_club_id ) {
					if ( ! $this->api->helper->get_mapped_club_id( $api_club_id ) ) {
						$api_new_clubs[] = $api_club_id;
					}
				}

				if ( ! empty( $api_new_clubs ) ) {
					$this->api->update_clubs_data( $api_new_clubs, $params['league'], $params['season'] );

					//ToDO check API limit

					// trigger force update
					$this->api->helper->get_mapped_club_id( 0, true, true );
				}
			}
		} elseif ( 'createGames' === $params['action'] ) {
			$this->create_new_games( $params );
		} elseif ( 'updateStanding' === $params['action'] ) {
			$this->create_standings( $params, $import_config );
		} elseif ( 'updatePlayers' === $params['action'] ) {
			$this->update_players( $params );
		} elseif ( 'updateGames' === $params['action'] ) {
			$this->update_games( $params );
		}

		$import['qty'] ++;

		$import['result']       = true;
		$import['progress_sum'] = $params['progress_sum'] + $import['qty'];

		return $import;
	}

	/**
	 * Get Import Configuration file
	 * @param $league_id
	 * @param $season_id
	 *
	 * @return false|mixed
	 */
	private function get_import_config( $league_id, $season_id ) {

		if ( ! absint( $league_id ) || ! absint( $season_id ) ) {
			return false;
		}

		$wizard_data_local = apply_filters( 'anwpfl/wizard-data/data_local', false );

		if ( $wizard_data_local ) {
			$data_file_path = apply_filters( 'anwpfl/wizard-data/data_local_path', '' );

			if ( $data_file_path ) {
				$response = json_decode( file_get_contents( $data_file_path . '\\' . $league_id . '-' . $season_id . '.json' ) ); // phpcs:ignore
			}
		} elseif ( apply_filters( 'anwpfl/wizard-data/ignore_cache', false ) ) {

			$wizard_response = wp_remote_get( 'https://gitlab.com/api/v4/projects/37123257/repository/files/' . $league_id . '-' . $season_id . '.json/raw?ref=main' );

			if ( 200 === wp_remote_retrieve_response_code( $wizard_response ) ) {
				$response = json_decode( wp_remote_retrieve_body( $wizard_response ) );
			} else {
				return false;
			}
		} else {
			$serialized_data = [
				'provider'  => $this->api->provider,
				'league_id' => $league_id,
				'season_id' => $season_id,
			];

			// Get transient cache key
			$cache_key = 'ANWPFL-API-IMPORT-WIZARD-' . md5( maybe_serialize( $serialized_data ) );

			// Try to get saved transient
			$response = get_transient( $cache_key );

			if ( ! empty( $response ) ) {
				return $response;
			}

			$wizard_response = wp_remote_get( 'https://gitlab.com/api/v4/projects/37123257/repository/files/' . $league_id . '-' . $season_id . '.json/raw?ref=main' );

			if ( 200 === wp_remote_retrieve_response_code( $wizard_response ) ) {
				$response = json_decode( wp_remote_retrieve_body( $wizard_response ) );
			} else {
				return false;
			}

			if ( ! empty( $response ) && $cache_key ) {
				set_transient( $cache_key, $response, HOUR_IN_SECONDS );
			}
		}

		return empty( $response ) ? false : $response;
	}

	/**
	 * Create new API Games
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	private function get_create_games_totals( $params ) {

		$league_obj = wp_parse_args(
			$params['league_obj'] ?? [],
			[
				'year'        => '',
				'start'       => '',
				'end'         => '',
				'id'          => '',
				'local_value' => '',
			]
		);

		global $wpdb;

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
			return [];
		}

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
		// phpcs:disable
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
		// phpcs:enable
		);

		/*
		|--------------------------------------------------------------------------
		| Step 3 :: create new (missed) matches
		|--------------------------------------------------------------------------
		*/
		$output      = [];
		$loop_ids    = [];
		$loop_titles = [];
		$step_limit  = 5;

		foreach ( $api_data as $game ) {

			if ( isset( $game['fixture']['id'] ) && ! in_array( (string) $game['fixture']['id'], $matches_mapped, true ) ) {

				if ( -- $step_limit < 0 ) {
					$step_limit = 4;

					$output[] = [
						'id'    => implode( ',', $loop_ids ),
						'title' => implode( '; ', $loop_titles ),
					];

					$loop_ids    = [];
					$loop_titles = [];
				}

				$loop_ids[]    = $game['fixture']['id'];
				$loop_titles[] = $game['teams']['home']['name'] . ' - ' . $game['teams']['away']['name'];
			}
		}

		if ( ! empty( $loop_ids ) ) {
			$output[] = [
				'id'    => implode( ',', $loop_ids ),
				'title' => implode( '; ', $loop_titles ),
			];
		}

		return $output;
	}

	/**
	 * Update missed matches from API.
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	private function get_update_games_totals( $params ) {

		global $wpdb;

		$competition_id = isset( $params['league_obj']['local_value'] ) ? absint( $params['league_obj']['local_value'] ) : '';

		if ( ! $competition_id ) {
			return false;
		}

		/*
		|--------------------------------------------------------------------------
		| Step 1 :: get all finished matches from API
		|--------------------------------------------------------------------------
		*/
		$competition_api = $this->api->helper->get_api_competition_by_local( $competition_id );

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
		$output      = [];
		$loop_ids    = [];
		$loop_titles = [];
		$step_limit  = 3;

		foreach ( $matches_to_update as $match_external => $match_local ) {

			if ( -- $step_limit < 0 ) {
				$step_limit = 2;

				$output[] = [
					'id'    => implode( ',', $loop_ids ),
					'title' => 'Game Ids: ' . implode( '; ', $loop_titles ),
				];

				$loop_ids    = [];
				$loop_titles = [];
			}

			$loop_ids[]    = $match_external . '-' . $match_local;
			$loop_titles[] = $match_local;
		}

		if ( ! empty( $loop_ids ) ) {
			$output[] = [
				'id'    => implode( ',', $loop_ids ),
				'title' => 'Game Ids: ' . implode( '; ', $loop_titles ),
			];
		}

		$output[] = [
			'id'    => 'recalculate_standings',
			'ids'   => $matches_to_update,
			'title' => 'Recalculate Standings',
		];

		return $output;
	}

	/**
	 * Get Competition Id by its old Id
	 *
	 * @return string/null
	 */
	private function get_competition_by_old_id( $old_id ) {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
					SELECT post_id
					FROM $wpdb->postmeta
					WHERE meta_key = '_anwpfl_api_wizard_competition_id' AND meta_value = %s
					",
				$old_id
			)
		);
	}

	/**
	 * Create Wizard Competitions
	 *
	 * @return array|bool
	 */
	private function create_competitions( $params, $import_config ) {

		foreach ( $import_config->competitions as $competition_config ) {

			if ( $this->get_competition_by_old_id( $competition_config->competition_id ) ) {
				continue;
			}

			/*
			|--------------------------------------------------------------------
			| Get Season Term Id
			|--------------------------------------------------------------------
			*/
			$season_text    = $import_config->season[0];
			$season_term_id = get_term_by( 'slug', $season_text, 'anwp_season' ) ? get_term_by( 'slug', $season_text, 'anwp_season' )->term_id : '';

			if ( empty( $season_term_id ) ) {
				$insert_result = wp_insert_term(
					$season_text,
					'anwp_season'
				);

				if ( ! empty( $insert_result ) && ! is_wp_error( $insert_result ) && ! empty( $insert_result['term_id'] ) ) {
					$season_term_id = absint( $insert_result['term_id'] );
				}
			}

			if ( empty( $season_term_id ) ) {
				return false;
			}

			/*
			|--------------------------------------------------------------------
			| Get League Term Id
			|--------------------------------------------------------------------
			*/
			global $wpdb;
			$league_term_id = '';

			$other_id = $wpdb->get_var(
				$wpdb->prepare(
					"
						SELECT local_value
						FROM {$wpdb->prefix}anwpfl_import_mapping
						WHERE       external_value = %s
						            AND type = 'competition-v3'
						",
					$params['league']
				)
			);

			if ( ! empty( $other_id ) ) {
				$league_term_id = anwp_football_leagues()->competition->get_competition( $other_id )->league_id;
			}

			if ( empty( $league_term_id ) ) {

				$maybe_slug = sanitize_title( $import_config->league_local->name . ' ' . $import_config->league_local->country );
				$query      = $wpdb->prepare( "SELECT slug FROM $wpdb->terms WHERE slug = %s", $maybe_slug );

				if ( $wpdb->get_var( $query ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$num = 2;
					do {
						$alt_slug = $maybe_slug . "-$num";
						$num ++;
						$slug_check = $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM $wpdb->terms WHERE slug = %s", $alt_slug ) );
					} while ( $slug_check );
					$maybe_slug = $alt_slug;
				}

				$insert_result = wp_insert_term(
					$import_config->league_local->name,
					'anwp_league',
					[
						'slug' => $maybe_slug,
					]
				);

				if ( ! empty( $insert_result ) && ! is_wp_error( $insert_result ) && ! empty( $insert_result['term_id'] ) ) {
					$league_term_id = absint( $insert_result['term_id'] );

					if ( ! empty( $import_config->league_local->country ) ) {
						update_term_meta( $league_term_id, '_anwpfl_country', $import_config->league_local->country );
					}
				}
			}

			if ( empty( $league_term_id ) ) {
				return false;
			}

			/*
			|--------------------------------------------------------------------
			| Create Competition
			|--------------------------------------------------------------------
			*/
			$competition_local_id = wp_insert_post(
				[
					'post_title'  => $import_config->league_local->name . ' ' . $import_config->season[0],
					'post_status' => 'secondary' === $competition_config->_anwpfl_multistage ? 'stage_secondary' : 'publish',
					'post_type'   => 'anwp_competition',
					'meta_input'  => [
						'_anwpfl_api_wizard_competition_id' => $competition_config->competition_id,
					],
				]
			);

			if ( empty( absint( $competition_local_id ) ) ) {
				return false;
			}

			wp_set_object_terms( $competition_local_id, $league_term_id, 'anwp_league' );
			wp_set_object_terms( $competition_local_id, [ $season_term_id ], 'anwp_season' );

			$competition_fields_to_save = [
				'_anwpfl_rounds',
				'_anwpfl_type',
				'_anwpfl_format_robin',
				'_anwpfl_format_knockout',
				'_anwpfl_multistage',
				'_anwpfl_stage_title',
				'_anwpfl_stage_order',
				'_anwpfl_group_next_id',
				'_anwpfl_round_next_id',
				'_anwpfl_competition_order',
				'_anwpfl_bracket',
				'_anwpfl_bracket_options',
			];

			foreach ( $competition_fields_to_save as $competition_field ) {
				if ( isset( $competition_config->{$competition_field} ) ) {
					update_post_meta( $competition_local_id, $competition_field, $competition_config->{$competition_field}, true );
				}
			}

			if ( absint( $competition_config->_anwpfl_multistage_main ) ) {
				$old_main_competitions = $this->get_competition_by_old_id( $competition_config->_anwpfl_multistage_main );

				if ( absint( $old_main_competitions ) ) {
					update_post_meta( $competition_local_id, '_anwpfl_multistage_main', $old_main_competitions );
				}
			}

			if ( ! empty( $competition_config->_anwpfl_groups ) ) {
				foreach ( $competition_config->_anwpfl_groups as $competition_group ) {

					$team_ids_local = [];

					foreach ( $competition_group->clubs as $group_team_id ) {
						$team_id_local = $this->api->helper->get_mapped_club_id( $group_team_id );

						if ( absint( $team_id_local ) ) {
							$team_ids_local[] = absint( $team_id_local );
						}
					}

					$competition_group->clubs = $team_ids_local;
				}

				update_post_meta( $competition_local_id, '_anwpfl_groups', wp_slash( wp_json_encode( $competition_config->_anwpfl_groups ) ) );
			}

			if ( 'secondary' !== $competition_config->_anwpfl_multistage ) {
				// Setup table name
				$mapping_table = $wpdb->prefix . 'anwpfl_import_mapping';

				$extra_data = wp_json_encode(
					(object) [
						'season' => $params['league_obj']['year'],
						'start'  => $params['league_obj']['start'],
						'end'    => $params['league_obj']['end'],
					]
				);

				// Update saved config mapping
				$data = [
					'provider'       => 'api-football',
					'type'           => 'config-saved-v3',
					'local_value'    => $competition_local_id,
					'external_value' => $params['league'],
					'status'         => AnWP_Football_Leagues::string_to_bool( $params['league_obj']['current'] ) ? 'true' : 'false',
					'extra_data'     => $extra_data,
				];

				$wpdb->replace( $mapping_table, $data );

				// Update Competition mapping
				$data['status'] = '';
				$data['type']   = 'competition-v3';

				$wpdb->replace( $mapping_table, $data );

				// Update Logo
				$this->api->updater->api_update_competition_logo(
					[
						'competition_id' => $competition_local_id,
						'logo'           => $params['league_obj']['logo'],
					]
				);
			}
		}

		/*
		|--------------------------------------------------------------------
		| Save mapped data
		|--------------------------------------------------------------------
		*/
		if ( isset( $import_config->api_rounds ) ) {
			$this->api->helper->update_api_rounds( $import_config->api_rounds, $params['league'], $this->get_competition_by_old_id( $import_config->competition_id ) );
		}

		if ( isset( $import_config->api_mapped_rounds ) ) {
			$mapped_rounds = [];

			foreach ( $import_config->api_mapped_rounds as $mapped_index => $mapped_round ) {
				$m_competition = explode( '-', $mapped_index )[0];
				$m_round       = isset( explode( '-', $mapped_index )[1] ) ? explode( '-', $mapped_index )[1] : 1;

				$mapped_rounds[ $this->get_competition_by_old_id( $m_competition ) . '-' . $m_round ] = $mapped_round;
			}

			$this->api->helper->update_api_mapped_rounds( (object) $mapped_rounds, $params['league'], $this->get_competition_by_old_id( $import_config->competition_id ) );
		}

		// Reset Competition Cache
		if ( class_exists( 'AnWPFL_Cache' ) ) {
			anwp_football_leagues()->cache->delete( 'FL-COMPETITIONS-LIST' );
		}

		return true;
	}

	/**
	 * Create new API Games
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	private function create_new_games( $params ) {

		$league_obj = wp_parse_args(
			$params['league_obj'] ?? [],
			[
				'year'        => '',
				'start'       => '',
				'end'         => '',
				'id'          => '',
				'local_value' => '',
			]
		);

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

		$api_data      = [];
		$ids_to_create = wp_parse_id_list( $params['progress_id'] );

		foreach ( $response['response'] as $api_match ) {

			if ( in_array( absint( $api_match['fixture']['id'] ), $ids_to_create, true ) ) {
				$api_data[] = $api_match;
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Create new (missed) matches
		|--------------------------------------------------------------------------
		*/
		$mapped_rounds = $this->api->helper->get_api_mapped_rounds( $league_obj['id'], $league_obj['local_value'] );

		foreach ( $api_data as $game ) {
			$this->api->helper->create_match( $league_obj['local_value'], $game, $mapped_rounds );
		}

		return true;
	}

	/**
	 * Create Wizard Standing
	 *
	 * @return array|bool
	 */
	private function create_standings( $params, $import_config ) {
		foreach ( $import_config->standings as $standing_config ) {
			/*
			|--------------------------------------------------------------------
			| Create Standing
			|--------------------------------------------------------------------
			*/
			$standing_local_id = wp_insert_post(
				[
					'post_title'  => empty( $standing_config->title ) ? ( $import_config->league_local->name . ' ' . $import_config->season[0] ) : $standing_config->title,
					'post_status' => 'publish',
					'post_type'   => 'anwp_standing',
					'meta_input'  => [
						'_anwpfl_competition'            => $this->get_competition_by_old_id( $standing_config->_anwpfl_competition ),
						'_anwpfl_competition_group'      => $standing_config->_anwpfl_competition_group,
						'_anwpfl_table_notes'            => isset( $standing_config->_anwpfl_table_notes ) ? $standing_config->_anwpfl_table_notes : '',
						'_anwpfl_table_colors'           => isset( $standing_config->_anwpfl_table_colors ) ? $standing_config->_anwpfl_table_colors : '',
						'_anwpfl_ranking_rules_current'  => $standing_config->_anwpfl_ranking_rules_current,
						'_anwpfl_fixed'                  => 'true',
						'_anwpfl_points_win'             => '3',
						'_anwpfl_points_draw'            => '1',
						'_anwpfl_points_loss'            => '0',
						'_anwpfl_manual_ordering'        => 'false',
						'_anwpfl_is_initial_data_active' => '0',
						'_anwpfl_manual_filling'         => 'false',
						'_anwpfl_conferences_support'    => 'false',
						'_anwpfl_columns_order'          => '[{"display":true,"slug":"played"},{"display":true,"slug":"won"},{"display":true,"slug":"drawn"},{"display":true,"slug":"lost"},{"display":true,"slug":"gf"},{"display":true,"slug":"ga"},{"display":true,"slug":"gd"},{"display":true,"slug":"points"}]',
						'_anwpfl_columns_order_sm'       => '[{"display":true,"slug":"played"},{"display":false,"slug":"won"},{"display":false,"slug":"drawn"},{"display":false,"slug":"lost"},{"display":false,"slug":"gf"},{"display":false,"slug":"ga"},{"display":true,"slug":"gd"},{"display":true,"slug":"points"}]',
						'_anwpfl_columns_order_xs'       => '[{"display":true,"slug":"played"},{"display":false,"slug":"won"},{"display":false,"slug":"drawn"},{"display":false,"slug":"lost"},{"display":false,"slug":"gf"},{"display":false,"slug":"ga"},{"display":true,"slug":"gd"},{"display":true,"slug":"points"}]',
						'_anwpfl_columns_mini_order'     => '[{"display":true,"slug":"played"},{"display":true,"slug":"won"},{"display":true,"slug":"drawn"},{"display":true,"slug":"lost"},{"display":true,"slug":"points"}]',
						'_anwpfl_columns_mini_order_sm'  => '[{"display":true,"slug":"played"},{"display":false,"slug":"won"},{"display":false,"slug":"drawn"},{"display":false,"slug":"lost"},{"display":true,"slug":"points"}]',
					],
				]
			);

			if ( empty( absint( $standing_local_id ) ) ) {
				return false;
			}

			/*
			|--------------------------------------------------------------------
			| Save Initial points
			|--------------------------------------------------------------------
			*/
			if ( ! empty( $standing_config->_anwpfl_points_initial ) ) {

				$initial_points_raw = json_decode( $standing_config->_anwpfl_points_initial );
				$initial_points     = [];

				if ( ! empty( $initial_points_raw ) ) {
					foreach ( $initial_points_raw as $team_api_id => $points ) {

						$local_team_id = $this->api->helper->get_mapped_club_id( $team_api_id );

						if ( absint( $local_team_id ) ) {
							$initial_points[ absint( $local_team_id ) ] = $points;
						}
					}

					update_post_meta( $standing_local_id, '_anwpfl_points_initial', wp_json_encode( (object) $initial_points ) );
				}
			}

			/*
			|--------------------------------------------------------------------
			| Save Initial table
			|--------------------------------------------------------------------
			*/
			if ( ! empty( $standing_config->_anwpfl_is_initial_data_active ) && '1' === $standing_config->_anwpfl_is_initial_data_active ) {

				$initial_table_raw = json_decode( $standing_config->_anwpfl_table_initial );
				$initial_table     = [];

				if ( ! empty( $initial_table_raw ) ) {
					foreach ( $initial_table_raw as $team_api_id => $table_data ) {

						$local_team_id = $this->api->helper->get_mapped_club_id( $team_api_id );

						if ( absint( $local_team_id ) ) {
							$initial_table[ absint( $local_team_id ) ] = $table_data;
						}
					}

					update_post_meta( $standing_local_id, '_anwpfl_table_initial', wp_json_encode( (object) $initial_table ) );
					update_post_meta( $standing_local_id, '_anwpfl_is_initial_data_active', '1' );
				}
			}

			/*
			|--------------------------------------------------------------------
			| Conferences Support
			|--------------------------------------------------------------------
			*/
			if ( ! empty( $standing_config->_anwpfl_conferences_support ) && 'true' === $standing_config->_anwpfl_conferences_support && ! empty( $standing_config->_anwpfl_club_conferences ) ) {

				update_post_meta( $standing_local_id, '_anwpfl_conferences_support', 'true' );

				$conferences_raw = json_decode( $standing_config->_anwpfl_club_conferences );
				$conferences     = [];

				if ( ! empty( $conferences_raw ) ) {
					foreach ( $conferences_raw as $team_api_id => $conference ) {

						$local_team_id = $this->api->helper->get_mapped_club_id( $team_api_id );

						if ( absint( $local_team_id ) ) {
							$conferences[ absint( $local_team_id ) ] = $conference;
						}
					}

					update_post_meta( $standing_local_id, '_anwpfl_club_conferences', wp_json_encode( (object) $conferences ) );
				}
			}
		}

		return true;
	}

	/**
	 * Update club squad.
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	private function update_players( $params ) {

		$competition_id = $params['league_obj']['local_value'];
		$club_api_id    = absint( $params['progress_id'] );
		$club_id        = $this->api->helper->get_mapped_club_id( $club_api_id, true );

		if ( ! absint( $club_api_id ) || ! absint( $club_id ) || ! absint( $competition_id ) ) {
			return false;
		}

		/*
		|--------------------------------------------------------------------
		| Prepare season data
		|--------------------------------------------------------------------
		*/
		$competition_obj = anwp_football_leagues()->competition->get_competition( $competition_id );

		if ( empty( $competition_obj ) ) {
			return false;
		}

		$season_api = absint( $params['season'] );

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

		if ( isset( $club_squad->{$season_slug} ) ) {
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
				if ( ! empty( $api_player['player'] ) && ! empty( $api_player['player']['id'] ) ) {

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
		if ( isset( $params['league_obj']['current'] ) && 'yes' === $params['league_obj']['current'] ) {

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

		return true;
	}

	/**
	 * Update missed matches from API.
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	private function update_games( $params ) {

		if ( 'recalculate_standings' === $params['progress_id'] ) {
			anwp_football_leagues_premium()->standing->calculate_competition_standings_by_games( $params['league_obj']['local_value'], $params['progress_item']['ids'] );
			anwp_football_leagues_premium()->competition->update_current_matchweek( $params['league_obj']['local_value'] );
		} else {

			$game_complex_ids = explode( ',', $params['progress_id'] );

			foreach ( $game_complex_ids as $game_ids ) {

				$game_api_id   = explode( '-', $game_ids )[0];
				$game_local_id = explode( '-', $game_ids )[1];

				if ( $this->api->helper->update_match( $game_api_id, $game_local_id ) ) {
					$this->api->insert_mapped_link( 'match', $game_local_id, $game_api_id, 'result' );
				}
			}
		}

		return true;
	}
}
