<?php
/**
 * The Template for displaying Club >> Birthdays Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/club/club-birthdays.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.10.1
 * @version       0.10.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'club_id' => '',
	]
);

if ( ! absint( $data->club_id ) ) {
	return;
}
?>
<div class="club__birthdays club-section anwp-section">
	<?php
	$shortcode_attr = [
		'club_id'       => $data->club_id,
		'group_by_date' => 1,
	];

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo anwp_football_leagues()->template->shortcode_loader( 'premium-birthdays', $shortcode_attr );
	?>
</div>
