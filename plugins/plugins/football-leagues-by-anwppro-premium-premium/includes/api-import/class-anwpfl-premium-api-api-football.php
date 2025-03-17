<?php
/**
 * Import from External API
 * AnWP Football Leagues Premium :: Import.
 *
 * @since   0.13.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AnWPFL_Premium_API_API_Football', false ) ) :

	/**
	 * @property-read AnWPFL_Premium_API_API_Football_Helper $helper
	 */
	class AnWPFL_Premium_API_API_Football {

		/**
		 * Data Provider.
		 *
		 * @var string
		 */
		public $provider = null;

		/**
		 * @var AnWPFL_Premium_API_API_Football_Helper
		 */
		public $helper;

		/**
		 * @var AnWPFL_Premium_API_API_Football_Wizard
		 */
		public $wizard;

		/**
		 * @var AnWPFL_Premium_API_API_Football_Setup
		 */
		public $setup;

		/**
		 * @var AnWPFL_Premium_API_API_Football_Updater
		 */
		public $updater;

		/**
		 * @var AnWPFL_Premium_API_API_Football_Parser
		 */
		public $parser;

		/**
		 * @var AnWPFL_Premium_API_API_Football_Schedule
		 */
		public $schedule;

		/**
		 * API Config
		 */
		public $config = [];

		/**
		 * Store mapped stadiums data.
		 */
		public static $stadiums_mapped = null;

		/**
		 * Store mapped players data.
		 */
		public $players_mapped = null;

		/**
		 * Store mapped players data.
		 */
		public $coaches_mapped = null;

		/**
		 * Constructor.
		 *
		 * @since  0.8.11
		 */
		public function __construct() {

			// Set provider name
			$this->provider = 'api-football';

			$this->config = wp_parse_args(
				get_option( 'anwpfl_api_import_config' ) ?: [],
				[
					'key'                         => '',
					'request_url'                 => '',
					'cache_requests'              => 'yes',
					'stadiums'                    => 'no',
					'referees'                    => 'no',
					'coaches'                     => 'yes',
					'lineups_formation'           => 'no',
					'club_advanced_stats'         => 'no',
					'player_stats'                => 'no',
					'club_logos'                  => 'yes',
					'photos_coach'                => 'no',
					'photos_player'               => 'no',
					'stadium_photos'              => 'no',
					'photos_player_force'         => 'no',
					'predictions'                 => 'no',
					'prediction_show_bottom_line' => 'no',
					'predictions_data'            => 'advice',
					'live'                        => 'no',
					'reset_squad'                 => 'yes',
					'sslverify'                   => 'yes',
					'update_kickoff_0'            => '',
					'odds'                        => 'no',
					'odds_clickable'              => 'no',
					'aff_links'                   => [],
					'books'                       => [],
				]
			);

			// Extra classes
			AnWP_Football_Leagues_Premium::include_file( 'includes/api-import/class-anwpfl-premium-api-api-football-helper' );
			AnWP_Football_Leagues_Premium::include_file( 'includes/api-import/class-anwpfl-premium-api-api-football-wizard' );
			AnWP_Football_Leagues_Premium::include_file( 'includes/api-import/class-anwpfl-premium-api-api-football-setup' );
			AnWP_Football_Leagues_Premium::include_file( 'includes/api-import/class-anwpfl-premium-api-api-football-updater' );
			AnWP_Football_Leagues_Premium::include_file( 'includes/api-import/class-anwpfl-premium-api-api-football-parser' );
			AnWP_Football_Leagues_Premium::include_file( 'includes/api-import/class-anwpfl-premium-api-api-football-schedule' );

			$this->helper   = new AnWPFL_Premium_API_API_Football_Helper( $this );
			$this->wizard   = new AnWPFL_Premium_API_API_Football_Wizard( $this );
			$this->setup    = new AnWPFL_Premium_API_API_Football_Setup( $this );
			$this->updater  = new AnWPFL_Premium_API_API_Football_Updater( $this );
			$this->parser   = new AnWPFL_Premium_API_API_Football_Parser( $this );
			$this->schedule = new AnWPFL_Premium_API_API_Football_Schedule( $this );
		}

		/**
		 * Get list of all available GET API methods
		 *
		 * @return string[]
		 */
		public function get_available_api_get_methods(): array {
			return [
				'api_get_status',
				'api_get_debug_info',
				'api_get_leagues',
				'api_get_wizard_leagues',
				'api_get_league_teams',
				'api_get_local_competition_teams',
				'api_get_local_competition_games',
				'api_get_edit_structure_data',
				'get_available_local_competitions',
				'api_get_available_wizard_import',
			];
		}

		/**
		 * Get list of all available GET data methods
		 *
		 * @return string[]
		 */
		public function get_available_get_data_methods() {
			return [
				'get_data_saved_configs',
				'get_data_league_actions',
			];
		}

		/**
		 * Get API status.
		 *
		 * @return array|WP_Error
		 * @throws Exception
		 */
		public function api_get_leagues( $params = [] ) {

			$force_update = AnWP_Football_Leagues::string_to_bool( $params['force_update'] ?? false );

			$current_date      = new DateTime( current_time( 'mysql' ) );
			$unix_time         = $current_date->format( 'U' );
			$leagues_updated_u = absint( get_option( 'anwpfl_premium_api_leagues_updated_u' ) );

			if ( ! $force_update && ( $leagues_updated_u + DAY_IN_SECONDS ) > $unix_time ) {
				$leagues = get_option( 'anwpfl_premium_api_leagues' );

				if ( ! empty( $leagues ) ) {
					return [
						'leagues'         => $leagues,
						'leagues_updated' => get_option( 'anwpfl_premium_api_leagues_updated' ),
					];
				}
			}

			// Get Leagues from API
			$response = $this->send_request_to_api( 'leagues', $force_update ? 0 : DAY_IN_SECONDS );

			if ( ! empty( $response['errors'] ) ) {
				return new WP_Error( 'rest_invalid', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
			}

			if ( empty( $response['response'] ) ) {
				return new WP_Error( 'rest_invalid', 'Empty API Data', [ 'status' => 400 ] );
			}

			try {
				$leagues_updated   = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $unix_time );
				$leagues_updated_u = $unix_time;
			} catch ( Exception $e ) {
				$leagues_updated   = ' - datetime error  - ';
				$leagues_updated_u = 0;
			}

			// Cache results
			update_option( 'anwpfl_premium_api_leagues', $response['response'], false );
			update_option( 'anwpfl_premium_api_leagues_updated', $leagues_updated, false );
			update_option( 'anwpfl_premium_api_leagues_updated_u', $leagues_updated_u, false );

			return [
				'leagues'         => $response['response'],
				'leagues_updated' => $leagues_updated,
			];
		}

		/**
		 * Get API status.
		 *
		 * @return array|WP_Error
		 */
		public function api_get_status( $params = [] ) {

			if ( 'rapid' === $this->config['request_url'] ) {
				return new WP_Error( 'rest_invalid', 'RapidAPI status is not supported', [ 'status' => 400 ] );
			}

			// Send request to API
			$response = $this->send_request_to_api( 'status' );

			if ( ! empty( $response['errors'] ) ) {
				return new WP_Error( 'rest_invalid', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
			}

			if ( empty( $response['response'] ) ) {
				return new WP_Error( 'rest_invalid', 'Empty API Data', [ 'status' => 400 ] );
			}

			$api_response = $response['response'];

			ob_start();
			?>
			<div class="anwp-text-gray-600 anwp-text-sm">
				Account
			</div>
			<div class="anwp-text-gray-900 anwp-text-lg anwp-leading-1">
				<?php echo esc_html( ! empty( $api_response['account']['firstname'] ) ? $api_response['account']['firstname'] : '' ); ?>
				<?php echo esc_html( ! empty( $api_response['account']['lastname'] ) ? $api_response['account']['lastname'] : '' ); ?>
			</div>
			<div class="anwp-text-gray-600 anwp-text-sm mt-4">
				Subscription
			</div>
			<div class="anwp-text-gray-900 anwp-text-lg anwp-leading-1">
				<?php echo esc_html( ! empty( $api_response['subscription']['plan'] ) ? $api_response['subscription']['plan'] : '' ); ?>
				<span class="anwp-text-sm">(<?php echo esc_html( ! empty( $api_response['subscription']['end'] ) ? $api_response['subscription']['end'] : '' ); ?>)</span>
				- <?php echo empty( $api_response['subscription']['active'] ) ? '<span class="anwp-text-red-800">Inactive</span>' : '<span class="anwp-text-green-800">Active</span>'; ?>
			</div>
			<div class="anwp-text-gray-600 anwp-text-sm mt-4">
				Requests (current/day limit)
			</div>
			<div class="anwp-text-gray-900 anwp-text-lg anwp-leading-1">
				<?php echo esc_html( isset( $api_response['requests']['current'] ) ? $api_response['requests']['current'] : '' ); ?>/<?php echo esc_html( isset( $api_response['requests']['limit_day'] ) ? $api_response['requests']['limit_day'] : '' ); ?>
			</div>
			<?php
			return [ 'app_html' => ob_get_clean() ];
		}

		/**
		 * Get API debug info.
		 *
		 * @return array|WP_Error
		 */
		public function api_get_debug_info( $params = [] ) {

			if ( empty( $params['competition'] ) ) {
				return new WP_Error( 'rest_invalid', 'Error', [ 'status' => 400 ] );
			}

			$competition_data = $params['competition'];

			// Get Fixtures from API
			$response = $this->send_request_to_api(
				'fixtures',
				0,
				[
					'league' => $competition_data['id'],
					'season' => $competition_data['year'],
				]
			);

			if ( ! empty( $response['errors'] ) ) {
				return new WP_Error( 'anwp_rest_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
			}

			if ( empty( $response['response'] ) ) {
				return new WP_Error( 'anwp_rest_error', 'Empty API Data - Fixtures', [ 'status' => 400 ] );
			}

			ob_start();
			echo '<pre>';
			print_r( $response['response'] ); // phpcs:ignore
			echo '</pre>';

			return [ 'app_html' => ob_get_clean() ];
		}

		/**
		 * Get API League teams by season.
		 *
		 * @return array|WP_Error
		 */
		public function api_get_league_teams( $params = [] ) {

			// Get params
			$api_league_id = empty( $params['league_id'] ) ? 0 : absint( $params['league_id'] );
			$api_season    = empty( $params['season'] ) ? 0 : absint( $params['season'] );

			if ( ! $api_league_id || ! $api_season ) {
				return new WP_Error( 'rest_invalid', 'Incorrect data', [ 'status' => 400 ] );
			}

			// Get Leagues from API
			$response = $this->send_request_to_api(
				'teams',
				DAY_IN_SECONDS,
				[
					'league' => $api_league_id,
					'season' => $api_season,
				]
			);

			if ( ! empty( $response['errors'] ) ) {
				return new WP_Error( 'rest_invalid', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
			}

			if ( empty( $response['response'] ) ) {
				return new WP_Error( 'rest_invalid', 'Empty API Data', [ 'status' => 400 ] );
			}

			$api_response = $response['response'];

			/*
			|--------------------------------------------------------------------
			| Prepare output data
			|--------------------------------------------------------------------
			*/
			$teams = [];
			foreach ( $api_response as $team_data ) {
				if ( ! empty( $team_data['team']['id'] ) && ! empty( $team_data['team']['name'] ) ) {
					$teams[] = [
						'id'      => absint( $team_data['team']['id'] ),
						'name'    => $team_data['team']['name'],
						'checked' => true,
						'action'  => absint( $this->helper->get_mapped_club_id( $team_data['team']['id'] ) ) ? 'update' : 'create',
					];
				}
			}

			return [
				'teams' => $teams,
			];
		}

		/**
		 * Get Local League teams
		 *
		 * @return array|WP_Error
		 */
		public function api_get_local_competition_teams( $params = [] ) {

			if ( empty( $params['competition_id'] ) ) {
				return [];
			}

			$team_ids = anwp_football_leagues()->competition->get_competition_multistage_clubs( $params['competition_id'] );
			$teams    = [];

			if ( ! empty( $team_ids ) && is_array( $team_ids ) ) {
				$team_ids = array_unique( $team_ids );

				foreach ( $team_ids as $team_id ) {
					$teams[] = [
						'id'    => $team_id,
						'title' => anwp_football_leagues()->club->get_club_title_by_id( $team_id ),
					];
				}
			}

			return [
				'teams' => $teams,
			];
		}

		/**
		 * Get Local League finished games
		 *
		 * @return array|WP_Error
		 */
		public function api_get_local_competition_games( $params = [] ) {

			if ( empty( $params['competition_id'] ) ) {
				return [];
			}

			$query_args = [
				'competition_id' => absint( $params['competition_id'] ),
				'show_secondary' => 1,
				'type'           => 'result',
			];

			// Get finished matches
			$local_matches     = [];
			$local_matches_raw = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $query_args );

			if ( ! empty( $local_matches_raw ) && is_array( $local_matches_raw ) ) {
				foreach ( $local_matches_raw as $local_match ) {
					$local_matches[] = [
						'id'    => $local_match->match_id,
						'title' => sprintf(
							'%s - %s - %d:%d (%s)',
							anwp_football_leagues()->club->get_club_title_by_id( $local_match->home_club ),
							anwp_football_leagues()->club->get_club_title_by_id( $local_match->away_club ),
							$local_match->home_goals,
							$local_match->away_goals,
							explode( ' ', $local_match->kickoff )[0]
						),
					];
				}
			}

			return [
				'games' => $local_matches,
			];
		}

		/**
		 * Send request to API
		 *
		 * @param string $endpoint
		 * @param int    $cache_time
		 * @param array  $query_args
		 *
		 * @return mixed
		 * @since 0.13.0
		 */
		public function send_request_to_api( $endpoint, $cache_time = 0, $query_args = [] ) {

			$response  = null;
			$cache_key = '';

			if ( 'yes' === $this->config['cache_requests'] && $cache_time ) {
				$serialized_data = [
					'provider'   => $this->provider,
					'endpoint'   => $endpoint,
					'rapid_url'  => 'rapid' === $this->config['request_url'],
					'query_args' => $query_args,
				];

				// Get transient cache key
				$cache_key = 'ANWPFL-API-IMPORT-' . md5( maybe_serialize( $serialized_data ) );

				// Try to get saved transient
				$response = get_transient( $cache_key );

				if ( ! empty( $response ) ) {
					return $response;
				}
			}

			$args = [
				'method'  => 'GET',
				'headers' => [
					'X-RapidAPI-Key' => $this->config['key'],
					'Accept'         => 'application/json',
				],
			];

			if ( 'no' === $this->config['sslverify'] ) {
				$args['sslverify'] = false;
			}

			try {

				$api_url = 'https://v3.football.api-sports.io/';

				if ( 'rapid' === $this->config['request_url'] ) {
					$api_url = 'https://api-football-v1.p.rapidapi.com/v3/';
				}

				$full_api_url = add_query_arg(
					$query_args,
					$api_url . sanitize_text_field( $endpoint )
				);

				$api_response = wp_remote_get( $full_api_url, $args );

				if ( 200 === wp_remote_retrieve_response_code( $api_response ) ) {
					$response = json_decode( wp_remote_retrieve_body( $api_response ), true );
				} elseif ( 401 === wp_remote_retrieve_response_code( $api_response ) ) {
					return [ 'errors' => [ 'Unauthorized - check API key or URL' ] ];
				} elseif ( 403 === wp_remote_retrieve_response_code( $api_response ) ) {

					$errors_array = [ 'Forbidden - 403 error' ];

					if ( ! empty( json_decode( wp_remote_retrieve_body( $api_response ), true ) ) ) {
						$errors_array[] = json_decode( wp_remote_retrieve_body( $api_response ), true )['message'];
					}

					return [ 'errors' => $errors_array ];
				}

				// Try one more time
				if ( empty( $response['response'] ) ) {
					sleep( 5 );

					$api_response = wp_remote_get( $full_api_url, $args );

					if ( 200 === wp_remote_retrieve_response_code( $api_response ) ) {
						$response = json_decode( wp_remote_retrieve_body( $api_response ), true );
					} elseif ( 401 === wp_remote_retrieve_response_code( $api_response ) ) {
						return [ 'errors' => [ 'Unauthorized - check API key or URL' ] ];
					}
				}

				if ( ! empty( $response['errors'] ) ) {
					return [ 'errors' => $response['errors'] ];
				}
			} catch ( Exception $e ) {
				return [ 'errors' => [ $e->getMessage() ] ];
			}

			if ( ! empty( $response ) && $cache_time && $cache_key ) {
				set_transient( $cache_key, $response, $cache_time );
			}

			return $response;
		}

		/**
		 * Get list of saved configs.
		 *
		 * @return array
		 */
		public function get_data_saved_configs() {

			global $wpdb;

			// Get saved in DB configs
			$saved_configs = $wpdb->get_results(
				"
					SELECT external_value, local_value, status, extra_data
					FROM {$wpdb->prefix}anwpfl_import_mapping
					WHERE       provider = 'api-football'
					            AND type = 'config-saved-v3'
				"
			);

			// Prepare config data
			if ( ! empty( $saved_configs ) ) {

				// Add local competitions data
				$competitions = anwp_fl()->competition->get_competitions( true );

				foreach ( $saved_configs as $saved_config ) {

					// Parse extra data
					$extra_data                    = json_decode( $saved_config->extra_data );
					$saved_config->external_season = $extra_data->season ?? '';

					if ( ! empty( wp_list_filter( $competitions, [ 'id' => absint( $saved_config->local_value ) ] ) ) ) {
						$competition = array_values( wp_list_filter( $competitions, [ 'id' => absint( $saved_config->local_value ) ] ) )[0];

						$saved_config->local_title  = $competition->title ?? '';
						$saved_config->local_logo   = $competition->logo ?? '';
						$saved_config->local_season = $competition->season_text ?? '';
						$saved_config->local_edit   = isset( $competition->id ) ? admin_url( '/post.php?post=' . absint( $competition->id ) . '&action=edit' ) : '';
					}
				}
			}

			return $saved_configs;
		}

		/**
		 * Update clubs data.
		 *
		 * @param array $team_ids
		 * @param int   $api_league_id
		 * @param int   $api_season
		 *
		 * @return int|WP_Error
		 */
		public function update_clubs_data( $team_ids, $api_league_id, $api_season ) {

			global $wpdb;

			$team_ids = wp_parse_id_list( $team_ids );

			$updated_clubs = 0;

			// Get Leagues from API
			$response = $this->send_request_to_api(
				'teams',
				DAY_IN_SECONDS,
				[
					'league' => $api_league_id,
					'season' => $api_season,
				]
			);

			if ( ! empty( $response['errors'] ) ) {
				return new WP_Error( 'rest_invalid', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
			}

			if ( empty( $response['response'] ) ) {
				return new WP_Error( 'rest_invalid', 'Empty API Data', [ 'status' => 400 ] );
			}

			$api_response = $response['response'];

			foreach ( $api_response as $team_data ) {
				if ( ! empty( $team_data['team']['id'] ) && in_array( absint( $team_data['team']['id'] ), $team_ids, true ) ) {

					$team = wp_parse_args(
						$team_data['team'],
						[
							'name'       => '',
							'id'         => '',
							'logo'       => '',
							'code'       => '',
							'country'    => '',
							'founded'    => '',
							'national'   => '',
							'create_new' => true,
						]
					);

					/*
					|--------------------------------------------------------------------
					| Create or Update club
					|--------------------------------------------------------------------
					*/
					$club_api_id   = absint( $team['id'] );
					$club_local_id = absint( $this->helper->get_mapped_club_id( $club_api_id ) );

					// Extra check for existing team ID
					if ( empty( $club_local_id ) ) {
						$wpdb->flush();

						$maybe_club_local_id = $wpdb->get_var(
							$wpdb->prepare(
								"
								SELECT local_value
								FROM {$wpdb->prefix}anwpfl_import_mapping
								WHERE       provider = %s
								            AND type = 'club'
								            AND external_value = %d
								",
								$this->provider,
								$club_api_id
							)
						);

						if ( absint( $maybe_club_local_id ) ) {
							$club_local_id = absint( $maybe_club_local_id );
						}
					}

					$post_club_array = [
						'post_status' => 'publish',
						'post_type'   => 'anwp_club',
					];

					$meta_club_array = [];

					$meta_club_array['_anwpfl_is_national_team'] = AnWP_Football_Leagues::string_to_bool( $team['national'] ) ? 'yes' : '';

					if ( trim( $team['founded'] ?? '' ) ) {
						$meta_club_array['_anwpfl_founded'] = $team['founded'];
					}

					if ( trim( $team['country'] ) && anwp_football_leagues_premium()->data->get_api_country_code_by_name( $team['country'] ) ) {
						$meta_club_array['_anwpfl_nationality'] = anwp_football_leagues_premium()->data->get_api_country_code_by_name( $team['country'] );
					}

					if ( 'no' !== $this->config['stadiums'] && ! empty( $team_data['venue'] ) ) {
						$meta_club_array['_anwpfl_stadium'] = $this->get_mapped_stadium_id( $team_data['venue'] );
					}

					if ( $club_local_id ) {
						$team['create_new']            = false;
						$post_club_array['ID']         = $club_local_id;
						$post_club_array['post_title'] = get_the_title( $club_local_id );
					} else {
						$post_club_array['post_title'] = wp_strip_all_tags( $team['name'] );
					}

					if ( ( ! $club_local_id || ! get_post_meta( $club_local_id, '_anwpfl_abbr', true ) ) && $team['code'] ) {
						$meta_club_array['_anwpfl_abbr'] = $team['code'];
					}

					if ( ! empty( $meta_club_array ) ) {
						$post_club_array['meta_input'] = $meta_club_array;
					}

					$club_local_id = wp_insert_post( $post_club_array );

					if ( ! $club_local_id ) {
						continue;
					}

					// Map API and Local club on mapping table
					if ( $team['create_new'] ) {
						$wpdb->replace(
							$wpdb->prefix . 'anwpfl_import_mapping',
							[
								'provider'       => $this->provider,
								'type'           => 'club',
								'local_value'    => $club_local_id,
								'external_value' => $club_api_id,
								'extra_data'     => '',
							]
						);
					}

					// Club logo (upload if not set)
					if ( 'no' !== $this->config['club_logos'] && ! absint( get_post_meta( $club_local_id, '_anwpfl_logo_id', true ) ) && ! absint( get_post_meta( $club_local_id, '_anwpfl_logo_big_id', true ) ) ) {
						if ( ! empty( $team['logo'] ) ) {
							$this->upload_logo( $team['logo'], $club_local_id, 'club' );
						}
					}

					// Set home team in Stadium
					if ( ! empty( $meta_club_array['_anwpfl_stadium'] ) ) {
						$clubs = get_post_meta( $meta_club_array['_anwpfl_stadium'], '_anwpfl_clubs', true );

						if ( ! empty( $clubs ) && is_array( $clubs ) ) {
							$clubs[] = (string) $club_local_id;
							$clubs   = array_unique( $clubs );
						} else {
							$clubs = [ (string) $club_local_id ];
						}

						update_post_meta( $meta_club_array['_anwpfl_stadium'], '_anwpfl_clubs', $clubs );
					}

					$updated_clubs++;
				}
			}

			return $updated_clubs;
		}

		/**
		 * Get mapped stadium id.
		 *
		 * @param array $stadium_data
		 *
		 * @return string
		 * @since 0.13.0
		 */
		public function get_mapped_stadium_id( $stadium_data ) {

			if ( empty( $stadium_data['id'] ) ) {
				return false;
			}

			$stadium_api_id = $stadium_data['id'];

			$this->init_stadiums_map();

			if ( isset( self::$stadiums_mapped[ $stadium_api_id ] ) && ! empty( self::$stadiums_mapped[ $stadium_api_id ]->local_value ) ) {
				return self::$stadiums_mapped[ $stadium_api_id ]->local_value;
			} else {
				// Try to create stadium if not exists
				return $this->update_stadium( $stadium_data );
			}
		}

		/**
		 * Update stadium data.
		 *
		 * @param array $api_data
		 *
		 * @return mixed
		 * @since 0.13.0
		 */
		private function update_stadium( $api_data ) {

			if ( empty( $api_data['name'] ) ) {
				return false;
			}

			$api_data = wp_parse_args(
				$api_data,
				[
					'name'     => '',
					'id'       => '',
					'address'  => '',
					'city'     => '',
					'capacity' => '',
					'surface'  => '',
					'image'    => '',
				]
			);

			$post_data = [
				'post_title'  => sanitize_text_field( $api_data['name'] ),
				'post_status' => 'publish',
				'post_type'   => 'anwp_stadium',
				'ID'          => 0,
			];

			// Extra check for post ID (look for stadium with the same name without mapping)
			global $wpdb;

			$local_value = $wpdb->get_var(
				$wpdb->prepare(
					"
						SELECT p.ID
						FROM $wpdb->posts p
						LEFT JOIN {$wpdb->prefix}anwpfl_import_mapping m ON ( m.local_value = p.ID AND m.provider = %s )
						WHERE       p.post_type = 'anwp_stadium' AND p.post_status = 'publish'
						            AND p.post_title = %s
						            AND m.external_value IS NULL
						",
					$this->provider,
					$post_data['post_title']
				)
			);

			if ( absint( $local_value ) ) {
				$post_data['ID'] = absint( $local_value );
			}

			// Handle Stadium meta
			$meta_array = [];

			if ( trim( $api_data['surface'] ?? '' ) ) {
				$meta_array['_anwpfl_surface'] = $api_data['surface'];
			}

			if ( trim( $api_data['address'] ?? '' ) ) {
				$meta_array['_anwpfl_address'] = $api_data['address'];
			}

			if ( trim( $api_data['city'] ?? '' ) ) {
				$meta_array['_anwpfl_city'] = $api_data['city'];
			}

			if ( trim( $api_data['capacity'] ?? '' ) ) {
				$meta_array['_anwpfl_capacity'] = $api_data['capacity'];
			}

			if ( ! empty( $meta_array ) ) {
				$post_data['meta_input'] = $meta_array;
			}

			try {

				// Try to insert Post
				$post_id = wp_insert_post( $post_data );

				if ( $this->insert_mapped_link( 'stadium', $post_id, $api_data['id'] ) ) {
					self::$stadiums_mapped[ $api_data['id'] ] = (object) [
						'external_value' => $api_data['id'],
						'local_value'    => $post_id,
						'status'         => '',
					];
				}

				if ( $post_id ) {
					if ( 'no' !== $this->config['stadium_photos'] && ! empty( $api_data['image'] ) ) {
						$this->upload_logo( $api_data['image'], $post_id, 'stadium' );
					}
				}

				return $post_id ?: false;

			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Upload logo.
		 *
		 * @param string $url
		 * @param int    $post_id
		 * @param string $context
		 *
		 * @return string
		 * @since 0.13.0
		 */
		public function upload_logo( $url, $post_id, $context = 'club' ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$image_id = media_sideload_image( $url, 0, null, 'id' );
			$src      = '';

			if ( $image_id && ! is_wp_error( $image_id ) ) {
				$src = wp_get_attachment_url( $image_id );
			}

			if ( empty( $src ) ) {
				return '';
			}

			switch ( $context ) {
				case 'club':
					update_post_meta( $post_id, '_anwpfl_logo_id', $image_id );
					update_post_meta( $post_id, '_anwpfl_logo_big_id', $image_id );
					update_post_meta( $post_id, '_anwpfl_logo', $src );
					update_post_meta( $post_id, '_anwpfl_logo_big', $src );
					break;

				case 'competition':
					update_post_meta( $post_id, '_anwpfl_logo_id', $image_id );
					update_post_meta( $post_id, '_anwpfl_logo', $src );
					update_post_meta( $post_id, '_anwpfl_logo_big_id', $image_id );
					update_post_meta( $post_id, '_anwpfl_logo_big', $src );
					break;

				case 'stadium':
				case 'coach':
					update_post_meta( $post_id, '_anwpfl_photo_id', $image_id );
					update_post_meta( $post_id, '_anwpfl_photo', $src );
					break;
			}

			return $src;
		}

		/**
		 * Create stadiums map.
		 *
		 * @since 0.13.0
		 */
		private function init_stadiums_map() {

			if ( null === self::$stadiums_mapped ) {
				global $wpdb;

				$items = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT external_value, local_value, status
						FROM {$wpdb->prefix}anwpfl_import_mapping
						WHERE       provider = %s
						            AND type = 'stadium'
						",
						$this->provider
					),
					OBJECT_K
				);

				self::$stadiums_mapped = ( ! empty( $items ) && is_array( $items ) ) ? $items : [];
			}
		}

		/**
		 * Insert mapped info into Import Mapped DB table.
		 *
		 * @param $type
		 * @param $local_id
		 * @param $api_id
		 * @param string $mapped_status
		 *
		 * @return false|int
		 * @since 0.13.0
		 */
		public function insert_mapped_link( $type, $local_id, $api_id, string $mapped_status = '' ) {

			if ( ! in_array( $type, [ 'stadium', 'match', 'player', 'coach' ], true ) || ! absint( $local_id ) ) {
				return false;
			}

			global $wpdb;

			$table = $wpdb->anwpfl_import_mapping;

			// Prepare data to insert
			$data = [
				'provider'       => $this->provider,
				'type'           => $type,
				'local_value'    => absint( $local_id ),
				'external_value' => absint( $api_id ),
				'status'         => $mapped_status,
				'extra_data'     => '',
			];

			return $wpdb->replace( $table, $data );
		}

		/**
		 * Create players map.
		 *
		 * @since 0.13.0
		 */
		public function init_players_map() {
			if ( null === $this->players_mapped ) {
				global $wpdb;

				$items = $wpdb->get_results(
					$wpdb->prepare(
						"
					SELECT external_value, local_value, status
					FROM {$wpdb->prefix}anwpfl_import_mapping
					WHERE       provider = %s
					            AND type = 'player'
					",
						$this->provider
					),
					OBJECT_K
				);

				if ( ! empty( $items ) && is_array( $items ) ) {
					$this->players_mapped = $items;
				} else {
					$this->players_mapped = [];
				}
			}
		}

		/**
		 * Get API fixture by ID
		 *
		 * @param $match_api_id
		 *
		 * @return array|WP_Error
		 * @since 0.13.0
		 */
		public function get_fixture_from_api( $match_api_id ) {

			// Get Fixtures from API
			$response = $this->send_request_to_api(
				'fixtures',
				120,
				[
					'id' => $match_api_id,
				]
			);

			if ( ! empty( $response['errors'] ) ) {
				return new WP_Error( 'anwp_rest_error', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
			}

			if ( empty( $response['response'] ) || empty( $response['response'][0] ) || empty( $response['response'][0]['fixture'] ) ) {
				return new WP_Error( 'anwp_rest_error', 'Empty API Data - Fixture', [ 'status' => 400 ] );
			}

			if ( absint( $response['response'][0]['fixture']['id'] ) !== absint( $match_api_id ) ) {
				return new WP_Error( 'anwp_rest_error', 'Invalid API Data - Fixture ID', [ 'status' => 400 ] );
			}

			return $response['response'][0];
		}

		/**
		 * Get referee ID by its name.
		 *
		 * @param $api_referee_name_raw
		 *
		 * @return int|string
		 * @since 0.13.0
		 */
		public function get_referee_id_by_api_name( $api_referee_name_raw ) {

			$api_referee_name = trim( explode( ',', $api_referee_name_raw )[0] );

			if ( empty( $api_referee_name ) ) {
				return '';
			}

			return anwp_football_leagues_premium()->referee->find_referee_by_name( $api_referee_name, $api_referee_name_raw );
		}

		/**
		 * Get or Create player if not exists.
		 *
		 * @param int    $player_id
		 * @param string $player_name
		 * @param int $club_id
		 *
		 * @return mixed
		 * @since 0.13.0
		 */
		public function get_or_create_player( int $player_id, string $player_name, int $club_id = 0 ) {

			if ( empty( $player_id ) ) {
				return false;
			}

			if ( isset( $this->players_mapped[ $player_id ] ) && ! empty( $this->players_mapped[ $player_id ]->local_value ) ) {
				return $this->players_mapped[ $player_id ]->local_value;
			} else {
				// Try to create player if not exists
				return $this->helper->update_player(
					[
						'player' => [
							'name' => $player_name,
							'id'   => $player_id,
						],
					],
					false,
					absint( $this->helper->get_mapped_club_id( $club_id ) ?? 0 )
				);
			}
		}

		/**
		 * Get API Competition Structure data.
		 *
		 * @return array|WP_Error
		 */
		public function api_get_edit_structure_data( $params = [] ) {

			$competition_api_id     = isset( $params['api_id'] ) ? absint( $params['api_id'] ) : '';
			$competition_api_season = isset( $params['api_season'] ) ? absint( $params['api_season'] ) : '';
			$competition_local_id   = isset( $params['local_id'] ) ? absint( $params['local_id'] ) : '';

			if ( ! $competition_api_id || ! $competition_local_id || ! $competition_api_season ) {
				return new WP_Error( 'rest_anwp_fl_error', 'Invalid Initial Data', [ 'status' => 400 ] );
			}

			$query_args = [
				'league' => $competition_api_id,
				'season' => $competition_api_season,
			];

			// Get Rounds from API
			$response = $this->send_request_to_api( 'fixtures/rounds', 600, $query_args );

			if ( ! empty( $response['errors'] ) ) {
				return new WP_Error( 'rest_invalid', implode( '; ', $response['errors'] ), [ 'status' => 400 ] );
			}

			if ( empty( $response['response'] ) ) {
				return new WP_Error( 'rest_invalid', 'Empty API Data', [ 'status' => 400 ] );
			}

			$api_rounds = $response['response'];

			// Get local competition structure
			$local_competition = anwp_football_leagues()->competition->get_competition( $competition_local_id );
			$local_stages      = [ $local_competition ];

			if ( empty( $local_competition ) ) {
				return new WP_Error( 'rest_anwp_fl_error', 'Local Competition not exists', [ 'status' => 400 ] );
			}

			if ( 'main' === $local_competition->multistage ) {
				$all_competitions = anwp_football_leagues()->competition->get_competitions();

				if ( ! empty( $all_competitions ) && is_array( $all_competitions ) ) {
					foreach ( $all_competitions as $competition ) {
						if ( absint( $competition->multistage_main ) === absint( $competition_local_id ) ) {
							$local_stages[] = $competition;
						}
					}
				}
			}

			return [
				'api_rounds'    => $api_rounds,
				'stages'        => $local_stages,
				'mapped_rounds' => $this->helper->get_api_mapped_rounds( $competition_api_id, $competition_local_id ),
			];
		}

		/**
		 * Get available (not mapped) local competitions.
		 *
		 * @return array|WP_Error
		 */
		public function get_available_local_competitions() {

			global $wpdb;

			$mapped_competitions = $wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT local_value
					FROM {$wpdb->prefix}anwpfl_import_mapping
					WHERE       provider = %s
					            AND type = 'competition-v3'
					",
					$this->provider
				)
			);

			$mapped_competitions = array_unique( array_map( 'absint', $mapped_competitions ) );
			$all_competitions    = anwp_football_leagues()->competition->get_competitions();
			$output              = [];

			if ( ! empty( $all_competitions ) && is_array( $all_competitions ) ) {
				foreach ( $all_competitions as $competition ) {
					if ( 'secondary' === $competition->multistage || in_array( absint( $competition->id ), $mapped_competitions, true ) ) {
						continue;
					}

					$output[] = [
						'local_value'  => $competition->id,
						'local_title'  => $competition->title,
						'local_season' => $competition->season_text,
						'local_logo'   => $competition->logo ?: '',
					];
				}
			}

			return [
				'competitions' => $output,
			];
		}

		/**
		 * Get list of saved competitions.
		 *
		 * @return array
		 */
		public function get_data_league_actions(): array {

			global $wpdb;

			$options = [];

			$rows = $wpdb->get_results(
				"
				SELECT local_value, external_value, extra_data
				FROM {$wpdb->prefix}anwpfl_import_mapping
				WHERE       provider = 'api-football'
				            AND type = 'config-saved-v3'
				            AND status = 'true'
			"
			);

			if ( empty( $rows ) ) {
				return [];
			}

			// Load saved options
			$saved = get_option( 'anwp_fl_api_league_actions' );

			if ( empty( $saved ) || ! is_array( $saved ) ) {
				$saved = [];
			}

			// Prepare options
			foreach ( $rows as $row ) {
				$competition_data = anwp_fl()->competition->get_competition( $row->local_value );
				$extra_data       = json_decode( $row->extra_data, true ) ?: [];

				$options[] = [
					'local_id'   => $row->local_value,
					'api_id'     => $row->external_value,
					'api_season' => $extra_data['season'] ?? '',
					'title'      => $competition_data->title,
					'logo'       => $competition_data->logo ?: '',
					'finished'   => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['finished'] ),
					'lineups'    => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['lineups'] ),
					'kickoff'    => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['kickoff'] ),
					'prediction' => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['prediction'] ),
					'odds'       => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['odds'] ),
					'injuries'   => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['injuries'] ),
					'transfers'  => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['transfers'] ),
					'live'       => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['live'] ),
					'players'    => isset( $saved[ $row->local_value ] ) && ! empty( $saved[ $row->local_value ]['players'] ),
				];
			}

			return wp_list_sort( $options, 'title' );
		}

		/**
		 * Create coaches map.
		 *
		 * @since 0.14.5
		 */
		private function init_coaches_map() {
			if ( null === $this->coaches_mapped ) {
				global $wpdb;

				$items = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT external_value, local_value, status
						FROM {$wpdb->prefix}anwpfl_import_mapping
						WHERE       provider = %s
						            AND type = 'coach'
						",
						$this->provider
					),
					OBJECT_K
				);

				if ( ! empty( $items ) && is_array( $items ) ) {
					$this->coaches_mapped = $items;
				} else {
					$this->coaches_mapped = [];
				}
			}
		}

		/**
		 * Get or Create coach if not exists.
		 *
		 * @param int    $coach_id
		 * @param string $coach_name
		 *
		 * @return mixed
		 * @since 0.14.5
		 */
		public function get_or_create_coach( int $coach_id, string $coach_name ) {

			if ( empty( $coach_id ) ) {
				return false;
			}

			$this->init_coaches_map();

			if ( isset( $this->coaches_mapped[ $coach_id ] ) && ! empty( $this->coaches_mapped[ $coach_id ]->local_value ) ) {
				return $this->coaches_mapped[ $coach_id ]->local_value;
			} else {
				// Try to create coach if not exists
				return $this->helper->update_coach(
					$coach_id,
					[
						'name' => $coach_name,
						'id'   => $coach_id,
					]
				);
			}
		}

		/**
		 * Get Wizard Available Leagues
		 *
		 * @return array|WP_Error
		 * @throws Exception
		 */
		public function api_get_wizard_leagues() {
			return $this->wizard->get_wizard_leagues();
		}
	}

endif;

return new AnWPFL_Premium_API_API_Football();
