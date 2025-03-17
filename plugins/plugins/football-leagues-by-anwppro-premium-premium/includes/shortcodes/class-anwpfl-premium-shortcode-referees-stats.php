<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Referees Stat
 *
 * @since   0.14.7
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Referees Stat.
 */
class AnWPFL_Premium_Shortcode_Referees_Stats {

	private $shortcode = 'anwpfl-referees-stats';

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
	 */
	public function add_shortcode_option( $data ) {

		$data['referees-stats'] = __( 'Referees Statistics', 'anwp-football-leagues-premium' );

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_referees-stats', [ $this, 'load_shortcode_form' ] );
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
				<th scope="row"><label for="fl-form-shortcode-competition_id"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="competition_id" data-fl-type="select2" id="fl-form-shortcode-competition_id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->competition->get_competition_options() as $competition_id => $competition_title ) : ?>
							<option value="<?php echo esc_attr( $competition_id ); ?>"><?php echo esc_html( $competition_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_secondary"><?php echo esc_html__( 'Include All Stages (in Multistage competition)', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_secondary" data-fl-type="select" id="fl-form-shortcode-multistage" class="postform fl-shortcode-attr">
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-filter_by_clubs"><?php echo esc_html__( 'Filter by Clubs', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<input name="filter_by_clubs" data-fl-type="text" type="text" id="fl-form-shortcode-filter_by_clubs" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="club" data-single="no">
						<span class="dashicons dashicons-search"></span>
					</button>
					<span class="anwp-option-desc"><?php echo esc_html__( 'comma-separated list of IDs', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-home_club"><?php echo esc_html__( 'Filter by Home Club', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<input name="home_club" data-fl-type="text" type="text" id="fl-form-shortcode-home_club" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="club" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-away_club"><?php echo esc_html__( 'Filter by Away Club', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<input name="away_club" data-fl-type="text" type="text" id="fl-form-shortcode-away_club" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="club" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
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
				<th scope="row"><label for="fl-form-shortcode-limit"><?php echo esc_html__( 'Matches Limit (0 - for all)', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="limit" data-fl-type="text" type="text" id="fl-form-shortcode-limit" value="0" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-date_from"><?php echo esc_html__( 'Date From', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="date_from" data-fl-type="text" type="text" id="fl-form-shortcode-date_from" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'Format: YYYY-MM-DD. E.g.: 2019-04-21', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-date_to"><?php echo esc_html__( 'Date To', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="date_to" data-fl-type="text" type="text" id="fl-form-shortcode-date_to" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'Format: YYYY-MM-DD. E.g.: 2019-04-21', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-per_game"><?php echo esc_html__( 'Show Per Game Columns', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="per_game" data-fl-type="select" id="fl-form-shortcode-per_game" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_games"><?php echo esc_html__( 'Show Number of Games', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_games" data-fl-type="select" id="fl-form-shortcode-show_games" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_rc"><?php echo esc_html__( 'Show Red Cards', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_rc" data-fl-type="select" id="fl-form-shortcode-show_rc" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_fouls"><?php echo esc_html__( 'Show Fouls', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_fouls" data-fl-type="select" id="fl-form-shortcode-show_fouls" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-home_away"><?php echo esc_html__( 'Show Home/Away statistics', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="home_away" data-fl-type="select" id="fl-form-shortcode-home_away" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-profile_link"><?php echo esc_html__( 'Link to Referee profile', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="profile_link" data-fl-type="select" id="fl-form-shortcode-profile_link" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-order"><?php echo esc_html__( 'Sorting Order', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="order" data-fl-type="text" type="text" id="fl-form-shortcode-order" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc">"2,desc;1,asc" or "1,asc"</span>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-referees-stats">
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
			'league_id'            => '',
			'show_secondary'       => 1,
			'filter_by_clubs'      => '',
			'filter_by_matchweeks' => '',
			'limit'                => '',
			'date_from'            => '',
			'date_to'              => '',
			'exclude_ids'          => '',
			'include_ids'          => '',
			'home_club'            => '',
			'away_club'            => '',
			'class'                => '',
			'header'               => '',
			'layout'               => '',
			'show_games'           => 1,
			'show_rc'              => 1,
			'show_fouls'           => 1,
			'home_away'            => 1,
			'per_game'             => 1,
			'profile_link'         => 1,
			'order'                => '',
			'layout_mod'           => 'even',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-referees-stats', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Referees_Stats();
