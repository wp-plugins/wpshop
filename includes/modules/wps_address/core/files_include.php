<?php
/**
 * Main plugin configuration file
 *
 * @author Developpment team <dev@eoxia.com>
 * @version 1.0
 * @package core
 * @subpackage configuration
 */

/** Check if the plugin version is defined. If not defined script will be stopped here	*/
if ( !defined( 'WPS_LOCALISATION_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpeo_geoloc') );
}

/**	Include display component	*/
require_once(WPS_LOCALISATION_CORELIBS_DIR . 'wps_address.class.php' );