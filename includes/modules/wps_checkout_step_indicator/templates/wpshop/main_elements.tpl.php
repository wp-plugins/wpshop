<?php
$tpl_element = array();
/**
 * WPShop checkout Steps Indicator
 */
ob_start();
?>
<div id="wps-checkout-steps">
	{WPSHOP_CHECKOUT_STEPS}
</div>
<?php
$tpl_element['wpshop']['default']['wps_checkout_step_indicator'] = ob_get_contents();
ob_end_clean();

/**
 * WPShop checkout Step
 */
ob_start();
?>
<div class="wps-checkout-step {WPSHOP_CURRENT_STEP_CLASS}">
	<i>{WPSHOP_CHECKOUT_STEP_ID}</i>
	<span>{WPSHOP_CHECKOUT_STEP_NAME}</span>
</div>
<?php
$tpl_element['wpshop']['default']['wps_checkout_step'] = ob_get_contents();
ob_end_clean();