<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Results Matrix.
 *
 * @since   0.5.4
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Results Matrix.
 */
class AnWPFL_Premium_Shortcode_Results_Matrix {

	private $shortcode = 'anwpfl-results-matrix';

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_results-matrix', [ $this, 'load_shortcode_form' ] );
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

		$data['results-matrix'] = __( 'Results Matrix', 'anwp-football-leagues-premium' );

		return $data;
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
				<th scope="row"><label for="fl-form-shortcode-id"><?php echo esc_html__( 'Standing Table', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="standing_id" data-fl-type="select2" id="fl-form-shortcode-id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<?php foreach ( anwp_football_leagues()->standing->get_standing_options() as $standing_id => $standing_title ) : ?>
							<option value="<?php echo esc_attr( $standing_id ); ?>"><?php echo esc_html( $standing_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-results-matrix">
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
			'group_id'       => '1',
			'standing_id'    => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		// Validate shortcode attr
		$atts['competition_id'] = (int) $atts['competition_id'] ? (int) $atts['competition_id'] : '';
		$atts['group_id']       = (int) $atts['group_id'];
		$atts['standing_id']    = (int) $atts['standing_id'];

		return anwp_football_leagues()->template->shortcode_loader( 'premium-results-matrix', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Results_Matrix();
