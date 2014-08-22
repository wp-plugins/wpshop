<div>
<?php echo do_shortcode( '[wps_shipping_method]' ); ?>
</div>
<div id="wps-checkout-step-errors"></div>
<?php if( !empty( $_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type'] == 'quotation' ) : ?>
<div class="wps"><button class="wps-bton-first-alignRight-rounded" id="wps-checkout-valid-step-four"><?php _e( 'Validate my quotation', 'wpshop' ); ?></button></div>
<?php else : ?>
<div class="wps"><button class="wps-bton-first-alignRight-rounded" id="wps-checkout-valid-step-four"><?php _e( 'Order', 'wpshop' ); ?></button></div>
<?php endif; ?>