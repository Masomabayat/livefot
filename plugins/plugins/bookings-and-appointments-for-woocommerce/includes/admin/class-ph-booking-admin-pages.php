<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class phive_booking_admin_report {
	public function __construct() {
		add_action(	'admin_menu', array( $this, 'ph_booking_admin_report_menu' ) );
	}

	
	public function ph_booking_admin_report_menu(){	
		add_menu_page('bookings',
		 __('Bookings','bookings-and-appointments-for-woocommerce'), 
			'manage_options',
			 'all-bookings', 
			 array($this, 'ph_generate_booking_report'), 
			 'dashicons-calendar', 
			 56
		);
		add_submenu_page(
			'all-bookings',
			'Booking Settings',
			__('Settings','bookings-and-appointments-for-woocommerce'),
			'manage_options',
			'bookings-settings', 
			array( $this, 'ph_booking_settings_page' )
		);
	}

	function ph_generate_booking_report(){
		include_once('class-ph-booking-admin-reports-list.php');
		$bookings_list = new phive_booking_all_list();

		printf( '<div class="wrap"><h2>%s</h2>', __( 'Bookings', 'bookings-and-appointments-for-woocommerce' ) );
		echo '<form id="booking-list-table-form" method="post">';

		$bookings_list->prepare_items();
		$bookings_list->display();
		
		echo '</form>';
		echo '</div>';
	}
	function ph_booking_settings_page(){
		?>
		<div class="wrap woocommerce">
			<div id="icon-options-general" class="icon32"></div>
			

			<?php
			$active_tab = !empty($_GET['tab']) ? $_GET['tab'] : 'calendar-color';
			?>
			
			<h2 class="nav-tab-wrapper">
				
				<a href="?page=bookings-settings&tab=calendar-color-customizer" class="nav-tab <?php if($active_tab == 'calendar-color-customizer'){echo 'nav-tab-active';} ?>"><?php _e('Customize Calendar Color', 'bookings-and-appointments-for-woocommerce'); ?></a>
				
			</h2>


			<div class="metabox-holder has-right-sidebar">
				<?php
				// if( $active_tab == "calendar-color-customizer" ){
					$this->ph_settings_calendar_color();
				// }
				
				?>
			</div> 
		</div>
		<?php
	}
	private function ph_settings_calendar_color(){
		include('views/html-ph-booking-settings-calendar-color-customizer.php');
	}

}
new phive_booking_admin_report;