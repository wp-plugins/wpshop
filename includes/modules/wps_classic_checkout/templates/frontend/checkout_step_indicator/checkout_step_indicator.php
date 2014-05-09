<div class="wps-gridwrapper6-marged">
<?php 
	$step_finished = false;
	foreach( $steps as $step_id => $step) : 
		$step_id += 1;
		$step_class = ( $default_step == $step_id ) ? 'wps-checkout-step-current' : ( ( $default_step > $step_id) ? 'wps-checkout-step-finished' : 'wps-checkout-step' ) ;
		$step_finished = ( ( $default_step > $step_id) ? true : false ) ;
		require( $this->get_template_part( "frontend", "checkout_step_indicator/checkout_step_indicator_step") );
	endforeach; 
?>
</div>

