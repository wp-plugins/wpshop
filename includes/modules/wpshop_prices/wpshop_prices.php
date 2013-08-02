<?php
/**
 * Plugin Name: WP-Shop-prices
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Prices
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WPSHOP Prices bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wpshop_prices") ) {
	class wpshop_prices {

		function __construct() {
			add_action('wsphop_options', array('wpshop_prices', 'declare_options'));
		}

		function declare_options () {
			register_setting('wpshop_options', 'wpshop_catalog_product_option', array('wpshop_prices', 'wpshop_options_validate_prices'));
			add_settings_field('wpshop_catalog_product_option_discount', __('Activate the discount on products', 'wpshop'), array('wpshop_prices', 'wpshop_activate_discount_prices_field'), 'wpshop_catalog_product_option', 'wpshop_catalog_product_section');
		}

		function wpshop_options_validate_prices($input) {
			global $wpdb;
			
			/** Price attribute Def **/
			$price_piloting_option = get_option('wpshop_shop_price_piloting');
			$price_attribute_def = wpshop_attributes::getElement( ( (!empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? 'price_ht' : 'product_price' ), "'valid'", 'code');
			
			/** Discount attributes **/
			$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE code = %s OR code = %s OR  code = %s OR code = %s OR code = %s', 'discount_amount', 'discount_rate', 'special_price', 'special_from', 'special_to' );
			$discount_attributes = $wpdb->get_results($query);
			
			/** Check if discount is actived **/
			if ( !empty( $input) && !empty($input['discount']) ) {
				/** Activate the attributes **/
				if ( !empty($discount_attributes) ) {
					foreach ( $discount_attributes as $discount_attribute ) {
						$update = $wpdb->prepare('UPDATE ' .WPSHOP_DBT_ATTRIBUTE. ' SET status = "valid" WHERE code = %s', $discount_attribute->code);
						$wpdb->query($update);
					}
				}
				
				/** Affect discount attributes to All Attributes Set section where there is a Price attribute **/
				$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE attribute_id = %d', $price_attribute_def->id);
				$attributes_sections = $wpdb->get_results( $query );
				if ( !empty($attributes_sections) ) {
					foreach ( $attributes_sections as $attributes_section ) {
						/** Check the Max Position for the Attribute section */
						$query = $wpdb->prepare('SELECT MAX(position) AS max_position FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE attribute_set_id = %d AND attribute_group_id = %d AND entity_type_id = %s', $attributes_section->attribute_set_id, $attributes_section->attribute_group_id, $attributes_section->entity_type_id);
						$max_position = $wpdb->get_var( $query );
						$max_position = ( !empty($max_position) ) ? $max_position : 0;
						/** Affect the discount attributes **/
						foreach ( $discount_attributes as $discount_attribute) {
							$query = $wpdb->prepare(' SELECT COUNT(*) AS count_attributes_affect FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE attribute_set_id = %d AND attribute_group_id = %d AND entity_type_id = %s AND attribute_id = %d', $attributes_section->attribute_set_id, $attributes_section->attribute_group_id, $attributes_section->entity_type_id, $discount_attribute->id);
							$count_attribute_affectation = $wpdb->get_row( $query );
							if ( !empty($count_attribute_affectation) && ( empty($count_attribute_affectation->count_attributes_affect) || $count_attribute_affectation->count_attributes_affect == 0 ) ) {
								$result = $wpdb->insert( WPSHOP_DBT_ATTRIBUTE_DETAILS, array( 'status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $attributes_section->entity_type_id, 'attribute_set_id' => $attributes_section->attribute_set_id, 'attribute_group_id' => $attributes_section->attribute_group_id, 'attribute_id' => $discount_attribute->id, 'position' => $max_position) );
								$max_position += 1;
							}
						}
					}
				}
			}
			else {
				/** Desactivate Discount Attributes **/
				if ( !empty($discount_attributes) ) {
					foreach ( $discount_attributes as $discount_attribute ) {
						$update = $wpdb->prepare('UPDATE ' .WPSHOP_DBT_ATTRIBUTE. ' SET status = "notused" WHERE code = %s', $discount_attribute->code);
						$wpdb->query($update);
					}
				}
				
				/** Delete the Price attribute set section affectation **/
				$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE attribute_id = %d', $price_attribute_def->id);
				$attributes_sections = $wpdb->get_results( $query );
				if ( !empty($attributes_sections) ) {
					foreach ( $attributes_sections as $attributes_section ) {
						/** Affect the discount attributes **/
						foreach ( $discount_attributes as $discount_attribute) {
							$wpdb->delete( WPSHOP_DBT_ATTRIBUTE_DETAILS, array( 'entity_type_id' => $attributes_section->entity_type_id, 'attribute_set_id' => $attributes_section->attribute_set_id, 'attribute_group_id' => $attributes_section->attribute_group_id, 'attribute_id' => $discount_attribute->id ) );
						}
					}
				}
			}
			
			return $input;
		}

		function wpshop_activate_discount_prices_field() {
			$product_discount_option = get_option('wpshop_catalog_product_option');

			$output  = '<input type="checkbox" id="wpshop_catalog_product_option_discount" name="wpshop_catalog_product_option[discount]" ' .( (!empty($product_discount_option) && !empty($product_discount_option['discount'])) ? 'checked="checked"' : '' ). ' />';
			$output .= '<a class="wpshop_infobulle_marker" title="' .__('Activate the possibility to create discount on products', 'wpshop'). '" href="#">?</a>';
			echo $output;
		}

		/**
		 * Check the product price, return price
		 * @param unknown_type $product
		 */
		function check_product_price ( $product ) {
			global $wpdb;
			$prices = array();
			$fork_price = false;
			$price_ati = $price_et = $tva = $min_price = $max_price = $tva_id = 0;
			if ( !empty($product) ) {
				$product_meta = get_post_meta($product['product_id'], '_wpshop_variations_attribute_def', true);
				if ( !empty($product_meta) ) {
					/** If it's a product with variations **/
					$parent_product = wpshop_products::get_parent_variation( $product['product_id']);
					if ( !empty($parent_product) && !empty($parent_product['parent_post']) && !empty($parent_product['parent_post_meta']) ) {
						//parent informations
						$parent_post = $parent_product['parent_post'];
						$parent_post_meta = $parent_product['parent_post_meta'];
						// Check the options for the price of a variation
						$variation_post_meta = get_post_meta($product['product_id'], '_wpshop_product_metadata', true);
						$variation_options = get_post_meta($parent_post->ID, '_wpshop_variation_defining', true);
						if ( !empty($variation_options) && !empty($variation_options['options']) && !empty($variation_options['options']['price_behaviour']) ) {
							if ( $variation_options['options']['price_behaviour'][0] == 'addition') {


								$price_ati = str_replace(',', '.',$parent_post_meta['product_price']) + str_replace(',', '.',$variation_post_meta['product_price']);
								$price_et = str_replace(',', '.',$parent_post_meta['price_ht']) + str_replace(',', '.',$variation_post_meta['price_ht']);
								$tva_id = $parent_post_meta['tx_tva'];
							}
							else {
								$price_ati = $variation_post_meta['product_price'];
								$price_et = $variation_post_meta['price_ht'];
								$tva_id = $parent_post_meta['tx_tva'];

							}
						}
						/** If it's a product with variation but variation parameters are not checked **/
						elseif (  !empty($variation_options) && empty($variation_options['options']) && empty($variation_options['options']['price_behaviour']) )  {
							if ( empty($variation_post_meta['product_price']) && empty($variation_post_meta['price_ht']) ) {
								$price_ati = str_replace(',', '.',$parent_post_meta['product_price']);
								$price_et = str_replace(',', '.',$parent_post_meta['price_ht']);
								$tva_id = str_replace(',', '.',$parent_post_meta['tx_tva']);
							}
							else {
								$price_ati = str_replace(',', '.',$variation_post_meta['product_price']);
								$price_et = str_replace(',', '.',$variation_post_meta['price_ht']);
								$tva_id = str_replace(',', '.',$parent_post_meta['tx_tva']);
							}
						}
						/** If it's a simple product **/
						else {
							$price_ati = str_replace(',', '.',$variation_post_meta['product_price']);
							$price_et = str_replace(',', '.',$variation_post_meta['price_ht']);
							$tva_id = str_replace(',', '.',$parent_post_meta['tx_tva']);
						}
					}
				}
				else {
					/** Check the min price and the max price for a product **/
					
					$product_variations_meta = get_post_meta( $product['product_id'], '_wpshop_variation_defining', true);
					if ( !empty($product_variations_meta) ) {
						$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_parent = %d', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, $product['product_id']);
						$product_variations = $wpdb->get_results($query);

						
						if ( !empty($product_variations) ) {
							$price_piloting_option = get_option('wpshop_shop_price_piloting');
							$parent_product_price = ( !empty($price_piloting_option) &&  $price_piloting_option == 'HT' && !empty( $product['price_ht'] ) ) ? $product['price_ht'] : $product['product_price'];
							foreach ( $product_variations as $product_variation ) {
								$product_variation_metadata = get_post_meta( $product_variation->ID, '_wpshop_product_metadata', true);
								if ( !empty($product_variation_metadata) && !empty($product_variations_meta['options']) && !empty($product_variations_meta['options']['price_behaviour']) ) {

										if ( $product_variations_meta['options']['price_behaviour'][0] == 'addition' ) {
											$product_price = ( (!empty($price_piloting_option) &&  $price_piloting_option == 'HT') ? $product_variation_metadata['price_ht'] : $product_variation_metadata['product_price']) + $parent_product_price;
										}
										else {
											$product_price = (!empty($price_piloting_option) &&  !empty($product_variation_metadata['price_ht']) && $price_piloting_option == 'HT') ? $product_variation_metadata['price_ht'] : ( (!empty($product_variation_metadata['product_price']) ) ? $product_variation_metadata['product_price'] : null) ;
										}
								}
								else {
									if ( $product_variation_metadata > 0 ) {
										if ( !empty($product_variation_metadata['product_price']) && $product_variation_metadata['product_price'] == 0) {
											$product_postmeta = get_post_meta($product['product_id'], '_wpshop_product_metadata', true);
											$product_price = ( !empty($price_piloting_option) &&  $price_piloting_option == 'HT')  ? str_replace(',', '.', $product_postmeta['price_ht']) : str_replace(',', '.', $product_postmeta['product_price']);
										}
										else {
											$product_price = !empty( $product_variation_metadata['product_price'] ) ? $product_variation_metadata['product_price'] : 0;
										}
									}
									else {
										$product_price = $parent_product_price;
									}
								}
								if ( $product_price > $max_price ) {
									$max_price = $product_price;
								}
								if ( $product_price < $min_price || $min_price == 0 ) {
									$min_price = $product_price;
								}
							}
							$fork_price = true;
						}
					}

					// It's a product without variations
					$price_ati = !empty($product[WPSHOP_PRODUCT_PRICE_TTC]) ? $product[WPSHOP_PRODUCT_PRICE_TTC] : 0;
					$price_et = !empty($product[WPSHOP_PRODUCT_PRICE_HT]) ? $product[WPSHOP_PRODUCT_PRICE_HT] : 0;
					$product_metadata = get_post_meta($product['product_id'], WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
					$attribute_tva = wpshop_attributes::getElement(WPSHOP_PRODUCT_PRICE_TAX, "'valid'", 'code');
					$tva_id = !empty($product_metadata[WPSHOP_PRODUCT_PRICE_TAX]) ? $product_metadata[WPSHOP_PRODUCT_PRICE_TAX] : $attribute_tva->default_value;
				}
			}
			$query = $wpdb->prepare('SELECT value FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $tva_id);
			$tva_rate = $wpdb->get_var($query);

			if ( $price_ati == 0 &&  $price_et != 0) {
				$price_ati = $price_et * ( 1+($tva_rate/100) );
			}
			elseif ( $price_et == 0 && $price_ati != 0) {
				$price_et = $price_ati * ( 1-($tva_rate/100) );
			}

			$tva = $price_ati - $price_et;

			/** Discount Part */
			$calcul_discount = ( !empty($product['product_id']) ) ? wpshop_prices::get_discount_amount($product) : array();
			$discount_exist = false;
			$discount_ati_price = $discount_et_price = $discount_tva = 0;

			if ( !empty($calcul_discount) && !empty($calcul_discount[0]) && $calcul_discount[0]) {
				$discount_exist = $calcul_discount[0];
				$discount_et_price = $calcul_discount[1];
				$discount_ati_price = $discount_et_price * ( 1 + ($tva_rate/100) );
				$discount_tva = $discount_ati_price - $discount_et_price;
			}

			$prices = array('ati' => number_format((float)$price_ati, 5, '.', ''), 'et' => number_format((float)$price_et, 5, '.', ''), 'tva' => $tva, 'discount' => array( 'discount_exist' => $discount_exist,'discount_ati_price' => $discount_ati_price, 'discount_et_price' => $discount_et_price, 'discount_tva' => $discount_tva), 'fork_price' => array('have_fork_price' => $fork_price, 'min_product_price' => $min_price, 'max_product_price' => $max_price) );
			
			return $prices;
		}

		/**
		 * Allows to get the correct price for a product
		 *
		 * @param object $product An object with the product definition
		 * @param string $return_type The type the price have to be returned under
		 * @param string $output_type The current output type (mini | complete)
		 *
		 * @return boolean|string Boolean: If the product price is set for cart adding | String: An error message if the price is not well set OR The product price
		 */
		function get_product_price($product, $return_type, $output_type = '', $only_price = false) {
			global  $wpdb;
			$productCurrency = wpshop_tools::wpshop_get_currency();
			$wpshop_price_piloting_option = get_option('wpshop_shop_price_piloting');
			$tpl_component = array();
			$tpl_component['CROSSED_OUT_PRICE'] = '';
			$tpl_component['TAX_PILOTING'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT')  ? __('ET', 'wpshop') : '';

			if ( $return_type == 'check_only' ) {
				/** Check if the product price has been set	*/
				if(isset($product[WPSHOP_PRODUCT_PRICE_TTC]) && $product[WPSHOP_PRODUCT_PRICE_TTC] === '') return __('This product cannot be purchased - the price is not yet announced', 'wpshop');
				/** Check if the product price is coherent (not less than 0)	*/
				if(isset($product[WPSHOP_PRODUCT_PRICE_TTC]) && $product[WPSHOP_PRODUCT_PRICE_TTC] < 0) return __('This product cannot be purchased - its price is negative', 'wpshop');

				return true;
			}
			else if ( $return_type == 'price_display' ) {
				$price_infos = wpshop_prices::check_product_price($product);
				$the_price = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $price_infos['et'] : $price_infos['ati'];

				$discount_exist = false;
		        if ( !empty($price_infos['discount']) && !empty($price_infos['discount']['discount_exist']) && $price_infos['discount']['discount_exist'] ) {
		        	$the_price = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $price_infos['discount']['discount_et_price'] : $price_infos['discount']['discount_ati_price'];
		        	$discount_exist = true;
		        }
				$display_type = $output_type;

				/** Add a class decimal numbers on price display **/
				$exploded_price = explode('.', number_format($the_price,2, '.', ''));
				$the_price = $exploded_price[0].'.<span class="wpshop_price_centimes_display">'.( (!empty($exploded_price[1]) ) ? $exploded_price[1] : '').'</span>';

				if ( !empty($output_type) && is_array($output_type) ) {
					$display_type = $output_type[0];
					$display_sub_type = $output_type[1];
				}

				/** Get the definition for attribute price: allows to define if the price have to displayed or not	*/
				$price_attribute = wpshop_attributes::getElement(WPSHOP_PRODUCT_PRICE_TTC, "'valid'", 'code');

				/** Check price configuration for output	*/
				$price_display = wpshop_attributes::check_attribute_display( (($display_type == 'mini_output' ) ? $price_attribute->is_visible_in_front_listing : $price_attribute->is_visible_in_front), $product['custom_display'], 'attribute', WPSHOP_PRODUCT_PRICE_TTC, $display_type);


				/** Check the current output type and the price attribute configuration for knowing the output to take	*/
				if ( !$price_display ) {
					$price_display = '';
				}
				else {
					$price = !empty( $the_price ) ? wpshop_display::format_field_output('wpshop_product_price', $the_price) . ' ' . $productCurrency : __('Unknown price','wpshop');
					if ( $discount_exist ) {
						$crossed_out_price = ( (!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? number_format($price_infos['et'], 2) : number_format($price_infos['ati'], 2) ).' '. $productCurrency;
						$tpl_component['CROSSED_OUT_PRICE'] = wpshop_display::display_template_element('product_price_template_crossed_out_price', array('CROSSED_OUT_PRICE_VALUE' => $crossed_out_price));
					}
					$template_part = 'product_price_template_' . $display_type;
					$tpl_component['PRODUCT_PRICE'] = $price;
					$tpl_component['MESSAGE_SAVE_MONEY'] = wpshop_marketing_messages::display_message_you_save_money($product);
					$tpl_component['PRODUCT_ORIGINAL_PRICE'] = ($price != __('Unknown price','wpshop')) ? $price : '';
					$tpl_component['TAX_PILOTING'] = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT')  ? __('ET', 'wpshop') : '';

					/**	Check if there are variaton for current product	*/
					$current_product_variation = ( !empty($product['product_id']) ) ? wpshop_products::get_variation( $product['product_id'] ) : null;
					if ( !empty($current_product_variation) ) {
						$head_wpshop_variation_definition = get_post_meta( $product['product_id'], '_wpshop_variation_defining', true );
						$product_post_meta = get_post_meta( $product['product_id'], '_wpshop_product_metadata', true );
						/** Check if the price to display must be the lowest price of variation */
						$catalog_product_option = get_option('wpshop_catalog_product_option');
						if ( ( !empty($catalog_product_option) && !empty($catalog_product_option['price_display']) && !empty($catalog_product_option['price_display']['lower_price']) ) || (!empty($head_wpshop_variation_definition['options']['price_display']) && !empty($head_wpshop_variation_definition['options']['price_display']['lower_price']) && ($head_wpshop_variation_definition['options']['price_display']['lower_price'] == 'on')) ) {
							
							$lower_price = $discount_lower_price = 0;
							$price_index = constant('WPSHOP_PRODUCT_PRICE_' . WPSHOP_PRODUCT_PRICE_PILOT);
							foreach ($current_product_variation as $variation_id => $variation_definition) {
								// Get product price for option

								$discount_exist = false;
								$variation_product_price_infos  = wpshop_prices::check_product_price( wpshop_products::get_product_data($variation_id) );

								if ( !empty($variation_product_price_infos) ) {
									/** Check iof there is a discount **/
									if ( !empty($variation_product_price_infos) && !empty($variation_product_price_infos['discount']) && !empty($variation_product_price_infos['discount']['discount_exist']) ) {
										if ( !empty($price_infos) && !empty($price_infos['discount']) && !empty($price_infos['discount']['discount_exist']) ) {

											if ( !empty($price_infos['discount']['discount_ati_price']) && !empty($variation_product_price_infos['discount']['discount_ati_price']) && $variation_product_price_infos['discount']['discount_ati_price'] < $price_infos['discount']['discount_ati_price'] ) {
												$price_infos['discount'] = $variation_product_price_infos['discount'];
												$lower_price = (!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $variation_product_price_infos['et'] : $variation_product_price_infos['ati'];
												$tpl_component['MESSAGE_SAVE_MONEY'] = wpshop_marketing_messages::display_message_you_save_money( wpshop_products::get_product_data($variation_id) );
												$crossed_out_price = ( (!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? number_format($variation_product_price_infos['et'], 2) : number_format($variation_product_price_infos['ati'], 2) ).' '. $productCurrency;
												$tpl_component['CROSSED_OUT_PRICE'] = wpshop_display::display_template_element('product_price_template_crossed_out_price', array('CROSSED_OUT_PRICE_VALUE' => $crossed_out_price));
												$discount_exist = true;
											}
										}
										else {
											$price_infos['discount'] = $variation_product_price_infos['discount'];
											$lower_price = (!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $variation_product_price_infos['discount']['discount_et_price'] : $variation_product_price_infos['discount']['discount_ati_price'];
											$price = (!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $variation_product_price_infos['discount']['discount_et_price'] : $variation_product_price_infos['discount']['discount_ati_price'];
											$discount_exist = true;
											$tpl_component['MESSAGE_SAVE_MONEY'] = wpshop_marketing_messages::display_message_you_save_money( wpshop_products::get_product_data($variation_id) );
											$crossed_out_price = ( (!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? number_format($variation_product_price_infos['et'], 2) : number_format($variation_product_price_infos['ati'], 2) ).' '. $productCurrency;
											$tpl_component['CROSSED_OUT_PRICE'] = wpshop_display::display_template_element('product_price_template_crossed_out_price', array('CROSSED_OUT_PRICE_VALUE' => $crossed_out_price));
										}
									}
									elseif( !$discount_exist || ($discount_exist && $price_infos['discount']['discount_ati_price'] > $variation_product_price_infos['ati']) ) {

										if ( ( !empty($price_infos['ati']) && !empty($variation_product_price_infos['ati']) && $price_infos['ati'] > $variation_product_price_infos['ati'] )  || ( !empty($price_infos['ati']) && !empty($price_infos['ati']) && $price_infos['et'] > $variation_product_price_infos['et'] )  ) {
											$lower_price = (!empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $variation_product_price_infos['et'] : $variation_product_price_infos['ati'];
											if ( $lower_price == 0 && !empty( $variation_product_price_infos ) && !empty($variation_product_price_infos) && $variation_product_price_infos['fork_price'] && $variation_product_price_infos['fork_price']['min_product_price'] ) {
												$lower_price =  $variation_product_price_infos['fork_price']['min_product_price'];
											}
											$price = $lower_price;
											$discount_exist = false;
											$price_infos['et'] = $variation_product_price_infos['et'];
											$price_infos['ati'] = $variation_product_price_infos['ati'];
										}
										else {
											if ( empty($lower_price) && !empty($price_infos['fork_price']) && !empty($price_infos['fork_price']['min_product_price']) ) {
												$lower_price = $price_infos['fork_price']['min_product_price'];
											}
										}

									}
								}
							
								if ( !empty($variation_definition['variation_dif']) ) {
									foreach ($variation_definition['variation_dif'] as $attribute_code => $attribute_value_for_variation) {
										$attribute = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
										if ( !empty($attribute_value_for_variation) && wpshop_attributes::check_attribute_display( (($display_type == 'mini_output' ) ? $attribute->is_visible_in_front_listing : $attribute->is_visible_in_front), $product['custom_display'], 'attribute', $attribute_code, $display_type) ) {
											$tpl_component['PRODUCT_PRICES_' . strtoupper($attribute_code)] = wpshop_display::format_field_output('wpshop_product_price', $attribute_value_for_variation) . ' ' . $productCurrency;
										}
										else {
											$tpl_component['PRODUCT_PRICES_' . strtoupper($attribute_code)] = '';
										}
									}
								}
							}

							/** Add a class decimal numbers on price display **/
							$exploded_price = explode('.', number_format($lower_price,2, '.', ''));
							$lower_price = $exploded_price[0].'.<span class="wpshop_price_centimes_display">'.( (!empty($exploded_price[1]) ) ? $exploded_price[1] : '').'</span>';

							$tpl_component['PRODUCT_PRICE'] = ( $lower_price > 0 ) ? wpshop_display::format_field_output('wpshop_product_price', $lower_price) . ' ' . $productCurrency : $price;
							if ( $lower_price > 0 && $discount_exist ) {
								$tpl_component['CROSSED_OUT_PRICE'] = wpshop_display::display_template_element('product_price_template_crossed_out_price', array('CROSSED_OUT_PRICE_VALUE' => $tpl_component['PRODUCT_PRICE']));
								$lower_price = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $lower_price : ( $lower_price / (1 + ($product[WPSHOP_PRODUCT_PRICE_TAX]/100) ) );
								$discount_price = wpshop_prices::get_discount_amount($product);
								$discount_price_to_display = ( ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $price_infos['discount']['discount_et_price'] : $price_infos['discount']['discount_ati_price'] /*($price_infos[1] * (1 + ($product[WPSHOP_PRODUCT_PRICE_TAX]/100) ))*/ );

								/** Add a class decimal numbers on price display **/
								$exploded_price = explode('.', number_format($discount_price_to_display,2, '.', ''));
								$discount_price_to_display = $exploded_price[0].'.<span class="wpshop_price_centimes_display">'.( (!empty($exploded_price[1]) ) ? $exploded_price[1] : '').'</span>';

								$tpl_component['PRODUCT_PRICE'] = wpshop_display::format_field_output('wpshop_product_price',  $discount_price_to_display). ' ' . $productCurrency;
							}

						}

						/**	Check if the text "PRICE FROM" must be displayed before price	*/
						if ( (!empty($catalog_product_option) && !empty($catalog_product_option['price_display']) && !empty($catalog_product_option['price_display']['text_from'])) || ( !empty($head_wpshop_variation_definition['options']['price_display']) && !empty($head_wpshop_variation_definition['options']['price_display']['text_from']) && ($head_wpshop_variation_definition['options']['price_display']['text_from'] == 'on') ) ) {
							/** Check if it's a multi-option product **/
							if ( !empty($product['item_meta']) && !empty($product['item_meta']['variations']) ) {
								/** Check if all required are selected **/
								$variations_option = get_post_meta($product['product_id'], '_wpshop_variation_defining', true);
								$required_attributes = array();
								$all_required_attributes_are_selected = true;
								/** Check all required attributes and stock their ID in an array **/
								if ( !empty($variations_option) && !empty($variations_option['attributes']) ) {
									foreach( $variations_option['attributes'] as $attribute_code ) {
										$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE code = %s', $attribute_code);
										$attribute_datas = $wpdb->get_row( $query );
										if ( ( !empty( $attribute_datas) && !empty($attribute_datas->is_required) && $attribute_datas->is_required == 'yes') || (!empty($variations_option['options']) && !empty($variations_option['options']['required_attributes']) && in_array($attribute_code, $variations_option['options']['required_attributes'])) ) {
											$required_attributes[] = $attribute_datas->code;
										}
									}

									$sent_attribute = array();
									foreach ( $product['item_meta']['variations'] as $product_variation) {
										foreach ( $product_variation['item_meta']['variation_definition'] as $k => $product_data ) {
											$sent_attribute[$k] = $product_data['VALUE'];
										}
									}
									if ( !empty($required_attributes) && !empty($sent_attribute) ) {
										foreach( $required_attributes as $required_attribute ) {
											if ( !array_key_exists($required_attribute, $sent_attribute) ) {
												$all_required_attributes_are_selected = false;
											}
										}
									}

								}
								if( $all_required_attributes_are_selected ) {
									$tpl_component['PRODUCT_PRICE'] = (( $discount_exist && !empty($crossed_out_price) ) ? $tpl_component['CROSSED_OUT_PRICE'] : ''). ' ' . $tpl_component['PRODUCT_PRICE'];
									$tpl_component['CROSSED_OUT_PRICE'] = '';
								}
								else {
									
									$tpl_component['PRODUCT_PRICE'] =  __('Price from', 'wpshop') . ' ' .(( $discount_exist && !empty($crossed_out_price) ) ? $tpl_component['CROSSED_OUT_PRICE'] : ''). ' ' . $tpl_component['PRODUCT_PRICE'];
									$tpl_component['CROSSED_OUT_PRICE'] = '';
								}
							}
							else {
								$tpl_component['PRODUCT_PRICE'] =  __('Price from', 'wpshop') . ' ' .(( $discount_exist && !empty($crossed_out_price) ) ? $tpl_component['CROSSED_OUT_PRICE'] : ''). ' ' . $tpl_component['PRODUCT_PRICE'];
								$tpl_component['CROSSED_OUT_PRICE'] = '';
							}
						}
					}

					/** For each attribute in price set section: create an element for display	*/
					$atribute_list = wpshop_attributes::get_attribute_list_in_same_set_section( WPSHOP_PRODUCT_PRICE_TTC );
					if ( !empty($atribute_list) && is_array($atribute_list) ) {
						foreach ( $atribute_list as $attribute) {
							if ( !empty($product[$attribute->code]) && wpshop_attributes::check_attribute_display( (($display_type == 'mini_output' ) ? $attribute->is_visible_in_front_listing : $attribute->is_visible_in_front), $product['custom_display'], 'attribute', $attribute->code, $display_type) ) {
								$tpl_component['PRODUCT_PRICES_' . strtoupper($attribute->code)] = wpshop_display::format_field_output('wpshop_product_price', $product[$attribute->code]) . ' ' . $productCurrency;
							}
							else {
								$tpl_component['PRODUCT_PRICES_' . strtoupper($attribute->code)] = '';
							}
						}
					}

					/** Template parameters	*/
					$price_display = wpshop_display::display_template_element($template_part, $tpl_component);
					unset($tpl_component);

					$wpshop_price_display_behaviour = get_option('wpshop_catalog_main_option');
					if ( !empty($wpshop_price_display_behaviour) && !empty($wpshop_price_display_behaviour['wpshop_catalog_empty_price_behaviour']) && ($wpshop_price_display_behaviour['wpshop_catalog_empty_price_behaviour'] == 'yes') ) {
						$price_must_be_hide = false;
						if ( isset($lower_price) && ($lower_price <= 0) ) {
							$price_must_be_hide = true;
						}
						elseif ( empty($the_price) || ($the_price <= 0) ) {
							$price_must_be_hide = true;
						}

						if ($price_must_be_hide) {
						}
					}

					/** Build template	*/
					if ( $only_price ) {
						$price_display = $price;
					}
					else {
						$tpl_to_check = ($display_type == 'complete_sheet') ? 'product_complete_tpl' : 'product_mini_' . $display_sub_type;
						$tpl_way_to_take = wpshop_display::check_way_for_template($tpl_to_check);
						if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
							$price_display = $price;
						}
						else if ( is_file(get_stylesheet_directory() . '/wpshop/wpshop_elements_template.tpl.php') ) {
							$file_path = get_stylesheet_directory() . '/wpshop/wpshop_elements_template.tpl.php';

							require($file_path);
							if ( !empty($tpl_element) && !empty($tpl_element[$tpl_to_check]) ) {
								$price_display = $price;
							}
						}
					}
				}

				return str_replace('.', ',', $price_display);
			}
			return false;
		}

		function get_discount_amount ( $product ) {
			global $wpdb;
			$exist_discount = false;
			$product_price_et = $product_discount_date_from = $product_discount_date_to = $total_discount_rate = $total_discount_amount = 0;
			$discount_infos = array();
			
			$time_def = array('0000-00-00 00:00:00', '0000-00-00');
			
			$wpshop_price_piloting_option = get_option('wpshop_shop_price_piloting');
			$discount_options = get_option('wpshop_catalog_product_option');
			
			/** Dates */
			$product_discount_date_from = ( !empty($product['special_from']) ) ? $product['special_from'] : 0;
			$product_discount_date_to = ( !empty($product['special_to']) ) ? $product['special_to'] : 0;
			$current_date = date('Y-m-d');
			
			
			
			
			
			/** Check if the product is a variation (Single variation product choice ) **/
			if ( !empty($discount_options) && !empty($discount_options['discount']) && !empty($product) ) {
				$variation_def = get_post_meta( $product['product_id'], '_wpshop_variations_attribute_def', true );
				if ( !empty($variation_def) ) {
					$parent_product_infos = wpshop_products::get_parent_variation( $product['product_id'] );
					$parent_post_meta = $parent_product_infos['parent_post_meta'];
					$product_discount_date_from = ( !empty($parent_post_meta['special_from']) ) ? $parent_post_meta['special_from'] : 0;
					$product_discount_date_to = ( !empty($parent_post_meta['special_to']) ) ? $parent_post_meta['special_to'] : 0;
					
					
					if ( ( empty($product_discount_date_from) && empty($product_discount_date_to) ) || ( in_array($product_discount_date_from, $time_def)  && in_array( $product_discount_date_to, $time_def) ) || (strtotime($product_discount_date_from) < strtotime($current_date) && strtotime($current_date) < strtotime($product_discount_date_to) ) ) {
						
						$to_do = 'replacement';
						$variation_options_def = get_post_meta( $product['product_id'], '_wpshop_variation_defining', true);
						if ( !empty($variation_options_def) && !empty($variation_options_def['options']) && !empty($variation_options_def['options']['price_behaviour']) && $variation_options_def['options']['price_behaviour'] == 'addition' ) {
							$to_do = 'addition';
						}
						
						$product_price_et = ( $to_do == 'addition') ? $parent_post_meta['price_ht'] : 0; 
						
						
						
						
						if ( !empty($product['special_price']) && (float)$product['special_price'] > 0 ) {
							$product_price_et = ( $to_do == 'addition') ? $parent_post_meta['special_price'] : 0;
							$query = $wpdb->prepare('SELECT value FROM '. WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.' WHERE id = %s', $parent_post_meta['tx_tva']);
							$tx_tva = $wpdb->get_var( $query );
							$product_price_et += ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $product['special_price'] : ( $product['special_price'] / ( 1 + ($tx_tva / 100) ) );

							$exist_discount = true;
							$discount_infos[] = array('discount_type' => 'special_price', 'amount' => $product_price_et);
						}
						else {
							/** Check others Discount **/
							if ( !empty($variation['discount_amount']) ) {
								$product_price_et += $product['price_ht'] - $product['discount_amount'];
								$exist_discount = true;
								$discount_infos[] = array('discount_type' => 'discount_amount', 'amount' => $product['discount_amount'] );
							}
							if ( !empty($product['discount_rate']) ) {
								$product_price_et += $product['price_ht'] / (1 + ($product['discount_rate'] / 100) );
								$exist_discount = true;
								$discount_infos[] = array('discount_type' => 'discount_rate', 'amount' => $product['discount_rate']);
							}
						}

						/** If variation discount is not configure, we check the parent config **/
						if ( !$exist_discount ){
							if ( !empty($parent_post_meta['special_price']) ) {
								$query = $wpdb->prepare('SELECT value FROM '. WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.' WHERE id = %s', $parent_post_meta['tx_tva']);
								$tx_tva = $wpdb->get_var( $query );
								$product_price_et = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $parent_post_meta['special_price'] : ( $parent_post_meta['special_price'] / ( 1 + ($tx_tva/100) ) );
								$exist_discount = true;
								$discount_infos[0] = array('discount_type' => 'special_price', 'amount' => $product_price_et);
							}
							else {
								/** Check others Discount **/
								if ( !empty($parent_post_meta['discount_amount']) ) {
									$product_price_et += $parent_post_meta['price_ht'] - $parent_post_meta['discount_amount'];
									$exist_discount = true;
									$discount_infos[] = array('discount_type' => 'discount_amount', 'amount' => $parent_post_meta['discount_amount'] );
								}
								if ( !empty($product['discount_rate']) ) {
									$product_price_et += $parent_post_meta['price_ht'] / (1 + ($parent_post_meta['discount_rate'] / 100) );
									$exist_discount = true;
									$discount_infos[] = array('discount_type' => 'discount_rate', 'amount' => $parent_post_meta['discount_rate']);
								}
							}
						}
						
					}
				}
				
				
				
				/** Multi-options Product **/
				elseif( !empty( $product['item_meta']['variations']) ) {
					if ( ( empty($product_discount_date_from) && empty($product_discount_date_to) ) || ( in_array($product_discount_date_from, $time_def)  && in_array($product_discount_date_to, $time_def) ) || (strtotime($product_discount_date_from) < strtotime($current_date) && strtotime($current_date) < strtotime($product_discount_date_to) ) ) {
						$to_do = 'replacement';
						$variation_options_def = get_post_meta( $product['product_id'], '_wpshop_variation_defining', true);
						if ( !empty($variation_options_def) && !empty($variation_options_def['options']) && !empty($variation_options_def['options']['price_behaviour']) && in_array('addition', $variation_options_def['options']['price_behaviour']) ) {
							$to_do = 'addition';
						}
						/** Parent price if "Addition Option" **/
						if ( $to_do == 'addition' ) {
							if ( !empty($product['special_price']) ) {
								$product_price_et = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $product['special_price'] : ( $product['special_price'] / ( 1 + ($product['tx_tva']/100) ) );
							}
							else {
								if ( !empty($product['discount_amount']) ) {
									$product_price_et = $product['price_ht'] - $product['discount_amount'];
								}
								if ( !empty($product['discount_rate']) ) {
									$product_price_et = $product['price_ht'] / (1 + ($product['discount_rate'] / 100) );
								}
							}
						}
						else {
							$product_price_et = 0;
						}
						
						foreach ( $product['item_meta']['variations'] as $variation_id => $variation ) {
							if ( !empty($variation['special_price']) && (float)$variation['special_price'] > 0 ) {
								$product_price_et += ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $variation['special_price'] : ( $variation['special_price'] / ( 1 + ($product['tx_tva']/100) ) );
								$exist_discount = true;
								$discount_infos[0] = array('discount_type' => 'special_price', 'amount' => $product_price_et);
							}
							else {
								/** Check others Discount **/
								if ( !empty($variation['discount_amount']) && (float)$variation['discount_amount'] > 0 ) {
									$product_price_et += $variation['price_ht'] - $variation['discount_amount'];
									$exist_discount = true;
									$total_discount_amount += $variation['discount_amount'];
									$discount_infos[] = array('discount_type' => 'discount_amount', 'amount' => $total_discount_amount );
								}
								if ( !empty($product['discount_rate']) && (float)$variation['discount_amount'] > 0 ) {
									$product_price_et += $variation['price_ht'] / (1 + ($variation['discount_rate'] / 100) );
									$exist_discount = true;
									$total_discount_rate += $variation['discount_rate'];
									$discount_infos[] = array('discount_type' => 'discount_rate', 'amount' => $total_discount_rate);
								}
							}
						}

						/** If variation discount is not configure, we check the parent config **/
						if ( !$exist_discount ){
							if ( !empty($product['special_price']) && (float)$product['special_price'] > 0 ) {
								
								$product_price_et = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $product['special_price'] : ( $product['special_price'] / ( 1 + ($product['tx_tva']/100) ) );
								$exist_discount = true;
								$discount_infos[] = array('discount_type' => 'special_price', 'amount' => $product_price_et);
							}
							else {
								/** Check others Discount **/
								if ( !empty($product['discount_amount']) && (float)$product['discount_amount'] > 0 ) {
									$product_price_et += $product['price_ht'] - $product['discount_amount'];
									$exist_discount = true;
									$discount_infos[] = array('discount_type' => 'discount_amount', 'amount' => $product['discount_amount'] );
								}
								if ( !empty($product['discount_rate']) && (float)$product['discount_rate'] > 0  ) {
									$product_price_et += $product['price_ht'] / (1 + ($product['discount_rate'] / 100) );
									$exist_discount = true;
									$discount_infos[] = array('discount_type' => 'discount_rate', 'amount' => $product['discount_rate']);
								}
							}
						}
						
					}
				}
				
				
				
				/** Product without variations **/
				else {
					if ( ( empty($product_discount_date_from) && empty($product_discount_date_to) ) || ( in_array($product_discount_date_from, $time_def) && in_array($product_discount_date_to, $time_def) ) || (strtotime($product_discount_date_from) < strtotime($current_date) && strtotime($current_date) < strtotime($product_discount_date_to) ) ) {
						/** Special Price **/
						if ( !empty($product['special_price']) && (float)$product['special_price'] > 0 ) {
							$product_price_et = ( !empty($wpshop_price_piloting_option) && $wpshop_price_piloting_option == 'HT') ? $product['special_price'] : ( $product['special_price'] / ( 1 + ($product['tx_tva']/100) ) );
							$exist_discount = true;
							$discount_infos[] = array('discount_type' => 'special_price', 'amount' => $product_price_et);
						}
						else {
							/** Check others Discount **/
							if ( !empty($product['discount_amount']) && (float)$product['discount_amount'] > 0 ) {
								$product_price_et = $product['price_ht'] - $product['discount_amount'];
								$exist_discount = true;
								$discount_infos[] = array('discount_type' => 'discount_amount', 'amount' => $product['discount_amount'] );
							}
							if ( !empty($product['discount_rate']) && (float)$product['discount_rate'] ) {
								$product_price_et = $product['price_ht'] / (1 + ($product['discount_rate'] / 100) );
								$exist_discount = true;
								$discount_infos[] = array('discount_type' => 'discount_rate', 'amount' => $product['discount_rate']);
							}
						}
					}
				}
				
			}
			$discount_def = array($exist_discount, number_format($product_price_et, 5, '.', ''), array('discount_infos'=> $discount_infos, 'discount_date_from' => $product_discount_date_from, 'discount_date_to'=> $product_discount_date_to));
			return $discount_def;
		}
		
	}
 }
/**	Instanciate the module utilities if not	*/
if ( class_exists("wpshop_prices") ) {
	$wpshop_prices = new wpshop_prices();
}