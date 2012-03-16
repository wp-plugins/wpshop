<div class="product_main_information_container-mini-list wpshop_clear">
	<?php
		$datetime = date('Y-m-d H:i:s');
		if($product_declare_new==='Yes' && $datetime >= $product_set_new_from && $datetime <= $product_set_new_to) {
			echo '<span class="vignette_nouveaute">'.__('New','wpshop').'</span>';
		}
	?>
	<a href="<?php echo $product_link; ?>" >
		<div class="product_thumbnail-mini-list" ><?php echo $productThumbnail; ?></div>
		<div class="product_information-mini-list" >
			<div class="product_title-mini-list" ><?php echo $product_title; ?> <?php echo !empty($productPrice) ? '- '.wpshop_tools::price($productPrice).' '.$productCurrency : null; ?></div>
			<div class="product_more_mini-list" ><?php echo $product_more_informations; ?></div>
			<input type="hidden" value="<?php echo $product_id; ?>" name="product_id" />
			<input type="button" value="Ajouter au panier" name="addToCart" /><div class="loading"></div>
		</div>
	</a>
</div>