<?php

/**
 * Plugin Name: AnWP Football Leagues Premium
 * Plugin URI:  https://anwppro.userecho.com/communities/1-football-leagues
 * Description: Premium Version of AnWP Football Leagues.
 * Version:     0.16.1
 * Update URI: https://api.freemius.com
 * Author:      Andrei Strekozov <anwppro>
 * Author URI:  https://anwp.pro
 * License:     GPLv2+
 * Requires PHP: 7.0
 * Text Domain: anwp-football-leagues-premium
 * Domain Path: /languages
 *
 * @package AnWP_Football_Leagues_Premium
 *
 * Built using generator-plugin-wp (https://github.com/WebDevStudios/generator-plugin-wp)
 */
/**
 * Copyright (c) 2017-2024 Andrei Strekozov <anwppro> (email: anwp.pro@gmail.com)
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
if ( !defined( 'ABSPATH' ) ) {
    die;
}
define( 'ANWP_FL_PREMIUM_VERSION', '0.16.1' );
// Check for required PHP version

if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
    add_action( 'admin_notices', 'anwpfl_premium_requirements_not_met_notice' );
    add_action( 'admin_init', function () {
        if ( function_exists( 'deactivate_plugins' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
        }
    } );
} else {
    
    if ( !function_exists( 'flbap_fs' ) ) {
        // Create a helper function for easy SDK access.
        function flbap_fs()
        {
            global  $flbap_fs ;
            
            if ( !isset( $flbap_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $flbap_fs = fs_dynamic_init( [
                    'id'               => '2789',
                    'slug'             => 'football-leagues-by-anwppro-premium',
                    'type'             => 'plugin',
                    'public_key'       => 'pk_9ab24c586f6bfd7c1e4a177495e55',
                    'is_premium'       => true,
                    'is_premium_only'  => true,
                    'has_addons'       => false,
                    'has_paid_plans'   => true,
                    'is_org_compliant' => false,
                    'trial'            => [
                    'days'               => 7,
                    'is_require_payment' => true,
                ],
                    'menu'             => [
                    'slug'    => 'anwp-football-leagues',
                    'contact' => false,
                    'support' => false,
                ],
                    'is_live'          => true,
                ] );
            }
            
            return $flbap_fs;
        }
        
        // Init Freemius.
        flbap_fs();
        // Signal that SDK was initiated.
        do_action( 'flbap_fs_loaded' );
    }
    
    // Require the main plugin class
    require_once plugin_dir_path( __FILE__ ) . 'class-anwp-football-leagues-premium.php';
    // Kick it off.
    add_action( 'plugins_loaded', array( anwp_football_leagues_premium(), 'hooks' ) );
    // Activation and deactivation.
    register_deactivation_hook( __FILE__, array( anwp_football_leagues_premium(), 'deactivate' ) );
}

function anwp_fl_pro_check_main_plugin()
{
    if ( !class_exists( 'AnWP_Football_Leagues' ) ) {
        add_action( 'admin_notices', 'anwpfl_premium_requirements_not_met_notice' );
    }
}

add_action( 'plugins_loaded', 'anwp_fl_pro_check_main_plugin' );
/**
 * Adds a notice to the dashboard if the plugin requirements are not met.
 *
 * @since  0.1.0
 * @return void
 */
function anwpfl_premium_requirements_not_met_notice()
{
    // Default details.
    $details = '';
    
    if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
        ?>
		<div class="notice notice-error">
			<p>
				<?php 
        /* translators: %s minimum PHP version */
        echo  sprintf( esc_html__( 'Football Leagues Premium cannot run on PHP versions older than %s. Please contact your hosting provider to update your site.', 'anwp-football-leagues-premium' ), '7.0' ) ;
        ?>
			</p>
		</div>
		<?php 
    } elseif ( !class_exists( 'AnWP_Football_Leagues' ) ) {
        if ( !current_user_can( 'install_plugins' ) ) {
            return;
        }
        // phpcs:ignore WordPress.Security.NonceVerification
        if ( isset( $_GET['action'] ) && 'install-plugin' === $_GET['action'] ) {
            return;
        }
        // Check FL core installed
        if ( !function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all_plugins = get_plugins();
        $plugin_installed = isset( $all_plugins['football-leagues-by-anwppro/anwp-football-leagues.php'] );
        ?>
		<div class="notice notice-error anwp-fl-pro-notice">
			<img style="float: left; width: 95px; margin-right: 15px; margin-top: 15px; margin-bottom: 10px;" src="<?php 
        echo  esc_url( AnWP_Football_Leagues_Premium::url( 'admin/img/fl-pro-icon.png' ) ) ;
        ?>">
			<h3 style="margin: 15px 0 5px; color: #dc3232; font-size: 1.2em;"><?php 
        echo  esc_html__( 'Missing required plugin', 'anwp-football-leagues-premium' ) ;
        ?></h3>
			<p style="margin: 10px 0;"><?php 
        echo  esc_html__( 'Football Leagues Premium requires free version of Football Leagues by AnWP.pro. Please install it first.', 'anwp-football-leagues-premium' ) ;
        ?></p>
			<p style="margin: 10px 0;">
				<?php 
        
        if ( $plugin_installed && current_user_can( 'activate_plugins' ) ) {
            ?>
					<a href="<?php 
            echo  esc_url( wp_nonce_url( 'plugins.php?action=activate&plugin=' . rawurlencode( 'football-leagues-by-anwppro/anwp-football-leagues.php' ), 'activate-plugin_football-leagues-by-anwppro/anwp-football-leagues.php' ) ) ;
            ?>" class="button button-primary"><?php 
            echo  esc_html__( 'Activate plugin', 'anwp-football-leagues-premium' ) ;
            ?></a>
				<?php 
        } elseif ( current_user_can( 'install_plugins' ) ) {
            ?>
					<a href="<?php 
            echo  esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=football-leagues-by-anwppro' ), 'install-plugin_football-leagues-by-anwppro' ) ) ;
            ?>" class="button button-primary"><?php 
            echo  esc_html__( 'Install plugin', 'anwp-football-leagues-premium' ) ;
            ?></a>
				<?php 
        }
        
        ?>
			</p>
			<p style="clear: both; margin: 0;"></p>
		</div>
		<?php 
    }

}

/**
 * Grab the AnWP_Football_Leagues_Premium object and return it.
 * Wrapper for AnWP_Football_Leagues_Premium::get_instance().
 *
 * @since  0.1.0
 * @return AnWP_Football_Leagues_Premium  Singleton instance of plugin class.
 * @deprecated use short version anwp_fl_pro()
 */
function anwp_football_leagues_premium() : AnWP_Football_Leagues_Premium
{
    return AnWP_Football_Leagues_Premium::get_instance();
}

/**
 * Grab the AnWP_Football_Leagues_Premium object and return it.
 * Wrapper for AnWP_Football_Leagues_Premium::get_instance().
 *
 * @since  0.1.0
 * @return AnWP_Football_Leagues_Premium  Singleton instance of plugin class.
 */
function anwp_fl_pro() : AnWP_Football_Leagues_Premium
{
    return AnWP_Football_Leagues_Premium::get_instance();
}
