<div class="wps-boxed">
<span class="wps-h5"><span class="dashicons dashicons-search"></span> <?php _e( 'Search products by letter', 'wpshop' ); ?></span>
<?php foreach( $letters as $letter ) : ?>
<a href="#" class="wps-bton-second-mini-rounded <?php echo ( $current_letter == $letter ) ? 'third' : ''; ?> search_product_by_letter" id="<?php echo $letter; ?>"><?php echo $letter; ?></a>
<?php endforeach; ?>
</div>

<div id="wps_orders_product_listing_table">
<?php require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "product-listing/wps_orders_product_listing_table") ); ?>
</div>



