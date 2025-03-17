<?php
/**
 * AnWP Football Leagues Premium :: Data.
 *
 * @since   0.6.0
 * @package AnWP_Football_Leagues_Premium
 */

class AnWPFL_Premium_Data {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Admin strings for localization.
	 *
	 * @var    - Array of strings
	 * @since  0.6.0
	 */
	private $admin_l10n = [];

	/**
	 * Constructor.
	 *
	 * @since  0.1.0
	 *
	 * @param  AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;

		$this->admin_l10n = [
			'_1_st_half'                         => esc_html__( '1st Half', 'anwp-football-leagues-premium' ),
			'_2_nd_half'                         => esc_html__( '2nd Half', 'anwp-football-leagues-premium' ),
			'automatic_data_filling'             => esc_html__( 'Automatic data filling', 'anwp-football-leagues-premium' ),
			'automatic'                          => esc_html__( 'automatic', 'anwp-football-leagues-premium' ),
			'bracket_layout'                     => esc_html__( 'Bracket Layout', 'anwp-football-leagues-premium' ),
			'cancelled_goal'                     => esc_html__( 'Cancelled goal', 'anwp-football-leagues-premium' ),
			'comment'                            => esc_html__( 'Comment', 'anwp-football-leagues-premium' ),
			'conference'                         => esc_html__( 'Conference', 'anwp-football-leagues-premium' ),
			'conferences_support'                => esc_html__( 'Conferences Support', 'anwp-football-leagues-premium' ),
			'current_matchweek'                  => esc_html__( 'Current MatchWeek', 'anwp-football-leagues-premium' ),
			'display_options'                    => esc_html__( 'Display Options', 'anwp-football-leagues-premium' ),
			'edit_mode'                          => esc_html__( 'Edit Mode', 'anwp-football-leagues-premium' ),
			'empty_placeholder'                  => esc_html__( 'empty placeholder', 'anwp-football-leagues-premium' ),
			'event_comment'                      => esc_html__( 'Event Comment', 'anwp-football-leagues-premium' ),
			'extra_time'                         => esc_html__( 'Extra Time', 'anwp-football-leagues-premium' ),
			'finished'                           => esc_html__( 'Finished', 'anwp-football-leagues-premium' ),
			'friendly_not_calculated'            => esc_html__( 'In "Friendly" status player statistics is NOT calculated', 'anwp-football-leagues-premium' ),
			'for_not_finished_only'              => esc_html__( 'For not finished only', 'anwp-football-leagues-premium' ),
			'formations'                         => esc_html__( 'Formations', 'anwp-football-leagues-premium' ),
			'full_time'                          => esc_html__( 'Full Time', 'anwp-football-leagues-premium' ),
			'go_to_the_documentation'            => esc_html__( 'Go to the Documentation', 'anwp-football-leagues-premium' ),
			'half_time'                          => esc_html__( 'Half Time', 'anwp-football-leagues-premium' ),
			'hide'                               => esc_html__( 'hide', 'anwp-football-leagues-premium' ),
			'inherit_from_settings'              => esc_html__( 'inherit (from settings)', 'anwp-football-leagues-premium' ),
			'layout_mini_widget'                 => esc_html__( 'Layout Mini (Widget)', 'anwp-football-leagues-premium' ),
			'live'                               => esc_html__( 'Live', 'anwp-football-leagues-premium' ),
			'live_status'                        => esc_html__( 'Live status', 'anwp-football-leagues-premium' ),
			'matrix_results'                     => esc_html__( 'Matrix Results', 'anwp-football-leagues-premium' ),
			'manual'                             => esc_html__( 'manual', 'anwp-football-leagues-premium' ),
			'not_set'                            => esc_html__( 'not set', 'anwp-football-leagues-premium' ),
			'offset_in_minutes'                  => esc_html__( 'offset in minutes', 'anwp-football-leagues-premium' ),
			'penalty'                            => esc_html__( 'Penalty', 'anwp-football-leagues-premium' ),
			'player_not_in_squad'                => esc_html__( '! player not in squad', 'anwp-football-leagues-premium' ),
			'ranking_rules_notes_pro_2'          => esc_html__( 'Only works if Automatic Position Calculation is set to "YES"', 'anwp-football-leagues-premium' ),
			'ranking_rules_notes_pro_3'          => esc_html__( 'The following criteria are applied in the order from top to bottom.', 'anwp-football-leagues-premium' ),
			'saving_data_error'                  => esc_html__( 'Saving data error', 'anwp-football-leagues-premium' ),
			'scores'                             => esc_html__( 'Scores', 'anwp-football-leagues-premium' ),
			'set_team_formation'                 => esc_html__( 'Set team formation from left to right', 'anwp-football-leagues-premium' ),
			'show_below_standing'                => esc_html__( 'show below Standing', 'anwp-football-leagues-premium' ),
			'significant_event'                  => esc_html__( 'Significant event', 'anwp-football-leagues-premium' ),
			'sorting_minute'                     => esc_html__( 'Sorting minute', 'anwp-football-leagues-premium' ),
			'sorting_minute_hint'                => esc_html__( 'Used for sorting when minute field is empty. Useful for pre or post match comments. Enter additional minutes separated by "+". Example: "90+9", "-5"', 'anwp-football-leagues-premium' ),
			'table_columns_order_and_visibility' => esc_html__( 'Table columns (order and visibility)', 'anwp-football-leagues-premium' ),
			'update'                             => esc_html__( 'Update', 'anwp-football-leagues-premium' ),
			'bracket_options'                    => esc_html__( 'Bracket Options', 'anwp-football-leagues-premium' ),
			'sorting_index'                      => esc_html__( 'Sorting Index', 'anwp-football-leagues-premium' ),
			'click_to_edit'                      => esc_html__( 'Click to edit', 'anwp-football-leagues-premium' ),
			'team_a_placeholder'                 => esc_html__( 'Team A (placeholder)', 'anwp-football-leagues-premium' ),
			'team_b_placeholder'                 => esc_html__( 'Team B (placeholder)', 'anwp-football-leagues-premium' ),

			// FrontEnd LIVE edit ToDo make translatable
			'live_status_and_offset'             => esc_html__( 'LIVE Status and Offset', 'anwp-football-leagues-premium' ),
			'current_live_status'                => esc_html__( 'Current LIVE status', 'anwp-football-leagues-premium' ),
			'current_minute'                     => esc_html__( 'Current minute', 'anwp-football-leagues-premium' ),
			'current_scores'                     => esc_html__( 'Current Scores', 'anwp-football-leagues-premium' ),
			'set_new_status'                     => esc_html__( 'Set new status', 'anwp-football-leagues-premium' ),
			'add_new_event'                      => esc_html__( 'Add New Event', 'anwp-football-leagues-premium' ),
			'refresh_events'                     => esc_html__( 'Refresh Events', 'anwp-football-leagues-premium' ),
			'event_edit_mode'                    => esc_html__( 'Event Edit Mode', 'anwp-football-leagues-premium' ),
			'edit_only_associated_your_club'     => esc_html__( 'You can edit only data associated with your club', 'anwp-football-leagues-premium' ),
		];

		$this->hooks();
	}

	/**
	 * Initiate hooks.
	 */
	public function hooks() {

		add_filter( 'cron_schedules', [ $this, 'add_cron_minute_interval' ] );

		add_action( 'wp_ajax_anwp_fl_import_matches', [ $this, 'get_data_for_import_matches' ] );
		add_action( 'wp_ajax_anwp_fl_import_matches_save', [ $this, 'save_data_for_import_matches' ] );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );

		add_filter( 'nav_menu_item_title', [ $this, 'nav_menu_item_title_icon' ] );
	}

	/**
	 * Add special Home icon to the menu.
	 *
	 * @param $title
	 *
	 * @return array|mixed|string|string[]
	 */
	public function nav_menu_item_title_icon( $title ) {
		if ( strpos( $title, '[anwp-fl-home-icon]' ) !== false ) {
			$title = str_ireplace( '[anwp-fl-home-icon]', '<svg class="anwp-icon anwp-icon--s18 anwp-icon--feather anwp-opacity-80"><use xlink:href="#icon-home"></use></svg>', $title );
		}

		return $title;
	}

	/**
	 * Register REST routes.
	 */
	public function add_rest_routes() {
		register_rest_route(
			'anwpfl/v1',
			'/addons/toggle-addon',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'toggle_addon' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Callback for the rest route
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public function toggle_addon( WP_REST_Request $request ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Access Denied !!!', [ 'status' => 400 ] );
		}

		// Get Request params
		$params = $request->get_params();

		if ( ! isset( $params['addon'] ) ) {
			return new WP_Error( 'rest_invalid', 'Incorrect Data', [ 'status' => 400 ] );
		}

		$addon_name  = sanitize_text_field( $params['addon'] );
		$addon_value = isset( $params['switch'] ) ? sanitize_text_field( $params['switch'] ) : '';

		$sl_addons = wp_parse_args(
			get_option( 'anwp-fl-addons', [] ),
			[
				'megamenu' => '',
				'sidebars' => '',
			]
		);

		if ( isset( $sl_addons[ $addon_name ] ) ) {
			$sl_addons[ $addon_name ] = $addon_value;
		}

		update_option( 'anwp-fl-addons', $sl_addons );

		return rest_ensure_response( [] );
	}

	/**
	 * Check if addon is active
	 * @param $addon_name
	 *
	 * @return bool
	 */
	public function is_addon_active( $addon_name ) {

		static $sl_addons = null;

		if ( null === $sl_addons ) {
			$sl_addons = wp_parse_args(
				get_option( 'anwp-fl-addons', [] ),
				[
					'megamenu' => '',
					'sidebars' => '',
				]
			);
		}

		return isset( $sl_addons[ $addon_name ] ) && 'yes' === $sl_addons[ $addon_name ];
	}

	/**
	 * Add special cron interval.
	 *
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public function add_cron_minute_interval( $schedules ) {
		$schedules['anwp-fl-every-minute'] = [
			'interval' => 60,
			'display'  => 'Every Minute',
		];

		$schedules['anwp-fl-every-10-minutes'] = [
			'interval' => 600,
			'display'  => 'Every Ten Minutes',
		];

		$schedules['anwp-fl-every-4-hours'] = [
			'interval' => HOUR_IN_SECONDS * 4,
			'display'  => 'Every 4 Hours',
		];

		return $schedules;
	}

	/**
	 * Get groups/rounds with clubs for import matches tool.
	 *
	 * @since 0.9.3
	 */
	public function get_data_for_import_matches() {
		check_ajax_referer( 'anwp-fl-import-matches' );

		$competition_id = absint( $_POST['competition'] );

		if ( ! $competition_id ) {
			wp_send_json_error();
		}

		// The user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$competition_type = get_post_meta( $competition_id, '_anwpfl_type', true );

		if ( ! in_array( $competition_type, [ 'knockout', 'round-robin' ], true ) ) {
			wp_send_json_error();
		}

		$output = [
			'html'       => '',
			'type'       => $competition_type,
			'clubs'      => [],
			'clubs_html' => [],
		];

		$groups = get_post_meta( $competition_id, '_anwpfl_groups', true );

		if ( empty( $groups ) ) {
			wp_send_json_error();
		}

		$groups = json_decode( $groups );

		if ( 'round-robin' === $competition_type ) {
			ob_start();
			?>
			<label for="anwp-selector-group" class="d-block"><?php echo esc_html__( 'Group', 'anwp-football-leagues' ); ?></label>
			<select id="anwp-selector-group">
				<option value=""><?php echo esc_html__( '- select group -', 'anwp-football-leagues-premium' ); ?></option>
				<?php foreach ( $groups as $group ) : ?>
					<option value="<?php echo esc_attr( $group->id ); ?>"><?php echo esc_html( $group->title ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php
			$output['html'] = ob_get_clean();

			foreach ( $groups as $group ) {
				if ( ! empty( $group->clubs ) && is_array( $group->clubs ) ) {

					// Start HTML code
					$output['clubs_html'][ $group->id ] = '<div class="p-3"><h3 class="mt-0 mb-2">' . esc_html__( 'Available clubs', 'anwp-football-leagues-premium' ) . '</h3>';

					// Populate club arrays
					foreach ( $group->clubs as $club ) {
						// Simple array of clubs
						$output['clubs'][ $group->id ][] = anwp_football_leagues()->club->get_club_title_by_id( $club );

						// HTML output
						$output['clubs_html'][ $group->id ] .= '<span class="mr-3">' . anwp_football_leagues()->club->get_club_title_by_id( $club ) . '</span><br>';
					}

					// Finish HTML code
					$output['clubs_html'][ $group->id ] .= '</div>';
				}
			}
		} else {

			$rounds = get_post_meta( $competition_id, '_anwpfl_rounds', true );

			if ( empty( $rounds ) ) {
				wp_send_json_error();
			}

			$rounds = json_decode( $rounds );
			ob_start();
			?>
			<label for="anwp-selector-group" class="d-block"><?php echo esc_html__( 'Round', 'anwp-football-leagues' ); ?></label>
			<select id="anwp-selector-group">
				<option value=""><?php echo esc_html__( '- select round -', 'anwp-football-leagues-premium' ); ?></option>
				<?php foreach ( $rounds as $round ) : ?>
					<option value="<?php echo esc_attr( $round->id ); ?>"><?php echo esc_html( $round->title ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php
			$output['html'] = ob_get_clean();

			foreach ( $groups as $group ) {
				if ( ! empty( $group->clubs ) && is_array( $group->clubs ) ) {
					foreach ( $group->clubs as $club ) {
						// Simple array of clubs
						$output['clubs'][ $group->round ][] = anwp_football_leagues()->club->get_club_title_by_id( $club );

						// HTML output
						$output['clubs_html'][ $group->round ][] = '<span class="mr-3">' . anwp_football_leagues()->club->get_club_title_by_id( $club ) . '</span><br>';
					}
				}
			}

			foreach ( $rounds as $round ) {
				$output['clubs_html'][ $round->id ] = '<div class="p-3"><h3 class="mt-0 mb-2">Available clubs</h3>' . implode( '', $output['clubs_html'][ $round->id ] ) . '</div>';
			}
		}

		wp_send_json_success( $output );
	}

	/**
	 * Try to save import matches.
	 *
	 * @since 0.9.3
	 */
	public function save_data_for_import_matches() {

		/*
		|--------------------------------------------------------------------
		| Validate and prepare initial data
		|--------------------------------------------------------------------
		*/
		check_ajax_referer( 'anwp-fl-import-matches' );

		$post_data = wp_unslash( $_POST );

		$competition_id  = absint( $post_data['competition'] );
		$post_group_id   = absint( $post_data['group'] );
		$competition_obj = anwp_fl()->competition->get_competition( $competition_id );

		if ( ! $competition_id || ! $post_group_id || ! $competition_obj ) {
			wp_send_json_error();
		}

		// The user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		if ( ! in_array( $competition_obj->type, [ 'knockout', 'round-robin' ], true ) ) {
			wp_send_json_error();
		}

		$table = $post_data['table'];

		if ( empty( $table ) || ! is_array( $table ) ) {
			wp_send_json_error();
		}

		/*
		|--------------------------------------------------------------------
		| Try to Save
		|--------------------------------------------------------------------
		*/
		try {

			// Init counter
			$updated = 0;

			// Prepare data
			$clubs  = [];
			$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

			if ( empty( $groups ) ) {
				wp_send_json_error();
			}

			if ( 'round-robin' === $competition_obj->type ) {
				foreach ( $groups as $group ) {
					if ( absint( $post_group_id ) === absint( $group->id ) && ! empty( $group->clubs ) && is_array( $group->clubs ) ) {
						foreach ( $group->clubs as $club_id ) {
							$club_title = anwp_fl()->club->get_club_title_by_id( $club_id );

							if ( ! empty( $club_title ) ) {
								$clubs[ $club_title ] = $club_id;
							}
						}
					}
				}
			} elseif ( 'knockout' === $competition_obj->type ) {
				foreach ( $groups as $group ) {
					if ( absint( $post_group_id ) === absint( $group->round ) && ! empty( $group->clubs ) && is_array( $group->clubs ) ) {
						foreach ( $group->clubs as $club_id ) {
							$club_title = anwp_fl()->club->get_club_title_by_id( $club_id );

							if ( ! empty( $club_title ) ) {
								$clubs[ $club_title ] = $club_id;
							}
						}
					}
				}
			}

			/*
			|--------------------------------------------------------------------
			| Prepare Stadiums
			|--------------------------------------------------------------------
			*/
			$stadiums = [];

			if ( ! empty( $clubs ) && is_array( $clubs ) ) {
				foreach ( $clubs as $club_id ) {
					$stadium_id = get_post_meta( $club_id, '_anwpfl_stadium', true );

					if ( absint( $stadium_id ) ) {
						$stadiums[ $club_id ] = absint( $stadium_id );
					}
				}
			}

			foreach ( $table as $match_post ) {
				if ( empty( $match_post[0] ) || empty( $match_post[1] ) || empty( $clubs[ $match_post[0] ] ) || empty( $clubs[ $match_post[1] ] ) ) {
					continue;
				}

				$round_id = 0;

				$game_data = [
					'competition_id' => $competition_id,
					'main_stage_id'  => 'secondary' === $competition_obj->multistage ? $competition_obj->multistage_main : 0,
					'season_id'      => intval( $competition_obj->season_ids ),
					'league_id'      => intval( $competition_obj->league_id ),
					'home_club'      => absint( $clubs[ $match_post[0] ] ),
					'away_club'      => absint( $clubs[ $match_post[1] ] ),
				];

				$game_data['stadium_id'] = $stadiums[ $game_data['home_club'] ] ?? '';

				// Get group ID & aggregate text
				if ( 'round-robin' === $competition_obj->type ) {
					$game_data['group_id'] = $post_group_id;
					$game_data['aggtext']  = isset( $match_post[8] ) ? sanitize_text_field( $match_post[8] ) : '';
				} else {
					$game_data['aggtext'] = isset( $match_post[7] ) ? sanitize_text_field( $match_post[7] ) : '';

					foreach ( $groups as $group ) {
						if ( absint( $post_group_id ) === absint( $group->round ) ) {
							if ( in_array( $game_data['home_club'], $group->clubs, true ) && in_array( $game_data['away_club'], $group->clubs, true ) ) {
								$game_data['group_id'] = absint( $group->id );
								$round_id              = absint( $group->round );
								break;
							}
						}
					}
				}

				if ( empty( $game_data['group_id'] ) ) {
					continue;
				}

				// Home/Away goals
				$game_data['home_goals'] = sanitize_text_field( $match_post[2] );
				$game_data['away_goals'] = sanitize_text_field( $match_post[3] );

				if ( '' === $game_data['home_goals'] && '' === $game_data['away_goals'] ) {
					$game_data['finished'] = 0;
				} else {
					$game_data['finished'] = 1;
				}

				if ( 'round-robin' === $competition_obj->type ) {
					$game_data['home_goals_half'] = isset( $match_post[6] ) ? absint( $match_post[6] ) : 0;
					$game_data['away_goals_half'] = isset( $match_post[7] ) ? absint( $match_post[7] ) : 0;
				} else {
					$game_data['home_goals_half'] = isset( $match_post[5] ) ? absint( $match_post[5] ) : 0;
					$game_data['away_goals_half'] = isset( $match_post[6] ) ? absint( $match_post[6] ) : 0;
				}

				// Set MatchWeek for Round Robin competition
				if ( 'round-robin' === $competition_obj->type ) {
					$game_data['match_week'] = absint( $match_post[5] ?? 0 );
				} else {
					$game_data['match_week'] = absint( $round_id );
				}

				if ( ! empty( $match_post[4] ) ) {
					$match_date = DateTime::createFromFormat( 'Y-m-d H:i', sanitize_text_field( $match_post[4] . ( mb_strpos( $match_post[4], ':' ) ? '' : ' 00:00' ) ) );

					if ( $match_date && anwp_fl()->helper->validate_date( $match_date->format( 'Y-m-d H:i:s' ) ) ) {
						$game_data['kickoff'] = $match_date->format( 'Y-m-d H:i:s' );
					}
				}

				if ( 'friendly' === get_post_meta( $competition_id, '_anwpfl_competition_status', true ) ) {
					$game_data['game_status'] = 0;
				}

				// Insert empty match into the database.
				$game_id = wp_insert_post(
					[
						'post_type'   => 'anwp_match',
						'post_status' => 'publish',
					]
				);

				if ( empty( $game_id ) || ! absint( $game_id ) ) {
					continue;
				}

				$game_data['match_id'] = $game_id;
				anwp_fl()->match->insert( $game_data );

				// Update Match title and slug.
				$home_club_title = anwp_fl()->club->get_club_title_by_id( $game_data['home_club'] );
				$away_club_title = anwp_fl()->club->get_club_title_by_id( $game_data['away_club'] );

				if ( trim( AnWPFL_Options::get_value( 'match_title_generator' ) ) ) {
					$match_title = anwp_fl()->match->get_match_title_generated( $game_data, $home_club_title, $away_club_title );
				} else {
					$match_title_separator = AnWPFL_Options::get_value( 'match_title_separator', '-' );
					$match_title           = sanitize_text_field( $home_club_title . ' ' . $match_title_separator . ' ' . $away_club_title );
				}

				$match_slug = anwp_fl()->match->get_match_slug_generated( $game_data, $home_club_title, $away_club_title, get_post( $game_id ) );

				// Rename Match (title and slug)
				wp_update_post(
					[
						'ID'         => $game_id,
						'post_title' => $match_title,
						'post_name'  => $match_slug,
					]
				);

				$updated++;
			}

			if ( 'round-robin' === $competition_obj->type && $updated && ! empty( $game_id ) && absint( $game_id ) ) {
				anwp_fl()->standing->calculate_standing_prepare( $game_id );
			}

			wp_send_json_success( [ 'qty' => $updated ] );
		} catch ( Exception $e ) {

			wp_send_json_error();
		}
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.6.0
	 *
	 * @param  string $field Field to get.
	 *
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {

		if ( property_exists( $this, $field ) ) {
			return $this->$field;
		}

		throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
	}

	/**
	 * Getter for array of admin localization strings.
	 *
	 * @return array
	 * @since 0.6.0
	 */
	public function get_l10n_admin() {
		return $this->admin_l10n;
	}

	/**
	 * Array of localization strings for Vue Datepicker.
	 *
	 * @return array
	 * @since 0.12.3
	 */
	public function get_vue_datepicker_locale() {
		return method_exists( 'AnWPFL_Data', 'get_vue_datepicker_locale' ) ? anwp_football_leagues()->data->get_vue_datepicker_locale() : [];
	}

	/**
	 * Array of localization strings.
	 *
	 * @return array
	 * @since 0.5.7
	 */
	public function get_l10n_public() {

		global $wp_locale;

		return [
			'_live_cache_period'   => anwp_football_leagues_premium()->live->get_live_cache_period_prefix(),
			'_live_update_list'    => AnWPFL_Premium_Options::get_value( 'live_update_period_list', '' ),
			'_live_update_single'  => AnWPFL_Premium_Options::get_value( 'live_update_period_single', '' ),
			'_game_date_format'    => $this->convert_php_to_js_moment_format( anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M Y' ),
			'_game_date_format_v2' => $this->convert_php_to_js_moment_format( 'j M' ),
			'_game_date_format_v3' => $this->convert_php_to_js_moment_format( anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M' ),
			'_game_time_format'    => $this->convert_php_to_js_moment_format( anwp_football_leagues()->get_option_value( 'custom_match_time_format' ) ?: get_option( 'time_format' ) ),
			'_dayjs_locale'        => $this->get_dayjs_locale(),
			'_live_import_api'     => 'yes' === AnWPFL_Premium_API::get_config_value( 'live' ) ? 'yes' : '',
			'_live_import_manual'  => 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_mode' ) ? 'yes' : '',
			'ajax_url'             => admin_url( 'admin-ajax.php' ),
			'aria_sortAscending'   => esc_html_x( ': activate to sort column ascending', 'statistic table: aria_sortAscending', 'anwp-football-leagues-premium' ),
			'aria_sortDescending'  => esc_html_x( ': activate to sort column descending', 'anwp-football-leagues-premium' ),
			'colvis'               => esc_html_x( 'Column visibility', 'statistic table', 'anwp-football-leagues-premium' ),
			'emptyTable'           => esc_html_x( 'No data available in table', 'statistic table: emptyTable', 'anwp-football-leagues-premium' ),
			'first_day_week'       => absint( get_option( 'start_of_week' ) ),
			'info'                 => esc_html_x( 'Showing _START_ to _END_ of _TOTAL_ entries', 'statistic table: info', 'anwp-football-leagues-premium' ),
			'infoEmpty'            => esc_html_x( 'Showing 0 to 0 of 0 entries', 'statistic table: infoEmpty', 'anwp-football-leagues-premium' ),
			'infoFiltered'         => esc_html_x( '(filtered from _MAX_ total entries)', 'statistic table: infoFiltered', 'anwp-football-leagues-premium' ),
			'infoPostFix'          => '',
			'lengthMenu'           => esc_html_x( 'Show _MENU_ entries', 'statistic table: lengthMenu', 'anwp-football-leagues-premium' ),
			'live'                 => esc_html__( 'Live', 'anwp-football-leagues-premium' ),
			'live_scheduled'       => esc_html__( 'LIVE scheduled', 'anwp-football-leagues-premium' ),
			'live_sound_goal'      => anwp_football_leagues_premium()->live->get_live_sound_goal(),
			'loader'               => includes_url( 'js/tinymce/skins/lightgray/img/loader.gif' ),
			'loadingDataError'     => esc_html__( 'Loading data error! Please, reload page and try again.', 'anwp-football-leagues-premium' ),
			'loadingRecords'       => esc_html_x( 'Loading...', 'statistic table: loadingRecords', 'anwp-football-leagues-premium' ),
			'months'               => array_values( $wp_locale->month ),
			'monthsShort'          => array_values( $wp_locale->month_abbrev ),
			'paginate_first'       => esc_html_x( 'First', 'statistic table: paginate_first', 'anwp-football-leagues-premium' ),
			'paginate_last'        => esc_html_x( 'Last', 'statistic table: paginate_last', 'anwp-football-leagues-premium' ),
			'paginate_next'        => esc_html_x( 'Next', 'statistic table: paginate_next', 'anwp-football-leagues-premium' ),
			'paginate_previous'    => esc_html_x( 'Previous', 'statistic table: paginate_previous', 'anwp-football-leagues-premium' ),
			'processing'           => esc_html_x( 'Processing...', 'statistic table: processing', 'anwp-football-leagues-premium' ),
			'public_nonce'         => wp_create_nonce( 'anwpfl-public-nonce' ),
			'rest_root'            => esc_url_raw( rest_url() ),
			'rows_10'              => esc_html_x( '10 rows', 'statistic table', 'anwp-football-leagues-premium' ),
			'rows_25'              => esc_html_x( '25 rows', 'statistic table', 'anwp-football-leagues-premium' ),
			'rows_50'              => esc_html_x( '50 rows', 'statistic table', 'anwp-football-leagues-premium' ),
			'search'               => esc_html_x( 'Search:', 'statistic table: search', 'anwp-football-leagues-premium' ),
			'show__rows'           => esc_html_x( 'Show %d rows', 'statistic table: show__rows', 'anwp-football-leagues-premium' ), // phpcs:ignore
			'show_all'             => esc_html_x( 'Show all', 'statistic table', 'anwp-football-leagues-premium' ),
			'spinner_url'          => admin_url( '/images/spinner.gif' ),
			'user_auto_timezone'   => AnWPFL_Premium_Options::get_value( 'user_auto_timezone' ),
			'weekdays'             => array_values( $wp_locale->weekday ),
			'weekdaysShort'        => array_values( $wp_locale->weekday_abbrev ),
			'zeroRecords'          => esc_html_x( 'No matching records found', 'statistic table: zeroRecords', 'anwp-football-leagues-premium' ),
		];
	}

	/**
	 * Array of localization strings for DayJS.
	 *
	 * @return array
	 */
	public function get_dayjs_locale() {

		global $wp_locale;

		return [
			'weekStart'     => absint( get_option( 'start_of_week' ) ),
			'months'        => array_values( $wp_locale->month ),
			'monthsShort'   => array_values( $wp_locale->month_abbrev ),
			'weekdays'      => array_values( $wp_locale->weekday ),
			'weekdaysShort' => array_values( $wp_locale->weekday_abbrev ),
			'weekdaysMin'   => array_values( $wp_locale->weekday_abbrev ),
		];
	}

	/**
	 * Converts php DateTime format to Javascript Moment format.
	 * From - https://stackoverflow.com/a/55173613
	 *
	 * @param string $phpFormat
	 *
	 * @return string
	 */
	public function convert_php_to_js_moment_format( $phpFormat ) {
		$replacements = [
			'A' => 'A',      // for the sake of escaping below
			'a' => 'a',      // for the sake of escaping below
			'B' => '',       // Swatch internet time (.beats), no equivalent
			'c' => 'YYYY-MM-DD[T]HH:mm:ssZ', // ISO 8601
			'D' => 'ddd',
			'd' => 'DD',
			'e' => 'zz',     // deprecated since version 1.6.0 of moment.js
			'F' => 'MMMM',
			'G' => 'H',
			'g' => 'h',
			'H' => 'HH',
			'h' => 'hh',
			'I' => '',       // Daylight Saving Time? => moment().isDST();
			'i' => 'mm',
			'j' => 'D',
			'L' => '',       // Leap year? => moment().isLeapYear();
			'l' => 'dddd',
			'M' => 'MMM',
			'm' => 'MM',
			'N' => 'E',
			'n' => 'M',
			'O' => 'ZZ',
			'o' => 'YYYY',
			'P' => 'Z',
			'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ', // RFC 2822
			'S' => 'o',
			's' => 'ss',
			'T' => 'z',      // deprecated since version 1.6.0 of moment.js
			't' => '',       // days in the month => moment().daysInMonth();
			'U' => 'X',
			'u' => 'SSSSSS', // microseconds
			'v' => 'SSS',    // milliseconds (from PHP 7.0.0)
			'W' => 'W',      // for the sake of escaping below
			'w' => 'e',
			'Y' => 'YYYY',
			'y' => 'YY',
			'Z' => '',       // time zone offset in minutes => moment().zone();
			'z' => 'DDD',
		];

		// Converts escaped characters.
		foreach ( $replacements as $from => $to ) {
			$replacements[ '\\' . $from ] = '[' . $from . ']';
		}

		return strtr( $phpFormat, $replacements );
	}

	/**
	 * Get countries list.
	 *
	 * - https://api-football-v1.p.rapidapi.com/v2/countries
	 *
	 * @return array
	 * @since 0.9.6
	 */
	public function get_api_import_countries() {
		$options = [
			[
				'country' => '- all countries -',
				'code'    => '-all-',
			],
			[
				'country' => 'Albania',
				'code'    => 'AL',
			],
			[
				'country' => 'Algeria',
				'code'    => 'DZ',
			],
			[
				'country' => 'Andorra',
				'code'    => 'AD',
			],
			[
				'country' => 'Angola',
				'code'    => 'AO',
			],
			[
				'country' => 'Argentina',
				'code'    => 'AR',
			],
			[
				'country' => 'Armenia',
				'code'    => 'AM',
			],
			[
				'country' => 'Aruba',
				'code'    => 'AW',
			],
			[
				'country' => 'Australia',
				'code'    => 'AU',
			],
			[
				'country' => 'Austria',
				'code'    => 'AT',
			],
			[
				'country' => 'Azerbaidjan',
				'code'    => 'AZ',
			],
			[
				'country' => 'Bahrain',
				'code'    => 'BH',
			],
			[
				'country' => 'Bangladesh',
				'code'    => 'BD',
			],
			[
				'country' => 'Barbados',
				'code'    => 'BB',
			],
			[
				'country' => 'Belarus',
				'code'    => 'BY',
			],
			[
				'country' => 'Belgium',
				'code'    => 'BE',
			],
			[
				'country' => 'Belize',
				'code'    => 'BZ',
			],
			[
				'country' => 'Benin',
				'code'    => 'BJ',
			],
			[
				'country' => 'Bermuda',
				'code'    => 'BM',
			],
			[
				'country' => 'Bhutan',
				'code'    => 'BT',
			],
			[
				'country' => 'Bolivia',
				'code'    => 'BO',
			],
			[
				'country' => 'Bosnia',
				'code'    => 'BA',
			],
			[
				'country' => 'Botswana',
				'code'    => 'BW',
			],
			[
				'country' => 'Brazil',
				'code'    => 'BR',
			],
			[
				'country' => 'Bulgaria',
				'code'    => 'BG',
			],
			[
				'country' => 'Burkina-Faso',
				'code'    => 'BF',
			],
			[
				'country' => 'Burundi',
				'code'    => 'BI',
			],
			[
				'country' => 'Cambodia',
				'code'    => 'KH',
			],
			[
				'country' => 'Cameroon',
				'code'    => 'CM',
			],
			[
				'country' => 'Canada',
				'code'    => 'CA',
			],
			[
				'country' => 'Chile',
				'code'    => 'CL',
			],
			[
				'country' => 'China',
				'code'    => 'CN',
			],
			[
				'country' => 'Chinese-Taipei',
				'code'    => 'TW',
			],
			[
				'country' => 'Colombia',
				'code'    => 'CO',
			],
			[
				'country' => 'Congo-DR',
				'code'    => 'CG',
			],
			[
				'country' => 'Costa-Rica',
				'code'    => 'CR',
			],
			[
				'country' => 'Croatia',
				'code'    => 'HR',
			],
			[
				'country' => 'Curacao',
				'code'    => 'CW',
			],
			[
				'country' => 'Cyprus',
				'code'    => 'CY',
			],
			[
				'country' => 'Czech-Republic',
				'code'    => 'CZ',
			],
			[
				'country' => 'Denmark',
				'code'    => 'DK',
			],
			[
				'country' => 'Ecuador',
				'code'    => 'EC',
			],
			[
				'country' => 'Egypt',
				'code'    => 'EG',
			],
			[
				'country' => 'El-Salvador',
				'code'    => 'SV',
			],
			[
				'country' => 'England',
				'code'    => 'GB',
			],
			[
				'country' => 'Estonia',
				'code'    => 'EE',
			],
			[
				'country' => 'Ethiopia',
				'code'    => 'ET',
			],
			[
				'country' => 'Faroe-Islands',
				'code'    => 'FO',
			],
			[
				'country' => 'Fiji',
				'code'    => 'FJ',
			],
			[
				'country' => 'Finland',
				'code'    => 'FI',
			],
			[
				'country' => 'France',
				'code'    => 'FR',
			],
			[
				'country' => 'Georgia',
				'code'    => 'GE',
			],
			[
				'country' => 'Germany',
				'code'    => 'DE',
			],
			[
				'country' => 'Ghana',
				'code'    => 'GH',
			],
			[
				'country' => 'Greece',
				'code'    => 'GR',
			],
			[
				'country' => 'Guadeloupe',
				'code'    => 'GP',
			],
			[
				'country' => 'Guatemala',
				'code'    => 'GT',
			],
			[
				'country' => 'Guinea',
				'code'    => 'GN',
			],
			[
				'country' => 'Haiti',
				'code'    => 'HT',
			],
			[
				'country' => 'Honduras',
				'code'    => 'HN',
			],
			[
				'country' => 'Hong-Kong',
				'code'    => 'HK',
			],
			[
				'country' => 'Hungary',
				'code'    => 'HU',
			],
			[
				'country' => 'Iceland',
				'code'    => 'IS',
			],
			[
				'country' => 'India',
				'code'    => 'IN',
			],
			[
				'country' => 'Indonesia',
				'code'    => 'ID',
			],
			[
				'country' => 'Iran',
				'code'    => 'IR',
			],
			[
				'country' => 'Iraq',
				'code'    => 'IQ',
			],
			[
				'country' => 'Ireland',
				'code'    => 'IE',
			],
			[
				'country' => 'Israel',
				'code'    => 'IL',
			],
			[
				'country' => 'Italy',
				'code'    => 'IT',
			],
			[
				'country' => 'Ivory-Coast',
				'code'    => 'CI',
			],
			[
				'country' => 'Jamaica',
				'code'    => 'JM',
			],
			[
				'country' => 'Japan',
				'code'    => 'JP',
			],
			[
				'country' => 'Jordan',
				'code'    => 'JO',
			],
			[
				'country' => 'Kazakhstan',
				'code'    => 'KZ',
			],
			[
				'country' => 'Kenya',
				'code'    => 'KE',
			],
			[
				'country' => 'Kosovo',
				'code'    => 'XK',
			],
			[
				'country' => 'Kuwait',
				'code'    => 'KW',
			],
			[
				'country' => 'Kyrgyzstan',
				'code'    => 'KG',
			],
			[
				'country' => 'Laos',
				'code'    => 'LA',
			],
			[
				'country' => 'Latvia',
				'code'    => 'LV',
			],
			[
				'country' => 'Lebanon',
				'code'    => 'LB',
			],
			[
				'country' => 'Libya',
				'code'    => 'LY',
			],
			[
				'country' => 'Lithuania',
				'code'    => 'LT',
			],
			[
				'country' => 'Luxembourg',
				'code'    => 'LU',
			],
			[
				'country' => 'Macedonia',
				'code'    => 'MK',
			],
			[
				'country' => 'Malawi',
				'code'    => 'MW',
			],
			[
				'country' => 'Malaysia',
				'code'    => 'MY',
			],
			[
				'country' => 'Mali',
				'code'    => 'ML',
			],
			[
				'country' => 'Malta',
				'code'    => 'MT',
			],
			[
				'country' => 'Mexico',
				'code'    => 'MX',
			],
			[
				'country' => 'Moldova',
				'code'    => 'MD',
			],
			[
				'country' => 'Montenegro',
				'code'    => 'ME',
			],
			[
				'country' => 'Morocco',
				'code'    => 'MA',
			],
			[
				'country' => 'Myanmar',
				'code'    => 'MM',
			],
			[
				'country' => 'Namibia',
				'code'    => 'NA',
			],
			[
				'country' => 'Nepal',
				'code'    => 'NP',
			],
			[
				'country' => 'Netherlands',
				'code'    => 'NL',
			],
			[
				'country' => 'New-Zealand',
				'code'    => 'NZ',
			],
			[
				'country' => 'Nicaragua',
				'code'    => 'NI',
			],
			[
				'country' => 'Nigeria',
				'code'    => 'NG',
			],
			[
				'country' => 'Northern-Ireland',
				'code'    => 'GB',
			],
			[
				'country' => 'Norway',
				'code'    => 'NO',
			],
			[
				'country' => 'Oman',
				'code'    => 'OM',
			],
			[
				'country' => 'Palestine',
				'code'    => 'PS',
			],
			[
				'country' => 'Panama',
				'code'    => 'PA',
			],
			[
				'country' => 'Paraguay',
				'code'    => 'PY',
			],
			[
				'country' => 'Peru',
				'code'    => 'PE',
			],
			[
				'country' => 'Poland',
				'code'    => 'PL',
			],
			[
				'country' => 'Portugal',
				'code'    => 'PT',
			],
			[
				'country' => 'Qatar',
				'code'    => 'QA',
			],
			[
				'country' => 'Romania',
				'code'    => 'RO',
			],
			[
				'country' => 'Russia',
				'code'    => 'RU',
			],
			[
				'country' => 'Rwanda',
				'code'    => 'RW',
			],
			[
				'country' => 'San-Marino',
				'code'    => 'SM',
			],
			[
				'country' => 'Saudi-Arabia',
				'code'    => 'SA',
			],
			[
				'country' => 'Scotland',
				'code'    => 'GB',
			],
			[
				'country' => 'Senegal',
				'code'    => 'SN',
			],
			[
				'country' => 'Serbia',
				'code'    => 'RS',
			],
			[
				'country' => 'Singapore',
				'code'    => 'SG',
			],
			[
				'country' => 'Slovakia',
				'code'    => 'SK',
			],
			[
				'country' => 'Slovenia',
				'code'    => 'SI',
			],
			[
				'country' => 'South-Africa',
				'code'    => 'ZA',
			],
			[
				'country' => 'South-Korea',
				'code'    => 'KR',
			],
			[
				'country' => 'Spain',
				'code'    => 'ES',
			],
			[
				'country' => 'Sudan',
				'code'    => 'SD',
			],
			[
				'country' => 'Surinam',
				'code'    => 'SR',
			],
			[
				'country' => 'Sweden',
				'code'    => 'SE',
			],
			[
				'country' => 'Switzerland',
				'code'    => 'CH',
			],
			[
				'country' => 'Syria',
				'code'    => 'SY',
			],
			[
				'country' => 'Tajikistan',
				'code'    => 'TJ',
			],
			[
				'country' => 'Tanzania',
				'code'    => 'TZ',
			],
			[
				'country' => 'Thailand',
				'code'    => 'TH',
			],
			[
				'country' => 'Trinidad-And-Tobago',
				'code'    => 'TT',
			],
			[
				'country' => 'Tunisia',
				'code'    => 'TN',
			],
			[
				'country' => 'Turkey',
				'code'    => 'TR',
			],
			[
				'country' => 'Turkmenistan',
				'code'    => 'TM',
			],
			[
				'country' => 'Uganda',
				'code'    => 'UG',
			],
			[
				'country' => 'Ukraine',
				'code'    => 'UA',
			],
			[
				'country' => 'United-Arab-Emirates',
				'code'    => 'AE',
			],
			[
				'country' => 'Uruguay',
				'code'    => 'UY',
			],
			[
				'country' => 'USA',
				'code'    => 'US',
			],
			[
				'country' => 'Uzbekistan',
				'code'    => 'UZ',
			],
			[
				'country' => 'Venezuela',
				'code'    => 'VE',
			],
			[
				'country' => 'Vietnam',
				'code'    => 'VN',
			],
			[
				'country' => 'Wales',
				'code'    => 'GB',
			],
			[
				'country' => 'World',
				'code'    => '',
			],
			[
				'country' => 'Zambia',
				'code'    => 'ZM',
			],
			[
				'country' => 'Zimbabwe',
				'code'    => 'ZW',
			],
		];

		return $options;
	}

	/**
	 * Get country code by its name.
	 *
	 * @param string $name - Country name
	 *
	 * @return string|bool
	 * @since 0.9.6
	 */
	public function get_api_country_code_by_name( $name ) {
		$countries = [
			'Afghanistan'                          => 'af',
			'Albania'                              => 'al',
			'Algeria'                              => 'dz',
			'American Samoa'                       => 'ds',
			'Andorra'                              => 'ad',
			'Angola'                               => 'ao',
			'Anguilla'                             => 'ai',
			'Antarctica'                           => 'aq',
			'Antigua and Barbuda'                  => 'ag',
			'Argentina'                            => 'ar',
			'Armenia'                              => 'am',
			'Aruba'                                => 'aw',
			'Australia'                            => 'au',
			'Austria'                              => 'at',
			'Azerbaidjan'                          => 'az',
			'Azerbaijan'                           => 'az',
			'Bahamas'                              => 'bs',
			'Bahrain'                              => 'bh',
			'Bangladesh'                           => 'bd',
			'Barbados'                             => 'bb',
			'Belarus'                              => 'by',
			'Belgium'                              => 'be',
			'Belize'                               => 'bz',
			'Benin'                                => 'bj',
			'Bermuda'                              => 'bm',
			'Bhutan'                               => 'bt',
			'Bolivia'                              => 'bo',
			'Bosnia and Herzegovina'               => 'ba',
			'Botswana'                             => 'bw',
			'Bouvet Island'                        => 'bv',
			'Brazil'                               => 'br',
			'British Indian Ocean Territory'       => 'io',
			'Brunei Darussalam'                    => 'bn',
			'Bulgaria'                             => 'bg',
			'Burkina Faso'                         => 'bf',
			'Burundi'                              => 'bi',
			'Cambodia'                             => 'kh',
			'Cameroon'                             => 'cm',
			'Canada'                               => 'ca',
			'Cape Verde'                           => 'cv',
			'Cape Verde Islands'                   => 'cv',
			'Cayman Islands'                       => 'ky',
			'Central African Republic'             => 'cf',
			'Chad'                                 => 'td',
			'Chile'                                => 'cl',
			'China'                                => 'cn',
			'Christmas Island'                     => 'cx',
			'Cocos (Keeling) Islands'              => 'cc',
			'Colombia'                             => 'co',
			'Comoros'                              => 'km',
			'Congo'                                => 'cg',
			'Congo DR'                             => 'cd',
			'Cook Islands'                         => 'ck',
			'Costa Rica'                           => 'cr',
			'Croatia'                              => 'hr',
			'Cuba'                                 => 'cu',
			'Curaçao'                              => 'cw',
			'Cyprus'                               => 'cy',
			'Czech Republic'                       => 'cz',
			'Denmark'                              => 'dk',
			'Djibouti'                             => 'dj',
			'Dominica'                             => 'dm',
			'Dominican Republic'                   => 'do',
			'East Timor'                           => 'tp',
			'Ecuador'                              => 'ec',
			'Egypt'                                => 'eg',
			'El Salvador'                          => 'sv',
			'Equatorial Guinea'                    => 'gq',
			'Eritrea'                              => 'er',
			'Estonia'                              => 'ee',
			'Ethiopia'                             => 'et',
			'Falkland Islands (Malvinas)'          => 'fk',
			'Faroe Islands'                        => 'fo',
			'Fiji'                                 => 'fj',
			'Finland'                              => 'fi',
			'France'                               => 'fr',
			'France, Metropolitan'                 => 'fx',
			'French Guiana'                        => 'gf',
			'French Polynesia'                     => 'pf',
			'French Southern Territories'          => 'tf',
			'Gabon'                                => 'ga',
			'Gambia'                               => 'gm',
			'Georgia'                              => 'ge',
			'Germany'                              => 'de',
			'Ghana'                                => 'gh',
			'Gibraltar'                            => 'gi',
			'Guernsey'                             => 'gk',
			'Greece'                               => 'gr',
			'Greenland'                            => 'gl',
			'Grenada'                              => 'gd',
			'Guadeloupe'                           => 'gp',
			'Guam'                                 => 'gu',
			'Guatemala'                            => 'gt',
			'Guinea'                               => 'gn',
			'Guinea Bissau'                        => 'gw',
			'Guinea-Bissau'                        => 'gw',
			'Guyana'                               => 'gy',
			'Haiti'                                => 'ht',
			'Heard and Mc Donald Islands'          => 'hm',
			'Honduras'                             => 'hn',
			'Hong Kong'                            => 'hk',
			'Hungary'                              => 'hu',
			'Iceland'                              => 'is',
			'India'                                => 'in',
			'Isle of Man'                          => 'im',
			'Indonesia'                            => 'id',
			'Iran'                                 => 'ir',
			'Iraq'                                 => 'iq',
			'Ireland'                              => 'ie',
			'Republic of Ireland'                  => 'ie',
			'Israel'                               => 'il',
			'Italy'                                => 'it',
			'Ivory Coast'                          => 'ci',
			'Côte d\'Ivoire'                       => 'ci',
			'Jersey'                               => 'je',
			'Jamaica'                              => 'jm',
			'Japan'                                => 'jp',
			'Jordan'                               => 'jo',
			'Kazakhstan'                           => 'kz',
			'Kenya'                                => 'ke',
			'Kiribati'                             => 'ki',
			'Korea DPR'                            => 'kp',
			'South Korea'                          => 'kr',
			'Korea Republic'                       => 'kr',
			'Kosovo'                               => 'xk',
			'Kuwait'                               => 'kw',
			'Kyrgyzstan'                           => 'kg',
			'Lao'                                  => 'la',
			'Latvia'                               => 'lv',
			'Lebanon'                              => 'lb',
			'Lesotho'                              => 'ls',
			'Liberia'                              => 'lr',
			'Libyan Arab Jamahiriya'               => 'ly',
			'Liechtenstein'                        => 'li',
			'Lithuania'                            => 'lt',
			'Luxembourg'                           => 'lu',
			'Macau'                                => 'mo',
			'Macedonia'                            => 'mk',
			'North Macedonia'                      => 'mk',
			'Madagascar'                           => 'mg',
			'Malawi'                               => 'mw',
			'Malaysia'                             => 'my',
			'Maldives'                             => 'mv',
			'Mali'                                 => 'ml',
			'Malta'                                => 'mt',
			'Marshall Islands'                     => 'mh',
			'Martinique'                           => 'mq',
			'Mauritania'                           => 'mr',
			'Mauritius'                            => 'mu',
			'Mayotte'                              => 'ty',
			'Mexico'                               => 'mx',
			'Micronesia, Federated States of'      => 'fm',
			'Moldova'                              => 'md',
			'Monaco'                               => 'mc',
			'Mongolia'                             => 'mn',
			'Montenegro'                           => 'me',
			'Montserrat'                           => 'ms',
			'Morocco'                              => 'ma',
			'Mozambique'                           => 'mz',
			'Myanmar'                              => 'mm',
			'Namibia'                              => 'na',
			'Nauru'                                => 'nr',
			'Nepal'                                => 'np',
			'Netherlands'                          => 'nl',
			'Netherlands Antilles'                 => 'an',
			'New Caledonia'                        => 'nc',
			'New Zealand'                          => 'nz',
			'Nicaragua'                            => 'ni',
			'Niger'                                => 'ne',
			'Nigeria'                              => 'ng',
			'Niue'                                 => 'nu',
			'Norfolk Island'                       => 'nf',
			'Northern Mariana Islands'             => 'mp',
			'Norway'                               => 'no',
			'Oman'                                 => 'om',
			'Pakistan'                             => 'pk',
			'Palau'                                => 'pw',
			'Palestine'                            => 'ps',
			'Panama'                               => 'pa',
			'Papua New Guinea'                     => 'pg',
			'Paraguay'                             => 'py',
			'Peru'                                 => 'pe',
			'Philippines'                          => 'ph',
			'Pitcairn'                             => 'pn',
			'Poland'                               => 'pl',
			'Portugal'                             => 'pt',
			'Puerto Rico'                          => 'pr',
			'Qatar'                                => 'qa',
			'Reunion'                              => 're',
			'Romania'                              => 'ro',
			'Russia'                               => 'ru',
			'Rwanda'                               => 'rw',
			'Saint Kitts and Nevis'                => 'kn',
			'Saint Lucia'                          => 'lc',
			'Saint Vincent and the Grenadines'     => 'vc',
			'Samoa'                                => 'ws',
			'San Marino'                           => 'sm',
			'Sao Tome and Principe'                => 'st',
			'Saudi Arabia'                         => 'sa',
			'Senegal'                              => 'sn',
			'Serbia'                               => 'rs',
			'Seychelles'                           => 'sc',
			'Sierra Leone'                         => 'sl',
			'Singapore'                            => 'sg',
			'Slovakia'                             => 'sk',
			'Slovenia'                             => 'si',
			'Solomon Islands'                      => 'sb',
			'Somalia'                              => 'so',
			'South Africa'                         => 'za',
			'South Sudan'                          => 'ss',
			'South Georgia South Sandwich Islands' => 'gs',
			'Spain'                                => 'es',
			'Sri Lanka'                            => 'lk',
			'St. Helena'                           => 'sh',
			'St. Pierre and Miquelon'              => 'pm',
			'Sudan'                                => 'sd',
			'Suriname'                             => 'sr',
			'Svalbard and Jan Mayen Islands'       => 'sj',
			'Swaziland'                            => 'sz',
			'Sweden'                               => 'se',
			'Switzerland'                          => 'ch',
			'Syria'                                => 'sy',
			'Taiwan'                               => 'tw',
			'Tajikistan'                           => 'tj',
			'Tanzania, United Republic of'         => 'tz',
			'Thailand'                             => 'th',
			'Togo'                                 => 'tg',
			'Tokelau'                              => 'tk',
			'Tonga'                                => 'to',
			'Trinidad and Tobago'                  => 'tt',
			'Tunisia'                              => 'tn',
			'Turkey'                               => 'tr',
			'Turkmenistan'                         => 'tm',
			'Turks and Caicos Islands'             => 'tc',
			'Tuvalu'                               => 'tv',
			'Uganda'                               => 'ug',
			'Ukraine'                              => 'ua',
			'United Arab Emirates'                 => 'ae',
			'United Kingdom'                       => 'gb',
			'USA'                                  => 'us',
			'United States minor outlying islands' => 'um',
			'Uruguay'                              => 'uy',
			'Uzbekistan'                           => 'uz',
			'Vanuatu'                              => 'vu',
			'Vatican City State'                   => 'va',
			'Venezuela'                            => 've',
			'Vietnam'                              => 'vn',
			'Virgin Islands (British)'             => 'vg',
			'Virgin Islands (U.S.)'                => 'vi',
			'Wallis and Futuna Islands'            => 'wf',
			'Western Sahara'                       => 'eh',
			'Yemen'                                => 'ye',
			'Zaire'                                => 'zr',
			'Zambia'                               => 'zm',
			'Zimbabwe'                             => 'zw',
			'England'                              => '_England',
			'Northern Ireland'                     => '_Northern_Ireland',
			'Scotland'                             => '_Scotland',
			'Wales'                                => '_Wales',
		];

		return isset( $countries[ $name ] ) ? $countries[ $name ] : '';
	}

	/**
	 * Get posts with associated tag.
	 *
	 * @param array $term_ids
	 * @param int   $limit
	 *
	 * @return array
	 * @since 0.10.5
	 */
	public function get_tag_posts_by_id( $term_ids, $limit ) {

		$output = [];

		if ( empty( $term_ids ) ) {
			return $output;
		}

		$all_posts = get_posts(
			[
				'tag__in'     => $term_ids,
				'numberposts' => absint( $limit ),
				'post_type'   => 'any',
			]
		);

		if ( ! empty( $all_posts ) ) {
			foreach ( $all_posts as $single_post ) {
				if ( 0 !== mb_stripos( $single_post->post_type, 'anwp_' ) && 0 !== mb_stripos( $single_post->post_type, 'sl_' ) ) {
					$output[] = $single_post;
				}
			}
		}

		return $output;
	}

	/**
	 * Return an image URI.
	 *
	 * @param  string $size The image size you want to return.
	 * @param  bool   $allow_placeholder
	 * @param  int    $pre_post_id
	 *
	 * @return string         The image URI.
	 * @since 0.10.5
	 */
	public function get_post_image_uri( $size = 'full', $allow_placeholder = true, $pre_post_id = null ) {

		$media_url = '';

		$post_id = $pre_post_id ? $pre_post_id : get_the_ID();

		// If featured image is present, use that.
		if ( has_post_thumbnail( $post_id ) ) {

			$featured_image_id = get_post_thumbnail_id( $post_id );
			$media_url         = wp_get_attachment_image_url( $featured_image_id, sanitize_key( $size ) );

			if ( $media_url ) {
				return $media_url;
			}
		}

		/*
		|--------------------------------------------------------------------
		| Video Post Format
		|--------------------------------------------------------------------
		*/
		// Get image for video post format
		if ( 'video' === get_post_format( $post_id ) && function_exists( 'aneto_get_youtube_id' ) ) {

			$video_data = array(
				'source' => get_post_meta( $post_id, '_anwp_extras_video_source', true ), // site, youtube or vimeo
				'url'    => get_post_meta( $post_id, '_anwp_extras_video_id', true ),
			);

			// Check youtube id
			if ( 'youtube' === $video_data['source'] || empty( $video_data['source'] ) ) {

				// Try to get video ID
				$video_id = aneto_get_youtube_id( $video_data['url'] );

				if ( $video_id ) {
					return esc_url( sprintf( 'http://img.youtube.com/vi/%s/maxresdefault.jpg', $video_id ) );
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Gallery Post Format
		|--------------------------------------------------------------------
		*/
		// Get image for gallery post type
		if ( 'gallery' === get_post_format( $post_id ) ) {

			$gallery_images = get_post_meta( $post_id, '_anwp_extras_gallery_images', true );

			if ( ! empty( $gallery_images ) && is_array( $gallery_images ) ) {

				reset( $gallery_images );
				$gallery_image_id = key( $gallery_images );

				$media_url = wp_get_attachment_image_url( $gallery_image_id, sanitize_key( $size ) );

				if ( $media_url ) {
					return $media_url;
				}
			}
		}

		if ( 'post' === get_post_type( $post_id ) && 'video' !== get_post_format( $post_id ) ) {
			// Check for any attached image.
			$media = get_attached_media( 'image', $post_id );

			// If an image is present, then use it.
			if ( is_array( $media ) && 0 < count( $media ) ) {
				$media     = current( $media );
				$media_url = wp_get_attachment_image_url( $media->ID, sanitize_key( $size ) );
			}
		}

		// Set up default image path.
		if ( empty( $media_url ) && $allow_placeholder ) {
			$media_url = AnWP_Football_Leagues_Premium::url( 'public/img/empty_image.jpg' );
		}

		return $media_url;
	}

	/**
	 * Render post date
	 *
	 * @param int $post_id
	 *
	 * @return string
	 * @since 0.10.5
	 */
	public function get_post_date( $post_id ) {

		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			get_the_date( DATE_W3C, $post_id ),
			get_the_date( '', $post_id ),
			get_the_modified_date( DATE_W3C, $post_id ),
			get_the_modified_date( '', $post_id )
		);

		// Wrap the time string in a link, and preface it with 'Posted on'.
		return '<span class="screen-reader-text">' . esc_html_x( 'Posted on', 'post date', 'anwp-football-leagues-premium' ) . '</span>' . $time_string;
	}

	/**
	 * Get Player POST season.
	 *
	 * @param int $referee_id
	 *
	 * @return int
	 * @since 0.14.8
	 */
	public function get_referee_post_season( $referee_id ) {

		static $season_id = null;

		if ( null === $season_id ) {

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( ! empty( $_GET['season'] ) ) {

				// phpcs:ignore WordPress.Security.NonceVerification
				$maybe_season_id = anwp_football_leagues()->season->get_season_id_by_slug( sanitize_key( $_GET['season'] ) );

				if ( absint( $maybe_season_id ) ) {

					$season_id = absint( $maybe_season_id );

					return $season_id;
				}
			}

			// Get Season ID
			$season_id = anwp_football_leagues()->get_active_referee_season( $referee_id );
		}

		return absint( $season_id );
	}

	/**
	 * Get Player POST season.
	 *
	 * @return array
	 * @since 0.14.8
	 */
	public function get_available_font_sizes() {

		return [
			''               => __( 'default' ),
			'anwp-text-xxs'  => 'XXS',
			'anwp-text-xs'   => 'XS',
			'anwp-text-sm'   => 'SM',
			'anwp-text-base' => 'M',
			'anwp-text-lg'   => 'LG',
			'anwp-text-xl'   => 'XL',
			'anwp-text-2xl'  => '2XL',
		];
	}

	/**
	 * Get all available timezones.
	 *
	 * @return array
	 * @throws Exception
	 * @since 0.14.10
	 */
	public function get_timezone_offsets() {
		$timezones = [];
		$offsets   = [];
		$now       = new DateTime( 'now', new DateTimeZone( 'UTC' ) );

		foreach ( DateTimeZone::listIdentifiers() as $timezone ) {
			$now->setTimezone( new DateTimeZone( $timezone ) );
			$offsets[] = $now->getOffset();
		}

		$offsets = array_unique( $offsets );
		sort( $offsets );

		foreach ( $offsets as $offset ) {
			$timezones[ intval( $offset / 60 ) ] = $this->format_GMT_offset( $offset );
		}

		return $timezones;
	}

	/**
	 * Format Timezones
	 *
	 * @param $offset
	 *
	 * @return string
	 * @version 0.14.10
	 */
	public function format_GMT_offset( $offset ) {
		$hours   = intval( $offset / 3600 );
		$minutes = abs( intval( $offset % 3600 / 60 ) );

		return 'UTC ' . sprintf( '%+03d:%02d', $hours, $minutes );
	}

	public function is_valid_ISO8601_date( $value ) {

		$dateTime = DateTime::createFromFormat( DateTime::ISO8601, $value );

		return ! empty( $dateTime );
	}

	public function get_color_rating( $player_rating ) {

		$min = anwp_football_leagues_premium()->customizer->get_value( 'general', 'rating_min_color', 5 );
		$max = anwp_football_leagues_premium()->customizer->get_value( 'general', 'rating_max_color', 9 );

		$color_rating = $player_rating;

		if ( $color_rating > $max ) {
			$color_rating = $max;
		} elseif ( $color_rating < $min ) {
			$color_rating = $min;
		}

		return round( ( $color_rating - $min ) / ( $max - $min ), 2 ) * 200;
	}
}
