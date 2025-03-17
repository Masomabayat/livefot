<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnWP Football Leagues Premium :: Builder :: Competition Multi
 */
class AnWPFL_Premium_Builder_Competition_Multi implements AnWPFL_Premium_Builder_Interface {

	/**
	 * Get builder type name.
	 *
	 * @return string
	 */
	public function get_builder_type_name() {
		return esc_html__( 'Competition Multistage', 'anwp-football-leagues' );
	}

	/**
	 * Rendering admin list content (builder type).
	 *
	 * @param int $post_id
	 */
	public function admin_list_column_display( int $post_id ) {
		// IDs
		$ids = get_post_meta( $post_id, '_fl_builder_competition_ids', true );
		echo '<strong>' . esc_html__( 'IDs', 'anwp-football-leagues-premium' ) . ':</strong> ' . esc_html( $ids ?: '-' ) . '<br/>';

		// Leagues
		$league      = get_post_meta( $post_id, '_fl_builder_competition_league', true );
		$league_name = '-';

		if ( absint( $league ) ) {

			$league_term = get_term( $league );

			if ( $league_term ) {
				$league_name = $league_term->name;
			}
		}

		echo '<strong>' . esc_html__( 'League', 'anwp-football-leagues' ) . ':</strong> ' . esc_html( $league_name ) . '<br/>';
	}

	/**
	 * Get tutorial link.
	 *
	 * @return string
	 */
	public function get_tutorial_link(): string {
		return 'https://anwppro.userecho.com/knowledge-bases/2/articles/542-competition-available-block-types';
	}

	/**
	 * Get builder type option.
	 *
	 * @return string
	 */
	public function get_builder_type_option() {
		return '<option value="competition_multi">' . esc_html__( 'Competition Multistage', 'anwp-football-leagues' ) . '</option>';
	}

	/**
	 * Get builder wrapper classes.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_builder_wrapper_classes( WP_Post $post ) {
		return ' competition competition__inner competition-' . absint( $post->ID );
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
			'%competition_id%',
			'%group_nN%',
			'%stage_nN%',
			'%season_id%',
			'%season_name%',
			'%league_name%',
			'%league_country%',
			'%competition_title%',
		];
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_conditional_tags() {
		return [
			'%league_country%',
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
					<strong>%group_nN%</strong> - placeholder for the specified group number (N - have to be changed). E.g.: %group_n2% <br>
					<strong>%stage_nN%</strong> - placeholder for the specified competition stage number (N - have to be changed). E.g.: %stage_n2% <br>
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

		$block_text = str_ireplace( '%competition_id%', $post_id, $block_text );

		/* Handle group_nN */
		preg_match( '/(%group_n)(\d+)(%)/', $block_text, $group_matches );

		if ( ! empty( $group_matches[0] ) ) {
			$group_id = '';

			if ( ! empty( $group_matches[2] ) && absint( $group_matches[2] ) ) {
				$groups = json_decode( get_post_meta( $post_id, '_anwpfl_groups', true ) );

				if ( isset( $groups[ $group_matches[2] - 1 ] ) && isset( $groups[ $group_matches[2] - 1 ]->id ) ) {
					$group_id = $groups[ $group_matches[2] - 1 ]->id;
				}

				$block_text = str_ireplace( $group_matches[0], $group_id, $block_text );
			}
		}

		/* Handle stage_nN */
		preg_match( '/(%stage_n)(\d+)(%)/', $block_text, $stage_matches );

		if ( ! empty( $stage_matches[0] ) ) {
			$stage_id = '';

			if ( ! empty( $stage_matches[2] ) && absint( $stage_matches[2] ) ) {

				if ( 1 === absint( $stage_matches[2] ) ) {
					$stage_id = $post_id;
				} else {
					$multi_stages = anwp_football_leagues()->competition->get_secondary_competitions_list( $post_id );

					if ( isset( $multi_stages[ $stage_matches[2] - 2 ] ) && isset( $multi_stages[ $stage_matches[2] - 2 ]['id'] ) ) {
						$stage_id = $multi_stages[ $stage_matches[2] - 2 ]['id'];
					}
				}

				$block_text = str_ireplace( $stage_matches[0], $stage_id, $block_text );
			}
		}

		$competition_obj = anwp_football_leagues()->competition->get_competition( $post_id );

		// '%competition_title%
		if ( strpos( $block_text, '%competition_title%' ) !== false ) {
			$block_text = str_ireplace( '%competition_title%', $competition_obj->title, $block_text );
		}

		// '%season_id%
		if ( strpos( $block_text, '%season_id%' ) !== false ) {
			$block_text = str_ireplace( '%season_id%', $competition_obj->season_ids, $block_text );
		}

		// '%season_name%
		if ( strpos( $block_text, '%season_name%' ) !== false ) {
			$block_text = str_ireplace( '%season_name%', $competition_obj->season_text, $block_text );
		}

		// '%league_name%
		if ( strpos( $block_text, '%league_name%' ) !== false ) {
			$block_text = str_ireplace( '%league_name%', $competition_obj->league_text, $block_text );
		}

		// '%league_name%
		if ( strpos( $block_text, '%league_name%' ) !== false ) {
			$block_text = str_ireplace( '%league_name%', $competition_obj->league_text, $block_text );
		}

		// '%league_country%
		if ( strpos( $block_text, '%league_country%' ) !== false ) {
			$league_country = anwp_football_leagues()->data->get_value_by_key( anwp_football_leagues()->league->get_league_country_code( $competition_obj->league_id ), 'country' );
			$block_text     = preg_replace( '/\[IF%league_country%\](.+?)\[ENDIF%league_country%\]/is', $league_country ? '$1' : '', $block_text );
			$block_text     = str_ireplace( '%league_country%', $league_country, $block_text );
		}

		return $block_text;
	}

	/**
	 * Rendering admin list icon
	 *
	 * @param int $post_id
	 */
	public function admin_list_icon_display( int $post_id ) {
		echo '<span class="anwp-builder-list anwp-competition"></span>';
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
			'meta_value'  => 'competition_multi',
		];

		$layouts = get_posts( $args );

		// Highest priority >> Check for ID
		foreach ( $layouts as $layout ) {
			$ids = trim( get_post_meta( $layout->ID, '_fl_builder_competition_ids', true ) );

			if ( $ids ) {
				$ids = wp_parse_id_list( $ids );

				if ( in_array( $post->ID, $ids, true ) ) {
					return $layout->ID;
				}
			}
		}

		// Low priority >> Check for leagues
		foreach ( $layouts as $layout ) {
			$competition_league = get_post_meta( $layout->ID, '_fl_builder_competition_league', true );

			if ( absint( $competition_league ) ) {
				if ( has_term( absint( $competition_league ), 'anwp_league', $post ) ) {
					return $layout->ID;
				}
			}
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
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_header',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Matches All',
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_matches_all',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'stage_n', 'group_n' ],
			],
			[
				'name'     => 'Matches Finished',
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_matches_finished',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'stage_n', 'group_n' ],
			],
			[
				'name'     => 'Matches Upcoming',
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_matches_upcoming',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'stage_n', 'group_n' ],
			],
			[
				'name'     => 'Matchweek Slides',
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_matchweek_slides',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'stage_n', 'group_n' ],
			],
			[
				'name'     => 'Standing Tables',
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_standings',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'stage_n', 'group_n' ],
			],
			[
				'name'     => 'Results Matrix',
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_results_matrix',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'stage_n', 'group_n' ],
			],
			[
				'name'     => 'Players Stats',
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_players_stats',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bracket',
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_bracket',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'stage_n' ],
			],
			[
				'name'     => 'Transfers',
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_transfers',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bottom Content',
				'group'    => 'Competition Multistage',
				'alias'    => 'competition_multi_custom_content',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
		];
	}

	/**
	 * Render Competition Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_header( $block, $post_id ) {
		$shortcode_attrs = [
			'id'              => $post_id,
			'season_selector' => 1,
			'title_field'     => anwp_football_leagues_premium()->customizer->get_value( 'competition', 'competition_title_field' ),
		];

		echo '<div class="anwp-fl-builder-block mb-0 anwp-col-12 ' . esc_attr( $block->classes ) . '">';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo anwp_football_leagues()->template->shortcode_loader( 'competition_header', $shortcode_attrs );
		echo '</div>';
	}

	/**
	 * Render Competition Custom Content
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_custom_content( $block, $post_id ) {
		$classes = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		$header = '';
		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		$custom_content = get_post_meta( $post_id, '_anwpfl_custom_content_below', true );

		if ( ! empty( $custom_content ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">' . do_shortcode( wpautop( $custom_content ) ) . '</div></div>';
		}
	}

	/**
	 * Render Competition Bracket
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_bracket( $block, $post_id ) {
		$classes      = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;
		$multi_stages = anwp_football_leagues()->competition->get_secondary_competitions_list( $post_id );
		$header       = '';

		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';

		if ( absint( $block->stage_n ) ) {
			$competition_id = $this->get_competition_multi_stage_id( $post_id, $block, $multi_stages );

			if ( 'knockout' === get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
				$bracket_rendering = get_post_meta( $competition_id, '_anwpfl_bracket', true );

				if ( in_array( $bracket_rendering, [ 'manual', 'show' ], true ) ) {

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo anwp_football_leagues()->template->shortcode_loader(
						'premium-bracket',
						[
							'competition_id' => absint( $competition_id ),
							'final_stage_id' => absint( $competition_id ),
						]
					);
				}
			}
		} else {
			foreach ( $this->get_competition_multi_stage_ids_joined( $post_id, $multi_stages ) as $competition_id ) {
				if ( 'knockout' !== get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
					continue;
				}

				$bracket_rendering = get_post_meta( $competition_id, '_anwpfl_bracket', true );

				if ( in_array( $bracket_rendering, [ 'manual', 'show' ], true ) ) {

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo anwp_football_leagues()->template->shortcode_loader(
						'premium-bracket',
						[
							'competition_id' => absint( $competition_id ),
							'final_stage_id' => absint( $competition_id ),
						]
					);
				}
			}
		}

		echo '</div></div>';
	}

	/**
	 * Render Competition Transfers
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_transfers( $block, $post_id ) {

		$classes = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		$header = '';
		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		$data = [
			'header'         => false,
			'competition_id' => $post_id,
		];

		ob_start();
		anwp_football_leagues()->load_partial( $data, 'competition/competition-transfers' );
		$section_output = ob_get_clean();

		if ( ! empty( $section_output ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';
			echo $section_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div></div>';
		}
	}

	/**
	 * Render Competition Results Matrix
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_results_matrix( $block, $post_id ) {
		$classes      = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;
		$multi_stages = anwp_football_leagues()->competition->get_secondary_competitions_list( $post_id );
		$header       = '';

		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';

		if ( absint( $block->stage_n ) ) {
			$competition_id = $this->get_competition_multi_stage_id( $post_id, $block, $multi_stages );

			if ( ! empty( $competition_id ) && 'round-robin' === get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
				$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

				if ( ! empty( $groups ) && is_array( $groups ) ) {
					if ( absint( $block->group_n ) ) {
						if ( isset( $groups[ $block->group_n - 1 ] ) && isset( $groups[ $block->group_n - 1 ]->id ) ) {
							$standing = anwp_football_leagues()->competition->tmpl_get_competition_standings( $competition_id, $groups[ $block->group_n - 1 ]->id );

							if ( ! empty( $standing[0] ) && ! empty( $standing[0]->ID ) ) :

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo anwp_football_leagues()->template->shortcode_loader(
									'premium-results-matrix',
									[
										'competition_id' => absint( $competition_id ),
										'group_id'       => absint( $groups[ $block->group_n - 1 ]->id ),
									]
								);
							endif;
						}
					} else {
						foreach ( $groups as $group ) {
							$standing = anwp_football_leagues()->competition->tmpl_get_competition_standings( $competition_id, $group->id );

							if ( ! empty( $standing[0] ) && ! empty( $standing[0]->ID ) ) :

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo anwp_football_leagues()->template->shortcode_loader(
									'premium-results-matrix',
									[
										'competition_id' => absint( $competition_id ),
										'group_id'       => absint( $group->id ),
									]
								);
							endif;
						}
					}
				}
			}
		} else {
			foreach ( $this->get_competition_multi_stage_ids_joined( $post_id, $multi_stages ) as $competition_id ) {

				if ( 'round-robin' !== get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
					continue;
				}

				$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

				if ( ! empty( $groups ) && is_array( $groups ) ) {
					if ( absint( $block->group_n ) ) {
						if ( isset( $groups[ $block->group_n - 1 ] ) && isset( $groups[ $block->group_n - 1 ]->id ) ) {
							$standing = anwp_football_leagues()->competition->tmpl_get_competition_standings( $competition_id, $groups[ $block->group_n - 1 ]->id );

							if ( ! empty( $standing[0] ) && ! empty( $standing[0]->ID ) ) :

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo anwp_football_leagues()->template->shortcode_loader(
									'premium-results-matrix',
									[
										'competition_id' => absint( $competition_id ),
										'group_id'       => absint( $groups[ $block->group_n - 1 ]->id ),
									]
								);
							endif;
						}
					} else {
						foreach ( $groups as $group ) {
							$standing = anwp_football_leagues()->competition->tmpl_get_competition_standings( $competition_id, $group->id );

							if ( ! empty( $standing[0] ) && ! empty( $standing[0]->ID ) ) :

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo anwp_football_leagues()->template->shortcode_loader(
									'premium-results-matrix',
									[
										'competition_id' => absint( $competition_id ),
										'group_id'       => absint( $group->id ),
									]
								);
							endif;
						}
					}
				}
			}
		}

		echo '</div></div>';
	}

	/**
	 * Render Competition Standings
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_standings( $block, $post_id ) {

		$classes      = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;
		$multi_stages = anwp_football_leagues()->competition->get_secondary_competitions_list( $post_id );
		$header       = '';

		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';

		if ( absint( $block->stage_n ) ) {
			$competition_id = $this->get_competition_multi_stage_id( $post_id, $block, $multi_stages );

			if ( ! empty( $competition_id ) && 'round-robin' === get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
				$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

				if ( ! empty( $groups ) && is_array( $groups ) ) {
					if ( absint( $block->group_n ) ) {
						if ( isset( $groups[ $block->group_n - 1 ] ) && isset( $groups[ $block->group_n - 1 ]->id ) ) {
							$standing = anwp_football_leagues()->competition->tmpl_get_competition_standings( $competition_id, $groups[ $block->group_n - 1 ]->id );

							if ( ! empty( $groups[ $block->group_n - 1 ]->title ) ) {
								echo '<div class="competition__group-title mt-4 mb-2 anwp-group-header">' . esc_html( $groups[ $block->group_n - 1 ]->title ) . '</div>';
							}

							if ( ! empty( $standing[0] ) && ! empty( $standing[0]->ID ) ) {
								$shortcode_attrs = [
									'id'      => absint( $standing[0]->ID ),
									'title'   => '',
									'context' => 'competition',
								];
							}

							if ( ! empty( $shortcode_attrs ) ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo anwp_football_leagues()->template->shortcode_loader( 'standing', $shortcode_attrs );
							}
						}
					} else {
						foreach ( $groups as $group ) {
							$standing = anwp_football_leagues()->competition->tmpl_get_competition_standings( $competition_id, $group->id );

							if ( count( $groups ) > 1 && ! empty( $group->title ) ) {
								echo '<div class="competition__group-title mt-4 mb-2 anwp-group-header">' . esc_html( $group->title ) . '</div>';
							}

							if ( ! empty( $standing[0] ) && ! empty( $standing[0]->ID ) ) {
								$shortcode_attrs = [
									'id'      => absint( $standing[0]->ID ),
									'title'   => '',
									'context' => 'competition',
								];
							}

							if ( ! empty( $shortcode_attrs ) ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo anwp_football_leagues()->template->shortcode_loader( 'standing', $shortcode_attrs );
							}
						}
					}
				}
			}
		} else {
			foreach ( $this->get_competition_multi_stage_ids_joined( $post_id, $multi_stages ) as $competition_id ) {

				if ( 'round-robin' !== get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
					continue;
				}

				$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

				if ( ! empty( $groups ) && is_array( $groups ) ) {
					if ( absint( $block->group_n ) ) {
						if ( isset( $groups[ $block->group_n - 1 ] ) && isset( $groups[ $block->group_n - 1 ]->id ) ) {
							$standing = anwp_football_leagues()->competition->tmpl_get_competition_standings( $competition_id, $groups[ $block->group_n - 1 ]->id );

							if ( ! empty( $groups[ $block->group_n - 1 ]->title ) ) {
								echo '<div class="competition__group-title mt-4 mb-2 anwp-group-header">' . esc_html( $groups[ $block->group_n - 1 ]->title ) . '</div>';
							}

							if ( ! empty( $standing[0] ) && ! empty( $standing[0]->ID ) ) {
								$shortcode_attrs = [
									'id'      => absint( $standing[0]->ID ),
									'title'   => '',
									'context' => 'competition',
								];
							}

							if ( ! empty( $shortcode_attrs ) ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo anwp_football_leagues()->template->shortcode_loader( 'standing', $shortcode_attrs );
							}
						}
					} else {
						foreach ( $groups as $group ) {
							$standing = anwp_football_leagues()->competition->tmpl_get_competition_standings( $competition_id, $group->id );

							if ( count( $groups ) > 1 && ! empty( $group->title ) ) {
								echo '<div class="competition__group-title mt-4 mb-2 anwp-group-header">' . esc_html( $group->title ) . '</div>';
							}

							if ( ! empty( $standing[0] ) && ! empty( $standing[0]->ID ) ) {
								$shortcode_attrs = [
									'id'      => absint( $standing[0]->ID ),
									'title'   => '',
									'context' => 'competition',
								];
							}

							if ( ! empty( $shortcode_attrs ) ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo anwp_football_leagues()->template->shortcode_loader( 'standing', $shortcode_attrs );
							}
						}
					}
				}
			}
		}

		echo '</div></div>';
	}

	/**
	 * Render Competition Players Stats
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_players_stats( $block, $post_id ) {
		$classes = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;
		$header  = '';

		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';

		$shortcode_attrs = [
			'type'           => '',
			'rows'           => 10,
			'multistage'     => 1,
			'competition_id' => $post_id,
			'layout_mod'     => 'even',
		];

		if ( ! empty( $shortcode_attrs ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo anwp_football_leagues()->template->shortcode_loader( 'premium-stats-players', $shortcode_attrs );
		}

		echo '</div></div>';
	}

	/**
	 * Render Competition Matchweek Slides
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_matchweek_slides( $block, $post_id ) {
		$classes      = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;
		$multi_stages = anwp_football_leagues()->competition->get_secondary_competitions_list( $post_id );
		$header       = '';

		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';

		if ( absint( $block->stage_n ) ) {
			$competition_id = $this->get_competition_multi_stage_id( $post_id, $block, $multi_stages );

			if ( ! empty( $competition_id ) ) {
				$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

				if ( ! empty( $groups ) && is_array( $groups ) ) {
					if ( absint( $block->group_n ) ) {
						if ( isset( $groups[ $block->group_n - 1 ] ) && isset( $groups[ $block->group_n - 1 ]->id ) ) {
							$shortcode_attrs = [
								'group_id'            => $groups[ $block->group_n - 1 ]->id,
								'show_club_logos'     => 1,
								'show_match_datetime' => 1,
								'match_card'          => 'slim',
								'slides_to_show'      => 10,
								'matchweek'           => - 1,
								'competition_id'      => $competition_id,
							];
						}
					} else {
						$shortcode_attrs = [
							'show_club_logos'     => 1,
							'show_match_datetime' => 1,
							'match_card'          => 'slim',
							'slides_to_show'      => 10,
							'matchweek'           => - 1,
							'competition_id'      => $competition_id,
						];
					}

					if ( ! empty( $shortcode_attrs ) ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo anwp_football_leagues()->template->shortcode_loader( 'premium-matchweeks-slides', $shortcode_attrs );
					}
				}
			}
		} else {
			foreach ( $this->get_competition_multi_stage_ids_joined( $post_id, $multi_stages ) as $competition_id ) {
				if ( 'round-robin' !== get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
					continue;
				}

				$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

				if ( ! empty( $groups ) && is_array( $groups ) ) {
					if ( absint( $block->group_n ) ) {
						if ( isset( $groups[ $block->group_n - 1 ] ) && isset( $groups[ $block->group_n - 1 ]->id ) ) {
							$shortcode_attrs = [
								'group_id'            => $groups[ $block->group_n - 1 ]->id,
								'show_club_logos'     => 1,
								'show_match_datetime' => 1,
								'match_card'          => 'slim',
								'slides_to_show'      => 10,
								'matchweek'           => - 1,
								'competition_id'      => $competition_id,
							];
						}
					} else {
						$shortcode_attrs = [
							'show_club_logos'     => 1,
							'show_match_datetime' => 1,
							'match_card'          => 'slim',
							'slides_to_show'      => 10,
							'matchweek'           => - 1,
							'competition_id'      => $competition_id,
						];
					}

					if ( ! empty( $shortcode_attrs ) ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo anwp_football_leagues()->template->shortcode_loader( 'premium-matchweeks-slides', $shortcode_attrs );
					}
				}
			}
		}

		echo '</div></div>';
	}

	/**
	 * Render Competition Matches All
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_matches_all( $block, $post_id ) {
		$classes      = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;
		$multi_stages = anwp_football_leagues()->competition->get_secondary_competitions_list( $post_id );
		$header       = '';

		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';

		if ( absint( $block->stage_n ) ) {
			$competition_id = $this->get_competition_multi_stage_id( $post_id, $block, $multi_stages );

			if ( ! empty( $competition_id ) && 'knockout' === get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->render_knockout_matches( $competition_id );
			} elseif ( ! empty( $competition_id ) ) {
				if ( absint( $block->group_n ) ) {
					$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

					if ( isset( $groups[ $block->group_n - 1 ] ) && isset( $groups[ $block->group_n - 1 ]->id ) ) {
						$shortcode_attrs = [
							'competition_id'      => $competition_id,
							'group_id'            => $groups[ $block->group_n - 1 ]->id,
							'limit'               => 0,
							'sort_by_date'        => 'asc',
							'group_by'            => 'matchweek',
							'show_club_logos'     => 1,
							'show_match_datetime' => 1,
							'competition_logo'    => 0,
						];
					}
				} else {
					$shortcode_attrs = [
						'competition_id'      => $competition_id,
						'limit'               => 0,
						'sort_by_date'        => 'asc',
						'group_by'            => 'matchweek',
						'show_club_logos'     => 1,
						'show_match_datetime' => 1,
						'competition_logo'    => 0,
					];
				}

				if ( ! empty( $shortcode_attrs ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo anwp_football_leagues()->template->shortcode_loader( 'matches', $shortcode_attrs );
				}
			}
		} else {

			echo '<div class="anwp-section-header anwp-section">' . esc_html( get_post_meta( $post_id, '_anwpfl_stage_title', true ) ) . '</div>';

			if ( 'knockout' === get_post_meta( $post_id, '_anwpfl_type', true ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->render_knockout_matches( $post_id );
			} else {

				$shortcode_attrs = [
					'competition_id'      => $post_id,
					'limit'               => 0,
					'sort_by_date'        => 'asc',
					'group_by'            => 'matchweek',
					'show_club_logos'     => 1,
					'show_match_datetime' => 1,
					'competition_logo'    => 0,
					'show_secondary'      => 0,
				];

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo anwp_football_leagues()->template->shortcode_loader( 'matches', $shortcode_attrs );
			}

			foreach ( $multi_stages as $stage ) {

				if ( 'knockout' === $stage['type'] ) {
					echo '<div class="anwp-section-header anwp-section mt-5">' . esc_html( $stage['stage_title'] ) . '</div>';
					echo $this->render_knockout_matches( $stage['id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo '<div class="anwp-section-header anwp-section mt-5 mb-n4">' . esc_html( $stage['stage_title'] ) . '</div>';

					$shortcode_attrs = [
						'competition_id'      => $stage['id'],
						'limit'               => 0,
						'sort_by_date'        => 'asc',
						'group_by'            => 'matchweek',
						'show_club_logos'     => 1,
						'show_match_datetime' => 1,
						'competition_logo'    => 0,
						'show_secondary'      => 0,
					];

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo anwp_football_leagues()->template->shortcode_loader( 'matches', $shortcode_attrs );
				}
			}
		}

		echo '</div></div>';
	}

	/**
	 * Render Competition Matches Upcoming
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_matches_upcoming( $block, $post_id ) {
		$classes      = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;
		$multi_stages = anwp_football_leagues()->competition->get_secondary_competitions_list( $post_id );
		$header       = '';

		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';

		if ( absint( $block->stage_n ) ) {
			$competition_id = $this->get_competition_multi_stage_id( $post_id, $block, $multi_stages );

			if ( ! empty( $competition_id ) ) {
				if ( absint( $block->group_n ) ) {
					$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

					if ( isset( $groups[ $block->group_n - 1 ] ) && isset( $groups[ $block->group_n - 1 ]->id ) ) {
						$shortcode_attrs = [
							'competition_id'      => $competition_id,
							'group_id'            => $groups[ $block->group_n - 1 ]->id,
							'type'                => 'fixture',
							'limit'               => 0,
							'sort_by_date'        => 'asc',
							'group_by'            => 'day',
							'show_club_logos'     => 1,
							'show_match_datetime' => 1,
							'competition_logo'    => 0,
						];
					}
				} else {
					$shortcode_attrs = [
						'competition_id'      => $competition_id,
						'type'                => 'fixture',
						'limit'               => 0,
						'sort_by_date'        => 'asc',
						'group_by'            => 'day',
						'show_club_logos'     => 1,
						'show_match_datetime' => 1,
						'competition_logo'    => 0,
					];
				}

				if ( ! empty( $shortcode_attrs ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo anwp_football_leagues()->template->shortcode_loader( 'matches', $shortcode_attrs );
				}
			}
		} else {
			$shortcode_attrs = [
				'competition_id'      => $post_id,
				'type'                => 'fixture',
				'limit'               => 0,
				'sort_by_date'        => 'asc',
				'group_by'            => 'day',
				'show_club_logos'     => 1,
				'show_match_datetime' => 1,
				'competition_logo'    => 0,
				'show_secondary'      => 1,
			];

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo anwp_football_leagues()->template->shortcode_loader( 'matches', $shortcode_attrs );
		}

		echo '</div></div>';
	}

	/**
	 * Render Competition Matches Finished
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_competition_multi_matches_finished( $block, $post_id ) {
		$classes      = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;
		$multi_stages = anwp_football_leagues()->competition->get_secondary_competitions_list( $post_id );
		$header       = '';

		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">';

		if ( absint( $block->stage_n ) ) {
			$competition_id = $this->get_competition_multi_stage_id( $post_id, $block, $multi_stages );

			if ( ! empty( $competition_id ) ) {
				if ( absint( $block->group_n ) ) {
					$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

					if ( isset( $groups[ $block->group_n - 1 ] ) && isset( $groups[ $block->group_n - 1 ]->id ) ) {
						$shortcode_attrs = [
							'competition_id'      => $competition_id,
							'group_id'            => $groups[ $block->group_n - 1 ]->id,
							'type'                => 'result',
							'limit'               => 0,
							'sort_by_date'        => 'desc',
							'group_by'            => 'day',
							'show_club_logos'     => 1,
							'show_match_datetime' => 1,
							'competition_logo'    => 0,
						];
					}
				} else {
					$shortcode_attrs = [
						'competition_id'      => $competition_id,
						'type'                => 'result',
						'limit'               => 0,
						'sort_by_date'        => 'desc',
						'group_by'            => 'day',
						'show_club_logos'     => 1,
						'show_match_datetime' => 1,
						'competition_logo'    => 0,
					];
				}

				if ( ! empty( $shortcode_attrs ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo anwp_football_leagues()->template->shortcode_loader( 'matches', $shortcode_attrs );
				}
			}
		} else {
			$shortcode_attrs = [
				'competition_id'      => $post_id,
				'type'                => 'result',
				'limit'               => 0,
				'sort_by_date'        => 'desc',
				'group_by'            => 'day',
				'show_club_logos'     => 1,
				'show_match_datetime' => 1,
				'competition_logo'    => 0,
				'show_secondary'      => 1,
			];

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo anwp_football_leagues()->template->shortcode_loader( 'matches', $shortcode_attrs );
		}

		echo '</div></div>';
	}

	/**
	 * Rendering knockout matches
	 * @param $competition_id
	 *
	 * @return false|string
	 * @since 0.10.0
	 */
	private function render_knockout_matches( $competition_id ) {

		if ( 'knockout' !== get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
			return '';
		}

		// Prepare groups
		$groups = json_decode( get_post_meta( $competition_id, '_anwpfl_groups', true ) );

		if ( empty( $groups ) || ! is_array( $groups ) ) {
			return '';
		}

		/*
		|--------------------------------------------------------------------
		| Populate round field
		| Backward compatibility for competitions created before v0.10
		| @since 0.10.0 (CORE)
		|--------------------------------------------------------------------
		*/
		foreach ( $groups as $group_id => $group ) {
			if ( empty( $groups[ $group_id ]->round ) ) {
				$groups[ $group_id ]->round = 1;
			}
		}

		/*
		|--------------------------------------------------------------------
		| Prepare rounds
		| @since 0.10.0 (CORE)
		|--------------------------------------------------------------------
		*/
		$rounds = json_decode( get_post_meta( $competition_id, '_anwpfl_rounds', true ) );

		if ( empty( $rounds ) || ! is_array( $rounds ) ) {
			$rounds = [
				(object) [
					'id'    => 1,
					'title' => '',
				],
			];
		}

		// Check DESC round sorting
		if ( 'desc' === anwp_football_leagues_premium()->customizer->get_value( 'competition', 'competition_rounds_order' ) ) {
			$rounds = wp_list_sort( $rounds, 'id', 'DESC' );
		}

		$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches( $competition_id, false );

		ob_start();

		foreach ( $rounds as $round ) :
			if ( count( $rounds ) > 1 && ! empty( $round->title ) ) :
				?>
				<div class="anwp-section">
					<?php
					anwp_football_leagues()->load_partial(
						[
							'text'  => $round->title,
							'class' => 'competition__round-title',
						],
						'general/header'
					);
					?>
				</div>
				<?php
			endif;

			foreach ( $groups as $group ) :

				if ( intval( $group->round ) !== intval( $round->id ) ) {
					continue;
				}

				?>
				<div class="competition__group-wrapper">
					<?php
					foreach ( $matches as $match ) :

						if ( absint( $match->competition_id ) !== absint( $competition_id ) || absint( $match->group_id ) !== absint( $group->id ) ) {
							continue;
						}

						// Get match data to render
						$data = anwp_football_leagues()->match->prepare_match_data_to_render( $match );

						$data['competition_logo'] = false;
						anwp_football_leagues()->load_partial( $data, 'match/match', 'slim' );

					endforeach;
					?>
				</div>
				<?php
			endforeach; // End of Groups Loop
		endforeach; // End of Round Loop

		return ob_get_clean();
	}

	private function get_competition_multi_stage_ids_joined( $post_id, $multi_stages ) {
		$output = wp_list_pluck( $multi_stages, 'id' );

		array_unshift( $output, $post_id );

		return $output;
	}

	private function get_competition_multi_stage_id( $post_id, $block, $multi_stages ) {
		$competition_id = '';

		if ( 1 === absint( $block->stage_n ) ) {
			$competition_id = $post_id;
		} else {
			if ( isset( $multi_stages[ $block->stage_n - 2 ] ) && isset( $multi_stages[ $block->stage_n - 2 ]['id'] ) ) {
				$competition_id = $multi_stages[ $block->stage_n - 2 ]['id'];
			}
		}

		return $competition_id;
	}
}

return new AnWPFL_Premium_Builder_Competition_Multi();
