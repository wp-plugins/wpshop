jQuery(document).ready(function() {
	if ( jQuery("#wpshop_custom_shipping").val() != null ) {
		refresh_shipping_rules_display();
	}
	if ( jQuery("#custom_shipping_active_cp").is(':checked') ) {
		jQuery(".postcode_rule").show();
		jQuery(".global_rule_checkbox_indic").html(WPSHOP_APPLY_MAIN_RULE_FOR_POSTCODES);
	}
	else {
		jQuery(".postcode_rule").hide();
		jQuery(".global_rule_checkbox_indic").html(WPSHOP_APPLY_MAIN_RULE_FOR_COUNTRIES);
	}
	
	
	/* Display the postcode Field when the option is activated */
	jQuery("#custom_shipping_active_cp").live('click', function() {
		if ( jQuery("#custom_shipping_active_cp").is(':checked') ) {
			jQuery(".postcode_rule").fadeIn('slow');
			jQuery(".global_rule_checkbox_indic").html(WPSHOP_APPLY_MAIN_RULE_FOR_POSTCODES);
		}
		else {
			jQuery(".postcode_rule").fadeOut('slow');
			jQuery(".global_rule_checkbox_indic").html(WPSHOP_APPLY_MAIN_RULE_FOR_COUNTRIES);
		}
	});
	
	/* Save rule Action */
	jQuery("#save_rule").live('click', function() {
		var selected_country = '';
		if ( jQuery("#main_rule").is(':checked') && jQuery("#custom_shipping_active_cp").is(':checked')) {
			if ( jQuery("#country_list").val() != 0 ) {
				selected_country = jQuery("#country_list").val()+'-'+jQuery("#main_rule").val();
			}
			else {
				alert('You must choose a country.');
			}
		}
		else if( jQuery("#custom_shipping_active_cp").is(':checked')) {
			if ( jQuery("#country_list").val() != 0 && jQuery('#postcode_rule').val() != null) {
				selected_country = jQuery("#country_list").val()+'-'+jQuery('#postcode_rule').val();
			}
			else {
				alert('You must choose a country or write a postcode.');
			}
		}
		else if( jQuery("#main_rule").is(':checked') ) {
			selected_country = jQuery("#main_rule").val();
		}
		else {
			selected_country = jQuery("#country_list").val();
		}
		
		if (jQuery("#weight_rule").val() != null && jQuery("#shipping_price").val() != null) {
			var data = {
					action: "save_shipping_rule",
					weight_rule : jQuery("#weight_rule").val(),
					shipping_price : jQuery("#shipping_price").val(),
					selected_country : selected_country,
					fees_data : jQuery("#wpshop_custom_shipping").val()
				};
				jQuery.post(ajaxurl, data, function(response) {
					if ( response['status'] ) {
						jQuery("#wpshop_custom_shipping").val( response['reponse'] );
						refresh_shipping_rules_display();
						jQuery("#country_list").val(0);
						jQuery("#shipping_price").val('');
						jQuery("#weight_rule").val('');
						jQuery("#main_rule").removeAttr("checked");
					}
					
				}, 'json');
		}
		else {
			alert("You must write a weight");
		}
	});
	
	jQuery(".delete_rule").live('click', function() {
		jQuery(".delete_rules").attr('disabled', 'disabled');
		var data = {
				action: "delete_shipping_rule",
				country_and_weight: jQuery(this).attr('id'),
				fees_data : jQuery("#wpshop_custom_shipping").val()
			};
			jQuery.post(ajaxurl, data, function(response) {
				if ( response['status'] ) {
					jQuery("#wpshop_custom_shipping").val( response['reponse'] );
					refresh_shipping_rules_display();
					jQuery(".delete_rules").removeAttr('disabled');
				}
				
			}, 'json');
	});
	
});

function refresh_shipping_rules_display() {
	jQuery("#loader_custom_shipping_rules").show();
	var data = {
		action: "display_shipping_rules",
		fees_data : jQuery("#wpshop_custom_shipping").val()
	};
	jQuery.post(ajaxurl, data, function(response) {
		if ( response['status'] ) {
			jQuery("#loader_custom_shipping_rules").hide();
			jQuery("#shipping_rules_container").html( response['reponse'] );
		}
	}, 'json');
	jQuery("#loader_custom_shipping_rules").hide();
}