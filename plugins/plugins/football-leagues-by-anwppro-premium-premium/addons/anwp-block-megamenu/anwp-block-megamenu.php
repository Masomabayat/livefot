<?php
/**
 * Plugin Name: AnWP Block Menu
 * Description: Simple Block Menu for every theme
 * Version:     1.0.0
 * Author:      Andrei Strekozov <anwp.pro>
 * Author URI:  https://anwp.pro
 * License:     GPLv2+
 * Requires PHP: 5.4
 * Text Domain: anwp-menu
 * Domain Path: /languages
 *
 * @link    https://anwp.pro
 *
 * @package AnWP_Menu
 * @version 1.0.0
 *
 * Built using generator-plugin-wp (https://github.com/WebDevStudios/generator-plugin-wp)
 */

/**
 * Copyright (c) 2023 Andrei Strekozov <anwp.pro> (email : anwp.pro@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'AnWP_Menu', false ) ) {

	/**
	 * Autoload files with classes when needed.
	 *
	 * @since  0.1.0
	 *
	 * @param string $class_name Name of the class being requested.
	 */
	function anwp_megamenu_autoload_classes( $class_name ) {

		// If our class doesn't have our prefix, don't load it.
		if ( 0 !== strpos( $class_name, 'AnWP_MM_' ) ) {
			return;
		}

		// Set up our filename.
		$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'AnWP_MM_' ) ) ) );

		// Include our file.
		AnWP_Menu::include_file( 'includes/class-anwp-mm-' . $filename );
	}

	spl_autoload_register( 'anwp_megamenu_autoload_classes' );

	/**
	 * Main initiation class.
	 *
	 * @property-read AnWP_MM_Options $options
	 *
	 * @since  0.1.0
	 */
	final class AnWP_Menu {

		/**
		 * Current version.
		 *
		 * @var    string
		 * @since  0.1.0
		 */
		const VERSION = '1.2.0';

		/**
		 * URL of plugin directory.
		 *
		 * @var    string
		 * @since  0.1.0
		 */
		protected $url = '';

		/**
		 * Path of plugin directory.
		 *
		 * @var    string
		 * @since  0.1.0
		 */
		protected $path = '';

		/**
		 * Plugin basename.
		 *
		 * @var    string
		 * @since  0.1.0
		 */
		protected $basename = '';

		/**
		 * Instance of AnWP_MM_Options
		 *
		 * @since 0.1.0
		 * @var AnWP_MM_Options
		 */
		protected $options;

		/**
		 * Singleton instance of plugin.
		 *
		 * @var    AnWP_Menu
		 * @since  0.1.0
		 */
		protected static $single_instance = null;

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @since   0.1.0
		 * @return  AnWP_Menu A single instance of this class.
		 */
		public static function get_instance() {

			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}

			return self::$single_instance;
		}

		/**
		 * Sets up our plugin.
		 *
		 * @since  0.1.0
		 */
		protected function __construct() {
			$this->basename = plugin_basename( __FILE__ );
			$this->url      = plugin_dir_url( __FILE__ );
			$this->path     = plugin_dir_path( __FILE__ );
		}

		/**
		 * Add hooks and filters.
		 * Priority needs to be
		 * < 10 for CPT_Core,
		 * < 5 for Taxonomy_Core,
		 * and 0 for Widgets because widgets_init runs at init priority 1.
		 *
		 * @since  0.1.0
		 */
		public function hooks() {
			add_action( 'init', [ $this, 'init' ] );

			add_action( 'init', [ $this, 'register_post_type' ] );
			add_action( 'init', [ $this, 'register_meta' ] );

			add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_assets' ] );

			add_action( 'manage_anwp_menu_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
			add_filter( 'manage_edit-anwp_menu_columns', [ $this, 'columns' ] );

			add_action( 'wp_footer', [ $this, 'render_megamenu_content' ] );

			add_action( 'wp_enqueue_scripts', [ $this, 'public_enqueue_scripts' ] );

			add_action( 'nav_menu_css_class', [ $this, 'menu_item_class' ], 10, 4 );
			add_action( 'nav_menu_link_attributes', [ $this, 'menu_item_atts' ], 10, 4 );

			add_filter( 'anwpfl/admin/plugin_pages', [ $this, 'add_plugin_pages' ] );
			add_filter( 'sports-leagues/admin/plugin_pages', [ $this, 'add_plugin_pages' ] );
		}

		public function add_plugin_pages( $plugin_pages ) {

			$plugin_pages[] = 'anwp_menu_page_anwp-megamenu';

			return $plugin_pages;
		}

		/**
		 * Add special attribute to the saved menus
		 *
		 * @param $atts array
		 * @param $menu_item WP_Post
		 * @param $args
		 * @param $depth
		 *
		 * @return array
		 */
		public function menu_item_atts( $atts, $menu_item, $args, $depth ) {
			if ( empty( $menu_item->ID ) || empty( $this->get_saved_menus_ids() ) || $depth > 0 ) {
				return $atts;
			}

			if ( 'primary' === $args->theme_location && in_array( $menu_item->ID, $this->get_saved_menus_ids(), true ) ) {
				$atts['data-anwp-mm-id'] = absint( $menu_item->ID );
			}

			return $atts;
		}

		/**
		 * Add special class to the saved menus
		 *
		 * @param $classes array
		 * @param $item WP_Post
		 * @param $args
		 * @param $depth
		 *
		 * @return array
		 */
		public function menu_item_class( $classes, $item, $args, $depth ) {

			if ( empty( $item->ID ) || empty( $this->get_saved_menus_ids() ) || $depth > 0 ) {
				return $classes;
			}

			if ( 'primary' === $args->theme_location && in_array( $item->ID, $this->get_saved_menus_ids(), true ) ) {
				$classes[] = 'anwp-megamenu-menu-item';
			}

			return $classes;
		}


		/**
		 * Load admin scripts and styles
		 *
		 * @since 0.1.0
		 */
		public function public_enqueue_scripts() {
			wp_enqueue_script( 'anwp-megamenu-scripts', self::url( 'public/js/anwp-menu-public.min.js' ), [], self::VERSION, true );

			wp_add_inline_script(
				'anwp-megamenu-scripts',
				'window.AnWPMegaMenu = ' . wp_json_encode(
					[
						'container_class'    => AnWP_MM_Options::get_value( 'selector_after' ) ?: '',
						'interactive_border' => absint( AnWP_MM_Options::get_value( 'interactive_border' ) ) ?: 25,
					]
				),
				'before'
			);
		}

		/**
		 * Registers admin columns to display.
		 *
		 * @since  0.1.0
		 *
		 * @param  array $columns Array of registered column names/labels.
		 * @return array          Modified array.
		 */
		public function columns( $columns ) {

			// Add new columns
			$new_columns = [
				'anwp_menu' => esc_html__( 'Menu Title', 'anwp-menu' ),
			];

			return array_merge( $columns, $new_columns );
		}

		/**
		 * Handles admin column display.
		 *
		 * @param array   $column  Column currently being rendered.
		 * @param integer $post_id ID of post to display column for.
		 *
		 * @since  0.1.0
		 */
		public function columns_display( $column, $post_id ) {

			switch ( $column ) {
				case 'anwp_menu':

					$menu_id = get_post_meta( $post_id, '_anwp_megamenu_id', true );

					if ( ! empty( $menu_id ) ) {
						echo get_the_title( $menu_id );
					}
					break;
			}
		}

		/**
		 * Render Mega Menu Content
		 *
		 * @return void
		 */
		public function render_megamenu_content() {
			$all_menus = get_posts(
				[
					'numberposts' => 0,
					'post_type'   => 'anwp_menu',
				]
			);

			if ( empty( $all_menus ) ) {
				return;
			}
			?>
			<style>
                [data-tippy-root] { max-width: 100vw !important; }
				.anwp-megamenu {
					height: 0 !important;
					overflow: visible;
					position: relative;
					<?php echo AnWP_MM_Options::get_value( 'top_offset' ) ? 'top:' . intval( AnWP_MM_Options::get_value( 'top_offset' ) ) . 'px' : ''; ?>
				}
				.anwp-megamenu > .anwp-megamenu__content {
						display: none;
				}
				.anwp-megamenu-wrapper__popper {
						position: relative !important;
						inset: unset !important;
						margin: initial !important;
						transform: none !important;
				}

                <?php if ( 'hide' !== AnWP_MM_Options::get_value( 'show_dropdown_icon' ) ) : ?>
	                .anwp-megamenu-menu-item > a:after {
	                    border-bottom: 0;
	                    border-left: 0.3em solid transparent;
	                    border-right: 0.3em solid transparent;
	                    border-top: 0.3em solid;
	                    bottom: 0;
	                    content: "";
	                    display: inline-block;
	                    position: relative;
	                    transition: transform 0.2s ease-in-out;
	                    vertical-align: 0.255em;

	                <?php if ( is_rtl() ) : ?>
	                    right: 0;
	                    margin-right: 0.255em;
	                <?php else : ?>
	                    left: 0;
	                    margin-left: 0.255em;
	                <?php endif; ?>
	                }

	                .anwp-megamenu-menu-item--active > a:after {
	                    transform: rotate(180deg);
	                }
                <?php endif; ?>
			</style>
			<div id="anwp-megamenu-wrapper" class="anwp-megamenu" data-debug="<?php echo AnWP_MM_Options::get_value( 'debug_mode' ); ?>">
				<?php
				$container_class = AnWP_MM_Options::get_value( 'container_class' ) ? : 'site-container ct-container grid-container ast-container';

				foreach ( $all_menus as $single_menu ) :
					$menu_id = get_post_meta( $single_menu->ID, '_anwp_megamenu_id', true );

					if ( empty( $menu_id ) ) {
						continue;
					}
					?>
					<div class="<?php echo esc_attr( $container_class ); ?> anwp-megamenu__content"
					     data-menu-id="<?php echo esc_attr( $menu_id ); ?>">
						<?php echo do_blocks( do_shortcode( $single_menu->post_content ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Register Custom Post Type
		 *
		 * @since 0.1.0
		 */
		public function register_post_type() {

			// Register this CPT.
			$labels = [
				'name'          => _x( 'AnWP Block Menu', 'Post type general name', 'anwp-menu' ),
				'singular_name' => _x( 'Block Menu', 'Post type singular name', 'anwp-menu' ),
				'edit_item'     => __( 'Edit Block Menu', 'anwp-menu' ),
				'view_item'     => __( 'View Block Menu', 'anwp-menu' ),
				'all_items'     => __( 'Block Menus', 'anwp-menu' ),
			];

			$args = [
				'labels'              => $labels,
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'query_var'           => false,
				'show_in_rest'        => true,
				'capability_type'     => 'post',
				'capabilities'        => [
					'edit_post'         => 'manage_options',
					'read_post'         => 'manage_options',
					'delete_post'       => 'manage_options',
					'edit_posts'        => 'manage_options',
					'edit_others_posts' => 'manage_options',
					'delete_posts'      => 'manage_options',
					'publish_posts'     => 'manage_options',
				],
				'menu_icon'           => 'dashicons-menu',
				'has_archive'         => false,
				'hierarchical'        => false,
				'supports'            => [ 'title', 'editor', 'custom-fields' ],
			];

			register_post_type( 'anwp_menu', $args );
		}

		/**
		 * Register meta field
		 *
		 * @return void
		 */
		public function register_meta() {
			register_meta(
				'post',
				'_anwp_megamenu_id',
				[
					'object_subtype'    => 'anwp_menu',
					'single'            => true,
					'type'              => 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				]
			);
		}

		/**
		 * Enqueue block scripts
		 *
		 * @return void
		 */
		public function enqueue_block_assets() {
			if ( ! empty( get_current_screen() ) && 'anwp_menu' === get_current_screen()->id ) {
				$asset_file = self::include_file( 'build/sidebar/index.asset' );

				wp_enqueue_script( 'anwp-menu-block-scripts', self::url( 'build/sidebar/index.js' ), $asset_file['dependencies'], $asset_file['version'], false );
				wp_localize_script(
					'anwp-menu-block-scripts',
					'AnWPMenu',
					[
						'menus' => $this->get_primary_menu_items(),
					]
				);
			}
		}

		/**
		 * Get an array of top menu items from primary location
		 *
		 * @return array
		 */
		public function get_primary_menu_items() {

			$primary_location = apply_filters( 'anwp-menu/menu/primary_location', 'primary' );
			$all_locations    = get_nav_menu_locations();

			if ( empty( $all_locations[ $primary_location ] ) ) {
				return [];
			}

			$menu            = wp_get_nav_menu_object( $all_locations[ $primary_location ] );
			$menu_items      = wp_get_nav_menu_items( $menu->term_id );
			$saved_menus_ids = $this->get_saved_menus_ids();
			$main_menu_items = [];

			foreach ( $menu_items as $mm ) {
				if ( 0 === intval( $mm->menu_item_parent ) ) {
					$main_menu_items[] = [
						'label'    => $mm->title,
						'value'    => $mm->ID,
						'disabled' => in_array( $mm->ID, $saved_menus_ids, true ),
					];
				}
			}

			return $main_menu_items;
		}

		/**
		 * Get IDs of the saved menus
		 *
		 * @return array|null
		 */
		public function get_saved_menus_ids() {

			static $saved_menus_ids = null;

			if ( null === $saved_menus_ids ) {
				// Get already saved menus
				$saved_menus = get_posts(
					[
						'numberposts'  => 0,
						'post_type'    => 'anwp_menu',
						'post__not_in' => [ get_the_ID() ]
					]
				);

				$saved_menus_ids = [];

				foreach ( $saved_menus as $menu_post ) {
					$saved_menus_ids[] = absint( get_post_meta( $menu_post->ID, '_anwp_megamenu_id', true ) );
				}
			}

			return $saved_menus_ids;
		}

		/**
		 * Init hooks
		 *
		 * @since  0.1.0
		 */
		public function init() {

			// Load translated strings for plugin.
			load_plugin_textdomain( 'anwp-menu', false, dirname( $this->basename ) . '/languages/' );

			// Register CPT
			$this->plugin_classes();
		}

		/**
		 * Attach other plugin classes to the base plugin class.
		 *
		 * @since  0.1.0
		 */
		public function plugin_classes() {
			$this->options = new AnWP_MM_Options( $this );
		}

		/**
		 * Magic getter for our object.
		 *
		 * @since  0.1.0
		 *
		 * @param  string $field Field to get.
		 * @throws Exception     Throws an exception if the field is invalid.
		 * @return mixed         Value of the field.
		 */
		public function __get( $field ) {
			switch ( $field ) {
				case 'version':
					return self::VERSION;
				case 'basename':
				case 'url':
				case 'path':
				case 'options':
					return $this->$field;
				default:
					throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
			}
		}

		/**
		 * Include a file from the includes directory.
		 *
		 * @since  0.1.0
		 *
		 * @param  string $filename Name of the file to be included.
		 * @return boolean          Result of include call.
		 */
		public static function include_file( $filename ) {
			$file = self::dir( $filename . '.php' );
			if ( file_exists( $file ) ) {
				return include_once $file;
			}
			return false;
		}

		/**
		 * This plugin's directory.
		 *
		 * @since  0.1.0
		 *
		 * @param  string $path (optional) appended path.
		 * @return string       Directory and path.
		 */
		public static function dir( $path = '' ) {
			static $dir;
			$dir = $dir ? : trailingslashit( dirname( __FILE__ ) );
			return $dir . $path;
		}

		/**
		 * This plugin's url.
		 *
		 * @since  0.1.0
		 *
		 * @param  string $path (optional) appended path.
		 * @return string       URL and path.
		 */
		public static function url( $path = '' ) {
			static $url;
			$url = $url ? : trailingslashit( plugin_dir_url( __FILE__ ) );
			return $url . $path;
		}
	}

	/**
	 * Grab the AnWP_FLMixer object and return it.
	 * Wrapper for AnWP_FLMixer::get_instance().
	 *
	 * @since  0.1.0
	 * @return AnWP_Menu Singleton instance of plugin class.
	 */
	function anwp_menu() {
		return AnWP_Menu::get_instance();
	}

	// Kick it off.
	add_action( 'plugins_loaded', [ anwp_menu(), 'hooks' ] );
}
