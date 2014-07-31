<div class="<?php echo $extra_class; ?>">
	<div class="wps-gridwrapper">
		<div class="wps-grid4x6"><span class="wps-h3"><?php echo $address_title; ?></span></div>
		<div class="wps-grid2x6">
			<?php if( !$is_from_admin ) : ?>
				<button id="wps-add-an-address-<?php echo $address_type_id; ?>" class="wps-bton-first-mini-rounded wps-add-an-address"><i class="wps-icon-plus"></i><?php printf( __('Add a %s', 'wpshop' ), strtolower($address_title) ); ?></button>
			<?php endif; ?>
		</div>
	</div>
	<ul class="wps-itemList wps-address-container" id="wps-address-container-<?php echo $address_type_id; ?>">
		<?php if( !empty($box_content) ): ?>
		<?php echo $box_content; ?>
		<?php else : ?>
			<div class="wps-alert-info"><?php printf( __( 'You do not have create a %s', 'wpshop'), strtolower( $address_title ) ); ?></div>
		<?php endif; ?>
	</ul>
</div>
<?php if ( $first_address_checking && !$is_from_admin ) : ?>
			<div class="wps_address_use_same_addresses"><input id="wps-shipping_to_billing" type="checkbox" checked="checked" /> <label for="wps-shipping_to_billing"><?php _e( 'Use the same address for billing', 'wpshop')?></label></div>
<?php endif; ?>
