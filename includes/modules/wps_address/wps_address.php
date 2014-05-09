<?php
/*
Plugin Name: Eoxia - Addresses management
Description: Allows to manage addresses for a custom post type created into wordpress
Version: 1.0
Author: Eoxia
Author URI: http://eoxia.com/
*/

/**
* Bootstrap file
*
* @author Development team <dev@eoxia.com>
* @version 1.0
*/

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}


/**
* Define the current version for the plugin. Interresting for clear cache for plugin style and script
* @var string Plugin current version number
*/
DEFINE('WPS_LOCALISATION_VERSION', '1.0');

/**
* Get the plugin main dirname. Allows to avoid writing path directly into code
* @var string Dirname of the plugin
*/
DEFINE('WPS_LOCALISATION_DIR', basename(dirname(__FILE__)));

/** Template Global vars **/
DEFINE('WPS_ADDRESS_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_ADDRESS_PATH', str_replace( "\\", "/", str_replace( WPS_ADDRESS_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_ADDRESS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_ADDRESS_PATH ) );


/**	Include config file */
require_once(WPSHOP_MODULES_DIR . '/' . WPS_LOCALISATION_DIR . '/core/config.php' );

/** Include all librairies on plugin load */
require_once( WPS_LOCALISATION_CORELIBS_DIR . '/files_include.php' );

/** Plugin initialisation */
$wps_address = new wps_address();

?>