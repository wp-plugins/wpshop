<div id="wps_cart_error_container" class="wps-gridwrapper wps-alert-error"></div>
<ul class="wps-fullcart">
	<li class="wps-clearfix cart_header">
		<div class="wps-cart-item-img"></div>
		
		<div class="wps-cart-item-content"><?php _e( 'Product name', 'wpshop'); ?></div>
		
		<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' ) : ?>
		<div class="wps-cart-item-unit-price"><?php _e( 'P.U', 'wpshop' ); ?></div>
		<?php endif; ?>
		<?php if( $cart_option == 'simplified_et' ) : ?>
		<div class="wps-cart-item-unit-price"><?php _e( 'Unit price ET', 'wpshop' ); ?></div>
		<?php endif; ?>
		
		<div class="wps-cart-item-quantity"><?php _e( 'Qty', 'wpshop'); ?></div>
		
		<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' ) : ?>
		<div class="wps-cart-item-price"><?php _e( 'Total', 'wpshop' ); ?></div>
		<?php endif; ?>
		<?php if( $cart_option == 'simplified_et' ) : ?>
		<div class="wps-cart-item-price"><?php _e( 'Total ET', 'wpshop' ); ?></div>
		<?php endif; ?>
		<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' ) ) : ?>
		<div class="wps-cart-item-close"></div>
		<?php endif; ?>
	</li>
	<?php 
		foreach( $cart_items as $item_id => $item ) :
		/** Check if it's a product or a variation **/
		$item_post_type = get_post_type( $item_id );
		$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($item['item_id'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
		$output_order = array();
		if ( count($product_attribute_order_detail) > 0  && is_array($product_attribute_order_detail) ) {
			foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
				foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
					if ( !empty($attribute_def->code) )
						$output_order[$attribute_def->code] = $position;
				}
			}
		}
		$variation_attribute_ordered = wpshop_products::get_selected_variation_display( $item['item_meta'], $output_order, 'cart' );
		ksort($variation_attribute_ordered['attribute_list']);
		$variations_indicator = '';
		if( !empty($variation_attribute_ordered['attribute_list']) ) {
			
			$variations_indicator .= '<ul>';
			foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) {
				if ( !empty($attribute_variation_to_output) ) {
					$variations_indicator .= $attribute_variation_to_output;
				}
			}
			$variations_indicator .= '</ul>';
			
		}
		if ( $item_post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
			$parent_def = wpshop_products::get_parent_variation( $item_id );
			$parent_post = $parent_def['parent_post'];
			$item_id = $parent_post->ID;
			$item_title =  $parent_post->post_title;
		}
		else {
			$item_title = $item['item_name'];
		}
		require( $this->get_template_part( "frontend", "cart/cart", "item") );
		endforeach;
	?>
</ul>
<?php require_once( $this->get_template_part( "frontend", "cart/cart", "total") ); ?>
<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' ) ) : ?>
<?php echo apply_filters( 'wps_cart_footer_extra_content', ''); ?>
<?php endif?>

