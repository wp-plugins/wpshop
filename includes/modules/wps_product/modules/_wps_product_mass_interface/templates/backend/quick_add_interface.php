<div class="wps-product-mass-interface-top" >
	<button class="button button-primary alignright" id="wps-mass-interface-button-new-product" ><?php _e( 'Create a new product', 'wps-product-mass-interface-i18n' ); ?></button>
</div>


<div class="wps-product-mass-interface-main wps-bloc-loader" >
	<form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post" >
		<input type="hidden" name="action" value="wps_save_product_quick_interface"  />
		<?php require( wpshop_tools::get_template_part( WPS_PDCT_MASS_DIR, WPS_PDCT_MASS_TEMPLATES_MAIN_DIR, "backend", "quick_add_interface", "product_list" ) ); ?>
	</form>
</div>

<div class="wps-product-mass-interface-bottom" >
	<?php echo $pagination; ?>
	<button class="button button-secondary alignright" id="wps-mass-interface-button-cancel" ><?php _e( 'Cancel', 'wps-product-mass-interface-i18n' ); ?></button>
	<button class="button button-primary alignright" id="wps-mass-interface-button-save" ><?php _e( 'Save selected product', 'wps-product-mass-interface-i18n' ); ?></button>&nbsp;&nbsp;
</div>

<script type="text/javascript" >
	jQuery(document).ready( function(){
		/**	Change the location of bottolm buttons (save/cancel and pagination)	*/
		jQuery( "#TB_ajaxContent" ).before( jQuery( ".wps-product-mass-interface-top" ) );
		jQuery( "#TB_ajaxContent" ).after( jQuery( ".wps-product-mass-interface-bottom" ) );

		jQuery( "#TB_ajaxContent .wpshop_icons_add_new_value_to_option_list" ).remove();
	});
</script>