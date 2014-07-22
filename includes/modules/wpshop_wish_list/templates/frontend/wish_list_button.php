<!-- -------------------------------------------------------->
<!-- TEMPLATE OF WISHLIST BUTTON (add and remove product)  -->
<input type='hidden' id='product_ID' value='<?php echo $product_ID ?>' />
<div id='wish_list_button_container'>
<?php
    $wps_wishlist_model = new wpeo_wish_list_model();
    $post_type = get_post_type();

	if ( $post_type == 'wpshop_product' ) :
		$user_ID = get_current_user_id();
		$meta_values = (array)get_user_meta($user_ID, 'wish-list-item', true); // Get all product ID in string
		if ( in_array( $product_ID, $meta_values )) :
			require( $this->get_template_part( WPWISHLIST_DIR, WPWISHLIST_TEMPLATES_DIR, "frontend", "button", "delete") );
		else :
			require( $this->get_template_part( WPWISHLIST_DIR, WPWISHLIST_TEMPLATES_DIR, "frontend", "button", "add") );
		endif;
	endif;
?>
</div>