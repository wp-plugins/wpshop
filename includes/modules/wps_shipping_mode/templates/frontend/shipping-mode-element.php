<?php if( !empty( $shipping_mode['active'] ) ) : ?>
<li>
	<?php 
	$free_shipping_cost_alert = '';
	$currency = wpshop_tools::wpshop_get_currency();
	$cart_items = ( !empty($_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items'])  ) ? $_SESSION['cart']['order_items'] : '';
	$price_piloting = get_option( 'wpshop_shop_price_piloting' );
	if( !empty($cart_items) ) {
		$cart_weight = wpshop_shipping::calcul_cart_weight( $cart_items );
		$total_cart_ht_or_ttc_regarding_config = ( !empty($price_piloting) && $price_piloting == 'HT' )  ? $_SESSION['cart']['order_total_ht'] : $_SESSION['cart']['order_total_ttc'];
		$total_shipping_cost_for_products = wpshop_shipping::calcul_cart_items_shipping_cost( $cart_items );
		$shipping_cost = wpshop_shipping::get_shipping_cost( count( $cart_items ), $total_cart, $total_shipping_cost, $total_weight, $shipping_mode_id ).' '.$currency;
	}
	
	if (  !empty($shipping_mode['free_from']) ) {
		$order_amount = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? number_format((float)$_SESSION['cart']['order_total_ht'], 2, '.', '') : number_format((float)$_SESSION['cart']['order_total_ttc'], 2, '.', '');
		if ( $order_amount  < $shipping_mode['free_from'] ) {
			$free_in = ($shipping_mode['free_from'] - $order_amount);
			$shipping_cost .= '<br/>'.sprintf(__('Free in %s', 'wpshop'), $free_in. ' ' . $currency);
		}
		else {
			$shipping_cost = '<span class="wps-badge-vert">'.__('Free shipping cost', 'wpshop').'</span>';
		}
	}
	
	?>



	<div class="wps-list-expander-header">
		<span>
			<input type="radio" name="wps-shipping-method" value="<?php echo $shipping_mode_id; ?>" id="<?php echo $shipping_mode_id ; ?>" > <?php apply_filters( 'wps-extra-fields-'.$shipping_mode_id, '' ); ?>
		</span>
		<div class="wps-gridwrapper4">
			<div>
				<span class="wps-shipping-method-logo">
				<?php echo ( !empty($shipping_mode['logo']) ? wp_get_attachment_image( $shipping_mode['logo'], 'thumbnail' ): '' ); ?>
				</span>
			</div>
			<div>
				<span class="wps-shipping-method-name"><?php _e( $shipping_mode['name'], 'wpshop' ); ?></span>
			</div>
			<div>
				<span class="wps-shipping-method-explanation"><?php _e( $shipping_mode['explanation'], 'wpshop' ); ?></span>
			</div>
			<div>
				<span class="wps-shipping-method-price"><?php echo $shipping_cost; ?></span>
			</div>
		</div>
	</div>
	
	
	<div class="wps-list-expander-content" id="container_<?php echo $shipping_mode_id ; ?>"></div>
</li>




<?php endif; ?>
