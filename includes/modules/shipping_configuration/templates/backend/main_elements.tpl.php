<?php
$tpl_element = array();

/**
 * CUSTOM SHIPPING CONFIGURATION INTERFACE
 */
ob_start();
?>
<input type="checkbox" name="custom_shipping_active" id="custom_shipping_active" {WPSHOP_CUSTOM_SHIPPING_ACTIVE} />
<label for="custom_shipping_active"><?php _e('Activate custom shipping fees','wpshop_shipping_configuration'); ?></label><a href="#" title="<?php _e('Custom shipping fees. Edit as you want but respect the syntax.','wpshop_shipping_configuration'); ?>" class="wpshop_infobulle_marker">?</a><br />
<div class="wpshop_shipping_method_parameter custom_shipping_active_content {WPSHOP_CUSTOM_SHIPPING_SHOW_INTERFACE}" >
<h3><?php _e('Custom shipping rules by country', 'wpshop_shipping_configuration'); ?></h3>
<input type="checkbox" id="custom_shipping_active_cp" name="custom_shipping_active_cp" {WPSHOP_CUSTOM_SHIPPING_ACTIVE_CP}/><label for="custom_shipping_active_cp"> <?php _e('Activate custom shipping fees by postcode', 'wpshop_shipping_configuration'); ?></label><br/>
<textarea id="wpshop_custom_shipping" name="wpshop_custom_shipping" class="wpshopHide">{WPSHOP_CUSTOM_SHIPPING_FEES_DATA}</textarea>
<label for="country_list"><?php _e('Country', 'wpshop_shipping_configuration'); ?> : </label>
<select id="country_list" name="country_list">
{WPSHOP_CUSTOM_SHIPPING_COUNTRY_LIST}
</select>
<label for="postcode_rule" class="postcode_rule"><?php _e('Postcode', 'wpshop_shipping_configuration'); ?> : </label><input type="text" name="postcode_rule" id="postcode_rule" class="shipping_rules_configuration_input postcode_rule"/>
 <label for="weight_rule"><?php _e('Weight', 'wpshop_shipping_configuration'); ?> : </label><input type="text" name="weight_rule" id="weight_rule" class="shipping_rules_configuration_input"/>({WPSHOP_SHIPPING_WEIGHT_UNITY})
 <label for="shipping_price"><?php _e('Price', 'wpshop_shipping_configuration'); ?>  : </label><input type="text" name="shipping_price" id="shipping_price" class="shipping_rules_configuration_input"/>{WPSHOP_DEFAULT_CURRENCY} <?php echo WPSHOP_PRODUCT_PRICE_PILOT; ?>
<br/>
<input type="checkbox" id="main_rule" name="main_rule" value="OTHERS"/> <label for="main_rule" class="global_rule_checkbox_indic"><?php _e('Apply a common rule to all others countries','wpshop_shipping_configuration'); ?></label><br/>
<a id="save_rule" class="button-primary"><?php _e('Add the rule', 'wpshop_shipping_configuration'); ?></a>
<div class="loading_picture_container wpshopHide" id="loader_custom_shipping_rules"><img src="{WPSHOP_LOADING_ICON}" alt="Loading"/></div>
<div id="shipping_rules_container"></div>
<img src="{WPSHOP_MEDIAS_ICON_URL}error.gif" alt="" /> <i><?php _e('Don\'t forget to click on "Save Changes" button to save your shipping rules.', 'wpshop_shipping_configuration'); ?></i><br/>
</div>
<?php
$tpl_element['admin']['default']['shipping_configuration_interface'] = ob_get_contents();
ob_end_clean();



/**
 * SHIPPING RULES TABLE
 */

ob_start();
?>
<table border="1" width="450" cellpadding="0" cellspacing="0">
<tr>
	<th><?php _e('Country', 'wpshop_shipping_configuration'); ?></th>
	<th><?php _e('Weight', 'wpshop_shipping_configuration'); ?></th>
	<th><?php _e('Price', 'wpshop_shipping_configuration'); ?></th>
	<th></th>
</tr>
{WPSHOP_CUSTOM_SHIPPING_RULES_LINES}
</table>
<?php
$tpl_element['admin']['default']['shipping_rules_table'] = ob_get_contents();
ob_end_clean();


/**
 * SHIPPING RULES TABLE LINE
 */

ob_start();
?>
<tr>
	<td>{WPSHOP_SHIPPING_RULE_COUNTRY} ({WPSHOP_SHIPPING_RULE_DESTINATION})</td>
	<td>{WPSHOP_SHIPPING_RULE_WEIGHT} {WPSHOP_SHIPPING_RULE_WEIGHT_UNITY}</td>
	<td>{WPSHOP_SHIPPING_RULE_FEE} {WPSHOP_SHIPPING_RULE_WEIGHT_CURRENCY}</td>
	<td><div id="{WPSHOP_SHIPPING_RULE_DESTINATION}|{WPSHOP_SHIPPING_RULE_WEIGHT}" class="delete_rule"><img src="{WPSHOP_MEDIAS_ICON_URL}delete.png" alt="<?php _e('Delete', 'wpshop_shipping_configuration'); ?>" /></div></td>
</tr>
<?php
$tpl_element['admin']['default']['shipping_rules_table_line'] = ob_get_contents();
ob_end_clean();

?>