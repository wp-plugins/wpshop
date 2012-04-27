<?php
/**
 * Wpshop Payment Gateway
 *
 * @class 		wpshop_payment
 * @package		WP-Shop
 * @category	Payment Gateway
 * @author		Eoxia
 */
class wpshop_payment {
		
	public function __construct() { 
		global $wpshop;
		
		$wpshop_paypal = new wpshop_paypal();
		// If the CIC payment method is active
		if(WPSHOP_PAYMENT_METHOD_CIC) {
			$wpshop_cic = new wpshop_CIC();
		}
		
	}
	
	/** Display the list of payment methods available */
	function display_payment_methods_choice_form() {
	
		// On récupère les méthodes de paiements disponibles
		$paymentMethod = get_option('wpshop_paymentMethod', array());
			
		echo '<form method="post" name="checkoutForm" action="'.get_permalink(get_option('wpshop_checkout_page_id')).'">';
		
			if(!empty($paymentMethod['paypal'])) {
				echo '<table class="blockPayment active">';
				echo '<tr>';
				echo '<td class="paymentInput rounded-left"><input type="radio" name="modeDePaiement" checked="checked" value="paypal" /></td>';
				echo '<td class="paymentImg"><img src="'.WPSHOP_TEMPLATES_URL.'wpshop/medias//paypal.png" alt="Paypal" title="Payer avec Paypal" /></td>';
				echo '<td class="paymentName">Paypal</td>';
				echo '<td class="last rounded-right">'.__('<strong>Tips</strong> : If you have a Paypal account, by choosing this payment method, you will be redirected to the secure payment site Paypal to make your payment. Debit your PayPal account, immediate booking products.','wpshop').'</td>';
				echo '</tr>';
				echo '</table>';
			}
			
			if(!empty($paymentMethod['checks'])) {
				$active_check = $paymentMethod['paypal'] ? false : true;
				echo '<table class="blockPayment '.($active_check?'active':null).'">';
				echo '<tr>';
				echo '<td class="paymentInput rounded-left"><input type="radio" name="modeDePaiement" '.($active_check?'checked="checked"':null).' value="check" /></td>';
				echo '<td class="paymentImg"><img src="'.WPSHOP_TEMPLATES_URL.'wpshop/medias//cheque.png" alt="Chèque" title="Payer par chèque" /></td>';
				echo '<td class="paymentName">'.__('Check','wpshop').'</td>';
				echo '<td class="last rounded-right">'.__('Reservation of products upon receipt of the check.','wpshop').'</td>';
				echo '</tr>';
				echo '</table>';
			}
			
			if(WPSHOP_PAYMENT_METHOD_CIC) {
				$active_check = false;
				echo '<table class="blockPayment '.($active_check?'active':null).'">';
				echo '<tr>';
				echo '<td class="paymentInput rounded-left"><input type="radio" name="modeDePaiement" '.($active_check?'checked="checked"':null).' value="cic" /></td>';
				echo '<td class="paymentName" colspan="3">'.__('Credit card','wpshop').'</td>';
				echo '</tr>';
				echo '</table>';
				echo '<br />';
			}
			
			// Si une méthode de paiement est disponible
			if(!empty($paymentMethod['paypal']) || !empty($paymentMethod['checks']) || WPSHOP_PAYMENT_METHOD_CIC) {
				echo '<input type="submit" name="takeOrder" value="'.__('Order', 'wpshop').'" />';
			}
			else echo '<p><strong>'.__('It is impossible to order for the moment','wpshop').'</strong></p>';
			
		echo '</form>';
	}
	
	/**
	* Reduce the stock regarding the order
	*/
	function the_order_payment_is_completed($order_id, $txn_id=null) {
		// Données commande
		$order = get_post_meta($order_id, '_order_postmeta', true);
		
		if(!empty($order) && empty($order['order_invoice_ref'])){
			// Reduction des stock produits
			foreach($order['order_items'] as $o) {
				wpshop_products::reduce_product_stock_qty($o['id'], $o['qty']);
			}
			
			// Generate the billing reference (payment is completed here!!)
			wpshop_orders::order_generate_billing_number($order_id);
			
			$order_info = get_post_meta($order_id, '_order_info', true);
			$email = $order_info['billing']['email'];
			$first_name = $order_info['billing']['first_name'];
			$last_name = $order_info['billing']['last_name'];
			
			// Envoie du message de confirmation de paiement au client
			switch($order['payment_method']) {
				case 'check':
					wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name));
				break;
				
				case 'paypal':
					wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', array('paypal_order_key' => $txn_id, 'customer_first_name' => $first_name, 'customer_last_name' => $last_name));
				break;
				
				default:
					wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name));
			}
		}
	}
	
	/**
	* Set order payment status
	*/
	function setOrderPaymentStatus($order_id, $payment_status) {
		// Données commande
		$order = get_post_meta($order_id, '_order_postmeta', true);
		
		if(!empty($order)) {
			// On stocke la date dans une variable pour réutilisation
			$order['order_status'] = strtolower($payment_status);
			$order['order_payment_date'] = date('Y-m-d H:i:s');
					
			// On met à jour le statut de la commande
			update_post_meta($order_id, '_order_postmeta', $order);
		}
	}

	/**
	* Set payment transaction number
	*/
	function set_payment_transaction_number($post_id){
		$payment_validation = '';
		$display_button = false;

		$order_postmeta = get_post_meta($post_id, '_order_postmeta', true);
		switch($order_postmeta['payment_method']){
			case 'check':
				$transaction_indentifier = get_post_meta($post->ID, '_order_check_number', true);
			break;
			case 'paypal':
				$transaction_indentifier = get_post_meta($post->ID, '_order_paypal_txn_id', true);
			break;
			case 'cic':
				$transaction_indentifier = get_post_meta($post->ID, '_order_cic_txn_id', true);
			break;
			default:
				$transaction_indentifier = 0;
			break;
		}

		$paymentMethod = get_option('wpshop_paymentMethod', array());
		$payment_validation .= '
<div id="order_payment_method_'.$post_id.'" class="clear wpshopHide" >
	<input type="hidden" id="used_method_payment_'.$post_id.'" value="' . (!empty($payment_method) ? $payment_method : 'no_method') . '"/>
	<input type="hidden" id="used_method_payment_transaction_id_'.$post_id.'" value="' . (!empty($payment_transaction) ? $payment_transaction : 0) . '"/>';
		if(!empty($order_postmeta['payment_method'])){
			$payment_validation .= sprintf(__('Selected payment method: %s', 'wpshop'), __($order_postmeta['payment_method'], 'wpshop')) . '<br/>';
		}

		if(!empty($paymentMethod['paypal']) && empty($order_postmeta['payment_method'])) {
			$payment_validation .= '<input type="radio" class="payment_method" name="payment_method" value="paypal" id="payment_method_paypal" /><label for="payment_method_paypal" >' . __('Paypal', 'wpshop') . '</label><br/>';
			$display_button = true;
		}

		if(!empty($paymentMethod['checks']) && empty($order_postmeta['payment_method'])) {
			$payment_validation .= '<input type="radio" class="payment_method" name="payment_method" value="check" id="payment_method_check" /><label for="payment_method_check" >' . __('Check', 'wpshop') . '</label><br/>';
			$display_button = true;
		}

		if(WPSHOP_PAYMENT_METHOD_CIC && empty($order_postmeta['payment_method'])) {
			$payment_validation .= '<input type="radio" class="payment_method" name="payment_method" value="cb" id="payment_method_cb" /><label for="payment_method_cb" >' . __('Credit card', 'wpshop') . '</label><br/>';
			$display_button = true;
		}

		if(empty($payment_transaction)){
			$payment_validation .= '<hr/>' . __('Transaction number', 'wpshop') . '&nbsp;:&nbsp;<input type="text" value="" name="payment_method_transaction_number" id="payment_method_transaction_number_'.$post_id.'" />';
			$display_button = true;
		}

		if($display_button){
			$payment_validation .= '
		<br/><br/><a class="button payment_method_validate order_'.$post_id.' clear" >'.__('Validate payment method', 'wpshop').'</a>';
		}
		
		$payment_validation .= '
</div>';

		return $payment_validation;
	}

}
?>