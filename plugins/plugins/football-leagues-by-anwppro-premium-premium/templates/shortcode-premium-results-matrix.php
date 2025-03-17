<?php
/**
 * The Template for displaying Results Matrix.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-results-matrix.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.5.3
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
		'group_id'       => 0,
		'standing_id'    => '',
	]
);

// Try to get Standing table
if ( absint( $data->standing_id ) ) {

	$standing = get_post( $data->standing_id );

	$data->competition_id = get_post_meta( $standing->ID, '_anwpfl_competition', true );
	$data->group_id       = get_post_meta( $standing->ID, '_anwpfl_competition_group', true );

	if ( empty( $standing ) || empty( $standing->ID ) ) {
		return;
	}

	$table = json_decode( get_post_meta( $standing->ID, '_anwpfl_table_main', true ) );

} else {
	/*
	|--------------------------------------------------------------------
	| Get Matrix by Competition and Group (Deprecated)
	|--------------------------------------------------------------------
	*/
	if ( empty( $data->competition_id ) || empty( $data->group_id ) ) {
		return;
	}

	// Get Standing
	$standing = anwp_football_leagues()->competition->tmpl_get_competition_standings( $data->competition_id, $data->group_id );

	if ( empty( $standing[0] ) || empty( $standing[0]->ID ) ) {
		return;
	}

	$table = json_decode( get_post_meta( $standing[0]->ID, '_anwpfl_table_main', true ) );
}

// Check data is valid
if ( null === $table ) {
	// something went wrong
	return;
}

// Get matches
$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches( $data->competition_id, false );

// Prepare rendering data
$matrix         = [];
$club_place_map = [];

foreach ( $table as $row ) {
	$matrix[ $row->place ] = [
		'club_id' => $row->club_id,
		'cells'   => [],
	];

	$club_place_map[ $row->club_id ] = $row->place;
}

// Check for symmetric type
$symmetric = 'symmetric' === AnWPFL_Premium_Options::get_value( 'matrix_results_type' );

// Populate matrix
foreach ( $matches as $match ) {
	if ( (int) $data->group_id !== (int) $match->group_id ) {
		continue;
	}

	if ( $symmetric ) {
		$matrix[ $club_place_map[ $match->home_club ] ]['cells'][ $club_place_map[ $match->away_club ] ][] = (object) [
			'finished'   => $match->finished,
			'home_goals' => $match->home_goals,
			'away_goals' => $match->away_goals,
			'side'       => 'home',
		];

		$matrix[ $club_place_map[ $match->away_club ] ]['cells'][ $club_place_map[ $match->home_club ] ][] = (object) [
			'finished'   => $match->finished,
			'home_goals' => $match->home_goals,
			'away_goals' => $match->away_goals,
			'side'       => 'away',
		];
	} else {
		$matrix[ $club_place_map[ $match->home_club ] ]['cells'][ $club_place_map[ $match->away_club ] ][] = (object) [
			'finished'   => $match->finished,
			'home_goals' => $match->home_goals,
			'away_goals' => $match->away_goals,
		];
	}
}

$qty = count( $matrix );

$round_robin_format = get_post_meta( $data->competition_id, '_anwpfl_format_robin', true );
$custom_class       = 'custom' === $round_robin_format ? 'py-0' : '';

// Create unique ID
$wrapper_id = 'anwp-matrix-id-' . $data->competition_id . '-' . $data->group_id;

// New display options - added in v0.9.2
$show_club_place = 'no' !== AnWPFL_Premium_Options::get_value( 'matrix_results_club_place' );

$horizontal_label = AnWPFL_Premium_Options::get_value( 'matrix_results_horizontal_labels' );
$vertical_label   = AnWPFL_Premium_Options::get_value( 'matrix_results_vertical_labels' );

if ( ! in_array( $horizontal_label, [ 'logo', 'logo_abbr', 'abbr' ], true ) ) {
	$horizontal_label = 'name';
}

if ( ! in_array( $vertical_label, [ 'logo', 'logo_abbr', 'abbr' ], true ) ) {
	$vertical_label = 'name';
}

// Matrix classes
$matrix_classes  = 'results-matrix anwp-text-xs';
$matrix_classes .= ' competition-id-' . absint( $data->competition_id );
$matrix_classes .= ' group-id-' . absint( $data->group_id );
$matrix_classes .= ' results-matrix__format-' . $round_robin_format;
$matrix_classes .= ' results-matrix__symmetric-' . (string) $symmetric;
?>
<div class="anwp-b-wrap" id="<?php echo esc_attr( $wrapper_id ); ?>">

	<?php if ( count( $club_place_map ) > 10 ) : ?>
		<a class="anwp-text-xs anwp-hide-full-modaal anwp-matrix-modal-link anwp-fl-modal-full-open" href="#" data-target="#<?php echo esc_attr( $wrapper_id ); ?>">
			<?php echo esc_html__( 'show in full screen', 'anwp-football-leagues-premium' ); ?>
		</a>
	<?php endif; ?>

	<div class="table-responsive <?php echo esc_attr( $matrix_classes ); ?>">
		<table class="results-matrix__table table table-bordered table-sm mb-0">
			<tbody>
			<?php
			/*
			|--------------------------------------------------------------------
			| Table Header
			|--------------------------------------------------------------------
			*/
			?>
			<tr>
				<?php if ( $show_club_place ) : ?>
					<td></td>
				<?php endif; ?>

				<td></td>
				<?php
				for ( $hh = 1; $hh <= $qty; $hh ++ ) :
					$club_hh_logo = anwp_football_leagues()->club->get_club_logo_by_id( $matrix[ $hh ]['club_id'] );

					if ( 'logo' === $vertical_label ) :
						?>
						<td class="anwp-text-center p-1">
							<?php if ( $club_hh_logo ) : ?>
								<div class="anwp-w-25 anwp-h-25 anwp-text-center mx-auto">
									<img loading="lazy" width="25" height="25" class="anwp-object-contain anwp-w-25 anwp-h-25"
										data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $matrix[ $hh ]['club_id'] ) ); ?>"
										src="<?php echo esc_url( $club_hh_logo ); ?>" alt="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $matrix[ $hh ]['club_id'] ) ); ?>">
								</div>
							<?php endif; ?>
						</td>
					<?php elseif ( 'abbr' === $vertical_label ) : ?>
						<td class="results-matrix__club--vertical">
							<div class="results-matrix__club-title--vertical mx-auto">
								<?php echo esc_html( anwp_football_leagues()->club->get_club_abbr_by_id( $matrix[ $hh ]['club_id'] ) ); ?>
							</div>
						</td>
					<?php elseif ( 'logo_abbr' === $vertical_label ) : ?>
						<td class="results-matrix__club--vertical">
							<div class="results-matrix__club-title--vertical mx-auto">
								<?php echo esc_html( anwp_football_leagues()->club->get_club_abbr_by_id( $matrix[ $hh ]['club_id'] ) ); ?>
							</div>
							<?php if ( $club_hh_logo ) : ?>
								<div class="anwp-w-25 anwp-h-25 anwp-text-center mx-auto mt-2">
									<img loading="lazy" width="25" height="25" class="anwp-object-contain anwp-w-25 anwp-h-25" src="<?php echo esc_url( $club_hh_logo ); ?>"/>
								</div>
							<?php endif; ?>
						</td>
					<?php else : ?>
						<td class="results-matrix__club--vertical">
							<div class="results-matrix__club-title--vertical mx-auto">
								<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $matrix[ $hh ]['club_id'] ) ); ?>
							</div>
						</td>
					<?php endif; ?>
				<?php endfor; ?>
			</tr>
			<?php
			/*
			|--------------------------------------------------------------------
			| Matrix rows
			|--------------------------------------------------------------------
			*/
			for ( $yy = 1; $yy <= $qty; $yy ++ ) :
				if ( empty( $matrix[ $yy ] ) ) {
					continue;
				}

				$club_logo = anwp_football_leagues()->club->get_club_logo_by_id( $matrix[ $yy ]['club_id'] );
				?>
				<tr>
					<?php if ( $show_club_place ) : ?>
						<td class="results-matrix__place <?php echo esc_attr( $custom_class ); ?>"><?php echo (int) $yy; ?></td>
					<?php endif; ?>

					<?php if ( 'logo' === $horizontal_label ) : ?>
						<td class="results-matrix__club p-1 anwp-text-center">
							<?php if ( $club_logo ) : ?>
								<div class="anwp-w-25 anwp-h-25 anwp-text-center mx-auto">
									<img loading="lazy" width="25" height="25" class="anwp-object-contain anwp-w-25 anwp-h-25"
										data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $matrix[ $yy ]['club_id'] ) ); ?>"
										src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $matrix[ $yy ]['club_id'] ) ); ?>">
								</div>
							<?php endif; ?>
						</td>
					<?php elseif ( 'abbr' === $horizontal_label ) : ?>
						<td class="results-matrix__club <?php echo esc_attr( $custom_class ); ?>"><?php echo esc_html( anwp_football_leagues()->club->get_club_abbr_by_id( $matrix[ $yy ]['club_id'] ) ); ?></td>
					<?php elseif ( 'logo_abbr' === $horizontal_label ) : ?>
						<td class="results-matrix__club anwp-text-nowrap anwp-leading-2 <?php echo esc_attr( $custom_class ); ?>">
							<?php if ( $club_logo ) : ?>
								<div class="d-inline-block anwp-w-25 anwp-h-25 anwp-text-center mr-2">
									<img loading="lazy" width="25" height="25" class="anwp-object-contain anwp-w-25 anwp-h-25" src="<?php echo esc_url( $club_logo ); ?>"/>
								</div>
							<?php endif; ?>
							<?php echo esc_html( anwp_football_leagues()->club->get_club_abbr_by_id( $matrix[ $yy ]['club_id'] ) ); ?>
						</td>
					<?php else : ?>
						<td class="results-matrix__club <?php echo esc_attr( $custom_class ); ?>"><?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $matrix[ $yy ]['club_id'] ) ); ?></td>
					<?php endif; ?>

					<?php for ( $xx = 1; $xx <= $qty; $xx ++ ) : ?>
						<?php
						$classes = '';

						// Render self cell
						if ( $xx === $yy ) :
							echo '<td class="results-matrix__cell results-matrix__cell-self"></td>';
							continue;
						endif;

						$home_score = '';
						$away_score = '';
						$match      = null;

						if ( 'custom' === $round_robin_format ) :
							?>
							<td class="p-0 align-top">
								<?php
								if ( ! empty( $matrix[ $yy ]['cells'][ $xx ] ) && is_array( $matrix[ $yy ]['cells'][ $xx ] ) ) :
									foreach ( $matrix[ $yy ]['cells'][ $xx ] as $match ) :
										if ( '1' === $match->finished ) :

											$home_score = $match->home_goals;
											$away_score = $match->away_goals;
											$classes    = 'anwp-bg-warning-light';

											if ( $home_score > $away_score ) {
												$classes = 'anwp-bg-success-light';
											} elseif ( $home_score < $away_score ) {
												$classes = 'anwp-bg-danger-light';
											}
											?>
											<div class="results-matrix__cell <?php echo esc_attr( $classes ); ?>">
												<?php echo esc_html( '' !== $home_score && '' !== $away_score ? ( $home_score . '–' . $away_score ) : '' ); ?>
											</div>
											<?php
										endif;
									endforeach;
								endif;
								?>
							</td>
						<?php elseif ( $symmetric ) : ?>
							<?php
							$extra_cell_class = ( ! empty( $matrix[ $yy ]['cells'][ $xx ] ) && 1 === count( $matrix[ $yy ]['cells'][ $xx ] ) && 'away' === $matrix[ $yy ]['cells'][ $xx ][0]->side ) ? 'align-bottom' : 'align-top';
							?>
							<td class="p-0 <?php echo esc_attr( $extra_cell_class ); ?>">
								<?php
								if ( ! empty( $matrix[ $yy ]['cells'][ $xx ] ) && is_array( $matrix[ $yy ]['cells'][ $xx ] ) ) :
									foreach ( $matrix[ $yy ]['cells'][ $xx ] as $match ) :
										if ( '1' === $match->finished && 'home' === $match->side ) :

											$home_score = $match->home_goals;
											$away_score = $match->away_goals;
											$classes    = 'anwp-bg-warning-light';

											if ( $home_score > $away_score ) {
												$classes = 'anwp-bg-success-light';
											} elseif ( $home_score < $away_score ) {
												$classes = 'anwp-bg-danger-light';
											}

											$classes .= ' results-matrix__cell-' . $match->side;
											?>
											<div class="results-matrix__cell <?php echo esc_attr( $classes ); ?>">
												<?php echo esc_html( '' !== $home_score && '' !== $away_score ? ( $home_score . '–' . $away_score ) : '' ); ?>
											</div>
											<?php
										elseif ( '0' === $match->finished && 'home' === $match->side && 'single' !== $round_robin_format ) :
											echo '<div class="results-matrix__cell">&nbsp;</div>';
										endif;
									endforeach;
									foreach ( $matrix[ $yy ]['cells'][ $xx ] as $match ) :
										if ( '1' === $match->finished && 'away' === $match->side ) :

											$home_score = $match->home_goals;
											$away_score = $match->away_goals;
											$classes    = 'anwp-bg-warning-light';

											if ( $home_score < $away_score ) {
												$classes = 'anwp-bg-success-light';
											} elseif ( $home_score > $away_score ) {
												$classes = 'anwp-bg-danger-light';
											}

											$classes .= ' results-matrix__cell-' . $match->side;
											?>
											<div class="results-matrix__cell <?php echo esc_attr( $classes ); ?>">
												<?php echo esc_html( '' !== $home_score && '' !== $away_score ? ( $away_score . '–' . $home_score ) : '' ); ?>
											</div>
											<?php
										endif;
									endforeach;
								endif;
								?>
							</td>
							<?php
							else :

								if ( ! empty( $matrix[ $yy ]['cells'][ $xx ] ) ) :
									$match = $matrix[ $yy ]['cells'][ $xx ][0];

									if ( '1' === $match->finished ) :

										$home_score = $match->home_goals;
										$away_score = $match->away_goals;
										$classes    = 'anwp-bg-warning-light';

										if ( $home_score > $away_score ) {
											$classes = 'anwp-bg-success-light';
										} elseif ( $home_score < $away_score ) {
											$classes = 'anwp-bg-danger-light';
										}
									endif;
								endif;
								?>
							<td class="results-matrix__cell <?php echo esc_attr( $classes ); ?>">
								<?php
								if ( '' !== $home_score && '' !== $away_score ) {
									echo esc_html( $home_score . '–' . $away_score );
								}
								?>
							</td>
							<?php endif; ?>
					<?php endfor; ?>
				</tr>
			<?php endfor; ?>
			</tbody>
		</table>
	</div>
</div>
