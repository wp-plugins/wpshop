jQuery( document ).ready( function() {
	jQuery( '#wps-hourly-orders-loader' ).hide();
	jQuery(document).on( 'change', '#wps-statistics-orders-moment-selectbox', function() {
		jQuery( '#wps-hourly-orders-loader' ).show();
		var id = jQuery( this ).val();
		var data = {
				action: "wps_hourly_order_day",
				day : id,
				date_begin : jQuery( '#wps_statistics_begin_date' ).val(),
				date_end : jQuery( '#wps_statistics_end_date' ).val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery( '#wps-orders-moment-statistics .inside' ).html( response['response'] );
					//jQuery( '#wps-hourly-orders-loader' ).hide();
				}
				else {
					jQuery( '#wps-hourly-orders-loader' ).hide();
				}
				
			}, 'json');
	});
});