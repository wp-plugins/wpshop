<?php
/**
 * Plugin Name: WPShop Login
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description:  WPShop Login
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WpShop  WPShop Login bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
 
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wps_login") ) {
	class wps_login {
		function __construct() {
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			add_shortcode( 'wpshop_login', array( &$this, 'get_login_form'));
			
			
			add_action('wp_ajax_wps_display_connexion_form', array(&$this, 'wps_ajax_get_login_form_interface') );
			add_action('wp_ajax_nopriv_wps_display_connexion_form', array(&$this, 'wps_ajax_get_login_form_interface') );
			
			add_action('wp_ajax_wps_login_request', array(&$this, 'control_login_form_request') );
			add_action('wp_ajax_nopriv_wps_login_request', array(&$this, 'control_login_form_request') );
			
			
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'wps_login_js', plugins_url('templates/wpshop/js/wps_login.js', __FILE__) );
			
			
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
		
		function get_login_form() {
			$tpl_component = array();
			//$tpl_component['WPS_LOGIN_ALERT_MESSAGE'] = self::control_login_form_request();
			$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
			
			$output = wpshop_display::display_template_element('wps_login_form', $tpl_component, array(), 'wpshop');
			unset( $tpl_component );
			return $output;
		}
		
		function control_login_form_request() {
			$result = '';
			$status = false;
			if ( !empty($_POST['wps_login_user_login']) && !empty($_POST['wps_login_password']) ) {
				
				$creds = array();
				$creds['user_login'] = sanitize_user($_POST['wps_login_user_login']);
				$creds['user_password'] = wpshop_tools::varSanitizer( $_POST['wps_login_password'] );
				$creds['remember'] =  ( !empty($_POST['wps_login_remember_me']) ) ? true : false;
				$user = wp_signon( $creds, false );
				if ( is_wp_error($user) ) {
					$result = '<div class="wps-alert wps-alert-error">' .__('Connexion error', 'wpshop'). '</div>';
				}
				else {
					$permalink_option = get_option( 'permalink_structure' );
					$result = get_permalink( get_option('wpshop_checkout_page_id') ).( (!empty($permalink_option) ) ? '?order_step=2' : '&order_step=2' ) ;
					$status = true;
				}
			}
			else {
				$result = '<div class="wps-alert wps-alert-error">' .__('E-Mail and Password are required', 'wpshop'). '</div>';
			}
			
			echo json_encode( array( $status, $result) );
			die();
		}
	
		function wps_ajax_get_login_form_interface() {
			$response = array( 'status' => true, 'response' => self::get_login_form() );
			echo json_encode( $response );
			die();
		}
	}
}
if ( class_exists("wps_login") ) {
	$wps_login = new wps_login();
}