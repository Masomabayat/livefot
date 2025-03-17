<?php
/**
 * Import Matches Tool for AnWP Football Leagues Premium
 *
 * @link       https://anwp.pro
 * @since      0.9.3
 *
 * @package    AnWP_Football_Leagues_Premium
 * @subpackage AnWP_Football_Leagues_Premium/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues' ) );
}

$competitions = anwp_football_leagues()->competition->get_competitions();
?>
<div class="anwp-b-wrap wrap" id="anwpfl-import-wrapper">

	<h2 class="text-left"><?php echo esc_html__( 'Batch Matches Import', 'anwp-football-leagues-premium' ); ?></h2>

	<div class="anwp-shortcode-docs-link my-2">
		<svg class="anwp-icon anwp-icon--octi anwp-icon--s16">
			<use xlink:href="#icon-book"></use>
		</svg>
		<strong class="mx-2"><?php echo esc_html__( 'Documentation', 'anwp-football-leagues' ); ?>:</strong>
		<a target="_blank" href="https://anwppro.userecho.com/knowledge-bases/2/articles/425-import-matches-tool"><?php echo esc_html__( 'Import Matches tool', 'anwp-football-leagues-premium' ); ?></a>
	</div>

	<div class="d-flex mt-3">
		<div class="col-auto pl-0">
			<label for="anwp-selector-competition" class="d-block"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?></label>
			<select name="" id="anwp-selector-competition">
				<option value=""><?php echo esc_html__( '- select competition -', 'anwp-football-leagues' ); ?></option>
				<?php foreach ( $competitions as $competition ) : ?>
					<option value="<?php echo esc_attr( $competition->id ); ?>"><?php echo esc_html( $competition->title_full ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<span class="spinner mt-4 px-0 mx-0" id="anwp-fl-matches-import-spinner-group"></span>
		<div class="col-auto pl-0 ml-n3" id="anwp-selector-group-wrap"></div>
	</div>

	<div id="anwp-fl-import-matches-clubs" class="table-info my-3"></div>

	<div class="my-3 d-none" id="anwp-fl-import-matches-tips">
		<p class="font-italic my-0">* - <?php echo esc_html__( 'if both "Goals H (Home)" and "Goals A (Away)" will be empty, match will be treated as fixture (upcoming)', 'anwp-football-leagues-premium' ); ?></p>
		<p class="font-italic my-0">** - <?php echo esc_html__( 'you may copy and paste data from MS Excel or other spreadsheet into the table below', 'anwp-football-leagues-premium' ); ?></p>
		<p class="font-italic my-0">*** - <?php echo esc_html__( 'home club stadium will be set as a game venue', 'anwp-football-leagues-premium' ); ?></p>
		<p class="font-italic my-0">**** - <?php echo esc_html__( '"HT - Goals H" - half time goals by home team', 'anwp-football-leagues-premium' ); ?></p>
	</div>

	<div id="anwp-fl-import-matches-table"></div>

	<div class="anwpfl-batch-import-save-wrapper">
		<div class="mt-3" id="anwp-fl-import-matches-save-info"></div>
		<div>
			<button id="anwp-fl-import-matches-save-btn" type="button" class="button button-primary px-4 d-none">
				<?php echo esc_html__( 'Save Data', 'anwp-football-leagues' ); ?>
			</button>
			<span class="spinner mt-1 px-0 mx-2 float-none"></span>
		</div>
	</div>

</div>

<script>
	(function( $ ) {
		'use strict';

		$( function() {

			var $wrapper           = $( '#anwpfl-import-wrapper' );
			var $wrapperClubs      = $( '#anwp-fl-import-matches-clubs' );
			var $selectCompetition = $( '#anwp-selector-competition' );
			var $selectGroupWrap   = $( '#anwp-selector-group-wrap' );
			var $spinnerGroup      = $( '#anwp-fl-matches-import-spinner-group' );
			var $infoBlock         = $( '#anwp-fl-import-matches-save-info' );
			var $btnSave           = $( '#anwp-fl-import-matches-save-btn' );
			var $wrapperTips       = $( '#anwp-fl-import-matches-tips' );
			var $xhr               = null;
			var clubsMap           = null;
			var clubsMapHtml       = null;
			var jexcelActive       = false;
			var activeRequest      = false;
			var competitionType    = '';
			var nonce              = '<?php echo esc_html( wp_create_nonce( 'anwp-fl-import-matches' ) ); ?>';

			function initJexcel() {

				if ( competitionType === 'round-robin' ) {
					$( '#anwp-fl-import-matches-table' ).jexcel( {
						data: [],
						columns: [
							{
								title: 'Club Home',
								type: 'dropdown',
								source: clubsMap[ $wrapper.find( '#anwp-selector-group' ).val() ],
								autocomplete: true,
								width: 150
							},
							{
								title: 'Club Away',
								width: 150,
								type: 'dropdown',
								source: clubsMap[ $wrapper.find( '#anwp-selector-group' ).val() ],
								autocomplete: true
							},
							{
								title: 'Goals H',
								width: 60,
								type: 'numeric'
							},
							{
								title: 'Goals A',
								width: 60,
								type: 'numeric'
							},
							{
								type: 'numeric',
								title: 'Kickoff (YYYY-MM-DD HH:MM)',
								width: 140,
								mask: 'yyyy-mm-dd hh24:mi'
							},
							{
								title: 'MatchWeek',
								width: 100,
								type: 'numeric'
							},
							{
								title: 'HT - Goals H',
								width: 90,
								type: 'numeric'
							},
							{
								title: 'HT - Goals A',
								width: 90,
								type: 'numeric'
							},
							{
								title: 'Aggregate Text',
								width: 160,
								type: 'text'
							}
						],
						allowToolbar: true,
						columnSorting: false,
						rowDrag: false,
						allowInsertRow: true,
						allowManualInsertRow: true,
						allowInsertColumn: false,
						allowManualInsertColumn: false,
						allowDeleteRow: false,
						allowDeletingAllRows: false,
						allowDeleteColumn: false,
						allowRenameColumn: false,
						defaultColWidth: '110px',
						rowResize: true,
						minDimensions: [ 1, 5 ],
						contextMenu: function() {
							return null;
						},
					} );
				} else {
					$( '#anwp-fl-import-matches-table' ).jexcel( {
						data: [],
						columns: [
							{
								title: 'Club Home',
								width: 150,
								type: 'dropdown',
								source: clubsMap[ $wrapper.find( '#anwp-selector-group' ).val() ],
								autocomplete:true
							},
							{
								title: 'Club Away',
								width: 150,
								type: 'dropdown',
								source: clubsMap[ $wrapper.find( '#anwp-selector-group' ).val() ],
								autocomplete:true
							},
							{
								title: 'Goals H',
								width: 60,
								type: 'numeric'
							},
							{
								title: 'Goals A',
								width: 60,
								type: 'numeric'
							},
							{
								type: 'numeric',
								title: 'Kickoff (YYYY-MM-DD HH:MM)',
								width: 140,
								mask: 'yyyy-mm-dd hh24:mi'
							},
							{
								title: 'HT - Goals H',
								width: 90,
								type: 'numeric'
							},
							{
								title: 'HT - Goals A',
								width: 90,
								type: 'numeric'
							},
							{
								title: 'Aggregate Text',
								width: 160,
								type: 'text'
							}
						],
						allowToolbar: true,
						columnSorting: false,
						rowDrag: false,
						allowInsertRow: true,
						allowManualInsertRow: true,
						allowInsertColumn: false,
						allowManualInsertColumn: false,
						allowDeleteRow: false,
						allowDeletingAllRows: false,
						allowDeleteColumn: false,
						allowRenameColumn: false,
						defaultColWidth: '110px',
						rowResize: true,
						minDimensions: [ 1, 5 ],
						contextMenu: function() {
							return null;
						},
					} );
				}

				jexcelActive = true;
				$wrapperTips.removeClass( 'd-none' );
				$btnSave.removeClass( 'd-none' );
			}

			function destroyJexcel() {
				if ( ! jexcelActive ) {
					return false;
				}

				$('#anwp-fl-import-matches-table').jexcel('destroy');
				jexcelActive = false;
				$wrapperTips.addClass( 'd-none' );
				$btnSave.addClass( 'd-none' );
			}

			if ( typeof jQuery.fn.jexcel === 'undefined' ) {
				return;
			}

			$selectCompetition.on( 'change', function() {

				if ( $xhr && $xhr.readyState !== 4 ) {
					$xhr.abort();
				}

				$selectGroupWrap.html( '' );
				clubsMap     = null;
				clubsMapHtml = null;
				$wrapperClubs.html( '' );

				destroyJexcel();

				if ( $selectCompetition.val() ) {

					$spinnerGroup.addClass( 'is-active' );
					$infoBlock.html( '' );

					$xhr = $.ajax( {
						url: ajaxurl,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'anwp_fl_import_matches',
							_ajax_nonce: nonce,
							competition: $selectCompetition.val()
						}
					} ).done( function( response ) {
						if ( response.success ) {
							$selectGroupWrap.html( response.data.html );

							clubsMapHtml = response.data.clubs_html;
							clubsMap     = response.data.clubs;
							competitionType = response.data.type;
						} else {
							$infoBlock.html( '<div class="alert alert-danger">Data Error! Reload page and try again.</div>' );
						}
					} ).fail( function() {
						$infoBlock.html( '<div class="alert alert-danger">Data Error! Reload page and try again.</div>' );
					} ).always( function() {
						$spinnerGroup.removeClass( 'is-active' );
					} );
				} else {
					$spinnerGroup.removeClass( 'is-active' );
				}
			} );

			$wrapper.on( 'change', '#anwp-selector-group', function() {
				var $this = $( this );

				$wrapperClubs.html( '' );
				destroyJexcel();

				if ( clubsMapHtml[ $this.val() ] ) {
					$wrapperClubs.html( clubsMapHtml[ $this.val() ] );
					initJexcel();
				}
			} );

			// Save data
			$btnSave.on( 'click', function( e ) {

				e.preventDefault();

				if ( ! $wrapper.find( '#anwp-selector-group' ).val() || ! $selectCompetition.val() ) {
					toastr.error( 'Incomplete data!' );
					return false;
				}

				// Check for active request and type
				if ( activeRequest ) {
					toastr.error( 'Previous request still active' );
					return false;
				}

				activeRequest = true;
				$btnSave.prop( 'disabled', true );
				$btnSave.next( '.spinner' ).addClass( 'is-active' );

				jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'anwp_fl_import_matches_save',
						_ajax_nonce: nonce,
						competition: $selectCompetition.val(),
						group: $wrapper.find( '#anwp-selector-group' ).val(),
						table: $( '#anwp-fl-import-matches-table' ).jexcel( 'getData', false )
					}
				} ).done( function( response ) {
					if ( response.success && response.data.qty ) {
						$infoBlock.html( '<div class="alert alert-info">Created Matches: ' + response.data.qty + '</div>' );
						$selectCompetition.val( '' ).trigger( 'change' );

						$( 'html, body' ).animate( {
							scrollTop: 0
						}, 500 );
					} else {
						$infoBlock.html( '<div class="alert alert-danger">Save Error! Reload page and try again.</div>' );
					}
				} ).fail( function() {
					$infoBlock.html( '<div class="alert alert-danger">Save Error! Reload page and try again.</div>' );
				} ).always( function() {
					activeRequest = false;
					$btnSave.prop( 'disabled', false );
					$btnSave.next( '.spinner' ).removeClass( 'is-active' );
				} );
			} );
		} );
	}( jQuery ));
</script>

