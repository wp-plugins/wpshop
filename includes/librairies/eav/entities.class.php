<?php
/**
* Define the different method to manage entities
* 
*	Define the different method and variable used to manage entities
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/


/**
* Define the different method to manage entities
* @package wpshop
* @subpackage librairies
*/
class wpshop_entities
{
	/**
	*	Define the database table used in the current class
	*/
	const dbTable = WPSHOP_DBT_ENTITIES;	

	/**
	*	Get the database table of the current class
	*
	*	@return string The table of the class
	*/
	function getDbTable()
	{
		return self::dbTable;
	}

	/**
	*	Return entities. Get a specific entity if first parameter is not empty
	*
	*	@param integer optionnal $entityId The entity identifier we want to get
	*	@param string optionnal $entityStatus The entity status we want to get
	*
	*	@return object $entities A wordpress object with the result. Could be a collection or a single result regarding the first function parameter
	*/
	function get_entity($entityId = '', $entityStatus = "'valid'"){
		global $wpdb;
		$entities = array();
		$moreQuery = "";

		if($entityId != ''){
			$moreQuery = "
			AND ENTITY.id = '" . $entityId . "' ";
		}

		$query = $wpdb->prepare(
			"SELECT * 
			FROM " . self::getDbTable() . " AS ENTITY
			WHERE ENTITY.status IN (" . $entityStatus . ") " . $moreQuery);

		/*	Get the query result regarding on the function parameters. If there must be only one result or a collection	*/
		if($entityId == '')
		{
			$entities = $wpdb->get_results($query);
		}
		else
		{
			$entities = $wpdb->get_row($query);
		}

		return $entities;
	}

	/**
	*	Return the entity id from a given antity code
	*
	*	@return integer $entity->id The entity identifier
	*/
	function get_entity_identifier_from_code($code)
	{
		global $wpdb;

		$query = $wpdb->prepare("SELECT id FROM " . self::getDbTable() . " WHERE code = %s", $code);
		$entity = $wpdb->get_row($query);

		return $entity->id;
	}

}