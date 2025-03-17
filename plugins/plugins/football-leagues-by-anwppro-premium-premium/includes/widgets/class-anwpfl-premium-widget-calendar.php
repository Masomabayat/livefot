<?php
/**
 * AnWP Football Leagues Premium :: Widget >> Calendar.
 *
 * @since   0.5.10 (Premium)
 * @package AnWP_Football_Leagues_Premium
 */

class AnWPFL_Premium_Widget_Calendar extends AnWPFL_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'anwpfl-widget-premium-calendar';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'FL+ Calendar', 'anwp-football-leagues-premium' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show Matches for selected date.', 'anwp-football-leagues-premium' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'anwpfl-widget-premium-calendar';
	}

	/**
	 * Get widget options fields.
	 *
	 * @return array
	 */
	protected function get_widget_fields() {
		return [
			[
				'id'      => 'title',
				'type'    => 'text',
				'label'   => esc_html__( 'Title:', 'anwp-football-leagues' ),
				'default' => '',
			],
			[
				'id'      => 'competition_id',
				'type'    => 'competition_id',
				'label'   => esc_html__( 'Competition ID', 'anwp-football-leagues' ),
				'default' => '',
				'single'  => 'yes',
			],
			[
				'id'    => 'show_secondary',
				'type'  => 'checkbox',
				'label' => esc_html__( 'Include matches from secondary stages', 'anwp-football-leagues' ),
			],
			[
				'id'          => 'club_id',
				'type'        => 'club_id',
				'label'       => esc_html__( 'Filter by Club IDs', 'anwp-football-leagues-premium' ),
				'description' => esc_html__( 'Club IDs, separated by commas.', 'anwp-football-leagues' ),
				'single'      => 'no',
			],
			[
				'id'      => 'show_club_logos',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show club logos', 'anwp-football-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'show_club_name',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show Club Name', 'anwp-football-leagues' ),
				'default' => 1,
			],
			[
				'id'      => 'group_by_competition',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Group by competition', 'anwp-football-leagues-premium' ),
				'default' => 0,
			],
			[
				'id'      => 'group_by_time',
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Group by time', 'anwp-football-leagues-premium' ),
				'default' => 0,
			],
			[
				'id'      => 'layout',
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
