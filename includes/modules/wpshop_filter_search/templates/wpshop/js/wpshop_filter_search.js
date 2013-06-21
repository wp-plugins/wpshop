jQuery(document).ready(function() {
	jQuery(".chzn-select").chosen();
	
	jQuery('#wpshop_filter_search_container').on('slidestop', '.filter_search_element', function() {
		make_filter_search_request ();
	});
	
	jQuery('#wpshop_filter_search_container').on('change', '.filter_search_element', function() {
		make_filter_search_request ();
	});
	
	jQuery('#wpshop_filter_search_container').on('change', '.chzn-select', function() {
		make_filter_search_request ();
	});
	

	jQuery('#wpshop_filter_search_container').on('click', '#init_fields', function() {
		jQuery('#filter_search_action select').each( function() {
			jQuery( this ).removeAttr('selected');
			var id = jQuery(this).attr('id');
			jQuery("#" + id).val("").trigger("liszt:updated");
		});
		
		jQuery('.ui-slider').each( function() {
			var id_slider  = jQuery(this).attr('id');
			var attribute_name = id_slider.replace('slider', '');
			
			jQuery('#' + id_slider ).slider('values', 0, jQuery('#basic_min_value' + attribute_name).val());
			jQuery('#' + id_slider ).slider('values', 1, jQuery('#basic_max_value' + attribute_name).val());
			
			jQuery('#amount_min' + attribute_name ).val( jQuery('#basic_min_value' + attribute_name).val() );
			jQuery('#amount_max' + attribute_name ).val( jQuery('#basic_max_value' + attribute_name).val() );
			
			jQuery('#amount_min_indicator' + attribute_name ).html( jQuery('#basic_min_value' + attribute_name).val() );
			jQuery('#amount_max_indicator' + attribute_name ).html( jQuery('#basic_max_value' + attribute_name).val() );
			
		});
		
		make_filter_search_request ();
	});
	
	
	function make_filter_search_request () {
		jQuery('#filter_search_action').ajaxForm({
			dataType: 'json',
			beforeSubmit : function() {
				jQuery('.container_product_listing').html('<div class="wpshop_loading_picture"></div>');
			},
			success: function(response) {
				jQuery('.wpshop_products_block').html(response['result']);
				jQuery('#wpshop_filter_search_count_products').html( response['products_count'] );
			}
		}	
		).submit();
	}
	
});