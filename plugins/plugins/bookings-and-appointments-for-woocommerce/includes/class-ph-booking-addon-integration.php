<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WC_Product_phive_booking_addon_integration {


	public function __construct(){

		//add_filter( 'woocommerce_product_addons_show_grand_total', array( $this, 'phive_addon_hide_total' ), 20, 2 );
		add_filter('phive_booking_cost',array($this,'phive_apply_addon_price'),10,3);
	}


	public function phive_addon_hide_total($show_total, $product){
		if ( $product->is_type( 'phive_booking' ) ) {
			$show_total = false;
		}
		return $show_total;
	}

	public function phive_apply_addon_price($booking_cost,$id) {
		if (  in_array( 'woocommerce-product-addons/woocommerce-product-addons.php', 
		    apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || in_array('woocommerce-product-addons-master/woocommerce-product-addons.php',apply_filters( 'active_plugins', get_option( 'active_plugins' ) ))) {
			parse_str($_POST['addon_data'],$addon_data);
	
			$addons       = $GLOBALS['Product_Addon_Cart']->add_cart_item_data( array(), $id, $addon_data, true );
			$addon_costs  = 0;
			
			if ( ! empty( $addons['addons'] ) ) {
				foreach ( $addons['addons'] as $addon ) {
					
					$addon['price'] = ( ! empty( $addon['price'] ) ) ? $addon['price'] : 0;

					
					$addon_costs += floatval( $addon['price'] ) ;
				}
			}
		 	$booking_cost= ($booking_cost == '')?$this->display_cost : $booking_cost;
			$total = $booking_cost + $addon_costs;
			return $total;
		}
		else{
			return $booking_cost;
		}
		
	}

}
new WC_Product_phive_booking_addon_integration();