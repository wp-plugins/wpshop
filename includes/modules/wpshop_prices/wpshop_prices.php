<?php
/**
 * Plugin Name: WP-Shop-marketing_messages
 * Plugin URI: http://www.eoxia.com/wpshop-simple-ecommerce-pour-wordpress/
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
	die( __("You are not allowed to use this service.", 'wp_easy_extends') );
}
if ( !class_exists("wpshop_prices") ) {
	class wpshop_prices {
		function __construct() {
			//add_action('wsphop_options', array('wpshop_prices', 'declare_options'), 8);
		}
		
		function declare_options () {
			register_setting('wpshop_options', 'wpshop_catalog_product_option', array('wpshop_prices', 'wpshop_options_validate_prices'));
			add_settings_field('wpshop_catalog_product_option', __('Activate the discount on products', 'wpshop'), array('wpshop_prices', 'wpshop_activate_discount_prices_field'), 'wpshop_catalog_product_option', 'wpshop_catalog_product_section');
		}
		
		function wpshop_options_validate_prices($input) {
			global $wpdb;
			$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE code = %s OR code = %s', 'discount_amount', 'discount_rate' );
			$discount_attributes_status = $wpdb->get_results($query);
			foreach ( $discount_attributes_status as $discount_attribute_status ) {
				if ( $discount_attribute_status->status == 'notused' && !empty($input['discount']) && $input['discount'] == 'on' ) {
					$update = $wpdb->prepare('UPDATE ' .WPSHOP_DBT_ATTRIBUTE. ' SET status = "valid" WHERE code = %s', $discount_attribute_status->code);
					$wpdb->query($update);
				}
				elseif ( $discount_attribute_status->status == 'valid') {
					$input['discount'] = 'on';
				}
			}
			
			return $input;
		}
		
		function wpshop_activate_discount_prices_field() {
			$product_discount_option = get_option('wpshop_catalog_product_option');
			
			$output  = '<input type="checkbox" id="wpshop_catalog_product_option[discount]" name="wpshop_catalog_product_option[discount]" />';
			$output .= '<a class="wpshop_infobulle_marker" title="' .__('Activate the possibility to create discount on products', 'wpshop'). '" href="#">?</a>';
			echo $output;
		}
		
	}
}
/**	Instanciate the module utilities if not	*/
if ( class_exists("wpshop_prices") ) {
	$wpshop_prices = new wpshop_prices();
}