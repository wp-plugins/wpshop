<?php
/**
* Plugin document manager
* 
*	Define the different method to manage the different document into the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/


/**
* Define the different method to manage the different document into the plugin
* @package wpshop
* @subpackage librairies
*/
class wpshop_document
{	
	/**
	*	Define the database table used in the current class
	*/
	const dbTable = WPSHOP_DBT_DOCUMENT;
	/**
	*	Define the url listing slug used in the current class
	*/
	const urlSlugListing = WPSHOP_URL_SLUG_DOCUMENT_LISTING;
	/**
	*	Define the url edition slug used in the current class
	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_DOCUMENT_EDITION;
	/**
	*	Define the current entity code
	*/
	const currentPageCode = 'document';
	/**
	*	Define the page title
	*/
	const pageContentTitle = 'Documents';
	/**
	*	Define the page title when adding an attribute
	*/
	const pageAddingTitle = 'Ajouter un document';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageEditingTitle = '&Eacute;diter le document "%s"';
	/**
	*	Define the page title when editing an attribute
	*/
	const pageTitle = 'Liste des documents';

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
	*	Return the list page content, containing the table that present the item list
	*
	*	@return string $listItemOutput The html code that output the item list
	*/
	function elementList($elementList = '', $documentSection = '')
	{
		$listItemOutput = '';

		$dbFieldList = wpshop_database::fields_to_input(self::getDbTable());
		$entitySetList = wpshop_attributes_set::getAttributeListForEntity(wpshop_entities::getEntityIdFromCode(self::currentPageCode));

		/*	Start the table definition	*/
		$tableId = self::getDbTable() . $documentSection . '_list';
		$tableSummary = __('Existing document listing', 'wpshop');
		$tableTitles = array();
		$tableTitles[] = __('Aper&ccedil;u', 'wpshop');
		$tableTitles[] = __('Options', 'wpshop');
		$tableTitles[] = __('Informations G&eacute;n&eacute;rales', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_file_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_option_column';
		$tableClasses[] = 'wpshop_' . self::currentPageCode . '_infos_column';

		$line = 0;
		if($elementList == '')
		{
			$elementList = self::getElement();
		}
		if(is_array($elementList) && (count($elementList) > 0))
		{
			foreach($elementList as $element)
			{
				$tableRowsId[$line] = self::getDbTable() . '_' . $element->id;

				/*	Check if the file exist and select the type of preview to put	*/
				$elementPreview = __('Fichier non trouv&eacute;', 'wpshop');
				if(self::checkIfExist('file', WP_CONTENT_DIR . $element->filepath, $element->filename))
				{
					$fileUrl = WP_CONTENT_URL . $element->filepath . $element->filename;
					$elementPreview = self::getFileOutputType($fileUrl, $element, 'preview');
				}

				$subRowActions = '';
				if(current_user_can('wpshop_edit_document'))
				{
					$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $element->id);
					$subRowActions .= '
		<a href="' . $editAction . '" >' . __('Modifier', 'wpshop') . '</a>';
					$elementLabel = '<a href="' . $editAction . '" >' . $elementPreview  . '</a>';
				}
				elseif(current_user_can('wpshop_view_document_details'))
				{
					$editAction = admin_url('admin.php?page=' . self::getEditionSlug() . '&amp;action=edit&amp;id=' . $element->id);
					$subRowActions .= '
		<a href="' . $editAction . '" >' . __('Voir', 'wpshop') . '</a>';
					$elementLabel = '<a href="' . $editAction . '" >' . $elementPreview  . '</a>';
				}
				if(current_user_can('wpshop_delete_document'))
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

				$elementOptions = '';
				{/*	Define an input to set the document as default document for the current element in it's category	*/
					$input_def = array();
					$input_def['possible_value'] = $element->id;
					if($element->default_for_element == 'yes')
					{
						$input_def['value'] = $element->id;
					}
					$input_def['type'] = 'radio';
					$input_def['id'] = 'isDefault_' . $element->id;
					$input_def['name'] = 'default' . $element->category;
					if(strpos($element->category , '_picture'))
					{
						$defaultText = __('Photo principale pour l\'&eacute;l&eacute;ment', 'wpshop');
					}
					else
					{
						$defaultText = __('D&eacute;finir comme principal', 'wpshop');
					}
					$elementOptions .= wpshop_form::check_input_type($input_def, self::getDbTable() . '_options[default]') . '<label for="' . $input_def['id'] . '" >' . $defaultText . '</label>';
				}
				{/*	Define if the document will be shwon on the frontend part	*/
					$input_def = array();
					$input_def['possible_value'] = 'valid';
					if($element->linkStatus == 'valid')
					{
						$input_def['value'] = 'valid';
					}
					$input_def['type'] = 'checkbox';
					$input_def['name'] = $element->category . '_status_' . $element->linkId;
					if($elementOptions != '')
					{
						$elementOptions .= '<br/><br/>';
					}
					$elementOptions .= wpshop_form::check_input_type($input_def, self::getDbTable() . '_options[frontenddisplay]') . '<label for="' . $input_def['name'] . '" >' . __('Faire appara&icirc;tre dans la gallerie', 'wpshop') . '</label>';
				}

				/*	Get the attribute set list for the current element and check the number	*/
				$attribute_set_id = 0;
				$currentTabContent = array();
				if((count($entitySetList) == 1) && ($element->attribute_set_id == 0))
				{/*	In case that there is only one attrbute set for the current entity and the entity has no attribute_set defined	*/
					$attribute_set_id = $entitySetList[0]->id;
				}
				elseif($element->attribute_set_id > 0)
				{/*	In case that the attribute set has already been set	*/
					$attribute_set_id = $element->attribute_set_id;
				}
				else
				{/*	In case that no attribute set has been set for the current element and that several set exist	*/
					$attribute_set_id = 0;
				}
				if($attribute_set_id > 0)
				{/*	Get the attribute set details in order to build the product interface	*/
					$currentTabContent = wpshop_attributes::getAttributeFieldOutput($attribute_set_id, self::currentPageCode, $element->id, 'column');
				}

				unset($tableRowValue);
				$tableRowValue[] = array('class' => self::currentPageCode . '_file_cell', 'value' => $elementPreview . '
	<div id="rowDocumentName' . $element->id . '" class="wpshopDocumentname" >' . $element->filename . '
	</div>');
				$tableRowValue[] = array('class' => self::currentPageCode . '_option_cell', 'value' => $elementOptions);
				if(count($currentTabContent) > 0)
				{
					$tableRowValue[] = array('class' => self::currentPageCode . '_infos_cell', 'value' => implode('
		', $currentTabContent['generalTabContent']));
					foreach($currentTabContent['columnContent'] as $columnAttributeGroup)
					{
						$tableRowValue[] = array('class' => self::currentPageCode . '_infos_cell', 'value' => $columnAttributeGroup);
					}
				}
				else
				{
					$tableRowValue[] = array('class' => self::currentPageCode . '_infos_cell', 'value' => '');
				}
				$tableRows[] = $tableRowValue;

				$line++;
			}

			/*	Add the column header	*/
			if(count($currentTabContent) > 0)
			{
				$tableRowValue[] = array('class' => self::currentPageCode . '_infos_cell', 'value' => implode('
	', $currentTabContent['generalTabContent']));
				foreach($currentTabContent['columnTitle'] as $columnAttributeGroupCode => $columnAttributeGroupContent)
				{
					$tableTitles[] = __($columnAttributeGroupContent, 'wpshop');
					$tableClasses[] = 'wpshop_' . self::currentPageCode . '_' . $columnAttributeGroupCode . '_column';
				}
			}
		}
		else
		{
			$tableRowsId[] = self::getDbTable() . $documentSection . '_noResult';
			unset($tableRowValue);
			$tableRowValue[] = array('class' => self::currentPageCode . '_no_element_cell', 'value' => __('Aucun document existant', 'wpshop'));
			$tableRows[] = $tableRowValue;
		}
		$listItemOutput = wpshop_display::getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, true);

		return $listItemOutput;
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
			AND DOC.id = '" . $elementId . "' ";
		}

		$query = $wpdb->prepare(
		"SELECT DOC.*
		FROM " . self::getDbTable() . " AS DOC
		WHERE DOC.status IN (".$elementStatus.") " . $moreQuery
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
	*	This function is called when saving the new document into database, by calling the save function and setting the action message
	*
	*	@param string $fileCompletePath The complete path to the uploaded file. With the path and the filename for saving into database
	*
	*	@return string $uploadActionMessage The message returned by the save action
	*/
	function newDocumentSave($fileCompletePath, $isDefault = 'no')
	{
		global $wpdb;
		global $current_user;
		$uploadActionMessage = '';

		/*	Get the document category from the request vars	*/
		$documentCategory = wpshop_tools::varSanitizer($_REQUEST['documentCategory']);

		/*	Save the new uploaded document into the database	*/
		$documentInformations = array();
		$documentInformations['status'] = 'valid';
		$documentInformations['is_default'] = $isDefault;
		$documentInformations['creation_date'] = date('Y-m-d H:i:s');
		$documentInformations['creation_user_id'] = $current_user->ID;
		$documentInformations['category'] = $documentCategory;
		$documentInformations['filename'] = basename($fileCompletePath);
		$documentInformations['filepath'] = dirname(str_replace(str_replace('\\', '/', WP_CONTENT_DIR), '', $fileCompletePath)) . '/';

		if(current_user_can('wpshop_add_document'))
		{
			$newDocument = wpshop_database::save($documentInformations, self::getDbTable());
			if($newDocument != 'error')
			{
				$newDocument = $wpdb->insert_id;
			}
		}
		else
		{
			$newDocument = 'userNotAllowedForActionAdd';
		}

		if(is_integer($newDocument))
		{
			/*	Get the current element type and identifier from the request vars to save the link betwwen the document and the element	*/
			$elementType = wpshop_tools::varSanitizer($_REQUEST['elementType']);
			$elementId = wpshop_tools::varSanitizer($_REQUEST['elementId']);

			/*	Save the link between the new document and the current element	*/
			$documentSaveLink = wpshop_document::saveDocumentLink($newDocument, $elementType, $elementId);
			if(is_integer($documentSaveLink))
			{
				$uploadActionMessage = __('Votre document a correctement &eacute;t&eacute ajout&eacute;', 'wpshop');
			}
			elseif($documentSaveLink == 'error')
			{
				$uploadActionMessage = __('Une erreur est survenue lors de l\'enregistrement de la liaison entre l\'&eacute;l&eacute;ment et le document', 'wpshop');
			}
			elseif($documentSaveLink == 'userNotAllowedForActionAdd')
			{
				$uploadActionMessage = __('Vous n\'&ecirc;test pas autoris&eacute; &agrave; lier des documents et des &eacutel&eacute;lents', 'wpshop');
			}
		}
		elseif($newDocument == 'error')
		{
			$uploadActionMessage = __('Une erreur est survenue lors de l\'enregistrement de votre fichier dans la base de donn&eacute;e', 'wpshop');
		}
		elseif($newDocument == 'userNotAllowedForActionAdd')
		{
			$uploadActionMessage = __('Vous n\'&ecirc;test pas autoris&eacute; &agrave; ajouter des documents', 'wpshop');
		}

		return $uploadActionMessage;
	}

	/**
	* Save a link between a document and an element into database
	*
	*	@param string $newDocument The document identifier we want to link to the element
	*	@param string $elementType The element type (code) we want to associate the document to
	*	@param string $elementId The element identifier we want to associate the document to
	*
	*	@return string|integer $actionResult An error message in case a problem appear during the saving process|An integer that represents the new document link saved into the database
	*/
	function saveDocumentLink($newDocument, $elementType, $elementId)
	{
		global $wpdb;
		global $current_user;
		$actionResult = '';

		$documentInformations = array();
		$documentInformations['status'] = 'valid';
		$documentInformations['attribution_date'] = date('Y-m-d H:i:s');
		$documentInformations['attribution_user_id'] = $current_user->ID;
		$documentInformations['document_id'] = $newDocument;
		$documentInformations['element_type'] = $elementType;
		$documentInformations['element_id'] = $elementId;

		if(current_user_can('wpshop_add_document_link'))
		{
			$actionResult = wpshop_database::save($documentInformations, WPSHOP_DBT_DOCUMENT_LINK_ELEMENT);
			if($actionResult != 'error')
			{
				$actionResult = $wpdb->insert_id;
			}
		}
		else
		{
			$actionResult = 'userNotAllowedForActionAdd';
		}

		return $actionResult;
	}

	/**
	*	Check if the element given in parameter exist on the disc
	*
	*	@param string $pathToCheck The path to the document to check if exist. If only this parameter is given we will check if the path is a directory
	*	@param string $fileToCheck optionnal The file we want to check if exists. If this parameter is given we will check if the file exist
	*
	*	@return boolean $documentExist A boolean variable that allows to know if the file really exist on the disc
	*/
	function checkIfExist($typeToCheck, $pathToCheck, $fileToCheck = '')
	{
		$documentExist = false;

		switch($typeToCheck)
		{
			case 'dir':
				if(is_dir($pathToCheck))
				{
					$documentExist = true;
				}
			break;
			case 'file':
				if(is_file($pathToCheck . $fileToCheck))
				{
					$documentExist = true;
				}
			break;
		}

		return $documentExist;
	}

	/**
	*	Define the type of document output by checking mime type
	*
	*	@param string $file The complete file path to check mime type
	*	@param string $type Define if the output document will be a preview or a full size output when applicable
	*/
	function getFileOutputType($file, $documentInformations, $type = '')
	{
		$mimeTypeComponent = explode('/', self::get_mimetype($file));
		switch($mimeTypeComponent[0])
		{
			case 'image':
				return '<a rel="product_gallery" href="' . $file . '" ><img src="' . $file . '" alt="" class="' . self::getDbTable() . '_' . $documentInformations->category . '_' . $type . '" /></a>';
			break;
			default:
				return __('Aucune aper&ccedil;u disponible', 'wpshop');
			break;
		}
	}
	function get_file_extension($file)
	{
		return array_pop(explode('.',$file));
	}
	function get_mimetype($value='')
	{
		$ct['htm'] = 'text/html';
		$ct['html'] = 'text/html';
		$ct['txt'] = 'text/plain';
		$ct['asc'] = 'text/plain';
		$ct['bmp'] = 'image/bmp';
		$ct['gif'] = 'image/gif';
		$ct['jpeg'] = 'image/jpeg';
		$ct['jpg'] = 'image/jpeg';
		$ct['jpe'] = 'image/jpeg';
		$ct['png'] = 'image/png';
		$ct['ico'] = 'image/vnd.microsoft.icon';
		$ct['mpeg'] = 'video/mpeg';
		$ct['mpg'] = 'video/mpeg';
		$ct['mpe'] = 'video/mpeg';
		$ct['qt'] = 'video/quicktime';
		$ct['mov'] = 'video/quicktime';
		$ct['avi'] = 'video/x-msvideo';
		$ct['wmv'] = 'video/x-ms-wmv';
		$ct['mp2'] = 'audio/mpeg';
		$ct['mp3'] = 'audio/mpeg';
		$ct['rm'] = 'audio/x-pn-realaudio';
		$ct['ram'] = 'audio/x-pn-realaudio';
		$ct['rpm'] = 'audio/x-pn-realaudio-plugin';
		$ct['ra'] = 'audio/x-realaudio';
		$ct['wav'] = 'audio/x-wav';
		$ct['css'] = 'text/css';
		$ct['zip'] = 'application/zip';
		$ct['pdf'] = 'application/pdf';
		$ct['doc'] = 'application/msword';
		$ct['bin'] = 'application/octet-stream';
		$ct['exe'] = 'application/octet-stream';
		$ct['class']= 'application/octet-stream';
		$ct['dll'] = 'application/octet-stream';
		$ct['xls'] = 'application/vnd.ms-excel';
		$ct['ppt'] = 'application/vnd.ms-powerpoint';
		$ct['wbxml']= 'application/vnd.wap.wbxml';
		$ct['wmlc'] = 'application/vnd.wap.wmlc';
		$ct['wmlsc']= 'application/vnd.wap.wmlscriptc';
		$ct['dvi'] = 'application/x-dvi';
		$ct['spl'] = 'application/x-futuresplash';
		$ct['gtar'] = 'application/x-gtar';
		$ct['gzip'] = 'application/x-gzip';
		$ct['js'] = 'application/x-javascript';
		$ct['swf'] = 'application/x-shockwave-flash';
		$ct['tar'] = 'application/x-tar';
		$ct['xhtml']= 'application/xhtml+xml';
		$ct['au'] = 'audio/basic';
		$ct['snd'] = 'audio/basic';
		$ct['midi'] = 'audio/midi';
		$ct['mid'] = 'audio/midi';
		$ct['m3u'] = 'audio/x-mpegurl';
		$ct['tiff'] = 'image/tiff';
		$ct['tif'] = 'image/tiff';
		$ct['rtf'] = 'text/rtf';
		$ct['wml'] = 'text/vnd.wap.wml';
		$ct['wmls'] = 'text/vnd.wap.wmlscript';
		$ct['xsl'] = 'text/xml';
		$ct['xml'] = 'text/xml';

		$extension = self::get_file_extension($value);

		if (!$type = $ct[strtolower($extension)])
		{
			$type = 'text/html';
		}

		return $type;
	}


	/**
	* Return an upload form
	*
	* @param string $tableElement Table of the element which is the photo relative to.
	* @param int $idElement Identifier in the table of the element which is the photo relative to.
	* @param string $repertoireDestination Repository of the uploaded file.
	* @param string $idUpload HTML div identifier.
	* @param string $allowedExtensions Allowed extensions for the upload (ex:"['jpeg','png']"). All extensions is written "[]".
	* @param bool $multiple Can the user upload multiple files in one time ?
	* @param string $actionUpload The url of the file call when the user press on upload button.
	*
	* @return string The upload form with eventually a thumbnail.
	*/
	function getFormButton($documentCategory, $elementType, $elementId, $repertoireDestination, $idUpload, $allowedExtensions, $multiple, $actionUpload, $texteBoutton = '')
	{
		$texteBoutton = ($texteBoutton == '') ? __("Envoyer un fichier", "evarisk") : $texteBoutton;
		$actionUpload = ($actionUpload == '') ? WPSHOP_LIB_PLUGIN_URL . 'documents/uploadFile.php' : $actionUpload;
		$repertoireDestination = ($repertoireDestination == '') ? str_replace('\\', '/', WPSHOP_UPLOAD_DIR . WPSHOP_PLUGIN_DIR . '/vrac/') : $repertoireDestination;
		$multiple = $multiple ? 'true' : 'false';

		$uploadButton = 
			'<script type="text/javascript">        
				wpshop(document).ready(function(){
					var uploader' . $idUpload . ' = new qq.FileUploader({
						element: document.getElementById("' . $idUpload . '"),
						action: "' . $actionUpload . '",
						allowedExtensions: ' . $allowedExtensions . ',
						multiple: ' . $multiple . ',
						params:{
							"folder": "' . $repertoireDestination . '",
							"abspath": "' . str_replace("\\", "/", ABSPATH) . '",
							"mainFile": "' . str_replace("\\", "/", WPSHOP_HOME_DIR . "wpshop.php") . '",
							"documentCategory": "' . $documentCategory . '",
							"elementType": "' . $elementType . '",
							"elementId": "' . $elementId . '"
						},
						onComplete: function(response, file){
							if(response == "0")
							{
								wpshop(".qq-upload-list").hide();
								wpshopShowMessage(wpshopConvertAccentTojs("' . __('Votre document a bien &eacute;t&eacute; envoy&eacute; sur le serveur, il est en cours d\'enregistrement dans la base de donn&eacute;es', 'wpshop') . '"));
								hideShowMessage(5000);
								wpshop("#' . $documentCategory . 'Container").load("' . WPSHOP_AJAX_URL . '", {
										"post": "true",
										"elementCode": "' . self::currentPageCode . '",
										"action": "loadDocumentListForElement",
										"elementType": "' . $elementType . '",
										"elementIdentifier": "' . $elementId . '",
										"documentCategory": "' . $documentCategory . '"
									});
							}
						}
					});

					wpshop("#' . $idUpload . ' .qq-upload-button").each(function(){
						wpshop(this).html("' . $texteBoutton . '");
						uploader' . $idUpload . '._button = new qq.UploadButton({
							element: uploader' . $idUpload . '._getElement("button"),
							multiple: ' . $multiple . ',
							onChange: function(input){
								uploader' . $idUpload . '._onInputChange(input);
							}
						});
					});
					wpshop(".qq-upload-drop-area").each(function(){
						wpshop(this).html("' . __("D&eacute;poser les fichiers ici pour les t&eacute;l&eacute;charger", "wpshop") . '");
					});
				});
			</script>
			<div id="' . $idUpload . '" class="divUpload">
				<noscript>			
					<p>' . __("Vous devez activer le javascript pour pouvoir envoyer un fichier", "wpshop") . '</p>
				</noscript>         
			</div>';

		return $uploadButton;
	}

	/**
	*	Upload files through a basic html form
	*
	*	@param array $fileInformations An array containing $_FILES the informations concerning the file send
	*	@param string $inputName optionnal In case that the form contains only one file input we specify the name of this file to avoid useless treatment
	*
	*	@return string $uploadFileResponse The response of the file upload
	*/
	function uploadFileHttpMethod($fileInformations, $uploadDirectory, $inputName = '')
	{
		$uploadFileResponse = '';

		/*	Upload only one input	*/
		if(($inputName != '') && isset($fileInformations) && isset($fileInformations['name'][$inputName]) && ($fileInformations['name'][$inputName] != ''))
		{
			if($fileInformations['error'][$inputName] != UPLOAD_ERR_OK)
			{
				$uploadFileResponse = 'error';
			}
			else
			{
				wpshop_tools::createDirectory(WP_CONTENT_DIR . $uploadDirectory);
				$iconeFileName = $uploadDirectory . $fileInformations['name'][$inputName];
				if(move_uploaded_file($fileInformations['tmp_name'][$inputName], WP_CONTENT_DIR . $iconeFileName))
				{
					$uploadFileResponse = $iconeFileName;
				}
				else
				{
					$uploadFileResponse = 'error';
				}
			}
		}

		return $uploadFileResponse;
	}

	/**
	* Upload a file by looking at the upload method. Could be http method or ajax method
	*
	*	@param string $destinationFolder The folder we want that receive the uploaded file
	*	@param string $inputName optionnal The name of the input to get for file informations
	*
	*	@return array An array with the result success(true or false), file(The uploaded file path), error(The error message)
	*/
	function uploadFile($destinationFolder, $inputName = 'qqfile')
	{
		$maxFileSize = MAX_PICTURE_SIZE;

		if(isset($_GET[$inputName]))
		{
			$file = new wpshop_uploadFileAjax();
		} 
		elseif(isset($_FILES[$inputName]))
		{
			$file = new wpshop_uploadFileHttp();
		} 
		else 
		{
			return array(success=>false);
		}	

		$size = $file->getSize($inputName);
		if ($size == 0)
		{
			return array(success=>false, error=>__('Le fichier est vide', 'wpshop'));
		}				
		if ($size > $maxFileSize)
		{
			return array(success=>false, error=>__('Le fichier est trop volumineux', 'wpshop'));
		}

		$pathinfo = pathinfo($file->getName($inputName));		
		$ext = $pathinfo['extension'];

		$tempFile = $_GET[$inputName];
		$targetPath = $destinationFolder;
		$targetFile =  str_replace('//','/',$targetPath) . wpshop_tools::slugify($_GET[$inputName], array('noAccent', 'noSpaces', 'lowerCase'));
		$numero = $extention = $nomFichier = "";
		$temps = explode('.', wpshop_tools::slugify($_GET[$inputName], array('noAccent', 'noSpaces', 'lowerCase')));
		foreach($temps as $temp)
		{
			$nomFichier = $nomFichier . $extention;
			$extention = $temp;
		}
		if(file_exists($targetFile))
		{
			$numero = 1;
			$nomFichierTest = $nomFichier . $numero . '.' . $extention;
			while(file_exists(str_replace('//','/',$targetPath) . $nomFichierTest))
			{
				$numero = $numero + 1;
				$nomFichierTest = $nomFichier . $numero . '.'  . $extention;
			}
			$targetFile = str_replace('//','/',$targetPath). $nomFichierTest;
		}

		/*	Check if the destination folder exist, if not exists create it recursively	*/
		if(!file_exists(str_replace('//','/',$targetPath)))
		{
			mkdir(str_replace('//','/',$targetPath), 0755, true);
		}
		/*	Change the authorization on the target directory	*/
		wpshop_tools::changeAccesAuthorisation($targetPath);

		/*	Save the file into the good directory on the disk	*/
		$file->save($targetFile, $inputName);

		/*	Save the new document into database and output the message according to the result	*/
		$savingMessage = self::newDocumentSave($targetFile);

		return array("success" => true, "file" => $targetFile, "actionMessage" => $savingMessage);
	}

	/**
	*	Output the document list for a given element and a given document category
	*
	*	@param string $elementType The type of the element we want to get the document list for
	*	@param integer $elementId The identifier of the element we want to get the document list for
	*	@param string $documentCategory The category of the document we want to get
	*
	*	@return mixed The output of the document list
	*/
	function getAssociatedDocument($elementType, $elementId, $documentCategory, $documentStatus = "'valid'", $documentLinkStatus = "'valid'") 
	{
		global $wpdb;

		$query = $wpdb->prepare("
			SELECT DOC.*, DOCLINK.id AS linkId, DOCLINK.status AS linkStatus, DOCLINK.default_for_element, DOCLINK.element_id, DOCLINK.element_type
			FROM " . self::getDbTable() . " AS DOC
				INNER JOIN " . WPSHOP_DBT_DOCUMENT_LINK_ELEMENT . " AS DOCLINK ON (DOCLINK.document_id = DOC.id) 
			WHERE DOCLINK.element_id = %d
				AND DOCLINK.element_type = %s
				AND DOCLINK.status IN (" . $documentLinkStatus . ")
				AND DOC.status IN (" . $documentStatus . ")
				AND DOC.category = '%s' ", $elementId, $elementType, $documentCategory);
		$associatedDocument = $wpdb->get_results($query);

		return self::elementList($associatedDocument, $documentCategory);
	}

	/**
	*	Set a document as the default one for a given element
	*
	*	@param string $elementType The element's type we want to set the document as default for
	*	@param integer $elementIdentifier The element's identifier we want to set the default document for
	*	@param integer $documentIdentifier The document identifier we want to set as default for the selected element
	*
	*	@return 
	*/
	function setDefaultDocumentForElement($elementType, $elementIdentifier, $documentIdentifier, $documentCategory)
	{
		global $wpdb;

		$query = $wpdb->prepare("
			UPDATE " . WPSHOP_DBT_DOCUMENT_LINK_ELEMENT . " AS DOC_LINK
				INNER JOIN " . WPSHOP_DBT_DOCUMENT . " AS DOC ON (DOC.id = DOC_LINK.document_id)
				SET DOC_LINK.default_for_element = 'no' 
			WHERE DOC_LINK.element_id = %d
				AND DOC_LINK.element_type = %d
				AND DOC.category = %s",
		$elementIdentifier, $elementType, $documentCategory);
		$wpdb->query($query);

		$query = $wpdb->prepare("
			UPDATE " . WPSHOP_DBT_DOCUMENT_LINK_ELEMENT . " 
				SET default_for_element = 'yes' 
			WHERE document_id = %d
				AND element_id = %d
				AND element_type = %d",
		$documentIdentifier, $elementIdentifier, $elementType);
		$wpdb->query($query);
	}
	/**
	*	Set a document as the default one for a given element
	*
	*	@param string $elementType The element's type we want to set the document as default for
	*	@param integer $elementIdentifier The element's identifier we want to set the default document for
	*	@param string $documentIdentifier A list with the document identifier
	*/
	function setStatusHideForElement($elementType, $elementIdentifier, $documentIdentifier, $documentCategory)
	{
		global $wpdb;

		/*	First set all status to moderated	*/
		$query = $wpdb->prepare("
			UPDATE " . WPSHOP_DBT_DOCUMENT_LINK_ELEMENT . " AS DOC_LINK
				INNER JOIN " . WPSHOP_DBT_DOCUMENT . " AS DOC ON (DOC.id = DOC_LINK.document_id)
				SET DOC_LINK.status = 'moderated' 
			WHERE DOC_LINK.element_id = %d
				AND DOC_LINK.element_type = %d
				AND DOC.category = %s",
		$elementIdentifier, $elementType, $documentCategory);
		$wpdb->query($query);

		/*	Then set the selected documents as valid	*/
		$query = $wpdb->prepare("
			UPDATE " . WPSHOP_DBT_DOCUMENT_LINK_ELEMENT . " AS DOC_LINK
				SET DOC_LINK.status = 'valid' 
			WHERE DOC_LINK.document_id IN (" . $documentIdentifier . ")
				AND DOC_LINK.element_id = %d
				AND DOC_LINK.element_type = %d",
		$elementIdentifier, $elementType);
		$wpdb->query($query);
	}

}

/**
* Define the different method to manage an ajax upload
* @package wpshop
* @subpackage librairies
*/
class wpshop_uploadFileAjax
{
	/**
	*	Place the uploaded file into the selected directory
	*
	*	@param string $path The directory we want to put the uploaded file into
	*	@param string $inputName The input name to get file informations from
	*
	*	@return void
	*/
	function save($path, $inputName)
	{
		$input = fopen("php://input", "r");
		$fp = fopen($path, "w");
		while($data = fread($input, 1024))
		{
			fwrite($fp,$data);
		}
		fclose($fp);
		fclose($input);			
	}

	/**
	* Return the uploaded file name
	*
	*	@param string $inputName The input name to get file informations from
	*
	*	@return string The name of the uploaded file
	*/
	function getName($inputName)
	{
		return $_GET['name'];
	}

	/**
	* Return the uploaded file size
	*
	*	@param string $inputName The input name to get file informations from
	*
	*	@return string The size of the uploaded file
	*/
	function getSize($inputName)
	{
		return (int)$_SERVER['CONTENT_LENGTH'];
	}
}

/**
* Define the different method to manage a http upload
* @package wpshop
* @subpackage librairies
*/
class wpshop_uploadFileHttp
{	
	/**
	*	Place the uploaded file into the selected directory
	*
	*	@param string $path The directory we want to put the uploaded file into
	*	@param string $inputName The input name to get file informations from
	*
	*	@return void
	*/
	function save($path, $inputName)
	{
		move_uploaded_file($_FILES[$inputName]['tmp_name'], $path);
	}

	/**
	* Return the uploaded file name
	*
	*	@param string $inputName The input name to get file informations from
	*
	*	@return string The name of the uploaded file
	*/
	function getName($inputName)
	{
		return $_FILES[$inputName]['name'];
	}

	/**
	* Return the uploaded file size
	*
	*	@param string $inputName The input name to get file informations from
	*
	*	@return string The size of the uploaded file
	*/
	function getSize($inputName)
	{
		return $_FILES[$inputName]['size'];
	}
}