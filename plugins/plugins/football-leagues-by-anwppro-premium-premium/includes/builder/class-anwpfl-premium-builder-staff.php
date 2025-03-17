<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnWP Football Leagues Premium :: Builder :: Staff
 */
class AnWPFL_Premium_Builder_Staff implements AnWPFL_Premium_Builder_Interface {

	/**
	 * Get builder type name.
	 *
	 * @return string
	 */
	public function get_builder_type_name() {
		return esc_html__( 'Staff', 'anwp-football-leagues' );
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
		return 'https://anwppro.userecho.com/knowledge-bases/2/articles/537-staff-available-block-types';
	}

	/**
	 * Get builder type option.
	 *
	 * @return string
	 */
	public function get_builder_type_option() {
		return '<option value="staff">' . esc_html__( 'Staff', 'anwp-football-leagues' ) . '</option>';
	}

	/**
	 * Get builder wrapper classes.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_builder_wrapper_classes( WP_Post $post ) {
		return ' player player__inner player-id-' . absint( $post->ID );
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
			'%staff_id%',
			'%staff_name%',
			'%staff_job_title%',
			'%staff_current_club%',
			'%staff_nationality%',
			'%staff_date_of_birth%',
		];
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_conditional_tags() {
		return [
			'%staff_job_title%',
			'%staff_current_club%',
			'%staff_nationality%',
			'%staff_date_of_birth%',
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
					<strong>%staff_id%</strong> - placeholder for the current staff ID<br>
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

		$block_text = str_ireplace( '%staff_id%', $post_id, $block_text );

		// %staff_name%
		if ( strpos( $block_text, '%staff_name%' ) !== false ) {
			$block_text = str_ireplace( '%staff_name%', get_the_title( $post_id ), $block_text );
		}

		// %staff_job_title%
		if ( strpos( $block_text, '%staff_job_title%' ) !== false ) {
			$job_title  = get_post_meta( $post_id, '_anwpfl_job_title', true );
			$block_text = preg_replace( '/\[IF%staff_job_title%\](.+?)\[ENDIF%staff_job_title%\]/is', $job_title ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%staff_job_title%', $job_title, $block_text );
		}

		// %staff_nationality%
		if ( strpos( $block_text, '%staff_nationality%' ) !== false ) {
			$country   = '';
			$countries = get_post_meta( $post_id, '_anwpfl_nationality', true );

			if ( ! empty( $countries ) && is_array( $countries ) ) {
				$country = anwp_football_leagues()->data->get_value_by_key( $countries[0], 'country' );
			}

			$block_text = preg_replace( '/\[IF%staff_nationality%\](.+?)\[ENDIF%staff_nationality%\]/is', $country ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%staff_nationality%', $country, $block_text );
		}

		// %staff_date_of_birth%
		if ( strpos( $block_text, '%staff_date_of_birth%' ) !== false ) {

			$dob       = '';
			$dob_saved = get_post_meta( $post_id, '_anwpfl_date_of_birth', true );

			if ( ! empty( $dob_saved ) ) {
				$dob = date_i18n( get_option( 'date_format' ), strtotime( $dob_saved ) );
			}

			$block_text = preg_replace( '/\[IF%staff_date_of_birth%\](.+?)\[ENDIF%staff_date_of_birth%\]/is', $dob ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%staff_date_of_birth%', $dob, $block_text );
		}

		// %staff_current_club%
		if ( strpos( $block_text, '%staff_current_club%' ) !== false ) {
			$staff_current_club = anwp_football_leagues()->club->get_club_title_by_id( get_post_meta( $post_id, '_anwpfl_current_club', true ) );
			$block_text         = preg_replace( '/\[IF%staff_current_club%\](.+?)\[ENDIF%staff_current_club%\]/is', $staff_current_club ? '$1' : '', $block_text );
			$block_text         = str_ireplace( '%staff_current_club%', $staff_current_club, $block_text );
		}

		return $block_text;
	}

	/**
	 * Rendering admin list icon
	 *
	 * @param int $post_id
	 */
	public function admin_list_icon_display( int $post_id ) {
		echo '<span class="anwp-builder-list dashicons dashicons-groups"></span>';
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
			'meta_value'  => 'staff',
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
				'group'    => 'Staff',
				'alias'    => 'staff_header',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Bio',
				'group'    => 'Staff',
				'alias'    => 'staff_description',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'History',
				'group'    => 'Staff',
				'alias'    => 'staff_history',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bottom Content',
				'group'    => 'Staff',
				'alias'    => 'staff_bottom_content',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
		];
	}

	/**
	 * Render Staff Description
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_staff_description( $block, $post_id ) {
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
	 * Render Staff Bottom Content
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_staff_bottom_content( $block, $post_id ) {
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
	 * Render Staff Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_staff_header( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'header' );
	}

	/**
	 * Render Staff History
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_staff_history( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'history' );
	}

	/**
	 * Get Staff section content.
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
			'staff_id' => $post_id,
			'header'   => false,
		];

		anwp_football_leagues()->load_partial( $data, 'staff/staff-' . sanitize_key( $section ) );

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

return new AnWPFL_Premium_Builder_Staff();
