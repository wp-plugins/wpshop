jQuery( document ).ready( function() {
	
	jQuery( document ).on( 'click', '#wps_submit_address_form', function() {
		/** Ajax Form Address Save **/
		jQuery('#wps_address_form_save').ajaxSubmit({
			dataType:  'json',
			beforeSubmit : function() {
			//jQuery('#loader_make_return_action').show();
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		wps_modal_closer();
	        	}
	        	else {
	        		jQuery('#wps_address_error_container').html( response[1] );
	        	}
	        },
		});	
	});
	
	jQuery(document).on('click','.wps_modify_address_modal_opener',function(e){
		e.preventDefault();
		var data = {
			action: "wps_load_address_form",
			address_id: jQuery( this ).attr('id').replace( "wps_modify_address_", ""),
		};
		jQuery.post(ajaxurl, data, function( response ){
			wps_modal(response[0], response[1]);
		}, 'json');
	});
	/*
	jQuery(document).on('click','.add_new_address',function(e){
		var id = jQuery( this ).attr('id').replace('add_new_address_', '');
		e.preventDefault();
		var data = {
			action: "wps_load_address_form",
			address_type_id: id,
		};
		jQuery.post(ajaxurl, data, function( response ){
			wps_modal(response[0], response[1]);
		}, 'json');
	});
	*/
	
	jQuery( document ).on('click', '#wps_checkout_save_first_address_btn', function() {
		/** Ajax Form Address Save **/
		jQuery( '#wps_save_first_address_loader' ).show();
		jQuery('#wps_checkout_save_form').ajaxSubmit({
			dataType:  'json',
			beforeSubmit : function() {
			//jQuery('#loader_make_return_action').show();
			},
	        success: function( response ) {
	        	if ( response[0] ) {
	        		jQuery('#wps_checkout_save_first_address_container').fadeOut('slow', function() {
	        			jQuery('#wps_checkout_save_first_address_container').html( response[1] );
	        			jQuery('#wps_checkout_save_first_address_container').fadeIn();
	        		});
	        		reload_shipping_mode();
	        	}
	        	else {
	        		jQuery('#wps_save_first_address_errors_container').html( response[1] );
	        		jQuery( '#wps_save_first_address_loader' ).hide();
	        	}

	        },
		});	
	});
	

	
});


