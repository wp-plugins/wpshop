<?php
/**
 * Plugin Name: WpShop - Entity Filter
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: List products by filter
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/** Check if the plugin version is defined. If not defined script will be stopped here */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

if ( !class_exists("wpshop_entity_filter") ) {
	class wpshop_entity_filter {
		function __construct() {
			global $wpdb;
			$locale = get_locale();
			if ( defined("ICL_LANGUAGE_CODE") ) {
				$query = $wpdb->prepare("SELECT locale FROM " . $wpdb->prefix . "icl_locale_map WHERE code = %s", ICL_LANGUAGE_CODE);
				$local = $wpdb->get_var($query);
				$locale = !empty($local) ? $local : $locale;
			}
			$moFile = dirname(__FILE__).'/languages/wpshop_entity_filter-' . $locale . '.mo';
			if ( !empty($locale) && (is_file($moFile)) ) {
				load_textdomain('wpshop', $moFile);
			}

			add_action('restrict_manage_posts', array(&$this, 'wpshop_entity_filter'));
			add_filter('parse_query', array(&$this, 'wpshop_entity_filter_parse_query'));
		}

		function wpshop_entity_filter() {
			if (isset($_GET['post_type'])) {
				$post_type = $_GET['post_type'];
				if (post_type_exists($post_type) && ($post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)) {
					$filter_possibilities = array();
					$filter_possibilities[''] = __('-- Select Filter --', 'wpshop');
					$filter_possibilities['no_picture'] = __('List products without picture', 'wpshop');
					$filter_possibilities['no_price'] = __('List products without price', 'wpshop');
					$filter_possibilities['no_description'] = __('List products without description', 'wpshop');
					echo wpshop_form::form_input_select('entity_filter', 'entity_filter', $filter_possibilities, (!empty($_GET['entity_filter']) ? $_GET['entity_filter'] : ''), '', 'index');
				}
			}
		}

		function wpshop_entity_filter_parse_query($query) {
			global $pagenow, $wpdb;

			if ( is_admin() && ($pagenow == 'edit.php') && !empty( $_GET['post_type'] ) && ( $_GET['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) && !empty( $_GET['entity_filter'] ) ) {

				/**	No picture */
				if( $_GET['entity_filter'] =='no_picture') {
					$sql_query = $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} pm WHERE meta_key = %s ", '_thumbnail_id');
					$check = 'post__not_in';
				}

				/**	No price	*/
				if($_GET['entity_filter'] =='no_price') {
					$table_attribute_decimal = $wpdb->prefix . "wpshop__attribute_value_decimal";
					$price_attribute = wpshop_attributes::getElement( WPSHOP_PRODUCT_PRICE_TTC, "'valid'", 'code');
					$sql_query = $wpdb->prepare("SELECT DISTINCT ID as post_id FROM {$wpdb->posts} WHERE post_type = %s AND ID NOT IN (SELECT entity_id FROM {$table_attribute_decimal} WHERE value > 0 AND attribute_id = %d)", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $price_attribute->id);
					$check = 'post__in';
				}

				/**	No description	*/
				if($_GET['entity_filter'] =='no_description') {
					$sql_query = $wpdb->prepare("SELECT ID as post_id FROM {$wpdb->posts} WHERE post_content = '' AND post_type = %s", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
					$check = 'post__in';
				}

				$results = $wpdb->get_results($sql_query);
				$post_id_list = array();
				foreach($results as $item){
					$post_id_list[] = $item->post_id;
				}

				$query->query_vars[$check] = $post_id_list;
				$query->query_vars['post_type'] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
			}
		}
	}
}

if (class_exists("wpshop_entity_filter")) {
	$inst_wpshop_entity_filter = new wpshop_entity_filter();
}

?>