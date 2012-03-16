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
function wpshop_signup_init() {
	global $wpshop_signup;
	$wpshop_signup = &new wpshop_signup();
	$wpshop_signup->display_form();
}

class wpshop_signup {
	
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
		
		$this->managePost();
			
		$user_id = get_current_user_id();	
			
		if($user_id) {
			echo __('Your are already registered','wpshop');
		}
		else {	
			echo '<form method="post" name="checkoutForm">';
			// Bloc REGISTER
			echo '<div class="col1 wpshopShow" id="register">';
			$wpshop_account->display_billing_and_shipping_form_field();
			echo '<input type="submit" name="submitOrderInfos" value="'.__('Take order','wpshop').'"" />';
			echo '</div>';
			echo '</form>';
		}
	}
	
	/** Traite les données reçus en POST
	 * @return void
	*/
	function managePost() {
	
		global $wpshop, $wpshop_account;
		
		// Nouveau compte client
		if(isset($_POST['submitOrderInfos'])) {
			$this->div_login = $this->div_infos_login = 'display:none';
			
			if($wpshop->validateForm($wpshop_account->personal_info_fields) && $wpshop->validateForm($wpshop_account->billing_fields)) {
				
				if(isset($_POST['shiptobilling']) || (!isset($_POST['shiptobilling']) && $wpshop->validateForm($wpshop_account->shipping_fields))) {
				
					if ($this->new_customer_account()) {
						wpshop_tools::wpshop_safe_redirect(get_permalink(get_option('wpshop_myaccount_page_id')));
						exit;
					}
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
	
	/** Register a new customer
	 * @return boolean
	*/
	function new_customer_account() {
	
		global $wpdb, $wpshop, $wpshop_account;
		
		// Checkout fields (non-shipping/billing)
		$this->posted['terms'] 				= 	isset($_POST['terms']) ? 1 : 0;
		$this->posted['createaccount'] 		= 	true;
		$this->posted['payment_method'] 	= 	isset($_POST['payment_method']) ? wpshop_tools::wpshop_clean($_POST['payment_method']) : '';
		$this->posted['shipping_method']	= 	isset($_POST['shipping_method']) ? wpshop_tools::wpshop_clean($_POST['shipping_method']) : '';
		$this->posted['account_username']	= 	wpshop_tools::wpshop_clean(strtolower($_POST['account_first_name'].substr($_POST['account_last_name'],0,1).'_'.uniqid()));
		$this->posted['account_password'] 	= 	isset($_POST['account_password_1']) ? wpshop_tools::wpshop_clean($_POST['account_password_1']) : '';
		$this->posted['account_password_2'] = 	isset($_POST['account_password_2']) ? wpshop_tools::wpshop_clean($_POST['account_password_2']) : '';
		$this->posted['account_email'] 		= 	isset($_POST['account_email']) ? wpshop_tools::wpshop_clean($_POST['account_email']) : null;
		$this->posted['account_civility'] 		= 	isset($_POST['account_civility']) ? wpshop_tools::wpshop_clean($_POST['account_civility']) : null;
			
		// On verifie certains champs du formulaire
		if (empty($this->posted['account_civility']) OR !in_array($this->posted['account_civility'], array(1,2,3))) $wpshop->add_error(__('Please enter an user civility', 'wpshop'));
		if (empty($this->posted['account_password'])) $wpshop->add_error(__('Please enter an account password.', 'wpshop'));
		if ($this->posted['account_password_2'] !== $this->posted['account_password']) $wpshop->add_error(__('Passwords do not match.', 'wpshop'));
				
		// On s'assure que le nom d'utilisateur est libre
		if (!validate_username($this->posted['account_username'])) :
			$wpshop->add_error( __('Invalid email/username.', 'wpshop') );
		elseif (username_exists($this->posted['account_username'])) :
			$wpshop->add_error( __('An account is already registered with that username. Please choose another.', 'wpshop') );
		endif;
						
		// Check the e-mail address
		if (email_exists($this->posted['account_email'])) :
			$wpshop->add_error(__('An account is already registered with your email address. Please login.', 'wpshop'));
		endif;
				
		// Si il n'y a pas d'erreur
		if ($wpshop->error_count()==0) :
					
			/** Création compte client */
			$reg_errors = new WP_Error();
			do_action('register_post', $this->posted['account_email'], $this->posted['account_email'], $reg_errors);
			$errors = apply_filters('registration_errors', $reg_errors, $this->posted['account_email'], $this->posted['account_email']);
							
			// if there are no errors, let's create the user account
			if (!$reg_errors->get_error_code()) :
				
				$user_pass = $this->posted['account_password'];
				$user_id = wp_create_user($this->posted['account_username'], $user_pass, $this->posted['account_email']);
				if (!$user_id) {
					$wpshop->add_error(sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', 'woothemes'), get_option('admin_email')));
					return false;
				}
				// Change role
				wp_update_user(array('ID' => $user_id, 'role' => 'customer'));
						
				// Set the WP login cookie
				$secure_cookie = is_ssl() ? true : false;
				wp_set_auth_cookie($user_id, true, $secure_cookie);
						
				// Envoi du mail d'inscription
				wpshop_tools::wpshop_prepared_email($this->posted['account_email'], 'WPSHOP_SIGNUP_MESSAGE', array(
					'customer_first_name' => $_POST['account_first_name'], 
					'customer_last_name' => $_POST['account_last_name']
				));
				
				// Récupere les données en POST et enregistre les infos de livraison et facturation
				$wpshop_account->save_billing_and_shipping_info($user_id);
					
				return true;
			else :
				$wpshop->add_error($reg_errors->get_error_message());
				return false;
			endif;
				
		endif;
			
		return false;
	}
}