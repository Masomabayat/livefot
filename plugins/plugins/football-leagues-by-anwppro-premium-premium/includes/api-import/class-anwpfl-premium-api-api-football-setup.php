<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AnWPFL_Premium_API_API_Football_Setup {

	/**
	 * API
	 *
	 * @var AnWPFL_Premium_API_API_Football
	 */
	protected $api;

	/**
	 * Constructor.
	 *
	 * @param AnWPFL_Premium_API_API_Football $api
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}


	/**
	 * Get list of all available API Setup methods
	 *
	 * @return string[]
	 */
	public function get_available_api_setup_methods() {
		return [
			'api_setup_check_api_key_valid',
			'api_setup_check_stats_team',
			'api_setup_fix_stats_team',
			'api_setup_check_stats_player',
			'api_setup_fix_stats_player',
			'api_setup_save_initial_config',
			'api_setup_save_settings',
			'api_setup_check_game_duplicates',
			'api_setup_check_team_duplicates',
		];
	}

	/**
	 * Save API Config
	 *
	 * @param $params
	 *
	 * @return array|WP_Error
	 * @since 0.15.4
	 */
	public function api_setup_save_settings( $params ) {

		$api_config = isset( $params['api_config'] ) ? $params['api_config'] : [];

		if ( empty( $api_config ) ) {
			return new WP_Error( 'rest_invalid', 'API Config is empty', [ 'status' => 400 ] );
		}

		if ( ! empty( $api_config['aff_links'] ) ) {
			$api_config['aff_links'] = array_filter(
				$api_config['aff_links'],
				function ( $i ) {
					return ! empty( $i );
				}
			);
		}

		update_option( 'anwpfl_api_import_config', $api_config );

		return [
			'result' => ! empty( $api_config ),
			'config' => $api_config,
		];
	}

	/**
	 * Save API Config
	 *
	 * @param $params
	 *
	 * @return array|WP_Error
	 * @since 0.15.4
	 */
	public function api_setup_save_initial_config( $params ) {

		$api_config = isset( $params['api_config'] ) ? $params['api_config'] : [];

		if ( empty( $api_config ) ) {
			return new WP_Error( 'rest_invalid', 'API Config is empty', [ 'status' => 400 ] );
		}

		$api_config = wp_parse_args(
			$api_config,
			[
				'initial_provider' => '',
				'provider'         => '',
			]
		);

		$api_config['provider']         = $api_config['initial_provider'];
		$api_config['initial_provider'] = '';

		if ( ! empty( $api_config['aff_links'] ) ) {
			$api_config['aff_links'] = array_filter(
				$api_config['aff_links'],
				function ( $i ) {
					return ! empty( $i );
				}
			);
		}

		update_option( 'anwpfl_api_import_config', $api_config );

		return [
			'result' => ! empty( $api_config ),
			'config' => $api_config,
		];
	}

	/**
	 * Check API Key is valid
	 *
	 * @param $params
	 *
	 * @return array
	 * @since 0.15.4
	 */
	public function api_setup_check_api_key_valid( $params ) {

		$params = wp_parse_args(
			$params,
			[
				'api_key'     => '',
				'request_url' => '',
			]
		);

		$this->api->config['key']         = sanitize_text_field( $params['api_key'] );
		$this->api->config['request_url'] = sanitize_key( $params['request_url'] );

		$response = $this->api->send_request_to_api( 'leagues' );

		return [ 'result' => empty( $response['errors'] ) && ! empty( $response['response'] ) ];
	}

	/**
	 * Check Game Duplicates
	 *
	 * @param $params
	 *
	 * @return array
	 * @since 0.15.4
	 */
	public function api_setup_check_game_duplicates( $params = [] ) {

		global $wpdb;

		// Delete OLD duplicate label
		$wpdb->delete(
			$wpdb->postmeta,
			[
				'meta_key' => '_anwpfl_match_duplicates',
			]
		);

		// Find Duplicates
		$items = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT external_value, COUNT(*) as qty
				FROM {$wpdb->prefix}anwpfl_import_mapping
				WHERE provider = %s
				  AND `type` = 'match'
				GROUP BY external_value
				HAVING COUNT(*) > 1
				",
				$this->api->provider
			)
		);

		$number_of_duplicates = 0;

		foreach ( $items as $item ) {
			$duplicates = $wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT local_value
					FROM {$wpdb->prefix}anwpfl_import_mapping
					WHERE provider = %s AND external_value = %d
					",
					$this->api->provider,
					$item->external_value
				)
			);

			$duplicates = array_map( 'absint', $duplicates );

			sort( $duplicates );
			array_shift( $duplicates );

			foreach ( $duplicates as $duplicate ) {
				update_post_meta( $duplicate, '_anwpfl_match_duplicates', 'yes' );
				$number_of_duplicates++;
			}
		}

		return [
			'result' => true,
			'qty'    => $number_of_duplicates,
		];
	}

	/**
	 * Check Team Duplicates
	 *
	 * @param $params
	 *
	 * @return array
	 * @since 0.15.4
	 */
	public function api_setup_check_team_duplicates( $params = [] ) {

		global $wpdb;

		// Delete OLD duplicate label
		$wpdb->delete(
			$wpdb->postmeta,
			[
				'meta_key' => '_anwpfl_club_duplicates',
			]
		);

		// Find Duplicates
		$items = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT external_value, COUNT(*) as qty
				FROM {$wpdb->prefix}anwpfl_import_mapping
				WHERE provider = %s
				  AND `type` = 'club'
				GROUP BY external_value
				HAVING COUNT(*) > 1
				",
				$this->api->provider
			)
		);

		$number_of_duplicates = 0;

		foreach ( $items as $item ) {
			$duplicates = $wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT local_value
					FROM {$wpdb->prefix}anwpfl_import_mapping
					WHERE provider = %s AND external_value = %d
					",
					$this->api->provider,
					$item->external_value
				)
			);

			$duplicates = array_map( 'absint', $duplicates );

			sort( $duplicates );
			array_shift( $duplicates );

			foreach ( $duplicates as $duplicate ) {
				update_post_meta( $duplicate, '_anwpfl_club_duplicates', 'yes' );
				$number_of_duplicates++;
			}
		}

		return [
			'result' => true,
			'qty'    => $number_of_duplicates,
		];
	}

	/**
	 * Check Team Stats Structure
	 *
	 * @param $params
	 *
	 * @return array
	 * @since 0.15.4
	 */
	public function api_setup_check_stats_team( $params = [] ) {

		$stats_options = json_decode( get_option( 'anwpfl_stats_columns_match_club' ) );

		$advanced_api_stats = [
			'shots_off_goal'   => false,
			'blocked_shots'    => false,
			'shots_insidebox'  => false,
			'shots_outsidebox' => false,
			'goalkeeper_saves' => false,
			'total_passes'     => false,
			'passes_accurate'  => false,
		];

		foreach ( $stats_options as $stats_option ) {
			if ( 'simple' === $stats_option->type && isset( $advanced_api_stats[ $stats_option->field_slug ] ) ) {
				$advanced_api_stats[ $stats_option->field_slug ] = true;
			}
		}

		return [ 'stats' => $advanced_api_stats ];
	}

	/**
	 * Check Team Stats Structure
	 *
	 * @param $params
	 *
	 * @return mixed
	 * @since 0.15.4
	 */
	public function api_setup_fix_stats_team( $params = [] ) {

		$stats_options = json_decode( get_option( 'anwpfl_stats_columns_match_club' ) );
		$stats_last_id = get_option( 'anwpfl_stats_columns_match_club_last_id' ) ? : 0;

		$api_stats_map = [
			'shots_off_goal'   => esc_html__( 'Shots off Goal', 'anwp-football-leagues-premium' ),
			'blocked_shots'    => esc_html__( 'Blocked Shots', 'anwp-football-leagues-premium' ),
			'shots_insidebox'  => esc_html__( 'Shots insidebox', 'anwp-football-leagues-premium' ),
			'shots_outsidebox' => esc_html__( 'Shots outsidebox', 'anwp-football-leagues-premium' ),
			'goalkeeper_saves' => esc_html__( 'Goalkeeper Saves', 'anwp-football-leagues-premium' ),
			'total_passes'     => esc_html__( 'Total passes', 'anwp-football-leagues-premium' ),
			'passes_accurate'  => esc_html__( 'Passes accurate', 'anwp-football-leagues-premium' ),
		];

		foreach ( $this->api_setup_check_stats_team()['stats'] as $stat_key => $stat_status ) {

			$max_option = 50;

			switch ( $stat_key ) {
				case 'passes_accurate':
				case 'total_passes':
					$max_option = 1000;
					break;

				case 'goalkeeper_saves':
					$max_option = 20;
					break;
			}

			if ( ! $stat_status ) {
				$stats_options[] = (object) [
					'type'       => 'simple',
					'name'       => isset( $api_stats_map[ $stat_key ] ) ? $api_stats_map[ $stat_key ] : ucwords( str_ireplace( '_', ' ', $stat_key ) ),
					'field_slug' => $stat_key,
					'visibility' => '',
					'postfix'    => '',
					'prefix'     => '',
					'digits'     => '',
					'max'        => $max_option,
					'id'         => ++ $stats_last_id,
				];
			}
		}

		$stats_options = wp_json_encode( $stats_options );

		if ( $stats_options ) {
			update_option( 'anwpfl_stats_columns_match_club', $stats_options, false );
		}

		if ( absint( $stats_last_id ) ) {
			update_option( 'anwpfl_stats_columns_match_club_last_id', absint( $stats_last_id ), false );
		}

		return $this->api_setup_check_stats_team();
	}

	/**
	 * Check Player Stats Structure
	 *
	 * @return mixed
	 * @since 0.15.4
	 */
	public function api_setup_check_stats_player() {

		$stats_options = json_decode( get_option( 'anwpfl_stats_columns_match_player' ) ) ?: [];

		$advanced_api_stats = [
			'goals__saves'           => false,
			'shots__total'           => false,
			'shots__on'              => false,
			'passes__total'          => false,
			'passes__accuracy'       => false,
			'passes__key'            => false,
			'tackles__total'         => false,
			'tackles__blocks'        => false,
			'tackles__interceptions' => false,
			'dribbles__attempts'     => false,
			'dribbles__success'      => false,
			'fouls__drawn'           => false,
			'fouls__committed'       => false,
			'rating'                 => false,
		];

		foreach ( $stats_options as $stats_option ) {
			if ( 'simple' === $stats_option->type && isset( $advanced_api_stats[ $stats_option->api_field ] ) ) {
				$advanced_api_stats[ $stats_option->api_field ] = true;
			}
		}

		return [ 'stats' => $advanced_api_stats ];
	}

	/**
	 * Check Player Stats Structure
	 *
	 * @return mixed
	 * @since 0.15.4
	 */
	public function api_setup_fix_stats_player() {

		$stats_options = json_decode( get_option( 'anwpfl_stats_columns_match_player' ), true ) ?: [];
		$stats_last_id = get_option( 'anwpfl_stats_columns_match_player_last_id' ) ?: 0;

		$api_stats_map = [
			'goals__saves'           => [
				'name'  => 'Saves',
				'abbr'  => 'Saves',
				'group' => 'g',
			],
			'shots__total'           => [
				'name'  => 'Shots Total',
				'abbr'  => 'S',
				'group' => '',
			],
			'shots__on'              => [
				'name'  => 'Shots On Target',
				'abbr'  => 'SO',
				'group' => '',
			],
			'passes__total'          => [
				'name'  => 'Total Passes',
				'abbr'  => 'PT',
				'group' => '',
			],
			'passes__accuracy'       => [
				'name'  => 'Accurate Passes',
				'abbr'  => 'PA',
				'group' => '',
			],
			'passes__key'            => [
				'name'  => 'Key Passes',
				'abbr'  => 'PK',
				'group' => '',
			],
			'tackles__total'         => [
				'name'  => 'Tackles Total',
				'abbr'  => 'TT',
				'group' => '',
			],
			'tackles__blocks'        => [
				'name'  => 'Tackles Blocks',
				'abbr'  => 'TB',
				'group' => '',
			],
			'tackles__interceptions' => [
				'name'  => 'Tackles Interceptions',
				'abbr'  => 'TI',
				'group' => '',
			],
			'dribbles__attempts'     => [
				'name'  => 'Dribbles Attempts',
				'abbr'  => 'DA',
				'group' => '',
			],
			'dribbles__success'      => [
				'name'  => 'Dribbles Success',
				'abbr'  => 'DS',
				'group' => '',
			],
			'fouls__drawn'           => [
				'name'  => 'Fouls Drawn',
				'abbr'  => 'FD',
				'group' => '',
			],
			'fouls__committed'       => [
				'name'  => 'Fouls Committed',
				'abbr'  => 'FC',
				'group' => '',
			],
			'rating'                 => [
				'name'  => 'Player rating',
				'abbr'  => 'Rating',
				'group' => '',
			],
		];

		foreach ( $this->api_setup_check_stats_player()['stats'] as $stat_key => $stat_status ) {
			if ( ! $stat_status && isset( $api_stats_map[ $stat_key ] ) ) {
				$stats_options[] = [
					'name'       => $api_stats_map[ $stat_key ]['name'],
					'abbr'       => $api_stats_map[ $stat_key ]['abbr'],
					'type'       => 'simple',
					'group'      => $api_stats_map[ $stat_key ]['group'],
					'prefix'     => '',
					'postfix'    => '',
					'visibility' => '',
					'digits'     => 'rating' === $stat_key ? 1 : '',
					'api_field'  => $stat_key,
					'id'         => ++ $stats_last_id,
				];

				if ( 'rating' === $stat_key && function_exists( 'cmb2_get_option' ) ) {
					cmb2_update_option( 'anwp_football_leagues_premium_options', 'player_rating', $stats_last_id );
				}
			}
		}

		if ( wp_json_encode( $stats_options ) ) {
			update_option( 'anwpfl_stats_columns_match_player', wp_json_encode( $stats_options ), false );
		}

		if ( absint( $stats_last_id ) ) {
			update_option( 'anwpfl_stats_columns_match_player_last_id', absint( $stats_last_id ), false );
		}

		foreach ( anwp_fl_pro()->stats->check_player_stats_db_sync_needed() as $stat_column_to_create ) {
			anwp_fl_pro()->stats->create_stat_column_in_players_table( $stat_column_to_create );
		}

		return $this->api_setup_check_stats_player();
	}
}
