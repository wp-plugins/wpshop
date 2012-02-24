/*	Define the jQuery noConflict var for the plugin	*/
var wpshop = jQuery.noConflict();

/*	Action launched directly after the page is load	*/
wpshop(document).ready(function(){
	wpshop('.edit-tags-php form').attr('enctype', 'multipart/form-data').attr('encoding', 'multipart/form-data');

	/*	Hide the message container if not empty	*/
	if(wpshop("#wpshopMessage").html != ''){
		hideShowMessage(5000);
	}

	/*	Start the script that allows to make the header part of a page following the scroll	*/
	if(wpshop("#pageTitleContainer").offset()){
		var pageTitleContainerOffset = wpshop("#pageTitleContainer").offset().top;
		wpshop(window).scroll(function(){
			if((wpshop(window).scrollTop() > pageTitleContainerOffset) && !(wpshop("#pageTitleContainer").hasClass("pageTitle_Fixed"))){
				wpshop("#pageTitleContainer").removeClass("pageTitle");
				wpshop("#pageTitleContainer").addClass("pageTitle_Fixed");
				wpshop("#pageHeaderButtonContainer").removeClass("pageHeaderButton");
				wpshop("#pageHeaderButtonContainer").addClass("pageHeaderButton_Fixed");
				wpshop("#wpshopMainContent").addClass("wpshopContent_Fixed");
			}
			else if((wpshop(window).scrollTop() <= pageTitleContainerOffset)  && (wpshop("#pageTitleContainer").hasClass("pageTitle_Fixed"))){
				wpshop("#pageTitleContainer").addClass("pageTitle");
				wpshop("#pageTitleContainer").removeClass("pageTitle_Fixed");
				wpshop("#pageHeaderButtonContainer").addClass("pageHeaderButton");
				wpshop("#pageHeaderButtonContainer").removeClass("pageHeaderButton_Fixed");
				wpshop("#wpshopMainContent").removeClass("wpshopContent_Fixed");
			}
		});
	}

	/*	Start the script that allows to make the message container following the scroll	*/
	if(wpshop("#wpshopMessage").offset()){
		var pageTitleContainerOffset = wpshop("#wpshopMessage").offset().top;
		wpshop(window).scroll(function(){
			if((wpshop(window).scrollTop() > pageTitleContainerOffset) && !(wpshop("#wpshopMessage").hasClass("wpshopPageMessage_Fixed"))){
				wpshop("#wpshopMessage").addClass("wpshopPageMessage_Fixed");
			}
			else if((wpshop(window).scrollTop() <= pageTitleContainerOffset)  && (wpshop("#wpshopMessage").hasClass("wpshopPageMessage_Fixed"))){
				wpshop("#wpshopMessage").removeClass("wpshopPageMessage_Fixed");
			}
		});
	}

	/*	Start attribute unit management part	*/
	wpshop("#wpshop_attribute_unit_manager").dialog({
		autoOpen: false,
		width: 800,
		height: 600,
		modal: true,
		close:function(){
			wpshop("#wpshop_attribute_unit_manager").html("");
		}
	});
	wpshop("#wpshop_attribute_unit_manager_opener").click(function(){
		wpshop("#wpshop_attribute_unit_manager").html("<div class='wpshopCenterContainer' >" + wpshop("#wpshopLoadingPicture").html() + "</div>");
		wpshop("#wpshop_attribute_unit_manager").load(WPSHOP_AJAX_FILE_URL,{
			"post": "true",
			"elementCode": "attribute_unit_management",
			"action": "load_unit_interface"
		});
		wpshop("#wpshop_attribute_unit_manager").dialog("open");
	});

	/*	Allows to fill the installation form without having to type anything	*/
	jQuery(".fill_form_for_test").click(function(){
		jQuery("#company_info_capital").val("10000");
		jQuery("#company_info_legal_statut").val("sarl");

		jQuery("#company_info_name").val("Ma societe");
		jQuery("#company_info_street").val("5 avenue des champs Elisee");
		jQuery("#company_info_postcode").val("75000");
		jQuery("#company_info_city").val("Paris");
		jQuery("#company_info_country").val("France");

		jQuery("#company_name").val("Ma societe");
		jQuery("#company_street").val("5 avenue des champs Elisee");
		jQuery("#company_postcode").val("75000");
		jQuery("#company_city").val("Paris");
		jQuery("#company_country").val("France");

		jQuery("#billing_number_figures").val("10");
		jQuery("#NOREPLY_EMAIL").val("dev@masociete.com");
		jQuery("#CONTACT_EMAIL").val("dev@masociete.com");

		jQuery("#paymentByChecks").prop("checked", true);
		jQuery("#paymentByPaypal").prop("checked", false);
	});

	/*	Add support for option excluded domain deletion	*/
	jQuery(".delete_option").live('click', function(){
		if(confirm(wpshopConvertAccentTojs(WPSHOP_SURE_TO_DELETE_ATTR_OPTION_FROM_LIST))){
			if(jQuery(this).attr("id")) {
				jQuery(".delete_option_pic_" + jQuery(this).attr("id").replace("att_opt_", "")).html(jQuery("#wpshopLoadingPicture").html());
				jQuery("#ajax-response").load(WPSHOP_AJAX_FILE_URL,{
					"post": "true",
					"elementCode": "attribute",
					"action": "delete_option",
					"elementIdentifier": jQuery(this).attr("id").replace("att_opt_", "") 			
				});
			}
			else {
				jQuery(this).closest("li").remove();
			}
		}
	});
	
	/*	Add support for option excluded domain addition	*/
	jQuery(".add_new_option").live("click",function(){
		add_new_option();
	});

	/*	Start product price amount calcul	*/
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_HT).live("keyup", function(){
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'HT'){
			calcul_price_from_ET();
		}
	});
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_HT).live("blur", function(){
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'HT'){
			calcul_price_from_ET();
		}
	});

	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX).change(function(){
		jQuery("#wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX + "_current_value").val(jQuery("#wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX + "_value_" + jQuery(this).val()).val());
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'HT'){
			calcul_price_from_ET();
		}
		else if(WPSHOP_PRODUCT_PRICE_PILOT == 'TTC'){
			calcul_price_from_ATI();
		}
	});

	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TTC).live("keyup", function(){
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'TTC'){
			calcul_price_from_ATI();
		}
	});
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TTC).live("blur", function(){
		if(WPSHOP_PRODUCT_PRICE_PILOT == 'TTC'){
			calcul_price_from_ATI();
		}
	});
});

function calcul_price_from_ET(){
	var ht_amount = jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_HT).val().replace(",", ".");
	var tax_rate = 1 + (jQuery("#wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX + "_current_value").val() / 100);

	var ttc_amount = ht_amount * tax_rate;
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TTC).val(ttc_amount.toFixed(5));
	var tva_amount = ttc_amount - ht_amount;
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX_AMOUNT).val(tva_amount.toFixed(5));
}
function calcul_price_from_ATI(){
	var ttc_amount = jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TTC).val().replace(",", ".");
	var tax_rate = 1 + (jQuery("#wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX + "_current_value").val() / 100);

	var ht_amount = ttc_amount / tax_rate;
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_HT).val(ht_amount.toFixed(5));
	var tva_amount = ttc_amount - ht_amount;
	jQuery(".wpshop_product_attribute_" + WPSHOP_PRODUCT_PRICE_TAX_AMOUNT).val(tva_amount.toFixed(5));
}
	
function add_new_option(){
		if(jQuery("#new_option_label").val() != "" && jQuery("#new_option_value").val() != ""){
			var option_value_already_exist = false;
			jQuery(".attribute_options_fieldset input[type=text]").each(function(){
				if(jQuery(this).val() == jQuery("#new_option_value").val()){
					option_value_already_exist = true;
				}
			});
			if(!option_value_already_exist){
				jQuery("#sortable_attribute").append("<li class='ui-state-default'><div class='clear' ><span class='attributeOptionValue alignleft ui-icon' >&nbsp;</span><input type='text' value='" + jQuery("#new_option_label").val() + "' name='options[]' /><input type='text' value='" + jQuery("#new_option_value").val() + "' name='optionsValue[]' /><img src='" + WPSHOP_MEDIAS_ICON_URL + "delete.png' alt='' title='' class='delete_option' /></div></li>");
			}
			else{
				alert(wpshopConvertAccentTojs(WPSHOP_NEW_OPTION_ALREADY_EXIST_IN_LIST));
			}
			jQuery("#new_option_label").val("");
			jQuery("#new_option_value").val("");
		}
		else{
			alert(wpshopConvertAccentTojs(WPSHOP_NEW_OPTION_IN_LIST_EMPTY));
		}
}

/**
*	Function for showing a message on a page after an actiontd
*
*	@param string message The message to add to the page
*
*/
function wpshopShowMessage(message){
	wpshop("#wpshopMessage").addClass("wpshopPageMessage_Updated");
	wpshop("#wpshopMessage").html(wpshopConvertAccentTojs(message));
}
/**
*	Function for hidding the message on the page after an action
*
*	@param string timeToWaitForHiding The time the counter will take before hiding and emptying the page message
*
*/
function hideShowMessage(timeToWaitForHiding){
	setTimeout(function(){
		wpshop("#wpshopMessage").removeClass("wpshopPageMessage_Updated");
		wpshop("#wpshopMessage").html("");
	}, timeToWaitForHiding);
}

/**	
*	Contains different function for the common interface into the plugin
*
*	@param string currentType The type of element we want to delete to determin wich form we have to submit
*	@param string returnAlertMessage The message showed to the user before changing page if he click on the return button and that there are changes on the page
*	@param string deleteElementMessage The message showed to the user before submitting the form
*
*/
function wpshopMainInterface(currentType, returnAlertMessage, deleteElementMessage){
	(function(){
		/*	Change the interface layout by adding tabs for navigation	*/
		jQuery("#wpshopFormManagementContainer").tabs();

		/*	Add an indicator on the page for usert alert when changing something in the page and clicking on return button	*/
		jQuery("#" + currentType + "_form input, #" + currentType + "_form textarea").keypress(function(){
			jQuery("#" + currentType + "_form_has_modification").val("yes");
		});
		jQuery("#" + currentType + "_form select").change(function(){
			jQuery("#" + currentType + "_form_has_modification").val("yes");
		});

		/*	Action when clicking on the delete button	*/
		jQuery("#delete").click(function(){
			jQuery("#" + currentType + "_action").val("delete");
			deleteElement(currentType, deleteElementMessage);
		});
		if(jQuery("#" + currentType + "_action").val() == "delete"){
			deleteElement(currentType, deleteElementMessage);
		}

		/*	Action when clicking on the save/add/saveandcontinue button	*/
		jQuery("#save, #add").click(function(){
			jQuery("#" + currentType + "_form").submit();
		});
		jQuery("#saveandcontinue").click(function(){
			jQuery("#" + currentType + "_form").attr("action", jQuery("#" + currentType + "_form").attr("action") + jQuery("#wpshopFormManagementContainer li.ui-tabs-selected a").attr("href"));
			jQuery("#" + currentType + "_action").val(jQuery("#" + currentType + "_action").val() + "andcontinue");
			jQuery("#" + currentType + "_form").submit();
		});

		/*	When clicking on return button show an alert message to the user to prevent that something has been changed into the page	*/
		jQuery(".cancelButton").click(function(){
			if((jQuery("#" + currentType + "_form_has_modification").val() == "yes")){
				if(!confirm(wpshopConvertAccentTojs(returnAlertMessage))){
					return false;
				}
			}
		});
	})(wpshop);
}

/**
*	When clicking on submit button or link, Ask the question to the user if he is sure that he want to delete the current element if not, stay on the current page in edit mode
*
*	@param string currentType The type of element we want to delete to determin wich form we have to submit
*	@param string deleteElementMessage The message showed to the user before submitting the form
*
*/
function deleteElement(currentType, deleteElementMessage){
	if(confirm(wpshopConvertAccentTojs(deleteElementMessage))){
		wpshop("#" + currentType + "_form").submit();
	}
	else{
		wpshop("#" + currentType + "_action").val("edit");
	}
}

/**	
*	Allows to convert html special chars to normal chars in javascript messages
*
*	@param string text The text we want to change html special chars into normal chars
*
*/
function wpshopConvertAccentTojs(text){
	text = text.replace(/&Agrave;/g, "\300");
	text = text.replace(/&Aacute;/g, "\301");
	text = text.replace(/&Acirc;/g, "\302");
	text = text.replace(/&Atilde;/g, "\303");
	text = text.replace(/&Auml;/g, "\304");
	text = text.replace(/&Aring;/g, "\305");
	text = text.replace(/&AElig;/g, "\306");
	text = text.replace(/&Ccedil;/g, "\307");
	text = text.replace(/&Egrave;/g, "\310");
	text = text.replace(/&Eacute;/g, "\311");
	text = text.replace(/&Ecirc;/g, "\312");
	text = text.replace(/&Euml;/g, "\313");
	text = text.replace(/&Igrave;/g, "\314");
	text = text.replace(/&Iacute;/g, "\315");
	text = text.replace(/&Icirc;/g, "\316");
	text = text.replace(/&Iuml;/g, "\317");
	text = text.replace(/&Eth;/g, "\320");
	text = text.replace(/&Ntilde;/g, "\321");
	text = text.replace(/&Ograve;/g, "\322");
	text = text.replace(/&Oacute;/g, "\323");
	text = text.replace(/&Ocirc;/g, "\324");
	text = text.replace(/&Otilde;/g, "\325");
	text = text.replace(/&Ouml;/g, "\326");
	text = text.replace(/&Oslash;/g, "\330");
	text = text.replace(/&Ugrave;/g, "\331");
	text = text.replace(/&Uacute;/g, "\332");
	text = text.replace(/&Ucirc;/g, "\333");
	text = text.replace(/&Uuml;/g, "\334");
	text = text.replace(/&Yacute;/g, "\335");
	text = text.replace(/&THORN;/g, "\336");
	text = text.replace(/&Yuml;/g, "\570");
	text = text.replace(/&szlig;/g, "\337");
	text = text.replace(/&agrave;/g, "\340");
	text = text.replace(/&aacute;/g, "\341");
	text = text.replace(/&acirc;/g, "\342");
	text = text.replace(/&atilde;/g, "\343");
	text = text.replace(/&auml;/g, "\344");
	text = text.replace(/&aring;/g, "\345");
	text = text.replace(/&aelig;/g, "\346");
	text = text.replace(/&ccedil;/g, "\347");
	text = text.replace(/&egrave;/g, "\350");
	text = text.replace(/&eacute;/g, "\351");
	text = text.replace(/&ecirc;/g, "\352");
	text = text.replace(/&euml;/g, "\353");
	text = text.replace(/&igrave;/g, "\354");
	text = text.replace(/&iacute;/g, "\355");
	text = text.replace(/&icirc;/g, "\356");
	text = text.replace(/&iuml;/g, "\357");
	text = text.replace(/&eth;/g, "\360");
	text = text.replace(/&ntilde;/g, "\361");
	text = text.replace(/&ograve;/g, "\362");
	text = text.replace(/&oacute;/g, "\363");
	text = text.replace(/&ocirc;/g, "\364");
	text = text.replace(/&otilde;/g, "\365");
	text = text.replace(/&ouml;/g, "\366");
	text = text.replace(/&oslash;/g, "\370");
	text = text.replace(/&ugrave;/g, "\371");
	text = text.replace(/&uacute;/g, "\372");
	text = text.replace(/&ucirc;/g, "\373");
	text = text.replace(/&uuml;/g, "\374");
	text = text.replace(/&yacute;/g, "\375");
	text = text.replace(/&thorn;/g, "\376");
	text = text.replace(/&yuml;/g, "\377");
	text = text.replace(/&oelig;/g, "\523");
	text = text.replace(/&OElig;/g, "\522");
	return text;
}

/**
*	Method used to save the new order in a sortable list
*/
function saveAttibuteState(table){
	wpshop(".newOrder").each(function(){
		currentIdentifier = wpshop(this).attr("id").replace("newOrder", "");
		newOrder = wpshop("#attribute_group_" + currentIdentifier + "_details").sortable("toArray");
		wpshop("#newOrder" + currentIdentifier + "").val(newOrder);
		wpshop("#" + table + "_form_has_modification").val("yes");
	});
}
/**
*	Method to change a basic list into a sortable list
*/
function make_list_sortable(table){
	wpshop(".attributeGroupDetails").sortable({
		cancel: ".ui-state-disabled",
		placeholder: "ui-state-highlight",
		connectWith: "ul",
		update: function(){
			saveAttibuteState(table);
		}
	}).disableSelection();
}