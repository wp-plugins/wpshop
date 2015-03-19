<?php if( is_user_logged_in() ): ?>
	<?php if(!empty($user_meta)):?>
		<?php foreach($user_meta as $name => $meta):?>
			<!-- 
			<div class='alignleft margin-right-20'><input type='button' class='button-secondary wps-add-product-to-wishlist wpshop_add_to_cart_button wps-bton-first-mini-rounded' value='<?php echo $name; ?>' /></div>
			-->
			<button class="wps-add-product-to-wishlist wps-bton-first-mini-rounded"><?php echo $name; ?></button>
		<?php endforeach; ?>
	<?php endif; ?>
	
	<p class='clear'></p>
	
	<input type='hidden' class='wps-product-id' value='<?php echo $postID; ?>' />
	<input type='text' class='wps-name-wishlist' placeholder='<?php _e('Please enter the name of wishlist', 'wps_wishlist_i18n');?>' />
	<button class='wps-bton-first-mini-rounded create-wishlist-and-add-product-to-it'><?php _e('Create and add my product', 'wps_wishlist_i18n'); ?></button>
<?php endif; ?>
