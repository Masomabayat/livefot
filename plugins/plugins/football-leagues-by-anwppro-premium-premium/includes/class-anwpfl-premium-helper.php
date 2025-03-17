<?php
/**
 * AnWP Football Leagues Premium :: Helper
 *
 * @since 0.14.19
 */
class AnWPFL_Premium_Helper {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param  AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// Save main plugin object
		$this->plugin = $plugin;
	}

	/**
	 * Parse Url params from WP Json request and return array with params.
	 *
	 * @param string $params_string
	 *
	 * @return array
	 */
	public static function parse_rest_url_params( string $params_string ): array {

		$args = [];

		foreach ( explode( '~', $params_string ) as $arg_string ) {
			$arg_parsed = explode( ':', $arg_string );

			if ( isset( $arg_parsed[1] ) ) {
				$args[ sanitize_key( $arg_parsed[0] ) ] = sanitize_text_field( str_replace( '%20', ' ', $arg_parsed[1] ) );
			}
		}

		return $args;
	}

	/**
	 * Retrieves the current time as an object using the site's timezone.
	 *
	 * @return DateTime|DateTimeImmutable
	 * @throws Exception
	 */
	public static function get_current_datetime() {
		return function_exists( 'current_datetime' ) ? current_datetime() : new DateTime( current_time( 'mysql' ) );
	}
}
