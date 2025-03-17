<?php
/**
 * The sidebar containing the second widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package True News
 */

if ( ! is_active_sidebar( 'sidebar-2' ) ) {
	return;
}

dynamic_sidebar( 'sidebar-2' );
