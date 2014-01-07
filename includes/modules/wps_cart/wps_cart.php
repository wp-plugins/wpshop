<?php
/**
Plugin Name: Eoxia - Cart
Description: Manage a cart
Version: 1.0
Author: Eoxia
Author URI: http://eoxia.com/
*/
/**
 * Bootstrap file
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists('wps_cart') ) {
	class wps_cart {
		function __construct() {
			
		}
		
		function add_product_to_cart() {
			$product_id = isset($_POST['wpshop_pdt']) ? intval(wpshop_tools::varSanitizer($_POST['wpshop_pdt'])) : null;
			$product_qty= isset($_POST['wpshop_pdt_qty']) ? intval(wpshop_tools::varSanitizer($_POST['wpshop_pdt_qty'])) : 1;
			$cart_option = get_option('wpshop_cart_option', array() );
			$wpshop_variation_selected = !empty($_POST['wps_pdt_variations']) ? $_POST['wps_pdt_variations'] : array();
			$from_administration =  ( !empty($_POST['from_admin']) ) ? true : false;
			$order_id =  ( !empty($_POST['wps_orders_order_id']) ) ? wpshop_tools::varSanitizer( $_POST['wps_orders_order_id'] ) : null;
			
			if( !empty($product_id) ) {
				$product_data = wpshop_products::get_product_data($product_id);
				/** If the product have many variations **/
				if ( count($wpshop_variation_selected ) > 1 ) {
					if ( !empty($wpshop_variation_selected) ) {
						$product_with_variation = wpshop_products::get_variation_by_priority( $wpshop_variation_selected, $product_id, true );
					}
				
					if ( !empty($product_with_variation[$product_id]['variations']) ) {
						$product = $product_data;
						$has_variation = true;
						$head_product_id = $product_id;
				
						if ( !empty($product_with_variation[$product_id]['variations']) && ( count($product_with_variation[$product_id]['variations']) == 1 ) && ($product_with_variation[$product_id]['variation_priority'] != 'single') ) {
							$product_id = $product_with_variation[$product_id]['variations'][0];
						}
				
						$product = wpshop_products::get_product_data($product_id, true);
				
				
						$the_product = array_merge( array(
								'product_id'	=> $product_id,
								'product_qty' 	=> $product_qty
						), $product);
				
						/*	Add variation to product into cart for storage	*/
						if ( !empty($product_with_variation[$head_product_id]['variations']) ) {
							$the_product = wpshop_products::get_variation_price_behaviour( $the_product, $product_with_variation[$head_product_id]['variations'], $head_product_id, array('type' => $product_with_variation[$head_product_id]['variation_priority']) );
						}
				
						$product_data = $the_product;
					}
				}
				
				
				
				
			}
			die();
		}
		
		function prepare_product_to_add_to_cart( ) {
			
		}
		
		
	}
}
if ( class_exists('wps_cart') ) {
	$wps_cart = new wps_cart();
}