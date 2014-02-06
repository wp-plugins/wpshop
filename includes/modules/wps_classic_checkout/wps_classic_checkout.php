<?php
/**
 * Plugin Name: WP Shop Classic Checkout
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WP Shop Classic Checkout
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WP Shop Classic Checkout bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
 
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wps_classic_checkout") ) {
	class wps_classic_checkout {
		function __construct() {
			add_shortcode( 'wps_checkout', array( &$this, 'show_classic_checkout') );
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			
			/** Enqueue Script **/
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'wps_classic_checkout_js', plugins_url('templates/wpshop/js/wps_classic_checkout.js', __FILE__) );
			
			
			add_action('wp_ajax_wps_control_validity_step_two', array( &$this, 'wps_control_validity_step_two') );
			add_action( 'wp_ajax_wps_classic_ckeckout_finish_order', array( &$this, 'wps_classic_ckeckout_finish_order') );
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
		
		
		function show_classic_checkout() {
			$output = '';
			$output = do_shortcode('[wps_checkout_step_indicator]');
			$tpl_component = array();
			if ( empty($_GET['order_step']) && get_current_user_id() != 0 ) {
				$permalink_option = get_option( 'permalink_structure' );
				$link = get_permalink( get_option('wpshop_checkout_page_id') ).( (!empty($permalink_option) ) ? '/?order_step=2' : '&order_step=2' ) ;
				wp_safe_redirect( $link );
			}
			if ( get_current_user_id() == 0 || !empty( $_GET['order_step']) && $_GET['order_step'] == 1 ) {
				$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT']  = '<div class="wps-form-container">';
				$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] .= '<div id="wps_address_error_container"></div>';
				$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] .= '<div id="wps_form_content">';
				if ( !empty($_GET['action']) && $_GET['action'] == 'retrieve_password' ) {
					$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] .= do_shortcode( '[wpshop_forgot_password]');
				}
				else {
					$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] .= do_shortcode( '[wpshop_sign_up]');
				}
				$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] .= '</div></div>';
				$tpl_component['CLASSIC_CHECKOUT_SECONDARY_CONTENT'] = do_shortcode('[wps_cart_summary]');
				$tpl_component['NEXT_STEP_BUTTON'] = '';
			}
			if ( !empty( $_GET['order_step']) && $_GET['order_step'] == 2 ) {
				$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] = do_shortcode( '[wps_address_list]' );
				$tpl_component['CLASSIC_CHECKOUT_SECONDARY_CONTENT'] = do_shortcode('[wps_cart_summary]');
				$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] .= '<div id="wps_shipping_modes_choice">' .do_shortcode( '[wps_shipping_mode]' ). '</div>';
				$tpl_component['NEXT_STEP_BUTTON'] = '<button id="wps_classic_checkout_finish_step_2" class="wps-bton wps-bton-prim">' .__('Next step', 'wpshop'). '</button><img src="' .WPSHOP_LOADING_ICON.'" alt="' .__('Loading', 'wpshop'). '" id="wps_classic_checkout_step_two_loader" class="wpshopHide" />';
			}
			if ( !empty( $_GET['order_step']) && $_GET['order_step'] == 3 ) {
				if ( empty($_SESSION['cart']) ) {
					wp_safe_redirect( site_url() );
				}
				
				$cart_type = (!empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type']=='quotation') ? 'quotation' : 'cart';
				if ( $cart_type == 'quotation' ) {
					wpshop_checkout::process_checkout('quotation');
					$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] = '<p>'.__('Thank you ! Your quotation has been sent. We will respond to you as soon as possible.', 'wpshop').'</p>';
					$tpl_component['NEXT_STEP_BUTTON'] = '';
					wpshop_cart::empty_cart();
				}
				else {
					if ( $_SESSION['cart']['order_amount_to_pay_now'] > 0 ) {
						$available_payement_method = wpshop_payment::display_payment_methods_choice_form(0, $cart_type);
						$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT']  = '<div id="wps_payment_method_container">';
						$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] .= wpshop_tools::create_custom_hook('wpshop_payment_method');
						$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] .= $available_payement_method[0];
						$tpl_component['NEXT_STEP_BUTTON'] = '<input type="button" class="wps-bton wps-bton-prim" name="takeOrder" id="wps_classic_checkout_take_order" value="' .__('Order', 'wpshop'). '"><img src="' .WPSHOP_LOADING_ICON.'" alt="' .__('Loading', 'wpshop'). '" id="wps_classic_checkout_finish_order_loader" class="wpshopHide" /></div>';
						
					}
					else {
						wpshop_checkout::process_checkout( 'free' );
						$tpl_component['CLASSIC_CHECKOUT_PRIMARY_CONTENT'] = wpshop_display::display_template_element('wpshop_checkout_page_free_confirmation_message', array() );
						$tpl_component['NEXT_STEP_BUTTON'] = '';
					}
				}
				$tpl_component['CLASSIC_CHECKOUT_SECONDARY_CONTENT']  = do_shortcode('[wps_billing_address_summary]');
				$tpl_component['CLASSIC_CHECKOUT_SECONDARY_CONTENT']  .= do_shortcode('[wps_shipping_address_summary]');
				$tpl_component['CLASSIC_CHECKOUT_SECONDARY_CONTENT'] .= do_shortcode('[wps_cart_summary]');
			}
			
			
			
			$output .= wpshop_display::display_template_element('wps_classic_checkout', $tpl_component, array(), 'wpshop');
			unset( $tpl_component );
			return $output;
		}
		
		/** Finish Checkout Step Two **/
		function wps_control_validity_step_two() {
			$status = false; $result = '';
			$shipping_address_id = ( !empty( $_POST['shipping_address_id']) ) ? wpshop_tools::varSanitizer( $_POST['shipping_address_id'] ) : '';
			$billing_address_id = ( !empty( $_POST['billing_address_id']) ) ? wpshop_tools::varSanitizer( $_POST['billing_address_id'] ) : '';
			
			if ( !empty( $shipping_address_id ) && !empty($billing_address_id) ){
				$_SESSION['shipping_address'] = $shipping_address_id;
				$_SESSION['billing_address'] = $billing_address_id;
				$permalink_option = get_option( 'permalink_structure' );
				$result = get_permalink( get_option('wpshop_checkout_page_id') ).( (!empty($permalink_option) ) ? '?order_step=3' : '&order_step=3' );
				$status = true;
			}
			else {
				$result = __('You must choose or create a shipping address', 'wpshop');
			}
			$response = array( 'status' => $status, 'response' => $result );
			echo json_encode( $response );
			die();
		}
		
		/** Finish the Order **/
		function wps_classic_ckeckout_finish_order() {
			$status = false; $result = '';
			$available_paymentMethod = get_option('wps_payment_mode', array() );
			$payment_method = ( !empty($_POST['payment_method']) ) ? wpshop_tools::varSanitizer($_POST['payment_method']) : null;
			if ( !empty($payment_method) ) {
				if ( !empty($_SESSION['shipping_address']) ) {
					if ( !empty($available_paymentMethod) && !empty($available_paymentMethod['mode']) && !empty($available_paymentMethod['mode']['checks']) && !empty($available_paymentMethod['mode']['checks']['active'])  && $payment_method == 'check' ) {
						$paymentInfo = get_option('wpshop_paymentAddress', true);
						$tpl_component = array();
						if ( !empty($paymentInfo) ) {
							foreach ( $paymentInfo as $key => $value) {
								$tpl_component['CHECK_CONFIRMATION_MESSAGE_' . strtoupper($key)] = $value;
							}
						}
						$tpl_component['ORDER_AMOUNT'] = ( !empty($_SESSION['cart']['order_amount_to_pay_now']) ) ? number_format($_SESSION['cart']['order_amount_to_pay_now'], 2, ',', '') : '';
						$result = wpshop_display::display_template_element('wpshop_checkout_page_check_confirmation_message', $tpl_component);
						/** Process Checkout **/
						wpshop_checkout::process_checkout( $payment_method );
					}
					elseif( !empty($available_paymentMethod) && empty($available_paymentMethod['mode']) && !empty($available_paymentMethod['mode']['banktransfer']) && !empty($available_paymentMethod['mode']['banktransfer']['active']) && $payment_method == 'banktransfer' ) {
						$wpshop_paymentMethod_options = get_option('wpshop_paymentMethod_options');
						$tpl_component = array();
						if ( !empty($wpshop_paymentMethod_options['banktransfer']) ) {
							foreach ( $wpshop_paymentMethod_options['banktransfer'] as $key => $value) {
								$tpl_component['BANKTRANSFER_CONFIRMATION_MESSAGE_' . strtoupper($key)] = $value;
							}
							$result .= wpshop_display::display_template_element('wpshop_checkout_page_banktransfer_confirmation_message', $tpl_component);
						}
						wpshop_checkout::process_checkout( $payment_method );
					}
					/** CIC **/
					elseif( !empty($available_paymentMethod) && !empty($available_paymentMethod['mode']) && !empty($available_paymentMethod['mode']['cic']) && !empty($available_paymentMethod['mode']['cic']['active']) && $payment_method == 'cic' ) {
						$result = wpshop_cic::display_form( $_SESSION['order_id'] );
					}
					elseif( !empty($available_paymentMethod) && !empty($available_paymentMethod['mode']) && !empty($available_paymentMethod['mode']['paypal']) && !empty($available_paymentMethod['mode']['paypal']['active']) && $payment_method == 'paypal' ) {
						$result = wpshop_paypal::display_form( $_SESSION['order_id'] );
					}
					else {
						$result = wpshop_tools::create_custom_hook('wpshop_payment_actions');
					}
					wpshop_cart::empty_cart();
					$status = true;
				}
			}
			else {
				$result = __('You must choose a payment method', 'wpshop' );
			}
			
			$response = array( 'status' => $status, 'response' => $result );
			echo json_encode( $response );
			die();
		}
		
	}
}
if ( class_exists("wps_classic_checkout") ) {
	$wps_classic_checkout = new wps_classic_checkout();
}