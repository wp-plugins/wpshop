/*	Define the jQuery noConflict var for the plugin	*/
var wpshop = jQuery.noConflict();

// Centre un élèment sur la page
jQuery.fn.center = function () {
	this.css("top", ( jQuery(window).height() - this.height() ) / 2 + "px");
	this.css("left", ( jQuery(window).width() - this.width() ) / 2 + "px");
	return this;
}

/*	Action launched directly after the page is load	*/
wpshop(document).ready(function(){
	
	jQuery("#superTab").tabs();
	
	// Copie automatique de formulaire
	jQuery('input[name="wpshop_company_info[company_name]"]').keyup(function(){jQuery('input[name="wpshop_paymentAddress[company_name]"]').val(jQuery(this).val());});
	jQuery('input[name="wpshop_company_info[company_street]"]').keyup(function(){jQuery('input[name="wpshop_paymentAddress[company_street]"]').val(jQuery(this).val());});
	jQuery('input[name="wpshop_company_info[company_postcode]"]').keyup(function(){jQuery('input[name="wpshop_paymentAddress[company_postcode]"]').val(jQuery(this).val());});
	jQuery('input[name="wpshop_company_info[company_city]"]').keyup(function(){jQuery('input[name="wpshop_paymentAddress[company_city]"]').val(jQuery(this).val());});
	jQuery('input[name="wpshop_company_info[company_country]"]').keyup(function(){jQuery('input[name="wpshop_paymentAddress[company_country]"]').val(jQuery(this).val());});
	
	// -----------------
	// Insertion balises
	// -----------------
	
	// PRODUCTS
	jQuery("#insert_products").click(function(){
		if(jQuery('ul#products_selected input:checked').length>0)
		{
			var display_type = jQuery('input[type=radio][name=product_display_type]:checked').attr('value');
			var string = ' [wpshop_product pid="';
			jQuery('ul#products_selected input:checked').each(function() {
				string += jQuery(this).val()+',';
			});
			string = string.slice(0,-1)+'" type="'+display_type+'"] ';
			addTextareaContent(string);
		}
	});
	
	// ATTRIBUTS
	jQuery("#insert_attr").click(function(){
		var string='';
		jQuery('ul#attr_selected input:checked').each(function() {
			var data = jQuery(this).val().split('-');
			string += '[wpshop_att_val type="'+data[2]+'" attid="'+data[1]+'" pid="'+data[0]+'"]';
		});
		addTextareaContent(string);
	});
	
	// ATTRIBUTS GROUPS
	jQuery("#insert_groups").click(function(){
		var string='';
		jQuery('ul#groups_selected input:checked').each(function() {
			var data = jQuery(this).val().split('-');
			string += '[wpshop_att_group pid="'+data[0]+'" sid="'+data[1]+'"]';
		});
		addTextareaContent(string);
	});
	
	// CATEGORY
	jQuery("#insert_cats").click(function(){
		var string='';
		var display_type = jQuery('input[type=radio][name=cats_display_type]:checked').attr('value');
		jQuery('ul#cats_selected input:checked').each(function() {
			var data = jQuery(this).val().split('-');
			string += '[wpshop_category cid="'+jQuery(this).val()+'" type="'+display_type+'"]';
		});
		addTextareaContent(string);
	});
	
	// ------------------
	// Recherche via Ajax
	// ------------------
	
	// PRODUCTS
	jQuery("#search_products").keyup(function() {
		var search_string = jQuery(this).val();
		if (search_string.length>2) {
			jQuery.get(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "speedSearch", searchType: "products", search: search_string },
				function(data){jQuery('ul#products_selected').html(data);}
			);
		}
		else if (search_string.length==0) {
			jQuery.get(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "speedSearch", searchType: "products", search: "" },
				function(data){jQuery('ul#products_selected').html(data);}
			);
		}
	});
	
	// ATTRIBUTS
	jQuery("#search_attr").keyup(function() {
		var search_string = jQuery(this).val();
		if (search_string.length>2) {
			jQuery.get(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "speedSearch", searchType: "attr", search: search_string },
				function(data){jQuery('ul#attr_selected').html(data);}
			);
		}
		else if (search_string.length==0) {
			jQuery.get(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "speedSearch", searchType: "attr", search: "" },
				function(data){jQuery('ul#attr_selected').html(data);}
			);
		}
	});
	
	// ATTRIBUTS GROUP
	jQuery("#search_groups").keyup(function() {
		var search_string = jQuery(this).val();
		if (search_string.length>2) {
			jQuery.get(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "speedSearch", searchType: "groups", search: search_string },
				function(data){jQuery('ul#groups_selected').html(data);}
			);
		}
		else if (search_string.length==0) {
			jQuery.get(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "speedSearch", searchType: "groups", search: "" },
				function(data){jQuery('ul#groups_selected').html(data);}
			);
		}
	});
	
	// CATEGORY
	jQuery("#search_cats").keyup(function() {
		var search_string = jQuery(this).val();
		if (search_string.length>2) {
			jQuery.get(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "speedSearch", searchType: "cats", search: search_string },
				function(data){jQuery('ul#cats_selected').html(data);}
			);
		}
		else if (search_string.length==0) {
			jQuery.get(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "speedSearch", searchType: "cats", search: "" },
				function(data){jQuery('ul#cats_selected').html(data);}
			);
		}
	});
	
	// Ajoute le contenu au textarea de WP, Visuel + HTML
	function addTextareaContent(string) {
		jQuery("#content").append(string);
		jQuery("#tinymce",jQuery("#content_ifr").contents()).append(string);
	}
	
	// CATEGORY
	jQuery(".markAsShipped").live('click',function(){
		var _this = jQuery(this);
		var this_class = _this.attr('class').split(' ');
		var oid = this_class[2].substr(6);
		
		// Display loading...
		_this.addClass('loading');
		
		jQuery.getJSON(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "ajax_loadOrderTrackNumberForm", oid: oid},
			function(data){
				if(data[0]) {
					var data = data[1];
					jQuery('body').append('<div class="superBackground"></div><div class="popupAlert">'+data+'</div>');
					jQuery('.popupAlert').center();
					_this.removeClass('loading');
				}
				else {
					_this.removeClass('loading');
					alert(data[1]);
				}
			}
		);
	});
	
	/* Paiement reçu */
	jQuery(".markAsCompleted").live('click',function(){
		var _this = jQuery(this);
		var this_class = _this.attr('class').split(' ');
		var oid = this_class[2].substr(6);
		
		// Display loading...
		_this.addClass('loading');
		
		// Start ajax request
		jQuery.getJSON(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "ajax_markAsCompleted", oid: oid},
			function(data){
				if(data[0]) {
					jQuery('mark#order_status_'+oid).hide().html(data[2]).fadeIn(500);
					jQuery('mark#order_status_'+oid).attr('class', data[1]);
					// Hide loading and replace button!
					_this.attr('class', 'button markAsShipped order_'+oid).html(data['new_button_title']);
				}
				else {
					_this.removeClass('loading');
					alert(data[1]);
				}
			}
		);
	});
	
	// DUPLICATE A PRODUCT
	jQuery("a#duplicate_the_product").click(function(){
		var _this = jQuery(this);
		_this.attr('class', 'button');
		// Display loading...
		_this.addClass('loading');
		
		var pid = jQuery('input[name=pid]').val();
		
		jQuery.getJSON(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "duplicate_the_product", pid:pid},
			function(data){
				_this.removeClass('loading');
				if(data[0]) {
					_this.addClass('success');
				}
				else {
					_this.addClass('error');
					alert(data[1]);
				}
			}
		);
		
		return false;
	});
	
	// Ferme la boite de dialogue
	jQuery("input.closeAlert").live('click', function(){
		jQuery('.superBackground').remove();
		jQuery('.popupAlert').remove();
	});
	
	// Valide le numéro de suivi
	jQuery("input.sendTrackingNumber").live('click',function(){
		var oid = jQuery('input[name=oid]').val();
		var trackingNumber = jQuery('input[name=trackingNumber]').val();
		var _this = jQuery('a.order_'+oid);
		jQuery('.superBackground').remove();
		jQuery('.popupAlert').remove();
		
		// Display loading...
		_this.addClass('loading');
		
		// Start ajax request
		jQuery.getJSON(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "ajax_markAsShipped", oid: oid, trackingNumber: trackingNumber},
			function(data){
				if(data[0]) {
					jQuery('mark#order_status_'+oid).hide().html(data[2]).fadeIn(500);
					jQuery('mark#order_status_'+oid).attr('class', data[1]);
					// Hide loading!
					_this.remove();
				}
				else {
					_this.removeClass('loading');
					alert(data[1]);
				}
			}
		);
	});
	
	// Cache l'alerte utilisateur concernant la MAJ des fichiers de templates
	/*
	jQuery('a.hideTplVersionNotice').click(function() {
		jQuery(this).parent().parent().fadeOut(500);
		jQuery.getJSON(WPSHOP_AJAX_FILE_URL, { post: "true", elementCode: "ajax_hideTplVersionNotice"});
		return false;
	});
	*/
	
	/* Alerte lors de la décoche de l'utilisation de permaliens personnalisé */
	jQuery('input[type=checkbox][name=useSpecialPermalink]').click(function(){
		if(jQuery(this).prop('checked') == false) {
			return confirm(jQuery('input[type=hidden][name=useSpecialPermalink_confirmMessage]').val());
		}
		return true;
	});
	
	jQuery('a.show-hide-shortcodes').click(function() {
		var element = jQuery('.shortcodes_container', jQuery(this).parent());
		if(element.css('display')=='block'){
			jQuery(this).html('Afficher');
			element.hide(250);
		}
		else {
			element.show(250);
			jQuery(this).html('Cacher');
		}
		return false;
	});
	
});