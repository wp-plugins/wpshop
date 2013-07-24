<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Products management method file
*
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/
class wpshop_shipping {

	/**
	 * Convert fees (string format) to fees in array format
	 * @param $fees_string : fees string type by the user
	 * @return $data : fees in array format
	 */
	function shipping_fees_string_2_array($fees_string) {
		$data = array();
		if(!empty($fees_string) && !is_array($fees_string) ) {
			if(preg_match_all('#{[^{]+}#', $fees_string, $cont)){
				foreach($cont[0] as $c) {
					preg_match_all('#([a-z]+) ?: ?"(.+)"#', $c, $atts);
					$temp_data = array();
					$country_code = '';
					foreach($atts[1] as $key => $value) {
						$temp_data[$value] =  $atts[2][$key];
						if($value=='destination') {
							$country_code = $atts[2][$key];
						}
						elseif($value=='fees') {
							$fees_data = array();
							$fees = explode(',', $atts[2][$key]);
							foreach($fees as $fee){
								$fee_element = explode(':', $fee);
								$fees_data[trim($fee_element[0])] =  trim($fee_element[1]);
							}
							$number = count($fees_data);

							$fees_data_1 = array();
							preg_match_all('#([0-9]+\.?[0-9]?+) ?: ?([0-9]+\.?[0-9]?+)#', $atts[2][$key], $fees);
							foreach($fees[1] as $_key => $_value) {
								$fees_data_1[$_value] =  $fees[2][$_key];
							}
							$number_1 = count($fees_data_1);
							if ($number == $number_1) {
								$temp_data[$value] =  $fees_data;
							}
							else {
								$temp_data[$value] =  $fees_data_1;
							}
						}
					}
					if(!empty($country_code)) {
						$data[$country_code] = $temp_data;
					}
				}
			}
			return $data;
		}
		return array();
	}

	/**
	 * Convert fees (array format) to fees in string format
	 * @param $fees_array : fees in array format
	 * @return $string : fees in string format
	 */
	function shipping_fees_array_2_string($fees_array) {
		$string = '';
		if(!empty($fees_array)) {
			foreach($fees_array as $d) {
				$string .= '{'."\n";
				foreach($d as $att => $value) {
					$val = '';
					if($att=='fees') {
						foreach($value as $_k=>$_value) $val .= $_k.':'.$_value.', ';
						$val = substr($val,0,-2);
					} else $val = $value;
					$string .= $att.': "'.$val.'",'."\n";
				}
				$string = substr($string,0,-2)."\n";
				$string .= '},'."\n";
			}
			$string = substr($string,0,-2);
			return $string;
		}
		else return false;
	}


	/**
	 * Get the shipping cost for the current cart
	 *
	 * @param integer $nb_of_items The number of items in cart
	 * @param float $total_cart The amount of the cart
	 * @param float $total_shipping_cost The amount of the shipping cost calculate from the sum of shipping cost for each product in cart
	 * @param float $total_weight The total weight of all product in cart
	 *
	 * @return number|string The sipping cost for the current cart
	 */
	function get_shipping_cost($nb_of_items, $total_cart, $total_shipping_cost, $total_weight) {
		global $wpdb;
		/** Check if shipping cost ar free **/
		$rules = get_option('wpshop_shipping_rules',array());
		$shipping_cost = $total_shipping_cost;
		if ( !empty($rules) && !empty($rules['wpshop_shipping_rule_free_shipping']) ) {
			$shipping_cost = 0;
		}
		elseif ( !empty($rules) && !empty($rules['free_from_active']) && !empty($rules['free_from']) && $rules['free_from'] >= 0 && number_format((float)$total_cart, 2, '.', '') >= $rules['free_from']) {
			$shipping_cost = 0;
		}
		else {
			$total_weight = !empty($total_weight) ? $total_weight : 0;
			if ($nb_of_items == 0) {
				return 0;
			}
			if ( !empty($_SESSION['wpshop_pos_addon']) ) {
				return number_format(0, 2, '.', '');
			}
			$shipping_cost = false;
			$current_user = wp_get_current_user();
			$country = '';
			$shipping_option = get_option('wpshop_custom_shipping');
			/** Check if the user is logged **/
			if ( $current_user->ID !== 0 ) {
				/** Check if a shipping address is already selected */
				if ( !empty( $_SESSION['shipping_address'] ) ) {
					$address = get_post_meta($_SESSION['shipping_address'],'_wpshop_address_metadata', true);
					$country = ( !empty($address['country']) ) ? $address['country'] : '';
					// Check custom shipping cost with postcode
					if ( !empty($shipping_option) ) {
						if ( !empty($shipping_option['active_cp']) ) {
							$postcode = $address['postcode'];
							if ( array_key_exists($country.'-'.$postcode, $shipping_option['fees']) ) {
								$country = $country.'-'.$postcode;
							}
							elseif( array_key_exists($country.'-OTHERS', $shipping_option['fees']) ) {
								$country = $country.'-OTHERS';
							}
						}
					}
					
				}
				else {
					/** If the shipping address isn't selected */
					$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->posts. ' WHERE post_author = %d AND post_type = %s', $current_user->ID, WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS);
					$addresses = $wpdb->get_results( $query );
					$wpshop_shipping_address_choice = get_option('wpshop_shipping_address_choice');
					$shipping_address_type_id = $wpshop_shipping_address_choice['choice'];
					$user_shipping_address = '';
					$first = false;
					foreach( $addresses as $address ) {
						$address_meta_data_type = get_post_meta($address->ID, '_wpshop_address_attribute_set_id', true);
						if ( !empty($address_meta_data_type) && $address_meta_data_type == $shipping_address_type_id ) {
							$address_meta_data = get_post_meta($address->ID,'_wpshop_address_metadata', true);
							if ( !empty($address_meta_data) && !empty($address_meta_data['country']) ) {
								if ($first == false) {
									$country = $address_meta_data['country'];
									$first = true;
								}
							}
						}
					}
				}
			}
			
			if (!empty($shipping_option) && !empty($shipping_option['active']) && $shipping_option['active'] ) {
				if ( !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) && empty($total_weight) ) {
					foreach ( $_SESSION['cart']['order_items'] as $item ) {
						if ( !empty( $item['item_meta']['attribute_visible_listing']['product_weight'] ) ) {
							$total_weight += ($item['item_meta']['attribute_visible_listing']['product_weight'] * $item['item_qty']);
						}
						elseif (!empty( $item['item_meta']['variation_definition']) ) {
							$parent_product = wpshop_products::get_parent_variation ( $item['item_id'] );
							if ( !empty($parent_product) && !empty($parent_product['parent_post_meta']) ) {
								$total_weight += ($parent_product['parent_post_meta']['product_weight'] * $item['item_qty']);
							}
						}
					}
				}
				
				
				$shipping_cost = wpshop_shipping::calculate_custom_shipping_cost($country, array('weight'=>$total_weight,'price'=> $total_cart), $shipping_option['fees']);
				
				if ( !empty($_SESSION['cart']['order_shipping_cost']) && $shipping_cost != false ) {
					$_SESSION['cart']['order_shipping_cost'] = $shipping_cost;
				}
			}
			/** If custom shipping fees is not active or if no rules has been used, get the basic rules	*/
			if ($shipping_cost === false) {
				$shipping_cost = 0;
				if ( !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) ) {
					foreach( $_SESSION['cart']['order_items'] as $k => $item ) {
						$product = get_post_meta( $k, '_wpshop_product_metadata', true);
						$shipping_cost = $shipping_cost + ( ( !empty($product['cost_of_postage']) ) ? $product['cost_of_postage']: 0 );
					}
					if ( !empty($rules['min_max']) && !empty($rules['min_max']['min']) && $shipping_cost <=  $rules['min_max']['min'] ) {
						$shipping_cost = $rules['min_max']['min'];
					}
					elseif( !empty($rules['min_max']) && !empty($rules['min_max']['max']) && $shipping_cost > $rules['min_max']['max']) {
						$shipping_cost = $rules['min_max']['max'];
					}
				}
			}
		}
		return number_format($shipping_cost, 2, '.', '');
	}
	
	
	function calculate_custom_shipping_cost($dest='', $data, $fees) {
		$fees_table = array();
		$key = '';

		if ( !empty($_SESSION['shipping_partner_id']) ) {
			return 0;
		}

		if(!empty($fees) || !empty($dest) ) {
			$custom_shipping_option = get_option( 'wpshop_custom_shipping', true );
			if ( !empty($custom_shipping_option) && !empty($custom_shipping_option['activate_cp']) ) {
				if ( array_key_exists($dest.'-'.$postcode, $fees) ) {
						$key = $dest.'-'.$postcode;
				}
				elseif( array_key_exists( $dest.'-OTHERS', $fees) ) {
						$key = $dest.'-OTHERS';
				}
				else {
					return false;
				}
			}
			else {
				if ( array_key_exists($dest, $fees) ) {
					$key = $dest;
				}
				elseif( array_key_exists( 'OTHERS', $fees) ) {
					$key = 'OTHERS';
				}
				else {
					return false;
				}
			}

			//Search fees
			if ( !empty($key) ) {
				$price = 0;
				foreach ($fees[$key]['fees'] as $k => $shipping_price) {
					if ( $data['weight'] <= $k) {
						$price = $shipping_price;
						return $price;
					}
				}
				return false;
			}
			else {
				return false;
			}

		}
		return false;
	}
	
}

?>