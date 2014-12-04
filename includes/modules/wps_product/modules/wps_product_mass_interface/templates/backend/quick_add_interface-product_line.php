
<tr class="<?php echo $class; ?>">
	<td class="wps-mass-interface-line-selector"><div class="wps-form-group"><label>&nbsp;</label><div class="wps-form"><center><input type="checkbox" class="wps-save-product-checkbox" name="wps_product_quick_save[]" value="<?php echo $product['post_datas']->ID; ?>" /></center></div></div></td>
	<td>
		<div class="wps-form-group">
			<label><?php _e( 'Product title', 'wpshop'); ?> :</label>
			<div class="wps-form">
				<input type="text" name="wps_mass_interface[<?php echo $product['post_datas']->ID; ?>][post_title]"  value="<?php echo $product['post_datas']->post_title; ?>" />
			</div>
		</div>
	</td>
	<td>
		<div class="wps-form-group">
			<label><?php _e( 'Product description', 'wpshop'); ?> :</label>
			<div class="wps-form">
				<textarea id="wps_product_description_<?php echo $product['post_datas']->ID; ?>" name="wps_mass_interface[<?php echo $product['post_datas']->ID; ?>][post_content]"><?php echo nl2br( $product['post_datas']->post_content );?></textarea>
			</div>
		</div>
	</td>

	<td>
		<span class="wps_mass_interface_picture_container" id="wps_mass_interface_picture_container_<?php echo $product['post_datas']->ID; ?>"><?php echo get_the_post_thumbnail( $product['post_datas']->ID, 'thumbnail'); ?></span>
		<input type="hidden" value="" name="wps_mass_interface[<?php echo $product['post_datas']->ID; ?>][picture]" />
		<center><a href="#" class="wps-bton-second-mini-rounded wps_add_picture_to_product_in_mass_interface" id="wps_add_picture_to_product_in_mass_interface_<?php echo $product['post_datas']->ID; ?>"><?php _e( 'Add a picture', 'wpshop'); ?></a></center>
	</td>
	
	<td>
		<input type="hidden" name="wps_mass_interface[<?php echo $product['post_datas']->ID; ?>][files]" />
		<div id="wps_mass_update_product_file_list_<?php echo $product['post_datas']->ID; ?>"><?php echo $this->wps_product_attached_files( $product['post_datas']->ID ); ?></div>
		<center><a class="wps-bton-first-mini-rounded wps_add_files_to_product_in_mass_interface" id="wps_add_files_to_product_in_mass_interface_<?php echo $product['post_datas']->ID; ?>"><?php _e( 'Add files', 'wpshop'); ?></a></center>
	</td>
	
	<?php if( !empty($quick_add_form_attributes) ) : ?>
		<?php foreach( $quick_add_form_attributes as $attribute_id => $att_def ) :
		
			$att = null;
			$query = $wpdb->prepare( 'SELECT * FROM '. WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE attribute_set_id = %d AND attribute_id = %d AND status = %s', $default, $attribute_id, 'valid' );
			$checking_display_att = $wpdb->get_results( $query );

			if( !empty($checking_display_att) ) :
				$current_value = wpshop_attributes::getAttributeValueForEntityInSet( $att_def['data_type'], $attribute_id, $product_entity_id, $product['post_datas']->ID );
				$output_specs =  array(
					'page_code' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
					'element_identifier' => $product['post_datas']->ID,
					'field_id' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'_'.$product['post_datas']->ID. '_',
					'current_value' => ( !empty($current_value->value) ? $current_value->value : '' )
				);
				$att = wpshop_attributes::display_attribute( $att_def['code'], 'admin', $output_specs );
			endif;
			?>
			<td>
				<div class="wps-form-group">
					<label><?php  _e( $att['field_definition']['label'], 'wpshop' ); ?></label>
					<div class="wps-form"><?php echo str_replace( 'name="wpshop_product_attribute', 'name="wpshop_product_attribute[' .$product['post_datas']->ID. ']', $att['field_definition']['output'] ); ?></div>
				</div>
			</td>
		<?php endforeach; ?>
	<?php endif; ?>
</tr>