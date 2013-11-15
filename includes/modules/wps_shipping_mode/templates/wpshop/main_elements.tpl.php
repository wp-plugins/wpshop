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
	<div class="wps_shipping_mode_choice_explanation">{WPSHOP_SHIPPING_METHOD_EXPLANATION}</div>
</div>
<div class="wps_shipping_method_additional_element_container wpshopHide" id="container_{WPSHOP_SHIPPING_METHOD_CODE}">{WPSHOP_SHIPPING_METHOD_CONTENT}</div>
<div class="clear"></div>
<!--
<li>
	<label for="{WPSHOP_SHIPPING_METHOD_CODE}">
		<input type="radio" name="wps_shipping_method_choice" id="{WPSHOP_SHIPPING_METHOD_CODE}" value="{WPSHOP_SHIPPING_METHOD_NAME}" {WPSHOP_SHIPPING_MODE_SELECTED} />
		{WPSHOP_SHIPPING_METHOD_NAME}
	</label>
	<span class="wps-inline-info-right">{WPSHOP_SHIPPING_MODE_LOGO}</span>
	<div class="wps-form-list-content">{WPSHOP_WPS_SHIPPING_MODE_ADDITIONAL_CONTENT}</div>
</li>
-->
<?php
$tpl_element['wpshop']['default']['shipping_mode_front_display'] = ob_get_contents();
ob_end_clean();



/** Shipping Method **/
ob_start();
?>
<div id="wps_shipping_modes_choice">
	<ul class="wps-form-list">
	{WPSHOP_SHIPPING_MODES}
	</ul>
</div>
<?php
$tpl_element['wpshop']['default']['shipping_modes'] = ob_get_contents();
ob_end_clean();