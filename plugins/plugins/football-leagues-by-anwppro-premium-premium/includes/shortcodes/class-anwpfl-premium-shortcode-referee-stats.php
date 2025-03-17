<?php
/**
 * AnWP Football Leagues Premium :: Shortcode > Referee Stat
 *
 * @since   0.14.7
 * @package AnWP_Football_Leagues_Premium
 */

/**
 * AnWP Football Leagues Premium :: Shortcode > Referee Stat.
 */
class AnWPFL_Premium_Shortcode_Referee_Stats {

	private $shortcode = 'anwpfl-referee-stats';

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

		$data['referee-stats'] = __( 'Referee Statistics', 'anwp-football-leagues-premium' );

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
		add_action( 'anwpfl/shortcode-pro/get_shortcode_form_referee-stats', [ $this, 'load_shortcode_form' ] );
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
				<th scope="row">
					<label for="fl-form-shortcode-referee_id"><?php echo esc_html__( 'Referee', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<input name="referee_id" data-fl-type="text" type="text" id="fl-form-shortcode-referee_id" value="" class="fl-shortcode-attr code">
					<button type="button" class="button anwp-fl-selector" data-context="referee" data-single="yes">
						<span class="dashicons dashicons-search"></span>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-stats"><?php echo esc_html__( 'Stat Value', 'anwp-football-leagues-premium' ); ?></label></th>
				<td>
					<select name="stats" data-fl-type="select2" id="fl-form-shortcode-stats" class="postform fl-shortcode-attr fl-shortcode-select2" multiple="multiple">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<option value="card_y"><?php echo esc_html__( 'Yellow Cards', 'anwp-football-leagues-premium' ); ?></option>
						<option value="card_y_h"><?php echo esc_html__( 'Yellow Cards (Home)', 'anwp-football-leagues-premium' ); ?></option>
						<option value="card_y_a"><?php echo esc_html__( 'Yellow Cards (Away)', 'anwp-football-leagues-premium' ); ?></option>
						<option value="card_r"><?php echo esc_html__( 'Red Cards', 'anwp-football-leagues-premium' ); ?></option>
						<option value="card_r_h"><?php echo esc_html__( 'Red Cards (Home)', 'anwp-football-leagues-premium' ); ?></option>
						<option value="card_r_a"><?php echo esc_html__( 'Red Cards (Away)', 'anwp-football-leagues-premium' ); ?></option>
						<option value="foul"><?php echo esc_html__( 'Fouls', 'anwp-football-leagues-premium' ); ?></option>
						<option value="foul_h"><?php echo esc_html__( 'Fouls (Home)', 'anwp-football-leagues-premium' ); ?></option>
						<option value="foul_a"><?php echo esc_html__( 'Fouls (Away)', 'anwp-football-leagues-premium' ); ?></option>
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
				<th scope="row"><label for="fl-form-shortcode-league_id"><?php echo esc_html__( 'League', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="league_id" data-fl-type="select2" id="fl-form-shortcode-league_id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->league->get_league_options() as $league_id => $league_title ) : ?>
							<option value="<?php echo esc_attr( $league_id ); ?>"><?php echo esc_html( $league_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-competition_id"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?></label></th>
				<td>
					<select name="competition_id" data-fl-type="select2" id="fl-form-shortcode-competition_id" class="postform fl-shortcode-attr fl-shortcode-select2">
						<option value="">- <?php echo esc_html__( 'select', 'anwp-football-leagues' ); ?> -</option>
						<?php foreach ( anwp_football_leagues()->competition->get_competition_options() as $competition_id => $competition_title ) : ?>
							<option value="<?php echo esc_attr( $competition_id ); ?>"><?php echo esc_html( $competition_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_secondary"><?php echo esc_html__( 'Include All Stages (in Multistage competition)', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_secondary" data-fl-type="select" id="fl-form-shortcode-multistage" class="postform fl-shortcode-attr">
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="fl-form-shortcode-block_width"><?php echo esc_html__( 'Block Width (px)', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<input name="block_width" data-fl-type="text" type="text" id="fl-form-shortcode-block_width" value="" class="fl-shortcode-attr regular-text code">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-per_game"><?php echo esc_html__( 'Show Per Game value', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="per_game" data-fl-type="select" id="fl-form-shortcode-per_game" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fl-form-shortcode-show_games"><?php echo esc_html__( 'Show Number of Games', 'anwp-football-leagues-premium' ); ?></label>
				</th>
				<td>
					<select name="show_games" data-fl-type="select" id="fl-form-shortcode-show_games" class="postform fl-shortcode-attr">
						<option value="1" selected><?php echo esc_html__( 'Yes', 'anwp-football-leagues' ); ?></option>
						<option value="0"><?php echo esc_html__( 'No', 'anwp-football-leagues' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" class="fl-shortcode-name" name="fl-slug" value="anwpfl-referee-stats">
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
			'referee_id'     => '',
			'competition_id' => '',
			'show_secondary' => 1,
			'season_id'      => '',
			'league_id'      => '',
			'per_game'       => 1,
			'block_width'    => '',
			'stats'          => '',
			'class'          => '',
			'header'         => '',
			'layout'         => '',
			'notes'          => '',
			'show_games'     => 1,
		];

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, $this->shortcode );

		return anwp_football_leagues()->template->shortcode_loader( 'premium-referee-stats', $atts );
	}
}

// Bump
new AnWPFL_Premium_Shortcode_Referee_Stats();
