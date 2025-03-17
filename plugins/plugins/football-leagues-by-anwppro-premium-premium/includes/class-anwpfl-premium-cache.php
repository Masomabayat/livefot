<?php
/**
 * AnWP Football Leagues Premium :: Cache
 *
 * @since   0.13.4
 */

class AnWPFL_Premium_Cache {

	/**
	 * Parent plugin class.
	 *
	 * @var    AnWP_Football_Leagues_Premium
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( AnWP_Football_Leagues_Premium $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Maybe flush site page cache
	 *
	 * @param $type        string - game/competition
	 * @param $context string - api_update_matches/run_scheduled_finished/run_scheduled_import_live/live_api_import_finished/run_scheduled_lineups
	 * @param $instance_id int
	 *
	 * @return void
	 */
	public function maybe_flush_cache( string $type, string $context = '', int $instance_id = 0 ) {

		$page_cache_support = AnWPFL_Premium_Options::get_value( 'page_cache_support' );

		if ( empty( $page_cache_support ) ) {
			return;
		}

		if ( apply_filters( 'anwpfl/cache/cancel_flush_page_cache', false, $type, $context, $instance_id ) ) {
			return;
		}

		if ( 'game' === $type ) {
			if ( 'full' === $page_cache_support ) {
				wp_cache_flush();
				$this->purge_cache_known_plugins();
			} else {
				$this->purge_post_cache_known_plugins( $instance_id );
			}
		}

		if ( 'competition' === $type ) {
			if ( 'full' === $page_cache_support ) {
				wp_cache_flush();
				$this->purge_cache_known_plugins();
			} else {
				$this->purge_post_cache_known_plugins( $instance_id );

				// Try to flush teams cache
				$teams = anwp_football_leagues()->competition->get_competition_clubs( $instance_id, 'all' );

				if ( ! empty( $teams ) && is_array( $teams ) ) {
					foreach ( $teams as $team_id ) {
						$this->purge_post_cache_known_plugins( $team_id );
					}
				}
			}
		}

		do_action( 'anwpfl/cache/after_flush_page_cache', $type, $context, $instance_id );
	}

	/**
	 * Purge all cache.
	 */
	public function purge_post_cache_known_plugins( $post_id ) {

		clean_post_cache( $post_id );

		// W3 Total Cache
		if ( function_exists( 'w3tc_flush_post' ) ) {
			w3tc_flush_post( $post_id, true );
		}

		if ( defined( 'LSCWP_V' ) ) {
			do_action( 'litespeed_purge_post', $post_id );
		}

		if ( function_exists( 'spinupwp_purge_post' ) ) {
			spinupwp_purge_post();
		}

		do_action( 'anwpfl/cache/try_flush_page_cache', $post_id );
	}

	/**
	 * Purge all cache.
	 */
	public function purge_cache_known_plugins() {

		// Autoptimize
		if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall_actionless' ) ) {
			autoptimizeCache::clearall_actionless();
		}

		// Fast Velocity Minify
		if ( function_exists( 'fvm_purge_all' ) ) {
			fvm_purge_all();
		}

		// WPRocket
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}

		// Swift Performance
		if ( class_exists( 'Swift_Performance_Cache' ) && method_exists( 'Swift_Performance_Cache', 'clear_all_cache' ) ) {
			Swift_Performance_Cache::clear_all_cache();
		}

		// RunCloud_Hub
		if ( class_exists( 'RunCloud_Hub' ) && method_exists( 'RunCloud_Hub', 'purge_cache_all' ) ) {
			RunCloud_Hub::purge_cache_all();
		}

		// WP Fastest Cache
		if ( class_exists( 'WpFastestCache' ) && method_exists( 'WpFastestCache', 'deleteCache' ) ) {
			$wpfc = new WpFastestCache();
			$wpfc->deleteCache();
		}

		// WP Super Cache
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
				wp_cache_clear_cache( $blog_id );
			} else {
				wp_cache_clear_cache();
			}
		}

		// W3 Total Cache
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}

		// Hyper Cache
		if ( class_exists( 'HyperCache' ) ) {
			$hypercache = new HyperCache();
			$hypercache->clean();
		}

		// Breeze
		if ( class_exists( 'Breeze_PurgeCache' ) && method_exists( 'Breeze_PurgeCache', 'breeze_cache_flush' ) ) {
			Breeze_PurgeCache::breeze_cache_flush();
		}

		// WP Optimize
		if ( function_exists( 'wpo_cache_flush' ) ) {
			wpo_cache_flush();
		}

		// WPEngine
		if ( class_exists( 'WpeCommon' ) && method_exists( 'WpeCommon', 'purge_memcached' ) ) {
			WpeCommon::purge_memcached();
			WpeCommon::purge_varnish_cache();
		}

		// SG Optimizer by Siteground
		if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache();
		}

		// LiteSpeed
		if ( class_exists( 'LiteSpeed_Cache_API' ) && method_exists( 'LiteSpeed_Cache_API', 'purge_all' ) ) {
			LiteSpeed_Cache_API::purge_all();
		}

		// LiteSpeed - New
		if ( defined( 'LSCWP_V' ) ) {
			do_action( 'litespeed_purge_all' );
		}

		// Super Page Cache for Cloudflare
		if ( class_exists( 'SW_CLOUDFLARE_PAGECACHE' ) ) {
			do_action( 'swcfpc_purge_cache' );
		}

		if ( function_exists( 'spinupwp_purge_site' ) ) {
			spinupwp_purge_site();
		}

//		if ( class_exists( '\CF\WordPress\Hooks' ) ) {
//			$cloudflare_hooks = new \CF\WordPress\Hooks();
//			$cloudflare_hooks->purgeCacheEverything();
//		}

		do_action( 'anwpfl/cache/try_flush_all_cache' );
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_filter( 'anwpfl/cache/expiration_map', [ $this, 'add_premium_keys_expiration' ], 10, 1 );

		// Cloudflare
		add_filter(
			'cloudflare_purge_everything_actions',
			function ( $actions ) {
				$actions[] = 'anwpfl/cache/try_flush_all_cache';

				return $actions;
			}
		);

		add_filter(
			'cloudflare_purge_url_actions',
			function ( $actions ) {
				$actions[] = 'anwpfl/cache/try_flush_page_cache';

				return $actions;
			}
		);
	}

	/**
	 * Add premium keys expiration
	 */
	public function add_premium_keys_expiration( $expiration_map ) {

		$pro_expiration_map = [
			'FL-PRO-CHARTS_get_stats_goals_15'            => WEEK_IN_SECONDS,
			'FL-PRO-CLUB_get_transfers'                   => WEEK_IN_SECONDS,
			'FL-PRO-PLAYER_get_player_stats_totals'       => WEEK_IN_SECONDS,
			'FL-PRO-PLAYER_get_players_stats_totals'      => WEEK_IN_SECONDS,
			'FL-PRO-SHORTCODE_missing-players'            => WEEK_IN_SECONDS,
			'FL-PRO-SHORTCODE_stat-players'               => WEEK_IN_SECONDS,
			'FL-PRO-SHORTCODE_stats-clubs'                => WEEK_IN_SECONDS,
			'FL-PRO-SHORTCODE_stats-players-custom'       => WEEK_IN_SECONDS,
			'FL-PRO-PLAYER_get_players_suspension_risk'   => WEEK_IN_SECONDS,
			'FL-PRO-REFEREE_get_referees_stats'           => WEEK_IN_SECONDS,
			'FL-PRO-CLUB_get_team_calendar_dates'         => WEEK_IN_SECONDS,
			'FL-PRO-GAMES_get_calendar_slider_game_dates' => DAY_IN_SECONDS,
		];

		return array_merge( $expiration_map, $pro_expiration_map );
	}
}
