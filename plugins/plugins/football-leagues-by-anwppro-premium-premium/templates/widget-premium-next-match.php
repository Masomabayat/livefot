<?php
/**
 * The Template for displaying Next Match.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/widget-premium-next-match.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.1.0
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check for required data
if ( empty( $data->club_id ) && empty( $data->competition_id ) ) {
	return;
}

// Prevent errors with new params
$args = (object) wp_parse_args(
	$data,
	[
		'club_id'         => '',
		'competition_id'  => '',
		'season_id'       => '',
		'timer'           => '',
		'match_link_text' => '',
		'show_club_name'  => 1,
		'exclude_ids'     => '',
		'include_ids'     => '',
	]
);

$date_from = '';

if ( function_exists( 'current_datetime' ) && empty( $args->include_ids ) ) {
	$date_from = current_datetime()->format( 'Y-m-d' );
}

// Get competition matches
$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended(
	[
		'competition_id' => $args->competition_id,
		'season_id'      => $args->season_id,
		'show_secondary' => 1,
		'type'           => 'fixture',
		'filter_values'  => $args->club_id,
		'filter_by'      => 'club',
		'limit'          => 1,
		'sort_by_date'   => 'asc',
		'exclude_ids'    => $args->exclude_ids,
		'include_ids'    => $args->include_ids,
		'date_from'      => $date_from,
	]
);

if ( empty( $matches ) || empty( $matches[0]->match_id ) ) {
	return;
}

$data = (object) anwp_football_leagues()->match->prepare_match_data_to_render( $matches[0], [], 'widget', 'full' );

// Get kickoff diff
$kickoff_diff = ( date_i18n( 'U', get_date_from_gmt( $data->kickoff, 'U' ) ) - date_i18n( 'U' ) ) > 0 ? date_i18n( 'U', get_date_from_gmt( $data->kickoff, 'U' ) ) - date_i18n( 'U' ) : 0;

$show_name  = AnWP_Football_Leagues::string_to_bool( $args->show_club_name );
$referee_id = get_post_meta( $data->match_id, '_anwpfl_referee', true );
?>
<div class="anwp-b-wrap match-widget anwp-bg-light py-3 timer-type--<?php echo esc_attr( $args->timer ); ?>">

	<?php if ( $data->stadium_id ) : ?>
		<div class="match-widget__stadium anwp-text-center anwp-opacity-80 anwp-text-xs d-flex flex-wrap align-items-center justify-content-center mb-2">
			<svg class="anwp-icon anwp-icon--octi mr-1 anwp-icon--s12">
				<use xlink:href="#icon-location"></use>
			</svg>
			<?php
			// Stadium name
			echo esc_html( get_the_title( $data->stadium_id ) );

			// Stadium city
			$stadium_city = get_post_meta( $data->stadium_id, '_anwpfl_city', true );
			echo $stadium_city ? esc_html( ', ' . $stadium_city ) : '';
			?>
		</div>
	<?php endif; ?>

	<div class="match-widget__competition anwp-text-center anwp-text-sm">
		<?php echo esc_html( $data->stage_title ? ( $data->stage_title . ' - ' ) : '' ); ?>
		<?php echo esc_html( get_post( (int) $data->competition_id )->post_title ); ?>
	</div>

	<div class="match-widget__clubs d-flex my-3">
		<div class="anwp-flex-1 d-flex flex-column align-items-center anwp-text-center anwp-min-width-0 px-1">
			<?php if ( $show_name ) : ?>
				<img loading="lazy" class="match-widget__club-logo anwp-object-contain my-2 anwp-w-50 anwp-h-50" src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>">
				<div class="match-widget__club-title anwp-text-sm">
					<?php echo esc_html( $data->club_home_title ); ?>
				</div>
			<?php else : ?>
				<img loading="lazy" class="match-widget__club-logo anwp-object-contain my-2 anwp-w-50 anwp-h-50" src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>"
					data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $data->club_home_title ); ?>">
			<?php endif; ?>
		</div>
		<div class="anwp-flex-none align-self-center match-list__scores d-flex anwp-text-base anwp-opacity-80">vs</div>
		<div class="anwp-flex-1 d-flex flex-column align-items-center anwp-text-center anwp-min-width-0 px-1">
			<?php if ( $show_name ) : ?>
				<img loading="lazy" class="match-widget__club-logo anwp-object-contain my-2 anwp-w-50 anwp-h-50" src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>">
				<div class="match-widget__club-title anwp-text-sm">
					<?php echo esc_html( $data->club_away_title ); ?>
				</div>
			<?php else : ?>
				<img loading="lazy" class="match-widget__club-logo anwp-object-contain my-2 anwp-w-50 anwp-h-50" src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>"
					data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $data->club_away_title ); ?>">
			<?php endif; ?>
		</div>
	</div>

	<?php
	if ( 'flip' === $args->timer && $kickoff_diff > 0 ) :
		anwp_football_leagues()->load_partial( $data, 'match/match-countdown', 'modern' );
	elseif ( 'simple' === $args->timer && $kickoff_diff > 0 ) :
		anwp_football_leagues()->load_partial( $data, 'match/match-countdown' );
	else :
		?>
		<div class="match-card__timer match-card__timer-static anwp-text-center mt-3">
			<div class="d-inline-block py-1 px-2 anwp-bg-white anwp-text-base" data-fl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>" data-fl-date-format="v3">
				<?php
				if ( $data->kickoff && '0000-00-00 00:00:00' !== $data->kickoff ) {
					$date_format = anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M ';
					$time_format = anwp_football_leagues()->get_option_value( 'custom_match_time_format' ) ?: get_option( 'time_format' );

					echo '<span class="match__date-formatted">' . esc_html( date_i18n( $date_format, get_date_from_gmt( $data->kickoff, 'U' ) ) ) . '</span><span class="mx-1"></span>';
					echo '<span class="match__time-formatted">' . esc_html( date_i18n( $time_format, get_date_from_gmt( $data->kickoff, 'U' ) ) ) . '</span>';
				}
				?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $args->match_link_text ) : ?>
		<div class="anwp-text-center anwp-match-preview-link mt-2">
			<a href="<?php echo esc_url( get_permalink( (int) $data->match_id ) ); ?>" class="anwp-link-without-effects match-widget__link-preview">
				<span class="d-inline-block"><?php echo esc_html( $args->match_link_text ); ?></span>
			</a>
		</div>
	<?php endif; ?>

	<?php if ( $referee_id ) : ?>
		<div class="anwp-text-center mt-2 anwp-text-sm match-widget__referee">
			<?php echo esc_html( AnWPFL_Text::get_value( 'match__referees__referee', __( 'Referee', 'anwp-football-leagues' ) ) ); ?>:
			<b><?php echo esc_html( get_the_title( $referee_id ) ); ?></b>
		</div>
	<?php endif; ?>
</div>
