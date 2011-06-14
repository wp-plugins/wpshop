<?php
/**
* Database management
* 
* Define the different method to access to database, for database creation and update for the different version
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to access to database, for database creation and update for the different version
* @package wpshop
* @subpackage librairies
*/
class wpshop_database
{

	/**
	* Define the different database element to create for each plugin's version
	*/
	function wpshop_db_creation()
	{
		global $wpdb;
		global $db_options;

		/*	Check the current version	*/
		$currentVersion = $db_options->get_db_version();

		if($currentVersion == 0)
		{/*	Create the different table and add the data	. Check whether the table exist or not	*/

		/*	PRODUCT	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_PRODUCT . "'") != WPSHOP_DBT_PRODUCT)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_PRODUCT . " (
						id int(10) unsigned NOT NULL auto_increment,
						status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
						creation_date datetime ,
						last_update_date datetime ,
						attribute_set_id int(10) unsigned NOT NULL,
						reference varchar(128) collate utf8_unicode_ci NOT NULL,
						PRIMARY KEY  (id),
						KEY status (status),
						KEY attribute_set_id (attribute_set_id),
						UNIQUE reference (reference)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Products listing'; ";
				$wpdb->query($query);
			}
		/*	PRODUCTS RELATION	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_PRODUCT_RELATION . "'") != WPSHOP_DBT_PRODUCT_RELATION)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_PRODUCT_RELATION . " (
						parent_product int(10) unsigned NOT NULL,
						children_product int(10) unsigned NOT NULL,
						quantity int(10) unsigned NOT NULL,
						PRIMARY KEY (parent_product, children_product),
						KEY quantity (quantity)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Product composition listing'; ";
				$wpdb->query($query);
			}

		/*	CATEGORY	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_CATEGORY . "'") != WPSHOP_DBT_CATEGORY)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_CATEGORY . " (
						id int(10) unsigned NOT NULL auto_increment,
						status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
						creation_date datetime,
						last_update_date datetime,
						attribute_set_id int(10) unsigned NOT NULL,
						PRIMARY KEY  (id),
						KEY status (status),
						KEY attribute_set_id (attribute_set_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Categories listing'; ";
				$wpdb->query($query);
			}
		/*	CATEGORIES RELATION	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_CATEGORY_RELATION . "'") != WPSHOP_DBT_CATEGORY_RELATION)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_CATEGORY_RELATION . " (
						children_category int(10) unsigned NOT NULL,
						position int(10) unsigned NOT NULL,
						PRIMARY KEY (parent_category, children_category),
						KEY position (position)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Categories composition listing'; ";
				// $wpdb->query($query);
			}

		/*	CATEGORY PRODUCT DETAILS	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_CATEGORY_PRODUCT_DETAILS . "'") != WPSHOP_DBT_CATEGORY_PRODUCT_DETAILS)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_CATEGORY_PRODUCT_DETAILS . " (
						id int(10) unsigned NOT NULL auto_increment,
						id_category int(10) unsigned NOT NULL,
						id_product int(10) unsigned NOT NULL,
						status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
						position int(10) unsigned NOT NULL,
						attribution_date datetime NOT NULL,
						attribution_user_id bigint(20) NOT NULL,
						unassigning_date datetime NOT NULL,
						unassigning_user_id bigint(20) NOT NULL,
						PRIMARY KEY (id),
						KEY position (position)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Products in categories listing'; ";
				$wpdb->query($query);
			}

		/*	LANGUAGE	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_LANGUAGE . "'") != WPSHOP_DBT_LANGUAGE)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_LANGUAGE . " (
						id int(10) unsigned NOT NULL auto_increment,
						status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
						creation_date datetime ,
						last_update_date datetime ,
						is_default enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no' COMMENT 'Define if the language is the default language into the store',
						code char(6) collate utf8_unicode_ci NOT NULL,
						name varchar(64) collate utf8_unicode_ci NOT NULL,
						icon_path varchar(255) collate utf8_unicode_ci NOT NULL,
						PRIMARY KEY (id),
						KEY status (status),
						KEY code (code)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Languages listing'; ";
				$wpdb->query($query);
			}

		/*	ENTITIES	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ENTITIES . "'") != WPSHOP_DBT_ENTITIES)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ENTITIES . " (
						id INT(10) unsigned NOT NULL AUTO_INCREMENT ,
						status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
						creation_date datetime ,
						last_update_date datetime ,
						code varchar(50) collate utf8_unicode_ci NOT NULL ,
						entity_table varchar(255) collate utf8_unicode_ci NOT NULL ,
						PRIMARY KEY (id),
						KEY status (status),
						UNIQUE code (code)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Entities listing'; ";
				$wpdb->query($query);
			}

		/*	ATTRIBUTES	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE . "'") != WPSHOP_DBT_ATTRIBUTE)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE . " (
						id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
						status ENUM('valid','moderated','deleted') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'valid' ,
						creation_date datetime ,
						last_update_date datetime ,
						entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						is_global ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_user_defined ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_required ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_visible_in_advanced_search ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_searchable ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_filterable ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_comparable ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_html_allowed_on_front ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_unique ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_filterable_in_search ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_used_for_sort_by ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						is_configurable ENUM('yes','no') NOT NULL DEFAULT 'no' ,
						data_type ENUM('static','datetime','decimal','int','text','varchar') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'static' ,
						backend_table VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
						frontend_input VARCHAR(50) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
						frontend_label VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
						frontend_verification VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
						code VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '' ,
						note VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
						default_value TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
						PRIMARY KEY (id) ,
						UNIQUE code (code ASC) ,
						KEY status (status),
						KEY is_global (is_global),
						KEY is_user_defined (is_user_defined),
						KEY is_required (is_required),
						KEY is_visible_in_advanced_search (is_visible_in_advanced_search),
						KEY is_searchable (is_searchable),
						KEY is_filterable (is_filterable),
						KEY is_comparable (is_comparable),
						KEY is_html_allowed_on_front (is_html_allowed_on_front),
						KEY is_unique (is_unique),
						KEY is_filterable_in_search (is_filterable_in_search),
						KEY is_used_for_sort_by (is_used_for_sort_by ),
						KEY is_configurable (is_configurable ),
						KEY data_type (data_type )
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Attributes listing'; ";
				$wpdb->query($query);
			}
		/*	ATTRIBUTES LABEL	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_LABEL . "'") != WPSHOP_DBT_ATTRIBUTE_LABEL)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_LABEL . " (
						id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
						status ENUM('valid','moderated','deleted') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'valid' ,
						attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						language_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						name VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
						PRIMARY KEY (id) ,
						UNIQUE KEY attribute_key (attribute_id, language_id) ,
						KEY status (status)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Attributes label listing'; ";
				$wpdb->query($query);
			}
		/*	ATTRIBUTES SET	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_SET . "'") != WPSHOP_DBT_ATTRIBUTE_SET)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_SET . " (
						id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
						status ENUM('valid','moderated','deleted') NULL DEFAULT 'valid' ,
						creation_date datetime ,
						last_update_date datetime ,
						position INT(10) NOT NULL DEFAULT '0' ,
						entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						name VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_swedish_ci' NOT NULL DEFAULT '' ,
						PRIMARY KEY (id) ,
						KEY position (position) ,
						KEY status (status) ,
						KEY entity_id (entity_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Attributes set listing'; ";
				$wpdb->query($query);
			}
		/*	ATTRIBUTES GROUP	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_GROUP . "'") != WPSHOP_DBT_ATTRIBUTE_GROUP)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_GROUP . " (
						id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
						status ENUM('valid','moderated','deleted') NULL DEFAULT 'valid' ,
						attribute_set_id INT UNSIGNED NOT NULL DEFAULT '0' ,
						position INT NOT NULL DEFAULT '0' ,
						creation_date datetime ,
						last_update_date datetime ,
						code VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '' ,
						name VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '' ,
						PRIMARY KEY (id) ,
						UNIQUE attribute_set_id_name_unique (attribute_set_id, name) ,
						KEY attribute_set_id_position_key (attribute_set_id, position) ,
						KEY attribute_set_id_index (attribute_set_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Attributes group listing'; ";
				$wpdb->query($query);
			}
		/*	ATTRIBUTES OPTION	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_OPTION . "'") != WPSHOP_DBT_ATTRIBUTE_OPTION)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_OPTION . " (
						id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
						status ENUM('valid','moderated','deleted') NULL DEFAULT 'valid' ,
						attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						PRIMARY KEY (id) ,
						INDEX attribute_id (attribute_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Link betwen option type attributes and its different values'; ";
				$wpdb->query($query);
			}		
		/*	ATTRIBUTES OPTION	VALUE	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_OPTION_VALUE . "'") != WPSHOP_DBT_ATTRIBUTE_OPTION_VALUE)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_OPTION_VALUE . " (
						id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
						status ENUM('valid','moderated','deleted') NULL DEFAULT 'valid' ,
						option_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						position INT(10) UNSIGNED NOT NULL DEFAULT '0',
						option_value VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '' ,
						PRIMARY KEY (id) ,
						KEY status (status),
						KEY option_id (option_id),
						KEY position (position)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Different possible values for option type attributes'; ";
				$wpdb->query($query);
			}
		/*	ATTRIBUTES OPTION	DETAILS	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_DETAILS . "'") != WPSHOP_DBT_ATTRIBUTE_DETAILS)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " (
						id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
						entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						attribute_set_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						attribute_group_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						position INT(10) NOT NULL DEFAULT '0' ,
						PRIMARY KEY (id) ,
						UNIQUE attribute_group_id_attid (attribute_group_id, attribute_id) ,
						KEY attribute_set_id (attribute_set_id, position) ,
						KEY position (position) ,
						KEY attribute_id (attribute_id) ,
						KEY attribute_set_id_position (attribute_set_id) ,
						KEY attribute_group_id (attribute_group_id) ,
						KEY entity_type_id (entity_type_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";
				$wpdb->query($query);
			}

		/*	ATTRIBUTES VALUE VARCHAR	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR . "'") != WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR . " (
						value_id INT(10) NOT NULL AUTO_INCREMENT ,
						entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						language_id INT(10) UNSIGNED NOT NULL ,
						value VARCHAR(255) NOT NULL DEFAULT '' ,
						PRIMARY KEY (value_id) ,
						UNIQUE entity_attribute_id (entity_id, attribute_id) ,
						INDEX entity_id (entity_id) ,
						INDEX attribute_id (attribute_id) ,
						INDEX entity_type_id (entity_type_id) ,
						INDEX language_id (language_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
				$wpdb->query($query);
			}
		/*	ATTRIBUTES VALUE DATETIME	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME . "'") != WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME . " (
						value_id INT(10) NOT NULL AUTO_INCREMENT ,
						entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						language_id INT(10) UNSIGNED NOT NULL ,
						value datetime ,
						PRIMARY KEY (value_id) ,
						UNIQUE entity_attribute_id (entity_id, attribute_id) ,
						INDEX entity_id (entity_id) ,
						INDEX attribute_id (attribute_id) ,
						INDEX entity_type_id (entity_type_id) ,
						INDEX language_id (language_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";
				$wpdb->query($query);
			}		
		/*	ATTRIBUTES VALUE DECIMAL	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . "'") != WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " (
						value_id INT(10) NOT NULL AUTO_INCREMENT ,
						entity_type_id INT(10) UNSIGNED NOT NULL ,
						attribute_id INT(10) UNSIGNED NOT NULL ,
						entity_id INT(10) UNSIGNED NOT NULL ,
						language_id INT(10) UNSIGNED NOT NULL ,
						value decimal(12,4) NOT NULL ,
						PRIMARY KEY (value_id) ,
						UNIQUE entity_attribute_id (entity_id, attribute_id) ,
						INDEX entity_id (entity_id) ,
						INDEX attribute_id (attribute_id) ,
						INDEX entity_type_id (entity_type_id) ,
						INDEX language_id (language_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";
				$wpdb->query($query);
			}
		/*	ATTRIBUTES VALUE INTEGER	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . "'") != WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " (
						value_id INT(10) NOT NULL AUTO_INCREMENT ,
						entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						language_id INT(10) UNSIGNED NOT NULL ,
						value INT(10) NOT NULL,
						PRIMARY KEY (value_id) ,
						UNIQUE entity_attribute_id (entity_id, attribute_id) ,
						INDEX entity_id (entity_id) ,
						INDEX attribute_id (attribute_id) ,
						INDEX entity_type_id (entity_type_id) ,
						INDEX language_id (language_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";
				$wpdb->query($query);
			}
		/*	ATTRIBUTES VALUE TEXT	*/
			if( $wpdb->get_var("show tables like '" . WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT . "'") != WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT . " (
						value_id INT(10) NOT NULL AUTO_INCREMENT ,
						entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
						language_id INT(10) UNSIGNED NOT NULL ,
						value text NOT NULL DEFAULT '' ,
						PRIMARY KEY (value_id) ,
						UNIQUE entity_attribute_id (entity_id, attribute_id) ,
						INDEX entity_id (entity_id) ,
						INDEX attribute_id (attribute_id) ,
						INDEX entity_type_id (entity_type_id) ,
						INDEX language_id (language_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";
				$wpdb->query($query);
			}

		/*	PLUGIN PERMISSIONS	*/
			if($wpdb->get_var("show tables like '" . WPSHOP_DBT_PERMISSIONS . "'") != WPSHOP_DBT_PERMISSIONS)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_PERMISSIONS . " (
						id int(10) unsigned NOT NULL auto_increment,
						status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
						creation_date datetime ,
						last_update_date datetime ,
						set_by_default enum('yes', 'no') collate utf8_unicode_ci NOT NULL default 'no' ,
						permission_type enum('read', 'write', 'delete') collate utf8_unicode_ci NOT NULL default 'read',
						permission_sub_type varchar(64) collate utf8_unicode_ci NOT NULL,
						permission_module varchar(64) collate utf8_unicode_ci NOT NULL ,
						permission varchar(64) collate utf8_unicode_ci NOT NULL ,
						permission_name varchar(64) collate utf8_unicode_ci NOT NULL ,
						PRIMARY KEY  (id),
						KEY status (status),
						KEY permission_type (permission_type),
						UNIQUE permission_unique_key (permission_module, permission)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Define the different permissions available'; ";
				$wpdb->query($query);
			}

		/*	DOCUMENT	*/
			if($wpdb->get_var("show tables like '" . WPSHOP_DBT_DOCUMENT . "'") != WPSHOP_DBT_DOCUMENT)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_DOCUMENT . " (
						id int(10) unsigned NOT NULL auto_increment,
						status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
						is_default enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no' COMMENT 'Define if the document is the deault document for the category',
						creation_date datetime NOT NULL,
						creation_user_id int(10) unsigned NOT NULL,
						deletion_date datetime NOT NULL,
						deletion_user_id int(10) unsigned NOT NULL,
						attribute_set_id int(10) unsigned NOT NULL,
						category varchar(255) collate utf8_unicode_ci NOT NULL,
						filename varchar(255) collate utf8_unicode_ci NOT NULL,
						filepath varchar(255) collate utf8_unicode_ci NOT NULL,
						PRIMARY KEY (id),
						KEY status (status),
						KEY is_default (is_default),
						KEY attribute_set_id (attribute_set_id)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Document management';";
				$wpdb->query($query);
			}
		/*	DOCUMENT RELATION */
			if($wpdb->get_var("show tables like '" . WPSHOP_DBT_DOCUMENT_LINK_ELEMENT . "'") != WPSHOP_DBT_DOCUMENT_LINK_ELEMENT)
			{
				$query = 
					"CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_DOCUMENT_LINK_ELEMENT . " (
						id int(10) NOT NULL auto_increment,
						status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
						default_for_element enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no' COMMENT 'Define if the document is the default one for the current element',
						attribution_date datetime NOT NULL,
						attribution_user_id bigint(20) NOT NULL,
						unassigning_date datetime NOT NULL,
						unassigning_user_id bigint(20) NOT NULL,
						document_id int(10) NOT NULL,
						element_id int(10) NOT NULL,
						element_type char(255) collate utf8_unicode_ci NOT NULL,
						PRIMARY KEY  (id),
						UNIQUE KEY uniqueKey (status,document_id,element_id,element_type),
						KEY status (status),
						KEY document_id (document_id),
						KEY element_id (element_id),
						KEY element_type (element_type),
						KEY default_for_element (default_for_element)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Relation between document and other element in the shop' ;";
				$wpdb->query($query);
			}

			$db_options->set_db_version(1);
			$db_options->set_db_option();

			wpshop_database::wpshop_db_insert($currentVersion);
		}
	}

	/**
	* Optimize the different database table 
	*/
	function wpshop_db_optimisation()
	{
		global $wpdb;

		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_PRODUCT;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_PRODUCT_RELATION;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_CATEGORY_PRODUCT_DETAILS;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_CATEGORY;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_CATEGORY_RELATION;
		$wpdb->query($query);

		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_LANGUAGE;
		$wpdb->query($query);

		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ENTITIES;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_LABEL;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_SET;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_GROUP;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_OPTION;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_OPTION_VALUE;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_DETAILS;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT;
		$wpdb->query($query);

		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_PERMISSIONS;
		$wpdb->query($query);

		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_DOCUMENT;
		$wpdb->query($query);
		$query = "OPTIMIZE TABLE " . WPSHOP_DBT_DOCUMENT_LINK_ELEMENT;
		$wpdb->query($query);
	}

	/**
	* Define the different database element to create for each plugin's version
	*/
	function wpshop_db_update()
	{
		global $wpdb;

	}

	/**
	* Define the different database element to insert for each plugin's version
	*/
	function wpshop_db_insert($versionNumber)
	{
		global $wpdb;

		switch($versionNumber)
		{
			case 0:
			{	/*	Create the default entities and attribute for those entities / Add the different permissions	*/
				/*	Insert default Eav content	*/
				/*	Entities	*/
				$query = $wpdb->prepare(
				"INSERT INTO " . WPSHOP_DBT_ENTITIES . " (id, status, creation_date, code, entity_table) 
					VALUES
				('', 'valid', NOW(), 'product', '" . WPSHOP_DBT_PRODUCT . "'), 
				('', 'valid', NOW(), 'product_category', '" . WPSHOP_DBT_CATEGORY . "'), 
				('', 'valid', NOW(), 'document', '" . WPSHOP_DBT_DOCUMENT . "')");
				$wpdb->query($query);

				/*	Get the value usefull for the script	*/
				$productEntityId = $wpdb->get_row($wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ENTITIES . " WHERE code = 'product'"));
				$productCategoryEntityId = $wpdb->get_row($wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ENTITIES . " WHERE code = 'product_category'"));
				$documentEntityId = $wpdb->get_row($wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ENTITIES . " WHERE code = 'document'"));
				$generalAttributeGroup = __('G&eacute;n&eacute;ral', 'wpshop');
				$imageAttributeGroup = __('Images', 'wpshop');
				$documentAttributeGroup = __('Documents', 'wpshop');

				/*	Attribute set	*/
				$query = $wpdb->prepare(
				"INSERT INTO " . WPSHOP_DBT_ATTRIBUTE_SET . " (id, status, creation_date, position, entity_id, name) 
					VALUES
				('', 'valid', NOW(), '0', '" . $productEntityId->id . "', 'Default'), 
				('', 'valid', NOW(), '0', '" . $productCategoryEntityId->id . "', 'Default'), 
				('', 'valid', NOW(), '0', '" . $documentEntityId->id . "', 'Default')");
				$wpdb->query($query);

				/*	Attribute group	*/
					/*	Get the value to assign	*/
					$query = $wpdb->prepare("SELECT id, name, entity_id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE status = 'valid'");
					$wpshopAttributeSetList = $wpdb->get_results($query);
					$attributeGroupSubQuery = "  ";
					foreach($wpshopAttributeSetList as $wpshopAttributeSet)
					{
						$attributeGroupSubQuery .= "('', 'valid', '" . $wpshopAttributeSet->id . "', '0', NOW(), '" . wpshop_tools::slugify($generalAttributeGroup, array('noAccent', 'noSpaces', 'lowerCase')) . "', '" . $generalAttributeGroup . "'), ";
					}
					$attributeGroupSubQuery = trim(substr($attributeGroupSubQuery, 0, -2));
				$query = $wpdb->prepare(
				"INSERT INTO " . WPSHOP_DBT_ATTRIBUTE_GROUP . " (id, status, attribute_set_id, position, creation_date, code, name) 
					VALUES " .$attributeGroupSubQuery);
				$wpdb->query($query);

				/*	Attributes	*/
				$query = $wpdb->prepare(
				"INSERT INTO " . WPSHOP_DBT_ATTRIBUTE . " (id, status, creation_date, entity_id, is_required, data_type, code, frontend_input, frontend_label) 
					VALUES
				('', 'valid', NOW(), '" . $productEntityId->id . "', 'no', 'static', 'product_status', 'text', '" . __('Visibilit&eacute; du produit', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $productEntityId->id . "', 'yes', 'static', 'product_reference', 'text', '" . __('R&eacute;f&eacute;rence du produit', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $productEntityId->id . "', 'yes', 'static', 'product_attribute_set_id', 'text', '" . __('Set d\'\'attributs', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $productEntityId->id . "', 'yes', 'varchar', 'product_name', 'text', '" . __('Nom du produit', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $productEntityId->id . "', 'no', 'text', 'product_description', 'textarea', '" . __('Description du produit', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $productEntityId->id . "', 'no', 'text', 'product_short_description', 'textarea', '" . __('Description courte du produit', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $productEntityId->id . "', 'no', 'decimal', 'product_weight', 'text', '" . __('Poids du produit', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $productCategoryEntityId->id . "', 'no', 'static', 'product_category_status', 'text', '" . __('Visibilit&eacute; de la cat&eacute;gorie', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $productCategoryEntityId->id . "', 'yes', 'varchar', 'product_category_name', 'text', '" . __('Nom de la cat&eacute;gorie', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $productCategoryEntityId->id . "', 'no', 'text', 'product_category_description', 'textarea', '" . __('Description de la cat&eacute;gorie', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $documentEntityId->id . "', 'no', 'static', 'document_status', 'text', '" . __('Visibilit&eacute; du document', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $documentEntityId->id . "', 'no', 'varchar', 'document_name', 'text', '" . __('Nom du document', 'wpshop') . "'),
				('', 'valid', NOW(), '" . $documentEntityId->id . "', 'no', 'text', 'document_description', 'textarea', '" . __('Description du document', 'wpshop') . "')");
				$wpdb->query($query);

				/*	Attribute group	*/
					$attribute_set_group_details_query = "  ";
					/*	Get the value to assign	*/
					$query = $wpdb->prepare(
						"SELECT ATTRIBUTE.entity_id, ATTRIBUTE.id AS attribute_id
						FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE
						WHERE code IN ('reference', 'product_status', 'attribute_set_id', 'product_name', 'product_description', 'product_short_description', 'product_weight')");
					$wpshopProductGeneral = $wpdb->get_results($query);
					$position = 0;
					foreach($wpshopProductGeneral as $wpshopProductIdGeneral)
					{
						$query = $wpdb->prepare(
							"SELECT ATTRIBUTE_SET.id AS attribute_set_id
							FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTRIBUTE_SET
							WHERE ATTRIBUTE_SET.entity_id = %d", $wpshopProductIdGeneral->entity_id);
						$wpshopAttributeSetId = $wpdb->get_row($query);
						$query = $wpdb->prepare(
							"SELECT ATTRIBUTE_GROUP.id AS attribute_group_id
							FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
							WHERE ATTRIBUTE_GROUP.attribute_set_id = %d
								AND ATTRIBUTE_GROUP.code = '" . wpshop_tools::slugify($generalAttributeGroup, array('noAccent', 'noSpaces', 'lowerCase')) . "' ", $wpshopAttributeSetId->attribute_set_id);
						$wpshopAttributeGroupId = $wpdb->get_row($query);
						$attribute_set_group_details_query .=	"('', '" . $wpshopProductIdGeneral->entity_id . "', '" . $wpshopAttributeSetId->attribute_set_id . "', '" . $wpshopAttributeGroupId->attribute_group_id . "', '" . $wpshopProductIdGeneral->attribute_id . "', '" . $position . "'), ";
						$position++;
					}

					$query = $wpdb->prepare(
						"SELECT ATTRIBUTE.entity_id, ATTRIBUTE.id AS attribute_id
						FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE
						WHERE code IN ('product_category_status', 'product_category_name', 'product_category_description')");
					$wpshopCategoryGeneral = $wpdb->get_results($query);
					$position = 0;
					foreach($wpshopCategoryGeneral as $wpshopProductCategoriesIdGeneral)
					{
						$query = $wpdb->prepare(
							"SELECT ATTRIBUTE_SET.id AS attribute_set_id
							FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTRIBUTE_SET
							WHERE ATTRIBUTE_SET.entity_id = %d", $wpshopProductCategoriesIdGeneral->entity_id);
						$wpshopAttributeSetId = $wpdb->get_row($query);
						$query = $wpdb->prepare(
							"SELECT ATTRIBUTE_GROUP.id AS attribute_group_id
							FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
							WHERE ATTRIBUTE_GROUP.attribute_set_id = %d
								AND ATTRIBUTE_GROUP.code = '" . wpshop_tools::slugify($generalAttributeGroup, array('noAccent', 'noSpaces', 'lowerCase')) . "' ", $wpshopAttributeSetId->attribute_set_id);
						$wpshopAttributeGroupId = $wpdb->get_row($query);
						$attribute_set_group_details_query .=	"('', '" . $wpshopProductCategoriesIdGeneral->entity_id . "', '" . $wpshopAttributeSetId->attribute_set_id . "', '" . $wpshopAttributeGroupId->attribute_group_id . "', '" . $wpshopProductCategoriesIdGeneral->attribute_id . "', '" . $position . "'), ";
						$position++;
					}

					$query = $wpdb->prepare(
						"SELECT ATTRIBUTE.entity_id, ATTRIBUTE.id AS attribute_id
						FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE
						WHERE code IN ('document_status', 'document_name', 'document_description')");
					$wpshopDocumentGeneral = $wpdb->get_results($query);
					$position = 0;
					foreach($wpshopDocumentGeneral as $wpshopDocumentIdGeneral)
					{
						$query = $wpdb->prepare(
							"SELECT ATTRIBUTE_SET.id AS attribute_set_id
							FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTRIBUTE_SET
							WHERE ATTRIBUTE_SET.entity_id = %d", $wpshopDocumentIdGeneral->entity_id);
						$wpshopAttributeSetId = $wpdb->get_row($query);
						$query = $wpdb->prepare(
							"SELECT ATTRIBUTE_GROUP.id AS attribute_group_id
							FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
							WHERE ATTRIBUTE_GROUP.attribute_set_id = %d
								AND ATTRIBUTE_GROUP.code = '" . wpshop_tools::slugify($generalAttributeGroup, array('noAccent', 'noSpaces', 'lowerCase')) . "' ", $wpshopAttributeSetId->attribute_set_id);
						$wpshopAttributeGroupId = $wpdb->get_row($query);
						$attribute_set_group_details_query .=	"('', '" . $wpshopDocumentIdGeneral->entity_id . "', '" . $wpshopAttributeSetId->attribute_set_id . "', '" . $wpshopAttributeGroupId->attribute_group_id . "', '" . $wpshopDocumentIdGeneral->attribute_id . "', '" . $position . "'), ";
						$position++;
					}

				$query = $wpdb->prepare(
				"INSERT INTO " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " (id, entity_type_id, attribute_set_id, attribute_group_id, attribute_id, position) 
					VALUES " . substr($attribute_set_group_details_query, 0, -2));
				$wpdb->query($query);

				/*	Insert user permissions	*/
				$query = $wpdb->prepare(
				"INSERT INTO " . WPSHOP_DBT_PERMISSIONS . " (id, status, creation_date, set_by_default, permission_type, permission_sub_type, permission_module, permission, permission_name) 
					VALUES 
				('', 'valid', NOW(), 'yes', 'read', '', 'dashboard', 'wpshop_view_dashboard', '" . __('Voir le tableau de bord', 'wpshop') . "'),

				('', 'valid', NOW(), 'yes', 'read', '', 'product', 'wpshop_view_product', '" . __('Voir les produits', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'read', 'detail', 'product', 'wpshop_view_product_details', '" . __('Voir le d&eacute;tail des produits', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'edit', 'product', 'wpshop_edit_product', '" . __('&Eacute;diter les produits', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'add', 'product', 'wpshop_add_product', '" . __('Ajouter des produits', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'delete', '', 'product', 'wpshop_delete_product', '" . __('Supprimer des produits', 'wpshop') . "'),

				('', 'valid', NOW(), 'yes', 'read', '', 'product_category', 'wpshop_view_product_category', '" . __('Voir les cat&eacute;gories de produits', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'read', 'detail', 'product_category', 'wpshop_view_product_category_details', '" . __('Voir le d&eacute;tail des cat&eacute;gories de produits', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'edit', 'product_category', 'wpshop_edit_product_category', '" . __('&Eacute;diter les cat&eacute;gories de produits', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'add', 'product_category', 'wpshop_add_product_category', '" . __('Ajouter des cat&eacute;gories de produits', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'delete', '', 'product_category', 'wpshop_delete_product_category', '" . __('Supprimer des cat&eacute;gories de produits', 'wpshop') . "'),

				('', 'valid', NOW(), 'no', 'read', '', 'attribute_set', 'wpshop_view_attribute_set', '" . __('Voir les groupes d\'\'attributs', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'read', 'detail', 'attribute_set', 'wpshop_view_attribute_set_details', '" . __('Voir le d&eacute;tail des groupes d\'\'attributs', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'edit', 'attribute_set', 'wpshop_edit_attribute_set', '" . __('&Eacute;diter les ensembles d\'\'attributs', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'add', 'attribute_set', 'wpshop_add_attribute_set', '" . __('Ajouter des ensembles d\'\'attributs', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'delete', '', 'attribute_set', 'wpshop_delete_attribute_set', '" . __('Supprimer des ensembles d\'\'attributs', 'wpshop') . "'),

				('', 'valid', NOW(), 'no', 'read', '', 'attribute', 'wpshop_view_attribute', '" . __('Voir les attributs', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'read', 'detail', 'attribute', 'wpshop_view_attribute_details', '" . __('Voir le d&eacute;tail des attributs', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'edit', 'attribute', 'wpshop_edit_attribute', '" . __('&Eacute;diter les attributs', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'add', 'attribute', 'wpshop_add_attribute', '" . __('Ajouter des attributs', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'delete', '', 'attribute', 'wpshop_delete_attribute', '" . __('Supprimer des attributs', 'wpshop') . "'),

				('', 'valid', NOW(), 'no', 'read', '', 'language', 'wpshop_view_language', '" . __('Voir les langues', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'read', 'detail', 'language', 'wpshop_view_language_details', '" . __('Voir le d&eacute;tail des langues', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'edit', 'language', 'wpshop_edit_language', '" . __('&Eacute;diter les langues', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'add', 'language', 'wpshop_add_language', '" . __('Ajouter des langues', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'delete', '', 'language', 'wpshop_delete_language', '" . __('Supprimer des langues', 'wpshop') . "'),

				('', 'valid', NOW(), 'yes', 'read', '', 'document', 'wpshop_view_document', '" . __('Voir les documents', 'wpshop') . "'), 
				('', 'valid', NOW(), 'yes', 'read', 'detail', 'document', 'wpshop_view_document_details', '" . __('Voir le d&eacute;tail des documents', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'edit', 'document', 'wpshop_edit_document', '" . __('&Eacute;diter les document', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'add', 'document', 'wpshop_add_document', '" . __('Ajouter des documents', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'delete', '', 'document', 'wpshop_delete_document', '" . __('Supprimer des documents', 'wpshop') . "'), 

				('', 'valid', NOW(), 'no', 'write', '', 'document', 'wpshop_add_document_link', '" . __('Lier un document &agrave; un &eacute;l&eacute;ment', 'wpshop') . "'),

				('', 'valid', NOW(), 'yes', 'read', '', 'options', 'wpshop_view_options', '" . __('Voir les options', 'wpshop') . "'), 
				('', 'valid', NOW(), 'no', 'write', 'edit', 'options', 'wpshop_edit_options', '" . __('&Eacute;diter les options', 'wpshop') . "')");
				$wpdb->query($query);

				/*	Insert different languages	*/
				$query = $wpdb->prepare(
				"INSERT INTO " . WPSHOP_DBT_LANGUAGE . " (id, status, creation_date, is_default, code, name, icon_path)
					VALUES 
				('', 'valid', NOW(), 'yes', 'fr_FR', '" . __('Fran&ccedil;ais', 'wpshop') . "', '/plugins/" . WPSHOP_IMAGE_URL . "pictos/flag/flag_fr.png')");
				$wpdb->query($query);
			}
			break;
		}

	}

	/**
	*	Prepare the different field before use them in the query
	*
	*	@param array $prm An array containing the fields to prepare
	*	@param mixed $operation The type of query we are preparing the vars for
	*
	*	@return mixed $preparedFields The fields ready to be injected in the query
	*/
	function prepare_query($prm, $operation = 'creation')
	{
		$preparedFields = array();

		foreach($prm as $field => $value)
		{
			if($field != 'id')
			{
				if($operation == 'creation')
				{
					$preparedFields['fields'][] = $field;
					$preparedFields['values'][] = "'" . mysql_real_escape_string($value) . "'";
				}
				elseif($operation == 'update')
				{
					$preparedFields['values'][] = $field . " = '" . mysql_real_escape_string($value) . "'";
				}
			}
		}

		return $preparedFields;
	}

	/**
	*	Get the field list into a database table
	*
	*	@param string $table_name The name of the table we want to retrieve field list for
	*
	*	@return object $field_list A wordpress database object containing the different field of the table
	*/
	function get_field_list($table_name)
	{
		global $wpdb;

		$query = $wpdb->prepare("SHOW COLUMNS FROM " . $table_name);
		$field_list = $wpdb->get_results($query);

		return $field_list;
	}
	/**
	*	Get a field defintion into a database table
	*
	*	@param string $table_name The name of the table we want to retrieve field list for
	*
	*	@return object $field A wordpress database object containing the field definition into the database table
	*/
	function get_field_definition($table_name, $field)
	{
		global $wpdb;

		$query = $wpdb->prepare("SHOW COLUMNS FROM " . $table_name . " WHERE Field = %s", $field);
		$fieldDefinition = $wpdb->get_results($query);

		return $fieldDefinition;
	}

	/**
	*	Make a translation of the different database field type into a form input type
	*
	*	@param string $table_name The name of the table we want to retrieve field input type for
	*
	*	@return array $field_to_form An array with the list of field with its type, name and value
	*/
	function fields_to_input($table_name)
	{

		$list_of_field_to_convert = wpshop_database::get_field_list($table_name);

$field_to_form = self::fields_type($list_of_field_to_convert);

		return $field_to_form;
	}

	function fields_type($list_of_field_to_convert)
	{
		$field_to_form = array();
		$i = 0;
		foreach ($list_of_field_to_convert as $Key => $field_definition){

			$field_to_form[$i]['name'] = $field_definition->Field;
			$field_to_form[$i]['value'] = $field_definition->Default;

			$type = 'text';
			if(($field_definition->Key == 'PRI') || ($field_definition->Field == 'creation_date') || ($field_definition->Field == 'last_update_date'))
			{
				$type =  'hidden';
			}
			else
			{
				$fieldtype = explode('(',$field_definition->Type);
				if($fieldtype[1] != '')$fieldtype[1] = str_replace(')','',$fieldtype[1]);

				if(($fieldtype[0] == 'char') || ($fieldtype[0] == 'varchar') || ($fieldtype[0] == 'int'))
				{
					$type = 'text';
				}
				elseif($fieldtype[0] == 'text')
				{
					$type = 'textarea';
				}
				elseif($fieldtype[0] == 'enum')
				{
					$fieldtype[1] = str_replace("'","",$fieldtype[1]);
					$possible_value = explode(",",$fieldtype[1]);

					if(count($possible_value) > 1)
					{
						$type = 'select';
					}
					else
					{
						$type = 'radio';
					}

					$field_to_form[$i]['possible_value'] = $possible_value;
				}
			}
			$field_to_form[$i]['type'] = $type;
			
			$i++;
		}
		return $field_to_form;
	}

	/**
	*	Save a new attribute in database
	*
	*	@param array $informationsToSet An array with the different information we want to set
	*
	*	@return string $requestResponse A message that allows to know if the creation has been done correctly or not
	*/
	function save($informationsToSet, $dataBaseTable)
	{
		global $wpdb;
		$requestResponse = '';

		$whatToUpdate = wpshop_database::prepare_query($informationsToSet, 'creation');
		$query = $wpdb->prepare(
			"INSERT INTO " . $dataBaseTable . " 
			(" . implode(', ', $whatToUpdate['fields']) . ")
			VALUES
			(" . implode(', ', $whatToUpdate['values']) . ") "
		);

		if( $wpdb->query($query) )
		{
			$requestResponse = 'done';
		}
		else
		{
			$requestResponse = 'error';
		}

		return $requestResponse;
	}
	/**
	*	Update an existing attribute in database
	*
	*	@param array $informationsToSet An array with the different information we want to set
	*
	*	@return string $requestResponse A message that allows to know if the update has been done correctly or not
	*/
	function update($informationsToSet, $id, $dataBaseTable)
	{
		global $wpdb;
		$requestResponse = '';

		$whatToUpdate = wpshop_database::prepare_query($informationsToSet, 'update');
		$query = $wpdb->prepare(
			"UPDATE " . $dataBaseTable . " 
			SET " . implode(', ', $whatToUpdate['values']) . "
			WHERE id = '%s' ",
			$id
		);
		if( $wpdb->query($query) )
		{
			$requestResponse = 'done';
		}
		elseif( $wpdb->query($query) == 0 )
		{
			$requestResponse = 'nothingToUpdate';
		}
		else
		{
			$requestResponse = 'error';
		}

		return $requestResponse;
	}

}