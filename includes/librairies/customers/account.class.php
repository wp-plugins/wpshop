<?php

/* Instantiate the class from the shortcode */
function wpshop_account_display_form() {
	global $wpdb, $wpshop, $wpshop_account, $civility;
	
	$wpshop_account->managePost();
	
	$user_id = get_current_user_id();
	
	if(!$user_id)
	{
		echo '<div id="reponseBox"></div>';
		echo '<form method="post" id="login_form" action="'.WPSHOP_AJAX_FILE_URL.'">';
			echo '<input type="hidden" name="post" value="true" />';
			echo '<input type="hidden" name="elementCode" value="ajax_login" />';
			echo '<div class="create-account">'; 
				$wpshop_account->display_login_form();
			echo '</div>';
			echo '<input type="submit" name="submitLoginInfos" value="'.__('Login', 'wpshop').'" />';	
		echo '</form>';
		echo '<br />'.sprintf(__('Never created an account, Yet ? <a href="%s">Create one</a>','wpshop'), get_permalink(get_option('wpshop_signup_page_id')));
	}
	else
	{
		// Order status possibilities
		$order_status = array('awaiting_payment' => __('Awaiting payment', 'wpshop'), 'completed' => __('Paid', 'wpshop'), 'shipped' => __('Shipped', 'wpshop'), 'denied' => __('Denied', 'wpshop'));
		// Payment method possibilities
		$payment_method = array('paypal' => 'Paypal', 'check' => __('Check','wpshop'), 'cic' => __('Credit card','wpshop'));
	
		if(!empty($_GET['action'])) {
		
			// --------------------------
			// Edition infos personnelles
			// --------------------------
			if($_GET['action']=='editinfo') 
			{
				$shipping_info = get_user_meta($user_id, 'shipping_info', true);
				$billing_info = get_user_meta($user_id, 'billing_info', true);
				$user_preferences = get_user_meta($user_id, 'user_preferences', true);
				
				// Si il y a des infos à afficher
				if(!empty($shipping_info) && !empty($billing_info)) {
					// On ajoute le préfixe qu'il faut pour que tout soit fonctionnel
					foreach($shipping_info as $k => $v):
						$shipping_info['shipping_'.$k] = $shipping_info[$k];
						unset($shipping_info[$k]);
					endforeach;
					foreach($billing_info as $k => $v):
						$billing_info['billing_'.$k] = $billing_info[$k];
						unset($billing_info[$k]);
					endforeach;
				}
				else {
					$shipping_info = $billing_info = array('first_name'=>null,'last_name'=>null,'address'=>null,'postcode'=>null,'city'=>null,'country'=>null);
				}
				
				if(empty($_GET['return'])) :
					echo '<h2>'.__('Edit my personal informations','wpshop').'</h2>';
				elseif($_GET['return'] == 'checkout'):
					echo '<div class="infos_bloc wpshopShow" id="infos_register">'.__('You must type your billing and shipping info to continue.', 'wpshop').'</div>';
				endif;

				echo '<form method="post" name="billingAndShippingForm">';
					$wpshop_account->display_billing_and_shipping_form_field($billing_info, $shipping_info, $user_preferences);
					echo '<input type="submit" name="submitbillingAndShippingInfo" value="'.__('Save','wpshop').'" />';
				echo '</form>';
			}
			// --------------------------
			// Infos commande
			// --------------------------
			elseif($_GET['action']=='order' && !empty($_GET['oid']) && is_numeric($_GET['oid']))
			{	
				$order_info = get_post_meta($_GET['oid'], '_order_postmeta', true);
				
				if(!empty($order_info) && $order_info['customer_id']==$user_id) {
				
					echo '<h2>'.__('Order details','wpshop').'</h2>';
					
					$order_info = get_post_meta($_GET['oid'], '_order_info', true);
					
					$shipping_info = $order_info['shipping'];
					$billing_info = $order_info['billing'];
					
					echo '<h2>'.__('Shipping & billing info', 'wpshop').'</h2>';
					
					echo '<div class="half">';
					echo '<h2>'.__('Shipping address', 'wpshop').'</h2>';
					echo $shipping_info['first_name'].' '.$shipping_info['last_name'];
					echo empty($shipping_info['company'])?'<br />':', <i>'.$shipping_info['company'].'</i><br />';
					echo $shipping_info['address'].'<br />';
					echo $shipping_info['postcode'].', '.$shipping_info['city'].'<br />';
					echo $shipping_info['country'];
					echo '</div>';
							
					echo '<div class="half">';
					echo '<h2>'.__('Billing address', 'wpshop').'</h2>';
					echo (!empty($billing_info['civility']) ? $civility[$billing_info['civility']] : null).' '.$billing_info['first_name'].' '.$billing_info['last_name'];
					echo empty($billing_info['company'])?'<br />':', <i>'.$billing_info['company'].'</i><br />';
					echo $billing_info['address'].'<br />';
					echo $billing_info['postcode'].', '.$billing_info['city'].'<br />';
					echo $billing_info['country'];
					echo '</div><br />';
					
					// Données commande
					$order = get_post_meta($_GET['oid'], '_order_postmeta', true);
					$currency = wpshop_tools::wpshop_get_sigle($order['order_currency']);
					
					if(!empty($order)) {
						echo '<div class="order"><div>';
						echo __('Order number','wpshop').' : <strong>'.$order['order_key'].'</strong><br />';
						echo __('Date','wpshop').' : <strong>'.$order['order_date'].'</strong><br />';
						echo __('Total','wpshop').' : <strong>'.number_format($order['order_total_ttc'], 2, '.', '').' '.$currency.'</strong><br />';
						echo __('Payment method','wpshop').' : <strong>'.$payment_method[$order['payment_method']].'</strong><br />';
						if($order['payment_method']=='paypal') {
							$order_paypal_txn_id = get_post_meta($_GET['oid'], '_order_paypal_txn_id', true);
							echo __('Paypal transaction id', 'wpshop').' : <strong>'.(empty($order_paypal_txn_id)?'Unassigned':$order_paypal_txn_id).'</strong><br />';
						}
						echo __('Status','wpshop').' : <strong><span class="status '.$order['order_status'].'">'.$order_status[$order['order_status']].'</span></strong><br />';
						echo __('Tracking number','wpshop').' : '.(empty($order['order_trackingNumber'])?__('none','wpshop'):'<strong>'.$order['order_trackingNumber'].'</strong>').'<br /><br />';
						echo '<strong>'.__('Order content','wpshop').'</strong><br />';
						if(!empty($order['order_items'])){
							foreach($order['order_items'] as $o) {
								echo '<span class="right">'.number_format($o['item_total_ttc'], 2, '.', '').' '.$currency.'</span>'.$o['item_qty'].' x '.$o['item_name'].'<br />';
							}
							echo '<hr />';
							echo '<span class="right">'.number_format($order['order_total_ht'], 2, '.', '').' '.$currency.'</span>'.__('Total ET','wpshop').'<br />';
							echo '<span class="right">'.number_format(array_sum($order['order_tva']), 2, '.', '').' '.$currency.'</span>'.__('Taxes','wpshop').'<br />';
							echo '<span class="right">'.(empty($order['order_shipping_cost'])?'<strong>'.__('Free','wpshop').'</strong>':number_format($order['order_shipping_cost'], 2, '.', '').' '.$currency).'</span>'.__('Shipping fee','wpshop').'<br />';
							echo '<span class="right"><strong>'.number_format($order['order_grand_total'], 2, '.', '').' '.$currency.'</strong></span>'.__('Total ATI','wpshop');
						}
						else{
							echo __('No product for this order', 'wpshop');
						}
						echo '</div></div>';

						/* If the payment is completed */
						if(in_array($order['order_status'], array('completed', 'shipped'))) {
							echo '<a href="?action=order&oid='.$_GET['oid'].'&download_invoice='.$_GET['oid'].'">'.__('Download the invoice','wpshop').'</a>';
						}
					}
					else echo __('No order', 'wpshop');
			    }
				else echo __('You don\'t have the right to access this order.', 'wpshop');
			}
		}
		// --------------------------
		// Tableau de bord
		// --------------------------
		else
		{
			$shipping_info = get_user_meta($user_id, 'shipping_info', true);
			$billing_info = get_user_meta($user_id, 'billing_info', true);
	
			echo '<a href="'.wp_logout_url(get_permalink(get_option('wpshop_product_page_id'))).'" title="'.__('Logout','wpshop').'" class="right">'.__('Logout','wpshop').'</a>';
			
			echo '<p>'.sprintf(__('Hi <strong>%s %s</strong>', 'wpshop'), $billing_info['first_name'], $billing_info['last_name']).'.</p>';
			
			echo '<h2>'.__('Default shipping & billing info', 'wpshop').'</h2>';
			
			echo '<div class="half">';
			echo '<h2>'.__('Shipping address', 'wpshop').'</h2>';
			if(!empty($shipping_info)) {
				echo $shipping_info['first_name'].' '.$shipping_info['last_name'];
				echo empty($shipping_info['company'])?'<br />':', <i>'.$shipping_info['company'].'</i><br />';
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
				echo (!empty($billing_info['civility']) ? $civility[$billing_info['civility']] : null).' '.$billing_info['first_name'].' '.$billing_info['last_name'];
				echo empty($billing_info['company'])?'<br />':', <i>'.$billing_info['company'].'</i><br />';
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

			$query = $wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'" AND post_author = '.$user_id.' AND post_status = "publish" ORDER BY post_date DESC');
			$orders_id = $wpdb->get_results($query);
			
			if(!empty($orders_id)) 
			{
				$order = array();
				foreach($orders_id as $o) {
				
					$order_id = $o->ID;
					$o = get_post_meta($order_id, '_order_postmeta', true);
					$currency = wpshop_tools::wpshop_get_sigle($o['order_currency']);

					echo '<div class="order"><div>';
					echo __('Order number','wpshop').' : <strong>'.$o['order_key'].'</strong><br />';
					echo __('Date','wpshop').' : <strong>'.$o['order_date'].'</strong><br />';
					echo __('Total ATI','wpshop').' : <strong>'.number_format($o['order_grand_total'], 2, '.', '').' '.$currency.'</strong><br />';
					echo __('Status','wpshop').' : <strong><span class="status '.$o['order_status'].'">'.$order_status[$o['order_status']].'</span></strong><br />';
					echo '<a href="?action=order&oid='.$order_id.'" title="'.__('More info about this order...', 'wpshop').'">'.__('More info about this order...', 'wpshop').'</a>';
					echo '</div></div>';
				}
			}
			else echo __('No order', 'wpshop');
		}
	}
}

/* Class wpshop_account */
class wpshop_account {

	var $login_fields = array();
	var $personal_info_fields = array();
	var $billing_fields = array();
	var $shipping_fields = array();
	
	/** Constructor of the class */
	function __construct() {
	
		$this->login_fields = array(
			'account_email' => array( 
				'label' 		=> __('Email Address or username', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true, 
				'class' 		=> array('form-row-first') 
			),
			'account_password' => array( 
				'type' => 'password', 
				'label' => __('Password', 'wpshop'), 
				'placeholder' => '',
				'class' => array('form-row-last'), 
				'required' 		=> true,
				'label_class' => array('hidden')
			)
		);
		
		$this->personal_info_fields = array(
			'account_first_name' => array(
				'label' => __('First name', 'wpshop'), 
				'placeholder' => '',
				'class' => array('form-row-first'), 
				'required' 		=> true
			),
			'account_last_name' => array( 
				'label' 		=> __('Last name', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true, 
				'class' 		=> array('form-row-last') 
			),
			'account_company' 	=> array( 
				'label' 		=> __('Company', 'wpshop'), 
				'placeholder' 	=> '' 
			),
			'account_username' 	=> array(
				'label' 		=> __('Username', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true
			),
			'account_email' 	=> array(
				'type'			=> 'email',
				'label' 		=> __('Email Address', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true
			),
			'account_password_1' => array(
				'type'			=> 'password',
				'label' 		=> __('Password', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true, 
				'class' 		=> array('form-row-first') 
			),
			'account_password_2' => array(
				'type'			=> 'password',
				'label' 		=> __('Re-type password', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true, 
				'class' 		=> array('form-row-last') 
			),
		);
		
		// Define billing fields in an array.
		$this->billing_fields = array(
			'billing_address' 	=> array( 
				'label' 		=> __('Address', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true
				),
			'billing_city' 		=> array( 
				'label' 		=> __('City', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true, 
				'class' 		=> array('form-row-first') 
				),
			'billing_postcode' 	=> array( 
				'type'			=> 'postcode',
				'label' 		=> __('Postcode', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true, 
				'class'			=> array('form-row-last') 
				),
			'billing_country' 	=> array( 
				'type'			=> 'country', 
				'label' 		=> __('Country', 'wpshop'),
				'placeholder' 	=> '',
				'required' 		=> true, 
				'class' 		=> array('form-row-first')
				),
			'billing_state' 	=> array( 
				'type'			=> 'state', 
				'name'			=>'billing_state', 
				'label' 		=> __('State/County', 'wpshop'),
				'placeholder' 	=> '',
				'required' 		=> false, 
				'class' 		=> array('form-row-last') 
				),
			'billing_phone' 	=> array( 
				'type'			=> 'phone',
				'label' 		=> __('Phone', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> false
			)
		);
		
		// Define shipping fields in an array.
		$this->shipping_fields = array(
			'shipping_first_name' => array( 
				'name'			=>'shipping_first_name', 
				'label' 		=> __('First Name', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true, 
				'class'			=> array('form-row-first') 
				),
			'shipping_last_name' => array( 
				'label' 		=> __('Last Name', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true, 
				'class' 		=> array('form-row-last') 
				),
			'shipping_company' 	=> array( 
				'label' 		=> __('Company', 'wpshop'), 
				'placeholder' 	=> '' 
				),
			'shipping_address' 	=> array( 
				'label' 		=> __('Address', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true
				),
			'shipping_city' 		=> array( 
				'label' 		=> __('City', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true, 
				'class' 		=> array('form-row-first') 
				),
			'shipping_postcode' 	=> array( 
				'type'			=> 'postcode',
				'label' 		=> __('Postcode', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> true, 
				'class'			=> array('form-row-last') 
				),
			'shipping_country' 	=> array( 
				'type'			=> 'country', 
				'label' 		=> __('Country', 'wpshop'), 
				'placeholder' 	=> '',
				'required' 		=> true, 
				'class' 		=> array('form-row-first')
				),
			'shipping_state' 	=> array( 
				'type'			=> 'state', 
				'name'			=>'shipping_state', 
				'label' 		=> __('State/County', 'wpshop'),
				'placeholder' 	=> '',
				'required' 		=> false, 
				'class' 		=> array('form-row-last'),
				),
			'shipping_phone' 	=> array( 
				'type'			=> 'phone',
				'label' 		=> __('Phone', 'wpshop'), 
				'placeholder' 	=> '', 
				'required' 		=> false
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
			if($wpshop->validateForm($wpshop_account->personal_info_fields) && $wpshop->validateForm($this->billing_fields)) {
				if(isset($_POST['shiptobilling']) || (!isset($_POST['shiptobilling']) && $wpshop->validateForm($this->shipping_fields))) {
					if($this->save_billing_and_shipping_info()) {
						if(!empty($_GET['return']) && $_GET['return']=='checkout') {
							wpshop_tools::wpshop_safe_redirect(get_permalink(get_option('wpshop_checkout_page_id')));
						}
						else wpshop_tools::wpshop_safe_redirect(get_permalink(get_option('wpshop_myaccount_page_id')));
					}
				}
			}
		}
		// Téléchargement de la facture
		elseif(!empty($_GET['download_invoice'])) {
			$pdf = new wpshop_export_pdf();
			$pdf->invoice_export($_GET['download_invoice']);
		}
		
		// Si il y a des erreurs
		if($wpshop->error_count()>0) {
			echo $wpshop->show_messages();
			return false;
		}
		else return true;
	}
	
	/** Display the login form
	 * @return void
	*/
	function display_login_form() {
	
		global $wpshop;
		
		foreach ($this->login_fields as $key => $field) :
			$wpshop->display_field($key, $field);
		endforeach;
	}
	
	/** Display the billing and shipping form
	 * @return void
	*/
	function display_billing_and_shipping_form_field($billing_info=array(), $shipping_info=array(), $user_preferences=array()) {
	
		global $wpshop;

		if(WPSHOP_DEBUG_MODE && in_array(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), unserialize(WPSHOP_DEBUG_ALLOWED_IP))){
			echo '<span class="fill_form_checkout_for_test" >Fill the form for test</span>';
		}

		echo '<h2>'.__('Personal information', 'wpshop').'</h2>';
		echo '<p class="formField"><label>'.__('Civility', 'wpshop').'</label> 
		<span class="required">*</span> &nbsp; <input type="radio" name="account_civility" value="1" '.((empty($billing_info['billing_civility']) OR $billing_info['billing_civility']==1)?'checked="checked"':null).' /> Monsieur 
		<input type="radio" name="account_civility" value="2" '.($billing_info['billing_civility']==2?'checked="checked"':null).' /> Madame 
		<input type="radio" name="account_civility" value="3" '.($billing_info['billing_civility']==3?'checked="checked"':null).' /> Mademoiselle';
		
		foreach ($this->personal_info_fields as $key => $field) :
		//echo '<pre>';print_r($billing_info);echo '</pre>';
			$default_value = !empty($billing_info['billing_'.substr($key,8)]) ? $billing_info['billing_'.substr($key,8)] : null;
			$wpshop->display_field($key, $field, $default_value);
		endforeach;
		echo '<br />';
		
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
		echo '</div><br />';
		
		echo '<h2>Mes newsletters et informations commerciales</h2>';
		echo '<input type="checkbox" name="newsletters_site" id="newsletters_site" '.(($user_preferences['newsletters_site']==1 OR !empty($_POST['newsletters_site']))?'checked="checked"':null).' /><label for="newsletters_site">'.__('I want to receive promotional information from the site','wpshop').'</label><br />';
		echo '<input type="checkbox" name="newsletters_site_partners" id="newsletters_site_partners" '.(($user_preferences['newsletters_site_partners']==1 OR !empty($_POST['newsletters_site_partners']))?'checked="checked"':null).' /><label for="newsletters_site_partners">'.__('I want to receive promotional information from partner companies','wpshop').'</label><br /><br />';

	}
	
	/** Save the billing and shipping info
	 * @return void
	*/
	function save_billing_and_shipping_info($user_id=0) {
		global $wpdb, $wpshop;
		
		$user_id = intval($user_id);
		if ($user_id>0 OR is_user_logged_in()) :
		
			$user_id = $user_id>0 ? $user_id : get_current_user_id();
		
			// Save billing/shipping to user meta fields
			if ($user_id>0) :
			
				// Billing Information
				foreach ($this->personal_info_fields as $key => $field) :
					$this->posted[$key] = isset($_POST[$key]) ? wpshop_tools::wpshop_clean($_POST[$key]) : null;
				endforeach;
				foreach ($this->billing_fields as $key => $field) :
					$this->posted[$key] = isset($_POST[$key]) ? wpshop_tools::wpshop_clean($_POST[$key]) : null;
				endforeach;
				foreach ($this->shipping_fields as $key => $field) :
					$this->posted[$key] = isset($_POST[$key]) ? wpshop_tools::wpshop_clean($_POST[$key]) : null;
				endforeach;
				
				// Modification du mot de passe
				if (!empty($this->posted['account_password_1']) && !empty($this->posted['account_password_2']) && is_user_logged_in()) {
					if ($this->posted['account_password_2'] == $this->posted['account_password_1']) {
						// Modification dans la BDD
						wp_update_user(array('ID' => $user_id, 'user_pass' => $this->posted['account_password_1']));
					} else $wpshop->add_error(__('Passwords do not match.', 'wpshop'));
				}
					
				$this->posted['shiptobilling'] = !empty($_POST['shiptobilling']) ? true : false;
				
				// Si il n'y a pas d'erreur
				if ($wpshop->error_count()==0) :
				
					$billing_info = array(
						'civility' => $_POST['account_civility'],
						'first_name' => $this->posted['account_first_name'],
						'last_name' => $this->posted['account_last_name'],
						'company' => $this->posted['account_company'],
						'email' => $this->posted['account_email'],
						'address' => $this->posted['billing_address'],
						'city' => $this->posted['billing_city'],
						'postcode' => $this->posted['billing_postcode'],
						'country' => $this->posted['billing_country'],
						'state' => $this->posted['billing_state'],
						'phone' => $this->posted['billing_phone']
					);
					update_user_meta($user_id, 'billing_info', $billing_info);
					update_user_meta($user_id, 'first_name', $this->posted['account_first_name']);
					update_user_meta($user_id, 'last_name', $this->posted['account_last_name']);
						
					// Get shipping/billing
					if ($this->posted['shiptobilling']) :
						unset($billing_info['civility']);
						unset($billing_info['email']);
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
					
					// User preferences
					$user_preferences = array(
						'newsletters_site' => isset($_POST['newsletters_site']) && $_POST['newsletters_site']=='on',
						'newsletters_site_partners' => isset($_POST['newsletters_site_partners']) && $_POST['newsletters_site_partners']=='on'
					);
					update_user_meta($user_id, 'user_preferences', $user_preferences);
					return true;
					
				endif;
			endif;
		endif;
		
		return false;
	}
	
	/** Return true if the login info is ok and not if not
	 * @return boolean
	*/
	function isRegistered($email_or_username, $password, $login=false) {
	
		global $wpshop;
		
		if(!empty($email_or_username)) {
			$user_data = get_user_by('email', $email_or_username);
			// Test connexion par identifiant et par email
			if(user_pass_ok($email_or_username, $password) OR user_pass_ok($user_data->user_login, $password)) {
				if($login) {
					$user_data = empty($user_data) ? get_user_by('login', $email_or_username) : $user_data;
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

	/**
	*	Return output for customer adress
	*
	*	@param array $address_type The customer address stored into an array
	*
	*	@return string $user_address_output The html output for the customer address
	*/
	function display_customer_address($address_type = 'billing', $address_infos){
		global $civility;
		$user_address_output = '';

		$user_address_output .=  '<div class="half"><span >'.__(ucfirst(strtolower($address_type)),'wpshop').'</span><br /><br />';
		$user_address_output .=  (!empty($address_infos['civility']) ? __($civility[$address_infos['civility']], 'wpshop') : null).' <strong>'.$address_infos['first_name'].' '.$address_infos['last_name'].'</strong>';
		$user_address_output .=  empty($address_infos['company'])?'<br />':'<br/><i>' . __('Company', 'wpshop') . '</i>: '.$address_infos['company'].'<br />';
		$user_address_output .=  '<i>' . __('Email address', 'wpshop') . '</i>: '.(!empty($address_infos['email']) ? $address_infos['email'] : ' - ').'<br />';
		$user_address_output .=  '<i>' . __('Phone', 'wpshop') . '</i>: '.(!empty($address_infos['phone']) ? $address_infos['phone'] : ' - ').'<br />';
		$user_address_output .=  $address_infos['address'].'<br />';
		$user_address_output .=  $address_infos['postcode'].' '.$address_infos['city'].', '.$address_infos['country'];
		$user_address_output .=  '</div>';

		return $user_address_output;
	}
	/**
	*	Return output for customer adress
	*
	*	@param array $address_type The customer address stored into an array
	*
	*	@return string $user_address_output The html output for the customer address
	*/
	function edit_customer_address($address_type = 'Billing', $address_infos, $customer_id){
		global $civility;
		$user_address_output = '';

		$user_info = null;
		if(!empty($customer_id)){
			$user_info = get_userdata($customer_id);

			if(empty($address_infos['first_name'])){
				if(!empty($user_info->user_firstname)){
					$address_infos['first_name'] = $user_info->user_firstname;
				}else{
					$address_infos['first_name'] = $user_info->user_login;
				}
			}
			if(empty($address_infos['last_name'])){
				if(!empty($user_info->user_lastname)){
					$address_infos['last_name'] = $user_info->user_lastname;
				}
			}
			if(empty($address_infos['email'])){
				if(!empty($user_info->user_email )){
					$address_infos['email'] = $user_info->user_email ;
				}
			}
		}

		$user_address_output .=  '<div class="half"><span>'.__(ucfirst(strtolower($address_type)),'wpshop').'</span><br/>' . ($address_type=='Shipping' ? ' <input type="checkbox" name="use_billing_address_as_shipping_address" value="yes" class="billing_as_shipping" id="billing_as_shipping" />&nbsp;<label for="billing_as_shipping" >' . __('Use billing address for shipping', 'wpshop') : '') . '</label><br /><br />';
		$user_address_output .=  (!empty($address_infos['civility']) ? __($civility[$address_infos['civility']], 'wpshop') : null).'
<div>
	<div class="order_customer_adresses_edition_info_title" >' . __('Civility', 'wpshop') . '</div> ';
		if(!empty($civility)){
			$user_address_output .= '<select name="user[' . strtolower($address_type) . '_info][civility]" id="order_customer_address_input_' . $address_type . '_civility" class="order_customer_adresses_edition_input order_customer_adresses_edition_input_' . $address_type . '" >';
			foreach($civility as $key => $civil){
				$selected = (!empty($address_infos['civility']) && ($address_infos['civility'] == $key) ? ' selected="selected" ' : '');
				$user_address_output .= '<option value="' . $key . '"' . $selected . '>' . __($civil, 'wpshop') . '</option>';
			}
			$user_address_output .= '</select>';
		} else $user_address_output .= __('Please ask site administrator to add civilities', 'wpshop');
		$user_address_output .= '
</div>
<div>
	<div class="order_customer_adresses_edition_info_title" >' . __('Firstname', 'wpshop') . '</div> <input type="text" class="order_customer_adresses_edition_input order_customer_adresses_edition_input_' . $address_type . '" name="user[' . strtolower($address_type) . '_info][first_name]" value="'.$address_infos['first_name'].'" id="order_customer_address_input_' . $address_type . '_first_name" />
</div>
<div>
	<div class="order_customer_adresses_edition_info_title" >' . __('Lastname', 'wpshop') . '</div> <input type="text" class="order_customer_adresses_edition_input order_customer_adresses_edition_input_' . $address_type . '" name="user[' . strtolower($address_type) . '_info][last_name]" value="'.$address_infos['last_name'].'" id="order_customer_address_input_' . $address_type . '_last_name" />
</div>
<div>
	<div class="order_customer_adresses_edition_info_title" >' . __('Company', 'wpshop') . '</div> <input type="text" class="order_customer_adresses_edition_input order_customer_adresses_edition_input_' . $address_type . '" name="user[' . strtolower($address_type) . '_info][company]" value="'.$address_infos['company'].'" id="order_customer_address_input_' . $address_type . '_company" />
</div>
<div>
	<div class="order_customer_adresses_edition_info_title" >' . __('Email address', 'wpshop') . '</div> <input type="text" class="order_customer_adresses_edition_input order_customer_adresses_edition_input_' . $address_type . '" name="user[' . strtolower($address_type) . '_info][email]" value="'.$address_infos['email'].'" id="order_customer_address_input_' . $address_type . '_email" />
</div>
<div>
	<div class="order_customer_adresses_edition_info_title" >' . __('Phone', 'wpshop') . '</div> <input type="text" class="order_customer_adresses_edition_input order_customer_adresses_edition_input_' . $address_type . '" name="user[' . strtolower($address_type) . '_info][phone]" value="'.$address_infos['phone'].'" id="order_customer_address_input_' . $address_type . '_phone" />
</div>
<div>
	<div class="order_customer_adresses_edition_info_title" >' . __('Address', 'wpshop') . '</div> <input type="text" class="order_customer_adresses_edition_input order_customer_adresses_edition_input_' . $address_type . '" name="user[' . strtolower($address_type) . '_info][address]" value="' . $address_infos['address'].'" id="order_customer_address_input_' . $address_type . '_address" />
</div>
<div>
	<div class="order_customer_adresses_edition_info_title" >' . __('Postcode', 'wpshop') . '</div> <input type="text" class="order_customer_adresses_edition_input order_customer_adresses_edition_input_' . $address_type . '" name="user[' . strtolower($address_type) . '_info][postcode]" value="' . $address_infos['postcode'].'" id="order_customer_address_input_' . $address_type . '_postcode" />
</div>
<div>
	<div class="order_customer_adresses_edition_info_title" >' . __('City', 'wpshop') . '</div> <input type="text" class="order_customer_adresses_edition_input order_customer_adresses_edition_input_' . $address_type . '" name="user[' . strtolower($address_type) . '_info][city]" value="'.$address_infos['city'].'" id="order_customer_address_input_' . $address_type . '_city" />
</div>
<div>
	<div class="order_customer_adresses_edition_info_title" >' . __('Country', 'wpshop') . '</div> <input type="text" class="order_customer_adresses_edition_input order_customer_adresses_edition_input_' . $address_type . '" name="user[' . strtolower($address_type) . '_info][country]" value="'.$address_infos['country'] . '" id="order_customer_address_input_' . $address_type . '_country" />
</div>';
		$user_address_output .=  '</div>';

		return $user_address_output;
	}

}

?>