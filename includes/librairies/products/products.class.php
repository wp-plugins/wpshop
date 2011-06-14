<?php
/**
* Plugin products methods definer
* 
*	Define the different method and variable used to manage products
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method and variable used to manage products
* @package wpshop
* @subpackage librairies
*/
class wpshop_products
{	
	/**
	*	Define the database table used in the current class
	*/
	const dbTable = WPSHOP_DBT_PRODUCT;
	/**
	*	Define the url listing slug used in the current class
	*/
	const urlSlugListing = WPSHOP_URL_SLUG_PRODUCT_LISTING;
	/**
	*	Define the url edition slug used in the current class
	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_PRODUCT_EDITION;
	/**
	*	Define the current entity code
	*/
	const currentPageCode = 'product';
	/**
	*	Define the page title
	*/
	const pageTitle = 'Produits';
	/**
	*	Define the page title when adding an element
	*/
	const pageAddingTitle = 'Ajouter un produit';
	/**
	*	Define the page title when editing an element
	*/
	const pageEditingTitle = '&Eacute;diter le produit "%s"';

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
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), $editedItem[$objectInEdition]['attributes']['product_name']['value']  . '&nbsp;(' . __('r&eacute;f.', 'wpshop') . $editedItem[$objectInEdition]['reference'] . ')');
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
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('L\'enregistrement de %s s\'est d&eacute;roul&eacute; avec succ&eacute;s', 'wpshop'), '<span class="bold" >' . $editedElement->reference . '</span>');
		}
		elseif(($action != '') && ($action == 'deleteok') && ($saveditem > 0))
		{
			$editedElement = self::getElement($saveditem, "'deleted'");
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s a &eacute;t&eacute; supprim&eacute;e avec succ&eacute;s', 'wpshop'), '<span class="bold" >' . $editedElement->reference . '</span>');
		}

		/*	Define the database operation type from action launched by the user	 */
		/*************************			GENERIC				****************************/
		/*************************************************************************/
		if(($pageAction != '') && (($pageAction == 'edit') || ($pageAction == 'editandcontinue')))
		{
			if(current_user_can('wpshop_edit_product'))
			{
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				if($pageAction == 'delete')
				{
					if(current_user_can('wpshop_delete_product'))
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
			if(current_user_can('wpshop_delete_product'))
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
			if(current_user_can('wpshop_add_product'))
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
			$elementIdentifierForMessage = '<span class="bold" >' . $_REQUEST[self::getDbTable()]['reference'] . '</span>';
			if($actionResult == 'error')
			{/*	CHANGE HERE FOR SPECIFIC CASE	*/
				if(isset($wpdb->last_error) && (substr($wpdb->last_error, 0, 15) == "Duplicate entry"))
				{
					$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . sprintf(__('Une erreur est survenue lors de l\'enregistrement de %s, cette r&eacute;f&eacute;rence existe d&eacute;j&agrave; et ne peut &ecirc;tre utilis&eacute;e plusieurs fois', 'wpshop'), $elementIdentifierForMessage);
				}
				else
				{
					$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . sprintf(__('Une erreur est survenue lors de l\'enregistrement de %s', 'wpshop'), $elementIdentifierForMessage);
					if(wpshop_DEBUG)
					{
						$pageMessage .= '<br/>' . $wpdb->last_error;
					}
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

				/*	Assign product to categories	*/
				if(isset($_REQUEST['product_category']))
				{
					wpshop_categories::updateAssociatedCategories($_REQUEST['product_category'], $id);
				}

				/*	Set the options for the different associated documents	*/
				if(isset($_REQUEST[WPSHOP_DBT_DOCUMENT . '_options']) && is_array($_REQUEST[WPSHOP_DBT_DOCUMENT . '_options']))
				{
					/*	Set the default document	*/
					foreach($_REQUEST[WPSHOP_DBT_DOCUMENT . '_options']['default'] as $documentDomain => $documentId)
					{
						wpshop_document::setDefaultDocumentForElement(self::currentPageCode, $id, $documentId, str_replace('default', '', $documentDomain));
					}
					
					/*	Set the status of document link to specify the displayed document into the frontend gallery	*/
					$documentCategoryList = array();
					/*	Read informations sent by the user, and stored them into a temporary array	*/
					foreach($_REQUEST[WPSHOP_DBT_DOCUMENT . '_options']['frontenddisplay'] as $documentStatusIndex => $documentStatus)
					{
						$statusComponent = explode('_status_', $documentStatusIndex);
						$documentCategoryList[$statusComponent[0]] .= "'" . $statusComponent[1] . "', ";
					}
					/*	If the temporary array is not empty, we read it.	*/
					if(is_array($documentCategoryList) && (count($documentCategoryList) > 0))
					{
						foreach($documentCategoryList as $documentCategory => $documentIdentifiers)
						{
							/*	For each document category on the current page, if the identifier list is longer than 2 caracters, we store the new value for document link status	*/
							if(strlen($documentIdentifiers) > 2)
							{
								wpshop_document::setStatusHideForElement(self::currentPageCode, $id, substr($documentIdentifiers, 0, -2), $documentCategory);
							}
						}
					}
				}

				/*	Update informations about the associated documents 	*/
				

				/*************************			GENERIC				****************************/
				/*************************************************************************/
				$pageMessage .= '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('L\'enregistrement de %s s\'est d&eacute;roul&eacute; avec succ&eacute;s', 'wpshop'), $elementIdentifierForMessage);
				if(($pageAction == 'edit'))
				{
					wp_redirect(admin_url('admin.php?page=' . self::getListingSlug() . "&action=saveok&saveditem=" . $id));
				}
				elseif(($pageAction == 'add') || ($pageAction == 'save'))
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
		$tableSummary = __('Existing products listing', 'wpshop');
		$tableTitles = array();
		$tableTitles[] = __('R&eacute;f&eacute;rence', 'wpshop');
		$tableTitles[] = __('Nom', 'wpshop');
		$tableTitles[] = __('Statut', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_ref_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_name_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_status_column';

		$line = 0;
		$elementList = wpshop_attributes::getElementWithAttributeAndValue(self::getDbTable(), wpshop_entities::getEntityIdFromCode(self::currentPageCode), 1, 'code');
		if(count($elementList) > 0)
		{
			foreach($elementList as $elementId => $elementInformations)
			{
				$tableRowsId[$line] = self::getDbTable() . '_' . $elementId;

				$elementLabel = $elementInformations['reference'];
				$subRowActions = '';
				if(current_user_can('wpshop_edit_product'))
				{
					$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $elementId);
					$subRowActions .= '
		<a href="' . $editAction . '" >' . __('Modifier', 'wpshop') . '</a>';
					$elementLabel = '<a href="' . $editAction . '" >' . $elementInformations['reference']  . '</a>';
				}
				elseif(current_user_can('wpshop_view_product_details'))
				{
					$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $elementId);
					$subRowActions .= '
		<a href="' . $editAction . '" >' . __('Voir', 'wpshop') . '</a>';
					$elementLabel = '<a href="' . $editAction . '" >' . $elementInformations['reference']  . '</a>';
				}
				if(current_user_can('wpshop_delete_product'))
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
				$tableRowValue[] = array('class' => self::currentPageCode . '_ref_cell', 'value' => $elementLabel . $rowActions);
				$tableRowValue[] = array('class' => self::currentPageCode . '_name_cell', 'value' => $elementInformations['attributes']['product_name']['value']);
				$tableRowValue[] = array('class' => self::currentPageCode . '_status_cell', 'value' => __($elementInformations['status'], 'wpshop'));
				$tableRows[] = $tableRowValue;

				$line++;
			}
		}
		else
		{
			$subRowActions = '';
			if(current_user_can('wpshop_add_product'))
			{
				$subRowActions .= '
	<a href="' . admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=add') . '" >' . __('Ajouter', 'wpshop') . '</a>';
			}
			$rowActions = '
	<div id="rowAction' . $elementId . '" class="wpshopRowAction" >' . $subRowActions . '
	</div>';
			$tableRowsId[] = self::getDbTable() . '_noResult';
			unset($tableRowValue);
			$tableRowValue[] = array('class' => self::currentPageCode . '_label_cell', 'value' => __('Aucun produits existant', 'wpshop') . $rowActions);
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
		global $wpdb;
		$moreTabs = $moreTabsContent = '';

		$dbFieldList = wpshop_database::fields_to_input(self::getDbTable());
		$entitySetList = wpshop_attributes_set::getAttributeListForEntity(wpshop_entities::getEntityIdFromCode(self::currentPageCode));

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
			$requestFormValue = isset($_REQUEST[self::getDbTable()][$input_def['name']]) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()][$input_def['name']]) : '';
			$currentFieldValue = $input_value;
			if(is_object($editedItem))
			{
				$currentFieldValue = $editedItem->$input_def['name'];
			}
			elseif(($pageAction != '') && ($requestFormValue != '') && ($input_def['name'] != 'icon_path'))
			{
				$currentFieldValue = $requestFormValue;
			}

			$input_def['value'] = $currentFieldValue;
			if(($input_def['name'] == 'reference') && !is_object($editedItem))
			{
				$options = get_option('wpshop_product_options');
				$query = $wpdb->prepare("SELECT MAX(id) as LASTID FROM " . self::getDbTable());
				$lastProductId = $wpdb->get_row($query);
				if($lastProductId->LASTID <=  0)
				{
					$lastProductId->LASTID = 1;
				}
				$input_def['value'] = $options['product_reference_prefix'] . $lastProductId->LASTID;
			}
			if($input_def['name'] == 'attribute_set_id')
			{
				if(count($entitySetList) > 1)
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
				$label = 'for="' . $input_def['name'] . '"';
				if(($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox'))
				{
					$label = '';
				}
				$input = '
			<div class="clear" >
				<div class="wpshop_form_label wpshop_attr_' . $input_def['name'] . '_label alignleft" >
					<label ' . $label . ' >' . __($input_def['name'], 'wpshop') . '</label>
				</div>
				<div class="wpshop_form_input wpshop_attr_' . $input_def['name'] . '_input alignleft" >
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

		/*	Add the additionnal content when edition mode is in progress	*/
		if(is_object($editedItem))
		{
			/*	Get the attribute set details in order to build the product interface	*/
			$currentTabContent = wpshop_attributes::getAttributeFieldOutput($editedItem->attribute_set_id, self::currentPageCode, $itemToEdit);
			$moreTabs .= implode('
		', $currentTabContent['tabs']);
			$moreTabsContent .= implode('
		', $currentTabContent['tabsContent']);
			$the_form_general_content .= implode('
		', $currentTabContent['generalTabContent']);

			/*	Add The category tab for product association	*/
			$linkToAddCategory = '<a href="' . admin_url('admin.php?page=' . wpshop_categories::getEditionSlug() . '&amp;action=add') . '" >' . __('Cr&eacute;er une cat&eacute;gorie', 'wpshop') . '</a>';
			$categoryTabContent = sprintf(__('Aucune cat&eacute;gorie n\'a &eacute;t&eacute; cr&eacute;e pour le moment. %s', 'wpshop'), $linkToAddCategory);
			$associatedCategories = wpshop_categories::getAssociatedCategories($itemToEdit);
			$categoriesList = wpshop_attributes::getElementWithAttributeAndValue(WPSHOP_DBT_CATEGORY, wpshop_entities::getEntityIdFromCode('product_category'), 1, 'code');
			if(count($categoriesList) > 0)
			{
				$categoryTabContent = '';
				foreach($categoriesList as $categoryId => $categoryDefinition)
				{
					$input_def['id'] = self::currentPageCode . '_category_' . $categoryId;
					$input_def['name'] = wpshop_tools::slugify($categoryId, array('noAccent', 'noSpaces', 'lowerCase'));
					$input_def['type'] = 'checkbox';
					$input_def['possible_value'] = $categoryId;
					if(isset($associatedCategories[$categoryId]))
					{
						$input_def['value'] = $categoryId;
					}
					$categoryTabContent .= '
		<div class="clear" >
			<div class="wpshop_product_category_selector wpshop_' . self::currentPageCode . '_' . $input_def['name'] . '_input alignleft" >
				' . wpshop_form::check_input_type($input_def, self::currentPageCode . '_category') . '
				<label for="' . $input_def['id'] . '" >' . __($categoryDefinition['attributes']['product_category_name']['value'], 'wpshop') . '</label>
			</div>
		</div>';
				}
			}
			$moreTabs .= '
		<li><a href="#wpshop_' . self::currentPageCode . '_category_tree" >' . __('Cat&eacute;gories', 'wpshop') . '</a></li>';
			$moreTabsContent .= '
	<div id="wpshop_' . self::currentPageCode . '_category_tree" >' . $categoryTabContent . '
	</div>';

			/*	Add the picture tab	*/
			if(current_user_can('wpshop_view_document') || current_user_can('wpshop_view_document_details'))
			{
			$moreTabs .= '
		<li><a href="#wpshop_' . self::currentPageCode . '_images" >' . __('Images', 'wpshop') . '</a></li>';
			$moreTabsContent .= '
	<div id="wpshop_' . self::currentPageCode . '_images" >
		' . wpshop_document::getFormButton(self::currentPageCode . '_picture', self::currentPageCode, $itemToEdit, str_replace('\\', '/', WP_CONTENT_DIR . WPSHOP_UPLOAD_DIR . WPSHOP_PLUGIN_DIR . '/' . self::currentPageCode . '/' . $itemToEdit . '/'), 'product_picture_upload', WPSHOP_AUTORISED_PICTURE_EXTENSION, true, '', __('Ajouter une image au produit', 'wpshop')) . '
		<div id="' . self::currentPageCode . '_pictureContainer" >' . wpshop_document::getAssociatedDocument(self::currentPageCode, $itemToEdit, self::currentPageCode . '_picture', "'valid'", "'valid', 'moderated'") . '</div>
	</div>';
			}

			/*	Add the document tab	*/
			if(current_user_can('wpshop_view_document') || current_user_can('wpshop_view_document_details'))
			{
			$moreTabs .= '
		<li><a href="#wpshop_' . self::currentPageCode . '_documents" >' . __('Documents', 'wpshop') . '</a></li>';
			$moreTabsContent .= '
	<div id="wpshop_' . self::currentPageCode . '_documents" >
		' . wpshop_document::getFormButton(self::currentPageCode . '_document', self::currentPageCode, $itemToEdit, str_replace('\\', '/', WP_CONTENT_DIR . WPSHOP_UPLOAD_DIR . WPSHOP_PLUGIN_DIR . '/' . self::currentPageCode . '/' . $itemToEdit . '/'), 'product_document_upload', WPSHOP_AUTORISED_DOCUMENTS_EXTENSION, true, '', __('Ajouter un document au produit', 'wpshop')) . '
		<div id="' . self::currentPageCode . '_documentContainer" >' . wpshop_document::getAssociatedDocument(self::currentPageCode, $itemToEdit, self::currentPageCode . '_document', "'valid'", "'valid', 'moderated'") . '</div>
	</div>';
			}
		}

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="" enctype="multipart/form-data" >
' . wpshop_form::form_input(self::getDbTable() . '_action', self::getDbTable() . '_action', (isset($_REQUEST['action']) && ($_REQUEST['action'] != '') ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'save') , 'hidden') . '
' . wpshop_form::form_input(self::getDbTable() . 'form_has_modification', self::getDbTable() . 'form_has_modification', 'no' , 'hidden') . '
<div id="wpshopFormManagementContainer" >
	<ul>
		<li><a href="#wpshop_' . self::currentPageCode . '_main_infos_form" >' . __('G&eacute;n&eacute;ral', 'wpshop') . '</a></li>' . $moreTabs . '
	</ul>
	<div id="wpshop_' . self::currentPageCode . '_main_infos_form" >' . $the_form_content_hidden . $the_form_general_content . '
	</div>' . $moreTabsContent . '
</div>
</form>
<script type="text/javascript" >
	(function(){
		wpshopMainInterface("' . self::getDbTable() . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir quitter cette page? Vous perdrez toutes les modification que vous aurez effectu&eacute;es', 'wpshop') . '", "' . __('&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce produit?', 'wpshop') . '");

		jQuery("a[rel=product_gallery]").fancybox({
			"transitionIn"		: "none",
			"transitionOut"		: "none",
			"titlePosition" 	: "over",
			"titleFormat"		: function(title, currentArray, currentIndex, currentOpts){
				return "&nbsp;' . __('Image', 'wpshop') . ' " + (currentIndex + 1) + " / " + currentArray.length + (title.length ? " &nbsp; " + title : "");
			}
		});
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
			if(current_user_can('wpshop_add_product'))
			{
				$currentPageButton .= '<input type="button" class="button-primary" id="add" name="add" value="' . __('Ajouter', 'wpshop') . '" />';
			}
		}
		elseif(current_user_can('wpshop_edit_product'))
		{
			$currentPageButton .= '<input type="button" class="button-primary" id="save" name="save" value="' . __('Enregistrer', 'wpshop') . '" /><input type="button" class="button-primary" id="saveandcontinue" name="saveandcontinue" value="' . __('Enregistrer et continuer l\'&eacute;dition', 'wpshop') . '" />';
		}
		if(current_user_can('wpshop_delete_product') && ($action != 'add'))
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
			AND PRODUCT.id = '" . $elementId . "' ";
		}

		$query = $wpdb->prepare(
		"SELECT PRODUCT.*
		FROM " . self::getDbTable() . " AS PRODUCT
		WHERE PRODUCT.status IN (".$elementStatus.") " . $moreQuery
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

}