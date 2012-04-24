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

{/*	Define the different path for the plugin	*/
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
		DEFINE('WPSHOP_MEDIAS_ICON_DIR', WPSHOP_MEDIAS_DIR . 'icones/');
		DEFINE('WPSHOP_MEDIAS_ICON_URL', WPSHOP_MEDIAS_URL . 'icones/');
		DEFINE('WPSHOP_MEDIAS_IMAGES_DIR', WPSHOP_MEDIAS_DIR . 'images/');
		DEFINE('WPSHOP_MEDIAS_IMAGES_URL', WPSHOP_MEDIAS_URL . 'images/');

	/*	Define upload dir	*/
	$wp_upload_dir = wp_upload_dir();
	DEFINE('WPSHOP_UPLOAD_DIR', $wp_upload_dir['basedir'] . '/'.WPSHOP_PLUGIN_DIR.'/');
	DEFINE('WPSHOP_UPLOAD_URL', $wp_upload_dir['baseurl'] . '/'.WPSHOP_PLUGIN_DIR.'/');

	/*	Define medias directory for our plugin	*/
	DEFINE('WPSHOP_JS_DIR', WPSHOP_DIR . '/js/');
	DEFINE('WPSHOP_JS_URL', WPSHOP_URL . '/js/');

	/*	Define medias directory for our plugin	*/
	DEFINE('WPSHOP_CSS_DIR', WPSHOP_DIR . '/css/');
	DEFINE('WPSHOP_CSS_URL', WPSHOP_URL . '/css/');

	DEFINE('WPSHOP_AJAX_FILE_URL', WPSHOP_INCLUDES_URL . 'ajax.php');
}

{/*	Define element for new type creation	*/
	DEFINE('WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT', 'wpshop_product');
	DEFINE('WPSHOP_IDENTIFIER_PRODUCT', 'P');
	DEFINE('WPSHOP_NEWTYPE_IDENTIFIER_ORDER', 'wpshop_shop_order');
	DEFINE('WPSHOP_NEWTYPE_IDENTIFIER_COUPON', 'wpshop_shop_coupon');
	DEFINE('WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY', '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute_set_id');
	DEFINE('WPSHOP_PRODUCT_ATTRIBUTE_META_KEY', '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_metadata');
	DEFINE('WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES', 'wpshop_product_category');
	DEFINE('WPSHOP_PRODUCT_RELATED_PRODUCTS', '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_related_products');

	DEFINE('WPSHOP_IDENTIFIER_CUSTOMER', 'U');
	$cat_options = get_option('wpshop_catalog_categories_option');
	DEFINE('WPSHOP_UNCATEGORIZED_PRODUCT_SLUG', !empty($cat_options['wpshop_catalog_no_category_slug']) ? $cat_options['wpshop_catalog_no_category_slug'] : 'no-category');
	DEFINE('WPSHOP_CATALOG_PRODUCT_SLUG', 'catalog');
	DEFINE('WPSHOP_CATALOG_CATEGORIES_SLUG', 'catalog');
	DEFINE('WPSHOP_CATALOG_PRODUCT_NO_CATEGORY', 'no-categories');
}

{/*	Define database table names	*/
	DEFINE('WPSHOP_DBT_ENTITIES', $wpdb->prefix . 'wpshop__entity');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_SET', $wpdb->prefix . 'wpshop__attribute_set');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_GROUP', $wpdb->prefix . 'wpshop__attribute_set_section');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_DETAILS', $wpdb->prefix . 'wpshop__attribute_set_section_details');

	DEFINE('WPSHOP_DBT_ATTRIBUTE_UNIT', $wpdb->prefix . 'wpshop__attributes_unit');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP', $wpdb->prefix . 'wpshop__attributes_unit_groups');

	DEFINE('WPSHOP_DBT_ATTRIBUTE', $wpdb->prefix . 'wpshop__attribute');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX', $wpdb->prefix . 'wpshop__attribute_value_');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'varchar');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'datetime');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'decimal');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'integer');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'text');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . '_histo');
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'options');

	DEFINE('WPSHOP_DBT_HISTORIC', $wpdb->prefix . 'wpshop__historique');
	DEFINE('WPSHOP_DBT_MESSAGES', $wpdb->prefix . 'wpshop__message');

	/*	Delete table at database version 12 for new cart management with session and usermeta database	*/
	DEFINE('WPSHOP_DBT_CART', $wpdb->prefix . 'wpshop__cart');
	DEFINE('WPSHOP_DBT_CART_CONTENTS', $wpdb->prefix . 'wpshop__cart_contents');
}

{/*	Define the different url for the plugin	*/
	DEFINE('WPSHOP_URL_SLUG_DASHBOARD', 'wpshop_dashboard');
	DEFINE('WPSHOP_URL_SLUG_ATTRIBUTE_LISTING', 'wpshop_attribute');
	DEFINE('WPSHOP_URL_SLUG_SHORTCODES', 'wpshop_shortcodes');
	DEFINE('WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING', 'wpshop_attribute_group');
	DEFINE('WPSHOP_URL_SLUG_OPTION', 'wpshop_option');
	DEFINE('WPSHOP_URL_SLUG_MESSAGES', 'wpshop_messages');
	DEFINE('WPSHOP_URL_SLUG_TOOLS', 'wpshop_tools');
}

{/*	Define the different pictures for the plugin	*/
	DEFINE('WPSHOP_AUTHORIZED_PICS_EXTENSIONS', 'gif|jp(e)*g|png');
	DEFINE('WPSHOP_LOADING_ICON', WPSHOP_TEMPLATES_URL . 'wpshop/medias//loading.gif');
	DEFINE('WPSHOP_ERROR_ICON', WPSHOP_MEDIAS_URL . 'icones/informations/error_s.png');
	DEFINE('WPSHOP_SUCCES_ICON', WPSHOP_MEDIAS_URL . 'icones/informations/success_s.png');
	DEFINE('WPSHOP_DEFAULT_PRODUCT_PICTURE', WPSHOP_MEDIAS_IMAGES_URL . 'no_picture.png');
	DEFINE('WPSHOP_DEFAULT_CATEGORY_PICTURE', WPSHOP_MEDIAS_IMAGES_URL . 'no_picture.png');
	DEFINE('WPSHOP_PRODUCT_NOT_EXIST', WPSHOP_MEDIAS_IMAGES_URL . 'no_picture.gif');
}

{/*	Define various congiguration vars	*/
	$wpshop_display_option = get_option('wpshop_display_option');
	DEFINE('WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE', (isset($wpshop_display_option['wpshop_display_grid_element_number']) && ($wpshop_display_option['wpshop_display_grid_element_number'] >= 3) ? $wpshop_display_option['wpshop_display_grid_element_number'] : 3));
	DEFINE('WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE', 3);
	DEFINE('WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE', 6);

	DEFINE('WPSHOP_ELEMENT_NB_PER_PAGE', !empty($wpshop_display_option['wpshop_display_element_per_page']) ? $wpshop_display_option['wpshop_display_element_per_page'] : 20);
	DEFINE('WPSHOP_DISPLAY_GALLERY_ELEMENT_NUMBER_PER_LINE', 3);
	DEFINE('WPSHOP_DISPLAY_LIST_TYPE', $wpshop_display_option['wpshop_display_list_type']);
}

{/*	Define the default email messages	*/
	DEFINE('WPSHOP_SIGNUP_MESSAGE_OBJECT', __('Account creation confirmation','wpshop'));
	DEFINE('WPSHOP_SIGNUP_MESSAGE', __('Hello [customer_first_name] [customer_last_name], this email confirms that your account has just been created. Thank you for your loyalty. Have a good day.','wpshop'));
	
	DEFINE('WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT', __('Your order has been recorded', 'wpshop'));
	DEFINE('WPSHOP_ORDER_CONFIRMATION_MESSAGE', __('Hello [customer_first_name] [customer_last_name], this email confirms that your order has been recorded. Thank you for your loyalty. Have a good day.', 'wpshop'));
	DEFINE('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', __('Order payment confirmation (Paypal id [paypal_order_key])', 'wpshop'));
	DEFINE('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', __('Hello [customer_first_name] [customer_last_name], this email confirms that your payment about your recent order on our website has been completed. Thank you for your loyalty. Have a good day.', 'wpshop'));
	
	DEFINE('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', __('Your payment has been received', 'wpshop'));
	DEFINE('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', __('Hello [customer_first_name] [customer_last_name], this email confirms that your payment regarding your order ([order_key]) has just been received. Thank you for your loyalty. Have a good day.', 'wpshop'));
	
	DEFINE('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT', __('Your order has been shipped', 'wpshop'));
	DEFINE('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', __('Hello [customer_first_name] [customer_last_name], this email confirms that your order ([order_key]) has just been shipped. Thank you for your loyalty. Have a good day.', 'wpshop'));
}

{/*	Define debug vars	*/
	DEFINE('WPSHOP_DEBUG_ALLOWED_IP', serialize(array('127.0.0.1')));
	DEFINE('WPSHOP_DEBUG_MODE', false);
	DEFINE('WPSHOP_DEBUG_ALLOW_DATA_DELETION', false);
	DEFINE('WPSHOP_DISPLAY_TOOLS_MENU', false);
}

{/*	Define development vars	*/
	DEFINE('WPSHOP_PRODUCT_PRICE_PILOT', 'HT');
	DEFINE('WPSHOP_PRODUCT_REFERENCE_PREFIX', 'PDCT');
	DEFINE('WPSHOP_PRODUCT_REFERENCE_PREFIX_NB_FILL', 5);
	DEFINE('WPSHOP_BILLING_REFERENCE_PREFIX', 'FA');
	DEFINE('WPSHOP_ORDER_REFERENCE_PREFIX', 'OR');
	DEFINE('WPSHOP_PREORDER_REFERENCE_PREFIX', 'D');
}

{/*	Define the different vars used for price calculation	*/
	DEFINE('WPSHOP_COST_OF_POSTAGE', 'cost_of_postage');

	DEFINE('WPSHOP_PRODUCT_PRICE_HT', 'price_ht');
	DEFINE('WPSHOP_PRODUCT_PRICE_TAX', 'tx_tva');
	DEFINE('WPSHOP_PRODUCT_PRICE_TTC', 'product_price');
	DEFINE('WPSHOP_PRODUCT_PRICE_TAX_AMOUNT', 'tva');

	DEFINE('WPSHOP_ATTRIBUTE_PRICES', serialize(array(WPSHOP_PRODUCT_PRICE_HT, WPSHOP_PRODUCT_PRICE_TAX, WPSHOP_PRODUCT_PRICE_TTC, WPSHOP_PRODUCT_PRICE_TAX_AMOUNT, WPSHOP_COST_OF_POSTAGE)));
}
{/*	Define the different attribute that user won't be able to delete from interface	*/

	DEFINE('WPSHOP_ATTRIBUTE_UNDELETABLE', serialize(array_merge(array(), unserialize(WPSHOP_ATTRIBUTE_PRICES))));
}
{/*	Define the default currency	*/
	DEFINE('WPSHOP_SHOP_DEFAULT_CURRENCY', 'EUR');
	DEFINE('WPSHOP_SHOP_CURRENCIES', serialize(array(
		'EUR' => '&euro;',
		'USD' => '$'
	)));
}
{/*	Define the shipping default rules	*/
	DEFINE('WPSHOP_SHOP_SHIPPING_RULES', serialize(array(
		'min_max' => array('min'=>5,'max'=>30),
		'free_from' => 100
	)));
}
{/*	Define payment method params	*/
	DEFINE('WPSHOP_PAYMENT_METHOD_CIC', false);
	if(WPSHOP_PAYMENT_METHOD_CIC) {
		$cmcic_params = get_option('wpshop_cmcic_params', array());
		if(!empty($cmcic_params)){
			DEFINE("CMCIC_CLE", $cmcic_params['cle']);
			DEFINE("CMCIC_TPE", $cmcic_params['tpe']);
			DEFINE("CMCIC_VERSION", $cmcic_params['version']);
			DEFINE("CMCIC_SERVEUR", $cmcic_params['serveur']);
			DEFINE("CMCIC_CODESOCIETE", $cmcic_params['codesociete']);
			DEFINE("CMCIC_URLOK", $cmcic_params['urlok']);
			DEFINE("CMCIC_URLKO", $cmcic_params['urlko']);
		}
	}
}

/* Civility	*/
$civility = array(1=>__('Mr.','wpshop'),__('Mrs.','wpshop'),__('Miss','wpshop'));
/* Status	*/
$order_status = array(
	'awaiting_payment' => __('Awaiting payment', 'wpshop'),
	'completed' => __('Paid', 'wpshop'),
	'shipped' => __('Shipped', 'wpshop')
);
/*	Register post type support	*/
$register_post_type_support = array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats');
$mandatory_register_post_type_support = array('title', 'editor', 'thumbnail', 'excerpt');

/*	Start form field display config	*/
{/*	Get the list of possible posts status	*/
	$posts_status = array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash');
}
{/*	General configuration	*/
	$comboxOptionToHide = array('deleted');
}
{/*	Attributes form	*/ 
	$attribute_displayed_field = array('id', 'status', 'entity_id', 'is_visible_in_front', 'data_type', 'frontend_input', 'frontend_label', 'default_value', 'is_requiring_unit', '_unit_group_id', '_default_unit', 'is_historisable','is_intrinsic','code', 'is_used_for_sort_by');
}
{/*	General form	*/
	$attribute_hidden_field = array('position');
}
/*	End form field display config		*/