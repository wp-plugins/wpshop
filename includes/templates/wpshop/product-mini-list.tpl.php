<li class="product_main_information_container-mini-list clearfix wpshop_clear <?php echo $product_class; ?>">
	<?php echo $product_new; ?>
		<a href="<?php echo $product_link; ?>" class="product_thumbnail-mini-list" title="<?php echo $product_title; ?>">
			<?php echo $productThumbnail; ?>
		</a>
		<span class="product_information-mini-list">
			<a href="<?php echo $product_link; ?>" title="<?php echo $product_title; ?>" class="clearfix">
				<h2><?php echo $product_title; ?></h2>
				<span class="wpshop_products_listing_price"><?php echo !empty($productPrice) ? wpshop_tools::price($productPrice).' '.$productCurrency : null; ?></span>
				<p class="wpshop_liste_description"><?php echo $product_more_informations; ?></p>
			</a>
			<?php if(!empty($productStock)): ?>
				<button type="button" id="wpshop_add_to_cart_<?php echo $product_id; ?>" class="wpshop_add_to_cart_button wpshop_products_listing_bton_panier_active"><?php _e('Add to cart', 'wpshop'); ?></button><div class="loading"></div>
			<?php else: ?>
				<button type="button" disabled="disabled" class="no_stock"><?php _e('Soon available', 'wpshop'); ?></button>
			<?php endif; ?>
		</span>
</li>