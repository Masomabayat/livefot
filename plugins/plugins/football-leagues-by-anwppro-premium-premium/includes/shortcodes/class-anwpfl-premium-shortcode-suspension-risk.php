<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Suspension Risk.
 *
 * @since   0.13.7
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Suspension Risk.
 */
class AnWPFL_Premium_Shortcode_Suspension_Risk {

	private $shortcode = 'anwpfl-suspension-risk';

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

		$data['suspension-risk'] = __( 'Suspension Risk', 'anwp-football-leagues-premium' );

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_suspension-risk', [ $this, 'load_shortcode_form' ] );
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
				<th scope="row"><label for="fl-form-shortcode-competition_id"><?php echo esc_html__( 'Competition ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="competition_id" data-fl-type="select2" id="fl-form-shortcode-competition_id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->competition->get_competition_options( false ) as $competition_id => $competition_title ) : ?>
							<option value="<?php echo esc_attr( $competition_id ); ?>"><?php echo esc_html( $competition_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-game_id"><?php echo esc_html__( 'Match ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="game_id" data-fl-type="text" type="text" id="fl-form-shortcode-include_ids" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="match" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-header"><?php echo esc_html__( 'Header', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="header" data-fl-type="text" type="text" id="fl-form-shortcode-header" value="" class="fl-shortcode-attr regular-text">
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_links"><?php echo esc_html__( 'Show link to Player profile', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_links" data-fl-type="select" id="fl-form-shortcode-show_links" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_photos"><?php echo esc_html__( 'Show Photo', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="show_photos" data-fl-type="select" id="fl-form-shortcode-show_photos" class="postform fl-shortcode-attr">
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_teams"><?php echo esc_html__( 'Show Teams', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_teams" data-fl-type="select" id="fl-form-shortcode-show_teams" class="postform fl-shortcode-attr">
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-suspension-risk">
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
			'club_id'        => '',
			'game_id'        => '',
			'header'         => '',
			'class'          => '',
			'show_links'     => 0,
			'show_photos'    => 1,
			'show_teams'     => 1,
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-suspension-risk', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Suspension_Risk();
