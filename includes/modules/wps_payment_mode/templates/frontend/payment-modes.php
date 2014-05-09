<!--  
<div class="wps-boxed">
	<span class="wps-h5"><?php _e( 'Payment', 'wpshop'); ?></span>
	<ul class="wps-list-expander">
	<?php 
	foreach( $payment_modes as $payment_mode_id => $payment_mode ) : 
			if( !empty($payment_mode['active']) ) :
	?>
		<li <?php echo ( ($default_choice == $payment_mode_id ) ? 'class="wps-activ"'  : ''); ?>>
		<div class="wps-list-expander-header">
			<span>
				<input type="radio" name="wps-payment_mode" value="<?php echo $payment_mode_id; ?>" id="<?php echo $payment_mode_id ; ?>" <?php echo ( ($default_choice == $payment_mode_id ) ? 'checked="checked"' : ''); ?> >
			</span>
			<span>
				<?php echo ( !empty($payment_mode_id['logo']) ? ( (stristr($payment_mode_id['logo'], 'http://') === FALSE ) ? wp_get_attachment_image( $payment_mode_id['logo'], array( 40, 40) ) : '<img src="' .$payment_mode_id['logo']. '" alt="" />' ) : '' ); ?>
			</span>
			<span>
				<label><?php _e( $payment_mode['name'], 'wpshop' ); ?></label>
			</span>
		</div>
		<div class="wps-list-expander-content" id="container_<?php echo $payment_mode_id ; ?>" <?php echo ( ($default_choice == $payment_mode_id ) ? 'style="display:block"'  : ''); ?>><?php _e( $payment_mode['description'], 'wpshop' ); ?></div>
		</li>
	<?php 
		endif;
	endforeach; 
	?>
	</ul>
</div>
-->
<?php if( !empty($payment_modes) ) : ?>
<?php $count_payment_mode = count( $payment_modes ); ?>
<div class="wps-item-selector-gridwrapper3">
	<?php foreach( $payment_modes as $payment_mode_id => $payment_mode ) : ?>
		<div class="wps-item" id="<?php echo $payment_mode_id; ?>">
		<a href="#">
			<p class="wps-payment_logo">
				<?php echo ( !empty($payment_mode['logo']) ? ( (strstr($payment_mode['logo'], 'http://') === FALSE ) ? wp_get_attachment_image( $payment_mode['logo'], 'full' ) : '<img src="' .$payment_mode['logo']. '" alt="" />' ) : '' ); ?>
			</p>
			<p class="wps-payment_explanation"><?php _e( $payment_mode['description'], 'wpshop' ); ?></p>
			<p class="wps-payment_name"><?php _e( $payment_mode['name'], 'wpshop' ); ?></p>
		</a>
		</div>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<input type="hidden" id="wps-selected-payment-method" value="" />


