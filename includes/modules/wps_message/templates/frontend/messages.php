<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Message title', 'wpshop' ); ?></div>
		<div class="wps-table-cell"><?php _e( 'Send date', 'wpshop' ); ?></div>
	</div>
	
	<?php foreach( $messages_data as $message ) : ?>
	<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell"><?php echo $message[0]['mess_title']; ?></div>
		<div class="wps-table-cell">
		<?php if( !empty($message[0]['mess_dispatch_date']) ) : ?>
			<ul>
			<?php foreach( $message[0]['mess_dispatch_date'] as $date) : ?>
				<li><?php echo mysql2date( get_option('date_format'), $date, true ); ?></li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		</div>
	</div>
	<?php endforeach; ?>
</div>
