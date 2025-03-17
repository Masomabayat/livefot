/**
 * AnWP Football Leagues Admin Premium
 * https://anwp.pro
 *
 * Licensed under the GPLv2+ license.
 */

window.AnWPFootballLeaguesAdminPremiumReport = window.AnWPFootballLeaguesAdminPremiumReport || {};

( function( window, document, $, plugin ) {

	'use strict';

	var $c = {};

	plugin.init = function() {
		plugin.cache();
		plugin.bindEvents();
	};

	plugin.cache = function() {
		$c.window      = $( window );
		$c.body        = $( document.body );
		$c.sendButtons = $( '.anwp-fl-send-report' );
	};

	plugin.bindEvents = function() {
		if ( document.readyState !== 'loading' ) {
			plugin.onPageReady();
		} else {
			document.addEventListener( 'DOMContentLoaded', plugin.onPageReady );
		}
	};

	plugin.onPageReady = function() {
		plugin.initSendButtons();
	};

	plugin.initSendButtons = function() {
		if ( ! $c.sendButtons.length ) {
			return false;
		}

		$c.sendButtons.on( 'click', function( e ) {

			var $this    = $( this );
			var $spinner = $this.find( '.spinner' );

			e.preventDefault();

			$spinner.addClass( 'is-active' );

			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'fl_send_match_report',
					nonce: anwpflGlobals.ajaxNonce,
					match_id: $this.data( 'match-id' )
				}
			} ).done( function( response ) {
				if ( response.success ) {
					toastr.success( 'Send Successfully' );
				}
			} ).always( function() {
				$spinner.removeClass( 'is-active' );
			} );
		} );
	};

	$( plugin.init );
}( window, document, jQuery, window.AnWPFootballLeaguesAdminPremiumReport ) );
