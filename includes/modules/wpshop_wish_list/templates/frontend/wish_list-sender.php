<div id='receivers_form'>
    <form>
		<input type='text' id='wishlist_email_receivers' name='wishlist_email_receivers' size='50' placeholder='<?php _e('Email of new receiver', 'wp_wish_list'); ?>' />
		<button id='wishlist_button_add_receivers'><?php _e('Add receiver', 'wp_wish_list'); ?></button>
		<br />
		<div id='wishlist_display_error'></div> <!-- to display error on pop up -->
		<br />
		<br />
		<label for='wish_list_receivers'> <?php _e('List of receivers', 'wp_wish_list'); ?></label>
		<SELECT id='wish_list_receivers' class='chosen-select' name='wish_list_receivers[]' multiple='multiple' data-placeholder='<?php _e('Your receivers', 'wp_wish_list'); ?>'>
		</SELECT>
		<br />
		<div id='receivers_form_buttons'>
		    <button type='button' id='wishlist_button_cancel'><?php _e('Cancel', 'wp_wish_list'); ?></button>
		    <button type='button' id='wishlist_button_send'> <img id='receivers_form_status' src='ajax-loader.gif' alt='' /> <?php _e('Send', 'wp_wish_list'); ?></button>
		</div>
    </form>
</div>