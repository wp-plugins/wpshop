<?php
/**
* Define the different method to manage attributes
* 
*	Define the different method and variable used to manage attributes
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/


/**
* Define the different method to manage attributes
* @package wpshop
* @subpackage librairies
*/
class wpshop_attributes
{
	/**
	*	Define the database table used in the current class
	*/
	const dbTable = WPSHOP_DBT_ATTRIBUTE;
	/**
	*	Define the url listing slug used in the current class
	*/
	const urlSlugListing = WPSHOP_URL_SLUG_ATTRIBUTE_LISTING;
	/**
	*	Define the url edition slug used in the current class
	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_ATTRIBUTE_LISTING;
	/**
	*	Define the current entity code
	*/
	const currentPageCode = 'attributes';
	/**
	*	Define the page title
	*/
	const pageContentTitle = 'Attributes';
	/**
	*	Define the page title when adding an attribute
	*/
	const pageAddingTitle = 'Add an attribute';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageEditingTitle = 'Attribute "%s" edit';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageTitle = 'Attributes list';

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
			if(($action == 'edit') || ($action == 'delete'))
			{
				$editedItem = self::getElement($objectInEdition);
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), str_replace("\\", "", $editedItem->frontend_label) . '&nbsp;(' . $editedItem->code . ')');
			}
			elseif($action == 'add')
			{
				$title = __(self::pageAddingTitle, 'wpshop');
			}
		}
		elseif((self::getEditionSlug() != self::getListingSlug()) && ($_GET['page'] == self::getEditionSlug()))
		{
			$title = __(self::pageAddingTitle, 'wpshop');
		}
		return $title;
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
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$saveditem = isset($_REQUEST['saveditem']) ? wpshop_tools::varSanitizer($_REQUEST['saveditem']) : '';
		if(($action != '') && ($action == 'saveok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem);
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), '<span class="bold" >' . $editedElement->code . '</span>');
		}
		elseif(($action != '') && ($action == 'deleteok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem, "'deleted'");
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully deleted', 'wpshop'), '<span class="bold" >' . $editedElement->code . '</span>');
		}

		/*	Define the database operation type from action launched by the user	 */
		$_REQUEST[self::getDbTable()]['default_value'] = str_replace('"', "'", $_REQUEST[self::getDbTable()]['default_value']);
		/*************************		GENERIC				**************************/
		/*************************************************************************/
		$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
		$id = isset($_REQUEST[self::getDbTable()]['id']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['id']) : '';
		if(($pageAction != '') && (($pageAction == 'edit') || ($pageAction == 'editandcontinue'))){
			if(current_user_can('wpshop_edit_attributes'))
			{
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				if($pageAction == 'delete')
				{
					if(current_user_can('wpshop_delete_attributes'))
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
		elseif(($pageAction != '') && (($pageAction == 'delete'))){
			if(current_user_can('wpshop_delete_attributes'))
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
		elseif(($pageAction != '') && (($pageAction == 'save') || ($pageAction == 'saveandcontinue') || ($pageAction == 'add'))){
			if(current_user_can('wpshop_add_attributes')){
				$_REQUEST[self::getDbTable()]['creation_date'] = date('Y-m-d H:i:s');
				if(trim($_REQUEST[self::getDbTable()]['code']) == ''){
					$_REQUEST[self::getDbTable()]['code'] = $_REQUEST[self::getDbTable()]['frontend_label'];
				}
				$_REQUEST[self::getDbTable()]['code'] = wpshop_tools::slugify(str_replace("\'", "_", str_replace('\"', "_", $_REQUEST[self::getDbTable()]['code'])), array('noAccent', 'noSpaces', 'lowerCase', 'noPunctuation'));
				$code_exists = self::getElement($_REQUEST[self::getDbTable()]['code'], "'valid', 'moderated', 'deleted'", 'code');
				if((is_object($code_exists) || is_array($code_exists)) && (count($code_exists) > 0)){
					$_REQUEST[self::getDbTable()]['code'] = $_REQUEST[self::getDbTable()]['code'] . '_' . (count($code_exists) + 1);
				}
				$actionResult = wpshop_database::save($_REQUEST[self::getDbTable()], self::getDbTable());
				$id = $wpdb->insert_id;
			}
			else{
				$actionResult = 'userNotAllowedForActionAdd';
			}
		}


		/*	When an action is launched and there is a result message	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/************		CHANGE ERROR MESSAGE FOR SPECIFIC CASE					*************/
		/****************************************************************************/
		if($actionResult != ''){
			$elementIdentifierForMessage = '<span class="bold" >' . $_REQUEST[self::getDbTable()]['frontend_label'] . '</span>';
			if($actionResult == 'error')
			{/*	CHANGE HERE FOR SPECIFIC CASE	*/
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . sprintf(__('An error occured while saving %s', 'wpshop'), $elementIdentifierForMessage);
				if(WPSHOP_DEBUG)
				{
					$pageMessage .= '<br/>' . $wpdb->last_error;
				}
			}
			elseif(($actionResult == 'done') || ($actionResult == 'nothingToUpdate'))
			{/*	CHANGE HERE FOR SPECIFIC CASE	*/
				/*****************************************************************************************************************/
				/*************************			CHANGE FOR SPECIFIC ACTION FOR CURRENT ELEMENT				****************************/
				/*****************************************************************************************************************/
				if(isset($_REQUEST[self::getDbTable() . '_label']))
				{/*	Set a specific label for the current attribute in different language	*/
					foreach($_REQUEST[self::getDbTable() . '_label'] as $languageId => $attributeLabelValue)
					{
						self::saveAttributeLabel($id, $languageId, $attributeLabelValue);
					}
				}

				{/*	Add the new attribute in the additionnal informations attribute group	*/
					$query = $wpdb->prepare(
						"SELECT ATTRIBUTE_SET.id AS attribute_set_id
						FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTRIBUTE_SET
						WHERE ATTRIBUTE_SET.entity_id = %d
						ORDER BY id ASC
						LIMIT 1", $_REQUEST[self::getDbTable()]['entity_id']
					);
					$wpshopAttributeSetId = $wpdb->get_row($query);
					include(WPSHOP_LIBRAIRIES_DIR . 'db/db_data_definition.php');
					$query = $wpdb->prepare(
						"SELECT ATTRIBUTE_GROUP.id AS attribute_group_id
						FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
						WHERE ATTRIBUTE_GROUP.attribute_set_id = %d
							AND ATTRIBUTE_GROUP.code = '" . $initialEavData['attributeGroup'][2]['code'] . "' ", $wpshopAttributeSetId->attribute_set_id
					);
					$wpshopAttributeGroupId = $wpdb->get_row($query);
					$query = $wpdb->prepare(
						"SELECT (MAX(position) + 1) AS position 
						FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " 
						WHERE attribute_set_id = '" . $wpshopAttributeSetId->attribute_set_id . "' 
							AND attribute_group_id = '" . $wpshopAttributeGroupId->attribute_group_id . "' 
							AND entity_type_id = '" . $_REQUEST[self::getDbTable()]['entity_id'] . "'"
					);
					$wpshopAttributePosition = $wpdb->get_var($query);
					if($wpshopAttributePosition == 0)$wpshopAttributePosition = 1;
					$query = $wpdb->prepare(
						"INSERT INTO " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " 
							(id, entity_type_id, attribute_set_id, attribute_group_id, attribute_id, position) 
						VALUES 
							('', '" . $_REQUEST[self::getDbTable()]['entity_id'] . "', '" . $wpshopAttributeSetId->attribute_set_id . "', '" . $wpshopAttributeGroupId->attribute_group_id . "', '" . $id . "', '" . $wpshopAttributePosition . "') "
					);
					$wpdb->query($query);
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
		$tableSummary = __('Existing attributes listing', 'wpshop');
		$tableTitles = array();
		$tableTitles[] = __('Attribute name', 'wpshop');
		$tableTitles[] = __('Entity', 'wpshop');
		$tableTitles[] = __('Attribute code', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_label_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_entity_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_attr_code_column';

		$line = 0;
		$elementList = self::getElement();
		if(is_array($elementList) && (count($elementList) > 0)){
		foreach($elementList as $element)
		{
			$tableRowsId[$line] = self::getDbTable() . '_' . $element->id;

			$elementLabel = __($element->frontend_label, 'wpshop');
			$subRowActions = '';
			$attributeSlugUrl = self::getListingSlug();
			if(current_user_can('wpshop_add_attributes'))
			{
				$attributeSlugUrl = self::getEditionSlug();
			}
			if(current_user_can('wpshop_edit_attributes'))
			{
				$editAction = admin_url('admin.php?page=' . $attributeSlugUrl . '&amp;action=edit&amp;id=' . $element->id);
				$subRowActions .= '
	<a href="' . $editAction . '" >' . __('Edit', 'wpshop') . '</a>';
				$elementLabel = '<a href="' . $editAction . '" >' . __($element->frontend_label, 'wpshop')  . '</a>';
			}
			elseif(current_user_can('wpshop_view_attributes_details'))
			{
				$editAction = admin_url('admin.php?page=' . $attributeSlugUrl . '&amp;action=edit&amp;id=' . $element->id);
				$subRowActions .= '
	<a href="' . $editAction . '" >' . __('View', 'wpshop') . '</a>';
				$elementLabel = '<a href="' . $editAction . '" >' . __($element->frontend_label, 'wpshop')  . '</a>';
			}
			if(current_user_can('wpshop_delete_attributes'))
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
			$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' => str_replace('\\', '', $elementLabel) . $rowActions);
			$tableRowValue[] = array('class' => self::currentPageCode . '_name_cell', 'value' => __($element->entity, 'wpshop'));
			$tableRowValue[] = array('class' => self::currentPageCode . '_code_cell', 'value' => $element->code);
			$tableRows[] = $tableRowValue;

			$line++;
		}
		}
		else{
			unset($tableRowValue);
			$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' =>  __('No element to ouput here', 'wpshop'));
			$tableRowValue[] = array('class' => self::currentPageCode . '_name_cell', 'value' => '');
			$tableRowValue[] = array('class' => self::currentPageCode . '_code_cell', 'value' => '');
			$tableRows[] = $tableRowValue;
		}
		$listItemOutput = '';
		if(current_user_can('wpshop_view_attributes_unit') || current_user_can('wpshop_edit_attributes_unit') || current_user_can('wpshop_add_attributes_unit') || current_user_can('wpshop_delete_attributes_unit') || current_user_can('wpshop_view_attributes_unit_group') || current_user_can('wpshop_edit_attributes_unit_group') || current_user_can('wpshop_add_attributes_unit_group') || current_user_can('wpshop_delete_attributes_unit_group')){
			$listItemOutput .= '<div id="wpshop_attribute_unit_manager" class="wpshopHide" title="' . __('Unit for attribute', 'wpshop') . '" >&nbsp;</div><input type="button" class="alignleft button add-new-h2" id="wpshop_attribute_unit_manager_opener" value="' . __('Manage units', 'wpshop') . '" />';
		}
		$listItemOutput .= wpshop_display::getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, true) . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#' . $tableId . '").dataTable();
	});
</script>';

		return $listItemOutput;
	}
	/**
	*	Return the page content to add a new item
	*
	*	@return string The html code that output the interface for adding a nem item
	*/
	function elementEdition($itemToEdit = ''){
		global $attribute_displayed_field;
		$dbFieldList = wpshop_database::fields_to_input(self::getDbTable());

		$editedItem = '';
		if($itemToEdit != '')
		{
			$editedItem = self::getElement($itemToEdit);
		}

		$the_form_content_hidden = $the_form_general_content = $the_form_option_content = '';
		foreach($dbFieldList as $input_key => $input_def)
		{
			if(!isset($attribute_displayed_field) || !is_array($attribute_displayed_field) || in_array($input_def['name'], $attribute_displayed_field)){
				$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
				$requestFormValue = isset($_REQUEST[self::currentPageCode][$input_def['name']]) ? wpshop_tools::varSanitizer($_REQUEST[self::currentPageCode][$input_def['name']]) : '';
				$currentFieldValue = $input_def['value'];
				if(is_object($editedItem)){
					$currentFieldValue = $editedItem->$input_def['name'];
				}
				elseif(($pageAction != '') && ($requestFormValue != '')){
					$currentFieldValue = $requestFormValue;
				}

				$input_def['value'] = $currentFieldValue;
				if($input_def['name'] == 'entity_id'){
					$input_def['possible_value'] = wpshop_entities::get_entity();
					if(count($input_def['possible_value']) == 1){
						$input_def['value'] = $input_def['possible_value'][0]->id;
						$input_def['type'] = 'hidden';
					}
					else{
						$input_def['type'] = 'select';
					}
				}
				elseif($input_def['name'] == '_unit_group_id'){
					$input_def['possible_value'] = wpshop_attributes_unit::get_unit_group();
					$input_def['type'] = 'select';
				}
				elseif($input_def['name'] == '_default_unit'){
					$input_def['possible_value'] = wpshop_attributes_unit::get_unit_list_for_group($editedItem->_unit_group_id);
					$input_def['type'] = 'select';
				}

				if(is_object($editedItem) && (($input_def['name'] == 'code') || ($input_def['name'] == 'data_type') || ($input_def['name'] == 'entity_id'))){
					$input_def['option'] = ' disabled="disabled" ';
				}
				elseif(($input_def['name'] != 'entity_id') && (substr($input_def['name'], 0, 3) != 'is_')){
					$input_def['value'] = __($currentFieldValue, 'wpshop');
				}

				$input_def['value'] = str_replace("\\", "", $input_def['value']);
				$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());

				if($input_def['type'] != 'hidden'){
					$label = 'for="' . $input_def['name'] . '"';
					if(($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox')){
						$label = '';
					}
					$input = '
		<div class="clear" >
			<div class="wpshop_form_label wpshop_' . self::currentPageCode . '_' . $input_def['name'] . '_label alignleft" >
				<label ' . $label . ' >' . __($input_def['name'], 'wpshop') . '</label>
			</div>
			<div class="wpshop_form_input wpshop_' . self::currentPageCode . '_' . $input_def['name'] . '_input alignleft" >
				' . $the_input . '
			</div>
		</div>';
					if((substr($input_def['name'], 0, 3) == 'is_') || (substr($input_def['name'], 0, 1) == '_')){
						$the_form_option_content .= $input;
					}
					else{
						$the_form_general_content .= $input;
					}
				}
				else{
					$the_form_content_hidden .= '
		' . $the_input;
				}
			}
		}

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="" >
' . wpshop_form::form_input(self::getDbTable() . '_action', self::getDbTable() . '_action', (isset($_REQUEST['action']) && ($_REQUEST['action'] != '') ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'save') , 'hidden') . '
' . wpshop_form::form_input(self::currentPageCode . '_form_has_modification', self::currentPageCode . '_form_has_modification', 'no' , 'hidden') . '
<div id="wpshopFormManagementContainer" >
	<ul>
		<li><a href="#wpshop_attr_main_infos_form" >' . __('Main informations', 'wpshop') . '</a></li>
		<li><a href="#wpshop_attr_option_infos_form" >' . __('Options', 'wpshop') . '</a></li>
	</ul>' . $the_form_content_hidden .'
	<div id="wpshop_attr_main_infos_form" >' . $the_form_general_content . '
	</div>
	<div id="wpshop_attr_option_infos_form" >' . $the_form_option_content . '
	</div>
</div>
</form>
<div class="wpshopHide" ><div id="default_value_content_default" >&nbsp;</div><div id="default_value_content_datetime" ><input type="checkbox" name="wp_wpshop__attribute[default_value]" value="date_of_current_day" />' . __('Date of the day', 'wpshop') . '</div></div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		wpshopMainInterface("' . self::getDbTable() . '", "' . __('Are you sure you want to quit this page? You will loose all current modification', 'wpshop') . '", "' . __('Are you sure you want to delete this attribute?', 'wpshop') . '");

		if(jQuery("#is_requiring_unit").val() == "no"){
			jQuery("#_unit_group_id").hide();
			jQuery(".wpshop_' . self::currentPageCode . '__unit_group_id_label").hide();
			jQuery("#_default_unit").hide();
			jQuery(".wpshop_' . self::currentPageCode . '__default_unit_label").hide();
		}
		else if(jQuery("#is_requiring_unit").val() == "yes"){
			jQuery("#_unit_group_id").show();
			jQuery(".wpshop_' . self::currentPageCode . '__unit_group_id_label").show();
			jQuery("#_default_unit").show();
			jQuery(".wpshop_' . self::currentPageCode . '__default_unit_label").show();
		}
		jQuery("#is_requiring_unit").change(function(){
			if(jQuery(this).val() == "no"){
				jQuery("#_unit_group_id").hide();
				jQuery(".wpshop_' . self::currentPageCode . '__unit_group_id_label").hide();
				jQuery("#_default_unit").hide();
				jQuery(".wpshop_' . self::currentPageCode . '__default_unit_label").hide();
			}
			else if(jQuery(this).val() == "yes"){
				jQuery("#_unit_group_id").show();
				jQuery(".wpshop_' . self::currentPageCode . '__unit_group_id_label").show();
				jQuery("#_default_unit").show();
				jQuery(".wpshop_' . self::currentPageCode . '__default_unit_label").show();
			}
		});

		jQuery("#_unit_group_id").change(function(){
			jQuery(".wpshop_attributes__default_unit_input").load(WPSHOP_AJAX_FILE_URL,{
				"post": "true",
				"elementCode": "attribute_unit_management",
				"action": "load_attribute_unit_list",
				"current_group": jQuery(this).val()
			});
		});

		function change_date_default_value_input(current_value){
			if(current_value == "datetime"){
				wpshop("#default_value_content_default").html(wpshop(".wpshop_attributes_default_value_input").html());
				wpshop("#default_value_content_default textarea").attr("id", "old_default");
				wpshop(".wpshop_attributes_default_value_input").html(wpshop("#default_value_content_datetime").html());
				wpshop(".wpshop_attributes_default_value_input input").attr("id", "default_value");
				if(wpshop("#old_default").val() == "date_of_current_day"){
					wpshop("#default_value").prop("checked", "true");
				}
			}
			else{
				wpshop(".wpshop_attributes_default_value_input").html(wpshop("#default_value_content_default").html());
				wpshop(".wpshop_attributes_default_value_input textarea").attr("id", "default_value");
			}
		}
		if(wpshop("#data_type").val() == "datetime"){
			change_date_default_value_input(wpshop("#data_type").val());
		}
		wpshop("#data_type").change(function(){
			change_date_default_value_input(wpshop(this).val());
		});
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
			if(current_user_can('wpshop_add_attributes'))
			{
				$currentPageButton .= '<input type="button" class="button-primary" id="add" name="add" value="' . __('Add', 'wpshop') . '" />';
			}
		}
		elseif(current_user_can('wpshop_edit_attributes'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="save" name="save" value="' . __('Save', 'wpshop') . '" /><input type="button" class="button-primary" id="saveandcontinue" name="saveandcontinue" value="' . __('Save and continue edit', 'wpshop') . '" />';
		}
		if(current_user_can('wpshop_delete_attributes') && ($action != 'add'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="delete" name="delete" value="' . __('Delete', 'wpshop') . '" />';
		}

		$currentPageButton .= '<h2 class="alignright cancelButton" ><a href="' . admin_url('admin.php?page=' . self::getListingSlug()) . '" class="button add-new-h2" >' . __('Back', 'wpshop') . '</a></h2>';

		return $currentPageButton;
	}

	/**
	*	Get the existing attribute list into database
	*
	*	@param integer $element_id optionnal The attribute identifier we want to get. If not specify the entire list will be returned
	*	@param string $element_status optionnal The status of element to get into database. Default is set to valid element
	*	@param mixed $field_to_search optionnal The field we want to check the row identifier into. Default is to set id
	*
	*	@return object $element_list A wordpress database object containing the attribute list
	*/
	function getElement($element_id = '', $element_status = "'valid', 'moderated'", $field_to_search = 'id')
	{
		global $wpdb;
		$element_list = array();
		$moreQuery = "";

		if($element_id != '')
		{
			$moreQuery = "
			AND CURRENT_ELEMENT." . $field_to_search . " = '" . $element_id . "' ";
		}

		$query = $wpdb->prepare(
		"SELECT CURRENT_ELEMENT.*, ENTITIES.code as entity
		FROM " . self::getDbTable() . " AS CURRENT_ELEMENT
			INNER JOIN " . WPSHOP_DBT_ENTITIES . " AS ENTITIES ON (ENTITIES.id = CURRENT_ELEMENT.entity_id)
		WHERE CURRENT_ELEMENT.status IN (".$element_status.") " . $moreQuery
		);

		/*	Get the query result regarding on the function parameters. If there must be only one result or a collection	*/
		if($element_id == '')
		{
			$element_list = $wpdb->get_results($query);
		}
		else
		{
			$element_list = $wpdb->get_row($query);
		}

		return $element_list;
	}


	/**
	*	Save the different label value for an attribute
	*
	*	@param integer $attributeId The attribute identifier we want to set a label for
	*	@param integer $languageId The language we want to set
	*	@param string $label The label value to set for the attribute in the given language
	*
	*	@return string $requestResponse The operation response. If there has been an error or not
	*/
	function saveAttributeLabel($attributeId, $languageId, $label)
	{
		global $wpdb;
		$requestResponse = '';

		$query = $wpdb->prepare(
			"REPLACE INTO " . WPSHOP_DBT_ATTRIBUTE_LABEL . " (attribute_id, language_id, name) 
				VALUES 
			(%d, %d, %s)", 
		$attributeId, $languageId, $label);
		if( $wpdb->query($query) )
		{
			$requestResponse = 'done';
		}
		elseif( $wpdb->query($query) == 0 )
		{
			$requestResponse = 'nothingToUpdate';
		}
		else
		{
			$requestResponse = 'error';
		}

		return $requestResponse;
	}

	/**
	*	Return the value of attribute label defined in the different existing language
	*
	*	@param integer $attributeId The attribute identifier we want to get the label for
	*	@param integer $languageId optionnal The language we want to get the label for. If this parameter is not specified we will get the entire label list for the attribute
	*/
	function getAttributeLabel($attributeId, $languageId = '')
	{
		global $wpdb;
		$moreQuery = "";
		$labelList = '';

		if($languageId != '')
		{
			$moreQuery = "
			AND ATTR_LABEL.language_id = '" . $languageId . "' ";
		}

		$query = $wpdb->prepare(
			"SELECT ATTR_LABEL.name
			FROM " . WPSHOP_DBT_ATTRIBUTE_LABEL . " AS ATTR_LABEL
			WHERE ATTR_LABEL.status = 'valid' 
				AND ATTR_LABEL.attribute_id = %d" . $moreQuery,
		$attributeId);

		if($languageId != '')
		{
			$labelList = $wpdb->get_row($query);
		}
		else
		{
			$labelList = $wpdb->get_results($query);
		}

		return $labelList;
	}

	/**
	*	Save the different value for attribute of a given entity type and entity
	*
	*	@param array $attributeToSet The list of attribute with each value to set
	*	@param integer $entityTypeId The entity type identifier (products/categories/...)
	*	@param integer $entityId The entity identifier we want to save attribute for (The specific product/category/...)
	*	@param string $language The language to set the value for into database
	*
	*/
	function saveAttributeForEntity($attributeToSet, $entityTypeId, $entityId, $language){
		global $wpdb;

		foreach($attributeToSet as $attributeType => $attributeTypeDetails){
			$q = "  ";
			foreach($attributeTypeDetails as $attribute_code => $attributeValue){
				if($attribute_code != 'unit'){
					$unit_id = 0;
					if(isset($attributeTypeDetails['unit'][$attribute_code])){
						$unit_id = $attributeTypeDetails['unit'][$attribute_code];
					}
					$currentAttribute = self::getElement($attribute_code, "'valid'", 'code');
					if($currentAttribute->is_historisable == 'yes'){
						$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType . " WHERE entity_type_id = %d AND attribute_id = %d AND entity_id = %d", $entityTypeId, $currentAttribute->id, $entityId);
						$attribute_histo = $wpdb->get_results($query);
						$attribute_histo_content['status'] = 'valid';
						$attribute_histo_content['creation_date'] = date('Y-m-d H:i:s');
						$attribute_histo_content['original_value_id'] = $attribute_histo[0]->value_id;
						$attribute_histo_content['entity_type_id'] = $attribute_histo[0]->entity_type_id;
						$attribute_histo_content['attribute_id'] = $attribute_histo[0]->attribute_id;
						$attribute_histo_content['entity_id'] = $attribute_histo[0]->entity_id;
						$attribute_histo_content['unit_id'] = $attribute_histo[0]->unit_id;
						$attribute_histo_content['language'] = $attribute_histo[0]->language;
						$attribute_histo_content['value'] = $attribute_histo[0]->value;
						$attribute_histo_content['value_type'] = WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType;
						$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, $attribute_histo_content);
					}
					$attributeValue = str_replace("\\", "", $attributeValue);
					$q .= ("('', '" . $entityTypeId . "', '" . $currentAttribute->id . "', '" . $entityId . "', '" . $unit_id . "', '" . $language . "', '" . $wpdb->escape($attributeValue) . "'), ");
				}
			}
			$q = substr($q, 0, -2);

			if(trim($q) != ''){
				$query = 
				"REPLACE INTO " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType . " (value_id, entity_type_id, attribute_id, entity_id, unit_id, language, value) 
					VALUES " . $q . ";";
				$wpdb->query($query);
			}
		}
	}

	/**
	*	Return the value for a given attribute of a given entity type and a given entity
	*
	*	@param string $attributeType The extension of the database table to get the attribute value in
	*	@param integer $attributeId The attribute identifier we want to get the value for
	*	@param integer $entityTypeId The entity type identifier we want to get the attribute value for (example: product = 1)
	*	@param integer $entityId The entity id we want the attribute value for
	*
	*	@return object $attributeValue A wordpress database object containing the value of the attribute for the selected entity
	*/
	function getAttributeValueForEntityInSet($attributeType, $attributeId, $entityTypeId, $entityId){
		global $wpdb;
		$attributeValue = '';

		$query = $wpdb->prepare(
			"SELECT value, unit_id
			FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType . " 
			WHERE attribute_id = %d 
				AND entity_type_id = %d 
				AND entity_id = %d", 
		$attributeId, $entityTypeId, $entityId);
		$attributeValue = $wpdb->get_row($query);

		return $attributeValue;
	}

	/**
	*	Get the existing element list into database
	*
	*	@param integer $elementId optionnal The element identifier we want to get. If not specify the entire list will be returned
	*	@param string $elementStatus optionnal The status of element to get into database. Default is set to valid element
	*
	*	@return object $elements A wordpress database object containing the element list
	*/
	function getElementWithAttributeAndValue($entityId, $elementId, $language, $keyForArray = '', $outputType = ''){
		global $wpdb;
		$elements = array();
		$moreQuery = "";

		if($outputType == 'frontend'){
			$moreQuery .= " 
				AND is_visible_in_front = 'yes' ";
		}

		$query = $wpdb->prepare(
			"SELECT POST_META.*,
				ATTR.id as attribute_id, ATTR.data_type, ATTR.backend_table, ATTR.frontend_input, ATTR.frontend_label, ATTR.code AS attribute_code,
				ATTR_VALUE_VARCHAR.value AS attribute_value_varchar, ATTR_UNIT_VARCHAR.unit AS attribute_unit_varchar, 
				ATTR_VALUE_DECIMAL.value AS attribute_value_decimal, ATTR_UNIT_DECIMAL.unit AS attribute_unit_decimal, 
				ATTR_VALUE_TEXT.value AS attribute_value_text, ATTR_UNIT_TEXT.unit AS attribute_unit_text, 
				ATTR_VALUE_INTEGER.value AS attribute_value_integer, ATTR_UNIT_INTEGER.unit AS attribute_unit_integer, 
				ATTR_VALUE_DATETIME.value AS attribute_value_datetime, ATTR_UNIT_DATETIME.unit AS attribute_unit_datetime,
				ATTRIBUTE_GROUP.code AS attribute_set_section_code, ATTRIBUTE_GROUP.name AS attribute_set_section_name
			FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS EAD ON ((EAD.attribute_id = ATTR.id))
				INNER JOIN " . $wpdb->postmeta . " AS POST_META ON ((POST_META.post_id = %d) AND (POST_META.meta_key = '_wpshop_product_attribute_set_id') AND (POST_META.meta_value = EAD.attribute_set_id))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP  ON ((ATTRIBUTE_GROUP.id = EAD.attribute_group_id) AND (ATTRIBUTE_GROUP.status = 'valid'))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR . " AS ATTR_VALUE_VARCHAR ON ((ATTR_VALUE_VARCHAR.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_VARCHAR.attribute_id = ATTR.id) AND (ATTR_VALUE_VARCHAR.entity_id = %d) AND (ATTR_VALUE_VARCHAR.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_VARCHAR ON ((ATTR_UNIT_VARCHAR.id = ATTR_VALUE_VARCHAR.unit_id) AND (ATTR_UNIT_VARCHAR.status = 'valid'))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATTR_VALUE_DECIMAL ON ((ATTR_VALUE_DECIMAL.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_DECIMAL.attribute_id = ATTR.id) AND (ATTR_VALUE_DECIMAL.entity_id = %d) AND (ATTR_VALUE_DECIMAL.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_DECIMAL ON ((ATTR_UNIT_DECIMAL.id = ATTR_VALUE_DECIMAL.unit_id) AND (ATTR_UNIT_DECIMAL.status = 'valid'))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT . " AS ATTR_VALUE_TEXT ON ((ATTR_VALUE_TEXT.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_TEXT.attribute_id = ATTR.id) AND (ATTR_VALUE_TEXT.entity_id = %d) AND (ATTR_VALUE_TEXT.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_TEXT ON ((ATTR_UNIT_TEXT.id = ATTR_VALUE_TEXT.unit_id) AND (ATTR_UNIT_TEXT.status = 'valid'))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATTR_VALUE_INTEGER ON ((ATTR_VALUE_INTEGER.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_INTEGER.attribute_id = ATTR.id) AND (ATTR_VALUE_INTEGER.entity_id = %d) AND (ATTR_VALUE_INTEGER.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_INTEGER ON ((ATTR_UNIT_INTEGER.id = ATTR_VALUE_INTEGER.unit_id) AND (ATTR_UNIT_INTEGER.status = 'valid'))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME . " AS ATTR_VALUE_DATETIME ON ((ATTR_VALUE_DATETIME.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_DATETIME.attribute_id = ATTR.id) AND (ATTR_VALUE_DATETIME.entity_id = %d) AND (ATTR_VALUE_DATETIME.language = '" . $language . "')) 
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_DATETIME ON ((ATTR_UNIT_DATETIME.id = ATTR_VALUE_DATETIME.unit_id) AND (ATTR_UNIT_DATETIME.status = 'valid'))
			WHERE 1 
				AND ATTR.status = 'valid'
				AND EAD.entity_type_id = '" . $entityId . "' " . $moreQuery, 
		$elementId, $elementId, $elementId, $elementId, $elementId, $elementId);

		$elementsWithAttributeAndValues = $wpdb->get_results($query);
		foreach($elementsWithAttributeAndValues as $elementDefinition)
		{
			$arrayKey = $elementDefinition->attribute_id;
			if($keyForArray == 'code')
			{
				$arrayKey = $elementDefinition->attribute_code;
			}
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['code'] = $elementDefinition->attribute_set_section_code;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['data_type'] = $elementDefinition->data_type;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['backend_table'] = $elementDefinition->backend_table;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['frontend_input'] = $elementDefinition->frontend_input;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['frontend_label'] = $elementDefinition->frontend_label;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['attribute_code'] = $elementDefinition->attribute_code;
			$attributeValueField = 'attribute_value_' . $elementDefinition->data_type;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'] = $elementDefinition->$attributeValueField;
			$attributeUnitField = 'attribute_unit_' . $elementDefinition->data_type;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['unit'] = $elementDefinition->$attributeUnitField;
		}

		return $elements;
	}
	
	/**
	* Traduit le shortcode et affiche la valeur d'un attribut donné
	* @param array $atts : tableau de paramètre du shortcode
	* @return mixed
	**/
	function wpshop_att_val_func($atts) {
		global $wpdb;
		global $wp_query;
		
		$att_type = array(
			'datetime'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME,
			'decimal'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL,
			'integer'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER,
			'text'		=>	WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT,
			'varchar'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR
		);
		if(empty($atts['pid'])) $atts['pid']=$wp_query->posts[0]->ID;
		if(in_array($atts['type'], array_keys($att_type))) {
			$query = 'SELECT value FROM '.$att_type[$atts['type']].' WHERE entity_id='.$atts['pid'].' AND attribute_id='.$atts['attid'].'';
			$data = $wpdb->get_results($query);
			return $data[0]->value;
		}
	}

	/**
	*	Return the output for attribute list for a given attribute set and a given item to edit
	*
	*	@param integer $attributeSetId The attribute set to get the attribute for
	*	@param string $currentPageCode Define on wich page we want to get the attribute
	*	@param integer $itemToEdit The item identifier we are working on and we want to get attributes and attributes value for
	*
	*	@return array $box An array with the different content to output: box and box content
	*/
	function getAttributeFieldOutput($attributeSetId, $currentPageCode, $itemToEdit, $outputType = 'box'){
		global $wpdb;
		$box = $box['box'] = $box['boxContent'] = $box['generalTabContent'] = array();

		/*	Get the attribute set details in order to build the product interface	*/
		$productAttributeSetDetails = wpshop_attributes_set::getAttributeSetDetails($attributeSetId, "'valid'");
		if(count($productAttributeSetDetails) > 0){
			/*	Read the attribute list in order to output	*/
			foreach($productAttributeSetDetails as $productAttributeSetDetail){
				$currentTabContent = '';
				$shortcodes = '';
				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute){
						if(!empty($attribute->id)){
							$attributeInputDomain = $currentPageCode . '_attribute[' . $attribute->data_type . ']';
							$input_def['id'] = $currentPageCode . '_' . $itemToEdit . '_attribute_' . $attribute->id;
							$input_def['name'] = $attribute->code;
							$input_def['type'] = wpshop_tools::defineFieldType($attribute->data_type);
							$input_label = $attribute->frontend_label;
							$input_def['value'] = $attribute->default_value;
							$attributeValue = wpshop_attributes::getAttributeValueForEntityInSet($attribute->data_type, $attribute->id, wpshop_entities::get_entity_identifier_from_code($currentPageCode), $itemToEdit);
							if($attributeValue != ''){
								$input_def['value'] = $attributeValue->value;
							}

							/*	Manage specific field as the attribute_set_id in product form	*/
							if($input_def['name'] == 'product_attribute_set_id'){
								$input_def['value'] = get_post_meta($itemToEdit, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
								$input_def['type'] = 'hidden';
							}

							$input_options = '';
							if($attribute->data_type == 'datetime'){
								if((($input_def['value'] == '') || ($input_def['value'] == 'date_of_current_day')) && ($attribute->default_value == 'date_of_current_day')){
									$input_def['value'] = date('Y-m-d');
								}
								$input_def['option'] = ' class="wpshop_input_datetime" ';
								$input_options = '<script type="text/javascript" >wpshop(document).ready(function(){wpshop("#' . $input_def['id'] . '").val("' . str_replace(" 00:00:00", "", $input_def['value']) . '")});</script>';
							}

							$label = 'for="' . $input_def['id'] . '"';
							if(($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox')){
								$label = '';
							}
							$input_label = str_replace("\\", "", $input_label);
							$input_def['value'] = str_replace("\\", "", $input_def['value']);
							$input = wpshop_form::check_input_type($input_def, $attributeInputDomain);

							/*	Add the unit to the attribute if attribute configuration is set to yes	*/
							if($attribute->is_requiring_unit == 'yes'){
								$unit_input_def['possible_value'] = wpshop_attributes_unit::get_unit_list_for_group($attribute->_unit_group_id);
								$unit_input_def['type'] = 'select';
								$unit_input_def['option'] = ' class="wpshop_attribute_unit_input" ';
								$unit_input_def['id'] = $currentPageCode . '_' . $itemToEdit . '_unit_attribute_' . $attribute->id;
								$unit_input_def['name'] = $attribute->code;
								$unit_input_def['value'] = $attributeValue->unit_id;
								if($unit_input_def['value'] == ''){
									if($attribute->_default_unit > 0){
										$unit_input_def['value'] = $attribute->_default_unit;
									}
									else{
										$unit_input_def['value'] = wpshop_attributes_unit::get_default_unit_for_group($attribute->_unit_group_id);
									}
								}
								$input .= wpshop_form::check_input_type($unit_input_def, $attributeInputDomain .= '[unit]');
							}

							if($input_def['type'] != 'hidden'){
								$currentTabContent .= '
									<div class="clear" >
										<div class="wpshop_form_label wpshop_' . $currentPageCode . '_' . $input_def['name'] . '_label alignleft" >
											<label ' . $label . ' >' . __($input_label, 'wpshop') . '</label>
										</div>
										<div class="wpshop_form_input_element wpshop_' . $currentPageCode . '_' . $input_def['name'] . '_input alignleft" >
											' . $input . $input_options . '
										</div>
									</div>';
									
								$shortcodes .= __($input_label, 'wpshop').'<code>[wpshop_att_val type="'.$attribute->data_type.'" attid="'.$attribute->id.'" pid="'.$itemToEdit.'"]</code> '.__('or', 'wpshop').' <code>&lt;?php echo do_shortcode(\'[wpshop_att_val type="'.$attribute->data_type.'" attid="'.$attribute->id.'" pid="'.$itemToEdit.'"]\'); ?></code><br /><br />';
							}
							else{
								$currentTabContent .= $input;
							}
						}
						else{
							$currentTabContent = __('Nothing avaiblable here. You can go in attribute management interface in order to add content here.', 'wpshop');
						}
					}
					$currentTabContent .= '<br /><br /><div><strong>'.__('Shortcodes','wpshop').'</strong> - <a href="#" class="show-hide-shortcodes">Afficher</a><div class="shortcodes_container" style="display:none;"><br />'.$shortcodes;
					$currentTabContent .='<label>'.__('Attribut group code insertion', 'wpshop').'</label> <code>[wpshop_att_group pid="'.$itemToEdit.'" sid="'.$productAttributeSetDetail['id'].'"]</code> '.__('or', 'wpshop').' <code>&lt;?php echo do_shortcode(\'[wpshop_att_group pid="'.$itemToEdit.'" sid="'.$productAttributeSetDetail['id'].'"]\'); ?></code>';
					$currentTabContent .= '</div></div>';
				}
				
				if($outputType == 'box'){
					if($productAttributeSetDetail['code'] != 'general'){
						$box['box'][$productAttributeSetDetail['code']] = $productAttributeSetDetail['name'];
						$box['boxContent'][$productAttributeSetDetail['code']] = '
		<div id="wpshop_' . $currentPageCode . '_' . wpshop_tools::slugify($productAttributeSetDetail['code'], array('noAccent')) . '_form" >' . $currentTabContent . '
		</div>';
					}
					else{
						$box['generalTabContent'][$productAttributeSetDetail['code']] = $currentTabContent;
					}
				}
				elseif($outputType == 'column'){
					$currentTabContent = str_replace('wpshop_form_input_element', 'wpshop_form_input_column', $currentTabContent);
					$currentTabContent = str_replace('wpshop_form_label', 'wpshop_form_label_column', $currentTabContent);
					if($productAttributeSetDetail['code'] != 'general'){
						$box['columnTitle'][$productAttributeSetDetail['code']] = __($productAttributeSetDetail['name'], 'wpshop');
						$box['columnContent'][$productAttributeSetDetail['code']] = $currentTabContent;
					}
					else{
						$box['generalTabContent'][$productAttributeSetDetail['code']] = $currentTabContent;
					}
				}
			}
		}

		return $box;
	}

	/**
	*	Return content informations about a given attribute
	*
	*	@param string $attribute_code The code of attribute to get (Not the id because if another system is using eav model it could have some conflict)
	*	@param integer $entity_id The current entity we want to have the attribute value for
	*	@param string $entity_type The current entity type code we want to have the attribute value for
	*
	*	@return object $attribute_value_content The attribute content
	*/
	function get_attribute_value_content($attribute_code, $entity_id, $entity_type){
		$attribute_value_content = '';

		$atributes = self::getElement($attribute_code, "'valid'", 'code');
		$attribute_value_content = self::getAttributeValueForEntityInSet($atributes->data_type, $atributes->id,  wpshop_entities::get_entity_identifier_from_code($entity_type), $entity_id);

		return $attribute_value_content;
	}

}