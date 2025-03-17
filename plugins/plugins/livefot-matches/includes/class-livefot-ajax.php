<?php
class LiveFot_Ajax {
    public function __construct() {
        add_action('wp_ajax_get_matches', array($this, 'get_matches'));
        add_action('wp_ajax_nopriv_get_matches', array($this, 'get_matches'));
        
        add_action('wp_ajax_get_match_events', array($this, 'get_match_events'));
        add_action('wp_ajax_nopriv_get_match_events', array($this, 'get_match_events'));
        
        add_action('wp_ajax_get_match_lineup', array($this, 'get_match_lineup'));
        add_action('wp_ajax_nopriv_get_match_lineup', array($this, 'get_match_lineup'));
        
        add_action('wp_ajax_get_match_stats', array($this, 'get_match_stats'));
        add_action('wp_ajax_nopriv_get_match_stats', array($this, 'get_match_stats'));
		
		add_action('wp_ajax_get_standings', array($this, 'get_standings'));
        add_action('wp_ajax_nopriv_get_standings', array($this, 'get_standings'));
		
		add_action('wp_ajax_get_live_matches', array($this, 'get_live_matches'));
		add_action('wp_ajax_nopriv_get_live_matches', array($this, 'get_live_matches'));	
    }

    public function get_matches() {
        check_ajax_referer('livefot_nonce', 'nonce');

        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : date('Y-m-d');
        $utc_offset = isset($_POST['utc_offset']) ? intval($_POST['utc_offset']) : 0;

        $api = new LiveFot_API();
        $matches = $api->get_matches_live($date, $utc_offset);  //change from get_matches   to  get_matches_live

        wp_send_json($matches);
    }

	
	
	public function get_live_matches() {
    // 1) Security check
    check_ajax_referer('livefot_nonce', 'nonce');

    // 2) Initialize your API
    $api = new LiveFot_API();

    // 3) Call a method that returns ONLY "live" matches
    //    This is up to you. Maybe you have a method named "get_matches_live_only" or "get_currently_live_matches".
    //    Adjust to match the actual function name in your LiveFot_API class.
    $liveMatches = $api->get_live_matches(); 

    // 4) If empty, return an error; otherwise return success
    if (empty($liveMatches)) {
        wp_send_json_error('No live matches found');
    } else {
        wp_send_json_success($liveMatches);
    }
}

	
	public function get_match_events() {
    check_ajax_referer('livefot_nonce', 'nonce');

    $match_id = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
    if (!$match_id) {
        wp_send_json_error('Invalid match ID');
    }

    $api = new LiveFot_API();
    $events = $api->get_match_events($match_id);

    if (empty($events)) {
        wp_send_json_error('No events found for this match.');
    } else {
        wp_send_json_success($events); // Properly encapsulates the data with success flag
    }
}

	public function get_match_lineup() {
    check_ajax_referer('livefot_nonce', 'nonce');

    $match_id = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
    if (!$match_id) {
        wp_send_json_error(array('message' => 'Invalid match ID'));
    }

    $api = new LiveFot_API();
    $lineup = $api->get_match_lineup($match_id);

    if (isset($lineup['error'])) {
        wp_send_json_error(array('message' => $lineup['error']));
    }

    wp_send_json_success($lineup);
}

		

	public function get_standings() {
    // Verify the nonce for security
    check_ajax_referer('livefot_nonce', 'nonce');

    // Retrieve and sanitize parameters from the AJAX request
    $match_id   = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
    $league_id  = isset($_POST['league_id']) ? intval($_POST['league_id']) : 0;
    $group_id   = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    $season_id  = isset($_POST['season_id']) ? intval($_POST['season_id']) : 0;

    // Validate the parameters
    if (!$match_id || !$league_id || !$season_id) {
        wp_send_json_error('Invalid or missing parameters: match_id, league_id, and season_id are required.');
    }

    // Initialize the API
    $api = new LiveFot_API();

    // Fetch the standings data by passing the required parameters (including $match_id)
    $standings = $api->get_standings($match_id, $league_id, $group_id, $season_id);

    // Check if standings data was retrieved successfully
    if (empty($standings)) {
        wp_send_json_error('No standings data found for the provided parameters.');
    }

    // Send the successful JSON response with standings data
    wp_send_json_success($standings);
}


   public function get_match_stats() {
    check_ajax_referer('livefot_nonce', 'nonce');

    $match_id = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
    if (!$match_id) {
        wp_send_json_error('Invalid match ID');
    }

    $api = new LiveFot_API();
    $stats = $api->get_match_stats($match_id);

    if ($stats['status'] === 'success') {
        wp_send_json_success($stats['data']);
    } else {
        wp_send_json_error($stats['message']);
    }
}

}

new LiveFot_Ajax();