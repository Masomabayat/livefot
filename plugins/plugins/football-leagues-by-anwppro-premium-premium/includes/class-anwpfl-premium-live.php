<?php
/**
 * AnWP Football Leagues Premium :: Live
 *
 * @since 0.9.5
 */
class AnWPFL_Premium_Live {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 */
	protected $plugin = null;

	protected $api_players;

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
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		/**
		 * Live status save handlers.
		 *
		 * @since 0.8.1
		 */
		add_action( 'wp_ajax_anwp_fl_match_save_live_status', [ $this, 'save_live_status' ] );
		add_action( 'wp_ajax_anwp_fl_match_save_live_scores', [ $this, 'save_live_scores' ] );
		add_action( 'wp_ajax_anwp_fl_match_save_live_events', [ $this, 'save_live_events' ] );
		add_action( 'wp_ajax_anwp_fl_match_update_live_events_status', [ $this, 'update_live_events_status' ] );

		add_action( 'anwpfl/match/on_save', [ $this, 'close_live_match' ] );

		add_action( 'wp_ajax_anwp_fl_live_scores_dashboard_update', [ $this, 'get_live_dashboard_data' ] );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Register REST routes.
	 */
	public function add_rest_routes() {
		register_rest_route(
			'anwpfl/v1',
			'/live/get-live-api-games/(?P<timestamp>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_live_games_api' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/live/get-live-games/(?P<timestamp>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_live_games' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/live/get-live-api-game/(?P<id>\d+)/(?P<timestamp>[-a-z0-9]+)/(?P<args>[a-zA-Z0-9-_~:]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_live_game_api' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/live/get-live-game/(?P<id>\d+)/(?P<timestamp>[-a-z0-9]+)/(?P<args>[a-zA-Z0-9-_~:]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_live_match_to_update' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/live/init-live-game/(?P<id>\d+)/(?P<timestamp>[-a-z0-9]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_live_match_to_init' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/live/check-live-game-finished/(?P<id>\d+)/(?P<timestamp>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'check_live_game_finished' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/live/get-shortcode-games/(?P<hash>\d+)/(?P<args>[a-zA-Z0-9-_~:,]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_live_shortcode_games' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);
	}

	/**
	 * Get Data to update Live Matches
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 * @since 0.14.15
	 */
	public function get_live_games_api( WP_REST_Request $request ) {

		// Check Live mode enabled
		if ( 'yes' !== AnWPFL_Premium_API::get_config_value( 'live' ) ) {
			return new WP_Error( 'rest_invalid', 'Live is disabled', [ 'status' => 400 ] );
		}

		$output = [
			'live' => [],
		];

		$live_data = get_option( 'anwpfl_api_import_live_data' ) ?: [];

		if ( ! empty( $live_data ) && is_array( $live_data ) ) {
			foreach ( $live_data as $live_game_id => $live_game ) {
				$output['live'][ $live_game_id ] = [
					'status_short' => $live_game['fixture']['status']['short'] ?? '',
					'live_status'  => $this->get_live_status_label_api( $live_game['fixture']['status']['short'] ?? '' ),
					'elapsed'      => $live_game['fixture']['status']['elapsed'] ?? '',
					'home_score'   => $live_game['goals']['home'] ?? '',
					'away_score'   => $live_game['goals']['away'] ?? '',
				];
			}
		}

		return rest_ensure_response( $output );
	}

	/**
	 * Get Data to update Live Matches
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 * @since 0.14.15
	 */
	public function check_live_game_finished( WP_REST_Request $request ) {

		// Check Live mode enabled
		if ( 'yes' !== AnWPFL_Premium_API::get_config_value( 'live' ) ) {
			return new WP_Error( 'rest_invalid', 'Live is disabled', [ 'status' => 400 ] );
		}

		$output = [
			'finished'   => '',
			'home_score' => '',
			'away_score' => '',
		];

		$game_id = absint( $request['id'] );

		if ( empty( $game_id ) ) {
			return new WP_Error( 'rest_invalid', 'Invalid Data', [ 'status' => 400 ] );
		}

		$game_data = anwp_fl()->match->get_game_data( $game_id );

		if ( absint( $game_data['finished'] ?? 0 ) ) {
			$output['home_score'] = $game_data['home_goals'];
			$output['away_score'] = $game_data['away_goals'];
			$output['finished']   = 'yes';
		}

		return rest_ensure_response( $output );
	}

	/**
	 * Get Data to update Live Matches
	 *
	 * @param WP_REST_Request $request
	 *
	 * @since 0.8.1
	 */
	public function get_live_games( WP_REST_Request $request ) {

		global $wpdb;

		// Check Live mode enabled
		if ( 'yes' !== AnWPFL_Premium_Options::get_value( 'match_live_mode', '' ) ) {
			return new WP_Error( 'rest_invalid', 'Live is disabled', [ 'status' => 400 ] );
		}

		$output = [
			'live' => [],
		];

		/*
		|--------------------------------------------------------------------
		| Get All Live Games
		|--------------------------------------------------------------------
		*/
		$live_ids = $wpdb->get_col(
			"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_anwpfl_live_status' AND meta_value != ''
			"
		);

		if ( ! empty( $live_ids ) && is_array( $live_ids ) ) {
			$posts_live = get_posts(
				[
					'include'   => $live_ids,
					'post_type' => 'anwp_match',
				]
			);

			$games_data = anwp_fl()->match->get_game_data_by_ids( $live_ids );

			if ( ! empty( $posts_live ) && ! empty( $games_data ) ) {

				/** @var WP_Post $match */
				foreach ( $posts_live as $match ) {
					$game_data = $games_data[ $match->ID ];

					if ( ! $game_data || absint( $game_data['finished'] ) ) {
						continue;
					}

					$output['live'][ $match->ID ] = [
						'id'               => $match->ID,
						'home_score'       => $match->_anwpfl_live_home_score,
						'away_score'       => $match->_anwpfl_live_away_score,
						'timestamp_scores' => $match->_anwpfl_live_timestamp_scores,
						'timestamp_status' => $match->_anwpfl_live_timestamp_status,
						'max_time'         => '',
						'current_time'     => '',
						'live_status_slug' => $match->_anwpfl_live_status,
						'live_status'      => $this->get_live_status_label( $match->_anwpfl_live_status ),
					];

					// Live timing
					$cur_time    = $match->_anwpfl_live_current_time;
					$max_time    = $match->_anwpfl_live_max_time;
					$time_offset = absint( ( time() - $match->_anwpfl_live_timestamp_status ) / 60 );

					switch ( $match->_anwpfl_live_status ) {

						case '_1_st_half':
						case '_2_nd_half':
						case 'extra_time':
							$output['live'][ $match->ID ]['max_time']     = $max_time;
							$output['live'][ $match->ID ]['current_time'] = min( ( $cur_time + $time_offset ), $max_time );
							break;

						case 'penalty':
						case 'half_time':
						case 'full_time':
							$output['live'][ $match->ID ]['max_time']     = $max_time;
							$output['live'][ $match->ID ]['current_time'] = $max_time;
							break;
					}
				}
			}
		}

		return rest_ensure_response( $output );
	}

	/**
	 * Get Data to update Live Matches
	 *
	 * @since 0.14.10
	 */
	public function is_api_game_active( int $game_id ): bool {
		if ( 'yes' !== AnWPFL_Premium_API::get_config_value( 'live' ) ) {
			return false;
		}

		$saved_live_data = get_option( 'anwpfl_api_import_live_data' );

		if ( empty( $saved_live_data ) ) {
			return false;
		}

		return ! empty( $saved_live_data[ $game_id ] );
	}

	/**
	 * Init API players data
	 *
	 * @param $game_data
	 *
	 * @return void
	 */
	private function init_game_api_players_list( $game_data ) {

		global $wpdb;
		$player_api_ids = [];

		if ( empty( $game_data['events'] ) || ! is_array( $game_data['events'] ) ) {
			return;
		}

		// Get all api ids
		foreach ( $game_data['events'] as $game_event ) {
			if ( isset( $game_event['player']['id'] ) && absint( $game_event['player']['id'] ) ) {
				$player_api_ids[] = absint( $game_event['player']['id'] );
			}

			if ( isset( $game_event['assist']['id'] ) && absint( $game_event['assist']['id'] ) ) {
				$player_api_ids[] = absint( $game_event['assist']['id'] );
			}
		}

		if ( empty( $player_api_ids ) ) {
			return;
		}

		$include_placeholders = array_fill( 0, count( $player_api_ids ), '%d' );
		$include_format       = implode( ', ', $include_placeholders );

		$query = "
			SELECT m.external_value api_id, p.player_id id, p.name, p.photo
			FROM $wpdb->anwpfl_import_mapping m
			LEFT JOIN $wpdb->anwpfl_player_data p ON p.player_id = m.local_value
			WHERE m.`type` = 'player'
		";

		$query .= $wpdb->prepare( " AND m.external_value IN ({$include_format})", $player_api_ids ); // phpcs:ignore

		$this->api_players = $wpdb->get_results( $query, OBJECT_K ) ?: []; // phpcs:ignore
	}

	/**
	 * Helper function to get API Players data.
	 *
	 * @param $event_player
	 * @param string $output (name or photo)
	 *
	 * @return mixed|string
	 */
	private function get_player_data( $event_player, string $output = 'name' ) {

		if ( empty( $event_player['id'] ) ) {
			return '';
		}

		if ( empty( $this->api_players[ $event_player['id'] ] ) ) {
			return 'photo' === $output ? 'https://media.api-sports.io/football/leagues/' . absint( $event_player['id'] ) . '.png' : $event_player['name'];
		}

		return 'photo' === $output ? anwp_fl()->upload_dir . $this->api_players[ $event_player['id'] ]->photo : $this->api_players[ $event_player['id'] ]->name;
	}

	/**
	 * Get live status title by slug
	 *
	 * @param string $status_short
	 *
	 * @return string
	 * @since 0.14.10
	 */
	public function get_live_status_label_api( string $status_short = '' ): string {

		$label = '';

		switch ( $status_short ) {
			case '1H':
				$label = AnWPFL_Text::get_value( 'match__live__1_st_half', __( '1st Half', 'anwp-football-leagues-premium' ) );
				break;

			case '2H':
				$label = AnWPFL_Text::get_value( 'match__live__2_nd_half', __( '2nd Half', 'anwp-football-leagues-premium' ) );
				break;

			case 'ET':
				$label = AnWPFL_Text::get_value( 'match__live__extra_time', __( 'Extra Time', 'anwp-football-leagues-premium' ) );
				break;

			case 'P':
				$label = AnWPFL_Text::get_value( 'match__live__penalty', __( 'Penalty', 'anwp-football-leagues-premium' ) );
				break;

			case 'HT':
			case 'BT':
				$label = AnWPFL_Text::get_value( 'match__live__half_time', __( 'Half Time', 'anwp-football-leagues-premium' ) );
				break;

			case 'FT':
			case 'AET':
			case 'PEN':
				$label = AnWPFL_Text::get_value( 'match__live__full_time', __( 'Full Time', 'anwp-football-leagues-premium' ) );
				break;
		}

		return $label;
	}

	/**
	 * Get Data to update Live Matches
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 * @since 0.14.10
	 */
	public function get_live_game_api( WP_REST_Request $request ) {

		// Check Live mode enabled
		if ( 'yes' !== AnWPFL_Premium_API::get_config_value( 'live' ) ) {
			return new WP_Error( 'rest_invalid', 'Live is disabled', [ 'status' => 400 ] );
		}

		$params  = $request->get_params();
		$game_id = $params['id'];
		$args    = AnWPFL_Premium_Helper::parse_rest_url_params( $params['args'] );

		$game_data = anwp_fl()->match->get_game_data( $game_id );

		if ( empty( $game_data ) || absint( $game_data['match_id'] ) !== absint( $game_id ) ) {
			return new WP_Error( 'rest_invalid', 'Invalid Data', [ 'status' => 400 ] );
		}

		$has_commentary = AnWP_Football_Leagues::string_to_bool( $args['comm'] );
		$has_timeline   = AnWP_Football_Leagues::string_to_bool( $args['tmline'] );

		/*
		|--------------------------------------------------------------------
		| Check Match is finished
		|--------------------------------------------------------------------
		*/
		if ( absint( $game_data['finished'] ) ) {
			$output['status'] = 'finished';

			return rest_ensure_response( [ 'game_data' => $output ] );
		}

		/*
		|--------------------------------------------------------------------
		| Get All Live Games
		|--------------------------------------------------------------------
		*/
		$live_data = get_option( 'anwpfl_api_import_live_data' ) ?: [];

		if ( empty( $live_data[ $game_id ] ) ) {
			return rest_ensure_response( 'No LIVE Data' );
		}

		$live_game = $live_data[ $game_id ];

		$output = [
			'status_short' => $live_game['fixture']['status']['short'] ?? '',
			'live_status'  => $this->get_live_status_label_api( $live_game['fixture']['status']['short'] ?? '' ),
			'elapsed'      => $live_game['fixture']['status']['elapsed'] ?? '',
			'home_score'   => $live_game['goals']['home'] ?? '',
			'away_score'   => $live_game['goals']['away'] ?? '',
		];

		/*
		|--------------------------------------------------------------------
		| Check event update required
		|--------------------------------------------------------------------
		*/
		$html_output = [];

		$game_events = $live_game['events'] ?? '';
		$game_events = apply_filters( 'anwpfl/match-live/game_events', $game_events, $game_id );

		$user_events_hash = empty( $rest_args['evt'] ) ? '' : wp_unslash( $rest_args['evt'] );
		$live_events_hash = md5( maybe_serialize( $game_events ) );

		if ( ! empty( $game_events ) && $live_events_hash !== $user_events_hash && ( $has_commentary || $has_timeline ) ) {

			$game_data['club_home_logo'] = anwp_fl()->club->get_club_logo_by_id( $game_data['home_club'] ) ?: anwp_fl()->helper->get_default_club_logo();
			$game_data['club_away_logo'] = anwp_fl()->club->get_club_logo_by_id( $game_data['away_club'] ) ?: anwp_fl()->helper->get_default_club_logo();
			$game_data['home_club_api']  = absint( $live_game['teams']['home']['id'] );
			$game_data['away_club_api']  = absint( $live_game['teams']['away']['id'] );
			$game_data['events']         = $game_events;

			$this->init_game_api_players_list( $live_game );

			if ( $has_commentary ) {
				$html_output['events'] = $this->get_live_commentary_output_api( $live_game, $game_data );
			}

			if ( $has_timeline ) {
				ob_start();
				anwp_fl()->load_partial( $game_data, 'match/match-timeline', 'live-api' );
				$html_output['timeline'] = ob_get_clean();
			}

			if ( ! empty( $html_output['events'] ) || ! empty( $html_output['timeline'] ) ) {
				$output['update_events'] = 'yes';
				$output['events_hash']   = md5( maybe_serialize( $game_events ) );
			}
		}

		return rest_ensure_response(
			[
				'game_data' => $output,
				'html'      => $html_output,
			]
		);
	}

	public function render_timeline_event_api( $event, $game_data ) {

		$event_name = '';

		if ( 'goal' === mb_strtolower( $event['type'] ) && 'Missed Penalty' === $event['detail'] ) {
			$event_name = $this->get_event_name_by_api( 'missed_penalty' );
		} elseif ( 'goal' === mb_strtolower( $event['type'] ) ) {
			if ( 'penalty' === trim( mb_strtolower( $event['detail'] ) ) ) {
				$event_name = $this->get_event_name_by_api( 'penalty_goal' );
			} elseif ( 'own goal' === trim( mb_strtolower( $event['detail'] ) ) ) {
				$event_name = $this->get_event_name_by_api( 'own_goal' );
			} else {
				$event_name = $this->get_event_name_by_api( 'goal' );
			}
		} elseif ( 'subst' === mb_strtolower( $event['type'] ) ) {
			$event_name = $this->get_event_name_by_api( 'substitute' );
		} elseif ( 'card' === mb_strtolower( $event['type'] ) ) {
			$event_name = $this->get_event_name_by_api( 'Red Card' === $event['detail'] ? 'r' : 'y' );
		}

		$event_class  = ( (int) $event['team']['id'] === (int) $game_data->home_club_api ) ? 'match-timeline__item-home' : 'match-timeline__item-away';
		$tooltip_text = intval( $event['time']['elapsed'] ) . ( intval( $event['time']['extra'] ) ? ( '\'+' . intval( $event['time']['extra'] ) ) : '' ) . '\' ' . $event_name . ': ' . $this->get_player_data( $event['player'] );

		if ( 'subst' === $event['type'] ) {
			$tooltip_text .= ' > ' . $this->get_player_data( $event['assist'] );
		}

		if ( 'goal' === mb_strtolower( $event['type'] ) && ! empty( $event['assist'] ) && 'Missed Penalty' !== $event['detail'] ) {
			$tooltip_text .= ' (' . esc_html( AnWPFL_Text::get_value( 'match__timeline__assistant', __( 'assistant', 'anwp-football-leagues-premium' ) ) );
			$tooltip_text .= ': ' . $this->get_player_data( $event['assist'] ) . ')';
		}

		ob_start();
		?>
		<div class="match-timeline__item position-absolute <?php echo esc_attr( $event_class ); ?>">
			<div class="match__timeline-icon" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $tooltip_text ); ?>">
				<?php if ( 'Goal' === $event['type'] && 'Missed Penalty' === $event['detail'] ) : ?>
					<svg class="match-timeline__icon  icon__ball">
						<use xlink:href="#icon-ball_canceled"></use>
					</svg>
				<?php elseif ( 'Goal' === $event['type'] ) : ?>
					<svg class="match-timeline__icon icon__ball <?php echo esc_attr( 'own goal' === trim( mb_strtolower( $event['detail'] ) ) ? 'icon__ball--own' : '' ); ?>">
						<use xlink:href="#<?php echo esc_attr( 'penalty' === trim( mb_strtolower( $event['detail'] ) ) ? 'icon-ball_penalty' : 'icon-ball' ); ?>"></use>
					</svg>
				<?php elseif ( 'subst' === $event['type'] ) : ?>
					<svg class="match-timeline__icon icon__substitute">
						<use xlink:href="#icon-substitute"></use>
					</svg>
				<?php elseif ( 'Card' === $event['type'] ) : ?>
					<svg class="match-timeline__icon icon__card">
						<use xlink:href="#icon-card_<?php echo esc_attr( 'Red Card' === $event['detail'] ? 'r' : 'y' ); ?>"></use>
					</svg>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	private function get_event_name_by_api( $event_name ) {
		$event_name_map = [
			'substitute'       => esc_html( AnWPFL_Text::get_value( 'match__event__substitute', _x( 'Substitute', 'match event', 'anwp-football-leagues' ) ) ),
			'goal'             => esc_html( AnWPFL_Text::get_value( 'match__event__goal', _x( 'Goal', 'match event', 'anwp-football-leagues' ) ) ),
			'own_goal'         => esc_html( AnWPFL_Text::get_value( 'match__event__goal_own', _x( 'Goal (own)', 'match event', 'anwp-football-leagues' ) ) ),
			'penalty_goal'     => esc_html( AnWPFL_Text::get_value( 'match__event__goal_from_penalty', _x( 'Goal (from penalty)', 'match event', 'anwp-football-leagues' ) ) ),
			'penalty_shootout' => esc_html( AnWPFL_Text::get_value( 'match__event__penalty_shootout', __( 'Penalty Shootout', 'anwp-football-leagues' ) ) ),
			'missed_penalty'   => esc_html( AnWPFL_Text::get_value( 'match__event__missed_penalty', _x( 'Missed Penalty', 'match event', 'anwp-football-leagues' ) ) ),
			'y'                => AnWPFL_Text::get_value( 'data__cards__yellow_card', esc_html__( 'Yellow Card', 'anwp-football-leagues' ) ),
			'r'                => AnWPFL_Text::get_value( 'data__cards__red_card', esc_html__( 'Red Card', 'anwp-football-leagues' ) ),
			'yr'               => AnWPFL_Text::get_value( 'data__cards__red_yellow_card', esc_html__( '2nd Yellow > Red Card', 'anwp-football-leagues' ) ),
		];

		return $event_name_map[ $event_name ] ?? '';
	}

	private function get_live_commentary_output_api( $live_data, $match_data ): string {

		$events = array_reverse( $live_data['events'] );

		$event_match_data = [
			'color_home'    => get_post_meta( $match_data['home_club'], '_anwpfl_main_color', true ) ?: '#0085ba',
			'color_away'    => get_post_meta( $match_data['away_club'], '_anwpfl_main_color', true ) ?: '#dc3545',
			'home_club_api' => absint( $live_data['teams']['home']['id'] ),
			'away_club_api' => absint( $live_data['teams']['away']['id'] ),
		];

		$slim_layout = 'slim' === AnWPFL_Premium_Options::get_value( 'match_commentary_layout' );
		$show_photo  = 'no' !== AnWPFL_Premium_Options::get_value( 'match_commentary_show_player_photo' );

		$html_output = '';

		foreach ( $events as $event ) {
			$html_output .= $this->get_commentary_event_tmpl_api( $event, $event_match_data, $slim_layout, $show_photo );
		}

		return $html_output;
	}

	/**
	 * Event output for commentary block
	 *
	 * @param $event      object Event object
	 * @param $event_match_data
	 * @param $slim_layout
	 * @param $show_photo
	 *
	 * @return string
	 * @since 0.14.10
	 */
	public function get_commentary_event_tmpl_api( $event, $event_match_data, $slim_layout, $show_photo ): string {

		if ( empty( $event['type'] ) ) {
			return '';
		}

		/*
		|--------------------------------------------------------------------
		| Prepare event data
		|--------------------------------------------------------------------
		*/
		$event_data = (object) [
			'minute_text' => empty( $event['time']['elapsed'] ) ? '' : ( $event['time']['elapsed'] . "'" ),
			'icon'        => '',
			'subheader'   => '',
			'event_name'  => '',
		];

		if ( ! empty( $event['time']['extra'] ) && intval( $event['time']['extra'] ) && $event_data->minute_text ) {
			$event_data->minute_text .= ' +' . absint( $event['time']['extra'] ) . "'";
		}

		switch ( mb_strtolower( $event['type'] ) ) {

			case 'goal':
				if ( 'Penalty Shootout' === $event['comments'] ) {
					$event_data->event_name = $this->get_event_name_by_api( 'penalty_shootout' );
					$event_data->subheader  = $this->get_player_data( $event['player'] );

					$event_data->icon = 'Missed Penalty' === $event['detail'] ? '<svg class="icon__ball"><use xlink:href="#icon-ball_canceled"></use></svg>' : '<svg class="icon__ball"><use xlink:href="#icon-ball_penalty"></use></svg>';

				} elseif ( 'Missed Penalty' === $event['detail'] ) {
					$event_data->icon       = '<svg class="icon__ball"><use xlink:href="#icon-ball_canceled"></use></svg>';
					$event_data->event_name = $this->get_event_name_by_api( 'missed_penalty' );

					$event_data->subheader = $this->get_player_data( $event['player'] );
				} else {
					$event_data->icon .= '<svg class="icon__ball ' . esc_attr( 'own goal' === trim( mb_strtolower( $event['detail'] ) ) ? 'icon__ball--own' : '' ) . '">';
					$event_data->icon .= '<use xlink:href="#' . esc_attr( 'penalty' === trim( mb_strtolower( $event['detail'] ) ) ? 'icon-ball_penalty' : 'icon-ball' ) . '"></use>';
					$event_data->icon .= '</svg>';

					$event_data->subheader .= $this->get_player_data( $event['player'] );

					if ( ! empty( $event['assist']['id'] ) && absint( $event['assist']['id'] ) ) {
						$event_data->subheader .= '<div class="anwp-text-nowrap ml-3"><span class="anwp-text-lowercase match-commentary__meta">' . esc_html( AnWPFL_Text::get_value( 'match__commentary__assistant', __( 'Assistant', 'anwp-football-leagues' ) ) ) . ': </span>';
						$event_data->subheader .= $this->get_player_data( $event['assist'] ) . '</div>';
					}

					if ( 'penalty' === trim( mb_strtolower( $event['detail'] ) ) ) {
						$event_data->event_name = $this->get_event_name_by_api( 'penalty_goal' );
					} elseif ( 'own goal' === trim( mb_strtolower( $event['detail'] ) ) ) {
						$event_data->event_name = $this->get_event_name_by_api( 'own_goal' );
					} else {
						$event_data->event_name = $this->get_event_name_by_api( 'goal' );
					}
				}

				break;

			case 'subst':
				$event_data->event_name = $this->get_event_name_by_api( 'substitute' );
				$event_data->icon       = '<svg class="icon__substitute"><use xlink:href="#icon-substitute"></use></svg>';

				$event_data->subheader .= '<div class="anwp-text-nowrap"><span class="anwp-text-lowercase mr-1 match-commentary__meta">' . esc_html( AnWPFL_Text::get_value( 'match__commentary__in', __( 'In', 'anwp-football-leagues-premium' ) ) ) . ':</span>';
				$event_data->subheader .= $this->get_player_data( $event['player'] ) . '</div>';
				$event_data->subheader .= '<div class="anwp-text-nowrap ml-3"><span class="anwp-text-lowercase mr-1 match-commentary__meta">' . esc_html( AnWPFL_Text::get_value( 'match__commentary__out', __( 'Out', 'anwp-football-leagues-premium' ) ) ) . ':</span>';
				$event_data->subheader .= $this->get_player_data( $event['assist'] ) . '</div>';
				break;

			case 'card':
				$card_type = 'Red Card' === $event['detail'] ? 'r' : 'y';

				$event_data->event_name = $this->get_event_name_by_api( $card_type );
				$event_data->icon       = '<svg class="icon__card"><use xlink:href="#icon-card_' . esc_attr( $card_type ) . '"></use></svg>';
				$event_data->subheader  = $this->get_player_data( $event['player'] );
				break;

			case 'var':
				$event_data->event_name = 'VAR';
				$event_data->icon       = '<svg class="anwp-icon anwp-icon--octi"><use xlink:href="#icon-var"></use></svg>';
				$event['comments']      = $event['detail'] . ( isset( $event['comments'] ) ? ( ' ' . $event['comments'] ) : '' );
				break;
		}

		$event_data->text = $event['comments'] ?? '';

		ob_start();
		?>
		<div class="anwp-row anwp-no-gutters match-commentary__row <?php echo $slim_layout ? 'my-2' : 'my-3'; ?> match-commentary__event--<?php echo esc_html( $event['type'] ); ?>">
			<div class="anwp-col-md">
				<?php if ( absint( $event['team']['id'] ) === $event_match_data['home_club_api'] ) : ?>
					<div class="match-commentary__block match-commentary__block--home d-flex <?php echo $slim_layout ? 'py-2 px-3' : 'p-3'; ?>" style="<?php echo esc_attr( is_rtl() ? 'border-right-color' : 'border-left-color' ); ?>: <?php echo esc_attr( $event_match_data['color_home'] ); ?>">
						<?php if ( $show_photo && $this->get_player_data( $event['player'], 'photo' ) ) : ?>
							<div class="position-relative anwp-text-center mr-1 player__photo-wrapper--list">
								<img class="anwp-object-contain <?php echo $slim_layout ? 'anwp-w-40 anwp-h-40' : 'anwp-w-50 anwp-h-50'; ?>"
									src="<?php echo esc_url( $this->get_player_data( $event['player'], 'photo' ) ); ?>" alt="player photo">
							</div>
						<?php endif; ?>
						<div class="flex-grow-1 <?php echo $slim_layout ? 'anwp-text-sm' : 'anwp-text-base'; ?>">
							<div class="match-commentary__block-header d-flex align-items-center flex-wrap justify-content-md-end">
								<div class="match-commentary__event-icon--inner d-md-none mr-2"><?php echo $event_data->icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								<span class="match-commentary__event-name"><?php echo esc_html( $event_data->event_name ); ?></span>

								<?php if ( $event_data->minute_text ) : ?>
									<span class="match-commentary__minute ml-2"><?php echo esc_html( $event_data->minute_text ); ?></span>
								<?php endif; ?>
							</div>
							<div class="match-commentary__block-sub-header d-flex flex-wrap align-items-end justify-content-md-end">
								<?php echo $event_data->subheader; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
							<?php if ( $event_data->text ) : ?>
								<div class="match-commentary__block-text text-md-right anwp-text-xs anwp-opacity-70"><?php echo esc_html( $event_data->text ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<div class="anwp-col-auto d-none d-md-block">
				<div class="match-commentary__event-icon"><?php echo $event_data->icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</div>
			<div class="anwp-col-md">
				<?php if ( absint( $event['team']['id'] ) === $event_match_data['away_club_api'] ) : ?>
					<div class="match-commentary__block match-commentary__block--away d-flex <?php echo $slim_layout ? 'py-2 px-2' : 'p-3'; ?>" style="<?php echo esc_attr( is_rtl() ? 'border-left-color' : 'border-right-color' ); ?>: <?php echo esc_attr( $event_match_data['color_away'] ); ?>">
						<div class="flex-grow-1 <?php echo $slim_layout ? 'anwp-text-sm' : 'anwp-text-base'; ?>">
							<div class="match-commentary__block-header d-flex align-items-center flex-wrap justify-content-end justify-content-md-start">
								<?php if ( $event_data->minute_text ) : ?>
									<span class="match-commentary__minute mr-2"><?php echo esc_html( $event_data->minute_text ); ?></span>
								<?php endif; ?>

								<span class="match-commentary__event-name"><?php echo esc_html( $event_data->event_name ); ?></span>
								<div class="match-commentary__event-icon--inner d-md-none ml-2"><?php echo $event_data->icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
							</div>
							<div class="match-commentary__block-sub-header d-flex flex-wrap align-items-end justify-content-end justify-content-md-start">
								<?php echo $event_data->subheader; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
							<?php if ( $event_data->text ) : ?>
								<div class="match-commentary__block-text anwp-text-xs anwp-opacity-70"><?php echo esc_html( $event_data->text ); ?></div>
							<?php endif; ?>
						</div>
						<?php if ( $show_photo && $this->get_player_data( $event['player'], 'photo' ) ) : ?>
							<div class="position-relative anwp-text-center ml-1 player__photo-wrapper--list">
								<img class="anwp-object-contain <?php echo $slim_layout ? 'anwp-w-40 anwp-h-40' : 'anwp-w-50 anwp-h-50'; ?>"
									src="<?php echo esc_url( $this->get_player_data( $event['player'], 'photo' ) ); ?>" alt="player photo">
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
		$output_html = ob_get_clean();

		return apply_filters( 'anwpfl/tmpl-match/commentary_event_api', $output_html, $event, $event_data, $event_match_data );
	}

	/**
	 * Get Data to initialize Live Matches
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since 0.8.1
	 */
	public function get_live_match_to_init( WP_REST_Request $request ) {

		// Check Live mode enabled
		if ( 'yes' !== AnWPFL_Premium_Options::get_value( 'match_live_mode', '' ) ) {
			return new WP_Error( 'rest_invalid', 'Live is disabled', [ 'status' => 400 ] );
		}

		$game_id = absint( $request->get_param( 'id' ) );

		if ( empty( $game_id ) ) {
			return new WP_Error( 'rest_invalid', esc_html__( 'Incorrect Data', 'anwp-football-leagues-premium' ), [ 'status' => 400 ] );
		}

		$match_post = get_post( $game_id );

		// Check post type
		if ( ! $match_post || 'anwp_match' !== $match_post->post_type ) {
			return new WP_Error( 'rest_invalid', esc_html__( 'Incorrect post type.', 'anwp-football-leagues-premium' ), [ 'status' => 400 ] );
		}

		// Prepare output data
		$output = [
			'id'                => $match_post->ID,
			'live_status'       => $match_post->_anwpfl_live_status,
			'live_status_title' => $this->get_live_status_label( $match_post->_anwpfl_live_status ),
			'home_score'        => $match_post->_anwpfl_live_home_score,
			'away_score'        => $match_post->_anwpfl_live_away_score,
			'timestamp_scores'  => $match_post->_anwpfl_live_timestamp_scores,
			'timestamp_status'  => $match_post->_anwpfl_live_timestamp_status,
		];

		$live_status = $match_post->_anwpfl_live_status;

		if ( empty( $live_status ) && 'yes' !== $match_post->_anwpfl_match_live_commentary ) {
			return rest_ensure_response( 'live-empty' );
		}

		// Live timing
		$cur_time = $match_post->_anwpfl_live_current_time;
		$max_time = $match_post->_anwpfl_live_max_time;

		// Time offset
		$time_offset = absint( ( time() - $match_post->_anwpfl_live_timestamp_status ) / 60 );

		switch ( $live_status ) {

			case '_1_st_half':
			case '_2_nd_half':
			case 'extra_time':
				$output['max_time']     = $max_time;
				$output['current_time'] = min( ( $cur_time + $time_offset ), $max_time );
				break;

			case 'penalty':
			case 'half_time':
			case 'full_time':
				$output['max_time']     = $max_time;
				$output['current_time'] = $max_time;
				break;

			default:
				$output['max_time']     = '';
				$output['current_time'] = '';
				break;
		}

		$game_data = anwp_fl()->match->get_game_data( $match_post->ID );

		$events_raw            = $game_data['match_events'] ?: '';
		$output['events_hash'] = md5( $events_raw );
		$output['events_html'] = '';

		$events = json_decode( $events_raw );
		/*
		|--------------------------------------------------------------------
		| Events Output
		|--------------------------------------------------------------------
		*/
		if ( null !== $events ) {
			$events = anwp_fl_pro()->match->parse_match_comments_events( $events );
		} else {
			$events = [];
		}

		if ( ! empty( $events ) ) {
			$events = array_reverse( $events );
			$data   = anwp_fl()->match->prepare_match_data_to_render( $game_data, [], 'match', 'full' );

			$color_home = get_post_meta( $game_data['home_club'], '_anwpfl_main_color', true );
			$color_away = get_post_meta( $game_data['away_club'], '_anwpfl_main_color', true );

			if ( empty( $color_home ) ) {
				$color_home = '#0085ba';
			}

			if ( empty( $color_away ) ) {
				$color_away = '#dc3545';
			}

			$event_match_data = (object) [
				'club_home_title' => $data['club_home_title'],
				'club_away_title' => $data['club_away_title'],
				'club_home_logo'  => $data['club_home_logo'],
				'club_away_logo'  => $data['club_away_logo'],
				'match_id'        => $data['match_id'],
				'club_home_abbr'  => $data['club_home_abbr'],
				'club_away_abbr'  => $data['club_away_abbr'],
				'color_home'      => $color_home,
				'color_away'      => $color_away,
				'home_club'       => intval( $data['home_club'] ),
				'away_club'       => intval( $data['away_club'] ),
				'players'         => anwp_fl()->player->get_game_players( $data ),
			];

			ob_start();
			foreach ( $events as $event ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo anwp_fl_pro()->match->get_commentary_event_tmpl( $event, $event_match_data );
			}
			$output['events_html'] = ob_get_clean();
		}

		return rest_ensure_response( $output );
	}

	/**
	 * Get Data to update Live Matches
	 *
	 * @param WP_REST_Request $request
	 *
	 * @since 0.8.1
	 */
	public function get_live_match_to_update( WP_REST_Request $request ) {

		// Check Live mode enabled
		if ( 'yes' !== AnWPFL_Premium_Options::get_value( 'match_live_mode', '' ) ) {
			return new WP_Error( 'rest_invalid', 'Live is disabled', [ 'status' => 400 ] );
		}

		$params  = $request->get_params();
		$game_id = $params['id'];
		$args    = AnWPFL_Premium_Helper::parse_rest_url_params( $params['args'] );

		// Prepare output data
		$output = [
			'status' => '',
		];

		/*
		|--------------------------------------------------------------------
		| Get Post Data
		|--------------------------------------------------------------------
		*/
		$match_data = [
			'id'               => absint( $game_id ),
			'timestamp_scores' => sanitize_text_field( $args['t_sc'] ),
			'timestamp_status' => sanitize_text_field( $args['t_st'] ),
			'events_hash'      => sanitize_text_field( $args['evt'] ),
		];

		/*
		|--------------------------------------------------------------------
		| Check Post is correct
		|--------------------------------------------------------------------
		*/
		$match_post = get_post( $match_data['id'] );
		$game_data  = anwp_fl()->match->get_game_data( $match_data['id'] );

		// Check post type
		if ( ! $match_post || 'anwp_match' !== $match_post->post_type ) {
			return new WP_Error( 'rest_invalid', esc_html__( 'Incorrect post type.', 'anwp-football-leagues-premium' ), [ 'status' => 400 ] );
		}

		/*
		|--------------------------------------------------------------------
		| #1 - Check Match is finished
		|--------------------------------------------------------------------
		*/
		if ( absint( $game_data['finished'] ) ) {
			$output['status'] = 'finished';
			return rest_ensure_response( $output );
		}

		/*
		|--------------------------------------------------------------------
		| #2 - Check Match Update require
		|--------------------------------------------------------------------
		*/
		$update_scores = true;
		$update_time   = true;
		$update_events = true;

		// Get timestamps and hash
		$timestamp_scores = $match_post->_anwpfl_live_timestamp_scores;
		$timestamp_status = $match_post->_anwpfl_live_timestamp_status;

		$events_raw            = $game_data['match_events'] ?: '';
		$output['events_hash'] = md5( $events_raw );
		$output['events_html'] = '';

		// Check if update required
		if ( $timestamp_scores === $match_data['timestamp_scores'] ) {
			$update_scores = false;
		}

		if ( $timestamp_status === $match_data['timestamp_status'] ) {
			$update_time = false;
		}

		if ( $output['events_hash'] === $match_data['events_hash'] ) {
			$update_events = false;
		}

		if ( ! $update_scores && ! $update_time && ! $update_events ) {
			return rest_ensure_response( $output );
		}

		/*
		|--------------------------------------------------------------------
		| Prepare data to update
		|--------------------------------------------------------------------
		*/
		$output['status']            = 'updated';
		$output['update_scores']     = $update_scores ? 'yes' : '';
		$output['update_time']       = $update_time ? 'yes' : '';
		$output['update_events']     = $update_events ? 'yes' : '';
		$output['home_score']        = $match_post->_anwpfl_live_home_score;
		$output['away_score']        = $match_post->_anwpfl_live_away_score;
		$output['timestamp_scores']  = $match_post->_anwpfl_live_timestamp_scores;
		$output['timestamp_status']  = $match_post->_anwpfl_live_timestamp_status;
		$output['live_status']       = $match_post->_anwpfl_live_status;
		$output['live_status_title'] = $this->get_live_status_label( $match_post->_anwpfl_live_status );
		$output['max_time']          = '';
		$output['current_time']      = '';

		if ( $update_time ) {

			// Live timing
			$cur_time = $match_post->_anwpfl_live_current_time;
			$max_time = $match_post->_anwpfl_live_max_time;

			// Time offset
			$time_offset = absint( ( time() - $output['timestamp_status'] ) / 60 );

			switch ( $output['live_status'] ) {
				case '_1_st_half':
				case '_2_nd_half':
				case 'extra_time':
					$output['max_time']     = $max_time;
					$output['current_time'] = min( ( $cur_time + $time_offset ), $max_time );
					break;

				case 'penalty':
				case 'half_time':
				case 'full_time':
					$output['max_time']     = $max_time;
					$output['current_time'] = $max_time;
					break;
			}
		}

		if ( $update_events ) {
			$events = json_decode( $events_raw );

			/*
			|--------------------------------------------------------------------
			| Events Output
			|--------------------------------------------------------------------
			*/
			if ( null !== $events ) {
				$events = anwp_fl_pro()->match->parse_match_comments_events( $events );
			} else {
				$events = [];
			}

			if ( ! empty( $events ) ) {
				$events = array_reverse( $events );

				// Get match data to render
				$game_data = anwp_fl()->match->prepare_match_data_to_render( $game_data, [], 'match', 'full' );

				$color_home = get_post_meta( $game_data['home_club'], '_anwpfl_main_color', true );
				$color_away = get_post_meta( $game_data['away_club'], '_anwpfl_main_color', true );

				if ( empty( $color_home ) ) {
					$color_home = '#0085ba';
				}

				if ( empty( $color_away ) ) {
					$color_away = '#dc3545';
				}

				$event_match_data = (object) [
					'club_home_title' => $game_data['club_home_title'],
					'club_away_title' => $game_data['club_away_title'],
					'club_home_logo'  => $game_data['club_home_logo'],
					'club_away_logo'  => $game_data['club_away_logo'],
					'match_id'        => $game_data['match_id'],
					'club_home_abbr'  => $game_data['club_home_abbr'],
					'club_away_abbr'  => $game_data['club_away_abbr'],
					'color_home'      => $color_home,
					'color_away'      => $color_away,
					'home_club'       => intval( $game_data['home_club'] ),
					'away_club'       => intval( $game_data['away_club'] ),
					'players'         => anwp_fl()->player->get_game_players( $game_data ),
				];

				// Commentary Output
				ob_start();
				foreach ( $events as $event ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo anwp_fl_pro()->match->get_commentary_event_tmpl( $event, $event_match_data );
				}

				$output['events_html'] = ob_get_clean();

				// Timeline Output
				$section_match_data           = anwp_fl_pro()->match->get_match_data( $match_post->ID );
				$section_match_data['header'] = false;
				ob_start();
				anwp_fl()->load_partial( $section_match_data, 'match/match-timeline' );

				$output['timeline_html'] = ob_get_clean();
			}
		}

		return rest_ensure_response( $output );
	}

	/**
	 * Save Live Match status
	 *
	 * @since 0.8.1
	 */
	public function save_live_status() {

		// Check nonce
		check_ajax_referer( 'anwpfl-live-save' );

		// Check user caps
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		$post_id = intval( $_POST['match_id'] );

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
				$data['live_current_time'] = intval( $time_full ) + absint( $time_offset ) + 1;
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

		/*
		|--------------------------------------------------------------------
		| Update Meta!
		|--------------------------------------------------------------------
		*/
		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, '_anwpfl_' . $key, $value );
		}

		if ( '' === $old_live_status && $old_live_status !== $data['live_status'] ) {
			anwp_football_leagues_premium()->cache->purge_post_cache_known_plugins( $post_id );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Save Live Match Scores
	 *
	 * @since 0.8.1
	 */
	public function save_live_scores() {

		// Check nonce
		check_ajax_referer( 'anwpfl-live-save' );

		// Check user caps
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		$post_id = intval( $_POST['match_id'] );

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

		wp_send_json_success( $data );
	}

	/**
	 * Save Live Match Scores
	 *
	 * @since 0.8.1
	 */
	public function save_live_events() {

		// Check nonce
		check_ajax_referer( 'anwpfl-live-save' );

		// Check user caps
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		$post_id = intval( $_POST['match_id'] );

		// Check post type
		if ( 'anwp_match' !== get_post_type( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Incorrect post type.', 'anwp-football-leagues-premium' ) );
		}

		$game_events = ( $_POST['live_events'] ?? [] ) ? json_decode( wp_unslash( $_POST['live_events'] ), true ) : [];
		anwp_fl()->match->update( $post_id, [ 'match_events' => $game_events ? wp_json_encode( $game_events ) : '' ] );

		wp_send_json_success();
	}

	/**
	 * Update Live Events status
	 *
	 * @since 0.9.5
	 */
	public function update_live_events_status() {

		// Check nonce
		check_ajax_referer( 'anwpfl-live-save' );

		// Check user caps
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		$live_status = sanitize_key( $_POST['live_status'] );
		$post_id     = intval( $_POST['match_id'] );

		update_post_meta( $post_id, '_anwpfl_match_live_commentary', $live_status );

		wp_send_json_success();
	}

	/**
	 * Close live status for Match.
	 *
	 * @param $data
	 *
	 * @since 0.8.1
	 * @return mixed
	 */
	public function close_live_match( $data ) {

		$match_id = $data['match_id'];

		if ( empty( $data['match_id'] ) || 'yes' !== AnWPFL_Premium_Options::get_value( 'match_live_mode', '' ) ) {
			return;
		}

		$live_status = get_post_meta( $match_id, '_anwpfl_live_status', true );

		if ( absint( $data['finished'] ) && ! empty( $live_status ) ) {
			delete_post_meta( $match_id, '_anwpfl_live_away_score' );
			delete_post_meta( $match_id, '_anwpfl_live_current_time' );
			delete_post_meta( $match_id, '_anwpfl_live_home_score' );
			delete_post_meta( $match_id, '_anwpfl_live_max_time' );
			delete_post_meta( $match_id, '_anwpfl_live_status' );
			delete_post_meta( $match_id, '_anwpfl_live_timestamp_scores' );
			delete_post_meta( $match_id, '_anwpfl_live_timestamp_status' );
		}
	}

	/**
	 * Get Data for Live Dashboard
	 *
	 * @since 0.9.5
	 */
	public function get_live_dashboard_data() {

		// Check nonce
		check_ajax_referer( 'anwpfl-live-save' );

		// Check user caps
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You have no rights to edit this Match.', 'anwp-football-leagues-premium' ) );
		}

		global $wpdb;

		$custom_time_format  = anwp_fl()->get_option_value( 'custom_match_time_format' );
		$default_time_format = get_option( 'time_format' );

		// Check Live mode enabled
		if ( 'yes' !== AnWPFL_Premium_Options::get_value( 'match_live_mode', '' ) ) {
			wp_send_json_error();
		}

		$output_matches = [];

		/*
		|--------------------------------------------------------------------
		| Today Matches
		|--------------------------------------------------------------------
		*/
		$current_date = AnWPFL_Premium_Helper::get_current_datetime();

		$args = [
			'type'         => 'fixture',
			'sort_by_date' => 'asc',
			'date_from'    => $current_date->sub( new DateInterval( 'PT3H' ) )->format( 'Y-m-d' ),
			'date_to'      => $current_date->add( new DateInterval( 'P1D' ) )->format( 'Y-m-d' ),
		];

		$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $args );

		if ( ! empty( $matches ) && is_array( $matches ) ) {

			/*
			|--------------------------------------------------------------------
			| Get Available matches
			|--------------------------------------------------------------------
			*/
			foreach ( $matches as $match_data ) {
				$scheduled = false;

				$output_matches[ absint( $match_data->match_id ) ] = [
					'id'               => $match_data->match_id,
					'date'             => date_i18n( 'j M', strtotime( $match_data->kickoff ) ),
					'time'             => date( $custom_time_format ?: $default_time_format, strtotime( $match_data->kickoff ) ),
					'club_home_title'  => anwp_football_leagues()->club->get_club_abbr_by_id( $match_data->home_club ),
					'club_away_title'  => anwp_football_leagues()->club->get_club_abbr_by_id( $match_data->away_club ),
					'club_home_goals'  => '-',
					'club_away_goals'  => '-',
					'club_home_logo'   => anwp_football_leagues()->club->get_club_logo_by_id( $match_data->home_club ),
					'club_away_logo'   => anwp_football_leagues()->club->get_club_logo_by_id( $match_data->away_club ),
					'group'            => 'today',
					'edit_link'        => get_edit_post_link( $match_data->match_id, '' ),
					'live_status_slug' => '',
					'scheduled'        => $scheduled,
				];
			}
		}

		/*
		|--------------------------------------------------------------------
		| Live
		|--------------------------------------------------------------------
		*/
		$live_ids = $wpdb->get_col(
			"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_anwpfl_live_status' AND meta_value != ''
			"
		);

		if ( ! empty( $live_ids ) && is_array( $live_ids ) ) {
			$live_posts = get_posts(
				[
					'include'   => $live_ids,
					'post_type' => 'anwp_match',
				]
			);

			$games_data = anwp_fl()->match->get_game_data_by_ids( $live_ids );

			if ( ! empty( $live_posts ) && ! empty( $games_data ) ) {

				/** @var WP_Post $match */
				foreach ( $live_posts as $match ) {
					$game_data = $games_data[ $match->ID ];

					if ( ! $game_data || absint( $game_data['finished'] ) ) {
						continue;
					}

					$output_matches[ $match->ID ] = [
						'id'               => $match->ID,
						'club_home_goals'  => absint( $match->_anwpfl_live_home_score ),
						'club_away_goals'  => absint( $match->_anwpfl_live_away_score ),
						'timestamp_status' => $match->_anwpfl_live_timestamp_status,
						'max_time'         => '',
						'current_time'     => '',
						'edit_link'        => get_edit_post_link( $match->ID, '' ),
						'date'             => date_i18n( 'j M', strtotime( $game_data['kickoff'] ) ),
						'time'             => date( $custom_time_format ?: $default_time_format, strtotime( $game_data['kickoff'] ) ),
						'club_home_title'  => anwp_fl()->club->get_club_abbr_by_id( $game_data['home_club'] ),
						'club_away_title'  => anwp_fl()->club->get_club_abbr_by_id( $game_data['away_club'] ),
						'club_home_logo'   => anwp_fl()->club->get_club_logo_by_id( $game_data['home_club'] ),
						'club_away_logo'   => anwp_fl()->club->get_club_logo_by_id( $game_data['away_club'] ),
						'group'            => 'live',
						'live_status_slug' => $match->_anwpfl_live_status,
						'scheduled'        => false,
					];

					$live_status = $match->_anwpfl_live_status;

					// Live timing
					$cur_time = $match->_anwpfl_live_current_time;
					$max_time = $match->_anwpfl_live_max_time;

					$time_offset                                 = absint( ( time() - $match->_anwpfl_live_timestamp_status ) / 60 );
					$output_matches[ $match->ID ]['live_status'] = $this->get_live_status_label( $live_status );

					switch ( $live_status ) {

						case '_2_nd_half':
						case 'extra_time':
						case '_1_st_half':
							$output_matches[ $match->ID ]['max_time']     = $max_time;
							$output_matches[ $match->ID ]['current_time'] = min( ( $cur_time + $time_offset ), $max_time );
							break;

						case 'half_time':
						case 'full_time':
						case 'penalty':
							$output_matches[ $match->ID ]['max_time']     = $max_time;
							$output_matches[ $match->ID ]['current_time'] = $max_time;
							break;

					}
				}
			}
		}

		wp_send_json_success( array_values( $output_matches ) );
	}

	/**
	 * Get live status title by slug
	 *
	 * @param string $slug
	 *
	 * @return string
	 * @since 0.9.6
	 */
	public function get_live_status_label( string $slug = '' ): string {

		$label = '';

		switch ( $slug ) {
			case '_1_st_half':
				$label = AnWPFL_Text::get_value( 'match__live__1_st_half', __( '1st Half', 'anwp-football-leagues-premium' ) );
				break;

			case '_2_nd_half':
				$label = AnWPFL_Text::get_value( 'match__live__2_nd_half', __( '2nd Half', 'anwp-football-leagues-premium' ) );
				break;

			case 'extra_time':
				$label = AnWPFL_Text::get_value( 'match__live__extra_time', __( 'Extra Time', 'anwp-football-leagues-premium' ) );
				break;

			case 'penalty':
				$label = AnWPFL_Text::get_value( 'match__live__penalty', __( 'Penalty', 'anwp-football-leagues-premium' ) );
				break;

			case 'half_time':
				$label = AnWPFL_Text::get_value( 'match__live__half_time', __( 'Half Time', 'anwp-football-leagues-premium' ) );
				break;

			case 'full_time':
				$label = AnWPFL_Text::get_value( 'match__live__full_time', __( 'Full Time', 'anwp-football-leagues-premium' ) );
				break;
		}

		return $label;
	}

	/**
	 * Get Goal sound url.
	 *
	 * @return string
	 * @since 0.9.6
	 */
	public function get_live_sound_goal() {
		$sound_url = '';

		if ( 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_mode' ) ) {
			if ( 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_mode_sound' ) ) {
				$sound_url = AnWPFL_Premium_Options::get_value( 'match_live_mode_sound_file', '' );
			}
		}

		return $sound_url;
	}

	/**
	 * Get LIVE rest cache period prefix in seconds (60/30/15).
	 *
	 * @return int
	 * @since 0.14.15
	 */
	public function get_live_cache_period_prefix() {

		return apply_filters( 'anwpfl/live/cache-period', 15 );
	}

	/**
	 * Get LIVE Shortcode games
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since 0.15.0
	 */
	public function get_live_shortcode_games( WP_REST_Request $request ) {

		global $wpdb;

		$params   = $request->get_params();
		$args_raw = AnWPFL_Premium_Helper::parse_rest_url_params( $params['args'] );
		$output   = [];

		/*
		|--------------------------------------------------------------------
		| Get LIVE API Games
		|--------------------------------------------------------------------
		*/
		$live_ids  = [];
		$live_data = get_option( 'anwpfl_api_import_live_data' ) ?: [];

		if ( ! empty( $live_data ) && is_array( $live_data ) ) {
			foreach ( $live_data as $live_game_id => $live_game ) {
				$live_ids[] = $live_game_id;
			}
		}

		/*
		|--------------------------------------------------------------------
		| Get LIVE Manual Games
		|--------------------------------------------------------------------
		*/
		$live_local_ids = $wpdb->get_col(
			"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_anwpfl_live_status' AND meta_value != ''
			"
		);

		if ( ! empty( $live_local_ids ) && is_array( $live_local_ids ) ) {
			$live_ids = array_merge( $live_ids, $live_local_ids );
		}

		/*
		|--------------------------------------------------------------------
		| Prepare Games
		|--------------------------------------------------------------------
		*/
		if ( empty( $live_ids ) ) {
			return rest_ensure_response( [ 'items' => [] ] );
		}

		$args = (object) [
			'sort_by_date'          => 'asc',
			'type'                  => 'fixture',
			'include_ids'           => implode( ',', $live_ids ),
			'group_by'              => isset( $args_raw['group_by'] ) ? sanitize_key( $args_raw['group_by'] ) : '',
			'competition_id'        => isset( $args_raw['competition_id'] ) ? sanitize_key( $args_raw['competition_id'] ) : '',
			'filter_by_clubs'       => isset( $args_raw['filter_by_clubs'] ) ? sanitize_text_field( $args_raw['filter_by_clubs'] ) : '',
			'class'                 => isset( $args_raw['class'] ) ? sanitize_text_field( $args_raw['class'] ) : '',
			'show_secondary'        => 1,
			'group_by_header_style' => isset( $args_raw['group_by_header_style'] ) ? sanitize_text_field( $args_raw['group_by_header_style'] ) : '',
			'show_club_logos'       => isset( $args_raw['show_club_logos'] ) ? sanitize_key( $args_raw['show_club_logos'] ) : '',
			'competition_logo'      => isset( $args_raw['competition_logo'] ) ? sanitize_key( $args_raw['competition_logo'] ) : '',
			'layout'                => isset( $args_raw['layout'] ) ? sanitize_key( $args_raw['layout'] ) : '',
		];

		$games = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $args );

		$group_current = '';

		foreach ( $games as $ii => $single_game ) :
			if ( '' !== $args->group_by ) {

				$group_text = '';

				if ( 'competition' === $args->group_by && $group_current !== $single_game->competition_id ) {
					$group_text    = anwp_football_leagues()->competition->get_competition_title( $single_game->competition_id );
					$group_current = $single_game->competition_id;
				}

				if ( $group_text ) {
					ob_start();
					if ( 'secondary' === $args->group_by_header_style ) {
						anwp_football_leagues()->load_partial(
							[
								'text'  => esc_html( $group_text ),
								'class' => $ii ? ' mt-4 mb-1' : 'mb-1',
							],
							'general/subheader'
						);
					} else {
						anwp_football_leagues()->load_partial(
							[
								'text'  => esc_html( $group_text ),
								'class' => $ii ? ' mt-4' : '',
							],
							'general/header'
						);
					}

					$output[] = [
						'type' => 'group',
						'id'   => $group_current,
						'html' => ob_get_clean(),
					];
				}
			}

			// Get match data to render
			$game_data = anwp_football_leagues()->match->prepare_match_data_to_render( $single_game, $args );

			$game_data['competition_logo'] = $args->competition_logo;

			ob_start();
			anwp_football_leagues()->load_partial( $game_data, 'match/match', $args->layout ?: 'slim' );

			$output[] = [
				'type' => 'game',
				'id'   => $single_game->match_id,
				'html' => ob_get_clean(),
			];

		endforeach;

		return rest_ensure_response( [ 'items' => $output ] );
	}
}
