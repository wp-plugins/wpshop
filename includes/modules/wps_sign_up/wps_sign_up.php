<?php
/**
 * Plugin Name: WPShop Sign Up
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description:  WPShop Sign Up
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WpShop  WPShop Sign Up bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
 
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wps_sign_up") ) {
	class wps_sign_up {
		var $signup_fields = array();
		
		
		function __construct() {
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'wps_sign_up_js', plugins_url('templates/wpshop/js/wps_sign_up.js', __FILE__) );
			
			add_shortcode( 'wpshop_sign_up' , array( &$this, 'get_sign_up_form') );
			
			add_action('wp_ajax_wps_display_sign_up_form', array( &$this, 'wps_ajax_get_sign_up_form'));
			add_action('wp_ajax_nopriv_wps_display_sign_up_form', array( &$this, 'wps_ajax_get_sign_up_form'));
			
			add_action( 'wp_ajax_wps_save_account_form', array( &$this, 'wps_save_account_form' ) );
			add_action( 'wp_ajax_nopriv_wps_save_account_form', array( &$this, 'wps_save_account_form' ) );
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
		

		/** Display Sign Up Form **/
		function get_sign_up_form() {
			global $wpdb; 
			$output = $form_fields = $additionnal_form_fields = '';
			$tpl_component = array();
			$tpl_component['SIGN_UP_INTERFACE'] = self::sign_up_interface();
			$output = wpshop_display::display_template_element('wps_sign_up_form', $tpl_component, array(), 'wpshop');
			unset( $tpl_component );
			return $output;
		}
	
		function sign_up_interface() {
			global $wpdb;
			$output = $form_fields = $additionnal_form_fields = '';
				
			$password_attribute = $signup_form_attributes =  array();
				
			$entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
				
			$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_SET.' WHERE entity_id = %d', $entity_id);
			$customer_entity_id = $wpdb->get_var( $query );
			$attributes_set = wpshop_attributes_set::getElement($customer_entity_id);
			$account_attributes = wpshop_attributes_set::getAttributeSetDetails( ( !empty($attributes_set->id) ) ? $attributes_set->id : '', "'valid'");
			$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_GROUP.' WHERE attribute_set_id = %d', $attributes_set->id );
			$customer_attributes_sections = $wpdb->get_results( $query );
			foreach( $customer_attributes_sections as $k => $customer_attributes_section ) {
				foreach( $account_attributes[$customer_attributes_section->id]['attribut'] as $attribute ) {
					//$signup_form_attributes[] = $attribute;
					$signup_fields[] = $attribute;
				}
			}
				
			$tpl_component = array();
			$tpl_component['WPS_LOGIN_ALERT_MESSAGE'] = self::control_sign_up_form_request();
			foreach( $signup_fields as $signup_form_attribute ) {
				$value = ( !empty($signup_form_attribute->frontend_input) && $signup_form_attribute->frontend_input != 'password' && !empty($_POST) && !empty($_POST['attribute']) && !empty($_POST['attribute'][$signup_form_attribute->data_type]) && !empty( $_POST['attribute'][$signup_form_attribute->data_type][$signup_form_attribute->code]) ) ? $_POST['attribute'][$signup_form_attribute->data_type][$signup_form_attribute->code] : '';
				$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $signup_form_attribute, $value, array() );
				$sub_tpl_component['SIGNUP_FORM_LABEL'] = $attribute_output_def['label'];
				$sub_tpl_component['SIGNUP_FORM_FIELD'] = $attribute_output_def['output'];
				$sub_tpl_component['SIGNUP_FORM_LABEL_FOR'] = $attribute_output_def['label_pointer'];
				if ( !empty($signup_form_attribute->frontend_input) && $signup_form_attribute->frontend_input != 'password' ) {
					$form_fields .=  wpshop_display::display_template_element('wps_sign_up_form_field', $sub_tpl_component, array(), 'wpshop');
				}
				else {
					$additionnal_form_fields .= wpshop_display::display_template_element('wps_sign_up_form_field', $sub_tpl_component, array(), 'wpshop');
				}

			}
				
				
			$tpl_component['SIGN_UP_FORM_FIELDS'] = $form_fields;
			$tpl_component['SIGN_UP_ADDITIONNAL_FIELDS'] = $additionnal_form_fields;
			$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
			$output = wpshop_display::display_template_element('wps_sign_up_form_interface', $tpl_component, array(), 'wpshop');
			return $output;
		}
		
		function wps_ajax_get_sign_up_form() {
			$response = array( 'status' => true, 'response' => self::sign_up_interface());
			echo json_encode( $response );
			die();
		}
		
		
		function control_sign_up_form_request() {
			$output = '';
			
			if ( !empty( $_POST['wps_sign_up_request']) ) {
				$save_account = wpshop_account::save_account_form();
				if ( empty($save_account) ){
					 
				}
				
			}
			return $output;
		}
		
		function wps_save_account_form() {
			global $wpdb; global $wpshop;
			$user_id = get_current_user_id();
			$status = $account_creation = false; $result = '';
			$exclude_user_meta = array( 'user_email', 'user_pass' );
			$element_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
			if ( !empty( $element_id) ){
				$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d', $element_id );
				$attribute_set_id = $wpdb->get_var( $query );
				if ( !empty($attribute_set_id) ){
					$group  = wps_address::get_addresss_form_fields_by_type( $attribute_set_id );
					foreach ( $group as $attribute_sets ) {
						foreach ( $attribute_sets as $attribute_set_field ) {
							$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'] );
						}
						if ( empty($wpshop->errors) ) {
							$user_name = !empty($_POST['attribute']['varchar']['user_login']) ? $_POST['attribute']['varchar']['user_login'] : $_POST['attribute']['varchar']['user_email'];
							$user_pass = ( !empty($_POST['attribute']['varchar']['user_pass']) && !empty($_POST['wps_signup_account_creation']) ) ? $_POST['attribute']['varchar']['user_pass'] : wp_generate_password( 12, false );
							
							if ( $user_id == 0  ) {
								$user_id = wp_create_user($user_name, $user_pass, $_POST['attribute']['varchar']['user_email']);
								if ( !is_object( $user_id) ) {
									$account_creation = true;
								}
							}
							
							
							
							foreach( $attribute_set_field['content'] as $attribute ) {
								if ( !in_array( $attribute['name'], $exclude_user_meta ) ) {
									update_user_meta( $user_id, $attribute['name'], wpshop_tools::varSanitizer( $_POST['attribute'][$attribute['data_type']][$attribute['name']])  );
								}
								else {
									wp_update_user( array('ID' => $user_id, $attribute['name'] => wpshop_tools::varSanitizer( $_POST['attribute'][$attribute['data_type']][$attribute['name']]) ) );
								}
							}
							
							$permalink_option = get_option( 'permalink_structure' );
							$result = get_permalink( get_option('wpshop_checkout_page_id') ).( (!empty($permalink_option) ) ? '?order_step=2' : '&order_step=2' ) ;
							$status = true;
							
							if ( $account_creation ) {
								$secure_cookie = is_ssl() ? true : false;
								wp_set_auth_cookie($user_id, true, $secure_cookie);
								
								wpshop_messages::wpshop_prepared_email($_POST['attribute']['varchar']['user_email'], 'WPSHOP_SIGNUP_MESSAGE', array('customer_first_name' => ( !empty($_POST['attribute']['varchar']['first_name']) ) ? $_POST['attribute']['varchar']['first_name'] : '', 'customer_last_name' => ( !empty($_POST['attribute']['varchar']['last_name']) ) ? $_POST['attribute']['varchar']['last_name'] : '', 'customer_user_email' => ( !empty($_POST['attribute']['varchar']['user_email']) ) ? $_POST['attribute']['varchar']['user_email'] : '') );
							}
							
						}
						else {
							$result = '<div class="wps-alert wps-alert-error">' .__('Some errors have been detected', 'wpshop') . ' : <ul>';
							foreach(  $wpshop->errors as $error ){
								$result .= '<li>' .$error. '</li>';
							}
							$result .= '</div>';
						}
					}
					
				}
			}
			echo json_encode( array( $status, $result) );
			die();
		}
	
	}
}
if ( class_exists("wps_sign_up") ) {
	$wps_forgot_password = new wps_sign_up();
}