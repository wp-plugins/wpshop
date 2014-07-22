<div class="wps-gridwrapper2-padded">
	<div>
		<?php if ( !empty($cart_type) && $cart_type == 'summary' && !$account_origin ) : ?>
			<?php $url_step_one = get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ); ?>
			<?php printf( __( 'You have forget an item ? <a href="%s">Modify your cart !</a>', 'wpshop'), $url_step_one ); ?>
		<?php else : ?>
			<?php if( !$account_origin ) : ?>
				<div class="wps-boxed"><?php echo do_shortcode('[wps_apply_coupon]');?></div>
				<?php else : ?>
				<button id="<?php echo $oid; ?>" class="wps-bton-first-mini-rounded make_order_again"><?php _e( 'Make this order again', 'wpshop'); ?></button>
			<?php endif; ?>
		<?php endif; ?>
		
	</div>
	<div>
		<div class="wps-boxed" style="min-height : 127px">
			<div class="wps-cart-resume-alignRight">
				<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_et' ) : ?>
				<p><?php _e( 'Shipping cost ET', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $shipping_cost_et ); ?></strong> <?php echo $currency; ?></span></p>
				<?php endif; ?>
				
				<?php if( $cart_option == 'full_cart' ) : ?>
				<p><?php _e( 'VAT on Shipping cost', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $shipping_cost_vat ); ?></strong> ?php echo $currency; ?></span></p>
				<?php endif; ?>
				
				<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' ) : ?>
				<p><?php _e( 'Shipping cost', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $shipping_cost_ati ); ?></strong> <?php echo $currency; ?></span></p>
				<?php endif; ?>
				
				<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_et' ) : ?>
				<p><?php _e( 'Total ET', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $total_et ); ?></strong> <?php echo $currency; ?></span></p>
				<?php endif; ?>
				
				<?php if( $cart_option == 'full_cart' ) : ?>
				<?php foreach( $cart_content['order_tva'] as $order_vat_rate => $order_vat_value ) : ?>
				<p><?php printf( __( 'VAT (%s %%)', 'wpshop'), $order_vat_rate); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $order_vat_value ); ?></strong> <?php echo $currency; ?></span></p>
				<?php endforeach; ?>
				<?php endif; ?>
				
				<?php if ( !empty( $cart_content['coupon_id']) ) : ?>
				<p><?php _e( 'Total ATI before discount', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $order_totla_before_discount ); ?></strong> <?php echo $currency; ?></span></p>
				<p><?php _e( 'Discount', 'wpshop'); ?> (<?php echo $coupon_title; ?>) <span class="wps-alignRight"><strong><?php echo $coupon_value; ?></strong><?php echo $currency; ?></span></p>
				<?php endif; ?>
				
				<?php if( !empty($_SESSION['cart']['order_partial_payment']) ) : 
					$wps_partial_payment_data = get_option( 'wpshop_payment_partial' );
					$partial_payment_informations = $wps_partial_payment_data['for_all'];
				?>	
					<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $cart_content['order_grand_total'] ); ?></strong> <?php echo $currency; ?></span></p>
					<p class="wps-hightlight">
					<?php printf(__('Payable now %s','wpshop'), '(' . $partial_payment_informations['value'] . ( ( !empty($partial_payment_informations['type']) && $partial_payment_informations['type'] == 'percentage' ) ? '%': wpshop_tools::wpshop_get_currency( false ) ) . ')'); ?>
					<span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $total_ati ); ?></strong> <?php echo $currency; ?>
					</span></p>
				<?php else : ?>
					<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $total_ati ); ?></strong> <?php echo $currency; ?></span></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' ) ) : ?>
<div>
	<button class="wps-bton-first-alignRight-rounded" id="wps-cart-order-action"><?php _e( 'Order', 'wpshop' ); ?></button>
	<button class="wps-bton-second-alignRight-rounded emptyCart"><?php _e( 'Empty the cart', 'wpshop' ); ?></button>
</div>
<div style="clear : both; text-align : right; padding : 8px 0; margin-top : 8px; font-size : 14px; font-style : italic; font-weight : bold; color : #C7CE06;"><span class="wps-mini-cart-free-shipping-alert"><?php echo wpshop_tools::create_custom_hook('wpshop_free_shipping_cost_alert'); ?></span></div> 
<?php endif; ?>




