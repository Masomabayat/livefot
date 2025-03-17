jQuery(document).ready(function($) {
	function toggleInterval(){
		jQuery( '._tax_status_field' ).closest( '.show_if_simple' ).addClass( 'show_if_phive_booking' ).show();
		if( $("#_phive_book_interval_type").val() != 'fixed' && $("#_phive_book_interval_period").val() != 'minute' && $("#_phive_book_interval_period").val() != 'hour'){
			$("#_phive_book_interval").hide();
		}else{
			$("#_phive_book_interval").show();
		}
	}
	function toggleWorkingTime(){
		if( $("#_phive_book_interval_period").val() == 'minute' || $("#_phive_book_interval_period").val() == 'hour' ){
			$("._phive_book_working_hour_start_field").show();
			$("._phive_book_working_hour_end_field").show();
		}else{
			$("._phive_book_working_hour_start_field").hide();
			$("._phive_book_working_hour_end_field").hide();
		}
	}
	function toggleNonWorkingHours(){
		if( $("#_phive_book_interval_period").val() == 'minute' || $("#_phive_book_interval_period").val() == 'hour' ){
			$(".non-working-hours").show();
			$(".non-working-hours").show();
		}else{
			$(".non-working-hours").hide();
			$(".non-working-hours").hide();
		}
	}
	function toggleCallender(){
		if( $("#_phive_book_interval_type").val()=='customer_choosen' ){
			$("#_phive_book_interval_period").val("day").change();
			$("#_phive_book_interval").val(1).change();
			$("#_phive_book_interval_period").hide();
		}else{
			$("#_phive_book_interval_period").show();
		}
	}
	toggleInterval();
	toggleWorkingTime();
	toggleCallender();
	toggleNonWorkingHours()
	$("#_phive_book_interval_type").change(function(){
		toggleCallender()
		toggleInterval();
	});

	$("#_phive_book_interval_period").change(function(){
		toggleInterval()
		toggleWorkingTime();

		toggleNonWorkingHours();
	})
})