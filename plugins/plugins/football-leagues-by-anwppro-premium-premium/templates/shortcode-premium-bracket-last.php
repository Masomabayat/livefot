<?php
/**
 * DEPRECATED. Old Bracket logic.
 *
 * The Template for displaying Bracket view for competition layout.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-bracket.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.7.1
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
		'club_links'     => 1,
	]
);

if ( empty( $data->competition_id ) || empty( $data->final_stage_id ) ) {
	return;
}

// Get matches
$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches( $data->competition_id, true );

// Get competitions
$competitions = anwp_football_leagues()->competition->tmpl_get_prepared_competitions( $data->competition_id, true, $matches );

// Initial data
$bracket_span  = false;
$bracket_data  = [];
$sorting_order = [];

foreach ( $competitions as $competition ) {

	// Check competition type
	$competition_type = get_post_meta( $competition->ID, '_anwpfl_type', true );

	// Filter only proper types
	if ( 'knockout' !== $competition_type ) {
		continue;
	}

	// Start
	if ( 'last' === get_post_meta( $competition->ID, '_anwpfl_bracket', true ) && $competition->ID === (int) $data->final_stage_id ) {
		$bracket_span = true;
	}

	// Populate Bracket data with Competitions
	if ( $bracket_span ) {

		$bracket_data[] = [
			'post' => $competition,
			'type' => get_post_meta( $competition->ID, '_anwpfl_format_knockout', true ),
			'ties' => [],
		];
	}

	// Finish
	if ( 'first' === get_post_meta( $competition->ID, '_anwpfl_bracket', true ) && $bracket_span ) {
		break;
	}
}

// Bracket sorting
foreach ( $bracket_data as $index => $competition ) {

	// Get competition ties
	$ties = json_decode( get_post_meta( $competition['post']->ID, '_anwpfl_groups', true ) );

	// Check for valid data
	if ( empty( $ties ) || ! is_array( $ties ) ) {
		continue;
	}

	// Sort ties
	if ( $index && $sorting_order ) {

		$sorting_order = array_flip( $sorting_order );
		$ties_sorted   = [];

		foreach ( $ties as $tie ) {
			if ( isset( $sorting_order[ $tie->clubs[0] ] ) ) {
				$ties_sorted[ $sorting_order[ $tie->clubs[0] ] ] = $tie;
			} elseif ( isset( $sorting_order[ $tie->clubs[1] ] ) ) {
				$ties_sorted[ $sorting_order[ $tie->clubs[1] ] ] = $tie;
			}
		}

		$sorting_order = [];
	} elseif ( 0 === $index ) {
		$ties_sorted = $ties;
	}

	ksort( $ties_sorted );

	// Create sorting order for a next Competition ties
	foreach ( $ties_sorted as $tie_index => $tie ) {
		$sorting_order[] = $tie->clubs[0];
		$sorting_order[] = $tie->clubs[1];
	}

	$bracket_data[ $index ]['ties'] = array_values( $ties_sorted );
}

// Populate ties with matches
foreach ( $bracket_data as $index_competition => $competition ) {

	foreach ( $competition['ties'] as $tie_index => $tie ) {

		$tie_matches = [];

		foreach ( $matches as $match ) {
			if ( (int) $match->competition_id !== $competition['post']->ID || (int) $match->group_id !== $tie->id ) {
				continue;
			}

			$tie_matches[] = $match;
		}

		$bracket_data[ $index_competition ]['ties'][ $tie_index ]->matches = $tie_matches;
	}
}

$wrapper_id = 'anwp-bracket-id-' . $data->final_stage_id;
?>
<a class="anwp-text-xs anwp-fl-modal-full-open" href="#" data-target="#<?php echo esc_attr( $wrapper_id ); ?>">
	<?php echo esc_html__( 'show in full screen', 'anwp-football-leagues-premium' ); ?>
</a>

<div class="anwp-b-wrap" id="<?php echo esc_attr( $wrapper_id ); ?>">
	<div class="anwp-responsive-table">
		<div class="d-flex bracket">
			<?php foreach ( array_reverse( $bracket_data ) as $competition ) : ?>
				<div class="d-flex flex-fill flex-column bracket__column">
					<div class="bracket__column-title anwp-bg-light anwp-text-center py-1">
						<?php echo esc_html( get_post_meta( $competition['post']->ID, '_anwpfl_stage_title', true ) ); ?>
					</div>
					<?php
					foreach ( $competition['ties'] as $tie_index => $tie ) :
						if ( ! ( $tie_index % 2 ) ) {
							echo '<div class="bracket__block flex-fill d-flex flex-column justify-content-around">';
						}
						?>
						<div class="bracket__item">
							<div class="bracket__club-row d-flex align-items-center mb-1">
								<img loading="lazy" width="25" height="25" class="anwp-object-contain anwp-w-25 anwp-h-25"
									src="<?php echo esc_url( anwp_football_leagues()->club->get_club_logo_by_id( $tie->clubs[0] ) ); ?>" alt="club logo">

								<div class="match-list__club anwp-leading-1 d-inline-block mr-auto">
									<?php if ( AnWP_Football_Leagues::string_to_bool( $data->club_links ) ) : ?>
										<a class="club__link anwp-leading-1 anwp-link-without-effects anwp-link" href="<?php echo esc_url( get_permalink( $tie->clubs[0] ) ); ?>">
											<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $tie->clubs[0] ) ); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $tie->clubs[0] ) ); ?>
									<?php endif; ?>
								</div>

								<?php
								if ( 'single' === $competition['type'] ) :
									$score = '-';
									if ( isset( $tie->matches[0]->finished ) && '1' === $tie->matches[0]->finished ) {
										$score = ( $tie->clubs[0] === (int) $tie->matches[0]->home_club ) ? $tie->matches[0]->home_goals : $tie->matches[0]->away_goals;
									}
									?>
									<div class="bracket__final-score">
										<?php echo esc_html( $score ); ?>
									</div>
									<?php
								else :
									$score_1 = '-';
									$score_2 = '-';
									$score_f = '-';

									if ( isset( $tie->matches[0]->finished ) && '1' === $tie->matches[0]->finished ) {
										$score_1 = ( $tie->clubs[0] === (int) $tie->matches[0]->home_club ) ? $tie->matches[0]->home_goals : $tie->matches[0]->away_goals;
									}

									if ( isset( $tie->matches[1]->finished ) && '1' === $tie->matches[1]->finished ) {
										$score_2 = ( $tie->clubs[0] === (int) $tie->matches[1]->home_club ) ? $tie->matches[1]->home_goals : $tie->matches[1]->away_goals;
									}

									if ( '-' !== $score_1 && '-' !== $score_2 ) {
										$score_f = (int) $score_1 + (int) $score_2;
									}
									?>
									<div class="bracket__match-score">
										<?php echo esc_html( $score_1 ); ?>
									</div>
									<div class="bracket__match-score">
										<?php echo esc_html( $score_2 ); ?>
									</div>
									<div class="bracket__final-score">
										<?php echo esc_html( $score_f ); ?>
									</div>
								<?php endif; ?>
							</div>
							<div class="bracket__club-row d-flex align-items-center mt-1">
								<img loading="lazy" width="25" height="25" class="anwp-object-contain anwp-w-25 anwp-h-25"
									src="<?php echo esc_url( anwp_football_leagues()->club->get_club_logo_by_id( $tie->clubs[1] ) ); ?>" alt="club logo">

								<div class="match-list__club anwp-leading-1 d-inline-block mr-auto">
									<?php if ( AnWP_Football_Leagues::string_to_bool( $data->club_links ) ) : ?>
										<a class="club__link anwp-leading-1 anwp-link-without-effects anwp-link" href="<?php echo esc_url( get_permalink( $tie->clubs[1] ) ); ?>">
											<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $tie->clubs[1] ) ); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $tie->clubs[1] ) ); ?>
									<?php endif; ?>
								</div>
								<?php
								if ( 'single' === $competition['type'] ) :
									$score = '-';
									if ( isset( $tie->matches[0]->finished ) && '1' === $tie->matches[0]->finished ) {
										$score = ( $tie->clubs[1] === (int) $tie->matches[0]->home_club ) ? $tie->matches[0]->home_goals : $tie->matches[0]->away_goals;
									}
									?>
									<div class="bracket__final-score">
										<?php echo esc_html( $score ); ?>
									</div>
									<?php
								else :
									$score_1 = '-';
									$score_2 = '-';
									$score_f = '-';

									if ( isset( $tie->matches[0]->finished ) && '1' === $tie->matches[0]->finished ) {
										$score_1 = ( $tie->clubs[1] === (int) $tie->matches[0]->home_club ) ? $tie->matches[0]->home_goals : $tie->matches[0]->away_goals;
									}

									if ( isset( $tie->matches[1]->finished ) && '1' === $tie->matches[1]->finished ) {
										$score_2 = ( $tie->clubs[1] === (int) $tie->matches[1]->home_club ) ? $tie->matches[1]->home_goals : $tie->matches[1]->away_goals;
									}

									if ( '-' !== $score_1 && '-' !== $score_2 ) {
										$score_f = (int) $score_1 + (int) $score_2;
									}
									?>
									<div class="bracket__match-score">
										<?php echo esc_html( $score_1 ); ?>
									</div>
									<div class="bracket__match-score">
										<?php echo esc_html( $score_2 ); ?>
									</div>
									<div class="bracket__final-score">
										<?php echo esc_html( $score_f ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<?php
						if ( $tie_index % 2 || count( $competition['ties'] ) < 2 ) {
							echo '</div>';
						}
					endforeach;
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
