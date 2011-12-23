<?php
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
	$wpshop_checkout = &new wpshop_checkout();
	$wpshop_checkout->display_form();
}

class wpshop_checkout {
	
	var $div_register, $div_infos_register, $div_login, $div_infos_login = 'display:block;';
	var $creating_account = true;
	
	/** Constructor of the class
	* @return void
	*/
	function __construct () {
	}
	
	/** Affiche le formulaire de commande
	* @return void
	*/
	function display_form() {
	
		global $wpshop, $wpshop_account;
	
		if(!empty($_GET['action']) && $_GET['action']=='cancel') {
			// On vide le panier
			$GLOBALS['cart']->empty_cart();
			echo __('Your order has been succesfully cancelled.', 'wpshop');
			return false;
		}
		
		// Si le panier n'est pas vide
		if($GLOBALS['cart']->is_empty()) :
			echo '<p>'.__('Your cart is empty. Select product(s) before checkout.','wpshop').'</p>';
		else :
		
			$user_id = get_current_user_id();
			$this->managePost();
			
			// On récupère les méthodes de paiements disponibles
			$paymentMethod = get_option('wpshop_paymentMethod', array());
			
			// PAYPAL
			if(!empty($paymentMethod['paypal']) && isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='paypal') {
			
				$paypalBusinessEmail = get_option('wpshop_paypalEmail', null);
			
				// Si l'email Paypal n'est pas vide
				if(!empty($paypalBusinessEmail)) {
				
					$paypalMode = get_option('wpshop_paypalMode', null);
					if($paypalMode == 'sandbox') {
						$paypal = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
					}
					else {
						$paypal = 'https://www.paypal.com/cgi-bin/webscr';
					}
					
					// Url de retour après paiement
					$return_url = get_permalink(get_option('wpshop_myaccount_page_id'));
					
					echo '<script type="text/javascript">jQuery(document).ready(function(){ jQuery(\'#paypalForm\').submit(); });</script>';
					echo '<div class="paypalPaymentLoading"><span>Redirection vers le site de Paypal en cours...</span></div>';
					echo '
						<form action="'.$paypal.'" id="paypalForm" method="post">
							<input id="cmd" name="cmd" type="hidden" value="_cart" />
							<input id="upload" name="upload" type="hidden" value="1" />
							<input id="charset" name="charset" type="hidden" value="utf-8" />
							<input id="no_shipping" name="no_shipping" type="hidden" value="1" />
							<input id="no_note" name="no_note" type="hidden" value="0" />
							<input id="rm" name="rm" type="hidden" value="0" />
							
							<input id="custom" name="custom" type="hidden" value="'.$user_id.'" />
							<input id="invoice" name="invoice" type="hidden" value="'.$_SESSION['order_id'].'" /> <!-- Numéro de facture -->
							<input id="business" name="business" type="hidden" value="'.$paypalBusinessEmail.'" /> <!-- compte business -->
							<input id="cbt" name="cbt" type="hidden" value="Retourner sur le magasin" />
							<input id="lc" name="lc" type="hidden" value="FR" />
							<input id="currency_code" name="currency_code" type="hidden" value="EUR" />
							
							<input id="return" name="return" type="hidden" value="'.$return_url.'" />
							<input id="cancel_return" name="cancel_return" type="hidden" value="'.wpshop_cart::get_checkout_url().'?action=cancel" />
							<input id="notify_url" name="notify_url" type="hidden" value="'.trailingslashit(home_url()).'?paymentListener=paypal" />
					';
				
					$i=0;
					foreach ($_SESSION['cart']['content'] as $cart) :
						$i++;
					
						echo '
							<input id="item_number_'.$i.'" name="item_number_'.$i.'" type="hidden" value="'.$cart['product_id'].'" />
							<input id="item_name_'.$i.'" name="item_name_'.$i.'" type="hidden" value="'.$cart['data']['product_name'].'" />
							<input id="quantity_'.$i.'" name="quantity_'.$i.'" type="hidden" value="'.$cart['quantity'].'" />
							<input id="amount_'.$i.'" name="amount_'.$i.'" type="hidden" value="'.sprintf('%0.2f', $cart['data']['product_price']).'" />
						';
						
					endforeach;

					echo '
							<input id="shipping_1" name="shipping_1" type="hidden" value="'.$_SESSION['total_ship'].'" />
							<noscript><input type="submit" value="Checkout" /></noscript>
						</form>
					';
					
					// On vide le panier
					$GLOBALS['cart']->empty_cart();
				}
			}
			// CHECK
			elseif(!empty($paymentMethod['checks']) && isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='check') {
				
				// On récupère les informations de paiements par chèque
				$paymentInfo = get_option('wpshop_paymentAddress', true);
				echo '<p>'.__('Thank you ! Your order has been placed and you will receive a confirmation email shortly.', 'wpshop').'</p>';
				echo '<p>'.__('You have to send the check with the good amount to the adress :', 'wpshop').'</p>';
				echo $paymentInfo['company_name'].'<br />';
				echo $paymentInfo['company_street'].'<br />';
				echo $paymentInfo['company_postcode'].', '.$paymentInfo['company_city'].'<br />';
				echo $paymentInfo['company_country'].'<br /><br />';
				echo '<p>'.__('Your order will be shipped upon receipt of the check.', 'wpshop').'</p>';
				
				// On vide le panier
				$GLOBALS['cart']->empty_cart();
			}
			else {
			
				if($user_id) {
					global $current_user;
					get_currentuserinfo();
					$shipping_info = get_user_meta($current_user->ID, 'shipping_info', true);
					$billing_info = get_user_meta($current_user->ID, 'billing_info', true);
					
					// Si il n'y pas d'info de livraison et de facturation on redirectionne l'utilisateur
					if(empty($shipping_info) || empty($billing_info)) {
						wp_safe_redirect(get_permalink(get_option('wpshop_myaccount_page_id')).'?action=editinfo&return=checkout');
						exit;
					}
					
					echo '<form method="post" name="checkoutForm">';
					
						echo '<p>'.sprintf(__('Hi <strong>%s</strong>, you would like to take an order :','wpshop'), $current_user->user_login).'</p>';
						
						echo '<div class="half">';
						echo '<h2>'.__('Shipping address', 'wpshop').'</h2>';
						echo $shipping_info['first_name'].' '.$shipping_info['last_name'].'<br />';
						echo $shipping_info['address'].'<br />';
						echo $shipping_info['postcode'].', '.$shipping_info['city'].'<br />';
						echo $shipping_info['country'];
						echo '</div>';
						
						echo '<div class="half">';
						echo '<h2>'.__('Billing address', 'wpshop').'</h2>';
						echo $billing_info['first_name'].' '.$billing_info['last_name'].'<br />';
						echo $billing_info['address'].'<br />';
						echo $billing_info['postcode'].', '.$billing_info['city'].'<br />';
						echo $billing_info['country'];
						echo '</div>';
						
						echo '<p><a href="'.get_permalink(get_option('wpshop_myaccount_page_id')).'?action=editinfo&amp;return=checkout" title="'.__('Edit shipping & billing info...', 'wpshop').'">'.__('Edit shipping & billing info...', 'wpshop').'</a></p>';
						
						echo '<h2>'.__('Summary of the order','wpshop').'</h2>';
						$GLOBALS['cart']->display_cart($hide_button=true);
						
						if(!empty($paymentMethod['paypal'])) {
							echo '<table class="blockPayment active">';
							echo '<tr>';
							echo '<td class="paymentInput rounded-left"><input type="radio" name="modeDePaiement" checked="checked" value="paypal" /></td>';
							echo '<td class="paymentImg"><img src="'.WPSHOP_TEMPLATES_URL.'wpshop/medias/icones/paypal.png" alt="Paypal" title="Payer avec Paypal" /></td>';
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
							echo '<td class="paymentImg"><img src="'.WPSHOP_TEMPLATES_URL.'wpshop/medias/icones/cheque.png" alt="Chèque" title="Payer par chèque" /></td>';
							echo '<td class="paymentName">'.__('Check','wpshop').'</td>';
							echo '<td class="last rounded-right">'.__('Reservation of products upon receipt of the check.','wpshop').'</td>';
							echo '</tr>';
							echo '</table>';
							echo '<br />';
						}
						
						// Si une méthode de paiement est disponible
						if(!empty($paymentMethod['paypal']) || !empty($paymentMethod['checks'])) {
							echo '<input type="submit" name="takeOrder" value="'.__('Order', 'wpshop').'" />';
						}
						else echo '<p><strong>'.__('It is impossible to order for the moment','wpshop').'</strong></p>';
					
					echo '</form>';
				}
				
				if(!$user_id) :
				
					echo '<div class="infos_bloc" id="infos_register" style="'.$this->div_infos_register.'">'.__('Already registered? <a href="#" class="checkoutForm_login">Please login</a>.','wpshop').'</div>';
					echo '<div class="infos_bloc" id="infos_login" style="'.$this->div_infos_login.'">'.__('Not already registered? <a href="#" class="checkoutForm_login">Please register</a>.','wpshop').'</div>';
					
					echo '<form method="post" name="checkoutForm">';
					
						// Bloc REGISTER
						echo '<div class="col1" id="register" style="'.$this->div_register.'">';
					
							$wpshop_account->display_billing_and_shipping_form_field();
							
							echo '<div class="create-account">';
								echo '<p>'.__('Create an account by entering the information below. If you are a returning customer please login with your username at the top of the page.', 'wpshop').'</p>'; 
								$wpshop->display_field('account_username', array('type' => 'text', 'label' => __('Account username', 'wpshop'), 'placeholder' => __('Username', 'wpshop'), 'required' => true));
								$wpshop->display_field('account_password', array('type' => 'password', 'label' => __('Account password', 'wpshop'), 'placeholder' => __('Password', 'wpshop'), 'required' => true, 'class' => array('form-row-first')));
								$wpshop->display_field('account_password-2', array('type' => 'password', 'label' => __('Retype the password', 'wpshop'), 'placeholder' => __('Password', 'wpshop'), 'class' => array('form-row-last'), 'required' => true, 'label_class' => array('hidden')));
							echo '</div>';
					
							echo '<input type="submit" name="submitOrderInfos" value="'.__('Take order','wpshop').'"" />';
							
						echo '</div>';
						
					echo '</form>';
					
					echo '<form method="post" name="checkoutForm_login">';
					
						// Bloc LOGIN
						echo '<div class="col1" id="login" style="'.$this->div_login.'">';
						
							echo '<div class="create-account">'; 
								$wpshop_account->display_login_form();
							echo '</div>';
				
							echo '<input type="submit" name="submitLoginInfos" value="'.__('Login and order','wpshop').'" />';
				
						echo '</div>';
						
					echo '</form>';	
					
				endif;
			}
		endif;
	}
	
	/** Traite les données reçus en POST
	 * @return void
	*/
	function managePost() {
	
		global $wpshop, $wpshop_account;
		
		// Nouveau compte client
		if(isset($_POST['submitOrderInfos'])) {
			$this->div_login = $this->div_infos_login = 'display:none';
			
			if($wpshop->validateForm($wpshop_account->billing_fields)) {
				
				if(isset($_POST['shiptobilling']) || (!isset($_POST['shiptobilling']) && $wpshop->validateForm($wpshop_account->shipping_fields))) {
				
					if ($this->new_customer_account()) {
						wp_safe_redirect(get_permalink(get_option('wpshop_checkout_page_id')));
						exit;
					}
				}
			}
		}
		// Connexion
		elseif(isset($_POST['submitLoginInfos'])) {
			$this->div_register = $this->div_infos_register = 'display:none';
			
			if($wpshop->validateForm($wpshop_account->login_fields)) {
			
				// On connecte le client
				if($wpshop_account->isRegistered($_POST['account_email'], $_POST['account_password'], true)) {
					wp_safe_redirect(get_permalink(get_option('wpshop_checkout_page_id')));
					exit;
				}
			}
		}
		// Confirmation (dernière étape)
		elseif(isset($_POST['takeOrder'])) {
			// Paypal
			if(isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='paypal') {
				$this->process_checkout($paymentMethod='paypal');
			}
			// Chèque
			elseif(isset($_POST['modeDePaiement']) && $_POST['modeDePaiement']=='check') {
				$this->process_checkout($paymentMethod='check');
			}
			else $wpshop->add_error(__('You have to choose a payment method to continue.', 'wpshop'));
			
		}
		else {
			$this->div_login = $this->div_infos_login = 'display:none';
		}
		
		// Si il y a des erreurs
		if($wpshop->error_count()>0) {
			echo $wpshop->show_messages();
			return false;
		}
		else return true;
	}
	
	function new_customer_account() {
	
		global $wpdb, $wpshop, $wpshop_account;
		
			// Checkout fields (non-shipping/billing)
			$this->posted['terms'] 				= isset($_POST['terms']) ? 1 : 0;
			$this->posted['createaccount'] 		= true;
			$this->posted['payment_method'] 	= isset($_POST['payment_method']) ? wpshop_tools::wpshop_clean($_POST['payment_method']) : '';
			$this->posted['shipping_method']	= isset($_POST['shipping_method']) ? wpshop_tools::wpshop_clean($_POST['shipping_method']) : '';
			//$this->posted['order_comments'] 	= isset($_POST['order_comments']) ? wpshop_tools::wpshop_clean($_POST['order_comments']) : '';
			$this->posted['account_username']	= isset($_POST['account_username']) ? wpshop_tools::wpshop_clean($_POST['account_username']) : '';
			$this->posted['account_password'] 	= isset($_POST['account_password']) ? wpshop_tools::wpshop_clean($_POST['account_password']) : '';
			$this->posted['account_password-2'] = isset($_POST['account_password-2']) ? wpshop_tools::wpshop_clean($_POST['account_password-2']) : '';
			$this->posted['billing_email'] = isset($_POST['billing_email']) ? wpshop_tools::wpshop_clean($_POST['billing_email']) : null;	
				
			if (empty($this->posted['account_password'])) $wpshop->add_error(__('Please enter an account password.', 'wpshop'));
			if ($this->posted['account_password-2'] !== $this->posted['account_password']) $wpshop->add_error(__('Passwords do not match.', 'wpshop'));
				
			// Check the username
			if (empty($this->posted['account_username'])) :
				$wpshop->add_error(__('Please enter an account username.', 'wpshop'));
			elseif (!validate_username($this->posted['account_username'])) :
				$wpshop->add_error( __('Invalid email/username.', 'wpshop') );
			elseif (username_exists($this->posted['account_username'])) :
				$wpshop->add_error( __('An account is already registered with that username. Please choose another.', 'wpshop') );
			endif;
						
			// Check the e-mail address
			if (email_exists($this->posted['billing_email'])) :
				$wpshop->add_error( __('An account is already registered with your email address. Please login.', 'wpshop') );
			endif;
				
			// Si il n'y a pas d'erreur
			if ($wpshop->error_count()==0) :
					
				while (1) : //break;
					
					/** Création compte client */
					$reg_errors = new WP_Error();
					do_action('register_post', $this->posted['billing_email'], $this->posted['billing_email'], $reg_errors);
					$errors = apply_filters('registration_errors', $reg_errors, $this->posted['billing_email'], $this->posted['billing_email']);
							
					// if there are no errors, let's create the user account
					if (!$reg_errors->get_error_code()) :
					
						$user_pass = $this->posted['account_password'];
						$user_id = wp_create_user($this->posted['account_username'], $user_pass, $this->posted['billing_email']);
						if (!$user_id) {
							$wpshop->add_error(sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', 'woothemes'), get_option('admin_email')));
							break;
						}
						// Change role
						wp_update_user(array('ID' => $user_id, 'role' => 'customer'));
						// send the user a confirmation and their login details
						wp_new_user_notification($user_id, $user_pass);
						// set the WP login cookie
						$secure_cookie = is_ssl() ? true : false;
						wp_set_auth_cookie($user_id, true, $secure_cookie);
									
					else :
						$wpshop->add_error($reg_errors->get_error_message());
						break;                    
					endif;
					
					// Récupere les données en POST et enregistre les infos de livraison et facturation
					$wpshop_account->save_billing_and_shipping_info($user_id);
				
					return true;
					
					// On casse la boucle
					break;
					
				endwhile;
				
				return false;
				
			endif;
	}
	
	/** Enregistre la commande dans la bdd après que les champs aient été validé, ou que l'utilisateur soit connecté
	 * @param int $user_id=0 : id du client passant commande. Par défaut 0 pour un nouveau client
	 * @return void
	*/
	function process_checkout($paymentMethod='paypal') {
	
		global $wpdb, $wpshop;
		
		if (is_user_logged_in()) :
		
			$user_id = get_current_user_id();
		
			$order_data = array(
				'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
				'post_title' => sprintf(__('Order - %s','wpshop'), date('d M Y, H:i:s')),
				'post_status' => 'publish',
				/*'post_excerpt' => $this->posted['order_comments'],*/
				'post_author' => $user_id,
				'comment_status' => 'closed'
			);
			
			// Cart items
			$order_items = array();
			
			// Boucle sur les produits du panier
			foreach ($_SESSION['cart']['content'] as $cart) :
				$order_items[] = array(
					'id' => $cart['product_id'],
					'name' => $cart['data']['product_name'],
					'qty' => (int) $cart['quantity'],
					'cost' => number_format($cart['data']['product_price'], 2, '.', '')
				);
			endforeach;
			
			// Nouvelle commande
			$order_id = wp_insert_post($order_data);
			$_SESSION['order_id'] = $order_id;
			
			// Informations de commande à stocker
			$order = array(
				'order_key' => uniqid('order_'),
				'customer_id' => $user_id,
				'order_status' => 'awaiting_payment',
				'order_date' => date('Y-m-d H:i:s'),
				'order_payment_date' => null,
				'order_shipping_date' => null,
				'payment_method' => $paymentMethod,
				'order_currency' => 'EUR',
				'order_subtotal' => number_format($_SESSION['cart']['subtotal'], 2, '.', ''),
				'order_shipping' => '0',
				'order_total' => number_format($_SESSION['cart']['total'], 2, '.', ''),
				'order_items' => $order_items
			);
			
			// On enregistre la commande
			update_post_meta($order_id, '_order_postmeta', $order);
			
			// On récupére les infos de facturation et de livraison
			$shipping_info = get_user_meta($user_id, 'shipping_info', true);
			$billing_info = get_user_meta($user_id, 'billing_info', true);
			
			$email = $billing_info['email'];
			$first_name = $billing_info['first_name'];
			$last_name = $billing_info['last_name'];
										
			// Envoie du message de confirmation de commande au client
			$title = __('Your order has been shipped', 'wpshop');
			$message = sprintf(__('Hello %s %s, this email confirms that your order has been recorded. Thank you for your loyalty. Have a good day.', 'wpshop'), $first_name, $last_name);
			@mail($email, $title, $message);
				
			$order_info = array('billing' => $billing_info, 'shipping' => $shipping_info);
			// On enregistre l'adresse de facturation et de livraison
			update_post_meta($order_id, '_order_info', $order_info);
			
		endif;
	}
}