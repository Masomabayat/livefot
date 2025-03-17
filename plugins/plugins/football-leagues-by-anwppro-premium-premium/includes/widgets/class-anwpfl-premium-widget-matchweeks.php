<?php
/**
 * AnWP Football Leagues Premium :: Widget >> MatchWeeks.
 *
 * @since   0.1.0
 * @package AnWP_Football_Leagues_Premium
 */

class AnWPFL_Premium_Widget_Matchweeks extends AnWPFL_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'anwpfl-widget-premium-matchweeks';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'FL+ MatchWeeks Slider', 'anwp-football-leagues-premium' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show MatchWeeks as sliding tabs.', 'anwp-football-leagues-premium' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'anwpfl-widget-premium-matchweeks';
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
				'label' => esc_html__( 'Title:', 'anwp-football-leagues' ),
			],
			[
				'id'      => 'competition_id',
				'type'    => 'competition_id',
				'label'   => esc_html__( 'Competition ID', 'anwp-football-leagues' ),
				'default' => '',
				'single'  => 'yes',
			],
			[
				'id'    => 'matchweek',
				'type'  => 'text',
				'label' => esc_html__( 'Active MatchWeek:', 'anwp-football-leagues-premium' ),
			],
			[
				'id'      => 'show_club_logos',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show club logos', 'anwp-football-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'show_match_datetime',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show match datetime', 'anwp-football-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'show_club_name',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show Club Name', 'anwp-football-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'match_card',
				'type'    => 'select',
				'label'   => esc_html__( 'Layout', 'anwp-football-leagues' ),
				'default' => 'simple',
				'options' => [
					'simple' => esc_html__( 'Default', 'anwp-football-leagues' ),
					'modern' => esc_html__( 'Modern', 'anwp-football-leagues' ),
				],
			],
		];
	}
}
