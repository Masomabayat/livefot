<?php
/**
 * Live Scores Dashboard page for AnWP Football Leagues
 *
 * @link       https://anwp.pro
 * @since      0.9.5
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

$live_options = [
	'_nonce_live_save'    => wp_create_nonce( 'anwpfl-live-save' ),
	'live_dashboard_mode' => 'manual',
];

$option__match_live_mode = AnWPFL_Premium_Options::get_value( 'match_live_mode', '' );
?>
<script type="text/javascript">
	var _flProLiveDashboardOptions = <?php echo wp_json_encode( $live_options ); ?>;
</script>
<div class="wrap anwp-b-wrap">

	<div class="mt-3 d-flex flex-wrap align-items-center">
		<h2 class="my-0 h2 mr-2"><?php echo esc_html__( 'Live Scores Dashboard', 'anwp-football-leagues-premium' ); ?></h2> -

		<div class="anwp-header-docs mx-2">
			<svg class="anwp-icon anwp-icon--octi anwp-icon--s16">
				<use xlink:href="#icon-book"></use>
			</svg>

			<a href="https://anwppro.userecho.com/knowledge-bases/2/articles/468-live-scores-dashboard" target="_blank">How to Use Live Scores Dashboard</a>
		</div>
	</div>

	<?php if ( 'yes' === $option__match_live_mode ) : ?>
		<div id="anwp-fl-app-live-scores"></div>
	<?php else : ?>
		<div class="alert alert-info mt-3">Live Scores is disabled in "FL+ Configurator."</div>
	<?php endif; ?>
</div>
