<?php 
/**
 Plugin Name: Export Client
 Description: Export your clients list
 */
/**
 * Base file
 * @author Sanchez Cédric
 * @version 0.1
 * @package File
 */
 
include( plugin_dir_path( __FILE__ ).'/controller/exportclientctr.php' );
include( plugin_dir_path( __FILE__ ).'/model/exportclientmdl.php' );
include ( plugin_dir_path( __FILE__ ).'/include/exportdisplay.php' );

DEFINE( 'WPS_EXPORT_VERSION', '1.0' );
DEFINE( 'WPS_EXPORT_DIR', basename( dirname( __FILE__ ) ) );
DEFINE( 'WPS_EXPORT_PATH', str_replace( "\\", "/", str_replace( WPS_EXPORT_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE( 'WPS_EXPORT_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_EXPORT_PATH ) );
load_plugin_textdomain( 'wpsexport_i18n', false, dirname(plugin_basename( __FILE__ )).'/languages/');

$exportclientctr = new exportclientctr();