<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnWP Football Leagues Premium :: Builder :: Referee
 */
class AnWPFL_Premium_Builder_Referee implements AnWPFL_Premium_Builder_Interface {

	/**
	 * Get builder type name.
	 *
	 * @return string
	 */
	public function get_builder_type_name() {
		return esc_html__( 'Referee', 'anwp-football-leagues' );
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
		return 'https://anwppro.userecho.com/knowledge-bases/2/articles/538-referee-available-block-types';
	}

	/**
	 * Get builder type option.
	 *
	 * @return string
	 */
	public function get_builder_type_option() {
		return '<option value="referee">' . esc_html__( 'Referee', 'anwp-football-leagues' ) . '</option>';
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
	public function get_builder_wrapper_attributes( WP_Post $post ): string {
		return '';
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_dynamic_variables(): array {
		return [
			'%referee_id%',
			'%referee_name%',
			'%referee_job_title%',
			'%referee_nationality%',
			'%referee_date_of_birth%',
			'%season_id%',
			'%season_name%',
		];
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_conditional_tags(): array {
		return [
			'%referee_job_title%',
			'%referee_nationality%',
			'%referee_date_of_birth%',
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
					<strong>%referee_id%</strong> - placeholder for the current referee ID<br>
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

		$post_season = anwp_football_leagues_premium()->data->get_referee_post_season( $post_id );

		$block_text = str_ireplace( '%season_id%', $post_season, $block_text );
		$block_text = str_ireplace( '%referee_id%', $post_id, $block_text );

		// %season_name%
		if ( strpos( $block_text, '%season_name%' ) !== false && $post_season ) {
			$block_text = str_ireplace( '%season_name%', anwp_football_leagues()->season->get_seasons_options()[ $post_season ], $block_text );
		}

		// %referee_name%
		if ( strpos( $block_text, '%referee_name%' ) !== false ) {
			$block_text = str_ireplace( '%referee_name%', get_the_title( $post_id ), $block_text );
		}

		// %referee_job_title%
		if ( strpos( $block_text, '%referee_job_title%' ) !== false ) {
			$job_title  = get_post_meta( $post_id, '_anwpfl_job_title', true );
			$block_text = preg_replace( '/\[IF%referee_job_title%\](.+?)\[ENDIF%referee_job_title%\]/is', $job_title ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%referee_job_title%', $job_title, $block_text );
		}

		// %referee_nationality%
		if ( strpos( $block_text, '%referee_nationality%' ) !== false ) {
			$country   = '';
			$countries = get_post_meta( $post_id, '_anwpfl_nationality', true );

			if ( ! empty( $countries ) && is_array( $countries ) ) {
				$country = anwp_football_leagues()->data->get_value_by_key( $countries[0], 'country' );
			}

			$block_text = preg_replace( '/\[IF%referee_nationality%\](.+?)\[ENDIF%referee_nationality%\]/is', $country ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%referee_nationality%', $country, $block_text );
		}

		// %referee_date_of_birth%
		if ( strpos( $block_text, '%referee_date_of_birth%' ) !== false ) {

			$dob       = '';
			$dob_saved = get_post_meta( $post_id, '_anwpfl_date_of_birth', true );

			if ( ! empty( $dob_saved ) ) {
				$dob = date_i18n( get_option( 'date_format' ), strtotime( $dob_saved ) );
			}

			$block_text = preg_replace( '/\[IF%referee_date_of_birth%\](.+?)\[ENDIF%referee_date_of_birth%\]/is', $dob ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%referee_date_of_birth%', $dob, $block_text );
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
			'meta_value'  => 'referee',
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
				'group'    => 'Referee',
				'alias'    => 'referee_header',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Upcoming Games',
				'group'    => 'Referee',
				'alias'    => 'referee_fixtures',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Finished Games',
				'group'    => 'Referee',
				'alias'    => 'referee_finished',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bio',
				'group'    => 'Referee',
				'alias'    => 'referee_description',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bottom Content',
				'group'    => 'Referee',
				'alias'    => 'referee_bottom_content',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
		];
	}

	/**
	 * Render Referee Description
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_referee_description( $block, $post_id ) {
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
	 * Render Referee Bottom Content
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_referee_bottom_content( $block, $post_id ) {
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
	 * Render Referee Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_referee_header( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'header' );
	}

	/**
	 * Render Referee Fixtures
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_referee_fixtures( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'fixtures' );
	}

	/**
	 * Render Referee Finished Games
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_referee_finished( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'finished' );
	}

	/**
	 * Get Referee section content.
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
			'staff_id'  => $post_id,
			'header'    => false,
			'season_id' => anwp_football_leagues()->helper->get_season_id_maybe( $_GET, anwp_football_leagues()->get_active_referee_season( $post_id ) ), // phpcs:ignore WordPress.Security.NonceVerification
		];

		anwp_football_leagues()->load_partial( $data, 'referee/referee-' . sanitize_key( $section ) );

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

return new AnWPFL_Premium_Builder_Referee();
