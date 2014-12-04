<?php if ( !empty($order_postmeta['order_payment']) ) : 
$total_amount = ( !empty($order_postmeta['order_grand_total']) ) ? $order_postmeta['order_grand_total'] : '';
$waited_amount_sum = $received_amount_sum = 0;
?>
	<?php $payment_modes = get_option( 'wps_payment_mode' ); ?>
	<?php if( !empty( $order_postmeta['order_payment']['customer_choice'] ) && !empty( $order_postmeta['order_payment']['customer_choice']['method'] ) )?>
		<div class="wps-alert-info"><strong><?php _e( 'Payment method customer select', 'wpshop'); ?> : </strong><br/><?php ?><?php echo $order_postmeta['order_payment']['customer_choice']['method']  ?></div>
		
	<?php echo apply_filters( 'wps_administration_order_payment_informations', $order->ID ); ?>
	
	<?php if( !empty( $order_postmeta['order_payment']['received'] ) ) : ?>
		<div class="wps-boxed">
			<div class="wps-h3"><?php _e( 'Received payments', 'wpshop'); ?></div>
		<?php foreach( $order_postmeta['order_payment']['received'] as $received_payment ) : ?>	
			<?php 
			if ( !empty($received_payment['waited_amount']) ) {
				$waited_amount_sum += $received_payment['waited_amount'];
			}
			if ( !empty($received_payment['received_amount']) && ($received_payment['status'] == 'payment_received') ) {
				$received_amount_sum += $received_payment['received_amount'];
			}
			?>
			
				<div class="wps-h6"><br/><?php echo ( !empty( $received_payment ) &&  !empty(  $received_payment['method'] ) ?  $payment_modes['mode'][ $received_payment['method'] ]['name'] : __( 'Unknow', 'wpshop') ); ?></div>
				<div><strong><?php _e( 'Payment date', 'wpshop'); ?> :</strong> <?php echo ( !empty( $received_payment ) && !empty($received_payment['date']) ) ? mysql2date('d F Y H:i', $received_payment['date'], true) : __( 'Unknow', 'wpshop'); ?></div>
				<div><strong><?php _e( 'Payment reference', 'wpshop'); ?> :</strong> <?php echo ( !empty( $received_payment ) && !empty($received_payment['payment_reference']) ) ? $received_payment['payment_reference'] : __( 'Unknow', 'wpshop'); ?></div>
				<div><strong><?php _e( 'Amount', 'wpshop'); ?> :</strong> <?php echo ( !empty( $received_payment ) && !empty($received_payment['received_amount']) ) ? $received_payment['received_amount'].' '.wpshop_tools::wpshop_get_currency() : __( 'Unknow', 'wpshop'); ?></div>
				<div><strong><?php _e( 'Status', 'wpshop'); ?> :</strong> 
					<?php if( !empty($received_payment['status']) && $received_payment['status'] == 'payment_received' ) : ?>
						<span class="wps-label-vert"><?php _e( 'Received payment', 'wpshop'); ?></span>
					<?php elseif( $received_payment['status'] == 'incorrect_amount' )  : ?>
						<span class="wps-label-orange"><?php _e( 'Incorrect amount', 'wpshop'); ?></span>
					<?php elseif( $received_payment['status'] == 'waiting_payment') : ?>	
						<span class="wps-label-rouge"><?php _e( 'Waiting payment', 'wpshop'); ?></span>
					<?php else : ?>
						<span class="wps-label-rouge"><?php echo $received_payment['status']; ?></span>
					<?php endif; ?>
				</div>
				<?php if( !empty( $received_payment ) && !empty($received_payment['invoice_ref']) ) : ?>
					<div><br/><a href="<?php echo WPSHOP_TEMPLATES_URL; ?>invoice.php?order_id=<?php echo $order->ID; ?>&invoice_ref=<?php echo $received_payment['invoice_ref']; ?>&mode=pdf" target="_blank" class="wps-bton-fourth-mini-rounded" role="button"><?php _e( 'Download invoice', 'wpshop' ); ?></a></div>
				<?php endif; ?>
				
			
		<?php endforeach;?>	
		</div>
		
		<?php if ( ( ($total_amount - $received_amount_sum ) > 0) && ($order_postmeta['order_grand_total'] > 0) ) : ?>
		<div class="wps-boxed">
			<div class="wps-h5"><?php _e( 'Add a new payment', 'wpshop'); ?></div>
			<div class="wps-gridwrapper2-padded">
				<div class="wps-form-group">
					<label><?php _e('Method', 'wpshop'); ?> :</label>
					<div class="wps-form">
						<select name="wpshop_admin_order_payment_received[method]">
							<?php if(!empty( $payment_modes ) && !empty($payment_modes['mode'])  ) : ?>
								<?php foreach( $payment_modes['mode'] as $mode_id => $mode ) : ?>
									<?php if( !empty($mode['active']) ) : ?>
										<option value="<?php echo $mode_id; ?>"><?php echo $mode['name']; ?></option>
									<?php endif;?>
								<?php endforeach; ?>
							<?php endif;?>
						</select>
					</div>
				</div>
				
				<div class="wps-form-group">
					<label><?php _e('Reference', 'wpshop'); ?> :</label>
					<div class="wps-form">
						<input type="text" name="wpshop_admin_order_payment_received[payment_reference]" />
					</div>
				</div>
				
			</div>
			
			<div class="wps-gridwrapper2-padded">
				<div class="wps-form-group">
					<label><?php _e('Date', 'wpshop'); ?> :</label>
					<div class="wps-form">
						<input type="text" name="wpshop_admin_order_payment_received[date]" class="wpshop_admin_order_arrived_payment_date" value="" />
					</div>
				</div>
				
				<div class="wps-form-group">
					<label><?php _e('Amount', 'wpshop'); ?> (<?php echo wpshop_tools::wpshop_get_currency(); ?>):</label>
					<div class="wps-form">
						<input type="text" name="wpshop_admin_order_payment_received[received_amount]" value="<?php echo $order_postmeta['order_amount_to_pay_now']; ?>" />
					</div>
				</div>
				
			</div>
			<input type="hidden" value="<?php echo ($waited_amount_sum - $received_amount_sum ); ?>" id="wpshop_admin_order_due_amount" />
			<input type="hidden" value="" id="action_triggered_from" name="action_triggered_from" />
			<div><button class="wps-bton-first-mini-rounded" id="wpshop_order_arrived_payment_amount_add_button"><?php _e( 'Add the payment', 'wpshop' ); ?></button></div>
		</div>
		
		<script type="text/javascript" >
			wpshop(document).ready(function(){
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker();
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "dateFormat", "yy-mm-dd");
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "changeMonth", true);
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "changeYear", true);
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "yearRange", "-90:+10");
				jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "navigationAsDateFormat", true);
				jQuery(".wpshop_admin_order_arrived_payment_date").val("<?php echo substr(current_time('mysql', 0), 0, 10); ?>");


				/**	Add an action on order save button	*/
				jQuery("#wpshop_order_arrived_payment_amount_add_button").live("click", function(){
					jQuery("#action_triggered_from").val('add_payment');
					display_message_for_received_payment( false );
				});
			});
		</script>
		
		
		<?php endif; ?>
		<div class="wps-alert-<?php echo ( ( ($order_postmeta['order_amount_to_pay_now']) <= 0 ) ? 'success': 'warning' ); ?>"><u><?php _e( 'Due amount for this order', 'wpshop'); ?></u> : <span class="alignright"><strong><?php echo $order_postmeta['order_amount_to_pay_now'];?> <?php echo wpshop_tools::wpshop_get_currency(); ?></strong></span></div>
		
	<?php endif; ?>
<?php else: ?>
	<div class="wps-alert-info"><?php _e('No information available for this order payment', 'wpshop'); ?></div>
<?php endif;?>
