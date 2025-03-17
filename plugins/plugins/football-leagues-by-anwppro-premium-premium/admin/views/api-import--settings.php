<?php
/**
 * Import API subpage for AnWP Football Leagues
 *
 * @link       https://anwp.pro
 * @since      0.13.0
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
?>
<div id="fl-app-api-import--settings"></div>
