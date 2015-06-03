jQuery(document).ready(function() {

	jQuery( '#wps_login_form_container' ).hide();
	jQuery( '#wps_signup_form_container' ).hide();

	jQuery( document ).on( 'click', '#wps_login_button', function() {
			jQuery( '#wps_login_button').addClass( 'wps-bton-loading' );
			/** Ajax Form Address Save **/
			jQuery('#wps_login_form').ajaxForm({
				dataType:  'json',
				beforeSubmit : function() {
						jQuery('#login_loader').show();
				},
		        success: function( response ) {
		        	if ( response[0] ) {
							// Special wishlist
							/*if(open_modal_wishlist)
								open_modal_wishlist();
							else*/
		        			window.location.replace( response[1] );
		        		jQuery('#login_loader').hide();
		        		jQuery( '#wps_login_button').removeClass( 'wps-bton-loading' );
		        	}
		        	else {
		        		jQuery( '#wps_login_error_container' ).hide();
		        		jQuery( '#wps_login_error_container' ).html( response[1] ).slideDown( 'slow' ).delay( 3000 ).slideUp( 'slow' );
		        		jQuery( '#wps_login_button').removeClass( 'wps-bton-loading' );
		        	}

		        },
			});
		});
	
	/** Quand on presse return on click sur wps_first_login_button */
	jQuery('#wps_login_first_email_address').keyup(function(event) {
		if(event.which == 13) {
			jQuery("#wps_first_login_button").click();
		}
	});
	
	jQuery( document ).on( 'click', '#wps_first_login_button', function() {
		jQuery( this ).addClass( 'wps-bton-loading' );
		var data = {
				action: "wps_login_first_request",
				email_address : jQuery('#wps_login_first_email_address').val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ){
					if( response['login_action'] == true ) {
						jQuery( '#wps_login_email' ).val( jQuery('#wps_login_first_email_address').val() );
						jQuery( '#user_firstname').html( response['response'] );
						jQuery( '#wps_login_form_first_step' ).slideUp( 'slow' );
						jQuery( '#wps_login_form_container').slideDown('slow');
					}
					else {
						jQuery( '.wpshop_product_attribute_user_email').val( response['response'] );
						jQuery( '#wps_login_form_first_step' ).slideUp( 'slow' );
						jQuery( '#wps_signup_form_container').slideDown('slow');
						jQuery( '#wps_first_login_button' ).removeClass( 'wps-bton-loading' );
					}
				}
				else {
					jQuery( '#wps_login_first_error_container').hide().html( response['response']).slideDown( 'slow' ).delay( 4000 ).slideUp( 'slow' );
					jQuery( '#wps_first_login_button' ).removeClass( 'wps-bton-loading' );
				}
			}, 'json');
	});

});
