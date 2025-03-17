<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Live Matches.
 *
 * @since   0.15.0
 * @package AnWP_Football_Leagues_Premium
 */

class AnWPFL_Premium_Shortcode_Live {

	private $shortcode = 'anwpfl-live';

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

		// Load shortcode form
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_live', [ $this, 'load_shortcode_form' ] );

		// Add shortcode option
		add_filter( 'anwpfl/shortcode-pro/get_shortcode_options', [ $this, 'add_shortcode_option' ] );
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
			'competition_id'        => '',
			'filter_by_clubs'       => '',
			'class'                 => '',
			'group_by'              => '',
			'group_by_header_style' => '',
			'show_club_logos'       => 1,
			'competition_logo'      => 1,
			'no_data_text'          => '',
			'layout'                => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-live', $atts );
	}

	/**
	 * Add shortcode options.
	 * Used in Shortcode Builder and Shortcode TinyMCE tool.
	 *
	 * @param array $data Shortcode options.
	 *
	 * @return array
	 * @since 0.15.0
	 */
	public function add_shortcode_option( $data ) {
		$data['live'] = __( 'LIVE Matches', 'anwp-football-leagues-premium' );

		return $data;
	}

	/**
	 * Load shortcode form with options.
	 * Used in Shortcode Builder and Shortcode TinyMCE tool.
	 */
	public function load_shortcode_form() {

		$available_layouts = [
			'slim',
			'modern',
			'simple',
		];
		?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-competition_id"><?php echo esc_html__( 'Competition ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="competition_id" data-fl-type="text" type="text" id="fl-form-shortcode-competition_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="competition" data-single="no">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-filter_by_clubs"><?php echo esc_html__( 'Filter by Clubs', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<input name="filter_by_clubs" data-fl-type="text" type="text" id="fl-form-shortcode-filter_by_clubs" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="club" data-single="no">
						<span class="dashicons dashicons-search"></span>
					</button>
					<span class="anwp-option-desc"><?php echo esc_html__( 'comma-separated list of IDs', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-group_by"><?php echo esc_html__( 'Group By', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="group_by" data-fl-type="select" id="fl-form-shortcode-group_by" class="postform fl-shortcode-attr">
						<option value=""><?php echo esc_html__( 'none', 'anwp-football-leagues' ); ?></option>
						<option value="competition"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-group_by_header_style"><?php echo esc_html__( 'Group By Header Style', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="group_by_header_style" data-fl-type="select" id="fl-form-shortcode-group_by_header_style" class="postform fl-shortcode-attr">
						<option value=""><?php echo esc_html__( 'Default', 'anwp-football-leagues' ); ?></option>
						<option value="secondary"><?php echo esc_html__( 'Secondary', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_club_logos"><?php echo esc_html__( 'Show club logos', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="show_club_logos" data-fl-type="select" id="fl-form-shortcode-show_club_logos" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-competition_logo"><?php echo esc_html__( 'Show Competition Logo', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="competition_logo" data-fl-type="select" id="fl-form-shortcode-competition_logo" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-no_data_text"><?php echo esc_html__( 'No data text', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="no_data_text" data-fl-type="text" type="text" id="fl-form-shortcode-no_data_text" value="" class="fl-shortcode-attr regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-available-layouts"><?php echo esc_html__( 'Available Layouts', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="layout" data-fl-type="select" id="fl-form-shortcode-available-layouts" class="postform fl-shortcode-attr">
						<?php foreach ( $available_layouts as $layout ) : ?>
							<option value="<?php echo esc_attr( 'slim' === $layout ? '' : $layout ); ?>"><?php echo esc_html( $layout ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-live">
		<?php
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Live();
