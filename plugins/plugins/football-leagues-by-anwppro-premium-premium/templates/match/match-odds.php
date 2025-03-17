<?php
/**
 * The Template for displaying Match >> Odds Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-odds.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.14.3
 *
 * @version       0.14.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'match_id'       => '',
		'competition_id' => '',
		'main_stage_id'  => '',
		'header'         => true,
	]
);

$odds_competition = absint( $data->main_stage_id ) ?: absint( $data->competition_id );
$odds_data        = get_post_meta( $odds_competition, '_anwpfl_league_odds', true );

if ( empty( $odds_data[ $data->match_id ] ) ) {
	return;
}

$odds_data = $odds_data[ $data->match_id ];

if ( empty( $odds_data['odds'] ) ) {
	return;
}
?>
<div class="anwp-section anwp-b-wrap">
	<?php
	if ( ! empty( $data->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__odds__odds', __( 'Odds', 'anwp-football-leagues-premium' ) ),
			],
			'general/header'
		);
	endif;
	?>

	<div class="odds-wrapper">

		<select class="w-100 odds__selector anwp-fl-odds-selector" data-game="<?php echo absint( $data->match_id ); ?>">
			<?php foreach ( $odds_data['odds'] as $odd_type_id => $odd_data ) : ?>
				<option value="<?php echo esc_attr( $odd_type_id ); ?>"><?php echo esc_html( $odd_data['name'] ); ?></option>
			<?php endforeach; ?>
		</select>

		<div class="mt-1 anwp-text-xs anwp-opacity-70 odds__updated-wrapper">
			<span class="odds__updated-text"><?php echo esc_html__( 'Updated', 'anwp-football-leagues-premium' ); ?>:</span>
			<span class="odds__updated-date"><?php echo esc_html( $odds_data['last_update'] ); ?></span>
		</div>

		<div class="table-responsive">
			<?php echo anwp_football_leagues_premium()->match->get_match_odds_table( $odds_data['odds'][ array_key_first( $odds_data['odds'] ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<div class="anwp-odds-loading anwp-text-center my-3 d-none d-print-none">
			<img alt="spinner" class="mx-auto" src="<?php echo esc_url( admin_url( '/images/spinner.gif' ) ); ?>">
		</div>
	</div>
</div>
