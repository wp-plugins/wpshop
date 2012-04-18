<li class="product_main_information_container-mini-grid <?php echo $product_class; ?>">
	<a href="<?php echo $product_link; ?>" title="<?php echo $product_title; ?>" >
		<span class="wpshop_mini_grid_thumbnail"><?php echo $productThumbnail; ?></span>
		<?php echo $product_new; ?>
		<h2><?php echo $product_title; ?></h2>
		<span class="wpshop_products_listing_price"><?php echo !empty($productPrice) ? wpshop_tools::price($productPrice).' '.$productCurrency : null; ?></span>
	</a>
	<?php if(!empty($productStock)): ?>
		<button type="button" id="wpshop_add_to_cart_<?php echo $product_id; ?>" class="wpshop_add_to_cart_button wpshop_products_listing_bton_panier_active">Ajouter au panier</button><div class="loading"></div>
		<?php else: ?>
			<button type="button" disabled="disabled" class="no_stock">Bientôt disponible</button>
		<?php endif; ?>
</li>