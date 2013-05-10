<?php

$tpl_element = array();

/**	Form field	*/
ob_start();
?>
<div><label{WPSHOP_FIELD_LABEL_POINTER}>{WPSHOP_FIELD_LABEL_TEXT}</label> : {WPSHOP_FIELD_INPUT}</div><?php
$tpl_element['wpshop']['default']['advanced_search_form_input'] = ob_get_contents();
ob_end_clean();


/**	Form	*/
ob_start();
?>
<form method="post" >
	<div><label for="wpshop_search_post_title" ><?php _e('Name','wpshop'); ?></label> : <input type="text" class="wpshop_advanced_search_field wpshop_advanced_search_field_post_title" name="wpshop_search_post_title" name="wpshop_search_post_title"  value="{WPSHOP_SEARCHED_POST_TITLE}" /></div>
	{WPSHOP_SPECIAL_FIELDS}
	<input type="submit" name="search" value="<?php _e('Search','wpshop'); ?>" />
</form><?php
$tpl_element['wpshop']['default']['advanced_search_form'] = ob_get_contents();
ob_end_clean();