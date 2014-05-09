<h3 class="wps-h3"><?php _e( 'Cart summary', 'wpshop' ); ?></h3>
<ul class="wps-fullcart">
	<?php foreach( $cart_items as $item_id => $item ) : 
			$item_post_type = get_post_type( $item_id );
			if ( $item_post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
				$parent_def = wpshop_products::get_parent_variation( $item_id );
				$parent_post = $parent_def['parent_post'];
				$item_id = $parent_post->ID;
				$item_title =  $parent_post->post_title;
			}
			else {
				$item_title = $item['item_name'];
			}
			?>
			
			<li class="wps-clearfix">
				<div class="wps-cart-item-img">
					<a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>">
						<?php echo get_the_post_thumbnail( $item_id, 'thumbnail', array('class' => 'wps-circlerounded') ); ?>
					</a>
				</div>
				<div class="wps-cart-item-content">
					<a href="<?php echo get_permalink( $item_id ); ?>" title="<?php echo $item_title; ?>">
						<?php echo $item_title; ?>
					</a>										
				</div>
				<div class="wps-cart-item-price">
			    	<span class="wps-price"><?php echo wpshop_tools::formate_number( $item['item_total_ttc'] ); ?> <span><?php echo $currency; ?></span></span>
			    	<span class="wps-tva"><?php _e( 'ATI', 'wpshop'); ?></span><br>
				</div>							
			</li>
	<?php endforeach; ?>
</ul>
<p><?php _e( 'Shipping cost ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $shipping_cost_ati ); ?></strong><?php echo $currency; ?></span></p>
<?php if ( !empty( $cart_content['coupon_id']) ) : ?>
	<p><?php _e( 'Total ATI before discount', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $order_total_before_discount ); ?></strong><?php echo $currency; ?></span></p>
	<p><?php _e( 'Discount', 'wpshop'); ?><span class="wps-inline-alignRight"><strong><?php echo wpshop_tools::formate_number( $coupon_value ); ?></strong><?php echo $currency; ?></span></p>
<?php endif; ?>
<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-inline-alignRight"><strong><?php echo wpshop_tools::formate_number( $total_ati ); ?></strong><?php echo $currency; ?></span></p>