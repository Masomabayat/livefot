<?php
/**
 * Plugin Name: FotLive Login
 * Plugin URI: https://fotlive.com
 * Description: Integrates FotLive authentication system with WordPress
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: fotlive-login
 */

if (!defined('ABSPATH')) {
    exit;
}

class FotLive_Login {
    // The API endpoints
    private $api_base_url = 'https://api.livefot.com/api/v1/Users';

    public function __construct() {
        // Start session on init with priority 1
        add_action('init', array($this, 'start_session'), 1);

        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Authentication endpoints
        add_action('wp_ajax_fotlive_sign_in', array($this, 'handle_sign_in'));
        add_action('wp_ajax_nopriv_fotlive_sign_in', array($this, 'handle_sign_in'));

        add_action('wp_ajax_fotlive_sign_up', array($this, 'handle_sign_up'));
        add_action('wp_ajax_nopriv_fotlive_sign_up', array($this, 'handle_sign_up'));

        add_action('wp_ajax_fotlive_logout', array($this, 'handle_logout'));
        add_action('wp_ajax_nopriv_fotlive_logout', array($this, 'handle_logout'));

        add_action('wp_ajax_fotlive_reset_password_request', array($this, 'handle_reset_password_request'));
        add_action('wp_ajax_nopriv_fotlive_reset_password_request', array($this, 'handle_reset_password_request'));

        add_action('wp_ajax_fotlive_reset_password', array($this, 'handle_reset_password'));
        add_action('wp_ajax_nopriv_fotlive_reset_password', array($this, 'handle_reset_password'));

        // Dashboard data endpoints
        add_action('wp_ajax_fotlive_get_leagues', array($this, 'handle_get_leagues'));
        add_action('wp_ajax_nopriv_fotlive_get_leagues', array($this, 'handle_get_leagues'));

        add_action('wp_ajax_fotlive_get_api_usage', array($this, 'handle_get_api_usage'));
        add_action('wp_ajax_nopriv_fotlive_get_api_usage', array($this, 'handle_get_api_usage'));

        add_action('wp_ajax_fotlive_get_subscription_plans', array($this, 'handle_get_subscription_plans'));
        add_action('wp_ajax_nopriv_fotlive_get_subscription_plans', array($this, 'handle_get_subscription_plans'));

        add_action('wp_ajax_fotlive_create_subscription_order', array($this, 'handle_create_subscription_order'));
        add_action('wp_ajax_nopriv_fotlive_create_subscription_order', array($this, 'handle_create_subscription_order'));

        add_action('wp_ajax_fotlive_get_available_leagues', array($this, 'handle_get_available_leagues'));
        add_action('wp_ajax_nopriv_fotlive_get_available_leagues', array($this, 'handle_get_available_leagues'));

        add_action('wp_ajax_fotlive_subscribe_to_leagues', array($this, 'handle_subscribe_to_leagues'));
        add_action('wp_ajax_nopriv_fotlive_subscribe_to_leagues', array($this, 'handle_subscribe_to_leagues'));
    }

    public function start_session() {
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }

    public function init() {
        add_shortcode('fotlive_login', array($this, 'login_shortcode'));
    }

    public function enqueue_scripts() {
        // jQuery
        wp_enqueue_script('jquery');

        // CSS
        wp_enqueue_style('fotlive-login', plugins_url('css/style.css', __FILE__), array(), '1.0.2');
        wp_enqueue_style('fotlive-dashboard', plugins_url('css/dashboard.css', __FILE__), array(), '1.0.2');

        // JS
        wp_enqueue_script('fotlive-login', plugins_url('js/login.js', __FILE__), array('jquery'), '1.0.2', true);
        wp_enqueue_script('fotlive-dashboard', plugins_url('js/dashboard.js', __FILE__), array('jquery'), '1.0.2', true);

        // Pass data to login.js
        $token = isset($_SESSION['fotlive_token']) ? $_SESSION['fotlive_token'] : '';
        wp_localize_script('fotlive-login', 'fotliveAjax', array(
            'ajaxurl'        => admin_url('admin-ajax.php'),
            'nonce'          => wp_create_nonce('fotlive-login-nonce'),
            'redirectUrl'    => 'https://livefootballcenter.com/login-page/',
            'token'          => $token
        ));

        wp_localize_script('fotlive-dashboard', 'fotliveDashboard', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('fotlive-dashboard-nonce'),
            'token'   => $token
        ));
    }

    public function handle_sign_in() {
        check_ajax_referer('fotlive-login-nonce', 'nonce');

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($email) || empty($password)) {
            wp_send_json_error('Email and password are required');
            return;
        }

        $url = $this->api_base_url . '/SignIn';
        $body = array(
            'email' => $email,
            'password' => $password
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        $data = json_decode($body);

        if ($status_code !== 200 || empty($data) || empty($data->token)) {
            $error_message = isset($data->message) ? $data->message : 'Authentication failed';
            wp_send_json_error($error_message);
            return;
        }

        // Start session if not started
        if (!session_id() && !headers_sent()) {
            session_start();
        }

        $_SESSION['fotlive_token'] = $data->token;
        $_SESSION['fotlive_name'] = $data->name ?? '';
        $_SESSION['fotlive_email'] = $data->email ?? '';
        $_SESSION['fotlive_user_id'] = $data->user_id ?? '';
        $_SESSION['fotlive_status'] = $data->status ?? 'active';

        wp_send_json_success(array(
            'token' => $data->token,
            'name' => $data->name ?? '',
            'email' => $data->email ?? '',
            'user_id' => $data->user_id ?? '',
            'status' => $data->status ?? 'active'
        ));
    }

    public function handle_sign_up() {
        check_ajax_referer('fotlive-login-nonce', 'nonce');

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($email) || empty($name) || empty($password)) {
            wp_send_json_error('All fields are required');
            return;
        }

        $url = $this->api_base_url . '/SignUp';
        $body = array(
            'email' => $email,
            'name' => $name,
            'password' => $password
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        $data = json_decode($body);

        if ($status_code !== 200 || empty($data) || empty($data->token)) {
            $error_message = isset($data->message) ? $data->message : 'Registration failed';
            wp_send_json_error($error_message);
            return;
        }

        // Start session if not started
        if (!session_id() && !headers_sent()) {
            session_start();
        }

        $_SESSION['fotlive_token'] = $data->token;
        $_SESSION['fotlive_name'] = $data->name ?? '';
        $_SESSION['fotlive_email'] = $data->email ?? '';
        $_SESSION['fotlive_user_id'] = $data->user_id ?? '';
        $_SESSION['fotlive_status'] = $data->status ?? 'active';

        wp_send_json_success(array(
            'token' => $data->token,
            'name' => $data->name ?? '',
            'email' => $data->email ?? '',
            'user_id' => $data->user_id ?? '',
            'status' => $data->status ?? 'active'
        ));
    }
	
	
public function handle_reset_password_request() {
    check_ajax_referer('fotlive-login-nonce', 'nonce');

    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

    if (empty($email)) {
        wp_send_json_error('Email is required');
        return;
    }

    // Pass the email via the query string so the API binds it to the 'resetEmail' parameter
    $url = 'https://api.livefot.com/api/v1/Users/resetPasswordRequest?resetEmail=' . urlencode($email);

    $response = wp_remote_post($url, array(
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('Connection error: ' . $response->get_error_message());
        return;
    }

    $body        = wp_remote_retrieve_body($response);
    $status_code = wp_remote_retrieve_response_code($response);

    error_log('Reset Password Request - Status Code: ' . $status_code);
    error_log('Reset Password Request - Response Body: ' . $body);

    $data = json_decode($body);

    if ($status_code !== 200) {
        $error_message = isset($data->message) ? $data->message : 'Server returned status code: ' . $status_code;
        wp_send_json_error($error_message);
        return;
    }
    
    // Check if the API response is simply "true" or if it's an object with a "success" property.
    if ($data === true || (is_object($data) && isset($data->success) && $data->success === true)) {
        wp_send_json_success('Password reset code sent successfully');
    } else {
        wp_send_json_error(isset($data->message) ? $data->message : 'Failed to send password reset code');
    }
}





   /* public function handle_reset_password_request() {
        check_ajax_referer('fotlive-login-nonce', 'nonce');

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (empty($email)) {
            wp_send_json_error('Email is required');
            return;
        }

        // Direct API call to the endpoint with the email as a query parameter
        $url = 'https://api.livefot.com/api/v1/Users/resetPasswordRequest?resetEmail=' . urlencode($email);

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Accept' => 'application/json'
            ),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            wp_send_json_error('Connection error: ' . $response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        
        // Debug information
        error_log('Reset Password Request - Status Code: ' . $status_code);
        error_log('Reset Password Request - Response Body: ' . $body);
        
        $data = json_decode($body);

        if ($status_code !== 200) {
            $error_message = 'Server returned status code: ' . $status_code;
            wp_send_json_error($error_message);
            return;
        }
        
        if ($data === true) {
            wp_send_json_success('Password reset code sent successfully');
        } else {
            wp_send_json_error('Failed to send password reset code');
        }
    }*/
	
	
public function handle_reset_password() {
    check_ajax_referer('fotlive-login-nonce', 'nonce');

    $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';

    if (empty($code) || empty($new_password)) {
        wp_send_json_error('Code and new password are required');
        return;
    }

    $url = 'https://api.livefot.com/api/v1/Users/resetPassword';
    $payload = array(
        'code'         => $code,
        'new_password' => $new_password
    );
    $json_payload = json_encode($payload);

    $response = wp_remote_post($url, array(
        'headers' => array(
            'Content-Type' => 'application/json; charset=utf-8',
            'Accept'       => 'application/json'
        ),
        'body'    => $json_payload,
        'timeout' => 30
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('Connection error: ' . $response->get_error_message());
        return;
    }

    $response_body = wp_remote_retrieve_body($response);
    $status_code   = wp_remote_retrieve_response_code($response);

    error_log('Reset Password - Status Code: ' . $status_code);
    error_log('Reset Password - Response Body: ' . $response_body);

    if ($status_code !== 200) {
        $error_message = 'Server returned status code: ' . $status_code;
        wp_send_json_error($error_message);
        return;
    }

    $data = json_decode($response_body, true);

    // Check if the API returns a boolean true or an object/array with a success flag.
    if ($data === true || (is_array($data) && isset($data['success']) && $data['success'] === true)) {
        wp_send_json_success('Password reset successfully');
    } else {
        $error_message = (is_array($data) && isset($data['message'])) ? $data['message'] : 'Failed to reset password';
        wp_send_json_error($error_message);
    }
}





   /* public function handle_reset_password() {
        check_ajax_referer('fotlive-login-nonce', 'nonce');

        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';

        if (empty($code) || empty($new_password)) {
            wp_send_json_error('Code and new password are required');
            return;
        }

        $url = 'https://api.livefot.com/api/v1/Users/resetPassword';
        $body = array(
            'code' => $code,
            'new_password' => $new_password
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            wp_send_json_error('Connection error: ' . $response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        
        // Debug information
        error_log('Reset Password - Status Code: ' . $status_code);
        error_log('Reset Password - Response Body: ' . $body);
        
        $data = json_decode($body);

        if ($status_code !== 200) {
            $error_message = 'Server returned status code: ' . $status_code;
            wp_send_json_error($error_message);
            return;
        }
        
        if ($data === true) {
            wp_send_json_success('Password reset successfully');
        } else {
            wp_send_json_error('Failed to reset password');
        }
    }*/

    public function handle_logout() {
        check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

        if (session_id()) {
            session_destroy();
        }
        wp_send_json_success();
    }

 /*   public function handle_get_leagues() {
        check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        if (empty($token) && isset($_SESSION['fotlive_token'])) {
            $token = $_SESSION['fotlive_token'];
        }
        if (empty($token)) {
            wp_send_json_error('No user session found. Please log in.');
            return;
        }

        $url = 'https://api.livefot.com/api/v1/tournaments/wp/user/leagues';

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
            return;
        }

        $body        = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        $data        = json_decode($body, true);

        if ($status_code === 401) {
            if (session_id()) {
                session_destroy();
            }
            wp_send_json_error('Session expired. Please log in again.');
            return;
        }

        if ($status_code >= 200 && $status_code < 300 && is_array($data)) {
            wp_send_json_success($data);
        } else {
            $error_message = is_array($data) && isset($data['message']) ? $data['message'] : 'Failed to load leagues';
            wp_send_json_error($error_message);
        }
    }*/
	
	public function handle_get_leagues() {
    check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

    $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
    if (empty($token) && isset($_SESSION['fotlive_token'])) {
        $token = $_SESSION['fotlive_token'];
    }
    if (empty($token)) {
        wp_send_json_error('No user session found. Please log in.');
        return;
    }

    // Cache key for leagues (15 minutes)
    $cache_key = 'fotlive_leagues_' . md5($token);
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        wp_send_json_success($cached_data);
        return;
    }

    $url = 'https://api.livefot.com/api/v1/tournaments/wp/user/leagues';
    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json'
        ),
        'timeout' => 15
    ));

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
        return;
    }

    $body        = wp_remote_retrieve_body($response);
    $status_code = wp_remote_retrieve_response_code($response);
    $data        = json_decode($body, true);

    if ($status_code === 401) {
        if (session_id()) {
            session_destroy();
        }
        wp_send_json_error('Session expired. Please log in again.');
        return;
    }

    if ($status_code >= 200 && $status_code < 300 && is_array($data)) {
        // Cache the response for 15 minutes (15 * 60 seconds)
        set_transient($cache_key, $data, 15 * MINUTE_IN_SECONDS);
        wp_send_json_success($data);
    } else {
        $error_message = is_array($data) && isset($data['message']) ? $data['message'] : 'Failed to load leagues';
        wp_send_json_error($error_message);
    }
}

	
	
	
/*
    public function handle_get_api_usage() {
        check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        if (empty($token) && isset($_SESSION['fotlive_token'])) {
            $token = $_SESSION['fotlive_token'];
        }
        if (empty($token)) {
            wp_send_json_error('No user session found. Please log in.');
            return;
        }

        $url = 'https://api.livefot.com/api/v1/tournaments/wp/user/endpoints';

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
            return;
        }

        $body        = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        $data        = json_decode($body, true);

        if ($status_code === 401) {
            if (session_id()) {
                session_destroy();
            }
            wp_send_json_error('Session expired. Please log in again.');
            return;
        }

        if ($status_code >= 200 && $status_code < 300 && is_array($data)) {
            // Return the first item only
            wp_send_json_success($data[0]);
        } else {
            $error_message = is_array($data) && isset($data['message']) ? $data['message'] : 'Failed to load API usage';
            wp_send_json_error($error_message);
        }
    }*/
	
	
	public function handle_get_api_usage() {
    check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

    $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
    if (empty($token) && isset($_SESSION['fotlive_token'])) {
        $token = $_SESSION['fotlive_token'];
    }
    if (empty($token)) {
        wp_send_json_error('No user session found. Please log in.');
        return;
    }

    // Cache key for API usage (1 minute)
    $cache_key = 'fotlive_api_usage_' . md5($token);
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        wp_send_json_success($cached_data);
        return;
    }

    $url = 'https://api.livefot.com/api/v1/tournaments/wp/user/endpoints';
    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json'
        ),
        'timeout' => 15
    ));

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
        return;
    }

    $body        = wp_remote_retrieve_body($response);
    $status_code = wp_remote_retrieve_response_code($response);
    $data        = json_decode($body, true);

    if ($status_code === 401) {
        if (session_id()) {
            session_destroy();
        }
        wp_send_json_error('Session expired. Please log in again.');
        return;
    }

    if ($status_code >= 200 && $status_code < 300 && is_array($data)) {
        // For this endpoint, we assume the API returns an array and we want the first item.
        $result = $data[0] ?? $data;
        // Cache for 1 minute
        set_transient($cache_key, $result, 1 * MINUTE_IN_SECONDS);
        wp_send_json_success($result);
    } else {
        $error_message = is_array($data) && isset($data['message']) ? $data['message'] : 'Failed to load API usage';
        wp_send_json_error($error_message);
    }
}


 /*   public function handle_get_subscription_plans() {
        check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

        if (!class_exists('WooCommerce')) {
            wp_send_json_error('WooCommerce is not active');
            return;
        }

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_price',
                    'value'   => 0,
                    'compare' => '>',
                    'type'    => 'NUMERIC'
                )
            )
        );

        $products = get_posts($args);
        if (empty($products)) {
            wp_send_json_error('No subscription plans found');
            return;
        }

        $plans = array();
        foreach ($products as $product_post) {
            $product = wc_get_product($product_post->ID);
            if (!$product) continue;

            $price     = $product->get_price();
            $period    = get_post_meta($product->get_id(), '_subscription_period', true);
            $interval  = get_post_meta($product->get_id(), '_subscription_period_interval', true);

            $period_label  = $interval > 1 ? $period . 's' : $period;
            $period_string = $interval > 1 ? "every $interval $period_label" : "per $period_label";

            // Extract short description lines as features
            $features = array();
            $short_description = $product->get_short_description();
            if (!empty($short_description)) {
                $lines = explode("\n", strip_tags($short_description));
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if (!empty($trimmed)) {
                        $features[] = $trimmed;
                    }
                }
            }
            if (empty($features)) {
                $features = array(
                    'Full access to all leagues',
                    'Real-time match updates',
                    'API access included',
                    'Priority support'
                );
            }

            $plans[] = array(
                'product_id'    => $product->get_id(),
                'name'          => $product->get_name(),
                'price'         => $price,
                'billing_period'=> $period_string,
                'features'      => $features,
                'is_popular'    => (get_post_meta($product->get_id(), '_is_popular_plan', true) === 'yes'),
                'url'           => get_permalink($product->get_id())
            );
        }

        if (empty($plans)) {
            wp_send_json_error('No subscription plans could be loaded');
            return;
        }

        wp_send_json_success($plans);
    }*/
	
/*	public function handle_get_subscription_plans() {
    check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce is not active');
        return;
    }

    // Cache key for subscription plans (15 minutes)
    $cache_key = 'fotlive_subscription_plans';
    $cached_plans = get_transient($cache_key);
    if ($cached_plans !== false) {
        wp_send_json_success($cached_plans);
        return;
    }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'     => '_price',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC'
            )
        )
    );*/
	
/*	public function handle_get_subscription_plans() {
    check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce is not active');
        return;
    }

    // Cache key for subscription plans (15 minutes)
    $cache_key = 'fotlive_subscription_plans';
    $cached_plans = get_transient($cache_key);
    if ($cached_plans !== false) {
        wp_send_json_success($cached_plans);
        return;
    }  //jk

    // MODIFIED: Only get products with specific IDs or in a specific category
    $specific_product_ids = array( 124699, 123844, 135878); // Replace with your specific product IDs
    
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'post__in'       => $specific_product_ids, // Only include these specific products
        'meta_query'     => array(
            array(
                'key'     => '_price',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC'
            )
        )
    );

    $products = get_posts($args);
    if (empty($products)) {
        wp_send_json_error('No subscription plans found');
        return;
    }

    $plans = array();
    foreach ($products as $product_post) {
        $product = wc_get_product($product_post->ID);
        if (!$product) continue;

        $price    = $product->get_price();
        $period   = get_post_meta($product->get_id(), '_subscription_period', true);
        $interval = get_post_meta($product->get_id(), '_subscription_period_interval', true);
        $period_label  = $interval > 1 ? $period . 's' : $period;
        $period_string = $interval > 1 ? "every $interval $period_label" : "per $period_label";

        $features = array();
        $short_description = $product->get_short_description();
        if (!empty($short_description)) {
            $lines = explode("\n", strip_tags($short_description));
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (!empty($trimmed)) {
                    $features[] = $trimmed;
                }
            }
        }
        if (empty($features)) {
            $features = array(
                'Full access to all leagues',
                'Real-time match updates',
                'API access included',
                'Priority support'
            );
        }

        $plans[] = array(
            'product_id'     => $product->get_id(),
            'name'           => $product->get_name(),
            'price'          => $price,
            'billing_period' => $period_string,
            'features'       => $features,
            'is_popular'     => (get_post_meta($product->get_id(), '_is_popular_plan', true) === 'yes'),
            'url'            => get_permalink($product->get_id())
        );
    }

    if (empty($plans)) {
        wp_send_json_error('No subscription plans could be loaded');
        return;
    }

    // Cache the subscription plans for 15 minutes
    set_transient($cache_key, $plans, 15 * MINUTE_IN_SECONDS);
    wp_send_json_success($plans);
}*/
	
	
	
public function handle_get_subscription_plans() {
    check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce is not active');
        return;
    }

    // (Optional) If you're caching with a transient, keep or remove the code as needed
    // $cache_key = 'fotlive_subscription_plans';
    // $cached_plans = get_transient($cache_key);
    // if ($cached_plans !== false) {
    //     wp_send_json_success($cached_plans);
    //     return;
    // }

    $specific_product_ids = array(135912, 135914, 135915, 135878); // Example IDs    //jkjkjk
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'post__in'       => $specific_product_ids,
        // 'meta_query'   => ... (if you want to filter by price, etc.)
    );

    $products = get_posts($args);
    if (empty($products)) {
        wp_send_json_error('No subscription plans found');
        return;
    }

    $plans = array();
    foreach ($products as $product_post) {
        $product = wc_get_product($product_post->ID);
        if (!$product) continue;

        // Get the normal price data
        $price    = $product->get_price();
        $period   = get_post_meta($product->get_id(), '_subscription_period', true);
        $interval = get_post_meta($product->get_id(), '_subscription_period_interval', true);
        $period_label  = $interval > 1 ? $period . 's' : $period;
        $period_string = $interval > 1 ? "every $interval $period_label" : "per $period_label";

        // Extract short description lines as features
        $features = array();
        $short_description = $product->get_short_description();
        if (!empty($short_description)) {
            $lines = explode("\n", strip_tags($short_description));
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (!empty($trimmed)) {
                    $features[] = $trimmed;
                }
            }
        }
        if (empty($features)) {
            $features = array(
                'Full access to all leagues',
                'Real-time match updates',
                'API access included',
                'Priority support',
            );
        }

        // ───────────────────────────────────────────────────
        // GET THE THUMBNAIL (featured image) URL
        // ───────────────────────────────────────────────────
        $thumbnail_id  = $product->get_image_id();                // the attachment ID
        $thumbnail_url = wp_get_attachment_url($thumbnail_id);    // the image URL or false if none
        
        // Build the plan array
        $plans[] = array(
            'product_id'     => $product->get_id(),
            'name'           => $product->get_name(),
            'price'          => $price,
            'billing_period' => $period_string,
            'features'       => $features,
            'is_popular'     => (get_post_meta($product->get_id(), '_is_popular_plan', true) === 'yes'),
            'url'            => get_permalink($product->get_id()),
            'image_url'      => $thumbnail_url ?: '', // might be empty string if no image
        );
    }

    if (empty($plans)) {
        wp_send_json_error('No subscription plans could be loaded');
        return;
    }

    // set_transient($cache_key, $plans, 15 * MINUTE_IN_SECONDS); // optional caching
    wp_send_json_success($plans);
}

	

    public function handle_create_subscription_order() {
        check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

        if (!class_exists('WooCommerce')) {
            wp_send_json_error('WooCommerce is not active');
            return;
        }

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error('Invalid product ID');
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error('Invalid product');
            return;
        }

        try {
            // Ensure cart objects are available
            if (!WC()->cart) {
                WC()->frontend_includes();
                WC()->session = new WC_Session_Handler();
                WC()->session->init();
                WC()->cart = new WC_Cart();
                WC()->customer = new WC_Customer(get_current_user_id(), true);
            }
            WC()->cart->empty_cart();

            // Add product to cart
            $cart_item_key = WC()->cart->add_to_cart($product_id);
            if ($cart_item_key) {
                $checkout_url = wc_get_checkout_url();
                wp_send_json_success(array('checkout_url' => $checkout_url));
            } else {
                wp_send_json_error('Could not add product to cart');
            }
        } catch (Exception $e) {
            wp_send_json_error('Error creating order: ' . $e->getMessage());
        }
    }

    public function handle_get_available_leagues() {
        check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        if (empty($token)) {
            wp_send_json_error('No token provided');
            return;
        }

        $response = wp_remote_get('https://api.livefot.com/api/v1/tournaments/wp/leagues', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
            return;
        }

        $body        = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code === 401) {
            if (session_id()) {
                session_destroy();
            }
            wp_send_json_error('Session expired. Please log in again.');
            return;
        }

        $data = json_decode($body);
        if ($status_code >= 200 && $status_code < 300 && is_array($data)) {
            wp_send_json_success($data);
        } else {
            $error_message = is_object($data) && isset($data->message) ? $data->message : 'Failed to load leagues';
            wp_send_json_error($error_message);
        }
    }

    public function handle_subscribe_to_leagues() {
        check_ajax_referer('fotlive-dashboard-nonce', 'nonce');

        $token      = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        $code       = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        $league_ids = isset($_POST['league_ids']) ? array_map('intval', (array)$_POST['league_ids']) : array();

        if (empty($token) || empty($code) || empty($league_ids)) {
            wp_send_json_error('Missing required data');
            return;
        }

        $response = wp_remote_post('https://api.livefot.com/api/v1/tournaments/wp/user/subscribe', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json'
            ),
            'body' => json_encode(array(
                'code'      => $code,
                'leagueIds' => $league_ids
            )),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
            return;
        }

        $body        = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code === 401) {
            if (session_id()) {
                session_destroy();
            }
            wp_send_json_error('Session expired. Please log in again.');
            return;
        }

        $data = json_decode($body);
        if ($status_code >= 200 && $status_code < 300) {
            wp_send_json_success($data);
        } else {
            $error_message = is_object($data) && isset($data->message) ? $data->message : 'Failed to subscribe to leagues';
            wp_send_json_error($error_message);
        }
    }

    public function login_shortcode() {
        // Start session if not started
        if (!session_id() && !headers_sent()) {
            session_start();
        }

        // Check if user is logged in
        $is_logged_in = isset($_SESSION['fotlive_token']);

        ob_start();
        ?>
        <div class="fotlive-container" dir="ltr">

            <!-- Login Section -->
            <div id="fotlive-login-section" class="fotlive-login-container" style="<?php echo $is_logged_in ? 'display: none;' : ''; ?>">
                <div class="fotlive-login-header">
                    <h2>Welcome to LiveFot</h2>
                    <p>Sign in to access your account</p>
                </div>

                <!-- Login/Register Tabs -->
                <div class="fotlive-tabs">
                    <button class="fotlive-tab active" data-tab="login">Sign In</button>
                    <button class="fotlive-tab" data-tab="register">Register</button>
                </div>

                <!-- Login Form -->
                <div id="login-tab-content" class="fotlive-tab-content active">
                    <form id="fotlive-login-form" class="fotlive-form">
                        <div class="fotlive-form-group">
                            <label for="login-email">Email</label>
                            <input type="email" id="login-email" name="email" required>
                        </div>
                        <div class="fotlive-form-group">
                            <label for="login-password">Password</label>
                            <input type="password" id="login-password" name="password" required>
                        </div>
                        <div class="fotlive-form-actions">
                            <button type="submit" class="fotlive-btn fotlive-btn-primary">Sign In</button>
                        </div>
                    </form>
                    <div class="fotlive-form-footer">
                        <a href="#" id="forgot-password-link">Forgot Password?</a>
                    </div>
                </div>

                <!-- Register Form -->
                <div id="register-tab-content" class="fotlive-tab-content">
                    <form id="fotlive-register-form" class="fotlive-form">
                        <div class="fotlive-form-group">
                            <label for="register-name">Full Name</label>
                            <input type="text" id="register-name" name="name" required>
                        </div>
                        <div class="fotlive-form-group">
                            <label for="register-email">Email</label>
                            <input type="email" id="register-email" name="email" required>
                        </div>
                        <div class="fotlive-form-group">
                            <label for="register-password">Password</label>
                            <input type="password" id="register-password" name="password" required>
                        </div>
                        <div class="fotlive-form-group">
                            <label for="register-confirm-password">Confirm Password</label>
                            <input type="password" id="register-confirm-password" name="confirm_password" required>
                        </div>
                        <div class="fotlive-form-actions">
                            <button type="submit" class="fotlive-btn fotlive-btn-primary">Register</button>
                        </div>
                    </form>
                </div>

                <!-- Forgot Password Form -->
                <div id="forgot-password-content" class="fotlive-tab-content">
                    <form id="fotlive-forgot-password-form" class="fotlive-form">
                        <div class="fotlive-form-group">
                            <label for="forgot-email">Email Address</label>
                            <input type="email" id="forgot-email" name="email" required>
                            <p class="text-sm text-gray-500 mt-1">
                                Enter your email address and we'll send you a code to reset your password.
                            </p>
                        </div>
                        <div class="fotlive-form-actions">
                            <button type="submit" class="fotlive-btn fotlive-btn-primary">Send Reset Code</button>
                        </div>
                    </form>
                    <div class="fotlive-form-footer">
                        <a href="#" id="back-to-login-link">Back to Login</a>
                    </div>
                </div>

                <!-- Reset Password Form -->
                <div id="reset-password-content" class="fotlive-tab-content">
                    <form id="fotlive-reset-password-form" class="fotlive-form">
                        <div class="fotlive-form-group">
                            <label for="reset-code">Reset Code</label>
                            <input type="text" id="reset-code" name="code" required>
                            <p class="text-sm text-gray-500 mt-1">
                                Enter the code that was sent to your email address.
                            </p>
                        </div>
                        <div class="fotlive-form-group">
                            <label for="new-password">New Password</label>
                            <input type="password" id="new-password" name="new_password" required>
                        </div>
                        <div class="fotlive-form-group">
                            <label for="confirm-new-password">Confirm New Password</label>
                            <input type="password" id="confirm-new-password" name="confirm_new_password" required>
                        </div>
                        <div class="fotlive-form-actions">
                            <button type="submit" class="fotlive-btn fotlive-btn-primary">Reset Password</button>
                        </div>
                    </form>
                    <div class="fotlive-form-footer">
                        <a href="#" id="back-to-login-link">Back to Login</a>
                    </div>
                </div>

                <div id="fotlive-login-message"></div>
            </div>

            <!-- Dashboard Section -->
            <div id="fotlive-dashboard-section" class="fotlive-dashboard" style="<?php echo $is_logged_in ? '' : 'display: none;'; ?>">
                <!-- Navigation -->
                <nav class="fotlive-nav">
                    <div class="fotlive-nav-container">
                        <div class="fotlive-nav-logo">
                            <svg class="fotlive-logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path>
                                <line x1="16" y1="8" x2="2" y2="22"></line>
                                <line x1="17.5" y1="15" x2="9" y2="15"></line>
                            </svg>
                            <span>LiveFot</span>
                        </div>
                        <div class="fotlive-nav-user">
                            <span id="fotlive-username" class="fotlive-username"><?php echo isset($_SESSION['fotlive_name']) ? htmlspecialchars($_SESSION['fotlive_name']) : ''; ?></span>
                            <button id="fotlive-logout-btn" class="fotlive-btn-link">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                Logout
                            </button>
                        </div>
                    </div>
                </nav>

                <div class="fotlive-dashboard-container">
                    <div class="fotlive-dashboard-grid">
                        <!-- Sidebar -->
                        <div class="fotlive-sidebar">
                            <nav class="fotlive-sidebar-nav">
                                <button class="fotlive-nav-item active" data-tab="profile">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    Profile
                                </button>
                                <button class="fotlive-nav-item" data-tab="leagues">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    Subscribed Leagues
                                </button>
                                <button class="fotlive-nav-item" data-tab="usage">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                        <line x1="18" y1="20" x2="18" y2="10"></line>
                                        <line x1="12" y1="20" x2="12" y2="4"></line>
                                        <line x1="6" y1="20" x2="6" y2="14"></line>
                                    </svg>
                                    API Usage
                                </button>
                                <button class="fotlive-nav-item" data-tab="subscriptions">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                        <line x1="1" y1="10" x2="23" y2="10"></line>
                                    </svg>
                                    Subscriptions
                                </button>
                            </nav>
                        </div>

                        <!-- Content Area -->
                        <div class="fotlive-content">
                            <!-- Profile Tab -->
                            <div class="fotlive-tab-content active" id="profile-tab">
                                <h2>Profile Information</h2>
                                <div class="fotlive-profile-grid">
                                    <div class="fotlive-profile-item">
                                        <label>Name</label>
                                        <p id="profile-name"><?php echo isset($_SESSION['fotlive_name']) ? htmlspecialchars($_SESSION['fotlive_name']) : ''; ?></p>
                                    </div>
                                    <div class="fotlive-profile-item">
                                        <label>Email</label>
                                        <p id="profile-email"><?php echo isset($_SESSION['fotlive_email']) ? htmlspecialchars($_SESSION['fotlive_email']) : ''; ?></p>
                                    </div>
                                    <div class="fotlive-profile-item">
                                        <label>User ID</label>
                                        <p id="profile-user-id"><?php echo isset($_SESSION['fotlive_user_id']) ? htmlspecialchars($_SESSION['fotlive_user_id']) : ''; ?></p>
                                    </div>
                                    <div class="fotlive-profile-item">
                                        <label>Status</label>
                                        <span id="profile-status" class="fotlive-status-badge"><?php echo isset($_SESSION['fotlive_status']) ? htmlspecialchars($_SESSION['fotlive_status']) : ''; ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Leagues Tab -->
                            <div class="fotlive-tab-content" id="leagues-tab">
                                <div class="fotlive-header-actions">
                                    <h2>Subscribed Leagues</h2>
                                    <button class="fotlive-btn-primary">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="16"></line>
                                            <line x1="8" y1="12" x2="16" y2="12"></line>
                                        </svg>
                                        Add League
                                    </button>
                                </div>
                                <div class="fotlive-table-container">
                                    <table class="fotlive-table">
                                        <thead>
                                            <tr>
                                                <th>League Name</th>
                                                <th>Status</th>
                                                <th>Id</th>
                                            </tr>
                                        </thead>
                                        <tbody id="leagues-table-body">
                                            <!-- Populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- API Usage Tab -->
                            <div class="fotlive-tab-content" id="usage-tab">
                                <h2>API Usage</h2>
                                <div class="fotlive-usage-grid" id="api-usage-grid">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>

                            <!-- Subscriptions Tab -->
                            <div class="fotlive-tab-content" id="subscriptions-tab">
                                <!-- Content dynamically loaded -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new FotLive_Login();