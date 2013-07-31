<?php
/**
 * Plugin Name: WP-Shop-Customer-Account
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Customer Account
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WpShop Customer Account bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wps_account") ) {
	class wps_account {
		function __construct() {
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
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
		
		
		/**
		 * Return the account form attributes List
		 * @return array
		 */
		function get_account_form_attributes_list () {
			global $wpdb;
			$account_entity_post_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
			$attributes_list = array();
			if ( !empty($account_entity_post_id) ) {
				$attributes_sets_list = wpshop_attributes_set::getElement( $account_entity_post_id, "'valid'", 'entity_id', 'all');
				/** Attributes List **/
				foreach ( $attributes_sets_list as $attributes_set ) {
					if ( $attributes_set->default_set == 'yes' ) {
						$attributes_set_details = wpshop_attributes_set::getAttributeSetDetails( $attributes_set->id, "'valid'"  );

						foreach ( $attributes_set_details as $attributes_set_section ) {
							if ( !empty($attributes_set_section) && !empty($attributes_set_section['attribut']) && is_array($attributes_set_section['attribut']) ) {
								foreach( $attributes_set_section['attribut'] as $attribute ) {
									$attributes_list[] = $attribute;
								}
							}
						}
					}
				}
			}
			
			return $attributes_list;
		}
		
		
		function get_account_form ( $quick_form = false ) {
			$output = '';
			$attributes = $this->get_account_form_attributes_list();
			foreach( $attributes as $attribute ) {
				if ( $quick_form && $attribute->is_used_in_quick_add_form == 'yes' ) {
					$this->get_form_element( $attribute );
				}
				else {
					$this->get_form_element( $attribute );
				}
			}
			return $output;
		}
		
		function get_form_element ( $attribute_def ) {
			$tpl_component = array();
			$tpl_component['ACCOUNT_FORM_ELEMENT_LABEL'] = stripslashes($attribute_def->frontend_label);
			$tpl_component['ACCOUNT_FORM_REQUIRED_ELEMENT'] = ( !empty( $attribute_def->is_required ) && $attribute_def->is_required == 'yes' ) ? '*' : '';
			$value = ( !empty($_POST) && !empty($_POST['attribute']) && !empty($_POST['attribute'][$attribute_def->data_type]) && !empty($_POST['attribute'][$attribute_def->data_type]) && !empty($_POST['attribute'][$attribute_def->data_type][$attribute_def->code]) ) ? $_POST['attribute'][$attribute_def->data_type][$attribute_def->code] : '';
			
			$attribute_definition = wpshop_attributes::get_attribute_field_definition( $attribute_def, $value, array() );
			$tpl_component['ACCOUNT_FORM_ELEMENT_INPUT'] = $attribute_definition['output'];
			$output = wpshop_display::display_template_element('wps_account_form_element', $tpl_component, array(), 'wpshop');
		
			return $output;
		}
		
		
	}
}	
if ( class_exists("wps_account") ) {
	$wps_account = new wps_account();
}
?>