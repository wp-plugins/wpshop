<!--  <div class="wps-gridwrapper">-->
<?php 
$wps_coupon_ctr = new wps_coupon_ctr();
$result = $wps_coupon_ctr->getCoupons();
unset($wps_coupon_ctr);
if( !empty($result) ) {
?>
<div class="wps-boxed">
	<span class="wps-h5"><?php _e( 'Coupon', 'wpshop'); ?></span>
	<div id="wps_coupon_alert_container"></div>
	<input type="text" value="" id="wps_coupon_code" />
	<button id="wps_apply_coupon" class="wps-bton-first-rounded"><?php _e( 'Apply coupon', 'wpshop' ); ?></button>	
</div>
<?php } ?>
<!--  </div> -->
