<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnWP Football Leagues Premium :: Builder :: Interface
 *
 * @since 0.11.3
 */
interface AnWPFL_Premium_Builder_Interface {

	/**
	 * Get builder type name.
	 */
	public function get_builder_type_name();

	/**
	 * Get tutorial link.
	 *
	 * @return string
	 */
	public function get_tutorial_link(): string;

	/**
	 * Get builder type option.
	 */
	public function get_builder_type_option();

	/**
	 * Get dynamic variables info.
	 *
	 * @return string
	 */
	public function get_dynamic_variables_info(): string;

	/**
	 * Replace dynamic variables.
	 *
	 * @param string $block_text
	 * @param int    $post_id
	 *
	 * @return string
	 */
	public function replace_dynamic_variables( string $block_text, int $post_id ): string;

	/**
	 * Get builder wrapper classes.
	 *
	 * @param WP_Post $post
	 */
	public function get_builder_wrapper_classes( WP_Post $post );

	/**
	 * Get builder wrapper attributes.
	 *
	 * @param WP_Post $post
	 */
	public function get_builder_wrapper_attributes( WP_Post $post );

	/**
	 * Rendering admin list content (builder type).
	 *
	 * @param int $post_id
	 */
	public function admin_list_column_display( int $post_id );

	/**
	 * Rendering admin list icon (builder type).
	 *
	 * @param int $post_id
	 */
	public function admin_list_icon_display( int $post_id );

	/**
	 * Get builder layout ID.
	 *
	 * @param WP_Post $post
	 *
	 * @return bool
	 */
	public function get_builder_layout_id( WP_Post $post );

	/**
	 * Get type elements.
	 */
	public function get_type_elements();

}
