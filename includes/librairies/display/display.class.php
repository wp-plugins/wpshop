<?php

/*	Vérification de l'inclusion correcte du fichier => Interdiction d'acceder au fichier directement avec l'url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

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
	function displayPageHeader($pageTitle, $pageIcon, $iconTitle, $iconAlt, $hasAddButton = true, $addButtonLink = '', $actionInformationMessage = '', $current_page_slug = ''){
		include(WPSHOP_TEMPLATES_DIR.'admin/admin_page_header.tpl.php');
	}

	/**
	*	Returns the end of a classical page
	*
	*	@see displayPageHeader
	*
	*	@return string Html code composing the page footer
	*/
	function displayPageFooter($formActionButton){
		include(WPSHOP_TEMPLATES_DIR.'admin/admin_page_footer.tpl.php');
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
				if(current_user_can('wpshop_add_attributes')){
					$pageAddButton = true;
				}
			break;
			case WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING:
				$objectType = new wpshop_attributes_set();
				$current_user_can_edit = current_user_can('wpshop_edit_attribute_set');
				$current_user_can_add = current_user_can('wpshop_add_attribute_set');
				$current_user_can_delete = current_user_can('wpshop_delete_attribute_set');
				if(current_user_can('wpshop_add_attribute_set')){
					$pageAddButton = true;
				}
			break;
			case WPSHOP_URL_SLUG_SHORTCODES:
				$pageAddButton = false;
				$current_user_can_edit = false;
				$objectType = new wpshop_shortcodes();
			break;
			case WPSHOP_URL_SLUG_MESSAGES:
				$pageAddButton = false;
				$objectType = new wpshop_messages();
				$current_user_can_edit = true;
				if(!empty($_GET['mid'])){
					$action = 'edit';
				}
			break;
			case WPSHOP_URL_SLUG_DASHBOARD:
				$pageAddButton = false;
				$pageTitle = __('Shop dashboard', 'wpshop');
				$pageContent = wpshop_dashboard::display_dashboard();
			break;
			default:{
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

				$pageFormButton = $objectType->getPageFormButton($objectToEdit);

				$pageContent = $objectType->elementEdition($objectToEdit);
			}

			$pageTitle = $objectType->pageTitle();
			$pageMessage = $objectType->pageMessage;
			$addButtonLink = admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&amp;page=' . $objectType->getEditionSlug() . '&amp;action=add');
		}

		/*	Page content header	*/
		wpshop_display::displayPageHeader($pageTitle, $pageIcon, $pageIconTitle, $pageIconAlt, $pageAddButton, $addButtonLink, $pageMessage, $pageSlug);

		/*	Page content	*/
		echo $pageContent;

		/*	Page content footer	*/
		wpshop_display::displayPageFooter($pageFormButton);
	}

	function custom_page_output_builder($content, $output_type='tab'){
		$output_custom_layout = '';

		switch($output_type){
			case 'separated_bloc':
				foreach($content as $element_type => $element_type_details){
					$output_custom_layout.='
	<div class="wpshop_separated_bloc wpshop_separated_bloc_'.$element_type.'" >';
					foreach($element_type_details as $element_type_key => $element_type_content){
						$output_custom_layout.='
		<div class="wpshop_admin_box wpshop_admin_box_'.$element_type.' wpshop_admin_box_'.$element_type.'_'.$element_type_key.'" >
			<h3>' . $element_type_content['title'] . '</h3>' . $element_type_content['content'] . '
		</div>';
					}
					$output_custom_layout.='
	</div>';
				}
			break;
			case 'tab':
				$tab_list=$tab_content_list='';
				foreach($content as $element_type => $element_type_details){
					foreach($element_type_details as $element_type_key => $element_type_content){
						$tab_list.='
		<li><a href="#wpshop_'.$element_type.'_'.$element_type_key.'" >'.$element_type_content['title'].'</a></li>';
						$tab_content_list.='
		<div id="wpshop_'.$element_type.'_'.$element_type_key.'" class="wpshop_admin_box wpshop_admin_box_'.$element_type.' wpshop_admin_box_'.$element_type.'_'.$element_type_key.'" >'.$element_type_content['content'].'
		</div>';
					}
				}
				$output_custom_layout.='
	<div id="wpshopFormManagementContainer" class="wpshop_tabs wpshop_full_page_tabs wpshop_'.$element_type.'_tabs" >
		<ul>' . $tab_list . '</ul>' . $tab_content_list . '
	</div>';
					break;
		}

		return $output_custom_layout;
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
	function getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary = '', $withFooter = true){
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
<table id="' . $tableId . '" cellspacing="0" cellpadding="0" class="widefat post fixed" >';
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
		$wpshop_directory = get_stylesheet_directory() . '/wpshop';

		/*	Add different file template	*/
		if(!is_dir($wpshop_directory)){
			mkdir($wpshop_directory, 0755, true);
		}
		/* On s'assure que le dossier principal est bien en 0755	*/
		@chmod($wpshop_directory, 0755);
		$upload_dir = wp_upload_dir();
		exec('chmod -R 755 ' . $upload_dir['basedir']);

		/*	Add the category template	*/
		if(!is_file(get_stylesheet_directory() . '/taxonomy-wpshop_product_category.php') || ($force_replacement)){
			copy(WPSHOP_TEMPLATES_DIR . 'taxonomy-wpshop_product_category.php', get_stylesheet_directory() . '/taxonomy-wpshop_product_category.php');
		}
	}

	/**
	*	Read the template files content
	*/
	function list_template_files($directory, $tab = 0){
		$output = '';
		$dir_content = opendir($directory);

		$i = $tab + 1;
		while($item = readdir($dir_content)){
			if(is_dir($directory . '/' . $item) && ($item != '.') && ($item != '..')  && ($item != '.svn') ){
				$output .= '<span class="wpshop_underline" >' . str_repeat('-', $tab) . $directory . '/' . $item . '</span><br/>';
				$new_tab = $tab + 1;
				$output .= self::list_template_files($directory . '/' . $item, $new_tab);
			}
			elseif(is_file($directory . '/' . $item)){
				$output .= str_repeat('-', $tab) . '<input type="checkbox" checked="checked" class="template_file_to_replace_checkbox" name="template_file_to_replace[]" id="template_file_to_replace_' . $item . '" value="' . $directory . '/' . $item . '" />&nbsp;<label for="template_file_to_replace_' . $item . '" >' . $item . '</label><br/>';
			}
			$i++;
		}
		closedir($dir_content);

		return $output;
	}
	
	// -----------------
	// -- RICH TEXT EDIT
	// -----------------
	function wpshop_rich_text_tags() {
	
		global $wpdb, $user, $current_user, $pagenow, $wp_version;
		
		// ADD EVENTS
		if($pagenow == 'edit-tags.php') {
		
			if(!user_can_richedit()) { return; }

			$taxonomies = get_taxonomies();
			
			foreach($taxonomies as $tax) {
				add_action($tax.'_edit_form_fields', array('wpshop_display','wpshop_add_form'));
				add_action($tax.'_add_form_fields', array('wpshop_display','wpshop_add_form'));
			}
			
			if($pagenow == 'edit-tags.php' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && empty($_REQUEST['taxonomy'])) {
				add_action('edit_term',array('wpshop_display','wpshop_rt_taxonomy_save'));
			}
			
			foreach ( array( 'pre_term_description', 'pre_link_description', 'pre_link_notes', 'pre_user_description' ) as $filter ) {
				remove_filter( $filter, 'wp_filter_kses' );
			}
			
			//add_action('show_user_profile', array('wpshop_display','wpshop_add_form'), 1);
			//add_action('edit_user_profile', array('wpshop_display','wpshop_add_form'), 1);
			//add_action('edit_user_profile_update', array('wpshop_display','wpshop_rt_taxonomy_save'));
		}
		
		// Enable shortcodes in category, taxonomy, tag descriptions
		if(function_exists('term_description')) {
			add_filter('term_description', 'do_shortcode');
		} else {
			add_filter('category_description', 'do_shortcode');
		}
	}

	// PROCESS FIELDS
	function wpshop_rt_taxonomy_save() {
		global $tag_ID;
		$a = array('description');
		echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
		foreach($a as $v) {
			wp_update_term($tag_ID,$v,$_POST[$v]);
		}
	}


	function wpshop_add_form($object = ''){
		global $pagenow;
		
		$css = '
		<style type="text/css">
			.wp-editor-container .quicktags-toolbar input.ed_button {
				width:auto;
			}
			.html-active .wp-editor-area { border:0;}
		</style>';

		// This is a profile page
		if(is_a($object, 'WP_User')) {
			$content = html_entity_decode(get_user_meta($object->ID, 'description', true));
			$editor_selector = $editor_id = 'description';
			?>
		<table class="form-table rich-text-tags">
		<tr>
			<th><label for="description"><?php _e('Biographical Info'); ?></label></th>
			<td><?php wp_editor($content, $editor_id, 
				array(
					'textarea_name' => $editor_selector, 
					'editor_css' => $css,
				)); ?><br />
			<span class="description"><?php _e('Share a little biographical information to fill out your profile. This may be shown publicly.'); ?></span></td>
		</tr>
	<?php
		} 
		// This is a taxonomy
		else {
			$content = is_object($object) && isset($object->description) ? html_entity_decode($object->description) : '';
			
			if( in_array($pagenow, array('edit-tags.php')) ) {
				$editor_id = 'tag_description';
				$editor_selector = 'description';
			} else {
				$editor_id = $editor_selector = 'category_description';
			}
			
			?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="description"><?php _ex('Description', 'Taxonomy Description'); ?></label></th>
		<td><?php wp_editor($content, $editor_id, 
			array(
				'textarea_name' => $editor_selector, 
				'editor_css' => $css,
			)); ?><br />
		<span class="description"><?php _e('The description is not prominent by default, however some themes may show it.'); ?></span></td>
	</tr>
	<?php 
		}
	}


}