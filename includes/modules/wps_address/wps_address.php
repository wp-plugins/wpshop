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

DEFINE( 'WPS_LOCALISATION_VERSION', '1.' );
DEFINE( 'WPS_LOCALISATION_DIR', basename(dirname(__FILE__)));
DEFINE( 'WPS_ADDRESS_DIR', basename(dirname(__FILE__)));
DEFINE( 'WPS_ADDRESS_PATH', dirname( __FILE__ ) );
DEFINE( 'WPS_ADDRESS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', str_replace( "\\", "/", WPS_ADDRESS_PATH ) ) );

DEFINE( 'WPS_LOCALISATION_TEMPLATES_MAIN_DIR', WPS_ADDRESS_PATH . '/templates/' );

/**	Load plugin translation	*/
load_plugin_textdomain( 'wpeo_geoloc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**	Load wps address controller	*/
require_once( WPS_ADDRESS_PATH . '/controller/wps_address_ctr.php' );
require_once( WPS_ADDRESS_PATH . '/controller/wps_address_admin_ctr.php' );

/** Plugin initialisation */
$wps_address = new wps_address();
$wps_address_admin = new wps_address_admin();

?>