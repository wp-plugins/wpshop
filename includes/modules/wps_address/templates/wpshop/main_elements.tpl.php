<?php
$tpl_element = array();
/**
 * WPSHOP ADDRESSES
 */
ob_start();
?>
<section>
<button id="add_new_address_{WPSHOP_BILLING_ADDRESS_TYPE_ID}" class="wps-bton-rounded wps-bton-prim add_new_address"><?php _e('Add a new Billing address', 'wpshop'); ?></button> 
<button id="add_new_address_{WPSHOP_SHIPPING_ADDRESS_TYPE_ID}" class="wps-bton-rounded wps-bton-prim add_new_address"><?php _e('Add a new Shipping address', 'wpshop'); ?></button>
<ul class="wps-form-list">
	{WPSHOP_ADDRESSES_LIST}
</ul>
</section>
<?php
$tpl_element['wpshop']['default']['wps_addresses_container'] = ob_get_contents();
ob_end_clean();

/**
 * WPSHOP ADDRESS
 */
ob_start();
?>
<li {WPSHOP_ADDRESS_CLASS_OPEN_ELEMENT}>
	
	<input id="wps_address_{WPSHOP_ADDRESS_ID}" class="wps_address_{WPSHOP_ADDRESS_TYPE}" type="radio" name="wps_address_{WPSHOP_ADDRESS_TYPE}" value="{WPSHOP_ADDRESS_ID}" {WPSHOP_SELECTED_ADDRESS}  />
	<label for="wps_address_{WPSHOP_ADDRESS_ID}">	
		<strong>{WPSHOP_ADDRESS_TITLE}</strong>
	</label>
	<div class="wps-form-list-content">
		<a class="wps-label wps_modify_address_modal_opener" id="wps_modify_address_{WPSHOP_ADDRESS_ID}" href="#"><?php _e('Modify', 'wpshop'); ?></a>
		<p>{WPSHOP_ADDRESS}</p>
	</div>
</li>
<?php
$tpl_element['wpshop']['default']['wps_address'] = ob_get_contents();
ob_end_clean();


/**
 * WPSHOP ADDRESS FIELD
 */
ob_start();
?>
<div class="wps-form-group">
	<label {WPSHOP_CUSTOMER_FORM_INPUT_LABEL_OPTIONS}>{WPSHOP_CUSTOMER_FORM_INPUT_LABEL}</label> 
	<div class="wps-form">
	{WPSHOP_CUSTOMER_FORM_INPUT_FIELD}
	</div>
</div>
<?php
$tpl_element['wpshop']['default']['wps_address_field'] = ob_get_contents();
ob_end_clean();


/**
 * WPSHOP FIRST ADDRESS FORM
 */
ob_start();
?>
<section>
<div id="wps_checkout_save_first_address_container">
	<div id="wps_save_first_address_errors_container"></div>
	<form id="wps_checkout_save_form" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" >
	<input type="hidden" name="action" value="wps_save_first_address" />
	{WPSHOP_SHIPPING_FORM}
	<div class="wps-form-group">
	<input type="checkbox" name="shiptobilling" checked="checked" /> <?php _e('Use as billing information','wpshop'); ?></label>
	</div>
	<div id="shipping_infos_bloc" class="wpshopHide">
	{WPSHOP_BILLING_FORM}
	</div>
	<input type="button" class="wps-bton wps-bton-prim" id="wps_checkout_save_first_address_btn" value="<?php _e('Save address', 'wpshop'); ?>" /> <img src="{WPSHOP_LOADING_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>" class="wpshopHide" id="wps_save_first_address_loader" />
	</form>
</div>
</section>
<?php
$tpl_element['wpshop']['default']['wps_first_address_container'] = ob_get_contents();
ob_end_clean();



/**
 * WPSHOP SHIPPING ADDRESS SUMMARY
 */
ob_start();
?>
<div class="wps-cart-resume">
	<h3>{WPSHOP_TITLE}</h3>
	<p><a class="wps-label wps_modify_address_modal_opener" id="wps_modify_address_{WPSHOP_ADDRESS_ID}" href="#"><?php _e('Modify', 'wpshop'); ?></a></p>
	{WPSHOP_ADDRESS}
</div>
<?php
$tpl_element['wpshop']['default']['wps_shipping_address_summary'] = ob_get_contents();
ob_end_clean();
