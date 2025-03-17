<?php

/**
 * AnWP Football Leagues Premium :: Suspension
 *
 * @since 0.13.7
 */
class AnWPFL_Premium_Suspension {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 * @since  0.13.7
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * @param AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// Save core plugin to var
		$this->plugin = $plugin;

		// Register CPT
		$this->register_post_type();

		// Run hooks
		$this->hooks();
	}

	/**
	 * Register Custom Post Type
	 */
	public function register_post_type() {

		// Register this CPT.
		$labels = [
			'name'          => _x( 'Card Suspensions', 'Post type general name', 'anwp-football-leagues-premium' ),
			'singular_name' => _x( 'Card Suspensions', 'Post type singular name', 'anwp-football-leagues-premium' ),
			'menu_name'     => _x( 'Card Suspensions', 'Admin Menu text', 'anwp-football-leagues-premium' ),
			'add_new'       => __( 'Add New', 'anwp-football-leagues-premium' ),
			'add_new_item'  => __( 'Add New', 'anwp-football-leagues-premium' ),
		];

		$args = [
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'rewrite'            => false,
			'show_in_admin_bar'  => false,
			'show_in_menu'       => 'edit.php?post_type=anwp_competition',
			'show_ui'            => true,
			'supports'           => false,
		];

		register_post_type( 'anwp_fl_suspension', $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.13.7
	 */
	public function hooks() {

		// Admin Table filters
		add_filter( 'disable_months_dropdown', [ $this, 'disable_months_dropdown' ], 10, 2 );

		// Metaboxes
		add_action( 'load-post.php', [ $this, 'init_metaboxes' ] );
		add_action( 'load-post-new.php', [ $this, 'init_metaboxes' ] );
		add_action( 'add_meta_boxes_anwp_fl_suspension', [ $this, 'remove_metaboxes' ], 10, 1 );
		add_action( 'save_post_anwp_fl_suspension', [ $this, 'save_metabox' ], 10, 2 );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		add_action( 'anwpfl/match/on_save', [ $this, 'add_suspension_automatically' ], 20, 1 );

		// Notices
		add_action( 'admin_notices', [ $this, 'display_admin_missing_notice' ] );
	}

	/**
	 * Display missing players text.
	 *
	 * @since 0.13.7
	 */
	public function display_admin_missing_notice() {

		if ( get_transient( 'anwp-admin-missing-calculated' ) ) :
			?>
			<div class="notice notice-success is-dismissible anwp-visible-after-header">
				<p><?php echo wp_kses_post( get_transient( 'anwp-admin-missing-calculated' ) ); ?></p>
			</div>
			<?php
			delete_transient( 'anwp-admin-missing-calculated' );
		endif;
	}

	/**
	 * Remove submit metaboxes.
	 *
	 * @since 0.13.7
	 */
	public function remove_metaboxes() {

		$post = get_post();

		if ( 'yes' !== get_post_meta( $post->ID, '_fl_fixed', true ) ) {
			remove_meta_box( 'submitdiv', 'anwp_fl_suspension', 'side' );
		}
	}

	/**
	 * Filters whether to remove the 'Months' drop-down from the post list table.
	 *
	 * @param bool   $disable   Whether to disable the drop-down. Default false.
	 * @param string $post_type The post type.
	 *
	 * @return bool
	 * @since 0.13.7
	 */
	public function disable_months_dropdown( $disable, $post_type ) {

		return 'anwp_fl_suspension' === $post_type ? true : $disable;
	}

	/**
	 * Meta box initialization.
	 */
	public function init_metaboxes() {
		add_action(
			'add_meta_boxes',
			function ( $post_type ) {
				if ( 'anwp_fl_suspension' === $post_type ) {
					add_meta_box(
						'anwp_fl_suspension',
						__( 'Card Suspensions', 'anwp-football-leagues-premium' ) . ' (Experimental)',
						[ $this, 'render_metabox' ],
						$post_type,
						'normal',
						'high'
					);
				}
			}
		);
	}

	/**
	 * Render Meta Box
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @since  0.13.7
	 */
	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'anwp_save_metabox_' . $post->ID, 'anwp_metabox_nonce' );

		if ( 'yes' === get_post_meta( $post->ID, '_fl_fixed', true ) ) :

			$competition_obj = anwp_football_leagues()->competition->get_competition( get_post_meta( $post->ID, '_fl_suspension_id', true ) );
			?>
			<input type="hidden" value="yes" name="_fl_fixed">
			<div class="anwp-admin-metabox anwp-b-wrap">

				<div class="d-flex flex-wrap align-items-center my-2 anwp-bg-blue-100 p-1">
					<b class="mr-1"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?>:</b> <span><?php echo esc_html( $competition_obj->title ); ?></span>

					<?php if ( ! empty( $competition_obj->league_text ) ) : ?>
						<span class="text-muted small mx-2">|</span>
						<b class="mr-1"><?php echo esc_html__( 'League', 'anwp-football-leagues' ); ?>:</b> <span><?php echo esc_html( $competition_obj->league_text ); ?></span>
					<?php endif; ?>

					<?php if ( ! empty( $competition_obj->season_text ) ) : ?>
						<span class="text-muted small mx-2">|</span>
						<b class="mr-1"><?php echo esc_html__( 'Season', 'anwp-football-leagues' ); ?>:</b> <span><?php echo esc_html( $competition_obj->season_text ); ?></span>
					<?php endif; ?>
				</div>

				<div id="fl-app-suspension"></div>
				<input class="button button-primary button-large mt-0 px-5" type="submit" value="<?php esc_html_e( 'Save', 'anwp-football-leagues-premium' ); ?>">
			</div>
		<?php else : ?>
			<div class="anwp-admin-metabox anwp-b-wrap">
				<div class="anwp-font-normal mt-3"><?php echo esc_html__( 'Select Competition', 'anwp-football-leagues-premium' ); ?></div>
				<div class="mb-3">
					<select name="_fl_suspension_id" class="postform">
						<?php
						foreach ( anwp_football_leagues()->competition->get_competitions() as $competition_obj ) :
							if ( 'secondary' === $competition_obj->multistage ) {
								continue;
							}
							?>
							<option value="<?php echo absint( $competition_obj->id ); ?>"><?php echo esc_html( $competition_obj->title ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="mb-3">
					<button class="button button-primary button-large" name="select-competition" type="submit" value="yes"><?php esc_html_e( 'Save & Continue', 'anwp-football-leagues-premium' ); ?></button>
				</div>
			</div>
			<?php
		endif;
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int     $post_id The ID of the post being saved.
	 * @param WP_Post $post
	 *
	 * @since  0.13.7
	 * @return bool|int
	 */
	public function save_metabox( $post_id, $post ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['anwp_metabox_nonce'] ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['anwp_metabox_nonce'], 'anwp_save_metabox_' . $post_id ) ) {
			return $post_id;
		}

		// Check post type
		if ( 'anwp_fl_suspension' !== $_POST['post_type'] ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// check if there was a multisite switch before
		if ( is_multisite() && ms_is_switched() ) {
			return $post_id;
		}

		/* OK, it's safe for us to save the data now. */

		/** ---------------------------------------
		 * Save Data
		 * ---------------------------------------*/

		$fixed = empty( $_POST['_fl_fixed'] ) ? '' : sanitize_key( $_POST['_fl_fixed'] );

		if ( 'yes' === $fixed ) {

			update_post_meta( $post_id, '_fl_fixed', 'yes' );

			$auto_suspension = isset( $_POST['_fl_pro_suspension_auto'] ) && 'yes' === $_POST['_fl_pro_suspension_auto'];

			// Prepare data
			$save_data = [
				'y'    => isset( $_POST['_fl_pro_suspension_y'] ) ? wp_json_encode( json_decode( wp_unslash( $_POST['_fl_pro_suspension_y'] ) ) ) : [],
				'2y'   => $auto_suspension && isset( $_POST['_fl_pro_suspension_2y'] ) ? absint( $_POST['_fl_pro_suspension_2y'] ) : '',
				'r'    => $auto_suspension && isset( $_POST['_fl_pro_suspension_r'] ) ? absint( $_POST['_fl_pro_suspension_r'] ) : '',
				'auto' => $auto_suspension ? get_post_meta( $post_id, '_fl_suspension_id', true ) : '',
			];

			foreach ( $save_data as $save_key => $save_value ) {
				$meta_key = '_fl_pro_suspension_' . $save_key;

				if ( empty( $save_value ) ) {
					delete_post_meta( $post_id, $meta_key );
				} else {
					update_post_meta( $post_id, $meta_key, $save_value );
				}
			}
		} elseif ( isset( $_POST['select-competition'] ) && 'yes' === $_POST['select-competition'] ) {

			$suspension_id = isset( $_POST['_fl_suspension_id'] ) ? absint( $_POST['_fl_suspension_id'] ) : '';

			if ( $suspension_id ) {
				update_post_meta( $post_id, '_fl_suspension_id', $suspension_id );
				update_post_meta( $post_id, '_fl_fixed', 'yes' );

				$update_data = [
					'ID'         => $post_id,
					'post_title' => anwp_football_leagues()->competition->get_competition_title( $suspension_id ),
				];

				if ( 'publish' !== $post->post_status ) {
					$update_data['post_status'] = 'publish';
				}

				remove_action( 'save_post_anwp_fl_suspension', [ $this, 'save_metabox' ] );

				wp_update_post( $update_data );

				// re-hook this function
				add_action( 'save_post_anwp_fl_suspension', [ $this, 'save_metabox' ] );
			}
		}

		return $post_id;
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @since 0.13.7
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		$current_screen = get_current_screen();

		if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) && 'anwp_fl_suspension' === $current_screen->id ) {

			$post_id = get_the_ID();

			$data = [
				'suspensionRules'      => json_decode( get_post_meta( $post_id, '_fl_pro_suspension_y', true ) ) ?: '',
				'redCardRule'          => get_post_meta( $post_id, '_fl_pro_suspension_r', true ),
				'api_import_used'      => AnWPFL_Premium_API::get_config_value( 'key' ) ? 'yes' : '',
				'secondYellowCardRule' => get_post_meta( $post_id, '_fl_pro_suspension_2y', true ),
				'automaticallySuspend' => get_post_meta( $post_id, '_fl_pro_suspension_auto', true ),
				'l10n'                 => [
					'suspended_rules'               => __( 'Suspended Rules', 'anwp-football-leagues-premium' ),
					'add_yellow_cards_rule'         => __( "add yellow card's rule", 'anwp-football-leagues-premium' ),
					'card'                          => __( 'Card', 'anwp-football-leagues' ),
					'yellow_card'                   => __( 'Yellow Card', 'anwp-football-leagues' ),
					'nd_yellow_red_card'            => __( '2nd Yellow > Red Card', 'anwp-football-leagues' ),
					'red_card'                      => __( 'Red Card', 'anwp-football-leagues' ),
					'number_of_cards'               => __( 'Number of Cards', 'anwp-football-leagues-premium' ),
					'games_to_suspend'              => __( 'Games to suspend', 'anwp-football-leagues-premium' ),
					'automatically_suspend_players' => __( 'Automatically Suspend Players', 'anwp-football-leagues-premium' ),
				],
			];

			if ( 'yes' === get_post_meta( $post_id, '_fl_fixed', true ) ) {
				wp_localize_script( 'anwpfl_premium_admin_vue', 'anwpSuspensionData', $data );
			}
		}
	}

	/**
	 * Get Players with suspension risk
	 *
	 * @param array $game_data
	 *
	 * @return void
	 * @since 0.13.7
	 */
	public function add_suspension_automatically( $game_data ) {

		if ( ! empty( $game_data['match_events'] ) && ! empty( json_decode( $game_data['match_events'] ) ) ) {
			$game_data['events'] = json_decode( $game_data['match_events'] );
		}

		if ( empty( $game_data['events'] ) || empty( $game_data['match_id'] ) || empty( $game_data['competition_id'] ) || ! absint( $game_data['finished'] ) ) {
			return;
		}

		/*
		|--------------------------------------------------------------------
		| Check auto calculation
		|--------------------------------------------------------------------
		*/
		global $wpdb;

		$competition_id = anwp_fl()->competition->get_main_competition_id( $game_data['competition_id'] );

		$auto_calculation_id = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_fl_pro_suspension_auto' AND meta_value = %d
				",
				$competition_id
			)
		);

		if ( empty( $auto_calculation_id ) ) {
			return;
		}

		/*
		|--------------------------------------------------------------------
		| Prepare risk players of game
		|--------------------------------------------------------------------
		*/
		$cards = [
			'y'  => [],
			'yr' => [],
			'r'  => [],
		];

		foreach ( $game_data['events'] as $event ) {
			if ( 'card' !== $event->type || ! in_array( $event->card, [ 'y', 'yr', 'r' ], true ) || empty( $event->player ) ) {
				continue;
			}

			$cards[ $event->card ][ $event->player ] = $event->club;
		}

		/*
		|--------------------------------------------------------------------
		| Get risk players before
		|--------------------------------------------------------------------
		*/
		$risk_ids = $this->get_players_suspension_risk( (object) [ 'game_id' => $game_data['match_id'] ], 'ids' );
		$y_card_s = [];

		foreach ( $risk_ids as $risk_id ) {
			if ( empty( $cards['y'][ $risk_id ] ) || ! empty( $cards['yr'][ $risk_id ] ) ) {
				continue;
			}

			$y_card_s[] = $risk_id;
		}

		if ( empty( $y_card_s ) && empty( $cards['yr'] ) && empty( $cards['r'] ) ) {
			return;
		}

		/*
		|--------------------------------------------------------------------
		| Prepare Missing game and saved missed players
		|--------------------------------------------------------------------
		*/
		$missed_clubs = [];

		foreach ( $y_card_s as $y_card ) {
			$missed_clubs[ $cards['y'][ $y_card ] ] = [];
		}

		foreach ( $cards['yr'] as $club_id ) {
			$missed_clubs[ $club_id ] = [];
		}

		foreach ( $cards['r'] as $club_id ) {
			$missed_clubs[ $club_id ] = [];
		}

		foreach ( $missed_clubs as $missed_club_id => $club_data ) {

			if ( ! anwp_fl()->helper->validate_date( $game_data['kickoff'] ) ) {
				continue;
			}

			$upcoming_games = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended(
				[
					'competition_id'  => $competition_id,
					'show_secondary'  => 1,
					'type'            => 'fixture',
					'filter_by_clubs' => $missed_club_id,
					'limit'           => 1,
					'sort_by_date'    => 'asc',
					'date_from'       => DateTime::createFromFormat( 'Y-m-d H:i:s', $game_data['kickoff'] )->modify( '+1 day' )->format( 'Y-m-d' ),
				]
			);

			if ( empty( $upcoming_games ) || empty( $upcoming_games[0]->match_id ) ) {
				continue;
			}

			$missed_clubs[ $missed_club_id ]['missed_game_id'] = $upcoming_games[0]->match_id;
			$missed_clubs[ $missed_club_id ]['missed_players'] =
				array_unique(
					array_map(
						'absint',
						$wpdb->get_col(
							$wpdb->prepare(
								"
								SELECT player_id
								FROM {$wpdb->prefix}anwpfl_missing_players
								WHERE match_id = %d
								",
								$missed_clubs[ $missed_club_id ]['missed_game_id']
							)
						)
					)
				);
		}

		/*
		|--------------------------------------------------------------------
		| Save missed status
		|--------------------------------------------------------------------
		*/
		$l10n_text = [
			'red_card'     => AnWPFL_Text::get_value( 'suspension__comment__red_card', __( 'Red card suspension', 'anwp-football-leagues-premium' ) ),
			'yellow_cards' => AnWPFL_Text::get_value( 'suspension__comment__yellow_cards', __( 'Yellow card suspension', 'anwp-football-leagues-premium' ) ),
		];

		$saved_data = [];

		foreach ( $y_card_s as $player_id ) {
			$club_id = $cards['y'][ $player_id ];

			if ( ! isset( $missed_clubs[ $club_id ] ) || ! isset( $missed_clubs[ $club_id ]['missed_players'] ) || in_array( absint( $player_id ), $missed_clubs[ $club_id ]['missed_players'], true ) ) {
				continue;
			}

			$this->insert_missing_player(
				(object) [
					'reason'    => 'suspended',
					'match_id'  => $missed_clubs[ $club_id ]['missed_game_id'],
					'club_id'   => $club_id,
					'player_id' => $player_id,
					'comment'   => $l10n_text['yellow_cards'],
				]
			);

			$saved_data[] = ( anwp_fl()->player->get_player_data( $player_id )['name'] ?? '' ) . ' / ' . anwp_fl_pro()->club->get_club( $club_id )->title . ' / ' . get_the_title( $missed_clubs[ $club_id ]['missed_game_id'] );
		}

		foreach ( [ 'yr', 'r' ] as $card_slug ) {
			foreach ( $cards[ $card_slug ] as $player_id => $club_id ) {

				if ( ! isset( $missed_clubs[ $club_id ] ) || ! isset( $missed_clubs[ $club_id ]['missed_players'] ) || in_array( absint( $player_id ), $missed_clubs[ $club_id ]['missed_players'], true ) ) {
					continue;
				}

				$this->insert_missing_player(
					(object) [
						'reason'    => 'suspended',
						'match_id'  => $missed_clubs[ $club_id ]['missed_game_id'],
						'club_id'   => $club_id,
						'player_id' => $player_id,
						'comment'   => $l10n_text['red_card'],
					]
				);

				$saved_data[] = ( anwp_fl()->player->get_player_data( (int) $player_id )['name'] ?? '' ) . ' / ' . anwp_fl_pro()->club->get_club( $club_id )->title . ' / ' . get_the_title( $missed_clubs[ $club_id ]['missed_game_id'] );
			}
		}

		/*
		|--------------------------------------------------------------------
		| Add admin notice
		|--------------------------------------------------------------------
		*/
		if ( $saved_data ) {
			$notice_text = '<b>Saved Missing Players (player/team/game):</b><br>- ' . implode( '<br>- ', $saved_data );
			set_transient( 'anwp-admin-missing-calculated', $notice_text, 10 );
		}
	}

	/**
	 * Insert missing player
	 *
	 * @param object $data
	 *
	 * @return bool
	 * @since 0.13.7
	 */
	private function insert_missing_player( $data ) {

		global $wpdb;

		// Prepare data to insert
		$data = [
			'reason'    => $data->reason,
			'match_id'  => $data->match_id,
			'club_id'   => $data->club_id,
			'player_id' => $data->player_id,
			'comment'   => $data->comment,
		];

		// Insert data to DB
		return $wpdb->insert( $wpdb->prefix . 'anwpfl_missing_players', $data );
	}

	/**
	 * Get Players with suspension risk
	 *
	 * @param object $args
	 * @param string $output
	 *
	 * @return array
	 * @since 0.13.7
	 */
	public function get_players_suspension_risk( $args, $output = '' ) {

		$args = wp_parse_args(
			$args,
			[
				'competition_id' => '',
				'game_id'        => '',
				'club_id'        => '',
				'show_links'     => 0,
			]
		);

		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'FL-PRO-PLAYER_get_players_suspension_risk__' . md5( maybe_serialize( $args ) );

		if ( class_exists( 'AnWPFL_Cache' ) && anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) && anwp_football_leagues()->cache->get( $cache_key, 'anwp_fl_suspension' ) ) {
			return anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' );
		}

		$game_data = $args['game_id'] ? anwp_fl()->match->get_game_data( $args['game_id'] ) : [];

		/*
		|--------------------------------------------------------------------
		| Load data in default way
		|--------------------------------------------------------------------
		*/
		if ( absint( $args['game_id'] ) ) {
			$args['competition_id'] = anwp_fl()->competition->get_main_competition_id( $game_data['competition_id'] );
		}

		if ( ! absint( $args['competition_id'] ) ) {
			return [];
		}

		global $wpdb;

		/*
		|--------------------------------------------------------------------
		| Get Number of Risk Cards
		|--------------------------------------------------------------------
		*/
		$rules_raw = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT pm2.meta_value card_rules
				FROM $wpdb->posts p
				LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = p.ID AND pm1.meta_key = '_fl_suspension_id' )
				LEFT JOIN $wpdb->postmeta pm2 ON ( pm2.post_id = p.ID AND pm2.meta_key = '_fl_pro_suspension_y' )
				WHERE p.post_status = 'publish' AND p.post_type = 'anwp_fl_suspension' AND pm1.meta_value = %d AND pm2.meta_value IS NOT NULL
				",
				$args['competition_id']
			)
		);

		$rules_raw = $rules_raw ? json_decode( $rules_raw ) : false;

		if ( empty( $rules_raw ) ) {
			return [];
		}

		$risk_cards = [];

		foreach ( $rules_raw as $card_rule ) {
			if ( absint( $card_rule->cards ) && $card_rule->cards > 1 && absint( $card_rule->games ) ) {
				$risk_cards[] = absint( $card_rule->cards ) - 1;
			}
		}

		if ( empty( $risk_cards ) ) {
			return [];
		}

		/*
		|--------------------------------------------------------------------
		| Get player's cards
		|--------------------------------------------------------------------
		*/
		$query =
			"
				SELECT
					p.player_id,
					SUM( CASE WHEN p.card_yr > 0 THEN 0 ELSE p.card_y END ) as cards
				FROM {$wpdb->prefix}anwpfl_players p
				LEFT JOIN {$wpdb->prefix}anwpfl_matches m ON m.match_id = p.match_id
				WHERE m.game_status = 1
			";

		// WHERE filter by competition ID
		$query .= $wpdb->prepare( ' AND ( m.competition_id = %d OR m.main_stage_id = %d ) ', $args['competition_id'], $args['competition_id'] );

		// WHERE filter by game ID
		if ( absint( $args['game_id'] ) ) {
			$query .= $wpdb->prepare( ' AND p.match_id != %d ', $args['game_id'] );
		}

		// Group by Player
		$query .= ' GROUP BY p.player_id';

		// Filter risk players
		$format = implode( ', ', array_fill( 0, count( $risk_cards ), '%d' ) );
		$query .= $wpdb->prepare( " HAVING cards IN ({$format}) ", $risk_cards ); // phpcs:ignore

		// phpcs:ignore WordPress.DB.PreparedSQL
		$rows = $wpdb->get_results( $query );

		if ( empty( $rows ) ) {
			return [];
		}

		if ( 'ids' === $output ) {
			return wp_list_pluck( $rows, 'player_id' );
		}

		$risk_players = [];
		foreach ( $rows as $row ) {
			$risk_players[ $row->player_id ] = $row->cards;
		}

		/*
		|--------------------------------------------------------------------
		| Filter & Group By Teams
		|--------------------------------------------------------------------
		*/
		$club_ids  = [];

		if ( absint( $args['club_id'] ) ) {
			$club_ids = wp_parse_id_list( $args['club_id'] );
		} elseif ( $args['game_id'] ) {
			$club_ids[] = absint( $game_data['home_club'] );
			$club_ids[] = absint( $game_data['away_club'] );
		} else {
			$club_ids = anwp_fl()->competition->get_competition_clubs( $args['competition_id'], 'all' );
		}

		if ( empty( $club_ids ) ) {
			return [];
		}

		/*
		|--------------------------------------------------------------------
		| Prepare Output
		|--------------------------------------------------------------------
		*/
		$output = [];

		// Add photos
		$season_id = absint( $args['game_id'] ) ? $game_data['season_id'] : absint( anwp_fl()->competition->get_competition( $args['competition_id'] )->season_ids );

		foreach ( $club_ids as $club_id ) {
			$club_players = anwp_fl()->club->get_club_season_players(
				[
					'club' => $club_id,
					'id'   => $season_id,
				],
				'short'
			);

			foreach ( $club_players as $club_player ) {
				if ( 'left' === $club_player->status || ! isset( $risk_players[ $club_player->id ] ) ) {
					continue;
				}

				if ( ! isset( $output[ $club_id ] ) ) {
					$output[ $club_id ] = [];
				}

				$output[ $club_id ][] = [
					'player_id' => $club_player->id,
					'link'      => '',
					'cards'     => $risk_players[ $club_player->id ],
					'position'  => $club_player->position,
				];
			}
		}

		/*
		|--------------------------------------------------------------------
		| player data
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $output ) ) {
			$players = anwp_fl()->player->get_players_by_ids( array_unique( array_column( array_merge( ...$output ), 'player_id' ) ), AnWP_Football_Leagues::string_to_bool( $args['show_links'] ) );

			foreach ( $output as $club_id => $club_output ) {
				foreach ( $club_output as $player_index => $player_data ) {
					$output[ $club_id ][ $player_index ]['link']       = $players[ $player_data['player_id'] ]['link'] ?? '';
					$output[ $club_id ][ $player_index ]['short_name'] = $players[ $player_data['player_id'] ]['short_name'] ?? '';
					$output[ $club_id ][ $player_index ]['photo']      = $players[ $player_data['player_id'] ]['photo'] ?? '';
					$output[ $club_id ][ $player_index ]['position']   = anwp_fl()->player->get_position_l10n( $player_data['position'] ?: $players[ $player_data['player_id'] ]['position'] );
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $output ) && class_exists( 'AnWPFL_Cache' ) ) {
			anwp_fl()->cache->set( $cache_key, $output, [ 'anwp_match', 'anwp_fl_suspension' ] );
		}

		return $output;
	}
}
