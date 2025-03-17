<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class phive_booking_ajax_manager{
	public function __construct() {
		add_action( 'wp_ajax_phive_get_callender_next_month', array($this,'phive_get_callender_next_month') );
		add_action( 'wp_ajax_nopriv_phive_get_callender_next_month', array($this,'phive_get_callender_next_month') );
		
		add_action( 'wp_ajax_phive_get_callender_prev_month', array($this,'phive_get_callender_prev_month') );
		add_action( 'wp_ajax_nopriv_phive_get_callender_prev_month', array($this,'phive_get_callender_prev_month') );

		add_action( 'wp_ajax_phive_get_booked_datas_of_date', array($this,'phive_callender_time_for_date') );
		add_action( 'wp_ajax_nopriv_phive_get_booked_datas_of_date', array($this,'phive_callender_time_for_date') );

		add_action( 'wp_ajax_phive_get_booked_price', array($this,'phive_get_booked_price') );
		add_action( 'wp_ajax_nopriv_phive_get_booked_price', array($this,'phive_get_booked_price') );
		// $this->phive_callender_time_for_date();
	}

	public function phive_get_booked_price(){
		global $woocommerce;

		$product_id = $_POST['product_id'];
		$from 		= wp_unslash( $_POST['book_from'] );
		$to 		= wp_unslash( $_POST['book_to'] );
		$addon_data = $_POST['addon_data'];

		$value[ $product_id ] = array(
			'book_from' => $from,
			'book_to'	=>$to,
			'addon_data'=> $addon_data,
		);
		WC()->customer->update_meta_data( 'phive_booking_details', $value );

		$prod_obj = new WC_Product_phive_booking( $product_id );
		$prod_obj->set_id($product_id);

		echo json_encode(
			array(
				'price_html' 	=> $prod_obj->get_price_html(),
				'price'			=> $prod_obj->get_price(),
				'from_date'		=> $this->phive_get_date_in_wp_format($from),
				'to_date'		=> $this->phive_get_date_in_wp_format($to),
			)
		);
		exit();
	}

	public static function phive_get_date_in_wp_format( $input_date, $input_format='' ){
		
		if( empty($input_date) ){
			return false;
		}

		if( empty($input_format) ){
			switch ( strlen($input_date) ) {

				case 7: //Month calendar
					$input_format 	= "Y-m";
					$output_format 	= "Y-F";
					break;

				case 10: //Day calendar
					$input_format 	= "Y-m-d";
					$output_format 	= get_option( 'date_format' );
					break;

				case 16: //Time picker
					$input_format 	= "Y-m-d H:i";
					$output_format 	= get_option( 'date_format' ).' '.get_option( 'time_format' );
					break;
				
				default:
					$input_format 	= "Y-m-d";
					$output_format 	= "Y-m-d";
					break;
			
			}
		}
		$output_date = DateTime::createFromFormat( $input_format, esc_attr( $input_date ) );
			return is_a( $output_date, 'DateTime' ) ? date_i18n( $output_format, strtotime($output_date->format( "F j, Y H:i:s" )) ) : $input_date;

	}

	public function phive_callender_time_for_date(){
		$zone=get_option('timezone_string');
		if(empty($zone)){
			$time_offset		= get_option('gmt_offset');
			$zone				= timezone_name_from_abbr( "", $time_offset*60*60, 0 );
		}
		date_default_timezone_set($zone);
		$product_id = $_POST['product_id'];
		$date = $_POST['date'];
		if( !class_exists('phive_booking_callender') ){
			include_once('booking-callender/class-ph-booking-callender.php');
		}
		
		$shop_opening_time = get_post_meta( $product_id, "_phive_book_working_hour_start", 1 );
		$shop_opening_time = !empty( $shop_opening_time ) ? date( 'H:i',strtotime($shop_opening_time) ) : '00:00';
		
		$start_date = $date.' '.$shop_opening_time;

		$callender = new phive_booking_callender();
		echo $callender->phive_generate_time_for_period( $start_date, '', $product_id );
		exit();
	}

	public function phive_get_callender_next_month(){
		$product_id 	= $_POST['product_id'];
		$month 			= $_POST['month'];
		$year 			= $_POST['year'];
		$calendar_for 	= $_POST['calendar_for'];
		if( !class_exists('phive_booking_callender') ){
			include_once('booking-callender/class-ph-booking-callender.php');
		}
		$callender = new phive_booking_callender();

	 	$start_date = date ( "Y-m-d", strtotime( "+1 month", strtotime("$year-$month-01") ) ) ;
		
		echo json_encode(
			array(
				'days' 		=> $callender->phive_generate_days_for_period( $start_date, '', $product_id ,$calendar_for),
				'month'		=> date( "F",strtotime($start_date) ),
				'year'		=> date( "Y",strtotime($start_date) ),
			)
		);
		exit();
	}
	public function phive_get_callender_prev_month(){
		$product_id 	= $_POST['product_id'];
		$month 			= $_POST['month'];
		$year 			= $_POST['year'];
		$calendar_for 	= $_POST['calendar_for'];
		if( !class_exists('phive_booking_callender') ){
			include_once('booking-callender/class-ph-booking-callender.php');
		}
		$callender = new phive_booking_callender();

	 	$start_date = date ( "Y-m-d", strtotime( "-1 month", strtotime("$year-$month-01") ) ) ;
		echo json_encode(
			array(
				'days' 		=> $callender->phive_generate_days_for_period( $start_date, '', $product_id,$calendar_for ),
				'month'		=> date( "F",strtotime($start_date) ),
				'year'		=> date( "Y",strtotime($start_date) ),
			)
		);
		exit();
	}
}
new phive_booking_ajax_manager();