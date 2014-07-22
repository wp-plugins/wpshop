<?php
/*
Plugin Name: Wp Wish list
Description:
Version: 1.0
Author: Eoxia
*/

/**
 * Bootstrap file
 *
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Define the current version for the plugin. Interresting for clear cache for plugin style and script
 * @var string Plugin current version number
 */
DEFINE('WPWISHLIST_VERSION', '1.0');

/**
 * Get the plugin main dirname. Allows to avoid writing path directly into code
 * @var string Dirname of the plugin
 */
DEFINE('WPWISHLIST_DIR', basename(dirname(__FILE__)));

DEFINE('WPWISHLIST_FILE', __FILE__);

DEFINE('WPWISHLIST_PATH', str_replace( "\\", "/", str_replace( WPWISHLIST_DIR, "", dirname(__FILE__) ) ) );

DEFINE('WPWISHLIST_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPWISHLIST_PATH ) );

/**	Include config file */
require_once('config/config.php' );

/**	Include all others file */
require_once(WPWISHLIST_CONFIG_LIBS_DIR . 'files_include.php' );


global $wpeo_wish_list;
$wpeo_wish_list = new wpeo_wish_list_ctr(); // Start plugin


?>