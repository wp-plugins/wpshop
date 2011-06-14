<?php
/*
Plugin Name: wpshop
Description: This plugin allows to manage products in order to sold them
Version: 1.0
Author: Eoxia
Author URI: http://www.eoxia.com
*/

/**
* Plugin main file.
* 
*	This file is the main file called by wordpress for our plugin use. It define the basic vars and include the different file needed to use the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
*/

/**
*	First thing we define the main directory for our plugin in a super global var	
*/
DEFINE('WPSHOP_PLUGIN_DIR', basename(dirname(__FILE__)));
/**
*	Include the different config for the plugin	
*/
require_once(WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/includes/configs/config.php' );
/**
*	Define the path where to get the config file for the plugin
*/
DEFINE('WPSHOP_CONFIG_FILE', WPSHOP_INC_PLUGIN_DIR . 'configs/config.php');
/**
*	Include the file which includes the different files used by all the plugin
*/
require_once(	WPSHOP_INC_PLUGIN_DIR . 'includes.php' );

/*	Create an instance for the database option	*/
global $db_options;
$db_options = new wpshop_db_option();
// echo __FILE__ . '<pre>';print_r($db_options);echo '</pre>';exit;

/**
*	Include tools that will launch different action when plugin will be loaded
*/
require_once(WPSHOP_LIB_PLUGIN_DIR . 'install.class.php' );
/**
*	On plugin loading, call the different element for creation output for our plugin	
*/
register_activation_hook( __FILE__ , array('wpshop_install', 'wpshop_activate') );
register_deactivation_hook( __FILE__ , array('wpshop_install', 'wpshop_deactivate') );

/**
*	Include tools that will launch different action when plugin will be loaded
*/
require_once(WPSHOP_LIB_PLUGIN_DIR . 'init.class.php' );
/**
*	On plugin loading, call the different element for creation output for our plugin	
*/
add_action('plugins_loaded', array('wpshop_init', 'wpshop_plugin_load'));