<?php
/**
 Plugin Name: WPShop Quotation
Description: Quotation for WPShop
Version: 1.0
Author: Eoxia
Author URI: http://eoxia.com/
*/

DEFINE('WPS_QUOTATION_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_QUOTATION_PATH', str_replace( "\\", "/", str_replace( WPS_QUOTATION_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_QUOTATION_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_QUOTATION_PATH ) );

load_plugin_textdomain( 'wps_quotation', false, basename(dirname(__FILE__)).'/languages/');

include( plugin_dir_path( __FILE__ ).'/controller/wps_quotation_backend_ctr.php' );

new wps_quotation_backend_ctr();