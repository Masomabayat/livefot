<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Matches.
 *
 * @since   0.6.0
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Matches Scoreboard.
 */
class AnWPFL_Premium_Shortcode_Matches_Scoreboard {

	private $shortcode = 'anwpfl-matches-scoreboard';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hooks();
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

		$data['matches-scoreboard'] = __( 'Matches Horizontal Scoreboard', 'anwp-football-leagues-premium' );

		return $data;
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'init', [ $this, 'shortcode_init' ] );

		// Add shortcode option
		add_filter( 'anwpfl/shortcode-pro/get_shortcode_options', [ $this, 'add_shortcode_option' ] );

		// Load shortcode form
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_matches-scoreboard', [ $this, 'load_shortcode_form' ] );
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
					<select name="competition_id" data-fl-type="select2" id="fl-form-shortcode-id" class="postform fl-shortcode-attr fl-shortcode-select2" multiple="multiple">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->competition->get_competition_options() as $competition_id => $competition_title ) : ?>
							<option value="<?php echo esc_attr( $competition_id ); ?>"><?php echo esc_html( $competition_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_secondary"><?php echo esc_html__( 'Include matches from secondary stages', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="show_secondary" data-fl-type="select" id="fl-form-shortcode-show_secondary" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
					<span class="anwp-option-desc"><?php echo esc_html__( 'Applied for multistage main stage competitions only.', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-days_offset"><?php echo esc_html__( 'Dynamic days filter', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="days_offset" data-fl-type="text" type="text" id="fl-form-shortcode-days_offset" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'For example: "-2" from 2 days ago and newer; "2" from the day after tomorrow and newer', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-days_offset_to"><?php echo esc_html__( 'Dynamic days filter to', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="days_offset_to" data-fl-type="text" type="text" id="fl-form-shortcode-days_offset_to" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'For example: "1" - till tomorrow (tomorrow not included)', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-season_id"><?php echo esc_html__( 'Season', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="season_id" data-fl-type="select2" id="fl-form-shortcode-season_id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->season->get_seasons_options() as $season_id => $season_title ) : ?>
							<option value="<?php echo esc_attr( $season_id ); ?>"><?php echo esc_html( $season_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-type"><?php echo esc_html__( 'Match Type', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="type" data-fl-type="select" id="fl-form-shortcode-type" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'All', 'anwp-football-leagues' ); ?></option>
						<option value="result"><?php echo esc_html__( 'Result', 'anwp-football-leagues' ); ?></option>
						<option value="fixture"><?php echo esc_html__( 'Fixture', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-limit"><?php echo esc_html__( 'Matches Limit (0 - for all)', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="limit" data-fl-type="text" type="text" id="fl-form-shortcode-limit" value="20" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-stadium_id"><?php echo esc_html__( 'Filter by Stadium', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="stadium_id" data-fl-type="select2" id="fl-form-shortcode-stadium_id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->stadium->get_stadiums_options() as $stadium_id => $stadium_title ) : ?>
							<option value="<?php echo esc_attr( $stadium_id ); ?>"><?php echo esc_html( $stadium_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-filter_by_clubs"><?php echo esc_html__( 'Filter by Clubs', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="filter_by_clubs" data-fl-type="select2" id="fl-form-shortcode-filter_by_clubs" class="postform fl-shortcode-attr fl-shortcode-select2" multiple="multiple">
						<?php foreach ( anwp_football_leagues()->club->get_clubs_options() as $club_id => $club_title ) : ?>
							<option value="<?php echo esc_attr( $club_id ); ?>"><?php echo esc_html( $club_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-filter_by_matchweeks"><?php echo esc_html__( 'Filter by Matchweeks or Round IDs', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="filter_by_matchweeks" data-fl-type="text" type="text" id="fl-form-shortcode-filter_by_matchweeks" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'comma-separated list of matchweeks or rounds to filter', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_match_datetime"><?php echo esc_html__( 'Show match datetime', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="show_match_datetime" data-fl-type="select" id="fl-form-shortcode-show_match_datetime" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
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
				<th scope="row"><label for="fl-form-shortcode-club_titles"><?php echo esc_html__( 'Club Titles', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="club_titles" data-fl-type="select" id="fl-form-shortcode-club_titles" class="postform fl-shortcode-attr">
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-today_focus"><?php echo esc_html__( 'Focus on Today', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="today_focus" data-fl-type="select" id="fl-form-shortcode-today_focus" class="postform fl-shortcode-attr">
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-priority"><?php echo esc_html__( 'Priority', 'anwp-football-leagues-premium' ); ?> (>=)</label>
				</th>
				<td>
					<select name="priority" data-fl-type="select" id="fl-form-shortcode-priority" class="postform fl-shortcode-attr">
						<option value="0" selected>0</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-autoplay"><?php echo esc_html__( 'Autoplay', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="autoplay" data-fl-type="select" id="fl-form-shortcode-autoplay" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-loop"><?php echo esc_html__( 'Continuous loop mode', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="loop" data-fl-type="select" id="fl-form-shortcode-loop" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-matches-scoreboard">
		<?php
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
			'competition_id'       => '',
			'season_id'            => '',
			'stadium_id'           => '',
			'show_secondary'       => 0,
			'type'                 => '',
			'limit'                => 20,
			'filter_by'            => '',
			'filter_values'        => '',
			'filter_by_clubs'      => '',
			'filter_by_matchweeks' => '',
			'days_offset'          => '',
			'days_offset_to'       => '',
			'today_focus'          => 1,
			'show_match_datetime'  => 1,
			'club_titles'          => 1,
			'club_links'           => 1,
			'priority'             => 0,
			'loop'                 => 0,
			'autoplay'             => 0,
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		// Validate shortcode attr
		$atts['show_secondary']      = (int) $atts['show_secondary'];
		$atts['limit']               = (int) $atts['limit'];
		$atts['competition_id']      = sanitize_text_field( $atts['competition_id'] );
		$atts['season_id']           = (int) $atts['season_id'] ? (int) $atts['season_id'] : '';
		$atts['stadium_id']          = (int) $atts['stadium_id'] ? (int) $atts['stadium_id'] : '';
		$atts['club_links']          = (int) $atts['club_links'];
		$atts['show_match_datetime'] = (int) $atts['show_match_datetime'];
		$atts['club_titles']         = (int) $atts['club_titles'];
		$atts['priority']            = (int) $atts['priority'];

		$atts['type']      = in_array( $atts['type'], [ 'result', 'fixture' ], true ) ? $atts['type'] : '';
		$atts['filter_by'] = in_array( $atts['filter_by'], [ 'club', 'matchweek' ], true ) ? $atts['filter_by'] : '';

		return anwp_football_leagues()->template->shortcode_loader( 'premium-matches-scoreboard', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Matches_Scoreboard();
