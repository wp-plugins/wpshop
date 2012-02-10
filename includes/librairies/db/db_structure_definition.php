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

$wpshop_update_way = array();
$wpshop_db_table = array();
$wpshop_db_table_list = array();
$wpshop_db_table_operation_list = array();
$wpshop_db_version = 0;

/*	Define the different database table	*/
{
	/*	Entities	*/
	$t = WPSHOP_DBT_ENTITIES;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
	id INT(10) unsigned NOT NULL AUTO_INCREMENT ,
	status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
	creation_date datetime ,
	last_update_date datetime ,
	code varchar(50) collate utf8_unicode_ci NOT NULL ,
	entity_table varchar(255) collate utf8_unicode_ci NOT NULL ,
	PRIMARY KEY (id),
	KEY status (status),
	UNIQUE code (code)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute set	*/
	$t = WPSHOP_DBT_ATTRIBUTE_SET;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute set	*/
	$t = WPSHOP_DBT_ATTRIBUTE_GROUP;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute units	*/
	$t = WPSHOP_DBT_ATTRIBUTE_UNIT;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `group_id` int(10) default NULL,
  `is_default_of_group` enum('yes','no') collate utf8_unicode_ci default 'no',
  `unit` char(25) collate utf8_unicode_ci NOT NULL,
  `name` char(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute units group	*/
	$t = WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute	*/
	$t = WPSHOP_DBT_ATTRIBUTE;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted','notused') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `entity_id` int(10) unsigned NOT NULL default '0',
  `is_visible_in_front` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  `is_global` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_user_defined` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_required` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_visible_in_advanced_search` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_searchable` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_filterable` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_comparable` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_html_allowed_on_front` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_unique` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_filterable_in_search` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_used_for_sort_by` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_configurable` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `is_requiring_unit` enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  `_unit_group_id` int(10) default NULL,
  `_default_unit` int(10) default NULL,
  `is_historisable` enum('yes','no') collate utf8_unicode_ci default 'yes',
  `is_intrinsic` enum('yes','no') collate utf8_unicode_ci default 'no',
  `data_type` enum('datetime','decimal','integer','text','varchar') collate utf8_unicode_ci NOT NULL default 'varchar',
  `backend_table` varchar(255) collate utf8_unicode_ci default NULL,
  `frontend_input` enum('text', 'textarea', 'select') collate utf8_unicode_ci NOT NULL default 'text',
  `frontend_label` varchar(255) collate utf8_unicode_ci default NULL,
  `frontend_verification` varchar(255) collate utf8_unicode_ci default NULL,
  `code` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `note` varchar(255) collate utf8_unicode_ci NOT NULL,
  `default_value` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `status` (`status`),
  KEY `is_global` (`is_global`),
  KEY `is_user_defined` (`is_user_defined`),
  KEY `is_required` (`is_required`),
  KEY `is_visible_in_advanced_search` (`is_visible_in_advanced_search`),
  KEY `is_searchable` (`is_searchable`),
  KEY `is_filterable` (`is_filterable`),
  KEY `is_comparable` (`is_comparable`),
  KEY `is_html_allowed_on_front` (`is_html_allowed_on_front`),
  KEY `is_unique` (`is_unique`),
  KEY `is_filterable_in_search` (`is_filterable_in_search`),
  KEY `is_used_for_sort_by` (`is_used_for_sort_by`),
  KEY `is_configurable` (`is_configurable`),
  KEY `is_requiring_unit` (`is_requiring_unit`),
  KEY `data_type` (`data_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute	*/
	$t = WPSHOP_DBT_ATTRIBUTE_DETAILS;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute	values (VARCHAR) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
  `value_id` int(10) NOT NULL auto_increment,
  `entity_type_id` int(10) unsigned NOT NULL default '0',
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `unit_id` int(10) unsigned NOT NULL default '0',
	`user_id` bigint(20) unsigned NOT NULL default '1',
	`creation_date_value` datetime,
  `language` char(10) collate utf8_unicode_ci NOT NULL default 'fr_FR',
  `value` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `entity_attribute_id` (`entity_id`,`attribute_id`),
  KEY `entity_id` (`entity_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `entity_type_id` (`entity_type_id`),
  KEY `unit_id` (`unit_id`),
  KEY `language` (`language`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	/*	Attribute	values (DATETIME) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
  `value_id` int(10) NOT NULL auto_increment,
  `entity_type_id` int(10) unsigned NOT NULL default '0',
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `unit_id` int(10) unsigned NOT NULL default '0',
	`user_id` bigint(20) unsigned NOT NULL default '1',
	`creation_date_value` datetime,
  `language` char(10) collate utf8_unicode_ci NOT NULL default 'fr_FR',
  `value` datetime default NULL,
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `entity_attribute_id` (`entity_id`,`attribute_id`),
  KEY `entity_id` (`entity_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `entity_type_id` (`entity_type_id`),
  KEY `unit_id` (`unit_id`),
  KEY `language` (`language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	/*	Attribute	values (DECIMAL) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
  `value_id` int(10) NOT NULL auto_increment,
  `entity_type_id` int(10) unsigned NOT NULL,
  `attribute_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `unit_id` int(10) unsigned NOT NULL default '0',
	`user_id` bigint(20) unsigned NOT NULL default '1',
	`creation_date_value` datetime,
  `language` char(10) collate utf8_unicode_ci NOT NULL default 'fr_FR',
  `value` decimal(12,5) NOT NULL,
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `entity_attribute_id` (`entity_id`,`attribute_id`),
  KEY `entity_id` (`entity_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `entity_type_id` (`entity_type_id`),
  KEY `unit_id` (`unit_id`),
  KEY `language` (`language`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	/*	Attribute	values (INTEGER) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
  `value_id` int(10) NOT NULL auto_increment,
  `entity_type_id` int(10) unsigned NOT NULL default '0',
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `unit_id` int(10) unsigned NOT NULL default '0',
	`user_id` bigint(20) unsigned NOT NULL default '1',
	`creation_date_value` datetime,
  `language` char(10) collate utf8_unicode_ci NOT NULL default 'fr_FR',
  `value` int(10) NOT NULL,
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `entity_attribute_id` (`entity_id`,`attribute_id`),
  KEY `entity_id` (`entity_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `entity_type_id` (`entity_type_id`),
  KEY `unit_id` (`unit_id`),
  KEY `language` (`language`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	/*	Attribute	values (TEXT) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
  `value_id` int(10) NOT NULL auto_increment,
  `entity_type_id` int(10) unsigned NOT NULL default '0',
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `unit_id` int(10) unsigned NOT NULL default '0',
	`user_id` bigint(20) unsigned NOT NULL default '1',
	`creation_date_value` datetime,
  `language` char(10) collate utf8_unicode_ci NOT NULL default 'fr_FR',
  `value` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `entity_attribute_id` (`entity_id`,`attribute_id`),
  KEY `entity_id` (`entity_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `entity_type_id` (`entity_type_id`),
  KEY `unit_id` (`unit_id`),
  KEY `language` (`language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute	values (HISTO) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
  `value_id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime default NULL,
  `last_update_date` datetime default NULL,
  `original_value_id` int(10) unsigned NOT NULL default '0',
  `entity_type_id` int(10) unsigned NOT NULL default '0',
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `unit_id` int(10) unsigned NOT NULL default '0',
	`user_id` bigint(20) unsigned NOT NULL default '1',
	`creation_date_value` datetime,
  `language` char(10) collate utf8_unicode_ci NOT NULL default 'fr_FR',
  `value` longtext collate utf8_unicode_ci NOT NULL,
  `value_type` char(70) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `entity_id` (`entity_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `entity_type_id` (`entity_type_id`),
  KEY `unit_id` (`unit_id`),
  KEY `language` (`language`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Plugin documentation */
	$t = $wpdb->prefix . wpshop_doc::prefix . '__documentation';
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
	doc_id int(11) unsigned NOT NULL AUTO_INCREMENT,
	doc_active ENUM('active', 'deleted') default 'active',
	doc_page_name varchar(255) NOT NULL,
	doc_url varchar(255) NOT NULL,
	doc_html text NOT NULL,
	doc_creation_date datetime NOT NULL,
	PRIMARY KEY ( doc_id )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Users' cart */
	$t = WPSHOP_DBT_CART;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,   
	`session_id` varchar(255) DEFAULT NULL,
	`user_id` int(11) unsigned DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
	/*	Users' cart content */
	$t = WPSHOP_DBT_CART_CONTENTS;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`cart_id` int(11) unsigned NOT NULL,
	`product_id` int(11) unsigned NOT NULL,
	`product_qty` int(11) unsigned NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

	/*	Messages send to user */
	$t = WPSHOP_DBT_MESSAGES;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
	`mess_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`mess_user_id` bigint(20) unsigned NOT NULL,
	`mess_user_email` varchar(255) NOT NULL,
	`mess_title` varchar(255) NOT NULL,
	`mess_message` text CHARACTER SET utf8 NOT NULL,
	`mess_statut` enum('sent','resent') NOT NULL DEFAULT 'sent',
	`mess_visibility` enum('normal','archived') NOT NULL DEFAULT 'normal',
	`mess_creation_date` datetime NOT NULL,
	`mess_last_dispatch_date` datetime NOT NULL,
	PRIMARY KEY (`mess_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	/*	Message history of send message */
	$t = WPSHOP_DBT_HISTORIC;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
	`hist_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`hist_message_id` int(11) unsigned NOT NULL,
	`hist_datetime` datetime NOT NULL,
	PRIMARY KEY (`hist_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

	/*	Message history of send message */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS;
	$wpshop_db_table[$t] = 
"CREATE TABLE {$t} (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
	status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
	creation_date datetime ,
	last_update_date datetime ,
	attribute_id INT(10) UNSIGNED NOT NULL,
	position INT(10) UNSIGNED NOT NULL DEFAULT '1',
	value VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
	label VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

}

/*	Start the different creation and update plan	*/
{/*	Version 0	*/
	$wpshop_db_version = 0;
	$wpshop_update_way[$wpshop_db_version] = 'creation';

	$wpshop_db_table_operation_list[$wpshop_db_version]['ADD_TABLE'] = array(WPSHOP_DBT_ENTITIES, WPSHOP_DBT_ATTRIBUTE_SET, WPSHOP_DBT_ATTRIBUTE_GROUP, WPSHOP_DBT_ATTRIBUTE_UNIT, WPSHOP_DBT_ATTRIBUTE, WPSHOP_DBT_ATTRIBUTE_DETAILS, WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR, WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME, WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT);

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ENTITIES, WPSHOP_DBT_ATTRIBUTE_SET, WPSHOP_DBT_ATTRIBUTE_GROUP, WPSHOP_DBT_ATTRIBUTE_UNIT, WPSHOP_DBT_ATTRIBUTE, WPSHOP_DBT_ATTRIBUTE_DETAILS, WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR, WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME, WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT);
}
{/*	Version 1	*/
	$wpshop_db_version = 1;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	/*	Add some explanation in order to check done update	*/
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_UNIT] = array('group_id', 'is_default_of_group');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE] = array('_unit_group_id', '_default_unit', 'is_historisable');
	$wpshop_db_table_operation_list[$wpshop_db_version]['ADD_TABLE'] = array($wpdb->prefix . wpshop_doc::prefix . '__documentation', WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP, WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO);

	$wpshop_db_table_list[$wpshop_db_version] = array($wpdb->prefix . wpshop_doc::prefix . '__documentation', WPSHOP_DBT_ATTRIBUTE_UNIT, WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP, WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, WPSHOP_DBT_ATTRIBUTE);
}
{/*	Version 2	*/
	$wpshop_db_version = 2;
	$wpshop_update_way[$wpshop_db_version] = 'creation';
	$wpshop_db_table_operation_list[$wpshop_db_version]['ADD_TABLE'] = array(WPSHOP_DBT_CART, WPSHOP_DBT_CART_CONTENTS, WPSHOP_DBT_MESSAGES, WPSHOP_DBT_HISTORIC);

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_CART, WPSHOP_DBT_CART_CONTENTS);
}
{/*	Version 3	*/
	$wpshop_db_version = 3;
	$wpshop_update_way[$wpshop_db_version] = 'update';

	/*	Add some explanation in order to check done update	*/
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['ADD_TABLE'] = array(WPSHOP_DBT_MESSAGES, WPSHOP_DBT_HISTORIC);

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME, WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT, WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR, WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, WPSHOP_DBT_MESSAGES, WPSHOP_DBT_HISTORIC);
}
{/*	Version 4	*/
	$wpshop_db_version = 4;
	$wpshop_update_way[$wpshop_db_version] = 'update';

	/*	Add some explanation in order to check done update	*/
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE] = array('is_intrinsic');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE);
}

{/*	Version 7	*/
	$wpshop_db_version = 7;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	/*	Add some explanation in order to check done update	*/
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT] = array(array('field' => 'value', 'type' => 'longtext'));
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO] = array(array('field' => 'value', 'type' => 'longtext'));
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL] = array(array('field' => 'value', 'type' => 'decimal(12,5)'));
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE] = array(array('field' => 'status', 'type' => "enum('valid','moderated','deleted','notused')"));
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE] = array(array('field' => 'frontend_input', 'type' => "enum('text','textarea','select')"));
	$wpshop_db_table_operation_list[$wpshop_db_version]['ADD_TABLE'] = array(WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS);

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT, WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS, WPSHOP_DBT_ATTRIBUTE, WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL);
}
{/*	Version 8	*/
	$wpshop_db_version = 8;
	$wpshop_update_way[$wpshop_db_version] = 'datas';
}