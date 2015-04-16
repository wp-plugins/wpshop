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

	/** Template Global vars **/
	DEFINE('WPS_CLASSIC_CHECKOUT_DIR', basename(dirname(__FILE__)));
	DEFINE('WPS_CLASSIC_CHECKOUT_PATH', str_replace( "\\", "/", str_replace( WPS_CLASSIC_CHECKOUT_DIR, "", dirname( __FILE__ ) ) ) );
	DEFINE('WPS_CLASSIC_CHECKOUT_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_CLASSIC_CHECKOUT_PATH ) );


	class wps_classic_checkout {
		/**
		 * Define the main directory containing the template for the current plugin
		 * @var string
		 */
		private $template_dir;

		/**
		 * Define the directory name for the module in order to check into frontend
		 * @var string
		 */
		private $plugin_dirname = WPS_CLASSIC_CHECKOUT_DIR;


		function __construct() {
			/** Template Load **/
			$this->template_dir = WPS_CLASSIC_CHECKOUT_PATH . WPS_CLASSIC_CHECKOUT_DIR . "/templates/";

			/** Classic Checkout Shortcode **/
			add_shortcode( 'wps_checkout', array( &$this, 'show_classic_checkout') );
			/** Checkout Step indicator **/
			add_shortcode('wps_checkout_step_indicator', array(&$this, 'get_checkout_step_indicator') );

			// Add scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts') );

			/** Ajax Actions **/
			add_action( 'wp_ajax_wps-checkout_valid_step_three', array( &$this, 'wps_checkout_valid_step_three') );
			add_action( 'wp_ajax_wps-checkout_valid_step_four', array( &$this, 'wps_checkout_valid_step_four') );
			add_action( 'wp_ajax_wps-checkout_valid_step_five', array( &$this, 'wps_checkout_valid_step_five') );
		}

		/**
		 * Add scripts
		 */
		function add_scripts() {
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'wps_classic_checkout', plugins_url('templates/frontend/js/wps_classic_checkout.js', __FILE__) );
		}

		/**
		 * Display Classic Checkout
		 */
		function show_classic_checkout() {

			if( !empty($_GET['action']) && $_GET['action'] == 'direct_payment_link') {
				wpshop_checkout::direct_payment_link( $_GET['token'], $_GET['order_id'], $_GET['login']);
			}

			$checkout_step_indicator = do_shortcode( '[wps_checkout_step_indicator]');
			$checkout_content = '';

			if ( !empty($_GET['order_step']) ) {
				switch( $_GET['order_step']) {
					case 1 :
						ob_start();
						require( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir, "frontend", "classic-checkout", "step-one") );
						$checkout_content .= ob_get_contents();
						ob_end_clean();
					break;
					case 2 :
						if ( get_current_user_id() != 0 ) {
							$permalink_option = get_option( 'permalink_structure' );
							$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
							$url = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=3';
							wpshop_tools::wpshop_safe_redirect( $url );
						}
						else {
							ob_start();
							require( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir, "frontend", "classic-checkout", "step-two") );
							$checkout_content .= ob_get_contents();
							ob_end_clean();
						}
					break;
					case 3 :
						if ( get_current_user_id() == 0 ) {
							$permalink_option = get_option( 'permalink_structure' );
							$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
							$url = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=2';
							wpshop_tools::wpshop_safe_redirect( $url );
						}
						else {
							if( !empty($_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) ) {
								ob_start();
								require( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir, "frontend", "classic-checkout", "step-three") );
								$checkout_content .= ob_get_contents();
								ob_end_clean();
								$url = apply_filters('wps_extra_signup_actions', ( isset( $url ) ? $url : '' ) );
								if(!empty($url)) {
									wpshop_tools::wpshop_safe_redirect( $url );
								}
							}
							else {
								$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
								$url = get_permalink( $checkout_page_id  );
								wpshop_tools::wpshop_safe_redirect( $url );
							}
						}
					break;
					case 4 :
						if ( get_current_user_id() == 0 ) {
							$permalink_option = get_option( 'permalink_structure' );
							$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
							$url = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=2';
							wpshop_tools::wpshop_safe_redirect( $url );
						}
						else {
							if( !empty($_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) ) {
								ob_start();
								require( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir, "frontend", "classic-checkout", "step-four") );
								$checkout_content .= ob_get_contents();
								ob_end_clean();
							}
							else {
								$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
								$url = get_permalink( $checkout_page_id  );
								wpshop_tools::wpshop_safe_redirect( $url );
							}
						}
					break;
					case 5 :
						if ( get_current_user_id() == 0 ) {
							$permalink_option = get_option( 'permalink_structure' );
							$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
							$url = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=2';
							wpshop_tools::wpshop_safe_redirect( $url );
						}
						else {
							$wps_cart = new wps_cart();
							$order = $wps_cart->calcul_cart_information( array() );
							$wps_cart->store_cart_in_session($order);
							$shipping_option = get_option( 'wpshop_shipping_address_choice' );
							if ( !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) && ( ( !empty($shipping_option) && !empty($shipping_option['activate']) && !empty($_SESSION['shipping_method']) ) || ( !empty($shipping_option) && empty($shipping_option['activate']) )  ) ) {
								$order_id = ( !empty($_SESSION['cart']['order_id']) ) ? wpshop_tools::varSanitizer($_SESSION['cart']['order_id']) : 0;
								ob_start();
								require( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir, "frontend", "classic-checkout", "step-five") );
								$checkout_content .= ob_get_contents();
								ob_end_clean();
							}
							else {
								$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
								$url = get_permalink( $checkout_page_id  );
								wpshop_tools::wpshop_safe_redirect( $url );
							}
						}
					break;
					case 6 :

						if ( !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) ){
						 	$wps_marketing_tools_ctr = new wps_marketing_tools_ctr();
						 	$checkout_content .=  $wps_marketing_tools_ctr->display_ecommerce_ga_tracker( $_SESSION['order_id'] );
						 	$checkout_content .= $this->wps_classic_confirmation_message();
						}
						else {
							$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
							$url = get_permalink( $checkout_page_id  );
							wpshop_tools::wpshop_safe_redirect( $url );
						}
					break;
					default :
						ob_start();
						require( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir, "frontend", "classic-checkout", "step-one") );
						$checkout_content .= ob_get_contents();
						ob_end_clean();
					break;
				}

			}
			else {
				$checkout_content = do_shortcode('[wps_cart]');
			}

			require_once( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir, "frontend", "classic_checkout") );

		}

		/**
		 * Display the Step 6 confirmation message
		 * @return string
		 */
		function wps_classic_confirmation_message() {
			$output = '';
			$wps_cart = new wps_cart();
			$available_templates = array( 'banktransfer', 'checks', 'free', 'paypal', 'cic', 'quotation', 'cash_on_delivery' );
			$payment_method = ( !empty($_SESSION['payment_method']) && in_array($_SESSION['payment_method'], $available_templates) ) ? $_SESSION['payment_method'] : 'others';
			ob_start();
			require( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir,"frontend", "confirmation/confirmation", $payment_method) );
			$output .= ob_get_contents();
			ob_end_clean();
			return $output;
		}

		/**
		 * Display Checkout Step indicator
		 * @return String
		 */
		function get_checkout_step_indicator() {
			$default_step = ( !empty( $_GET['order_step'] ) ) ? wpshop_tools::varSanitizer( $_GET['order_step'] ) : 1;
			$steps = array( __('Cart', 'wpshop'), __('Identification', 'wpshop'), __('Addresses', 'wpshop'), __('Shipping Mode', 'wpshop'), __( 'Payment', 'wpshop'), __( 'Confirmation', 'wpshop' )  );
			$steps = apply_filters('wps_extra_action_checkout_indicator', $steps);
			require_once( wpshop_tools::get_template_part( WPS_CLASSIC_CHECKOUT_DIR, $this->template_dir,"frontend", "checkout_step_indicator/checkout_step_indicator") );
		}

		/**
		 * AJAX - Valid Checkout Step three
		 */
		function wps_checkout_valid_step_three() {
			$response = ''; $status = true;

			$shipping_address = ( !empty($_POST['shipping_address_id']) ) ? wpshop_tools::varSanitizer( $_POST['shipping_address_id'] ): null;
			$billing_address = ( !empty($_POST['billing_address_id']) ) ? wpshop_tools::varSanitizer( $_POST['billing_address_id'] ): null;

			$user_id = get_current_user_id();

			$response = '<div class="wps-alert-error"><ul>';

			if( $user_id != 0 ) {
				$shipping_option = get_option( 'wpshop_shipping_address_choice' );
				$billing_option = get_option( 'wpshop_billing_address' );
				$user_addresses = wps_address::get_addresses_list( $user_id );

				if( !empty($shipping_option) && !empty($shipping_option['activate']) ) {
					/** Check Shipping address **/
					if ( empty($shipping_address) ) {
						$status = false;
						/** Check if user have already create a shipping address **/
						if ( !empty($shipping_option['choice']) && !empty($user_addresses) && !empty($user_addresses[ $shipping_option['choice'] ]) ){
							$response .= '<li>'.__( 'You must select a shipping address', 'wpshop' ).'</li>';
						}
						else {
							$response .= '<li>'.__( 'You must create a shipping address', 'wpshop' ).'</li>';
						}
					}

				}
				/** Check Billing address **/
				if( empty($billing_address) ) {
					$status = false;
					if ( !empty($billing_option['choice']) && !empty($user_addresses) && !empty($user_addresses[ $billing_option['choice'] ]) ){
						$response .= '<li>'.__( 'You must select a billing address', 'wpshop' ).'</li>';
					}
					else {
						$response .= '<li>'.__( 'You must create a billing address', 'wpshop' ).'</li>';
					}
				}
			}
			else {
				$status = false;
				$response .= '<li>'.__( 'You must be logged to pass to next step', 'wpshop' ).'</li>';
			}
			$response .= '</ul></div>';

			/** If no error **/
			if( $status ) {
				$_SESSION['shipping_address'] = $shipping_address;
				$_SESSION['billing_address'] = $billing_address;

				$permalink_option = get_option( 'permalink_structure' );
				$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
				/** Checking if no shipping method is required and it is a quotation or a free order **/
				$shipping_option = get_option( 'wps_shipping_mode' );
				$available_shipping_method = false;
				if( !empty($shipping_option) && !empty($shipping_option['modes']) ) {
					foreach( $shipping_option['modes'] as $shipping_mode_id => $shipping_mode ) {
						if( !empty($shipping_mode['active']) && $shipping_mode['active'] == 'on' ) {
							$available_shipping_method = true;
						}
					}
				}

				if( !$available_shipping_method ) {
					$_SESSION['shipping_method'] = 'No Shipping method required';
					$order_id = ( !empty($_SESSION['cart']['order_id']) ) ? wpshop_tools::varSanitizer($_SESSION['cart']['order_id']) : 0;

					if ( !empty($_SESSION) && !empty( $_SESSION['cart'] ) && !empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type'] == 'quotation') {
						$status = true;
						$payment_method = $_SESSION['payment_method'] = 'quotation';
						$order_id = wpshop_checkout::process_checkout( $payment_method, $order_id, get_current_user_id(), $_SESSION['billing_address'], $_SESSION['shipping_address'] );
						$response = get_permalink( wpshop_tools::get_page_id( $checkout_page_id )  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=6';
					}
					elseif( !empty($_SESSION) && !empty( $_SESSION['cart'] ) && isset($_SESSION['cart']['order_amount_to_pay_now']) && number_format( $_SESSION['cart']['order_amount_to_pay_now'], 2, '.', '' ) == '0.00' ) {
						$status = true;
						$payment_method = $_SESSION['payment_method'] = 'free';
						$order_id = wpshop_checkout::process_checkout( $payment_method, $order_id, get_current_user_id(), $_SESSION['billing_address'], $_SESSION['shipping_address'] );
						$permalink_option = get_option( 'permalink_structure' );
						$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
						$url = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=6';
// 						wpshop_tools::wpshop_safe_redirect( $url );
						$response = $url;
					}
					else {
						$status = true;
						$response = get_permalink( wpshop_tools::get_page_id( $checkout_page_id )  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=5';
					}
				}
				else {
					$status = true;
					$response = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=4';
				}
			}
			//Stock checking verification
			$this->checking_stock();


			echo json_encode( array( 'status' => $status, 'response' => $response ) );
			die();
		}

		/**
		 * AJAX - Valid Checkout step four
		 */
		function wps_checkout_valid_step_four() {
			$shipping_method = ( !empty($_POST['shipping_mode']) ) ? wpshop_tools::varSanitizer($_POST['shipping_mode']) : null;
			$status = false;
			$response = '';
			$permalink_option = get_option( 'permalink_structure' );
			$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
			if ( !empty($shipping_method) ) {
				$status = true;
				$_SESSION['shipping_method'] = $shipping_method;
				$order_id = ( !empty($_SESSION['cart']['order_id']) ) ? wpshop_tools::varSanitizer($_SESSION['cart']['order_id']) : 0;
				if ( !empty($_SESSION) && !empty( $_SESSION['cart'] ) && !empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type'] == 'quotation') {
					$payment_method = $_SESSION['payment_method'] = 'quotation';
					$order_id = wpshop_checkout::process_checkout( $payment_method, $order_id, get_current_user_id(), $_SESSION['billing_address'], $_SESSION['shipping_address'] );
					$response = get_permalink( wpshop_tools::get_page_id( $checkout_page_id )  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=6';
				}
				elseif( !empty($_SESSION) && !empty( $_SESSION['cart'] ) && !empty($_SESSION['cart']['order_amount_to_pay_now']) && number_format( $_SESSION['cart']['order_amount_to_pay_now'], 2, '.', '' ) == '0.00' ) {
					$payment_method = $_SESSION['payment_method'] = 'free';
					$order_id = wpshop_checkout::process_checkout( $payment_method, $order_id, get_current_user_id(), $_SESSION['billing_address'], $_SESSION['shipping_address'] );
					$permalink_option = get_option( 'permalink_structure' );
					$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
					$url = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=6';
					$response = $url;
				}
				else {
					$response = get_permalink( wpshop_tools::get_page_id( $checkout_page_id )  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=5';
				}
			}
			else {
				$response .= '<div class="wps-alert-error">'.__( 'You must select a shipping method', 'wpshop' ).'</div>';
			}

			//Stock checking verification
			$this->checking_stock();

			echo json_encode( array( 'status' => $status, 'response' => $response) );
			die();
		}

		/**
		 * AJAX - Valid Checkout step four
		 */
		function wps_checkout_valid_step_five() {
			$status = false; $response = '';
			$payment_method = ( !empty($_POST['wps-payment-method']) ) ? wpshop_tools::varSanitizer( $_POST['wps-payment-method'] ): null;
			$order_id = ( !empty($_SESSION['cart']['order_id']) ) ? wpshop_tools::varSanitizer($_SESSION['cart']['order_id']) : 0;
			$customer_comment = ( !empty($_POST['wps-customer-comment']) ) ? wpshop_tools::varSanitizer( $_POST['wps-customer-comment'] ) : null;

			$terms_of_sale_checking = ( ( isset($_POST['terms_of_sale_indicator']) && !empty($_POST['terms_of_sale']) ) || ( !empty($_POST['terms_of_sale']) ) || ( !isset($_POST['terms_of_sale_indicator']) && empty($_POST['terms_of_sale']) ) ) ? true : false;

			if ($terms_of_sale_checking) {
				if ( !empty($payment_method) ) {
					/** Check if the payment method exist for the shop **/
					$payment_option = get_option( 'wps_payment_mode' );

					if( !empty($payment_option) && !empty($payment_option['mode']) && array_key_exists( $payment_method, $payment_option['mode']) && !empty($payment_option['mode'][$payment_method]['active']) ) {
						$order_id = wpshop_checkout::process_checkout( $payment_method, $order_id, get_current_user_id(), $_SESSION['billing_address'], $_SESSION['shipping_address'] );
						if( !empty($order_id) && !empty($customer_comment) ) {
							wp_update_post( array('ID' => $order_id, 'post_excerpt' => $customer_comment) );
						}
						$permalink_option = get_option( 'permalink_structure' );
						$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
						$response = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=6';
						$_SESSION['payment_method'] = $payment_method;
						$status = true;
						//Add an action to extra actions on order save
						$args = array( 'order_id' => $order_id, 'posted_data' => $_REQUEST);
						wpshop_tools::create_custom_hook( 'wps_order_extra_save_action', $args );
					}
					else {
						$response = '<div class="wps-alert-error">' .__( 'This payment method is unavailable', 'wpshop' ).'</div>';
					}
				}
				else {
					$response = '<div class="wps-alert-error">' .__( 'You must choose a payment method', 'wpshop' ).'</div>';
				}
			}
			else {
				$response = '<div class="wps-alert-error">' .__( 'You must accept the terms of sale to order', 'wpshop' ).'</div>';
			}

			echo json_encode( array('status' => $status, 'response' => $response) );
			die();
		}

		/**
		 * Checking stock in differents checkout steps
		 */
		function checking_stock() {
			if( !empty($_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) ) {
				foreach( $_SESSION['cart']['order_items'] as $item_id => $item ) {
					$wps_product = new wps_product_ctr();
					$checking = $wps_product->check_stock( $item['item_id'], $item['item_qty'], $item_id );
					if( $checking !== true ) {
						unset(  $_SESSION['cart']['order_items'][$item_id] );
						$wps_cart_ctr = new wps_cart();
						$order = $wps_cart_ctr->calcul_cart_information( array() );
						$wps_cart_ctr->store_cart_in_session( $order );
					}
				}
			}
		}

	}
}
if ( class_exists("wps_classic_checkout") ) {
	$wps_classic_checkout = new wps_classic_checkout();
}