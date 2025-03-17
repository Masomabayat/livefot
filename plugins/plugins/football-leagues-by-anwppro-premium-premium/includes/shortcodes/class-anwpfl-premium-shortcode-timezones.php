<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > TimeZone
 *
 * @since   0.11.2
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > TimeZone
 */
class AnWPFL_Premium_Shortcode_Timezones {

	private $shortcode = 'anwpfl-timezones';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'init', [ $this, 'shortcode_init' ] );
	}

	/**
	 * Add shortcode.
	 */
	public function shortcode_init() {
		add_shortcode( $this->shortcode, [ $this, 'render_shortcode' ] );
	}

	/**
	 * Rendering shortcode.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function render_shortcode( $atts ) {

		$defaults = [
			'clock_icon'                 => 1,
			'default_classes'            => 1,
			'text'                       => '',
			'text_utc'                   => __( 'UTC', 'anwp-football-leagues-premium' ),
			'text_auto'                  => _x( 'auto', 'user timezone', 'anwp-football-leagues-premium' ),
			'text_save'                  => __( 'Save', 'anwp-football-leagues-premium' ),
			'text_change_your_time_zone' => __( 'Change Your Time Zone', 'anwp-football-leagues-premium' ),
			'custom_classes'             => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-timezones', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Timezones();
