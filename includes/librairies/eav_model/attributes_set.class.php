<?php
/**
* Define the different method to manage attributes set
* 
*	Define the different method and variable used to manage attributes set
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/


/**
* Define the different method to manage attributes set
* @package wpshop
* @subpackage librairies
*/
class wpshop_attributes_set
{
	/**
	*	Define the database table used in the current class
	*/
	const dbTable = WPSHOP_DBT_ATTRIBUTE_SET;
	/**
	*	Define the url listing slug used in the current class
	*/
	const urlSlugListing = WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING;
	/**
	*	Define the url edition slug used in the current class
	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_ATTRIBUTE_SET_EDITION;
	/**
	*	Define the current entity code
	*/
	const currentPageCode = 'attribute_set';
	/**
	*	Define the page title
	*/
	const pageTitle = 'Groupes d\'attributs';
	/**
	*	Define the page title when adding an attribute
	*/
	const pageAddingTitle = 'Ajouter un groupe d\'attributs';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageEditingTitle = '&Eacute;diter le groupe d\'attribut "%s (%s)"';

	/**
	*	Define the path to page main icon
	*/
	public $pageIcon = '';
	/**
	*	Define the message to output after an action
	*/
	public $pageMessage = '';

	/**
	*	Get the url listing slug of the current class
	*
	*	@return string The table of the class
	*/
	function setMessage($message)
	{
		$this->pageMessage = $message;
	}
	/**
	*	Get the url listing slug of the current class
	*
	*	@return string The table of the class
	*/
	function getListingSlug()
	{
		return self::urlSlugListing;
	}
	/**
	*	Get the url edition slug of the current class
	*
	*	@return string The table of the class
	*/
	function getEditionSlug()
	{
		return self::urlSlugEdition;
	}
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
	*	Define the title of the page 
	*
	*	@return string $title The title of the page looking at the environnement
	*/
	function pageTitle()
	{
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : '';
		$objectInEdition = isset($_REQUEST['id']) ? wpshop_tools::varSanitizer($_REQUEST['id']) : '';

		$title = __(self::pageTitle, 'wpshop' );
		if($action != '')
		{
			if($action == 'edit')
			{
				$editedItem = self::getElement($objectInEdition);
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), $editedItem->name, $editedItem->entity);
			}
			elseif($action == 'add')
			{
				$title = __(self::pageAddingTitle, 'wpshop');
			}
		}
		return $title;
	}


	/**
	*	Define the different message and action after an action is send through the element interface
	*/
	function elementAction()
	{
		global $wpdb;

		$pageMessage = $actionResult = '';

		/*	Start definition of output message when action is doing on another page	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/****************************************************************************/
		$saveditem = isset($_REQUEST['saveditem']) ? wpshop_tools::varSanitizer($_REQUEST['saveditem']) : '';
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		if(($action != '') && ($action == 'saveok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem);
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('L\'enregistrement de %s s\'est d&eacute;roul&eacute; avec succ&eacute;s', 'wpshop'), '<span class="bold" >' . $editedElement->name . '</span>');
		}
		elseif(($action != '') && ($action == 'deleteok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem, "'deleted'");
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s a &eacute;t&eacute; supprim&eacute;e avec succ&eacute;s', 'wpshop'), '<span class="bold" >' . $editedElement->name . '</span>');
		}

		/*	Define the database operation type from action launched by the user	 */
		/*************************			GENERIC				****************************/
		/*************************************************************************/
		$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
		$id = isset($_REQUEST[self::getDbTable()]['id']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['id']) : '';
		
		if(($pageAction != '') && (($pageAction == 'edit') || ($pageAction == 'editandcontinue') || ($pageAction == 'delete')))
		{
			if(current_user_can('wpshop_edit_attribute_set'))
			{
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				if($pageAction == 'delete')
				{
					if(current_user_can('wpshop_delete_attribute_set'))
					{
						$_REQUEST[self::getDbTable()]['status'] = 'deleted';
					}
					else
					{
						$actionResult = 'userNotAllowedForActionDelete';
					}
				}
				$actionResult = wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());
			}
			else
			{
				$actionResult = 'userNotAllowedForActionEdit';
			}
		}
		elseif(($pageAction != '') && (($pageAction == 'delete')))
		{
			if(current_user_can('wpshop_delete_attribute_set'))
			{
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				$_REQUEST[self::getDbTable()]['status'] = 'deleted';
				$actionResult = wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());
			}
			else
			{
				$actionResult = 'userNotAllowedForActionDelete';
			}
		}
		elseif(($pageAction != '') && (($pageAction == 'save') || ($pageAction == 'saveandcontinue') || ($pageAction == 'add')))
		{
			if(current_user_can('wpshop_add_attribute_set'))
			{
				$_REQUEST[self::getDbTable()]['creation_date'] = date('Y-m-d H:i:s');
				$actionResult = wpshop_database::save($_REQUEST[self::getDbTable()], self::getDbTable());
				$id = $wpdb->insert_id;
			}
			else
			{
				$actionResult = 'userNotAllowedForActionAdd';
			}
		}

		
		/*	When an action is launched and there is a result message	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/************		CHANGE ERROR MESSAGE FOR SPECIFIC CASE					*************/
		/****************************************************************************/
		if($actionResult != '')
		{
			$elementIdentifierForMessage = '<span class="bold" >' . $_REQUEST[self::getDbTable()]['name'] . '</span>';
			if($actionResult == 'error')
			{/*	CHANGE HERE FOR SPECIFIC CASE	*/
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . sprintf(__('Une erreur est survenue lors de l\'enregistrement de %s', 'wpshop'), $elementIdentifierForMessage);
				if(wpshop_DEBUG)
				{
					$pageMessage .= '<br/>' . $wpdb->last_error;
				}
			}
			elseif(($actionResult == 'done') || ($actionResult == 'nothingToUpdate'))
			{
				/*****************************************************************************************************************/
				/*************************			CHANGE FOR SPECIFIC ACTION FOR CURRENT ELEMENT				****************************/
				/*****************************************************************************************************************/

				/*************************			GENERIC				****************************/
				/*************************************************************************/
				$pageMessage .= '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('L\'enregistrement de %s s\'est d&eacute;roul&eacute; avec succ&eacute;s', 'wpshop'), $elementIdentifierForMessage);
				if(($pageAction == 'edit') || ($pageAction == 'save'))
				{
					wp_redirect(admin_url('admin.php?page=' . self::getListingSlug() . "&action=saveok&saveditem=" . $id));
				}
				elseif($pageAction == 'add')
				{
					wp_redirect(admin_url('admin.php?page=' . self::getListingSlug() . "&action=edit&id=" . $id));
				}
				elseif($pageAction == 'delete')
				{
					wp_redirect(admin_url('admin.php?page=' . self::getListingSlug() . "&action=deleteok&saveditem=" . $id));
				}
			}
			elseif(($actionResult == 'userNotAllowedForActionEdit') || ($actionResult == 'userNotAllowedForActionAdd') || ($actionResult == 'userNotAllowedForActionDelete'))
			{
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . __('Vous n\'avez pas les droits n&eacute;cessaire pour effectuer cette action.', 'wpshop');
			}
		}

		self::setMessage($pageMessage);
	}
	/**
	*	Return the list page content, containing the table that present the item list
	*
	*	@return string $listItemOutput The html code that output the item list
	*/
	function elementList()
	{
		$listItemOutput = '';

		/*	Start the table definition	*/
		$tableId = self::getDbTable() . '_list';
		$tableSummary = __('Existing attribute set listing', 'wpshop');
		$tableTitles = array();
		$tableTitles[] = __('Nom du groupe d\'attributs', 'wpshop');
		$tableTitles[] = __('Entit&eacute;', 'wpshop');
		$tableTitles[] = __('Statut', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_name_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_entity_id_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_status_column';

		$line = 0;
		$elementList = self::getElement();
		foreach($elementList as $element)
		{
			$tableRowsId[$line] = self::getDbTable() . '_' . $element->id;

			$elementLabel = $element->name;
			$subRowActions = '';
			if(current_user_can('wpshop_edit_attribute_set'))
			{
				$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $element->id);
				$subRowActions .= '
	<a href="' . $editAction . '" >' . __('Modifier', 'wpshop') . '</a>';
				$elementLabel = '<a href="' . $editAction . '" >' . $element->name  . '</a>';
			}
			elseif(current_user_can('wpshop_view_attribute_set_details'))
			{
				$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $element->id);
				$subRowActions .= '
	<a href="' . $editAction . '" >' . __('Voir', 'wpshop') . '</a>';
				$elementLabel = '<a href="' . $editAction . '" >' . $element->name  . '</a>';
			}
			if(current_user_can('wpshop_delete_attribute_set'))
			{
				if($subRowActions != '')
				{
					$subRowActions .= '&nbsp;|&nbsp;';
				}
				$subRowActions .= '
	<a href="' . admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=delete&amp;id=' . $element->id). '" >' . __('Supprimer', 'wpshop') . '</a>';
			}
			$rowActions = '
<div id="rowAction' . $element->id . '" class="wpshopRowAction" >' . $subRowActions . '
</div>';

			unset($tableRowValue);
			$tableRowValue[] = array('class' => 'attribute_set_label_cell', 'value' => $elementLabel . $rowActions);
			$tableRowValue[] = array('class' => 'attribute_set_name_cell', 'value' => __($element->entity, 'wpshop'));
			$tableRowValue[] = array('class' => 'attribute_set_status_cell', 'value' => __($element->status, 'wpshop'));
			$tableRows[] = $tableRowValue;

			$line++;
		}
		$listItemOutput = wpshop_display::getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, true);

		return $listItemOutput;
	}
	/**
	*	Return the page content to add a new item
	*
	*	@return string The html code that output the interface for adding a nem item
	*/
	function elementEdition($itemToEdit = '')
	{
		$dbFieldList = wpshop_database::fields_to_input(self::getDbTable());
		$moreTabs = $moreTabsContent = '';

		$editedItem = '';
		if($itemToEdit != '')
		{
			$editedItem = self::getElement($itemToEdit);
		}

		$the_form_content_hidden = $the_form_general_content = '';
		foreach($dbFieldList as $input_key => $input_def)
		{
			$input_name = $input_def['name'];
			$input_value = $input_def['value'];
			$input_type = $input_def['type'];

			$attributeAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
			$attributeFormValue = isset($_REQUEST[self::getDbTable()][$input_name]) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()][$input_name]) : '';
			$currentFieldValue = $input_value;
			if(is_object($editedItem))
			{
				$currentFieldValue = $editedItem->$input_name;
			}
			elseif(($attributeAction != '') && ($attributeFormValue != '') && ($input_name != 'icon_path'))
			{
				$currentFieldValue = $attributeFormValue;
			}

			if($input_name == 'entity_id')
			{
				$input_def['name'] = $input_name;
				$input_def['possible_value'] = wpshop_entities::getEntity();
				$input_def['value'] = $currentFieldValue;
				$input_def['type'] = 'select';
				$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());
			}
			else
			{
				$input_def['value'] = $currentFieldValue;
				$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());
			}

			if($input_type != 'hidden')
			{
				$label = 'for="' . $input_name . '"';
				if(($input_type == 'radio') || ($input_type == 'checkbox'))
				{
					$label = '';
				}
				$input = '
		<div class="clear" >
			<div class="wpshop_form_label wpshop_' . self::currentPageCode . '_' . $input_name . '_label alignleft" >
				<label ' . $label . ' >' . __($input_name, 'wpshop') . '</label>
			</div>
			<div class="wpshop_form_input wpshop_' . self::currentPageCode . '_' . $input_name . '_input alignleft" >
				' . $the_input . '
			</div>
		</div>';
				$the_form_general_content .= $input;
			}
			else
			{
				$the_form_content_hidden .= '
		' . $the_input;
			}
		}

		if(is_object($editedItem))
		{
			$attributeSetDetailsContent = '';
			$attributeSetDetails = self::getAttributeSetDetails($itemToEdit);
			foreach($attributeSetDetails as $attributeSetDetailsGroup)
			{
				$attributeSetDetailsContent .= '
		<div id="attribute_group_' . wpshop_tools::slugify($attributeSetDetailsGroup['name'], array('noAccent')) . '" >
			' . $attributeSetDetailsGroup['name'];
				if(is_array($attributeSetDetailsGroup['attribut']) && count($attributeSetDetailsGroup['attribut']) >= 1)
				{
					$attributeSetDetailsContent .= '
			<ul id="attribute_group_' . wpshop_tools::slugify($attributeSetDetailsGroup['name'], array('noAccent')) . '_details" class="attributeGroupDetails" >';
					ksort($attributeSetDetailsGroup['attribut']);
					foreach($attributeSetDetailsGroup['attribut'] as $attributInGroup)
					{
						$attributeSetDetailsContent .= '
					<li class="ui-state-default" >' . $attributInGroup->frontend_label . '</li>';
					}
					$attributeSetDetailsContent .= '
			</ul>';
				}
				$attributeSetDetailsContent .= '
		</div>';
			}
			$moreTabs .= '<li><a href="#wpshop_' . self::currentPageCode . '_details_main_infos_form" >' . __('Attributs du groupe', 'wpshop') . '</a></li>';
			$moreTabsContent .= '
	<div id="wpshop_' . self::currentPageCode . '_details_main_infos_form" >' . $attributeSetDetailsContent . '
	</div>';
		}

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="" enctype="multipart/form-data" >
' . wpshop_form::form_input(self::getDbTable() . '_action', self::getDbTable() . '_action', (isset($_REQUEST['action']) && ($_REQUEST['action'] != '') ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'save') , 'hidden') . '
' . wpshop_form::form_input(self::getDbTable() . '_form_has_modification', self::getDbTable() . '_form_has_modification', 'no' , 'hidden') . '
<div id="wpshopFormManagementContainer" >
	<ul>
		<li><a href="#wpshop_' . self::currentPageCode . '_main_infos_form" >' . __('G&eacute;n&eacute;ral', 'wpshop') . '</a></li>' . $moreTabs . '
	</ul>' . $the_form_content_hidden .'
	<div id="wpshop_' . self::currentPageCode . '_main_infos_form" >' . $the_form_general_content . '
	</div>' . $moreTabsContent . '
</div>
</form>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		wpshopMainInterface("' . self::getDbTable() . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir quitter cette page? Vous perdrez toutes les modification que vous aurez effectu&eacute;es', 'wpshop') . '");

		wpshop("#delete").click(function(){
			wpshop("#' . self::getDbTable() . '_action").val("delete");
			deleteAttributeSet();
		});
		if(wpshop("#' . self::getDbTable() . '_action").val() == "delete"){
			deleteAttributeSet();
		}
		function deleteAttributeSet(){
			if(confirm(wpshopConvertAccentTojs("' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce groupe d\'attributs?', 'wpshop') . '"))){
				wpshop("#' . self::getDbTable() . '_form").submit();
			}
			else{
				wpshop("#' . self::getDbTable() . '_action").val("edit");
			}
		}
	});
</script>';

		return $the_form;
	}
	/**
	*	Return the different button to save the item currently being added or edited
	*
	*	@return string $currentPageButton The html output code with the different button to add to the interface
	*/
	function getPageFormButton()
	{
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$currentPageButton = '';

		if($action == 'add')
		{
			if(current_user_can('wpshop_add_attribute_set'))
			{
				$currentPageButton .= '<input type="button" class="button-primary" id="add" name="add" value="' . __('Ajouter', 'wpshop') . '" />';
			}
		}
		elseif(current_user_can('wpshop_edit_attribute_set'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="save" name="save" value="' . __('Enregistrer', 'wpshop') . '" /><input type="button" class="button-primary" id="saveandcontinue" name="saveandcontinue" value="' . __('Enregistrer et continuer l\'&eacute;dition', 'wpshop') . '" />';
		}
		if(current_user_can('wpshop_delete_attribute_set') && ($action != 'add'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="delete" name="delete" value="' . __('Supprimer', 'wpshop') . '" />';
		}

		$currentPageButton .= '<h2 class="alignright cancelButton" ><a href="' . admin_url('admin.php?page=' . self::getListingSlug()) . '" class="button add-new-h2" >' . __('Retour', 'wpshop') . '</a></h2>';

		return $currentPageButton;
	}

	/**
	*	Get the existing element list into database
	*
	*	@param integer $elementId optionnal The element identifier we want to get. If not specify the entire list will be returned
	*	@param string $elementStatus optionnal The status of element to get into database. Default is set to valid element
	*
	*	@return object $elements A wordpress database object containing the element list
	*/
	function getElement($elementId = '', $elementStatus = "'valid', 'moderated'", $whatToSearch = 'id')
	{
		global $wpdb;
		$elements = array();
		$moreQuery = "";

		if($elementId != '')
		{
			switch($whatToSearch)
			{
				case 'entity_code':
					$moreQuery = "
			AND ENTITIES.code = '" . $elementId . "' ";
				break;
				default:
					$moreQuery = "
			AND ATTRIBUTE_SET.id = '" . $elementId . "' ";
				break;
			}
		}

		$query = $wpdb->prepare(
		"SELECT ATTRIBUTE_SET.*, ENTITIES.code as entity
		FROM " . self::getDbTable() . " AS ATTRIBUTE_SET
			INNER JOIN " . WPSHOP_DBT_ENTITIES . " AS ENTITIES ON (ENTITIES.id = ATTRIBUTE_SET.entity_id)
		WHERE ATTRIBUTE_SET.status IN (".$elementStatus.") " . $moreQuery
		);

		/*	Get the query result regarding on the function parameters. If there must be only one result or a collection	*/
		if($elementId == '')
		{
			$elements = $wpdb->get_results($query);
		}
		else
		{
			$elements = $wpdb->get_row($query);
		}

		return $elements;
	}


	/**
	*	Get the complete details about attributes sets
	*
	*	@param integer $attributeSetId The attribute set identifier we want to get the details for
	*	@param string $attributeSetStatus optionnal The attribute set status. Allows to define if we want all attribute sets or a deleted or valid and so on
	*	
	*	@return array $attributeSetDetailsGroups The List of attribute and attribute groups for the given attribute set
	*/
	function getAttributeSetDetails($attributeSetId, $attributeSetStatus = "'valid', 'moderated'")
	{
		global $wpdb;
		$attributeSetDetailsGroups = '';

		$query = $wpdb->prepare(
			"SELECT ATTRIBUTE_GROUP.id AS attr_group_id, ATTRIBUTE_GROUP.code AS attr_group_code, ATTRIBUTE_GROUP.position AS attr_group_position, ATTRIBUTE_GROUP.name AS attr_group_name, 
				ATTRIBUTE.*, ATTRIBUTE_DETAILS.position AS attr_position_in_group
			FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
				INNER JOIN " . self::getDbTable() . " AS ATTRIBUTE_SET ON (ATTRIBUTE_SET.id = ATTRIBUTE_GROUP.attribute_set_id)
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTRIBUTE_DETAILS ON ((ATTRIBUTE_DETAILS.attribute_group_id = ATTRIBUTE_GROUP.id) AND (ATTRIBUTE_DETAILS.attribute_set_id = ATTRIBUTE_SET.id))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE ON (ATTRIBUTE.id = ATTRIBUTE_DETAILS.attribute_id)
			WHERE ATTRIBUTE_SET.id = %d
				AND ATTRIBUTE_SET.status IN (" . $attributeSetStatus . ") 
				AND ATTRIBUTE_GROUP.status IN (" . $attributeSetStatus . ") 
			ORDER BY ATTRIBUTE_GROUP.position",
			$attributeSetId);
		$attributeSetDetails = $wpdb->get_results($query);

		foreach($attributeSetDetails as $attributeGroup)
		{
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['code'] = $attributeGroup->attr_group_code;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['name'] = $attributeGroup->attr_group_name;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['attribut'][$attributeGroup->attr_position_in_group] = $attributeGroup;
		}

		return $attributeSetDetailsGroups;
	}

	/**
	*	Get the existing attribute set for an entity
	*
	*	@param integer $entityId The entity identifier we want to get the entity set list for
	*
	*	@return object $entitySets The entity sets list for the given entity
	*/
	function getAttributeListForEntity($entityId)
	{
		global $wpdb;
		$entitySetList = '';

		$query = $wpdb->prepare(
			"SELECT id, name
			FROM " . self::getDbTable() . "
			WHERE status = 'valid'
				AND entity_id = %d",
			$entityId);
		$entitySetList = $wpdb->get_results($query);

		return $entitySetList;
	}

}