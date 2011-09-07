<?php
/**
* Plugin Name: wpshop
* Plugin URI: http://eoxia.com/
* Description: With this plugin you will be able to manage the products you want to sell and user would be able to buy this products
* Version: 1.1
* Author: Eoxia
* Author URI: http://eoxia.com/
*/

/**
* Plugin main file.
* 
*	This file is the main file called by wordpress for our plugin use. It define the basic vars and include the different file needed to use the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.1
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

/*	Call main initialisation function	*/
add_action('init', array('wpshop_init', 'load'));

/*	Call function to create the main left menu	*/
add_action('admin_menu', array('wpshop_init', 'admin_menu'));

/*	Call function for new wordpress element creating [term (product_category) / post (product)]	*/
add_action('init', array('wpshop_init', 'add_new_wp_type'));

/*	Call function allowing to change element front output	*/
add_action('the_content', array('wpshop_frontend_display', 'products_page'), 1);
add_action('archive_template', array('wpshop_categories', 'category_template_switcher'));

/*	On plugin activation call the function for default configuration creation	*/
include(WPSHOP_LIBRAIRIES_DIR . 'install.class.php');
register_activation_hook( __FILE__ , array('wpshop_install', 'install_wpshop') );
/*	On plugin deactivation call the function to clean the wordpress installation	*/
register_deactivation_hook( __FILE__ , array('wpshop_install', 'uninstall_wpshop') );