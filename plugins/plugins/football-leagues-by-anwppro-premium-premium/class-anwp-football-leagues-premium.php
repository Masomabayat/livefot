<?php
/**
 * AnWP Football Leagues Premium :: Main Class
 *
 * @since   0.1.0
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * Autoloads files with classes when needed.
 *
 * @since  0.1.0
 *
 * @param  string $class_name Name of the class being requested.
 */
function anwp_football_leagues_premium_autoload_classes( $class_name ) {

	// If our class doesn't have our prefix, don't load it.
	if ( 0 !== strpos( $class_name, 'AnWPFL_Premium_' ) ) {
		return;
	}

	// Set up our filename.
	$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'AnWPFL_Premium_' ) ) ) );

	// Include our file.
	AnWP_Football_Leagues_Premium::include_file( 'includes/class-anwpfl-premium-' . $filename );
}

spl_autoload_register( 'anwp_football_leagues_premium_autoload_classes' );

/**
 * Main initiation class.
 *
 * @property-read AnWPFL_Premium_Assets        $assets
 * @property-read AnWPFL_Premium_Club          $club
 * @property-read AnWPFL_Premium_Charts        $charts
 * @property-read AnWPFL_Premium_Cache         $cache
 * @property-read AnWPFL_Premium_Data          $data
 * @property-read AnWPFL_Premium_API           $api
 * @property-read AnWPFL_Premium_Options       $options
 * @property-read AnWPFL_Premium_Match         $match
 * @property-read AnWPFL_Premium_Match_Report  $match_report
 * @property-read AnWPFL_Premium_Match_Public  $match_public
 * @property-read AnWPFL_Premium_Competition   $competition
 * @property-read AnWPFL_Premium_Standing      $standing
 * @property-read AnWPFL_Premium_Stadium       $stadium
 * @property-read AnWPFL_Premium_Player        $player
 * @property-read AnWPFL_Premium_Referee       $referee
 * @property-read AnWPFL_Premium_Transfer      $transfer
 * @property-read AnWPFL_Premium_Builder       $builder
 * @property-read AnWPFL_Premium_Live          $live
 * @property-read AnWPFL_Premium_Stats         $stats
 * @property-read AnWPFL_Premium_Text          $text
 * @property-read AnWPFL_Premium_Health        $health
 * @property-read AnWPFL_Premium_Customizer    $customizer
 * @property-read AnWPFL_Premium_Suspension    $suspension
 * @property-read AnWPFL_Premium_Helper        $helper
 * @property-read AnWPFL_Premium_Upgrade       $upgrade
 *
 * @since  0.1.0
 */
final class AnWP_Football_Leagues_Premium {

	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	const VERSION = '0.16.1';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * @var AnWPFL_Premium_Match_Report
	 */
	protected $match_report;

	/**
	 * @var AnWPFL_Premium_Match_Public
	 */
	protected $match_public;

	/**
	 * @var AnWPFL_Premium_Upgrade
	 */
	protected $upgrade;

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  0.1.0
	 */
	protected $activation_errors = [];

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    AnWP_Football_Leagues_Premium
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Instance of AnWPFL_Premium_Data
	 *
	 * @since 0.6.0
	 * @var AnWPFL_Premium_Data
	 */
	protected $data;

	/**
	 * Instance of AnWPFL_Premium_Data
	 *
	 * @since 0.6.0
	 * @var AnWPFL_Premium_Data
	 */
	protected $helper;

	/**
	 * Instance of AnWPFL_Premium_Assets
	 *
	 * @since 0.15.4
	 * @var AnWPFL_Premium_Assets
	 */
	protected $assets;

	/**
	 * Instance of AnWPFL_Premium_Options
	 *
	 * @since 0.1.0
	 * @var AnWPFL_Premium_Options
	 */
	protected $options;

	/**
	 * Instance of AnWPFL_Premium_Match
	 *
	 * @since 0.1.0
	 * @var AnWPFL_Premium_Match
	 */
	protected $match;

	/**
	 * Instance of AnWPFL_Premium_Live
	 *
	 * @since 0.1.0
	 * @var AnWPFL_Premium_Live
	 */
	protected $live;

	/**
	 * Instance of AnWPFL_Premium_Competition
	 *
	 * @since 0.1.0
	 * @var AnWPFL_Premium_Competition
	 */
	protected $competition;

	/**
	 * Instance of AnWPFL_Premium_Club
	 *
	 * @since 0.6.0
	 * @var AnWPFL_Premium_Club
	 */
	protected $club;

	/**
	 * Instance of AnWPFL_Premium_Stadium
	 *
	 * @since 0.6.0
	 * @var AnWPFL_Premium_Stadium
	 */
	protected $stadium;

	/**
	 * Instance of AnWPFL_Premium_Health
	 *
	 * @since 0.13.2
	 * @var AnWPFL_Premium_Health
	 */
	protected $health;

	/**
	 * Instance of AnWPFL_Premium_Suspension
	 *
	 * @since 0.13.7
	 * @var AnWPFL_Premium_Suspension
	 */
	protected $suspension;

	/**
	 * Instance of AnWPFL_Premium_Player
	 *
	 * @since 0.5.7
	 * @var AnWPFL_Premium_Player
	 */
	protected $player;

	/**
	 * @var AnWPFL_Premium_Referee
	 */
	protected $referee;

	/**
	 * @var AnWPFL_Premium_Standing
	 */
	protected $standing;

	/**
	 * @var AnWPFL_Premium_Transfer
	 */
	protected $transfer;

	/**
	 * Instance of AnWPFL_Premium_Builder
	 *
	 * @since 0.8.15
	 * @var AnWPFL_Premium_Builder
	 */
	protected $builder;

	/**
	 * Instance of AnWPFL_Premium_API
	 *
	 * @since 0.9.6
	 * @var AnWPFL_Premium_API
	 */
	protected $api;

	/**
	 * Instance of AnWPFL_Premium_Stats
	 *
	 * @since 0.9.7
	 * @var AnWPFL_Premium_Stats
	 */
	protected $stats;

	/**
	 * Instance of AnWPFL_Premium_Text
	 *
	 * @since 0.10.7
	 * @var AnWPFL_Premium_Text
	 */
	protected $text;

	/**
	 * Instance of AnWPFL_Premium_Charts
	 *
	 * @since 0.10.7
	 * @var AnWPFL_Premium_Charts
	 */
	protected $charts;

	/**
	 * Instance of AnWPFL_Premium_Cache
	 *
	 * @since 0.13.4
	 * @var AnWPFL_Premium_Cache
	 */
	protected $cache;

	/**
	 * Instance of AnWPFL_Premium_Customizer
	 *
	 * @since 0.14.0
	 * @var AnWPFL_Premium_Customizer
	 */
	protected $customizer;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.1.0
	 * @return  AnWP_Football_Leagues_Premium A single instance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
		$this->basename = plugin_basename( self::dir( 'anwp-football-leagues-premium.php' ) );

		$this->define_tables();
	}

	/**
	 * Register custom tables within $wpdb object.
	 */
	private function define_tables() {
		global $wpdb;

		$tables = [
			'anwpfl_import_mapping',
			'anwpfl_predictions',
			'anwpfl_formations',
			'anwpfl_transfers',
		];

		foreach ( $tables as $table ) {
			$wpdb->$table   = $wpdb->prefix . $table;
			$wpdb->tables[] = $table;
		}
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.1.0
	 */
	public function plugin_classes() {

		$this->assets       = new AnWPFL_Premium_Assets( $this );
		$this->builder      = new AnWPFL_Premium_Builder( $this );
		$this->cache        = new AnWPFL_Premium_Cache( $this );
		$this->charts       = new AnWPFL_Premium_Charts( $this );
		$this->club         = new AnWPFL_Premium_Club( $this );
		$this->competition  = new AnWPFL_Premium_Competition( $this );
		$this->customizer   = new AnWPFL_Premium_Customizer( $this );
		$this->data         = new AnWPFL_Premium_Data( $this );
		$this->health       = new AnWPFL_Premium_Health( $this );
		$this->helper       = new AnWPFL_Premium_Helper( $this );
		$this->live         = new AnWPFL_Premium_Live( $this );
		$this->match        = new AnWPFL_Premium_Match( $this );
		$this->match_report = new AnWPFL_Premium_Match_Report( $this );
		$this->match_public = new AnWPFL_Premium_Match_Public( $this );
		$this->options      = new AnWPFL_Premium_Options( $this );
		$this->player       = new AnWPFL_Premium_Player( $this );
		$this->referee      = new AnWPFL_Premium_Referee( $this );
		$this->stadium      = new AnWPFL_Premium_Stadium( $this );
		$this->standing     = new AnWPFL_Premium_Standing( $this );
		$this->stats        = new AnWPFL_Premium_Stats( $this );
		$this->suspension   = new AnWPFL_Premium_Suspension( $this );
		$this->text         = new AnWPFL_Premium_Text( $this );
		$this->transfer     = new AnWPFL_Premium_Transfer( $this );
		$this->upgrade      = new AnWPFL_Premium_Upgrade( $this );

		if ( apply_filters( 'anwpfl/api-import/load_api_football', true ) ) {
			$this->api = new AnWPFL_Premium_API( $this );
		}

		// Shortcodes
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-matches-scoreboard.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-results-matrix.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-matchweeks-slides.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-stats-players.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-stats-players-custom.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-bracket.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-transfers.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-birthdays.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-tag-posts.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-matches-h2h.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-stats-h2h.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-standings.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-missing-players.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-charts.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-calendar-slider.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-stat-players.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-stats-club.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-stats-clubs.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-suspension-risk.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-referee-stats.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-player-stats-panel.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-referees-stats.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-timezones.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-live.php' );
		require self::dir( 'includes/shortcodes/class-anwpfl-premium-shortcode-standing-advanced.php' );

		// Add-ons
		if ( anwp_football_leagues_premium()->data->is_addon_active( 'megamenu' ) && ! defined( 'ANETO_VERSION' ) ) {
			self::include_file( 'addons/anwp-block-megamenu/anwp-block-megamenu' );
			anwp_menu()->hooks();
		}

		if ( anwp_football_leagues_premium()->data->is_addon_active( 'sidebars' ) && ! defined( 'ANETO_VERSION' ) ) {
			self::include_file( 'addons/anwp-sidebars/anwp-sidebars' );
			anwp_sidebars()->hooks();
		}

	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		if ( ! defined( 'ANWP_FL_VERSION' ) || version_compare( ANWP_FL_VERSION, '0.16.0', '<' ) || ! flbap_fs()->can_use_premium_code() ) {
			return;
		}

		add_action( 'init', [ $this, 'init' ], 0 );

		// Initialize widgets
		add_action( 'widgets_init', [ $this, 'register_widgets' ], 0 );

		// Filter submenu pages.
		add_filter( 'anwpfl/admin/submenu_pages', [ $this, 'change_submenu_pages' ] );
		add_filter( 'anwpfl/admin/submenu_settings_pages', [ $this, 'change_submenu_settings_pages' ] );

		// Add extra path for premium templates
		add_filter( 'anwpfl_template_paths', [ $this, 'add_premium_template_path' ] );

		/**
		 * Add plugin meta links.
		 *
		 * @since 0.5.4
		 */
		add_filter( 'plugin_row_meta', [ $this, 'add_plugin_meta_links' ], 10, 2 );

		/**
		 * Add to body classes
		 *
		 * @since 0.8.1
		 */
		add_filter( 'body_class', [ $this, 'add_body_classes' ] );

		/**
		 * Add modaal wrappers
		 *
		 * @since 0.8.1
		 */
		add_action( 'wp_footer', [ $this, 'add_modaal_wrappers' ], 99 );

		/**
		 * Change menu order
		 *
		 * @since 0.9.4
		 */
		add_filter( 'custom_menu_order', [ $this, 'menu_settings_custom_order' ] );

		/**
		 * Added tags functionality
		 *
		 * @since 0.10.5
		 */
		add_action( 'init', [ $this, 'register_post_tag' ], 20 );

		add_filter( 'manage_edit-anwp_match_columns', [ $this, 'add_post_tags_column' ], 70 );
		add_filter( 'manage_edit-anwp_club_columns', [ $this, 'add_post_tags_column' ], 70 );
		add_filter( 'manage_edit-anwp_competition_columns', [ $this, 'add_post_tags_column' ], 70 );
		add_filter( 'manage_edit-anwp_player_columns', [ $this, 'add_post_tags_column' ], 70 );
		add_filter( 'manage_edit-anwp_referee_columns', [ $this, 'add_post_tags_column' ], 70 );
		add_filter( 'manage_edit-anwp_staff_columns', [ $this, 'add_post_tags_column' ], 70 );
	}

	/**
	 * Add post tag taxonomy to the plugin instances.
	 *
	 * @since 0.10.5
	 */
	public function register_post_tag() {
		register_taxonomy_for_object_type( 'post_tag', 'anwp_club' );
		register_taxonomy_for_object_type( 'post_tag', 'anwp_competition' );
		register_taxonomy_for_object_type( 'post_tag', 'anwp_match' );
		register_taxonomy_for_object_type( 'post_tag', 'anwp_player' );
		register_taxonomy_for_object_type( 'post_tag', 'anwp_referee' );
		register_taxonomy_for_object_type( 'post_tag', 'anwp_staff' );
	}

	/**
	 * Added post tags column.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 * @since  0.10.5
	 */
	public function add_post_tags_column( $columns ) {

		$columns = array_merge(
			$columns,
			[
				'tags' => esc_html__( 'Tags', 'anwp-football-leagues-premium' ),
			]
		);

		return $columns;
	}

	/**
	 * Add modaal wrappers.
	 *
	 * @return void
	 * @since 0.8.7
	 */
	public function add_modaal_wrappers() {
		?>
		<div id="anwp-modal-games-list" class="anwp-fl-modal" aria-hidden="true">
			<div class="anwp-fl-modal__overlay" tabindex="-1" data-micromodal-close>
				<div class="anwp-fl-modal__container anwp-b-wrap anwp-overflow-y-auto" role="dialog" aria-modal="true">
					<button class="anwp-fl-modal__close" aria-label="Close modal" type="button" data-micromodal-close></button>

					<div class="match-list match-list--widget layout--slim">
						<div id="anwp-modaal-games-list__matches" class="list-group"></div>
						<div id="anwp-modaal-games-list__loader" class="anwp-matches-loading anwp-text-center my-3 d-none d-print-none">
							<img src="<?php echo esc_url( admin_url( '/images/spinner.gif' ) ); ?>" alt="spinner">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="anwp-modaal-stat-players" class="anwp-fl-modal" aria-hidden="true">
			<div class="anwp-fl-modal__overlay" tabindex="-1" data-micromodal-close>
				<div class="anwp-fl-modal__container anwp-b-wrap anwp-overflow-y-auto anwp-w-400" role="dialog" aria-modal="true">
					<button tabindex="-1" class="anwp-fl-modal__close" aria-label="Close modal" type="button" data-micromodal-close></button>

					<div id="anwp-modaal-stat-players__players" class="shortcode-stat_players stat-players anwp-fl-border anwp-border-light"></div>
					<div id="anwp-modaal-stat-players__loader" class="anwp-text-center mt-3 d-none d-print-none">
						<img src="<?php echo esc_url( admin_url( '/images/spinner.gif' ) ); ?>" alt="spinner" class="d-inline-block">
					</div>
					<div class="d-none mt-2 anwp-text-center" id="anwp-modaal-stat-players__load-more">
						<div class="position-relative anwp-fl-btn-outline anwp-text-base w-100 anwp-cursor-pointer">
							<?php echo esc_html( AnWPFL_Text::get_value( 'general__load_more', __( 'load more', 'anwp-football-leagues-premium' ) ) ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="anwp-fl-modal-full" class="anwp-fl-modal" aria-hidden="true">
			<div class="anwp-fl-modal__container anwp-fl-modal__container--full anwp-b-wrap anwp-overflow-y-auto" role="dialog" aria-modal="true">
				<button tabindex="-1" class="anwp-fl-modal__close" aria-label="Close modal" type="button" data-micromodal-close></button>

				<div id="anwp-fl-modal-full__container" class="p-4 anwp-fl-modal-full__container"></div>

			</div>
		</div>
		<?php
	}

	/**
	 * Add body classes.
	 *
	 * @param array $classes
	 *
	 * @return array
	 * @since 0.8.1
	 */
	public function add_body_classes( $classes ) {

		if ( 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_mode' ) ) {

			// Live Scores - blinking changed score
			if ( 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_mode_blinking' ) ) {
				$classes[] = 'fl-live-mode__blinking';
			}

			// Live Scores - play sound on score changed
			if ( 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_mode_sound' ) ) {
				$classes[] = 'fl-live-mode__sound';
			}
		}

		if ( AnWPFL_Premium_Options::get_value( 'match_list_custom_button', '' ) ) {
			$classes[] = 'fl-match-list-custom-btn';
		}

		return $classes;
	}

	/**
	 * Add plugin meta links.
	 *
	 * @param array  $links       An array of the plugin's metadata,
	 *                            including the version, author,
	 *                            author URI, and plugin URI.
	 * @param string $file        Path to the plugin file, relative to the plugins directory.
	 *
	 * @since 0.5.4
	 * @return array
	 */
	public function add_plugin_meta_links( $links, $file ) {

		if ( false !== strpos( $file, $this->basename ) ) {
			$new_links = [
				'changelog' => '<a href="https://anwppro.userecho.com/knowledge-bases/11-fl-changelog/categories/29-premium-version/articles" target="_blank">' . esc_html__( 'Changelog', 'anwp-football-leagues' ) . '</a>',
			];

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	/**
	 * Add extra path for loading premium templates.
	 *
	 * @param $file_paths
	 *
	 * @return mixed
	 * @since 0.1.0
	 */
	public function add_premium_template_path( $file_paths ) {

		$file_paths[50] = $this->path . 'templates/';

		return $file_paths;
	}

	/**
	 * Filter registered submenu pages.
	 *
	 * @param array $submenu_pages
	 *
	 * @return array
	 * @since 0.1.0 (2018-04-01)
	 */
	public function change_submenu_pages( $submenu_pages ) {

		unset( $submenu_pages['premium'] );

		if ( flbap_fs()->can_use_premium_code() && flbap_fs()->is_premium() ) {
			$submenu_pages['support']['output_func'] = [ $this, 'render_support_page' ];

			$submenu_pages['anwp-fl-addons'] = [
				'parent_slug' => 'anwp-football-leagues',
				'page_title'  => esc_html__( 'Add-ons', 'anwp-football-leagues-premium' ),
				'menu_title'  => '<span class="anwpfl-pro-menu-text">FL+</span>' . esc_html__( 'Add-ons', 'anwp-football-leagues-premium' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'anwp-football-leagues-bonus_theme',
				'output_func' => [ $this, 'render_addons_page' ],
			];
		}

		return $submenu_pages;
	}

	/**
	 * Filter registered submenu settings pages.
	 *
	 * @param array $submenu_pages
	 *
	 * @return array
	 * @since 0.9.3
	 */
	public function change_submenu_settings_pages( $submenu_pages ) {

		if ( flbap_fs()->can_use_premium_code() && flbap_fs()->is_premium() ) {
			$submenu_pages['import-matches'] = [
				'parent_slug' => 'anwp-settings-tools',
				'page_title'  => esc_html__( 'Import Matches', 'anwp-football-leagues-premium' ),
				'menu_title'  => '<span class="anwpfl-pro-menu-text">FL+</span>' . esc_html__( 'Import Matches', 'anwp-football-leagues-premium' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'anwp-football-leagues-import-matches',
				'output_func' => [ $this, 'render_import_matches_page' ],
			];

			$submenu_pages['live-scores'] = [
				'parent_slug' => 'anwp-settings-tools',
				'page_title'  => esc_html__( 'Live Scores', 'anwp-football-leagues-premium' ),
				'menu_title'  => '<span class="anwpfl-pro-menu-text">FL+</span>' . esc_html__( 'Live Scores', 'anwp-football-leagues-premium' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'anwp-football-leagues-live-scores',
				'output_func' => [ $this, 'render_live_scores_page' ],
			];

			$submenu_pages['stats'] = [
				'parent_slug' => 'anwp-settings-tools',
				'page_title'  => esc_html__( 'Statistics Configurator', 'anwp-football-leagues-premium' ),
				'menu_title'  => '<span class="anwpfl-pro-menu-text">FL+</span>' . esc_html__( 'Statistics', 'anwp-football-leagues-premium' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'anwp-football-leagues-stats-config',
				'output_func' => [ $this, 'render_stats_config_page' ],
			];
		}

		return $submenu_pages;
	}

	/**
	 * Rendering Premium Support page
	 *
	 * @since 0.5.4
	 */
	public function render_support_page() {

		//must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues' ) );
		}

		self::include_file( 'admin/views/support' );
	}

	/**
	 * Rendering Premium Bonus page
	 *
	 * @since 0.6.0
	 */
	public function render_addons_page() {

		//must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues' ) );
		}

		self::include_file( 'admin/views/addons' );
	}

	/**
	 * Rendering Import Matches page
	 *
	 * @since 0.9.3
	 */
	public function render_import_matches_page() {

		//must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues-premium' ) );
		}

		self::include_file( 'admin/views/import-matches' );
	}

	/**
	 * Rendering Live Scores Dashboard page
	 *
	 * @since 0.9.5
	 */
	public function render_live_scores_page() {

		//must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues-premium' ) );
		}

		self::include_file( 'admin/views/live-scores' );
	}

	/**
	 * Rendering Statistics Configurator page
	 *
	 * @since 0.9.7
	 */
	public function render_stats_config_page() {

		//must check that the user has the required capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues-premium' ) );
		}

		self::include_file( 'admin/views/stats-config' );
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 * Add deactivation cleanup functionality here.
	 *
	 * @since  0.9.6
	 */
	public function deactivate() {
		wp_unschedule_hook( 'anwp_fl_api_scheduled_finished' );
		wp_unschedule_hook( 'anwp_fl_api_scheduled_live' );
		wp_unschedule_hook( 'anwp_fl_api_scheduled_lineups' );
		wp_unschedule_hook( 'anwp_fl_api_scheduled_injuries' );
		wp_unschedule_hook( 'anwp_fl_api_scheduled_odds' );
		wp_unschedule_hook( 'anwp_fl_api_scheduled_kickoff' );
		wp_unschedule_hook( 'anwp_fl_api_scheduled_predictions' );

		wp_unschedule_hook( 'anwp_fl_api_scheduled_import_lineups' );
		wp_unschedule_hook( 'anwp_fl_api_scheduled_import_live' );
	}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 */
	public function init() {

		// Bail early if requirements aren't met.
		if ( ! flbap_fs()->can_use_premium_code() || ! flbap_fs()->is_premium() ) {
			return;
		}

		// Load translated strings for plugin.
		load_plugin_textdomain( 'anwp-football-leagues-premium', false, dirname( $this->basename ) . '/languages/' );

		// Initialize plugin classes.
		$this->plugin_classes();
	}

	/**
	 * Register widgets.
	 *
	 * @since 0.1.0
	 */
	public function register_widgets() {

		if ( flbap_fs()->can_use_premium_code() && flbap_fs()->is_premium() ) {

			// Parent class
			AnWP_Football_Leagues::include_file( 'includes/widgets/class-anwpfl-widget' );

			// Include classes
			self::include_file( 'includes/widgets/class-anwpfl-premium-widget-next-match' );
			self::include_file( 'includes/widgets/class-anwpfl-premium-widget-matchweeks' );
			self::include_file( 'includes/widgets/class-anwpfl-premium-widget-calendar' );
			self::include_file( 'includes/widgets/class-anwpfl-premium-widget-stat-players' );

			// Register widgets
			register_widget( 'AnWPFL_Premium_Widget_Next_Match' );
			register_widget( 'AnWPFL_Premium_Widget_Matchweeks' );
			register_widget( 'AnWPFL_Premium_Widget_Calendar' );
			register_widget( 'AnWPFL_Premium_Widget_Stat_Players' );
		}
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param  string $field Field to get.
	 *
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'import':
			case 'api':
			case 'match':
			case 'match_report':
			case 'match_public':
			case 'referee':
			case 'data':
			case 'competition':
			case 'club':
			case 'player':
			case 'standing':
			case 'stadium':
			case 'health':
			case 'options':
			case 'builder':
			case 'suspension':
			case 'live':
			case 'stats':
			case 'text':
			case 'charts':
			case 'cache':
			case 'customizer':
			case 'helper':
			case 'assets':
			case 'upgrade':
			case 'transfer':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @param  string $filename Name of the file to be included.
	 *
	 * @return boolean Result of include call.
	 * @since 0.1.0
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once $file;
		}

		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @param  string $path (optional) appended path.
	 *
	 * @return string       Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? : trailingslashit( dirname( __FILE__ ) );

		return $dir . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @param  string $path (optional) appended path.
	 *
	 * @return string       URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? : trailingslashit( plugin_dir_url( __FILE__ ) );

		return $url . $path;
	}

	/**
	 * Modify time limit.
	 *
	 * @param int $limit
	 *
	 * @since 0.8.3
	 */
	public static function set_time_limit( $limit = 0 ) {
		@set_time_limit( $limit ); // phpcs:ignore
	}

	/**
	 * Change settings menu order.
	 *
	 * @param bool $custom_order
	 *
	 * @return bool
	 * @since 0.9.4
	 */
	public function menu_settings_custom_order( $custom_order ) {

		global $submenu;

		if ( ! empty( $submenu['anwp-settings-tools'] ) ) {

			$current_index  = 2;
			$reordered_menu = [];

			foreach ( $submenu['anwp-settings-tools'] as $menu_item ) {
				if ( 'anwp-settings-tools' === $menu_item[2] ) {
					$reordered_menu[0] = $menu_item;
				} elseif ( 'anwp_football_leagues_options' === $menu_item[2] ) {
					$reordered_menu[1] = $menu_item;
				} else {
					$reordered_menu[ $current_index ++ ] = $menu_item;
				}
			}

			ksort( $reordered_menu );
			$submenu['anwp-settings-tools'] = $reordered_menu; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		return $custom_order;
	}

	/**
	 * Change settings menu order.
	 *
	 * @return string
	 * @since 0.12.8
	 */
	public function create_metabox_header( $args ) {
		if ( method_exists( anwp_football_leagues()->helper, 'create_metabox_header' ) ) {
			return anwp_football_leagues()->helper->create_metabox_header( $args );
		}

		return '';
	}
}
