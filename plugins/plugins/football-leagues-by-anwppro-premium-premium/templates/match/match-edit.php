<?php
/**
 * The Template for displaying Match >> Edit Section.
 * !!!! Not Override it if you don't know how it works.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-edit.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues-Premium/Templates
 * @since         0.8.1
 *
 * @version       0.11.12
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$data,
	[
		'match_id' => '',
		'finished' => '',
	]
);

$live_edit = anwp_fl_pro()->match_public->is_front_live_edit_enabled() && ! absint( $data['finished'] );
?>
<div class="anwp-fl-match-edit-panel anwp-b-wrap w-100 anwp-col-12">
	<div class="d-flex mb-3">
		<div class="anwp-flex-1 <?php echo $live_edit ? 'mr-2' : ''; ?>">
			<div id="anwpfl-app-match-edit-front"></div>
		</div>
		<?php
		/*
		|--------------------------------------------------------------------
		| LIVE UI
		|--------------------------------------------------------------------
		*/
		if ( $live_edit ) :
			?>
			<div class="anwp-flex-1 ml-2">
				<div id="anwpfl-app-match-live-ui-front"></div>
			</div>
		<?php endif; ?>
	</div>
</div>
