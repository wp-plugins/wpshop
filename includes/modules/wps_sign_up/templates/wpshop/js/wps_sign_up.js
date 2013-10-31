jQuery(document).ready(function() {
	jQuery('#wps_signup_account_creation_additional_fields').hide();
	
	jQuery( document ).on( 'click', 'input[name=wps_sign_up_request]', function() {
		/** Ajax Form Address Save **/
		jQuery('#wps_sign_up_form').ajaxSubmit({
			dataType:  'json',
			beforeSubmit : function() {
					jQuery('#signup_loader').show();
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		window.location.replace( response[1] );
	        	}
	        	else {
	        		jQuery( '#wps_address_error_container' ).html( response[1] );
	        		jQuery('#signup_loader').hide();
	        	}

	        },
		});	
	});
	
	
	
	jQuery( document ).on('click', '#wps_signup_account_creation', function() {
		if ( jQuery(this).is(':checked') ){
			jQuery('#wps_signup_account_creation_additional_fields').slideDown();
		}
		else {
			jQuery('#wps_signup_account_creation_additional_fields').slideUp();
		}
	});
	
	jQuery( document ).on('click', '#display_connexion_form', function() {
		var data = {
				action: "wps_display_connexion_form",
			};
			jQuery.post(ajaxurl, data, function(response){
				if(response['status']) {
					jQuery( '#wps_address_error_container' ).html('');
					jQuery('#wps_form_content').fadeOut('slow', function(){
						jQuery('#wps_form_content').html( response['response'] );
						jQuery('#wps_form_content').fadeIn('slow');
					});		
				}
			}, 'json');
	});
	
	jQuery( document ).on('click', '#display_sign_up_form', function() {
		var data = {
				action: "wps_display_sign_up_form",
			};
			jQuery.post(ajaxurl, data, function(response){
				if(response['status']) {
						jQuery( '#wps_address_error_container' ).html('');
						jQuery('#wps_form_content').fadeOut('slow', function(){
						jQuery('#wps_form_content').html( response['response'] );
						jQuery('#wps_form_content').fadeIn('slow');
					});		
				}
			}, 'json');
	});
	
	
	
	
});
