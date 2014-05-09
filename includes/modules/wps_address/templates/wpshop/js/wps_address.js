jQuery( document ).ready( function() {

	if( jQuery('#wps-shipping_to_billing').length > 0 && jQuery('#wps-shipping_to_billing').is(':checked') ) {
		jQuery('.wps-billing-address').hide();
	}
	
	jQuery( document ).on( 'click', '#wps-shipping_to_billing', function() {
		if( jQuery('#wps-shipping_to_billing').is(':checked') ) {
			jQuery('.wps-billing-address').slideUp( 'slow' );
		}
		else {
			jQuery('.wps-billing-address').slideDown( 'slow' );
		}
	});
	
	
	
	jQuery( document ).on( 'click', '#wps_submit_address_form', function() {
		/** Ajax Form Address Save **/
		jQuery('#wps_address_form_save').ajaxForm({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery( '#wps_submit_address_form' ).addClass( 'wps-bton-loading' );
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		//wps_modal_closer();
	        		jQuery( '.wps-modal-close').click();
	        		jQuery( '#wps_submit_address_form' ).removeClass( 'wps-bton-loading' );
	        		
	        		reload_address_container( response[2], '' );
	        		if( response[3] != null ) {
	        			reload_address_container( response[3], '' );
	        			setTimeout(function() {
	        				var height_tab = parseFloat( jQuery( '#wps-address-container-' + response[2] + ' .wps-adresse-listing-select').height() );
	        				jQuery( '#wps-address-container-' + response[3] + ' .wps-adresse-listing-select').height( height_tab );
	        			}, 5000);
	        		
	        		}
	        	}
	        	else {
	        		jQuery('#wps_address_error_container').html( response[1] );
	        		jQuery( '#wps_submit_address_form' ).removeClass( 'wps-bton-loading' );
	        	}
	        },
		});	
	});

	
	jQuery( document ).on('click', '#wps_checkout_save_first_address_btn', function() {
		/** Ajax Form Address Save **/
		jQuery( '#wps_save_first_address_loader' ).show();
		jQuery('#wps_checkout_save_form').ajaxSubmit({
			dataType:  'json',
			beforeSubmit : function() {
				jQuery( '#wps_submit_address_form' ).addClass( 'wps-bton-loading' );
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		jQuery( '#wps_submit_address_form' ).removeClass( 'wps-bton-loading' );
	        		jQuery('#wps_checkout_save_first_address_container').fadeOut('slow', function() {
	        			jQuery('#wps_checkout_save_first_address_container').html( response[1] );
	        			jQuery('#wps_checkout_save_first_address_container').fadeIn();
	        		});
	        		reload_shipping_mode();
	        	}
	        	else {
	        		jQuery( '#wps_submit_address_form' ).removeClass( 'wps-bton-loading' );
	        		jQuery('#wps_save_first_address_errors_container').html( response[1] );
	        		jQuery( '#wps_save_first_address_loader' ).hide();
	        	}

	        },
		});	
	});
	
	/** Add an address **/
	jQuery( document ).on( 'click', '.wps-add-an-address', function() {
		var address_infos = jQuery( this ).attr( 'id' ).replace( 'wps-add-an-address-', '');
		address_infos = address_infos.split( '-' );
		var data = {
				action: "wps_load_address_form",
				address_type_id : address_infos[0]
			};
			jQuery.post(ajaxurl, data, function(response) {

					jQuery( '.wps-modal-body').html( response[0] );
					jQuery( '.wps-modal-header h3').html( response[1] ); 
					jQuery('.wps-modal-wrapper').addClass('wps-modal-opened');
					jQuery('body').addClass('wps-body-inactiv');

				
			}, 'json');
	});
	
	/** Edit an address **/
	jQuery( document ).on( 'click', '.wps-address-edit-address', function() {
		var address_id = jQuery( this ).attr( 'id' ).replace( 'wps-address-edit-address-', '' );
		var data = {
				action: "wps_load_address_form",
				address_id :  address_id
			};
			jQuery.post(ajaxurl, data, function(response) {
				//if ( response['status'] ) {
					jQuery( '.wps-modal-body').html( response[0] );
					jQuery( '.wps-modal-header h3').html( response[1] ); 
					jQuery('.wps-modal-wrapper').addClass('wps-modal-opened');
					jQuery('body').addClass('wps-body-inactiv');
				//}
				
			}, 'json');
	});
	
	/** Delete an address */
	jQuery( document ).on( 'click', '.wps-address-delete-address', function(){
		if( confirm(WPSHOP_CONFIRM_DELETE_ADDRESS) ) {
		var address_infos = jQuery( this ).attr( 'id' ).replace( 'wps-address-delete-address-', '' );
		address_infos = address_infos.split( '-' );
		var data = {
				action: "wps_delete_an_address",
				address_id :  address_infos[0]
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					reload_address_container( address_infos[1], '' );
				}
				
			}, 'json');
		}
	});
	
	function reload_address_container( address_type, address_id  ) {
		
		var data = {
				action: "wps_reload_address_interface",
				address_id :  address_id,
				address_type : address_type
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery( '#wps-address-container-' + address_type ).animate({'opacity' : 0.1}, 350, function() {
						jQuery( '#wps-address-container-' + address_type ).html( response['response'] );
						jQuery( '#wps-address-container-' + address_type ).animate({'opacity' : 1}, 350, function() {
							wp_select_adresses( '.wps-change-adresse');
						});
					});	
				}
				
			}, 'json');
	}
	
	
	
	
});


