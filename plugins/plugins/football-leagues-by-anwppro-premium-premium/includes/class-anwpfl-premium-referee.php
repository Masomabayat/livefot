<?php
/**
 * AnWP Football Leagues Premium :: Referee
 *
 * @since 0.14.8
 */
class AnWPFL_Premium_Referee {

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
		add_action( 'init', [ $this, 'add_custom_fields_support' ] );
	}

	/**
	 * Add Custom Fields support to referees
	 */
	public function add_custom_fields_support() {
		add_post_type_support(
			'anwp_referee',
			[
				'title',
				'comments',
				'custom-fields',
			]
		);
	}

	/**
	 * Get array of matches for widget and shortcode.
	 *
	 * @param object|array $options
	 *
	 * @since 0.14.8
	 * @return array|null|object
	 */
	public function get_referees_stats( $options ) {

		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'FL-PRO-REFEREE_get_referees_stats__' . md5( maybe_serialize( $options ) );

		if ( class_exists( 'AnWPFL_Cache' ) && anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) ) {
			return anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' );
		}

		global $wpdb;

		$options = (object) wp_parse_args(
			$options,
			[
				'competition_id'       => '',
				'show_secondary'       => '',
				'season_id'            => '',
				'league_id'            => '',
				'filter_by_clubs'      => '',
				'filter_by_matchweeks' => '',
				'limit'                => '',
				'date_from'            => '',
				'date_to'              => '',
				'exclude_ids'          => '',
				'include_ids'          => '',
				'home_club'            => '',
				'away_club'            => '',
			]
		);

		$query = "
		SELECT g.*, g.referee as referee_id
		FROM {$wpdb->prefix}anwpfl_matches g
		WHERE g.finished = 1 AND g.referee != '' ";

		/**==================
		 * WHERE filter by competition
		 *================ */
		if ( ! empty( $options->competition_id ) ) {
			if ( AnWP_Football_Leagues::string_to_bool( $options->show_secondary ) ) {
				$competition_ids = wp_parse_id_list( $options->competition_id );
				$format          = implode( ', ', array_fill( 0, count( $competition_ids ), '%d' ) );

				$query .= $wpdb->prepare( " AND ( g.competition_id IN ({$format}) OR g.main_stage_id IN ({$format}) ) ", array_merge( $competition_ids, $competition_ids ) ); // phpcs:ignore
			} else {

				$competition_ids = wp_parse_id_list( $options->competition_id );
				$format          = implode( ', ', array_fill( 0, count( $competition_ids ), '%d' ) );

				$query .= $wpdb->prepare( " AND g.competition_id IN ({$format}) ", $competition_ids ); // phpcs:ignore
			}
		}

		/**==================
		 * WHERE filter by season
		 *================ */
		if ( ! empty( $options->season_id ) ) {
			$query .= $wpdb->prepare( ' AND g.season_id = %d ', $options->season_id );
		}

		/**==================
		 * WHERE filter by league
		 *================ */
		if ( absint( $options->league_id ) ) {
			$query .= $wpdb->prepare( ' AND g.league_id = %d ', $options->league_id );
		}

		/**==================
		 * WHERE filter by date_to
		 *================ */
		if ( trim( $options->date_to ) ) {
			$date_to = explode( ' ', $options->date_to )[0];

			if ( anwp_football_leagues()->helper->validate_date( $date_to, 'Y-m-d' ) ) {
				$query .= $wpdb->prepare( ' AND g.kickoff <= %s ', $date_to . ' 23:59:59' );
			}
		}

		/**==================
		 * WHERE filter by club
		 *================ */
		if ( ! empty( $options->filter_by_clubs ) ) {

			$clubs  = wp_parse_id_list( $options->filter_by_clubs );
			$format = implode( ', ', array_fill( 0, count( $clubs ), '%d' ) );

			$query .= $wpdb->prepare( " AND ( g.home_club IN ({$format}) OR g.away_club IN ({$format}) ) ", array_merge( $clubs, $clubs ) ); // phpcs:ignore
		}

		if ( ! empty( $options->home_club ) ) {
			$query .= $wpdb->prepare( ' AND g.home_club = %d ', $options->home_club );
		}

		if ( ! empty( $options->away_club ) ) {
			$query .= $wpdb->prepare( ' AND g.away_club = %d ', $options->away_club );
		}

		/**==================
		 * WHERE filter by matchweek
		 *================ */
		if ( ! empty( $options->filter_by_matchweeks ) ) {

			$rounds = wp_parse_id_list( $options->filter_by_matchweeks );
			$format = implode( ', ', array_fill( 0, count( $rounds ), '%d' ) );

			$query .= $wpdb->prepare( " AND g.match_week IN ({$format}) ", $rounds ); // phpcs:ignore
		}

		/**==================
		 * WHERE filter by date_from
		 *================ */
		if ( trim( $options->date_from ) ) {
			$date_from = explode( ' ', $options->date_from )[0];

			if ( anwp_football_leagues()->helper->validate_date( $date_from, 'Y-m-d' ) ) {
				$query .= $wpdb->prepare( ' AND g.kickoff >= %s ', $date_from . ' 00:00:00' );
			}
		}

		/**==================
		 * WHERE exclude ids
		 *================ */
		if ( trim( $options->exclude_ids ) ) {
			$exclude_ids = wp_parse_id_list( $options->exclude_ids );

			if ( ! empty( $exclude_ids ) && is_array( $exclude_ids ) && count( $exclude_ids ) ) {

				// Prepare exclude format and placeholders
				$exclude_placeholders = array_fill( 0, count( $exclude_ids ), '%s' );
				$exclude_format       = implode( ', ', $exclude_placeholders );

				$query .= $wpdb->prepare( " AND g.match_id NOT IN ({$exclude_format})", $exclude_ids ); // phpcs:ignore
			}
		}

		/**==================
		 * WHERE include ids
		 *================ */
		if ( trim( $options->include_ids ) ) {
			$include_ids = wp_parse_id_list( $options->include_ids );

			if ( ! empty( $include_ids ) && is_array( $include_ids ) && count( $include_ids ) ) {

				// Prepare include format and placeholders
				$include_placeholders = array_fill( 0, count( $include_ids ), '%s' );
				$include_format       = implode( ', ', $include_placeholders );

				$query .= $wpdb->prepare( " AND g.match_id IN ({$include_format})", $include_ids ); // phpcs:ignore
			}
		}

		/**==================
		 * ORDER BY
		 *================ */
		$query .= ' ORDER BY g.kickoff DESC';

		/**==================
		 * LIMIT clause
		 *================ */
		if ( isset( $options->limit ) && 0 < $options->limit ) {
			$query .= $wpdb->prepare( ' LIMIT %d', $options->limit );
		}

		$games = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $games ) ) {
			return [];
		}

		$output = [];

		$stat_fields = [
			'home_cards_y'  => 'card_y_h',
			'away_cards_y'  => 'card_y_a',
			'home_cards_yr' => 'home_cards_yr',
			'away_cards_yr' => 'away_cards_yr',
			'home_cards_r'  => 'card_r_h',
			'away_cards_r'  => 'card_r_a',
			'home_fouls'    => 'fouls_h',
			'away_fouls'    => 'fouls_a',
		];

		foreach ( $games as $game ) {
			if ( empty( $output[ $game->referee_id ] ) ) {
				$output[ $game->referee_id ]          = array_fill_keys( $stat_fields, 0 );
				$output[ $game->referee_id ]['games'] = 0;
			}

			foreach ( $stat_fields as $stat_field => $stat_alias ) {
				$output[ $game->referee_id ][ $stat_alias ] += $game->{$stat_field};
			}

			$output[ $game->referee_id ]['games'] ++;
		}

		$yr_count = AnWPFL_Options::get_value( 'yr_card_count', 'r' );

		foreach ( $output as $referee_id => $referee_stats ) {
			$output[ $referee_id ]['card_y_h'] += ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? absint( $output[ $referee_id ]['home_cards_yr'] ) : 0 );
			$output[ $referee_id ]['card_y_a'] += ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? absint( $output[ $referee_id ]['away_cards_yr'] ) : 0 );
			$output[ $referee_id ]['card_r_h'] += ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? absint( $output[ $referee_id ]['home_cards_yr'] ) : 0 );
			$output[ $referee_id ]['card_r_a'] += ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? absint( $output[ $referee_id ]['away_cards_yr'] ) : 0 );

			$output[ $referee_id ]['card_y'] = $output[ $referee_id ]['card_y_h'] + $output[ $referee_id ]['card_y_a'];
			$output[ $referee_id ]['card_r'] = $output[ $referee_id ]['card_r_h'] + $output[ $referee_id ]['card_r_a'];
			$output[ $referee_id ]['fouls']  = $output[ $referee_id ]['fouls_h'] + $output[ $referee_id ]['fouls_a'];
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $data ) && class_exists( 'AnWPFL_Cache' ) ) {
			anwp_football_leagues()->cache->set( $cache_key, $data, 'anwp_match' );
		}

		return $output;
	}

	/**
	 * Get list of all referee names.
	 *
	 * @since 0.14.9
	 * @return array $output_data
	 */
	public function get_referee_name_list() {

		$cache_key = 'FL-PRO-REFEREES-NAME-LIST';

		if ( anwp_football_leagues()->cache->get( $cache_key ) ) {
			return anwp_football_leagues()->cache->get( $cache_key );
		}

		global $wpdb;

		$all_officials = $wpdb->get_results(
			"
			SELECT p.ID id, p.post_title name,
				MAX( CASE WHEN pm.meta_key = '_anwpfl_short_name' THEN pm.meta_value ELSE '' END) as short_name,
				MAX( CASE WHEN pm.meta_key = '_anwpfl_full_name' THEN pm.meta_value ELSE '' END) as full_name
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm ON ( pm.post_id = p.ID )
			WHERE p.post_status = 'publish' AND p.post_type = 'anwp_referee'
			GROUP BY p.ID
			ORDER BY p.post_title
			"
		);

		/*
		|--------------------------------------------------------------------
		| Prepare referee Data
		|--------------------------------------------------------------------
		*/
		if ( empty( $all_officials ) ) {
			return [];
		}

		anwp_football_leagues()->cache->set( $cache_key, $all_officials );

		return $all_officials;
	}

	/**
	 * Get Referee api text
	 *
	 * @return array
	 * @since 0.14.10
	 */
	public function get_referee_api_text() {
		global $wpdb;

		$output = [];

		$referees = $wpdb->get_results(
			"
				SELECT post_id, meta_value
				FROM $wpdb->postmeta
				WHERE meta_key = 'fl_referee_api_name' AND meta_value != ''
			"
		);

		if ( empty( $referees ) ) {
			return $output;
		}

		foreach ( $referees as $referee ) {
			$output[ $referee->meta_value ] = $referee->post_id;
		}

		return $output;
	}

	/**
	 * Find referee by name part.
	 *
	 * @return int|string
	 * @since 0.14.9
	 */
	public function find_referee_by_name( $name_part, $referee_api ) {

		static $referee_api_text = null;
		static $referee_names    = null;

		if ( null === $referee_api_text ) {
			$referee_api_text = $this->get_referee_api_text();
		}

		if ( ! empty( $referee_api_text[ $referee_api ] ) ) {
			return $referee_api_text[ $referee_api ];
		}

		if ( ! empty( $referee_api_text[ sanitize_title( $referee_api ) ] ) ) {
			return $referee_api_text[ sanitize_title( $referee_api ) ];
		}

		$referee_id = 0;

		if ( null === $referee_names ) {
			$referee_names = $this->get_referee_name_list();
		}

		foreach ( $referee_names as $referee_name ) {

			if ( mb_strtolower( $name_part ) === mb_strtolower( $referee_name->name ) ) {
				$referee_id = $referee_name->id;
				break;
			}

			if ( sanitize_title( mb_strtolower( $name_part ) ) === sanitize_title( mb_strtolower( $referee_name->name ) ) ) {
				$referee_id = $referee_name->id;
				break;
			}

			if ( mb_strtolower( $name_part ) === mb_strtolower( $referee_name->full_name ) ) {
				$referee_id = $referee_name->id;
				break;
			}

			if ( sanitize_title( mb_strtolower( $name_part ) ) === sanitize_title( mb_strtolower( $referee_name->full_name ) ) ) {
				$referee_id = $referee_name->id;
				break;
			}

			if ( mb_strtolower( $name_part ) === mb_strtolower( $referee_name->short_name ) ) {
				$referee_id = $referee_name->id;
				break;
			}

			if ( sanitize_title( mb_strtolower( $name_part ) ) === sanitize_title( mb_strtolower( $referee_name->short_name ) ) ) {
				$referee_id = $referee_name->id;
				break;
			}
		}

		if ( empty( $referee_id ) && false === mb_strpos( $name_part, '.' ) && 2 === count( explode( ' ', $name_part, 2 ) ) ) {

			$name_part_short = mb_substr( $name_part, 0, 1 ) . '. ' . explode( ' ', $name_part, 2 )[1];

			foreach ( $referee_names as $referee_index => $referee_name ) {
				if ( mb_strtolower( $name_part_short ) === mb_strtolower( $referee_name->name ) && empty( $referee_name->full_name ) ) {

					$referee_names[ $referee_index ]->full_name = $name_part;
					update_post_meta( $referee_name->id, '_anwpfl_full_name', $name_part );
					delete_transient( 'FL-PRO-REFEREES-NAME-LIST' );

					$referee_id = $referee_name->id;
					break;
				}

				if ( mb_strtolower( $name_part_short ) === mb_strtolower( $referee_name->short_name ) && empty( $referee_name->full_name ) ) {

					$referee_names[ $referee_index ]->full_name = $name_part;
					update_post_meta( $referee_name->id, '_anwpfl_full_name', $name_part );
					delete_transient( 'FL-PRO-REFEREES-NAME-LIST' );

					$referee_id = $referee_name->id;
					break;
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Create New Referee
		|--------------------------------------------------------------------
		*/
		if ( empty( $referee_id ) ) {
			if ( false !== mb_strpos( $name_part, '.' ) || false === mb_strpos( $name_part, ' ' ) ) {
				$short_name = sanitize_text_field( $name_part );
			} else {
				$short_name = sanitize_text_field( mb_substr( $name_part, 0, 1 ) . '. ' . ( explode( ' ', $name_part, 2 )[1] ?? '' ) );
			}

			$post_data = [
				'post_status' => 'publish',
				'post_type'   => 'anwp_referee',
				'post_title'  => $short_name,
				'meta_input'  => [
					'fl_referee_api_name' => $referee_api,
					'_anwpfl_short_name'  => $short_name,
					'_anwpfl_full_name'   => false === mb_strpos( $name_part, '.' ) ? sanitize_text_field( $name_part ) : '',
				],
			];

			$referee_id = wp_insert_post( $post_data );

			if ( $referee_id ) {
				$referee_api_text[ $referee_api ] = $referee_id;
			}
		}

		return $referee_id ?: '';
	}
}
