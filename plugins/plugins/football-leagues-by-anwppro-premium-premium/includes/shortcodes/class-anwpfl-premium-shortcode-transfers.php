<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Transfers.
 *
 * @since   0.9.4
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Transfers.
 */
class AnWPFL_Premium_Shortcode_Transfers {

	private $shortcode = 'anwpfl-transfers';

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_transfers', [ $this, 'load_shortcode_form' ] );
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
				<th scope="row">
					<label for="fl-form-shortcode-competition_id"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?></label></th>
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
				<th scope="row"><label for="fl-form-shortcode-window"><?php echo esc_html__( 'Window', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="window" data-fl-type="select" id="fl-form-shortcode-window" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( '- not selected -', 'anwp-football-leagues' ); ?></option>
						<option value="pre"><?php echo esc_html__( 'Pre-season window', 'anwp-football-leagues-premium' ); ?></option>
						<option value="mid"><?php echo esc_html__( 'Mid-season window', 'anwp-football-leagues-premium' ); ?></option>
						<option value="none"><?php echo esc_html__( 'Not set', 'anwp-football-leagues-premium' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-player_id"><?php echo esc_html__( 'Player ID', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="player_id" data-fl-type="text" type="text" id="fl-form-shortcode-player_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="player" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-date_from"><?php echo esc_html__( 'Date From', 'anwp-football-leagues' ); ?> - YYYY-MM-DD</label></th>
				<td>
					<input name="date_from" data-fl-type="text" type="text" id="fl-form-shortcode-date_from" value="" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-date_to"><?php echo esc_html__( 'Date To', 'anwp-football-leagues' ); ?> - YYYY-MM-DD</label></th>
				<td>
					<input name="date_to" data-fl-type="text" type="text" id="fl-form-shortcode-date_to" value="" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-layout"><?php echo esc_html__( 'Layout', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="layout" data-fl-type="select" id="fl-form-shortcode-layout" class="postform fl-shortcode-attr">
						<option value="club" selected><?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></option>
						<option value="competition"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?></option>
						<option value="competition-compact"><?php echo esc_html__( 'Competition (compact)', 'anwp-football-leagues-premium' ); ?></option>
						<option value="player"><?php echo esc_html__( 'Player', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-link"><?php echo esc_html__( 'Show link to profile', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="link" data-fl-type="select" id="fl-form-shortcode-link" class="postform fl-shortcode-attr">
						<option value="0" selected><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1"><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
<!--			<tr>-->
<!--				<th scope="row"><label for="fl-form-shortcode-club_column">--><?php //echo esc_html__( 'Club Column', 'anwp-football-leagues-premium' ); ?><!--</label>-->
<!--				</th>-->
<!--				<td>-->
<!--					<select name="club_column" data-fl-type="select" id="fl-form-shortcode-club_column" class="postform fl-shortcode-attr">-->
<!--						<option value="title" selected>--><?php //echo esc_html__( 'Club Title', 'anwp-football-leagues' ); ?><!--</option>-->
<!--						<option value="logo" >--><?php //echo esc_html__( 'Logo', 'anwp-football-leagues' ); ?><!--</option>-->
<!--						<option value="abbr">--><?php //echo esc_html__( 'Abbreviation', 'anwp-football-leagues' ); ?><!--</option>-->
<!--						<option value="logo_title">--><?php //echo esc_html__( 'Logo', 'anwp-football-leagues' ); ?><!--+--><?php //echo esc_html__( 'Club Title', 'anwp-football-leagues' ); ?><!--</option>-->
<!--						<option value="logo_abbr">--><?php //echo esc_html__( 'Logo', 'anwp-football-leagues' ); ?><!--+--><?php //echo esc_html__( 'Abbreviation', 'anwp-football-leagues' ); ?><!--</option>-->
<!--					</select>-->
<!--				</td>-->
<!--			</tr>-->
			<tr>
				<th scope="row"><label for="fl-form-shortcode-type"><?php echo esc_html__( 'Type', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="type" data-fl-type="select" id="fl-form-shortcode-type" class="postform fl-shortcode-attr">
						<option value="" selected><?php echo esc_html__( 'All', 'anwp-football-leagues' ); ?></option>
						<option value="out"><?php echo esc_html__( 'Club Out', 'anwp-football-leagues-premium' ); ?></option>
						<option value="in"><?php echo esc_html__( 'Club In', 'anwp-football-leagues-premium' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-limit"><?php echo esc_html__( 'Matches Limit (0 - for all)', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="limit" data-fl-type="text" type="text" id="fl-form-shortcode-limit" value="0" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-order"><?php echo esc_html__( 'Sort By Date', 'anwp-football-leagues' ); ?></label>
				</th>
				<td>
					<select name="order" data-fl-type="select" id="fl-form-shortcode-order" class="postform fl-shortcode-attr">
						<option value="asc" selected><?php echo esc_html__( 'Ascending', 'anwp-football-leagues' ); ?></option>
						<option value="desc"><?php echo esc_html__( 'Descending', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-transfers">
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

		$data['transfers'] = __( 'Transfers', 'anwp-football-leagues-premium' );

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
			'club_id'        => '',
			'season_id'      => '',
			'type'           => '',
			'limit'          => 0,
			'link'           => 0,
			'order'          => 'ASC',
			'window'         => '',
			'player_id'      => '',
			'date_from'      => '',
			'date_to'        => '',
			'competition_id' => '',
			'layout'         => '',
			'club_column'    => 'logo',
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		// Fix before v0.12.6
		$atts['club_column'] = empty( $atts['club_column'] ) ? 'logo' : $atts['club_column'];

		return anwp_football_leagues()->template->shortcode_loader( 'premium-transfers', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Transfers();
