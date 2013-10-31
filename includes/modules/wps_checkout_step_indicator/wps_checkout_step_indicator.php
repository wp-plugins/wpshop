<?php
/**
 * Plugin Name: WPShop Checkout Step Indicator
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WP Shop Shipping Mode
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * Cart rules bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
 
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

if ( !class_exists("wps_checkout_step_indicator") ) {
	class wps_checkout_step_indicator {
		function __construct() {
			add_shortcode('wps_checkout_step_indicator', array(&$this, 'get_checkout_step_indicator') );
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
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
		
		function get_checkout_step_indicator() {
			$output = '';
			$tpl_component = $sub_tpl_component = array();
			$default_step = ( !empty( $_GET['order_step'] ) ) ? wpshop_tools::varSanitizer( $_GET['order_step'] ) : 1;
			$steps = array('1' => __('Your informations', 'wpshop'), '2' => __('Shipping modes', 'wpshop'), '3' => __('Secure payment', 'wpshop') );
			$steps_tpl = '';
			foreach ( $steps as $step_id => $step ) {
				$sub_tpl_component['CHECKOUT_STEP_ID'] = $step_id;
				$sub_tpl_component['CHECKOUT_STEP_NAME'] = $step;
				$sub_tpl_component['CURRENT_STEP_CLASS'] = ( $default_step == $step_id ) ? 'wps-currents-step' : '' ;
				$steps_tpl .= wpshop_display::display_template_element('wps_checkout_step', $sub_tpl_component, array(), 'wpshop');
				unset( $sub_tpl_component );
			}
			$tpl_component['CHECKOUT_STEPS'] = $steps_tpl;
			$output = wpshop_display::display_template_element('wps_checkout_step_indicator', $tpl_component, array(), 'wpshop');
			unset( $tpl_component );
			return $output;
		}
		
		
	}
	
}
/**	Instanciate the module utilities if not	*/
if ( class_exists("wps_checkout_step_indicator") ) {
	$wps_checkout_step_indicator = new wps_checkout_step_indicator();
}