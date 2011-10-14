<?php
/**
* Plugin database definition file.
* 
*	This file contains the different definitions for the database structure. It will permit to check if database is correctly build
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies-db
*/

/**
*	USAGE FOR ADDING REMOVING CHANGING FIELD OF DATABASE TABLE
*
*						ADDING A NEW FIELD TO AN EXISTING TABLE
*	$wpshop_db_table_additionnal_field[$current_version][DATABASE_TABLE]['ADD']['FIELDNAME']['type'] = 'FIELDTYPE_MYSQL_TYPE';
*	$wpshop_db_table_additionnal_field[$current_version][DATABASE_TABLE]['ADD']['FIELDNAME']['length'] = 'FIELDLENGTH_VALUE';
*	$wpshop_db_table_additionnal_field[$current_version][DATABASE_TABLE]['ADD']['FIELDNAME']['option'] = 'FIELDOPTION';
*
*						REMOVING A EXISINTG FIELD TO A TABLE
*	$wpshop_db_table_additionnal_field[$current_version][DATABASE_TABLE]['DROP'][] = 'FIELDNAME';
*
*
*						ADDING A NEW FIELD TO AN EXISTING TABLE
*	$wpshop_db_table_additionnal_field[$current_version][DATABASE_TABLE]['CHANGE']['FIELDNAME']['old_field_name'] = 'OLDFIELDNAME';
*	$wpshop_db_table_additionnal_field[$current_version][DATABASE_TABLE]['CHANGE']['FIELDNAME']['type'] = 'FIELDTYPE_MYSQL_TYPE';
*	$wpshop_db_table_additionnal_field[$current_version][DATABASE_TABLE]['CHANGE']['FIELDNAME']['length'] = 'FIELDLENGTH_VALUE';
*	$wpshop_db_table_additionnal_field[$current_version][DATABASE_TABLE]['CHANGE']['FIELDNAME']['option'] = 'FIELDOPTION';
*
*/

{/*	Define the main table necessary for plugin working	*/
/**
*	Define the table definition for entities
*/
$wpshop_db_table['entities']['db_table_name'] = WPSHOP_DBT_ENTITIES;
$wpshop_db_table['entities']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ENTITIES . " (
		id INT(10) unsigned NOT NULL AUTO_INCREMENT ,
		status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
		creation_date datetime ,
		last_update_date datetime ,
		code varchar(50) collate utf8_unicode_ci NOT NULL ,
		entity_table varchar(255) collate utf8_unicode_ci NOT NULL ,
		PRIMARY KEY (id),
		KEY status (status),
		UNIQUE code (code)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";

/**
*	Define the table definition for attributes set
*/
$wpshop_db_table['attribute_set']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_SET;
$wpshop_db_table['attribute_set']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_SET . " (
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
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

/**
*	Define the table definition for attribute groups
*/
$wpshop_db_table['attribute_set_group']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_GROUP;
$wpshop_db_table['attribute_set_group']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_GROUP . " (
		id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
		status ENUM('valid','moderated','deleted') NULL DEFAULT 'valid' ,
		attribute_set_id INT UNSIGNED NOT NULL DEFAULT '0' ,
		position INT NOT NULL DEFAULT '0' ,
		creation_date datetime ,
		last_update_date datetime ,
		code VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '' ,
		name VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '' ,
		PRIMARY KEY (id) ,
		UNIQUE attribute_set_id_name_unique (attribute_set_id, code) ,
		KEY attribute_set_id_position_key (attribute_set_id, position) ,
		KEY attribute_set_id_index (attribute_set_id)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";

/**
*	Define the table definition for attributes units
*/
$wpshop_db_table['attributes_unit']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_UNIT;
$wpshop_db_table['attributes_unit']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_UNIT . " (
		id INT(10) unsigned NOT NULL AUTO_INCREMENT ,
		status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
		creation_date datetime ,
		last_update_date datetime ,
		unit char(25) collate utf8_unicode_ci NOT NULL ,
		name char(50) collate utf8_unicode_ci NOT NULL ,
		PRIMARY KEY (id),
		KEY status (status)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";

/**
*	Define the table definition for attributes
*/
$wpshop_db_table['attributes']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE;
$wpshop_db_table['attributes']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE . " (
		id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
		status ENUM('valid','moderated','deleted') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'valid' ,
		creation_date datetime ,
		last_update_date datetime ,
		entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		is_visible_in_front ENUM('yes','no') NOT NULL DEFAULT 'yes' ,
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
		is_requiring_unit ENUM('yes','no') NOT NULL DEFAULT 'no' ,
		data_type ENUM('datetime','decimal','integer','text','varchar') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'varchar' ,
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
		KEY is_used_for_sort_by (is_used_for_sort_by),
		KEY is_configurable (is_configurable),
		KEY is_requiring_unit (is_requiring_unit),
		KEY data_type (data_type)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/**
*	Define the table definition for attributes
*/
$wpshop_db_table['attributes_set_details']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_DETAILS;
$wpshop_db_table['attributes_set_details']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " (
		id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
		status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
		creation_date datetime ,
		last_update_date datetime ,
		entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		attribute_set_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		attribute_group_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		position INT(10) NOT NULL DEFAULT '0' ,
		PRIMARY KEY (id) ,
		KEY status (status),
		KEY attribute_set_id (attribute_set_id, position) ,
		KEY position (position) ,
		KEY attribute_id (attribute_id) ,
		KEY attribute_set_id_position (attribute_set_id) ,
		KEY attribute_group_id (attribute_group_id) ,
		KEY entity_type_id (entity_type_id)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";

/**
*	Define the table definition for attributes values (VARCHAR)
*/
$wpshop_db_table['attributes_varchar']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR;
$wpshop_db_table['attributes_varchar']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR . " (
		value_id INT(10) NOT NULL AUTO_INCREMENT ,
		entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		unit_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		language CHAR(10) NOT NULL DEFAULT '" . get_locale() . "',
		value VARCHAR(255) NOT NULL DEFAULT '' ,
		PRIMARY KEY (value_id) ,
		UNIQUE entity_attribute_id (entity_id, attribute_id) ,
		INDEX entity_id (entity_id) ,
		INDEX attribute_id (attribute_id) ,
		INDEX entity_type_id (entity_type_id) ,
		INDEX unit_id (unit_id) ,
		INDEX language (language)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";

/**
*	Define the table definition for attributes values (DATETIME)
*/
$wpshop_db_table['attributes_datetime']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME;
$wpshop_db_table['attributes_datetime']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME . " (
		value_id INT(10) NOT NULL AUTO_INCREMENT ,
		entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		unit_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		language CHAR(10) NOT NULL DEFAULT '" . get_locale() . "',
		value datetime ,
		PRIMARY KEY (value_id) ,
		UNIQUE entity_attribute_id (entity_id, attribute_id) ,
		INDEX entity_id (entity_id) ,
		INDEX attribute_id (attribute_id) ,
		INDEX entity_type_id (entity_type_id) ,
		INDEX unit_id (unit_id) ,
		INDEX language (language)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";

/**
*	Define the table definition for attributes values (DECIMAL)
*/
$wpshop_db_table['attributes_decimal']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL;
$wpshop_db_table['attributes_decimal']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " (
		value_id INT(10) NOT NULL AUTO_INCREMENT ,
		entity_type_id INT(10) UNSIGNED NOT NULL ,
		attribute_id INT(10) UNSIGNED NOT NULL ,
		entity_id INT(10) UNSIGNED NOT NULL ,
		unit_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		language CHAR(10) NOT NULL DEFAULT '" . get_locale() . "',
		value decimal(12,4) NOT NULL ,
		PRIMARY KEY (value_id) ,
		UNIQUE entity_attribute_id (entity_id, attribute_id) ,
		INDEX entity_id (entity_id) ,
		INDEX attribute_id (attribute_id) ,
		INDEX entity_type_id (entity_type_id) ,
		INDEX unit_id (unit_id) ,
		INDEX language (language)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";

/**
*	Define the table definition for attributes values (INTEGER)
*/
$wpshop_db_table['attributes_integer']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER;
$wpshop_db_table['attributes_integer']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " (
		value_id INT(10) NOT NULL AUTO_INCREMENT ,
		entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		unit_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		language CHAR(10) NOT NULL DEFAULT '" . get_locale() . "',
		value INT(10) NOT NULL,
		PRIMARY KEY (value_id) ,
		UNIQUE entity_attribute_id (entity_id, attribute_id) ,
		INDEX entity_id (entity_id) ,
		INDEX attribute_id (attribute_id) ,
		INDEX entity_type_id (entity_type_id) ,
		INDEX unit_id (unit_id) ,
		INDEX language (language)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";

/**
*	Define the table definition for attributes values (INTEGER)
*/
$wpshop_db_table['attributes_text']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT;
$wpshop_db_table['attributes_text']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT . " (
		value_id INT(10) NOT NULL AUTO_INCREMENT ,
		entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		unit_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		language CHAR(10) NOT NULL DEFAULT '" . get_locale() . "',
		value text NOT NULL DEFAULT '' ,
		PRIMARY KEY (value_id) ,
		UNIQUE entity_attribute_id (entity_id, attribute_id) ,
		INDEX entity_id (entity_id) ,
		INDEX attribute_id (attribute_id) ,
		INDEX entity_type_id (entity_type_id) ,
		INDEX unit_id (unit_id) ,
		INDEX language (language)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";

}


{/*	Define database structure for plugin version	*/													/*	1	*/
	$current_version = 1;

	/*	Define the table definition for documentation	*/
	$wpshop_db_table_version[$current_version]['documentation']['db_table_name'] = $wpdb->prefix . wpshop_doc::prefix . '__documentation';
	$wpshop_db_table_version[$current_version]['documentation']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . wpshop_doc::prefix . "__documentation (
		doc_id int(11) unsigned NOT NULL AUTO_INCREMENT,
		doc_active ENUM('active', 'deleted') default 'active',
		doc_page_name varchar(255) NOT NULL,
		doc_url varchar(255) NOT NULL,
		doc_html text NOT NULL,
		doc_creation_date datetime NOT NULL,
		PRIMARY KEY ( doc_id )
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";

	/*	Define the table definition for unit groups	*/
	$wpshop_db_table_version[$current_version]['unit_groups']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP;
	$wpshop_db_table_version[$current_version]['unit_groups']['main_definition'] = "
		CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . " (
			id INT(10) unsigned NOT NULL AUTO_INCREMENT ,
			status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
			creation_date datetime ,
			last_update_date datetime ,
			name varchar(255) collate utf8_unicode_ci NOT NULL ,
			PRIMARY KEY (id),
			KEY status (status)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";

	/**
	*	Define the table definition for attributes values (INTEGER)
	*/
	$wpshop_db_table_version[$current_version]['attributes_histo']['db_table_name'] = WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO;
	$wpshop_db_table_version[$current_version]['attributes_histo']['main_definition'] = "
	CREATE TABLE IF NOT EXISTS " . WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO . " (
		value_id INT(10) NOT NULL AUTO_INCREMENT ,
		status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
		creation_date datetime ,
		last_update_date datetime ,
		original_value_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		unit_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
		language CHAR(10) NOT NULL DEFAULT '" . get_locale() . "',
		value text NOT NULL DEFAULT '' ,
		value_type char(70) NOT NULL DEFAULT '' ,
		PRIMARY KEY (value_id) ,
		INDEX entity_id (entity_id) ,
		INDEX attribute_id (attribute_id) ,
		INDEX entity_type_id (entity_type_id) ,
		INDEX unit_id (unit_id) ,
		INDEX language (language),
		KEY status (status)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; ";

	/*	Add a new field for the unit group in unit database table	*/
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE_UNIT]['ADD']['group_id']['type'] = 'INT';
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE_UNIT]['ADD']['group_id']['length'] = '10';
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE_UNIT]['ADD']['group_id']['option'] = ' AFTER last_update_date ';

	/*	Add a new field for the unit group in attribute database table	*/
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE]['ADD']['_unit_group_id']['type'] = 'INT';
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE]['ADD']['_unit_group_id']['length'] = '10';
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE]['ADD']['_unit_group_id']['option'] = ' AFTER is_requiring_unit ';

	/*	Add a new field in order to define default unit for a group in unit database table	*/
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE_UNIT]['ADD']['is_default_of_group']['type'] = 'ENUM';
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE_UNIT]['ADD']['is_default_of_group']['length'] = "'yes','no'";
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE_UNIT]['ADD']['is_default_of_group']['option'] = " DEFAULT 'no' AFTER group_id ";

	/*	Add a new field in order to define default unit for a group in attribute database table	*/
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE]['ADD']['_default_unit']['type'] = 'INT';
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE]['ADD']['_default_unit']['length'] = '10';
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE]['ADD']['_default_unit']['option'] = ' AFTER _unit_group_id ';

	/*	Add a new field for the unit group in unit database table	*/
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE]['ADD']['is_historisable']['type'] = 'ENUM';
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE]['ADD']['is_historisable']['length'] = "'yes','no'";
	$wpshop_db_table_additionnal_field[$current_version][WPSHOP_DBT_ATTRIBUTE]['ADD']['is_historisable']['option'] = " DEFAULT 'yes' AFTER _default_unit ";

	unset($current_version);
}

{/*	Define database structure for plugin version	*/													/*	2	*/
	$current_version = 2;

	unset($current_version);
}