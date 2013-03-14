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


	function calculate_shipping_cost($dest='', $data, $fees) {
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
				foreach ($fees[$key]['fees'] as $k => $shipping_price) {
					if ( $data['weight'] <= $k) {
						return $shipping_price;
					}
				}
			}
			else {
				return false;
			}

		}


		return false;
	}
}




















