<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnWP Football Leagues Premium :: Builder :: Match
 */
class AnWPFL_Premium_Builder_Match implements AnWPFL_Premium_Builder_Interface {

	/**
	 * Get builder type name.
	 *
	 * @return string
	 */
	public function get_builder_type_name(): string {
		return esc_html__( 'Match', 'anwp-football-leagues' );
	}

	/**
	 * Rendering admin list content (builder type).
	 *
	 * @param int $post_id
	 */
	public function admin_list_column_display( int $post_id ) {
		$ids = get_post_meta( $post_id, '_fl_builder_match_ids', true );
		echo '<strong>' . esc_html__( 'IDs', 'anwp-football-leagues-premium' ) . ':</strong> ' . esc_html( $ids ?: '-' ) . '<br/>';

		$status = get_post_meta( $post_id, '_fl_builder_match_type', true );
		echo '<strong>' . esc_html__( 'Status', 'anwp-football-leagues' ) . ':</strong> ' . esc_html( empty( $status ) ? '-' : implode( ', ', $status ) ) . '<br/>';

		// Leagues
		$league      = get_post_meta( $post_id, '_fl_builder_match_league', true );
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
		return 'https://anwppro.userecho.com/knowledge-bases/2/articles/541-match-available-block-types';
	}

	/**
	 * Get builder type option.
	 *
	 * @return string
	 */
	public function get_builder_type_option(): string {
		return '<option value="match">' . esc_html__( 'Match', 'anwp-football-leagues' ) . '</option>';
	}

	/**
	 * Get builder wrapper classes.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 * @throws Exception
	 */
	public function get_builder_wrapper_classes( WP_Post $post ): string {

		$wrapper_classes = '';
		$game_data       = anwp_fl()->match->get_game_data( $post->ID );

		$wrapper_classes .= ' position-relative match match__inner match-' . absint( $post->ID );
		$wrapper_classes .= ' match-status__' . absint( $game_data['finished'] ? 'result' : 'fixture' );

		if ( anwp_fl_pro()->match->maybe_match_live( $post ) ) {
			$wrapper_classes .= ' match__inner--live';
		}

		if ( ! absint( $game_data['finished'] ) ) {
			if ( ( ! empty( $post->_anwpfl_live_status ) || ! empty( $post->_anwpfl_match_live_commentary ) ) || anwp_fl_pro()->live->is_api_game_active( $post->ID ) ) {
				$wrapper_classes .= ' fl-match-live-layout';
			}
		}

		return $wrapper_classes;
	}

	/**
	 * Get builder wrapper attributes.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function get_builder_wrapper_attributes( WP_Post $post ): string {
		return ' data-id="' . absint( $post->ID ) . '"';
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_dynamic_variables(): array {
		return [
			'%match_id%',
			'%competition_id%',
			'%season_id%',
			'%league_id%',
			'%matchweek%',
			'%home_id%',
			'%away_id%',
			'%home_goals%',
			'%away_goals%',
			'%kickoff%',
			'%home_club_title%',
			'%away_club_title%',
			'%competition_title%',
			'%season_title%',
			'%league_title%',
			'%match_date%',
			'%match_time%',
			'%referee_id%',
			'%referee_name%',
		];
	}

	/**
	 * Get dynamic variables
	 *
	 * @return array
	 */
	public function get_conditional_tags(): array {
		return [
			'%referee_name%',
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
		$block_text = str_ireplace( '%league_id%', $match_league_id, $block_text );
		$block_text = str_ireplace( '%matchweek%', $match_match_week, $block_text );
		$block_text = str_ireplace( '%home_id%', $match_home_id, $block_text );
		$block_text = str_ireplace( '%away_id%', $match_away_id, $block_text );
		$block_text = str_ireplace( '%home_goals%', absint( $game_data['home_goals'] ), $block_text );
		$block_text = str_ireplace( '%away_goals%', absint( $game_data['away_goals'] ), $block_text );

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

		// %referee_id%
		if ( strpos( $block_text, '%referee_id%' ) !== false ) {
			$block_text = str_ireplace( '%referee_id%', $game_data['referee'] ?: '', $block_text );
		}

		// %referee_name%
		if ( strpos( $block_text, '%referee_name%' ) !== false ) {
			$block_text = preg_replace( '/\[IF%referee_name%\](.+?)\[ENDIF%referee_name%\]/is', $game_data['referee'] ? '$1' : '', $block_text );
			$block_text = str_ireplace( '%referee_name%', $game_data['referee'] ? anwp_fl()->referee->get_referee( $game_data['referee'] )->name : '', $block_text );
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

		$game_id   = $post->ID;
		$game_data = anwp_fl()->match->get_game_data( $game_id );

		if ( empty( $game_data ) ) {
			return false;
		}

		// Get all layouts
		$args = [
			'post_type'   => 'anwp_fl_builder',
			'numberposts' => - 1,
			'meta_key'    => '_fl_builder_type',
			'meta_value'  => 'match',
		];

		$layouts = get_posts( $args );

		/*
		|--------------------------------------------------------------------
		| High priority >> Match ID
		|--------------------------------------------------------------------
		*/
		foreach ( $layouts as $layout ) {
			$ids = trim( get_post_meta( $layout->ID, '_fl_builder_match_ids', true ) );

			if ( $ids ) {
				$ids = wp_parse_id_list( $ids );

				if ( in_array( $game_id, $ids, true ) ) {
					return $layout->ID;
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Low priority >> Match Status
		|--------------------------------------------------------------------
		*/
		foreach ( $layouts as $layout ) {
			$types     = get_post_meta( $layout->ID, '_fl_builder_match_type', true );
			$league_id = get_post_meta( $layout->ID, '_fl_builder_match_league', true );

			if ( absint( $league_id ) && absint( $league_id ) !== absint( $game_data['league_id'] ) ) {
				continue;
			}

			if ( ! empty( $types ) && is_array( $types ) ) {

				if ( absint( $game_data['finished'] ) && in_array( 'finished', $types, true ) ) {
					return $layout->ID;
				} elseif ( ! absint( $game_data['finished'] ) && in_array( 'upcoming', $types, true ) ) {
					return $layout->ID;
				}
			}
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
				'alias'    => 'match_header',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Scoreboard',
				'group'    => 'Match',
				'alias'    => 'match_scoreboard',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [],
			],
			[
				'name'     => 'Summary',
				'group'    => 'Match',
				'alias'    => 'match_summary',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Bottom Content',
				'group'    => 'Match',
				'alias'    => 'match_bottom_content',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Custom Code',
				'group'    => 'Match',
				'alias'    => 'match_custom_code',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Cards',
				'group'    => 'Match',
				'alias'    => 'match_cards',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Goals',
				'group'    => 'Match',
				'alias'    => 'match_goals',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Latest Matches',
				'group'    => 'Match',
				'alias'    => 'match_latest',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'no_data_text' ],
			],
			[
				'name'     => 'LineUps',
				'group'    => 'Match',
				'alias'    => 'match_lineups',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Missed Penalties',
				'group'    => 'Match',
				'alias'    => 'match_missed_penalties',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Penalty Shootout',
				'group'    => 'Match',
				'alias'    => 'match_penalty_shootout',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Referees',
				'group'    => 'Match',
				'alias'    => 'match_referees',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Substitutes',
				'group'    => 'Match',
				'alias'    => 'match_substitutes',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Missing Players',
				'group'    => 'Match',
				'alias'    => 'match_missing',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Video',
				'group'    => 'Match',
				'alias'    => 'match_video',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Stats',
				'group'    => 'Match',
				'alias'    => 'match_stats',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Commentary',
				'group'    => 'Match',
				'alias'    => 'match_commentary',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Formation',
				'group'    => 'Match',
				'alias'    => 'match_formation',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Head-to-head matches',
				'group'    => 'Match',
				'alias'    => 'match_h2h',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width', 'no_data_text' ],
			],
			[
				'name'     => 'Timeline',
				'group'    => 'Match',
				'alias'    => 'match_timeline',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Custom Player Stats',
				'group'    => 'Match',
				'alias'    => 'match_player_stats',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
			[
				'name'     => 'Match Gallery',
				'group'    => 'Match',
				'alias'    => 'match_gallery',
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
			[
				'name'     => 'Odds',
				'group'    => 'Match',
				'alias'    => 'match_odds',
				'header'   => '',
				'text'     => '',
				'width'    => '',
				'classes'  => '',
				'supports' => [ 'header', 'width' ],
			],
		];

		if ( 'advice_comparison' === AnWPFL_Premium_API::get_config_value( 'predictions_data' ) ) {
			$builder_elements[] = [
				'name'     => 'Prediction',
				'group'    => 'Match',
				'alias'    => 'match_predictions',
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
	 * Render Match Staff
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_custom_code( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'custom-code' );
	}

	/**
	 * Render Match Cards
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_cards( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'cards' );
	}

	/**
	 * Render Match Goals
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_goals( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'goals' );
	}

	/**
	 * Render Match Latest
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_latest( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'latest' );
	}

	/**
	 * Render Match Lineups
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_lineups( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'lineups' );
	}

	/**
	 * Render Match Prediction
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_predictions( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'predictions' );
	}

	/**
	 * Render Match Missed Penalties
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_missed_penalties( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'missed_penalties' );
	}

	/**
	 * Render Match Penalty Shootout
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_penalty_shootout( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'penalty_shootout' );
	}

	/**
	 * Render Match Referees
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_referees( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'referees' );
	}

	/**
	 * Render Match Substitutes
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_substitutes( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'substitutes' );
	}

	/**
	 * Render Match Missing Players
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_missing( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'missing' );
	}

	/**
	 * Render Match Video
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_video( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'video' );
	}

	/**
	 * Render Match Stats
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_stats( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'stats' );
	}

	/**
	 * Render Match Commentary
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_commentary( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'commentary' );
	}

	/**
	 * Render Match Formation
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_formation( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'formation' );
	}

	/**
	 * Render Match H2H
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_h2h( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'h2h' );
	}

	/**
	 * Render Match Gallery
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_gallery( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'gallery' );
	}

	/**
	 * Render Match Timeline
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_timeline( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'timeline' );
	}

	/**
	 * Render Match Timeline
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_odds( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'odds' );
	}

	/**
	 * Render Match player-stats
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_player_stats( $block, int $post_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_section_content( $block, $post_id, 'player-stats' );
	}

	/**
	 * Get match section content.
	 *
	 * @param object $block
	 * @param int    $post_id
	 * @param string $section
	 *
	 * @return string
	 */
	private function get_section_content( $block, int $post_id, string $section ): string {

		ob_start();

		$data           = anwp_football_leagues_premium()->match->get_match_data( $post_id );
		$data['header'] = false;

		if ( 'stats' === $section ) {
			$stats_layout = ( AnWPFL_Premium_Options::get_value( 'match_stats_layout', '' ) );
			anwp_football_leagues()->load_partial( $data, 'match/match-stats', sanitize_key( $stats_layout ) );
		} else {
			anwp_football_leagues()->load_partial( $data, 'match/match-' . sanitize_key( $section ) );
		}

		$html_content = ob_get_clean();

		if ( empty( $html_content ) && ! empty( $block->no_data_text ) ) {
			ob_start();

			if ( trim( $block->no_data_text ) ) {
				anwp_football_leagues()->load_partial(
					[
						'no_data_text' => $block->no_data_text,
					],
					'general/no-data'
				);
			}

			$html_content = ob_get_clean();
		}

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

	/**
	 * Render Match Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_header( $block, $post_id ) {
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
	public function render_match_scoreboard( $block, $post_id ) {
		$data = anwp_football_leagues_premium()->match->get_match_data( $post_id );

		if ( anwp_football_leagues_premium()->match_public->is_front_edit_enabled() && anwp_football_leagues_premium()->match_public->has_user_cap_front_edit( $post_id ) ) {
			anwp_football_leagues()->load_partial( $data, 'match/match-edit' );
		}

		echo '<div class="anwp-fl-builder-block anwp-col-12 ' . esc_attr( $block->classes ) . '">';

		anwp_football_leagues()->load_partial( $data, 'match/match-scoreboard' );

		echo '</div>';
	}

	/**
	 * Render Match Summary
	 *
	 * @param object $block
	 * @param int    $post_id
	 */
	public function render_match_summary( $block, $post_id ) {
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
	public function render_match_bottom_content( $block, $post_id ) {
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
	 * Render Home Club Header
	 *
	 * @param object $block
	 * @param int    $post_id
	 *
	 * @since 0.11.10
	 */
	public function render_match_home_club_header( $block, int $post_id ) {
		$classes = anwp_fl_pro()->builder->get_builder_width_class( $block->width ) . ' ' . $block->classes;

		$game_data = anwp_fl()->match->get_game_data( $post_id );
		$club_id   = absint( $game_data['home_club'] );

		ob_start();
		?>
		<div class="anwp-fl-builder-block <?php echo esc_attr( $classes ); ?>">
			<div class="anwp-block-content">
				<?php
				anwp_fl()->load_partial(
					[
						'club_id' => $club_id,
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

		$game_data = anwp_fl()->match->get_game_data( $post_id );
		$club_id   = absint( $game_data['away_club'] );

		ob_start();
		?>
		<div class="anwp-fl-builder-block <?php echo esc_attr( $classes ); ?>">
			<div class="anwp-block-content">
				<?php
				anwp_fl()->load_partial(
					[
						'club_id' => $club_id,
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

	/**
	 * Get List of Predefined Layouts.
	 *
	 * @return array
	 */
	public function get_predefined_layouts(): array {
		return [
			[
				'id'            => 1,
				'title'         => 'Finished Game',
				'description'   => 'Basic layout for finished game.',
				'layout_top'    => [
					[
						'name'     => 'Scoreboard',
						'group'    => 'Match',
						'alias'    => 'match_scoreboard',
						'header'   => '',
						'text'     => '',
						'width'    => '',
						'classes'  => '',
						'supports' => [],
					],
					[
						'name'     => 'Timeline',
						'group'    => 'Match',
						'alias'    => 'match_timeline',
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
						'header'   => '',
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
						'alias'    => 'match_referees',
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
								'alias'    => 'match_commentary',
								'header'   => '',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
						],
					],
					[
						'title'  => 'Line Ups',
						'id'     => 2,
						'layout' => [
							[
								'name'     => 'Formation',
								'group'    => 'Match',
								'alias'    => 'match_formation',
								'header'   => '',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
							[
								'name'     => 'LineUps',
								'group'    => 'Match',
								'alias'    => 'match_lineups',
								'header'   => '',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
							[
								'name'     => 'Substitutes',
								'group'    => 'Match',
								'alias'    => 'match_substitutes',
								'header'   => 'Substitutes',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
						],
					],
					[
						'title'  => 'Stats',
						'id'     => 3,
						'layout' => [
							[
								'name'     => 'Stats',
								'group'    => 'Match',
								'alias'    => 'match_stats',
								'header'   => '',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
							[
								'name'     => 'Custom Player Stats',
								'group'    => 'Match',
								'alias'    => 'match_player_stats',
								'header'   => '',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
						],
					],
					[
						'title'  => 'Events',
						'id'     => 4,
						'layout' => [
							[
								'name'     => 'Goals',
								'group'    => 'Match',
								'alias'    => 'match_goals',
								'header'   => 'Goals',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
							[
								'name'     => 'Cards',
								'group'    => 'Match',
								'alias'    => 'match_cards',
								'header'   => 'Cards',
								'text'     => '',
								'width'    => '',
								'classes'  => '',
								'supports' => [ 'header', 'width' ],
							],
						],
					],
				],
			],
			[
				'id'            => 2,
				'title'         => 'Upcoming Game',
				'description'   => 'Basic layout for upcoming game.',
				'layout_top'    => [
					[
						'name'     => 'Scoreboard',
						'group'    => 'Match',
						'alias'    => 'match_scoreboard',
						'header'   => '',
						'text'     => '',
						'width'    => '',
						'classes'  => '',
						'supports' => [],
					],
					[
						'name'     => 'Head-to-head matches',
						'group'    => 'Match',
						'alias'    => 'match_h2h',
						'header'   => 'Head-to-head matches',
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
					[
						'name'     => 'Latest Matches',
						'group'    => 'Match',
						'alias'    => 'match_latest',
						'header'   => 'Latest Matches',
						'text'     => '',
						'width'    => '',
						'classes'  => '',
						'supports' => [ 'header', 'width' ],
					],
				],
				'layout_bottom' => [],
				'tabs'          => [],
			],
		];
	}
}

return new AnWPFL_Premium_Builder_Match();
