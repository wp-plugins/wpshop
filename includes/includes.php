<?php
/**
* Include every file we need in the plugin.
*
*	It avoid to include files in every script and allows to make changes on the filename easily. And to know wich file is included and were it is located
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
*/

/**
* Include the different configuration for pictures used into the plugin
*/
require_once('configs/configPicture.php');

/**
* Include the different configuration for permissions existing into the plugin
*/
require_once('configs/configDBTablesName.php');
/**
* Include the database option management file
*/
require_once('librairies/options/db_options.class.php');
require_once('librairies/options/options.class.php');

/**
* Include the tools to manage plugin's display
*/
require_once('librairies/tools.class.php');

/**
* Include the tools to manage plugin's display
*/
require_once('librairies/display.class.php');

/**
* Include the tools to manage plugin's languages
*/
require_once('librairies/languages.class.php');

/**
* Include the tools to manage plugin's document
*/
require_once('librairies/documents/documents.class.php');

/**
* Include the tools to manage attributes
*/
require_once('librairies/eav_model/attributes.class.php');
require_once('librairies/eav_model/attributes_set.class.php');
require_once('librairies/eav_model/entities.class.php');

/**
* Include the tools to manage user's permission into the plugin
*/
require_once('librairies/permissions.class.php');

/**
* Include the tools to manage database plugin
*/
require_once('librairies/database.class.php');

/**
* Include the tools to manage form into the plugin
*/
require_once('librairies/form.class.php');


include_once(WPSHOP_LIB_PLUGIN_DIR . 'dashboard/dashboard.class.php');
include_once(WPSHOP_LIB_PLUGIN_DIR . 'products/products.class.php');
include_once(WPSHOP_LIB_PLUGIN_DIR . 'categories/categories.class.php');