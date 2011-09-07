<?php
/**
* Plugin configuration file.
* 
*	This file contains the different static configuration for the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage includes
*/

/*	Declare wordpress database class reference var	*/
global $wpdb;

DEFINE('WPSHOP_VERSION', '1.1');

/**
*	Define the different path for the plugin
*/
{
	/*	Define main plugin directory for our plugin	*/
	DEFINE('WPSHOP_DIR', WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR);
	DEFINE('WPSHOP_URL', WP_PLUGIN_URL . '/' . WPSHOP_PLUGIN_DIR);


	/*	Define includes directory for our plugin	*/
	DEFINE('WPSHOP_INCLUDES_DIR', WPSHOP_DIR . '/includes/');
	DEFINE('WPSHOP_INCLUDES_URL', WPSHOP_URL . '/includes/');
		/*	Define librairies directory for our plugin	*/
		DEFINE('WPSHOP_LIBRAIRIES_DIR', WPSHOP_INCLUDES_DIR . 'librairies/');
		DEFINE('WPSHOP_LIBRAIRIES_URL', WPSHOP_INCLUDES_URL . 'librairies/');
		/*	Define languages directory for our plugin	*/
		DEFINE('WPSHOP_LANGUAGES_DIR', WPSHOP_INCLUDES_DIR . 'languages/');
		DEFINE('WPSHOP_LANGUAGES_URL', WPSHOP_INCLUDES_URL . 'languages/');
		/*	Define templates directory for our plugin	*/
		DEFINE('WPSHOP_TEMPLATES_DIR', WPSHOP_INCLUDES_DIR . 'templates/');
		DEFINE('WPSHOP_TEMPLATES_URL', WPSHOP_INCLUDES_URL . 'templates/');


	/*	Define medias directory for our plugin	*/
	DEFINE('WPSHOP_MEDIAS_DIR', WPSHOP_DIR . '/medias/');
	DEFINE('WPSHOP_MEDIAS_URL', WPSHOP_URL . '/medias/');
		DEFINE('WPSHOP_MEDIAS_IMAGES_DIR', WPSHOP_MEDIAS_DIR . '/images/');
		DEFINE('WPSHOP_MEDIAS_IMAGES_URL', WPSHOP_MEDIAS_URL . '/images/');

	/*	Define upload dir	*/
	$wp_upload_dir = wp_upload_dir();
	DEFINE('WPSHOP_UPLOAD_DIR', $wp_upload_dir['basedir'] . '/wpshop/');
	DEFINE('WPSHOP_UPLOAD_URL', $wp_upload_dir['baseurl'] . '/wpshop/');

	/*	Define medias directory for our plugin	*/
	DEFINE('WPSHOP_JS_DIR', WPSHOP_DIR . '/js/');
	DEFINE('WPSHOP_JS_URL', WPSHOP_URL . '/js/');

	/*	Define medias directory for our plugin	*/
	DEFINE('WPSHOP_CSS_DIR', WPSHOP_DIR . '/css/');
	DEFINE('WPSHOP_CSS_URL', WPSHOP_URL . '/css/');

	DEFINE('WPSHOP_AJAX_FILE_URL', WPSHOP_INCLUDES_URL . 'ajax.php');
}


/**
*	Define element for new type creation
*/
{
	DEFINE('WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT', 'wpshop_product');
	DEFINE('WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES', 'wpshop_product_category');

	DEFINE('WPSHOP_UNCATEGORIZED_PRODUCT_SLUG', 'no-category');
}


/**
*	Define database table names
*/
{
	DEFINE('WPSHOP_DBT_ENTITIES', $wpdb->prefix . 'wpshop__entity');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_SET', $wpdb->prefix . 'wpshop__attribute_set');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_GROUP', $wpdb->prefix . 'wpshop__attribute_set_section');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_DETAILS', $wpdb->prefix . 'wpshop__attribute_set_section_details');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_UNIT', $wpdb->prefix . 'wpshop__attributes_unit');
	DEFINE('WPSHOP_DBT_ATTRIBUTE', $wpdb->prefix . 'wpshop__attribute');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX', $wpdb->prefix . 'wpshop__attribute_value_');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'varchar');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'datetime');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'decimal');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'integer');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'text');
}


/**
*	Define the different url for the plugin
*/
{
	DEFINE('WPSHOP_URL_SLUG_DASHBOARD', 'wpshop_dashboard');

	DEFINE('WPSHOP_URL_SLUG_ATTRIBUTE_LISTING', 'wpshop_attribute');
	DEFINE('WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING', 'wpshop_attribute_group');

	DEFINE('WPSHOP_URL_SLUG_OPTION', 'wpshop_option');
}


/**
*	Define the different pictures for the plugin
*/
{
	DEFINE('WPSHOP_AUTHORIZED_PICS_EXTENSIONS', 'gif|jp(e)*g|png');
	DEFINE('WPSHOP_LOADING_ICON', WPSHOP_MEDIAS_URL . 'icones/loading.gif');
	DEFINE('WPSHOP_ERROR_ICON', WPSHOP_MEDIAS_URL . 'icones/informations/error_s.png');
	DEFINE('WPSHOP_SUCCES_ICON', WPSHOP_MEDIAS_URL . 'icones/informations/success_s.png');
	DEFINE('WPSHOP_DEFAULT_PRODUCT_PICTURE', WPSHOP_MEDIAS_IMAGES_URL . 'product_default.png');
	DEFINE('WPSHOP_DEFAULT_CATEGORY_PICTURE', WPSHOP_MEDIAS_IMAGES_URL . 'category_default.png');
}

/**
*	Define various congiguration vars
*/
{
	DEFINE('WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE', '3');
}

/*	Start form field display config	*/
{/*	Get the list of possible posts status	*/
	$posts_status = array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash');
}
{/*	General configuration	*/
	$comboxOptionToHide = array('deleted');
}
{/*	Attributes form	*/
// 'is_required', 
	$attribute_displayed_field = array('id', 'status', 'entity_id', 'is_visible_in_front', 'data_type', 'frontend_label', 'default_value', 'is_requiring_unit');
}
{/*	General form	*/
	$attribute_hidden_field = array('position');
}
/*	End form field display config		*/