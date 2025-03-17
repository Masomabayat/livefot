<?php
/**
 * The Template for displaying Player >> Birthday Card Wide (slim).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/player/player-birthday-card--wide-slim.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.10.2
 *
 * @version       0.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$data,
	[
		'ID'            => '',
		'current_club'  => '',
		'date_of_birth' => '',
		'post_title'    => '',
		'post_type'     => '',
		'photo'         => '',
	]
);

if ( ! anwp_fl()->helper->validate_date( $data['date_of_birth'], 'Y-m-d' ) ) {
	return;
}

$default_photo  = anwp_fl()->helper->get_default_player_photo();
$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $data['date_of_birth'] );
$diff_date_obj  = DateTime::createFromFormat( 'Y-m-d', date( 'Y' ) . '-' . date( 'm-d', strtotime( $data['date_of_birth'] ) ) );
$age            = $birth_date_obj->diff( $diff_date_obj )->y;
$date_format    = anwp_fl()->get_option_value( 'custom_match_date_format' ) ?: 'M j, Y';
?>
<div class="player-birthday-card-wide anwp-fl-border anwp-border-light player-birthday-card-wide-slim">
	<div class="d-flex">
		<div class="player-birthday-card-wide__age-wrapper d-flex flex-column p-2">
			<div class="player-birthday-card-wide-slim__age px-1 anwp-text-2xl anwp-leading-1"><?php echo absint( $age ); ?></div>
		</div>
		<div class="d-flex flex-column flex-grow-1 player-birthday-card-wide__meta py-1">
			<div class="player-birthday-card-wide-slim__name anwp-text-base"><?php echo esc_html( $data['player_name'] ); ?></div>
			<div class="player-birthday-card-wide__meta d-flex flex-wrap align-items-center anwp-text-sm mt-1">
				<div class="player-birthday-card-wide__date d-flex align-items-center">
					<svg class="anwp-icon anwp-icon--octi mr-1">
						<use xlink:href="#icon-calendar"></use>
					</svg>
					<span class="player-birthday-card-wide__date-text"><?php echo esc_html( date_i18n( $date_format, get_date_from_gmt( $data['date_of_birth'], 'U' ) ) ); ?></span>
				</div>
				<?php
				if ( absint( $data['current_club'] ) ) :

					$club_title = anwp_fl()->club->get_club_abbr_by_id( $data['current_club'] );
					$club_logo  = anwp_fl()->club->get_club_logo_by_id( $data['current_club'] );
					?>
					<span class="mx-2">-</span>
					<div class="player-birthday-card-wide__club-wrapper d-flex align-items-center">
						<?php if ( $club_logo ) : ?>
							<img loading="lazy" width="20" height="20" class="anwp-object-contain mr-1 anwp-w-20 anwp-h-20"
								src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
						<?php endif; ?>
						<?php echo esc_html( $club_title ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $data['position'] ) : ?>
					<span class="mx-2">-</span>
					<div class="player-birthday-card-wide__position"><?php echo esc_html( $data['position'] ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
