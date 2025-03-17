<?php
/**
 * AnWP Football Leagues Premium :: Match Report
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */
class AnWPFL_Premium_Match_Report {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( AnWP_Football_Leagues_Premium $plugin ) {

		// Save main plugin object
		$this->plugin = $plugin;

		// Init hooks
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_report_sending_scripts' ] );
		add_action( 'wp_ajax_fl_send_match_report', [ $this, 'send_match_report' ] );
	}

	/**
	 * Send Match report.
	 *
	 * @since 0.12.6
	 */
	public function send_match_report() {

		// Check if our nonce is set.
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax_anwpfl_nonce' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		$match_id = isset( $_POST['match_id'] ) ? absint( $_POST['match_id'] ) : 0;

		if ( ! $match_id || ! current_user_can( 'edit_post', $match_id ) ) {
			wp_send_json_error( 'Error : Invalid Data' );
		}

		if ( 'yes' !== AnWPFL_Premium_Options::get_value( 'send_match_report_by_email' ) ) {
			wp_send_json_error( 'Error : Inactive' );
		}

		// Prepare match related data
		$game_data     = anwp_fl()->match->get_game_data( $match_id );
		$match_home_id = absint( $game_data['home_club'] );
		$match_away_id = absint( $game_data['away_club'] );

		$game_data['events']        = [];
		$game_data['parsed_events'] = [];

		if ( ! empty( $game_data['match_events'] ) && ! empty( json_decode( $game_data['match_events'] ) ) ) {
			$game_data['parsed_events'] = anwp_fl()->helper->parse_match_events( json_decode( $game_data['match_events'] ) );
			$game_data['events']        = json_decode( $game_data['match_events'] );
		}

		$game_data['players'] = anwp_fl()->player->get_game_players( $game_data );

		/*
		|--------------------------------------------------------------------
		| Get recipients
		|--------------------------------------------------------------------
		*/
		$recipients   = [];
		$admin_emails = AnWPFL_Premium_Options::get_value( 'send_match_report_admin_email', [] );

		if ( ! empty( $admin_emails ) && is_array( $admin_emails ) ) {
			$recipients = $admin_emails;
		}

		$home_recipients = get_post_meta( $match_home_id, '_anwpfl_report_email', true );
		$away_recipients = get_post_meta( $match_away_id, '_anwpfl_report_email', true );

		if ( ! empty( $home_recipients ) && is_array( $home_recipients ) ) {
			$recipients = array_merge( $recipients, $home_recipients );
		}

		if ( ! empty( $away_recipients ) && is_array( $away_recipients ) ) {
			$recipients = array_merge( $recipients, $away_recipients );
		}

		/*
		|--------------------------------------------------------------------
		| Prepare mail data
		|--------------------------------------------------------------------
		*/
		$mail_subject = AnWPFL_Premium_Options::get_value( 'send_match_report_email_subject', '' );

		if ( $mail_subject ) {
			$mail_subject = $this->replace_match_report_variables( $mail_subject, $game_data, $match_id );
		}

		$mail_body = AnWPFL_Premium_Options::get_value( 'send_match_report_tmpl', '' );

		if ( $mail_body ) {
			$mail_body = $this->replace_match_report_variables( nl2br( $mail_body ), $game_data, $match_id );
		}

		/*
		|--------------------------------------------------------------------
		| Send Email
		|--------------------------------------------------------------------
		*/
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		foreach ( $recipients as $to ) {
			if ( ! filter_var( $to, FILTER_VALIDATE_EMAIL ) ) {
				continue;
			}

			try {
				wp_mail( $to, $mail_subject, $mail_body, $headers );
			} catch ( Exception $e ) {
				continue;
			}
		}

		wp_send_json_success();
	}

	/**
	 * Replace dynamic variables.
	 *
	 * @param string $block_text
	 * @param array  $game_data
	 * @param int    $match_id
	 *
	 * @return string
	 */
	public function replace_match_report_variables( string $block_text, array $game_data, int $match_id ) {

		$match_competition_id = absint( $game_data['main_stage_id'] ) ?: $game_data['competition_id'];
		$match_season_id      = absint( $game_data['season_id'] );
		$match_league_id      = absint( $game_data['league_id'] );
		$match_match_week     = absint( $game_data['match_week'] );
		$match_home_id        = absint( $game_data['home_club'] );
		$match_away_id        = absint( $game_data['away_club'] );
		$match_kickoff        = $game_data['kickoff'];

		$block_text = str_ireplace( '%match_id%', $match_id, $block_text );
		$block_text = str_ireplace( '%competition_id%', $match_competition_id, $block_text );
		$block_text = str_ireplace( '%season_id%', $match_season_id, $block_text );
		$block_text = str_ireplace( '%league_id%', $match_league_id, $block_text );
		$block_text = str_ireplace( '%matchweek%', $match_match_week, $block_text );
		$block_text = str_ireplace( '%home_id%', $match_home_id, $block_text );
		$block_text = str_ireplace( '%away_id%', $match_away_id, $block_text );

		$block_text = str_ireplace( '%kickoff%', date_i18n( anwp_fl()->get_option_value( 'custom_match_date_format' ) ?: 'Y-m-d', get_date_from_gmt( $match_kickoff, 'U' ) ), $block_text );

		// %home_club_title%
		if ( strpos( $block_text, '%home_club_title%' ) !== false ) {
			$block_text = str_ireplace( '%home_club_title%', anwp_fl()->club->get_club_title_by_id( $match_home_id ), $block_text );
		}

		// %away_club_title%
		if ( strpos( $block_text, '%away_club_title%' ) !== false ) {
			$block_text = str_ireplace( '%away_club_title%', anwp_fl()->club->get_club_title_by_id( $match_away_id ), $block_text );
		}

		// %competition_title%
		if ( strpos( $block_text, '%competition_title%' ) !== false ) {
			$block_text = str_ireplace( '%competition_title%', anwp_fl()->competition->get_competition_title( $game_data['competition_id'] ), $block_text );
		}

		// %season_title%
		if ( strpos( $block_text, '%season_title%' ) !== false ) {
			$block_text = str_ireplace( '%season_title%', get_term( $match_season_id )->name, $block_text );
		}

		// %league_title%
		if ( strpos( $block_text, '%league_title%' ) !== false ) {
			$block_text = str_ireplace( '%league_title%', get_term( $match_league_id )->name, $block_text );
		}

		// %match_date%
		if ( strpos( $block_text, '%match_date%' ) !== false ) {
			$date_format = anwp_fl()->get_option_value( 'custom_match_date_format' ) ?: get_option( 'date_format' );
			$block_text  = str_ireplace( '%match_date%', date_i18n( $date_format, get_date_from_gmt( $match_kickoff, 'U' ) ), $block_text );
		}

		// %match_time%
		if ( strpos( $block_text, '%match_time%' ) !== false ) {
			$time_format = anwp_fl()->get_option_value( 'custom_match_time_format' ) ?: get_option( 'time_format' );
			$block_text  = str_ireplace( '%match_time%', date_i18n( $time_format, get_date_from_gmt( $match_kickoff, 'U' ) ), $block_text );
		}

		// Populate other fields
		$game_fields = [
			'attendance',
			'home_goals',
			'away_goals',
			'home_goals_half',
			'away_goals_half',
			'home_cards_y',
			'away_cards_y',
			'home_cards_yr',
			'away_cards_yr',
			'home_cards_r',
			'away_cards_r',
			'home_corners',
			'away_corners',
			'home_fouls',
			'away_fouls',
		];

		foreach ( $game_fields as $game_field ) {
			if ( strpos( $block_text, '%' . $game_field . '%' ) !== false ) {
				$block_text = str_ireplace( '%' . $game_field . '%', $game_data[ $game_field ], $block_text );
			}
		}

		// All cards
		if ( strpos( $block_text, '%home_cards%' ) !== false ) {
			$cards_number = (int) $game_data['home_cards_y'] + (int) $game_data['home_cards_yr'] + (int) $game_data['home_cards_r'];
			$block_text   = str_ireplace( '%home_cards%', $cards_number, $block_text );
		}

		if ( strpos( $block_text, '%away_cards%' ) !== false ) {
			$cards_number = (int) $game_data['away_cards_y'] + (int) $game_data['away_cards_yr'] + (int) $game_data['away_cards_r'];
			$block_text   = str_ireplace( '%away_cards%', $cards_number, $block_text );
		}

		// Referees
		$ref_main_id = absint( $game_data['referee'] );
		$ref_a1_id   = absint( get_post_meta( $match_id, '_anwpfl_assistant_1', true ) );
		$ref_a2_id   = absint( get_post_meta( $match_id, '_anwpfl_assistant_2', true ) );
		$ref_4_id    = absint( get_post_meta( $match_id, '_anwpfl_referee_fourth', true ) );

		if ( strpos( $block_text, '%ref_main_name%' ) !== false ) {
			$referee_name = $ref_main_id ? get_the_title( $ref_main_id ) : '';
			$block_text   = str_ireplace( '%ref_main_name%', $referee_name, $block_text );
		}

		if ( strpos( $block_text, '%ref_a1_name%' ) !== false ) {
			$referee_name = $ref_a1_id ? get_the_title( $ref_a1_id ) : '';
			$block_text   = str_ireplace( '%ref_a1_name%', $referee_name, $block_text );
		}

		if ( strpos( $block_text, '%ref_a2_name%' ) !== false ) {
			$referee_name = $ref_a2_id ? get_the_title( $ref_a2_id ) : '';
			$block_text   = str_ireplace( '%ref_a2_name%', $referee_name, $block_text );
		}

		if ( strpos( $block_text, '%ref_4_name%' ) !== false ) {
			$referee_name = $ref_4_id ? get_the_title( $ref_4_id ) : '';
			$block_text   = str_ireplace( '%ref_4_name%', $referee_name, $block_text );
		}

		// Stadium
		$stadium_id = absint( $game_data['stadium_id'] );

		if ( strpos( $block_text, '%stadium_title%' ) !== false ) {
			$stadium_title = $stadium_id ? get_the_title( $stadium_id ) : '';
			$block_text    = str_ireplace( '%stadium_title%', $stadium_title, $block_text );
		}

		if ( strpos( $block_text, '%stadium_address%' ) !== false ) {
			$stadium_address = $stadium_id ? get_post_meta( $stadium_id, '_anwpfl_address', true ) : '';
			$block_text      = str_ireplace( '%stadium_address%', $stadium_address, $block_text );
		}

		if ( strpos( $block_text, '%stadium_city%' ) !== false ) {
			$stadium_city = $stadium_id ? get_post_meta( $stadium_id, '_anwpfl_city', true ) : '';
			$block_text   = str_ireplace( '%stadium_city%', $stadium_city, $block_text );
		}

		if ( strpos( $block_text, '%stadium_capacity%' ) !== false ) {
			$stadium_capacity = $stadium_id ? get_post_meta( $stadium_id, '_anwpfl_capacity', true ) : '';
			$block_text       = str_ireplace( '%stadium_capacity%', $stadium_capacity, $block_text );
		}

		if ( strpos( $block_text, '%stadium_surface%' ) !== false ) {
			$stadium_surface = $stadium_id ? get_post_meta( $stadium_id, '_anwpfl_surface', true ) : '';
			$block_text      = str_ireplace( '%stadium_surface%', $stadium_surface, $block_text );
		}

		// Coach
		$coach_home_id = absint( $game_data['coach_home'] );
		$coach_away_id = absint( $game_data['coach_away'] );

		if ( strpos( $block_text, '%coach_home%' ) !== false ) {
			$coach_home = $coach_home_id ? get_the_title( $coach_home_id ) : '';
			$block_text = str_ireplace( '%coach_home%', $coach_home, $block_text );
		}

		if ( strpos( $block_text, '%coach_away%' ) !== false ) {
			$coach_away = $coach_away_id ? get_the_title( $coach_away_id ) : '';
			$block_text = str_ireplace( '%coach_away%', $coach_away, $block_text );
		}

		// LineUps
		if ( strpos( $block_text, '%lineups_home%' ) !== false ) {
			$block_text = str_ireplace( '%lineups_home%', $this->get_report_lineups( $game_data['home_line_up'], $game_data ), $block_text );
		}

		if ( strpos( $block_text, '%lineups_away%' ) !== false ) {
			$block_text = str_ireplace( '%lineups_away%', $this->get_report_lineups( $game_data['away_line_up'], $game_data ), $block_text );
		}

		// Goals text
		if ( strpos( $block_text, '%goals_text%' ) !== false ) {
			$block_text = str_ireplace( '%goals_text%', $this->get_report_goals( $game_data ), $block_text );
		}

		// Goals text with club
		if ( strpos( $block_text, '%goals_text_player_club%' ) !== false ) {
			$block_text = str_ireplace( '%goals_text_player_club%', $this->get_report_goals( $game_data, true ), $block_text );
		}

		// Cards text
		if ( strpos( $block_text, '%cards_text%' ) !== false ) {
			$block_text = str_ireplace( '%cards_text%', $this->get_report_cards( $game_data ), $block_text );
		}

		if ( strpos( $block_text, '%cards_text_y%' ) !== false ) {
			$block_text = str_ireplace( '%cards_text_y%', $this->get_report_cards( $game_data, 'y' ), $block_text );
		}

		if ( strpos( $block_text, '%cards_text_yr%' ) !== false ) {
			$block_text = str_ireplace( '%cards_text_yr%', $this->get_report_cards( $game_data, 'yr' ), $block_text );
		}

		if ( strpos( $block_text, '%cards_text_r%' ) !== false ) {
			$block_text = str_ireplace( '%cards_text_r%', $this->get_report_cards( $game_data, 'r' ), $block_text );
		}

		if ( strpos( $block_text, '%cards_text_player_club%' ) !== false ) {
			$block_text = str_ireplace( '%cards_text_player_club%', $this->get_report_cards( $game_data, '', true ), $block_text );
		}

		if ( strpos( $block_text, '%cards_text_y_player_club%' ) !== false ) {
			$block_text = str_ireplace( '%cards_text_y_player_club%', $this->get_report_cards( $game_data, 'y', true ), $block_text );
		}

		if ( strpos( $block_text, '%cards_text_yr_player_club%' ) !== false ) {
			$block_text = str_ireplace( '%cards_text_yr_player_club%', $this->get_report_cards( $game_data, 'yr', true ), $block_text );
		}

		if ( strpos( $block_text, '%cards_text_r_player_club%' ) !== false ) {
			$block_text = str_ireplace( '%cards_text_r_player_club%', $this->get_report_cards( $game_data, 'r', true ), $block_text );
		}

		// Comments list
		if ( strpos( $block_text, '%comments_list%' ) !== false ) {
			$block_text = str_ireplace( '%comments_list%', $this->get_report_comments( $game_data['events'] ), $block_text );
		}

		return $block_text;
	}

	/**
	 * Get list of events for the Game report
	 *
	 * @param $game_events
	 *
	 * @return string
	 * @since 0.12.6
	 */
	private function get_report_comments( $game_events ): string {

		if ( empty( $game_events ) || ! is_array( $game_events ) ) {
			return '';
		}

		$comments = [];

		foreach ( $game_events as $event ) {
			if ( ! in_array( $event->type, [ 'commentary', 'significant_event' ], true ) || empty( $event->comment ) || ! trim( $event->comment ) ) {
				continue;
			}

			$comments[] = $event->comment;
		}

		if ( empty( $comments ) ) {
			return '';
		}

		return '<ul><li>' . implode( '</li><li>', $comments ) . '</li></ul>';
	}

	/**
	 * Get list of goals for the Game report in test string format
	 *
	 * @param array $game_data
	 * @param bool $show_club
	 *
	 * @return string
	 * @since 0.12.6
	 */
	private function get_report_goals( array $game_data, bool $show_club = false ): string {

		$game_events = $game_data['parsed_events'];

		if ( empty( $game_events['goals'] ) || ! is_array( $game_events['goals'] ) ) {
			return '';
		}

		$goals = [];

		foreach ( $game_events['goals'] as $goal ) {
			if ( empty( absint( $goal->player ) ) ) {
				continue;
			}

			$goal_text = $game_data['players'][ $goal->player ]['short_name'] ?? '';

			if ( ! empty( $goal->minute ) ) {
				$goal_text .= ' ' . $goal->minute . '\'';
			}

			if ( ! empty( $goal->minuteAdd ) ) {
				$goal_text .= '+' . $goal->minuteAdd;
			}

			if ( ! empty( $goal->ownGoal ) && 'yes' === $goal->ownGoal ) {
				$goal_text .= ' (' . mb_strtolower( esc_html__( 'Own Goal', 'anwp-football-leagues' ) ) . ')';
			}

			if ( ! empty( $goal->fromPenalty ) && 'yes' === $goal->fromPenalty ) {
				$goal_text .= ' (' . mb_strtolower( esc_html__( 'From Penalty', 'anwp-football-leagues' ) ) . ')';
			}

			if ( $show_club && ! empty( $goal->club ) ) {
				$goal_text .= ' (' . anwp_fl()->club->get_club_title_by_id( $goal->club ) . ')';
			}

			$goals[] = $goal_text;
		}

		return implode( ', ', $goals );
	}

	/**
	 * Get list of cards for the Game report in test string format
	 *
	 * @param array  $game_data
	 * @param string $card_type
	 * @param bool   $show_club
	 *
	 * @return string
	 * @since 0.12.6
	 */
	private function get_report_cards( array $game_data, string $card_type = '', bool $show_club = false ): string {

		$game_events = $game_data['parsed_events'];

		if ( empty( $game_events['cards'] ) || ! is_array( $game_events['cards'] ) ) {
			return '';
		}

		$cards = [];

		foreach ( $game_events['cards'] as $card ) {
			if ( empty( absint( $card->player ) ) ) {
				continue;
			}

			if ( ! empty( $card_type ) && $card_type !== $card->card ) {
				continue;
			}

			$card_text = $game_data['players'][ $card->player ]['short_name'] ?? '';


			if ( ! empty( $card->minute ) ) {
				$card_text .= ' ' . $card->minute . '\'';
			}

			if ( ! empty( $card->minuteAdd ) ) {
				$card_text .= '+' . $card->minuteAdd;
			}

			if ( ! empty( $card->card ) && 'yr' === $card->card && empty( $card_type ) ) {
				$card_text .= ' (' . mb_strtolower( esc_html__( '2nd Yellow > Red Card', 'anwp-football-leagues' ) ) . ')';
			}

			if ( ! empty( $card->card ) && 'r' === $card->card && empty( $card_type ) ) {
				$card_text .= ' (' . mb_strtolower( esc_html__( 'Red Card', 'anwp-football-leagues' ) ) . ')';
			}

			if ( $show_club && ! empty( $card->club ) ) {
				$card_text .= ' (' . anwp_fl()->club->get_club_title_by_id( $card->club ) . ')';
			}

			$cards[] = $card_text;
		}

		return implode( ', ', $cards );
	}

	/**
	 * Get LineUps for game report
	 *
	 * @param string $lineups
	 * @param array  $game_data
	 *
	 * @return string
	 */
	private function get_report_lineups( string $lineups, array $game_data ): string {

		$game_events = $game_data['parsed_events'];

		if ( empty( $lineups ) ) {
			return '';
		}

		$players = [];

		foreach ( explode( ',', $lineups ) as $player_id ) {
			$players[] = [
				'id'   => absint( $player_id ),
				'name' => $game_data['players'][ $player_id ]['short_name'] ?? '',
				'subs' => [],
			];
		}

		if ( ! empty( $game_events['subs'] ) && is_array( $game_events['subs'] ) ) {
			foreach ( $game_events['subs'] as $sub ) {
				if ( ! absint( $sub->playerOut ) || ! absint( $sub->player ) ) {
					continue;
				}

				foreach ( $players as $player_index => $player ) {
					if ( absint( $sub->playerOut ) === $player['id'] ) {
						$players[ $player_index ]['subs'][] = [
							'id'      => absint( $sub->player ),
							'name'    => $game_data['players'][ $sub->player ]['short_name'] ?? '',
							'min'     => $sub->minute,
							'min_add' => $sub->minuteAdd,
						];

						continue;
					}

					foreach ( $player['subs'] as $sub_player ) {
						if ( absint( $sub->playerOut ) === $sub_player['id'] ) {
							$players[ $player_index ]['subs'][] = [
								'id'      => absint( $sub->player ),
								'name'    => $game_data['players'][ $sub->player ]['short_name'] ?? '',
								'min'     => $sub->minute,
								'min_add' => $sub->minuteAdd,
							];

							continue;
						}
					}
				}
			}
		}

		if ( empty( $players ) || ! is_array( $players ) ) {
			return '';
		}

		$output = '';

		foreach ( $players as $index => $player ) {
			$output .= $index ? ', ' : '';
			$output .= $player['name'];

			if ( ! empty( $player['subs'] ) && is_array( $player['subs'] ) ) {

				$output .= ' (';

				foreach ( $player['subs'] as $sub_index => $sub_player ) {
					$output .= $sub_index ? ', ' : '';
					$output .= $sub_player['name'];
					$output .= $sub_player['min'] ? ( ' ' . $sub_player['min'] . '\'' ) : '';
					$output .= $sub_player['min_add'] ? ( '+' . $sub_player['min_add'] ) : '';
				}

				$output .= ')';
			}
		}

		return $output;
	}

	/**
	 * Load Match report sending admin scripts
	 *
	 * @since 0.12.6
	 */
	public function admin_enqueue_report_sending_scripts() {

		$current_screen = get_current_screen();

		// Load Common files
		if ( 'edit-anwp_match' === $current_screen->id ) {
			wp_enqueue_script( 'anwpfl_admin_pro_report', AnWP_Football_Leagues_Premium::url( 'admin/js/anwpfl-premium-send-report.js' ), [], AnWP_Football_Leagues_Premium::VERSION, false );
		}
	}
}
