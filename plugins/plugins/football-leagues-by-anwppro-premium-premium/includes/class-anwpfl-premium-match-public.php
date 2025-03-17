<?php
/**
 * AnWP Football Leagues Premium :: Match Public
 */
class AnWPFL_Premium_Match_Public {

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
	public function __construct( AnWP_Football_Leagues_Premium $plugin ) {

		// Save main plugin object
		$this->plugin = $plugin;

		// Init hooks
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'wp_enqueue_scripts', [ $this, 'public_enqueue_scripts' ] );

		add_action( 'wp_ajax_anwp_fl_match_front_save', [ $this, 'save_frontend_match_data' ] );
		add_action( 'wp_ajax_anwp_fl_match_front_save_live_scores', [ $this, 'save_frontend_live_scores' ] );
		add_action( 'wp_ajax_anwp_fl_match_front_save_live_status', [ $this, 'save_frontend_live_status' ] );
		add_action( 'wp_ajax_anwp_fl_match_front_get_live_events', [ $this, 'get_frontend_live_events' ] );
		add_action( 'wp_ajax_anwp_fl_match_front_remove_live_event', [ $this, 'remove_frontend_live_event' ] );
		add_action( 'wp_ajax_anwp_fl_match_front_save_live_event', [ $this, 'save_frontend_live_event' ] );
	}

	/**
	 * Checks if front edit option is available.
	 *
	 * @return bool
	 * @since 0.8.1.
	 */
	public function is_front_edit_enabled(): bool {

		return 'yes' === AnWPFL_Premium_Options::get_value( 'match_front_edit' );
	}

	/**
	 * Checks if front LIVE edit option is available.
	 *
	 * @return bool
	 * @since 0.11.12
	 */
	public function is_front_live_edit_enabled(): bool {

		return 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_front_edit' ) && $this->is_front_edit_enabled();
	}

	/**
	 * Checks if front edit option is available.
	 *
	 * @param int $match_id
	 *
	 * @return bool|int
	 * @since 0.8.1
	 */
	public function has_user_cap_front_edit( int $match_id ) {

		// Super admin and admin
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		if ( ! $match_id ) {
			return false;
		}

		$user_id   = get_current_user_id();
		$game_data = anwp_fl()->match->get_game_data( $match_id );

		if ( ! $match_id ) {
			return false;
		}

		// Match Editor
		if ( $user_id ) {

			$editors = get_post_meta( $match_id, '_anwpfl_role_match_editor', false );

			if ( ! empty( $editors ) && is_array( $editors ) ) {
				$editors = array_map( 'absint', $editors );

				if ( in_array( $user_id, $editors, true ) ) {
					return true;
				}
			}
		}

		// Competition Supervisor
		if ( $user_id ) {

			if ( $game_data['competition_id'] ) {
				$editors = get_post_meta( $game_data['competition_id'], '_anwpfl_role_competition_supervisor', false );

				if ( ! empty( $editors ) && is_array( $editors ) ) {
					$editors = array_map( 'absint', $editors );

					if ( in_array( $user_id, $editors, true ) ) {
						return true;
					}
				}
			}
		}

		if ( $user_id ) {

			// Home Captain
			$home_editors = get_post_meta( $game_data['home_club'], '_anwpfl_role_club_captain', false );

			if ( ! empty( $home_editors ) && is_array( $home_editors ) ) {
				$home_editors = array_map( 'absint', $home_editors );

				if ( in_array( $user_id, $home_editors, true ) ) {
					return absint( $game_data['home_club'] );
				}
			}

			// Away Captain
			$away_editors = get_post_meta( $game_data['away_club'], '_anwpfl_role_club_captain', false );

			if ( ! empty( $away_editors ) && is_array( $away_editors ) ) {
				$away_editors = array_map( 'absint', $away_editors );

				if ( in_array( $user_id, $away_editors, true ) ) {
					return absint( $game_data['away_club'] );
				}
			}
		}

		return false;
	}

	/**
	 * Save Frontend Match data
	 *
	 * @since 0.8.1
	 */
	public function save_frontend_match_data() {
		global $wpdb;

		check_ajax_referer( 'anwpfl-public-front-edit' );

		$post_data = wp_unslash( $_POST );
		$game_id   = absint( $post_data['match_id'] ?? '' );

		if ( empty( $game_id ) ) {
			wp_send_json_error( esc_html__( 'Post ID not set.', 'anwp-football-leagues-premium' ) );
		}

		$has_user_cap = $this->has_user_cap_front_edit( $game_id );

		if ( true !== $has_user_cap && absint( $has_user_cap ) && $this->is_front_edit_enabled() ) {
			$this->save_frontend_match_data_by_captain( $game_id, $has_user_cap, $post_data );
			wp_send_json_success();
		}

		if ( true !== $has_user_cap || ! $this->is_front_edit_enabled() ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		// Check post type
		if ( 'anwp_match' !== get_post_type( $game_id ) ) {
			wp_send_json_error( esc_html__( 'Incorrect post type.', 'anwp-football-leagues-premium' ) );
		}

		$game_data_old = anwp_fl()->match->get_game_data( $game_id );

		/*
		|--------------------------------------------------------------------
		| Parse Game Data
		|--------------------------------------------------------------------
		*/
		$data = [];

		$data['extra']      = absint( $post_data['extra'] ?? 0 );
		$data['finished']   = absint( 'result' === ( $post_data['status'] ?? '' ) );
		$data['stadium']    = absint( $post_data['stadium'] ?? 0 );
		$data['coach_home'] = absint( $post_data['coach_home'] ?? '' );
		$data['coach_away'] = absint( $post_data['coach_away'] ?? '' );
		$data['attendance'] = absint( $post_data['attendance'] ?? 0 );
		$data['aggtext']    = sanitize_text_field( $post_data['aggtext'] ?? '' );

		if ( 'round-robin' === get_post_meta( $game_data_old['competition_id'], '_anwpfl_type', true ) ) {
			$data['match_week'] = sanitize_text_field( $post_data['matchWeek'] ?? '' );
		}

		$maybe_kickoff   = sanitize_text_field( $post_data['datetime'] ?? '' );
		$data['kickoff'] = anwp_fl()->helper->validate_date( $maybe_kickoff ) ? $maybe_kickoff : '0000-00-00 00:00:00';

		$game_stats = ( $post_data['stats'] ?? [] ) ? json_decode( $post_data['stats'], true ) : [];

		if ( ! empty( $game_stats ) ) {
			$game_stats = array_map(
				function ( $stat ) {
					return '' === $stat ? null : sanitize_text_field( $stat );
				},
				$game_stats
			);

			$data = array_merge( $data, $game_stats );
		}

		$game_events          = ( $post_data['events'] ?? [] ) ? json_decode( $post_data['events'], true ) : [];
		$data['match_events'] = $game_events ? wp_json_encode( $game_events ) : '';

		/*
		|--------------------------------------------------------------------
		| Update Data
		|--------------------------------------------------------------------
		*/
		anwp_fl()->match->update( $game_id, $data );

		/*
		|--------------------------------------------------------------------
		| Lineups data
		|--------------------------------------------------------------------
		*/
		$lineups_data = [
			'home_line_up'   => sanitize_text_field( $post_data['home_line_up'] ?? '' ),
			'away_line_up'   => sanitize_text_field( $post_data['away_line_up'] ?? '' ),
			'home_subs'      => sanitize_text_field( $post_data['home_subs'] ?? '' ),
			'away_subs'      => sanitize_text_field( $post_data['away_subs'] ?? '' ),
			'custom_numbers' => ( $post_data['custom_numbers'] ?? '' ) ? wp_json_encode( json_decode( $post_data['custom_numbers'] ) ) : '',
		];

		$wpdb->update( $wpdb->anwpfl_lineups, $lineups_data, [ 'match_id' => $game_id ] );

		/*
		|--------------------------------------------------------------------
		| Some extra data needed for recalculating
		|--------------------------------------------------------------------
		*/
		$data = array_merge( $game_data_old, $data, $lineups_data );
		anwp_fl()->match->save_player_statistics( $data, $game_events );

		// Recalculate standing
		anwp_fl()->standing->calculate_standing_prepare( $game_id, $data['competition_id'], $data['group_id'] );

		if ( absint( $game_data_old['home_goals'] ) !== absint( $data['home_goals'] ) || absint( $game_data_old['away_goals'] ) !== absint( $data['away_goals'] ) || $game_data_old['kickoff'] !== $data['kickoff'] ) {
			anwp_fl_pro()->match->update_match_title_slug( $data, $game_id );
		}

		do_action( 'anwp_fl_edit_post', $game_id, get_post( $game_id ) );
		anwp_fl_pro()->live->close_live_match( $data );

		/*
		|--------------------------------------------------------------------
		| Reset Cache
		|--------------------------------------------------------------------
		*/
		if ( AnWPFL_Premium_Options::get_value( 'page_cache_support' ) ) {
			if ( 'full' === AnWPFL_Premium_Options::get_value( 'page_cache_support' ) ) {
				wp_cache_flush();
				anwp_fl_pro()->cache->purge_cache_known_plugins();
			} else {
				anwp_fl_pro()->cache->purge_post_cache_known_plugins( $game_id );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Save Frontend Match data by Captain
	 *
	 * phpcs:ignore WordPress.Security.NonceVerification
	 *
	 * @param int   $game_id
	 * @param int   $cap_club
	 * @param array $post_data
	 *
	 * @since 0.8.5
	 */
	protected function save_frontend_match_data_by_captain( int $game_id, int $cap_club, array $post_data ) {
		global $wpdb;

		// Check post type
		if ( 'anwp_match' !== get_post_type( $game_id ) ) {
			wp_send_json_error( esc_html__( 'Incorrect post type.', 'anwp-football-leagues-premium' ) );
		}

		// Check editable club and user rights
		if ( ! isset( $post_data['editable_club'] ) ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		$game_data_old = anwp_fl()->match->get_game_data( $game_id );
		$editable_club = sanitize_key( $post_data['editable_club'] );

		if ( ! in_array( $editable_club, [ 'home', 'away' ], true ) ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		$editable_club_id = $game_data_old[ $editable_club . '_club' ];

		if ( empty( $editable_club_id ) || absint( $editable_club_id ) !== absint( $cap_club ) ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		/*
		|--------------------------------------------------------------------
		| Ready to save Match Captain data
		|--------------------------------------------------------------------
		*/
		$data = [];

		$data['extra'] = absint( $post_data['extra'] ?? 0 );

		if ( 'home' === $editable_club ) {
			$data['coach_home'] = absint( $post_data['coach_home'] ?? '' );
		} else {
			$data['coach_away'] = absint( $post_data['coach_away'] ?? '' );
		}

		/*
		|--------------------------------------------------------------------
		| Status
		|--------------------------------------------------------------------
		*/
		$finished_status_captain = absint( 'result' === ( $post_data['status'] ?? '' ) );

		// Update captain data
		update_post_meta( $game_id, '_anwpfl_status_cap_' . $editable_club, $finished_status_captain );
		$status_opp_cap = get_post_meta( $game_id, '_anwpfl_status_cap_' . ( 'home' === $editable_club ? 'away' : 'home' ), true );

		if ( absint( $finished_status_captain ) && absint( $status_opp_cap ) ) {
			$data['finished'] = 1;
		}

		/*
		|--------------------------------------------------------------------
		| STATS
		|--------------------------------------------------------------------
		*/
		$game_stats = ( $post_data['stats'] ?? [] ) ? json_decode( $post_data['stats'], true ) : [];

		foreach ( $game_stats as $key => $value ) {
			if ( false !== mb_strpos( $key, $editable_club ) ) {
				$data[ $key ] = '' === $value ? null : sanitize_text_field( $value );
			}
		}

		/*
		|--------------------------------------------------------------------
		| Events
		|--------------------------------------------------------------------
		*/
		$post_events  = ( $post_data['events'] ?? [] ) ? json_decode( $post_data['events'], true ) : [];
		$saved_events = $game_data_old['match_events'] ? json_decode( $game_data_old['match_events'], true ) : [];
		$game_events  = [];

		// Remove editable club from saved events
		foreach ( $saved_events as $event ) {
			if ( empty( $event['club'] ) || absint( $event['club'] ) !== absint( $editable_club_id ) ) {
				$game_events[] = $event;
			}
		}

		// Add from posted data
		foreach ( $post_events as $event ) {
			if ( ! empty( $event['club'] ) && absint( $event['club'] ) === absint( $editable_club_id ) ) {
				$game_events[] = $event;
			}
		}

		$data['match_events'] = $game_events ? wp_json_encode( $game_events ) : '';

		/*
		|--------------------------------------------------------------------
		| Update Data
		|--------------------------------------------------------------------
		*/
		anwp_fl()->match->update( $game_id, $data );

		/*
		|--------------------------------------------------------------------
		| Lineups data
		|--------------------------------------------------------------------
		*/
		$lineups_data = [
			'home_line_up' => $game_data_old['home_line_up'],
			'away_line_up' => $game_data_old['away_line_up'],
			'home_subs'    => $game_data_old['home_subs'],
			'away_subs'    => $game_data_old['away_subs'],
		];

		if ( 'home' === $editable_club ) {
			$lineups_data['home_line_up'] = sanitize_text_field( $post_data['home_line_up'] ?? '' );
			$lineups_data['home_subs']    = sanitize_text_field( $post_data['home_subs'] ?? '' );
		} else {
			$lineups_data['away_line_up'] = sanitize_text_field( $post_data['away_line_up'] ?? '' );
			$lineups_data['away_subs']    = sanitize_text_field( $post_data['away_subs'] ?? '' );
		}

		// CUSTOM NUMBERS
		$custom_numbers = json_decode( $game_data_old['custom_numbers'] ) ?: (object) [];
		$posted_numbers = ( $post_data['custom_numbers'] ?? [] ) ? json_decode( $post_data['custom_numbers'] ) : (object) [];

		if ( 'home' === $editable_club ) {
			$club_players = array_merge( explode( ',', $lineups_data['home_line_up'] ), explode( ',', $lineups_data['home_subs'] ) );
		} else {
			$club_players = array_merge( explode( ',', $lineups_data['away_line_up'] ), explode( ',', $lineups_data['away_subs'] ) );
		}

		foreach ( $club_players as $club_player ) {
			if ( isset( $posted_numbers->{$club_player} ) ) {
				$custom_numbers->{$club_player} = $posted_numbers->{$club_player};
			} elseif ( isset( $custom_numbers->{$club_player} ) ) {
				unset( $custom_numbers->{$club_player} );
			}
		}

		$lineups_data['custom_numbers'] = wp_json_encode( $custom_numbers ) ?: '';
		$wpdb->update( $wpdb->anwpfl_lineups, $lineups_data, [ 'match_id' => $game_id ] );

		/*
		|--------------------------------------------------------------------
		| Some extra data needed for recalculating
		|--------------------------------------------------------------------
		*/
		$data = array_merge( $game_data_old, $data, $lineups_data );
		anwp_fl()->match->save_player_statistics( $data, $game_events );

		// Recalculate standing
		anwp_fl()->standing->calculate_standing_prepare( $game_id, $data['competition_id'], $data['group_id'] );

		do_action( 'anwp_fl_edit_post', $game_id, get_post( $game_id ) );
		anwp_fl_pro()->live->close_live_match( $data );

		/*
		|--------------------------------------------------------------------
		| Reset Cache
		|--------------------------------------------------------------------
		*/
		if ( AnWPFL_Premium_Options::get_value( 'page_cache_support' ) ) {
			if ( 'full' === AnWPFL_Premium_Options::get_value( 'page_cache_support' ) ) {
				wp_cache_flush();
				anwp_fl_pro()->cache->purge_cache_known_plugins();
			} else {
				anwp_fl_pro()->cache->purge_post_cache_known_plugins( $game_id );
			}
		}
	}

	/**
	 * Save Frontend LIVE scores
	 *
	 * @since 0.11.12
	 */
	public function save_frontend_live_scores() {

		check_ajax_referer( 'anwpfl-public-front-edit' );

		$post_id = isset( $_POST['match_id'] ) ? intval( $_POST['match_id'] ) : '';

		if ( empty( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Post ID not set.', 'anwp-football-leagues-premium' ) );
		}

		$has_user_cap = $this->has_user_cap_front_edit( $post_id );

		if ( true !== $has_user_cap || ! $this->is_front_live_edit_enabled() ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		// Check post type
		if ( 'anwp_match' !== get_post_type( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Incorrect post type.', 'anwp-football-leagues-premium' ) );
		}

		$data = [
			'live_timestamp_scores' => time(),
			'live_home_score'       => isset( $_POST['home_score'] ) ? absint( $_POST['home_score'] ) : '',
			'live_away_score'       => isset( $_POST['away_score'] ) ? absint( $_POST['away_score'] ) : '',
		];

		/*
		|--------------------------------------------------------------------
		| Update Meta!
		|--------------------------------------------------------------------
		*/
		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, '_anwpfl_' . $key, $value );
		}

		wp_send_json_success();
	}

	/**
	 * Save Frontend LIVE status
	 *
	 * @since 0.11.12
	 */
	public function save_frontend_live_status() {

		check_ajax_referer( 'anwpfl-public-front-edit' );

		$post_id = isset( $_POST['match_id'] ) ? intval( $_POST['match_id'] ) : '';

		if ( empty( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Post ID not set.', 'anwp-football-leagues-premium' ) );
		}

		$has_user_cap = $this->has_user_cap_front_edit( $post_id );

		if ( true !== $has_user_cap || ! $this->is_front_live_edit_enabled() ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		// Check post type
		if ( 'anwp_match' !== get_post_type( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Incorrect post type.', 'anwp-football-leagues-premium' ) );
		}

		$data = [
			'live_timestamp_status' => time(),
			'live_status'           => isset( $_POST['live_status'] ) ? sanitize_key( $_POST['live_status'] ) : '',
		];

		$old_live_status = get_post_meta( $post_id, '_anwpfl_live_status', true );

		/*
		|--------------------------------------------------------------------
		| Calculate Live time and max time
		|--------------------------------------------------------------------
		*/
		$time_full  = get_post_meta( $post_id, '_anwpfl_duration_full', true );
		$time_extra = get_post_meta( $post_id, '_anwpfl_duration_extra', true );

		$time_full   = $time_full ?: 90;
		$time_extra  = $time_extra ?: 30;
		$time_offset = isset( $_POST['live_offset'] ) ? intval( $_POST['live_offset'] ) : 0;

		switch ( $data['live_status'] ) {
			case '_1_st_half':
				$data['live_max_time']     = intval( $time_full / 2 );
				$data['live_current_time'] = absint( $time_offset ) + 1;
				break;

			case 'half_time':
				$data['live_max_time']     = intval( $time_full / 2 );
				$data['live_current_time'] = intval( $time_full / 2 );
				break;

			case '_2_nd_half':
				$data['live_max_time']     = intval( $time_full );
				$data['live_current_time'] = intval( $time_full / 2 ) + absint( $time_offset ) + 1;
				break;

			case 'full_time':
				$data['live_max_time']     = intval( $time_full );
				$data['live_current_time'] = intval( $time_full );
				break;

			case 'extra_time':
				$data['live_max_time']     = intval( $time_full + $time_extra );
				$data['live_current_time'] = intval( $time_full ) + 1;
				break;

			case 'penalty':
				$data['live_max_time']     = intval( $time_full + $time_extra );
				$data['live_current_time'] = intval( $time_full + $time_extra );
				break;

			default:
				$data['live_max_time']     = '';
				$data['live_current_time'] = '';
				break;
		}

		if ( '' !== $data['live_current_time'] && '' !== $data['live_max_time'] && $data['live_current_time'] > $data['live_max_time'] ) {
			$data['live_current_time'] = $data['live_max_time'];
		}

		if ( '' === get_post_meta( $post_id, '_anwpfl_live_home_score', true ) && '' === get_post_meta( $post_id, '_anwpfl_live_away_score', true ) ) {
			$data['live_timestamp_scores'] = time();
			$data['live_home_score']       = 0;
			$data['live_away_score']       = 0;
		}

		/*
		|--------------------------------------------------------------------
		| Update Meta!
		|--------------------------------------------------------------------
		*/
		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, '_anwpfl_' . $key, $value );
		}

		if ( '' === $old_live_status && $old_live_status !== $data['live_status'] ) {
			anwp_fl_pro()->cache->purge_post_cache_known_plugins( $post_id );
		}

		wp_send_json_success();
	}

	/**
	 * Get Frontend LIVE events
	 *
	 * @since 0.11.12
	 */
	public function get_frontend_live_events() {

		check_ajax_referer( 'anwpfl-public-front-edit' );

		$post_id = isset( $_POST['match_id'] ) ? intval( $_POST['match_id'] ) : '';

		if ( empty( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Post ID not set.', 'anwp-football-leagues-premium' ) );
		}

		$has_user_cap = $this->has_user_cap_front_edit( $post_id );

		if ( true !== $has_user_cap || ! $this->is_front_live_edit_enabled() ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		// Check post type
		if ( 'anwp_match' !== get_post_type( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Incorrect post type.', 'anwp-football-leagues-premium' ) );
		}

		// Prepare events output
		$game_events = json_decode( anwp_fl()->match->get_game_data( $post_id )['match_events'] ) ?: [];
		$output      = [];

		if ( empty( $game_events ) || ! is_array( $game_events ) ) {
			wp_send_json_success( $output );
		}

		foreach ( $game_events as $event_index => $event ) {

			// Prepare sorting fields
			$event->sort_a = 0;
			$event->sort_b = 0;

			// Custom handling for penalty shootout
			if ( 'penalty_shootout' === $event->type ) {
				$event->sort_a = 121;
				$event->sort_b = $event_index;
			}

			// Minutes as sorting fields
			if ( ! empty( $event->minute ) ) {
				$event->sort_a = $event->minute;
				$event->sort_b = $event->minuteAdd ?? 0; //phpcs:ignore WordPress.NamingConventions
			}

			// Special sorting
			if ( empty( $event->sort_a ) && ! empty( $event->sorting ) ) {
				$special_sorting = explode( '+', $event->sorting );
				$event->sort_a   = $special_sorting[0];
				$event->sort_b   = $special_sorting[1] ?? 0;
			}

			if ( $event->sort_a ) {

				// Set some initial values
				$event->comment = $event->comment ?? '';
				$event->sorting = $event->sorting ?? '';

				// Check ID is set
				if ( empty( $event->id ) ) {
					$event->id = md5( wp_json_encode( $event ) );
				}

				$output[] = $event;
			}
		}

		if ( ! empty( $output ) && is_array( $output ) ) {
			$output = wp_list_sort(
				$output,
				[
					'sort_a' => 'DESC',
					'sort_b' => 'DESC',
				]
			);
		}

		wp_send_json_success( $output );
	}

	/**
	 * Remove Frontend LIVE event
	 *
	 * @since 0.11.12
	 */
	public function remove_frontend_live_event() {

		check_ajax_referer( 'anwpfl-public-front-edit' );

		$post_id = isset( $_POST['match_id'] ) ? intval( $_POST['match_id'] ) : '';

		if ( empty( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Post ID not set.', 'anwp-football-leagues-premium' ) );
		}

		$has_user_cap = $this->has_user_cap_front_edit( $post_id );

		if ( true !== $has_user_cap || ! $this->is_front_live_edit_enabled() ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		// Check post type
		if ( 'anwp_match' !== get_post_type( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Incorrect post type.', 'anwp-football-leagues-premium' ) );
		}

		// Get event id to remove
		$event_id_remove = isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : '';

		if ( empty( $event_id_remove ) ) {
			wp_send_json_success();
		}

		// Prepare events output
		$game_events = json_decode( anwp_fl()->match->get_game_data( $post_id )['match_events'] ) ?: [];

		if ( empty( $game_events ) || ! is_array( $game_events ) ) {
			wp_send_json_success();
		}

		$updated_events = [];

		foreach ( $game_events as $event ) {

			// Check ID is set
			if ( empty( $event->id ) ) {
				$event->id = md5( wp_json_encode( $event ) );
			}

			if ( strval( $event->id ) !== strval( $event_id_remove ) ) {
				$updated_events[] = $event;
			}
		}

		anwp_fl()->match->update( $post_id, [ 'match_events' => wp_json_encode( $updated_events ) ] );

		wp_send_json_success();
	}

	/**
	 * Save Frontend LIVE event
	 *
	 * @since 0.11.12
	 */
	public function save_frontend_live_event() {

		check_ajax_referer( 'anwpfl-public-front-edit' );

		$post_id = isset( $_POST['match_id'] ) ? intval( $_POST['match_id'] ) : '';

		if ( empty( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Post ID not set.', 'anwp-football-leagues-premium' ) );
		}

		$has_user_cap = $this->has_user_cap_front_edit( $post_id );

		if ( true !== $has_user_cap || ! $this->is_front_live_edit_enabled() ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		// Check post type
		if ( 'anwp_match' !== get_post_type( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Incorrect post type.', 'anwp-football-leagues-premium' ) );
		}

		// Get live event id
		$event_live_id = isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : '';

		if ( empty( $event_live_id ) ) {
			wp_send_json_success();
		}

		// New Event Post data
		$event_post_data = $_POST['event_to_save'] ?? [];

		if ( empty( $event_post_data ) ) {
			wp_send_json_success();
		}

		// Prepare events output
		$game_events = json_decode( anwp_fl()->match->get_game_data( $post_id )['match_events'] ) ?: [];

		if ( ! is_array( $game_events ) ) {
			$game_events = [];
		}

		$updated_events = [];

		/*
		|--------------------------------------------------------------------
		| Prepare Event Data
		|--------------------------------------------------------------------
		*/
		$is_new_event = isset( $_POST['new'] ) && 'yes' === $_POST['new'];

		$live_event = (object) [];

		$event_fields = [
			'type',
			'club',
			'minute',
			'minuteAdd',
			'ownGoal',
			'fromPenalty',
			'id',
			'comment',
			'id',
			'scored',
			'card',
			'sorting',
			'player',
			'assistant',
			'playerOut',
		];

		foreach ( $event_fields as $event_field ) {
			$live_event->{$event_field} = isset( $event_post_data[ $event_field ] ) ? sanitize_text_field( $event_post_data[ $event_field ] ) : '';
		}

		if ( $is_new_event ) {
			$updated_events   = $game_events;
			$updated_events[] = $live_event;
		} else {
			foreach ( $game_events as $event ) {

				// Check ID is set
				if ( empty( $event->id ) ) {
					$event->id = md5( wp_json_encode( $event ) );
				}

				if ( strval( $event->id ) === strval( $event_live_id ) ) {
					$updated_events[] = $live_event;
				} else {
					$updated_events[] = $event;
				}
			}
		}

		anwp_fl()->match->update( $post_id, [ 'match_events' => wp_json_encode( $updated_events ) ] );
		wp_send_json_success();
	}

	/**
	 * Load scripts for match frontend edit
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @since 0.8.1
	 */
	public function public_enqueue_scripts( string $hook_suffix ) {
		if ( ! is_singular( 'anwp_match' ) || ! anwp_fl_pro()->match_public->is_front_edit_enabled() ) {
			return;
		}

		$post_id      = get_the_ID();
		$has_user_cap = $this->has_user_cap_front_edit( $post_id );

		if ( ! is_singular( 'anwp_match' ) || ! $this->is_front_edit_enabled() || ! $has_user_cap ) {
			return;
		}

		$game_data = anwp_fl()->match->get_game_data( $post_id );

		if ( ! empty( $game_data ) ) {
			$competition_id   = $game_data['competition_id'];
			$season_id        = $game_data['season_id'];
			$competition_type = get_post_meta( $competition_id, '_anwpfl_type', true );

			/*
			|--------------------------------------------------------------------
			| Prepare Clubs Data
			|--------------------------------------------------------------------
			*/
			$club_home_id = absint( $game_data['home_club'] );
			$club_away_id = absint( $game_data['away_club'] );

			$club_home = (object) [
				'id'    => $club_home_id,
				'logo'  => anwp_fl()->club->get_club_logo_by_id( $club_home_id ),
				'title' => anwp_fl()->club->get_club_title_by_id( $club_home_id ),
			];

			$club_away = (object) [
				'id'    => $club_away_id,
				'logo'  => anwp_fl()->club->get_club_logo_by_id( $club_away_id ),
				'title' => anwp_fl()->club->get_club_title_by_id( $club_away_id ),
			];

			/*
			|--------------------------------------------------------------------
			| Prepare players
			|--------------------------------------------------------------------
			*/
			$home_players = anwp_fl()->club->get_club_season_players(
				[
					'club' => $club_home_id,
					'id'   => $season_id,
				]
			);

			$away_players = anwp_fl()->club->get_club_season_players(
				[
					'club' => $club_away_id,
					'id'   => $season_id,
				]
			);

			$all_players     = array_merge( $home_players, $away_players );
			$all_players_map = [];

			foreach ( $all_players as $player ) {
				$all_players_map[ $player->id ] = $player;
			}

			$home_lineup = $game_data['home_line_up'];
			$away_lineup = $game_data['away_line_up'];
			$home_subs   = $game_data['home_subs'];
			$away_subs   = $game_data['away_subs'];

			$game_stats = [];
			foreach ( anwp_fl()->match->game_default_stats as $game_stat_slug ) {
				$game_stats[ $game_stat_slug ] = $game_data[ $game_stat_slug ] ?? '';
			}

			/*
			|--------------------------------------------------------------------
			| Populate Data
			|--------------------------------------------------------------------
			*/
			$data = [
				'optionsStadium'    => anwp_fl()->stadium->get_stadiums(),
				'allPlayersMap'     => (object) $all_players_map,
				'playersHomeAll'    => $this->prepare_players_for_edit_match( $home_players, $home_lineup, $home_subs ),
				'playersAwayAll'    => $this->prepare_players_for_edit_match( $away_players, $away_lineup, $away_subs ),
				'staffHomeAll'      => anwp_fl()->club->get_club_season_staff(
					[
						'club' => $club_home_id,
						'id'   => $season_id,
					]
				),
				'staffAwayAll'      => anwp_fl()->club->get_club_season_staff(
					[
						'club' => $club_away_id,
						'id'   => $season_id,
					]
				),
				'status'            => $game_data['finished'] ? 'result' : 'fixture',
				'datetime'          => $game_data['kickoff'],
				'stadium'           => $game_data['stadium_id'],
				'competitionType'   => $competition_type,
				'matchWeek'         => $game_data['match_week'],
				'clubHome'          => $club_home,
				'clubAway'          => $club_away,
				'stats'             => $game_stats,
				'attendance'        => $game_data['attendance'] ?: '',
				'aggtext'           => $game_data['aggtext'],
				'extraTime'         => absint( $game_data['extra'] ) > 0 && absint( $game_data['extra'] ) < 3 ? 'yes' : '',
				'penalty'           => absint( $game_data['extra'] ) > 1 ? 'yes' : '',
				'playersHomeLineUp' => $home_lineup,
				'playersHomeSubs'   => $home_subs,
				'playersAwayLineUp' => $away_lineup,
				'playersAwaySubs'   => $away_subs,
				'coachHomeId'       => $game_data['coach_home'],
				'coachAwayId'       => $game_data['coach_away'],
				'matchEvents'       => json_decode( $game_data['match_events'] ) ?: [],
				'customNumbers'     => $game_data['custom_numbers'],
				'_nonce'            => wp_create_nonce( 'anwpfl-public-front-edit' ),
				'match_id'          => $post_id,
				'editableClub'      => ( true !== $has_user_cap && absint( $has_user_cap ) ) ? absint( $has_user_cap ) : '',
				'editableStatus'    => '',
				'loader'            => includes_url( 'js/tinymce/skins/lightgray/img/loader.gif' ),
				'l10n_datepicker'   => anwp_fl()->data->get_vue_datepicker_locale(),
			];

			if ( $data['editableClub'] ) {
				if ( absint( $data['editableClub'] ) === absint( $club_home_id ) ) {
					$data['editableStatus'] = get_post_meta( $post_id, '_anwpfl_status_cap_home', true );
				} elseif ( absint( $data['editableClub'] ) === absint( $club_away_id ) ) {
					$data['editableStatus'] = get_post_meta( $post_id, '_anwpfl_status_cap_away', true );
				}

				if ( ! in_array( $data['editableStatus'], [ 'fixture', 'result' ], true ) ) {
					$data['editableStatus'] = '';
				}
			}

			/**
			 * Filters a match data to localize.
			 *
			 * @param array $data    Match data
			 * @param int   $post_id Match Post ID
			 *
			 * @since 0.8.1
			 *
			 */
			$data = apply_filters( 'anwpfl/match/data_to_localize', $data, $post_id );

			wp_localize_script( 'anwp-fl-pro-public-app', '_anwpMatchEdit', $data );
		}
	}

	/**
	 * Prepare Players array for edit match form.
	 *
	 * @param array  $players
	 * @param string $lineup_str
	 * @param string $subs_str
	 *
	 * @return array
	 * @since 0.8.1
	 */
	protected function prepare_players_for_edit_match( $players, $lineup_str, $subs_str ) {

		// Output array
		$options = [];

		// LineUp
		$lineup = $lineup_str ? array_filter( array_map( 'intval', explode( ',', $lineup_str ) ) ) : [];

		// Subs
		$subs = $subs_str ? array_filter( array_map( 'intval', explode( ',', $subs_str ) ) ) : [];

		foreach ( $players as $player ) {

			$group = '';

			if ( in_array( $player->id, $lineup, true ) ) {
				$group = 'lineup';
			} elseif ( in_array( $player->id, $subs, true ) ) {
				$group = 'subs';
			}

			$options[] = (object) [
				'id'       => $player->id,
				'position' => $player->position,
				'number'   => $player->number ?: '',
				'name'     => $player->name,
				'country'  => $player->nationality,
				'group'    => $group,
			];
		}

		/*
		|--------------------------------------------------------------------
		| Sorting Players
		|--------------------------------------------------------------------
		*/
		$sorting = AnWPFL_Options::get_value( 'players_dropdown_sorting', 'number' );

		if ( in_array( $sorting, [ 'number', 'name' ], true ) ) {
			$options = wp_list_sort( $options, $sorting );
		}

		return $options;
	}
}
