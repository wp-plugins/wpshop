<div class="wps-table-content wps-table-row wps_shipping_mode_container">
		<div class="wps-table-cell wps-cart-item-img">
			<div id="wps_shipping_mode_logo_container_<?php echo $k; ?>">
				<?php echo ( !empty($shipping_mode['logo']) ? ( (strstr($shipping_mode['logo'], 'http://') === FALSE ) ? wp_get_attachment_image( $shipping_mode['logo'], 'thumbnail') : '<img src="' .$shipping_mode['logo']. '" alt="" />' ) : '' ); ?>
			</div>
		</div>
		<div class="wps-table-cell">
			<a class="wps-bton-first-mini-rounded add_logo_to_shipping_mode" id="add_logo_to_shipping_mode_<?php echo $k; ?>" href="#"><?php _e( 'Add a logo', 'wpshop'); ?></a>
			<input type="hidden" name="wps_shipping_mode[modes][<?php echo $k; ?>][logo]"  id="wps_shipping_mode_logo_<?php echo $k; ?>" value="<?php echo ( !empty($shipping_mode['logo']) ) ? $shipping_mode['logo'] : ''; ?>" />
		</div>
		<div class="wps-table-cell">
			<input type="text" name="wps_shipping_mode[modes][<?php echo $k; ?>][name]" id="wps_shipping_mode_configuration_<?php echo $k; ?>_name" value="<?php echo ( !empty($shipping_mode['name']) ) ? $shipping_mode['name'] : ''; ?>" />
		</div>
		<div class="wps-table-cell"><a href="#TB_inline?width=780&amp;height=700&amp;inlineId=<?php echo $k; ?>_shipping_configuration_interface" class="thickbox wps-bton-first-mini-rounded" title="<?php _e('Configure the shipping mode', 'wpshop'); ?>" ><?php _e( 'Configure', 'wpshop'); ?></a></div>
		<div class="wps-table-cell"><input type="checkbox" name="wps_shipping_mode[modes][<?php echo $k; ?>][active]" <?php echo ( (!empty($shipping_mode) && !empty($shipping_mode['active']) ) ? 'checked="checked"' : '' ); ?> /></div>
		<div class="wps-table-cell"><input type="radio" name="wps_shipping_mode[default_choice]" value="<?php echo $k; ?>" <?php echo ( !empty($shipping_mode['modes']) && !empty( $shipping_mode['modes']['default_choice'] ) && $shipping_mode['modes']['default_choice'] == $k ) ? 'checked="checked"' : ''; ?> /></div>
		<!-- Configuration interface -->
		<div id="<?php echo $k; ?>_shipping_configuration_interface" style="display : none">	
			<?php 
			$wps_shipping_mode_ctr = new wps_shipping_mode_ctr();
			echo $wps_shipping_mode_ctr->generate_shipping_mode_interface( $k, $shipping_mode ); ?>
		</div>
</div>
	
	
