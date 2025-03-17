<?php
/**
 * Add Shortcodes Premium Button in TinyMCE.
 *
 * @since   0.5.4
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode
 */
class AnWPFL_Premium_Shortcode {

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
		add_action( 'anwpfl/shortcodes/selector_bottom', [ $this, 'add_shortcode_options' ] );
		add_action( 'anwpfl/shortcodes/modal_form_shortcode', [ $this, 'get_modal_form' ] );
	}

	/**
	 * Add options to the shortcode dropdown list.
	 *
	 * @since 0.8.6
	 */
	public function add_shortcode_options() {

		/**
		 * Get all shortcode options.
		 *
		 * @param array $data Options
		 *
		 * @since 0.11.10
		 */
		$available_shortcodes = apply_filters( 'anwpfl/shortcode-pro/get_shortcode_options', [] );

		if ( ! empty( $available_shortcodes ) && is_array( $available_shortcodes ) ) :

			asort( $available_shortcodes );

			foreach ( $available_shortcodes as $shortcode_slug => $shortcode_name ) :
				?>
				<option value="<?php echo esc_attr( $shortcode_slug ); ?>">&#128312; <?php echo esc_html( $shortcode_name ); ?></option>
				<?php
			endforeach;
		endif;
	}

	/**
	 * Render shortcode form.
	 *
	 * @param string $shortcode
	 *
	 * @since 0.8.6
	 */
	public function get_modal_form( $shortcode ) {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->render_docs_link( $shortcode ); // ToDo move to own classes

		/**
		 * Render form with shortcode options.
		 *
		 * @since 0.11.10
		 */
		do_action( 'anwpfl/shortcode-pro/get_shortcode_form_' . sanitize_text_field( $shortcode ) );
	}

	/**
	 * Renders documentation link.
	 *
	 * @param string $shortcode
	 *
	 * @return string
	 * @since 0.8.6
	 */
	private function render_docs_link( $shortcode ) {

		if ( empty( $shortcode ) ) {
			return '';
		}

		$shortcode_link  = '';
		$shortcode_title = '';

		switch ( $shortcode ) {
			case 'results-matrix':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/165-results-matrix-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Results Matrix', 'anwp-football-leagues-premium' );
				break;

			case 'stats-players':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/40-players-stats-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Stats :: Players', 'anwp-football-leagues-premium' );
				break;

			case 'stats-clubs':
				$shortcode_link  = 'https://anwppro.userecho.com/en/knowledge-bases/2/articles/1385-stats-clubs';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Stats :: Clubs', 'anwp-football-leagues-premium' );
				break;

			case 'stats-club':
				$shortcode_link  = 'https://anwppro.userecho.com/en/knowledge-bases/2/articles/1362-club-stats';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Club Stats', 'anwp-football-leagues-premium' );
				break;

			case 'matchweeks-slides':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/167-matchweeks-slides-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'MatchWeeks Slides', 'anwp-football-leagues-premium' );
				break;

			case 'matches-scoreboard':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/35-matches-horizontal-scoreboard-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Matches Horizontal Scoreboard', 'anwp-football-leagues-premium' );
				break;

			case 'bracket':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/178-bracket-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Bracket Layout', 'anwp-football-leagues-premium' );
				break;

			case 'transfers':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/460-transfers-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Transfers', 'anwp-football-leagues-premium' );
				break;

			case 'birthdays':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/578-birthdays-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Birthdays', 'anwp-football-leagues-premium' );
				break;

			case 'tag-posts':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/661-tag-posts-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Tag Posts', 'anwp-football-leagues-premium' );
				break;

			case 'matches-h2h':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/775-head-to-head-matches-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Head to Head Matches', 'anwp-football-leagues-premium' );
				break;

			case 'standings':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/791-standings-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Standings', 'anwp-football-leagues-premium' );
				break;

			case 'missing-players':
				$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/800-missing-players-and-cards-shortcode';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Missing Players and Cards', 'anwp-football-leagues-premium' );
				break;

			case 'charts-goals-15':
				$shortcode_link  = 'https://anwppro.userecho.com/en/knowledge-bases/2/articles/1016-charts-goals-per-15-min';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Charts: Team Goals per 15 min', 'anwp-football-leagues-premium' );
				break;

			case 'charts-team-defaults':
				$shortcode_link  = 'https://anwppro.userecho.com/en/knowledge-bases/2/articles/1018-charts-team-default-stats';
				$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Charts: Team Default Stats', 'anwp-football-leagues-premium' );
				break;
		}

		/**
		 * Modify shortcode documentation link.
		 *
		 * @param string $shortcode_link
		 * @param string $shortcode
		 *
		 * @since 0.10.8
		 */
		$shortcode_link = apply_filters( 'anwpfl/shortcode/docs_link', $shortcode_link, $shortcode );

		/**
		 * Modify shortcode title.
		 *
		 * @param string $shortcode_title
		 * @param string $shortcode
		 *
		 * @since 0.10.8
		 */
		$shortcode_title = apply_filters( 'anwpfl/shortcode/docs_title', $shortcode_title, $shortcode );

		if ( empty( $shortcode_title ) ) {
			return '';
		}

		$output = '<div class="anwp-shortcode-docs-link">';

		$output .= '<svg class="anwp-icon anwp-icon--octi anwp-icon--s16"><use xlink:href="#icon-book"></use></svg>';
		$output .= '<b class="mx-2">' . esc_html__( 'Documentation', 'anwp-football-leagues' ) . ':</b> ';
		$output .= '<a target="_blank" href="' . esc_url( $shortcode_link ) . '">' . esc_html( $shortcode_title ) . '</a>';
		$output .= '</div>';

		return $output;
	}
}

// Bump
new AnWPFL_Premium_Shortcode();
