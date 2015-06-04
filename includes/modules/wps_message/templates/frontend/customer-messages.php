<div class="wps-table wps-my-message">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Message title', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php _e( 'Send date', 'wpshop' ); ?></div>
	</div>
<?php if( !empty($messages_histo) && is_array($messages_histo) ) :?>
	<?php foreach( $messages_histo as $first_send_date => $messages ) : ?>
	<?php foreach( $messages as $key => $message ) : ?>

	<div class="wps-table-content wps-table-row" data-date="<?php echo substr($first_send_date, 0, 7); ?>" >
		<div class="wps-table-cell wps-message-title-container">
			<?php $message_special_id = rand(); ?>
			<span class="wps-message-title"><a title="<?php echo $message['title']; ?>" href="#TB_inline?width=600&height=550&inlineId=wps-customer-message-<?php echo $message_special_id; ?>" class="thickbox" ><?php echo $message['title']; ?></a></span>
			<div id="wps-customer-message-<?php echo $message_special_id; ?>" style="display:none;" ><?php echo $message['message']; ?></div>
		</div>
		<div class="wps-table-cell">
		<?php if( !empty($message['dates']) ) : ?>
			<ul>
			<?php foreach( $message['dates'] as $date ) : ?>
				<li><?php echo mysql2date( get_option('date_format') . ' ' . get_option('time_format') , $date, true ); ?></li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		</div>
	</div>
	<?php endforeach; ?>
	<?php endforeach; ?>
<?php endif;?>
</div>