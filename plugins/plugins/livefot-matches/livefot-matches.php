<?php
/**
 * Plugin Name: LiveFot Matches
 * Plugin URI: https://livefootballcenter.com
 * Description: Display live football matches and scores from LiveFot API .
 * Version: 1.3.1
 * Author: LiveFot
 * Author URI: https://livefootballcenter.com
 * Text Domain: livefot-matches
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('LIVEFOT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LIVEFOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LIVEFOT_VERSION', '1.3.1');

// Include required files
require_once LIVEFOT_PLUGIN_PATH . 'includes/class-livefot-api.php';
require_once LIVEFOT_PLUGIN_PATH . 'includes/class-livefot-ajax.php';

// Table creation during plugin activation
register_activation_hook(__FILE__, 'livefot_activate_plugin');

function livefot_activate_plugin() {
    livefot_create_tables(); 

	// Schedule the daily cron job if enabled
    $enable_cron = get_option('livefot_enable_cron_job', 1); // Default to enabled
    if ($enable_cron && !wp_next_scheduled('livefot_daily_fetch_fixtures')) {
        livefot_schedule_cron_job();
    }
	
	  // Schedule the short-range cron job if enabled
    $enable_short_cron = get_option('livefot_enable_short_cron_job', 1); // Default to enabled
    if ($enable_short_cron && !wp_next_scheduled('livefot_short_fetch_fixtures')) {
        livefot_schedule_short_cron_job();
    }
}

// Plugin deactivation: unschedule cron jobs and cleanup
register_deactivation_hook(__FILE__, 'livefot_deactivate_plugin');

function livefot_deactivate_plugin() {
    // Unschedule the daily cron job
    wp_clear_scheduled_hook('livefot_daily_fetch_fixtures');

    // Unschedule the short-range cron job
    wp_clear_scheduled_hook('livefot_short_fetch_fixtures');

    // Perform any additional cleanup if necessary
    // Note: Uninstall hook handles database cleanup
}

// Plugin uninstall: cleanup
register_uninstall_hook(__FILE__, 'livefot_cleanup_on_uninstall');

function livefot_cleanup_on_uninstall() {
    global $wpdb;
    $tables = [
        'livefot_fixtures',
        'livefot_leagues',
        'livefot_countries',
        'livefot_scores',
        'livefot_time',
        'livefot_aggregate',
        'livefot_referees',
        'livefot_venues',
        'livefot_lineups',
        'livefot_players',
        'livefot_events',
        'livefot_teams',
        'livefot_formations',
		'livefot_standings',
		'livefot_team_stats',
        'livefot_api_calls'
    ];
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$table");
    }

    // Delete plugin options
    delete_option('livefot_api_key');
    delete_option('livefot_api_url'); 
    delete_option('livefot_enable_cron_job');
    delete_option('livefot_cron_hour');
    delete_option('livefot_cron_minute');
    delete_option('livefot_manual_date');
    delete_option('livefot_interval_matches');
    delete_option('livefot_interval_lineups');
    delete_option('livefot_interval_events');
    delete_option('livefot_interval_stats');
	delete_option('livefot_interval_standings');

    // Delete short-range cron job options
    delete_option('livefot_enable_short_cron_job');
  //  delete_option('livefot_short_cron_start_hour');
  //  delete_option('livefot_short_cron_start_minute');
    delete_option('livefot_cron_interval_short');
	delete_option('livefot_cron_last_run_status');
	delete_option('livefot_cron_last_run_start');
	delete_option('livefot_cron_last_run_end');
    delete_option('livefot_cron_last_run_status_short');
    delete_option('livefot_cron_last_run_start_short');
    delete_option('livefot_cron_last_run_end_short');
}

// Hook the scheduled event to a callback function
add_action('livefot_daily_fetch_fixtures', 'livefot_fetch_and_insert_fixtures');

  function livefot_fetch_and_insert_fixtures() {
    // Get current UTC time for start
    $utc_start_time = gmdate('Y-m-d H:i:s'); // UTC time
    update_option('livefot_cron_last_run_start', $utc_start_time);

    // Initialize status as running
    update_option('livefot_cron_last_run_status', 'Running');

    $api = new LiveFot_API();
    $result = $api->livefot_fetch_and_insert_fixtures(); // Your custom logic

    // Get current UTC time for end
    $utc_end_time = gmdate('Y-m-d H:i:s'); // UTC time
    update_option('livefot_cron_last_run_end', $utc_end_time);

    // Update status based on result
    if ($result === true) {
        update_option('livefot_cron_last_run_status', 'Success');
    } else {
        update_option('livefot_cron_last_run_status', 'Failed: ' . $result);
    }

    // Log start and end times for debugging
    error_log("Daily cron job started at: $utc_start_time UTC");
    error_log("Daily cron job ended at: $utc_end_time UTC");
}


// Register short-range cron event
add_action('livefot_short_fetch_fixtures', 'livefot_handle_short_fetch_fixtures');

function livefot_handle_short_fetch_fixtures() {
    try {
        // Mark start time and status for SHORT cron only
        $utc_start_time_short = gmdate('Y-m-d H:i:s');
        update_option('livefot_cron_last_run_status_short', 'Running');
        update_option('livefot_cron_last_run_start_short', $utc_start_time_short);

        // Perform the fetch logic
        $api = new LiveFot_API();
        $result = $api->livefot_handle_short_fetch_fixtures();

        // Update status based on result
        if ($result === true) {
            update_option('livefot_cron_last_run_status_short', 'Success');
        } else {
            update_option('livefot_cron_last_run_status_short', 'Failed: ' . $result);
        }
    } catch (Exception $e) {
        // Catch any unexpected exceptions
        update_option('livefot_cron_last_run_status_short', 'Failed: ' . $e->getMessage());
    } finally {
        // Mark end time
        $utc_end_time_short = gmdate('Y-m-d H:i:s');
        update_option('livefot_cron_last_run_end_short', $utc_end_time_short);

        // Log execution times
        error_log("Short-range cron started at (UTC): {$utc_start_time_short}");
        error_log("Short-range cron ended at (UTC):   {$utc_end_time_short}");
    }
}


add_filter('cron_schedules', 'livefot_add_cron_intervals');
function livefot_add_cron_intervals($schedules) {
    // Retrieve the interval from options, default to 15 minutes
    $interval_minutes = intval(get_option('livefot_cron_interval_short', 15));
    if ($interval_minutes < 15) {
        $interval_minutes = 15; // Enforce minimum 15 minutes
        update_option('livefot_cron_interval_short', $interval_minutes);
        error_log("Short-range cron interval too low. Set to minimum 15 minutes.");
    }

    // Add the custom interval to the schedules array
    $schedules['livefot_short_interval'] = array(
        'interval' => $interval_minutes * MINUTE_IN_SECONDS,
        'display'  => sprintf(__('Every %d minutes', 'livefot'), $interval_minutes)
    );
    return $schedules;
}


// Table creation function
function livefot_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Initialize an array to hold SQL statements
    $sql = [];

    // Lineup table
    $lineup_table = $wpdb->prefix . 'livefot_lineups';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $lineup_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            match_id BIGINT(20) NOT NULL,
            team_id BIGINT(20) NOT NULL,
            player_id BIGINT(20) NOT NULL,
            number INT NOT NULL,
            position VARCHAR(10) NOT NULL,
            formation_position INT DEFAULT NULL,
            captain BOOLEAN NOT NULL DEFAULT FALSE,
            type VARCHAR(20) NOT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			UNIQUE KEY unique_match_team_player (match_id, team_id, player_id)
        ) $charset_collate;
    ";

    // Leagues table
    $leagues_table = $wpdb->prefix . 'livefot_leagues';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $leagues_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            league_id BIGINT(20) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            is_cup BOOLEAN NOT NULL DEFAULT FALSE,
            logo_path VARCHAR(2083) DEFAULT NULL,
            current_season_id BIGINT(20) DEFAULT NULL,
            stage_name VARCHAR(100) DEFAULT NULL,
            country_id BIGINT(20) NOT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    // Countries table
    $countries_table = $wpdb->prefix . 'livefot_countries';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $countries_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            country_id BIGINT(20) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            image_path VARCHAR(2083) DEFAULT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    // Fixtures table
    $fixtures_table = $wpdb->prefix . 'livefot_fixtures';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $fixtures_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fixture_id BIGINT(20) NOT NULL UNIQUE,
            season_id BIGINT(20) NOT NULL,
            league_id BIGINT(20) NOT NULL,
            group_id BIGINT(20) DEFAULT NULL,
            aggregate_id BIGINT(20) DEFAULT NULL,
            localteam_id BIGINT(20) NOT NULL,
            localteam_name VARCHAR(255) NOT NULL,
            visitorteam_id BIGINT(20) NOT NULL,
            visitorteam_name VARCHAR(255) NOT NULL,
            stage_type VARCHAR(50) DEFAULT NULL,
            localteam_coach_id BIGINT(20) DEFAULT NULL,
            visitorteam_coach_id BIGINT(20) DEFAULT NULL,
            winner_team_id BIGINT(20) DEFAULT NULL,
            commentaries BOOLEAN DEFAULT FALSE,
            leg VARCHAR(10) DEFAULT NULL,
            is_placeholder BOOLEAN DEFAULT FALSE,
            referee_id BIGINT(20) DEFAULT NULL,
            venue_id BIGINT(20) DEFAULT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    // Referees table
    $referees_table = $wpdb->prefix . 'livefot_referees';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $referees_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            referee_id BIGINT(20) NOT NULL UNIQUE,
            common_name VARCHAR(255) NOT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    // Venues table
    $venues_table = $wpdb->prefix . 'livefot_venues';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $venues_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            venue_id BIGINT(20) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    // Scores table
    $scores_table = $wpdb->prefix . 'livefot_scores';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $scores_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fixture_id BIGINT(20) NOT NULL UNIQUE,
            localteam_score INT DEFAULT NULL,
            visitorteam_score INT DEFAULT NULL,
            localteam_pen_score INT DEFAULT NULL,
            visitorteam_pen_score INT DEFAULT NULL,
            ht_score VARCHAR(10) DEFAULT NULL,
            ft_score VARCHAR(10) DEFAULT NULL,
            et_score VARCHAR(10) DEFAULT NULL,
            ps_score VARCHAR(10) DEFAULT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    // Time table
    $time_table = $wpdb->prefix . 'livefot_time';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $time_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fixture_id BIGINT(20) NOT NULL UNIQUE,
            status VARCHAR(50) DEFAULT NULL,
            starting_at_datetime DATETIME DEFAULT NULL,
            starting_at_date DATE DEFAULT NULL,
            starting_at_time TIME DEFAULT NULL,
            starting_at_timestamp BIGINT(20) DEFAULT NULL,
            starting_at_timezone VARCHAR(50) DEFAULT NULL,
            minute INT DEFAULT NULL,
            second INT DEFAULT NULL,
            added_time INT DEFAULT NULL,
            extra_minute INT DEFAULT NULL,
            injury_time INT DEFAULT NULL,
            match_period VARCHAR(50) DEFAULT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    // Aggregate table
    $aggregate_table = $wpdb->prefix . 'livefot_aggregate';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $aggregate_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            aggregate_id BIGINT(20) NOT NULL UNIQUE,
            league_id BIGINT(20) NOT NULL,
            season_id BIGINT(20) NOT NULL,
            stage_id BIGINT(20) DEFAULT NULL,
            localteam VARCHAR(255) DEFAULT NULL,
            localteam_id BIGINT(20) DEFAULT NULL,
            visitorteam VARCHAR(255) DEFAULT NULL,
            visitorteam_id BIGINT(20) DEFAULT NULL,
            result VARCHAR(50) DEFAULT NULL,
            winner BIGINT(20) DEFAULT NULL,
            detail VARCHAR(255) DEFAULT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    // Players table
    $players_table = $wpdb->prefix . 'livefot_players';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $players_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            player_id BIGINT(20) NOT NULL UNIQUE,
            player_name VARCHAR(255) NOT NULL,
            logo_path VARCHAR(2083) DEFAULT NULL
        ) $charset_collate;
    ";

    // Events table
    $events_table = $wpdb->prefix . 'livefot_events';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $events_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_id BIGINT(20) NOT NULL UNIQUE,
            match_id BIGINT(20) NOT NULL,
            team_id BIGINT(20) NOT NULL,
            player_id BIGINT(20) DEFAULT NULL,
            related_player_id BIGINT(20) DEFAULT NULL,
            type VARCHAR(50) NOT NULL,
            minute INT DEFAULT NULL,
            extra_minute INT DEFAULT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    // Teams table
    $teams_table = $wpdb->prefix . 'livefot_teams';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $teams_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            team_id BIGINT(20) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            shortcode VARCHAR(50) DEFAULT NULL,
            twitter VARCHAR(255) DEFAULT NULL,
            country_id BIGINT(20) DEFAULT NULL,
            national_team BOOLEAN DEFAULT FALSE,
            founded INT DEFAULT NULL,
            logo_path VARCHAR(2083) DEFAULT NULL,
            venue_id BIGINT(20) DEFAULT NULL,
            current_season_id BIGINT(20) DEFAULT NULL,
            gender VARCHAR(10) DEFAULT NULL,
            team_type VARCHAR(50) DEFAULT NULL
        ) $charset_collate;
    ";

    // Formations table
    $formations_table = $wpdb->prefix . 'livefot_formations';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $formations_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            match_id BIGINT(20) NOT NULL UNIQUE,
            local_team_id BIGINT(20) NOT NULL,
            local_team_formation VARCHAR(50) DEFAULT NULL,
            visitor_team_id BIGINT(20) NOT NULL,
            visitor_team_formation VARCHAR(50) DEFAULT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";
	
	
	
	// New Standings Table
$standings_table = $wpdb->prefix . 'livefot_standings';
$sql[] = "
    CREATE TABLE IF NOT EXISTS $standings_table (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        standing_id BIGINT(20) NOT NULL UNIQUE,
        league_id BIGINT(20) NOT NULL,
        group_id BIGINT(20) DEFAULT NULL,
        season_id BIGINT(20) NOT NULL,
        stage_id BIGINT(20) NOT NULL,
        round_id BIGINT(20) NOT NULL,
        position INT NOT NULL,
        team_id BIGINT(20) NOT NULL,
        team_name VARCHAR(255) NOT NULL,
        shortcode VARCHAR(50) DEFAULT NULL,
        team_logo VARCHAR(2083) DEFAULT NULL,
        goals VARCHAR(20) DEFAULT NULL,
        goal_diff INT DEFAULT NULL,
        wins INT DEFAULT NULL,
        lost INT DEFAULT NULL,
        draws INT DEFAULT NULL,
        played INT DEFAULT NULL,
        points INT DEFAULT NULL,
        description VARCHAR(255) DEFAULT NULL,
        recent_form VARCHAR(255) DEFAULT NULL,
        standing_rule_id BIGINT(20) DEFAULT NULL,
        result VARCHAR(100) DEFAULT NULL,
        fairplay_points_lose INT DEFAULT NULL,
        updated_at TIMESTAMP NULL DEFAULT NULL,
        inserted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) $charset_collate;
";
	
	$team_stats_table = $wpdb->prefix . 'livefot_team_stats';

$sql[] = "
    CREATE TABLE IF NOT EXISTS $team_stats_table (
        stats_id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fixture_id BIGINT(20) UNSIGNED NOT NULL,
        team_id BIGINT(20) UNSIGNED NOT NULL,
        fouls INT DEFAULT NULL,
        corners INT DEFAULT NULL,
        offsides INT DEFAULT NULL,
        possession_time INT DEFAULT NULL,
        yellow_cards INT DEFAULT NULL,
        red_cards INT DEFAULT NULL,
        yellow_red_cards INT DEFAULT NULL,
        saves INT DEFAULT NULL,
        substitutions INT DEFAULT NULL,
        goal_kick INT DEFAULT NULL,
        goal_attempts INT DEFAULT NULL,
        free_kick INT DEFAULT NULL,
        throw_in INT DEFAULT NULL,
        ball_safe INT DEFAULT NULL,
        goals INT DEFAULT NULL,
        penalties INT DEFAULT NULL,
        injuries INT DEFAULT NULL,
        tackles INT DEFAULT NULL,
        attacks INT DEFAULT NULL,
        dangerous_attacks INT DEFAULT NULL,
        passes_total INT DEFAULT NULL,
        passes_accurate INT DEFAULT NULL,
        passes_percentage INT DEFAULT NULL,
        shots_total INT DEFAULT NULL,
        shots_ongoal INT DEFAULT NULL,
        shots_blocked INT DEFAULT NULL,
        shots_offgoal INT DEFAULT NULL,
        shots_insidebox INT DEFAULT NULL,
        shots_outsidebox INT DEFAULT NULL,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_fixture_team (fixture_id, team_id)
    ) $charset_collate;
";



    // API call tracking table
    $api_calls_table = $wpdb->prefix . 'livefot_api_calls';
    $sql[] = "
        CREATE TABLE IF NOT EXISTS $api_calls_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            endpoint_name VARCHAR(255) NOT NULL UNIQUE,
            remaining_calls INT DEFAULT NULL,
            status VARCHAR(50) NOT NULL,
            last_call_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($sql as $query) {
        dbDelta($query);
    }
}

// Initialize the plugin
add_action('init', 'livefot_init');
function livefot_init() {
    // Register scripts and styles
    add_action('wp_enqueue_scripts', 'livefot_enqueue_scripts');
	
	 // Admin scripts and styles
  //  add_action('admin_enqueue_scripts', 'livefot_enqueue_admin_scripts');
    
    
    // Register shortcode
    add_shortcode('livefot_matches', 'livefot_matches_shortcode');
}

// Enqueue scripts and styles
function livefot_enqueue_scripts() {

// Enqueue Flatpickr CSS from CDN
    wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), '4.6.13');

    // Enqueue Flatpickr JS from CDN
    wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), '4.6.13', true);

	
    wp_enqueue_style(
        'livefot-styles',
        LIVEFOT_PLUGIN_URL . 'assets/css/styles.css',
        array(),
        LIVEFOT_VERSION
    );
	
    wp_enqueue_script(
        'livefot-matches',
        LIVEFOT_PLUGIN_URL . 'assets/js/matches.js',
        array('jquery'),
        LIVEFOT_VERSION,
        true
    );

    // Retrieve user-defined intervals from options (default values if not set)
    $matches_interval_ms = (int) get_option('livefot_interval_matches', 30) * 1000;   // default 30s
    $lineups_interval_ms = (int) get_option('livefot_interval_lineups', 60) * 1000;   // default 60s
    $events_interval_ms = (int) get_option('livefot_interval_events', 60) * 1000;     // default 60s
    $stats_interval_ms = (int) get_option('livefot_interval_stats', 120) * 1000;      // default 120s
	$standings_interval_ms = (int) get_option('livefot_interval_standings', 120) * 1000;      // default 120s

    wp_localize_script('livefot-matches', 'livefotAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('livefot_nonce'),
        'intervals' => array(
            'matches' => $matches_interval_ms,
            'lineups' => $lineups_interval_ms,
            'events' => $events_interval_ms,
			'standings' => $standings_interval_ms,
            'statistics' => $stats_interval_ms
        ),
		'icons_base_url' => plugins_url('assets/images/', __FILE__),
    ));
}



// Shortcode callback
function livefot_matches_shortcode($atts) {
	
    ob_start();
    include LIVEFOT_PLUGIN_PATH . 'templates/matches-container.php';
    return ob_get_clean();
}

// Add admin menu and settings pages
add_action('admin_menu', 'livefot_add_admin_menu');

function livefot_add_admin_menu() {
    // Add main plugin menu (LiveFot)
    add_menu_page(
        'LiveFot',                // Page title
        'LiveFot',                // Menu title
        'manage_options',         // Capability
        'livefot-main',           // Menu slug
        'livefot_welcome_page',   // Callback function for the main/welcome page
        'dashicons-admin-generic',// Icon
        20                        // Position
    );

    // Add submenu for Settings (API Key) under LiveFot
    add_submenu_page(
        'livefot-main',           // Parent slug
        'Settings',               // Page title
        'Settings',               // Menu title
        'manage_options',         // Capability
        'livefot-settings',       // Menu slug
        'livefot_settings_page'   // Callback function for the settings page
    );

    // Add submenus for Data Tables
    add_submenu_page(
        'livefot-main',
        'Lineup Data',
        'Lineup Data',
        'manage_options',
        'livefot-lineup-data',
        'livefot_display_lineup_data'
    );

    add_submenu_page(
        'livefot-main',
        'Leagues Data',
        'Leagues Data',
        'manage_options',
        'livefot-leagues-data',
        'livefot_display_leagues_data'
    );

    add_submenu_page(
        'livefot-main',
        'Countries Data',
        'Countries Data',
        'manage_options',
        'livefot-countries-data',
        'livefot_display_countries_data'
    );

    add_submenu_page(
        'livefot-main',
        'Fixtures Data',
        'Fixtures Data',
        'manage_options',
        'livefot-fixtures-data',
        'livefot_display_fixtures_data'
    );

    add_submenu_page(
        'livefot-main',
        'Referees Data',
        'Referees Data',
        'manage_options',
        'livefot-referees-data',
        'livefot_display_referees_data'
    );

    add_submenu_page(
        'livefot-main',
        'Venues Data',
        'Venues Data',
        'manage_options',
        'livefot-venues-data',
        'livefot_display_venues_data'
    );

    add_submenu_page(
        'livefot-main',
        'Scores Data',
        'Scores Data',
        'manage_options',
        'livefot-scores-data',
        'livefot_display_scores_data'
    );

    add_submenu_page(
        'livefot-main',
        'Time Data',
        'Time Data',
        'manage_options',
        'livefot-time-data',
        'livefot_display_time_data'
    );

    add_submenu_page(
        'livefot-main',
        'Aggregate Data',
        'Aggregate Data',
        'manage_options',
        'livefot-aggregate-data',
        'livefot_display_aggregate_data'
    );

    add_submenu_page(
        'livefot-main',
        'Players Data',
        'Players Data',
        'manage_options',
        'livefot-players-data',
        'livefot_display_players_data'
    );

    add_submenu_page(
        'livefot-main',
        'Events Data',
        'Events Data',
        'manage_options',
        'livefot-events-data',
        'livefot_display_events_data'
    );

    add_submenu_page(
        'livefot-main',
        'Teams Data',
        'Teams Data',
        'manage_options',
        'livefot-teams-data',
        'livefot_display_teams_data'
    );

    add_submenu_page(
        'livefot-main',
        'Formations Data',
        'Formations Data',
        'manage_options',
        'livefot-formations-data',
        'livefot_display_formations_data'
    );
	
	// New Standings Data Submenu
	add_submenu_page(
    	'livefot-main',
    	'Standings Data',
    	'Standings Data',
    	'manage_options',
    	'livefot-standings-data',
    	'livefot_display_standings_data'
	);


	
	
	
	// New Standings Data Submenu
	add_submenu_page(
    	'livefot-main',
    	'Stats Data',
    	'Stats Data',
    	'manage_options',
    	'livefot-team-stats-data',
    	'livefot_display_team_stats_data'
	);
	
	
    add_submenu_page(
        'livefot-main',
        'API Calls',
        'API Calls',
        'manage_options',
        'livefot-api-calls',
        'livefot_display_api_calls'
    );
}

// Welcome page callback (main page)

function livefot_welcome_page() {

    wp_enqueue_style(
        'livefot-styles',
        LIVEFOT_PLUGIN_URL . 'assets/css/admin/styles.css',
        array(),
        LIVEFOT_VERSION
    );

    ?>
    <div class="wrap livefot-welcome-page">
        <div class="header-title">
          <h1>Welcome to <span>LiveFot</span></h1>
          <p class="about-description">
                    Your ultimate solution for automatic live football scores, match lineups, standings, stats, and live events.
                </p>
        </div>
        <div class="livefot-welcome-header">
            <div class="livefot-welcome-text">
<!--                 <p class="about-description">
                    Your ultimate solution for automatic live football scores, match lineups, standings, stats, and live events.
                </p> -->
            </div>
        </div>

        <div class="livefot-features-grid">
            <div class="feature-section">
                <h2 class="live-section-heading">🚀 Key Features</h2>
                <div class="feature-grid">
                    <div class="feature-item">
                        <h3>Automatic Updates</h3>
                        <p>Daily updates covering -5 to +7 days with hourly synchronization for matches within -1 to +1 day. Manual fetch option available for specific dates.</p>
                    </div>
                    
                    <div class="feature-item">
                        <h3>User-Friendly</h3>
                        <p>Matches automatically display in your timezone - no manual date configuration needed!</p>
                    </div>
                    
                    <div class="feature-item">
                        <h3>Cross-Platform</h3>
                        <p>Experience live updates on web or through our mobile apps on Google Play Store and Apple App Store.</p>
                    </div>
                    
                    <div class="feature-item">
                        <h3>Real-Time Data</h3>
                        <p>Get instant access to live scores, lineups, standings, and match events as they happen.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="livefot-features-grid livefot-getting-started-wrapper">
            <div class="livefot-getting-started">
                <h2>🎯 Getting Started</h2>
                <p>To begin displaying live football data on your website:</p>
                <ol>
                    <li>Configure your API key in the Settings page</li>
                    <li>Use the shortcode <code>[livefot_matches]</code> in any post or page</li>
                    <li>Customize the display options as needed</li>
                </ol>
            </div>
        </div>
        <div class="livefot-features-grid">

            <div class="livefot-versions">
                <h2 class="live-section-heading">📦 Available Versions</h2>
                <div class="version-grid">
                    <div class="version-item">
                        <h3>Basic Version</h3>
                        <p>Fully functional with essential features for live scores and updates.</p>
                        <ul>
                            <li>Live Scores</li>
                            <li>Match Updates</li>
                            <li>Basic Statistics</li>
                            <li>Team Lineups</li>
                        </ul>
                    </div>
                    
                    <div class="version-item">
                        <h3>Advanced Version (Coming Soon)</h3>
                        <p>Enhanced features for a superior experience.</p>
                        <ul>
                            <li>Everything in Basic Version</li>
                            <li>Advanced Analytics</li>
                            <li>Custom Widgets</li>
                            <li>Priority Support</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="livefot-requirements">
                <h2 class="live-section-heading">⚙️ Technical Requirements</h2>
                <div class="requirements-grid">
                    <div class="requirement-item">
                        <h3>Platform</h3>
                        <p>Optimized for WordPress</p>
                    </div>
                    
                    <div class="requirement-item">
                        <h3>Hosting</h3>
                        <p>Works on both dedicated and shared WordPress hosting</p>
                    </div>
                    
                    <div class="requirement-item">
                        <h3>PHP Version</h3>
                        <p>PHP 7.4 or higher recommended</p>
                    </div>
                </div>
            </div>

            <div class="livefot-support">
                <h2 class="live-section-heading">📞 Support & Contact</h2>
                <p>For leagues, subscriptions, or support:</p>
                <ul>
                    <li>Email: <a href="mailto:info@livefootballcenter.com">info@livefootballcenter.com</a></li>
                    <li>Documentation: <a href="https://livefootballcenter.com/docs" target="_blank">View Documentation</a></li>
                </ul>
            </div>

            
        </div>
    </div>
    <?php
}


// Register settings for API key
add_action('admin_init', 'livefot_register_settings');
function livefot_register_settings() {
    register_setting('livefot_options', 'livefot_api_key'); // Register API key setting
    
    // Register the API URL (New)
    register_setting('livefot_options', 'livefot_api_url');
}

// Register cron job enable/disable setting
add_action('admin_init', 'livefot_register_cron_job_setting');

function livefot_register_cron_job_setting() {
    register_setting('livefot_options', 'livefot_enable_cron_job', array(
        'type'              => 'boolean',
        'description'       => 'Enable or disable the daily fixtures fetch cron job.',
        'default'           => true,
        'sanitize_callback' => 'absint', // Ensures the value is an integer (0 or 1)
    ));
}

// Register settings for short-range cron job
add_action('admin_init', 'livefot_register_short_cron_job_settings');

function livefot_register_short_cron_job_settings() {
    register_setting('livefot_options', 'livefot_enable_short_cron_job', array(
        'type'              => 'boolean',
        'description'       => 'Enable or disable the short-range fixtures fetch cron job.',
        'default'           => true,
        'sanitize_callback' => 'absint',
    ));
}

// Register cron job time settings
add_action('admin_init', 'livefot_register_cron_time_settings');

function livefot_register_cron_time_settings() {
    register_setting('livefot_options', 'livefot_cron_hour', array(
        'type'              => 'integer',
        'description'       => 'Hour of the day to run the daily cron job (0-23).',
        'default'           => 8,
        'sanitize_callback' => 'absint',
    ));

    register_setting('livefot_options', 'livefot_cron_minute', array(
        'type'              => 'integer',
        'description'       => 'Minute of the hour to run the daily cron job (0-59).',
        'default'           => 0,
        'sanitize_callback' => 'absint',
    ));
}

// Register manual run date setting
add_action('admin_init', 'livefot_register_manual_run_date_setting');

function livefot_register_manual_run_date_setting() {
    register_setting('livefot_options', 'livefot_manual_date', array(
        'type'              => 'string',
        'description'       => 'Date for manual fixtures fetch.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
}

// Register settings for intervals
add_action('admin_init', 'livefot_register_update_interval_settings');
function livefot_register_update_interval_settings() {
    register_setting('livefot_options', 'livefot_interval_matches', array('default' => 30));
    register_setting('livefot_options', 'livefot_interval_lineups', array('default' => 60));
    register_setting('livefot_options', 'livefot_interval_events', array('default' => 60));
    register_setting('livefot_options', 'livefot_interval_stats', array('default' => 120));
    register_setting('livefot_options', 'livefot_interval_standings', array('default' => 120));
	
    // Register short-range cron interval setting
    register_setting('livefot_options', 'livefot_cron_interval_short', array(
        'type'              => 'integer',
        'description'       => 'Interval in minutes for short-range fixtures fetch cron job.',
        'default'           => 15,
        'sanitize_callback' => 'absint',
    ));
}

// Handle cron job scheduling based on settings
add_action('update_option_livefot_enable_cron_job', 'livefot_handle_cron_job_setting', 10, 2);
add_action('update_option_livefot_cron_hour', 'livefot_handle_cron_job_time_setting', 10, 2);
add_action('update_option_livefot_cron_minute', 'livefot_handle_cron_job_time_setting', 10, 2);

// Handle short-range cron job scheduling based on settings
add_action('update_option_livefot_enable_short_cron_job', 'livefot_handle_short_cron_job_setting', 10, 2);
add_action('update_option_livefot_short_cron_start_hour', 'livefot_handle_short_cron_job_setting', 10, 2);
add_action('update_option_livefot_short_cron_start_minute', 'livefot_handle_short_cron_job_setting', 10, 2);
add_action('update_option_livefot_cron_interval_short', 'livefot_handle_short_cron_interval_update', 10, 2);

function livefot_handle_cron_job_setting($old_value, $new_value) {
    if ($new_value && !$old_value) {
        // Enable cron job
        if (!wp_next_scheduled('livefot_daily_fetch_fixtures')) {
            livefot_schedule_cron_job();
        }
    } elseif (!$new_value && $old_value) {
        // Disable cron job
        wp_clear_scheduled_hook('livefot_daily_fetch_fixtures');
    }
}

function livefot_handle_cron_job_time_setting($old_value, $new_value) {
    // Reschedule the daily cron job based on the new time
    if (get_option('livefot_enable_cron_job')) {
        wp_clear_scheduled_hook('livefot_daily_fetch_fixtures');
        livefot_schedule_cron_job();
    }
}

function livefot_handle_short_cron_job_setting($old_value, $new_value) {
    // Reschedule the short-range cron job based on the new settings
    if ($new_value) {
        // Enabled
        livefot_schedule_short_cron_job();
    } else {
        // Disabled
        wp_clear_scheduled_hook('livefot_short_fetch_fixtures');
    }
}

function livefot_handle_short_cron_interval_update($old_value, $new_value) {
    // Reschedule the short-range cron job based on the new interval
    wp_clear_scheduled_hook('livefot_short_fetch_fixtures');
    livefot_schedule_short_cron_job();
}

// Updated livefot_schedule_cron_job function for daily fixtures
function livefot_schedule_cron_job() {
    // Retrieve user-defined cron time settings
    $hour = intval(get_option('livefot_cron_hour', 8));
    $minute = intval(get_option('livefot_cron_minute', 0));

    // Get WordPress local time
    $current_time_local = current_time('timestamp');

    // Calculate the scheduled time based on WordPress's local time
    $scheduled_time_local = mktime($hour, $minute, 0, date('n', $current_time_local), date('j', $current_time_local), date('Y', $current_time_local));

    // Convert scheduled_time_local to UTC
    $scheduled_time_utc = get_gmt_from_date(date('Y-m-d H:i:s', $scheduled_time_local), 'U');

    // Get current UTC time
    $current_time_utc = current_time('timestamp', true); // 'true' gets GMT timestamp

    // If the scheduled time has already passed today, schedule for tomorrow
    if ($scheduled_time_utc <= $current_time_utc) {
        $scheduled_time_local += DAY_IN_SECONDS; // Add 24 hours
        $scheduled_time_utc = get_gmt_from_date(date('Y-m-d H:i:s', $scheduled_time_local), 'U');
    }

    // Schedule the cron event if not already scheduled
    if (!wp_next_scheduled('livefot_daily_fetch_fixtures')) {
        wp_schedule_event($scheduled_time_utc, 'daily', 'livefot_daily_fetch_fixtures');
    }

    // Log the scheduled time for debugging
    error_log("Scheduled daily cron job for WordPress local time: " . date('Y-m-d H:i:s', $scheduled_time_local) . " (UTC: " . date('Y-m-d H:i:s', $scheduled_time_utc) . ")");
}




function livefot_schedule_short_cron_job() {
    $enable_short_cron = get_option('livefot_enable_short_cron_job', 1);
    if (!$enable_short_cron) {
        error_log("Short-range cron job is disabled.");
        return; // Short-range cron job is disabled
    }

    // Clear any existing schedules to avoid duplicates
    wp_clear_scheduled_hook('livefot_short_fetch_fixtures');

    // Get interval in minutes (default to 15 if not set)
    $interval_minutes = intval(get_option('livefot_cron_interval_short', 15));
    if ($interval_minutes < 15) { // Enforce minimum 15 minutes
        $interval_minutes = 15;
        update_option('livefot_cron_interval_short', $interval_minutes);
        error_log("Short-range cron interval too low. Set to minimum 15 minutes.");
    }

    // Schedule the recurring cron event if it's not already scheduled
    if (!wp_next_scheduled('livefot_short_fetch_fixtures')) {
        wp_schedule_event(time(), 'livefot_short_interval', 'livefot_short_fetch_fixtures');
        error_log("Scheduled short-range cron job to run every $interval_minutes minutes.");
    }
}



// Settings page callback
function livefot_settings_page() {
    // Check if manual fetch form was submitted
    if (isset($_POST['fetch_fixtures']) && check_admin_referer('livefot_manual_fetch', '_wpnonce_livefot_manual_fetch')) {
        // Sanitize the input date
        $manual_date = sanitize_text_field($_POST['livefot_manual_date']);
        
        if (empty($manual_date)) {
            add_settings_error(
                'livefot_messages',
                'livefot_manual_date_error',
                __('Please select a valid date.', 'livefot'),
                'error'
            );
        } else {
            // Create a new instance of LiveFot_API
            $api = new LiveFot_API();
            $result = $api->fetch_and_store_fixtures_manual($manual_date);

            if ($result === true) {
                add_settings_error(
                    'livefot_messages',
                    'livefot_fetch_success',
                    sprintf(__('Fixtures successfully fetched for %s.', 'livefot'), esc_html($manual_date)),
                    'success'
                );
            } else {
                add_settings_error(
                    'livefot_messages',
                    'livefot_fetch_error',
                    sprintf(__('Error fetching fixtures: %s', 'livefot'), esc_html($result)),
                    'error'
                );
            }
        }
    }

    // Display any error/success messages
    settings_errors('livefot_messages');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('LiveFot Settings', 'livefot'); ?></h1>
        
        <!-- Main Settings Form -->
        <form method="post" action="options.php">
            <?php
            // Output security fields for the registered setting "livefot_options"
            settings_fields('livefot_options');
            // Output setting sections and their fields
            do_settings_sections('livefot_options');
            ?>

            <!-- API Settings Section -->
            <h2><?php esc_html_e('API Settings', 'livefot'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="livefot_api_key"><?php esc_html_e('API Key', 'livefot'); ?></label></th>
                    <td>
                        <input type="text" id="livefot_api_key" name="livefot_api_key" value="<?php echo esc_attr(get_option('livefot_api_key')); ?>" class="regular-text" required>
                        <p class="description"><?php esc_html_e('Enter your API key to access LiveFot services.', 'livefot'); ?></p>
                    </td>
                </tr>
                 <tr>
                    <th scope="row">
                        <label for="livefot_api_url"><?php esc_html_e('API URL', 'livefot'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="livefot_api_url" name="livefot_api_url" 
                               value="<?php echo esc_attr(get_option('livefot_api_url')); ?>" 
                               class="regular-text" required>
                        <p class="description"><?php esc_html_e('Enter your custom API base URL if different from default.', 'livefot'); ?></p>
                    </td>
                </tr>
            </table>

            <!-- Cron Job Settings Section -->
            <h2><?php esc_html_e('Cron Job Settings', 'livefot'); ?></h2>
            <table class="form-table">
                <!-- Daily Cron Job Enable/Disable -->
                <tr>
                    <th scope="row"><label for="livefot_enable_cron_job"><?php esc_html_e('Enable Daily Fixtures Cron Job', 'livefot'); ?></label></th>
                    <td>
                        <input type="checkbox" id="livefot_enable_cron_job" name="livefot_enable_cron_job" value="1" <?php checked(1, get_option('livefot_enable_cron_job', 1)); ?>>
                        <p class="description"><?php esc_html_e('Enable the daily cron job to fetch fixtures and update the database.', 'livefot'); ?></p>
                    </td>
                </tr>
                <!-- Daily Cron Job Time -->
                <tr>
                    <th scope="row"><?php esc_html_e('Daily Cron Job Time', 'livefot'); ?></th>
                    <td>
                        <label for="livefot_cron_hour"><?php esc_html_e('Hour:', 'livefot'); ?></label>
                        <select name="livefot_cron_hour" id="livefot_cron_hour">
                            <?php
                            $selected_hour = get_option('livefot_cron_hour', 8);
                            for ($h = 0; $h < 24; $h++) {
                                printf(
                                    '<option value="%d" %s>%02d:00</option>',
                                    $h,
                                    selected($h, $selected_hour, false),
                                    $h
                                );
                            }
                            ?>
                        </select>
                        &nbsp;&nbsp;
                        <label for="livefot_cron_minute"><?php esc_html_e('Minute:', 'livefot'); ?></label>
                        <select name="livefot_cron_minute" id="livefot_cron_minute">
                            <?php
                            $selected_minute = get_option('livefot_cron_minute', 0);
                            for ($m = 0; $m < 60; $m += 5) {
                                printf(
                                    '<option value="%d" %s>%02d</option>',
                                    $m,
                                    selected($m, $selected_minute, false),
                                    $m
                                );
                            }
                            ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select the time of day when the daily fixtures fetch should run.', 'livefot'); ?></p>
                    </td>
                </tr>

                <!-- Short-Range Cron Job Enable/Disable -->
                <tr>
                    <th scope="row"><label for="livefot_enable_short_cron_job"><?php esc_html_e('Enable Short-Range Fixtures Cron Job', 'livefot'); ?></label></th>
                    <td>
                        <input type="checkbox" id="livefot_enable_short_cron_job" name="livefot_enable_short_cron_job" value="1" <?php checked(1, get_option('livefot_enable_short_cron_job', 1)); ?>>
                        <p class="description"><?php esc_html_e('Enable the short-range cron job to fetch fixtures periodically.', 'livefot'); ?></p>
                    </td>
                </tr>

                <!-- Short-Range Cron Job Interval -->
             
				<tr>
    <th scope="row"><label for="livefot_cron_interval_short"><?php esc_html_e('Short-Range Fixtures Cron Interval (Minutes)', 'livefot'); ?></label></th>
    <td>
        <input type="number" id="livefot_cron_interval_short" name="livefot_cron_interval_short" value="<?php echo esc_attr(get_option('livefot_cron_interval_short', 60)); ?>" class="small-text" min="15" step="1" required>
        <p class="description"><?php esc_html_e('Set how often (in minutes) the short-range fixtures fetch should run.', 'livefot'); ?></p>
    </td>
</tr>


            </table>

            <!-- Update Intervals Section -->
            <h2><?php esc_html_e('Update Intervals', 'livefot'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="livefot_interval_matches"><?php esc_html_e('Matches Update Interval (seconds)', 'livefot'); ?></label></th>
                    <td>
                        <input type="number" id="livefot_interval_matches" name="livefot_interval_matches" value="<?php echo esc_attr(get_option('livefot_interval_matches', 30)); ?>" class="small-text" min="30" step="5" required>
                        <p class="description"><?php esc_html_e('Set the interval at which live matches are updated. Minimum 30 seconds.', 'livefot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="livefot_interval_lineups"><?php esc_html_e('Lineups Update Interval (seconds)', 'livefot'); ?></label></th>
                    <td>
                        <input type="number" id="livefot_interval_lineups" name="livefot_interval_lineups" value="<?php echo esc_attr(get_option('livefot_interval_lineups', 60)); ?>" class="small-text" min="60" step="5" required>
                        <p class="description"><?php esc_html_e('Set the interval at which lineups are updated. Minimum 60 seconds.', 'livefot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="livefot_interval_events"><?php esc_html_e('Events Update Interval (seconds)', 'livefot'); ?></label></th>
                    <td>
                        <input type="number" id="livefot_interval_events" name="livefot_interval_events" value="<?php echo esc_attr(get_option('livefot_interval_events', 60)); ?>" class="small-text" min="30" step="5" required>
                        <p class="description"><?php esc_html_e('Set the interval at which events are updated. Minimum 30 seconds.', 'livefot'); ?></p>
                    </td>
                </tr>
                <tr>
				  <th scope="row"><label for="livefot_interval_standings"><?php esc_html_e('Standings Update Interval (seconds)', 'livefot'); ?></label></th>
                    <td>
                        <input type="number" id="livefot_interval_standings" name="livefot_interval_standings" value="<?php echo esc_attr(get_option('livefot_interval_standings', 120)); ?>" class="small-text" min="60" step="5" required>
                        <p class="description"><?php esc_html_e('Set the interval at which standings are updated. Minimum 60 seconds.', 'livefot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="livefot_interval_stats"><?php esc_html_e('Statistics Update Interval (seconds)', 'livefot'); ?></label></th>
                    <td>
                        <input type="number" id="livefot_interval_stats" name="livefot_interval_stats" value="<?php echo esc_attr(get_option('livefot_interval_stats', 120)); ?>" class="small-text" min="60" step="5" required>
                        <p class="description"><?php esc_html_e('Set the interval at which statistics are updated. Minimum 60 seconds.', 'livefot'); ?></p>
                    </td>
                </tr>
            </table>

            <!-- Save Changes Button -->
            <?php submit_button(); ?>
        </form>

        <!-- Manual Fetch Form -->
        <h2><?php esc_html_e('Manual Fixtures Fetch', 'livefot'); ?></h2>
        <form method="post" action="">
            <?php
            // Add nonce for security
            wp_nonce_field('livefot_manual_fetch', '_wpnonce_livefot_manual_fetch');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="livefot_manual_date"><?php esc_html_e('Select Date to Fetch Fixtures', 'livefot'); ?></label></th>
                    <td>
                        <input type="date" id="livefot_manual_date" name="livefot_manual_date" value="<?php echo esc_attr(get_option('livefot_manual_date')); ?>" required>
                        <?php
                        // Render the submit button without wrapping it in a paragraph or div
                        submit_button(__('Fetch Fixtures for Selected Date', 'livefot'), 'secondary', 'fetch_fixtures', false);
                        ?>
                        <p class="description"><?php esc_html_e('Choose a date and click the button to manually fetch fixtures for that specific date.', 'livefot'); ?></p>
                    </td>
                </tr>
            </table>
        </form>

        <!-- Cron Job Status Section -->
        <h2><?php esc_html_e('Cron Job Status', 'livefot'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Last Run Start Time (Daily)', 'livefot'); ?></th>
                <td>
                    <?php 
                        $last_run_start = get_option('livefot_cron_last_run_start', 'Never');
                        if ($last_run_start !== 'Never') {
                            // Convert UTC to WordPress local time
                            $last_run_start_local = get_date_from_gmt($last_run_start, 'Y-m-d H:i:s');
                        } else {
                            $last_run_start_local = 'Never';
                        }
                        echo esc_html($last_run_start_local);
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Last Run End Time (Daily)', 'livefot'); ?></th>
                <td>
                    <?php 
                        $last_run_end = get_option('livefot_cron_last_run_end', 'Never');
                        if ($last_run_end !== 'Never') {
                            // Convert UTC to WordPress local time
                            $last_run_end_local = get_date_from_gmt($last_run_end, 'Y-m-d H:i:s');
                        } else {
                            $last_run_end_local = 'Never';
                        }
                        echo esc_html($last_run_end_local);
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Last Run Status (Daily)', 'livefot'); ?></th>
                <td>
                    <?php 
                        $last_run_status = get_option('livefot_cron_last_run_status', 'Never');
                        echo esc_html($last_run_status);
                    ?>
                </td>
            </tr>
        </table>

        <!-- Short-Range Cron Job Status Section -->
        <h2><?php esc_html_e('Short-Range Cron Job Status', 'livefot'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Last Run Start Time', 'livefot'); ?></th>
                <td>
                    <?php 
                        $last_run_start_short = get_option('livefot_cron_last_run_start_short', 'Never');
                        if ($last_run_start_short !== 'Never') {
                            // Convert UTC to WordPress local time
                            $last_run_start_short_local = get_date_from_gmt($last_run_start_short, 'Y-m-d H:i:s');
                        } else {
                            $last_run_start_short_local = 'Never';
                        }
                        echo esc_html($last_run_start_short_local);
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Last Run End Time', 'livefot'); ?></th>
                <td>
                    <?php 
                        $last_run_end_short = get_option('livefot_cron_last_run_end_short', 'Never');
                        if ($last_run_end_short !== 'Never') {
                            // Convert UTC to WordPress local time
                            $last_run_end_short_local = get_date_from_gmt($last_run_end_short, 'Y-m-d H:i:s');
                        } else {
                            $last_run_end_short_local = 'Never';
                        }
                        echo esc_html($last_run_end_short_local);
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Last Run Status', 'livefot'); ?></th>
                <td>
                    <?php 
                        $last_run_status_short = get_option('livefot_cron_last_run_status_short', 'Never');
                        echo esc_html($last_run_status_short);
                    ?>
                </td>
            </tr>
        </table>

        <!-- Current Time Section -->
        <h2><?php esc_html_e('Current Time', 'livefot'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Current Time (Local)', 'livefot'); ?></th>
                <td>
                    <?php 
                        // Get current WordPress local time
                        $current_time = current_time('Y-m-d H:i:s');
                        echo esc_html($current_time);
                    ?>
                </td>
            </tr>
        </table>

        <!-- Shortcode Usage Section -->
        <div class="card" style="background-color: #f9f9f9; padding: 20px; margin-top: 30px; border-radius: 5px;">
            <h2><?php esc_html_e('Shortcode Usage', 'livefot'); ?></h2>
            <p><?php esc_html_e('Use this shortcode to display matches on any page or post:', 'livefot'); ?></p>
            <code>[livefot_matches]</code>
        </div>
    </div>
    <?php
}

// Helper function to display pagination
function livefot_display_pagination($current_page, $total_pages, $base_url) {
    if ($total_pages <= 1) {
        return;
    }

    echo '<div class="livefot-pagination" style="margin-top: 20px; text-align: center;">';

    // Previous Page
    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        echo '<a href="' . esc_url(add_query_arg('paged', $prev_page, $base_url)) . '" class="livefot-paginate-button" style="margin-right: 10px; text-decoration: none; color: #0073aa;">';
        echo '<span class="dashicons dashicons-arrow-left"></span> Previous';
        echo '</a>';
    }

    // Next Page
    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;
        echo '<a href="' . esc_url(add_query_arg('paged', $next_page, $base_url)) . '" class="livefot-paginate-button" style="text-decoration: none; color: #0073aa;">';
        echo 'Next <span class="dashicons dashicons-arrow-right"></span>';
        echo '</a>';
    }

    echo '</div>';
}

// Fixtures Data page callback with pagination
function livefot_display_fixtures_data() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_fixtures';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_fixture_id = isset($_GET['search_fixture_id']) ? sanitize_text_field($_GET['search_fixture_id']) : '';
    $search_team = isset($_GET['search_team']) ? sanitize_text_field($_GET['search_team']) : '';
    $search_venue_id = isset($_GET['search_venue_id']) ? sanitize_text_field($_GET['search_venue_id']) : '';

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_fixture_id)) {
        $where .= ' AND fixture_id = %d';
        $params[] = $search_fixture_id;
    }

    if (!empty($search_team)) {
        $where .= ' AND (localteam_name LIKE %s OR visitorteam_name LIKE %s)';
        $like_search = '%' . $wpdb->esc_like($search_team) . '%';
        $params[] = $like_search;
        $params[] = $like_search;
    }

    if (!empty($search_venue_id)) {
        $where .= ' AND venue_id = %d';
        $params[] = $search_venue_id;
    }

    // Prepare the SQL query with dynamic WHERE clause
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where ORDER BY id DESC LIMIT %d OFFSET %d",
        array_merge($params, [$items_per_page, $offset])
    );

    // Get total items for pagination
    $total_items = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE $where", $params)
    );
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-fixtures-data');

    echo '<div class="wrap">';
    echo '<h1>LiveFot Fixtures Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-fixtures-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_fixture_id">Fixture ID</label></th>
                <td><input type="text" id="search_fixture_id" name="search_fixture_id" value="<?php echo esc_attr($search_fixture_id); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_team">Team Name</label></th>
                <td><input type="text" id="search_team" name="search_team" value="<?php echo esc_attr($search_team); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_venue_id">Venue ID</label></th>
                <td><input type="text" id="search_venue_id" name="search_venue_id" value="<?php echo esc_attr($search_venue_id); ?>" /></td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Fixture ID</th>';
        echo '<th>League ID</th>';
        echo '<th>Local Team ID</th>';
        echo '<th>Local Team Name</th>';
        echo '<th>Visitor Team ID</th>';
        echo '<th>Visitor Team Name</th>';
        echo '<th>Referee ID</th>';
        echo '<th>Venue ID</th>';
        // Removed Match Time and Scores Columns
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['fixture_id']) . '</td>';
            echo '<td>' . esc_html($row['league_id']) . '</td>';
            echo '<td>' . esc_html($row['localteam_id']) . '</td>';
            echo '<td>' . esc_html($row['localteam_name']) . '</td>';
            echo '<td>' . esc_html($row['visitorteam_id']) . '</td>';
            echo '<td>' . esc_html($row['visitorteam_name']) . '</td>';
            echo '<td>' . esc_html($row['referee_id']) . '</td>';
            echo '<td>' . esc_html($row['venue_id']) . '</td>';
            // Removed Match Time and Scores Data Columns
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot fixtures table with the specified search criteria.</p>';
    }

    echo '</div>';
}


// Leagues Data page callback with pagination and search
function livefot_display_leagues_data() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_leagues';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_league_id = isset($_GET['search_league_id']) ? sanitize_text_field($_GET['search_league_id']) : '';
    $search_league_name = isset($_GET['search_league_name']) ? sanitize_text_field($_GET['search_league_name']) : '';

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_league_id)) {
        $where .= ' AND league_id = %d';
        $params[] = $search_league_id;
    }

    if (!empty($search_league_name)) {
        $where .= ' AND name LIKE %s';
        $like_search = '%' . $wpdb->esc_like($search_league_name) . '%';
        $params[] = $like_search;
    }

    // Prepare the SQL query with dynamic WHERE clause
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where ORDER BY last_updated DESC LIMIT %d OFFSET %d",
        array_merge($params, [$items_per_page, $offset])
    );

    // Get total items for pagination
    $total_items = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE $where", array_slice($params, 0, count($params)))
    );
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-leagues-data');

    // Preserve search parameters in pagination URLs
    $base_url = add_query_arg([
        'search_league_id'   => $search_league_id,
        'search_league_name' => $search_league_name
    ], $base_url);

    echo '<div class="wrap">';
    echo '<h1>LiveFot Leagues Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-leagues-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_league_id">League ID</label></th>
                <td><input type="text" id="search_league_id" name="search_league_id" value="<?php echo esc_attr($search_league_id); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_league_name">League Name</label></th>
                <td><input type="text" id="search_league_name" name="search_league_name" value="<?php echo esc_attr($search_league_name); ?>" /></td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>League ID</th>';
        echo '<th>Name</th>';
        echo '<th>Is Cup</th>';
        echo '<th>Logo</th>';
        echo '<th>Current Season Id</th>';
        echo '<th>Stage</th>';
        echo '<th>Country ID</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['league_id']) . '</td>';
            echo '<td>' . esc_html($row['name']) . '</td>';
            echo '<td>' . esc_html($row['is_cup']) . '</td>';
            echo '<td>' . esc_html($row['logo_path']) . '</td>';
            echo '<td>' . esc_html($row['current_season_id']) . '</td>';
            echo '<td>' . esc_html($row['stage_name']) . '</td>';
            echo '<td>' . esc_html($row['country_id']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Leagues table with the specified search criteria.</p>';
    }

    echo '</div>';
}


// Countries Data page callback with pagination
function livefot_display_countries_data() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_countries';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Get total items
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY last_updated DESC LIMIT %d OFFSET %d", $items_per_page, $offset), ARRAY_A);

    // Base URL for pagination
    $base_url = admin_url('admin.php?page=livefot-countries-data');

    echo '<div class="wrap">';
    echo '<h1>LiveFot Countries Data</h1>';

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Country ID</th>';
        echo '<th>Name</th>';
        echo '<th>Image Path</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['country_id']) . '</td>';
            echo '<td>' . esc_html($row['name']) . '</td>';
            echo '<td>' . esc_html($row['image_path']) . '</td>';   
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Countries table.</p>';
    }

    echo '</div>';
}

// Scores Data page callback with pagination
/*function livefot_display_scores_data() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_scores';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Get total items
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY last_updated DESC LIMIT %d OFFSET %d", $items_per_page, $offset), ARRAY_A);

    // Base URL for pagination
    $base_url = admin_url('admin.php?page=livefot-scores-data');

    echo '<div class="wrap">';
    echo '<h1>LiveFot Scores Data</h1>';

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Match ID</th>';
        echo '<th>Local Team Score</th>';
        echo '<th>Visitor Team Score</th>';
        echo '<th>Local Team Penalty Score</th>';
        echo '<th>Visitor Team Penalty Score</th>';
        echo '<th>HT Score</th>';
        echo '<th>FT Score</th>';
        echo '<th>ET Score</th>';
        echo '<th>PS Score</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['fixture_id']) . '</td>';
            echo '<td>' . esc_html($row['localteam_score']) . '</td>';
            echo '<td>' . esc_html($row['visitorteam_score']) . '</td>';
            echo '<td>' . esc_html($row['localteam_pen_score']) . '</td>';
            echo '<td>' . esc_html($row['visitorteam_pen_score']) . '</td>';
            echo '<td>' . esc_html($row['ht_score']) . '</td>';
            echo '<td>' . esc_html($row['ft_score']) . '</td>';
            echo '<td>' . esc_html($row['et_score']) . '</td>';
            echo '<td>' . esc_html($row['ps_score']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Scores table.</p>';
    }

    echo '</div>';
}*/


function livefot_display_scores_data() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_scores';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_fixture_id = isset($_GET['search_fixture_id']) ? sanitize_text_field($_GET['search_fixture_id']) : '';

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_fixture_id)) {
        $where .= ' AND fixture_id = %d';
        $params[] = $search_fixture_id;
    }

    // Prepare the SQL query with dynamic WHERE clause
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where ORDER BY last_updated DESC LIMIT %d OFFSET %d",
        array_merge($params, [$items_per_page, $offset])
    );

    // Get total items for pagination
    if (!empty($search_fixture_id)) {
        $total_items = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE $where", $params)
        );
    } else {
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-scores-data');

    echo '<div class="wrap">';
    echo '<h1>LiveFot Scores Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-scores-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_fixture_id">Fixture ID</label></th>
                <td><input type="text" id="search_fixture_id" name="search_fixture_id" value="<?php echo esc_attr($search_fixture_id); ?>" /></td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Fixture ID</th>';
        echo '<th>Local Team Score</th>';
        echo '<th>Visitor Team Score</th>';
        echo '<th>Local Team Penalty Score</th>';
        echo '<th>Visitor Team Penalty Score</th>';
        echo '<th>HT Score</th>';
        echo '<th>FT Score</th>';
        echo '<th>ET Score</th>';
        echo '<th>PS Score</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['fixture_id']) . '</td>';
            echo '<td>' . esc_html($row['localteam_score']) . '</td>';
            echo '<td>' . esc_html($row['visitorteam_score']) . '</td>';
            echo '<td>' . esc_html($row['localteam_pen_score']) . '</td>';
            echo '<td>' . esc_html($row['visitorteam_pen_score']) . '</td>';
            echo '<td>' . esc_html($row['ht_score']) . '</td>';
            echo '<td>' . esc_html($row['ft_score']) . '</td>';
            echo '<td>' . esc_html($row['et_score']) . '</td>';
            echo '<td>' . esc_html($row['ps_score']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Scores table with the specified search criteria.</p>';
    }

    echo '</div>';
}




function livefot_display_time_data() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_time';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_fixture_id = isset($_GET['search_fixture_id']) ? sanitize_text_field($_GET['search_fixture_id']) : '';

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_fixture_id)) {
        $where .= ' AND fixture_id = %d';
        $params[] = $search_fixture_id;
    }

    // Prepare the SQL query with dynamic WHERE clause
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where ORDER BY last_updated DESC LIMIT %d OFFSET %d",
        array_merge($params, [$items_per_page, $offset])
    );

    // Get total items for pagination
    if (!empty($search_fixture_id)) {
        $total_items = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE $where", $params)
        );
    } else {
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-time-data');

    // Append search parameters to the base URL if they exist
    if (!empty($search_fixture_id)) {
        $base_url = add_query_arg('search_fixture_id', $search_fixture_id, $base_url);
    }

    echo '<div class="wrap">';
    echo '<h1>LiveFot Time Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-time-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_fixture_id">Fixture ID</label></th>
                <td><input type="text" id="search_fixture_id" name="search_fixture_id" value="<?php echo esc_attr($search_fixture_id); ?>" /></td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Fixture ID</th>';
        echo '<th>Status</th>';
        echo '<th>Starting DateTime</th>';
        echo '<th>Starting Date</th>';
        echo '<th>Starting Time</th>';
        echo '<th>Timestamp</th>';
        echo '<th>Timezone</th>';
        echo '<th>Minute</th>';
        echo '<th>Second</th>';
        echo '<th>Added Time</th>';
        echo '<th>Extra Minute</th>';
        echo '<th>Injury Time</th>';
        echo '<th>Match Period</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['fixture_id']) . '</td>';
            echo '<td>' . esc_html($row['status']) . '</td>';
            echo '<td>' . esc_html($row['starting_at_datetime']) . '</td>';
            echo '<td>' . esc_html($row['starting_at_date']) . '</td>';
            echo '<td>' . esc_html($row['starting_at_time']) . '</td>';
            echo '<td>' . esc_html($row['starting_at_timestamp']) . '</td>';
            echo '<td>' . esc_html($row['starting_at_timezone']) . '</td>';
            echo '<td>' . esc_html($row['minute']) . '</td>';
            echo '<td>' . esc_html($row['second']) . '</td>';
            echo '<td>' . esc_html($row['added_time']) . '</td>';
            echo '<td>' . esc_html($row['extra_minute']) . '</td>';
            echo '<td>' . esc_html($row['injury_time']) . '</td>';
            echo '<td>' . esc_html($row['match_period']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Time table with the specified search criteria.</p>';
    }

    echo '</div>';
}


function livefot_display_aggregate_data() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_aggregate';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_aggregate_id = isset($_GET['search_aggregate_id']) ? sanitize_text_field($_GET['search_aggregate_id']) : '';
    $search_league_id = isset($_GET['search_league_id']) ? sanitize_text_field($_GET['search_league_id']) : '';
    $search_visitorteam = isset($_GET['search_visitorteam']) ? sanitize_text_field($_GET['search_visitorteam']) : '';
    $search_localteam = isset($_GET['search_localteam']) ? sanitize_text_field($_GET['search_localteam']) : '';

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_aggregate_id)) {
        $where .= ' AND aggregate_id = %d';
        $params[] = $search_aggregate_id;
    }

    if (!empty($search_league_id)) {
        $where .= ' AND league_id = %d';
        $params[] = $search_league_id;
    }

    if (!empty($search_visitorteam)) {
        $where .= ' AND visitorteam LIKE %s';
        $params[] = '%' . $wpdb->esc_like($search_visitorteam) . '%';
    }

    if (!empty($search_localteam)) {
        $where .= ' AND localteam LIKE %s';
        $params[] = '%' . $wpdb->esc_like($search_localteam) . '%';
    }

    // Prepare the SQL query with dynamic WHERE clause
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where ORDER BY last_updated DESC LIMIT %d OFFSET %d",
        array_merge($params, [$items_per_page, $offset])
    );

    // Get total items for pagination
    if (!empty($params)) {
        // If any search parameters are set, include them in the count query
        $count_sql = "SELECT COUNT(*) FROM $table_name WHERE $where";
        $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $params));
    } else {
        // No search parameters, count all items
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-aggregate-data');

    // Append search parameters to the base URL if they exist
    if (!empty($search_aggregate_id)) {
        $base_url = add_query_arg('search_aggregate_id', $search_aggregate_id, $base_url);
    }
    if (!empty($search_league_id)) {
        $base_url = add_query_arg('search_league_id', $search_league_id, $base_url);
    }
    if (!empty($search_visitorteam)) {
        $base_url = add_query_arg('search_visitorteam', $search_visitorteam, $base_url);
    }
    if (!empty($search_localteam)) {
        $base_url = add_query_arg('search_localteam', $search_localteam, $base_url);
    }

    echo '<div class="wrap">';
    echo '<h1>LiveFot Aggregate Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-aggregate-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_aggregate_id">Aggregate ID</label></th>
                <td><input type="text" id="search_aggregate_id" name="search_aggregate_id" value="<?php echo esc_attr($search_aggregate_id); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_league_id">League ID</label></th>
                <td><input type="text" id="search_league_id" name="search_league_id" value="<?php echo esc_attr($search_league_id); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_visitorteam">Visitor Team</label></th>
                <td><input type="text" id="search_visitorteam" name="search_visitorteam" value="<?php echo esc_attr($search_visitorteam); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_localteam">Local Team</label></th>
                <td><input type="text" id="search_localteam" name="search_localteam" value="<?php echo esc_attr($search_localteam); ?>" /></td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Aggregate ID</th>';
        echo '<th>League ID</th>';
        echo '<th>Season ID</th>';
        echo '<th>Stage ID</th>';
        echo '<th>Local Team</th>';
        echo '<th>Local Team ID</th>';
        echo '<th>Visitor Team</th>';
        echo '<th>Visitor Team ID</th>';
        echo '<th>Result</th>';
        echo '<th>Winner</th>';
        echo '<th>Detail</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['aggregate_id']) . '</td>';
            echo '<td>' . esc_html($row['league_id']) . '</td>';
            echo '<td>' . esc_html($row['season_id']) . '</td>';
            echo '<td>' . esc_html($row['stage_id']) . '</td>';
            echo '<td>' . esc_html($row['localteam']) . '</td>';
            echo '<td>' . esc_html($row['localteam_id']) . '</td>';
            echo '<td>' . esc_html($row['visitorteam']) . '</td>';
            echo '<td>' . esc_html($row['visitorteam_id']) . '</td>';
            echo '<td>' . esc_html($row['result']) . '</td>';
            echo '<td>' . esc_html($row['winner']) . '</td>';
            echo '<td>' . esc_html($row['detail']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Aggregate table with the specified search criteria.</p>';
    }

    echo '</div>';
}

function livefot_display_referees_data() {
    global $wpdb;

    // Capability check to ensure only authorized users can access this page
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_referees';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_referee_id = isset($_GET['search_referee_id']) ? sanitize_text_field($_GET['search_referee_id']) : '';
    $search_common_name = isset($_GET['search_common_name']) ? sanitize_text_field($_GET['search_common_name']) : '';

    // Cast numerical inputs to integers for validation
    if (!empty($search_referee_id)) {
        $search_referee_id = intval($search_referee_id);
    }

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_referee_id)) {
        $where .= ' AND referee_id = %d';
        $params[] = $search_referee_id;
    }

    if (!empty($search_common_name)) {
        $where .= ' AND common_name LIKE %s';
        $params[] = '%' . $wpdb->esc_like($search_common_name) . '%';
    }

    // Prepare the SQL query with dynamic WHERE clause
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where ORDER BY last_updated DESC LIMIT %d OFFSET %d",
        array_merge($params, [$items_per_page, $offset])
    );

    // Get total items for pagination
    if (!empty($params)) {
        // If any search parameters are set, include them in the count query
        $count_sql = "SELECT COUNT(*) FROM $table_name WHERE $where";
        $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $params));
    } else {
        // No search parameters, count all items
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-referees-data');

    // Append search parameters to the base URL if they exist
    if (!empty($search_referee_id)) {
        $base_url = add_query_arg('search_referee_id', $search_referee_id, $base_url);
    }
    if (!empty($search_common_name)) {
        $base_url = add_query_arg('search_common_name', $search_common_name, $base_url);
    }

    echo '<div class="wrap">';
    echo '<h1>LiveFot Referees Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-referees-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_referee_id">Referee ID</label></th>
                <td><input type="text" id="search_referee_id" name="search_referee_id" value="<?php echo esc_attr($search_referee_id); ?>" placeholder="Enter Referee ID" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_common_name">Common Name</label></th>
                <td><input type="text" id="search_common_name" name="search_common_name" value="<?php echo esc_attr($search_common_name); ?>" placeholder="Enter Common Name" /></td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
        <?php if (!empty($search_referee_id) || !empty($search_common_name)) : ?>
            <a href="<?php echo admin_url('admin.php?page=livefot-referees-data'); ?>" class="button-secondary">Reset</a>
        <?php endif; ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Referee ID</th>';
        echo '<th>Common Name</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['referee_id']) . '</td>';
            echo '<td>' . esc_html($row['common_name']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Referees table with the specified search criteria.</p>';
    }

    echo '</div>';
}



function livefot_display_venues_data() {
    global $wpdb;

    // Capability check to ensure only authorized users can access this page
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_venues';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_venue_id = isset($_GET['search_venue_id']) ? sanitize_text_field($_GET['search_venue_id']) : '';
    $search_name = isset($_GET['search_name']) ? sanitize_text_field($_GET['search_name']) : '';

    // Cast numerical inputs to integers for validation
    if (!empty($search_venue_id)) {
        $search_venue_id = intval($search_venue_id);
    }

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_venue_id)) {
        $where .= ' AND venue_id = %d';
        $params[] = $search_venue_id;
    }

    if (!empty($search_name)) {
        $where .= ' AND name LIKE %s';
        $params[] = '%' . $wpdb->esc_like($search_name) . '%';
    }

    // Prepare the SQL query with dynamic WHERE clause
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where ORDER BY last_updated DESC LIMIT %d OFFSET %d",
        array_merge($params, [$items_per_page, $offset])
    );

    // Get total items for pagination
    if (!empty($params)) {
        // If any search parameters are set, include them in the count query
        $count_sql = "SELECT COUNT(*) FROM $table_name WHERE $where";
        $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $params));
    } else {
        // No search parameters, count all items
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-venues-data');

    // Append search parameters to the base URL if they exist
    if (!empty($search_venue_id)) {
        $base_url = add_query_arg('search_venue_id', $search_venue_id, $base_url);
    }
    if (!empty($search_name)) {
        $base_url = add_query_arg('search_name', $search_name, $base_url);
    }

    echo '<div class="wrap">';
    echo '<h1>LiveFot Venues Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-venues-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_venue_id">Venue ID</label></th>
                <td><input type="text" id="search_venue_id" name="search_venue_id" value="<?php echo esc_attr($search_venue_id); ?>" placeholder="Enter Venue ID" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_name">Venue Name</label></th>
                <td><input type="text" id="search_name" name="search_name" value="<?php echo esc_attr($search_name); ?>" placeholder="Enter Venue Name" /></td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
        <?php if (!empty($search_venue_id) || !empty($search_name)) : ?>
            <a href="<?php echo admin_url('admin.php?page=livefot-venues-data'); ?>" class="button-secondary">Reset</a>
        <?php endif; ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Venue ID</th>';
        echo '<th>Name</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['venue_id']) . '</td>';
            echo '<td>' . esc_html($row['name']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Venues table with the specified search criteria.</p>';
    }

    echo '</div>';
}


function livefot_display_lineup_data() {
    global $wpdb;

    // Capability check to ensure only authorized users can access this page
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_lineups';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_match_id = isset($_GET['search_match_id']) ? sanitize_text_field($_GET['search_match_id']) : '';
    $search_team_id = isset($_GET['search_team_id']) ? sanitize_text_field($_GET['search_team_id']) : '';
    $search_player_id = isset($_GET['search_player_id']) ? sanitize_text_field($_GET['search_player_id']) : '';
    $search_position = isset($_GET['search_position']) ? sanitize_text_field($_GET['search_position']) : '';
    $search_formation_position = isset($_GET['search_formation_position']) ? sanitize_text_field($_GET['search_formation_position']) : '';

    // Cast numerical inputs to integers for validation
    if (!empty($search_match_id)) {
        $search_match_id = intval($search_match_id);
    }
    if (!empty($search_team_id)) {
        $search_team_id = intval($search_team_id);
    }
    if (!empty($search_player_id)) {
        $search_player_id = intval($search_player_id);
    }

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_match_id)) {
        $where .= ' AND match_id = %d';
        $params[] = $search_match_id;
    }

    if (!empty($search_team_id)) {
        $where .= ' AND team_id = %d';
        $params[] = $search_team_id;
    }

    if (!empty($search_player_id)) {
        $where .= ' AND player_id = %d';
        $params[] = $search_player_id;
    }

    if (!empty($search_position)) {
        $where .= ' AND position LIKE %s';
        $params[] = '%' . $wpdb->esc_like($search_position) . '%';
    }

    if (!empty($search_formation_position)) {
        $where .= ' AND formation_position LIKE %s';
        $params[] = '%' . $wpdb->esc_like($search_formation_position) . '%';
    }

    // Prepare the SQL query with dynamic WHERE clause
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where ORDER BY last_updated DESC LIMIT %d OFFSET %d",
        array_merge($params, [$items_per_page, $offset])
    );

    // Get total items for pagination
    if (!empty($params)) {
        // If any search parameters are set, include them in the count query
        $count_sql = "SELECT COUNT(*) FROM $table_name WHERE $where";
        $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $params));
    } else {
        // No search parameters, count all items
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-lineup-data');

    // Append search parameters to the base URL if they exist
    if (!empty($search_match_id)) {
        $base_url = add_query_arg('search_match_id', $search_match_id, $base_url);
    }
    if (!empty($search_team_id)) {
        $base_url = add_query_arg('search_team_id', $search_team_id, $base_url);
    }
    if (!empty($search_player_id)) {
        $base_url = add_query_arg('search_player_id', $search_player_id, $base_url);
    }
    if (!empty($search_position)) {
        $base_url = add_query_arg('search_position', $search_position, $base_url);
    }
    if (!empty($search_formation_position)) {
        $base_url = add_query_arg('search_formation_position', $search_formation_position, $base_url);
    }

    echo '<div class="wrap">';
    echo '<h1>LiveFot Lineup Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-lineup-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_match_id">Match ID</label></th>
                <td><input type="text" id="search_match_id" name="search_match_id" value="<?php echo esc_attr($search_match_id); ?>" placeholder="Enter Match ID" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_team_id">Team ID</label></th>
                <td><input type="text" id="search_team_id" name="search_team_id" value="<?php echo esc_attr($search_team_id); ?>" placeholder="Enter Team ID" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_player_id">Player ID</label></th>
                <td><input type="text" id="search_player_id" name="search_player_id" value="<?php echo esc_attr($search_player_id); ?>" placeholder="Enter Player ID" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_position">Position</label></th>
                <td><input type="text" id="search_position" name="search_position" value="<?php echo esc_attr($search_position); ?>" placeholder="Enter Position" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_formation_position">Formation Position</label></th>
                <td><input type="text" id="search_formation_position" name="search_formation_position" value="<?php echo esc_attr($search_formation_position); ?>" placeholder="Enter Formation Position" /></td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
        <?php if (!empty($search_match_id) || !empty($search_team_id) || !empty($search_player_id) || !empty($search_position) || !empty($search_formation_position)) : ?>
            <a href="<?php echo admin_url('admin.php?page=livefot-lineup-data'); ?>" class="button-secondary">Reset</a>
        <?php endif; ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Match ID</th>';
        echo '<th>Team ID</th>';
        echo '<th>Player ID</th>';
        echo '<th>Number</th>';
        echo '<th>Position</th>';
        echo '<th>Formation Position</th>';
        echo '<th>Captain</th>';
        echo '<th>Type</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['match_id']) . '</td>';
            echo '<td>' . esc_html($row['team_id']) . '</td>';
            echo '<td>' . esc_html($row['player_id']) . '</td>';
            echo '<td>' . esc_html($row['number']) . '</td>';
            echo '<td>' . esc_html($row['position']) . '</td>';
            echo '<td>' . esc_html($row['formation_position']) . '</td>';
            echo '<td>' . ($row['captain'] ? 'Yes' : 'No') . '</td>';
            echo '<td>' . esc_html($row['type']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Lineups table with the specified search criteria.</p>';
    }

    echo '</div>';
}


function livefot_display_players_data() {
    global $wpdb;

    // Capability check to ensure only authorized users can access this page
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_players';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_player_id = isset($_GET['search_player_id']) ? sanitize_text_field($_GET['search_player_id']) : '';
    $search_player_name = isset($_GET['search_player_name']) ? sanitize_text_field($_GET['search_player_name']) : '';

    // Cast numerical input to integer for validation
    if (!empty($search_player_id)) {
        $search_player_id = intval($search_player_id);
    }

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_player_id)) {
        $where .= ' AND player_id = %d';
        $params[] = $search_player_id;
    }

    if (!empty($search_player_name)) {
        $where .= ' AND player_name LIKE %s';
        $params[] = '%' . $wpdb->esc_like($search_player_name) . '%';
    }

    // Prepare the SQL query with dynamic WHERE clause
    if (!empty($params)) {
        // If any search parameters are set, include them in the query
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE $where ORDER BY id DESC LIMIT %d OFFSET %d",
            array_merge($params, [$items_per_page, $offset])
        );

        // Prepare count query with the same WHERE clause
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE $where",
            $params
        );
    } else {
        // No search parameters, fetch all
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d",
            $items_per_page,
            $offset
        );
        $count_sql = "SELECT COUNT(*) FROM $table_name";
    }

    // Get total items for pagination
    $total_items = $wpdb->get_var($count_sql);
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-players-data');

    // Append search parameters to the base URL if they exist
    if (!empty($search_player_id)) {
        $base_url = add_query_arg('search_player_id', $search_player_id, $base_url);
    }
    if (!empty($search_player_name)) {
        $base_url = add_query_arg('search_player_name', $search_player_name, $base_url);
    }

    echo '<div class="wrap">';
    echo '<h1>LiveFot Players Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-players-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_player_id">Player ID</label></th>
                <td>
                    <input type="text" id="search_player_id" name="search_player_id" value="<?php echo esc_attr($search_player_id); ?>" placeholder="Enter Player ID" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="search_player_name">Player Name</label></th>
                <td>
                    <input type="text" id="search_player_name" name="search_player_name" value="<?php echo esc_attr($search_player_name); ?>" placeholder="Enter Player Name" />
                </td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
        <?php if (!empty($search_player_id) || !empty($search_player_name)) : ?>
            <a href="<?php echo admin_url('admin.php?page=livefot-players-data'); ?>" class="button-secondary">Reset</a>
        <?php endif; ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Player ID</th>';
        echo '<th>Player Name</th>';
        echo '<th>Logo Path</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['player_id']) . '</td>';
            echo '<td>' . esc_html($row['player_name']) . '</td>';
            echo '<td>' . esc_html($row['logo_path']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Players table with the specified search criteria.</p>';
    }

    echo '</div>';
}




function livefot_display_events_data() {
    global $wpdb;

    // Capability check to ensure only authorized users can access this page
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_events';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_match_id = isset($_GET['search_match_id']) ? sanitize_text_field($_GET['search_match_id']) : '';
    $search_event_id = isset($_GET['search_event_id']) ? sanitize_text_field($_GET['search_event_id']) : '';
    $search_type = isset($_GET['search_type']) ? sanitize_text_field($_GET['search_type']) : '';
    $search_team_id = isset($_GET['search_team_id']) ? sanitize_text_field($_GET['search_team_id']) : '';
    $search_player_id = isset($_GET['search_player_id']) ? sanitize_text_field($_GET['search_player_id']) : '';
    $search_related_player_id = isset($_GET['search_related_player_id']) ? sanitize_text_field($_GET['search_related_player_id']) : '';

    // Cast numerical inputs to integers for validation
    if (!empty($search_match_id)) {
        $search_match_id = intval($search_match_id);
    }
    if (!empty($search_event_id)) {
        $search_event_id = intval($search_event_id);
    }
    if (!empty($search_team_id)) {
        $search_team_id = intval($search_team_id);
    }
    if (!empty($search_player_id)) {
        $search_player_id = intval($search_player_id);
    }
    if (!empty($search_related_player_id)) {
        $search_related_player_id = intval($search_related_player_id);
    }

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_match_id)) {
        $where .= ' AND match_id = %d';
        $params[] = $search_match_id;
    }

    if (!empty($search_event_id)) {
        $where .= ' AND event_id = %d';
        $params[] = $search_event_id;
    }

    if (!empty($search_type)) {
        $where .= ' AND type LIKE %s';
        $params[] = '%' . $wpdb->esc_like($search_type) . '%';
    }

    if (!empty($search_team_id)) {
        $where .= ' AND team_id = %d';
        $params[] = $search_team_id;
    }

    if (!empty($search_player_id)) {
        $where .= ' AND player_id = %d';
        $params[] = $search_player_id;
    }

    if (!empty($search_related_player_id)) {
        $where .= ' AND related_player_id = %d';
        $params[] = $search_related_player_id;
    }

    // Prepare the SQL query with dynamic WHERE clause
    $sql = "";
    $count_sql = "";

    if (!empty($params)) {
        // If any search parameters are set, include them in the query
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE $where ORDER BY last_updated DESC LIMIT %d OFFSET %d",
            array_merge($params, [$items_per_page, $offset])
        );

        // Prepare count query with the same WHERE clause
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE $where",
            $params
        );
    } else {
        // No search parameters, fetch all
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY last_updated DESC LIMIT %d OFFSET %d",
            $items_per_page,
            $offset
        );
        $count_sql = "SELECT COUNT(*) FROM $table_name";
    }

    // Get total items for pagination
    $total_items = $wpdb->get_var($count_sql);
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-events-data');

    // Append search parameters to the base URL if they exist
    if (!empty($search_match_id)) {
        $base_url = add_query_arg('search_match_id', $search_match_id, $base_url);
    }
    if (!empty($search_event_id)) {
        $base_url = add_query_arg('search_event_id', $search_event_id, $base_url);
    }
    if (!empty($search_type)) {
        $base_url = add_query_arg('search_type', $search_type, $base_url);
    }
    if (!empty($search_team_id)) {
        $base_url = add_query_arg('search_team_id', $search_team_id, $base_url);
    }
    if (!empty($search_player_id)) {
        $base_url = add_query_arg('search_player_id', $search_player_id, $base_url);
    }
    if (!empty($search_related_player_id)) {
        $base_url = add_query_arg('search_related_player_id', $search_related_player_id, $base_url);
    }

    echo '<div class="wrap">';
    echo '<h1>LiveFot Events Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-events-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_match_id">Match ID</label></th>
                <td><input type="text" id="search_match_id" name="search_match_id" value="<?php echo esc_attr($search_match_id); ?>" placeholder="Enter Match ID" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_event_id">Event ID</label></th>
                <td><input type="text" id="search_event_id" name="search_event_id" value="<?php echo esc_attr($search_event_id); ?>" placeholder="Enter Event ID" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_type">Type</label></th>
                <td><input type="text" id="search_type" name="search_type" value="<?php echo esc_attr($search_type); ?>" placeholder="Enter Type" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_team_id">Team ID</label></th>
                <td><input type="text" id="search_team_id" name="search_team_id" value="<?php echo esc_attr($search_team_id); ?>" placeholder="Enter Team ID" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_player_id">Player ID</label></th>
                <td><input type="text" id="search_player_id" name="search_player_id" value="<?php echo esc_attr($search_player_id); ?>" placeholder="Enter Player ID" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_related_player_id">Related Player ID</label></th>
                <td><input type="text" id="search_related_player_id" name="search_related_player_id" value="<?php echo esc_attr($search_related_player_id); ?>" placeholder="Enter Related Player ID" /></td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
        <?php if (!empty($search_match_id) || !empty($search_event_id) || !empty($search_type) || !empty($search_team_id) || !empty($search_player_id) || !empty($search_related_player_id)) : ?>
            <a href="<?php echo admin_url('admin.php?page=livefot-events-data'); ?>" class="button-secondary">Reset</a>
        <?php endif; ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Event ID</th>';
        echo '<th>Match ID</th>';
        echo '<th>Team ID</th>';
        echo '<th>Player ID</th>';
        echo '<th>Related Player ID</th>';
        echo '<th>Type</th>';
        echo '<th>Minute</th>';
        echo '<th>Extra Minute</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['event_id']) . '</td>';
            echo '<td>' . esc_html($row['match_id']) . '</td>';
            echo '<td>' . esc_html($row['team_id']) . '</td>';
            echo '<td>' . esc_html($row['player_id']) . '</td>';
            echo '<td>' . esc_html($row['related_player_id']) . '</td>';
            echo '<td>' . esc_html($row['type']) . '</td>';
            echo '<td>' . esc_html($row['minute']) . '</td>';
            echo '<td>' . esc_html($row['extra_minute']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Events table with the specified search criteria.</p>';
    }

    echo '</div>';
}




function livefot_display_teams_data() {
    global $wpdb;

    // Capability check to ensure only authorized users can access this page
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_teams';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_team_id = isset($_GET['search_team_id']) ? sanitize_text_field($_GET['search_team_id']) : '';
    $search_name = isset($_GET['search_name']) ? sanitize_text_field($_GET['search_name']) : '';

    // Cast numerical inputs to integers for validation
    if (!empty($search_team_id)) {
        $search_team_id = intval($search_team_id);
    }

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_team_id)) {
        $where .= ' AND team_id = %d';
        $params[] = $search_team_id;
    }

    if (!empty($search_name)) {
        $where .= ' AND name LIKE %s';
        $params[] = '%' . $wpdb->esc_like($search_name) . '%';
    }

    // Prepare the SQL query with dynamic WHERE clause
    if (!empty($params)) {
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE $where ORDER BY id DESC LIMIT %d OFFSET %d",
            array_merge($params, [$items_per_page, $offset])
        );
    } else {
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d",
            [$items_per_page, $offset]
        );
    }

    // Get total items for pagination
    if (!empty($params)) {
        // If any search parameters are set, include them in the count query
        $count_sql = "SELECT COUNT(*) FROM $table_name WHERE $where";
        $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $params));
    } else {
        // No search parameters, count all items
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-teams-data');

    // Append search parameters to the base URL if they exist
    if (!empty($search_team_id)) {
        $base_url = add_query_arg('search_team_id', $search_team_id, $base_url);
    }
    if (!empty($search_name)) {
        $base_url = add_query_arg('search_name', urlencode($search_name), $base_url);
    }

    echo '<div class="wrap">';
    echo '<h1>LiveFot Teams Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-teams-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_team_id">Team ID</label></th>
                <td><input type="text" id="search_team_id" name="search_team_id" value="<?php echo esc_attr($search_team_id); ?>" placeholder="Enter Team ID" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="search_name">Team Name</label></th>
                <td><input type="text" id="search_name" name="search_name" value="<?php echo esc_attr($search_name); ?>" placeholder="Enter Team Name" /></td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
        <?php if (!empty($search_team_id) || !empty($search_name)) : ?>
            <a href="<?php echo admin_url('admin.php?page=livefot-teams-data'); ?>" class="button-secondary">Reset</a>
        <?php endif; ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Team ID</th>';
        echo '<th>Name</th>';
        echo '<th>Shortcode</th>';
        echo '<th>Twitter</th>';
        echo '<th>Country ID</th>';
        echo '<th>National Team</th>';
        echo '<th>Founded</th>';
        echo '<th>Logo Path</th>';
        echo '<th>Venue ID</th>';
        echo '<th>Current Season ID</th>';
        echo '<th>Gender</th>';
        echo '<th>Team Type</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['team_id']) . '</td>';
            echo '<td>' . esc_html($row['name']) . '</td>';
            echo '<td>' . esc_html($row['shortcode']) . '</td>';
            echo '<td>' . esc_html($row['twitter']) . '</td>';
            echo '<td>' . esc_html($row['country_id']) . '</td>';
            echo '<td>' . ($row['national_team'] ? 'Yes' : 'No') . '</td>';
            echo '<td>' . esc_html($row['founded']) . '</td>';
            echo '<td>' . esc_html($row['logo_path']) . '</td>';
            echo '<td>' . esc_html($row['venue_id']) . '</td>';
            echo '<td>' . esc_html($row['current_season_id']) . '</td>';
            echo '<td>' . esc_html($row['gender']) . '</td>';
            echo '<td>' . esc_html($row['team_type']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Teams table with the specified search criteria.</p>';
    }

    echo '</div>';
}



// Formations Data page callback with pagination
function livefot_display_formations_data() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_formations';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Get total items
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d", $items_per_page, $offset), ARRAY_A);

    // Base URL for pagination
    $base_url = admin_url('admin.php?page=livefot-formations-data');

    echo '<div class="wrap">';
    echo '<h1>LiveFot Formations Data</h1>';

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Match ID</th>';
        echo '<th>Local Team ID</th>';
        echo '<th>Local Team Formation</th>';
        echo '<th>Visitor Team ID</th>';
        echo '<th>Visitor Team Formation</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['match_id']) . '</td>';
            echo '<td>' . esc_html($row['local_team_id']) . '</td>';
            echo '<td>' . esc_html($row['local_team_formation']) . '</td>';
            echo '<td>' . esc_html($row['visitor_team_id']) . '</td>';
            echo '<td>' . esc_html($row['visitor_team_formation']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Formations table.</p>';
    }

    echo '</div>';
}



function livefot_display_standings_data() {
    global $wpdb;

    // Capability check to ensure only authorized users can access this page
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_standings';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_standing_id = isset($_GET['search_standing_id']) ? sanitize_text_field($_GET['search_standing_id']) : '';
    $search_team_id      = isset($_GET['search_team_id'])      ? sanitize_text_field($_GET['search_team_id'])      : '';
    $search_league_id    = isset($_GET['search_league_id'])    ? sanitize_text_field($_GET['search_league_id'])    : '';
    $search_round_id     = isset($_GET['search_round_id'])     ? sanitize_text_field($_GET['search_round_id'])     : '';
    $search_season_id    = isset($_GET['search_season_id'])    ? sanitize_text_field($_GET['search_season_id'])    : '';
    $search_stage_id     = isset($_GET['search_stage_id'])     ? sanitize_text_field($_GET['search_stage_id'])     : '';
    $search_group_id     = isset($_GET['search_group_id'])     ? sanitize_text_field($_GET['search_group_id'])     : '';

    // Cast numerical inputs to integers for validation
    if (!empty($search_standing_id)) {
        $search_standing_id = intval($search_standing_id);
    }
    if (!empty($search_team_id)) {
        $search_team_id = intval($search_team_id);
    }
    if (!empty($search_league_id)) {
        $search_league_id = intval($search_league_id);
    }
    if (!empty($search_round_id)) {
        $search_round_id = intval($search_round_id);
    }
    if (!empty($search_season_id)) {
        $search_season_id = intval($search_season_id);
    }
    if (!empty($search_stage_id)) {
        $search_stage_id = intval($search_stage_id);
    }
    if (!empty($search_group_id)) {
        $search_group_id = intval($search_group_id);
    }

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_standing_id)) {
        $where .= ' AND standing_id = %d';
        $params[] = $search_standing_id;
    }

    if (!empty($search_team_id)) {
        $where .= ' AND team_id = %d';
        $params[] = $search_team_id;
    }

    if (!empty($search_league_id)) {
        $where .= ' AND league_id = %d';
        $params[] = $search_league_id;
    }

    if (!empty($search_round_id)) {
        $where .= ' AND round_id = %d';
        $params[] = $search_round_id;
    }

    if (!empty($search_season_id)) {
        $where .= ' AND season_id = %d';
        $params[] = $search_season_id;
    }

    if (!empty($search_stage_id)) {
        $where .= ' AND stage_id = %d';
        $params[] = $search_stage_id;
    }

    if (!empty($search_group_id)) {
        $where .= ' AND group_id = %d';
        $params[] = $search_group_id;
    }

    // Prepare the SQL query with dynamic WHERE clause
    if (!empty($params)) {
        // If any search parameters are set, include them in the query
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE $where ORDER BY position ASC LIMIT %d OFFSET %d",
            array_merge($params, [$items_per_page, $offset])
        );

        // Prepare count query with the same WHERE clause
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE $where",
            $params
        );
    } else {
        // No search parameters, fetch all
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY position ASC LIMIT %d OFFSET %d",
            $items_per_page,
            $offset
        );
        $count_sql = "SELECT COUNT(*) FROM $table_name";
    }

    // Get total items for pagination
    $total_items = $wpdb->get_var($count_sql);
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-standings-data');

    // Append search parameters to the base URL if they exist
    if (!empty($search_standing_id)) {
        $base_url = add_query_arg('search_standing_id', $search_standing_id, $base_url);
    }
    if (!empty($search_team_id)) {
        $base_url = add_query_arg('search_team_id', $search_team_id, $base_url);
    }
    if (!empty($search_league_id)) {
        $base_url = add_query_arg('search_league_id', $search_league_id, $base_url);
    }
    if (!empty($search_round_id)) {
        $base_url = add_query_arg('search_round_id', $search_round_id, $base_url);
    }
    if (!empty($search_season_id)) {
        $base_url = add_query_arg('search_season_id', $search_season_id, $base_url);
    }
    if (!empty($search_stage_id)) {
        $base_url = add_query_arg('search_stage_id', $search_stage_id, $base_url);
    }
    if (!empty($search_group_id)) {
        $base_url = add_query_arg('search_group_id', $search_group_id, $base_url);
    }

    echo '<div class="wrap">';
    echo '<h1>LiveFot Standings Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-standings-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_standing_id">Standing ID</label></th>
                <td>
                    <input type="text" id="search_standing_id" name="search_standing_id" value="<?php echo esc_attr($search_standing_id); ?>" placeholder="Enter Standing ID" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="search_team_id">Team ID</label></th>
                <td>
                    <input type="text" id="search_team_id" name="search_team_id" value="<?php echo esc_attr($search_team_id); ?>" placeholder="Enter Team ID" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="search_league_id">League ID</label></th>
                <td>
                    <input type="text" id="search_league_id" name="search_league_id" value="<?php echo esc_attr($search_league_id); ?>" placeholder="Enter League ID" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="search_round_id">Round ID</label></th>
                <td>
                    <input type="text" id="search_round_id" name="search_round_id" value="<?php echo esc_attr($search_round_id); ?>" placeholder="Enter Round ID" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="search_season_id">Season ID</label></th>
                <td>
                    <input type="text" id="search_season_id" name="search_season_id" value="<?php echo esc_attr($search_season_id); ?>" placeholder="Enter Season ID" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="search_stage_id">Stage ID</label></th>
                <td>
                    <input type="text" id="search_stage_id" name="search_stage_id" value="<?php echo esc_attr($search_stage_id); ?>" placeholder="Enter Stage ID" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="search_group_id">Group ID</label></th>
                <td>
                    <input type="text" id="search_group_id" name="search_group_id" value="<?php echo esc_attr($search_group_id); ?>" placeholder="Enter Group ID" />
                </td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
        <?php if (!empty($search_standing_id) || !empty($search_team_id) || !empty($search_league_id) || !empty($search_round_id) || !empty($search_season_id) || !empty($search_stage_id) || !empty($search_group_id)) : ?>
            <a href="<?php echo admin_url('admin.php?page=livefot-standings-data'); ?>" class="button-secondary">Reset</a>
        <?php endif; ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Standing ID</th>';
        echo '<th>League ID</th>';
        echo '<th>Group ID</th>';
        echo '<th>Season ID</th>';
        echo '<th>Stage ID</th>';
        echo '<th>Round ID</th>';
        echo '<th>Position</th>';
        echo '<th>Team ID</th>';
        echo '<th>Team Name</th>';
        echo '<th>Shortcode</th>';
        echo '<th>Team Logo</th>';
        echo '<th>Goals</th>';
        echo '<th>Goal Diff</th>';
        echo '<th>Wins</th>';
        echo '<th>Lost</th>';
        echo '<th>Draws</th>';
        echo '<th>Played</th>';
        echo '<th>Points</th>';
        echo '<th>Description</th>';
        echo '<th>Recent Form</th>';
        echo '<th>Standing Rule ID</th>';
        echo '<th>Result</th>';
        echo '<th>Fairplay Points Lose</th>';
        echo '<th>Updated At</th>';
        echo '<th>Inserted At</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['standing_id']) . '</td>';
            echo '<td>' . esc_html($row['league_id']) . '</td>';
            echo '<td>' . esc_html($row['group_id']) . '</td>';
            echo '<td>' . esc_html($row['season_id']) . '</td>';
            echo '<td>' . esc_html($row['stage_id']) . '</td>';
            echo '<td>' . esc_html($row['round_id']) . '</td>';
            echo '<td>' . esc_html($row['position']) . '</td>';
            echo '<td>' . esc_html($row['team_id']) . '</td>';
            echo '<td>' . esc_html($row['team_name']) . '</td>';
            echo '<td>' . esc_html($row['shortcode']) . '</td>';
            echo '<td><img src="' . esc_url($row['team_logo']) . '" alt="' . esc_attr($row['team_name']) . '" width="50"></td>';
            echo '<td>' . esc_html($row['goals']) . '</td>';
            echo '<td>' . esc_html($row['goal_diff']) . '</td>';
            echo '<td>' . esc_html($row['wins']) . '</td>';
            echo '<td>' . esc_html($row['lost']) . '</td>';
            echo '<td>' . esc_html($row['draws']) . '</td>';
            echo '<td>' . esc_html($row['played']) . '</td>';
            echo '<td>' . esc_html($row['points']) . '</td>';
            echo '<td>' . esc_html($row['description']) . '</td>';
            echo '<td>' . esc_html($row['recent_form']) . '</td>';
            echo '<td>' . esc_html($row['standing_rule_id']) . '</td>';
            echo '<td>' . esc_html($row['result']) . '</td>';
            echo '<td>' . esc_html($row['fairplay_points_lose']) . '</td>';
            echo '<td>' . esc_html($row['updated_at']) . '</td>';
            echo '<td>' . esc_html($row['inserted_at']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Standings table with the specified search criteria.</p>';
    }

    echo '</div>';
}




function livefot_display_team_stats_data() {
    global $wpdb;

    // Capability check to ensure only authorized users can access this page
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_team_stats';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Initialize search variables
    $search_team_id    = isset($_GET['search_team_id'])    ? sanitize_text_field($_GET['search_team_id'])    : '';
    $search_fixture_id = isset($_GET['search_fixture_id']) ? sanitize_text_field($_GET['search_fixture_id']) : '';

    // Cast numerical inputs to integers for validation
    if (!empty($search_team_id)) {
        $search_team_id = intval($search_team_id);
    }
    if (!empty($search_fixture_id)) {
        $search_fixture_id = intval($search_fixture_id);
    }

    // Build the WHERE clause based on search inputs
    $where = '1=1'; // Default where clause
    $params = [];

    if (!empty($search_team_id)) {
        $where .= ' AND team_id = %d';
        $params[] = $search_team_id;
    }

    if (!empty($search_fixture_id)) {
        $where .= ' AND fixture_id = %d';
        $params[] = $search_fixture_id;
    }

    // Prepare the SQL query with dynamic WHERE clause
    if (!empty($params)) {
        // If any search parameters are set, include them in the query
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE $where ORDER BY stats_id ASC LIMIT %d OFFSET %d",
            array_merge($params, [$items_per_page, $offset])
        );

        // Prepare count query with the same WHERE clause
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE $where",
            $params
        );
    } else {
        // No search parameters, fetch all
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY stats_id ASC LIMIT %d OFFSET %d",
            $items_per_page,
            $offset
        );
        $count_sql = "SELECT COUNT(*) FROM $table_name";
    }

    // Get total items for pagination
    $total_items = $wpdb->get_var($count_sql);
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination and search filters
    $results = $wpdb->get_results($sql, ARRAY_A);

    // Base URL for pagination and maintaining search parameters
    $base_url = admin_url('admin.php?page=livefot-team-stats-data');

    // Append search parameters to the base URL if they exist
    if (!empty($search_team_id)) {
        $base_url = add_query_arg('search_team_id', $search_team_id, $base_url);
    }
    if (!empty($search_fixture_id)) {
        $base_url = add_query_arg('search_fixture_id', $search_fixture_id, $base_url);
    }

    echo '<div class="wrap">';
    echo '<h1>LiveFot Team Statistics Data</h1>';

    // Search Form
    ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="livefot-team-stats-data" />
        <table class="form-table">
            <tr>
                <th scope="row"><label for="search_team_id">Team ID</label></th>
                <td>
                    <input type="text" id="search_team_id" name="search_team_id" value="<?php echo esc_attr($search_team_id); ?>" placeholder="Enter Team ID" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="search_fixture_id">Fixture ID</label></th>
                <td>
                    <input type="text" id="search_fixture_id" name="search_fixture_id" value="<?php echo esc_attr($search_fixture_id); ?>" placeholder="Enter Fixture ID" />
                </td>
            </tr>
        </table>
        <?php submit_button('Search'); ?>
        <?php if (!empty($search_team_id) || !empty($search_fixture_id)) : ?>
            <a href="<?php echo admin_url('admin.php?page=livefot-team-stats-data'); ?>" class="button-secondary">Reset</a>
        <?php endif; ?>
    </form>
    <?php

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Fixture ID</th>';
        echo '<th>Team ID</th>';
     //   echo '<th>Fouls</th>';
        echo '<th>Corners</th>';
        echo '<th>Offsides</th>';
        echo '<th>Pos(%)</th>';
        echo '<th>Yellow</th>';
        echo '<th>Red</th>';
        echo '<th>Yellow-Red</th>';
      //  echo '<th>Saves</th>';
        echo '<th>Sub</th>';
      //  echo '<th>Goal Kicks</th>';
      ////  echo '<th>Goal Attempts</th>';
      //  echo '<th>Free Kicks</th>';
     //   echo '<th>Throw Ins</th>';
      ///  echo '<th>Ball Safe</th>';
        echo '<th>Goals</th>';
        echo '<th>Pen</th>';
        echo '<th>Inj</th>';
     //   echo '<th>Tackles</th>';
      //  echo '<th>Attacks</th>';
     //   echo '<th>Dangerous Attacks</th>';
     //   echo '<th>Passes Total</th>';
    //    echo '<th>Passes Accurate</th>';
    //    echo '<th>Passes Percentage</th>';
     //   echo '<th>SHOTS</th>';
     //   echo '<th>SHOG</th>';
    //    echo '<th>SHOB</th>';
      //  echo '<th>SHOFG</th>';
      //  echo '<th>SHINB</th>';
     //   echo '<th>SHOUTB</th>';
        echo '<th>Last Updated</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['stats_id']) . '</td>';
            echo '<td>' . esc_html($row['fixture_id']) . '</td>';
            echo '<td>' . esc_html($row['team_id']) . '</td>';
        //    echo '<td>' . esc_html($row['fouls']) . '</td>';
            echo '<td>' . esc_html($row['corners']) . '</td>';
            echo '<td>' . esc_html($row['offsides']) . '</td>';
            echo '<td>' . esc_html($row['possession_time']) . '%</td>';
            echo '<td>' . esc_html($row['yellow_cards']) . '</td>';
            echo '<td>' . esc_html($row['red_cards']) . '</td>';
            echo '<td>' . (!is_null($row['yellow_red_cards']) ? esc_html($row['yellow_red_cards']) : 'N/A') . '</td>';
        //    echo '<td>' . esc_html($row['saves']) . '</td>';
            echo '<td>' . esc_html($row['substitutions']) . '</td>';
        //    echo '<td>' . esc_html($row['goal_kick']) . '</td>';
        //    echo '<td>' . (!is_null($row['goal_attempts']) ? esc_html($row['goal_attempts']) : 'N/A') . '</td>';
         //   echo '<td>' . esc_html($row['free_kick']) . '</td>';
        //    echo '<td>' . esc_html($row['throw_in']) . '</td>';
       //     echo '<td>' . esc_html($row['ball_safe']) . '</td>';
            echo '<td>' . esc_html($row['goals']) . '</td>';
            echo '<td>' . esc_html($row['penalties']) . '</td>';
            echo '<td>' . esc_html($row['injuries']) . '</td>';
       //     echo '<td>' . esc_html($row['tackles']) . '</td>';
       //     echo '<td>' . esc_html($row['attacks']) . '</td>';
        //    echo '<td>' . esc_html($row['dangerous_attacks']) . '</td>';
         //   echo '<td>' . esc_html($row['passes_total']) . '</td>';
         //   echo '<td>' . (!is_null($row['passes_accurate']) ? esc_html($row['passes_accurate']) : 'N/A') . '</td>';
         //   echo '<td>' . esc_html($row['passes_percentage']) . '%</td>';
         //   echo '<td>' . esc_html($row['shots_total']) . '</td>';
          //  echo '<td>' . esc_html($row['shots_ongoal']) . '</td>';
          //  echo '<td>' . esc_html($row['shots_blocked']) . '</td>';
          //  echo '<td>' . esc_html($row['shots_offgoal']) . '</td>';
         //   echo '<td>' . esc_html($row['shots_insidebox']) . '</td>';
         //   echo '<td>' . esc_html($row['shots_outsidebox']) . '</td>';
            echo '<td>' . esc_html($row['last_updated']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot Team Statistics table with the specified search criteria.</p>';
    }

    echo '</div>';
}


// API Calls Data page callback with pagination
function livefot_display_api_calls() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'livefot_api_calls';

    // Pagination settings
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Get total items
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch data with pagination
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY last_call_time DESC LIMIT %d OFFSET %d", $items_per_page, $offset), ARRAY_A);

    // Base URL for pagination
    $base_url = admin_url('admin.php?page=livefot-api-calls');

    echo '<div class="wrap">';
    echo '<h1>LiveFot API Calls</h1>';

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Endpoint Name</th>';
        echo '<th>Remaining Calls</th>';
        echo '<th>Status</th>';
        echo '<th>Last Call Time</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['endpoint_name']) . '</td>';
            echo '<td>' . esc_html($row['remaining_calls']) . '</td>';
            echo '<td>' . esc_html($row['status']) . '</td>';
            echo '<td>' . esc_html($row['last_call_time']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Display pagination
        livefot_display_pagination($current_page, $total_pages, $base_url);
    } else {
        echo '<p>No data found in the LiveFot API Calls table.</p>';
    }

    echo '</div>';
}  

?>