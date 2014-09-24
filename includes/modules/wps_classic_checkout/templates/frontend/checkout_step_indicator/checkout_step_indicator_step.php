<div class="<?php echo $step_class; ?>" id="wps-step-indicator-<?php echo $step_id; ?>">
	<?php if( $step_finished ) : 
		$permalink_option = get_option( 'permalink_structure' );
		$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
		$url = get_permalink( wpshop_tools::get_page_id($checkout_page_id)  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step='.$step_id;
		?>
		<a href="<?php echo $url; ?>"><i class="wps-circlerounded"><?php echo $step_id; ?></i><span><?php echo $step; ?></span></a>
	<?php else : ?>
		<i class="wps-circlerounded"><?php echo $step_id; ?></i><span><?php echo $step; ?></span>
	<?php endif ?>
</div>

