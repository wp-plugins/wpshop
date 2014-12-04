<div class="wrap">
	<h2><span class="dashicons dashicons-update" style="font-size : 30px; width : 30px; height : 30px"></span> <?php _e( 'Mass edit products interface', 'wpshop')?></h2>
</div>

<div class="wps-boxed">
	<div class="wps-row wps-gridwrapper3-padded">
		<div>
			<div class="wps-form-group">
				<label><?php _e( 'Products Attributes groups', 'wpshop' ); ?> : </label>
				<div class="wps-form">
					<?php if( !empty($products_attributes_groups) ): ?>
					<select id="wps_mass_edit_products_default_attributes_set" />
						<?php foreach( $products_attributes_groups as $products_attributes_group ) : ?>
						<option value="<?php echo $products_attributes_group->id; ?>" <?php echo ( (!empty($products_attributes_group->default_set) && $products_attributes_group->default_set == 'yes' ) ? 'selected="selected"' : '' ); ?>><?php echo $products_attributes_group->name; ?></option>
						<?php endforeach; ?>
					</select>
					<?php endif; ?>
				</div>
			</div>
			<div class="wps-form-group">
				<label><?php _e( 'Pagination', 'wpshop'); ?> :</label>
				<div class="wps-form wps_mass_products_edit_pagination_container"><?php echo $pagination; ?></div>
			</div>
		</div>
		
		<div>&nbsp;</div>
		
		<div>
			<div style="width : 100%" class="alignright"><button class="wps-bton-third-rounded alignright" id="wps-mass-interface-button-new-product"><i class="wps-icon-pencil"></i> <?php _e( 'Create a new product', 'wpshop'); ?></button></div>
			<div style="width : 100%; margin-top : 20px" class="alignright"><button class="wps-bton-first-rounded wps-mass-interface-button-save alignright"><i class="wps-icon-save"></i> <?php _e( 'Save selected products', 'wpshop' ); ?> </button></div>
		</div>
		
	</div>
	
</div>
<div style="display : none" class="wps-alert-error"></div>
<div style="display : none" class="wps-alert-success"></div>

<div id="wps_mass_products_edit_tab_container">

<?php  echo $product_list_interface; ?>

</div>



<div class="wps-boxed">
	<input type="hidden" value="1" id="wps_mass_edit_interface_current_page_id" />
	<div class="wps-row wps-gridwrapper3-padded">
		<div>
			<div class="wps-form-group">
				<label><?php _e( 'Pagination', 'wpshop'); ?> :</label>
				<div class="wps-form wps_mass_products_edit_pagination_container"><?php echo $pagination; ?></div>
			</div>
		</div>
		<div>&nbsp;</div>
		<div>
			<button class="wps-bton-first-rounded alignright wps-mass-interface-button-save"><i class="wps-icon-save"></i> <?php _e( 'Save selected products', 'wpshop' ); ?> </button>
		</div>
	</div>
	
	
	
</div>
