jQuery(document).ready(function () {
    jQuery('.adtm-alert').on('click', '.closebtn', function () {
        jQuery(this).closest('.adtm-alert').fadeOut(); //.css('display', 'none');
    });

    jQuery('.adtm-boost-label input').on('click', function() { 
        jQuery('.adtm-boost').slideToggle();
    });

    jQuery('.adtm-bialty-label input').on('click', function() { 
        jQuery('.adtm-bialty').slideToggle();
    });

    jQuery('.adtm-mobi-label input').on('click', function() { 
        jQuery('.adtm-mobi').slideToggle();
    });

    
    jQuery('.adtm-tabs ul.tabs li').click(function(){
		var tab_id = jQuery(this).attr('data-tab');

		jQuery('ul.tabs li').removeClass('current');
		jQuery('.tab-content').removeClass('current');

		jQuery(this).addClass('current');
		jQuery("#"+tab_id).addClass('current');
    });
    

    jQuery('#app_ads_repeater').isiaFormRepeater({
        addButton: '<div class="repeat-add-wrapper"><a data-repeat-add-btn class="adtm-btn repeat-add" href="#" style="display: inline-block">Add</a></div>',
        removeButton: '<a data-repeat-remove-btn class="adtm-btn repeat-remove" href="#">Remove</a>'
    });

    jQuery('#ads_txt_repeater').isiaFormRepeater({
        addButton: '<div class="repeat-add-wrapper"><a data-repeat-add-btn class="adtm-btn repeat-add" href="#" style="display: inline-block">Add</a></div>',
        removeButton: '<a data-repeat-remove-btn class="adtm-btn repeat-remove" href="#">Remove</a>'
    });

    jQuery("#fs_connect button[type=submit]").on("click", function(e) {
        window.open('https://better-robots.com/subscribe.php?plugin=app-ads','app-ads','resizable,height=400,width=700');
    });

});