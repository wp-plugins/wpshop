<div id="product_main_information_container" >
	<div id="product_galery" ><?php _e($productThumbnail); ?></div>
	<div id="product_wp_initial_content" >
		<?php echo !empty($productPrice) ? '<h2>'.wpshop_tools::price($productPrice).' &#8364;</h2>' : __('Unknown price','wpshop').'<br />'; ?>
		<?php _e($initialContent); ?>
	</div>
	<?php if(!empty($productStock)): ?>
		<input type="hidden" value="<?php echo $product_id; ?>" name="product_id" />
		<input type="button" value="Ajouter au panier" name="addToCart" /><div class="loading"></div>
	<?php else: ?>
		Pas de stock
	<?php endif; ?>
</div>
<div id="product_picture_galery_container" ><?php _e($product_picture_galery); ?></div>
<div id="product_document_galery_container" ><?php _e($product_document_galery); ?></div>
<div id="product_attribute_container" ><?php _e($attributeContentOutput); ?></div>