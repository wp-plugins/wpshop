jQuery(document).ready(function() {
	/*
	jQuery.address.init( function( event ) {
		construct_filter_with_deep_link( jQuery.address.value() );
	}); 
	*/
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
		/** Deep linking creation **/
		var ad = construct_link_for_deep_linking();	
		//jQuery.address.value( ad );
		
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
	
	/** Construct Filter interface choices with the deep link **/
	function construct_filter_with_deep_link ( link ) {
		var parameters = jQuery.address.parameterNames();
		for( i = 0; i < parameters.length; i++ ) {
			if ( parameters[i] != '' ) {
				if ( jQuery('#' + parameters[i]).is('input') ) {
					//jQuery('#' + parameters[i]).val( jQuery.address.parameter( parameters[i]) );
				}
				
				if( jQuery('#' + parameters[i]).is('select') ) {
					//jQuery('#' + parameters[i] + ' option[value=' + jQuery.address.parameter( parameters[i]) + ']').attr('selected' , 'selected');
				}
				
			}
		}
	}
	
	/** Construct a link with filter parameters **/
	function construct_link_for_deep_linking() {
		var link = '?';
		jQuery('#filter_search_action input').each(function() {
			if ( jQuery( this ).val() != '' && jQuery(this).attr('id') != undefined ) {
				link += jQuery( this ).attr('id') + '=' + jQuery(this).val() + '&';
			}
		});
		
		var length_select = jQuery('#filter_search_action select').length;
		var i = 1;
		jQuery('#filter_search_action select').each(function() {
			var id = jQuery(this).attr('id');
			var id_field = '#' + id;
			if ( id != undefined ) {
				if ( jQuery(id_field).val() != null && jQuery(id_field).val() != 'all_attribute_values' &&  jQuery(id_field).val() != undefined) {
					link += id + '=' + jQuery(id_field).val();
					if ( i < length_select ) {
						link += '&';
					}
				}
			}
			i++;
		});
		
		return link;
	}
	
});