<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Stats Clubs.
 *
 * @since   0.12.4
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Stats Clubs.
 */
class AnWPFL_Premium_Shortcode_Stats_Clubs {

	private $shortcode = 'anwpfl-stats-clubs';

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

		$data['stats-clubs'] = __( 'Stats :: Clubs', 'anwp-football-leagues-premium' );

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_stats-clubs', [ $this, 'load_shortcode_form' ] );
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
				<th scope="row"><label for="fl-form-shortcode-columns"><?php echo esc_html__( 'Columns', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="columns" data-fl-type="select2" id="fl-form-shortcode-columns" class="postform fl-shortcode-attr fl-shortcode-select2" multiple="multiple">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<option value="wins"><?php echo esc_html__( 'Wins', 'anwp-football-leagues' ); ?></option>
						<option value="draws"><?php echo esc_html__( 'Draws', 'anwp-football-leagues-premium' ); ?></option>
						<option value="losses"><?php echo esc_html__( 'Losses', 'anwp-football-leagues-premium' ); ?></option>
						<option value="played"><?php echo esc_html__( 'Played', 'anwp-football-leagues-premium' ); ?></option>
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
						<?php foreach ( anwp_football_leagues_premium()->stats->get_options_match_club_stats_simple() as $stat_id => $stat_title ) : ?>
							<option value="<?php echo esc_attr( $stat_id ); ?>"><?php echo esc_html( $stat_title ); ?></option>
						<?php endforeach; ?>
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
				<th scope="row"><label for="fl-form-shortcode-side"><?php echo esc_html__( 'Side', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="side" data-fl-type="select" id="fl-form-shortcode-side" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'All', 'anwp-football-leagues' ); ?></option>
						<option value="home"><?php echo esc_html__( 'Home', 'anwp-football-leagues' ); ?></option>
						<option value="away"><?php echo esc_html__( 'Away', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-sort_column"><?php echo esc_html__( 'Sorting By Column', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="sort_column" data-fl-type="select" id="fl-form-shortcode-sort_column" class="postform fl-shortcode-attr">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<option value="wins"><?php echo esc_html__( 'Wins', 'anwp-football-leagues' ); ?></option>
						<option value="draws"><?php echo esc_html__( 'Draws', 'anwp-football-leagues-premium' ); ?></option>
						<option value="losses"><?php echo esc_html__( 'Losses', 'anwp-football-leagues-premium' ); ?></option>
						<option value="played"><?php echo esc_html__( 'Played', 'anwp-football-leagues-premium' ); ?></option>
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
						<?php foreach ( anwp_football_leagues_premium()->stats->get_options_match_club_stats_simple() as $stat_id => $stat_title ) : ?>
							<option value="<?php echo esc_attr( $stat_id ); ?>"><?php echo esc_html( $stat_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-sort_order"><?php echo esc_html__( 'Sort Order', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="sort_order" data-fl-type="select" id="fl-form-shortcode-sort_order" class="postform fl-shortcode-attr">
						<option value="desc" selected><?php echo esc_html__( 'Descending', 'anwp-football-leagues' ); ?></option>
						<option value="asc"><?php echo esc_html__( 'Ascending', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-club_column"><?php echo esc_html__( 'Club Column', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="club_column" data-fl-type="select" id="fl-form-shortcode-club_column" class="postform fl-shortcode-attr">
						<option value="title" selected><?php echo esc_html__( 'Club Title', 'anwp-football-leagues' ); ?></option>
						<option value="logo" ><?php echo esc_html__( 'Logo', 'anwp-football-leagues' ); ?></option>
						<option value="abbr"><?php echo esc_html__( 'Abbreviation', 'anwp-football-leagues' ); ?></option>
						<option value="logo_title"><?php echo esc_html__( 'Logo', 'anwp-football-leagues' ); ?>+<?php echo esc_html__( 'Club Title', 'anwp-football-leagues' ); ?></option>
						<option value="logo_abbr"><?php echo esc_html__( 'Logo', 'anwp-football-leagues' ); ?>+<?php echo esc_html__( 'Abbreviation', 'anwp-football-leagues' ); ?></option>
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
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-stats-clubs">
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
			'columns'        => '',
			'season_id'      => '',
			'competition_id' => '',
			'multistage'     => 0,
			'league_id'      => '',
			'date_before'    => '',
			'date_after'     => '',
			'side'           => '',
			'class'          => '',
			'club_column'    => 'logo_abbr',
			'sort_column'    => '',
			'header'         => '',
			'sort_order'     => 'DESC',
			'layout_mod'     => 'even',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( '%competition_id%' === $atts['competition_id'] && ! empty( $_GET['competition_id'] ) && absint( $_GET['competition_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$atts['competition_id'] = absint( $_GET['competition_id'] );
		}

		return anwp_football_leagues()->template->shortcode_loader( 'premium-stats-clubs', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Stats_Clubs();
