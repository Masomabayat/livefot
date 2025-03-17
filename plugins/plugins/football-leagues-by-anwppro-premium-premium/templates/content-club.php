<?php
/**
 * The Template for displaying club content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/content-club.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.3.0
 * @version       0.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Prepare tmpl data
$club   = get_post();
$prefix = '_anwpfl_';
$data   = [];

$fields = [
	'logo_big',
	'description',
	'city',
	'nationality',
	'address',
	'website',
	'founded',
	'stadium',
	'club_kit',
	'twitter',
	'youtube',
	'facebook',
	'instagram',
	'vk',
	'tiktok',
	'linkedin',
];

foreach ( $fields as $field ) {
	$data[ $field ] = $club->{$prefix . $field};
}

/**
 * Filter: anwpfl/tmpl-club/data_fields
 *
 * @since 0.7.5
 *
 * @param array   $data
 * @param WP_Post $club
 */
$data = apply_filters( 'anwpfl/tmpl-club/data_fields', $data, $club );

$data['club_id']   = $club->ID;
$data['season_id'] = anwp_football_leagues_premium()->club->get_post_season( $club->ID );

// Check Tabs mode
$data['tabs_mode'] = 'list' !== anwp_football_leagues()->get_option_value( 'club_sections_mode' );
?>
<div class="anwp-b-wrap club club__inner club-<?php echo (int) $club->ID; ?>">
	<?php
	if ( 'summary' === get_post_meta( $club->ID, '_anwpfl_root_type', true ) ) {
		$club_sections = [
			'header',
			'description',
			'fixtures',
			'latest',
			'squad',
			'gallery',
		];
	} else {
		$club_sections = [
			'header',
			'description',
			'game-stats',
			'fixtures',
			'latest',
			'squad',
			'stats',
			'gallery',
			'transfers',
		];
	}

	foreach ( $club_sections as $section ) {
		anwp_football_leagues()->load_partial( $data, 'club/club-' . sanitize_key( $section ) );
	}
	?>
</div>
