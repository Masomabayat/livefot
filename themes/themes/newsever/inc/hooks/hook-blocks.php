<?php
if ( ! function_exists( 'newsever_archive_layout_selection' ) ) :
	/**
	 *
	 * @param null
	 *
	 * @return null
	 *
	 * @since Newsever 1.0.0
	 *
	 */
	function newsever_archive_layout_selection( $archive_layout = 'list' ) {

        newsever_get_block( 'list', 'archive' );

	}
endif;


if ( ! function_exists( 'newsever_archive_layout' ) ) :
	/**
	 *
	 * @param null
	 *
	 * @return null
	 *
	 * @since Newsever 1.0.0
	 *
	 */
	function newsever_archive_layout( $cat_slug = '' ) {

		//$archive_class = newsever_get_option('archive_layout');

		$archive_args = newsever_archive_layout_class( $cat_slug );

		?>

		<?php if ( ! empty( $archive_args['data_mh'] ) ): ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( $archive_args['add_archive_class'] ); ?>
                     data-mh="<?php echo esc_attr( $archive_args['data_mh'] ); ?>">
				<?php newsever_archive_layout_selection( $archive_args['archive_layout'] ); ?>
            </article>
		<?php else: ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( $archive_args['add_archive_class'] ); ?> >
				<?php newsever_archive_layout_selection( $archive_args['archive_layout'] ); ?>
            </article>
		<?php endif; ?>

		<?php

	}

	add_action( 'newsever_action_archive_layout', 'newsever_archive_layout', 10, 1 );
endif;

function newsever_archive_layout_class( $cat_slug ) {

	if ( is_category() || !empty($cat_slug)) {

		$term_meta      = '';
		$term_meta_list = '';
		$term_meta_grid = '';
		if ( ! empty( $cat_slug ) ) {
			$ajax_term = get_term_by( 'slug', $cat_slug, 'category' );
			$t_id      = $ajax_term->term_id;
		} else {
			$queried_object = get_queried_object();
			$t_id           = $queried_object->term_id;

		}

		$term_meta      = get_option( "category_layout_$t_id" );
		$term_meta_list = get_option( "category_layout_list_$t_id" );
		$term_meta_grid = get_option( "category_layout_grid_$t_id" );

		$archive_args = array();


		if ( ! empty( $term_meta ) ) {
			$archive_class = $term_meta['archive_layout_term_meta'];
		} else {
			$archive_class = newsever_get_option( 'archive_layout' );
		}

		if ( ! empty( $term_meta_list ) ) {
			$archive_layout_list = $term_meta_list['archive_layout_alignment_term_meta_list'];

		} else {

			$archive_layout_list = newsever_get_option( 'archive_image_alignment_list' );

		}

		if ( ! empty( $term_meta_grid ) ) {
			$archive_layout_grid = $term_meta_grid['archive_layout_alignment_term_meta_gird'];
		} else {
			$archive_layout_grid = newsever_get_option( 'archive_image_alignment_grid' );
		}

	} else {

		$archive_class       = newsever_get_option( 'archive_layout' );
		$archive_layout_list = newsever_get_option( 'archive_image_alignment_list' );


	}

    $archive_args['archive_layout']    = 'archive-layout-list';
    $archive_args['add_archive_class'] = 'latest-posts-list col-1 float-l pad';
    $archive_args['data_mh']           = '';
    //$image_align_class = newsever_get_option('archive_image_alignment');
    $image_align_class                 = $archive_layout_list;
    $archive_args['add_archive_class'] .= ' ' . $archive_class . ' ' . $image_align_class;

	return $archive_args;

}


//Archive div wrap before loop

if ( ! function_exists( 'newsever_archive_layout_before_loop' ) ) :

	/**
	 *
	 * @param null
	 *
	 * @return null
	 *
	 * @since Newsever 1.0.0
	 *
	 */

	function newsever_archive_layout_before_loop() {

		if ( is_category() ) {
			$archive_class  = '';
			$archive_mode   = newsever_get_option( 'archive_layout' );
			$queried_object = get_queried_object();
			$t_id           = $queried_object->term_id;
			$term_meta      = get_option( "category_layout_$t_id" );


			if ( ! empty( $term_meta ) ) {
				$term_meta = $term_meta['archive_layout_term_meta'];
				if ( $term_meta == 'archive-layout-masonry' ) {
					$archive_class = 'aft-masonry-archive-posts';
				} else {
					$archive_class = $term_meta;
				}
			} else {
				if ( $archive_mode == 'archive-layout-masonry' ) {
					$archive_class = 'aft-masonry-archive-posts';
				} else {

					$archive_class = $archive_mode;
				}
			}
		} else {
			$archive_mode = newsever_get_option( 'archive_layout' );
			if ( $archive_mode == 'archive-layout-masonry' ) {
				$archive_class = 'aft-masonry-archive-posts';
			} else {

				$archive_class = $archive_mode;
			}
		}
		?>
        <div class="af-container-row aft-archive-wrapper clearfix <?php echo esc_attr( $archive_class ); ?>">
		<?php

	}

	add_action( 'newsever_archive_layout_before_loop', 'newsever_archive_layout_before_loop' );
endif;

if ( ! function_exists( 'newsever_archive_layout_after_loop' ) ):

	function newsever_archive_layout_after_loop() {
		?>
        </div>
	<?php }

	add_action( 'newsever_archive_layout_after_loop', 'newsever_archive_layout_after_loop' );

endif;
