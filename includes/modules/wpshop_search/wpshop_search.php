<?php
/**
 * Plugin Name: WPShop - search module
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WPShop search utilities
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}


/**
 * WPShop importer bootstrap file
 * @author Alexandre Techer - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 */
if ( !class_exists( "wpshop_search" ) ) {
	class wpshop_search {

		function __construct() {
			/**	Extend search action with wpshop	*/
			if  (!is_admin() ) {
 				//add_action('posts_where_request', array(&$this, 'wpshop_search_where'));
			}

			add_shortcode('wpshop_custom_search', array(&$this, 'get_products_search'/*'wpshop_custom_search_shortcode'*/)); // Custom search
			add_shortcode('wpshop_advanced_search', array(&$this, 'wpshop_advanced_search_shortcode')); // Advanced search
		}

		
		function get_products_search( ) {
			global $wpdb;
			$search_request = wpshop_tools::varSanitizer( get_search_query() );
			$request = '';
			/** Get Product entity ID **/
			$product_entity_id = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			
			$prepare_params = array(  WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'publish', '%'.$search_request.'%', '%'.$search_request.'%', '%'.$search_request.'%' );
			if ( !empty($product_entity_id) ) {
				/** Get searchable attributes **/
				$query = $wpdb->prepare('SELECT code FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE entity_id = %d AND is_searchable = %s', $product_entity_id, 'yes');
				$searchable_attributes = $wpdb->get_results( $query );
				
				foreach( $searchable_attributes as $searchable_attribute ) {
					$request .= 'OR (meta_key = %s AND meta_value LIKE %s) ';
					$prepare_params[] = '_'.$searchable_attribute->code;
					$prepare_params[] = '%'.$search_request.'%';
					}
			}

			$query = $wpdb->prepare('SELECT DISTINCT ID FROM ' .$wpdb->posts.', '.$wpdb->postmeta.' WHERE post_type = %s AND post_status = %s AND post_id = ID AND (post_title LIKE %s OR post_content LIKE %s ' .$request. ')' , $prepare_params);
	
			$products = $wpdb->get_results( $query );
			if ( !empty($products) ) {
				$products_id = '';
				foreach ( $products as $product ) {
					$products_id .= $product->ID.',';
				}
				echo do_shortcode( '[wpshop_products pid="' . $products_id . '" ]' ) ;
			}
		}
		
		
		
		/**
		 * Custom search shortcode
		 */
		function wpshop_custom_search_shortcode( $custom_search_shortcode_args ) {
			
			$products_list = $others = '';

			$display_type = !empty($custom_search_shortcode_args['type']) ? $custom_search_shortcode_args['type'] : 'list';
			$element_per_line = !empty($custom_search_shortcode_args['per_line']) ? $custom_search_shortcode_args['per_line'] : WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE;

			$current_element_position = 1;
			$final_result = array();
			while ( have_posts() ) : the_post();
				if ( get_post_type( get_the_ID() ) == "wpshop_product" ) {
// 					ob_start();
// 					echo wpshop_products::product_mini_output(get_the_ID(), 0, $display_type, $current_element_position, $element_per_line);
// 					$products_list .= ob_get_contents();
// 					ob_end_clean();
					
					$final_result[] = get_the_ID();
				}
				else if (!isset($custom_search_shortcode_args['display_element']) || ($custom_search_shortcode_args['display_element'] != 'only_products')) {
					ob_start();
					get_template_part( 'content', get_post_format() );
					$others .= ob_get_contents();
					ob_end_clean();
				}
				$current_element_position++;
			endwhile;

			
			$tpl_component = array();
			if ( !empty($final_result) ) {
				//$tpl_component['PRODUCT_CONTAINER_TYPE_CLASS'] = ($display_type == 'grid' ? ' ' . $display_type . '_' . $element_per_line : '') . ' '. $display_type .'_mode';
				//$tpl_component['PRODUCT_LIST'] =  do_shortcode( '[wpshop_products pid="' . implode(',', $final_result) . '" ]' ) ;;
				echo do_shortcode( '[wpshop_products pid="' . implode(',', $final_result) . '" ]' ) ;//wpshop_display::display_template_element('product_list_container', $tpl_component);
			}

			echo $others;
		}

		/**
		 * Advanced search shortcode
		 */
		function wpshop_advanced_search_shortcode() {
			global $wpdb;

			/*	Display advanced search form	*/
			$output = wpshop_search::wpshop_advanced_search_form_display();

			$display_type = !empty($custom_search_shortcode_args['type']) ? $custom_search_shortcode_args['type'] : 'list';
			$element_per_line = !empty($custom_search_shortcode_args['per_line']) ? $custom_search_shortcode_args['per_line'] : WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE;

			/*		*/
			if(!empty($_POST['search'])) {

				if(!empty($_POST['advanced_search_attribute'])) {
					$table_to_use = $data_to_use = array();
					// Foreach the post data
					foreach($_POST['advanced_search_attribute'] as $type => $array) {
						foreach($array as $att_code => $att_value) {
							if(!empty($att_value)) {

								// If data type is decimal, we trait the number format
								if($type=='decimal') {
									$att_value = str_replace(',', '.', $att_value);
									$number_figures=5;
									$att_value = number_format((float)$att_value, $number_figures, '.', '');
								}

								$data_to_use[$type][$att_code] = $att_value;

								if(!in_array($type, $table_to_use)) {
									$table_to_use[] = $type;
								}
							}
						}
					}
					$left_join=$where='';
					foreach($table_to_use as $t) {
						$left_join .= ' LEFT JOIN ' . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $t . ' AS att_' . $t . ' ON att_' . $t . '.entity_id=post.ID';

						foreach($data_to_use[$t] as $code => $value) {
							$attr = wpshop_attributes::getElement($code,"'valid'",'code');
							$where .= 'att_'.$t.'.attribute_id="'.$attr->id.'" AND att_' . $t . '.value LIKE "%' . $value . '%" AND ';
						}
					}
					if(!empty($where))$where='WHERE '.substr($where,0,-4);
				}

				$results = '';

				if( (!empty($table_to_use) && !empty($data_to_use) && !empty($where) && !empty($left_join)) OR !empty($_POST['wpshop_search_post_title'])) {
					if(!empty($_POST['wpshop_search_post_title'])) {
						if(!empty($where))$where.='AND post.post_title LIKE "%'.$wpdb->escape($_POST['wpshop_search_post_title']).'%"';
						else $where.='WHERE post.post_title LIKE "%'.$wpdb->escape($_POST['wpshop_search_post_title']).'%"';
					}

					$query = "SELECT post.ID FROM " . $wpdb->posts . " AS post " . $left_join . " " . $where . " AND post.post_status = 'publish' GROUP BY post.ID";
					$data = $wpdb->get_results($query);

					if(!empty($data)) {
						$current_element_position = 0;
						foreach($data as $d) {
							$results .= wpshop_products::product_mini_output($d->ID, 0, $display_type, $current_element_position, $element_per_line);
							$current_element_position++;
						}
					}
				}

				if ( !empty($results) ) {
					$tpl_component = array();
					$tpl_component['PRODUCT_CONTAINER_TYPE_CLASS'] = ($display_type == 'grid' ? ' ' . $display_type . '_' . $element_per_line : '') . ' '. $display_type .'_mode';
					$tpl_component['PRODUCT_LIST'] = $results;

					$output .= wpshop_display::display_template_element('product_list_container', $tpl_component);
				}
				else {
					$output .= '<p>'.__('Empty list','wpshop').'</p>';
				}
			}

			return $output;
		}

		/**
		 *
		 * @return Ambigous <string, string, mixed>
		 */
		function wpshop_advanced_search_form_display() {
			$output = '';

			$tpl_component = array();
			$tpl_component['SEARCHED_POST_TITLE'] = (!empty($_POST['wpshop_search_post_title']) ? $_POST['wpshop_search_post_title'] : '');

			$attribute_for_advanced_search = wpshop_attributes::getElement('yes', "'valid'", 'is_visible_in_advanced_search', true);
			$tpl_component['SPECIAL_FIELDS'] = '';
			if ( !empty( $attribute_for_advanced_search ) ) {
				foreach ( $attribute_for_advanced_search as $attribute) {
					$attribute_display = wpshop_attributes::get_attribute_field_definition( $attribute, (!empty($_POST['advanced_search_attribute'][$attribute->data_type][$attribute->code]) ? $_POST['advanced_search_attribute'][$attribute->data_type][$attribute->code] : ''), array('page_code' => 'advanced_search', 'input_class' => ' wpshop_advanced_search_field wpshop_advanced_search_field_' . $attribute->code) );
					$tpl_component['SPECIAL_FIELDS'] .= wpshop_display::display_template_element('advanced_search_form_input', array('FIELD_LABEL_POINTER' => $attribute_display['label_pointer'], 'FIELD_LABEL_TEXT' => __($attribute_display['label'], 'wpshop'), 'FIELD_INPUT' => $attribute_display['output']));
				}
			}
			$output = wpshop_display::display_template_element('advanced_search_form', $tpl_component);
			unset($tpl_component);

			return $output;
		}

		/**
		 * Add table for search query
		 * @param string $join The current
		 * @return string The new search query table list
		 */
		function wpshop_search_join( $join ) {
			global $wpdb;

			if( is_search() ) {
				$join .= " LEFT JOIN $wpdb->postmeta ON " . $wpdb->posts . ".ID = $wpdb->postmeta.post_id ";
			}

			return $join;
		}

		/**
		 *
		 * @param unknown_type $where
		 * @return mixed
		 */
		function wpshop_search_where( $where ) {
			global $wpdb;
			if( is_search() ) {
				/* Read the field to look into */
				$attribute_searchable = wpshop_attributes::getElement('yes', "'valid'", 'is_searchable', true);
				if ( !empty($attribute_searchable) ) {
					$fields = array();
					foreach ( $attribute_searchable as $attribute) {
						$fields[] = '_' . $attribute->code;
					}
					$more_where = "";
					$i = 1;
					foreach ( $fields as $field ) {
						if ($i > 1) {
							$more_where .= " OR ";
						}
						$more_where .= "({$wpdb->postmeta}.meta_key = '$field' AND {$wpdb->postmeta}.meta_value LIKE $1)";
						$i++;
					}

					$where = preg_replace(
							"/\(\s*wp_posts.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
							"(wp_posts.post_title LIKE $1) OR $more_where", $where );
					add_filter('posts_join_request', array('wpshop_search', 'wpshop_search_join'));
					add_filter('posts_groupby_request', array('wpshop_search', 'wpshop_search_groupby'));
				}
			}
			//echo $where.'<hr/>';
			return($where);
		}

		/**
		 *
		 * @param unknown_type $groupby
		 * @return unknown|string
		 */
		function wpshop_search_groupby( $groupby ) {
			global $wpdb;
			if ( !is_search() ) {
				return $groupby;
			}

			// we need to group on post ID
			$mygroupby = "{$wpdb->posts}.ID";

			if ( preg_match( "/$mygroupby/", $groupby )) {
				// grouping we need is already there
				return $groupby;
			}

			if ( !strlen(trim($groupby))) {
				// groupby was empty, use ours
				return $mygroupby;
			}
			
			// wasn't empty, append ours
			return $groupby . ", " . $mygroupby;
		}
	}
}

/**	Instanciate module utilities	*/
if ( class_exists( "wpshop_search" ) ) {
	$wpshop_search = new wpshop_search();
}
