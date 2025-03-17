<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AnWPFL_Premium_API_API_Football_Helper {

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
	 * @param $club_api_id
	 * @param $season_text
	 *
	 * @return array
	 */
	public function get_staff_squad_data( $club_api_id, $season_text ): array {

		$response = $this->api->send_request_to_api(
			'coachs',
			DAY_IN_SECONDS,
			[
				'team' => $club_api_id,
			]
		);

		$season_arr = explode( '-', $season_text );
		$check_date = 2 === count( $season_arr ) ? ( $season_arr[0] . '-10-01' ) : ( $season_arr[0] . '-05-01' );
		$output     = [];

		if ( ! empty( $response['errors'] ) || empty( $response['response'] ) ) {
			return [];
		}

		foreach ( $response['response'] as $api_coach ) {

			if ( empty( $api_coach['career'] ) || ! is_array( $api_coach['career'] ) ) {
				continue;
			}

			$this->update_coach( $api_coach['id'], $api_coach, $club_api_id );

			foreach ( $api_coach['career'] as $career_team ) {
				if ( absint( $career_team['team']['id'] ) === absint( $club_api_id ) && $career_team['start'] < $check_date && ( ! $career_team['end'] || $career_team['end'] > $check_date ) ) {

					$coach_id = $this->api->get_or_create_coach( $api_coach['id'], '' );

					if ( $coach_id ) {
						$output[] = [
							'id'       => $coach_id,
							'job'      => AnWPFL_Text::get_value( 'api_import__coach', 'Coach' ),
							'grouping' => 'no',
							'group'    => '',
						];
					}
				}
			}
		}

		return $output;
	}

	/**
	 * Get mapped team id by API team id or vice versa.
	 *
	 * @param int  $input_id
	 * @param bool $get_local - Get Local team ID on true or team API ID on false
	 * @param bool $force_update
	 *
	 * @return string
	 * @since 0.13.0
	 */
	public function get_mapped_club_id( $input_id, $get_local = true, $force_update = false ) {

		static $options = null;

		$output_id = '';

		if ( null === $options || $force_update ) {
			global $wpdb;

			$items = $wpdb->get_results(
				$wpdb->prepare(
					"
						SELECT external_value, local_value
						FROM {$wpdb->prefix}anwpfl_import_mapping
						WHERE       provider = %s
						            AND type = 'club'
						",
					$this->api->provider
				)
			);

			if ( ! empty( $items ) && is_array( $items ) ) {
				$options = [];

				foreach ( $items as $item ) {
					$options[ $item->external_value ] = $item->local_value;
				}
			}
		}

		if ( $get_local ) {
			if ( ! empty( $options[ $input_id ] ) ) {
				return $options[ $input_id ];
			}
		} else {
			$flipped = array_flip( $options );

			if ( ! empty( $flipped[ $input_id ] ) ) {
				return $flipped[ $input_id ];
			}
		}

		return $output_id;
	}

	/**
	 * Get API mapped rounds.
	 *
	 * @param $api_id
	 * @param $local_id
	 *
	 * @return object
	 * @since 0.13.0
	 */
	public function get_api_mapped_rounds( $api_id, $local_id ) {

		global $wpdb;

		$mapped_data = $wpdb->get_var(
			$wpdb->prepare(
				"
						SELECT extra_data
						FROM {$wpdb->prefix}anwpfl_import_mapping
						WHERE       provider = %s
						            AND type = 'api_mapped_rounds'
						            AND local_value = %d
						            AND external_value = %d
					",
				$this->api->provider,
				$local_id,
				$api_id
			)
		);

		if ( empty( $mapped_data ) ) {
			return (object) [];
		}

		return json_decode( $mapped_data ) ?: (object) [];
	}

	/**
	 * Get API mapped rounds.
	 *
	 * @param $api_id
	 * @param $local_id
	 *
	 * @return object
	 * @since 0.14.6
	 */
	public function get_api_rounds( $api_id, $local_id ) {

		global $wpdb;

		$mapped_data = $wpdb->get_var(
			$wpdb->prepare(
				"
						SELECT extra_data
						FROM {$wpdb->prefix}anwpfl_import_mapping
						WHERE       provider = %s
						            AND type = 'api_rounds'
						            AND local_value = %d
						            AND external_value = %d
					",
				$this->api->provider,
				$local_id,
				$api_id
			)
		);

		return json_decode( $mapped_data ) ?: (object) [];
	}

	/**
	 * Remove missed records from mapping table.
	 * Check mapping table for non-exising local game ids and remove them.
	 *
	 * @param $matches_api_ids
	 *
	 * @since 0.13.0
	 */
	public function remove_missed_matches( $matches_api_ids ) {

		global $wpdb;

		$placeholders = array_fill( 0, count( $matches_api_ids ), '%s' );
		$format       = implode( ', ', $placeholders );

		$matches_missed = $wpdb->get_col(
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"
					SELECT a.local_value
					FROM {$wpdb->prefix}anwpfl_import_mapping a
					LEFT JOIN {$wpdb->posts} AS b ON b.ID = a.local_value
					WHERE       a.provider = %s
					            AND a.type = 'match'
					            AND b.ID IS NULL
					            AND a.external_value IN ({$format})
					",
				array_merge( [ $this->api->provider ], $matches_api_ids )
			)
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		if ( ! empty( $matches_missed ) && is_array( $matches_missed ) && count( $matches_missed ) ) {
			foreach ( $matches_missed as $missed_match ) {
				try {
					$wpdb->delete(
						$wpdb->prefix . 'anwpfl_import_mapping',
						[
							'local_value' => $missed_match,
							'type'        => 'match',
							'provider'    => $this->api->provider,
						]
					);
				} catch ( Exception $e ) {
					continue;
				}
			}
		}
	}

	/**
	 * Update API rounds.
	 *
	 * @param $api_rounds
	 * @param $api_id
	 * @param $local_id
	 *
	 * @return false|int
	 * @since 0.13.0
	 */
	public function update_api_rounds( $api_rounds, $api_id, $local_id ) {

		if ( ! is_array( $api_rounds ) ) {
			return false;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'anwpfl_import_mapping';

		// Prepare data to insert
		$data = [
			'provider'       => $this->api->provider,
			'type'           => 'api_rounds',
			'local_value'    => absint( $local_id ),
			'external_value' => absint( $api_id ),
			'extra_data'     => wp_json_encode( $api_rounds ),
		];

		return $wpdb->replace( $table, $data );
	}

	/**
	 * Update API mapped rounds.
	 *
	 * @param $api_mapped_rounds
	 * @param $api_id
	 * @param $local_id
	 *
	 * @return false|int
	 * @since 0.13.0
	 */
	public function update_api_mapped_rounds( $api_mapped_rounds, $api_id, $local_id ) {

		global $wpdb;

		$table = $wpdb->prefix . 'anwpfl_import_mapping';

		// Prepare data to insert
		$data = [
			'provider'       => $this->api->provider,
			'type'           => 'api_mapped_rounds',
			'local_value'    => absint( $local_id ),
			'external_value' => absint( $api_id ),
			'extra_data'     => wp_json_encode( $api_mapped_rounds ),
		];

		return $wpdb->replace( $table, $data );
	}

	/**
	 * Create or Update Match from API data.
	 *
	 * @param $competition_id
	 * @param $data_api
	 * @param $mapped_rounds
	 *
	 * @since 0.16.0
	 * @return bool|int
	 */
	public function create_match( $competition_id, $data_api, $mapped_rounds ) {

		/*
		|--------------------------------------------------------------------
		| Extra check for existing game ID
		|--------------------------------------------------------------------
		*/
		global $wpdb;

		if ( ! empty( $data_api['fixture']['id'] ) ) {
			$wpdb->flush();

			$local_value = $wpdb->get_var(
				$wpdb->prepare(
					"
						SELECT local_value
						FROM {$wpdb->prefix}anwpfl_import_mapping
						WHERE       provider = %s
						            AND type = 'match'
						            AND external_value = %d
						",
					$this->api->provider,
					$data_api['fixture']['id']
				)
			);

			if ( absint( $local_value ) ) {
				return absint( $local_value );
			}
		}

		/*
		|--------------------------------------------------------------------
		| Try to Create new Game
		|--------------------------------------------------------------------
		*/
		try {
			if ( empty( $data_api['league']['round'] ) ) {
				return false;
			}

			$mapped_competition_id = 0;
			$mapped_round_id       = 0;

			foreach ( $mapped_rounds as $mapped_key => $mapped_round ) {
				if ( in_array( $data_api['league']['round'], $mapped_round, true ) ) {
					$mapped_key_arr = explode( '-', $mapped_key );

					$mapped_competition_id = absint( $mapped_key_arr[0] );
					$mapped_round_id       = isset( $mapped_key_arr[1] ) ? absint( $mapped_key_arr[1] ) : 0;
				}
			}

			if ( empty( $mapped_competition_id ) || empty( $mapped_round_id ) ) {
				return false;
			}

			if ( absint( $competition_id ) !== $mapped_competition_id ) {
				$competition_id = $mapped_competition_id;
			}

			$competition_obj = anwp_fl()->competition->get_competition( $competition_id );

			// Get Home and Away Clubs ids
			$home_club_id = absint( $this->get_mapped_club_id( $data_api['teams']['home']['id'] ) );
			$away_club_id = absint( $this->get_mapped_club_id( $data_api['teams']['away']['id'] ) );

			if ( ! $home_club_id || ! $away_club_id ) {
				return false;
			}

			// Get group ID
			$group_id = '';
			$round_id = '';

			foreach ( $competition_obj->groups as $group ) {
				$group_clubs = array_unique( array_map( 'absint', $group->clubs ) );

				if ( in_array( $home_club_id, $group_clubs, true ) && in_array( $away_club_id, $group_clubs, true ) && absint( $group->round ) === $mapped_round_id ) {
					$group_id = $group->id;
					$round_id = $group->round ?? 1;
				}
			}

			if ( ! $group_id ) {
				return false;
			}

			// Insert empty match into the database.
			$match_id = wp_insert_post(
				[
					'post_title'   => '',
					'post_content' => '',
					'post_type'    => 'anwp_match',
					'post_status'  => 'publish',
				]
			);

			if ( $match_id ) {
				$this->api->insert_mapped_link( 'match', $match_id, $data_api['fixture']['id'], 'fixture' );
			}

			// Prepare data to save
			$game_data = [
				'match_id'       => $match_id,
				'competition_id' => $competition_id,
				'finished'       => 0,
				'season_id'      => intval( $competition_obj->season_ids ),
				'league_id'      => intval( $competition_obj->league_id ),
				'group_id'       => (int) $group_id,
				'home_club'      => (int) $home_club_id,
				'away_club'      => (int) $away_club_id,
			];

			if ( 'secondary' === $competition_obj->multistage ) {
				$game_data['main_stage_id'] = $competition_obj->multistage_main;
			}

			if ( 'friendly' === get_post_meta( $competition_id, '_anwpfl_competition_status', true ) ) {
				$game_data['game_status'] = 0;
			}

			if ( ! empty( $data_api['fixture']['venue']['id'] ) && 'no' !== $this->api->config['stadiums'] ) {
				$stadium_id = $this->api->get_mapped_stadium_id( $data_api['fixture']['venue'] );

				if ( ! empty( $stadium_id ) ) {
					$game_data['stadium'] = $stadium_id;
				}
			}

			// Referee
			if ( ! empty( $data_api['fixture']['referee'] ) && 'yes' === $this->api->config['referees'] ) {
				$game_data['referee'] = $this->api->get_referee_id_by_api_name( $data_api['fixture']['referee'] );
			}

			// Set MatchWeek for Round Robin competition
			if ( 'round-robin' === $competition_obj->type ) {
				if ( preg_match_all( '/\d+/', $data_api['league']['round'], $numbers ) ) {
					$last_num = end( $numbers[0] );

					if ( absint( $last_num ) ) {
						$game_data['match_week'] = absint( $last_num );
					}
				}
			} elseif ( 'knockout' === $competition_obj->type && absint( $round_id ) ) {
				$game_data['match_week'] = absint( $round_id );
			}

			// Parse Special Status
			if ( in_array( $data_api['fixture']['status']['short'], [ 'PST', 'TBD', 'CANC' ], true ) ) {
				$game_data['special_status'] = $data_api['fixture']['status']['short'];
			}

			// Parse Match date
			if ( ! empty( $data_api['fixture']['date'] ) ) {
				$maybe_gmt_date = DateTime::createFromFormat( 'Y-m-d\TH:i:sP', $data_api['fixture']['date'] )->format( 'Y-m-d H:i:s' );

				if ( anwp_fl()->helper->validate_date( $maybe_gmt_date ) ) {
					$game_data['kickoff_gmt'] = $maybe_gmt_date;
					$game_data['kickoff']     = get_date_from_gmt( $maybe_gmt_date );
				}
			}

			anwp_fl()->match->insert( $game_data );

			//Update Match title and slug.
			anwp_fl_pro()->match->update_match_title_slug( $game_data, $match_id );

			return $match_id;
		} catch ( Exception $e ) {

			return false;
		}
	}

	/**
	 * Update finished Match.
	 *
	 * phpcs:disable WordPress.NamingConventions
	 *
	 * @param $match_api_id
	 * @param $match_id
	 * @param $data_api
	 *
	 * @return bool|int
	 * @since 0.13.0
	 */
	public function update_match( $match_api_id, $match_id, $data_api = null ) {
		global $wpdb;

		// Init players map
		$this->api->init_players_map();

		try {
			if ( null === $data_api ) {
				$data_api = $this->api->get_fixture_from_api( $match_api_id );
			}

			if ( is_wp_error( $data_api ) || ! isset( $data_api['fixture']['status']['short'] ) || ! in_array( $data_api['fixture']['status']['short'], [ 'FT', 'PEN', 'AET' ], true ) ) {
				return false;
			}

			$old_game_data = anwp_fl()->match->get_game_data( $match_id );

			// Prepare data to save
			$game_data = [
				'finished'       => 1,
				'special_status' => '',
				'extra'          => 0,
			];

			if ( 'AET' === $data_api['fixture']['status']['short'] ) {
				$game_data['extra'] = 1;
			} elseif ( 'PEN' === $data_api['fixture']['status']['short'] ) {
				$game_data['extra'] = 2;
			}

			// Stadium
			if ( 'no' !== $this->api->config['stadiums'] ) {
				$stadium_id = $this->api->get_mapped_stadium_id( $data_api['fixture']['venue'] );

				if ( ! empty( $stadium_id ) ) {
					$game_data['stadium'] = $stadium_id;
				}
			}

			// Referee
			if ( ! empty( $data_api['fixture']['referee'] ) && 'yes' === $this->api->config['referees'] ) {
				$game_data['referee'] = $this->api->get_referee_id_by_api_name( $data_api['fixture']['referee'] );
			}

			// Coach
			if ( ! empty( $data_api['lineups'] ) ) {
				foreach ( $data_api['lineups'] as $team_lineups ) {
					if ( 'no' !== $this->api->config['coaches'] && absint( $team_lineups['coach']['id'] ?? '' ) ) {
						if ( absint( $team_lineups['team']['id'] ) === absint( $data_api['teams']['home']['id'] ) ) {
							$game_data['coach_home'] = $this->api->get_or_create_coach( $team_lineups['coach']['id'], $team_lineups['coach']['name'] ?? '' );
						} else {
							$game_data['coach_away'] = $this->api->get_or_create_coach( $team_lineups['coach']['id'], $team_lineups['coach']['name'] ?? '' );
						}
					}
				}
			}

			// Parse Match date
			if ( ! empty( $data_api['fixture']['date'] ) ) {
				$maybe_gmt_date = DateTime::createFromFormat( 'Y-m-d\TH:i:sP', $data_api['fixture']['date'] )->format( 'Y-m-d H:i:s' );

				if ( anwp_fl()->helper->validate_date( $maybe_gmt_date ) ) {
					$game_data['kickoff_gmt'] = $maybe_gmt_date;
					$game_data['kickoff']     = get_date_from_gmt( $maybe_gmt_date );
				}
			}

			// Parse Scores
			$game_data['home_goals']      = absint( $data_api['goals']['home'] ?? 0 );
			$game_data['away_goals']      = absint( $data_api['goals']['away'] ?? 0 );
			$game_data['home_goals_half'] = '' === ( $data_api['score']['halftime']['home'] ?? '' ) ? null : absint( $data_api['score']['halftime']['home'] );
			$game_data['away_goals_half'] = '' === ( $data_api['score']['halftime']['away'] ?? '' ) ? null : absint( $data_api['score']['halftime']['away'] );
			$game_data['home_goals_ft']   = '' === ( $data_api['score']['fulltime']['home'] ?? '' ) ? null : absint( $data_api['score']['fulltime']['home'] );
			$game_data['away_goals_ft']   = '' === ( $data_api['score']['fulltime']['away'] ?? '' ) ? null : absint( $data_api['score']['fulltime']['away'] );
			$game_data['home_goals_e']    = '' === ( $data_api['score']['extratime']['home'] ?? '' ) ? null : absint( $data_api['score']['extratime']['home'] );
			$game_data['away_goals_e']    = '' === ( $data_api['score']['extratime']['away'] ?? '' ) ? null : absint( $data_api['score']['extratime']['away'] );
			$game_data['home_goals_p']    = '' === ( $data_api['score']['penalty']['home'] ?? '' ) ? null : absint( $data_api['score']['penalty']['home'] );
			$game_data['away_goals_p']    = '' === ( $data_api['score']['penalty']['away'] ?? '' ) ? null : absint( $data_api['score']['penalty']['away'] );

			/*
			|--------------------------------------------------------------------
			| Parse Stats
			|--------------------------------------------------------------------
			*/
			// Cards (will be populated from events)
			$game_data['home_cards_y']  = 0;
			$game_data['away_cards_y']  = 0;
			$game_data['home_cards_yr'] = 0;
			$game_data['away_cards_yr'] = 0;
			$game_data['home_cards_r']  = 0;
			$game_data['away_cards_r']  = 0;

			$stats_to_parse = [
				'Corner Kicks'    => 'corners',
				'Fouls'           => 'fouls',
				'Offsides'        => 'offsides',
				'Ball Possession' => 'possession',
				'Shots on Goal'   => 'shots_on_goal',
				'Total Shots'     => 'shots',
			];

			if ( ! empty( $data_api['statistics'] ) && is_array( $data_api['statistics'] ) ) {
				foreach ( $data_api['statistics'] as $team_api_stats ) {

					if ( empty( $team_api_stats['team']['id'] ) || empty( $team_api_stats['statistics'] ) ) {
						continue;
					}

					$is_home_team = absint( $team_api_stats['team']['id'] ) === absint( $data_api['teams']['home']['id'] );

					foreach ( $team_api_stats['statistics'] as $team_statistic_value ) {
						if ( ! empty( $team_statistic_value['type'] ) && ! empty( $team_statistic_value['value'] ) && ! empty( $stats_to_parse[ $team_statistic_value['type'] ] ) ) {
							$game_data[ ( $is_home_team ? 'home_' : 'away_' ) . $stats_to_parse[ $team_statistic_value['type'] ] ] = absint( $team_statistic_value['value'] );
						}
					}
				}
			}

			if ( 'yes' === $this->api->config['club_advanced_stats'] ) {
				$game_data = $this->api->parser->get_team_advanced_stats( $game_data, $data_api );
			}

			/*
			|--------------------------------------------------------------------
			| Events
			|--------------------------------------------------------------------
			*/
			$events        = $this->api->parser->parse_match_events( $data_api );
			$local_home_id = (int) $this->get_mapped_club_id( $data_api['teams']['home']['id'] );

			$game_data['match_events'] = $events ? wp_json_encode( $events ) : '';

			// Populate cards
			foreach ( $events as $event ) {
				if ( 'card' === $event['type'] ) {
					if ( 'y' === $event['card'] ) {
						absint( $event['club'] ) === $local_home_id ? $game_data['home_cards_y'] ++ : $game_data['away_cards_y'] ++;
					} elseif ( 'yr' === $event['card'] ) {
						absint( $event['club'] ) === $local_home_id ? $game_data['home_cards_yr'] ++ : $game_data['away_cards_yr'] ++;
					} elseif ( 'r' === $event['card'] ) {
						absint( $event['club'] ) === $local_home_id ? $game_data['home_cards_r'] ++ : $game_data['away_cards_r'] ++;
					}
				}
			}

			/*
			|--------------------------------------------------------------------
			| Save Game Data
			|--------------------------------------------------------------------
			*/
			anwp_fl()->match->update( $match_id, $game_data );

			/*
			|--------------------------------------------------------------------
			| Line Ups
			|--------------------------------------------------------------------
			*/
			$lineups_data_exists = $wpdb->get_var(
				$wpdb->prepare(
					"
						SELECT COUNT(*)
						FROM $wpdb->anwpfl_lineups
						WHERE       match_id = %d
						",
					$match_id
				)
			);

			$lineups_data = $this->api->parser->parse_match_lineups_and_subs( $data_api );

			if ( $lineups_data_exists ) {
				$wpdb->update( $wpdb->anwpfl_lineups, $lineups_data, [ 'match_id' => $match_id ] );
			} else {
				$wpdb->insert( $wpdb->anwpfl_lineups, array_merge( $lineups_data, [ 'match_id' => $match_id ] ) );
			}

			/*
			|--------------------------------------------------------------------
			| Player Stats
			|--------------------------------------------------------------------
			*/
			// General Player Stats
			anwp_fl()->match->save_player_statistics( array_merge( $old_game_data, $game_data, $lineups_data ), $events );

			if ( 'yes' === $this->api->config['player_stats'] ) {
				$this->api->parser->parse_players_stats( array_merge( $old_game_data, $game_data ), $data_api );
			}

			/*
			|--------------------------------------------------------------------
			| Update Formations
			|--------------------------------------------------------------------
			*/
			if ( 'yes' === $this->api->config['lineups_formation'] ) {
				$this->api->parser->parse_lineups_formation( $match_api_id, $match_id, $data_api['lineups'] ?? [] );
			}

			/*
			|--------------------------------------------------------------------
			| Update Match title and slug.
			|--------------------------------------------------------------------
			*/
			if ( trim( AnWPFL_Options::get_value( 'match_title_generator' ) ) && $old_game_data['kickoff'] !== $game_data['kickoff'] ) {
				anwp_fl_pro()->match->update_match_title_slug( array_merge( $old_game_data, $game_data, $lineups_data ), $match_id );
			}

			return $match_id;

		} catch ( Exception $e ) {

			return false;
		}
	}

	/**
	 * Get API competition data by local competition ID
	 *
	 * @param int $competition_id
	 *
	 * @return array [
	 *     'competition_api_id' => 1,
	 *     'season_api'         => 2022,
	 *     'start'              => '2022-02-01,
	 *     'end'                => '2022-12-03,
	 * ]
	 * @since 0.13.0
	 */
	public function get_api_competition_by_local( int $competition_id ): array {
		global $wpdb;
		static $competitions = null;

		if ( null === $competitions ) {
			$competitions = [];

			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"
						SELECT external_value, local_value, extra_data
						FROM {$wpdb->prefix}anwpfl_import_mapping
						WHERE       provider = %s
						            AND type = 'competition-v3'
						",
					$this->api->provider
				)
			);

			foreach ( $rows as $data_row ) {
				$extra_data = json_decode( $data_row->extra_data, true ) ?: [];

				$competitions[ $data_row->local_value ] = [
					'competition_api_id' => absint( $data_row->external_value ),
					'season_api'         => absint( $extra_data['season'] ?? 0 ),
					'start'              => ( $extra_data['start'] ?? '' ),
					'end'                => ( $extra_data['end'] ?? '' ),
				];
			}
		}

		if ( isset( $competitions[ $competition_id ] ) ) {
			return $competitions[ $competition_id ];
		}

		return [
			'competition_api_id' => 0,
			'season_api'         => 0,
			'start'              => '',
			'end'                => '',
		];
	}

	/**
	 * Update player
	 *
	 * @param array $api_data_full
	 * @param bool  $full_update
	 * @param int   $club_id
	 *
	 * @return mixed
	 */
	public function update_player( array $api_data_full, bool $full_update = false, int $club_id = 0 ) {

		$api_data = $api_data_full['player'];

		// Get player ID
		if ( isset( $this->api->players_mapped[ $api_data['id'] ] ) && absint( $this->api->players_mapped[ $api_data['id'] ]->local_value ) ) {
			$player_id = absint( $this->api->players_mapped[ $api_data['id'] ]->local_value );
		}

		if ( empty( $api_data['name'] ) ) {
			return false;
		}

		// Get short name
		$short_name = '';

		if ( ! empty( $api_data['firstname'] ) && ! empty( $api_data['lastname'] ) ) {
			$short_name = mb_substr( $api_data['firstname'], 0, 1 ) . '. ' . $api_data['lastname'];
		}

		if ( empty( $short_name ) || mb_strlen( $short_name ) > mb_strlen( $api_data['name'] ) ) {
			$short_name = sanitize_text_field( $api_data['name'] );
		}

		// Save new player
		if ( empty( $player_id ) || ! absint( $player_id ) ) {
			$player_id = wp_insert_post(
				[
					'post_title'   => $short_name,
					'post_content' => '',
					'post_type'    => 'anwp_player',
					'post_status'  => 'publish',
				]
			);

			if ( $player_id && $this->api->insert_mapped_link( 'player', $player_id, $api_data['id'], $full_update ? 'full' : '' ) ) {
				$this->api->players_mapped[ $api_data['id'] ] = (object) [
					'external_value' => $api_data['id'],
					'local_value'    => $player_id,
					'status'         => $full_update ? 'full' : '',
				];
			}
		}

		if ( empty( $player_id ) ) {
			return false;
		}

		$saved_player_data = anwp_fl()->player->get_player_data( $player_id );
		$player_data       = [
			'player_id' => $player_id,
		];

		if ( empty( $saved_player_data['name'] ?? '' ) ) {
			$player_data['name'] = $api_data['name'];
		}

		if ( $full_update ) {
			if ( empty( $saved_player_data['full_name'] ?? '' ) && ! empty( $api_data['firstname'] ) && ! empty( $api_data['lastname'] ) ) {
				$player_data['full_name'] = $api_data['firstname'] . ' ' . $api_data['lastname'];
			}

			if ( empty( $saved_player_data['short_name'] ?? '' ) ) {
				$player_data['short_name'] = $short_name;
			}

			// Height
			if ( absint( $api_data['height'] ?? 0 ) ) {
				$player_data['height'] = absint( $api_data['height'] );
			}

			// Weight
			if ( absint( $api_data['weight'] ?? 0 ) ) {
				$player_data['weight'] = absint( $api_data['weight'] );
			}

			// Nationality
			if ( ! empty( $api_data['nationality'] ) && empty( $saved_player_data['nationality'] ) ) {
				$nationality = anwp_fl_pro()->data->get_api_country_code_by_name( $api_data['nationality'] );

				if ( $nationality ) {
					$player_data['nationality'] = sanitize_text_field( $nationality );
				}
			}

			// Date of Birth
			if ( ! empty( $api_data['birth']['date'] ) && empty( $saved_player_data['date_of_birth'] ) ) {
				$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $api_data['birth']['date'] );

				if ( $birth_date_obj ) {
					$player_data['date_of_birth'] = $birth_date_obj->format( 'Y-m-d' );
				}
			}

			// Place of Birth
			if ( ! empty( $api_data['birth']['place'] ) && empty( $saved_player_data['place_of_birth'] ) ) {
				$player_data['place_of_birth'] = $api_data['birth']['place'];
			}

			// Country of Birth
			if ( ! empty( $api_data['birth']['country'] ) && empty( $saved_player_data['country_of_birth'] ) ) {
				$country_of_birth = anwp_fl_pro()->data->get_api_country_code_by_name( $api_data['birth']['country'] );

				if ( $country_of_birth ) {
					$player_data['country_of_birth'] = $country_of_birth;
				}
			}
		}

		// Current Club
		if ( absint( $club_id ) ) {
			$is_national_club = anwp_fl_pro()->club->is_national_club( $club_id );

			if ( $is_national_club ) {
				$player_data['national_team'] = absint( $club_id );
			} else {
				$player_data['team_id'] = absint( $club_id );
			}
		}

		// Position
		if ( empty( $saved_player_data['position'] ) ) {
			if ( ! empty( $api_data_full['statistics'] ) && ! empty( $api_data_full['statistics'][0]['games'] ) && ! empty( $api_data_full['statistics'][0]['games']['position'] ) ) {
				$player_data['position'] = mb_strtolower( mb_substr( $api_data_full['statistics'][0]['games']['position'], 0, 1 ) );
			} elseif ( ! empty( $api_data['position'] ) ) {
				$player_data['position'] = mb_strtolower( mb_substr( $api_data['position'], 0, 1 ) );
			}

			if ( ! empty( $player_data['position'] ) && 'a' === $player_data['position'] ) {
				$player_data['position'] = 'f';
			}
		}

		// Photo
		if ( ! empty( $api_data['photo'] ) && 'yes' === $this->api->config['photos_player'] ) {
			if ( empty( $saved_player_data['photo'] ) || 'yes' === $this->api->config['photos_player_force'] ) {
				$photo_src = $this->api->upload_logo( $api_data['photo'], $player_id, 'player' );

				if ( ! empty( $photo_src ) ) {
					$player_data['photo'] = str_ireplace( wp_make_link_relative( wp_upload_dir()['baseurl'] ), '', wp_make_link_relative( $photo_src ) );
				}
			}
		}

		if ( absint( $saved_player_data['player_id'] ?? 0 ) === absint( $player_id ) ) {
			anwp_fl()->player->update( $player_id, $player_data );
		} else {
			anwp_fl()->player->insert( array_merge( $player_data, [ 'player_id' => $player_id ] ) );
		}

		return $player_id;
	}

	/**
	 * Update coach
	 *
	 * @param int   $coach_id
	 * @param array $api_data
	 * @param int   $club_api_id
	 *
	 * @return mixed|void
	 * @since 0.14.5
	 */
	public function update_coach( int $coach_id, array $api_data = [], int $club_api_id = 0 ) {

		if ( empty( $api_data ) ) {
			$response = $this->api->send_request_to_api(
				'coachs',
				DAY_IN_SECONDS,
				[
					'id' => $coach_id,
				]
			);

			if ( ! empty( $response['errors'] ) || empty( $response['response'] ) ) {
				return;
			}

			$api_data = $response['response'];
		}

		$post_data   = [];
		$update_post = false;

		// Set Post ID
		if ( isset( $this->api->coaches_mapped[ $api_data['id'] ] ) && absint( $this->api->coaches_mapped[ $api_data['id'] ]->local_value ) ) {
			$post_data['ID'] = absint( $this->api->coaches_mapped[ $api_data['id'] ]->local_value );
		}

		if ( empty( $api_data['name'] ) ) {
			return false;
		}

		// Extra check for post ID
		if ( empty( $post_data['ID'] ) ) {

			global $wpdb;

			$local_value = $wpdb->get_var(
				$wpdb->prepare(
					"
						SELECT local_value
						FROM {$wpdb->prefix}anwpfl_import_mapping
						WHERE       provider = %s
						            AND type = 'coach'
						            AND external_value = %d
						",
					$this->api->provider,
					$api_data['id']
				)
			);

			if ( $local_value && absint( $local_value ) ) {
				$post_data['ID'] = absint( $local_value );
			}
		}

		// Get old post data
		$old_post = false;

		if ( ! empty( $post_data['ID'] ) && absint( $post_data['ID'] ) ) {
			$old_post = get_post( $post_data['ID'] );
		}

		// Prevent Changing existing player name
		if ( empty( $post_data['ID'] ) ) {
			$post_data['post_title'] = sanitize_text_field( $api_data['name'] );

			$update_post = true;
		} else {
			$post_data['post_title'] = $old_post ? $old_post->post_title : '';
		}

		$meta_input = [];

		// Nationality
		if ( ! empty( $api_data['nationality'] ) ) {

			if ( ( $old_post && empty( get_post_meta( $old_post->ID, '_anwpfl_nationality', true ) ) ) || ! $old_post ) {
				$nationality = anwp_fl_pro()->data->get_api_country_code_by_name( $api_data['nationality'] );

				if ( $nationality ) {
					$meta_input['_anwpfl_nationality'] = [ sanitize_text_field( $nationality ) ];
				}
			}
		}

		// Date of Birth
		if ( ! empty( $api_data['birth']['date'] ) ) {

			if ( ( $old_post && empty( get_post_meta( $old_post->ID, '_anwpfl_date_of_birth', true ) ) ) || ! $old_post ) {
				$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $api_data['birth']['date'] );

				if ( $birth_date_obj ) {
					$meta_input['_anwpfl_date_of_birth'] = $birth_date_obj->format( 'Y-m-d' );
				}
			}
		}

		// Place of Birth
		if ( ! empty( $api_data['birth']['place'] ) ) {

			if ( ( $old_post && empty( get_post_meta( $old_post->ID, '_anwpfl_place_of_birth', true ) ) ) || ! $old_post ) {
				$meta_input['_anwpfl_place_of_birth'] = $api_data['birth']['place'];
			}
		}

		$meta_input['_anwpfl_job_title'] = AnWPFL_Text::get_value( 'api_import__coach', 'Coach' );

		// Career
		if ( ! empty( $api_data['career'] ) && is_array( $api_data['career'] ) && $club_api_id ) {

			$check_date = date_i18n( 'Y-m-d' );

			foreach ( $api_data['career'] as $career_team ) {
				if ( ! $this->get_mapped_club_id( $career_team['team']['id'] ) ) {
					continue;
				}

				if ( absint( $career_team['team']['id'] ) === absint( $club_api_id ) && $career_team['start'] < $check_date && ( ! $career_team['end'] || $career_team['end'] > $check_date ) ) {
					$club_id = $this->get_mapped_club_id( $club_api_id );

					if ( absint( $club_id ) ) {
						$meta_input['_anwpfl_current_club'] = absint( $club_id );
					}
				}

				// !! Do not import career because data are not correct.
				/*
				if ( ! isset( $meta_input['_anwpfl_staff_history_metabox_group'] ) ) {
					$meta_input['_anwpfl_staff_history_metabox_group'] = [];
				}

				$meta_input['_anwpfl_staff_history_metabox_group'][] = [
					'job'  => AnWPFL_Text::get_value( 'api_import__coach', 'Coach' ),
					'from' => isset( $career_team['start'] ) ? $career_team['start'] : '',
					'to'   => isset( $career_team['end'] ) ? $career_team['end'] : '',
					'club' => $this->helper->get_mapped_club_id( $career_team['team']['id'] ),
				];
				*/
			}
		}

		// Check meta update required
		if ( ! empty( $meta_input ) ) {
			$post_data['meta_input'] = $meta_input;

			$update_post = true;
		}

		try {

			if ( $update_post ) {

				$post_data['post_status'] = 'publish';
				$post_data['post_type']   = 'anwp_staff';

				$post_id = wp_insert_post( $post_data );

				if ( $this->api->insert_mapped_link( 'coach', $post_id, $api_data['id'] ) ) {
					$this->api->coaches_mapped[ $api_data['id'] ] = (object) [
						'external_value' => $api_data['id'],
						'local_value'    => $post_id,
					];
				}

				// Try to update photo
				if ( 'no' !== $this->api->config['photos_coach'] && ! absint( get_post_meta( $post_id, '_anwpfl_photo_id', true ) ) ) {
					if ( ! empty( $api_data['photo'] ) ) {
						$this->api->upload_logo( $api_data['photo'], $post_id, 'coach' );
					}
				}

				return $post_id ?: false;
			} elseif ( ! empty( $post_data['ID'] ) ) {
				return $post_data['ID'];
			}

			return false;

		} catch ( Exception $e ) {
			return false;
		}
	}
}
