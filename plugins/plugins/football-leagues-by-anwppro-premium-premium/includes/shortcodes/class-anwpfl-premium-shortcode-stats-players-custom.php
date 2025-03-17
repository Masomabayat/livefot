<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Stats Players - Custom.
 *
 * @since   0.12.2
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Stats Players - Custom.
 */
class AnWPFL_Premium_Shortcode_Stats_Players_Custom {

	private $shortcode = 'anwpfl-stats-players-custom';

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

		$data['stats-players-custom'] = __( 'Stats :: Players (Custom)', 'anwp-football-leagues-premium' );

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_stats-players-custom', [ $this, 'load_shortcode_form' ] );
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
						<option value="ranking"><?php echo esc_html__( 'Ranking Place', 'anwp-football-leagues' ); ?></option>
						<option value="player_name"><?php echo esc_html__( 'Player Name', 'anwp-football-leagues' ); ?></option>
						<option value="club"><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></option>
						<option value="position"><?php echo esc_html__( 'Position', 'anwp-football-leagues' ); ?></option>
						<option value="appearance"><?php echo esc_html__( 'Appearance (Games Played)', 'anwp-football-leagues-premium' ); ?></option>
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
						<?php foreach ( anwp_football_leagues_premium()->stats->get_options_match_player_stats_simple() as $stat_id => $stat_title ) : ?>
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
				<th scope="row"><label for="fl-form-shortcode-sort_column"><?php echo esc_html__( 'Sorting By Column', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="sort_column" data-fl-type="select" id="fl-form-shortcode-sort_column" class="postform fl-shortcode-attr">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<option value="player_name"><?php echo esc_html__( 'Player Name', 'anwp-football-leagues' ); ?></option>
						<option value="club"><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></option>
						<option value="position"><?php echo esc_html__( 'Position', 'anwp-football-leagues' ); ?></option>
						<option value="appearance"><?php echo esc_html__( 'Appearance (Games Played)', 'anwp-football-leagues-premium' ); ?></option>
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
						<?php foreach ( anwp_football_leagues_premium()->stats->get_options_match_player_stats_simple() as $stat_id => $stat_title ) : ?>
							<option value="<?php echo esc_attr( $stat_id ); ?>"><?php echo esc_html( $stat_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-sort_order"><?php echo esc_html__( 'Sorting Order', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="sort_order" data-fl-type="select" id="fl-form-shortcode-sort_order" class="postform fl-shortcode-attr">
						<option value="asc"><?php echo esc_html__( 'Ascending', 'anwp-football-leagues' ); ?></option>
						<option value="desc" selected><?php echo esc_html__( 'Descending', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-sort_column_2"><?php echo esc_html__( 'Sorting By Column', 'anwp-football-leagues-premium' ); ?> 2</label></th>
				<td>
					<select name="sort_column_2" data-fl-type="select" id="fl-form-shortcode-sort_column_2" class="postform fl-shortcode-attr">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<option value="player_name"><?php echo esc_html__( 'Player Name', 'anwp-football-leagues' ); ?></option>
						<option value="club"><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></option>
						<option value="position"><?php echo esc_html__( 'Position', 'anwp-football-leagues' ); ?></option>
						<option value="appearance"><?php echo esc_html__( 'Appearance (Games Played)', 'anwp-football-leagues-premium' ); ?></option>
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
						<?php foreach ( anwp_football_leagues_premium()->stats->get_options_match_player_stats_simple() as $stat_id => $stat_title ) : ?>
							<option value="<?php echo esc_attr( $stat_id ); ?>"><?php echo esc_html( $stat_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-sort_order_2"><?php echo esc_html__( 'Sorting Order', 'anwp-football-leagues-premium' ); ?> 2</label>
				</th>
				<td>
					<select name="sort_order_2" data-fl-type="select" id="fl-form-shortcode-sort_order_2" class="postform fl-shortcode-attr">
						<option value="asc"><?php echo esc_html__( 'Ascending', 'anwp-football-leagues' ); ?></option>
						<option value="desc" selected><?php echo esc_html__( 'Descending', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-filter_column_1"><?php echo esc_html__( 'Filter By Column', 'anwp-football-leagues-premium' ); ?> 1</label></th>
				<td>
					<select name="filter_column_1" data-fl-type="select" id="fl-form-shortcode-filter_column_1" class="postform fl-shortcode-attr">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<option value="player_name"><?php echo esc_html__( 'Player Name', 'anwp-football-leagues' ); ?></option>
						<option value="club"><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></option>
						<option value="position"><?php echo esc_html__( 'Position', 'anwp-football-leagues' ); ?></option>
						<option value="appearance"><?php echo esc_html__( 'Appearance (Games Played)', 'anwp-football-leagues-premium' ); ?></option>
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
						<?php foreach ( anwp_football_leagues_premium()->stats->get_options_match_player_stats_simple() as $stat_id => $stat_title ) : ?>
							<option value="<?php echo esc_attr( $stat_id ); ?>"><?php echo esc_html( $stat_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-filter_column_1_value"><?php echo esc_html__( 'Filter Column 1 value (min)', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<input name="filter_column_1_value" data-fl-type="text" type="text" id="fl-form-shortcode-filter_column_1_value" value="" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-filter_column_2"><?php echo esc_html__( 'Filter By Column', 'anwp-football-leagues-premium' ); ?> 2</label></th>
				<td>
					<select name="filter_column_2" data-fl-type="select" id="fl-form-shortcode-filter_column_2" class="postform fl-shortcode-attr">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<option value="player_name"><?php echo esc_html__( 'Player Name', 'anwp-football-leagues' ); ?></option>
						<option value="club"><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></option>
						<option value="position"><?php echo esc_html__( 'Position', 'anwp-football-leagues' ); ?></option>
						<option value="appearance"><?php echo esc_html__( 'Appearance (Games Played)', 'anwp-football-leagues-premium' ); ?></option>
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
						<?php foreach ( anwp_football_leagues_premium()->stats->get_options_match_player_stats_simple() as $stat_id => $stat_title ) : ?>
							<option value="<?php echo esc_attr( $stat_id ); ?>"><?php echo esc_html( $stat_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-filter_column_2_value"><?php echo esc_html__( 'Filter Column 2 value (min)', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<input name="filter_column_2_value" data-fl-type="text" type="text" id="fl-form-shortcode-filter_column_2_value" value="" class="fl-shortcode-attr regular-text code">
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
				<th scope="row"><label for="fl-form-shortcode-layout"><?php echo esc_html__( 'Layout', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="layout" data-fl-type="select" id="fl-form-shortcode-layout" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'Regular Table', 'anwp-football-leagues-premium' ); ?></option>
						<option value="tabulator" ><?php echo esc_html__( 'Tabulator (with sorting and filtering)', 'anwp-football-leagues-premium' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-stats-players-custom">
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
			'columns'               => '',
			'season_id'             => '',
			'league_id'             => '',
			'club_id'               => '',
			'competition_id'        => '',
			'multistage'            => 0,
			'type'                  => '',
			'links'                 => 0,
			'photos'                => 0,
			'limit'                 => 10,
			'class'                 => '',
			'club_column'           => 'logo_abbr',
			'sort_column'           => '',
			'header'                => '',
			'sort_order'            => 'DESC',
			'sort_column_2'         => '',
			'sort_order_2'          => 'DESC',
			'date_from'             => '',
			'date_to'               => '',
			'filter_column_1'       => '',
			'filter_column_1_value' => '',
			'filter_column_2'       => '',
			'filter_column_2_value' => '',
			'layout'                => '',
			'layout_mod'            => 'even',
			'rows'                  => 10,
			'paging'                => 1,
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( '%competition_id%' === $atts['competition_id'] && ! empty( $_GET['competition_id'] ) && absint( $_GET['competition_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$atts['competition_id'] = absint( $_GET['competition_id'] );
		}

		return anwp_football_leagues()->template->shortcode_loader( 'premium-stats-players-custom', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Stats_Players_Custom();
