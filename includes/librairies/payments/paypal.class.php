<?php
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
		global $wpshop;
		
		if(!empty($_GET['paymentListener']) && $_GET['paymentListener']=='paypal') {
		
			// read the post from PayPal system and add 'cmd'
			$req = 'cmd=_notify-validate';
			foreach ($_POST as $key => $value) {
				$value = urlencode(stripslashes($value));
				$req .= "&$key=$value";
			}

			// post back to PayPal system to validate
			$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

			// If testing on Sandbox use:
			$paypalMode = get_option('wpshop_paypalMode', null);
			if($paypalMode == 'sandbox') {
				$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
			}
			else {
				$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
			}

			/* Variables */
			$customer_id = $_POST['custom']; // id client
			$shipping = $_POST['mc_shipping']; // frais de livraison
			$business = $_POST['business']; // compte pro
			$order_id = (int)$_POST['invoice']; // num de facture
			$receiver_email = $_POST['receiver_email'];
			$amount_paid = $_POST['mc_gross']; // total (hors frais livraison)
			$txn_id = $_POST['txn_id']; // numéro de transaction
			$payment_status = $_POST['payment_status']; // status du paiement
			$payer_email = $_POST['payer_email']; // email du client
			$txn_type = $_POST['txn_type'];

			$notify_email =  ''; // email address to which debug emails are sent to
			//@mail($notify_email, 'Payment Paypal', 'step3'); // temporaire

			if (!$fp) echo 'HTTP ERROR!';
			else {
				fputs ($fp, $header.$req);
				while (!feof($fp)) {
					$res = fgets ($fp, 1024);
					
					if (strcmp ($res, "VERIFIED") == 0) { 
					
						$paypalBusinessEmail = get_option('wpshop_paypalEmail', null);
						
						// On vérifie que le paiement est envoyé à la bonne adresse email
						if ($receiver_email == $paypalBusinessEmail) { 
						
							// On cherche à récupérer l'id de la transaction
							$paypal_txn_id = get_post_meta($order_id, '_order_paypal_txn_id', true);
							
							// Si la transaction est unique
							if (empty($paypal_txn_id)) { 
							
								// On enregistre l'id unique de la transaction
								update_post_meta($order_id, '_order_paypal_txn_id', $txn_id);
								// Données commande
								$order = get_post_meta($order_id, '_order_postmeta', true);
								// On parse les montant afin de pouvoir les comparer correctement
								$amount2pay = floatval($order['order_total']);
								$amount_paid = floatval($amount_paid);
								
								// On vérifie que le montant payé correspond au montant A payer..
								if ($amount_paid == $amount2pay ) {
								
									// On vérifie que le statut du paiement est OK
									if ($payment_status == 'Completed') {
										
										// Reduction des stock produits
										foreach($order['order_items'] as $o) {
											wpshop_products::reduce_product_stock_qty($o['id'], $o['qty']);
										}
										
										$order_info = get_post_meta($order_id, '_order_info', true);
										$email = $order_info['billing']['email'];
										$first_name = $order_info['billing']['first_name'];
										$last_name = $order_info['billing']['last_name'];
										
										// Envoie du message de confirmation de paiement au client
										/*$title = sprintf(__('Order payment confirmation (Paypal id %s)', 'wpshop'), $txn_id);
										$message = sprintf(__('Hello %s %s, this email confirms that your payment about your recent order on our website has been completed. Thank you for your loyalty. Have a good day.', 'wpshop'), $first_name, $last_name);
										@mail($email, $title, $message);*/
										wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', array('paypal_order_key' => $txn_id, 'customer_first_name' => $first_name, 'customer_last_name' => $last_name));
									}
								}
								
								// On stocke la date dans une variable pour réutilisation
								$order['order_status'] = strtolower($payment_status);
								$order['order_payment_date'] = date('Y-m-d H:i:s');
										
								// On met à jour le statut de la commande
								update_post_meta($order_id, '_order_postmeta', $order);
							}
							else {
								@mail($notify_email, 'VERIFIED DUPLICATED TRANSACTION', 'VERIFIED DUPLICATED TRANSACTION');
							}
						}
						exit;
					}
					// if the IPN POST was 'INVALID'...do this
					elseif (strcmp ($res, "INVALID") == 0) {
						@mail($notify_email, "INVALID IPN", "$res\n $req");
					}
				}
				fclose ($fp);
			}
		}
	}
}
?>