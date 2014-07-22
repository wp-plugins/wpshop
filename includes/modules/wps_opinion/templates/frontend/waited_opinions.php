<span class="wps-h5"><?php _e( 'This products wait your opinion', 'wpshop'); ?></span>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Picture', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Product', 'wpshop'); ?></div>
		<div class="wps-table-cell"></div>
	</div>
	<?php if( !empty($ordered_products) ) : ?>
	<?php foreach( $ordered_products as $ordered_product ) : ?>
	<?php require( $this->get_template_part( "frontend", "waited_opinion") ); ?>
	<?php endforeach; ?>
	<?php else : ?>
	<?php _e( 'No products wait your opinion !', 'wpshop'); ?>
	<?php endif; ?>
</div>
