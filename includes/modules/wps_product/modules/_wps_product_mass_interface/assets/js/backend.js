jQuery( document ).ready( function() {

	/**	Add the interface openr at the right of add product button	*/
	jQuery( "#wpbody-content" ).children( ".wrap" ).children( "h2:first-child" ).append( jQuery( "a#product-mass-interface-button" ) );

	/**	Trigger event on cancel general button	*/
	jQuery( document ).on( "click", "#wps-mass-interface-button-cancel", function(){
		if ( confirm( wpshopConvertAccentTojs( wpsmassinterface_i18n_js_confirm_cancel ) ) ) {
			jQuery( "#TB_closeWindowButton" ).click();
		}
	});

	/**	Trigger event on mass update pagination	*/
	jQuery( document ).on( "click", ".wps-mass-product-pagination li a", function( event ){
		event.preventDefault();
		jQuery( ".wps-product-mass-interface-top" ).remove();
		jQuery( ".wps-product-mass-interface-bottom" ).remove();
		jQuery( "#TB_window" ).hide().after( '<div id="TB_load" style="display:block; " ><img width="208" src="' + thickboxL10n.loadingAnimation + '" /></div>' );
		jQuery( "#TB_ajaxContent" ).load( jQuery( this ).attr( "href" ), {}, function(){
			jQuery( "#TB_window" ).show();
			jQuery( "#TB_load" ).remove();
		} );
	} );


	/**	Trigger event on input/select/textarea click onto a line in order to check the box corresponding to the line for saving	*/
	/**	Trigger event on text and textarea focus	*/
	jQuery( document ).on( 'focus', '.wps-product-mass-interface-table input[type="text"], .wps-product-mass-interface-table textarea', function() {
		jQuery( this ).closest( "tr" ).children( "td.wps-mass-interface-line-selector" ).children( "input[type=checkbox]" ).prop( "checked", true );
	} );

	/**	Trigger event on dropdown change	*/
	jQuery( document ).on( 'change', '.wps-product-mass-interface-table select', function() {
		jQuery( this ).closest( "tr" ).children( "td.wps-mass-interface-line-selector" ).children( "input[type=checkbox]" ).prop( "checked", true );
	});

	/**	Trigger event on radio button and checkboxes state change	*/
	jQuery( document ).on( 'click', '.wps-product-mass-interface-table input[type="radio"], .wps-product-mass-interface-table input[type="checkbox"]', function() {
		jQuery( this ).closest( "tr" ).children( "td.wps-mass-interface-line-selector" ).children( "input[type=checkbox]" ).prop( "checked", true );
	});


	/**	Trigger event on new product button click	*/
	jQuery( document ).on( "click", "#wps-mass-interface-button-new-product", function( event ){
		event.preventDefault();

		var data = {
			action: "wps_mass_interface_new_product_creation",
		};

		jQuery.post( ajaxurl, data, function( response ){
			jQuery( ".wps-product-mass-interface-table" ).prepend( response );
		});
	});

	/**	Trigger event on save button for sending product to update	*/
	jQuery( document ).on( 'click', '#wps-mass-interface-button-save', function() {

		var nb_of_product_to_save = 0;
		jQuery( ".wps-mass-interface-line-selector input[type=checkbox]" ).each( function(){
			if ( jQuery( this ).is( ":checked" ) ) {
				nb_of_product_to_save += 1;
			}
		});


		if ( 0 == nb_of_product_to_save ) {
			alert( wpsmassinterface_i18n_js_no_selection_done );
		}
		else {
			var speed = 1000;
			jQuery("#TB_ajaxContent").animate( {scrollTop: 0 }, speed, "swing");
			wpshop_add_loader( ".wpshop-admin-post-type-wpshop_product #TB_ajaxContent" );
			jQuery('.wps-product-mass-interface-main form').ajaxSubmit({
				beforeSubmit : function() {
					jQuery( '#wps_quick_add_save_data' ).addClass( 'wps-bton-loading' );
				},
		        success: function( response ) {
		        	wpshop_remove_loader( ".wpshop-admin-post-type-wpshop_product #TB_ajaxContent" );
		    		wpshop_add_loader( ".wpshop-admin-post-type-wpshop_product #TB_ajaxContent", response );
		    		setTimeout( function(){
			        	wpshop_remove_loader( ".wpshop-admin-post-type-wpshop_product #TB_ajaxContent" );
		    		}, "1500" );
		        },
			});
		}
	});

});

