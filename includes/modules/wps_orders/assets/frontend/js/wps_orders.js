jQuery( document ).ready( function() {
	/** Open order details */
	jQuery( document ).on( 'click', '.wps-orders-details-opener', function() {
		var order_id = jQuery( this ).attr( 'id' ).replace( 'wps-order-details-opener-','');
		jQuery( this ).addClass( 'wps-bton-loading' );
		var data = {
				action: "wps_orders_load_details",
				order_id : order_id
			};
			jQuery.post(ajaxurl, data, function(response) {
					if( response['status'] ) {
						fill_the_modal( response['title'], response['content'], '' );
						jQuery( '#wps-order-details-opener-' + order_id ).removeClass( 'wps-bton-loading' );
					}
					else {
						jQuery( '#wps-order-details-opener-' + order_id ).removeClass( 'wps-bton-loading' );
					}

			}, 'json');
	});
	/** Delete order */
	jQuery(document).on('click', '.wps-orders-delete', function() {
		var element = jQuery(this);
		var order_id = jQuery(this).data('id');
		jQuery( this ).addClass( 'wps-bton-loading' );
		var data = {
			action: "wps_delete_order",
			order_id: order_id,
		};
		jQuery.post(ajaxurl, data, function() {
			element.removeClass('wps-bton-loading');
			element.closest('.wps-table-row').fadeOut();
		});
	});
});