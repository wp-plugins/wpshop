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
			add_action('wp_ajax_nopriv_update_filter_product_display',array(&$this, 'wpshop_ajax_update_filter_product_display'));
			add_action('wp_ajax_filter_search_action',array(&$this, 'wpshop_ajax_filter_search_action'));
			add_action('wp_ajax_nopriv_filter_search_action',array(&$this, 'wpshop_ajax_filter_search_action'));
			
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
						return $this->get_filter_element_for_list_data ( $attribute_def, $category_id);
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
 					$sub_tpl_component['FILTER_SEARCH_MIN_DATA'] = number_format($min_value,2, '.', '');
 					$sub_tpl_component['FILTER_SEARCH_MAX_DATA'] = number_format($max_value,2, '.', '');
 				}
 				if ( $sub_tpl_component['FILTER_SEARCH_MIN_DATA'] != $sub_tpl_component['FILTER_SEARCH_MAX_DATA'] ) {
 					$output = wpshop_display::display_template_element('wpshop_filter_search_element_for_integer_data', $sub_tpl_component, array(), 'wpshop');
 				}
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
			global $wpdb;
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
		function get_filter_element_for_list_data ( $attribute_def, $category_id ) {
			global $wpdb;
			$output = '';
			$products = wpshop_categories::get_product_of_category( $category_id );
			
			if ( !empty( $attribute_def) ){
				$sub_tpl_component['FILTER_SEARCH_ATTRIBUTE_TITLE'] = __($attribute_def->frontend_label, 'wpshop');
				$sub_tpl_component['FILTER_SEARCH_FILTER_LIST_NAME'] = $attribute_def->code;
				$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] = '';
				$available_attribute_values = array();
				foreach ( $products as $product ) {
					$available_attribute_values = array_merge( $available_attribute_values, wpshop_attributes::get_affected_value_for_list( $attribute_def->code, $product, $attribute_def->data_type_to_use) ) ;
				}
				$available_attribute_values = array_flip($available_attribute_values);
				
				if ( !empty($available_attribute_values) ) {
					foreach( $available_attribute_values as $k => $available_attribute_value ) {
						$brand_name = get_the_title( $k );
						$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] .= '<option value="' .$k. '">' .$brand_name. '</option>';
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
			$page_id = ( !empty( $_POST['wpshop_filter_search_current_page_id']) ) ? wpshop_tools::varSanitizer( $_POST['wpshop_filter_search_current_page_id'] ) : 1;
			$request_cmd = '';
			$status = false;
			
			
			
			foreach ( $filter_search_elements as $k=>$filter_search_element) {
				if ( $filter_search_element['type'] == 'select_value' && $_REQUEST['filter_search'.$k] == 'all_attribute_values' ) {
					unset( $filter_search_elements[$k]);
				}
			}

			/** SQL request Construct for pick up all product with one of filter search element value **/
			if ( !empty($filter_search_elements) && !empty($_REQUEST) ) {
				$request_cmd = '';
				$first = true;
				$i = 1;
				$filter_search_elements_count = count($filter_search_elements);
				foreach ( $filter_search_elements as $k=>$filter_search_element ) {
					if ( !empty($filter_search_element['type']) && !empty($_REQUEST['filter_search'.$k]) && $filter_search_element['type'] == 'select_value' && $_REQUEST['filter_search'.$k] != 'all_attribute_values') {
						$request_cmd .= 'SELECT meta_key, post_id FROM ' .$wpdb->postmeta. ' INNER JOIN ' .$wpdb->posts. ' ON  post_id = ID WHERE (meta_key = "'.$k.'" AND meta_value = "'.wpshop_tools::varSanitizer($_REQUEST['filter_search'.$k]).'") AND post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'" ';
						$request_cmd .= ' AND post_id IN (SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id = '.$category_id.') ';
					}
					else if($filter_search_element['type'] == 'fork_values') {
						$request_cmd .= 'SELECT meta_key, post_id FROM ' .$wpdb->postmeta. ' INNER JOIN ' .$wpdb->posts. ' ON  post_id = ID WHERE (meta_key = "'.( ( !empty($k) && $k == '_product_price' ) ? '_wpshop_displayed_price' : $k).'" AND meta_value BETWEEN '.wpshop_tools::varSanitizer($_REQUEST['amount_min'.$k]).' AND '.wpshop_tools::varSanitizer($_REQUEST['amount_max'.$k]).') AND post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'"';
						$request_cmd .= ' AND post_id IN (SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id = '.wpshop_tools::varSanitizer($category_id).') ';
	
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
				
				$last = '';
				/** Transform the query result array **/
				foreach ( $results as $result ) {
					$result->meta_key = ( !empty($result->meta_key) && $result->meta_key == '_wpshop_displayed_price' ) ? '_product_price' : $result->meta_key;
					if ( $last != $result->meta_key ){
						$filter_search_elements[$result->meta_key]['count'] = 1;
						$last = $result->meta_key;
					}
					else
						$filter_search_elements[$result->meta_key]['count']++;
					
					$filter_search_elements[$result->meta_key]['values'][] = $result->post_id;
				}
				/** Check the smaller array of attributes **/
				$smaller_array = '';
				$smaller_array_count = -1;
				foreach ( $filter_search_elements as $k=>$filter_search_element ) {
					if ( empty($filter_search_element['count']) ) {
						$smaller_array_count = 0;
						$smaller_array = $k;
					}
					elseif( $smaller_array_count == -1 || $filter_search_element['count'] <= $smaller_array_count ) {
						$smaller_array_count = $filter_search_element['count'];
						$smaller_array = $k;
					}
					$filter_search_element_recap = '';
					
					/** Create a recap. */
					if ( !empty( $filter_search_element['type']) ) {
						
						$attribute_name = $k;
						if ( $filter_search_element['type'] == 'fork_values') {
							$sub_tpl_component['FILTER_SEARCH_REACAP_EACH_ELEMENT'] = sprintf( __(' %s between %d and %d', 'wpshop'), $attribute_name, $_REQUEST['amount_min'.$k], $_REQUEST['amount_max'.$k] );
						}
						else {
							$sub_tpl_component['FILTER_SEARCH_REACAP_EACH_ELEMENT'] = ( !empty($_REQUEST['filter_search'.$k]) ) ? $attribute_name.' : '.$_REQUEST['filter_search'.$k] : '';
						}
						$filter_search_element_recap .= wpshop_display::display_template_element('filter_search_recap_each_element', $sub_tpl_component, array(), 'wpshop');
						unset($sub_tpl_component);
					}
					
				}

				/** Compare the smaller array with the others **/
				if ( !empty($smaller_array_count) ) {
					$temp_tab = $filter_search_elements[$smaller_array]['values'];
					foreach ( $filter_search_elements as $filter_search) { 
						foreach ( $temp_tab as $value ) {
							if ( !in_array($value, $filter_search['values']) ) {
								/** If value don't exist in the smaller array, delete it **/
								$key = array_keys($temp_tab, $value);
								if ( !empty($key) && !empty($key[0]) ) {
									unset($temp_tab[$key[0]]);
								}
							}
							
						}
					}
					/** Final result to display the products **/
					$final_result = $temp_tab;
				}
				else {
					$final_result = array();
				}
				/** If there is products for this filter search **/
				$status = true;
				//echo do_shortcode( $this->display_ajax_filter_search_action($final_result, $filter_search_element_recap) ) ;
				echo do_shortcode( '[wpshop_products pid="' . implode(',', $final_result) . '" container="no" ]' ) ;
			}
			die();
		}
		
		/**
		 * Return the result of filter search
		 * @param array $product_id_list
		 * @return string
		 */
		function display_ajax_filter_search_action ( $product_id_list, $filter_search_element_recap ) {
			$result_product_display = $products_list = '';
			$display_options = get_option('wpshop_display_option');
			$display_type = ( !empty($display_options) && !empty($display_options['wpshop_display_list_type']) ) ? $display_options['wpshop_display_list_type'] : 'grid';
			$element_per_line = (!empty($display_options) && !empty($display_options['wpshop_display_grid_element_number'])) ? $display_options['wpshop_display_grid_element_number'] : 3;
			$elements_per_page = ( !empty( $display_options) && !empty( $display_options['wpshop_display_element_per_page']) ) ? $display_options['wpshop_display_element_per_page'] : 20;
			
			
			if ( !empty($product_id_list) ) {
				$tpl_component = array();
				$current_element_position = 1;
				foreach ( $product_id_list as $product ) {
			
					$cats = get_the_terms($product, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
					$cats = !empty($cats) ? array_values($cats) : array();
					$cat_id = empty($cats) ? 0 : $cats[0]->term_id;
					$products_list .= wpshop_products::product_mini_output( $product, $cat_id, $display_type, $current_element_position, $element_per_line);
					$current_element_position  ++;
				}
				$tpl_component = array();
				$tpl_component['PRODUCT_CONTAINER_TYPE_CLASS'] = ($display_type == 'grid' ? ' ' . $display_type . '_' . $element_per_line : '') . ' '. $display_type .'_mode';
				$tpl_component['PRODUCT_LIST'] = $products_list;
				$tpl_component['CROSSED_OUT_PRICE'] = '';
				$tpl_component['LOW_STOCK_ALERT_MESSAGE'] = '';
				$result_product_display = count($product_id_list).'<br/>'.wpshop_display::display_template_element('product_list_container', $tpl_component);
				unset( $tpl_component);
					
				$page_number = 1;
				$total_max_pages = round( (count($product_id_list) / (int)$elements_per_page) , 0, PHP_ROUND_HALF_UP);
					
				/** Pagination managment **/
				$paginate = paginate_links(array(
						'base' => '#',
						'current' => $page_number,
						'total' => $total_max_pages,
						'type' => 'array',
						'prev_next' => false
				));
					
				if(!empty($paginate)) {
					$result_product_display .= '<ul id="pagination_filter_search" >';
					foreach($paginate as $p) {
						$result_product_display .= '<li>'.$p.'</li>';
					}
					$result_product_display .= '</ul>';
				}
					
				/** Create a recap **/
				$sub_tpl_component['FILTER_SEARCH_RECAP'] = $filter_search_element_recap;
				unset($tpl_component);
				$recap_to_display = wpshop_display::display_template_element('filter_search_recap', $sub_tpl_component, array(), 'wpshop');
				unset($sub_tpl_component);
					
				$result_product_display = $recap_to_display.$result_product_display;
					
			}
			else {
				$result_product_display = __('Sorry ! No product correspond to your filter search request', 'wpshop');
			}
			return $result_product_display;
		}
		
		/** 
		 * Save the price which is displayed on website
		 */
		
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