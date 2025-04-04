<?php
/**
 * AnWP Football Leagues :: Shortcode > Standing Advanced.
 *
 * @since   0.15.3
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Standing Advanced.
 */
class AnWPFL_Premium_Shortcode_Standing_Advanced {

	private $shortcode = 'anwpfl-standing-advanced';

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_standing-advanced', [ $this, 'load_shortcode_form' ] );

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
			'title'          => '',
			'id'             => '',
			'exclude_ids'    => '',
			'layout'         => '',
			'partial'        => '',
			'bottom_link'    => '',
			'link_text'      => '',
			'wrapper_class'  => '',
			'show_notes'     => 1,
			'show_home_away' => '',
			'filtered_data'  => '',
			'matchweeks'     => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'standing', $atts );
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
		$data['standing-advanced'] = __( 'Standing Table Advanced', 'anwp-football-leagues-premium' );

		return $data;
	}

	/**
	 * Load shortcode form with options.
	 * Used in Shortcode Builder and Shortcode TinyMCE tool.
	 */
	public function load_shortcode_form() {

		$shortcode_link  = 'https://anwppro.userecho.com/knowledge-bases/2/articles/155-standing-table-shortcode';
		$shortcode_title = esc_html__( 'Shortcodes', 'anwp-football-leagues' ) . ' :: ' . esc_html__( 'Standing Table', 'anwp-football-leagues' );

		anwp_football_leagues()->helper->render_docs_template( $shortcode_link, $shortcode_title );
		?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-title"><?php echo esc_html__( 'Title', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="title" data-fl-type="text" type="text" id="fl-form-shortcode-title" value="" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-id"><?php echo esc_html__( 'Standing Table', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="id" data-fl-type="select2" id="fl-form-shortcode-id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<?php foreach ( anwp_football_leagues()->standing->get_standing_options() as $standing_id => $standing_title ) : ?>
							<option value="<?php echo esc_attr( $standing_id ); ?>"><?php echo esc_html( $standing_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-exclude_ids"><?php echo esc_html__( 'Exclude Clubs', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="exclude_ids" data-fl-type="text" type="text" id="fl-form-shortcode-exclude_ids" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="club" data-single="no">
						<span class="dashicons dashicons-search"></span>
					</button>
					<span class="anwp-option-desc"><?php echo esc_html__( 'comma-separated list of IDs', 'anwp-football-leagues' ); ?></span>
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
				<th scope="row"><label for="fl-form-shortcode-link_text"><?php echo esc_html__( 'Alternative bottom link text', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="link_text" data-fl-type="text" type="text" id="fl-form-shortcode-link_text" value="" class="fl-shortcode-attr regular-text code">
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
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_home_away"><?php echo esc_html__( 'Show Home/Away Selector', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="show_home_away" data-fl-type="select" id="fl-form-shortcode-show_home_away" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'default', 'anwp-football-leagues' ); ?></option>
						<option value="yes"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="no"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-filtered_data"><?php echo esc_html__( 'Filtered Data', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="filtered_data" data-fl-type="select" id="fl-form-shortcode-filtered_data" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'All', 'anwp-football-leagues' ); ?></option>
						<option value="home"><?php echo esc_html__( 'Home', 'anwp-football-leagues' ); ?></option>
						<option value="away"><?php echo esc_html__( 'Away', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-matchweeks"><?php echo esc_html__( 'Matchweeks', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<input name="matchweeks" data-fl-type="text" type="text" id="fl-form-shortcode-matchweeks" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'e.g.: "5" or "1-5"', 'anwp-football-leagues' ); ?></span>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-standing-advanced">
		<?php
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Standing_Advanced();
