<?php
/**
 * The Template for displaying Match >> Formation Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-formation.php.
 *
 * // phpcs:disable WordPress.NamingConventions.ValidVariableName
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.1.0
 *
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$data,
	[
		'club_links'      => 'yes',
		'home_club'       => '',
		'away_club'       => '',
		'club_home_title' => '',
		'club_away_title' => '',
		'club_home_logo'  => '',
		'club_away_logo'  => '',
		'match_id'        => '',
		'custom_numbers'  => [],
		'events'          => [],
	]
);

// Check Match ID is set
if ( empty( $data['match_id'] ) ) {
	return;
}

$formation_data = anwp_football_leagues_premium()->match->get_formation_data( $data['match_id'] );

if ( empty( $formation_data ) ) {
	return;
}

// Prepare formation data
$formation = json_decode( $formation_data['formation'], true );

// Check formation property is set
if ( ! $formation ) {
	return;
}

// Check players have been set
if ( empty( array_filter( array_values( $formation['home'] ) ) ) && empty( array_filter( array_values( $formation['away'] ) ) ) ) {
	return;
}

// Prepare squad
$home_squad = anwp_football_leagues()->club->tmpl_prepare_club_squad( $data['home_club'], $data['season_id'] );
$away_squad = anwp_football_leagues()->club->tmpl_prepare_club_squad( $data['away_club'], $data['season_id'] );

$bg_field      = AnWP_Football_Leagues_Premium::url( 'public/img/soccer_field.svg' );
$bg_field_vert = AnWP_Football_Leagues_Premium::url( 'public/img/soccer_field_vert.svg' );

/*
|--------------------------------------------------------------------
| Default Colors
|--------------------------------------------------------------------
*/
$color_home = get_post_meta( $data['home_club'], '_anwpfl_main_color', true ) ?: '#0085ba';
$color_away = get_post_meta( $data['away_club'], '_anwpfl_main_color', true ) ?: '#dc3545';

/*
|--------------------------------------------------------------------
| Extra Formation data
|--------------------------------------------------------------------
*/
$formation_extra = wp_parse_args(
	json_decode( $formation_data['formation_extra'], true ) ?: [],
	[
		'home_colors'    => '',
		'home_formation' => '',
		'away_colors'    => '',
		'away_formation' => '',
	]
);

/*
|--------------------------------------------------------------------
| Shirt & Number styles (since v0.6.0)
|--------------------------------------------------------------------
*/
$shirt_home = anwp_football_leagues_premium()->club->get_club_shirt( $data['home_club'], 'home', $data['match_id'], $formation_extra['home_colors'], $color_home, $formation_data['home_club_shirt'] );
$shirt_away = anwp_football_leagues_premium()->club->get_club_shirt( $data['away_club'], 'away', $data['match_id'], $formation_extra['away_colors'], $color_away, $formation_data['away_club_shirt'] );

$temp_players = anwp_football_leagues_premium()->match->get_temp_players( $data['match_id'] );

/*
|--------------------------------------------------------------------
| Display Options
|--------------------------------------------------------------------
*/
$show_player   = anwp_football_leagues_premium()->customizer->get_value( 'match', 'formation_show_player', 'photo_shirt' );
$default_photo = anwp_football_leagues()->helper->get_default_player_photo();
$photo_dir     = wp_upload_dir()['baseurl'];

$show_country = 'show' === anwp_football_leagues_premium()->customizer->get_value( 'match', 'formation_show_country' );
$show_events  = 'hide' !== anwp_football_leagues_premium()->customizer->get_value( 'match', 'formation_show_events' );
$events       = empty( $data['events'] ) ? [] : anwp_football_leagues()->helper->parse_match_events_lineups( $data['events'] );

/*
|--------------------------------------------------------------------
| Prepare Player Rating data
|--------------------------------------------------------------------
*/
$show_rating = 'hide' !== anwp_football_leagues_premium()->customizer->get_value( 'match', 'formation_show_rating' );

if ( $show_rating ) {
	$rating_field = AnWPFL_Premium_Options::get_value( 'player_rating' );

	if ( $rating_field ) {
		$home_players_statistics = anwp_football_leagues_premium()->stats->get_game_players_statistics( $data['match_id'], $data['home_club'] );
		$away_players_statistics = anwp_football_leagues_premium()->stats->get_game_players_statistics( $data['match_id'], $data['away_club'] );
	}
}
?>
<div class="match-formation anwpfl-not-ready position-relative">
	<div class="match-formation__field d-flex flex-column flex-md-row match-formation__show-player--<?php echo esc_attr( $show_player ); ?>">
		<div class="d-flex flex-column justify-content-around fl-formation-home">
		<?php
		foreach ( $formation['home'] as $formation_line ) :
			if ( empty( $formation_line ) ) {
				continue;
			}

			?>
			<div class="d-flex flex-row-reverse match-formation__line">
				<?php
				foreach ( $formation_line as $player_id ) :
					if ( '_' === $player_id ) {
						echo '<div class="my-1 anwp-text-center position-relative match-formation__empty anwp-flex-1">&nbsp;</div>';
						continue;
					}

					$player_number = '';

					if ( ! empty( $data['custom_numbers']->{$player_id} ) ) {
						$player_number = (int) $data['custom_numbers']->{$player_id};
					} elseif ( isset( $home_squad[ $player_id ] ) && $home_squad[ $player_id ]['number'] ) {
						$player_number = (int) $home_squad[ $player_id ]['number'];
					}

					if ( ! empty( $temp_players ) && 'temp__' === mb_substr( $player_id, 0, 6 ) && isset( $temp_players[ $player_id ] ) ) {
						$player = [
							'name'       => $temp_players[ $player_id ]->name,
							'short_name' => $temp_players[ $player_id ]->name,
							'position'   => $temp_players[ $player_id ]->position,
						];
					} elseif ( ! empty( $data['players'][ $player_id ] ?? [] ) ) {
						$player = $data['players'][ $player_id ];
					} else {
						$player = anwp_fl()->player->get_player_data( $player_id );
					}

					if ( empty( $player ) ) {
						continue;
					}

					$is_goalkeeper = 'g' === $player['position'];
					?>
					<div class="anwp-flex-1 my-1 anwp-text-center position-relative match-formation__player d-flex flex-column align-items-center justify-content-center">
						<div class="match-formation__player_shirt <?php echo esc_attr( $is_goalkeeper ? 'match-formation__player_shirt--goalkeeper' : '' ); ?>">

							<?php if ( 'photo_shirt' === $show_player || 'shirt' === $show_player ) : ?>
								<?php if ( $shirt_home['svg'] && ! $is_goalkeeper ) : ?>
									<svg class="anwp-icon anwpfl-icon--shirt">
										<use xlink:href="#anwpfl-shirt-home"></use>
									</svg>
								<?php elseif ( $shirt_home['jcolor'] || ( $is_goalkeeper && ! empty( $shirt_home['jcolor_gk'] ) ) ) : ?>
									<svg class="anwp-icon anwpfl-icon--shirt" style="fill: <?php echo esc_attr( ( $is_goalkeeper && ! empty( $shirt_home['jcolor_gk'] ) ) ? ( $shirt_home['jcolor_gk'] . '!important' ) : $shirt_home['jcolor'] ); ?>">
										<use xlink:href="#icon-shirt"></use>
									</svg>
								<?php else : ?>
									<svg class="anwp-icon anwpfl-icon--shirt" style="fill: <?php echo esc_attr( $color_home ); ?>">
										<use xlink:href="#icon-shirt"></use>
									</svg>
								<?php endif; ?>
							<?php endif; ?>

							<?php if ( ( 'photo_shirt' === $show_player && $player['photo'] ) || 'photo' === $show_player ) : ?>
								<img class="match-formation__player-photo" src="<?php echo esc_attr( $player['photo'] ? $photo_dir . $player['photo'] : $default_photo ); ?>" alt="player photo" />
							<?php endif; ?>

							<?php if ( $show_country ) : ?>
								<div class="match-formation__player-left-side flex-column d-flex">
									<?php
									if ( $player['nationality'] ) :
										anwp_football_leagues()->load_partial(
											[
												'class'         => 'options__flag',
												'wrapper_class' => 'mb-2 ml-n1',
												'size'          => 16,
												'country_code'  => $player['nationality'],
											],
											'general/flag'
										);
									endif;
									?>
								</div>
							<?php endif; ?>

							<?php if ( $show_events ) : ?>
								<div class="match-formation__player-goals">
									<?php
									if ( ! empty( $events[ $player_id ] ) ) :
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo anwp_football_leagues_premium()->match->get_formation_event_icons( $events[ $player_id ], 'goal' );
									endif;
									?>
								</div>
								<div class="match-formation__player-subs">
									<?php
									if ( ! empty( $events[ $player_id ] ) ) :
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo anwp_football_leagues_premium()->match->get_formation_event_icons( $events[ $player_id ], 'sub' );
									endif;
									?>
								</div>
								<div class="match-formation__player-cards">
									<?php
									if ( ! empty( $events[ $player_id ] ) ) :
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo anwp_football_leagues_premium()->match->get_formation_event_icons( $events[ $player_id ], 'card', true );
									endif;
									?>
								</div>
							<?php endif; ?>

							<?php
							/*
							|--------------------------------------------------------------------
							| Player Rating
							|--------------------------------------------------------------------
							*/
							if ( $show_rating && ! empty( $rating_field ) && ! empty( $home_players_statistics ) ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo anwp_football_leagues_premium()->stats->render_lineup_player_rating( $rating_field, $player_id, $home_players_statistics );
							}
							?>

							<div class="match-formation__player-number match-formation__player-number--home" style="<?php echo esc_attr( $is_goalkeeper && ! empty( $shirt_home['css_gk'] ) ? $shirt_home['css_gk'] : $shirt_home['css'] ); ?>"><?php echo esc_html( $player_number ) ?: '&nbsp;'; ?></div>
						</div>

						<span class="match-formation__player-name"><?php echo esc_html( $player['short_name'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		</div>

		<div class="d-flex flex-column flex-column-reverse justify-content-around fl-formation-away">
			<?php
			foreach ( $formation['away'] as $formation_line ) :

				if ( empty( $formation_line ) ) {
					continue;
				}

				?>
				<div class="d-flex match-formation__line">
					<?php
					foreach ( $formation_line as $player_id ) :

						if ( '_' === $player_id ) {
							echo '<div class="my-1 anwp-text-center position-relative match-formation__empty anwp-flex-1">&nbsp;</div>';
							continue;
						}

						// Init empty number
						$player_number = '';

						if ( ! empty( $data['custom_numbers']->{$player_id} ) ) {
							$player_number = (int) $data['custom_numbers']->{$player_id};
						} elseif ( isset( $away_squad[ $player_id ] ) && $away_squad[ $player_id ]['number'] ) {
							$player_number = (int) $away_squad[ $player_id ]['number'];
						}

						if ( ! empty( $temp_players ) && 'temp__' === mb_substr( $player_id, 0, 6 ) && isset( $temp_players[ $player_id ] ) ) {
							$player = [
								'name'       => $temp_players[ $player_id ]->name,
								'short_name' => $temp_players[ $player_id ]->name,
								'position'   => $temp_players[ $player_id ]->position,
							];
						} elseif ( ! empty( $data['players'][ $player_id ] ?? [] ) ) {
							$player = $data['players'][ $player_id ];
						} else {
							$player = anwp_fl()->player->get_player_data( $player_id );
						}

						if ( empty( $player ) ) {
							continue;
						}

						$is_goalkeeper = 'g' === $player['position'];
						?>
						<div class="anwp-flex-1 my-1 anwp-text-center position-relative match-formation__player d-flex flex-column align-items-center justify-content-center">
							<div class="match-formation__player_shirt <?php echo esc_attr( $is_goalkeeper ? 'match-formation__player_shirt--goalkeeper' : '' ); ?>">

								<?php if ( 'photo_shirt' === $show_player || 'shirt' === $show_player ) : ?>
									<?php if ( $shirt_away['svg'] && ! $is_goalkeeper ) : ?>
										<svg class="anwp-icon anwpfl-icon--shirt">
											<use xlink:href="#anwpfl-shirt-away"></use>
										</svg>
									<?php elseif ( $shirt_away['jcolor'] || ( $is_goalkeeper && ! empty( $shirt_away['jcolor_gk'] ) ) ) : ?>
										<svg class="anwp-icon anwpfl-icon--shirt" style="fill: <?php echo esc_attr( ( $is_goalkeeper && ! empty( $shirt_away['jcolor_gk'] ) ) ? ( $shirt_away['jcolor_gk'] . '!important' ) : $shirt_away['jcolor'] ); ?>">
											<use xlink:href="#icon-shirt"></use>
										</svg>
									<?php else : ?>
										<svg class="anwp-icon anwpfl-icon--shirt" style="fill: <?php echo esc_attr( $color_away ); ?>">
											<use xlink:href="#icon-shirt"></use>
										</svg>
									<?php endif; ?>
								<?php endif; ?>

								<?php if ( ( 'photo_shirt' === $show_player && $player['photo'] ) || 'photo' === $show_player ) : ?>
									<img class="match-formation__player-photo" src="<?php echo esc_attr( $player['photo'] ? $photo_dir . $player['photo'] : $default_photo ); ?>" alt="player photo" />
								<?php endif; ?>

								<?php if ( $show_country ) : ?>
									<div class="match-formation__player-left-side flex-column d-flex">
										<?php
										if ( $player['nationality'] ) :
											anwp_football_leagues()->load_partial(
												[
													'class'         => 'options__flag',
													'wrapper_class' => 'mb-2 ml-n1',
													'size'          => 16,
													'country_code'  => $player['nationality'],
												],
												'general/flag'
											);
										endif;
										?>
									</div>
								<?php endif; ?>

								<?php if ( $show_events ) : ?>
									<div class="match-formation__player-goals">
										<?php
										if ( ! empty( $events[ $player_id ] ) ) :
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo anwp_football_leagues_premium()->match->get_formation_event_icons( $events[ $player_id ], 'goal' );
										endif;
										?>
									</div>
									<div class="match-formation__player-subs">
										<?php
										if ( ! empty( $events[ $player_id ] ) ) :
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo anwp_football_leagues_premium()->match->get_formation_event_icons( $events[ $player_id ], 'sub' );
										endif;
										?>
									</div>
									<div class="match-formation__player-cards">
										<?php
										if ( ! empty( $events[ $player_id ] ) ) :
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo anwp_football_leagues_premium()->match->get_formation_event_icons( $events[ $player_id ], 'card', true );
										endif;
										?>
									</div>
								<?php endif; ?>

								<?php
								/*
								|--------------------------------------------------------------------
								| Player Rating
								|--------------------------------------------------------------------
								*/
								if ( $show_rating && ! empty( $rating_field ) && ! empty( $away_players_statistics ) ) {
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo anwp_football_leagues_premium()->stats->render_lineup_player_rating( $rating_field, $player_id, $away_players_statistics );
								}
								?>

								<div class="match-formation__player-number match-formation__player-number--away" style="<?php echo esc_attr( $is_goalkeeper && ! empty( $shirt_away['css_gk'] ) ? $shirt_away['css_gk'] : $shirt_away['css'] ); ?>"><?php echo esc_html( $player_number ) ?: '&nbsp;'; ?></div>
							</div>

							<span class="match-formation__player-name"><?php echo esc_html( $player['short_name'] ?? '' ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

		</div>
	</div>
	<img loading="lazy" class="match-formation__field-bg position-absolute d-none d-md-block" src="<?php echo esc_attr( $bg_field ); ?>" alt="field">
	<img loading="lazy" class="match-formation__field-bg match-formation__field-bg--vert position-absolute d-md-none" src="<?php echo esc_attr( $bg_field_vert ); ?>" alt="field">
</div>
<?php
if ( $shirt_home['svg'] ) {
	echo $shirt_home['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

if ( $shirt_away['svg'] ) {
	echo $shirt_away['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
