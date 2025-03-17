<?php
/**
 * AnWP Football Leagues Premium :: Widget >> Stat Players.
 *
 * @since   0.12.0
 * @package AnWP_Football_Leagues_Premium
 */

class AnWPFL_Premium_Widget_Stat_Players extends AnWPFL_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'anwpfl-widget-premium-stat-players';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return 'FL+ ' . esc_html__( 'Stat :: Players (Single Stat Value)', 'anwp-football-leagues-premium' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return '';
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'anwpfl-widget-premium-stat-players';
	}

	/**
	 * Get widget options fields.
	 *
	 * @return array
	 */
	protected function get_widget_fields() {
		return [
			[
				'id'    => 'title',
				'type'  => 'text',
				'label' => esc_html__( 'Title', 'anwp-football-leagues' ),
			],
			[
				'id'         => 'stat',
				'type'       => 'select',
				'label'      => esc_html__( 'Stat Value', 'anwp-football-leagues-premium' ),
				'show_empty' => '- ' . esc_html__( 'select', 'anwp-football-leagues' ) . ' -',
				'default'    => '',
				'options_cb' => [ anwp_football_leagues_premium()->player, 'get_stat_players_options' ],
			],
			[
				'id'         => 'season_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Season', 'anwp-football-leagues' ),
				'show_empty' => esc_html__( '- select season -', 'anwp-football-leagues' ),
				'default'    => '',
				'options_cb' => [ anwp_football_leagues()->season, 'get_seasons_options' ],
			],
			[
				'id'         => 'league_id',
				'type'       => 'select',
				'label'      => esc_html__( 'League', 'anwp-football-leagues' ),
				'show_empty' => esc_html__( '- select league -', 'anwp-football-leagues' ),
				'default'    => '',
				'options_cb' => [ anwp_football_leagues()->league, 'get_league_options' ],
			],
			[
				'id'     => 'club_id',
				'type'   => 'club_id',
				'label'  => esc_html__( 'Club ID', 'anwp-football-leagues' ),
				'single' => 'yes',
			],
			[
				'id'      => 'competition_id',
				'type'    => 'competition_id',
				'label'   => esc_html__( 'Competition ID', 'anwp-football-leagues' ),
				'default' => '',
				'single'  => 'yes',
			],
			[
				'id'    => 'multistage',
				'type'  => 'checkbox',
				'label' => esc_html__( 'Include All Stages (in Multistage competition)', 'anwp-football-leagues' ),
			],
			[
				'id'      => 'type',
				'type'    => 'select',
				'label'   => esc_html__( 'Type', 'anwp-football-leagues' ),
				'default' => '',
				'options' => [
					''  => esc_html__( 'All', 'anwp-football-leagues' ),
					'p' => esc_html__( 'Players', 'anwp-football-leagues' ),
					'g' => esc_html__( 'Goalkeepers', 'anwp-football-leagues' ),
				],
			],
			[
				'id'      => 'limit',
				'type'    => 'number',
				'label'   => esc_html__( 'Players Limit (0 - for all)', 'anwp-football-leagues' ),
				'default' => 10,
			],
			[
				'id'      => 'soft_limit',
				'type'    => 'select',
				'label'   => esc_html__( 'Soft Limit', 'anwp-football-leagues' ),
				'default' => 'yes',
				'options' => [
					'no'  => esc_html__( 'No', 'anwp-football-leagues' ),
					'yes' => esc_html__( 'Yes', 'anwp-football-leagues' ),
				],
			],
			[
				'id'      => 'first_em',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Emphasize the first player', 'anwp-football-leagues-premium' ),
				'default' => 1,
			],
			[
				'id'      => 'links',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show link to Player profile', 'anwp-football-leagues-premium' ),
				'default' => 0,
			],
			[
				'id'      => 'photos',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show Photo', 'anwp-football-leagues' ),
				'default' => 0,
			],
			[
				'id'      => 'games_played',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show number of games played', 'anwp-football-leagues-premium' ),
				'default' => 0,
			],
			[
				'id'      => 'show_full',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show full list', 'anwp-football-leagues-premium' ),
				'default' => 0,
			],
			[
				'id'      => 'hide_zero',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Hide Zeros', 'anwp-football-leagues' ),
				'default' => 1,
			],
		];
	}
}
