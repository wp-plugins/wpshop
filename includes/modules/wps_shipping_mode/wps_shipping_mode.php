<?php
/**
 * Plugin Name: WP Shop Shipping Mode
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WP Shop Shipping Mode
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * Cart rules bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
 
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

if ( !class_exists("wps_shipping_mode") ) {
	class wps_shipping_mode {
		function __construct() {
			self::migrate_default_shipping_mode();
			
			wp_enqueue_script('jquery-ui-sortable'); 
			
			add_thickbox();
			/** Template Load **/
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			/** Create Options **/
			//add_action('admin_init', array(&$this, 'create_options') );
			
			/** Load Admin JS Scripts & CSS Stylesheet **/
			if ( is_admin() ) {
				wp_register_style( 'wps_shipping_mode_css', plugins_url('templates/backend/css/wps_shipping_mode.css', __FILE__) );
				wp_enqueue_style( 'wps_shipping_mode_css' );
				wp_enqueue_script('jquery');
				wp_enqueue_script( 'wps_shipping_mode_js', plugins_url('templates/backend/js/wps_shipping_mode.js', __FILE__) );
			}
			else {
				wp_register_style( 'wps_shipping_mode_css', plugins_url('templates/wpshop/css/wps_shipping_mode.css', __FILE__) );
				wp_enqueue_style( 'wps_shipping_mode_css' );
			}
			/**	Add module option to wpshop general options	*/
			add_filter('wpshop_options', array(&$this, 'add_options'), 9);
			add_action('wsphop_options', array(&$this, 'create_options'), 8);
			
			
			/** Ajax Actions **/
			add_action('wp_ajax_save_shipping_rule',array(&$this, 'wpshop_ajax_save_shipping_rule'));
			add_action('wp_ajax_display_shipping_rules',array(&$this, 'wpshop_ajax_display_shipping_rules'));
			add_action('wp_ajax_delete_shipping_rule',array(&$this, 'wpshop_ajax_delete_shipping_rule'));
			add_action('wp_ajax_add_shipping_mode',array(&$this, 'wpshop_ajax_add_shipping_mode'));
			add_action('wp_ajax_wps_reload_shipping_mode',array(&$this, 'wps_reload_shipping_mode'));
			add_action('wp_ajax_wps_calculate_shipping_cost',array(&$this, 'wps_calculate_shipping_cost'));
			
			
			add_shortcode( 'wps_shipping_mode', array( &$this, 'display_shipping_mode') );
		}
		
		/**
		 * Declare option groups for the module
		 */
		function add_options( $option_group ) {
			$option_group['wpshop_shipping_option']['subgroups']['wps_shipping_mode']['class'] = ' wpshop_admin_box_options_shipping_mode';
			return $option_group;
		}
		
		/** Load module/addon automatically to existing template list
		 *
		 * @param array $templates The current template definition
		 *
		 * @return array The template with new elements
		 */
		function custom_template_load( $templates ) {
			include('templates/backend/main_elements.tpl.php');
			include('templates/wpshop/main_elements.tpl.php');
			$templates = wpshop_display::add_modules_template_to_internal( $tpl_element, $templates );
			unset($tpl_element);
		
			return $templates;
		}
		
		/** Create Options ***/
		function create_options() {
			add_settings_section('wps_shipping_mode', __('Shipping method', 'wpshop'), '', 'wps_shipping_mode');
			
			register_setting('wpshop_options', 'wps_shipping_mode', array(&$this, 'wpshop_options_validate_wps_shipping_mode'));
			add_settings_field('wps_shipping_mode', __('Shipping Mode', 'wpshop'), array(&$this, 'display_shipping_mode_in_admin'), 'wps_shipping_mode', 'wps_shipping_mode');
			
		}
		
		/** Display the Admin Interface for Shipping Mode **/
		function display_shipping_mode_in_admin() {
			$shipping_mode_option = get_option( 'wps_shipping_mode' );
			
			$tpl_component = array();
			$tpl_component['INTERFACES'] = '';
			$tpl_component['LOADER_ICON'] = WPSHOP_LOADING_ICON;
			if( !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) ){
				
				foreach( $shipping_mode_option['modes'] as $key => $shipping_mode ) {
					$tpl_component['INTERFACES'] .= self::generate_shipping_mode_interface( $key, $shipping_mode );

				}
			}
			
			$output = wpshop_display::display_template_element('wps_shipping_mode_main', $tpl_component, array(), 'admin');
			unset( $tpl_component );
			echo $output;
		}
		
		function generate_shipping_mode_interface( $key, $shipping_mode ) {
			global $wpdb;
			$tpl_component = array();
			
			$shipping_mode_option = get_option( 'wps_shipping_mode');
			$default_shipping_mode = !empty( $shipping_mode_option['default_choice'] ) ? $shipping_mode_option['default_choice'] : '';

			$countries = unserialize(WPSHOP_COUNTRY_LIST);
				
			/** Default Weight Unity **/
			$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
			$query = $wpdb->prepare('SELECT name FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
			$unity = $wpdb->get_var( $query );
			
			
			$sub_tpl_component = $tpl_thickbox_content =  array();
			
			$tpl_thickbox_content['LOADER_ICON'] = WPSHOP_LOADING_ICON;
			
			$sub_tpl_component['DEFAULT_SHIPPING_MODE_ACTIVE'] = ( !empty($default_shipping_mode) && $default_shipping_mode == $key ) ? 'checked="checked"' : '';
			$sub_tpl_component['SHIPPING_MODE_ACTIVE'] = ( !empty( $shipping_mode) && !empty($shipping_mode['active']) ) ? 'checked="checked"' : '';
			$sub_tpl_component['SHIPPING_MODE_NAME'] = $shipping_mode['name'];
			$sub_tpl_component['SHIPPING_MODE_ID'] = $tpl_thickbox_content['SHIPPING_MODE_ID'] = $key;
			$sub_tpl_component['SHIPPING_MODE_THUMBNAIL'] = ( !empty($shipping_mode['logo']) ) ? wp_get_attachment_image( $shipping_mode['logo'], 'thumbnail', false, array('class' => 'wps_shipping_mode_logo') ) : '';
			$sub_tpl_component['SHIPPING_MODE_LOGO_POST_ID'] = ( !empty($shipping_mode['logo']) ) ? $shipping_mode['logo'] : '';
			
			
			$tpl_thickbox_content['EXTRA_CONTENT'] = apply_filters('wps_shipping_mode_config_extra_params_'.$key, $key );
			
			/** Free From Config **/
			$tpl_thickbox_content['EXPLANATION'] = !empty($shipping_mode['explanation']) ? $shipping_mode['explanation'] : '';
			$tpl_thickbox_content['FREE_FROM_VALUE'] = !empty($shipping_mode['free_from']) ? $shipping_mode['free_from'] : '';
			$tpl_thickbox_content['FREE_SHIPPING'] = !empty($shipping_mode['free_shipping']) ? 'checked="checked"' : '';
				
			/** Min-Max Config **/
			$tpl_thickbox_content['MIN_MAX_ACTIVATE'] = (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['activate']) ) ? 'checked="checked"' : '';
			$tpl_thickbox_content['ADDITIONNAL_CLASS'] = (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['activate']) ) ? '' : 'wpshopHide';
			$tpl_thickbox_content['MIN_VALUE'] = (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['min']) ) ? $shipping_mode['min_max']['min'] : '';
			$tpl_thickbox_content['MAX_VALUE'] = (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['max']) ) ? $shipping_mode['min_max']['max'] : '';
				
			/** Shipping Limit destination Configuration **/
			$tpl_thickbox_content['COUNTRIES_LIST'] = '';
			if( !empty($countries) ) {
				foreach( $countries as $key => $country) {
			
					$tpl_thickbox_content['COUNTRIES_LIST'] .= '<option value="' .$key. '"' . ( (!empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['country']) && in_array($key, $shipping_mode['limit_destination']['country']) ) ? 'selected="selected"' : '' ) .'>' .$country. '</option>';
				}
			}
				
				
			/** Custom Shipping Rules COnfiguration **/
			$tpl_thickbox_content['CUSTOM_SHIPPING_FEES_DATA'] = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) ) ? $shipping_mode['custom_shipping_rules'] : '';
			$tpl_thickbox_content['CUSTOM_SHIPPING_RULES_ACTIVE'] = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['active']) ) ? 'checked="checked"' : '';
			$tpl_thickbox_content['CUSTOM_SHIPPING_ACTIVE_CP'] = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['active_cp']) ) ? 'checked="checked"' : '';
			$tpl_thickbox_content['CUSTOM_SHIPPING_ACTIVE_DEPARTMENT'] = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['active_department']) ) ? 'checked="checked"' : '';
				
				
			$tpl_thickbox_content['SHIPPING_WEIGHT_UNITY'] = __($unity, 'wpshop');
				
			$tpl_thickbox_content['CUSTOM_SHIPPING_COUNTRY_LIST'] = '';
			if( !empty($countries) ) {
				foreach( $countries as $key => $country) {
					$tpl_thickbox_content['CUSTOM_SHIPPING_COUNTRY_LIST'] .= '<option value="' .$key. '">' .$country. '</option>';
				}
			}

			$tpl_thickbox_content['SHIPPING_MODE_POSTCODE_LIMIT_DESTINATION'] = ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['postcode']) ) ? $shipping_mode['limit_destination']['postcode'] : '';
			
			$fees_data = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['fees']) ) ? $shipping_mode['custom_shipping_rules']['fees'] : array();
			if(is_array($fees_data)) {
				$fees_data = wpshop_shipping::shipping_fees_array_2_string($fees_data);
			}
			$tpl_thickbox_content['CUSTOM_SHIPPING_FEES_DATA'] = $fees_data;
			$tpl_thickbox_content['CUSTOM_SHIPPING_RULES_DISPLAY'] = self::generate_shipping_rules_table( $fees_data, $tpl_thickbox_content['SHIPPING_MODE_ID'] );
				
			$sub_tpl_component['SHIPPING_MODE_CONFIGURATION_INTERFACE'] = wpshop_display::display_template_element('wps_shipping_mode_configuration_interface', $tpl_thickbox_content, array(), 'admin');
			unset( $tpl_thickbox_content );
			$output = wpshop_display::display_template_element('wps_shipping_mode_each_interface', $sub_tpl_component, array(), 'admin');
			unset( $sub_tpl_component );
			return $output;
		}

		
		/** Option Validator **/
		function wpshop_options_validate_wps_shipping_mode( $input ) {
			if ( !empty($input['modes']) ) {
				foreach( $input['modes'] as $mode => $mode_det ) {
					/** Custom Shipping rules **/
					$input['modes'][$mode]['custom_shipping_rules']['fees'] = wpshop_shipping::shipping_fees_string_2_array( $input['modes'][$mode]['custom_shipping_rules']['fees'] );
					
					/** Shipping Modes Logo Treatment **/
					if ( !empty($_FILES[$mode.'_logo']['name']) && empty($_FILES[$mode.'_logo']['error']) ) {
						$filename = $_FILES[$mode.'_logo'];
						$upload  = wp_handle_upload($filename, array('test_form' => false));
						$wp_filetype = wp_check_filetype(basename($filename['name']), null );
						$wp_upload_dir = wp_upload_dir();
						$attachment = array(
								'guid' => $wp_upload_dir['url'] . '/' . basename( $filename['name'] ),
								'post_mime_type' => $wp_filetype['type'],
								'post_title' => preg_replace(' /\.[^.]+$/', '', basename($filename['name'])),
								'post_content' => '',
								'post_status' => 'inherit'
						);
						$attach_id = wp_insert_attachment( $attachment, $upload['file']);
						require_once(ABSPATH . 'wp-admin/includes/image.php');
						$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
						wp_update_attachment_metadata( $attach_id, $attach_data );
						
						$input['modes'][$mode]['logo'] = $attach_id;
					}
				}
			}
			return $input;
		}
		
		/** Migrate Old Shipping Mode to the new **/
		function migrate_default_shipping_mode() {
			$data = array();
			$shipping_mode_option = get_option( 'wps_shipping_mode' );
			if ( empty($shipping_mode_option) ) {
				$data['modes']['default_shipping_mode']['active'] = 'on';
				$data['modes']['default_shipping_mode']['name'] = __('Home Delivery', 'wpshop');
				/** Check CUstom Shipping **/
				$custom_shipping_option = get_option( 'wpshop_custom_shipping' );
				if ( !empty($custom_shipping_option) ) {
					$data['modes']['default_shipping_mode']['custom_shipping_rules'] = $custom_shipping_option;
				}
				/** Check Country Limit **/
				$limit_destination = get_option( 'wpshop_limit_shipping_destination' );
				if ( !empty($custom_shipping_option) ) {
					$data['modes']['default_shipping_mode']['limit_destination'] = $limit_destination;
				}
				
				/** Check Others shipping configurations **/
				$wpshop_shipping_rules_option = get_option('wpshop_shipping_rules');
				if ( !empty($wpshop_shipping_rules_option) ){
					if ( !empty($wpshop_shipping_rules_option['min_max']) ) {
						$data['modes']['default_shipping_mode']['min_max'] = $wpshop_shipping_rules_option['min_max'];
					}
					if ( !empty($wpshop_shipping_rules_option['free_from']) ) {
						$data['modes']['default_shipping_mode']['free_from'] = $wpshop_shipping_rules_option['free_from'];
					}
					if ( !empty($wpshop_shipping_rules_option['wpshop_shipping_rule_free_shipping']) ) {
						$data['modes']['default_shipping_mode']['free_shipping'] = $wpshop_shipping_rules_option['wpshop_shipping_rule_free_shipping'];
					}
				}
				$data['default_choice'] = 'default_shipping_mode';
				
				update_option( 'wps_shipping_mode', $data );
			}
		}
		
		/** Save custom Rules **/
		function wpshop_ajax_save_shipping_rule(){
			global $wpdb;
			$status = false;
			$reponse = array();
			$fees_data = ( !empty($_POST['fees_data']) ) ?  $_POST['fees_data'] : null;
			$weight_rule = ( !empty($_POST['weight_rule']) ) ? wpshop_tools::varSanitizer( $_POST['weight_rule'] ) : null;
			$shipping_price = ( !empty($_POST['shipping_price']) ) ? wpshop_tools::varSanitizer( $_POST['shipping_price'] ) : 0;
			$selected_country = ( !empty($_POST['selected_country']) ) ? wpshop_tools::varSanitizer( $_POST['selected_country'] ) : null;
			$shipping_rules = wpshop_shipping::shipping_fees_string_2_array( stripslashes($fees_data) );
		
			$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
			$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
			$unity = $wpdb->get_var( $query );
		
			$weight_rule = ( !empty($unity) && $unity == 'kg' ) ? $weight_rule * 1000 : $weight_rule;
			//Check if this shipping rule (same country and same weight) already exist in the shipping rules definition
			if( !empty($shipping_rules) ) {
				$existing_country = false;
				$tab_key = -1;
				foreach ( $shipping_rules as $key=>$shipping_rule) {
					if ( $shipping_rule['destination'] == $selected_country) {
						$existing_country = true;
						$tab_key = $key;
					}
				}
				if ( $existing_country && $tab_key > -1) {
					$shipping_rules[$tab_key]['fees'][$weight_rule] = $shipping_price;
				}
				else {
					$shipping_rules[] = array( 'destination' => $selected_country, 'rule' => 'weight', 'fees' => array($weight_rule => $shipping_price) );
				}
				$status = true;
			}
			else {
				$shipping_rules = array( '0' => array('destination' => $selected_country, 'rule' => 'weight', 'fees' => array( $weight_rule => $shipping_price)) );
				$status = true;
			}
			$reponse = array('status' => $status, 'reponse' => wpshop_shipping::shipping_fees_array_2_string( $shipping_rules ) );
			echo json_encode($reponse);
			die();
		}

		/**
		 * Delete Custom shipping Rule
		 */
		function wpshop_ajax_delete_shipping_rule() {
		 	global $wpdb;
		 	$fees_data = ( !empty($_POST['fees_data']) ) ? $_POST['fees_data'] : null;
		 	$country_and_weight =  ( !empty($_POST['country_and_weight']) ) ? $_POST['country_and_weight'] : null;
		 	$datas = explode("|", $country_and_weight);
		 	$country = $datas[0];
		 	$weight = $datas[1];
		 	$shipping_mode_id = $datas[2];
		 	
		 	$shipping_rules = wpshop_shipping::shipping_fees_string_2_array( stripslashes($fees_data) );
		 		
		 	/** Check the default weight unity **/
		 	$weight_unity_id = get_option('wpshop_shop_default_weight_unity');
		 	if ( !empty($weight_unity_id) ) {
		 		$query = $wpdb->prepare('SELECT unit FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id=%d', $weight_unity_id);
		 		$weight_unity = $wpdb->get_var( $query );
		 		 
		 		if( $weight_unity == 'kg' ) {
		 			$weight = $weight * 1000;
		 		}
		 	}
		 
		 	if ( array_key_exists($country, $shipping_rules) ) {
		 		if ( array_key_exists((string)$weight, $shipping_rules[$country]['fees']) ) {
		 			unset($shipping_rules[$country]['fees'][$weight]);
		 		}
		 		if ( empty($shipping_rules[$country]['fees']) ) {
		 			unset($shipping_rules[$country]);
		 		}
		 
		 	}
		 	foreach ( $shipping_rules as $k=>$shipping_rule ) {
		 		if ( !isset($shipping_rule['fees']) ) {
		 			unset($shipping_rules[$k]);
		 		}
		 	}
		 
		 	$status = true;
		 	
		 	if ( !empty($shipping_rules) ) {
		 		$rules = wpshop_shipping::shipping_fees_array_2_string( $shipping_rules );
		 	}
		 	else {
		 		$rules = '';
		 	}
		 	$reponse = array('status' => $status, 'reponse' => $rules );
		 	echo json_encode($reponse);
		 	die();
		 }
		
		/**
		 * Display Created custom shipping rules
		 */ 
		function wpshop_ajax_display_shipping_rules () {
		 	$status = false;
		 	$fees_data = ( !empty($_POST['fees_data']) ) ? $_POST['fees_data'] : null;
		 	$shipping_mode_id = ( !empty($_POST['shipping_mode_id']) ) ? $_POST['shipping_mode_id'] : null;
		 	$result = '';
		 	if( !empty($fees_data) ) {
		 		$result = self::generate_shipping_rules_table( $fees_data, $shipping_mode_id );
		 		$status = true;
		 	}
		 	else {
		 		$status = true;
		 		$result = __('No shipping rules are created', 'wpshop');
		 	}
		 	
		 	echo json_encode(array('status' => $status, 'reponse' => $result));
		 	die();
		 }
		 
		 /** Add a new Shipping Rule **/
		function wpshop_ajax_add_shipping_mode() {
			$status = $code_exists = $name_exists = false;
			$result = '';
			$shipping_mode_name = ( !empty($_POST['shipping_mode_name']) ) ? wpshop_tools::varSanitizer($_POST['shipping_mode_name']) : null;
			
			
			if ( !empty($shipping_mode_name) ) {
				$shipping_mode_option = get_option( 'wps_shipping_mode' );
				$shipping_mode_code = sanitize_title( $shipping_mode_name );
				
				/** Check if a shipping mode with the same name exists **/
				if ( !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) ) {
					foreach( $shipping_mode_option['modes'] as $k => $shipping_mode ) {
						if ( $k == $shipping_mode_code ) {
							$code_exists = true;
							continue;
						}
						if( !empty($shipping_mode) && !empty($shipping_mode['name']) &&  $shipping_mode['name'] == $shipping_mode_name ) {
							$name_exists = true;
							continue;
						}
					}
					if ( $code_exists ) {
						$result = __('A shipping Mode with the same ID already exists, Please change the shipping mode name', 'wpshop'); 
					}
					if( $name_exists ) {
						$result = __('A Shipping Mode with the same name already exists, please change the shipping mode name', 'wpshop');
					}
					/** If all is OK, create the Shipping Mode **/
					if ( !$name_exists && !$code_exists ) {
						$shipping_mode = array();
						$shipping_mode['name'] = $shipping_mode_name;
						$shipping_mode['min_max'] = array();
						$shipping_mode['free_from'] = '';
						$shipping_mode['free_shipping'] = '';
						$shipping_mode['custom_shipping_rules'] = array();
						$shipping_mode['limit_destination'] = array();
						
						$result = self::generate_shipping_mode_interface($shipping_mode_code, $shipping_mode );
						$status = true;
					}
					
				}
			}
			else {
				$result = __('The "Shipping Mode Name" is required', 'wpshop' );
			}
			
			
			$response = array( 'status' => $status, 'response' => $result );
			echo json_encode( $response );
			die();
		}	
		 
		 /**
		  * Genrate the Shipping Custom rules Table
		  * @param string Rules already created (Serialized Array )
		  * @param string Shipping Mode ID
		  * @return string
		  */
		function generate_shipping_rules_table( $fees_data, $shipping_mode_id ) {
			global $wpdb;
			$result = '';
			if ( !empty( $fees_data) ) {
				$shipping_rules = wpshop_shipping::shipping_fees_string_2_array( stripslashes($fees_data) );
				$result = '';
				$tpl_component ='';
				$tpl_component['CUSTOM_SHIPPING_RULES_LINES'] = '';
				$tpl_component['SHIPPING_MODE_ID'] = $shipping_mode_id;
				$country_list = unserialize(WPSHOP_COUNTRY_LIST);
				$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
				$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
				$unity = $wpdb->get_var( $query );
				$currency_defaut_option = get_option ('wpshop_shop_default_currency');
				$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $currency_defaut_option);
				$currency = $wpdb->get_var( $query );
				if ( !empty($shipping_rules) ) {
					foreach ( $shipping_rules as $shipping_rule ) {
						$country_name = '';
						$code_country = explode('-', $shipping_rule['destination']);
						$code_country = $code_country[0];
						foreach ( $country_list as $key=>$country ) {
							if (  $key == $code_country ) {
								$country_name = $country;
							}
						}
						if ( !empty($shipping_rule['fees']) ) {
							foreach( $shipping_rule['fees'] as $k=>$fee ) {
								$tpl_line_component['SHIPPING_MODE_ID'] = $shipping_mode_id;
								$tpl_line_component['SHIPPING_RULE_DESTINATION'] = $shipping_rule['destination'];
								$tpl_line_component['SHIPPING_RULE_COUNTRY'] = $country_name;
								$tpl_line_component['SHIPPING_RULE_WEIGHT'] = ($unity == 'kg') ? $k / 1000 : $k;
								$tpl_line_component['SHIPPING_RULE_WEIGHT_UNITY'] = $unity;
								$tpl_line_component['SHIPPING_RULE_FEE'] = $fee;
								$tpl_line_component['SHIPPING_RULE_WEIGHT_CURRENCY'] = $currency;
								$tpl_line_component['MEDIAS_ICON_URL'] = WPSHOP_MEDIAS_ICON_URL;
				
								$tpl_component['CUSTOM_SHIPPING_RULES_LINES'] .= wpshop_display::display_template_element('shipping_rules_table_line', $tpl_line_component, array(), 'admin');
								unset($tpl_line_component);
							}
						}
							
					}
					$result = wpshop_display::display_template_element('shipping_rules_table', $tpl_component, array(), 'admin');
					unset($tpl_component);
				}
			}
			return $result;
		}
	
		
		function generate_shipping_mode_for_an_address() {
			$output = '';
			$status = false;
			$shipping_address_id = ( !empty($_SESSION['shipping_address']) ) ? $_SESSION['shipping_address'] : null;
			if ( !empty($shipping_address_id) ) {
				$shipping_mode_option = get_option( 'wps_shipping_mode' );
				$address_metadata = get_post_meta( $shipping_address_id, '_wpshop_address_metadata', true);
				if( !empty( $shipping_mode_option ) && !empty($shipping_mode_option['modes']) ){
					foreach( $shipping_mode_option['modes'] as $k => $shipping_mode ) {
						$tpl_component = array($shipping_mode);
						if ( !empty($shipping_mode) && !empty($shipping_mode['active']) ) {
							/** Check Country Shipping Limitation **/
							if ( empty($shipping_mode['limit_destination']) || ( !empty($shipping_mode['limit_destination']) && empty($shipping_mode['limit_destination']['country']) ) || ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['country']) && in_array($address_metadata['country'], $shipping_mode['limit_destination']['country']) ) ) { 	
								/** Check Limit Destination By Postcode **/
								$visible = true;
								
								if ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['postcode']) ) {
									$postcodes = explode(',', $shipping_mode['limit_destination']['postcode'] );
									foreach( $postcodes as $postcode_id => $postcode ) {
										$postcodes[ $postcode_id ] = trim( str_replace( ' ', '', $postcode) );
									}
									if ( !in_array($address_metadata['postcode'], $postcodes) ) {
										$visible = false;
									}
								}
								if ( $visible ) {
									$tpl_component['SHIPPING_MODE_SELECTED'] = ( !empty($shipping_mode_option) && !empty($shipping_mode_option['default_choice']) && $shipping_mode_option['default_choice'] == $k ) ? 'checked="checked"' : '';
									$tpl_component['SHIPPING_MODE_LOGO'] = !empty( $shipping_mode['logo'] ) ? wp_get_attachment_image( $shipping_mode['logo'], 'thumbnail', false, array('height' => '40') ) : ''; 
									$tpl_component['SHIPPING_METHOD_CODE'] = $k;
									$tpl_component['SHIPPING_METHOD_NAME'] = $shipping_mode['name'];
									$tpl_component['SHIPPING_METHOD_EXPLANATION'] = !empty($shipping_mode['explanation']) ? $shipping_mode['explanation'] : '';
									$tpl_component['WPS_SHIPPING_MODE_ADDITIONAL_CONTENT'] = apply_filters('wps_shipping_mode_additional_content', $k );
									if ( $tpl_component['WPS_SHIPPING_MODE_ADDITIONAL_CONTENT'] == $k ) {
										$tpl_component['WPS_SHIPPING_MODE_ADDITIONAL_CONTENT'] = '';
									}
									$tpl_component['SHIPPING_METHOD_CONTENT'] = '';
									$tpl_component['SHIPPING_METHOD_CONTAINER_CLASS'] = '';
									$output .= wpshop_display::display_template_element('shipping_mode_front_display', $tpl_component, array(), 'wpshop');
									unset( $tpl_component );
									$status = true;
								}
								else {
									$output = '<div class="error_bloc">' .__('Sorry ! You can\'t order on this shop, because we don\'t ship in your area.', 'wpshop' ). '</div>';
								}
							}
							
						}
					}
					
					if ( empty( $output) ) {
						$output = '<div class="error_bloc">' .__('Sorry ! You can\'t order on this shop, because we don\'t ship in your country.', 'wpshop' ). '</div>';
					}
				}
				else {
					$output .= __('No shipping mode are avalaible for your shipping address.', 'wpshop');
				}
			}
			else {
				$output .= __('The shipping modes will be display when you have register an shipping address.', 'wpshop');
			}
			return array( $status, $output);
		}
		
		function display_shipping_mode() {
			$shipping_modes = self::generate_shipping_mode_for_an_address();
			$output = wpshop_display::display_template_element('shipping_modes', array( 'SHIPPING_MODES' => $shipping_modes[1] ), array(), 'wpshop');
			$output .= apply_filters( 'wps_additionnal_shipping_mode','' );
			return $output;
		}
	
		function wps_reload_shipping_mode() {
			$status = false; $allow_order = true;
			$result = '';
			if ( !empty($_POST['address_id']) ) {
				$_SESSION['shipping_address'] = wpshop_tools::varSanitizer( $_POST['address_id'] );
			}
			$shipping_address_id = ( !empty($_SESSION['shipping_address']) ) ? $_SESSION['shipping_address'] : '';
			if ( !empty($shipping_address_id) ) {
				//$result = self::generate_shipping_mode_for_an_address();
				$shipping_modes = self::generate_shipping_mode_for_an_address();
				$status = $allow_order = $shipping_modes[0];
				if( empty( $shipping_modes[0]) || $shipping_modes[0] == false ) {
					$status = false;
				}
				
				$result = $shipping_modes[1];
				
				if ( $status == false ) {
					$allow_order = false;
					$result = '<div class="error_bloc">' .__('Sorry ! You can\'t order on this shop, because we don\'t ship in your country.', 'wpshop' ). '</div>';
				}

			}
			$response = array('status' => $status, 'response' => $result, 'allow_order' => $allow_order );
			echo json_encode( $response );
			die();
		}
		
		function wps_calculate_shipping_cost() {
			$status = false;
			$result = '';
			$chosen_method = !empty($_POST['chosen_method']) ? wpshop_tools::varSanitizer($_POST['chosen_method']) : null;
			
			if( !empty($chosen_method) ) {
				$_SESSION['shipping_method'] = $chosen_method;				
				$order = wpshop_cart::calcul_cart_information( array() );
				wpshop_cart::store_cart_in_session($order);
				
				$status = true;
			}
			
			$response = array('status' => $status );
			echo json_encode( $response );
			die();
		}
	}
}

/**	Instanciate the module utilities if not	*/
if ( class_exists("wps_shipping_mode") ) {
	$wps_shipping_mode = new wps_shipping_mode();
}