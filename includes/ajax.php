<?php
/**
* Plugin ajax request management.
*
*	Every ajax request will be send to this page wich will return the request result regarding all the parameters
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage includes
*/

/**
*	Wordpress - Ajax functionnality activation
*/
DEFINE('DOING_AJAX', true);
/**
*	Wordpress - Specify that we are in wordpress admin
*/
DEFINE('WP_ADMIN', true);
/**
*	Wordpress - Main bootstrap file that load wordpress basic files
*/
require_once('../../../../wp-load.php');
/**
*	Wordpress - Admin page that define some needed vars and include file
*/
require_once(ABSPATH . 'wp-admin/includes/admin.php');


/**
*	First thing we define the main directory for our plugin in a super global var	
*/
DEFINE('WPSHOP_PLUGIN_DIR', basename(dirname(__FILE__)));
/**
*	Include the different config for the plugin	
*/
require_once(WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/includes/configs/config.php' );
/**
*	Define the path where to get the config file for the plugin
*/
DEFINE('WPSHOP_CONFIG_FILE', WPSHOP_INC_PLUGIN_DIR . 'configs/config.php');
/**
*	Include the file which includes the different files used by all the plugin
*/
require_once(	WPSHOP_INC_PLUGIN_DIR . 'includes.php' );

/*	Get the different resquest vars to sanitize them before using	*/
$method = wpshop_tools::varSanitizer($_REQUEST['post']);
$action = wpshop_tools::varSanitizer($_REQUEST['action']);

/*	Element code define the main element type we are working on	*/
$elementCode = wpshop_tools::varSanitizer($_REQUEST['elementCode']);

/*	Element code define the secondary element type we are working on. For example when working on elementCode:Document elementType:product, we are working on the document for products	*/
$elementType = wpshop_tools::varSanitizer($_REQUEST['elementType']);
$elementIdentifier = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);

/*	First look at the request method Could be post or get	*/
switch($method)
{
	case 'true':
	{/*	In case request method is equal to true, it means that we are working with post request method	*/
		/*	Look at the element type we have to work on	*/
		switch($elementCode)
		{
			case 'document':
			{
				$documentCategory = wpshop_tools::varSanitizer($_REQUEST['documentCategory']);
				switch($action)
				{
					case 'loadDocumentListForElement';
					{
						echo wpshop_document::getAssociatedDocument($elementType, $elementIdentifier, $documentCategory, "'valid'", "'valid', 'moderated'");
					}
					break;
				}
			}
			break;
		}
	}
	break;

	default:
	{/*	Default case is get request method	*/

	}
	break;
}
