<div>
	<div class="wps-boxed <?php echo $extra_class; ?> wps-address-container" id="wps-address-container-<?php echo $address_type_id; ?>">
		<?php echo $box_content; ?>
	</div>
	<?php if ( $first_address_checking ) : ?>
			<div><input id="wps-shipping_to_billing" type="checkbox" checked="checked" /> <label for="wps-shipping_to_billing"><?php _e( 'Use the same address for billing', 'wpshop')?></label></div>
	<?php endif; ?>
</div>

