<?php
/**
 * The Template for displaying Standing Table Shortcode. Layout "mini". Used for widget.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-standing--mini.php.
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

if ( ! empty( $data->context ) && 'widget' === $data->context ) {
	$data->id = $data->standing;
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
		'title'       => '',
		'bottom_link' => '',
		'link_text'   => '',
		'partial'     => '',
		'show_notes'  => 1,
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
	[ 'played', 'won', 'drawn', 'lost', 'points' ],
	$standing_id,
	'mini',
	$competition_id,
	$group_id
);

$columns_order_sm = apply_filters(
	'anwpfl/tmpl-standing/columns_order',
	[ 'played', 'points' ],
	$standing_id,
	'mini-sm',
	$competition_id,
	$group_id
);

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

// Slice table if partial option is set
if ( $data->partial ) {
	$table = anwp_football_leagues()->standing->get_standing_partial_data( $table, $data->partial );
}

$arrows_data = anwp_football_leagues_premium()->standing->get_arrows_data( $standing_id );
?>

<div class="anwp-b-wrap standing standing--widget standing__inner competition-<?php echo (int) $competition_id; ?> standing-<?php echo (int) $standing_id; ?> context--<?php echo esc_attr( empty( $data->context ) ? '' : $data->context ); ?>">

	<?php if ( $data->title ) : ?>
		<h4 class="standing__title"><?php echo esc_html( $data->title ); ?></h4>
	<?php endif; ?>

	<?php
	foreach ( $conferences_list as $conference_title ) :
		$conference_place = 1;
		?>

		<?php if ( $conference_title ) : ?>
			<div class="standing__conference-title mt-3 mb-1 anwp-text-lg">
				<?php echo esc_html( $conference_title ); ?>
			</div>
		<?php endif; ?>

	<div class="anwp-b-wrap anwpfl-not-ready standing-table-mini anwp-grid-table anwp-grid-table--aligned anwp-grid-table--bordered anwp-text-sm anwp-border-light <?php echo esc_attr( 'yes' === anwp_football_leagues_premium()->customizer->get_value( 'standing', 'standing_font_mono' ) ? 'standing-text-mono' : '' ); ?>"
		style="--standing-cols: <?php echo count( $columns_order ); ?>; --standing-cols-sm: <?php echo count( $columns_order_sm ); ?>;">

		<div class="anwp-grid-table__th anwp-border-light standing-table-mini__rank justify-content-center anwp-bg-light">
			#
		</div>

		<div class="anwp-grid-table__th anwp-border-light standing-table-mini__club anwp-bg-light">
			<?php echo esc_html( AnWPFL_Text::get_value( 'standing__shortcode__club', __( 'Club', 'anwp-football-leagues' ) ) ); ?>
		</div>

		<?php foreach ( $columns_order as $col ) : ?>
			<?php $classes = ! in_array( $col, $columns_order_sm, true ) ? 'anwp-grid-table__sm-none' : ''; ?>
			<div class="anwp-grid-table__th anwp-border-light standing-table-mini__<?php echo esc_attr( $col ); ?> justify-content-center anwp-bg-light <?php echo esc_attr( $classes ); ?>"
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

				$club_classes = 'club-' . (int) $row->club_id . ' place-' . (int) $row->place;
				?>

				<div class="anwp-grid-table__td standing-table-mini__rank standing-table__cell-number justify-content-center <?php echo esc_attr( $club_classes ); ?> <?php echo esc_attr( $color_class ); ?>"
					style="<?php echo esc_attr( $color_style ); ?>">
					<?php echo esc_html( $conferences_support ? (int) $conference_place ++ : $row->place ); ?>
					<?php if ( isset( $arrows_data[ $row->club_id ] ) && absint( $arrows_data[ $row->club_id ] ) !== absint( $row->place ) ) : ?>
						<span class="standing-table-mini__arrow standing-table-mini__arrow-<?php echo $arrows_data[ $row->club_id ] > $row->place ? 'up' : 'down'; ?>">
							<?php echo $arrows_data[ $row->club_id ] > $row->place ? '▲' : '▼'; ?>
						</span>
					<?php endif; ?>
				</div>

				<div class="anwp-grid-table__td standing-table-mini__club anwp-overflow-hidden <?php echo esc_attr( $club_classes ); ?>">
					<?php
					$club_title = 'no' !== anwp_football_leagues_premium()->customizer->get_value( 'standing', 'use_abbr_in_standing_mini' ) ? anwp_football_leagues()->club->get_club_abbr_by_id( $row->club_id ) : anwp_football_leagues()->club->get_club_title_by_id( $row->club_id );
					$club_logo  = anwp_football_leagues()->club->get_club_logo_by_id( $row->club_id );
					$club_link  = anwp_football_leagues()->club->get_club_link_by_id( $row->club_id );
					?>

					<?php if ( $club_logo ) : ?>
						<img loading="lazy" width="25" height="25" class="anwp-object-contain mr-2 anwp-w-25 anwp-h-25"
							src="<?php echo esc_url( $club_logo ); ?>"
							alt="<?php echo esc_attr( $club_title ); ?>">
					<?php endif; ?>

					<a class="club__link anwp-link anwp-link-without-effects" href="<?php echo esc_url( $club_link ); ?>">
						<?php echo esc_html( $club_title ); ?>
					</a>
				</div>

				<?php foreach ( $columns_order as $col ) : ?>
					<?php $classes = ! in_array( $col, $columns_order_sm, true ) ? 'anwp-grid-table__sm-none' : '' ?>
					<div class="anwp-grid-table__td justify-content-center standing-table-mini__cell-number standing-table-mini__<?php echo esc_attr( $col ); ?> <?php echo esc_attr( $classes ); ?> <?php echo esc_attr( $club_classes ); ?>">
						<?php if ( 'played' === $col && 'no' !== AnWPFL_Premium_Options::get_value( 'standing_click_show_matches' ) ) : ?>
							<a href="#anwp-modaal-games-list" data-club="<?php echo esc_attr( $row->club_id ); ?>" data-standing="<?php echo esc_attr( $standing_id ); ?>" class="anwp-modaal-games-list" style="color: inherit; text-decoration: underline;"><?php echo esc_html( $row->{$col} ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $row->{$col} ); ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

	<?php if ( $table_notes ) : ?>
		<div class="standing-table__notes mt-2 anwp-text-xs">
			<?php echo wp_kses_post( anwp_football_leagues()->standing->prepare_table_notes( $table_notes, $table_colors ) ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $data->bottom_link ) ) : ?>
		<div class="standing-table__competition-link mt-2">
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
