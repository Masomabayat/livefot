<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Birthdays.
 *
 * @since   0.10.1
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Birthdays.
 */
class AnWPFL_Premium_Shortcode_Birthdays {

	private $shortcode = 'anwpfl-birthdays';

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_birthdays', [ $this, 'load_shortcode_form' ] );
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
					<select name="club_id" data-fl-type="select2" id="fl-form-shortcode-club_id" class="postform fl-shortcode-attr fl-shortcode-select2" multiple="multiple">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->club->get_clubs_options() as $club_id => $club_title ) : ?>
							<option value="<?php echo esc_attr( $club_id ); ?>"><?php echo esc_html( $club_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-type"><?php echo esc_html__( 'Type', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="type" data-fl-type="select" id="fl-form-shortcode-type" class="postform fl-shortcode-attr">
						<option value="players" selected><?php echo esc_html__( 'Players only', 'anwp-football-leagues' ); ?></option>
						<option value="staff"><?php echo esc_html__( 'Staff only', 'anwp-football-leagues' ); ?></option>
						<option value="all"><?php echo esc_html__( 'Players and Staff', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-days_before"><?php echo esc_html__( 'Days before birthday', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="days_before" data-fl-type="text" type="text" id="fl-form-shortcode-days_before" value="5" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-days_after"><?php echo esc_html__( 'Days after birthday', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="days_after" data-fl-type="text" type="text" id="fl-form-shortcode-days_after" value="3" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-group_by_date"><?php echo esc_html__( 'Group by date', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="group_by_date" data-fl-type="select" id="fl-form-shortcode-group_by_date" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-layout"><?php echo esc_html__( 'Layout', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="layout" data-fl-type="select" id="fl-form-shortcode-layout" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'Default', 'anwp-football-leagues' ); ?></option>
						<option value="slim"><?php echo esc_html__( 'Slim', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-birthdays">
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

		$data['birthdays'] = __( 'Birthdays', 'anwp-football-leagues-premium' );

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
			'club_id'       => '',
			'type'          => 'players',
			'days_before'   => 5,
			'days_after'    => 3,
			'group_by_date' => 0,
			'layout'        => 'wide',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-birthdays', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Birthdays();
