<?php
/**
 * AnWP Football Leagues Premium :: Transfer
 *
 * @since 0.8.11
 */
class AnWPFL_Premium_Transfer {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * @param AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( AnWP_Football_Leagues_Premium $plugin ) {

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
			'name'               => _x( 'Transfer', 'Post type general name', 'anwp-football-leagues-premium' ),
			'singular_name'      => _x( 'Transfer', 'Post type singular name', 'anwp-football-leagues-premium' ),
			'menu_name'          => _x( 'Transfers', 'Admin Menu text', 'anwp-football-leagues-premium' ),
			'name_admin_bar'     => _x( 'Transfer', 'Add New on Toolbar', 'anwp-football-leagues-premium' ),
			'add_new'            => __( 'Add New', 'anwp-football-leagues-premium' ),
			'add_new_item'       => __( 'Add New Transfer', 'anwp-football-leagues-premium' ),
			'new_item'           => __( 'New Transfer', 'anwp-football-leagues-premium' ),
			'edit_item'          => __( 'Edit Transfer', 'anwp-football-leagues-premium' ),
			'view_item'          => __( 'View Transfer', 'anwp-football-leagues-premium' ),
			'all_items'          => __( 'Transfers', 'anwp-football-leagues-premium' ),
			'search_items'       => __( 'Search Transfers', 'anwp-football-leagues-premium' ),
			'not_found'          => __( 'No Transfers found.', 'anwp-football-leagues-premium' ),
			'not_found_in_trash' => __( 'No Transfers found in Trash.', 'anwp-football-leagues-premium' ),
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
			'show_in_menu'       => true,
			'menu_position'      => 35,
			'menu_icon'          => 'dashicons-controls-repeat',
			'show_ui'            => true,
			'supports'           => false,
		];

		register_post_type( 'anwp_transfer', $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Modifies columns in Admin tables
		add_action( 'manage_anwp_transfer_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
		add_filter( 'manage_edit-anwp_transfer_columns', [ $this, 'columns' ] );

		// Init metabox
		add_action( 'load-post.php', [ $this, 'init_metaboxes' ] );
		add_action( 'load-post-new.php', [ $this, 'init_metaboxes' ] );

		add_action( 'save_post_anwp_transfer', [ $this, 'save_transfer_data' ], 20, 2 );

		// Admin Table filters
		add_filter( 'disable_months_dropdown', [ $this, 'disable_months_dropdown' ], 10, 2 );
		add_action( 'restrict_manage_posts', [ $this, 'add_more_filters' ] );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );

		add_action( 'delete_post', [ $this, 'on_transfer_delete' ] );

		add_filter( 'posts_clauses', [ $this, 'modify_query_clauses' ], 20, 2 );
	}

	/**
	 * Handle custom filter.
	 *
	 * Covers the WHERE, GROUP BY, JOIN, ORDER BY, DISTINCT,
	 * fields (SELECT), and LIMIT clauses.
	 *
	 * @param string[]  $clauses  {
	 *     Associative array of the clauses for the query.
	 *
	 *     @type string $where The WHERE clause of the query.
	 *     @type string $groupby The GROUP BY clause of the query.
	 *     @type string $join The JOIN clause of the query.
	 *     @type string $orderby The ORDER BY clause of the query.
	 *     @type string $distinct The DISTINCT clause of the query.
	 *     @type string $fields The SELECT clause of the query.
	 *     @type string $limits The LIMIT clause of the query.
	 * }
	 *
	 * @param WP_Query  $query The WP_Query instance (passed by reference).
	 */
	public function modify_query_clauses( array $clauses, WP_Query $query ): array {
		global $post_type, $pagenow, $wpdb;

		// Check main query in admin
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return $clauses;
		}

		if ( 'edit.php' === $pagenow && 'anwp_transfer' === $post_type ) {
			$clauses['join'] .= " LEFT JOIN $wpdb->anwpfl_transfers fl_transfer ON fl_transfer.transfer_id = {$wpdb->posts}.ID";
			$clauses['join'] .= " LEFT JOIN $wpdb->anwpfl_player_data fl_players ON fl_transfer.player_id = fl_players.player_id";

			$transfers_fields = [
				'fl_transfer.player_id          _fl_player_id',
				'fl_transfer.season_id          _fl_season_id',
				'fl_transfer.club_in            _fl_club_in',
				'fl_transfer.club_out           _fl_club_out',
				'fl_transfer.club_in_text       _fl_club_in_text',
				'fl_transfer.club_out_text      _fl_club_out_text',
				'fl_transfer.fee                _fl_fee',
				'fl_transfer.transfer_date      _fl_transfer_date',
				'fl_transfer.transfer_end_date  _fl_transfer_end_date',
				'fl_transfer.transfer_status    _fl_transfer_status',
				'fl_transfer.transfer_window    _fl_transfer_window',
				'fl_players.name                _fl_player_name',
			];

			$clauses['fields'] .= ',' . implode( ',', $transfers_fields );

			$get_data = wp_parse_args(
				$_GET, // phpcs:ignore WordPress.Security.NonceVerification
				[
					'_fl_player_id'              => '',
					'_fl_season_id'              => '',
					'_fl_club_in'                => '',
					'_fl_club_out'               => '',
					'_fl_transfer_date_from'     => '',
					'_fl_transfer_date_to'       => '',
					'_fl_transfer_end_date_from' => '',
					'_fl_transfer_end_date_to'   => '',
				]
			);

			$get_data = array_map( 'sanitize_text_field', $get_data );

			if ( absint( $get_data['_fl_player_id'] ) ) {
				$clauses['where'] .= $wpdb->prepare( ' AND fl_transfer.player_id = %d ', absint( $get_data['_fl_player_id'] ) );
			}

			if ( absint( $get_data['_fl_season_id'] ) ) {
				$clauses['where'] .= $wpdb->prepare( ' AND fl_transfer.season_id = %d ', absint( $get_data['_fl_season_id'] ) );
			}

			if ( absint( $get_data['_fl_club_in'] ) ) {
				$clauses['where'] .= $wpdb->prepare( ' AND fl_transfer.club_in = %d ', absint( $get_data['_fl_club_in'] ) );
			}

			if ( absint( $get_data['_fl_club_out'] ) ) {
				$clauses['where'] .= $wpdb->prepare( ' AND fl_transfer.club_out = %d ', absint( $get_data['_fl_club_out'] ) );
			}

			if ( sanitize_text_field( $get_data['_fl_transfer_date_from'] ) ) {
				$clauses['where'] .= $wpdb->prepare( ' AND fl_transfer.transfer_date >= %s ', sanitize_text_field( $get_data['_fl_transfer_date_from'] ) );
			}

			if ( sanitize_text_field( $get_data['_fl_transfer_date_to'] ) ) {
				$clauses['where'] .= $wpdb->prepare( ' AND fl_transfer.transfer_date <= %s ', sanitize_text_field( $get_data['_fl_transfer_date_to'] ) );
			}

			if ( sanitize_text_field( $get_data['_fl_transfer_end_date_from'] ) ) {
				$clauses['where'] .= $wpdb->prepare( ' AND fl_transfer.transfer_end_date >= %s ', sanitize_text_field( $get_data['_fl_transfer_end_date_from'] ) );
			}

			if ( sanitize_text_field( $get_data['_fl_transfer_end_date_to'] ) ) {
				$clauses['where'] .= $wpdb->prepare( ' AND fl_transfer.transfer_end_date <= %s ', sanitize_text_field( $get_data['_fl_transfer_end_date_to'] ) );
			}
		}

		return $clauses;
	}

	/**
	 * Fires before removing a post.
	 *
	 * @param int $post_ID Post ID.
	 */
	public function on_transfer_delete( int $post_ID ) {
		if ( 'anwp_transfer' === get_post_type( $post_ID ) ) {
			global $wpdb;

			$wpdb->delete( $wpdb->anwpfl_transfers, [ 'transfer_id' => $post_ID ] );
		}
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.9.6
	 */
	public function add_rest_routes() {

		register_rest_route(
			'anwpfl/transfer',
			'/get-player-options/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_transfer_player_options' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Get Player Options
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since 0.12.3
	 */
	public function get_transfer_player_options( WP_REST_Request $request ) {
		global $wpdb;

		$params  = $request->get_params();
		$players = [];

		if ( empty( $params['search'] ) ) {
			return rest_ensure_response( [ 'players' => [] ] );
		}

		$search_string = '%' . $wpdb->esc_like( str_replace( [ "\r", "\n" ], '', stripslashes( $params['search'] ) ) ) . '%';

		$results = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $wpdb->anwpfl_player_data WHERE `name` LIKE %s LIMIT 20", $search_string )
		) ?: [];

		foreach ( $results as $player_data ) {
			$players[] = [
				'id'         => $player_data->player_id,
				'name'       => $player_data->name,
				'birth_date' => $player_data->date_of_birth,
				'country'    => $player_data->nationality,
			];
		}

		return rest_ensure_response( [ 'players' => $players ] );
	}

	/**
	 * Meta box initialization.
	 *
	 * @since  0.1.0
	 */
	public function init_metaboxes() {
		add_action(
			'add_meta_boxes',
			function ( $post_type ) {
				if ( 'anwp_transfer' === $post_type ) {
					add_meta_box(
						'anwp_transfer',
						__( 'Transfer Data', 'anwp-football-leagues-premium' ),
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
	 * Render Meta Box content for Layout Builder.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @since  0.8.15
	 */
	public function render_metabox( WP_Post $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'anwp_save_metabox_' . $post->ID, 'anwp_metabox_nonce' );

		$available_seasons = anwp_fl()->season->get_seasons_list();

		$transfer_l10n = [
			'rest_root'                     => esc_url_raw( rest_url() ),
			'rest_nonce'                    => wp_create_nonce( 'wp_rest' ),
			'club_in'                       => __( 'Club In', 'anwp-football-leagues-premium' ),
			'additional_actions'            => __( 'Additional actions', 'anwp-football-leagues-premium' ),
			'are_you_sure'                  => __( 'Are you sure?', 'anwp-football-leagues' ),
			'cancel'                        => __( 'Cancel', 'anwp-football-leagues' ),
			'club_in_out'                   => __( 'Club IN - OUT', 'anwp-football-leagues-premium' ),
			'club_out'                      => __( 'Club Out', 'anwp-football-leagues-premium' ),
			'enter_club_name_manually'      => __( 'Enter club name manually without creating', 'anwp-football-leagues-premium' ),
			'fee'                           => __( 'Fee', 'anwp-football-leagues-premium' ),
			'fee_examples'                  => __( 'Examples: $120000, Free, Loan, Ended Loan 2019-06-01', 'anwp-football-leagues-premium' ),
			'loading_more_clubs'            => __( 'Loading more clubs', 'anwp-football-leagues-premium' ),
			'mid_season_window'             => __( 'Mid-season window', 'anwp-football-leagues-premium' ),
			'not_selected'                  => __( '- not selected -', 'anwp-football-leagues' ),
			'official'                      => __( 'Official', 'anwp-football-leagues-premium' ),
			'player'                        => __( 'Player', 'anwp-football-leagues-premium' ),
			'player_details'                => __( 'Player Details', 'anwp-football-leagues-premium' ),
			'pre_season_window'             => __( 'Pre-season window', 'anwp-football-leagues-premium' ),
			'register_in_squad'             => __( 'register in squad', 'anwp-football-leagues-premium' ),
			'register_player_in_club_squad' => __( 'Register player in the club\'s squad', 'anwp-football-leagues-premium' ),
			'required_fields'               => __( 'required fields', 'anwp-football-leagues' ),
			'rumour'                        => __( 'Rumour', 'anwp-football-leagues-premium' ),
			'saving_data_error'             => __( 'Saving Data Error', 'anwp-football-leagues' ),
			'successfully_saved'            => __( 'Successfully Saved', 'anwp-football-leagues-premium' ),
			'season'                        => __( 'Season', 'anwp-football-leagues' ),
			'set_as_current_club'           => __( 'set as current club', 'anwp-football-leagues-premium' ),
			'start_typing_player_name'      => __( 'start typing player name', 'anwp-football-leagues-premium' ),
			'transfer_date'                 => __( 'Transfer Date', 'anwp-football-leagues-premium' ),
			'transfer_end_date'             => __( 'Transfer End Date', 'anwp-football-leagues-premium' ),
			'transfer_details'              => __( 'Transfer Details', 'anwp-football-leagues-premium' ),
			'transfer_status'               => __( 'Transfer Status', 'anwp-football-leagues-premium' ),
			'window'                        => __( 'Window', 'anwp-football-leagues-premium' ),
		];

		$transfer_data = $this->get_transfer_data( $post->ID );

		$active_data = [
			'season_id'            => $transfer_data['season_id'] ?? '',
			'player_id'            => ( $transfer_data['player_id'] ?? '' ) ?: '',
			'transfer_date'        => '0000-00-00' === ( $transfer_data['transfer_date'] ?? '0000-00-00' ) ? '' : $transfer_data['transfer_date'],
			'transfer_end_date'    => '0000-00-00' === ( $transfer_data['transfer_end_date'] ?? '0000-00-00' ) ? '' : $transfer_data['transfer_end_date'],
			'status'               => $transfer_data['transfer_status'] ?? 1,
			'window'               => $transfer_data['transfer_window'] ?? 0,
			'fee'                  => $transfer_data['fee'] ?? '',
			'custom_club_in_text'  => $transfer_data['club_in_text'] ?? '',
			'custom_club_out_text' => $transfer_data['club_out_text'] ?? '',
			'club_in_initial'      => $transfer_data['club_in'] ?? 0,
			'club_out_initial'     => $transfer_data['club_out'] ?? 0,
		];

		// Prepare players
		$player_options = [];
		if ( $active_data['player_id'] ) {
			$player_data = anwp_fl()->player->get_player_data( $active_data['player_id'] );

			if ( $player_data ) {
				$player_options[] = [
					'id'         => $player_data['player_id'],
					'name'       => $player_data['name'],
					'birth_date' => '0000-00-00' !== $player_data['date_of_birth'] ? $player_data['date_of_birth'] : '',
					'country'    => $player_data['nationality'],
				];
			}
		}

		// Initial data - create transfer
		$maybe_data = [];

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['maybe_player'] ) && isset( $_GET['maybe_club_in'] ) ) {
			$maybe_data['player'] = absint( $_GET['maybe_player'] ); // phpcs:ignore WordPress.Security.NonceVerification
			$maybe_data['club']   = absint( $_GET['maybe_club_in'] ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( ! empty( $maybe_data['player'] ) ) {
				$player_data = anwp_fl()->player->get_player_data( $maybe_data['player'] );

				if ( $player_data ) {
					$player_options[] = [
						'id'         => $player_data['player_id'],
						'name'       => $player_data['name'],
						'birth_date' => '0000-00-00' !== $player_data['date_of_birth'] ? $player_data['date_of_birth'] : '',
						'country'    => $player_data['nationality'],
					];
				}
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( absint( $_GET['maybe_club_out'] ?? 0 ) ) {
				$maybe_data['club_out'] = absint( $_GET['maybe_club_out'] ); // phpcs:ignore WordPress.Security.NonceVerification
			}
		}

		// Club Options
		$club_options = anwp_fl()->club->get_clubs_list();

		array_unshift(
			$club_options,
			(object) [
				'id'    => 1,
				'title' => '- ' . esc_html__( 'unknown club', 'anwp-football-leagues-premium' ) . ' -',
				'logo'  => '',
			],
			(object) [
				'id'    => 2,
				'title' => '- ' . esc_html__( 'custom text', 'anwp-football-leagues-premium' ) . ' -',
				'logo'  => '',
			]
		);
		?>
		<script type="text/javascript">
			var _flTransferSeasons        = <?php echo wp_json_encode( $available_seasons ); ?>;
			var _flTransferL10n           = <?php echo wp_json_encode( $transfer_l10n ); ?>;
			var _flTransferActiveData     = <?php echo wp_json_encode( $active_data ); ?>;
			var _flTransferPlayers        = <?php echo wp_json_encode( $player_options ); ?>;
			var _flTransferClubs          = <?php echo wp_json_encode( $club_options ); ?>;
			var _flTransferMaybe          = <?php echo wp_json_encode( $maybe_data ); ?>;
			var _flTransferDatepickerL10n = <?php echo wp_json_encode( anwp_fl_pro()->data->get_vue_datepicker_locale() ); ?>;
		</script>
		<div class="anwp-admin-metabox anwp-b-wrap">
			<div id="anwp-fl-app-transfer"></div>
		</div>
		<?php
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int          $post_id The ID of the post being saved.
	 * @param WP_Post|null $post
	 *
	 * @return int
	 * @since  0.8.11
	 */
	public function save_transfer_data( int $post_id, WP_Post $post = null ): int {

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
		if ( 'anwp_transfer' !== $post->post_type ) {
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
		global $wpdb;

		// Parse transfer data
		$post_data = json_decode( wp_unslash( $_POST['_anwpfl_transfer_data'] ), true );

		/*
		|--------------------------------------------------------------------
		| Create player record if not exists
		|--------------------------------------------------------------------
		*/
		if ( empty( $this->get_transfer_data( $post_id ) ) || absint( $post_id ) !== absint( $this->get_transfer_data( $post_id )['transfer_id'] ) ) {
			$wpdb->insert( $wpdb->anwpfl_transfers, [ 'transfer_id' => $post_id ] );
		}

		/*
		|--------------------------------------------------------------------
		| Insert Data
		|--------------------------------------------------------------------
		*/
		$data_to_save = [
			'player_id'         => absint( $post_data['player_id'] ?? '' ),
			'season_id'         => absint( $post_data['season_id'] ?? '' ),
			'club_in'           => absint( $post_data['club_in']['id'] ?? '' ),
			'club_out'          => absint( $post_data['club_out']['id'] ?? '' ),
			'fee'               => sanitize_text_field( $post_data['fee'] ?? '' ),
			'club_in_text'      => sanitize_text_field( $post_data['custom_club_in_text'] ?? '' ),
			'club_out_text'     => sanitize_text_field( $post_data['custom_club_out_text'] ?? '' ),
			'transfer_date'     => sanitize_text_field( $post_data['transfer_date'] ?? '' ),
			'transfer_end_date' => sanitize_text_field( $post_data['transfer_end_date'] ?? '' ),
			'transfer_status'   => absint( $post_data['status'] ?? 1 ),
			'transfer_window'   => absint( $post_data['window'] ?? 0 ),
		];

		$wpdb->update( $wpdb->anwpfl_transfers, $data_to_save, [ 'transfer_id' => $post_id ] );

		/*
		|--------------------------------------------------------------------
		| Save Transfer Title
		|--------------------------------------------------------------------
		*/
		$new_post_title = esc_html__( '- player not selected -', 'anwp-football-leagues-premium' );

		if ( absint( $data_to_save['player_id'] ) ) {
			$new_post_title = anwp_fl()->player->get_player_data( $data_to_save['player_id'] )['name'] ?? '';
		}

		if ( $new_post_title && $new_post_title !== $post->post_title ) {
			$update_data = [
				'ID'         => $post_id,
				'post_title' => $new_post_title,
			];

			remove_action( 'save_post_anwp_transfer', [ $this, 'save_transfer_data' ] );

			wp_update_post( $update_data );

			// re-hook this function
			add_action( 'save_post_anwp_transfer', [ $this, 'save_transfer_data' ] );
		}

		/**
		 * Change post status when player_id and Season is not set.
		 */
		if ( 'publish' === $post->post_status && ( empty( $data_to_save['player_id'] ) || empty( $data_to_save['season_id'] ) ) ) {

			remove_action( 'save_post_anwp_transfer', [ $this, 'save_transfer_data' ] );

			wp_update_post(
				[
					'ID'          => $post_id,
					'post_status' => 'draft',
				]
			);

			add_action( 'save_post_anwp_transfer', [ $this, 'save_transfer_data' ] );

			$wpdb->delete( $wpdb->anwpfl_transfers, [ 'transfer_id' => $post_id ] );
		}

		return $post_id;
	}

	/**
	 * Registers admin columns to display.
	 *
	 * @param  array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 *@since  0.8.11
	 *
	 */
	public function columns( array $columns ): array {

		// Add new columns
		$new_columns = [
			'transfer_player'   => esc_html__( 'Player', 'anwp-football-leagues' ),
			'season'            => esc_html__( 'Season', 'anwp-football-leagues-premium' ),
			'club_in'           => esc_html__( 'Club In', 'anwp-football-leagues-premium' ),
			'club_out'          => esc_html__( 'Club Out', 'anwp-football-leagues-premium' ),
			'transfer_date'     => esc_html__( 'Transfer Date', 'anwp-football-leagues-premium' ),
			'transfer_end_date' => esc_html__( 'Transfer End Date', 'anwp-football-leagues-premium' ),
			'fee'               => esc_html__( 'Fee', 'anwp-football-leagues-premium' ),
			'status'            => esc_html__( 'Transfer Status', 'anwp-football-leagues-premium' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'transfer_player',
			'season',
			'club_in',
			'club_out',
			'transfer_date',
			'transfer_end_date',
			'fee',
			'status',
		];

		$new_columns = [];

		foreach ( $new_columns_order as $c ) {

			if ( isset( $columns[ $c ] ) ) {
				$new_columns[ $c ] = $columns[ $c ];
			}
		}

		return $new_columns;
	}

	/**
	 * Handles admin column display.
	 *
	 * @param string   $column  Column currently being rendered.
	 * @param integer $post_id ID of post to display column for.
	 *
	 * @since  0.8.11
	 */
	public function columns_display( string $column, int $post_id ) {
		global $post;

		switch ( $column ) {
			case 'transfer_player':
				$this->column_title( $post, $post->_fl_player_name ?? '' );
				break;

			case 'season':
				if ( ! empty( $post->_fl_season_id ) ) {
					echo esc_html( anwp_fl()->season->get_seasons_options()[ $post->_fl_season_id ] ?? '' );
				}

				break;

			case 'club_in':
				$club_id = absint( $post->_fl_club_in ?? '' );

				if ( absint( $club_id ) > 2 ) {
					$club_logo = anwp_fl()->club->get_club_logo_by_id( $club_id );

					if ( $club_logo ) {
						echo '<span class="anwp-admin-list-club-logo" style="background-image: url(' . esc_attr( $club_logo ) . ')"></span>';
					}

					echo esc_html( anwp_fl()->club->get_club_title_by_id( $club_id ) );
				} elseif ( 1 === $club_id ) {
					$club_logo = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-unknown.svg' );
					echo '<span class="anwp-admin-list-club-logo" style="background-image: url(' . esc_attr( $club_logo ) . ')"></span>';
					echo '- ' . esc_html__( 'unknown club', 'anwp-football-leagues-premium' ) . ' -';
				} elseif ( 2 === $club_id ) {
					echo esc_html( $post->_fl_club_in_text ?? '' );
				} else {
					$club_logo = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-none.svg' );
					echo '<span class="anwp-admin-list-club-logo" style="background-image: url(' . esc_attr( $club_logo ) . ')"></span>';
					echo '- ' . esc_html__( 'without club', 'anwp-football-leagues-premium' ) . ' -';
				}

				break;

			case 'club_out':
				$club_id = absint( $post->_fl_club_out ?? '' );

				if ( absint( $club_id ) > 2 ) {
					$club_logo = anwp_fl()->club->get_club_logo_by_id( $club_id );
					if ( $club_logo ) {
						echo '<span class="anwp-admin-list-club-logo" style="background-image: url(' . esc_attr( $club_logo ) . ')"></span>';
					}

					echo esc_html( anwp_fl()->club->get_club_title_by_id( $club_id ) );
				} elseif ( 1 === $club_id ) {
					$club_logo = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-unknown.svg' );
					echo '<span class="anwp-admin-list-club-logo" style="background-image: url(' . esc_attr( $club_logo ) . ')"></span>';
					echo '- ' . esc_html__( 'unknown club', 'anwp-football-leagues-premium' ) . ' -';
				} elseif ( 2 === $club_id ) {
					echo esc_html( $post->_fl_club_out_text ?? '' );
				} else {
					$club_logo = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-none.svg' );
					echo '<span class="anwp-admin-list-club-logo" style="background-image: url(' . esc_attr( $club_logo ) . ')"></span>';
					echo '- ' . esc_html__( 'without club', 'anwp-football-leagues-premium' ) . ' -';
				}

				break;

			case 'fee':
				echo esc_html( $post->_fl_fee ?? '' );

				break;

			case 'transfer_date':
				$transfer_date = $post->_fl_transfer_date ?? '0000-00-00';

				if ( ! empty( $transfer_date ) && '0000-00-00' !== $transfer_date ) {
					echo esc_html( date_i18n( 'M j, Y', strtotime( $transfer_date ) ) );
				}

				break;

			case 'transfer_end_date':
				$transfer_end_date = $post->_fl_transfer_end_date ?? '0000-00-00';

				if ( ! empty( $transfer_end_date ) && '0000-00-00' !== $transfer_end_date ) {
					echo esc_html( date_i18n( 'M j, Y', strtotime( $transfer_end_date ) ) );
				}

				break;

			case 'status':
				if ( absint( $post->_fl_transfer_status ?? 1 ) ) {
					echo esc_html__( 'Official', 'anwp-football-leagues-premium' );
				} else {
					echo esc_html__( 'Rumour', 'anwp-football-leagues-premium' );
				}

				break;
		}
	}

	/**
	 * Filters whether to remove the 'Months' drop-down from the post list table.
	 *
	 * @param bool   $disable   Whether to disable the drop-down. Default false.
	 * @param string $post_type The post type.
	 *
	 * @return bool
	 * @since 0.8.11
	 */
	public function disable_months_dropdown( bool $disable, string $post_type ): bool {

		return 'anwp_transfer' === $post_type ? true : $disable;
	}

	/**
	 * Fires before the Filter button on the Posts and Pages list tables.
	 *
	 * The Filter button allows sorting by date and/or category on the
	 * Posts list table, and sorting by date on the Pages list table.
	 *
	 * @param string $post_type The post type slug.
	 */
	public function add_more_filters( string $post_type ) {

		if ( 'anwp_transfer' === $post_type ) {

			// Seasons dropdown
			$seasons = get_terms(
				[
					'taxonomy'   => 'anwp_season',
					'hide_empty' => false,
				]
			);

			ob_start();

			/*
			|--------------------------------------------------------------------
			| Player ID
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$current_player_id_filter = absint( $_GET['_fl_player_id'] ?? '' ) ? : '';
			?>
			<input name="_fl_player_id" type="text" value="<?php echo esc_attr( $current_player_id_filter ); ?>" class="anwp-w-90 anwp-g-float-left"
				placeholder="<?php echo esc_attr__( 'Player ID', 'anwp-football-leagues' ); ?>">
			<button type="button" class="button anwp-fl-selector anwp-g-float-left anwp-mr-2" data-context="player" data-single="yes">
				<span class="dashicons dashicons-search"></span>
			</button>
			<?php

			/*
			|--------------------------------------------------------------------
			| Seasons
			|--------------------------------------------------------------------
			*/
			if ( ! is_wp_error( $seasons ) && ! empty( $seasons ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification
				$current_season_filter = absint( $_GET['_fl_season_id'] ?? '' ) ?: '';
				?>

				<select name='_fl_season_id' id='anwp_season_filter' class='postform'>
					<option value=''><?php echo esc_attr__( 'All Seasons', 'anwp-football-leagues' ); ?></option>
					<?php foreach ( $seasons as $season ) : ?>
						<option value="<?php echo esc_attr( $season->term_id ); ?>" <?php selected( $season->term_id, $current_season_filter ); ?>>
							<?php echo esc_html( $season->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php
			}

			/*
			|--------------------------------------------------------------------
			| Clubs
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$current_club_in_filter = empty( $_GET['_fl_club_in'] ) ? '' : absint( $_GET['_fl_club_in'] );
			?>
			<input name="_fl_club_in" type="text" value="<?php echo esc_attr( $current_club_in_filter ); ?>" class="anwp-w-90 anwp-g-float-left"
				placeholder="<?php echo esc_attr__( 'Club In ID', 'anwp-football-leagues-premium' ); ?>">
			<button type="button" class="button anwp-fl-selector anwp-g-float-left anwp-mr-2" data-context="club" data-single="yes">
				<span class="dashicons dashicons-search"></span>
			</button>
			<?php

			// phpcs:ignore WordPress.Security.NonceVerification
			$current_club_out_filter = empty( $_GET['_fl_club_out'] ) ? '' : absint( $_GET['_fl_club_out'] );
			?>
			<input name="_fl_club_out" type="text" value="<?php echo esc_attr( $current_club_out_filter ); ?>" class="anwp-w-90 anwp-g-float-left"
				placeholder="<?php echo esc_attr__( 'Club Out ID', 'anwp-football-leagues-premium' ); ?>">
			<button type="button" class="button anwp-fl-selector anwp-g-float-left anwp-mr-2" data-context="club" data-single="yes">
				<span class="dashicons dashicons-search"></span>
			</button>
			<?php

			/*
			|--------------------------------------------------------------------
			| Date From/To
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$date_from = empty( $_GET['_fl_transfer_date_from'] ) ? '' : sanitize_text_field( $_GET['_fl_transfer_date_from'] );
			// phpcs:ignore WordPress.Security.NonceVerification
			$date_to = empty( $_GET['_fl_transfer_date_to'] ) ? '' : sanitize_text_field( $_GET['_fl_transfer_date_to'] );
			?>
			<input type="text" class="postform anwp-g-float-left anwp-w-100 anwp-fl-admin-datepicker" name="_fl_transfer_date_from"
				placeholder="<?php echo esc_attr__( 'Date From', 'anwp-football-leagues' ); ?>" value="<?php echo esc_attr( $date_from ); ?>"/>
			<input type="text" class="postform anwp-g-float-left anwp-w-100 anwp-fl-admin-datepicker anwp-mr-2" name="_fl_transfer_date_to"
				placeholder="<?php echo esc_attr__( 'Date To', 'anwp-football-leagues' ); ?>" value="<?php echo esc_attr( $date_to ); ?>"/>
			<?php

			/*
			|--------------------------------------------------------------------
			| Date End From/To
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$date_end_from = empty( $_GET['_fl_transfer_end_date_from'] ) ? '' : sanitize_text_field( $_GET['_fl_transfer_end_date_from'] );
			// phpcs:ignore WordPress.Security.NonceVerification
			$date_end_to = empty( $_GET['_fl_transfer_end_date_to'] ) ? '' : sanitize_text_field( $_GET['_fl_transfer_end_date_to'] );
			?>
			<input type="text" class="postform anwp-g-float-left anwp-w-100 anwp-fl-admin-datepicker" name="_fl_transfer_end_date_from"
				placeholder="<?php echo esc_attr__( 'Date End From', 'anwp-football-leagues-premium' ); ?>" value="<?php echo esc_attr( $date_end_from ); ?>"/>
			<input type="text" class="postform anwp-g-float-left anwp-w-100 anwp-fl-admin-datepicker" name="_fl_transfer_end_date_to"
				placeholder="<?php echo esc_attr__( 'Date End To', 'anwp-football-leagues-premium' ); ?>" value="<?php echo esc_attr( $date_end_to ); ?>"/>
			<?php

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}
	}

	/**
	 * Handles the title column output.
	 * Overrided WP method of WP_Posts_List_Table.
	 *
	 * phpcs:disable
	 *
	 * @param WP_Post $post The current WP_Post object.
	 *
	 * @global string $mode List table view mode.
	 *
	 * @since 0.9.4
	 *
	 */
	public function column_title( WP_Post $post, string $player_name ) {

		$can_edit_post = current_user_can( 'edit_post', $post->ID );

		if ( $can_edit_post && 'trash' !== $post->post_status ) {
			$lock_holder = wp_check_post_lock( $post->ID );

			if ( $lock_holder ) {
				$lock_holder   = get_userdata( $lock_holder );
				$locked_avatar = get_avatar( $lock_holder->ID, 18 );
				$locked_text   = esc_html( sprintf( __( '%s is currently editing' ), $lock_holder->display_name ) );
			} else {
				$locked_avatar = $locked_text = '';
			}

			echo '<div class="locked-info"><span class="locked-avatar">' . $locked_avatar . '</span> <span class="locked-text">' . $locked_text . "</span></div>\n";
		}

		echo '<strong>';

		$title = empty( $player_name ) ? esc_html__( '- player not selected -', 'anwp-football-leagues-premium' ) : $player_name;

		if ( $can_edit_post && 'trash' !== $post->post_status ) {
			printf(
				'<a class="row-title" href="%s" aria-label="%s">%s</a>',
				get_edit_post_link( $post->ID ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $title ) ),
				$title
			);
		} else {
			printf(
				'<span>%s</span>',
				$title
			);
		}
	}

	/**
	 * Get transfer data.
	 *
	 * @param int $transfer_id
	 *
	 * @since 0.16.0
	 * @return array [
	 *  "transfer_id" => 461,
	 *  "player_id" => 89,
	 *  "season_id" => 10,
	 *  "club_in" => 11,
	 *  "club_out" => 59,
	 *  "club_in_text" => "",
	 *  "club_out_text" => "",
	 *  "fee" => "",
	 *  "transfer_date" => "2023-05-27",
	 *  "transfer_end_date" => "2023-05-27",
	 *  "transfer_status" => 1,
	 *  "transfer_window" => 0,
	 *  "api_transfer_hash" => "",
	 *  "api_transfer_hash_full" => "",
	 *  ]
	 */
	public function get_transfer_data( int $transfer_id ): array {
		global $wpdb;

		static $transfers = [];

		if ( ! empty( $transfers[ $transfer_id ] ) ) {
			return $transfers[ $transfer_id ];
		}

		$transfers[ $transfer_id ] = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT *
				FROM $wpdb->anwpfl_transfers
				WHERE transfer_id = %d
				",
				$transfer_id
			),
			ARRAY_A
		) ? : [];

		return $transfers[ $transfer_id ];
	}

	/**
	 * Get transfers.
	 *
	 * @param array $options
	 *
	 * @return array
	 * @since 0.10.2
	 */
	public function get_transfers( array $options ): array {

		global $wpdb;

		$options = wp_parse_args(
			$options,
			[
				'club_id'        => '',
				'season_id'      => '',
				'player_id'      => '',
				'limit'          => 0,
				'date_from'      => '',
				'date_to'        => '',
				'competition_id' => '',
				'window'         => '',
				'order'          => 'ASC',
				'version'        => 2,
				'link'           => 0,
			]
		);

		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'FL-PRO-CLUB_get_transfers__' . md5( maybe_serialize( $options ) );

		if ( class_exists( 'AnWPFL_Cache' ) && anwp_fl()->cache->get( $cache_key, 'anwp_transfer' ) ) {
			return anwp_fl()->cache->get( $cache_key, 'anwp_transfer' );
		}

		/*
		|--------------------------------------------------------------------
		| Load data in default way
		|--------------------------------------------------------------------
		*/
		$query = "
		SELECT *
		FROM $wpdb->anwpfl_transfers p
		WHERE 1=1 
		";

		/**==================
		 * WHERE filter by player ID
		 *================ */
		if ( absint( $options['player_id'] ) ) {
			$query .= $wpdb->prepare( ' AND player_id = %d ', $options['player_id'] );
		}

		/**==================
		 * WHERE filter by season ID
		 *================ */
		if ( absint( $options['season_id'] ) ) {
			$query .= $wpdb->prepare( ' AND season_id = %d ', $options['season_id'] );
		}

		/**==================
		 * WHERE filter by club ID or competition ID
		 *================ */
		$clubs = [];

		if ( absint( $options['competition_id'] ) ) {
			if ( 'main' === get_post_meta( $options['competition_id'], '_anwpfl_multistage', true ) ) {
				$clubs = anwp_fl()->competition->get_competition_multistage_clubs( $options['competition_id'] );
			} else {
				$clubs = anwp_fl()->competition->get_competition_clubs( $options['competition_id'], 'all' );
			}
		} elseif ( absint( $options['club_id'] ) ) {
			$clubs = wp_parse_id_list( $options['club_id'] );
		}

		if ( ! empty( $clubs ) && is_array( $clubs ) ) {
			$format = implode( ', ', array_fill( 0, count( $clubs ), '%d' ) );

			$query .= $wpdb->prepare( " AND ( club_in IN ({$format}) ", $clubs ); // phpcs:ignore
			$query .= $wpdb->prepare( " OR club_out IN ({$format}) )", $clubs ); // phpcs:ignore
		}

		/**==================
		 * WHERE filter by window
		 *================ */
		if ( in_array( mb_strtolower( $options['window'] ), [ 'pre', 'mid' ], true ) ) {
			$query .= $wpdb->prepare( ' AND transfer_window = %d ', 'pre' === mb_strtolower( $options['window'] ) ? 1 : 2 );
		}

		/**==================
		 * WHERE filter by date
		 *================ */
		if ( trim( $options['date_to'] ) ) {
			$date_to = explode( ' ', $options['date_to'] )[0];

			if ( anwp_fl()->helper->validate_date( $date_to, 'Y-m-d' ) ) {
				$query .= $wpdb->prepare( ' AND transfer_date <= %s ', $date_to );
			}
		}

		if ( trim( $options['date_from'] ) ) {
			$date_from = explode( ' ', $options['date_from'] )[0];

			if ( anwp_fl()->helper->validate_date( $date_from, 'Y-m-d' ) ) {
				$query .= $wpdb->prepare( ' AND transfer_date >= %s ', $date_from );
			}
		}

		/**==================
		 * ORDER
		 *================ */
		$ordering = in_array( mb_strtoupper( $options['order'] ), [ 'ASC', 'DESC' ], true ) ? mb_strtoupper( $options['order'] ) : 'ASC';

		$query .= " ORDER BY transfer_date $ordering"; // phpcs:ignore

		/**==================
		 * LIMIT clause
		 *================ */
		if ( absint( $options['limit'] ) ) {
			$query .= $wpdb->prepare( ' LIMIT %d', $options['limit'] );
		}

		/**==================
		 * Bump Query
		 *================ */
		$transfers = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL

		// Get player data
		$player_ids   = wp_list_pluck( $transfers, 'player_id' );
		$players_data = [];

		if ( ! empty( $player_ids ) && is_array( $player_ids ) ) {
			$players_data = anwp_fl()->player->get_players_by_ids( array_unique( $player_ids ), (bool) $options['link'] );
		}

		foreach ( $transfers as &$transfer ) {
			$transfer['player_link']        = $players_data[ $transfer['player_id'] ]['link'] ?? '';
			$transfer['player_photo']       = $players_data[ $transfer['player_id'] ]['photo'] ?? '';
			$transfer['player_birth_date']  = $players_data[ $transfer['player_id'] ]['date_of_birth'] ?? '';
			$transfer['player_name']        = $players_data[ $transfer['player_id'] ]['name'] ?? '';
			$transfer['player_nationality'] = $players_data[ $transfer['player_id'] ]['nationality'] ?? '';
			$transfer['player_position']    = anwp_fl()->player->get_position_l10n( $players_data[ $transfer['player_id'] ]['position'] ?? '' );
		}

		$output = [
			'transfers' => $transfers,
			'clubs'     => $clubs,
		];

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		anwp_fl()->cache->set( $cache_key, $output, 'anwp_transfer' );

		return $output;
	}
}
