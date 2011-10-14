/*	Define the jQuery noConflict var for the plugin	*/
var wpshop = jQuery.noConflict();

/*	Action launched directly after the page is load	*/
wpshop(document).ready(function(){
	jQuery("#superTab").tabs();
	
	jQuery("#insert_products").click(function(){
		var string='';
		jQuery('ul#products_selected input:checked').each(function() {
			var display_type = jQuery('input[type=radio][name=product_display_type]:checked').attr('value');
			string += '[wpshop_product pid="'+jQuery(this).val()+'" type="'+display_type+'"]';
		});
		addTextareaContent(string);
	});
	
	jQuery("#insert_attr").click(function(){
		var string='';
		jQuery('ul#attr_selected input:checked').each(function() {
			var data = jQuery(this).val().split('-');
			string += '[wpshop_att_val type="'+data[2]+'" attid="'+data[1]+'" pid="'+data[0]+'"]';
		});
		addTextareaContent(string);
	});
	
	jQuery("#insert_groups").click(function(){
		var string='';
		jQuery('ul#groups_selected input:checked').each(function() {
			var data = jQuery(this).val().split('-');
			string += '[wpshop_att_group pid="'+data[0]+'" sid="'+data[1]+'"]';
		});
		addTextareaContent(string);
	});
	
	jQuery("#search_products").keyup(function() {
		var search_string = jQuery(this).val();
		if (search_string.length>2) {
			jQuery.get("../wp-content/plugins/wpshop/includes/ajax.php", { post: "true", elementCode: "speedSearch", searchType: "products", search: search_string },
				function(data){jQuery('ul#products_selected').html(data);}
			);
		}
		else if (search_string.length==0) {
			jQuery.get("../wp-content/plugins/wpshop/includes/ajax.php", { post: "true", elementCode: "speedSearch", searchType: "products", search: "" },
				function(data){jQuery('ul#products_selected').html(data);}
			);
		}
	});
	
	jQuery("#search_attr").keyup(function() {
		var search_string = jQuery(this).val();
		if (search_string.length>2) {
			jQuery.get("../wp-content/plugins/wpshop/includes/ajax.php", { post: "true", elementCode: "speedSearch", searchType: "attr", search: search_string },
				function(data){jQuery('ul#attr_selected').html(data);}
			);
		}
		else if (search_string.length==0) {
			jQuery.get("../wp-content/plugins/wpshop/includes/ajax.php", { post: "true", elementCode: "speedSearch", searchType: "attr", search: "" },
				function(data){jQuery('ul#attr_selected').html(data);}
			);
		}
	});
	
	jQuery("#search_groups").keyup(function() {
		var search_string = jQuery(this).val();
		if (search_string.length>2) {
			jQuery.get("../wp-content/plugins/wpshop/includes/ajax.php", { post: "true", elementCode: "speedSearch", searchType: "groups", search: search_string },
				function(data){jQuery('ul#groups_selected').html(data);}
			);
		}
		else if (search_string.length==0) {
			jQuery.get("../wp-content/plugins/wpshop/includes/ajax.php", { post: "true", elementCode: "speedSearch", searchType: "groups", search: "" },
				function(data){jQuery('ul#groups_selected').html(data);}
			);
		}
	});
	
	function addTextareaContent(string) {
		jQuery("#content").append(string);
		jQuery("#tinymce",jQuery("#content_ifr").contents()).append(string);
	}
});