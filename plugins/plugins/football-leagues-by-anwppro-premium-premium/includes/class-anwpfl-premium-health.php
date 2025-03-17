<?php
/**
 * AnWP Football Leagues Premium :: Health
 *
 * @since 0.13.2
 */
class AnWPFL_Premium_Health {

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
	 */
	public function hooks() {
		add_filter( 'anwpfl/health/available_actions', [ $this, 'get_premium_actions' ] );
		add_filter( 'anwpfl/health/process_plugin_health_test', [ $this, 'process_plugin_premium_health_test' ], 10, 2 );
		add_filter( 'anwpfl/health/process_plugin_health_fix', [ $this, 'process_plugin_premium_health_fix' ], 10, 3 );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @return array
	 * @since  0.13.2
	 */
	public function get_premium_actions( $plugin_health_actions ) {

		// Add Premium Tables check
		$plugin_health_actions[] = [
			'slug'     => 'plugin_premium_tables',
			'title'    => 'Premium Plugin Tables',
			'fix_data' => (object) [],
			'status'   => '',
		];

		// Add API Import Missed IDs check
		if ( ! empty( AnWPFL_Premium_API::get_config_value( 'key', '' ) ) ) {
			$plugin_health_actions[] = [
				'slug'     => 'plugin_api_import_missed_mapped_ids',
				'title'    => 'API Import missed mapped IDs',
				'fix_data' => (object) [],
				'status'   => '',
			];
		}

		return $plugin_health_actions;
	}

	/**
	 * Handle plugin health tests
	 *
	 * @param $action_slug
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function process_plugin_premium_health_test( $result, $action_slug ) {

		$available_actions = [
			'plugin_premium_tables',
			'plugin_api_import_missed_mapped_ids',
		];

		$maybe_method_name = 'test_health_premium_' . $action_slug;

		if ( in_array( $action_slug, $available_actions, true ) && method_exists( $this, $maybe_method_name ) ) {
			return $this->{$maybe_method_name}();
		}

		return $result;
	}

	/*
	|--------------------------------------------------------------------
	|--------------------------------------------------------------------
	| Tests go here
	|--------------------------------------------------------------------
	|--------------------------------------------------------------------
	*/

	/**
	 * Test premium tables
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 *
	 * @return array
	 * @since        0.13.2
	 */
	private function test_health_premium_plugin_premium_tables() {

		global $wpdb;

		/*
		|--------------------------------------------------------------------
		| Check Competition IDs
		|--------------------------------------------------------------------
		*/
		$database_tables = $wpdb->get_col(
			$wpdb->prepare(
				'
				SELECT table_name
				FROM information_schema.TABLES
				WHERE table_schema = %s
				',
				DB_NAME
			)
		);

		$required_tables = [
			$wpdb->prefix . 'anwpfl_import_mapping',
		];

		$missing_tables = array_diff( $required_tables, $database_tables );

		/*
		|--------------------------------------------------------------------
		| Prepare Output
		|--------------------------------------------------------------------
		*/
		if ( count( $missing_tables ) ) {
			$output_text = '<span class="anwp-text-red-500">Some premium plugin tables don\'t exist. Ask plugin support for help.</span>';
		} else {
			$output_text = '<span class="anwp-text-green-500">Premium Plugin tables - OK</span>';
		}

		return [
			'result'   => true,
			'status'   => count( $missing_tables ) ? 'problems' : 'ok',
			'fix_data' => [
				'text'      => $output_text,
				'link_type' => 'link',
				'links'     => [],
			],
		];
	}

	/**
	 * Test missed mapped IDs
	 *
	 * @return array
	 * @since 0.13.2
	 */
	private function test_health_premium_plugin_api_import_missed_mapped_ids() {

		global $wpdb;

		$fix_links = [];

		/*
		|--------------------------------------------------------------------
		| Get Competition IDs
		|--------------------------------------------------------------------
		*/
		$competition_mapped_ids = $wpdb->get_col(
			"
			SELECT DISTINCT local_value
			FROM {$wpdb->prefix}anwpfl_import_mapping
			WHERE `type` IN ('competition-v3', 'config-saved', 'config-live', 'config-saved-v3', 'api_rounds', 'api_mapped_rounds')
			"
		);

		$competition_mapped_ids = array_unique( array_map( 'absint', $competition_mapped_ids ) );

		$all_competition_ids = get_posts(
			[
				'numberposts'   => - 1,
				'post_type'     => 'anwp_competition',
				'post_status'   => [ 'publish', 'stage_secondary', 'draft', 'trash' ],
				'cache_results' => false,
				'fields'        => 'ids',
			]
		);

		$all_competition_ids = array_unique( array_map( 'absint', $all_competition_ids ) );

		$missing_competition_ids = array_diff( $competition_mapped_ids, $all_competition_ids );

		if ( ! empty( $missing_competition_ids ) && count( $missing_competition_ids ) ) {
			$output_text[] = '<span class="anwp-text-red-500">You have invalid mapped Competition IDs (' . count( $missing_competition_ids ) . ')</span>';
			$fix_links[]   = [
				'text'      => 'Fix Competition IDs',
				'context'   => 'competition',
				'link_type' => 'action',
				'data'      => implode( ',', $missing_competition_ids ),
			];
		} else {
			$output_text[] = '<span class="anwp-text-green-500">Competition IDs - OK</span>';
		}

		/*
		|--------------------------------------------------------------------
		| Get Club IDs
		|--------------------------------------------------------------------
		*/
		$club_mapped_ids = $wpdb->get_col(
			"
			SELECT DISTINCT local_value
			FROM {$wpdb->prefix}anwpfl_import_mapping
			WHERE `type` = 'club'
			"
		);

		$club_mapped_ids = array_unique( array_map( 'absint', $club_mapped_ids ) );

		$all_club_ids = get_posts(
			[
				'numberposts'   => - 1,
				'post_type'     => 'anwp_club',
				'post_status'   => [ 'publish', 'draft', 'trash' ],
				'cache_results' => false,
				'fields'        => 'ids',
			]
		);

		$all_club_ids = array_unique( array_map( 'absint', $all_club_ids ) );

		$missing_club_ids = array_diff( $club_mapped_ids, $all_club_ids );

		if ( ! empty( $missing_club_ids ) && count( $missing_club_ids ) ) {
			$output_text[] = '<span class="anwp-text-red-500">You have invalid mapped Club IDs (' . count( $missing_club_ids ) . ')</span>';
			$fix_links[]   = [
				'text'      => 'Fix Club IDs',
				'context'   => 'club',
				'link_type' => 'action',
				'data'      => implode( ',', $missing_club_ids ),
			];
		} else {
			$output_text[] = '<span class="anwp-text-green-500">Club IDs - OK</span>';
		}

		/*
		|--------------------------------------------------------------------
		| Output
		|--------------------------------------------------------------------
		*/
		return [
			'result'   => true,
			'status'   => count( $fix_links ) ? 'problems' : 'ok',
			'fix_data' => [
				'text'      => implode( '<br>', $output_text ),
				'link_type' => 'action',
				'links'     => $fix_links,
			],
		];
	}

	/*
	|--------------------------------------------------------------------
	|--------------------------------------------------------------------
	| Fixes go here
	|--------------------------------------------------------------------
	|--------------------------------------------------------------------
	*/

	/**
	 * Handle plugin health fixes
	 *
	 * @param $result
	 * @param $action_slug
	 * @param $params
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function process_plugin_premium_health_fix( $result, $action_slug, $params ) {

		$available_actions = [
			'plugin_api_import_missed_mapped_ids',
		];

		$maybe_method_name = 'fix_health_premium_' . $action_slug;

		if ( in_array( $action_slug, $available_actions, true ) && method_exists( $this, $maybe_method_name ) ) {
			return $this->{$maybe_method_name}( $params );
		}

		return $result;
	}

	/**
	 * Try to fix missed mapped IDs problem.
	 *
	 * @noinspection PhpUnusedPrivateMethodInspection
	 *
	 * @param $params
	 *
	 * @return array|bool
	 * @since  0.13.2
	 */
	private function fix_health_premium_plugin_api_import_missed_mapped_ids( $params ) {

		$action_context = isset( $params['action_context'] ) ? sanitize_text_field( $params['action_context'] ) : '';

		if ( ! in_array( $action_context, [ 'competition', 'club' ], true ) ) {
			return false;
		}

		global $wpdb;

		$ids_to_remove = isset( $params['action_data'] ) ? wp_parse_id_list( $params['action_data'] ) : false;

		if ( empty( $ids_to_remove ) ) {
			return false;
		}

		foreach ( $ids_to_remove as $id_to_remove ) {
			if ( $this->check_post_id_exists( $id_to_remove ) ) {
				continue;
			}

			$wpdb->delete(
				$wpdb->prefix . 'anwpfl_import_mapping',
				[
					'local_value' => $id_to_remove,
				]
			);
		}

		return $this->test_health_premium_plugin_api_import_missed_mapped_ids();
	}

	/**
	 * Check Post ID exists
	 *
	 * @todo move to helper
	 * @param $post_id
	 *
	 * @return bool
	 */
	private function check_post_id_exists( $post_id ) {
		global $wpdb;

		$post_id = absint( $post_id );

		if ( empty( $post_id ) ) {
			return false;
		}

		if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE ID=%d", $post_id ) ) ) {
			return true;
		}

		return false;
	}
}
