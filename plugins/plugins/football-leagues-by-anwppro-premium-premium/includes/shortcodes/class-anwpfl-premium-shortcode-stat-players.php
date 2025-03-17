<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Stat Players.
 *
 * @since   0.12.0
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Stats Players.
 */
class AnWPFL_Premium_Shortcode_Stat_Players {

	private $shortcode = 'anwpfl-stat-players';

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

		$data['stat-players'] = __( 'Stat :: Players (Single Stat Value)', 'anwp-football-leagues-premium' );

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_stat-players', [ $this, 'load_shortcode_form' ] );
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
				<th scope="row"><label for="fl-form-shortcode-stat"><?php echo esc_html__( 'Stat Value', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="stat" data-fl-type="select" id="fl-form-shortcode-stat" class="postform fl-shortcode-attr">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<optgroup label="<?php echo esc_html__( 'Default Stat', 'anwp-football-leagues-premium' ); ?>">
							<option value="appearance"><?php echo esc_html__( 'Appearance', 'anwp-football-leagues-premium' ); ?></option>
							<option value="started"><?php echo esc_html__( 'Started', 'anwp-football-leagues-premium' ); ?></option>
							<option value="minutes"><?php echo esc_html__( 'Minutes', 'anwp-football-leagues' ); ?></option>
							<option value="cards_all"><?php echo esc_html__( 'Cards (All)', 'anwp-football-leagues-premium' ); ?></option>
							<option value="cards_y"><?php echo esc_html__( 'Cards - Yellow', 'anwp-football-leagues-premium' ); ?></option>
							<option value="cards_r"><?php echo esc_html__( 'Cards - Red', 'anwp-football-leagues-premium' ); ?></option>
							<option value="goals"><?php echo esc_html__( 'Goals', 'anwp-football-leagues' ); ?></option>
							<option value="goals_penalty"><?php echo esc_html__( 'Goal From Penalty', 'anwp-football-leagues' ); ?></option>
							<option value="goals_own"><?php echo esc_html__( 'Own Goals', 'anwp-football-leagues' ); ?></option>
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
				<th scope="row"><label for="fl-form-shortcode-type"><?php echo esc_html__( 'Type', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="type" data-fl-type="select" id="fl-form-shortcode-type" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'All', 'anwp-football-leagues' ); ?></option>
						<option value="p"><?php echo esc_html__( 'Players', 'anwp-football-leagues' ); ?></option>
						<option value="g"><?php echo esc_html__( 'Goalkeepers', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-limit"><?php echo esc_html__( 'Players Limit (0 - for all)', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<input name="limit" data-fl-type="text" type="text" id="fl-form-shortcode-limit" value="10" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-soft_limit"><?php echo esc_html__( 'Soft Limit', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="soft_limit" data-fl-type="select" id="fl-form-shortcode-soft_limit" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-first_em"><?php echo esc_html__( 'Emphasize the first player', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="first_em" data-fl-type="select" id="fl-form-shortcode-first_em" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-links"><?php echo esc_html__( 'Show link to Player profile', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="links" data-fl-type="select" id="fl-form-shortcode-links" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-photos"><?php echo esc_html__( 'Show Photo', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="photos" data-fl-type="select" id="fl-form-shortcode-photos" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-games_played"><?php echo esc_html__( 'Show number of games played', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="games_played" data-fl-type="select" id="fl-form-shortcode-games_played" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_full"><?php echo esc_html__( 'Show full list', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_full" data-fl-type="select" id="fl-form-shortcode-show_full" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-players-hide_zero"><?php echo esc_html__( 'Hide Zeros', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="hide_zero" data-fl-type="select" id="fl-form-shortcode-players-hide_zero" class="postform fl-shortcode-attr">
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
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-stat-players">
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
			'competition_id' => '',
			'multistage'     => 0,
			'season_id'      => '',
			'league_id'      => '',
			'club_id'        => '',
			'type'           => '',
			'links'          => 0,
			'first_em'       => 1,
			'stat'           => '',
			'limit'          => 10,
			'soft_limit'     => 0,
			'photos'         => 0,
			'games_played'   => 0,
			'show_full'      => 0,
			'hide_zero'      => 1,
			'date_from'      => '',
			'date_to'        => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-stat-players', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Stat_Players();
