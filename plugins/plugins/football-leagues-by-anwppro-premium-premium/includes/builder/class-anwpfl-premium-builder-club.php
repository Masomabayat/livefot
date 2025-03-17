<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnWP Football Leagues Premium :: Builder :: Club
 */
class AnWPFL_Premium_Builder_Club implements AnWPFL_Premium_Builder_Interface {

	/**
	 * Get builder type name.
	 *
	 * @return string
	 */
	public function get_builder_type_name(): string {
		return esc_html__( 'Club', 'anwp-football-leagues' );
	}

	/**
	 * Rendering admin list content (builder type).
	 *
	 * @param int $post_id
	 */
	public function admin_list_column_display( int $post_id ) {}

	/**
	 * Get tutorial link.
	 *
	 * @return string
	 */
	public function get_tutorial_link(): string {
		return 'https://anwppro.userecho.com/knowledge-bases/2/articles/540-club-available-block-types';
	}

	/**
	 * Get builder type option.
	 *
	 * @return string
	 */
	public function get_builder_type_option(): string {
		return '<option value="club">' . esc_html__( 'Club', 'anwp-football-leagues' ) . '</option>';
	}

	/**
	 * Get builder wrapper classes.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_builder_wrapper_classes( WP_Post $post ) {
		return ' club club__inner club-' . absint( $post->ID );
	}

	/**
	 * Get builder wrapper attributes.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_builder_wrapper_attributes( WP_Post $post ) {
		return '';
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_dynamic_variables() {
		return [
			'%club_id%',
			'%season_id%',
			'%season_name%',
			'%club_title%',
			'%club_city%',
			'%club_country%',
			'%club_home_stadium%',
		];
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_conditional_tags() {
		return [
			'%club_city%',
			'%club_country%',
			'%club_home_stadium%',
		];
	}

	/**
	 * Get dynamic variables info.
	 *
	 * @return string
	 */
	public function get_dynamic_variables_info(): string {
		ob_start();
		?>
		<div class="anwp-admin-metabox anwp-b-wrap">
			<div class="anwp-admin-block mt-4">
				<div class="anwp-admin-block__header d-flex align-items-center">
					<span class="dashicons dashicons-book-alt mr-2"></span>
					Dynamic variables
				</div>
				<div class="anwp-admin-block__content">
					<strong>%club_id%</strong> - placeholder for the current club ID<br>
					<strong>%season_id%</strong> - placeholder for the current season ID<br>
				</div>
			</div>
		</div>
		<div class="anwp-admin-metabox anwp-b-wrap">
			<div class="anwp-admin-block mt-4">
				<div class="anwp-admin-block__header d-flex align-items-center">
					<span class="dashicons dashicons-book-alt mr-2"></span>
					Ready to use "Global::Shortcode" snippets
				</div>
				<div class="anwp-admin-block__content">
					<h4 class="mb-2 mt-0">Season club matches grouped by competition</h4>
					<div class="bg-light p-2">[anwpfl-matches season_id="%season_id%" limit="0" filter_by_clubs="%club_id%" sort_by_date="asc" group_by="competition" show_club_logos="1" show_match_datetime="1" competition_logo="1"]</div>
				</div>
			</div>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return ob_get_clean();
	}

	/**
	 * Replace dynamic variables.
	 *
	 * @param string $block_text
	 * @param int    $post_id
	 *
	 * @return string
	 */
	public function replace_dynamic_variables( string $block_text, int $post_id ): string {

		$post_season = anwp_football_leagues_premium()->club->get_post_season( $post_id );

		$block_text = str_ireplace( '%club_id%', $post_id, $block_text );
		$block_text = str_ireplace( '%season_id%', $post_season, $block_text );

		// %season_name%
		if ( strpos( $block_text, '%season_name%' ) !== false && $post_season ) {
			$block_text = str_ireplace( '%season_name%', anwp_football_leagues()->season->get_seasons_options()[ $post_season ], $block_text );
		}

		// %club_title%
		if ( strpos( $block_text, '%club_title%' ) !== false ) {
			$block_text = str_ireplace( '%club_title%', anwp_football_leagues()->club->get_club_title_by_id( $post_id ), $block_text );
		}

		// %club_city%
		if ( strpos( $block_text, '%club_city%' ) !== false ) {
			$club_city  = get_post_meta( $post_id, '_anwpfl_city', true );
			$block_text = preg_replace( '/\[IF%club_city%\](.+?)\[ENDIF%club_city%\]/is', $club_city ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%club_city%', $club_city, $block_text );
		}

		// %club_country%
		if ( strpos( $block_text, '%club_country%' ) !== false ) {
			$club_country = anwp_football_leagues()->data->get_value_by_key( get_post_meta( $post_id, '_anwpfl_nationality', true ), 'country' );
			$block_text   = preg_replace( '/\[IF%club_country%\](.+?)\[ENDIF%club_country%\]/is', $club_country ? '$1' : '', $block_text );
			$block_text   = str_ireplace( '%club_country%', $club_country, $block_text );
		}

		// %club_home_stadium%
		if ( strpos( $block_text, '%club_home_stadium%' ) !== false ) {
			$club_home_stadium = anwp_football_leagues()->stadium->get_stadium_title( get_post_meta( $post_id, '_anwpfl_stadium', true ) );
			$block_text        = preg_replace( '/\[IF%club_home_stadium%\](.+?)\[ENDIF%club_home_stadium%\]/is', $club_home_stadium ? '$1' : '', $block_text );
			$block_text        = str_ireplace( '%club_home_stadium%', $club_home_stadium, $block_text );
		}

		return $block_text;
	}

	/**
	 * Rendering admin list icon
	 *
	 * @param int $post_id
	 */
	public function admin_list_icon_display( int $post_id ) {
		echo '<span class="anwp-builder-list dashicons dashicons-shield"></span>';
	}

	/**
	 * Get builder layout ID.
	 *
	 * @param WP_Post $post
	 *
	 * @return bool/int
	 */
	public function get_builder_layout_id( WP_Post $post ) {

		if ( 'summary' === get_post_meta( $post->ID, '_anwpfl_root_type', true ) ) {
			return false;
		}

		$layout_id = false;

		// Get all layouts
		$args = [
			'post_type'   => 'anwp_fl_builder',
			'numberposts' => - 1,
			'meta_key'    => '_fl_builder_type',
			'meta_value'  => 'club',
		];

		$layouts = get_posts( $args );

		if ( ! empty( $layouts[0] ) && ! empty( $layouts[0]->ID ) ) {
			return $layouts[0]->ID;
		}

		return $layout_id;
	}

	/**
	 * Get type elements.
	 *
	 * @return array
	 */
	public function get_type_elements() {
		return [
			[
				'name'     => 'Header',
				'group'    => 'Club',
				'alias'    => 'club_header',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Info',
				'group'    => 'Club',
				'alias'    => 'club_description',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bottom Content',
				'group'    => 'Club',
				'alias'    => 'club_bottom_content',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Fixtures',
				'group'    => 'Club',
				'alias'    => 'club_fixtures',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Latest Matches',
				'group'    => 'Club',
				'alias'    => 'club_latest',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Squad',
				'group'    => 'Club',
				'alias'    => 'club_squad',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Players Stats',
				'group'    => 'Club',
				'alias'    => 'club_players_stats',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Transfers',
				'group'    => 'Club',
				'alias'    => 'club_transfers',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Gallery',
				'group'    => 'Club',
				'alias'    => 'club_gallery',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Birthdays',
				'group'    => 'Club',
				'alias'    => 'club_birthdays',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Club Stats',
				'group'    => 'Club',
				'alias'    => 'club_stats',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
		];
	}

	/**
	 * Render Club Fixtures
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_fixtures( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'fixtures' );
	}

	/**
	 * Render Club Latest Games
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_latest( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'latest' );
	}

	/**
	 * Render Club Squad
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_squad( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'squad' );
	}

	/**
	 * Render Club Players Stats
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_players_stats( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'stats' );
	}

	/**
	 * Render Club Transfers
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_transfers( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'transfers' );
	}

	/**
	 * Render Club Birthdays
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_birthdays( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'birthdays' );
	}

	/**
	 * Render Club Stats
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_stats( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'game-stats' );
	}

	/**
	 * Render Club Gallery
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_gallery( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'gallery' );
	}

	/**
	 * Render Club Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_header( $block, $post_id ) {
		$data = anwp_football_leagues_premium()->club->get_club_data( $post_id );

		$data->dropdown_class = '';

		echo '<div class="anwp-fl-builder-block anwp-col-12 ' . esc_attr( $block->classes ) . '">';
		anwp_football_leagues()->load_partial( $data, 'club/club-header' );
		echo '</div>';
	}

	/**
	 * Render Club Description
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_description( $block, $post_id ) {
		$classes = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		$header = '';
		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		$custom_content = get_post_meta( $post_id, '_anwpfl_description', true );

		if ( ! empty( $custom_content ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">' . do_shortcode( wpautop( $custom_content ) ) . '</div></div>';
		}
	}

	/**
	 * Render Club Bottom Content
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_club_bottom_content( $block, $post_id ) {
		$classes = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		$header = '';
		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		$custom_content = get_post_meta( $post_id, '_anwpfl_custom_content_below', true );

		if ( ! empty( $custom_content ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">' . do_shortcode( wpautop( wp_kses_post( $custom_content ) ) ) . '</div></div>';
		}
	}

	/**
	 * Get Club section content.
	 *
	 * @param object $block
	 * @param int    $post_id
	 * @param string $section
	 *
	 * @return string
	 */
	private function get_section_content( $block, $post_id, $section ) {

		ob_start();

		$data         = anwp_football_leagues_premium()->club->get_club_data( $post_id );
		$data->header = false;

		anwp_football_leagues()->load_partial( $data, 'club/club-' . sanitize_key( $section ) );

		$html_content = ob_get_clean();

		if ( empty( $html_content ) ) {
			return '';
		}

		/*
		|--------------------------------------------------------------------
		| Render section content
		|--------------------------------------------------------------------
		*/
		ob_start();

		$header  = '';
		$classes = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html_content;

		echo '</div></div>';

		return ob_get_clean();
	}
}

return new AnWPFL_Premium_Builder_Club();
