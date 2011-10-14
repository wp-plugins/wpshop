<?php
/**
* Plugin tools librairies file.
* 
*	This file contains the different common tools used in all the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

class wpshop_display
{

	/**
	*	Returns the header display of a classical HTML page.
	*
	*	@see afficherFinPage
	*
	*	@param string $pageTitle Title of the page.
	*	@param string $pageIcon Path of the icon.
	*	@param string $iconTitle Title attribute of the icon.
	*	@param string $iconAlt Alt attribute of the icon.
	*	@param boolean $hasAddButton Define if there must be a "add" button for this page
	*	@param string $actionInformationMessage A message to display in case of action is send
	*
	*	@return string Html code composing the page header
	*/
	function displayPageHeader($pageTitle, $pageIcon, $iconTitle, $iconAlt, $hasAddButton = true, $addButtonLink = '', $formActionButton = '', $actionInformationMessage = ''){
?>
<div class="wrap wpshopMainWrap" >
	<div id="wpshopLoadingPicture" class="wpshopHide" ><img src="<?php echo WPSHOP_LOADING_ICON; ?>" alt="loading picture" class="wpshopPageMessage_Icon" /></div>
	<div id="wpshopMessage" class="fade below-h2 wpshopPageMessage <?php echo (($actionInformationMessage != '') ? 'wpshopPageMessage_Updated' : ''); ?>" ><?php _e($actionInformationMessage, 'wpshop'); ?></div>
<?php
	if($pageIcon != ''){
?>
	<div class="icon32 wpshopPageIcon" ><img alt="<?php _e($iconAlt, 'wpshop'); ?>" src="<?php _e($pageIcon); ?>" title="<?php _e($iconTitle, 'wpshop'); ?>" /></div>
<?php
	}
?>
	<div class="pageTitle" id="pageTitleContainer" >
		<h2 class="alignleft" ><?php _e($pageTitle, 'wpshop');
		if($hasAddButton){
?>
			<a href="<?php echo $addButtonLink ?>" class="button add-new-h2" ><?php _e('Add', 'wpshop') ?></a>
<?php
		}
?>
		</h2>
		<div id="pageHeaderButtonContainer" class="pageHeaderButton" ><?php _e($formActionButton); ?></div>
	</div>
	<div id="champsCaches" class="clear wpshopHide" ></div>
	<div class="clear" id="wpshopMainContent" >
<?php
	}

	/**
	*	Returns the end of a classical page
	*
	*	@see displayPageHeader
	*
	*	@return string Html code composing the page footer
	*/
	function displayPageFooter(){
?>
	</div>
	<div class="clear wpshopHide" id="ajax-response"></div>
</div>
<?php
	}

	/**
	*	Return The complete output page code
	*
	*	@return string The complete html page output
	*/
	function display_page(){
		$pageAddButton = false;
		$pageMessage = $addButtonLink = $pageFormButton = $pageIcon = $pageIconTitle = $pageIconAlt = $objectType = '';
		$outputType = 'listing';
		$objectToEdit = isset($_REQUEST['id']) ? wpshop_tools::varSanitizer($_REQUEST['id']) : '';
		$pageSlug = isset($_REQUEST['page']) ? wpshop_tools::varSanitizer($_REQUEST['page']) : '';
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : '';

		/*	Select the content to add to the page looking for the parameter	*/
		switch($pageSlug){
			case WPSHOP_URL_SLUG_ATTRIBUTE_LISTING:
				$objectType = new wpshop_attributes();
				$current_user_can_edit = current_user_can('wpshop_edit_attributes');
				$current_user_can_add = current_user_can('wpshop_add_attributes');
				$current_user_can_delete = current_user_can('wpshop_delete_attributes');
				if(current_user_can('wpshop_add_attributes'))
				{
					$pageAddButton = true;
				}
			break;
			case WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING:
				$objectType = new wpshop_attributes_set();
				$current_user_can_edit = current_user_can('wpshop_edit_attribute_set');
				$current_user_can_add = current_user_can('wpshop_add_attribute_set');
				$current_user_can_delete = current_user_can('wpshop_delete_attribute_set');
				if(current_user_can('wpshop_add_attribute_set'))
				{
					$pageAddButton = true;
				}
			break;
			default:
			{
				$pageTitle = sprintf(__('You have to add this page into %s at line %s', 'wpshop'), __FILE__, (__LINE__ - 4));
				$pageAddButton = false;
			}
			break;
		}

		if($objectType != ''){
			if(($action != '') && ((($action == 'edit') && $current_user_can_edit) || (($action == 'add') && $current_user_can_add) || (($action == 'delete') && $current_user_can_delete))){
				$outputType = 'adding';
			}
			$objectType->elementAction();

			$pageIcon = self::getPageIconInformation('path', $objectType);
			$pageIconTitle = self::getPageIconInformation('title', $objectType);
			$pageIconAlt = self::getPageIconInformation('alt', $objectType);

			if($outputType == 'listing'){
				$pageContent = $objectType->elementList();
			}
			elseif($outputType == 'adding'){
				$pageAddButton = false;

				$pageFormButton = $objectType->getPageFormButton();

				$pageContent = $objectType->elementEdition($objectToEdit);
			}

			$pageTitle = $objectType->pageTitle();
			$pageMessage = $objectType->pageMessage;
			$addButtonLink = admin_url('admin.php?page=' . $objectType->getEditionSlug() . '&amp;action=add');
		}

		/*	Page content header	*/
		wpshop_display::displayPageHeader($pageTitle, $pageIcon, $pageIconTitle, $pageIconAlt, $pageAddButton, $addButtonLink, $pageFormButton, $pageMessage);

		/*	Page content	*/
		echo $pageContent;

		/*	Page content footer	*/
		wpshop_display::displayPageFooter();
	}


	/**
	*	Return the page help content
	*
	*	@return void
	*/
	function addContextualHelp(){
		$pageSlug = isset($_REQUEST['page']) ? wpshop_tools::varSanitizer($_REQUEST['page']) : '';

		/*	Select the content to add to the page looking for the parameter	*/
		switch($pageSlug){
			case WPSHOP_URL_SLUG_PRODUCT_EDITION:
				$pageHelpContent = __('There is no help for this page', 'wpshop');
			break;
		}

		add_contextual_help('boutique_page_' . $pageSlug , __($pageHelpContent, 'wpshop') );
	}


	/*
	* Return a complete html table with header, body and content
	*
	*	@param string $tableId The unique identifier of the table in the document
	*	@param array $tableTitles An array with the different element to put into the table's header and footer
	*	@param array $tableRows An array with the different value to put into the table's body
	*	@param array $tableClasses An array with the different class to affect to table rows and cols
	*	@param array $tableRowsId An array with the different identifier for table lines
	*	@param string $tableSummary A summary for the table
	*	@param boolean $withFooter Allow to define if the table must be create with a footer or not
	*
	*	@return string $table The html code of the table to output
	*/
	function getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, $withFooter = true){
		$tableTitleBar = $tableBody = '';

		/*	Create the header and footer row	*/
		for($i=0; $i<count($tableTitles); $i++){
			$tableTitleBar .= '
				<th class="' . $tableClasses[$i] . '" scope="col" >' . $tableTitles[$i] . '</th>';
		}
		
		/*	Create each table row	*/
		for($lineNumber=0; $lineNumber<count($tableRows); $lineNumber++){
			$tableRow = $tableRows[$lineNumber];
			$tableBody .= '
		<tr id="' . $tableRowsId[$lineNumber] . '" class="tableRow" >';
			for($i=0; $i<count($tableRow); $i++){
				$tableBody .= '
			<td class="' . $tableClasses[$i] . ' ' . $tableRow[$i]['class'] . '" >' . $tableRow[$i]['value'] . '</td>';
			}
			$tableBody .= '
		</tr>';
		}

		/*	Create the table output	*/
		$table = '
<table id="' . $tableId . '" cellspacing="0" cellpadding="0" class="widefat post fixed" summary="' . $tableSummary . '" >';
		if($tableTitleBar != ''){
			$table .= '
	<thead>
			<tr class="tableTitleHeader" >' . $tableTitleBar . '
			</tr>
	</thead>';
			if($withFooter){
				$table .= '
	<tfoot>
			<tr class="tableTitleFooter" >' . $tableTitleBar . '
			</tr>
	</tfoot>';
			}
		}
		$table .= '
	<tbody>' . $tableBody . '
	</tbody>
</table>';

		return $table;
	}

	/**
	*	Define the icon informations for the page
	*
	*	@param string $infoType The information type we want to get Could be path / alt / title
	*
	*	@return string $pageIconInformation The information to output in the page
	*/
	function getPageIconInformation($infoType, $object){
		switch($infoType){
			case 'path':
				$pageIconInformation = $object->pageIcon;
			break;
			case 'alt':
			case 'title':
			default:
				$pageIconInformation = $object->pageTitle();
			break;
		}

		return $pageIconInformation;
	}

	/**
	*	Check if the templates file are available from the current theme. If not present return the default templates files
	*
	*	@param string $file_name The file name to check if exists in current theme
	*	@param string $dir_name Optionnal The directory name of the file to check Default : wpshop
	*
	*	@return string $file_path The good filepath to include
	*/
	function get_template_file($file_name, $default_dir = WPSHOP_TEMPLATES_DIR, $dir_name = 'wpshop', $usage_type = 'include'){
		$file_path = '';
		$the_file = $dir_name . '/' . $file_name;

		if(is_file(get_stylesheet_directory() . '/' . $the_file)){
			$default_dir = str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, get_stylesheet_directory());
			if($usage_type == 'include'){
				$default_dir = get_stylesheet_directory();
			}
			$file_path = $default_dir . '/' . $the_file;
		}
		else{
			$file_path = $default_dir . $the_file;
		}

		return $file_path;
	}


	/**
	*	Check if template file exist in current theme directory. If not the case copy all template files into
	*
	*	@param boolean $force_replacement Define if we overwrite in all case or just if it not exist
	*/
	function check_template_file($force_replacement = false){
		/*	Add different file template	*/
		if(!is_dir(get_stylesheet_directory() . '/wpshop')){
			mkdir(get_stylesheet_directory() . '/wpshop', 0755, true);
			wpshop_tools::copyEntireDirectory(WPSHOP_TEMPLATES_DIR . 'wpshop', get_stylesheet_directory() . '/wpshop');
		}
		elseif(($force_replacement)){
			wpshop_tools::copyEntireDirectory(WPSHOP_TEMPLATES_DIR . 'wpshop', get_stylesheet_directory() . '/wpshop');
		}

		/*	Add the category template	*/
		if(!is_file(get_stylesheet_directory() . '/taxonomy-wpshop_product_category.php') || ($force_replacement)){
			copy(WPSHOP_TEMPLATES_DIR . 'taxonomy-wpshop_product_category.php', get_stylesheet_directory() . '/taxonomy-wpshop_product_category.php');
		}
	}

}