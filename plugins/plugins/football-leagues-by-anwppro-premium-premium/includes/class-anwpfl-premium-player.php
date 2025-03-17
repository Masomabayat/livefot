<?php

/**
 * AnWP Football Leagues Premium :: Player
 *
 * @since 0.5.7
 */
class AnWPFL_Premium_Player {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// Save main plugin object
		$this->plugin = $plugin;

		// Init hooks
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'anwpfl/shortcodes/player_shortcode_options', [ $this, 'add_player_shortcode_premium_sections' ] );

		add_filter( 'anwpfl/player/player_actions_data', [ $this, 'modify_player_actions_data' ], 10, 2 );
		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Register REST routes.
	 */
	public function add_rest_routes() {
		register_rest_route(
			'anwpfl/v1',
			'/players/get_stat/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_stat_players' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);
	}

	/**
	 * Modify Player actions data.
	 *
	 * @since 0.12.6
	 */
	public function modify_player_actions_data( $player_data, $params ) {

		$player_id = isset( $params['player_id'] ) ? absint( $params['player_id'] ) : '';
		$club_id   = isset( $params['club_id'] ) ? absint( $params['club_id'] ) : '';

		if ( ! empty( $club_id ) && ! empty( $player_id ) ) {

			$shortcode_args = [
				'limit'       => 0,
				'order'       => 'DESC',
				'player_id'   => $player_id,
				'layout'      => 'player',
				'club_column' => 'title',

			];

			$maybe_club_out = get_post_meta( $player_id, '_anwpfl_current_club', true );

			ob_start();

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo anwp_football_leagues()->template->shortcode_loader( 'premium-transfers', $shortcode_args );

			$transfers_history = ob_get_clean();

			$new_player_data = [
				'transfers_history' => $transfers_history,
				'transfer_link'     => add_query_arg(
					[
						'maybe_player'   => $player_id,
						'maybe_club_in'  => $club_id,
						'maybe_club_out' => absint( $club_id ) !== absint( $maybe_club_out ) ? $maybe_club_out : '',
					],
					admin_url( '/post-new.php?post_type=anwp_transfer' )
				),
			];

			$player_data = array_merge( $player_data, $new_player_data );
		}

		return $player_data;
	}

	/**
	 * Rendering premium Player sections in shortcode helper.
	 *
	 * @since 0.11.8
	 */
	public function add_player_shortcode_premium_sections() {
		ob_start();
		?>
		<option value="stats_panel"><?php echo esc_html__( 'Stats Panel', 'anwp-football-leagues-premium' ); ?></option>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get totals statistic for players.
	 *
	 * @param object $data
	 * @param array  $sections
	 *
	 * @return array|null|object
	 * @since 0.5.7
	 */
	public function get_players_stats_totals( $data, array $sections ) {

		$season_id      = $data->season_id;
		$club_id        = $data->club_id;
		$competition_id = $data->competition_id;
		$show_secondary = $data->multistage;
		$date_from      = $data->date_from ?? '';
		$date_to        = $data->date_to ?? '';

		$player_yr_card_count = AnWPFL_Options::get_value( 'player_yr_card_count', 'yyr' );

		global $wpdb;

		// New caching system
		$cache_key = 'FL-PRO-PLAYER_get_players_stats_totals__' . md5( maybe_serialize( $data ) . '-' . maybe_serialize( $sections ) );

		if ( class_exists( 'AnWPFL_Cache' ) && anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) ) {
			return anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' );
		}

		// Section limitation
		$query_clubs          = $sections['club'] ? ', GROUP_CONCAT(DISTINCT a.club_id) as clubs' : '';
		$query_minutes        = $sections['minutes'] ? ', SUM( CASE WHEN (a.time_out >= a.time_in AND a.time_out = 46  AND a.time_in != 46 ) THEN ( a.time_out - a.time_in - 1 ) WHEN (a.time_out >= a.time_in AND a.time_in = 46 ) THEN ( a.time_out - a.time_in + 1 ) WHEN (a.time_out >= a.time_in) THEN ( a.time_out - a.time_in ) ELSE 0 END ) as minutes' : '';
		$query_clean_sheets   = $sections['clean_sheets'] ? ", SUM( CASE WHEN (a.appearance = 1 AND a.goals_conceded = 0 AND p.position = 'g') THEN 1 ELSE 0 END ) as clean_sheets" : '';
		$query_positions      = ( $sections['position'] || ! empty( $data->type ) ) ? ', p.position' : '';
		$query_appearance     = $sections['appearance'] ? ', SUM( CASE WHEN (a.appearance > 0) THEN 1 ELSE 0 END ) as played, SUM( CASE WHEN (a.appearance = 1 OR a.appearance = 2) THEN 1 ELSE 0 END ) as started' : '';
		$query_goals          = $sections['goals'] ? ', SUM( a.goals ) as sum_goals' : '';
		$query_goals_penalty  = $sections['goals_penalty'] ? ', SUM( a.goals_penalty ) as sum_goals_penalty' : '';
		$query_goals_own      = $sections['goals_own'] ? ', SUM( a.goals_own ) as sum_goals_own' : '';
		$query_assists        = $sections['assists'] ? ', SUM( a.assist ) as sum_assists' : '';
		$query_goals_conceded = $sections['goals_conceded'] ? ', SUM( a.goals_conceded ) as sum_conceded' : '';

		// Prepare countable field
		if ( 'yr' === $player_yr_card_count ) {
			$query_cards = $sections['cards'] ? ', SUM( CASE WHEN a.card_yr > 0 THEN 0 ELSE a.card_y END ) as sum_card_y, SUM( a.card_yr ) as sum_card_yr, SUM( a.card_r ) as sum_card_r' : '';
		} else {
			$query_cards = $sections['cards'] ? ', SUM( a.card_y ) as sum_card_y, SUM( a.card_yr ) as sum_card_yr, SUM( a.card_r ) as sum_card_r' : '';
		}

		$query_minutes = apply_filters( 'anwpflpro/stats-players/minutes_query', $query_minutes );

		$query = "
		SELECT
			a.player_id, p.name as player_name, p.photo
			$query_positions
			$query_clubs
			$query_goals
			$query_goals_penalty
			$query_goals_own
			$query_cards
			$query_assists
			$query_goals_conceded
			$query_minutes
			$query_appearance
			$query_clean_sheets
		FROM {$wpdb->prefix}anwpfl_players a
		LEFT JOIN {$wpdb->prefix}anwpfl_matches m ON m.match_id = a.match_id
		LEFT JOIN {$wpdb->prefix}anwpfl_player_data p ON p.player_id = a.player_id
		WHERE 1=1
		";

		/**==================
		 * WHERE filter by season ID
		 *================ */
		if ( intval( $season_id ) ) {
			$query .= $wpdb->prepare( ' AND m.season_id = %d ', $season_id );
		}

		/**==================
		 * WHERE filter by club ID
		 *================ */
		if ( intval( $club_id ) ) {
			$query .= $wpdb->prepare( ' AND a.club_id = %d ', $club_id );
		}

		/**==================
		 * WHERE filter by date
		 *================ */
		if ( ! empty( $date_from ) || ! empty( $date_to ) ) {

			// date_to
			if ( trim( $date_to ) ) {
				$date_to = explode( ' ', $date_to )[0];

				if ( anwp_football_leagues()->helper->validate_date( $date_to, 'Y-m-d' ) ) {
					$query .= $wpdb->prepare( ' AND m.kickoff <= %s ', $date_to . ' 23:59:59' );
				}
			}

			// date_from
			if ( trim( $date_from ) ) {
				$date_from = explode( ' ', $date_from )[0];

				if ( anwp_football_leagues()->helper->validate_date( $date_from, 'Y-m-d' ) ) {
					$query .= $wpdb->prepare( ' AND m.kickoff >= %s ', $date_from . ' 00:00:00' );
				}
			}
		}

		/**==================
		 * WHERE filter by competition ID
		 *================ */
		// Get competition to filter
		if ( AnWP_Football_Leagues::string_to_bool( $show_secondary ) && absint( $competition_id ) ) {
			$query .= $wpdb->prepare( ' AND ( m.competition_id = %d OR m.main_stage_id = %d ) ', $competition_id, $competition_id );
		} elseif ( absint( $competition_id ) ) {
			$query .= $wpdb->prepare( ' AND m.competition_id = %d ', $competition_id );
		}

		/**==================
		 * WHERE official only
		 *================ */
		if ( ! absint( $competition_id ) ) {
			$query .= ' AND m.game_status = 1 ';
		}

		/**==================
		 * Group by Player
		 *================ */
		$query .= ' GROUP BY a.player_id';

		// phpcs:ignore WordPress.DB.PreparedSQL
		$rows = $wpdb->get_results( $query );

		foreach ( $rows as $row ) {
			$row->sort_name = esc_attr( '. ' === mb_substr( $row->player_name ?? '', 1, 2 ) ? mb_substr( $row->player_name ?? '', 3 ) : $row->player_name );
		}

		$rows = wp_list_sort( $rows, 'sort_name' );

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $rows ) && class_exists( 'AnWPFL_Cache' ) ) {
			anwp_football_leagues()->cache->set( $cache_key, $rows, 'anwp_match' );
		}

		return $rows;
	}

	/**
	 * Get totals statistic for players - Custom.
	 *
	 * @param array $args
	 * @param array  $columns
	 *
	 * @return array
	 * @since 0.12.2
	 */
	public function get_players_stats_totals_custom( array $args, array $columns ): array {

		$args = wp_parse_args(
			$args,
			[
				'season_id'             => '',
				'league_id'             => '',
				'club_id'               => '',
				'competition_id'        => '',
				'multistage'            => 0,
				'type'                  => '',
				'links'                 => 0,
				'rows'                  => '',
				'photos'                => 0,
				'limit'                 => 10,
				'sort_column'           => '',
				'sort_order'            => 'DESC',
				'sort_column_2'         => '',
				'sort_order_2'          => 'DESC',
				'date_from'             => '',
				'date_to'               => '',
				'filter_column_1'       => '',
				'filter_column_1_value' => '',
				'filter_column_2'       => '',
				'filter_column_2_value' => '',
			]
		);

		global $wpdb;

		$player_yr_card_count = AnWPFL_Options::get_value( 'player_yr_card_count', 'yyr' );

		/*
		|--------------------------------------------------------------------
		| Build DataBase Query
		|--------------------------------------------------------------------
		*/
		$query_stat = [];

		foreach ( $columns as $column ) {

			switch ( $column ) {
				case 'appearance':
					$query_stat[] = 'SUM( CASE WHEN (a.appearance > 0) THEN 1 ELSE 0 END ) as appearance';
					break;

				case 'started':
					$query_stat[] = 'SUM( CASE WHEN (a.appearance = 1 OR a.appearance = 2) THEN 1 ELSE 0 END ) as started';
					break;

				case 'minutes':
					$query_stat_minutes = 'SUM( CASE WHEN (a.time_out >= a.time_in AND a.time_out = 46 ) THEN ( a.time_out - a.time_in - 1 ) WHEN (a.time_out >= a.time_in AND a.time_in = 46 ) THEN ( a.time_out - a.time_in + 1 ) WHEN (a.time_out >= a.time_in) THEN ( a.time_out - a.time_in ) ELSE 0 END ) as minutes';
					$query_stat[]       = apply_filters( 'anwpflpro/stat-players-custom/minutes_query', $query_stat_minutes );
					break;

				case 'cards_all':
					$query_stat[] = '( SUM( a.card_y ) + SUM( a.card_yr ) + SUM( a.card_r ) ) as cards_all';
					break;

				case 'cards_y':
					if ( 'yr' === $player_yr_card_count ) {
						$query_stat[] = 'SUM( CASE WHEN a.card_yr > 0 THEN 0 ELSE a.card_y END ) as cards_y';
					} else {
						$query_stat[] = '( SUM( a.card_y ) + SUM( a.card_yr ) ) as cards_y';
					}
					break;

				case 'cards_r':
					$query_stat[] = '( SUM( a.card_yr ) + SUM( a.card_r ) ) as cards_r';
					break;

				case 'goals':
					$query_stat[] = 'SUM( a.goals ) as goals';
					break;

				case 'goals_penalty':
					$query_stat[] = 'SUM( a.goals_penalty ) as goals_penalty';
					break;

				case 'goals_own':
					$query_stat[] = 'SUM( a.goals_own ) as goals_own';
					break;

				case 'assists':
					$query_stat[] = 'SUM( a.assist ) as assists';
					break;

				case 'goals_conceded':
					$query_stat[] = 'SUM( a.goals_conceded ) as goals_conceded';
					break;

				case 'clean_sheets':
					$query_stat[] = 'SUM( CASE WHEN (a.appearance = 1 AND a.goals_conceded = 0 AND p.position = "g") THEN 1 ELSE 0 END ) as clean_sheets';
					break;
			}

			// Handle custom Stats
			if ( false !== mb_strpos( $column, 'c__' ) ) {

				$col_stat_id = absint( mb_substr( $column, 3 ) );

				if ( ! $col_stat_id ) {
					continue;
				}

				$stats_columns = json_decode( get_option( 'anwpfl_stats_columns_match_player' ) );

				if ( empty( $stats_columns ) && ! is_array( $stats_columns ) ) {
					continue;
				}

				$stat_items = array_values( wp_list_filter( $stats_columns, [ 'id' => absint( $col_stat_id ) ] ) );

				if ( empty( $stat_items ) || empty( $stat_items[0] ) || empty( $stat_items[0]->type ) ) {
					continue;
				}

				$stat_item = $stat_items[0];

				if ( ! in_array( $stat_item->type, [ 'simple' ], true ) ) {
					continue;
				}

				$stat_slug = 'c_id__' . absint( $stat_item->id );

				if ( absint( $stat_item->digits ) ) {
					$query_stat[] = " ROUND( SUM( a.{$stat_slug} ), $stat_item->digits ) as $column";
				} else {
					$query_stat[] = " SUM( a.{$stat_slug} ) as $column";
				}
			}
		}

		if ( empty( $query_stat ) ) {
			return [];
		}

		$query_stat = implode( ',', $query_stat ) . ',';

		$query =
			"
			SELECT
				a.player_id, p.position, p.name as player_name, p.photo, 
				$query_stat
				GROUP_CONCAT(DISTINCT a.club_id) as clubs
			FROM {$wpdb->prefix}anwpfl_players a
			LEFT JOIN {$wpdb->prefix}anwpfl_matches m ON m.match_id = a.match_id
			LEFT JOIN {$wpdb->prefix}anwpfl_player_data p ON p.player_id = a.player_id
			WHERE 1=1 
		";

		/**==================
		 * WHERE filter by season ID
		 *================ */
		if ( intval( $args['season_id'] ) ) {
			$query .= $wpdb->prepare( ' AND m.season_id = %d ', $args['season_id'] );
		}

		/**==================
		 * WHERE filter by league ID
		 *================ */
		if ( intval( $args['league_id'] ) ) {
			$query .= $wpdb->prepare( ' AND m.league_id = %d ', $args['league_id'] );
		}

		/**==================
		 * WHERE filter by club ID
		 *================ */
		if ( intval( $args['club_id'] ) ) {
			$query .= $wpdb->prepare( ' AND a.club_id = %d ', $args['club_id'] );
		}

		/**==================
		 * WHERE filter by competition ID
		 *================ */
		// Get competition to filter
		if ( AnWP_Football_Leagues::string_to_bool( $args['multistage'] ) && absint( $args['competition_id'] ) ) {
			$query .= $wpdb->prepare( ' AND ( m.competition_id = %d OR m.main_stage_id = %d ) ', $args['competition_id'], $args['competition_id'] );
		} elseif ( absint( $args['competition_id'] ) ) {
			$query .= $wpdb->prepare( ' AND m.competition_id = %d ', $args['competition_id'] );
		}

		/**==================
		 * WHERE filter by player type
		 *================ */
		if ( ! empty( $args['type'] ) ) {
			if ( 'g' === $args['type'] ) {
				$query .= ' AND p.position = "g"';
			} else {
				$query .= ' AND p.position != "g" AND p.position != ""';
			}
		}

		/**==================
		 * WHERE filter by date
		 *================ */
		if ( ! empty( $args['date_from'] ) || ! empty( $args['date_to'] ) ) {

			// date_to
			if ( trim( $args['date_to'] ) ) {
				$date_to = explode( ' ', $args['date_to'] )[0];

				if ( anwp_football_leagues()->helper->validate_date( $date_to, 'Y-m-d' ) ) {
					$query .= $wpdb->prepare( ' AND m.kickoff <= %s ', $date_to . ' 23:59:59' );
				}
			}

			// date_from
			if ( trim( $args['date_from'] ) ) {
				$date_from = explode( ' ', $args['date_from'] )[0];

				if ( anwp_football_leagues()->helper->validate_date( $date_from, 'Y-m-d' ) ) {
					$query .= $wpdb->prepare( ' AND m.kickoff >= %s ', $date_from . ' 00:00:00' );
				}
			}
		}

		/**==================
		 * WHERE official only
		 *================ */
		$query .= ' AND m.game_status = 1 ';

		/**==================
		 * WHERE Hide Not Played
		 *================ */
		$query .= ' AND a.appearance > 0 ';

		/**==================
		 * Group by Player
		 *================ */
		$query .= ' GROUP BY a.player_id';

		/**==================
		 * HAVING Filter 1
		 *================ */
		$has_having = false;
		if ( ! empty( $args['filter_column_1'] ) && ! empty( $args['filter_column_1_value'] ) ) {
			$query     .= ' HAVING ' . sanitize_text_field( $args['filter_column_1'] ) . '>=' . (int) $args['filter_column_1_value'];
			$has_having = true;
		}

		/**==================
		 * HAVING Filter 2
		 *================ */
		if ( ! empty( $args['filter_column_2'] ) && ! empty( $args['filter_column_2_value'] ) ) {
			$query     .= ( $has_having ? ' AND ' : ' HAVING ' ) . sanitize_text_field( $args['filter_column_2'] ) . '>=' . (int) $args['filter_column_2_value'];
			$has_having = true;
		}

		/*
		|--------------------------------------------------------------------
		| Sorting
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $args['sort_column'] ) && ! empty( $args['sort_column_2'] ) ) {
			$sort_column   = sanitize_text_field( $args['sort_column'] );
			$sort_column_2 = sanitize_text_field( $args['sort_column_2'] );
			$sort_order    = 'asc' === mb_strtolower( $args['sort_order'] ) ? 'ASC' : 'DESC';
			$sort_order_2  = 'asc' === mb_strtolower( $args['sort_order_2'] ) ? 'ASC' : 'DESC';

			$query .= " ORDER BY $sort_column $sort_order, $sort_column_2 $sort_order_2";
		} elseif ( ! empty( $args['sort_column'] ) ) {
			$sort_column = sanitize_text_field( $args['sort_column'] );
			$sort_order  = 'asc' === mb_strtolower( $args['sort_order'] ) ? 'ASC' : 'DESC';

			$query .= " ORDER BY $sort_column $sort_order";
		} else {
			$query .= ' ORDER BY player_name';
		}

		/**==================
		 * LIMIT clause
		 *================ */
		if ( absint( $args['limit'] ) ) {
			$query .= $wpdb->prepare( ' LIMIT %d', $args['limit'] );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL
		$rows = $wpdb->get_results( $query, ARRAY_A );

		if ( empty( $rows ) ) {
			return [];
		}

		/*
		|--------------------------------------------------------------------
		| Link To Profile
		|--------------------------------------------------------------------
		*/
		if ( $args['links'] ) {
			$links_map = anwp_fl()->helper->get_permalinks_by_ids( wp_list_pluck( $rows, 'player_id' ), 'anwp_player' );

			foreach ( $rows as $row_id => $player ) {
				$rows[ $row_id ]['link'] = $links_map[ $player['player_id'] ] ?? '';
			}
		}

		return $rows;
	}

	/**
	 * Get Player stats data for json
	 *
	 * @param array  $stats
	 * @param object $data
	 *
	 * @return array
	 * @since 0.11.0
	 */
	public function get_players_stats_totals_json( array $stats, $data ): array {

		$player_link = AnWP_Football_Leagues::string_to_bool( $data->links );

		if ( $player_link && count( $stats ) > 250 && 2 !== absint( $data->links ) ) {
			$player_link = false;
		} elseif ( '2' === $data->links && ! $player_link ) {
			$player_link = true;
		}

		$output    = [];
		$links_map = [];

		if ( $player_link ) {
			$links_map = anwp_fl()->helper->get_permalinks_by_ids( wp_list_pluck( $stats, 'player_id' ), 'anwp_player' );
		}

		foreach ( $stats as $player ) {

			$player = (object) wp_parse_args(
				$player,
				[
					'position'          => '',
					'player_name'       => '',
					'player_id'         => '',
					'clubs'             => '',
					'played'            => '',
					'started'           => '',
					'minutes'           => '',
					'sum_card_y'        => '',
					'sum_card_yr'       => '',
					'sum_card_r'        => '',
					'sum_goals'         => '',
					'sum_goals_penalty' => '',
					'sum_goals_own'     => '',
					'sum_assists'       => '',
					'sum_conceded'      => '',
					'clean_sheets'      => '',
				]
			);

			if ( is_null( $player->position ) ) {
				continue;
			}

			if ( ( 'g' === $player->position && 'p' === $data->type ) || ( 'g' !== $player->position && 'g' === $data->type ) || ! trim( $player->player_name ) ) {
				continue;
			}

			$club_row = '';
			if ( trim( $player->clubs ) ) {
				foreach ( wp_parse_id_list( $player->clubs ) as $ii => $club_id ) {
					$club_row .= $ii ? '; ' : '';
					$club_row .= esc_html( anwp_football_leagues()->club->get_club_title_by_id( $club_id ) );
				}
			}

			$player_field = esc_html( $player->player_name );

			if ( $player_link ) {
				$player_field = '<a href="' . esc_url( $links_map[ $player->player_id ] ) . '">' . esc_html( $player->player_name ) . '</a>';
			}

			$data_row = [
				'player'         => $player_field,
				'sort_name'      => esc_attr( '. ' === mb_substr( $player->player_name, 1, 2 ) ? mb_substr( $player->player_name, 3 ) : $player->player_name ),
				'club'           => $club_row,
				'position'       => esc_html( anwp_fl()->player->get_position_l10n( $player->position ) ),
				'played'         => (int) $player->played,
				'started'        => (int) $player->started,
				'minutes'        => (int) $player->minutes,
				'card_y'         => (int) $player->sum_card_y,
				'card_yr'        => (int) $player->sum_card_yr,
				'card_r'         => (int) $player->sum_card_r,
				'goals'          => (int) $player->sum_goals,
				'goals_penalty'  => (int) $player->sum_goals_penalty,
				'goals_own'      => (int) $player->sum_goals_own,
				'assists'        => (int) $player->sum_assists,
				'goals_conceded' => (int) $player->sum_conceded,
				'clean_sheets'   => (int) $player->clean_sheets,
			];

			$output[] = $data_row;
		}

		return $output;
	}

	/**
	 * Get Player Data.
	 *
	 * @param int $post_id
	 *
	 * @return array [
	 *        'player_id' => 0,
	 *        'name' => 'John Doe',
	 *        'full_name' => 'John Doe',
	 *        'short_name' => 'J. Doe',
	 *        'weight' => '70',
	 *        'height' => '181',
	 *        'position' => 'm',
	 *        'team_id' => 12211,
	 *        'national_team' => 0,
	 *        'nationality' => 'tr',
	 *        'nationality_extra' => '',
	 *        'place_of_birth' => 'Manisa',
	 *        'country_of_birth' => 'tr',
	 *        'date_of_birth' => '2004-01-20',
	 *        'date_of_death' => '0000-00-00',
	 *        'player_external_id' => '',
	 *        'photo' => '/2022/07/128903.jpg',
	 *        'photo_sm' => '',
	 *        'link' => '',
	 *        'current_season_id' => '',
	 *        'competition_matches' => '',
	 *        'card_icons' => '',
	 *        'series_map' => '',
	 *        'club_id' => '',
	 *        'position_code' => '',
	 *        'club_title' => '',
	 *        'club_link' => '',
	 *    ]
	 * @since 0.9.2
	 */
	public function get_player_data( int $post_id ): array {

		static $output = null;

		if ( null === $output || empty( $output[ $post_id ] ) ) {

			$season_id = anwp_fl_pro()->player->get_post_season( $post_id );

			// Card icons
			$card_icons = [
				'y'  => '<svg class="icon__card m-0"><use xlink:href="#icon-card_y"></use></svg>',
				'r'  => '<svg class="icon__card m-0"><use xlink:href="#icon-card_r"></use></svg>',
				'yr' => '<svg class="icon__card m-0"><use xlink:href="#icon-card_yr"></use></svg>',
			];

			$series_map = anwp_fl()->data->get_series();

			// Get season matches
			$season_matches      = anwp_fl()->player->tmpl_get_latest_matches( $post_id, $season_id );
			$competition_matches = anwp_fl()->player->tmpl_prepare_competition_matches( $season_matches );

			$player_data = [
				'current_season_id'   => $season_id,
				'competition_matches' => $competition_matches,
				'card_icons'          => $card_icons,
				'series_map'          => $series_map,
			];

			$player_data += anwp_fl()->player->get_players_by_ids( [ $post_id ], false )[ $post_id ];

			$player_data['club_id']       = $player_data['team_id']; // compatibility with pre v0.16.0
			$player_data['position_code'] = $player_data['position']; // compatibility with pre v0.16.0

			$player_data['club_title'] = anwp_fl()->club->get_club_title_by_id( $player_data['club_id'] );
			$player_data['club_link']  = anwp_fl()->club->get_club_link_by_id( $player_data['club_id'] );


			$output[ $post_id ] = $player_data;
		}

		return empty( $output[ $post_id ] ) ? [] : $output[ $post_id ];
	}

	/**
	 * Get totals statistic for players.
	 *
	 * @param object $args
	 *
	 * @return array
	 * @since 0.11.3
	 */
	public function get_players_missing_matches( $args ) {

		$args = (object) wp_parse_args(
			$args,
			[
				'competition_id' => '',
				'club_id'        => '',
				'season_id'      => '',
				'all_players'    => 0,
				'finished_only'  => 1,
				'sections'       => '', // cards, other, suspended, injured
			]
		);

		global $wpdb;

		$show_all_players = AnWP_Football_Leagues::string_to_bool( $args->all_players );

		/*
		|--------------------------------------------------------------------
		| Get All Games
		|--------------------------------------------------------------------
		*/
		$matches_args = [
			'sort_by_date'    => 'asc',
			'show_secondary'  => 1,
			'competition_id'  => absint( $args->competition_id ) ?: '',
			'season_id'       => absint( $args->season_id ) ?: '',
			'filter_by_clubs' => absint( $args->club_id ),
			'type'            => AnWP_Football_Leagues::string_to_bool( $args->finished_only ) ? 'result' : '',
		];

		$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $matches_args );

		if ( empty( $matches ) ) {
			return [];
		}

		$matches_ids = wp_parse_id_list( wp_list_pluck( $matches, 'match_id' ) );

		/*
		|--------------------------------------------------------------------
		| Get Players
		|--------------------------------------------------------------------
		*/
		$data    = [];
		$players = [];

		if ( 'missed' !== $args->sections || $show_all_players ) {

			$query = "
			SELECT match_id, player_id, card_y, card_yr, card_r
			FROM {$wpdb->prefix}anwpfl_players
			";

			$query .= $wpdb->prepare( ' WHERE club_id = %d ', $args->club_id );

			if ( ! $show_all_players ) {
				$query .= ' AND ( card_y != 0 OR card_yr != 0 OR card_r != 0 ) ';
			}

			$format = implode( ', ', array_fill( 0, count( $matches_ids ), '%d' ) );
			$query  .= $wpdb->prepare( " AND match_id IN ({$format}) ", $matches_ids ); // phpcs:ignore

			$player_rows = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

			if ( empty( $player_rows ) ) {
				return [];
			}

			foreach ( $player_rows as $player_row ) {

				if ( ! absint( $player_row->match_id ) || ! absint( $player_row->player_id ) ) {
					continue;
				}

				if ( ! isset( $data[ $player_row->match_id ] ) ) {
					$data[ $player_row->match_id ] = [];
				}

				if ( ! isset( $data[ $player_row->match_id ][ $player_row->player_id ] ) ) {
					$data[ $player_row->match_id ][ $player_row->player_id ] = [];
				}

				$players[ absint( $player_row->player_id ) ] = '';

				if ( 'missed' !== $args->sections ) {
					$data[ $player_row->match_id ][ $player_row->player_id ] = [
						'card_y'  => $player_row->card_y,
						'card_yr' => $player_row->card_yr,
						'card_r'  => $player_row->card_r,
					];
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Get Missing Players
		|--------------------------------------------------------------------
		*/
		if ( 'cards' !== $args->sections ) {
			$query = "
				SELECT match_id, player_id, reason, comment
				FROM {$wpdb->prefix}anwpfl_missing_players
				";

			$query .= $wpdb->prepare( ' WHERE club_id = %d ', $args->club_id );

			$format = implode( ', ', array_fill( 0, count( $matches_ids ), '%d' ) );
			$query  .= $wpdb->prepare( " AND match_id IN ({$format}) ", $matches_ids ); // phpcs:ignore

			$missing_rows = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

			foreach ( $missing_rows as $player_row ) {

				if ( ! absint( $player_row->match_id ) || ! absint( $player_row->player_id ) ) {
					continue;
				}

				if ( ! isset( $data[ $player_row->match_id ] ) ) {
					$data[ $player_row->match_id ] = [];
				}

				if ( ! isset( $data[ $player_row->match_id ][ $player_row->player_id ] ) ) {
					$data[ $player_row->match_id ][ $player_row->player_id ] = [];
				}

				$players[ absint( $player_row->player_id ) ] = '';

				$data[ $player_row->match_id ][ $player_row->player_id ]['reason']  = $player_row->reason;
				$data[ $player_row->match_id ][ $player_row->player_id ]['comment'] = $player_row->comment;
			}
		}

		if ( ! empty( $players ) ) {
			/*
			|--------------------------------------------------------------------
			| Get Players` Names
			|--------------------------------------------------------------------
			*/
			$query = "SELECT ID, post_title	FROM $wpdb->posts";

			$format = implode( ', ', array_fill( 0, count( array_keys( $players ) ), '%d' ) );
			$query  .= $wpdb->prepare( " WHERE ID IN ({$format}) ", array_keys( $players ) ); // phpcs:ignore

			$post_rows = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

			foreach ( $post_rows as $post_row ) {
				$players[ $post_row->ID ] = $post_row->post_title;
			}

			asort( $players );
		}

		return [
			'players' => $players,
			'matches' => $matches,
			'data'    => $data,
		];
	}

	/**
	 * Get Player POST season.
	 *
	 * @param int $player_id
	 *
	 * @return int
	 * @since 0.11.7
	 */
	public function get_post_season( $player_id ) {

		static $season_id = null;

		if ( null === $season_id ) {

			$season_id = isset( $_GET['season'] ) ? sanitize_text_field( $_GET['season'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			if ( empty( $season_id ) && 'yes' === AnWPFL_Options::get_value( 'all_season_default' ) ) {
				$season_id = 'all';

				return 'all';
			} elseif ( ! empty( $season_id ) ) {

				if ( 'all' === $season_id ) {
					return 'all';
				}

				// phpcs:ignore WordPress.Security.NonceVerification
				$maybe_season_id = anwp_football_leagues()->season->get_season_id_by_slug( sanitize_key( $_GET['season'] ) );

				if ( absint( $maybe_season_id ) ) {

					$season_id = absint( $maybe_season_id );

					return $season_id;
				}
			}

			// Get Season ID
			$season_id = anwp_football_leagues()->get_active_player_season( $player_id );
		}

		return 'all' === $season_id ? 'all' : absint( $season_id );
	}

	/**
	 * Get Player Statistics
	 *
	 * @param array $args
	 *
	 * @return array
	 * @since 0.14.9
	 */
	public function get_player_statistics( array $args ): array {

		$args = wp_parse_args(
			$args,
			[
				'player_id'      => '',
				'competition_id' => '',
				'show_secondary' => 1,
				'season_id'      => '',
				'league_id'      => '',
				'club_id'        => '',
				'stats'          => '',
				'date_from'      => '',
				'date_to'        => '',
			]
		);

		$stats = wp_parse_slug_list( $args['stats'] );

		if ( ! absint( $args['player_id'] ) || empty( $stats ) ) {
			return [];
		}

		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'FL-PRO-PLAYER_get_player_statistics__' . md5( maybe_serialize( $args ) );

		if ( class_exists( 'AnWPFL_Cache' ) && anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) ) {
			return anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' );
		}

		global $wpdb;

		/*
		|--------------------------------------------------------------------
		| 1 - Get Default Stats
		|--------------------------------------------------------------------
		*/

		// minutes query
		$query_minutes = ', SUM(CASE WHEN (a.time_out >= a.time_in AND a.time_out = 46 ) THEN ( a.time_out - a.time_in - 1 ) WHEN (a.time_out >= a.time_in AND a.time_in = 46 ) THEN ( a.time_out - a.time_in + 1 ) WHEN (a.time_out >= a.time_in) THEN ( a.time_out - a.time_in ) ELSE 0 END ) as minutes';
		$query_minutes = apply_filters( 'anwpflpro/stats-players/minutes_query', $query_minutes );

		// custom stats
		$query_custom_stat = [];
		$custom_stats      = array_map( 'absint', array_filter( $stats, 'absint' ) );

		foreach ( $custom_stats as $stat_custom_id ) {
			$stat_config = anwp_football_leagues_premium()->stats->get_stats_player_match_column_by_id( $stat_custom_id );

			if ( 'simple' !== $stat_config['type'] ) {
				continue;
			}

			$stat_slug = 'c_id__' . absint( $stat_custom_id );

			if ( absint( $stat_config['digits'] ) ) {
				$query_custom_stat[] = " ROUND( SUM( a.{$stat_slug} ), {$stat_config['digits']} ) as $stat_slug";
			} else {
				$query_custom_stat[] = " SUM( a.{$stat_slug} ) as $stat_slug";
			}

			$query_custom_stat[] = " SUM(CASE WHEN a.{$stat_slug} != '' THEN 1 ELSE 0 END) as {$stat_slug}__qty";
		}

		$query_custom_stat = $query_custom_stat ? ( ',' . implode( ',', $query_custom_stat ) ) : '';

		$query = "
		SELECT a.player_id, SUM( a.goals ) as goals, SUM( a.assist ) as assists, SUM( a.goals_conceded ) as goals_conceded, SUM( a.goals_penalty ) as goals_penalty, SUM( a.goals_own ) as own_goals,
			SUM( a.card_y ) as card_y, SUM( a.card_yr ) as card_yr, SUM( a.card_r ) as card_r,
			SUM(CASE WHEN (a.appearance > 0) THEN 1 ELSE 0 END) as played,
			SUM(CASE WHEN (a.appearance = 1 OR a.appearance = 2) THEN 1 ELSE 0 END) as started,
			SUM(CASE WHEN a.appearance = 3 THEN 1 ELSE 0 END) as sub_in,
			SUM(CASE WHEN (a.appearance = 1 AND a.goals_conceded = 0) THEN 1 ELSE 0 END) as clean_sheets
			$query_minutes
			$query_custom_stat
		FROM {$wpdb->prefix}anwpfl_players a
		LEFT JOIN {$wpdb->prefix}anwpfl_matches m ON m.match_id = a.match_id
		";

		// WHERE filter by player ID
		$query .= $wpdb->prepare( ' WHERE a.player_id = %d ', $args['player_id'] );

		// WHERE official games only
		if ( ! absint( $args['competition_id'] ) ) {
			$query .= ' AND m.game_status = 1 ';
		}

		if ( ! empty( $args['date_from'] ) || ! empty( $args['date_to'] ) ) {

			// date_to
			if ( trim( $args['date_to'] ) ) {
				$date_to = explode( ' ', $args['date_to'] )[0];

				if ( anwp_football_leagues()->helper->validate_date( $date_to, 'Y-m-d' ) ) {
					$query .= $wpdb->prepare( ' AND m.kickoff <= %s ', $date_to . ' 23:59:59' );
				}
			}

			// date_from
			if ( trim( $args['date_from'] ) ) {
				$date_from = explode( ' ', $args['date_from'] )[0];

				if ( anwp_football_leagues()->helper->validate_date( $date_from, 'Y-m-d' ) ) {
					$query .= $wpdb->prepare( ' AND m.kickoff >= %s ', $date_from . ' 00:00:00' );
				}
			}
		}

		/**==================
		 * WHERE filter by competition ID
		 *================ */
		// Get competition to filter
		if ( AnWP_Football_Leagues::string_to_bool( $args['show_secondary'] ) && absint( $args['competition_id'] ) ) {
			$query .= $wpdb->prepare( ' AND ( m.competition_id = %d OR m.main_stage_id = %d ) ', $args['competition_id'], $args['competition_id'] );
		} elseif ( absint( $args['competition_id'] ) ) {
			$query .= $wpdb->prepare( ' AND m.competition_id = %d ', $args['competition_id'] );
		}

		/**==================
		 * WHERE filter by season ID
		 *================ */
		if ( intval( $args['season_id'] ) ) {
			$query .= $wpdb->prepare( ' AND m.season_id = %d ', $args['season_id'] );
		}

		/**==================
		 * WHERE filter by league ID
		 *================ */
		if ( intval( $args['league_id'] ) ) {
			$query .= $wpdb->prepare( ' AND m.league_id = %d ', $args['league_id'] );
		}

		/**==================
		 * WHERE filter by club ID
		 *================ */
		if ( intval( $args['club_id'] ) ) {
			$query .= $wpdb->prepare( ' AND a.club_id = %d ', $args['club_id'] );
		}

		$query .= ' GROUP BY a.player_id';

		// phpcs:ignore WordPress.DB.PreparedSQL
		$data = $wpdb->get_row( $query, ARRAY_A ) ?: [];

		/*
		|--------------------------------------------------------------------
		| 2 - Get Manual Stats
		|--------------------------------------------------------------------
		*/
		if ( empty( $args['date_from'] ) || empty( $args['date_to'] ) ) { // ignore manual stats if date_from or date_to are set
			$query = "
				SELECT *
				FROM {$wpdb->prefix}anwpfl_players_manual_stats
			";

			$query .= $wpdb->prepare( 'WHERE player_id = %d ', $args['player_id'] );

			if ( intval( $args['season_id'] ) ) {
				$query .= $wpdb->prepare( ' AND season_id = %d ', $args['season_id'] );
			}

			if ( absint( $args['competition_id'] ) ) {
				$query .= $wpdb->prepare( ' AND competition_id = %d ', $args['competition_id'] );
			}

			// phpcs:ignore WordPress.DB.PreparedSQL
			$manual_data = $wpdb->get_results( $query );

			if ( ! empty( $manual_data ) ) {

				$required_manual_data = array_diff( [ 'played', 'minutes' ], $stats );

				foreach ( $manual_data as $data_row ) {
					foreach ( $stats as $stat_slug ) {
						if ( ! isset( $data[ $stat_slug ] ) || ! isset( $data_row->{$stat_slug} ) ) {
							continue;
						}

						$data[ $stat_slug ] += (int) $data_row->{$stat_slug};
					}

					if ( ! empty( $required_manual_data ) ) {
						foreach ( $required_manual_data as $m_stat_slug ) {
							if ( ! isset( $data[ $m_stat_slug ] ) || ! isset( $data_row->{$m_stat_slug} ) ) {
								continue;
							}

							$data[ $m_stat_slug ] += (int) $data_row->{$m_stat_slug};
						}
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $data ) && class_exists( 'AnWPFL_Cache' ) ) {
			anwp_football_leagues()->cache->set( $cache_key, $data, 'anwp_match' );
		}

		return $data;
	}

	/**
	 * Get Players data for single stat value
	 *
	 * @param array $args
	 *
	 * @return array [
	 *      [
	 *          'player_id' => 1,
	 *          'name'      => 'Player Name', // Player ID
	 *          'photo'     => 'photo url',
	 *           'link'     => 'player profile url',
	 *          'clubs'     => '1,2,3',
	 *          'stat'      => 12,
	 *          'gp'        => 30, // Games Played
	 *      ]
	 * ]
	 *
	 * @since 0.12.0
	 */
	public function get_players_single_stat( array $args ): array {
		$args = wp_parse_args(
			$args,
			[
				'competition_id' => '',
				'multistage'     => 0,
				'season_id'      => '',
				'league_id'      => '',
				'club_id'        => '',
				'type'           => '',
				'links'          => 0,
				'stat'           => '',
				'limit'          => 10,
				'soft_limit'     => 0,
				'soft_limit_qty' => '',
				'offset'         => 0,
				'hide_zero'      => 1,
				'date_from'      => '',
				'date_to'        => '',
			]
		);

		global $wpdb;

		$player_yr_card_count = AnWPFL_Options::get_value( 'player_yr_card_count', 'yyr' );

		/*
		|--------------------------------------------------------------------
		| Handle Default Stats
		|--------------------------------------------------------------------
		*/
		$default_stats = [
			'appearance',
			'started',
			'minutes',
			'cards_all',
			'cards_y',
			'cards_r',
			'goals',
			'goals_penalty',
			'goals_own',
			'assists',
			'goals_conceded',
			'clean_sheets',
		];

		if ( in_array( mb_strtolower( $args['stat'] ), $default_stats, true ) ) {

			$query_stat = '';

			switch ( $args['stat'] ) {
				case 'appearance':
					$query_stat = 'SUM( CASE WHEN (a.appearance > 0) THEN 1 ELSE 0 END )';
					break;

				case 'started':
					$query_stat = 'SUM( CASE WHEN (a.appearance = 1 OR a.appearance = 2) THEN 1 ELSE 0 END )';
					break;

				case 'minutes':
					$query_stat = 'SUM( CASE WHEN (a.time_out >= a.time_in AND a.time_out = 46 ) THEN ( a.time_out - a.time_in - 1 ) WHEN (a.time_out >= a.time_in AND a.time_in = 46 ) THEN ( a.time_out - a.time_in + 1 ) WHEN (a.time_out >= a.time_in) THEN ( a.time_out - a.time_in ) ELSE 0 END )';
					$query_stat = apply_filters( 'anwpflpro/stat-players/minutes_query', $query_stat );
					break;

				case 'cards_all':
					$query_stat = '( SUM( a.card_y ) + SUM( a.card_yr ) + SUM( a.card_r ) )';
					break;

				case 'cards_y':
					if ( 'yr' === $player_yr_card_count ) {
						$query_stat = 'SUM( CASE WHEN a.card_yr > 0 THEN 0 ELSE a.card_y END )';
					} else {
						$query_stat = '( SUM( a.card_y ) + SUM( a.card_yr ) )';
					}
					break;

				case 'cards_r':
					$query_stat = '( SUM( a.card_yr ) + SUM( a.card_r ) )';
					break;

				case 'goals':
					$query_stat = 'SUM( a.goals )';
					break;

				case 'goals_penalty':
					$query_stat = 'SUM( a.goals_penalty )';
					break;

				case 'goals_own':
					$query_stat = 'SUM( a.goals_own )';
					break;

				case 'assists':
					$query_stat = 'SUM( a.assist )';
					break;

				case 'goals_conceded':
					$query_stat = 'SUM( a.goals_conceded )';
					break;

				case 'clean_sheets':
					$query_stat = 'SUM( CASE WHEN (a.appearance = 1 AND a.goals_conceded = 0 AND p.position = "g") THEN 1 ELSE 0 END )';
					break;
			}

			if ( empty( $query_stat ) ) {
				return [];
			}

			$query =
				"
					SELECT
						a.player_id, p.name, p.photo,
						GROUP_CONCAT(DISTINCT a.club_id) as clubs,
						$query_stat as stat,
						SUM( CASE WHEN (a.appearance > 0) THEN 1 ELSE 0 END ) as gp
					FROM {$wpdb->prefix}anwpfl_players a
					LEFT JOIN {$wpdb->prefix}anwpfl_matches m ON m.match_id = a.match_id
					LEFT JOIN {$wpdb->prefix}anwpfl_player_data p ON p.player_id = a.player_id
					WHERE m.game_status = 1
				";

			/*
			|--------------------------------------------------------------------
			| Handle Custom Stats
			|--------------------------------------------------------------------
			*/
		} elseif ( absint( $args['stat'] ) ) {
			$stat_item = anwp_fl_pro()->stats->get_stats_player_match_column_by_id( $args['stat'] );

			if ( empty( $stat_item['type'] ) || 'simple' !== $stat_item['type'] ) {
				return [];
			}

			$stat_column = 'c_id__' . absint( $stat_item['id'] );

			$query =
				"
					SELECT
						a.player_id, p.name, p.photo,
						GROUP_CONCAT(DISTINCT a.club_id) as clubs,
						SUM( a.{$stat_column} ) as stat,
						SUM( CASE WHEN (a.appearance > 0) THEN 1 ELSE 0 END ) as gp
					FROM {$wpdb->prefix}anwpfl_players a
					LEFT JOIN {$wpdb->prefix}anwpfl_matches m ON m.match_id = a.match_id
					LEFT JOIN {$wpdb->prefix}anwpfl_player_data p ON p.player_id = a.player_id
					WHERE 1 = 1
				";

		} else {
			return [];
		}

		/*
		|--------------------------------------------------------------------
		| only official games if competition_id not set
		|--------------------------------------------------------------------
		*/
		if ( ! absint( $args['competition_id'] ) ) {
			$query .= ' AND m.game_status = 1 ';
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by season ID
		|--------------------------------------------------------------------
		*/
		if ( intval( $args['season_id'] ) ) {
			$query .= $wpdb->prepare( ' AND m.season_id = %d ', $args['season_id'] );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by league ID
		|--------------------------------------------------------------------
		*/
		if ( intval( $args['league_id'] ) ) {
			$query .= $wpdb->prepare( ' AND m.league_id = %d ', $args['league_id'] );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by club ID
		|--------------------------------------------------------------------
		*/
		if ( intval( $args['club_id'] ) ) {
			$query .= $wpdb->prepare( ' AND a.club_id = %d ', $args['club_id'] );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by competition ID
		|--------------------------------------------------------------------
		*/
		if ( AnWP_Football_Leagues::string_to_bool( $args['multistage'] ) && absint( $args['competition_id'] ) ) {
			$query .= $wpdb->prepare( ' AND ( m.competition_id = %d OR m.main_stage_id = %d ) ', $args['competition_id'], $args['competition_id'] );
		} elseif ( absint( $args['competition_id'] ) ) {
			$query .= $wpdb->prepare( ' AND m.competition_id = %d ', $args['competition_id'] );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by player type
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $args['type'] ) ) {
			if ( 'g' === $args['type'] ) {
				$query .= ' AND p.position = "g"';
			} else {
				$query .= ' AND p.position != "g" AND p.position != ""';
			}
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by date
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $args['date_from'] ) || ! empty( $args['date_to'] ) ) {

			// date_to
			if ( trim( $args['date_to'] ) ) {
				$date_to = explode( ' ', $args['date_to'] )[0];

				if ( anwp_football_leagues()->helper->validate_date( $date_to, 'Y-m-d' ) ) {
					$query .= $wpdb->prepare( ' AND m.kickoff <= %s ', $date_to . ' 23:59:59' );
				}
			}

			// date_from
			if ( trim( $args['date_from'] ) ) {
				$date_from = explode( ' ', $args['date_from'] )[0];

				if ( anwp_football_leagues()->helper->validate_date( $date_from, 'Y-m-d' ) ) {
					$query .= $wpdb->prepare( ' AND m.kickoff >= %s ', $date_from . ' 00:00:00' );
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Group by Player
		|--------------------------------------------------------------------
		*/
		$query .= ' GROUP BY a.player_id';

		/*
		|--------------------------------------------------------------------
		| Soft Limit & Hide Zero
		|--------------------------------------------------------------------
		*/
		$hide_zero = AnWP_Football_Leagues::string_to_bool( $args['hide_zero'] );

		if ( absint( $args['soft_limit_qty'] ) ) {
			if ( $hide_zero ) {
				$query .= $wpdb->prepare( ' HAVING stat >= %d AND stat != 0 ', $args['soft_limit_qty'] );
			} else {
				$query .= $wpdb->prepare( ' HAVING stat >= %d ', $args['soft_limit_qty'] );
			}
		} elseif ( $hide_zero ) {
			$query .= ' HAVING stat != 0 ';
		}

		/*
		|--------------------------------------------------------------------
		| Order
		|--------------------------------------------------------------------
		*/
		$query .= ' ORDER BY stat DESC';

		/*
		|--------------------------------------------------------------------
		| LIMIT clause
		|--------------------------------------------------------------------
		*/
		if ( absint( $args['limit'] ) ) {

			if ( absint( $args['offset'] ) ) {
				$query .= $wpdb->prepare( ' LIMIT %d, %d', $args['offset'], $args['limit'] );
			} else {
				$query .= $wpdb->prepare( ' LIMIT %d', $args['limit'] );
			}

			if ( AnWP_Football_Leagues::string_to_bool( $args['soft_limit'] ) ) {
				$soft_limit_qty = $wpdb->get_row( $query, OBJECT, ( $args['limit'] - 1 ) ); // phpcs:ignore WordPress.DB.PreparedSQL

				if ( ! empty( $soft_limit_qty ) && isset( $soft_limit_qty->stat ) ) {
					$args['limit']          = 0;
					$args['soft_limit']     = 0;
					$args['soft_limit_qty'] = $soft_limit_qty->stat;

					return $this->get_players_single_stat( $args );
				}
			}
		}

		// phpcs:ignore WordPress.DB.PreparedSQL
		$rows = $wpdb->get_results( $query, ARRAY_A );

		/*
		|--------------------------------------------------------------------
		| Link To Profile
		|--------------------------------------------------------------------
		*/
		if ( AnWP_Football_Leagues::string_to_bool( $args['links'] ) ) {
			$links_map = anwp_fl()->helper->get_permalinks_by_ids( wp_list_pluck( $rows, 'player_id' ), 'anwp_player' );

			foreach ( $rows as $row_id => $player ) {
				$rows[ $row_id ]['link'] = $links_map[ $player['player_id'] ] ?? '';
			}
		}

		return $rows;
	}

	/**
	 * Get serialized data for the Stat Players
	 *
	 * @param $data
	 *
	 * @return string
	 * @since 0.12.0
	 */
	public function get_serialized_stat_players_data( $data ) {

		$default_data = [
			'competition_id' => '',
			'multistage'     => 0,
			'season_id'      => '',
			'league_id'      => '',
			'club_id'        => '',
			'type'           => '',
			'links'          => 0,
			'stat'           => '',
			'photos'         => 0,
			'games_played'   => 0,
			'hide_zero'      => 1,
		];

		return wp_json_encode( array_intersect_key( wp_parse_args( $data, $default_data ), $default_data ) );
	}

	/**
	 * Get Players Stat (Single value)
	 *
	 * @since 0.12.0
	 */
	public function get_stat_players( WP_REST_Request $request ) {

		$args = $request->get_params();

		// Sanitize and validate
		$data = [
			'competition_id' => empty( $args['competition_id'] ) ? '' : absint( $args['competition_id'] ),
			'multistage'     => empty( $args['multistage'] ) ? 0 : absint( $args['multistage'] ),
			'season_id'      => empty( $args['season_id'] ) ? '' : absint( $args['season_id'] ),
			'league_id'      => empty( $args['league_id'] ) ? '' : absint( $args['league_id'] ),
			'club_id'        => empty( $args['club_id'] ) ? '' : absint( $args['club_id'] ),
			'type'           => empty( $args['type'] ) ? '' : sanitize_key( $args['type'] ),
			'links'          => empty( $args['links'] ) ? 0 : absint( $args['links'] ),
			'stat'           => empty( $args['stat'] ) ? '' : sanitize_text_field( $args['stat'] ),
			'photos'         => empty( $args['photos'] ) ? 0 : absint( $args['photos'] ),
			'games_played'   => empty( $args['games_played'] ) ? 0 : absint( $args['games_played'] ),
			'hide_zero'      => empty( $args['hide_zero'] ) ? 0 : absint( $args['hide_zero'] ),
			'soft_limit'     => 0,
			'limit'          => 21,
			'first_em'       => 0,
			'offset'         => empty( $args['loaded'] ) ? 0 : absint( $args['loaded'] ),
		];

		$stat_rows = $this->get_players_single_stat( $data );

		// Check next time "load more"
		$next_load = count( $stat_rows ) > 20;

		if ( $next_load ) {
			array_pop( $stat_rows );
		}

		/*
		|--------------------------------------------------------------------
		| Prepare Output
		|--------------------------------------------------------------------
		*/
		$show_link    = AnWP_Football_Leagues::string_to_bool( $data['links'] );
		$show_photos  = AnWP_Football_Leagues::string_to_bool( $data['photos'] );
		$games_played = AnWP_Football_Leagues::string_to_bool( $data['games_played'] );

		$photo_dir     = wp_upload_dir()['baseurl'];
		$photo_default = anwp_football_leagues()->helper->get_default_player_photo();

		// Start output
		ob_start();

		foreach ( $stat_rows as $row_index => $player_stat ) {

			/** @var array $player_stat = [
			 *           'player_id' => 1, // Player ID
			 *           'name'      => 'Player Name',
			 *           'photo'     => 'photo url',
			 *           'link'      => 'player profile url',
			 *           'clubs'     => '1,2,3',
			 *           'stat'      => 12,
			 *           'gp'        => 30, // Games Played
			 *  ] */

			$index = $row_index + $data['offset'];
			?>
			<div class="d-flex align-items-center py-1 px-2 <?php echo esc_attr( $index > 0 ? 'anwp-fl-border-top anwp-border-light' : '' ); ?>">
				<div class="stat-players__place anwp-w-30 pr-2 anwp-text-center"><?php echo absint( $index + 1 ); ?></div>

				<?php if ( $show_photos ) : ?>
					<div class="stat-players__photo mr-2 anwp-flex-none">
						<img
								alt="<?php echo esc_html( $player_stat['name'] ); ?>"
								class="stat-players__photo_img anwp-w-40 anwp-h-40 mb-0"
								src="<?php echo esc_url( $player_stat['photo'] ? ( $photo_dir . $player_stat['photo'] ) : $photo_default ); ?>">
					</div>
				<?php endif; ?>

				<div class="stat-players__clubs my-n1 anwp-flex-none d-flex mr-2">
					<?php
					foreach ( explode( ',', $player_stat['clubs'] ) as $club_id ) :
						$club_logo = anwp_football_leagues()->club->get_club_logo_by_id( $club_id );

						if ( $club_logo ) :
							?>
							<img
								alt="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $club_id ) ); ?>"
								class="anwp-w-30 anwp-h-30 anwp-object-contain mr-1 mb-0" src="<?php echo esc_url( $club_logo ); ?>"
								data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $club_id ) ); ?>">
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<div class="stat-players__name pr-2">
					<?php if ( $show_link && ! empty( $player_stat['link'] ) ) : ?>
						<a class="text-decoration-none anwp-link-without-effects" href="<?php echo esc_attr( $player_stat['link'] ); ?>">
							<?php echo esc_html( $player_stat['name'] ); ?>
						</a>
					<?php else : ?>
						<?php echo esc_html( $player_stat['name'] ); ?>
					<?php endif; ?>
				</div>

				<div class="stat-players__stat ml-auto"><?php echo esc_html( empty( $player_stat['stat'] ) ? 0 : $player_stat['stat'] ); ?></div>

				<?php if ( $games_played ) : ?>
					<div class="stat-players__gp ml-2 anwp-opacity-60">(<?php echo esc_html( $player_stat['gp'] ); ?>)</div>
				<?php endif; ?>
			</div>
			<?php
		}

		$html_output = ob_get_clean();

		return rest_ensure_response(
			[
				'html'   => $html_output,
				'next'   => $next_load,
				'offset' => $data['offset'] + count( $stat_rows ),
			]
		);
	}

	/**
	 * Get Stat :: Players "stat" argument options.
	 * Used in widget.
	 *
	 * @since 0.12.0
	 */
	public function get_stat_players_options() {
		$options = [
			'appearance'     => esc_html__( 'Appearance', 'anwp-football-leagues-premium' ),
			'started'        => esc_html__( 'Started', 'anwp-football-leagues-premium' ),
			'minutes'        => esc_html__( 'Minutes', 'anwp-football-leagues' ),
			'cards_all'      => esc_html__( 'Cards (All)', 'anwp-football-leagues-premium' ),
			'cards_y'        => esc_html__( 'Cards - Yellow', 'anwp-football-leagues-premium' ),
			'cards_r'        => esc_html__( 'Cards - Red', 'anwp-football-leagues-premium' ),
			'goals'          => esc_html__( 'Goals', 'anwp-football-leagues' ),
			'goals_penalty'  => esc_html__( 'Goal From Penalty', 'anwp-football-leagues' ),
			'goals_own'      => esc_html__( 'Own Goals', 'anwp-football-leagues' ),
			'assists'        => esc_html__( 'Assists', 'anwp-football-leagues' ),
			'goals_conceded' => esc_html__( 'Goals Conceded', 'anwp-football-leagues' ),
			'clean_sheets'   => esc_html__( 'Clean Sheets', 'anwp-football-leagues' ),
		];

		foreach ( anwp_football_leagues_premium()->stats->get_options_match_player_stats_simple() as $stat_id => $stat_title ) {
			$options[ $stat_id ] = $stat_title;
		}

		return $options;
	}
}
