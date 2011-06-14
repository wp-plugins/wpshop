<?php
/**
* Database table config file
* 
* Define the different names for the database element. Allows to avoid the name hard coded in different script
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage config
*/

/**
* Get the global wordpress prefix for database table
*/
global $wpdb;

/**
* Define the main plugin prefix
*/
DEFINE('WPSHOP_DB_PREFIX', $wpdb->prefix . "wpshop__");

/*	Start catalog database table name definition		*/

DEFINE('WPSHOP_DBT_PRODUCT', WPSHOP_DB_PREFIX . "product");
DEFINE('WPSHOP_DBT_PRODUCT_RELATION', WPSHOP_DB_PREFIX . "product_relation");

DEFINE('WPSHOP_DBT_CATEGORY_PRODUCT_DETAILS', WPSHOP_DB_PREFIX . "category_product_details");

DEFINE('WPSHOP_DBT_CATEGORY', WPSHOP_DB_PREFIX . "category");
DEFINE('WPSHOP_DBT_CATEGORY_RELATION', WPSHOP_DB_PREFIX . "category_relation");

DEFINE('WPSHOP_DBT_LANGUAGE', WPSHOP_DB_PREFIX . "language");

/*	End catalog database table name definition		*/



/*	Start eav database table name definition		*/

DEFINE('wpshop_DB_EAV_PREFIX', $wpdb->prefix . "eo_eav__");

DEFINE('WPSHOP_DBT_ENTITIES', wpshop_DB_EAV_PREFIX . "entities");

DEFINE('WPSHOP_DBT_ATTRIBUTE', wpshop_DB_EAV_PREFIX . "attribute");
DEFINE('WPSHOP_DBT_ATTRIBUTE_LABEL', wpshop_DB_EAV_PREFIX . "attribute_label");
DEFINE('WPSHOP_DBT_ATTRIBUTE_SET', wpshop_DB_EAV_PREFIX . "attribute_group");
DEFINE('WPSHOP_DBT_ATTRIBUTE_GROUP', wpshop_DB_EAV_PREFIX . "attribute_group_section");
DEFINE('WPSHOP_DBT_ATTRIBUTE_OPTION', wpshop_DB_EAV_PREFIX . "attribute_option");
DEFINE('WPSHOP_DBT_ATTRIBUTE_OPTION_VALUE', wpshop_DB_EAV_PREFIX . "attribute_option_value");
DEFINE('WPSHOP_DBT_ATTRIBUTE_DETAILS', wpshop_DB_EAV_PREFIX . "entity_set_attribute_details");

DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX', wpshop_DB_EAV_PREFIX . "attribute_value_");
DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . "varchar");
DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . "datetime");
DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . "decimal");
DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . "integer");
DEFINE('WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT', WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . "text");

/*	End eav database table name definition		*/



/*	Start permissions database table name definition		*/

DEFINE('WPSHOP_DBT_PERMISSIONS', WPSHOP_DB_PREFIX . "permission");

/*	End permissions database table name definition		*/


/*	Start document management database table name definition		*/

DEFINE('WPSHOP_DBT_DOCUMENT', WPSHOP_DB_PREFIX . "document");
DEFINE('WPSHOP_DBT_DOCUMENT_LINK_ELEMENT', WPSHOP_DB_PREFIX . "document_relation");

/*	End document management database table name definition		*/