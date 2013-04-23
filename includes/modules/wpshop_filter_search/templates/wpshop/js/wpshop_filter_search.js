jQuery(document).ready(function() {
	jQuery('#filter_search_action').ajaxForm();
	
	jQuery('.filter_search_element').on('slidestop', function() {
		jQuery('#filter_search_action').ajaxForm({
			beforeSubmit : function() {
				jQuery('.container_product_listing').html('<div class="wpshop_loading_picture"></div>');
			},
			complete: function(xhr) {
				jQuery('.container_product_listing').html(xhr.responseText);
			}
		}	
		).submit();
	});
	
	
	
	jQuery('.filter_search_element').on('change', function() {
		jQuery('#filter_search_action').ajaxForm({
			beforeSubmit : function() {
				jQuery('.container_product_listing').html('<div class="wpshop_loading_picture"></div>');
			},
			complete: function(xhr) {
				jQuery('.container_product_listing').html(xhr.responseText);
			}
		}	
		).submit();
	});
	
	
	
});