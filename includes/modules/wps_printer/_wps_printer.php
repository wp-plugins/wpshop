<?php
/**
 * Plugin Name: WPShop Printer
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: Wpshop Printer
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * Printer module bootstrap file
 *
 * @author Alexandre Techer - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 */
 
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

DEFINE('WPS_PRINTER_DIR', basename(dirname(__FILE__)) );
DEFINE('WPS_PRINTER_PATH', str_replace( "\\", "/", str_replace( WPS_PRINTER_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_PRINTER_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_PRINTER_PATH ) );



include( plugin_dir_path( __FILE__ ).'/controller/wps_printer_ctr.php' );
include( plugin_dir_path( __FILE__ ).'/controller/wps_billing.php' );
