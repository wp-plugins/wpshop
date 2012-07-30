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

/*	Define the shop type	*/
DEFINE('WPSHOP_DEFAULT_SHOP_TYPE', 'presentation');
$wpshop_shop_type = get_option('wpshop_shop_type', WPSHOP_DEFAULT_SHOP_TYPE);
DEFINE('WPSHOP_DEFINED_SHOP_TYPE', $wpshop_shop_type);

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
	DEFINE('WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE', 'wpshop_shop_message');
	DEFINE('WPSHOP_NEWTYPE_IDENTIFIER_GROUP', 'wpshop_shop_group');
	DEFINE('WPSHOP_NEWTYPE_IDENTIFIER_ADDONS', 'wpshop_shop_addons');
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
	DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . 'options');

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
	DEFINE('WPSHOP_LOADING_ICON', WPSHOP_TEMPLATES_URL . 'wpshop/medias/loading.gif');
	DEFINE('WPSHOP_ERROR_ICON', WPSHOP_MEDIAS_URL . 'icones/informations/error_s.png');
	DEFINE('WPSHOP_SUCCES_ICON', WPSHOP_MEDIAS_URL . 'icones/informations/success_s.png');
	DEFINE('WPSHOP_DEFAULT_PRODUCT_PICTURE', WPSHOP_MEDIAS_IMAGES_URL . 'no_picture.png');
	DEFINE('WPSHOP_DEFAULT_CATEGORY_PICTURE', WPSHOP_MEDIAS_IMAGES_URL . 'no_picture.png');
	DEFINE('WPSHOP_PRODUCT_NOT_EXIST', WPSHOP_MEDIAS_IMAGES_URL . 'no_picture.gif');
}

{/*	Define various configuration vars	*/
	/*	FRONTEND DISPLAY	*/
	$wpshop_display_option = get_option('wpshop_display_option');
	DEFINE('WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE', (isset($wpshop_display_option['wpshop_display_grid_element_number']) && ($wpshop_display_option['wpshop_display_grid_element_number'] >= 3) ? $wpshop_display_option['wpshop_display_grid_element_number'] : 3));
	DEFINE('WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE', 3);
	DEFINE('WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE', 6);

	DEFINE('WPSHOP_ELEMENT_NB_PER_PAGE', !empty($wpshop_display_option['wpshop_display_element_per_page']) ? $wpshop_display_option['wpshop_display_element_per_page'] : 20);
	DEFINE('WPSHOP_DISPLAY_GALLERY_ELEMENT_NUMBER_PER_LINE', 3);
	DEFINE('WPSHOP_DISPLAY_LIST_TYPE', $wpshop_display_option['wpshop_display_list_type']);

	/*	ADMIN DISPLAY	*/
	$attribute_page_layout_types=array('tab' => __('Tabs', 'wpshop'), 'separated_bloc' => __('Separated bloc', 'wpshop'));
	$wpshop_admin_display_option = get_option('wpshop_admin_display_option', array());
	DEFINE('WPSHOP_ATTRIBUTE_SET_EDITION_PAGE_LAYOUT', (!empty($wpshop_admin_display_option['wpshop_admin_attr_set_layout'])?$wpshop_admin_display_option['wpshop_admin_attr_set_layout']:'separated_bloc'));
	DEFINE('WPSHOP_ATTRIBUTE_EDITION_PAGE_LAYOUT', (!empty($wpshop_admin_display_option['wpshop_admin_attr_layout'])?$wpshop_admin_display_option['wpshop_admin_attr_layout']:'tab'));
	$product_page_layout_types=array('movable-tab' => __('Separated box in product page', 'wpshop'), 'fixed-tab' => sprintf(__('A tab in product data box "%s"', 'wpshop'), __('Product data', 'wpshop')), 'each-box' => sprintf(__('In each attribute group section "%s"', 'wpshop'), __('Product data', 'wpshop')));
	DEFINE('WPSHOP_PRODUCT_SHORTCODE_DISPLAY_TYPE', (!empty($wpshop_admin_display_option['wpshop_admin_product_shortcode_display'])?$wpshop_admin_display_option['wpshop_admin_product_shortcode_display']:'each-box'));
}

{/*	Define the default email messages	*/
	DEFINE('WPSHOP_SIGNUP_MESSAGE_OBJECT', __('Account creation confirmation','wpshop'));
	DEFINE('WPSHOP_SIGNUP_MESSAGE', __('Hello [customer_first_name] [customer_last_name], this email confirms that your account has just been created. Thank you for your loyalty. Have a good day.','wpshop'));
	
	DEFINE('WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT', __('Your order has been recorded', 'wpshop'));
	DEFINE('WPSHOP_ORDER_CONFIRMATION_MESSAGE', __('Hello [customer_first_name] [customer_last_name], this email confirms that your order has been recorded (order date : [order_date]). Thank you for your loyalty. Have a good day.', 'wpshop'));
	DEFINE('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', __('Order payment confirmation (Paypal id [paypal_order_key])', 'wpshop'));
	DEFINE('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', __('Hello [customer_first_name] [customer_last_name], this email confirms that your payment about your recent order on our website has been completed (order date : [order_date]). Thank you for your loyalty. Have a good day.', 'wpshop'));
	
	DEFINE('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', __('Your payment has been received', 'wpshop'));
	DEFINE('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', __('Hello [customer_first_name] [customer_last_name], this email confirms that your payment regarding your order ([order_key]) has just been received (order date : [order_date]). Thank you for your loyalty. Have a good day.', 'wpshop'));
	
	DEFINE('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT', __('Your order has been shipped', 'wpshop'));
	DEFINE('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', __('Hello [customer_first_name] [customer_last_name], this email confirms that your order ([order_key]) has just been shipped (order date : [order_date], tracking number : [order_trackingNumber]). Thank you for your loyalty. Have a good day.', 'wpshop'));
	
	DEFINE('WPSHOP_ORDER_UPDATE_MESSAGE_OBJECT', __('Your order has been updated', 'wpshop'));
	DEFINE('WPSHOP_ORDER_UPDATE_MESSAGE', __('Hello [customer_first_name] [customer_last_name], your order ([order_key]) has just been updated. Please login to your account to view details. Thank you for your loyalty. Have a good day.', 'wpshop'));
	
	DEFINE('WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE_OBJECT', __('Your order has been updated', 'wpshop'));
	DEFINE('WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE', __('Hello [customer_first_name] [customer_last_name], your order ([order_key]) has just been updated. A comment has been added:<br /><br />"[message]".<br /><br /> Thank you for your loyalty. Have a good day.', 'wpshop'));
}

{/*	Define debug vars	*/
	$extra_options = get_option('wpshop_extra_options', array());

	/*	ALLOWED IPS	*/
	$default_ip = array('127.0.0.1');
	DEFINE('WPSHOP_DEBUG_MODE_ALLOWED_IP', (!empty($extra_options['WPSHOP_DEBUG_MODE_ALLOWED_IP'])?serialize(array_merge($default_ip, array($extra_options['WPSHOP_DEBUG_MODE_ALLOWED_IP']))):serialize($default_ip)));
	/*	DEBUG MODE	*/
	$debug_mode = false;
	if ( !empty($extra_options['WPSHOP_DEBUG_MODE']) && ($extra_options['WPSHOP_DEBUG_MODE'] == 'true') )
		$debug_mode = true;
	DEFINE('WPSHOP_DEBUG_MODE', $debug_mode);
	/*	DATA DELETE	*/
	$delete_data = false;
	if ( !empty($extra_options['WPSHOP_DEBUG_MODE_ALLOW_DATA_DELETION']) && ($extra_options['WPSHOP_DEBUG_MODE_ALLOW_DATA_DELETION'] == 'true') )
		$delete_data = true;
	DEFINE('WPSHOP_DEBUG_MODE_ALLOW_DATA_DELETION', $delete_data);

	/*	TOOLS MENU	*/
	$tools_menu_display = false;
	if ( !empty($extra_options['WPSHOP_DISPLAY_TOOLS_MENU']) && ($extra_options['WPSHOP_DISPLAY_TOOLS_MENU'] == 'true') )
		$tools_menu_display = true;
	DEFINE('WPSHOP_DISPLAY_TOOLS_MENU', $tools_menu_display);

	/*	ATT VALUE PER USER	*/
	$attr_value_per_user = false;
	if ( !empty($extra_options['WPSHOP_ATTRIBUTE_VALUE_PER_USER']) && ($extra_options['WPSHOP_ATTRIBUTE_VALUE_PER_USER'] == 'true') )
		$attr_value_per_user = true;
	DEFINE('WPSHOP_ATTRIBUTE_VALUE_PER_USER', $attr_value_per_user);
	/*	MULTIPLE VALUE PER USER	*/
	$attr_value_per_user_multiple = false;
	if ( !empty($extra_options['WPSHOP_MULTIPLE_ATTRIBUTE_VALUE_PER_USER']) && ($extra_options['WPSHOP_MULTIPLE_ATTRIBUTE_VALUE_PER_USER'] == 'true') )
		$attr_value_per_user_multiple = true;
	DEFINE('WPSHOP_MULTIPLE_ATTRIBUTE_VALUE_PER_USER', $attr_value_per_user_multiple);
	DEFINE('WPSHOP_DISPLAY_VALUE_FOR_ATTRIBUTE_SELECT', false);
}

{/*	Define element prefix	*/
	DEFINE('WPSHOP_PRODUCT_REFERENCE_PREFIX', 'PDCT');
	DEFINE('WPSHOP_PRODUCT_REFERENCE_PREFIX_NB_FILL', 5);
	DEFINE('WPSHOP_BILLING_REFERENCE_PREFIX', 'FA');
	DEFINE('WPSHOP_ORDER_REFERENCE_PREFIX', 'OR');
	DEFINE('WPSHOP_PREORDER_REFERENCE_PREFIX', 'D');
}

{/*	Define the different pages to create for basic usage	*/
	$default_pages = array();
	$product_options = get_option('wpshop_catalog_product_option');
	$default_pages['presentation'][] = array('page_code' => 'wpshop_product_page_id', 'post_title' => __('Shop', 'wpshop'), 'post_name' => $product_options['wpshop_catalog_product_slug'], 'post_content' => '[wpshop_products]');
	$default_pages['sale'][] = array('page_code' => 'wpshop_cart_page_id', 'post_title' => __('Cart', 'wpshop'), 'post_name' => 'cart', 'post_content' => '[wpshop_cart]');
	$default_pages['sale'][] = array('page_code' => 'wpshop_checkout_page_id', 'post_title' => __('Checkout', 'wpshop'), 'post_name' => 'checkout', 'post_content' => '[wpshop_checkout]');
	$default_pages['sale'][] = array('page_code' => 'wpshop_myaccount_page_id', 'post_title' => __('My account', 'wpshop'), 'post_name' => 'myaccount', 'post_content' => '[wpshop_myaccount]');
	$default_pages['sale'][] = array('page_code' => 'wpshop_signup_page_id', 'post_title' => __('Signup', 'wpshop'), 'post_name' => 'signup', 'post_content' => '[wpshop_signup]');
	$default_pages['sale'][] = array('page_code' => 'wpshop_payment_return_page_id', 'post_title' => __('Payment return', 'wpshop'), 'post_name' => 'return', 'post_content' => '[wpshop_payment_result]');
	// $default_pages[] = array('page_code' => 'wpshop_advanced_search_page_id', 'post_title' => __('Advanced Search', 'wpshop'), 'post_name' => 'advanced-search', 'post_content' => '[wpshop_advanced_search]');

	DEFINE('WPSHOP_DEFAULT_PAGES', serialize($default_pages));
}

{/*	Define the different vars used for price calculation	*/
	$wpshop_shop_price_piloting = get_option('wpshop_shop_price_piloting');
	DEFINE('WPSHOP_PRODUCT_PRICE_PILOT', ( !empty($wpshop_shop_price_piloting) ? $wpshop_shop_price_piloting : 'TTC'));

	DEFINE('WPSHOP_COST_OF_POSTAGE', 'cost_of_postage');
	DEFINE('WPSHOP_PRODUCT_PRICE_HT', 'price_ht');
	DEFINE('WPSHOP_PRODUCT_PRICE_TAX', 'tx_tva');
	DEFINE('WPSHOP_PRODUCT_PRICE_TTC', 'product_price');
	DEFINE('WPSHOP_PRODUCT_PRICE_TAX_AMOUNT', 'tva');
	DEFINE('WPSHOP_PRODUCT_WEIGHT', 'product_weight');

	DEFINE('WPSHOP_ATTRIBUTE_PRICES', serialize(array(WPSHOP_PRODUCT_PRICE_HT, WPSHOP_PRODUCT_PRICE_TAX, WPSHOP_PRODUCT_PRICE_TTC, WPSHOP_PRODUCT_PRICE_TAX_AMOUNT, WPSHOP_COST_OF_POSTAGE)));
	DEFINE('WPSHOP_ATTRIBUTE_WEIGHT', serialize(array(WPSHOP_PRODUCT_WEIGHT)));
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
	/*DEFINE('WPSHOP_SHOP_SHIPPING_FEES', '{'."
".'destination:"FR",'."
".'rules: "weight",'."
".'fees:"100:5.60, 200:6.95, 2.0:7.95, 3.0:8.95, 5.0:10.95, 7.0:12.95, 10.0:15.95, 15.0:18.20, 30.0:24.90"'."
".'},'."
".'{'."
".'destination:"ES",'."
".'fees:"10.0:15.95, 15.0:18.20, 30.0:24.90"'."
".'}');*/
	$shipping_fees_array = array(
		'active' => false,
		'fees' => array(
			'FR' => array(
				'destination' => 'FR',
				'rule' => 'weight',
				'fees' => array(100 => 5.6, 250 => 7.2, 500 => 9)
			),
			'OTHERS' => array(
				'destination' => 'OTHERS',
				'rule' => 'weight',
				'fees' => array(100 => 6.7, 250 => 7.9, 500 => 10.2)
			)
		)
	);
	DEFINE('WPSHOP_SHOP_CUSTOM_SHIPPING', serialize($shipping_fees_array));
}
{/*	Define payment method params	*/
	DEFINE('WPSHOP_PAYMENT_METHOD_CIC', (!empty($extra_options['WPSHOP_PAYMENT_METHOD_CIC'])?$extra_options['WPSHOP_PAYMENT_METHOD_CIC']:false));
	$wpshop_paymentMethod = get_option('wpshop_paymentMethod');
	if(WPSHOP_PAYMENT_METHOD_CIC || !empty($wpshop_paymentMethod['cic'])) {
		$cmcic_params = get_option('wpshop_cmcic_params', array());
		if(!empty($cmcic_params)){
			DEFINE("CMCIC_CLE", $cmcic_params['cle']);
			DEFINE("CMCIC_TPE", $cmcic_params['tpe']);
			DEFINE("CMCIC_VERSION", $cmcic_params['version']);
			DEFINE("CMCIC_SERVEUR", $cmcic_params['serveur']);
			DEFINE("CMCIC_CODESOCIETE", $cmcic_params['codesociete']);
			DEFINE("CMCIC_URLOK", '');
			DEFINE("CMCIC_URLKO", '');
		}
	}
}

{/*	Define addons modules */
	DEFINE('WPSHOP_ADDONS_LIST', serialize(array('WPSHOP_ADDONS_QUOTATION' => __('Quotation','wpshop'))));
	DEFINE('WPSHOP_ADDONS_QUOTATION', (!empty($extra_options['WPSHOP_ADDONS_QUOTATION'])?$extra_options['WPSHOP_ADDONS_QUOTATION']:false));
	DEFINE('WPSHOP_ADDONS_QUOTATION_CODE', 'QUOTATION_CODE');
}

/* Civility	*/
$civility = array(1=>__('Mr.','wpshop'),__('Mrs.','wpshop'),__('Miss','wpshop'));
/* Status	*/
$order_status = array(
	'' => __('Awaiting treatment', 'wpshop'),
	'awaiting_payment' => __('Awaiting payment', 'wpshop'),
	'completed' => __('Paid', 'wpshop'),
	'shipped' => __('Shipped', 'wpshop'),
	'denied' => __('Denied', 'wpshop')
);
/*	Register post type support	*/
$register_post_type_support = array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats');
$mandatory_register_post_type_support = array('title', 'editor', 'thumbnail', 'excerpt');
DEFINE('WPSHOP_PRODUCT_HIDDEN_METABOX', serialize(array('formatdiv', 'pageparentdiv', 'postexcerpt', 'trackbacksdiv', 'postcustom', 'postcustom', 'commentstatusdiv', 'commentsdiv', 'slugdiv', 'authordiv', 'revisionsdiv')));
/* Shop type	*/
DEFINE('WPSHOP_SHOP_TYPES', serialize(array('presentation', 'sale')));

/*	Define the types existing into the current wordpress installation	*/
$default_to_exclude = array('attachment','revision','nav_menu_item');
DEFINE('WPSHOP_INTERNAL_TYPES_TO_EXCLUDE', (!empty($extra_options['WPSHOP_INTERNAL_TYPES_TO_EXCLUDE'])?serialize(array_merge(array($extra_options['WPSHOP_INTERNAL_TYPES_TO_EXCLUDE']),$default_to_exclude)):serialize($default_to_exclude)));
$wp_types=get_post_types();
$to_exclude=unserialize(WPSHOP_INTERNAL_TYPES_TO_EXCLUDE);
if(!empty($to_exclude)):
	foreach($to_exclude as $excluded_type):
		if(isset($wp_types[$excluded_type]))unset($wp_types[$excluded_type]);
	endforeach;
endif;
DEFINE('WPSHOP_INTERNAL_TYPES', serialize(array_merge($wp_types, array('users'))));

/*	Start form field display config	*/
{/*	Get the list of possible posts status	*/
	$posts_status = array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash');
}
{/*	General configuration	*/
	$comboxOptionToHide = array('deleted');
}
{/*	Attributes form	*/ 
	$attribute_displayed_field = array('id', 'status', 'entity_id', 'is_visible_in_front', /* 'data_type',  */'backend_input', 'frontend_label', 'default_value', 'is_requiring_unit', '_unit_group_id', '_default_unit', 'is_historisable','is_intrinsic','code', 'is_used_for_sort_by', 'is_visible_in_advanced_search'/*, 'is_user_defined' , 'is_recordable_in_cart_meta' */);
	$attribute_options_group = array(__('Attribute unit', 'wpshop')=>array('is_requiring_unit','_unit_group_id','_default_unit'), __('Frontend option', 'wpshop')=>array('is_visible_in_front','is_used_for_sort_by','is_visible_in_advanced_search'));
}
{/*	General form	*/
	$attribute_hidden_field = array('position');
}
{/*		*/
	$customer_adress_information_field = array('civility' => __('Civility', 'wpshop'), 'first_name' => __('First name', 'wpshop'), 'last_name' => __('Last name', 'wpshop'), 'email' => __('Email adress', 'wpshop'), 'phone' => __('Phone number', 'wpshop'), 'company' => __('Company', 'wpshop'), 'adress' => __('Adresse', 'wpshop'), 'postcode' => __('Postcode', 'wpshop'), 'city' => __('City', 'wpshop'), 'country' => __('Country', 'wpshop'));
}
/*	End form field display config		*/