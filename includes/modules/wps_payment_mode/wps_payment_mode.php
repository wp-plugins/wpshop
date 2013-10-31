<?php
/**
 * Plugin Name: WP Shop Payment Mode
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WPShop Payment Mode
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WPShop Payment Mode bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
 
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wps_payment_mode") ) {
	class wps_payment_mode {
		function __construct() {
			/** Checking Payment Mode Option **/
			$payment_option = get_option( 'wps_payment_mode' );
			if ( empty($payment_option) ) {
				self::migrate_payment_modes();
			}
			
			/** Check if SystemPay is registred in Payment Main Option **/
			$payment_option = get_option( 'wps_payment_mode' );
			if ( !empty($payment_option) && !empty($payment_option['mode']) && !array_key_exists('checks', $payment_option['mode']) ) {
				$payment_option['mode']['checks']['name'] = __('Checks', 'wpshop');
				$payment_option['mode']['checks']['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/cheque.png';
				$payment_option['mode']['checks']['description'] = __('Reservation of products upon receipt of the check.', 'wpshop');
				update_option( 'wps_payment_mode', $payment_option );
			}
			
			if ( !empty($payment_option) && !empty($payment_option['mode']) && !array_key_exists('banktransfer', $payment_option['mode']) ) {
				$payment_modes['mode']['banktransfer']['name'] = __('Banktransfer', 'wpshop');
				$payment_modes['mode']['banktransfer']['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/cheque.png';
				$payment_modes['mode']['banktransfer']['description'] = __('Reservation of products upon confirmation of payment.', 'wpshop');
				update_option( 'wps_payment_mode', $payment_option );
			}
			
			
			wp_enqueue_script('jquery-ui-sortable');
			if ( is_admin() ) {
				wp_enqueue_script('jquery');
				wp_enqueue_script( 'wps_payment_mode_js', plugins_url('templates/backend/js/wps_payment_mode.js', __FILE__) );
			}
			
			/** Create Options **/
			add_action('wsphop_options', array(&$this, 'create_options') );
			
			add_thickbox();
			
			/** Template Load **/
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			
			add_filter( 'wps_payment_mode_interface_checks', array( &$this, 'display_interface_check') );
			add_filter( 'wps_payment_mode_interface_banktransfer', array( &$this, 'display_admin_interface_banktransfer') );
		}
		
		/** Load module/addon automatically to existing template list
		 *
		 * @param array $templates The current template definition
		 *
		 * @return array The template with new elements
		 */
		function custom_template_load( $templates ) {
			include('templates/backend/main_elements.tpl.php');
			$templates = wpshop_display::add_modules_template_to_internal( $tpl_element, $templates );
			unset($tpl_element);
		
			return $templates;
		}
		
		/**
		 * Create the options 
		 */
		function create_options() {
			register_setting('wpshop_options', 'wps_payment_mode', array(&$this, 'wps_validate_payment_option'));
			add_settings_field('wps_payment_mode', __('Payment Modes', 'wpshop'), array(&$this, 'display_payment_modes_in_admin'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');	
		}
		
		
		/**
		 * Options Validator
		 * @param array $input
		 * @return array
		 */
		function wps_validate_payment_option( $input ) {
			foreach( $input['mode'] as $mode_key => $mode_config ) {
				if ( !empty($_FILES[$mode_key.'_logo']['name']) && empty($_FILES[$mode_key.'_logo']['error']) ) {
					$filename = $_FILES[$mode_key.'_logo'];
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
					
					$input['mode'][$mode_key]['logo'] = $attach_id;
				}
			}
			return $input;
		}
		
		/**
		 * Display Payment Mode in Admin
		 */
		function display_payment_modes_in_admin() {
			$output = '';
			$payment_option = get_option( 'wps_payment_mode' );
			if ( !empty($payment_option) && !empty($payment_option['mode']) ) {
				$output .= wpshop_display::display_template_element('wps_payment_mode_each_interface', array(), array(), 'admin');
				$tpl_component = array();
				$tpl_component['INTERFACES'] = '';
				
				foreach( $payment_option['mode'] as $k => $payment_mode ){
					$sub_tpl_component = array();
					$sub_tpl_component['PAYMENT_MODE_ID'] = $k;
					$sub_tpl_component['PAYMENT_MODE_NAME'] = ( !empty($payment_mode['name']) ) ? $payment_mode['name'] : '';
					$sub_tpl_component['PAYMENT_MODE_ACTIVE'] = !empty( $payment_mode['active'] ) ? 'checked="checked"' : '';
					$sub_tpl_component['DEFAULT_PAYMENT_MODE_ACTIVE'] = ( !empty( $payment_option['default_payment_mode'] ) && $payment_option['default_payment_mode'] == $k ) ? 'checked="checked"' : '';
					$sub_tpl_component['PAYMENT_MODE_LOGO_POST_ID'] = !empty($payment_mode['logo']) ? $payment_mode['logo'] : '';
					if ( !empty($payment_mode['logo']) && (int)$payment_mode['logo'] != 0 ) {
						$sub_tpl_component['PAYMENT_MODE_THUMBNAIL'] = ( !empty($payment_mode['logo']) ) ? wp_get_attachment_image( $payment_mode['logo'], 'thumbnail', false, array('class' => 'wps_shipping_mode_logo') ) : '';
					}
					else {
						$sub_tpl_component['PAYMENT_MODE_THUMBNAIL'] = ( !empty($payment_mode['logo']) ) ? '<img src="' .$payment_mode['logo']. '" alt="" />' : '';
					}
					$sub_tpl_component['PAYMENT_DESCRIPTION'] =  ( !empty($payment_mode['description']) ) ? $payment_mode['description'] : '';
					$sub_tpl_component['PAYMENT_MODE_CONFIGURATION_INTERFACE'] = apply_filters('wps_payment_mode_interface_'.$k, $k );
					$tpl_component['INTERFACES'] .= wpshop_display::display_template_element('wps_payment_mode_each_interface', $sub_tpl_component, array(), 'admin');
					unset( $sub_tpl_component );
				}
				$output = wpshop_display::display_template_element('wps_payment_mode_interface', $tpl_component, array(), 'admin');
				unset( $tpl_component );
			}
			
			echo $output;
		}
		
		
		function migrate_payment_modes() {
			$payment_modes = array();
			$payment_option = get_option( 'wpshop_paymentMethod' );
			$methods = array();
			$methods['display_position']['paypal'] = ( !empty($payment_option) && !empty($payment_option['paypal']) ) ? 'on' : '';
			$methods['display_position']['checks'] = ( !empty($payment_option) && !empty($payment_option['checks']) ) ? 'on' : '';
			$methods['display_position']['banktransfer'] = ( !empty($payment_option) && !empty($payment_option['banktransfer']) ) ? 'on' : '';
			$methods['default_method'] = ( !empty($payment_option['default_method']) ) ? $payment_option['default_method'] : 'checks';
			
			if ( !empty($payment_option['display_position']) ) {
				$methods['display_position'] = array_merge( $methods['display_position'], $payment_option['display_position'] );
				foreach( $methods['display_position'] as $k => $v ) {
					if ( !empty($payment_option[$k]) ) {
						$methods['display_position'][$k] = $payment_option[$k];
					}
				}
			}
			
			if ( !empty($methods) && !empty($methods['display_position']) ) {
				foreach( $methods['display_position'] as $key => $value ) {
						$payment_modes['mode'][$key]['active'] = ( !empty($methods['display_position'][ $key ]) && $methods['display_position'][ $key ] == 'on' ) ? $methods['display_position'][ $key ] : '';
						switch( $key ) {
							case 'paypal' : 
								$payment_modes['mode'][$key]['name'] = __('Paypal', 'wpshop');
								$payment_modes['mode'][$key]['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/paypal.png';
								$payment_modes['mode'][$key]['description'] = __('<strong>Tips</strong> : If you have a Paypal account, by choosing this payment method, you will be redirected to the secure payment site Paypal to make your payment. Debit your PayPal account, immediate booking products.', 'wpshop');
							break;
							case 'banktransfer' : 
								$payment_modes['mode'][$key]['name'] = __('Banktransfer', 'wpshop');
								$payment_modes['mode'][$key]['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/cheque.png';
								$payment_modes['mode'][$key]['description'] = __('Reservation of products upon confirmation of payment.', 'wpshop');
							break;
							case 'checks' : 
								$payment_modes['mode'][$key]['name'] = __('Checks', 'wpshop');
								$payment_modes['mode'][$key]['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/cheque.png';
								$payment_modes['mode'][$key]['description'] = __('Reservation of products upon receipt of the check.', 'wpshop');
							break;
							case 'systempay' : 
								$payment_modes['mode'][$key]['name'] = __('Systempay', 'wpshop');
								$payment_modes['mode'][$key]['logo'] = plugins_url().'/wpshop_systemPay/img/systemPay.png';
								$payment_modes['mode'][$key]['description'] = __('SystemPay - Banque Populaire', 'wpshop_systemPay');
							break;
							case 'cic' : 
								$payment_modes['mode'][$key]['name'] = __('CIC', 'wpshop');
								$payment_modes['mode'][$key]['logo'] = WPSHOP_TEMPLATES_URL.'wpshop/medias/cic_payment_logo.jpg';
								$payment_modes['mode'][$key]['description'] = __('Reservation of products upon confirmation of payment.', 'wpshop');
							break;
						}
				}
				
				if ( $methods['default_method'] ) {
					$payment_modes['default_method'] = $methods['default_method'];
				}
				update_option( 'wps_payment_mode', $payment_modes);
			}
			
		}
		
		function display_interface_check() {
			$output = '';
			$company_payment = get_option('wpshop_paymentAddress');
			$company = get_option('wpshop_company_info');
			$output .= '<label class="simple_right">'.__('Company name', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_name]" type="text" value="'.(!empty($company_payment['company_name'])?$company_payment['company_name']:'').'" /><br />';
			$output .= '<label class="simple_right">'.__('Street', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_street]" type="text" value="'.(!empty($company_payment['company_street'])?$company_payment['company_street']:'').'" /><br />';
			$output .= '<label class="simple_right">'.__('Postcode', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_postcode]" type="text" value="'.(!empty($company_payment['company_postcode'])?$company_payment['company_postcode']:'').'" /><br />';
			$output .= '<label class="simple_right">'.__('City', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_city]" type="text" value="'.(!empty($company_payment['company_city'])?$company_payment['company_city']:'').'" /><br />';
			$output .= '<label class="simple_right">'.__('Country', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_country]" type="text" value="'.(!empty($company_payment['company_country'])?$company_payment['company_country']:'').'" />';
			return $output;
		}
		
		function display_admin_interface_banktransfer() {
			$wpshop_paymentMethod_options = get_option('wpshop_paymentMethod_options');
			$output = '';
			$output .= '<label class="simple_right">'.__('Bank name', 'wpshop').'</label> <input name="wpshop_paymentMethod_options[banktransfer][bank_name]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['bank_name'])?$wpshop_paymentMethod_options['banktransfer']['bank_name']:'').'" /><br />';
			$output .= '<label class="simple_right">'.__('IBAN', 'wpshop').'</label> <input name="wpshop_paymentMethod_options[banktransfer][iban]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['iban'])?$wpshop_paymentMethod_options['banktransfer']['iban']:'').'" /><br />';
			$output .= '<label class="simple_right">'.__('BIC/SWIFT', 'wpshop').'</label> <input name="wpshop_paymentMethod_options[banktransfer][bic]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['bic'])?$wpshop_paymentMethod_options['banktransfer']['bic']:'').'" /><br />';
			$output .= '<label class="simple_right">'.__('Account owner name', 'wpshop').'</label> <input name="wpshop_paymentMethod_options[banktransfer][accountowner]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['accountowner'])?$wpshop_paymentMethod_options['banktransfer']['accountowner']:'').'" /><br />';
			return $output;
		}

	}
}

/**	Instanciate the module utilities if not	*/
if ( class_exists("wps_payment_mode") ) {
	$wps_shipping_mode = new wps_payment_mode();
}