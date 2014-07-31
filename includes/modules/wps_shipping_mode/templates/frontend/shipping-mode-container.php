<?php do_action( 'wpshop_account_custom_hook'); ?>
<div class="wps-gridwrapper">
	<span class="wps-h3"><?php _e( 'Shipping method', 'wpshop'); ?></span>
</div>
<div>
	<?php if( !empty($shipping_modes) && !empty($shipping_modes['modes']) ) : ?>
	<ul class="wps-itemList" id="wps-shipping-method-list-container">
		<?php 
		$i = 0;	
		foreach( $shipping_modes['modes'] as $shipping_mode_id => $shipping_mode ) :
			require( $this->get_template_part( "frontend", "shipping-mode", "element") ); 
		endforeach; 
		?>
	</ul>
	<?php else : ?>
	<div class="wps-alert-info"><?php _e( 'No shipping mode available', 'wpshop' ); ?>	</div>
	<?php endif; ?>
</div>
