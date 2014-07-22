<li class="wps-clearfix" id="wps_product_<?php echo $item['item_id']; ?>">
	<div class="wps-cart-item-img">
		<a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>">
			<?php echo get_the_post_thumbnail($item_id, 'thumbnail', array('class' => 'wps-circlerounded')); ?>
		</a>
	</div>
	<div class="wps-cart-item-content">
		<a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>"><?php echo $item_title; ?></a><?php echo $variations_indicator; ?>
	</div>
	
	<?php if( $cart_option == 'simplified_et' ) : ?>
	<div class="wps-cart-item-unit-price">
		<span class="wps-price"> <?php echo wpshop_tools::formate_number( $item['item_pu_ht'] ); ?><span><?php echo $currency; ?></span></span>
	</div>
	<?php endif; ?>
	
	<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' ) : ?>
	<div class="wps-cart-item-unit-price">
		<span class="wps-price"> <?php echo wpshop_tools::formate_number( $item['item_pu_ttc'] ); ?><span><?php echo $currency; ?></span></span>
	</div>
	<?php endif; ?>
	
	<div class="wps-cart-item-quantity">
		<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' ) ) : ?>
		<a href="" class="wps-bton-icon-plus-small wps-cart-add-product-qty"></a>							
		<input type="text" name="french-hens" id="wps-cart-product-qty-<?php echo $item['item_id']; ?>" value="<?php echo $item['item_qty']; ?>" class="wps-circlerounded wps-cart-product-qty">
		<a href="" class="wps-bton-icon-minus-small wps-cart-reduce-product-qty"></a>
		<?php else : ?>
			<?php echo $item['item_qty']; ?>
		<?php endif; ?>
	</div>
	
	
	
	<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_et' ) : ?>
	<div class="wps-cart-item-price">
    	<span class="wps-price"> <?php echo wpshop_tools::formate_number( $item['item_total_ht'] ); ?><span><?php echo $currency; ?></span></span>
    	<span class="wps-tva"><?php _e( 'ET', 'wpshop'); ?></span>
	</div>
	<?php endif; ?>
	
	<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' ) : ?>
	<div class="wps-cart-item-price">
    	<span class="wps-price"> <?php echo wpshop_tools::formate_number( $item['item_total_ttc'] ); ?><span><?php echo $currency; ?></span></span>
	</div>
	<?php endif; ?>
	
	
	<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' ) ) : ?>
	<div class="wps-cart-item-close">
		<button type="button" class="wps-bton-icon-close wps_cart_delete_product" id="wps-close-<?php echo $item['item_id']; ?>"></button>
	</div>
	<?php endif; ?>
		
</li>
