<?php
/**
 * AnWP Football Leagues Premium Options.
 *
 * @since   0.1.0
 * @package AnWP_Football_Leagues_Premium
 */


/**
 * AnWP Football Leagues Premium Options class.
 *
 * @since 0.1.0
 */
class AnWPFL_Premium_Options {

	/**
	 * Parent plugin class.
	 *
	 * @var    AnWP_Football_Leagues_Premium
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Option key, and option page slug.
	 *
	 * @var    string
	 * @since  0.8.0
	 */
	protected static $key = 'anwp_football_leagues_premium_options';

	/**
	 * Options page metabox ID.
	 *
	 * @var    string
	 * @since  0.8.0
	 */
	protected static $metabox_id = 'anwp_football_leagues_premium_options_metabox';

	/**
	 * Options Page title.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $title = '';

	/**
	 * Constructor.
	 *
	 * @since  0.1.0
	 *
	 * @param  AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Set our title.
		$this->title = esc_html__( 'Football Leagues :: Premium Settings', 'anwp-football-leagues-premium' );

		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Hook in our actions to the admin.
		add_action( 'cmb2_admin_init', [ $this, 'add_premium_options_page_metabox' ] );

		// Add tabs functionality
		add_action( 'cmb2_before_options-page_form_anwp_football_leagues_premium_options_metabox', [ $this, 'cmb2_before_metabox' ] );
	}

	/**
	 * Load Tabs Navigation in options page
	 *
	 * @since 0.11.12
	 */
	public function cmb2_before_metabox() {
		ob_start();
		?>
		<nav class="nav-tab-wrapper wp-clearfix anwp-b-wrap" aria-label="Secondary menu">
			<a href="#anwp-fl-pro-options--general" class="anwp-fl-pro-options__control-item nav-tab nav-tab-active">General</a>
			<a href="#anwp-fl-pro-options--live" class="anwp-fl-pro-options__control-item nav-tab d-flex align-items-center">Live</a>
			<a href="#anwp-fl-pro-options--frontend-edit" class="anwp-fl-pro-options__control-item nav-tab">Frontend Edit</a>
		</nav>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Add custom fields to the options page.
	 *
	 * @since  0.8.0
	 */
	public function add_premium_options_page_metabox() {

		// Add our CMB2 metabox.
		$cmb = new_cmb2_box(
			[
				'id'           => self::$metabox_id,
				'title'        => $this->title,
				'object_types' => [ 'options-page' ],
				'classes'      => 'anwp-b-wrap anwp-settings',
				'option_key'   => self::$key,
				'capability'   => 'manage_options',
				'parent_slug'  => 'anwp-settings-tools',
				'menu_title'   => '<span class="anwpfl-pro-menu-text">FL+</span>' . esc_html__( 'Configurator', 'anwp-football-leagues' ),
			]
		);

		/*
		|--------------------------------------------------------------------
		| TAB >> Frontend Edit
		|--------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'before_row' => '<div class="anwp-fl-pro-options__content-item d-none" id="anwp-fl-pro-options--frontend-edit">',
				'name'       => esc_html__( 'FrontEnd Match edit and User Roles', 'anwp-football-leagues-premium' ),
				'type'       => 'title',
				'id'         => 'section_front_edit',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Enable frontend Match edit', 'anwp-football-leagues-premium' ),
				'id'      => 'match_front_edit',
				'type'    => 'anwpfl_simple_trigger',
				'default' => 'no',
				'options' => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Enable frontend LIVE Match edit', 'anwp-football-leagues-premium' ),
				'id'        => 'match_live_front_edit',
				'type'      => 'anwpfl_simple_trigger',
				'default'   => 'no',
				'options'   => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
				'after_row' => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------
		| TAB >> Live
		|--------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'before_row' => '<div class="anwp-fl-pro-options__content-item d-none" id="anwp-fl-pro-options--live">',
				'name'       => esc_html__( 'Live Scores', 'anwp-football-leagues-premium' ),
				'type'       => 'title',
				'id'         => 'section_live_mode',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Enable Manual Live Scores', 'anwp-football-leagues-premium' ),
				'id'      => 'match_live_mode',
				'type'    => 'anwpfl_simple_trigger',
				'default' => 'no',
				'desc'    => esc_html__( 'Activate this option only if you plan to add LIVE data manually.', 'anwp-football-leagues-premium' ),
				'options' => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Live Scores Settings (Manual & Automatic by API Import)', 'anwp-football-leagues-premium' ),
				'type' => 'title',
				'id'   => 'section_live_scores_settings_mode',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Update Period - single Match events (sec)', 'anwp-football-leagues-premium' ),
				'id'      => 'live_update_period_single',
				'type'    => 'text_small',
				'default' => '30',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Update Period - match list (sec)', 'anwp-football-leagues-premium' ),
				'id'      => 'live_update_period_list',
				'type'    => 'text_small',
				'default' => '60',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Blinking effect on changed score (1 min)', 'anwp-football-leagues-premium' ),
				'id'      => 'match_live_mode_blinking',
				'type'    => 'anwpfl_simple_trigger',
				'default' => 'no',
				'options' => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Play sound on changed scores', 'anwp-football-leagues-premium' ),
				'id'      => 'match_live_mode_sound',
				'type'    => 'anwpfl_simple_trigger',
				'default' => 'no',
				'options' => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Sound file to play on changed scores', 'anwp-football-leagues-premium' ),
				'id'         => 'match_live_mode_sound_file',
				'type'       => 'file',
				'options'    => [
					'url' => false,
				],
				'text'       => [
					'add_upload_file_text' => 'Add File',
				],
				'query_args' => [
					'type' => 'audio',
				],
				'after_row'  => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------
		| TAB >> General
		|--------------------------------------------------------------------
		*/

		$cmb->add_field(
			[
				'before_row' => '<div class="anwp-fl-pro-options__content-item" id="anwp-fl-pro-options--general">',
				'name'       => esc_html__( "Use default user's timezone", 'anwp-football-leagues-premium' ),
				'id'         => 'user_auto_timezone',
				'default'    => '',
				'desc'       => 'some features are not supported (e.g.: "group by day" in Matches shortcode)',
				'type'       => 'anwpfl_simple_trigger',
				'options'    => [
					''    => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
			]
		);

		$page_cache_support_desc = '* Activating this feature is not guarantee full-page cache support for your site. Test your site in guest mode after activating it.';
		$page_cache_support_desc .= '<br><b>Partial:</b> the plugin will try to purge cache of the game, competition and competition teams only';
		$page_cache_support_desc .= '<br><b>Full:</b> the plugin will try to purge all site cache';

		$cmb->add_field(
			[
				'name'    => 'Support Full Page Caching (on API Import - update games) *',
				'id'      => 'page_cache_support',
				'type'    => 'select',
				'desc'    => $page_cache_support_desc,
				'default' => '',
				'options' => [
					''        => esc_html__( 'No', 'anwp-football-leagues' ),
					'partial' => esc_html__( 'Partial', 'anwp-football-leagues-premium' ),
					'full'    => esc_html__( 'Full', 'anwp-football-leagues-premium' ),
				],
			]
		);

		// Club
		$cmb->add_field(
			[
				'name' => esc_html__( 'Club', 'anwp-football-leagues' ),
				'type' => 'title',
				'id'   => 'section_club',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Show calendar in club header', 'anwp-football-leagues-premium' ),
				'id'      => 'club_header_calendar',
				'type'    => 'anwpfl_simple_trigger',
				'default' => 'show',
				'options' => [
					'hide' => [
						'color' => 'neutral',
						'text'  => esc_html__( 'Hide', 'anwp-football-leagues' ),
					],
					'show' => [
						'color' => 'success',
						'text'  => esc_html__( 'Show', 'anwp-football-leagues' ),
					],
				],
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Results Matrix
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'      => esc_html__( 'Results Matrix', 'anwp-football-leagues-premium' ),
				'type'      => 'title',
				'id'        => 'section_results_matrix',
				'after_row' => $this->render_docs_link( [ 'https://anwppro.userecho.com/knowledge-bases/2/articles/32-results-matrix' => esc_html__( 'Results Matrix', 'anwp-football-leagues-premium' ) ] ),
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Horizontal labels', 'anwp-football-leagues-premium' ),
				'id'      => 'matrix_results_horizontal_labels',
				'type'    => 'select',
				'default' => 'name',
				'options' => [
					'name'      => esc_html__( 'club name', 'anwp-football-leagues-premium' ),
					'logo'      => esc_html__( 'club logo', 'anwp-football-leagues-premium' ),
					'abbr'      => esc_html__( 'club abbreviation', 'anwp-football-leagues-premium' ),
					'logo_abbr' => esc_html__( 'club logo and abbreviation', 'anwp-football-leagues-premium' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Vertical labels', 'anwp-football-leagues-premium' ),
				'id'      => 'matrix_results_vertical_labels',
				'type'    => 'select',
				'default' => 'name',
				'options' => [
					'name'      => esc_html__( 'club name', 'anwp-football-leagues-premium' ),
					'logo'      => esc_html__( 'club logo', 'anwp-football-leagues-premium' ),
					'abbr'      => esc_html__( 'club abbreviation', 'anwp-football-leagues-premium' ),
					'logo_abbr' => esc_html__( 'club logo and abbreviation', 'anwp-football-leagues-premium' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Show club place', 'anwp-football-leagues-premium' ),
				'id'      => 'matrix_results_club_place',
				'type'    => 'select',
				'default' => '',
				'options' => [
					''   => esc_html__( 'yes', 'anwp-football-leagues' ),
					'no' => esc_html__( 'no', 'anwp-football-leagues' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Type', 'anwp-football-leagues' ),
				'id'      => 'matrix_results_type',
				'type'    => 'select',
				'default' => '',
				'options' => [
					''          => esc_html__( 'default', 'anwp-football-leagues' ),
					'symmetric' => esc_html__( 'symmetric', 'anwp-football-leagues-premium' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Match Display Options
		|--------------------------------------------------------------------------
		*/
		$docs_section_match_display = [
			'https://anwppro.userecho.com/knowledge-bases/2/articles/36-match-scoreboard-with-image-background'               => esc_html__( 'Display Scoreboard with Image Background', 'anwp-football-leagues-premium' ),
			'https://anwppro.userecho.com/knowledge-bases/2/articles/61-hidden-match-scores-before-button-click-single-match' => esc_html__( 'Hide score before click', 'anwp-football-leagues-premium' ),
		];

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Match Display Options', 'anwp-football-leagues-premium' ),
				'type'      => 'title',
				'id'        => 'section_match_display',
				'after_row' => $this->render_docs_link( $docs_section_match_display ),
			]
		);

		$cmb->add_field(
			[
				'name'        => esc_html__( 'Display Scoreboard with Image Background', 'anwp-football-leagues-premium' ),
				'after_field' => '<p class="cmb2-metabox-description pt-0">' . esc_html__( 'display Scoreboard instead of default Match header', 'anwp-football-leagues-premium' ) . '</p>',
				'id'          => 'match_scoreboard',
				'type'        => 'anwpfl_simple_trigger',
				'default'     => 'yes',
				'options'     => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'name'         => esc_html__( 'Select Default Scoreboard Background Image', 'anwp-football-leagues-premium' ),
				'id'           => 'match_scoreboard_image',
				'type'         => 'file',
				'options'      => [
					'url' => false,
				],
				'query_args'   => [
					'type' => 'image',
				],
				'preview_size' => 'medium',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Club form (outcomes) in the Game Scoreboard', 'anwp-football-leagues-premium' ),
				'id'      => 'match_club_form_scoreboard',
				'type'    => 'anwpfl_simple_trigger',
				'default' => 'show',
				'options' => [
					'hide' => [
						'color' => 'neutral',
						'text'  => esc_html__( 'Hide', 'anwp-football-leagues' ),
					],
					'show' => [
						'color' => 'success',
						'text'  => esc_html__( 'Show', 'anwp-football-leagues' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Hide score before click', 'anwp-football-leagues-premium' ),
				'id'      => 'match_hide_score_before_click',
				'type'    => 'anwpfl_simple_trigger',
				'default' => 'no',
				'options' => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Match LineUps
		|--------------------------------------------------------------------------
		*/
		$docs_section_match_lineups = [
			'https://anwppro.userecho.com/knowledge-bases/2/articles/520-player-match-rating' => esc_html__( 'Player Match Rating', 'anwp-football-leagues-premium' ),
		];

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Match Lineups', 'anwp-football-leagues-premium' ),
				'type'      => 'title',
				'id'        => 'section_match_lineups',
				'after_row' => $this->render_docs_link( $docs_section_match_lineups ),
			]
		);

		$cmb->add_field(
			[
				'name'             => esc_html__( 'Player Rating Field', 'anwp-football-leagues-premium' ),
				'id'               => 'player_rating',
				'show_option_none' => '- ' . esc_html__( 'select rating field', 'anwp-football-leagues-premium' ) . ' -',
				'options_cb'       => [ anwp_football_leagues_premium()->stats, 'get_options_match_player_stats_simple' ],
				'type'             => 'select',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Match List Display Options
		|--------------------------------------------------------------------------
		*/
		$docs_section_match_list_display = [
			'https://anwppro.userecho.com/knowledge-bases/2/articles/62-custom-icon-button-in-the-match-list' => esc_html__( 'Match List (slim) custom button', 'anwp-football-leagues-premium' ),
		];

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Match List Display Options', 'anwp-football-leagues-premium' ),
				'type'      => 'title',
				'id'        => 'section_match_list_display',
				'after_row' => $this->render_docs_link( $docs_section_match_list_display ),
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Display MatchWeeks as Slides', 'anwp-football-leagues-premium' ),
				'id'      => 'matchweeks_as_slides',
				'type'    => 'anwpfl_simple_trigger',
				'default' => 'no',
				'options' => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'name'         => esc_html__( 'Custom button image', 'anwp-football-leagues-premium' ),
				'id'           => 'match_list_custom_button',
				'type'         => 'file',
				'options'      => [
					'url' => false,
				],
				'query_args'   => [
					'type' => [
						'image/gif',
						'image/jpeg',
						'image/png',
						'image/svg',
					],
				],
				'preview_size' => 'small',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Match Stats
		|--------------------------------------------------------------------------
		*/
		$docs_section_match_stats = [
			'https://anwppro.userecho.com/knowledge-bases/2/articles/198-match-stats-premium-options' => esc_html__( 'Match Stats', 'anwp-football-leagues-premium' ),
		];

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Match Stats', 'anwp-football-leagues' ),
				'type'      => 'title',
				'id'        => 'section_match_stats',
				'after_row' => $this->render_docs_link( $docs_section_match_stats ),
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Match Stats Layout', 'anwp-football-leagues-premium' ),
				'id'      => 'match_stats_layout',
				'type'    => 'select',
				'default' => '',
				'options' => [
					'modern' => esc_html__( 'Modern', 'anwp-football-leagues' ),
					''       => esc_html__( 'Classic', 'anwp-football-leagues-premium' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Show Shots Stat Widget', 'anwp-football-leagues-premium' ),
				'id'      => 'match_stats_widget_shots',
				'type'    => 'anwpfl_simple_trigger',
				'default' => 'show',
				'options' => [
					'hide' => [
						'color' => 'neutral',
						'text'  => esc_html__( 'Hide', 'anwp-football-leagues' ),
					],
					'show' => [
						'color' => 'success',
						'text'  => esc_html__( 'Show', 'anwp-football-leagues' ),
					],
				],
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Match Commentary Options
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name' => esc_html__( 'Match Report', 'anwp-football-leagues-premium' ),
				'type' => 'title',
				'id'   => 'section_match_report',
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Send Match Report by email', 'anwp-football-leagues-premium' ) . ' [BETA]',
				'id'         => 'send_match_report_by_email',
				'default'    => 'no',
				'type'       => 'select',
				'options'    => [
					'no'  => esc_html__( 'No', 'anwp-football-leagues' ),
					'yes' => esc_html__( 'Yes', 'anwp-football-leagues' ),
				],
				'attributes' => [
					'class'     => 'cmb2_select anwp-fl-parent-of-dependent',
					'data-name' => 'send_match_report_by_email',
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Admin Email', 'anwp-football-leagues-premium' ),
				'type'       => 'text',
				'id'         => 'send_match_report_admin_email',
				'repeatable' => true,
				'text'       => [
					'add_row_text' => esc_html__( 'Add email', 'anwp-football-leagues-premium' ),
				],
				'before_row' => '<div class="cmb-row"><div class="anwp-fl-dependent-field" data-parent="send_match_report_by_email" data-action="show" data-value="yes">',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Email Subject', 'anwp-football-leagues-premium' ),
				'type' => 'text',
				'id'   => 'send_match_report_email_subject',
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Match Report Template', 'anwp-football-leagues-premium' ),
				'default'   => '',
				'id'        => 'send_match_report_tmpl',
				'type'      => 'wysiwyg',
				'options'   => [
					'teeny'         => true,
					'textarea_rows' => 5,
				],
				'after_row' => '</div></div>',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Match Commentary Options
		|--------------------------------------------------------------------------
		*/
		$docs_section_match_commentary = [
			'https://anwppro.userecho.com/knowledge-bases/2/articles/48-commentary-block' => esc_html__( 'Match Commentary', 'anwp-football-leagues-premium' ),
		];

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Match Commentary', 'anwp-football-leagues-premium' ),
				'type'      => 'title',
				'id'        => 'section_match_commentary',
				'after_row' => $this->render_docs_link( $docs_section_match_commentary ),
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Layout', 'anwp-football-leagues' ),
				'id'      => 'match_commentary_layout',
				'type'    => 'select',
				'default' => 'default',
				'options' => [
					'default' => esc_html__( 'Default', 'anwp-football-leagues' ),
					'slim'    => esc_html__( 'Slim', 'anwp-football-leagues' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Events Order', 'anwp-football-leagues-premium' ),
				'id'      => 'match_commentary_order',
				'type'    => 'select',
				'default' => 'asc',
				'options' => [
					'asc'  => esc_html__( 'Ascending', 'anwp-football-leagues' ),
					'desc' => esc_html__( 'Descending', 'anwp-football-leagues' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'        => esc_html__( 'Max Height (px)', 'anwp-football-leagues-premium' ),
				'type'        => 'text',
				'default'     => '0',
				'id'          => 'match_commentary_max_height',
				'description' => esc_html__( 'Enter max height. 0 - no limit', 'anwp-football-leagues-premium' ),
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Show player photo in Match Commentary block', 'anwp-football-leagues-premium' ),
				'id'      => 'match_commentary_show_player_photo',
				'default' => 'yes',
				'type'    => 'anwpfl_simple_trigger',
				'options' => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Show scores in Match Commentary block', 'anwp-football-leagues-premium' ),
				'id'      => 'match_commentary_show_scores',
				'default' => 'yes',
				'type'    => 'anwpfl_simple_trigger',
				'options' => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Standing
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name' => esc_html__( 'Standing Display Options', 'anwp-football-leagues-premium' ),
				'type' => 'title',
				'id'   => 'section_standing_display',
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Click in the Standing table open a modal with a list of club matches', 'anwp-football-leagues-premium' ),
				'id'        => 'standing_click_show_matches',
				'default'   => 'yes',
				'type'      => 'anwpfl_simple_trigger',
				'options'   => [
					'no'  => [
						'color' => 'neutral',
						'text'  => esc_html__( 'No', 'anwp-football-leagues' ),
					],
					'yes' => [
						'color' => 'success',
						'text'  => esc_html__( 'Yes', 'anwp-football-leagues' ),
					],
				],
				'after_row' => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------
		| TAB >> Import API
		|--------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name' => esc_html__( 'API key', 'anwp-football-leagues-premium' ),
				'type' => 'hidden',
				'id'   => 'import_api_api_football_com_key',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Request URL', 'anwp-football-leagues-premium' ),
				'type' => 'hidden',
				'id'   => 'import_api_api_football_com_url',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Cache some requests (players, leagues, fixtures)', 'anwp-football-leagues-premium' ),
				'id'   => 'import_api_api_football_com_cache',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Import Stadiums', 'anwp-football-leagues-premium' ),
				'id'   => 'import_api_api_football_com_stadiums',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Import Referees', 'anwp-football-leagues-premium' ),
				'id'   => 'import_api_api_football_com_referees',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Import Coaches',
				'id'   => 'import_api_api_football_coaches',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Import Additional Club Statistics', 'anwp-football-leagues-premium' ),
				'id'   => 'import_api_api_football_com_club_advanced_stats',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Import Player Stats', 'anwp-football-leagues-premium' ),
				'id'   => 'import_api_api_football_com_player_stats',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Import LineUps Formation', 'anwp-football-leagues-premium' ),
				'id'   => 'import_api_api_football_com_lineups_formation',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Reset Squad',
				'id'   => 'import_api_api_football_com_reset_squad',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Import LIVE data from API', 'anwp-football-leagues-premium' ),
				'id'   => 'import_api_api_football_com_live',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Manual LIVE Import support',
				'id'   => 'import_api_api_football_com_live_hybrid',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Get game predictions from API', 'anwp-football-leagues-premium' ),
				'id'   => 'import_api_api_football_com_predictions',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Prediction data to import from API',
				'id'   => 'import_api_api_football_com_predictions_data',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Show prediction in Match List item (bottom line)', 'anwp-football-leagues-premium' ),
				'id'   => 'prediction_show_bottom_line',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Import Players photos',
				'id'   => 'import_api_api_photos_player',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Force update Players photos on squad update',
				'id'   => 'import_api_api_photos_player_force',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Import Coach photos',
				'id'   => 'import_api_api_photos_coach',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Import Club logos',
				'id'   => 'import_api_api_club_logos',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Import Stadium photos',
				'id'   => 'import_api_api_stadium_photos',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Update only not finished games in "UPDATE KICKOFF TIME" action',
				'id'   => 'import_api_api_football_com_update_kickoff_0',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Verify SSL certificate (v3 only)',
				'id'   => 'import_api_api_football_com_sslverify',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Import Odds',
				'id'   => 'import_api_api_ods',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Clickable Odds',
				'id'   => 'import_api_api_ods_clickable',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Disabled Bookmakers',
				'id'   => 'import_api_api_ods_disabled_books',
				'type' => 'hidden',
			]
		);

		$cmb->add_field(
			[
				'name' => '10Bet',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_1',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Marathonbet',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_2',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Betfair',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_3',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Pinnacle',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_4',
			]
		);

		$cmb->add_field(
			[
				'name' => 'SBO',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_5',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Bwin',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_6',
			]
		);

		$cmb->add_field(
			[
				'name' => 'William Hill',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_7',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Bet365',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_8',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Dafabet',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_9',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Ladbrokes',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_10',
			]
		);

		$cmb->add_field(
			[
				'name' => '1xBet',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_11',
			]
		);

		$cmb->add_field(
			[
				'name' => 'BetFred',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_12',
			]
		);

		$cmb->add_field(
			[
				'name' => '188Bet',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_13',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Interwetten',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_15',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Unibet',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_16',
			]
		);

		$cmb->add_field(
			[
				'name' => '5Dimes',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_17',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Intertops',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_18',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Bovada',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_19',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Betcris',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_20',
			]
		);

		$cmb->add_field(
			[
				'name' => '888Sport',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_21',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Tipico',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_22',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Sportingbet',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_23',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Betway',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_24',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Expekt',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_25',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Betsson',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_26',
			]
		);

		$cmb->add_field(
			[
				'name' => 'NordicBet',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_27',
			]
		);

		$cmb->add_field(
			[
				'name' => 'ComeOn',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_28',
			]
		);

		$cmb->add_field(
			[
				'name' => 'Netbet',
				'type' => 'hidden',
				'id'   => 'import_api_bookmaker_30',
			]
		);
	}

	/**
	 * Wrapper function around cmb2_get_option.
	 *
	 * @since  0.8.0
	 *
	 * @param  string $key     Options array key
	 * @param  mixed  $default Optional default value
	 * @return mixed           Option value
	 */
	public static function get_value( $key = '', $default = false ) {
		if ( function_exists( 'cmb2_get_option' ) ) {

			// Use cmb2_get_option as it passes through some key filters.
			return cmb2_get_option( self::$key, $key, $default );
		}

		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( self::$key, $default );

		$val = $default;

		if ( 'all' === $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}

		return $val;
	}

	/**
	 * Returns config options for selected value.
	 *
	 * @param string $value
	 *
	 * @return array
	 * @since 0.8.0
	 */
	public function get_options( $value ) {

		$options = self::get_value( $value );

		if ( ! empty( $options ) && is_array( $options ) ) {
			return $options;
		}

		return [];
	}

	/**
	 * Renders documentation link.
	 *
	 * @param array $links
	 *
	 * @return string
	 * @since 0.9.2
	 */
	private function render_docs_link( $links ) {

		if ( empty( $links ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="anwp-options-docs-link">
			<div class="d-flex">
				<svg class="anwp-icon anwp-icon--octi anwp-icon--s24">
					<use xlink:href="#icon-book"></use>
				</svg>
				<div class="d-flex flex-column pl-2">
					<?php foreach ( $links as $link_url => $link_text ) : ?>
						<div class="mb-1">- <a target="_blank" href="<?php echo esc_url( $link_url ); ?>"><?php echo esc_html( $link_text ); ?></a></div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
