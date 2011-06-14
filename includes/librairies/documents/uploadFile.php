<?php
/**
* Document uploader
* 
* Allows to upload a file to associate to a document
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Allows to upload a file to associate to a document
* @package wpshop
* @subpackage librairies
*/
require_once($_GET['abspath'] . 'wp-load.php');
require_once($_GET['abspath'] . 'wp-admin/includes/admin.php');
require_once($_GET['mainFile']);
require_once(WPSHOP_CONFIG_FILE);
require_once(WPSHOP_LIB_PLUGIN_DIR . 'documents/documents.class.php');

$result['success'] = false;
if(current_user_can('wpshop_add_document'))
{
	$result = wpshop_document::uploadFile($_GET['folder'], 'qqfile');
}

echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);