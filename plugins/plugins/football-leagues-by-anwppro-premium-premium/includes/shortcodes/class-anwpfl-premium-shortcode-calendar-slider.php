<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Calendar Slider
 *
 * @since   0.11.15
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Calendar Slider.
 */
class AnWPFL_Premium_Shortcode_Calendar_Slider {

	private $shortcode = 'anwpfl-calendar-slider';

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

		// Initialize shortcode
		add_action( 'init', [ $this, 'shortcode_init' ] );

		// Add shortcode option
		add_filter( 'anwpfl/shortcode-pro/get_shortcode_options', [ $this, 'add_shortcode_option' ] );

		// Load shortcode form
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_calendar-slider', [ $this, 'load_shortcode_form' ] );
	}

	/**
	 * Load shortcode form with options.
	 * Used in Shortcode Builder and Shortcode TinyMCE tool.
	 *
	 * @since 0.11.15
	 */
	public function load_shortcode_form() {
		?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-id"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="competition_id" data-fl-type="select2" id="fl-form-shortcode-id" class="postform fl-shortcode-attr fl-shortcode-select2" multiple="multiple">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->competition->get_competition_options() as $competition_id => $competition_title ) : ?>
							<option value="<?php echo esc_attr( $competition_id ); ?>"><?php echo esc_html( $competition_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-filter_by_clubs"><?php echo esc_html__( 'Filter by Clubs', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="filter_by_clubs" data-fl-type="select2" id="fl-form-shortcode-filter_by_clubs" class="postform fl-shortcode-attr fl-shortcode-select2" multiple="multiple">
						<?php foreach ( anwp_football_leagues()->club->get_clubs_options() as $club_id => $club_title ) : ?>
							<option value="<?php echo esc_attr( $club_id ); ?>"><?php echo esc_html( $club_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-group_by_competition"><?php echo esc_html__( 'Group by competition', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="group_by_competition" data-fl-type="select" id="fl-form-shortcode-group_by_competition" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-competition_country"><?php echo esc_html__( 'Competition Country', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="competition_country" data-fl-type="select" id="fl-form-shortcode-competition_country" class="postform fl-shortcode-attr">
						<option value="" selected>- <?php echo esc_html__( 'none', 'anwp-football-leagues' ); ?> -</option>
						<option value="country"><?php echo esc_html__( 'Country', 'anwp-football-leagues' ); ?></option>
						<option value="flag"><?php echo esc_html__( 'Flag', 'anwp-football-leagues-premium' ); ?></option>
						<option value="country-flag"><?php echo esc_html__( 'Country', 'anwp-football-leagues' ); ?> + <?php echo esc_html__( 'Flag', 'anwp-football-leagues-premium' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_day_of_week"><?php echo esc_html__( 'Show day of week', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_day_of_week" data-fl-type="select" id="fl-form-shortcode-show_day_of_week" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-day_leading_zero"><?php echo esc_html__( 'Day of the month with leading zeros', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="day_leading_zero" data-fl-type="select" id="fl-form-shortcode-day_leading_zero" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-month_text"><?php echo esc_html__( 'Textual representation of a month', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="month_text" data-fl-type="select" id="fl-form-shortcode-month_text" class="postform fl-shortcode-attr">
						<option value="short" selected><?php echo esc_html__( 'Short', 'anwp-football-leagues-premium' ); ?></option>
						<option value="full"><?php echo esc_html__( 'Full', 'anwp-football-leagues-premium' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-no_data_text"><?php echo esc_html__( 'No data text', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="no_data_text" data-fl-type="text" type="text" id="fl-form-shortcode-no_data_text" value="<?php echo esc_html__( 'No games on this date', 'anwp-football-leagues-premium' ); ?>" class="fl-shortcode-attr regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-calendar_size"><?php echo esc_html__( 'Calendar Size', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="calendar_size" data-fl-type="select" id="fl-form-shortcode-calendar_size" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'Default', 'anwp-football-leagues' ); ?></option>
						<option value="small"><?php echo esc_html__( 'Small', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-competition_title"><?php echo esc_html__( 'Competition Title', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="competition_title" data-fl-type="select" id="fl-form-shortcode-competition_title" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'Competition Title', 'anwp-football-leagues' ); ?></option>
						<option value="league"><?php echo esc_html__( 'League Name', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-centered"><?php echo esc_html__( 'Centered Active Day', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="centered" data-fl-type="select" id="fl-form-shortcode-centered" class="postform fl-shortcode-attr">
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-day_width"><?php echo esc_html__( 'Day Width (px)', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<input name="day_width" data-fl-type="text" type="text" id="fl-form-shortcode-day_width" value="" class="fl-shortcode-attr regular-text">
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-calendar-slider">
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

		$data['calendar-slider'] = __( 'Calendar Slider', 'anwp-football-leagues-premium' );

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
			'competition_id'       => '',
			'filter_by_clubs'      => 0,
			'no_data_text'         => '',
			'competition_country'  => '',
			'group_by_competition' => 1,
			'show_day_of_week'     => 1,
			'day_leading_zero'     => 1,
			'month_text'           => 'short',
			'calendar_size'        => '',
			'competition_title'    => '',
			'competition_link'     => 1,
			'centered'             => 0,
			'day_width'            => '',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-calendar-slider', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Calendar_Slider();
