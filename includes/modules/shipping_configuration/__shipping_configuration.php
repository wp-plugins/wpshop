<?php
/**
 * Plugin Name: WP-Shop-shipping-configuration-module
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: Display an interface for custom shipping fees
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * Shipping configuration module bootstrap file
 *
 * @author Alexandre Techer - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 */


/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}
if ( !class_exists("wpshop_shipping_configuration") ) {
	class wpshop_shipping_configuration {
		function __construct() {
			global $wpdb;

			$locale = get_locale();
			if ( defined("ICL_LANGUAGE_CODE") ) {
				$query = $wpdb->prepare("SELECT locale FROM " . $wpdb->prefix . "icl_locale_map WHERE code = %s", ICL_LANGUAGE_CODE);
				$local = $wpdb->get_var($query);
				$locale = !empty($local) ? $local : $locale;
			}
			$moFile = dirname(__FILE__).'/languages/wpshop_shipping_configuration-' . $locale . '.mo';
			if ( !empty($locale) && (is_file($moFile)) ) {
				load_textdomain('wpshop_shipping_configuration', $moFile);
			}

			add_action('wp_print_scripts', array(&$this, 'admin_print_js'));

			add_action('admin_init', array(&$this, 'create_shipping_configuration_option'));
			if ( is_admin() ) {
				wp_register_style( 'wpshop_shipping_rules_configuration_css', plugins_url('templates/backend/css/wpshop_shipping_rules_configuration.css', __FILE__) );
				wp_enqueue_style( 'wpshop_shipping_rules_configuration_css' );
			
				wp_enqueue_script("jquery");
				wp_enqueue_script( 'wpshop_shipping_rules_configuration', plugins_url('templates/backend/js/shipping_rules_configuration.js', __FILE__) );
			}
			add_action('wp_ajax_save_shipping_rule',array(&$this, 'wpshop_ajax_save_shipping_rule'));
// 			add_action('wp_ajax_display_shipping_rules',array(&$this, 'wpshop_ajax_display_shipping_rules'));
			add_action('wp_ajax_delete_shipping_rule',array(&$this, 'wpshop_ajax_delete_shipping_rule'));

		/**	Add custom template for current module	*/
			add_filter( 'wpshop_custom_template', array( 'wpshop_shipping_configuration', 'custom_template_load' ) );
		}

		function admin_print_js() {
			echo '
				<script type="text/javascript">
					var WPSHOP_APPLY_MAIN_RULE_FOR_POSTCODES = "' . __('Apply a common rule to all others postcodes','wpshop_shipping_configuration'). '";
					var WPSHOP_APPLY_MAIN_RULE_FOR_COUNTRIES = "' . __('Apply a common rule to all others countries','wpshop_shipping_configuration'). '";
				</script>';
		}

		public static function custom_template_load( $templates ) {
			include('templates/backend/main_elements.tpl.php');

			foreach ( $tpl_element as $template_part => $template_part_content) {
				foreach ( $template_part_content as $template_type => $template_type_content) {
					foreach ( $template_type_content as $template_key => $template) {
						$templates[$template_part][$template_type][$template_key] = $template;
					}
				}
			}
			unset($tpl_element);

			return $templates;
		}

		/**
		 * Create the options for the custom shipping rules configuration
		 */
		function create_shipping_configuration_option () {

			register_setting('wpshop_options', 'wpshop_custom_shipping', array(&$this, 'wpshop_options_validate_shipping_fees'));
			add_settings_field('wpshop_custom_shipping', __('Custom shipping fees', 'wpshop'), array(&$this, 'display_custom_shipping_configuration_interface'), 'wpshop_shipping_mode', 'wpshop_shipping_mode');
		
			register_setting('wpshop_options', 'wpshop_limit_shipping_destination', array(&$this, 'wpshop_options_validate_shipping_fees_limit_shipping_destination'));
			add_settings_field('wpshop_limit_shipping_destination', __('Limit shipping destination', 'wpshop'), array(&$this, 'wpshop_limit_shipping_destination_field'), 'wpshop_shipping_mode', 'wpshop_shipping_mode');
		}

		/**
		 * Display the shipping cost configuration interface
		 */
		function display_custom_shipping_configuration_interface() {
			global $wpdb;
			$fees = get_option('wpshop_custom_shipping', unserialize(WPSHOP_SHOP_CUSTOM_SHIPPING));
			$fees_data = $fees['fees'];
			$fees_active = $fees['active'];
			$fees_active_cp = $fees['active_cp'];


			if(is_array($fees_data)) {
				$fees_data = wpshop_shipping::shipping_fees_array_2_string($fees_data);
			}
			$tpl_component['CUSTOM_SHIPPING_ACTIVE'] = ($fees_active?'checked="checked"':null);
			$tpl_component['CUSTOM_SHIPPING_ACTIVE_CP'] = ($fees_active_cp ?'checked="checked"':null);
			$tpl_component['CUSTOM_SHIPPING_SHOW_INTERFACE'] = (!$fees_active?' wpshopHide':null);
			$tpl_component['CUSTOM_SHIPPING_FEES_DATA'] = $fees_data;
			$country_list = unserialize(WPSHOP_COUNTRY_LIST);
			if ( !empty($country_list) ) {
				$tpl_component['CUSTOM_SHIPPING_COUNTRY_LIST'] = '<option value="0">' .__('Choose a country', 'wpshop_shipping_configuration'). '</option>';
				foreach( $country_list as $k=>$country) {
					$tpl_component['CUSTOM_SHIPPING_COUNTRY_LIST']  .= '<option value="' .$k. '">' .$country. '</option>';
				}
			}
			/**
			 * Get the Weight default unity
			 */
			$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
			$query = $wpdb->prepare('SELECT name FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
			$unity = $wpdb->get_var( $query );
			$tpl_component['SHIPPING_WEIGHT_UNITY'] = $unity;
			/**
			 * Get the shop default currency
			 */
			$currency_defaut_option = get_option ('wpshop_shop_default_currency');
			$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $currency_defaut_option);
			$currency = $wpdb->get_var( $query );
			$tpl_component['DEFAULT_CURRENCY'] = $currency;
			$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
			$tpl_component['MEDIAS_ICON_URL'] = WPSHOP_MEDIAS_ICON_URL;
			$output = wpshop_display::display_template_element('shipping_configuration_interface', $tpl_component, array(), 'admin');
			unset($tpl_component);
			echo $output;
		}

		/**
		 * Validate custom shipping rules configuration fields
		 * @param  $input
		 * @return multitype:boolean Ambigous <$data, multitype:, multitype:multitype:NULL multitype:NULL  multitype:string   >
		 */
		function wpshop_options_validate_shipping_fees($input) {
			$fees = array();
			$fees['fees'] = wpshop_shipping::shipping_fees_string_2_array($input);
			$fees['active'] = isset($_POST['custom_shipping_active']) && $_POST['custom_shipping_active']=='on';
			$fees['active_cp'] = isset($_POST['custom_shipping_active_cp']) && $_POST['custom_shipping_active_cp']=='on';

			return $fees;
		}

	 	function wpshop_ajax_save_shipping_rule(){
		 	global $wpdb;
		 	$status = false;
		 	$reponse = array();
		 	$fees_data = ( !empty($_POST['fees_data']) ) ?  $_POST['fees_data'] : null;
		 	$weight_rule = ( !empty($_POST['weight_rule']) ) ? wpshop_tools::varSanitizer( $_POST['weight_rule'] ) : null;
		 	$shipping_price = ( !empty($_POST['shipping_price']) ) ? wpshop_tools::varSanitizer( $_POST['shipping_price'] ) : 0;
		 	$selected_country = ( !empty($_POST['selected_country']) ) ? wpshop_tools::varSanitizer( $_POST['selected_country'] ) : null;
		 	$shipping_rules = wpshop_shipping::shipping_fees_string_2_array( stripslashes($fees_data) );
		 	
		 	$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
		 	$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
		 	$unity = $wpdb->get_var( $query );
		 	
		 	$weight_rule = ( !empty($unity) && $unity == 'kg' ) ? $weight_rule * 1000 : $weight_rule;
		 	//Check if this shipping rule (same country and same weight) already exist in the shipping rules definition
		 	if( !empty($shipping_rules) ) {
		 		$existing_country = false;
		 		$tab_key = -1;
		 		foreach ( $shipping_rules as $key=>$shipping_rule) {
					if ( $shipping_rule['destination'] == $selected_country) {
						$existing_country = true;
						$tab_key = $key;
					}
		 		}
		 		if ( $existing_country && $tab_key > -1) {
		 			$shipping_rules[$tab_key]['fees'][$weight_rule] = $shipping_price;
		 		}
		 		else {
		 			$shipping_rules[] = array( 'destination' => $selected_country, 'rule' => 'weight', 'fees' => array($weight_rule => $shipping_price) );
		 		}
		 		$status = true;
		 	}
		 	else {
		 		$shipping_rules = array( '0' => array('destination' => $selected_country, 'rule' => 'weight', 'fees' => array( $weight_rule => $shipping_price)) );
		 		$status = true;
		 	}
		 	$reponse = array('status' => $status, 'reponse' => wpshop_shipping::shipping_fees_array_2_string( $shipping_rules ) );
		 	echo json_encode($reponse);
		 	die();
		 }

		 /**
		  * Display all saved rules
		  */
		 function wpshop_ajax_display_shipping_rules () {
		 	global $wpdb;
		 	$status = false;
		 	$fees_data = ( !empty($_POST['fees_data']) ) ? $_POST['fees_data'] : null;
		 	$shipping_rules = wpshop_shipping::shipping_fees_string_2_array( stripslashes($fees_data) );
		 	$result = '';
		 	$tpl_component ='';
		 	$tpl_component['CUSTOM_SHIPPING_RULES_LINES'] = '';
		 	$country_list = unserialize(WPSHOP_COUNTRY_LIST);
		 	$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
		 	$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
		 	$unity = $wpdb->get_var( $query );
		 	$currency_defaut_option = get_option ('wpshop_shop_default_currency');
		 	$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $currency_defaut_option);
		 	$currency = $wpdb->get_var( $query );
		 	if ( !empty($shipping_rules) ) {
			 	foreach ( $shipping_rules as $shipping_rule ) {
			 		$country_name = '';
			 		$code_country = explode('-', $shipping_rule['destination']);
			 		$code_country = $code_country[0];
			 		foreach ( $country_list as $key=>$country ) {
			 			if (  $key == $code_country ) {
			 				$country_name = $country;
			 			}
			 		}
			 		if ( !empty($shipping_rule['fees']) ) {
				 		foreach( $shipping_rule['fees'] as $k=>$fee ) {
				 			$tpl_line_component['SHIPPING_RULE_DESTINATION'] = $shipping_rule['destination'];
				 			$tpl_line_component['SHIPPING_RULE_COUNTRY'] = $country_name;
				 			$tpl_line_component['SHIPPING_RULE_WEIGHT'] = ($unity == 'kg') ? $k / 1000 : $k;
				 			$tpl_line_component['SHIPPING_RULE_WEIGHT_UNITY'] = $unity;
				 			$tpl_line_component['SHIPPING_RULE_FEE'] = $fee;
				 			$tpl_line_component['SHIPPING_RULE_WEIGHT_CURRENCY'] = $currency;
				 			$tpl_line_component['MEDIAS_ICON_URL'] = WPSHOP_MEDIAS_ICON_URL;
	
				 			$tpl_component['CUSTOM_SHIPPING_RULES_LINES'] .=   wpshop_display::display_template_element('shipping_rules_table_line', $tpl_line_component, array(), 'admin');
				 			unset($tpl_line_component);
				 		}
			 		}
			 	
			 	}
			 	$result = wpshop_display::display_template_element('shipping_rules_table', $tpl_component, array(), 'admin');
				unset($tpl_component);
		 	}
		 	$status = true;
		 	
		 	
		 	echo json_encode(array('status' => $status, 'reponse' => $result));
		 	die();
		 }

		 /**
		  * Delete a Shipping Rule
		  */
		 function wpshop_ajax_delete_shipping_rule() {
		 	global $wpdb;
		 	$fees_data = ( !empty($_POST['fees_data']) ) ? $_POST['fees_data'] : null;
		 	$country_and_weight =  ( !empty($_POST['country_and_weight']) ) ? $_POST['country_and_weight'] : null;
		 	$datas = explode("|", $country_and_weight);
		 	$country = $datas[0];
		 	$weight = $datas[1];
		 	$shipping_rules = wpshop_shipping::shipping_fees_string_2_array( stripslashes($fees_data) );
			
		 	/** Check the default weight unity **/
		 	$weight_unity_id = get_option('wpshop_shop_default_weight_unity');
		 	if ( !empty($weight_unity_id) ) {
		 		$query = $wpdb->prepare('SELECT unit FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id=%d', $weight_unity_id);
		 		$weight_unity = $wpdb->get_var( $query );
		 		
		 		if( $weight_unity == 'kg' ) {
		 			$weight = $weight * 1000;
		 		}
		 	}

		 	if ( array_key_exists($country, $shipping_rules) ) {
		 		if ( array_key_exists((string)$weight, $shipping_rules[$country]['fees']) ) {
		 			unset($shipping_rules[$country]['fees'][$weight]);
		 		}
		 		if ( empty($shipping_rules[$country]['fees']) ) {
		 			unset($shipping_rules[$country]);
		 		}

		 	}
		 	foreach ( $shipping_rules as $k=>$shipping_rule ) {
		 		if ( !isset($shipping_rule['fees']) ) {
		 			unset($shipping_rules[$k]);
		 		}
		 	}
		 	
		 	$status = true;
		 	$reponse = array('status' => $status, 'reponse' => wpshop_shipping::shipping_fees_array_2_string( $shipping_rules ) );
		 	echo json_encode($reponse);
		 	die();
		 }
	
		function wpshop_limit_shipping_destination_field () {
			$limit_country_option = get_option('wpshop_limit_shipping_destination');
			$output  = '<input type="checkbox" name="wpshop_limit_shipping_destination[active]" id="wpshop_limit_shipping_destination_active" ' .( ( !empty($limit_country_option) && !empty($limit_country_option['active'])) ? 'checked="checked"' : ''). ' /> ';
			$output .= '<label for="wpshop_limit_shipping_destination[active]" >' .__('Allowed the sale for a defined country list', 'wpshop'). '</label>';
			$output .= '<div id="wpshop_limit_shipping_destination_interface" class="' . ( ( !empty($limit_country_option) && !empty($limit_country_option['active'])) ? '' : 'wpshopHide') . '">';
			$countries = unserialize(WPSHOP_COUNTRY_LIST);
			if ( !empty ($countries) ) {
				$output .= '<select name="wpshop_limit_shipping_destination[country][]" class="chosen_select" multiple data-placeholder="Choose a Country">';
				foreach( $countries as $key => $country ) {
					$is_selected = ( !empty($limit_country_option) && !empty($limit_country_option['country']) && is_array($limit_country_option['country']) && in_array($key, $limit_country_option['country']) ) ? true : false;
					$output .= '<option value="' .$key. '" ' . ( ($is_selected) ? 'selected="selected"' : '' ) . '>' .$country. '</option>';
				}
				$output .= '</select>';
			}
			$output .= '</div>';
			echo $output;
		}
		
		function wpshop_options_validate_shipping_fees_limit_shipping_destination ($input) {
			return $input;
		}
		
		/**
		 * Check if the shipping address is in an allowed shipping country
		 * @param integer $address_id
		 * @return boolean
		 */
		function is_allowed_country ( $address_id ) {
			$is_allowed_country = true;
			if ( !empty($address_id) ) { 
				$address_post_meta = get_post_meta($address_id, '_wpshop_address_metadata', true);
				$address_type_id = get_post_meta($address_id, '_wpshop_address_attribute_set_id', true);
				$shipping_address_id = get_option( 'wpshop_shipping_address_choice' );
				
				if ( !empty($address_post_meta) && !empty($address_post_meta['country']) ) {
					if ( !empty($shipping_address_id) && !empty($shipping_address_id['activate']) && !empty($shipping_address_id['choice']) && !empty($address_type_id) && $shipping_address_id['choice'] == $address_type_id ) {
						$limit_country_option = get_option('wpshop_limit_shipping_destination');
						if ( !empty($limit_country_option) && !empty($limit_country_option['active']) && !empty($limit_country_option['country']) ) {
							if ( !in_array($address_post_meta['country'], $limit_country_option['country']) ) {
								$is_allowed_country = false;
							}
						}
					}
				}
			}
			return $is_allowed_country;
		}
	}
}
if (class_exists("wpshop_shipping_configuration"))
{
	$inst_wpshop_shipping_partners = new wpshop_shipping_configuration();
}