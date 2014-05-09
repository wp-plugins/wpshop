<?php
/**
 * Plugin Name: WP-Shop-marketing_messages
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Marketing messages 
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * Marketing messages bootstrap file
 * @author Alexandre Techer - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 * 
 */
 
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

if ( !class_exists("wpshop_marketing_messages") ) {
	class wpshop_marketing_messages {
		function __construct() {
			add_action('wsphop_options', array('wpshop_marketing_messages', 'declare_options'), 8);
			add_action('wpshop_free_shipping_cost_alert', array('wpshop_marketing_messages', 'display_free_shipping_cost_alert'));
			add_shortcode('display_save_money_message', array(&$this, 'display_save_money_message'));
			
			/** Template include **/
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
		}
		
		/** Load module/addon automatically to existing template list
		 *
		 * @param array $templates The current template definition
		 *
		 * @return array The template with new elements
		 */
		function custom_template_load( $templates ) {
			include('templates/wpshop/main_elements.tpl.php');
			$templates = wpshop_display::add_modules_template_to_internal( $tpl_element, $templates );
			unset($tpl_element);
		
			return $templates;
		}
		
		public static function declare_options () {
			if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation')) && !isset($_POST['old_wpshop_shop_type']) || (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation')) ){
				register_setting('wpshop_options', 'wpshop_cart_option', array('wpshop_marketing_messages', 'wpshop_options_validate_free_shipping_cost_alert'));
				add_settings_field('wpshop_free_shipping_cost_alert', __('Display a free shipping cost alert in the cart', 'wpshop'), array('wpshop_marketing_messages', 'wpshop_free_shipping_cost_alert_field'), 'wpshop_cart_info', 'wpshop_cart_info');
			}
		}
		
		function wpshop_free_shipping_cost_alert_field () {
			$cart_option = get_option('wpshop_cart_option');
			$input_def = array();
			$input_def['name'] = '';
			$input_def['id'] = 'wpshop_cart_option[free_shipping_cost_alert]';
			$input_def['type'] = 'checkbox';
			$input_def['valueToPut'] = 'index';
			$input_def['value'] = !empty($cart_option['free_shipping_cost_alert']) ? $cart_option['free_shipping_cost_alert'][0] : '';
			$input_def['possible_value'] = 'yes';
			$output = wpshop_form::check_input_type($input_def, 'wpshop_cart_option[free_shipping_cost_alert]') . '<a href="#" title="'.__('Check this box if you want to display an free shipping cost in the mini-cart','wpshop').'" class="wpshop_infobulle_marker">?</a>';
		
			echo $output;
		}
		
		function wpshop_options_validate_free_shipping_cost_alert ($input) {
			return $input;
		}
		
		/**
		 * Display a free Shipping cost alert in cart and shop
		 */
		function display_free_shipping_cost_alert () {
			global $wpdb;
		
			$output = '';
			$cart = ( !empty($_SESSION['cart']) && is_array($_SESSION['cart']) ) ? $_SESSION['cart'] : null;
			$cart_option = get_option('wpshop_cart_option');
			$price_piloting_option = get_option('wpshop_shop_price_piloting');
			$shipping_rules_option = get_option('wpshop_shipping_rules');
			if ( !empty($shipping_rules_option) && !empty($shipping_rules_option['free_from']) && $shipping_rules_option['free_from'] > 0 )
			$free_shipping_cost_limit = $shipping_rules_option['free_from'];
			if ( !empty($cart_option) && !empty($cart_option['free_shipping_cost_alert']) ) {
 				if ( !empty($cart['order_items']) && !empty($cart['order_grand_total'])) {
 					$order_amount = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? number_format((float)$cart['order_total_ht'], 2, '.', '') : number_format((float)$cart['order_total_ttc'], 2, '.', '');
					if ( $order_amount  < $free_shipping_cost_limit ) {
						$free_in = number_format((float)($free_shipping_cost_limit - $order_amount), 2, '.', '');
						$currency = wpshop_tools::wpshop_get_currency();
						$output = sprintf(__('Free shipping cost in %s', 'wpshop'), $free_in. ' ' . $currency);
					}
					else {
						$output = __('Free shipping cost', 'wpshop');
					}
 				}
			}
			echo $output;
		}
		
		function display_message_you_save_money ( $price_infos ) {
			$output = '';
			if ( !empty($price_infos) ) {
				if ( !empty($price_infos) && !empty($price_infos['discount']) && !empty($price_infos['discount']['discount_exist']) ) {
					$tax_piloting_option = get_option('wpshop_shop_price_piloting');
					$save_amount = ( !empty($tax_piloting_option) && $tax_piloting_option == 'HT') ? ($price_infos['et'] - $price_infos['discount']['discount_et_price']) : ($price_infos['ati'] - $price_infos['discount']['discount_ati_price']);
					$output = wpshop_display::display_template_element('wpshop_marketing_message_save_money', array('SAVING_MONEY_AMOUNT' => number_format($save_amount,2) ), array(), 'wpshop');// sprintf(__('You save %s', 'wpshop'), number_format($save_amount,2). wpshop_tools::wpshop_get_currency() );
				}
			}
			return $output;
		}
		
		
	}
}
/**	Instanciate the module utilities if not	*/
if ( class_exists("wpshop_marketing_messages") ) {
	$wpshop_marketing_messages = new wpshop_marketing_messages();
}