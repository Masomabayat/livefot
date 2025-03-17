<?php
/**
 * AnWP Football Leagues Premium :: Charts
 *
 * @since   0.11.10
 */

class AnWPFL_Premium_Charts {

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
	 * @since  0.11.10
	 */
	public function hooks() {

	}

	/**
	 * Get statistic of goals per 15 minutes.
	 *
	 * @param object $args
	 *
	 * @return array
	 * @since 0.11.10
	 */
	public function get_stats_goals_15( $args ) {

		$args = (object) wp_parse_args(
			$args,
			[
				'competition_id' => '',
				'season_id'      => '',
				'league_id'      => '',
				'club_id'        => '',
				'multistage'     => '',
				'limit'          => '',
			]
		);

		if ( empty( $args->club_id ) || ! absint( $args->club_id ) ) {
			return [];
		}

		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'FL-PRO-CHARTS_get_stats_goals_15__' . md5( maybe_serialize( $args ) );

		if ( class_exists( 'AnWPFL_Cache' ) && anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) ) {
			return anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' );
		}

		/*
		|--------------------------------------------------------------------
		| Load data in default way
		|--------------------------------------------------------------------
		*/
		global $wpdb;

		/*
		|--------------------------------------------------------------------
		| Get all filtered events JSON arrays
		|--------------------------------------------------------------------
		*/
		$query = "
		SELECT
			m.home_club,
			m.away_club,
			m.match_events as events
		FROM {$wpdb->prefix}anwpfl_matches m
		WHERE 1=1
		";

		/**==================
		 * WHERE filter by season ID
		 *================ */
		if ( intval( $args->season_id ) ) {
			$query .= $wpdb->prepare( ' AND m.season_id = %d ', $args->season_id );
		}

		/**==================
		 * WHERE filter by league ID
		 *================ */
		if ( intval( $args->league_id ) ) {
			$query .= $wpdb->prepare( ' AND m.league_id = %d ', $args->league_id );
		}

		/**==================
		 * WHERE filter by club ID
		 *================ */
		if ( absint( $args->club_id ) ) {
			$query .= $wpdb->prepare( ' AND ( m.home_club = %d OR m.away_club = %d ) ', $args->club_id, $args->club_id );
		}

		/**==================
		 * WHERE filter by competition ID
		 *================ */
		// Get competition to filter
		if ( AnWP_Football_Leagues::string_to_bool( $args->multistage ) && absint( $args->competition_id ) ) {
			$query .= $wpdb->prepare( ' AND ( m.competition_id = %d OR m.main_stage_id = %d ) ', $args->competition_id, $args->competition_id );
		} elseif ( absint( $args->competition_id ) ) {
			$query .= $wpdb->prepare( ' AND m.competition_id = %d ', $args->competition_id );
		}

		/**==================
		 * WHERE official and finished only
		 *================ */
		$query .= ' AND m.game_status = 1 ';
		$query .= ' AND m.finished = 1 ';

		/**==================
		 * Group by Match
		 *================ */
		$query .= ' GROUP BY m.match_id';

		/**==================
		 * Order By
		 *================ */
		if ( absint( $args->limit ) ) {
			$query .= ' ORDER BY m.kickoff DESC';
			$query .= $wpdb->prepare( ' LIMIT %d', $args->limit );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL
		$rows = $wpdb->get_results( $query );

		/*
		|--------------------------------------------------------------------
		| Empty data check
		|--------------------------------------------------------------------
		*/
		if ( empty( $rows ) ) {
			return [];
		}

		/*
		|--------------------------------------------------------------------
		| Prepare Output Data Structure
		|--------------------------------------------------------------------
		*/
		$min_segment = apply_filters( 'anwpfl/charts/min_segment_15', 15 );
		$full_length = apply_filters( 'anwpfl/charts/full_length_90', 90 );

		$output_data = array_fill(
			1,
			absint( $full_length / $min_segment ),
			[
				'plus'  => 0,
				'minus' => 0,
			]
		);

		$output_data_indexes = [];

		for ( $ii = 1; $ii < $full_length; $ii += $min_segment ) {
			$output_data_indexes[] = $ii . '-' . ( $ii + $min_segment - 1 );
		}

		foreach ( $rows as $row ) {
			$game_events = json_decode( $row->events );

			if ( empty( $game_events ) || ! is_array( $game_events ) ) {
				continue;
			}

			foreach ( $game_events as $game_event ) {
				if ( 'goal' !== $game_event->type || $game_event->minute < 1 || $game_event->minute > $full_length ) {
					continue;
				}

				// Scored or Conceded
				$goal_side = absint( $args->club_id ) === absint( $game_event->club ) ? 'plus' : 'minus';

				// Get Segment Index
				$min_segment_index = ceil( $game_event->minute / $min_segment );

				if ( isset( $output_data[ $min_segment_index ][ $goal_side ] ) ) {
					$output_data[ $min_segment_index ][ $goal_side ]++;
				}
			}
		}

		$series_1 = [];
		$series_2 = [];

		foreach ( $output_data as $output_datum ) {
			$series_1[] = $output_datum['plus'];
			$series_2[] = $output_datum['minus'];
		}

		$series_2 = array_map(
			function ( $a ) {
				return $a ? ( $a * - 1 ) : $a;
			},
			$series_2
		);

		$output = [
			'series_1' => $series_1,
			'series_2' => $series_2,
			'indexes'  => $output_data_indexes,
		];

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( class_exists( 'AnWPFL_Cache' ) ) {
			anwp_football_leagues()->cache->set( $cache_key, $output, 'anwp_match' );
		}

		return $output;
	}

	/**
	 * Get default team statistics.
	 *
	 * @param object $args
	 *
	 * @return array
	 * @since 0.11.10
	 */
	public function get_stats_team_defaults( $args ) {

		$args = (object) wp_parse_args(
			$args,
			[
				'competition_id' => '',
				'season_id'      => '',
				'league_id'      => '',
				'club_id'        => '',
				'limit'          => '',
				'multistage'     => '',
				'stat'           => '',
			]
		);

		if ( empty( $args->club_id ) || ! absint( $args->club_id ) ) {
			return [];
		}

		global $wpdb;

		/*
		|--------------------------------------------------------------------
		| Get all filtered games
		|--------------------------------------------------------------------
		*/
		$stat_value = '';

		switch ( $args->stat ) {
			case 'fouls':
				$stat_value = 'home_fouls as stat_home, away_fouls as stat_away';
				break;

			case 'offsides':
				$stat_value = 'home_offsides as stat_home, away_offsides as stat_away';
				break;

			case 'possession':
				$stat_value = 'home_possession as stat_home, away_possession as stat_away';
				break;

			case 'shots':
				$stat_value = 'home_shots as stat_home, away_shots as stat_away';
				break;

			case 'shots_on_goal':
				$stat_value = 'home_shots_on_goal as stat_home, away_shots_on_goal as stat_away';
				break;

			case 'corners':
				$stat_value = 'home_corners as stat_home, away_corners as stat_away';
				break;
		}

		if ( empty( $stat_value ) ) {
			return [];
		}

		$query = "
			SELECT home_club, away_club, kickoff, home_goals, away_goals, {$stat_value}
			FROM {$wpdb->prefix}anwpfl_matches
			WHERE 1=1
		";

		/**==================
		 * WHERE filter by season ID
		 *================ */
		if ( intval( $args->season_id ) ) {
			$query .= $wpdb->prepare( ' AND season_id = %d ', $args->season_id );
		}

		/**==================
		 * WHERE filter by league ID
		 *================ */
		if ( intval( $args->league_id ) ) {
			$query .= $wpdb->prepare( ' AND league_id = %d ', $args->league_id );
		}

		/**==================
		 * WHERE filter by club ID
		 *================ */
		if ( absint( $args->club_id ) ) {
			$query .= $wpdb->prepare( ' AND ( home_club = %d OR away_club = %d ) ', $args->club_id, $args->club_id );
		}

		/**==================
		 * WHERE filter by competition ID
		 *================ */
		// Get competition to filter
		if ( AnWP_Football_Leagues::string_to_bool( $args->multistage ) && absint( $args->competition_id ) ) {
			$query .= $wpdb->prepare( ' AND ( competition_id = %d OR main_stage_id = %d ) ', $args->competition_id, $args->competition_id );
		} elseif ( absint( $args->competition_id ) ) {
			$query .= $wpdb->prepare( ' AND competition_id = %d ', $args->competition_id );
		}

		/**==================
		 * WHERE official and finished only
		 *================ */
		$query .= ' AND game_status = 1 ';
		$query .= ' AND finished = 1 ';

		/**==================
		 * Order By
		 *================ */
		if ( absint( $args->limit ) ) {
			$query .= ' ORDER BY kickoff DESC';
			$query .= $wpdb->prepare( ' LIMIT %d', $args->limit );
		} else {
			$query .= ' ORDER BY kickoff ASC';
		}

		// phpcs:ignore WordPress.DB.PreparedSQL
		$rows = $wpdb->get_results( $query );

		/*
		|--------------------------------------------------------------------
		| Prepare Output Data Structure
		|--------------------------------------------------------------------
		*/
		$output = [
			'series'   => [],
			'indexes'  => [],
			'tooltips' => [],
		];

		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return [];
		}

		if ( absint( $args->limit ) ) {
			$rows = array_reverse( $rows );
		}

		foreach ( $rows as $row ) {
			if ( absint( $args->club_id ) === absint( $row->home_club ) ) {
				$output['series'][]  = $row->stat_home;
				$output['indexes'][] = anwp_football_leagues()->club->get_club_abbr_by_id( $row->away_club );
			} else {
				$output['series'][]  = $row->stat_away;
				$output['indexes'][] = anwp_football_leagues()->club->get_club_abbr_by_id( $row->home_club );
			}

			// Tooltip
			$date_format          = anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M Y';
			$output['tooltips'][] = anwp_football_leagues()->club->get_club_title_by_id( $row->home_club ) . ' - ' . anwp_football_leagues()->club->get_club_title_by_id( $row->away_club ) . '||' . date_i18n( $date_format, get_date_from_gmt( $row->kickoff, 'U' ) ) . ' | ' . $row->home_goals . ':' . $row->away_goals;
		}

		return $output;
	}
}
