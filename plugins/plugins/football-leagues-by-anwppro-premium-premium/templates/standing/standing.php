<?php
/**
 * The Template for displaying Standing Table part.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/standing/standing.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP-Football-Leagues/Templates
 * @since            0.14.18
 *
 * @version          0.14.18
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Merge with default params
$data = (object) wp_parse_args(
	$data,
	[
		'table'       => '',
		'id'          => '',
		'exclude_ids' => '',
		'type'        => 'all',
		'matchweeks'  => '',
	]
);

$table = $data->table;

// Check data is valid
if ( empty( $table ) ) {
	// something went wrong
	return;
}

$standing_id  = (int) $data->id;
$table_colors = json_decode( get_post_meta( $standing_id, '_anwpfl_table_colors', true ) );

$competition_id = get_post_meta( $standing_id, '_anwpfl_competition', true );
$group_id       = get_post_meta( $standing_id, '_anwpfl_competition_group', true );

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

// Check table colors
if ( is_object( $table_colors ) ) {
	$table_colors = (array) $table_colors;
}

// Prepare data
$column_header = anwp_football_leagues()->data->get_standing_headers();

$exclude_ids = [];
if ( ! empty( $data->exclude_ids ) ) {
	$exclude_ids = array_map( 'absint', explode( ',', $data->exclude_ids ) );
}

// Get club form (series)
$club_ids        = wp_list_pluck( $table, 'club_id' );
$clubs_form      = anwp_football_leagues_premium()->standing->get_clubs_form( $club_ids, $standing_id, $data->type, $data->matchweeks );
$clubs_form_next = $data->matchweeks ? [] : anwp_football_leagues_premium()->standing->get_clubs_form_next( $club_ids, $standing_id, $data->type );
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
		<?php
	endforeach;

	foreach ( $table as $row ) :

		if ( in_array( (int) $row->club_id, $exclude_ids, true ) ) {
			continue;
		}

		// Prepare Color Class
		$color_class = '';
		$color_style = '';

		if ( ! empty( $table_colors[ 'p' . $row->place ] ) ) {
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
			<?php echo esc_html( $row->place ); ?>
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
				<?php echo esc_html( $row->{$col} ); ?>
			</div>
			<?php
		endforeach;
	endforeach;
	?>
</div>
