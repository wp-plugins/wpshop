<?php
/**
* Plugin categories methods definer
* 
*	Define the different method and variable used to manage product categories
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/


/**
 * Define the different method and variable used to manage product categories
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_categories
{	
	/**
	*	Define the database table used in the current class
	*/
	const dbTable = WPSHOP_DBT_CATEGORY;
	/**
	*	Define the url listing slug used in the current class
	*/
	const urlSlugListing = WPSHOP_URL_SLUG_CATEGORY_LISTING;
	/**
	*	Define the url edition slug used in the current class
	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_CATEGORY_EDITION;
	/**
	*	Define the current entity code
	*/
	const currentPageCode = 'product_category';
	/**
	*	Define the page title
	*/
	const pageContentTitle = 'Cat&eacute;gories';
	/**
	*	Define the page title when adding an attribute
	*/
	const pageAddingTitle = 'Ajouter une cat&eacute;gorie';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageEditingTitle = '&Eacute;diter la cat&eacute;gorie "%s"';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageTitle = 'Liste des cat&eacute;gories';

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
				$editedItem = wpshop_attributes::getElementWithAttributeAndValue(self::getDbTable(), wpshop_entities::getEntityIdFromCode(self::currentPageCode), 1, 'code', $objectInEdition);
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), $editedItem[$objectInEdition]['attributes']['product_category_name']['value']);
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

		/*	Define the database operation type from action launched by the user	 */
		/*************************				GENERIC				**************************/
		/*************************************************************************/
		if(($pageAction != '') && (($pageAction == 'edit') || ($pageAction == 'editandcontinue')))
		{
			if(current_user_can('wpshop_edit_product_category'))
			{
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				if($pageAction == 'delete')
				{
					if(current_user_can('wpshop_delete_product_category'))
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
			if(current_user_can('wpshop_delete_product_category'))
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
			if(current_user_can('wpshop_add_product_category'))
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
				/*	Update the different attribute for the selected entity	*/
				if(isset($_REQUEST[self::currentPageCode . '_attribute']))
				{
					wpshop_attributes::saveAttributeForEntity($_REQUEST[self::currentPageCode . '_attribute'], wpshop_entities::getEntityIdFromCode(self::currentPageCode), $id, 1);
				}

				/*************************			GENERIC				****************************/
				/*************************************************************************/
				$pageMessage .= '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('L\'enregistrement de %s s\'est d&eacute;roul&eacute; avec succ&eacute;s', 'wpshop'), $elementIdentifierForMessage);
				if(($pageAction == 'edit'))
				{
					wp_redirect(admin_url('admin.php?page=' . self::getListingSlug() . "&action=saveok&saveditem=" . $id));
				}
				elseif(($pageAction == 'add') || ($pageAction == 'save'))
				{
					wp_redirect(admin_url('admin.php?page=' . self::getEditionSlug() . "&action=edit&id=" . $id));
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
		$tableSummary = __('Existing categories listing', 'wpshop');
		$tableTitles = array();
		$tableTitles[] = __('Nom de la cat&eacute;gorie', 'wpshop');
		$tableTitles[] = __('Statut', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_name_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_status_column';

		$line = 0;
		$elementList = wpshop_attributes::getElementWithAttributeAndValue(self::getDbTable(), wpshop_entities::getEntityIdFromCode(self::currentPageCode), 1, 'code');
		if(count($elementList) > 0)
		{
			foreach($elementList as $elementId => $elementDetails)
			{
				$tableRowsId[$line] = self::getDbTable() . '_' . $elementId;

				$elementLabel = $elementDetails['attributes']['product_category_name']['value'];
				$subRowActions = '';
				if(current_user_can('wpshop_edit_product_category'))
				{
					$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $elementId);
					$subRowActions .= '
		<a href="' . $editAction . '" >' . __('Modifier', 'wpshop') . '</a>';
					$elementLabel = '<a href="' . $editAction . '" >' . $elementDetails['attributes']['product_category_name']['value']  . '</a>';
				}
				elseif(current_user_can('wpshop_view_product_category_details'))
				{
					$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $elementId);
					$subRowActions .= '
		<a href="' . $editAction . '" >' . __('Voir', 'wpshop') . '</a>';
					$elementLabel = '<a href="' . $editAction . '" >' . $elementDetails['attributes']['product_category_name']['value'] . '</a>';
				}
				if(current_user_can('wpshop_delete_product_category'))
				{
					if($subRowActions != '')
					{
						$subRowActions .= '&nbsp;|&nbsp;';
					}
					$subRowActions .= '
		<a href="' . admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=delete&amp;id=' . $elementId). '" >' . __('Supprimer', 'wpshop') . '</a>';
				}
				$rowActions = '
	<div id="rowAction' . $elementId . '" class="wpshopRowAction" >' . $subRowActions . '
	</div>';

				unset($tableRowValue);
				$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' => $elementLabel . $rowActions);
				$tableRowValue[] = array('class' => self::currentPageCode . '_status_cell', 'value' => __($elementDetails['status'], 'wpshop'));
				$tableRows[] = $tableRowValue;

				$line++;
			}
		}
		else
		{
			$subRowActions = '';
			if(current_user_can('wpshop_add_product_category'))
			{
				$subRowActions .= '
	<a href="' . admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=add') . '" >' . __('Ajouter', 'wpshop') . '</a>';
			}
			$rowActions = '
	<div id="rowAction' . $element->id . '" class="wpshopRowAction" >' . $subRowActions . '
	</div>';
			$tableRowsId[] = self::getDbTable() . '_noResult';
			unset($tableRowValue);
			$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' => __('Aucune cat&eacute;gories existante', 'wpshop') . $rowActions);
			$tableRowValue[] = array('class' => self::currentPageCode . '_status_cell', 'value' => '');
			$tableRows[] = $tableRowValue;
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

		$entitySetList = wpshop_attributes_set::getAttributeListForEntity(wpshop_entities::getEntityIdFromCode(self::currentPageCode));
		$attributeSetForCategories = wpshop_attributes_set::getElement(self::currentPageCode, "'valid'", 'entity_code');
		$attributeSetId = $attributeSetForCategories->id;
		$editedItem = '';
		if($itemToEdit != '')
		{
			$editedItem = self::getElement($itemToEdit);
			$attributeSetId = $editedItem->attribute_set_id;
		}

		/*	Add the content of the general tab information	*/
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

			$input_def['value'] = $currentFieldValue;
			if($input_def['name'] == 'attribute_set_id')
			{
				if((count($entitySetList) > 1) && (!is_object($editedItem)))
				{
					$input_def['type'] = 'select';
					$input_def['possible_value'] = $entitySetList;
				}
				else
				{
					$input_def['type'] = 'hidden';
					$input_def['value'] = $entitySetList[0]->id;
				}
			}
			$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());

			if($input_def['type'] != 'hidden')
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

		/*	Get the existing categories for the tree composition	*/
		// getElement($elementId = '', $elementStatus = "'valid', 'moderated'")

		/*	Add the additionnal content when edition mode is in progress	*/
		/*	Get the attribute set details in order to build the product interface	*/
		$currentTabContent = wpshop_attributes::getAttributeFieldOutput($attributeSetId, self::currentPageCode, $itemToEdit);
		if(is_object($editedItem))
		{
			$moreTabs .= implode('', $currentTabContent['tabs']);
			$moreTabsContent .= implode('', $currentTabContent['tabsContent']);;
		}
		$the_form_general_content .= implode('', $currentTabContent['generalTabContent']);

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="" enctype="multipart/form-data" >
' . wpshop_form::form_input(self::getDbTable() . '_action', self::getDbTable() . '_action', (isset($_REQUEST['action']) && ($_REQUEST['action'] != '') ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'save') , 'hidden') . '
' . wpshop_form::form_input(self::getDbTable() . 'form_has_modification', self::getDbTable() . 'form_has_modification', 'no' , 'hidden') . '
<div id="wpshopFormManagementContainer" >
	<ul>
		<li><a href="#wpshop_' . self::currentPageCode . '_main_infos_form" >' . __('G&eacute;n&eacute;ral', 'wpshop') . '</a></li>' . $moreTabs . '
	</ul>' . $the_form_content_hidden .'
	<div id="wpshop_' . self::currentPageCode . '_main_infos_form" >' . $the_form_general_content . '
	</div>' . $moreTabsContent . '
</div>
</form>
<script type="text/javascript" >
	(function(){
		wpshopMainInterface("' . self::getDbTable() . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir quitter cette page? Vous perdrez toutes les modification que vous aurez effectu&eacute;es', 'wpshop') . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer cette cat&eacute;gorie?', 'wpshop') . '");
	})(wpshop);
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
			if(current_user_can('wpshop_add_product_category'))
			{
				$currentPageButton .= '<input type="button" class="button-primary" id="add" name="add" value="' . __('Ajouter', 'wpshop') . '" />';
			}
		}
		elseif(current_user_can('wpshop_edit_product_category'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="save" name="save" value="' . __('Enregistrer', 'wpshop') . '" /><input type="button" class="button-primary" id="saveandcontinue" name="saveandcontinue" value="' . __('Enregistrer et continuer l\'&eacute;dition', 'wpshop') . '" />';
		}
		if(current_user_can('wpshop_delete_product_category') && ($action != 'add'))
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
			AND CATEGORY.id = '" . $elementId . "' ";
		}

		$query = $wpdb->prepare(
		"SELECT CATEGORY.*
		FROM " . self::getDbTable() . " AS CATEGORY
		WHERE CATEGORY.status IN (".$elementStatus.") " . $moreQuery
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
	*	Get the existing relation between product and categories for a given product
	*
	*	@param integer $productId The product identifier we want to get the related categories
	*
	*	@return array $associatedCategories An array with the the relation and the different informations about this relation
	*/
	function getAssociatedCategories($productId)
	{
		global $wpdb;
		$associatedCategories = array();

		$query = $wpdb->prepare(
			"SELECT * 
			FROM " . WPSHOP_DBT_CATEGORY_PRODUCT_DETAILS . "
			WHERE id_product = %d
				AND status = 'valid' ", $productId);
		$associatedCategoriesResult = $wpdb->get_results($query);

		foreach($associatedCategoriesResult as $associatedCategory)
		{
			$associatedCategories[$associatedCategory->id_category]['id'] = $associatedCategory->id;
			$associatedCategories[$associatedCategory->id_category]['id_category'] = $associatedCategory->id_category;
			$associatedCategories[$associatedCategory->id_category]['id_product'] = $associatedCategory->id_product;
			$associatedCategories[$associatedCategory->id_category]['status'] = $associatedCategory->status;
			$associatedCategories[$associatedCategory->id_category]['position'] = $associatedCategory->position;
			$associatedCategories[$associatedCategory->id_category]['attribution_date'] = $associatedCategory->attribution_date;
			$associatedCategories[$associatedCategory->id_category]['attribution_user_id'] = $associatedCategory->attribution_user_id;
			$associatedCategories[$associatedCategory->id_category]['unassigning_date'] = $associatedCategory->unassigning_date;
			$associatedCategories[$associatedCategory->id_category]['unassigning_user_id'] = $associatedCategory->unassigning_user_id;

			/*	Get the category information	*/
			$categoryInformations = wpshop_attributes::getElementWithAttributeAndValue(self::getDbTable(), wpshop_entities::getEntityIdFromCode('product_category'), 1, 'code', $associatedCategory->id_category, "'valid'");
			$associatedCategories[$associatedCategory->id_category]['category_name'] = $categoryInformations[$associatedCategory->id_category]['attributes']['product_category_name']['value'];
			$associatedCategories[$associatedCategory->id_category]['category_description'] = $categoryInformations[$associatedCategory->id_category]['attributes']['product_category_description']['value'];
		}

		return $associatedCategories;
	}

	/**
	*	Set the relation between categories and products
	*
	*	@param array $categoriesToSet The list of categories to relate to the product
	*	@param integer $productId The product identifier we want to set the category relation for
	*
	*	
	*/
	function updateAssociatedCategories($categoriesToSet, $productId)
	{
		global $wpdb;
		global $current_user;
		$categoryAssociation = $categoryUnAssociation = "  ";

		$associatedCategories = wpshop_categories::getAssociatedCategories($productId);
		foreach($associatedCategories as $associatedCategoryId => $associatedCategoryDefinition)
		{
			if(!isset($categoriesToSet[$associatedCategoryId]))
			{
				$query = "UPDATE " . WPSHOP_DBT_CATEGORY_PRODUCT_DETAILS . " SET status = 'deleted', unassigning_date = NOW(), unassigning_user_id = '" . $current_user->ID . "' WHERE id = '" . $wpdb->escape($associatedCategoryDefinition['id']) . "';  ";
				$wpdb->query($query);
			}
		}

		foreach($categoriesToSet as $categoryId)
		{
			if(!isset($associatedCategories[$categoryId]))
			{
				$categoryAssociation .= "('" . $wpdb->escape($categoryId) . "', '" . $wpdb->escape($productId) . "', 'valid', NOW(), '" . $current_user->ID . "'), ";
			}
		}

		$associationSubQuery = trim(substr($categoryAssociation, 0, -2));
		if($associationSubQuery != "")
		{
			$query = $wpdb->prepare("INSERT INTO " . WPSHOP_DBT_CATEGORY_PRODUCT_DETAILS . " (id_category, id_product, status, attribution_date, attribution_user_id) VALUES ") . $associationSubQuery;
			$wpdb->query($query);
		}
	}

	/**
	*	Return the product's list of a given category
	*
	*	@param integer $categoryId The category identifier we want to get the product for
	*
	*	@return object $productsOfCategory A wordpress database object containing the list of product affected to the category
	*/
	function getProductOfCategory($categoryId)
	{
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT * 
			FROM " . WPSHOP_DBT_CATEGORY_PRODUCT_DETAILS . "
			WHERE status = 'valid' 
				AND id_category = %d "
			,$categoryId);
		$productsOfCategory = $wpdb->get_results($query);

		return $productsOfCategory;
	}

}