<table class="table table-striped wps-pos-element-table">
	<tr>
		<th><?php _e('Product name', 'wps-pos-i18n'); ?></th>
		<th><?php _e('Product price', 'wps-pos-i18n'); ?></th>
		<th><?php _e('Action', 'wps-pos-i18n'); ?></th>
	</tr>
	<?php foreach ( $product_list as $product ) : ?>
		<?php $product_data = wpshop_products::get_product_data( $product['ID'] ); ?>
		<?php $product_variation_definition = get_post_meta( $product['ID'], '_wpshop_variation_defining', true );?>
		<?php require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/products', 'product' ) ); ?>
	<?php endforeach; ?>
</table>