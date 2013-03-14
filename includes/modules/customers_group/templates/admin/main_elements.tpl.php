<?php
$tpl_element = array();

/**
 * CUSTOMERS GROUP INTERFACE
*/
ob_start();
?>

<p>
	<label><?php _e('Parent','wpshop'); ?></label><br />
	<select name="group-parent" class="chosen_select">
		{WPSHOP_SELECTED_PARENT}
	</select>
</p>
<p>
	<label><?php _e('Users','wpshop'); ?></label><br />
	<select name="group-users[]" class="chosen_select" multiple>
			{WPSHOP_SELECTED_USERS}
	</select>
</p>
<p>
	<label><?php _e('Description','wpshop'); ?></label><br />
	<textarea name="group-description">{WPSHOP_CUSTOMER_GROUP_DESCRIPTION}</textarea>
</p>

<?php 
$tpl_element['admin']['default']['wpshop_customer_groups_interface'] = ob_get_contents();
ob_end_clean();
?>