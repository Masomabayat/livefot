<?php
/**
 * The Template for displaying Players with Suspension Risk.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-premium-suspension-risk.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         AnWP-Football-Leagues/Templates
 * @since           0.13.7
 *
 * @version         0.16.00
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'competition_id' => '',
		'game_id'        => '',
		'club_id'        => '',
		'class'          => '',
		'header'         => '',
		'show_links'     => '1',
		'show_photos'    => '0',
		'show_teams'     => '0',
	]
);

if ( ! absint( $args->competition_id ) && ! absint( $args->game_id ) ) {
	return;
}

$risk_data = anwp_fl_pro()->suspension->get_players_suspension_risk( $args );

if ( empty( $risk_data ) ) {
	return;
}

$show_link     = AnWP_Football_Leagues::string_to_bool( $args->show_links );
$show_photos   = AnWP_Football_Leagues::string_to_bool( $args->show_photos );
$show_teams    = AnWP_Football_Leagues::string_to_bool( $args->show_teams );
$default_photo = anwp_fl()->helper->get_default_player_photo();
?>
<div class="anwp-b-wrap anwpfl-suspension-risk">

	<?php
	if ( ! empty( $args->header ) ) :
		anwp_fl()->load_partial(
			[
				'text'  => $args->header,
				'class' => 'anwpfl-suspension-risk__header',
			],
			'general/header'
		);
	endif;
	?>

	<div class="anwpfl-suspension-risk__grid anwp-fl-border-top anwp-border-light">
		<?php
		foreach ( $risk_data as $club_id => $club_players ) :
			if ( $show_teams ) :

				$team_obj = anwp_fl_pro()->club->get_club( $club_id );

				if ( empty( $team_obj ) ) :
					continue;
				endif;
				?>
				<div class="anwpfl-suspension-risk__team d-flex align-items-center anwp-bg-light p-2 anwp-fl-border-bottom anwp-border-light">

					<?php if ( $team_obj->logo ) : ?>
						<img loading="lazy" width="40" height="40" class="anwp-flex-none anwp-object-contain mb-0 mr-2 anwpfl-suspension-risk__team-logo anwp-w-40 anwp-h-40" src="<?php echo esc_attr( $team_obj->logo ); ?>" alt="club logo">
					<?php endif; ?>

					<span class="anwpfl-suspension-risk__team-name"><?php echo esc_html( $team_obj->title ); ?></span>
				</div>
				<?php
			endif;

			foreach ( $club_players as $club_player ) :
				if ( empty( $club_player['short_name'] ) ) :
					continue;
				endif;
				?>
				<div class="anwpfl-suspension-risk__player d-flex py-2 align-items-center anwp-fl-border-bottom anwp-border-light">

					<?php if ( $show_photos ) : ?>
						<img loading="lazy" width="40" height="40" class="mb-0 anwp-object-contain anwpfl-suspension-risk__player-photo mr-2 anwp-w-40 anwp-h-40"
								src="<?php echo esc_url( $club_player['photo'] ? anwp_fl()->upload_dir . $club_player['photo'] : $default_photo ); ?>"
								alt="<?php echo esc_attr( $club_player['short_name'] ); ?>">
					<?php endif; ?>

					<div class="anwpfl-suspension-risk__player-data d-flex flex-column">
						<div class="anwpfl-suspension-risk__player-name">
							<?php if ( $show_link && ! empty( $club_player['link'] ) ) : ?>
								<a class="text-decoration-none anwp-link-without-effects" href="<?php echo esc_attr( $club_player['link'] ); ?>">
									<?php echo esc_html( $club_player['short_name'] ); ?>
								</a>
							<?php else : ?>
								<?php echo esc_html( $club_player['short_name'] ); ?>
							<?php endif; ?>
						</div>
						<div class="anwpfl-suspension-risk__player-position anwp-opacity-60 anwp-leading-1 anwp-text-xs">
							<?php echo esc_html( $club_player['position'] ); ?>
						</div>
					</div>
				</div>
				<div class="anwpfl-suspension-risk__cards d-flex align-items-center pr-2 anwp-fl-border-bottom anwp-border-light">
					<?php echo esc_html( $club_player['cards'] ); ?>
				</div>
			<?php endforeach; ?>

		<?php endforeach; ?>
	</div>
</div>
