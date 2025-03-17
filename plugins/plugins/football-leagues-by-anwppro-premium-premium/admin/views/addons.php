<?php
/**
 * Add-ons page for AnWP Football Leagues
 *
 * @link       https://anwp.pro
 * @since      0.15.0
 *
 * @package    AnWP_Football_Leagues
 * @subpackage AnWP_Football_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues' ) );
}

$fl_addons = wp_parse_args(
	get_option( 'anwp-fl-addons', [] ),
	[
		'megamenu' => '',
		'sidebars' => '',
	]
)
?>
<div class="about-wrap anwp-b-wrap">
	<div class="inside">
		<h3 class="text-left">Recommended Themes</h3>
		<div class="bg-white py-3 px-4" style="width: 500px">
			The plugin works well with most classic themes out of the box.<br>
			Our recommendations are:

			<ul class="mt-1">
				<li>
					<a target="_blank" href="https://wordpress.org/themes/kadence/">Kadence</a>
				</li>
				<li>
					<a target="_blank" href="https://wordpress.org/themes/generatepress/">GeneratePress</a>
				</li>
				<li>
					<a target="_blank" href="https://wordpress.org/themes/blocksy/">Blocksy</a>
				</li>
			</ul>

			<h4 class="mt-4 mb-1">Useful tutorials:</h4>
			- <a href="https://www.anwp.pro/kadence-wp-setup-from-scratch-for-football-website/" target="_blank">Kadence WP - Setup from Scratch - Part 1</a><br>
			- <a href="https://www.anwp.pro/kadence-wp-setup-from-scratch-for-football-website-part-2/" target="_blank">Kadence WP - Setup from Scratch - Part 2</a><br>
			- <a href="https://www.anwp.pro/anwp-block-megamenu-add-on/" target="_blank">AnWP Block MegaMenu add-on</a>
		</div>

		<h3 class="text-left">Special Add-ons available for all Premium users</h3>

		<div class="d-flex flex-wrap mx-n2">
			<div class="bg-white p-3 anwp-w-400 m-2 d-flex flex-column">
				<h3 class="mt-2 mb-3 anwp-font-normal anwp-text-center">AnWP Block MegaMenu</h3>
				<div class="anwp-text-center">
					<span class="px-2 text-white ml-2 anwp-flex-none d-inline-block mr-auto anwp-rounded <?php echo 'yes' === $fl_addons['megamenu'] && ! defined( 'ANETO_VERSION' ) ? 'anwp-bg-green-600' : 'anwp-bg-gray-500'; ?>">
						<?php echo 'yes' === $fl_addons['megamenu'] && ! defined( 'ANETO_VERSION' ) ? 'Active' : 'Disabled'; ?>
					</span>
				</div>

				<div class="mb-4">
					<p>Simple mega menu plugin which helps you to create awesome dropdown menus using Gutenberg editor.</p>
				</div>

				<div class="d-flex flex-wrap align-items-center justify-content-center anwp-bg-gray-100 p-2 mt-auto">
					<?php if ( defined( 'ANETO_VERSION' ) ) : ?>
						<span class="my-2 p-2 anwp-bg-orange-100 anwp-border anwp-border-orange-800 anwp-text-xs">
							the plugin doesn't work with Aneto theme
						</span>
					<?php else : ?>
						<button class="button button-secondary d-flex align-items-center ml-2 px-3 anwp-fl-addon-switcher" type="button"
								data-addon="megamenu" data-switch="<?php echo 'yes' === $fl_addons['megamenu'] ? '' : 'yes'; ?>">
							<?php echo 'yes' === $fl_addons['megamenu'] ? 'Deactivate' : 'Activate'; ?>
						</button>
						<span class="spinner is-active ml-1 mt-0 d-none"></span>
					<?php endif; ?>
				</div>
			</div>

			<div class="bg-white p-3 anwp-w-400 m-2 d-flex flex-column">
				<h3 class="mt-2 mb-3 anwp-font-normal anwp-text-center">AnWP Sidebars</h3>
				<div class="anwp-text-center">
					<span class="px-2 text-white ml-2 anwp-flex-none d-inline-block mr-auto anwp-rounded <?php echo 'yes' === $fl_addons['sidebars'] && ! defined( 'ANETO_VERSION' ) ? 'anwp-bg-green-600' : 'anwp-bg-gray-500'; ?>">
						<?php echo 'yes' === $fl_addons['sidebars'] && ! defined( 'ANETO_VERSION' ) ? 'Active' : 'Disabled'; ?>
					</span>
				</div>

				<div class="mb-4">
					<p>Create any number of custom sidebars you want.</p>
				</div>

				<div class="d-flex flex-wrap align-items-center justify-content-center anwp-bg-gray-100 p-2 mt-auto">
					<?php if ( defined( 'ANETO_VERSION' ) ) : ?>
						<span class="my-2 p-2 anwp-bg-orange-100 anwp-border anwp-border-orange-800 anwp-text-xs">
							the plugin doesn't work with Aneto theme
						</span>
					<?php else : ?>
						<button class="button button-secondary d-flex align-items-center ml-2 px-3 anwp-fl-addon-switcher" type="button"
								data-addon="sidebars" data-switch="<?php echo 'yes' === $fl_addons['sidebars'] ? '' : 'yes'; ?>">
							<?php echo 'yes' === $fl_addons['sidebars'] ? 'Deactivate' : 'Activate'; ?>
						</button>
						<span class="spinner is-active ml-1 mt-0 d-none"></span>
					<?php endif; ?>
				</div>
			</div>

			<div class="bg-white py-1 px-3 anwp-w-400 m-2">
				<h3 class="my-3 anwp-font-normal anwp-text-center">Aneto Theme (deprecated)</h3>

				<div class="p-3 anwp-bg-orange-100 anwp-border anwp-border-orange-800">
					Aneto theme is deprecated and no longer maintained (but you can use it as long as you want). See the "Recommended Themes" section.
				</div>

				<div class="mb-4">
					<p>Documentation - <a href="https://anwppro.userecho.com/knowledge-bases/15-aneto-tutorials" target="_blank">Aneto Docs</a></p>
					<p>Your key:<br>
						<code>sk_[oNvf?)(G~78lz6EeoJc;3P&S@UPJ</code>
					</p>
					<p>
						Theme archive (update to the latest version will be available after install):
						<a href="https://drive.google.com/file/d/1nQCD5LHn-Y2hloSvhcMn15RVtKPQclpF/view?usp=sharing" target="_blank">Aneto v0.12.0</a>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	( function( $ ) {
		'use strict';

		let notyf = false;

		if ( 'undefined' !== typeof Notyf ) {
			notyf = new Notyf();
		}

		$( function() {

			let activeRequest = false;

			$( '.anwp-fl-addon-switcher' ).on( 'click', function( e ) {

				e.preventDefault();
				const $this = $( this );

				if ( activeRequest ) {
					return;
				}

				activeRequest = true;
				$this.siblings( 'span.spinner' ).removeClass( 'd-none' );
				$this.prop( 'disabled', true );

				jQuery.ajax( {
					dataType: 'json',
					method: 'POST',
					data: {
						'addon': $this.data( 'addon' ),
						'switch': $this.data( 'switch' ),
					},
					beforeSend: function( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', anwp.rest_nonce );
					}.bind( this ),
					url: anwp.rest_root + 'anwpfl/v1/addons/toggle-addon/',
				} ).done( () => {
					location.reload();
				} ).fail( ( response ) => {
					notyf.error( response.responseJSON.message ? response.responseJSON.message : 'Error' );
				} ).always( () => {
					activeRequest = false;
					$this.siblings( 'span.spinner' ).removeClass( 'is-active' );
					$this.prop( 'disabled', false );
				} );
			} );
		} );
	}( jQuery ) );
</script>
