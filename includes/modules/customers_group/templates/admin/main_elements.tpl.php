<?php
$tpl_element = array();

/**
 * CUSTOMERS GROUP INTERFACE
*/
ob_start();
?>
{WPSHOP_INTERFACE_HEADER}
<form method="post">
<p>
	<label><?php _e('Name','wpshop'); ?></label><br />
	<input type="text" name="group-name" value="{WPSHOP_CUSTOMER_GROUP_NAME}" {WPSHOP_READ_ONLY_FIELD} />
</p>

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
<p>
<input type="submit" class="button-primary" name="{WPSHOP_SUBMIT_BUTTON_NAME}" value="{WPSHOP_SUBMIT_BUTTON_VALUE}" /> &nbsp;&nbsp;&nbsp; <a href="admin.php?page={WPSHOP_NEWTYPE_IDENTIFIER_GROUP}"><?php _e('Cancel','wpshop'); ?></a>
</p>
</form>
<?php 
$tpl_element['admin']['default']['wpshop_customer_groups_interface'] = ob_get_contents();
ob_end_clean();
?>