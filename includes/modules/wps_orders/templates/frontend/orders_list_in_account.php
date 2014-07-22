<?php 
$order_status = unserialize( WPSHOP_ORDER_STATUS );
$permalink_option = get_option( 'permalink_structure' );
$currency = wpshop_tools::wpshop_get_currency( false );
$account_page_id = get_option('wpshop_myaccount_page_id');
$color_label = array( 'awaiting_payment' => 'jaune', 'canceled' => 'rouge', 'partially_paid' => 'orange', 'incorrect_amount' => 'orange', 'denied' => 'rouge', 'shipped' => 'bleu', 'payment_refused' => 'rouge', 'completed' => 'vert', 'refunded' => 'rouge');
?>



<span class="wps-h5"><?php _e( 'My last orders', 'wpshop'); ?></span>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Date', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Reference', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Total', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Status', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Details', 'wpshop'); ?></div>
	</div>
	<?php
	foreach( $orders as $order ) : 
		$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
	?>
	<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell"><?php echo mysql2date( get_option('date_format'), $order_meta['order_date'], true ); ?></div>
		<div class="wps-table-cell"><?php echo $order_meta['order_key']; ?></div>
		<div class="wps-table-cell"><?php echo wpshop_tools::formate_number( $order_meta['order_grand_total'] ).' '.$currency; ?></div>
		<div class="wps-table-cell"><span class="wps-label-<?php echo $color_label[$order_meta['order_status']]; ?>"><?php _e( $order_status[$order_meta['order_status']], 'wpshop' ); ?></span></div>
		<div class="wps-table-cell"><button class="wps-bton-first-mini-rounded wps-orders-details-opener" id="wps-order-details-opener-<?php echo $order->ID; ?>"><?php _e( 'Order details', 'wpshop' ); ?></button></div>
	</div>
	<?php endforeach; ?>
</div>


