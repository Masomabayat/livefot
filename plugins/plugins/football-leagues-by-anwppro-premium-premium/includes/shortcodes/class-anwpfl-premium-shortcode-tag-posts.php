<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Tag Posts.
 *
 * @since   0.10.5
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Tag Posts.
 */
class AnWPFL_Premium_Shortcode_Tag_Posts {

	private $shortcode = 'anwpfl-tag-posts';

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_tag-posts', [ $this, 'load_shortcode_form' ] );
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
				<th scope="row"><label for="fl-form-shortcode-id"><?php echo esc_html__( 'ID', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<input name="id" data-fl-type="text" type="text" id="fl-form-shortcode-id" value="" class="fl-shortcode-attr regular-text code">
					<span class="anwp-option-desc"><?php echo esc_html__( 'Club ID, Match ID, Competition ID, Player ID, Staff ID or Referee ID', 'anwp-football-leagues-premium' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-limit"><?php echo esc_html__( 'Posts Limit (0 - for all)', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<input name="limit" data-fl-type="text" type="text" id="fl-form-shortcode-limit" value="10" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_tags"><?php echo esc_html__( 'Show Tags', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_tags" data-fl-type="select" id="fl-form-shortcode-show_tags" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-cols"><?php echo esc_html__( 'Number of Columns', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="cols" data-fl-type="select" id="fl-form-shortcode-cols" class="postform fl-shortcode-attr">
						<option value="1">1</option>
						<option value="2" selected>2</option>
						<option value="3">3</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-layout"><?php echo esc_html__( 'Layout', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="layout" data-fl-type="select" id="fl-form-shortcode-layout" class="postform fl-shortcode-attr">
						<option value="grid" selected><?php echo esc_html__( 'Grid', 'anwp-football-leagues-premium' ); ?></option>
						<option value="simple"><?php echo esc_html__( 'Simple', 'anwp-football-leagues-premium' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-tag-posts">
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

		$data['tag-posts'] = __( 'Tag Posts', 'anwp-football-leagues-premium' );

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
			'id'        => '',
			'limit'     => 10,
			'show_tags' => 1,
			'cols'      => 2,
			'layout'    => 'grid',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-tag-posts', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Tag_Posts();
