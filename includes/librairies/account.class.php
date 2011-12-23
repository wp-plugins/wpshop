<?php

/* Instantiate the class from the shortcode */
function wpshop_account_display_form() {
	global $wpdb, $wpshop, $wpshop_account, $current_user;
	
	$wpshop_account->managePost();
	
	$user_id = get_current_user_id();
	
	if(!$user_id) :
	
		if(isset($_POST['submitLoginInfos'])) {
			if($wpshop->validateForm($wpshop_account->login_fields)) {
				// On connecte le client
				if($wpshop_account->isRegistered($_POST['account_email'], $_POST['account_password'], true)) {
					wp_safe_redirect(get_permalink(get_option('wpshop_myaccount_page_id')));
					exit;
				}
			}
			
			// Si il y a des erreurs
			if($wpshop->error_count()>0) {
				echo $wpshop->show_messages();
			}
		}
		
		echo '<form method="post">';
			echo '<div class="create-account">'; 
				$wpshop_account->display_login_form();
			echo '</div>';
			echo '<input type="submit" name="submitLoginInfos" value="'.__('Login', 'wpshop').'" />';	
		echo '</form>';
		
	else:
	
		// Order status possibilities
		$order_status = array('awaiting_payment' => __('Awaiting payment', 'wpshop'), 'completed' => __('Paid', 'wpshop'), 'shipped' => __('Shipped', 'wpshop'));
		// Payment method possibilities
		$payment_method = array('paypal' => 'Paypal', 'check' => __('Check','wpshop'));
	
		if(!empty($_GET['action'])) :
		
			// --------------------------
			// Edition infos personnelles
			// --------------------------
			if($_GET['action']=='editinfo') :
				
				$shipping_info = get_user_meta($user_id, 'shipping_info', true);
				$billing_info = get_user_meta($user_id, 'billing_info', true);
				
				// Si il y a des infos à afficher
				if(!empty($shipping_info) && !empty($billing_info)) {
					// On ajoute le préfixe qu'il faut pour que tout soit fonctionnel
					foreach($shipping_info as $k => $v):
						$shipping_info['shipping_'.$k] = $shipping_info[$k];
						$billing_info['billing_'.$k] = $billing_info[$k];
						unset($shipping_info[$k]); unset($billing_info[$k]);
					endforeach;
				}
				else {
					$shipping_info = $billing_info = array('first_name'=>null,'last_name'=>null,'address'=>null,'postcode'=>null,'city'=>null,'country'=>null);
				}
				
				if(empty($_GET['return'])) :
					echo '<h2>'.__('Edit my personal informations','wpshop').'</h2>';
				elseif($_GET['return'] == 'checkout'):
					echo '<div class="infos_bloc" id="infos_register" style="display:block;">'.__('You must to type your billing and shipping info to continue.', 'wpshop').'</div>';
				endif;
				
				echo '<form method="post" name="billingAndShippingForm">';
					$wpshop_account->display_billing_and_shipping_form_field($billing_info, $shipping_info);
					echo '<input type="submit" name="submitbillingAndShippingInfo" value="'.__('Save','wpshop').'" />';
				echo '</form>';
			
			// --------------------------
			// Infos commande
			// --------------------------
			elseif($_GET['action']=='order' && !empty($_GET['oid']) && is_numeric($_GET['oid'])) :
			
				echo '<h2>'.__('Order details','wpshop').'</h2>';
				
				$order_info = get_post_meta($_GET['oid'], '_order_info', true);
				$shipping_info = $order_info['shipping'];
				$billing_info = $order_info['billing'];
				
				echo '<h2>'.__('Shipping & billing info', 'wpshop').'</h2>';
				
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
				echo '</div><br />';
				
				// Données commande
				$order = get_post_meta($_GET['oid'], '_order_postmeta', true);
				
				if(!empty($order)) {
					echo '<div class="order"><div>';
					echo __('Order number','wpshop').' : <strong>'.$order['order_key'].'</strong><br />';
					echo __('Date','wpshop').' : <strong>'.$order['order_date'].'</strong><br />';
					echo __('Total','wpshop').' : <strong>'.number_format($order['order_total'], 2, '.', '').' '.$order['order_currency'].'</strong><br />';
					echo __('Payment method','wpshop').' : <strong>'.$payment_method[$order['payment_method']].'</strong><br />';
					if($order['payment_method']=='paypal'):
						$order_paypal_txn_id = get_post_meta($_GET['oid'], '_order_paypal_txn_id', true);
						echo __('Paypal transaction id', 'wpshop').' : <strong>'.(empty($order_paypal_txn_id)?'Unassigned':$order_paypal_txn_id).'</strong><br />';
					endif;
					echo __('Status','wpshop').' : <strong><span class="status '.$order['order_status'].'">'.$order_status[$order['order_status']].'</span></strong><br />';
					echo __('Tracking number','wpshop').' : '.(empty($order['order_trackingNumber'])?__('none','wpshop'):'<strong>'.$order['order_trackingNumber'].'</strong>').'<br /><br />';
					echo '<strong>'.__('Order content','wpshop').'</strong><br />';
					foreach($order['order_items'] as $o) {
						echo '<span class="right">'.number_format($o['cost'], 2, '.', '').' '.$order['order_currency'].'</span>'.$o['qty'].' x '.$o['name'].'<br />';
					}
					echo '<hr />';
					echo '<span class="right">'.number_format($order['order_subtotal'], 2, '.', '').' '.$order['order_currency'].'</span>'.__('Subtotal','wpshop').'<br />';
					echo '<span class="right">'.(empty($order['order_shipping'])?'<strong>'.__('Free','wpshop').'</strong>':number_format($order['order_shipping'], 2, '.', '').' '.$order['order_currency']).'</span>'.__('Shipping fee','wpshop').'<br />';
					echo '<span class="right"><strong>'.number_format($order['order_total'], 2, '.', '').' '.$order['order_currency'].'</strong></span>'.__('Total','wpshop');
					echo '</div></div>';
				}
				else echo __('No order', 'wpshop');
			
			endif;
		
		// --------------------------
		// Tableau de bord
		// --------------------------
		else :
	
			echo '<a href="'.wp_logout_url(get_permalink(get_option('wpshop_product_page_id'))).'" title="'.__('Logout','wpshop').'" class="right">'.__('Logout','wpshop').'</a>';
			get_currentuserinfo();
			echo '<p>'.sprintf(__('Hi <strong>%s</strong>', 'wpshop'), $current_user->user_login).'.</p>';
			
			$shipping_info = get_user_meta($user_id, 'shipping_info', true);
			$billing_info = get_user_meta($user_id, 'billing_info', true);
			
			echo '<h2>'.__('Default shipping & billing info', 'wpshop').'</h2>';
			
			echo '<div class="half">';
			echo '<h2>'.__('Shipping address', 'wpshop').'</h2>';
			if(!empty($shipping_info)) {
				echo $shipping_info['first_name'].' '.$shipping_info['last_name'].'<br />';
				echo $shipping_info['address'].'<br />';
				echo $shipping_info['postcode'].', '.$shipping_info['city'].'<br />';
				echo $shipping_info['country'];
			}
			else {
				echo '<span style="color:red;">'.__('No data','wpshop').'</span>';
			}
			echo '</div>';
						
			echo '<div class="half">';
			echo '<h2>'.__('Billing address', 'wpshop').'</h2>';
			if(!empty($billing_info)) {
				echo $billing_info['first_name'].' '.$billing_info['last_name'].'<br />';
				echo $billing_info['address'].'<br />';
				echo $billing_info['postcode'].', '.$billing_info['city'].'<br />';
				echo $billing_info['country'];
			}
			else {
				echo '<span style="color:red;">'.__('No data','wpshop').'</span>';
			}
			echo '</div>';
			
			echo '<p><a href="?action=editinfo" title="'.__('Edit shipping & billing info...', 'wpshop').'">'.__('Edit shipping & billing info...', 'wpshop').'</a></p>';
			
			echo '<h2>'.__('Your orders','wpshop').'</h2>';
			
			$orders_id = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'" AND post_author = '.$user_id.' ORDER BY post_date DESC');
			
			if(!empty($orders_id)) {
				$order = array();
				foreach($orders_id as $o) {
					$order[$o->ID] = get_post_meta($o->ID, '_order_postmeta', true);
				}
				
				foreach($order as $k => $o) {
					echo '<div class="order"><div>';
					echo __('Order number','wpshop').' : <strong>'.$o['order_key'].'</strong><br />';
					echo __('Date','wpshop').' : <strong>'.$o['order_date'].'</strong><br />';
					echo __('Total','wpshop').' : <strong>'.number_format($o['order_total'], 2, '.', '').' '.$o['order_currency'].'</strong><br />';
					echo __('Status','wpshop').' : <strong><span class="status '.$o['order_status'].'">'.$order_status[$o['order_status']].'</span></strong><br />';
					echo '<a href="?action=order&oid='.$k.'" title="'.__('More info about this order...', 'wpshop').'">'.__('More info about this order...', 'wpshop').'</a>';
					echo '</div></div>';
				}
			}
			else echo __('No order', 'wpshop');
			
		endif;
	endif;
}

/* Class wpshop_account */
class wpshop_account {

	var $login_fields = array();
	var $billing_fields = array();
	var $shipping_fields = array();
	
	function __construct() {
	
		$this->login_fields = array(
			'account_email' => array( 
				'type'			=> 'email',
				'label' 		=> __('Email Address', 'wpshop'), 
				'placeholder' 	=> __('you@yourdomain.com', 'wpshop'), 
				'required' 		=> true, 
				'class' 		=> array('form-row-first') 
			),
			'account_password' => array( 
				'type' => 'password', 
				'label' => __('Password', 'wpshop'), 
				'placeholder' => __('Password', 'wpshop'),
				'class' => array('form-row-last'), 
				'required' 		=> true,
				'label_class' => array('hidden')
			)
		);
		
		// Define billing fields in an array.
		$this->billing_fields = array(
			'billing_first_name' => array( 
				'name'			=>'billing_first_name', 
				'label' 		=> __('First Name', 'wpshop'), 
				'placeholder' 	=> __('First Name', 'wpshop'), 
				'required' 		=> true, 
				'class'			=> array('form-row-first') 
				),
			'billing_last_name' => array( 
				'label' 		=> __('Last Name', 'wpshop'), 
				'placeholder' 	=> __('Last Name', 'wpshop'), 
				'required' 		=> true, 
				'class' 		=> array('form-row-last') 
				),
			'billing_company' 	=> array( 
				'label' 		=> __('Company', 'wpshop'), 
				'placeholder' 	=> __('Company', 'wpshop') 
				),
			'billing_address' 	=> array( 
				'label' 		=> __('Address', 'wpshop'), 
				'placeholder' 	=> __('Address', 'wpshop'), 
				'required' 		=> true
				),
			'billing_city' 		=> array( 
				'label' 		=> __('City', 'wpshop'), 
				'placeholder' 	=> __('City', 'wpshop'), 
				'required' 		=> true, 
				'class' 		=> array('form-row-first') 
				),
			'billing_postcode' 	=> array( 
				'type'			=> 'postcode',
				'label' 		=> __('Postcode', 'wpshop'), 
				'placeholder' 	=> __('Postcode', 'wpshop'), 
				'required' 		=> true, 
				'class'			=> array('form-row-last update_totals_on_change') 
				),
			'billing_country' 	=> array( 
				'type'			=> 'country', 
				'label' 		=> __('Country', 'wpshop'),
				'placeholder' 	=> __('Country', 'wpshop'),
				'required' 		=> true, 
				'class' 		=> array('form-row-first update_totals_on_change')
				),
			'billing_state' 	=> array( 
				'type'			=> 'state', 
				'name'			=>'billing_state', 
				'label' 		=> __('State/County', 'wpshop'),
				'placeholder' 	=> __('State/County', 'wpshop'),
				'required' 		=> false, 
				'class' 		=> array('form-row-last update_totals_on_change') 
				),
			'billing_email' 	=> array(
				'type'			=> 'email',
				'label' 		=> __('Email Address', 'wpshop'), 
				'placeholder' 	=> __('you@yourdomain.com', 'wpshop'), 
				'required' 		=> true, 
				'class' 		=> array('form-row-first') 
				),
			'billing_phone' 	=> array( 
				'type'			=> 'phone',
				'label' 		=> __('Phone', 'wpshop'), 
				'placeholder' 	=> __('Phone number', 'wpshop'), 
				'required' 		=> true, 
				'class' 		=> array('form-row-last') 
				)
		);
		
		// Define shipping fields in an array.
		$this->shipping_fields = array(
			'shipping_first_name' => array( 
				'name'			=>'shipping_first_name', 
				'label' 		=> __('First Name', 'wpshop'), 
				'placeholder' 	=> __('First Name', 'wpshop'), 
				'required' 		=> true, 
				'class'			=> array('form-row-first') 
				),
			'shipping_last_name' => array( 
				'label' 		=> __('Last Name', 'wpshop'), 
				'placeholder' 	=> __('Last Name', 'wpshop'), 
				'required' 		=> true, 
				'class' 		=> array('form-row-last') 
				),
			'shipping_company' 	=> array( 
				'label' 		=> __('Company', 'wpshop'), 
				'placeholder' 	=> __('Company', 'wpshop') 
				),
			'shipping_address' 	=> array( 
				'label' 		=> __('Address', 'wpshop'), 
				'placeholder' 	=> __('Address', 'wpshop'), 
				'required' 		=> true
				),
			'shipping_city' 		=> array( 
				'label' 		=> __('City', 'wpshop'), 
				'placeholder' 	=> __('City', 'wpshop'), 
				'required' 		=> true, 
				'class' 		=> array('form-row-first') 
				),
			'shipping_postcode' 	=> array( 
				'type'			=> 'postcode',
				'label' 		=> __('Postcode', 'wpshop'), 
				'placeholder' 	=> __('Postcode', 'wpshop'), 
				'required' 		=> true, 
				'class'			=> array('form-row-last update_totals_on_change') 
				),
			'shipping_country' 	=> array( 
				'type'			=> 'country', 
				'label' 		=> __('Country', 'wpshop'), 
				'placeholder' 	=> __('Country', 'wpshop'),
				'required' 		=> true, 
				'class' 		=> array('form-row-first update_totals_on_change')
				),
			'shipping_state' 	=> array( 
				'type'			=> 'state', 
				'name'			=>'shipping_state', 
				'label' 		=> __('State/County', 'wpshop'),
				'placeholder' 	=> __('State/County', 'wpshop'),
				'required' 		=> false, 
				'class' 		=> array('form-row-last update_totals_on_change'),
				),
			'shipping_email' 	=> array(
				'type'			=> 'email',
				'label' 		=> __('Email Address', 'wpshop'), 
				'placeholder' 	=> __('you@yourdomain.com', 'wpshop'), 
				'required' 		=> true, 
				'class' 		=> array('form-row-first') 
				),
			'shipping_phone' 	=> array( 
				'type'			=> 'phone',
				'label' 		=> __('Phone', 'wpshop'), 
				'placeholder' 	=> __('Phone number', 'wpshop'), 
				'required' 		=> true, 
				'class' 		=> array('form-row-last') 
				)
		);
	}
	
	/** Traite les données reçus en POST
	 * @return void
	*/
	function managePost() {
	
		global $wpshop, $wpshop_account;
		
		// Modificiation info de livraison et facturation
		if(isset($_POST['submitbillingAndShippingInfo'])) {
			if($wpshop->validateForm($this->billing_fields)) {
				if(isset($_POST['shiptobilling']) || (!isset($_POST['shiptobilling']) && $wpshop->validateForm($this->shipping_fields))) {
					$this->save_billing_and_shipping_info();
					if(!empty($_GET['return']) && $_GET['return']=='checkout') {
						wp_safe_redirect(get_permalink(get_option('wpshop_checkout_page_id')));
					}
					else wp_safe_redirect(get_permalink(get_option('wpshop_myaccount_page_id')));
				}
			}
		}
		
		// Si il y a des erreurs
		if($wpshop->error_count()>0) {
			echo $wpshop->show_messages();
			return false;
		}
		else return true;
	}
	
	function display_login_form() {
	
		global $wpshop;
		
		foreach ($this->login_fields as $key => $field) :
			$wpshop->display_field($key, $field);
		endforeach;
		
	}
	
	function display_billing_and_shipping_form_field($billing_info=array(), $shipping_info=array()) {
	
		global $wpshop;
		
		echo '<h2>'.__('Billing address', 'wpshop').'</h2>';
							
		foreach ($this->billing_fields as $key => $field) :
			$default_value = !empty($billing_info[$key]) ? $billing_info[$key] : null;
			$wpshop->display_field($key, $field, $default_value);
		endforeach;
							
		if(isset($_POST['shiptobilling']) || array_values($billing_info) == array_values($shipping_info)) :
			echo '<p style="margin-top:15px;"><label><input type="checkbox" name="shiptobilling" checked="checked" /> '.__('Use as shipping information','wpshop').'</label></p>';
		else : 
			echo '<p style="margin-top:15px;"><label><input type="checkbox" name="shiptobilling" /> '.__('Use as shipping information','wpshop').'</label></p>';
		endif;
		
		$display = (isset($_POST['shiptobilling']) || array_values($billing_info) == array_values($shipping_info)) ? 'display:none;' : 'display:block;';
		
		echo '<div id="shipping_infos_bloc" style="'.$display.'">';
			echo '<h2>'.__('Shipping address', 'wpshop').'</h2>';
			foreach ($this->shipping_fields as $key => $field) :
				$default_value = !empty($shipping_info[$key]) ? $shipping_info[$key] : null;
				$wpshop->display_field($key, $field, $default_value);
			endforeach;
		echo '</div>';
	}
	
	function save_billing_and_shipping_info($user_id=0) {
		
		if (is_user_logged_in() || $user_id) :
		
			$user_id = $user_id ? $user_id : get_current_user_id();
		
			// Save billing/shipping to user meta fields
			if ($user_id>0) :
			
				// Billing Information
				foreach ($this->billing_fields as $key => $field) :
					$this->posted[$key] = isset($_POST[$key]) ? wpshop_tools::wpshop_clean($_POST[$key]) : '';
				endforeach;
				foreach ($this->shipping_fields as $key => $field) :
					$this->posted[$key] = isset($_POST[$key]) ? wpshop_tools::wpshop_clean($_POST[$key]) : '';
				endforeach;
					
				$this->posted['shiptobilling'] = isset($_POST['shiptobilling']) ? 1 : 0;
				
				$billing_info = array(
					'first_name' => $this->posted['billing_first_name'],
					'last_name' => $this->posted['billing_last_name'],
					'company' => $this->posted['billing_company'],
					'email' => $this->posted['billing_email'],
					'address' => $this->posted['billing_address'],
					'city' => $this->posted['billing_city'],
					'postcode' => $this->posted['billing_postcode'],
					'country' => $this->posted['billing_country'],
					'state' => $this->posted['billing_state'],
					'phone' => $this->posted['billing_phone']
				);
				update_user_meta($user_id, 'billing_info', $billing_info);
					
				// Get shipping/billing
				if ($this->posted['shiptobilling']) :
					
					update_user_meta($user_id, 'shipping_info', $billing_info);
						
				else:
						
					$shipping_info = array(
						'first_name' => $this->posted['shipping_first_name'],
						'last_name' => $this->posted['shipping_last_name'],
						'company' => $this->posted['shipping_company'],
						'email' => $this->posted['shipping_email'],
						'address' => $this->posted['shipping_address'],
						'city' => $this->posted['shipping_city'],
						'postcode' => $this->posted['shipping_postcode'],
						'country' => $this->posted['shipping_country'],
						'state' => $this->posted['shipping_state'],
						'phone' => $this->posted['shipping_phone']
					);
					update_user_meta($user_id, 'shipping_info', $shipping_info);
						
				endif;
			endif;
		endif;
	}
	
	function isRegistered($email, $password, $login=false) {
	
		global $wpshop;
		
		$user_data = get_user_by('email', $email);
		if(!empty($user_data)) {
			if(user_pass_ok($user_data->user_login, $password)) {
				if($login) {
					$user_id = $user_data->ID;
					$secure_cookie = is_ssl() ? true : false;
					// On connecte l'utilisateur
					wp_set_auth_cookie($user_id, true, $secure_cookie);
				}
				return true;
			} else $wpshop->add_error(__('Incorrect login infos', 'wpshop'));
		} else $wpshop->add_error(__('Incorrect login infos', 'wpshop'));
		return false;
	}
}
?>