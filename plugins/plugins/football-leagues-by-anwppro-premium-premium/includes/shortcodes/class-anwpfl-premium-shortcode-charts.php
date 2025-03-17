<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Charts.
 *
 * @since   0.11.10
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Charts.
 */
class AnWPFL_Premium_Shortcode_Charts {

	private $shortcode = 'anwpfl-charts';

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

		$data['charts-goals-15']      = __( 'Charts: Team Goals per 15 min', 'anwp-football-leagues-premium' );
		$data['charts-team-defaults'] = __( 'Charts: Team Default Stats', 'anwp-football-leagues-premium' );

		return $data;
	}

	/**
	 * Load shortcode form with options.
	 * Used in Shortcode Builder and Shortcode TinyMCE tool.
	 *
	 * @since 0.11.10
	 */
	public function load_shortcode_form_charts_goals_15() {
		?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-type"><?php echo esc_html__( 'Type', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="type" data-fl-type="text" type="text" id="fl-form-shortcode-type" value="goals-15" readonly class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-club_id"><?php echo esc_html__( 'Club ID', 'anwp-football-leagues' ); ?>*</label></th>
				<td>
					<input name="club_id" data-fl-type="text" type="text" id="fl-form-shortcode-club_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="club" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-competition_id"><?php echo esc_html__( 'Competition ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="competition_id" data-fl-type="text" type="text" id="fl-form-shortcode-competition_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="competition" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-multistage"><?php echo esc_html__( 'Include matches from secondary stages', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="multistage" data-fl-type="select" id="fl-form-shortcode-multistage" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
					<span class="anwp-option-desc"><?php echo esc_html__( 'Applied for multistage main stage competitions only.', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-season_id"><?php echo esc_html__( 'Season ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="season_id" data-fl-type="text" type="text" id="fl-form-shortcode-season_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="season" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-league_id"><?php echo esc_html__( 'League ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="league_id" data-fl-type="text" type="text" id="fl-form-shortcode-league_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="league" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-club_side"><?php echo esc_html__( 'Club Header', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="club_side" data-fl-type="select" id="fl-form-shortcode-club_side" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'None', 'anwp-football-leagues-premium' ); ?></option>
						<option value="home"><?php echo esc_html__( 'Home', 'anwp-football-leagues' ); ?></option>
						<option value="away"><?php echo esc_html__( 'Away', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-legend"><?php echo esc_html__( 'Show Chart Legend', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="legend" data-fl-type="select" id="fl-form-shortcode-legend" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-text_scored">
						<?php echo esc_html__( 'Alternative Text', 'anwp-football-leagues-premium' ); ?>:
						<?php echo esc_html__( 'Scored', 'anwp-football-leagues-premium' ); ?>
					</label>
				</th>
				<td>
					<input name="text_scored" data-fl-type="text" type="text" id="fl-form-shortcode-text_scored" value="" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-text_conceded">
						<?php echo esc_html__( 'Alternative Text', 'anwp-football-leagues-premium' ); ?>:
						<?php echo esc_html__( 'Conceded', 'anwp-football-leagues-premium' ); ?>
					</label>
				</th>
				<td>
					<input name="text_conceded" data-fl-type="text" type="text" id="fl-form-shortcode-text_conceded" value="" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-no_data_text"><?php echo esc_html__( 'No data text', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="no_data_text" data-fl-type="text" type="text" id="fl-form-shortcode-no_data_text" value="" class="fl-shortcode-attr regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-limit"><?php echo esc_html__( 'Matches Limit (0 - for all)', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="limit" data-fl-type="text" type="text" id="fl-form-shortcode-limit" value="0" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-charts">
		<?php
	}

	/**
	 * Load shortcode form with options.
	 * Used in Shortcode Builder and Shortcode TinyMCE tool.
	 *
	 * @since 0.11.10
	 */
	public function load_shortcode_form_charts_team_defaults() {
		?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-type"><?php echo esc_html__( 'Type', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="type" data-fl-type="text" type="text" id="fl-form-shortcode-type" value="team-defaults" readonly class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-stat"><?php echo esc_html__( 'Stat Value', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="stat" data-fl-type="select" id="fl-form-shortcode-stat" class="postform fl-shortcode-attr">
						<option value="fouls" selected><?php echo esc_html__( 'Fouls', 'anwp-football-leagues' ); ?></option>
						<option value="corners"><?php echo esc_html__( 'Corners', 'anwp-football-leagues' ); ?></option>
						<option value="offsides"><?php echo esc_html__( 'Offsides', 'anwp-football-leagues' ); ?></option>
						<option value="possession"><?php echo esc_html__( 'Ball Possession', 'anwp-football-leagues' ); ?></option>
						<option value="shots"><?php echo esc_html__( 'Shots', 'anwp-football-leagues' ); ?></option>
						<option value="shots_on_goal"><?php echo esc_html__( 'Shots on Goal', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-club_id"><?php echo esc_html__( 'Club ID', 'anwp-football-leagues' ); ?>*</label></th>
				<td>
					<input name="club_id" data-fl-type="text" type="text" id="fl-form-shortcode-club_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="club" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-competition_id"><?php echo esc_html__( 'Competition ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="competition_id" data-fl-type="text" type="text" id="fl-form-shortcode-competition_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="competition" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-multistage"><?php echo esc_html__( 'Include matches from secondary stages', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="multistage" data-fl-type="select" id="fl-form-shortcode-multistage" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
					<span class="anwp-option-desc"><?php echo esc_html__( 'Applied for multistage main stage competitions only.', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-season_id"><?php echo esc_html__( 'Season ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="season_id" data-fl-type="text" type="text" id="fl-form-shortcode-season_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="season" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-league_id"><?php echo esc_html__( 'League ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="league_id" data-fl-type="text" type="text" id="fl-form-shortcode-league_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="league" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-club_side"><?php echo esc_html__( 'Club Header', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="club_side" data-fl-type="select" id="fl-form-shortcode-club_side" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'None', 'anwp-football-leagues-premium' ); ?></option>
						<option value="home"><?php echo esc_html__( 'Home', 'anwp-football-leagues' ); ?></option>
						<option value="away"><?php echo esc_html__( 'Away', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-x_axis_label"><?php echo esc_html__( 'Show Opposite Club Names in the Bottom', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="x_axis_label" data-fl-type="select" id="fl-form-shortcode-x_axis_label" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-data_label"><?php echo esc_html__( 'Show Value Label Inside the Bar', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="data_label" data-fl-type="select" id="fl-form-shortcode-data_label" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-title">
						<?php echo esc_html__( 'Chart Title', 'anwp-football-leagues-premium' ); ?>
					</label>
				</th>
				<td>
					<input name="title" data-fl-type="text" type="text" id="fl-form-shortcode-title" value="" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-color">
						<?php echo esc_html__( 'Bar Color', 'anwp-football-leagues-premium' ); ?>
					</label>
				</th>
				<td>
					<input name="color" data-fl-type="text" type="text" id="fl-form-shortcode-color" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'Set bar color in hex format (e.g. #800000) or use "club" value for default team color', 'anwp-football-leagues-premium' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-limit"><?php echo esc_html__( 'Matches Limit (0 - for all)', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="limit" data-fl-type="text" type="text" id="fl-form-shortcode-limit" value="0" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-no_data_text"><?php echo esc_html__( 'No data text', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="no_data_text" data-fl-type="text" type="text" id="fl-form-shortcode-no_data_text" value="" class="fl-shortcode-attr regular-text">
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-charts">
		<?php
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'init', [ $this, 'shortcode_init' ] );

		// Add shortcode option
		add_filter( 'anwpfl/shortcode-pro/get_shortcode_options', [ $this, 'add_shortcode_option' ] );

		// Load shortcode form
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_charts-goals-15', [ $this, 'load_shortcode_form_charts_goals_15' ] );
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_charts-team-defaults', [ $this, 'load_shortcode_form_charts_team_defaults' ] );
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
			'type'           => '', // goals-15, team-defaults
			'no_data_text'   => '', // goals-15, team-defaults
			'club_id'        => '', // goals-15
			'club_side'      => '', // goals-15
			'competition_id' => '', // goals-15
			'multistage'     => '', // goals-15
			'season_id'      => '', // goals-15
			'league_id'      => '', // goals-15, team-defaults
			'text_scored'    => '', // goals-15
			'text_conceded'  => '', // goals-15
			'legend'         => 1,  // goals-15
			'stat'           => '', // team-defaults
			'limit'          => '', // team-defaults, goals-15
			'x_axis_label'   => 1,  // team-defaults
			'data_label'     => 1,  // team-defaults
			'color'          => '', // team-defaults
			'title'          => '', // team-defaults
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		// Sanitize Chart Type
		$atts['type'] = sanitize_text_field( $atts['type'] );

		// Try to load Chart type (as layout)
		$atts['layout'] = $atts['type'];

		return anwp_football_leagues()->template->shortcode_loader( 'premium-charts', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Charts();
