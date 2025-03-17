<?php
/**
 * The Template for displaying Bracket view for competition layout.
 * Automatic ordering and sorting.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-bracket-auto.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP-Football-Leagues-Premium/Templates
 * @since            0.7.1
 *
 * @version          0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'competition_id' => 0,
		'final_stage_id' => 0,
		'club_links'     => 1,
	]
);

$competition_id = $data->final_stage_id;

if ( empty( $competition_id ) || 'knockout' !== get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Group Matches
|--------------------------------------------------------------------
*/
$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches( $competition_id, false );

if ( empty( $matches ) ) {
	return;
}

$group_matches = [];
foreach ( $matches as $match ) {
	$group_matches[ $match->group_id ][] = $match;
}

/*
|--------------------------------------------------------------------
| Rounds and Groups
|--------------------------------------------------------------------
*/
$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );
$rounds = json_decode( get_post_meta( $competition_id, '_anwpfl_rounds', true ) );

if ( empty( $rounds ) || ! is_array( $rounds ) || empty( $groups ) || ! is_array( $groups ) ) {
	return;
}

$rounds = wp_list_sort( $rounds, 'id', 'DESC' );

foreach ( $rounds as $round ) {
	$round->groups = [];

	foreach ( $groups as $group ) {
		if ( intval( $group->round ) === intval( $round->id ) ) {
			$round->groups[ $group->id ] = $group;
		}
	}
}

// Initial data
$sorting_order = [];

// Bracket sorting
foreach ( $rounds as $round_index => $round ) {

	// Check for valid data
	if ( empty( $round->groups ) ) {
		continue;
	}

	// Sort ties
	if ( $round_index && $sorting_order ) {

		$sorting_order = array_flip( $sorting_order );
		$groups_sorted = [];

		foreach ( $round->groups as $group ) {
			if ( isset( $sorting_order[ $group->clubs[0] ] ) ) {
				$groups_sorted[ $sorting_order[ $group->clubs[0] ] ] = $group;
			} elseif ( isset( $sorting_order[ $group->clubs[1] ] ) ) {
				$groups_sorted[ $sorting_order[ $group->clubs[1] ] ] = $group;
			}
		}

		$sorting_order = [];
	} elseif ( 0 === $round_index ) {
		$groups_sorted = $round->groups;
	}

	ksort( $groups_sorted );

	// Create sorting order for a next Competition ties
	foreach ( $groups_sorted as $group_index => $group ) {
		$sorting_order[] = $group->clubs[0];
		$sorting_order[] = $group->clubs[1];
	}

	$rounds[ $round_index ]->groups = array_values( $groups_sorted );
}

$wrapper_id = 'anwp-bracket-id-' . $competition_id;
?>
<a class="anwp-text-xs anwp-fl-modal-full-open" href="#" data-target="#<?php echo esc_attr( $wrapper_id ); ?>">
	<?php echo esc_html__( 'show in full screen', 'anwp-football-leagues-premium' ); ?>
</a>

<div class="anwp-b-wrap" id="<?php echo esc_attr( $wrapper_id ); ?>">
	<div class="anwp-responsive-table">
		<div class="d-flex bracket">
			<?php foreach ( array_reverse( $rounds ) as $round ) : ?>
				<div class="d-flex anwp-flex-1 flex-column bracket__column">
					<div class="bracket__column-title anwp-bg-light anwp-text-center py-1 anwp-text-base">
						<?php echo esc_html( $round->title ); ?>
					</div>
					<?php
					foreach ( $round->groups as $group_index => $tie ) :

						$tie->matches = empty( $group_matches[ $tie->id ] ) ? [] : $group_matches[ $tie->id ];

						if ( ! ( $group_index % 2 ) ) {
							echo '<div class="bracket__block flex-fill d-flex flex-column justify-content-around">';
						}
						?>
						<div class="bracket__item">
							<div class="bracket__club-row d-flex align-items-center mb-1">
								<img loading="lazy" width="25" height="25" class="anwp-object-contain mr-2 anwp-w-25 anwp-h-25"
									src="<?php echo esc_url( anwp_football_leagues()->club->get_club_logo_by_id( $tie->clubs[0] ) ); ?>" alt="club logo">

								<div class="match-list__club anwp-leading-1 d-inline-block mr-auto">
									<?php if ( AnWP_Football_Leagues::string_to_bool( $data->club_links ) ) : ?>
										<a class="club__link anwp-leading-1 anwp-link-without-effects anwp-link anwp-text-sm" href="<?php echo esc_url( get_permalink( $tie->clubs[0] ) ); ?>">
											<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $tie->clubs[0] ) ); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $tie->clubs[0] ) ); ?>
									<?php endif; ?>
								</div>

								<?php
								if ( count( $tie->matches ) < 2 ) :
									$score = '-';
									if ( isset( $tie->matches[0]->finished ) && '1' === $tie->matches[0]->finished ) {
										$score = ( $tie->clubs[0] === (int) $tie->matches[0]->home_club ) ? $tie->matches[0]->home_goals : $tie->matches[0]->away_goals;

										if ( isset( $tie->matches[0]->extra ) && 2 === absint( $tie->matches[0]->extra ) ) {
											$score .= ' (' . ( ( $tie->clubs[0] === (int) $tie->matches[0]->home_club ) ? $tie->matches[0]->home_goals_p : $tie->matches[0]->away_goals_p ) . ')';
										}
									}
									?>
									<div class="bracket__final-score">
										<?php echo esc_html( $score ); ?>
									</div>
									<?php
								else :

									$finished = [];
									$upcoming = [];

									$total = false;

									foreach ( $tie->matches as $match ) {
										if ( '1' === $match->finished ) {
											$score = ( $tie->clubs[0] === (int) $match->home_club ) ? $match->home_goals : $match->away_goals;

											// Calc totals
											$total += $score;

											if ( isset( $match->extra ) && 2 === absint( $match->extra ) ) {
												$score .= ' (' . ( ( $tie->clubs[0] === (int) $match->home_club ) ? $match->home_goals_p : $match->away_goals_p ) . ')';
											}

											$finished[] = $score;
										} else {
											$upcoming[] = '-';
										}
									}

									foreach ( array_merge( $finished, $upcoming ) as $sc ) {
										echo '<div class="bracket__match-score">' . esc_html( $sc ) . '</div>';
									}

									if ( false !== $total ) {
										echo '<div class="bracket__final-score">' . esc_html( $total ) . '</div>';
									}

								endif;
								?>
							</div>
							<div class="bracket__club-row d-flex align-items-center mt-1">
								<img loading="lazy" width="25" height="25" class="anwp-object-contain mr-2 anwp-w-25 anwp-h-25"
									src="<?php echo esc_url( anwp_football_leagues()->club->get_club_logo_by_id( $tie->clubs[1] ) ); ?>" alt="club logo">

								<div class="match-list__club anwp-leading-1 d-inline-block mr-auto">
									<?php if ( AnWP_Football_Leagues::string_to_bool( $data->club_links ) ) : ?>
										<a class="club__link anwp-leading-1 anwp-link-without-effects anwp-link anwp-text-sm" href="<?php echo esc_url( get_permalink( $tie->clubs[1] ) ); ?>">
											<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $tie->clubs[1] ) ); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $tie->clubs[1] ) ); ?>
									<?php endif; ?>
								</div>
								<?php
								if ( count( $tie->matches ) < 2 ) :
									$score = '-';
									if ( isset( $tie->matches[0]->finished ) && '1' === $tie->matches[0]->finished ) {
										$score = ( $tie->clubs[1] === (int) $tie->matches[0]->home_club ) ? $tie->matches[0]->home_goals : $tie->matches[0]->away_goals;

										if ( isset( $tie->matches[0]->extra ) && 2 === absint( $tie->matches[0]->extra ) ) {
											$score .= ' (' . ( ( $tie->clubs[1] === (int) $tie->matches[0]->home_club ) ? $tie->matches[0]->home_goals_p : $tie->matches[0]->away_goals_p ) . ')';
										}
									}
									?>
									<div class="bracket__final-score">
										<?php echo esc_html( $score ); ?>
									</div>
									<?php
								else :

									$finished = [];
									$upcoming = [];

									$total = false;

									foreach ( $tie->matches as $match ) {
										if ( '1' === $match->finished ) {
											$score = ( $tie->clubs[1] === (int) $match->home_club ) ? $match->home_goals : $match->away_goals;

											// Calc totals
											$total += $score;

											if ( isset( $match->extra ) && 2 === absint( $match->extra ) ) {
												$score .= ' (' . ( ( $tie->clubs[1] === (int) $match->home_club ) ? $match->home_goals_p : $match->away_goals_p ) . ')';
											}

											$finished[] = $score;

										} else {
											$upcoming[] = '-';
										}
									}

									foreach ( array_merge( $finished, $upcoming ) as $sc ) {
										echo '<div class="bracket__match-score">' . esc_html( $sc ) . '</div>';
									}

									if ( false !== $total ) {
										echo '<div class="bracket__final-score">' . esc_html( $total ) . '</div>';
									}
								endif;
								?>
							</div>
						</div>
						<?php
						if ( $group_index % 2 || count( $round->groups ) < 2 ) {
							echo '</div>';
						}
					endforeach;
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
