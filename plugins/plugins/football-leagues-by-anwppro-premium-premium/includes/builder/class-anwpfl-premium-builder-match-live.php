<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnWP Football Leagues Premium :: Builder :: Match LIVE
 */
class AnWPFL_Premium_Builder_Match_Live implements AnWPFL_Premium_Builder_Interface {

	/**
	 * Get builder type name.
	 *
	 * @return string
	 */
	public function get_builder_type_name() {
		return esc_html__( 'Match LIVE', 'anwp-football-leagues-premium' );
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
		return 'https://anwppro.userecho.com/knowledge-bases/2/articles/541-match-available-block-types';
	}

	/**
	 * Get builder type option.
	 *
	 * @return string
	 */
	public function get_builder_type_option() {
		return '<option value="match_live">' . esc_html__( 'Match LIVE', 'anwp-football-leagues-premium' ) . '</option>';
	}

	/**
	 * Get builder wrapper classes.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_builder_wrapper_classes( WP_Post $post ) {

		return ' match__inner--live fl-match-live-layout match-status__fixture position-relative match match__inner match-' . absint( $post->ID );
	}

	/**
	 * Get builder wrapper attributes.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_builder_wrapper_attributes( WP_Post $post ) {
		return ' data-id="' . absint( $post->ID ) . '"';
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_dynamic_variables() {
		return [
			'%match_id%',
			'%competition_id%',
			'%season_id%',
			'%matchweek%',
			'%home_id%',
			'%away_id%',
			'%kickoff%',
			'%home_club_title%',
			'%away_club_title%',
			'%competition_title%',
			'%season_title%',
			'%league_title%',
			'%match_date%',
			'%match_time%',
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
					<strong>%match_id%</strong> - placeholder for the current match ID<br>
					<strong>%competition_id%</strong> - placeholder for the match competition ID<br>
					<strong>%season_id%</strong> - placeholder for the match season ID<br>
					<strong>%matchweek%</strong> - placeholder for the match MatchWeek<br>
					<strong>%home_id%</strong> - placeholder for the home club ID<br>
					<strong>%away_id%</strong> - placeholder for the away club ID<br>
					<strong>%kickoff%</strong> - placeholder for the kickoff time<br>
				</div>
			</div>
		</div>
		<div class="anwp-admin-metabox anwp-b-wrap">
			<div class="anwp-admin-block mt-4">
				<div class="anwp-admin-block__header"><span class="dashicons dashicons-book-alt"></span> Ready to use "Global::Shortcode" snippets</div>
				<div class="anwp-admin-block__content">
					<h4 class="mb-2 mt-0">All matches of this matchweek (horizontal slider)</h4>
					<div class="bg-light p-2">[anwpfl-matches-scoreboard competition_id="%competition_id%" show_secondary="0" limit="0" filter_by_matchweeks="%matchweek%" show_match_datetime="1" club_links="1" club_titles="1" today_focus="0" priority="0"]</div>

					<h4 class="mb-2 mt-4">All matches of this matchweek (list)</h4>
					<div class="bg-light p-2">[anwpfl-matches competition_id="%competition_id%" show_secondary="0" limit="0" filter_by_matchweeks="%matchweek%" show_club_logos="1" show_match_datetime="1" competition_logo="1"]</div>
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

		// Prepare match related IDs
		$game_data = anwp_fl()->match->get_game_data( $post_id );

		$match_competition_id = absint( $game_data['main_stage_id'] ) ?: $game_data['competition_id'];
		$match_season_id      = absint( $game_data['season_id'] );
		$match_league_id      = absint( $game_data['league_id'] );
		$match_match_week     = absint( $game_data['match_week'] );
		$match_home_id        = absint( $game_data['home_club'] );
		$match_away_id        = absint( $game_data['away_club'] );
		$match_kickoff        = $game_data['kickoff'];

		$block_text = str_ireplace( '%match_id%', $post_id, $block_text );
		$block_text = str_ireplace( '%competition_id%', $match_competition_id, $block_text );
		$block_text = str_ireplace( '%season_id%', $match_season_id, $block_text );
		$block_text = str_ireplace( '%matchweek%', $match_match_week, $block_text );
		$block_text = str_ireplace( '%home_id%', $match_home_id, $block_text );
		$block_text = str_ireplace( '%away_id%', $match_away_id, $block_text );

		$block_text = str_ireplace( '%kickoff%', date_i18n( anwp_fl()->get_option_value( 'custom_match_date_format' ) ?: 'Y-m-d', get_date_from_gmt( $match_kickoff, 'U' ) ), $block_text );

		// %home_club_title%
		if ( strpos( $block_text, '%home_club_title%' ) !== false ) {
			$block_text = str_ireplace( '%home_club_title%', anwp_fl()->club->get_club_title_by_id( $match_home_id ), $block_text );
		}

		// %away_club_title%
		if ( strpos( $block_text, '%away_club_title%' ) !== false ) {
			$block_text = str_ireplace( '%away_club_title%', anwp_fl()->club->get_club_title_by_id( $match_away_id ), $block_text );
		}

		// %competition_title%
		if ( strpos( $block_text, '%competition_title%' ) !== false ) {
			$block_text = str_ireplace( '%competition_title%', anwp_fl()->competition->get_competition_title( $game_data['competition_id'] ), $block_text );
		}

		// %season_title%
		if ( strpos( $block_text, '%season_title%' ) !== false ) {
			$block_text = str_ireplace( '%season_title%', get_term( $match_season_id )->name, $block_text );
		}

		// %league_title%
		if ( strpos( $block_text, '%league_title%' ) !== false ) {
			$block_text = str_ireplace( '%league_title%', get_term( $match_league_id )->name, $block_text );
		}

		// %match_date%
		if ( strpos( $block_text, '%match_date%' ) !== false ) {
			$date_format = anwp_fl()->get_option_value( 'custom_match_date_format' ) ?: get_option( 'date_format' );
			$block_text  = str_ireplace( '%match_date%', date_i18n( $date_format, get_date_from_gmt( $match_kickoff, 'U' ) ), $block_text );
		}

		// %match_time%
		if ( strpos( $block_text, '%match_time%' ) !== false ) {
			$time_format = anwp_fl()->get_option_value( 'custom_match_time_format' ) ?: get_option( 'time_format' );
			$block_text  = str_ireplace( '%match_time%', date_i18n( $time_format, get_date_from_gmt( $match_kickoff, 'U' ) ), $block_text );
		}

		return $block_text;
	}

	/**
	 * Rendering admin list icon
	 *
	 * @param int $post_id
	 */
	public function admin_list_icon_display( int $post_id ) {
		echo '<span class="anwp-builder-list anwp-match"></span>';
	}

	/**
	 * Get builder layout ID.
	 *
	 * @param WP_Post $post
	 *
	 * @return bool|int
	 */
	public function get_builder_layout_id( WP_Post $post ) {

		$layout_id = false;
		$match_id  = $post->ID;

		$game_data = anwp_fl()->match->get_game_data( $post->ID );

		if ( empty( $game_data ) ) {
			return false;
		}

		if ( absint( $game_data['finished'] ) ) {
			return false;
		}

		if ( empty( get_post_meta( $match_id, '_anwpfl_live_status', true ) ) && ! anwp_fl_pro()->live->is_api_game_active( $match_id ) ) {
			return false;
		}

		// Get all layouts
		$args = [
			'post_type'   => 'anwp_fl_builder',
			'numberposts' => - 1,
			'meta_key'    => '_fl_builder_type',
			'meta_value'  => 'match_live',
		];

		$layouts = get_posts( $args );

		if ( ! empty( $layouts[0] ) && ! empty( $layouts[0]->ID ) ) {
			return $layouts[0]->ID;
		}

		return false;
	}

	/**
	 * Get type elements.
	 *
	 * @return array
	 */
	public function get_type_elements(): array {
		$builder_elements = [
			[
				'name'     => 'Header',
				'group'    => 'Match',
				'alias'    => 'match_live_header',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Scoreboard',
				'group'    => 'Match',
				'alias'    => 'match_live_scoreboard',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Summary',
				'group'    => 'Match',
				'alias'    => 'match_live_summary',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bottom Content',
				'group'    => 'Match',
				'alias'    => 'match_live_bottom_content',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Custom Code',
				'group'    => 'Match',
				'alias'    => 'match_live_custom_code',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Latest Matches',
				'group'    => 'Match',
				'alias'    => 'match_live_latest',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'no_data_text' ],
			],
			[
				'name'     => 'LineUps',
				'group'    => 'Match',
				'alias'    => 'match_live_lineups',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Referees',
				'group'    => 'Match',
				'alias'    => 'match_live_referees',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Substitutes',
				'group'    => 'Match',
				'alias'    => 'match_live_substitutes',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Missing Players',
				'group'    => 'Match',
				'alias'    => 'match_live_missing',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Video',
				'group'    => 'Match',
				'alias'    => 'match_live_video',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Stats',
				'group'    => 'Match',
				'alias'    => 'match_live_stats',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Commentary',
				'group'    => 'Match',
				'alias'    => 'match_live_commentary',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Formation',
				'group'    => 'Match',
				'alias'    => 'match_live_formation',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Head-to-head matches',
				'group'    => 'Match',
				'alias'    => 'match_live_h2h',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'no_data_text' ],
			],
			[
				'name'     => 'Timeline',
				'group'    => 'Match',
				'alias'    => 'match_live_timeline',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Match Gallery',
				'group'    => 'Match',
				'alias'    => 'match_live_gallery',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Home Club Header',
				'group'    => 'Match',
				'alias'    => 'match_home_club_header',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'width' ],
			],
			[
				'name'     => 'Away Club Header',
				'group'    => 'Match',
				'alias'    => 'match_away_club_header',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'width' ],
			],
		];

		if ( 'advice_comparison' === AnWPFL_Premium_API::get_config_value( 'predictions_data' ) ) {
			$builder_elements[] = [
				'name'     => 'Prediction',
				'group'    => 'Match',
				'alias'    => 'match_live_predictions',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			];
		}

		return $builder_elements;
	}

	/**
	 * Render Match Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_header( $block, $post_id ) {
		$data = anwp_football_leagues_premium()->match->get_match_data( $post_id );

		if ( anwp_football_leagues_premium()->match_public->is_front_edit_enabled() && anwp_football_leagues_premium()->match_public->has_user_cap_front_edit( $post_id ) ) {
			anwp_football_leagues()->load_partial( $data, 'match/match-edit' );
		}

		echo '<div class="anwp-fl-builder-block anwp-col-12 ' . esc_attr( $block->classes ) . '">';

		anwp_football_leagues()->load_partial( $data, 'match/match' );

		echo '</div>';
	}

	/**
	 * Render Match Scoreboard
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_scoreboard( $block, $post_id ) {
		$data = anwp_football_leagues_premium()->match->get_match_data( $post_id );

		if ( anwp_football_leagues_premium()->match_public->is_front_edit_enabled() && anwp_football_leagues_premium()->match_public->has_user_cap_front_edit( $post_id ) ) {
			anwp_football_leagues()->load_partial( $data, 'match/match-edit' );
		}

		echo '<div class="anwp-fl-builder-block anwp-col-12 ' . esc_attr( $block->classes ) . '">';

		anwp_football_leagues()->load_partial( $data, 'match/match-scoreboard' );

		echo '</div>';
	}

	/**
	 * Render Match Prediction
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_predictions( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'predictions' );
	}

	/**
	 * Render Match Summary
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_summary( $block, $post_id ) {
		$classes = anwp_football_leagues_premium()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		$header = '';
		if ( trim( $block->header ) ) {
			$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
		}

		$custom_content = get_post_meta( $post_id, '_anwpfl_summary', true );

		if ( ! empty( $custom_content ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">' . do_shortcode( wpautop( $custom_content ) ) . '</div></div>';
		}
	}

	/**
	 * Render Match Bottom Content
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_bottom_content( $block, $post_id ) {
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
	 * Render Match Staff
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_custom_code( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'custom-code' );
	}

	/**
	 * Render Match Latest
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_latest( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'latest' );
	}

	/**
	 * Render Match Lineups
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_lineups( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'lineups' );
	}

	/**
	 * Render Match Referees
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_referees( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'referees' );
	}

	/**
	 * Render Match Substitutes
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_substitutes( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'substitutes' );
	}

	/**
	 * Render Match Missing Players
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_missing( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'missing' );
	}

	/**
	 * Render Match Video
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_video( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'video' );
	}

	/**
	 * Render Match Stats
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_stats( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'stats' );
	}

	/**
	 * Render Match Commentary
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_commentary( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'commentary', true );
	}

	/**
	 * Render Match Formation
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_formation( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'formation' );
	}

	/**
	 * Render Match H2H
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_h2h( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'h2h' );
	}

	/**
	 * Render Match Gallery
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_gallery( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'gallery' );
	}

	/**
	 * Render Match Timeline
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_live_timeline( $block, $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'timeline', true );
	}

	/**
	 * Get match section content.
	 *
	 * @param object $block
	 * @param int    $post_id
	 * @param string $section
	 * @param bool   $force_load
	 *
	 * @return string
	 */
	private function get_section_content( $block, $post_id, $section, $force_load = false ) {

		ob_start();

		$data = anwp_fl_pro()->match->get_match_data( $post_id );

		$data['header']  = false;
		$data['builder'] = 'match-live';

		if ( 'stats' === $section ) {
			$stats_layout = ( AnWPFL_Premium_Options::get_value( 'match_stats_layout', '' ) );
			anwp_fl()->load_partial( $data, 'match/match-stats', sanitize_key( $stats_layout ) );
		} else {
			anwp_fl()->load_partial( $data, 'match/match-' . sanitize_key( $section ) );
		}

		$html_content = ob_get_clean();

		if ( empty( $html_content ) && ! $force_load && ! empty( $block->no_data_text ) ) {
			ob_start();

			if ( trim( $block->no_data_text ) ) {
				anwp_fl()->load_partial(
					[
						'no_data_text' => $block->no_data_text,
					],
					'general/no-data'
				);
			}

			$html_content = ob_get_clean();
		}

		if ( empty( $html_content ) && ! $force_load ) {
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

	/**
	 * Get List of Predefined Layouts.
	 *
	 * @return array
	 */
	public function get_predefined_layouts() {
		return [
			[
				'id'            => 1,
				'title'         => 'LIVE Game',
				'description'   => 'Basic layout for LIVE game.',
				'layout_top'    => [
					[
						'name'     => 'Scoreboard',
						'group'    => 'Match',
						'alias'    => 'match_live_scoreboard',
						'header'   => '',
						'text'     => '',
						'width'    => '',
						'classes'  => '',
						'supports' => [],
					],
					[
						'name'     => 'Timeline',
						'group'    => 'Match',
						'alias'    => 'match_live_timeline',
						'header'   => '',
						'text'     => '',
						'width'    => '',
						'classes'  => '',
						'supports' => [ 'header', 'width' ],
					],
					[
						'name'     => 'Shortcode',
						'group'    => 'Global',
						'alias'    => 'global_shortcode',
						'header'   => 'MatchDay Matches',
						'text'     => '[anwpfl-matches-scoreboard competition_id="%competition_id%" show_secondary="0" limit="0" filter_by_matchweeks="%matchweek%" show_match_datetime="1" club_links="1" club_titles="1" today_focus="0" priority="0" autoplay="1" loop="1"]',
						'width'    => '',
						'classes'  => '',
						'supports' => [ 'header', 'text', 'width' ],
					],
				],
				'layout_bottom' => [
					[
						'name'     => 'Referees',
						'group'    => 'Match',
						'alias'    => 'match_live_referees',
						'header'   => '',
						'text'     => '',
						'width'    => '',
						'classes'  => '',
						'supports' => [ 'header', 'width' ],
					],
				],
				'tabs'          => [
					[
						'title'  => 'Commentary',
						'id'     => 1,
						'layout' => [
							[
								'name'     => 'Commentary',
								'group'    => 'Match',
								'alias'    => 'match_live_commentary',
								'header'   => '',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
						],
					],
					[
						'title'  => 'LineUps',
						'id'     => 2,
						'layout' => [
							[
								'name'     => 'Formation',
								'group'    => 'Match',
								'alias'    => 'match_live_formation',
								'header'   => '',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
							[
								'name'     => 'LineUps',
								'group'    => 'Match',
								'alias'    => 'match_live_lineups',
								'header'   => '',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
						],
					],
					[
						'title'  => 'Latest Matches',
						'id'     => 3,
						'layout' => [
							[
								'name'     => 'Latest Matches',
								'group'    => 'Match',
								'alias'    => 'match_live_latest',
								'header'   => 'Latest Matches',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Render Home Club Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 *
	 * @since 0.11.10
	 */
	public function render_match_home_club_header( $block, int $post_id ) {
		$classes = anwp_fl_pro()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		ob_start();
		?>
		<div class="anwp-fl-builder-block <?php echo esc_attr( $classes ); ?>">
			<div class="anwp-block-content">
				<?php
				anwp_fl()->load_partial(
					[
						'club_id' => absint( anwp_fl()->match->get_game_data( $post_id )['home_club'] ),
					],
					'club/club-title'
				);
				?>
			</div>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Render Away Club Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 *
	 * @since 0.11.10
	 */
	public function render_match_away_club_header( $block, int $post_id ) {
		$classes = anwp_fl_pro()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		ob_start();
		?>
		<div class="anwp-fl-builder-block <?php echo esc_attr( $classes ); ?>">
			<div class="anwp-block-content">
				<?php
				anwp_fl()->load_partial(
					[
						'club_id' => absint( anwp_fl()->match->get_game_data( $post_id )['away_club'] ),
						'is_home' => false,
					],
					'club/club-title'
				);
				?>
			</div>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}
}

return new AnWPFL_Premium_Builder_Match_Live();
