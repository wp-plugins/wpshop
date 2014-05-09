<span class="wps-h5"><?php echo $address_title; ?></span>
<?php if ( !empty($list_addresses) ){ ?>
<div id="#" class="wps-form">
	<div class="wps-gridwrapper">
		<div class="wps-grid5x6">
			<select name="select" class="wps-change-adresse" id="<?php echo $select_id; ?>">
				<?php 
				$i = 0;
				foreach( $list_addresses as $address_id => $address ) : ?>
					<option data-target="wps-adress-<?php echo $address_id; ?>" <?php echo ( ($i == 0) ? 'selected="selected"' : '' ); ?> value="<?php echo $address_id; ?>" ><?php echo $address['address_title']; ?></option>
				<?php 
				$i++;
				endforeach; ?>
			</select>
		</div>
		<div class="wps-grid1x6">
			<button class="wps-bton-icon-plus-tooltip wps-plus wps-add-an-address" id="wps-add-an-address-<?php echo $address_type_id; ?>-<?php echo $address_id; ?>" href="#" title="<?php _e( 'Add', 'wpshop' ); ?>"><span><?php _e( 'Add', 'wpshop' ); ?></span></button>
		</div>
	</div>
</div>
<div class="wps-adresse-listing-select">
	<?php if( !empty( $list_addresses) ) : 
			$i = 0;
	?>
		<?php foreach( $list_addresses as $address_id => $address ) : ?>
			
			<div class="wps-adresse <?php echo ( ($i == 0) ? ' wps-activ': '' ); ?>" data-slug="wps-adress-<?php echo $address_id; ?>">
				<address>
				<?php echo wps_address::display_an_address( $address, $address_id );  ?>
					<div class="wps-gridwrapper2-padded">
						<div><button class="wps-bton-icon-pencil wps-edit wps-address-edit-address" id="wps-address-edit-address-<?php echo $address_id; ?>" href="#" title="<?php _e( 'Edit this address', 'wpshop' ); ?>"><span><?php _e( 'Modify', 'wpshop' ); ?></span></button></div>
						<div><button class="wps-bton-icon-trash wps-erase wps-address-delete-address" id="wps-address-delete-address-<?php echo $address_id; ?>-<?php echo $address_type_id; ?>" href="#" title="<?php _e( 'Delete this address', 'wpshop' ); ?>"><span><?php _e( 'Delete', 'wpshop' ); ?></span></button></div>
					</div>	
				</address>
			</div>
		<?php 
		$i++;
		endforeach; ?>
	<?php endif; ?> 
</div>
<?php }
else {
?>
	<?php printf( __( 'You don\'t have a %s', 'wpshop'), strtolower( $address_title) ); ?><br/>
	<button class="wps-bton-first-mini-rounded wps-add-an-address" id="wps-add-an-address-<?php echo $address_type_id; ?>" ><?php _e( 'Add an address', 'wpshop' ); ?></button>
<?php } ?>
