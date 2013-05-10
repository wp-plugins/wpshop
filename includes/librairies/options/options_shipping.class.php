<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Shipping options management
*
* Define the different method to manage the different shipping options
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different shipping options
* @package wpshop
* @subpackage librairies
*/
class wpshop_shipping_options {
	/**
	*
	*/
	function declare_options(){
		add_settings_section('wpshop_shipping_rules', __('Shipping', 'wpshop'), array('wpshop_shipping_options', 'plugin_section_text'), 'wpshop_shipping_rules');
			register_setting('wpshop_options', 'wpshop_shipping_rules', array('wpshop_shipping_options', 'wpshop_options_validate_shipping_rules'));
			add_settings_field('wpshop_shipping_rule_by_min_max', __('Min-Max shipping fees', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_rule_by_min_max_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			add_settings_field('wpshop_shipping_rule_free_from', __('Free shipping', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_rule_free_from_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			add_settings_field('wpshop_shipping_rule_free_shipping', '', array('wpshop_shipping_options', 'wpshop_shipping_rule_free_shipping'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			// add_settings_field('wpshop_shipping_rule_free_shipping_from_date', '', array('wpshop_shipping_options', 'wpshop_shipping_rule_free_shipping_from_date'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			// add_settings_field('wpshop_shipping_rule_free_shipping_to_date', '', array('wpshop_shipping_options', 'wpshop_shipping_rule_free_shipping_to_date'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			//add_settings_field('wpshop_shipping_rule_by_weight', __('By weight', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_rule_by_weight_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			//add_settings_field('wpshop_shipping_rule_by_percent', __('By percent', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_rule_by_percent_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			//add_settings_field('wpshop_shipping_rule_by_nb_of_items', __('By number of items', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_rule_by_nb_of_items_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
		/* SHIPPING MODE */
		add_settings_section('wpshop_shipping_mode', __('Shipping mode', 'wpshop'), array('wpshop_shipping_options', 'plugin_section_text'), 'wpshop_shipping_mode');
 	
		/* SHIPPING ADDRESS CHOICE */
		register_setting('wpshop_options', 'wpshop_shipping_address_choice', array('wpshop_shipping_options', 'wpshop_shipping_address_validator'));
		add_settings_field('wpshop_shipping_address_choice', __('Shipping address choice', 'wpshop'), array('wpshop_shipping_options', 'wpshop_shipping_address_field'), 'wpshop_shipping_mode', 'wpshop_shipping_mode');
	}

	// Common section description
	function plugin_section_text() {
		echo '';
	}


	
	
	function wpshop_shipping_rule_by_min_max_field() {
		$currency_code = wpshop_tools::wpshop_get_currency();
		$rules = get_option('wpshop_shipping_rules');
		$default_rules = unserialize(WPSHOP_SHOP_SHIPPING_RULES);
		
		$output  = '<input type="checkbox" name="wpshop_shipping_rules[min_max][activate]" id="wpshop_shipping_rules_min_max_activate" ' .( (!empty($rules) && !empty($rules['min_max']) && !empty($rules['min_max']['activate']) ) ? 'checked="checked"' : ''). ' /> '.__('Activate the min. and max. shipping cost', 'wpshop');
		$output .= '<div id="min_max_shipping_rules_configuration" ' .( (!empty($rules) && !empty($rules['min_max']) && !empty($rules['min_max']['activate']) ) ? '' : 'class="wpshopHide"'). '>';
		$output .= __('Minimum', 'wpshop').' : <input type="text" name="wpshop_shipping_rules[min_max][min]" id="wpshop_shipping_rules[min_max][min]" value="' .( (!empty($rules) && !empty($rules['min_max']) && !empty($rules['min_max']['min']) ) ?  $rules['min_max']['min'] : 0). '" style="width:50px" /> '.$currency_code.' '; 
		$output .= __('Maximum', 'wpshop').' : <input type="text" name="wpshop_shipping_rules[min_max][max]" id="wpshop_shipping_rules[min_max][max]" value="' .( (!empty($rules) && !empty($rules['min_max']) && !empty($rules['min_max']['max']) ) ?  $rules['min_max']['max'] : 100). '" style="width:50px" /> '.$currency_code; 
		$output .= '</div>';
				
		echo $output;
	}

	function wpshop_shipping_rule_free_from_field() {
		$default_rules = unserialize(WPSHOP_SHOP_SHIPPING_RULES);
		$rules = get_option('wpshop_shipping_rules');
		if(empty($rules)) $rules = $default_rules;

		/*	Free shipping for all orders	*/
		echo '<div class="wpshop_free_fees" ><input type="checkbox" id="wpshop_shipping_rule_free_shipping" ' . (isset($rules['wpshop_shipping_rule_free_shipping']) && ($rules['wpshop_shipping_rule_free_shipping']) ? ' checked="checked" ' : '') . ' name="wpshop_shipping_rules[wpshop_shipping_rule_free_shipping]" />&nbsp;<label for="wpshop_shipping_rule_free_shipping" >'.__('Free shipping for all order', 'wpshop').'</label>
		<a href="#" title="'.__('Activate free shipping for all orders','wpshop').'" class="wpshop_infobulle_marker">?</a></div>';

		/*	Free shipping from given order amount	*/
		echo '<div class="wpshop_free_fees" ><input type="checkbox" id="wpshop_shipping_fees_freefrom_activation" name="wpshop_shipping_rules[free_from_active]" '.( (!empty($rules) && !empty($rules['free_from_active']) ) ? 'checked="checked"' : '').' />&nbsp;<label for="wpshop_shipping_fees_freefrom_activation" >'.__('Activate free shipping cost starting from an amount','wpshop').'</label>
<a href="#" title="'.__('Apply free shipping from the indicate amount. You can deactivate this option.','wpshop').'" class="wpshop_infobulle_marker">?</a></div>';
	}

	function wpshop_shipping_rule_free_shipping() {
		$currency_code = wpshop_tools::wpshop_get_currency();

		$default_rules = unserialize(WPSHOP_SHOP_SHIPPING_RULES);
		$rules = get_option('wpshop_shipping_rules');
		if(empty($rules))
			$rules = $default_rules;
		elseif(empty($rules['free_from']) || ($rules['free_from']<0)){
			$rules['free_from']=$default_rules['free_from'];
			$activated=false;
		}
		$output  = '<div class="wpshop_shipping_method_parameter wpshop_shipping_fees_freefrom_activation_content'.( (!empty($rules) && empty($rules['free_from_active']) ) ?" wpshopHide" : null ).'" >';
		$output .= __('Free shipping for order over amount below','wpshop').' <input type="text" name="wpshop_shipping_rules[free_from]" id="wpshop_shipping_rules[free_from]" value="' .$rules['free_from']. '" style="width:90px;"/> '.$currency_code;
		$output .= '</div>';
		echo $output;
	}
	function wpshop_shipping_rule_free_shipping_from_date() {
	}
	function wpshop_shipping_rule_free_shipping_to_date() {
	}

	function wpshop_shipping_rule_by_weight_field() {
		$currency = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$currency_code=$currencies[$currency];
		$rules = get_option('wpshop_shipping_rules',array());
		echo '<input type="text" name="priority[]" value="1" style="float:right;width:50px;" />';
		echo '<textarea name="wpshop_shipping_rules[by_weight]" cols="80" rows="4">'.$rules['by_weight'].'</textarea><br />'.__('Example','wpshop').' : 500:5.45,1000:7.20,2000:10.30<br />'.__('Means','wpshop').' : 0 <= Weight < 500 (g) => 5.45 '.$currency_code.' etc..';
	}

	function wpshop_shipping_rule_by_percent_field() {
		$currency = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$currency_code=$currencies[$currency];
		$rules = get_option('wpshop_shipping_rules',array());
		echo '<input type="text" name="priority[]" value="2" style="float:right;width:50px;" />';
		echo '<textarea name="wpshop_shipping_rules[by_percent]" cols="80" rows="4">'.$rules['by_percent'].'</textarea><br />'.__('Example','wpshop').' : 100:8,200:6,300:4<br />'.__('Means','wpshop').' : 0 <= Amount < 100 ('.$currency_code.') => Shipping = 8% etc..';
	}

	function wpshop_shipping_rule_by_nb_of_items_field() {
		$currency = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$currency_code=$currencies[$currency];
		$rules = get_option('wpshop_shipping_rules',array());
		echo '<input type="text" name="priority[]" value="3" style="float:right;width:50px;" />';
		echo '<textarea name="wpshop_shipping_rules[by_nb_of_items]" cols="80" rows="4">'.$rules['by_nb_of_items'].'</textarea><br />'.__('Example','wpshop').' : 5:10,10:12,20:15<br />'.__('Means','wpshop').' : 0 <= Number of items < 5 (items) => 10 '.$currency_code.' etc..';
	}

	function wpshop_options_validate_shipping_rules($input) {
		return $input;
	}

	

	function wpshop_shipping_address_validator($input){

		return $input;
	}

	function wpshop_shipping_address_field() {
		global $wpdb;
		$choice = get_option('wpshop_shipping_address_choice', unserialize(WPSHOP_SHOP_CUSTOM_SHIPPING));
		$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_name = "' .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. '" AND post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES. '"', '');
		$entity_id = $wpdb->get_var($query);

		$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = ' .$entity_id. '', '');
		$content = $wpdb->get_results($query);

		$input_def['name'] = 'wpshop_shipping_address_choice[choice]';
		$input_def['id'] = 'wpshop_shipping_address_choice[choice]';
		$input_def['possible_value'] = $content;
		$input_def['type'] = 'select';
		$input_def['value'] = $choice['choice'];

		$active = !empty($choice['activate']) ? $choice['activate'] : false;

		echo '<input type="checkbox" name="wpshop_shipping_address_choice[activate]" id="wpshop_shipping_address_choice[activate]" '.($active ? 'checked="checked"' :null).'/> <label for="active_shipping_address">'.__('Activate shipping address','wpshop').'</label></br/>
		<div">' .wpshop_form::check_input_type($input_def). '</div>';

	}
}































