<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AnWPFL_Premium_API_API_Football_Parser {

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
	 * Parse advanced Club Statistics
	 *
	 * @param array $game_data
	 * @param array $data_api
	 *
	 * @return array
	 * @since 0.16.0
	 */
	public function get_team_advanced_stats( array $game_data, array $data_api ): array {

		$columns  = json_decode( get_option( 'anwpfl_stats_columns_match_club' ) );
		$stat_map = [];

		$available_slugs = [
			'Shots off Goal'   => 'shots_off_goal',
			'Blocked Shots'    => 'blocked_shots',
			'Shots insidebox'  => 'shots_insidebox',
			'Shots outsidebox' => 'shots_outsidebox',
			'Goalkeeper Saves' => 'goalkeeper_saves',
			'Total passes'     => 'total_passes',
			'Passes accurate'  => 'passes_accurate',
		];

		foreach ( $columns as $stat_column ) {
			if ( 'simple' === $stat_column->type && in_array( $stat_column->field_slug, array_values( $available_slugs ), true ) ) {
				$stat_map[ $stat_column->field_slug ] = $stat_column->id;
			}
		}

		$home_stats = [];
		$away_stats = [];

		if ( ! empty( $data_api['statistics'] ) && is_array( $data_api['statistics'] ) ) {
			foreach ( $data_api['statistics'] as $team_api_stats ) {

				if ( empty( $team_api_stats['team']['id'] ) || empty( $team_api_stats['statistics'] ) ) {
					continue;
				}

				$is_home_team = absint( $team_api_stats['team']['id'] ) === absint( $data_api['teams']['home']['id'] );

				foreach ( $team_api_stats['statistics'] as $team_statistic_value ) {
					if ( ! empty( $team_statistic_value['type'] ) && ! empty( $team_statistic_value['value'] ) && ! empty( $available_slugs[ $team_statistic_value['type'] ] ) && ! empty( $stat_map[ $available_slugs[ $team_statistic_value['type'] ] ] ) ) {
						if ( $is_home_team ) {
							$home_stats[ $stat_map[ $available_slugs[ $team_statistic_value['type'] ] ] ] = $team_statistic_value['value'];
						} else {
							$away_stats[ $stat_map[ $available_slugs[ $team_statistic_value['type'] ] ] ] = $team_statistic_value['value'];
						}
					}
				}
			}
		}

		$game_data['stats_home_club'] = $home_stats ? wp_json_encode( $home_stats ) : '';
		$game_data['stats_away_club'] = $home_stats ? wp_json_encode( $away_stats ) : '';

		return $game_data;
	}

	/**
	 * Parse API events
	 *
	 * @param array $data_api
	 *
	 * @return array
	 * @since 0.13.0
	 */
	public function parse_match_events( array $data_api ): array {

		$events = [];

		if ( empty( $data_api['events'] ) || ! is_array( $data_api['events'] ) ) {
			return $events;
		}

		// Get active field players
		$field_players = [];

		if ( ! empty( $data_api['lineups'] ) ) {
			foreach ( $data_api['lineups'] as $team_lineups ) {
				if ( ! empty( $team_lineups['startXI'] ) ) {
					foreach ( $team_lineups['startXI'] as $start_player ) {
						if ( empty( $start_player['player']['id'] ) || ! absint( $start_player['player']['id'] ) ) {
							continue;
						}

						$field_players[] = absint( $start_player['player']['id'] );
					}
				}
			}
		}

		// Fix Second Yellow card assigning
		$yellow_players = [];

		foreach ( $data_api['events'] as $event ) {

			$e = [
				'type'        => 'goal',
				'club'        => '',
				'minute'      => '',
				'minuteAdd'   => '',
				'player'      => '',
				'assistant'   => '',
				'playerOut'   => '',
				'card'        => '',
				'ownGoal'     => '',
				'fromPenalty' => '',
			];

			switch ( $event['type'] ) {
				case 'Card':
					$e['type']      = 'card';
					$e['minute']    = $event['time']['elapsed'] ?? '';
					$e['minuteAdd'] = $event['time']['extra'] ?? ''; // phpcs:ignore
					$e['club']      = $this->api->helper->get_mapped_club_id( $event['team']['id'] );

					if ( ! empty( $event['comments'] ) ) {
						$e['comment'] = sanitize_text_field( $event['comments'] );
					}

					if ( ! empty( $event['player']['id'] ) && absint( $event['player']['id'] ) ) {
						$e['player'] = $this->api->get_or_create_player( $event['player']['id'], $event['player']['name'], $event['team']['id'] );

						if ( $e['player'] ) {
							$yellow_players[ $e['player'] ] = isset( $yellow_players[ $e['player'] ] ) ? ++ $yellow_players[ $e['player'] ] : 1;
						}
					}

					if ( 'Yellow Card' === $event['detail'] ) {
						$e['card'] = 'y';
					} elseif ( 'Red Card' === $event['detail'] ) {
						$e['card'] = 'r';
					}

					if ( isset( $yellow_players[ $e['player'] ] ) && 2 === $yellow_players[ $e['player'] ] ) {
						$e['card'] = 'yr';
					}

					if ( isset( $yellow_players[ $e['player'] ] ) && $yellow_players[ $e['player'] ] < 3 ) {
						$events[] = $e;
					}
					break;

				case 'Goal':
					$e['minute']    = $event['time']['elapsed'] ?? '';
					$e['minuteAdd'] = $event['time']['extra'] ?? ''; // phpcs:ignore
					$e['club']      = $this->api->helper->get_mapped_club_id( $event['team']['id'] );

					if ( ! empty( $event['player']['id'] ) && absint( $event['player']['id'] ) ) {
						$e['player'] = $this->api->get_or_create_player( $event['player']['id'], $event['player']['name'], $event['team']['id'] );
					}

					if ( ! empty( $event['assist']['id'] ) && absint( $event['assist']['id'] ) ) {
						$e['assistant'] = $this->api->get_or_create_player( $event['assist']['id'], $event['assist']['name'], $event['team']['id'] );
					}

					if ( 'Penalty Shootout' === $event['comments'] ) {
						$e['type']   = 'penalty_shootout';
						$e['scored'] = 'Missed Penalty' === $event['detail'] ? '' : 'yes';
					} elseif ( 'own goal' === trim( mb_strtolower( $event['detail'] ) ) ) {
						$e['ownGoal'] = 'yes'; // phpcs:ignore
					} elseif ( 'penalty' === trim( mb_strtolower( $event['detail'] ) ) ) {
						$e['fromPenalty'] = 'yes'; // phpcs:ignore
					} elseif ( 'Missed Penalty' === $event['detail'] ) {
						$e['type'] = 'missed_penalty';
					}

					$events[] = $e;
					break;

				case 'Var':
					$e['type']      = 'var';
					$e['minute']    = $event['time']['elapsed'] ?? '';
					$e['minuteAdd'] = $event['time']['extra'] ?? ''; // phpcs:ignore
					$e['club']      = $this->api->helper->get_mapped_club_id( $event['team']['id'] );

					if ( ! empty( $event['player']['id'] ) && absint( $event['player']['id'] ) ) {
						$e['player'] = $this->api->get_or_create_player( $event['player']['id'], $event['player']['name'], $event['team']['id'] );
					}

					if ( ! empty( $event['detail'] ) ) {
						$e['comment'] = sanitize_text_field( $event['detail'] );
					}

					$events[] = $e;
					break;

				case 'subst':
					$e['type']      = 'substitute';
					$e['minute']    = $event['time']['elapsed'] ?? '';
					$e['minuteAdd'] = $event['time']['extra'] ?? ''; // phpcs:ignore
					$e['club']      = $this->api->helper->get_mapped_club_id( $event['team']['id'] );

					// player_id | Player == player OFF
					// assist_id | assist == PLAYER ON

					if ( absint( $event['assist']['id'] ?? 0 ) && absint( $event['player']['id'] ?? 0 ) ) {
						$e['playerOut'] = $this->api->get_or_create_player( $event['player']['id'], $event['player']['name'], $event['team']['id'] ); // phpcs:ignore
						$e['player']    = $this->api->get_or_create_player( $event['assist']['id'], $event['assist']['name'], $event['team']['id'] );

						// Check for reverse order (API has that mistake in some competitions)
						if ( ! empty( $field_players ) && ! in_array( absint( $event['player']['id'] ), $field_players, true ) && in_array( absint( $event['assist']['id'] ), $field_players, true ) ) {
							$e['playerOut'] = $this->api->get_or_create_player( $event['assist']['id'], $event['assist']['name'], $event['team']['id'] ); // phpcs:ignore
							$e['player']    = $this->api->get_or_create_player( $event['player']['id'], $event['player']['name'], $event['team']['id'] );

							if ( ( $key = array_search( absint( $event['assist']['id']), $field_players, true ) ) !== false ) { // phpcs:ignore
								unset( $field_players[ $key ] );
							}

							$field_players[] = absint( $event['player']['id'] );
						} else {
							if ( ( $key = array_search( absint( $event['player']['id'] ), $field_players, true ) ) !== false ) { // phpcs:ignore
								unset( $field_players[ $key ] );
							}

							$field_players[] = absint( $event['assist']['id'] );
						}

						$events[] = $e;
					}

					break;
			}
		}

		return $events;
	}

	/**
	 * Parse Match Line-ups & Substitutions
	 *
	 * @param array $data_api
	 *
	 * @return array
	 * @since 0.13.0
	 */
	public function parse_match_lineups_and_subs( array $data_api = [] ): array {

		$lineups_data = [];

		$lineups_data['home_line_up']   = [];
		$lineups_data['away_line_up']   = [];
		$lineups_data['home_subs']      = [];
		$lineups_data['away_subs']      = [];
		$lineups_data['custom_numbers'] = (object) [];

		if ( ! empty( $data_api['lineups'] ) ) {
			foreach ( $data_api['lineups'] as $team_lineups ) {
				if ( ! empty( $team_lineups['startXI'] ) ) {
					foreach ( $team_lineups['startXI'] as $start_player ) {
						if ( empty( $start_player['player']['id'] ) || ! absint( $start_player['player']['id'] ) ) {
							continue;
						}

						// Get player ID
						$player_id = $this->api->get_or_create_player( $start_player['player']['id'], $start_player['player']['name'], $team_lineups['team']['id'] );

						if ( empty( $player_id ) || ! absint( $player_id ) ) {
							continue;
						}

						if ( absint( $team_lineups['team']['id'] ) === absint( $data_api['teams']['home']['id'] ) ) {
							$lineups_data['home_line_up'][] = absint( $player_id );
						} else {
							$lineups_data['away_line_up'][] = absint( $player_id );
						}

						if ( ! empty( $start_player['player']['number'] ) ) {
							$lineups_data['custom_numbers']->{$player_id} = (string) $start_player['player']['number'];
						}
					}
				}

				if ( ! empty( $team_lineups['substitutes'] ) ) {
					foreach ( $team_lineups['substitutes'] as $subs_player ) {
						if ( empty( $subs_player['player']['id'] ) || ! absint( $subs_player['player']['id'] ) ) {
							continue;
						}

						// Get player ID
						$player_id = $this->api->get_or_create_player( $subs_player['player']['id'], $subs_player['player']['name'], $team_lineups['team']['id'] );

						if ( empty( $player_id ) || ! absint( $player_id ) ) {
							continue;
						}

						if ( absint( $team_lineups['team']['id'] ) === absint( $data_api['teams']['home']['id'] ) ) {
							$lineups_data['home_subs'][] = absint( $player_id );
						} else {
							$lineups_data['away_subs'][] = absint( $player_id );
						}

						if ( ! empty( $subs_player['player']['number'] ) ) {
							$lineups_data['custom_numbers']->{$player_id} = (string) $subs_player['player']['number'];
						}
					}
				}
			}
		}

		$lineups_data['home_line_up'] = $lineups_data['home_line_up'] ? implode( ',', $lineups_data['home_line_up'] ) : '';
		$lineups_data['away_line_up'] = $lineups_data['away_line_up'] ? implode( ',', $lineups_data['away_line_up'] ) : '';
		$lineups_data['home_subs']    = $lineups_data['home_subs'] ? implode( ',', $lineups_data['home_subs'] ) : '';
		$lineups_data['away_subs']    = $lineups_data['away_subs'] ? implode( ',', $lineups_data['away_subs'] ) : '';

		$lineups_data['custom_numbers'] = wp_json_encode( $lineups_data['custom_numbers'] );

		return $lineups_data;
	}

	/**
	 * Save LineUps formation
	 *
	 * @since 0.13.4
	 */
	public function parse_lineups_formation( int $game_api_id, int $game_id, array $api_data = [], int $cache_time = 900 ): bool {

		global $wpdb;
		$formation_exists = false;

		if ( empty( $api_data ) ) {
			$response = $this->api->send_request_to_api( 'fixtures/lineups', $cache_time, [ 'fixture' => $game_api_id ] );

			if ( ! empty( $response['errors'] ) ) {
				return false;
			}

			$api_data = $response['response'];
		}

		if ( empty( $api_data ) || ! is_array( $api_data ) ) {
			return false;
		}

		$this->api->init_players_map();

		$formation = [
			'home' => [
				'l1' => [],
				'l2' => [],
				'l3' => [],
				'l4' => [],
				'l5' => [],
				'l6' => [],
			],
			'away' => [
				'l1' => [],
				'l2' => [],
				'l3' => [],
				'l4' => [],
				'l5' => [],
				'l6' => [],
			],
		];

		$additional_formation_data = [
			'home_colors'    => $api_data[0]['team']['colors'] ?? [],
			'home_formation' => $api_data[0]['formation'] ?? '',
			'away_colors'    => $api_data[1]['team']['colors'] ?? [],
			'away_formation' => $api_data[1]['formation'] ?? '',
		];

		foreach ( $api_data as $team_index => $team_data ) {
			if ( empty( $team_data['startXI'] ) ) {
				continue;
			}

			foreach ( $team_data['startXI'] as $st_player ) {
				if ( ! empty( $st_player['player'] ) && ! empty( $st_player['player']['id'] ) && isset( $this->api->players_mapped[ $st_player['player']['id'] ] ) ) {
					if ( ! empty( $st_player['player']['grid'] ) ) {
						$grid_pos = explode( ':', $st_player['player']['grid'] );

						if ( ! empty( $grid_pos[0] ) && $grid_pos[0] > 0 && $grid_pos[0] < 7 && ! empty( $grid_pos[1] ) ) {
							$formation[ $team_index ? 'away' : 'home' ][ 'l' . absint( $grid_pos[0] ) ][ absint( $grid_pos[1] ) ] = absint( $this->api->players_mapped[ $st_player['player']['id'] ]->local_value );

							$formation_exists = true;
						}
					}
				}
			}
		}

		// Normalize formation
		if ( $formation_exists ) {
			foreach ( $formation as $side => $side_data ) {
				foreach ( $side_data as $line_index => $line ) {
					ksort( $line );
					$formation[ $side ][ $line_index ] = array_filter( array_values( $line ) );
				}
			}

			$formation_data = [
				'formation' => wp_json_encode( $formation ) ? : '',
			];

			// Additional Formation Data
			if ( ! empty( $additional_formation_data ) ) {
				$ignore_options = [
					'custom_color',
					'color',
					'home',
					'away',
				];

				if ( ! empty( $additional_formation_data['home_colors']['player']['primary'] ) ) {
					if ( ! in_array( get_post_meta( $game_id, '_anwpfl_home_club_shirt', true ), $ignore_options, true ) ) {
						$formation_data['home_club_shirt'] = 'custom';
					}
				}

				if ( ! empty( $additional_formation_data['away_colors']['player']['primary'] ) ) {
					if ( ! in_array( get_post_meta( $game_id, '_anwpfl_away_club_shirt', true ), $ignore_options, true ) ) {
						$formation_data['away_club_shirt'] = 'custom';
					}
				}

				if ( $formation_data['formation'] ) {
					$formation_data['formation_extra'] = wp_json_encode( $additional_formation_data ) ? : '';
				}
			}

			if ( empty( anwp_fl_pro()->match->get_formation_data( $game_id ) ) ) {
				$formation_data['match_id'] = $game_id;
				$wpdb->insert( $wpdb->anwpfl_formations, $formation_data );
			} else {
				$wpdb->update( $wpdb->anwpfl_formations, $formation_data, [ 'match_id' => $game_id ] );
			}
		}

		return $formation_exists;
	}

	/**
	 * Save Players stats
	 *
	 * @param array $game_data
	 * @param array $data_api
	 *
	 * @return bool
	 * @since 0.13.0
	 */
	public function parse_players_stats( array $game_data, array $data_api ): bool {

		if ( empty( $game_data['match_id'] ) ) {
			return false;
		}

		global $wpdb;

		$game_id = absint( $game_data['match_id'] );

		// Get existing records to ignore missing
		$saved_records = $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT CONCAT( club_id, '-', player_id )
					FROM $wpdb->anwpfl_players
					WHERE match_id = %d
				",
				$game_id
			)
		);

		// Fetch time type columns
		$player_stats_columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ) );
		$api_stats_columns    = [];

		if ( ! empty( $player_stats_columns ) ) {
			foreach ( $player_stats_columns as $stat ) {
				if ( ! empty( $stat->api_field ) ) {
					$api_stats_columns[] = $stat;
				}
			}
		}

		if ( empty( $api_stats_columns ) || empty( $data_api['players'] ) || ! is_array( $data_api['players'] ) ) {
			return false;
		}

		foreach ( $data_api['players'] as $team_stats ) {

			if ( empty( $team_stats['players'] ) || empty( $team_stats['team']['id'] ) ) {
				continue;
			}

			$club_id = $this->api->helper->get_mapped_club_id( $team_stats['team']['id'] );

			foreach ( $team_stats['players'] as $team_player_stats ) {

				if ( empty( $team_player_stats['statistics'][0] ) || ! absint( $team_player_stats['statistics'][0]['games']['minutes'] ?? 0 ) ) {
					continue;
				}

				$player_id = absint( $this->api->players_mapped[ $team_player_stats['player']['id'] ]->local_value ?? 0 );

				if ( ! absint( $player_id ) || ! absint( $club_id ) || ! in_array( $club_id . '-' . $player_id, $saved_records, true ) ) {
					continue;
				}

				$player_stats   = $team_player_stats['statistics'][0];
				$data_to_update = [];

				foreach ( $api_stats_columns as $api_stats_column ) {
					$value = null;

					switch ( $api_stats_column->api_field ) {
						case 'rating':
							$value = sanitize_text_field( $player_stats['games']['rating'] ?? '' );
							break;

						case 'goals__saves':
							$value = sanitize_text_field( $player_stats['goals']['saves'] ?? '' );
							break;

						case 'passes__total':
							$value = sanitize_text_field( $player_stats['passes']['total'] ?? '' );
							break;

						case 'passes__key':
							$value = sanitize_text_field( $player_stats['passes']['key'] ?? '' );
							break;

						case 'passes__accuracy':
							$value = sanitize_text_field( $player_stats['passes']['accuracy'] ?? '' );
							break;

						case 'shots__total':
							$value = sanitize_text_field( $player_stats['shots']['total'] ?? '' );
							break;

						case 'shots__on':
							$value = sanitize_text_field( $player_stats['shots']['on'] ?? '' );
							break;

						case 'tackles__total':
							$value = sanitize_text_field( $player_stats['tackles']['total'] ?? '' );
							break;

						case 'tackles__blocks':
							$value = sanitize_text_field( $player_stats['tackles']['blocks'] ?? '' );
							break;

						case 'tackles__interceptions':
							$value = sanitize_text_field( $player_stats['tackles']['interceptions'] ?? '' );
							break;

						case 'duels__total':
							$value = sanitize_text_field( $player_stats['duels']['total'] ?? '' );
							break;

						case 'duels__won':
							$value = sanitize_text_field( $player_stats['duels']['won'] ?? '' );
							break;

						case 'dribbles__attempts':
							$value = sanitize_text_field( $player_stats['dribbles']['attempts'] ?? '' );
							break;

						case 'dribbles__success':
							$value = sanitize_text_field( $player_stats['dribbles']['success'] ?? '' );
							break;

						case 'dribbles__past':
							$value = sanitize_text_field( $player_stats['dribbles']['past'] ?? '' );
							break;

						case 'fouls__drawn':
							$value = sanitize_text_field( $player_stats['fouls']['drawn'] ?? '' );
							break;

						case 'fouls__committed':
							$value = sanitize_text_field( $player_stats['fouls']['committed'] ?? '' );
							break;
					}

					if ( null !== $value ) {
						$data_to_update[ 'c_id__' . absint( $api_stats_column->id ) ] = $value;
					}
				}

				if ( ! empty( $data_to_update ) ) {
					$wpdb->update(
						$wpdb->anwpfl_players,
						$data_to_update,
						[
							'match_id'  => $game_id,
							'player_id' => $player_id,
							'club_id'   => $club_id,
						]
					);
				}
			}
		}

		return true;
	}

	/**
	 * Parse and prepare game odds to save
	 *
	 * @param $game_odd
	 *
	 * @return array
	 * @since 0.14.3
	 */
	public function parse_odds( $game_odd ) {

		$output = [];

		foreach ( $game_odd as $game_bookmaker ) {

			if ( empty( $game_bookmaker['bets'] ) ) {
				continue;
			}

			foreach ( $game_bookmaker['bets'] as $game_bets ) {

				if ( empty( $game_bets['id'] ) || empty( $game_bets['name'] ) || empty( $game_bets['values'] ) ) {
					continue;
				}

				if ( ! isset( $output[ $game_bets['id'] ] ) ) {
					$output[ $game_bets['id'] ] = [
						'name'       => $game_bets['name'],
						'bookmakers' => [],
					];
				}

				$output[ $game_bets['id'] ]['bookmakers'][ $game_bookmaker['id'] ] = $game_bets['values'];
			}
		}

		return $output;
	}
}
