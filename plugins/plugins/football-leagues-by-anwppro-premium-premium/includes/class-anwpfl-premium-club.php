<?php
/**
 * AnWP Football Leagues Premium :: Club
 *
 * @since 0.1.0
 */
class AnWPFL_Premium_Club {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
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
		add_filter( 'anwpfl/cmb2_tabs_content/club', [ $this, 'add_premium_metabox_options' ] );

		// Club trophies
		add_action( 'anwpfl/club/metabox_bottom', [ $this, 'render_trophies_metabox' ], 5 );
		add_action( 'anwpfl/club/metabox_nav_items', [ $this, 'render_trophies_metabox_nav' ], 5 );
		add_action( 'admin_enqueue_scripts', [ $this, 'localize_trophies_script' ] );

		add_action( 'anwpfl/club/on_save', [ $this, 'save_premium_data' ], 10, 2 );

		add_action( 'anwpfl/shortcodes/club_shortcode_options', [ $this, 'add_club_shortcode_premium_sections' ] );

		// Modify Squad App data
		add_filter( 'anwpfl/club/squad_app_data', [ $this, 'modify_squad_app_data' ], 10, 1 );

		// Create CMB2 Metabox
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );
	}

	/**
	 * Modify Squad App data
	 *
	 * @return array
	 * @since 0.12.6
	 */
	public function modify_squad_app_data( $squad_data ) {

		$squad_data['transfers_available'] = 'yes';

		return $squad_data;
	}

	/**
	 * Rendering premium Club sections in shortcode helper.
	 *
	 * @since 0.11.10
	 */
	public function add_club_shortcode_premium_sections() {
		ob_start();
		?>
		<option value="birthdays"><?php echo esc_html__( 'Birthdays', 'anwp-football-leagues-premium' ); ?></option>
		<option value="stats"><?php echo esc_html__( 'Players Stats', 'anwp-football-leagues-premium' ); ?></option>
		<option value="transfers"><?php echo esc_html__( 'Transfers', 'anwp-football-leagues-premium' ); ?></option>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render Trophies metabox navigation
	 *
	 * @since 0.12.6
	 */
	public function render_trophies_metabox_nav( $nav_items ) {

		$insert_index = array_search( 'anwp-fl-bottom_content-metabox', array_column( $nav_items, 'slug' ), true );

		array_splice(
			$nav_items,
			$insert_index + 1,
			0,
			[
				[
					'classes' => 'anwp-text-orange-700 anwp-fl-metabox-nav-pro',
					'icon'    => 'jersey',
					'label'   => __( 'Club Shirt', 'anwp-football-leagues-premium' ),
					'slug'    => 'anwp-fl-shirt-metabox',
				],
				[
					'classes' => 'anwp-text-orange-700 anwp-fl-metabox-nav-pro',
					'icon'    => 'star',
					'label'   => __( 'Premium Options', 'anwp-football-leagues-premium' ),
					'slug'    => 'anwp-fl-premium-metabox',
				],
			]
		);

		$nav_items[] = [
			'classes' => 'anwp-text-orange-700 anwp-fl-metabox-nav-pro',
			'icon'    => 'trophy-outline',
			'label'   => __( 'Trophies', 'anwp-football-leagues-premium' ),
			'slug'    => 'anwp-fl-trophies-metabox',
		];

		return $nav_items;
	}

	/**
	 * Render Trophies metabox
	 *
	 * @since 0.12.6
	 */
	public function render_trophies_metabox() {
		?>
		<div id="anwpfl-pro-app-trophies"></div>
		<?php
	}

	/**
	 * Localize Trophies Script
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @since 0.12.6
	 */
	public function localize_trophies_script( $hook_suffix ) {

		$current_screen = get_current_screen();

		if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) && 'anwp_club' === $current_screen->id ) {

			$l10n = [
				'add_new_trophy'               => esc_html__( 'Add Another Trophy', 'anwp-football-leagues-premium' ),
				'are_you_sure'                 => esc_html__( 'Are you sure?', 'anwp-football-leagues' ),
				'confirm_delete'               => esc_html__( 'Confirm Delete', 'anwp-football-leagues' ),
				'close'                        => esc_html__( 'Close', 'anwp-football-leagues' ),
				'delete'                       => esc_html__( 'Delete', 'anwp-football-leagues' ),
				'do_you_really_want_to_delete' => esc_html__( 'Do you really want to delete?', 'anwp-football-leagues-premium' ),
				'number'                       => esc_html_x( 'Number', 'Trophy Number', 'anwp-football-leagues-premium' ),
				'number_tooltip'               => esc_html_x( 'Number tooltip (e.g.: years)', 'Trophy Number', 'anwp-football-leagues-premium' ),
				'remove'                       => esc_html__( 'Remove', 'anwp-football-leagues' ),
				'remove_image'                 => esc_html__( 'Remove Image', 'anwp-football-leagues' ),
				'select_image'                 => esc_html__( 'Select Image', 'anwp-football-leagues' ),
				'title'                        => esc_html__( 'Title', 'anwp-football-leagues' ),
				'trophies'                     => esc_html__( 'Trophies', 'anwp-football-leagues-premium' ),
				'trophy_image'                 => esc_html__( 'Trophy Image', 'anwp-football-leagues-premium' ),
			];

			$post_id = get_the_ID();

			wp_localize_script(
				'anwpfl_admin_vue',
				'anwpTrophiesData',
				[
					'trophies' => get_post_meta( $post_id, '_fl_pro_trophies', true ),
					'l10n'     => $l10n,
				]
			);
		}
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 * @param array $posted_data
	 *
	 * @since  0.12.6
	 */
	public function save_premium_data( $post_id, $posted_data ) {
		// Prepare data & Encode with some WP sanitization
		update_post_meta( $post_id, '_fl_pro_trophies', isset( $posted_data['_fl_pro_trophies'] ) ? json_decode( wp_unslash( $posted_data['_fl_pro_trophies'] ), true ) : [] );
	}

	/**
	 * Adds fields to the club metabox.
	 *
	 * @return array
	 * @since 0.5.11
	 */
	public function add_premium_metabox_options() {
		$prefix = '_anwpfl_';

		// Init fields array
		$fields =
			[
				[
					'name'       => '',
					'type'       => 'title',
					'classes'    => 'd-none',
					'id'         => $prefix . 'section_display_shirt_home_alt',
					'before_row' => anwp_football_leagues_premium()->create_metabox_header(
						[
							'icon'  => 'jersey',
							'label' => __( 'Club Shirt', 'anwp-football-leagues-premium' ),
							'slug'  => 'anwp-fl-shirt-metabox',
						]
					),
				],
				[
					'name'       => esc_html__( 'Shirt - Home', 'anwp-football-leagues-premium' ),
					'type'       => 'title',
					'id'         => $prefix . 'section_display_shirt_home',
					'before_row' => $this->render_shirt_docs_links(),
				],
				[
					'name'            => esc_html__( 'SVG image', 'anwp-football-leagues-premium' ),
					'id'              => $prefix . 'shirt_home',
					'type'            => 'textarea_small',
					'label_cb'        => [ anwp_football_leagues(), 'cmb2_field_label' ],
					'label_help'      => '<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/2/articles/37-club-match-formation">' . esc_html__( 'more info', 'anwp-football-leagues' ) . '</a>',
					'sanitization_cb' => false,
					'escape_cb'       => false,
					'default'         => '',
				],
				[
					'name'            => esc_html__( 'Shirt Solid Color', 'anwp-football-leagues-premium' ),
					'id'              => $prefix . 'shirt_home_color',
					'type'            => 'colorpicker',
					'label_cb'        => [ anwp_football_leagues(), 'cmb2_field_label' ],
					'label_help'      => esc_html__( 'used when SVG image is empty', 'anwp-football-leagues-premium' ),
					'sanitization_cb' => false,
					'escape_cb'       => false,
					'default'         => '',
				],

				[
					'name'    => esc_html__( 'Number Color', 'anwp-football-leagues-premium' ),
					'id'      => $prefix . 'number_shirt_home_color',
					'type'    => 'colorpicker',
					'default' => '',
				],
				[
					'name'    => esc_html__( 'Stroke Number Color', 'anwp-football-leagues-premium' ),
					'id'      => $prefix . 'number_shirt_home_stroke_color',
					'type'    => 'colorpicker',
					'default' => '',
				],
				[
					'name' => esc_html__( 'Shirt - Away', 'anwp-football-leagues-premium' ),
					'type' => 'title',
					'id'   => $prefix . 'section_display_shirt_away',
				],
				[
					'name'            => esc_html__( 'SVG image', 'anwp-football-leagues-premium' ),
					'id'              => $prefix . 'shirt_away',
					'type'            => 'textarea_small',
					'label_cb'        => [ anwp_football_leagues(), 'cmb2_field_label' ],
					'label_help'      => '<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/2/articles/37-club-match-formation">' . esc_html__( 'more info', 'anwp-football-leagues' ) . '</a>',
					'sanitization_cb' => false,
					'escape_cb'       => false,
					'default'         => '',
				],
				[
					'name'            => esc_html__( 'Shirt Solid Color', 'anwp-football-leagues-premium' ),
					'id'              => $prefix . 'shirt_away_color',
					'type'            => 'colorpicker',
					'label_cb'        => [ anwp_football_leagues(), 'cmb2_field_label' ],
					'label_help'      => esc_html__( 'used when SVG image is empty', 'anwp-football-leagues-premium' ),
					'sanitization_cb' => false,
					'escape_cb'       => false,
					'default'         => '',
				],
				[
					'name'    => esc_html__( 'Number Color', 'anwp-football-leagues-premium' ),
					'id'      => $prefix . 'number_shirt_away_color',
					'type'    => 'colorpicker',
					'default' => '',
				],
				[
					'name'      => esc_html__( 'Stroke Number Color', 'anwp-football-leagues-premium' ),
					'id'        => $prefix . 'number_shirt_away_stroke_color',
					'type'      => 'colorpicker',
					'default'   => '',
					'after_row' => '</div></div>',
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
					'before_row'   => anwp_football_leagues_premium()->create_metabox_header(
						[
							'icon'  => 'star',
							'label' => __( 'Premium Options', 'anwp-football-leagues-premium' ),
							'slug'  => 'anwp-fl-premium-metabox',
						]
					),
				],
				[
					'name' => esc_html__( 'User Roles', 'anwp-football-leagues-premium' ),
					'type' => 'title',
					'id'   => $prefix . 'section_display_roles',
				],
				[
					'name'        => esc_html__( 'Club Captain (User role)', 'anwp-football-leagues-premium' ),
					'description' => esc_html__( 'Grant user rights to edit club matches at frontent (only club data)', 'anwp-football-leagues-premium' ),
					'id'          => $prefix . 'role_club_captain',
					'type'        => 'anwp_user_ajax_search',
					'multiple'    => true,
					'limit'       => 5,
					'query_args'  => [
						'role__not_in' => [ 'Administrator', 'Super Admin' ],
					],
					'after_row'   => '</div></div>',
				],
			];

		return $fields;
	}

	/**
	 * Rendering Shirt Docs link.
	 *
	 * @return string
	 * @since 0.8.10
	 */
	public function render_shirt_docs_links() {
		$output = '<div class="anwp-shortcode-docs-link my-2">';

		$output .= '<svg class="anwp-icon anwp-icon--octi anwp-icon--s16"><use xlink:href="#icon-book"></use></svg>';
		$output .= '<strong class="mx-2">' . esc_html__( 'Documentation', 'anwp-football-leagues' ) . ':</strong> ';
		$output .= '<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/2/articles/37-club-match-formation#club_shirt_selecting_priority">' . esc_html__( 'Using Club Shirt in Formation', 'anwp-football-leagues-premium' ) . '</a>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Get club shirt.
	 * Used in Match Formation.
	 *
	 * @param int    $club_id
	 * @param string $side Home or away
	 * @param int    $match_id
	 * @param object $extra
	 *
	 * @return array
	 * @since 0.6.0
	 */
	public function get_club_shirt( $club_id, $side, $match_id, $extra = [], $fallback_color = '', $game_jersey = '' ) {

		// Initialize output data
		$output = [
			'svg'       => '',
			'css'       => '',
			'jcolor'    => '',
			'css_gk'    => '',
			'jcolor_gk' => '',
		];

		$symbol_id = 'anwpfl-shirt-' . $side;

		if ( 'custom' === $game_jersey ) {
			// Set custom colors
			$output['jcolor']    = ! empty( $extra['player']['primary'] ) ? ( '#' . $extra['player']['primary'] ) : $fallback_color;
			$output['jcolor_gk'] = ! empty( $extra['goalkeeper']['primary'] ) ? ( '#' . $extra['goalkeeper']['primary'] ) : $fallback_color;

			// Set CSS
			$output['css']    = 'color: ' . ( ! empty( $extra['player']['number'] ) ? ( '#' . $extra['player']['number'] ) : '' ) . ' !important;';
			$output['css_gk'] = 'color: ' . ( ! empty( $extra['goalkeeper']['number'] ) ? ( '#' . $extra['goalkeeper']['number'] ) : '' ) . ' !important;';

			return $output;
		} elseif ( 'color' === $game_jersey ) {
			return $output;
		} elseif ( 'custom_color' === $game_jersey ) {

			$jersey_colors = get_post_meta( $match_id, '_anwpfl_' . $side . '_shirt_color', true )
				? json_decode( get_post_meta( $match_id, '_anwpfl_' . $side . '_shirt_color', true ) ) : false;

			if ( ! empty( $jersey_colors ) ) {
				if ( ! empty( $jersey_colors->field_shirt ) && ! empty( $jersey_colors->field_number ) ) {
					$output['jcolor'] = $jersey_colors->field_shirt;
					$output['css']    = 'color: ' . $jersey_colors->field_number . ' !important;';
				}

				if ( ! empty( $jersey_colors->goalkeeper_shirt ) && ! empty( $jersey_colors->goalkeeper_number ) ) {
					$output['jcolor_gk'] = $jersey_colors->goalkeeper_shirt;
					$output['css_gk']    = 'color: ' . $jersey_colors->goalkeeper_number . ' !important;';
				}

				return $output;
			}
		} elseif ( ( 'away' === $game_jersey && 'home' === $side ) || ( 'home' === $game_jersey && 'away' === $side ) ) {
			$side = $game_jersey;
		}

		// Getting data
		$home_svg = trim( get_post_meta( $club_id, '_anwpfl_shirt_home', true ) );
		$away_svg = trim( get_post_meta( $club_id, '_anwpfl_shirt_away', true ) );

		$home_jcolor = get_post_meta( $club_id, '_anwpfl_shirt_home_color', true );
		$away_jcolor = get_post_meta( $club_id, '_anwpfl_shirt_away_color', true );

		// Away Club
		if ( 'away' === $side ) {

			if ( $away_svg ) {
				$output['svg'] = str_ireplace( '<svg ', '<svg xmlns="http://www.w3.org/2000/svg" style="display:none"><symbol id="' . $symbol_id . '" ', $away_svg );
				$output['svg'] = str_ireplace( '</svg>', '</symbol></svg>', $output['svg'] );
			} elseif ( $away_jcolor && '#' !== $away_jcolor ) {
				$output['jcolor'] = $away_jcolor;
			}

			if ( get_post_meta( $club_id, '_anwpfl_number_shirt_away_color', true ) ) {
				$output['css'] .= 'color: ' . esc_attr( get_post_meta( $club_id, '_anwpfl_number_shirt_away_color', true ) ) . ';';
			}

			if ( get_post_meta( $club_id, '_anwpfl_number_shirt_away_stroke_color', true ) ) {
				$output['css'] .= sprintf( 'text-shadow: -1px -1px 0 %1$s, 1px -1px 0 %1$s, -1px 1px 0 %1$s, 1px 1px 0 %1$s;', esc_attr( get_post_meta( $club_id, '_anwpfl_number_shirt_away_stroke_color', true ) ) );
			}
		}

		// Home Club
		if ( 'home' === $side || ( empty( $away_svg ) && empty( $output['jcolor'] ) ) ) {

			if ( $home_svg ) {
				$output['svg'] = str_ireplace( '<svg', '<svg xmlns="http://www.w3.org/2000/svg" style="display:none"><symbol id="' . $symbol_id . '" ', $home_svg );
				$output['svg'] = str_ireplace( '</svg>', '</symbol></svg>', $output['svg'] );
			} elseif ( $home_jcolor && '#' !== $home_jcolor ) {
				$output['jcolor'] = $home_jcolor;
			}

			if ( empty( $output['css'] ) ) {
				if ( get_post_meta( $club_id, '_anwpfl_number_shirt_home_color', true ) ) {
					$output['css'] .= 'color: ' . esc_attr( get_post_meta( $club_id, '_anwpfl_number_shirt_home_color', true ) ) . ';';
				}

				if ( get_post_meta( $club_id, '_anwpfl_number_shirt_home_stroke_color', true ) ) {
					$output['css'] .= sprintf( 'text-shadow: -1px -1px 0 %1$s, 1px -1px 0 %1$s, -1px 1px 0 %1$s, 1px 1px 0 %1$s;', esc_attr( get_post_meta( $club_id, '_anwpfl_number_shirt_home_stroke_color', true ) ) );
				}
			}
		}

		return $output;
	}

	/**
	 * Get Club Data.
	 *
	 * @param int $post_id
	 *
	 * @return object|bool
	 * @since 0.9.2
	 */
	public function get_club_data( $post_id ) {

		static $output = null;

		if ( null === $output || empty( $output[ $post_id ] ) ) {

			$club = get_post( $post_id );

			$prefix = '_anwpfl_';
			$data   = [];

			$fields = [
				'logo_big',
				'description',
				'city',
				'nationality',
				'address',
				'website',
				'founded',
				'stadium',
				'club_kit',
				'twitter',
				'youtube',
				'facebook',
				'instagram',
				'vk',
				'tiktok',
				'linkedin',
			];

			foreach ( $fields as $field ) {
				$data[ $field ] = $club->{$prefix . $field};
			}

			/**
			 * Filter: anwpfl/tmpl-club/data_fields
			 *
			 * @since 0.7.5
			 *
			 * @param array   $data
			 * @param WP_Post $club
			 */
			$data = apply_filters( 'anwpfl/tmpl-club/data_fields', $data, $club );

			$data['club_id']   = $club->ID;
			$data['season_id'] = anwp_football_leagues_premium()->club->get_post_season( $club->ID );

			$output[ $post_id ] = (object) $data;
		}

		return empty( $output[ $post_id ] ) ? false : $output[ $post_id ];
	}

	/**
	 * Get array of dates and clubs.
	 *
	 * @param int $team_id
	 *
	 * @return string
	 * @since 0.10.5
	 */
	public function get_team_calendar_dates( int $team_id ): string {

		if ( ! $team_id ) {
			return '';
		}

		// Try to get from cache
		$cache_key = 'FL-PRO-CLUB_get_team_calendar_dates__' . $team_id;

		if ( anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) ) {
			return anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' );
		}

		$output     = [];
		$team_games = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( [ 'filter_by_clubs' => $team_id ] );

		if ( ! empty( $team_games ) && is_array( $team_games ) ) {

			foreach ( $team_games as $single_game ) {

				if ( '0000-00-00 00:00:00' === $single_game->kickoff ) {
					continue;
				}

				$opp_club_id   = absint( $team_id ) === absint( $single_game->home_club ) ? $single_game->away_club : $single_game->home_club;
				$opp_club_logo = anwp_football_leagues()->club->get_club_logo_by_id( $opp_club_id );

				if ( empty( $opp_club_logo ) ) {
					$opp_club_logo = AnWP_Football_Leagues::url( 'public/img/empty_logo.png' );
				}

				$output[] = absint( $single_game->match_id ) . '|' . date_i18n( 'c', strtotime( $single_game->kickoff ) ) . '|' . esc_url( $opp_club_logo );
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
	 * Get club form
	 *
	 * @param int    $club_id
	 * @param string $date_to
	 * @param string $classes
	 *
	 * @return string
	 * @since 0.10.8
	 */
	public function get_club_form( $club_id, $date_to = '', $classes = '' ) {

		$options = [
			'filter_by_clubs' => $club_id,
			'type'            => 'result',
			'sort_by_date'    => 'desc',
			'limit'           => 5,
		];

		if ( $date_to ) {
			$date_to = explode( ' ', $date_to )[0];

			if ( '0000-00-00' !== $date_to && anwp_football_leagues()->helper->validate_date( $date_to, 'Y-m-d' ) ) {
				$options['date_to'] = DateTime::createFromFormat( 'Y-m-d', $date_to )->modify( '-1 day' )->format( 'Y-m-d' );
			}
		}

		// Get latest matches
		$games = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $options );

		if ( ! empty( $games ) && is_array( $games ) ) {
			$games = array_reverse( $games );
		} else {
			return '';
		}

		// Mapping outcome labels
		$series_map = anwp_football_leagues()->data->get_series();

		ob_start();
		?>
		<div class="club-form d-flex align-items-center justify-content-center <?php echo esc_attr( $classes ); ?>">
			<?php
			foreach ( $games as $game ) :
				if ( ( absint( $club_id ) === absint( $game->home_club ) && $game->home_goals > $game->away_goals ) || absint( $club_id ) === absint( $game->away_club ) && $game->away_goals > $game->home_goals ) {
					$outcome_label = $series_map['w'];
					$outcome_class = 'anwp-bg-success';
				} elseif ( ( absint( $club_id ) === absint( $game->home_club ) && $game->home_goals < $game->away_goals ) || absint( $club_id ) === absint( $game->away_club ) && $game->away_goals < $game->home_goals ) {
					$outcome_label = $series_map['l'];
					$outcome_class = 'anwp-bg-danger';
				} else {
					$outcome_label = $series_map['d'];
					$outcome_class = 'anwp-bg-warning';
				}
				?>
				<span data-anwp-fl-match-tooltip data-match-id="<?php echo absint( $game->match_id ); ?>" class="d-inline-block club-form__item-pro anwp-text-white anwp-text-uppercase anwp-text-monospace <?php echo esc_attr( $outcome_class ); ?>">
					<?php echo esc_html( $outcome_label ); ?>
				</span>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get Club POST season.
	 *
	 * @param int $club_id
	 *
	 * @return int
	 * @since 0.11.7
	 */
	public function get_post_season( $club_id ) {

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
			$season_id = anwp_football_leagues()->get_active_club_season( $club_id );
		}

		return absint( $season_id );
	}

	/**
	 * Is current club national team.
	 *
	 * @param int $club_id
	 *
	 * @return bool
	 * @since 0.12.2
	 */
	public function is_national_club( $club_id ) {

		static $options = null;

		if ( null === $options || ! isset( $options[ $club_id ] ) ) {

			if ( null === $options ) {
				$options = [];
			}

			$options[ $club_id ] = 'yes' === get_post_meta( $club_id, '_anwpfl_is_national_team', true );
		}

		return $options[ $club_id ];
	}

	/**
	 * Get h2h team stats
	 *
	 * @param object $args
	 * @param bool   $h2h - Get only H2H stats
	 *
	 * @return array
	 * @since 0.12.2
	 */
	public function get_h2h_clubs_stats( $args, $h2h ) {

		global $wpdb;

		$args = (object) wp_parse_args(
			$args,
			[
				'competition_id' => '',
				'multistage'     => 0,
				'season_id'      => '',
				'league_id'      => '',
				'club_a'         => '',
				'club_b'         => '',
				'date_before'    => '',
				'date_after'     => '',
				'caching_time'   => '',
				'stats'          => [],
			]
		);

		$yr_count = AnWPFL_Options::get_value( 'yr_card_count', 'r' );

		/*
		|--------------------------------------------------------------------
		| Check cache exists
		|--------------------------------------------------------------------
		*/
		if ( absint( $args->caching_time ) ) {

			$cache_key = 'ANWPFL-SHORTCODE-STATS-H2H-' . md5( maybe_serialize( $args ) );

			// Try to get saved transient
			$response = get_transient( $cache_key );

			if ( ! empty( $response ) && is_array( $response ) ) {
				return $response;
			}
		}

		/*
		|--------------------------------------------------------------------
		| Get all games
		|--------------------------------------------------------------------
		*/
		$query = "
			SELECT a.*
			FROM {$wpdb->prefix}anwpfl_matches a
			WHERE a.finished = 1
		";

		/**==================
		 * WHERE filter by competition
		 *================ */
		if ( AnWP_Football_Leagues::string_to_bool( $args->multistage ) && absint( $args->competition_id ) ) {
			$query .= $wpdb->prepare( ' AND (a.competition_id = %d OR a.main_stage_id = %d) ', $args->competition_id, $args->competition_id );
		} elseif ( absint( $args->competition_id ) ) {
			$query .= $wpdb->prepare( ' AND a.competition_id = %d ', $args->competition_id );
		}

		/**==================
		 * WHERE filter by season
		 *================ */
		if ( absint( $args->season_id ) ) {
			$query .= $wpdb->prepare( ' AND a.season_id = %d ', $args->season_id );
		}

		/**==================
		 * WHERE filter by league
		 *================ */
		if ( absint( $args->league_id ) ) {
			$query .= $wpdb->prepare( ' AND a.league_id = %d ', $args->league_id );
		}

		/**==================
		 * WHERE date_before
		 *================ */
		if ( ! empty( $args->date_before ) && anwp_football_leagues()->helper->validate_date( $args->date_before, 'Y-m-d' ) ) {
			$query .= $wpdb->prepare( ' AND a.kickoff < %s ', $args->date_before . ' 00:00:00' );
		}

		/**==================
		 * WHERE date_after
		 *================ */
		if ( ! empty( $args->date_after ) && anwp_football_leagues()->helper->validate_date( $args->date_after, 'Y-m-d' ) ) {
			$query .= $wpdb->prepare( ' AND a.kickoff >= %s ', $args->date_after . ' 00:00:00' );
		}

		/**==================
		 * WHERE filter by clubs
		 *================ */
		if ( $h2h ) {
			$query .= $wpdb->prepare(
				' AND ( ( a.home_club = %d AND a.away_club = %d ) OR ( a.home_club = %d AND a.away_club = %d ) )',
				$args->club_a,
				$args->club_b,
				$args->club_b,
				$args->club_a
			);
		} else {
			$query .= $wpdb->prepare(
				' AND ( a.home_club = %d OR a.away_club = %d OR a.home_club = %d OR a.away_club = %d )',
				$args->club_a,
				$args->club_b,
				$args->club_b,
				$args->club_a
			);
		}

		$games = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		/*
		|--------------------------------------------------------------------
		| Populate stats data
		|--------------------------------------------------------------------
		*/
		$club_a = absint( $args->club_a );
		$club_b = absint( $args->club_b );

		$stats_data = [
			$club_a => [
				'played' => 0,
				'wins'   => 0,
				'draws'  => 0,
				'losses' => 0,
			],
			$club_b => [
				'played' => 0,
				'wins'   => 0,
				'draws'  => 0,
				'losses' => 0,
			],
		];

		foreach ( $games as $game ) {

			// TEAM A
			if ( absint( $game->away_club ) === $club_a || absint( $game->home_club ) === $club_a ) {
				$stats_data[ $club_a ]['played'] ++;

				if ( $game->home_goals === $game->away_goals ) {
					$stats_data[ $club_a ]['draws'] ++;
				}
			}

			if ( absint( $game->home_club ) === $club_a && $game->home_goals > $game->away_goals ) {
				$stats_data[ $club_a ]['wins'] ++;
			}

			if ( absint( $game->away_club ) === $club_a && $game->home_goals < $game->away_goals ) {
				$stats_data[ $club_a ]['wins'] ++;
			}

			if ( absint( $game->home_club ) === $club_a && $game->home_goals < $game->away_goals ) {
				$stats_data[ $club_a ]['losses'] ++;
			}

			if ( absint( $game->away_club ) === $club_a && $game->home_goals > $game->away_goals ) {
				$stats_data[ $club_a ]['losses'] ++;
			}

			// TEAM B
			if ( absint( $game->away_club ) === $club_b || absint( $game->home_club ) === $club_b ) {
				$stats_data[ $club_b ]['played'] ++;

				if ( $game->home_goals === $game->away_goals ) {
					$stats_data[ $club_b ]['draws'] ++;
				}
			}

			if ( absint( $game->home_club ) === $club_b && $game->home_goals > $game->away_goals ) {
				$stats_data[ $club_b ]['wins'] ++;
			}

			if ( absint( $game->away_club ) === $club_b && $game->home_goals < $game->away_goals ) {
				$stats_data[ $club_b ]['wins'] ++;
			}

			if ( absint( $game->home_club ) === $club_b && $game->home_goals < $game->away_goals ) {
				$stats_data[ $club_b ]['losses'] ++;
			}

			if ( absint( $game->away_club ) === $club_b && $game->home_goals > $game->away_goals ) {
				$stats_data[ $club_b ]['losses'] ++;
			}

			// Default Stats
			if ( ! empty( $args->stats ) && is_array( $args->stats ) ) {
				foreach ( $args->stats as $stat ) {

					$stat = mb_strtolower( $stat );

					if ( ! isset( $stats_data[ $club_a ][ $stat ] ) ) {
						$stats_data[ $club_a ][ $stat ] = 0;
					}

					if ( ! isset( $stats_data[ $club_b ][ $stat ] ) ) {
						$stats_data[ $club_b ][ $stat ] = 0;
					}

					// Default Stat
					if ( in_array( $stat, [ 'corners', 'fouls', 'offsides', 'shots', 'shots_on_goal' ], true ) ) {
						$home_property = 'home_' . $stat;
						$away_property = 'away_' . $stat;

						if ( absint( $game->home_club ) === $club_a ) {
							$stats_data[ $club_a ][ $stat ] += $game->{$home_property};
						}

						if ( absint( $game->away_club ) === $club_a ) {
							$stats_data[ $club_a ][ $stat ] += $game->{$away_property};
						}

						if ( absint( $game->home_club ) === $club_b ) {
							$stats_data[ $club_b ][ $stat ] += $game->{$home_property};
						}

						if ( absint( $game->away_club ) === $club_b ) {
							$stats_data[ $club_b ][ $stat ] += $game->{$away_property};
						}
					}

					// cards_y
					if ( 'cards_y' === $stat ) {
						if ( absint( $game->home_club ) === $club_a ) {
							$stats_data[ $club_a ]['cards_y'] += ( $game->home_cards_y + ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? $game->home_cards_yr : 0 ) );
						}

						if ( absint( $game->away_club ) === $club_a ) {
							$stats_data[ $club_a ]['cards_y'] += ( $game->away_cards_y + ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? $game->away_cards_yr : 0 ) );
						}

						if ( absint( $game->home_club ) === $club_b ) {
							$stats_data[ $club_b ]['cards_y'] += ( $game->home_cards_y + ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? $game->home_cards_yr : 0 ) );
						}

						if ( absint( $game->away_club ) === $club_b ) {
							$stats_data[ $club_b ]['cards_y'] += ( $game->away_cards_y + ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? $game->away_cards_yr : 0 ) );
						}
					}

					// cards_r
					if ( 'cards_r' === $stat ) {
						if ( absint( $game->home_club ) === $club_a ) {
							$stats_data[ $club_a ]['cards_r'] += ( $game->home_cards_r + ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? $game->home_cards_yr : 0 ) );
						}

						if ( absint( $game->away_club ) === $club_a ) {
							$stats_data[ $club_a ]['cards_r'] += ( $game->away_cards_r + ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? $game->away_cards_yr : 0 ) );
						}

						if ( absint( $game->home_club ) === $club_b ) {
							$stats_data[ $club_b ]['cards_r'] += ( $game->home_cards_r + ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? $game->home_cards_yr : 0 ) );
						}

						if ( absint( $game->away_club ) === $club_b ) {
							$stats_data[ $club_b ]['cards_r'] += ( $game->away_cards_r + ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? $game->away_cards_yr : 0 ) );
						}
					}

					// goals
					if ( 'goals' === $stat ) {
						if ( absint( $game->home_club ) === $club_a ) {
							$stats_data[ $club_a ]['goals'] += $game->home_goals;
						}

						if ( absint( $game->away_club ) === $club_a ) {
							$stats_data[ $club_a ]['goals'] += $game->away_goals;
						}

						if ( absint( $game->home_club ) === $club_b ) {
							$stats_data[ $club_b ]['goals'] += $game->home_goals;
						}

						if ( absint( $game->away_club ) === $club_b ) {
							$stats_data[ $club_b ]['goals'] += $game->away_goals;
						}
					}

					// goals_conceded
					if ( 'goals_conceded' === $stat ) {
						if ( absint( $game->home_club ) === $club_a ) {
							$stats_data[ $club_a ]['goals_conceded'] += $game->away_goals;
						}

						if ( absint( $game->away_club ) === $club_a ) {
							$stats_data[ $club_a ]['goals_conceded'] += $game->home_goals;
						}

						if ( absint( $game->home_club ) === $club_b ) {
							$stats_data[ $club_b ]['goals_conceded'] += $game->away_goals;
						}

						if ( absint( $game->away_club ) === $club_b ) {
							$stats_data[ $club_b ]['goals_conceded'] += $game->home_goals;
						}
					}

					// clean_sheets
					if ( 'clean_sheets' === $stat ) {
						if ( absint( $game->home_club ) === $club_a ) {
							$stats_data[ $club_a ]['clean_sheets'] += ( $game->away_goals > 0 ? 0 : 1 );
						}

						if ( absint( $game->away_club ) === $club_a ) {
							$stats_data[ $club_a ]['clean_sheets'] += ( $game->home_goals > 0 ? 0 : 1 );
						}

						if ( absint( $game->home_club ) === $club_b ) {
							$stats_data[ $club_b ]['clean_sheets'] += ( $game->away_goals > 0 ? 0 : 1 );
						}

						if ( absint( $game->away_club ) === $club_b ) {
							$stats_data[ $club_b ]['clean_sheets'] += ( $game->home_goals > 0 ? 0 : 1 );
						}
					}
				}
			}
		}

		$output = [
			'h' => $stats_data[ $club_a ],
			'a' => $stats_data[ $club_b ],
		];

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( $args->caching_time && ! empty( $output ) && ! empty( $cache_key ) ) {
			set_transient( $cache_key, $output, $args->caching_time * 60 );
		}

		return $output;
	}

	/**
	 * Get team stats
	 *
	 * @param object $args
	 *
	 * @return array
	 * @since 0.12.4
	 */
	public function get_club_stats( $args ) {

		global $wpdb;

		$args = (object) wp_parse_args(
			$args,
			[
				'competition_id' => '',
				'multistage'     => 0,
				'season_id'      => '',
				'league_id'      => '',
				'club_id'        => '',
				'date_after'     => '',
				'date_before'    => '',
				'caching_time'   => '',
				'stats'          => [],
			]
		);

		$yr_count = AnWPFL_Options::get_value( 'yr_card_count', 'r' );

		/*
		|--------------------------------------------------------------------
		| Check cache exists
		|--------------------------------------------------------------------
		*/
		if ( absint( $args->caching_time ) ) {

			$cache_key = 'ANWPFL-SHORTCODE-STATS-CLUB-' . md5( maybe_serialize( $args ) );

			// Try to get saved transient
			$response = get_transient( $cache_key );

			if ( ! empty( $response ) && is_array( $response ) ) {
				return $response;
			}
		}

		/*
		|--------------------------------------------------------------------
		| Get all games
		|--------------------------------------------------------------------
		*/
		$query = "
			SELECT a.*
			FROM {$wpdb->prefix}anwpfl_matches a
			WHERE a.finished = 1
		";

		/**==================
		 * WHERE filter by competition
		 *================ */
		if ( AnWP_Football_Leagues::string_to_bool( $args->multistage ) && absint( $args->competition_id ) ) {
			$query .= $wpdb->prepare( ' AND (a.competition_id = %d OR a.main_stage_id = %d) ', $args->competition_id, $args->competition_id );
		} elseif ( absint( $args->competition_id ) ) {
			$query .= $wpdb->prepare( ' AND a.competition_id = %d ', $args->competition_id );
		}

		/**==================
		 * WHERE filter by season
		 *================ */
		if ( absint( $args->season_id ) ) {
			$query .= $wpdb->prepare( ' AND a.season_id = %d ', $args->season_id );
		}

		/**==================
		 * WHERE filter by league
		 *================ */
		if ( absint( $args->league_id ) ) {
			$query .= $wpdb->prepare( ' AND a.league_id = %d ', $args->league_id );
		}

		/**==================
		 * WHERE date_before
		 *================ */
		if ( ! empty( $args->date_before ) && anwp_football_leagues()->helper->validate_date( $args->date_before, 'Y-m-d' ) ) {
			$query .= $wpdb->prepare( ' AND a.kickoff < %s ', $args->date_before . ' 00:00:00' );
		}

		/**==================
		 * WHERE date_after
		 *================ */
		if ( ! empty( $args->date_after ) && anwp_football_leagues()->helper->validate_date( $args->date_after, 'Y-m-d' ) ) {
			$query .= $wpdb->prepare( ' AND a.kickoff >= %s ', $args->date_after . ' 00:00:00' );
		}

		/**==================
		 * WHERE filter by club
		 *================ */
		$query .= $wpdb->prepare(
			' AND ( a.home_club = %d OR a.away_club = %d ) ',
			$args->club_id,
			$args->club_id
		);

		$games = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $games ) ) {
			return [];
		}

		/*
		|--------------------------------------------------------------------
		| Populate stats data
		|--------------------------------------------------------------------
		*/
		$club_id = absint( $args->club_id );

		$stats_data = [
			'played' => [
				'h' => 0,
				'a' => 0,
			],
			'wins'   => [
				'h' => 0,
				'a' => 0,
			],
			'draws'  => [
				'h' => 0,
				'a' => 0,
			],
			'losses' => [
				'h' => 0,
				'a' => 0,
			],
		];

		foreach ( $games as $game ) {

			if ( absint( $game->home_club ) === $club_id ) {
				$stats_data['played']['h'] ++;

				if ( $game->home_goals > $game->away_goals ) {
					$stats_data['wins']['h'] ++;
				} elseif ( $game->home_goals === $game->away_goals ) {
					$stats_data['draws']['h'] ++;
				} else {
					$stats_data['losses']['h'] ++;
				}
			} else {
				$stats_data['played']['a'] ++;

				if ( $game->home_goals < $game->away_goals ) {
					$stats_data['wins']['a'] ++;
				} elseif ( $game->home_goals === $game->away_goals ) {
					$stats_data['draws']['a'] ++;
				} else {
					$stats_data['losses']['a'] ++;
				}
			}

			// Default Stats
			if ( ! empty( $args->stats ) && is_array( $args->stats ) ) {
				foreach ( $args->stats as $stat ) {

					$stat = mb_strtolower( $stat );

					if ( ! isset( $stats_data[ $stat ]['h'] ) ) {
						$stats_data[ $stat ]['h'] = 0;
						$stats_data[ $stat ]['a'] = 0;
					}

					// Default Stat
					if ( in_array( $stat, [ 'corners', 'fouls', 'offsides', 'shots', 'shots_on_goal' ], true ) ) {
						$home_property = 'home_' . $stat;
						$away_property = 'away_' . $stat;

						if ( absint( $game->home_club ) === $club_id ) {
							$stats_data[ $stat ]['h'] += $game->{$home_property};
						} else {
							$stats_data[ $stat ]['a'] += $game->{$away_property};
						}
					}

					// cards_y
					if ( 'cards_y' === $stat ) {
						if ( absint( $game->home_club ) === $club_id ) {
							$stats_data['cards_y']['h'] += ( $game->home_cards_y + ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? $game->home_cards_yr : 0 ) );
						} else {
							$stats_data['cards_y']['a'] += ( $game->away_cards_y + ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? $game->away_cards_yr : 0 ) );
						}
					}

					// cards_r
					if ( 'cards_r' === $stat ) {
						if ( absint( $game->home_club ) === $club_id ) {
							$stats_data['cards_r']['h'] += ( $game->home_cards_r + ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? $game->home_cards_yr : 0 ) );
						} else {
							$stats_data['cards_r']['a'] += ( $game->away_cards_r + ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? $game->away_cards_yr : 0 ) );
						}
					}

					// goals
					if ( 'goals' === $stat ) {
						if ( absint( $game->home_club ) === $club_id ) {
							$stats_data['goals']['h'] += $game->home_goals;
						} else {
							$stats_data['goals']['a'] += $game->away_goals;
						}
					}

					// goals_conceded
					if ( 'goals_conceded' === $stat ) {
						if ( absint( $game->home_club ) === $club_id ) {
							$stats_data['goals_conceded']['h'] += $game->away_goals;
						} else {
							$stats_data['goals_conceded']['a'] += $game->home_goals;
						}
					}

					// clean_sheets
					if ( 'clean_sheets' === $stat ) {
						if ( absint( $game->home_club ) === $club_id ) {
							$stats_data['clean_sheets']['h'] += ( $game->away_goals > 0 ? 0 : 1 );
						} else {
							$stats_data['clean_sheets']['a'] += ( $game->home_goals > 0 ? 0 : 1 );
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
		if ( $args->caching_time && ! empty( $stats_data ) && ! empty( $cache_key ) ) {
			set_transient( $cache_key, $stats_data, $args->caching_time * 60 );
		}

		return $stats_data;
	}

	/**
	 * Get totals statistic for Clubs
	 *
	 * @param array $args
	 * @param array $columns
	 *
	 * @return array
	 * @since 0.12.4
	 */
	public function get_clubs_stats_totals_custom( array $args, array $columns ): array {

		$args = wp_parse_args(
			$args,
			[
				'columns'        => '',
				'season_id'      => '',
				'date_before'    => '',
				'date_after'     => '',
				'league_id'      => '',
				'competition_id' => '',
				'multistage'     => 0,
				'caching_time'   => '',
				'club_column'    => 'logo_abbr',
				'sort_column'    => '',
				'side'           => '',
				'sort_order'     => 'DESC',
			]
		);

		$columns = wp_parse_slug_list( $columns );

		if ( empty( $columns ) ) {
			return [];
		}

		global $wpdb;

		$yr_count = AnWPFL_Options::get_value( 'yr_card_count', 'r' );

		// Check custom fields
		$has_custom_stat = (bool) array_filter(
			$columns,
			function ( $c ) {
				return false !== mb_strpos( $c, 'c__' );
			}
		);

		/*
		|--------------------------------------------------------------------
		| Get all games - home_stats_adv
		|--------------------------------------------------------------------
		*/
		$query = "SELECT * FROM $wpdb->anwpfl_matches	WHERE finished = 1 ";

		/**==================
		 * WHERE filter by competition
		 *================ */
		if ( AnWP_Football_Leagues::string_to_bool( $args['multistage'] ) && absint( $args['competition_id'] ) ) {
			$query .= $wpdb->prepare( ' AND (competition_id = %d OR main_stage_id = %d) ', $args['competition_id'], $args['competition_id'] );
		} elseif ( absint( $args['competition_id'] ) ) {
			$query .= $wpdb->prepare( ' AND competition_id = %d ', $args['competition_id'] );
		}

		/**==================
		 * WHERE filter by season
		 *================ */
		if ( absint( $args['season_id'] ) ) {
			$query .= $wpdb->prepare( ' AND season_id = %d ', $args['season_id'] );
		}

		/**==================
		 * WHERE filter by league
		 *================ */
		if ( absint( $args['league_id'] ) ) {
			$query .= $wpdb->prepare( ' AND league_id = %d ', $args['league_id'] );
		}

		/**==================
		 * WHERE date_before
		 *================ */
		if ( ! empty( $args['date_before'] ) && anwp_football_leagues()->helper->validate_date( $args['date_before'], 'Y-m-d' ) ) {
			$query .= $wpdb->prepare( ' AND kickoff < %s ', $args['date_before'] . ' 00:00:00' );
		}

		/**==================
		 * WHERE date_after
		 *================ */
		if ( ! empty( $args['date_after'] ) && anwp_football_leagues()->helper->validate_date( $args['date_after'], 'Y-m-d' ) ) {
			$query .= $wpdb->prepare( ' AND kickoff >= %s ', $args['date_after'] . ' 00:00:00' );
		}

		$games = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $games ) ) {
			return [];
		}

		/*
		|--------------------------------------------------------------------
		| Populate Stats
		|--------------------------------------------------------------------
		*/
		$club_ids   = array_unique( array_merge( [], wp_list_pluck( $games, 'home_club' ), wp_list_pluck( $games, 'away_club' ) ) );
		$stats_data = array_fill_keys( $club_ids, array_fill_keys( $columns, 0 ) );

		foreach ( $games as $game ) {

			if ( $has_custom_stat ) {
				$stats_home = json_decode( $game->stats_home_club, true ) ? : [];
				$stats_away = json_decode( $game->stats_away_club, true ) ? : [];
			}

			foreach ( $columns as $stat ) {

				if ( ! isset( $stats_data[ $game->home_club ][ $stat ] ) ) {
					$stats_data[ $game->home_club ][ $stat ] = 0;
				}

				if ( ! isset( $stats_data[ $game->away_club ][ $stat ] ) ) {
					$stats_data[ $game->away_club ][ $stat ] = 0;
				}

				// Played
				if ( 'played' === $stat ) {
					if ( 'away' !== $args['side'] ) {
						$stats_data[ $game->home_club ]['played'] ++;
					}

					if ( 'home' !== $args['side'] ) {
						$stats_data[ $game->away_club ]['played'] ++;
					}
				}

				// Wins
				if ( 'wins' === $stat ) {
					if ( 'away' !== $args['side'] && $game->home_goals > $game->away_goals ) {
						$stats_data[ $game->home_club ]['wins'] ++;
					}

					if ( 'home' !== $args['side'] && $game->home_goals < $game->away_goals ) {
						$stats_data[ $game->away_club ]['wins'] ++;
					}
				}

				// Losses
				if ( 'losses' === $stat ) {
					if ( 'away' !== $args['side'] && $game->home_goals < $game->away_goals ) {
						$stats_data[ $game->home_club ]['losses'] ++;
					}

					if ( 'home' !== $args['side'] && $game->home_goals > $game->away_goals ) {
						$stats_data[ $game->away_club ]['losses'] ++;
					}
				}

				// Draws
				if ( 'draws' === $stat ) {
					if ( 'away' !== $args['side'] && $game->home_goals === $game->away_goals ) {
						$stats_data[ $game->home_club ]['draws'] ++;
					}

					if ( 'home' !== $args['side'] && $game->home_goals === $game->away_goals ) {
						$stats_data[ $game->away_club ]['draws'] ++;
					}
				}

				// Default Stat
				if ( in_array( $stat, [ 'corners', 'fouls', 'offsides', 'shots', 'shots_on_goal', 'goals' ], true ) ) {

					$home_property = 'home_' . $stat;
					$away_property = 'away_' . $stat;

					if ( 'away' !== $args['side'] ) {
						$stats_data[ $game->home_club ][ $stat ] += $game->{$home_property};
					}

					if ( 'home' !== $args['side'] ) {
						$stats_data[ $game->away_club ][ $stat ] += $game->{$away_property};
					}
				}

				// cards_y
				if ( 'cards_y' === $stat ) {
					if ( 'away' !== $args['side'] ) {
						$stats_data[ $game->home_club ]['cards_y'] += ( $game->home_cards_y + ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? $game->home_cards_yr : 0 ) );
					}

					if ( 'home' !== $args['side'] ) {
						$stats_data[ $game->away_club ]['cards_y'] += ( $game->away_cards_y + ( in_array( $yr_count, [ 'y', 'yr' ], true ) ? $game->away_cards_yr : 0 ) );
					}
				}

				// cards_r
				if ( 'cards_r' === $stat ) {
					if ( 'away' !== $args['side'] ) {
						$stats_data[ $game->home_club ]['cards_r'] += ( $game->home_cards_r + ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? $game->home_cards_yr : 0 ) );
					}

					if ( 'home' !== $args['side'] ) {
						$stats_data[ $game->away_club ]['cards_r'] += ( $game->away_cards_r + ( in_array( $yr_count, [ 'r', 'yr' ], true ) ? $game->away_cards_yr : 0 ) );
					}
				}

				// goals_conceded
				if ( 'goals_conceded' === $stat ) {
					if ( 'away' !== $args['side'] ) {
						$stats_data[ $game->home_club ]['goals_conceded'] += $game->away_goals;
					}

					if ( 'home' !== $args['side'] ) {
						$stats_data[ $game->away_club ]['goals_conceded'] += $game->home_goals;
					}
				}

				// clean_sheets
				if ( 'clean_sheets' === $stat ) {
					if ( 'away' !== $args['side'] ) {
						$stats_data[ $game->home_club ]['clean_sheets'] += ( $game->away_goals > 0 ? 0 : 1 );
					}

					if ( 'home' !== $args['side'] ) {
						$stats_data[ $game->away_club ]['clean_sheets'] += ( $game->home_goals > 0 ? 0 : 1 );
					}
				}

				if ( $has_custom_stat && false !== mb_strpos( $stat, 'c__' ) ) {
					$col_stat_id = absint( mb_substr( $stat, 3 ) );

					if ( ! $col_stat_id ) {
						continue;
					}

					if ( 'away' !== $args['side'] && is_numeric( $stats_home[ $col_stat_id ] ?? '' ) ) {
						$stats_data[ $game->home_club ][ $stat ] += $stats_home[ $col_stat_id ];
					}

					if ( 'home' !== $args['side'] && isset( $stats_away->{$col_stat_id} ) && is_numeric( $stats_away->{$col_stat_id} ) ) {
						$stats_data[ $game->away_club ][ $stat ] += $stats_away->{$col_stat_id};
					}
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Sorting
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $args['sort_column'] ) ) {
			$sort_column = sanitize_text_field( $args['sort_column'] );
			$sort_order  = 'asc' === mb_strtolower( $args['sort_order'] ) ? 'ASC' : 'DESC';

			$stats_data = wp_list_sort( $stats_data, $sort_column, $sort_order, true );
		}

		return $stats_data;
	}

	/**
	 * Create CMB2 metaboxes
	 *
	 * @since 0.12.6
	 */
	public function init_cmb2_metaboxes() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_anwpfl_';

		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box(
			[
				'id'           => 'anwp_club_report-recipients_metabox',
				'title'        => esc_html__( 'Match Report Recipients', 'anwp-football-leagues-premium' ),
				'object_types' => [ 'anwp_club' ],
				'priority'     => 'low',
				'classes'      => 'anwp-b-wrap',
				'show_on_cb'   => function () {
					return 'yes' === AnWPFL_Premium_Options::get_value( 'send_match_report_by_email' );
				},
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Recipient Email', 'anwp-football-leagues-premium' ),
				'id'         => $prefix . 'report_email',
				'type'       => 'text_email',
				'repeatable' => true,
				'text'       => [
					'add_row_text' => esc_html__( 'Add', 'anwp-football-leagues-premium' ),
				],
			]
		);
	}

	/**
	 * Get Club data
	 *
	 * @param int $club_id Club ID
	 *
	 * @return (object) [ // <pre>
	 *        'title'     => (string),
	 *        'abbr'      => (string),
	 *        'link'      => (string),
	 *        'logo_big'  => (string),
	 *        'logo'      => (string),
	 * ]|bool
	 * @since 0.13.7
	 */
	public function get_club( $club_id ) {
		if ( method_exists( anwp_football_leagues()->club, 'get_club' ) ) {
			return anwp_football_leagues()->club->get_club( $club_id );
		}

		return false;
	}
}
