<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Standings
 *
 * @since   0.11.2
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Standings
 */
class AnWPFL_Premium_Shortcode_Standings {

	private $shortcode = 'anwpfl-standings';

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_standings', [ $this, 'load_shortcode_form' ] );
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
				<th scope="row"><label for="fl-form-shortcode-match_id"><?php echo esc_html__( 'Match ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="match_id" data-fl-type="text" type="text" id="fl-form-shortcode-match_id" value="" class="fl-shortcode-attr regular-text code">
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
				<th scope="row"><label for="fl-form-shortcode-show_titles"><?php echo esc_html__( 'Show Standing Title', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="show_titles" data-fl-type="select" id="fl-form-shortcode-show_titles" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-standing-layout"><?php echo esc_html__( 'Layout', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="layout" data-fl-type="select" id="fl-form-shortcode-standing-layout" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'default', 'anwp-football-leagues' ); ?></option>
						<option value="mini"><?php echo esc_html__( 'mini', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-partial"><?php echo esc_html__( 'Show Partial Data', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="partial" data-fl-type="text" type="text" id="fl-form-shortcode-partial" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'Eg.: "1-5" (show teams from 1 to 5 place), "45" - show table slice with specified team ID in the middle', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-standing-bottom_link"><?php echo esc_html__( 'Show link to', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="bottom_link" data-fl-type="select" id="fl-form-shortcode-standing-bottom_link" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'none', 'anwp-football-leagues' ); ?></option>
						<option value="competition"><?php echo esc_html__( 'competition', 'anwp-football-leagues' ); ?></option>
						<option value="standing"><?php echo esc_html__( 'standing', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_notes"><?php echo esc_html__( 'Show Notes', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="show_notes" data-fl-type="select" id="fl-form-shortcode-show_notes" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-standings">
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

		$data['standings'] = __( 'Standings', 'anwp-football-leagues-premium' );

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
			'match_id'       => '',
			'season_id'      => '',
			'club_id'        => '',
			'layout'         => '',
			'partial'        => '',
			'bottom_link'    => '',
			'show_notes'     => 1,
			'show_titles'    => 1,
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-standings', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Standings();
