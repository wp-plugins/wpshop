jQuery( document ).ready( function() {
	jQuery( '#wps-hourly-orders-loader' ).hide();
	jQuery(document).on( 'click', '.wps_day_button', function() {
		jQuery( '#wps-hourly-orders-loader' ).show();
		var id = jQuery( this ).attr( 'id' );
		var data = {
				action: "wps_hourly_order_day",
				day : id,
				date_begin : jQuery( '#wps_statistics_begin_date' ).val(),
				date_end : jQuery( '#wps_statistics_end_date' ).val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery( '#inside_wps_hourly_orders_canvas' ).html( response['response'] );
					jQuery( '#wps-hourly-orders-loader' ).hide();
				}
				else {
					jQuery( '#wps-hourly-orders-loader' ).hide();
				}
				
			}, 'json');
	});
});