<?php
$tpl_element = array();
/**
 * WPSHOP CLASSIC CHECKOUT
 */
ob_start();
?>
<div id="primary">
<section>
{WPSHOP_CLASSIC_CHECKOUT_PRIMARY_CONTENT}
<div id="wps_classic_checkout_error_alert_container" class="wpshopHide wps-alert wps-alert-error"></div> 
{WPSHOP_NEXT_STEP_BUTTON}
</section>
</div>
<div id="secondary">
<section>
{WPSHOP_CLASSIC_CHECKOUT_SECONDARY_CONTENT}
</section>
</div>
<?php
$tpl_element['wpshop']['default']['wps_classic_checkout'] = ob_get_contents();
ob_end_clean();