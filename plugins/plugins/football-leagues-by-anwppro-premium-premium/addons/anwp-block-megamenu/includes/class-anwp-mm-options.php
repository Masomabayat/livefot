<?php
/**
 * AnWP MegaMenu Options.
 *
 * @since   0.1.0
 * @package AnWP_Menu
 */

class AnWP_MM_Options {
	/**
	 * Parent plugin class.
	 *
	 * @var    AnWP_Menu
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Option key, and option page slug.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected static $key = 'anwp_mm_options';

	/**
	 * Options page metabox ID.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected static $metabox_id = 'anwp_mm_options_metabox';

	/**
	 * Options Page title.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $title = '';

	/**
	 * Options Page hook.
	 *
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor.
	 *
	 * @param  AnWP_Menu $plugin Main plugin object.
	 *
	 *@since  0.1.0
	 *
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		// Set our title.
		$this->title = esc_html__( 'AnWP Mega Menu :: Settings', 'anwp-menu' );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {
		add_action( 'cmb2_admin_init', [ $this, 'add_options_page_metabox' ] );
	}

	/**
	 * Add custom fields to the options page.
	 *
	 * @since  0.1.0
	 */
	public function add_options_page_metabox() {

		// Add our CMB2 metabox.
		$cmb = new_cmb2_box(
			[
				'id'           => self::$metabox_id,
				'title'        => $this->title,
				'object_types' => [ 'options-page' ],
				'classes'      => 'anwp-b-wrap anwp-settings',
				'option_key'   => self::$key,
				'menu_title'   => esc_html__( 'Settings', 'anwp-menu' ),
				'parent_slug'  => 'edit.php?post_type=anwp_menu',
				'capability'   => 'manage_options',
			]
		);

		$cmb->add_field(
			[
				'type'    => 'hidden',
				'id'      => 'anwp_mm_current_page_hash',
				'default' => '',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Show "triangle down" icon on Menu with dropdown', 'anwp-menu' ),
				'id'      => 'show_dropdown_icon',
				'type'    => 'select',
				'default' => 'show',
				'options' => [
					'show' => esc_html__( 'show', 'anwp-menu' ),
					'hide' => esc_html__( 'hide', 'anwp-menu' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Selector to insert after', 'anwp-menu' ),
				'id'      => 'selector_after',
				'type'    => 'text',
				'default' => '',
				'desc'    => esc_html__( 'Set the proper selector when megamenu is not positioning correctly. Default: "#masthead"', 'anwp-menu' ),
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Interactive Border', 'anwp-menu' ),
				'id'      => 'interactive_border',
				'type'    => 'text',
				'default' => '',
				'desc'    => esc_html__( 'Determines the size of the invisible border around the dropdown menu that will prevent it from hiding if the cursor left it. Default: 25', 'anwp-menu' ),
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( "Theme's container class", 'anwp-menu' ),
				'id'      => 'container_class',
				'type'    => 'text',
				'default' => '',
				'desc'    => esc_html__( 'Set the proper theme container class when megamenu width is not correct.', 'anwp-menu' )
				             . '<br>' .
				             esc_html__( 'Default class value: "site-container ct-container grid-container ast-container"', 'anwp-menu' ),
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Top offset (in px.)', 'anwp-menu' ),
				'id'      => 'top_offset',
				'type'    => 'text',
				'default' => '',
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'Debug Mode', 'anwp-menu' ),
				'id'      => 'debug_mode',
				'type'    => 'select',
				'default' => 'no',
				'desc'    => esc_html__( 'Open dropdown menu for a long time to check its styles', 'anwp-menu' ),
				'options' => [
					'yes' => esc_html__( 'yes', 'anwp-menu' ),
					'no'  => esc_html__( 'no', 'anwp-menu' ),
				],
			]
		);
	}

	/**
	 * Wrapper function around cmb2_get_option.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $key     Options array key
	 * @param  mixed  $default Optional default value
	 * @return mixed           Option value
	 */
	public static function get_value( $key = '', $default = false ) {
		if ( function_exists( 'cmb2_get_option' ) ) {

			// Use cmb2_get_option as it passes through some key filters.
			return cmb2_get_option( self::$key, $key, $default );
		}

		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( self::$key, $default );

		$val = $default;

		if ( 'all' === $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}

		return $val;
	}
}
