<?php
	$ph_calendar_color 			= get_option('ph_booking_settings_calendar_color') ;
	$ph_calendar_month_color 	= $ph_calendar_color['ph_calendar_month_color'] ;
	$booking_full_color 		= $ph_calendar_color['booking_full_color'];
	$selected_date_color 		= $ph_calendar_color['selected_date_color'];
	$booking_info_wraper_color 	= $ph_calendar_color['booking_info_wraper_color'];
	$ph_calendar_weekdays_color = $ph_calendar_color['ph_calendar_weekdays_color'];
	$ph_calendar_days_color 	= $ph_calendar_color['ph_calendar_days_color'];

	$ph_calendar_design=isset($ph_calendar_color['ph_calendar_design'])?$ph_calendar_color['ph_calendar_design']:'';

	?>
<style type="text/css">
	
	<?php if($ph_calendar_design==1 || empty($ph_calendar_month_color)){?>
		.single-product div.product form.cart
		{
			background-color: #1791ce !important;
		}
		.single-product div.product form.cart
		{
			background-color: #1791ce !important;
		}
		.booking-info-wraper{
			background: #ffffff !important;  
		}
		.selected-date, .timepicker-selected-date, li.ph-calendar-date.mouse_hover, .time-picker-wraper #ph-calendar-time li.ph-calendar-date , li.ph-calendar-date.today:hover, .ph-calendar-date.today{
		    border: 0px solid transparent;
		}

		.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
		    border: 1px solid #ffffff;
		}
		li.ph-calendar-date.mouse_hover, li.ph-calendar-date.today:hover, li.ph-calendar-date:hover{
		  background: #4fb5e9;
		}
		.ph-next:hover, .ph-prev:hover{
		  color: #4d8e7a ;
		}
		li.ph-calendar-date.de-active.booking-full:hover, .ph-calendar-date.booking-full {
		  background-color: #dadada;
		  cursor: text;
		}
		.ph_bookings_book_now_button,.ph_bookings_book_now_button:hover{
		  background-color: #1373a3 !important;
		    border: 1px #1373a3 !important;
		}
		.ph_bookings_book_now_button:before {
		  background: #2098D1;
		}
		<?php 
		} elseif ($ph_calendar_design==2) {?>
		.single-product div.product form.cart
		{
			background-color: #a5a5a5 !important
		}
		.single-product div.product form.cart
		{
			background-color: #a5a5a5 !important
		}
		.booking-info-wraper{
			background: #ffffff !important;  
		}
		.selected-date, .timepicker-selected-date, li.ph-calendar-date.mouse_hover, .time-picker-wraper #ph-calendar-time li.ph-calendar-date , li.ph-calendar-date.today:hover, .ph-calendar-date.today{
		    border: 0px solid transparent;
		}

		.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
		    border: 1px solid #ffffff;
		}

		li.ph-calendar-date.mouse_hover, li.ph-calendar-date.today:hover, li.ph-calendar-date:hover, .timepicker-selected-date, .selected-date, li.ph-calendar-date.today.timepicker-selected-date{
			background: #f4fafd;
    		color: #131515 !important;
		}
		.ph-next:hover, .ph-prev:hover{
			color: #e9e6e6;
		}
		li.ph-calendar-date.de-active.booking-full:hover, .ph-calendar-date.booking-full {
		  background-color: #dadada;
		  cursor: text;
		}
		.ph_bookings_book_now_button,.ph_bookings_book_now_button:hover{
		  background-color: #5f5858 !important;
		    border: 1px #5f5858 !important;
		}
		.ph_bookings_book_now_button:before {
		  background: #2b2828;
		}
		<?php 
		} elseif ($ph_calendar_design==3) {?>
		.single-product div.product form.cart
		{
			background-color: #ff005e !important;
			background-image: linear-gradient(135deg, #362dc7, #00b8ff);
		}
		.booking-info-wraper{
			background: #ffffff !important;  
		}
		.selected-date, .timepicker-selected-date, li.ph-calendar-date.mouse_hover, .time-picker-wraper #ph-calendar-time li.ph-calendar-date , li.ph-calendar-date.today:hover, .ph-calendar-date.today{
		    border: 0px solid transparent;
		}

		.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
		    border: 1px solid #ffffff;
		}

		li.ph-calendar-date.mouse_hover, li.ph-calendar-date.today:hover, li.ph-calendar-date:hover, .timepicker-selected-date, .selected-date, li.ph-calendar-date.today.timepicker-selected-date{
			background: #f4fafd;
    		color: #131515 !important;
		}
		.ph-next:hover, .ph-prev:hover{
			color: #806c6c;
		}
		li.ph-calendar-date.de-active.booking-full:hover, .ph-calendar-date.booking-full {
		  background-color: #8fa2f5;
		  cursor: text;
		}
		.ph_bookings_book_now_button,.ph_bookings_book_now_button:hover{
		  background-color: #085c86 !important;
		    border: 1px #085c86 !important;
		}
		.ph_bookings_book_now_button:before {
		  background: #052433;
		}
		<?php 
		} elseif ($ph_calendar_design==4) {?>
		.single-product div.product form.cart
		{
			background-color: #ff005e !important;
			background-image: linear-gradient(135deg, #f30a0a, #131111cf,#1000fd)
		}
		.booking-info-wraper{
			background: #ffffff !important; 
		}
		.selected-date, .timepicker-selected-date, li.ph-calendar-date.mouse_hover, .time-picker-wraper #ph-calendar-time li.ph-calendar-date , li.ph-calendar-date.today:hover, .ph-calendar-date.today{
		    border: 0px solid transparent;
		}

		.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
		    border: 1px solid #ffffff;
		}

		li.ph-calendar-date.mouse_hover, li.ph-calendar-date.today:hover, li.ph-calendar-date:hover, .timepicker-selected-date, .selected-date, li.ph-calendar-date.today.timepicker-selected-date{
			background: #f4fafd;
    		color: #131515 !important;
		}
		.ph-next:hover, .ph-prev:hover{
			color: #e9e6e6;
		}
		li.ph-calendar-date.de-active.booking-full:hover, .ph-calendar-date.booking-full {
		  background-color: #ff3406;
		  cursor: text;
		}
		.ph_bookings_book_now_button,.ph_bookings_book_now_button:hover{
		  	background-color: #a93837 !important;
		    border: 1px #a93837 !important;
		}
		.ph_bookings_book_now_button:before {
		  background: #ad0000;
		}
		<?php 
		} elseif ($ph_calendar_design==5) {?>
		.single-product div.product form.cart
		{
			background-color: #ff005e !important;
			background-image: radial-gradient(#271be0, #7725c1,#2700fd);
		}
		.booking-info-wraper{
			background: #ffffff !important; 
		}
		.selected-date, .timepicker-selected-date, li.ph-calendar-date.mouse_hover, .time-picker-wraper #ph-calendar-time li.ph-calendar-date , li.ph-calendar-date.today:hover, .ph-calendar-date.today{
		    border: 0px solid transparent;
		}

		.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
		    border: 1px solid #ffffff;
		}

		li.ph-calendar-date.mouse_hover, li.ph-calendar-date.today:hover, li.ph-calendar-date:hover, .timepicker-selected-date, .selected-date, li.ph-calendar-date.today.timepicker-selected-date{
			background: #f4fafd;
    		color: #131515 !important;
		}
		.ph-next:hover, .ph-prev:hover{
			color: #e9e6e6;
		}

		li.ph-calendar-date.de-active.booking-full:hover, .ph-calendar-date.booking-full {
		  background-color: #a7a8ec;
		  cursor: text;
		}
		.ph_bookings_book_now_button,.ph_bookings_book_now_button:hover{
		  	background-color: #9149ff !important;
		    border: 1px #9149ff !important;
		}
		.ph_bookings_book_now_button:before {
		  background: #3a1071;
		}
		<?php }
		else{?>
			.ph-calendar-month{
					background: <?php echo $ph_calendar_month_color ?> !important;
				}
				.booking-full{
					background: <?php echo $booking_full_color ?> !important;
				}
				.timepicker-selected-date, .selected-date{
					background: <?php echo $selected_date_color ?> !important;
				}
				.booking-info-wraper{
					background: <?php echo $booking_info_wraper_color ?> !important;
				}
				.ph-calendar-weekdays{
					background: <?php echo $ph_calendar_weekdays_color ?> !important;
				}
				.ph-calendar-days{
					background: <?php echo $ph_calendar_days_color ?> !important;
				}
		<?php }?>

</style>

<div class="month-picker-wraper">
	<input type="hidden" id="book_interval_type" value="<?php echo $product->get_interval_type()?>">
	<input type="hidden" id="book_interval" value="<?php echo $product->get_interval()?>">

	
	<div class="ph-calendar-month">	
		<ul>
			<li><?php _e('Pick Month(s)','bookings-and-appointments-for-woocommerce')?></li>
		</ul>
	</div>

	<ul class="ph-calendar-days">	<?php
		$start_date = date('Y-m');
		// End date
		echo $this->phive_generate_month_for_period($start_date, '');
		?>
	</ul>
</div>

<div class="booking-info-wraper">
	<div class="callender-error-msg"><?php _e('Please pick a booking period', 'bookings-and-appointments-for-woocommerce')?></div>
	<p id="booking_info_text"> </p>
	<p id="booking_price_text"> </p>
</div>
