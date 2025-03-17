<?php
/**
 * Support page for AnWP Football Leagues
 *
 * @link       https://anwp.pro
 * @since      0.5.5
 *
 * @package    AnWP_Football_Leagues
 * @subpackage AnWP_Football_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues' ) );
}

global $wp_version, $wpdb;

$database_tables = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT table_name AS 'name'
				FROM information_schema.TABLES
				WHERE table_schema = %s
				ORDER BY name ASC;",
		DB_NAME
	)
);

try {
	$matches = get_posts(
		[
			'numberposts' => - 1,
			'post_type'   => 'anwp_match',
			'post_status' => 'publish',
			'fields'      => 'ids',
		]
	);

	$matches_qty = is_array( $matches ) ? count( $matches ) : 0;

	$stats_qty = $wpdb->get_var(
		"
				SELECT COUNT(*)
				FROM {$wpdb->prefix}anwpfl_matches
				"
	);
} catch ( RuntimeException $e ) {
	$matches_qty = 0;
	$stats_qty   = 0;
}
?>

<div class="about-wrap anwp-b-wrap">
	<div class="postbox">
		<div class="inside">
			<h2 class="text-left text-uppercase">Premium Plugin Support</h2>

			<hr>

			<h4>Before sending a support request, please check:</h4>
			<ul>
				<li>- free core plugin is installed and activated</li>
				<li>- a premium plugin is installed and activated</li>
				<li>- required CMB2 plugin is installed and activated</li>
			</ul>

			<hr>

			<p>
				Support hours are Monday to Friday, 09:00 to 17:00 CEST (GMT +02:00).
				<br>I will endeavor to respond to all requests within 48 hours.
				<br><br>
				Questions asked on the weekends or holidays in Poland will be answered in the next business day.
			</p>

			<p>To get support, please just email me at <code>fl-premium-support@anwppro.userecho.com</code></p>
			<p>with the following email subject <code>[PREMIUM SUPPORT] FL - <?php echo esc_html( get_site_url() ); ?></code> </p>

			<p class="mt-3 mb-0 h4">System Info:</p>

			<ul>
				<li>============================================</li>
				<li>
					<b>Free Plugin Version:</b> AnWP Football Leagues <?php echo esc_html( AnWP_Football_Leagues::VERSION ); ?>
				</li>
				<li>
					<b>Premium Plugin Version:</b> AnWP Football Leagues Premium <?php echo esc_html( AnWP_Football_Leagues_Premium::VERSION ); ?>
				</li>

				<li>
					<b>WordPress version:</b> <?php echo esc_html( $wp_version ); ?>
				</li>

				<li>
					<b>PHP version:</b> <?php echo esc_html( phpversion() ); ?>
				</li>

				<li>
					<b>Server Time:</b> <?php echo esc_html( date_default_timezone_get() ); ?>
				</li>

				<li>
					<b>WP Time:</b> <?php echo esc_html( get_option( 'timezone_string' ) ); ?>
				</li>

				<li>
					<b>Current Date:</b> <?php echo esc_html( date_i18n( 'Y-m-d' ) ); ?>
				</li>

				<li>
					<b>Site Locale:</b> <?php echo esc_html( get_locale() ); ?>
				</li>

				<li>
					<b>Plugin DB version:</b> <?php echo esc_html( get_option( 'anwpfl_db_version' ) ); ?>
				</li>

				<li>
					<b>Plugin Premium DB version:</b> <?php echo esc_html( get_option( 'anwpfl_premium_db_version' ) ); ?>
				</li>

				<li>
					<b>Multisite:</b> <?php echo esc_html( is_multisite() ? 'yes' : 'no' ); ?>
				</li>


				<li>
					<b>Statistic records:</b> (matches/stats - <?php echo intval( $matches_qty ); ?>/<?php echo intval( $stats_qty ); ?>)
				</li>

				<li>
					<b>Active Plugins:</b>
					<?php
					foreach ( get_option( 'active_plugins' ) as $value ) {
						$string = explode( '/', $value );
						echo '<br>--- ' . esc_html( $string[0] );
					}
					?>
				</li>
				<li>
					<b><?php echo esc_html_x( 'List of DB tables', 'support page', 'anwp-football-leagues' ); ?>:</b><br>
					<?php
					if ( ! empty( $database_tables ) && is_array( $database_tables ) ) {
						$database_tables = wp_list_pluck( $database_tables, 'name' );

						if ( is_array( $database_tables ) ) {
							echo esc_html( implode( ', ', $database_tables ) );
						}
					}
					?>
				</li>
				<li>============================================</li>
			</ul>

			<h5>Customization request or CSS conflict</h5>

			<p>
				If you a have a CSS problem (conflict with your theme) or Customization request, attach a URL to that page and a screenshot with comments (System Info not needed).
				Customization request has lower priority and response time may be increased.
			</p>

		</div>
	</div>
</div>
