<?php
/**
* General options management
* 
* Define the different method to manage the different general options
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different general options
* @package wpshop
* @subpackage librairies
*/
class wpshop_general_options
{

	/**
	*
	*/
	function declare_options(){
		add_settings_section('wpshop_general_config', __('Shop main configuration', 'wpshop'), array('wpshop_general_options', 'plugin_section_text'), 'wpshop_general_config');

		register_setting('wpshop_options', 'wpshop_shop_type', array('wpshop_general_options', 'wpshop_options_validate_wpshop_shop_type'));
			add_settings_field('wpshop_shop_type', __('Shop type', 'wpshop'), array('wpshop_general_options', 'wpshop_shop_type'), 'wpshop_general_config', 'wpshop_general_config');

		register_setting('wpshop_options', 'wpshop_shop_default_currency', array('wpshop_general_options', 'wpshop_options_validate_default_currency'));
			add_settings_field('wpshop_shop_default_currency', __('Currency', 'wpshop'), array('wpshop_general_options', 'wpshop_shop_default_currency_field'), 'wpshop_general_config', 'wpshop_general_config');
	}

	// Common section description
	function plugin_section_text() {
		echo '';
	}

	/*	Default currecy for the entire shop	*/
	function wpshop_shop_default_currency_field() {
		$wpshop_shop_currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		$current_currency = get_option('wpshop_shop_default_currency');
		
		$currencies_options = '';
		foreach($wpshop_shop_currencies as $k => $v) {
			$currencies_options .= '<option value="'.$k.'"'.(($k==$current_currency) ? ' selected="selected"' : null).'>'.$k.' ('.$v.')</option>';
		}
		echo '<select name="wpshop_shop_default_currency">'.$currencies_options.'</select> 
		<a href="#" title="'.__('This is the currency the shop will use','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_default_currency($input) {
		return $input;
	}

	/*	Shop type definition	*/
	function wpshop_shop_type() {
		$shop_types = unserialize(WPSHOP_SHOP_TYPES);
		$shop_types_options = '';
		foreach($shop_types as $type) {
			$shop_types_options .= '<option value="'.$type.'"'.(($type==WPSHOP_DEFINED_SHOP_TYPE) ? ' selected="selected"' : null).'>'.__($type, 'wpshop').'</option>';
		}
		echo '<select name="wpshop_shop_type">'.$shop_types_options.'</select><input type="hidden" name="old_wpshop_shop_type" value="'.WPSHOP_DEFINED_SHOP_TYPE.'" />
		<a href="#" title="'.__('Define if you have a shop to sale item or just for item showing','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	function wpshop_options_validate_wpshop_shop_type($input) {
		global $current_db_version;
		$current_db_version['installation_state'] = 'completed';
		update_option('wpshop_db_options', $current_db_version);
		if($input=='sale'){
			wpshop_install::wpshop_insert_default_pages();
		}
		return $input;
	}

}