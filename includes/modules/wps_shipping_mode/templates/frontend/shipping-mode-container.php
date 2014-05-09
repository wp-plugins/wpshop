<?php do_action( 'wpshop_account_custom_hook'); ?>
<div class="wps-boxed">
	<span class="wps-h5"><?php _e( 'Shipping method', 'wpshop'); ?></span>
	<ul class="wps-list-expander" id="wps-shipping-method-list-container">
		<?php if( !empty($shipping_modes) && !empty($shipping_modes['modes']) ) : ?>
			<?php foreach( $shipping_modes['modes'] as $shipping_mode_id => $shipping_mode ) : ?>
			<?php require( $this->get_template_part( "frontend", "shipping-mode", "element") ); ?>
			<?php endforeach; ?>
			<?php else : ?>
			<?php _e( 'No shipping mode available', 'wpshop' ); ?>	
		<?php endif; ?>
	</ul>
</div>
