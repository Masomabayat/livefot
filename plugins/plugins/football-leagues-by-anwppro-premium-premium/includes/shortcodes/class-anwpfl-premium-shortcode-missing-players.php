<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Missing Players.
 *
 * @since   0.10.3
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Stats Players.
 */
class AnWPFL_Premium_Shortcode_Missing_Players {

	private $shortcode = 'anwpfl-missing-players';

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
		add_action( 'init', [ $this, 'shortcode_init' ] );

		// Add shortcode option
		add_filter( 'anwpfl/shortcode-pro/get_shortcode_options', [ $this, 'add_shortcode_option' ] );

		// Load shortcode form
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_missing-players', [ $this, 'load_shortcode_form' ] );
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
				<th scope="row">
					<label for="fl-form-shortcode-club_id"><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="club_id" data-fl-type="select2" id="fl-form-shortcode-club_id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<option value="competition">- <?php echo esc_html__( 'All Competition Clubs', 'anwp-football-leagues-premium' ); ?> -</option>
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
				<th scope="row"><label for="fl-form-shortcode-season_grouped"><?php echo esc_html__( 'Show all season matches in one table.', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="season_grouped" data-fl-type="select" id="fl-form-shortcode-season_grouped" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
					<span class="anwp-option-desc"><?php echo esc_html__( 'Set to "No" to create a separate table for every competition. Works only when Season is set.', 'anwp-football-leagues-premium' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-all_players"><?php echo esc_html__( 'Show all players', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="all_players" data-fl-type="select" id="fl-form-shortcode-all_players" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-finished_only"><?php echo esc_html__( 'Show finished games only', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="finished_only" data-fl-type="select" id="fl-form-shortcode-finished_only" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_date"><?php echo esc_html__( 'Show date', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_date" data-fl-type="select" id="fl-form-shortcode-show_date" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-standing-sections"><?php echo esc_html__( 'Sections', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="sections" data-fl-type="select" id="fl-form-shortcode-standing-sections" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'all (missed + cards)', 'anwp-football-leagues-premium' ); ?></option>
						<option value="missed"><?php echo esc_html__( 'Missed Matches', 'anwp-football-leagues' ); ?></option>
						<option value="cards"><?php echo esc_html__( 'Cards', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-missing-players">
		<?php
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

		$data['missing-players'] = __( 'Missing Players and Cards', 'anwp-football-leagues-premium' );

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
			'show_date'         => '',
			'competition_id'    => '',
			'competition_title' => false,
			'club_id'           => '',
			'season_id'         => '',
			'season_grouped'    => '',
			'all_players'       => 0,
			'finished_only'     => 1,
			'sections'          => '', // cards, missed
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-missing-players', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Missing_Players();
