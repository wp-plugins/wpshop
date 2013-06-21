<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * PayPal Standard Payment Gateway
 *
 * Provides a PayPal Standard Payment Gateway.
 *
 * @class 		wpshop_paypal
 * @package		WP-Shop
 * @category	Payment Gateways
 * @author		Eoxia
 */
class wpshop_paypal {

	public function __construct() {
		if(!empty($_GET['paymentListener']) && $_GET['paymentListener']=='paypal') {
			$payment_status = 'denied';
			// read the post from PayPal system and add 'cmd'
			$req = 'cmd=_notify-validate';
			foreach ($_POST as $key => $value) {
				$value = urlencode(stripslashes($value));
				$req .= "&$key=$value";
			}

			// If testing on Sandbox use:
			$paypalMode = get_option('wpshop_paypalMode', null);
			if($paypalMode == 'sandbox') {
				$fp = fsockopen ('ssl://sandbox.paypal.com', 443, $errno, $errstr, 30);
				$host = "www.sandbox.paypal.com";
			}
			else {
				$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
				$host = "www.paypal.com";
			}

			// post back to PayPal system to validate
			$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Host: " . $host . "\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

			/* Variables */
			$customer_id = $_POST['custom']; // id client
			$shipping = $_POST['mc_shipping']; // frais de livraison
			$business = $_POST['business']; // compte pro
			$order_id = (int)$_POST['invoice']; // num de facture
			$receiver_email = $_POST['receiver_email'];
			$amount_paid = $_POST['mc_gross']; // total (hors frais livraison)
			$txn_id = $_POST['txn_id']; // num�ro de transaction
			$payment_status = $_POST['payment_status']; // status du paiement
			$payer_email = $_POST['payer_email']; // email du client
			$txn_type = $_POST['txn_type'];

			if ( !empty($_POST) ) {
				foreach ( $_POST as $key => $value) {
					if ( substr($key, 0, 9) == 'item_name' ) {
						$_POST[$key] = htmlentities($value);
					}
				}
			}

			/**	Save paypal return data automatically	*/
			wpshop_payment::save_payment_return_data( $order_id );

			$notify_email = get_option('wpshop_paypalEmail', null); // email address to which debug emails are sent to

			if (!$fp){
				echo 'HTTP ERROR!';
			}
			else {
				fputs ($fp, $header.$req);
				while (!feof($fp)) {
					$res = fgets ($fp, 1024);
					if (strcmp ($res, "VERIFIED") == 0) {
						$paypalBusinessEmail = get_option('wpshop_paypalEmail', null);

						/**	Check if payment has been send to good paypal account	*/
						if ($receiver_email == $paypalBusinessEmail) {
							/**	Get the payment transaction identifier	*/
							$paypal_txn_id = wpshop_payment::get_payment_transaction_number( $order_id,  wpshop_payment::get_order_waiting_payment_array_id( $order_id, 'paypal'));

							/**	If no transaction reference has been saved for this order	*/
							if ( empty($paypal_txn_id) ) {
								/**	Set the payment reference for the order	*/
								wpshop_payment::set_payment_transaction_number($order_id, $txn_id);

								/**	Get order content	*/
								$order = get_post_meta($order_id, '_order_postmeta', true);

								/**	Check the different amount : Order total / Paypal paid amount	*/
// 								$amount2pay = floatval($order['order_grand_total']);
								$amount2pay = floatval($order['order_amount_to_pay_now']);
								$amount_paid = floatval($amount_paid);

								/*	Check if the paid amount is equal to the order amount	*/
								if ($amount_paid == sprintf('%0.2f', $amount2pay) ) {
									$payment_status = 'completed';
								}
								else {
									$payment_status = 'incorrect_amount';
								}

							}
							else {
								@mail($notify_email, 'VERIFIED DUPLICATED TRANSACTION', 'VERIFIED DUPLICATED TRANSACTION');
								$payment_status = 'completed';
							}
						}
					}
					// if the IPN POST was 'INVALID'...do this
					elseif (strcmp ($res, "INVALID") == 0) {
						@mail($notify_email, "INVALID IPN", "$res\n $req");
						$payment_status = 'payment_refused';
					}
				}
				fclose($fp);
			}

			$params_array = array('method' => 'paypal',
					'waited_amount' => $order['order_amount_to_pay_now'],
					'status' => ( ($order['order_amount_to_pay_now'] == $_POST['mc_gross']) ? 'payment_received' : 'incorrect_amount' ),
					'author' => $order['customer_id'],
					'payment_reference' => $txn_id,
					'date' => current_time('mysql', 0),
					'received_amount' => $_POST['mc_gross']);
			wpshop_payment::check_order_payment_total_amount($order_id, $params_array, $payment_status);

		}


	}

	/**
	* Display the paypal form in order to redirect correctly to paypal
	*/
	function display_form($oid) {
		global $wpdb;
		$order = get_post_meta($oid, '_order_postmeta', true);

		// If the order exist
		if(!empty($order)) {

			$paypalBusinessEmail = get_option('wpshop_paypalEmail', null);

			// Si l'email Paypal n'est pas vide
			if(!empty($paypalBusinessEmail)) {

				$paypalMode = get_option('wpshop_paypalMode', null);
				if($paypalMode == 'sandbox') $paypal = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				else $paypal = 'https://www.paypal.com/cgi-bin/webscr';

				$current_currency = get_option('wpshop_shop_default_currency');
				$query = $wpdb->prepare('SELECT code_iso FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id =%d ', $current_currency );
				$currency = $wpdb->get_var($query);

				$output  = '<script type="text/javascript">jQuery(document).ready(function(){ jQuery("#paypalForm").submit(); });</script>';
				$output .= '<div class="paypalPaymentLoading"><span>' . __('Redirecting to paypal. Please wait', 'wpshop') . '</span></div>';
				$output .= '
						<form action="'.$paypal.'" id="paypalForm" method="post">
						<input id="cmd" name="cmd" type="hidden" value="_cart" />
						<input id="upload" name="upload" type="hidden" value="1" />
						<input id="charset" name="charset" type="hidden" value="utf-8" />
						<input id="no_shipping" name="no_shipping" type="hidden" value="1" />
						<input id="no_note" name="no_note" type="hidden" value="0" />
						<input id="rm" name="rm" type="hidden" value="0" />

						<input id="custom" name="custom" type="hidden" value="'.$order['customer_id'].'" />
						<input id="invoice" name="invoice" type="hidden" value="'.$oid.'" /> <!-- Invoice number -->
						<input id="business" name="business" type="hidden" value="'.$paypalBusinessEmail.'" /> <!-- Paypal business account -->
						<input id="cbt" name="cbt" type="hidden" value="' . __('Back to shop', 'wpshop') . '" />
						<input id="lc" name="lc" type="hidden" value="FR" />
						<input id="currency_code" name="currency_code" type="hidden" value="'.$currency.'" />

						<input id="return" name="return" type="hidden" value="'.wpshop_payment::get_success_payment_url().'" />
						<input id="cancel_return" name="cancel_return" type="hidden" value="'.wpshop_payment::get_cancel_payment_url().'" />
						<input id="notify_url" name="notify_url" type="hidden" value="'.wpshop_payment::construct_url_parameters(trailingslashit(home_url()), 'paymentListener', 'paypal').'" />
				';

				$i=0;
				if ( !empty( $order['order_partial_payment']) && !empty($order['order_partial_payment']['amount_of_partial_payment']) ) {
					$i++;
					$output .=	'
									<input id="item_number_'.$i.'" name="item_number_'.$i.'" type="hidden" value="' .$oid. '_partial_payment" />
									<input id="item_name_'.$i.'" name="item_name_'.$i.'" type="hidden" value="'.__('Partial payment', 'wpshop').' (' .__('Order number', 'wpshop'). ' : ' .$order['order_key']. ')" />
									<input id="quantity_'.$i.'" name="quantity_'.$i.'" type="hidden" value="1" />
									<input id="amount_'.$i.'" name="amount_'.$i.'" type="hidden" value="'.number_format($order['order_amount_to_pay_now'], 2, '.', '').'" />
									';
				}
				else {
					$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );
					$order_amount = 0;
					foreach ($order['order_items'] as $c) :
						$i++;
						if ( !empty($price_piloting_option) && $price_piloting_option == 'TTC' ) {
							$output .=	'
										<input id="item_number_'.$i.'" name="item_number_'.$i.'" type="hidden" value="'.$c['item_id'].'" />
										<input id="item_name_'.$i.'" name="item_name_'.$i.'" type="hidden" value="'.htmlentities($c['item_name'], ENT_QUOTES, 'UTF-8').'" />
										<input id="quantity_'.$i.'" name="quantity_'.$i.'" type="hidden" value="'.$c['item_qty'].'" />
										<input id="amount_'.$i.'" name="amount_'.$i.'" type="hidden" value="'.number_format( $c['item_pu_ttc'], 2, '.', '').'" />';
						}
						$order_amount += $c['item_total_ttc'];
					endforeach;
					
					if ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) {
						$i = 1;
						$output .=	'
									<input id="item_number_'.$i.'" name="item_number_'.$i.'" type="hidden" value="'.$order['order_key'].'" />
									<input id="item_name_'.$i.'" name="item_name_'.$i.'" type="hidden" value="'.sprintf(__('Order - %s', 'wpshop'), $order['order_key']).'" />
									<input id="quantity_'.$i.'" name="quantity_'.$i.'" type="hidden" value="1" />
									<input id="amount_'.$i.'" name="amount_'.$i.'" type="hidden" value="'.number_format($order_amount, 2, '.', '').'" />
									';
						
					}
				}

				/*
					<input id="shipping_1" name="shipping_1" type="hidden" value="' . $order['order_shipping_cost'] . '" />
				*/
				$shipping_option = get_option('wpshop_shipping_address_choice');
				if (!empty($shipping_option['activate']) && $shipping_option['activate']) {
					$output .= '
							   <input id="item_number_'.($i+1).'" name="item_number_'.($i+1).'" type="hidden" value="wps_cart_shipping_cost" />
							   <input id="item_name_'.($i+1).'" name="item_name_'.($i+1).'" type="hidden" value="' . __('Shipping cost', 'wpshop') . '" />
							   <input id="quantity_'.($i+1).'" name="quantity_'.($i+1).'" type="hidden" value="1" />
							   <input id="amount_'.($i+1).'" name="amount_'.($i+1).'" type="hidden" value="'.( ( !empty($order['order_tva']) && !empty($order['order_tva']['VAT_shipping_cost']) ) ? number_format( ($order['order_shipping_cost'] + $order['order_tva']['VAT_shipping_cost']), 2, '.', '' ) : number_format($order['order_shipping_cost'], 2, '.', '') ).'" />';

				}

				$output .=	'<noscript><input type="submit" value="' . __('Checkout', 'wpshop') . '" /></noscript></form>';
			}
		}

		echo $output;
	}
}

?>