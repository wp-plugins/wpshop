<div class="product_main_information_container-mini-grid" style="width:<?php echo $item_width; ?>;" >
	<?php
		$datetime = date('Y-m-d H:i:s');
		if($product_declare_new==='Yes' && $datetime >= $product_set_new_from && $datetime <= $product_set_new_to) {
			echo '<span class="vignette_nouveaute">'.__('New','wpshop').'</span>';
		}
	?>
	<a href="<?php echo $product_link; ?>" >
		<div class="product_thumbnail-mini-grid" ><?php echo $productThumbnail; ?></div>
		<div class="product_information-mini-grid" >
			<div class="product_title-mini-grid" ><?php echo wpshop_tools::trunk($product_title,25); ?> <?php echo !empty($productPrice) ? '- '.wpshop_tools::price($productPrice).' '.$productCurrency : null; ?></div>
			<?php if(!empty($productStock)): ?>
			<input type="hidden" value="<?php echo $product_id; ?>" name="product_id" />
			<input type="button" value="Ajouter au panier" name="addToCart" /><div class="loading"></div>
			<?php else: ?>
			Pas de stock
			<?php endif; ?>
		</div>
	</a>
</div>