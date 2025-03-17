<?php
/**
 * AnWP Football Leagues Premium :: Match
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 *
 * @since 0.1.0
 */
class AnWPFL_Premium_Match {

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
	 * @param AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 *
	 * @since  0.1.0
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

		add_filter(
			'anwpfl/match/vue_app_id',
			function () {
				return 'anwpfl-app-match-premium';
			}
		);

		// Integrate Match Formation
		add_action( 'anwpfl/match/on_save', [ $this, 'save_match_premium_data' ], 10, 2 );

		add_filter( 'anwpfl/match/data_to_localize', [ $this, 'modify_localized_data' ], 10, 2 );

		// Premium Metabox
		add_action( 'anwpfl/match/metabox_nav_items', [ $this, 'add_premium_metabox_nav_items' ] );
		add_filter( 'anwpfl/cmb2_tabs_content/match', [ $this, 'add_premium_metabox_options' ] );

		// Manage columns
		add_filter( 'manage_edit-anwp_match_columns', [ $this, 'columns' ], 50 );
		add_action( 'manage_anwp_match_posts_custom_column', [ $this, 'match_columns_display' ], 50, 2 );

		add_filter( 'anwpfl/tmpl-match/render_header', '__return_false' );
		add_action( 'anwpfl/tmpl-match/after_header', [ $this, 'render_scoreboard' ], 10, 2 );

		add_action( 'restrict_manage_posts', [ $this, 'add_more_filters' ], 15 );
		add_filter( 'pre_get_posts', [ $this, 'handle_custom_filter' ], 15 );

		/**
		 * Inject extra actions info match slim.
		 *
		 * @since 0.10.3
		 */
		add_filter( 'anwpfl/tmpl-match-slim/extra_action', [ $this, 'add_match_list_custom_btn' ], 10, 2 );
		add_action( 'anwpfl/tmpl-match-super-slim/bottom', [ $this, 'add_prediction_match_super_slim_list' ], 10, 2 );
		add_action( 'anwpfl/tmpl-match-slim/bottom', [ $this, 'add_prediction_match_list' ], 10, 2 );

		add_action( 'anwpfl/shortcodes/match_shortcode_options', [ $this, 'add_match_shortcode_premium_sections' ] );

		add_action( 'anwpfl/match/match-header-bottom', [ $this, 'render_match_header_prediction' ], 10, 2 );

		add_filter( 'anwpfl/shortcodes/matches_available_layouts', [ $this, 'matches_available_layouts' ], 10, 2 );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );

		add_filter( 'cmb2_override_meta_value', [ $this, 'get_cmb2_game_pro_data' ], 10, 4 );
	}

	/**
	 * Get game fields not saved in postmeta
	 *
	 * @since 0.16.0
	 */
	public function get_cmb2_game_pro_data( $initial_value, $post_id, $args, CMB2_Field $cmb_field ) {
		if ( ! empty( $cmb_field->cmb_id ) || 'anwp_match_metabox' !== $cmb_field->cmb_id ) {
			return $initial_value;
		}

		$field_name = str_replace( '_anwpfl_', '', $args['field_id'] );

		$available_fields = [
			'priority',
			'prediction_advice_alt',
		];

		if ( ! in_array( $field_name, $available_fields, true ) ) {
			return $initial_value;
		}

		if ( 'prediction_advice_alt' === $field_name ) {
			return $this->get_prediction_data( $post_id )['prediction_advice_alt'] ?? '';
		}

		static $game_data = null;

		if ( null === $game_data ) {
			$game_data = anwp_fl()->match->get_game_data( $post_id );

			if ( empty( $game_data ) ) {
				return $initial_value;
			}
		}

		return $game_data[ $field_name ] ?? $initial_value;
	}

	/**
	 * Register REST routes.
	 */
	public function add_rest_routes() {
		register_rest_route(
			'anwpfl/v1',
			'/data/get-calendar-slider-games/(?P<hash>\d+)/(?P<args>[a-zA-Z0-9-_~:%,]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_calendar_slider_games' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/odds/get_game_all_odds/(?P<game_id>\d+)/(?P<odd_id>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_match_odds' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/data/get-calendar-dates/(?P<hash>\d+)/(?P<args>[a-zA-Z0-9-_~:,]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_calendar_dates' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/data/get-calendar-games/(?P<hash>\d+)/(?P<args>[a-zA-Z0-9-_~:,]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_calendar_widget_matches' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/data/get-team-form-game/(?P<game_id>\d+)/(?P<hash>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_form_club_match' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);

		register_rest_route(
			'anwpfl/v1',
			'/standing/get_team_games/(?P<standing_id>\d+)/(?P<team_id>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_standing_club_matches' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);
	}

	/**
	 * Method renders custom match list button.
	 *
	 * @param string $extra_actions_html
	 * @param array  $data
	 *
	 * @return string
	 * @since 0.8.3
	 */
	public function add_match_list_custom_btn( string $extra_actions_html, array $data ): string {

		static $icon = null;

		if ( null === $icon ) {
			$icon = AnWPFL_Premium_Options::get_value( 'match_list_custom_button', '' );
		}

		if ( empty( $icon ) ) {
			return $extra_actions_html;
		}

		$btn_type = $this->get_match_custom_button_data( $data['match_id'] );

		switch ( $btn_type ) {
			case 'image':
				$extra_actions_html .= '<a class="anwp-match-slim-btn" style="background-image: url(' . esc_attr( $icon ) . ')"></a>';
				break;

			case 'link':
				$extra_actions_html .= '<a href="' . esc_url( get_post_meta( $data['match_id'], '_anwpfl_match_list_custom_button_url', true ) ) . '" class="anwp-match-slim-btn anwp-match-slim-btn--link d-sm-inline-block" target="_blank" style="background-image: url(' . esc_attr( $icon ) . ')"></a>';
				break;
		}

		return $extra_actions_html;
	}

	/**
	 * Get Match Custom Button data
	 *
	 * @param $match_id
	 *
	 * @return string
	 * @since 0.14.7
	 */
	private function get_match_custom_button_data( $match_id ) {

		global $wpdb;
		static $all_data = null;

		if ( null === $all_data ) {
			$all_data = [];

			$available_data = $wpdb->get_results(
				"
					SELECT meta_value, post_id
					FROM $wpdb->postmeta
					WHERE meta_key = '_anwpfl_match_list_custom_button' AND meta_value > ''
				"
			);

			foreach ( $available_data as $available_button_data ) {
				$all_data[ $available_button_data->post_id ] = $available_button_data->meta_value;
			}
		}

		return empty( $all_data[ $match_id ] ) ? '' : $all_data[ $match_id ];
	}

	/**
	 * Fires before the Filter button on the Posts and Pages list tables.
	 *
	 * The Filter button allows sorting by date and/or category on the
	 * Posts list table, and sorting by date on the Pages list table.
	 *
	 * @param string $post_type The post type slug.
	 */
	public function add_more_filters( $post_type ) {

		if ( 'anwp_match' === $post_type ) {

			ob_start();
			/*
			|--------------------------------------------------------------------
			| Live Status Filter
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$current_live_filter = empty( $_GET['_anwpfl_live_status_filter'] ) ? '' : sanitize_key( $_GET['_anwpfl_live_status_filter'] );
			?>
			<select name='_anwpfl_live_status_filter' id='anwp_live_status_filter' class='postform'>
				<option value=''><?php echo esc_html__( 'Live Status', 'anwp-football-leagues-premium' ); ?></option>
				<option value="active" <?php selected( 'active', $current_live_filter ); ?>>
					- <?php echo esc_html__( 'Live active', 'anwp-football-leagues-premium' ); ?>
				</option>
				<option value="upcoming" <?php selected( 'upcoming', $current_live_filter ); ?>>
					- <?php echo esc_html__( 'Live upcoming', 'anwp-football-leagues-premium' ); ?>
				</option>
			</select>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}
	}

	/**
	 * Handle custom filter.
	 *
	 * @param WP_Query $query
	 */
	public function handle_custom_filter( $query ) {
		global $post_type, $pagenow;

		// Check main query in admin
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$sub_query = [];

		// phpcs:ignore WordPress.Security.NonceVerification
		$live_status_filter = empty( $_GET['_anwpfl_live_status_filter'] ) ? '' : sanitize_key( $_GET['_anwpfl_live_status_filter'] );

		// Filter by Live Status
		if ( 'edit.php' === $pagenow && 'anwp_match' === $post_type && $live_status_filter ) {

			if ( 'active' === $live_status_filter ) {
				$sub_query[] =
					[
						'key'     => '_anwpfl_live_status',
						'value'   => [ '_1_st_half', 'half_time', '_2_nd_half', 'full_time', 'extra_time', 'penalty', 'finished' ],
						'compare' => 'IN',
					];
			}

			if ( 'upcoming' === $live_status_filter ) {
				$sub_query[] =
					[
						[
							'key'   => '_anwpfl_match_live_commentary',
							'value' => 'yes',
						],
					];
			}
		}

		if ( ! empty( $sub_query ) ) {
			$sub_query_original = $query->get( 'meta_query' );

			if ( ! empty( $sub_query_original ) && is_array( $sub_query_original ) ) {
				$query->set(
					'meta_query',
					[
						array_merge( [ 'relation' => 'AND' ], $sub_query_original, $sub_query ),
					]
				);
			} else {
				$query->set(
					'meta_query',
					[
						array_merge( [ 'relation' => 'AND' ], $sub_query ),
					]
				);
			}
		}
	}

	/**
	 * Render scoreboard.
	 *
	 * @param WP_Post $match_post
	 * @param array   $data
	 *
	 * @since 0.6.0
	 */
	public function render_scoreboard( $match_post, $data ) {

		// Get background image
		$bg_image = $this->get_scoreboard_image( $match_post, $data );

		if ( empty( $bg_image ) ) {
			anwp_football_leagues()->load_partial( $data, 'match/match' );
		} else {
			$data['scoreboard_image'] = $bg_image;
			$data['context']          = 'match';

			anwp_football_leagues()->load_partial( $data, 'match/match-scoreboard' );
		}
	}

	/**
	 * Get Scoreboard image (if display is set to "yes").
	 *
	 * @param $match_post
	 * @param $data
	 *
	 * @return string
	 */
	public function get_scoreboard_image( $match_post, $data ) {

		// Check Match option
		$scoreboard_display = get_post_meta( $match_post->ID, '_anwpfl_match_scoreboard', true );

		// Check global option
		if ( '' === $scoreboard_display ) {
			$scoreboard_display = AnWPFL_Premium_Options::get_value( 'match_scoreboard' );
		}

		if ( 'no' === $scoreboard_display ) {
			return '';
		}

		// Get background image - Match
		$bg_image = get_post_meta( $match_post->ID, '_anwpfl_match_scoreboard_image', true );

		if ( $bg_image ) {
			return $bg_image;
		}

		// Get background image - Stadium
		if ( (int) $data['stadium_id'] ) {
			$bg_image = get_post_meta( $data['stadium_id'], '_anwpfl_match_scoreboard_image', true );

			if ( $bg_image ) {
				return $bg_image;
			}
		}

		// Get background image - Club Home
		$bg_image = get_post_meta( $data['home_club'], '_anwpfl_match_scoreboard_image', true );

		if ( $bg_image ) {
			return $bg_image;
		}

		// Get background image - Plugin Settings
		$bg_image = AnWPFL_Premium_Options::get_value( 'match_scoreboard_image' );

		if ( $bg_image ) {
			return $bg_image;
		}

		// Get default stadium image
		$bg_image = AnWP_Football_Leagues_Premium::url( 'public/img/stadium.jpg' );

		return $bg_image;
	}

	/**
	 * Renders premium tab control.
	 *
	 * @since 0.5.11
	 */
	public function add_premium_metabox_nav_items( $items ) {

		$insert_index = array_search( 'anwp-fl-referee-metabox', array_column( $items, 'slug' ), true );

		$splice_items = [];

		if ( 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_mode', '' ) ) {
			$splice_items[] = [
				'classes' => 'anwp-text-orange-700 anwp-fl-metabox-nav-pro',
				'icon'    => 'fl-pro-radio-tower',
				'label'   => __( 'Live', 'anwp-football-leagues-premium' ),
				'slug'    => 'anwp-fl-game-live-metabox',
			];
		}

		// Add Players Statistics
		$splice_items[] = [
			'classes' => 'anwp-text-orange-700 anwp-fl-metabox-nav-pro',
			'icon'    => 'graph',
			'label'   => __( 'Players Statistics', 'anwp-football-leagues-premium' ),
			'slug'    => 'anwp-fl-game-custom-stats-metabox',
		];

		// Add Players Statistics
		$splice_items[] = [
			'classes' => 'anwp-text-orange-700 anwp-fl-metabox-nav-pro',
			'icon'    => 'jersey',
			'label'   => __( 'Formations', 'anwp-football-leagues-premium' ),
			'slug'    => 'anwp-fl-game-formations-metabox',
		];

		// Add splice items
		array_splice( $items, $insert_index + 1, 0, $splice_items );

		// Custom Code section
		$items[] = [
			'classes' => 'anwp-text-orange-700 anwp-fl-metabox-nav-pro',
			'icon'    => 'fl-pro-code',
			'label'   => __( 'Custom Code', 'anwp-football-leagues-premium' ),
			'slug'    => 'anwp-fl-pro-custom-code-match-metabox',
		];

		// Premium section
		$items[] = [
			'classes' => 'anwp-text-orange-700 anwp-fl-metabox-nav-pro',
			'icon'    => 'star',
			'label'   => __( 'Premium Options', 'anwp-football-leagues-premium' ),
			'slug'    => 'anwp-fl-pro-premium-match-metabox',
		];

		return $items;
	}

	/**
	 * Adds fields to the match metabox.
	 *
	 * @return array
	 * @since 0.5.11
	 */
	public function add_premium_metabox_options() {
		$prefix = '_anwpfl_';

		// Init fields array
		return [
			[
				'name'            => esc_html__( 'Custom Code', 'anwp-football-leagues-premium' ),
				'description'     => esc_html__( 'add any HTML and JS code here', 'anwp-football-leagues-premium' ),
				'id'              => $prefix . 'match_custom_code',
				'type'            => 'textarea',
				'default'         => '',
				'sanitization_cb' => false,
				'after_row'       => '</div></div>',
				'before_row'      => anwp_football_leagues_premium()->create_metabox_header(
					[
						'icon'  => 'fl-pro-code',
						'label' => __( 'Custom Code', 'anwp-football-leagues-premium' ),
						'slug'  => 'anwp-fl-pro-custom-code-match-metabox',
					]
				),
			],
			[
				'name'       => esc_html__( 'Match Priority', 'anwp-football-leagues-premium' ),
				'id'         => $prefix . 'priority',
				'type'       => 'select',
				'save_field' => false,
				'default'    => '0',
				'options'    => [
					'0' => esc_html__( '- not set -', 'anwp-football-leagues-premium' ),
					'1' => 1,
					'2' => 2,
					'3' => 3,
					'4' => 4,
					'5' => esc_html__( '5 (highest)', 'anwp-football-leagues-premium' ),
				],
				'before_row' => anwp_football_leagues_premium()->create_metabox_header(
					[
						'icon'  => 'star',
						'label' => __( 'Premium Options', 'anwp-football-leagues-premium' ),
						'slug'  => 'anwp-fl-pro-premium-match-metabox',
					]
				),
			],
			[
				'name'    => esc_html__( 'Display Match Scoreboard with Image Background', 'anwp-football-leagues-premium' ),
				'id'      => $prefix . 'match_scoreboard',
				'type'    => 'select',
				'default' => '',
				'options' => [
					''    => esc_html__( 'inherit (from settings)', 'anwp-football-leagues' ),
					'no'  => esc_html__( 'no', 'anwp-football-leagues' ),
					'yes' => esc_html__( 'yes', 'anwp-football-leagues' ),
				],
			],
			[
				'name'    => esc_html__( 'Match List (slim) custom button', 'anwp-football-leagues-premium' ),
				'id'      => $prefix . 'match_list_custom_button',
				'type'    => 'select',
				'default' => '',
				'options' => [
					''      => esc_html__( 'none', 'anwp-football-leagues' ),
					'image' => esc_html__( 'image only', 'anwp-football-leagues' ),
					'link'  => esc_html__( 'image with link', 'anwp-football-leagues' ),
				],
			],
			[
				'name' => esc_html__( 'Match List custom button URL', 'anwp-football-leagues-premium' ),
				'id'   => $prefix . 'match_list_custom_button_url',
				'type' => 'text_url',
			],
			[
				'name'        => esc_html__( 'Match Editor (User role)', 'anwp-football-leagues-premium' ),
				'description' => esc_html__( 'Grant user rights to edit match on frontent', 'anwp-football-leagues-premium' ),
				'id'          => $prefix . 'role_match_editor',
				'type'        => 'anwp_user_ajax_search',
				'multiple'    => true,
				'limit'       => 5,
				'query_args'  => [
					'role__not_in' => [ 'Administrator', 'Super Admin' ],
				],
			],
			[
				'name'       => esc_html__( 'Alternative Prediction Text', 'anwp-football-leagues-premium' ),
				'id'         => $prefix . 'prediction_advice_alt',
				'type'       => 'text',
				'save_field' => false,
				'show_on_cb' => function ( $field ) {
					return absint( $field->object_id ) && ( $this->get_prediction_data( $field->object_id )['prediction_advice'] ?? '' );
				},
			],
			[
				'name'         => esc_html__( 'Scoreboard Background Image', 'anwp-football-leagues-premium' ),
				'id'           => $prefix . 'match_scoreboard_image',
				'type'         => 'file',
				'options'      => [
					'url' => false,
				],
				'query_args'   => [
					'type' => 'image',
				],
				'preview_size' => 'medium',
				'after_row'    => '</div></div>',
			],
		];
	}

	/**
	 * Save Match Premium Data.
	 *
	 * @param $data
	 * @param $posted_data
	 *
	 * @since 0.1.0
	 */
	public function save_match_premium_data( $data, $posted_data ) {
		global $wpdb;

		if ( empty( $data['match_id'] ) ) {
			return;
		}

		// Priority
		anwp_fl()->match->update( $data['match_id'], [ 'priority' => absint( $posted_data['_anwpfl_priority'] ?? 0 ) ] );

		// Prediction ALT
		if ( $this->get_prediction_data( $data['match_id'] )['prediction_advice'] ?? '' ) {
			global $wpdb;

			$wpdb->update(
				$wpdb->anwpfl_predictions,
				[
					'prediction_advice_alt' => sanitize_text_field( $posted_data['_anwpfl_prediction_advice_alt'] ),
				],
				[
					'match_id' => $data['match_id'],
				]
			);
		}

		/*
		|--------------------------------------------------------------------
		| Formation
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $posted_data['_anwpfl_match_formation'] ) ) {
			$formation_data = [
				'home_club_shirt' => sanitize_text_field( $posted_data['_anwpfl_home_club_shirt'] ?? '' ),
				'away_club_shirt' => sanitize_text_field( $posted_data['_anwpfl_away_club_shirt'] ?? '' ),
				'formation'       => wp_json_encode( json_decode( $posted_data['_anwpfl_match_formation'] ) ) ?: '',
			];

			if ( 'custom_color' === $formation_data['home_club_shirt'] ) {
				$shirt_color      = isset( $posted_data['_anwpfl_home_shirt_color'] ) ? json_decode( $posted_data['_anwpfl_home_shirt_color'] ) : false;
				$shirt_color_json = $shirt_color ? wp_json_encode( $shirt_color ) : false;

				if ( $shirt_color_json ) {
					update_post_meta( $data['match_id'], '_anwpfl_home_shirt_color', wp_slash( $shirt_color_json ) );
				}
			}

			if ( 'custom_color' === $formation_data['away_club_shirt'] ) {
				$shirt_color      = isset( $posted_data['_anwpfl_away_shirt_color'] ) ? json_decode( $posted_data['_anwpfl_away_shirt_color'] ) : false;
				$shirt_color_json = $shirt_color ? wp_json_encode( $shirt_color ) : false;

				if ( $shirt_color_json ) {
					update_post_meta( $data['match_id'], '_anwpfl_away_shirt_color', wp_slash( $shirt_color_json ) );
				}
			}

			if ( empty( $this->get_formation_data( $data['match_id'] ) ) ) {
				$formation_data['match_id'] = $data['match_id'];
				$wpdb->insert( $wpdb->anwpfl_formations, $formation_data );
			} else {
				$wpdb->update( $wpdb->anwpfl_formations, $formation_data, [ 'match_id' => $data['match_id'] ] );
			}
		}
	}

	/**
	 * Modify Match edit localized data.
	 * Add Match formation to the localized Match data.
	 *
	 * @param $data
	 * @param $post_id
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function modify_localized_data( $data, $post_id ) {
		if ( is_array( $data ) ) {

			$formation_data = $this->get_formation_data( $post_id );

			// Match Formation
			$data['formation']       = $formation_data['formation'] ?? '';
			$data['home_club_shirt'] = $formation_data['home_club_shirt'] ?? '';
			$data['away_club_shirt'] = $formation_data['away_club_shirt'] ?? '';
			$data['formation_bg']    = AnWP_Football_Leagues_Premium::url( 'public/img/soccer_field_cut.svg' );

			if ( 'custom_color' === $data['home_club_shirt'] ) {
				$data['home_shirt_color'] = get_post_meta( $post_id, '_anwpfl_home_shirt_color', true )
					? json_decode( get_post_meta( $post_id, '_anwpfl_home_shirt_color', true ) ) : '';
			}

			if ( 'custom_color' === $data['away_club_shirt'] ) {
				$data['away_shirt_color'] = get_post_meta( $post_id, '_anwpfl_away_shirt_color', true )
					? json_decode( get_post_meta( $post_id, '_anwpfl_away_shirt_color', true ) ) : '';
			}

			// Live
			$data['_live_mode']       = AnWPFL_Premium_Options::get_value( 'match_live_mode', '' );
			$data['_live_commentary'] = get_post_meta( $post_id, '_anwpfl_match_live_commentary', true );

			if ( 'yes' === $data['_live_mode'] ) {
				$data['_nonce_live_save'] = wp_create_nonce( 'anwpfl-live-save' );
				$data['_live_home_score'] = get_post_meta( $post_id, '_anwpfl_live_home_score', true );
				$data['_live_away_score'] = get_post_meta( $post_id, '_anwpfl_live_away_score', true );
				$data['_live_status']     = get_post_meta( $post_id, '_anwpfl_live_status', true );

				// Live timing
				$cur_time = get_post_meta( $post_id, '_anwpfl_live_current_time', true );
				$max_time = get_post_meta( $post_id, '_anwpfl_live_max_time', true );

				// Time offset
				$time_offset = absint( ( time() - ( get_post_meta( $post_id, '_anwpfl_live_timestamp_status', true ) ?: time() ) ) / 60 );

				switch ( $data['_live_status'] ) {

					case '_1_st_half':
					case '_2_nd_half':
					case 'extra_time':
						$data['live_max_time']     = $max_time;
						$data['live_current_time'] = ( $cur_time + $time_offset ) > $max_time ? $max_time : ( $cur_time + $time_offset );
						break;

					case 'penalty':
					case 'half_time':
					case 'full_time':
						$data['live_max_time']     = $max_time;
						$data['live_current_time'] = $max_time;
						break;

					default:
						$data['live_max_time']     = '';
						$data['live_current_time'] = '';
						break;
				}
			}
		}

		return $data;
	}

	/**
	 * Registers admin columns to display.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 * @since  0.6.0
	 */
	public function columns( array $columns ): array {

		$columns = array_merge( $columns, [ '_fl_match_priority' => esc_html__( 'Priority', 'anwp-football-leagues-premium' ) ] );

		if ( 'yes' === AnWPFL_Premium_Options::get_value( 'send_match_report_by_email' ) ) {
			$columns = array_merge( $columns, [ '_fl_send_match_report' => esc_html__( 'Send Report', 'anwp-football-leagues-premium' ) ] );
		}

		return $columns;
	}

	/**
	 * Handles admin column display.
	 *
	 * @param string  $column  Column currently being rendered.
	 * @param integer $post_id ID of post to display column for.
	 */
	public function match_columns_display( string $column, int $post_id ) {
		global $post;

		switch ( $column ) {
			case '_fl_match_priority':
				echo esc_html( $post->_fl_priority ?? '' );
				break;

			case '_fl_send_match_report':
				if ( absint( $post->_fl_finished ) ) {
					echo '<button class="button anwp-fl-send-report" type="button" data-match-id="' . absint( $post_id ) . '">Send Report<span class="spinner" style="margin: -4px 0 0 8px;float: none;"></span></button>';
				}
				break;
		}
	}

	/**
	 * Get array of H2H matches.
	 *
	 * @param object|array $options
	 *
	 * @return array|null|object
	 * @since 0.5.8
	 */
	public function get_matches_h2h( $options ) {

		global $wpdb;

		$options = (object) wp_parse_args(
			$options,
			[
				'competition_id' => '',
				'season_id'      => '',
				'league_id'      => '',
				'show_secondary' => '',
				'type'           => '',
				'sort_by_date'   => '',
				'limit'          => '',
				'club_a'         => '',
				'club_b'         => '',
				'date_before'    => '',
			]
		);

		$query = "
			SELECT *
			FROM {$wpdb->prefix}anwpfl_matches
			WHERE 1=1
		";

		/**==================
		 * WHERE filter by competition
		 *================ */
		// Get competition to filter
		if ( AnWP_Football_Leagues::string_to_bool( $options->show_secondary ) && '' !== $options->competition_id ) {
			$query .= $wpdb->prepare( ' AND (competition_id = %d OR main_stage_id = %d) ', $options->competition_id, $options->competition_id );
		} elseif ( '' !== $options->competition_id ) {
			$query .= $wpdb->prepare( ' AND competition_id = %d ', $options->competition_id );
		}

		/**==================
		 * WHERE filter by season
		 *================ */
		if ( '' !== $options->season_id && '' === $options->competition_id ) {
			$query .= $wpdb->prepare( ' AND season_id = %d ', $options->season_id );
		}

		/**==================
		 * WHERE filter by league
		 *================ */
		if ( absint( $options->league_id ) ) {
			$query .= $wpdb->prepare( ' AND league_id = %d ', absint( $options->league_id ) );
		}

		/**==================
		 * WHERE filter by type
		 *================ */
		if ( '' !== $options->type ) {
			$query .= $wpdb->prepare( ' AND finished = %d ', 'result' === $options->type ? 1 : 0 );
		}

		/**==================
		 * WHERE date_before
		 *================ */
		if ( '' !== $options->date_before ) {
			$query .= $wpdb->prepare( ' AND kickoff < %s ', $options->date_before );
		}

		/**==================
		 * WHERE filter by clubs
		 *================ */
		$query .= $wpdb->prepare(
			' AND ( ( home_club = %d AND away_club = %d ) OR ( home_club = %d AND away_club = %d ) )',
			$options->club_a,
			$options->club_b,
			$options->club_b,
			$options->club_a
		);

		/**==================
		 * ORDER BY match date
		 *================ */
		if ( 'asc' === mb_strtolower( $options->sort_by_date ) ) {
			$query .= ' ORDER BY kickoff ASC';
		} elseif ( 'desc' === mb_strtolower( $options->sort_by_date ) ) {
			$query .= ' ORDER BY kickoff DESC';
		}

		/**==================
		 * LIMIT clause
		 *================ */
		if ( isset( $options->limit ) && 0 < $options->limit ) {
			$query .= $wpdb->prepare( ' LIMIT %d', $options->limit );
		}

		$matches = $wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore WordPress.DB.PreparedSQL

		// Add links
		$links_map = anwp_fl()->helper->get_permalinks_by_ids( wp_list_pluck( $matches, 'match_id' ), 'anwp_match' );

		foreach ( $matches as $row_id => $match ) {
			$match->permalink = $links_map[ $match->match_id ] ?? '';
		}

		return $matches;
	}

	/**
	 * Get array of dates.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function get_calendar_dates( WP_REST_Request $request ) {

		global $wpdb;

		$params = $request->get_params();
		$args   = AnWPFL_Premium_Helper::parse_rest_url_params( $params['args'] );

		$user_auto_timezone = ( 'yes' === AnWPFL_Premium_Options::get_value( 'user_auto_timezone' ) && ! empty( $args['_user_tz'] ) );
		$user_tz            = isset( $args['_user_tz'] ) ? (int) $args['_user_tz'] : 0;

		$options = (object) wp_parse_args(
			$args,
			[
				'c_id'  => '',
				's_s'   => '',
				'cl_id' => '',
			]
		);

		$min_delta = ( $user_tz - ( wp_date( 'Z' ) / 60 ) );

		if ( $user_auto_timezone && $min_delta ) {

			if ( $min_delta > 0 ) {
				$query = $wpdb->prepare( 'SELECT DISTINCT DATE( DATE_ADD( kickoff, INTERVAL %d MINUTE ) )', $min_delta );
			} else {
				$query = $wpdb->prepare( 'SELECT DISTINCT DATE( DATE_SUB( kickoff, INTERVAL %d MINUTE ) )', absint( $min_delta ) );
			}

			$query .= " FROM {$wpdb->prefix}anwpfl_matches WHERE 1=1 ";

		} else {
			$query = "
				SELECT DISTINCT DATE( kickoff )
				FROM {$wpdb->prefix}anwpfl_matches
				WHERE 1=1
			";
		}

		/**==================
		 * WHERE filter by competition
		 *================ */
		// Get competition to filter
		if ( 1 === $options->s_s && '' !== $options->c_id ) {
			$query .= $wpdb->prepare( ' AND (competition_id = %d OR main_stage_id = %d) ', $options->c_id, $options->c_id );
		} elseif ( '' !== $options->c_id ) {
			$query .= $wpdb->prepare( ' AND competition_id = %d ', $options->c_id );
		}

		/**==================
		 * WHERE filter by club
		 *================ */
		if ( ! empty( $options->cl_id ) ) {
			$clubs  = wp_parse_id_list( $options->cl_id );
			$format = implode( ', ', array_fill( 0, count( $clubs ), '%d' ) );

			$query .= $wpdb->prepare( " AND ( home_club IN ({$format}) OR away_club IN ({$format}) ) ", array_merge( $clubs, $clubs ) ); // phpcs:ignore
		}

		$query .= ' ORDER BY kickoff ASC';

		$dates = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		return rest_ensure_response( [ 'dates' => empty( $dates ) || ! is_array( $dates ) ? [] : $dates ] );
	}

	/**
	 * Calendar Widget Output
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since 0.5.10 (Premium)
	 */
	public function get_calendar_widget_matches( WP_REST_Request $request ) {

		$params   = $request->get_params();
		$args_min = AnWPFL_Premium_Helper::parse_rest_url_params( $params['args'] );

		// Check date is set
		$date = $args_min['date'];

		if ( ! $date ) {
			return new WP_Error( 'rest_invalid', 'Date is invalid', [ 'status' => 400 ] );
		}

		$user_auto_timezone = ( 'yes' === AnWPFL_Premium_Options::get_value( 'user_auto_timezone' ) && ! empty( $args_min['_user_tz'] ) );
		$user_tz            = isset( $args_min['_user_tz'] ) ? (int) $args_min['_user_tz'] : '';

		ob_start();

		anwp_football_leagues()->load_partial(
			[
				'no_data_text' => AnWPFL_Text::get_value( 'data__matches__no_matches_for_this_date', esc_html__( 'No matches for this date.', 'anwp-football-leagues-premium' ) ),
			],
			'general/no-data'
		);

		$html_output = ob_get_clean();

		$args = (object) [
			'competition_id'       => isset( $args_min['c_id'] ) ? $args_min['c_id'] : '',
			'show_secondary'       => isset( $args_min['s_s'] ) ? $args_min['s_s'] : '',
			'club_id'              => isset( $args_min['cl_id'] ) ? $args_min['cl_id'] : '',
			'layout'               => isset( $args_min['l'] ) ? $args_min['l'] : '',
			'show_club_logos'      => isset( $args_min['s_c_l'] ) ? $args_min['s_c_l'] : 1,
			'show_club_name'       => isset( $args_min['s_c_n'] ) ? $args_min['s_c_n'] : true,
			'group_by_time'        => isset( $args_min['g_t'] ) ? $args_min['g_t'] : 0,
			'group_by_competition' => isset( $args_min['g_c'] ) ? $args_min['g_c'] : 0,
		];

		// Sanitize and validate
		$data = [
			'competition_id'       => sanitize_text_field( $args->competition_id ),
			'show_secondary'       => absint( $args->show_secondary ),
			'filter_by_clubs'      => sanitize_text_field( $args->club_id ),
			'group_by_competition' => AnWP_Football_Leagues::string_to_bool( $args->group_by_competition ),
			'group_by_time'        => AnWP_Football_Leagues::string_to_bool( absint( $args->group_by_time ) ),
			'competition_logo'     => ! AnWP_Football_Leagues::string_to_bool( $args->group_by_competition ),
			'club_links'           => false,
			'show_match_datetime'  => false,
			'date_from'            => $date,
			'date_to'              => $date,
			'sort_by_date'         => 'asc',
			'group_by'             => AnWP_Football_Leagues::string_to_bool( $args->group_by_competition ) ? 'competition' : '',
			'show_club_logos'      => sanitize_text_field( $args->show_club_logos ),
			'show_club_name'       => AnWP_Football_Leagues::string_to_bool( sanitize_text_field( $args->show_club_name ) ),
		];

		if ( $user_auto_timezone ) {
			$min_delta = ( ( wp_date( 'Z' ) / 60 ) - $user_tz );

			if ( $min_delta ) {
				$match_date_from = DateTime::createFromFormat( 'Y-m-d H:i:s', $date . ' 00:00:00' );
				$match_date_to   = DateTime::createFromFormat( 'Y-m-d H:i:s', $date . ' 23:59:59' );

				$data['kickoff_from'] = $match_date_from->modify( $min_delta . ' minute' )->format( 'Y-m-d H:i:s' );
				$data['kickoff_to']   = $match_date_to->modify( $min_delta . ' minute' )->format( 'Y-m-d H:i:s' );

				unset( $data['date_from'] );
				unset( $data['date_to'] );
			}
		}

		// Get matches
		$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $data );

		if ( empty( $matches ) || ! is_array( $matches ) ) {
			return rest_ensure_response( [ 'html' => $html_output ] );
		}

		// Set layout
		$layout = in_array( $args->layout, [ 'simple', 'modern' ], true ) ? $args->layout : 'simple';

		$ids = array_unique( array_values( wp_list_pluck( $matches, 'competition_id' ) ) );

		// Reset ids if group by competition not set
		if ( ! $data['group_by_competition'] ) {
			$ids = [ reset( $ids ) ];
		}

		// Init group initials
		$last_group_by_competition = '';

		ob_start();
		foreach ( $ids as $competition_id ) {

			$last_group_by_time = '';

			foreach ( $matches as $index => $match ) {

				if ( $data['group_by_competition'] ) {

					if ( absint( $competition_id ) !== absint( $match->competition_id ) ) {
						continue;
					}

					if ( $last_group_by_competition !== $competition_id ) {

						$last_group_by_competition = $competition_id;
						$competition_obj           = anwp_football_leagues()->competition->get_competition( $competition_id );

						if ( empty( $competition_obj ) ) {
							continue;
						}

						$competition_logo = $competition_obj->logo;

						if ( 'secondary' === $competition_obj->multistage && absint( $competition_obj->multistage_main ) ) {
							$competition_main_obj = anwp_football_leagues()->competition->get_competition( $competition_obj->multistage_main );

							if ( empty( $competition_main_obj ) ) {
								continue;
							}

							$competition_logo = $competition_main_obj->logo;
						}
						?>
						<div class="anwp-fl-block-header anwp-text-sm d-flex align-items-center anwp-text-sm mb-1 <?php echo $index ? 'mt-3' : ''; ?>">
							<?php if ( ! empty( $competition_logo ) ) : ?>
								<img loading="lazy" width="30" height="30" src="<?php echo esc_url( $competition_logo ); ?>" alt="competition logo" class="anwp-object-contain ml-0 mr-2 my-1 anwp-w-30 anwp-h-30">
							<?php endif; ?>
							<?php echo esc_html( $competition_obj->title ); ?>
						</div>
						<?php
					}
				}

				if ( $user_auto_timezone ) {
					$min_delta = ( $user_tz - ( wp_date( 'Z' ) / 60 ) );

					if ( $min_delta ) {
						$match_date     = DateTime::createFromFormat( 'Y-m-d H:i:s', $match->kickoff );
						$match->kickoff = $match_date->modify( $min_delta . ' minute' )->format( 'Y-m-d H:i:s' );
					}
				}

				$tmpl_data = array_merge( (array) $data, anwp_football_leagues()->match->prepare_match_data_to_render( $match, $data ) );

				if ( $data['group_by_time'] && $last_group_by_time !== $tmpl_data['match_time'] ) {
					$last_group_by_time = $tmpl_data['match_time'];
					?>
					<div class="anwp-text-base anwp-bg-light p-1 my-1 d-flex align-items-center">
						<svg class="anwp-icon anwp-icon--octi anwp-icon--gray-700 anwp-icon--s14 mr-1">
							<use xlink:href="#icon-clock"></use>
						</svg>
						<?php echo esc_html( $last_group_by_time ); ?>
					</div>
					<?php
				}

				anwp_football_leagues()->load_partial( $tmpl_data, 'match/match', $layout );
			}
		}

		$html_output = ob_get_clean();

		return rest_ensure_response( [ 'html' => $html_output ] );
	}

	/**
	 * Get Match Odds
	 *
	 * @since 0.15.0
	 */
	public function get_match_odds( WP_REST_Request $request ) {

		$params  = $request->get_params();
		$odd_id  = absint( $params['odd_id'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$game_id = absint( $params['game_id'] ); // phpcs:ignore WordPress.Security.NonceVerification

		if ( ! $odd_id || ! $game_id ) {
			return new WP_Error( 'rest_invalid', 'Data is invalid', [ 'status' => 400 ] );
		}

		$game_data = anwp_fl()->match->get_game_data( $game_id );

		$odds_competition = absint( $game_data['main_stage_id'] ) ?: absint( $game_data['competition_id'] );
		$odds_data        = get_post_meta( $odds_competition, '_anwpfl_league_odds', true );

		if ( empty( $odds_data[ $game_id ] ) ) {
			return rest_ensure_response( [ 'html' => '' ] );
		}

		$odds_data = $odds_data[ $game_id ];

		if ( empty( $odds_data['odds'] ) ) {
			return rest_ensure_response( [ 'html' => '' ] );
		}

		return rest_ensure_response( [ 'html' => anwp_football_leagues_premium()->match->get_match_odds_table( $odds_data['odds'][ $odd_id ] ) ] );
	}

	/**
	 * Calendar Slider Game Dates
	 *
	 * @param $options
	 * @param $min_date
	 * @param $max_date
	 *
	 * @return string
	 * @since 0.16.0
	 */
	public function get_calendar_slider_games_dates( $options, $min_date, $max_date ): string {

		global $wpdb;

		$options = wp_parse_args(
			$options,
			[
				'competition_id'  => '',
				'filter_by_clubs' => '',
			]
		);

		// Try to get from cache
		$cache_key = 'FL-PRO-GAMES_get_calendar_slider_game_dates__' . md5( maybe_serialize( $options ) );

		if ( anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) ) {
			return anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' );
		}

		$query = "
		SELECT kickoff, match_id
		FROM {$wpdb->prefix}anwpfl_matches
		";

		$query .= $wpdb->prepare( ' WHERE kickoff >= %s AND kickoff <= %s', $min_date . ' 00:00:00', $max_date . ' 23:59:59' );

		/*
		|--------------------------------------------------------------------
		| WHERE filter by competition_id
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options['competition_id'] ) {
			$query .= $wpdb->prepare( ' AND ( competition_id = %d OR main_stage_id = %d ) ', $options['competition_id'], $options['competition_id'] );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter_by_clubs
		|--------------------------------------------------------------------
		*/
		if ( '' !== $options['filter_by_clubs'] && absint( $options['filter_by_clubs'] ) ) {

			$clubs  = wp_parse_id_list( $options['filter_by_clubs'] );
			$format = implode( ', ', array_fill( 0, count( $clubs ), '%d' ) );

			$query .= $wpdb->prepare( " AND ( home_club IN ({$format}) OR away_club IN ({$format}) ) ", array_merge( $clubs, $clubs ) ); // phpcs:ignore
		}

		$query .= ' ORDER BY kickoff ASC';

		$output = [];
		$games  = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( ! empty( $games ) && is_array( $games ) ) {
			foreach ( $games as $single_match ) {
				if ( '0000-00-00 00:00:00' === $single_match->kickoff ) {
					continue;
				}

				$output[] = absint( $single_match->match_id ) . '|' . date_i18n( 'c', strtotime( $single_match->kickoff ) );
			}
		}

		$output = implode( '||', $output );

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $output ) ) {
			anwp_football_leagues()->cache->set( $cache_key, $output, 'anwp_match' );
		}

		return $output;
	}

	/**
	 * Calendar Slider Matches
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since 0.11.15
	 */
	public function get_calendar_slider_games( WP_REST_Request $request ) {

		$params = $request->get_params();
		$args   = AnWPFL_Premium_Helper::parse_rest_url_params( $params['args'] );

		if ( empty( $args['ids'] ) ) {
			return new WP_Error( 'rest_invalid', 'Incorrect IDs', [ 'status' => 400 ] );
		}

		$competition_country_type = isset( $args['c_cy'] ) ? sanitize_text_field( $args['c_cy'] ) : '';
		$competition_title        = isset( $args['c_tl'] ) ? sanitize_text_field( $args['c_tl'] ) : '';
		$competition_link         = AnWP_Football_Leagues::string_to_bool( $args['c_lk'] ?? 1 );
		$group_by_competition     = AnWP_Football_Leagues::string_to_bool( $args['gr_by_c'] ?? 1 );
		$html_output              = '';

		// Get matches
		$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended(
			[
				'include_ids'    => $args['ids'],
				'sort_by_date'   => 'asc',
				'show_secondary' => 1,
				'group_by'       => $group_by_competition ? 'competition' : '',
			]
		);

		if ( empty( $matches ) || ! is_array( $matches ) ) {
			return rest_ensure_response( [ 'html' => $html_output ] );
		}

		// Set layout
		$layout = 'slim';

		$ids = array_unique( array_values( wp_list_pluck( $matches, 'competition_id' ) ) );

		// Reset ids if group by competition not set
		if ( ! $group_by_competition ) {
			$ids = [ reset( $ids ) ];
		}

		// Init group initials
		$last_group_by_competition = '';

		ob_start();
		foreach ( $ids as $competition_id ) {

			foreach ( $matches as $match ) {

				if ( $group_by_competition ) {

					if ( absint( $competition_id ) !== absint( $match->competition_id ) ) {
						continue;
					}

					if ( $last_group_by_competition !== $competition_id ) {

						$block_class = $last_group_by_competition ? ' mt-3' : '';

						$last_group_by_competition = $competition_id;
						$competition_obj           = anwp_football_leagues()->competition->get_competition( $competition_id );

						if ( empty( $competition_obj ) ) {
							continue;
						}

						/*
						|--------------------------------------------------------------------
						| Competition Name
						|--------------------------------------------------------------------
						*/
						$competition_name = 'league' === $competition_title ? $competition_obj->league_text : $competition_obj->title;

						/*
						|--------------------------------------------------------------------
						| Competition Logo
						|--------------------------------------------------------------------
						*/
						if ( 'secondary' === $competition_obj->multistage && absint( $competition_obj->multistage_main ) ) {
							$competition_main_obj = anwp_football_leagues()->competition->get_competition( $competition_obj->multistage_main );

							if ( empty( $competition_main_obj ) ) {
								continue;
							}

							$competition_logo = $competition_main_obj->logo;
						} else {
							$competition_logo = $competition_obj->logo;
						}

						if ( ! empty( $competition_logo ) ) {
							$competition_logo = '<img loading="lazy" width="30" height="30" class="anwp-object-contain mb-1 mr-2 anwp-w-30 anwp-h-30" src="' . esc_url( $competition_logo ) . '">';
						} else {
							$competition_logo = '';
						}

						/*
						|--------------------------------------------------------------------
						| Competition Country
						|--------------------------------------------------------------------
						*/
						$competition_country = '';

						if ( ! empty( $competition_country_type ) && in_array( $competition_country_type, [ 'country', 'country-flag', 'flag' ], true ) ) {
							$country_code = absint( $competition_obj->league_id ) ? anwp_football_leagues_premium()->competition->get_league_country_code( $competition_obj->league_id ) : '';

							if ( ! empty( $country_code ) ) {
								$country_name = anwp_football_leagues()->data->get_value_by_key( $country_code, 'country' );

								if ( 'country' === $competition_country_type || 'country-flag' === $competition_country_type ) {
									$competition_name .= ' (' . $country_name . ')';
								}

								if ( ( 'flag' === $competition_country_type || 'country-flag' === $competition_country_type ) ) {
									ob_start();

									anwp_football_leagues()->load_partial(
										[
											'class'         => 'options__flag ml-2',
											'wrapper_class' => 'ml-2 anwp-flex-none',
											'size'          => 32,
											'width'         => 25,
											'country_code'  => $country_code,
										],
										'general/flag'
									);

									$competition_country .= ob_get_clean();
								}
							}
						}

						if ( $competition_link ) {
							$competition_name = sprintf( '<a href="%s" class="anwp-link-without-effects">%s</a>', get_permalink( absint( $competition_obj->multistage_main ) ?: $competition_id ), $competition_name );
						}

						anwp_football_leagues()->load_partial(
							[
								'text'       => $competition_logo . $competition_name . $competition_country,
								'allow_html' => true,
								'class'      => 'd-flex align-items-center anwp-leading-1' . $block_class,
							],
							'general/header'
						);
					}
				}

				$game_data = anwp_football_leagues()->match->prepare_match_data_to_render( $match );

				$game_data['competition_logo'] = ! $group_by_competition;
				$game_data['club_links']       = false;
				anwp_football_leagues()->load_partial( $game_data, 'match/match', $layout );
			}
		}

		$html_output = ob_get_clean();

		return rest_ensure_response( [ 'html' => $html_output ] );
	}

	/**
	 * Standing Club Matches Output
	 *
	 * @since 0.8.7 (Premium)
	 */
	public function get_standing_club_matches( WP_REST_Request $request ) {

		$params = (object) wp_parse_args(
			$request->get_params(),
			[
				'standing_id' => '',
				'team_id'     => '',
			]
		);

		if ( ! absint( $params->standing_id ) || ! absint( $params->team_id ) ) {
			return new WP_Error( 'rest_invalid', 'Invalid Data', [ 'status' => 400 ] );
		}

		$competition_id = get_post_meta( $params->standing_id, '_anwpfl_competition', true );

		if ( ! $competition_id ) {
			return new WP_Error( 'rest_invalid', 'Invalid Competition Data', [ 'status' => 400 ] );
		}

		$html_output = '<div class="p-3 bg-light w-100 mb-0">' . AnWPFL_Text::get_value( 'data__matches__no_matches_found', esc_html__( 'No matches found.', 'anwp-football-leagues-premium' ) ) . '</div>';

		// Sanitize and validate
		$data = [
			'competition_id'      => $competition_id,
			'filter_by_clubs'     => [ $params->team_id ],
			'outcome_id'          => $params->team_id,
			'show_secondary'      => 0,
			'show_club_logos'     => 1,
			'show_match_datetime' => 1,
			'club_links'          => 1,
			'sort_by_date'        => 'asc',
			'competition_logo'    => 0,
		];

		// Get competition matches
		$matches = anwp_fl()->competition->tmpl_get_competition_matches_extended( $data );

		if ( empty( $matches ) || ! is_array( $matches ) ) {
			return rest_ensure_response(
				[
					'html' => $html_output,
				]
			);
		}

		ob_start();

		foreach ( $matches as $m_index => $match ) {
			$tmpl_data = array_merge( (array) $data, anwp_fl()->match->prepare_match_data_to_render( $match, $data ) );
			anwp_fl()->load_partial( $tmpl_data, 'match/match', 'slim' );
		}

		$html_output = ob_get_clean();

		return rest_ensure_response(
			[
				'html' => $html_output,
			]
		);
	}

	/**
	 * Club Form Match tooltip Output
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since 0.9.3
	 */
	public function get_form_club_match( WP_REST_Request $request ) {

		$game_id = absint( $request->get_param( 'game_id' ) );

		if ( ! $game_id ) {
			return new WP_Error( 'rest_invalid', 'Game is not set', [ 'status' => 400 ] );
		}

		// Get competition matches
		$game_data = anwp_fl()->match->get_game_data( $game_id );

		if ( empty( $game_data ) ) {
			return new WP_Error( 'rest_invalid', 'Game is invalid', [ 'status' => 400 ] );
		}

		ob_start();
		anwp_fl()->load_partial( anwp_fl()->match->prepare_match_data_to_render( $game_data ), 'match/match', 'card-a' );
		$html_output = ob_get_clean();

		return rest_ensure_response( [ 'html' => $html_output ] );
	}

	/**
	 * Event output for commentary block
	 *
	 * @param $event      object Event object
	 * @param $match_data object Match data (shorten)
	 *
	 * @return string
	 * @since 0.8.0 (Premium)
	 */
	public function get_commentary_event_tmpl( $event, $match_data ) {
		if ( ! isset( $event->type ) ) {
			return '';
		}

		$temp_players = anwp_football_leagues_premium()->match->get_temp_players( $match_data->match_id );
		$slim_layout  = 'slim' === AnWPFL_Premium_Options::get_value( 'match_commentary_layout' );

		/*
		|--------------------------------------------------------------------
		| Prepare event data
		|--------------------------------------------------------------------
		*/
		$event_data = (object) [
			'minute_text' => empty( $event->minute ) ? '' : ( $event->minute . "'" ),
			'half'        => intval( $event->club ),
			'icon'        => '',
			'subheader'   => '',
			'event_name'  => anwp_football_leagues_premium()->match->get_event_name_by_type( $event ),
		];

		if ( ! empty( $event->minuteAdd ) && intval( $event->minuteAdd ) && $event_data->minute_text ) {
			$event_data->minute_text .= ' +' . intval( $event->minuteAdd ) . "'";
		}

		switch ( $event->type ) {
			/*
			|--------------------------------------------------------------------
			| Goal
			|--------------------------------------------------------------------
			*/
			case 'goal':
				$event_data->icon .= '<svg class="icon__ball ' . esc_attr( 'yes' === $event->ownGoal ? 'icon__ball--own' : '' ) . '">';
				$event_data->icon .= '<use xlink:href="#' . esc_attr( 'yes' === $event->fromPenalty ? 'icon-ball_penalty' : 'icon-ball' ) . '"></use>';
				$event_data->icon .= '</svg>';

				$event_data->subheader .= esc_html( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->player, 0, 6 ) ? $temp_players[ $event->player ]->name : ( $match_data->players[ $event->player ]['short_name'] ?? '' ) );
				if ( ! empty( $event->assistant ) ) {
					$event_data->subheader .= '<div class="anwp-text-nowrap ml-3"><span class="anwp-text-lowercase match-commentary__meta">' . esc_html( AnWPFL_Text::get_value( 'match__commentary__assistant', __( 'Assistant', 'anwp-football-leagues' ) ) ) . ': </span>';
					$event_data->subheader .= esc_html( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->assistant, 0, 6 ) ? $temp_players[ $event->assistant ]->name : ( $match_data->players[ $event->assistant ]['short_name'] ?? '' ) ) . '</div>';
				}
				break;

			case 'substitute':
				$event_data->icon = '<svg class="icon__substitute"><use xlink:href="#icon-substitute"></use></svg>';

				$event_data->subheader .= '<div class="anwp-text-nowrap"><span class="anwp-text-lowercase mr-1 match-commentary__meta">' . esc_html( AnWPFL_Text::get_value( 'match__commentary__in', __( 'In', 'anwp-football-leagues-premium' ) ) ) . ':</span>';
				$event_data->subheader .= esc_html( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->player, 0, 6 ) ? $temp_players[ $event->player ]->name : ( $match_data->players[ $event->player ]['short_name'] ?? '' ) ) . '</div>';
				$event_data->subheader .= '<div class="anwp-text-nowrap ml-3"><span class="anwp-text-lowercase mr-1 match-commentary__meta">' . esc_html( AnWPFL_Text::get_value( 'match__commentary__out', __( 'Out', 'anwp-football-leagues-premium' ) ) ) . ':</span>';
				$event_data->subheader .= esc_html( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->playerOut, 0, 6 ) ? $temp_players[ $event->playerOut ]->name : ( $match_data->players[ $event->playerOut ]['short_name'] ?? '' ) ) . '</div>';
				break;

			case 'card':
				$event_data->icon      = '<svg class="icon__card"><use xlink:href="#icon-card_' . esc_attr( $event->card ) . '"></use></svg>';
				$event_data->subheader = esc_html( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->player, 0, 6 ) ? $temp_players[ $event->player ]->name : ( $match_data->players[ $event->player ]['short_name'] ?? '' ) );
				break;

			case 'cancelled_goal':
			case 'missed_penalty':
				$event_data->icon      = '<svg class="icon__ball"><use xlink:href="#icon-ball_canceled"></use></svg>';
				$event_data->subheader = esc_html( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->player, 0, 6 ) ? $temp_players[ $event->player ]->name : ( $match_data->players[ $event->player ]['short_name'] ?? '' ) );
				break;

			case 'penalty_shootout':
				$event_data->icon      = '<svg class="icon__ball"><use xlink:href="#' . esc_attr( 'yes' === $event->scored ? 'icon-ball' : 'icon-ball_canceled' ) . '"></use></svg>';
				$event_data->subheader = esc_html( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->player, 0, 6 ) ? $temp_players[ $event->player ]->name : ( $match_data->players[ $event->player ]['short_name'] ?? '' ) );
				break;

			case 'commentary':
				$event_data->icon      = '<svg class="anwp-icon anwp-icon--octi"><use xlink:href="#icon-comment"></use></svg>';
				$event_data->subheader = esc_html( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->player, 0, 6 ) ? $temp_players[ $event->player ]->name : ( $match_data->players[ $event->player ]['short_name'] ?? '' ) );
				break;

			case 'significant_event':
				$event_data->icon      = '<svg class="anwp-icon anwp-icon--octi anwp-icon--octi-red"><use xlink:href="#icon-issue-opened"></use></svg>';
				$event_data->subheader = esc_html( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->player, 0, 6 ) ? $temp_players[ $event->player ]->name : ( $match_data->players[ $event->player ]['short_name'] ?? '' ) );
				break;

			case 'var':
				$event_data->icon = '<svg class="anwp-icon anwp-icon--octi"><use xlink:href="#icon-var"></use></svg>';
				break;

		}

		$event->text = empty( $event->comment ) ? '' : $event->comment;
		$photo_dir   = wp_upload_dir()['baseurl'];
		$show_photo  = 'no' !== AnWPFL_Premium_Options::get_value( 'match_commentary_show_player_photo' );

		ob_start();
		if ( $event_data->half ) :
			?>
			<div class="anwp-row anwp-no-gutters match-commentary__row <?php echo $slim_layout ? 'my-2' : 'my-3'; ?> match-commentary__event--<?php echo esc_html( $event->type ); ?>" data-event-id="<?php echo isset( $event->id ) ? esc_attr( $event->id ) : ''; ?>">
				<div class="anwp-col-md">
					<?php if ( absint( $event->club ) === absint( $match_data->home_club ) ) : ?>
						<div class="match-commentary__block match-commentary__block--home d-flex <?php echo $slim_layout ? 'py-2 px-3' : 'p-3'; ?>" style="<?php echo esc_attr( is_rtl() ? 'border-right-color' : 'border-left-color' ); ?>: <?php echo esc_attr( $match_data->color_home ); ?>">
							<?php
							/*
							|--------------------------------------------------------------------
							| Player Photo
							|--------------------------------------------------------------------
							*/
							if ( $show_photo && ! empty( $event->player ) && ( $match_data->players[ $event->player ]['photo'] ?? '' ) ) :
								?>
								<div class="position-relative anwp-text-center mr-1 player__photo-wrapper--list">
									<img loading="lazy" class="anwp-object-contain <?php echo $slim_layout ? 'anwp-w-40 anwp-h-40' : 'anwp-w-50 anwp-h-50'; ?>"
											src="<?php echo esc_url( $photo_dir . $match_data->players[ $event->player ]['photo'] ); ?>" alt="<?php echo esc_attr( $match_data->players[ $event->player ]['short_name'] ); ?>">
								</div>
							<?php endif; ?>
							<div class="flex-grow-1 <?php echo $slim_layout ? 'anwp-text-sm' : 'anwp-text-base'; ?>">
								<div class="match-commentary__block-header d-flex align-items-center flex-wrap justify-content-md-end">
									<div class="match-commentary__event-icon--inner d-md-none mr-2"><?php echo $event_data->icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
									<span class="match-commentary__event-name"><?php echo esc_html( $event_data->event_name ); ?></span>

									<?php if ( ! empty( $event->scores ) ) : ?>
										<span class="match-commentary__scores ml-2"><?php echo esc_html( $event->scores ); ?></span>
									<?php endif; ?>

									<?php if ( $event_data->minute_text ) : ?>
										<span class="match-commentary__minute ml-2"><?php echo esc_html( $event_data->minute_text ); ?></span>
									<?php endif; ?>
								</div>
								<div class="match-commentary__block-sub-header d-flex flex-wrap align-items-end justify-content-md-end">
									<?php echo $event_data->subheader; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
								<?php if ( ! empty( $event->text ) ) : ?>
									<div class="match-commentary__block-text text-md-right anwp-text-xs anwp-opacity-70"><?php echo esc_html( $event->text ); ?></div>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<div class="anwp-col-auto d-none d-md-block">
					<div class="match-commentary__event-icon"><?php echo $event_data->icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				</div>
				<div class="anwp-col-md">
					<?php if ( absint( $event->club ) === absint( $match_data->away_club ) ) : ?>
						<div class="match-commentary__block match-commentary__block--away d-flex <?php echo $slim_layout ? 'py-2 px-2' : 'p-3'; ?>" style="<?php echo esc_attr( is_rtl() ? 'border-left-color' : 'border-right-color' ); ?>: <?php echo esc_attr( $match_data->color_away ); ?>">
							<div class="flex-grow-1 <?php echo $slim_layout ? 'anwp-text-sm' : 'anwp-text-base'; ?>">
								<div class="match-commentary__block-header d-flex align-items-center flex-wrap justify-content-end justify-content-md-start">
									<?php if ( $event_data->minute_text ) : ?>
										<span class="match-commentary__minute mr-2"><?php echo esc_html( $event_data->minute_text ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $event->scores ) ) : ?>
										<span class="match-commentary__scores mr-2"><?php echo esc_html( $event->scores ); ?></span>
									<?php endif; ?>
									<span class="match-commentary__event-name"><?php echo esc_html( $event_data->event_name ); ?></span>
									<div class="match-commentary__event-icon--inner d-md-none ml-2"><?php echo $event_data->icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								</div>
								<div class="match-commentary__block-sub-header d-flex flex-wrap align-items-end justify-content-end justify-content-md-start">
									<?php echo $event_data->subheader; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
								<?php if ( ! empty( $event->text ) ) : ?>
									<div class="match-commentary__block-text anwp-text-xs anwp-opacity-70"><?php echo esc_html( $event->text ); ?></div>
								<?php endif; ?>
							</div>
							<?php
							/*
							|--------------------------------------------------------------------
							| Player Photo
							|--------------------------------------------------------------------
							*/
							if ( $show_photo && ! empty( $event->player ) && ( $match_data->players[ $event->player ]['photo'] ?? '' ) ) :
								?>
								<div class="position-relative anwp-text-center ml-1 player__photo-wrapper--list">
									<img class="anwp-object-contain <?php echo $slim_layout ? 'anwp-w-40 anwp-h-40' : 'anwp-w-50 anwp-h-50'; ?>"
											src="<?php echo esc_url( $photo_dir . $match_data->players[ $event->player ]['photo'] ); ?>" alt="<?php echo esc_attr( $match_data->players[ $event->player ]['short_name'] ); ?>">
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php else : ?>
			<div class="anwp-row anwp-no-gutters match-commentary__row match-commentary__event--<?php echo esc_html( $event->type ); ?>" data-event-id="<?php echo isset( $event->id ) ? esc_attr( $event->id ) : ''; ?>">
				<div class="anwp-col-md">
					<div class="match-commentary__block <?php echo $slim_layout ? 'anwp-text-sm' : 'anwp-text-base'; ?> <?php echo $slim_layout ? 'py-2 px-3' : 'p-3'; ?>">
						<div class="match-commentary__block-header d-flex align-items-center flex-wrap">

							<?php if ( $event_data->minute_text ) : ?>
								<span class="match-commentary__minute mr-2"><?php echo esc_html( $event_data->minute_text ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $event->scores ) ) : ?>
								<span class="match-commentary__scores mr-2"><?php echo esc_html( $event->scores ); ?></span>
							<?php endif; ?>
							<?php if ( $event_data->event_name ) : ?>
								<span class="match-commentary__event-name mr-2"><?php echo esc_html( $event_data->event_name ); ?></span>
							<?php endif; ?>
							<div class="match-commentary__event-icon--inner"><?php echo $event_data->icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						</div>
						<div class="match-commentary__block-sub-header d-flex flex-wrap">
							<?php echo $event_data->subheader; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<?php if ( ! empty( $event->text ) ) : ?>
							<div class="match-commentary__block-text anwp-text-xs anwp-opacity-70"><?php echo esc_html( $event->text ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		endif;
		$output_html = ob_get_clean();

		/**
		 * Filter: anwpfl/tmpl-match/commentary_event
		 *
		 * @param string $output_html
		 * @param object $event
		 * @param array  $match_data
		 *
		 * @since 0.8.0
		 *
		 */
		$output_html = apply_filters( 'anwpfl/tmpl-match/commentary_event', $output_html, $event, $match_data );

		return $output_html;
	}

	/**
	 * Get event name from event object
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
	 *
	 * @param object $event
	 *
	 * @return string
	 * @since 0.8.0
	 */
	public function get_event_name_by_type( $event ) {

		// Get name for core events ( goal, substitute, card, missed_penalty )
		$name = anwp_football_leagues()->match->get_event_name_by_type( $event );

		if ( ! empty( $name ) ) {
			return $name;
		}

		// Premium events
		switch ( $event->type ) {
			case 'penalty_shootout':
				$name = esc_html( AnWPFL_Text::get_value( 'match__event__penalty_shootout', __( 'Penalty Shootout', 'anwp-football-leagues' ) ) );
				break;

			case 'cancelled_goal':
				$name = esc_html( AnWPFL_Text::get_value( 'match__event__cancelled_goal', __( 'Cancelled goal', 'anwp-football-leagues-premium' ) ) );
				break;

			case 'var':
				$name = 'VAR';
				break;
		}

		return $name;
	}

	/**
	 * Prepare events for rendering in match commentary block.
	 *
	 * @param array  $events
	 * @param string $game_id
	 * @param bool   $is_live
	 *
	 * @return array
	 * @since 0.8.0.
	 */
	public function parse_match_comments_events( array $events, string $game_id = '', bool $is_live = false ): array {

		if ( empty( $events ) ) {
			return [];
		}

		$output           = [];
		$temp_players     = $game_id ? anwp_fl()->match->get_temp_players( $game_id ) : [];
		$events_supported = [
			'var',
			'goal',
			'card',
			'substitute',
			'missed_penalty',
			'penalty_shootout',
			'cancelled_goal',
			'commentary',
			'significant_event',
		];

		/**
		 * Events supported by commentary
		 *
		 * @param array $events_supported
		 *
		 * @since 0.8.0
		 *
		 */
		$events_supported = apply_filters( 'anwpfl/match/commentary_events_supported', $events_supported );

		foreach ( $events as $event_index => $event ) {

			// Check if event is supported
			if ( ! in_array( $event->type, $events_supported, true ) ) {
				continue;
			}

			foreach ( [ 'player', 'assistant', 'playerOut' ] as $player_slug ) {

				if ( ! isset( $event->{$player_slug} ) ) {
					$event->{$player_slug} = '';
					continue;
				}

				if ( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->{$player_slug}, 0, 6 ) ) {

					$event->{$player_slug} = sanitize_text_field( $event->{$player_slug} );
					continue;
				}

				$event->{$player_slug} = intval( $event->{$player_slug} ) ?: '';
			}

			// Prepare fields
			$event->club      = isset( $event->club ) && intval( $event->club ) ? intval( $event->club ) : '';
			$event->minute    = isset( $event->minute ) && intval( $event->minute ) ? intval( $event->minute ) : '';
			$event->minuteAdd = isset( $event->minuteAdd ) && intval( $event->minuteAdd ) ? intval( $event->minuteAdd ) : '';

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
				$event->sort_b = $event->minuteAdd;
			}

			// Special sorting
			if ( empty( $event->sort_a ) && ! empty( $event->sorting ) ) {
				$special_sorting = explode( '+', $event->sorting );
				$event->sort_a   = $special_sorting[0];
				$event->sort_b   = $special_sorting[1] ?? 0;
			}

			if ( $event->sort_a ) {
				$output[] = $event;
			}
		}

		if ( ! empty( $output ) && is_array( $output ) ) {

			$sort_order = ( 'desc' === AnWPFL_Premium_Options::get_value( 'match_commentary_order' ) || $is_live ) ? 'DESC' : 'ASC';

			$output = wp_list_sort(
				$output,
				[
					'sort_a' => $sort_order,
					'sort_b' => $sort_order,
				]
			);
		}

		return $output;
	}

	/**
	 * Set scores in Match commentary
	 *
	 * @param $events
	 * @param $match_data
	 *
	 * @return array
	 * @since 0.11.8
	 */
	public function set_scores_in_commentary( $events, $match_data, $order = '' ): array {

		$commentary_order = $order ?: AnWPFL_Premium_Options::get_value( 'match_commentary_order' );

		$data = [
			'home' => [
				'club' => absint( $match_data['home_club'] ),
				'goal' => 0,
			],
			'away' => [
				'club' => absint( $match_data['away_club'] ),
				'goal' => 0,
			],
		];

		if ( 'desc' === $commentary_order ) {
			$data['home']['goal'] = absint( $match_data['home_goals'] );
			$data['away']['goal'] = absint( $match_data['away_goals'] );
		}

		foreach ( $events as $event ) {
			if ( 'goal' !== $event->type ) {
				continue;
			}

			if ( 'desc' === $commentary_order ) {

				$event->scores = $data['home']['goal'] . ':' . $data['away']['goal'];

				if ( absint( $event->club ) === $data['home']['club'] ) {
					$data['home']['goal'] --;
				} elseif ( absint( $event->club ) === $data['away']['club'] ) {
					$data['away']['goal'] --;
				}
			} else {

				if ( absint( $event->club ) === $data['home']['club'] ) {
					$data['home']['goal'] ++;
				} elseif ( absint( $event->club ) === $data['away']['club'] ) {
					$data['away']['goal'] ++;
				}

				$event->scores = $data['home']['goal'] . ':' . $data['away']['goal'];
			}
		}

		return $events;
	}

	/**
	 * Update Match title and slug.
	 *
	 * @param array $game_data
	 * @param int   $post_id
	 *
	 * @return bool
	 * @since 0.8.1
	 */
	public function update_match_title_slug( array $game_data, int $post_id ): bool {

		if ( empty( $game_data ) ) {
			$game_data = anwp_fl()->match->get_game_data( $post_id );
		}

		/**
		 * Update Match title and slug
		 */
		if ( ! empty( $game_data['home_club'] && ! empty( $game_data['away_club'] ) ) ) {

			/**
			 * Update Match title and slug.
			 *
			 * @since 0.3.0
			 */
			$post      = get_post( $post_id );
			$home_club = anwp_fl()->club->get_club_title_by_id( $game_data['home_club'] );
			$away_club = anwp_fl()->club->get_club_title_by_id( $game_data['away_club'] );

			if ( ! $home_club || ! $away_club ) {
				return false;
			}

			if ( trim( AnWPFL_Options::get_value( 'match_title_generator' ) ) ) {
				$match_title = anwp_fl()->match->get_match_title_generated( $game_data, $home_club, $away_club );
			} else {
				$match_title_separator = AnWPFL_Options::get_value( 'match_title_separator', '-' );

				/**
				 * Filters a match title clubs separator.
				 *
				 * @param string  $match_title_separator Match title separator to be returned.
				 * @param WP_Post $post                  Match WP_Post object
				 * @param array   $data                  Match data
				 *
				 * @since 0.10.1
				 *
				 */
				$match_title_separator = apply_filters( 'anwpfl/match/title_separator_to_save', $match_title_separator, $post, $game_data );

				$match_title = sanitize_text_field( $home_club . ' ' . $match_title_separator . ' ' . $away_club );

				/**
				 * Filters a match title before save.
				 *
				 * @param string  $match_title Match title to be returned.
				 * @param string  $home_club   Home club title.
				 * @param string  $away_club   Away club title.
				 * @param WP_Post $post        Match WP_Post object
				 * @param array   $data        Match data
				 *
				 * @since 0.5.3
				 */
				$match_title = apply_filters( 'anwpfl/match/title_to_save', $match_title, $home_club, $away_club, $post, $game_data );
			}

			$match_slug = anwp_fl()->match->get_match_slug_generated( $game_data, $home_club, $away_club, $post );

			// Rename Match (title and slug)
			if ( $post->post_name !== $match_slug || $post->post_title !== $match_title ) {

				// update the post, which calls save_post again
				wp_update_post(
					[
						'ID'         => $post_id,
						'post_title' => $match_title,
						'post_name'  => $match_slug,
					]
				);

				return true;
			}
		}

		return false;
	}

	/**
	 * Rendering premium Match sections in shortcode helper.
	 *
	 * @since 0.9.2
	 */
	public function add_match_shortcode_premium_sections() {
		ob_start();
		?>
		<option value="scoreboard"><?php echo esc_html__( 'Scoreboard', 'anwp-football-leagues-premium' ); ?></option>
		<option value="commentary"><?php echo esc_html__( 'Commentary', 'anwp-football-leagues-premium' ); ?></option>
		<option value="custom-code"><?php echo esc_html__( 'Custom Code', 'anwp-football-leagues-premium' ); ?></option>
		<option value="formation"><?php echo esc_html__( 'Formations', 'anwp-football-leagues-premium' ); ?></option>
		<option value="timeline"><?php echo esc_html__( 'Timeline', 'anwp-football-leagues-premium' ); ?></option>
		<option value="h2h"><?php echo esc_html__( 'Head to Head Matches', 'anwp-football-leagues-premium' ); ?></option>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get Match Data.
	 *
	 * @param int $post_id
	 *
	 * @return object|bool
	 * @since 0.9.2
	 */
	public function get_match_data( int $post_id ) {
		static $output = null;

		if ( null === $output || empty( $output[ $post_id ] ) ) {

			$game_data = anwp_fl()->match->prepare_match_data_to_render( anwp_football_leagues()->match->get_game_data( $post_id ), [], 'match', 'full' );

			$game_data['summary']         = get_post_meta( $post_id, '_anwpfl_summary', true );
			$game_data['video_source']    = get_post_meta( $post_id, '_anwpfl_video_source', true );
			$game_data['video_media_url'] = get_post_meta( $post_id, '_anwpfl_video_media_url', true );
			$game_data['video_id']        = get_post_meta( $post_id, '_anwpfl_video_id', true );

			// Get extra Referees
			$game_data['assistant_1']       = get_post_meta( $post_id, '_anwpfl_assistant_1', true );
			$game_data['assistant_2']       = get_post_meta( $post_id, '_anwpfl_assistant_2', true );
			$game_data['referee_fourth_id'] = get_post_meta( $post_id, '_anwpfl_referee_fourth', true );

			$game_data['players'] = anwp_fl()->player->get_game_players( $game_data );

			$output[ $post_id ] = $game_data;
		}

		return empty( $output[ $post_id ] ) ? false : $output[ $post_id ];
	}

	/**
	 * Check maybe match can be live.
	 *
	 * @param WP_Post $post
	 *
	 * @return bool
	 * @throws Exception
	 * @since 0.9.5
	 */
	public function maybe_match_live( WP_Post $post ): bool {

		$game_data = anwp_fl()->match->get_game_data( $post->ID );

		if ( absint( $game_data['finished'] ) ) {
			return false;
		}

		if ( 'yes' === $post->_anwpfl_match_live_commentary ) {
			return true;
		}

		// Check live status is set
		if ( $post->_anwpfl_live_status ) {
			return true;
		}

		if ( '0000-00-00 00:00:00' !== $game_data['kickoff'] && ( 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_mode', '' ) || 'yes' === AnWPFL_Premium_API::get_config_value( 'live' ) ) ) {
			$match_date_obj = new DateTime( $game_data['kickoff'] );

			if ( $match_date_obj->sub( new DateInterval( 'PT2H' ) ) < new DateTime( current_time( 'mysql' ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get Match prediction advice.
	 *
	 * @param int  $match_id
	 * @param bool $cache
	 *
	 * @return string
	 * @since 0.10.0
	 */
	public function get_match_prediction_advice( int $match_id, bool $cache = false ): string {

		if ( 'yes' !== AnWPFL_Premium_API::get_config_value( 'predictions' ) ) {
			return '';
		}

		$advice = '';

		if ( $cache ) {
			global $wpdb;
			static $available_predictions = null;

			if ( null === $available_predictions ) {
				if ( apply_filters( 'anwpfl/prediction/show_in_finished', false ) ) {
					$available_predictions = $wpdb->get_results(
						"
							SELECT match_id, prediction_advice, prediction_advice_alt
							FROM $wpdb->anwpfl_predictions
						",
						OBJECT_K
					) ?: [];
				} else {
					$available_predictions = $wpdb->get_results(
						"
							SELECT p.match_id, p.prediction_advice, p.prediction_advice_alt
							FROM $wpdb->anwpfl_predictions p
							INNER JOIN $wpdb->anwpfl_matches AS m ON p.match_id = m.match_id
							WHERE m.finished = 0
						",
						OBJECT_K
					) ?: [];
				}
			}

			if ( ! empty( $available_predictions[ $match_id ] ) ) {
				$advice = $available_predictions[ $match_id ]->prediction_advice_alt ?: $available_predictions[ $match_id ]->prediction_advice;
			}
		} else {
			$prediction_data = $this->get_prediction_data( $match_id );

			if ( ! empty( $prediction_data ) ) {
				$advice = $prediction_data['prediction_advice_alt'] ?: $prediction_data['prediction_advice'];
			}
		}

		return $this->translate_prediction_advice( $advice );
	}

	/**
	 * Translate prediction advice
	 *
	 * @param $advice
	 *
	 * @return string
	 * @since 0.14.4
	 */
	public function translate_prediction_advice( $advice ): string {

		static $translatable_words = null;

		if ( null === $translatable_words ) {

			$translatable_words    = [];
			$possible_translations = [
				'api_import__predictions__winner'                   => 'Winner',
				'api_import__predictions__double_chance'            => 'Double chance',
				'api_import__predictions__draw'                     => 'draw',
				'api_import__predictions__or'                       => 'or',
				'api_import__predictions__and'                      => 'and',
				'api_import__predictions__combo_double_chance'      => 'Combo Double chance',
				'api_import__predictions__no_predictions_available' => 'No predictions available',
				'api_import__predictions__goals'                    => 'goals',
				'api_import__predictions__plus'                     => '+',
				'api_import__predictions__minus'                    => '-',
			];

			foreach ( $possible_translations as $possible_key => $translated_text ) {
				if ( trim( AnWPFL_Text::get_value( $possible_key ) ) ) {
					$translatable_words[ $translated_text ] = AnWPFL_Text::get_value( $possible_key );
				}
			}
		}

		foreach ( $translatable_words as $key => $translation ) {
			$key    = in_array( $key, [ 'draw', 'goals', '+', '-' ], true ) ? ' ' . $key : $key;
			$key    = in_array( $key, [ 'or', 'and' ], true ) ? ' ' . $key . ' ' : $key;
			$advice = str_ireplace( $key, ' ' . $translation . ' ', $advice );
		}

		return $advice;
	}

	/**
	 * Render prediction in the game list
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function add_prediction_match_list( array $data ) {

		if ( 'yes' !== AnWPFL_Premium_API::get_config_value( 'prediction_show_bottom_line' ) || ( absint( $data['finished'] ) && ! apply_filters( 'anwpfl/prediction/show_in_finished', false ) ) ) {
			return;
		}

		$prediction_advice = anwp_fl_pro()->match->get_match_prediction_advice( $data['match_id'], true );

		if ( ! $prediction_advice ) {
			return;
		}

		ob_start();
		?>
		<div class="anwp-fl-game__prediction match-slim__prediction anwp-text-xs anwp-text-center mt-1">
			<span class="match-slim__prediction-term">
				<?php echo esc_html( AnWPFL_Text::get_value( 'match__match__prediction', __( 'Prediction', 'anwp-football-leagues-premium' ) ) ); ?>:
			</span>
			<span class="match-slim__prediction-value"><?php echo esc_html( $prediction_advice ); ?></span>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Render prediction in the game list
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function add_prediction_match_super_slim_list( array $data ) {

		if ( 'yes' !== AnWPFL_Premium_API::get_config_value( 'prediction_show_bottom_line' ) || ( absint( $data['finished'] ) && ! apply_filters( 'anwpfl/prediction/show_in_finished', false ) ) ) {
			return;
		}

		$prediction_advice = anwp_fl_pro()->match->get_match_prediction_advice( $data['match_id'], true );

		if ( ! $prediction_advice ) {
			return;
		}

		ob_start();
		?>
		<div class="anwp-fl-game__prediction match-slim__prediction anwp-text-xs anwp-text-center mt-1">
			<span class="match-slim__prediction-term">
				<?php echo esc_html( AnWPFL_Text::get_value( 'match__match__prediction', __( 'Prediction', 'anwp-football-leagues-premium' ) ) ); ?>:
			</span>
			<span class="match-slim__prediction-value"><?php echo esc_html( $prediction_advice ); ?></span>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Render prediction in the game header
	 *
	 * @param       $match_id
	 * @param array $data
	 *
	 * @return void
	 */
	public function render_match_header_prediction( $match_id, array $data ) {

		$prediction_advice = ( absint( $data['finished'] ) && ! apply_filters( 'anwpfl/prediction/show_in_finished', false ) ) ? '' : anwp_fl_pro()->match->get_match_prediction_advice( $match_id );

		if ( empty( $prediction_advice ) ) {
			return;
		}

		ob_start();
		?>
		<div class="anwp-text-center anwp-text-xs py-2 anwp-match-prediction-wrapper">
			<span class="anwp-font-semibold mr-1"><?php echo esc_html( AnWPFL_Text::get_value( 'match__match__prediction', __( 'Prediction', 'anwp-football-leagues-premium' ) ) ); ?>: </span>
			<?php echo esc_html( $prediction_advice ); ?>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Get game temporary players
	 *
	 * @param $game_id
	 *
	 * @return array
	 * @since 0.14.2
	 */
	public function get_temp_players( $game_id ) {

		if ( method_exists( anwp_football_leagues()->match, 'get_temp_players' ) ) {
			return anwp_football_leagues()->match->get_temp_players( $game_id );
		}

		return [];
	}

	/**
	 * Get odds table
	 *
	 * @return string
	 * @since 0.14.3
	 */
	public function get_match_odds_table( $odds_data ) {

		$table_columns = [];
		$max_values    = [];

		$active_books = AnWPFL_Premium_API::get_config_value( 'books', [] );

		foreach ( $odds_data['bookmakers'] as $b_key => $b_values ) {
			if ( ! in_array( absint( $b_key ), $active_books, true ) ) {
				continue;
			}

			$odds_data['bookmakers'][ $b_key ] = [];

			foreach ( $b_values as $b_value ) {

				$odds_data['bookmakers'][ $b_key ][ $b_value['value'] ] = $b_value['odd'];

				if ( ! isset( $max_values[ $b_value['value'] ] ) ) {
					$max_values[ $b_value['value'] ] = 0;
				}

				if ( $max_values[ $b_value['value'] ] < $b_value['odd'] ) {
					$max_values[ $b_value['value'] ] = $b_value['odd'];
				}

				if ( in_array( $b_value['value'], $table_columns, true ) ) {
					continue;
				}

				$max_values[ $b_value['value'] ] = $b_value['odd'];

				$table_columns[] = $b_value['value'];
			}
		}

		if ( empty( $table_columns ) ) {
			ob_start();

			anwp_fl()->load_partial(
				[
					'no_data_text' => AnWPFL_Text::get_value( 'club__transfers__no_data', __( 'No data', 'anwp-football-leagues-premium' ) ),
					'class'        => 'mt-2',
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		$odds_clickable = 'yes' === AnWPFL_Premium_API::get_config_value( 'odds_clickable' );
		$bookmakers     = anwp_football_leagues_premium()->match->get_bookmakers();

		ob_start();
		?>
		<table class="anwp-border-0 anwp-text-xs mt-2 w-100 odds__table">
			<thead>
			<tr class="anwp-fl-border-bottom anwp-border-light">
				<th class="anwp-border-0"></th>
				<?php foreach ( $table_columns as $table_column ) : ?>
					<th class="anwp-border-0 anwp-text-center"><?php echo esc_html( $table_column ); ?></th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $odds_data['bookmakers'] as $book_id => $book_data ) :

				if ( ! in_array( absint( $book_id ), $active_books, true ) ) {
					continue;
				}

				$aff_link = isset( AnWPFL_Premium_API::get_config_value( 'aff_links', [] )[ absint( $book_id ) ] ) ? AnWPFL_Premium_API::get_config_value( 'aff_links', [] )[ absint( $book_id ) ] : '';
				$load_alt = apply_filters( 'anwpfl/bookmaker/alternative_affiliate_link', '', $aff_link, $bookmakers[ $book_id ] );
				?>
				<tr class="anwp-fl-border-bottom anwp-border-light">
					<td class="anwp-border-0 anwp-text-left anwp-w-min-100">
						<?php
						if ( $aff_link && $load_alt ) :
							echo $load_alt; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						elseif ( $aff_link ) :
							?>
							<a href="<?php echo esc_url( do_shortcode( $aff_link ) ); ?>" target="_blank" class="anwp-link-without-effects">
								<img loading="lazy" src="<?php echo esc_url( AnWP_Football_Leagues_Premium::url( 'public/img/bk/' . $bookmakers[ $book_id ]['img'] ) ); ?>" class="anwp-w-120 anwp-object-contain anwp-h-20" alt="<?php echo esc_attr( $bookmakers[ $book_id ]['img'] ); ?>">
							</a>
						<?php else : ?>
							<img loading="lazy" src="<?php echo esc_url( AnWP_Football_Leagues_Premium::url( 'public/img/bk/' . $bookmakers[ $book_id ]['img'] ) ); ?>" class="anwp-w-120 anwp-object-contain anwp-h-20" alt="<?php echo esc_attr( $bookmakers[ $book_id ]['img'] ); ?>">
						<?php endif; ?>
					</td>
					<?php foreach ( $table_columns as $table_column ) : ?>
						<?php if ( empty( $book_data[ $table_column ] ) ) : ?>
							<td class="anwp-border-0"></td>
						<?php else : ?>
							<td class="anwp-text-center anwp-border-0 <?php echo esc_attr( isset( $max_values[ $table_column ] ) && $max_values[ $table_column ] === $book_data[ $table_column ] ? 'anwp-bg-success-lightest' : '' ); ?>">
								<?php if ( $odds_clickable && $aff_link ) : ?>
									<a href="<?php echo esc_url( do_shortcode( $aff_link ) ); ?>" target="_blank" class="anwp-link-without-effects">
										<?php echo esc_html( $book_data[ $table_column ] ); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html( $book_data[ $table_column ] ); ?>
								<?php endif; ?>
							</td>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get Bookmakers data
	 *
	 * @return array
	 * @since 0.14.4
	 */
	public function get_bookmaker_options() {

		$options = [];

		foreach ( $this->get_bookmakers() as $book_id => $book_data ) {
			$options[ $book_id ] = $book_data['name'];
		}

		asort( $options, SORT_NATURAL );

		return $options;
	}

	/**
	 * Get Bookmakers data
	 *
	 * @return array
	 * @since 0.15.4
	 */
	public function get_bookmaker_list() {

		$options = [];

		foreach ( $this->get_bookmakers() as $book_id => $book_data ) {
			$options[] = [
				'id'   => $book_id,
				'name' => $book_data['name'],
			];
		}

		return wp_list_sort( $options, 'name' );
	}

	/**
	 * Get Bookmakers data
	 *
	 * @return array
	 * @since 0.14.3
	 */
	public function get_bookmakers() {

		return [
			1  => [
				'name' => '10Bet',
				'img'  => '10bet.svg',
			],
			2  => [
				'name' => 'Marathonbet',
				'img'  => 'marathon.png',
			],
			3  => [
				'name' => 'Betfair',
				'img'  => 'betfair.png',
			],
			4  => [
				'name' => 'Pinnacle',
				'img'  => 'pinnacle.png',
			],
			5  => [
				'name' => 'SBO',
				'img'  => 'sbobet.png',
			],
			6  => [
				'name' => 'Bwin',
				'img'  => 'bwin.png',
			],
			7  => [
				'name' => 'William Hill',
				'img'  => 'willam-hill.png',
			],
			8  => [
				'name' => 'Bet365',
				'img'  => 'bet365.png',
			],
			9  => [
				'name' => 'Dafabet',
				'img'  => 'databet.png',
			],
			10 => [
				'name' => 'Ladbrokes',
				'img'  => 'ladbrokes.png',
			],
			11 => [
				'name' => '1xBet',
				'img'  => '1xbet.png',
			],
			12 => [
				'name' => 'BetFred',
				'img'  => 'betfred.png',
			],
			13 => [
				'name' => '188Bet',
				'img'  => '188bet.png',
			],
			15 => [
				'name' => 'Interwetten',
				'img'  => 'interwetten.png',
			],
			16 => [
				'name' => 'Unibet',
				'img'  => 'unibet.png',
			],
			17 => [
				'name' => '5Dimes',
				'img'  => '5Dimes.png',
			],
			18 => [
				'name' => 'Intertops',
				'img'  => '',
			],
			19 => [
				'name' => 'Bovada',
				'img'  => 'Intertops.png',
			],
			20 => [
				'name' => 'Betcris',
				'img'  => 'Betcris.png',
			],
			21 => [
				'name' => '888Sport',
				'img'  => '888Sport.png',
			],
			22 => [
				'name' => 'Tipico',
				'img'  => 'Tipico.png',
			],
			23 => [
				'name' => 'Sportingbet',
				'img'  => 'Sportingbet.png',
			],
			24 => [
				'name' => 'Betway',
				'img'  => 'Betway.png',
			],
			25 => [
				'name' => 'Expekt',
				'img'  => 'Expekt.png',
			],
			26 => [
				'name' => 'Betsson',
				'img'  => 'Betsson.png',
			],
			27 => [
				'name' => 'NordicBet',
				'img'  => 'NordicBet.png',
			],
			28 => [
				'name' => 'ComeOn',
				'img'  => 'ComeOn.png',
			],
			30 => [
				'name' => 'Netbet',
				'img'  => 'Netbet.png',
			],
		];
	}

	/**
	 * Get Available layouts for the shortcode Matches
	 *
	 * @param $layouts array
	 *
	 * @return array
	 * @since 0.14.8
	 */
	public function matches_available_layouts( $layouts ) {

		return array_merge( $layouts, [ 'plain' ] );
	}

	/**
	 * Get Available layouts for the shortcode Matches
	 *
	 * @param $events array
	 *
	 * @return string
	 * @since 0.14.18
	 */
	public function get_formation_event_icons( $events, $filter = '', $single_event = false ) {

		static $event_icons = null;

		if ( null === $event_icons ) {
			$event_icons = anwp_football_leagues()->data->get_event_icons();

			$event_icons['subs_in']  = '<svg class="icon__subs-alt icon--lineups"><use xlink:href="#icon-subs-alt"></use></svg>';
			$event_icons['subs_out'] = '<svg class="icon__subs-alt icon--lineups"><use xlink:href="#icon-subs-alt"></use></svg>';
		}

		$html_output = '';

		if ( ! empty( $events ) ) {
			foreach ( $events as $evt ) {
				if ( 0 === mb_stripos( $evt['type'], $filter ) ) {
					if ( $single_event ) {
						$html_output = $event_icons[ $evt['type'] ] ?? '';
					} else {
						$html_output .= $event_icons[ $evt['type'] ] ?? '';
					}
				}
			}
		}

		return $html_output;
	}

	/**
	 * Get formation data.
	 *
	 * @param int $game_id
	 *
	 * @return array [
	 *  "home_club_shirt" => '',
	 *  "away_club_shirt" => '',
	 *  "formation" => "",
	 *  "formation_extra" => "",
	 *  ]
	 * @since 0.16.0
	 */
	public function get_formation_data( int $game_id ): array {

		static $output = [];

		if ( isset( $output[ $game_id ] ) ) {
			return $output[ $game_id ];
		}

		global $wpdb;

		$output[ $game_id ] = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT `home_club_shirt`, `away_club_shirt`, `formation`, `formation_extra`
				FROM {$wpdb->prefix}anwpfl_formations
				WHERE match_id = %d
				",
				$game_id
			),
			ARRAY_A
		) ?: [];

		return $output[ $game_id ];
	}

	/**
	 * Get prediction data.
	 *
	 * @param int $game_id
	 *
	 * @return array [
	 *  "prediction_advice" => '',
	 *  "prediction_percent" => '',
	 *  "prediction_comparison" => "",
	 *  "prediction_advice_alt" => "",
	 *  ]
	 * @since 0.16.0
	 */
	public function get_prediction_data( int $game_id ): array {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT `prediction_advice`, `prediction_percent`, `prediction_comparison`, `prediction_advice_alt`
				FROM $wpdb->anwpfl_predictions
				WHERE match_id = %d
				",
				$game_id
			),
			ARRAY_A
		) ? : [];
	}
}
