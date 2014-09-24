<div><span class="wps-h3"><?php _e( 'This products wait your opinion', 'wps_opinion'); ?></span></div>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Picture', 'wps_opinion'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Product', 'wps_opinion'); ?></div>
		<div class="wps-table-cell"></div>
	</div>
	<?php if( !empty($ordered_products) ) : ?>
	<?php foreach( $ordered_products as $ordered_product ) : ?>
	<?php require( $this->get_template_part( "frontend", "waited_opinion") ); ?>
	<?php endforeach; ?>
	<?php else : ?>
	<?php _e( 'No products wait your opinion !', 'wps_opinion'); ?>
	<?php endif; ?>
</div>
