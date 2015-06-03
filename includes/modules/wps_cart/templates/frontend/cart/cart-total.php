<div class="wps-gridwrapper2-padded">
	<div>
		<?php if( $cart_type != 'admin-panel' ) : ?>
			<?php if ( !empty($cart_type) && $cart_type == 'summary' && !$account_origin ) : ?>
				<?php $url_step_one = get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ); ?>
				<?php printf( __( 'You have forget an item ? <a href="%s">Modify your cart !</a>', 'wpshop'), $url_step_one ); ?>
			<?php else : ?>
				<?php if( !$account_origin ) :
							echo do_shortcode('[wps_apply_coupon]');
						else : ?>
					<button id="<?php echo $oid; ?>" class="wps-bton-first-mini-rounded make_order_again"><?php _e( 'Make this order again', 'wpshop'); ?></button>
				<?php endif; ?>
				<?php if( !empty($tracking) ) : ?>
					<p><br />
					<?php if( !empty($tracking['number']) ) : ?>
						<strong><?php _e('Tracking number','wpshop'); ?> :</strong> <?php _e($tracking['number']); ?><br />
					<?php endif; ?>
					<?php if( !empty($tracking['link']) ) : ?>
						<?php /** Check if http:// it's found in the link */ 
						$url = $tracking['link'];
						if('http://' != substr($url, 0, 7))
							$url = 'http://' . $url;
						?>
						<a class="wps-bton-fourth-mini-rounded" href="<?php echo $url; ?>" target="_blank"><?php _e('Tracking link','wpshop'); ?></a>
					<?php endif; ?>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
		&nbsp;
	</div>
	<div>


		<div class="wps-boxed" style="min-height : 127px">
			<?php $shipping_price_from = get_option( 'wpshop_shipping_cost_from' ); ?>
			
			
			<!--	Recap shipping	-->
			<div class="wps-cart-resume-alignRight">
			<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_et' || $price_piloting == 'HT' ) : ?>
				<p>
					<?php _e( 'Shipping cost ET', 'wpshop'); ?> <?php echo ( ( !empty($shipping_price_from) && empty( $_SESSION['shipping_address'] ) ) ? '<br/><i>('.__( 'From', 'wpshop').')</i>' : '' ); ?>
					<span class="wps-alignRight">
						<?php if( $cart_type != 'admin-panel' ) : ?>
							<strong><?php echo wpshop_tools::formate_number( $shipping_cost_et ); ?></strong> <?php echo $currency; ?>
						<?php else : ?>
							<?php if( ( empty( $cart_content['order_status'] ) || ( $cart_content['order_status'] == 'awaiting_payment' ) ) && $price_piloting == 'HT' ) : ?>
								<span class="wps-form-group">
									<span class="wps-form">
										<input type="text" size="5" value="<?php echo number_format( $shipping_cost_et, 2 ); ?>" id="wps-orders-shipping-cost" class="wps-error" style="text-align : right" />
									</span>
								</span>
							<?php else : ?>
								<strong><?php echo wpshop_tools::formate_number( $shipping_cost_et ); ?> <?php echo wpshop_tools::wpshop_get_currency(); ?></strong>
							<?php endif; ?>
						<?php endif; ?>
					</span>
				</p>
			<?php endif; ?>
			
			<?php if( $cart_option == 'full_cart' ) : ?>
				<p>
					<?php _e( 'VAT on Shipping cost', 'wpshop'); ?>
					<span class="wps-alignRight">
						<strong><?php echo wpshop_tools::formate_number( $shipping_cost_vat ); ?></strong> <?php echo $currency; ?>
					</span>
				</p>
			<?php endif; ?>
			
			<?php if( $cart_option == 'full_cart' || $cart_option == 'simplified_ati' || $price_piloting == 'TTC' ) : ?>
				<p>
					<?php _e( 'Shipping cost', 'wpshop'); ?> <?php echo ( ( !empty($shipping_price_from) && empty( $_SESSION['shipping_address'] ) ) ? '<br/><i>('.__( 'From', 'wpshop').')</i>' : '' ); ?>
					<span class="wps-alignRight">
						<?php if( $cart_type != 'admin-panel' ) : ?>
							<strong><?php echo wpshop_tools::formate_number( $shipping_cost_ati ); ?></strong> <?php echo $currency; ?>
						<?php else : ?>
							<?php if( ( empty( $cart_content['order_status'] ) || ( $cart_content['order_status'] == 'awaiting_payment' ) ) && $price_piloting == 'TTC' ) : ?>
								<span class="wps-form-group">
									<span class="wps-form">
										<input type="text" size="5" value="<?php echo number_format( $shipping_cost_ati, 2 ); ?>" id="wps-orders-shipping-cost" class="wps-error" style="text-align : right" />
									</span>
								</span>
							<?php else : ?>
								<strong><?php echo wpshop_tools::formate_number( $shipping_cost_ati ); ?> <?php echo wpshop_tools::wpshop_get_currency(); ?></strong>
							<?php endif; ?>
						<?php endif; ?>
					</span>
				</p>
			<?php endif; ?>
			<!--	**************	-->
			
			
			<?php if( $cart_option == 'full_cart' && !empty($cart_content['order_tva']) ) : ?>
			<?php foreach( $cart_content['order_tva'] as $order_vat_rate => $order_vat_value ) :
					if( $order_vat_rate != 'VAT_shipping_cost') :
						?>
						<p>
							<?php printf( __( 'VAT (%s %%)', 'wpshop'), $order_vat_rate); ?>
							<span class="wps-alignRight">
								<strong><?php echo wpshop_tools::formate_number( $order_vat_value ); ?></strong> <?php echo $currency; ?>
							</span>
						</p>
						<?php
					endif;
			endforeach; ?>
			<?php endif; ?>
				<?php if( $cart_type == 'admin-panel' ) : ?>
					<p>
						<?php _e( 'Discount', 'wpshop'); ?>
						<span class="wps-alignRight">
							<?php if( empty( $cart_content['order_status'] ) || $cart_content['order_status'] == 'awaiting_payment' ) : ?>
								<span class="wps-form-group"><span class="wps-form"><input type="text" id="wps-orders-discount-value" size="5" style="text-align : right" value="<?php echo ( !empty($cart_content['order_discount_value']) ) ? $cart_content['order_discount_value'] : ''; ?>"/></span></span>
							<?php else : ?>
								<?php if( !empty($cart_content['order_discount_value']) ) : ?>
									<strong><?php echo $cart_content['order_discount_value']; ?> <?php echo ( !empty($cart_content['order_discount_type']) && $cart_content['order_discount_type'] == 'percent' ) ? '%' : wpshop_tools::wpshop_get_currency(); ?></strong>
								<?php else : ?>
									0 <?php echo wpshop_tools::wpshop_get_currency(); ?>
								<?php endif; ?>
							<?php endif; ?>
						</span>
					</p>
					<?php if( empty( $cart_content['order_status'] ) || $cart_content['order_status'] == 'awaiting_payment' ) : ?>
						<p>
							<?php _e( 'Discount type', 'wpshop'); ?>
							<span class="wps-alignRight">
								<span class="wps-form-group">
									<span class="wps-form">
										<select id="wps-orders-discount-type">
											<option value="percent" <?php echo ( !empty($cart_content) && !empty($cart_content['order_discount_type']) && $cart_content['order_discount_type'] == 'percent' ) ? 'selected="selected"' : ''; ?>>%</option>
											<option value="amount" <?php echo ( !empty($cart_content) && !empty($cart_content['order_discount_type']) && $cart_content['order_discount_type'] == 'amount' ) ? 'selected="selected"' : ''; ?>><?php echo wpshop_tools::wpshop_get_currency(); ?></option>
										</select>
									</span>
								</span>
							</span>
						</p>
					<?php endif; ?>
				<?php endif; ?>


				<?php if ( !empty( $cart_content['coupon_id']) ) : ?>
				<p><?php _e( 'Total ATI before discount', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $order_totla_before_discount ); ?></strong> <?php echo $currency; ?></span></p>
				<p><?php _e( 'Discount', 'wpshop'); ?> (<?php echo $coupon_title; ?>) <span class="wps-alignRight"><strong><?php echo $coupon_value; ?></strong><?php echo $currency; ?></span></p>
				<?php endif; ?>

				<?php if( !empty($_SESSION['cart']['order_partial_payment']) ) :
					$wps_partial_payment_data = get_option( 'wpshop_payment_partial' );
					$partial_payment_informations = $wps_partial_payment_data['for_all'];
					$partial_payment_amount =  $_SESSION['cart']['order_partial_payment'];
				?>
					<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $cart_content['order_grand_total'] ); ?></strong> <?php echo $currency; ?></span></p>
					<p class="wps-hightlight">
					<?php printf(__('Payable now %s','wpshop'), '(' . $partial_payment_informations['value'] . ( ( !empty($partial_payment_informations['type']) && $partial_payment_informations['type'] == 'percentage' ) ? '%': wpshop_tools::wpshop_get_currency( false ) ) . ')'); ?>
					<span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $partial_payment_amount ); ?></strong> <?php echo $currency; ?>
					</span></p>
				<?php elseif ( !empty( $cart_content ) && !empty( $cart_content[ 'order_status'] ) && ( 'partially_paid' == $cart_content[ 'order_status' ] ) && !empty( $cart_content[ 'order_payment' ] ) && !empty( $cart_content[ 'order_payment' ][ 'received' ] ) ) : ?>
					<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $cart_content['order_grand_total'] ); ?></strong> <?php echo $currency; ?></span></p>
					<?php $allready_received_amount = 0; ?>
					<?php foreach ( $cart_content[ 'order_payment' ][ 'received' ] as $payment ) : ?>
						<?php if ( 'payment_received' == $payment[ 'status' ] ) : ?>
							<?php $allready_received_amount += $payment[ 'received_amount' ]; ?>
						<?php endif; ?>
					<?php endforeach; ?>
					<p><?php _e( 'Already paid', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $allready_received_amount ); ?></strong> <?php echo $currency; ?></span></p>
					<p class="wps-hightlight"><?php _e( 'Due amount for this order', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $cart_content['order_grand_total'] - $allready_received_amount ); ?></strong> <?php echo $currency; ?></span></p>
				<?php else : ?>
					<p class="wps-hightlight"><?php _e( 'Total ATI', 'wpshop'); ?><span class="wps-alignRight"><strong><?php echo wpshop_tools::formate_number( $total_ati ); ?></strong> <?php echo $currency; ?></span></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php if ( empty($cart_type) || ( !empty($cart_type) && $cart_type != 'summary' && $cart_type != 'admin-panel')  ) : ?>
<div>
	<?php if( !empty( $_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type'] == 'quotation' ) : ?>
	<button class="wps-bton-first-alignRight-rounded" id="wps-cart-order-action"><?php _e( 'Validate my quotation', 'wpshop' ); ?></button>
	<?php else : ?>
	<button class="wps-bton-first-alignRight-rounded" id="wps-cart-order-action"><?php _e( 'Order', 'wpshop' ); ?></button>
	<?php endif; ?>
	<button class="wps-bton-second-alignRight-rounded emptyCart"><?php _e( 'Empty the cart', 'wpshop' ); ?></button>
</div>
<div style="clear : both; text-align : right; padding : 8px 0; margin-top : 8px; font-size : 14px; font-style : italic; font-weight : bold; color : #C7CE06;"><span class="wps-mini-cart-free-shipping-alert"><?php echo wpshop_tools::create_custom_hook('wpshop_free_shipping_cost_alert'); ?></span></div>
<?php endif; ?>

<?php if( !empty($cart_type) && $cart_type == 'admin-panel' && ( empty( $cart_content['order_status'] ) || $cart_content['order_status'] == 'awaiting_payment' ) ) : ?>
<button class="wps-bton-second-rounded alignRight" id="wps-orders-update-cart-informations"><i class="dashicons dashicons-update"></i><?php _e( 'Update order informations', 'wpshop'); ?></button>
<?php endif; ?>




