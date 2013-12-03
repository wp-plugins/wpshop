<?php if ( !empty($addresses) ) : ?>
<ul class="wps-addresses-list" >
	<?php
		foreach ( $addresses as $address_type => $addresses_list_by_type ) :
			foreach ( $addresses_list_by_type as $address_id => $address ) :
	?>
	<li id="wps-address-item-<?php echo $address_id ; ?>" >
	<?php require( WPS_LOCALISATION_BACKEND_TPL_DIR . 'address.php' ); ?>
	</li>
	<?php
			endforeach;
		endforeach;
	?>
</ul>
<?php else: ?>
<span class="wps-addresses-list wps-no-result" ><?php _e( 'No addresses founded', 'wpeo_geoloc' ); ?></span>
<?php endif; ?>