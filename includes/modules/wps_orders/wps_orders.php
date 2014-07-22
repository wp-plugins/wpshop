<?php/** * Plugin Name: WPShop Orders * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/ * Description : WPShop Orders * Version: 0.1 * Author: Eoxia * Author URI: http://eoxia.com/ *//** * Orders module bootstrap file * * @author ALLEGRE Jérôme - Eoxia dev team <dev@eoxia.com> * @version 0.1 * @package includes * @subpackage modules *//** Template Global vars **/
DEFINE('WPS_ORDERS_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_ORDERS_PATH', str_replace( "\\", "/", str_replace( WPS_ORDERS_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_ORDERS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_ORDERS_PATH ) );
DEFINE( 'WPS_ORDERS_BASE', plugin_dir_path( __FILE__ ) );
include( plugin_dir_path( __FILE__ ).'/controller/wps_orders_ctr.php' );
include( plugin_dir_path( __FILE__ ).'/model/wps_orders_mdl.php' );

$wps_orders = new wps_orders_ctr();