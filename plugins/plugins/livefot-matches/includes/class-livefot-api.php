<?php
class LiveFot_API {
    private $api_url;
    private $api_key;

    public function __construct() {
        // Fetch the API key from settings
        $this->api_key = get_option('livefot_api_key'); 

        // Fetch the API URL from settings
		$this->api_url = get_option('livefot_api_url');
    }
	

	 public function fetch_and_store_fixtures() {
        $date = date('Y-m-d'); // Fetch fixtures for the current date
        $utc_offset = 0; // Adjust this value if you have timezone settings

        // Fetch matches from the API and store them
        $success = $this->get_matches3($date, $utc_offset);

        if ($success === true) {
            return true;
        } else {
         /*   return __('Error fetching fixtures for the current date. Please check the logs for more details.', 'livefot') . ' ' . $this->get_last_error();*/
			
			 return true;
        }
    }
	
	

	public function livefot_handle_short_fetch_fixtures() {
    // **1. Update Status to 'Running'**
    update_option('livefot_cron_last_run_status_short', 'Running');

    try {
        $utc_offset = 0; // Since we're using server time, adjust if necessary
        $today = new DateTime('now'); // Current server date and time

        // **2. Fetch Fixtures for the Date Range**
        for ($i = -1; $i <= 1; $i++) {
            $date = clone $today; // Clone the DateTime object to modify independently
            $date->modify("$i days"); // Adjust the date by $i days
            $formatted_date = $date->format('Y-m-d'); // Format the date as 'Y-m-d'

            try {
                // **2.1. Fetch Data from API**
                $data = $this->get_matches3($formatted_date, $utc_offset);

                // **2.2. Handle Fetch Success or Error**
                if (!isset($data['error'])) {
                    error_log("LiveFot data fetched successfully for date $formatted_date.");
                } else {
                    error_log("LiveFot fetch failed for date $formatted_date: " . $data['error']);
                    continue; // Skip to the next date on error
                }

            } catch (Exception $e) {
                // **2.3. Handle Exceptions During Fetching**
                error_log("Exception during LiveFot fetch for date $formatted_date: " . $e->getMessage());
                continue; // Skip to the next date in case of an exception
            }
        }

        // **3. Update Status to 'Success'**
        update_option('livefot_cron_last_run_status_short', 'Success');
        return true; // Indicate overall success

    } catch (Exception $e) {
        // **4. Handle Unexpected Exceptions**
        update_option('livefot_cron_last_run_status_short', 'Failed: ' . $e->getMessage()); // Fixed option name
        error_log("Unexpected exception in LiveFot fetch: " . $e->getMessage());
        return 'Failed: ' . $e->getMessage(); // Return error message

    } finally {
        // **5. Update Last Run End Time**
        $utc_end_time = gmdate('Y-m-d H:i:s'); // UTC time
        update_option('livefot_cron_last_run_end_short', $utc_end_time); // Fixed option name

        // **6. Log Completion**
        error_log("LiveFot fetch completed at: $utc_end_time UTC");
    }
}


	
	public function livefot_fetch_and_insert_fixtures() 
{
    // **1. Update Status to 'Running'**
    update_option('livefot_cron_last_run_status', 'Running'); // Correct option

    try {
        $utc_offset = 0; 
        $today = new DateTime('now');

        // **2. Fetch Fixtures for the Date Range (-5 to +7 days)**
        for ($i = -5; $i <= 7; $i++) {
            $date = clone $today;
            $date->modify("$i days");
            $formatted_date = $date->format('Y-m-d');

            try {
                // **2.1. Fetch Data from API**
                $data = $this->get_matches3($formatted_date, $utc_offset);

                // **2.2. After Fetching and Inserting Data**
                if (!isset($data['error'])) {
                    $insert_success = $this->insert_api_data_into_tables($data);

                    if ($insert_success === true) {
                        error_log("LiveFot data inserted successfully for date $formatted_date.");
                    } else {
                        error_log("Failed to insert LiveFot data for date $formatted_date: " . $insert_success);
                        continue;
                    }
                } else {
                    error_log("LiveFot fetch failed for date $formatted_date: " . $data['error']);
                    continue;
                }

            } catch (Exception $e) {
                error_log("Exception during LiveFot fetch for date $formatted_date: " . $e->getMessage());
                continue;
            }
        }

        // **3. Update Status to 'Success'**
        update_option('livefot_cron_last_run_status', 'Success');
        return true; // Indicate overall success

    } catch (Exception $e) {
        // **4. Handle Unexpected Exceptions**
        update_option('livefot_cron_last_run_status', 'Failed: ' . $e->getMessage());
        error_log("Unexpected exception in LiveFot short-range fetch: " . $e->getMessage());
        return 'Failed: ' . $e->getMessage();

    } finally {
        // **5. Update Last Run End Time**
        $utc_end_time = gmdate('Y-m-d H:i:s');
        update_option('livefot_cron_last_run_end', $utc_end_time);

        // **6. Log Completion**
        error_log("LiveFot short-range fetch completed at: $utc_end_time UTC");
    }
}

	
	
	
	

    /**
     * Fetch fixtures for a specific date and store them in the database.
     *
     * @param string $date Date in 'Y-m-d' format.
     * @return bool|string True on success, error message on failure.
     */
  public function fetch_and_store_fixtures_manual($date) {
        // Validate date format
        if (!$this->validate_date($date)) {
            return __('Invalid date format. Please use YYYY-MM-DD.', 'livefot');
        }

        $utc_offset = 0; // Adjust this value if you have timezone settings

        // Fetch matches from the API and store them
        $success = $this->get_matches3($date, $utc_offset);

        if ($success === true) {
            return true;
        } else {
          /*  return __('Error fetching fixtures. Please check the logs for more details.', 'livefot');*/
			 return true;
        }
    }

    /**
     * Validate date format.
     *
     * @param string $date Date string.
     * @return bool
     */
    private function validate_date($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
	
	
	
	
	
	//standing 

	public function fetch_standings_from_api($fixture_id, $league_id, $group_id, $season_id) {
    // Check if API URL is set
    if (empty($this->api_url)) {
        $error_message = "API URL is null or empty. Skipping API call for fetch_standings_from_api.";
        error_log($error_message);
        return ['status' => 'error', 'error' => $error_message];
    }

    // Validate fixture_id
    $fixture_id = intval($fixture_id);
    if ($fixture_id <= 0) {
        $error_message = "Invalid fixture_id provided: {$fixture_id}";
        error_log($error_message);
        return ['status' => 'error', 'error' => $error_message];
    }

    // Build the API URL with dynamic parameters
    $url = add_query_arg([
        'fixture_id' => $fixture_id,
        'league_id'  => intval($league_id),
        'group_id' => intval($group_id ?? 0), //'group_id'   => intval($group_id),
        'season_id'  => intval($season_id),
        'api_key'    => $this->api_key,
        'endpoint'   => 'standings' // Ensure this is correct
    ], $this->api_url . "wp/fixture/{$fixture_id}/standings");
     
    // Make the API request
    $response = wp_remote_get($url);

    // Handle potential request errors
    if (is_wp_error($response)) {
        $error_message = 'Error fetching standings: ' . $response->get_error_message();
        error_log($error_message);
        return ['status' => 'error', 'error' => $error_message];
    }

    // Retrieve and decode the response body
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error_message = 'Invalid JSON response for standings: ' . json_last_error_msg();
        error_log($error_message);
        return ['status' => 'error', 'error' => $error_message];
    }

    // Validate API response structure
    if (!isset($data['status']) || $data['status'] !== 'success' || !isset($data['data'])) {
        $error_message = 'API returned an error or unexpected structure for standings.';
        error_log($error_message);
        return ['status' => 'error', 'error' => $error_message];
    }

    // Prepare additional data for saving
    $endpoint_name = 'standings';
    $lineup = [
        'remainingCalls' => isset($data['remainingCalls']) ? intval($data['remainingCalls']) : null,
        'status'         => isset($data['status']) ? sanitize_text_field($data['status']) : 'N/A',
    ];

    // Call the save_standings_to_db function with all required parameters
    $save_result = $this->save_standings_to_db(
        $data['data'],    // Standings data array
        $league_id,       // League ID
        $group_id,        // Group ID
        $season_id,       // Season ID
        $endpoint_name,   // Endpoint name
        $lineup           // Lineup data (remainingCalls and status)
    );

    // Handle potential errors from saving to DB
    if ($save_result['status'] !== 'success') {
        $error_message = 'Failed to save standings to DB: ' . $save_result['error'];
        error_log($error_message);
        return ['status' => 'error', 'error' => $error_message];
    }

    // Return success status
    return ['status' => 'success'];
}

		
//save standing to DB

	
	
	public function save_standings_to_db($standings_data, $league_id, $group_id, $season_id, $endpoint_name, $lineup) {
    global $wpdb;

    // Define table names
    $standings_table = $wpdb->prefix . 'livefot_standings';
    $api_calls_table = $wpdb->prefix . 'livefot_api_calls';

    // Start a transaction to ensure data integrity
    $wpdb->query('START TRANSACTION');

    try {
        // 1) Delete existing standings for the same league, group, and season
        $wpdb->delete(
            $standings_table,
            [
                'league_id' => $league_id,
                'group_id'  => $group_id,
                'season_id' => $season_id
            ],
            ['%d', '%d', '%d']
        );

        // 2) Process each standing entry for insertion
        foreach ($standings_data as $standing) {
            // Prepare data for insertion/update
            $data = [
                'standing_id'          => isset($standing['Id']) ? intval($standing['Id']) : null,
                'league_id'            => isset($standing['LeagueId']) ? intval($standing['LeagueId']) : $league_id,
                'group_id'             => isset($standing['GroupId']) ? intval($standing['GroupId']) : $group_id,
                'season_id'            => isset($standing['SeasonId']) ? intval($standing['SeasonId']) : $season_id,
                'stage_id'             => isset($standing['StageId']) ? intval($standing['StageId']) : null,
                'round_id'             => isset($standing['RoundId']) ? intval($standing['RoundId']) : null,
                'position'             => isset($standing['Position']) ? intval($standing['Position']) : null,
                'team_id'              => isset($standing['TeamId']) ? intval($standing['TeamId']) : null,
                'team_name'            => isset($standing['TeamName']) ? sanitize_text_field($standing['TeamName']) : '',
                'shortcode'            => isset($standing['ShortCode']) ? sanitize_text_field($standing['ShortCode']) : null,
                'team_logo'            => isset($standing['TeamLogo']) ? esc_url_raw($standing['TeamLogo']) : null,
                'goals'                => isset($standing['Goals']) ? sanitize_text_field($standing['Goals']) : null,
                'goal_diff'            => isset($standing['GoalDiff']) ? intval($standing['GoalDiff']) : null,
                'wins'                 => isset($standing['Wins']) ? intval($standing['Wins']) : null,
                'lost'                 => isset($standing['Lost']) ? intval($standing['Lost']) : null,
                'draws'                => isset($standing['Draws']) ? intval($standing['Draws']) : null,
                'played'               => isset($standing['Played']) ? intval($standing['Played']) : null,
                'points'               => isset($standing['Points']) ? intval($standing['Points']) : null,
                'description'          => isset($standing['Description']) ? sanitize_text_field($standing['Description']) : null,
                'recent_form'          => isset($standing['RecentForm']) ? sanitize_text_field($standing['RecentForm']) : null,
                'standing_rule_id'     => isset($standing['StandingRuleId']) ? intval($standing['StandingRuleId']) : null,
                'result'               => isset($standing['Result']) ? sanitize_text_field($standing['Result']) : null,
                'fairplay_points_lose' => isset($standing['FairplayPointsLose']) ? intval($standing['FairplayPointsLose']) : null,
                'updated_at'           => isset($standing['updated_at']) ? sanitize_text_field($standing['updated_at']) : null,
            ];

            // Define the format for each field
            $format = [
                '%d', // standing_id
                '%d', // league_id
                '%d', // group_id
                '%d', // season_id
                '%d', // stage_id
                '%d', // round_id
                '%d', // position
                '%d', // team_id
                '%s', // team_name
                '%s', // shortcode
                '%s', // team_logo
                '%s', // goals
                '%d', // goal_diff
                '%d', // wins
                '%d', // lost
                '%d', // draws
                '%d', // played
                '%d', // points
                '%s', // description
                '%s', // recent_form
                '%d', // standing_rule_id
                '%s', // result
                '%d', // fairplay_points_lose
                '%s', // updated_at
            ];

            // Insert or update (REPLACE) the standing record
            $wpdb->replace(
                $standings_table,
                $data,
                $format
            );
        }

        // 3) Insert or update API call information
        $api_call_data = [
            'endpoint_name'   => sanitize_text_field($endpoint_name),
            'remaining_calls' => isset($lineup['remainingCalls']) ? intval($lineup['remainingCalls']) : null,
            'status'          => isset($lineup['status']) ? sanitize_text_field($lineup['status']) : 'N/A',
            // 'last_call_time' could be added if needed and your DB structure supports it
        ];

        // Define the format for API call data
        $api_call_format = [
            '%s', // endpoint_name
            '%d', // remaining_calls
            '%s', // status
            // '%s'  // last_call_time (uncomment if needed)
        ];

        $wpdb->replace(
            $api_calls_table,
            $api_call_data,
            $api_call_format
        );

        // 4) Commit the transaction
        $wpdb->query('COMMIT');

    } catch (Exception $e) {
        // Roll back on error
        $wpdb->query('ROLLBACK');
        error_log('Error saving standings to DB: ' . $e->getMessage());
        return ['status' => 'error', 'error' => $e->getMessage()];
    }

    return ['status' => 'success'];
}

	
	
public function get_matches($date, $utc_offset) {
    global $wpdb;

    // 1. Query all fixture rows for the specified date from your local tables.
    //    - We assume here the `livefot_time` table has a column named `starting_at_date`
    //      that we can match against `$date`.
    //    - We also assume that the relationship between fixtures -> leagues, fixtures -> aggregate,
    //      and so forth, is done through foreign keys like `fixtures.aggregate_id = aggregate.aggregate_id`,
    //      `aggregate.league_id = leagues.league_id`, etc.
    //
    //    Adjust the JOIN clauses, column names, and conditions to match your actual schema.

    $fixtures_table    = $wpdb->prefix . 'livefot_fixtures';
    $scores_table      = $wpdb->prefix . 'livefot_scores';
    $time_table        = $wpdb->prefix . 'livefot_time';
    $aggregate_table   = $wpdb->prefix . 'livefot_aggregate';
    $leagues_table     = $wpdb->prefix . 'livefot_leagues';
    $countries_table   = $wpdb->prefix . 'livefot_countries';
    $teams_table       = $wpdb->prefix . 'livefot_teams';
    $referees_table    = $wpdb->prefix . 'livefot_referees';
    $venues_table      = $wpdb->prefix . 'livefot_venues';

    // Example multi-join query to gather all data needed for each fixture row.
    // You may have to tweak the ON clauses or add more LEFT JOINs as needed.
    $sql = "
        SELECT 
            -- League & country
            l.league_id         AS league__id,
            l.name              AS league__name,
            l.is_cup            AS league__is_cup,
            l.logo_path         AS league__logo_path,
            l.current_season_id AS league__current_season_id,
            l.stage_name        AS league__stage_name,
            c.country_id        AS country__id,
            c.name              AS country__name,
            c.image_path        AS country__image_path,

            -- Fixture
            f.fixture_id,
            f.season_id,
            f.group_id,
            f.aggregate_id,
            f.localteam_id,
            f.localteam_name,
            f.visitorteam_id,
            f.visitorteam_name,
            f.stage_type,
            f.localteam_coach_id,
            f.visitorteam_coach_id,
            f.winner_team_id,
            f.commentaries,
            f.leg,
            f.is_placeholder,
            f.referee_id,
            f.venue_id,

            -- Scores
            s.localteam_score,
            s.visitorteam_score,
            s.localteam_pen_score,
            s.visitorteam_pen_score,
            s.ht_score,
            s.ft_score,
            s.et_score,
            s.ps_score,

            -- Time
            t.status                  AS time__status,
            t.starting_at_datetime    AS time__datetime,
            t.starting_at_date        AS time__date,
            t.starting_at_time        AS time__time,
            t.starting_at_timestamp   AS time__timestamp,
            t.starting_at_timezone    AS time__timezone,
            t.minute                  AS time__minute,
            t.second                  AS time__second,
            t.added_time              AS time__added_time,
            t.extra_minute            AS time__extra_minute,
            t.injury_time             AS time__injury_time,
            t.match_period            AS time__match_period,

            -- Aggregate
            ag.league_id      AS ag__league_id,
            ag.season_id      AS ag__season_id,
            ag.stage_id       AS ag__stage_id,
            ag.localteam      AS ag__localteam,
            ag.localteam_id   AS ag__localteam_id,
            ag.visitorteam    AS ag__visitorteam,
            ag.visitorteam_id AS ag__visitorteam_id,
            ag.result         AS ag__result,
            ag.winner         AS ag__winner,
            ag.detail         AS ag__detail,

            -- Referee
            r.referee_id,
            r.common_name AS referee_name,

            -- Venue
            v.venue_id,
            v.name        AS venue_name,

            -- Local Team
            ltm.team_id     AS ltm__id,
            ltm.name        AS ltm__name,
            ltm.shortcode   AS ltm__short_code,
            ltm.twitter     AS ltm__twitter,
            ltm.country_id  AS ltm__country_id,
            ltm.national_team AS ltm__national_team,
            ltm.founded       AS ltm__founded,
            ltm.logo_path     AS ltm__logo_path,
            ltm.venue_id      AS ltm__venue_id,

            -- Visitor Team
            vtm.team_id      AS vtm__id,
            vtm.name         AS vtm__name,
            vtm.shortcode    AS vtm__short_code,
            vtm.twitter      AS vtm__twitter,
            vtm.country_id   AS vtm__country_id,
            vtm.national_team AS vtm__national_team,
            vtm.founded       AS vtm__founded,
            vtm.logo_path     AS vtm__logo_path,
            vtm.venue_id      AS vtm__venue_id

        FROM   {$fixtures_table} f
        LEFT JOIN {$scores_table} s    ON f.fixture_id = s.fixture_id
        LEFT JOIN {$time_table} t      ON f.fixture_id = t.fixture_id
        LEFT JOIN {$aggregate_table} ag ON f.aggregate_id = ag.aggregate_id

        -- Link from aggregate -> league
        LEFT JOIN {$leagues_table} l   ON f.league_id  = l.league_id
        LEFT JOIN {$countries_table} c ON l.country_id  = c.country_id

        -- Teams
        LEFT JOIN {$teams_table} ltm   ON f.localteam_id  = ltm.team_id
        LEFT JOIN {$teams_table} vtm   ON f.visitorteam_id = vtm.team_id

        -- Referee
        LEFT JOIN {$referees_table} r  ON f.referee_id = r.referee_id

        -- Venue
        LEFT JOIN {$venues_table} v    ON f.venue_id = v.venue_id

        WHERE  t.starting_at_date = %s
        ORDER BY t.starting_at_time ASC , f.fixture_id ASC
    ";

    // Prepare the SQL query (helps prevent SQL injection).
    $results = $wpdb->get_results(
        $wpdb->prepare($sql, $date),
        ARRAY_A
    );

    if (empty($results)) {
        // Return an empty structure or handle "no matches" scenario
        return [
            'meta' => [
                'pagination' => [
                    'total'        => 0,
                    'count'        => 0,
                    'per_page'     => 0,
                    'current_page' => 1,
                    'total_pages'  => 0,
                    'links'        => null,
                ],
            ],
            'data' => [],
        ];
    }

    // 2. Build the data structure: group fixtures by league.
    //    We want: 
    //    {
    //      "meta": { ...pagination info... },
    //      "data": [
    //          {
    //            "league_info": { ... },
    //            "fixtures": [ ...list of fixtures... ]
    //          },
    //          ...
    //      ]
    //    }

    // An associative array grouping by `league_id`
    $leagues_indexed = [];

    foreach ($results as $row) {
        $league_id = (int) $row['league__id'];


		 // Ensure the league data is initialized correctly
     if (!isset($leagues_indexed[$league_id])) {
        $leagues_indexed[$league_id] = [
            'league_info' => [
                'id'                => $league_id,
                'name'              => $row['league__name'] ?? null,
                'is_cup'            => isset($row['league__is_cup']) ? (bool) $row['league__is_cup'] : null,
                'logo_path'         => $row['league__logo_path'] ?? null,
                'current_season_id' => isset($row['league__current_season_id']) ? (int) $row['league__current_season_id'] : null,
                'stage_name'        => $row['league__stage_name'] ?? null,
                'country'           => [
                    'id'         => isset($row['country__id']) ? (int) $row['country__id'] : null,
                    'name'       => $row['country__name'] ?? null,
                    'image_path' => $row['country__image_path'] ?? null,
                ],
            ],
            'fixtures' => [],
        ];
    }

		

        // Build out the fixture structure
        $fixture_id = (int) $row['fixture_id'];

        // Scores
        $scores = [
            'fixture_id'           => $fixture_id,
            'localteam_score'      => isset($row['localteam_score']) ? (int) $row['localteam_score'] : null,
            'visitorteam_score'    => isset($row['visitorteam_score']) ? (int) $row['visitorteam_score'] : null,
            'localteam_pen_score'  => isset($row['localteam_pen_score']) ? (int) $row['localteam_pen_score'] : null,
            'visitorteam_pen_score'=> isset($row['visitorteam_pen_score']) ? (int) $row['visitorteam_pen_score'] : null,
            'ht_score'             => $row['ht_score'],
            'ft_score'             => $row['ft_score'],
            'et_score'             => $row['et_score'],
            'ps_score'             => $row['ps_score'],
        ];

        // Time
        $time = [
            'fixture_id' => $fixture_id,
            'status'     => $row['time__status'],
            'starting_at' => [
                'fixture_id' => $fixture_id,
                'date_time'  => $row['time__datetime'],
                'date'       => $row['time__date'],
                'time'       => $row['time__time'],
                'timestamp'  => isset($row['time__timestamp']) ? (int) $row['time__timestamp'] : null,
                'timezone'   => $row['time__timezone'],
            ],
            'minute'       => isset($row['time__minute']) ? (int) $row['time__minute'] : null,
            'second'       => isset($row['time__second']) ? (int) $row['time__second'] : null,
            'added_time'   => isset($row['time__added_time']) ? (int) $row['time__added_time'] : null,
            'extra_minute' => isset($row['time__extra_minute']) ? (int) $row['time__extra_minute'] : null,
            'injury_time'  => isset($row['time__injury_time']) ? (int) $row['time__injury_time'] : null,
            'matchPeriod'  => $row['time__match_period'],
        ];

        // Local Team
        $localTeam = [
            'id'            => isset($row['ltm__id']) ? (int) $row['ltm__id'] : null,
            'legacy_id'     => null, // only if you have it
            'name'          => $row['ltm__name'],
            'short_code'    => $row['ltm__short_code'],
            'twitter'       => $row['ltm__twitter'],
            'country_id'    => isset($row['ltm__country_id']) ? (int) $row['ltm__country_id'] : null,
            'national_team' => (bool) $row['ltm__national_team'],
            'founded'       => isset($row['ltm__founded']) ? (int) $row['ltm__founded'] : null,
            'logo_path'     => $row['ltm__logo_path'],
            'venue_id'      => isset($row['ltm__venue_id']) ? (int) $row['ltm__venue_id'] : null,
            'current_season_id' => null,  // populate if you store it in your DB
            'is_placeholder'    => false, // placeholder field if you need it
            'logo_url'          => null,  // if you store a different logo URL
        ];

        // Visitor Team
        $visitorTeam = [
            'id'            => isset($row['vtm__id']) ? (int) $row['vtm__id'] : null,
            'legacy_id'     => null,
            'name'          => $row['vtm__name'],
            'short_code'    => $row['vtm__short_code'],
            'twitter'       => $row['vtm__twitter'],
            'country_id'    => isset($row['vtm__country_id']) ? (int) $row['vtm__country_id'] : null,
            'national_team' => (bool) $row['vtm__national_team'],
            'founded'       => isset($row['vtm__founded']) ? (int) $row['vtm__founded'] : null,
            'logo_path'     => $row['vtm__logo_path'],
            'venue_id'      => isset($row['vtm__venue_id']) ? (int) $row['vtm__venue_id'] : null,
            'current_season_id' => null,
            'is_placeholder'    => false,
            'logo_url'          => null,
        ];

        // Referee
        $referee = [
            'id'          => isset($row['referee_id']) ? (int) $row['referee_id'] : null,
            'common_name' => $row['referee_name'],
        ];

        // Venue
        $venue = [
            'id'   => isset($row['venue_id']) ? (int) $row['venue_id'] : null,
            'name' => $row['venue_name'],
        ];

        // Aggregate
        $aggregate = null;
        if (!empty($row['aggregate_id'])) {
            $aggregate = [
                'id'            => (int) $row['aggregate_id'],
                'league_id'     => (int) $row['ag__league_id'],
                'season_id'     => (int) $row['ag__season_id'],
                'stage_id'      => isset($row['ag__stage_id']) ? (int) $row['ag__stage_id'] : null,
                'localteam'     => $row['ag__localteam'],
                'localteam_id'  => isset($row['ag__localteam_id']) ? (int) $row['ag__localteam_id'] : null,
                'visitorteam'   => $row['ag__visitorteam'],
                'visitorteam_id'=> isset($row['ag__visitorteam_id']) ? (int) $row['ag__visitorteam_id'] : null,
                'result'        => $row['ag__result'],
                'winner'        => isset($row['ag__winner']) ? (int) $row['ag__winner'] : null,
                'detail'        => $row['ag__detail'],
            ];
        }

        // Build the single fixture array
        $fixture_data = [
            'id'                   => $fixture_id,
            'season_id'            => (int) $row['season_id'],
            'group_id'             => $row['group_id'] ? (int) $row['group_id'] : null,
            'aggregate_id'         => $row['aggregate_id'] ? (int) $row['aggregate_id'] : null,
            'localteam_id'         => (int) $row['localteam_id'],
            'visitorteam_id'       => (int) $row['visitorteam_id'],
            'stage_type'           => $row['stage_type'],
            'localteam_coach_id'   => $row['localteam_coach_id'] ? (int) $row['localteam_coach_id'] : null,
            'visitorteam_coach_id' => $row['visitorteam_coach_id'] ? (int) $row['visitorteam_coach_id'] : null,
            'winner_team_id'       => $row['winner_team_id'] ? (int) $row['winner_team_id'] : null,
            'commentaries'         => (bool) $row['commentaries'],
            'leg'                  => $row['leg'],
            'is_placeholder'       => (bool) $row['is_placeholder'],

            'scores'     => $scores,
            'time'       => $time,
            'localTeam'  => $localTeam,
            'visitorTeam'=> $visitorTeam,

            // If you store cards or events in separate tables, you might need additional queries or left joins:
            'cards'      => [],
            'referee'    => $referee,
            'venue'      => $venue,
            'aggregate'  => $aggregate,
            'events'     => [],  // same note as for cards
        ];

        $leagues_indexed[$league_id]['fixtures'][] = $fixture_data;
    }

    // Build final return structure
    // Basic pagination metadata: we’re not actually paginating in this example, 
    // but you can do so if needed. For now, total = count of fixtures, etc.
    $all_fixture_count = 0;
    foreach ($leagues_indexed as $league_data) {
        $all_fixture_count += count($league_data['fixtures']);
    }

    $response = [
        'meta' => [
            'pagination' => [
                'total'        => $all_fixture_count,
                'count'        => $all_fixture_count,
                'per_page'     => $all_fixture_count, // no real pagination here
                'current_page' => 1,
                'total_pages'  => 1,
                'links'        => null,
            ],
        ],
        'data' => array_values($leagues_indexed), // convert from assoc array to indexed
    ];

    return $response;
}
	
	
	/*****************************************************************live*****************************/
	
	
	public function get_live_matches() {
    global $wpdb;

    // 1) Define the live statuses
    $live_statuses = ['LIVE', 'HT', 'INT', 'ET', 'BREAK', 'PEN_LIVE', 'PEN'];

    // 2) Build the IN() placeholders, one for each status
    $placeholders = implode(',', array_fill(0, count($live_statuses), '%s'));

    // 3) Define your table names
    $fixtures_table    = $wpdb->prefix . 'livefot_fixtures';
    $scores_table      = $wpdb->prefix . 'livefot_scores';
    $time_table        = $wpdb->prefix . 'livefot_time';
    $aggregate_table   = $wpdb->prefix . 'livefot_aggregate';
    $leagues_table     = $wpdb->prefix . 'livefot_leagues';
    $countries_table   = $wpdb->prefix . 'livefot_countries';
    $teams_table       = $wpdb->prefix . 'livefot_teams';
    $referees_table    = $wpdb->prefix . 'livefot_referees';
    $venues_table      = $wpdb->prefix . 'livefot_venues';
    $team_stats_table  = $wpdb->prefix . 'livefot_team_stats';

    // 4) Build the SQL (matching the structure in get_matches_from_db but using status IN ...)
    $sql = "
        SELECT 
            -- League & Country
            l.league_id AS league__id,
            l.name AS league__name,
            l.is_cup AS league__is_cup,
            l.logo_path AS league__logo_path,
            l.current_season_id AS league__current_season_id,
            l.stage_name AS league__stage_name,
            c.country_id AS country__id,
            c.name AS country__name,
            c.image_path AS country__image_path,

            -- Fixture
            f.fixture_id,
            f.season_id,
            f.group_id,
            f.aggregate_id,
            f.localteam_id,
            f.localteam_name,
            f.visitorteam_id,
            f.visitorteam_name,
            f.stage_type,
            f.localteam_coach_id,
            f.visitorteam_coach_id,
            f.winner_team_id,
            f.commentaries,
            f.leg,
            f.is_placeholder,
            f.referee_id,
            f.venue_id,

            -- Scores
            s.localteam_score,
            s.visitorteam_score,
            s.localteam_pen_score,
            s.visitorteam_pen_score,
            s.ht_score,
            s.ft_score,
            s.et_score,
            s.ps_score,

            -- Time
            t.status AS time__status,
            t.starting_at_datetime AS time__datetime,
            t.starting_at_date AS time__date,
            t.starting_at_time AS time__time,
            UNIX_TIMESTAMP(t.starting_at_datetime) AS time__timestamp,
            t.starting_at_timezone AS time__timezone,
            t.minute AS time__minute,
            t.second AS time__second,
            t.added_time AS time__added_time,
            t.extra_minute AS time__extra_minute,
            t.injury_time AS time__injury_time,
            t.match_period AS time__match_period,

            -- Aggregate
            ag.league_id AS ag__league_id,
            ag.season_id AS ag__season_id,
            ag.stage_id AS ag__stage_id,
            ag.localteam AS ag__localteam,
            ag.localteam_id AS ag__localteam_id,
            ag.visitorteam AS ag__visitorteam,
            ag.visitorteam_id AS ag__visitorteam_id,
            ag.result AS ag__result,
            ag.winner AS ag__winner,
            ag.detail AS ag__detail,

            -- Referee
            r.referee_id,
            r.common_name AS referee_name,

            -- Venue
            v.venue_id,
            v.name AS venue_name,

            -- Local Team
            ltm.team_id AS ltm__id,
            ltm.name AS ltm__name,
            ltm.shortcode AS ltm__short_code,
            ltm.twitter AS ltm__twitter,
            ltm.country_id AS ltm__country_id,
            ltm.national_team AS ltm__national_team,
            ltm.founded AS ltm__founded,
            ltm.logo_path AS ltm__logo_path,
            ltm.venue_id AS ltm__venue_id,

            -- Visitor Team
            vtm.team_id AS vtm__id,
            vtm.name AS vtm__name,
            vtm.shortcode AS vtm__short_code,
            vtm.twitter AS vtm__twitter,
            vtm.country_id AS vtm__country_id,
            vtm.national_team AS vtm__national_team,
            vtm.founded AS vtm__founded,
            vtm.logo_path AS vtm__logo_path,
            vtm.venue_id AS vtm__venue_id,

            -- Red cards from team stats
            ts_local.red_cards AS red_cards_local,
            ts_visitor.red_cards AS red_cards_visitor

        FROM {$fixtures_table} f
        LEFT JOIN {$scores_table} s         ON f.fixture_id = s.fixture_id
        LEFT JOIN {$time_table} t           ON f.fixture_id = t.fixture_id
        LEFT JOIN {$aggregate_table} ag     ON f.aggregate_id = ag.aggregate_id
        LEFT JOIN {$leagues_table} l        ON f.league_id  = l.league_id
        LEFT JOIN {$countries_table} c      ON l.country_id = c.country_id
        LEFT JOIN {$teams_table} ltm        ON f.localteam_id  = ltm.team_id
        LEFT JOIN {$teams_table} vtm        ON f.visitorteam_id = vtm.team_id
        LEFT JOIN {$referees_table} r       ON f.referee_id = r.referee_id
        LEFT JOIN {$venues_table} v         ON f.venue_id  = v.venue_id

        LEFT JOIN {$team_stats_table} ts_local
            ON ts_local.fixture_id = f.fixture_id
            AND ts_local.team_id   = ltm.team_id

        LEFT JOIN {$team_stats_table} ts_visitor
            ON ts_visitor.fixture_id = f.fixture_id
            AND ts_visitor.team_id   = vtm.team_id

        WHERE t.status IN ($placeholders)
        ORDER BY t.starting_at_time ASC , f.fixture_id ASC
    ";

    // 5) Execute query using placeholders for the statuses
    $results = $wpdb->get_results(
        $wpdb->prepare($sql, ...$live_statuses),
        ARRAY_A
    );

    // If no matching fixtures, return empty structure
    if (empty($results)) {
        return [
            'meta' => [
                'pagination' => [
                    'total'        => 0,
                    'count'        => 0,
                    'per_page'     => 0,
                    'current_page' => 1,
                    'total_pages'  => 0,
                    'links'        => null,
                ],
            ],
            'data' => [],
        ];
    }

    // 6) Group the results by league
    $leagues_indexed = [];

    foreach ($results as $row) {
        $league_id = (int) $row['league__id'];

        // Initialize league array if not present
        if (!isset($leagues_indexed[$league_id])) {
            $leagues_indexed[$league_id] = [
                'league_info' => [
                    'id'                => $league_id,
                    'name'              => $row['league__name'] ?? null,
                    'is_cup'            => isset($row['league__is_cup']) ? (bool) $row['league__is_cup'] : null,
                    'logo_path'         => $row['league__logo_path'] ?? null,
                    'current_season_id' => isset($row['league__current_season_id']) ? (int) $row['league__current_season_id'] : null,
                    'stage_name'        => $row['league__stage_name'] ?? null,
                    'country'           => [
                        'id'         => isset($row['country__id']) ? (int) $row['country__id'] : null,
                        'name'       => $row['country__name'] ?? null,
                        'image_path' => $row['country__image_path'] ?? null,
                    ],
                ],
                'fixtures' => [],
            ];
        }

        // Build fixture structure
        $fixture_id = (int) $row['fixture_id'];

        // Scores
        $scores = [
            'localteam_score'       => isset($row['localteam_score']) ? (int) $row['localteam_score'] : null,
            'visitorteam_score'     => isset($row['visitorteam_score']) ? (int) $row['visitorteam_score'] : null,
            'localteam_pen_score'   => isset($row['localteam_pen_score']) ? (int) $row['localteam_pen_score'] : null,
            'visitorteam_pen_score' => isset($row['visitorteam_pen_score']) ? (int) $row['visitorteam_pen_score'] : null,
            'ht_score'              => $row['ht_score'],
            'ft_score'              => $row['ft_score'],
            'et_score'              => $row['et_score'],
            'ps_score'              => $row['ps_score'],
        ];

        // Time
        $time = [
            'status'      => $row['time__status'],
            'starting_at' => [
                'date_time'  => $row['time__datetime'],
                'date'       => $row['time__date'],
                'time'       => $row['time__time'],
                'timestamp'  => isset($row['time__timestamp']) ? (int) $row['time__timestamp'] : null,
                'timezone'   => $row['time__timezone'],
            ],
            'minute'       => isset($row['time__minute']) ? (int) $row['time__minute'] : null,
            'second'       => isset($row['time__second']) ? (int) $row['time__second'] : null,
            'added_time'   => isset($row['time__added_time']) ? (int) $row['time__added_time'] : null,
            'extra_minute' => isset($row['time__extra_minute']) ? (int) $row['time__extra_minute'] : null,
            'injury_time'  => isset($row['time__injury_time']) ? (int) $row['time__injury_time'] : null,
            'match_period' => $row['time__match_period'],
        ];

        // Local Team
        $localTeam = [
            'id'            => isset($row['ltm__id']) ? (int) $row['ltm__id'] : null,
            'name'          => $row['ltm__name'],
            'short_code'    => $row['ltm__short_code'],
            'twitter'       => $row['ltm__twitter'],
            'country_id'    => isset($row['ltm__country_id']) ? (int) $row['ltm__country_id'] : null,
            'national_team' => isset($row['ltm__national_team']) ? (bool) $row['ltm__national_team'] : false,
            'founded'       => isset($row['ltm__founded']) ? (int) $row['ltm__founded'] : null,
            'logo_path'     => $row['ltm__logo_path'],
            'venue_id'      => isset($row['ltm__venue_id']) ? (int) $row['ltm__venue_id'] : null,
            'legacy_id'         => null,
            'current_season_id' => null,
            'is_placeholder'    => false,
            'logo_url'          => null,
        ];

        // Visitor Team
        $visitorTeam = [
            'id'            => isset($row['vtm__id']) ? (int) $row['vtm__id'] : null,
            'name'          => $row['vtm__name'],
            'short_code'    => $row['vtm__short_code'],
            'twitter'       => $row['vtm__twitter'],
            'country_id'    => isset($row['vtm__country_id']) ? (int) $row['vtm__country_id'] : null,
            'national_team' => isset($row['vtm__national_team']) ? (bool) $row['vtm__national_team'] : false,
            'founded'       => isset($row['vtm__founded']) ? (int) $row['vtm__founded'] : null,
            'logo_path'     => $row['vtm__logo_path'],
            'venue_id'      => isset($row['vtm__venue_id']) ? (int) $row['vtm__venue_id'] : null,
            'legacy_id'         => null,
            'current_season_id' => null,
            'is_placeholder'    => false,
            'logo_url'          => null,
        ];

        // Referee
        $referee = [
            'id'          => isset($row['referee_id']) ? (int) $row['referee_id'] : null,
            'common_name' => $row['referee_name'],
        ];

        // Venue
        $venue = [
            'id'   => isset($row['venue_id']) ? (int) $row['venue_id'] : null,
            'name' => $row['venue_name'],
        ];

        // Aggregate
        $aggregate = null;
        if (!empty($row['aggregate_id'])) {
            $aggregate = [
                'id'             => (int) $row['aggregate_id'],
                'league_id'      => isset($row['ag__league_id']) ? (int) $row['ag__league_id'] : null,
                'season_id'      => isset($row['ag__season_id']) ? (int) $row['ag__season_id'] : null,
                'stage_id'       => isset($row['ag__stage_id']) ? (int) $row['ag__stage_id'] : null,
                'localteam'      => $row['ag__localteam'],
                'localteam_id'   => isset($row['ag__localteam_id']) ? (int) $row['ag__localteam_id'] : null,
                'visitorteam'    => $row['ag__visitorteam'],
                'visitorteam_id' => isset($row['ag__visitorteam_id']) ? (int) $row['ag__visitorteam_id'] : null,
                'result'         => $row['ag__result'],
                'winner'         => isset($row['ag__winner']) ? (int) $row['ag__winner'] : null,
                'detail'         => $row['ag__detail'],
            ];
        }

        // Red Cards logic
        $red_cards = [
            [
                'team_id' => isset($row['localteam_id']) ? (int) $row['localteam_id'] : null,
                'count'   => isset($row['red_cards_local']) ? (int) $row['red_cards_local'] : 0,
            ],
            [
                'team_id' => isset($row['visitorteam_id']) ? (int) $row['visitorteam_id'] : null,
                'count'   => isset($row['red_cards_visitor']) ? (int) $row['red_cards_visitor'] : 0,
            ],
        ];

        // Single fixture array
        $fixture_data = [
            'id'                   => $fixture_id,
            'season_id'            => isset($row['season_id']) ? (int) $row['season_id'] : null,
            'group_id'             => isset($row['group_id']) ? (int) $row['group_id'] : null,
            'aggregate_id'         => isset($row['aggregate_id']) ? (int) $row['aggregate_id'] : null,
            'localteam_id'         => isset($row['localteam_id']) ? (int) $row['localteam_id'] : null,
            'visitorteam_id'       => isset($row['visitorteam_id']) ? (int) $row['visitorteam_id'] : null,
            'stage_type'           => $row['stage_type'],
            'localteam_coach_id'   => isset($row['localteam_coach_id']) ? (int) $row['localteam_coach_id'] : null,
            'visitorteam_coach_id' => isset($row['visitorteam_coach_id']) ? (int) $row['visitorteam_coach_id'] : null,
            'winner_team_id'       => isset($row['winner_team_id']) ? (int) $row['winner_team_id'] : null,
            'commentaries'         => isset($row['commentaries']) ? (bool) $row['commentaries'] : false,
            'leg'                  => $row['leg'],
            'is_placeholder'       => isset($row['is_placeholder']) ? (bool) $row['is_placeholder'] : false,

            'scores'      => $scores,
            'time'        => $time,
            'localTeam'   => $localTeam,
            'visitorTeam' => $visitorTeam,
            'cards'       => [], // placeholder
            'referee'     => $referee,
            'venue'       => $venue,
            'aggregate'   => $aggregate,
            'events'      => [], // placeholder
            'red_cards'   => $red_cards,
        ];

        $leagues_indexed[$league_id]['fixtures'][] = $fixture_data;
    }

    // 7) Build the final return structure (no pagination used)
    $all_fixture_count = 0;
    foreach ($leagues_indexed as $league_data) {
        $all_fixture_count += count($league_data['fixtures']);
    }

    $response = [
        'meta' => [
            'pagination' => [
                'total'        => $all_fixture_count,
                'count'        => $all_fixture_count,
                'per_page'     => $all_fixture_count,
                'current_page' => 1,
                'total_pages'  => 1,
                'links'        => null,
            ],
        ],
        'data' => array_values($leagues_indexed), // convert to numeric array
    ];

    //return $response;   jk
    //
        wp_send_json_success($response['data']);

}

	


	
	public function get_matches_live($date, $utc_offset) {
    global $wpdb;

    // Define table names
    $fixtures_table    = $wpdb->prefix . 'livefot_fixtures';
    $scores_table      = $wpdb->prefix . 'livefot_scores';
    $time_table        = $wpdb->prefix . 'livefot_time';
    $aggregate_table   = $wpdb->prefix . 'livefot_aggregate';
    $leagues_table     = $wpdb->prefix . 'livefot_leagues';
    $countries_table   = $wpdb->prefix . 'livefot_countries';
    $teams_table       = $wpdb->prefix . 'livefot_teams';
    $referees_table    = $wpdb->prefix . 'livefot_referees';
    $venues_table      = $wpdb->prefix . 'livefot_venues';
    $api_calls_table   = $wpdb->prefix . 'api_calls'; // Define the API calls tracking table

   

     // Define the endpoint name
    $endpoint = 'matches';

    // Retrieve the interval for the 'matches' endpoint (default to 300 seconds if not set)
    $matches_interval_seconds = intval(get_option('livefot_interval_matches', 300));

 
     $now = current_time('timestamp');
    $today = date('Y-m-d', $now);
    $yesterday = date('Y-m-d', strtotime('-1 day', $now));
		
		
		
		
		   // Calculate current UTC timestamp
    $utc_now_timestamp = time();

    // Apply UTC offset (since $utc_offset is in minutes)
    $adjusted_timestamp = $utc_now_timestamp + ($utc_offset * 60);

    // Get the UTC date and adjusted local date
    $utc_date = gmdate('Y-m-d', $utc_now_timestamp);
    $local_date = gmdate('Y-m-d', $adjusted_timestamp);

    // Compare local date with UTC date and adjust $date accordingly
    if ($local_date > $utc_date) {
        // Local date is ahead of UTC date
        $date = date('Y-m-d', strtotime($date . ' +1 day'));
    } elseif ($local_date < $utc_date) {
        // Local date is behind UTC date
        $date = date('Y-m-d', strtotime($date . ' -1 day'));
    }
		elseif ($local_date === $utc_date) {
        // Local date is behind UTC date
        $date = date('Y-m-d', strtotime($date . ' +0 day'));
    }
    // Else, $date remains unchanged


    // For today's matches
    if ($date === $today) {
        $times = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    MIN(starting_at_time) AS first_match_time, 
                    MAX(starting_at_time) AS last_match_time, 
                    MAX(last_updated) AS last_updated 
                FROM {$time_table} 
                WHERE starting_at_date = %s 
                HAVING MAX(last_updated) < NOW() - INTERVAL %d SECOND
				AND NOW() >= MIN(starting_at_time) - INTERVAL 2 MINUTE 
                AND NOW() < MAX(starting_at_time) + INTERVAL 3 HOUR",
                $date,
                $matches_interval_seconds
            )
        );

        if ($times) {
            return $this->get_matches2($date, $utc_offset);
        }
    }

    // For yesterday's matches
    if ($date === $yesterday) {
        $times = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    MAX(starting_at_time) AS first_match_time, 
                    MAX(starting_at_time) AS last_match_time, 
                    MAX(last_updated) AS last_updated 
                FROM {$time_table} 
                WHERE starting_at_date = %s 
                HAVING MAX(last_updated) < NOW() - INTERVAL %d SECOND 
				AND NOW() >= MAX(starting_at_time) - INTERVAL 2 MINUTE 
                AND NOW() < MAX(starting_at_time) + INTERVAL 3 HOUR",
                $yesterday,
                $matches_interval_seconds
            )
        );

        if ($times) {
            return $this->get_matches2($date, $utc_offset);
        }
    }
		
		return $this->get_matches_from_db($date, $utc_offset);
    

}
	

	
	
	public function get_matches_from_db($date, $utc_offset) {
    global $wpdb;

    // Define table names
    $fixtures_table    = $wpdb->prefix . 'livefot_fixtures';
    $scores_table      = $wpdb->prefix . 'livefot_scores';
    $time_table        = $wpdb->prefix . 'livefot_time';
    $aggregate_table   = $wpdb->prefix . 'livefot_aggregate';
    $leagues_table     = $wpdb->prefix . 'livefot_leagues';
    $countries_table   = $wpdb->prefix . 'livefot_countries';
    $teams_table       = $wpdb->prefix . 'livefot_teams';
    $referees_table    = $wpdb->prefix . 'livefot_referees';
    $venues_table      = $wpdb->prefix . 'livefot_venues';
    $events_table      = $wpdb->prefix . 'livefot_events'; // Added for events
	$team_stats_table  = $wpdb->prefix . 'livefot_team_stats';

    // SQL query to fetch match data with red cards count
   // SQL query to fetch match data with red cards count from team stats
$sql = "
    SELECT 
        l.league_id AS league__id,
        l.name AS league__name,
        l.is_cup AS league__is_cup,
        l.logo_path AS league__logo_path,
        l.current_season_id AS league__current_season_id,
        l.stage_name AS league__stage_name,
        c.country_id AS country__id,
        c.name AS country__name,
        c.image_path AS country__image_path,

        f.fixture_id,
        f.season_id,
        f.group_id,
        f.aggregate_id,
        f.localteam_id,
        f.localteam_name,
        f.visitorteam_id,
        f.visitorteam_name,
        f.stage_type,
        f.localteam_coach_id,
        f.visitorteam_coach_id,
        f.winner_team_id,
        f.commentaries,
        f.leg,
        f.is_placeholder,
        f.referee_id,
        f.venue_id,

        s.localteam_score,
        s.visitorteam_score,
        s.localteam_pen_score,
        s.visitorteam_pen_score,
        s.ht_score,
        s.ft_score,
        s.et_score,
        s.ps_score,

        t.status AS time__status,
        t.starting_at_datetime AS time__datetime,
        t.starting_at_date AS time__date,
        t.starting_at_time AS time__time,
        UNIX_TIMESTAMP(t.starting_at_datetime) AS time__timestamp,
        t.starting_at_timezone AS time__timezone,
        t.minute AS time__minute,
        t.second AS time__second,
        t.added_time AS time__added_time,
        t.extra_minute AS time__extra_minute,
        t.injury_time AS time__injury_time,
        t.match_period AS time__match_period,

        ag.league_id AS ag__league_id,
        ag.season_id AS ag__season_id,
        ag.stage_id AS ag__stage_id,
        ag.localteam AS ag__localteam,
        ag.localteam_id AS ag__localteam_id,
        ag.visitorteam AS ag__visitorteam,
        ag.visitorteam_id AS ag__visitorteam_id,
        ag.result AS ag__result,
        ag.winner AS ag__winner,
        ag.detail AS ag__detail,

        r.referee_id,
        r.common_name AS referee_name,

        v.venue_id,
        v.name AS venue_name,

        ltm.team_id AS ltm__id,
        ltm.name AS ltm__name,
        ltm.shortcode AS ltm__short_code,
        ltm.twitter AS ltm__twitter,
        ltm.country_id AS ltm__country_id,
        ltm.national_team AS ltm__national_team,
        ltm.founded AS ltm__founded,
        ltm.logo_path AS ltm__logo_path,
        ltm.venue_id AS ltm__venue_id,

        vtm.team_id AS vtm__id,
        vtm.name AS vtm__name,
        vtm.shortcode AS vtm__short_code,
        vtm.twitter AS vtm__twitter,
        vtm.country_id AS vtm__country_id,
        vtm.national_team AS vtm__national_team,
        vtm.founded AS vtm__founded,
        vtm.logo_path AS vtm__logo_path,
        vtm.venue_id AS vtm__venue_id,

        -- Join to get red cards from team stats
        ts_local.red_cards AS red_cards_local,
        ts_visitor.red_cards AS red_cards_visitor

    FROM {$fixtures_table} f
    LEFT JOIN {$scores_table} s ON f.fixture_id = s.fixture_id
    LEFT JOIN {$time_table} t ON f.fixture_id = t.fixture_id
    LEFT JOIN {$aggregate_table} ag ON f.aggregate_id = ag.aggregate_id
    LEFT JOIN {$leagues_table} l ON f.league_id = l.league_id
    LEFT JOIN {$countries_table} c ON l.country_id = c.country_id
    LEFT JOIN {$teams_table} ltm ON f.localteam_id = ltm.team_id
    LEFT JOIN {$teams_table} vtm ON f.visitorteam_id = vtm.team_id
    LEFT JOIN {$referees_table} r ON f.referee_id = r.referee_id
    LEFT JOIN {$venues_table} v ON f.venue_id = v.venue_id

    -- Join for local team stats
    LEFT JOIN {$team_stats_table} ts_local 
        ON ts_local.fixture_id = f.fixture_id 
        AND ts_local.team_id = ltm.team_id

    -- Join for visitor team stats
    LEFT JOIN {$team_stats_table} ts_visitor 
        ON ts_visitor.fixture_id = f.fixture_id 
        AND ts_visitor.team_id = vtm.team_id

    WHERE t.starting_at_date BETWEEN DATE_SUB(%s, INTERVAL 1 DAY) AND DATE_ADD(%s, INTERVAL 1 DAY)
    ORDER BY t.starting_at_time ASC , f.fixture_id ASC
";


    // Prepare the SQL query (helps prevent SQL injection).
    $results = $wpdb->get_results(
        $wpdb->prepare($sql, $date, $date),
        ARRAY_A
    );

    if (empty($results)) {
        // Return an empty structure or handle "no matches" scenario
        return [
            'meta' => [
                'pagination' => [
                    'total'        => 0,
                    'count'        => 0,
                    'per_page'     => 0,
                    'current_page' => 1,
                    'total_pages'  => 0,
                    'links'        => null,
                ],
            ],
            'data' => [],
        ];
    }

    // 2. Build the data structure: group fixtures by league.
    //    We want: 
    //    {
    //      "meta": { ...pagination info... },
    //      "data": [
    //          {
    //            "league_info": { ... },
    //            "fixtures": [ ...list of fixtures... ]
    //          },
    //          ...
    //      ]
    //    }

    // An associative array grouping by `league_id`
    $leagues_indexed = [];

    foreach ($results as $row) {
        $league_id = (int) $row['league__id'];

        // Ensure the league data is initialized correctly
        if (!isset($leagues_indexed[$league_id])) {
            $leagues_indexed[$league_id] = [
                'league_info' => [
                    'id'                => $league_id,
                    'name'              => $row['league__name'] ?? null,
                    'is_cup'            => isset($row['league__is_cup']) ? (bool) $row['league__is_cup'] : null,
                    'logo_path'         => $row['league__logo_path'] ?? null,
                    'current_season_id' => isset($row['league__current_season_id']) ? (int) $row['league__current_season_id'] : null,
                    'stage_name'        => $row['league__stage_name'] ?? null,
                    'country'           => [
                        'id'         => isset($row['country__id']) ? (int) $row['country__id'] : null,
                        'name'       => $row['country__name'] ?? null,
                        'image_path' => $row['country__image_path'] ?? null,
                    ],
                ],
                'fixtures' => [],
            ];
        }

        // Build out the fixture structure
        $fixture_id = (int) $row['fixture_id'];

        // Scores
        $scores = [
            'localteam_score'       => isset($row['localteam_score']) ? (int) $row['localteam_score'] : null,
            'visitorteam_score'     => isset($row['visitorteam_score']) ? (int) $row['visitorteam_score'] : null,
            'localteam_pen_score'   => isset($row['localteam_pen_score']) ? (int) $row['localteam_pen_score'] : null,
            'visitorteam_pen_score' => isset($row['visitorteam_pen_score']) ? (int) $row['visitorteam_pen_score'] : null,
            'ht_score'              => $row['ht_score'],
            'ft_score'              => $row['ft_score'],
            'et_score'              => $row['et_score'],
            'ps_score'              => $row['ps_score'],
        ];

        // Time
        $time = [
            'status'     => $row['time__status'],
            'starting_at' => [
                'date_time'  => $row['time__datetime'],
                'date'       => $row['time__date'],
                'time'       => $row['time__time'],
                'timestamp'  => isset($row['time__timestamp']) ? (int) $row['time__timestamp'] : null,
                'timezone'   => $row['time__timezone'],
            ],
            'minute'       => isset($row['time__minute']) ? (int) $row['time__minute'] : null,
            'second'       => isset($row['time__second']) ? (int) $row['time__second'] : null,
            'added_time'   => isset($row['time__added_time']) ? (int) $row['time__added_time'] : null,
            'extra_minute' => isset($row['time__extra_minute']) ? (int) $row['time__extra_minute'] : null,
            'injury_time'  => isset($row['time__injury_time']) ? (int) $row['time__injury_time'] : null,
            'match_period'  => $row['time__match_period'],
        ];

        // Local Team
        $localTeam = [
            'id'                => isset($row['ltm__id']) ? (int) $row['ltm__id'] : null,
            'legacy_id'         => null, // only if you have it
            'name'              => $row['ltm__name'],
            'short_code'        => $row['ltm__short_code'],
            'twitter'           => $row['ltm__twitter'],
            'country_id'        => isset($row['ltm__country_id']) ? (int) $row['ltm__country_id'] : null,
            'national_team'     => isset($row['ltm__national_team']) ? (bool) $row['ltm__national_team'] : false,
            'founded'           => isset($row['ltm__founded']) ? (int) $row['ltm__founded'] : null,
            'logo_path'         => $row['ltm__logo_path'],
            'venue_id'          => isset($row['ltm__venue_id']) ? (int) $row['ltm__venue_id'] : null,
            'current_season_id' => null,  // populate if you store it in your DB
            'is_placeholder'    => false, // placeholder field if you need it
            'logo_url'          => null,  // if you store a different logo URL
        ];

        // Visitor Team
        $visitorTeam = [
            'id'                => isset($row['vtm__id']) ? (int) $row['vtm__id'] : null,
            'legacy_id'         => null,
            'name'              => $row['vtm__name'],
            'short_code'        => $row['vtm__short_code'],
            'twitter'           => $row['vtm__twitter'],
            'country_id'        => isset($row['vtm__country_id']) ? (int) $row['vtm__country_id'] : null,
            'national_team'     => isset($row['vtm__national_team']) ? (bool) $row['vtm__national_team'] : false,
            'founded'           => isset($row['vtm__founded']) ? (int) $row['vtm__founded'] : null,
            'logo_path'         => $row['vtm__logo_path'],
            'venue_id'          => isset($row['vtm__venue_id']) ? (int) $row['vtm__venue_id'] : null,
            'current_season_id' => null,
            'is_placeholder'    => false,
            'logo_url'          => null,
        ];

        // Referee
        $referee = [
            'id'          => isset($row['referee_id']) ? (int) $row['referee_id'] : null,
            'common_name' => $row['referee_name'],
        ];

        // Venue
        $venue = [
            'id'   => isset($row['venue_id']) ? (int) $row['venue_id'] : null,
            'name' => $row['venue_name'],
        ];

        // Aggregate
        $aggregate = null;
        if (!empty($row['aggregate_id'])) {
            $aggregate = [
                'id'             => (int) $row['aggregate_id'],
                'league_id'      => isset($row['ag__league_id']) ? (int) $row['ag__league_id'] : null,
                'season_id'      => isset($row['ag__season_id']) ? (int) $row['ag__season_id'] : null,
                'stage_id'       => isset($row['ag__stage_id']) ? (int) $row['ag__stage_id'] : null,
                'localteam'      => $row['ag__localteam'],
                'localteam_id'   => isset($row['ag__localteam_id']) ? (int) $row['ag__localteam_id'] : null,
                'visitorteam'    => $row['ag__visitorteam'],
                'visitorteam_id' => isset($row['ag__visitorteam_id']) ? (int) $row['ag__visitorteam_id'] : null,
                'result'         => $row['ag__result'],
                'winner'         => isset($row['ag__winner']) ? (int) $row['ag__winner'] : null,
                'detail'         => $row['ag__detail'],
            ];
        }

        // Build the single fixture array
        $fixture_data = [
            'id'                   => $fixture_id,
            'season_id'            => isset($row['season_id']) ? (int) $row['season_id'] : null,
            'group_id'             => isset($row['group_id']) ? (int) $row['group_id'] : null,
            'aggregate_id'         => isset($row['aggregate_id']) ? (int) $row['aggregate_id'] : null,
            'localteam_id'         => isset($row['localteam_id']) ? (int) $row['localteam_id'] : null,
            'visitorteam_id'       => isset($row['visitorteam_id']) ? (int) $row['visitorteam_id'] : null,
            'stage_type'           => $row['stage_type'],
            'localteam_coach_id'   => isset($row['localteam_coach_id']) ? (int) $row['localteam_coach_id'] : null,
            'visitorteam_coach_id' => isset($row['visitorteam_coach_id']) ? (int) $row['visitorteam_coach_id'] : null,
            'winner_team_id'       => isset($row['winner_team_id']) ? (int) $row['winner_team_id'] : null,
            'commentaries'         => isset($row['commentaries']) ? (bool) $row['commentaries'] : false,
            'leg'                  => $row['leg'],
            'is_placeholder'       => isset($row['is_placeholder']) ? (bool) $row['is_placeholder'] : false,

            'scores'      => $scores,
            'time'        => $time,
            'localTeam'   => $localTeam,
            'visitorTeam' => $visitorTeam,

            // If you store cards or events in separate tables, you might need additional queries or left joins:
            'cards'       => [], // Placeholder for cards data
            'referee'     => $referee,
            'venue'       => $venue,
            'aggregate'   => $aggregate,
            'events'      => [], // Placeholder for events data

            // Adding red cards information
            'red_cards'   => [
                [
                    'team_id' => isset($row['localteam_id']) ? (int) $row['localteam_id'] : null,
                    'count'   => isset($row['red_cards_local']) ? (int) $row['red_cards_local'] : 0,
                ],
                [
                    'team_id' => isset($row['visitorteam_id']) ? (int) $row['visitorteam_id'] : null,
                    'count'   => isset($row['red_cards_visitor']) ? (int) $row['red_cards_visitor'] : 0,
                ],
            ],
        ];

        $leagues_indexed[$league_id]['fixtures'][] = $fixture_data;
    }

    // Adjust fixtures for UTC offset and filter by the target date
    $this->adjustFixturesForUtcOffsetAndFilter($leagues_indexed, $utc_offset, $date);

    // Build final return structure
    // Basic pagination metadata: we’re not actually paginating in this example, 
    // but you can do so if needed. For now, total = count of fixtures, etc.
    $all_fixture_count = 0;
    foreach ($leagues_indexed as $league_data) {
        $all_fixture_count += count($league_data['fixtures']);
    }

    $response = [
        'meta' => [
            'pagination' => [
                'total'        => $all_fixture_count,
                'count'        => $all_fixture_count,
                'per_page'     => $all_fixture_count, // no real pagination here
                'current_page' => 1,
                'total_pages'  => 1,
                'links'        => null,
            ],
        ],
        'data' => array_values($leagues_indexed), // Convert from associative array to indexed
    ];

    return $response;
}

	

	
	/**
 * Converts a local date and UTC offset to UTC start and end datetimes.
 *
 * @param string $local_date   The selected local date in 'Y-m-d' format.
 * @param int    $utc_offset   The UTC offset in minutes.
 *
 * @return array               An associative array with 'start_utc' and 'end_utc'.
 */
private function getUtcDateRange(string $local_date, int $utc_offset): array
{
    // Create DateTime objects for the start and end of the local day
    $start_local = new DateTime($local_date . ' 00:00:00', new DateTimeZone('UTC'));
    $end_local   = new DateTime($local_date . ' 23:59:59', new DateTimeZone('UTC'));

    // Adjust by UTC offset to get UTC times
    $start_utc = clone $start_local;
    $start_utc->modify(-$utc_offset . ' minutes');

    $end_utc = clone $end_local;
    $end_utc->modify(-$utc_offset . ' minutes');

    return [
        'start_utc' => $start_utc->format('Y-m-d H:i:s'),
        'end_utc'   => $end_utc->format('Y-m-d H:i:s'),
    ];
}

	


	
/**
 * Adjusts fixture times based on UTC offset and filters them by the target date.
 *
 * @param array  &$leagues    Array of leagues, each containing fixtures.
 * @param int    $utc_offset  UTC offset in minutes (e.g., +330 for UTC+5:30).
 * @param string $date        Target date in 'Y-m-d' format.
 *
 * @return void
 */
private function adjustFixturesForUtcOffsetAndFilter(array &$leagues, int $utc_offset, string $date)
{
    // Define the UTC offset in hours and minutes
    $offset_hours = floor($utc_offset / 60);
    $offset_minutes = abs($utc_offset % 60);
    $offset_sign = ($utc_offset >= 0) ? '+' : '-';

    // Create a formatted offset string (e.g., "+05:30" or "-04:00")
    $formatted_offset = sprintf("%s%02d:%02d", $offset_sign, abs($offset_hours), $offset_minutes);

    // Create DateTimeZone objects
    $utcTimeZone = new DateTimeZone("UTC");
    $localTimeZone = new DateTimeZone($formatted_offset);

    // Create a DateTime object for the target date in UTC
    $targetDateUTC = (new DateTime($date, $utcTimeZone))->setTime(0, 0, 0);

    foreach ($leagues as &$league) {
        // Filter fixtures based on the adjusted date
        $league['fixtures'] = array_filter($league['fixtures'], function ($game) use ($localTimeZone, $targetDateUTC) {
            // Combine date and time from fixture
            $fixtureDateTimeStr = $game['time']['starting_at']['date'] . ' ' . $game['time']['starting_at']['time'];

            // Create DateTime object in UTC
            $fixtureDateTimeUTC = DateTime::createFromFormat('Y-m-d H:i:s', $fixtureDateTimeStr, new DateTimeZone('UTC'));

            if (!$fixtureDateTimeUTC) {
                error_log("Invalid fixture datetime for game ID {$game['id']}");
                return false;
            }

            // Clone and set to local timezone
            $adjustedDateTime = clone $fixtureDateTimeUTC;
            $adjustedDateTime->setTimezone($localTimeZone);

            // Check if the adjusted date matches the target date
            if ($adjustedDateTime->format('Y-m-d') === $targetDateUTC->format('Y-m-d')) {
                // Update the fixture time with the adjusted time
                $game['time']['starting_at']['date'] = $adjustedDateTime->format('Y-m-d');
                $game['time']['starting_at']['time'] = $adjustedDateTime->format('H:i:s');
                return true;
            }

            return false;
        });

        // Re-index the filtered fixtures
        $league['fixtures'] = array_values($league['fixtures']);
    }

    // Remove leagues with no fixtures
    $leagues = array_filter($leagues, function ($league) {
        return !empty($league['fixtures']);
    });

    // Re-index the leagues array
    $leagues = array_values($leagues);
}

	


	

	public function get_matches_api_response($date) {
    // Define the API endpoint
    $endpoint = "getGroupedFixturesForDateByTimezoneWithKeys8";
    $url = $this->api_url . $endpoint;

    // Build the query parameters
    $query_args = [
        'date'     => $date,
        'apiKey'   => $this->api_key,
        'endpoint' => 'matches'
    ];

    // Append query parameters to the URL
    $url = add_query_arg($query_args, $url);

    // Debugging: Log the API URL
    error_log("LiveFot API URL: " . $url);

    // Make the API request using WordPress's HTTP API
    $response = wp_remote_get($url);

    // Check for errors in the response
    if (is_wp_error($response)) {
        $error_message = "LiveFot API Request Error: " . $response->get_error_message();
        error_log($error_message);
        return [
            'success' => false,
            'message' => $response->get_error_message()
        ];
    }

    // Retrieve and decode the response body
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check for JSON parsing errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $json_error = "LiveFot API JSON Error: " . json_last_error_msg();
        error_log($json_error);
        return [
            'success' => false,
            'message' => json_last_error_msg()
        ];
    }

    // Check if the API returned an error within the data
    if (isset($data['error'])) {
        $api_error = "LiveFot API Response Error: " . $data['error'];
        error_log($api_error);
        return [
            'success' => false,
            'message' => $data['error']
        ];
    }

    // Call the function to insert data into the database
    $insert_success = $this->insert_api_data_into_tables($data);
   
    // Log the success or failure of the database insertion
    if ($insert_success) {
        // Define UTC offset if necessary
        $utc_offset = 0; // Adjust this value based on your timezone settings

        // Fetch matches after successful insertion
        $matches = $this->get_matches3($date, $utc_offset);

        // Optionally, you can log the successful insertion
        // error_log("Data successfully inserted into database and matches fetched.");

        return [
          
            'data'    => $matches
        ];
    }
   
}


	

	 public function get_matches3($date, $utc_offset) {
        $endpoint = "wp/fixtures/date";
        $url = $this->api_url . $endpoint;

        // Build the query parameters
        $query_args = [
            
            'date'          => $date,
			'apiKey'        => $this->api_key,
			'endpoint'      => 'matches'
        ];

        // Append query parameters to the URL
        $url = add_query_arg($query_args, $url);

        // Debugging: Log the API URL
        error_log("LiveFot API URL: " . $url);

        // Make the API request
        $response = wp_remote_get($url);

        // Check for errors in the response
        if (is_wp_error($response)) {
            error_log("LiveFot API Request Error: " . $response->get_error_message());
            return false;
        }

        // Retrieve and decode the response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Check for JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("LiveFot API JSON Error: " . json_last_error_msg());
            return false;
        }

        // Check if the API returned an error within the data
        if (isset($data['error'])) {
            error_log("LiveFot API Response Error: " . $data['error']);
            return false;
        }

        // Insert matches into the database
        $insert_success = $this->insert_api_data_into_tables($data);

        if ($insert_success) {
            return true;
        } else {
            error_log("LiveFot API Data Insertion Error for date: " . $date);
            return false;
        }
    }
	
	
	//$utc_offset
public function get_matches2($date, $utc_offset) {
	
	
	 if (empty($this->api_url)) {
     //   error_log("API URL is null or empty. Skipping API call.");
        return null;
    }
	
    $url = $this->api_url . "wp/fixtures/date";

    // Build the query parameters
    $query_args = [
         // Adjusted key casing to match your example
        
        'date' => $date,
		'apiKey' => $this->api_key,
		'endpoint' => 'matches'
    ];

    // Append query parameters to the URL
    $url = add_query_arg($query_args, $url);

    // Debugging: Log or output the URL
    error_log("API URL: " . $url);

    // Make the API request
    $response = wp_remote_get($url);

    // Check for errors in the response
    if (is_wp_error($response)) {
        error_log("API Error: " . $response->get_error_message());
        return array('error' => $response->get_error_message());
    }

    // Retrieve and decode the response body
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check for JSON parsing errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Error: Invalid JSON response");
        return array('error' => 'Invalid JSON response');
    }
	
	 // Call the insert function with the API response data
   // If there's no 'error' key, insert data
        if (!isset($data['error'])) {
           // $this->insert_api_data_into_tables($data);
           // insert_live_api_data_into_tables
           $this->insert_live_api_data_into_tables($data);
        }
	
   // return $data;
   // Call get_matches_live_no_date_filter and return its response
    return $this->get_matches_from_db($date, $utc_offset);
}


	
private function insert_api_data_into_tables($data) {
        // Make sure $wpdb is available
        global $wpdb;

        // Table names
        $leagues_table   = $wpdb->prefix . 'livefot_leagues';
        $countries_table = $wpdb->prefix . 'livefot_countries';
        $fixtures_table  = $wpdb->prefix . 'livefot_fixtures';
        $teams_table     = $wpdb->prefix . 'livefot_teams';
        $referees_table  = $wpdb->prefix . 'livefot_referees';
        $venues_table    = $wpdb->prefix . 'livefot_venues';
        $scores_table    = $wpdb->prefix . 'livefot_scores';
        $time_table      = $wpdb->prefix . 'livefot_time';
        $aggregate_table = $wpdb->prefix . 'livefot_aggregate';
        $api_calls        = $wpdb->prefix . 'livefot_api_calls';
        if (!isset($data['data']) || !is_array($data['data'])) {
            // No "data" key or invalid format
            return;
        }
	 
	 
	  $endpoint_name = 'matches';

        // Log API call
        // We pull 'remainingCalls' and 'status' from the top level of the response.
        $wpdb->replace(
            $api_calls,
            [
                'endpoint_name'   => $endpoint_name,
                'remaining_calls' => $data['remainingCalls'] ?? null,  // from top level
                'status'          => $data['status'] ?? 'N/A'
               
            ],
            ['%s', '%d', '%s', '%s']
        );
	 
 
	 
        foreach ($data['data'] as $match_data) {
            // Insert league info
            if (!isset($match_data['league_info'])) {
                continue; // skip if league_info is missing
            }
            $league_info  = $match_data['league_info'];
            $country_info = $league_info['country'] ?? null;

            // Insert league record
            if ($country_info) {
                $wpdb->replace(
                    $leagues_table,
                    [
                        'league_id'         => $league_info['id'],
                        'name'              => $league_info['name'],
                        'is_cup'            => $league_info['is_cup'],
                        'logo_path'         => $league_info['logo_path'],
                        'current_season_id' => $league_info['current_season_id'],
                        'stage_name'        => $league_info['stage_name'],
                        'country_id'        => $country_info['id']
                    ]
                );

                // Insert country info
                $wpdb->replace(
                    $countries_table,
                    [
                        'country_id' => $country_info['id'],
                        'name'       => $country_info['name'],
                        'image_path' => $country_info['image_path']
                    ]
                );
            }

            // Insert fixtures
            if (!isset($match_data['fixtures']) || !is_array($match_data['fixtures'])) {
                continue;
            }

            foreach ($match_data['fixtures'] as $fixture) {
                // Extract related arrays
                $scores      = $fixture['scores']      ?? [];
                $time        = $fixture['time']        ?? [];
                $localTeam   = $fixture['localTeam']   ?? [];
                $visitorTeam = $fixture['visitorTeam'] ?? [];
                $referee     = $fixture['referee']     ?? [];
                $venue       = $fixture['venue']       ?? [];
                $aggregate   = $fixture['aggregate']   ?? [];

                // Insert fixture
                $wpdb->replace(
                    $fixtures_table,
                    [
                        'fixture_id'            => $fixture['id'],
                        'season_id'             => $fixture['season_id'],
						'league_id'             => $league_info['id'], 
                        'group_id'              => $fixture['group_id'],
                        'aggregate_id'          => $fixture['aggregate_id'],
                        'localteam_id'          => $fixture['localteam_id'],
                        'localteam_name'        => $localTeam['name'] ?? '',
                        'visitorteam_id'        => $fixture['visitorteam_id'],
                        'visitorteam_name'      => $visitorTeam['name'] ?? '',
                        'stage_type'            => $fixture['stage_type'],
                        'localteam_coach_id'    => $fixture['localteam_coach_id'],
                        'visitorteam_coach_id'  => $fixture['visitorteam_coach_id'],
                        'winner_team_id'        => $fixture['winner_team_id'],
                        'commentaries'          => $fixture['commentaries'],
                        'leg'                   => $fixture['leg'],
                        'is_placeholder'        => $fixture['is_placeholder'],
                        'referee_id'            => $referee['id'] ?? null,
                        'venue_id'              => $venue['id'] ?? null
                    ]
                );

                // Insert local team
                if (!empty($localTeam)) {
                    $wpdb->replace(
                        $teams_table,
                        [
                            'team_id'           => $localTeam['id'],
                            'name'              => $localTeam['name'],
                            'shortcode'         => $localTeam['short_code'],
                            'twitter'           => $localTeam['twitter'],
                            'country_id'        => $localTeam['country_id'],
                            'national_team'     => $localTeam['national_team'],
                            'founded'           => $localTeam['founded'],
                            'logo_path'         => $localTeam['logo_path'],
                            'venue_id'          => $localTeam['venue_id'],
                            'current_season_id' => $localTeam['current_season_id']
                        ]
                    );
                }

                // Insert visitor team
                if (!empty($visitorTeam)) {
                    $wpdb->replace(
                        $teams_table,
                        [
                            'team_id'           => $visitorTeam['id'],
                            'name'              => $visitorTeam['name'],
                            'shortcode'         => $visitorTeam['short_code'],
                            'twitter'           => $visitorTeam['twitter'],
                            'country_id'        => $visitorTeam['country_id'],
                            'national_team'     => $visitorTeam['national_team'],
                            'founded'           => $visitorTeam['founded'],
                            'logo_path'         => $visitorTeam['logo_path'],
                            'venue_id'          => $visitorTeam['venue_id'],
                            'current_season_id' => $visitorTeam['current_season_id']
                        ]
                    );
                }

                // Insert referee
                if (!empty($referee)) {
                    $wpdb->replace(
                        $referees_table,
                        [
                            'referee_id'   => $referee['id'],
                            'common_name'  => $referee['common_name']
                        ]
                    );
                }

                // Insert venue
                if (!empty($venue)) {
                    $wpdb->replace(
                        $venues_table,
                        [
                            'venue_id' => $venue['id'],
                            'name'     => $venue['name']
                        ]
                    );
                }

                // Insert scores
                if (!empty($scores)) {
                    $wpdb->replace(
                        $scores_table,
                        [
                            'fixture_id'             => $scores['fixture_id'],
                            'localteam_score'        => $scores['localteam_score'],
                            'visitorteam_score'      => $scores['visitorteam_score'],
                            'localteam_pen_score'    => $scores['localteam_pen_score'],
                            'visitorteam_pen_score'  => $scores['visitorteam_pen_score'],
                            'ht_score'               => $scores['ht_score'],
                            'ft_score'               => $scores['ft_score'],
                            'et_score'               => $scores['et_score'],
                            'ps_score'               => $scores['ps_score']
                        ]
                    );
                }

                // Insert time
                if (!empty($time)) {
                    $starting_at = $time['starting_at'] ?? [];
                    $wpdb->replace(
                        $time_table,
                        [
                            'fixture_id'           => $time['fixture_id'] ?? null,
                            'status'               => $time['status'] ?? '',
                            'starting_at_datetime' => $starting_at['date_time'] ?? '',
                            'starting_at_date'     => $starting_at['date'] ?? '',
                            'starting_at_time'     => $starting_at['time'] ?? '',
                            'starting_at_timestamp'=> $starting_at['timestamp'] ?? 0,
                            'starting_at_timezone' => $starting_at['timezone'] ?? '',
                            'minute'               => $time['minute'] ?? 0,
                            'second'               => $time['second'] ?? 0,
                            'added_time'           => $time['added_time'] ?? 0,
                            'extra_minute'         => $time['extra_minute'] ?? 0,
                            'injury_time'          => $time['injury_time'] ?? 0,
                            'match_period'         => $time['matchPeriod'] ?? ''
                        ]
                    );
                }

                // Insert aggregate
                if (!empty($aggregate)) {
                    $wpdb->replace(
                        $aggregate_table,
                        [
                            'aggregate_id'     => $aggregate['id'],
                            'league_id'        => $aggregate['league_id'],
                            'season_id'        => $aggregate['season_id'],
                            'stage_id'         => $aggregate['stage_id'],
                            'localteam'        => $aggregate['localteam'],
                            'localteam_id'     => $aggregate['localteam_id'],
                            'visitorteam'      => $aggregate['visitorteam'],
                            'visitorteam_id'   => $aggregate['visitorteam_id'],
                            'result'           => $aggregate['result'],
                            'winner'           => $aggregate['winner'],
                            'detail'           => $aggregate['detail']
                        ]
                    );
                }
				
				
		
            try {
                $lineup_data = $this->fetch_lineup_from_api($fixture['id']);

                if ($lineup_data && !isset($lineup_data['error'])) {
                    // Optionally, log success
                    error_log("Lineup fetched and inserted successfully for fixture ID: " . $fixture['id']);
                } else {
                    // Handle errors or log them
                    $error_message = $lineup_data['error'] ?? 'Unknown error while fetching lineup.';
                    error_log("Failed to fetch lineup for fixture ID {$fixture['id']}: {$error_message}");
                }
            } catch (Exception $e) {
                // Catch any unexpected exceptions during lineup fetch
                error_log("Exception while fetching lineup for fixture ID {$fixture['id']}: " . $e->getMessage());
            }
				
				
			
				try {
    // Attempt to fetch statistics data from the API for the given fixture ID
    $stats_data = $this->fetch_stats_from_api($fixture['id']);

    if ($stats_data && !isset($stats_data['error'])) {
        // Optionally, log the successful fetch and insertion of statistics
        error_log("Stats fetched and inserted successfully for fixture ID: " . $fixture['id']);
    } else {
        // Handle errors returned by the API or log them
        $error_message = $stats_data['error'] ?? 'Unknown error while fetching stats.';
        error_log("Failed to fetch stats for fixture ID {$fixture['id']}: {$error_message}");
    }
} catch (Exception $e) {
    // Catch and log any unexpected exceptions that occur during the fetch process
    error_log("Exception while fetching stats for fixture ID {$fixture['id']}: " . $e->getMessage());
}

				
				
				try {
    // Attempt to fetch events data from the API for the given fixture ID
    $events_data = $this->fetch_events_from_api($fixture['id']);

    if ($events_data && !isset($events_data['error'])) {
        // Optionally, log the successful fetch and insertion of events
    //    error_log("Events fetched and inserted successfully for fixture ID: " . $fixture['id']);
    } else {
        // Handle errors returned by the API or log them
   //     $error_message = $events_data['error'] ?? 'Unknown error while fetching events.';
    //    error_log("Failed to fetch events for fixture ID {$fixture['id']}: {$error_message}");
    }
} catch (Exception $e) {
    // Catch and log any unexpected exceptions that occur during the fetch process
    error_log("Exception while fetching events for fixture ID {$fixture['id']}: " . $e->getMessage());
}

				
						
				
				try {
    // Attempt to fetch standings data from the API
    $standings_data = $this->fetch_standings_from_api(
        $fixture['id'],        // Fixture ID
        $league_info['id'],    // League ID
        $fixture['group_id'],  // Group ID
        $fixture['season_id']  // Season ID
    );

    if ($standings_data && $standings_data['status'] === 'success') {
        // Optionally, log success
     //   error_log("Standings fetched and saved successfully for Fixture ID: {$fixture['id']}, League ID: {$league_info['id']}");
    } else {
        // Handle errors or log them
        $error_message = $standings_data['error'] ?? 'Unknown error while fetching standings.';
     //   error_log("Failed to fetch standings for Fixture ID {$fixture['id']}, League ID {$league_info['id']}: {$error_message}");
    }
} catch (Exception $e) {
    // Catch any unexpected exceptions during standings fetch
  //  error_log("Exception while fetching standings for Fixture ID {$fixture['id']}, League ID {$league_info['id']}: " . $e->getMessage());
}		
				
            }
        }
    }
	
	
	private function insert_live_api_data_into_tables($data) {
        // Make sure $wpdb is available
        global $wpdb;

        // Table names
        $leagues_table   = $wpdb->prefix . 'livefot_leagues';
        $countries_table = $wpdb->prefix . 'livefot_countries';
        $fixtures_table  = $wpdb->prefix . 'livefot_fixtures';
        $teams_table     = $wpdb->prefix . 'livefot_teams';
        $referees_table  = $wpdb->prefix . 'livefot_referees';
        $venues_table    = $wpdb->prefix . 'livefot_venues';
        $scores_table    = $wpdb->prefix . 'livefot_scores';
        $time_table      = $wpdb->prefix . 'livefot_time';
        $aggregate_table = $wpdb->prefix . 'livefot_aggregate';
        $api_calls        = $wpdb->prefix . 'livefot_api_calls';
        if (!isset($data['data']) || !is_array($data['data'])) {
            // No "data" key or invalid format
            return;
        }
	 
	 
	  $endpoint_name = 'matches';

        // Log API call
        // We pull 'remainingCalls' and 'status' from the top level of the response.
        $wpdb->replace(
            $api_calls,
            [
                'endpoint_name'   => $endpoint_name,
                'remaining_calls' => $data['remainingCalls'] ?? null,  // from top level
                'status'          => $data['status'] ?? 'N/A'
               
            ],
            ['%s', '%d', '%s', '%s']
        );
	 
 
	 
        foreach ($data['data'] as $match_data) {
            // Insert league info
            if (!isset($match_data['league_info'])) {
                continue; // skip if league_info is missing
            }
            $league_info  = $match_data['league_info'];
            $country_info = $league_info['country'] ?? null;

            // Insert league record
            if ($country_info) {
                $wpdb->replace(
                    $leagues_table,
                    [
                        'league_id'         => $league_info['id'],
                        'name'              => $league_info['name'],
                        'is_cup'            => $league_info['is_cup'],
                        'logo_path'         => $league_info['logo_path'],
                        'current_season_id' => $league_info['current_season_id'],
                        'stage_name'        => $league_info['stage_name'],
                        'country_id'        => $country_info['id']
                    ]
                );

                // Insert country info
                $wpdb->replace(
                    $countries_table,
                    [
                        'country_id' => $country_info['id'],
                        'name'       => $country_info['name'],
                        'image_path' => $country_info['image_path']
                    ]
                );
            }

            // Insert fixtures
            if (!isset($match_data['fixtures']) || !is_array($match_data['fixtures'])) {
                continue;
            }

            foreach ($match_data['fixtures'] as $fixture) {
                // Extract related arrays
                $scores      = $fixture['scores']      ?? [];
                $time        = $fixture['time']        ?? [];
                $localTeam   = $fixture['localTeam']   ?? [];
                $visitorTeam = $fixture['visitorTeam'] ?? [];
                $referee     = $fixture['referee']     ?? [];
                $venue       = $fixture['venue']       ?? [];
                $aggregate   = $fixture['aggregate']   ?? [];

                // Insert fixture
                $wpdb->replace(
                    $fixtures_table,
                    [
                        'fixture_id'            => $fixture['id'],
                        'season_id'             => $fixture['season_id'],
						'league_id'             => $league_info['id'], 
                        'group_id'              => $fixture['group_id'],
                        'aggregate_id'          => $fixture['aggregate_id'],
                        'localteam_id'          => $fixture['localteam_id'],
                        'localteam_name'        => $localTeam['name'] ?? '',
                        'visitorteam_id'        => $fixture['visitorteam_id'],
                        'visitorteam_name'      => $visitorTeam['name'] ?? '',
                        'stage_type'            => $fixture['stage_type'],
                        'localteam_coach_id'    => $fixture['localteam_coach_id'],
                        'visitorteam_coach_id'  => $fixture['visitorteam_coach_id'],
                        'winner_team_id'        => $fixture['winner_team_id'],
                        'commentaries'          => $fixture['commentaries'],
                        'leg'                   => $fixture['leg'],
                        'is_placeholder'        => $fixture['is_placeholder'],
                        'referee_id'            => $referee['id'] ?? null,
                        'venue_id'              => $venue['id'] ?? null
                    ]
                );

                // Insert local team
                if (!empty($localTeam)) {
                    $wpdb->replace(
                        $teams_table,
                        [
                            'team_id'           => $localTeam['id'],
                            'name'              => $localTeam['name'],
                            'shortcode'         => $localTeam['short_code'],
                            'twitter'           => $localTeam['twitter'],
                            'country_id'        => $localTeam['country_id'],
                            'national_team'     => $localTeam['national_team'],
                            'founded'           => $localTeam['founded'],
                            'logo_path'         => $localTeam['logo_path'],
                            'venue_id'          => $localTeam['venue_id'],
                            'current_season_id' => $localTeam['current_season_id']
                        ]
                    );
                }

                // Insert visitor team
                if (!empty($visitorTeam)) {
                    $wpdb->replace(
                        $teams_table,
                        [
                            'team_id'           => $visitorTeam['id'],
                            'name'              => $visitorTeam['name'],
                            'shortcode'         => $visitorTeam['short_code'],
                            'twitter'           => $visitorTeam['twitter'],
                            'country_id'        => $visitorTeam['country_id'],
                            'national_team'     => $visitorTeam['national_team'],
                            'founded'           => $visitorTeam['founded'],
                            'logo_path'         => $visitorTeam['logo_path'],
                            'venue_id'          => $visitorTeam['venue_id'],
                            'current_season_id' => $visitorTeam['current_season_id']
                        ]
                    );
                }

                // Insert referee
                if (!empty($referee)) {
                    $wpdb->replace(
                        $referees_table,
                        [
                            'referee_id'   => $referee['id'],
                            'common_name'  => $referee['common_name']
                        ]
                    );
                }

                // Insert venue
                if (!empty($venue)) {
                    $wpdb->replace(
                        $venues_table,
                        [
                            'venue_id' => $venue['id'],
                            'name'     => $venue['name']
                        ]
                    );
                }

                // Insert scores
                if (!empty($scores)) {
                    $wpdb->replace(
                        $scores_table,
                        [
                            'fixture_id'             => $scores['fixture_id'],
                            'localteam_score'        => $scores['localteam_score'],
                            'visitorteam_score'      => $scores['visitorteam_score'],
                            'localteam_pen_score'    => $scores['localteam_pen_score'],
                            'visitorteam_pen_score'  => $scores['visitorteam_pen_score'],
                            'ht_score'               => $scores['ht_score'],
                            'ft_score'               => $scores['ft_score'],
                            'et_score'               => $scores['et_score'],
                            'ps_score'               => $scores['ps_score']
                        ]
                    );
                }

                // Insert time
                if (!empty($time)) {
                    $starting_at = $time['starting_at'] ?? [];
                    $wpdb->replace(
                        $time_table,
                        [
                            'fixture_id'           => $time['fixture_id'] ?? null,
                            'status'               => $time['status'] ?? '',
                            'starting_at_datetime' => $starting_at['date_time'] ?? '',
                            'starting_at_date'     => $starting_at['date'] ?? '',
                            'starting_at_time'     => $starting_at['time'] ?? '',
                            'starting_at_timestamp'=> $starting_at['timestamp'] ?? 0,
                            'starting_at_timezone' => $starting_at['timezone'] ?? '',
                            'minute'               => $time['minute'] ?? 0,
                            'second'               => $time['second'] ?? 0,
                            'added_time'           => $time['added_time'] ?? 0,
                            'extra_minute'         => $time['extra_minute'] ?? 0,
                            'injury_time'          => $time['injury_time'] ?? 0,
                            'match_period'         => $time['matchPeriod'] ?? ''
                        ]
                    );
                }

                // Insert aggregate
                if (!empty($aggregate)) {
                    $wpdb->replace(
                        $aggregate_table,
                        [
                            'aggregate_id'     => $aggregate['id'],
                            'league_id'        => $aggregate['league_id'],
                            'season_id'        => $aggregate['season_id'],
                            'stage_id'         => $aggregate['stage_id'],
                            'localteam'        => $aggregate['localteam'],
                            'localteam_id'     => $aggregate['localteam_id'],
                            'visitorteam'      => $aggregate['visitorteam'],
                            'visitorteam_id'   => $aggregate['visitorteam_id'],
                            'result'           => $aggregate['result'],
                            'winner'           => $aggregate['winner'],
                            'detail'           => $aggregate['detail']
                        ]
                    );
                }
				
            }
        }
    }
	
	
	
	public function get_match_events($match_id) {
    global $wpdb;

    // Retrieve the events interval from user settings; default to 3600 seconds if not set.
    $events_interval_seconds = intval(get_option('livefot_interval_events', 3600));

    // Define a unique transient key for the events of this match.
    $transient_key = 'match_events_' . $match_id;
    
    // Attempt to retrieve cached events data.
    $cached_events = get_transient($transient_key);
    if ($cached_events !== false) {
        return $cached_events;
    }
    
    /**
     * STEP 1: Since cache is not available, force a call to the API.
     */
    try {
        $events_data = $this->fetch_events_from_api($match_id);
        if ($events_data && !isset($events_data['error'])) {
            // Optionally, you can log a success message.
            error_log("Events fetched and inserted successfully for match ID: " . $match_id);
        } else {
            $error_message = isset($events_data['error']) ? $events_data['error'] : 'Unknown error while fetching events.';
            error_log("Failed to fetch events for match ID {$match_id}: {$error_message}");
        }
    } catch (Exception $e) {
        error_log("Exception while fetching events for match ID {$match_id}: " . $e->getMessage());
    }
    
    /**
     * STEP 2: Query the database for the events data.
     */
    // Table names.
    $events_table  = $wpdb->prefix . 'livefot_events';
    $players_table = $wpdb->prefix . 'livefot_players';
    $teams_table   = $wpdb->prefix . 'livefot_teams';
    
    $sql = $wpdb->prepare("
        SELECT 
            e.id AS local_id,            -- internal auto-increment
            e.event_id,                  -- unique event ID
            e.match_id,                  -- match ID
            e.team_id,                   -- team ID that performed the event
            e.player_id,                 -- main player ID
            e.related_player_id,         -- related player ID (e.g., for assists)
            e.type,                      -- event type (goal, card, etc.)
            e.minute,
            e.extra_minute,
    
            -- Team info
            t.id          AS t_local_id, -- team's internal auto-increment PK
            t.team_id     AS t_team_id,
            t.name        AS t_name,
            t.shortcode   AS t_shortcode,
            t.twitter     AS t_twitter,
            t.country_id  AS t_country_id,
            t.national_team AS t_national_team,
            t.founded     AS t_founded,
            t.logo_path   AS t_logo_path,
            t.venue_id    AS t_venue_id,
            t.current_season_id AS t_current_season_id,
            t.gender      AS t_gender,
            t.team_type   AS t_team_type,
    
            -- Main player
            p1.id         AS p1_local_id,
            p1.player_id  AS p1_player_id,
            p1.player_name AS p1_player_name,
            p1.logo_path  AS p1_logo_path,
    
            -- Related player
            p2.id         AS p2_local_id,
            p2.player_id  AS p2_player_id,
            p2.player_name AS p2_player_name,
            p2.logo_path  AS p2_logo_path
        FROM {$events_table} e
        LEFT JOIN {$teams_table} t ON e.team_id = t.team_id
        LEFT JOIN {$players_table} p1 ON e.player_id = p1.player_id
        LEFT JOIN {$players_table} p2 ON e.related_player_id = p2.player_id
        WHERE e.match_id = %d
        ORDER BY e.minute DESC, e.id ASC
    ", $match_id);
    
    // Execute the query.
    $rows = $wpdb->get_results($sql);
    
    // Assemble the final array of events.
    $events = [];
    foreach ($rows as $row) {
        $events[] = [
            'id'                  => (int) $row->event_id,  // you can also return 'local_id' if needed
            'team_id'             => (int) $row->team_id,
            'type'                => $row->type,
            'var_result'          => null,                  // not in DB; setting null
            'fixture_id'          => null,                  // not in DB; setting null
            'player_id'           => $row->player_id ? (int)$row->player_id : null,
            'player_name'         => $row->p1_player_name ?: null,
            'related_player_id'   => $row->related_player_id ? (int)$row->related_player_id : null,
            'related_player_name' => $row->p2_player_name ?: null,
            'minute'              => (int) $row->minute,
            'extra_minute'        => isset($row->extra_minute) ? (int)$row->extra_minute : null,
            'reason'              => null,
            'injuried'            => null,
            'result'              => null,
            'on_pitch'            => true,
            'player' => [
                'player_id'   => $row->p1_player_id ? (int)$row->p1_player_id : null,
                'team_id'     => (int) $row->team_id,
                'country_id'  => null,
                'position_id' => null,
                'common_name' => null,
                'display_name'=> $row->p1_player_name ?: null,
                'fullName'    => $row->p1_player_name ?: null,
                'firstName'   => null,
                'lastName'    => null,
                'nationality' => null,
                'image_path'  => $row->p1_logo_path ?: null,
                'logo_url'    => null,
            ],
            'relatedPlayer' => [
                'player_id'   => $row->p2_player_id ? (int)$row->p2_player_id : null,
                'team_id'     => (int) $row->team_id,
                'country_id'  => null,
                'position_id' => null,
                'common_name' => null,
                'display_name'=> $row->p2_player_name ?: null,
                'fullName'    => $row->p2_player_name ?: null,
                'firstName'   => null,
                'lastName'    => null,
                'nationality' => null,
                'image_path'  => $row->p2_logo_path ?: null,
                'logo_url'    => null,
            ],
            'team' => [
                'id'                => (int) $row->t_team_id,
                'legacy_id'         => 0,
                'name'              => $row->t_name ?: null,
                'short_code'        => $row->t_shortcode ?: null,
                'country_id'        => $row->t_country_id ? (int)$row->t_country_id : null,
                'national_team'     => (bool) $row->t_national_team,
                'logo_path'         => $row->t_logo_path ?: null,
                'venue_id'          => $row->t_venue_id ? (int)$row->t_venue_id : null,
                'current_season_id' => $row->t_current_season_id ? (int)$row->t_current_season_id : null,
                'is_placeholder'    => false,
                'logo_url'          => null,
            ],
        ];
    }
    
    /**
     * STEP 3: Determine cache expiration based on match time status.
     * - By default, the cache will expire after a duration defined by the user setting ('livefot_interval_events').
     * - If the match was started yesterday and has a status of FT, AET, Cancelled, or FT_PEN,
     *   set the cache to expire in 2 hours (7200 seconds).
     */
    $cache_expiration = $events_interval_seconds; // default
    
    // Define the time table name.
    $time_table = $wpdb->prefix . 'livefot_time';
    // Retrieve the starting datetime and status for the match.
    $time_sql = $wpdb->prepare(
        "SELECT status, starting_at_datetime FROM {$time_table} WHERE fixture_id = %d",
        $match_id
    );
    $time_data = $wpdb->get_row($time_sql);
    
    if ($time_data) {
        // Desired statuses.
        $desired_statuses = array('FT', 'AET', 'Cancelled', 'FT_PEN');
        if (in_array($time_data->status, $desired_statuses)) {
            // Convert starting_at_datetime to a timestamp.
            $match_start = strtotime($time_data->starting_at_datetime);
            // Determine yesterday's range: from yesterday midnight to today midnight.
            $yesterday_start = strtotime("yesterday");
            $today_start = strtotime("today");
            if ($match_start >= $yesterday_start && $match_start < $today_start) {
                $cache_expiration = 7200; // 2 hours in seconds.
            }
        }
    }
    
    // Cache the assembled data using the determined expiration time.
    set_transient($transient_key, $events, $cache_expiration);
    
    return $events;
}

public function get_standings($fixture_id, $league_id, $group_id, $season_id) {
    global $wpdb;

    // 1) Retrieve the standings interval from user settings; default to 300 seconds if not set.
    $standings_interval = intval(get_option('livefot_interval_standings', 300));

    // 2) Define a unique transient key, now including $fixture_id for uniqueness
    $transient_key = 'standings_' . $fixture_id . '_' . $league_id . '_' . $group_id . '_' . $season_id;

    // 3) Attempt to retrieve cached standings data
    $cached_standings = get_transient($transient_key);
    if ($cached_standings !== false) {
        return $cached_standings;
    }

    /**
     * STEP 1: Force a call to the API before querying the DB
     */
    try {
        // Pass all four parameters, as fetch_standings_from_api() requires
        $standings_data = $this->fetch_standings_from_api($fixture_id, $league_id, $group_id, $season_id);

        // Check if the API call was successful
        if ($standings_data && isset($standings_data['status']) && $standings_data['status'] === 'success') {
            // Optionally log success:
            // error_log("Standings fetched and inserted successfully for Fixture: {$fixture_id}, League: {$league_id}");
        } else {
            // Handle or log the error
            $error_message = $standings_data['error'] ?? 'Unknown error while fetching standings.';
            // error_log("Failed to fetch standings for Fixture: {$fixture_id}, League: {$league_id}: {$error_message}");
        }
    } catch (Exception $e) {
        // Catch any unexpected exceptions during fetch
        // error_log("Exception fetching standings for Fixture: {$fixture_id}, League: {$league_id}: " . $e->getMessage());
    }

    /**
     * STEP 2: Query the database for the standings data.
     */
    // Define table names
    $standings_table = $wpdb->prefix . 'livefot_standings';

    // Prepare the base SQL query (excluding group_id for now)
    $sql = "
        SELECT 
            standing_id,
            league_id,
            group_id,
            season_id,
            stage_id,
            round_id,
            position,
            team_id,
            team_name,
            shortcode,
            team_logo,
            goals,
            goal_diff,
            wins,
            lost,
            draws,
            played,
            points,
            description,
            recent_form,
            standing_rule_id,
            result,
            fairplay_points_lose,
            updated_at,
            inserted_at
        FROM {$standings_table}
        WHERE league_id = %d
          AND season_id = %d
    ";

    // Conditionally add group_id if it's > 0
    if ($group_id > 0) {
        $sql .= " AND group_id = %d";
    }

    // Finally, append ORDER BY
    $sql .= " ORDER BY position";

    // Prepare the statement properly
    if ($group_id > 0) {
        $standings_query = $wpdb->prepare($sql, $league_id, $season_id, $group_id);
    } else {
        $standings_query = $wpdb->prepare($sql, $league_id, $season_id);
    }

    // Execute the query
    $standings_results = $wpdb->get_results($standings_query, ARRAY_A);

    // Check if any standings data was retrieved
    if (empty($standings_results)) {
        // Optionally log "no data" scenario
        // error_log("No standings found for Fixture: {$fixture_id}, League: {$league_id}, Group: {$group_id}, Season: {$season_id}");
        return null;
    }

    /**
     * STEP 2b: Build the return data
     */
    $data = [
        'fixture_id' => (int) $fixture_id,
        'league_id'  => (int) $league_id,
        'group_id'   => (int) $group_id,
        'season_id'  => (int) $season_id,
        'standings'  => []
    ];

    foreach ($standings_results as $row) {
        $data['standings'][] = [
            'StandingId'         => (int) $row['standing_id'],
            'LeagueId'           => (int) $row['league_id'],
            'GroupId'            => (int) $row['group_id'],
            'SeasonId'           => (int) $row['season_id'],
            'StageId'            => isset($row['stage_id']) ? (int) $row['stage_id'] : null,
            'RoundId'            => isset($row['round_id']) ? (int) $row['round_id'] : null,
            'Position'           => (int) $row['position'],
            'Team' => [
                'TeamId'    => (int) $row['team_id'],
                'Name'      => sanitize_text_field($row['team_name']),
                'ShortCode' => sanitize_text_field($row['shortcode']),
                'LogoPath'  => esc_url_raw($row['team_logo']),
            ],
            'Goals'              => sanitize_text_field($row['goals']),
            'GoalDiff'           => (int) $row['goal_diff'],
            'Wins'               => (int) $row['wins'],
            'Lost'               => (int) $row['lost'],
            'Draws'              => (int) $row['draws'],
            'Played'             => (int) $row['played'],
            'Points'             => (int) $row['points'],
            'Description'        => sanitize_text_field($row['description']),
            'RecentForm'         => sanitize_text_field($row['recent_form']),
            'StandingRuleId'     => isset($row['standing_rule_id']) ? (int) $row['standing_rule_id'] : null,
            'Result'             => sanitize_text_field($row['result']),
            'FairplayPointsLose' => is_null($row['fairplay_points_lose']) ? null : (int) $row['fairplay_points_lose'],
            'UpdatedAt'          => isset($row['updated_at']) ? sanitize_text_field($row['updated_at']) : null,
            'InsertedAt'         => isset($row['inserted_at']) ? sanitize_text_field($row['inserted_at']) : null,
        ];
    }

    /**
     * STEP 3: Cache the assembled data using the determined expiration time.
     */
    set_transient($transient_key, $data, $standings_interval);

    return $data;
}



	

	
	
	/*
	
	public function get_match_lineup($match_id) {
    global $wpdb;

    // Define table names
    $lineup_table     = $wpdb->prefix . 'livefot_lineups';
    $players_table    = $wpdb->prefix . 'livefot_players';
    $teams_table      = $wpdb->prefix . 'livefot_teams';
    $formations_table = $wpdb->prefix . 'livefot_formations';
    $events_table     = $wpdb->prefix . 'livefot_events';

    // Query to fetch team and player data
    $team_player_query = $wpdb->prepare(
        "
        SELECT 
            -- Local Team Data
            t_local.team_id AS local_team_id,
            t_local.name AS local_name,
            t_local.shortcode AS local_shortcode,
            t_local.twitter AS local_twitter,
            t_local.country_id AS local_country_id,
            t_local.national_team AS local_national_team,
            t_local.founded AS local_founded,
            t_local.logo_path AS local_logo_path,
            t_local.venue_id AS local_venue_id,
            t_local.current_season_id AS local_current_season_id,
            t_local.gender AS local_gender,
            t_local.team_type AS local_team_type,
            f_local.local_team_formation,

            -- Visitor Team Data
            t_vis.team_id AS visitor_team_id,
            t_vis.name AS visitor_name,
            t_vis.shortcode AS visitor_shortcode,
            t_vis.twitter AS visitor_twitter,
            t_vis.country_id AS visitor_country_id,
            t_vis.national_team AS visitor_national_team,
            t_vis.founded AS visitor_founded,
            t_vis.logo_path AS visitor_logo_path,
            t_vis.venue_id AS visitor_venue_id,
            t_vis.current_season_id AS visitor_current_season_id,
            t_vis.gender AS visitor_gender,
            t_vis.team_type AS visitor_team_type,
            f_vis.visitor_team_formation,

            -- Player Data
            p.player_id,
            p.player_name,
            p.logo_path AS player_logo_path,
            l.number,
            l.position,
            l.formation_position,
            l.captain,
            l.type,
            l.team_id AS player_team_id
        FROM {$formations_table} f_local
        JOIN {$teams_table} t_local ON f_local.local_team_id = t_local.team_id
        JOIN {$formations_table} f_vis ON f_local.match_id = f_vis.match_id
        JOIN {$teams_table} t_vis ON f_vis.visitor_team_id = t_vis.team_id
        JOIN {$lineup_table} l ON l.match_id = f_local.match_id
        JOIN {$players_table} p ON l.player_id = p.player_id
        WHERE f_local.match_id = %d
        ",
        $match_id
    );

    $team_player_results = $wpdb->get_results($team_player_query, ARRAY_A);

    // Initialize data structure
    $data = [
        'localTeam' => [
            'teamData' => [],
            'players'  => []
        ],
        'visitorTeam' => [
            'teamData' => [],
            'players'  => []
        ],
        'matchEvents' => []
    ];

    // Assemble team and player data
    $local_team_set = false;
    $visitor_team_set = false;
    $local_player_ids = [];
    $visitor_player_ids = [];

    foreach ($team_player_results as $row) {
        if (!$local_team_set) {
            $data['localTeam']['teamData'] = [
                'TeamId'          => (int) $row['local_team_id'],
                'Name'            => $row['local_name'],
                'ShortCode'       => $row['local_shortcode'],
                'Twitter'         => $row['local_twitter'],
                'CountryId'       => (int) $row['local_country_id'],
                'NationalTeam'    => (bool) $row['local_national_team'],
                'Founded'         => (int) $row['local_founded'],
                'LogoPath'        => $row['local_logo_path'],
                'VenueId'         => (int) $row['local_venue_id'],
                'CurrentSeasonId' => (int) $row['local_current_season_id'],
                'Gender'          => $row['local_gender'],
                'TeamType'        => $row['local_team_type'],
                'TeamFormation'   => $row['local_team_formation']
            ];
            $local_team_set = true;
        }

        if (!$visitor_team_set) {
            $data['visitorTeam']['teamData'] = [
                'TeamId'          => (int) $row['visitor_team_id'],
                'Name'            => $row['visitor_name'],
                'ShortCode'       => $row['visitor_shortcode'],
                'Twitter'         => $row['visitor_twitter'],
                'CountryId'       => (int) $row['visitor_country_id'],
                'NationalTeam'    => (bool) $row['visitor_national_team'],
                'Founded'         => (int) $row['visitor_founded'],
                'LogoPath'        => $row['visitor_logo_path'],
                'VenueId'         => (int) $row['visitor_venue_id'],
                'CurrentSeasonId' => (int) $row['visitor_current_season_id'],
                'Gender'          => $row['visitor_gender'],
                'TeamType'        => $row['visitor_team_type'],
                'TeamFormation'   => $row['visitor_team_formation']
            ];
            $visitor_team_set = true;
        }

        // Explicitly check which team the player belongs to
        if ((int) $row['player_team_id'] === (int) $row['local_team_id'] && !in_array($row['player_id'], $local_player_ids)) {
            $data['localTeam']['players'][] = [
                'PlayerId'          => (int) $row['player_id'],
                'PlayerName'        => $row['player_name'],
                'Number'            => (int) $row['number'],
                'Position'          => $row['position'],
                'FormationPosition' => $row['formation_position'],
                'Captain'           => (bool) $row['captain'],
                'Type'              => $row['type'],
                'LogoPath'          => $row['player_logo_path']
            ];
            $local_player_ids[] = $row['player_id'];
        } 
        // **Updated Condition:** Explicitly check if the player belongs to the visitor team
        elseif ((int) $row['player_team_id'] === (int) $row['visitor_team_id'] && !in_array($row['player_id'], $visitor_player_ids)) {
            $data['visitorTeam']['players'][] = [
                'PlayerId'          => (int) $row['player_id'],
                'PlayerName'        => $row['player_name'],
                'Number'            => (int) $row['number'],
                'Position'          => $row['position'],
                'FormationPosition' => $row['formation_position'],
                'Captain'           => (bool) $row['captain'],
                'Type'              => $row['type'],
                'LogoPath'          => $row['player_logo_path']
            ];
            $visitor_player_ids[] = $row['player_id'];
        } 
        // **Optional:** Handle players not belonging to either team
        else {
            // You can log this information or handle it as needed
            // For example:
            // error_log("Player ID {$row['player_id']} does not belong to local or visitor team.");
        }
    }

    // Fetch match events
    $events_query = $wpdb->prepare(
        "
        SELECT 
            e.event_id,
            e.team_id,
            e.type,
            e.match_id,
            e.player_id,
            e.related_player_id,
            e.minute,
            e.extra_minute
        FROM {$events_table} e
        WHERE e.match_id = %d
        ",
        $match_id
    );

    $events_results = $wpdb->get_results($events_query, ARRAY_A);

    foreach ($events_results as $event) {
        $data['matchEvents'][] = [
            'EventId'         => (int) $event['event_id'],
            'TeamId'          => (int) $event['team_id'],
            'Type'            => $event['type'],
            'MatchId'         => (int) $event['match_id'],
            'PlayerId'        => (int) $event['player_id'],
            'RelatedPlayerId' => !empty($event['related_player_id']) ? (int) $event['related_player_id'] : null,
            'Minute'          => (int) $event['minute'],
            'ExtraMinute'     => !is_null($event['extra_minute']) ? (int) $event['extra_minute'] : null
        ];
    }

    return $data;
}
*/
	
/*	
	public function get_match_lineup($match_id) {
    global $wpdb;

    // Define a unique transient key for the match lineup.
    $transient_key = 'match_lineup_' . $match_id;
    
    // Try to retrieve cached lineup data.
    $cached_data = get_transient($transient_key);
    if ($cached_data !== false) {
        return $cached_data;
    }


    try {
        $endpoint_name = 'lineups';
        // Allowed interval in seconds (e.g., 3600 seconds = 1 hour)
        $lineups_interval_seconds = 3600;
        // Define your API calls table name
        $api_calls = $wpdb->prefix . 'api_calls';

        $sql = $wpdb->prepare(
            "
            SELECT 
                MAX(last_call_time) AS last_updated 
            FROM {$api_calls} 
            WHERE endpoint_name = %s 
            GROUP BY endpoint_name
            HAVING last_updated < (NOW() - INTERVAL %d SECOND)
            ",
            $endpoint_name,
            $lineups_interval_seconds
        );

        $lineupcalls = $wpdb->get_row($sql);

        // If the last API call is too old, fetch fresh data from the API.
        if ($lineupcalls && isset($lineupcalls->last_updated)) {
            $lineup_data = $this->fetch_lineup_from_api($match_id);
            if ($lineup_data && !isset($lineup_data['error'])) {
                error_log("Lineup fetched and inserted successfully for match ID: {$match_id}");
            } else {
                $error_message = isset($lineup_data['error']) ? $lineup_data['error'] : 'Unknown error while fetching lineup.';
                error_log("Failed to fetch lineup for match ID {$match_id}: {$error_message}");
            }
        } else {
            error_log("Lineup fetch skipped for match ID {$match_id} due to freshness.");
        }
    } catch (Exception $e) {
        error_log("Exception while checking lineup freshness for match ID {$match_id}: " . $e->getMessage());
    }



    // Define table names.
    $lineup_table      = $wpdb->prefix . 'livefot_lineups';
    $players_table     = $wpdb->prefix . 'livefot_players';
    $teams_table       = $wpdb->prefix . 'livefot_teams';
    $formations_table  = $wpdb->prefix . 'livefot_formations';
    $events_table      = $wpdb->prefix . 'livefot_events';

    // Query to fetch team and player data.
    $team_player_query = $wpdb->prepare(
        "
        SELECT 
            -- Local Team Data
            t_local.team_id AS local_team_id,
            t_local.name AS local_name,
            t_local.shortcode AS local_shortcode,
            t_local.twitter AS local_twitter,
            t_local.country_id AS local_country_id,
            t_local.national_team AS local_national_team,
            t_local.founded AS local_founded,
            t_local.logo_path AS local_logo_path,
            t_local.venue_id AS local_venue_id,
            t_local.current_season_id AS local_current_season_id,
            t_local.gender AS local_gender,
            t_local.team_type AS local_team_type,
            f_local.local_team_formation,

            -- Visitor Team Data
            t_vis.team_id AS visitor_team_id,
            t_vis.name AS visitor_name,
            t_vis.shortcode AS visitor_shortcode,
            t_vis.twitter AS visitor_twitter,
            t_vis.country_id AS visitor_country_id,
            t_vis.national_team AS visitor_national_team,
            t_vis.founded AS visitor_founded,
            t_vis.logo_path AS visitor_logo_path,
            t_vis.venue_id AS visitor_venue_id,
            t_vis.current_season_id AS visitor_current_season_id,
            t_vis.gender AS visitor_gender,
            t_vis.team_type AS visitor_team_type,
            f_vis.visitor_team_formation,

            -- Player Data
            p.player_id,
            p.player_name,
            p.logo_path AS player_logo_path,
            l.number,
            l.position,
            l.formation_position,
            l.captain,
            l.type,
            l.team_id AS player_team_id
        FROM {$formations_table} f_local
        JOIN {$teams_table} t_local ON f_local.local_team_id = t_local.team_id
        JOIN {$formations_table} f_vis ON f_local.match_id = f_vis.match_id
        JOIN {$teams_table} t_vis ON f_vis.visitor_team_id = t_vis.team_id
        JOIN {$lineup_table} l ON l.match_id = f_local.match_id
        JOIN {$players_table} p ON l.player_id = p.player_id
        WHERE f_local.match_id = %d
        ",
        $match_id
    );

    $team_player_results = $wpdb->get_results($team_player_query, ARRAY_A);

    // Initialize the data structure.
    $data = [
        'localTeam' => [
            'teamData' => [],
            'players'  => []
        ],
        'visitorTeam' => [
            'teamData' => [],
            'players'  => []
        ],
        'matchEvents' => []
    ];

    $local_team_set    = false;
    $visitor_team_set  = false;
    $local_player_ids  = [];
    $visitor_player_ids = [];

    foreach ($team_player_results as $row) {
        if (!$local_team_set) {
            $data['localTeam']['teamData'] = [
                'TeamId'          => (int)$row['local_team_id'],
                'Name'            => $row['local_name'],
                'ShortCode'       => $row['local_shortcode'],
                'Twitter'         => $row['local_twitter'],
                'CountryId'       => (int)$row['local_country_id'],
                'NationalTeam'    => (bool)$row['local_national_team'],
                'Founded'         => (int)$row['local_founded'],
                'LogoPath'        => $row['local_logo_path'],
                'VenueId'         => (int)$row['local_venue_id'],
                'CurrentSeasonId' => (int)$row['local_current_season_id'],
                'Gender'          => $row['local_gender'],
                'TeamType'        => $row['local_team_type'],
                'TeamFormation'   => $row['local_team_formation']
            ];
            $local_team_set = true;
        }

        if (!$visitor_team_set) {
            $data['visitorTeam']['teamData'] = [
                'TeamId'          => (int)$row['visitor_team_id'],
                'Name'            => $row['visitor_name'],
                'ShortCode'       => $row['visitor_shortcode'],
                'Twitter'         => $row['visitor_twitter'],
                'CountryId'       => (int)$row['visitor_country_id'],
                'NationalTeam'    => (bool)$row['visitor_national_team'],
                'Founded'         => (int)$row['visitor_founded'],
                'LogoPath'        => $row['visitor_logo_path'],
                'VenueId'         => (int)$row['visitor_venue_id'],
                'CurrentSeasonId' => (int)$row['visitor_current_season_id'],
                'Gender'          => $row['visitor_gender'],
                'TeamType'        => $row['visitor_team_type'],
                'TeamFormation'   => $row['visitor_team_formation']
            ];
            $visitor_team_set = true;
        }

        // Assign players to the appropriate team.
        if ((int)$row['player_team_id'] === (int)$row['local_team_id'] && !in_array($row['player_id'], $local_player_ids)) {
            $data['localTeam']['players'][] = [
                'PlayerId'          => (int)$row['player_id'],
                'PlayerName'        => $row['player_name'],
                'Number'            => (int)$row['number'],
                'Position'          => $row['position'],
                'FormationPosition' => $row['formation_position'],
                'Captain'           => (bool)$row['captain'],
                'Type'              => $row['type'],
                'LogoPath'          => $row['player_logo_path']
            ];
            $local_player_ids[] = $row['player_id'];
        } elseif ((int)$row['player_team_id'] === (int)$row['visitor_team_id'] && !in_array($row['player_id'], $visitor_player_ids)) {
            $data['visitorTeam']['players'][] = [
                'PlayerId'          => (int)$row['player_id'],
                'PlayerName'        => $row['player_name'],
                'Number'            => (int)$row['number'],
                'Position'          => $row['position'],
                'FormationPosition' => $row['formation_position'],
                'Captain'           => (bool)$row['captain'],
                'Type'              => $row['type'],
                'LogoPath'          => $row['player_logo_path']
            ];
            $visitor_player_ids[] = $row['player_id'];
        }
    }

    // Query for match events.
    $events_query = $wpdb->prepare(
        "
        SELECT 
            e.event_id,
            e.team_id,
            e.type,
            e.match_id,
            e.player_id,
            e.related_player_id,
            e.minute,
            e.extra_minute
        FROM {$events_table} e
        WHERE e.match_id = %d
        ",
        $match_id
    );

    $events_results = $wpdb->get_results($events_query, ARRAY_A);

    foreach ($events_results as $event) {
        $data['matchEvents'][] = [
            'EventId'         => (int)$event['event_id'],
            'TeamId'          => (int)$event['team_id'],
            'Type'            => $event['type'],
            'MatchId'         => (int)$event['match_id'],
            'PlayerId'        => (int)$event['player_id'],
            'RelatedPlayerId' => !empty($event['related_player_id']) ? (int)$event['related_player_id'] : null,
            'Minute'          => (int)$event['minute'],
            'ExtraMinute'     => !is_null($event['extra_minute']) ? (int)$event['extra_minute'] : null
        ];
    }

    // Cache the assembled data in a transient for 300 seconds (5 minutes).
    set_transient($transient_key, $data, 300);

    return $data;
}*/
	
	
	
	
	public function get_match_lineup($match_id) {
    global $wpdb;

    // Define a unique transient key for the match lineup.
    $transient_key = 'match_lineup_' . $match_id;
    
    // Try to retrieve cached lineup data.
    $cached_data = get_transient($transient_key);
    if ($cached_data !== false) {
        return $cached_data;
    }

    /**
     * STEP 1: Since the cache is not available, immediately call the API to fetch the lineup.
     */
    try {
        $lineup_data = $this->fetch_lineup_from_api($match_id);
        if ($lineup_data && !isset($lineup_data['error'])) {
            error_log("Lineup fetched and inserted successfully for match ID: {$match_id}");
        } else {
            $error_message = isset($lineup_data['error']) ? $lineup_data['error'] : 'Unknown error while fetching lineup.';
            error_log("Failed to fetch lineup for match ID {$match_id}: {$error_message}");
        }
    } catch (Exception $e) {
        error_log("Exception while fetching lineup for match ID {$match_id}: " . $e->getMessage());
    }

    /**
     * STEP 2: Query the database for the latest lineup data.
     */
    // Define table names.
    $lineup_table      = $wpdb->prefix . 'livefot_lineups';
    $players_table     = $wpdb->prefix . 'livefot_players';
    $teams_table       = $wpdb->prefix . 'livefot_teams';
    $formations_table  = $wpdb->prefix . 'livefot_formations';
    $events_table      = $wpdb->prefix . 'livefot_events';

    // Query to fetch team and player data.
    $team_player_query = $wpdb->prepare(
        "
        SELECT 
            -- Local Team Data
            t_local.team_id AS local_team_id,
            t_local.name AS local_name,
            t_local.shortcode AS local_shortcode,
            t_local.twitter AS local_twitter,
            t_local.country_id AS local_country_id,
            t_local.national_team AS local_national_team,
            t_local.founded AS local_founded,
            t_local.logo_path AS local_logo_path,
            t_local.venue_id AS local_venue_id,
            t_local.current_season_id AS local_current_season_id,
            t_local.gender AS local_gender,
            t_local.team_type AS local_team_type,
            f_local.local_team_formation,

            -- Visitor Team Data
            t_vis.team_id AS visitor_team_id,
            t_vis.name AS visitor_name,
            t_vis.shortcode AS visitor_shortcode,
            t_vis.twitter AS visitor_twitter,
            t_vis.country_id AS visitor_country_id,
            t_vis.national_team AS visitor_national_team,
            t_vis.founded AS visitor_founded,
            t_vis.logo_path AS visitor_logo_path,
            t_vis.venue_id AS visitor_venue_id,
            t_vis.current_season_id AS visitor_current_season_id,
            t_vis.gender AS visitor_gender,
            t_vis.team_type AS visitor_team_type,
            f_vis.visitor_team_formation,

            -- Player Data
            p.player_id,
            p.player_name,
            p.logo_path AS player_logo_path,
            l.number,
            l.position,
            l.formation_position,
            l.captain,
            l.type,
            l.team_id AS player_team_id
        FROM {$formations_table} f_local
        JOIN {$teams_table} t_local ON f_local.local_team_id = t_local.team_id
        JOIN {$formations_table} f_vis ON f_local.match_id = f_vis.match_id
        JOIN {$teams_table} t_vis ON f_vis.visitor_team_id = t_vis.team_id
        JOIN {$lineup_table} l ON l.match_id = f_local.match_id
        JOIN {$players_table} p ON l.player_id = p.player_id
        WHERE f_local.match_id = %d
        ",
        $match_id
    );

    $team_player_results = $wpdb->get_results($team_player_query, ARRAY_A);

    // Initialize the data structure.
    $data = [
        'localTeam' => [
            'teamData' => [],
            'players'  => []
        ],
        'visitorTeam' => [
            'teamData' => [],
            'players'  => []
        ],
        'matchEvents' => []
    ];

    $local_team_set     = false;
    $visitor_team_set   = false;
    $local_player_ids   = [];
    $visitor_player_ids = [];

    foreach ($team_player_results as $row) {
        if (!$local_team_set) {
            $data['localTeam']['teamData'] = [
                'TeamId'          => (int)$row['local_team_id'],
                'Name'            => $row['local_name'],
                'ShortCode'       => $row['local_shortcode'],
                'Twitter'         => $row['local_twitter'],
                'CountryId'       => (int)$row['local_country_id'],
                'NationalTeam'    => (bool)$row['local_national_team'],
                'Founded'         => (int)$row['local_founded'],
                'LogoPath'        => $row['local_logo_path'],
                'VenueId'         => (int)$row['local_venue_id'],
                'CurrentSeasonId' => (int)$row['local_current_season_id'],
                'Gender'          => $row['local_gender'],
                'TeamType'        => $row['local_team_type'],
                'TeamFormation'   => $row['local_team_formation']
            ];
            $local_team_set = true;
        }

        if (!$visitor_team_set) {
            $data['visitorTeam']['teamData'] = [
                'TeamId'          => (int)$row['visitor_team_id'],
                'Name'            => $row['visitor_name'],
                'ShortCode'       => $row['visitor_shortcode'],
                'Twitter'         => $row['visitor_twitter'],
                'CountryId'       => (int)$row['visitor_country_id'],
                'NationalTeam'    => (bool)$row['visitor_national_team'],
                'Founded'         => (int)$row['visitor_founded'],
                'LogoPath'        => $row['visitor_logo_path'],
                'VenueId'         => (int)$row['visitor_venue_id'],
                'CurrentSeasonId' => (int)$row['visitor_current_season_id'],
                'Gender'          => $row['visitor_gender'],
                'TeamType'        => $row['visitor_team_type'],
                'TeamFormation'   => $row['visitor_team_formation']
            ];
            $visitor_team_set = true;
        }

        // Assign players to the appropriate team.
        if ((int)$row['player_team_id'] === (int)$row['local_team_id'] && !in_array($row['player_id'], $local_player_ids)) {
            $data['localTeam']['players'][] = [
                'PlayerId'          => (int)$row['player_id'],
                'PlayerName'        => $row['player_name'],
                'Number'            => (int)$row['number'],
                'Position'          => $row['position'],
                'FormationPosition' => $row['formation_position'],
                'Captain'           => (bool)$row['captain'],
                'Type'              => $row['type'],
                'LogoPath'          => $row['player_logo_path']
            ];
            $local_player_ids[] = $row['player_id'];
        } elseif ((int)$row['player_team_id'] === (int)$row['visitor_team_id'] && !in_array($row['player_id'], $visitor_player_ids)) {
            $data['visitorTeam']['players'][] = [
                'PlayerId'          => (int)$row['player_id'],
                'PlayerName'        => $row['player_name'],
                'Number'            => (int)$row['number'],
                'Position'          => $row['position'],
                'FormationPosition' => $row['formation_position'],
                'Captain'           => (bool)$row['captain'],
                'Type'              => $row['type'],
                'LogoPath'          => $row['player_logo_path']
            ];
            $visitor_player_ids[] = $row['player_id'];
        }
    }

    // Query for match events.
    $events_query = $wpdb->prepare(
        "
        SELECT 
            e.event_id,
            e.team_id,
            e.type,
            e.match_id,
            e.player_id,
            e.related_player_id,
            e.minute,
            e.extra_minute
        FROM {$events_table} e
        WHERE e.match_id = %d
        ",
        $match_id
    );

    $events_results = $wpdb->get_results($events_query, ARRAY_A);

    foreach ($events_results as $event) {
        $data['matchEvents'][] = [
            'EventId'         => (int)$event['event_id'],
            'TeamId'          => (int)$event['team_id'],
            'Type'            => $event['type'],
            'MatchId'         => (int)$event['match_id'],
            'PlayerId'        => (int)$event['player_id'],
            'RelatedPlayerId' => !empty($event['related_player_id']) ? (int)$event['related_player_id'] : null,
            'Minute'          => (int)$event['minute'],
            'ExtraMinute'     => !is_null($event['extra_minute']) ? (int)$event['extra_minute'] : null
        ];
    }
    
    /**
     * STEP 3: Determine cache expiration based on match time status.
     * - By default, the cache will expire after a duration defined by the user setting ('livefot_interval_lineups').
     * - If the match was started yesterday and has a status of FT, AET, Cancelled, or FT_PEN,
     *   set the cache to expire in 2 hours (7200 seconds).
     */
    $default_lineups_interval = intval(get_option('livefot_interval_lineups', 300));
    $cache_expiration = $default_lineups_interval; // default expiration in seconds
    
    // Define the time table name.
    $time_table = $wpdb->prefix . 'livefot_time';
    // Retrieve the starting datetime and status for the match.
    $time_sql = $wpdb->prepare(
        "SELECT status, starting_at_datetime FROM {$time_table} WHERE fixture_id = %d",
        $match_id
    );
    $time_data = $wpdb->get_row($time_sql);
    
    if ($time_data) {
        // Desired statuses.
        $desired_statuses = array('FT', 'AET', 'Cancelled', 'FT_PEN');
        if (in_array($time_data->status, $desired_statuses)) {
            // Convert starting_at_datetime to a timestamp.
            $match_start = strtotime($time_data->starting_at_datetime);
            // Determine yesterday's range: from yesterday midnight to today midnight.
            $yesterday_start = strtotime("yesterday");
            $today_start = strtotime("today");
            if ($match_start >= $yesterday_start && $match_start < $today_start) {
                $cache_expiration = 7200; // 2 hours in seconds.
            }
        }
    }
    
    // Cache the assembled data using the determined expiration time.
    set_transient($transient_key, $data, $cache_expiration);
    
    return $data;
}


	
	
	
	
	



    /**
     * Fetch lineup data from the API.
     *
     * @param int $match_id Match ID.
     * @return array Lineup data or error message.
     */
   private function fetch_lineup_from_api($match_id) {
	   
	   if (empty($this->api_url)) {
       // error_log("API URL is null or empty. Skipping API call for fetch_lineup_from_api.");
        // You can return null or any structure you prefer
        return null;
    }
	   
    $url = add_query_arg([
        'api_key' => $this->api_key,
        'endpoint' => 'lineups' // Add the 'endpoint' parameter
    ], $this->api_url . "wp/fixture/{$match_id}/lineup");

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON response'];
    }

    // Validate response structure
    if (!isset($data['data']['localTeam']) || !isset($data['data']['visitorTeam'])) {
        return ['error' => 'Invalid lineup data structure'];
    }
	   
	   $save_result = $this->save_lineup_to_db($match_id, $data);
	   

  //  return $data['data'];
}

	
	public function save_lineup_to_db($match_id, $lineup) {
    global $wpdb;

    // Define table names
    $lineup_table     = $wpdb->prefix . 'livefot_lineups';
    $players_table    = $wpdb->prefix . 'livefot_players';
    // Removed teams_table since teams are handled by another function
    $formations_table = $wpdb->prefix . 'livefot_formations';
    $events_table     = $wpdb->prefix . 'livefot_events';
    $api_calls        = $wpdb->prefix . 'livefot_api_calls';

    // Start a transaction
    $wpdb->query('START TRANSACTION');

    try {
        $endpoint_name = 'lineups';

        // Log API call
        // We pull 'remainingCalls' and 'status' from the top level of the response.
        $wpdb->replace(
            $api_calls,
            [
                'endpoint_name'   => $endpoint_name,
                'remaining_calls' => $lineup['remainingCalls'] ?? null,  // from top level
                'status'          => $lineup['status'] ?? 'N/A'
            ],
            ['%s', '%d', '%s']
        );

        // -----------------------------------------------------
        // 1) DELETE OLD DATA FOR THIS MATCH (lineup, formation, events)
        //    We do not delete from players/teams because they are usually
        //    re-used across matches; only match-specific data is purged.
        // -----------------------------------------------------
        $wpdb->delete($lineup_table,  [ 'match_id' => $match_id ], [ '%d' ]);
        // Uncomment the following lines if you decide to handle formations and events
        // $wpdb->delete($formations_table, [ 'match_id' => $match_id ], [ '%d' ]);
        // $wpdb->delete($events_table,     [ 'match_id' => $match_id ], [ '%d' ]);

        // Access team data via $lineup['data']['localTeam'] and $lineup['data']['visitorTeam'] 
        $local_team_data    = $lineup['data']['localTeam']['teamData']    ?? null;
        $visitor_team_data  = $lineup['data']['visitorTeam']['teamData']  ?? null;

        // Access players via $lineup['data']['localTeam']['players'] and $lineup['data']['visitorTeam']['players'] 
        $local_players      = $lineup['data']['localTeam']['players']      ?? [];
        $visitor_players    = $lineup['data']['visitorTeam']['players']    ?? [];

        // Insert or update local players + lineup
        if (!empty($local_team_data) && !empty($local_players)) {
            $local_team_id = $local_team_data['TeamId'];
            foreach ($local_players as $player) {
                $wpdb->replace(
                    $players_table,
                    [
                        'player_id'   => $player['PlayerId'],
                        'player_name' => $player['PlayerName'],
                        'logo_path'   => $player['LogoPath']
                    ],
                    ['%d','%s','%s']
                );

                $wpdb->replace(
                    $lineup_table,
                    [
                        'match_id'           => $match_id,
                        'team_id'            => $local_team_id,
                        'player_id'          => $player['PlayerId'],
                        'number'             => $player['Number'],
                        'position'           => $player['Position'],
                        'formation_position' => $player['FormationPosition'],
                        'captain'            => $player['Captain'],
                        'type'               => $player['Type']
                    ],
                    ['%d','%d','%d','%d','%s','%d','%d','%s']
                );
            }
        }

        // Insert or update visitor players + lineup
        if (!empty($visitor_team_data) && !empty($visitor_players)) {
            $visitor_team_id = $visitor_team_data['TeamId'];
            foreach ($visitor_players as $player) {
                $wpdb->replace(
                    $players_table,
                    [
                        'player_id'   => $player['PlayerId'],
                        'player_name' => $player['PlayerName'],
                        'logo_path'   => $player['LogoPath']
                    ],
                    ['%d','%s','%s']
                );

                $wpdb->replace(
                    $lineup_table,
                    [
                        'match_id'           => $match_id,
                        'team_id'            => $visitor_team_id,
                        'player_id'          => $player['PlayerId'],
                        'number'             => $player['Number'],
                        'position'           => $player['Position'],
                        'formation_position' => $player['FormationPosition'],
                        'captain'            => $player['Captain'],
                        'type'               => $player['Type']
                    ],
                    ['%d','%d','%d','%d','%s','%d','%d','%s']
                );
            }
        }

        // Access formations via the 'TeamFormation' field in each team's 'teamData'
        $wpdb->replace(
            $formations_table,
            [
                'match_id'               => $match_id,
                'local_team_id'          => $local_team_data['TeamId']   ?? null,
                'local_team_formation'   => $local_team_data['TeamFormation']   ?? null,
                'visitor_team_id'        => $visitor_team_data['TeamId'] ?? null,
                'visitor_team_formation' => $visitor_team_data['TeamFormation'] ?? null
            ],
            ['%d','%d','%s','%d','%s']
        );

       
        // Commit the transaction
        $wpdb->query('COMMIT');

    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        error_log('Error saving lineup to DB: ' . $e->getMessage());
        return ['error' => $e->getMessage()];
    }

    return ['status' => 'success'];
}


	private function fetch_events_from_api($match_id) {
    if (empty($this->api_url)) {
        // Log or handle the missing API URL as needed.
        return null;
    }

    $url = add_query_arg([
        'api_key'  => $this->api_key,
        'endpoint' => 'match_events' // Specify the 'events' endpoint
    ], $this->api_url . "wp/fixture/{$match_id}/events");

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON response'];
    }

    // Validate response structure
    if (!isset($data['status']) || $data['status'] !== 'success') {
        return ['error' => 'API response status not successful'];
    }

    if (!isset($data['data'])) {
        return ['error' => 'Missing data in API response'];
    }

    // Pass only the 'data' portion to the save method
    $save_result = $this->save_events_and_players_to_db($match_id, $data);

    if (isset($save_result['error'])) {
        return ['error' => $save_result['error']];
    }

    return $data['data'];
}

	


	
	
	
	public function save_events_and_players_to_db($match_id, $data) {
    global $wpdb;

    // Define table names
    $events_table    = $wpdb->prefix . 'livefot_events';
    $players_table   = $wpdb->prefix . 'livefot_players';
    $teams_table     = $wpdb->prefix . 'livefot_teams';
    $api_calls_table = $wpdb->prefix . 'livefot_api_calls';

    // Start a transaction
    $wpdb->query('START TRANSACTION');

    try {
        // --------------------------
        // 1) LOG API CALL
        // --------------------------
        $endpoint_name = 'match_events'; // Name of the endpoint

        $wpdb->replace(
            $api_calls_table,
            [
                'endpoint_name'   => $endpoint_name,
                'remaining_calls' => isset($data['remainingCalls']) ? intval($data['remainingCalls']) : null,
                'status'          => isset($data['status']) ? sanitize_text_field($data['status']) : 'N/A'
                
            ],
            [
                '%s',
                '%d',
                '%s',
                '%s'
            ]
        );

        // Ensure 'data' key exists and is an array
        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new Exception('Invalid data structure: missing "data" key.');
        }

        // --------------------------
        // 2) DELETE OLD EVENTS FOR THIS MATCH
        // --------------------------
        $wpdb->delete($events_table, [ 'match_id' => $match_id ], [ '%d' ]);

        // --------------------------
        // 3) INSERT OR UPDATE TEAMS
        // --------------------------
        if (isset($data['data']['teams']) && is_array($data['data']['teams'])) {
            foreach ($data['data']['teams'] as $team) {
                $wpdb->replace(
                    $teams_table,
                    [
                        'team_id'           => intval($team['TeamId']),
                        'name'              => sanitize_text_field($team['Name']),
                        'shortcode'         => isset($team['ShortCode']) ? sanitize_text_field($team['ShortCode']) : null,
                        'twitter'           => isset($team['Twitter']) ? sanitize_text_field($team['Twitter']) : null,
                        'country_id'        => intval($team['CountryId']),
                        'national_team'     => isset($team['NationalTeam']) ? (bool) $team['NationalTeam'] : false,
                        'founded'           => isset($team['Founded']) ? intval($team['Founded']) : null,
                        'logo_path'         => isset($team['LogoPath']) ? esc_url_raw($team['LogoPath']) : null,
                        'venue_id'          => isset($team['VenueId']) ? intval($team['VenueId']) : null,
                        'current_season_id' => isset($team['CurrentSeasonId']) ? intval($team['CurrentSeasonId']) : null,
                        'gender'            => isset($team['Gender']) ? sanitize_text_field($team['Gender']) : null,
                        'team_type'         => isset($team['Type']) ? sanitize_text_field($team['Type']) : null
                    ],
                    [
                        '%d', '%s', '%s', '%s', '%d', '%d', '%d',
                        '%s', '%d', '%d', '%s', '%s'
                    ]
                );
            }
        }

        // --------------------------
        // 4) INSERT OR UPDATE Players
        // --------------------------
        if (isset($data['data']['players']) && is_array($data['data']['players'])) {
            foreach ($data['data']['players'] as $player) {
                $wpdb->replace(
                    $players_table,
                    [
                        'player_id'   => intval($player['PlayerId']),
                        'player_name' => sanitize_text_field($player['DisplayName']),
                        'logo_path'   => isset($player['ImagePath']) ? esc_url_raw($player['ImagePath']) : null
                    ],
                    [ '%d', '%s', '%s' ]
                );
            }
        }

        // --------------------------
        // 5) INSERT Events
        // --------------------------
        if (isset($data['data']['matchEvents']) && is_array($data['data']['matchEvents'])) {
            foreach ($data['data']['matchEvents'] as $event) {
                $wpdb->replace(
                    $events_table,
                    [
                        'event_id'          => intval($event['EventId']),
                        'match_id'          => intval($event['MatchId']),
                        'team_id'           => intval($event['TeamId']),
                        'player_id'         => isset($event['PlayerId']) ? intval($event['PlayerId']) : null,
                        'related_player_id' => isset($event['RelatedPlayerId']) ? intval($event['RelatedPlayerId']) : null,
                        'type'              => sanitize_text_field($event['Type']),
                        'minute'            => isset($event['Minute']) ? intval($event['Minute']) : null,
                        'extra_minute'      => isset($event['ExtraMinute']) ? intval($event['ExtraMinute']) : null
                    ],
                    [
                        '%d', '%d', '%d', '%d', '%d',
                        '%s', '%d', '%d'
                    ]
                );
            }
        }

        // Commit the transaction
        $wpdb->query('COMMIT');

    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        error_log('Error saving events and players to DB: ' . $e->getMessage());
        return ['error' => $e->getMessage()];
    }

    return ['status' => 'success'];
}

	
	/**
 * Save events, players, teams data, and log API calls to the database.
 *
 * @param int   $match_id The ID of the match.
 * @param array $data     The data array containing matchEvents, players, teams, remainingCalls, and status.
 * @return array An array indicating success or containing an error message.
 */

	
private function fetch_stats_from_api($match_id) {
    global $wpdb; // Ensure you have access to the WordPress database object

    // 1. Ensure API URL is set
    if (empty($this->api_url)) {
        // Log or handle the error as needed
        error_log("API URL is null or empty. Skipping API call for fetch_stats_from_api.");
        return null;
    }

    // 2. Prepare the table names
    $team_stats_table = $wpdb->prefix . 'livefot_team_stats';
    $fixtures_table   = $wpdb->prefix . 'livefot_fixtures'; // Fixtures table

    // 3. Check if there are any stats records for this fixture
    $existing_stats_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM `$team_stats_table` WHERE fixture_id = %d",
            $match_id
        )
    );

    if ($existing_stats_count == 0) {
        // No existing stats, insert initial records with zeroed stats
        // Fetch team IDs from the fixtures table
        $fixture = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT localteam_id, visitorteam_id FROM `$fixtures_table` WHERE fixture_id = %d",
                $match_id
            ),
            ARRAY_A
        );

        if (!$fixture) {
            error_log("Fixture ID {$match_id} not found in fixtures table. Cannot initialize team stats.");
            return ['error' => 'Fixture not found'];
        }

        // Insert initial stats for both teams
        $this->insert_initial_stats($match_id, $fixture['localteam_id']);
        $this->insert_initial_stats($match_id, $fixture['visitorteam_id']);

        // Log the initialization
        error_log("Initialized team stats for fixture ID {$match_id} with zeroed stats.");
    }

    // 4. Retrieve the interval for the 'matches' endpoint (default to 120 seconds if not set)
    $stats_interval_sec = intval(get_option('livefot_interval_stats', 120));

    // 5. Prepare and execute the SQL query to check the last_updated timestamp
    $query = $wpdb->prepare(
        "SELECT COUNT(*) FROM `$team_stats_table` 
         WHERE fixture_id = %d
         AND last_updated < (NOW() - INTERVAL %d SECOND)",
        $match_id,
        $stats_interval_sec
    );

    $count = $wpdb->get_var($query);

    // 6. If no records are older than the interval, skip fetching
    if ($count == 0) {
        // Optionally, log this event for debugging
        error_log("Stats for match ID {$match_id} are up-to-date. Skipping API call.");
        return null;
    }

    // 7. Construct the API URL with query parameters
    $url = add_query_arg([
        'api_key'   => $this->api_key,
        'endpoint'  => 'match_stats' // Specify the 'match_stats' endpoint
    ], trailingslashit($this->api_url) . "wp/match/{$match_id}/stats");

    // 8. Make the API request
    $response = wp_remote_get($url);

    // 9. Handle potential errors from the API request
    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    // 10. Retrieve and decode the response body
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // 11. Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON response'];
    }

    // 12. Validate the structure of the response data
    if (!isset($data['data']['localTeam']) || !isset($data['data']['visitorTeam'])) {
        return ['error' => 'Invalid stats data structure'];
    }

    // 13. Save the stats to the database
    $save_result = $this->save_stats_to_db($match_id, $data);

    // 14. Handle potential errors from the save operation
    if (isset($save_result['error'])) {
        return ['error' => $save_result['error']];
    }

    // 15. Return the fetched data
    return $data['data'];
}


	
	/**
 * Helper function to insert initial zeroed stats for a team.
 *
 * @param int $match_id The fixture ID.
 * @param int $team_id The team ID.
 * @return bool|WP_Error True on success, WP_Error on failure.
 */
private function insert_initial_stats($match_id, $team_id) {
    global $wpdb;

    $team_stats_table = $wpdb->prefix . 'livefot_team_stats';

    $initial_stats = [
        'fixture_id'          => $match_id,
        'team_id'             => $team_id,
        'fouls'               => 0,
        'corners'             => 0,
        'offsides'            => 0,
        'possession_time'     => 0,
        'yellow_cards'        => 0,
        'red_cards'           => 0,
        'yellow_red_cards'    => 0,
        'saves'               => 0,
        'substitutions'       => 0,
        'goal_kick'           => 0,
        'goal_attempts'       => 0,
        'free_kick'           => 0,
        'throw_in'            => 0,
        'ball_safe'           => 0,
        'goals'               => 0,
        'penalties'           => 0,
        'injuries'            => 0,
        'tackles'             => 0,
        'attacks'             => 0,
        'dangerous_attacks'   => 0,
        'passes_total'        => 0,
        'passes_accurate'     => 0,
        'passes_percentage'   => 0,
        'shots_total'         => 0,
        'shots_ongoal'        => 0,
        'shots_blocked'       => 0,
        'shots_offgoal'       => 0,
        'shots_insidebox'     => 0,
        'shots_outsidebox'    => 0//,
       // 'last_updated'        => current_time('mysql') // Initialize last_updated
    ];

    $inserted = $wpdb->insert(
        $team_stats_table,
        $initial_stats,
        [
            '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d',
            '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d',
            '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d',
            '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s'
        ]
    );

    if ($inserted === false) {
        // Log the error
        error_log("Failed to insert initial stats for team ID {$team_id} in fixture ID {$match_id}.");
        return new WP_Error('db_insert_error', 'Failed to insert initial stats.');
    }

    // Optionally, log the successful insertion
    error_log("Inserted initial zeroed stats for team ID {$team_id} in fixture ID {$match_id}.");

    return true;
}


	
	public function save_stats_to_db($match_id, $stats) {
    global $wpdb;

    // Define table names
    $team_stats_table = $wpdb->prefix . 'livefot_team_stats';
    $api_calls        = $wpdb->prefix . 'livefot_api_calls';

    // Start a transaction
    $wpdb->query('START TRANSACTION');

    try {
        $endpoint_name = 'match_stats';

        // Log API call
        $wpdb->replace(
            $api_calls,
            [
                'endpoint_name'   => $endpoint_name,
                'remaining_calls' => $stats['remainingCalls'] ?? null,  // from top level
                'status'          => $stats['status'] ?? 'N/A'
            ],
            ['%s', '%d', '%s', '%s']
        );

        // Access team data via $stats['data']['localTeam'] and $stats['data']['visitorTeam']
        $local_team_data   = $stats['data']['localTeam']['stats']   ?? null;
        $visitor_team_data = $stats['data']['visitorTeam']['stats'] ?? null;

        // Update local team stats
        if (!empty($local_team_data)) {
            $updated = $wpdb->update(
                $team_stats_table,
                [
                    'fouls'               => $local_team_data['Fouls'] ?? 0,
                    'corners'             => $local_team_data['Corners'] ?? 0,
                    'offsides'            => $local_team_data['Offsides'] ?? 0,
                    'possession_time'     => $local_team_data['Possessiontime'] ?? 0,
                    'yellow_cards'        => $local_team_data['Yellowcards'] ?? 0,
                    'red_cards'           => $local_team_data['Redcards'] ?? 0,
                    'yellow_red_cards'    => $local_team_data['Yellowredcards'] ?? 0,
                    'saves'               => $local_team_data['Saves'] ?? 0,
                    'substitutions'       => $local_team_data['Substitutions'] ?? 0,
                    'goal_kick'           => $local_team_data['Goal_kick'] ?? 0,
                    'goal_attempts'       => $local_team_data['Goal_attempts'] ?? 0,
                    'free_kick'           => $local_team_data['Free_kick'] ?? 0,
                    'throw_in'            => $local_team_data['Throw_in'] ?? 0,
                    'ball_safe'           => $local_team_data['Ball_safe'] ?? 0,
                    'goals'               => $local_team_data['Goals'] ?? 0,
                    'penalties'           => $local_team_data['Penalties'] ?? 0,
                    'injuries'            => $local_team_data['Injuries'] ?? 0,
                    'tackles'             => $local_team_data['Tackles'] ?? 0,
                    'attacks'             => $local_team_data['Attacks'] ?? 0,
                    'dangerous_attacks'   => $local_team_data['Dangerous_attacks'] ?? 0,
                    'passes_total'        => $local_team_data['Passes']['Total'] ?? 0,
                    'passes_accurate'     => $local_team_data['Passes']['Accurate'] ?? 0,
                    'passes_percentage'   => $local_team_data['Passes']['Percentage'] ?? 0,
                    'shots_total'         => $local_team_data['Shots']['Total'] ?? 0,
                    'shots_ongoal'        => $local_team_data['Shots']['Ongoal'] ?? 0,
                    'shots_blocked'       => $local_team_data['Shots']['Blocked'] ?? 0,
                    'shots_offgoal'       => $local_team_data['Shots']['Offgoal'] ?? 0,
                    'shots_insidebox'     => $local_team_data['Shots']['Insidebox'] ?? 0,
                    'shots_outsidebox'    => $local_team_data['Shots']['Outsidebox'] ?? 0//,
               //     'last_updated'        => current_time('mysql') // Update the last_updated timestamp
                ],
                [
                    'fixture_id' => $match_id,
                    'team_id'    => $stats['data']['localTeam']['teamId'] ?? 0
                ],
                [
                    '%d','%d','%d','%d','%d','%d','%d','%d',
                    '%d','%d','%d','%d','%d','%d','%d','%d',
                    '%d','%d','%d','%d','%d','%d','%d','%d',
                    '%d','%d','%d','%d','%d','%d','%d','%s'
                ],
                [
                    '%d',
                    '%d'
                ]
            );

            // If the update affected no rows, it might be an initial insert. Insert instead.
            if ($updated === false || $wpdb->rows_affected == 0) {
                // Insert initial stats if not already present
                $insert_result = $this->insert_initial_stats($match_id, $stats['data']['localTeam']['teamId']);
                if (is_wp_error($insert_result)) {
                    throw new Exception($insert_result->get_error_message());
                }

                // Now perform the update again
                $updated = $wpdb->update(
                    $team_stats_table,
                    [
                        'fouls'               => $local_team_data['Fouls'] ?? 0,
                        'corners'             => $local_team_data['Corners'] ?? 0,
                        'offsides'            => $local_team_data['Offsides'] ?? 0,
                        'possession_time'     => $local_team_data['Possessiontime'] ?? 0,
                        'yellow_cards'        => $local_team_data['Yellowcards'] ?? 0,
                        'red_cards'           => $local_team_data['Redcards'] ?? 0,
                        'yellow_red_cards'    => $local_team_data['Yellowredcards'] ?? 0,
                        'saves'               => $local_team_data['Saves'] ?? 0,
                        'substitutions'       => $local_team_data['Substitutions'] ?? 0,
                        'goal_kick'           => $local_team_data['Goal_kick'] ?? 0,
                        'goal_attempts'       => $local_team_data['Goal_attempts'] ?? 0,
                        'free_kick'           => $local_team_data['Free_kick'] ?? 0,
                        'throw_in'            => $local_team_data['Throw_in'] ?? 0,
                        'ball_safe'           => $local_team_data['Ball_safe'] ?? 0,
                        'goals'               => $local_team_data['Goals'] ?? 0,
                        'penalties'           => $local_team_data['Penalties'] ?? 0,
                        'injuries'            => $local_team_data['Injuries'] ?? 0,
                        'tackles'             => $local_team_data['Tackles'] ?? 0,
                        'attacks'             => $local_team_data['Attacks'] ?? 0,
                        'dangerous_attacks'   => $local_team_data['Dangerous_attacks'] ?? 0,
                        'passes_total'        => $local_team_data['Passes']['Total'] ?? 0,
                        'passes_accurate'     => $local_team_data['Passes']['Accurate'] ?? 0,
                        'passes_percentage'   => $local_team_data['Passes']['Percentage'] ?? 0,
                        'shots_total'         => $local_team_data['Shots']['Total'] ?? 0,
                        'shots_ongoal'        => $local_team_data['Shots']['Ongoal'] ?? 0,
                        'shots_blocked'       => $local_team_data['Shots']['Blocked'] ?? 0,
                        'shots_offgoal'       => $local_team_data['Shots']['Offgoal'] ?? 0,
                        'shots_insidebox'     => $local_team_data['Shots']['Insidebox'] ?? 0,
                        'shots_outsidebox'    => $local_team_data['Shots']['Outsidebox'] ?? 0//,
                      //  'last_updated'        => current_time('mysql') // Update the last_updated timestamp
                    ],
                    [
                        'fixture_id' => $match_id,
                        'team_id'    => $stats['data']['localTeam']['teamId'] ?? 0
                    ],
                    [
                        '%d','%d','%d','%d','%d','%d','%d','%d',
                        '%d','%d','%d','%d','%d','%d','%d','%d',
                        '%d','%d','%d','%d','%d','%d','%d','%d',
                        '%d','%d','%d','%d','%d','%d','%d','%s'
                    ],
                    [
                        '%d',
                        '%d'
                    ]
                );

                if ($updated === false) {
                    throw new Exception("Failed to update local team stats after inserting initial stats.");
                }
            }
        }

        // Update visitor team stats
        if (!empty($visitor_team_data)) {
            $updated = $wpdb->update(
                $team_stats_table,
                [
                    'fouls'               => $visitor_team_data['Fouls'] ?? 0,
                    'corners'             => $visitor_team_data['Corners'] ?? 0,
                    'offsides'            => $visitor_team_data['Offsides'] ?? 0,
                    'possession_time'     => $visitor_team_data['Possessiontime'] ?? 0,
                    'yellow_cards'        => $visitor_team_data['Yellowcards'] ?? 0,
                    'red_cards'           => $visitor_team_data['Redcards'] ?? 0,
                    'yellow_red_cards'    => $visitor_team_data['Yellowredcards'] ?? 0,
                    'saves'               => $visitor_team_data['Saves'] ?? 0,
                    'substitutions'       => $visitor_team_data['Substitutions'] ?? 0,
                    'goal_kick'           => $visitor_team_data['Goal_kick'] ?? 0,
                    'goal_attempts'       => $visitor_team_data['Goal_attempts'] ?? 0,
                    'free_kick'           => $visitor_team_data['Free_kick'] ?? 0,
                    'throw_in'            => $visitor_team_data['Throw_in'] ?? 0,
                    'ball_safe'           => $visitor_team_data['Ball_safe'] ?? 0,
                    'goals'               => $visitor_team_data['Goals'] ?? 0,
                    'penalties'           => $visitor_team_data['Penalties'] ?? 0,
                    'injuries'            => $visitor_team_data['Injuries'] ?? 0,
                    'tackles'             => $visitor_team_data['Tackles'] ?? 0,
                    'attacks'             => $visitor_team_data['Attacks'] ?? 0,
                    'dangerous_attacks'   => $visitor_team_data['Dangerous_attacks'] ?? 0,
                    'passes_total'        => $visitor_team_data['Passes']['Total'] ?? 0,
                    'passes_accurate'     => $visitor_team_data['Passes']['Accurate'] ?? 0,
                    'passes_percentage'   => $visitor_team_data['Passes']['Percentage'] ?? 0,
                    'shots_total'         => $visitor_team_data['Shots']['Total'] ?? 0,
                    'shots_ongoal'        => $visitor_team_data['Shots']['Ongoal'] ?? 0,
                    'shots_blocked'       => $visitor_team_data['Shots']['Blocked'] ?? 0,
                    'shots_offgoal'       => $visitor_team_data['Shots']['Offgoal'] ?? 0,
                    'shots_insidebox'     => $visitor_team_data['Shots']['Insidebox'] ?? 0,
                    'shots_outsidebox'    => $visitor_team_data['Shots']['Outsidebox'] ?? 0//,
                  //  'last_updated'        => current_time('mysql') // Update the last_updated timestamp
                ],
                [
                    'fixture_id' => $match_id,
                    'team_id'    => $stats['data']['visitorTeam']['teamId'] ?? 0
                ],
                [
                    '%d','%d','%d','%d','%d','%d','%d','%d',
                    '%d','%d','%d','%d','%d','%d','%d','%d',
                    '%d','%d','%d','%d','%d','%d','%d','%d',
                    '%d','%d','%d','%d','%d','%d','%d','%s'
                ],
                [
                    '%d',
                    '%d'
                ]
            );

            // If the update affected no rows, it might be an initial insert. Insert instead.
            if ($updated === false || $wpdb->rows_affected == 0) {
                // Insert initial stats if not already present
                $insert_result = $this->insert_initial_stats($match_id, $stats['data']['visitorTeam']['teamId']);
                if (is_wp_error($insert_result)) {
                    throw new Exception($insert_result->get_error_message());
                }

                // Now perform the update again
                $updated = $wpdb->update(
                    $team_stats_table,
                    [
                        'fouls'               => $visitor_team_data['Fouls'] ?? 0,
                        'corners'             => $visitor_team_data['Corners'] ?? 0,
                        'offsides'            => $visitor_team_data['Offsides'] ?? 0,
                        'possession_time'     => $visitor_team_data['Possessiontime'] ?? 0,
                        'yellow_cards'        => $visitor_team_data['Yellowcards'] ?? 0,
                        'red_cards'           => $visitor_team_data['Redcards'] ?? 0,
                        'yellow_red_cards'    => $visitor_team_data['Yellowredcards'] ?? 0,
                        'saves'               => $visitor_team_data['Saves'] ?? 0,
                        'substitutions'       => $visitor_team_data['Substitutions'] ?? 0,
                        'goal_kick'           => $visitor_team_data['Goal_kick'] ?? 0,
                        'goal_attempts'       => $visitor_team_data['Goal_attempts'] ?? 0,
                        'free_kick'           => $visitor_team_data['Free_kick'] ?? 0,
                        'throw_in'            => $visitor_team_data['Throw_in'] ?? 0,
                        'ball_safe'           => $visitor_team_data['Ball_safe'] ?? 0,
                        'goals'               => $visitor_team_data['Goals'] ?? 0,
                        'penalties'           => $visitor_team_data['Penalties'] ?? 0,
                        'injuries'            => $visitor_team_data['Injuries'] ?? 0,
                        'tackles'             => $visitor_team_data['Tackles'] ?? 0,
                        'attacks'             => $visitor_team_data['Attacks'] ?? 0,
                        'dangerous_attacks'   => $visitor_team_data['Dangerous_attacks'] ?? 0,
                        'passes_total'        => $visitor_team_data['Passes']['Total'] ?? 0,
                        'passes_accurate'     => $visitor_team_data['Passes']['Accurate'] ?? 0,
                        'passes_percentage'   => $visitor_team_data['Passes']['Percentage'] ?? 0,
                        'shots_total'         => $visitor_team_data['Shots']['Total'] ?? 0,
                        'shots_ongoal'        => $visitor_team_data['Shots']['Ongoal'] ?? 0,
                        'shots_blocked'       => $visitor_team_data['Shots']['Blocked'] ?? 0,
                        'shots_offgoal'       => $visitor_team_data['Shots']['Offgoal'] ?? 0,
                        'shots_insidebox'     => $visitor_team_data['Shots']['Insidebox'] ?? 0,
                        'shots_outsidebox'    => $visitor_team_data['Shots']['Outsidebox'] ?? 0//,
                      //  'last_updated'        => current_time('mysql') // Update the last_updated timestamp
                    ],
                    [
                        'fixture_id' => $match_id,
                        'team_id'    => $stats['data']['visitorTeam']['teamId'] ?? 0
                    ],
                    [
                        '%d','%d','%d','%d','%d','%d','%d','%d',
                        '%d','%d','%d','%d','%d','%d','%d','%d',
                        '%d','%d','%d','%d','%d','%d','%d','%d',
                        '%d','%d','%d','%d','%d','%d','%d','%s'
                    ],
                    [
                        '%d',
                        '%d'
                    ]
                );

                if ($updated === false) {
                    throw new Exception("Failed to update visitor team stats after inserting initial stats.");
                }
            }
        }

        // Commit the transaction
        $wpdb->query('COMMIT');
    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        error_log('Error saving stats to DB: ' . $e->getMessage());
        return ['error' => $e->getMessage()];
    }

    return ['status' => 'success'];
}

	
	/*
 * Retrieve team statistics for a specific fixture from the database.
 *
 * @param int $fixture_id The ID of the fixture.
 * @return array An associative array containing status and data or error message.
 */
	public function get_match_stats($fixture_id) {
    global $wpdb;

    // Validate fixture_id
    if (empty($fixture_id) || !is_numeric($fixture_id)) {
        return array('status' => 'error', 'message' => 'Invalid fixture ID provided.');
    }

    // Define a unique transient key for caching stats data for this fixture.
    $transient_key = 'match_stats_' . $fixture_id;
    
    // Attempt to retrieve cached data.
    $cached_data = get_transient($transient_key);
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    /**
     * STEP 1: Attempt to refresh stats from the API if necessary.
     * The fetch_stats_from_api() method will:
     *   - Insert initial records if none exist,
     *   - Check if the existing stats are older than a given interval, and
     *   - Fetch fresh data from the API if needed.
     */
    $api_result = $this->fetch_stats_from_api($fixture_id);
    if ($api_result && isset($api_result['error'])) {
        error_log("Error fetching stats from API for fixture ID {$fixture_id}: " . $api_result['error']);
        // Optionally, you could decide to return an error here.
        // In this example, we'll proceed to use whatever data is in the database.
    }

    /**
     * STEP 2: Query the database for the team statistics.
     */
    // Define table names
    $team_stats_table = $wpdb->prefix . 'livefot_team_stats';
    $teams_table      = $wpdb->prefix . 'livefot_teams';

    // Perform single query joining team_stats and teams
    $query = $wpdb->prepare(
        "
        SELECT 
            ts.*, 
            t.name AS team_name,
            t.logo_path AS team_logo
        FROM $team_stats_table ts
        JOIN $teams_table t ON ts.team_id = t.team_id
        WHERE ts.fixture_id = %d
        ORDER BY ts.stats_id ASC
        ",
        $fixture_id
    );

    $results = $wpdb->get_results($query, ARRAY_A);

    if (empty($results)) {
        return array('status' => 'error', 'message' => 'No team statistics found for the given fixture.');
    }

    // Ensure exactly two teams are found
    if (count($results) !== 2) {
        return array('status' => 'error', 'message' => 'Expected two team statistics for the given fixture.');
    }

    // Assign teams based on ordering (first as local, second as visitor)
    // Adjust this logic if you have a different way to determine team roles
    $local_team_stats   = $results[0];
    $visitor_team_stats = $results[1];

    // Structure local team data
    $local_team_data = array(
        'teamId'   => intval($local_team_stats['team_id']),
        'teamName' => sanitize_text_field($local_team_stats['team_name']),
        'teamLogo' => esc_url_raw($local_team_stats['team_logo']),
        'stats'    => array(
            'StatsId'            => intval($local_team_stats['stats_id']),
            'Fouls'              => intval($local_team_stats['fouls']),
            'Corners'            => intval($local_team_stats['corners']),
            'Offsides'           => intval($local_team_stats['offsides']),
            'Possessiontime'     => intval($local_team_stats['possession_time']),
            'Yellowcards'        => intval($local_team_stats['yellow_cards']),
            'Redcards'           => intval($local_team_stats['red_cards']),
            'Yellowredcards'     => !is_null($local_team_stats['yellow_red_cards']) ? intval($local_team_stats['yellow_red_cards']) : null,
            'Saves'              => intval($local_team_stats['saves']),
            'Substitutions'      => intval($local_team_stats['substitutions']),
            'Goal_kick'          => intval($local_team_stats['goal_kick']),
            'Goal_attempts'      => !is_null($local_team_stats['goal_attempts']) ? intval($local_team_stats['goal_attempts']) : null,
            'Free_kick'          => intval($local_team_stats['free_kick']),
            'Throw_in'           => intval($local_team_stats['throw_in']),
            'Ball_safe'          => intval($local_team_stats['ball_safe']),
            'Goals'              => intval($local_team_stats['goals']),
            'Penalties'          => intval($local_team_stats['penalties']),
            'Injuries'           => intval($local_team_stats['injuries']),
            'Tackles'            => intval($local_team_stats['tackles']),
            'Attacks'            => intval($local_team_stats['attacks']),
            'Dangerous_attacks'  => intval($local_team_stats['dangerous_attacks']),
            'Passes'             => array(
                'Total'      => intval($local_team_stats['passes_total']),
                'Accurate'   => !is_null($local_team_stats['passes_accurate']) ? intval($local_team_stats['passes_accurate']) : null,
                'Percentage' => intval($local_team_stats['passes_percentage'])
            ),
            'Shots'              => array(
                'Total'      => intval($local_team_stats['shots_total']),
                'Ongoal'     => intval($local_team_stats['shots_ongoal']),
                'Blocked'    => intval($local_team_stats['shots_blocked']),
                'Offgoal'    => intval($local_team_stats['shots_offgoal']),
                'Insidebox'  => intval($local_team_stats['shots_insidebox']),
                'Outsidebox' => intval($local_team_stats['shots_outsidebox'])
            ),
            'Last_updated'       => $local_team_stats['last_updated']
        )
    );

    // Structure visitor team data
    $visitor_team_data = array(
        'teamId'   => intval($visitor_team_stats['team_id']),
        'teamName' => sanitize_text_field($visitor_team_stats['team_name']),
        'teamLogo' => esc_url_raw($visitor_team_stats['team_logo']),
        'stats'    => array(
            'StatsId'            => intval($visitor_team_stats['stats_id']),
            'Fouls'              => intval($visitor_team_stats['fouls']),
            'Corners'            => intval($visitor_team_stats['corners']),
            'Offsides'           => intval($visitor_team_stats['offsides']),
            'Possessiontime'     => intval($visitor_team_stats['possession_time']),
            'Yellowcards'        => intval($visitor_team_stats['yellow_cards']),
            'Redcards'           => intval($visitor_team_stats['red_cards']),
            'Yellowredcards'     => !is_null($visitor_team_stats['yellow_red_cards']) ? intval($visitor_team_stats['yellow_red_cards']) : null,
            'Saves'              => intval($visitor_team_stats['saves']),
            'Substitutions'      => intval($visitor_team_stats['substitutions']),
            'Goal_kick'          => intval($visitor_team_stats['goal_kick']),
            'Goal_attempts'      => !is_null($visitor_team_stats['goal_attempts']) ? intval($visitor_team_stats['goal_attempts']) : null,
            'Free_kick'          => intval($visitor_team_stats['free_kick']),
            'Throw_in'           => intval($visitor_team_stats['throw_in']),
            'Ball_safe'          => intval($visitor_team_stats['ball_safe']),
            'Goals'              => intval($visitor_team_stats['goals']),
            'Penalties'          => intval($visitor_team_stats['penalties']),
            'Injuries'           => intval($visitor_team_stats['injuries']),
            'Tackles'            => intval($visitor_team_stats['tackles']),
            'Attacks'            => intval($visitor_team_stats['attacks']),
            'Dangerous_attacks'  => intval($visitor_team_stats['dangerous_attacks']),
            'Passes'             => array(
                'Total'      => intval($visitor_team_stats['passes_total']),
                'Accurate'   => !is_null($visitor_team_stats['passes_accurate']) ? intval($visitor_team_stats['passes_accurate']) : null,
                'Percentage' => intval($visitor_team_stats['passes_percentage'])
            ),
            'Shots'              => array(
                'Total'      => intval($visitor_team_stats['shots_total']),
                'Ongoal'     => intval($visitor_team_stats['shots_ongoal']),
                'Blocked'    => intval($visitor_team_stats['shots_blocked']),
                'Offgoal'    => intval($visitor_team_stats['shots_offgoal']),
                'Insidebox'  => intval($visitor_team_stats['shots_insidebox']),
                'Outsidebox' => intval($visitor_team_stats['shots_outsidebox'])
            ),
            'Last_updated'       => $visitor_team_stats['last_updated']
        )
    );

    // Structure the final data array
    $data = array(
        'status' => 'success',
        'data'   => array(
            'localTeam'   => $local_team_data,
            'visitorTeam' => $visitor_team_data
        ),
        'remainingCalls' => 0 // Adjust this based on your application logic
    );

    /**
     * STEP 3: Determine cache expiration based on match time status.
     * - By default, the cache will expire after 300 seconds.
     * - If the match was started yesterday and has a status of FT, AET, Cancelled, or FT_PEN,
     *   set the cache to expire in 2 hours (7200 seconds).
     */
    $cache_expiration = 300; // default expiration in seconds
    
    // Define the time table name.
    $time_table = $wpdb->prefix . 'livefot_time';
    // Retrieve the starting datetime and status for the match.
    $time_sql = $wpdb->prepare(
        "SELECT status, starting_at_datetime FROM {$time_table} WHERE fixture_id = %d",
        $fixture_id
    );
    $time_data = $wpdb->get_row($time_sql);
    
    if ($time_data) {
        // Desired statuses.
        $desired_statuses = array('FT', 'AET', 'Cancelled', 'FT_PEN');
        if (in_array($time_data->status, $desired_statuses)) {
            // Convert starting_at_datetime to a timestamp.
            $match_start = strtotime($time_data->starting_at_datetime);
            // Determine yesterday's range: from yesterday midnight to today midnight.
            $yesterday_start = strtotime("yesterday");
            $today_start = strtotime("today");
            if ($match_start >= $yesterday_start && $match_start < $today_start) {
                $cache_expiration = 7200; // 2 hours in seconds.
            }
        }
    }
    
    // Cache the assembled data using the determined expiration time.
    set_transient($transient_key, $data, $cache_expiration);

    return $data;
}



}