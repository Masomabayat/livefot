<style>
	i{
		font-size: 11px;
	}
	
</style>
<div id='booking_options' class='panel woocommerce_options_panel'><?php
	$interval_period 	= get_post_meta( $post->ID, '_phive_book_interval_period', 1);
	$interval_type 		= get_post_meta( $post->ID, '_phive_book_interval_type', 1);
	$interval 			= get_post_meta( $post->ID, '_phive_book_interval', 1);
	$price 				= get_post_meta( $post->ID, '_phive_book_price', 1);
	$opening_time		= get_post_meta( $post->ID, '_phive_book_working_hour_start', 1);
	$closing_tme		= get_post_meta( $post->ID, '_phive_book_working_hour_end', 1);
	?>
	<p class="form-field" style="line-height: 15px;">
		<label for="_phive_book_price" ><?php _e('Booking Period','bookings-and-appointments-for-woocommerce')?></label>
		<select id="_phive_book_interval_type" name="_phive_book_interval_type" class="input-item">
			<option value="fixed"<?php if($interval_type=='fixed')echo'selected="selected"'; ?> ><?php _e('Fixed period of','bookings-and-appointments-for-woocommerce')?></option>
			<option value="customer_choosen" <?php if($interval_type=='customer_choosen')echo'selected="selected"'; ?> ><?php _e('Enable Calendar Range','bookings-and-appointments-for-woocommerce')?></option>
		</select>
		
		<input type="number" onKeyPress="if(this.value.length==3) return false;" class="short input-item" style="width:50px;margin-left: 10px;" name="_phive_book_interval" id="_phive_book_interval" value="<?php echo $interval;?>" placeholder="1">
		<select id="_phive_book_interval_period" name="_phive_book_interval_period" class="select short input-item" style="width:85px;margin-left: 10px;" >
			<option value="minute" <?php if($interval_period=='minute')echo'selected="selected"'; ?> ><?php _e('Minutes(s)','bookings-and-appointments-for-woocommerce')?></option>
			<option value="hour" <?php if($interval_period=='hour')echo'selected="selected"'; ?>><?php _e('Hour(s)','bookings-and-appointments-for-woocommerce')?></option>
			<option value="day" <?php if($interval_period=='day'  || empty($interval_period))echo'selected="selected"'; ?>><?php _e('Day(s)','bookings-and-appointments-for-woocommerce')?></option>
			<!-- <option value="week" <?php //if($interval_period=='week')echo'selected="selected"'; ?>><?php // _e('Week(s','bookings-and-appointments-for-woocommerce')?>)</option> -->
			<option value="month" <?php if($interval_period=='month')echo'selected="selected"'; ?>><?php _e('Month(s)','bookings-and-appointments-for-woocommerce')?></option>
		</select>
		<br>
		<i id="ph_fixed_period">
			<?php echo
			 __( "Fixed Period : Allows users to select a single appointment with the duration you define here.", 'bookings-and-appointments-for-woocommerce' ) ;?>
			</i> 	
		
		<i id="ph_range_period">
			<?php echo
			 __( "Enable Range : Allows users to choose multiple days by selecting a start date and an end date.", 'bookings-and-appointments-for-woocommerce' ) ;?>
		</i> 	
	</p>
	
	<p class="form-field" >
		<label for="_phive_book_price" ><?php _e('Price','bookings-and-appointments-for-woocommerce')?></label>
		<input type="text" class="short input-item" style="width:70px" name="_phive_book_price" id="_phive_book_price" value="<?php echo $price;?>" placeholder=""><br>
		<i><?php
		echo  __("This price applies to one block of time. Gets multiplied if user chooses multiple days.",'bookings-and-appointments-for-woocommerce') ?>
		</i>
	</p>
	

	<?php

	woocommerce_wp_text_input( array(
		'id'			=> '_phive_book_working_hour_start',
		'label'			=> __( 'First booking of the day starts at', 'bookings-and-appointments-for-woocommerce' ),
		'value'			=> !empty($opening_time) ? $opening_time : '10:00',
		'type' 			=> 'time',
		'style'			=> "width: 120px",
	) );
	woocommerce_wp_text_input( array(
		'id'			=> '_phive_book_working_hour_end',
		'label'			=> __( 'Last booking of the day starts at', 'bookings-and-appointments-for-woocommerce' ),
		'value'			=> !empty($closing_tme) ? $closing_tme : '20:00',
		'type' 			=> 'time',
		'style'			=> "width: 120px;",

	) );
?>
</div>
