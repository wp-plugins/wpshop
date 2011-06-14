<?php
/**
* Plugin permission manager
* 
* Define the different method to manage the different permission into the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different permission into the plugin
* @package wpshop
* @subpackage librairies
*/
class wpshop_permissions
{

	/**
	*	Get the different existing permission
	*/
	function getPermissionList()
	{
		global $wpdb;

		$query = $wpdb->prepare(
		"SELECT * FROM 
		" . WPSHOP_DBT_PERMISSIONS . "
		WHERE status = 'valid' ");

		$permissionsList = $wpdb->get_results($query);

		return $permissionsList;
	}

}