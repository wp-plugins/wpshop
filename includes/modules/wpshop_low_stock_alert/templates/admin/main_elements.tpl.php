<?php
$tpl_element = array();
/**
 * LOW STOCK ALERT ADMIN INTERFACE
 */
ob_start();
?>
<input type="checkbox" name="wpshop_low_stock_alert_options[active]" id="wpshop_low_stock_options_active" {WPSHOP_ACTIVATE_LOW_STOCK_ALERT} />
<label for="wpshop_low_stock_options_active"><?php _e('Activate the low-stock alert display', 'wpshop_low_stock_alert'); ?></label>

<div id="low_stock_alert_configuration">
	<?php _e('Low stock alert is based on real stock ', 'wpshop_low_stock_alert'); ?> ?<br/>
	<input type="radio" name="wpshop_low_stock_alert_options[based_on_stock]" id="wpshop_low_stock_alert_options_based_on_stock" value="yes" {WPSHOP_BASED_ON_REAL_STOCKS} /><label for="wpshop_low_stock_alert_options_based_on_stock"><?php _e('Yes', 'wpshop_low_stock_alert'); ?> </label>
	<input type="radio" name="wpshop_low_stock_alert_options[based_on_stock]" id="wpshop_low_stock_alert_options_not_based_on_stock" value="no" {WPSHOP_NOT_BASED_ON_REAL_STOCKS} /><label for="wpshop_low_stock_alert_options_not_based_on_stock"><?php _e('No', 'wpshop_low_stock_alert'); ?> </label>
	<div id="low_stock_alert_limit"><label for="stock_alert_limit"><input type="text" id="stock_alert_limit" name="wpshop_low_stock_alert_options[alert_limit]" value="{WPSHOP_LOW_STOCK_ALERT_LIMIT}" style="width : 80px" /><?php _e('Number of remaining products to display the alert', 'wpshop_low_stock_alert'); ?></label></div>
</div>

<?php 
$tpl_element['admin']['default']['wpshop_low_stock_admin_interface'] = ob_get_contents();
ob_end_clean();
?>