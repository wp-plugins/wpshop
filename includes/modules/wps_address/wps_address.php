<?php
/**
 * Plugin Name: WP Shop Address
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WP Shop Address
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WP Shop Address bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wps_address") ) {
	class wps_address {
		function __construct() {
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			add_action('wp_ajax_wps_load_address_form', array( &$this, 'wps_load_address_form') );
			add_action('wp_ajax_wps_save_address', array( &$this, 'wps_save_address') );
			add_shortcode('wps_address_list', array( &$this, 'get_addresses') );
			add_shortcode('wps_shipping_address_summary', array( &$this, 'get_shipping_address_summary') );
			add_shortcode('wps_billing_address_summary', array( &$this, 'get_billing_address_summary') );
			
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'wps_address_js', plugins_url('templates/wpshop/js/wps_address.js', __FILE__) );
			
			add_action( 'wp_ajax_wps_save_first_address', array( &$this, 'wps_save_first_address') );
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
		 * Get Address Interface 
		 * @return string
		 */
		function get_addresses() {
			global $wpdb;
			$output = '';
			$user_id = get_current_user_id();
			$addresses_list = array();
			$addresses = '';
			
			$shipping_address_option = get_option( 'wpshop_shipping_address_choice' );
			$billing_address_option = get_option( 'wpshop_billing_address' );
			$addresses_types_def = array( $shipping_address_option['choice'] => 'shipping_address', $billing_address_option['choice'] => 'billing_address');
			
			if ( !empty($user_id) ) {
				$addresses_list = self::get_addresses_list( $user_id );
				if ( !empty($addresses_list) ) {
					foreach( $addresses_list as $type => $addresses_list_by_type ) {
						$first = true;
						foreach( $addresses_list_by_type as $address_id => $address ) {
							$address_type_id = get_post_meta( $address_id, '_wpshop_address_attribute_set_id', true);
							$sub_tpl_component = array();

							if ( ( $first && empty($_SESSION[ $addresses_types_def[$address_type_id] ]) ) || $first && !empty($_SESSION[$addresses_types_def[$address_type_id]]) ) {
								$sub_tpl_component['SELECTED_ADDRESS'] = 'checked="checked"';
								$sub_tpl_component['ADDRESS_CLASS_OPEN_ELEMENT'] = 'class="wps-list-open"';
								$_SESSION[$addresses_types_def[$address_type_id]] = $address_id;
								$first = false;
							}
							elseif( !empty($_SESSION['shipping_address']) && $_SESSION['shipping_address'] == $address_id ) {
								$sub_tpl_component['SELECTED_ADDRESS'] = 'checked="checked"';
								$sub_tpl_component['ADDRESS_CLASS_OPEN_ELEMENT'] = 'class="wps-list-open"';
							}
							else {
								$sub_tpl_component['SELECTED_ADDRESS'] = '';
								$sub_tpl_component['ADDRESS_CLASS_OPEN_ELEMENT'] = '';
							}
							$sub_tpl_component['ADDRESS_ID'] = $address_id;
							$sub_tpl_component['ADDRESS_TYPE'] = $addresses_types_def[$address_type_id];
							$sub_tpl_component['ADDRESS_TITLE'] = ( !empty($address['address_title']) ) ? $address['address_title'] : '';
							
							$sub_tpl_component['ADDRESS'] = self::display_an_address( $address );
							$addresses .= wpshop_display::display_template_element('wps_address', $sub_tpl_component, array(), 'wpshop');
							unset( $sub_tpl_component );
						}
					}
					$tpl_component = array();
					$billing_address_option = get_option( 'wpshop_billing_address' );
					$tpl_component['BILLING_ADDRESS_TYPE_ID'] = $billing_address_option['choice'];
					
					$shipping_address_option = get_option( 'wpshop_shipping_address_choice' );
					$tpl_component['SHIPPING_ADDRESS_TYPE_ID'] = $shipping_address_option['choice'];
					$tpl_component['ADDRESSES_LIST'] = $addresses;
					$output = wpshop_display::display_template_element('wps_addresses_container', $tpl_component, array(), 'wpshop');
					unset( $tpl_component );
				}
				else {
					/** Display an address_form **/
					$tpl_component['SHIPPING_FORM'] = self::display_form_fields( $shipping_address_option['choice'], '', 'first');
					$tpl_component['BILLING_FORM'] = self::display_form_fields( $billing_address_option['choice'], '', 'first');
					$tpl_component['LOADING_ICON'] =  WPSHOP_LOADING_ICON;
					$output = wpshop_display::display_template_element('wps_first_address_container', $tpl_component, array(), 'wpshop');
				}
			}
			return $output;
		}
		
		/**
		 * Get adress list for an user
		 * @param Integer $user_id
		 * @return Ambigous <multitype:, mixed, string, boolean, unknown, string>
		 */
		function get_addresses_list( $user_id ) {
			global $wpdb;
			$addresses_list = array();
			$query = $wpdb->prepare( 'SELECT ID FROM '. $wpdb->posts. ' WHERE post_type = %s AND post_parent = %s', WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, $user_id );
			$addresses = $wpdb->get_results( $query );
			foreach( $addresses as $address ) {
				$address_post_meta = get_post_meta( $address->ID, '_wpshop_address_metadata', true);
				$address_type_post_meta = get_post_meta( $address->ID, '_wpshop_address_attribute_set_id', true);
					
				if( !empty($address_post_meta) && !empty($address_type_post_meta) ) {
					$addresses_list[$address_type_post_meta][$address->ID] = $address_post_meta;
				}
			}
			return $addresses_list;
		}
		
		/** Display Address**/
		function display_an_address( $address ) {
			$model = '';
			if ( !empty($address) ){
				$model = get_post( wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS) );
				$model = $model->post_content;
				foreach( $address as $k => $address_element ) {
					$model = str_replace('['.$k.']', $address_element, $model);
				}
			}
			return $model;
		}
		
		/** Load Address Modal Box Content **/
		function wps_load_address_form() {
			$response = array();
			$address_id = ( !empty( $_POST['address_id']) ) ? wpshop_tools::varSanitizer( $_POST['address_id' ]) : '';
			$address_type_id = ( !empty( $_POST['address_type_id']) ) ? wpshop_tools::varSanitizer( $_POST['address_type_id']) : '';
			$response  = '<div id="wps_address_error_container"></div>';
			$response .= '<form id="wps_address_form_save" action="' .admin_url('admin-ajax.php'). '" method="post">';
			$response .= '<input type="hidden" name="action" value="wps_save_address" />';
			if ( !empty($address_id) ) {
				$address_type = get_post_meta( $address_id, '_wpshop_address_attribute_set_id', true); 
				$response .= self::display_form_fields($address_type, $address_id);
				$title = __('Edit your address', 'wpshop');
			}
			elseif($address_type_id) {
				$response .= self::display_form_fields($address_type_id);
				$title = __('Add a new address', 'wpshop');
			}
			$response .= '<input type="button" class="wps-bton wps-bton-prim" id="wps_submit_address_form" value="' .__('Save', 'wpshop'). '"/>';
			$response .= '</form>';
			echo json_encode( array($response, $title) );
			die();
		}
		
		/** Ajax Function for save address **/
		function wps_save_address() {
			global $wpshop;
			$status = false; $result = '';
			foreach ( $_POST['attribute'] as $id_group => $attribute_group ) {
				$group = wps_address::get_addresss_form_fields_by_type ($id_group);
				foreach ( $group as $attribute_sets ) {
					foreach ( $attribute_sets as $attribute_set_field ) {
						$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'][$id_group], 'address_edition');
					}
					if ( $validate ) {
						self::save_address_infos( $id_group );
						$status = true;
					}
					else {
						if ( !empty($wpshop->errors) ){
							$result = '<div class="wps-alert wps-alert-error">' .__('Some errors have been detected', 'wpshop') . ' : <ul>';
							foreach(  $wpshop->errors as $error ){
								$result .= '<li>' .$error. '</li>';
							}
							$result .= '</div>';
						}
					}
				}
			}
			echo json_encode( array( $status, $result ) );
			die();
		}
		
		
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
		
		function wps_save_first_address() {
			global $wpshop;
			$errors = '';
			$status = false; $result = ''; $validate_address_2 = true;
			$shipping_address_option = get_option( 'wpshop_shipping_address_choice' );
			$billing_address_option = get_option( 'wpshop_billing_address' );
			/** Validate Shipping address **/
			$group = wps_address::get_addresss_form_fields_by_type ( $shipping_address_option['choice'] );
			foreach ( $group as $attribute_sets ) {
				foreach ( $attribute_sets as $attribute_set_field ) {
					$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'][$shipping_address_option['choice']], 'address_edition');
				}
			}
			
			if ( empty($_POST['shiptobilling']) ) {
				$group = wps_address::get_addresss_form_fields_by_type ( $billing_address_option['choice'] );
				foreach ( $group as $attribute_sets ) {
					foreach ( $attribute_sets as $attribute_set_field ) {
						$validate_address_2 = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'][$billing_address_option['choice']], 'address_edition');
					}
				}
			}
			
			if ( $validate && $validate_address_2) {
				$return = wps_address::save_address_infos( $shipping_address_option['choice'] );
				if( !empty($return) && !empty($return['current_id']) ) {
					$_SESSION['shipping_address'] = $return['current_id'];
				}
				if ( !empty( $_POST['shiptobilling']) ) {
					self::same_shipping_as_billing($_POST['billing_address'], $_POST['shipping_address']);
					$return = wps_address::save_address_infos( $billing_address_option['choice'] );
					if( !empty($return) && !empty($return['current_id']) ) {
						$_SESSION['billing_address'] = $return['current_id'];
					}
				}
				else {
					$return = wps_address::save_address_infos( $billing_address_option['choice'] );
					if( !empty($return) && !empty($return['current_id']) ) {
						$_SESSION['billing_address'] = $return['current_id'];
					}
				}
				$status = true;
				$result = self::get_addresses();
			}
			else {
				if ( !empty($wpshop->errors) ){
					$result = '<div class="wps-alert wps-alert-error">' .__('Some errors have been detected', 'wpshop') . ' : <ul>';
					foreach(  $wpshop->errors as $error ){
						$result .= '<li>' .$error. '</li>';
					}
					$result .= '</div>';
				}
			}
			
			$response = array( $status, $result );
			echo json_encode( $response );
			die();
		}
		
		
		/** Treat the differents fields of form and classified them by form
	 	* @return boolean
		 */
		function save_address_infos( $attribute_set_id ) {	
			global $wpdb;
			$current_item_edited = !empty($_POST['attribute'][$attribute_set_id]['item_id']) ? (int)wpshop_tools::varSanitizer($_POST['attribute'][$attribute_set_id]['item_id']) : null;
			// Create or update the post address
			$post_parent = '';
			$post_author = get_current_user_id();
			if ( !empty($_REQUEST['user']['customer_id']) ) {
				$post_parent = $_REQUEST['user']['customer_id'];
				$post_author = $_REQUEST['user']['customer_id'];
			}
			elseif ( !empty($_REQUEST['post_ID']) ) {
				$post_parent = $_REQUEST['post_ID'];
			}
			else {
				$post_parent = get_current_user_id();
			}
			$post_address = array(
				'post_author' => $post_author,
				'post_title' => !empty($_POST['attribute'][$attribute_set_id]['varchar']['address_title']) ? $_POST['attribute'][$attribute_set_id]['varchar']['address_title'] : '',
				'post_status' => 'draft',
				'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
				'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
				'post_parent'=>	$post_parent
			);
			$_POST['edit_other_thing'] = true;
	
			if ( empty($current_item_edited) && (empty($_POST['current_attribute_set_id']) || $_POST['current_attribute_set_id'] != $attribute_set_id )) {
				$current_item_edited = wp_insert_post( $post_address );
				if ( is_admin()) {
					$_POST['attribute'][$attribute_set_id]['item_id'] = $current_item_edited;
				}
			}
			else {
				$post_address['ID'] = $current_item_edited;
				wp_update_post( $post_address );
			}
	
			//Update the post_meta of address
			update_post_meta($current_item_edited, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, $attribute_set_id);
	
			foreach ( $_POST['attribute'][ $attribute_set_id ] as $type => $type_content) {
				$attribute_not_to_do = array();
				if (is_array($type_content) ) {
					foreach ( $type_content as $code => $value) {
						$attribute_def = wpshop_attributes::getElement($code, "'valid'", 'code');
						if ( !empty($attribute_def->_need_verification) && $attribute_def->_need_verification == 'yes' ) {
							$code_verif = $code.'2';
							$attribute_not_to_do[] = $code_verif;
							if ( !empty($attributes[$code_verif] )) {
								unset($attributes[$code_verif]);
							}
						}
						if( !in_array($code, $attribute_not_to_do)) $attributes[$code] = $value;
					}
				}
			}
	
			//GPS coord
			$address = (!empty($attributes) ) ? $attributes['address']. ' ' .$attributes['postcode']. ' ' .$attributes['city'] : '';
			$gps_coord = '';//wps_google_map::return_coord_from_address($address);
			$attributes['longitude'] = ( !empty($gps_coord['lng']) ) ? $gps_coord['lng'] : '';
			$attributes['latitude'] = ( !empty($gps_coord['lat']) ) ? $gps_coord['lat'] : '';
	
			$result = wpshop_attributes::setAttributesValuesForItem($current_item_edited, $attributes, false, '');
			$result['current_id'] = $current_item_edited;
			
			return $result;
		}
		
		/**
		 * Display the differents forms fields
		 * @param string $type : Type of address
		 * @param string $first : Customer first address ?
		 * @param string $referer : Referer website page
		 * @param string $admin : Display this form in admin panel
		 */
		function display_form_fields($type, $id = '', $first = '', $referer = '', $special_values = array(), $options = array(), $display_for_admin = array() ) {
			global $wpshop, $wpshop_form, $wpdb;
			$choosen_address = get_option('wpshop_billing_address');
			$output_form_fields = '';
		
			if ( empty($type) ) {
				$type = $choosen_address['choice'];
			}
			$result = wps_address::get_addresss_form_fields_by_type($type, $id);

		
			$form = $result[$type];
			// Take the post id to make the link with the post meta of  address
			$values = array();
			// take the address informations
			$current_item_edited = !empty($id) ? (int)wpshop_tools::varSanitizer($id) : null;
		
			foreach ( $form as $group_id => $group_fields) {
				if ( empty($options) || (!empty($options) && ($options['title']))) $output_form_fields .= '<h2>'.$group_fields['name'].'</h2>';
				foreach ( $group_fields['content'] as $key => $field) {
					if ( empty($options['field_to_hide']) || !is_array($options['field_to_hide']) || !in_array( $key, $options['field_to_hide'] ) ) {
						$attributeInputDomain = 'attribute[' . $type . '][' . $field['data_type'] . ']';
						// Test if there is POST var or if user have already fill his address infos and fill the fields with these infos
						if( !empty($_POST) ) {
							$referer = !empty($_POST['referer']) ? $_POST['referer'] : '';
							if ( !empty($form['id']) && !empty($field['name']) && isset($_POST[$form['id']."_".$field['name']]) ) {
								$value = $_POST[$form['id']."_".$field['name']];
							}
						}
		
		
		
						// Fill Automaticly some fields when it's an address creation
						if ( !is_admin() && !empty($_GET['action']) && $_GET['action'] == 'add_address' ) {
		
							switch ( $field['name']) {
								case 'address_title' :
									$field['value'] = ( $type == $choosen_address['choice'] ) ? __('Billing address', 'wpshop') : __('Shipping address', 'wpshop');
									break;
								case 'address_last_name' :
									$usermeta_last_name = get_user_meta( get_current_user_id(), 'last_name', true);
									$field['value'] = ( !empty($usermeta_last_name) ) ? $usermeta_last_name :  '';
									break;
								case 'address_first_name' :
									$usermeta_first_name = get_user_meta( get_current_user_id(), 'first_name', true);
									$field['value'] = ( !empty($usermeta_first_name) ) ? $usermeta_first_name :  '';
									break;
								case 'address_user_email' :
									$user_infos = get_userdata( get_current_user_id() );
									$field['value'] = ( !empty($user_infos) && !empty($user_infos->user_email) ) ? $user_infos->user_email :  '';
									break;
								default :
									$field['value'] = '';
									break;
							}
		
						}
		
						/** Fill fields if $_POST exist **/
						if ( !empty( $_POST['attribute'][$type][$field['data_type']][$field['name']] ) ) {
							$field['value'] = $_POST['attribute'][$type][$field['data_type']][$field['name']];
						}
		

						if( $field['name'] == 'address_title' && !empty($first) && $type == __('Billing address', 'wpshop') ) {
							$value = __('Billing address', 'wpshop');
						}
						elseif( $field['name'] == 'address_title' && !empty($first) && $type == __('Shipping address', 'wpshop') ) {
							$value = __('Shipping address', 'wpshop');
						}
		
						if ( !empty($special_values[$field['name']]) ) {
							$field['value'] = $special_values[$field['name']];
						}
		
						$template = 'wpshop_account_form_input';
						if ( $field['type'] == 'hidden' ) {
							$template = 'wpshop_account_form_hidden_input';
						}
		
						if ( $field['frontend_verification'] == 'country' ) {
							$field['type'] = 'select';
							$possible_values = array_merge(array('' => __('Choose a country')), unserialize(WPSHOP_COUNTRY_LIST));
							$field['possible_value'] = $possible_values;
							$field['valueToPut'] = 'index';
						}
		

						
						$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
						$input_tpl_component = array();

						//$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
						$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = ( $field['type'] != 'hidden' ) ? $field['label'] . ( ( $field['required'] == 'yes' ) ? ' <span class="required">*</span>' : '') : '';
						$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL_OPTIONS'] = ' for="' . $field['id'] . '"';
						$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain);
						//$output_form_fields .= wpshop_display::display_template_element($template, $input_tpl_component);
						
						
						$output_form_fields .= wpshop_display::display_template_element('wps_address_field', $input_tpl_component, array(), 'wpshop');
						
						
						unset($input_tpl_component);
		
						if ( $field['_need_verification'] == 'yes' && !is_admin() ) {
							$field['name'] = $field['name'] . '2';
							$field['id'] = $field['id'] . '2';
							$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
							$input_tpl_component = array();
							$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = $field['label'] . ( ( ($field['required'] == 'yes' && !is_admin()) || ($field['name'] == 'address_user_email' && is_admin()) ) ? ' <span class="required">*</span>' : '');
							$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL_OPTIONS'] = ' for="' . $field['id'] . '"';
							$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
							$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = sprintf( __('Confirm %s', 'wpshop'), strtolower($field['label']) ). ( ($field['required'] == 'yes') && !is_admin() ? ' <span class="required">*</span>' : '');
							$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain) . $field['options'];
							//$output_form_fields .= wpshop_display::display_template_element($template, $input_tpl_component);
							$output_form_fields .= wpshop_display::display_template_element('wps_address_field', $input_tpl_component, array(), 'wpshop');
							unset($input_tpl_component);
						}
					}
				}
			}
		
			if ( $type ==  $choosen_address['choice'] ) {
				$output_form_fields .= '<input type="hidden" name="billing_address" value="'.$choosen_address['choice'].'" />';
			}
			$shipping_address_options = get_option('wpshop_shipping_address_choice');
			if ( $type ==  $shipping_address_options['choice'] ) {
				$output_form_fields .= '<input type="hidden" name="shipping_address" value="' .$shipping_address_options['choice']. '" />';
			}
			$output_form_fields .= '<input type="hidden" name="edit_other_thing" value="'.false.'" /><input type="hidden" name="referer" value="'.$referer.'" />
								<input type="hidden" name="type_of_form" value="' .$type. '" /><input type="hidden" name="attribute[' .$type. '][item_id]" value="' .$current_item_edited. '" />';
		
			if ( !is_admin() && empty($first) ) $output_form_fields = wpshop_display::display_template_element('wpshop_customer_addresses_form', array('CUSTOMER_ADDRESSES_FORM_CONTENT' => $output_form_fields, 'CUSTOMER_ADDRESSES_FORM_BUTTONS' => '<input type="submit" name="submitbillingAndShippingInfo" value="' . __('Save','wpshop') . '" />'));
			return $output_form_fields;
		}
		
		function same_shipping_as_billing($billing_address_id, $shipping_address_id) {
				if ( !empty($_POST) ) {
					$tableauGeneral =  $_POST;
				}
				else {
					$tableauGeneral = $_REQUEST;
				}
			
				// Create an array with the shipping address fields definition
				$shipping_fields = array();
				foreach ($tableauGeneral['attribute'][$billing_address_id] as $key=>$attribute_group ) {
					if ( is_array($attribute_group) ) {
						foreach( $attribute_group as $field_name=>$value ) {
							$shipping_fields[] =  $field_name;
						}
					}
				}
				// Test if the billing address field exist in shipping form
				foreach ($tableauGeneral['attribute'][$shipping_address_id] as $key=>$attribute_group ) {
					if (is_array($attribute_group) ) {
						foreach( $attribute_group as $field_name=>$value ) {
							if ( in_array($field_name, $shipping_fields) ) {
								if ($field_name == 'address_title') {
									$tableauGeneral['attribute'][$billing_address_id][$key][$field_name] = __('Billing address', 'wpshop');
								}
								else {
									$tableauGeneral['attribute'][$billing_address_id][$key][$field_name] = $tableauGeneral['attribute'][$shipping_address_id][$key][$field_name];
								}
							}
						}
					}
				}
			
				foreach ( $tableauGeneral as $key=>$value ) {
					if ( !empty($_POST) ) {
						$_POST[$key] = $value;
					}
					else {
						$_REQUEST[$key] = $value;
					}
				}
			
		}
		
		function get_shipping_address_summary() {
			$output = '';
			if ( !empty( $_SESSION['shipping_address']) ){
				$address_infos = get_post_meta($_SESSION['shipping_address'], '_wpshop_address_metadata', true);
				$tpl_component['ADDRESS'] = self::display_an_address( $address_infos );
				$tpl_component['TITLE'] = __('Shipping informations', 'wpshop');
				$tpl_component['ADDRESS_ID'] = $_SESSION['shipping_address'];
				$output = wpshop_display::display_template_element('wps_shipping_address_summary', $tpl_component, array(), 'wpshop');
				unset( $tpl_component );
			}
			return $output;
		}
		
		function get_billing_address_summary() {
			$output = '';
			if ( !empty( $_SESSION['billing_address']) ){
				$address_infos = get_post_meta($_SESSION['billing_address'], '_wpshop_address_metadata', true);
				$tpl_component['ADDRESS'] = self::display_an_address( $address_infos );
				$tpl_component['TITLE'] = __('Billing informations', 'wpshop');
				$tpl_component['ADDRESS_ID'] = $_SESSION['billing_address'];
				$output = wpshop_display::display_template_element('wps_shipping_address_summary', $tpl_component, array(), 'wpshop');
				unset( $tpl_component );
			}
			return $output;
		}
		
		
	}
}
if ( class_exists("wps_address") ) {
	$wps_address = new wps_address();
}
