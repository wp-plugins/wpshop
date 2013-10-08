<?php
/** Shipping Method **/
ob_start();
?>
<div class="wps_shipping_method_choice wps_shipping_method_{WPSHOP_SHIPPING_METHOD_CODE}" >
	<div class="wps_shipping_method_choice_input">
		<input type="radio" name="wps_shipping_method_choice" id="{WPSHOP_SHIPPING_METHOD_CODE}" value="{WPSHOP_SHIPPING_METHOD_NAME}" {WPSHOP_SHIPPING_MODE_SELECTED} />
	</div>
	<div class="wps_shipping_method_choice_logo">
		<label for="{WPSHOP_SHIPPING_METHOD_CODE}">{WPSHOP_SHIPPING_MODE_LOGO}</label>
	</div>
	<div class="wps_shipping_mode_choice_name">
		<label for="{WPSHOP_SHIPPING_METHOD_CODE}">{WPSHOP_SHIPPING_METHOD_NAME}</label>
	</div>
</div>
{WPSHOP_WPS_SHIPPING_MODE_ADDITIONAL_CONTENT}
<div class="wps_shipping_method_additional_element_container wpshopHide" id="container_{WPSHOP_SHIPPING_METHOD_CODE}">{WPSHOP_SHIPPING_METHOD_CONTENT}</div>
<div class="clear"></div>
<?php
$tpl_element['wpshop']['default']['shipping_mode_front_display'] = ob_get_contents();
ob_end_clean();



/** Shipping Method **/
ob_start();
?>
<h2><?php _e('Shipping modes', 'wpshop')?></h2>
<div id="wps_shipping_modes_choice">{WPSHOP_SHIPPING_MODES}</div>
<?php
$tpl_element['wpshop']['default']['shipping_modes'] = ob_get_contents();
ob_end_clean();