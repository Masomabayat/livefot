<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Stats H2H
 *
 * @since   0.12.2
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Stats H2H.
 */
class AnWPFL_Premium_Shortcode_Stats_H2H {

	private $shortcode = 'anwpfl-stats-h2h';

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

		// Initialize shortcode
		add_action( 'init', [ $this, 'shortcode_init' ] );

		// Add shortcode option
		add_filter( 'anwpfl/shortcode-pro/get_shortcode_options', [ $this, 'add_shortcode_option' ] );

		// Load shortcode form
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_stats-h2h', [ $this, 'load_shortcode_form' ] );
	}

	/**
	 * Load shortcode form with options.
	 * Used in Shortcode Builder and Shortcode TinyMCE tool.
	 */
	public function load_shortcode_form() {
		?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-club_a"><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="club_a" data-fl-type="select2" id="fl-form-shortcode-club_a" class="postform fl-shortcode-attr fl-shortcode-select2">
						<?php foreach ( anwp_football_leagues()->club->get_clubs_options() as $club_id => $club_title ) : ?>
							<option value="<?php echo esc_attr( $club_id ); ?>"><?php echo esc_html( $club_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-club_b"><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="club_b" data-fl-type="select2" id="fl-form-shortcode-club_b" class="postform fl-shortcode-attr fl-shortcode-select2">
						<?php foreach ( anwp_football_leagues()->club->get_clubs_options() as $club_id => $club_title ) : ?>
							<option value="<?php echo esc_attr( $club_id ); ?>"><?php echo esc_html( $club_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-id"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="competition_id" data-fl-type="select2" id="fl-form-shortcode-id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->competition->get_competition_options() as $competition_id => $competition_title ) : ?>
							<option value="<?php echo esc_attr( $competition_id ); ?>"><?php echo esc_html( $competition_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-multistage"><?php echo esc_html__( 'Include All Stages (in Multistage competition)', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="multistage" data-fl-type="select" id="fl-form-shortcode-multistage" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
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
				<th scope="row"><label for="fl-form-shortcode-league_id"><?php echo esc_html__( 'League', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="league_id" data-fl-type="select2" id="fl-form-shortcode-league_id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->league->get_league_options() as $league_id => $league_title ) : ?>
							<option value="<?php echo esc_attr( $league_id ); ?>"><?php echo esc_html( $league_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-date_before"><?php echo esc_html__( 'Date To', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="date_before" data-fl-type="text" type="text" id="fl-form-shortcode-date_before" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'Format: YYYY-MM-DD. E.g.: 2019-04-21', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-date_after"><?php echo esc_html__( 'Date From', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="date_after" data-fl-type="text" type="text" id="fl-form-shortcode-date_after" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'Format: YYYY-MM-DD. E.g.: 2019-04-21', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-h2h_only"><?php echo esc_html__( 'Head-to-Head matches stats only', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="h2h_only" data-fl-type="select" id="fl-form-shortcode-h2h_only" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-highlight_best"><?php echo esc_html__( 'Highlight Best Result', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="highlight_best" data-fl-type="select" id="fl-form-shortcode-highlight_best" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-per_game"><?php echo esc_html__( 'Show Average per match value ', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="per_game" data-fl-type="select" id="fl-form-shortcode-per_game" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-stats"><?php echo esc_html__( 'Stats', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="stats" data-fl-type="select2" id="fl-form-shortcode-stats" class="postform fl-shortcode-attr fl-shortcode-select2" multiple>
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<option value="goals"><?php echo esc_html__( 'Goals', 'anwp-football-leagues' ); ?></option>
						<option value="goals_conceded"><?php echo esc_html__( 'Goals Conceded', 'anwp-football-leagues' ); ?></option>
						<option value="cards_y"><?php echo esc_html__( 'Cards - Yellow', 'anwp-football-leagues-premium' ); ?></option>
						<option value="cards_r"><?php echo esc_html__( 'Cards - Red', 'anwp-football-leagues-premium' ); ?></option>
						<option value="clean_sheets"><?php echo esc_html__( 'Clean Sheets', 'anwp-football-leagues' ); ?></option>
						<option value="corners"><?php echo esc_html__( 'Corners', 'anwp-football-leagues' ); ?></option>
						<option value="fouls"><?php echo esc_html__( 'Fouls', 'anwp-football-leagues' ); ?></option>
						<option value="offsides"><?php echo esc_html__( 'Offsides', 'anwp-football-leagues' ); ?></option>
						<option value="shots"><?php echo esc_html__( 'Shots', 'anwp-football-leagues' ); ?></option>
						<option value="shots_on_goal"><?php echo esc_html__( 'Shots on Goal', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-header"><?php echo esc_html__( 'Header', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="header" data-fl-type="text" type="text" id="fl-form-shortcode-header" value="" class="fl-shortcode-attr regular-text">
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-stats-h2h">
		<?php
	}

	/**
	 * Add shortcode options.
	 * Used in Shortcode Builder and Shortcode TinyMCE tool.
	 *
	 * @param array $data Shortcode options.
	 *
	 * @return array
	 */
	public function add_shortcode_option( $data ) {

		$data['stats-h2h'] = __( 'Head to Head Team Stats', 'anwp-football-leagues-premium' );

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
			'competition_id' => '',
			'multistage'     => 0,
			'season_id'      => '',
			'league_id'      => '',
			'club_a'         => '',
			'club_b'         => '',
			'date_before'    => '',
			'date_after'     => '',
			'h2h_only'       => 1,
			'highlight_best' => 1,
			'per_game'       => 1,
			'stats'          => '',
			'class'          => '',
			'header'         => '',
			'caching_time'   => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-stats-h2h', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Stats_H2H();
