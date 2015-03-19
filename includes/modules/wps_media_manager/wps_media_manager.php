<?php
/**
 Plugin Name: WPShop Media Manager v3
 Description: Manage media for WPShop
 Version: 1.0
 Author: Eoxia
 Author URI: http://eoxia.com/
 */

DEFINE('WPS_MEDIA_MANAGER_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_MEDIA_MANAGER_PATH', str_replace( "\\", "/", str_replace( WPS_MEDIA_MANAGER_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_MEDIA_MANAGER_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_MEDIA_MANAGER_PATH ) );

load_plugin_textdomain( 'wps_media_manager', false, basename(dirname(__FILE__)).'/languages/');

include( plugin_dir_path( __FILE__ ).'/controller/wps_media_manager_backend_ctr.php' );
include( plugin_dir_path( __FILE__ ).'/controller/wps_media_manager_frontend_ctr.php' );

new wps_media_manager_backend_ctr();