<?php
if ( !empty($orders) ) :
	$order_status = unserialize( WPSHOP_ORDER_STATUS );
	$permalink_option = get_option( 'permalink_structure' );
	$currency = wpshop_tools::wpshop_get_currency( false );
	$account_page_id = get_option('wpshop_myaccount_page_id');
	$color_label = array( 'awaiting_payment' => 'jaune', 'canceled' => 'rouge', 'partially_paid' => 'orange', 'incorrect_amount' => 'orange', 'denied' => 'rouge', 'shipped' => 'bleu', 'payment_refused' => 'rouge', 'completed' => 'vert', 'refunded' => 'rouge');
	$wpshop_display_delete_order_option = get_option('wpshop_display_option');
?>


<?php if( !$from_admin ): ?>
<span class="wps-h5"><?php _e( 'My last orders', 'wpshop'); ?></span>
<?php endif; ?>

<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Date', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Reference', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Total', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Status', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Tracking number', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Actions', 'wpshop'); ?></div>
	</div>
	<?php
	foreach( $orders as $order ) :
		$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
	?>
	<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell"><?php echo mysql2date( get_option('date_format') . ' ' . get_option('time_format'), $order_meta['order_date'], true ); ?></div>
		<div class="wps-table-cell"><?php echo $order_meta['order_key']; ?></div>
		<div class="wps-table-cell"><?php echo wpshop_tools::formate_number( $order_meta['order_grand_total'] ).' '.$currency; ?></div>
		<div class="wps-table-cell">
			<span class="wps-label-<?php echo $color_label[$order_meta['order_status']]; ?>"><?php _e( $order_status[$order_meta['order_status']], 'wpshop' ); ?></span>
		</div>
		<div class="wps-table-cell">
			<?php if(!empty($order_meta['order_trackingLink'])):?>
				<?php /** Check if http:// it's found in the link */
				$url = $order_meta['order_trackingLink'];
				if('http://' != substr($url, 0, 7))
					$url = 'http://' . $url;
				?>
				<a href="<?php echo $url; ?>" target="_blank"><?php echo !empty($order_meta['order_trackingNumber']) ? $order_meta['order_trackingNumber'] : ""; ?></a>
			<?php else: ?>
				<?php _e('No tracking links', 'wpshop'); ?>
			<?php endif; ?>
		</div>
		<?php if( !is_admin() ): ?>
			<div class="wps-table-cell wps-customer-order-list-actions">
				<button class="wps-bton-first-mini-rounded wps-orders-details-opener" id="wps-order-details-opener-<?php echo $order->ID; ?>"><?php _e( 'Order details', 'wpshop' ); ?></button>
				<?php if ( !empty( $order_meta ) && !empty( $order_meta[ 'order_invoice_ref' ] ) ) : ?>
				<br/><a href="<?php echo WPSHOP_TEMPLATES_URL; ?>invoice.php?order_id=<?php echo $order->ID; ?>&invoice_ref=<?php echo $order_meta[ 'order_invoice_ref' ]; ?>&mode=pdf" target="_blank" class="wps-bton-first-mini-rounded" role="button"><?php _e( 'Download invoice', 'wpshop' ); ?></a>
				<?php endif; ?>

				<!-- Display delete order -->
				<?php if(!empty($wpshop_display_delete_order_option) && !empty($wpshop_display_delete_order_option['wpshop_display_delete_order']) && $wpshop_display_delete_order_option['wpshop_display_delete_order']):?>
					<button class="wps-bton-first-mini-rounded wps-orders-delete button-secondary" data-id="<?php echo $order->ID; ?>"><?php _e( 'Delete order', 'wpshop' ); ?></button>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="wps-table-cell"><a href="<?php echo admin_url( 'post.php?post='.$order->ID.'&action=edit' ); ?>" target="_blank" role="button" class="wps-bton-first-mini-rounded" ><?php _e( 'Order details', 'wpshop' ); ?></a></div>
		<?php endif?>
	</div>
	<?php endforeach; ?>
</div>
<?php else : ?>
<div class="wps-alert-info"><?php _e( 'No order have been created for the moment', 'wpshop'); ?></div>
<?php endif; ?>


