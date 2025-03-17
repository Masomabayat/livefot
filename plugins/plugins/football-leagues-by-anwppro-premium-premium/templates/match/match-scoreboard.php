<?php
/**
 * The Template for displaying Match Scoreboard.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-scoreboard.php.
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.5.10
 *
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar

$data = wp_parse_args(
	$data,
	[
		'show_match_datetime' => true,
		'kickoff'             => '',
		'kickoff_c'           => '',
		'match_date'          => '',
		'match_time'          => '',
		'club_links'          => true,
		'home_club'           => '',
		'away_club'           => '',
		'club_home_title'     => '',
		'club_away_title'     => '',
		'club_home_link'      => '',
		'club_away_link'      => '',
		'club_home_logo'      => '',
		'club_away_logo'      => '',
		'match_id'            => '',
		'finished'            => '',
		'home_goals'          => '',
		'away_goals'          => '',
		'match_week'          => '',
		'stadium_id'          => '',
		'competition_id'      => '',
		'main_stage_id'       => '',
		'stage_title'         => '',
		'attendance'          => '',
		'aggtext'             => '',
		'home_goals_half'     => '',
		'away_goals_half'     => '',
		'home_goals_p'        => '',
		'away_goals_p'        => '',
		'home_goals_ft'       => '',
		'away_goals_ft'       => '',
		'referee'             => '',
		'scoreboard_image'    => '',
		'context'             => 'match',
		'special_status'      => '',
	]
);

// Show Goal scorers
$home_goals = [];
$away_goals = [];

if ( absint( $data['finished'] ) ) {
	if ( ! empty( $data['parsed_events']['goals'] ) ) {
		foreach ( $data['parsed_events']['goals'] as $e ) {
			if ( $e->club === (int) $data['home_club'] ) {
				$home_goals[] = $e;
			} elseif ( $e->club === (int) $data['away_club'] ) {
				$away_goals[] = $e;
			}
		}
	}
}

if ( empty( $data['scoreboard_image'] ) ) {
	$data['scoreboard_image'] = anwp_fl_pro()->match->get_scoreboard_image(
		get_post( $data['match_id'] ),
		[
			'stadium_id' => $data['stadium_id'],
			'home_club'  => $data['home_club'],
		]
	);
}

/*
|--------------------------------------------------------------------
| Hide score before click
| @since 0.8.1
|--------------------------------------------------------------------
*/
$hide_score_click  = absint( $data['finished'] ) && 'yes' === AnWPFL_Premium_Options::get_value( 'match_hide_score_before_click', '' );
$prediction_advice = absint( $data['finished'] ) ? '' : anwp_fl_pro()->match->get_match_prediction_advice( $data['match_id'] );
$temp_players      = anwp_fl_pro()->match->get_temp_players( $data['match_id'] );
?>
<div style="background-image: url('<?php echo esc_attr( $data['scoreboard_image'] ); ?>')"
	data-fl-game-datetime="<?php echo esc_attr( $data['kickoff_c'] ); ?>"
	class="match-scoreboard anwp-image-background-cover py-md-1 px-md-4 match-status__<?php echo esc_attr( $data['finished'] ); ?> <?php echo $hide_score_click ? 'fl-hide-score-click' : ''; ?>">

	<div class="match-scoreboard__inner my-md-5">

		<?php
		/*
		|--------------------------------------------------------------------
		| Scoreboard Header
		|--------------------------------------------------------------------
		*/
		?>
		<div class="match-scoreboard__header anwp-text-center px-2 anwp-text-xs">
			<div class="match-scoreboard__header-line">
				<a class="anwp-link anwp-link-without-effects"
					href="<?php echo esc_url( get_permalink( (int) $data['main_stage_id'] ? : (int) $data['competition_id'] ) ); ?>">
					<?php echo esc_html( $data['stage_title'] ? ( $data['stage_title'] . ' - ' ) : '' ); ?>
					<?php echo esc_html( get_the_title( (int) $data['competition_id'] ) ); ?>
				</a>
				<?php if ( $data['match_week'] ) : ?>
					<span class="anwp-words-separator">|</span>
					<span class="anwp-text-nowrap"><?php echo esc_html( anwp_football_leagues()->competition->tmpl_get_matchweek_round_text( $data['match_week'], $data['competition_id'] ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="match-scoreboard__header-line d-flex flex-wrap justify-content-center align-items-center">
				<?php
				// Match stadium
				$stadium = intval( $data['stadium_id'] ) ? get_post( $data['stadium_id'] ) : null;

				if ( $stadium && 'publish' === $stadium->post_status ) :
					?>
					<span class="anwp-words-separator">|</span>
					<a class="anwp-link anwp-link-without-effects" href="<?php echo esc_url( get_permalink( $stadium ) ); ?>">
						<?php echo esc_html( $stadium->post_title ); ?>
					</a>
					<?php
				endif;

				if ( ( '0000-00-00 00:00:00' !== $data['kickoff'] ) ) :
					if ( 'TBD' === $data['special_status'] ) :
						$date_format = anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M Y';
						?>
						<span class="anwp-words-separator">|</span>
						<span class="match-scoreboard__kickoff match__date-formatted"><?php echo esc_html( date_i18n( $date_format, get_date_from_gmt( $data['kickoff'], 'U' ) ) ); ?></span>
						<?php
					else :
						$date_format = anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M Y';
						$time_format = anwp_football_leagues()->get_option_value( 'custom_match_time_format' ) ?: get_option( 'time_format' );
						?>
						<span class="anwp-words-separator">|</span>
						<span class="match-scoreboard__kickoff">
							<?php
							echo '<span class="match__date-formatted">' . esc_html( date_i18n( $date_format, get_date_from_gmt( $data['kickoff'], 'U' ) ) ) . '</span><span class="mx-1">-</span>';
							echo '<span class="match__time-formatted">' . esc_html( date_i18n( $time_format, get_date_from_gmt( $data['kickoff'], 'U' ) ) ) . '</span>';
							?>
						</span>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<?php
		/*
		|--------------------------------------------------------------------
		| Scoreboard Main Content: team name, logos and scores
		|--------------------------------------------------------------------
		*/
		?>
		<div class="match-scoreboard__main d-flex flex-wrap anwp-no-gutters">

			<div class="match-scoreboard__club-wrapper anwp-col px-2 anwp-min-width-0 anwp-order-1">
				<div class="d-flex flex-column position-relative">

					<img loading="lazy" width="80" height="80" class="anwp-object-contain mx-auto my-1 anwp-w-80 anwp-h-80"
						src="<?php echo esc_url( $data['club_home_logo'] ); ?>" alt="<?php echo esc_attr( $data['club_home_title'] ); ?>">

					<div class="match-scoreboard__club-title anwp-text-truncate anwp-text-white anwp-text-base">
						<?php echo esc_html( $data['club_home_title'] ); ?>
					</div>

					<a class="anwp-link-cover anwp-link-without-effects" href="<?php echo esc_url( $data['club_home_link'] ); ?>"></a>
					<?php
					if ( 'hide' !== AnWPFL_Premium_Options::get_value( 'match_club_form_scoreboard' ) ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo anwp_football_leagues_premium()->club->get_club_form( $data['home_club'], $data['kickoff'], 'my-2' );
					}
					?>
				</div>

			</div>
			<div class="anwp-order-4 w-100 d-sm-none"></div>
			<div class="anwp-col-sm-auto anwp-col mb-3 anwp-order-sm-2 anwp-order-5 anwp-text-center">
				<div class="anwp-text-nowrap match-scoreboard__scores d-flex d-sm-block">
					<span class="match-scoreboard__score-number anwp-flex-1"><?php echo esc_html( absint( $data['finished'] ) ? $data['home_goals'] : '-' ); ?></span>
					<span class="match-scoreboard__score-separator anwp-flex-none">:</span>
					<span class="match-scoreboard__score-number anwp-flex-1"><?php echo esc_html( absint( $data['finished'] ) ? $data['away_goals'] : '-' ); ?></span>
				</div>

				<div class="anwp-text-center px-2 py-3 my-4 d-none fl-show-score-wrapper">
					<button id="fl-show-score-btn" class="anwp-fl-btn-outline" type="button"><?php echo esc_html( AnWPFL_Text::get_value( 'match__scoreboard__show_score', __( 'Show Score', 'anwp-football-leagues-premium' ) ) ); ?></button>
				</div>

				<?php if ( absint( $data['finished'] ) ) : ?>
					<div class="match-scoreboard__text-result anwp-text-center anwp-text-uppercase pb-2">
						<span class="px-1 py-0">
							<?php
							if ( 'yes' === get_post_meta( $data['match_id'], '_anwpfl_custom_outcome', true ) && ! empty( get_post_meta( $data['match_id'], '_anwpfl_outcome_text', true ) ) ) {
								echo esc_html( get_post_meta( $data['match_id'], '_anwpfl_outcome_text', true ) );
							} else {
								$time_result = esc_html( AnWPFL_Text::get_value( 'match__match__full_time', __( 'Full Time', 'anwp-football-leagues' ) ) );

								switch ( intval( $data['extra'] ) ) {
									case 1:
										$time_result = esc_html( AnWPFL_Text::get_value( 'match__match__aet', _x( 'AET', 'Abbr: after extra time', 'anwp-football-leagues' ) ) );
										break;
									case 2:
										$time_result = esc_html( AnWPFL_Text::get_value( 'match__match__penalties', _x( 'Penalties', 'on penalties', 'anwp-football-leagues' ) ) ) . ' ' . $data['home_goals_p'] . '-' . $data['away_goals_p'];
										break;
								}
								echo esc_html( $time_result );
							}
							?>
						</span>
					</div>
				<?php elseif ( in_array( $data['special_status'], [ 'PST', 'CANC' ], true ) ) : ?>
					<div class="match-scoreboard__text-result anwp-text-center anwp-text-uppercase pb-2 anwp-text-sm">
						<span class="px-1 py-0">
							<?php echo esc_html( anwp_football_leagues()->data->get_value_by_key( $data['special_status'], 'special_status' ) ); ?>
						</span>
					</div>
				<?php endif; ?>
			</div>
			<div class="match-scoreboard__club-wrapper anwp-col px-2 anwp-min-width-0 anwp-order-3">
				<div class="d-flex flex-column position-relative">

					<img loading="lazy" width="80" height="80" class="anwp-object-contain mx-auto my-1 anwp-w-80 anwp-h-80"
						src="<?php echo esc_url( $data['club_away_logo'] ); ?>" alt="<?php echo esc_attr( $data['club_away_title'] ); ?>">

					<div class="match-scoreboard__club-title anwp-text-truncate anwp-text-white anwp-text-base">
						<?php echo esc_html( $data['club_away_title'] ); ?>
					</div>

					<a class="anwp-link-cover anwp-link-without-effects" href="<?php echo esc_url( $data['club_away_link'] ); ?>"></a>

					<?php
					if ( 'hide' !== AnWPFL_Premium_Options::get_value( 'match_club_form_scoreboard' ) ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo anwp_football_leagues_premium()->club->get_club_form( $data['away_club'], $data['kickoff'], 'my-2' );
					}
					?>
				</div>
			</div>
		</div>

		<?php
		/*
		|--------------------------------------------------------------------
		| Scoreboard Events: goal scorers
		|--------------------------------------------------------------------
		*/
		if ( absint( $data['finished'] ) && ( $home_goals || $away_goals ) ) :
			?>
			<div class="match-scoreboard__events d-flex anwp-no-gutters mb-2 anwp-text-xs">
				<div class="anwp-col d-flex flex-column anwp-min-width-0 pl-2">
					<?php if ( $home_goals ) : ?>
						<?php foreach ( $home_goals as $e ) : ?>
							<div class="match-scoreboard__goal-wrapper mt-1 d-flex anwp-min-width-0 justify-content-start">
								<div class="match-scoreboard__goal-icon anwp-icon mr-2">
									<svg class="match-scoreboard__icon icon__ball <?php echo esc_attr( 'yes' === $e->ownGoal ? 'icon__ball--own' : '' ); ?>">
										<use xlink:href="#<?php echo esc_attr( 'yes' === $e->fromPenalty ? 'icon-ball_penalty' : 'icon-ball' ); ?>"></use>
									</svg>
								</div>

								<?php if ( ! empty( $temp_players ) && 'temp__' === mb_substr( $e->player, 0, 6 ) ) : ?>
									<div class="match-scoreboard__goal-player anwp-text-truncate" data-toggle="anwp-tooltip"
										data-tippy-content="<?php echo esc_attr( isset( $temp_players[ $e->player ] ) ? $temp_players[ $e->player ]->name : '' ); ?>">
										<?php echo esc_html( isset( $temp_players[ $e->player ] ) ? $temp_players[ $e->player ]->name : '' ); ?>
									</div>
								<?php elseif ( ! empty( $e->player ) && ! empty( $data['players'][ $e->player ] ) ) : ?>
									<div class="match-scoreboard__goal-player anwp-text-truncate" data-toggle="anwp-tooltip"
										data-tippy-content="<?php echo esc_attr( $data['players'][ $e->player ]['short_name'] ); ?>">
										<?php echo esc_html( $data['players'][ $e->player ]['short_name'] ); ?>
									</div>
								<?php else : ?>
									<div class="match-scoreboard__goal-player anwp-text-truncate"></div>
								<?php endif; ?>

								<div class="match-scoreboard__goal-minute ml-2">
									<?php echo (int) $e->minute . ( intval( $e->minuteAdd ) ? ( '\'+' . intval( $e->minuteAdd ) ) : '' ); ?>'
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<div class="anwp-col anwp-min-width-0 d-flex flex-column pr-2">
					<?php if ( $away_goals ) : ?>
						<?php foreach ( $away_goals as $e ) : ?>
							<div class="match-scoreboard__goal-wrapper mt-1 d-flex anwp-min-width-0 justify-content-end">

								<?php if ( ! empty( $temp_players ) && 'temp__' === mb_substr( $e->player, 0, 6 ) ) : ?>
									<div class="match-scoreboard__goal-player anwp-text-truncate" data-toggle="anwp-tooltip"
										data-tippy-content="<?php echo esc_attr( isset( $temp_players[ $e->player ] ) ? $temp_players[ $e->player ]->name : '' ); ?>">
										<?php echo esc_html( isset( $temp_players[ $e->player ] ) ? $temp_players[ $e->player ]->name : '' ); ?>
									</div>
								<?php elseif ( ! empty( $e->player ) && ! empty( $data['players'][ $e->player ] ) ) : ?>
									<div class="match-scoreboard__goal-player text-truncate" data-toggle="anwp-tooltip"
										data-tippy-content="<?php echo esc_attr( $data['players'][ $e->player ]['short_name'] ); ?>">
										<?php echo esc_html( $data['players'][ $e->player ]['short_name'] ); ?>
									</div>
								<?php else : ?>
									<div class="match-scoreboard__goal-player"></div>
								<?php endif; ?>

								<div class="match-scoreboard__goal-minute ml-2 d-inline-block">
									<?php echo (int) $e->minute . ( intval( $e->minuteAdd ) ? ( '\'+' . intval( $e->minuteAdd ) ) : '' ); ?>'
								</div>

								<div class="match-scoreboard__goal-icon anwp-icon ml-2 d-inline-block">
									<svg class="match-scoreboard__icon icon__ball <?php echo esc_attr( 'yes' === $e->ownGoal ? 'icon__ball--own' : '' ); ?>">
										<use xlink:href="#<?php echo esc_attr( 'yes' === $e->fromPenalty ? 'icon-ball_penalty' : 'icon-ball' ); ?>"></use>
									</svg>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

			</div>
		<?php endif; ?>

		<?php
		/*
		|--------------------------------------------------------------------
		| Scoreboard Countdown // fixture_flip_countdown
		|--------------------------------------------------------------------
		*/
		if ( ! absint( $data['finished'] ) ) :
			anwp_football_leagues()->load_partial( $data, 'match/match-countdown', 'modern' );
		endif;
		?>

		<?php
		/*
		|--------------------------------------------------------------------
		| Scoreboard Footer
		|--------------------------------------------------------------------
		*/
		?>
		<div class="match-scoreboard__footer anwp-text-center px-2 anwp-text-xs">
			<div class="match-scoreboard__footer-line">
				<?php
				// Prepare Footer line text
				$footer_texts = [];

				if ( (int) $data['attendance'] ) {
					$footer_texts[] = esc_html( AnWPFL_Text::get_value( 'match__match__attendance', __( 'Attendance', 'anwp-football-leagues' ) ) ) . ': ' . number_format_i18n( (int) $data['attendance'] );
				}

				if ( absint( $data['referee'] ) ) {
					$footer_texts[] = esc_html( AnWPFL_Text::get_value( 'match__match__referee', __( 'Referee', 'anwp-football-leagues' ) ) ) . ': ' . get_the_title( $data['referee'] );
				}

				if ( absint( $data['finished'] ) && apply_filters( 'anwpfl/match/show_half_time_score', true ) ) {
					$footer_texts[] = esc_html( AnWPFL_Text::get_value( 'match__match__half_time', __( 'Half Time', 'anwp-football-leagues' ) ) ) . ': ' . $data['home_goals_half'] . '-' . $data['away_goals_half'];
				}

				if ( absint( $data['finished'] ) && apply_filters( 'anwpfl/match/show_full_time_score', true ) && ( '1' === $data['extra'] || '2' === $data['extra'] ) ) {
					$footer_texts[] = esc_html( AnWPFL_Text::get_value( 'match__match__full_time', __( 'Full Time', 'anwp-football-leagues' ) ) ) . ': ' . $data['home_goals_ft'] . '-' . $data['away_goals_ft'];
				}

				if ( $data['aggtext'] ) {
					$footer_texts[] = $data['aggtext'];
				}
				?>

				<?php foreach ( $footer_texts as $text ) : ?>
					<span class="anwp-words-separator">|</span>
					<span><?php echo esc_html( $text ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php if ( $prediction_advice ) : ?>
				<div class="match-scoreboard__footer-line anwp-match-prediction-wrapper">
					<span class="anwp-font-semibold mr-1"><?php echo esc_html( AnWPFL_Text::get_value( 'match__match__prediction', __( 'Prediction', 'anwp-football-leagues-premium' ) ) ); ?>: </span><?php echo esc_html( $prediction_advice ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
