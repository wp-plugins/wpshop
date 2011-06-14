<?php
/**
* Plugin language manager
* 
* Define the different method to manage the different language into the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different language into the plugin
* @package wpshop
* @subpackage librairies
*/
class wpshop_language
{	
	/**
	*	Define the database table used in the current class
	*/
	const dbTable = WPSHOP_DBT_LANGUAGE;
	/**
	*	Define the url listing slug used in the current class
	*/
	const urlSlugListing = WPSHOP_URL_SLUG_LANGUAGE;
	/**
	*	Define the url edition slug used in the current class
	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_LANGUAGE_EDITION;
	/**
	*	Define the current entity code
	*/
	const currentPageCode = 'language';
	/**
	*	Define the page title
	*/
	const pageContentTitle = 'Langues';
	/**
	*	Define the page title when adding an attribute
	*/
	const pageAddingTitle = 'Ajouter une langue';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageEditingTitle = '&Eacute;diter la langue "%s"';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageTitle = 'Liste des langues';

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
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), $editedItem->name);
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
		$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$id = isset($_REQUEST[self::getDbTable()]['id']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['id']) : '';

		/*	Start definition of output message when action is doing on another page	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/****************************************************************************/
		$saveditem = isset($_REQUEST['saveditem']) ? wpshop_tools::varSanitizer($_REQUEST['saveditem']) : '';
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

		/*	If the current language is set as default language we unset the current default	*/
		if($_REQUEST[self::getDbTable()]['is_default'] == 'yes')
		{
			self::unsetDefaultLang();
		}

		/*	Define the database operation type from action launched by the user	 */
		/*************************				GENERIC				**************************/
		/*************************************************************************/
		if(($pageAction != '') && (($pageAction == 'edit') || ($pageAction == 'editandcontinue') || ($pageAction == 'delete')))
		{
			if(current_user_can('wpshop_edit_language'))
			{
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				if($pageAction == 'delete')
				{
					if(current_user_can('wpshop_delete_language'))
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
			if(current_user_can('wpshop_delete_language'))
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
			if(current_user_can('wpshop_delete_language'))
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
				$icon_path = '';
				if(isset($_FILES[self::getDbTable() . 'icon']))
				{
					$icon_path = wpshop_document::uploadFileHttpMethod($_FILES[self::getDbTable() . 'icon'], WPSHOP_UPLOAD_DIR . WPSHOP_IMAGE_URL . 'languages/', 'icon_path');
				}
				if($icon_path != '')
				{
					$_REQUEST[self::getDbTable()]['icon_path'] = $icon_path;
				}
				wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());

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
		$tableSummary = __('Existing languages listing', 'wpshop');
		$tableTitles = array();
		$tableTitles[] = __('Nom de la langue', 'wpshop');
		$tableTitles[] = __('Code iso', 'wpshop');
		$tableTitles[] = __('Ic&ocirc;ne', 'wpshop');
		$tableTitles[] = __('Langue par d&eacute;faut', 'wpshop');
		$tableTitles[] = __('Statut', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_name_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_isocode_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_icon_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_default_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_status_column';

		$line = 0;
		$elementList = self::getElement();
		foreach($elementList as $element)
		{
			$tableRowsId[$line] = self::getDbTable() . '_' . $element->id;

			$elementLabel = $element->name;
			$subRowActions = '';
			if(current_user_can('wpshop_edit_language'))
			{
				$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $element->id);
				$subRowActions .= '
	<a href="' . $editAction . '" >' . __('Modifier', 'wpshop') . '</a>';
				$elementLabel = '<a href="' . $editAction . '" >' . $element->name  . '</a>';
			}
			elseif(current_user_can('wpshop_view_language_details'))
			{
				$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $element->id);
				$subRowActions .= '
	<a href="' . $editAction . '" >' . __('Voir', 'wpshop') . '</a>';
				$elementLabel = '<a href="' . $editAction . '" >' . $element->name  . '</a>';
			}
			if(current_user_can('wpshop_delete_language'))
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

			$languageIcon = __('Aucune ic&ocirc;ne attribut&eacute;e', 'wpshop');
			if(wpshop_document::checkIfExist('file', WP_CONTENT_DIR, $element->icon_path))
			{
				$languageIcon = '<img class="languageIconAdmin" src="' . WP_CONTENT_URL . $element->icon_path . '" alt="language icon ' . $element->name . '" />';
			}

			unset($tableRowValue);
			$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' => $elementLabel . $rowActions);
			$tableRowValue[] = array('class' => self::currentPageCode . '_name_cell', 'value' => __($element->code, 'wpshop'));
			$tableRowValue[] = array('class' => self::currentPageCode . '_code_cell', 'value' => $languageIcon);
			$tableRowValue[] = array('class' => self::currentPageCode . '_default_cell', 'value' => __($element->is_default, 'wpshop'));
			$tableRowValue[] = array('class' => self::currentPageCode . '_status_cell', 'value' => __($element->status, 'wpshop'));
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

			$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
			$requestFormValue = isset($_REQUEST[self::getDbTable()][$input_name]) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()][$input_name]) : '';
			$currentFieldValue = $input_value;
			if(is_object($editedItem))
			{
				$currentFieldValue = $editedItem->$input_name;
			}
			elseif(($pageAction != '') && ($requestFormValue != '') && ($input_name != 'icon_path'))
			{
				$currentFieldValue = $requestFormValue;
			}

			if($input_name == 'icon_path')
			{
				$input_def['value'] = $currentFieldValue;
				$languageIcon = __('Aucune ic&ocirc;ne attribut&eacute;e', 'wpshop');
				if(wpshop_document::checkIfExist('file', WP_CONTENT_DIR, $currentFieldValue))
				{
					$languageIcon = '<img class="languageIconAdmin" src="' . WP_CONTENT_URL . $currentFieldValue . '" alt="language icon ' . $editedItem->name . '" />';
				}
				$input_def['name'] = $input_name;

				$input_def['type'] = 'file';
				$the_input = $languageIcon . wpshop_form::check_input_type($input_def, self::getDbTable() . 'icon');
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
				if(($editedItem->is_default != 'yes') || (($editedItem->is_default == 'yes') && ($input_name != 'status')))
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

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="" enctype="multipart/form-data" >
' . wpshop_form::form_input(self::getDbTable() . '_action', self::getDbTable() . '_action', (isset($_REQUEST['action']) && ($_REQUEST['action'] != '') ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'save') , 'hidden') . '
' . wpshop_form::form_input(self::getDbTable() . 'form_has_modification', self::getDbTable() . 'form_has_modification', 'no' , 'hidden') . '
<div id="wpshopFormManagementContainer" >
	<ul>
		<li><a href="#wpshop_' . self::currentPageCode . '_main_infos_form" >' . __('G&eacute;n&eacute;ral', 'wpshop') . '</a></li>
	</ul>' . $the_form_content_hidden .'
	<div id="wpshop_' . self::currentPageCode . '_main_infos_form" >' . $the_form_general_content . '
	</div>
</div>
</form>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		wpshopMainInterface("' . self::getDbTable() . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir quitter cette page? Vous perdrez toutes les modification que vous aurez effectu&eacute;es', 'wpshop') . '");

		wpshop("#delete").click(function(){
			wpshop("#' . self::getDbTable() . '_action").val("delete");
			deleteLanguage();
		});
		if(wpshop("#' . self::getDbTable() . '_action").val() == "delete"){
			deleteLanguage();
		}
		function deleteLanguage(){
			if(confirm(wpshopConvertAccentTojs("' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cette langue?', 'wpshop') . '"))){
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
			if(current_user_can('wpshop_add_language'))
			{
				$currentPageButton .= '<input type="button" class="button-primary" id="add" name="add" value="' . __('Ajouter', 'wpshop') . '" />';
			}
		}
		elseif(current_user_can('wpshop_edit_language'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="save" name="save" value="' . __('Enregistrer', 'wpshop') . '" /><input type="button" class="button-primary" id="saveandcontinue" name="saveandcontinue" value="' . __('Enregistrer et continuer l\'&eacute;dition', 'wpshop') . '" />';
		}
		if(current_user_can('wpshop_delete_language') && ($action != 'add'))
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
	function getElement($elementId = '', $elementStatus = "'valid', 'moderated'")
	{
		global $wpdb;
		$elements = array();
		$moreQuery = "";

		if($elementId != '')
		{
			$moreQuery = "
			AND LANGUAGE.id = '" . $elementId . "' ";
		}

		$query = $wpdb->prepare(
		"SELECT LANGUAGE.*
		FROM " . self::getDbTable() . " AS LANGUAGE
		WHERE LANGUAGE.status IN (".$elementStatus.") " . $moreQuery
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
	*	Unset the default language for all existing languages
	*
	* return void
	*/
	function unsetDefaultLang()
	{
		global $wpdb;

		$query = $wpdb->prepare("UPDATE " . self::getDbTable() . " SET is_default = 'no'");
		$wpdb->query($query);

		return;
	}

}