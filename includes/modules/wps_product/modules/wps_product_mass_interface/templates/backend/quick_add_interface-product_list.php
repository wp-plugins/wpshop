<?php if( !empty($products) ) : ?>
	<?php $tab_def = array(); ?>
	<?php if( !empty($products) ) : ?>
				<?php foreach( $products as $product ) : ?>
					<?php foreach( $product['attributes_datas'] as $group ) : ?>
						<?php foreach( $group as $group_def ) : ?>
							<?php foreach( $group_def['attributes'] as $att_id => $attribute ) : ?>
									<?php if( !empty($attribute['is_used_in_quick_add_form']) && $attribute['is_used_in_quick_add_form'] == 'yes' ) : ?>
									<?php $tab_def[$att_id]['name'] = $attribute['frontend_label']; ?>
									<?php $tab_def[$att_id]['def'] = $attribute; ?>
									<?php endif; ?>
							<?php endforeach; ?>
						<?php endforeach; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
		<?php endif; ?>

	<form method="post" id="wps_mass_edit_product_form" action="<?php echo admin_url( 'admin-ajax.php' ); ?>">
		<input type="hidden" name="action" value="wps_mass_edit_product_save_action" />
		<table class="wp-list-table widefat wps-product-mass-interface-table" >
			<tr>
				<th width="80"><?php _e( 'Save it', 'wpshop'); ?> ?</th>
				<th width="250"><?php _e( 'Title', 'wpshop'); ?></th>
				<th width="250"><?php _e( 'Description', 'wpshop'); ?></th>
				<th width="80"><?php _e( 'Picture', 'wpshop'); ?></th>
				<th width="80"><?php _e( 'Files', 'wpshop'); ?></th>
				<?php if( !empty($tab_def) ) : ?>
				<?php foreach( $tab_def as $col ) : ?>
					<th width="100"><?php echo $col['name']; ?></th>
				<?php endforeach; ?>
				<?php endif; ?>
			</tr>
		
		
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
	</form>
<?php else: ?>
	<div class="wps-alert-info"><?php _e( 'You don\'t have any product for the moment', 'wpshop' ); ?></div>
<?php endif; ?>