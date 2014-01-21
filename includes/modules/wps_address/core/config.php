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

/** Define core librairies directory */
DEFINE( 'WPS_LOCALISATION_CORELIBS_DIR', WPSHOP_MODULES_DIR . '/' . WPS_LOCALISATION_DIR . '/core/');

/** Define modules librairies directory */
DEFINE( 'WPS_LOCALISATION_MODULESLIBS_DIR', WPSHOP_MODULES_DIR . '/' . WPS_LOCALISATION_DIR . '/modules/');

/**	Define internationnalisation directory	*/
DEFINE( 'WPS_LOCALISATION_LANGUAGES_DIR', WPS_LOCALISATION_DIR . '/languages/');

/**	Define the templates directories	*/
DEFINE( 'WPS_LOCALISATION_TEMPLATES_MAIN_DIR', WPSHOP_MODULES_DIR . '/' . WPS_LOCALISATION_DIR . '/templates/');
DEFINE( 'WPS_LOCALISATION_TEMPLATES_MAIN_URL', str_replace( substr( ABSPATH, 0, -1 ), site_url(), WPSHOP_MODULES_DIR . WPS_LOCALISATION_DIR ) . '/templates/');
DEFINE( 'WPS_LOCALISATION_BACKEND_TPL_DIR', WPS_LOCALISATION_TEMPLATES_MAIN_DIR. 'backend/');
DEFINE( 'WPS_LOCALISATION_BACKEND_TPL_URL', WPS_LOCALISATION_TEMPLATES_MAIN_URL. 'backend/');
DEFINE( 'WPS_LOCALISATION_FRONTEND_TPL_DIR', WPS_LOCALISATION_TEMPLATES_MAIN_DIR . 'wpshop/');
DEFINE( 'WPS_LOCALISATION_FRONTEND_TPL_URL', WPS_LOCALISATION_TEMPLATES_MAIN_URL . 'wpshop/');
DEFINE( 'WPS_LOCALISATION_COMMON_TPL_DIR', WPS_LOCALISATION_TEMPLATES_MAIN_DIR . 'common/');
DEFINE( 'WPS_LOCALISATION_COMMON_TPL_URL', WPS_LOCALISATION_TEMPLATES_MAIN_URL . 'common/');

/**	Check if there are options corresponding to this debug vars	*/
$wpee_extra_options = get_option('wpeogeoloc_extra_options', array());
/**	Define all var as global for use in all plugin	*/
foreach ( $wpee_extra_options as $options_key => $options_value ) {
	if ( is_array($options_value) ) {
		$options_value= serialize($options_value);
	}
	DEFINE( strtoupper($options_key), $options_value );
}