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
	const urlSlugEdition = WPSHOP_URL_SLUG_ATTRIBUTE_EDITION;
	/**
	*	Define the current entity code
	*/
	const currentPageCode = 'attributes';
	/**
	*	Define the page title
	*/
	const pageContentTitle = 'Attributs';
	/**
	*	Define the page title when adding an attribute
	*/
	const pageAddingTitle = 'Ajouter un attribut';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageEditingTitle = '&Eacute;diter l\'attribut "%s"';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageTitle = 'Liste des attributs';

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
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), $editedItem->code);
			}
			elseif($action == 'add')
			{
				$title = __(self::pageAddingTitle, 'wpshop');
			}
		}
		elseif($_GET['page'] == self::getEditionSlug())
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
		global $wpdb;

		$pageMessage = $actionResult = '';

		/*	Start definition of output message when action is doing on another page	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/****************************************************************************/
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$saveditem = isset($_REQUEST['saveditem']) ? wpshop_tools::varSanitizer($_REQUEST['saveditem']) : '';
		if(($action != '') && ($action == 'saveok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem);
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('L\'enregistrement de %s s\'est d&eacute;roul&eacute; avec succ&eacute;s', 'wpshop'), '<span class="bold" >' . $editedElement->code . '</span>');
		}
		elseif(($action != '') && ($action == 'deleteok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem, "'deleted'");
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s a &eacute;t&eacute; supprim&eacute;e avec succ&eacute;s', 'wpshop'), '<span class="bold" >' . $editedElement->code . '</span>');
		}

		/*	Define the database operation type from action launched by the user	 */
		/*************************		GENERIC				**************************/
		/*************************************************************************/
		$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
		$id = isset($_REQUEST[self::getDbTable()]['id']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['id']) : '';
		if(($pageAction != '') && (($pageAction == 'edit') || ($pageAction == 'editandcontinue')))
		{
			if(current_user_can('wpshop_edit_attribute'))
			{
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				if($pageAction == 'delete')
				{
					if(current_user_can('wpshop_delete_attribute'))
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
			if(current_user_can('wpshop_delete_attribute'))
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
			if(current_user_can('wpshop_add_attribute'))
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
			{/*	CHANGE HERE FOR SPECIFIC CASE	*/
				/*****************************************************************************************************************/
				/*************************			CHANGE FOR SPECIFIC ACTION FOR CURRENT ELEMENT				****************************/
				/*****************************************************************************************************************/
				if(isset($_REQUEST[self::getDbTable() . '_label']))
				{
					foreach($_REQUEST[self::getDbTable() . '_label'] as $languageId => $attributeLabelValue)
					{
						self::saveAttributeLabel($id, $languageId, $attributeLabelValue);
					}
				}

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
		$tableSummary = __('Existing attributes listing', 'wpshop');
		$tableTitles = array();
		$tableTitles[] = __('Nom de l\'attribut', 'wpshop');
		$tableTitles[] = __('Entit&eacute;', 'wpshop');
		$tableTitles[] = __('Code de l\'attribut', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_label_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_entity_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_attr_code_column';

		$line = 0;
		$elementList = self::getElement();
		foreach($elementList as $element)
		{
			$tableRowsId[$line] = self::getDbTable() . '_' . $element->id;

			$elementLabel = $element->frontend_label;
			$subRowActions = '';
			$attributeSlugUrl = self::getListingSlug();
			if(current_user_can('wpshop_add_attribute'))
			{
				$attributeSlugUrl = self::getEditionSlug();
			}
			if(current_user_can('wpshop_edit_attribute'))
			{
				$editAction = admin_url('admin.php?page=' . $attributeSlugUrl . '&amp;action=edit&amp;id=' . $element->id);
				$subRowActions .= '
	<a href="' . $editAction . '" >' . __('Modifier', 'wpshop') . '</a>';
				$elementLabel = '<a href="' . $editAction . '" >' . $element->frontend_label  . '</a>';
			}
			elseif(current_user_can('wpshop_view_attribute_details'))
			{
				$editAction = admin_url('admin.php?page=' . $attributeSlugUrl . '&amp;action=edit&amp;id=' . $element->id);
				$subRowActions .= '
	<a href="' . $editAction . '" >' . __('Voir', 'wpshop') . '</a>';
				$elementLabel = '<a href="' . $editAction . '" >' . $element->frontend_label  . '</a>';
			}
			if(current_user_can('wpshop_delete_attribute'))
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
			$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' => $elementLabel . $rowActions);
			$tableRowValue[] = array('class' => self::currentPageCode . '_name_cell', 'value' => __($element->entity, 'wpshop'));
			$tableRowValue[] = array('class' => self::currentPageCode . '_code_cell', 'value' => $element->code);
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
		global $attribute_displayed_field;
		$dbFieldList = wpshop_database::fields_to_input(self::getDbTable());
		$moreTabs = $moreTabsContent = '';

		$editedItem = '';
		if($itemToEdit != '')
		{
			$editedItem = self::getElement($itemToEdit);
		}

		$the_form_content_hidden = $the_form_general_content = $the_form_option_content = '';
		foreach($dbFieldList as $input_key => $input_def)
		{
			$input_name = $input_def['name'];
			$input_value = $input_def['value'];
			$input_type = $input_def['type'];

			if(!isset($attribute_displayed_field) || !is_array($attribute_displayed_field) || in_array($input_name, $attribute_displayed_field))
			{
				$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
				$requestFormValue = isset($_REQUEST[self::currentPageCode][$input_name]) ? wpshop_tools::varSanitizer($_REQUEST[self::currentPageCode][$input_name]) : '';
				$currentFieldValue = $input_value;
				if(is_object($editedItem))
				{
					$currentFieldValue = $editedItem->$input_name;
				}
				elseif(($pageAction != '') && ($requestFormValue != ''))
				{
					$currentFieldValue = $requestFormValue;
				}
				$input_def['value'] = $currentFieldValue;

				if($input_name == 'entity_id')
				{
					$input_def['possible_value'] = wpshop_entities::getEntity();
					$input_def['type'] = 'select';
				}
				if(is_object($editedItem) && (($input_name == 'code') || ($input_name == 'data_type') || ($input_name == 'entity_id')))
				{
					$input_def['option'] = ' disabled="disabled" ';
				}
				$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());

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
					if(substr($input_name, 0, 3) == 'is_')
					{
						$the_form_option_content .= $input;
					}
					else
					{
						$the_form_general_content .= $input;
					}
				}
				else
				{
					$the_form_content_hidden .= '
		' . $the_input;
				}
			}
		}

		/*	Get the language list for adding a tab in case of several languages exist	*/
		$languageList = wpshop_language::getElement('', "'valid'");
		if(count($languageList) > 1)
		{
			$moreTabs = '<li><a href="#wpshop_' . self::currentPageCode . '_infos_form" >' . __('Label de l\'attribut', 'wpshop') . '</a></li>';
			$the_form_lang_content = '';
			foreach($languageList as $language)
			{
				$input_def['id'] = 'lang' . $language->id;
				$input_def['name'] = $language->id;
				$input_def['value'] = '';
				if($itemToEdit != '')
				{
					$attributeLabel = self::getAttributeLabel($itemToEdit, $language->id);
					$input_def['value'] = $attributeLabel->name;
				}
				$input_def['type'] = 'text';
				$isDefaultLanguage = '';
				$languageIcon = '';
				if(wpshop_document::checkIfExist('file', WP_CONTENT_DIR, $language->icon_path))
				{
					$languageIcon = '&nbsp;<img class="languageIconAdmin" src="' . WP_CONTENT_URL . $language->icon_path . '" alt="language icon ' . $language->name . '" />&nbsp;';
				}
				if($language->is_default == 'yes')
				{
					$isDefaultLanguage = '&nbsp;(' . __('D&eacute;faut', 'wpshop') . ')';
					if(($itemToEdit != '') && ($input_def['value'] == '') && (is_object($editedItem)) && ($editedItem->frontend_label != ''))
					{
						$input_def['value'] = $editedItem->frontend_label;
					}
				}
				$the_form_lang_content .= '
	<div class="clear" >
		<div class="wpshop_form_label wpshop_' . self::currentPageCode . '_' . $input_def['name'] . '_label alignleft" >
			<label for="' . $input_def['id'] . '" >' . $languageIcon . $language->name . $isDefaultLanguage . '</label>
		</div>
		<div class="wpshop_form_input wpshop_' . self::currentPageCode . '_' . $input_def['name'] . '_input alignleft" >
			' . wpshop_form::check_input_type($input_def, self::getDbTable() . '_label') . '
		</div>
	</div>';
			}
			$moreTabsContent = '
	<div id="wpshop_' . self::currentPageCode . '_infos_form" >
		<div class="wpshopHelp" >' . __('Vous pouvez d&eacute;finir le nom affich&eacute; pour les diff&eacute;rentes langues que vous avez d&eacute;finies dans la section option du plugin', 'wpshop') . '</div><div class="wpshopAttrLangContainer" >' . $the_form_lang_content . '</div>
	</div>';
		}

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="" >
' . wpshop_form::form_input(self::getDbTable() . '_action', self::getDbTable() . '_action', (isset($_REQUEST['action']) && ($_REQUEST['action'] != '') ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'save') , 'hidden') . '
' . wpshop_form::form_input(self::currentPageCode . '_form_has_modification', self::currentPageCode . '_form_has_modification', 'no' , 'hidden') . '
<div id="wpshopFormManagementContainer" >
	<ul>
		<li><a href="#wpshop_attr_main_infos_form" >' . __('G&eacute;n&eacute;ral', 'wpshop') . '</a></li>
		<li><a href="#wpshop_attr_option_infos_form" >' . __('Options', 'wpshop') . '</a></li>' . $moreTabs . '
	</ul>' . $the_form_content_hidden .'
	<div id="wpshop_attr_main_infos_form" >' . $the_form_general_content . '
	</div>
	<div id="wpshop_attr_option_infos_form" >' . $the_form_option_content . '
	</div>' . $moreTabsContent . '
</div>
</form>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		wpshopMainInterface("' . self::getDbTable() . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir quitter cette page? Vous perdrez toutes les modification que vous aurez effectu&eacute;es', 'wpshop') . '");

		wpshop("#delete").click(function(){
			wpshop("#' . self::getDbTable() . '_action").val("delete");
			deleteAttribute();
		});
		if(wpshop("#' . self::getDbTable() . '_action").val() == "delete"){
			deleteAttribute();
		}
		function deleteAttribute(){
			if(confirm(wpshopConvertAccentTojs("' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cet attribut?', 'wpshop') . '"))){
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
			if(current_user_can('wpshop_add_attribute'))
			{
				$currentPageButton .= '<input type="button" class="button-primary" id="add" name="add" value="' . __('Ajouter', 'wpshop') . '" />';
			}
		}
		elseif(current_user_can('wpshop_edit_attribute'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="save" name="save" value="' . __('Enregistrer', 'wpshop') . '" /><input type="button" class="button-primary" id="saveandcontinue" name="saveandcontinue" value="' . __('Enregistrer et continuer l\'&eacute;dition', 'wpshop') . '" />';
		}
		if(current_user_can('wpshop_delete_attribute') && ($action != 'add'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="delete" name="delete" value="' . __('Supprimer', 'wpshop') . '" />';
		}

		$currentPageButton .= '<h2 class="alignright cancelButton" ><a href="' . admin_url('admin.php?page=' . self::getListingSlug()) . '" class="button add-new-h2" >' . __('Retour', 'wpshop') . '</a></h2>';

		return $currentPageButton;
	}

	/**
	*	Get the existing attribute list into database
	*
	*	@param integer $attributeId optionnal The attribute identifier we want to get. If not specify the entire list will be returned
	*	@param string $attributeStatus optionnal The status of element to get into database. Default is set to valid element
	*
	*	@return object $attributes A wordpress database object containing the attribute list
	*/
	function getElement($attributeId = '', $attributeStatus = "'valid', 'moderated'", $whatToSearch = 'id')
	{
		global $wpdb;
		$attributes = array();
		$moreQuery = "";

		if($attributeId != '')
		{
			$moreQuery = "
			AND ATTRIBUTES." . $whatToSearch . " = '" . $attributeId . "' ";
		}

		$query = $wpdb->prepare(
		"SELECT ATTRIBUTES.*, ENTITIES.code as entity
		FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTES
			INNER JOIN " . WPSHOP_DBT_ENTITIES . " AS ENTITIES ON (ENTITIES.id = ATTRIBUTES.entity_id)
		WHERE ATTRIBUTES.status IN (".$attributeStatus.") " . $moreQuery
		);

		/*	Get the query result regarding on the function parameters. If there must be only one result or a collection	*/
		if($attributeId == '')
		{
			$attributes = $wpdb->get_results($query);
		}
		else
		{
			$attributes = $wpdb->get_row($query);
		}

		return $attributes;
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
	*
	*/
	function saveAttributeForEntity($attributeToSet, $entityTypeId, $entityId, $languageId)
	{
		global $wpdb;

		foreach($attributeToSet as $attributeType => $attributeTypeDetails)
		{
			$q = "  ";
			foreach($attributeTypeDetails as $attributeId => $attributeValue)
			{
				$currentAttribute = self::getElement($attributeId, "'valid'", 'code');
				$q .= "('', '" . $entityTypeId . "', '" . $currentAttribute->id . "', '" . $entityId . "', '" . $languageId . "', '" . $attributeValue . "'), " ;
			}
			$q = substr($q, 0, -2);

			$query = $wpdb->prepare(
			"REPLACE INTO " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType . " (value_id, entity_type_id, attribute_id, entity_id, language_id, value) 
				VALUES " . $q . ";");
			$wpdb->query($query);
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
	function getAttributeValueForEntityInSet($attributeType, $attributeId, $entityTypeId, $entityId)
	{
		global $wpdb;
		$attributeValue = '';

		$query = $wpdb->prepare(
			"SELECT value 
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
	function getElementWithAttributeAndValue($table, $entityId, $languageId, $keyForArray = '', $elementId = '', $elementStatus = "'valid', 'moderated'")
	{
		global $wpdb;
		$elements = array();
		$moreQuery = "";

		if($elementId != '')
		{
			$moreQuery = "
			AND ENTITY_TO_GET.id = '" . $elementId . "' ";
		}

		$entityId = $wpdb->escape($entityId);
		$languageId = $wpdb->escape($languageId);
		$query = 
		"SELECT ENTITY_TO_GET.*, 
			ATTR.id as attribute_id, ATTR.data_type, ATTR.backend_table, ATTR.frontend_input, ATTR.frontend_label, ATTR.code AS attribute_code,
			ATTR_VALUE_VARCHAR.value AS attribute_value_varchar, ATTR_VALUE_DECIMAL.value AS attribute_value_decimal, ATTR_VALUE_TEXT.value AS attribute_value_text, ATTR_VALUE_INTEGER.value AS attribute_value_integer, ATTR_VALUE_DATETIME.value AS attribute_value_datetime
		FROM " . $table . " AS ENTITY_TO_GET
			LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS EAD ON ((EAD.attribute_set_id = ENTITY_TO_GET.attribute_set_id) AND (EAD.entity_type_id = '" . $entityId . "'))
			LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR ON ((ATTR.id = EAD.attribute_id) AND (ATTR.status = 'valid'))
			LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR . " AS ATTR_VALUE_VARCHAR ON ((ATTR_VALUE_VARCHAR.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_VARCHAR.attribute_id = ATTR.id) AND (ATTR_VALUE_VARCHAR.entity_id = ENTITY_TO_GET.id) AND (ATTR_VALUE_VARCHAR.language_id = '" . $languageId . "'))
			LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATTR_VALUE_DECIMAL ON ((ATTR_VALUE_DECIMAL.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_DECIMAL.attribute_id = ATTR.id) AND (ATTR_VALUE_DECIMAL.entity_id = ENTITY_TO_GET.id) AND (ATTR_VALUE_DECIMAL.language_id = '" . $languageId . "'))
			LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT . " AS ATTR_VALUE_TEXT ON ((ATTR_VALUE_TEXT.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_TEXT.attribute_id = ATTR.id) AND (ATTR_VALUE_TEXT.entity_id = ENTITY_TO_GET.id) AND (ATTR_VALUE_TEXT.language_id = '" . $languageId . "'))
			LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATTR_VALUE_INTEGER ON ((ATTR_VALUE_INTEGER.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_INTEGER.attribute_id = ATTR.id) AND (ATTR_VALUE_INTEGER.entity_id = ENTITY_TO_GET.id) AND (ATTR_VALUE_INTEGER.language_id = '" . $languageId . "'))
			LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME . " AS ATTR_VALUE_DATETIME ON ((ATTR_VALUE_DATETIME.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_DATETIME.attribute_id = ATTR.id) AND (ATTR_VALUE_DATETIME.entity_id = ENTITY_TO_GET.id) AND (ATTR_VALUE_DATETIME.language_id = '" . $languageId . "'))
		WHERE ENTITY_TO_GET.status IN (" . $elementStatus . ") " . $moreQuery;

		$elementsWithAttributeAndValues = $wpdb->get_results($query);
		foreach($elementsWithAttributeAndValues as $elementDefinition)
		{
			$arrayKey = $elementDefinition->attribute_id;
			if($keyForArray == 'code')
			{
				$arrayKey = $elementDefinition->attribute_code;
			}
			/*	Check for the entity we are getting in order to add additionnal field to result	*/
			switch($table)
			{
				case WPSHOP_DBT_PRODUCT:
				{/*	In case we want to get product informations we add the product reference to the result	*/
					$elements[$elementDefinition->id]['reference'] = $elementDefinition->reference;
				}
				break;
			}
			$elements[$elementDefinition->id]['status'] = $elementDefinition->status;
			$elements[$elementDefinition->id]['creation_date'] = $elementDefinition->creation_date;
			$elements[$elementDefinition->id]['last_update_date'] = $elementDefinition->last_update_date;
			$elements[$elementDefinition->id]['attribute_set_id'] = $elementDefinition->attribute_set_id;
			$elements[$elementDefinition->id]['attributes'][$arrayKey]['data_type'] = $elementDefinition->data_type;
			$elements[$elementDefinition->id]['attributes'][$arrayKey]['backend_table'] = $elementDefinition->backend_table;
			$elements[$elementDefinition->id]['attributes'][$arrayKey]['frontend_input'] = $elementDefinition->frontend_input;
			$elements[$elementDefinition->id]['attributes'][$arrayKey]['frontend_label'] = $elementDefinition->frontend_label;
			$elements[$elementDefinition->id]['attributes'][$arrayKey]['attribute_code'] = $elementDefinition->attribute_code;
			$attributeValueField = 'attribute_value_' . $elementDefinition->data_type;
			if($elementDefinition->data_type == 'static')
			{
				$attributeValueField = 'attribute_value_varchar';
			}
			$elements[$elementDefinition->id]['attributes'][$arrayKey]['value'] = $elementDefinition->$attributeValueField;
		}

		return $elements;
	}

	/**
	*	Return the output for attribute list for a given attribute set and a given item to edit
	*
	*	@param integer $attributeSetId The attribute set to get the attribute for
	*	@param string $currentPageCode Define on wich page we want to get the attribute
	*	@param integer $itemToEdit The item identifier we are working on and we want to get attributes and attributes value for
	*
	*	@return array $tabs An array with the different content to output: tabs and tabs content
	*/
	function getAttributeFieldOutput($attributeSetId, $currentPageCode, $itemToEdit, $outputType = 'tabs')
	{
		$tabs = array();
		$tabs['tabs'] = array();
		$tabs['tabsContent'] = array();
		$tabs['generalTabContent'] = array();

		/*	Get the attribute set details in order to build the product interface	*/
		$productAttributeSetDetails = wpshop_attributes_set::getAttributeSetDetails($attributeSetId, "'valid'");
		if(count($productAttributeSetDetails) > 0)
		{
			foreach($productAttributeSetDetails as $productAttributeSetDetail)
			{
				$currentTabContent = '';
				if(count($productAttributeSetDetail['attribut']) >= 1)
				{
					foreach($productAttributeSetDetail['attribut'] as $attribute)
					{
						$attributeInputDomain = $currentPageCode . '_attribute[' . $attribute->data_type . ']';
						$input_def['id'] = $currentPageCode . '_' . $itemToEdit . '_attribute_' . $attribute->id;
						$input_def['name'] = $attribute->code;
						$input_def['type'] = wpshop_tools::defineFieldType($attribute->data_type);
						$input_name = $attribute->code;
						$input_label = $attribute->frontend_label;
						$input_def['value'] = $attribute->default_value;
						$attributeValue = wpshop_attributes::getAttributeValueForEntityInSet($attribute->data_type, $attribute->id, wpshop_entities::getEntityIdFromCode($currentPageCode), $itemToEdit);
						if($attributeValue->value != '')
						{
							$input_def['value'] = $attributeValue->value;
						}

						if($attribute->data_type != 'static')
						{
							$label = 'for="' . $input_def['id'] . '"';
							if(($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox'))
							{
								$label = '';
							}
							$input = wpshop_form::check_input_type($input_def, $attributeInputDomain);
							if($input_def['type'] != 'hidden')
							{
								$currentTabContent .= '
		<div class="clear" >
			<div class="wpshop_form_label wpshop_' . $currentPageCode . '_' . $input_def['name'] . '_label alignleft" >
				<label ' . $label . ' >' . __($input_label, 'wpshop') . '</label>
			</div>
			<div class="wpshop_form_input wpshop_' . $currentPageCode . '_' . $input_def['name'] . '_input alignleft" >
				' . $input . '
			</div>
		</div>';
							}
							else
							{
								$currentTabContent .= $input;
							}
						}
					}
				}
				if($outputType == 'tabs')
				{
					if($productAttributeSetDetail['code'] != 'general')
					{
						$tabs['tabs'][$productAttributeSetDetail['code']] = '
			<li><a href="#wpshop_' . $currentPageCode . '_' . wpshop_tools::slugify($productAttributeSetDetail['name'], array('noAccent')) . '_form" >' . __($productAttributeSetDetail['name'], 'wpshop') . '</a></li>';
						$tabs['tabsContent'][$productAttributeSetDetail['code']] = '
		<div id="wpshop_' . $currentPageCode . '_' . wpshop_tools::slugify($productAttributeSetDetail['name'], array('noAccent')) . '_form" >' . $currentTabContent . '
		</div>';
					}
					else
					{
						$tabs['generalTabContent'][$productAttributeSetDetail['code']] = $currentTabContent;
					}
				}
				elseif($outputType == 'column')
				{
					$currentTabContent = str_replace('wpshop_form_input', 'wpshop_form_input_column', $currentTabContent);
					$currentTabContent = str_replace('wpshop_form_label', 'wpshop_form_label_column', $currentTabContent);
					if($productAttributeSetDetail['code'] != 'general')
					{
						$tabs['columnTitle'][$productAttributeSetDetail['code']] = __($productAttributeSetDetail['name'], 'wpshop');
						$tabs['columnContent'][$productAttributeSetDetail['code']] = $currentTabContent;
					}
					else
					{
						$tabs['generalTabContent'][$productAttributeSetDetail['code']] = $currentTabContent;
					}
				}
			}
		}

		return $tabs;
	}

}