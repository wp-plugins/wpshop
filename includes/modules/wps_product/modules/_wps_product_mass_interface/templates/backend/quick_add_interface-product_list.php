<?php if( !empty($products) ) : ?>
	<table class="wp-list-table widefat wps-product-mass-interface-table" >
		<?php
			$i = 1;
			foreach( $products as $product ) :
				$product_attribute_set_id = get_post_meta( $product['post_datas']->ID, '_wpshop_product_attribute_set_id', true );
				$class = ($i % 2) ? 'alternate' : '';
		?>

		<?php require( wpshop_tools::get_template_part( WPS_PDCT_MASS_DIR, WPS_PDCT_MASS_TEMPLATES_MAIN_DIR, "backend", "quick_add_interface", "product_line" ) ); ?>

		<?php
			$i++;
			endforeach;
		?>
	</table>
<?php else: ?>
	<?php _e( 'You don\'t have any product for the moment', 'wps-product-mass-interface-i18n' ); ?>
<?php endif; ?>