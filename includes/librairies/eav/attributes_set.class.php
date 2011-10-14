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
	const urlSlugEdition = WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING;
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
	
	function wpshop_att_group_func($atts) {
		global $wpdb;
		$query = '
		SELECT '.WPSHOP_DBT_ATTRIBUTE.'.frontend_label, '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.value AS value_decimal, '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.value AS value_datetime, '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.value AS value_integer, '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.value AS value_text, '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.'.value AS value_varchar, '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.unit AS unit
		FROM '.WPSHOP_DBT_ATTRIBUTE_DETAILS.'
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE.' ON '.WPSHOP_DBT_ATTRIBUTE_DETAILS.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_UNIT.' ON (
				'.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.unit_id 
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.unit_id
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.unit_id
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.unit_id
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.'.unit_id
			)
		WHERE 
			'.WPSHOP_DBT_ATTRIBUTE_DETAILS.'.status="valid"
			AND '.WPSHOP_DBT_ATTRIBUTE.'.status="valid"
			AND '.WPSHOP_DBT_ATTRIBUTE_DETAILS.'.attribute_group_id='.$atts['sid'].'
			AND (
				'.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.entity_id='.$atts['pid'].'
				OR '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.entity_id='.$atts['pid'].'
				OR '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.entity_id='.$atts['pid'].'
				OR '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.entity_id='.$atts['pid'].'
				OR '.WPSHOP_DBT_ATTRIBUTE.'_value_varchar.entity_id='.$atts['pid'].'
			)
		';
		$data = $wpdb->get_results($query);
		foreach($data as $d) {
			echo '<strong>'.__($d->frontend_label, 'wpshop').'</strong> : '.$d->value_decimal.$d->value_datetime.$d->value_integer.$d->value_text.$d->value_varchar.' ('.$d->unit.')<br />';
		}
	}


	/**
	*	Define the different message and action after an action is send through the element interface
	*/
	function elementAction()
	{
		global $wpdb, $initialEavData;

		$pageMessage = $actionResult = '';

		/*	Start definition of output message when action is doing on another page	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/****************************************************************************/
		$saveditem = isset($_REQUEST['saveditem']) ? wpshop_tools::varSanitizer($_REQUEST['saveditem']) : '';
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		if(($action != '') && ($action == 'saveok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem);
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), '<span class="bold" >' . $editedElement->name . '</span>');
		}
		elseif(($action != '') && ($action == 'deleteok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem, "'deleted'");
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully deleted', 'wpshop'), '<span class="bold" >' . $editedElement->name . '</span>');
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
				/*	Insert the default group for the set	*/
				include(WPSHOP_LIBRAIRIES_DIR . 'db/db_data_definition.php');
				wpshop_database::save(array('id' => '', 'status' => 'valid', 'attribute_set_id' => $id, 'position' => 1, 'creation_date' => 'NOW()', 'code' => $initialEavData['attributeGroup'][0]['code'], 'name' => $initialEavData['attributeGroup'][0]['name']), WPSHOP_DBT_ATTRIBUTE_GROUP);
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
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . sprintf(__('An error occured while saving %s', 'wpshop'), $elementIdentifierForMessage);
				if(WPSHOP_DEBUG)
				{
					$pageMessage .= '<br/>' . $wpdb->last_error;
				}
			}
			elseif(($actionResult == 'done') || ($actionResult == 'nothingToUpdate'))
			{
				/*****************************************************************************************************************/
				/*************************			CHANGE FOR SPECIFIC ACTION FOR CURRENT ELEMENT				****************************/
				/*****************************************************************************************************************/
				if(isset($_REQUEST['attribute_group_order']) && ($_REQUEST['attribute_group_order'] != '')){
					foreach($_REQUEST['attribute_group_order'] as $groupIdentifier => $newOrder){
						$newOrder = str_replace('attribute_', '', $newOrder);
						$order = explode(',', $newOrder);
						$groupId = str_replace('newOrder', '', $groupIdentifier);
						$i = 1;
						foreach($order as $element){
							if($element != ''){
								if($groupId > 0){
									$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE attribute_id = %d AND status = %s AND attribute_set_id = %d", $element, 'valid', $id);
									$validElement = $wpdb->get_var($query);
									if(!empty($validElement)){
										$query = $wpdb->prepare("UPDATE " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " SET position = %d, attribute_group_id = %d, last_update_date = NOW() WHERE attribute_id = %d AND status = %s AND attribute_set_id = %d", $i, $groupId, $element, 'valid', $id);
									}
									else{
										$entityTypeId = 1;
										$query = $wpdb->prepare("INSERT INTO " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " (id, status, creation_date, entity_type_id, attribute_set_id, attribute_group_id, attribute_id, position) VALUES ('', 'valid', NOW(), %d, %d, %d, %d, %d)", $entityTypeId, $id, $groupId, $element, $i);
									}
								$wpdb->query($query);
								}
								else{
									$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'deleted', 'attribute_group_id' => $groupId, 'last_update_date' => 'NOW()'), array('attribute_id' => $element, 'status' => 'valid', 'attribute_set_id' => $id));
									// $query = $wpdb->prepare("UPDATE " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " SET status = 'deleted', attribute_group_id = %d, last_update_date = NOW() WHERE attribute_id = %d AND status = %s AND attribute_set_id = %d", $groupId, $element, 'valid', $id);
								}
								$i++;
							}
						}
					}
				}

				/*************************			GENERIC				****************************/
				/*************************************************************************/
				$pageMessage .= '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), $elementIdentifierForMessage);
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
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . __('You are not allowed to do this action', 'wpshop');
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
		$tableTitles[] = __('Attribute group name', 'wpshop');
		$tableTitles[] = __('Entity', 'wpshop');
		$tableTitles[] = __('Status', 'wpshop');
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
	<a href="' . $editAction . '" >' . __('Edit', 'wpshop') . '</a>';
				$elementLabel = '<a href="' . $editAction . '" >' . $element->name  . '</a>';
			}
			elseif(current_user_can('wpshop_view_attribute_set_details'))
			{
				$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $element->id);
				$subRowActions .= '
	<a href="' . $editAction . '" >' . __('View', 'wpshop') . '</a>';
				$elementLabel = '<a href="' . $editAction . '" >' . $element->name  . '</a>';
			}
			if(current_user_can('wpshop_delete_attribute_set'))
			{
				if($subRowActions != '')
				{
					$subRowActions .= '&nbsp;|&nbsp;';
				}
				$subRowActions .= '
	<a href="' . admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=delete&amp;id=' . $element->id). '" >' . __('Delete', 'wpshop') . '</a>';
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
		global $attribute_hidden_field;

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

			$attributeAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
			$attributeFormValue = isset($_REQUEST[self::getDbTable()][$input_name]) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()][$input_name]) : '';
			$currentFieldValue = $input_value;
			if(is_object($editedItem))
			{
				$currentFieldValue = $editedItem->$input_name;
			}
			elseif(($attributeAction != '') && ($attributeFormValue != ''))
			{
				$currentFieldValue = $attributeFormValue;
			}

			if(in_array($input_name, $attribute_hidden_field))
			{
				$input_def['type'] = 'hidden';
			}
			if($input_name == 'entity_id')
			{
				$input_def['name'] = $input_name;
				$input_def['possible_value'] = wpshop_entities::get_entity();
				$input_def['value'] = $currentFieldValue;
				$input_def['type'] = 'select';
				if(is_object($editedItem)){
					$input_def['option'] = ' disabled="disabled" ';
				}
				$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());
			}
			else
			{
				$input_def['value'] = $currentFieldValue;
				$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());
			}

			if($input_def['type'] != 'hidden')
			{
				$label = 'for="' . $input_name . '"';
				if(($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox'))
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
			$userCan = $userCanScript = '';
			if(current_user_can('wpshop_add_attribute_group'))
			{
				$userCan .= '<div id="attributeSetSection_New" class="wpshopHide" title="' . __('Add a new section to attibute group', 'wpshop') . '" ><input type="text" name="attributeSetSectionName" id="attributeSetSectionName" value="" /></div>
		<input type="button" class="button-primary" name="addAttributeSetSection" id="addAttributeSetSection" value="' . __('Add a section to the group', 'wpshop') . '" />';
				$userCanScript .= '
		wpshop("#addAttributeSetSection").click(function(){
			wpshop("#attributeSetSection_New").dialog("open");
		});
		wpshop("#attributeSetSection_New").dialog({
			autoOpen: false,
			modal:  true,
			buttons:{
				"' . __('Cancel', 'wpshop') . '": function(){
					wpshop(this).dialog("close");
				},
				"' . __('Save', 'wpshop') . '": function(){
					wpshop("#managementContainer").html(wpshop("#wpshopLoadingPicture").html());
					wpshop("#managementContainer").load(WPSHOP_AJAX_FILE_URL, {
						"post": "true",
						"elementCode": "' . self::currentPageCode . '",
						"action": "saveNewAttributeSetSection",
						"elementType": "attributeSetSection",
						"elementIdentifier": "' . $itemToEdit . '",
						"attributeSetSectionName": wpshop("#attributeSetSectionName").val()
					});
					wpshop(this).dialog("close");
				}
			},
			close: function(){
				wpshop("#attributeSetSectionName").val("");
			}
		});';
			}
			/*	Add action for the current user if this one is allowed to do this action	*/
			if(current_user_can('wpshop_edit_attribute_group'))
			{
				$userCan .= '<div id="attributeSetSection_Edit" class="wpshopHide" title="' . __('Edit section', 'wpshop') . '" ><input type="text" name="attributeSetSectionNameEdit" id="attributeSetSectionNameEdit" value="" /><input type="hidden" name="attributeSetSectionIdEdit" id="attributeSetSectionIdEdit" value="" /></div>';
			}

			$moreTabs .= '<li><a href="#wpshop_' . self::currentPageCode . '_details_main_infos_form" >' . __('Attribute group section details', 'wpshop') . '</a></li>';
			$moreTabsContent .= '
	<div id="wpshop_' . self::currentPageCode . '_details_main_infos_form" >
		' . $userCan . '
		<div id="managementContainer" >
			' . self::attributeSetDetailsManagement($itemToEdit) . '
		</div>
	</div>';
		}

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="" enctype="multipart/form-data" >
' . wpshop_form::form_input(self::getDbTable() . '_action', self::getDbTable() . '_action', (isset($_REQUEST['action']) && ($_REQUEST['action'] != '') ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'save') , 'hidden') . '
' . wpshop_form::form_input(self::getDbTable() . '_form_has_modification', self::getDbTable() . '_form_has_modification', 'no' , 'hidden') . '
<div id="wpshopFormManagementContainer" >
	<ul>
		<li><a href="#wpshop_' . self::currentPageCode . '_main_infos_form" >' . __('Main informations', 'wpshop') . '</a></li>' . $moreTabs . '
	</ul>' . $the_form_content_hidden . '
	<div id="wpshop_' . self::currentPageCode . '_main_infos_form" >' . $the_form_general_content . '
	</div>' . $moreTabsContent . '
</div>
</form>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		wpshopMainInterface("' . self::getDbTable() . '", "' . __('Are you sure you want to quit this page? You will loose all current modification', 'wpshop') . '", "' . __('Are you sure you want to delete this attributes group?', 'wpshop') . '");

		make_list_sortable("' . self::getDbTable() . '");

		' . $userCanScript . '
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
				$currentPageButton .= '<input type="button" class="button-primary" id="add" name="add" value="' . __('Add', 'wpshop') . '" />';
			}
		}
		elseif(current_user_can('wpshop_edit_attribute_set'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="save" name="save" value="' . __('Save', 'wpshop') . '" /><input type="button" class="button-primary" id="saveandcontinue" name="saveandcontinue" value="' . __('Save and continue edit', 'wpshop') . '" />';
		}
		if(current_user_can('wpshop_delete_attribute_set') && ($action != 'add'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="delete" name="delete" value="' . __('Delete', 'wpshop') . '" />';
		}

		$currentPageButton .= '<h2 class="alignright cancelButton" ><a href="' . admin_url('admin.php?page=' . self::getListingSlug()) . '" class="button add-new-h2" >' . __('Back', 'wpshop') . '</a></h2>';

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
	function getElement($elementId = '', $elementStatus = "'valid', 'moderated'", $whatToSearch = 'id', $resultList = '')
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
		if(($elementId == '') || ($resultList == 'all'))
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
	*	Display inteface allowing to manage the attribute set and group details
	*
	*	@param object $atributeSetId The element's identifier we have to manage the details for
	*
	*	@return string $attributeSetDetailsManagement The html output of management interface
	*/
	function attributeSetDetailsManagement($attributeSetId = '')
	{
		global $validAttributeList;

		$attributeSetDetailsManagement = $userCan = $userCanScript = '';

		/*	Display 	*/
		$attributeSetDetailsManagement .= '';

		/*	Add action for the current user if this one is allowed to do this action	*/
		if(current_user_can('wpshop_edit_attribute_group'))
		{
			$userCanScript .= '
		wpshop(".attributeSetSectionName_PossibleEdit").hover(function(){
			wpshop("#attributeSetSectionNameEditIcon" + wpshop(this).attr("id").replace("attributeSetSectionName", "")).toggleClass("wpshopHide");
		});
		wpshop(".attributeSetSectionName_PossibleEdit").click(function(){
			wpshop("#attributeSetSectionNameEdit").val(wpshop(this).children("span:first").html());
			wpshop("#attributeSetSectionIdEdit").val(wpshop(this).attr("id").replace("attributeSetSectionName", ""));
			wpshop("#attributeSetSection_Edit").dialog("open");
		});
		wpshop("#attributeSetSection_Edit").dialog({
			autoOpen: false,
			width: 320,
			modal:  true,
			buttons:{
				"' . __('Cancel', 'wpshop') . '": function(){
					wpshop(this).dialog("close");
				},
				"' . __('Save', 'wpshop') . '": function(){
					wpshop("#managementContainer").html(wpshop("#wpshopLoadingPicture").html());
					wpshop("#managementContainer").load(WPSHOP_AJAX_FILE_URL, {
						"post": "true",
						"elementCode": "' . self::currentPageCode . '",
						"action": "editAttributeSetSection",
						"elementType": "attributeSetSection",
						"elementIdentifier": "' . $attributeSetId . '",
						"attributeSetSectionName": wpshop("#attributeSetSectionNameEdit").val(),
						"attributeSetSectionId": wpshop("#attributeSetSectionIdEdit").val()
					});
					wpshop(this).dialog("close");
				},
				"' . __('Delete', 'wpshop') . '": function(){
					if(confirm(wpshopConvertAccentTojs("' . __('Are you sure you want to delete this section?', 'wpshop') . '"))){
						wpshop("#managementContainer").html(wpshop("#wpshopLoadingPicture").html());
						wpshop("#managementContainer").load(WPSHOP_AJAX_FILE_URL, {
							"post": "true",
							"elementCode": "' . self::currentPageCode . '",
							"action": "deleteAttributeSetSection",
							"elementType": "attributeSetSection",
							"elementIdentifier": "' . $attributeSetId . '",
							"attributeSetSectionName": wpshop("#attributeSetSectionNameEdit").val(),
							"attributeSetSectionId": wpshop("#attributeSetSectionIdEdit").val()
						});
						wpshop(this).dialog("close");
					}
				}
			},
			close: function(){
				wpshop("#attributeSetSectionName").val("");
				wpshop("#attributeSetSectionIdEdit").val("");
			}
		});';
		}
		$attributeSetDetailsManagement .= '
		' . $userCan . '
		<div class="attribute_set_group_details" >';

		/*	Get information about the current attribute set we are editing	*/
		$attributeSetDetails = self::getAttributeSetDetails($attributeSetId);
		if(is_array($attributeSetDetails) && (count($attributeSetDetails) > 0)){
			/*	Build output with the current attribute set details	*/
			foreach($attributeSetDetails as $attributeSetIDGroup => $attributeSetDetailsGroup){
				/*	Check possible action for general code	*/
				$elementActionClass = 'attributeSetSectionName';
				$elementActionIcon = '';
				if($attributeSetDetailsGroup['code'] != 'general')
				{
					$elementActionClass = 'attributeSetSectionName_PossibleEdit';
					$elementActionIcon = '<span class="ui-icon attributeSetSectionNameEdit wpshopHide" id="attributeSetSectionNameEditIcon' . $attributeSetDetailsGroup['id'] . '"  >&nbsp;</span>';
				}

				$attributeSetDetailsManagement .= '
			<div id="attribute_group_' . $attributeSetIDGroup . '" class="attribute_set_section_container" >
				<fieldset>
					<legend id="attributeSetSectionName' . $attributeSetDetailsGroup['id'] . '" class="' . $elementActionClass . '" ><span class="alignleft" >' . __($attributeSetDetailsGroup['name'], 'wpshop') . '</span>' . $elementActionIcon . '</legend>';

				/*	Add the set section details	*/
				if(is_array($attributeSetDetailsGroup['attribut']) && count($attributeSetDetailsGroup['attribut']) >= 1)
				{
					$attributeSetDetailsManagement .= '
						<ul id="attribute_group_' . $attributeSetIDGroup . '_details" class="attributeGroupDetails" >';
					ksort($attributeSetDetailsGroup['attribut']);
					foreach($attributeSetDetailsGroup['attribut'] as $attributInGroup)
					{
						if(!empty($attributInGroup->id))
						{
							$attributeSetDetailsManagement .= '
					<li class="ui-state-default attribute" id="attribute_' . $attributInGroup->id . '" >' . __($attributInGroup->frontend_label, 'wpshop')  . '</li>';
							$currentOrder .= 'attribute_' . $attributInGroup->id . ', ';
						}
						else
						{
							$attributeSetDetailsManagement .= '
					<li class="invisibleAttribute" >&nbsp;</li>';
						}
					}
					$attributeSetDetailsManagement .= '
						</ul>';
				}

				$attributeSetDetailsManagement .= '<input class="newOrder" type="hidden" name="attribute_group_order[newOrder' . $attributeSetIDGroup . ']" id="newOrder' . $attributeSetIDGroup . '" value="" />
				</fieldset>
			</div>';
			}
		}

		/*	Add the interface for not-affected attribute	*/
		$attributeSetDetailsManagement .= '
		</div>
		<div class="attribute_set_not_affected_attribute" >
			<fieldset>
					<legend id="attributeSetUnaffectedAttributeSection" class="attributeSetSectionName" >' . __('Attribute not affected at this group', 'wpshop') . '</legend>
			</fieldset>
			<ul id="attribute_group_NotAffectedAttribute_details" class="attributeGroupDetails" >';
			/*	Get the not affected attribute list	*/
			$notAffectedAttributeList = self::get_not_affected_attribute($attributeSetId);
			if(count($notAffectedAttributeList) > 0){
				foreach($notAffectedAttributeList as $notAffectedAttribute){
					if(is_null($validAttributeList) || !in_array($notAffectedAttribute->id, $validAttributeList)){
						$attributeSetDetailsManagement .= '
				<li class="ui-state-default attribute" id="attribute_' . $notAffectedAttribute->id . '" >' . __($notAffectedAttribute->frontend_label, 'wpshop') . '</li>';
					}
				}
			}
			else{
				$attributeSetDetailsManagement .= '
				<li class="invisibleAttribute" >&nbsp;</li>';
			}
		$attributeSetDetailsManagement .= '
			</ul>
			<input class="newOrder" type="hidden" name="attribute_group_order[newOrderNotAffectedAttribute]" id="newOrderNotAffectedAttribute" value="" />
		</div>';

		$attributeSetDetailsManagement .= '
		<script type="text/javascript" >
			wpshop(document).ready(function(){
				make_list_sortable("' . WPSHOP_DBT_ATTRIBUTE_SET . '");

				' . $userCanScript . '
			});
		</script>';

		return $attributeSetDetailsManagement;
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
		global $wpdb, $validAttributeList;
		$attributeSetDetailsGroups = '';

		$query = $wpdb->prepare(
			"SELECT ATTRIBUTE_GROUP.id AS attr_group_id, ATTRIBUTE_GROUP.code AS attr_group_code, ATTRIBUTE_GROUP.position AS attr_group_position, ATTRIBUTE_GROUP.name AS attr_group_name, 
				ATTRIBUTE.*, ATTRIBUTE_DETAILS.position AS attr_position_in_group, ATTRIBUTE_GROUP.id as attribute_detail_id
			FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
				INNER JOIN " . self::getDbTable() . " AS ATTRIBUTE_SET ON (ATTRIBUTE_SET.id = ATTRIBUTE_GROUP.attribute_set_id)
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTRIBUTE_DETAILS ON ((ATTRIBUTE_DETAILS.attribute_group_id = ATTRIBUTE_GROUP.id) AND (ATTRIBUTE_DETAILS.attribute_set_id = ATTRIBUTE_SET.id) AND (ATTRIBUTE_DETAILS.status = 'valid'))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE ON (ATTRIBUTE.id = ATTRIBUTE_DETAILS.attribute_id AND ATTRIBUTE.status = 'valid')
			WHERE ATTRIBUTE_SET.id = %d
				AND ATTRIBUTE_SET.status IN (" . $attributeSetStatus . ") 
				AND ATTRIBUTE_GROUP.status IN (" . $attributeSetStatus . ") 
			ORDER BY ATTRIBUTE_GROUP.position, ATTRIBUTE_DETAILS.position",
			$attributeSetId);
		$attributeSetDetails = $wpdb->get_results($query);

		foreach($attributeSetDetails as $attributeGroup)
		{
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['id'] = $attributeGroup->attribute_detail_id;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['code'] = $attributeGroup->attr_group_code;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['name'] = $attributeGroup->attr_group_name;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['attribut'][$attributeGroup->attr_position_in_group] = $attributeGroup;
			$validAttributeList[] = $attributeGroup->id;
		}

		return $attributeSetDetailsGroups;
	}

	/**
	*	Get the attribute list of attribute not associated to he set we are editing
	*
	*	@param integer $attributeSetId The attribute set identifier we want to get the details for
	*	
	*	@return array $attributeSetDetails The List of attribute not affected
	*/
	function get_not_affected_attribute($attributeSetId){
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT ATTRIBUTE.*
			FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTRIBUTE_DETAILS
				INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE ON ((ATTRIBUTE.id = ATTRIBUTE_DETAILS.attribute_id) AND (ATTRIBUTE.status = 'valid'))
			WHERE ATTRIBUTE_DETAILS.status = 'deleted'
				AND ATTRIBUTE_DETAILS.attribute_set_id = %d
			GROUP BY ATTRIBUTE_DETAILS.attribute_id
			UNION
			SELECT ATTRIBUTE.*
			FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE
			WHERE ATTRIBUTE.status = 'valid'
				AND ATTRIBUTE.id NOT IN (
					SELECT ATTRIBUTE_DETAILS.attribute_id 
					FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTRIBUTE_DETAILS 
					WHERE ATTRIBUTE_DETAILS.status = 'valid'
						AND ATTRIBUTE_DETAILS.attribute_set_id = %d
				)
			GROUP BY ATTRIBUTE.id", $attributeSetId, $attributeSetId);
		$attributeSetDetails = $wpdb->get_results($query);

		return $attributeSetDetails;
	}

	/**
	*	Get the existing attribute set for an entity
	*
	*	@param integer $entityId The entity identifier we want to get the entity set list for
	*
	*	@return object $entitySets The entity sets list for the given entity
	*/
	function get_attribute_set_list_for_entity($entityId){
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