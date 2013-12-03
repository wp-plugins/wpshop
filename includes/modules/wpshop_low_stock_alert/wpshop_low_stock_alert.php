<?php
/**
 * Plugin Name: WP-Shop-low-stock-alert-module
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: Display an alert on frontend when products stocks ar low
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * Low-stock alert module bootstrap file
 *
 * @author Alexandre Techer - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 */

/** Check if the plugin version is defined. If not defined script will be stopped here */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wpshop_low_stock_alert") ) {
	class wpshop_low_stock_alert {
		function __construct() {
			global $wpdb;
			$locale = get_locale();
			if ( defined("ICL_LANGUAGE_CODE") ) {
				$query = $wpdb->prepare("SELECT locale FROM " . $wpdb->prefix . "icl_locale_map WHERE code = %s", ICL_LANGUAGE_CODE);
				$local = $wpdb->get_var($query);
				$locale = !empty($local) ? $local : $locale;
			}
			$moFile = dirname(__FILE__).'/languages/wpshop_low_stock_alert-' . $locale . '.mo';
			if ( !empty($locale) && (is_file($moFile)) ) {
				load_textdomain('wpshop_low_stock_alert', $moFile);
			}

			/**	Add custom template for current module	*/
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			add_action('admin_init', array(&$this, 'create_options'));
			if ( is_admin() ) {
				wp_enqueue_script("jquery");
				wp_enqueue_script( 'wpshop_low_stock_alert_js', plugins_url('/templates/admin/js/wpshop_low_stock_alert.js', __FILE__) );
			}
		}

		/** Load module/addon automatically to existing template list
		 *
		 * @param array $templates The current template definition
		 *
		 * @return array The template with new elements
		 */
		function custom_template_load( $templates ) {
			include('templates/admin/main_elements.tpl.php');
			include('templates/wpshop/main_elements.tpl.php');
			$templates = wpshop_display::add_modules_template_to_internal( $tpl_element, $templates );
			unset($tpl_element);

			return $templates;
		}

		/**
		 * Create the options for low-stock alert Module
		 */
		function create_options () {
			register_setting('wpshop_options', 'wpshop_low_stock_alert_options', array(&$this, 'wpshop_low_stock_alert_validator'));
			add_settings_field('wpshop_display_low_stock', __('Display Low stock Alert message', 'wpshop_low_stock_alert'), array('wpshop_low_stock_alert', 'wpshop_display_low_stock_alert_interface'), 'wpshop_display_option', 'wpshop_display_options_sections');

		}

		function wpshop_low_stock_alert_validator ($input) {
			return $input;
		}
		/**
		 * Display the low-stock alert admin interface
		 */
		function wpshop_display_low_stock_alert_interface () {
			$low_stock_option = get_option('wpshop_low_stock_alert_options');
			$tpl_component['ACTIVATE_LOW_STOCK_ALERT'] = ( (!empty($low_stock_option) && !empty($low_stock_option['active']) && $low_stock_option['active'] == 'on') ? 'checked="checked"' : null);
			$tpl_component['BASED_ON_REAL_STOCKS'] = ( !empty($low_stock_option) && !empty($low_stock_option['based_on_stock']) && $low_stock_option['based_on_stock'] == 'yes') ? 'checked="checked"' : null;
			$tpl_component['NOT_BASED_ON_REAL_STOCKS'] = ( !empty($low_stock_option) && !empty($low_stock_option['based_on_stock']) && $low_stock_option['based_on_stock'] == 'no') ? 'checked="checked"' : null;
			$tpl_component['LOW_STOCK_ALERT_LIMIT'] = ( !empty($low_stock_option) && !empty($low_stock_option['alert_limit']) ) ? $low_stock_option['alert_limit'] : '';
			$output = wpshop_display::display_template_element('wpshop_low_stock_admin_interface', $tpl_component, array(), 'admin');
			echo $output;
		}

		/**
		 * Display the alert message in the hook
		 */
		function display_alert_message ( $post_ID ) {
			$result = '';
			$low_stock_alert_option  = get_option('wpshop_low_stock_alert_options');
			if ( !empty( $low_stock_alert_option  ) && !empty($low_stock_alert_option['active']) && !empty($post_ID) ) {
				$product_meta = get_post_meta( $post_ID, '_wpshop_product_metadata', true);
				$product_stock = $product_meta['product_stock'];
				if ($product_stock > 0 ) {
					$tpl_component['MEDIAS_ICON_URL'] = WPSHOP_MEDIAS_ICON_URL;
					if ( !empty($low_stock_alert_option['based_on_stock']) && $low_stock_alert_option['based_on_stock'] == 'yes' && !empty( $low_stock_alert_option['alert_limit']) ) {
						if ( $product_stock <= $low_stock_alert_option['alert_limit'] ) {
							$tpl_component['REST_PRODUCT_QTY'] = sprintf( __('%s products in stock', 'wpshop_low_stock_alert'), $product_stock);
							$result = wpshop_display::display_template_element('wpshop_low_stock_alert_based_on_real_stock', $tpl_component, array(), 'wpshop');
						}
					}
					else {
						$result = wpshop_display::display_template_element('wpshop_low_stock_alert_not_based_on_real_stock', $tpl_component, array(), 'wpshop');
					}
				}
			}
			return $result;
		}
	}

}
if (class_exists("wpshop_low_stock_alert"))
{
	$inst_wpshop_low_stock_alert = new wpshop_low_stock_alert ();
	//add_action('wpshop_low_stock_alert', array('wpshop_low_stock_alert', 'display_alert_message'));
}