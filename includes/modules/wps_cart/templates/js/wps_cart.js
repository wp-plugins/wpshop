jQuery( document ).ready( function() {
	
	jQuery( '#wps_cart_error_container' ).hide();
	
	/** Product Qty Management in cart **/
	jQuery( document ).on( 'click',  '.wps-cart-reduce-product-qty', function(e) {
		e.preventDefault();
		var li_element = jQuery(this).closest( 'li' );
		var product_id = li_element.attr( 'id' ).replace( 'wps_product_', '' );
		var qty = jQuery( '#wps-cart-product-qty-' + product_id ).val();
		qty = parseInt( qty ) - 1;
		change_product_qty_in_cart( product_id, qty);
	});
	
	
	/** Product Qty Management in cart **/
	jQuery( document ).on( 'click',  '.wps-cart-add-product-qty', function(e) {
		e.preventDefault();
		var li_element = jQuery(this).closest( 'li' );
		var product_id = li_element.attr( 'id' ).replace( 'wps_product_', '' );
		var qty = jQuery( '#wps-cart-product-qty-' + product_id ).val();
		qty = parseInt( qty ) + 1;
		change_product_qty_in_cart( product_id, qty);
	});
	
	/** Delete product **/
	jQuery( document ).on( 'click', '.wps_cart_delete_product', function(e) {
		e.preventDefault();
		var li_element = jQuery(this).closest( 'li' );
		var product_id = li_element.attr( 'id' ).replace( 'wps_product_', '' );
		change_product_qty_in_cart( product_id, 0 );
	});
	
	/** Delete product **/
	jQuery( document ).on( 'click', '.wps_mini_cart_delete_product', function(e) {
		e.preventDefault();
		var li_element = jQuery(this).closest( 'li' );
		var product_id = li_element.attr( 'id' ).replace( 'wps_min_cart_product_', '' );
		change_product_qty_in_cart( product_id, 0 );
	});
	
	/** Apply Coupon Action **/
	jQuery( document ).on( 'click', '#wps_apply_coupon', function(e) {
		e.preventDefault();
		jQuery( '#wps_coupon_alert_container' ).hide();
		var data = {
				action: "wps_apply_coupon", 
				coupon_code : jQuery( '#wps_coupon_code' ).val()
			};
			jQuery.post(ajaxurl, data, function(response){
				if ( response['status'] ) {
					jQuery( '#wps_coupon_alert_container' ).html( response['response'] ).slideDown( 'slow' ).delay( 3000 ).slideUp( 'slow' );
					jQuery( '#wps_coupon_code' ).val( '' );
					reload_wps_cart();
					reload_mini_cart();
				}
				else {
					jQuery( '#wps_coupon_alert_container' ).html( response['response'] ).slideDown( 'slow' ).delay( 4000 ).slideUp( 'slow' );
					jQuery( '#wps_coupon_code' ).val( '' );
				}
		}, 'json');
	});
	
	
	jQuery( document ).on( 'click', '#wps-cart-order-action', function() {
		jQuery( this ).addClass( 'wps-bton-loading' );
		var data = {
				action: "wps_cart_pass_to_step_two"
			};
			jQuery.post(ajaxurl, data, function(response){
				if( response['status'] ) {
					window.location.replace( response['response'] );
				}
				else {
					jQuery( '#wps_cart_error_container' ).html( response['response']).slideDown( 'slow' ).delay( 3500 ).slideUp( 'slow' );
					jQuery( this ).removeClass( 'wps-bton-loading' );
				}
			}, 'json');	
	});
	
	jQuery( document ).on( 'keyup', '.wps-cart-product-qty', function() {
		var pid = jQuery( this ).attr('id').replace( 'wps-cart-product-qty-', '' );
		var qty = jQuery( this ).val();
		if( jQuery.isNumeric( qty ) ) {
			change_product_qty_in_cart( pid, qty );
		}
 	});
	
	
	
	/** Change product Qty in cart **/
	function change_product_qty_in_cart( product_id, product_qty ) {
		var data = {
				action: "wpshop_set_qtyfor_product_into_cart",
				product_id: product_id,
				product_qty: product_qty,
			};
			jQuery.post(ajaxurl, data, function(response){
				if(response[0] == 'success') {
					reload_wps_cart();
					reload_mini_cart();
					reload_summary_cart();
				}
				else {
					jQuery( '#wps_cart_error_container' ).html( response[0] );
					jQuery( '#wps_cart_error_container' ).slideDown( 'slow' ).delay( 3500 ).slideUp( 'slow' );
				}
			}, 'json');
	}
	
	
	jQuery( document ).on( 'click', '.emptyCart', function() {
		jQuery( this ).addClass( 'wps-bton-loading' );
		var data = {
				action: "wps_empty_cart"
			};
			jQuery.post(ajaxurl, data, function(response){
				if(response['status']) {
					reload_wps_cart();
					reload_mini_cart();
					reload_summary_cart();
				}
				jQuery( '.emptyCart' ).removeClass( 'wps-bton-loading' );
			}, 'json');	
	});	
	
	
});




/** Reload cart action **/
function reload_wps_cart() {
	var data = {
			action: "wps_reload_cart"
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery( '#wps_cart_container').animate({'opacity' : 0.1}, 450, function() {
				jQuery( '#wps_cart_container').delay( 500 ).html( response['response']);
				jQuery( '#wps_cart_error_container' ).hide();
				jQuery( '#wps_cart_container').delay( 200 ).animate({'opacity' : 1}, 450 );
			});
	}, 'json');
}

/** Reload Mini cart **/
function reload_mini_cart() {
	var data = {
			action: "wps_reload_mini_cart"
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery( '.wps-mini-cart-body').animate({'opacity' : 0.1}, 450, function() {
				jQuery( '.wps-mini-cart-body').delay( 500 ).html( response['response']);
				jQuery( '.wps-mini-cart-body').delay( 200 ).animate({'opacity' : 1}, 450 );
				jQuery( '.wps-mini-cart-free-shipping-alert' ).fadeOut( 'slow' ).html( response['free_shipping_alert']).fadeIn( 'slow' );
			});
			
			jQuery( '.wps-numeration-cart').html( response['count_items'] );
	}, 'json');
}
/** Reload Summary Cart **/
function reload_summary_cart() {
	var data = {
			action: "wps_reload_summary_cart"
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery( '#wps_resume_cart_container').animate({'opacity' : 0.1}, 450, function() {
				jQuery( '#wps_resume_cart_container').delay( 500 ).html( response['response']);
				jQuery( '#wps_resume_cart_container').delay( 200 ).animate({'opacity' : 1}, 450 );
			});

	}, 'json');
}