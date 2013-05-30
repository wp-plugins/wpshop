jQuery(document).ready(function() {
	//jQuery('#filter_search_action').ajaxForm();
	
	jQuery('.filter_search_element').live('slidestop', function() {
		make_filter_search_request ();
	});
	
	
	
	jQuery('.filter_search_element').live('change', function() {
		make_filter_search_request ();
	});

	
	
	function make_filter_search_request () {
		jQuery('#filter_search_action').ajaxForm({
			beforeSubmit : function() {
				jQuery('.container_product_listing').html('<div class="wpshop_loading_picture"></div>');
			},
			complete: function(xhr) {
				jQuery('.wpshop_products_block').html(xhr.responseText);
			}
		}	
		).submit();
	}
	
});