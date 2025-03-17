<?php
/**
 * The Template for displaying H2H Team Stats.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-stats-h2h.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         AnWP-Football-Leagues/Templates
 * @since           0.12.2
 *
 * @version         0.14.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$args = (object) wp_parse_args(
	$data,
	[
		'competition_id' => '',
		'season_id'      => '',
		'league_id'      => '',
		'multistage'     => 0,
		'club_a'         => '',
		'club_b'         => '',
		'date_before'    => '',
		'date_after'     => '',
		'h2h_only'       => 1,
		'highlight_best' => 1,
		'per_game'       => 1,
		'stats'          => '',
		'class'          => '',
		'header'         => '',
		'caching_time'   => '',
	]
);

if ( ! absint( $args->club_a ) || ! absint( $args->club_b ) ) {
	return;
}

$args->stats = wp_parse_slug_list( $args->stats );
$h2h_only    = AnWP_Football_Leagues::string_to_bool( $args->h2h_only );
$per_game    = AnWP_Football_Leagues::string_to_bool( $args->per_game );
$stats_data  = anwp_football_leagues_premium()->club->get_h2h_clubs_stats( $args, $h2h_only );

if ( empty( $stats_data ) ) {
	return;
}

// Team Logo
$logo_default = anwp_football_leagues()->helper->get_default_club_logo();
$logo_home    = anwp_football_leagues()->club->get_club_logo_by_id( $args->club_a, false );
$logo_away    = anwp_football_leagues()->club->get_club_logo_by_id( $args->club_b, false );

// Highlight best value
$highlight = AnWP_Football_Leagues::string_to_bool( $args->highlight_best ) ? 'anwp-fl-td-highlight' : '';

// Text Strings
$text_strings = [
	'total'          => AnWPFL_Text::get_value( 'stats__h2h__total', __( 'Total', 'anwp-football-leagues' ) ),
	'per_match'      => AnWPFL_Text::get_value( 'stats__h2h__per_match', __( 'Per Match', 'anwp-football-leagues' ) ),
	'wins'           => AnWPFL_Text::get_value( 'stats__h2h__wins', __( 'Wins', 'anwp-football-leagues' ) ),
	'draws'          => AnWPFL_Text::get_value( 'stats__h2h__draws', __( 'Draws', 'anwp-football-leagues-premium' ) ),
	'losses'         => AnWPFL_Text::get_value( 'stats__h2h__losses', __( 'Losses', 'anwp-football-leagues-premium' ) ),
	'played'         => AnWPFL_Text::get_value( 'stats__h2h__played', __( 'Played', 'anwp-football-leagues' ) ),
	'corners'        => AnWPFL_Text::get_value( 'stats__h2h__corners', __( 'Corners', 'anwp-football-leagues' ) ),
	'fouls'          => AnWPFL_Text::get_value( 'stats__h2h__fouls', __( 'Fouls', 'anwp-football-leagues' ) ),
	'offsides'       => AnWPFL_Text::get_value( 'stats__h2h__offsides', __( 'Offsides', 'anwp-football-leagues' ) ),
	'shots'          => AnWPFL_Text::get_value( 'stats__h2h__shots', __( 'Shots', 'anwp-football-leagues' ) ),
	'shots_on_goal'  => AnWPFL_Text::get_value( 'stats__h2h__shots_on_goal', __( 'Shots on Goal', 'anwp-football-leagues' ) ),
	'cards_y'        => AnWPFL_Text::get_value( 'stats__h2h__cards_y', __( 'Yellow Cards', 'anwp-football-leagues' ) ),
	'cards_r'        => AnWPFL_Text::get_value( 'stats__h2h__cards_r', __( 'Red Cards', 'anwp-football-leagues' ) ),
	'goals'          => AnWPFL_Text::get_value( 'stats__h2h__goals', __( 'Goals', 'anwp-football-leagues' ) ),
	'goals_conceded' => AnWPFL_Text::get_value( 'stats__h2h__goals_conceded', __( 'Goals Conceded', 'anwp-football-leagues' ) ),
	'clean_sheets'   => AnWPFL_Text::get_value( 'stats__h2h__clean_sheets', __( 'Clean Sheets', 'anwp-football-leagues' ) ),
];
?>
<div class="anwp-b-wrap">

	<?php
	if ( ! empty( $args->header ) ) :
		anwp_football_leagues()->load_partial(
			[
				'text' => $args->header,
			],
			'general/header'
		);
	endif;
	?>

	<div class="<?php echo esc_attr( $args->class ?: 'anwp-fl-border anwp-border-light' ); ?>">
		<div class="d-flex">
			<div class="anwp-flex-1 match__club--mini p-2 d-flex align-items-center anwp-bg-light">
				<img loading="lazy" width="40" height="40" class="anwp-flex-none anwp-object-contain mb-0 anwp-w-40 anwp-h-40" src="<?php echo esc_attr( $logo_home ? : $logo_default ); ?>" alt="club logo">
				<div class="match__club mx-2 anwp-text-truncate-multiline"><?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $args->club_a ) ); ?></div>
			</div>
			<div class="anwp-flex-1 match__club--mini p-2 d-flex align-items-center anwp-bg-light flex-row-reverse">
				<img loading="lazy" width="40" height="40" class="anwp-flex-none anwp-object-contain mb-0 anwp-w-40 anwp-h-40" src="<?php echo esc_attr( $logo_away ? : $logo_default ); ?>">
				<div class="match__club mx-2 anwp-text-truncate-multiline"><?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $args->club_b ) ); ?></div>
			</div>
		</div>
		<div class="d-flex py-1 anwp-text-center">
			<?php if ( $h2h_only ) : ?>
				<div class="anwp-flex-1">
					<?php echo esc_html( $text_strings['played'] ); ?>:
					<span class="anwp-font-semibold anwp-text-base"><?php echo esc_html( $stats_data['h']['played'] ); ?></span>
				</div>
			<?php else : ?>
				<div class="anwp-flex-1 anwp-font-semibold anwp-text-base"><?php echo esc_html( $stats_data['h']['played'] ); ?></div>
				<div class="anwp-flex-1"><?php echo esc_html( $text_strings['played'] ); ?></div>
				<div class="anwp-flex-1 anwp-font-semibold anwp-text-base"><?php echo esc_html( $stats_data['a']['played'] ); ?></div>
			<?php endif; ?>
		</div>

		<?php if ( $h2h_only ) : ?>
			<div class="d-flex anwp-text-center anwp-bg-light">
				<div class="anwp-flex-1 py-1 <?php echo ( $highlight && $stats_data['h']['wins'] > $stats_data['a']['wins'] ) ? esc_attr( $highlight ) : ''; ?>">
					<?php echo esc_html( $text_strings['wins'] ); ?><br>
					<span class="anwp-font-semibold anwp-text-base"><?php echo esc_html( $stats_data['h']['wins'] ); ?></span>
				</div>
				<div class="anwp-flex-1 py-1">
					<?php echo esc_html( $text_strings['draws'] ); ?><br>
					<span class="anwp-font-semibold anwp-text-base"><?php echo esc_html( $stats_data['h']['draws'] ); ?></span>
				</div>
				<div class="anwp-flex-1 py-1 <?php echo ( $highlight && $stats_data['a']['wins'] > $stats_data['h']['wins'] ) ? esc_attr( $highlight ) : ''; ?>">
					<?php echo esc_html( $text_strings['wins'] ); ?><br>
					<span class="anwp-font-semibold anwp-text-base"><?php echo esc_html( $stats_data['a']['wins'] ); ?></span>
				</div>
			</div>
			<?php
		else :
			foreach ( [ 'wins', 'draws', 'losses' ] as $row_index => $stat_value ) :
				$home_highlight = '';
				$away_highlight = '';

				if ( $highlight ) {
					switch ( $stat_value ) {
						case 'wins':
						case 'draws':
							$home_highlight = $stats_data['h'][ $stat_value ] > $stats_data['a'][ $stat_value ] ? $highlight : '';
							$away_highlight = $stats_data['a'][ $stat_value ] > $stats_data['h'][ $stat_value ] ? $highlight : '';
							break;

						case 'losses':
							$home_highlight = $stats_data['h'][ $stat_value ] < $stats_data['a'][ $stat_value ] ? $highlight : '';
							$away_highlight = $stats_data['a'][ $stat_value ] < $stats_data['h'][ $stat_value ] ? $highlight : '';
							break;
					}
				}

				?>
				<div class="d-flex anwp-text-center <?php echo esc_attr( ( 0 === $row_index % 2 ) ? 'anwp-bg-light' : '' ); ?>">
					<div class="anwp-flex-1 anwp-font-semibold anwp-text-base py-1 <?php echo esc_attr( $home_highlight ); ?>">
						<?php echo esc_html( $stats_data['h'][ $stat_value ] ); ?>
					</div>
					<div class="anwp-flex-1 py-1"><?php echo esc_html( isset( $text_strings[ $stat_value ] ) ? $text_strings[ $stat_value ] : '' ); ?></div>
					<div class="anwp-flex-1 anwp-font-semibold anwp-text-base py-1 <?php echo esc_attr( $away_highlight ); ?>">
						<?php echo esc_html( $stats_data['a'][ $stat_value ] ); ?>
					</div>
				</div>
				<?php
			endforeach;
		endif;

		if ( $per_game && ! empty( $args->stats ) ) :
			?>
			<div class="py-3"></div>
			<div class="d-flex py-1 anwp-fl-border-top anwp-fl-border-bottom anwp-text-xs anwp-bg-light anwp-border-light">
				<div class="anwp-flex-1 d-flex">
					<div class="anwp-flex-1 anwp-text-center"><?php echo esc_html( $text_strings['total'] ); ?></div>
					<div class="anwp-flex-1 anwp-text-center"><?php echo esc_html( $text_strings['per_match'] ); ?></div>
				</div>
				<div class="anwp-flex-1">&nbsp;</div>
				<div class="anwp-flex-1 d-flex">
					<div class="anwp-flex-1 anwp-text-center"><?php echo esc_html( $text_strings['per_match'] ); ?></div>
					<div class="anwp-flex-1 anwp-text-center"><?php echo esc_html( $text_strings['total'] ); ?></div>
				</div>
			</div>
			<?php
		endif;

		/*
		|--------------------------------------------------------------------
		| Render default stats
		| 'corners', 'fouls', 'offsides', 'shots', 'shots_on_goal','cards_y','cards_r','goals','goals_conceded','clean_sheets'
		|--------------------------------------------------------------------
		*/
		foreach ( $args->stats as $row_index => $stat_value ) :

			if ( ! isset( $stats_data['h'][ $stat_value ] ) || ! isset( $stats_data['a'][ $stat_value ] ) ) {
				continue;
			}

			$home_highlight = '';
			$away_highlight = '';

			if ( $highlight ) {
				switch ( $stat_value ) {
					case 'corners':
					case 'shots':
					case 'goals':
					case 'shots_on_goal':
					case 'clean_sheets':
						$home_highlight = $stats_data['h'][ $stat_value ] > $stats_data['a'][ $stat_value ] ? $highlight : '';
						$away_highlight = $stats_data['a'][ $stat_value ] > $stats_data['h'][ $stat_value ] ? $highlight : '';
						break;

					case 'fouls':
					case 'offsides':
					case 'cards_y':
					case 'cards_r':
					case 'goals_conceded':
						$home_highlight = $stats_data['h'][ $stat_value ] < $stats_data['a'][ $stat_value ] ? $highlight : '';
						$away_highlight = $stats_data['a'][ $stat_value ] < $stats_data['h'][ $stat_value ] ? $highlight : '';
						break;
				}
			}

			?>
			<div class="d-flex anwp-text-center <?php echo esc_attr( ( 0 !== $row_index % 2 ) ? 'anwp-bg-light' : '' ); ?>">
				<div class="anwp-flex-1 anwp-font-semibold anwp-text-base py-1 <?php echo esc_attr( $per_game ? 'd-flex' : '' ); ?> <?php echo esc_attr( $home_highlight ); ?>">
					<?php if ( $per_game ) : ?>
						<div class="anwp-flex-1"><?php echo esc_html( $stats_data['h'][ $stat_value ] ); ?></div>
						<div class="anwp-flex-1 font-weight-normal anwp-opacity-80 anwp-text-sm">
							<?php echo esc_html( $stats_data['h'][ $stat_value ] && $stats_data['h']['played'] ? ( round( $stats_data['h'][ $stat_value ] / $stats_data['h']['played'], 1 ) ) : '' ); ?>
						</div>
					<?php else : ?>
						<?php echo esc_html( $stats_data['h'][ $stat_value ] ); ?>
					<?php endif; ?>
				</div>
				<div class="anwp-flex-1 py-1"><?php echo esc_html( isset( $text_strings[ $stat_value ] ) ? $text_strings[ $stat_value ] : '' ); ?></div>
				<div class="anwp-flex-1 anwp-font-semibold anwp-text-base py-1 <?php echo esc_attr( $per_game ? 'd-flex' : '' ); ?> <?php echo esc_attr( $away_highlight ); ?>">
					<?php if ( $per_game ) : ?>
						<div class="anwp-flex-1 font-weight-normal anwp-opacity-80 anwp-text-sm">
							<?php echo esc_html( $stats_data['a'][ $stat_value ] && $stats_data['a']['played'] ? ( round( $stats_data['a'][ $stat_value ] / $stats_data['a']['played'], 1 ) ) : '' ); ?>
						</div>
						<div class="anwp-flex-1"><?php echo esc_html( $stats_data['a'][ $stat_value ] ); ?></div>
					<?php else : ?>
						<?php echo esc_html( $stats_data['a'][ $stat_value ] ); ?>
					<?php endif; ?>
				</div>
			</div>
			<?php
		endforeach;
		?>
	</div>
</div>
