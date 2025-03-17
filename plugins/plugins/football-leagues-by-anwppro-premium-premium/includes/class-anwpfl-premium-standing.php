<?php
/**
 * AnWP Football Leagues Premium :: Standing
 *
 * @since 0.6.0
 */
class AnWPFL_Premium_Standing {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.1.0
	 * @param  AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// Save main plugin object
		$this->plugin = $plugin;

		// Init hooks
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		add_filter(
			'anwpfl/standing/vue_app_id',
			function () {
				return 'anwpfl-app-standing-premium';
			}
		);

		add_filter( 'anwpfl/standing/data_to_save', [ $this, 'save_premium_meta' ], 10, 3 );
		add_filter( 'anwpfl/standing/data_to_cache', [ $this, 'update_cache_data' ], 10, 2 );
		add_filter( 'anwpfl/standing/data_to_admin_vue', [ $this, 'add_premium_meta_to_admin_vue' ], 10, 2 );

		add_filter( 'anwpfl/standing/calculate_standing', [ $this, 'check_manual_standing_filling' ], 10, 2 );
		add_action( 'anwpfl/standing-calculating/before_save', [ $this, 'save_previous_round_places' ], 10, 3 );
		add_action( 'anwpfl/standing-calculating/after_save', [ $this, 'remove_standing_optional' ], 10, 1 );

		add_action( 'anwpfl/standing/on_save', [ $this, 'remove_series_on_manual_edit' ], 10, 2 );
		add_filter( 'anwpfl/tmpl-standing/columns_order', [ $this, 'change_columns_order' ], 10, 3 );

		// Custom standing rules
		add_filter( 'anwpfl/standing/custom_position_calculation', '__return_true' );
		add_filter( 'anwpfl/standing/custom_position_calculation_table', [ $this, 'custom_position_calculation' ], 10, 3 );

		// Works if Builder not used
		add_action( 'anwpfl/tmpl-competition/after_group_standing', [ $this, 'render_results_matrix' ], 10, 2 );

		add_filter( 'anwpfl/standing/fields_to_clone', [ $this, 'add_premium_meta_fields_clone' ] );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Register REST routes.
	 */
	public function add_rest_routes() {
		register_rest_route(
			'anwpfl/v1',
			'/standing/get_filtered_data/(?P<id>\d+)/(?P<args>[a-z_0-9-~:]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_filtered_standing_data' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);
	}

	public function get_filtered_standing_data( WP_REST_Request $request ) {

		$data = (object) wp_parse_args(
			$request->get_params(),
			[
				'id'   => '',
				'args' => '',
			]
		);

		$args = (object) wp_parse_args(
			AnWPFL_Premium_Helper::parse_rest_url_params( $data->args ),
			[
				't'  => '',
				'mw' => '',
			]
		);

		if ( empty( $data->id ) || empty( $args->t ) || ! in_array( $args->t, [ 'home', 'away', 'all' ], true ) ) {
			return new WP_Error( 'rest_invalid', 'Invalid Data', [ 'status' => 400 ] );
		}

		$table_data = json_decode( get_post_meta( $data->id, '_anwpfl_table_main_' . sanitize_key( $args->t . '_' . $args->mw ), true ) );

		if ( empty( $table_data ) ) {
			$table_data = $this->calculate_standing_optional( $data->id, $args->t, $args->mw );
		}

		if ( empty( $table_data ) ) {
			return new WP_Error( 'rest_invalid', 'Empty Data', [ 'status' => 400 ] );
		}

		$shortcode_attr = [
			'table'       => $table_data,
			'exclude_ids' => '',
			'id'          => $data->id,
			'type'        => $args->t,
			'matchweeks'  => $args->mw,
		];

		ob_start();
		anwp_football_leagues()->load_partial( $shortcode_attr, 'standing/standing' );
		$output_html = ob_get_clean();

		return rest_ensure_response(
			[
				'data' => $output_html,
			]
		);
	}

	/**
	 * Recalculate Standing Table
	 *
	 * @param int    $standing_id
	 * @param string $type
	 * @param string $matchweeks
	 *
	 * @since 0.14.18
	 */
	public function calculate_standing_optional( $standing_id, $type = 'home', $matchweeks = '' ) {

		$data = anwp_football_leagues()->standing->get_standing( $standing_id );

		if ( empty( $data ) || AnWP_Football_Leagues::string_to_bool( $data->manual_ordering ) ) {
			return [];
		}

		$data = (array) $data;

		global $wpdb;

		// Prepare empty table
		$table = [];

		foreach ( anwp_football_leagues()->competition->get_competition_clubs( $data['competition'], $data['group'] ) as $club ) {

			if ( (int) $club ) {
				$table[ $club ] = [
					'club_id'    => $club,
					'club_title' => anwp_football_leagues()->club->get_club_title_by_id( $club ),
					'place'      => 0,
					'played'     => 0,
					'won'        => 0,
					'drawn'      => 0,
					'lost'       => 0,
					'gf'         => 0,
					'ga'         => 0,
					'gd'         => 0,
					'points'     => 0,
					'series'     => '',
				];
			}
		}

		/*
		|--------------------------------------------------------------------
		| Get Games
		|--------------------------------------------------------------------
		*/
		$query = $wpdb->prepare(
			"
			SELECT home_club, away_club, home_goals, away_goals, match_id, match_week, home_cards_y, away_cards_y, home_cards_yr, away_cards_yr, home_cards_r, away_cards_r
			FROM {$wpdb->prefix}anwpfl_matches
			WHERE competition_id = %d AND group_id = %d AND finished = 1
			",
			$data['competition'],
			$data['group']
		);

		if ( ! empty( $matchweeks ) ) {
			$matchweeks_arr = explode( '-', $matchweeks, 2 );
			$matchweek_from = absint( $matchweeks_arr[0] );
			$matchweek_to   = isset( $matchweeks_arr[1] ) ? absint( $matchweeks_arr[1] ) : absint( $matchweeks_arr[0] );

			$query .= $wpdb->prepare( ' AND match_week >= %d AND match_week <= %d ', $matchweek_from, $matchweek_to );
		}

		$query .= 'ORDER BY kickoff';

		$matches = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		/*
		|--------------------------------------------------------------------
		| Get games with custom outcome
		|--------------------------------------------------------------------
		*/
		$custom_outcomes = anwp_football_leagues()->standing->get_games_with_custom_outcome( $data['competition'], $data['group'] );

		// Populate stats
		foreach ( $matches as $match ) {

			if ( ! empty( $custom_outcomes ) && in_array( $match->match_id, $custom_outcomes, true ) ) {

				if ( 'away' !== $type ) {
					$home_outcome = get_post_meta( $match->match_id, '_anwpfl_outcome_home', true );

					switch ( $home_outcome ) {
						case 'won':
							$table[ $match->home_club ]['won'] ++;
							$table[ $match->home_club ]['series'] .= 'w';
							break;

						case 'drawn':
							$table[ $match->home_club ]['drawn'] ++;
							$table[ $match->home_club ]['series'] .= 'd';
							break;

						case 'lost':
							$table[ $match->home_club ]['lost'] ++;
							$table[ $match->home_club ]['series'] .= 'l';
							break;
					}
				}

				if ( 'home' !== $type ) {
					$away_outcome = get_post_meta( $match->match_id, '_anwpfl_outcome_away', true );

					switch ( $away_outcome ) {
						case 'won':
							$table[ $match->away_club ]['won'] ++;
							$table[ $match->away_club ]['series'] .= 'w';
							break;

						case 'drawn':
							$table[ $match->away_club ]['drawn'] ++;
							$table[ $match->away_club ]['series'] .= 'd';
							break;

						case 'lost':
							$table[ $match->away_club ]['lost'] ++;
							$table[ $match->away_club ]['series'] .= 'l';
							break;
					}
				}
			} else {
				if ( $match->home_goals > $match->away_goals ) {

					// Home Club
					if ( 'away' !== $type ) {
						$table[ $match->home_club ]['won'] ++;
						$table[ $match->home_club ]['series'] .= 'w';
					}

					// Away Club
					if ( 'home' !== $type ) {
						$table[ $match->away_club ]['lost'] ++;
						$table[ $match->away_club ]['series'] .= 'l';
					}
				} elseif ( $match->home_goals === $match->away_goals ) {

					// Home Club
					if ( 'away' !== $type ) {
						$table[ $match->home_club ]['drawn'] ++;
						$table[ $match->home_club ]['series'] .= 'd';
					}

					// Away Club
					if ( 'home' !== $type ) {
						$table[ $match->away_club ]['drawn'] ++;
						$table[ $match->away_club ]['series'] .= 'd';
					}
				} else {

					// Home Club
					if ( 'away' !== $type ) {
						$table[ $match->home_club ]['lost'] ++;
						$table[ $match->home_club ]['series'] .= 'l';
					}

					// Away Club
					if ( 'home' !== $type ) {
						$table[ $match->away_club ]['won'] ++;
						$table[ $match->away_club ]['series'] .= 'w';
					}
				}
			}

			if ( 'away' !== $type ) {
				$table[ $match->home_club ]['gf'] += $match->home_goals;
				$table[ $match->home_club ]['ga'] += $match->away_goals;
				$table[ $match->home_club ]['played'] ++;
			}

			if ( 'home' !== $type ) {
				$table[ $match->away_club ]['gf'] += $match->away_goals;
				$table[ $match->away_club ]['ga'] += $match->home_goals;
				$table[ $match->away_club ]['played'] ++;
			}
		}

		// Calculate others fields
		foreach ( $table as $club_id => $club ) {
			$table[ $club_id ]['points'] = $club['won'] * (int) $data['win'] + $club['drawn'] * (int) $data['draw'] + $club['lost'] * (int) $data['loss'];
			$table[ $club_id ]['gd']     = $club['gf'] - $club['ga'];
		}

		/*
		|--------------------------------------------------------------------
		| Custom Outcome Points
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $custom_outcomes ) && is_array( $custom_outcomes ) ) {
			foreach ( $custom_outcomes as $custom_outcome ) {
				$outcome_game_data = anwp_fl()->match->get_game_data( $custom_outcome );

				if ( 'away' !== $type ) {
					$home_outcome = get_post_meta( $custom_outcome, '_anwpfl_outcome_home', true );
					$home_club    = $outcome_game_data['home_club'] ?? '';
					$home_points  = get_post_meta( $custom_outcome, '_anwpfl_outcome_points_home', true );

					if ( isset( $table[ $home_club ]['points'] ) ) {

						$points_added = (int) $data['win'];

						switch ( $home_outcome ) {
							case 'drawn':
								$points_added = (int) $data['draw'];
								break;

							case 'lost':
								$points_added = (int) $data['loss'];
								break;
						}

						$table[ $home_club ]['points'] = $table[ $home_club ]['points'] - $points_added + absint( $home_points );
					}
				}

				if ( 'home' !== $type ) {
					$away_outcome = get_post_meta( $custom_outcome, '_anwpfl_outcome_away', true );
					$away_club    = $outcome_game_data['away_club'] ?? '';
					$away_points  = get_post_meta( $custom_outcome, '_anwpfl_outcome_points_away', true );

					if ( isset( $table[ $away_club ]['points'] ) ) {

						$points_added = (int) $data['win'];

						switch ( $away_outcome ) {
							case 'drawn':
								$points_added = (int) $data['draw'];
								break;

							case 'lost':
								$points_added = (int) $data['loss'];
								break;
						}

						$table[ $away_club ]['points'] = $table[ $away_club ]['points'] - $points_added + absint( $away_points );
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Add initial points
		|--------------------------------------------------------------------
		*/
		if ( $data['points_initial'] ) {
			$initial = json_decode( wp_unslash( $data['points_initial'] ) );

			if ( ! empty( $initial ) && is_object( $initial ) ) {
				foreach ( $initial as $club_id => $points_to_add ) {
					$table[ $club_id ]['points'] = $table[ $club_id ]['points'] + (int) $points_to_add;
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Check Initial Table Data
		|--------------------------------------------------------------------
		*/
		$is_initial_data_active = $data['is_initial_data_active'] ?? get_post_meta( $data['id'], '_anwpfl_is_initial_data_active', true );

		if ( AnWP_Football_Leagues::string_to_bool( $is_initial_data_active ) ) {
			$initial_data = json_decode( get_post_meta( $data['id'], '_anwpfl_table_initial', true ) );

			$table_fields = [
				'played',
				'won',
				'drawn',
				'lost',
				'gf',
				'ga',
				'gd',
				'points',
			];

			foreach ( $initial_data as $row_club_id => $data_row ) {
				foreach ( $table_fields as $table_field ) {
					if ( isset( $table[ $row_club_id ][ $table_field ] ) && ! empty( $data_row->{$table_field} ) ) {
						$table[ $row_club_id ][ $table_field ] += (int) $data_row->{$table_field};
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Ordering
		|--------------------------------------------------------------------
		*/
		$table = $this->custom_position_calculation( $table, $data, $matches );

		// Set Place field
		$place_counter = 1;
		foreach ( $table as $index => $row ) {
			$table[ $index ]['place'] = $place_counter ++;
		}

		/*
		|--------------------------------------------------------------------
		| Save to DB
		|--------------------------------------------------------------------
		*/
		if ( 'home' === $type ) {
			update_post_meta( $standing_id, '_anwpfl_table_main_home', wp_json_encode( $table ) );
		} elseif ( 'away' === $type ) {
			update_post_meta( $standing_id, '_anwpfl_table_main_away', wp_json_encode( $table ) );
		}

		return json_decode( wp_json_encode( $table ) );
	}

	/**
	 * Remove optional Standing data
	 *
	 * @param array $data
	 *
	 * @since 0.14.18
	 */
	public function remove_standing_optional( $data ) {
		$standing_id = $data['id'] ?? 0;

		if ( ! empty( $standing_id ) ) {
			delete_post_meta( $standing_id, '_anwpfl_table_main_home' );
			delete_post_meta( $standing_id, '_anwpfl_table_main_away' );
		}
	}

	/**
	 * Custom position calculation.
	 *
	 * @param array $table
	 * @param array $data
	 * @param array $matches
	 *
	 * @return array
	 * @since 0.6.0
	 */
	public function custom_position_calculation( $table, $data, $matches ) {

		/* v0.6.0
		Available rules:
		- goals_difference,
		- goals_scored,
		- wins,
		- away_goal_difference,
		- away_goals_scored,
		- away_wins,
		- head_to_head_goal_difference,
		- head_to_head_goals_scored,
		- head_to_head_away_goals_scored,
		- head_to_head_points,
		- head_to_head_wins
		- played_games (v0.11.8)
		- less_yellow_cards (v0.14.5)
		- less_red_cards (v0.14.5)
		 */

		// Clone table to include temp data
		$sorting_table = $table;

		// Sorting rules
		$rules = explode( ',', $data['ranking_rules'] );

		// Initial sorting by points
		$sorting_table = wp_list_sort( $sorting_table, 'points', 'DESC' );

		// Prepare Sorting Data
		$sorting_data = [];

		foreach ( $sorting_table as $row ) {
			$sorting_data[ $row['club_id'] ] = [
				'club_id'          => $row['club_id'],
				'club_title'       => anwp_football_leagues()->club->get_club_title_by_id( $row['club_id'] ),
				'place'            => 0,
				'points'           => $row['points'],
				'goals_difference' => $row['gd'],
				'goals_scored'     => $row['gf'],
				'wins'             => $row['won'],
				'played_games'     => $row['played'],
			];
		}

		// Add data for other sorting types (AWAY)
		if ( array_intersect( $rules, [ 'away_goal_difference', 'away_goals_scored', 'away_wins' ] ) ) {
			$sorting_data = $this->set_standing_away_stats( $sorting_data, $matches );
		}

		// Add data for cards sorting types
		if ( array_intersect( $rules, [ 'less_yellow_cards', 'less_red_cards' ] ) ) {
			$sorting_data = $this->set_standing_cards_stats( $sorting_data, $matches );
		}

		// Add data for other sorting types (H2H)
		if ( array_intersect(
			$rules,
			[
				'head_to_head_goal_difference',
				'head_to_head_goals_scored',
				'head_to_head_away_goals_scored',
				'head_to_head_points',
				'head_to_head_wins',
			]
		) ) {

			$points = [
				'win'  => $data['win'],
				'draw' => $data['draw'],
				'loss' => $data['loss'],
			];

			$sorting_data = $this->set_standing_h2h_stats( $sorting_data, $matches, $points );
		}

		// Prepare Sorting Order
		$sorting_order = [ 'points' => 'DESC' ];

		foreach ( $rules as $rule ) {
			if ( in_array( $rule, [ 'played_games', 'less_yellow_cards', 'less_red_cards' ], true ) ) {
				$sorting_order[ $rule ] = 'ASC';
			} else {
				$sorting_order[ $rule ] = 'DESC';
			}
		}

		$sorting_order['club_title'] = 'ASC';

		// Sort
		$sorting_data = wp_list_sort( $sorting_data, $sorting_order );

		foreach ( $sorting_data as $index => $row ) {
			$table[ $row['club_id'] ]['place'] = $index + 1;
		}

		return wp_list_sort( $table, 'place' );
	}

	/**
	 * Populate sorting data with H2H stats.
	 *
	 * @param $sorting_data
	 * @param $matches
	 * @param $points
	 *
	 * @return array
	 * @since 0.6.0
	 */
	private function set_standing_h2h_stats( $sorting_data, $matches, $points ) {

		$sorting_data_grouped = [];
		foreach ( $sorting_data as $club_id => $row ) {
			$sorting_data_grouped [ $row['points'] ][] = $row;

			$sorting_data[ $club_id ]['head_to_head_goal_difference']   = 0;
			$sorting_data[ $club_id ]['head_to_head_goals_scored']      = 0;
			$sorting_data[ $club_id ]['head_to_head_away_goals_scored'] = 0;
			$sorting_data[ $club_id ]['head_to_head_points']            = 0;
			$sorting_data[ $club_id ]['head_to_head_wins']              = 0;
			$sorting_data[ $club_id ]['h2h_lost']                       = 0;
			$sorting_data[ $club_id ]['h2h_drawn']                      = 0;
			$sorting_data[ $club_id ]['h2h_goals_lost']                 = 0;
		}

		foreach ( $sorting_data_grouped as $group ) {
			if ( count( $group ) < 2 ) {
				continue;
			}

			$clubs = wp_list_pluck( $group, 'club_id' );

			foreach ( $matches as $match ) {

				if ( ! in_array( (int) $match->away_club, $clubs, true ) || ! in_array( (int) $match->home_club, $clubs, true ) ) {
					continue;
				}

				if ( $match->home_goals > $match->away_goals ) {
					$sorting_data[ $match->home_club ]['head_to_head_wins'] ++;
					$sorting_data[ $match->away_club ]['h2h_lost'] ++;
				} elseif ( $match->home_goals === $match->away_goals ) {
					$sorting_data[ $match->home_club ]['h2h_drawn'] ++;
					$sorting_data[ $match->away_club ]['h2h_drawn'] ++;
				} else {
					$sorting_data[ $match->away_club ]['head_to_head_wins'] ++;
					$sorting_data[ $match->home_club ]['h2h_lost'] ++;
				}

				$sorting_data[ $match->home_club ]['head_to_head_goals_scored'] += $match->home_goals;
				$sorting_data[ $match->away_club ]['head_to_head_goals_scored'] += $match->away_goals;

				$sorting_data[ $match->home_club ]['h2h_goals_lost'] += $match->away_goals;
				$sorting_data[ $match->away_club ]['h2h_goals_lost'] += $match->home_goals;

				$sorting_data[ $match->away_club ]['head_to_head_away_goals_scored'] += $match->away_goals;
			}

			// Calculate others fields
			foreach ( $sorting_data as $club_id => $club ) {
				// Points
				$sorting_data[ $club_id ]['head_to_head_points'] = $club['head_to_head_wins'] * (int) $points['win'] + $club['h2h_drawn'] * (int) $points['draw'] + $club['h2h_lost'] * (int) $points['loss'];

				// Goal Deference
				$sorting_data[ $club_id ]['head_to_head_goal_difference'] = $club['head_to_head_goals_scored'] - $club['h2h_goals_lost'];
			}
		}

		return $sorting_data;
	}

	/**
	 * Populate sorting data with away stats.
	 * 'away_goal_difference', 'away_goals_scored', 'away_wins'
	 *
	 * @param $sorting_data
	 * @param $matches
	 *
	 * @return array
	 * @since 0.6.0
	 */
	private function set_standing_away_stats( $sorting_data, $matches ) {

		// Add new fields
		foreach ( $sorting_data as $club_id => $row ) {
			$sorting_data[ $club_id ]['away_wins']            = 0;
			$sorting_data[ $club_id ]['away_goals_scored']    = 0;
			$sorting_data[ $club_id ]['away_goals_lost']      = 0;
			$sorting_data[ $club_id ]['away_goal_difference'] = 0;
		}

		foreach ( $matches as $match ) {

			if ( $match->home_goals < $match->away_goals ) {
				$sorting_data[ $match->away_club ]['away_wins'] ++;
			}

			$sorting_data[ $match->away_club ]['away_goals_scored'] += $match->away_goals;
			$sorting_data[ $match->away_club ]['away_goals_lost']   += $match->home_goals;
		}

		foreach ( $sorting_data as $club_id => $club ) {
			$sorting_data[ $club_id ]['away_goal_difference'] = $club['away_goals_scored'] - $club['away_goals_lost'];
		}

		return $sorting_data;
	}

	/**
	 * Populate sorting data with cards stats.
	 * 'less_yellow_cards', 'less_red_cards'
	 *
	 * @param $sorting_data
	 * @param $matches
	 *
	 * @return array
	 * @since 0.14.5
	 */
	private function set_standing_cards_stats( $sorting_data, $matches ) {

		// Add new fields
		foreach ( $sorting_data as $club_id => $row ) {
			$sorting_data[ $club_id ]['less_yellow_cards'] = 0;
			$sorting_data[ $club_id ]['less_red_cards']    = 0;
		}

		foreach ( $matches as $match ) {
			$sorting_data[ $match->home_club ]['less_yellow_cards'] += absint( $match->home_cards_y );
			$sorting_data[ $match->home_club ]['less_red_cards']    += absint( $match->home_cards_yr ) + absint( $match->home_cards_r );
			$sorting_data[ $match->away_club ]['less_yellow_cards'] += absint( $match->away_cards_y );
			$sorting_data[ $match->away_club ]['less_red_cards']    += absint( $match->away_cards_yr ) + absint( $match->away_cards_r );
		}

		return $sorting_data;
	}

	/**
	 * Change order for standing columns.
	 *
	 * @param array  $columns
	 * @param int    $standing_id Standing ID
	 * @param string $layout      Standing ID
	 *
	 * @return array
	 * @since 0.6.0
	 */
	public function change_columns_order( $columns, $standing_id, $layout ) {

		$field_map = [
			'mini'       => '_anwpfl_columns_mini_order',
			'mini-sm'    => '_anwpfl_columns_mini_order_sm',
			'default-sm' => '_anwpfl_columns_order_sm',
			'default-xs' => '_anwpfl_columns_order_xs',
		];

		$meta_field    = isset( $field_map[ $layout ] ) ? $field_map[ $layout ] : '_anwpfl_columns_order';
		$columns_order = json_decode( get_post_meta( $standing_id, $meta_field, true ) );

		if ( $columns_order && is_array( $columns_order ) ) {

			$new_columns = [];

			foreach ( $columns_order as $col ) {
				if ( AnWP_Football_Leagues::string_to_bool( $col->display ) ) {
					$new_columns[] = $col->slug;
				}
			}
		}

		return empty( $new_columns ) ? $columns : $new_columns;
	}

	/**
	 * Prevent Standing calculation on manual data filling.
	 *
	 * @param array $data
	 * @param int  $standing_id Standing ID
	 *
	 * @since 0.6.0
	 */
	public function remove_series_on_manual_edit( $data, $standing_id ) {

		if ( 'true' === $data['_anwpfl_manual_filling'] ) {

			$table_main = json_decode( stripslashes( $_POST['_anwpfl_table_main'] ) ); // WPCS: CSRF ok.

			if ( $table_main && is_array( $table_main ) && ! empty( $table_main ) ) {

				foreach ( $table_main as $index => $row ) {
					$table_main[ $index ]->series = '';
				}

				update_post_meta( $standing_id, '_anwpfl_table_main', wp_slash( wp_json_encode( $table_main ) ) );
			}
		}
	}

	/**
	 * Prevent Standing calculation on manual data filling.
	 *
	 * @param bool $calculate_standing
	 * @param int  $standing_id Standing ID
	 *
	 * @return bool
	 * @since 0.6.0
	 */
	public function check_manual_standing_filling( $calculate_standing, $standing_id ) {

		if ( 'true' === get_post_meta( $standing_id, '_anwpfl_manual_filling', true ) ) {
			return false;
		}

		return $calculate_standing;
	}

	/**
	 * Add premium meta fields to the saving standing data.
	 *
	 * @param array $data    Match data
	 * @param int   $post_id Standing ID
	 * @param array $post_data
	 *
	 * @return array
	 * @since 0.6.0
	 */
	public function save_premium_meta( $data, $post_id, $post_data ) {

		// Manual data filling
		$data['_anwpfl_manual_filling'] = sanitize_key( $post_data['_anwpfl_manual_filling'] );

		// Conference support
		$data['_anwpfl_conferences_support'] = sanitize_key( $post_data['_anwpfl_conferences_support'] );
		if ( AnWP_Football_Leagues::string_to_bool( $data['_anwpfl_conferences_support'] ) ) {
			$main_table       = json_decode( $post_data['_anwpfl_table_main'] );
			$club_conferences = [];

			if ( $main_table && is_array( $main_table ) ) {
				foreach ( $main_table as $row ) {
					$club_conferences[ $row->club_id ] = $row->conference;
				}

				$data['_anwpfl_club_conferences'] = wp_slash( wp_json_encode( $club_conferences ) );
			}
		}

		// Columns order
		$columns_order         = wp_json_encode( json_decode( $post_data['_anwpfl_columns_order'] ) );
		$columns_order_sm      = wp_json_encode( json_decode( $post_data['_anwpfl_columns_order_sm'] ) );
		$columns_order_xs      = wp_json_encode( json_decode( $post_data['_anwpfl_columns_order_xs'] ) );
		$columns_mini_order    = wp_json_encode( json_decode( $post_data['_anwpfl_columns_mini_order'] ) );
		$columns_mini_order_sm = wp_json_encode( json_decode( $post_data['_anwpfl_columns_mini_order_sm'] ) );

		if ( $columns_order ) {
			$data['_anwpfl_columns_order']         = wp_slash( $columns_order );
			$data['_anwpfl_columns_order_sm']      = wp_slash( $columns_order_sm );
			$data['_anwpfl_columns_order_xs']      = wp_slash( $columns_order_xs );
			$data['_anwpfl_columns_mini_order']    = wp_slash( $columns_mini_order );
			$data['_anwpfl_columns_mini_order_sm'] = wp_slash( $columns_mini_order_sm );
		}

		if ( ! empty( sanitize_key( $post_data['_anwpfl_arrows_dynamic_ranking'] ) ) ) {
			$data['_anwpfl_arrows_dynamic_ranking'] = sanitize_key( $post_data['_anwpfl_arrows_dynamic_ranking'] );
		} else {
			delete_post_meta( $post_id, '_anwpfl_arrows_dynamic_ranking' );
		}

		return $data;
	}

	/**
	 * Update cache data
	 *
	 * @param array   $standing_data
	 * @param int     $standing_id
	 *
	 * @return array
	 * @since 0.14.4
	 */
	public function update_cache_data( $standing_data, $standing_id ) {

		$standing_data_premium = [
			'manual_filling'         => get_post_meta( $standing_id, '_anwpfl_manual_filling', true ),
			'conferences_support'    => get_post_meta( $standing_id, '_anwpfl_conferences_support', true ),
			'club_conferences'       => get_post_meta( $standing_id, '_anwpfl_club_conferences', true ),
			'columns_order'          => get_post_meta( $standing_id, '_anwpfl_columns_order', true ),
			'columns_mini_order'     => get_post_meta( $standing_id, '_anwpfl_columns_mini_order', true ),
			'arrows_dynamic_ranking' => get_post_meta( $standing_id, '_anwpfl_arrows_dynamic_ranking', true ),
		];

		return array_merge( $standing_data, $standing_data_premium );
	}

	/**
	 * Add premium meta fields to clone
	 *
	 * @param array $meta_keys Meta keys array
	 *
	 * @return array
	 * @since 0.11.1
	 */
	public function add_premium_meta_fields_clone( $meta_keys ) {
		$meta_keys[] = '_anwpfl_manual_filling';
		$meta_keys[] = '_anwpfl_conferences_support';
		$meta_keys[] = '_anwpfl_club_conferences';
		$meta_keys[] = '_anwpfl_columns_order';
		$meta_keys[] = '_anwpfl_columns_mini_order';

		return $meta_keys;
	}

	/**
	 * Add premium meta data to the localized array to use in admin Standing UI
	 *
	 * @param array $data    Match data
	 * @param int   $post_id Standing ID
	 *
	 * @return array
	 * @since 0.6.0
	 */
	public function add_premium_meta_to_admin_vue( $data, $post_id ) {

		$manual_filling        = AnWP_Football_Leagues::string_to_bool( get_post_meta( $post_id, '_anwpfl_manual_filling', true ) );
		$data['manualFilling'] = $manual_filling ? 'yes' : '';

		$conferences_support        = AnWP_Football_Leagues::string_to_bool( get_post_meta( $post_id, '_anwpfl_conferences_support', true ) );
		$data['conferencesSupport'] = $conferences_support ? 'yes' : '';

		if ( $conferences_support ) {
			$table_main       = json_decode( get_post_meta( $post_id, '_anwpfl_table_main', true ) );
			$club_conferences = json_decode( get_post_meta( $post_id, '_anwpfl_club_conferences', true ) );

			if ( $table_main && $club_conferences ) {

				foreach ( $table_main as $index => $row ) {
					$club_id = $table_main[ $index ]->club_id;

					$table_main[ $index ]->conference = empty( $club_conferences->{$club_id} ) ? '' : $club_conferences->{$club_id};
				}

				$data['tableMain'] = wp_json_encode( $table_main );
			}
		}

		$data['l10n_premium'] = [
			'automatic_data_filling'             => __( 'Automatic data filling', 'anwp-football-leagues-premium' ),
			'conference'                         => __( 'Conference', 'anwp-football-leagues-premium' ),
			'conferences_support'                => __( 'Conferences Support', 'anwp-football-leagues-premium' ),
			'display_options'                    => __( 'Display Options', 'anwp-football-leagues-premium' ),
			'global_settings'                    => __( 'Global Settings', 'anwp-football-leagues' ),
			'layout_mini_widget'                 => __( 'Layout Mini (Widget)', 'anwp-football-leagues-premium' ),
			'no'                                 => __( 'No', 'anwp-football-leagues' ),
			'ranking_rules_notes_pro_2'          => __( 'Only works if Automatic Position Calculation is set to "YES"', 'anwp-football-leagues-premium' ),
			'ranking_rules_notes_pro_3'          => __( 'The following criteria are applied in the order from top to bottom.', 'anwp-football-leagues-premium' ),
			'show_arrows_dynamic_ranking'        => __( 'Show arrows (dynamic of ranking change)', 'anwp-football-leagues-premium' ),
			'table_columns_order_and_visibility' => __( 'Table columns (order and visibility)', 'anwp-football-leagues-premium' ),
			'yes'                                => __( 'Yes', 'anwp-football-leagues' ),
		];

		$data['arrowsDynamicRanking'] = get_post_meta( $post_id, '_anwpfl_arrows_dynamic_ranking', true );
		$data['columnsOrder']         = json_decode( get_post_meta( $post_id, '_anwpfl_columns_order', true ) );
		$data['columnsOrderSM']       = json_decode( get_post_meta( $post_id, '_anwpfl_columns_order_sm', true ) );
		$data['columnsOrderXS']       = json_decode( get_post_meta( $post_id, '_anwpfl_columns_order_xs', true ) );
		$data['columnsMiniOrder']     = json_decode( get_post_meta( $post_id, '_anwpfl_columns_mini_order', true ) );
		$data['columnsMiniOrderSM']   = json_decode( get_post_meta( $post_id, '_anwpfl_columns_mini_order_sm', true ) );

		if ( empty( $data['columnsOrder'] ) ) {

			$columns = [
				'played' => true,
				'won'    => true,
				'drawn'  => true,
				'lost'   => true,
				'gf'     => true,
				'ga'     => true,
				'gd'     => true,
				'points' => true,
			];

			$data['columnsOrder'] = [];

			foreach ( $columns as $slug => $display ) {
				$data['columnsOrder'][] = (object) [
					'display' => $display,
					'slug'    => $slug,
				];
			}
		}

		if ( empty( $data['columnsOrderSM'] ) ) {

			$columns = [
				'played' => true,
				'won'    => false,
				'drawn'  => false,
				'lost'   => false,
				'gf'     => false,
				'ga'     => false,
				'gd'     => true,
				'points' => true,
			];

			$data['columnsOrderSM'] = [];

			foreach ( $columns as $slug => $display ) {
				$data['columnsOrderSM'][] = (object) [
					'display' => $display,
					'slug'    => $slug,
				];
			}
		}

		if ( empty( $data['columnsOrderXS'] ) ) {

			$columns = [
				'played' => true,
				'won'    => false,
				'drawn'  => false,
				'lost'   => false,
				'gf'     => false,
				'ga'     => false,
				'gd'     => true,
				'points' => true,
			];

			$data['columnsOrderXS'] = [];

			foreach ( $columns as $slug => $display ) {
				$data['columnsOrderXS'][] = (object) [
					'display' => $display,
					'slug'    => $slug,
				];
			}
		}

		if ( empty( $data['columnsMiniOrder'] ) ) {

			$columns = [
				'played' => true,
				'won'    => true,
				'drawn'  => true,
				'lost'   => true,
				'points' => true,
			];

			$data['columnsMiniOrder'] = [];

			foreach ( $columns as $slug => $display ) {
				$data['columnsMiniOrder'][] = (object) [
					'display' => $display,
					'slug'    => $slug,
				];
			}
		}

		if ( empty( $data['columnsMiniOrderSM'] ) ) {

			$columns = [
				'played' => true,
				'won'    => false,
				'drawn'  => false,
				'lost'   => false,
				'points' => true,
			];

			$data['columnsMiniOrderSM'] = [];

			foreach ( $columns as $slug => $display ) {
				$data['columnsMiniOrderSM'][] = (object) [
					'display' => $display,
					'slug'    => $slug,
				];
			}
		}

		return $data;
	}

	/**
	 * Render Results Matrix
	 * Used if Builder Layout not exists
	 *
	 * @param object  $group
	 * @param WP_Post $competition
	 *
	 * @return mixed|void
	 * @since 0.6.0 (2018-10-12)
	 */
	public function render_results_matrix( $group, $competition ) {

		$shortcode_attr = [
			'competition_id' => $competition->ID,
			'group_id'       => $group->id,
		];

		echo '<div class="my-4">';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo anwp_football_leagues()->template->shortcode_loader( 'premium-results-matrix', $shortcode_attr );
		echo '</div>';
	}

	/**
	 * Get clubs form. Used for Standing table.
	 *
	 * @param array $ids
	 * @param int   $standing_id
	 *
	 * @return array
	 * @since 0.9.3
	 */
	public function get_clubs_form( $ids, $standing_id, $type = 'all', $matchweeks = '' ) {

		$ids = array_map( 'absint', $ids );

		if ( empty( $ids ) ) {
			return [];
		}

		$series_map = anwp_football_leagues()->data->get_series();

		$matches_options = [
			'filter_by_clubs'      => $ids,
			'type'                 => 'result',
			'sort_by_date'         => 'desc',
			'filter_by_matchweeks' => $matchweeks,
			'group_id'             => get_post_meta( absint( $standing_id ), '_anwpfl_competition_group', true ),
			'competition_id'       => get_post_meta( absint( $standing_id ), '_anwpfl_competition', true ),
		];

		// Get latest matches
		$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $matches_options );

		$data = array_fill_keys(
			$ids,
			[
				'matches' => [],
				'html'    => '',
			]
		);

		foreach ( $matches as $match ) {
			if ( isset( $data[ $match->home_club ] ) && 'away' !== $type ) {
				$data[ $match->home_club ]['matches'][] = $match;
			}

			if ( isset( $data[ $match->away_club ] ) && 'home' !== $type ) {
				$data[ $match->away_club ]['matches'][] = $match;
			}
		}

		foreach ( $data as $club_id => $club ) {
			$club['matches'] = array_slice( $club['matches'], 0, 5 );

			if ( ! empty( $club['matches'] ) ) {
				$club['matches'] = array_reverse( $club['matches'] );
			}

			ob_start();
			?>
			<div class="club-form d-flex align-items-center">
				<?php
				foreach ( $club['matches'] as $match ) :
					if ( ( absint( $club_id ) === absint( $match->home_club ) && $match->home_goals > $match->away_goals ) || absint( $club_id ) === absint( $match->away_club ) && $match->away_goals > $match->home_goals ) {
						$outcome_label = $series_map['w'];
						$outcome_class = 'anwp-bg-success';
					} elseif ( ( absint( $club_id ) === absint( $match->home_club ) && $match->home_goals < $match->away_goals ) || absint( $club_id ) === absint( $match->away_club ) && $match->away_goals < $match->home_goals ) {
						$outcome_label = $series_map['l'];
						$outcome_class = 'anwp-bg-danger';
					} else {
						$outcome_label = $series_map['d'];
						$outcome_class = 'anwp-bg-warning';
					}
					?>
					<span data-anwp-fl-match-tooltip data-match-id="<?php echo absint( $match->match_id ); ?>" class="d-inline-block club-form__item-pro anwp-text-white anwp-text-uppercase anwp-text-monospace <?php echo esc_attr( $outcome_class ); ?>">
						<?php echo esc_html( $outcome_label ); ?>
					</span>
				<?php endforeach; ?>
			</div>
			<?php
			$data[ $club_id ]['html'] = ob_get_clean();
		}

		return wp_list_pluck( $data, 'html' );
	}

	/**
	 * Get clubs form next match. Used for Standing table.
	 *
	 * @param array $ids
	 * @param int   $standing_id
	 *
	 * @return array
	 * @since 0.9.3
	 */
	public function get_clubs_form_next( $ids, $standing_id, $type = 'all' ) {

		$ids = array_map( 'absint', $ids );

		if ( empty( $ids ) ) {
			return [];
		}

		$matches_options = [
			'filter_by_clubs' => $ids,
			'type'            => 'fixture',
			'sort_by_date'    => 'asc',
			'group_id'        => get_post_meta( absint( $standing_id ), '_anwpfl_competition_group', true ),
			'competition_id'  => get_post_meta( absint( $standing_id ), '_anwpfl_competition', true ),
		];

		// Get latest matches
		$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $matches_options );

		$data = array_fill_keys(
			$ids,
			[
				'matches' => [],
				'html'    => '',
			]
		);

		foreach ( $matches as $match ) {
			if ( isset( $data[ $match->home_club ] ) && 'away' !== $type ) {
				$data[ $match->home_club ]['matches'][] = $match;
			}

			if ( isset( $data[ $match->away_club ] ) && 'home' !== $type ) {
				$data[ $match->away_club ]['matches'][] = $match;
			}
		}

		foreach ( $data as $club_id => $club ) {
			$club['matches'] = array_slice( $club['matches'], 0, 1 );

			ob_start();
			?>
			<div class="club-form d-flex align-items-center">
				<?php foreach ( $club['matches'] as $match ) : ?>
					<span data-anwp-fl-match-tooltip data-match-id="<?php echo absint( $match->match_id ); ?>" class="d-inline-block club-form__item-pro anwp-text-white anwp-text-monospace anwp-bg-secondary">
						>
					</span>
				<?php endforeach; ?>
			</div>
			<?php
			$data[ $club_id ]['html'] = ob_get_clean();
		}

		return wp_list_pluck( $data, 'html' );
	}

	/**
	 * Get List of Standing IDs by Competition ID
	 *
	 * @param int $competition_id
	 *
	 * @return array
	 * @since 0.11.2
	 */
	public function get_standings_by_competition_id( $competition_id ) {

		if ( ! absint( $competition_id ) ) {
			return [];
		}

		$args = [
			'post_type'   => 'anwp_standing',
			'numberposts' => - 1,
			'meta_key'    => '_anwpfl_competition',
			'meta_value'  => absint( $competition_id ),
			'fields'      => 'ids',
			'orderby'     => 'title',
			'order'       => 'ASC',
		];

		return get_posts( $args );
	}

	/**
	 * Get List of Standing IDs by Match ID
	 *
	 * @param int $match_id
	 *
	 * @return array
	 * @since 0.11.2
	 */
	public function get_standings_by_match_id( $match_id ) {

		if ( ! absint( $match_id ) ) {
			return [];
		}

		return $this->get_standings_by_competition_id( get_post_meta( $match_id, '_anwpfl_competition', true ) );
	}

	/**
	 * Get Standing ID by Match ID (in array)
	 *
	 * @param int $match_id
	 *
	 * @return array
	 * @since 0.14.9
	 */
	public function get_standing_by_match_id( $match_id ) {

		if ( ! absint( $match_id ) ) {
			return [];
		}

		// Get competition & competitionGroup
		$competition_id = (int) get_post_meta( $match_id, '_anwpfl_competition', true );
		$group_id       = (int) get_post_meta( $match_id, '_anwpfl_competition_group', true );

		if ( ! $competition_id || ! $group_id ) {
			return [];
		}

		foreach ( anwp_football_leagues()->standing->get_standings() as $standing_data ) {
			if ( absint( $competition_id ) === $standing_data['competition'] && absint( $group_id ) === $standing_data['group'] ) {
				return [ $standing_data['id'] ];
			}
		}

		return [];
	}

	/**
	 * Get List of Standing IDs by Season Slug and Club ID
	 *
	 * @param int $season_id
	 * @param int $club_id
	 *
	 * @return array
	 * @since 0.11.2
	 */
	public function get_standings_by_season_id( $season_id, $club_id ) {

		if ( empty( trim( $season_id ) ) ) {
			return [];
		}

		$standings = [];

		if ( ! absint( $club_id ) ) {

			$args = [
				'post_type'   => 'anwp_competition',
				'numberposts' => - 1,
				'post_status' => [ 'publish', 'stage_secondary' ],
				'tax_query'   => [
					[
						'taxonomy' => 'anwp_season',
						'field'    => 'term_id',
						'terms'    => sanitize_text_field( $season_id ),
					],
				],
				'fields'      => 'ids',
			];

			$competition_ids = get_posts( $args );

			if ( ! empty( $competition_ids ) && is_array( $competition_ids ) ) {
				foreach ( $competition_ids as $competition_id ) {
					$standings = array_merge( $standings, $this->get_standings_by_competition_id( $competition_id ) );
				}
			}
		} else {

			$standing_data = [];

			$args = [
				'post_type'   => 'anwp_competition',
				'numberposts' => - 1,
				'post_status' => [ 'publish', 'stage_secondary' ],
				'tax_query'   => [
					[
						'taxonomy' => 'anwp_season',
						'field'    => 'term_id',
						'terms'    => sanitize_text_field( $season_id ),
					],
				],
			];

			$competitions = get_posts( $args );

			if ( ! empty( $competitions ) && is_array( $competitions ) ) {
				foreach ( $competitions as $competition ) {
					$groups = json_decode( get_post_meta( $competition->ID, '_anwpfl_groups', true ) );

					if ( ! empty( $groups ) && is_array( $groups ) ) {
						foreach ( $groups as $group ) {
							if ( isset( $group->clubs ) ) {
								$clubs = array_unique( array_map( 'absint', $group->clubs ) );

								if ( in_array( absint( $club_id ), $clubs, true ) ) {
									$standing_data[] = [
										'competition_id' => $competition->ID,
										'group_id'       => $group->id,
									];
								}
							}
						}
					}
				}
			}

			foreach ( $standing_data as $st_data ) {
				$args = [
					'post_type'   => 'anwp_standing',
					'numberposts' => - 1,
					'meta_query'  => [
						[
							'key'     => '_anwpfl_competition',
							'value'   => absint( $st_data['competition_id'] ),
							'compare' => '=',
						],
						[
							'key'     => '_anwpfl_competition_group',
							'value'   => absint( $st_data['group_id'] ),
							'compare' => '=',

						],
					],
					'fields'      => 'ids',
				];

				$standings = array_merge( $standings, get_posts( $args ) );
			}
		}

		$standings = array_unique( $standings );

		return $standings;
	}

	/**
	 * Calculate all competition standings (one/first per group).
	 * Used by API (competition couldn't be type="secondary")
	 *
	 * @param int   $competition_id
	 * @param array $game_ids
	 *
	 * @return void
	 * @since  0.13.1
	 */
	public function calculate_competition_standings_by_games( int $competition_id, array $game_ids ) {

		global $wpdb;

		if ( ! absint( $competition_id ) && ! empty( $game_ids ) ) {
			return;
		}

		$competition_obj = anwp_fl()->competition->get_competition( $competition_id );

		if ( 'round-robin' === $competition_obj->type ) {
			anwp_fl()->standing->calculate_competition_standings( $competition_id );
		}

		if ( 'main' === $competition_obj->multistage ) {
			$game_ids = array_unique( array_map( 'absint', array_values( $game_ids ) ) );

			if ( ! empty( $game_ids ) && count( $game_ids ) ) {

				$query = "SELECT DISTINCT competition_id FROM {$wpdb->prefix}anwpfl_matches";

				// Prepare include format and placeholders
				$include_game_placeholders = array_fill( 0, count( $game_ids ), '%s' );
				$include_game_format       = implode( ', ', $include_game_placeholders );

				$query .= $wpdb->prepare( " WHERE match_id IN ({$include_game_format})", $game_ids ); // phpcs:ignore

				$games_competitions = $wpdb->get_col( $query ); // phpcs:ignore

				if ( ! empty( $games_competitions ) && is_array( $games_competitions ) ) {
					foreach ( $games_competitions as $games_competition ) {
						if ( absint( $games_competition ) === absint( $competition_id ) ) {
							continue;
						}

						anwp_fl()->standing->calculate_competition_standings( $games_competition );
						anwp_fl_pro()->competition->update_current_matchweek( $games_competition );
					}
				}
			}
		}
	}

	/**
	 * Get standing data.
	 *
	 * @param $standing_id
	 *
	 * @return [ // <pre>
	 *        'id'                     => (int),
	 *        'title'                  => (string),
	 *        'table_notes'            => (string),
	 *        'manual_ordering'        => (string),
	 *        'ranking_rules'          => (string),
	 *        'points_initial'         => (string),
	 *        'is_initial_data_active' => (string),
	 *        'table_colors'           => (string),
	 *        'competition'            => (int),
	 *        'group'                  => (int),
	 *        'win'                    => (int),
	 *        'draw'                   => (int),
	 *        'loss'                   => (int),
	 * ]|bool
	 *
	 * @since 0.14.4
	 */
	public function get_standing( $standing_id ) {
		if ( method_exists( anwp_football_leagues()->standing, 'get_standing' ) ) {
			return anwp_football_leagues()->standing->get_standing( $standing_id );
		}

		return false;
	}

	/**
	 * Save Previous round places
	 * Use for arrows
	 *
	 * @param array $standing_data
	 * @param array $standing_table
	 * @param array $games
	 *
	 * @return void
	 * @since 0.14.4
	 */
	public function save_previous_round_places( $standing_data, $standing_table, $games ) {

		if ( empty( $standing_table ) ) {
			return;
		}

		if ( 'no' === anwp_football_leagues_premium()->customizer->get_value( 'standing', 'show_standing_arrows' ) ) {
			return;
		}

		if ( ! empty( $games ) && ! empty( $games[ count( $games ) - 1 ] ) ) {
			$last_games_round = absint( $games[ count( $games ) - 1 ]->match_week );
		}

		if ( empty( $last_games_round ) ) {
			return;
		}

		$saved_round = absint( get_post_meta( $standing_data['id'], '_anwpfl_table_last_round', true ) );

		if ( empty( $saved_round ) || $saved_round === $last_games_round ) {
			return;
		}

		$map_club_place = [];
		$standing_table = json_decode( get_post_meta( $standing_data['id'], '_anwpfl_table_main', true ) );

		if ( empty( $standing_table ) ) {
			return;
		}

		foreach ( $standing_table as $standing_row ) {
			$map_club_place[ $standing_row->club_id ] = $standing_row->place;
		}

		if ( empty( $map_club_place ) ) {
			return;
		}

		update_post_meta( $standing_data['id'], '_anwpfl_arrows_dynamic_ranking_data', $map_club_place );
	}

	/**
	 * Get Standing arrows data (previous places)
	 *
	 * @param int $standing_id
	 *
	 * @return array
	 * @since 0.14.4
	 */
	public function get_arrows_data( $standing_id ) {

		if ( empty( absint( $standing_id ) ) ) {
			return [];
		}

		$standing_obj      = anwp_football_leagues_premium()->standing->get_standing( $standing_id );
		$show_arrows_local = empty( $standing_obj->arrows_dynamic_ranking ) ? [] : $standing_obj->arrows_dynamic_ranking;

		if ( empty( $show_arrows_local ) ) {
			$show_arrows_local = anwp_football_leagues_premium()->customizer->get_value( 'standing', 'show_standing_arrows' );
		}

		if ( 'no' === $show_arrows_local ) {
			return [];
		}

		return get_post_meta( $standing_id, '_anwpfl_arrows_dynamic_ranking_data', true ) ?: [];
	}
}
