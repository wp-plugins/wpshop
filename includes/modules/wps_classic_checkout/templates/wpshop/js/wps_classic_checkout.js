jQuery( document ).ready( function() {
	jQuery( document ).on('click', '#wps_classic_checkout_finish_step_2', function() {
		jQuery('#wps_classic_checkout_error_alert_container').html('');
		if ( jQuery('input[name=wps_shipping_method_choice]:checked').length > 0 ) {
			jQuery('#wps_classic_checkout_step_two_loader').show();
			var data = {
					action: "wps_control_validity_step_two", 
					shipping_address_id : jQuery('input[name=wps_address_shipping_address]:checked').val(),
					billing_address_id : jQuery('input[name=wps_address_billing_address]:checked').val()
				};
				jQuery.post(ajaxurl, data, function(response){
					if(response['status']) {
						window.location.replace( response['response'] );
					}
					else {
						jQuery('#wps_classic_checkout_error_alert_container').html( response['response'] );
						jQuery('#wps_classic_checkout_error_alert_container').show();
						jQuery('#wps_classic_checkout_step_two_loader').hide();
					}
				}, 'json');
		}
		else {
			jQuery('#wps_classic_checkout_error_alert_container').html( 'You must choose a shipping method' );
			jQuery('#wps_classic_checkout_error_alert_container').show();
		}
	});
	
	
	jQuery( document ).on('click', '#wps_classic_checkout_take_order', function() {
		jQuery( '#wps_classic_checkout_finish_order_loader').show();
		jQuery( '#wps_classic_checkout_error_alert_container').fadeOut();
		var data = {
				action: "wps_classic_ckeckout_finish_order", 
				payment_method : jQuery( 'input[name=modeDePaiement]:checked').val()
			};
			jQuery.post(ajaxurl, data, function(response){
				if(response['status']) {
					jQuery('#wps_payment_method_container').html( response['response'] );
				}
				else {
					jQuery( '#wps_classic_checkout_error_alert_container').html( response['response'] );
					jQuery( '#wps_classic_checkout_error_alert_container').fadeIn('slow');
					jQuery( '#wps_classic_checkout_finish_order_loader').hide();	
				}
		}, 'json');
	});
});