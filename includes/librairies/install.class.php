<?php
/**
* Plugin installation file.
* 
*	This file contains the different methods called when plugin is actived and removed
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	Class defining the different method used when plugin is activated
* @package wpshop
* @subpackage librairies
*/
class wpshop_install
{

	/**
	*	Method called when activating the plugin through wordpress plugin management page
	*	@see register_activation_hook()
	*/
	function install_wpshop(){
		self::create_options();
		self::create_default_content();
	}

	/**
	*	Add the different options into wordpress for our plugin
	*	@see install_wpshop()
	*/
	function create_options(){
		add_option('wpshop_db_options', array('db_version' => 0));
		add_option(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, array('product_slug' => 'catalog'));
		add_option('wpshop_product_categories', array('product_categories_slug' => 'category'));
		add_option('wpshop_display_option', array('wpshop_display_list_type' => 'grid', 'wpshop_display_grid_element_number' => '3', 'wpshop_display_cat_sheet_output' => array('category_description', 'category_subcategory', 'category_subproduct')));
	}

	/**
	*	Add the default content for the plugin
	*	@see install_wpshop()
	*/
	function create_default_content(){
		global $wp_rewrite, $wpdb, $wpshop_db_table, $initialEavData, $initialData;

		/*	if we will create any new pages we need to flush page cache */
		$page_creation = false;
		
		/*	Check if catalog page exists. If page does not exist so we create the page	*/
		$query = $wpdb->prepare("SELECT ID FROM ". $wpdb->posts . " WHERE post_content LIKE %s	AND post_type != %s", '%[wpshop_product_page]%', 'revision');
		$product_page = $wpdb->get_var($query);
		if(empty($product_page))
		{
			/*	Get product option in order to create front product page with the goode name	*/
			$product_options = get_option(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);

			/*	Create the default page for product in front	*/
			// $products_page_id = wp_insert_post(array(
				// 'post_title' 	=>	__('Products page', 'wpshop'),
				// 'post_type' 	=>	'page',
				// 'post_name'		=>	$product_options['product_slug'],
				// 'comment_status'=>	'closed',
				// 'ping_status' 	=>	'closed',
				// 'post_content' 	=>	'[wpshop_product_page]',
				// 'post_status' 	=>	'publish',
				// 'post_author' 	=>	1,
				// 'menu_order'	=>	0
			// ));
			$page_creation = true;
		}
		if($page_creation)
		{
			wp_cache_delete('all_page_ids', 'pages');
			$wp_rewrite->flush_rules();
		}

		/*	Create the default database tables	*/
		if(is_array($wpshop_db_table)){
			foreach($wpshop_db_table as $table_type => $table_definition){
				if(isset($table_definition['main_definition'])){
					$wpdb->query($table_definition['main_definition']);
				}
			}

			/*	Insert default content into created database	*/
			include(WPSHOP_LIBRAIRIES_DIR . 'db/db_data_definition.php');
			$eavEntitiesQuery = "  ";
			foreach($initialEavData['entities'] as $entity => $entityDetails){
				$eavEntitiesQuery .= "('', 'valid', NOW(), '" . $entity . "', '" . $entityDetails['dbTable'] . "'), ";
			}
			$eavEntitiesQuery = trim(substr($eavEntitiesQuery, 0, -2));
			if($eavEntitiesQuery != ''){/*	If there are entities to create	*/
				/*	Create entities	*/
				$query = $wpdb->prepare(
				"INSERT INTO " . WPSHOP_DBT_ENTITIES . " (id, status, creation_date, code, entity_table) 
					VALUES 
				" . $eavEntitiesQuery);
				$wpdb->query($query);

				/*	Create attributes	*/
				$attributeQuery = "  ";
				reset($initialEavData['entities']);
				foreach($initialEavData['entities'] as $entity => $entityDetails){
					/*	Create the attribute query	*/
					foreach($entityDetails['attributes'] as $attributeCode => $attributeDefinition){
						$attributeFieldsToSet = array('id' => '', 'status' => 'valid', 'creation_date' => date('Y:m:d H:i:s'), 'entity_id' => wpshop_entities::get_entity_identifier_from_code($entity), 'code' => $attributeCode);

						foreach($attributeDefinition as $attributeField => $attributeFieldValue){
							$attributeFieldsToSet[$attributeField] = $attributeFieldValue;
						}
						$wpdb->insert(WPSHOP_DBT_ATTRIBUTE, $attributeFieldsToSet);
					}
				}

				/*	Get the entities listing	*/
				$entitiesList = wpshop_entities::get_entity();
				/*	Attribute set	*/
				$attributeSetQuery = "";
				foreach($entitiesList as $entity){
					$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE entity_id = %d AND name = %s", $entity->id, 'Default');
					$attribute_set_id = $wpdb->get_var($query);
					if($attribute_set_id <= 0){
						$attributeSetQuery .= "('', 'valid', NOW(), '0', '" . $entity->id . "', 'Default'), ";
					}
				}
				$attributeSetQuery = trim(substr($attributeSetQuery, 0, -2));
				if($attributeSetQuery != ''){
					$query = $wpdb->prepare(
					"INSERT INTO " . WPSHOP_DBT_ATTRIBUTE_SET . " (id, status, creation_date, position, entity_id, name) 
						VALUES
					" . $attributeSetQuery);
					$wpdb->query($query);
				}

				/*	Attribute group	*/
					/*	Get the value to assign	*/
					$query = $wpdb->prepare("SELECT id, name, entity_id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE status = 'valid'");
					$wpshopAttributeSetList = $wpdb->get_results($query);
					$attributeGroupSubQuery = "  ";
					foreach($wpshopAttributeSetList as $wpshopAttributeSet){
						$i = 1;
						foreach($initialEavData['attributeGroup'] as $attributeGroupIndex => $attributeDetails)
						{
							$attributeGroupSubQuery .= "('', 'valid', '" . $wpshopAttributeSet->id . "', '" . $i . "', NOW(), '" . $attributeDetails['code'] . "', '" . $attributeDetails['name'] . "'), ";
							$i++;
						}
					}
				$attributeGroupSubQuery = trim(substr($attributeGroupSubQuery, 0, -2));
				$query = $wpdb->prepare(
				"INSERT INTO " . WPSHOP_DBT_ATTRIBUTE_GROUP . " (id, status, attribute_set_id, position, creation_date, code, name) 
					VALUES " . $attributeGroupSubQuery);
				$wpdb->query($query);

				/*	Attribute group's details	*/
				$attribute_set_group_details_query = "  ";
				$position = 1;
				$lastAttributeGroupIndex = 'x';
				foreach($initialEavData['attributeGroup'] as $attributeGroupIndex => $attributeDetails){
					if($lastAttributeGroupIndex != $attributeGroupIndex){
						$position = 1;
						$lastAttributeGroupIndex = $attributeGroupIndex;
					}
					/*	Get the value to assign	*/
					$query = $wpdb->prepare(
						"SELECT ATTRIBUTE.entity_id, ATTRIBUTE.id AS attribute_id
						FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE
						WHERE code IN ('" . implode("','", $attributeDetails['details']) . "')");
					$wpshopAttributeGroupName = $wpdb->get_results($query);
					foreach($wpshopAttributeGroupName as $wpshopElementIdGroupName){
						$query = $wpdb->prepare(
							"SELECT ATTRIBUTE_SET.id AS attribute_set_id
							FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTRIBUTE_SET
							WHERE ATTRIBUTE_SET.entity_id = %d", $wpshopElementIdGroupName->entity_id);
						$wpshopAttributeSetId = $wpdb->get_row($query);
						$query = $wpdb->prepare(
							"SELECT ATTRIBUTE_GROUP.id AS attribute_group_id
							FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
							WHERE ATTRIBUTE_GROUP.attribute_set_id = %d
								AND ATTRIBUTE_GROUP.code = '" . wpshop_tools::slugify($attributeDetails['code'], array('noAccent', 'noSpaces', 'lowerCase')) . "' ", $wpshopAttributeSetId->attribute_set_id);
						$wpshopAttributeGroupId = $wpdb->get_row($query);
						
						$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d AND attribute_id = %d", $wpshopElementIdGroupName->entity_id, $wpshopAttributeSetId->attribute_set_id, $wpshopAttributeGroupId->attribute_group_id, $wpshopElementIdGroupName->attribute_id);
						$attribute_set_detail_id = $wpdb->get_var($query);
						if($attribute_set_detail_id <= 0){
							$attribute_set_group_details_query .=	"('', 'valid', NOW(), '" . $wpshopElementIdGroupName->entity_id . "', '" . $wpshopAttributeSetId->attribute_set_id . "', '" . $wpshopAttributeGroupId->attribute_group_id . "', '" . $wpshopElementIdGroupName->attribute_id . "', '" . $position . "'), ";
							$position++;
						}
					}
				}
				$attribute_set_group_details_query = trim(substr($attribute_set_group_details_query, 0, -2));
				if($attribute_set_group_details_query != ''){
					$query = $wpdb->prepare(
					"INSERT INTO " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " (id, status, creation_date, entity_type_id, attribute_set_id, attribute_group_id, attribute_id, position) 
						VALUES " . $attribute_set_group_details_query);
					$wpdb->query($query);
				}
			}

			/*	Insert Default datas for other table than eav model	*/
			foreach($initialData as $table => $table_default_content){
				switch($table){
					case WPSHOP_DBT_ATTRIBUTE_UNIT:
					{
						foreach($table_default_content as $unit => $unit_definition){
							$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT . " WHERE unit = %s AND name = %s", $unit, $unit_definition['name']);
							$unit_id = $wpdb->get_var($query);
							if($unit_id <= 0){
								$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_UNIT, array('id' => '', 'status' => 'valid', 'creation_date' => date('Y-m-d H:i:s'), 'unit' => $unit, 'name' => $unit_definition['name']));
							}
						}
					}
					break;
				}
			}
		}
	}

	/**
	*	Method called when plugin is loaded for database update
	*/
	function update_wpshop(){
		global $wpdb, $wpshop_db_table_version, $wpshop_db_table_additionnal_field, $wpshop_data_version;
		$do_version_update = false;

		/*	Get current plugin version	*/
		$current_db_version = get_option('wpshop_db_options', 0);
		$current_db_version = ($current_db_version['db_version'] + 1);

		/*	Create the new database tables	*/
		if(is_array($wpshop_db_table_version[$current_db_version])){
			$do_version_update = true;

			foreach($wpshop_db_table_version[$current_db_version] as $table_type => $table_definition){
				if(isset($table_definition['main_definition'])){
					$wpdb->query($table_definition['main_definition']);
				}
			}
		}


		/*	Add field to existing table	*/
		if(is_array($wpshop_db_table_additionnal_field[$current_db_version])){
			$do_version_update = true;
			$query = "";

			/*	New database table creation	*/
			foreach($wpshop_db_table_additionnal_field[$current_db_version] as $table => $table_definition){
				foreach($table_definition as $operation_type => $field){
					if($operation_type == 'ADD'){
						foreach($field as $field_name => $field_definition){
							$query = "ALTER TABLE " . $table . " " . $operation_type . " " . $field_name . " " . $field_definition['type'] . "(" . $field_definition['length'] . ") " . $field_definition['option'] . ";";
							$wpdb->query($query);
						}
					}
					elseif($operation_type == 'DROP'){
						foreach($field as $field_name){
							$query = "ALTER TABLE " . $table . " " . $operation_type . " " . $field_name . ";";
							$wpdb->query($query);
						}
					}
					elseif($operation_type == 'CHANGE'){
						foreach($field as $field_name => $field_definition){
							$query = "ALTER TABLE " . $table . " " . $operation_type . " " . $field_definition['old_field_name'] . " " . $field_name . " " . $field_definition['type'] . "(" . $field_definition['length'] . ") " . $field_definition['option'] . ";";
							$wpdb->query($query);
						}
					}
				}
			}
		}


		/*	New database content insertion	*/
		if(is_array($wpshop_data_version[$current_db_version])){
			unset($query);
			foreach($wpshop_data_version[$current_db_version] as $operation_type => $operation_type_details){
				foreach($operation_type_details as $table_name => $datas){
					foreach($datas as $fields){
						unset($datas_to_add);$datas_to_add = array();
						if($operation_type == 'INSERT'){
							foreach($fields as $field_name => $field_content){
								$datas_to_add[$field_name] = $field_content;
							}
							$wpdb->insert($table_name, $datas_to_add);
						}
						elseif($operation_type == 'UPDATE'){
							unset($datas_to_test);$datas_to_test = array();
							$where_condition = '';
							foreach($fields as $field_name => $field_content){
								if($field_name != 'where_condition'){
									$datas_to_add[$field_name] = $field_content;
								}
								else{
									foreach($field_content as $condition){
										foreach($condition as $condition_field => $condition_value){
											$datas_to_test[$condition_field] = $condition_value;
										}
									}
								}
							}
							$wpdb->update($table_name, $datas_to_add, $datas_to_test);
						}
					}
				}
			}
		}


		/*	Update the db version option value	*/
		if($do_version_update){
			$wpshop_db_options['db_version'] = $current_db_version;
			update_option('wpshop_db_options', $wpshop_db_options);
		}
	}

	/**
	*	Methid called when deactivating the plugin
	*	@see register_deactivation_hook()
	*/
	function uninstall_wpshop(){
		global $wpdb;

		/*	Unset administrator permission	*/
		$adminRole = get_role('administrator');
		foreach($adminRole->capabilities as $capabilityName => $capability){
			if(substr($capabilityName, 0, 7) == 'wpshop_'){
				if($adminRole->has_cap($capabilityName)){
					$adminRole->remove_cap($capabilityName);
				}
			}
		}

	}

}