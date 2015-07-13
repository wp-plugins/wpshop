<div class="wpspos-customer-selected" >
	<?php if ( !empty( $customer_infos ) ) : ?>
		<input type="hidden" id="wps_pos_selected_customer" value="<?php echo $customer_id; ?>" />
		<?php _e('Customer last name', 'wps-pos-i18n')?> : <?php echo $customer_infos->last_name; ?><br/>
		<?php _e('Customer first name', 'wps-pos-i18n')?> : <?php echo $customer_infos->first_name; ?><br/>
		<?php _e('Customer email', 'wps-pos-i18n')?> : <?php echo $customer_infos->user_email; ?><br/>
	<?php else : ?>
		<?php _e( 'Nothing was found for selected customer. Please check this customer account before continuing', 'wps-pos-i18n' ); ?>
	<?php endif; ?>
</div>