<?php
/**
* Pictures config file
* 
*	Define the different path to pictures used in the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage config
*/

/**
*	Define the picture used in the main wordpress menu
*/
DEFINE('WPSHOP_MENU_ICON', WP_PLUGIN_URL . '/' . WPSHOP_IMAGE_URL . 'pictos/picto_menu.png');

DEFINE('WPSHOP_LOADING_ICON', WPSHOP_IMAGE_URL . 'pictos/loading.gif');

DEFINE('WPSHOP_OPTIONS_ICON', WPSHOP_IMAGE_URL . 'icones/options.png');

DEFINE('WPSHOP_SUCCES_ICON', admin_url('images/yes.png'));
DEFINE('WPSHOP_ERROR_ICON', admin_url('images/no.png'));

DEFINE('MAX_PICTURE_SIZE', 100 * 1024 * 1024);