<?php
$tpl_element = array();
/**
 * WPS ORDERS CHOOSE CUSTOMERS INTERFACE 
 */
ob_start();
?>
<button id="wps_orders_create_customer" class="button-primary"><?php _e('Create a customer', 'wpshop') ; ?></button>  
<?php _e('OR', 'wpshop'); ?> 
{WPSHOP_CUSTOMERS_LIST}
<?php
$tpl_element['admin']['default']['wps_orders_choose_customer_interface'] = ob_get_contents();
ob_end_clean();