jQuery(document).ready(function() {
	
	 jQuery( "#shipping_mode_list_container" ).sortable();
	
	jQuery(document).on('click', '.wps_shipping_mode_configuation_min_max', function() {
		var id = jQuery(this).attr('id');
		id = id.replace('_min_max_activate', '');
		if ( jQuery(this).is(':checked') ) {
			jQuery('#'+ id + '_min_max_shipping_rules_configuration').slideDown();
		}
		else {
			jQuery('#'+ id + '_min_max_shipping_rules_configuration').slideUp();
		}
	});

	
	/** Hide Notice Message **/
	jQuery( document ).on("click", ".wps_hide_notice_message", function() {
		var data = {
				action: "wps_hide_notice_messages",
				indicator : jQuery("#hide_messages_indicator").val()
			};
			jQuery.post(ajaxurl, data, function(response){
				if ( response["status"] )  {
					jQuery("#wpshop_shop_sale_type_notice").hide();
				}
			}, "json");
	});		
	
	
	/* Save rule Action */
	jQuery( document ).on( 'click', '.save_rules_button', function() {
		var id_shipping_method = jQuery(this).attr('id');
		id_shipping_method = id_shipping_method.replace( '_save_rule', '');
		
		jQuery('#' + id_shipping_method + '_add_rule_loader').show();
		
		var selected_country = '';
		if ( jQuery("#" + id_shipping_method + "_main_rule").is(':checked') && jQuery("#" + id_shipping_method + "_custom_shipping_active_cp").is(':checked')) {
			if ( jQuery("#country_list").val() != 0 ) {
				selected_country = jQuery("#" + id_shipping_method + "_country_list").val()+'-'+jQuery("#" + id_shipping_method + "_main_rule").val();
			}
			else {
				alert('You must choose a country.');
			}
		}
		else if( jQuery("#" + id_shipping_method + "_custom_shipping_active_cp").is(':checked')) {
			if ( jQuery("#" + id_shipping_method + "_country_list").val() != 0 && jQuery('#' + id_shipping_method + '_postcode_rule').val() != null) {
				selected_country = jQuery("#" + id_shipping_method + "_country_list").val()+'-'+jQuery('#' + id_shipping_method + '_postcode_rule').val();
			}
			else {
				alert('You must choose a country or write a postcode.');
			}
		}
		else if( jQuery("#" + id_shipping_method + "_custom_shipping_active_department").is(':checked') && jQuery("#" + id_shipping_method + "_department_rule").val() != '' ) {
			selected_country = jQuery("#" + id_shipping_method + "_country_list").val()+'-'+jQuery('#' + id_shipping_method + '_department_rule').val();
		}
		else if( jQuery("#" + id_shipping_method + "_main_rule").is(':checked') ) {
			selected_country = jQuery("#" + id_shipping_method + "_main_rule").val();
		}
		else {
			selected_country = jQuery("#" + id_shipping_method + "_country_list").val();
		}
		
		if (jQuery("#" + id_shipping_method + "_weight_rule").val() != '' && jQuery("#" + id_shipping_method + "_shipping_price").val() != '') {
			var data = {
					action: "save_shipping_rule",
					weight_rule : jQuery("#" + id_shipping_method + "_weight_rule").val(),
					shipping_price : jQuery("#" + id_shipping_method + "_shipping_price").val(),
					selected_country : selected_country,
					fees_data : jQuery("#" + id_shipping_method + "_wpshop_custom_shipping").val()
				};
				jQuery.post(ajaxurl, data, function(response) {
					if ( response['status'] ) {
						jQuery("#" + id_shipping_method + "_wpshop_custom_shipping").val( response['reponse'] );
						refresh_shipping_rules_display( id_shipping_method );
						jQuery("#" + id_shipping_method + "_country_list").val(0);
						jQuery("#" + id_shipping_method + "_shipping_price").val('');
						jQuery("#" + id_shipping_method + "_weight_rule").val('');
						jQuery("#" + id_shipping_method + "_main_rule").removeAttr("checked");
						
						jQuery('#' + id_shipping_method + '_add_rule_loader').hide();
					}
					else {
						jQuery('#' + id_shipping_method + '_add_rule_loader').hide();
					}
					
				}, 'json');
		}
		else {
			alert("You must write a weight");
		}
	});
	
	
	
	/** Delete Rule **/
	jQuery(document).on('click', '.delete_rule', function() {
		jQuery(".delete_rule").attr('disabled', 'disabled');
		var id = jQuery(this).attr('title');
		jQuery('#' + id + '_delete_rule_loader').show();
		
		var data = {
				action: "delete_shipping_rule",
				country_and_weight: jQuery(this).attr('id'),
				fees_data : jQuery("#" + id + "_wpshop_custom_shipping").val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery("#" + id + "_wpshop_custom_shipping").val( response['reponse'] );
					refresh_shipping_rules_display( id );
					jQuery(".delete_rule").removeAttr('disabled');
					jQuery('#' + id + '_delete_rule_loader').hide();
				}
				else {
					jQuery('#' + id + '_delete_rule_loader').hide();
				}
				
				
			}, 'json');
	});
	
	
	function refresh_shipping_rules_display( id ) {
		//jQuery("#loader_custom_shipping_rules").show();
		var data = {
			action: "display_shipping_rules",
			fees_data : jQuery("#" + id + "_wpshop_custom_shipping").val(),
			shipping_mode_id : id
		};
		jQuery.post(ajaxurl, data, function(response) {
			if ( response['status'] ) {
				jQuery("#loader_custom_shipping_rules").hide();
				jQuery("#" + id + "_shipping_rules_container").html( response['reponse'] );
			}
		}, 'json');
		//jQuery("#loader_custom_shipping_rules").hide();
	}
	
	
	/** ADD A SHIPPING MODE **/
	jQuery( document ).on( 'click', '#add_shipping_mode', function() {
		jQuery('#add_shipping_mode_loader').show();
		var data = {
				action: "add_shipping_mode",
				shipping_mode_name : jQuery('#shipping_mode_name').val(),
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery('#shipping_mode_name').val(' ');
					jQuery('#shipping_mode_list_container').html( jQuery('#shipping_mode_list_container').html() + response['response'] );
					jQuery('#shipping_mode_creation_error').html(' ');
					jQuery('input[name=Submit]').click();
				}
				else {
					jQuery('#shipping_mode_creation_error').html( response['response'] );
					jQuery('#add_shipping_mode_loader').hide();
				}
			}, 'json');
		
	});
	
	jQuery( '.save_shipping_mode_rules' ).click(function() {
		jQuery('.save_configuration_loader').show();
		jQuery('input[name=Submit]').click();	
	});
	checked_active_custom_fees();
	jQuery( document ).on( 'click', '.active_postcode_custom_shipping', function() {
		checked_active_custom_fees();
	});
	jQuery( document ).on( 'click', '.active_department_custom_shipping', function() {
		checked_active_custom_fees();
	});
	
	function checked_active_custom_fees() {
		if ( jQuery('.active_postcode_custom_shipping').is(':checked') ) {
			jQuery( '.postcode_rule' ).fadeIn( 'slow' );
		}
		else {
			jQuery( '.postcode_rule' ).fadeOut( 'slow' );
		}
	}
	function checked_active_custom_fees() {
		/** Postcode **/
		if ( jQuery('.active_postcode_custom_shipping').is(':checked') ) {
			jQuery( '.postcode_rule' ).fadeIn( 'slow' );
		}
		else {
			jQuery( '.postcode_rule' ).fadeOut( 'slow' );
		}
		/** Department **/
		if ( jQuery('.active_department_custom_shipping').is(':checked') ) {
			jQuery( '.department_rule' ).fadeIn( 'slow' );
		}
		else {
			jQuery( '.department_rule' ).fadeOut( 'slow' );
		}
	}
});