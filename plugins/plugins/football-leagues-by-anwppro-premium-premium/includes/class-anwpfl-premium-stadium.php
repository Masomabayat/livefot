<?php
/**
 * AnWP Football Leagues Premium :: Stadium
 *
 * @since 0.6.0
 */
class AnWPFL_Premium_Stadium {

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

		// Init hooks
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Premium Metabox
		add_action( 'anwpfl/cmb2_tabs_control/stadium', [ $this, 'add_premium_metabox_tab' ] );
		add_filter( 'anwpfl/cmb2_tabs_content/stadium', [ $this, 'add_premium_metabox_options' ] );
	}

	/**
	 * Renders premium tab control.
	 *
	 * @since 0.5.11
	 */
	public function add_premium_metabox_tab() {
		ob_start();
		?>
		<div class="p-3 anwp-metabox-tabs__control-item anwp-metabox-tabs__control-item--premium" data-target="#anwp-tabs-premium-stadium_metabox">
			<svg class="anwp-icon anwp-icon--octi d-inline-block"><use xlink:href="#icon-star"></use></svg>
			<span class="d-block"><?php echo esc_html__( 'Premium Options', 'anwp-football-leagues-premium' ); ?></span>
		</div>
		<?php
		echo ob_get_clean(); // WPCS: XSS ok.
	}

	/**
	 * Adds fields to the stadium metabox.
	 *
	 * @return array
	 * @since 0.5.11
	 */
	public function add_premium_metabox_options() {
		$prefix = '_anwpfl_';

		// Init fields array
		$fields = [];

		$fields[] = [
			'name'         => esc_html__( 'Match Scoreboard Image', 'anwp-football-leagues-premium' ),
			'id'           => $prefix . 'match_scoreboard_image',
			'type'         => 'file',
			'options'      => [
				'url' => false,
			],
			'query_args'   => [
				'type' => 'image',
			],
			'preview_size' => 'medium',
			'before_row'   => '<div id="anwp-tabs-premium-stadium_metabox" class="anwp-metabox-tabs__content-item d-none">',
			'after_row'    => '</div>',
		];

		return $fields;
	}

	/**
	 * Get Stadium POST season.
	 *
	 * @param int $stadium_id
	 *
	 * @return int
	 * @since 0.11.7
	 */
	public function get_post_season( $stadium_id ) {

		static $season_id = null;

		if ( null === $season_id ) {

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( ! empty( $_GET['season'] ) ) {

				// phpcs:ignore WordPress.Security.NonceVerification
				$maybe_season_id = anwp_football_leagues()->season->get_season_id_by_slug( sanitize_key( $_GET['season'] ) );

				if ( absint( $maybe_season_id ) ) {

					$season_id = absint( $maybe_season_id );

					return $season_id;
				}
			}

			// Get Season ID
			$season_id = anwp_football_leagues()->get_active_stadium_season( $stadium_id );
		}

		return absint( $season_id );
	}
}
