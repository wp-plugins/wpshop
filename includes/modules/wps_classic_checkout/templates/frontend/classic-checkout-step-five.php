<div><?php echo do_shortcode( '[wps_cart cart_type="summary"]' ); ?></div>


<?php echo do_shortcode('[wps_shipping_summary]' ); ?>

<div class="wps-form-group">
	<label><?php _e( 'Customer comment', 'wpshop'); ?> : </label>
	<div class="wps-form"><textarea id="wps-customer-comment"></textarea></div>
</div>

<?php echo do_shortcode('[wps_payment]' ); ?>

<div style="text-align : right"><input id="terms_of_sale" type="checkbox" value="Terms of sale" name="terms_of_sale"> <label for="terms_of_sale"><?php printf( __('I have read and I accept %sthe terms of sale%s', 'wpshop'), '<a href="' . get_permalink( wpshop_tools::get_page_id( get_option('wpshop_terms_of_sale_page_id') ) ) . '" target="_blank">', '</a>'); ?></label></div>
<div id="wps-checkout-step-errors"></div>
<?php if( !empty( $_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type'] == 'quotation' ) : ?>
<div class="wps"><button class="wps-bton-first-alignRight-rounded" id="wps-checkout-valid-step-five"><?php _e( 'Validate my quotation', 'wpshop' ); ?></button></div>
<?php else : ?>
<div class="wps"><button class="wps-bton-first-alignRight-rounded" id="wps-checkout-valid-step-five"><?php _e( 'Order', 'wpshop' ); ?></button></div>
<?php endif; ?>