<?php
$tpl_element = array();

/**
 * WPS SHIPPING MODE MAIN INTERFACE
 */
ob_start();
?>
<ul id="shipping_mode_list_container">
{WPSHOP_INTERFACES}
</ul>

<div id="add_shipping_mode" style="display:none;">
<h2><?php _e('Shipping Mode Creation', 'wpshop'); ?></h2>
<div class="wps_shipping_mode_configuration_part">
	<p><label><?php _e('Shipping Mode name', 'wpshop'); ?> : </label> <input type="text" id="shipping_mode_name"></p> 
	<p><center><button class="button-primary" id="add_shipping_mode" ><?php _e('Add the shipping mode', 'wpshop'); ?></button><img src="{WPSHOP_LOADER_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>"  id="add_shipping_mode_loader" class="wpshopHide" /></center></p>
</div>
<div id="shipping_mode_creation_error"></div>
</div>
<a href="#TB_inline?width=600&height=200&inlineId=add_shipping_mode" class="thickbox button-secondary" id="create_new_shipping_mode"><?php _e('Create a shipping mode', 'wpshop'); ?></a>


<?php
$tpl_element['admin']['default']['wps_shipping_mode_main'] = ob_get_contents();
ob_end_clean();

/**
 * WPS SHIPPING MODE EACH INTERFACE
 */
ob_start();
?>
<li class="wps_shipping_mode_container" id="container_{WPSHOP_SHIPPING_MODE_ID}">
<div class="shipping_mode_titre">
<label for="wps_shipping_mode_configuration_{WPSHOP_SHIPPING_MODE_ID}_name"><?php _e('Name', 'wpshop'); ?></label> : <input type="text" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][name]" id="wps_shipping_mode_configuration_{WPSHOP_SHIPPING_MODE_ID}_name" value="{WPSHOP_SHIPPING_MODE_NAME}" /><br/>
<label for="{WPSHOP_SHIPPING_MODE_ID}_logo"><?php _e('Logo', 'wpshop'); ?></label> :<input type="file" id="{WPSHOP_SHIPPING_MODE_ID}_logo" name="{WPSHOP_SHIPPING_MODE_ID}_logo" /><input type="hidden" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][logo]" value="{WPSHOP_SHIPPING_MODE_LOGO_POST_ID}" /><br/>
{WPSHOP_SHIPPING_MODE_THUMBNAIL}
</div>
<div class="shipping_mode_little_configuration">
<label for="activate_shipping_mode_{WPSHOP_SHIPPING_MODE_ID}"><?php _e('Activate', 'wpshop')?></label> <input type="checkbox" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][active]" class="shipping_mode_is_active" id="activate_shipping_mode_{WPSHOP_SHIPPING_MODE_ID}" {WPSHOP_SHIPPING_MODE_ACTIVE} />
<br/>	
<label for="{WPSHOP_SHIPPING_MODE_ID}_default"><?php _e('Default shipping mode', 'wpshop'); ?></label> <input type="radio" name="wps_shipping_mode[default_choice]" value="{WPSHOP_SHIPPING_MODE_ID}" id="{WPSHOP_SHIPPING_MODE_ID}_default" {WPSHOP_DEFAULT_SHIPPING_MODE_ACTIVE} />
<br/>
	<div id="{WPSHOP_SHIPPING_MODE_ID}_configuration_interface" style="display:none;" class="wps_shipping_mode_configuration_interface" >
	     {WPSHOP_SHIPPING_MODE_CONFIGURATION_INTERFACE}
	</div>
	<a href="#TB_inline?width=600&height=650&inlineId={WPSHOP_SHIPPING_MODE_ID}_configuration_interface" class="thickbox button-secondary" ><?php _e('Configure the shipping mode', 'wpshop'); ?></a>
</div>
</li>
<?php
$tpl_element['admin']['default']['wps_shipping_mode_each_interface'] = ob_get_contents();
ob_end_clean();



/**
 * WPS SHIPPING MODE EACH INTERFACE
 */
ob_start();
?>
<h2><?php _e('General configurations', 'wpshop')?></h2>
<div class="wps_shipping_mode_configuration_part"><input type="checkbox" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][free_shipping]" id="{WPSHOP_SHIPPING_MODE_ID}_free_shipping" {WPSHOP_FREE_SHIPPING} /> <label for="{WPSHOP_SHIPPING_MODE_ID}_free_shipping"><?php _e('Activate free shipping for all orders', 'wpshop'); ?></label></div>
<div class="wps_shipping_mode_configuration_part"><label for="{WPSHOP_SHIPPING_MODE_ID}_free_from"><?php _e('Free shipping for order over amount below', 'wpshop'); ?></label> <input type="text" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][free_from]" id="{WPSHOP_SHIPPING_MODE_ID}_free_from" value="{WPSHOP_FREE_FROM_VALUE}" class="wps_little_input" /> {WPSHOP_CURRENCY} </div>



<div class="wps_shipping_mode_configuration_part">
	<input type="checkbox" class="wps_shipping_mode_configuation_min_max" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][min_max][activate]" id="{WPSHOP_SHIPPING_MODE_ID}_min_max_activate" {WPSHOP_MIN_MAX_ACTIVATE} /> <label for="{WPSHOP_SHIPPING_MODE_ID}_min_max_activate"><?php _e('Activate the min. and max. shipping cost', 'wpshop'); ?></label>
	<div id="{WPSHOP_SHIPPING_MODE_ID}_min_max_shipping_rules_configuration" class="{WPSHOP_ADDITIONNAL_CLASS} min_max_interface" >
		<div class="min_max_fields"><?php _e('Minimum', 'wpshop'); ?> : <input type="text" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][min_max][min]"  value="{WPSHOP_MIN_VALUE}" style="width:50px" /> {WPSHOP_CURRENCY}</div> 
		<div class="min_max_fields"><?php _e('Maximum', 'wpshop'); ?> : <input type="text" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][min_max][max]"  value="{WPSHOP_MAX_VALUE}" style="width:50px" /> {WPSHOP_CURRENCY}</div>
	</div>
</div>



<h2><?php _e('Countries Shipping Limitation', 'wpshop')?></h2>
<div class="wps_shipping_mode_configuration_part">
<p><?php _e('Choose all countries where you want to ship orders. Let empty you don\'t want limitations', 'wpshop'); ?></p>
<p>
<select name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][limit_destination][country][]" class="chosen_select" multiple data-placeholder="<?php __('Choose a Country', 'wpshop' ); ?>">
{WPSHOP_COUNTRIES_LIST}
</select>
</p>
</div>



<h2><?php _e('Custom shipping rules', 'wpshop'); ?></h2>

<div class="wps_shipping_mode_configuration_part">
<textarea id="{WPSHOP_SHIPPING_MODE_ID}_wpshop_custom_shipping" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][custom_shipping_rules][fees]" class="wpshopHide" >{WPSHOP_CUSTOM_SHIPPING_FEES_DATA}</textarea>
<input type="checkbox" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][custom_shipping_rules][active]" id="{WPSHOP_SHIPPING_MODE_ID}_custom_shipping_active" {WPSHOP_CUSTOM_SHIPPING_RULES_ACTIVE} />

<label for="custom_shipping_active"><?php _e('Activate custom shipping fees','wpshop'); ?></label>
<p><input type="checkbox" id="{WPSHOP_SHIPPING_MODE_ID}_custom_shipping_active_cp" name="wps_shipping_mode[modes][{WPSHOP_SHIPPING_MODE_ID}][custom_shipping_rules][active_cp]" {WPSHOP_CUSTOM_SHIPPING_ACTIVE_CP}/><label for="{WPSHOP_SHIPPING_MODE_ID}_custom_shipping_active_cp"> <?php _e('Activate custom shipping fees by postcode', 'wpshop'); ?></label></p>
<h3><?php _e('Configuration', 'wpshop'); ?></h3>

<table border="0" class="custom_shipping_rules_configuration" cellspacing="15" width="550">
	<tr>
		<td><label for="{WPSHOP_SHIPPING_MODE_ID}_country_list"><?php _e('Choose a country', 'wpshop'); ?> : </label><br/>
			<select id="{WPSHOP_SHIPPING_MODE_ID}_country_list" name="country_list" class="shipping_mode_config_input">
			{WPSHOP_CUSTOM_SHIPPING_COUNTRY_LIST}
			</select>
		</td>
		<td>
			<label for="{WPSHOP_SHIPPING_MODE_ID}_postcode_rule" class="postcode_rule"><?php _e('Postcode', 'wpshop'); ?> : </label><br/>
			<input type="text" name="postcode_rule" id="{WPSHOP_SHIPPING_MODE_ID}_postcode_rule" class="shipping_rules_configuration_input postcode_rule"/>
		</td>
	</tr>
	<tr>
		<td>
			<label for="{WPSHOP_SHIPPING_MODE_ID}_weight_rule"><?php _e('Weight', 'wpshop'); ?> : </label><br/>
			<input type="text" name="weight_rule" id="{WPSHOP_SHIPPING_MODE_ID}_weight_rule" class="shipping_rules_configuration_input"/>({WPSHOP_SHIPPING_WEIGHT_UNITY})
		</td>
		<td>
			<label for="{WPSHOP_SHIPPING_MODE_ID}_shipping_price"><?php _e('Price', 'wpshop'); ?>  : </label><br/>
			<input type="text" name="shipping_price" id="{WPSHOP_SHIPPING_MODE_ID}_shipping_price" class="shipping_rules_configuration_input"/>{WPSHOP_CURRENCY} <?php echo WPSHOP_PRODUCT_PRICE_PILOT; ?>
		</td>
	</tr>
</table>
<input type="checkbox" id="{WPSHOP_SHIPPING_MODE_ID}_main_rule" name="main_rule" value="OTHERS"/> <label for="main_rule" class="global_rule_checkbox_indic"><?php _e('Apply a common rule to all others countries','wpshop'); ?></label><br/><br/>
<center><a id="{WPSHOP_SHIPPING_MODE_ID}_save_rule" class="save_rules_button button-secondary"><?php _e('Add the rule', 'wpshop'); ?></a> <img src="{WPSHOP_LOADER_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>" id="{WPSHOP_SHIPPING_MODE_ID}_add_rule_loader" class="wpshopHide" /></center>

</div>
<div class="wps_shipping_mode_configuration_part" id="{WPSHOP_SHIPPING_MODE_ID}_shipping_rules_container" >
{WPSHOP_CUSTOM_SHIPPING_RULES_DISPLAY}
<img src="{WPSHOP_LOADER_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>" id="{WPSHOP_SHIPPING_MODE_ID}_delete_rule_loader" class="wpshopHide" />
</div>
<!--  
<p><center><button class="button-primary save_shipping_mode_rules" ><?php _e('Save shipping mode configuration', 'wpshop'); ?></button><img src="{WPSHOP_LOADER_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>"  class="wpshopHide save_configuration_loader" /></center></p>
-->
<?php
$tpl_element['admin']['default']['wps_shipping_mode_configuration_interface'] = ob_get_contents();
ob_end_clean();




/**
 * SHIPPING RULES TABLE
 */

ob_start();
?>
<table border="1" width="550" cellpadding="0" cellspacing="0">
<tr>
	<th><?php _e('Country', 'wpshop'); ?></th>
	<th><?php _e('Weight', 'wpshop'); ?></th>
	<th><?php _e('Price', 'wpshop'); ?></th>
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
	<td><div id="{WPSHOP_SHIPPING_RULE_DESTINATION}|{WPSHOP_SHIPPING_RULE_WEIGHT}|{WPSHOP_SHIPPING_MODE_ID}" class="delete_rule" title="{WPSHOP_SHIPPING_MODE_ID}"><img src="{WPSHOP_MEDIAS_ICON_URL}delete.png" alt="<?php _e('Delete', 'wpshop_shipping_configuration'); ?>" /></div></td>
</tr>
<?php
$tpl_element['admin']['default']['shipping_rules_table_line'] = ob_get_contents();
ob_end_clean();



