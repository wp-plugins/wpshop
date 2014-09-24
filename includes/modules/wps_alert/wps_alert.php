<?php

/**
 * Plugin Name: WPShop Alert
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WPShop Alert
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WPShop Statistics bootstrap file
 * @author Cédric SANCHEZ - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */

DEFINE('WPS_ALERT_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_ALERT_PATH_TO_MODULE', str_replace( str_replace( "\\", "/", WP_PLUGIN_DIR ), "", str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) ) );
DEFINE('WPS_ALERT_PATH', str_replace( "\\", "/", str_replace( WPS_ALERT_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_ALERT_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_ALERT_PATH ) );
 
 load_plugin_textdomain('wpsalert_i18n', false, dirname(plugin_basename( __FILE__ )).'/languages/' );
 include (WPS_ALERT_PATH. WPS_ALERT_DIR. '/controller/wps_alert_ctr.php');
 include (WPS_ALERT_PATH. WPS_ALERT_DIR. '/model/wps_alert_mdl.php');
 
 $wps_alert_ctr = new wps_alert_ctr();