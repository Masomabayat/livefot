<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Player Stat Panel
 *
 * @since   0.14.7
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Player Stat.
 */
class AnWPFL_Premium_Shortcode_Player_Stats_Panel {

	private $shortcode = 'anwpfl-player-stats-panel';

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

		$data['player-stats-panel'] = __( 'Player Stats Panel', 'anwp-football-leagues-premium' );

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_player-stats-panel', [ $this, 'load_shortcode_form' ] );
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
					<label for="fl-form-shortcode-player_id"><?php echo esc_html__( 'Player', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="player_id" data-fl-type="text" type="text" id="fl-form-shortcode-player_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="player" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-stats"><?php echo esc_html__( 'Stat Value', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="stats" data-fl-type="select2" id="fl-form-shortcode-stats" class="postform fl-shortcode-attr fl-shortcode-select2" multiple>
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<optgroup label="<?php echo esc_html__( 'Default Stat', 'anwp-football-leagues-premium' ); ?>">
							<option value="played"><?php echo esc_html__( 'Played', 'anwp-football-leagues-premium' ); ?></option>
							<option value="started"><?php echo esc_html__( 'Started', 'anwp-football-leagues-premium' ); ?></option>
							<option value="sub_in"><?php echo esc_html__( 'Substitute In', 'anwp-football-leagues-premium' ); ?></option>
							<option value="minutes"><?php echo esc_html__( 'Minutes', 'anwp-football-leagues' ); ?></option>
							<option value="cards_all"><?php echo esc_html__( 'Cards (All)', 'anwp-football-leagues-premium' ); ?></option>
							<option value="card_y"><?php echo esc_html__( 'Cards - Yellow', 'anwp-football-leagues-premium' ); ?></option>
							<option value="card_yr"><?php echo esc_html__( 'Cards - 2nd Yellow/Red', 'anwp-football-leagues-premium' ); ?></option>
							<option value="card_r"><?php echo esc_html__( 'Cards - Red', 'anwp-football-leagues-premium' ); ?></option>
							<option value="goals"><?php echo esc_html__( 'Goals', 'anwp-football-leagues' ); ?></option>
							<option value="goals_penalty"><?php echo esc_html__( 'Goal From Penalty', 'anwp-football-leagues' ); ?></option>
							<option value="own_goals"><?php echo esc_html__( 'Own Goals', 'anwp-football-leagues' ); ?></option>
							<option value="assists"><?php echo esc_html__( 'Assists', 'anwp-football-leagues' ); ?></option>
							<option value="goals_conceded"><?php echo esc_html__( 'Goals Conceded', 'anwp-football-leagues' ); ?></option>
							<option value="clean_sheets"><?php echo esc_html__( 'Clean Sheets', 'anwp-football-leagues' ); ?></option>
						</optgroup>
						<optgroup label="<?php echo esc_html__( 'Custom Simple Stat', 'anwp-football-leagues-premium' ); ?>">
							<?php foreach ( anwp_football_leagues_premium()->stats->get_options_match_player_stats_simple() as $stat_id => $stat_title ) : ?>
								<option value="<?php echo esc_attr( $stat_id ); ?>"><?php echo esc_html( $stat_title ); ?></option>
							<?php endforeach; ?>
						</optgroup>
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
					<select name="show_secondary" data-fl-type="select" id="fl-form-shortcode-show_secondary" class="postform fl-shortcode-attr">
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-club_id"><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="club_id" data-fl-type="select2" id="fl-form-shortcode-club_id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->club->get_clubs_options() as $club_id => $club_title ) : ?>
							<option value="<?php echo esc_attr( $club_id ); ?>"><?php echo esc_html( $club_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-block_width"><?php echo esc_html__( 'Block Width (px)', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<input name="block_width" data-fl-type="text" type="text" id="fl-form-shortcode-block_width" value="" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-per_game"><?php echo esc_html__( 'Show Per Game value', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="per_game" data-fl-type="select" id="fl-form-shortcode-per_game" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
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
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-player-stats-panel">
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
			'player_id'      => '',
			'competition_id' => '',
			'show_secondary' => 1,
			'season_id'      => '',
			'league_id'      => '',
			'club_id'        => '',
			'per_game'       => 1,
			'block_width'    => '',
			'stats'          => '',
			'class'          => '',
			'header'         => '',
			'layout'         => '',
			'date_from'      => '',
			'date_to'        => '',
			'season_text'    => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-player-stats-panel', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Player_Stats_Panel();
