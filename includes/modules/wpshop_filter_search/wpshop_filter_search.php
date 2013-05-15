<?php
/**
 * Plugin Name: WP-Shop-filter_search
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Filter Search
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WPShop Filter Search bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wpshop_filter_search") ) {
	class wpshop_filter_search {
		function __construct() {
			
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			add_shortcode('wpshop_filter_search', array(&$this, 'display_filter_search'));
			
			/** CSS Include **/
			wp_register_style( 'wpshop_filter_search_css', plugins_url('templates/wpshop/css/wpshop_filter_search.css', __FILE__) );
			wp_enqueue_style( 'wpshop_filter_search_css' );
			
			/** JS Include **/
			if ( !is_admin() ) {
				wp_enqueue_script( 'wpshop_filter_search_js', plugins_url('templates/wpshop/js/wpshop_filter_search.js', __FILE__) );
			}
			
			/** Ajax action **/
			add_action('wp_ajax_update_filter_product_display',array(&$this, 'wpshop_ajax_update_filter_product_display'));
			add_action('wp_ajax_filter_search_action',array(&$this, 'wpshop_ajax_filter_search_action'));
			
			add_action('save_post', array(&$this, 'save_displayed_price_meta'));
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
		
		function display_filter_search () {
			global $wp_query;
			if ( !empty($wp_query) && !empty($wp_query->queried_object_id) ) {
				$category_id = $wp_query->queried_object_id;
				$output = $this->construct_wpshop_filter_search_interface( $category_id );
				return $output;
			}
		}
		
		/**
		 * Return a filter search interface for the current category
		 * @param integer $category_id
		 * @return string
		 */
		function construct_wpshop_filter_search_interface ( $category_id ) {
			global $wpdb;
			$tpl_component = array();
			$tpl_component['CATEGORY_ID'] = $category_id;
			$filter_search_interface = $tpl_component['FILTER_SEARCH_ELEMENT'] = '';
			if ( !empty($category_id) ) {
				$category_option =  get_option('wpshop_product_category_'.$category_id);
				if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) ) {
					foreach ( $category_option['wpshop_category_filterable_attributes'] as $attribute ) {
						$attribute_def = wpshop_attributes::getElement($attribute);
						$tpl_component['FILTER_SEARCH_ELEMENT'] .= $this->construct_element( $attribute_def, $category_id );
						$unity = '';
						if ( !empty($attribute_def->_default_unit) ) {
							$query = $wpdb->prepare('SELECT unit FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id= %d', $attribute_def->_default_unit);
							$unity = $wpdb->get_var( $query );
						}
						
						$tpl_component['DEFAULT_UNITY'.'_'.$attribute_def->code] = $unity;
					}
				}
			}
			$filter_search_interface = wpshop_display::display_template_element('wpshop_filter_search_interface', $tpl_component, array(), 'wpshop');
			return $filter_search_interface;
		}
		
		function construct_element ( $attribute_def, $category_id ) {
			if ( !empty( $attribute_def ) ) {
				switch ( $attribute_def->frontend_input ) {
					case 'text' : 
						if ( $attribute_def->data_type == 'decimal' || $attribute_def->data_type == 'integer') {
							return $this->get_filter_element_for_integer_data( $attribute_def, $category_id );
						}
						else {
							return $this->get_filter_element_for_text_data( $attribute_def, $category_id );
						}
					break;
					
					case 'select' :
						return $this->get_filter_element_for_list_data ( $attribute_def);
					break;
				}
			}
		}
		
		
		/**
		 * Construct the element when it's a decimal Data
		 * @param StdObject $attribute_def
		 * @return string
		 */
		function get_filter_element_for_integer_data ( $attribute_def, $category_id ) {
 			$min_value = $max_value = 0;
 			$sub_tpl_component = array();
 			$output = '';
 			$category_product_ids = wpshop_categories::get_product_of_category( $category_id );
 			if ( !empty( $category_product_ids ) ) {
 				$price_piloting_option = get_option('wpshop_shop_price_piloting');
 				foreach ($category_product_ids as $category_product_id) {
 					if ( $attribute_def->code == WPSHOP_PRODUCT_PRICE_TTC || $attribute_def->code == WPSHOP_PRODUCT_PRICE_HT ) {
 						
 						$product_infos = wpshop_products::get_product_data($category_product_id);
 						$product_price_infos = wpshop_prices::check_product_price($product_infos);
 						if (!empty($product_price_infos) && !empty($product_price_infos['fork_price']) && !empty($product_price_infos['fork_price']['have_fork_price']) && $product_price_infos['fork_price']['have_fork_price'] ) {
  							
  											
 							$max_value = ( !empty($product_price_infos['fork_price']['max_product_price']) && $product_price_infos['fork_price']['max_product_price'] > $max_value ) ? $product_price_infos['fork_price']['max_product_price'] : $max_value;
 							$min_value = (!empty($product_price_infos['fork_price']['min_product_price']) && ( ( $product_price_infos['fork_price']['min_product_price'] < $min_value) || $min_value == 0) ) ?  $product_price_infos['fork_price']['min_product_price'] : $min_value;
 						}
 						else {
 							if (!empty($product_price_infos) && !empty($product_price_infos['discount']) && !empty($product_price_infos['discount']['discount_exist'] ) && $product_price_infos['discount']['discount_exist'] ) {
 								$product_data = (!empty($price_piloting_option) &&  $price_piloting_option == 'HT')  ? $product_price_infos['discount']['discount_et_price'] : $product_price_infos['discount']['discount_ati_price'];

 							}
 							else {
 		
 								$product_data = (!empty($price_piloting_option) &&  $price_piloting_option == 'HT')  ? $product_price_infos['et'] : $product_price_infos['ati'];
 							}
 							$max_value = ( !empty($product_data) && $product_data > $max_value ) ? $product_data : $max_value;
 							$min_value = (!empty($product_data) && ( ( $product_data < $min_value) || $min_value == 0) ) ?  $product_data : $min_value;
 						}
 					}
 					else {
 						$product_postmeta = get_post_meta($category_product_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
 						$product_data = $product_postmeta[$attribute_def->code];
 						$max_value = ( !empty($product_data) && $product_data > $max_value ) ? $product_data : $max_value;
 						$min_value = (!empty($product_data) && ( ( $product_data < $min_value) && $min_value == 0) ) ?  $product_data : $min_value;
 					}
 					$sub_tpl_component['FILTER_SEARCH_ATTRIBUTE_TITLE'] = __($attribute_def->frontend_label, 'wpshop');
 					$sub_tpl_component['FILTER_SEARCH_FILTER_LIST_NAME'] = $attribute_def->code;
 					$sub_tpl_component['FILTER_SEARCH_MIN_DATA'] = round($min_value,2);
 					$sub_tpl_component['FILTER_SEARCH_MAX_DATA'] = round($max_value,2);
 				}
 				$output = wpshop_display::display_template_element('wpshop_filter_search_element_for_integer_data', $sub_tpl_component, array(), 'wpshop');
 				unset($sub_tpl_component);
			}
			return $output;
		}
		
		/**
		 * Construct the element when it's a text Data
		 * @param StdObject $attribute_def
		 * @return string
		 */
		function get_filter_element_for_text_data( $attribute_def, $category_id ) {
			$output = '';
			$list_values = array();
			$sub_tpl_component = array();
			$sub_tpl_component['FILTER_SEARCH_ATTRIBUTE_TITLE'] = __($attribute_def->frontend_label, 'wpshop');
			$sub_tpl_component['FILTER_SEARCH_FILTER_LIST_NAME'] = $attribute_def->code;
			$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] = '';
			$category_product_ids = wpshop_categories::get_product_of_category( $category_id );
			if ( !empty( $category_product_ids ) ) {
				foreach ( $category_product_ids as $category_product_id ) {
					$product_postmeta = get_post_meta($category_product_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
					$product_data = $product_postmeta[$attribute_def->code];
					if ( !in_array( $product_data,  $list_values) ) {
						$list_values[] = $product_data;
						$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] .= '<option value="' .$product_data. '">' .$product_data. '</option>';
					}  
				}
				$output = wpshop_display::display_template_element('wpshop_filter_search_element_for_text_data', $sub_tpl_component, array(), 'wpshop');
				unset($sub_tpl_component);
			}
			return $output;
		}
		
		/**
		 * Construct the element when it's a list Data
		 * @param StdObject $attribute_def
		 * @return string
		 */
		function get_filter_element_for_list_data ( $attribute_def ) {
			global $wpdb;
			$output = '';
			if ( !empty( $attribute_def) ){
				$sub_tpl_component['FILTER_SEARCH_ATTRIBUTE_TITLE'] = __($attribute_def->frontend_label, 'wpshop');
				$sub_tpl_component['FILTER_SEARCH_FILTER_LIST_NAME'] = $attribute_def->code;
				$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] = '';
				$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE attribute_id = %d',  $attribute_def->id);
				$attribute_options = $wpdb->get_results($query);

				if ( !empty($attribute_options) ) {
					foreach( $attribute_options as $attribute_option ) {
						$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] .= '<option value="' .$attribute_option->id. '">' .$attribute_option->label. '</option>';
					}
					$output = wpshop_display::display_template_element('wpshop_filter_search_element_for_text_data', $sub_tpl_component, array(), 'wpshop');
				}
			}
			return $output;
		}
		
		
		/**
		 * Pick up all filter search element type
		 * @param integer $category_id
		 * @return array
		 */
		function pick_up_filter_search_elements_type ( $category_id ) {
			$filter_search_elements = array();
			if ( !empty($category_id) ) {
				$category_option =  get_option('wpshop_product_category_'.$category_id);
				if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) ) {
					foreach ( $category_option['wpshop_category_filterable_attributes'] as $attribute ) {
						$attribute_def = wpshop_attributes::getElement($attribute);
						if ( !empty($attribute_def) ) {
							if ( $attribute_def->frontend_input == 'text' ) {
								$filter_search_elements['_'.$attribute_def->code] = array('type' => 'fork_values');
							}
							else {
								$filter_search_elements['_'.$attribute_def->code] = array('type' => 'select_value');
							}
						}
					}
				}
			}
			return $filter_search_elements;
		}
		
		/**
		 * Ajax function which construct, execute and display the filter search request
		 */
		function wpshop_ajax_filter_search_action () {
			global $wpdb;
			$category_id =  !empty($_POST['wpshop_filter_search_category_id']) ? wpshop_tools::varSanitizer($_POST['wpshop_filter_search_category_id']) : 0;
			$filter_search_elements = $this->pick_up_filter_search_elements_type($category_id);
			$request_cmd = '';
			$status = false;
			
			/** SQL request Construct for pick up all product with one of filter search element value **/
			if ( !empty($filter_search_elements) && !empty($_REQUEST) ) {
				$request_cmd = '';
				$first = true;
				$i = 1;
				foreach ( $filter_search_elements as $k=>$filter_search_element ) {
					if ( $filter_search_element['type'] == 'select_value' ) {
						$request_cmd .= 'SELECT meta_key, post_id FROM ' .$wpdb->postmeta. ' INNER JOIN ' .$wpdb->posts. ' ON  post_id = ID WHERE (meta_key = "'.$k.'" AND meta_value = "'.$_REQUEST['filter_search'.$k].'") AND post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'" ';
						$request_cmd .= ' AND post_id IN (SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id = '.$category_id.') ';
					}
					else if($filter_search_element['type'] == 'fork_values') {
						$request_cmd .= 'SELECT meta_key, post_id FROM ' .$wpdb->postmeta. ' INNER JOIN ' .$wpdb->posts. ' ON  post_id = ID WHERE (meta_key = "'.( ( !empty($k) && $k == '_product_price' ) ? '_wpshop_displayed_price' : $k).'" AND meta_value BETWEEN '.$_REQUEST['amount_min'.$k].' AND '.$_REQUEST['amount_max'.$k].') AND post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'"';
						$request_cmd .= ' AND post_id IN (SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id = '.$category_id.') ';
	
					}
					
					if ($i < count($filter_search_elements) ) {
						$request_cmd .= ' UNION ';
					}
					$i++;
				}
				
				/** SQL Request execution **/
				$query = $wpdb->prepare($request_cmd, ''); 
				$results = $wpdb->get_results($query);
				$first = true;
				$final_result = array();
				
				$temp_result = array();
				$first_key = null;
				
				
				/** Make a sorting and keep all product which corresponds at all fiter serach elements values **/
				foreach ( $results as $result ) {
					if ( empty($first_key) ) {
						$first_key = $result->meta_key;
					}
					if ( $result->meta_key == $first_key ) {
						$temp_result[] = $result->post_id;
						$final_result[$result->post_id] = $result->post_id;
					}
					else {
						if ( in_array($result->post_id, $temp_result) ) {
							$final_result[$result->post_id] = $result->post_id;
						}
						else {
							unset( $final_result[$result->post_id] );
						}
					}
				}

				
				$result_product_display = $products_list = '';
				$display_type = 'grid';
				$element_per_line = 3;
				/** If there is products for this filter search **/
				if ( !empty($final_result) ) {
					$tpl_component = array();
					$current_element_position = 1;
					foreach ( $final_result as $product ) {
						$products_list .= wpshop_products::product_mini_output($product, $category_id, $display_type, '', $element_per_line);
					}
					$tpl_component['PRODUCT_CONTAINER_TYPE_CLASS'] = ($display_type == 'grid' ? ' ' . $display_type . '_' . $element_per_line : '') . ' '. $display_type .'_mode';
					$tpl_component['PRODUCT_LIST'] = $products_list;
					$result_product_display = wpshop_display::display_template_element('product_list_container', $tpl_component);
					unset($tpl_component);
					echo $result_product_display;
					
				}
				else {
					echo $result_product_display = __('Sorry ! No product correspond to your filter search request', 'wpshop');
				}
				
				$status = true;
			}
			die();
		}
		
		function save_displayed_price_meta() {
			if ( !empty($_POST) && !empty($_POST['ID']) && !empty($_POST['post_type']) && $_POST['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
				$price_piloting = get_option('wpshop_shop_price_piloting');
				$product_data = wpshop_products::get_product_data($_POST['ID']);
				$price_infos = wpshop_prices::check_product_price($product_data);

				if ( !empty($price_infos) ) {
					if ( !empty($price_infos['discount']) &&  !empty($price_infos['discount']['discount_exist']) ) {
						$displayed_price = ( !empty($price_piloting) && $price_piloting == 'HT') ? $price_infos['discount']['discount_et_price'] : $price_infos['discount']['discount_ati_price'];
					}
					else if( !empty($price_infos['fork_price']) && !empty($price_infos['fork_price']['have_fork_price']) ) {
						$displayed_price = $price_infos['fork_price']['min_product_price'];
					}
					else {
						$displayed_price = ( !empty($price_piloting) && $price_piloting == 'HT') ? $price_infos['et'] : $price_infos['ati'];
					}
				}
				update_post_meta($_POST['ID'], '_wpshop_displayed_price', number_format($displayed_price,2, '.','') );
			}
		}
	}
}
if ( class_exists("wpshop_filter_search") ) {
	$wpshop_filter_search = new wpshop_filter_search();
}