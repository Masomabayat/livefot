<?php
/**
 * The Template for displaying Transfers >> Player layout.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-transfers--player.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         AnWP-Football-Leagues-Premium/Templates
 *
 * @since           0.8.11
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
		'competition_id' => '',
		'club_column'    => 'logo',
	]
);

if ( ! absint( $args['player_id'] ) ) {
	return;
}

/*
|--------------------------------------------------------------------
| Prepare initial data
|--------------------------------------------------------------------
*/
$date_format = anwp_fl()->get_option_value( 'custom_match_date_format' ) ?: 'M j, Y';
$transfers   = anwp_fl_pro()->transfer->get_transfers( $args )['transfers'] ?? [];

if ( empty( $transfers ) ) {
	return;
}
?>
<div class="anwp-b-wrap transfers-list transfers-list--shortcode transfers-list-player anwp-grid-table anwp-grid-table--aligned anwp-grid-table--bordered anwp-text-xs anwp-border-light">

	<div class="anwp-grid-table__td transfers-list-player__club-out anwp-bg-light justify-content-start anwp-grid-table__sm-none">
		<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__club_out', __( 'Club Out', 'anwp-football-leagues-premium' ) ) ); ?>
	</div>

	<div class="anwp-grid-table__td transfers-list-player__club-in anwp-bg-light justify-content-start anwp-grid-table__sm-none">
		<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__club_in', __( 'Club In', 'anwp-football-leagues-premium' ) ) ); ?>
	</div>

	<div class="anwp-grid-table__td transfers-list-player__date anwp-bg-light justify-content-center anwp-grid-table__sm-none">
		<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__transfer_date', __( 'Transfer Date', 'anwp-football-leagues-premium' ) ) ); ?>
	</div>

	<div class="anwp-grid-table__td transfers-list-player__fee anwp-bg-light justify-content-center anwp-grid-table__sm-none">
		<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__fee', __( 'Fee', 'anwp-football-leagues-premium' ) ) ); ?>
	</div>

	<div class="anwp-grid-table__td transfers-list-player__data anwp-bg-light d-none anwp-grid-table__sm-flex flex-wrap">
		<span><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__club_out', __( 'Club Out', 'anwp-football-leagues-premium' ) ) ); ?></span>
		<span class="mx-2">/</span>
		<span><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__club_in', __( 'Club In', 'anwp-football-leagues-premium' ) ) ); ?></span>
	</div>

	<div class="anwp-grid-table__td transfers-list-player__data anwp-bg-light d-none anwp-grid-table__sm-flex flex-wrap">
		<span><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__transfer_date', __( 'Transfer Date', 'anwp-football-leagues-premium' ) ) ); ?></span>
		<span class="mx-2">/</span>
		<span><?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__fee', __( 'Fee', 'anwp-football-leagues-premium' ) ) ); ?></span>
	</div>

	<?php
	foreach ( $transfers as $transfer ) :
		?>

		<div class="anwp-grid-table__td transfers-list-player__club-out justify-content-start anwp-grid-table__sm-none anwp-overflow-x-hidden">
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
					<img loading="lazy" width="30" height="30" class="anwp-object-contain mr-2 transfers-list-player__club-out-logo anwp-w-30 anwp-h-30"
						data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
						src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
				</a>
				<?php echo esc_html( $club_title ? : '' ); ?>
			<?php elseif ( ! empty( $club_logo ) && 2 !== $club_id ) : ?>
				<img loading="lazy" width="30" height="30" class="anwp-object-contain mr-2 transfers-list-player__club-out-logo anwp-w-30 anwp-h-30"
					data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
					src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
				<?php echo esc_html( $club_title ? : '' ); ?>
			<?php elseif ( 2 === $club_id && ! empty( $transfer['club_out_text'] ) ) : ?>
				<?php echo esc_html( $transfer['club_out_text'] ); ?>
			<?php endif; ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-player__club-in justify-content-start anwp-text-center anwp-grid-table__sm-none anwp-overflow-x-hidden">
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
					<img loading="lazy" width="30" height="30" class="anwp-object-contain mr-2 transfers-list-player__club-in-logo anwp-w-30 anwp-h-30"
						data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
						src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
				</a>
				<?php echo esc_attr( $club_title ? : '' ); ?>
			<?php elseif ( ! empty( $club_logo ) && 2 !== $club_id ) : ?>
				<img loading="lazy" width="30" height="30" class="anwp-object-contain mr-2 transfers-list-player__club-in-logo anwp-w-30 anwp-h-30"
					data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
					src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
				<?php echo esc_attr( $club_title ? : '' ); ?>
			<?php elseif ( 2 === $club_id && ! empty( $transfer['club_in_text'] ) ) : ?>
				<?php echo esc_html( $transfer['club_in_text'] ); ?>
			<?php endif; ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-player__date justify-content-center anwp-grid-table__sm-none">
			<?php echo $transfer['transfer_date'] && '0000-00-00' !== $transfer['transfer_date'] ? esc_html( date_i18n( $date_format, strtotime( $transfer['transfer_date'] ) ) ) : ''; ?>
		</div>

		<div class="anwp-grid-table__td transfers-list-player__fee justify-content-center anwp-grid-table__sm-none flex-column">
			<div><?php echo esc_html( $transfer['fee'] ); ?></div>

			<?php if ( ! absint( $transfer['transfer_status'] ) ) : ?>
				<div class="anwp-bg-secondary anwp-text-white anwp-leading-1 anwp-text-xs text-uppercase anwp-text-center squad-rows__status-badge mt-1">
					<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__rumour', __( 'Rumour', 'anwp-football-leagues-premium' ) ) ); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="anwp-grid-table__td d-none transfers-list-player__data anwp-grid-table__sm-flex flex-column justify-content-start align-items-start anwp-text-sm">
			<div class="d-flex align-items-center">
				<svg class="icon__subs-out icon--lineups mr-2"><use xlink:href="#icon-arrow-o-down"></use></svg>
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
						<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-2 transfers-list-player__club-out-logo anwp-w-20 anwp-h-20"
							data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
							src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
					</a>
					<?php echo esc_attr( $club_title ? : '' ); ?>
				<?php elseif ( ! empty( $club_logo ) && 2 !== $club_id ) : ?>
					<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-2 transfers-list-player__club-out-logo anwp-w-20 anwp-h-20"
						data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
						src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
					<?php echo esc_attr( $club_title ? : '' ); ?>
				<?php elseif ( 2 === $club_id && ! empty( $transfer['club_out_text'] ) ) : ?>
					<?php echo esc_html( $transfer['club_out_text'] ); ?>
				<?php endif; ?>
			</div>

			<div class="d-flex align-items-center mt-1">
				<svg class="icon__subs-in icon--lineups mr-2"><use xlink:href="#icon-arrow-o-up"></use></svg>
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
						<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-2 transfers-list-player__club-in-logo anwp-w-20 anwp-h-20"
							data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
							src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
					</a>
					<?php echo esc_html( $club_title ); ?>
				<?php elseif ( ! empty( $club_logo ) && 2 !== $club_id ) : ?>
					<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-2 transfers-list-player__club-in-logo anwp-w-20 anwp-h-20"
						data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
						src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
					<?php echo esc_html( $club_title ); ?>
				<?php elseif ( 2 === $club_id && ! empty( $transfer['club_in_text'] ) ) : ?>
					<?php echo esc_html( $transfer['club_in_text'] ); ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="anwp-grid-table__td d-none transfers-list-player__data anwp-grid-table__sm-flex flex-column justify-content-start align-items-start anwp-text-sm">
			<?php if ( $transfer['transfer_date'] && '0000-00-00' !== $transfer['transfer_date'] ) : ?>
				<div class="mt-1 d-flex align-items-center transfers-list-player__date">
					<svg class="anwp-icon anwp-icon--octi mr-2 anwp-icon--s16" style="margin-left: 2px;">
						<use xlink:href="#icon-calendar"></use>
					</svg>
					<?php echo esc_html( date_i18n( $date_format, strtotime( $transfer['transfer_date'] ) ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $transfer['fee'] ) : ?>
				<div class="mt-1">
					<?php echo esc_html( $transfer['fee'] ); ?>
					<?php if ( ! absint( $transfer['transfer_status'] ) ) : ?>
						<span class="anwp-bg-secondary anwp-text-white anwp-leading-1 anwp-text-xs text-uppercase anwp-text-center squad-rows__status-badge ml-2 transfers-list-player__rumour">
							<?php echo esc_html( AnWPFL_Text::get_value( 'transfers__shortcode__rumour', __( 'Rumour', 'anwp-football-leagues-premium' ) ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
