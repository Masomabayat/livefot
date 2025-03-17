/**
 * AnWP Football Leagues Admin Premium
 * https://anwp.pro
 *
 * Licensed under the GPLv2+ license.
 */

window.AnWPFootballLeaguesAdminPremium = window.AnWPFootballLeaguesAdminPremium || {};

( function( window, document, $, plugin ) {

	'use strict';

	var $c = {};

	plugin.init = function() {
		plugin.cache();
		plugin.bindEvents();
	};

	plugin.cache = function() {
		$c.window     = $( window );
		$c.body       = $( document.body );
		$c.proOptions = $( '#anwp_football_leagues_premium_options_metabox' );
	};

	plugin.bindEvents = function() {
		if ( document.readyState !== 'loading' ) {
			plugin.onPageReady();
		} else {
			document.addEventListener( 'DOMContentLoaded', plugin.onPageReady );
		}
	};

	plugin.onPageReady = function() {
		plugin.initOptionTabs();
	};

	/**
	 * Initialize options and metabox tabs.
	 *
	 * @return {boolean} False if metabox not exists at the page.
	 */
	plugin.initOptionTabs = function() {

		if ( ! $c.proOptions.length ) {
			return false;
		}

		$c.proOptions.on( 'click', '.anwp-fl-pro-options__control-item', function( e ) {

			var $this  = $( this );
			var target = $( $this.attr( 'href' ) );

			e.preventDefault();

			if ( $this.hasClass( 'nav-tab-active' ) ) {
				return false;
			}

			$this.addClass( 'nav-tab-active' ).siblings( '.nav-tab-active' ).removeClass( 'nav-tab-active' );
			target.removeClass( 'd-none invisible' ).siblings( '.anwp-fl-pro-options__content-item:not( .d-none )' ).addClass( 'd-none' );

			// Add hash to URL
			if ( $this.attr( 'href' ) ) {
				if ( history.pushState ) {
					history.pushState( {}, '', $this.attr( 'href' ) );
				} else {
					window.location.hash = $this.attr( 'href' ).substr( 1 );
				}
			}
		} );

		// Get initial active tab
		var initialTab;

		if ( window.location.hash ) {
			initialTab = $c.proOptions.find( '.anwp-fl-pro-options__control-item[href="' + window.location.hash + '"]' );
		}

		if ( ! initialTab || ! initialTab.length ) {
			initialTab = $c.proOptions.find( '.anwp-fl-pro-options__control-item:first-child' );
		}

		initialTab.trigger( 'click' );
	};

	$( plugin.init );
}( window, document, jQuery, window.AnWPFootballLeaguesAdminPremium ) );
