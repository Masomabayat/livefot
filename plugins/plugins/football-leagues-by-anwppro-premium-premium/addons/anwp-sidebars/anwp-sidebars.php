<?php
/**
 * Plugin Name: AnWP Sidebars
 * Description: Create Custom Sidebars
 * Version:     1.0.0
 * Author:      Andrei Strekozov <anwp.pro>
 * Author URI:  https://anwp.pro
 * License:     GPLv2+
 * Requires PHP: 5.4
 * Text Domain: anwp-sidebars
 * Domain Path: /languages
 *
 * @link    https://anwp.pro
 *
 * @package AnWP_Sidebars
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

if ( ! class_exists( 'AnWP_Sidebars' ) ) :
	/**
	 * AnWP Sidebars class.
	 *
	 * @since 0.1.0
	 */
	class AnWP_Sidebars {

		/**
		 * List of AnWP sidebars.
		 *
		 * @var    array
		 * @since  0.1.0
		 */
		protected static $sidebars = null;

		/**
		 * Singleton instance of plugin.
		 *
		 * @var    AnWP_Sidebars
		 * @since  0.1.0
		 */
		protected static $single_instance = null;

		/**
		 * Constructor.
		 *
		 * @since  0.1.0
		 */
		public function __construct() {

			$this->hooks();
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return  AnWP_Sidebars A single instance of this class.
		 * @since   0.1.0
		 */
		public static function get_instance() {

			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}

			return self::$single_instance;
		}

		/**
		 * Initiate our hooks.
		 *
		 * @since 0.1.0
		 */
		public function hooks() {

			// Register on init
			add_action( 'init', [ $this, 'register_taxonomy' ] );

			add_filter( 'manage_edit-anwp_sidebar_columns', [ $this, 'manage_columns' ] );
			add_action( 'anwp_sidebar_edit_form', [ $this, 'add_tax_scripts' ] );
			add_action( 'anwp_sidebar_add_form', [ $this, 'add_tax_scripts' ] );

			add_action( 'init', [ $this, 'register_sidebars' ], 15 );

			add_action( 'admin_menu', [ $this, 'register_menu' ] );

			add_filter( 'parent_file', [ $this, 'menu_fix_parent' ] );
		}

		/**
		 * Register taxonomy for Blocks.
		 *
		 * @since 0.1.0
		 */
		public function register_taxonomy() {

			$labels = [
				'name'              => esc_html_x( 'Sidebars', 'Taxonomy General Name', 'anwp-sidebars' ),
				'singular_name'     => esc_html_x( 'Sidebar', 'Taxonomy Singular Name', 'anwp-sidebars' ),
				'menu_name'         => esc_html_x( 'Sidebars', 'Admin menu name', 'anwp-sidebars' ),
				'search_items'      => esc_html__( 'Search Sidebars', 'anwp-sidebars' ),
				'all_items'         => esc_html__( 'All Sidebars', 'anwp-sidebars' ),
				'parent_item'       => esc_html__( 'Parent Sidebar', 'anwp-sidebars' ),
				'parent_item_colon' => esc_html__( 'Parent Sidebar:', 'anwp-sidebars' ),
				'edit_item'         => esc_html__( 'Edit Sidebar', 'anwp-sidebars' ),
				'update_item'       => esc_html__( 'Update Sidebar', 'anwp-sidebars' ),
				'add_new_item'      => esc_html__( 'Add New Sidebar', 'anwp-sidebars' ),
				'new_item_name'     => esc_html__( 'New Sidebar Name', 'anwp-sidebars' ),
			];

			$args = [
				'labels'            => $labels,
				'hierarchical'      => false,
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => false,
				'show_in_nav_menus' => false,
				'show_in_menu'      => false,
				'show_tagcloud'     => false,
				'show_in_rest'      => false,
				'capabilities'      => [ 'manage_options' ],
				'query_var'         => false,
				'rewrite'           => false,
			];

			register_taxonomy( 'anwp_sidebar', [], $args );
		}

		/**
		 * Adds a submenu page under AnWP Theme menu.
		 *
		 * @since 0.1.0
		 */
		public function register_menu() {
			add_submenu_page(
				'anwp-football-leagues',
				esc_html__( 'Sidebars', 'anwp-sidebars' ),
				'<span class="anwpfl-pro-menu-text">FL+</span>' . esc_html__( 'AnWP Sidebars', 'anwp-sidebars' ),
				'manage_options',
				'edit-tags.php?taxonomy=anwp_sidebar'
			);
		}

		/**
		 * Fix the parent file of an admin menu sub-menu item.
		 *
		 * @param $parent_file
		 *
		 * @return string
		 * @since 0.1.0
		 */
		public function menu_fix_parent( $parent_file ) {

			global $current_screen;

			if ( 'edit-anwp_sidebar' === $current_screen->id ) {
				$parent_file = 'anwp-football-leagues';
			}

			return $parent_file;
		}

		/**
		 * Manage tax columns in admin list.
		 *
		 * @param $columns
		 *
		 * @return mixed
		 * @since 0.1.0
		 */
		public function manage_columns( $columns ) {

			unset( $columns['posts'] );
			unset( $columns['slug'] );

			return $columns;
		}

		/**
		 * Load scripts for minor tax page customization.
		 *
		 * @return void
		 * @since 0.1.0
		 */
		public function add_tax_scripts() {
			ob_start();
			?>
			<script>
				( function( $ ) {
					'use strict';
					const taxForm = $( 'form[action="edit-tags.php"]' );

					// Hide unused fields
					taxForm.find( '.term-slug-wrap' ).hide();
					taxForm.find( '.term-name-wrap p' ).hide();
					taxForm.find( '.term-description-wrap p' ).hide();
				}( jQuery ) );
			</script>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}

		/**
		 * Register AnWP Sidebars.
		 *
		 * @since 0.1.0
		 */
		public function register_sidebars() {

			$sidebars = $this->get_sidebars();

			if ( empty( $sidebars ) ) {
				return;
			}

			foreach ( $sidebars as $sidebar ) {

				register_sidebar(
					[
						'id'            => 'anwp-sidebar-' . sanitize_key( $sidebar['term_id'] ),
						'name'          => $sidebar['name'],
						'description'   => $sidebar['description'],
						'before_widget' => '<section id="%1$s" class="anwp-widget widget %2$s">',
						'after_widget'  => '</section>',
						'before_title'  => '<h4 class="widget-title anwp-widget-title anwp-theme-main-bg">',
						'after_title'   => '</h4>',
					]
				);
			}
		}

		/**
		 * Get list of registered sidebars.
		 *
		 * @return array|WP_Error List of WP_Term instances and their children. Will return WP_Error, if any of $taxonomies do not exist.
		 * @since 0.1.0
		 */
		public function get_sidebars() {

			if ( null === self::$sidebars ) {

				self::$sidebars = [];

				$sidebar_terms = get_terms(
					[
						'taxonomy'   => 'anwp_sidebar',
						'hide_empty' => false,
					]
				);

				if ( ! empty( $sidebar_terms ) && is_array( $sidebar_terms ) ) {
					foreach ( $sidebar_terms as $sidebar_term ) {
						self::$sidebars[] = [
							'term_id'     => $sidebar_term->term_id,
							'name'        => $sidebar_term->name,
							'description' => $sidebar_term->description,
						];
					}
				}
			}

			return self::$sidebars;
		}

		/**
		 * Get list of registered sidebars for CMB2 options.
		 *
		 * @return array
		 * @since 0.1.0
		 */
		public function get_sidebar_options() {

			$sidebars = [];

			foreach ( self::get_sidebars() as $sidebar ) {
				$sidebars[ $sidebar['term_id'] ] = $sidebar['name'];
			}

			return $sidebars;
		}

		/**
		 * Magic getter for our object.
		 *
		 * @param string $field Field to get.
		 *
		 * @return mixed         Value of the field.
		 * @throws Exception     Throws an exception if the field is invalid.
		 * @since  0.1.0
		 *
		 */
		public function __get( $field ) {

			if ( property_exists( $this, $field ) ) {
				return $this->$field;
			}

			throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Grab the AnWP_Sidebars object and return it.
	 * Wrapper for AnWP_Sidebars::get_instance().
	 *
	 * @return AnWP_Sidebars Singleton instance of plugin class.
	 * @since  0.1.0
	 */
	function anwp_sidebars() {
		return AnWP_Sidebars::get_instance();
	}

	// Kick it off.
	add_action( 'plugins_loaded', [ anwp_sidebars(), 'hooks' ] );
endif;
