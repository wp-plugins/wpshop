<?php
	global $wpdb;
	$query = $wpdb->prepare( "SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE name = %s", __( 'Billing address', 'wpshop' ) );
	$attribute_set_id = $wpdb->get_var( $query );
?>
	<tr>
		<td>
			<?php _e( 'No user has been found for current search.', 'wps-pos-i18n' ); ?>
		</td>
		<td>
			<a class="thickbox wps-bton-third-rounded" title="<?php _e( 'New customer creation', 'wps-pos-i18n' ); ?>" href="<?php echo admin_url( 'admin-ajax.php?action=wps-customer-quick-creation&wps-nonce=' . wp_create_nonce( 'wps-customer-quick-nonce' ) . '&width=550&height=600&customer_set_id=' . $attribute_set_id ); ?>"><?php _e('Create a customer', 'wps-pos-i18n'); ?></a>
		</td>
	</tr>