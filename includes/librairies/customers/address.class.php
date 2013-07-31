<?php
/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Define the different method to manage address
 *
 *	Define the different method and variable used to manage address
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 * @package wpshop
 * @subpackage librairies
 */

class wpshop_address{

	/**
	 * Generate an array with all fields for the address form construction. Classified by address type.
	 * @param $typeof
	 * @return array
	 */
	function get_addresss_form_fields_by_type ( $typeof, $id ='' ) {
		$current_item_edited = isset($id) ? (int)wpshop_tools::varSanitizer($id) : null;
		$address = array();
		$all_addresses = '';
		/*	Get the attribute set details in order to build the product interface	*/

		$atribute_set_details = wpshop_attributes_set::getAttributeSetDetails($typeof, "'valid'");
		if ( !empty($atribute_set_details) ) {
			foreach ($atribute_set_details as $productAttributeSetDetail) {
				$address = array();
				$group_name = $productAttributeSetDetail['name'];
				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute) {
						if(!empty($attribute->id)) {
							if ( !empty($_POST['submitbillingAndShippingInfo']) ) {
								$value = $_POST['attribute'][$typeof][$attribute->data_type][$attribute->code];
							}
							else {
								$value = wpshop_attributes::getAttributeValueForEntityInSet($attribute->data_type, $attribute->id, wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS), $current_item_edited, array('intrinsic' => $attribute->is_intrinsic, 'backend_input' => $attribute->backend_input));
							}
							$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute, $value, array() );
							$attribute_output_def['id'] = 'address_' . $typeof . '_' .$attribute_output_def['id'];
							$address[str_replace( '-', '_', sanitize_title($group_name) ).'_'.$attribute->code] = $attribute_output_def;
						}
					}
				}
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['name'] = $group_name;
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['content'] = $address;
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['id'] = str_replace('-', '_', sanitize_title($group_name));
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['attribute_set_id'] = $productAttributeSetDetail['attribute_set_id'];
			}

		}

		return $all_addresses;
	}

}