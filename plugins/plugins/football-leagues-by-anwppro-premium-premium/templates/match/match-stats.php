<?php
/**
 * The Template for displaying Match >> Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.8.9
 *
 * @version       0.16.0
 */
// phpcs:disable WordPress.NamingConventions.ValidVariableName

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
		'stats_home_club' => '',
		'stats_away_club' => '',
		'header'          => true,
	]
);

$stats_columns = json_decode( get_option( 'anwpfl_stats_columns_match_club' ) );

if ( empty( $stats_columns ) ) {
	return;
}

$stats_home = json_decode( $data['stats_home_club'], true ) ? : [];
$stats_away = json_decode( $data['stats_away_club'], true ) ? : [];

/*
|--------------------------------------------------------------------
| Get club colors
|--------------------------------------------------------------------
*/
$data['color_home'] = get_post_meta( $data['home_club'], '_anwpfl_main_color', true ) ? : '#0085ba';
$data['color_away'] = get_post_meta( $data['away_club'], '_anwpfl_main_color', true ) ? : '#dc3545';

/**
 * Hook: anwpfl/tmpl-match/stats_before
 *
 * @param object $data Match data
 *
 * @since 0.7.5
 */
do_action( 'anwpfl/tmpl-match/stats_before', $data );

/*
|--------------------------------------------------------------------
| Statistical Data
|--------------------------------------------------------------------
*/
ob_start();

/*
|--------------------------------------------------------------------
| Shots Statistical Widget
| @since 0.11.10
|--------------------------------------------------------------------
*/
if ( 'hide' !== AnWPFL_Premium_Options::get_value( 'match_stats_widget_shots' ) ) {
	anwp_football_leagues()->load_partial( $data, 'match/match-widget-shots' );
}

/*
|--------------------------------------------------------------------
| Statistical Data
|--------------------------------------------------------------------
*/
foreach ( $stats_columns as $stats_column ) :

	$stats_column = (object) wp_parse_args(
		$stats_column,
		[
			'visibility' => '',
			'type'       => '',
			'id'         => '',
			'prefix'     => '',
			'postfix'    => '',
			'max'        => '',
		]
	);

	if ( empty( $stats_column->id ) || empty( $stats_column->type ) ) {
		continue;
	}

	if ( 'hidden' === $stats_column->visibility ) {
		continue;
	}

	// Check default value not set
	if ( 'default' === $stats_column->type ) {
		$h_param = 'home_' . anwp_football_leagues_premium()->stats->get_new_game_team_stat_slug( $stats_column->field_slug );
		$a_param = 'away_' . anwp_football_leagues_premium()->stats->get_new_game_team_stat_slug( $stats_column->field_slug );

		if ( ! isset( $data[ $h_param ] ) && ! isset( $data[ $a_param ] ) ) {
			continue;
		}
	} elseif ( in_array( $stats_column->type, [ 'simple', 'time' ], true ) ) {
		if ( ! isset( $stats_home[ $stats_column->id ] ) && ! isset( $stats_away[ $stats_column->id ] ) ) {
			continue;
		}
	} elseif ( 'calculated' === $stats_column->type ) {

		$calc_field_1 = anwp_football_leagues_premium()->stats->get_club_match_stat_column_by_id( $stats_column->field_1 );

		if ( 'simple' === $calc_field_1->type && ( ! isset( $stats_home[ $calc_field_1->id ] ) || ! isset( $stats_away[ $calc_field_1->id ] ) ) ) {
			continue;
		}

		$calc_field_2 = anwp_football_leagues_premium()->stats->get_club_match_stat_column_by_id( $stats_column->field_2 );

		if ( 'simple' === $calc_field_2->type && ( ! isset( $stats_home[ $calc_field_2->id ] ) || ! isset( $stats_away[ $calc_field_2->id ] ) ) ) {
			continue;
		}
	}

	// Values
	$value_home = anwp_football_leagues_premium()->stats->get_club_match_stat_value( $data, $stats_home, $stats_column, 'home_' );
	$value_away = anwp_football_leagues_premium()->stats->get_club_match_stat_value( $data, $stats_away, $stats_column, 'away_' );

	if ( empty( $value_home ) && empty( $value_away ) ) {
		continue;
	}

	$value_total = empty( $stats_column->max ) ? '' : floatval( $stats_column->max );

	if ( 'time' === $stats_column->type ) {

		if ( ':' !== mb_substr( $value_home, - 3, 1 ) ) {
			$value_home = '0:00';
		}

		$time_home_arr   = explode( ':', $value_home, 2 );
		$value_home_time = absint( $time_home_arr[0] ) * 60 + absint( $time_home_arr[1] );

		if ( ':' !== mb_substr( $value_away, - 3, 1 ) ) {
			$value_away = '0:00';
		}

		$time_away_arr   = explode( ':', $value_away, 2 );
		$value_away_time = absint( $time_away_arr[0] ) * 60 + absint( $time_away_arr[1] );

		if ( ! $value_total ) {
			$value_total = $value_home_time + $value_away_time;
		}

		$width_home = $value_total ? intval( $value_home_time / $value_total * 100 ) : 0;
		$width_away = $value_total ? intval( $value_away_time / $value_total * 100 ) : 0;
	} else {

		if ( ! $value_total ) {
			$value_total = floatval( $value_home ) + floatval( $value_away );
		}

		$width_home = $value_total ? intval( floatval( $value_home ) / floatval( $value_total ) * 100 ) : 0;
		$width_away = $value_total ? intval( floatval( $value_away ) / floatval( $value_total ) * 100 ) : 0;
	}
	?>
	<div class="team-stats match-stats__stat-wrapper anwp-fl-border-bottom  anwp-border-light p-2 club-stats__<?php echo esc_attr( $stats_column->field_slug ); ?>">
		<div class="anwp-text-center anwp-text-base team-stats__title d-flex">
			<span class="anwp-text-nowrap anwp-text-monospace team-stats__value anwp-text-left anwp-flex-1"><?php echo esc_html( $stats_column->prefix . $value_home . $stats_column->postfix ); ?></span>
			<span class="anwp-text-nowrap anwp-flex-none anwp-text-sm"><?php echo esc_html( $stats_column->name ); ?></span>
			<span class="anwp-text-nowrap anwp-text-monospace team-stats__value anwp-flex-1 anwp-text-right"><?php echo esc_html( $stats_column->prefix . $value_away . $stats_column->postfix ); ?></span>
		</div>
		<div class="d-flex">
			<div class="anwp-flex-1 mx-1">
				<div class="match-stats__stat-bar d-flex anwp-overflow-hidden anwp-h-20 flex-row-reverse">
					<div class="match-stats__stat-bar-inner" style="width: <?php echo intval( $width_home ); ?>%; background-color: <?php echo esc_attr( $data['color_home'] ); ?>" role="progressbar"></div>
				</div>
			</div>
			<div class="anwp-flex-1 mx-1">
				<div class="match-stats__stat-bar d-flex anwp-overflow-hidden anwp-h-20">
					<div class="match-stats__stat-bar-inner" style="width: <?php echo intval( $width_away ); ?>%; background-color: <?php echo esc_attr( $data['color_away'] ); ?>" role="progressbar"></div>
				</div>
			</div>
		</div>
	</div>
	<?php
endforeach;

$stats_html = ob_get_clean();

if ( empty( $stats_html ) ) {
	return;
}
?>
<div class="anwp-section">

	<?php
	if ( ! empty( $data['header'] ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__stats__match_statistics', __( 'Match Statistics', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	endif;
	?>

	<div class="anwp-row anwp-no-gutters">
		<div class="anwp-col-sm">
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
		<div class="anwp-col-sm">
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

	<?php echo $stats_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

</div>
