<?php
/**
 * The Template for displaying Match >> Timeline Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-timeline.php.
 *
 * // phpcs:disable WordPress.NamingConventions.ValidVariableName
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.1.0
 *
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$data,
	[
		'club_links'      => 'yes',
		'home_club'       => '',
		'away_club'       => '',
		'club_home_title' => '',
		'club_away_title' => '',
		'club_home_logo'  => '',
		'club_away_logo'  => '',
		'match_id'        => '',
		'finished'        => '',
		'parsed_events'   => [],
		'extra'           => '',
	]
);

if ( ! absint( $data['finished'] ) && empty( get_post_meta( $data['match_id'], '_anwpfl_live_status', true ) ) && ! anwp_football_leagues_premium()->live->is_api_game_active( $data['match_id'] ) ) {
	return;
}

// Get match duration
$full_time = (int) get_post_meta( $data['match_id'], '_anwpfl_duration_full', true ) ? : 90;
$half_time = intval( $full_time / 2 );

$extra_time      = (int) get_post_meta( $data['match_id'], '_anwpfl_duration_extra', true ) ? : 30;
$extra_half_time = $extra_time / 2;

$events = [];

if ( ! empty( $data['parsed_events']['goals'] ) && is_array( $data['parsed_events']['goals'] ) ) {
	foreach ( $data['parsed_events']['goals'] as $event ) {

		// Get event minute
		$minute = intval( $event->minute ) + intval( $event->minuteAdd );

		// Populate Events array
		$events[ $minute ][] = $event;
	}
}

if ( ! empty( $data['parsed_events']['cards'] ) && is_array( $data['parsed_events']['cards'] ) ) {
	foreach ( $data['parsed_events']['cards'] as $event ) {

		// Get event minute
		$minute = intval( $event->minute ) + intval( $event->minuteAdd );

		// Populate Events array
		$events[ $minute ][] = $event;
	}
}

if ( ! empty( $data['parsed_events']['subs'] ) && is_array( $data['parsed_events']['subs'] ) ) {
	foreach ( $data['parsed_events']['subs'] as $event ) {

		// Get event minute
		$minute = intval( $event->minute ) + intval( $event->minuteAdd );

		// Populate Events array
		$events[ $minute ][] = $event;
	}
}

if ( ! empty( $data['parsed_events']['missed_penalty'] ) && is_array( $data['parsed_events']['missed_penalty'] ) ) {
	foreach ( $data['parsed_events']['missed_penalty'] as $event ) {

		// Get event minute
		$minute = intval( $event->minute ) + intval( $event->minuteAdd );

		// Populate Events array
		$events[ $minute ][] = $event;
	}
}

if ( empty( $events ) && empty( get_post_meta( $data['match_id'], '_anwpfl_live_status', true ) ) && ! anwp_football_leagues_premium()->live->is_api_game_active( $data['match_id'] ) ) {
	return '';
}

ksort( $events, SORT_NUMERIC );

// Get extended full time
$full_time_extended = $full_time;

if ( 0 === intval( $data['extra'] ) && is_array( $events ) && ! empty( $events ) ) {
	$latest_event       = max( array_keys( $events ) );
	$full_time_extended = max( $latest_event, $full_time_extended );
}

$initial_side = is_rtl() ? 'right' : 'left';
$temp_players = anwp_football_leagues_premium()->match->get_temp_players( $data['match_id'] );
?>
<div class="anwp-section match-timeline d-flex flex-wrap py-4 px-2 align-items-center no-gutters">

	<span class="anwp-text-center d-inline-block mb-3 w-100 d-md-none"><?php echo esc_html( AnWPFL_Text::get_value( 'match__timeline__1_st_half', __( '1st Half', 'anwp-football-leagues-premium' ) ) ); ?></span>

	<div class="match-timeline__club_logos mr-2 anwp-flex-none d-flex flex-column">
		<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data['club_home_logo'] ); ?>" alt="club logo">
		<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data['club_away_logo'] ); ?>" alt="club logo">
	</div>

	<div class="match-timeline__inner anwp-col mr-2 position-relative">
		<div class="match-timeline__progress d-flex">
			<div class="match-timeline__progress-filled d-flex align-items-center justify-content-end" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
				<span class="px-1 anwp-text-white anwp-text-sm"><?php echo (int) $half_time; ?>'</span>
			</div>
		</div>
		<?php
		foreach ( $events as $minute => $minute_events ) :
			if ( $minute <= $half_time ) :
				?>
				<div class="match-timeline__minute position-absolute" style="<?php echo esc_attr( $initial_side ); ?>: <?php echo esc_attr( $minute / ( $half_time / 100 ) ); ?>%">
					<?php
					foreach ( $minute_events as $event_index => $event ) :

						$event_class  = ( $event->club === (int) $data['home_club'] ) ? 'match-timeline__item-home' : 'match-timeline__item-away';
						$tooltip_text = intval( $event->minute ) . ( intval( $event->minuteAdd ) ? ( '\'+' . intval( $event->minuteAdd ) ) : '' ) . '\' ' . anwp_fl()->match->get_event_name_by_type( $event ) . ': ' . ( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->player, 0, 6 ) ? $temp_players[ $event->player ]->name : ( $data['players'][ $event->player ]['short_name'] ?? '' ) );

						if ( 'substitute' === $event->type ) {
							$tooltip_text .= ' > ' . ( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->playerOut, 0, 6 ) ? $temp_players[ $event->playerOut ]->name : ( $data['players'][ $event->playerOut ]['short_name'] ?? '' ) );
						}

						if ( 'goal' === $event->type && ! empty( $event->assistant ) ) {
							$tooltip_text .= ' (' . esc_html( AnWPFL_Text::get_value( 'match__timeline__assistant', __( 'assistant', 'anwp-football-leagues-premium' ) ) );
							$tooltip_text .= ': ' . ( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->assistant, 0, 6 ) ? $temp_players[ $event->assistant ]->name : ( $data['players'][ $event->assistant ]['short_name'] ?? '' ) ) . ')';
						}
						?>
						<div class="match-timeline__item position-absolute <?php echo esc_attr( $event_class ); ?>">
							<div class="match__timeline-icon" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $tooltip_text ); ?>">
								<?php if ( 'goal' === $event->type ) : ?>
									<svg class="match-timeline__icon icon__ball <?php echo esc_attr( 'yes' === $event->ownGoal ? 'icon__ball--own' : '' ); ?>">
										<use xlink:href="#<?php echo esc_attr( 'yes' === $event->fromPenalty ? 'icon-ball_penalty' : 'icon-ball' ); ?>"></use>
									</svg>
								<?php elseif ( 'substitute' === $event->type ) : ?>
									<svg class="match-timeline__icon icon__substitute">
										<use xlink:href="#icon-substitute"></use>
									</svg>
								<?php elseif ( 'card' === $event->type ) : ?>
									<svg class="match-timeline__icon icon__card">
										<use xlink:href="#icon-card_<?php echo esc_attr( $event->card ); ?>"></use>
									</svg>
								<?php elseif ( 'missed_penalty' === $event->type ) : ?>
									<svg class="match-timeline__icon  icon__ball">
										<use xlink:href="#icon-ball_canceled"></use>
									</svg>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<span class="anwp-text-center d-inline-block my-3 pt-1 anwp-fl-border-top anwp-border-light w-100 d-md-none"><?php echo esc_html( AnWPFL_Text::get_value( 'match__timeline__2_nd_half', __( '2nd Half', 'anwp-football-leagues-premium' ) ) ); ?></span>

	<div class="match-timeline__club_logos mr-2 anwp-flex-none d-md-none d-flex flex-column">
		<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data['club_home_logo'] ); ?>" alt="club logo">
		<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data['club_away_logo'] ); ?>" alt="club logo">
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
			if ( $minute > $half_time ) :
				?>
				<div class="match-timeline__minute position-absolute" style="<?php echo esc_attr( $initial_side ); ?>: <?php echo esc_attr( ( $minute - $half_time ) / ( ( $full_time_extended - $half_time ) / 100 ) ); ?>%">
					<?php
					foreach ( $minute_events as $event_index => $event ) :

						if ( $event->minute > $full_time ) {
							continue;
						}

						$event_class  = ( $event->club === (int) $data['home_club'] ) ? 'match-timeline__item-home' : 'match-timeline__item-away';
						$tooltip_text = intval( $event->minute ) . ( intval( $event->minuteAdd ) ? ( '\'+' . intval( $event->minuteAdd ) ) : '' ) . '\' ' . anwp_fl()->match->get_event_name_by_type( $event ) . ': ' . ( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->player, 0, 6 ) ? $temp_players[ $event->player ]->name : ( $data['players'][ $event->player ]['short_name'] ?? '' ) );

						if ( 'substitute' === $event->type ) {
							$tooltip_text .= ' > ' . ( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->playerOut, 0, 6 ) ? $temp_players[ $event->playerOut ]->name : ( $data['players'][ $event->playerOut ]['short_name'] ?? '' ) );
						}

						if ( 'goal' === $event->type && ! empty( $event->assistant ) ) {
							$tooltip_text .= ' (' . esc_html( AnWPFL_Text::get_value( 'match__timeline__assistant', __( 'assistant', 'anwp-football-leagues-premium' ) ) );
							$tooltip_text .= ': ' . ( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->assistant, 0, 6 ) ? $temp_players[ $event->assistant ]->name : ( $data['players'][ $event->assistant ]['short_name'] ?? '' ) ) . ')';
						}
						?>
						<div class="match-timeline__item position-absolute <?php echo esc_attr( $event_class ); ?>">
							<div class="match__timeline-icon" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $tooltip_text ); ?>">
								<?php if ( 'goal' === $event->type ) : ?>
									<svg class="match-timeline__icon icon__ball <?php echo esc_attr( 'yes' === $event->ownGoal ? 'icon__ball--own' : '' ); ?>">
										<use xlink:href="#<?php echo esc_attr( 'yes' === $event->fromPenalty ? 'icon-ball_penalty' : 'icon-ball' ); ?>"></use>
									</svg>
								<?php elseif ( 'substitute' === $event->type ) : ?>
									<svg class="match-timeline__icon icon__substitute">
										<use xlink:href="#icon-substitute"></use>
									</svg>
								<?php elseif ( 'card' === $event->type ) : ?>
									<svg class="match-timeline__icon icon__card">
										<use xlink:href="#icon-card_<?php echo esc_attr( $event->card ); ?>"></use>
									</svg>
								<?php elseif ( 'missed_penalty' === $event->type ) : ?>
									<svg class="match-timeline__icon  icon__ball">
										<use xlink:href="#icon-ball_canceled"></use>
									</svg>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<?php if ( $data['extra'] > 0 || 'yes' === get_post_meta( $data['match_id'], '_anwpfl_extra_time', true ) ) : ?>
		<span class="anwp-text-center d-inline-block my-3 pt-1 anwp-fl-border-top anwp-border-light w-100 d-md-none"><?php echo esc_html( AnWPFL_Text::get_value( 'match__timeline__extra_time', __( 'Extra Time', 'anwp-football-leagues-premium' ) ) ); ?></span>
		<div class="match-timeline__club_logos mr-2 anwp-flex-none d-md-none d-flex flex-column">
			<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data['club_home_logo'] ); ?>" alt="club logo">
			<img loading="lazy" width="40" height="40" class="anwp-object-contain m-1 anwp-w-40 anwp-h-40" src="<?php echo esc_url( $data['club_away_logo'] ); ?>" alt="club logo">
		</div>
		<div class="match-timeline__inner anwp-col-auto mr-2 position-relative">
			<div class="match-timeline__progress d-flex" style="width: 100px;">
				<div class="match-timeline__progress-filled match-timeline__progress-filled--extra d-flex align-items-center justify-content-end" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
					<span class="px-1 anwp-text-white anwp-text-sm"><?php echo absint( $full_time + $extra_time ); ?>'</span>
				</div>
			</div>
			<?php
			foreach ( $events as $minute => $minute_events ) :
				if ( $minute > $full_time ) :
					?>
					<div class="match-timeline__minute position-absolute" style="<?php echo esc_attr( $initial_side ); ?>: <?php echo esc_attr( ( $minute - $full_time ) / ( $extra_time / 100 ) ); ?>%">
						<?php
						foreach ( $minute_events as $event_index => $event ) :

							$event_class  = $event->club === (int) $data['home_club'] ? 'match-timeline__item-home' : 'match-timeline__item-away';
							$tooltip_text = $minute . '\' ' . anwp_fl()->match->get_event_name_by_type( $event ) . ': ' . ( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->player, 0, 6 ) ? $temp_players[ $event->player ]->name : ( $data['players'][ $event->player ]['short_name'] ?? '' ) );

							if ( 'substitute' === $event->type ) {
								$tooltip_text .= ' > ' . ( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->playerOut, 0, 6 ) ? $temp_players[ $event->playerOut ]->name : ( $data['players'][ $event->playerOut ]['short_name'] ?? '' ) );
							}

							if ( 'goal' === $event->type && ! empty( $event->assistant ) ) {
								$tooltip_text .= ' (' . esc_html( AnWPFL_Text::get_value( 'match__timeline__assistant', __( 'assistant', 'anwp-football-leagues-premium' ) ) );
								$tooltip_text .= ': ' . ( ! empty( $temp_players ) && 'temp__' === mb_substr( $event->assistant, 0, 6 ) ? $temp_players[ $event->assistant ]->name : ( $data['players'][ $event->assistant ]['short_name'] ?? '' ) ) . ')';
							}
							?>
							<div class="match-timeline__item position-absolute <?php echo esc_attr( $event_class ); ?>">
								<div class="match__timeline-icon" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $tooltip_text ); ?>">
									<?php if ( 'goal' === $event->type ) : ?>
										<svg class="match-timeline__icon icon__ball <?php echo esc_attr( 'yes' === $event->ownGoal ? 'icon__ball--own' : '' ); ?>">
											<use xlink:href="#<?php echo esc_attr( 'yes' === $event->fromPenalty ? 'icon-ball_penalty' : 'icon-ball' ); ?>"></use>
										</svg>
									<?php elseif ( 'substitute' === $event->type ) : ?>
										<svg class="match-timeline__icon icon__substitute">
											<use xlink:href="#icon-substitute"></use>
										</svg>
									<?php elseif ( 'card' === $event->type ) : ?>
										<svg class="match-timeline__icon icon__card">
											<use xlink:href="#icon-card_<?php echo esc_attr( $event->card ); ?>"></use>
										</svg>
									<?php elseif ( 'missed_penalty' === $event->type ) : ?>
										<svg class="match-timeline__icon  icon__ball">
											<use xlink:href="#icon-ball_canceled"></use>
										</svg>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
