<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnWP Football Leagues Premium :: Builder :: Stadium
 */
class AnWPFL_Premium_Builder_Stadium implements AnWPFL_Premium_Builder_Interface {

	/**
	 * Get builder type name.
	 *
	 * @return string
	 */
	public function get_builder_type_name(): string {
		return esc_html__( 'Stadium', 'anwp-football-leagues' );
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
		return 'https://anwppro.userecho.com/knowledge-bases/2/articles/536-stadium-available-block-types';
	}

	/**
	 * Get builder type option.
	 *
	 * @return string
	 */
	public function get_builder_type_option() {
		return '<option value="stadium">' . esc_html__( 'Stadium', 'anwp-football-leagues' ) . '</option>';
	}

	/**
	 * Get builder wrapper classes.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_builder_wrapper_classes( WP_Post $post ) {
		return ' stadium stadium__inner stadium-' . absint( $post->ID );
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
			'%stadium_id%',
			'%stadium_title%',
			'%stadium_address%',
			'%stadium_city%',
			'%stadium_capacity%',
			'%stadium_clubs%',
		];
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_conditional_tags() {
		return [
			'%stadium_address%',
			'%stadium_city%',
			'%stadium_capacity%',
			'%stadium_clubs%',
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
					<strong>%stadium_id%</strong> - placeholder for the current stadium ID<br>
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

		$block_text = str_ireplace( '%stadium_id%', $post_id, $block_text );

		// %stadium_title%
		if ( strpos( $block_text, '%stadium_title%' ) !== false ) {
			$block_text = str_ireplace( '%stadium_title%', get_the_title( $post_id ), $block_text );
		}

		// %stadium_address%
		if ( strpos( $block_text, '%stadium_address%' ) !== false ) {
			$stadium_address = get_post_meta( $post_id, '_anwpfl_address', true );
			$block_text      = preg_replace( '/\[IF%stadium_address%\](.+?)\[ENDIF%stadium_address%\]/is', $stadium_address ? '$1' : '', $block_text );
			$block_text      = str_ireplace( '%stadium_address%', $stadium_address, $block_text );
		}

		// %stadium_city%
		if ( strpos( $block_text, '%stadium_city%' ) !== false ) {
			$stadium_city = get_post_meta( $post_id, '_anwpfl_city', true );
			$block_text   = preg_replace( '/\[IF%stadium_city%\](.+?)\[ENDIF%stadium_city%\]/is', $stadium_city ? '$1' : '', $block_text );
			$block_text   = str_ireplace( '%stadium_city%', $stadium_city, $block_text );
		}

		// %stadium_capacity%
		if ( strpos( $block_text, '%stadium_capacity%' ) !== false ) {
			$stadium_capacity = get_post_meta( $post_id, '_anwpfl_capacity', true );
			$block_text       = preg_replace( '/\[IF%stadium_capacity%\](.+?)\[ENDIF%stadium_capacity%\]/is', $stadium_capacity ? '$1' : '', $block_text );
			$block_text       = str_ireplace( '%stadium_capacity%', $stadium_capacity, $block_text );
		}

		// %stadium_clubs%
		if ( strpos( $block_text, '%stadium_clubs%' ) !== false ) {
			$club_text = '';
			$club_ids  = get_post_meta( $post_id, '_anwpfl_clubs', true );

			if ( ! empty( $club_ids ) && is_array( $club_ids ) ) {
				foreach ( $club_ids as $index => $club_id ) {
					$club_text .= ( $index > 0 ? ', ' : '' ) . anwp_football_leagues()->club->get_club_title_by_id( $club_id );
				}
			}

			$block_text = preg_replace( '/\[IF%stadium_clubs%\](.+?)\[ENDIF%stadium_clubs%\]/is', $club_text ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%stadium_clubs%', $club_text, $block_text );
		}

		return $block_text;
	}

	/**
	 * Rendering admin list icon
	 *
	 * @param int $post_id
	 */
	public function admin_list_icon_display( int $post_id ) {
		echo '<span class="anwp-builder-list dashicons dashicons-location-alt"></span>';
	}

	/**
	 * Get builder layout ID.
	 *
	 * @param WP_Post $post
	 *
	 * @return bool/int
	 */
	public function get_builder_layout_id( WP_Post $post ) {

		$layout_id = false;

		// Get all layouts
		$args = [
			'post_type'   => 'anwp_fl_builder',
			'numberposts' => - 1,
			'meta_key'    => '_fl_builder_type',
			'meta_value'  => 'stadium',
		];

		$layouts = get_posts( $args );

		if ( ! empty( $layouts[0] ) && ! empty( $layouts[0]->ID ) ) {
			return $layouts[0]->ID;
		}

		return $layout_id;
	}

	/**
	 * Get type elements.
	 */
	public function get_type_elements() {
		return [
			[
				'name'     => 'Header',
				'group'    => 'Stadium',
				'alias'    => 'stadium_header',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Info',
				'group'    => 'Stadium',
				'alias'    => 'stadium_description',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Upcoming Matches',
				'group'    => 'Stadium',
				'alias'    => 'stadium_fixtures',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Finished Matches',
				'group'    => 'Stadium',
				'alias'    => 'stadium_latest',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Gallery',
				'group'    => 'Stadium',
				'alias'    => 'stadium_gallery',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Map',
				'group'    => 'Stadium',
				'alias'    => 'stadium_map',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bottom Content',
				'group'    => 'Stadium',
				'alias'    => 'stadium_bottom_content',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
		];
	}

	/**
	 * Render Stadium Description
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_stadium_description( $block, $post_id ) {
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
	 * Render Stadium Bottom Content
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_stadium_bottom_content( $block, $post_id ) {
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
	 * Render Stadium Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_stadium_header( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'header' );
	}

	/**
	 * Render Stadium Fixtures
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_stadium_fixtures( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'fixtures' );
	}

	/**
	 * Render Stadium Latest
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_stadium_latest( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'latest' );
	}

	/**
	 * Render Stadium Gallery
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_stadium_gallery( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'gallery' );
	}

	/**
	 * Render Stadium Map
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_stadium_map( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'map' );
	}

	/**
	 * Get Stadium section content.
	 *
	 * @param object $block
	 * @param int    $post_id
	 * @param string $section
	 *
	 * @return string
	 */
	private function get_section_content( $block, $post_id, $section ) {

		ob_start();

		$data = [
			'stadium_id' => $post_id,
			'header'     => false,
			'season_id'  => anwp_football_leagues_premium()->stadium->get_post_season( $post_id ),
		];

		anwp_football_leagues()->load_partial( $data, 'stadium/stadium-' . sanitize_key( $section ) );

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

return new AnWPFL_Premium_Builder_Stadium();
