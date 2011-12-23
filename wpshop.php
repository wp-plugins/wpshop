<?php
/**
* Plugin Name: WP-Shop
* Plugin URI: http://eoxia.com/
* Description: With this plugin you will be able to manage the products you want to sell and user would be able to buy this products
* Version: 1.3.0.0
* Author: Eoxia
* Author URI: http://eoxia.com/
*/

/**
* Plugin main file.
* 
*	This file is the main file called by wordpress for our plugin use. It define the basic vars and include the different file needed to use the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.3
* @package wpshop
*/

/**
*	First thing we define the main directory for our plugin in a super global var	
*/
DEFINE('WPSHOP_PLUGIN_DIR', basename(dirname(__FILE__)));

/*	Include the config file	*/
require(WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/includes/config.php');

/*	Include the main including file	*/
require(WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/includes/include.php');

/*	Check and set (if needed) administrator(s) permissions' each time the plugin is launched. Admin role has all right	*/
wpshop_permissions::set_administrator_role_permission();
wpshop_permissions::wpshop_init_roles();

/*	Call main initialisation function	*/
add_action('init', array('wpshop_init', 'load'));

/*	Call function to create the main left menu	*/
add_action('admin_menu', array('wpshop_init', 'admin_menu'));

/*	Call function for new wordpress element creating [term (product_category) / post (product)]	*/
add_action('init', array('wpshop_init', 'add_new_wp_type'));

/*	Call function allowing to change element front output	*/
add_action('the_content', array('wpshop_frontend_display', 'products_page'), 1);
add_action('archive_template', array('wpshop_categories', 'category_template_switcher'));
add_action('add_meta_boxes', array('wpshop_metabox','add_some_meta_box'));

/*	On plugin activation call the function for default configuration creation	*/
include(WPSHOP_LIBRAIRIES_DIR . 'install.class.php');
//register_activation_hook( __FILE__ , array('wpshop_install', 'install_wpshop') );
/*	On plugin deactivation call the function to clean the wordpress installation	*/
register_deactivation_hook( __FILE__ , array('wpshop_install', 'uninstall_wpshop') );

/*	Get current plugin version	*/
$current_db_version = get_option('wpshop_db_options', 0);
// Si la bdd est installée
if(isset($current_db_version['db_version']) && $current_db_version['db_version']>0){
	add_action('admin_init', array('wpshop_install', 'update_wpshop'));
	add_action('admin_init', array('wpshop_database', 'check_database'));
}

// Start session
session_start();

// Instantiation de la classe wpshop
$wpshop = &new wpshop();
$wpshop_account = &new wpshop_account();
$wpshop_paypal = &new wpshop_paypal();

// Gestion des ShortCode
add_shortcode('wpshop_att_val', array('wpshop_attributes', 'wpshop_att_val_func'));
add_shortcode('wpshop_product', array('wpshop_products', 'wpshop_product_func'));
add_shortcode('wpshop_products', array('wpshop_products', 'wpshop_products_func'));
add_shortcode('wpshop_category', array('wpshop_categories', 'wpshop_category_func'));
add_shortcode('wpshop_att_group', array('wpshop_attributes_set', 'wpshop_att_group_func'));
// Basket
$GLOBALS['cart'] = new wpshop_cart();
add_shortcode('wpshop_cart', array('wpshop_cart', 'display_cart'));
// Checkout
add_shortcode('wpshop_checkout', 'wpshop_checkout_init');
// Customer account
add_shortcode('wpshop_myaccount', 'wpshop_account_display_form');

/* Display a notice that can be dismissed */
add_action('admin_notices', 'tpl_admin_notice');
add_action('admin_notices', 'paymentMethod_admin_notice');
function tpl_admin_notice() {
    /* Check that the user hasn't already clicked to ignore the message */
	$templateVersions = get_option('wpshop_templateVersions', array());
    if(empty($templateVersions[WPSHOP_TPL_VERSION])) {
        echo '<div class="updated"><p>';
		echo __('This version of WP-Shop need to reset the template files with default plugin files to work correctly. Please don\'t forget to reset it from the settings page.','wpshop');
        echo '</p></div>';
    }
}
function paymentMethod_admin_notice() {
    /* Check that the user has already choose a payment method */
	$paymentMethod = get_option('wpshop_paymentMethod', array());
	if(empty($paymentMethod['paypal']) && empty($paymentMethod['checks'])) {
        echo '<div class="updated"><p>';
		echo __('You haven\'t choose any payment method, please choose in the settings page.','wpshop');
        echo '</p></div>';
    }
}

?>