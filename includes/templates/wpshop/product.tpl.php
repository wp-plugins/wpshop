<div id="product_main_information_container" >
	<div id="product_galery" >
		<?php _e($productThumbnail); ?>
		<?php _e($product_picture_galery); ?>
	</div>
	<div id="product_wp_initial_content" >
		<?php echo !empty($productPrice) ? '<h2>'.wpshop_tools::price($productPrice).' '.$productCurrency.'</h2>' : __('Unknown price','wpshop'); ?>
		<p><?php _e($initialContent); ?></p>
		<?php if(!empty($wpshop_shop_type) && ($wpshop_shop_type == 'sale')): ?>
			<?php if(!empty($productStock)): ?>
				<button type="button" id="wpshop_add_to_cart_<?php echo $product_id; ?>" class="wpshop_add_to_cart_button wpshop_products_listing_bton_panier_active"><?php _e('Add to cart', 'wpshop'); ?></button><span class="add2cart_loading"></span>
			<?php else: ?>
				<button type="button" disabled="disabled" class="no_stock"><?php _e('Soon available', 'wpshop'); ?></button>
			<?php endif; ?>
		<?php endif; ?>
		<div id="product_document_galery_container" ><?php _e($product_document_galery); ?></div>
	</div>	
</div>

<div id="product_attribute_container" ><?php _e($attributeContentOutput); ?></div>