<li class="wps-clearfix" id="wps_product_<?php echo $product_key; ?>">
	<div class="wps-cart-item-img">
		<?php if ( !$auto_added_product ) : ?><a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>"><?php endif; ?>
			<?php echo get_the_post_thumbnail($item['item_id'], 'thumbnail', array('class' => 'wps-circlerounded')); ?>
		<?php if ( !$auto_added_product ) : ?></a><?php endif; ?>
	</div>
	<div class="wps-cart-item-content">
		<?php if ( !$auto_added_product && get_post_status( $item_id ) != 'free_product' ) : ?><a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>"><?php endif; ?>
			<?php echo $item_title; ?>
		<?php if ( !$auto_added_product && get_post_status( $item_id ) != 'free_product' ) : ?></a><?php endif; ?>

		<?php echo $variations_indicator; ?>

		<?php if ( !empty( $cart_content ) && !empty( $cart_content[ 'order_status' ] ) && ( 'completed' == $cart_content[ 'order_status' ] ) && ( empty($cart_type) || ( !empty($cart_type) && $cart_type == 'summary' ) ) ) : ?>
			<?php echo $download_link; ?>
		<?php endif; ?>
	</div>

	<?php if( $cart_option == 'simplified_et' ) : ?>
	<div class="wps-cart-item-unit-price">
		<span class="wps-price">
		<?php echo wpshop_tools::formate_number( $item['item_pu_ht'] ); ?><span><?php echo $currency; ?></span>
		</span>
	</div>
	<?php endif; ?>

	<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' ) : ?>
	<div class="wps-cart-item-unit-price">
		<span class="wps-price"> <?php echo wpshop_tools::formate_number( $item['item_pu_ttc'] ); ?><span><?php echo $currency; ?></span></span>
	</div>
	<?php endif; ?>

	<div class="wps-cart-item-quantity">
		<?php if ( ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' ) ) && !$auto_added_product  ) : ?>
			<?php if( ( $cart_type != 'admin-panel' || ( $cart_type == 'admin-panel' && ( empty( $cart_content['order_status'] ) || $cart_content['order_status'] == 'awaiting_payment' ) ) ) ) : ?>
				<a href="" class="wps-bton-icon-plus-small wps-cart-add-product-qty"></a>
				<input type="text" name="french-hens" id="wps-cart-product-qty-<?php echo $product_key; ?>" value="<?php echo $item['item_qty']; ?>" class="wps-circlerounded wps-cart-product-qty">
				<a href="" class="wps-bton-icon-minus-small wps-cart-reduce-product-qty"></a>
				<?php else : ?>
					<?php echo $item['item_qty']; ?>
				<?php endif;?>
		<?php elseif ( $auto_added_product ) : ?>
			1
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
		<?php if( $cart_type != 'admin-panel' || ( $cart_type == 'admin-panel' && ( empty( $cart_content['order_status'] ) || $cart_content['order_status'] == 'awaiting_payment' ) ) ) : ?>
		<?php if ( !$auto_added_product ) : ?><button type="button" class="wps-bton-icon-close wps_cart_delete_product" id="wps-close-<?php echo $product_key; ?>"></button><?php endif; ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>

</li>
