<?php
/**
* Plugin Loader
* 
* Define the different element usefull for the plugin usage. The menus, includes script, start launch script, css, translations
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different element usefull for the plugin usage. The menus, includes script, start launch script, css, translations
* @package wpshop
* @subpackage librairies
*/
class wpshop_install
{

	/**
	*	Define actions lauched after plugin activation
	*/
	function wpshop_activate()
	{
		global $db_options;

		$currentDBVersion = $db_options->get_db_version();
		if(!($currentDBVersion > 0))
		{
			$db_options->create_db_option();
		}

		wpshop_database::wpshop_db_creation();

		wpshop_install::wpshop_set_permissions();
	}

	/**
	*	Define actions launched when plugin is deactivate
	*/
	function wpshop_deactivate()
	{
		global $wpdb;

		$wpshop_permission_list = wpshop_permissions::getPermissionList();
		/**
		*	Add capabilities to the administrator role
		*/
		$role = get_role('administrator');
		foreach($wpshop_permission_list as $permission)
		{
			if( ($role != null) && $role->has_cap($permission->permission) ) 
			{
				$role->remove_cap($permission->permission);
			}
		}
		unset($role);
		/**
		*	Add capabilities to the administrator role
		*/
		$role = get_role('subscriber');
		foreach($wpshop_permission_list as $permission)
		{
			if( ($role != null) && $role->has_cap($permission->permission))
			{
				$role->remove_cap($permission->permission);
			}
		}
		unset($role);

		// delete_option('wpshop_db_option');
		// $query = $wpdb->prepare("DROP TABLE wp_eo_eav__attribute, wp_eo_eav__attribute_group_section, wp_eo_eav__attribute_label, wp_eo_eav__attribute_option, wp_eo_eav__attribute_option_value, wp_eo_eav__attribute_group, wp_eo_eav__attribute_value_datetime, wp_eo_eav__attribute_value_integer, wp_eo_eav__attribute_value_text, wp_eo_eav__attribute_value_decimal, wp_eo_eav__attribute_value_varchar, wp_eo_eav__entities, wp_eo_eav__entity_set_attribute_details, wp_wpshop__category, wp_wpshop__category_product_details, wp_wpshop__category_relation, wp_wpshop__language, wp_wpshop__permission, wp_wpshop__product, wp_wpshop__product_relation, wp_wpshop__document, wp_wpshop__document_relation");
		// $wpdb->query($query);
	}

	/**
	*	Define the different permissions affected to users
	*/
	function wpshop_set_permissions()
	{
		$wpshop_permission_list = wpshop_permissions::getPermissionList();
		/**
		*	Add capabilities to the administrator role
		*/
		$role = get_role('administrator');
		foreach($wpshop_permission_list as $permission)
		{
			if( ($role != null) && !$role->has_cap($permission->permission) ) 
			{
				$role->add_cap($permission->permission);
			}
		}
		unset($role);
	}

}