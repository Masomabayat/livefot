<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Stats Players.
 *
 * @since   0.5.7
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Stats Players.
 */
class AnWPFL_Premium_Shortcode_Stats_Players {

	private $shortcode = 'anwpfl-stats-players';

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

		$data['stats-players'] = __( 'Stats :: Players', 'anwp-football-leagues-premium' );

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_stats-players', [ $this, 'load_shortcode_form' ] );
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
				<th scope="row"><label for="fl-form-shortcode-rows"><?php echo esc_html__( 'Rows', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="rows" data-fl-type="select" id="fl-form-shortcode-rows" class="postform fl-shortcode-attr">
						<option value="-1" selected><?php echo esc_html__( 'All', 'anwp-football-leagues' ); ?></option>
						<option value="10">10</option>
						<option value="25">25</option>
						<option value="50">50</option>
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
				<th scope="row"><label for="fl-form-shortcode-sections"><?php echo esc_html__( 'Sections', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="sections" data-fl-type="select2" id="fl-form-shortcode-sections" class="postform fl-shortcode-attr fl-shortcode-select2" multiple="multiple">
						<option value="club"><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></option>
						<option value="position"><?php echo esc_html__( 'Position', 'anwp-football-leagues' ); ?></option>
						<option value="appearance"><?php echo esc_html__( 'Appearance', 'anwp-football-leagues-premium' ); ?></option>
						<option value="minutes"><?php echo esc_html__( 'Minutes', 'anwp-football-leagues' ); ?></option>
						<option value="cards"><?php echo esc_html__( 'Cards', 'anwp-football-leagues' ); ?></option>
						<option value="goals"><?php echo esc_html__( 'Goals', 'anwp-football-leagues' ); ?></option>
						<option value="goals_penalty"><?php echo esc_html__( 'Goal From Penalty', 'anwp-football-leagues' ); ?></option>
						<option value="goals_own"><?php echo esc_html__( 'Own Goals', 'anwp-football-leagues' ); ?></option>
						<option value="assists"><?php echo esc_html__( 'Assists', 'anwp-football-leagues' ); ?></option>
						<option value="goals_conceded"><?php echo esc_html__( 'Goals Conceded', 'anwp-football-leagues' ); ?></option>
						<option value="clean_sheets"><?php echo esc_html__( 'Clean Sheets', 'anwp-football-leagues' ); ?></option>
					</select>
					<span class="anwp-option-desc"><?php echo esc_html__( 'Optional, leave empty for all', 'anwp-football-leagues' ); ?></span>
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
				<th scope="row"><label for="fl-form-shortcode-links"><?php echo esc_html__( 'Show link to profile', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="links" data-fl-type="select" id="fl-form-shortcode-links" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
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
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-stats-players">
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
			'club_id'        => '',
			'rows'           => '',
			'type'           => '',
			'links'          => 0,
			'paging'         => 1,
			'source'         => '',
			'sections'       => '',
			'date_from'      => '',
			'date_to'        => '',
			'layout_mod'     => 'even',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( '%competition_id%' === $atts['competition_id'] && ! empty( $_GET['competition_id'] ) && absint( $_GET['competition_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$atts['competition_id'] = absint( $_GET['competition_id'] );
		}

		// Validate shortcode attr
		$atts['season_id']      = intval( $atts['season_id'] );
		$atts['club_id']        = intval( $atts['club_id'] );
		$atts['competition_id'] = intval( $atts['competition_id'] );

		$atts['rows'] = sanitize_text_field( $atts['rows'] );
		$atts['type'] = sanitize_text_field( $atts['type'] );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-stats-players', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Stats_Players();
