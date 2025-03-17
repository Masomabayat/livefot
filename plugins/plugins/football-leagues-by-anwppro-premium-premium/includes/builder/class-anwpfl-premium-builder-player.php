<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnWP Football Leagues Premium :: Builder :: Player
 */
class AnWPFL_Premium_Builder_Player implements AnWPFL_Premium_Builder_Interface {

	/**
	 * Get builder type name.
	 *
	 * @return string
	 */
	public function maybe_render_block( $block, $post_id ) {

		if ( ! empty( $block->player_position ) ) {
			$player_position_code = anwp_fl()->player->get_player_data( $post_id )['position'] ?? '';

			if ( 'goalkeeper' === $block->player_position && 'g' !== $player_position_code ) {
				return false;
			} elseif ( 'field' === $block->player_position && 'g' === $player_position_code ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get builder type name.
	 *
	 * @return string
	 */
	public function get_builder_type_name(): string {
		return esc_html__( 'Player', 'anwp-football-leagues' );
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
		return 'https://anwppro.userecho.com/knowledge-bases/2/articles/539-player-available-block-types';
	}

	/**
	 * Get builder type option.
	 *
	 * @return string
	 */
	public function get_builder_type_option() {
		return '<option value="player">' . esc_html__( 'Player', 'anwp-football-leagues' ) . '</option>';
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
	public function get_dynamic_variables(): array {
		return [
			'%player_id%',
			'%season_id%',
			'%season_name%',
			'%player_name%',
			'%player_position%',
			'%player_club%',
			'%player_nationality%',
			'%player_date_of_birth%',
		];
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_conditional_tags(): array {
		return [
			'%player_position%',
			'%player_club%',
			'%player_nationality%',
			'%player_date_of_birth%',
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
					Dynamic variables for "Global::Shortcode"
				</div>
				<div class="anwp-admin-block__content">
					<strong>%player_id%</strong> - placeholder for the current player ID<br>
					<strong>%season_id%</strong> - placeholder for the current season ID<br>
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

		$post_season = anwp_fl_pro()->player->get_post_season( $post_id );
		$player_data = anwp_fl()->player->get_player_data( $post_id );

		$block_text = str_ireplace( '%player_id%', $post_id, $block_text );
		$block_text = str_ireplace( '%season_id%', $post_season, $block_text );

		// %season_name%
		if ( strpos( $block_text, '%season_name%' ) !== false && $post_season ) {
			$block_text = str_ireplace( '%season_name%', anwp_fl()->season->get_seasons_options()[ $post_season ], $block_text );
		}

		// %player_name%
		if ( strpos( $block_text, '%player_name%' ) !== false ) {
			$block_text = str_ireplace( '%player_name%', $player_data['name'], $block_text );
		}

		// %player_position%
		if ( strpos( $block_text, '%player_position%' ) !== false ) {
			$player_position = anwp_fl()->player->get_position_l10n( $player_data['position'] );
			$block_text      = preg_replace( '/\[IF%player_position%\](.+?)\[ENDIF%player_position%\]/is', $player_position ? '$1' : '', $block_text );
			$block_text      = str_ireplace( '%player_position%', $player_position, $block_text );
		}

		// %player_club%
		if ( strpos( $block_text, '%player_club%' ) !== false ) {
			$player_club = anwp_fl()->club->get_club_title_by_id( $player_data['team_id'] );
			$block_text  = preg_replace( '/\[IF%player_club%\](.+?)\[ENDIF%player_club%\]/is', $player_club ? '$1' : '', $block_text );
			$block_text  = str_ireplace( '%player_club%', $player_club, $block_text );
		}

		// %player_nationality%
		if ( strpos( $block_text, '%player_nationality%' ) !== false ) {
			$country = '';

			if ( ! empty( $player_data['nationality'] ) ) {
				$country = anwp_fl()->data->get_value_by_key( $player_data['nationality'], 'country' );
			}

			$block_text = preg_replace( '/\[IF%player_nationality%\](.+?)\[ENDIF%player_nationality%\]/is', $country ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%player_nationality%', $country, $block_text );
		}

		// %player_date_of_birth%
		if ( strpos( $block_text, '%player_date_of_birth%' ) !== false ) {

			$dob       = '';
			$dob_saved = $player_data['date_of_birth'];

			if ( ! empty( $dob_saved ) ) {
				$dob = date_i18n( get_option( 'date_format' ), strtotime( $dob_saved ) );
			}

			$block_text = preg_replace( '/\[IF%player_date_of_birth%\](.+?)\[ENDIF%player_date_of_birth%\]/is', $dob ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%player_date_of_birth%', $dob, $block_text );
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
			'meta_value'  => 'player',
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
				'group'    => 'Player',
				'alias'    => 'player_header',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Stats Panel',
				'group'    => 'Player',
				'alias'    => 'player_stats_panel',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Player Stats',
				'group'    => 'Player',
				'alias'    => 'player_stats',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Player Matches',
				'group'    => 'Player',
				'alias'    => 'player_matches',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Gallery',
				'group'    => 'Player',
				'alias'    => 'player_gallery',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Missed Matches',
				'group'    => 'Player',
				'alias'    => 'player_missed',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Transfers',
				'group'    => 'Player',
				'alias'    => 'player_transfers',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bio',
				'group'    => 'Player',
				'alias'    => 'player_description',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bottom Content',
				'group'    => 'Player',
				'alias'    => 'player_bottom_content',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
		];
	}

	/**
	 * Render Player Stats
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_player_stats( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'stats' );
	}

	/**
	 * Render Player Matches
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_player_matches( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'matches' );
	}

	/**
	 * Render Player Gallery
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_player_gallery( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'gallery' );
	}

	/**
	 * Render Player Missed Matches
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_player_missed( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'missed' );
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

		$data           = anwp_fl_pro()->player->get_player_data( $post_id );
		$data['header'] = false;

		anwp_fl()->load_partial( $data, 'player/player-' . sanitize_key( $section ) );

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
		$classes = anwp_fl_pro()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

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

	/**
	 * Render Player Stats Panel
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_player_stats_panel( $block, $post_id ) {
		$data    = anwp_fl_pro()->player->get_player_data( $post_id );
		$classes = anwp_fl_pro()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '"><div class="anwp-block-content">';
		anwp_fl()->load_partial( $data, 'player/player-stats_panel' );
		echo '</div></div>';
	}

	/**
	 * Render Player Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_player_header( $block, $post_id ) {
		$data = anwp_fl_pro()->player->get_player_data( $post_id );

		echo '<div class="anwp-fl-builder-block anwp-col-12 mb-4 ' . esc_attr( $block->classes ) . '">';

		anwp_fl()->load_partial( $data, 'player/player-header' );

		echo '</div>';
	}

	/**
	 * Render Player Transfers
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_player_transfers( $block, $post_id ) {
		$classes = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		$header = '';
		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		$shortcode_html = anwp_football_leagues()->template->shortcode_loader(
			'premium-transfers',
			[
				'limit'     => 0,
				'order'     => 'DESC',
				'player_id' => $post_id,
				'layout'    => 'player',
			]
		);

		if ( ! empty( $shortcode_html ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';
			echo $shortcode_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div></div>';
		}
	}

	/**
	 * Render Player Description
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_player_description( $block, $post_id ) {
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
	 * Render Player Bottom Content
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_player_bottom_content( $block, $post_id ) {
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
}

return new AnWPFL_Premium_Builder_Player();
