<?php
/**
 * Plugin Name: WP Shop Forgot Password
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Forgot PassWord
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
if ( !class_exists("wps_forgot_password") ) {
	class wps_forgot_password {
		function __construct() {
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			
			/** Ajax Requests **/
			add_action('wp_ajax_wps_forgot_password_request', array( &$this, 'wps_forgot_password_request') );
			add_action('wp_ajax_nopriv_wps_forgot_password_request', array( &$this, 'wps_forgot_password_request') );
			
			add_action('wp_ajax_get_forgot_password_form', array( &$this, 'wps_ajax_get_forgot_password_form') );
			add_action('wp_ajax_nopriv_get_forgot_password_form', array( &$this, 'wps_ajax_get_forgot_password_form') );
			
			add_action('wp_ajax_wps_forgot_password_renew', array( &$this, 'wps_forgot_password_renew') );
			add_action('wp_ajax_nopriv_wps_forgot_password_renew', array( &$this, 'wps_forgot_password_renew') );
			
			/** Enqueue Script **/
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'wps_forgot_password_js', plugins_url('templates/wpshop/js/wps_forgot_password.js', __FILE__) );
			
			wp_enqueue_script('utils');
			wp_enqueue_script('user-profile');
			
			add_shortcode( 'wpshop_forgot_password', array( &$this, 'get_forgot_password_form'));
		}
		
		/** Load module/addon automatically to existing template list
		 *
		 * @param array $templates The current template definition
		 *
		 * @return array The template with new elements
		 */
		function custom_template_load( $templates ) {
			include('templates/wpshop/main_elements.tpl.php');
			$templates = wpshop_display::add_modules_template_to_internal( $tpl_element, $templates );
			unset($tpl_element);
		
			return $templates;
		}
		
		/** 
		 * Display the forgot Password Form
		 */
		function get_forgot_password_form() {
			global $wpdb;
			$tpl_component = array();
			$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
			if( !empty( $_GET['action']) && $_GET['action'] == 'retrieve_password' ) {
				$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", wpshop_tools::varSanitizer($_GET['key']), wpshop_tools::varSanitizer($_GET['login']) ));
				if ( !empty($user) ) {
					$tpl_component['ACTIVATION_KEY'] = $_GET['key'];
					$tpl_component['USER_LOGIN'] = $_GET['login'];
					$output = wpshop_display::display_template_element('wps_forgot_password_form_renew', $tpl_component, array(), 'wpshop');
					
				}
				else {
					$output = wpshop_display::display_template_element('wps_forgot_password_form_request', $tpl_component, array(), 'wpshop');
				}
			}
			else {
				$output = wpshop_display::display_template_element('wps_forgot_password_form_request', $tpl_component, array(), 'wpshop');
			}
			unset( $tpl_component);
			return $output;
		}
		
		/**
		 * Forgot Password Request 
		 */
		function wps_forgot_password_request() {
			global $wpdb;
			$status = false; $result = '';
			$user_login = ( !empty( $_POST['wps_user_login']) ) ? wpshop_tools::varSanitizer($_POST['wps_user_login']) : null;
			if ( !empty($user_login) ) {
				$exist_user = get_user_by('login', $user_login);
				if ( !empty($exist_user) ) {
					$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
					if ( empty($key) ) {
						$key = wp_generate_password(20, false);
						$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
					}
					$this->send_forgot_password_email($key, $user_login, $exist_user);
					$result = '<div class="wps-alert wps-alert-info">' .__('An e-mail with an password renew link has been sent to you', 'wpshop'). '</div>';
					$status = true;
				}
				else {
					$result = '<div class="wps-alert wps-alert-error">' .__('No customer account corresponds to this email', 'wpshop'). '</div>';
				}
			}
			else {
				$result = '<div class="wps-alert wps-alert-error">' .__('Please fill the required field', 'wpshop'). '</div>';
			}
			$response = array( $status, $result );
			echo json_encode( $response );
			die();
		}
		
		/**
		 * Send Forgot Password Email Initialisation
		 * @param unknown_type $key
		 * @param unknown_type $user_login
		 */
		function send_forgot_password_email($key, $user_login, $exist_user){
			$user_data = $exist_user->data;
			$email = $user_data->user_email;
			
			$first_name = get_user_meta( $user_data->ID, 'first_name', true );
			$last_name = get_user_meta( $user_data->ID, 'last_name', true );
			$permalink_option = get_option( 'permalink_structure' );
			$link = '<a href="' .get_permalink( get_option('wpshop_checkout_page_id') ).( (!empty($permalink_option)) ? '?' : '&').'action=retrieve_password&key=' .$key. '&login=' .rawurlencode($user_login). '">' .get_permalink( get_option('wpshop_checkout_page_id') ). '&action=retrieve_password&key=' .$key. '&login=' .rawurlencode($user_login). '</a>';
			if( !empty($key) && !empty( $user_login ) ) {
				wpshop_messages::wpshop_prepared_email($email,
				'WPSHOP_FORGOT_PASSWORD_MESSAGE',
				array( 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'forgot_password_link' => $link)
				);
			}
		}
		
		function wps_forgot_password_renew() {
			global $wpdb;
			$status = false; $result = '';
			$password = ( !empty( $_POST['pass1']) ) ? wpshop_tools::varSanitizer( $_POST['pass1'] ) : null;
			$confirm_password = ( !empty( $_POST['pass2']) ) ? wpshop_tools::varSanitizer( $_POST['pass2'] ) : null;
			$activation_key = ( !empty( $_POST['activation_key']) ) ?  wpshop_tools::varSanitizer( $_POST['activation_key'] ) : null;
			$login = ( !empty( $_POST['user_login']) ) ?  wpshop_tools::varSanitizer( $_POST['user_login'] ) : null;
			if ( !empty($password) && !empty($confirm_password) && $confirm_password == $password ) {
				if ( !empty($activation_key) && !empty($login) ) {
					$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $activation_key, $login ) );
					if ( !empty( $user) ){
						wp_set_password($password, $user->ID);
						wp_password_change_notification($user);
						$status = true;
						$result = '<div class="wps-alert wps-alert-success">' .__('Your password has been updated', 'wpshop'). '. <a href="#" id="display_connexion_form"> ' .__('Connect you', 'wpshop').' !</a></div>';
					}
				}
				else {
					$result = '<div class="wps-alert wps-alert-error">' .__('Invalid activation key', 'wpshop'). '</div>';
				}
			}
			else {
				$result = '<div class="wps-alert wps-alert-error">' .__('Password and confirmation password are differents', 'wpshop'). '</div>';
			}
			
			$response = array( $status, $result );
			echo json_encode( $response);
			die();
		}
		
		
		function wps_ajax_get_forgot_password_form() {
			echo json_encode( array(self::get_forgot_password_form() ) );
			die();
		}
	}
}
if ( class_exists("wps_forgot_password") ) {
	$wps_forgot_password = new wps_forgot_password();
}