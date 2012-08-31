/*	Modification du format des champs ayant la classe datetime permettant de sélectionner une date	*/
if(wp_version >= "3.1"){
	wpshop(".wpshop_input_datetime").datepicker();
	wpshop(".wpshop_input_datetime").datepicker("option", "dateFormat", "yy-mm-dd");
	wpshop(".wpshop_input_datetime").datepicker("option", "changeMonth", true);
	wpshop(".wpshop_input_datetime").datepicker("option", "changeYear", true);
	wpshop(".wpshop_input_datetime").datepicker("option", "navigationAsDateFormat", true);
}

/*	Début de la gestion des déclinaisons	*/
	/*	Traitement de l'action lors du clic sur le bouton d'ajout d'une nouvelle déclinaison	*/
	jQuery("#wpshop_dialog_new_variation_button").click(function(){
		var checkboxes = [];
		var box_checked = false;
		jQuery(".wpshop_list_of_attribute_for_variation li input[type=radio]").each(function() {
			if( jQuery(this).is(':checked') ){
				checkboxes.push(jQuery(this).val());
				box_checked = true;
			}
		});
		if (box_checked) {
			var data = {
				action: "add_new_variation",
				checkboxes: checkboxes,
				current_post_id: jQuery("#post_ID").val(),
				wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_variation_creation"); ?>'
			};
			jQuery.post(ajaxurl, data, function(response){
				jQuery(".wpshop_product_variations").html(response);
				jQuery(".wpshop_list_of_attribute_for_variation li input[type=radio]").each(function() {
					//jQuery(this).prop('checked', false);
				});
			});
		}
		else {
			alert( wpshopConvertAccentTojs( WPSHOP_NO_ATTRIBUTES_SELECT_FOR_VARIATION ) );
		}
	});
	/*	Traitement de l'action lors du clic sur le bouton de suppression d'une déclinaison existante	*/
	jQuery(".product_variation_button_delete").live('click', function (){
		if( confirm(wpshopConvertAccentTojs("<?php echo __('Are you sure you want to delete this variation?', 'wpshop'); ?>")) ) {
			var data = {
				action: "delete_variation",
				current_post_id: jQuery(this).attr("id").replace('wpshop_variation_delete_', ''),
				wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_delete_variation"); ?>'
			};
			jQuery.post(ajaxurl, data, function(response){
				if(response[0]){
					jQuery("#wpshop_product_variation_metabox_" + response[1]).fadeOut('slow');
				}
				else{
					alert(wpshopConvertAccentToJs( "<?php __('An error occured while deleting selected variation', 'wpshop'); ?>" ));
				}
			}, 'json');
		}
	});
	/*	Traitement de l'action lors du clic sur le bouton de duplication d'une déclinaison existante	*/
	jQuery(".product_variation_button_duplicate").live('click', function (){
		var data = {
			action: "duplicate_variation",
			current_post_id: jQuery(this).attr("id").replace('wpshop_variation_duplicate_', ''),
			wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_variation_duplication"); ?>'
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery(".wpshop_product_variations").html(response);
		}, 'json');
	});
/*	Fin de la gestion des déclinaisons	*/


/*	Début de la gestion des valeurs pour les attributs de type liste déroulante	*/
	/*	Ajout d'une boite permettant d'ajouter des valeurs à la volée aux attributs de type liste déroulante	*/
	jQuery("#wpshop_new_attribute_option_value_add").dialog({
		modal: true,
		autoOpen:false,
		show: "blind",
		buttons:{
			'<?php _e('Add', 'wpshop'); ?>': function(){
				var data = {
					action: "new_option_for_select_from_product_edition",
					wpshop_ajax_nonce: '<?php echo wp_create_nonce("wpshop_new_option_for_attribute_creation"); ?>',
					attribute_code: jQuery("#wpshop_attribute_type_select_code").val(),
					attribute_new_label: jQuery("#wpshop_new_attribute_option_value").val(),
					item_in_edition: jQuery("#post_ID").val()
				};
				jQuery.post(ajaxurl, data, function(response) {
					if( response[0] ) {
						var container = "wpshop_product_" + response[2] + "_input";
						jQuery("." + container).html( response[1] );
						jQuery("select.chosen_select").chosen({disable_search_threshold: 5, no_results_text: WPSHOP_CHOSEN_NO_RESULT});
						jQuery("#wpshop_new_attribute_option_value_add").dialog("close");
					}
					else {
						alert( response[1] );
					}
					jQuery("#wpshop_new_attribute_option_value_add").children("img").hide();
					jQuery("#wpshop_attribute_type_select_code").val("");
				}, "json");
	
				jQuery(this).children("img").show();
			},
			'<?php _e('Cancel', 'wpshop'); ?>': function(){
				jQuery(this).dialog("close");
			}
		},
		close:function(){
			jQuery("#wpshop_new_attribute_option_value").val("");
		}
	});
	jQuery(".wpshop_icons_add_new_value_to_option_list").live('click', function(){
		jQuery("#wpshop_attribute_type_select_code").val(jQuery(this).attr("rel"));
		jQuery("#wpshop_new_attribute_option_value_add").dialog("open");
	});
/*	Fin de la gestion des valeurs pour les attributs de type liste déroulante	*/
