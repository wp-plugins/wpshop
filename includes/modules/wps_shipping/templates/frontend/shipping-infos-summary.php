<div class="wps-gridwrapper2-padded">
<?php if( !empty($shipping_content) ) : ?>
<div>
	<div class="wps-boxed summary_shipping_boxed">
		<div class="summary_shipping_boxed_title"><?php _e( 'Shipping address', 'wpshop')?></div>
		<?php echo $shipping_content; ?>
	</div>
</div>
<?php endif; ?>
<div>
	<div class="wps-boxed summary_shipping_boxed">
		<div class="summary_shipping_boxed_title"><?php _e( 'Billing address', 'wpshop')?></div>
		<?php echo $billing_content; ?>
	</div>
</div>


</div>
