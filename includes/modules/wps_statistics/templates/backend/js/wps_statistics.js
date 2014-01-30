jQuery(document).ready(function() {
    jQuery('.date').datepicker({
    	dateFormat : 'yy-mm-dd'
    });
    
    jQuery( '#wps_statistics_loader' ).hide();
    jQuery( document ).on( 'click', '#wps_change_statistics_date', function() {
    	jQuery( '#wps_statistics_loader' ).show();
    	var data = {
				action: "wps_reload_statistics", 
				date_begin : jQuery( '#wps_statistics_begin_date' ).val(),
				date_end : jQuery( '#wps_statistics_end_date' ).val()
			};
			jQuery.post(ajaxurl, data, function(response){
				if ( response["status"] )  {
					jQuery( '#wps_statistics_container').slideUp('slow', function() {
						jQuery( '#wps_statistics_container').html( response['response'] );
						jQuery( '#wps_statistics_container').slideDown();
						jQuery( '#wps_statistics_loader' ).hide();
					});
					
				}
				else {
					alert( response['reponse'] );
				}
		}, "json");
    });
    
    
});