<?php
/**
 * The Template for displaying Bracket view for competition layout.
 * Manual ordering and sorting.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-bracket.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.8.9
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'competition_id' => 0,
		'final_stage_id' => 0,
	]
);

$competition_id = absint( $data->final_stage_id );

if ( empty( $competition_id ) || 'knockout' !== get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
	return;
}

// Check automatic layout ("show" value used before - backward compatibility)
if ( 'show' === get_post_meta( $competition_id, '_anwpfl_bracket', true ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo anwp_football_leagues()->template->shortcode_loader( 'premium-bracket-auto', $data );
	return;
}

/*
|--------------------------------------------------------------------
| Group Matches
|--------------------------------------------------------------------
*/
$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches( $competition_id, false );

$group_matches = [];
foreach ( $matches as $match ) {
	$group_matches[ $match->group_id ][] = $match;
}

/*
|--------------------------------------------------------------------
| Get Rounds, Groups and Bracket Options
|--------------------------------------------------------------------
*/
$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );
$rounds = json_decode( get_post_meta( $competition_id, '_anwpfl_rounds', true ) );

if ( empty( $rounds ) || ! is_array( $rounds ) || empty( $groups ) || ! is_array( $groups ) ) {
	return;
}

$bracket_options = json_decode( get_post_meta( $competition_id, '_anwpfl_bracket_options', true ) );

if ( empty( $bracket_options ) || ! is_object( $bracket_options ) ) {
	$bracket_options = (object) [];
}

/*
|--------------------------------------------------------------------
| Start building Bracket data
|--------------------------------------------------------------------
*/
$bracket_data = [];

foreach ( $rounds as $round ) {
	$bracket_data[ $round->id ] = [
		'id'     => $round->id,
		'groups' => [],
		'title'  => $round->title,
	];
}

foreach ( $groups as $group_index => $group ) {

	if ( empty( $bracket_data[ $group->round ] ) ) {
		continue;
	}

	$saved_group_options   = isset( $bracket_options->{$group->id} ) ? $bracket_options->{$group->id} : (object) [];
	$default_group_options = [
		'sorting'    => 0,
		'textTop'    => '',
		'textBottom' => '',
		'teamA'      => '',
		'teamB'      => '',
		'free_mode'  => '',
	];

	$group_options = wp_parse_args( $saved_group_options, $default_group_options );

	$bracket_data[ $group->round ]['groups'][ $group->id ] = array_merge(
		[
			'group_id' => $group->id,
			'games'    => ! empty( $group_matches[ $group->id ] ) && is_array( $group_matches[ $group->id ] ) ? $group_matches[ $group->id ] : [],
			'title'    => $group->title,
			'round'    => $group->round,
			'teams'    => $group->clubs,
			'order'    => $group_index,
		],
		$group_options
	);
}

$wrapper_id = 'anwp-bracket-id-' . $competition_id;
?>
<a class="anwp-text-xs anwp-fl-modal-full-open" href="#" data-target="#<?php echo esc_attr( $wrapper_id ); ?>">
	<?php echo esc_html__( 'show in full screen', 'anwp-football-leagues-premium' ); ?>
</a>

<div class="anwp-b-wrap" id="<?php echo esc_attr( $wrapper_id ); ?>">
	<div class="anwp-responsive-table">
		<div class="d-flex bracket bracket--manual">
			<?php foreach ( $bracket_data as $round ) : ?>
				<div class="d-flex flex-column bracket__column anwp-flex-1">
					<div class="bracket__column-title anwp-bg-light anwp-text-center py-1 my-2 anwp-text-base">
						<?php echo esc_html( $round['title'] ); ?>
					</div>
					<?php
					$round_groups  = wp_list_sort( $round['groups'], [ 'sorting' => 'ASC', 'order' => 'ASC' ], true ); // Sorting - DEPRECATED - old saved
					$index_counter = 0;

					foreach ( $round_groups as $tie ) :

						if ( ! ( $index_counter % 2 ) ) {
							echo '<div class="bracket__block flex-fill d-flex flex-column justify-content-around">';
						}

						// Prepare club data
						$team_0 = (object) [
							'logo'  => empty( $tie['teams'][0] ) ? '' : anwp_football_leagues()->club->get_club_logo_by_id( $tie['teams'][0] ),
							'abbr'  => empty( $tie['teams'][0] ) ? '' : anwp_football_leagues()->club->get_club_abbr_by_id( $tie['teams'][0] ),
							'title' => empty( $tie['teams'][0] ) ? '' : anwp_football_leagues()->club->get_club_title_by_id( $tie['teams'][0] ),
						];

						$team_1 = (object) [
							'logo'  => empty( $tie['teams'][1] ) ? '' : anwp_football_leagues()->club->get_club_logo_by_id( $tie['teams'][1] ),
							'abbr'  => empty( $tie['teams'][1] ) ? '' : anwp_football_leagues()->club->get_club_abbr_by_id( $tie['teams'][1] ),
							'title' => empty( $tie['teams'][1] ) ? '' : anwp_football_leagues()->club->get_club_title_by_id( $tie['teams'][1] ),
						];
						?>
						<div class="bracket__item d-flex flex-wrap <?php echo AnWP_Football_Leagues::string_to_bool( $tie['free_mode'] ) ? 'bracket__item-free' : ''; ?>">

							<?php if ( ! empty( $tie['textTop'] ) ) : ?>
								<div class="bracket__text-top w-100"><?php echo esc_html( $tie['textTop'] ); ?></div>
							<?php endif; ?>

							<?php if ( ! empty( $tie['textBottom'] ) ) : ?>
								<div class="bracket__text-bottom w-100 d-flex align-items-center">
									<svg class="anwp-icon anwp-icon--feather anwp-icon--s12 mr-1">
										<use xlink:href="#icon-clock-alt"></use>
									</svg>
									<?php if ( anwp_football_leagues_premium()->data->is_valid_ISO8601_date( trim( $tie['textBottom'] ) ) ) : ?>
										<div data-fl-game-datetime="<?php echo esc_html( trim( $tie['textBottom'] ) ); ?>" class="d-flex">
											<div class="match__date-formatted"></div><span class="mx-1">-</span><div class="match__time-formatted"></div>
										</div>
									<?php else : ?>
										<?php echo esc_html( $tie['textBottom'] ); ?>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<div class="d-flex w-100">
								<div class="bracket__club-col mr-auto">
									<div class="bracket__club-row d-flex align-items-center mb-1">
										<?php if ( $team_0->logo ) : ?>
											<img loading="lazy" width="25" height="25" class="anwp-object-contain mr-2 anwp-w-25 anwp-h-25"
												src="<?php echo esc_url( $team_0->logo ); ?>" alt="club logo">
										<?php endif; ?>

										<div class="match-list__club d-inline-block mr-2 anwp-text-nowrap anwp-text-sm">
											<?php echo esc_html( ! empty( $tie['teams'][0] ) ? ( $team_0->abbr ? : $team_0->title ) : $tie['teamA'] ); ?>
										</div>
									</div>
									<div class="bracket__club-row d-flex align-items-center mt-1">
										<?php if ( $team_1->logo ) : ?>
											<img loading="lazy" width="25" height="25" class="anwp-object-contain mr-2 anwp-w-25 anwp-h-25"
												src="<?php echo esc_url( $team_1->logo ); ?>" alt="club logo">
										<?php endif; ?>

										<div class="match-list__club d-inline-block mr-2 anwp-text-nowrap anwp-text-sm">
											<?php echo esc_html( ! empty( $tie['teams'][1] ) ? ( $team_1->abbr ? : $team_1->title ) : $tie['teamB'] ); ?>
										</div>
									</div>
								</div>
								<?php
								$total_0 = 0;
								$total_1 = 0;

								foreach ( $tie['games'] as $game ) {

									$score_0 = '-';
									$score_1 = '-';

									$penalty = [
										'status'  => false,
										'active'  => false,
										'score_0' => '',
										'score_1' => '',
									];

									if ( '1' === $game->finished ) {

										if ( absint( $tie['teams'][0] ) === absint( $game->home_club ) ) {
											$score_0 = $game->home_goals;
											$score_1 = $game->away_goals;
										} else {
											$score_1 = $game->home_goals;
											$score_0 = $game->away_goals;
										}

										// Calc totals
										$total_0 += $score_0;
										$total_1 += $score_1;

										if ( isset( $game->extra ) && 2 === absint( $game->extra ) ) {
											if ( absint( $tie['teams'][0] ) === absint( $game->home_club ) ) {
												$penalty['score_0'] = $game->home_goals_p;
												$penalty['score_1'] = $game->away_goals_p;
											} else {
												$penalty['score_1'] = $game->home_goals_p;
												$penalty['score_0'] = $game->away_goals_p;
											}

											$penalty['status'] = true;
											$penalty['active'] = true;
										}
									}

									if ( count( $tie['games'] ) > 1 ) {
										if ( $penalty['active'] ) {
											echo '<div class="bracket__match-score d-flex flex-column justify-content-around"><div class="bracket__score-0 anwp-text-nowrap">' . esc_html( $score_0 ) . '(' . absint( $penalty['score_0'] ) . ')</div><div class="bracket__score-1 anwp-text-nowrap">' . esc_html( $score_1 ) . '(' . absint( $penalty['score_1'] ) . ')</div></div>';
											$penalty['active'] = false;
										} else {
											echo '<div class="bracket__match-score d-flex flex-column justify-content-around"><div class="bracket__score-0 anwp-text-nowrap">' . esc_html( $score_0 ) . '</div><div class="bracket__score-1 anwp-text-nowrap">' . esc_html( $score_1 ) . '</div></div>';
										}
									}
								}

								if ( count( $tie['games'] ) && ( $total_0 || $total_1 || ! empty( $penalty['status'] ) ) ) {
									if ( $penalty['active'] ) {
										echo '<div class="bracket__penalty-score d-flex flex-column justify-content-around ml-auto anwp-opacity-80 anwp-text-sm"><div class="bracket__score-final-0 anwp-text-nowrap">' . absint( $penalty['score_0'] ) . '</div><div class="bracket__score-final-1 anwp-text-nowrap">' . absint( $penalty['score_1'] ) . '</div></div>';
										echo '<div class="bracket__final-score d-flex flex-column justify-content-around ml-1"><div class="bracket__score-final-0 anwp-text-nowrap">' . esc_html( $total_0 ) . '</div><div class="bracket__score-final-1 anwp-text-nowrap">' . esc_html( $total_1 ) . '</div></div>';
									} else {
										echo '<div class="bracket__final-score d-flex flex-column justify-content-around"><div class="bracket__score-final-0 anwp-text-nowrap">' . esc_html( $total_0 ) . '</div><div class="bracket__score-final-1 anwp-text-nowrap">' . esc_html( $total_1 ) . '</div></div>';
									}
								}
								?>

							</div>
						</div>
						<?php
						if ( $index_counter % 2 || count( $round['groups'] ) < 2 ) {
							echo '</div>';
						}

						$index_counter++;
					endforeach;
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
