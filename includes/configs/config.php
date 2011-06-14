<?php
/**
* Main config file for the plugin
* 
* Define the different path to each directory in the plugin, define every common configuration. Specific configuration are in other config files
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage config
*/

/**
*	Start plugin version definition
*/
	DEFINE('WPSHOP_VERSION', '1.0');
	DEFINE('WPSHOP_DEBUG', false);


/**
*	Start plugin url slug	
*/
{
	DEFINE('WPSHOP_URL_SLUG_DASHBOARD', 'wpshop_product_main_menu');

	DEFINE('WPSHOP_URL_SLUG_OPTION_MAIN_MENU', 'wpshop_language_list');
	DEFINE('WPSHOP_URL_SLUG_OPTION', 'wpshop_option');
	DEFINE('WPSHOP_URL_SLUG_LANGUAGE', 'wpshop_language_list');
	DEFINE('WPSHOP_URL_SLUG_LANGUAGE_EDITION', 'wpshop_language_list');

	DEFINE('WPSHOP_URL_SLUG_PRODUCT_MAIN_MENU', 'wpshop_product_main_menu');
	DEFINE('WPSHOP_URL_SLUG_PRODUCT_LISTING', 'wpshop_product_list');
	DEFINE('WPSHOP_URL_SLUG_PRODUCT_EDITION', 'wpshop_product');

	DEFINE('WPSHOP_URL_SLUG_CATEGORY_MAIN_MENU', 'wpshop_product_category_main_menu');
	DEFINE('WPSHOP_URL_SLUG_CATEGORY_LISTING', 'wpshop_category_list');
	DEFINE('WPSHOP_URL_SLUG_CATEGORY_EDITION', 'wpshop_category');

	DEFINE('WPSHOP_URL_SLUG_ATTRIBUTE_MAIN_MENU', 'wpshop_attribute_main_menu');
	DEFINE('WPSHOP_URL_SLUG_ATTRIBUTE_LISTING', 'wpshop_attribute_list');
	DEFINE('WPSHOP_URL_SLUG_ATTRIBUTE_EDITION', 'wpshop_attribute');
	DEFINE('WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING', 'wpshop_attribute_group');
	DEFINE('WPSHOP_URL_SLUG_ATTRIBUTE_SET_EDITION', 'wpshop_attribute_group');

	DEFINE('WPSHOP_URL_SLUG_DOCUMENT_LISTING', 'wpshop_document');
	DEFINE('WPSHOP_URL_SLUG_DOCUMENT_EDITION', 'wpshop_document');
}
/*	End plugin url slug		*/


/*	Start plugin paths definition		*/
{
	DEFINE('WPSHOP_HOME_URL', WP_PLUGIN_URL . '/' . WPSHOP_PLUGIN_DIR . '/');
	DEFINE('WPSHOP_HOME_DIR', WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/');
	
	DEFINE('WPSHOP_INC_PLUGIN_DIR', WPSHOP_HOME_DIR . 'includes/');
	DEFINE('WPSHOP_INC_PLUGIN_URL', WPSHOP_HOME_URL . 'includes/');
	DEFINE('WPSHOP_JSURL', WPSHOP_HOME_URL . 'js/');
	DEFINE('WPSHOP_CSS_URL', WPSHOP_HOME_URL . 'css/');
	DEFINE('WPSHOP_MEDIAS_URL', WPSHOP_PLUGIN_DIR . '/medias/');
	DEFINE('WPSHOP_MEDIAS_DIR', WPSHOP_PLUGIN_DIR . '/medias/');

	DEFINE('WPSHOP_UPLOAD_DIR', '/uploads/');
	DEFINE('WPSHOP_UPLOAD_URL', '/uploads/');
	
	DEFINE('WPSHOP_LIB_PLUGIN_DIR', WPSHOP_INC_PLUGIN_DIR . 'librairies/');
	DEFINE('WPSHOP_LIB_PLUGIN_URL', WPSHOP_INC_PLUGIN_URL . 'librairies/');
	DEFINE('WPSHOP_MODULES_PLUGIN_DIR', WPSHOP_INC_PLUGIN_DIR . 'modules/');
	DEFINE('WPSHOP_METABOXES_PLUGIN_DIR', WPSHOP_MODULES_PLUGIN_DIR . 'metaBoxes/');
	DEFINE('WPSHOP_TEMPLATES_PLUGIN_DIR', WPSHOP_HOME_DIR . 'templates/');

	DEFINE('WPSHOP_IMAGE_URL', WPSHOP_MEDIAS_URL . 'images/');
	DEFINE('WPSHOP_ICONS_URL', WPSHOP_IMAGE_URL . 'icones/');
	DEFINE('WPSHOP_PICTOS_URL', WPSHOP_IMAGE_URL . 'pictos/');

	DEFINE('WPSHOP_AJAX_URL', WPSHOP_INC_PLUGIN_URL . 'ajax.php');
}
/*	End plugin paths definition			*/


/*	Start form field display config	*/
{/*	General configuration	*/
	$comboxOptionToHide = array('deleted');
}
{/*	Attributes form	*/
	$attribute_displayed_field = array('id', 'status', 'entity_id', 'is_required', 'data_type', 'frontend_label', 'code', 'default_value');
}
/*	End form field display config		*/


/*	Start definition about the authorised extension for the fileuploader	*/
DEFINE(WPSHOP_AUTORISED_PICTURE_EXTENSION, "['jpg', 'jpeg', 'gif', 'png']");
DEFINE(WPSHOP_AUTORISED_DOCUMENTS_EXTENSION, "['pdf', 'doc', 'docx', 'odt', 'ods', 'xls', 'xlsx']");
/*	End definition about the authorised extension for the fileuploader	*/
