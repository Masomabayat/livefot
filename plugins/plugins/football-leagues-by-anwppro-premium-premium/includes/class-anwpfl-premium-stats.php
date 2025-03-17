<?php
/**
 * AnWP Football Leagues Premium :: Stats
 *
 * @since   0.9.7
 */

class AnWPFL_Premium_Stats {

	/**
	 * Parent plugin class.
	 *
	 * @var    AnWP_Football_Leagues_Premium
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param  AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.9.7
	 */
	public function hooks() {
		add_action( 'anwpfl/match/on_save', [ $this, 'save_match_club_stats' ], 10, 2 );
		add_action( 'anwpfl/match/on_save', [ $this, 'save_player_stats_data' ], 10, 2 );
		add_filter( 'anwpfl/match/data_to_localize', [ $this, 'modify_edit_vue_data' ], 10, 2 );

		// anwpfl/stats/save_stat_config
		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Register REST routes.
	 */
	public function add_rest_routes() {
		register_rest_route(
			'anwpfl/stats',
			'/save_stat_config/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_stat_config' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Get match club stats options.
	 *
	 * @since 0.9.7
	 */
	public function get_match_stats_club_core_options(): array {

		return [
			'yellowCards'   => esc_html__( 'Yellow Cards', 'anwp-football-leagues' ),
			'yellow2RCards' => esc_html__( '2d Yellow / Red Cards', 'anwp-football-leagues' ),
			'redCards'      => esc_html__( 'Red Cards', 'anwp-football-leagues' ),
			'corners'       => esc_html__( 'Corners', 'anwp-football-leagues' ),
			'fouls'         => esc_html__( 'Fouls', 'anwp-football-leagues' ),
			'offsides'      => esc_html__( 'Offsides', 'anwp-football-leagues' ),
			'possession'    => esc_html__( 'Ball Possession', 'anwp-football-leagues' ),
			'shots'         => esc_html__( 'Shots', 'anwp-football-leagues' ),
			'shotsOnGoals'  => esc_html__( 'Shots on Goal', 'anwp-football-leagues' ),
			'goals'         => esc_html__( 'Goals', 'anwp-football-leagues' ),
		];
	}

	/**
	 * Get new slug by old one
	 *
	 * @since 0.16.0
	 */
	public function get_new_game_team_stat_slug( $old_slug ) {

		$slug_map = [
			'yellowCards'   => 'cards_y',
			'yellow2RCards' => 'cards_yr',
			'redCards'      => 'cards_r',
			'shotsOnGoals'  => 'shots_on_goal',
		];

		return $slug_map[ $old_slug ] ?? $old_slug;
	}

	/**
	 * Get match player stats options.
	 *
	 * @since 0.9.7
	 */
	public function get_match_stats_player_core_options(): array {

		return [
			'card_y'         => esc_html__( 'Yellow Cards', 'anwp-football-leagues' ),
			'card_yr'        => esc_html__( '2d Yellow / Red Cards', 'anwp-football-leagues' ),
			'card_r'         => esc_html__( 'Red Cards', 'anwp-football-leagues' ),
			'goals'          => esc_html__( 'Goals', 'anwp-football-leagues' ),
			'goals_penalty'  => esc_html__( 'Penalty Goals', 'anwp-football-leagues' ),
			'goals_own'      => esc_html__( 'Own Goals', 'anwp-football-leagues' ),
			'assist'         => esc_html__( 'Assists', 'anwp-football-leagues' ),
			'goals_conceded' => esc_html__( 'Goals Conceded', 'anwp-football-leagues' ),
			'minutes_played' => esc_html__( 'Minutes Played', 'anwp-football-leagues' ),
		];
	}

	/**
	 * Modify Game edit JS data.
	 *
	 * @param $data
	 * @param $post_id
	 *
	 * @since 0.9.7
	 * @return array
	 */
	public function modify_edit_vue_data( $data, $post_id ): array {

		$game_data = anwp_fl()->match->get_game_data( $post_id );

		// Club Stats Options
		$data['statsMatchClubColumns'] = get_option( 'anwpfl_stats_columns_match_club' );

		// Player Stats Options
		$data['statsMatchPlayerColumns'] = get_option( 'anwpfl_stats_columns_match_player' );

		// Load Saved Stats
		$data['statsMatchClubHomeData'] = $game_data['stats_home_club'];
		$data['statsMatchClubAwayData'] = $game_data['stats_away_club'];

		$data['statsHomePlayerData'] = $this->get_game_players_statistics( $post_id, $game_data['home_club'] );
		$data['statsAwayPlayerData'] = $this->get_game_players_statistics( $post_id, $game_data['away_club'] );

		return $data;
	}

	/**
	 * Save Match Club stats.
	 *
	 * @param $data
	 * @param $post_data
	 *
	 * @return void
	 * @since 0.9.7
	 */
	public function save_match_club_stats( $data, $post_data ) {

		if ( empty( $data['match_id'] ) ) {
			return;
		}

		$update_data = [
			'stats_home_club' => ( $post_data['_anwpfl_stats_home_club'] ?? '' ) ? wp_json_encode( json_decode( $post_data['_anwpfl_stats_home_club'], true ) ) : '',
			'stats_away_club' => ( $post_data['_anwpfl_stats_away_club'] ?? '' ) ? wp_json_encode( json_decode( $post_data['_anwpfl_stats_away_club'], true ) ) : '',
		];

		anwp_fl()->match->update( $data['match_id'], $update_data );
	}

	/**
	 * Render club match stats value.
	 *
	 * @param              $stats_default
	 * @param object       $stats_custom
	 * @param              $column
	 * @param              $club_side
	 *
	 * @return string
	 */
	public function get_club_match_stat_value( $stats_default, $stats_custom, $column, $club_side ) {

		$output = 0;

		if ( empty( $column->type ) || ! in_array( $column->type, [ 'simple', 'time', 'default', 'calculated' ], true ) ) {
			return $output;
		}

		// Fix PHP 5.6 typecasting problem
		if ( is_array( $stats_custom ) ) {
			$stats_custom = (object) $stats_custom;
		}

		if ( in_array( $column->type, [ 'simple', 'time' ], true ) && ! isset( $stats_custom->{$column->id} ) ) {
			return $output;
		}

		switch ( $column->type ) {
			case 'default':
				$param_slug = $club_side . $this->get_new_game_team_stat_slug( $column->field_slug );
				$output     = $stats_default[ $param_slug ] ?? 0;
				break;

			case 'time':
				$output = $stats_custom->{$column->id};
				break;

			case 'simple':
				$stats_value = empty( $stats_custom->{$column->id} ) ? 0 : floatval( $stats_custom->{$column->id} );
				$output      = number_format( $stats_value, absint( $column->digits ), '.', '' );
				break;

			case 'calculated':
				$field_1 = ( isset( $column->field_1 ) && '' !== $column->field_1 ) ? $this->get_club_match_stat_value( $stats_default, $stats_custom, $this->get_club_match_stat_column_by_id( $column->field_1 ), $club_side ) : 0;
				$field_2 = ( isset( $column->field_2 ) && '' !== $column->field_2 ) ? $this->get_club_match_stat_value( $stats_default, $stats_custom, $this->get_club_match_stat_column_by_id( $column->field_2 ), $club_side ) : 0;

				$field_1 = is_numeric( $field_1 ) ? $field_1 : 0;
				$field_2 = is_numeric( $field_2 ) ? $field_2 : 0;

				switch ( $column->calc ) {
					case 'sum':
						$output = $field_1 + $field_2;
						break;

					case 'difference':
						$output = $field_1 - $field_2;
						break;

					case 'ratio':
						$output = $field_2 ? ( $field_1 / $field_2 ) : 0;
						break;

					case 'ratio_pr':
						$output = $field_2 ? ( $field_1 / $field_2 * 100 ) : 0;
						break;
				}

				$output = number_format( $output, absint( $column->digits ), '.', '' );
				break;
		}

		return $output;
	}

	/**
	 * Get Club Match Stats column by id.
	 *
	 * @param int $column_id
	 *
	 * @return object
	 * @since 0.9.7
	 */
	public function get_club_match_stat_column_by_id( $column_id ) {
		$column = (object) [];

		$columns = json_decode( get_option( 'anwpfl_stats_columns_match_club' ) );

		if ( empty( $columns ) ) {
			return $column;
		}

		$column_filtered = wp_list_filter( $columns, [ 'id' => absint( $column_id ) ] );

		if ( ! empty( $column_filtered ) && is_array( $column_filtered ) ) {
			return reset( $column_filtered );
		}

		return $column;
	}

	/**
	 * Save Player Stats Game data.
	 *
	 * @param array $game_data
	 * @param array $post_data
	 *
	 * @since 0.9.7
	 */
	public function save_player_stats_data( array $game_data, array $post_data ) {
		if ( empty( $game_data['match_id'] ) ) {
			return;
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
		$time_stats_columns   = [];

		if ( ! empty( $player_stats_columns ) ) {
			foreach ( $player_stats_columns as $stat ) {
				if ( 'time' === $stat->type ) {
					$time_stats_columns[] = absint( $stat->id );
				}
			}
		}

		// Prepare data
		$player_stats_home = json_decode( $post_data['_anwpfl_stats_home_players'], true ) ?: [];
		$player_stats_away = json_decode( $post_data['_anwpfl_stats_away_players'], true ) ?: [];
		$player_stats      = array_merge( $player_stats_home, $player_stats_away );

		foreach ( $player_stats as $stats_data ) {
			if ( empty( $stats_data ) || ! in_array( $stats_data['club_id'] . '-' . $stats_data['player_id'], $saved_records, true ) ) {
				continue;
			}

			$update_data = [];

			foreach ( $stats_data as $stat_slug => $value ) {
				if ( 0 !== mb_strpos( $stat_slug, 'c_id__' ) ) {
					continue;
				}

				$stat_id = str_replace( 'c_id__', '', $stat_slug );

				if ( in_array( (int) $stat_id, $time_stats_columns, true ) ) {
					if ( mb_strlen( $value ) && ':' !== mb_substr( $value, - 3, 1 ) ) {
						continue;
					}

					if ( mb_strlen( $value ) < 4 ) {
						continue;
					}
				}

				$update_data[ $stat_id ] = $value;
			}

			$this->update_player_advanced_game_stats( $game_id, $stats_data['player_id'], $stats_data['club_id'], $update_data );
		}
	}

	/**
	 * Update Player Advanced Stats game data
	 *
	 * @param int   $game_id
	 * @param int   $player_id
	 * @param int   $club_id
	 * @param array $st_data
	 *
	 * @since 0.16.0
	 * @return bool
	 */
	public function update_player_advanced_game_stats( int $game_id, int $player_id, int $club_id, array $st_data ): bool {
		global $wpdb;

		if ( empty( $game_id ) || empty( $player_id ) || empty( $club_id ) ) {
			return false;
		}

		$columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ), true );

		if ( empty( $columns ) ) {
			return false;
		}

		$update_data_arr = [];

		foreach ( $columns as $column ) {
			if ( in_array( $column['type'], [ 'simple', 'time' ], true ) && absint( $column['id'] ) ) {
				$update_data_arr[ 'c_id__' . absint( $column['id'] ) ] = $st_data[ $column['id'] ] ?? '';
			}
		}

		return false !== $wpdb->update(
			$wpdb->prefix . 'anwpfl_players',
			$update_data_arr,
			[
				'match_id'  => $game_id,
				'player_id' => $player_id,
				'club_id'   => $club_id,
			]
		);
	}

	/**
	 * Save Player Advanced Stats game data
	 *
	 * @param int  $game_id
	 * @param int  $team_id
	 * @param bool $custom_stats_check
	 *
	 * @return array
	 * @since 0.16.0
	 */
	public function get_game_players_statistics( int $game_id, int $team_id, bool $custom_stats_check = true ): array {
		global $wpdb;

		static $stats = [];

		if ( empty( $game_id ) || empty( $team_id ) ) {
			return [];
		}

		if ( ! empty( $stats[ $game_id . '-' . $team_id ] ) ) {
			return $stats[ $game_id . '-' . $team_id ];
		}

		$columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ), true );

		if ( empty( $columns ) && $custom_stats_check ) {
			return [];
		}

		$stats[ $game_id . '-' . $team_id ] = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT p.player_id ID, p.*
					FROM {$wpdb->prefix}anwpfl_players p
					WHERE p.`match_id` = %d AND p.`club_id` = %d
				",
				$game_id,
				$team_id
			),
			OBJECT_K
		) ?: [];

		return $stats[ $game_id . '-' . $team_id ];
	}

	/**
	 * Get column sorting value
	 *
	 * @param array $column
	 * @param $cell_value
	 *
	 * @return array|mixed|string|string[]
	 */
	public function get_game_sorting_value( array $column, $cell_value ) {
		$data_order = '';

		if ( 'time' === $column['type'] ) {
			$data_order = str_replace( ':', '', $cell_value );
		} elseif ( ( isset( $column['prefix'] ) && '' !== $column['prefix'] ) || ( isset( $column['postfix'] ) && '' !== $column['postfix'] ) ) {

			$data_order = $cell_value;

			if ( isset( $column['prefix'] ) && '' !== $column['prefix'] ) {
				$data_order = ltrim( $data_order, $column['prefix'] );
			}

			if ( isset( $column['postfix'] ) && '' !== $column['postfix'] ) {
				$data_order = rtrim( $data_order, $column['postfix'] );
			}
		}

		return $data_order;
	}

	/**
	 * Render player game stats value.
	 *
	 * @param array $player_stats
	 * @param array $column
	 * @param bool  $raw_value
	 *
	 * @return string|int
	 * @since 0.9.7
	 */
	public function render_player_match_stats( array $player_stats, array $column, bool $raw_value = false ) {
		$output = 0;

		if ( empty( $column['type'] ) || ! in_array( $column['type'], [ 'default', 'simple', 'time', 'composed', 'calculated' ], true ) ) {
			return $output;
		}

		if ( in_array( $column['type'], [ 'simple', 'time' ], true ) && ! isset( $player_stats[ 'c_id__' . $column['id'] ] ) ) {
			return $output;
		}

		switch ( $column['type'] ) {
			case 'default':
				if ( 'minutes_played' === $column['field_slug'] ) {
					if ( $player_stats['time_out'] >= $player_stats['time_in'] && 46 === absint( $player_stats['time_out'] ) ) {
						$output = $player_stats['time_out'] - $player_stats['time_in'] - 1;
					} elseif ( $player_stats['time_out'] >= $player_stats['time_in'] && 46 === absint( $player_stats['time_in'] ) ) {
						$output = $player_stats['time_out'] - $player_stats['time_in'] + 1;
					} elseif ( $player_stats['time_out'] >= $player_stats['time_in'] ) {
						$output = $player_stats['time_out'] - $player_stats['time_in'];
					}
				} else {
					$output = $player_stats[ $column['field_slug'] ] ?? 0;
				}

				if ( ! empty( $column['prefix'] ) && ! $raw_value ) {
					$output = $column['prefix'] . $output;
				}

				if ( ! empty( $column['postfix'] ) && ! $raw_value ) {
					$output = $output . $column['postfix'];
				}

				break;

			case 'time':
				$output = $player_stats[ 'c_id__' . $column['id'] ];
				break;

			case 'simple':
				$stats_value = empty( $player_stats[ 'c_id__' . $column['id'] ] ) ? 0 : $player_stats[ 'c_id__' . $column['id'] ];

				if ( is_numeric( $stats_value ) ) {
					$output = number_format( $stats_value, absint( $column['digits'] ), '.', '' );

					if ( ! empty( $column['prefix'] ) && ! $raw_value ) {
						$output = $column['prefix'] . $output;
					}

					if ( ! empty( $column['postfix'] ) && ! $raw_value ) {
						$output = $output . $column['postfix'];
					}
				}

				break;

			case 'calculated':
				$field_1 = ( isset( $column['field_1'] ) && '' !== $column['field_1'] ) ? $this->render_player_match_stats( $player_stats, $this->get_stats_player_match_column_by_id( $column['field_1'] ), true ) : 0;
				$field_2 = ( isset( $column['field_2'] ) && '' !== $column['field_2'] ) ? $this->render_player_match_stats( $player_stats, $this->get_stats_player_match_column_by_id( $column['field_2'] ), true ) : 0;

				$field_1 = is_numeric( $field_1 ) ? $field_1 : 0;
				$field_2 = is_numeric( $field_2 ) ? $field_2 : 0;

				switch ( $column['calc'] ) {
					case 'sum':
						$output = $field_1 + $field_2;
						break;

					case 'difference':
						$output = $field_1 - $field_2;
						break;

					case 'ratio':
						$output = $field_2 ? ( $field_1 / $field_2 ) : 0;
						break;

					case 'ratio_pr':
						$output = $field_2 ? ( $field_1 / $field_2 * 100 ) : 0;
						break;
				}

				$output = number_format( $output, absint( $column['digits'] ), '.', '' );

				if ( ! empty( $column['prefix'] ) && ! $raw_value ) {
					$output = $column['prefix'] . $output;
				}

				if ( ! empty( $column['postfix'] ) && ! $raw_value ) {
					$output = $output . $column['postfix'];
				}
				break;

			case 'composed':
				$fields = [];

				if ( isset( $column['field_1'] ) && '' !== $column['field_1'] ) {
					$fields[] = $this->render_player_match_stats( $player_stats, $this->get_stats_player_match_column_by_id( $column['field_1'] ), true );
				}

				if ( isset( $column['field_2'] ) && '' !== $column['field_2'] ) {
					$fields[] = $this->render_player_match_stats( $player_stats, $this->get_stats_player_match_column_by_id( $column['field_2'] ), true );
				}

				if ( isset( $column['field_3'] ) && '' !== $column['field_3'] ) {
					$fields[] = $this->render_player_match_stats( $player_stats, $this->get_stats_player_match_column_by_id( $column['field_3'] ), true );
				}

				$separator = $column['separator'] ?: '-';

				if ( ! empty( $fields ) ) {
					$output = implode( $separator, $fields );
				}

				break;
		}

		return $output;
	}

	/**
	 * Get Player Match Stats column by id.
	 *
	 * @param int $column_id
	 *
	 * @return array [
	 *      'name' => 'Shots Total',
	 *      'abbr' => 'S',
	 *      'type' => 'simple',
	 *      'group' => '',
	 *      'prefix' => '',
	 *      'postfix' => '',
	 *      'visibility' => '',
	 *      'digits' => '',
	 *      'api_field' => 'shots__total',
	 *      'id' => 2,
	 *  ]
	 * @since 0.9.7
	 */
	public function get_stats_player_match_column_by_id( int $column_id ): array {
		$column  = [];
		$columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ), true );

		if ( empty( $columns ) ) {
			return $column;
		}

		$column_filtered = wp_list_filter( $columns, [ 'id' => absint( $column_id ) ] );

		if ( ! empty( $column_filtered ) && is_array( $column_filtered ) ) {
			return reset( $column_filtered );
		}

		return $column;
	}

	/**
	 * Get player match simple stats options.
	 *
	 * @return array
	 * @since 0.9.8
	 */
	public function get_options_match_player_stats_simple() {

		$options = [];

		$stats_columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ) );

		if ( empty( $stats_columns ) && ! is_array( $stats_columns ) ) {
			return $options;
		}

		$stats_columns = wp_list_filter( $stats_columns, [ 'type' => 'simple' ] );

		foreach ( $stats_columns as $stats_column ) {
			$options[ $stats_column->id ] = $stats_column->name;
		}

		return $options;
	}

	/**
	 * Get player match simple stats options.
	 *
	 * @return array
	 * @since 0.12.4
	 */
	public function get_options_match_club_stats_simple() {

		$options = [];

		$stats_columns = json_decode( get_option( 'anwpfl_stats_columns_match_club' ) );

		if ( empty( $stats_columns ) && ! is_array( $stats_columns ) ) {
			return $options;
		}

		$stats_columns = wp_list_filter( $stats_columns, [ 'type' => 'simple' ] );

		foreach ( $stats_columns as $stats_column ) {
			$options[ $stats_column->id ] = $stats_column->name;
		}

		return $options;
	}

	/**
	 * Get Labels for Match Club Statistics
	 *
	 * @return array
	 * @since 0.12.4
	 */
	public function get_labels_match_club_stats() {

		$labels = [];

		$stats_columns = json_decode( get_option( 'anwpfl_stats_columns_match_club' ) );

		if ( empty( $stats_columns ) && ! is_array( $stats_columns ) ) {
			return $labels;
		}

		foreach ( $stats_columns as $stat ) {
			if ( 'default' === $stat->type && ! empty( $stat->field_slug ) ) {
				$labels[ $stat->field_slug ] = [
					'text' => $stat->name,
					'abbr' => empty( $stat->abbr ) ? $stat->name : $stat->abbr,
				];
			} elseif ( 'simple' === $stat->type ) {
				$labels[ 'c__' . $stat->id ] = [
					'text' => $stat->name,
					'abbr' => empty( $stat->abbr ) ? $stat->name : $stat->abbr,
				];
			}
		}

		if ( ! empty( $labels['yellowCards'] ) ) {
			$labels['cards_y'] = $labels['yellowCards'];
		}

		if ( ! empty( $labels['redCards'] ) ) {
			$labels['cards_r'] = $labels['redCards'];
		}

		if ( ! empty( $labels['shotsOnGoals'] ) ) {
			$labels['shots_on_goal'] = $labels['shotsOnGoals'];
		}

		return $labels;
	}

	/**
	 * Render player rating.
	 *
	 * @param int   $field_id
	 * @param int   $player_id
	 * @param array $stats
	 *
	 * @return string
	 * @since 0.9.8
	 */
	public function render_lineup_player_rating( int $field_id, int $player_id, array $stats ): string {

		$output = '';

		// Check out initial fields
		if ( empty( $field_id ) || empty( $player_id ) || ! $stats ) {
			return $output;
		}

		// Check out data
		if ( ! isset( $stats[ $player_id ] ) || ! isset( $stats[ $player_id ]->{ 'c_id__' . $field_id } ) ) {
			return $output;
		}

		$player_rating = $stats[ $player_id ]->{ 'c_id__' . $field_id };

		if ( '' === $player_rating ) {
			return $output;
		}

		$background_color = 'hsl( ' . esc_attr( anwp_football_leagues_premium()->data->get_color_rating( $player_rating ) ) . 'deg, 60%, 45% )';

		return '<div class="ml-1 py-1 match__player-rating" style="background-color:' . esc_attr( $background_color ) . '">' . esc_html( $player_rating ) . '</div>';
	}

	/**
	 * Create Statistical column in Players Table
	 *
	 * @param int $stat_id
	 */
	public function create_stat_column_in_players_table( int $stat_id ) {
		global $wpdb;

		$columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ), true );

		if ( empty( $columns ) ) {
			return;
		}

		$stat_column_exists_in_config = false;

		foreach ( $columns as $column ) {
			if ( absint( $column['id'] ) === $stat_id && in_array( $column['type'], [ 'simple', 'time' ], true ) ) {
				$stat_column_exists_in_config = true;
				break;
			}
		}

		if ( ! $stat_column_exists_in_config ) {
			return;
		}

		$column_slug = 'c_id__' . absint( $stat_id );

		try {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->prefix}anwpfl_players` LIKE '$column_slug';" ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}anwpfl_players ADD COLUMN `{$column_slug}` varchar(100) NOT NULL DEFAULT '';" );
			}
		} catch ( Exception $exception ) {
			error_log( $exception ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	/**
	 * Check if table structure sync needed with Player Statistics config
	 *
	 * @return int[] Stat/Column IDs to create
	 */
	public function check_player_stats_db_sync_needed(): array {
		global $wpdb;

		$stat_config = json_decode( get_option( 'anwpfl_stats_columns_match_player' ), true ) ?: [];

		if ( empty( $stat_config ) ) {
			return [];
		}

		$columns_to_create = [];

		foreach ( $stat_config as $stat_column ) {
			if ( ! in_array( $stat_column['type'], [ 'simple', 'time' ], true ) || ! absint( $stat_column['id'] ) ) {
				continue;
			}

			$column_slug = 'c_id__' . absint( $stat_column['id'] );

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( ! $wpdb->get_var( "SHOW COLUMNS FROM $wpdb->anwpfl_players LIKE '$column_slug';" ) ) {
				$columns_to_create[] = absint( $stat_column['id'] );
			}
		}

		return $columns_to_create;
	}

	public function get_player_db_stat_ids() {
		global $wpdb;

		$columns = [];

		$fields = $wpdb->get_results( 'DESCRIBE ' . $wpdb->anwpfl_players );

		if ( is_array( $fields ) ) {
			foreach ( $fields as $column ) {
				if ( false !== strpos( $column->Field, 'c_id__' ) ) { //phpcs:ignore WordPress.NamingConventions
					$columns[] = absint( str_replace( 'c_id__', '', $column->Field ) ); //phpcs:ignore WordPress.NamingConventions
				}
			}
		}

		return $columns;
	}

	/**
	 * Save statistic configuration
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function save_stat_config( WP_REST_Request $request ) {
		$stat_options = $request->get_params();

		update_option( 'anwpfl_stats_columns_match_club', wp_json_encode( $stat_options['team_stat_columns'] ?? [] ), false );
		update_option( 'anwpfl_stats_columns_match_club_last_id', absint( $stat_options['team_stat_column_last_id'] ?? 0 ), false );

		update_option( 'anwpfl_stats_columns_match_player', wp_json_encode( $stat_options['player_stat_columns'] ?? [] ), true );
		update_option( 'anwpfl_stats_columns_match_player_last_id', absint( $stat_options['player_stat_column_last_id'] ?? 0 ), false );

		foreach ( $this->check_player_stats_db_sync_needed() as $stat_column_to_create ) {
			$this->create_stat_column_in_players_table( $stat_column_to_create );
		}

		return rest_ensure_response( [ 'result' => true ] );
	}
}
