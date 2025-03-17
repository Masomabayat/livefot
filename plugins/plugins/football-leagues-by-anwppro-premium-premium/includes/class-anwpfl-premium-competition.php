<?php
/**
 * AnWP Football Leagues Premium :: Competition
 *
 * @since 0.6.0
 */
class AnWPFL_Premium_Competition {

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
	 * @since  0.1.0
	 * @param  AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

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

		// Premium Metabox
		add_action( 'anwpfl/cmb2_tabs_control/competition', [ $this, 'add_premium_metabox_tab' ] );
		add_filter( 'anwpfl/cmb2_tabs_content/competition', [ $this, 'add_premium_metabox_options' ] );

		// Bracket layout
		add_action( 'anwpfl/tmpl-competition/before_stage', [ $this, 'render_bracket' ], 10, 2 );

		// Check MatchWeeks as slides option
		add_filter( 'anwpfl/tmpl-competition/render_list_of_matches', [ $this, 'check_render_list_matches' ], 10, 2 );
		add_action( 'anwpfl/tmpl-competition/after_list_of_matches', [ $this, 'render_matchweeks_as_slides' ], 10, 2 );

		// Inject admin styles and scripts
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		// Save Premium Data
		add_action( 'anwpfl/competition/after_save', [ $this, 'save_stage_premium_data' ], 10, 3 );

		// Override Vue App id
		add_filter(
			'anwpfl/competition/vue_app_id',
			function () {
				return 'anwpfl-app-competition-premium';
			}
		);

		add_filter( 'anwpfl/competition/fields_to_clone', [ $this, 'add_premium_meta_fields_clone' ] );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
		add_action( 'cmb2_render_anwp_fl_break_api_import_mapping', [ $this, 'break_api_import_mapping' ], 10, 3 );
	}

	/**
	 * Register REST routes.
	 */
	public function add_rest_routes() {
		register_rest_route(
			'anwpfl/v1',
			'/helper/unlink-api-competition',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'unlink_api_competition' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Unlink API Import Mapped Competitions
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 * @since 0.14.10
	 */
	public function unlink_api_competition( WP_REST_Request $request ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Access Denied !!!' );
		}

		global $wpdb;

		$params = $request->get_query_params();

		if ( empty( $params['competition_id'] ) || ! absint( $params['competition_id'] ) ) {
			wp_send_json_error( 'Invalid Competition ID' );
		}

		return $wpdb->delete(
			$wpdb->prefix . 'anwpfl_import_mapping',
			[
				'local_value' => absint( $params['competition_id'] ),
			]
		);
	}

	/**
	 * Field to unlink mapping
	 *
	 * @return mixed
	 * @since 0.14.10
	 */
	public function break_api_import_mapping( $field, $value, $object_id ) {
		?>
		<div class="my-1 d-flex">
			<div class="anwp-text-xs anwp-opacity-80">
				Use this option ONLY when you want to split Competition on API Import into several parts and only after importing all data to this Competition.
				Ask plugin support for more info about this functionality.
			</div>
			<button class="btn btn-danger btn-sm mx-2" data-anwpfl-unlink-competition>Unlink</button>
		</div>
		<script>
			( function( $ ) {
				$( document.body ).on( 'click', '[data-anwpfl-unlink-competition]', function( e ) {
					e.preventDefault();

					var $this = $( this );
					$this.data( 'oldText', $this.text() );

					jQuery.ajax( {
						dataType: 'json',
						method: 'GET',
						data: { competition_id: <?php echo absint( $object_id ); ?> },
						beforeSend: function( xhr ) {
							xhr.setRequestHeader( 'X-WP-Nonce', anwp.rest_nonce );
							$this.text( 'processing request ...' );
						},
						url: anwp.rest_root + 'anwpfl/v1/helper/unlink-api-competition'
					} ).always( function() {
						$this.text( $this.data( 'oldText' ) );
					} ).fail( function( e ) {
						console.log( e );
					} );
				} );
			} )( jQuery );
		</script>
		<?php
	}

	/**
	 * Add premium meta fields to clone
	 *
	 * @param array $meta_keys Meta keys array
	 *
	 * @return array
	 * @since 0.11.5
	 */
	public function add_premium_meta_fields_clone( $meta_keys ) {
		$meta_keys[] = '_anwpfl_role_competition_supervisor';
		$meta_keys[] = '_anwpfl_matchweeks_as_slides';
		$meta_keys[] = '_anwpfl_bracket_options';
		$meta_keys[] = '_anwpfl_bracket';

		return $meta_keys;
	}

	/**
	 * Check render list of matches.
	 *
	 * @param bool    $render
	 * @param WP_Post $competition
	 *
	 * @return bool
	 * @since 0.8.0
	 */
	public function check_render_list_matches( $render, $competition ) {

		if ( 'round-robin' === get_post_meta( $competition->ID, '_anwpfl_type', true ) ) {
			$render_value = get_post_meta( $competition->ID, '_anwpfl_matchweeks_as_slides', true );

			// Check global option
			if ( '' === $render_value ) {
				$render_value = AnWPFL_Premium_Options::get_value( 'matchweeks_as_slides' );
			}

			if ( 'yes' === $render_value ) {
				return false;
			}
		}

		return $render;
	}

	/**
	 * Render MatchWeeks as slides
	 *
	 * @param object  $group
	 * @param WP_Post $competition
	 *
	 * @since 0.6.0 (2018-10-26)
	 */
	public function render_matchweeks_as_slides( $group, $competition ) {

		if ( 'round-robin' === get_post_meta( $competition->ID, '_anwpfl_type', true ) ) {
			$render_value = get_post_meta( $competition->ID, '_anwpfl_matchweeks_as_slides', true );

			// Check global option
			if ( '' === $render_value ) {
				$render_value = AnWPFL_Premium_Options::get_value( 'matchweeks_as_slides' );
			}

			if ( 'yes' === $render_value ) {

				$data = [
					'competition_id' => $competition->ID,
					'matchweek'      => '-1',
					'match_card'     => 'slim',
					'slides_to_show' => 8,
					'group_id'       => $group->id,
				];

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo anwp_football_leagues()->template->shortcode_loader( 'premium-matchweeks-slides', $data );
			}
		}
	}

	/**
	 * Render Bracket layout
	 *
	 * @param WP_Post $competition
	 * @param integer $main_stage_id
	 *
	 * @since 0.6.0 (2018-10-11)
	 */
	public function render_bracket( $competition, $main_stage_id ) {

		$competition_type = get_post_meta( $competition->ID, '_anwpfl_type', true );
		$bracket_status   = get_post_meta( $competition->ID, '_anwpfl_bracket', true );

		// Check Bracket to render
		if ( 'knockout' === $competition_type && ( 'last' === $bracket_status || 'show' === $bracket_status || 'manual' === $bracket_status ) ) {

			$loader_data = [
				'competition_id' => $main_stage_id,
				'final_stage_id' => $competition->ID,
			];

			if ( 'last' === $bracket_status ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo anwp_football_leagues()->template->shortcode_loader( 'premium-bracket-last', $loader_data );
			} else {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo anwp_football_leagues()->template->shortcode_loader( 'premium-bracket', $loader_data );
			}
		}
	}

	/**
	 * Renders premium tab control.
	 *
	 * @since 0.7.1
	 */
	public function add_premium_metabox_tab() {
		ob_start();
		?>
		<div class="p-3 anwp-metabox-tabs__control-item anwp-metabox-tabs__control-item--premium" data-target="#anwp-tabs-premium-competition_metabox">
			<svg class="anwp-icon anwp-icon--octi d-inline-block"><use xlink:href="#icon-star"></use></svg>
			<span class="d-block"><?php echo esc_html__( 'Premium Options', 'anwp-football-leagues-premium' ); ?></span>
		</div>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Adds fields to the competition metabox.
	 *
	 * @return array
	 * @since 0.7.1
	 */
	public function add_premium_metabox_options() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_anwpfl_';

		$fields = [
			[
				'name'        => esc_html__( 'Competition Supervisor (User role)', 'anwp-football-leagues-premium' ),
				'description' => esc_html__( 'Grant user rights to edit competition matches at frontent', 'anwp-football-leagues-premium' ),
				'id'          => $prefix . 'role_competition_supervisor',
				'type'        => 'anwp_user_ajax_search',
				'multiple'    => true,
				'before_row'  => '<div id="anwp-tabs-premium-competition_metabox" class="anwp-metabox-tabs__content-item d-none">',
				'limit'       => 5,
				'query_args'  => [
					'role__not_in' => [ 'Administrator', 'Super Admin' ],
				],
			],
			[
				'name'       => 'Unlink the API Import Mapping',
				'id'         => $prefix . 'break_api_mapping',
				'type'       => 'anwp_fl_break_api_import_mapping',
				'show_on_cb' => function () {
					return ! empty( AnWPFL_Premium_API::get_config_value( 'key', '' ) );
				},
			],
			[
				'name'      => esc_html__( 'MatchWeeks as sliding tabs', 'anwp-football-leagues-premium' ) . ' (Deprecated - use Layout Builder)',
				'id'        => $prefix . 'matchweeks_as_slides',
				'type'      => 'select',
				'default'   => '',
				'options'   => [
					''    => esc_html__( 'inherit (from settings)', 'anwp-football-leagues' ),
					'no'  => esc_html__( 'no', 'anwp-football-leagues' ),
					'yes' => esc_html__( 'yes', 'anwp-football-leagues' ),
				],
				'after_row' => '</div>',
			],
		];

		return $fields;
	}

	/**
	 * Save Premium Tournament Stage data
	 *
	 * @param WP_Post $post
	 * @param array   $data
	 * @param array   $post_data
	 *
	 * @since 0.8.9
	 */
	public function save_stage_premium_data( $post, $data, $post_data ) {

		$premium_data = [];

		// Bracket
		$premium_data['_anwpfl_bracket'] = isset( $post_data['_anwpfl_bracket'] ) ? sanitize_key( $post_data['_anwpfl_bracket'] ) : '';

		$bracket_options = wp_json_encode( json_decode( wp_unslash( $post_data['_anwpfl_bracket_options'] ) ) );

		if ( $bracket_options ) {
			update_post_meta( $post->ID, '_anwpfl_bracket_options', wp_slash( $bracket_options ) );
		}

		// Current MatchWeek
		$premium_data['_anwpfl_matchweek_current'] = isset( $post_data['_anwpfl_matchweek_current'] ) ? sanitize_key( $post_data['_anwpfl_matchweek_current'] ) : '';

		// Save Premium Data
		foreach ( $premium_data as $key => $value ) {
			update_post_meta( $post->ID, $key, $value );
		}
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @since 0.8.9
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		$current_screen = get_current_screen();

		if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) && 'anwp_competition' === $current_screen->id ) {

			$post = get_post();

			wp_localize_script(
				'anwpfl_premium_admin_vue',
				'anwpCompetitionPro',
				[
					'bracketData'      => get_post_meta( $post->ID, '_anwpfl_bracket_options', true ),
					'bracketLayout'    => get_post_meta( $post->ID, '_anwpfl_bracket', true ),
					'currentMatchweek' => get_post_meta( $post->ID, '_anwpfl_matchweek_current', true ),
				]
			);
		}
	}

	/**
	 * Try to set current MatchWeek from the last game
	 *
	 * @param int $competition_id Competition ID
	 * @param int $game_id        Game ID
	 *
	 * @return bool
	 * @since 0.11.0
	 */
	public function update_current_matchweek( int $competition_id, int $game_id = 0 ): bool {

		$new_matchweek = 0;

		if ( ! $competition_id && $game_id ) {
			$game_data = anwp_fl()->match->get_game_data( $game_id );

			$competition_id = $game_data['competition_id'];
			$new_matchweek  = $game_data['match_week'];
		}

		if ( empty( $competition_id ) ) {
			return false;
		}

		if ( 'round-robin' !== get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
			return false;
		}

		// Get Old MatchWeek
		$old_matchweek = get_post_meta( $competition_id, '_anwpfl_matchweek_current', true );

		// Get New MatchWeek
		if ( ! $new_matchweek ) {
			$ids = anwp_fl()->competition->tmpl_get_competition_matches_extended(
				[
					'competition_id' => $competition_id,
					'limit'          => 1,
					'type'           => 'result',
					'sort_by_date'   => 'desc',
				],
				'ids'
			);

			if ( absint( $ids[0] ?? 0 ) ) {
				$new_matchweek = anwp_fl()->match->get_game_data( $ids[0] )['match_week'];
			}
		}

		if ( absint( $new_matchweek ) && $new_matchweek > $old_matchweek ) {
			return update_post_meta( $competition_id, '_anwpfl_matchweek_current', absint( $new_matchweek ) );
		}

		return false;
	}

	/**
	 * Get Club Competition IDs by Season ID
	 *
	 * @param string $club_id   Club ID
	 * @param string $season_id Season ID
	 *
	 * @return array
	 * @since 0.11.12
	 */
	public function get_club_competitions_by_season_id( $club_id, $season_id ) {

		global $wpdb;

		$query = $wpdb->prepare(
			"
			SELECT DISTINCT CASE WHEN main_stage_id != 0 THEN main_stage_id ELSE competition_id END id
			FROM {$wpdb->prefix}anwpfl_matches
			WHERE season_id = %d AND ( home_club = %d OR away_club = %d )
			",
			$season_id,
			$club_id,
			$club_id
		);

		return $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/**
	 * Get list of competitions.
	 *
	 * @return array $output_data -
	 */
	public function get_competitions() {

		static $output_data = null;

		if ( null === $output_data ) {

			$output_data = [];

			$all_competitions = get_posts(
				[
					'numberposts'      => - 1,
					'post_type'        => 'anwp_competition',
					'suppress_filters' => false,
					'post_status'      => [ 'publish', 'stage_secondary' ],
					'orderby'          => 'title',
					'order'            => 'ASC',
				]
			);

			/** @var WP_Post $competition */
			foreach ( $all_competitions as $competition ) {

				$obj = (object) [
					'id'          => $competition->ID,
					'title'       => $competition->post_title,
					'title_full'  => $competition->post_title,
					'groups'      => json_decode( get_post_meta( $competition->ID, '_anwpfl_groups', true ) ),
					'rounds'      => json_decode( get_post_meta( $competition->ID, '_anwpfl_rounds', true ) ),
					'type'        => get_post_meta( $competition->ID, '_anwpfl_type', true ),
					'season_ids'  => [],
					'league_id'   => 0,
					'logo'        => '',
					'league_text' => '',
					'season_text' => '',
					'multistage'  => get_post_meta( $competition->ID, '_anwpfl_multistage', true ),
				];

				// Check multistage
				if ( '' !== $obj->multistage ) {

					$stage_title = get_post_meta( $competition->ID, '_anwpfl_stage_title', true );

					if ( $stage_title ) {
						$obj->title_full .= ' - ' . $stage_title;
					}
				}

				// Get Season and League
				$terms = wp_get_post_terms( $competition->ID, [ 'anwp_league', 'anwp_season' ] );

				if ( is_array( $terms ) ) {

					/** @var WP_Term $term */
					foreach ( $terms as $term ) {

						if ( 'anwp_league' === $term->taxonomy && $term->term_id ) {
							$obj->league_id   = $term->term_id;
							$obj->league_text = $term->name;
						}

						if ( 'anwp_season' === $term->taxonomy ) {
							$obj->season_ids[] = $term->term_id;
							$obj->season_text  = $term->name;
						}
					}
				}

				$obj->season_ids = implode( ',', $obj->season_ids );

				if ( 'stage_secondary' === $competition->post_status ) {
					$obj->title_full  = '- ' . $obj->title_full;
					$obj->stage_order = get_post_meta( $competition->ID, '_anwpfl_stage_order', true );

					$secondary_stages[ get_post_meta( $competition->ID, '_anwpfl_multistage_main', true ) ][] = $obj;
				} else {
					$obj->logo     = get_post_meta( $competition->ID, '_anwpfl_logo', true );
					$output_data[] = $obj;
				}
			}

			$clone_data = $output_data;

			foreach ( $clone_data as $main_stage_competition ) {
				if ( ! empty( $secondary_stages[ $main_stage_competition->id ] ) ) {

					$stages = $secondary_stages[ $main_stage_competition->id ];
					$stages = wp_list_sort( $stages, 'stage_order' );
					$index  = array_search( $main_stage_competition->id, wp_list_pluck( $output_data, 'id' ) );

					array_splice( $output_data, $index + 1, 0, $stages );
				}
			}
		}

		return $output_data;
	}

	/**
	 * Get league country code.
	 * Proxy function to prevent code error.
	 *
	 * @param $league_id
	 *
	 * @return string
	 * @since 0.13.0
	 */
	public function get_league_country_code( $league_id ) {
		if ( method_exists( anwp_football_leagues()->league, 'get_league_country_code' ) ) {
			return anwp_football_leagues()->league->get_league_country_code( $league_id );
		}

		return '';
	}
}
