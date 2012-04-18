<div id="product_main_information_container" >
	<div id="product_galery" ><?php _e($productThumbnail); ?><?php _e($product_picture_galery); ?></div>
	<div id="product_wp_initial_content" >
		<?php echo !empty($productPrice) ? '<h2>'.wpshop_tools::price($productPrice).' '.$productCurrency.'</h2>' : __('Unknown price','wpshop'); ?>
		<p><?php _e($initialContent); ?></p>
		<?php if(!empty($productStock)): ?>
		<button type="button" id="wpshop_add_to_cart_<?php echo $product_id; ?>" class="wpshop_add_to_cart_button wpshop_products_listing_bton_panier_active">Ajouter au panier</button><div class="loading"></div>
		<?php else: ?>
			<button type="button" disabled="disabled" class="no_stock">Bientôt disponible</button>
		<?php endif; ?>
		<div id="product_document_galery_container" ><?php _e($product_document_galery); ?></div>
	</div>	
</div>

<div id="product_attribute_container" ><?php _e($attributeContentOutput); ?></div>