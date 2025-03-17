<?php
/**
 * AnWP Football Leagues Premium :: Text
 *
 * @since 0.10.7
 */
class AnWPFL_Premium_Text {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 * @since  0.10.7
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param  AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// Save core plugin to var
		$this->plugin = $plugin;

		// Run hooks
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.10.7
	 */
	public function hooks() {

		add_filter( 'anwpfl/text/text_extra_options', [ $this, 'add_text_premium_options' ] );
	}

	/**
	 * Add premium text options.
	 *
	 * @param $options
	 *
	 * @return array
	 * @since 0.10.7
	 */
	public function add_text_premium_options( $options ) {

		$options_new = [
			[
				'name' => esc_html__( 'Player Stats', 'anwp-football-leagues-premium' ),
				'desc' => 'club :: stats',
				'id'   => 'club__stats__player_stats',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Transfers', 'anwp-football-leagues-premium' ),
				'desc' => 'club :: transfers',
				'id'   => 'club__transfers__transfers',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Mid-season window', 'anwp-football-leagues-premium' ),
				'desc' => 'club :: transfers',
				'id'   => 'club__transfers__mid_season_window',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Pre-season window', 'anwp-football-leagues-premium' ),
				'desc' => 'club :: transfers',
				'id'   => 'club__transfers__pre_season_window',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'No data', 'anwp-football-leagues-premium' ),
				'desc' => 'club :: transfers',
				'id'   => 'club__transfers__no_data',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Transfers', 'anwp-football-leagues-premium' ),
				'desc' => 'competition :: transfers',
				'id'   => 'competition__transfers__transfers',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'No data', 'anwp-football-leagues-premium' ),
				'desc' => 'competition :: transfers',
				'id'   => 'competition__transfers__no_data',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Prediction', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: header',
				'id'   => 'match__match__prediction',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Match Commentary', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: commentary',
				'id'   => 'match__commentary__match_commentary',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Head to Head Matches', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: h2h',
				'id'   => 'match__h2h__head_to_head_matches',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Players Statistics', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: player stats',
				'id'   => 'match__player_stats__players_statistics',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goalkeepers', 'anwp-football-leagues' ),
				'desc' => 'match :: player stats',
				'id'   => 'match__player_stats__goalkeepers',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Player', 'anwp-football-leagues' ),
				'desc' => 'match :: player stats',
				'id'   => 'match__player_stats__player',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Field Players', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: player stats',
				'id'   => 'match__player_stats__field_players',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Show Score', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: scoreboard',
				'id'   => 'match__scoreboard__show_score',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Season', 'anwp-football-leagues' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__season',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'All', 'anwp-football-leagues' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__all',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Games Played', 'anwp-football-leagues-premium' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__games_played',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Games Started', 'anwp-football-leagues-premium' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__games_started',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals Conceded', 'anwp-football-leagues' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__goals_conceded',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Clean Sheets', 'anwp-football-leagues' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__clean_sheets',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals', 'anwp-football-leagues' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__goals',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Assists', 'anwp-football-leagues' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__assists',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Player', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__player',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Club', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__club',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Position', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__position',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Played Matches', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__played_matches',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Started', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__started',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Started', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__started',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Minutes', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__minutes',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Yellow Cards', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__yellow_cards',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( '2d Yellow > Red Cards', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__2_d_yellow_red_cards',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Red Cards', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__red_cards',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__goals',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals (from penalty)', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__goals_from_penalty',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Own Goals', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__own_goals',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Assists', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__assists',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals Conceded', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__goals_conceded',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Clean Sheets', 'anwp-football-leagues' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__clean_sheets',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'min', 'anwp-football-leagues-premium' ),
				'desc' => 'stats players :: shortcode',
				'id'   => 'stats_players__shortcode__min',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'No Posts Found', 'anwp-football-leagues-premium' ),
				'desc' => 'tag posts :: shortcode',
				'id'   => 'tag_posts__shortcode__no_posts_found',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'No data', 'anwp-football-leagues-premium' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__no_data',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Arrivals', 'anwp-football-leagues-premium' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__arrivals',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Player', 'anwp-football-leagues' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__player',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Club Out', 'anwp-football-leagues-premium' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__club_out',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Date Of Birth', 'anwp-football-leagues' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__date_of_birth',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Transfer Date', 'anwp-football-leagues-premium' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__transfer_date',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Fee', 'anwp-football-leagues-premium' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__fee',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Rumour', 'anwp-football-leagues-premium' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__rumour',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'unknown club', 'anwp-football-leagues-premium' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__unknown_club',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'without club', 'anwp-football-leagues-premium' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__without_club',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Departures', 'anwp-football-leagues-premium' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__departures',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Club In', 'anwp-football-leagues-premium' ),
				'desc' => 'transfers :: shortcode',
				'id'   => 'transfers__shortcode__club_in',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'assistant', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: timeline',
				'id'   => 'match__timeline__assistant',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Assistant', 'anwp-football-leagues' ),
				'desc' => 'match :: commentary',
				'id'   => 'match__commentary__assistant',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'In', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: commentary',
				'id'   => 'match__commentary__in',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Out', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: commentary',
				'id'   => 'match__commentary__out',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Cancelled goal', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: event',
				'id'   => 'match__event__cancelled_goal',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'No matches for this date.', 'anwp-football-leagues-premium' ),
				'desc' => 'data :: matches',
				'id'   => 'data__matches__no_matches_for_this_date',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'No matches found.', 'anwp-football-leagues-premium' ),
				'desc' => 'data :: matches',
				'id'   => 'data__matches__no_matches_found',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( '1st Half', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: timeline',
				'id'   => 'match__timeline__1_st_half',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( '2nd Half', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: timeline',
				'id'   => 'match__timeline__2_nd_half',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Extra Time', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: timeline',
				'id'   => 'match__timeline__extra_time',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( '1st Half', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: live',
				'id'   => 'match__live__1_st_half',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( '2nd Half', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: live',
				'id'   => 'match__live__2_nd_half',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Extra Time', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: live',
				'id'   => 'match__live__extra_time',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Penalty', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: live',
				'id'   => 'match__live__penalty',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Half Time', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: live',
				'id'   => 'match__live__half_time',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Full Time', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: live',
				'id'   => 'match__live__full_time',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Off Target', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: stats',
				'id'   => 'match__stats__off_target',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'On Target', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: stats',
				'id'   => 'match__stats__on_target',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'load more', 'anwp-football-leagues-premium' ),
				'desc' => 'general',
				'id'   => 'general__load_more',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Show full list', 'anwp-football-leagues-premium' ),
				'desc' => 'players :: stat',
				'id'   => 'players__stat__show_full_list',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Wins', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__wins',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Draws', 'anwp-football-leagues-premium' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__draws',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Losses', 'anwp-football-leagues-premium' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__losses',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Played', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__played',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Corners', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__corners',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Fouls', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__fouls',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Offsides', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__offsides',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Shots', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__shots',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Shots on Goal', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__shots_on_goal',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Yellow Cards', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__cards_y',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Red Cards', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__cards_r',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__goals',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals Conceded', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__goals_conceded',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Clean Sheets', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__clean_sheets',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Total', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__total',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Per Match', 'anwp-football-leagues' ),
				'desc' => 'stats :: h2h',
				'id'   => 'stats__h2h__per_match',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Club Stats', 'anwp-football-leagues-premium' ),
				'desc' => 'club :: stats',
				'id'   => 'club__stats__club_stats',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Home', 'anwp-football-leagues' ),
				'desc' => 'club :: stats',
				'id'   => 'stats__club__home',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Away', 'anwp-football-leagues' ),
				'desc' => 'club :: stats',
				'id'   => 'stats__club__away',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'All', 'anwp-football-leagues' ),
				'desc' => 'club :: stats',
				'id'   => 'stats__club__all',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Club', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__club',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Club', 'anwp-football-leagues' ) . ' - ' . esc_html__( 'Abbreviation', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__club__abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Wins', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__wins',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Wins', 'anwp-football-leagues' ) . ' - ' . esc_html__( 'Abbreviation', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__wins__abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Draws', 'anwp-football-leagues-premium' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__draws',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Draws', 'anwp-football-leagues-premium' ) . ' - ' . esc_html__( 'Abbreviation', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__draws__abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Losses', 'anwp-football-leagues-premium' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__losses',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Losses', 'anwp-football-leagues-premium' ) . ' - ' . esc_html__( 'Abbreviation', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__losses__abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Played', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__played',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Played', 'anwp-football-leagues' ) . ' - ' . esc_html__( 'Abbreviation', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__played__abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals Conceded', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__goals_conceded',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals Conceded', 'anwp-football-leagues' ) . ' - ' . esc_html__( 'Abbreviation', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__goals_conceded__abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__goals',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Clean Sheets', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__clean_sheets',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Clean Sheets', 'anwp-football-leagues' ) . ' - ' . esc_html__( 'Abbreviation', 'anwp-football-leagues' ),
				'desc' => 'stats :: clubs',
				'id'   => 'stats_clubs__shortcode__clean_sheets__abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'API Import - Transfers', 'anwp-football-leagues-premium' ) . ' - Free',
				'desc' => 'api import :: transfers',
				'id'   => 'api_import__transfers__free',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'API Import - Transfers', 'anwp-football-leagues-premium' ) . ' - Loan',
				'desc' => 'api import :: transfers',
				'id'   => 'api_import__transfers__loan',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Match Prediction', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__match_prediction',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Prediction', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__prediction',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Who Will Win', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__who_will_win',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Team comparison', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__team_comparison',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Current Form', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__current_form',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Attacking', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__attacking',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Defensive', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__defensive',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Poisson Distribution', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__poisson_distribution',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Head-to-Head', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__h2h',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Goals', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__goals',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Total', 'anwp-football-leagues-premium' ),
				'desc' => 'match :: prediction',
				'id'   => 'match__prediction__total',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Red Card', 'anwp-football-leagues-premium' ),
				'desc' => 'api import :: injuries',
				'id'   => 'api_import__injuries__red_card',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Yellow Cards', 'anwp-football-leagues-premium' ),
				'desc' => 'api import :: injuries',
				'id'   => 'api_import__injuries__yellow_cards',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'questionable', 'anwp-football-leagues-premium' ),
				'desc' => 'api import :: injuries',
				'id'   => 'api_import__injuries__questionable',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Red card suspension', 'anwp-football-leagues-premium' ),
				'desc' => 'suspension :: comment',
				'id'   => 'suspension__comment__red_card',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Yellow card suspension', 'anwp-football-leagues-premium' ),
				'desc' => 'suspension :: comment',
				'id'   => 'suspension__comment__yellow_cards',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'API Import - Predictions - "Winner"',
				'desc' => 'api import :: predictions',
				'id'   => 'api_import__predictions__winner',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'API Import - Predictions - "Double chance"',
				'desc' => 'api import :: predictions',
				'id'   => 'api_import__predictions__double_chance',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'API Import - Predictions - "draw"',
				'desc' => 'api import :: predictions',
				'id'   => 'api_import__predictions__draw',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'API Import - Predictions - "or"',
				'desc' => 'api import :: predictions',
				'id'   => 'api_import__predictions__or',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'API Import - Predictions - "and"',
				'desc' => 'api import :: predictions',
				'id'   => 'api_import__predictions__and',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'API Import - Predictions - "Combo Double chance"',
				'desc' => 'api import :: predictions',
				'id'   => 'api_import__predictions__combo_double_chance',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'API Import - Predictions - "No predictions available"',
				'desc' => 'api import :: predictions',
				'id'   => 'api_import__predictions__no_predictions_available',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'API Import - Predictions - "goals"',
				'desc' => 'api import :: predictions',
				'id'   => 'api_import__predictions__goals',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'API Import - Predictions - "+"',
				'desc' => 'api import :: predictions',
				'id'   => 'api_import__predictions__plus',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'API Import - Predictions - "-"',
				'desc' => 'api import :: predictions',
				'id'   => 'api_import__predictions__minus',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Coach',
				'desc' => 'api import :: coach',
				'id'   => 'api_import__coach',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Yellow Cards',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__card_y',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Yellow Cards (Home)',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__card_y_h',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Yellow Cards (Away)',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__card_y_a',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Red Cards',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__card_r',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Red Cards (Home)',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__card_r_h',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Red Cards (Away)',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__card_r_a',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Fouls',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__foul',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Fouls (Home)',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__foul_h',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Fouls (Away)',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__foul_a',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Per Match',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__per_match',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Games',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__games',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Referee',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__referee',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'All',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__all_abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'All',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__all_abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'All',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__all_tooltip',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'PG',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__pg_abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Per Game',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__pg_tooltip',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'H',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__h_abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Home',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__h_tooltip',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'A',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__a_abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Away',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__a_tooltip',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'H-PG',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__h_pg_abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Home - Per Game',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__h_pg_tooltip',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'A-PG',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__a_pg_abbr',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => 'Away - Per Game',
				'desc' => 'referee :: stats',
				'id'   => 'stats__referee__a_pg_tooltip',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => __( 'Substitute In', 'anwp-football-leagues-premium' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__sub_in',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => __( 'Minutes', 'anwp-football-leagues' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__minutes',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => __( 'Cards (All)', 'anwp-football-leagues-premium' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__cards_all',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => __( 'Cards - Yellow', 'anwp-football-leagues-premium' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__card_y',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => __( 'Cards - Red', 'anwp-football-leagues-premium' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__card_r',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => __( 'Goal From Penalty', 'anwp-football-leagues' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__goals_penalty',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => __( 'Own Goals', 'anwp-football-leagues' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__own_goals',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html__( 'Per Game', 'anwp-football-leagues-premium' ),
				'desc' => 'player :: stats panel',
				'id'   => 'player__stats_panel__per_game',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html_x( 'All', 'standing', 'anwp-football-leagues-premium' ),
				'desc' => 'standing :: all',
				'id'   => 'standing__filter__all',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html_x( 'Home', 'standing', 'anwp-football-leagues-premium' ),
				'desc' => 'standing :: home',
				'id'   => 'standing__filter__home',
				'type' => 'anwp_fl_text',
			],
			[
				'name' => esc_html_x( 'Away', 'standing', 'anwp-football-leagues-premium' ),
				'desc' => 'standing :: away',
				'id'   => 'standing__filter__away',
				'type' => 'anwp_fl_text',
			],
		];

		return array_merge( $options, $options_new );
	}
}
