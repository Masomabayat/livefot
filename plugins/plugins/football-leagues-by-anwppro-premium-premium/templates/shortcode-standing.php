<?php
/**
 * The Template for displaying Standing Table Shortcode.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-standing.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP-Football-Leagues/Templates
 * @since            0.3.0
 *
 * @version          0.14.18
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check for required data
if ( empty( $data->id ) || 'anwp_standing' !== get_post_type( $data->id ) ) {
	return;
}

// Prepare data
$standing_id    = (int) $data->id;
$competition_id = get_post_meta( $standing_id, '_anwpfl_competition', true );
$group_id       = get_post_meta( $standing_id, '_anwpfl_competition_group', true );

$table        = json_decode( get_post_meta( $standing_id, '_anwpfl_table_main', true ) );
$table_colors = json_decode( get_post_meta( $standing_id, '_anwpfl_table_colors', true ) );

// Check data is valid
if ( null === $table ) {
	// something went wrong
	return;
}

// Check table colors
if ( is_object( $table_colors ) ) {
	$table_colors = (array) $table_colors;
}

// Merge with default params
$data = (object) wp_parse_args(
	$data,
	[
		'title'          => '',
		'context'        => 'shortcode',
		'bottom_link'    => '',
		'link_text'      => '',
		'partial'        => '',
		'wrapper_class'  => '',
		'show_home_away' => '',
		'filtered_data'  => '',
		'matchweeks'     => '',
		'show_notes'     => 1,
	]
);

$table_notes = AnWP_Football_Leagues::string_to_bool( $data->show_notes ) ? get_post_meta( $standing_id, '_anwpfl_table_notes', true ) : '';

/**
 * Filter: anwpfl/tmpl-standing/columns_order
 *
 * @since 0.7.5
 *
 * @param array
 * @param object  $standing_id
 * @param string  $layout
 * @param integer $competition_id
 * @param integer $group_id
 */
$columns_order = apply_filters(
	'anwpfl/tmpl-standing/columns_order',
	[ 'played', 'won', 'drawn', 'lost', 'gf', 'ga', 'gd', 'points' ],
	$standing_id,
	'',
	$competition_id,
	$group_id
);

$columns_order_sm = apply_filters(
	'anwpfl/tmpl-standing/columns_order',
	[ 'played', 'won', 'drawn', 'lost', 'gd', 'points' ],
	$standing_id,
	'default-sm',
	$competition_id,
	$group_id
);

$columns_order_xs = apply_filters(
	'anwpfl/tmpl-standing/columns_order',
	[ 'played', 'points' ],
	$standing_id,
	'default-xs',
	$competition_id,
	$group_id
);

// Prepare data
$column_header = anwp_football_leagues()->data->get_standing_headers();

// Conference support
$conferences_list    = [ '' ];
$conferences_support = AnWP_Football_Leagues::string_to_bool( get_post_meta( $standing_id, '_anwpfl_conferences_support', true ) );

if ( $conferences_support ) {
	$club_conferences = json_decode( get_post_meta( $standing_id, '_anwpfl_club_conferences', true ) );

	$conferences_list = array_filter( array_unique( array_values( (array) $club_conferences ) ) );
}

$exclude_ids = [];
if ( ! empty( $data->exclude_ids ) ) {
	$exclude_ids = array_map( 'absint', explode( ',', $data->exclude_ids ) );
}

// Get club form (series)
$club_ids        = wp_list_pluck( $table, 'club_id' );
$clubs_form      = anwp_football_leagues_premium()->standing->get_clubs_form( $club_ids, $standing_id );
$clubs_form_next = anwp_football_leagues_premium()->standing->get_clubs_form_next( $club_ids, $standing_id );

// Slice table if partial option is set
if ( $data->partial ) {
	$table = anwp_football_leagues()->standing->get_standing_partial_data( $table, $data->partial );
}

$arrows_data = anwp_football_leagues_premium()->standing->get_arrows_data( $standing_id );

/*
|--------------------------------------------------------------------
| Show Home/Away
|--------------------------------------------------------------------
*/
$show_home_away_data = 'no' !== anwp_football_leagues_premium()->customizer->get_value( 'standing', 'show_home_away' );

if ( ! $show_home_away_data && 'yes' === $data->show_home_away ) {
	$show_home_away_data = true;
}

if ( $conferences_support || $data->partial || 'no' === $data->show_home_away ) {
	$show_home_away_data = false;
}

$load_filtered_data = in_array( $data->filtered_data, [ 'home', 'away' ], true ) && ! ( $conferences_support || $data->partial );
?>
<div class="anwp-b-wrap standing standing--shortcode standing__inner standing-<?php echo absint( $standing_id ); ?> context--<?php echo esc_attr( $data->context ); ?> <?php echo esc_attr( $data->wrapper_class ); ?>">

	<?php if ( $data->title ) : ?>
		<h4 class="standing__title"><?php echo esc_html( $data->title ); ?></h4>
	<?php endif; ?>

	<?php if ( $show_home_away_data ) : ?>
		<div class="d-inline-flex my-2 anwp-user-select-none anwp-btn-group anwp-fl-standing-filter" data-id="<?php echo absint( $standing_id ); ?>" data-matchweeks="<?php echo esc_attr( $data->matchweeks ); ?>">
			<div class="<?php echo $load_filtered_data ? 'anwp-bg-gray-light' : 'anwp-is-active'; ?> anwp-border anwp-border-light anwp-btn-group__btn px-3 anwp-fl-standing-filter__action" data-filter="all">
				<?php echo esc_html( AnWPFL_Text::get_value( 'standing__filter__all', _x( 'All', 'standing', 'anwp-football-leagues-premium' ) ) ); ?>
			</div>
			<div class="<?php echo $load_filtered_data && 'home' === $data->filtered_data ? 'anwp-is-active' : 'anwp-bg-gray-light'; ?> anwp-border anwp-border-light anwp-btn-group__btn px-3 anwp-fl-standing-filter__action" data-filter="home">
				<?php echo esc_html( AnWPFL_Text::get_value( 'standing__filter__home', _x( 'Home', 'standing', 'anwp-football-leagues-premium' ) ) ); ?>
			</div>
			<div class="<?php echo $load_filtered_data && 'away' === $data->filtered_data ? 'anwp-is-active' : 'anwp-bg-gray-light'; ?> anwp-border anwp-border-light anwp-btn-group__btn px-3 anwp-fl-standing-filter__action" data-filter="away">
				<?php echo esc_html( AnWPFL_Text::get_value( 'standing__filter__away', _x( 'Away', 'standing', 'anwp-football-leagues-premium' ) ) ); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	foreach ( $conferences_list as $conference_title ) :
		$conference_place = 1;

		if ( $conference_title ) :
			?>
			<div class="standing__conference-title mt-3 mb-1 anwp-text-lg">
				<?php echo esc_html( $conference_title ); ?>
			</div>
			<?php
		endif;

		if ( $load_filtered_data || $data->matchweeks ) :
			$table_data = json_decode( get_post_meta( $standing_id, '_anwpfl_table_main_' . sanitize_key( $data->filtered_data . '_' . $data->matchweeks ), true ) );

			if ( empty( $table_data ) ) :
				$table_data = anwp_football_leagues_premium()->standing->calculate_standing_optional( $standing_id, $data->filtered_data, $data->matchweeks );
			endif;

			if ( ! empty( $table_data ) ) :
				anwp_football_leagues()->load_partial(
					[
						'table'      => $table_data,
						'id'         => $standing_id,
						'type'       => $data->filtered_data,
						'matchweeks' => $data->matchweeks,
					],
					'standing/standing'
				);
			endif;
		else :
			?>
			<div class="standing-table anwp-grid-table anwp-grid-table--aligned anwp-grid-table--bordered anwp-text-sm anwp-border-light <?php echo esc_attr( 'yes' === anwp_football_leagues_premium()->customizer->get_value( 'standing', 'standing_font_mono' ) ? 'standing-text-mono' : '' ); ?>"
					style="--standing-cols: <?php echo count( $columns_order ); ?>; --standing-cols-sm: <?php echo count( $columns_order_sm ); ?>; --standing-cols-xs: <?php echo count( $columns_order_xs ); ?>;">

				<div class="anwp-grid-table__th anwp-border-light standing-table__rank justify-content-center anwp-bg-light">
					#
				</div>
				<div class="anwp-grid-table__th anwp-border-light standing-table__club anwp-bg-light">
					<?php echo esc_html( AnWPFL_Text::get_value( 'standing__shortcode__club', __( 'Club', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<?php
				foreach ( $columns_order as $col ) :
					$classes  = ! in_array( $col, $columns_order_sm, true ) ? 'anwp-grid-table__sm-none' : '';
					$classes .= ! in_array( $col, $columns_order_xs, true ) ? ' anwp-grid-table__xs-none' : '';
					?>
					<div class="anwp-grid-table__th anwp-border-light standing-table__<?php echo esc_attr( $col ); ?> justify-content-center anwp-bg-light <?php echo esc_attr( $classes ); ?>"
							data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_html( empty( $column_header[ $col ]['tooltip'] ) ? '' : $column_header[ $col ]['tooltip'] ); ?>">
						<?php echo esc_html( empty( $column_header[ $col ]['text'] ) ? '' : $column_header[ $col ]['text'] ); ?>
					</div>
				<?php endforeach; ?>

				<?php
				foreach ( $table as $row ) :

					if ( in_array( (int) $row->club_id, $exclude_ids, true ) ) {
						continue;
					}

					if ( $conferences_support && ! empty( $club_conferences->{$row->club_id} ) && $club_conferences->{$row->club_id} !== $conference_title ) {
						continue;
					}

					// Prepare Color Class
					$color_class = '';
					$color_style = '';

					if ( $conferences_support && ! empty( $table_colors[ 'p' . $conference_place ] ) ) {
						if ( '#' === mb_substr( $table_colors[ 'p' . $conference_place ], 0, 1 ) ) {
							$color_style = 'background-color: ' . esc_attr( $table_colors[ 'p' . $conference_place ] );
						} else {
							$color_class = 'anwp-bg-' . $table_colors[ 'p' . $conference_place ] . '-light';
						}
					} elseif ( ! empty( $table_colors[ 'p' . $row->place ] ) ) {
						if ( '#' === mb_substr( $table_colors[ 'p' . $row->place ], 0, 1 ) ) {
							$color_style = 'background-color: ' . esc_attr( $table_colors[ 'p' . $row->place ] );
						} else {
							$color_class = 'anwp-bg-' . $table_colors[ 'p' . $row->place ] . '-light';
						}
					}

					if ( ! empty( $table_colors[ 'c' . $row->club_id ] ) ) {
						if ( '#' === mb_substr( $table_colors[ 'c' . $row->club_id ], 0, 1 ) ) {
							$color_style = 'background-color: ' . esc_attr( $table_colors[ 'c' . $row->club_id ] );
						} else {
							$color_class = 'anwp-bg-' . $table_colors[ 'c' . $row->club_id ] . '-light';
						}
					}

					$series = str_split( substr( $row->series, - 5 ) );

					$club_title = anwp_football_leagues()->club->get_club_title_by_id( $row->club_id );
					$club_logo  = anwp_football_leagues()->club->get_club_logo_by_id( $row->club_id );
					$club_link  = anwp_football_leagues()->club->get_club_link_by_id( $row->club_id );

					$club_classes = 'club-' . (int) $row->club_id . ' place-' . (int) $row->place;
					?>

					<div class="anwp-grid-table__td standing-table__rank standing-table__cell-number justify-content-center <?php echo esc_attr( $club_classes ); ?> <?php echo esc_attr( $color_class ); ?>"
							style="<?php echo esc_attr( $color_style ); ?>">
						<?php echo esc_html( $conferences_support ? (int) $conference_place ++ : $row->place ); ?>
						<?php if ( isset( $arrows_data[ $row->club_id ] ) && absint( $arrows_data[ $row->club_id ] ) !== absint( $row->place ) ) : ?>
							<span class="standing-table__arrow standing-table__arrow-<?php echo $arrows_data[ $row->club_id ] > $row->place ? 'up' : 'down'; ?>">
							<?php echo $arrows_data[ $row->club_id ] > $row->place ? '▲' : '▼'; ?>
						</span>
						<?php endif; ?>
					</div>

					<div class="anwp-grid-table__td standing-table__club anwp-overflow-hidden <?php echo esc_attr( $club_classes ); ?>">
						<?php if ( $club_logo ) : ?>
							<img loading="lazy" width="25" height="25" class="anwp-object-contain mr-2 anwp-w-25 anwp-h-25"
									src="<?php echo esc_url( $club_logo ); ?>"
									alt="<?php echo esc_attr( $club_title ); ?>">
						<?php endif; ?>

						<div class="d-flex flex-column">
							<a class="club__link anwp-link anwp-link-without-effects" href="<?php echo esc_url( $club_link ); ?>">
								<?php echo esc_html( $club_title ); ?>
							</a>

							<div class="d-none anwp-grid-table__sm-flex mt-1">
								<?php
								if ( $row->series ) :
									for ( $ii = 0; $ii < 5; $ii ++ ) :
										$class = 'anwp-bg-secondary';
										if ( ! empty( $series[ $ii ] ) ) {
											$class = 'w' === $series[ $ii ] ? 'anwp-bg-success' : ( 'd' === $series[ $ii ] ? 'anwp-bg-warning' : 'anwp-bg-danger' );
										}
										?>
										<div class="standing-table__mini-cell-form d-inline-block anwp-w-10 anwp-opacity-80 <?php echo esc_attr( $class ); ?>"></div>
										<?php
									endfor;
								endif;
								?>
							</div>
						</div>

						<div class="anwp-text-xs d-flex anwp-grid-table__sm-none align-items-center standing-table__cell-form-wrapper ml-auto">
							<?php
							if ( ! empty( $clubs_form[ $row->club_id ] ) ) {
								echo $clubs_form[ $row->club_id ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}

							if ( ! empty( $clubs_form_next[ $row->club_id ] ) ) {
								echo $clubs_form_next[ $row->club_id ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>
					</div>

					<?php
					foreach ( $columns_order as $col ) :
						$classes  = ! in_array( $col, $columns_order_sm, true ) ? 'anwp-grid-table__sm-none' : '';
						$classes .= ! in_array( $col, $columns_order_xs, true ) ? ' anwp-grid-table__xs-none' : '';
						?>
						<div class="anwp-grid-table__td justify-content-center standing-table__cell-number standing-table__<?php echo esc_attr( $col ); ?> <?php echo esc_attr( $classes ); ?> <?php echo esc_attr( $club_classes ); ?>">
							<?php if ( 'played' === $col && 'no' !== AnWPFL_Premium_Options::get_value( 'standing_click_show_matches' ) ) : ?>
								<a href="#anwp-modaal-games-list" data-club="<?php echo esc_attr( $row->club_id ); ?>" data-standing="<?php echo esc_attr( $standing_id ); ?>" class="anwp-modaal-games-list" style="color: inherit; text-decoration: underline;"><?php echo esc_html( $row->{$col} ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $row->{$col} ); ?>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</div>
			<?php
		endif;
	endforeach;

	if ( $table_notes ) :
		?>
		<div class="standing-table__notes mt-2 anwp-text-xs">
			<?php echo wp_kses_post( anwp_football_leagues()->standing->prepare_table_notes( $table_notes, $table_colors ) ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $data->bottom_link ) ) : ?>
		<div class="standing-table__competition-link mt-2 anwp-text-xs">
			<?php
			if ( 'competition' === $data->bottom_link ) :
				$link_competition_id = anwp_football_leagues()->competition->get_main_competition_id( $competition_id );
				?>
				<a href="<?php echo esc_url( get_permalink( $link_competition_id ) ); ?>"><?php echo esc_html( $data->link_text ? $data->link_text : get_post( $link_competition_id )->post_title ); ?></a>
			<?php elseif ( 'standing' === $data->bottom_link ) : ?>
				<a href="<?php echo esc_url( get_permalink( $standing_id ) ); ?>"><?php echo esc_html( $data->link_text ? $data->link_text : get_the_title( $standing_id ) ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
