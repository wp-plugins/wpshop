<?php
/**
 * Plugin Name: WPShop Products
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Products
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */


if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

	/** Template Global vars **/
	DEFINE('WPS_PRODUCT_DIR', basename(dirname(__FILE__)));
	DEFINE('WPS_PRODUCT_PATH', str_replace( "\\", "/", str_replace( WPS_PRODUCT_DIR, "", dirname( __FILE__ ) ) ) );
	DEFINE('WPS_PRODUCT_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_PRODUCT_PATH ) );
	
	include( plugin_dir_path( __FILE__ ).'/controller/wps_product_ctr.php' );
	include( plugin_dir_path( __FILE__ ).'/model/wps_product_mdl.php' );
	
	$wps_product = new wps_product_ctr();