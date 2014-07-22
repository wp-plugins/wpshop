<?php
class wps_product_mdl {
	function __construct() {
		
	}
	
	function get_product_atts_def( $product_id ) {
		$wps_entites = new wpshop_entities();
		$product_entity_id = $wps_entites->get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
		$element_atribute_list = wpshop_attributes::getElementWithAttributeAndValue($product_entity_id, $product_id, WPSHOP_CURRENT_LOCALE, '', 'frontend');

		return $element_atribute_list;
	}
}