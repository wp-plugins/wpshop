<?php
/**
 * Main controller file for product mass modification module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Main controller class for product mass modification module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_product_mdl {

	/**
	 * Get product product Attributes definition
	 *
	 * @param integer $product_id
	 *
	 * @return array
	 */
	function get_product_atts_def( $product_id ) {
		$wps_entites = new wpshop_entities();
		$product_entity_id = $wps_entites->get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
		$element_atribute_list = wpshop_attributes::getElementWithAttributeAndValue( $product_entity_id, $product_id, WPSHOP_CURRENT_LOCALE, '', 'frontend' );

		return $element_atribute_list;
	}

	/**
	 * Return Products which name start by querying letter
	 * @param integer $letter
	 * @return array
	 */
	function get_products_by_letter( $letter = 'A' ) {
		global $wpdb;
		if ( $letter === __('ALL', 'wpshop' ) ) {
			$query = $wpdb->prepare( 'SELECT ID, post_title FROM ' .$wpdb->posts. ' WHERE post_status = %s AND post_type = %s  ORDER BY post_title ASC', 'publish', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
		}
		else {
			$query = $wpdb->prepare( 'SELECT ID, post_title FROM ' .$wpdb->posts. ' WHERE post_status = %s AND post_type = %s AND post_title LIKE %s ORDER BY post_title ASC', 'publish', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $letter.'%');
		}
		$products = $wpdb->get_results( $query );
		return $products;
	}
}