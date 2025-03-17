<?php
/**
 * Import data from External API
 * AnWP Football Leagues Premium :: API
 *
 * @since  0.13.0
 * @package AnWP_Football_Leagues_Premium
 */

class AnWPFL_Premium_API {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 */
	protected $plugin = null;

	/**
	 * Data Providers.
	 *
	 * @var array
	 */
	public $providers = [];

	/**
	 * Constructor.
	 *
	 * @param  AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;

		$this->providers['api-football'] = include 'api-import/class-anwpfl-premium-api-api-football.php';

		// Init hooks
		$this->hooks();
	}

	/**
	 * Get API Configuration value
	 *
	 * @param $param
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	public static function get_config_value( $param, $default = '' ) {
		$api_config = get_option( 'anwpfl_api_import_config', [] );

		return $api_config[ $param ] ?? $default;
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
		add_action( 'admin_menu', [ $this, 'register_public_menu' ] );
		add_action( 'before_delete_post', [ $this, 'on_delete' ] );

		// Main Scheduled Tasks
		add_action( 'anwp_fl_api_scheduled_predictions', [ $this, 'run_scheduled_task' ] );
		add_action( 'anwp_fl_api_scheduled_kickoff', [ $this, 'run_scheduled_task' ] );
		add_action( 'anwp_fl_api_scheduled_odds', [ $this, 'run_scheduled_task' ] );
		add_action( 'anwp_fl_api_scheduled_injuries', [ $this, 'run_scheduled_task' ] );
		add_action( 'anwp_fl_api_scheduled_lineups', [ $this, 'run_scheduled_task' ] );
		add_action( 'anwp_fl_api_scheduled_finished', [ $this, 'run_scheduled_task' ] );
		add_action( 'anwp_fl_api_scheduled_live', [ $this, 'run_scheduled_task' ] );

		// Scheduled Subtasks
		add_action( 'anwp_fl_api_scheduled_import_lineups', [ $this, 'run_scheduled_task' ] );
		add_action( 'anwp_fl_api_scheduled_import_live', [ $this, 'run_scheduled_task' ] );
	}

	/**
	 * Register Public Menu
	 */
	public function register_public_menu() {
		add_menu_page(
			esc_html_x( 'FL API Import', 'admin page title', 'anwp-football-leagues-premium' ),
			esc_html_x( 'FL API Import', 'admin menu title', 'anwp-football-leagues-premium' ),
			'manage_options',
			'anwp-football-leagues-api',
			[ $this, 'render_api_page' ],
			'dashicons-rest-api',
			33
		);
	}

	/**
	 * Rendering Public Page
	 *
	 * @since 0.12.1
	 */
	public function render_api_page() {

		// Must check that the user has the required capability
		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'sports-leagues' ) );
		}

		AnWP_Football_Leagues_Premium::include_file( 'admin/views/api-import' );
	}

	/**
	 * Fires before removing a post.
	 *
	 * @param int $post_ID Post ID.
	 *
	 * @since 0.1.0
	 */
	public function on_delete( $post_ID ) {

		global $wpdb;

		$table     = $wpdb->prefix . 'anwpfl_import_mapping';
		$post_type = get_post_type( $post_ID );

		if ( in_array( $post_type, [ 'anwp_match', 'anwp_player', 'anwp_club', 'anwp_stadium', 'anwp_competition' ], true ) ) {

			$type = str_ireplace( 'anwp_', '', $post_type );

			$wpdb->delete(
				$table,
				[
					'type'        => $type,
					'local_value' => $post_ID,
				]
			);

			if ( 'match' === $type ) {
				$wpdb->delete(
					$table,
					[
						'type'        => 'match-live',
						'local_value' => $post_ID,
					]
				);

				$wpdb->delete(
					$table,
					[
						'type'        => 'match-lineups',
						'local_value' => $post_ID,
					]
				);
			}

			if ( 'competition' === $type ) {

				$wpdb->delete(
					$table,
					[
						'type'        => 'competition-v3',
						'local_value' => $post_ID,
					]
				);

				$wpdb->delete(
					$table,
					[
						'type'        => 'config-saved',
						'local_value' => $post_ID,
					]
				);

				$wpdb->delete(
					$table,
					[
						'type'        => 'config-live',
						'local_value' => $post_ID,
					]
				);

				$wpdb->delete(
					$table,
					[
						'type'        => 'config-saved-v3',
						'local_value' => $post_ID,
					]
				);

				$wpdb->delete(
					$table,
					[
						'type'        => 'config-live-v3',
						'local_value' => $post_ID,
					]
				);

				$wpdb->delete(
					$table,
					[
						'type'        => 'api_rounds',
						'local_value' => $post_ID,
					]
				);

				$wpdb->delete(
					$table,
					[
						'type'        => 'api_mapped_rounds',
						'local_value' => $post_ID,
					]
				);
			}
		}
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.11.2
	 */
	public function add_rest_routes() {

		register_rest_route(
			'anwpfl/api-import',
			'/(?P<api_method>[a-z_]+)/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'process_api_get_requests' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'anwpfl/api-import',
			'/(?P<api_method>[a-z_]+)/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'process_api_post_requests' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			'anwpfl/api-setup',
			'/(?P<api_method>[a-z_]+)/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'process_api_setup_requests' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Handle API Get Requests
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function process_api_get_requests( WP_REST_Request $request ) {

		AnWP_Football_Leagues_Premium::set_time_limit( 600 );
		do_action( 'qm/cease' );

		// Get Request params
		$params = $request->get_params();

		// Check API Method exists
		if ( empty( $params['api_method'] ) ) {
			return new WP_Error( 'rest_invalid', 'Incorrect API Method', [ 'status' => 400 ] );
		}

		// Check provider is set
		if ( empty( $params['api_provider'] ) || empty( $this->providers[ $params['api_provider'] ] ) ) {
			return new WP_Error( 'rest_invalid', 'API Data Provider is not set or invalid', [ 'status' => 400 ] );
		}

		// Check if method is available
		$provider          = $this->providers[ $params['api_provider'] ];
		$available_methods = method_exists( $provider, 'get_available_api_get_methods' ) ? $provider->get_available_api_get_methods() : [];

		if ( empty( $available_methods ) || ! in_array( $params['api_method'], $available_methods, true ) ) {
			return new WP_Error( 'rest_invalid', 'Method not allowed', [ 'status' => 400 ] );
		}

		return rest_ensure_response( $provider->{$params['api_method']}( $params ) );
	}

	/**
	 * Handle API POST Requests
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function process_api_post_requests( WP_REST_Request $request ) {

		AnWP_Football_Leagues_Premium::set_time_limit( 600 );
		do_action( 'qm/cease' );

		// Get Request params
		$params = $request->get_params();

		// Check API Method exists
		if ( empty( $params['api_method'] ) ) {
			return new WP_Error( 'rest_invalid', 'Incorrect API Method', [ 'status' => 400 ] );
		}

		// Check provider is set
		if ( empty( $params['api_provider'] ) || empty( $this->providers[ $params['api_provider'] ] ) ) {
			return new WP_Error( 'rest_invalid', 'API Data Provider is not set or invalid', [ 'status' => 400 ] );
		}

		// Check if method is available
		$provider          = $this->providers[ $params['api_provider'] ];
		$available_methods = method_exists( $provider->updater, 'get_available_api_update_methods' ) ? $provider->updater->get_available_api_update_methods() : [];

		if ( empty( $available_methods ) || ! in_array( $params['api_method'], $available_methods, true ) ) {
			return new WP_Error( 'rest_invalid', 'Method not allowed', [ 'status' => 400 ] );
		}

		return rest_ensure_response( $provider->updater->{$params['api_method']}( $params ) );
	}

	/**
	 * Handle scheduled requests
	 */
	public function run_scheduled_task( $args = [] ) {

		$current_hook = current_filter();
		$api_method   = 'run_scheduled_' . str_replace( 'anwp_fl_api_scheduled_', '', $current_hook );
		$provider     = $this->providers['api-football'];

		if ( method_exists( $provider->schedule, $api_method ) ) {
			$provider->schedule->{$api_method}( $args );
		}
	}

	/**
	 * Handle API POST Requests
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function process_api_setup_requests( WP_REST_Request $request ) {

		AnWP_Football_Leagues_Premium::set_time_limit( 600 );
		do_action( 'qm/cease' );

		// Get Request params
		$params = $request->get_params();

		// Check API Method exists
		if ( empty( $params['api_method'] ) ) {
			return new WP_Error( 'rest_invalid', 'Incorrect API Method', [ 'status' => 400 ] );
		}

		// Check provider is set
		if ( empty( $params['api_provider'] ) || empty( $this->providers[ $params['api_provider'] ] ) ) {
			return new WP_Error( 'rest_invalid', 'API Data Provider is not set or invalid', [ 'status' => 400 ] );
		}

		// Check if method is available
		$provider          = $this->providers[ $params['api_provider'] ];
		$available_methods = method_exists( $provider->setup, 'get_available_api_setup_methods' ) ? $provider->setup->get_available_api_setup_methods() : [];

		if ( empty( $available_methods ) || ! in_array( $params['api_method'], $available_methods, true ) ) {
			return new WP_Error( 'rest_invalid', 'Method not allowed', [ 'status' => 400 ] );
		}

		return rest_ensure_response( $provider->setup->{$params['api_method']}( $params ) );
	}

	/**
	 * Get provider data
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function get_data( $params, $default = [] ) {

		// Check API Method exists
		if ( empty( $params['get_data_method'] ) ) {
			return $default;
		}

		// Check provider is set
		$api_provider = self::get_config_value( 'provider' );

		if ( empty( $api_provider ) || empty( $this->providers[ $api_provider ] ) ) {
			return $default;
		}

		// Check if method is available
		$provider          = $this->providers[ $api_provider ];
		$available_methods = method_exists( $provider, 'get_available_get_data_methods' ) ? $provider->get_available_get_data_methods() : [];

		if ( empty( $available_methods ) || ! in_array( $params['get_data_method'], $available_methods, true ) ) {
			return $default;
		}

		return $provider->{$params['get_data_method']}( $params );
	}
}
