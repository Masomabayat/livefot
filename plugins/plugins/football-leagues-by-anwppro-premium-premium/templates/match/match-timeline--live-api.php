<?php
/**
 * The Template for displaying Match >> Timeline Section (LIVE API Import).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-timeline__live-api.php.
 *
 * // phpcs:disable WordPress.NamingConventions.ValidVariableName
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.14.10
 *
 * @version       0.14.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'club_home_logo' => '',
		'club_away_logo' => '',
		'events'         => [],
	]
);

// Get match duration
$full_time = 90;
$half_time = 45;

$extra_time      = 30;
$extra_half_time = 15;

if ( empty( $data->events ) ) {
	return '';
}

$events = [];

foreach ( $data->events as $event ) {
	$events[ intval( $event['time']['elapsed'] ) + intval( $event['time']['extra'] ) ][] = $event;
}

ksort( $events, SORT_NUMERIC );

// Get extended full time
$latest_event       = max( array_keys( $events ) );
$full_time_extended = max( $latest_event, 90 );

$initial_side = is_rtl() ? 'right' : 'left';
?>
<div class="anwp-section match-timeline d-flex flex-wrap py-4 px-2 align-items-center no-gutters">

	<span class="anwp-text-center d-inline-block mb-3 w-100 d-md-none"><?php echo esc_html( AnWPFL_Text::get_value( 'match__timeline__1_st_half', __( '1st Half', 'anwp-football-leagues-premium' ) ) ); ?></span>

	<div class="match-timeline__club_logos mr-2 anwp-flex-none d-flex flex-column">
		<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="club logo">
		<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="club logo">
	</div>

	<div class="match-timeline__inner anwp-col mr-2 position-relative">
		<div class="match-timeline__progress d-flex">
			<div class="match-timeline__progress-filled d-flex align-items-center justify-content-end" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
				<span class="px-1 anwp-text-white anwp-text-sm"><?php echo (int) $half_time; ?>'</span>
			</div>
		</div>
		<?php
		foreach ( $events as $minute => $minute_events ) :
			if ( $minute_events[0]['time']['elapsed'] <= 45 ) :
				?>
				<div class="match-timeline__minute position-absolute" style="<?php echo esc_attr( $initial_side ); ?>: <?php echo esc_attr( $minute / ( 45 / 100 ) ); ?>%">
					<?php
					foreach ( $minute_events as $event_index => $event ) :
						echo anwp_football_leagues_premium()->live->render_timeline_event_api( $event, $data ); //phpcs:ignore
					endforeach;
					?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<span class="anwp-text-center d-inline-block my-3 pt-1 anwp-fl-border-top anwp-border-light w-100 d-md-none"><?php echo esc_html( AnWPFL_Text::get_value( 'match__timeline__2_nd_half', __( '2nd Half', 'anwp-football-leagues-premium' ) ) ); ?></span>

	<div class="match-timeline__club_logos mr-2 anwp-flex-none d-md-none d-flex flex-column">
		<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="club logo">
		<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="club logo">
	</div>

	<div class="match-timeline__inner anwp-col mr-2 position-relative">
		<div class="match-timeline__progress d-flex">
			<div class="match-timeline__progress-filled d-flex align-items-center justify-content-end" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
				<?php
				// Full time offset
				$full_time_offset = 0;

				if ( ( $full_time_extended - 1 ) > $full_time ) {
					$full_time_offset = ( $full_time_extended - $full_time - 1 ) / $half_time * 100;
				}

				?>
				<span class="px-1 anwp-text-white anwp-text-sm" style="<?php echo $full_time_offset ? esc_attr( 'margin-right:' . $full_time_offset . '%; padding-right: 0 !important;' ) : ''; ?>"><?php echo (int) $full_time; ?>'</span>
			</div>
		</div>
		<?php
		foreach ( $events as $minute => $minute_events ) :
			if ( $minute_events[0]['time']['elapsed'] > 45 && $minute_events[0]['time']['elapsed'] <= 90 ) :
				?>
				<div class="match-timeline__minute position-absolute" style="<?php echo esc_attr( $initial_side ); ?>: <?php echo esc_attr( ( $minute - $half_time ) / ( ( $full_time_extended - $half_time ) / 100 ) ); ?>%">
					<?php
					foreach ( $minute_events as $event_index => $event ) :
						echo anwp_football_leagues_premium()->live->render_timeline_event_api( $event, $data ); //phpcs:ignore
					endforeach;
					?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<?php if ( $events[ $latest_event ][0]['time']['elapsed'] > 90 ) : ?>
		<span class="anwp-text-center d-inline-block my-3 pt-1 anwp-fl-border-top anwp-border-light w-100 d-md-none"><?php echo esc_html( AnWPFL_Text::get_value( 'match__timeline__extra_time', __( 'Extra Time', 'anwp-football-leagues-premium' ) ) ); ?></span>
		<div class="match-timeline__club_logos mr-2 anwp-flex-none d-md-none d-flex flex-column">
			<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="club logo">
			<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="club logo">
		</div>
		<div class="match-timeline__inner anwp-col-auto mr-2 position-relative">
			<div class="match-timeline__progress d-flex" style="width: 100px;">
				<div class="match-timeline__progress-filled match-timeline__progress-filled--extra d-flex align-items-center justify-content-end" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
					<span class="px-1 anwp-text-white anwp-text-sm"><?php echo absint( $full_time + $extra_time ); ?>'</span>
				</div>
			</div>
			<?php
			foreach ( $events as $minute => $minute_events ) :
				if ( $minute_events[0]['time']['elapsed'] > 90 ) :
					?>
					<div class="match-timeline__minute position-absolute" style="<?php echo esc_attr( $initial_side ); ?>: <?php echo esc_attr( ( $minute - $full_time ) / ( $extra_time / 100 ) ); ?>%">
						<?php
						foreach ( $minute_events as $event_index => $event ) :
							echo anwp_football_leagues_premium()->live->render_timeline_event_api( $event, $data ); //phpcs:ignore
						endforeach;
						?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
