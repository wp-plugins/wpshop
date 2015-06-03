<div class="<?php echo $extra_class; ?>" <?php if ( $first_address_checking && !$is_from_admin && $type == 'billing' ) { echo 'style="display: none;"'; } ?>>
	<div class="wps-gridwrapper">
		<div class="wps-grid4x6"><span class="wps-h3"><?php echo $address_title; ?></span></div>
	</div>
	<ul class="wps-itemList wps-address-container" id="wps-address-container-<?php echo $address_type_id; ?>">
		<?php if( !empty($box_content) ): ?>
		<?php echo $box_content; ?>
		<?php endif; ?>
	</ul>
	<?php if( !$is_from_admin ) : ?>
		<button id="wps-add-an-address-<?php echo $address_type_id; ?>" class="wps-bton-first-mini-rounded wps-add-an-address"><i class="wps-icon-plus"></i><?php printf( __('Add a %s', 'wpshop' ), strtolower($address_title) ); ?></button>
	<?php endif; ?>
</div>
<?php /*if ( $first_address_checking && !$is_from_admin ) : ?>
			<div class="wps_address_use_same_addresses"><input id="wps-shipping_to_billing" type="checkbox" checked="checked" /> <label for="wps-shipping_to_billing"><?php _e( 'Use the same address for billing', 'wpshop')?></label></div>
<?php endif;*/ ?>
