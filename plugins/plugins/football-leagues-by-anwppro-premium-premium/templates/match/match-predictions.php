<?php
/**
 * The Template for displaying Match >> Predictions Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-predictions.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.13.4
 *
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$data,
	[
		'home_club'       => '',
		'away_club'       => '',
		'club_home_title' => '',
		'club_away_title' => '',
		'club_home_link'  => '',
		'club_away_link'  => '',
		'club_home_logo'  => '',
		'club_away_logo'  => '',
		'match_id'        => '',
		'finished'        => '',
		'header'          => true,
	]
);

// Show prediction only for upcoming games
if ( ! absint( $data['match_id'] ) || 'advice_comparison' !== AnWPFL_Premium_API::get_config_value( 'predictions_data' ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Get Prediction Data
|--------------------------------------------------------------------
*/
$predictions = anwp_football_leagues_premium()->match->get_prediction_data( $data['match_id'] );

if ( empty( $predictions['prediction_advice'] ) && empty( $predictions['prediction_percent'] ) ) {
	return;
}

$predictions['prediction_advice']     = anwp_football_leagues_premium()->match->translate_prediction_advice( $predictions['prediction_advice'] );
$predictions['prediction_percent']    = json_decode( $predictions['prediction_percent'], true ) ?: [];
$predictions['prediction_comparison'] = json_decode( $predictions['prediction_comparison'], true ) ?: [];

/*
|--------------------------------------------------------------------
| Get club colors
|--------------------------------------------------------------------
*/
$color_home = get_post_meta( $data['home_club'], '_anwpfl_main_color', true );
$color_away = get_post_meta( $data['away_club'], '_anwpfl_main_color', true );

if ( empty( $color_home ) ) {
	$color_home = '#0085ba';
}

if ( empty( $color_away ) ) {
	$color_away = '#dc3545';
}
?>
<div class="anwp-section anwp-b-wrap">

	<?php
	if ( ! empty( $data['header'] ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__prediction__match_prediction', __( 'Match Prediction', 'anwp-football-leagues-premium' ) ),
			],
			'general/header'
		);
	endif;
	?>

	<div class="d-sm-flex">
		<div class="anwp-flex-1 pr-2">
			<?php
			anwp_football_leagues()->load_partial(
				[
					'club_id' => $data['home_club'],
					'class'   => 'my-2 mr-sm-1',
				],
				'club/club-title'
			);
			?>
		</div>
		<div class="anwp-flex-1 pl-2">
			<?php
			anwp_football_leagues()->load_partial(
				[
					'club_id' => $data['away_club'],
					'class'   => 'my-2 ml-sm-1',
					'is_home' => false,
				],
				'club/club-title'
			);
			?>
		</div>
	</div>

	<?php if ( ! empty( $predictions['prediction_advice'] ) ) : ?>
		<div class="anwp-fl-prediction-advice mt-3 py-2 px-3 anwp-text-center anwp-bg-light">
			<div class="anwp-fl-prediction-advice__header anwp-text-sm d-flex align-items-center justify-content-center anwp-opacity-80 mb-2">
				<svg class="anwp-icon mr-1 anwp-icon--octi anwp-fill-current">
					<use xlink:href="#icon-light-bulb"></use>
				</svg>
				<?php echo esc_html( AnWPFL_Text::get_value( 'match__prediction__prediction', __( 'Prediction', 'anwp-football-leagues-premium' ) ) ); ?>
			</div>
			<div class="anwp-fl-prediction-advice__text anwp-text-base"><?php echo esc_html( $predictions['prediction_advice'] ); ?></div>
		</div>
	<?php endif; ?>

	<?php
	if ( ! empty( $predictions['prediction_percent'] ) ) :
		$predictions['prediction_percent'] = wp_parse_args(
			$predictions['prediction_percent'],
			[
				'home' => '',
				'draw' => '',
				'away' => '',
			]
		);

		if ( $predictions['prediction_percent']['home'] || $predictions['prediction_percent']['away'] ) :
			?>
			<div class="anwp-fl-prediction-percent mt-4">
				<div class="anwp-text-center anwp-text-2xl"><?php echo esc_html( AnWPFL_Text::get_value( 'match__prediction__who_will_win', __( 'Who Will Win', 'anwp-football-leagues-premium' ) ) ); ?></div>

				<div class="d-flex anwp-text-center my-3">
					<div class="anwp-flex-1 anwp-text-3xl" style="color: <?php echo esc_attr( $color_home ); ?>;">
						<?php echo intval( $predictions['prediction_percent']['home'] ); ?>%
					</div>
					<div class="anwp-flex-1 anwp-text-3xl">
						<?php echo intval( $predictions['prediction_percent']['draw'] ); ?>%
					</div>
					<div class="anwp-flex-1 anwp-text-3xl" style="color: <?php echo esc_attr( $color_away ); ?>;">
						<?php echo intval( $predictions['prediction_percent']['away'] ); ?>%
					</div>
				</div>

				<div class="d-flex align-items-center">
					<?php if ( intval( $predictions['prediction_percent']['home'] ) ) : ?>
						<div class="team-stats__bar" style="width: <?php echo intval( $predictions['prediction_percent']['home'] ); ?>%; background-color: <?php echo esc_attr( $color_home ); ?>"></div>
					<?php endif; ?>
					<?php if ( intval( $predictions['prediction_percent']['draw'] ) ) : ?>
						<div class="team-stats__bar" style="width: <?php echo intval( $predictions['prediction_percent']['draw'] ); ?>%; background-color: #919191;"></div>
					<?php endif; ?>
					<?php if ( intval( $predictions['prediction_percent']['away'] ) ) : ?>
						<div class="team-stats__bar" style="width: <?php echo intval( $predictions['prediction_percent']['away'] ); ?>%; background-color: <?php echo esc_attr( $color_away ); ?>"></div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="anwp-text-base anwp-text-center anwp-text-2xl mt-5 mb-1">
		<?php echo esc_html( AnWPFL_Text::get_value( 'match__prediction__team_comparison', __( 'Team comparison', 'anwp-football-leagues-premium' ) ) ); ?>
	</div>
	<?php
	/*
	|--------------------------------------------------------------------
	| Comparison Data
	|--------------------------------------------------------------------
	*/
	foreach ( $predictions['prediction_comparison'] as $comparison_slug => $comparison_column ) :

		if ( empty( $comparison_column['home'] ) || empty( $comparison_column['away'] ) ) {
			continue;
		}

		if ( empty( intval( $comparison_column['home'] ) ) && empty( intval( $comparison_column['away'] ) ) ) {
			continue;
		}

		$comparison_slug_l10n = [
			'form'                 => AnWPFL_Text::get_value( 'match__prediction__current_form', __( 'Current Form', 'anwp-football-leagues-premium' ) ),
			'att'                  => AnWPFL_Text::get_value( 'match__prediction__attacking', __( 'Attacking', 'anwp-football-leagues-premium' ) ),
			'def'                  => AnWPFL_Text::get_value( 'match__prediction__defensive', __( 'Defensive', 'anwp-football-leagues-premium' ) ),
			'poisson_distribution' => AnWPFL_Text::get_value( 'match__prediction__poisson_distribution', __( 'Poisson Distribution', 'anwp-football-leagues-premium' ) ),
			'h2h'                  => AnWPFL_Text::get_value( 'match__prediction__h2h', __( 'Head-to-Head', 'anwp-football-leagues-premium' ) ),
			'goals'                => AnWPFL_Text::get_value( 'match__prediction__goals', __( 'Goals', 'anwp-football-leagues-premium' ) ),
			'total'                => AnWPFL_Text::get_value( 'match__prediction__total', __( 'Total', 'anwp-football-leagues-premium' ) ),
		];

		?>
		<div class="anwp-fl-prediction-percent__stats team-stats p-2">
			<div class="anwp-fl-prediction-percent__text anwp-text-center team-stats__title d-flex">
				<span class="anwp-text-nowrap anwp-text-left anwp-flex-1 anwp-text-lg"><?php echo esc_html( $comparison_column['home'] ); ?></span>
				<span class="anwp-text-nowrap anwp-flex-1 anwp-text-base"><?php echo esc_html( $comparison_slug_l10n[ $comparison_slug ] ?? $comparison_slug ); ?></span>
				<span class="anwp-text-nowrap anwp-flex-1 anwp-text-right anwp-text-lg"><?php echo esc_html( $comparison_column['away'] ); ?></span>
			</div>
			<div class="d-flex align-items-center">
				<div class="team-stats__bar" style="width: <?php echo intval( $comparison_column['home'] ); ?>%; background-color: <?php echo esc_attr( $color_home ); ?>"></div>
				<div class="team-stats__bar" style="width: <?php echo intval( $comparison_column['away'] ); ?>%; background-color: <?php echo esc_attr( $color_away ); ?>"></div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
