<?php
/**
 * The Template for displaying Stat for Players.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-stat-players.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.12.2
 *
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = wp_parse_args(
	$data,
	[
		'competition_id' => '',
		'multistage'     => 0,
		'season_id'      => '',
		'league_id'      => '',
		'club_id'        => '',
		'type'           => '',
		'links'          => 0,
		'first_em'       => 1,
		'stat'           => '',
		'limit'          => 10,
		'soft_limit'     => 0,
		'photos'         => 0,
		'games_played'   => 0,
		'show_full'      => 0,
		'hide_zero'      => 1,
		'date_from'      => '',
		'date_to'        => '',
	]
);

if ( empty( $data['stat'] ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Prepare arguments
|--------------------------------------------------------------------
*/
$data['type'] = mb_strtolower( $data['type'] );

foreach ( [ 'links', 'photos', 'first_em', 'games_played', 'show_full' ] as $arg_slug ) {
	$data[ $arg_slug ] = AnWP_Football_Leagues::string_to_bool( $data[ $arg_slug ] );
}

/*
|--------------------------------------------------------------------
| Get data
|--------------------------------------------------------------------
*/

// Try to get from cache
$cache_key = 'FL-PRO-SHORTCODE_stat-players__' . md5( maybe_serialize( $data ) );

if ( class_exists( 'AnWPFL_Cache' ) && anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) ) {
	$stats_data = anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' );
} else {
	$stats_data = anwp_football_leagues_premium()->player->get_players_single_stat( $data );

	if ( ! empty( $stats_data ) && class_exists( 'AnWPFL_Cache' ) ) {
		anwp_fl()->cache->set( $cache_key, $stats_data, 'anwp_match' );
	}
}

if ( empty( $stats_data ) ) {
	return;
}

if ( absint( $data['stat'] ) ) {
	$stat_config = anwp_fl_pro()->stats->get_stats_player_match_column_by_id( $data['stat'] );
}

$photo_dir     = wp_upload_dir()['baseurl'];
$photo_default = anwp_fl()->helper->get_default_player_photo();
?>
<div class="anwp-b-wrap">
	<div class="shortcode-stat_players stat-players anwp-fl-border anwp-border-light anwp-text-sm stats-players--stat-<?php echo esc_attr( $data['stat'] ); ?>">
		<?php
		foreach ( $stats_data as $index => $player_stat ) :

			/** @var array $player_stat = [
			 *           'player_id' => 1, // Player ID
			 *           'name'      => 'Player Name',
			 *           'photo'     => 'photo url',
			 *           'link'      => 'player profile url',
			 *           'clubs'     => '1,2,3',
			 *           'stat'      => 12,
			 *           'gp'        => 30, // Games Played
			 *  ] */
			if ( $data['first_em'] && $index < 1 ) :
				?>
				<div class="d-flex flex-column stat-players__first-player-wrapper">
					<div class="stat-players__first_photo anwp-bg-light p-3 anwp-text-center">
						<img
								alt="<?php echo esc_html( $player_stat['name'] ); ?>"
								loading="lazy" width="80" height="80" class="stat-players__first_photo_img mx-auto mb-0 anwp-w-80 anwp-h-80"
								src="<?php echo esc_url( $player_stat['photo'] ? ( $photo_dir . $player_stat['photo'] ) : $photo_default ); ?>">
					</div>
					<div class="stat-players__first_data anwp-bg-light d-flex align-items-center p-2 anwp-text-lg anwp-fl-border-top anwp-border-light">
						<div class="stat-players__place stat-players__first-place anwp-w-30 pr-2 anwp-text-center"><?php echo absint( $index + 1 ); ?></div>
						<div class="stat-players__clubs stat-players__first-clubs my-n1 d-flex align-items-center anwp-flex-none mr-2">
							<?php
							foreach ( explode( ',', $player_stat['clubs'] ) as $club_id ) :
								$club_logo = anwp_football_leagues()->club->get_club_logo_by_id( $club_id );

								if ( $club_logo ) :
									?>
									<img
										alt="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $club_id ) ); ?>"
										loading="lazy" width="30" height="30" class="stat-players__club-logo stat-players__first-club-logo anwp-object-contain mr-1 mb-0 anwp-w-30 anwp-h-30" src="<?php echo esc_url( $club_logo ); ?>"
										data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $club_id ) ); ?>">
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
						<div class="stat-players__name stat-players__first-name pr-2">
							<?php if ( $data['links'] && ! empty( $player_stat['link'] ) ) : ?>
								<a class="text-decoration-none anwp-link-without-effects" href="<?php echo esc_attr( $player_stat['link'] ); ?>">
									<?php echo esc_html( $player_stat['name'] ); ?>
								</a>
							<?php else : ?>
								<?php echo esc_html( $player_stat['name'] ); ?>
							<?php endif; ?>
						</div>
						<div class="stat-players__stat stat-players__first-stat ml-auto">
							<?php
							if ( ! empty( $stat_config['digits'] ) && $stat_config['digits'] > 0 && ! empty( $player_stat['stat'] ) ) {
								$player_stat['stat'] = number_format( $player_stat['stat'], absint( $stat_config['digits'] ), '.', '' );
							}
							echo esc_html( $player_stat['stat'] );
							?>
						</div>

						<?php if ( $data['games_played'] ) : ?>
							<div class="stat-players__gp stat-players__first-gp ml-2 anwp-opacity-60">(<?php echo esc_html( $player_stat['gp'] ); ?>)</div>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
				<div class="stat-players__player-wrapper d-flex align-items-center py-1 px-2 <?php echo esc_attr( $index > 0 ? 'anwp-fl-border-top anwp-border-light' : '' ); ?>">
					<div class="stat-players__place anwp-w-30 pr-2 anwp-text-center"><?php echo absint( $index + 1 ); ?></div>

					<?php if ( $data['photos'] ) : ?>
						<div class="stat-players__photo mr-2 anwp-flex-none">
							<img
								alt="<?php echo esc_html( $player_stat['name'] ); ?>"
								loading="lazy" width="40" height="40" class="stat-players__photo_img mb-0 anwp-w-40 anwp-h-40"
								src="<?php echo esc_url( $player_stat['photo'] ? ( $photo_dir . $player_stat['photo'] ) : $photo_default ); ?>">
						</div>
					<?php endif; ?>

					<div class="stat-players__clubs anwp-flex-none d-flex mr-2 <?php echo $data['photos'] ? 'my-n1' : ''; ?>">
						<?php
						foreach ( explode( ',', $player_stat['clubs'] ) as $club_id ) :
							$club_logo = anwp_football_leagues()->club->get_club_logo_by_id( $club_id );

							if ( $club_logo ) :
								?>
								<img
									alt="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $club_id ) ); ?>"
									loading="lazy" width="30" height="30" class="stat-players__club-logo anwp-object-contain mr-1 mb-0 anwp-w-30 anwp-h-30"
									src="<?php echo esc_url( $club_logo ); ?>"
									data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $club_id ) ); ?>">
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="stat-players__name pr-2 anwp-leading-1">
						<?php if ( $data['links'] && ! empty( $player_stat['link'] ) ) : ?>
							<a class="text-decoration-none anwp-link-without-effects" href="<?php echo esc_attr( $player_stat['link'] ); ?>">
								<?php echo esc_html( $player_stat['name'] ); ?>
							</a>
						<?php else : ?>
							<?php echo esc_html( $player_stat['name'] ); ?>
						<?php endif; ?>
					</div>

					<div class="stat-players__stat ml-auto">
						<?php
						if ( ! empty( $stat_config['digits'] ) && $stat_config['digits'] > 0 && ! empty( $player_stat['stat'] ) ) {
							$player_stat['stat'] = number_format( $player_stat['stat'], absint( $stat_config['digits'] ), '.', '' );
						}
						echo esc_html( empty( $player_stat['stat'] ) ? 0 : $player_stat['stat'] );
						?>
					</div>

					<?php if ( $data['games_played'] ) : ?>
						<div class="stat-players__gp ml-2 anwp-opacity-60">(<?php echo esc_html( $player_stat['gp'] ); ?>)</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

		<?php if ( ! empty( $stats_data ) && $data['show_full'] ) : ?>
			<a class="d-flex align-items-center p-2 anwp-fl-border-top anwp-border-light justify-content-center anwp-bg-light text-decoration-none anwp-link-without-effects anwp-modaal-stat-players-list"
					href="#" data-anwp-args="<?php echo esc_attr( anwp_football_leagues_premium()->player->get_serialized_stat_players_data( $data ) ); ?>">
				<?php echo esc_html( AnWPFL_Text::get_value( 'players__stat__show_full_list', __( 'Show full list', 'anwp-football-leagues-premium' ) ) ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
