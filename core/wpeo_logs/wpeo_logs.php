<?php
/*
 * Plugin Name: Logs management by Eoxia
 * Description: This plugins allows to log informations and display them later / Plugin de gestion de logs
 * Version: 1.0
 * Author: Eoxia dev team <dev@eoxia.com>
 * Author URI: http://www.eoxia.com/
 * License: GPL2
 */

/**
 * Bootstrap file for plugin. Do main includes and create new instance for plugin components
 *
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 */

global $wpeologs;
if( !isset( $wpeologs ) ) {

	DEFINE( 'WPEO_LOGS_VERSION', '1.0' );
	DEFINE( 'WPEO_LOGS_DIR', basename(dirname(__FILE__)));
	DEFINE( 'WPEO_LOGS_PATH', dirname( __FILE__ ) );
	DEFINE( 'WPEO_LOGS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', str_replace( "\\", "/", WPEO_LOGS_PATH ) ) );

	/**	Load plugin translation	*/
	load_plugin_textdomain( 'wpeologs-i18n', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/**	Define the templates directories	*/
	DEFINE( 'WPEO_LOGS_TEMPLATES_MAIN_DIR', WPEO_LOGS_PATH . '/templates/');

	require_once( WPEO_LOGS_PATH . '/controller/wpeologs_ctr.php' );
	require_once( WPEO_LOGS_PATH . '/controller/wpeologs_settings_ctr.php' );
	require_once( WPEO_LOGS_PATH . '/controller/wpeologs_display_log_ctr.php' );

	$wpeologs = new wpeologs_ctr();
	$wpeologs->get_settings();

	$wpeologs_settings = new wpeologs_settings_ctr();

	new wpeologs_display_log_ctr();

	register_activation_hook( __FILE__, array( $wpeologs, 'install_service'));
}

?>