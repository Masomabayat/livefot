<?php
/**
 * The Template for displaying Missing Matches for Players.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-missing-players.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.11.3
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'show_date'         => '',
		'competition_id'    => '',
		'competition_title' => false,
		'club_id'           => '',
		'season_id'         => '',
		'season_grouped'    => 1,
		'all_players'       => 0,
		'finished_only'     => 1,
		'cache'             => 'v2',
		'sections'          => '', // cards, other, suspended, injured
	]
);

if ( 'competition' === $args->club_id && absint( $args->competition_id ) ) {
	$clubs = anwp_football_leagues()->competition->get_competition_multistage_clubs( $args->competition_id );

	/*
	|--------------------------------------------------------------------
	| Try to get from cache
	|--------------------------------------------------------------------
	*/
	$cache_key = 'FL-PRO-SHORTCODE_missing-players__' . md5( maybe_serialize( $args ) );

	if ( class_exists( 'AnWPFL_Cache' ) && anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) ) {
		echo anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {

		ob_start();

		if ( ! empty( $clubs ) && is_array( $clubs ) ) {
			foreach ( $clubs as $club_index => $club_id ) {

				$recursive_data = [
					'competition_id'    => $args->competition_id,
					'club_id'           => $club_id,
					'season_id'         => '',
					'competition_title' => false,
					'all_players'       => $args->all_players,
					'show_date'         => $args->show_date,
					'finished_only'     => $args->finished_only,
					'sections'          => $args->sections, // cards, other, suspended, injured
				];

				$shortcode_output = anwp_football_leagues()->template->shortcode_loader( 'premium-missing-players', (array) $recursive_data );

				if ( ! empty( $shortcode_output ) ) {

					$club_logo = anwp_football_leagues()->club->get_club_logo_by_id( $club_id, false );

					if ( empty( $club_logo ) ) {
						$club_logo = anwp_football_leagues()->helper->get_default_club_logo();
					}

					$club_title = anwp_football_leagues()->club->get_club_title_by_id( $club_id );
					?>
					<div class="anwp-b-wrap">
						<div class="p-2 d-flex align-items-center anwp-bg-light mb-2 <?php echo esc_attr( $club_index ? 'mt-4' : '' ); ?>">
							<img loading="lazy" width="25" height="25" class="anwp-object-contain anwp-w-25 anwp-h-25"
								data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
								src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
							<div class="anwp-text-base mx-2"><?php echo esc_html( $club_title ); ?></div>
						</div>
					</div>
					<?php
					echo $shortcode_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}

		$shortcode_output_all = ob_get_clean();

		if ( ! empty( $shortcode_output_all ) && class_exists( 'AnWPFL_Cache' ) ) {
			anwp_football_leagues()->cache->set( $cache_key, $shortcode_output_all, 'anwp_match' );
		}

		echo $shortcode_output_all; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	return;
}

if ( ! absint( $args->club_id ) || ( ! absint( $args->competition_id ) && ! absint( $args->season_id ) ) ) {
	return;
}

if ( absint( $args->season_id ) && ! AnWP_Football_Leagues::string_to_bool( $args->season_grouped ) ) {
	$competitions = anwp_football_leagues_premium()->competition->get_club_competitions_by_season_id( $args->club_id, $args->season_id );

	foreach ( $competitions as $competition_id ) {

		$recursive_data = [
			'competition_id'    => $competition_id,
			'club_id'           => $args->club_id,
			'season_id'         => '',
			'competition_title' => true,
			'all_players'       => $args->all_players,
			'show_date'         => $args->show_date,
			'finished_only'     => $args->finished_only,
			'sections'          => $args->sections, // cards, other, suspended, injured
		];

		$shortcode_output = anwp_football_leagues()->template->shortcode_loader( 'premium-missing-players', (array) $recursive_data );

		if ( ! empty( $shortcode_output ) ) {
			echo $shortcode_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	return;
}

// Date Format
$date_format = 1 === absint( $args->show_date ) ? 'j M' : $args->show_date;

/*
|--------------------------------------------------------------------
| Get shortcode data
|--------------------------------------------------------------------
*/
$data = anwp_football_leagues_premium()->player->get_players_missing_matches( $args );

if ( empty( $data ) || empty( $data['players'] ) || empty( $data['data'] ) || empty( $data['matches'] ) ) {
	return;
}

$wrapper_id = 'anwp-missing-shortcode-' . ( absint( $args->season_id ) ? : absint( $args->competition_id ) ) . '-' . absint( $args->club_id );
?>
<div class="anwp-d-wrap">
	<div class="d-flex align-items-center flex-wrap my-1">

		<?php if ( $args->competition_title && absint( $args->competition_id ) ) : ?>
			<div class="mr-4"><?php echo esc_html( get_post( $args->competition_id )->post_title ); ?></div>
		<?php endif; ?>

		<a class="anwp-text-xs anwp-fl-modal-full-open" href="#" data-target="<?php echo esc_attr( $wrapper_id ); ?>">
			<?php echo esc_html__( 'show in full screen', 'anwp-football-leagues-premium' ); ?>
		</a>
	</div>
</div>

<div class="anwp-b-wrap anwp-fl-missing-shortcode" id="<?php echo esc_attr( $wrapper_id ); ?>">
	<div class="table-responsive anwpfl-not-ready">
		<table class="table table-bordered table-sm anwp-text-center anwp-text-xs w-100 table-striped">
			<thead>
			<tr>
				<th class="text-left" title="<?php echo esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__player', __( 'Player', 'anwp-football-leagues' ) ) ); ?>">
					<?php echo esc_html( AnWPFL_Text::get_value( 'stats_players__shortcode__player', __( 'Player', 'anwp-football-leagues' ) ) ); ?>
				</th>
				<?php
				foreach ( $data['matches'] as $match ) :

					$opponent_id   = absint( $match->home_club ) === absint( $args->club_id ) ? $match->away_club : $match->home_club;
					$opponent_logo = anwp_football_leagues()->club->get_club_logo_by_id( $opponent_id );

					if ( empty( $opponent_logo ) ) {
						// Get photo from plugin options
						$opponent_logo = anwp_football_leagues_premium()->customizer->get_value( 'club', 'default_club_logo' );

						if ( ! $opponent_logo ) {
							$opponent_logo = AnWP_Football_Leagues::url( 'public/img/empty_logo.png' );
						}
					}
					?>
					<td class="anwp-text-center">

						<div data-anwp-fl-match-tooltip data-match-id="<?php echo absint( $match->match_id ); ?>"
							class="align-middle anwp-fl-missing-shortcode-match-tooltip anwp-w-30 anwp-h-30 mx-auto">
							<img loading="lazy" width="30" height="30" class="anwp-object-contain anwp-w-30 anwp-h-30" src="<?php echo esc_url( $opponent_logo ); ?>" alt="club logo">
						</div>
						<?php if ( ! empty( $args->show_date ) ) : ?>
							<span class="anwp-text-nowrap mt-1 anwp-leading-1 d-inline-block"><?php echo esc_html( date_i18n( $date_format, get_date_from_gmt( $match->kickoff, 'U' ) ) ); ?></span>
						<?php endif; ?>
					</td>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $data['players'] as $player_id => $player_name ) : ?>
				<tr>
					<td class="anwp-text-nowrap text-left"><?php echo esc_html( $player_name ? $player_name : 'ID: ' . $player_id ); ?></td>
					<?php
					foreach ( $data['matches'] as $match ) :

						$icons = '';

						if ( isset( $data['data'][ $match->match_id ] ) && isset( $data['data'][ $match->match_id ][ $player_id ] ) ) {
							$player_data = $data['data'][ $match->match_id ][ $player_id ];

							$player_data = wp_parse_args(
								$player_data,
								[
									'card_y'  => '',
									'card_yr' => '',
									'card_r'  => '',
									'reason'  => '',
									'comment' => '',
								]
							);

							if ( absint( $player_data['card_yr'] ) ) {
								$icons .= '<svg class="icon__card m-0"><use xlink:href="#icon-card_yr"></use></svg>';
							} elseif ( absint( $player_data['card_y'] ) ) {
								$icons .= '<svg class="icon__card m-0"><use xlink:href="#icon-card_y"></use></svg>';
							}

							if ( absint( $player_data['card_r'] ) ) {
								$icons .= '<svg class="icon__card m-0"><use xlink:href="#icon-card_r"></use></svg>';
							}

							if ( trim( $player_data['reason'] ) ) {
								switch ( $player_data['reason'] ) {

									case 'suspended':
										$icons .= '<svg ' . ( $player_data['comment'] ? 'data-toggle="anwp-tooltip" data-tippy-content="' . esc_attr( $player_data['comment'] ) . '"' : '' ) . ' class="anwp-icon anwp-icon--octi anwp-icon--s20"><use xlink:href="#icon-x-circle"></use></svg>';
										break;

									case 'injured':
										$icons .= '<svg ' . ( $player_data['comment'] ? 'data-toggle="anwp-tooltip" data-tippy-content="' . esc_attr( $player_data['comment'] ) . '"' : '' ) . ' class="anwp-icon anwp-icon--octi anwp-icon--s20"><use xlink:href="#icon-plus-circle"></use></svg>';
										break;

									default:
										$icons .= '<svg ' . ( $player_data['comment'] ? 'data-toggle="anwp-tooltip" data-tippy-content="' . esc_attr( $player_data['comment'] ) . '"' : '' ) . ' class="anwp-icon anwp-icon--octi anwp-icon--s20"><use xlink:href="#icon-question"></use></svg>';
								}
							}
						}
						?>
						<td class="anwp-text-nowrap anwp-text-center">
							<?php echo $icons; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
