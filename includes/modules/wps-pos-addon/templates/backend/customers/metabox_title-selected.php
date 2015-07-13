<?php
	global $wpdb;
	$query = $wpdb->prepare( "SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE name = %s", __( 'Billing address', 'wpshop' ) );
	$attribute_set_id = $wpdb->get_var( $query );
?>
<span class="dashicons dashicons-businessman"></span>
<?php _e( 'Selected customer', 'wps-pos-i18n' ); ?>
<a class="thickbox add-new-h2" title="<?php _e( 'New customer creation', 'wps-pos-i18n' ); ?>" href="<?php echo admin_url( 'admin-ajax.php?action=wpspos-customer-quick-creation&wps-nonce=' . wp_create_nonce( 'wps-customer-quick-nonce' ) . '&width=550&height=600&customer_set_id=' . $attribute_set_id ); ?>"><?php _e('Create a customer', 'wps-pos-i18n'); ?></a>

<button class="wps-bton-second-mini-rounded wpspos-continue-using-selected-customer alignright" ><?php _e( 'Cancel', 'wps-pos-i18n' ); ?></button>
<button type="button" class="wps-bton-third-mini-rounded alignright" id="wps-pos-change-customer" ><?php _e( 'Change customer', 'wps-pos-i18n' ); ?></button>