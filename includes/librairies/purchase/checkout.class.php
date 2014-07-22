<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Checkout
 *
 * The WPShop checkout class handles the checkout process, collecting user data and processing the payment.
 *
 * @class 		wpwhop_checkout
 * @package		WPShop
 * @category	Class
 * @author		Eoxia
 */

/* Instantiate the class from the shortcode */
function wpshop_checkout_init() {
	global $wpshop_checkout;
	$wpshop_checkout = new wpshop_checkout();
	if( !empty($_GET['action']) && $_GET['action'] == 'direct_payment_link') {
		$wpshop_checkout->direct_payment_link( $_GET['token'], $_GET['order_id'], $_GET['login']);
	}
	else {
		return $wpshop_checkout->display_form();
	}
}

class wpshop_checkout {

	var $div_register, $div_infos_register, $div_login, $div_infos_login = 'display:block;';
	var $creating_account = true;

	/** Constructor of the class
	* @return void
	*/
	function __construct () {
	}

	/**
	 * Display checkout form
	 *
	 * @return boolean|string
	 */
	function display_form() {
		global $wpshop, $wpshop_account, $wpshop_cart, $civility, $wpshop_signup;
		$output = '';

		/**	In case customer want to cancel order	*/
		if ( !empty($_GET['action']) && ($_GET['action']=='cancel') ) {
			$wpshop_cart->empty_cart();
			return __('Your order has been succesfully cancelled.', 'wpshop');
		}

		/**	Cart is empty -> Display message*/
		if($wpshop_cart->is_empty() && empty($_POST['order_id'])) :
			$output .= '<p>'.__('Your cart is empty. Select product(s) before checkout.','wpshop').'</p>';
		/**	Cart is not empty -> Check current step	*/
		else :
			/**	Check cart type for current order	*/
			$cart_type = (!empty($_SESSION['cart']['cart_type']) && $_SESSION['cart']['cart_type']=='quotation') ? 'quotation' : 'cart';

			/**	Check action to launch relative to post nformation	*/
			$form_is_ok = $this->managePost( $cart_type );

			/**	Get available payment method	*/
			$paymentMethod = get_option('wps_payment_mode');
			$paymentMethod = $paymentMethod['mode'];
			/**	Store order id into Session	*/
			$_SESSION['order_id'] = !empty($_POST['order_id']) ? $_POST['order_id'] : (!empty($_SESSION['order_id']) ? $_SESSION['order_id'] : 0);
			/**	if user ask a quotation	*/
			if ( $form_is_ok && isset($_POST['takeOrder']) && $cart_type=='quotation') {
// 				$output .= '<p>'.__('Thank you ! Your quotation has been sent. We will respond to you as soon as possible.', 'wpshop').'</p>';
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_quotation_confirmation_message', $tpl_component);
				/**	Empty customer cart	*/
				$wpshop_cart->empty_cart();
			}
			/**	If user want to pay with paypal	*/
			elseif($form_is_ok && !empty($paymentMethod['paypal']) && isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='paypal') {
				wpshop_paypal::display_form($_SESSION['order_id']);

				/**	Empty customer cart	*/
				$wpshop_cart->empty_cart();
			}
			/**	If user want to pay by check	*/
			elseif($form_is_ok && !empty($paymentMethod['checks']) && isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='checks') {
				// On recupere les informations de paiements par cheque
				$paymentInfo = get_option('wpshop_paymentAddress', true);
				$tpl_component = array();
				if ( !empty($paymentInfo) ) {
					foreach ( $paymentInfo as $key => $value) {
						$tpl_component['CHECK_CONFIRMATION_MESSAGE_' . strtoupper($key)] = $value;
					}
				}
				$tpl_component['ORDER_AMOUNT'] = ( !empty($_SESSION['cart']['order_amount_to_pay_now']) ) ? number_format($_SESSION['cart']['order_amount_to_pay_now'], 2, ',', '') : '';
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_check_confirmation_message', $tpl_component);
				/**	Empty customer cart	*/
				$wpshop_cart->empty_cart();
			}
			elseif($form_is_ok && !empty($paymentMethod['banktransfer']) && isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='banktransfer') {
				$wpshop_paymentMethod_options = get_option('wpshop_paymentMethod_options');
				$tpl_component = array();
				if ( !empty($wpshop_paymentMethod_options['banktransfer']) ) {
					foreach ( $wpshop_paymentMethod_options['banktransfer'] as $key => $value) {
						$tpl_component['BANKTRANSFER_CONFIRMATION_MESSAGE_' . strtoupper($key)] = $value;
					}
					$output .= wpshop_display::display_template_element('wpshop_checkout_page_banktransfer_confirmation_message', $tpl_component);
				}

				/**	Empty customer cart	*/
				$wpshop_cart->empty_cart();
			}

			/**	If Credit card by CIC is actived And the user selected this payment method	*/
			elseif($form_is_ok && isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='cic') {
				echo wpshop_CIC::display_form($_SESSION['order_id']);
				/**	Empty customer cart	*/
				$wpshop_cart->empty_cart();
			}
			elseif( $form_is_ok && !empty( $_POST['modeDePaiement']) && $_POST['modeDePaiement'] == 'free' ) {
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_free_confirmation_message', array() );

				/** Check if there is Downloadable products **/
				$order_meta = get_post_meta( $_SESSION['order_id'], '_order_postmeta', true );
				if ( !empty($order_meta['order_items']) ) {
					foreach( $order_meta['order_items'] as $key_value => $item ) {
						/** Check if it's a product with signle variation, check the parent product **/
						if ( !empty($item['item_id']) && get_post_type( $item['item_id'] ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
							$parent_item = wpshop_products::get_parent_variation( $item['item_id'] );
							$parent_post_metadata = $parent_item['parent_post_meta'];
							if ( !empty($parent_post_metadata['is_downloadable_']) ) {
								$query = $wpdb->prepare( 'SELECT value FROM '. WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS .' WHERE id = %d', $parent_post_metadata['is_downloadable_'] );
								$downloadable_option_value = $wpdb->get_var( $query );
								if ( !empty( $downloadable_option_value) ) {
									$item['item_is_downloadable_'] = $downloadable_option_value;
								}

							}
						}


						if ( !empty($item) && !empty($item['item_is_downloadable_']) && ( __( $item['item_is_downloadable_'], 'wpshop') == __('Yes', 'wpshop') || __( $item['item_is_downloadable_'], 'wpshop') == __('yes', 'wpshop') ) ) {
							$download_codes = get_user_meta($order_meta['customer_id'], '_order_download_codes_'.$_SESSION['order_id'], true);
							if ( !empty($download_codes) && !empty($download_codes[$key_value]) && !empty($download_codes[$key_value]['download_code']) ) {
								 $output .= wpshop_display::display_template_element('wpshop_checkout_page_free_download_link', array('DOWNLOAD_LINK' => WPSHOP_URL. '/download_file.php?oid=' .$_SESSION['order_id']. '&amp;download=' .$download_codes[$key_value]['download_code']) );
							}
						}
					}

				}

			}
			elseif ( $form_is_ok && !empty( $_POST['modeDePaiement'] ) ) {
				echo wpshop_tools::create_custom_hook('wpshop_payment_actions');
			}
			else {
				$user_id = get_current_user_id();
				if ($user_id) {
					$tpl_component = array();
					/** Display customer addresses */
					$tpl_component['CHECKOUT_CUSTOMER_BILLING_ADDRESS'] = wpshop_account::display_addresses_dashboard();
					$tpl_component['CHECKOUT_CUSTOMER_SHIPPING_CHOICE'] = wps_shipping_mode::display_shipping_mode();


					/** Display cart content	*/
					$tpl_component['CHECKOUT_SUMMARY_TITLE'] = ($cart_type=='quotation') ? __('Summary of the quotation','wpshop') : __('Summary of the order','wpshop');
					$tpl_component['CHECKOUT_CART_CONTENT'] = $wpshop_cart->display_cart(true);
					$tpl_component['CHECKOUT_TERM_OF_SALES'] = '';
					$option_page_id_terms_of_sale = wpshop_tools::get_page_id( get_option('wpshop_terms_of_sale_page_id') );
					if ( !empty($option_page_id_terms_of_sale) ) {
						$input_def['type'] = 'checkbox';
						$input_def['id'] = $input_def['name'] = 'terms_of_sale';

						$input_def['options']['label']['custom'] = sprintf( __('I have read and I accept %sthe terms of sale%s', 'wpshop'), '<a href="' . get_permalink($option_page_id_terms_of_sale) . '" target="_blank">', '</a>');
						$tpl_component['TERMS_ACCEPTATION_BOX_CONTENT'] = ''.wpshop_form::check_input_type($input_def);
						$tpl_component['CHECKOUT_TERM_OF_SALES'] = wpshop_display::display_template_element('wpshop_terms_box', $tpl_component);
					}

					/** Display available payment methods	*/
					if ( !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_amount_to_pay_now']) && $_SESSION['cart']['order_amount_to_pay_now'] > 0 ) {
						$available_payement_method = wpshop_payment::display_payment_methods_choice_form(0, $cart_type);
						$tpl_component['CHECKOUT_PAYMENT_METHODS'] = wpshop_tools::create_custom_hook('wpshop_payment_method');
						$tpl_component['CHECKOUT_PAYMENT_METHODS'] .= $available_payement_method[0];
					}
					else {
						$tpl_component['CHECKOUT_PAYMENT_METHODS'] = __('No payment method is required for this order', 'wpshop');
					}

					/**	Display order validation button in case payment methods are available	*/
					$tpl_component['CHECKOUT_PAYMENT_BUTTONS_CONTAINER'] = ' class="wpshop_checkout_button_container" ';
					if ( !empty($available_payement_method[0]) || $cart_type == 'quotation' || ( !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_amount_to_pay_now']) && $_SESSION['cart']['order_amount_to_pay_now'] == 0 ) ) {

						if ( empty($_SESSION['shipping_address']) || (!empty($_SESSION['shipping_address']) ) ) {
							if ($cart_type=='quotation') {
								$tpl_component['CHECKOUT_PAYMENT_BUTTONS'] = wpshop_display::display_template_element('wpshop_checkout_page_quotation_validation_button', array() );
							}
							else {
								if ( $_SESSION['cart']['order_amount_to_pay_now'] == 0 ) {
									$tpl_component['CHECKOUT_PAYMENT_BUTTONS'] = wpshop_display::display_template_element('wpshop_checkout_page_finish_order_button', array() );
								}
								else {
									$tpl_component['CHECKOUT_PAYMENT_BUTTONS'] = wpshop_display::display_template_element('wpshop_checkout_page_validation_button', array() );
								}
							}
						}
						else {
							$tpl_component['CHECKOUT_PAYMENT_BUTTONS'] = wpshop_display::display_template_element('wpshop_checkout_page_impossible_to_order', array());
						}
					}
					else{
						$tpl_component['CHECKOUT_PAYMENT_BUTTONS_CONTAINER'] = str_replace('_container"', '_container wpshop_checkout_button_container_no_method"', $tpl_component['CHECKOUT_PAYMENT_BUTTONS_CONTAINER']);
						$tpl_component['CHECKOUT_PAYMENT_BUTTONS'] = __('It is impossible to order for the moment','wpshop');
					}
					$output .= wpshop_display::display_template_element('wpshop_checkout_page', $tpl_component);
					unset($tpl_component);
				}
				else {
					$tpl_component = array();
					$tpl_component['CHECKOUT_LOGIN_FORM'] = $wpshop_account->display_login_form();
					$tpl_component['CHECKOUT_SIGNUP_FORM'] = wpshop_signup::display_form('partial');
					$output .= wpshop_display::display_template_element('wpshop_checkout_sign_up_page', $tpl_component);
					unset($tpl_component);
				}
			}
		endif;

		echo $output;
	}

	/**
	 * Validate an order. When customer validate checkout page this function do treatment for payment method
	 *
	 * @return boolean False if errors occured|True if all is OK
	 */
	function managePost( $cart_type ) {
		global $wpshop;
		$shipping_address_option = get_option('wpshop_shipping_address_choice');
		/**	If the user validate the checkout page	*/
		if( isset($_POST['takeOrder']) ) {
			// Finish an order
			if( !empty( $_POST['finish_order_id'] ) ) {
				if ( !empty($_POST['modeDePaiement']) ) {
					$this->process_checkout( wpshop_tools::varSanitizer($_POST['modeDePaiement']), wpshop_tools::varSanitizer($_POST['finish_order_id']) );
					return true;
				}
			}
			else {
			   if ( !empty($shipping_address_option['activate']) && ($shipping_address_option['activate'] == 'on') ) {
					if ( empty($_POST['shipping_address']) ) {
						$wpshop->add_error(__('You must choose a shipping address.', 'wpshop'));
					}
				}
				/** Billing adress if mandatory	*/
				if ( empty($_POST['billing_address']) ) {
					$wpshop->add_error(__('You must choose a billing address.', 'wpshop'));

				}
				else {
					/**	 If a order_id is given, meaning that the order is already created and the user wants to process to a new payment	*/
					$order_id = !empty($_POST['order_id']) && is_numeric($_POST['order_id']) ? $_POST['order_id'] : 0;

					/**	User ask a quotation for its order	*/
					if ($cart_type=='quotation') {
						$this->process_checkout('quotation', $order_id);
					}
					/**	Customer want to pay its order with one of available payment method 	*/
					elseif(isset($_POST['modeDePaiement']) || $_SESSION['cart']['order_amount_to_pay_now'] == 0 /*&& in_array( $_POST['modeDePaiement'], array('paypal', 'check', 'cic') )*/) {
						if ( $_SESSION['cart']['order_amount_to_pay_now'] == 0 ) {
							$_POST['modeDePaiement'] = 'free';
						}
						$this->process_checkout($_POST['modeDePaiement'], $order_id);
					}
					/**	Customer does not select any payment method for its order and it's not a quotation -> Display a error message to choose a payment method	*/
					else $wpshop->add_error(__('You have to choose a payment method to continue.', 'wpshop'));
				}
			}
		}
		else {
			$this->div_login = $this->div_infos_login = 'display:none';
		}

		/**	Display errors only in case the current cart is not a quotation	*/
		if ( ( $cart_type == 'cart' || $cart_type == 'quotation' ) && ($wpshop->error_count() > 0)) {
			echo $wpshop->show_messages();
			return false;
		}

		return true;
	}


	function process_checkout($paymentMethod='paypal', $order_id = 0, $customer_id = 0, $customer_billing_address_id = 0, $customer_shipping_address_id = 0) {
		global $wpdb, $wpshop, $wpshop_cart;

		$shipping_address_option = get_option('wpshop_shipping_address_choice');

		if (is_user_logged_in()) :
			$user_id = get_current_user_id();

		if ( $customer_id != 0 ) {
			$user_id = $customer_id;
		}

			// If the order is already created in the db
			if(!empty($order_id) && is_numeric($order_id)) {
				$order = get_post_meta($order_id, '_order_postmeta', true);

				if(!empty($order)) {
					if($order['customer_id'] == $user_id) {
						$order['payment_method'] = $paymentMethod;
						$_SESSION['order_id'] = wpshop_tools::varSanitizer( $order_id );
						// Store cart in session
						//wpshop_cart::store_cart_in_session($order);
						// Add a payment
						$order['order_payment']['received'][] = array( 'method' => $paymentMethod, 'waited_amount' => $order['order_amount_to_pay_now'], 'status' => 'waiting_payment', 'author' => get_current_user_id() );

						// On enregistre la commande
						update_post_meta($order_id, '_order_postmeta', $order);
						update_post_meta($order_id, '_wpshop_order_customer_id', $user_id);
					}
					else $wpshop->add_error(__('You don\'t own the order', 'wpshop'));
				}
				else $wpshop->add_error(__('The order doesn\'t exist.', 'wpshop'));
			}
			else{
				$order_data = array(
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
					'post_title' => sprintf(__('Order - %s','wpshop'), mysql2date('d M Y\, H:i:s', current_time('mysql', 0), true)),
					'post_status' => 'publish',
					'post_excerpt' => !empty($_POST['order_comments']) ? $_POST['order_comments'] : '',
					'post_author' => $user_id,
					'comment_status' => 'closed'
				);

				// Cart items
				$order_items = array();
				$order_tva = array();

				//$cart = (array)$wpshop_cart->cart;
				if ( !empty($_SESSION['cart']) && !empty( $_SESSION['cart']['shipping_method']) ) {
					$_SESSION['cart']['shipping_method'] = __('Standard shipping method', 'wpshop');
				}
				$cart = (array)$_SESSION['cart'];

				$download_codes = array();

				// Nouvelle commande
				$order_id = wp_insert_post($order_data);
				$_SESSION['order_id'] = $order_id;

				// Cr�ation des codes de t�l�chargement si il y a des produits t�l�chargeable dans le panier
				if ( !empty( $cart['order_items']  ) ) {
				foreach($cart['order_items'] as $c) {
					$product = wpshop_products::get_product_data($c['item_id']);
					/** Check if it's a variation and check the parent product **/
					if ( get_post_type( $c['item_id'] ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
						$parent_def = wpshop_products::get_parent_variation( $c['item_id'] );
						if ( !empty($parent_def) && !empty($parent_def['parent_post_meta']) && !empty($parent_def['parent_post_meta']['is_downloadable_']) ) {
							$product['is_downloadable_'] = $parent_def['parent_post_meta']['is_downloadable_'];
						}
					}
					if(!empty($product['is_downloadable_'])) {
						$download_codes[$c['item_id']] = array('item_id' => $c['item_id'], 'download_code' => uniqid('', true));
					}

				}
				}
				if(!empty($download_codes)) update_user_meta($user_id, '_order_download_codes_'.$order_id, $download_codes);

				// Informations de commande � stocker
				$currency = wpshop_tools::wpshop_get_currency(true);
				$order = array_merge(array(
					'order_key' 			=> NULL,
					'customer_id' 			=> $user_id,
					'order_status' 			=> 'awaiting_payment',
					'order_date' 			=> current_time('mysql', 0),
					'order_shipping_date' 	=> null,
					'order_invoice_ref'		=> '',
					'order_currency' 		=> $currency,
					'order_payment' 		=> array(
					'customer_choice' 		=> array('method' => $paymentMethod),
					'received'				=> array('0' => array('method' => $paymentMethod, 'waited_amount' => $cart['order_amount_to_pay_now'], 'status' =>  'waiting_payment', 'author' => $user_id)),
					'shipping_method'       => ( ( !empty( $_SESSION['shipping_method']) ) ? wpshop_tools::varSanitizer( $_SESSION['shipping_method']) : __('Standard shipping method', 'wpshop') )
					),
				), $cart);

				// Si c'est un devis
				if ( $paymentMethod == 'quotation' ) {
					$order['order_temporary_key'] = wpshop_orders::get_new_pre_order_reference();
				}
				else {
					$order['order_key'] = wpshop_orders::get_new_order_reference();
				}

				/** On enregistre la commande	*/
				update_post_meta($order_id, '_order_postmeta', $order);
				update_post_meta($order_id, '_wpshop_order_customer_id', $order['customer_id']);
				update_post_meta($order_id, '_wpshop_order_shipping_date', $order['order_shipping_date']);
				update_post_meta($order_id, '_wpshop_order_status', $order['order_status']);


				do_action( 'wps_order_extra_save', $order_id );


				/**	Set custmer information for the order	*/
				$shipping_address =  ( !empty($shipping_address_option) && !empty($shipping_address_option['activate']) ) ? ( ( !empty($_SESSION['shipping_address']) ) ? wpshop_tools::varSanitizer($_SESSION['shipping_address']) : $customer_shipping_address_id ) : '';
				$billing_address =  ( !empty($_SESSION['billing_address']) ) ? wpshop_tools::varSanitizer($_SESSION['billing_address']) : $customer_billing_address_id;


				if ( !empty( $billing_address) ) {
					wpshop_orders::set_order_customer_addresses($user_id, $order_id, $shipping_address, $billing_address);
				}

				if ( !empty($_SESSION['shipping_address_to_save']) ) {
					$order_infos_postmeta = get_post_meta($order_id, '_order_info', true);
					$order_infos_postmeta['shipping']['address'] = $_SESSION['shipping_address_to_save'];
					update_post_meta($order_id, '_order_info', $order_infos_postmeta);
					unset( $_SESSION['shipping_address_to_save'] );
				}


				/** Save Coupon use **/
				if ( !empty($_SESSION['cart']['coupon_id']) ) {
					wpshop_coupons::save_coupon_use( $_SESSION['cart']['coupon_id'] );
				}

				/**	Notify the customer as the case	*/
				$user_info = get_userdata($user_id);
				$email = $user_info->user_email;
				$first_name = $user_info->user_firstname ;
				$last_name = $user_info->user_lastname;

				// Envoie du message de confirmation de commande au client
				$order_meta = get_post_meta( $order_id, '_order_postmeta', true);

				$shipping_mode_option = get_option( 'wps_shipping_mode' );
				$shipping_method = ( !empty($order_meta['order_payment']['shipping_method']) && !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) && is_array($shipping_mode_option['modes']) && array_key_exists($order_meta['order_payment']['shipping_method'], $shipping_mode_option['modes'])) ? $shipping_mode_option['modes'][$order_meta['order_payment']['shipping_method']]['name'] : ( (!empty($order_meta['order_payment']['shipping_method']) ) ? $order_meta['order_payment']['shipping_method'] : '' );


				if ( !empty($order_meta) && !empty($order_meta['cart_type']) && $order_meta['cart_type'] == 'quotation' && empty($order_meta['order_key']) ) {
					wpshop_messages::wpshop_prepared_email($email, 'WPSHOP_QUOTATION_CONFIRMATION_MESSAGE', array('order_id' => $order_id,'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'customer_email' => $email, 'order_date' => current_time('mysql', 0), 'order_content' => '', 'order_addresses' => '', 'order_customer_comments' => '', 'order_billing_address' => '', 'order_shipping_address' => '', 'order_shipping_method' => $shipping_method, 'order_personnal_informations' => '') );
				}
				else {
					$email_option = get_option( 'wpshop_emails' );
					if ( empty($email_option['send_confirmation_order_message']) ) {
						$payment_method_option = get_option( 'wps_payment_mode' );
						$order_payment_method = ( !empty($payment_method_option) && !empty($payment_method_option['mode']) && !empty($order_meta['order_payment']['customer_choice']['method']) && !empty($payment_method_option['mode'][$order_meta['order_payment']['customer_choice']['method']])  ) ? $payment_method_option['mode'][$order_meta['order_payment']['customer_choice']['method']]['name'] : $order_meta['order_payment']['customer_choice']['method'];

						wpshop_messages::wpshop_prepared_email($email, 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', array('order_id' => $order_id,'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'customer_email' => $email, 'order_key' => ( ( !empty($order_meta['order_key']) ) ? $order_meta['order_key'] : ''),'order_date' => current_time('mysql', 0),  'order_payment_method' => $order_payment_method, 'order_content' => '', 'order_addresses' => '', 'order_customer_comments' => '', 'order_billing_address' => '', 'order_shipping_address' => '',  'order_shipping_method' => $shipping_method, 'order_personnal_informations' => '' ) );
					}
				}

				if ( empty($_SESSION['pos_addon']) ) {
					self::send_order_email_to_administrator( $order_id, $user_info );
				}


				/** IF Order amount is 0, Finish the Order **/
				if ( $cart['order_amount_to_pay_now'] == 0 ) {
					$order_meta = get_post_meta($order_id, '_order_postmeta', true);
					$payment_status = 'completed';
					$params_array = array (
						'method' =>'free',
						'waited_amount' => $order_meta['order_amount_to_pay_now'],
						'status' =>  'payment_received',
						'author' => $order_meta['customer_id'],
						'payment_reference' => 'FREE_ORDER',
						'date' => current_time('mysql', 0),
						'received_amount' => $order_meta['order_amount_to_pay_now']
					);
					wpshop_payment::check_order_payment_total_amount($order_id, $params_array, $payment_status);
				}
				apply_filters( 'wpshop_finish_order_extra_actions', $order_id);
			}
		endif;
		return $order_id;
	}

	function send_order_email_to_administrator ( $order_id, $customer_infos = ''  ) {
		if ( !empty($order_id) ) {
			$order_infos = get_post_meta($order_id, '_order_postmeta', true);
			//Send email to administrator(s)
			$shop_admin_email_option = get_option('wpshop_emails');
			$shop_admin_email = $shop_admin_email_option['contact_email'];
			$order_tmp_key = '';

			$shipping_mode_option = get_option( 'wps_shipping_mode' );
			$shipping_method = ( !empty($order_infos['order_payment']['shipping_method']) && !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) && is_array($shipping_mode_option['modes']) && array_key_exists($order_infos['order_payment']['shipping_method'], $shipping_mode_option['modes'])) ? $shipping_mode_option['modes'][$order_infos['order_payment']['shipping_method']]['name'] : ( (!empty($order_infos['order_payment']['shipping_method']) ) ? $order_infos['order_payment']['shipping_method'] : '' );


			if( !empty( $order_infos ) && !empty($order_infos['cart_type']) && $order_infos['cart_type'] == 'normal' && !empty($order_infos['order_key']) ){
				$message_type = 'WPSHOP_NEW_ORDER_ADMIN_MESSAGE';
			}
			else {
				$message_type = 'WPSHOP_NEW_QUOTATION_ADMIN_MESSAGE';
				$order_tmp_key = $order_infos['order_temporary_key'];
			}

			$payment_method_option = get_option( 'wps_payment_mode' );
			$order_payment_method = ( !empty($payment_method_option) && !empty($payment_method_option['mode']) && !empty($order_infos['order_payment']['customer_choice']['method']) && !empty($payment_method_option['mode'][$order_infos['order_payment']['customer_choice']['method']])  ) ? $payment_method_option['mode'][$order_infos['order_payment']['customer_choice']['method']]['name'] : $order_infos['order_payment']['customer_choice']['method'];


			wpshop_messages::wpshop_prepared_email( $shop_admin_email, $message_type, array('order_id' => $order_id, 'order_key' => $order_infos['order_key'], 'customer_email' => ( !empty($customer_infos) && !empty($customer_infos->user_email) ) ? $customer_infos->user_email : '' , 'customer_last_name' => ( !empty($customer_infos) && !empty($customer_infos->user_lastname) ) ? $customer_infos->user_lastname : '', 'customer_first_name' => ( !empty($customer_infos) && !empty($customer_infos->user_firstname) ) ? $customer_infos->user_firstname : '', 'order_date' => $order_infos['order_date'], 'order_payment_method' => $order_payment_method, 'order_temporary_key' => $order_tmp_key, 'order_content' => '', 'order_addresses' => '', 'order_customer_comments' => '', 'order_billing_address' => '', 'order_shipping_address' => '','order_shipping_method' => $shipping_method, 'order_personnal_informations' => '' ), array('object_type' => 'order', 'object_id' => $order_id));
		}
	}

	function direct_payment_link( $token, $order_id, $login ) {
		global $wpdb;
		if( !empty($token) && !empty($order_id) && !empty($login) ) {
			/** Verify informations **/
			$query = $wpdb->prepare( 'SELECT * FROM ' .$wpdb->users. ' WHERE user_login = %s AND user_activation_key = %s', $login, $token);
			$user_infos = $wpdb->get_row( $query );
			if( !empty($user_infos) ) {
				/** Connect the user **/
				$secure_cookie = is_ssl() ? true : false;
				wp_set_auth_cookie($user_infos->ID, true, $secure_cookie);

				/** Add order to SESSION **/
				$order_meta = get_post_meta($order_id, '_order_postmeta', true);
				$_SESSION['cart'] = array();
				$_SESSION['cart']['order_items'] = array();
				if ( !empty($order_meta) && !empty( $order_meta['order_items']) ) {
					$wpshop_cart_type = $order_meta['cart_type'];
					foreach( $order_meta['order_items'] as $item ) {;
						$_SESSION['cart']['order_items'][$item['item_id']] = $item;
					}
					$order = wpshop_cart::calcul_cart_information( array() );
					wpshop_cart::store_cart_in_session( $order );
				}
				$_SESSION['order_id'] = $order_id;
				$wpdb->update($wpdb->users, array('user_activation_key' => ''), array('user_login' => $login) );
				wpshop_tools::wpshop_safe_redirect( get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ) );
			}
			else {
				wpshop_tools::wpshop_safe_redirect( get_permalink( wpshop_tools::get_page_id( get_option('wpshop_myaccount_page_id') ) ) );
			}

		}
	}

}