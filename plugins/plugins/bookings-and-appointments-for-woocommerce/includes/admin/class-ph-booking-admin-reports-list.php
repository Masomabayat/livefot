<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include('market.php');

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class phive_booking_all_list extends WP_List_Table {
	protected $max_items;
	
	public function __construct() {
		parent::__construct( array() );
	}

	
	/**
	* Set the colomn titles
	*/
	public function get_columns() {

		$columns = array(
			'order_id'			=> esc_html( __( 'Order', 'bookings-and-appointments-for-woocommerce' ) ),
			'product'			=> esc_html( __( 'Product', 'bookings-and-appointments-for-woocommerce' ) ),
			'start_date'		=> esc_html( __( 'From', 'bookings-and-appointments-for-woocommerce' ) ),
			'end_date'			=> esc_html( __( 'To', 'bookings-and-appointments-for-woocommerce' ) ),
			'bookedby'			=> esc_html( __( 'Booked by', 'bookings-and-appointments-for-woocommerce' ) ),
		);

		return $columns;
	}

	/**
	* Set sortable columns
	*/
	protected function get_sortable_columns() {

		$sortable_columns = array(
			'order_id'		=> array( 'order_id', true ),
			'product'		=> array( 'product', true ),
			'start_date'	=> array( 'start_date', false ),
			'end_date'		=> array( 'end_date', false ),
			'bookedby'		=> array( 'bookedby', false )
		);

		return $sortable_columns;
	}

	/**
	* Prapare the table content
	*/
	public function prepare_items() {
		
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$current_page		  = absint( $this->get_pagenum() );
		$per_page			  = 20;

		$this->get_items( $current_page, $per_page );

		/**
		 * Pagination
		 */
		$this->set_pagination_args( array(
			'total_items' => $this->max_items,
			'per_page'	=> $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		) );
		
	}

	
	private function is_confrimed_all_bookings_of_order($order){

		$is_confrimed = true;

		$items 		= $order->get_items();

		foreach ($items as $order_item_id => $line_item) {

			$_product = wc_get_product( $line_item->get_product_id() );
			
			if( empty($_product) ){
				continue;
			}

			$required_confirmation 	= get_post_meta( $_product->get_id(), "_phive_book_required_confirmation", 1 );
			
			if( $required_confirmation == 'yes' && $line_item->get_meta('confirmed') != 'yes' ){
				$is_confrimed = false;
				break;
			}
		}
		return $is_confrimed;
	}

	/**
	* Disply the table content
	*/
	public function get_items( $current_page, $per_page ) {
		
		$filters = array(
			'ph_booking_status' 		=> isset( $_GET['ph_booking_status'] ) ? $_GET['ph_booking_status'] : '',
			'ph_filter_product_ids' 	=> isset( $_GET['ph_filter_product_ids']) ? $_GET['ph_filter_product_ids'] : '',
			'ph_filter_from' 			=> isset( $_GET['ph_filter_from']) ? $_GET['ph_filter_from'] : '',
			'ph_filter_to' 				=> isset( $_GET['ph_filter_to']) ? $_GET['ph_filter_to'] : '',
		);
		$this->max_items 	= $this->ph_get_bookings_count( $filters, $current_page, $per_page );
		$this->items 		= $this->ph_get_bookings_for_current_page( $filters, $current_page, $per_page );
		
		return;
	}

	/**
	* Get count of all items
	*
	*/
	private function ph_get_bookings_count( $filters, $current_page, $per_page ){
		
		global $wpdb;

		$query = "SELECT count(*)
		FROM {$wpdb->prefix}posts
		INNER JOIN {$wpdb->prefix}postmeta ometa on ometa.post_id = {$wpdb->prefix}posts.ID
		INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}posts.ID
		INNER JOIN (
				SELECT 
				order_item_id,
				MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
				MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
				MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
				MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END
			) AS BookTo,
			MAX(CASE WHEN meta_key = 'Booked To' THEN meta_value ELSE '' END
			) AS BookedTo,
			MAX(CASE WHEN meta_key = 'Booked From' THEN meta_value ELSE '' END
			) AS BookedFrom
		FROM {$wpdb->prefix}woocommerce_order_itemmeta 
		GROUP BY order_item_id) as imeta on  imeta.order_item_id = oitems.order_item_id
		INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId 
		INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
		INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
		WHERE {$wpdb->prefix}posts.post_type IN ('shop_order', 'shop_order_refund')
		AND (
			{$wpdb->prefix}posts.post_status = 'wc-pending'
			OR {$wpdb->prefix}posts.post_status = 'wc-processing'
			OR {$wpdb->prefix}posts.post_status = 'wc-on-hold'
			OR {$wpdb->prefix}posts.post_status = 'wc-completed'
			OR {$wpdb->prefix}posts.post_status = 'wc-cancelled'
			OR {$wpdb->prefix}posts.post_status = 'wc-refunded'
			OR {$wpdb->prefix}posts.post_status = 'wc-failed'
		) 
		AND ometa.meta_key in ('_billing_first_name')
		AND tt.taxonomy IN ('product_type')
		AND t.slug = 'phive_booking'";

		if( !empty($filters['ph_booking_status']) ){
			$query .= "AND imeta.BookingStatus = '".$filters['ph_booking_status']."'";
		}
		if( !empty($filters['ph_filter_product_ids']) ){
			$query .= "AND imeta.ProductId = '".$filters['ph_filter_product_ids']."'";
		}
		if( !empty($filters['ph_filter_from']) ){
			$query .= " AND (DATE(imeta.BookFrom) >= '".$filters['ph_filter_from']."'";
			$query .= " OR DATE(imeta.BookedFrom) >= '".$filters['ph_filter_from']."')";
		}elseif( !empty($filters['ph_filter_to']) ){
			$query .= " AND( DATE(imeta.BookFrom) <= '".$filters['ph_filter_to']."'";
			$query .= " OR DATE(imeta.BookedFrom) <= '".$filters['ph_filter_to']."')";
		}

		$bookings_count = $wpdb->get_var( $query );
		return $bookings_count;
	}

	private function ph_get_bookings_for_current_page( $filters, $current_page, $per_page ){

		global $wpdb;
		
		$query = "SELECT oitems.order_id, oitems.order_item_id,tr.object_id product_id,ometa.meta_value customer_name, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo,imeta.BookedTo
		FROM {$wpdb->prefix}posts
		INNER JOIN {$wpdb->prefix}postmeta ometa on ometa.post_id = {$wpdb->prefix}posts.ID
		INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}posts.ID
		INNER JOIN (
				SELECT 
				order_item_id,
				MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
				MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
				MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
				MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END
			) AS BookTo,
			MAX(CASE WHEN meta_key = 'Booked To' THEN meta_value ELSE '' END
			) AS BookedTo,
			MAX(CASE WHEN meta_key = 'Booked From' THEN meta_value ELSE '' END
			) AS BookedFrom
		FROM {$wpdb->prefix}woocommerce_order_itemmeta 
		GROUP BY order_item_id) as imeta on  imeta.order_item_id = oitems.order_item_id
		INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId 
		INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
		INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
		WHERE {$wpdb->prefix}posts.post_type IN ('shop_order', 'shop_order_refund')
		AND (
			{$wpdb->prefix}posts.post_status = 'wc-pending'
			OR {$wpdb->prefix}posts.post_status = 'wc-processing'
			OR {$wpdb->prefix}posts.post_status = 'wc-on-hold'
			OR {$wpdb->prefix}posts.post_status = 'wc-completed'
			OR {$wpdb->prefix}posts.post_status = 'wc-cancelled'
			OR {$wpdb->prefix}posts.post_status = 'wc-refunded'
			OR {$wpdb->prefix}posts.post_status = 'wc-failed'
		) 
		AND ometa.meta_key in ('_billing_first_name')
		AND tt.taxonomy IN ('product_type')
		AND t.slug = 'phive_booking'";

		if( !empty($filters['ph_booking_status']) ){
			$query .= " AND imeta.BookingStatus = '".$filters['ph_booking_status']."'";
		}
		if( !empty($filters['ph_filter_product_ids']) ){
			$query .= " AND imeta.ProductId = '".$filters['ph_filter_product_ids']."'";
		}
		if( !empty($filters['ph_filter_from']) ){
			$query .= " AND (DATE(imeta.BookFrom) >= '".$filters['ph_filter_from']."'";
			$query .= " OR DATE(imeta.BookedFrom) >= '".$filters['ph_filter_from']."')";
		}elseif( !empty($filters['ph_filter_to']) ){
			$query .= " AND( DATE(imeta.BookFrom) <= '".$filters['ph_filter_to']."'";
			$query .= " OR DATE(imeta.BookedFrom) <= '".$filters['ph_filter_to']."')";
		}

		$sortby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'order_id';
		switch ( $sortby ) {
			case 'order_status':
				$orderby = 'imeta.BookingStatus';
				break;
			
			case 'product':
				$orderby = 'imeta.ProductId';
				break;
			
			case 'start_date':
				$orderby = '(imeta.BookFrom)';
				
				break;
			
			case 'end_date':
				$orderby = '(imeta.BookTo)';
				
				break;

			case 'bookedby':
				$orderby = 'ometa.meta_value';
				break;
			
			case 'order_id':
			default:
				$orderby = 'order_id';
				break;
		}

		$order = !empty($_GET['order']) ? $_GET['order'] : 'DESC';
		$query .=" ORDER BY $orderby ".$order;

		$start_limit 	= ($current_page-1) * $per_page;
		$query .=" LIMIT $start_limit, $per_page";
		$results = $wpdb->get_results( $query );

		error_log(print_r("query : ".$query,1));

		$bookings = array();
		
		foreach ($results as $key => $result) {
			// if(empty($result->BookedTo) ){
			$BookTo = maybe_unserialize($result->BookTo);
			$BookTo = (is_array($BookTo))?$BookTo[0]:$BookTo;
			// }
			// else{
			// 	$BookTo = $result->BookedTo;
			// }
			$BookFrom = maybe_unserialize($result->BookFrom);
			$BookFrom = (is_array($BookFrom))?$BookFrom[0]:$BookFrom;
			$bookings[] = array(
				'ID' 			=> $result->order_item_id,
				'order_id' 		=> $result->order_id,
				'product_id' 	=> $result->product_id,
				'start' 		=> $BookFrom,
				'end' 			=> $BookTo,
				'bookedby' 		=> $result->customer_name,
				'booking_status'=> $result->BookingStatus,
			);
		}
		return $bookings;
	}

	/**
	* Check if the given time in between the given time interval.
	* @param $checkme: the time to check
	* @param $lower_range: The min range 
	* @param $heigher_range: The max range 
	* @return Bool
	*/
	/*private function is_time_in_between( $checkme, $lower_range, $heigher_range ){
		$return = true;
		
		if( !empty($lower_range) && $checkme < $lower_range ){
			$return = false;
		}

		if( !empty($heigher_range) && $checkme >= $heigher_range ){
			$return = false;
		}
		
		return $return;
	}*/


	/**
	* Display filter html and pagination html
	*
	*/
	protected function display_tablenav( $which ) {

		$ph_filter_product_ids 	= isset( $_GET['ph_filter_product_ids']) ? $_GET['ph_filter_product_ids'] : '';
		$ph_filter_from 		= isset( $_GET['ph_filter_from']) ? $_GET['ph_filter_from'] : '';
		$ph_filter_to 			= isset( $_GET['ph_filter_to']) ? $_GET['ph_filter_to'] : '';

		if ( ! empty( $filter_id ) ) {
			$_product = wc_get_product( $filter_id );
		}

		include( 'views/html-ph-booking-admin-reports-list-filters.php' );
	}

	/**
	* Content of each columns.
	*/
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'order_id' :

				if ( ! empty( $item['order_id'] ) ) {
					$order = wc_get_order( $item['order_id'] );
				} else {
					$order = false;
				}
				
				if ( $order ) {
					echo '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $order->get_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $order->get_order_number() ) . '</strong></a>';
				}
			break;

			case 'start_date' :
				//If booking time is there.
				if( strlen($item['start']) > 10 ){
					echo date_i18n( get_option( 'date_format' )." ".get_option( 'time_format' ), strtotime( $item['start'] ) );
				}else{
					echo date_i18n( get_option( 'date_format' ), strtotime( $item['start'] ) );
				}
			break;

			case 'end_date' :
				if ( !empty( $item['end'] ) ) {
					//If booking time is there.
					if( strlen($item['end']) > 10 ){
						echo date_i18n( get_option( 'date_format' )." ".get_option( 'time_format' ), strtotime( $item['end'] ) );
					}else{
						echo date_i18n( get_option( 'date_format' ), strtotime( $item['end'] ) );
					}
				}
			break;

			case 'product' :
				$product = wc_get_product( $item['product_id'] );
				if ( ! $product ) {
					return;
				}

				$product_name = $product->get_formatted_name();
				echo wp_kses_post( $product_name );
			break;

			default :
				echo isset( $item[$column_name] ) ? esc_html( $item[$column_name] ) : '';

			break;
		}
	}

	/**
	* Reorder the entries based on choosed colomn.
	*
	*/
	function order_bookings( $a, $b ) {

		// Default sort by order id
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'order_id';

		// Default sorting order
		$sorting_order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';

		switch ( $orderby ) {

			case 'order_id' :

				if ( isset( $a['order_id'] ) && isset( $b['order_id'] ) ) {
					$result = ( $a['order_id'] > $b['order_id'] ) ? -1 : 1;
				} else {
					$result = -1;
				}

			break;

			case 'order_status' :
				$result = ( $a['order_status'] > $b['order_status'] ) ? -1 : 1;
			break;

			case 'product' :
				$result = ( $a['product_id'] > $b['product_id'] ) ? -1 : 1;
			break;

			case 'start_date' :
				$a_start_date = strtotime( $a['start'] );
				$b_start_date = strtotime( $b['start'] );

				$result = ( $a_start_date > $b_start_date ) ? -1 : 1;
			break;

			case 'end_date' :
				$a_end_date = strtotime( $a['end'] );
				$b_end_date = strtotime( $b['end'] );

				$result = ( $a_end_date > $b_end_date ) ? -1 : 1;
			break;

			case 'bookedby' :
				$result = ( $a['bookedby'] > $b['bookedby'] ) ? -1 : 1;
			break;

			default:
				// do nothing
			break;

		}
		
		// Send final sort direction to usort
		return ( $sorting_order === 'asc' ) ? $result : -$result;
	}

}