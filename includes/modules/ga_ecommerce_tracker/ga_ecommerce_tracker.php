<?php
/**
 * Plugin Name: WPSHOP Google Analytics E-Commerce Tracker
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: Display a tracker for Google Analytics E-Commerce
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WPSHOP Google Analytics E-Commerce Tracker module bootstrap file
 *
 * @author Jérôme Allegre - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 */
 
/** Check if the plugin version is defined. If not defined script will be stopped here */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wps_ga_ecommerce_tracker") ) {
	class wps_ga_ecommerce_tracker {
		function __construct() {
			
		}
		
		function display_tracker ( $order_id ) {
			global $wpdb;
			$tracker = $ga_account_id = '';
			
			$ga_account_id = get_option('wpshop_ga_account_id');
			
			if ( !empty( $order_id ) && is_numeric( $order_id ) && !empty($ga_account_id) ) {
				$order_meta = get_post_meta( $order_id, '_order_postmeta', true);
				$order_info = get_post_meta( $order_id, '_order_info', true);
				
				if ( !empty($order_meta) ) {
					$tracker .= '<script type="text/javascript">';
					$tracker .= 'var _gaq = _gaq || [];';
					$tracker .= '_gaq.push([\'_setAccount\', \'' .$ga_account_id. '\']);';
					$tracker .= '_gaq.push([\'_trackPageview\']);';
					$company_infos = get_option( 'wpshop_company_info' );
					$total_tva = 0;
					if( !empty($order_meta['order_tva']) && is_array($order_meta['order_tva']) ) {
						foreach( $order_meta['order_tva'] as $tva ) {
							$total_tva += $tva;
						}
					}
					
					$tracker .= '_gaq.push([\'_addTrans\',\'' .$order_id. '\', \'' . ( !empty($company_infos) && !empty($company_infos['company_name']) ? $company_infos['company_name'] : ''  ) . '\', \'' .number_format($order_meta['order_grand_total'], 2, '.', ''). '\', \'' .number_format($total_tva, 2, '.', ''). '\', \'' .( ( !empty($order_meta['order_shipping_cost']) ) ? $order_meta['order_shipping_cost'] : 0). '\',';
					
					$tracker .= '\'' . ( ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['city']) ) ? $order_info['billing']['address']['city'] : '') . '\',';
					$tracker .= '\'' . ( ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['state']) ) ? $order_info['billing']['address']['state'] : '') . '\',';
					$tracker .= '\'' . ( ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['country']) ) ? $order_info['billing']['address']['country'] : '') . '\']);';
					/** Order Infos **/
					
					
					/** Order items **/
					if ( !empty( $order_meta['order_items'] ) && is_array( $order_meta['order_items'] ) ) {
						foreach( $order_meta['order_items'] as $item ) {
							/** Variation **/
							$variation = '';
							$variation_definition = get_post_meta( $item['item_id'], '_wpshop_variations_attribute_def', true);
							if ( !empty($variation_definition) && is_array($variation_definition) ) {
								foreach( $variation_definition as $k => $value ) {
									$attribute_def = wpshop_attributes::getElement( $k, '"valid"', 'code' );
									if ( !empty($attribute_def) ) {
										$variation .= $attribute_def->frontend_label.' : ';
										if ( $attribute_def->data_type_to_use == 'custom' ) {
											$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $value );
											$variation .= $wpdb->get_var( $query );
										}
										else {
											$variation .= get_the_title( $value );
										}
									}
								}
							}
							$item_meta = get_post_meta($item['item_id'], '_wpshop_product_metadata', true);
							$tracker .= '_gaq.push([\'_addItem\',\''.$order_id.'\',\'' . ( (!empty($item_meta) && !empty($item_meta['barcode']) ) ? $item_meta['barcode'] : '') . '\', \'' .$item['item_name']. '\', \''.$variation.'\', \''.$item['item_pu_ttc'].'\', \'' .$item['item_qty']. '\']);';
						}
					}
					$tracker .= '_gaq.push([\'_trackTrans\']);';
					$tracker .= '(function() {var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\'; var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);})();';
					$tracker .= '</script>';
				}
			}
			
			return $tracker;
		}
	}
}
if (class_exists("wps_ga_ecommerce_tracker")) {
	$inst_wps_ga_ecommerce_tracker = new wps_ga_ecommerce_tracker ();
}