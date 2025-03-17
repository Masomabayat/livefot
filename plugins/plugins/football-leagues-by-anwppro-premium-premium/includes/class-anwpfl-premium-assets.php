<?php
/**
 * AnWP Football Leagues Premium :: Assets.
 */

class AnWPFL_Premium_Assets {

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
	public function __construct( $plugin ) {

		$this->plugin = $plugin;

		// Run Hooks
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {

		add_action(
			'wp_print_scripts',
			function () {
				wp_dequeue_script( 'anwp-fl-public' );
			}
		);

		/**
		 * Add svg icons to the public side
		 *
		 * @since 0.5.12 (2019-01-30)
		 */
		add_action( 'wp_footer', [ $this, 'include_public_svg_icons' ], 99 );

		/**
		 * Add svg icons to the footer
		 *
		 * @since 0.8.1
		 */
		add_action( 'admin_footer', [ $this, 'include_admin_svg_icons' ], 99 );

		// Modify vuejs app scripts loader
		add_filter( 'anwpfl/admin/app_js_loader', [ $this, 'modify_loader_data' ], 10, 2 );

		// Enqueue admin scripts
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'public_enqueue_scripts' ] );

		// Add Premium menu pages to the array of plugin pages
		add_filter( 'anwpfl/admin/plugin_pages', [ $this, 'add_plugin_pages' ] );
	}

	/**
	 * Add Premium pages to the array of plugin pages.
	 *
	 * @param array $plugin_pages
	 *
	 * @return array
	 * @since 0.1.0 (2018-04-01)
	 */
	public function add_plugin_pages( $plugin_pages ) {

		if ( flbap_fs()->can_use_premium_code() && flbap_fs()->is_premium() ) {

			$prefix          = method_exists( 'AnWP_Football_Leagues', 'get_l10n_menu_prefix' ) ? anwp_football_leagues()->get_l10n_menu_prefix() : '';
			$prefix_settings = method_exists( 'AnWP_Football_Leagues', 'get_l10n_menu_settings_prefix' ) ? anwp_football_leagues()->get_l10n_menu_settings_prefix() : '';

			// Import
			$plugin_pages[] = 'toplevel_page_anwp-football-leagues-api';

			// Import Matches
			$plugin_pages[] = 'settings-tools_page_anwp-football-leagues-import-matches';
			$plugin_pages[] = $prefix_settings . '_page_anwp-football-leagues-import-matches';

			// Live Scores
			$plugin_pages[] = 'settings-tools_page_anwp-football-leagues-live-scores';
			$plugin_pages[] = $prefix_settings . '_page_anwp-football-leagues-live-scores';

			// Ads
			$plugin_pages[] = 'settings-tools_page_anwp_football_leagues_premium_ads';
			$plugin_pages[] = $prefix_settings . '_page_anwp_football_leagues_premium_ads';

			// Bonus
			$plugin_pages[] = 'football-leagues_page_anwp-football-leagues-bonus_theme';
			$plugin_pages[] = $prefix . '_page_anwp-football-leagues-bonus_theme';

			// Premium Options
			$plugin_pages[] = 'settings-tools_page_anwp_football_leagues_premium_options';
			$plugin_pages[] = $prefix_settings . '_page_anwp_football_leagues_premium_options';

			// Stats Config
			$plugin_pages[] = 'settings-tools_page_anwp-football-leagues-stats-config';
			$plugin_pages[] = $prefix_settings . '_page_anwp-football-leagues-stats-config';

			// Transfer
			$plugin_pages[] = 'anwp_transfer';

			// Builder
			$plugin_pages[] = 'anwp_fl_builder';

			// Suspension
			$plugin_pages[] = 'anwp_fl_suspension';

			// Match Admin List
			$plugin_pages[] = 'edit-anwp_transfer';
		}

		return $plugin_pages;
	}

	/**
	 * Modify Core admin scripts loader.
	 *
	 * @param $loader_data
	 * @param $current_screen_id
	 *
	 * @return mixed
	 */
	public function modify_loader_data( $loader_data, $current_screen_id ) {

		$prefix_settings = method_exists( 'AnWP_Football_Leagues', 'get_l10n_menu_settings_prefix' ) ? anwp_fl()->get_l10n_menu_settings_prefix() : '';

		if ( 'toplevel_page_anwp-football-leagues-api' === $current_screen_id ) {
			/*
			|--------------------------------------------------------------------------
			| API Import
			|--------------------------------------------------------------------------
			*/
			$loader_data['src'] = AnWP_Football_Leagues_Premium::url( 'admin/js/app/api-import.min.js' );

			wp_dequeue_script( 'vuejs' );
			wp_dequeue_script( 'anwpfl_admin_vue' );
		} elseif ( 'settings-tools_page_anwp-football-leagues-stats-config' === $current_screen_id || $prefix_settings . '_page_anwp-football-leagues-stats-config' === $current_screen_id ) {
			/*
			|--------------------------------------------------------------------------
			| API Import
			|--------------------------------------------------------------------------
			*/
			$loader_data['src'] = AnWP_Football_Leagues_Premium::url( 'admin/js/app/stats.min.js' );

			wp_dequeue_script( 'vuejs' );
			wp_dequeue_script( 'anwpfl_admin_vue' );
		}

		return $loader_data;
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @since 0.1.0
	 */
	public function public_enqueue_scripts() {

		if ( flbap_fs()->can_use_premium_code() && flbap_fs()->is_premium() ) {

			// Load main styles
			if ( is_rtl() ) {
				wp_enqueue_style( 'anwp-fl-premium-styles-rtl', AnWP_Football_Leagues_Premium::url( 'public/css/styles-rtl.css' ), [], AnWP_Football_Leagues_Premium::VERSION );
			} else {
				wp_enqueue_style( 'anwp-fl-premium-styles', AnWP_Football_Leagues_Premium::url( 'public/css/styles.min.css' ), [], AnWP_Football_Leagues_Premium::VERSION );
			}

			/*
			|--------------------------------------------------------------------------
			| Swiper
			| @license: MIT - https://github.com/nolimits4web/swiper
			|--------------------------------------------------------------------------
			*/
			wp_register_script( 'anwp-fl-swiper', AnWP_Football_Leagues_Premium::url( 'public/js/fl-pro-swiper-optimized.min.js' ), [], AnWP_Football_Leagues_Premium::VERSION, true );

			/*
			|--------------------------------------------------------------------------
			| Tabulator
			| @license: MIT - https://github.com/olifolkerd/tabulator
			|--------------------------------------------------------------------------
			*/
			wp_register_script( 'anwp-fl-tabulator', AnWP_Football_Leagues_Premium::url( 'public/js/fl-pro-tabulator.min.js' ), [], AnWP_Football_Leagues_Premium::VERSION, true );

			/*
			|--------------------------------------------------------------------------
			| Flatpickr
			| @license: MIT - https://flatpickr.js.org/
			|
			| Can be disabled if you don't need calendar widget.
			| @since 0.5.10
			|--------------------------------------------------------------------------
			*/
			if ( apply_filters( 'anwpfl/config/load_flatpickr', true ) ) {
				wp_register_style( 'flatpickr', AnWP_Football_Leagues_Premium::url( 'vendor/flatpickr/flatpickr.min.css' ), [], '4.6.9' );
				wp_register_style( 'flatpickr-airbnb', AnWP_Football_Leagues_Premium::url( 'vendor/flatpickr/airbnb.css' ), [], '4.6.9' );
				wp_register_script( 'flatpickr', AnWP_Football_Leagues_Premium::url( 'vendor/flatpickr/flatpickr.min.js' ), [], '4.6.9', false );
			}

			/*
			|--------------------------------------------------------------------------
			| Apache ECharts (incubating)
			| @license: Apache License 2.0
			|--------------------------------------------------------------------------
			*/
			wp_register_script( 'anwp-fl-echarts', AnWP_Football_Leagues_Premium::url( 'vendor/echarts/echarts.min.js' ), [], '4.9.0', false );

			/*
			|--------------------------------------------------------------------------
			| JS Main Script
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_script( 'anwp-fl-public-pro', AnWP_Football_Leagues_Premium::url( 'public/js/anwp-fl-pro-public.min.js' ), [], AnWP_Football_Leagues_Premium::VERSION, true );

			$stats_pagination_l10n = [
				'all'         => esc_html_x( 'Show all', 'statistic table', 'anwp-football-leagues-premium' ),
				'first'       => esc_html_x( 'First', 'statistic table: paginate_first', 'anwp-football-leagues-premium' ),
				'first_title' => esc_html_x( 'First Page', 'statistic table: paginate_first (tooltip)', 'anwp-football-leagues-premium' ),
				'last'        => esc_html_x( 'Last', 'statistic table: paginate_last', 'anwp-football-leagues-premium' ),
				'last_title'  => esc_html_x( 'Last Page', 'statistic table: paginate_last (tooltip)', 'anwp-football-leagues-premium' ),
				'prev'        => esc_html_x( 'Previous', 'statistic table: paginate_previous', 'anwp-football-leagues-premium' ),
				'prev_title'  => esc_html_x( 'Previous Page', 'statistic table: paginate_previous (tooltip)', 'anwp-football-leagues-premium' ),
				'next'        => esc_html_x( 'Next', 'statistic table: paginate_next', 'anwp-football-leagues-premium' ),
				'next_title'  => esc_html_x( 'Next Page', 'statistic table: paginate_next (tooltip)', 'anwp-football-leagues-premium' ),
				'page_size'   => esc_html_x( 'Rows', 'statistic table: Rows', 'anwp-football-leagues-premium' ),
			];

			wp_add_inline_script(
				'anwp-fl-public-pro',
				'window.AnWPFLPro = ' . wp_json_encode( anwp_football_leagues_premium()->data->get_l10n_public() ),
				'before'
			);

			wp_add_inline_script(
				'anwp-fl-public-pro',
				'window.AnWPFLProStatsL10n = ' . wp_json_encode( $stats_pagination_l10n ),
				'before'
			);

			// Backward compatibility - will be removed soon // ToDo
			wp_add_inline_script(
				'anwp-fl-public-pro',
				'window.anwpfl_premium_public_l10n = ' . wp_json_encode( anwp_football_leagues_premium()->data->get_l10n_public() ),
				'before'
			);

			wp_add_inline_script(
				'anwp-fl-public-pro',
				'window.AnWPFL = ' . wp_json_encode(
					[
						'native_yt' => in_array( AnWPFL_Options::get_value( 'preferred_video_player' ), [ 'youtube', 'mixed' ], true ) ? 'yes' : '',
						'rest_root' => esc_url_raw( rest_url() ),
					]
				),
				'before'
			);

			/*
			|--------------------------------------------------------------------------
			| Load Match Edit Form
			|--------------------------------------------------------------------------
			*/
			if ( is_singular( 'anwp_match' ) && anwp_fl_pro()->match_public->is_front_edit_enabled() && anwp_fl_pro()->match_public->has_user_cap_front_edit( get_the_ID() ) ) {

				/*
				|--------------------------------------------------------------------------
				| notyf
				|
				| @license  MIT
				| @link     https://github.com/caroso1222/notyf
				|--------------------------------------------------------------------------
				*/
				wp_enqueue_script( 'notyf', AnWP_Football_Leagues::url( 'vendor/notyf/notyf.min.js' ), [], '3.10.0', false );

				/*
				|--------------------------------------------------------------------------
				| VueJS Script
				|--------------------------------------------------------------------------
				*/
				wp_enqueue_script( 'anwp-fl-pro-public-app', AnWP_Football_Leagues_Premium::url( 'public/js/app/front-app.min.js' ), [ 'jquery' ], AnWP_Football_Leagues_Premium::VERSION, true );
				wp_enqueue_style( 'anwp-fl-admin-styles', AnWP_Football_Leagues::url( 'admin/css/styles.css' ), [], AnWP_Football_Leagues::VERSION );
				wp_enqueue_script( 'vuejs-fl-3', AnWP_Football_Leagues::url( 'vendor/vuejs/vue.runtime.global.prod.min.js' ), [], '3.3.7', false );

				$localized_strings = array_merge(
					anwp_fl_pro()->data->get_l10n_admin(),
					anwp_fl()->data->get_l10n_admin(),
					[
						'save_match_data'              => esc_html__( 'Save Match Data', 'anwp-football-leagues-premium' ),
						'saving'                       => esc_html__( 'saving ...', 'anwp-football-leagues-premium' ),
						'edit_match'                   => esc_html__( 'edit match', 'anwp-football-leagues-premium' ),
						'error_saving_data'            => esc_html__( 'Error saving data. Please, reload page and try again.', 'anwp-football-leagues-premium' ),
						'successfully_saved_reloading' => esc_html__( 'Successfully saved. Reloading page ...', 'anwp-football-leagues-premium' ),
						'live'                         => esc_html__( 'LIVE', 'anwp-football-leagues-premium' ),
						'update_live_status'           => esc_html__( 'Update LIVE status', 'anwp-football-leagues-premium' ),
						'live_scores'                  => esc_html__( 'LIVE Scores', 'anwp-football-leagues-premium' ),
						'set_scores'                   => esc_html__( 'Set Scores', 'anwp-football-leagues-premium' ),
						'update_scores'                => esc_html__( 'Update Scores', 'anwp-football-leagues-premium' ),
						'live_events'                  => esc_html__( 'LIVE Events', 'anwp-football-leagues-premium' ),
					]
				);

				wp_localize_script( 'anwp-fl-pro-public-app', 'anwpfl_public_l10n', $localized_strings );
			}
		}
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @since 0.1.0
	 */
	public function admin_enqueue_scripts() {

		// Load global styles
		wp_enqueue_style( 'anwpfl_premium_styles_global', AnWP_Football_Leagues_Premium::url( 'admin/css/global.css' ), [], AnWP_Football_Leagues_Premium::VERSION );

		// Load styles and scripts (limit to plugin pages)
		$current_screen  = get_current_screen();
		$prefix_page     = method_exists( 'AnWP_Football_Leagues', 'get_l10n_menu_prefix' ) ? anwp_football_leagues()->get_l10n_menu_prefix() : '';
		$prefix_settings = method_exists( 'AnWP_Football_Leagues', 'get_l10n_menu_settings_prefix' ) ? anwp_football_leagues()->get_l10n_menu_settings_prefix() : '';

		$plugin_pages = [

			// Import
			'toplevel_page_anwp-football-leagues-api',

			// Import Matches
			'settings-tools_page_anwp-football-leagues-import-matches',
			$prefix_settings . '_page_anwp-football-leagues-import-matches',

			// Live Scores Dashboard
			'settings-tools_page_anwp-football-leagues-live-scores',
			$prefix_settings . '_page_anwp-football-leagues-live-scores',

			// Options
			'settings-tools_page_anwp_football_leagues_options',
			$prefix_settings . '_page_anwp_football_leagues_options',

			// Premium Options
			'settings-tools_page_anwp_football_leagues_premium_options',
			$prefix_settings . '_page_anwp_football_leagues_premium_options',

			// Live Scores Dashboard
			'settings-tools_page_anwp-football-leagues-stats-config',
			$prefix_settings . '_page_anwp-football-leagues-stats-config',

			// CPTs
			'anwp_standing',
			'anwp_competition',
			'anwp_match',
			'anwp_club',
			'anwp_transfer',
			'anwp_fl_builder',
			'anwp_fl_suspension',
		];

		// Load Common files
		if ( in_array( $current_screen->id, $plugin_pages, true ) && flbap_fs()->can_use_premium_code() && flbap_fs()->is_premium() ) {

			wp_enqueue_media();

			/*
			|--------------------------------------------------------------------------
			| CSS Styles
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_style( 'anwpfl_premium_styles', AnWP_Football_Leagues_Premium::url( 'admin/css/styles.css' ), [ 'wp-color-picker' ], AnWP_Football_Leagues_Premium::VERSION );

			/*
			|--------------------------------------------------------------------------
			| VueJS Script (override default with premium)
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_script( 'anwpfl_premium_admin_vue', AnWP_Football_Leagues_Premium::url( 'admin/js/anwpfl-premium-admin-vue.min.js' ), [ 'anwpfl_premium_admin', 'anwpfl_admin_vue', 'wp-color-picker' ], AnWP_Football_Leagues_Premium::VERSION, true );

			if ( 'yes' === AnWPFL_Premium_Options::get_value( 'match_live_mode', '' ) ) {
				wp_enqueue_script( 'anwp-easytimer', AnWP_Football_Leagues_Premium::url( 'vendor/easytimer/easytimer.min.js' ), [], '4.5.4', false );
			}

			/*
			|--------------------------------------------------------------------------
			| Main admin JS
			|--------------------------------------------------------------------------
			*/
			wp_enqueue_script( 'anwpfl_premium_admin', AnWP_Football_Leagues_Premium::url( 'admin/js/anwpfl-premium-admin.min.js' ), [ 'anwpfl_admin' ], AnWP_Football_Leagues_Premium::VERSION, true );

			wp_localize_script( 'anwpfl_premium_admin', 'anwpfl_premium_admin_l10n', anwp_football_leagues_premium()->data->get_l10n_admin() );
		}

		if ( 'edit-anwp_transfer' === $current_screen->id ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
		}

		/*
		|--------------------------------------------------------------------------
		| Load Builder Scripts
		|--------------------------------------------------------------------------
		*/
		if ( 'anwp_fl_builder' === $current_screen->id ) {
			wp_enqueue_script( 'anwpfl_premium_admin_builder_vue', AnWP_Football_Leagues_Premium::url( 'admin/js/anwpfl-premium-admin-builder-vue.min.js' ), [], AnWP_Football_Leagues_Premium::VERSION, true );
		}

		if ( 'toplevel_page_anwp-football-leagues-api' === $current_screen->id ) {
			wp_dequeue_script( 'anwpfl_premium_admin_vue' );
		}

		if ( 'settings-tools_page_anwp-football-leagues-stats-config' === $current_screen->id || $prefix_settings . '_page_anwp-football-leagues-stats-config' === $current_screen->id ) {
			wp_dequeue_script( 'anwpfl_premium_admin_vue' );
		}
	}

	/**
	 * Add SVG definitions to the public footer.
	 *
	 * @since 0.6.0 (2019-01-30)
	 */
	public function include_public_svg_icons() {

		// Define SVG sprite file.
		$svg_icons = AnWP_Football_Leagues_Premium::dir( 'public/img/svg-icons.svg' );

		// If it exists, include it.
		if ( file_exists( $svg_icons ) ) {
			require_once $svg_icons;
		}
	}

	/**
	 * Add SVG definitions to the admin footer.
	 *
	 * @since 0.8.1
	 */
	public function include_admin_svg_icons() {

		// Define SVG sprite file.
		$svg_icons = AnWP_Football_Leagues_Premium::dir( 'admin/img/svg-icons.svg' );

		// If it exists, include it.
		if ( file_exists( $svg_icons ) ) {
			require_once $svg_icons;
		}
	}
}
