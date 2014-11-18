<div class="wps-gridwrapper6-marged">
<?php 
	$step_finished = false;
	foreach( $steps as $step_id => $step) : 
		$step_id += 1;
		$step_class = ( $default_step == $step_id ) ? 'wps-checkout-step-current' : ( ( $default_step > $step_id) ? 'wps-checkout-step-finished' : 'wps-checkout-step' ) ;
		$step_finished = ( ( $default_step > $step_id) ? true : false ) ;
		require( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir, "frontend", "checkout_step_indicator/checkout_step_indicator_step") );
	endforeach; 
?>
</div>

