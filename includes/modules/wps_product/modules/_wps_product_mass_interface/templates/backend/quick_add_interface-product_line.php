		<tr class="<?php echo $class; ?>" >
			<td class="wps-mass-interface-line-selector" ><input type="checkbox" name="wps_product_quick_save[]" <?php checked( true, $auto_check, true ); ?> value="<?php echo $product['post_datas']->ID; ?>" /></td>
			<td class="wps-mass-interface-main-post-infos" >
				<label>
					<?php _e( 'Title', 'wps-product-mass-interface-i18n'); ?>
					<input type="text" name="wps_mass_interface[<?php echo $product['post_datas']->ID; ?>][<?php echo $wpdb->posts; ?>][post_title]" value="<?php echo $product['post_datas']->post_title; ?>" />
				</label>
				<label>
					<?php _e( 'Description', 'wps-product-mass-interface-i18n'); ?>
					<textarea id="wps_product_description_<?php echo $product['post_datas']->ID; ?>" name="wps_mass_interface[<?php echo $product['post_datas']->ID; ?>][<?php echo $wpdb->posts; ?>][post_content]"><?php echo nl2br( $product['post_datas']->post_content );?></textarea>
				</label>
			</td>

<?php if( !empty($quick_add_form_attributes) ) : ?>
	<?php $extras_attributes = null; ?>
	<?php foreach( $quick_add_form_attributes as $attribute_id => $att_def ) : ?>
	<?php
		$att = null;
		$query = $wpdb->prepare( 'SELECT * FROM '. WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE attribute_set_id = %d AND attribute_id = %d AND status = %s', $product_attribute_set_id, $attribute_id, 'valid' );
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

		/*	Check the prices attribute because output for this attributes is customized	*/
		$price_tab = unserialize(WPSHOP_ATTRIBUTE_PRICES);
		unset($price_tab[array_search(WPSHOP_COST_OF_POSTAGE, $price_tab)]);

		ob_start();
	?>
					<li>
						<div class="wpshop_form_label <?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>_<?php echo $att['field_definition']['name']; ?>_label <?php echo (in_array( $att_def['code'], $price_tab) ? $currentPageCode . '_prices_label ' : '' ); ?>alignleft" >
							<label <?php echo $att['field_definition']['label_pointer']; ?> ><?php _e( $att['field_definition']['label'], 'wpshop' ); ?><?php echo ($att_def['is_required'] == 'yes' ? ' <span class="wpshop_required" >*</span>' : ''); ?></label>
						</div>
						<div class="wpshop_form_input_element <?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>_<?php echo $att['field_definition']['name']; ?>_input <?php echo (in_array( $att_def['code'], $price_tab) ? $currentPageCode . '_prices_input ' : '' ); echo $output['field_definition']['field_container_class']; ?> alignleft" >
							<?php echo str_replace( 'name="wpshop_product_attribute[', 'name="wpshop_product_attribute[' . $product['post_datas']->ID . '][', $att['field_definition']['output'] ); ?>
						</div>
					</li>
	<?php
		$extras_attributes .= ob_get_contents();
		ob_end_clean();
	?>
	<?php endforeach; ?>

	<?php if ( !empty( $extras_attributes ) ) : ?>
			<td class="wps-mass-interface-extras-post-infos" >
				<ul><?php echo $extras_attributes; ?></ul>
			</td>
	<?php endif; ?>

<?php endif; ?>
		</tr>