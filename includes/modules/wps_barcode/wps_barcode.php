<?php
/**
 * Plugin Name: WPSHOP Barcode
 * Version: 1.0
 * Description: Générateur de code-barres / Barcode generator
 * Author: Christophe DALOZ DE LOS RIOS - Eoxia dev team <dev@eoxia.com>
 * Author URI: http://www.eoxia.com
 */

$upload_dir = wp_upload_dir();

DEFINE( 'WPS_BARCODE_VERSION', 1.0 );
DEFINE( 'WPS_BARCODE_DIR', basename( dirname( __FILE__ ) ) );
DEFINE( 'WPS_BARCODE_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );
DEFINE( 'WPS_BARCODE_URL', str_replace( str_replace( "\\", "/", ABSPATH),
	site_url() . '/', WPS_BARCODE_PATH ) );
DEFINE( 'WPS_BARCODE_JSCRIPTS', plugins_url('/assets/js', __FILE__) );
DEFINE( 'WPS_BARCODE_UPLOAD', $upload_dir[ 'basedir' ] . '/wps_barcode/');

/**	Load plugin translation	*/
load_plugin_textdomain( 'wps_barcode', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );

/**	Define the templates directories	*/
DEFINE( 'WPS_BARCODE_TEMPLATES_MAIN_DIR', WPS_BARCODE_PATH . '/templates/');

require_once( WPS_BARCODE_PATH . 'controller/wps_barcode.ctr.php' );
require_once( WPS_BARCODE_PATH . 'controller/wps_barcode_settings.ctr.php' );
require_once( WPS_BARCODE_PATH . 'controller/wps_barcode_metabox.ctr.php' );

new wps_barcode();
new wps_barcode_settings();
new wps_barcode_metabox();

?>