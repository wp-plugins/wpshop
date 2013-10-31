jQuery(document).ready(function() {
	jQuery( document ).on( 'click', 'input[name=wps_login_request]', function() {
			/** Ajax Form Address Save **/
			jQuery('#wps_login_form').ajaxSubmit({
				dataType:  'json',
				beforeSubmit : function() {
						jQuery('#login_loader').show();
				},
		        success: function( response ) {
		        	if ( response[0] ) {
		        		window.location.replace( response[1] );
		        		jQuery('#login_loader').hide();
		        	}
		        	else {
		        		jQuery( '#wps_address_error_container' ).html( response[1] );
		        		jQuery('#login_loader').hide();
		        	}
	
		        },
			});	
		});
});