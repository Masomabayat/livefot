<?php
/**
 * The Template for displaying Transfers.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-transfers.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         AnWP-Football-Leagues-Premium/Templates
 *
 * @since           0.8.11
 *
 * @version         0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = wp_parse_args(
	$data,
	[
		'club_id'        => '',
		'season_id'      => '',
		'type'           => '',
		'window'         => '',
		'limit'          => 0,
		'order'          => 'ASC',
		'player_id'      => '',
		'date_from'      => '',
		'date_to'        => '',
		'link'           => '',
		'competition_id' => '',
	]
);

$transfer_club_id = absint( $args['club_id'] );

if ( empty( $transfer_club_id ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Prepare initial data
|--------------------------------------------------------------------
*/
$default_photo = anwp_fl()->helper->get_default_player_photo();
$date_format   = anwp_fl()->get_option_value( 'custom_match_date_format' ) ?: 'M j, Y';
$transfers     = anwp_fl_pro()->transfer->get_transfers( $args )['transfers'] ?? [];

if ( empty( $transfers ) ) {
	return;
}

$no_data_html = '<div class="anwp-grid-table__td transfers-list__nodata anwp-text-sm">' . esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__no_data', __( 'No data', 'anwp-football-leagues-premium' ) ) ) . '</div>';
$rendered_in  = 0;
$rendered_out = 0;
?>
<div class="anwp-b-wrap transfers-list transfers-list--shortcode transfers-list-club anwp-grid-table anwp-grid-table--aligned anwp-grid-table--bordered anwp-text-xs anwp-border-light">

	<?php if ( 'out' !== $args['type'] ) : ?>
		<div class="anwp-grid-table__td transfers-list-club__type anwp-bg-gray-light anwp-text-base">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__arrivals', __( 'Arrivals', 'anwp-football-leagues-premium' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__player anwp-bg-light">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__player', __( 'Player', 'anwp-football-leagues' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__club-out anwp-bg-light justify-content-center anwp-grid-table__sm-none">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__club_out', __( 'Club Out', 'anwp-football-leagues-premium' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__dob anwp-bg-light justify-content-center anwp-grid-table__sm-none">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__date_of_birth', __( 'Date Of Birth', 'anwp-football-leagues' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__date anwp-bg-light justify-content-center anwp-grid-table__sm-none">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__transfer_date', __( 'Transfer Date', 'anwp-football-leagues-premium' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__fee anwp-bg-light justify-content-center anwp-grid-table__sm-none">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__fee', __( 'Fee', 'anwp-football-leagues-premium' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__data anwp-bg-light d-none anwp-grid-table__sm-flex flex-wrap">
			<span><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__club_out', __( 'Club Out', 'anwp-football-leagues-premium' ) ) ); ?></span>
			<span class="mx-2">/</span>
			<span><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__transfer_date', __( 'Transfer Date', 'anwp-football-leagues-premium' ) ) ); ?></span>
			<span class="mx-2">/</span>
			<span><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__fee', __( 'Fee', 'anwp-football-leagues-premium' ) ) ); ?></span>
		</div>

		<?php
		foreach ( $transfers as $transfer ) :
			if ( empty( $transfer['player_id'] ) || absint( $transfer['club_in'] ) !== $transfer_club_id ) {
				continue;
			}

			$rendered_in ++;
			?>
			<div class="anwp-grid-table__td transfers-list-club__player d-flex align-items-start anwp-overflow-x-hidden">

				<img loading="lazy" width="40" height="40" class="anwp-object-contain m-0 mr-2 transfers-list-club__player-photo anwp-w-40 anwp-h-40"
						src="<?php echo esc_url( $transfer['player_photo'] ? anwp_fl()->upload_dir . $transfer['player_photo'] : $default_photo ); ?>" alt="player photo">

				<div class="d-flex flex-column">
					<?php if ( ! empty( $transfer['player_link'] ) ) : ?>
						<a class="anwp-link-without-effects d-flex align-items-center" href="<?php echo esc_url( $transfer['player_link'] ); ?>">
							<span class="mr-2 anwp-text-sm transfers-list-competition__player-name"><?php echo esc_html( $transfer['player_name'] ); ?></span>
						</a>
					<?php else : ?>
						<span class="mr-2 anwp-text-sm transfers-list-competition__player-name"><?php echo esc_html( $transfer['player_name'] ); ?></span>
					<?php endif; ?>

					<?php
					if ( ! empty( $transfer['player_nationality'] ) ) :
						anwp_fl()->load_partial(
							[
								'class'        => 'options__flag mb-n1',
								'size'         => 16,
								'country_code' => $transfer['player_nationality'],
							],
							'general/flag'
						);
					endif;
					?>
					<div class="d-flex align-items-center flex-wrap">
						<?php if ( $transfer['player_position'] ) : ?>
							<span class="transfers-list-club__player-position anwp-opacity-80 mr-2"><?php echo esc_html( $transfer['player_position'] ); ?></span>
						<?php endif; ?>
						<div class="d-none anwp-grid-table__sm-block transfers-list-club__player-dob anwp-opacity-80">
							- <?php echo $transfer['player_birth_date'] ? esc_html( date_i18n( $date_format, strtotime( $transfer['player_birth_date'] ) ) ) : ''; ?>
						</div>
						<?php if ( ! absint( $transfer['transfer_status'] ) ) : ?>
							<span class="anwp-bg-secondary anwp-text-white anwp-leading-1 anwp-text-xs text-uppercase anwp-text-center transfers-list-club__rumour"><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__rumour', __( 'Rumour', 'anwp-football-leagues-premium' ) ) ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="anwp-grid-table__td transfers-list-club__club-out justify-content-center anwp-text-center anwp-grid-table__sm-none">
				<?php
				$club_id = absint( $transfer['club_out'] );

				if ( $club_id > 2 ) {
					$club_logo  = anwp_fl()->club->get_club_logo_by_id( $club_id );
					$club_title = anwp_fl()->club->get_club_title_by_id( $club_id );
					$club_link  = anwp_fl()->club->get_club_link_by_id( $club_id );
				} elseif ( 1 === $club_id ) {
					$club_logo  = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-unknown.svg' );
					$club_title = '- ' . esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__unknown_club', __( 'unknown club', 'anwp-football-leagues-premium' ) ) ) . ' -';
				} elseif ( ! $club_id ) {
					$club_logo  = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-none.svg' );
					$club_title = '- ' . esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__without_club', __( 'without club', 'anwp-football-leagues-premium' ) ) ) . ' -';
				}

				if ( ! empty( $club_link ) && ! empty( $club_logo ) && 2 !== $club_id ) :
					?>
					<a href="<?php echo esc_url( $club_link ); ?>" class="anwp-link-without-effects text-decoration-none">
						<img loading="lazy" width="30" height="30" class="anwp-object-contain transfers-list-club__club-out-logo mr-2 anwp-w-30 anwp-h-30"
							data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ?? '' ); ?>"
							src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ?? '' ); ?>">
					</a>
					<?php echo esc_html( $club_title ?? '' ); ?>
				<?php elseif ( ! empty( $club_logo ) && 2 !== $club_id ) : ?>
					<img loading="lazy" width="30" height="30" class="anwp-object-contain transfers-list-club__club-out-logo mr-2 anwp-w-30 anwp-h-30"
						data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
						src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
					<?php echo esc_html( $club_title ); ?>
				<?php elseif ( 2 === $club_id && ! empty( $transfer['club_out_text'] ) ) : ?>
					<?php echo esc_html( $transfer['club_out_text'] ); ?>
				<?php endif; ?>
			</div>

			<div class="anwp-grid-table__td transfers-list-club__dob justify-content-center anwp-grid-table__sm-none">
				<?php echo $transfer['player_birth_date'] ? esc_html( date_i18n( $date_format, strtotime( $transfer['player_birth_date'] ) ) ) : ''; ?>
			</div>

			<div class="anwp-grid-table__td transfers-list-club__date justify-content-center anwp-grid-table__sm-none">
				<?php echo $transfer['transfer_date'] && '0000-00-00' !== $transfer['transfer_date'] ? esc_html( date_i18n( $date_format, strtotime( $transfer['transfer_date'] ) ) ) : ''; ?>
			</div>

			<div class="anwp-grid-table__td transfers-list-club__fee justify-content-center anwp-grid-table__sm-none">
				<?php echo esc_html( $transfer['fee'] ); ?>
			</div>

			<div class="anwp-grid-table__td d-none transfers-list-club__data anwp-grid-table__sm-flex flex-column justify-content-start align-items-start anwp-text-sm">
				<div class="d-flex align-items-center">
					<?php
					$club_id = absint( $transfer['club_out'] );

					if ( absint( $club_id ) ) {
						$club_logo  = anwp_fl()->club->get_club_logo_by_id( $club_id );
						$club_title = anwp_fl()->club->get_club_title_by_id( $club_id );
						$club_link  = anwp_fl()->club->get_club_link_by_id( $club_id );
					} elseif ( 1 === $club_id ) {
						$club_logo  = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-unknown.svg' );
						$club_title = '- ' . esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__unknown_club', __( 'unknown club', 'anwp-football-leagues-premium' ) ) ) . ' -';
					} elseif ( ! $club_id ) {
						$club_logo  = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-none.svg' );
						$club_title = '- ' . esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__without_club', __( 'without club', 'anwp-football-leagues-premium' ) ) ) . ' -';
					}

					if ( ! empty( $club_link ) && ! empty( $club_logo ) && 2 !== $club_id ) :
						?>
						<a href="<?php echo esc_url( $club_link ); ?>" class="anwp-link-without-effects text-decoration-none">
							<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-2 transfers-list-club__club-out-logo anwp-w-20 anwp-h-20"
								data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
								src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
						</a>
						<?php echo esc_html( $club_title ); ?>
					<?php elseif ( ! empty( $club_logo ) && 2 !== $club_id ) : ?>
						<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-2 transfers-list-club__club-out-logo anwp-w-20 anwp-h-20"
							data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
							src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
						<?php echo esc_html( $club_title ); ?>
					<?php elseif ( 2 === $club_id && ! empty( $transfer['club_out_text'] ) ) : ?>
						<?php echo esc_html( $transfer['club_out_text'] ); ?>
					<?php endif; ?>
				</div>

				<?php if ( $transfer['transfer_date'] && '0000-00-00' !== $transfer['transfer_date'] ) : ?>
					<span class="mt-1 transfers-list-club__date"><?php echo esc_html( date_i18n( $date_format, strtotime( $transfer['transfer_date'] ) ) ); ?></span>
				<?php endif; ?>

				<?php if ( $transfer['fee'] ) : ?>
					<span class="mt-1 transfers-list-club__fee"><?php echo esc_html( $transfer['fee'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( empty( $rendered_in ) ) : ?>
		<?php echo $no_data_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>

	<?php if ( 'in' !== $args['type'] ) : ?>
		<div class="anwp-grid-table__td transfers-list-club__type anwp-bg-gray-light anwp-text-base">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__departures', __( 'Departures', 'anwp-football-leagues-premium' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__player anwp-bg-light">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__player', __( 'Player', 'anwp-football-leagues' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__club-in anwp-bg-light justify-content-center anwp-grid-table__sm-none">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__club_in', __( 'Club In', 'anwp-football-leagues-premium' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__dob anwp-bg-light justify-content-center anwp-grid-table__sm-none">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__date_of_birth', __( 'Date Of Birth', 'anwp-football-leagues' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__date anwp-bg-light justify-content-center anwp-grid-table__sm-none">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__transfer_date', __( 'Transfer Date', 'anwp-football-leagues-premium' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__fee anwp-bg-light justify-content-center anwp-grid-table__sm-none">
			<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__fee', __( 'Fee', 'anwp-football-leagues-premium' ) ) ); ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-club__data anwp-bg-light d-none anwp-grid-table__sm-flex flex-wrap">
			<span><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__club_in', __( 'Club In', 'anwp-football-leagues-premium' ) ) ); ?></span>
			<span class="mx-2">/</span>
			<span><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__transfer_date', __( 'Transfer Date', 'anwp-football-leagues-premium' ) ) ); ?></span>
			<span class="mx-2">/</span>
			<span><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__fee', __( 'Fee', 'anwp-football-leagues-premium' ) ) ); ?></span>
		</div>

		<?php
		foreach ( $transfers as $transfer ) :
			if ( empty( $transfer['player_id'] ) || absint( $transfer['club_out'] ) !== $transfer_club_id ) {
				continue;
			}

			$rendered_out ++;
			?>

			<div class="anwp-grid-table__td transfers-list-club__player d-flex align-items-start anwp-overflow-x-hidden">

				<img loading="lazy" width="40" height="40" class="anwp-object-contain m-0 mr-2 transfers-list-club__player-photo anwp-w-40 anwp-h-40"
					src="<?php echo esc_url( $transfer['player_photo'] ? anwp_fl()->upload_dir . $transfer['player_photo'] : $default_photo ); ?>" alt="player photo">

				<div class="d-flex flex-column">
					<span class="mr-2 anwp-text-sm transfers-list-club__player-name"><?php echo esc_html( $transfer['player_name'] ); ?></span>

					<?php
					if ( ! empty( $transfer['player_nationality'] ) ) :
						anwp_fl()->load_partial(
							[
								'class'        => 'options__flag mb-n1',
								'size'         => 16,
								'country_code' => $transfer['player_nationality'],
							],
							'general/flag'
						);
					endif;
					?>
					<div class="d-flex align-items-center flex-wrap">
						<?php if ( $transfer['player_position'] ) : ?>
							<span class="team__player-role anwp-opacity-80 mr-2 transfers-list-club__player-position"><?php echo esc_html( $transfer['player_position'] ); ?></span>
						<?php endif; ?>
						<div class="d-none anwp-grid-table__sm-block anwp-opacity-80 transfers-list-club__date">
							- <?php echo $transfer['player_birth_date'] ? esc_html( date_i18n( $date_format, strtotime( $transfer['player_birth_date'] ) ) ) : ''; ?>
						</div>
						<?php if ( ! absint( $transfer['transfer_status'] ) ) : ?>
							<span class="anwp-bg-secondary anwp-text-white anwp-leading-1 anwp-text-xs text-uppercase anwp-text-center transfers-list-club__rumour"><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__rumour', __( 'Rumour', 'anwp-football-leagues-premium' ) ) ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="anwp-grid-table__td transfers-list-club__club-in justify-content-center anwp-text-center anwp-grid-table__sm-none">
				<?php
				$club_id = absint( $transfer['club_in'] );

				if ( $club_id > 2 ) {
					$club_logo  = anwp_fl()->club->get_club_logo_by_id( $club_id );
					$club_title = anwp_fl()->club->get_club_title_by_id( $club_id );
					$club_link  = anwp_fl()->club->get_club_link_by_id( $club_id );
				} elseif ( 1 === $club_id ) {
					$club_logo  = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-unknown.svg' );
					$club_title = '- ' . esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__unknown_club', __( 'unknown club', 'anwp-football-leagues-premium' ) ) ) . ' -';
				} elseif ( ! $club_id ) {
					$club_logo  = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-none.svg' );
					$club_title = '- ' . esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__without_club', __( 'without club', 'anwp-football-leagues-premium' ) ) ) . ' -';
				}

				if ( ! empty( $club_link ) && ! empty( $club_logo ) && 2 !== $club_id ) :
					?>
					<a href="<?php echo esc_url( $club_link ); ?>" class="anwp-link-without-effects text-decoration-none">
						<img loading="lazy" width="30" height="30" class="anwp-object-contain transfers-list-club__club-in-logo mr-2 anwp-w-30 anwp-h-30"
							data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
							src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
					</a>
					<?php echo esc_html( $club_title ); ?>
				<?php elseif ( ! empty( $club_logo ) && 2 !== $club_id ) : ?>
					<img loading="lazy" width="30" height="30" class="anwp-object-contain transfers-list-club__club-in-logo mr-2 anwp-w-30 anwp-h-30"
						data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
						src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
					<?php echo esc_html( $club_title ); ?>
				<?php elseif ( 2 === $club_id && ! empty( $transfer['club_in_text'] ) ) : ?>
					<?php echo esc_html( $transfer['club_in_text'] ); ?>
				<?php endif; ?>
			</div>

			<div class="anwp-grid-table__td transfers-list-club__dob justify-content-center anwp-grid-table__sm-none">
				<?php echo $transfer['player_birth_date'] ? esc_html( date_i18n( $date_format, strtotime( $transfer['player_birth_date'] ) ) ) : ''; ?>
			</div>

			<div class="anwp-grid-table__td transfers-list-club__date justify-content-center anwp-grid-table__sm-none">
				<?php echo $transfer['transfer_date'] && '0000-00-00' !== $transfer['transfer_date'] ? esc_html( date_i18n( $date_format, strtotime( $transfer['transfer_date'] ) ) ) : ''; ?>
			</div>

			<div class="anwp-grid-table__td transfers-list-club__fee justify-content-center anwp-grid-table__sm-none">
				<?php echo esc_html( $transfer['fee'] ); ?>
			</div>

			<div class="anwp-grid-table__td d-none transfers-list-club__data anwp-grid-table__sm-flex flex-column justify-content-start align-items-start anwp-text-sm">
				<div class="d-flex align-items-center">
					<?php
					$club_id = absint( $transfer['club_in'] );

					if ( absint( $club_id ) ) {
						$club_logo  = anwp_fl()->club->get_club_logo_by_id( $club_id );
						$club_title = anwp_fl()->club->get_club_title_by_id( $club_id );
						$club_link  = anwp_fl()->club->get_club_link_by_id( $club_id );
					} elseif ( 1 === $club_id ) {
						$club_logo  = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-unknown.svg' );
						$club_title = '- ' . esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__unknown_club', __( 'unknown club', 'anwp-football-leagues-premium' ) ) ) . ' -';
					} elseif ( ! $club_id ) {
						$club_logo  = AnWP_Football_Leagues_Premium::url( 'public/img/svg-icons/club-none.svg' );
						$club_title = '- ' . esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__without_club', __( 'without club', 'anwp-football-leagues-premium' ) ) ) . ' -';
					}

					if ( ! empty( $club_link ) && ! empty( $club_logo ) && 2 !== $club_id ) :
						?>
						<a href="<?php echo esc_url( $club_link ); ?>" class="anwp-link-without-effects text-decoration-none">
							<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-2 transfers-list-club__club-in-logo anwp-w-20 anwp-h-20"
								data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
								src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
						</a>
						<?php echo esc_html( $club_title ); ?>
					<?php elseif ( ! empty( $club_logo ) && 2 !== $club_id ) : ?>
						<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-2 transfers-list-club__club-in-logo anwp-w-20 anwp-h-20"
							data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
							src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
						<?php echo esc_html( $club_title ); ?>
					<?php elseif ( 2 === $club_id && ! empty( $transfer['club_in_text'] ) ) : ?>
						<?php echo esc_html( $transfer['club_in_text'] ); ?>
					<?php endif; ?>
				</div>

				<?php if ( $transfer['transfer_date'] && '0000-00-00' !== $transfer['transfer_date'] ) : ?>
					<span class="mt-1"><?php echo esc_html( date_i18n( $date_format, strtotime( $transfer['transfer_date'] ) ) ); ?></span>
				<?php endif; ?>

				<?php if ( $transfer['fee'] ) : ?>
					<span class="mt-1"><?php echo esc_html( $transfer['fee'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ( empty( $rendered_out ) ) : ?>
		<?php echo $no_data_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
</div>
