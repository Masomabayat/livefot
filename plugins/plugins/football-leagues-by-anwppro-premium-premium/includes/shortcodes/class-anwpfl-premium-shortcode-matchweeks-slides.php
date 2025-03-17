<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > MatchWeeks Slides.
 *
 * @since   0.5.6
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > MatchWeeks Slides.
 */
class AnWPFL_Premium_Shortcode_Matchweeks_Slides {

	private $shortcode = 'anwpfl-matchweeks-slides';

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

		// Add shortcode option
		add_filter( 'anwpfl/shortcode-pro/get_shortcode_options', [ $this, 'add_shortcode_option' ] );

		// Load shortcode form
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_matchweeks-slides', [ $this, 'load_shortcode_form' ] );
	}

	/**
	 * Load shortcode form with options.
	 * Used in Shortcode Builder and Shortcode TinyMCE tool.
	 *
	 * @since 0.11.10
	 */
	public function load_shortcode_form() {
		?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-id"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="competition_id" data-fl-type="select2" id="fl-form-shortcode-id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<?php foreach ( anwp_football_leagues()->competition->get_competition_options() as $competition_id => $competition_title ) : ?>
							<option value="<?php echo esc_attr( $competition_id ); ?>"><?php echo esc_html( $competition_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_club_logos"><?php echo esc_html__( 'Show club logos', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="show_club_logos" data-fl-type="select" id="fl-form-shortcode-show_club_logos" class="postform fl-shortcode-attr">
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_match_datetime"><?php echo esc_html__( 'Show match datetime', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="show_match_datetime" data-fl-type="select" id="fl-form-shortcode-show_match_datetime" class="postform fl-shortcode-attr">
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-club_links"><?php echo esc_html__( 'Club links', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="club_links" data-fl-type="select" id="fl-form-shortcode-club_links" class="postform fl-shortcode-attr">
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-matchweek"><?php echo esc_html__( 'Matchweek Current', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<input name="matchweek" data-fl-type="text" type="text" id="fl-form-shortcode-matchweek" value="" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-slides_to_show"><?php echo esc_html__( 'Navigation Slides to Show', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<input name="slides_to_show" data-fl-type="text" type="text" id="fl-form-shortcode-slides_to_show" value="4" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-match_card"><?php echo esc_html__( 'Match Card', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="match_card" data-fl-type="select" id="fl-form-shortcode-match_card" class="postform fl-shortcode-attr">
						<option value=""><?php echo esc_html__( 'Default', 'anwp-football-leagues' ); ?></option>
						<option value="slim" selected><?php echo esc_html__( 'Slim', 'anwp-football-leagues-premium' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-matchweeks-slides">
		<?php
	}

	/**
	 * Add shortcode options.
	 * Used in Shortcode Builder and Shortcode TinyMCE tool.
	 *
	 * @param array $data Shortcode options.
	 *
	 * @return array
	 * @since 0.11.10
	 */
	public function add_shortcode_option( $data ) {

		$data['matchweeks-slides'] = __( 'MatchWeeks Slides', 'anwp-football-leagues-premium' );

		return $data;
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
			'competition_id'      => '',
			'group_id'            => '',
			'show_club_logos'     => 1,
			'show_match_datetime' => 1,
			'club_links'          => 1,
			'matchweek'           => '',
			'match_card'          => '',
			'slides_to_show'      => 4,
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		// Validate shortcode attr
		$atts['competition_id']      = (int) $atts['competition_id'];
		$atts['show_club_logos']     = (int) $atts['show_club_logos'];
		$atts['show_match_datetime'] = (int) $atts['show_match_datetime'];
		$atts['club_links']          = (int) $atts['club_links'];
		$atts['matchweek']           = (int) $atts['matchweek'];
		$atts['slides_to_show']      = (int) $atts['slides_to_show'];

		return anwp_football_leagues()->template->shortcode_loader( 'premium-matchweeks-slides', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Matchweeks_Slides();
