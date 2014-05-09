<?php
/**
 * Plugin Name: WP-Shop-Customer-Account
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Customer Account
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WpShop Customer Account bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wps_account") ) {
	/** Template Global vars **/
	DEFINE('WPS_ACCOUNT_DIR', basename(dirname(__FILE__)));
	DEFINE('WPS_ACCOUNT_PATH', str_replace( "\\", "/", str_replace( WPS_ACCOUNT_DIR, "", dirname( __FILE__ ) ) ) );
	DEFINE('WPS_ACCOUNT_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_ACCOUNT_PATH ) );
	
	class wps_account {
		/**
		 * Define the main directory containing the template for the current plugin
		 * @var string
		 */
		private $template_dir;
		/**
		 * Define the directory name for the module in order to check into frontend
		 * @var string
		 */
		private $plugin_dirname = WPS_ACCOUNT_DIR;
		
		function __construct() {
			/** Template Load **/
			$this->template_dir = WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . "/templates/";
			/** Shortcodes **/
			// Sign up Display Shortcode
			add_shortcode( 'wps_signup', array( &$this, 'display_signup' ) );
			// Log in Form Display Shortcode
			add_shortcode( 'wpshop_login', array( &$this, 'get_login_form'));
			//Log in first step
			add_shortcode( 'wps_first_login', array( &$this, 'get_login_first_step'));
			// Forgot password Form
			add_shortcode( 'wps_forgot_password', array( &$this, 'get_forgot_password_form'));
			// Renew password form 
			add_shortcode( 'wps_renew_password', array( &$this, 'get_renew_password_form'));
			
			
			/** Ajax Actions **/	
			add_action('wp_ajax_wps_display_connexion_form', array(&$this, 'wps_ajax_get_login_form_interface') );
			add_action('wp_ajax_nopriv_wps_display_connexion_form', array(&$this, 'wps_ajax_get_login_form_interface') );
			
			add_action('wp_ajax_wps_login_request', array(&$this, 'control_login_form_request') );
			add_action('wp_ajax_nopriv_wps_login_request', array(&$this, 'control_login_form_request') );
			
			add_action('wp_ajax_wps_forgot_password_request', array(&$this, 'wps_forgot_password_request') );
			add_action('wp_ajax_nopriv_wps_forgot_password_request', array(&$this, 'wps_forgot_password_request') );
			
			add_action('wp_ajax_wps_forgot_password_renew', array(&$this, 'wps_forgot_password_renew') );
			add_action('wp_ajax_nopriv_wps_forgot_password_renew', array(&$this, 'wps_forgot_password_renew') );
			
			add_action('wp_ajax_wps_signup_request', array(&$this, 'wps_save_signup_form') );
			add_action('wp_ajax_nopriv_wps_signup_request', array(&$this, 'wps_save_signup_form') );
			
			add_action('wp_ajax_wps_login_first_request', array(&$this, 'wps_login_first_request') );
			add_action('wp_ajax_nopriv_wps_login_first_request', array(&$this, 'wps_login_first_request') );
				
			/** Add Javascript files **/	
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'wps_forgot_password_js', plugins_url('templates/frontend/js/wps_forgot_password.js', __FILE__) );
			wp_enqueue_script( 'wps_login_js', plugins_url('templates/frontend/js/wps_login.js', __FILE__) );
			wp_enqueue_script( 'wps_signup_js', plugins_url('templates/frontend/js/wps_signup.js', __FILE__) );
			
		}

		
		/** Load templates **/
		function get_template_part( $side, $slug, $name=null ) {
			$path = '';
			$templates = array();
			$name = (string)$name;
			if ( '' !== $name )
				$templates[] = "{$side}/{$slug}-{$name}.php";
			$templates[] = "{$side}/{$slug}.php";
		
			/**	Check if required template exists into current theme	*/
			$check_theme_template = array();
			foreach ( $templates as $template ) {
				$check_theme_template = $this->plugin_dirname . "/" . $template;
			}
			$path = locate_template( $check_theme_template, false );
		
			if ( empty( $path ) ) {
				foreach ( (array) $templates as $template_name ) {
					if ( !$template_name )
						continue;
		
					if ( file_exists($this->template_dir . $template_name)) {
						$path = $this->template_dir . $template_name;
						break;
					}
				}
			}
		
			return $path;
		}
		
		
		/** LOG IN - Display log in Form **/
		function get_login_form( $force_login = false ) {
			if ( get_current_user_id() != 0 ) {
				return __( 'You are already logged', 'wpshop');
			}
			else {
				if ( !empty($_GET['action']) && $_GET['action'] == 'retrieve_password' && !empty($_GET['key']) && !empty($_GET['login']) && !$force_login ) {
					$output = self::get_renew_password_form();
				}
				else {
					ob_start();
					require_once( $this->get_template_part( "frontend", "login/login-form") );
					$output = ob_get_contents();
					ob_end_clean();
					if ( !$force_login ) {
						$output .= do_shortcode( '[wps_signup]' );
					}
				}
				/** Modal Box **/
				ob_start();
				require_once( $this->get_template_part( "frontend", "forgot-password/forgot-password-modal") );
				$output .= ob_get_contents();
				ob_end_clean();
				
				return $output;
			}
		}
		
		/** LOG IN - AJAX - Action to connect **/
		function control_login_form_request() {
			$result = '';
			$status = false;
			if ( !empty($_POST['wps_login_user_login']) && !empty($_POST['wps_login_password']) ) {
				$creds = array();
				// Test if an user exist with this login
				$user_checking = get_user_by( 'login', $_POST['wps_login_user_login']);
				if( !empty($user_checking) ) {
					$creds['user_login'] = sanitize_user($_POST['wps_login_user_login']);
				}
				else {
					if ( is_email($_POST['wps_login_user_login']) ) {
						$user_checking = get_user_by( 'email', $_POST['wps_login_user_login']);
						$creds['user_login'] = $user_checking->user_login;
					}
				}
				$creds['user_password'] = wpshop_tools::varSanitizer( $_POST['wps_login_password'] );
				$creds['remember'] =  ( !empty($_POST['wps_login_remember_me']) ) ? true : false;
				$user = wp_signon( $creds, false );
				if ( is_wp_error($user) ) {
					$result = '<div class="wps-alert-error">' .__('Connexion error', 'wpshop'). '</div>';
				}
				else {
					$permalink_option = get_option( 'permalink_structure' );
					$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
					$result = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=3';
					$status = true;
				}
			}
			else {
				$result = '<div class="wps-alert-error">' .__('E-Mail and Password are required', 'wpshop'). '</div>';
			}
				
			echo json_encode( array( $status, $result) );
			die();
		}
		
		/** 
		 * LOG IN - AJAX - Display log in Form in Ajax 
		 */
		function wps_ajax_get_login_form_interface() {
			$response = array( 'status' => true, 'response' => self::get_login_form() );
			echo json_encode( $response );
			die();
		}
		
		/** LOG IN - Display first login step **/
		function get_login_first_step() {
			$output = ''; 
			ob_start();
			require_once( $this->get_template_part( "frontend", "login/login-form", "first") );
			$output .= ob_get_contents();
			ob_end_clean();
			return $output;
		}
		
		/**
		 * LOG IN - First Step log in request
		 */
		function wps_login_first_request() {
			$status = false; $login_action = false; $response = '';
			$user_email = ( !empty($_POST['email_address']) ) ? wpshop_tools::varSanitizer( $_POST['email_address'] ) : null;
			if ( !empty($user_email) ) {
				$status = true;
				/** Check if a user exist with it's email **/
				$checking_user = get_user_by( 'login', $user_email);
				if ( !empty($checking_user) ) {
					$login_action = true;
					$user_firstname = get_user_meta( $checking_user->ID, 'first_name', true );
					$response = $user_firstname;
				}
				else {
					$checking_user = get_user_by( 'email', $user_email);
					if ( !empty( $checking_user ) ) {
						$login_action = true;
						$user_firstname = get_user_meta( $checking_user->ID, 'first_name', true );
						$response = $user_firstname;
					}
				}
				
				if( !$login_action && is_email($user_email)  ) {
					$response = $user_email;
				}
			}
			else {
				$response = '<div class="wps-alert-error">' .__( 'An e-mail address is required', 'wpshop' ). '</div>';
			}
			echo json_encode( array( 'status'=> $status, 'response' => $response, 'login_action' => $login_action) );
			die();
		}
		
		/**
		 * FORGOT PASSWORD - Display the forgot Password Form
		 */
		function get_forgot_password_form() {
			$output = '';
			if ( get_current_user_id() == 0 ) {
				ob_start();
				require_once( $this->get_template_part( "frontend", "forgot-password/forgot-password") );
				$output = ob_get_contents();
				ob_end_clean();
			}
			return $output;
		}
		
		/**
		 * FORGOT PASSWORD- AJAX - Forgot Password Request
		 */
		function wps_forgot_password_request() {
			global $wpdb;
			$status = false; $result = '';
			$user_login = ( !empty( $_POST['wps_user_login']) ) ? wpshop_tools::varSanitizer($_POST['wps_user_login']) : null;
			if ( !empty($user_login) ) {
				$existing_user = false;
				$exist_user = get_user_by('login', $user_login);
				if( !empty($exist_user) ) {
					$existing_user = true;
				}
				else {
					$exist_user = get_user_by('email', $user_login);
					if ( !empty($exist_user) ) {
						$existing_user = true;
					}
				}
				
				if ( $existing_user ) {
					$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
					if ( empty($key) ) {
						$key = wp_generate_password(20, false);
						$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
					}
					$this->send_forgot_password_email($key, $user_login, $exist_user);
					$result = '<div class="wps-alert-info">' .__('An e-mail with an password renew link has been sent to you', 'wpshop'). '</div>';
					$status = true;
				}
				else {
					$result = '<div class="wps-alert-error">' .__('No customer account corresponds to this email', 'wpshop'). '</div>';
				}
			}
			else {
				$result = '<div class="wps-alert-error">' .__('Please fill the required field', 'wpshop'). '</div>';
			}
			$response = array( $status, $result );
			echo json_encode( $response );
			die();
		}
		
		/**
		 * FORGOT PASSWORD - Send Forgot Password Email Initialisation
		 * @param string $key
		 * @param string $user_login
		 */
		function send_forgot_password_email($key, $user_login, $exist_user){
			$user_data = $exist_user->data;
			$email = $user_data->user_email;
				
			$first_name = get_user_meta( $user_data->ID, 'first_name', true );
			$last_name = get_user_meta( $user_data->ID, 'last_name', true );
			$permalink_option = get_option( 'permalink_structure' );
			$link = '<a href="' .get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ).( (!empty($permalink_option)) ? '?' : '&').'order_step=2&action=retrieve_password&key=' .$key. '&login=' .rawurlencode($user_login). '">' .get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ). '&action=retrieve_password&key=' .$key. '&login=' .rawurlencode($user_login). '</a>';
			if( !empty($key) && !empty( $user_login ) ) {
				wpshop_messages::wpshop_prepared_email($email,
				'WPSHOP_DIRECT_PAYMENT_LINK_MESSAGE',
				array( 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'direct_payment_link' => $link)
				);
			}
		}
		
		/** FORGOT PASSWORD - AJAX - Make renew password action **/
		function wps_forgot_password_renew() {
			global $wpdb;
			$status = false; $result = $form = '';
			$password = ( !empty( $_POST['pass1']) ) ? wpshop_tools::varSanitizer( $_POST['pass1'] ) : null;
			$confirm_password = ( !empty( $_POST['pass2']) ) ? wpshop_tools::varSanitizer( $_POST['pass2'] ) : null;
			$activation_key = ( !empty( $_POST['activation_key']) ) ?  wpshop_tools::varSanitizer( $_POST['activation_key'] ) : null;
			$login = ( !empty( $_POST['user_login']) ) ?  wpshop_tools::varSanitizer( $_POST['user_login'] ) : null;
			if ( !empty($password) && !empty($confirm_password) && $confirm_password == $password ) {
				if ( !empty($activation_key) && !empty($login) ) {
					$existing_user = false;
					$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $activation_key, $login ) );
					if( empty($user) ) {
						$existing_user = true;
					}
					else {
						$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_email = %s", $activation_key, $login ) );
						if( !empty($user) ) {
							$existing_user = true;
						}
					}
					
					if ( $existing_user ){
						wp_set_password($password, $user->ID);
						wp_password_change_notification($user);
						$status = true;
						$result = '<div class="wps-alert-success">' .__('Your password has been updated', 'wpshop'). '. <a href="#" id="display_connexion_form"> ' .__('Connect you', 'wpshop').' !</a></div>';
						$form = self::get_login_form( true );
					}
					else {
						$result = '<div class=" wps-alert-error">' .__('Invalid activation key', 'wpshop'). '</div>';
					}
				}
				else {
					$result = '<div class=" wps-alert-error">' .__('Invalid activation key', 'wpshop'). '</div>';
				}
			}
			else {
				$result = '<div class="wps-alert-error">' .__('Password and confirmation password are differents', 'wpshop'). '</div>';
			}
				
			$response = array( $status, $result, $form );
			echo json_encode( $response);
			die();
		}
		
		/**
		 * FORGOT PASSWORD - Display renew password interface
		 * @return string
		 */
		function get_renew_password_form() {
			if ( get_current_user_id() == 0 ) {
				ob_start();
				require_once( $this->get_template_part( "frontend", "forgot-password/password-renew") );
				$output = ob_get_contents();
				ob_end_clean();
			}
			return $output;
		}
		
		/** FORGOT PASSWORD - AJAX - Get Forgot Password form **/
		function wps_ajax_get_forgot_password_form() {
			echo json_encode( array(self::get_forgot_password_form() ) );
			die();
		}
		
		/**
		 * SIGN UP - Display Sign up form
		 * @return string
		 */
		function display_signup() {
			global $wpdb;
			$output = '';
			if ( get_current_user_id() == 0 ) {
				$fields_to_output = $signup_fields = array();
				
				$password_attribute = $signup_form_attributes =  array();
				
				$entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
				
				$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_SET.' WHERE entity_id = %d', $entity_id);
				$customer_entity_id = $wpdb->get_var( $query );
				$attributes_set = wpshop_attributes_set::getElement($customer_entity_id);
				$account_attributes = wpshop_attributes_set::getAttributeSetDetails( ( !empty($attributes_set->id) ) ? $attributes_set->id : '', "'valid'");
				$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_GROUP.' WHERE attribute_set_id = %d', $attributes_set->id );
				$customer_attributes_sections = $wpdb->get_results( $query );
				foreach( $customer_attributes_sections as $k => $customer_attributes_section ) {
					foreach( $account_attributes[$customer_attributes_section->id]['attribut'] as $attribute ) {
						$signup_fields[] = $attribute;
					}
				}
				
				ob_start();
				require_once( $this->get_template_part( "frontend", "signup/signup") );
				$output = ob_get_contents();
				ob_end_clean();

			}
			return $output;
		}
		
		/**
		 * SIGN UP - Save sign up form
		 */
		function wps_save_signup_form() {
			global $wpdb; global $wpshop;
			$user_id = get_current_user_id();
			$status = $account_creation = false; $result = '';
			$exclude_user_meta = array( 'user_email', 'user_pass' );
			$element_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
			if ( !empty( $element_id) ){
				$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d', $element_id );
				$attribute_set_id = $wpdb->get_var( $query );
				if ( !empty($attribute_set_id) ){
					$group  = wps_address::get_addresss_form_fields_by_type( $attribute_set_id );
					foreach ( $group as $attribute_sets ) {
						foreach ( $attribute_sets as $attribute_set_field ) {
							$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'] );
						}
						if ( empty($wpshop->errors) ) {
							$user_name = !empty($_POST['attribute']['varchar']['user_login']) ? $_POST['attribute']['varchar']['user_login'] : $_POST['attribute']['varchar']['user_email'];
							$user_pass = ( !empty($_POST['attribute']['varchar']['user_pass']) && !empty($_POST['wps_signup_account_creation']) ) ? $_POST['attribute']['varchar']['user_pass'] : wp_generate_password( 12, false );
								
							if ( $user_id == 0  ) {
								$user_id = wp_create_user($user_name, $user_pass, $_POST['attribute']['varchar']['user_email']);
								if ( !is_object( $user_id) ) {
									$account_creation = true;
									/** Update newsletter user preferences **/
									$newsletter_preferences = array();
									if( !empty($_POST['newsletters_site']) ) {
										$newsletter_preferences['newsletters_site'] = 1;
									}
									if( !empty($_POST['newsletters_site_partners']) ) {
										$newsletter_preferences['newsletters_site_partners'] = 1;
									}
									
									update_user_meta( $user_id, 'user_preferences', $newsletter_preferences);
								}
							}
								
								
								
							foreach( $attribute_set_field['content'] as $attribute ) {
								if ( !in_array( $attribute['name'], $exclude_user_meta ) ) {
									update_user_meta( $user_id, $attribute['name'], wpshop_tools::varSanitizer( $_POST['attribute'][$attribute['data_type']][$attribute['name']])  );
								}
								else {
									wp_update_user( array('ID' => $user_id, $attribute['name'] => wpshop_tools::varSanitizer( $_POST['attribute'][$attribute['data_type']][$attribute['name']]) ) );
								}
							}
								
							$permalink_option = get_option( 'permalink_structure' );
							$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ));
							$result = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=3';
							$status = true;
								
							if ( $account_creation ) {
								$secure_cookie = is_ssl() ? true : false;
								wp_set_auth_cookie($user_id, true, $secure_cookie);
								wpshop_messages::wpshop_prepared_email($_POST['attribute']['varchar']['user_email'], 'WPSHOP_SIGNUP_MESSAGE', array('customer_first_name' => ( !empty($_POST['attribute']['varchar']['first_name']) ) ? $_POST['attribute']['varchar']['first_name'] : '', 'customer_last_name' => ( !empty($_POST['attribute']['varchar']['last_name']) ) ? $_POST['attribute']['varchar']['last_name'] : '', 'customer_user_email' => ( !empty($_POST['attribute']['varchar']['user_email']) ) ? $_POST['attribute']['varchar']['user_email'] : '') );
							}
								
						}
						else {
							$result = '<div class="wps-alert-error">' .__('Some errors have been detected', 'wpshop') . ' : <ul>';
							foreach(  $wpshop->errors as $error ){
								$result .= '<li>' .$error. '</li>';
							}
							$result .= '</div>';
						}
					}
						
				}
			}
			echo json_encode( array( $status, $result) );
			die();
		}
		
		/** SIGN UP - Display the commercial & newsletter form
		 * @return void
		 */
		function display_commercial_newsletter_form() {
			$output = '';
			$user_preferences = get_user_meta( get_current_user_id(), 'user_preferences', true );
			ob_start();
			require_once( $this->get_template_part( "frontend", "signup/signup", "newsletter") );
			$output = ob_get_contents();
			ob_end_clean();
			
			return $output;
		}
		
		
// 		function get_account_form_attributes_list () {
// 			global $wpdb;
// 			$account_entity_post_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
// 			$attributes_list = array();
// 			if ( !empty($account_entity_post_id) ) {
// 				$attributes_sets_list = wpshop_attributes_set::getElement( $account_entity_post_id, "'valid'", 'entity_id', 'all');
// 				/** Attributes List **/
// 				foreach ( $attributes_sets_list as $attributes_set ) {
// 					if ( $attributes_set->default_set == 'yes' ) {
// 						$attributes_set_details = wpshop_attributes_set::getAttributeSetDetails( $attributes_set->id, "'valid'"  );

// 						foreach ( $attributes_set_details as $attributes_set_section ) {
// 							if ( !empty($attributes_set_section) && !empty($attributes_set_section['attribut']) && is_array($attributes_set_section['attribut']) ) {
// 								foreach( $attributes_set_section['attribut'] as $attribute ) {
// 									$attributes_list[] = $attribute;
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
			
// 			return $attributes_list;
// 		}
		
		
// 		function get_account_form ( $quick_form = false ) {
// 			$output = '';
// 			$attributes = $this->get_account_form_attributes_list();
// 			foreach( $attributes as $attribute ) {
// 				if ( $quick_form && $attribute->is_used_in_quick_add_form == 'yes' ) {
// 					$this->get_form_element( $attribute );
// 				}
// 				else {
// 					$this->get_form_element( $attribute );
// 				}
// 			}
// 			return $output;
// 		}
		
// 		function get_form_element ( $attribute_def ) {
// 			$tpl_component = array();
// 			$tpl_component['ACCOUNT_FORM_ELEMENT_LABEL'] = stripslashes($attribute_def->frontend_label);
// 			$tpl_component['ACCOUNT_FORM_REQUIRED_ELEMENT'] = ( !empty( $attribute_def->is_required ) && $attribute_def->is_required == 'yes' ) ? '*' : '';
// 			$value = ( !empty($_POST) && !empty($_POST['attribute']) && !empty($_POST['attribute'][$attribute_def->data_type]) && !empty($_POST['attribute'][$attribute_def->data_type]) && !empty($_POST['attribute'][$attribute_def->data_type][$attribute_def->code]) ) ? $_POST['attribute'][$attribute_def->data_type][$attribute_def->code] : '';
			
// 			$attribute_definition = wpshop_attributes::get_attribute_field_definition( $attribute_def, $value, array() );
// 			$tpl_component['ACCOUNT_FORM_ELEMENT_INPUT'] = $attribute_definition['output'];
// 			$output = wpshop_display::display_template_element('wps_account_form_element', $tpl_component, array(), 'wpshop');
		
// 			return $output;
// 		}
		
	}
}	
if ( class_exists("wps_account") ) {
	$wps_account = new wps_account();
}
?>