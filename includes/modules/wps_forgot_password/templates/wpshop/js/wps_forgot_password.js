jQuery( document ).ready(function() {
	jQuery( document ).on('click', '#wps_send_forgot_password_request', function() {
		jQuery('#wps_forgot_password_form').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery('#wps_request_password_loader').show();
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		jQuery('#wps_request_password_loader').hide();
	        		jQuery('#wps_forgot_password_alert_container').html( response[1] );
	        	}
	        	else {
	        		jQuery('#wps_forgot_password_alert_container').html( response[1] );
	        		jQuery('#wps_request_password_loader').hide();
	        	}
	        },
		}).submit();
	});
	
	jQuery( document ).on('click', '#wps_send_forgot_password_renew', function() {
		jQuery('#wps_forgot_password_form_renew').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery('#wps_renew_password_loader').show();
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		jQuery('#wps_renew_password_loader').hide();
	        		jQuery('#wps_forgot_password_alert_container').html( response[1] );
	        	}
	        	else {
	        		jQuery('#wps_forgot_password_alert_container').html( response[1] );
	        		jQuery('#wps_renew_password_loader').hide();
	        	}
	        },
		}).submit();
	});
	
	jQuery( document ).on( 'click', '#forgot_password_interface_opener', function() {
		var data = {
				action: "get_forgot_password_form"
			};
			jQuery.post(ajaxurl, data, function(response){
				jQuery( '#wps_address_error_container' ).html('');
				jQuery('#wps_form_content').fadeOut('slow', function(){
					jQuery('#wps_form_content').html( response[0] );
					jQuery('#wps_form_content').fadeIn('slow');
				});		
		}, 'json');
		
	});
	
	
});